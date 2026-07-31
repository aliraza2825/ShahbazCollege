<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Campus_service {

    /** @var CI_Controller */
    private $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
    }

    public function can_manage($user)
    {
        if (!$user) {
            return false;
        }
        if ($user['role'] === 'Admin') {
            return true;
        }
        $access = $this->ci->db->get_where('access', array('user_id' => $user['user_id']))->row_array();
        return !empty($access['campuses']);
    }

    private function upload_path()
    {
        return FCPATH . 'uploads/';
    }

    private function upload_file($field)
    {
        if (empty($_FILES[$field]['name']) || !is_uploaded_file($_FILES[$field]['tmp_name'])) {
            return '';
        }
        $this->ci->load->library('upload');
        $config = array(
            'upload_path' => $this->upload_path(),
            'allowed_types' => 'gif|jpg|jpeg|png|webp',
            'encrypt_name' => false,
        );
        $this->ci->upload->initialize($config);
        if (!$this->ci->upload->do_upload($field)) {
            return '';
        }
        $data = $this->ci->upload->data();
        return !empty($data['file_name']) ? $data['file_name'] : '';
    }

    private function uploads_base_url()
    {
        return rtrim(base_url(), '/') . '/uploads/';
    }

    private function enrich_row($row)
    {
        if (!$row) {
            return $row;
        }
        foreach (array('logo', 'stamp', 'head_stamp') as $img) {
            if (!empty($row[$img])) {
                $row[$img . '_url'] = $this->uploads_base_url() . $row[$img];
            } else {
                $row[$img . '_url'] = '';
            }
        }
        $row['for_mobile_application'] = !empty($row['for_mobile_application']) ? 1 : 0;
        return $row;
    }

    public function list_active()
    {
        $rows = $this->ci->db
            ->where('status', 1)
            ->order_by('campus_name', 'ASC')
            ->get('campuses')
            ->result_array();
        return array_map(array($this, 'enrich_row'), $rows);
    }

    public function get($campus_id)
    {
        $row = $this->ci->db->get_where('campuses', array('campus_id' => (int) $campus_id))->row_array();
        if (!$row) {
            return null;
        }
        return $this->enrich_row($row);
    }

    private function read_fields($post, $is_update = false)
    {
        $fields = array(
            'campus_code' => isset($post['campus_code']) ? trim($post['campus_code']) : '',
            'campus_name' => isset($post['campus_name']) ? trim($post['campus_name']) : '',
            'roll_no_code' => isset($post['roll_no_code']) ? trim($post['roll_no_code']) : '',
            'website' => isset($post['website']) ? trim($post['website']) : '',
            'address' => isset($post['address']) ? trim($post['address']) : '',
            'sms' => isset($post['sms']) ? trim($post['sms']) : '',
            'facebook_api' => isset($post['facebook_api']) ? trim($post['facebook_api']) : '',
            'phone' => isset($post['phone']) ? trim($post['phone']) : '',
            'phone1' => isset($post['phone1']) ? trim($post['phone1']) : '',
            'phone2' => isset($post['phone2']) ? trim($post['phone2']) : '',
            'phone3' => isset($post['phone3']) ? trim($post['phone3']) : '',
            'phone4' => isset($post['phone4']) ? trim($post['phone4']) : '',
            'phone5' => isset($post['phone5']) ? trim($post['phone5']) : '',
            'phone6' => isset($post['phone6']) ? trim($post['phone6']) : '',
            'phone7' => isset($post['phone7']) ? trim($post['phone7']) : '',
            'bank_name' => isset($post['bank_name']) ? trim($post['bank_name']) : '',
            'account_no' => isset($post['account_no']) ? trim($post['account_no']) : '',
            'note' => isset($post['note']) ? trim($post['note']) : '',
            'email' => isset($post['email']) ? trim($post['email']) : '',
            'for_mobile_application' => !empty($post['for_mobile_application']) ? 1 : 0,
        );

        if ($fields['campus_name'] === '' || $fields['roll_no_code'] === '' || $fields['website'] === '' || $fields['address'] === '' || $fields['sms'] === '') {
            return array('success' => false, 'message' => 'Required fields missing');
        }
        if (!$is_update && $fields['campus_code'] === '') {
            return array('success' => false, 'message' => 'Campus code is required');
        }

        return array('success' => true, 'fields' => $fields);
    }

    public function create_from_request($post)
    {
        $parsed = $this->read_fields($post, false);
        if (empty($parsed['success'])) {
            return $parsed;
        }
        $fields = $parsed['fields'];

        $logo = $this->upload_file('logo');
        $stamp = $this->upload_file('stamp');
        $head_stamp = $this->upload_file('head_stamp');

        if ($logo === '' || $stamp === '' || $head_stamp === '') {
            return array('success' => false, 'message' => 'Logo, stamp and head stamp images are required');
        }

        $this->ci->db->set('logo', $logo);
        $this->ci->db->set('stamp', $stamp);
        $this->ci->db->set('head_stamp', $head_stamp);
        $this->ci->db->set('campus_code', $fields['campus_code']);
        $this->ci->db->set('status', 1);
        foreach ($fields as $k => $v) {
            if ($k === 'campus_code') {
                continue;
            }
            $this->ci->db->set($k, $v);
        }
        $this->ci->db->insert('campuses');
        $id = (int) $this->ci->db->insert_id();

        return array('success' => true, 'message' => 'Campus has been added.', 'campus_id' => $id, 'data' => $this->get($id));
    }

    public function update_from_request($campus_id, $post)
    {
        $campus_id = (int) $campus_id;
        $existing = $this->get($campus_id);
        if (!$existing) {
            return array('success' => false, 'message' => 'Campus not found');
        }

        $parsed = $this->read_fields($post, true);
        if (empty($parsed['success'])) {
            return $parsed;
        }
        $fields = $parsed['fields'];

        $logo = $this->upload_file('logo');
        $stamp = $this->upload_file('stamp');
        $head_stamp = $this->upload_file('head_stamp');

        if ($logo === '') {
            $logo = isset($post['old_logo']) ? $post['old_logo'] : $existing['logo'];
        }
        if ($stamp === '') {
            $stamp = isset($post['old_stamp']) ? $post['old_stamp'] : $existing['stamp'];
        }
        if ($head_stamp === '') {
            $head_stamp = isset($post['old_head_stamp']) ? $post['old_head_stamp'] : $existing['head_stamp'];
        }

        $this->ci->db->set('logo', $logo);
        $this->ci->db->set('stamp', $stamp);
        $this->ci->db->set('head_stamp', $head_stamp);
        foreach ($fields as $k => $v) {
            $this->ci->db->set($k, $v);
        }
        $this->ci->db->where('campus_id', $campus_id);
        $this->ci->db->update('campuses');

        return array('success' => true, 'message' => 'Campus has been updated.', 'data' => $this->get($campus_id));
    }

    public function soft_delete($campus_id)
    {
        $campus_id = (int) $campus_id;
        if (!$this->get($campus_id)) {
            return array('success' => false, 'message' => 'Campus not found');
        }
        $this->ci->db->set('status', 0);
        $this->ci->db->where('campus_id', $campus_id);
        $this->ci->db->update('campuses');
        return array('success' => true, 'message' => 'Campus has been deleted.');
    }

    private function decode_json_array($value)
    {
        if (is_array($value)) {
            return $value;
        }
        if ($value === '' || $value === null) {
            return array();
        }
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : array();
    }

    private function enrich_partner_row($row)
    {
        if (!$row) {
            return $row;
        }
        $flat = $this->decode_json_array(isset($row['partners']) ? $row['partners'] : array());
        $partners = array();
        for ($i = 0; $i < count($flat); $i += 2) {
            if (!isset($flat[$i + 1])) {
                break;
            }
            $uid = (int) $flat[$i];
            $user = $this->ci->db
                ->select('first_name, last_name')
                ->get_where('users', array('user_id' => $uid))
                ->row_array();
            $partners[] = array(
                'user_id' => $uid,
                'percentage' => $flat[$i + 1],
                'user_name' => $user ? trim($user['first_name'] . ' ' . $user['last_name']) : '',
            );
        }
        $share_ids = $this->decode_json_array(isset($row['campus_share_ids']) ? $row['campus_share_ids'] : array());
        $seats = $this->decode_json_array(isset($row['no_of_seats']) ? $row['no_of_seats'] : array());
        $shares = array();
        for ($i = 0; $i < count($share_ids); $i++) {
            $shares[] = array(
                'campus_id' => (int) $share_ids[$i],
                'no_of_seats' => isset($seats[$i]) ? $seats[$i] : '',
            );
        }
        $row['partners_list'] = $partners;
        $row['campus_shares'] = $shares;
        $row['special_expense_ids'] = array_map('intval', $this->decode_json_array(isset($row['special_expense_ids']) ? $row['special_expense_ids'] : array()));
        return $row;
    }

    public function profit_meta()
    {
        $campuses = $this->ci->db->order_by('campus_name', 'ASC')->get('campuses')->result_array();
        $users = $this->ci->db
            ->select('user_id, first_name, last_name')
            ->order_by('first_name', 'ASC')
            ->get('users')
            ->result_array();
        $expense_categories = $this->ci->db->order_by('name', 'ASC')->get('expense_category')->result_array();
        return array(
            'campuses' => $campuses,
            'users' => $users,
            'expense_categories' => $expense_categories,
        );
    }

    public function list_partners()
    {
        $this->ci->db->select('campus_partners.*, campuses.campus_name');
        $this->ci->db->from('campus_partners');
        $this->ci->db->join('campuses', 'campus_partners.campus_id = campuses.campus_id', 'inner');
        $this->ci->db->order_by('campuses.campus_name', 'ASC');
        $rows = $this->ci->db->get()->result_array();
        return array_map(array($this, 'enrich_partner_row'), $rows);
    }

    public function get_partner($campus_partner_id)
    {
        $this->ci->db->select('campus_partners.*, campuses.campus_name');
        $this->ci->db->from('campus_partners');
        $this->ci->db->join('campuses', 'campus_partners.campus_id = campuses.campus_id', 'inner');
        $this->ci->db->where('campus_partners.campus_partner_id', (int) $campus_partner_id);
        $row = $this->ci->db->get()->row_array();
        if (!$row) {
            return null;
        }
        return $this->enrich_partner_row($row);
    }

    private function parse_partner_body($body, $is_update = false, $campus_partner_id = 0)
    {
        $campus_id = (int) (isset($body['campus_id']) ? $body['campus_id'] : 0);
        if ($campus_id <= 0) {
            return array('success' => false, 'message' => 'Campus is required');
        }

        $partners_in = isset($body['partners']) && is_array($body['partners']) ? $body['partners'] : array();
        if (!count($partners_in)) {
            return array('success' => false, 'message' => 'At least one partner is required');
        }

        $flat = array();
        foreach ($partners_in as $p) {
            $uid = (int) (isset($p['user_id']) ? $p['user_id'] : 0);
            $pct = isset($p['percentage']) ? trim((string) $p['percentage']) : '';
            if ($uid <= 0 || $pct === '') {
                return array('success' => false, 'message' => 'Each partner needs user and percentage');
            }
            $flat[] = $uid;
            $flat[] = $pct;
        }

        $shares_in = isset($body['campus_shares']) && is_array($body['campus_shares']) ? $body['campus_shares'] : array();
        $share_ids = array();
        $seats = array();
        foreach ($shares_in as $s) {
            $cid = (int) (isset($s['campus_id']) ? $s['campus_id'] : 0);
            $seat = isset($s['no_of_seats']) ? trim((string) $s['no_of_seats']) : '';
            if ($cid <= 0 || $seat === '') {
                return array('success' => false, 'message' => 'Each sharing campus needs campus and seats');
            }
            $share_ids[] = $cid;
            $seats[] = $seat;
        }

        $special = isset($body['special_expense_ids']) && is_array($body['special_expense_ids'])
            ? array_values(array_filter(array_map('intval', $body['special_expense_ids'])))
            : array();

        if (!$is_update) {
            $exists = $this->ci->db->get_where('campus_partners', array('campus_id' => $campus_id))->row_array();
            if ($exists) {
                return array('success' => false, 'message' => 'Record already exists for this campus');
            }
        } else {
            $this->ci->db->where('campus_id', $campus_id);
            $this->ci->db->where('campus_partner_id !=', (int) $campus_partner_id);
            $dup = $this->ci->db->get('campus_partners')->row_array();
            if ($dup) {
                return array('success' => false, 'message' => 'Another record already uses this campus');
            }
        }

        return array(
            'success' => true,
            'data' => array(
                'campus_id' => $campus_id,
                'partners' => json_encode($flat),
                'campus_share_ids' => json_encode($share_ids),
                'no_of_seats' => json_encode($seats),
                'special_expense_ids' => json_encode($special),
            ),
        );
    }

    public function create_partner($body)
    {
        $parsed = $this->parse_partner_body($body, false);
        if (empty($parsed['success'])) {
            return $parsed;
        }
        $this->ci->db->insert('campus_partners', $parsed['data']);
        $id = (int) $this->ci->db->insert_id();
        return array('success' => true, 'message' => 'Added successfully.', 'campus_partner_id' => $id, 'data' => $this->get_partner($id));
    }

    public function update_partner($campus_partner_id, $body)
    {
        $campus_partner_id = (int) $campus_partner_id;
        if (!$this->get_partner($campus_partner_id)) {
            return array('success' => false, 'message' => 'Partner record not found');
        }
        $parsed = $this->parse_partner_body($body, true, $campus_partner_id);
        if (empty($parsed['success'])) {
            return $parsed;
        }
        $this->ci->db->where('campus_partner_id', $campus_partner_id);
        $this->ci->db->update('campus_partners', $parsed['data']);
        return array('success' => true, 'message' => 'Updated successfully.', 'data' => $this->get_partner($campus_partner_id));
    }

    public function delete_partner($campus_partner_id)
    {
        $campus_partner_id = (int) $campus_partner_id;
        if (!$this->get_partner($campus_partner_id)) {
            return array('success' => false, 'message' => 'Partner record not found');
        }
        $this->ci->db->where('campus_partner_id', $campus_partner_id);
        $this->ci->db->delete('campus_partners');
        return array('success' => true, 'message' => 'Deleted successfully.');
    }

    public function list_rooms($campus_id = 0)
    {
        $this->ci->db->select('rooms.*, campuses.campus_name');
        $this->ci->db->from('rooms');
        $this->ci->db->join('campuses', 'campuses.campus_id = rooms.campus_id', 'inner');
        if ((int) $campus_id > 0) {
            $this->ci->db->where('rooms.campus_id', (int) $campus_id);
        }
        $this->ci->db->order_by('campuses.campus_name', 'ASC');
        $this->ci->db->order_by('rooms.room_name', 'ASC');
        return $this->ci->db->get()->result_array();
    }

    public function get_room($room_id)
    {
        $this->ci->db->select('rooms.*, campuses.campus_name');
        $this->ci->db->from('rooms');
        $this->ci->db->join('campuses', 'campuses.campus_id = rooms.campus_id', 'inner');
        $this->ci->db->where('rooms.room_id', (int) $room_id);
        return $this->ci->db->get()->row_array();
    }

    public function save_rooms($body)
    {
        $room_id = (int) (isset($body['room_id']) ? $body['room_id'] : 0);
        $campus_id = (int) (isset($body['campus_id']) ? $body['campus_id'] : 0);
        $type = isset($body['type']) ? (int) $body['type'] : 0;

        if ($room_id > 0) {
            $name = isset($body['room_name']) ? trim($body['room_name']) : '';
            $room_no = isset($body['room_no']) ? trim($body['room_no']) : '';
            if ($campus_id <= 0 || $name === '' || $room_no === '') {
                return array('success' => false, 'message' => 'Campus, room name and room no are required');
            }
            $this->ci->db->where('room_id', $room_id)->update('rooms', array(
                'campus_id' => $campus_id,
                'room_name' => $name,
                'room_no' => $room_no,
                'type' => $type,
            ));
            return array('success' => true, 'message' => 'Room updated.', 'data' => $this->get_room($room_id));
        }

        if ($campus_id <= 0) {
            return array('success' => false, 'message' => 'Campus is required');
        }

        $items = isset($body['rooms']) && is_array($body['rooms']) ? $body['rooms'] : array();
        if (!count($items)) {
            $name = isset($body['room_name']) ? trim($body['room_name']) : '';
            $room_no = isset($body['room_no']) ? trim($body['room_no']) : '';
            if ($name === '' || $room_no === '') {
                return array('success' => false, 'message' => 'At least one room is required');
            }
            $items = array(array('room_name' => $name, 'room_no' => $room_no));
        }

        $inserted = 0;
        foreach ($items as $item) {
            $name = isset($item['room_name']) ? trim($item['room_name']) : '';
            $room_no = isset($item['room_no']) ? trim($item['room_no']) : '';
            if ($name === '' || $room_no === '') {
                continue;
            }
            $this->ci->db->insert('rooms', array(
                'campus_id' => $campus_id,
                'room_name' => $name,
                'room_no' => $room_no,
                'type' => $type,
            ));
            $inserted++;
        }
        if ($inserted === 0) {
            return array('success' => false, 'message' => 'Valid room name and number required');
        }
        return array('success' => true, 'message' => $inserted === 1 ? 'Room added.' : 'Rooms added.', 'count' => $inserted);
    }

    public function delete_room($room_id)
    {
        $room_id = (int) $room_id;
        if (!$this->get_room($room_id)) {
            return array('success' => false, 'message' => 'Room not found');
        }
        $this->ci->db->where('room_id', $room_id)->delete('rooms');
        return array('success' => true, 'message' => 'Room deleted.');
    }

    public function list_subrooms($campus_id = 0, $room_id = 0)
    {
        $this->ci->db->select('subrooms.*, rooms.room_name, rooms.room_no, rooms.campus_id, campuses.campus_name');
        $this->ci->db->from('subrooms');
        $this->ci->db->join('rooms', 'rooms.room_id = subrooms.room_id', 'inner');
        $this->ci->db->join('campuses', 'campuses.campus_id = rooms.campus_id', 'inner');
        if ((int) $room_id > 0) {
            $this->ci->db->where('subrooms.room_id', (int) $room_id);
        }
        if ((int) $campus_id > 0) {
            $this->ci->db->where('rooms.campus_id', (int) $campus_id);
        }
        $this->ci->db->order_by('campuses.campus_name', 'ASC');
        $this->ci->db->order_by('rooms.room_name', 'ASC');
        $this->ci->db->order_by('subrooms.subroom_name', 'ASC');
        return $this->ci->db->get()->result_array();
    }

    public function save_subroom($body)
    {
        $id = (int) (isset($body['subroom_id']) ? $body['subroom_id'] : 0);
        $room_id = (int) (isset($body['room_id']) ? $body['room_id'] : 0);
        $name = isset($body['subroom_name']) ? trim($body['subroom_name']) : '';
        if ($room_id <= 0 || $name === '') {
            return array('success' => false, 'message' => 'Room and sub-room name are required');
        }
        if (!$this->get_room($room_id)) {
            return array('success' => false, 'message' => 'Room not found');
        }
        if ($id > 0) {
            $this->ci->db->where('subroom_id', $id)->update('subrooms', array(
                'room_id' => $room_id,
                'subroom_name' => $name,
            ));
        } else {
            $this->ci->db->insert('subrooms', array('room_id' => $room_id, 'subroom_name' => $name));
            $id = (int) $this->ci->db->insert_id();
        }
        return array('success' => true, 'message' => $id ? 'Sub-room saved.' : 'Sub-room saved.', 'subroom_id' => $id);
    }

    public function delete_subroom($subroom_id)
    {
        $subroom_id = (int) $subroom_id;
        $row = $this->ci->db->get_where('subrooms', array('subroom_id' => $subroom_id))->row_array();
        if (!$row) {
            return array('success' => false, 'message' => 'Sub-room not found');
        }
        $this->ci->db->where('subroom_id', $subroom_id)->delete('subrooms');
        return array('success' => true, 'message' => 'Sub-room deleted.');
    }
}
