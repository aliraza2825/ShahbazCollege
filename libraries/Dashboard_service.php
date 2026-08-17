<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_service {

    /** @var CI_Controller */
    private $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->model('dashboards');
    }

    public function can_view_fee_status($user)
    {
        if (!$user) return false;
        if ($user['role'] === 'Admin') return true;
        $acc = $this->ci->db->get_where('access', array('user_id' => $user['user_id']))->row_array();
        return $acc && !empty($acc['dashboard_fee_status']);
    }

    public function meta($user)
    {
        return array(
            'permissions' => $this->permissions($user),
            'campuses' => $this->_campuses_for_user($user),
        );
    }

    public function permissions($user)
    {
        return array(
            'is_admin' => $this->_is_admin($user),
            'check_student' => $this->_can($user, 'dashboard_check_student_box'),
            'campus_status' => $this->_can($user, 'dashboard_campus_status_box'),
            'new_admission_entries' => $this->_can($user, 'dashboard_new_admisssion_entries_box'),
            'new_expense_entries' => $this->_can($user, 'dashboard_new_expense_entries_box'),
            'fee_status' => $this->can_view_fee_status($user),
            'update_fee_requests' => $this->_can($user, 'dashboard_update_payment_box'),
            'discount_requests' => $this->_can($user, 'dashboard_update_discount_box'),
            'update_student_requests' => $this->_can($user, 'dashboard_update_student_box'),
            'reminders_status' => $this->_can($user, 'dashboard_reminders_status'),
            'test_engine_questions' => $this->_can($user, 'dashboard_test_engine_questions'),
            'uncheck_assignments' => $this->_can($user, 'dashboard_uncheck_assignment'),
            'fee_reversal_requests' => $this->_can($user, 'dashboard_students_fees_reversal'),
            'total_students' => $this->_can($user, 'dashboard_total_student_box'),
            'total_teachers' => $this->_can($user, 'dashboard_total_teacher_box'),
            'new_admissions_month' => $this->_can($user, 'dashboard_new_admission'),
            'month_earning' => $this->_can($user, 'dashboard_month_earning'),
            'month_expense' => $this->_can($user, 'dashboard_month_expense'),
            'month_profit' => $this->_can($user, 'dashboard_month_profit'),
            'classes_status' => $this->_can($user, 'dashboard_classes_status'),
            'council_report' => $this->_can($user, 'council_report'),
            'online_applications' => $this->_can($user, 'online_application_new_admissions'),
            'expense_reversals' => $this->_can($user, 'expense_second_approval'),
            'struck_off_inquiry' => $this->_can($user, 'student_struck_off_list'),
            'struck_off_final' => $this->_can($user, 'student_delete'),
            'expense_approval' => $this->_can($user, 'expense_approval'),
        );
    }

    /** Clear procedure matrix + aggregate tiles (only permitted columns). */
    public function home_clear_procedure($user)
    {
        $this->_bootstrap_session($user);
        $this->ci->load->helper('custom');
        $perms = $this->permissions($user);
        $campuses = $this->_campuses_for_user($user);
        $matrix = array();
        $tiles = array(
            'new_admission_entries' => 0,
            'new_expense_entries' => 0,
            'fee_status' => 0,
            'update_fee_requests' => 0,
            'discount_requests' => 0,
            'update_student_requests' => 0,
            'reminders_pending' => 0,
            'reminders_under_review' => 0,
            'new_applications' => 0,
            'pending_applications' => 0,
        );

        foreach ($campuses as $campus) {
            $cid = (int) $campus['campus_id'];
            $row = array(
                'campus_id' => $cid,
                'campus_name' => $campus['campus_name'],
            );

            if ($perms['new_admission_entries']) {
                $n = count(dashboardNewAdmissions($cid));
                $row['new_admission_entries'] = $n;
                $tiles['new_admission_entries'] += $n;
            }
            if ($perms['new_expense_entries']) {
                $n = count(dashboardNewExpenseEntries($cid));
                $row['new_expense_entries'] = $n;
                $tiles['new_expense_entries'] += $n;
            }
            if ($perms['fee_status']) {
                $student = $this->_count_student_fee_groups($cid, $user);
                $contractor = $this->_count_contractor_fee_groups($cid, $user);
                $row['student_fee_count'] = $student;
                $row['contractor_fee_count'] = $contractor;
                $row['fee_status_count'] = $student + $contractor;
                $tiles['fee_status'] += $student + $contractor;
            }
            if ($perms['update_fee_requests']) {
                $n = count(dashboardUpdateFeeRequests($cid)) + count(dashboardUpdateFeeRequestsContractors($cid));
                $row['update_fee_requests'] = $n;
                $tiles['update_fee_requests'] += $n;
            }
            if ($perms['update_student_requests']) {
                $n = count(dashboardUpdateStudentRequests($cid));
                $row['update_student_requests'] = $n;
                $tiles['update_student_requests'] += $n;
            }
            if ($perms['reminders_status']) {
                $pending = count(dashboardPendingReminders($cid));
                $review = count(dashboardRemindersUnderReview($cid));
                $row['reminders_pending'] = $pending;
                $row['reminders_under_review'] = $review;
                $tiles['reminders_pending'] += $pending;
                $tiles['reminders_under_review'] += $review;
            }
            if ($perms['online_applications']) {
                $new_apps = dashboardNewApplications($cid);
                $pending_apps = dashboardPendingApplications($cid);
                $row['new_applications'] = $new_apps;
                $row['pending_applications'] = $pending_apps;
                $tiles['new_applications'] += $new_apps;
                $tiles['pending_applications'] += $pending_apps;
            }

            $matrix[] = $row;
        }

        if ($perms['discount_requests']) {
            $tiles['discount_requests'] = (int) $this->ci->db
                ->where('status', 0)
                ->count_all_results('discounts_approval');
        }

        return array('tiles' => $tiles, 'matrix' => $matrix);
    }

    /** Pending task tiles on dashboard home. */
    public function home_pending_tasks($user)
    {
        $this->_bootstrap_session($user);
        $perms = $this->permissions($user);
        $out = array();

        if ($perms['test_engine_questions']) {
            $out['test_engine_questions'] = (int) $this->ci->db
                ->where('status', 0)
                ->count_all_results('questions');
        }
        if ($perms['uncheck_assignments']) {
            $out['uncheck_assignments'] = count($this->ci->dashboards->getUncheckAssignments());
        }
        if ($perms['struck_off_inquiry']) {
            $out['struck_off_inquiry'] = $this->_struck_off_count(false);
        }
        if ($perms['struck_off_final']) {
            $out['struck_off_final'] = $this->_struck_off_count(true);
        }
        if ($perms['expense_approval']) {
            $row = $this->ci->db->query(
                "SELECT COUNT(*) AS c FROM expenses WHERE approved_status = '0' AND add_by != 'Muhammad Irfan'"
            )->row_array();
            $out['expense_approval'] = $row ? (int) $row['c'] : 0;
        }
        if ($perms['fee_reversal_requests']) {
            $out['fee_reversal_requests'] = (int) $this->ci->db
                ->where('status', 0)
                ->count_all_results('payments_reversal_requests');
        }
        if ($perms['expense_reversals']) {
            $this->ci->db->where(array('approved_status' => '1', 'rev_status' => '0'));
            if (!$this->_is_admin($user)) {
                $acc = $this->_access_row($user);
                $ids = ($acc && !empty($acc['campus_ids']))
                    ? array_filter(array_map('intval', explode(',', $acc['campus_ids'])))
                    : array(0);
                $this->ci->db->where_in('campus_id', $ids);
            }
            $out['expense_reversals'] = (int) $this->ci->db->count_all_results('expenses');
        }

        return $out;
    }

    /** Monthly statistics tiles. */
    public function home_statistics($user)
    {
        $this->_bootstrap_session($user);
        $perms = $this->permissions($user);
        $acc = $this->_access_row($user);
        $has_campus = $this->_is_admin($user) || ($acc && !empty($acc['campus_ids']));
        if (!$has_campus) {
            return array();
        }

        $out = array();
        if ($perms['total_students']) {
            $out['total_students'] = (int) $this->ci->dashboards->total_students();
        }
        if ($perms['total_teachers']) {
            $out['total_teachers'] = (int) $this->ci->dashboards->total_teachers();
        }
        if ($perms['new_admissions_month']) {
            $out['new_admissions_month'] = (int) $this->ci->dashboards->new_students_this_month();
        }
        if ($perms['month_earning']) {
            $earning = $this->ci->dashboards->getTotalSubmittedFee(date('Y-m-01'), date('Y-m-d'), 'actual_paid_date');
            $out['month_earning'] = isset($earning[0]['this_month_earning']) ? (float) $earning[0]['this_month_earning'] : 0;
        }
        if ($perms['month_expense']) {
            $expense = $this->ci->dashboards->thisMonthExpense();
            $out['month_expense'] = isset($expense[0]['this_month_expense']) ? (float) $expense[0]['this_month_expense'] : 0;
        }
        if ($perms['month_profit']) {
            $earning = $this->ci->dashboards->getTotalSubmittedFee(date('Y-m-01'), date('Y-m-d'), 'actual_paid_date');
            $expense = $this->ci->dashboards->thisMonthExpense();
            $e = isset($earning[0]['this_month_earning']) ? (float) $earning[0]['this_month_earning'] : 0;
            $x = isset($expense[0]['this_month_expense']) ? (float) $expense[0]['this_month_expense'] : 0;
            $out['month_profit'] = $e - $x;
        }
        if ($perms['classes_status']) {
            $out['classes_status'] = count($this->ci->dashboards->classesStatus());
        }

        return $out;
    }

    /** Logged-in user's due reminders (always available). */
    public function home_reminders($user)
    {
        $this->_bootstrap_session($user);
        $rows = $this->ci->dashboards->getReminders();
        $out = array();
        foreach ($rows as $r) {
            $out[] = array(
                'reminder_id' => (int) $r['reminder_id'],
                'note' => isset($r['note']) ? $r['note'] : '',
                'date' => isset($r['date']) ? $r['date'] : '',
                'status' => isset($r['status']) ? $r['status'] : '',
                'add_by' => isset($r['add_by']) ? $r['add_by'] : '',
            );
        }
        return $out;
    }

    /** Teacher's upcoming lectures for today / schedule. */
    public function home_lectures($user)
    {
        if (!$user) return array();
        $rows = $this->ci->db
            ->select('lectures.*, courses.course_name, campuses.campus_name, rooms.room_name')
            ->from('lectures')
            ->join('courses', 'courses.course_id = lectures.course', 'left')
            ->join('campuses', 'campuses.campus_id = lectures.campus', 'left')
            ->join('rooms', 'rooms.room_id = lectures.room', 'left')
            ->where('lectures.teacher', (int) $user['user_id'])
            ->order_by('lectures.date', 'ASC')
            ->limit(20)
            ->get()->result_array();
        $out = array();
        foreach ($rows as $r) {
            $out[] = array(
                'lecture_id' => (int) $r['lecture_id'],
                'course_name' => isset($r['course_name']) ? $r['course_name'] : '',
                'campus_name' => isset($r['campus_name']) ? $r['campus_name'] : '',
                'room_name' => isset($r['room_name']) ? $r['room_name'] : '',
                'date' => isset($r['date']) ? $r['date'] : '',
                'start_time' => isset($r['start_time']) ? $r['start_time'] : '',
                'end_time' => isset($r['end_time']) ? $r['end_time'] : '',
            );
        }
        return $out;
    }

    /** Campus P&L status table for date range. */
    public function campus_status($user, $from_date, $to_date, $date_type)
    {
        if (!$this->_can($user, 'dashboard_campus_status_box')) {
            return array();
        }
        $this->_bootstrap_session($user);
        $this->ci->load->helper('custom');
        if (!$from_date) $from_date = date('Y-m-01');
        if (!$to_date) $to_date = date('Y-m-d');
        if (!in_array($date_type, array('paid_date', 'actual_paid_date'), true)) {
            $date_type = 'actual_paid_date';
        }

        $campuses = $this->_campuses_for_user($user);
        $rows = array();
        foreach ($campuses as $campus) {
            $cid = (int) $campus['campus_id'];
            $rows[] = array(
                'campus_id' => $cid,
                'campus_name' => $campus['campus_name'],
                'new_admissions' => (int) getNewAdmissions($cid, $from_date, $to_date, $date_type),
                'fee_college' => (float) getFeeCollectinCollege($cid, $from_date, $to_date, $date_type),
                'fee_bank' => (float) getFeeCollectinBank($cid, $from_date, $to_date, $date_type),
                'expense' => (float) getCampusTotalExpense($cid, $from_date, $to_date),
            );
        }

        return array(
            'from_date' => $from_date,
            'to_date' => $to_date,
            'date_type' => $date_type,
            'rows' => $rows,
        );
    }

    public function clear_procedure($user)
    {
        if (!$this->can_view_fee_status($user)) return array();
        $rows = array();
        foreach ($this->_campuses_for_user($user) as $campus) {
            $cid = (int) $campus['campus_id'];
            $student_count = $this->_count_student_fee_groups($cid, $user);
            $contractor_count = $this->_count_contractor_fee_groups($cid, $user);
            $rows[] = array(
                'campus_id' => $cid,
                'campus_name' => $campus['campus_name'],
                'fee_status_count' => $student_count + $contractor_count,
                'student_fee_count' => $student_count,
                'contractor_fee_count' => $contractor_count,
            );
        }
        return $rows;
    }

    public function fee_status_page($user, $kind, $campus_id, $page, $page_size, $filters = array())
    {
        $kind = ($kind === 'contractor') ? 'contractor' : 'student';
        if ($kind === 'contractor') {
            $total = $this->_count_contractor_fee_groups($campus_id, $user, $filters);
            $ids = $this->_contractor_fee_page_ids($campus_id, $user, $page, $page_size, $filters);
            $rows = $this->_load_contractor_fee_rows($ids);
        } else {
            $total = $this->_count_student_fee_groups($campus_id, $user, $filters);
            $ids = $this->_student_fee_page_ids($campus_id, $user, $page, $page_size, $filters);
            $rows = $this->_load_student_fee_rows($ids);
        }

        $total_pages = $page_size > 0 ? (int) ceil($total / $page_size) : 1;
        if ($total_pages < 1) $total_pages = 1;

        return array(
            'kind' => $kind,
            'rows' => $rows,
            'filters' => $this->_normalize_fee_filters($filters),
            'pagination' => array(
                'page' => $page,
                'page_size' => $page_size,
                'total' => $total,
                'total_pages' => $total_pages,
            ),
        );
    }

    private function _normalize_fee_filters($filters)
    {
        $clear_status = isset($filters['clear_status']) ? $filters['clear_status'] : '';
        if (!in_array($clear_status, array('', 'all', 'clear', 'blocked'), true)) {
            $clear_status = '';
        }
        if ($clear_status === 'all') $clear_status = '';

        $date_field = (isset($filters['date_field']) && $filters['date_field'] === 'submit') ? 'submit' : 'paid';

        return array(
            'date_from' => isset($filters['date_from']) ? $filters['date_from'] : '',
            'date_to' => isset($filters['date_to']) ? $filters['date_to'] : '',
            'date_field' => $date_field,
            'clear_status' => $clear_status,
        );
    }

    private function _fee_filter_sql($filters)
    {
        $filters = $this->_normalize_fee_filters($filters);
        $sql = '';

        $date_col = ($filters['date_field'] === 'submit')
            ? 'payments.actual_paid_date'
            : 'payments.paid_date';

        if ($filters['date_from'] !== '') {
            $sql .= ' AND DATE('.$date_col.') >= DATE('.$this->ci->db->escape($filters['date_from']).')';
        }
        if ($filters['date_to'] !== '') {
            $sql .= ' AND DATE('.$date_col.') <= DATE('.$this->ci->db->escape($filters['date_to']).')';
        }

        if ($filters['clear_status'] === 'blocked') {
            $sql .= " AND LOWER(payments.fee_pay_through) = 'pay_pro'";
        } elseif ($filters['clear_status'] === 'clear') {
            $sql .= " AND (payments.fee_pay_through IS NULL OR LOWER(payments.fee_pay_through) != 'pay_pro')";
        }

        return $sql;
    }

    private function _is_paypro_payment($fee_pay_through)
    {
        return strtolower((string) $fee_pay_through) === 'pay_pro';
    }

    public function fee_status_detail($payment_id)
    {
        if ($payment_id <= 0) return null;
        $payment = $this->ci->db->get_where('payments', array('id' => $payment_id))->row_array();
        if (!$payment || (int) $payment['paid'] !== 1 || (int) $payment['clear_college_fee'] !== 0) {
            return null;
        }

        $is_contractor = !empty($payment['contract_id']);
        if ($is_contractor) {
            return $this->_contractor_detail($payment);
        }
        return $this->_student_detail($payment);
    }

    public function clear_fee($user, $payment_id)
    {
        if ($payment_id <= 0) return array('success' => false, 'message' => 'Invalid payment');
        $detail = $this->fee_status_detail($payment_id);
        if (!$detail) return array('success' => false, 'message' => 'Payment not found or already cleared');
        if (empty($detail['can_clear'])) {
            return array('success' => false, 'message' => isset($detail['clear_block_reason']) ? $detail['clear_block_reason'] : 'Cannot clear');
        }

        $name = trim((isset($user['first_name']) ? $user['first_name'] : '').' '.(isset($user['last_name']) ? $user['last_name'] : ''));
        if ($name === '') $name = 'Admin';

        $fee = $this->ci->db->get_where('payments', array('id' => $payment_id))->row_array();
        if (!empty($fee['merged_challan'])) {
            $this->ci->db->set('clear_college_fee', '1');
            $this->ci->db->set('clear_by', $name);
            $this->ci->db->where('merged_challan', $fee['merged_challan']);
            $this->ci->db->update('payments');
        } else {
            $this->ci->db->set('clear_college_fee', '1');
            $this->ci->db->set('clear_by', $name);
            $this->ci->db->where('id', $payment_id);
            $this->ci->db->update('payments');
        }

        return array('success' => true, 'message' => 'Fee cleared');
    }

    // --- Student list ---

    private function _student_fee_base_sql($campus_id, $user, $filters = array())
    {
        $sql = "
            FROM payments
            INNER JOIN students ON payments.student_id = students.student_id
            INNER JOIN classes ON classes.class_id = students.class_id
            LEFT JOIN courses ON courses.course_id = classes.course_id
            INNER JOIN campuses ON classes.campus_id = campuses.campus_id
            WHERE students.contract_id = 0
              AND payments.paid = 1
              AND payments.clear_college_fee = 0
              AND payments.fee_submit_type != 'computer_challan'
        ";
        if ($campus_id !== null && $campus_id !== '') {
            $sql .= ' AND campuses.campus_id = '.(int) $campus_id;
        }
        $sql .= $this->_fee_filter_sql($filters);
        return $sql;
    }

    private function _student_fee_group_by()
    {
        return " GROUP BY CASE WHEN payments.merged_challan IS NOT NULL THEN payments.merged_challan ELSE payments.challan_no END, payments.paid_challans ";
    }

    private function _count_student_fee_groups($campus_id, $user, $filters = array())
    {
        $sql = 'SELECT COUNT(*) AS c FROM (SELECT 1 '.$this->_student_fee_base_sql($campus_id, $user, $filters).$this->_student_fee_group_by().') t';
        $row = $this->ci->db->query($sql)->row_array();
        return $row ? (int) $row['c'] : 0;
    }

    private function _student_fee_page_ids($campus_id, $user, $page, $page_size, $filters = array())
    {
        $offset = ($page - 1) * $page_size;
        $sql = '
            SELECT MIN(payments.id) AS payment_id
            '.$this->_student_fee_base_sql($campus_id, $user, $filters).'
            '.$this->_student_fee_group_by().'
            ORDER BY MAX(payments.paid_date) DESC
            LIMIT '.(int) $page_size.' OFFSET '.(int) $offset;
        $rows = $this->ci->db->query($sql)->result_array();
        $ids = array();
        foreach ($rows as $r) {
            if (!empty($r['payment_id'])) $ids[] = (int) $r['payment_id'];
        }
        return $ids;
    }

    private function _load_student_fee_rows($ids)
    {
        if (empty($ids)) return array();
        $this->ci->db->select('payments.id, payments.challan_no, payments.paid_date, payments.actual_paid_date, payments.actual_amount, payments.amount, payments.fee_pay_through, payments.settlement_id, payments.settlement_payment_id, students.first_name, students.last_name, students.roll_no, students.cnic, students.mobile, students.emergency_no, classes.name AS class_name, courses.course_name, campuses.campus_id, campuses.campus_name');
        $this->ci->db->from('payments');
        $this->ci->db->join('students', 'payments.student_id=students.student_id', 'inner');
        $this->ci->db->join('classes', 'classes.class_id=students.class_id', 'inner');
        $this->ci->db->join('courses', 'courses.course_id=classes.course_id', 'left');
        $this->ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
        $this->ci->db->where_in('payments.id', $ids);
        $this->ci->db->order_by('payments.paid_date', 'DESC');
        $rows = $this->ci->db->get()->result_array();
        $out = array();
        foreach ($rows as $row) {
            if ($this->_is_paypro_payment(isset($row['fee_pay_through']) ? $row['fee_pay_through'] : '')) {
                $can = array('can_clear' => false, 'reason' => 'PayPro payment — open details before clearing');
            } else {
                $can = array('can_clear' => true, 'reason' => '');
            }
            $out[] = array(
                'kind' => 'student',
                'payment_id' => (int) $row['id'],
                'party_label' => trim($row['first_name'].' '.$row['last_name']),
                'roll_no' => $row['roll_no'],
                'cnic' => $row['cnic'],
                'campus_id' => (int) $row['campus_id'],
                'campus_name' => $row['campus_name'],
                'class_name' => $row['class_name'],
                'course_name' => $row['course_name'],
                'submit_date' => $row['actual_paid_date'],
                'paid_date' => $row['paid_date'],
                'amount' => $row['actual_amount'],
                'fee_pay_through' => $row['fee_pay_through'],
                'can_clear' => $can['can_clear'],
                'clear_block_reason' => $can['reason'],
            );
        }
        return $out;
    }

    // --- Contractor list ---

    private function _contractor_fee_base_sql($campus_id, $user, $filters = array())
    {
        $sql = "
            FROM payments
            INNER JOIN contracts ON contracts.contract_id = payments.contract_id
            INNER JOIN contractors ON contractors.contractor_id = contracts.contractor_id
            INNER JOIN campuses ON contracts.campus_id = campuses.campus_id
            WHERE payments.paid = 1
              AND payments.clear_college_fee = 0
              AND payments.fee_submit_type != 'computer_challan'
        ";
        if ($campus_id !== null && $campus_id !== '') {
            $sql .= ' AND campuses.campus_id = '.(int) $campus_id;
        }
        if (!$user || $user['role'] !== 'Admin') {
            $acc = $this->ci->db->get_where('access', array('user_id' => $user['user_id']))->row_array();
            $ids = ($acc && !empty($acc['campus_ids']))
                ? array_filter(array_map('intval', explode(',', $acc['campus_ids'])))
                : array(0);
            $sql .= ' AND campuses.campus_id IN ('.implode(',', $ids).')';
        }
        $sql .= $this->_fee_filter_sql($filters);
        return $sql;
    }

    private function _contractor_fee_group_by()
    {
        return " GROUP BY CASE WHEN payments.merged_challan IS NOT NULL THEN payments.merged_challan ELSE payments.challan_no END ";
    }

    private function _count_contractor_fee_groups($campus_id, $user, $filters = array())
    {
        $sql = 'SELECT COUNT(*) AS c FROM (SELECT 1 '.$this->_contractor_fee_base_sql($campus_id, $user, $filters).$this->_contractor_fee_group_by().') t';
        $row = $this->ci->db->query($sql)->row_array();
        return $row ? (int) $row['c'] : 0;
    }

    private function _contractor_fee_page_ids($campus_id, $user, $page, $page_size, $filters = array())
    {
        $offset = ($page - 1) * $page_size;
        $sql = '
            SELECT MIN(payments.id) AS payment_id
            '.$this->_contractor_fee_base_sql($campus_id, $user, $filters).'
            '.$this->_contractor_fee_group_by().'
            ORDER BY MAX(payments.paid_date) ASC
            LIMIT '.(int) $page_size.' OFFSET '.(int) $offset;
        $rows = $this->ci->db->query($sql)->result_array();
        $ids = array();
        foreach ($rows as $r) {
            if (!empty($r['payment_id'])) $ids[] = (int) $r['payment_id'];
        }
        return $ids;
    }

    private function _load_contractor_fee_rows($ids)
    {
        if (empty($ids)) return array();
        $this->ci->db->select('payments.id, payments.challan_no, payments.paid_date, payments.actual_paid_date, payments.actual_amount, payments.amount, payments.fee_pay_through, payments.contract_id, contractors.name AS contractor_name, contractors.contractor_id_from_college, campuses.campus_id, campuses.campus_name');
        $this->ci->db->from('payments');
        $this->ci->db->join('contracts', 'contracts.contract_id=payments.contract_id', 'inner');
        $this->ci->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'inner');
        $this->ci->db->join('campuses', 'contracts.campus_id=campuses.campus_id', 'inner');
        $this->ci->db->where_in('payments.id', $ids);
        $this->ci->db->order_by('payments.paid_date', 'ASC');
        $rows = $this->ci->db->get()->result_array();
        $out = array();
        foreach ($rows as $row) {
            $contract_name = '';
            if (!empty($row['contract_id'])) {
                $c = $this->ci->db->get_where('contracts', array('contract_id' => $row['contract_id']))->row_array();
                $contract_name = $c && isset($c['contract_name']) ? $c['contract_name'] : '';
            }
            $out[] = array(
                'kind' => 'contractor',
                'payment_id' => (int) $row['id'],
                'party_label' => $row['contractor_name'],
                'roll_no' => $row['contractor_id_from_college'],
                'contract_name' => $contract_name,
                'campus_id' => (int) $row['campus_id'],
                'campus_name' => $row['campus_name'],
                'submit_date' => $row['actual_paid_date'],
                'paid_date' => $row['paid_date'],
                'amount' => $row['actual_amount'],
                'fee_pay_through' => $row['fee_pay_through'],
                'can_clear' => !$this->_is_paypro_payment(isset($row['fee_pay_through']) ? $row['fee_pay_through'] : ''),
                'clear_block_reason' => $this->_is_paypro_payment(isset($row['fee_pay_through']) ? $row['fee_pay_through'] : '')
                    ? 'PayPro payment — open details before clearing'
                    : '',
            );
        }
        return $out;
    }

    // --- Detail ---

    private function _student_detail($payment)
    {
        $this->ci->db->select('students.*, classes.name AS class_name, courses.course_name, campuses.campus_name, payments.*');
        $this->ci->db->from('payments');
        $this->ci->db->join('students', 'payments.student_id=students.student_id', 'inner');
        $this->ci->db->join('classes', 'classes.class_id=students.class_id', 'inner');
        $this->ci->db->join('courses', 'courses.course_id=classes.course_id', 'left');
        $this->ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
        $this->ci->db->where('payments.id', (int) $payment['id']);
        $row = $this->ci->db->get()->row_array();
        if (!$row) return null;

        $can = $this->_can_clear_payment($row);
        $fee_lines = $this->_build_student_fee_lines($row);
        $paid_lines = $this->_build_paid_fee_lines($row);

        return array(
            'kind' => 'student',
            'payment_id' => (int) $row['id'],
            'party' => array(
                'course_name' => $row['course_name'],
                'campus_name' => $row['campus_name'],
                'class_name' => $row['class_name'],
                'name' => trim($row['first_name'].' '.$row['last_name']),
                'roll_no' => $row['roll_no'],
                'cnic' => $row['cnic'],
                'phone' => trim($row['mobile'].' '.$row['emergency_no']),
            ),
            'submit_date' => $row['actual_paid_date'],
            'paid_date' => $row['paid_date'],
            'fee_details' => $fee_lines,
            'paid_fee_details' => $paid_lines,
            'can_clear' => $can['can_clear'],
            'clear_block_reason' => $can['reason'],
            'links' => $this->_payment_links($row),
        );
    }

    private function _contractor_detail($payment)
    {
        $this->ci->db->select('payments.*, contractors.name AS contractor_name, contractors.contractor_id_from_college, contracts.contract_name, campuses.campus_name');
        $this->ci->db->from('payments');
        $this->ci->db->join('contracts', 'contracts.contract_id=payments.contract_id', 'inner');
        $this->ci->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'inner');
        $this->ci->db->join('campuses', 'contracts.campus_id=campuses.campus_id', 'inner');
        $this->ci->db->where('payments.id', (int) $payment['id']);
        $row = $this->ci->db->get()->row_array();
        if (!$row) return null;

        return array(
            'kind' => 'contractor',
            'payment_id' => (int) $row['id'],
            'party' => array(
                'campus_name' => $row['campus_name'],
                'name' => $row['contractor_name'],
                'contract_name' => $row['contract_name'],
                'roll_no' => $row['contractor_id_from_college'],
            ),
            'submit_date' => $row['actual_paid_date'],
            'paid_date' => $row['paid_date'],
            'fee_details' => $this->_build_contractor_fee_lines($row),
            'paid_fee_details' => $this->_build_paid_fee_lines($row),
            'can_clear' => true,
            'clear_block_reason' => '',
            'links' => $this->_payment_links($row),
        );
    }

    private function _build_student_fee_lines($row)
    {
        $lines = array();
        $totalpayable = 0;
        $totalfine = 0;

        if (!empty($row['merged_challan']) && (float) $row['actual_amount'] > 0 && !empty($row['paid_challans'])) {
            $ids = array_filter(array_map('trim', explode(',', rtrim($row['paid_challans'], ', '))));
            if (!empty($ids)) {
                $this->ci->db->select('payments.*, students.first_name, students.last_name, students.roll_no');
                $this->ci->db->from('payments');
                $this->ci->db->join('students', 'payments.student_id=students.student_id', 'inner');
                $this->ci->db->where_in('payments.challan_no', $ids);
                $merged = $this->ci->db->get()->result_array();
                foreach ($merged as $merg) {
                    $totalpayable += (float) $merg['amount'];
                    $fine = $this->_late_fine_days($merg['dead_line'], $merg['paid_date'], isset($row['payment_plan']) ? $row['payment_plan'] : '');
                    $totalfine += $fine['amount'];
                    $lines[] = 'Merged Challan # '.$merg['challan_no'].' '.$merg['payment_comment'];
                    $lines[] = 'Merged Amount: '.$merg['amount'];
                }
            }
        } else {
            $totalpayable = (float) $row['amount'];
            $lines[] = 'Challan # '.$row['challan_no'].' '.(isset($row['payment_comment']) ? $row['payment_comment'] : '');
            $lines[] = 'Installment Amount: '.$row['amount'];
        }

        $lines[] = 'Discount: '.(isset($row['discount']) ? $row['discount'] : '0');
        $lines[] = 'Previous Installment Amount: '.(isset($row['remaining_installment_amount']) ? $row['remaining_installment_amount'] : '0');
        $lines[] = 'Previous Fine Amount: '.(isset($row['extra_amount']) ? $row['extra_amount'] : '0');
        $lines[] = 'Installment Status: '.((int) $row['paid'] === 1 ? 'Paid' : 'Unpaid');

        if ((int) $row['paid'] === 1) {
            $lines[] = 'Late Fee Fine: '.$totalfine;
            $lines[] = 'Removed Fine: '.(isset($row['removed_fine']) ? $row['removed_fine'] : '0');
            $payable = $totalpayable + (float) $row['remaining_installment_amount'] + (float) $row['extra_amount'] + $totalfine;
            $lines[] = 'Payable Amount: '.$payable;
            $lines[] = 'Paid Amount: '.$row['actual_amount'];
        } else {
            $fine = $this->_late_fine_days($row['dead_line'], date('Y-m-d'), isset($row['payment_plan']) ? $row['payment_plan'] : '');
            if ($fine['days'] > 0) $lines[] = 'Late Fee Days: '.$fine['days'];
            $lines[] = 'Late Fee Amount: '.$fine['amount'];
            $payable = (float) $row['amount'] + (float) $row['remaining_installment_amount'] + (float) $row['extra_amount'] + $fine['amount'];
            $lines[] = 'Payable Amount: '.$payable;
        }

        return $lines;
    }

    private function _build_contractor_fee_lines($row)
    {
        $lines = array();
        if (isset($row['fee_pay_through']) && $row['fee_pay_through'] === 'bank' && !empty($row['statement_id'])) {
            $fee_counts = $this->ci->db
                ->select('students.first_name, students.last_name, students.roll_no, payments.challan_no, payments.amount')
                ->from('payments')
                ->join('students', 'students.student_id=payments.student_id', 'inner')
                ->where('payments.statement_id', $row['statement_id'])
                ->get()->result_array();
            if (!empty($fee_counts)) {
                $total = 0;
                foreach ($fee_counts as $fc) {
                    $lines[] = 'Student: '.$fc['first_name'].' '.$fc['last_name'];
                    $lines[] = 'Roll No: '.$fc['roll_no'];
                    $lines[] = 'Challan No: '.$fc['challan_no'];
                    $lines[] = 'Payable Amount: '.$fc['amount'];
                    $lines[] = '---';
                    $total += (float) $fc['amount'];
                }
                $lines[] = 'Total Students: '.count($fee_counts);
                $lines[] = 'Total Fees: '.$total;
            }
        }
        if (empty($lines)) {
            $lines[] = 'Challan # '.$row['challan_no'];
            $lines[] = 'Installment Amount: '.$row['amount'];
        }
        return $lines;
    }

    private function _build_paid_fee_lines($row)
    {
        $lines = array();
        if ((int) $row['paid'] !== 1) return $lines;

        if (empty($row['contract_id']) && !empty($row['student_id'])) {
            $this->ci->db->from('payments');
            $this->ci->db->where(array('dead_line<=' => $row['dead_line'], 'student_id' => $row['student_id']));
            $this->ci->db->group_by('merged_challan');
            $this->ci->db->order_by('dead_line', 'ASC');
            $installment_no = $this->ci->db->count_all_results();
            $lines[] = 'Installment No. '.$installment_no;
        }

        $lines[] = 'Paid Amount: '.$row['actual_amount'];

        if (!empty($row['shifted_installment'])) $lines[] = 'Shifted Previous Installment Amount: '.$row['shifted_installment'];
        if (!empty($row['shifted_previous_fine'])) $lines[] = 'Shifted Previous Installment Fine: '.$row['shifted_previous_fine'];
        if (!empty($row['shifted_fine'])) $lines[] = 'Shifted Current Installment Fine: '.$row['shifted_fine'];
        if (!empty($row['removed_previous_fine'])) $lines[] = 'Removed Previous Installment Fine: '.$row['removed_previous_fine'];
        if (!empty($row['removed_fine'])) $lines[] = 'Removed Current Installment Fine: '.$row['removed_fine'];

        $lines[] = 'Paid Date: '.$row['paid_date'];
        $lines[] = 'Paid Date System: '.(isset($row['updated_at']) ? $row['updated_at'] : '');
        $lines[] = 'Fee Pay Through: '.$row['fee_pay_through'];

        if ($row['fee_pay_through'] === 'bank') {
            $lines[] = 'Bank: '.(isset($row['bank_details']) ? $row['bank_details'] : '');
            $lines[] = 'Bank Challan / TID No.: '.(isset($row['tid_no']) ? $row['tid_no'] : '');
            $lines[] = 'Merged against Challan.: '.(isset($row['paid_challans']) ? $row['paid_challans'] : '');
        }

        if ($row['fee_pay_through'] === 'college' && isset($row['fee_submit_type']) && $row['fee_submit_type'] === 'receipt_book') {
            $campus = $this->ci->db->get_where('campuses', array('campus_id' => $row['submitted_fee_campus_id']))->row_array();
            $lines[] = 'Pad of: '.($campus ? $campus['campus_name'] : '');
            $lines[] = 'Book No.: '.(isset($row['book_no']) ? $row['book_no'] : '');
            $lines[] = 'Receipt No.: '.(isset($row['receipt_no']) ? $row['receipt_no'] : '');
        }

        if ($row['fee_pay_through'] === 'college' && isset($row['fee_submit_type']) && $row['fee_submit_type'] === 'computer_challan') {
            $lines[] = 'Pay by: Computer Challan';
        }

        if (!empty($row['paid_by'])) $lines[] = 'Paid BY.: '.$row['paid_by'];

        return $lines;
    }

    private function _payment_links($row)
    {
        $links = array();
        if (!empty($row['online_scan_challan'])) {
            $links[] = array('label' => 'See Challan', 'url' => $row['online_scan_challan']);
        } elseif (!empty($row['scan_challan'])) {
            $links[] = array('label' => 'See Challan', 'url' => base_url().'uploads/'.$row['scan_challan']);
        }
        if (isset($row['fee_pay_through']) && $row['fee_pay_through'] === 'college' && isset($row['fee_submit_type']) && $row['fee_submit_type'] === 'computer_challan') {
            $links[] = array('label' => 'Print College Challan', 'url' => site_url('students/print_college_challan/'.$row['id']));
        }
        if (!empty($row['fine_application']) && (int) $row['paid'] === 1) {
            $links[] = array('label' => 'See Application', 'url' => base_url().'uploads/'.$row['fine_application']);
        }
        if ($this->_is_paypro_payment(isset($row['fee_pay_through']) ? $row['fee_pay_through'] : '') && !empty($row['settlement_id'])) {
            $links[] = array('label' => 'PayPro Details', 'url' => site_url('excel_import/entries/'.$row['settlement_id']));
        }
        return $links;
    }

    private function _can_clear_payment($row)
    {
        if ($this->_is_paypro_payment(isset($row['fee_pay_through']) ? $row['fee_pay_through'] : '')) {
            $tagged = $this->_paypro_tagged($row);
            if (!$tagged) {
                return array('can_clear' => false, 'reason' => 'PayPro payment not tagged');
            }
        }
        return array('can_clear' => true, 'reason' => '');
    }

    private function _paypro_tagged($row)
    {
        if (empty($row['settlement_id']) || empty($row['settlement_payment_id'])) return false;
        $paypro_payment = $this->ci->db->get_where('settlement_payments', array('id' => $row['settlement_payment_id']))->row_array();
        if (!$paypro_payment) return false;
        $stats = $this->ci->db
            ->select('bank_reconciliation_statement.*, accounts.account_title, accounts.account_name')
            ->from('bank_reconciliation_statement')
            ->join('pay_pro_settlement', 'pay_pro_settlement.id = bank_reconciliation_statement.paypro_id', 'inner')
            ->join('accounts', 'accounts.id = bank_reconciliation_statement.account_id', 'inner')
            ->where('bank_reconciliation_statement.paypro_id', $row['settlement_id'])
            ->get()->result_array();
        foreach ($stats as $stat) {
            $credit = (int) str_replace(',', '', $stat['credit']);
            $via = isset($paypro_payment['paid_via']) ? $paypro_payment['paid_via'] : '';
            if ($via === '1LINK' || $via === '1Link' || $via === 'MBL') {
                if ($credit === (int) $stat['link_amount'] || $credit === (int) $stat['paid_amount']) return true;
            } elseif ($credit === (int) $stat['card_amount']) {
                return true;
            }
        }
        return false;
    }

    private function _late_fine_days($dead_line, $compare_date, $payment_plan)
    {
        if (empty($dead_line) || empty($compare_date)) return array('days' => 0, 'amount' => 0);
        $d1 = date_create($dead_line);
        $d2 = date_create($compare_date);
        if (!$d1 || !$d2) return array('days' => 0, 'amount' => 0);
        $diff = (int) date_diff($d1, $d2)->format('%R%a');
        if ($diff <= 0) return array('days' => 0, 'amount' => 0);
        $rate = ($payment_plan === '24 Installments') ? 10 : 50;
        return array('days' => $diff, 'amount' => $diff * $rate);
    }

    private function _campuses_for_user($user)
    {
        if ($user && $user['role'] === 'Admin') {
            return $this->ci->db->order_by('campus_name', 'ASC')->get('campuses')->result_array();
        }
        $acc = $this->ci->db->get_where('access', array('user_id' => $user['user_id']))->row_array();
        $ids = ($acc && !empty($acc['campus_ids']))
            ? array_filter(array_map('intval', explode(',', $acc['campus_ids'])))
            : array();
        if (!count($ids)) return array();
        return $this->ci->db->where_in('campus_id', $ids)->order_by('campus_name', 'ASC')->get('campuses')->result_array();
    }

    private function _bootstrap_session($user)
    {
        if (!$user) return;
        $name = trim((isset($user['first_name']) ? $user['first_name'] : '').' '.(isset($user['last_name']) ? $user['last_name'] : ''));
        $this->ci->session->set_userdata(array(
            'user_id' => (int) $user['user_id'],
            'role' => isset($user['role']) ? $user['role'] : '',
            'name' => $name,
        ));
    }

    private function _access_row($user)
    {
        if (!$user) return null;
        static $cache = array();
        $uid = (int) $user['user_id'];
        if (!isset($cache[$uid])) {
            $cache[$uid] = $this->ci->db->get_where('access', array('user_id' => $uid))->row_array();
        }
        return $cache[$uid];
    }

    private function _is_admin($user)
    {
        return $user && isset($user['role']) && $user['role'] === 'Admin';
    }

    private function _can($user, $key)
    {
        if (!$user) return false;
        if ($this->_is_admin($user)) return true;
        $acc = $this->_access_row($user);
        return $acc && !empty($acc[$key]);
    }

    /** @param bool $final true = final struck-off pending delete */
    private function _struck_off_count($final)
    {
        if ($final) {
            $sql = "SELECT COUNT(ast.student_id) AS c FROM struckofdetails_students ast
                WHERE (ast.status = 0 AND (SELECT COUNT(student_id) FROM struckofdetails_students WHERE student_id = ast.student_id) = 3)
                   OR (ast.action_type = 1 AND ast.status = 0)
                GROUP BY ast.student_id";
        } else {
            $sql = "SELECT COUNT(struckofdetails_students.student_id) AS c FROM struckofdetails_students
                WHERE struckofdetails_students.status = '0'
                GROUP BY struckofdetails_students.student_id";
        }
        $rows = $this->ci->db->query($sql)->result_array();
        return count($rows);
    }
}
