<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mobile_app_api_service {

    /** @var CI_Controller */
    private $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
    }

    public function can_manage($user)
    {
        return $user && $user['role'] === 'Admin';
    }

    public function campuses_list()
    {
        return $this->ci->db->order_by('campus_name', 'ASC')->get('campuses')->result_array();
    }

    public function campus_get($campus_id)
    {
        $campus = $this->ci->db->get_where('campuses', array('campus_id' => (int) $campus_id))->row_array();
        if (!$campus) {
            return null;
        }
        $this->ci->db->select('users_phones.phone');
        $this->ci->db->from('users');
        $this->ci->db->join('designations', 'designations.designation_id=users.designation_id', 'inner');
        $this->ci->db->join('users_phones', 'users_phones.user_id=users.user_id', 'inner');
        $this->ci->db->where(array('users.campus_id' => (int) $campus_id, 'designations.designation_name' => 'Receptionist'));
        $rec = $this->ci->db->get()->row_array();
        $campus['receptionist_phone'] = $rec ? $rec['phone'] : '';
        $campus['designation_ids'] = !empty($campus['designation_ids']) ? explode(',', $campus['designation_ids']) : array();
        $campus['whatsapp_number'] = preg_replace('/^https:\\/\\/wa\\.me\\//', '', (string) $campus['whatsapp']);
        return $campus;
    }

    public function campus_update($campus_id, $body, $files = array())
    {
        $campus = $this->campus_get($campus_id);
        if (!$campus) {
            return array('success' => false, 'message' => 'Campus not found');
        }
        $image = $this->_upload('campus_image', 'uploads/');
        if ($image === false) {
            return array('success' => false, 'message' => 'Image upload failed');
        }
        $campus_image = $image !== '' ? $image : (isset($body['old_campus_image']) ? $body['old_campus_image'] : $campus['campus_image']);
        $ids = array();
        if (isset($body['designation_ids']) && is_array($body['designation_ids'])) {
            $ids = $body['designation_ids'];
        } elseif (isset($body['designation_id']) && is_array($body['designation_id'])) {
            $ids = $body['designation_id'];
        }
        $designations = count($ids) ? implode(',', $ids) : '';
        $whatsapp = isset($body['whatsapp']) ? trim($body['whatsapp']) : '';
        if ($whatsapp !== '' && strpos($whatsapp, 'https://') !== 0) {
            $whatsapp = 'https://wa.me/' . preg_replace('/\\D+/', '', $whatsapp);
        }
        $update = array(
            'campus_image' => $campus_image,
            'mobile_status' => !empty($body['mobile_status']) ? 1 : 0,
            'google_map_link' => isset($body['google_map_link']) ? trim($body['google_map_link']) : '',
            'facebook' => isset($body['facebook']) ? trim($body['facebook']) : '',
            'twitter' => isset($body['twitter']) ? trim($body['twitter']) : '',
            'whatsapp' => $whatsapp,
            'content' => isset($body['content']) ? $body['content'] : '',
            'designation_ids' => $designations,
        );
        $this->ci->db->where('campus_id', (int) $campus_id)->update('campuses', $update);
        if ($image !== '') {
            $this->_sync_campus_image($campus_id);
        }
        return array('success' => true);
    }

    public function courses_list()
    {
        return $this->ci->db->order_by('course_name', 'ASC')->get('courses')->result_array();
    }

    public function course_get($course_id)
    {
        return $this->ci->db->get_where('courses', array('course_id' => (int) $course_id))->row_array();
    }

    public function course_update($course_id, $body)
    {
        if (!$this->course_get($course_id)) {
            return array('success' => false, 'message' => 'Course not found');
        }
        $this->ci->db->where('course_id', (int) $course_id)->update('courses', array(
            'content' => isset($body['content']) ? $body['content'] : '',
            'mobile_status' => !empty($body['mobile_status']) ? 1 : 0,
        ));
        return array('success' => true);
    }

    public function images_list()
    {
        return $this->ci->db->order_by('id', 'DESC')->get('mobile_advertisement')->result_array();
    }

    public function image_add($body)
    {
        $picture = $this->_upload('picture', 'uploads/');
        if ($picture === false) {
            return array('success' => false, 'message' => 'Picture upload failed');
        }
        $row = array(
            'title' => isset($body['title']) ? trim($body['title']) : '',
            'description' => isset($body['description']) ? $body['description'] : '',
            'type' => isset($body['type']) ? trim($body['type']) : '',
            'file' => $picture,
        );
        if (!empty($body['expire']) && !empty($body['expire_date'])) {
            $row['expire_date'] = $body['expire_date'];
        }
        $this->ci->db->insert('mobile_advertisement', $row);
        $id = $this->ci->db->insert_id();
        $this->_sync_ad_image($id);
        return array('success' => true, 'id' => $id);
    }

    public function image_delete($id)
    {
        $this->ci->db->delete('mobile_advertisement', array('id' => (int) $id));
        return array('success' => true);
    }

    public function news_list()
    {
        $rows = $this->ci->db->order_by('news_id', 'DESC')->get('news_updates')->result_array();
        foreach ($rows as &$row) {
            $row['course_ids'] = !empty($row['course_ids']) ? explode(',', $row['course_ids']) : array();
        }
        return $rows;
    }

    public function news_get($news_id)
    {
        $row = $this->ci->db->get_where('news_updates', array('news_id' => (int) $news_id))->row_array();
        if ($row) {
            $row['course_ids'] = !empty($row['course_ids']) ? explode(',', $row['course_ids']) : array();
        }
        return $row;
    }

    public function news_save($body, $news_id = null)
    {
        $course_ids = isset($body['course_ids']) && is_array($body['course_ids']) ? implode(',', $body['course_ids']) : '';
        $news = isset($body['news']) ? trim($body['news']) : '';
        if ($news === '') {
            return array('success' => false, 'message' => 'News text required');
        }
        $data = array('course_ids' => $course_ids, 'news' => $news);
        if ($news_id) {
            $this->ci->db->where('news_id', (int) $news_id)->update('news_updates', $data);
            return array('success' => true, 'news_id' => (int) $news_id);
        }
        $this->ci->db->insert('news_updates', $data);
        return array('success' => true, 'news_id' => $this->ci->db->insert_id());
    }

    public function news_delete($news_id)
    {
        $this->ci->db->delete('news_updates', array('news_id' => (int) $news_id));
        return array('success' => true);
    }

    public function complaint_types_list()
    {
        return $this->ci->db->order_by('complaint_type_id', 'ASC')->get('complaint_types')->result_array();
    }

    public function complaint_type_save($body, $id = null)
    {
        $name = isset($body['complaint_type']) ? trim($body['complaint_type']) : '';
        if ($name === '') {
            return array('success' => false, 'message' => 'Name required');
        }
        $data = array(
            'complaint_type' => $name,
            'status' => isset($body['status']) ? (int) $body['status'] : 1,
        );
        if ($id) {
            $this->ci->db->where('complaint_type_id', (int) $id)->update('complaint_types', $data);
            return array('success' => true, 'complaint_type_id' => (int) $id);
        }
        $this->ci->db->insert('complaint_types', $data);
        return array('success' => true, 'complaint_type_id' => $this->ci->db->insert_id());
    }

    public function complaint_type_delete($id)
    {
        $this->ci->db->delete('complaint_types', array('complaint_type_id' => (int) $id));
        return array('success' => true);
    }

    public function required_careers_list()
    {
        return $this->ci->db->order_by('required_career_id', 'ASC')->get('required_career')->result_array();
    }

    public function required_career_save($body, $id = null)
    {
        $name = isset($body['required_career']) ? trim($body['required_career']) : '';
        if ($name === '') {
            return array('success' => false, 'message' => 'Name required');
        }
        $data = array(
            'required_career' => $name,
            'status' => isset($body['status']) ? (int) $body['status'] : 1,
        );
        if ($id) {
            $this->ci->db->where('required_career_id', (int) $id)->update('required_career', $data);
            return array('success' => true, 'required_career_id' => (int) $id);
        }
        $this->ci->db->insert('required_career', $data);
        return array('success' => true, 'required_career_id' => $this->ci->db->insert_id());
    }

    public function required_career_delete($id)
    {
        $this->ci->db->delete('required_career', array('required_career_id' => (int) $id));
        return array('success' => true);
    }

    public function designations_list()
    {
        return $this->ci->db->order_by('designation_name', 'ASC')->get('designations')->result_array();
    }

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

    private function _sync_campus_image($campus_id)
    {
        if (!function_exists('curl_init')) {
            return;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.shahbazcollegeofpharmacy.edu.pk/s3/upload_campus_image.php');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'campus_id=' . (int) $campus_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }

    private function _sync_ad_image($id)
    {
        if (!function_exists('curl_init')) {
            return;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.shahbazcollegeofpharmacy.edu.pk/s3/upload_mobile_advertisement_image.php');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'id=' . (int) $id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }
}
