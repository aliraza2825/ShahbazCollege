<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Councils report drill-down pages and mutation endpoints for React POS.
 */
class Councils_drill_service {

    /** @var CI_Controller */
    private $ci;

    /** @var Councils_service */
    private $councils_service;

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->library('councils_service');
        $this->councils_service = $this->ci->councils_service;
    }

    // ── Drill page loaders ────────────────────────────────────────────────────

    public function drill($user, $params)
    {
        $page = isset($params['page']) ? $params['page'] : '';
        switch ($page) {
            case 'students':
                return $this->_drill_students($user, $params);
            case 'info_students':
                return $this->_drill_info_students($user, $params);
            case 'documents_students':
                return $this->_drill_documents_students($user, $params);
            case 'result_students':
                return $this->_drill_result_students($user, $params);
            default:
                return array('success' => false, 'message' => 'Invalid drill page');
        }
    }

    private function _drill_students($user, $params)
    {
        $course_id = (int) $params['course_id'];
        $session = isset($params['session']) ? $params['session'] : 0;
        $exam_no = $params['exam_no'];
        $class = $params['class'];
        $type = isset($params['type']) ? $params['type'] : null;
        $sequence_id = isset($params['sequence_id']) ? (int) $params['sequence_id'] : null;

        $course = $this->ci->db->get_where('courses', array('course_id' => $course_id))->row_array();
        if (!$course) {
            return array('success' => false, 'message' => 'Course not found');
        }

        $thisClass = $this->_class_label($class);
        $payment_comment = $this->_payment_comment($exam_no, $class, $course['course_type']);

        $this->ci->db->select('students.*, campuses.campus_name, classes.session, classes.name as class_name, payments.council_sequence_id, payments.paid');
        $this->ci->db->from('payments');
        $this->ci->db->join('students', 'students.student_id = COALESCE(NULLIF(payments.student_id, 0), payments.custom_student_id)', 'inner');
        $this->ci->db->join('classes', 'classes.class_id=students.class_id', 'inner');
        $this->ci->db->join('campuses', 'campuses.campus_id=classes.campus_id', 'left');
        if ($session != 0 && $session !== '0') {
            $this->ci->db->where('classes.session', $session);
        }
        $this->ci->db->where('students.status', 1);
        $this->ci->db->where('students.course_id', $course_id);
        $this->ci->db->like('payments.payment_comment', 'This fee for next exam # ' . $exam_no . ' ' . $thisClass . ' Year', 'both');
        if ($type === 'unpaid') {
            $this->ci->db->where('payments.paid', 0);
        }
        if ($type && $type !== 'fee_not_created' && $sequence_id) {
            $this->ci->db->where('payments.council_sequence_id', $sequence_id);
        }
        $this->ci->db->group_by('students.student_id');
        $this->ci->db->order_by('students.status ASC, payments.paid DESC');
        $students = $this->ci->db->get()->result_array();

        if ($type === 'fee_not_created' && $sequence_id) {
            foreach ($students as $key => $student) {
                $this->ci->db->select('payments.council_sequence_id');
                $this->ci->db->from('payments');
                $this->ci->db->like('payments.payment_comment', 'This fee for next exam # ' . $exam_no . ' ' . $thisClass . ' Year', 'both');
                $this->ci->db->where(
                    'COALESCE(NULLIF(payments.student_id, 0), payments.custom_student_id) =',
                    $student['student_id']
                );
                $student_fees = $this->ci->db->get()->result_array();
                foreach ($student_fees as $fee) {
                    if ((int) $fee['council_sequence_id'] === $sequence_id) {
                        unset($students[$key]);
                        break;
                    }
                }
            }
            $students = array_values($students);
        }

        $sequence = $sequence_id
            ? $this->ci->db->get_where('council_sequence', array('council_sequence_id' => $sequence_id))->row_array()
            : null;

        $exam_sequence = null;
        $fee_rules = array();
        if ($sequence) {
            $exam_sequence = $this->ci->db->get_where('exam_sequence', array(
                'course_id' => $sequence['course_id'],
                'first_year' => $exam_no,
                'class' => $class,
                'status' => 'Active',
            ))->row_array();
            if ($exam_sequence) {
                $fee_rules = $this->ci->db->get_where('council_sequence_fee_rules', array(
                    'sequence_fee_id' => $sequence_id,
                    'exam_sequence_id' => $exam_sequence['id'],
                ))->result_array();
            }
        }

        $perms = $this->councils_service->permissions($user);
        $rows = array();
        foreach ($students as $student) {
            $row = $this->_format_student_row($student);
            if ($type === 'paid' && $sequence_id) {
                $row['expense'] = $this->_get_student_expense($student['student_id'], $exam_no, $sequence_id, $class);
            }
            $rows[] = $row;
        }

        return array(
            'success' => true,
            'data' => array(
                'page' => 'students',
                'course' => $course,
                'sequence' => $sequence,
                'exam_sequence' => $exam_sequence,
                'type' => $type,
                'fee_rules' => $fee_rules,
                'council_bank_accounts' => $this->_council_bank_accounts(),
                'petty_cash_balance' => $this->_petty_cash_balance($user),
                'students' => $rows,
                'permissions' => array(
                    'can_add_expense' => !empty($perms['council_report_add_expense']),
                ),
                'legacy_root' => rtrim(base_url(), '/'),
            ),
        );
    }

    private function _drill_info_students($user, $params)
    {
        $course_id = (int) $params['course_id'];
        $exam_no = $params['exam_no'];
        $class = $params['class'];
        $sequence_id = (int) $params['sequence_id'];

        $course = $this->ci->db->get_where('courses', array('course_id' => $course_id))->row_array();
        $sequence = $this->ci->db->get_where('council_sequence', array('council_sequence_id' => $sequence_id))->row_array();
        if (!$course || !$sequence) {
            return array('success' => false, 'message' => 'Course or sequence not found');
        }

        $thisClass = $this->_class_label($class);
        $students = $this->_students_by_exam_fee($course_id, $exam_no, $thisClass);

        $rows = array();
        foreach ($students as $student) {
            $row = $this->_format_student_row($student);
            $roll = $this->_get_roll_record($student['cnic'], $exam_no, $class, $course_id);
            $row['roll_record'] = $roll;
            $row['inform_history'] = $this->_inform_history_for_sequence($roll, $sequence_id);
            $rows[] = $row;
        }

        $type = isset($params['type']) ? $params['type'] : null;
        $rows = $this->_apply_done_waiting_filter($rows, $type, 'info_students', $sequence_id);

        return array(
            'success' => true,
            'data' => array(
                'page' => 'info_students',
                'course' => $course,
                'sequence' => $sequence,
                'inform_reports' => $this->_inform_reports($sequence_id),
                'students' => $rows,
                'legacy_root' => rtrim(base_url(), '/'),
            ),
        );
    }

    private function _drill_documents_students($user, $params)
    {
        $course_id = (int) $params['course_id'];
        $exam_no = $params['exam_no'];
        $class = $params['class'];
        $sequence_id = (int) $params['sequence_id'];

        $course = $this->ci->db->get_where('courses', array('course_id' => $course_id))->row_array();
        $sequence = $this->ci->db->get_where('council_sequence', array('council_sequence_id' => $sequence_id))->row_array();
        if (!$course || !$sequence) {
            return array('success' => false, 'message' => 'Course or sequence not found');
        }

        $thisClass = $this->_class_label($class);

        $this->ci->db->select('students.*, campuses.campus_name, classes.session, classes.name as class_name, payments.council_sequence_id');
        $this->ci->db->from('payments');
        $this->ci->db->join('students', 'students.student_id = payments.student_id', 'inner');
        $this->ci->db->join('classes', 'classes.class_id = students.class_id', 'inner');
        $this->ci->db->join('campuses', 'campuses.campus_id = classes.campus_id', 'left');
        $this->ci->db->join(
            'expenses',
            'expenses.student_id = students.student_id
             AND expenses.council_exam_no = ' . $this->ci->db->escape($exam_no) . '
             AND expenses.council_sequence_id = ' . $this->ci->db->escape($sequence_id) . '
             AND expenses.class = ' . $this->ci->db->escape($class),
            'left'
        );
        $this->ci->db->where('students.course_id', $course_id);
        $this->ci->db->like('payments.payment_comment', 'This fee for next exam # ' . $exam_no . ' ' . $thisClass . ' Year', 'both');
        $this->ci->db->where('(students.status = 1 OR (students.status = 0 AND expenses.expense_id IS NOT NULL))', null, false);
        $this->ci->db->group_by('students.student_id');
        $students = $this->ci->db->get()->result_array();

        $rows = array();
        foreach ($students as $student) {
            $row = $this->_format_student_row($student);
            $row['roll_record'] = $this->_get_roll_record($student['cnic'], $exam_no, $class, $course_id);
            $rows[] = $row;
        }

        $type = isset($params['type']) ? $params['type'] : null;
        $rows = $this->_apply_done_waiting_filter($rows, $type, 'documents_students', $sequence_id);

        return array(
            'success' => true,
            'data' => array(
                'page' => 'documents_students',
                'course' => $course,
                'sequence' => $sequence,
                'students' => $rows,
                'legacy_root' => rtrim(base_url(), '/'),
            ),
        );
    }

    private function _drill_result_students($user, $params)
    {
        $course_id = (int) $params['course_id'];
        $exam_no = $params['exam_no'];
        $class = $params['class'];
        $sequence_id = (int) $params['sequence_id'];
        $exam_sequence_id = isset($params['exam_sequence_id']) ? (int) $params['exam_sequence_id'] : null;

        $course = $this->ci->db->get_where('courses', array('course_id' => $course_id))->row_array();
        $sequence = $this->ci->db->get_where('council_sequence', array('council_sequence_id' => $sequence_id))->row_array();
        if (!$course || !$sequence) {
            return array('success' => false, 'message' => 'Course or sequence not found');
        }

        $thisClass = $this->_class_label($class);
        $students = $this->_students_by_exam_fee($course_id, $exam_no, $thisClass);

        $current_exam_sequence = $exam_sequence_id
            ? $this->ci->db->get_where('exam_sequence', array('id' => $exam_sequence_id))->row_array()
            : null;

        $exams = $this->ci->db
            ->select('council_exams.*, paper_types.*')
            ->from('council_exams')
            ->join('paper_types', 'paper_types.paper_type_id = council_exams.paper_type_id')
            ->join(
                'course_subjects',
                'FIND_IN_SET(course_subjects.course_subject_id, council_exams.subject_ids) > 0',
                'inner',
                false
            )
            ->where('council_exams.course_id', $course_id)
            ->where('course_subjects.subject_year', $class)
            ->group_by('council_exams.council_exam_id')
            ->get()
            ->result_array();

        $roll_rows = $this->ci->db
            ->where('council_exam_no', $exam_no)
            ->where('class', $class)
            ->where('course_id', $course_id)
            ->get('punjab_council_roll_number')
            ->result_array();

        $roll_map = array();
        $roll_ids = array();
        foreach ($roll_rows as $row) {
            $roll_map[$row['cnic']] = $row;
            $roll_ids[] = $row['id'];
        }

        $paper_result_map = array();
        if (!empty($roll_ids)) {
            $paper_results = $this->ci->db
                ->where_in('punjab_council_roll_number_id', $roll_ids)
                ->get('council_exam_papers_result')
                ->result_array();
            foreach ($paper_results as $pr) {
                $key = $pr['punjab_council_roll_number_id'] . '_' . $pr['council_exam_id'];
                $paper_result_map[$key] = $pr;
            }
        }

        $total_pass = 0;
        $total_fail = 0;
        $total_absent = 0;
        $college_totals = array();
        $paper_pass = array();
        $paper_fail = array();
        foreach ($exams as $exam) {
            $paper_pass[$exam['council_exam_id']] = 0;
            $paper_fail[$exam['council_exam_id']] = 0;
        }

        $rows = array();
        foreach ($students as $student) {
            $row = $this->_format_student_row($student);
            $roll = isset($roll_map[$student['cnic']]) ? $this->_format_roll_record($roll_map[$student['cnic']]) : null;
            $row['roll_record'] = $roll;

            $college_name = !empty($student['campus_name']) ? $student['campus_name'] : 'N/A';
            if (!isset($college_totals[$college_name])) {
                $college_totals[$college_name] = array(
                    'students' => 0, 'pass' => 0, 'fail' => 0, 'absent' => 0,
                );
            }
            $college_totals[$college_name]['students']++;

            if ($roll && !empty($roll['result_remarks'])) {
                $remarks = strtolower(trim($roll['result_remarks']));
                if ($remarks === 'pass' || $remarks === 'pass*') {
                    $total_pass++;
                    $college_totals[$college_name]['pass']++;
                } elseif (stripos($remarks, 'absent') !== false) {
                    $total_absent++;
                    $college_totals[$college_name]['absent']++;
                } elseif (stripos($remarks, 'fail') !== false) {
                    $total_fail++;
                    $college_totals[$college_name]['fail']++;
                }
            }

            $paper_results = array();
            if ($roll && !empty($roll['id'])) {
                foreach ($exams as $exam) {
                    $result_key = $roll['id'] . '_' . $exam['council_exam_id'];
                    if (isset($paper_result_map[$result_key])) {
                        $pr = $paper_result_map[$result_key];
                        $paper_results[] = array(
                            'council_exam_id' => (int) $pr['council_exam_id'],
                            'paper_name' => $exam['paper_name'],
                            'paper_type_name' => $exam['name'],
                            'type' => $pr['type'],
                            'result' => $pr['result'],
                            'marks' => $pr['marks'],
                        );
                        if ($pr['result'] === 'Pass' || $pr['result'] === 'Pass*') {
                            $paper_pass[$exam['council_exam_id']]++;
                        } elseif ($pr['result'] === 'Fail') {
                            $paper_fail[$exam['council_exam_id']]++;
                        }
                    }
                }
            }
            $row['paper_results'] = $paper_results;
            $rows[] = $row;
        }

        $total_students_all = count($students);
        $total_results = $total_pass + $total_fail + $total_absent;
        $appeared_students = $total_students_all - $total_absent;

        $type = isset($params['type']) ? $params['type'] : null;
        $rows = $this->_apply_done_waiting_filter($rows, $type, 'result_students', $sequence_id);

        $college_summary = array();
        foreach ($college_totals as $name => $ct) {
            $appeared = $ct['students'] - $ct['absent'];
            $college_summary[] = array(
                'college_name' => $name,
                'students' => $ct['students'],
                'pass' => $ct['pass'],
                'fail' => $ct['fail'],
                'absent' => $ct['absent'],
                'pass_pct_with_absent' => $ct['students'] > 0 ? round(($ct['pass'] / $ct['students']) * 100, 2) : 0,
                'pass_pct_without_absent' => $appeared > 0 ? round(($ct['pass'] / $appeared) * 100, 2) : 0,
            );
        }

        return array(
            'success' => true,
            'data' => array(
                'page' => 'result_students',
                'course' => $course,
                'sequence' => $sequence,
                'current_exam_sequence' => $current_exam_sequence,
                'exams' => $exams,
                'summary' => array(
                    'total_students' => $total_students_all,
                    'total_results' => $total_results,
                    'total_pass' => $total_pass,
                    'total_fail' => $total_fail,
                    'total_absent' => $total_absent,
                    'paper_pass' => $paper_pass,
                    'paper_fail' => $paper_fail,
                    'pass_pct_with_absent' => $total_students > 0
                        ? round(($total_results / $total_students) * 100, 2) : 0,
                    'pass_pct_without_absent' => $appeared_students > 0
                        ? round(($total_results / $appeared_students) * 100, 2) : 0,
                    'college_totals' => $college_summary,
                ),
                'students' => $rows,
                'legacy_root' => rtrim(base_url(), '/'),
                'permissions' => array(
                    'can_add_fee' => !empty($this->councils_service->permissions($user)['council_report_add_fee']),
                    'can_add_expense' => !empty($this->councils_service->permissions($user)['council_report_add_expense']),
                ),
            ),
        );
    }

    private function _student_task_done($row, $page, $sequence_id)
    {
        if ($page === 'info_students') {
            if (empty($row['inform_history']) || !is_array($row['inform_history'])) {
                return false;
            }
            foreach ($row['inform_history'] as $info) {
                if (!empty($info['informed']) && (int) $info['informed'] === 1) {
                    return true;
                }
            }
            return false;
        }

        $roll = isset($row['roll_record']) ? $row['roll_record'] : null;
        if (!$roll) {
            return false;
        }
        if ($page === 'documents_students') {
            return !empty($roll['roll_no']);
        }
        if ($page === 'result_students') {
            return !empty($roll['result_remarks']);
        }

        return false;
    }

    private function _apply_done_waiting_filter($rows, $type, $page, $sequence_id)
    {
        if ($type !== 'done' && $type !== 'waiting') {
            return $rows;
        }

        $filtered = array();
        foreach ($rows as $row) {
            $done = $this->_student_task_done($row, $page, $sequence_id);
            if ($type === 'done' && $done) {
                $filtered[] = $row;
            }
            if ($type === 'waiting' && !$done) {
                $filtered[] = $row;
            }
        }

        return $filtered;
    }

    public function student_fee_status($user, $data)
    {
        $student_id = (int) $data['student_id'];
        $course_id = (int) $data['course_id'];
        $exam_no = $data['exam_no'];
        $class = $data['class'];
        $sequence_id = (int) $data['sequence_id'];
        $exam_sequence = (int) $data['exam_sequence'];

        $student = $this->ci->db
            ->select('students.*, classes.name as class_name')
            ->from('students')
            ->join('classes', 'classes.class_id = students.class_id', 'left')
            ->where('students.student_id', $student_id)
            ->get()
            ->row_array();

        if (!$student) {
            return array('success' => false, 'message' => 'Student not found');
        }

        $sequence = $this->ci->db->get_where('council_sequence', array(
            'council_sequence_id' => $sequence_id,
        ))->row_array();

        $current_exam_sequence = $this->ci->db->get_where('exam_sequence', array(
            'id' => $exam_sequence,
        ))->row_array();

        $course = $this->ci->db->get_where('courses', array(
            'course_id' => $course_id,
        ))->row_array();

        $result_rules = $this->ci->db->get_where('council_result_rules', array(
            'course_id' => $course_id,
        ))->row_array();

        $roll_number_and_result = $this->ci->db->get_where('punjab_council_roll_number', array(
            'cnic' => $student['cnic'],
            'council_exam_no' => $exam_no,
            'class' => $class,
            'course_id' => $course_id,
        ))->row_array();

        $fee_items = array();
        $blocks = $this->_build_fee_status_blocks(
            $student,
            $course,
            $course_id,
            $exam_no,
            $class,
            $current_exam_sequence,
            $result_rules,
            $roll_number_and_result,
            $fee_items
        );

        $has_error = false;
        foreach ($blocks as $block) {
            if (!empty($block['error'])) {
                $has_error = true;
                break;
            }
        }

        return array(
            'success' => true,
            'data' => array(
                'blocks' => $blocks,
                'fee_items' => $fee_items,
                'has_error' => $has_error,
            ),
        );
    }

    // ── Mutations ─────────────────────────────────────────────────────────────

    public function add_expense($user, $data)
    {
        $image = $this->_upload_image('image', 'uploads');
        $student_ids = isset($data['student_ids']) ? $data['student_ids'] : array();
        if (!is_array($student_ids)) {
            $student_ids = array_filter(array_map('trim', explode(',', (string) $student_ids)));
        }
        if (empty($student_ids)) {
            return array('success' => false, 'message' => 'No students selected');
        }

        $sequence = $this->ci->db->get_where('council_sequence', array(
            'council_sequence_id' => (int) $data['council_sequence_id'],
        ))->row_array();
        if (!$sequence) {
            return array('success' => false, 'message' => 'Council sequence not found');
        }

        $created = 0;
        foreach ($student_ids as $student_id) {
            $st_detail = $this->ci->db
                ->join('courses', 'courses.course_id = students.course_id')
                ->join('classes', 'classes.class_id = students.class_id')
                ->get_where('students', array('student_id' => (int) $student_id))
                ->row();

            if (!$st_detail) {
                continue;
            }

            $this->ci->db->set('campus_id', $st_detail->campus_id);
            $this->ci->db->set('expense_category_id', $sequence['exp_category_id']);
            $this->ci->db->set('title', $st_detail->course_name . ' / ' . @$data['examclass'] . ' / ' . $sequence['type_name'] . ' expense for student exam no ' . @$data['council_exam_no']);
            $this->ci->db->set('date', $data['expense_date']);
            $this->ci->db->set('amount', $data['expense_amount']);
            $this->ci->db->set('purpose', $st_detail->first_name . ' ' . $st_detail->last_name . '-' . $st_detail->cnic . '-' . $st_detail->mobile);
            $this->ci->db->set('month_year', date('Y-m'));
            $this->ci->db->set('class', @$data['examclass']);
            $this->ci->db->set('council_exam_no', $data['council_exam_no']);
            $this->ci->db->set('council_sequence_id', $data['council_sequence_id']);
            $this->ci->db->set('student_id', $student_id);
            $this->ci->db->set('actual_date', date('Y-m-d H:i:s'));
            $this->ci->db->set('image', $image);
            $this->ci->db->set('payment_type', $data['payment_type']);
            $this->ci->db->set('class_id', $st_detail->class_id);
            $this->ci->db->set('roll_no', $st_detail->roll_no);
            $this->ci->db->set('add_by', $user['name']);
            $this->ci->db->set('last_edit', $user['name']);
            $this->ci->db->set('add_by_id', $user['user_id']);
            $this->ci->db->set('approved_status', 1);
            $this->ci->db->set('paid_type', $data['payment_type'] === 'cash' ? 'cash' : 'bank');
            $this->ci->db->insert('expenses');
            $insert_id = $this->ci->db->insert_id();

            if ($data['payment_type'] !== 'cash') {
                $this->ci->db->set('tagged_amount', 'tagged_amount +' . $data['expense_amount'], false);
                $this->ci->db->set('expense_id', $insert_id);
                $this->ci->db->where('id', $data['payment_type']);
                $this->ci->db->update('bank_reconciliation_statement');
            }
            $created++;
        }

        return array('success' => true, 'message' => 'Exam Sequence Expense Updated Successfully.', 'created' => $created);
    }

    public function save_informed($user, $data)
    {
        $image = $this->_upload_image('image', 'uploads');
        $student = $this->ci->db->get_where('students', array(
            'student_id' => (int) $data['student_id'],
        ))->row_array();
        if (!$student) {
            return array('success' => false, 'message' => 'Student not found');
        }

        $row = $this->ci->db->get_where('punjab_council_roll_number', array(
            'cnic' => $student['cnic'],
            'council_exam_no' => $data['exam_no'],
            'course_id' => $data['course_id'],
            'class' => $data['class'],
        ))->row_array();

        $new_info = array(
            'council_sequence_id' => $data['council_sequence_id'],
            'comment' => isset($data['comment']) ? $data['comment'] : '',
            'informed_by' => $user['name'],
            'informed_at' => date('Y-m-d H:i:s'),
            'informed' => !empty($data['informed']) ? 1 : 0,
            'image_url' => $image,
        );

        if ($row) {
            $extra_info = !empty($row['extra_info']) ? json_decode($row['extra_info'], true) : array();
            if (!is_array($extra_info)) {
                $extra_info = array();
            }
            $extra_info[] = $new_info;
            $this->ci->db->where('id', $row['id'])->update('punjab_council_roll_number', array(
                'extra_info' => json_encode($extra_info),
            ));
        } else {
            $this->ci->db->insert('punjab_council_roll_number', array(
                'council_exam_no' => $data['exam_no'],
                'class' => $data['class'],
                'cnic' => $student['cnic'],
                'name' => $student['first_name'] . ' ' . $student['last_name'],
                'address' => $student['address'],
                'course_id' => $data['course_id'],
                'extra_info' => json_encode(array($new_info)),
            ));
        }

        return array('success' => true, 'message' => 'Information saved successfully');
    }

    public function add_sequence_information($user, $data)
    {
        $image = $this->_upload_image('image', 'uploads');
        $row = array(
            'council_sequenec_id' => $data['council_sequence_id'],
            'general_comment' => isset($data['general_comment']) ? $data['general_comment'] : '',
            'created_at' => date('Y-m-d H:i:s'),
            'image' => $image,
        );
        $this->ci->db->insert('council_sequence_inform_report', $row);

        return array(
            'success' => true,
            'message' => 'Information Added Successfully',
            'id' => (int) $this->ci->db->insert_id(),
        );
    }

    public function save_roll_no($user, $data)
    {
        $save_type = isset($data['save_type']) ? $data['save_type'] : 'roll_no';
        $subdir = 'rollno_slips';
        $image = $this->_upload_image('image', $subdir, true);

        $student = $this->ci->db->get_where('students', array(
            'student_id' => (int) $data['student_id'],
        ))->row_array();
        if (!$student) {
            return array('success' => false, 'message' => 'Student not found');
        }

        $row = $this->ci->db->get_where('punjab_council_roll_number', array(
            'cnic' => $student['cnic'],
            'council_exam_no' => $data['exam_no'],
            'course_id' => $data['course_id'],
            'class' => $data['class'],
        ))->row_array();

        $payload = array(
            'council_exam_no' => $data['exam_no'],
            'class' => $data['class'],
            'cnic' => $student['cnic'],
            'name' => $student['first_name'] . ' ' . $student['last_name'],
            'address' => $student['address'],
            'course_id' => $data['course_id'],
        );

        if ($save_type === 'roll_no') {
            $payload['roll_no'] = $data['roll_no'];
            if ($image) {
                $payload['slip_image'] = $image;
            }
        } else {
            $payload['result_remarks'] = $data['roll_no'];
            if ($image) {
                $payload['result_image'] = $image;
            }
        }

        if ($row) {
            $this->ci->db->where('id', $row['id'])->update('punjab_council_roll_number', $payload);
        } else {
            $this->ci->db->insert('punjab_council_roll_number', $payload);
        }

        return array('success' => true, 'message' => 'Roll/result saved successfully');
    }

    public function save_result($user, $data)
    {
        $council_exam_id = (int) $data['council_exam_id'];
        $punjab_council_roll_number_id = (int) $data['punjab_council_roll_number_id'];
        $type = $data['type'];
        $result_direct = isset($data['result_direct']) ? $data['result_direct'] : '';
        $result_marks = isset($data['result_marks']) ? $data['result_marks'] : null;

        $row = $this->ci->db->get_where('council_exam_papers_result', array(
            'punjab_council_roll_number_id' => $punjab_council_roll_number_id,
            'council_exam_id' => $council_exam_id,
        ))->row_array();

        $pass_rule = $this->ci->db->get_where('council_exams', array(
            'council_exam_id' => $council_exam_id,
        ))->row_array();

        if ($type === 'marks') {
            $result_direct = $result_marks >= $pass_rule['passing_marks'] ? 'Pass' : 'Fail';
        }

        $payload = array(
            'council_exam_id' => $council_exam_id,
            'punjab_council_roll_number_id' => $punjab_council_roll_number_id,
            'type' => $type,
            'result' => $result_direct,
            'marks' => $result_marks,
        );

        if ($row) {
            $this->ci->db->where('id', $row['id'])->update('council_exam_papers_result', $payload);
        } else {
            $this->ci->db->insert('council_exam_papers_result', $payload);
        }

        return array('success' => true, 'message' => 'Paper result saved successfully', 'result' => $result_direct);
    }

    public function create_fee_for_all($user, $data)
    {
        $payload = isset($data['bulk_fee_payload']) ? $data['bulk_fee_payload'] : null;
        if (is_string($payload)) {
            $payload = json_decode($payload, true);
        }
        if (empty($payload) || !is_array($payload)) {
            return array('success' => false, 'message' => 'Invalid fee payload.');
        }

        $course_id = isset($data['course_id']) ? $data['course_id'] : null;
        $created = 0;

        foreach ($payload as $studentData) {
            $student_id = $studentData['student_id'];
            if (empty($studentData['fees']) || !is_array($studentData['fees'])) {
                continue;
            }

            foreach ($studentData['fees'] as $fee) {
                if ($fee['type'] === 'Extra fee') {
                    $already = $this->ci->db->get_where('payments', array(
                        'student_id' => $student_id,
                        'exam_sequence_id' => $fee['next_exam_sequence_id'],
                        'exam_class' => $fee['class'],
                        'council_sequence_id' => $fee['council_sequence_id'],
                    ))->row_array();

                    if (!$already) {
                        $challan_no = $this->_get_challan_no();
                        $this->ci->db->insert('payments', array(
                            'amount' => $fee['amount'],
                            'dead_line' => $fee['dead_line'],
                            'student_id' => $student_id,
                            'payment_plan' => 'Custom Plan',
                            'payment_comment' => $fee['comment'],
                            'challan_no' => $challan_no,
                            'exam_class' => $fee['class'],
                            'exam_sequence_id' => $fee['next_exam_sequence_id'],
                            'council_sequence_id' => $fee['council_sequence_id'],
                            'add_by' => $user['name'],
                            'last_edit' => $user['name'],
                        ));
                        $created++;
                    }
                } else {
                    $challan_no = $this->_get_challan_no();
                    $insert = array(
                        'student_id' => $student_id,
                        'course_id' => $course_id,
                        'amount' => $fee['amount'],
                        'exam_class' => $fee['class'],
                        'exam_sequence_id' => $fee['next_exam_sequence_id'],
                        'council_sequence_id' => $fee['council_sequence_id'],
                        'dead_line' => $fee['dead_line'],
                        'paid' => 0,
                        'discount' => 0,
                        'remaining_installment_amount' => 0,
                        'extra_amount' => 0,
                        'shifted_installment' => 0,
                        'removed_previous_fine' => 0,
                        'shifted_previous_fine' => 0,
                        'removed_fine' => 0,
                        'shifted_fine' => 0,
                        'college_fee' => 0,
                        'clear_college_fee' => 0,
                        'paid_challans' => '',
                        'challan_no' => $challan_no,
                        'scan_challan' => '',
                        'online_scan_challan' => '',
                        'upload_scan_challan' => 0,
                        'fine_application' => '',
                        'online_fine_application' => '',
                        'upload_fine_application' => 0,
                        'payment_plan' => 'consulation fee',
                        'payment_comment' => 'This fee for next exam # ' . $fee['exam_no'] . ' ' . $this->_ordinal($fee['class']) . ' ' . $fee['course_type'],
                        'system_comment' => 'Council auto-generated fee',
                        'add_by' => $user['name'],
                    );

                    $already = $this->ci->db->get_where('payments', array(
                        'student_id' => $student_id,
                        'exam_sequence_id' => $fee['next_exam_sequence_id'],
                        'exam_class' => $fee['class'],
                        'council_sequence_id' => $fee['council_sequence_id'],
                    ))->row_array();

                    if (!$already) {
                        $this->ci->db->insert('payments', $insert);
                        $created++;
                    }
                }
            }
        }

        return array('success' => true, 'message' => 'Fees created successfully for all valid students.', 'created' => $created);
    }

    // ── Private: fee status blocks (port of student_fee_status_logic.php) ─────

    private function _build_fee_status_blocks(
        $student,
        $course,
        $course_id,
        $exam_no,
        $class,
        $current_exam_sequence,
        $result_rules,
        $roll_number_and_result,
        &$fee_items
    ) {
        $blocks = array();
        $is_year = $course['course_type'] === 'Annual' ? 'Year' : $course['course_type'];

        if (empty($roll_number_and_result['result_remarks'])) {
            $blocks[] = array('type' => 'empty', 'text' => '-');
            return $blocks;
        }

        $remarks = strtolower($roll_number_and_result['result_remarks']);
        $current_exam = $exam_no;

        if ($remarks === 'pass' || $remarks === 'pass*') {
            $blocks[] = array(
                'type' => 'status',
                'text' => 'Pass in Exam No ' . $current_exam . ' of ' . $this->_ordinal($current_exam_sequence['class']) . ' ' . $is_year,
            );

            if ($course['course_duration_year'] > $current_exam_sequence['class']) {
                $this->ci->db->where('course_id', $course_id);
                $this->ci->db->where('status', 'Active');
                $this->ci->db->where('class', ($current_exam_sequence['class'] + 1));
                $type = isset($result_rules['annual_students_can_appear_in']) ? $result_rules['annual_students_can_appear_in'] : '';
                if (!empty($type) && $type !== 'both') {
                    $this->ci->db->where('first_year_type', $type);
                }
                $next_exam = $this->ci->db->get('exam_sequence')->row_array();

                if ($next_exam) {
                    $tasks = $this->ci->db
                        ->join('councils', 'councils.council_id = council_sequence.council_id', 'inner')
                        ->where('course_id', $next_exam['course_id'])
                        ->where('action_type', 'fee')
                        ->where_in('recurring', array('Each Exam', 'Every Semester', 'After Chances'))
                        ->order_by("STR_TO_DATE(last_date,'%d/%m')", 'ASC', false)
                        ->get('council_sequence')
                        ->result_array();

                    foreach ($tasks as $task) {
                        $blocks = array_merge($blocks, $this->_fee_task_blocks(
                            $student, $task, $next_exam, $current_exam_sequence, $is_year, $fee_items, true
                        ));
                    }
                } else {
                    $blocks[] = array(
                        'type' => 'error',
                        'error' => 'Next Exam is Not Created. Please create Next Exam Sequence.',
                    );
                }
            } else {
                $blocks[] = array('type' => 'degree_clear', 'text' => 'Your Degree is Clear.');

                $tasks = $this->ci->db
                    ->join('councils', 'councils.council_id = council_sequence.council_id', 'inner')
                    ->where('course_id', $current_exam_sequence['course_id'])
                    ->where('action_type', 'fee')
                    ->where('recurring', 'End of Degree')
                    ->order_by("STR_TO_DATE(last_date,'%d/%m')", 'ASC', false)
                    ->get('council_sequence')
                    ->result_array();

                foreach ($tasks as $task) {
                    $blocks = array_merge($blocks, $this->_end_of_degree_fee_blocks(
                        $student, $task, $current_exam_sequence, $is_year, $fee_items
                    ));
                }
            }
        } elseif (stripos($remarks, 'fail') !== false || stripos($remarks, 'absent') !== false) {
            $blocks[] = array(
                'type' => 'status',
                'text' => 'Fail in Exam No ' . $current_exam . ' of ' . $this->_ordinal($current_exam_sequence['class']) . ' ' . $is_year,
            );

            $this->ci->db->where('course_id', $course_id);
            $this->ci->db->where('first_year >', $current_exam);
            $this->ci->db->where('class', $current_exam_sequence['class']);
            $type = isset($result_rules['supplementary_students_can_appear_in']) ? $result_rules['supplementary_students_can_appear_in'] : '';
            if (!empty($type) && $type !== 'both') {
                $this->ci->db->where('first_year_type', $type);
            }
            $this->ci->db->where('status', 'Active');
            $this->ci->db->order_by('first_year', 'ASC');
            $next_exam = $this->ci->db->get('exam_sequence')->row_array();

            if ($next_exam) {
                $tasks = $this->ci->db
                    ->join('councils', 'councils.council_id = council_sequence.council_id', 'inner')
                    ->where('course_id', $next_exam['course_id'])
                    ->where('action_type', 'fee')
                    ->where_in('recurring', array('Each Exam', 'After Chances'))
                    ->order_by("STR_TO_DATE(last_date,'%d/%m')", 'ASC', false)
                    ->get('council_sequence')
                    ->result_array();

                foreach ($tasks as $task) {
                    $blocks = array_merge($blocks, $this->_fee_task_blocks(
                        $student, $task, $next_exam, $current_exam_sequence, $is_year, $fee_items, false
                    ));
                }
            } else {
                $blocks[] = array(
                    'type' => 'error',
                    'error' => 'Next Exam is Not Created. Please create Next Exam Sequence.',
                );
            }

            if (!empty($result_rules['promote_on_supplementary']) && (int) $result_rules['promote_on_supplementary'] === 1) {
                $this->ci->db->where('course_id', $course_id);
                $this->ci->db->where('first_year >', $current_exam);
                $this->ci->db->where('class', ($current_exam_sequence['class'] + 1));
                $type = isset($result_rules['annual_students_can_appear_in']) ? $result_rules['annual_students_can_appear_in'] : '';
                if (!empty($type) && $type !== 'both') {
                    $this->ci->db->where('first_year_type', $type);
                }
                $next_exam = $this->ci->db->get('exam_sequence')->row_array();

                if ($next_exam) {
                    $tasks = $this->ci->db
                        ->join('councils', 'councils.council_id = council_sequence.council_id', 'inner')
                        ->where('course_id', $next_exam['course_id'])
                        ->where('action_type', 'fee')
                        ->where_in('recurring', array('Each Exam', 'Every Semester'))
                        ->order_by("STR_TO_DATE(last_date,'%d/%m')", 'ASC', false)
                        ->get('council_sequence')
                        ->result_array();

                    foreach ($tasks as $task) {
                        $blocks = array_merge($blocks, $this->_promote_fee_blocks(
                            $student, $task, $next_exam, $current_exam_sequence, $is_year, $fee_items
                        ));
                    }
                } else {
                    $blocks[] = array(
                        'type' => 'error',
                        'error' => 'Next Exam is Not Created. Please create Next Exam Sequence.',
                    );
                }
            }
        } else {
            $blocks[] = array('type' => 'empty', 'text' => '-');
        }

        return $blocks;
    }

    private function _fee_task_blocks($student, $task, $next_exam, $current_exam_sequence, $is_year, &$fee_items, $is_pass_promote)
    {
        $blocks = array();
        $should_check_fee = true;

        if ($task['recurring'] === 'After Chances') {
            $session_rows = $this->ci->db->get_where('punjab_council_roll_number', array(
                'cnic' => $student['cnic'],
            ))->result_array();
            if (count($session_rows) < $task['no_of_chances']) {
                $should_check_fee = false;
            }
        }

        if (!$should_check_fee) {
            return $blocks;
        }

        if ($task['recurring'] === 'After Chances') {
            $description = 'This Fee for ' . $task['type_name'];
        } elseif ($is_pass_promote) {
            $description = 'This Fee for ' . $this->_ordinal($current_exam_sequence['class'] + 1) . ' ' . $is_year
                . ' Exam No ' . $next_exam['first_year'] . ' ' . $next_exam['first_year_type'];
        } else {
            $description = 'This Fee for ' . $this->_ordinal($current_exam_sequence['class']) . ' ' . $is_year
                . ' Exam No ' . $next_exam['first_year'] . ' ' . $next_exam['first_year_type'];
        }

        $today = date('Y-m-d');
        $fee = $this->ci->db
            ->where('sequence_fee_id', $task['council_sequence_id'])
            ->where('exam_sequence_id', $next_exam['id'])
            ->where('to_date >=', $today)
            ->order_by('from_date', 'ASC')
            ->get('council_sequence_fee_rules')
            ->row_array();

        $block = array(
            'type' => 'fee_task',
            'task_name' => $task['type_name'],
            'description' => $description,
            'recurring' => $task['recurring'],
            'fee_rule' => null,
            'fee_created' => false,
            'paid' => false,
            'paid_at' => null,
            'error' => null,
        );

        if ($fee) {
            $block['fee_rule'] = array(
                'exam_fee' => $fee['exam_fee'],
                'from_date' => $fee['from_date'],
                'to_date' => $fee['to_date'],
            );

            $already = $this->ci->db->get_where('payments', array(
                'student_id' => $student['student_id'],
                'exam_sequence_id' => $next_exam['id'],
                'exam_class' => $next_exam['class'],
                'council_sequence_id' => $task['council_sequence_id'],
            ))->row_array();

            if ($already) {
                $block['fee_created'] = true;
                $block['paid'] = ((int) $already['paid'] === 1);
                if ($block['paid']) {
                    $block['paid_at'] = $already['updated_at'];
                }
            } else {
                $fee_item = array(
                    'next_exam_sequence_id' => $next_exam['id'],
                    'council_sequence_id' => $task['council_sequence_id'],
                    'class' => $next_exam['class'],
                    'dead_line' => $fee['from_date'],
                    'course_type' => $is_year,
                    'exam_no' => $next_exam['first_year'],
                    'amount' => $fee['exam_fee'],
                    'type' => 'College fee',
                );
                if ($task['recurring'] === 'After Chances') {
                    $fee_item['type'] = 'Extra fee';
                    $fee_item['comment'] = 'This Fee For ' . $task['type_name'];
                }
                $fee_items[] = $fee_item;
            }
        } else {
            $block['error'] = 'Fee is Not Created. Please create Fee for ' . $task['type_name'] . '.';
        }

        $blocks[] = $block;
        return $blocks;
    }

    private function _end_of_degree_fee_blocks($student, $task, $current_exam_sequence, $is_year, &$fee_items)
    {
        $blocks = array();
        $today = date('Y-m-d');
        $fee = $this->ci->db
            ->where('sequence_fee_id', $task['council_sequence_id'])
            ->where('exam_sequence_id', $current_exam_sequence['id'])
            ->where('to_date >=', $today)
            ->order_by('from_date', 'ASC')
            ->get('council_sequence_fee_rules')
            ->row_array();

        $block = array(
            'type' => 'fee_task',
            'task_name' => $task['type_name'],
            'description' => 'This Fee for ' . $task['type_name'],
            'recurring' => $task['recurring'],
            'fee_rule' => null,
            'fee_created' => false,
            'paid' => false,
            'paid_at' => null,
            'error' => null,
        );

        if ($fee) {
            $block['fee_rule'] = array(
                'exam_fee' => $fee['exam_fee'],
                'from_date' => $fee['from_date'],
                'to_date' => $fee['to_date'],
            );

            $already = $this->ci->db->get_where('payments', array(
                'student_id' => $student['student_id'],
                'exam_sequence_id' => $current_exam_sequence['id'],
                'exam_class' => $current_exam_sequence['class'],
                'council_sequence_id' => $task['council_sequence_id'],
            ))->row_array();

            if ($already) {
                $block['fee_created'] = true;
            } else {
                $fee_items[] = array(
                    'next_exam_sequence_id' => $current_exam_sequence['id'],
                    'council_sequence_id' => $task['council_sequence_id'],
                    'class' => $current_exam_sequence['class'],
                    'dead_line' => $fee['from_date'],
                    'course_type' => $is_year,
                    'exam_no' => $current_exam_sequence['first_year'],
                    'amount' => $fee['exam_fee'],
                    'type' => 'Extra fee',
                    'comment' => 'This Fee For ' . $task['type_name'],
                );
            }
        } else {
            $block['error'] = 'Fee is Not Created. Please create Fee for ' . $task['type_name'] . '.';
        }

        $blocks[] = $block;
        return $blocks;
    }

    private function _promote_fee_blocks($student, $task, $next_exam, $current_exam_sequence, $is_year, &$fee_items)
    {
        $blocks = array();
        $today = date('Y-m-d');
        $fee = $this->ci->db
            ->where('sequence_fee_id', $task['council_sequence_id'])
            ->where('exam_sequence_id', $next_exam['id'])
            ->where('to_date >=', $today)
            ->order_by('from_date', 'ASC')
            ->get('council_sequence_fee_rules')
            ->row_array();

        $block = array(
            'type' => 'fee_task',
            'task_name' => $task['type_name'],
            'description' => 'This Fee for ' . $this->_ordinal($current_exam_sequence['class'] + 1) . ' ' . $is_year
                . ' Exam No ' . $next_exam['first_year'] . ' ' . $next_exam['first_year_type'],
            'recurring' => $task['recurring'],
            'fee_rule' => null,
            'fee_created' => false,
            'paid' => false,
            'paid_at' => null,
            'error' => null,
        );

        if ($fee) {
            $block['fee_rule'] = array(
                'exam_fee' => $fee['exam_fee'],
                'from_date' => $fee['from_date'],
                'to_date' => $fee['to_date'],
            );

            $already = $this->ci->db->get_where('payments', array(
                'student_id' => $student['student_id'],
                'exam_sequence_id' => $next_exam['id'],
                'exam_class' => $next_exam['class'],
                'council_sequence_id' => $task['council_sequence_id'],
            ))->row_array();

            if ($already) {
                $block['fee_created'] = true;
            } else {
                $fee_items[] = array(
                    'next_exam_sequence_id' => $next_exam['id'],
                    'council_sequence_id' => $task['council_sequence_id'],
                    'class' => $next_exam['class'],
                    'dead_line' => $fee['from_date'],
                    'course_type' => $is_year,
                    'exam_no' => $next_exam['first_year'],
                    'amount' => $fee['exam_fee'],
                    'type' => 'College fee',
                );
            }
        } else {
            $block['error'] = 'Fee is Not Created. Please create Fee for ' . $task['type_name'] . '.';
        }

        $blocks[] = $block;
        return $blocks;
    }

    // ── Private: helpers ──────────────────────────────────────────────────────

    private function _students_by_exam_fee($course_id, $exam_no, $thisClass)
    {
        $this->ci->db->select('students.*, campuses.campus_name, classes.session, classes.name as class_name, payments.council_sequence_id, payments.paid');
        $this->ci->db->from('payments');
        $this->ci->db->join('students', 'students.student_id = COALESCE(NULLIF(payments.student_id, 0), payments.custom_student_id)', 'inner');
        $this->ci->db->join('classes', 'classes.class_id=students.class_id', 'inner');
        $this->ci->db->join('campuses', 'campuses.campus_id=classes.campus_id', 'left');
        $this->ci->db->where('students.status', 1);
        $this->ci->db->where('students.course_id', $course_id);
        $this->ci->db->like('payments.payment_comment', 'This fee for next exam # ' . $exam_no . ' ' . $thisClass . ' Year', 'both');
        $this->ci->db->group_by('students.student_id');
        return $this->ci->db->get()->result_array();
    }

    private function _format_student_row($student)
    {
        return array(
            'student_id' => (int) $student['student_id'],
            'roll_no' => $student['roll_no'],
            'first_name' => $student['first_name'],
            'last_name' => $student['last_name'],
            'father_name' => $student['father_name'],
            'cnic' => $student['cnic'],
            'mobile' => isset($student['mobile']) ? $student['mobile'] : '',
            'emergency_no' => isset($student['emergency_no']) ? $student['emergency_no'] : '',
            'class_name' => $student['class_name'],
            'session' => isset($student['session']) ? $student['session'] : '',
            'campus_name' => isset($student['campus_name']) ? $student['campus_name'] : '',
            'status' => (int) $student['status'],
            'paid' => isset($student['paid']) ? (int) $student['paid'] : null,
            'council_sequence_id' => isset($student['council_sequence_id']) ? (int) $student['council_sequence_id'] : null,
        );
    }

    private function _format_roll_record($row)
    {
        if (!$row) {
            return null;
        }
        return array(
            'id' => (int) $row['id'],
            'roll_no' => isset($row['roll_no']) ? $row['roll_no'] : '',
            'result_remarks' => isset($row['result_remarks']) ? $row['result_remarks'] : '',
            'slip_image' => isset($row['slip_image']) ? $row['slip_image'] : '',
            'result_image' => isset($row['result_image']) ? $row['result_image'] : '',
            'extra_info' => !empty($row['extra_info']) ? json_decode($row['extra_info'], true) : array(),
        );
    }

    private function _get_roll_record($cnic, $exam_no, $class, $course_id)
    {
        $row = $this->ci->db->get_where('punjab_council_roll_number', array(
            'cnic' => $cnic,
            'council_exam_no' => $exam_no,
            'class' => $class,
            'course_id' => $course_id,
        ))->row_array();
        return $this->_format_roll_record($row);
    }

    private function _get_student_expense($student_id, $exam_no, $sequence_id, $class)
    {
        $expense = $this->ci->db->get_where('expenses', array(
            'student_id' => $student_id,
            'council_exam_no' => $exam_no,
            'council_sequence_id' => $sequence_id,
            'class' => $class,
        ))->row_array();
        if (!$expense) {
            return null;
        }
        return array(
            'expense_id' => (int) $expense['expense_id'],
            'purpose' => $expense['purpose'],
            'date' => $expense['date'],
            'amount' => $expense['amount'],
            'payment_type' => $expense['payment_type'],
            'image' => isset($expense['image']) ? $expense['image'] : '',
        );
    }

    private function _inform_reports($sequence_id)
    {
        $reports = $this->ci->db
            ->where('council_sequenec_id', $sequence_id)
            ->order_by('id', 'desc')
            ->get('council_sequence_inform_report')
            ->result_array();

        $out = array();
        foreach ($reports as $r) {
            $out[] = array(
                'id' => (int) $r['id'],
                'general_comment' => $r['general_comment'],
                'image' => isset($r['image']) ? $r['image'] : '',
                'created_at' => $r['created_at'],
            );
        }
        return $out;
    }

    private function _inform_history_for_sequence($roll, $sequence_id)
    {
        if (!$roll || empty($roll['extra_info']) || !is_array($roll['extra_info'])) {
            return array();
        }
        $history = array();
        foreach ($roll['extra_info'] as $info) {
            if ((int) $info['council_sequence_id'] === (int) $sequence_id) {
                $history[] = array(
                    'comment' => isset($info['comment']) ? $info['comment'] : '',
                    'informed_by' => isset($info['informed_by']) ? $info['informed_by'] : '',
                    'informed_at' => isset($info['informed_at']) ? $info['informed_at'] : '',
                    'informed' => !empty($info['informed']) ? 1 : 0,
                    'image_url' => isset($info['image_url']) ? $info['image_url'] : '',
                );
            }
        }
        return $history;
    }

    private function _council_bank_accounts()
    {
        $rows = $this->ci->db
            ->get_where('bank_reconciliation_statement', 'is_council_fee = 1 and CAST(REPLACE(debit,\',\',\'\') as SIGNED) > tagged_amount')
            ->result_array();

        $out = array();
        foreach ($rows as $row) {
            $out[] = array(
                'id' => (int) $row['id'],
                'description' => $row['description'],
                'debit' => $row['debit'],
                'tagged_amount' => $row['tagged_amount'],
            );
        }
        return $out;
    }

    private function _petty_cash_balance($user)
    {
        $query = $this->ci->db->get_where('petty_cash_college_wise', array(
            'assign_to' => $user['user_id'],
        ))->result_array();

        if (count($query) > 0) {
            $this->ci->load->helper('custom');
            return (float) my_pettycash();
        }
        return 0;
    }

    private function _upload_image($field, $subdir, $prefix_subdir = false)
    {
        if (empty($_FILES[$field]['name'])) {
            return '';
        }

        if (!is_dir(FCPATH . $subdir)) {
            mkdir(FCPATH . $subdir, 0777, true);
        }

        $this->ci->load->library('upload');
        $config = array(
            'upload_path' => FCPATH . $subdir,
            'allowed_types' => 'gif|jpg|png|jpeg',
        );
        $this->ci->upload->initialize($config);

        if (!$this->ci->upload->do_upload($field)) {
            return '';
        }

        $upload_data = $this->ci->upload->data();
        if ($prefix_subdir) {
            return $subdir . '/' . $upload_data['file_name'];
        }
        return $upload_data['file_name'];
    }

    private function _get_challan_no()
    {
        $random_number = rand(1000, 999999999);
        $check = $this->ci->db->get_where('payments', array('challan_no' => $random_number))->result_array();
        if (count($check) > 0) {
            return $this->_get_challan_no();
        }
        return $random_number;
    }

    private function _class_label($class)
    {
        return ((int) $class === 1) ? '1st' : '2nd';
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

    private function _course_type_label($course_type)
    {
        return $course_type === 'Annual' ? 'Year' : $course_type;
    }

    private function _payment_comment($exam_no, $class, $course_type)
    {
        return 'This fee for next exam # ' . $exam_no . ' ' . $this->_ordinal($class) . ' ' . $this->_course_type_label($course_type);
    }
}
