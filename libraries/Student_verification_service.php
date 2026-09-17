<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Course-wise requirements used before a new admission can be cleared. */
class Student_verification_service {
    private $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
    }

    public function ensure_tables()
    {
        $this->ci->db->query("CREATE TABLE IF NOT EXISTS student_verification_rules (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            course_id INT UNSIGNED NOT NULL,
            min_paid_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_by VARCHAR(150) NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id), UNIQUE KEY student_verification_rules_course (course_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $this->ci->db->query("CREATE TABLE IF NOT EXISTS student_verification_rule_documents (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            rule_id INT UNSIGNED NOT NULL,
            document_type VARCHAR(150) NOT NULL,
            required_count INT UNSIGNED NOT NULL DEFAULT 1,
            sort_order INT UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY (id), KEY student_verification_rule_documents_rule (rule_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function list_rules()
    {
        $this->ensure_tables();
        $rows = $this->ci->db->select('student_verification_rules.*, courses.course_name')
            ->from('student_verification_rules')
            ->join('courses', 'courses.course_id = student_verification_rules.course_id', 'left')
            ->order_by('courses.course_name', 'ASC')->get()->result_array();
        foreach ($rows as &$row) $row['documents'] = $this->documents((int)$row['id']);
        return $rows;
    }

    public function documents($rule_id)
    {
        return $this->ci->db->where('rule_id', (int)$rule_id)->order_by('sort_order', 'ASC')->order_by('id', 'ASC')
            ->get('student_verification_rule_documents')->result_array();
    }

    public function save_rule($course_id, $min_paid_fee, $documents, $user_name)
    {
        $this->ensure_tables();
        $course_id = (int)$course_id;
        if ($course_id <= 0) return array('success' => false, 'message' => 'Course is required');
        $course = $this->ci->db->get_where('courses', array('course_id' => $course_id))->row_array();
        if (!$course) return array('success' => false, 'message' => 'Invalid course');
        $valid = array();
        foreach ((array)$documents as $index => $doc) {
            $type = trim(isset($doc['document_type']) ? $doc['document_type'] : '');
            if ($type === '') continue;
            $valid[] = array('document_type' => $type, 'required_count' => max(1, (int)(isset($doc['required_count']) ? $doc['required_count'] : 1)), 'sort_order' => $index);
        }
        $now = date('Y-m-d H:i:s');
        $existing = $this->ci->db->get_where('student_verification_rules', array('course_id' => $course_id))->row_array();
        $data = array('min_paid_fee' => max(0, (float)$min_paid_fee), 'is_active' => 1, 'updated_at' => $now);
        if ($existing) {
            $rule_id = (int)$existing['id'];
            $this->ci->db->where('id', $rule_id)->update('student_verification_rules', $data);
            $this->ci->db->where('rule_id', $rule_id)->delete('student_verification_rule_documents');
        } else {
            $data += array('course_id' => $course_id, 'created_by' => $user_name, 'created_at' => $now);
            $this->ci->db->insert('student_verification_rules', $data);
            $rule_id = (int)$this->ci->db->insert_id();
        }
        foreach ($valid as $doc) { $doc['rule_id'] = $rule_id; $this->ci->db->insert('student_verification_rule_documents', $doc); }
        return array('success' => true, 'message' => 'Verification checklist saved', 'rule_id' => $rule_id);
    }

    public function delete_rule($rule_id)
    {
        $this->ensure_tables();
        $rule_id = (int)$rule_id;
        $this->ci->db->where('rule_id', $rule_id)->delete('student_verification_rule_documents');
        $this->ci->db->where('id', $rule_id)->delete('student_verification_rules');
        return array('success' => true, 'message' => 'Verification checklist deleted');
    }

    private function _rule_map($course_ids)
    {
        if (!count($course_ids)) return array();
        $rows = $this->ci->db->where_in('course_id', $course_ids)->where('is_active', 1)->get('student_verification_rules')->result_array();
        $map = array();
        foreach ($rows as $row) { $row['documents'] = $this->documents((int)$row['id']); $map[(int)$row['course_id']] = $row; }
        return $map;
    }

    public function evaluate_batch($students, $payments_map = array(), $documents_map = array())
    {
        $this->ensure_tables();
        $course_ids = array_values(array_unique(array_filter(array_map(function($s) { return isset($s['course_id']) ? (int)$s['course_id'] : 0; }, $students))));
        $rules = $this->_rule_map($course_ids);
        $result = array();
        foreach ($students as $student) {
            $sid = (int)$student['student_id']; $course_id = (int)(isset($student['course_id']) ? $student['course_id'] : 0);
            $rule = isset($rules[$course_id]) ? $rules[$course_id] : null;
            if (!$rule) { $result[$sid] = array('has_rule' => false, 'can_clear' => true, 'status' => 'no_rule', 'paid_fee' => 0, 'min_paid_fee' => 0, 'documents' => array(), 'missing' => array()); continue; }
            $paid_fee = 0;
            foreach ((isset($payments_map[$sid]['paid']) ? $payments_map[$sid]['paid'] : array()) as $p) {
                if ((isset($p['payment_plan']) ? $p['payment_plan'] : '') !== 'consulation fee') $paid_fee += (float)(isset($p['actual_amount']) ? $p['actual_amount'] : 0);
            }
            $counts = array_count_values(isset($documents_map[$sid]) ? $documents_map[$sid] : array());
            $checks = array(); $missing = array();
            foreach ($rule['documents'] as $doc) {
                $type = $doc['document_type']; $required = (int)$doc['required_count']; $present = isset($counts[$type]) ? (int)$counts[$type] : 0;
                $passed = $present >= $required;
                $checks[] = array('document_type' => $type, 'required_count' => $required, 'present_count' => $present, 'passed' => $passed);
                if (!$passed) $missing[] = $type.' '.$present.'/'.$required;
            }
            $min_fee = (float)$rule['min_paid_fee'];
            if ($paid_fee + 0.0001 < $min_fee) $missing[] = 'Paid fee '.number_format($paid_fee, 0).' / '.number_format($min_fee, 0);
            $result[$sid] = array('has_rule' => true, 'can_clear' => !count($missing), 'status' => count($missing) ? 'blocked' : 'ready', 'paid_fee' => $paid_fee, 'min_paid_fee' => $min_fee, 'documents' => $checks, 'missing' => $missing);
        }
        return $result;
    }

    public function evaluate_student($student_id)
    {
        $student = $this->ci->db->select('students.student_id, classes.course_id')->from('students')->join('classes', 'classes.class_id = students.class_id', 'left')->where('students.student_id', (int)$student_id)->get()->row_array();
        if (!$student) return null;
        $payments = $this->ci->db->where('student_id', (int)$student_id)->get('payments')->result_array();
        $map = array((int)$student_id => array('paid' => array(), 'unpaid' => array()));
        foreach ($payments as $p) $map[(int)$student_id][!empty($p['paid']) ? 'paid' : 'unpaid'][] = $p;
        $docs = $this->ci->db->select('type')->where('student_id', (int)$student_id)->where('type !=', '')->get('student_documents')->result_array();
        $doc_map = array((int)$student_id => array()); foreach ($docs as $doc) $doc_map[(int)$student_id][] = $doc['type'];
        $all = $this->evaluate_batch(array($student), $map, $doc_map); return $all[(int)$student_id];
    }
}
