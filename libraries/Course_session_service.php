<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Course_session_service {

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

    private function session_fields($body)
    {
        return array(
            'course_id' => (int) (isset($body['course_id']) ? $body['course_id'] : 0),
            'session_name' => isset($body['session_name']) ? trim($body['session_name']) : '',
            'dead_line_add_edit_student' => isset($body['dead_line_add_edit_student']) ? trim($body['dead_line_add_edit_student']) : '',
            'last_date_auto_fee_installment' => isset($body['last_date_auto_fee_installment']) ? trim($body['last_date_auto_fee_installment']) : '',
            'per_student_fee' => isset($body['per_student_fee']) ? trim($body['per_student_fee']) : '',
            'maximum_fee_last_date' => isset($body['maximum_fee_last_date']) ? trim($body['maximum_fee_last_date']) : '',
            'minimum_installment_fee' => isset($body['minimum_installment_fee']) ? trim($body['minimum_installment_fee']) : '',
            'maximum_difference_installments' => isset($body['maximum_difference_installments']) ? trim($body['maximum_difference_installments']) : '',
            'council_board_fee' => isset($body['council_board_fee']) ? trim($body['council_board_fee']) : 'Yes',
            'first_council_exam_no' => isset($body['first_council_exam_no']) ? trim($body['first_council_exam_no']) : '',
            'first_time_council_fee' => isset($body['first_time_council_fee']) ? trim($body['first_time_council_fee']) : '0',
            'last_date_council_fee' => isset($body['last_date_council_fee']) ? trim($body['last_date_council_fee']) : date('Y-m-d'),
            're_admission_fee' => isset($body['re_admission_fee']) ? trim($body['re_admission_fee']) : '0',
            'freeze_fee' => isset($body['freeze_fee']) ? trim($body['freeze_fee']) : '0',
            'freeze_last_date' => isset($body['freeze_last_date']) ? trim($body['freeze_last_date']) : '',
        );
    }

    private function validate_fields($fields, $course_session_id = 0)
    {
        if ($fields['course_id'] <= 0) {
            return array('success' => false, 'message' => 'Course is required');
        }
        if ($fields['session_name'] === '') {
            return array('success' => false, 'message' => 'Session name is required');
        }
        $required = array(
            'dead_line_add_edit_student' => 'Deadline for add/edit student',
            'last_date_auto_fee_installment' => 'Last date of payment plan',
            'per_student_fee' => 'Per student fee',
            'maximum_fee_last_date' => 'Maximum last date of fee',
            'minimum_installment_fee' => 'Minimum installment fee',
            'maximum_difference_installments' => 'Maximum days between installments',
            'first_council_exam_no' => 'First council exam number',
            'freeze_last_date' => 'Maximum student freeze date',
        );
        foreach ($required as $key => $label) {
            if ($fields[$key] === '') {
                return array('success' => false, 'message' => $label . ' is required');
            }
        }

        $this->ci->db->where('course_id', $fields['course_id']);
        $this->ci->db->where('session_name', $fields['session_name']);
        if ($course_session_id > 0) {
            $this->ci->db->where('course_session_id !=', (int) $course_session_id);
        }
        if ($this->ci->db->count_all_results('course_sessions') > 0) {
            return array('success' => false, 'message' => 'Session already added for this course');
        }

        return array('success' => true, 'fields' => $fields);
    }

    public function meta()
    {
        $courses = $this->ci->db->order_by('course_name', 'ASC')->get('courses')->result_array();
        return array('courses' => $courses);
    }

    public function list_all()
    {
        $this->ci->db->select('course_sessions.*, courses.course_name');
        $this->ci->db->from('course_sessions');
        $this->ci->db->join('courses', 'courses.course_id = course_sessions.course_id', 'inner');
        $this->ci->db->order_by('course_sessions.course_session_id', 'DESC');
        return $this->ci->db->get()->result_array();
    }

    public function get($course_session_id)
    {
        $this->ci->db->select('course_sessions.*, courses.course_name');
        $this->ci->db->from('course_sessions');
        $this->ci->db->join('courses', 'courses.course_id = course_sessions.course_id', 'inner');
        $this->ci->db->where('course_sessions.course_session_id', (int) $course_session_id);
        return $this->ci->db->get()->row_array();
    }

    private function sync_fee_rules($fields)
    {
        $this->ci->db->set('last_date', $fields['last_date_auto_fee_installment']);
        $this->ci->db->set('total_fee', $fields['per_student_fee']);
        $this->ci->db->set('first_time_council_fee', $fields['first_time_council_fee']);
        $this->ci->db->set('last_date_council_fee', $fields['last_date_council_fee']);
        $this->ci->db->where('course_id', $fields['course_id']);
        $this->ci->db->where('session', $fields['session_name']);
        $this->ci->db->update('fee_rules');
    }

    private function sync_classes($fields)
    {
        $this->ci->db->set('dead_line_entry', $fields['dead_line_add_edit_student']);
        $this->ci->db->set('class_fee', $fields['per_student_fee']);
        $this->ci->db->set('minimum_installment_fee', $fields['minimum_installment_fee']);
        $this->ci->db->set('maximum_fee_last_date', $fields['maximum_fee_last_date']);
        $this->ci->db->set('maximum_difference_installments', $fields['maximum_difference_installments']);
        $this->ci->db->set('exam_no', $fields['first_council_exam_no']);
        $this->ci->db->set('freeze_fee', $fields['freeze_fee']);
        $this->ci->db->set('freeze_last_date', $fields['freeze_last_date']);
        $this->ci->db->set('admission_fee', $fields['re_admission_fee']);
        $this->ci->db->where('course_id', $fields['course_id']);
        $this->ci->db->where('session', $fields['session_name']);
        $this->ci->db->update('classes');
    }

    private function sync_council_payment_comments($fields)
    {
        if ($fields['first_council_exam_no'] === '' || $fields['first_council_exam_no'] === '0') {
            return;
        }
        $classes = $this->ci->db->get_where('classes', array(
            'course_id' => $fields['course_id'],
            'session' => $fields['session_name'],
        ))->result_array();
        $class_ids = array();
        foreach ($classes as $class) {
            $class_ids[] = $class['class_id'];
        }
        if (!count($class_ids)) {
            return;
        }
        $this->ci->db->select('student_id');
        $this->ci->db->from('students');
        $this->ci->db->where_in('class_id', $class_ids);
        $students = $this->ci->db->get()->result_array();
        foreach ($students as $student) {
            $this->ci->db->select('*');
            $this->ci->db->from('payments');
            $this->ci->db->where(array('student_id' => $student['student_id'], 'payment_plan' => 'consulation fee'));
            $this->ci->db->order_by('dead_line', 'ASC');
            $this->ci->db->limit(1);
            $payment = $this->ci->db->get()->row_array();
            if ($payment) {
                $this->ci->db->set('payment_comment', 'This fee for next exam # ' . $fields['first_council_exam_no'] . ' 1st Year');
                $this->ci->db->where('id', $payment['id']);
                $this->ci->db->update('payments');
            }
        }
    }

    public function create($body)
    {
        $fields = $this->session_fields($body);
        $parsed = $this->validate_fields($fields);
        if (empty($parsed['success'])) {
            return $parsed;
        }
        $fields = $parsed['fields'];
        $this->ci->db->insert('course_sessions', $fields);
        $id = (int) $this->ci->db->insert_id();
        $this->sync_fee_rules($fields);
        return array('success' => true, 'message' => 'Session added successfully.', 'course_session_id' => $id, 'data' => $this->get($id));
    }

    public function update($course_session_id, $body)
    {
        $course_session_id = (int) $course_session_id;
        if (!$this->get($course_session_id)) {
            return array('success' => false, 'message' => 'Session not found');
        }
        $fields = $this->session_fields($body);
        $parsed = $this->validate_fields($fields, $course_session_id);
        if (empty($parsed['success'])) {
            return $parsed;
        }
        $fields = $parsed['fields'];
        $this->sync_council_payment_comments($fields);
        $this->ci->db->where('course_session_id', $course_session_id);
        $this->ci->db->update('course_sessions', $fields);
        $this->sync_classes($fields);
        $this->sync_fee_rules($fields);
        return array('success' => true, 'message' => 'Session updated successfully.', 'data' => $this->get($course_session_id));
    }

    public function delete($course_session_id)
    {
        $course_session_id = (int) $course_session_id;
        if (!$this->get($course_session_id)) {
            return array('success' => false, 'message' => 'Session not found');
        }
        $this->ci->db->where('course_session_id', $course_session_id);
        $this->ci->db->delete('course_sessions');
        return array('success' => true, 'message' => 'Session deleted successfully.');
    }

    public function exams_for_course($course_id)
    {
        $course_id = (int) $course_id;
        if ($course_id <= 0) {
            return array();
        }
        $this->ci->db->from('exam_sequence');
        $this->ci->db->where('course_id', $course_id);
        $this->ci->db->where('class', 1);
        $this->ci->db->where('first_year_type', 'annual');
        return $this->ci->db->get()->result_array();
    }

    public function council_sequence_preview($course_id, $exam_first_year)
    {
        $course_id = (int) $course_id;
        $exam_first_year = trim((string) $exam_first_year);
        if ($course_id <= 0 || $exam_first_year === '') {
            return array();
        }
        $exam_row = $this->ci->db->get_where('exam_sequence', array(
            'course_id' => $course_id,
            'first_year' => $exam_first_year,
            'class' => 1,
        ))->row_array();
        if (!$exam_row) {
            return array();
        }
        $exam_sequence_id = (int) $exam_row['id'];
        $this->ci->db->from('council_sequence');
        $this->ci->db->where('course_id', $course_id);
        $this->ci->db->where('has_fee', 1);
        $this->ci->db->where('action_type', 'fee');
        $this->ci->db->where_in('recurring', array('One Time', 'Each Exam', 'Every Semester'));
        $sequences = $this->ci->db->get()->result_array();
        $out = array();
        foreach ($sequences as $fee) {
            $rules = $this->ci->db->get_where('council_sequence_fee_rules', array(
                'sequence_fee_id' => $fee['council_sequence_id'],
                'exam_sequence_id' => $exam_sequence_id,
            ))->result_array();
            $out[] = array(
                'type_name' => $fee['type_name'],
                'recurring' => $fee['recurring'],
                'rules' => $rules,
            );
        }
        return $out;
    }
}
