<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Complaints_service {

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

    public function list_by_status($status)
    {
        $this->ci->db->select('complaints.*, students.first_name, students.last_name, students.roll_no, complaint_types.complaint_type');
        $this->ci->db->from('complaints');
        $this->ci->db->join('complaint_types', 'complaint_types.complaint_type_id=complaints.complaint_type_id', 'inner');
        $this->ci->db->join('students', 'students.student_id=complaints.student_id', 'inner');
        $this->ci->db->where('complaints.complaint_status', (int) $status);
        $this->ci->db->order_by('complaints.complaint_id', 'DESC');
        return $this->ci->db->get()->result_array();
    }

    public function get_detail($complaint_id)
    {
        $complaint = $this->ci->db->get_where('complaints', array('complaint_id' => (int) $complaint_id))->row_array();
        if (!$complaint) {
            return null;
        }
        $this->ci->db->select('complaint_chats.*, users.first_name as user_first_name, users.last_name as user_last_name');
        $this->ci->db->from('complaint_chats');
        $this->ci->db->join('users', 'users.user_id=complaint_chats.user_id', 'left');
        $this->ci->db->where('complaint_chats.complaint_id', (int) $complaint_id);
        $this->ci->db->order_by('complaint_chats.created_at', 'ASC');
        $chats = $this->ci->db->get()->result_array();
        $student_id = (int) $complaint['student_id'];
        $student = $this->ci->db->get_where('students', array('student_id' => $student_id))->row_array();
        $photo = $this->ci->db->get_where('student_documents', array('student_id' => $student_id, 'type' => 'Photo'))->row_array();
        $student_photo = '';
        if ($photo) {
            $student_photo = !empty($photo['online_image']) ? $photo['online_image'] : rtrim(base_url(), '/') . '/uploads/' . $photo['image'];
        }
        $type = $this->ci->db->get_where('complaint_types', array('complaint_type_id' => $complaint['complaint_type_id']))->row_array();
        return array(
            'complaint' => $complaint,
            'chats' => $chats,
            'student' => $student,
            'student_photo' => $student_photo,
            'complaint_type' => $type ? $type['complaint_type'] : '',
        );
    }

    public function reply($complaint_id, $message, $user_id)
    {
        $message = trim($message);
        if ($message === '') {
            return array('success' => false, 'message' => 'Message required');
        }
        if (!$this->ci->db->get_where('complaints', array('complaint_id' => (int) $complaint_id))->row_array()) {
            return array('success' => false, 'message' => 'Complaint not found');
        }
        $this->ci->db->insert('complaint_chats', array(
            'user_id' => (int) $user_id,
            'message' => $message,
            'complaint_id' => (int) $complaint_id,
            'created_at' => date('Y-m-d H:i:s'),
        ));
        return array('success' => true);
    }

    public function mark_solved($complaint_id)
    {
        $this->ci->db->where('complaint_id', (int) $complaint_id)->update('complaints', array('complaint_status' => 1));
        return array('success' => true);
    }
}
