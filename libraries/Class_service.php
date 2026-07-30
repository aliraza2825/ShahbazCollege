<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Class_service {

    /** @var CI_Controller */
    private $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
    }

    private function access_row($user)
    {
        if (!$user) {
            return null;
        }
        return $this->ci->db->get_where('access', array('user_id' => $user['user_id']))->row_array();
    }

    public function is_admin($user)
    {
        return $user && $user['role'] === 'Admin';
    }

    public function allowed_class_ids($user)
    {
        if ($this->is_admin($user)) {
            return null;
        }
        $access = $this->access_row($user);
        if (!$access || empty($access['class_ids'])) {
            return array();
        }
        $ids = array();
        foreach (explode(',', $access['class_ids']) as $id) {
            $id = (int) trim($id);
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        return $ids;
    }

    public function allowed_campus_ids($user)
    {
        if ($this->is_admin($user)) {
            return null;
        }
        $access = $this->access_row($user);
        if (!$access || empty($access['campus_ids'])) {
            return array();
        }
        $ids = array();
        foreach (explode(',', $access['campus_ids']) as $id) {
            $id = (int) trim($id);
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        return $ids;
    }

    public function can_manage($user)
    {
        if (!$user) {
            return false;
        }
        if ($this->is_admin($user)) {
            return true;
        }
        $access = $this->access_row($user);
        return !empty($access['class_edit']) || !empty($access['class_delete']) || !empty($access['class_ids']);
    }

    private function apply_class_scope($user, $alias = 'classes')
    {
        $ids = $this->allowed_class_ids($user);
        if ($ids === null) {
            return;
        }
        if (!count($ids)) {
            $this->ci->db->where($alias . '.class_id', 0);
            return;
        }
        $this->ci->db->where_in($alias . '.class_id', $ids);
    }

    public function meta($user)
    {
        $campuses = $this->ci->db->where('status', 1)->order_by('campus_name', 'ASC')->get('campuses')->result_array();
        $campus_ids = $this->allowed_campus_ids($user);
        if ($campus_ids !== null) {
            $campuses = array_values(array_filter($campuses, function ($c) use ($campus_ids) {
                return in_array((int) $c['campus_id'], $campus_ids, true);
            }));
        }
        return array('campuses' => $campuses);
    }

    public function courses_for_campus($campus_id)
    {
        $campus_id = (int) $campus_id;
        if ($campus_id <= 0) {
            return array();
        }
        $this->ci->db->from('courses');
        $this->ci->db->like('campus_ids', (string) $campus_id, 'both');
        $this->ci->db->order_by('course_name', 'ASC');
        return $this->ci->db->get()->result_array();
    }

    public function sessions_for_course($course_id)
    {
        $course_id = (int) $course_id;
        if ($course_id <= 0) {
            return array();
        }
        return $this->ci->db
            ->order_by('session_name', 'ASC')
            ->get_where('course_sessions', array('course_id' => $course_id))
            ->result_array();
    }

    public function session_detail($course_id, $session_name)
    {
        $course_id = (int) $course_id;
        $session_name = trim((string) $session_name);
        if ($course_id <= 0 || $session_name === '') {
            return null;
        }
        $row = $this->ci->db->get_where('course_sessions', array(
            'course_id' => $course_id,
            'session_name' => $session_name,
        ))->row_array();
        if (!$row) {
            return null;
        }
        return array(
            'class_fee' => $row['per_student_fee'],
            'minimum_installment_fee' => $row['minimum_installment_fee'],
            'dead_line_entry' => $row['dead_line_add_edit_student'],
            'maximum_fee_last_date' => $row['maximum_fee_last_date'],
            'maximum_difference_installments' => $row['maximum_difference_installments'],
            'exam_no' => $row['first_council_exam_no'],
            'freeze_fee' => $row['freeze_fee'],
            'freeze_last_date' => $row['freeze_last_date'],
            'admission_fee' => $row['re_admission_fee'],
        );
    }

    private function read_body($body)
    {
        return array(
            'campus_id' => (int) (isset($body['campus_id']) ? $body['campus_id'] : 0),
            'course_id' => (int) (isset($body['course_id']) ? $body['course_id'] : 0),
            'session' => isset($body['session']) ? trim($body['session']) : '',
            'name' => isset($body['name']) ? trim($body['name']) : '',
            'badge_no' => isset($body['badge_no']) ? trim($body['badge_no']) : '',
            'seats' => isset($body['seats']) ? trim($body['seats']) : '',
            'online_study' => !empty($body['online_study']) ? 1 : 0,
            'status' => isset($body['status']) ? (int) $body['status'] : 1,
        );
    }

    private function validate_body($fields)
    {
        if ($fields['campus_id'] <= 0) {
            return array('success' => false, 'message' => 'Campus is required');
        }
        if ($fields['course_id'] <= 0) {
            return array('success' => false, 'message' => 'Course is required');
        }
        if ($fields['session'] === '') {
            return array('success' => false, 'message' => 'Session is required');
        }
        if ($fields['name'] === '') {
            return array('success' => false, 'message' => 'Class name is required');
        }
        if ($fields['badge_no'] === '') {
            return array('success' => false, 'message' => 'Badge number is required');
        }
        if ($fields['seats'] === '') {
            return array('success' => false, 'message' => 'Number of seats is required');
        }
        $detail = $this->session_detail($fields['course_id'], $fields['session']);
        if (!$detail) {
            return array('success' => false, 'message' => 'Course session not found');
        }
        return array('success' => true, 'fields' => $fields, 'detail' => $detail);
    }

    private function enrich_row($row)
    {
        if (!$row) {
            return $row;
        }
        $class_id = (int) $row['class_id'];
        $row['active_students'] = (int) $this->ci->db
            ->where(array('class_id' => $class_id, 'status' => 1))
            ->count_all_results('students');
        $row['inactive_students'] = (int) $this->ci->db
            ->where(array('class_id' => $class_id, 'status' => 0))
            ->count_all_results('students');
        $row['online_study'] = !empty($row['online_study']) ? 1 : 0;
        return $row;
    }

    public function list_all($user)
    {
        $this->ci->db->select('classes.*, campuses.campus_name, courses.course_name');
        $this->ci->db->from('classes');
        $this->ci->db->join('campuses', 'campuses.campus_id = classes.campus_id', 'left');
        $this->ci->db->join('courses', 'courses.course_id = classes.course_id', 'left');
        $this->ci->db->where('classes.status', 1);
        $this->apply_class_scope($user);
        $this->ci->db->order_by('classes.class_id', 'ASC');
        $rows = $this->ci->db->get()->result_array();
        return array_map(array($this, 'enrich_row'), $rows);
    }

    public function get($class_id, $user)
    {
        $class_id = (int) $class_id;
        $this->ci->db->select('classes.*, campuses.campus_name, courses.course_name');
        $this->ci->db->from('classes');
        $this->ci->db->join('campuses', 'campuses.campus_id = classes.campus_id', 'left');
        $this->ci->db->join('courses', 'courses.course_id = classes.course_id', 'left');
        $this->ci->db->where('classes.class_id', $class_id);
        $this->apply_class_scope($user);
        $row = $this->ci->db->get()->row_array();
        if (!$row) {
            return null;
        }
        return $this->enrich_row($row);
    }

    public function create($user, $body)
    {
        if (!$this->is_admin($user)) {
            $access = $this->access_row($user);
            if (empty($access['class_edit'])) {
                return array('success' => false, 'message' => 'Class add permission required');
            }
        }
        $fields = $this->read_body($body);
        $parsed = $this->validate_body($fields);
        if (empty($parsed['success'])) {
            return $parsed;
        }
        $data = array_merge($parsed['fields'], $parsed['detail']);
        $this->ci->db->insert('classes', $data);
        $id = (int) $this->ci->db->insert_id();
        return array('success' => true, 'message' => 'Class added successfully.', 'class_id' => $id, 'data' => $this->get($id, $user));
    }

    public function update($class_id, $user, $body)
    {
        $class_id = (int) $class_id;
        if (!$this->get($class_id, $user)) {
            return array('success' => false, 'message' => 'Class not found');
        }
        if (!$this->is_admin($user)) {
            $access = $this->access_row($user);
            if (empty($access['class_edit'])) {
                return array('success' => false, 'message' => 'Class edit permission required');
            }
        }
        $fields = $this->read_body($body);
        $parsed = $this->validate_body($fields);
        if (empty($parsed['success'])) {
            return $parsed;
        }
        $data = array_merge($parsed['fields'], $parsed['detail']);
        $this->ci->db->where('class_id', $class_id);
        $this->ci->db->update('classes', $data);
        return array('success' => true, 'message' => 'Class updated successfully.', 'data' => $this->get($class_id, $user));
    }

    public function delete($class_id, $user, $actor_name = 'POS')
    {
        $class_id = (int) $class_id;
        if (!$this->get($class_id, $user)) {
            return array('success' => false, 'message' => 'Class not found');
        }
        if (!$this->is_admin($user)) {
            $access = $this->access_row($user);
            if (empty($access['class_delete'])) {
                return array('success' => false, 'message' => 'Class delete permission required');
            }
        }

        $this->ci->db->set('status', 0);
        $this->ci->db->where('class_id', $class_id);
        $this->ci->db->update('classes');

        $this->ci->db->set('status', 0);
        $this->ci->db->where('class_id', $class_id);
        $this->ci->db->update('students');

        $students = $this->ci->db->get_where('students', array('class_id' => $class_id))->result_array();
        foreach ($students as $student) {
            $this->ci->db->insert('deleted_students', array(
                'delete_type' => 'Delete',
                'student_id' => $student['student_id'],
                'deleted_by' => $actor_name,
                'reason' => 'other',
                'refund_amount' => 0,
                'reason_detail' => 'Class Delete',
                'image' => '',
                'approve_by' => $actor_name,
                'status' => 1,
            ));
        }

        return array('success' => true, 'message' => 'Class deleted successfully.');
    }
}
