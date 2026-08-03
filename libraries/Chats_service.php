<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chats_service {

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

    public function list_by_status($status, $page = 1, $page_size = 25)
    {
        $page = max(1, (int) $page);
        $page_size = (int) $page_size;
        if ($page_size <= 0) {
            $page_size = 25;
        }
        if ($page_size > 5000) {
            $page_size = 5000;
        }
        $offset = ($page - 1) * $page_size;

        $this->ci->db->from('chats');
        $this->ci->db->where('chats.chat_status', (int) $status);
        $total = (int) $this->ci->db->count_all_results();

        $this->ci->db->select('chats.*, students.first_name, students.last_name, students.roll_no');
        $this->ci->db->from('chats');
        $this->ci->db->join('students', 'students.student_id=chats.student_id', 'left');
        $this->ci->db->where('chats.chat_status', (int) $status);
        $this->ci->db->order_by('chats.chat_id', 'DESC');
        $this->ci->db->limit($page_size, $offset);
        $rows = $this->ci->db->get()->result_array();

        return array(
            'rows' => $rows,
            'pagination' => array(
                'page' => $page,
                'page_size' => $page_size,
                'total' => $total,
                'total_pages' => max(1, (int) ceil($total / $page_size)),
            ),
        );
    }

    public function get_detail($chat_id)
    {
        $chat = $this->ci->db->get_where('chats', array('chat_id' => (int) $chat_id))->row_array();
        if (!$chat) {
            return null;
        }

        $this->ci->db->select('chat_history.*, users.first_name as user_first_name, users.last_name as user_last_name');
        $this->ci->db->from('chat_history');
        $this->ci->db->join('users', 'users.user_id=chat_history.user_id', 'left');
        $this->ci->db->where('chat_history.chat_id', (int) $chat_id);
        $this->ci->db->order_by('chat_history.created_at', 'ASC');
        $messages = $this->ci->db->get()->result_array();

        $student = null;
        $student_photo = 'https://i.pinimg.com/474x/0c/3b/3a/0c3b3adb1a7530892e55ef36d3be6cb8.jpg';
        $student_id = (int) $chat['student_id'];
        if ($student_id > 0) {
            $student = $this->ci->db->get_where('students', array('student_id' => $student_id))->row_array();
            $photo = $this->ci->db->get_where('student_documents', array('student_id' => $student_id, 'type' => 'Photo'))->row_array();
            if ($photo) {
                $student_photo = !empty($photo['online_image'])
                    ? $photo['online_image']
                    : rtrim(base_url(), '/') . '/uploads/' . $photo['image'];
            }
        }

        $question = null;
        if (!empty($chat['question_id'])) {
            $question = $this->ci->db->get_where('questions', array('question_id' => (int) $chat['question_id']))->row_array();
        }

        return array(
            'chat' => $chat,
            'messages' => $messages,
            'student' => $student,
            'student_photo' => $student_photo,
            'question' => $question,
        );
    }

    public function reply($chat_id, $message, $user_id)
    {
        $message = trim($message);
        if ($message === '') {
            return array('success' => false, 'message' => 'Message required');
        }
        if (!$this->ci->db->get_where('chats', array('chat_id' => (int) $chat_id))->row_array()) {
            return array('success' => false, 'message' => 'Chat not found');
        }
        $this->ci->db->insert('chat_history', array(
            'user_id' => (int) $user_id,
            'message' => $message,
            'chat_id' => (int) $chat_id,
            'created_at' => date('Y-m-d H:i:s'),
        ));
        return array('success' => true);
    }

    public function mark_closed($chat_id)
    {
        $this->ci->db->where('chat_id', (int) $chat_id)->update('chats', array('chat_status' => 1));
        return array('success' => true);
    }
}
