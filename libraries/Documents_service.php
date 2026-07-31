<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Documents_service {

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

    public function meta()
    {
        $campuses = $this->ci->db->order_by('campus_name', 'ASC')->get('campuses')->result_array();
        $courses = $this->ci->db->order_by('course_name', 'ASC')->get('courses')->result_array();
        return array(
            'campuses' => $campuses,
            'courses' => $courses,
        );
    }

    public function classes_for_filter($campus_id = null, $course_id = null)
    {
        $this->ci->db->select('classes.class_id, classes.name, classes.session, classes.campus_id, classes.course_id');
        $this->ci->db->from('classes');
        if ($campus_id) {
            $this->ci->db->where('classes.campus_id', (int) $campus_id);
        }
        if ($course_id) {
            $this->ci->db->where('classes.course_id', (int) $course_id);
        }
        $this->ci->db->order_by('classes.name', 'ASC');
        return $this->ci->db->get()->result_array();
    }

    public function how_to_use_list()
    {
        if (!$this->ci->db->table_exists('how_to_use')) {
            return array();
        }
        return $this->ci->db
            ->order_by('id', 'DESC')
            ->get_where('how_to_use', array('module' => 'documents'))
            ->result_array();
    }

    public function how_to_use_add($body, $user)
    {
        if (!$this->ci->db->table_exists('how_to_use')) {
            return array('success' => false, 'message' => 'how_to_use table missing');
        }
        $title = isset($body['title']) ? trim($body['title']) : '';
        if ($title === '') {
            return array('success' => false, 'message' => 'Title required');
        }
        $row = array(
            'title' => $title,
            'module' => 'documents',
            'created_by' => isset($user['first_name']) ? $user['first_name'] . ' ' . $user['last_name'] : 'Admin',
        );
        if ($this->ci->db->field_exists('detail', 'how_to_use')) {
            $row['detail'] = isset($body['detail']) ? trim($body['detail']) : '';
        } elseif ($this->ci->db->field_exists('description', 'how_to_use')) {
            $row['description'] = isset($body['detail']) ? trim($body['detail']) : '';
        }
        if ($this->ci->db->field_exists('created_at', 'how_to_use')) {
            $row['created_at'] = date('Y-m-d H:i:s');
        }
        $this->ci->db->insert('how_to_use', $row);
        return array('success' => true, 'id' => $this->ci->db->insert_id());
    }

    public function how_to_use_delete($id)
    {
        if (!$this->ci->db->table_exists('how_to_use')) {
            return array('success' => false, 'message' => 'how_to_use table missing');
        }
        $row = $this->ci->db->get_where('how_to_use', array('id' => (int) $id, 'module' => 'documents'))->row_array();
        if (!$row) {
            return array('success' => false, 'message' => 'Not found');
        }
        if (!empty($row['file']) && file_exists(FCPATH . 'how_to_use/' . $row['file'])) {
            @unlink(FCPATH . 'how_to_use/' . $row['file']);
        }
        $this->ci->db->delete('how_to_use', array('id' => (int) $id));
        return array('success' => true);
    }

    public function council_exam_numbers()
    {
        $this->ci->db->select('council_exam_no, class, date, result_update_date');
        $this->ci->db->from('punjab_council_roll_number');
        $this->ci->db->group_by('council_exam_no');
        $this->ci->db->group_by('class');
        $this->ci->db->where('class', '2');
        return $this->ci->db->get()->result_array();
    }

    public function diploma_search($council_exam_no, $campus_id = null)
    {
        if (!$council_exam_no) {
            return array();
        }
        $this->ci->db->select('campuses.campus_name, punjab_council_roll_number.*, students.class_id, students.roll_no as campus_roll_no, classes.name as class_name, students.mobile, students.emergency_no, contractors.name as contractor_name, students.student_id, students.first_name, students.last_name, students.father_name, students.address');
        $this->ci->db->from('punjab_council_roll_number');
        $this->ci->db->join('students', 'students.cnic=punjab_council_roll_number.cnic', 'left');
        $this->ci->db->join('classes', 'students.class_id=classes.class_id', 'left');
        $this->ci->db->join('contractors', 'students.contractor_id=contractors.contractor_id', 'left');
        $this->ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
        $this->ci->db->where('students.course_id', 1);
        $this->ci->db->where('students.status', 1);
        $this->ci->db->where('punjab_council_roll_number.result_remarks!=', '');
        $this->ci->db->where('punjab_council_roll_number.council_exam_no', $council_exam_no);
        $this->ci->db->where('punjab_council_roll_number.class', '2');
        $this->ci->db->where("(punjab_council_roll_number.result_remarks='Pass' OR punjab_council_roll_number.result_remarks='Pass*')", NULL, FALSE);
        if ($campus_id) {
            $this->ci->db->where('campuses.campus_id', (int) $campus_id);
        }
        return $this->ci->db->get()->result_array();
    }

    public function students_search($campus_id = null, $course_id = null, $class_id = null)
    {
        $this->ci->db->select('students.*, classes.name as class_name, classes.session, campuses.campus_name, courses.course_name');
        $this->ci->db->from('students');
        $this->ci->db->join('classes', 'classes.class_id=students.class_id', 'left');
        $this->ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
        $this->ci->db->join('courses', 'courses.course_id=students.course_id', 'left');
        $this->ci->db->where('students.status', '1');
        if ($campus_id) {
            $this->ci->db->where('classes.campus_id', (int) $campus_id);
        }
        if ($course_id) {
            $this->ci->db->where('courses.course_id', (int) $course_id);
        }
        if ($class_id) {
            $this->ci->db->where('classes.class_id', (int) $class_id);
        }
        $this->ci->db->group_by('students.student_id');
        $this->ci->db->order_by('students.roll_no', 'ASC');
        $rows = $this->ci->db->get()->result_array();
        foreach ($rows as &$row) {
            $row['documents'] = $this->_student_doc_flags((int) $row['student_id']);
            $row['contractor_name'] = $this->_contractor_label((int) $row['contractor_id']);
            $row['fee_alert'] = $this->_fee_alert((int) $row['student_id'], (int) $row['contractor_id']);
        }
        return $rows;
    }

    private function _student_doc_flags($student_id)
    {
        $types = array('ID Card', 'Photo', 'Result Card');
        $out = array('id_card' => false, 'photo' => false, 'result_card' => false);
        $map = array('ID Card' => 'id_card', 'Photo' => 'photo', 'Result Card' => 'result_card');
        foreach ($types as $type) {
            $n = $this->ci->db->where(array('student_id' => $student_id, 'type' => $type))->count_all_results('student_documents');
            if ($n > 0 && isset($map[$type])) {
                $out[$map[$type]] = true;
            }
        }
        return $out;
    }

    private function _contractor_label($contractor_id)
    {
        if ($contractor_id <= 0) {
            return 'N/A';
        }
        $c = $this->ci->db->get_where('contractors', array('contractor_id' => $contractor_id))->row_array();
        return $c ? $c['name'] . ' (' . $c['date'] . ')' : 'N/A';
    }

    private function _fee_alert($student_id, $contractor_id)
    {
        if ($contractor_id > 0) {
            return false;
        }
        $n = $this->ci->db->where(array('student_id' => $student_id, 'contractor_id' => 0))->count_all_results('payments');
        return $n === 0;
    }

    public function receipt_pad_list()
    {
        $this->ci->db->select('bookcount.*, campuses.campus_name, campuses.campus_code, users.first_name, users.last_name');
        $this->ci->db->from('bookcount');
        $this->ci->db->join('campuses', 'campuses.campus_code=bookcount.campus_code', 'inner');
        $this->ci->db->join('users', 'users.user_id = bookcount.created_by', 'inner');
        $this->ci->db->order_by('bookcount.id', 'DESC');
        return $this->ci->db->get()->result_array();
    }

    public function next_book_number($campus_code)
    {
        $this->ci->db->select_max('book');
        $this->ci->db->from('bookcount');
        $this->ci->db->where('campus_code', $campus_code);
        $row = $this->ci->db->get()->row_array();
        $count = isset($row['book']) ? (int) $row['book'] : 0;
        return $count + 1;
    }

    public function store_receipt_pad($campus_code, $book, $user_id)
    {
        $this->ci->db->insert('bookcount', array(
            'book' => (int) $book,
            'campus_code' => $campus_code,
            'created_by' => (int) $user_id,
            'created_at' => date('Y-m-d H:i:s'),
        ));
        return array('success' => true, 'id' => $this->ci->db->insert_id());
    }
}
