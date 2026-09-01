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

        // Untag bank reconciliation rows linked to this expense (legacy Expense::deleteExpense).
        $brs_rows = $this->ci->db
            ->get_where('bank_reconciliation_statement', array('expense_id' => $expense_id))
            ->result_array();
        foreach ($brs_rows as $brs) {
            $amount = isset($exp['amount']) ? (float) $exp['amount'] : 0;
            if ($amount > 0) {
                $this->ci->db->set('tagged_amount', 'GREATEST(0, tagged_amount - ' . $amount . ')', false);
            }
            $this->ci->db->set('expense_id', null);
            $this->ci->db->where('id', (int) $brs['id']);
            $this->ci->db->update('bank_reconciliation_statement');
        }

        // Also clear salary_expense_ids if this id was stored there as a single tag.
        $this->ci->db->where('salary_expense_ids', (string) $expense_id);
        $this->ci->db->update('bank_reconciliation_statement', array('salary_expense_ids' => null));

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

        // Keep categories for backward compat; add form should prefer categories_for_campus after campus select.
        $normalized = array();
        foreach ($categories as $row) {
            $sub = isset($row['sub_of']) ? $row['sub_of'] : null;
            if ($sub === '' || $sub === false) {
                $sub = null;
            }
            $normalized[] = array(
                'expense_category_id' => (int) $row['expense_category_id'],
                'name' => $row['name'],
                'sub_of' => $sub === null ? null : (int) $sub,
                'status' => isset($row['status']) ? $row['status'] : 'active',
                'for_campus' => isset($row['for_campus']) ? $row['for_campus'] : '',
            );
        }

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
            'categories' => $normalized,
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

    /**
     * Categories available for a campus (legacy AdminApi FIND_IN_SET on for_campus).
     * Flat list of active rows so the React chain picker can build root → sub levels.
     */
    public function categories_for_campus($campus_id)
    {
        $campus_id = (int) $campus_id;
        if ($campus_id <= 0) {
            return array();
        }

        $this->ci->db->order_by('name', 'ASC');
        $this->ci->db->where('status', 'active');
        $this->ci->db->where("FIND_IN_SET({$campus_id}, for_campus) > 0", null, false);
        $rows = $this->ci->db->get('expense_category')->result_array();

        // Fallback: legacy add_expense listed all root/active categories when campus mapping is empty.
        if (!$rows) {
            $this->ci->db->order_by('name', 'ASC');
            $this->ci->db->where('status', 'active');
            $rows = $this->ci->db->get('expense_category')->result_array();
        }

        $out = array();
        foreach ($rows as $row) {
            $sub = isset($row['sub_of']) ? $row['sub_of'] : null;
            if ($sub === '' || $sub === false) {
                $sub = null;
            }
            $out[] = array(
                'expense_category_id' => (int) $row['expense_category_id'],
                'name' => $row['name'],
                'sub_of' => $sub === null ? null : (int) $sub,
                'status' => isset($row['status']) ? $row['status'] : 'active',
                'has_sub' => isset($row['has_sub']) ? $row['has_sub'] : null,
                'for_campus' => isset($row['for_campus']) ? $row['for_campus'] : '',
            );
        }
        return $out;
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

    /**
     * Apply campus / user scope for expense dashboard queries (PHP 5.6 safe).
     */
    private function _dash_apply_scope($user, $is_admin, $acc, $campus_ids, $campus_id)
    {
        if (!$is_admin && empty($acc['expense_view_user'])) {
            $this->ci->db->where('expenses.add_by_id', (int) $user['user_id']);
        }
        if (!$is_admin && $campus_id <= 0 && !empty($campus_ids)) {
            $this->ci->db->where_in('expenses.campus_id', $campus_ids);
        }
        if ($campus_id > 0) {
            $this->ci->db->where('expenses.campus_id', $campus_id);
        }
    }

    private function _dash_root_category($cid, $cat_meta, &$cache)
    {
        $cid = (int) $cid;
        if (isset($cache[$cid])) {
            return $cache[$cid];
        }
        if ($cid <= 0 || !isset($cat_meta[$cid])) {
            $cache[$cid] = array('id' => 0, 'name' => 'Uncategorized');
            return $cache[$cid];
        }
        $guard = 0;
        $cur = $cid;
        while ($guard < 20) {
            $guard++;
            $sub = isset($cat_meta[$cur]['sub_of']) ? (int) $cat_meta[$cur]['sub_of'] : 0;
            if ($sub <= 0 || !isset($cat_meta[$sub])) {
                $cache[$cid] = array('id' => $cur, 'name' => $cat_meta[$cur]['name']);
                return $cache[$cid];
            }
            $cur = $sub;
        }
        $cache[$cid] = array('id' => $cid, 'name' => $cat_meta[$cid]['name']);
        return $cache[$cid];
    }

    private function _dash_head_nature($head_id, $head_name)
    {
        $id = (int) $head_id;
        $n = strtolower((string) $head_name);
        // Known structural category IDs from expenses module
        if ($id === 9 || $id === 36 || strpos($n, 'salary') !== false || strpos($n, 'payroll') !== false || strpos($n, 'wage') !== false) {
            return 'structural';
        }
        if ($id === 13 || strpos($n, 'council') !== false || strpos($n, 'exam') !== false || strpos($n, 'board') !== false) {
            return 'structural';
        }
        if ($id === 448 || strpos($n, 'construction') !== false || strpos($n, 'labour') !== false || strpos($n, 'contractor') !== false) {
            return 'project';
        }
        if (strpos($n, 'utilit') !== false || strpos($n, 'electric') !== false || strpos($n, 'gas') !== false || strpos($n, 'water') !== false || strpos($n, 'internet') !== false) {
            return 'ops_fixed';
        }
        if (strpos($n, 'market') !== false || strpos($n, 'advert') !== false || strpos($n, 'promo') !== false || strpos($n, 'flex') !== false || strpos($n, 'media') !== false) {
            return 'discretionary';
        }
        if (strpos($n, 'transport') !== false || strpos($n, 'rickshaw') !== false || strpos($n, 'fuel') !== false || strpos($n, 'travel') !== false || $id === 1) {
            return 'ops_variable';
        }
        if (strpos($n, 'misc') !== false || strpos($n, 'other') !== false || strpos($n, 'general') !== false) {
            return 'discretionary';
        }
        return 'ops_variable';
    }

    private function _dash_enrich_heads($by_head, $prior_head_map, $total, $from_date, $to_date, $prior_from, $prior_to)
    {
        $out = array();
        $cmp_phrase = 'this period (' . $from_date . ' to ' . $to_date . ') compared with '
            . $prior_from . ' to ' . $prior_to;
        foreach ($by_head as $h) {
            $hid = (int) $h['head_id'];
            $amount = (float) $h['amount'];
            $prior = isset($prior_head_map[$hid]) ? (float) $prior_head_map[$hid] : 0.0;
            $share = $total > 0 ? round(($amount / $total) * 100, 1) : 0.0;
            $growth = null;
            if ($prior > 0) {
                $growth = round((($amount - $prior) / $prior) * 100, 1);
            } elseif ($amount > 0) {
                $growth = 100.0;
            } else {
                $growth = 0.0;
            }
            $nature = $this->_dash_head_nature($hid, $h['head_name']);
            $score = 0;
            $reasons = array();
            // Share weight
            if ($share >= 35) {
                $score += 35;
                $reasons[] = $share . '% of all spend sits here';
            } elseif ($share >= 20) {
                $score += 22;
                $reasons[] = $share . '% share of period spend';
            } elseif ($share >= 10) {
                $score += 12;
                $reasons[] = $share . '% of total spend';
            }
            // Growth weight (discretionary / variable grows = control)
            if ($growth !== null && $growth >= 40) {
                $score += ($nature === 'structural' ? 12 : 30);
                $reasons[] = 'Up ' . $growth . '% — ' . $cmp_phrase;
            } elseif ($growth !== null && $growth >= 20) {
                $score += ($nature === 'structural' ? 6 : 18);
                $reasons[] = 'Rising ' . $growth . '% — ' . $cmp_phrase;
            } elseif ($growth !== null && $growth <= -15) {
                $reasons[] = 'Down ' . abs($growth) . '% — ' . $cmp_phrase . ' (improving)';
            }
            if ($nature === 'discretionary') {
                $score += 15;
                $reasons[] = 'Discretionary head — easiest place to cut / freeze';
            } elseif ($nature === 'ops_variable') {
                $score += 8;
                $reasons[] = 'Variable ops spend — needs weekly review';
            } elseif ($nature === 'structural') {
                $score -= 8;
                $reasons[] = 'Mostly structural (salary/council/payroll) — expected, not a soft cut';
            } elseif ($nature === 'project') {
                $score += 5;
                $reasons[] = 'Project-linked — check against construction budget';
            } elseif ($nature === 'ops_fixed') {
                $score += 4;
                $reasons[] = 'Utility / fixed ops — verify bills & meters';
            }

            if ($score >= 45) {
                $level = 'control_now';
            } elseif ($score >= 25) {
                $level = 'watch';
            } else {
                $level = 'ok';
            }

            if ($level === 'control_now') {
                if ($nature === 'discretionary') {
                    $action = 'Freeze non-essential vouchers this week; require director approval above average voucher size.';
                } elseif ($nature === 'ops_variable') {
                    $action = 'Cap daily cash for this head; switch large lines to bank; audit top 10 vouchers.';
                } elseif ($nature === 'structural') {
                    $action = 'Do not cut blindly — reconcile headcount / council lists; confirm no duplicate posts.';
                } else {
                    $action = 'Open College/Head report, drill top campus cells, and set a soft monthly ceiling.';
                }
            } elseif ($level === 'watch') {
                $action = 'Track next 2 weeks; if growth continues above 15%, move to control.';
            } else {
                $action = 'Healthy / expected — no immediate action.';
            }

            if (!count($reasons)) {
                $reasons[] = 'Normal share for this period';
            }

            $out[] = array(
                'head_id' => $hid,
                'head_name' => $h['head_name'],
                'amount' => $amount,
                'prior_amount' => $prior,
                'share_pct' => $share,
                'growth_pct' => $growth,
                'nature' => $nature,
                'control_score' => $score,
                'control_level' => $level,
                'why' => implode(' · ', $reasons),
                'action' => $action,
            );
        }
        usort($out, function ($a, $b) {
            if ($a['control_score'] == $b['control_score']) {
                if ($a['amount'] == $b['amount']) {
                    return 0;
                }
                return ($b['amount'] < $a['amount']) ? -1 : 1;
            }
            return ($b['control_score'] < $a['control_score']) ? -1 : 1;
        });
        return $out;
    }

    private function _dash_build_briefing($from_date, $to_date, $kpis, $by_head, $by_campus, $alerts)
    {
        $prior_from = isset($kpis['prior_from']) ? $kpis['prior_from'] : '';
        $prior_to = isset($kpis['prior_to']) ? $kpis['prior_to'] : '';

        $total = (float) $kpis['total_amount'];
        $mom = $kpis['mom_pct'];
        $cash_share = (float) $kpis['cash_share_pct'];
        $control_now = array();
        $watch = array();
        $ok = array();
        foreach ($by_head as $h) {
            if ($h['control_level'] === 'control_now') {
                $control_now[] = $h;
            } elseif ($h['control_level'] === 'watch') {
                $watch[] = $h;
            } else {
                $ok[] = $h;
            }
        }

        $top_campus = count($by_campus) ? $by_campus[0] : null;
        $top_head = count($by_head) ? $by_head[0] : null;

        $mom_bit = '';
        $vs_label = ($prior_from !== '' && $prior_to !== '')
            ? ('this period compared with ' . $prior_from . ' to ' . $prior_to)
            : 'this period compared with the days just before';
        if ($mom === null) {
            $mom_bit = 'Period comparison is limited.';
        } elseif ($mom > 10) {
            $mom_bit = 'Spend is up ' . $mom . '% (' . $vs_label . ') — pressure is rising.';
        } elseif ($mom < -5) {
            $mom_bit = 'Spend is down ' . abs($mom) . '% (' . $vs_label . ') — overall direction is improving.';
        } else {
            $mom_bit = 'Spend is roughly flat (' . $vs_label . ', ' . $mom . '%).';
        }

        $headline = 'Institute expense scene · ' . $from_date . ' to ' . $to_date;
        $summary = 'Total spend Rs ' . number_format($total, 0) . ' across ' . (int) $kpis['row_count'] . ' vouchers. ' . $mom_bit;
        if ($top_head) {
            $summary .= ' Largest pressure point right now: "' . $top_head['head_name'] . '" at Rs '
                . number_format($top_head['amount'], 0) . ' (' . $top_head['share_pct'] . '% of all spend).';
        }
        if ($top_campus) {
            $summary .= ' Heaviest campus: ' . $top_campus['campus_name'] . ' (Rs ' . number_format($top_campus['amount'], 0) . ').';
        }
        if ($cash_share >= 40) {
            $summary .= ' Cash share is high at ' . $cash_share . '% — leakage risk.';
        } else {
            $summary .= ' Cash share ' . $cash_share . '% looks manageable.';
        }

        $whats_ok = array();
        foreach (array_slice($ok, 0, 4) as $h) {
            $whats_ok[] = '"' . $h['head_name'] . '" looks fine for now (' . $h['share_pct'] . '% share'
                . ($h['growth_pct'] !== null ? ', ' . ($h['growth_pct'] > 0 ? '+' : '') . $h['growth_pct'] . '% vs ' . $prior_from . ' to ' . $prior_to : '')
                . ').';
        }
        if ($kpis['pending_aged'] <= 0) {
            $whats_ok[] = 'No aged approval backlog beyond 5 days.';
        }
        if ($cash_share < 35 && $cash_share >= 0) {
            $whats_ok[] = 'Bank tagging / cash mix is within a reasonable band.';
        }
        if (!count($whats_ok)) {
            $whats_ok[] = 'No major green flags yet — focus on control list below.';
        }

        $control_lines = array();
        foreach (array_slice($control_now, 0, 5) as $h) {
            $control_lines[] = array(
                'title' => $h['head_name'],
                'detail' => $h['why'],
                'action' => $h['action'],
                'amount' => $h['amount'],
                'share_pct' => $h['share_pct'],
                'growth_pct' => $h['growth_pct'],
                'level' => 'control_now',
            );
        }
        foreach (array_slice($watch, 0, 3) as $h) {
            $control_lines[] = array(
                'title' => $h['head_name'],
                'detail' => $h['why'],
                'action' => $h['action'],
                'amount' => $h['amount'],
                'share_pct' => $h['share_pct'],
                'growth_pct' => $h['growth_pct'],
                'level' => 'watch',
            );
        }

        $next_steps = array();
        if (count($control_now)) {
            $next_steps[] = 'Today: open "' . $control_now[0]['head_name'] . '" in College/Head report and review top campus cells.';
        }
        if ($kpis['pending_aged'] > 0) {
            $next_steps[] = 'Clear ' . (int) $kpis['pending_aged'] . ' aged pending approvals (Rs ' . number_format($kpis['pending_aged_amount'], 0) . ').';
        }
        if ($cash_share >= 40) {
            $next_steps[] = 'Enforce bank payment for vouchers above a campus cash cap.';
        }
        if (count($by_campus) >= 2) {
            $next_steps[] = 'Compare ' . $by_campus[0]['campus_name'] . ' vs next campus — ask why the gap exists.';
        }
        if (!count($next_steps)) {
            $next_steps[] = 'Keep weekly watch on top 3 heads; no emergency freeze needed.';
        }

        $alert_bits = array();
        foreach (array_slice($alerts, 0, 3) as $a) {
            $alert_bits[] = $a['area'] . ': ' . $a['signal'];
        }

        return array(
            'headline' => $headline,
            'summary' => $summary,
            'whats_ok' => $whats_ok,
            'control_priorities' => $control_lines,
            'next_steps' => $next_steps,
            'alert_bits' => $alert_bits,
            'counts' => array(
                'control_now' => count($control_now),
                'watch' => count($watch),
                'ok' => count($ok),
            ),
        );
    }

    private function _dash_enrich_campuses($by_campus, $total)
    {
        $out = array();
        $n = count($by_campus);
        $avg = $n > 0 ? ($total / $n) : 0;
        foreach ($by_campus as $i => $c) {
            $amount = (float) $c['amount'];
            $share = $total > 0 ? round(($amount / $total) * 100, 1) : 0.0;
            $vs_avg = $avg > 0 ? round((($amount - $avg) / $avg) * 100, 1) : 0.0;
            $level = 'ok';
            $note = 'In line with campus average.';
            if ($i === 0 && $share >= 30) {
                $level = 'control_now';
                $note = 'Dominant campus share — review head mix here first.';
            } elseif ($vs_avg >= 40) {
                $level = 'watch';
                $note = $vs_avg . '% above campus average spend.';
            } elseif ($vs_avg <= -30) {
                $note = 'Well below average — useful benchmark campus.';
            }
            $out[] = array(
                'campus_id' => (int) $c['campus_id'],
                'campus_name' => $c['campus_name'],
                'amount' => $amount,
                'count' => (int) $c['count'],
                'share_pct' => $share,
                'vs_avg_pct' => $vs_avg,
                'control_level' => $level,
                'note' => $note,
            );
        }
        return $out;
    }

    /**
     * Control + analytics dashboard — few aggregate queries (PHP 5.6 / shared host safe).
     */
    public function dashboard_analytics($user, $filters = array())
    {
        @set_time_limit(90);
        @ini_set('memory_limit', '256M');

        $is_admin = $user && isset($user['role']) && $user['role'] === 'Admin';
        $acc = $is_admin ? array() : $this->access_row($user);
        $campus_ids = $this->campus_ids_from_access($acc);

        $from_date = !empty($filters['from_date']) ? $filters['from_date'] : date('Y-m-01');
        $to_date = !empty($filters['to_date']) ? $filters['to_date'] : date('Y-m-d');
        $campus_id = isset($filters['campus_id']) ? (int) $filters['campus_id'] : 0;

        $from_ts = strtotime($from_date);
        $to_ts = strtotime($to_date);
        if ($from_ts === false || $to_ts === false || $from_ts > $to_ts) {
            $from_date = date('Y-m-01');
            $to_date = date('Y-m-d');
            $from_ts = strtotime($from_date);
            $to_ts = strtotime($to_date);
        }

        $days = (int) floor(($to_ts - $from_ts) / 86400) + 1;
        $prior_to = date('Y-m-d', $from_ts - 86400);
        $prior_from = date('Y-m-d', strtotime($prior_to) - (($days - 1) * 86400));
        $today = date('Y-m-d');
        $aged_cutoff = date('Y-m-d', strtotime('-5 days'));

        // 1) Period KPIs
        $this->ci->db->select("
            COALESCE(SUM(expenses.amount), 0) AS total_amount,
            SUM(CASE WHEN expenses.approved_status = '0' THEN 1 ELSE 0 END) AS pending_count,
            SUM(CASE WHEN expenses.approved_status = '1' THEN 1 ELSE 0 END) AS approved_count,
            SUM(CASE WHEN expenses.approved_status = '2' THEN 1 ELSE 0 END) AS rejected_count,
            SUM(CASE WHEN expenses.approved_status = '3' THEN 1 ELSE 0 END) AS reversed_count,
            COALESCE(SUM(CASE WHEN expenses.paid_type = 'cash' THEN expenses.amount ELSE 0 END), 0) AS cash_amount,
            COALESCE(SUM(CASE WHEN expenses.paid_type = 'bank' THEN expenses.amount ELSE 0 END), 0) AS bank_amount,
            COALESCE(SUM(CASE WHEN expenses.paid_type NOT IN ('cash','bank') OR expenses.paid_type IS NULL OR expenses.paid_type = '' THEN expenses.amount ELSE 0 END), 0) AS other_amount,
            COUNT(*) AS row_count
        ", false);
        $this->ci->db->from('expenses');
        $this->ci->db->where('expenses.date >=', $from_date);
        $this->ci->db->where('expenses.date <=', $to_date);
        $this->_dash_apply_scope($user, $is_admin, $acc, $campus_ids, $campus_id);
        $kpi_q = $this->ci->db->get();
        $kpi_row = ($kpi_q && $kpi_q->num_rows()) ? $kpi_q->row_array() : array();

        $total = (float) (isset($kpi_row['total_amount']) ? $kpi_row['total_amount'] : 0);
        $cash = (float) (isset($kpi_row['cash_amount']) ? $kpi_row['cash_amount'] : 0);
        $bank = (float) (isset($kpi_row['bank_amount']) ? $kpi_row['bank_amount'] : 0);
        $other = (float) (isset($kpi_row['other_amount']) ? $kpi_row['other_amount'] : 0);
        $cash_share = $total > 0 ? round(($cash / $total) * 100, 1) : 0;

        // 2) Prior period total
        $this->ci->db->select('COALESCE(SUM(expenses.amount), 0) AS total_amount', false);
        $this->ci->db->from('expenses');
        $this->ci->db->where('expenses.date >=', $prior_from);
        $this->ci->db->where('expenses.date <=', $prior_to);
        $this->_dash_apply_scope($user, $is_admin, $acc, $campus_ids, $campus_id);
        $prior_q = $this->ci->db->get();
        $prior_row = ($prior_q && $prior_q->num_rows()) ? $prior_q->row_array() : array();
        $prior_total = (float) (isset($prior_row['total_amount']) ? $prior_row['total_amount'] : 0);
        if ($prior_total > 0) {
            $mom_pct = round((($total - $prior_total) / $prior_total) * 100, 1);
        } elseif ($total > 0) {
            $mom_pct = 100.0;
        } else {
            $mom_pct = 0.0;
        }

        // 3) Pending aged + aging buckets (one scan)
        $this->ci->db->select("
            SUM(CASE WHEN expenses.date <= '{$aged_cutoff}' THEN 1 ELSE 0 END) AS aged_cnt,
            COALESCE(SUM(CASE WHEN expenses.date <= '{$aged_cutoff}' THEN expenses.amount ELSE 0 END), 0) AS aged_amount,
            SUM(CASE WHEN DATEDIFF('{$today}', expenses.date) <= 2 THEN 1 ELSE 0 END) AS d0_2,
            SUM(CASE WHEN DATEDIFF('{$today}', expenses.date) BETWEEN 3 AND 5 THEN 1 ELSE 0 END) AS d3_5,
            SUM(CASE WHEN DATEDIFF('{$today}', expenses.date) BETWEEN 6 AND 14 THEN 1 ELSE 0 END) AS d6_14,
            SUM(CASE WHEN DATEDIFF('{$today}', expenses.date) >= 15 THEN 1 ELSE 0 END) AS d15_plus
        ", false);
        $this->ci->db->from('expenses');
        $this->ci->db->where('expenses.approved_status', '0');
        $this->_dash_apply_scope($user, $is_admin, $acc, $campus_ids, $campus_id);
        $aging_q = $this->ci->db->get();
        $aging = ($aging_q && $aging_q->num_rows()) ? $aging_q->row_array() : array();
        $pending_aged = (int) (isset($aging['aged_cnt']) ? $aging['aged_cnt'] : 0);
        $pending_aged_amount = (float) (isset($aging['aged_amount']) ? $aging['aged_amount'] : 0);

        // 4) By campus
        $this->ci->db->select('expenses.campus_id, campuses.campus_name, COALESCE(SUM(expenses.amount), 0) AS amount, COUNT(*) AS cnt', false);
        $this->ci->db->from('expenses');
        $this->ci->db->join('campuses', 'campuses.campus_id = expenses.campus_id', 'left');
        $this->ci->db->where('expenses.date >=', $from_date);
        $this->ci->db->where('expenses.date <=', $to_date);
        $this->_dash_apply_scope($user, $is_admin, $acc, $campus_ids, $campus_id);
        $this->ci->db->group_by('expenses.campus_id');
        $this->ci->db->order_by('amount', 'DESC');
        $campus_q = $this->ci->db->get();
        $campus_rows = $campus_q ? $campus_q->result_array() : array();

        // 5) By category (period + prior) for head rollup / overrun
        $this->ci->db->select('expenses.expense_category_id, COALESCE(SUM(expenses.amount), 0) AS amount', false);
        $this->ci->db->from('expenses');
        $this->ci->db->where('expenses.date >=', $from_date);
        $this->ci->db->where('expenses.date <=', $to_date);
        $this->_dash_apply_scope($user, $is_admin, $acc, $campus_ids, $campus_id);
        $this->ci->db->group_by('expenses.expense_category_id');
        $cat_q = $this->ci->db->get();
        $cat_rows = $cat_q ? $cat_q->result_array() : array();

        $this->ci->db->select('expenses.expense_category_id, COALESCE(SUM(expenses.amount), 0) AS amount', false);
        $this->ci->db->from('expenses');
        $this->ci->db->where('expenses.date >=', $prior_from);
        $this->ci->db->where('expenses.date <=', $prior_to);
        $this->_dash_apply_scope($user, $is_admin, $acc, $campus_ids, $campus_id);
        $this->ci->db->group_by('expenses.expense_category_id');
        $prior_cat_q = $this->ci->db->get();
        $prior_cat_rows = $prior_cat_q ? $prior_cat_q->result_array() : array();

        $all_cats = $this->ci->db->select('expense_category_id, name, sub_of')->get('expense_category')->result_array();
        $cat_meta = array();
        foreach ($all_cats as $c) {
            $cat_meta[(int) $c['expense_category_id']] = $c;
        }
        $root_cache = array();

        $head_map = array();
        foreach ($cat_rows as $cr) {
            $root = $this->_dash_root_category($cr['expense_category_id'], $cat_meta, $root_cache);
            $hid = (int) $root['id'];
            if (!isset($head_map[$hid])) {
                $head_map[$hid] = array(
                    'head_id' => $hid,
                    'head_name' => $root['name'],
                    'amount' => 0.0,
                );
            }
            $head_map[$hid]['amount'] += (float) $cr['amount'];
        }
        $by_head = array_values($head_map);
        usort($by_head, function ($a, $b) {
            if ($a['amount'] == $b['amount']) {
                return 0;
            }
            return ($b['amount'] < $a['amount']) ? -1 : 1;
        });

        $prior_head_map = array();
        foreach ($prior_cat_rows as $cr) {
            $root = $this->_dash_root_category($cr['expense_category_id'], $cat_meta, $root_cache);
            $hid = (int) $root['id'];
            if (!isset($prior_head_map[$hid])) {
                $prior_head_map[$hid] = 0.0;
            }
            $prior_head_map[$hid] += (float) $cr['amount'];
        }

        $top_head_alert = null;
        if (count($by_head)) {
            $top = $by_head[0];
            $hid = (int) $top['head_id'];
            $prior_head = isset($prior_head_map[$hid]) ? (float) $prior_head_map[$hid] : 0.0;
            $growth = null;
            if ($prior_head > 0) {
                $growth = round((($top['amount'] - $prior_head) / $prior_head) * 100, 1);
            }
            if ($growth !== null && $growth >= 25) {
                $top_head_alert = array(
                    'head_name' => $top['head_name'],
                    'amount' => $top['amount'],
                    'prior_amount' => $prior_head,
                    'growth_pct' => $growth,
                );
            }
        }

        // 6) Submitters
        $this->ci->db->select('expenses.add_by_id, expenses.add_by, COALESCE(SUM(expenses.amount), 0) AS amount, COUNT(*) AS cnt', false);
        $this->ci->db->from('expenses');
        $this->ci->db->where('expenses.date >=', $from_date);
        $this->ci->db->where('expenses.date <=', $to_date);
        $this->_dash_apply_scope($user, $is_admin, $acc, $campus_ids, $campus_id);
        $this->ci->db->group_by('expenses.add_by_id');
        $this->ci->db->order_by('amount', 'DESC');
        $this->ci->db->limit(12);
        $sub_q = $this->ci->db->get();
        $submitters = $sub_q ? $sub_q->result_array() : array();

        // 7) Monthly trend — single query covering 24 months
        $end_ym = date('Y-m', $to_ts);
        $trend_start = date('Y-m-01', strtotime($end_ym . '-01 -23 months'));
        $this->ci->db->select("DATE_FORMAT(expenses.date, '%Y-%m') AS ym, COALESCE(SUM(expenses.amount), 0) AS amount", false);
        $this->ci->db->from('expenses');
        $this->ci->db->where('expenses.date >=', $trend_start);
        $this->ci->db->where('expenses.date <=', $to_date);
        $this->_dash_apply_scope($user, $is_admin, $acc, $campus_ids, $campus_id);
        $this->ci->db->group_by('ym');
        $trend_q = $this->ci->db->get();
        $trend_raw = $trend_q ? $trend_q->result_array() : array();
        $trend_map = array();
        foreach ($trend_raw as $tr) {
            $trend_map[$tr['ym']] = (float) $tr['amount'];
        }
        $trend = array();
        for ($i = 11; $i >= 0; $i--) {
            $ym = date('Y-m', strtotime($end_ym . '-01 -' . $i . ' months'));
            $m_from = $ym . '-01';
            $prior_ym = date('Y-m', strtotime($m_from . ' -1 year'));
            $trend[] = array(
                'month' => $ym,
                'label' => date('M y', strtotime($m_from)),
                'amount' => isset($trend_map[$ym]) ? $trend_map[$ym] : 0.0,
                'prior_amount' => isset($trend_map[$prior_ym]) ? $trend_map[$prior_ym] : 0.0,
            );
        }

        // 8) Heat cells from campus × category (reuse period cat×campus)
        $top_heads = array_slice($by_head, 0, 6);
        $heat_heads = array();
        foreach ($top_heads as $h) {
            $heat_heads[] = array('head_id' => (int) $h['head_id'], 'head_name' => $h['head_name']);
        }
        $heat_campuses = array();
        $slice_campus = array_slice($campus_rows, 0, 8);
        foreach ($slice_campus as $cr) {
            $heat_campuses[] = array(
                'campus_id' => (int) $cr['campus_id'],
                'campus_name' => (isset($cr['campus_name']) && $cr['campus_name'] !== '') ? $cr['campus_name'] : ('Campus #' . $cr['campus_id']),
            );
        }
        $heat_cells = array();
        if (count($heat_heads) && count($heat_campuses)) {
            $this->ci->db->select('expenses.campus_id, expenses.expense_category_id, COALESCE(SUM(expenses.amount), 0) AS amount', false);
            $this->ci->db->from('expenses');
            $this->ci->db->where('expenses.date >=', $from_date);
            $this->ci->db->where('expenses.date <=', $to_date);
            $this->_dash_apply_scope($user, $is_admin, $acc, $campus_ids, $campus_id);
            $this->ci->db->group_by('expenses.campus_id, expenses.expense_category_id');
            $heat_q = $this->ci->db->get();
            $raw_heat = $heat_q ? $heat_q->result_array() : array();
            $agg = array();
            foreach ($raw_heat as $rh) {
                $root = $this->_dash_root_category($rh['expense_category_id'], $cat_meta, $root_cache);
                $key = (int) $rh['campus_id'] . ':' . (int) $root['id'];
                if (!isset($agg[$key])) {
                    $agg[$key] = 0.0;
                }
                $agg[$key] += (float) $rh['amount'];
            }
            foreach ($heat_campuses as $hc) {
                foreach ($heat_heads as $hh) {
                    $key = $hc['campus_id'] . ':' . $hh['head_id'];
                    $heat_cells[] = array(
                        'campus_id' => $hc['campus_id'],
                        'head_id' => $hh['head_id'],
                        'amount' => isset($agg[$key]) ? $agg[$key] : 0.0,
                    );
                }
            }
        }

        // 9) Petty cash
        $this->ci->db->select('petty_cash_college_wise.campus_id, campuses.campus_name, COALESCE(SUM(petty_cash_college_wise.remaining_amount), 0) AS remaining', false);
        $this->ci->db->from('petty_cash_college_wise');
        $this->ci->db->join('campuses', 'campuses.campus_id = petty_cash_college_wise.campus_id', 'left');
        $this->ci->db->where('petty_cash_college_wise.petty_status', '1');
        if (!$is_admin && $campus_id <= 0 && !empty($campus_ids)) {
            $this->ci->db->where_in('petty_cash_college_wise.campus_id', $campus_ids);
        }
        if ($campus_id > 0) {
            $this->ci->db->where('petty_cash_college_wise.campus_id', $campus_id);
        }
        $this->ci->db->group_by('petty_cash_college_wise.campus_id');
        $this->ci->db->order_by('remaining', 'ASC');
        $petty_q = $this->ci->db->get();
        $petty_rows = $petty_q ? $petty_q->result_array() : array();
        $petty_total = 0.0;
        foreach ($petty_rows as $pr) {
            $petty_total += (float) $pr['remaining'];
        }

        $alerts = array();
        if ($pending_aged > 0) {
            $alerts[] = array(
                'level' => 'danger',
                'area' => 'Approval backlog',
                'signal' => $pending_aged . ' pending older than 5 days (Rs ' . number_format($pending_aged_amount, 0) . ')',
                'action' => 'Clear aged approvals or escalate second approver',
            );
        }
        if ($cash_share >= 40) {
            $alerts[] = array(
                'level' => 'warning',
                'area' => 'Cash share high',
                'signal' => $cash_share . '% of period spend is cash/petty',
                'action' => 'Prefer bank for large vouchers; review cash caps',
            );
        }
        if ($top_head_alert) {
            $alerts[] = array(
                'level' => 'warning',
                'area' => 'Head overrun',
                'signal' => $top_head_alert['head_name'] . ' +' . $top_head_alert['growth_pct'] . '% vs ' . $prior_from . ' to ' . $prior_to,
                'action' => 'Review head budget / freeze non-essential lines',
            );
        }
        if ($other > 0 && $total > 0 && ($other / $total) >= 0.08) {
            $alerts[] = array(
                'level' => 'info',
                'area' => 'Untyped payment',
                'signal' => round(($other / $total) * 100, 1) . '% spend missing clear cash/bank type',
                'action' => 'Require paid_type on entry before approve',
            );
        }
        if (count($petty_rows)) {
            $low = $petty_rows[0];
            if ((float) $low['remaining'] < 10000) {
                $alerts[] = array(
                    'level' => 'warning',
                    'area' => 'Petty cash float',
                    'signal' => (isset($low['campus_name']) ? $low['campus_name'] : 'Campus') . ' remaining Rs ' . number_format((float) $low['remaining'], 0),
                    'action' => 'Top-up float or reduce cash draws',
                );
            }
        }

        $by_campus = array();
        foreach ($campus_rows as $cr) {
            $by_campus[] = array(
                'campus_id' => (int) $cr['campus_id'],
                'campus_name' => (isset($cr['campus_name']) && $cr['campus_name'] !== '') ? $cr['campus_name'] : ('Campus #' . $cr['campus_id']),
                'amount' => (float) $cr['amount'],
                'count' => (int) $cr['cnt'],
            );
        }
        $by_campus = $this->_dash_enrich_campuses($by_campus, $total);
        $by_head = $this->_dash_enrich_heads($by_head, $prior_head_map, $total, $from_date, $to_date, $prior_from, $prior_to);

        $by_submitter = array();
        foreach ($submitters as $s) {
            $by_submitter[] = array(
                'user_id' => (int) $s['add_by_id'],
                'name' => (isset($s['add_by']) && $s['add_by'] !== '') ? $s['add_by'] : ('User #' . $s['add_by_id']),
                'amount' => (float) $s['amount'],
                'count' => (int) $s['cnt'],
            );
        }

        $petty = array();
        foreach ($petty_rows as $pr) {
            $petty[] = array(
                'campus_id' => (int) $pr['campus_id'],
                'campus_name' => isset($pr['campus_name']) ? $pr['campus_name'] : '',
                'remaining' => (float) $pr['remaining'],
            );
        }

        $kpis = array(
            'total_amount' => $total,
            'prior_amount' => $prior_total,
            'mom_pct' => $mom_pct,
            'row_count' => (int) (isset($kpi_row['row_count']) ? $kpi_row['row_count'] : 0),
            'pending' => (int) (isset($kpi_row['pending_count']) ? $kpi_row['pending_count'] : 0),
            'approved' => (int) (isset($kpi_row['approved_count']) ? $kpi_row['approved_count'] : 0),
            'rejected' => (int) (isset($kpi_row['rejected_count']) ? $kpi_row['rejected_count'] : 0),
            'reversed' => (int) (isset($kpi_row['reversed_count']) ? $kpi_row['reversed_count'] : 0),
            'cash_amount' => $cash,
            'bank_amount' => $bank,
            'other_amount' => $other,
            'cash_share_pct' => $cash_share,
            'pending_aged' => $pending_aged,
            'pending_aged_amount' => $pending_aged_amount,
            'petty_total' => $petty_total,
            'prior_from' => $prior_from,
            'prior_to' => $prior_to,
        );
        $briefing = $this->_dash_build_briefing($from_date, $to_date, $kpis, $by_head, $by_campus, $alerts);

        return array(
            'from_date' => $from_date,
            'to_date' => $to_date,
            'prior_from' => $prior_from,
            'prior_to' => $prior_to,
            'kpis' => $kpis,
            'briefing' => $briefing,
            'payment_mix' => array(
                array('label' => 'Bank', 'value' => $bank),
                array('label' => 'Cash / petty', 'value' => $cash),
                array('label' => 'Other / blank', 'value' => $other),
            ),
            'approval_aging' => array(
                array('label' => '0-2 days', 'value' => (int) (isset($aging['d0_2']) ? $aging['d0_2'] : 0)),
                array('label' => '3-5 days', 'value' => (int) (isset($aging['d3_5']) ? $aging['d3_5'] : 0)),
                array('label' => '6-14 days', 'value' => (int) (isset($aging['d6_14']) ? $aging['d6_14'] : 0)),
                array('label' => '15+ days', 'value' => (int) (isset($aging['d15_plus']) ? $aging['d15_plus'] : 0)),
            ),
            'trend' => $trend,
            'by_campus' => $by_campus,
            'by_head' => $by_head,
            'by_submitter' => $by_submitter,
            'heat' => array(
                'campuses' => $heat_campuses,
                'heads' => $heat_heads,
                'cells' => $heat_cells,
            ),
            'petty_cash' => $petty,
            'alerts' => $alerts,
        );
    }
}
