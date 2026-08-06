<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Expenses_service {

    /** @var CI_Controller */
    private $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
    }

    private function access_row($user)
    {
        if (!$user || $user['role'] === 'Admin') {
            return array();
        }
        return $this->ci->db->get_where('access', array('user_id' => $user['user_id']))->row_array() ?: array();
    }

    public function can_access($user)
    {
        if (!$user) {
            return false;
        }
        if ($user['role'] === 'Admin') {
            return true;
        }
        $acc = $this->access_row($user);
        return !empty($acc['expense_sidebar']);
    }

    public function permissions($user)
    {
        $is_admin = $user && $user['role'] === 'Admin';
        $acc = $is_admin ? array() : $this->access_row($user);

        return array(
            'is_admin' => $is_admin,
            'sidebar' => $is_admin || !empty($acc['expense_sidebar']),
            'add' => $is_admin || !empty($acc['expense_add']),
            'all' => $is_admin || !empty($acc['expense_all']),
            'edit' => $is_admin || !empty($acc['expense_edit']),
            'delete' => $is_admin || !empty($acc['expense_delete']),
            'category' => $is_admin || !empty($acc['expense_category']),
            'approval' => $is_admin || !empty($acc['expense_approval']),
            'second_approval' => $is_admin || !empty($acc['expense_second_approval']),
            'view_user' => $is_admin || !empty($acc['expense_view_user']),
        );
    }

    private function campus_ids_from_access($acc)
    {
        $raw = isset($acc['campus_ids']) ? (string) $acc['campus_ids'] : '';
        return array_values(array_filter(array_map('intval', explode(',', $raw))));
    }

    private function expense_campus_ids_from_access($acc)
    {
        $raw = isset($acc['expense_campus_ids']) ? (string) $acc['expense_campus_ids'] : '';
        $ids = array_values(array_filter(array_map('intval', explode(',', $raw))));
        if (empty($ids) && !empty($acc['campus_ids'])) {
            return $this->campus_ids_from_access($acc);
        }
        return $ids;
    }

    public function meta($user)
    {
        $is_admin = $user && $user['role'] === 'Admin';
        $acc = $is_admin ? array() : $this->access_row($user);
        $role = $user ? $user['role'] : '';

        $this->ci->db->select('*')->from('campuses')->where('status', 1)->order_by('campus_name', 'ASC');
        if (!$is_admin && $role !== 'Accounts') {
            $ids = $this->campus_ids_from_access($acc);
            if (!empty($ids)) {
                $this->ci->db->where_in('campus_id', $ids);
            }
        }
        $list_campuses = $this->ci->db->get()->result_array();

        $this->ci->db->select('*')->from('campuses')->where('status', 1)->order_by('campus_name', 'ASC');
        if (!$is_admin && $role !== 'Accounts') {
            $add_ids = $this->expense_campus_ids_from_access($acc);
            if (!empty($add_ids)) {
                $this->ci->db->where_in('campus_id', $add_ids);
            }
        }
        $add_campuses = $this->ci->db->get()->result_array();

        return array(
            'permissions' => $this->permissions($user),
            'campuses' => $list_campuses,
            'add_campuses' => $add_campuses,
            'legacy_root' => rtrim(base_url(), '/'),
        );
    }

    private function normalize_setype($setype)
    {
        $setype = trim((string) $setype);
        if ($setype === 'Pending' || $setype === '0') {
            return '0';
        }
        if ($setype === 'Approved' || $setype === '1') {
            return '1';
        }
        if ($setype === 'Rejected' || $setype === '2') {
            return '2';
        }
        if ($setype === 'Reversed' || $setype === '3') {
            return '3';
        }
        return '';
    }

    private function receipt_url($expense)
    {
        if (!empty($expense['online_image'])) {
            return str_replace(
                'https://shahbazcollegebucket.s3.ca-central-1.amazonaws.com',
                'https://d10iw6eujrfvyr.cloudfront.net',
                $expense['online_image']
            );
        }
        if (!empty($expense['image'])) {
            return rtrim(base_url(), '/') . '/uploads/' . $expense['image'];
        }
        return null;
    }

    private function enrich_row($expense)
    {
        $category_label = isset($expense['category_name']) ? $expense['category_name'] : '';
        $title_extra = '';

        $cat_id = (int) $expense['expense_category_id'];
        if ($cat_id === 9) {
            $user = $this->ci->db->get_where('users', array('user_id' => $expense['user_id']))->row_array();
            if ($user) {
                $category_label .= ' — ' . trim($user['first_name'] . ' ' . $user['last_name']);
            }
        } elseif ($cat_id === 36) {
            $payrolls = $this->ci->db
                ->select('users.first_name, users.last_name, payroll.earned_salary')
                ->from('payroll')
                ->join('users', 'users.user_id = payroll.user_id')
                ->where('payroll.expense_id', $expense['expense_id'])
                ->get()
                ->result_array();
            $parts = array();
            foreach ($payrolls as $payroll) {
                $parts[] = trim($payroll['first_name'] . ' ' . $payroll['last_name']) . ' (' . $payroll['earned_salary'] . ')';
            }
            if (!empty($parts)) {
                $category_label = implode('; ', $parts);
            }
        }

        if ($cat_id === 1) {
            $title_extra = trim(
                'Rickshaw: ' . $expense['rickshaw_number'] . '; Driver: ' . $expense['driver_phone']
            );
        } elseif ($cat_id === 13 && !empty($expense['student_id'])) {
            $student = $this->ci->db->get_where('students', array('student_id' => $expense['student_id']))->row_array();
            if ($student) {
                $title_extra = trim(
                    $student['first_name'] . ' ' . $student['last_name'] .
                    ' (' . $student['cnic'] . '); Class: ' . $expense['class'] .
                    ' Year; Exam: ' . $expense['council_exam_no']
                );
            }
        }

        $bank_label = '';
        if ($expense['paid_type'] === 'bank') {
            $ban = $this->ci->db
                ->select('accounts.account_title, accounts.account_name')
                ->from('bank_reconciliation_statement')
                ->join('accounts', 'accounts.id = bank_reconciliation_statement.account_id')
                ->group_start()
                ->where('bank_reconciliation_statement.expense_id', $expense['expense_id'])
                ->or_where('bank_reconciliation_statement.salary_expense_ids', $expense['expense_id'])
                ->group_end()
                ->get()
                ->row_array();
            if ($ban) {
                $bank_label = trim($ban['account_title'] . ' ' . $ban['account_name']);
            }
        }

        $status = (string) $expense['approved_status'];
        $status_label = 'Pending';
        if ($status === '1') {
            $status_label = 'Approved';
        } elseif ($status === '2') {
            $status_label = 'Rejected';
        } elseif ($status === '3') {
            $status_label = 'Reversed';
        }

        return array(
            'expense_id' => (int) $expense['expense_id'],
            'campus_id' => (int) $expense['campus_id'],
            'campus_name' => $expense['campus_name'],
            'expense_category_id' => $cat_id,
            'category_label' => $category_label,
            'title' => $expense['title'],
            'title_extra' => $title_extra,
            'purpose' => $expense['purpose'],
            'amount' => $expense['amount'],
            'date' => $expense['date'],
            'actual_date' => $expense['actual_date'],
            'receipt_url' => $this->receipt_url($expense),
            'add_by' => $expense['add_by'],
            'add_by_id' => (int) $expense['add_by_id'],
            'last_edit' => $expense['last_edit'],
            'approved_status' => $status,
            'status_label' => $status_label,
            'rev_status' => $expense['rev_status'],
            'rev_reason' => $expense['rev_reason'],
            'paid_type' => $expense['paid_type'],
            'paid_type_label' => trim($bank_label . ' ' . $expense['paid_type']),
            'is_payroll' => $cat_id === 36,
        );
    }

    public function list_expenses($user, $filters = array())
    {
        $is_admin = $user && $user['role'] === 'Admin';
        $acc = $is_admin ? array() : $this->access_row($user);
        $campus_ids = $this->campus_ids_from_access($acc);

        $from_date = !empty($filters['from_date']) ? $filters['from_date'] : date('Y-m-d');
        $to_date = !empty($filters['to_date']) ? $filters['to_date'] : date('Y-m-d');
        $campus_id = isset($filters['campus_id']) ? (int) $filters['campus_id'] : 0;
        $setype = $this->normalize_setype(isset($filters['setype']) ? $filters['setype'] : '');

        $this->ci->db->select('expenses.*, expense_category.name as category_name, campuses.campus_name');
        $this->ci->db->from('expenses');
        $this->ci->db->join('expense_category', 'expense_category.expense_category_id = expenses.expense_category_id', 'left');
        $this->ci->db->join('campuses', 'campuses.campus_id = expenses.campus_id', 'left');
        $this->ci->db->where(array('expenses.date >=' => $from_date, 'expenses.date <=' => $to_date));

        if ($setype !== '') {
            $this->ci->db->where('expenses.approved_status', $setype);
        }
        if (!$is_admin && empty($acc['expense_view_user'])) {
            $this->ci->db->where('expenses.add_by_id', (int) $user['user_id']);
        }
        if (!$is_admin) {
            if ($campus_id <= 0 && !empty($campus_ids)) {
                $this->ci->db->where_in('expenses.campus_id', $campus_ids);
            }
        }
        if ($campus_id > 0) {
            $this->ci->db->where('expenses.campus_id', $campus_id);
        }

        $this->ci->db->order_by('expenses.expense_id', 'DESC');
        $raw = $this->ci->db->get()->result_array();

        $rows = array();
        $counts = array('pending' => 0, 'approved' => 0, 'rejected' => 0, 'reversed' => 0);
        foreach ($raw as $expense) {
            $row = $this->enrich_row($expense);
            $rows[] = $row;
            if ($row['approved_status'] === '0') {
                $counts['pending']++;
            } elseif ($row['approved_status'] === '1') {
                $counts['approved']++;
            } elseif ($row['approved_status'] === '2') {
                $counts['rejected']++;
            } elseif ($row['approved_status'] === '3') {
                $counts['reversed']++;
            }
        }

        $this->ci->db->select_sum('amount');
        $this->ci->db->from('expenses');
        $this->ci->db->where(array('date >=' => $from_date, 'date <=' => $to_date));
        if (!$is_admin && empty($acc['expense_view_user'])) {
            $this->ci->db->where('add_by_id', (int) $user['user_id']);
        }
        if (!$is_admin) {
            if ($campus_id <= 0 && !empty($campus_ids)) {
                $this->ci->db->where_in('campus_id', $campus_ids);
            }
        }
        if ($campus_id > 0) {
            $this->ci->db->where('campus_id', $campus_id);
        }
        if ($setype !== '') {
            $this->ci->db->where('approved_status', $setype);
        }
        $total_row = $this->ci->db->get()->row_array();

        return array(
            'from_date' => $from_date,
            'to_date' => $to_date,
            'setype' => $setype,
            'rows' => $rows,
            'total_amount' => isset($total_row['amount']) ? $total_row['amount'] : 0,
            'counts' => $counts,
        );
    }

    public function expense_history($campus_id, $category_id, $limit = 5)
    {
        $campus_id = (int) $campus_id;
        $category_id = (int) $category_id;
        if ($campus_id <= 0 || $category_id <= 0) {
            return array();
        }

        $this->ci->db->select('expenses.*, expense_category.name as category_name, campuses.campus_name');
        $this->ci->db->from('expenses');
        $this->ci->db->join('expense_category', 'expense_category.expense_category_id = expenses.expense_category_id', 'inner');
        $this->ci->db->join('campuses', 'campuses.campus_id = expenses.campus_id', 'inner');
        $this->ci->db->where(array(
            'expenses.expense_category_id' => $category_id,
            'expenses.campus_id' => $campus_id,
        ));
        $this->ci->db->order_by('expenses.expense_id', 'DESC');
        $this->ci->db->limit((int) $limit);
        $raw = $this->ci->db->get()->result_array();

        $out = array();
        foreach ($raw as $expense) {
            $out[] = $this->enrich_row($expense);
        }
        return $out;
    }

    public function change_approve_status($user, $expense_id, $status, $last_edit)
    {
        $p = $this->permissions($user);
        if (empty($p['approval'])) {
            return array('success' => false, 'message' => 'Expense approval permission required');
        }

        $expense_id = (int) $expense_id;
        $status = (string) $status;
        if ($expense_id <= 0 || !in_array($status, array('0', '1', '2', '3'), true)) {
            return array('success' => false, 'message' => 'Invalid request');
        }

        $exp = $this->ci->db->get_where('expenses', array('expense_id' => $expense_id))->row_array();
        if (!$exp) {
            return array('success' => false, 'message' => 'Expense not found');
        }

        $this->ci->db->set('approved_status', $status);
        $this->ci->db->set('last_edit', $last_edit);
        $this->ci->db->where('expense_id', $expense_id);
        $this->ci->db->update('expenses');

        if ($status === '2') {
            $this->ci->db->set('expense_id', $exp['expense_id']);
            $this->ci->db->set('amount', $exp['amount']);
            $this->ci->db->set('reverse_by', (int) $user['user_id']);
            $this->ci->db->set('created_at', date('Y-m-d H:i:s'));
            $this->ci->db->insert('cash_reversal');

            $cos = $this->ci->db->get_where('bank_reconciliation_statement', array('expense_id' => $expense_id))->result_array();
            if (count($cos) > 0) {
                $this->ci->db->set('expense_id', null);
                $this->ci->db->where('expense_id', $expense_id);
                $this->ci->db->update('bank_reconciliation_statement');
            }
        }

        return array('success' => true, 'message' => 'Status updated');
    }

    public function request_reverse($user, $expense_id, $reason)
    {
        $expense_id = (int) $expense_id;
        if ($expense_id <= 0 || trim((string) $reason) === '') {
            return array('success' => false, 'message' => 'Reason is required');
        }

        $this->ci->db->set('rev_reason', $reason);
        $this->ci->db->set('rev_status', '0');
        $this->ci->db->where('expense_id', $expense_id);
        $this->ci->db->where('add_by_id', (int) $user['user_id']);
        $this->ci->db->update('expenses');

        if ($this->ci->db->affected_rows() === 0) {
            return array('success' => false, 'message' => 'Could not submit reversal request');
        }

        return array('success' => true, 'message' => 'Reversal request submitted');
    }

    public function delete_expense($user, $expense_id)
    {
        $p = $this->permissions($user);
        if (empty($p['delete'])) {
            return array('success' => false, 'message' => 'Expense delete permission required');
        }

        $expense_id = (int) $expense_id;
        $exp = $this->ci->db->get_where('expenses', array('expense_id' => $expense_id))->row_array();
        if (!$exp) {
            return array('success' => false, 'message' => 'Expense not found');
        }
        if ((int) $exp['expense_category_id'] === 36) {
            return array('success' => false, 'message' => 'Delete payroll expenses from Salary report');
        }

        $this->ci->db->where('expense_id', $expense_id);
        $this->ci->db->delete('expenses');

        return array('success' => true, 'message' => 'Expense deleted');
    }

    private function user_display_name($user)
    {
        $name = trim((string) $user['first_name'] . ' ' . (string) $user['last_name']);
        if ($name === '' && !empty($user['name'])) {
            $name = (string) $user['name'];
        }
        return $name;
    }

    public function add_form_meta($user)
    {
        $full = $this->meta($user);
        $this->ci->load->helper('custom');
        $this->ci->session->set_userdata('user_id', (int) $user['user_id']);

        $pettycash = 0;
        $pc = $this->ci->db->get_where('petty_cash_college_wise', array('assign_to' => (int) $user['user_id']))->result_array();
        if (count($pc) > 0) {
            $pettycash = (float) my_pettycash();
            $to_date = date('Y-m-d');
            $this->ci->db->select_sum('amount');
            $this->ci->db->from('expenses');
            $this->ci->db->where(array(
                'date' => $to_date,
                'user_id' => (int) $user['user_id'],
                'approved_status !=' => '1',
            ));
            $pending = $this->ci->db->get()->row_array();
            if (!empty($pending['amount'])) {
                $pettycash -= (float) $pending['amount'];
            }
        }

        $categories = $this->ci->db
            ->order_by('name', 'ASC')
            ->get_where('expense_category', array('status' => 'active'))
            ->result_array();

        $council_ids = $this->ci->db
            ->query("SELECT * FROM bank_reconciliation_statement WHERE is_council_fee = 1 AND CAST(REPLACE(debit,',','') AS SIGNED) > tagged_amount")
            ->result_array();

        $council_payments = array(array('id' => 'cash', 'label' => 'Cash'));
        foreach ($council_ids as $row) {
            $council_payments[] = array(
                'id' => (string) $row['id'],
                'label' => $row['description'] . ' ( ' . $row['debit'] . ' - ' . $row['tagged_amount'] . ' )',
            );
        }

        return array(
            'permissions' => $full['permissions'],
            'add_campuses' => $full['add_campuses'],
            'categories' => $categories,
            'petty_cash' => $pettycash,
            'council_payments' => $council_payments,
            'add_by' => $this->user_display_name($user),
            'add_by_id' => (int) $user['user_id'],
            'legacy_root' => $full['legacy_root'],
        );
    }

    public function campus_classes($campus_id)
    {
        $campus_id = (int) $campus_id;
        if ($campus_id <= 0) {
            return array();
        }
        return $this->ci->db
            ->get_where('classes', array(
                'campus_id' => $campus_id,
                'dead_line_entry >=' => date('Y-m-d'),
                'status' => 1,
            ))
            ->result_array();
    }

    public function campus_staff($campus_id)
    {
        $campus_id = (int) $campus_id;
        if ($campus_id <= 0) {
            return array();
        }
        return $this->ci->db
            ->select('user_id, first_name, last_name')
            ->get_where('users', array('campus_id' => $campus_id, 'status' => 1))
            ->result_array();
    }

    public function council_exam_numbers($selected_class)
    {
        $selected_class = (int) $selected_class;
        $rows = $this->ci->db
            ->get_where('exam_sequence', array(
                'class' => $selected_class,
                'course_id' => 1,
                'status' => 'Active',
            ))
            ->result_array();
        $out = array();
        foreach ($rows as $row) {
            $out[] = array('value' => $row['first_year'], 'label' => (string) $row['first_year']);
        }
        return $out;
    }

    public function council_exam_no_for_class($class_id)
    {
        $class_id = (int) $class_id;
        $class = $this->ci->db->get_where('classes', array('class_id' => $class_id))->row_array();
        return $class ? (string) $class['exam_no'] : '';
    }

    private function council_student_row($student, $payment, $exam_context, $campus_class, $council_exam_no, $base_url)
    {
        $paid = !empty($payment['paid']) && (int) $payment['paid'] === 1 ? 'Submitted' : 'Not Submitted';
        $check = $this->ci->db->get_where('expenses', array(
            'student_id' => $student['student_id'],
            'council_exam_no' => $council_exam_no,
            'class' => $campus_class,
            'approved_status !=' => '2',
        ))->result_array();
        $already = count($check) > 0;
        $contractor = $this->ci->db->get_where('contractors', array('contractor_id' => $student['contractor_id']))->row();
        return array(
            'student_id' => (int) $student['student_id'],
            'class_name' => isset($student['name']) ? $student['name'] : '',
            'student_name' => trim($student['first_name'] . ' ' . $student['last_name']),
            'contractor' => $contractor ? $contractor->name : '',
            'cnic' => isset($student['cnic']) ? $student['cnic'] : '',
            'roll_no' => isset($student['roll_no']) ? $student['roll_no'] : '',
            'fee_remarks' => $exam_context,
            'submit_fee' => $payment ? $payment['amount'] : '',
            'fee_created_by' => $payment ? $payment['add_by'] : '',
            'fee_status' => $paid,
            'already_added' => $already,
            'receipt_url' => $already && !empty($check[0]['image']) ? rtrim($base_url, '/') . '/uploads/' . $check[0]['image'] : null,
            'selectable' => !$already,
        );
    }

    public function council_fee_students_result($campus_id, $council_exam_no, $campus_class)
    {
        $campus_id = (int) $campus_id;
        $campus_class = (string) $campus_class;
        $class_label = $campus_class === '1' ? '1st' : '2nd';

        $this->ci->db->select('*');
        $this->ci->db->from('payments');
        $this->ci->db->join('students', 'students.student_id=payments.custom_student_id OR students.student_id=payments.student_id', 'inner');
        $this->ci->db->join('classes', 'classes.class_id=students.class_id', 'inner');
        $this->ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
        $this->ci->db->like('payments.payment_comment', 'This fee for next exam # ' . $council_exam_no . ' ' . $class_label . ' Year', 'both');
        $this->ci->db->where('payments.paid', '1');
        $this->ci->db->order_by('payments.paid', 'DESC');
        $this->ci->db->order_by('students.roll_no', 'ASC');
        $results = $this->ci->db->get()->result_array();

        $out = array();
        $i = 1;
        foreach ($results as $result) {
            $student = $this->ci->db
                ->select('students.*, classes.campus_id')
                ->from('students')
                ->join('classes', 'classes.class_id=students.class_id', 'inner')
                ->where('students.student_id', $result['student_id'])
                ->get()
                ->row_array();
            if (!$student || (int) $student['campus_id'] !== $campus_id) {
                continue;
            }
            $paid_status = !empty($result['paid']) && (int) $result['paid'] === 1 ? 'Submitted' : 'Not Submitted in Council';
            $row = $this->council_student_row(
                $student,
                $result,
                isset($result['payment_comment']) ? $result['payment_comment'] : '',
                $campus_class,
                $council_exam_no,
                base_url()
            );
            $row['sr'] = $i++;
            $row['fee_status'] = $paid_status;
            $row['submit_fee'] = isset($result['amount']) ? $result['amount'] : '';
            $row['fee_created_by'] = isset($result['add_by']) ? $result['add_by'] : '';
            $out[] = $row;
        }
        return $out;
    }

    public function council_fee_students_class($campus_id, $class_id, $exam_no)
    {
        $campus_id = (int) $campus_id;
        $class_id = (int) $class_id;
        $students = $this->ci->db
            ->select('students.*, classes.name, classes.campus_id')
            ->from('students')
            ->join('classes', 'classes.class_id=students.class_id', 'inner')
            ->where(array('students.class_id' => $class_id, 'students.status' => 1))
            ->get()
            ->result_array();

        $out = array();
        $i = 1;
        foreach ($students as $student) {
            if ((int) $student['campus_id'] !== $campus_id) {
                continue;
            }
            $payment = $this->ci->db
                ->get_where('payments', array('payment_plan' => 'consulation fee', 'student_id' => $student['student_id']))
                ->row_array();
            $row = $this->council_student_row(
                $student,
                $payment ?: array(),
                'Council Exam # ' . (isset($student['exam_no']) ? $student['exam_no'] : $exam_no),
                '1',
                $exam_no,
                base_url()
            );
            $row['sr'] = $i++;
            $out[] = $row;
        }
        return $out;
    }

    public function insert_expense($user, $data, $image = '')
    {
        $p = $this->permissions($user);
        if (empty($p['add'])) {
            return array('success' => false, 'message' => 'Add Expense permission required');
        }

        if (empty($data['campus_id']) || empty($data['expense_category_id']) || !is_array($data['expense_category_id'])) {
            return array('success' => false, 'message' => 'Campus and expense category are required');
        }

        $leaf = (int) $data['expense_category_id'][count($data['expense_category_id']) - 1];
        if ($leaf === 13) {
            if (empty($data['student_ids'])) {
                return array('success' => false, 'message' => 'Select at least one student for council fee');
            }
            if (empty($data['payment_type'])) {
                return array('success' => false, 'message' => 'Payment type is required');
            }
        }

        $display = $this->user_display_name($user);
        $this->ci->session->set_userdata('user_id', (int) $user['user_id']);
        $this->ci->session->set_userdata('name', $display);

        $data['add_by'] = $display;
        $data['add_by_id'] = (int) $user['user_id'];
        $data['last_edit'] = $display;

        $this->ci->load->model('expense');
        $this->ci->expense->storeExpense($data, $image);

        if (isset($data['payment_type']) && $data['payment_type'] === 'cash') {
            $this->ci->db->set('remaining_amount', 'remaining_amount - ' . (float) $data['amount'], false);
            $this->ci->db->where('assign_to', (int) $user['user_id']);
            $this->ci->db->update('petty_cash_college_wise');
        }

        return array('success' => true, 'message' => 'Expense added successfully!');
    }

    private function category_chain($category_id)
    {
        $chain = array();
        $id = (int) $category_id;
        $guard = 0;
        while ($id > 0 && $guard < 20) {
            array_unshift($chain, $id);
            $row = $this->ci->db->get_where('expense_category', array('expense_category_id' => $id))->row_array();
            if (!$row || empty($row['sub_of'])) {
                break;
            }
            $id = (int) $row['sub_of'];
            $guard++;
        }
        return $chain;
    }

    public function get_expense_detail($user, $expense_id)
    {
        $p = $this->permissions($user);
        if (empty($p['edit'])) {
            return array('success' => false, 'message' => 'Expense edit permission required');
        }

        $expense_id = (int) $expense_id;
        if ($expense_id <= 0) {
            return array('success' => false, 'message' => 'Invalid expense');
        }

        $exp = $this->ci->db->get_where('expenses', array('expense_id' => $expense_id))->row_array();
        if (!$exp) {
            return array('success' => false, 'message' => 'Expense not found');
        }

        $full = $this->meta($user);
        $categories = $this->ci->db
            ->order_by('name', 'ASC')
            ->get_where('expense_category', array('status' => 'active'))
            ->result_array();

        $cat = $this->ci->db->get_where('expense_category', array('expense_category_id' => $exp['expense_category_id']))->row_array();
        $campus = $this->ci->db->get_where('campuses', array('campus_id' => $exp['campus_id']))->row_array();

        $row = $this->enrich_row(array_merge($exp, array(
            'category_name' => $cat ? $cat['name'] : '',
            'campus_name' => $campus ? $campus['campus_name'] : '',
        )));
        $row['rickshaw_number'] = $exp['rickshaw_number'];
        $row['driver_phone'] = $exp['driver_phone'];
        $row['image'] = $exp['image'];

        return array(
            'success' => true,
            'expense' => $row,
            'category_chain' => $this->category_chain($exp['expense_category_id']),
            'categories' => $categories,
            'campuses' => $full['add_campuses'],
        );
    }

    public function update_expense($user, $expense_id, $data, $image = '')
    {
        $p = $this->permissions($user);
        if (empty($p['edit'])) {
            return array('success' => false, 'message' => 'Expense edit permission required');
        }

        $expense_id = (int) $expense_id;
        if ($expense_id <= 0) {
            return array('success' => false, 'message' => 'Invalid expense');
        }

        $exp = $this->ci->db->get_where('expenses', array('expense_id' => $expense_id))->row_array();
        if (!$exp) {
            return array('success' => false, 'message' => 'Expense not found');
        }

        if (empty($data['campus_id']) || empty($data['expense_category_id']) || !is_array($data['expense_category_id'])) {
            return array('success' => false, 'message' => 'Campus and expense category are required');
        }

        $data['last_edit'] = $this->user_display_name($user);
        if (!isset($data['image']) || $data['image'] === '') {
            $data['image'] = $exp['image'];
        }

        $skip = array('msg', 'expense_category_id', 'image', 'expense_id');
        foreach ($data as $k => $value) {
            if (in_array($k, $skip, true)) {
                continue;
            }
            $this->ci->db->set($k, $value);
        }
        if ($image !== '') {
            $this->ci->db->set('image', $image);
        }
        $this->ci->db->set('expense_category_id', $data['expense_category_id'][count($data['expense_category_id']) - 1]);
        $this->ci->db->where('expense_id', $expense_id);
        $this->ci->db->update('expenses');

        return array('success' => true, 'message' => 'Expense updated successfully!');
    }

    private function report_campuses($user)
    {
        $is_admin = $user && $user['role'] === 'Admin';
        $acc = $is_admin ? array() : $this->access_row($user);
        $this->ci->db->select('*')->from('campuses')->where('status', 1)->order_by('campus_name', 'ASC');
        if (!$is_admin) {
            $ids = $this->campus_ids_from_access($acc);
            if (!empty($ids)) {
                $this->ci->db->where_in('campus_id', $ids);
            }
        }
        return $this->ci->db->get()->result_array();
    }

    public function report_meta($user)
    {
        return array(
            'campuses' => $this->report_campuses($user),
            'leaf_categories' => $this->ci->db->get_where('expense_category', array('has_sub' => 0))->result_array(),
            'head_categories' => $this->ci->db->order_by('name', 'ASC')->get_where('expense_category', 'sub_of IS NULL', null, false)->result_array(),
            'all_campuses' => $this->ci->db->order_by('campus_name', 'ASC')->get_where('campuses', array('status' => 1))->result_array(),
            'legacy_root' => rtrim(base_url(), '/'),
        );
    }

    public function advertising_expenses($user, $from_date, $to_date)
    {
        if (!$user || $user['role'] !== 'Admin') {
            return array('success' => false, 'message' => 'Admin access required');
        }

        $from_date = !empty($from_date) ? $from_date : date('Y-m-d');
        $to_date = !empty($to_date) ? $to_date : date('Y-m-d');
        $from_ts = $from_date . ' 00:00:00';
        $to_ts = $to_date . ' 23:59:59';

        $this->ci->db->select('advertisement_expenses.*, campuses.campus_name, users.first_name, users.last_name');
        $this->ci->db->from('advertisement_expenses');
        $this->ci->db->join('campuses', 'campuses.campus_id=advertisement_expenses.campus_id', 'left');
        $this->ci->db->join('users', 'users.user_id=advertisement_expenses.created_by', 'left');
        $this->ci->db->where(array(
            'advertisement_expenses.created_at >=' => $from_ts,
            'advertisement_expenses.created_at <=' => $to_ts,
        ));
        $raw = $this->ci->db->get()->result_array();

        $rows = array();
        $i = 1;
        foreach ($raw as $exp) {
            $image_url = null;
            if (!empty($exp['online_image'])) {
                $image_url = str_replace(
                    'https://shahbazcollegebucket.s3.ca-central-1.amazonaws.com',
                    'https://d10iw6eujrfvyr.cloudfront.net',
                    $exp['online_image']
                );
            } elseif (!empty($exp['image'])) {
                $image_url = rtrim(base_url(), '/') . '/uploads/' . $exp['image'];
            }
            $rows[] = array(
                'sr' => $i++,
                'date' => date('M d, Y', strtotime($exp['created_at'])),
                'time' => date('h:i:s A', strtotime($exp['created_at'])),
                'vehicle_no' => $exp['vehicle_no'],
                'flax_sr_no' => $exp['flax_sr_no'],
                'location_url' => 'https://www.google.com/maps/?q=' . $exp['latitude'] . ',' . $exp['longitude'],
                'image_url' => $image_url,
                'add_by' => trim($exp['first_name'] . ' ' . $exp['last_name']),
            );
        }

        return array(
            'success' => true,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'rows' => $rows,
        );
    }

    public function report_headwise($user, $filters = array())
    {
        if (!$user || $user['role'] !== 'Admin') {
            return array('success' => false, 'message' => 'Admin access required');
        }

        $from_date = !empty($filters['from_date']) ? $filters['from_date'] : date('Y-m-d');
        $to_date = !empty($filters['to_date']) ? $filters['to_date'] : date('Y-m-d');
        $campus_ids = isset($filters['campus_ids']) ? $filters['campus_ids'] : array();
        $categories = isset($filters['categories']) ? $filters['categories'] : array();

        if (!is_array($campus_ids)) {
            $campus_ids = array_filter(array_map('intval', explode(',', (string) $campus_ids)));
        }
        if (!is_array($categories)) {
            $categories = array_filter(array_map('intval', explode(',', (string) $categories)));
        }

        if (empty($campus_ids) || empty($categories)) {
            return array(
                'success' => true,
                'from_date' => $from_date,
                'to_date' => $to_date,
                'rows' => array(),
                'total' => 0,
            );
        }

        $this->ci->db->select('SUM(expenses.amount) as total_amount, campuses.campus_id, campuses.campus_name, expense_category.name, expense_category.expense_category_id');
        $this->ci->db->from('expenses');
        $this->ci->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
        $this->ci->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
        $this->ci->db->where(array('expenses.date >=' => $from_date, 'expenses.date <=' => $to_date));
        $this->ci->db->where_in('expenses.expense_category_id', $categories);
        $this->ci->db->where_in('expenses.campus_id', $campus_ids);
        $this->ci->db->group_by('expenses.campus_id, expenses.expense_category_id');
        $raw = $this->ci->db->get()->result_array();

        $rows = array();
        $total = 0;
        $i = 0;
        foreach ($raw as $row) {
            $amt = (float) $row['total_amount'];
            $total += $amt;
            $rows[] = array(
                'sr' => $i++,
                'campus_id' => (int) $row['campus_id'],
                'campus_name' => $row['campus_name'],
                'category_id' => (int) $row['expense_category_id'],
                'category_name' => $row['name'],
                'total_amount' => $amt,
            );
        }

        return array(
            'success' => true,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'rows' => $rows,
            'total' => $total,
        );
    }

    private function purpose_extra($expense)
    {
        $cat_id = (int) $expense['expense_category_id'];
        if (($cat_id === 30 || $cat_id === 31) && !empty($expense['user_id'])) {
            $loan = $this->ci->db->get_where('users', array('user_id' => $expense['user_id']))->row_array();
            if ($loan) {
                return trim($loan['first_name'] . ' ' . $loan['last_name'] . ' ' . $loan['cnic']);
            }
        }
        return '';
    }

    public function report_headwise_details($user, $filters = array())
    {
        if (!$user || $user['role'] !== 'Admin') {
            return array('success' => false, 'message' => 'Admin access required');
        }

        $from_date = !empty($filters['from_date']) ? $filters['from_date'] : date('Y-m-d');
        $to_date = !empty($filters['to_date']) ? $filters['to_date'] : date('Y-m-d');
        $campus_id = isset($filters['campus_id']) ? (int) $filters['campus_id'] : 0;
        $category_id = isset($filters['category_id']) ? (int) $filters['category_id'] : 0;

        if ($campus_id <= 0 || $category_id <= 0) {
            return array('success' => false, 'message' => 'Campus and category required');
        }

        $this->ci->db->select('expenses.*, expense_category.name as category_name, campuses.campus_name');
        $this->ci->db->from('expenses');
        $this->ci->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'inner');
        $this->ci->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'inner');
        $this->ci->db->where(array(
            'expenses.date >=' => $from_date,
            'expenses.date <=' => $to_date,
            'expenses.campus_id' => $campus_id,
            'expenses.expense_category_id' => $category_id,
        ));
        $this->ci->db->order_by('expenses.expense_id', 'ASC');
        $raw = $this->ci->db->get()->result_array();

        $rows = array();
        $total = 0;
        $i = 0;
        $campus_name = '';
        $category_name = '';
        foreach ($raw as $expense) {
            $row = $this->enrich_row($expense);
            $extra = $this->purpose_extra($expense);
            if ($extra !== '') {
                $row['purpose'] = trim($row['purpose'] . ' ' . $extra);
            }
            $row['sr'] = $i++;
            $rows[] = $row;
            $total += (float) $row['amount'];
            if ($campus_name === '') {
                $campus_name = $row['campus_name'];
            }
            if ($category_name === '') {
                $category_name = $row['category_label'];
            }
        }

        return array(
            'success' => true,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'campus_id' => $campus_id,
            'category_id' => $category_id,
            'campus_name' => $campus_name,
            'category_name' => $category_name,
            'rows' => $rows,
            'total' => $total,
        );
    }

    private function sum_category_expenses($category_id, $campus_id, $from_date, $to_date)
    {
        $this->ci->db->select_sum('amount');
        $this->ci->db->from('expenses');
        $this->ci->db->where(array(
            'expense_category_id' => (int) $category_id,
            'campus_id' => (int) $campus_id,
            'date >=' => $from_date,
            'date <=' => $to_date,
        ));
        $row = $this->ci->db->get()->row_array();
        return isset($row['amount']) ? (float) $row['amount'] : 0;
    }

    private function subhead_breakdown($category_id, $campus_id, $from_date, $to_date)
    {
        $subs = $this->ci->db->get_where('expense_category', array('sub_of' => (int) $category_id))->result_array();
        $lines = array();
        foreach ($subs as $sub) {
            if ((string) $sub['has_sub'] === '1') {
                $lines = array_merge($lines, $this->subhead_breakdown($sub['expense_category_id'], $campus_id, $from_date, $to_date));
            } else {
                $amt = $this->sum_category_expenses($sub['expense_category_id'], $campus_id, $from_date, $to_date);
                if ($amt > 0) {
                    $lines[] = array('name' => $sub['name'], 'amount' => $amt);
                }
            }
        }
        return $lines;
    }

    public function report_subhead($user, $filters = array())
    {
        if (!$user || $user['role'] !== 'Admin') {
            return array('success' => false, 'message' => 'Admin access required');
        }

        $from_date = !empty($filters['from_date']) ? $filters['from_date'] : date('Y-m-d');
        $to_date = !empty($filters['to_date']) ? $filters['to_date'] : date('Y-m-d');
        $campus_id = isset($filters['campus_id']) ? (int) $filters['campus_id'] : 0;
        $category_id = isset($filters['category_id']) ? (int) $filters['category_id'] : 0;

        if ($campus_id <= 0 || $category_id <= 0) {
            return array(
                'success' => true,
                'from_date' => $from_date,
                'to_date' => $to_date,
                'rows' => array(),
                'total' => 0,
            );
        }

        $head = $this->ci->db->get_where('expense_category', array('expense_category_id' => $category_id))->row_array();
        $sub_heads = $this->ci->db->get_where('expense_category', array('sub_of' => $category_id))->result_array();
        $campus = $this->ci->db->get_where('campuses', array('campus_id' => $campus_id))->row_array();

        if (count($sub_heads) > 0) {
            $details = $this->subhead_breakdown($category_id, $campus_id, $from_date, $to_date);
            $total = 0;
            foreach ($details as $d) {
                $total += $d['amount'];
            }
            $rows = array(array(
                'head_name' => $head ? $head['name'] : '',
                'category_id' => $category_id,
                'campus_id' => $campus_id,
                'campus_name' => $campus ? $campus['campus_name'] : '',
                'details' => $details,
                'total_amount' => $total,
            ));
        } else {
            $total = $this->sum_category_expenses($category_id, $campus_id, $from_date, $to_date);
            $rows = array(array(
                'head_name' => $head ? $head['name'] : '',
                'category_id' => $category_id,
                'campus_id' => $campus_id,
                'campus_name' => $campus ? $campus['campus_name'] : '',
                'details' => array(),
                'total_amount' => $total,
            ));
        }

        $grand = 0;
        foreach ($rows as $r) {
            $grand += $r['total_amount'];
        }

        return array(
            'success' => true,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'rows' => $rows,
            'total' => $grand,
        );
    }

    private function build_category_tree($parent_id, $campus_id = null)
    {
        $this->ci->db->order_by('name', 'ASC');
        if ($parent_id === null) {
            $this->ci->db->where('sub_of IS NULL', null, false);
        } else {
            $this->ci->db->where('sub_of', (int) $parent_id);
        }
        if ($campus_id) {
            $campus_id = (int) $campus_id;
            $this->ci->db->where("FIND_IN_SET({$campus_id}, for_campus) > 0", null, false);
        }
        $rows = $this->ci->db->get('expense_category')->result_array();
        $out = array();
        foreach ($rows as $row) {
            $out[] = array(
                'expense_category_id' => (int) $row['expense_category_id'],
                'name' => $row['name'],
                'status' => $row['status'],
                'has_sub' => $row['has_sub'],
                'for_campus' => $row['for_campus'],
                'children' => $this->build_category_tree((int) $row['expense_category_id'], null),
            );
        }
        return $out;
    }

    public function list_categories($user, $campus_id = null)
    {
        $p = $this->permissions($user);
        if (empty($p['category']) && empty($p['is_admin'])) {
            return array('success' => false, 'message' => 'Category permission required');
        }

        $campus_id = $campus_id ? (int) $campus_id : null;
        return array(
            'success' => true,
            'campus_id' => $campus_id,
            'tree' => $this->build_category_tree(null, $campus_id),
            'campuses' => $this->ci->db->order_by('campus_name', 'ASC')->get_where('campuses', array('status' => 1))->result_array(),
            'legacy_root' => rtrim(base_url(), '/'),
        );
    }

    public function add_expense_category($user, $data)
    {
        $p = $this->permissions($user);
        if (empty($p['category']) && empty($p['is_admin'])) {
            return array('success' => false, 'message' => 'Category permission required');
        }

        if (empty($data['name']) || empty($data['campus_ids']) || !is_array($data['campus_ids'])) {
            return array('success' => false, 'message' => 'Name and campus selection required');
        }

        $this->ci->load->model('expense');
        $payload = array(
            'name' => $data['name'],
            'campus_ids' => $data['campus_ids'],
            'head_category' => isset($data['head_category']) ? $data['head_category'] : '',
        );
        $this->ci->expense->addExpenseCategories($payload);

        return array('success' => true, 'message' => 'Expense category added successfully!');
    }
}
