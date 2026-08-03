<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Website_service {

    /** @var CI_Controller */
    private $ci;

    const HOW_TO_USE_MODULE = 'downloads';

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->helper('custom');
    }

    public function can_manage($user)
    {
        return $user && $user['role'] === 'Admin';
    }

    public function meta()
    {
        $campuses = $this->ci->db->order_by('campus_name', 'ASC')->get('campuses')->result_array();
        $events = $this->ci->db->order_by('name', 'ASC')->get('events')->result_array();
        return array(
            'campuses' => $campuses,
            'events' => $events,
        );
    }

    // --- How To Use (module=downloads) ---

    public function how_to_use_list()
    {
        if (!$this->ci->db->table_exists('how_to_use')) {
            return array();
        }
        return $this->ci->db
            ->order_by('id', 'DESC')
            ->get_where('how_to_use', array('module' => self::HOW_TO_USE_MODULE))
            ->result_array();
    }

    public function how_to_use_add($user)
    {
        if (!$this->ci->db->table_exists('how_to_use')) {
            return array('success' => false, 'message' => 'how_to_use table missing');
        }
        $title = trim($this->ci->input->post('title'));
        if ($title === '') {
            return array('success' => false, 'message' => 'Title required');
        }
        $upload = $this->_upload('picture', 'how_to_use');
        if ($upload === false) {
            return array('success' => false, 'message' => 'File upload failed');
        }
        $mime = '';
        if (!empty($_FILES['picture']['type'])) {
            $mime = $_FILES['picture']['type'];
        }
        $name = isset($user['first_name']) ? $user['first_name'] . ' ' . $user['last_name'] : 'Admin';
        $row = array(
            'title' => $title,
            'file_type' => $mime,
            'file' => $upload,
            'module' => self::HOW_TO_USE_MODULE,
            'created_by' => $name,
        );
        $this->ci->db->insert('how_to_use', $row);
        return array('success' => true, 'id' => $this->ci->db->insert_id());
    }

    public function how_to_use_delete($id)
    {
        if (!$this->ci->db->table_exists('how_to_use')) {
            return array('success' => false, 'message' => 'how_to_use table missing');
        }
        $row = $this->ci->db->get_where('how_to_use', array(
            'id' => (int) $id,
            'module' => self::HOW_TO_USE_MODULE,
        ))->row_array();
        if (!$row) {
            return array('success' => false, 'message' => 'Not found');
        }
        if (!empty($row['file']) && file_exists(FCPATH . 'how_to_use/' . $row['file'])) {
            @unlink(FCPATH . 'how_to_use/' . $row['file']);
        }
        $this->ci->db->delete('how_to_use', array('id' => (int) $id));
        return array('success' => true);
    }

    // --- Downloads ---

    public function downloads_list($user)
    {
        $rows = $this->ci->db->order_by('download_id', 'DESC')->get('downloads')->result_array();
        return $this->_decorate_campus_rows($this->_filter_campus_list($rows, $user, 'campus_ids'), 'campus_ids');
    }

    public function download_add($body)
    {
        $campus_ids = $this->_campus_ids_from_body($body, 'campus_ids');
        if ($campus_ids === '') {
            return array('success' => false, 'message' => 'Campus required');
        }
        $title = isset($body['title']) ? trim($body['title']) : '';
        if ($title === '') {
            return array('success' => false, 'message' => 'Title required');
        }
        $document = $this->_upload('url', 'downloads');
        if ($document === false || $document === '') {
            return array('success' => false, 'message' => 'File upload required');
        }
        $this->ci->db->insert('downloads', array(
            'campus_ids' => $campus_ids,
            'title' => $title,
            'document' => $document,
        ));
        return array('success' => true, 'download_id' => $this->ci->db->insert_id());
    }

    public function download_delete($id, $user)
    {
        return $this->_delete_or_unlink_campus('downloads', 'download_id', (int) $id, 'campus_ids', $user);
    }

    // --- Events ---

    public function events_list($user)
    {
        $rows = $this->ci->db->order_by('event_id', 'DESC')->get('events')->result_array();
        return $this->_decorate_campus_rows($this->_filter_campus_list($rows, $user, 'campus_ids'), 'campus_ids');
    }

    public function event_get($event_id)
    {
        $row = $this->ci->db->get_where('events', array('event_id' => (int) $event_id))->row_array();
        if (!$row) {
            return null;
        }
        $row['campus_ids'] = !empty($row['campus_ids']) ? array_map('intval', explode(',', $row['campus_ids'])) : array();
        return $row;
    }

    public function event_save($body, $event_id = null)
    {
        $campus_ids = $this->_campus_ids_from_body($body, 'campus_ids');
        if ($campus_ids === '') {
            return array('success' => false, 'message' => 'Campus required');
        }
        $name = isset($body['name']) ? trim($body['name']) : '';
        if ($name === '') {
            return array('success' => false, 'message' => 'Event name required');
        }
        $show = isset($body['show_on_website']) && (string) $body['show_on_website'] === '0' ? 0 : 1;
        $data = array(
            'campus_ids' => $campus_ids,
            'name' => $name,
            'show_on_website' => $show,
        );
        if ($event_id) {
            $this->ci->db->where('event_id', (int) $event_id)->update('events', $data);
            return array('success' => true, 'event_id' => (int) $event_id);
        }
        $this->ci->db->insert('events', $data);
        return array('success' => true, 'event_id' => $this->ci->db->insert_id());
    }

    public function event_delete($id, $user)
    {
        return $this->_delete_or_unlink_campus('events', 'event_id', (int) $id, 'campus_ids', $user);
    }

    // --- Event images ---

    public function event_images_list($user)
    {
        $this->ci->db->select('event_images.*, campuses.campus_name, events.name as event_name');
        $this->ci->db->from('event_images');
        $this->ci->db->join('campuses', 'event_images.campus_id=campuses.campus_id', 'inner');
        $this->ci->db->join('events', 'event_images.event_id=events.event_id', 'left');
        if ($user['role'] !== 'Admin') {
            $access = checkUserAccess();
            $campus_ids = !empty($access[0]['campus_ids']) ? explode(',', $access[0]['campus_ids']) : array();
            if (count($campus_ids)) {
                $this->ci->db->where_in('event_images.campus_id', $campus_ids);
            }
        }
        $this->ci->db->order_by('event_images.image_id', 'DESC');
        return $this->ci->db->get()->result_array();
    }

    public function event_image_add($body)
    {
        $campus_id = isset($body['campus_id']) ? (int) $body['campus_id'] : 0;
        $event_id = isset($body['event_id']) ? (int) $body['event_id'] : 0;
        $title = isset($body['title']) ? trim($body['title']) : '';
        if (!$campus_id || !$event_id || $title === '') {
            return array('success' => false, 'message' => 'Campus, event and title required');
        }
        $url = $this->_upload('url', 'event_images');
        if ($url === false || $url === '') {
            return array('success' => false, 'message' => 'Image upload required');
        }
        $show = isset($body['show_on_website']) && (string) $body['show_on_website'] === '0' ? 0 : 1;
        $this->ci->db->insert('event_images', array(
            'campus_id' => $campus_id,
            'event_id' => $event_id,
            'show_on_website' => $show,
            'title' => $title,
            'url' => $url,
        ));
        return array('success' => true, 'image_id' => $this->ci->db->insert_id());
    }

    public function event_image_delete($id)
    {
        $this->ci->db->delete('event_images', array('image_id' => (int) $id));
        return array('success' => true);
    }

    // --- Slider images ---

    public function slider_images_list($user)
    {
        $rows = $this->ci->db->order_by('id', 'DESC')->get('slider_images')->result_array();
        return $this->_decorate_campus_rows($this->_filter_campus_list($rows, $user, 'campus_ids'), 'campus_ids');
    }

    public function slider_get($id)
    {
        $row = $this->ci->db->get_where('slider_images', array('id' => (int) $id))->row_array();
        if (!$row) {
            return null;
        }
        $row['campus_ids'] = !empty($row['campus_ids']) ? array_map('intval', explode(',', $row['campus_ids'])) : array();
        return $row;
    }

    public function slider_save($body, $id = null)
    {
        $campus_ids = $this->_campus_ids_from_body($body, 'campus_ids');
        if ($campus_ids === '') {
            return array('success' => false, 'message' => 'Campus required');
        }
        $image = $this->_upload('url', 'slider_images');
        if ($id) {
            if ($image === false) {
                return array('success' => false, 'message' => 'Image upload failed');
            }
            if ($image === '') {
                $image = isset($body['old_image']) ? $body['old_image'] : '';
            }
        } elseif ($image === false || $image === '') {
            return array('success' => false, 'message' => 'Image upload required');
        }
        $data = array('campus_ids' => $campus_ids, 'image' => $image);
        if ($id) {
            $this->ci->db->where('id', (int) $id)->update('slider_images', $data);
            return array('success' => true, 'id' => (int) $id);
        }
        $this->ci->db->insert('slider_images', $data);
        return array('success' => true, 'id' => $this->ci->db->insert_id());
    }

    public function slider_delete($id, $user)
    {
        return $this->_delete_or_unlink_campus('slider_images', 'id', (int) $id, 'campus_ids', $user);
    }

    // --- Website news (news_updates.campus_ids) ---

    public function website_news_list($user)
    {
        $this->ci->db->where('campus_ids IS NOT NULL', NULL, FALSE);
        $this->ci->db->where('campus_ids !=', '');
        $rows = $this->ci->db->order_by('news_id', 'DESC')->get('news_updates')->result_array();
        return $this->_decorate_campus_rows($this->_filter_campus_list($rows, $user, 'campus_ids'), 'campus_ids');
    }

    public function website_news_add($body)
    {
        $campus_ids = $this->_campus_ids_from_body($body, 'campus_ids');
        if ($campus_ids === '') {
            return array('success' => false, 'message' => 'Campus required');
        }
        $news = isset($body['news']) ? trim($body['news']) : '';
        if ($news === '') {
            return array('success' => false, 'message' => 'News required');
        }
        $this->ci->db->insert('news_updates', array(
            'campus_ids' => $campus_ids,
            'news' => $news,
        ));
        return array('success' => true, 'news_id' => $this->ci->db->insert_id());
    }

    public function website_news_delete($id, $user)
    {
        return $this->_delete_or_unlink_campus('news_updates', 'news_id', (int) $id, 'campus_ids', $user);
    }

    // --- FAQs ---

    public function faqs_list()
    {
        return $this->ci->db->order_by('faq_id', 'DESC')->get('faqs')->result_array();
    }

    public function faq_add($body)
    {
        $question = isset($body['question']) ? trim($body['question']) : '';
        $slug = isset($body['slug']) ? trim($body['slug']) : '';
        $answer = isset($body['answer']) ? trim($body['answer']) : '';
        if ($question === '' || $slug === '' || $answer === '') {
            return array('success' => false, 'message' => 'Question, slug and answer required');
        }
        $this->ci->db->insert('faqs', array(
            'question' => $question,
            'slug' => $slug,
            'answer' => $answer,
        ));
        return array('success' => true, 'faq_id' => $this->ci->db->insert_id());
    }

    // --- Videos ---

    public function videos_list()
    {
        $rows = $this->ci->db->order_by('video_id', 'DESC')->get('videos')->result_array();
        foreach ($rows as &$row) {
            if (!empty($row['campus_id'])) {
                $campus = $this->ci->db->get_where('campuses', array('campus_id' => $row['campus_id']))->row_array();
                $row['campus_name'] = $campus ? $campus['campus_name'] : '';
            }
        }
        return $rows;
    }

    public function video_add($body)
    {
        $campus_id = isset($body['campus_id']) ? (int) $body['campus_id'] : 0;
        $title = isset($body['title']) ? trim($body['title']) : '';
        $url = isset($body['url']) ? trim($body['url']) : '';
        if (!$campus_id || $title === '' || $url === '') {
            return array('success' => false, 'message' => 'Campus, title and URL required');
        }
        $show = isset($body['show_on_website']) && (string) $body['show_on_website'] === '0' ? 0 : 1;
        $apply = isset($body['for_apply_now']) && (string) $body['for_apply_now'] === '0' ? 0 : 1;
        $this->ci->db->insert('videos', array(
            'campus_id' => $campus_id,
            'title' => $title,
            'url' => $url,
            'show_on_website' => $show,
            'for_apply_now' => $apply,
        ));
        return array('success' => true, 'video_id' => $this->ci->db->insert_id());
    }

    public function video_delete($id)
    {
        $this->ci->db->delete('videos', array('video_id' => (int) $id));
        return array('success' => true);
    }

    // --- Home page content ---

    public function home_page_list()
    {
        $rows = $this->ci->db->order_by('website_content_id', 'DESC')->get('website_content')->result_array();
        foreach ($rows as &$row) {
            if (!empty($row['campus_id'])) {
                $campus = $this->ci->db->get_where('campuses', array('campus_id' => $row['campus_id']))->row_array();
                $row['campus_name'] = $campus ? $campus['campus_name'] : '';
            }
        }
        return $rows;
    }

    public function home_page_get($id)
    {
        return $this->ci->db->get_where('website_content', array('website_content_id' => (int) $id))->row_array();
    }

    public function home_page_save($body, $content_id = null)
    {
        $fields = array(
            'campus_id', 'point_1', 'point_1_explanation', 'point_2', 'point_2_explanation',
            'point_3', 'point_3_explanation', 'point_4', 'point_4_explanation',
            'home_right_heading', 'home_right_paragraph', 'home_left_paragraph',
        );
        $data = array();
        foreach ($fields as $f) {
            if (!isset($body[$f]) || trim((string) $body[$f]) === '') {
                return array('success' => false, 'message' => 'All required fields must be filled');
            }
            $data[$f] = trim((string) $body[$f]);
        }
        $data['campus_id'] = (int) $data['campus_id'];

        $point_center = $this->_upload('point_center_image', 'uploads');
        $home_left = $this->_upload('home_left_image', 'uploads');
        $home_right = $this->_upload('home_right_image', 'uploads');

        if ($content_id) {
            $existing = $this->home_page_get($content_id);
            if (!$existing) {
                return array('success' => false, 'message' => 'Not found');
            }
            $data['point_center_image'] = ($point_center === false || $point_center === '')
                ? (isset($body['point_center_image_old']) ? $body['point_center_image_old'] : $existing['point_center_image'])
                : $point_center;
            $data['home_left_image'] = ($home_left === false || $home_left === '')
                ? (isset($body['home_left_image_old']) ? $body['home_left_image_old'] : $existing['home_left_image'])
                : $home_left;
            $data['home_right_image'] = ($home_right === false || $home_right === '')
                ? (isset($body['home_right_image_old']) ? $body['home_right_image_old'] : $existing['home_right_image'])
                : $home_right;
            $this->ci->db->where('website_content_id', (int) $content_id)->update('website_content', $data);
            return array('success' => true, 'website_content_id' => (int) $content_id);
        }

        if ($point_center === false || $point_center === '' || $home_left === false || $home_left === '' || $home_right === false || $home_right === '') {
            return array('success' => false, 'message' => 'All three images required for new content');
        }
        $data['point_center_image'] = $point_center;
        $data['home_left_image'] = $home_left;
        $data['home_right_image'] = $home_right;

        $check = $this->ci->db->get_where('website_content', array('campus_id' => $data['campus_id']))->row_array();
        if ($check) {
            $this->ci->db->where('campus_id', $data['campus_id'])->update('website_content', $data);
            return array('success' => true, 'website_content_id' => (int) $check['website_content_id']);
        }
        $this->ci->db->insert('website_content', $data);
        return array('success' => true, 'website_content_id' => $this->ci->db->insert_id());
    }

    // --- Zoom ---

    public function zoom_list()
    {
        return $this->ci->db->order_by('zoom_id', 'ASC')->get('zoom')->result_array();
    }

    public function zoom_update($body)
    {
        $ids = isset($body['personal_meeting_ids']) ? $body['personal_meeting_ids'] : array();
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        $admission_timing = isset($body['admission_timing']) ? trim($body['admission_timing']) : '';
        if ($admission_timing === '') {
            return array('success' => false, 'message' => 'Admission timings required');
        }
        $image = $this->_upload('image', 'zoom_images');
        if ($image === false) {
            return array('success' => false, 'message' => 'Image upload failed');
        }
        $i = 1;
        foreach ($ids as $personal_meeting_id) {
            $update = array(
                'personal_meeting_id' => trim((string) $personal_meeting_id),
                'admission_timing' => $admission_timing,
            );
            if ($image !== '') {
                $update['image'] = $image;
            }
            $this->ci->db->where('zoom_id', $i)->update('zoom', $update);
            $i++;
        }
        return array('success' => true);
    }

    // --- Helpers ---

    private function _upload($field, $dir)
    {
        if (empty($_FILES[$field]['name']) || !is_uploaded_file($_FILES[$field]['tmp_name'])) {
            return '';
        }
        $this->ci->load->library('upload');
        $path = rtrim(FCPATH, '/') . '/' . trim($dir, '/') . '/';
        if (!is_dir($path)) {
            @mkdir($path, 0777, true);
        }
        $config = array('upload_path' => $path, 'allowed_types' => '*', 'encrypt_name' => false);
        $this->ci->upload->initialize($config);
        if (!$this->ci->upload->do_upload($field)) {
            return false;
        }
        $data = $this->ci->upload->data();
        return !empty($data['file_name']) ? $data['file_name'] : false;
    }

    private function _campus_ids_from_body($body, $key)
    {
        if (!isset($body[$key])) {
            return '';
        }
        $val = $body[$key];
        if (is_array($val)) {
            $val = array_filter(array_map('intval', $val));
            return implode(',', $val);
        }
        return trim((string) $val);
    }

    private function _filter_campus_list($rows, $user, $field)
    {
        if ($user['role'] === 'Admin') {
            return $rows;
        }
        $access = checkUserAccess();
        $user_campuses = !empty($access[0]['campus_ids']) ? explode(',', $access[0]['campus_ids']) : array();
        $filtered = array();
        foreach ($rows as $row) {
            $row_campuses = !empty($row[$field]) ? explode(',', $row[$field]) : array();
            if (count(array_intersect($user_campuses, $row_campuses)) > 0) {
                $filtered[] = $row;
            }
        }
        return $filtered;
    }

    private function _decorate_campus_rows($rows, $field)
    {
        $campus_map = array();
        $all = $this->ci->db->get('campuses')->result_array();
        foreach ($all as $c) {
            $campus_map[$c['campus_id']] = $c['campus_name'];
        }
        foreach ($rows as &$row) {
            $ids = !empty($row[$field]) ? explode(',', $row[$field]) : array();
            $names = array();
            foreach ($ids as $id) {
                if (isset($campus_map[$id])) {
                    $names[] = $campus_map[$id];
                }
            }
            $row['campus_names'] = $names;
        }
        return $rows;
    }

    private function _delete_or_unlink_campus($table, $id_field, $id, $campus_field, $user)
    {
        $row = $this->ci->db->get_where($table, array($id_field => $id))->row_array();
        if (!$row) {
            return array('success' => false, 'message' => 'Not found');
        }
        if ($user['role'] !== 'Admin') {
            $access = checkUserAccess();
            $campus_ids = !empty($access[0]['campus_ids']) ? explode(',', $access[0]['campus_ids']) : array();
            $item_campuses = !empty($row[$campus_field]) ? explode(',', $row[$campus_field]) : array();
            foreach ($campus_ids as $campus_id) {
                if (($key = array_search($campus_id, $item_campuses)) !== false) {
                    unset($item_campuses[$key]);
                }
            }
            $this->ci->db->set($campus_field, implode(',', $item_campuses));
            $this->ci->db->where($id_field, $id);
            $this->ci->db->update($table);
            return array('success' => true);
        }
        $this->ci->db->delete($table, array($id_field => $id));
        return array('success' => true);
    }
}
