<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Councils module business logic for React POS (report, CRUD, meta).
 */
class Councils_service {

    /** @var CI_Controller */
    private $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
    }

    // ── Access & meta ─────────────────────────────────────────────────────────

    public function can_access($user)
    {
        if (!$user) {
            return false;
        }
        if ($user['role'] === 'Admin') {
            return true;
        }
        $acc = $this->_access_row($user);
        return $acc && !empty($acc['council_report']);
    }

    public function permissions($user)
    {
        $is_admin = $user && $user['role'] === 'Admin';
        $acc = $is_admin ? array() : $this->_access_row($user);

        return array(
            'is_admin' => $is_admin,
            'sidebar' => $is_admin || !empty($acc['council_report']),
            'council_report' => $is_admin || !empty($acc['council_report']),
            'council_report_add_fee' => $is_admin || !empty($acc['council_report_add_information_can_add_fee']),
            'council_report_add_expense' => $is_admin || !empty($acc['council_report_add_information_can_add_expense']),
            'manage_councils' => $is_admin,
            'manage_paper_types' => $is_admin,
            'manage_council_exams' => $is_admin,
            'manage_result_rules' => $is_admin,
            'manage_sequences' => $is_admin,
            'manage_exam_sequences' => $is_admin,
        );
    }

    public function meta($user)
    {
        $is_admin = $user && $user['role'] === 'Admin';
        $acc = $is_admin ? array() : $this->_access_row($user);

        $this->ci->db->select('course_id, course_name, course_type, status');
        $this->ci->db->from('courses');
        if (!$is_admin && !empty($acc['council_report_courses'])) {
            $ids = array_values(array_filter(array_map('intval', explode(',', $acc['council_report_courses']))));
            if (!empty($ids)) {
                $this->ci->db->where_in('course_id', $ids);
            }
        }
        $this->ci->db->order_by('course_name', 'ASC');
        $courses = $this->ci->db->get()->result_array();

        return array(
            'permissions' => $this->permissions($user),
            'courses' => $courses,
            'councils' => $this->ci->db->order_by('name', 'ASC')->get('councils')->result_array(),
            'paper_types' => $this->ci->db->order_by('name', 'ASC')->get('paper_types')->result_array(),
            'expense_categories' => $this->ci->db
                ->select('expense_category_id, name, sub_of')
                ->where('status', 'active')
                ->order_by('name', 'ASC')
                ->get('expense_category')
                ->result_array(),
            'legacy_root' => rtrim(base_url(), '/'),
        );
    }

    public function list_course_subjects($course_id)
    {
        return $this->ci->db
            ->order_by('subject_name', 'ASC')
            ->get_where('course_subjects', array('course_id' => (int) $course_id))
            ->result_array();
    }

    // ── Report (JSON port of report_ajax.php) ─────────────────────────────────

    public function report_index($user, $status = null)
    {
        $this->ci->db->select(
            'exam_sequence.id, exam_sequence.course_id, exam_sequence.first_year, exam_sequence.first_year_type, exam_sequence.class, exam_sequence.status, courses.course_name'
        );
        $rows = $this->_exam_sequences_query($user, $status)->get()->result_array();

        $out = array();
        foreach ($rows as $row) {
            $out[] = array(
                'id' => (int) $row['id'],
                'course_id' => (int) $row['course_id'],
                'course_name' => $row['course_name'],
                'first_year' => $row['first_year'],
                'first_year_type' => isset($row['first_year_type']) ? $row['first_year_type'] : '',
                'class' => $row['class'],
                'status' => isset($row['status']) ? $row['status'] : '',
            );
        }

        return $out;
    }

    public function report_row($user, $exam_sequence_id)
    {
        $council_exam = $this->_get_exam_sequence_for_report($user, (int) $exam_sequence_id);
        if (!$council_exam) {
            return null;
        }

        return $this->_build_report_row($council_exam);
    }

    public function report($user, $status = null)
    {
        $council_exams = $this->_exam_sequences_query($user, $status)->get()->result_array();

        $rows = array();
        $final_liability = 0;

        foreach ($council_exams as $council_exam) {
            $row = $this->_build_report_row($council_exam);
            $final_liability += (float) $row['total_liability'];
            $rows[] = $row;
        }

        return array(
            'rows' => $rows,
            'total_liability' => $final_liability,
        );
    }

    // ── Councils CRUD ─────────────────────────────────────────────────────────

    public function list_councils()
    {
        return $this->ci->db->order_by('name', 'ASC')->get('councils')->result_array();
    }

    public function get_council($council_id)
    {
        return $this->ci->db->get_where('councils', array('council_id' => (int) $council_id))->row_array();
    }

    public function save_council($data)
    {
        $council_id = isset($data['council_id']) ? (int) $data['council_id'] : 0;
        $course_ids = isset($data['course_ids']) ? $data['course_ids'] : array();
        if (!is_array($course_ids)) {
            $course_ids = array_filter(array_map('trim', explode(',', (string) $course_ids)));
        }

        $row = array(
            'name' => isset($data['name']) ? $data['name'] : '',
            'code' => isset($data['code']) ? $data['code'] : '',
            'phone' => isset($data['phone']) ? $data['phone'] : '',
            'address' => isset($data['address']) ? $data['address'] : '',
            'location' => isset($data['location']) ? $data['location'] : '',
            'comment' => isset($data['comment']) ? $data['comment'] : '',
            'course_ids' => implode(',', $course_ids),
        );

        if ($council_id > 0) {
            $this->ci->db->where('council_id', $council_id)->update('councils', $row);
            return array('council_id' => $council_id, 'message' => 'Council updated successfully.');
        }

        $this->ci->db->insert('councils', $row);
        return array('council_id' => (int) $this->ci->db->insert_id(), 'message' => 'Council added successfully.');
    }

    public function delete_council($council_id)
    {
        $this->ci->db->where('council_id', (int) $council_id)->delete('councils');
        return array('message' => 'Council deleted successfully.');
    }

    // ── Paper types CRUD ──────────────────────────────────────────────────────

    public function list_paper_types()
    {
        return $this->ci->db->order_by('name', 'ASC')->get('paper_types')->result_array();
    }

    public function save_paper_type($data)
    {
        $paper_type_id = isset($data['paper_type_id']) ? (int) $data['paper_type_id'] : 0;
        $row = array('name' => isset($data['name']) ? $data['name'] : '');

        if ($paper_type_id > 0) {
            $this->ci->db->where('paper_type_id', $paper_type_id)->update('paper_types', $row);
            return array('paper_type_id' => $paper_type_id, 'message' => 'Paper Type updated successfully.');
        }

        $this->ci->db->insert('paper_types', $row);
        return array('paper_type_id' => (int) $this->ci->db->insert_id(), 'message' => 'Paper Type added successfully.');
    }

    public function delete_paper_type($paper_type_id)
    {
        $this->ci->db->where('paper_type_id', (int) $paper_type_id)->delete('paper_types');
        return array('message' => 'Paper type deleted successfully.');
    }

    // ── Council exam rules CRUD ───────────────────────────────────────────────

    public function list_council_exams()
    {
        $this->ci->db->select('council_exams.*, paper_types.name as paper_type_name, courses.course_name');
        $this->ci->db->from('council_exams');
        $this->ci->db->join('paper_types', 'paper_types.paper_type_id = council_exams.paper_type_id', 'inner');
        $this->ci->db->join('courses', 'courses.course_id = council_exams.course_id', 'inner');
        $this->ci->db->order_by('council_exams.council_exam_id', 'DESC');
        return $this->ci->db->get()->result_array();
    }

    public function get_council_exam($council_exam_id)
    {
        $this->ci->db->select('council_exams.*, paper_types.name as paper_type_name, courses.course_name');
        $this->ci->db->from('council_exams');
        $this->ci->db->join('paper_types', 'paper_types.paper_type_id = council_exams.paper_type_id', 'inner');
        $this->ci->db->join('courses', 'courses.course_id = council_exams.course_id', 'inner');
        $this->ci->db->where('council_exams.council_exam_id', (int) $council_exam_id);
        return $this->ci->db->get()->row_array();
    }

    public function save_council_exam($data)
    {
        $council_exam_id = isset($data['council_exam_id']) ? (int) $data['council_exam_id'] : 0;
        $subject_ids = isset($data['subject_ids']) ? $data['subject_ids'] : array();
        if (!is_array($subject_ids)) {
            $subject_ids = array_filter(array_map('trim', explode(',', (string) $subject_ids)));
        }

        $row = array(
            'course_id' => isset($data['course_id']) ? $data['course_id'] : null,
            'subject_ids' => implode(',', $subject_ids),
            'paper_type_id' => isset($data['paper_type_id']) ? $data['paper_type_id'] : null,
            'paper_name' => isset($data['paper_name']) ? $data['paper_name'] : '',
            'paper_no' => isset($data['paper_no']) ? $data['paper_no'] : '',
            'passing_marks' => isset($data['passing_marks']) ? $data['passing_marks'] : '',
            'passing_percentage' => isset($data['passing_percentage']) ? $data['passing_percentage'] : '',
        );

        if ($council_exam_id > 0) {
            $this->ci->db->where('council_exam_id', $council_exam_id)->update('council_exams', $row);
            return array('council_exam_id' => $council_exam_id, 'message' => 'Council exam updated successfully.');
        }

        $this->ci->db->insert('council_exams', $row);
        return array('council_exam_id' => (int) $this->ci->db->insert_id(), 'message' => 'Council exam added successfully.');
    }

    public function delete_council_exam($council_exam_id)
    {
        $this->ci->db->where('council_exam_id', (int) $council_exam_id)->delete('council_exams');
        return array('message' => 'Council Exam deleted successfully.');
    }

    // ── Result rules CRUD ─────────────────────────────────────────────────────

    public function list_result_rules()
    {
        $this->ci->db->select('council_result_rules.*, courses.course_name');
        $this->ci->db->from('council_result_rules');
        $this->ci->db->join('courses', 'courses.course_id = council_result_rules.course_id', 'inner');
        $this->ci->db->order_by('council_result_rules.council_result_rule_id', 'DESC');
        return $this->ci->db->get()->result_array();
    }

    public function save_result_rule($data)
    {
        $rule_id = isset($data['council_result_rule_id']) ? (int) $data['council_result_rule_id'] : 0;
        $row = array(
            'course_id' => isset($data['course_id']) ? $data['course_id'] : null,
            'total_chances' => isset($data['total_chances']) ? $data['total_chances'] : null,
            'after_chances' => isset($data['after_chances']) ? $data['after_chances'] : null,
            'attempt_scope' => isset($data['attempt_scope']) ? $data['attempt_scope'] : null,
            'annual_students_can_appear_in' => isset($data['annual_students_can_appear_in']) ? $data['annual_students_can_appear_in'] : null,
            'supplementary_students_can_appear_in' => isset($data['supplementary_students_can_appear_in']) ? $data['supplementary_students_can_appear_in'] : null,
            'promote_on_supplementary' => isset($data['promote_on_supplementary']) ? $data['promote_on_supplementary'] : null,
        );

        if ($rule_id > 0) {
            $this->ci->db->where('council_result_rule_id', $rule_id)->update('council_result_rules', $row);
            return array('council_result_rule_id' => $rule_id, 'message' => 'Council result rule updated successfully.');
        }

        $this->ci->db->insert('council_result_rules', $row);
        return array('council_result_rule_id' => (int) $this->ci->db->insert_id(), 'message' => 'Council result rule added successfully.');
    }

    public function delete_result_rule($council_result_rule_id)
    {
        $this->ci->db->where('council_result_rule_id', (int) $council_result_rule_id)->delete('council_result_rules');
        return array('message' => 'Council result rule deleted successfully.');
    }

    // ── Sequences & exam sequences ────────────────────────────────────────────

    public function list_sequences()
    {
        $this->ci->db->select('council_sequence.*, courses.course_name, councils.name as council_name');
        $this->ci->db->from('council_sequence');
        $this->ci->db->join('courses', 'courses.course_id = council_sequence.course_id', 'inner');
        $this->ci->db->join('councils', 'councils.council_id = council_sequence.council_id', 'inner');
        $this->ci->db->order_by('council_sequence.council_sequence_id', 'DESC');
        return $this->ci->db->get()->result_array();
    }

    public function list_exam_sequences($status = null)
    {
        $this->ci->db->select('exam_sequence.*, courses.course_name, courses.course_type');
        $this->ci->db->from('exam_sequence');
        $this->ci->db->join('courses', 'courses.course_id = exam_sequence.course_id', 'inner');
        if ($status !== null && $status !== '') {
            $this->ci->db->where('exam_sequence.status', $status);
        }
        $this->ci->db->order_by('exam_sequence.id', 'DESC');
        $rows = $this->ci->db->get()->result_array();

        foreach ($rows as &$row) {
            $row['fee_tasks'] = $this->_exam_sequence_fee_tasks($row['course_id'], $row['id']);
        }

        return $rows;
    }

    private function _exam_sequence_fee_tasks($course_id, $exam_sequence_id)
    {
        $this->ci->db->order_by('last_date', 'ASC');
        $fees = $this->ci->db->get_where('council_sequence', array(
            'course_id' => (int) $course_id,
            'has_fee' => '1',
        ))->result_array();

        $out = array();
        foreach ($fees as $fee) {
            $this->ci->db->order_by('from_date', 'ASC');
            $rules = $this->ci->db->get_where('council_sequence_fee_rules', array(
                'sequence_fee_id' => $fee['council_sequence_id'],
                'exam_sequence_id' => (int) $exam_sequence_id,
            ))->result_array();

            $out[] = array(
                'council_sequence_id' => (int) $fee['council_sequence_id'],
                'type_name' => $fee['type_name'],
                'recurring' => $fee['recurring'],
                'rules' => $rules,
            );
        }

        return $out;
    }

    public function set_exam_sequence_status($exam_sequence_id, $status)
    {
        $this->ci->db->where('id', (int) $exam_sequence_id)->update('exam_sequence', array('status' => $status));
        return array('message' => 'Exam sequence status updated.');
    }

    public function save_fee_rule($data)
    {
        $id = isset($data['fee_rule_id']) ? (int) $data['fee_rule_id'] : 0;
        $row = array(
            'sequence_fee_id' => isset($data['sequence_fee_id']) ? $data['sequence_fee_id'] : null,
            'exam_sequence_id' => isset($data['exam_sequence_id']) ? $data['exam_sequence_id'] : null,
            'from_date' => isset($data['from_date']) ? $data['from_date'] : null,
            'to_date' => isset($data['to_date']) ? $data['to_date'] : null,
            'exam_fee' => isset($data['exam_fee']) ? $data['exam_fee'] : 0,
            'expense_fee' => isset($data['expense_fee']) ? $data['expense_fee'] : 0,
            'has_first_time_fee' => !empty($data['has_first_time_fee']) && $data['has_first_time_fee'] !== '0' ? 1 : 0,
            'first_time_fee' => isset($data['first_time_fee']) ? $data['first_time_fee'] : 0,
            'first_time_expense' => isset($data['first_time_expense']) ? $data['first_time_expense'] : 0,
        );

        if ($id > 0) {
            $this->ci->db->where('id', $id)->update('council_sequence_fee_rules', $row);
            return array('id' => $id, 'message' => 'Fee rule updated successfully.');
        }

        $this->ci->db->insert('council_sequence_fee_rules', $row);
        return array('id' => (int) $this->ci->db->insert_id(), 'message' => 'Fee rule added successfully.');
    }

    public function delete_fee_rule($fee_rule_id)
    {
        $this->ci->db->where('id', (int) $fee_rule_id)->delete('council_sequence_fee_rules');
        return array('message' => 'Fee rule deleted successfully.');
    }

    public function get_sequence($council_sequence_id)
    {
        return $this->ci->db->get_where('council_sequence', array('council_sequence_id' => (int) $council_sequence_id))->row_array();
    }

    public function save_sequence($data)
    {
        $id = isset($data['council_sequence_id']) ? (int) $data['council_sequence_id'] : 0;
        $fee = isset($data['fee']) ? $data['fee'] : 0;
        $has_fee = ($fee === '1' || $fee === 1 || (is_numeric($fee) && (float) $fee > 0)) ? 1 : 0;
        $has_expense = !empty($data['has_expense']) && $data['has_expense'] !== '0' ? 1 : 0;

        $row = array(
            'council_id' => isset($data['council_id']) ? $data['council_id'] : null,
            'course_id' => isset($data['course_id']) ? $data['course_id'] : null,
            'type_name' => isset($data['type_name']) ? $data['type_name'] : '',
            'last_date' => isset($data['last_date']) ? $data['last_date'] : '',
            'fee' => $has_fee ? (is_numeric($fee) && (float) $fee > 1 ? $fee : 1) : 0,
            'has_fee' => $has_fee,
            'has_expense' => $has_expense,
            'expense_date' => isset($data['expense_date']) ? $data['expense_date'] : null,
            'expense_fee' => isset($data['expense_fee']) ? $data['expense_fee'] : null,
            'recurring' => isset($data['recurring']) ? $data['recurring'] : null,
            'action_type' => isset($data['action_type']) ? $data['action_type'] : 'fee',
            'no_of_chances' => isset($data['no_of_chances']) ? $data['no_of_chances'] : null,
            'exp_category_id' => ($has_expense && !empty($data['exp_category_id'])) ? $data['exp_category_id'] : null,
        );

        if ($id > 0) {
            $this->ci->db->where('council_sequence_id', $id)->update('council_sequence', $row);
            return array('council_sequence_id' => $id, 'message' => 'Sequence updated successfully.');
        }

        $this->ci->db->insert('council_sequence', $row);
        return array('council_sequence_id' => (int) $this->ci->db->insert_id(), 'message' => 'Sequence added successfully.');
    }

    public function delete_sequence($council_sequence_id)
    {
        $has_rules = $this->ci->db->get_where('council_sequence_fee_rules', array('sequence_fee_id' => (int) $council_sequence_id))->result_array();
        if ($has_rules) {
            throw new Exception('This sequence has fee rules — delete them first.');
        }
        $this->ci->db->where('council_sequence_id', (int) $council_sequence_id)->delete('council_sequence');
        return array('message' => 'Sequence deleted successfully.');
    }

    public function get_exam_sequence($exam_sequence_id)
    {
        $this->ci->db->select('exam_sequence.*, courses.course_name, courses.course_type, courses.course_duration_year');
        $this->ci->db->from('exam_sequence');
        $this->ci->db->join('courses', 'courses.course_id = exam_sequence.course_id', 'inner');
        $this->ci->db->where('exam_sequence.id', (int) $exam_sequence_id);
        return $this->ci->db->get()->row_array();
    }

    public function save_exam_sequence($data)
    {
        $id = isset($data['id']) ? (int) $data['id'] : 0;
        $row = array(
            'course_id' => isset($data['course_id']) ? $data['course_id'] : null,
            'first_year_type' => isset($data['first_year_type']) ? $data['first_year_type'] : null,
            'first_year' => isset($data['first_year']) ? $data['first_year'] : null,
            'class' => isset($data['class']) ? $data['class'] : null,
        );

        if ($id > 0) {
            $this->ci->db->where('id', $id)->update('exam_sequence', $row);
            return array('id' => $id, 'message' => 'Exam sequence updated successfully.');
        }

        $exists = $this->ci->db
            ->where('course_id', $row['course_id'])
            ->where('class', $row['class'])
            ->where('first_year_type', $row['first_year_type'])
            ->where('first_year', $row['first_year'])
            ->get('exam_sequence')
            ->row_array();
        if ($exists) {
            throw new Exception('Exam sequence already exists (status: ' . $exists['status'] . ').');
        }

        $this->ci->db->insert('exam_sequence', $row);
        return array('id' => (int) $this->ci->db->insert_id(), 'message' => 'Exam sequence added successfully.');
    }

    public function delete_exam_sequence($exam_sequence_id)
    {
        $this->ci->db->where('id', (int) $exam_sequence_id)->delete('exam_sequence');
        return array('message' => 'Exam sequence deleted successfully.');
    }

    // ── Private: report row builder ─────────────────────────────────────────

    private function _report_allowed_courses($user)
    {
        $is_admin = $user && $user['role'] === 'Admin';
        $acc = $is_admin ? array() : $this->_access_row($user);

        return (!$is_admin && !empty($acc['council_report_courses']))
            ? array_values(array_filter(array_map('intval', explode(',', $acc['council_report_courses']))))
            : array();
    }

    private function _exam_sequences_query($user, $status = null)
    {
        $is_admin = $user && $user['role'] === 'Admin';
        $allowed_courses = $this->_report_allowed_courses($user);

        $this->ci->db->from('exam_sequence');
        $this->ci->db->join('courses', 'courses.course_id = exam_sequence.course_id', 'inner');
        if ($status !== null && $status !== '') {
            $this->ci->db->where('exam_sequence.status', $status);
        }
        if (!$is_admin && !empty($allowed_courses)) {
            $this->ci->db->where_in('exam_sequence.course_id', $allowed_courses);
        }
        $this->ci->db->order_by('exam_sequence.id', 'DESC');

        return $this->ci->db;
    }

    private function _get_exam_sequence_for_report($user, $exam_sequence_id)
    {
        $is_admin = $user && $user['role'] === 'Admin';
        $allowed_courses = $this->_report_allowed_courses($user);

        $this->ci->db->select('exam_sequence.*, courses.course_name, courses.course_type');
        $this->ci->db->from('exam_sequence');
        $this->ci->db->join('courses', 'courses.course_id = exam_sequence.course_id', 'inner');
        $this->ci->db->where('exam_sequence.id', (int) $exam_sequence_id);
        if (!$is_admin && !empty($allowed_courses)) {
            $this->ci->db->where_in('exam_sequence.course_id', $allowed_courses);
        }

        return $this->ci->db->get()->row_array();
    }

    private function _build_report_row($council_exam)
    {
        $course_id = (int) $council_exam['course_id'];
        $exam_no = $council_exam['first_year'];
        $class = $council_exam['class'];
        $course_type = isset($council_exam['course_type']) ? $council_exam['course_type'] : 'Annual';
        $course_type_label = $this->_course_type_label($course_type);
        $payment_comment = $this->_payment_comment($exam_no, $class, $course_type);

        $tasks = $this->ci->db
            ->select('council_sequence.*, councils.name')
            ->from('council_sequence')
            ->join('councils', 'councils.council_id = council_sequence.council_id', 'inner')
            ->where('council_sequence.course_id', $course_id)
            ->order_by("STR_TO_DATE(council_sequence.last_date, '%d/%m')", 'ASC', false)
            ->get()
            ->result_array();

        $fee_tasks_raw = array();
        $other_tasks_raw = array();
        foreach ($tasks as $task) {
            if ($task['action_type'] === 'fee') {
                $fee_tasks_raw[] = $task;
            } else {
                $other_tasks_raw[] = $task;
            }
        }

        $total_students_rows = $this->_students_by_payment_comment($course_id, $payment_comment);
        $total_students_in_progress = 0;
        foreach ($total_students_rows as $student_row) {
            $total_students_in_progress += (int) $student_row['total_students'];
        }

        $task_wise_amounts = array();
        $fee_tasks = array();
        $overall_total = $total_students_in_progress > 0 ? $total_students_in_progress : (int) (isset($total_students_rows[0]['total_students']) ? $total_students_rows[0]['total_students'] : 0);

        foreach ($fee_tasks_raw as $task) {
            if (empty($task['has_fee'])) {
                continue;
            }

            $stats = $this->_fee_task_stats($council_exam, $task, $payment_comment, $overall_total);
            if (!isset($task_wise_amounts[$task['council_sequence_id']])) {
                $task_wise_amounts[$task['council_sequence_id']] = array(
                    'task_name' => $task['name'],
                    'type_name' => $task['type_name'],
                    'fee_amount' => 0,
                    'expense_amount' => 0,
                    'profit_amount' => 0,
                    'paid_students' => 0,
                    'unpaid_students' => 0,
                    'expense_done' => 0,
                    'liability' => 0,
                    'waiting_for_expense' => 0,
                );
            }

            $bucket =& $task_wise_amounts[$task['council_sequence_id']];
            $bucket['fee_amount'] += $stats['total_fee_amount'];
            $bucket['expense_amount'] += $stats['total_expense_amount'];
            $bucket['profit_amount'] += $stats['total_profit_amount'];
            $bucket['paid_students'] += $stats['paid'];
            $bucket['unpaid_students'] += $stats['unpaid'];
            $bucket['expense_done'] += $stats['expense_done'];
            $bucket['waiting_for_expense'] += $stats['waiting_for_expense'];
            $bucket['liability'] += $stats['total_liability'];

            $fee_tasks[] = array(
                'council_sequence_id' => (int) $task['council_sequence_id'],
                'council_name' => $task['name'],
                'type_name' => $task['type_name'],
                'has_fee' => (int) $task['has_fee'],
                'has_expense' => (int) $task['has_expense'],
                'fee_created' => $stats['fee_created'],
                'fee_not_created' => $stats['fee_not_created'],
                'paid' => $stats['paid'],
                'unpaid' => $stats['unpaid'],
                'expense_done' => $stats['expense_done'],
                'waiting_for_expense' => $stats['waiting_for_expense'],
                'colors' => $stats['colors'],
                'link_base' => $this->_student_link_params($course_id, 0, $exam_no, $class, null, (int) $task['council_sequence_id']),
                'links' => array(
                    'fee_created' => $this->_student_link_params($course_id, 0, $exam_no, $class, 'fee_created', (int) $task['council_sequence_id']),
                    'fee_not_created' => $this->_student_link_params($course_id, 0, $exam_no, $class, 'fee_not_created', (int) $task['council_sequence_id']),
                    'paid' => $this->_student_link_params($course_id, 0, $exam_no, $class, 'paid', (int) $task['council_sequence_id']),
                    'unpaid' => $this->_student_link_params($course_id, 0, $exam_no, $class, 'unpaid', (int) $task['council_sequence_id']),
                ),
            );
        }

        $accounts = array_values($task_wise_amounts);
        $total_liability = 0;
        foreach ($accounts as $account) {
            $total_liability += (float) $account['liability'];
        }

        $other_tasks = array();
        foreach ($other_tasks_raw as $task) {
            $other_tasks[] = $this->_other_task_stats($council_exam, $task, $payment_comment);
        }

        $sessions = $this->_session_breakdown($council_exam, $fee_tasks_raw, $payment_comment);

        return array(
            'id' => (int) $council_exam['id'],
            'course_id' => $course_id,
            'course_name' => $council_exam['course_name'],
            'course_type' => $course_type,
            'course_type_label' => $course_type_label,
            'first_year' => $exam_no,
            'first_year_type' => isset($council_exam['first_year_type']) ? $council_exam['first_year_type'] : '',
            'class' => $class,
            'status' => isset($council_exam['status']) ? $council_exam['status'] : '',
            'payment_comment' => $payment_comment,
            'total_students_in_progress' => $total_students_in_progress,
            'total_liability' => $total_liability,
            'accounts' => $accounts,
            'fee_tasks' => $fee_tasks,
            'other_tasks' => $other_tasks,
            'sessions' => $sessions,
        );
    }

    private function _fee_task_stats($council_exam, $task, $payment_comment, $total_students)
    {
        $course_id = (int) $council_exam['course_id'];
        $exam_no = $council_exam['first_year'];
        $class = $council_exam['class'];

        $this->ci->db->select('students.student_id, payments.paid, payments.amount, payments.paid_date');
        $this->ci->db->from('payments');
        $this->ci->db->join(
            'students',
            'students.student_id = COALESCE(NULLIF(payments.student_id, 0), payments.custom_student_id)',
            'inner'
        );
        $this->ci->db->join('classes', 'classes.class_id = students.class_id', 'inner');
        $this->ci->db->where('students.status', 1);
        $this->ci->db->where('students.course_id', $course_id);
        $this->ci->db->where('payments.council_sequence_id', (int) $task['council_sequence_id']);
        $this->ci->db->like('payments.payment_comment', $payment_comment, 'both');
        $this->ci->db->group_by('students.student_id');
        $task_students = $this->ci->db->get()->result_array();

        $paid = 0;
        $unpaid = 0;
        $expense_done = 0;
        $waiting_for_expense = 0;
        $total_fee_amount = 0;
        $total_expense_amount = 0;
        $total_profit_amount = 0;
        $total_liability = 0;

        foreach ($task_students as $task_student) {
            if (!empty($task['has_expense'])) {
                $expense = $this->ci->db->get_where('expenses', array(
                    'student_id' => $task_student['student_id'],
                    'council_exam_no' => $exam_no,
                    'council_sequence_id' => $task['council_sequence_id'],
                    'class' => $class,
                ))->result_array();

                if (count($expense) > 0) {
                    $expense_done++;
                    $fee_amount = (float) $task_student['amount'];
                    $expense_amount = (float) $expense[0]['amount'];
                    $total_expense_amount += $expense_amount;
                    $total_profit_amount += ($fee_amount - $expense_amount);
                } else {
                    $waiting_for_expense++;
                    $fee_rule = $this->ci->db
                        ->order_by('from_date', 'ASC')
                        ->get_where('council_sequence_fee_rules', array(
                            'exam_sequence_id' => $council_exam['id'],
                            'to_date >=' => $task_student['paid_date'],
                        ))
                        ->row_array();
                    $expense_amount = !empty($fee_rule['expense_fee']) ? (float) $fee_rule['expense_fee'] : 0;
                    $total_liability += $expense_amount;
                }
            }

            if ((int) $task_student['paid'] === 1) {
                $paid++;
                $total_fee_amount += (float) $task_student['amount'];
            } else {
                $unpaid++;
            }
        }

        $fee_created = abs($paid + $unpaid);
        $fee_not_created = $total_students - $paid - $unpaid;

        return array(
            'paid' => $paid,
            'unpaid' => $unpaid,
            'expense_done' => $expense_done,
            'waiting_for_expense' => $waiting_for_expense,
            'fee_created' => $fee_created,
            'fee_not_created' => $fee_not_created,
            'total_fee_amount' => $total_fee_amount,
            'total_expense_amount' => $total_expense_amount,
            'total_profit_amount' => $total_profit_amount,
            'total_liability' => $total_liability,
            'colors' => array(
                'fee_created' => ($fee_created == $total_students) ? 'green' : 'red',
                'fee_not_created' => ($fee_not_created > 0) ? 'red' : 'green',
                'paid' => ($paid == $total_students) ? 'green' : 'red',
                'unpaid' => ($unpaid == $total_students) ? 'green' : 'red',
                'expense_done' => ($expense_done == $total_students) ? 'green' : 'red',
                'waiting_for_expense' => ($waiting_for_expense > 0) ? 'red' : 'green',
            ),
        );
    }

    private function _other_task_stats($council_exam, $task, $payment_comment)
    {
        $course_id = (int) $council_exam['course_id'];
        $exam_no = $council_exam['first_year'];
        $class = $council_exam['class'];
        $action_type = trim(strtolower($task['action_type']));

        if ($action_type === 'information') {
            $page = 'info_students';
        } elseif ($action_type === 'add_roll_no') {
            $page = 'documents_students';
        } else {
            $page = 'result_students';
        }

        $this->ci->db->select('students.*, payments.paid');
        $this->ci->db->from('payments');
        $this->ci->db->join(
            'students',
            'students.student_id = COALESCE(NULLIF(payments.student_id, 0), payments.custom_student_id)',
            'inner'
        );
        $this->ci->db->join('classes', 'classes.class_id = students.class_id', 'inner');
        $this->ci->db->where('students.status', 1);
        $this->ci->db->where('students.course_id', $course_id);
        $this->ci->db->like('payments.payment_comment', $payment_comment, 'both');
        $this->ci->db->group_by('students.student_id');
        $task_students = $this->ci->db->get()->result_array();

        $done = 0;
        $not_done = 0;

        foreach ($task_students as $task_student) {
            $row = $this->ci->db->get_where('punjab_council_roll_number', array(
                'cnic' => $task_student['cnic'],
                'council_exam_no' => $exam_no,
                'class' => $class,
                'course_id' => $task['course_id'],
            ))->row_array();

            $found = false;
            if ($task['action_type'] === 'information') {
                if (!empty($row) && !empty($row['extra_info'])) {
                    $extra_info = json_decode($row['extra_info'], true);
                    if (is_array($extra_info) && !empty($extra_info)) {
                        $latest_info = null;
                        foreach ($extra_info as $info) {
                            if ((int) $info['council_sequence_id'] === (int) $task['council_sequence_id']) {
                                if (
                                    empty($latest_info)
                                    || strtotime($info['informed_at']) > strtotime($latest_info['informed_at'])
                                ) {
                                    $latest_info = $info;
                                }
                            }
                        }
                        if (!empty($latest_info) && !empty($latest_info['informed']) && (int) $latest_info['informed'] === 1) {
                            $found = true;
                        }
                    }
                }
            } elseif (!empty($row)) {
                if ($task['action_type'] === 'add_roll_no' && $row['roll_no'] !== '' && $row['roll_no'] !== null) {
                    $found = true;
                } elseif ($task['action_type'] === 'add_result' && $row['result_remarks'] !== '' && $row['result_remarks'] !== null) {
                    $found = true;
                }
            }

            if ($found) {
                $done++;
            } else {
                $not_done++;
            }
        }

        return array(
            'council_sequence_id' => (int) $task['council_sequence_id'],
            'type_name' => $task['type_name'],
            'action_type' => $task['action_type'],
            'page' => $page,
            'done' => $done,
            'not_done' => $not_done,
            'links' => array(
                'done' => $this->_other_task_link_params($page, $course_id, $exam_no, $class, 'done', (int) $task['council_sequence_id'], (int) $council_exam['id']),
                'waiting' => $this->_other_task_link_params($page, $course_id, $exam_no, $class, 'waiting', (int) $task['council_sequence_id'], (int) $council_exam['id']),
            ),
        );
    }

    private function _session_breakdown($council_exam, $fee_tasks_raw, $payment_comment)
    {
        $course_id = (int) $council_exam['course_id'];
        $exam_no = $council_exam['first_year'];
        $class = $council_exam['class'];

        $this->ci->db->select('students.student_id, classes.session, COUNT(DISTINCT students.student_id) as total_students');
        $this->ci->db->from('payments');
        $this->ci->db->join(
            'students',
            'students.student_id = COALESCE(NULLIF(payments.student_id, 0), payments.custom_student_id)',
            'inner'
        );
        $this->ci->db->join('classes', 'classes.class_id = students.class_id', 'inner');
        $this->ci->db->where('students.status', 1);
        $this->ci->db->where('students.course_id', $course_id);
        $this->ci->db->like('payments.payment_comment', $payment_comment, 'both');
        $this->ci->db->group_by('classes.session');
        $session_rows = $this->ci->db->get()->result_array();

        $sessions = array();
        foreach ($session_rows as $session_row) {
            $session_name = $session_row['session'];
            $session_total = (int) $session_row['total_students'];
            $task_breakdown = array();

            foreach ($fee_tasks_raw as $task) {
                if (empty($task['has_fee'])) {
                    continue;
                }

                $this->ci->db->select('students.student_id, payments.paid');
                $this->ci->db->from('payments');
                $this->ci->db->join(
                    'students',
                    'students.student_id = COALESCE(NULLIF(payments.student_id, 0), payments.custom_student_id)',
                    'inner'
                );
                $this->ci->db->join('classes', 'classes.class_id = students.class_id', 'inner');
                $this->ci->db->where('students.status', 1);
                $this->ci->db->where('students.course_id', $course_id);
                $this->ci->db->where('classes.session', $session_name);
                $this->ci->db->where('payments.council_sequence_id', (int) $task['council_sequence_id']);
                $this->ci->db->like('payments.payment_comment', $payment_comment, 'both');
                $task_students = $this->ci->db->get()->result_array();

                $paid = 0;
                $unpaid = 0;
                foreach ($task_students as $task_student) {
                    if ((int) $task_student['paid'] === 1) {
                        $paid++;
                    } else {
                        $unpaid++;
                    }
                }

                $fee_not_created = $session_total - $paid - $unpaid;
                $task_breakdown[] = array(
                    'council_sequence_id' => (int) $task['council_sequence_id'],
                    'type_name' => $task['type_name'],
                    'fee_not_created' => $fee_not_created,
                    'paid' => $paid,
                    'unpaid' => $unpaid,
                );
            }

            $sessions[] = array(
                'session' => $session_name,
                'total_students' => $session_total,
                'tasks' => $task_breakdown,
                'students_link' => $this->_student_link_params($course_id, $session_name, $exam_no, 1, null, null),
            );
        }

        return $sessions;
    }

    private function _students_by_payment_comment($course_id, $payment_comment)
    {
        $this->ci->db->select('classes.session, COUNT(DISTINCT students.student_id) as total_students');
        $this->ci->db->from('payments');
        $this->ci->db->join(
            'students',
            'students.student_id = COALESCE(NULLIF(payments.student_id, 0), payments.custom_student_id)',
            'inner'
        );
        $this->ci->db->join('classes', 'classes.class_id = students.class_id', 'left');
        $this->ci->db->where('students.status', 1);
        $this->ci->db->where('students.course_id', (int) $course_id);
        $this->ci->db->like('payments.payment_comment', $payment_comment, 'both');
        return $this->ci->db->get()->result_array();
    }

    // ── Private: helpers ──────────────────────────────────────────────────────

    private function _access_row($user)
    {
        if (!$user) {
            return array();
        }
        return $this->ci->db->get_where('access', array('user_id' => $user['user_id']))->row_array() ?: array();
    }

    private function _course_type_label($course_type)
    {
        return $course_type === 'Annual' ? 'Year' : $course_type;
    }

    private function _ordinal($number)
    {
        $number = (int) $number;
        $suffixes = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
        if (($number % 100) >= 11 && ($number % 100) <= 13) {
            return $number . 'th';
        }
        return $number . $suffixes[$number % 10];
    }

    private function _payment_comment($exam_no, $class, $course_type)
    {
        return 'This fee for next exam # ' . $exam_no . ' ' . $this->_ordinal($class) . ' ' . $this->_course_type_label($course_type);
    }

    private function _student_link_params($course_id, $session, $exam_no, $class, $type, $sequence_id)
    {
        return array(
            'page' => 'students',
            'course_id' => (int) $course_id,
            'session' => $session,
            'exam_no' => $exam_no,
            'class' => $class,
            'type' => $type,
            'sequence_id' => $sequence_id,
        );
    }

    private function _other_task_link_params($page, $course_id, $exam_no, $class, $type, $sequence_id, $exam_sequence_id)
    {
        return array(
            'page' => $page,
            'course_id' => (int) $course_id,
            'session' => 0,
            'exam_no' => $exam_no,
            'class' => $class,
            'type' => $type,
            'sequence_id' => $sequence_id,
            'exam_sequence_id' => $exam_sequence_id,
        );
    }
}
