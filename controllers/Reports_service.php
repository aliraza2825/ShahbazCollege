<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Reports module business logic for React POS (legacy /reports/* parity).
 */
class Reports_service {

    /** @var CI_Controller */
    private $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->helper('custom');
        $this->ensure_discount_report_columns();
    }

    private function ensure_discount_report_columns()
    {
        if ($this->ci->db->table_exists('discounts_approval')) {
            if (!$this->ci->db->field_exists('approved_by', 'discounts_approval')) {
                $this->ci->db->query("ALTER TABLE discounts_approval ADD approved_by VARCHAR(255) NULL AFTER created_by");
            }
            if (!$this->ci->db->field_exists('approved_at', 'discounts_approval')) {
                $this->ci->db->query("ALTER TABLE discounts_approval ADD approved_at DATETIME NULL AFTER approved_by");
            }
        }
        foreach (array('access_rules', 'access') as $table) {
            if ($this->ci->db->table_exists($table) && !$this->ci->db->field_exists('reports_discount_report', $table)) {
                $this->ci->db->query("ALTER TABLE `$table` ADD `reports_discount_report` TINYINT(1) NULL DEFAULT NULL");
            }
        }
    }

    private function _access_row($user)
    {
        if (!$user || empty($user['user_id'])) {
            return null;
        }
        return $this->ci->db->get_where('access', array('user_id' => $user['user_id']))->row_array();
    }

    private function _is_admin($user)
    {
        return $user && isset($user['role']) && $user['role'] === 'Admin';
    }

    private function _user_perm($user, $key)
    {
        if ($this->_is_admin($user)) {
            return true;
        }
        $acc = $this->_access_row($user);
        return $acc && !empty($acc[$key]);
    }

    private function _campus_ids($user)
    {
        $acc = $this->_access_row($user);
        if (!$acc || empty($acc['campus_ids'])) {
            return array();
        }
        return array_values(array_filter(array_map('intval', explode(',', $acc['campus_ids']))));
    }

    private function _class_ids($user)
    {
        $acc = $this->_access_row($user);
        if (!$acc || empty($acc['class_ids'])) {
            return array();
        }
        return array_values(array_filter(array_map('intval', explode(',', $acc['class_ids']))));
    }

    public function permissions($user)
    {
        $is_admin = $this->_is_admin($user);
        $acc = $is_admin ? array() : $this->_access_row($user);

        return array(
            'is_admin' => $is_admin,
            'sidebar' => $is_admin || !empty($acc['reports_sidebar']),
            'how_to_use' => $is_admin,
            'students_fee_problem' => $is_admin || !empty($acc['reports_student_fee_problem']),
            'discount_report' => $is_admin || !empty($acc['reports_discount_report']),
            'struckoff_report' => $is_admin || !empty($acc['all_struckofstudent_report']),
            'teacher_questions' => $is_admin || !empty($acc['reports_sidebar']),
            'sms_devices' => $is_admin || !empty($acc['reports_sidebar']),
            'agent_statement' => $is_admin || !empty($acc['agent_view_statement']),
            'agent_statement_coo' => $is_admin || !empty($acc['agent_view_statement_coo']),
            'students_backup' => $is_admin || !empty($acc['student_backup_report']),
            'coo_cash_report' => $is_admin || !empty($acc['closing_coo']),
        );
    }

    public function can_access($user)
    {
        $p = $this->permissions($user);
        if ($p['sidebar']) {
            return true;
        }
        foreach ($p as $k => $v) {
            if ($k === 'is_admin' || $k === 'sidebar') {
                continue;
            }
            if ($v) {
                return true;
            }
        }
        return false;
    }

    public function meta($user)
    {
        $is_admin = $this->_is_admin($user);

        $this->ci->db->select('course_id, course_name');
        $this->ci->db->from('courses');
        $this->ci->db->where('status', 1);
        $this->ci->db->order_by('course_name', 'ASC');
        $courses = $this->ci->db->get()->result_array();

        $this->ci->db->select('users.user_id, users.first_name, users.last_name, designations.designation_name');
        $this->ci->db->from('users');
        $this->ci->db->join('designations', 'designations.designation_id = users.designation_id', 'left');
        $this->ci->db->join('departments', 'departments.department_id = users.department_id', 'left');
        $this->ci->db->where('users.status', 1);
        $this->ci->db->where('departments.department_id', 13);
        $this->ci->db->order_by('users.first_name', 'ASC');
        $teachers = $this->ci->db->get()->result_array();

        $accounts = $this->ci->db->query('SELECT id, account_title, account_name FROM accounts WHERE type = "1" ORDER BY account_title ASC')->result_array();

        return array(
            'permissions' => $this->permissions($user),
            'courses' => $courses,
            'teachers' => $teachers,
            'bank_accounts' => $accounts,
            'legacy_root' => rtrim(base_url(), '/'),
            'uploads_root' => rtrim(base_url(), '/') . '/uploads/',
        );
    }

    public function course_subjects($course_id)
    {
        return $this->ci->db
            ->select('course_subject_id, subject_name')
            ->where('course_id', (int)$course_id)
            ->order_by('subject_name', 'ASC')
            ->get('course_subjects')
            ->result_array();
    }

    /**
     * Single-query fee-problem counts per campus (replaces N×M studentsFeeNotCreated calls).
     */
    private function _fee_problem_counts_by_campus($campus_ids = null)
    {
        $campus_filter = '';
        if (is_array($campus_ids) && !empty($campus_ids)) {
            $ids = array_map('intval', $campus_ids);
            $campus_filter = ' AND c.campus_id IN (' . implode(',', $ids) . ')';
        }

        $sql = "
            SELECT c.campus_id, COUNT(DISTINCT s.student_id) AS student_count
            FROM students s
            INNER JOIN classes cl ON cl.class_id = s.class_id
            INNER JOIN campuses c ON c.campus_id = cl.campus_id
            LEFT JOIN (
                SELECT student_id, SUM(amount) AS paid_amount
                FROM payments
                WHERE payment_plan != 'consulation fee'
                GROUP BY student_id
            ) pay ON pay.student_id = s.student_id
            WHERE s.status = 1
              AND s.contract_id = 0
              AND c.status = 1
              AND (
                pay.paid_amount IS NULL
                OR pay.paid_amount < s.total_fee
              )
              {$campus_filter}
            GROUP BY c.campus_id
        ";

        $rows = $this->ci->db->query($sql)->result_array();
        $map = array();
        foreach ($rows as $row) {
            $map[(int)$row['campus_id']] = (int)$row['student_count'];
        }
        return $map;
    }

    /**
     * Students with incomplete fee setup for one campus (one query + payment flags).
     */
    private function _fee_problem_students_for_campus($campus_id)
    {
        $campus_id = (int)$campus_id;
        $sql = "
            SELECT
                s.*,
                cl.name AS class_name,
                c.campus_name,
                co.course_name,
                pay.paid_amount,
                IF(fee_pay.fee_row_id IS NULL, 1, 0) AS fee_missing
            FROM students s
            INNER JOIN classes cl ON cl.class_id = s.class_id
            INNER JOIN courses co ON co.course_id = cl.course_id
            INNER JOIN campuses c ON c.campus_id = cl.campus_id
            LEFT JOIN (
                SELECT student_id, SUM(amount) AS paid_amount
                FROM payments
                WHERE payment_plan != 'consulation fee'
                GROUP BY student_id
            ) pay ON pay.student_id = s.student_id
            LEFT JOIN (
                SELECT MIN(id) AS fee_row_id, student_id
                FROM payments
                WHERE contract_id = 0 AND payment_plan != 'consulation fee'
                GROUP BY student_id
            ) fee_pay ON fee_pay.student_id = s.student_id
            WHERE c.campus_id = {$campus_id}
              AND s.status = 1
              AND s.contract_id = 0
              AND (
                pay.paid_amount IS NULL
                OR pay.paid_amount < s.total_fee
              )
            ORDER BY s.roll_no ASC
        ";

        $students = $this->ci->db->query($sql)->result_array();
        foreach ($students as $i => $student) {
            if ((int)$student['contractor_id'] > 0) {
                $students[$i]['fee_missing'] = 0;
            }
        }
        return $students;
    }

    public function students_fee_problem($user)
    {
        $perms = $this->permissions($user);
        if (!$perms['students_fee_problem']) {
            return array('success' => false, 'message' => 'Access denied');
        }

        $campus_ids = $this->_campus_ids($user);
        $this->ci->db->select('campus_id, campus_name');
        $this->ci->db->from('campuses');
        if (!$this->_is_admin($user) && !empty($campus_ids)) {
            $this->ci->db->where_in('campus_id', $campus_ids);
        }
        $this->ci->db->where('campuses.status', 1);
        $this->ci->db->order_by('campus_name', 'ASC');
        $campuses = $this->ci->db->get()->result_array();

        $filter_ids = null;
        if (!$this->_is_admin($user) && !empty($campus_ids)) {
            $filter_ids = $campus_ids;
        }
        $counts = $this->_fee_problem_counts_by_campus($filter_ids);

        foreach ($campuses as $i => $campus) {
            $cid = (int)$campus['campus_id'];
            $campuses[$i]['student_count'] = isset($counts[$cid]) ? $counts[$cid] : 0;
        }

        return array('success' => true, 'data' => array('campuses' => $campuses));
    }

    public function campus_fee_problem($user, $campus_id)
    {
        $perms = $this->permissions($user);
        if (!$perms['students_fee_problem']) {
            return array('success' => false, 'message' => 'Access denied');
        }

        $campus_id = (int)$campus_id;
        if (!$this->_is_admin($user)) {
            $allowed = $this->_campus_ids($user);
            if (!in_array($campus_id, $allowed, true)) {
                return array('success' => false, 'message' => 'Campus access denied');
            }
        }

        $stud = $this->_fee_problem_students_for_campus($campus_id);

        return array('success' => true, 'data' => array('students' => $stud, 'campus_id' => $campus_id));
    }

    public function discount_report($user, $from_date, $to_date)
    {
        $perms = $this->permissions($user);
        if (!$perms['discount_report']) {
            return array('success' => false, 'message' => 'Access denied');
        }

        $fromDate = $from_date ?: date('Y-m-01');
        $toDate = $to_date ?: date('Y-m-d');
        $campusIds = $this->_campus_ids($user);

        $this->ci->db->select('
            discounts_approval.*,
            students.first_name,
            students.last_name,
            students.roll_no,
            students.cnic,
            classes.name as class_name,
            courses.course_name,
            campuses.campus_name
        ');
        $this->ci->db->from('discounts_approval');
        $this->ci->db->join('students', 'students.student_id = discounts_approval.student_id', 'left');
        $this->ci->db->join('classes', 'classes.class_id = students.class_id', 'left');
        $this->ci->db->join('courses', 'courses.course_id = classes.course_id', 'left');
        $this->ci->db->join('campuses', 'campuses.campus_id = classes.campus_id', 'left');
        $this->ci->db->where('discounts_approval.status', 1);
        $this->ci->db->where('DATE(discounts_approval.created_at) >=', $fromDate);
        $this->ci->db->where('DATE(discounts_approval.created_at) <=', $toDate);
        if (!$this->_is_admin($user) && !empty($campusIds)) {
            $this->ci->db->where_in('campuses.campus_id', $campusIds);
        }
        $this->ci->db->order_by('discounts_approval.created_at', 'DESC');
        $discounts = $this->ci->db->get()->result_array();

        $total = 0;
        $uploads_base = rtrim(base_url(), '/') . '/uploads/';
        foreach ($discounts as $i => $d) {
            $total += (float)$d['discount'];
            if (!empty($d['application'])) {
                $discounts[$i]['application_url'] = $uploads_base . ltrim($d['application'], '/');
                $discounts[$i]['application_ext'] = strtolower(pathinfo($d['application'], PATHINFO_EXTENSION));
            }
        }

        return array(
            'success' => true,
            'data' => array(
                'discounts' => $discounts,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'total_discount' => $total,
                'count' => count($discounts),
            ),
        );
    }

    public function sms_devices_data()
    {
        $sms_gateways = $this->ci->db
            ->select('sms_gateway.*, campuses.campus_name')
            ->join('campuses', 'campuses.campus_id = sms_gateway.campus_id')
            ->get_where('sms_gateway', array('sms_gateway.status' => 'active'))
            ->result_array();

        $today = date('Y-m-d');
        $rows = array();
        foreach ($sms_gateways as $key => $purchase) {
            $sms_count = $this->ci->db
                ->select('count(*) as sms_count')
                ->get_where('sms', "sms.sent_from = '" . $purchase['device_id'] . "' and date >= '" . $today . " 00:00:00' and date <= '" . $today . " 23:59:59'")
                ->row();
            $rows[] = array(
                'id' => $key,
                'campus_name' => $purchase['campus_name'],
                'device_id' => $purchase['device_id'],
                'percentage' => $purchase['percentage'],
                'last_sent' => $purchase['updated_at'],
                'sms_count' => $sms_count ? (int)$sms_count->sms_count : 0,
            );
        }

        return array('success' => true, 'data' => array('devices' => $rows));
    }

    private function _dates_from_range($start, $end)
    {
        $array = array();
        $interval = new DateInterval('P1D');
        $realEnd = new DateTime($end);
        $realEnd->add($interval);
        $period = new DatePeriod(new DateTime($start), $interval, $realEnd);
        foreach ($period as $date) {
            $array[] = $date->format('Y-m-d');
        }
        return $array;
    }

    public function teacher_questions_report($course_id, $subject_id, $teacher_id, $from_date, $to_date)
    {
        $course = $this->ci->db->get_where('courses', array('course_id' => (int)$course_id))->row_array();
        $subject = $this->ci->db->get_where('course_subjects', array('course_subject_id' => (int)$subject_id))->row_array();
        $teacher = $this->ci->db->get_where('users', array('user_id' => (int)$teacher_id))->row_array();
        if (!$course || !$subject || !$teacher) {
            return array('success' => false, 'message' => 'Invalid filters');
        }

        $topics = $this->ci->db->get_where('topics', array('course_subject_id' => (int)$subject_id))->result_array();
        $topic_ids = array_column($topics, 'topic_id');
        if (empty($topic_ids)) {
            return array('success' => true, 'data' => array('rows' => array(), 'course' => $course, 'subject' => $subject, 'teacher' => $teacher));
        }

        $teacher_name = trim($teacher['first_name'] . ' ' . $teacher['last_name']);
        $dates = $this->_dates_from_range($from_date, $to_date);
        $rows = array();

        foreach ($dates as $date) {
            $this->ci->db->select('*');
            $this->ci->db->from('questions');
            $this->ci->db->where_in('topic_id', $topic_ids);
            $this->ci->db->where("option_1!='' and created_at >='$date 00:00:00' and created_at <='$date 23:59:59' and add_by = " . $this->ci->db->escape($teacher_name));
            $mcqs = $this->ci->db->get()->result_array();

            $short = $this->ci->db->where_in('topic_id', $topic_ids)->get_where('questions', "type = 'short-question' and created_at >='$date 00:00:00' and created_at <='$date 23:59:59' and add_by = " . $this->ci->db->escape($teacher_name))->result_array();
            $long = $this->ci->db->where_in('topic_id', $topic_ids)->get_where('questions', "type = 'long-question' and created_at >='$date 00:00:00' and created_at <='$date 23:59:59' and add_by = " . $this->ci->db->escape($teacher_name))->result_array();
            $word = $this->ci->db->where_in('topic_id', $topic_ids)->get_where('questions', "type = 'word-meaning' and created_at >='$date 00:00:00' and created_at <='$date 23:59:59' and add_by = " . $this->ci->db->escape($teacher_name))->result_array();
            $videos = $this->ci->db->where_in('topic_id', $topic_ids)->get_where('question_videos', "created_at >='$date 00:00:00' and created_at <='$date 23:59:59' and created_by = " . $this->ci->db->escape($teacher_name))->result_array();

            $rows[] = array(
                'date' => $date,
                'course_name' => $course['course_name'],
                'subject_name' => $subject['subject_name'],
                'teacher_name' => $teacher_name,
                'mcqs' => count($mcqs),
                'short' => count($short),
                'long' => count($long),
                'word_meaning' => count($word),
                'videos' => count($videos),
                'subject_id' => (int)$subject_id,
                'teacher_id' => (int)$teacher_id,
            );
        }

        return array(
            'success' => true,
            'data' => array(
                'rows' => $rows,
                'from_date' => $from_date,
                'to_date' => $to_date,
                'course' => $course,
                'subject' => $subject,
                'teacher' => $teacher,
            ),
        );
    }

    private function _statement_entries($from_date, $to_date, $account_id, $amount = null)
    {
        $this->ci->db->select('*,bank_reconciliation_statement.id as trans_id,bank_reconciliation_statement.statement_id as str_id,bank_reconciliation_statement.closing_id as closing_bank_id');
        $this->ci->db->from('bank_reconciliation_statement');
        $this->ci->db->join('payments', 'payments.statement_id = bank_reconciliation_statement.id', 'left');
        $this->ci->db->join('accounts', 'accounts.id=bank_reconciliation_statement.account_id', 'left');
        if ($amount !== null && $amount !== '') {
            $this->ci->db->where(
                "bank_reconciliation_statement.trans_date = " . $this->ci->db->escape($from_date) .
                " AND CAST(REPLACE(bank_reconciliation_statement.credit, ',', '') AS DECIMAL(15,2)) = " . (float)$amount .
                " and bank_reconciliation_statement.account_id = '" . (int)$account_id . "'"
            );
        } else {
            $this->ci->db->where(
                "bank_reconciliation_statement.trans_date >= " . $this->ci->db->escape($from_date) .
                " and bank_reconciliation_statement.trans_date <= " . $this->ci->db->escape($to_date) .
                " and bank_reconciliation_statement.account_id = '" . (int)$account_id . "'"
            );
        }
        $this->ci->db->group_by('bank_reconciliation_statement.description,bank_reconciliation_statement.trans_date,bank_reconciliation_statement.credit,bank_reconciliation_statement.debit');
        $this->ci->db->order_by('bank_reconciliation_statement.trans_date', 'ASC');
        return $this->ci->db->get()->result_array();
    }

    private function _enrich_statement_entry($entry)
    {
        $related = array();
        if (!empty($entry['statement_id'])) {
            $this->ci->db->select('payments.*,students.first_name,students.last_name,students.roll_no,students.cnic,students.mobile,students.emergency_no,classes.name as class_name,campuses.campus_name,courses.course_name');
            $this->ci->db->from('payments');
            $this->ci->db->join('students', 'students.student_id=payments.student_id', 'inner');
            $this->ci->db->join('classes', 'classes.class_id=students.class_id', 'inner');
            $this->ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
            $this->ci->db->join('courses', 'courses.course_id=students.course_id', 'left');
            $this->ci->db->where_in('payments.statement_id', $entry['statement_id']);
            $related['payments'] = $this->ci->db->get()->result_array();
        } elseif (!empty($entry['expense_id'])) {
            $this->ci->db->select('expenses.*,expense_category.name as category_name,campuses.campus_name');
            $this->ci->db->from('expenses');
            $this->ci->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
            $this->ci->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
            $this->ci->db->where('expenses.expense_id', $entry['expense_id']);
            $related['expenses'] = $this->ci->db->get()->result_array();
        }
        $entry['related'] = $related;
        return $entry;
    }

    public function agent_view_statement($user, $from_date, $amount, $account_id)
    {
        $perms = $this->permissions($user);
        if (!$perms['agent_statement']) {
            return array('success' => false, 'message' => 'Access denied');
        }
        $entries = $this->_statement_entries($from_date, $from_date, $account_id, $amount);
        foreach ($entries as $i => $entry) {
            $entries[$i] = $this->_enrich_statement_entry($entry);
        }
        return array(
            'success' => true,
            'data' => array(
                'entries' => $entries,
                'from_date' => $from_date,
                'amount' => $amount,
                'account_id' => (int)$account_id,
            ),
        );
    }

    public function agent_view_statement_coo($user, $from_date, $to_date, $account_id)
    {
        $perms = $this->permissions($user);
        if (!$perms['agent_statement_coo']) {
            return array('success' => false, 'message' => 'Access denied');
        }
        $entries = $this->_statement_entries($from_date, $to_date, $account_id);
        foreach ($entries as $i => $entry) {
            $entries[$i] = $this->_enrich_statement_entry($entry);
        }
        return array(
            'success' => true,
            'data' => array(
                'entries' => $entries,
                'from_date' => $from_date,
                'to_date' => $to_date,
                'account_id' => (int)$account_id,
            ),
        );
    }

    public function students_backup_report($user, $backup_date)
    {
        $perms = $this->permissions($user);
        if (!$perms['students_backup']) {
            return array('success' => false, 'message' => 'Access denied');
        }

        $this->ci->load->model('clas');
        $classes = $this->ci->clas->getAllClassesActiveInactive();
        $date = $backup_date ?: date('Y-m-d');

        return array(
            'success' => true,
            'data' => array(
                'classes' => $classes,
                'backup_date' => $date,
                's3_base' => 'https://shahbazcollegebucket.s3.ca-central-1.amazonaws.com/backup/' . $date . '/',
            ),
        );
    }

    public function struckoff_report($user, $strucktype, $from_date, $to_date)
    {
        $perms = $this->permissions($user);
        if (!$perms['struckoff_report']) {
            return array('success' => false, 'message' => 'Access denied');
        }

        if ($strucktype === '' || $strucktype === null) {
            return array('success' => true, 'data' => array('students' => array()));
        }

        $this->ci->db->select('students.*, classes.name as class_name, machine_data.machine_id, struckofdetails_students.updated_by, struckofdetails_students.reason, struckofdetails_students.status, struckofdetails_students.created_at as created, users.first_name as inquiry');
        $this->ci->db->from('struckofdetails_students');
        $this->ci->db->join('students', 'struckofdetails_students.student_id=students.student_id', 'inner');
        $this->ci->db->join('users', 'users.user_id = struckofdetails_students.created_by', 'inner');
        $this->ci->db->join('classes', 'classes.class_id=students.class_id', 'inner');
        $this->ci->db->join('machine_data', 'machine_data.teacher_student_id=students.student_id', 'inner');
        $this->ci->db->where("struckofdetails_students.status = '" . $this->ci->db->escape_str($strucktype) . "' and struckofdetails_students.created_at >= '" . $this->ci->db->escape_str($from_date) . "' and struckofdetails_students.created_at <= '" . $this->ci->db->escape_str($to_date) . "'");
        $this->ci->db->group_by('struckofdetails_students.student_id');
        $students = $this->ci->db->get()->result_array();

        foreach ($students as $i => $student) {
            $payment_plan = $this->ci->db->get_where('payments', array('student_id' => $student['student_id'], 'contract_id' => 0))->result_array();
            $students[$i]['fee_missing'] = count($payment_plan) < 1 && (int)$student['contractor_id'] <= 0;
            $photo = $this->ci->db->get_where('student_documents', array('student_id' => $student['student_id'], 'type' => 'Photo'))->row_array();
            $students[$i]['photo'] = $photo ? $photo['image'] : null;
        }

        return array(
            'success' => true,
            'data' => array(
                'students' => $students,
                'strucktype' => $strucktype,
                'from_date' => $from_date,
                'to_date' => $to_date,
            ),
        );
    }

    /**
     * Legacy Students::struckofstudentview parity for React reports drill-down.
     */
    private function _struckoff_student_context($user, $student_id)
    {
        $student_id = (int)$student_id;
        if ($student_id <= 0) {
            return array('success' => false, 'message' => 'Invalid student');
        }

        $this->ci->db->select('students.*, campuses.campus_name, courses.course_name, classes.name as class_name, machine_data.machine_id');
        $this->ci->db->from('students');
        $this->ci->db->join('classes', 'classes.class_id=students.class_id', 'inner');
        $this->ci->db->join('machine_data', 'machine_data.teacher_student_id=students.student_id', 'inner');
        $this->ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
        $this->ci->db->join('courses', 'courses.course_id=students.course_id', 'left');
        $this->ci->db->where('students.student_id', $student_id);

        if (!$this->_is_admin($user)) {
            $class_ids = $this->_class_ids($user);
            if (count($class_ids)) {
                $this->ci->db->where_in('classes.class_id', $class_ids);
            }
        }

        $student = $this->ci->db->get()->row_array();
        if (!$student) {
            return array('success' => false, 'message' => 'Student not found');
        }

        $contractor_label = 'N/A';
        if ((int)$student['contract_id'] > 0) {
            $contract = $this->ci->db->get_where('contracts', array('contract_id' => $student['contract_id']))->row_array();
            if ($contract) {
                $contractor_label = trim($contract['contract_name'] . ' (' . $contract['contract_date'] . ')');
            }
        }

        $photo_row = $this->ci->db->get_where('student_documents', array('student_id' => $student_id, 'type' => 'Photo'))->row_array();
        $photo = $photo_row ? $photo_row['image'] : null;

        $paid = $this->ci->db->order_by('paid_date', 'ASC')->get_where('payments', array('student_id' => $student_id, 'paid' => 1))->result_array();
        $unpaid = $this->ci->db->order_by('dead_line', 'ASC')->get_where('payments', array('student_id' => $student_id, 'paid' => 0))->result_array();
        $paid_total = 0;
        $unpaid_total = 0;
        foreach ($paid as $p) {
            $paid_total += (float)$p['actual_amount'];
        }
        foreach ($unpaid as $p) {
            $unpaid_total += (float)$p['amount'];
        }

        $payment_plan = $this->ci->db->get_where('payments', array('student_id' => $student_id, 'contract_id' => 0))->result_array();
        $fee_missing = count($payment_plan) < 1 && (int)$student['contractor_id'] <= 0;

        $council_fees = $this->ci->db->get_where('expenses', array('student_id' => $student_id))->result_array();

        return array(
            'success' => true,
            'student' => $student,
            'contractor_label' => $contractor_label,
            'photo' => $photo,
            'fee_missing' => $fee_missing,
            'paid_payments' => $paid,
            'unpaid_payments' => $unpaid,
            'paid_total' => $paid_total,
            'unpaid_total' => $unpaid_total,
            'council_fees' => $council_fees,
        );
    }

    private function _struckoff_detail_rows($student_id, $process_count)
    {
        if (!$this->ci->db->table_exists('struckofdetails_students')) {
            return array();
        }

        $details = $this->ci->db
            ->order_by('created_at', 'ASC')
            ->get_where('struckofdetails_students', array(
                'student_id' => (int)$student_id,
                'process_count' => (int)$process_count,
            ))
            ->result_array();

        foreach ($details as $i => $row) {
            $contact_by_name = '';
            if (!empty($row['created_by'])) {
                $user = $this->ci->db->get_where('users', array('user_id' => $row['created_by']))->row_array();
                if ($user) {
                    $contact_by_name = trim($user['first_name'] . ' ' . $user['last_name']);
                }
            }
            $details[$i]['contact_by_name'] = $contact_by_name;
            if (empty($row['contact_from_no']) && !empty($row['from_no'])) {
                $details[$i]['contact_from_no'] = $row['from_no'];
            }
            if (empty($row['contact_to_no']) && !empty($row['to_no'])) {
                $details[$i]['contact_to_no'] = $row['to_no'];
            }
        }

        return $details;
    }

    public function struckoff_student_view($user, $student_id)
    {
        $perms = $this->permissions($user);
        if (!$perms['struckoff_report']) {
            return array('success' => false, 'message' => 'Access denied');
        }

        return $this->_struckoff_student_view_payload($user, $student_id, false);
    }

    /** Legacy Students::struckofstudentview for can_student_struckof users. */
    public function struckof_manage_student_view($user, $student_id)
    {
        if (!$this->_user_perm($user, 'can_student_struckof')) {
            return array('success' => false, 'message' => 'Access denied');
        }

        return $this->_struckoff_student_view_payload($user, $student_id, true);
    }

    private function _struckoff_student_view_payload($user, $student_id, $manage_mode)
    {
        $ctx = $this->_struckoff_student_context($user, $student_id);
        if (empty($ctx['success'])) {
            return $ctx;
        }

        $student_id = (int)$student_id;
        $procedures = array();
        if ($this->ci->db->table_exists('struckof_procedures')) {
            $procedures = $this->ci->db
                ->order_by('process_count', 'ASC')
                ->get_where('struckof_procedures', array('student_id' => $student_id))
                ->result_array();
        }

        $last_status = count($procedures) ? $procedures[count($procedures) - 1]['status'] : null;

        return array(
            'success' => true,
            'data' => array(
                'student' => $ctx['student'],
                'contractor_label' => $ctx['contractor_label'],
                'photo' => $ctx['photo'],
                'fee_missing' => $ctx['fee_missing'],
                'paid_payments' => $ctx['paid_payments'],
                'unpaid_payments' => $ctx['unpaid_payments'],
                'paid_total' => $ctx['paid_total'],
                'unpaid_total' => $ctx['unpaid_total'],
                'council_fees' => $ctx['council_fees'],
                'procedures' => $procedures,
                'can_start_process' => $last_status !== 'pending',
                'can_manage' => $manage_mode,
                'letter_print_url' => rtrim(base_url(), '/') . '/documents/print_struck_off_notice/' . $student_id,
                'uploads_root' => rtrim(base_url(), '/') . '/uploads/',
                'recording_root' => rtrim(base_url(), '/') . '/recording/',
            ),
        );
    }

    public function struckoff_process_detail($user, $student_id, $process_count)
    {
        $perms = $this->permissions($user);
        if (!$perms['struckoff_report']) {
            return array('success' => false, 'message' => 'Access denied');
        }

        return $this->_struckoff_process_detail_payload($user, $student_id, $process_count, false);
    }

    /** Legacy Students::struckofstudentviewprocess for can_student_struckof users. */
    public function struckof_manage_process_detail($user, $student_id, $process_count)
    {
        if (!$this->_user_perm($user, 'can_student_struckof')) {
            return array('success' => false, 'message' => 'Access denied');
        }

        return $this->_struckoff_process_detail_payload($user, $student_id, $process_count, true);
    }

    private function _struckoff_process_detail_payload($user, $student_id, $process_count, $manage_mode)
    {
        $ctx = $this->_struckoff_student_context($user, $student_id);
        if (empty($ctx['success'])) {
            return $ctx;
        }

        $student_id = (int)$student_id;
        $process_count = (int)$process_count;
        $process = null;
        if ($this->ci->db->table_exists('struckof_procedures')) {
            $process = $this->ci->db->get_where('struckof_procedures', array(
                'student_id' => $student_id,
                'process_count' => $process_count,
            ))->row_array();
        }
        if (!$process) {
            return array('success' => false, 'message' => 'Process not found');
        }

        $details = $this->_struckoff_detail_rows($student_id, $process_count);
        $can_add_details = false;
        if ((int)$ctx['student']['status'] === 1) {
            if (($process['action_type'] === 'immediate' && count($details) === 0) || $process['action_type'] === 'process') {
                $can_add_details = $process['status'] !== 'reject';
            }
        }

        $can_finalize = false;
        if ($manage_mode && $this->_user_perm($user, 'student_delete') && (int)$ctx['student']['status'] === 1) {
            if ((($process['action_type'] === 'process' && count($details) > 2) || ($process['action_type'] === 'immediate' && count($details) > 0)) && $process['status'] === 'pending') {
                $can_finalize = true;
            }
        }

        return array(
            'success' => true,
            'data' => array(
                'student' => $ctx['student'],
                'contractor_label' => $ctx['contractor_label'],
                'photo' => $ctx['photo'],
                'fee_missing' => $ctx['fee_missing'],
                'paid_payments' => $ctx['paid_payments'],
                'unpaid_payments' => $ctx['unpaid_payments'],
                'paid_total' => $ctx['paid_total'],
                'unpaid_total' => $ctx['unpaid_total'],
                'council_fees' => $ctx['council_fees'],
                'process' => $process,
                'details' => $details,
                'can_add_details' => $can_add_details,
                'can_finalize' => $can_finalize,
                'can_reject' => $can_finalize,
                'can_manage' => $manage_mode,
                'letter_print_url' => rtrim(base_url(), '/') . '/documents/print_struck_off_notice/' . $student_id,
                'uploads_root' => rtrim(base_url(), '/') . '/uploads/',
                'recording_root' => rtrim(base_url(), '/') . '/recording/',
            ),
        );
    }

    /**
     * Render struck-off letter HTML for same-origin React preview/print (avoids cross-origin iframe).
     */
    public function struckoff_letter_html($user, $student_id)
    {
        $perms = $this->permissions($user);
        if (!$perms['struckoff_report'] && !$this->_user_perm($user, 'can_student_struckof')) {
            return array('success' => false, 'message' => 'Access denied');
        }

        $ctx = $this->_struckoff_student_context($user, $student_id);
        if (empty($ctx['success'])) {
            return $ctx;
        }

        $student_id = (int)$student_id;
        $this->ci->db->select('students.*, campuses.campus_name, campuses.address as campus_address, campuses.phone, campuses.phone1, campuses.phone2, campuses.phone3, campuses.phone4, campuses.phone5, campuses.phone6, campuses.phone7, campuses.logo, campuses.website, classes.session');
        $this->ci->db->from('students');
        $this->ci->db->join('classes', 'classes.class_id=students.class_id', 'inner');
        $this->ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
        $this->ci->db->where('students.student_id', $student_id);
        $student = $this->ci->db->get()->result_array();
        if (!$student) {
            return array('success' => false, 'message' => 'Student not found');
        }

        $html = $this->ci->load->view('documents/print_struck_off_notice', array('student' => $student), true);

        return array(
            'success' => true,
            'data' => array(
                'html' => $html,
            ),
        );
    }

    // ── COO cash report (petty + campus closings) ─────────────────────────────

    public function get_opening_balance($pettycashid, $from_date)
    {
        $check_record = $this->ci->db->get_where('petty_cash_college_wise', array('id' => $pettycashid))->row();
        if (!$check_record) {
            return 0;
        }

        $this->ci->db->select('sum(amount) as amount');
        $this->ci->db->from('expenses');
        $this->ci->db->where('add_by_id = "' . $check_record->assign_to . '"  and actual_date >= "' . $check_record->given_date . '"  and actual_date < "' . $from_date . '" and paid_type = "cash" and expense_id NOT IN (select expense_id from bank_reconciliation_statement where expense_id IS NOT NULL)');
        $expenseamount = $this->ci->db->get()->row();

        $this->ci->db->select('sum(cash_reversal.amount) as amount');
        $this->ci->db->from('cash_reversal');
        $this->ci->db->join('expenses', 'expenses.expense_id = cash_reversal.expense_id');
        $this->ci->db->where('expenses.add_by_id = "' . $check_record->assign_to . '"  and cash_reversal.created_at < "' . $from_date . ' 00:00:00"');
        $expensereverseamount = $this->ci->db->get()->row();

        $this->ci->db->select('id as trans_id,"receive from" as detail,"trans" as trans_type,amount_given as amount,"" as expstatus, debit_credit,created_at,"" as image,transaction_by as trans_by ');
        $this->ci->db->from('petty_cash_history');
        $this->ci->db->where('transaction_pettycash_account = "' . $check_record->id . '" and created_at < "' . $from_date . '" ');
        $trans_petty_cash = $this->ci->db->get()->result_array();

        $debit = 0;
        $credit = 0;
        foreach ($trans_petty_cash as $tran) {
            if ($tran['debit_credit'] == 'C') {
                $credit += $tran['amount'];
            } else {
                $debit += $tran['amount'];
            }
        }

        return ($check_record->opening_balance + $debit + ($expensereverseamount ? $expensereverseamount->amount : 0)) - $credit - ($expenseamount ? $expenseamount->amount : 0);
    }

    public function get_expenses($pettycashid, $date)
    {
        $check_record = $this->ci->db->get_where('petty_cash_college_wise', array('id' => $pettycashid))->row();
        if (!$check_record) {
            return 0;
        }
        $this->ci->db->select('sum(amount) as amount');
        $this->ci->db->from('expenses');
        $this->ci->db->where('add_by_id = "' . $check_record->assign_to . '" and actual_date >= "' . $date . ' 00:00:00" and actual_date <= "' . $date . ' 23:59:59" and paid_type = "cash" and expense_id NOT IN (select expense_id from bank_reconciliation_statement where expense_id IS NOT NULL)');
        $row = $this->ci->db->get()->row();
        return $row ? $row->amount : 0;
    }

    public function get_expenses_reversals($pettycashid, $date)
    {
        $check_record = $this->ci->db->get_where('petty_cash_college_wise', array('id' => $pettycashid))->row();
        if (!$check_record) {
            return 0;
        }
        $this->ci->db->select('sum(cash_reversal.amount) as amount');
        $this->ci->db->from('cash_reversal');
        $this->ci->db->join('expenses', 'expenses.expense_id = cash_reversal.expense_id');
        $this->ci->db->where('expenses.add_by_id = "' . $check_record->assign_to . '"  and cash_reversal.created_at >= "' . $date . ' 00:00:00"  and cash_reversal.created_at <= "' . $date . ' 23:59:59"');
        $row = $this->ci->db->get()->row();
        return $row ? $row->amount : 0;
    }

    public function get_received($pettycashid, $date)
    {
        $check_record = $this->ci->db->get_where('petty_cash_college_wise', array('id' => $pettycashid))->row();
        if (!$check_record) {
            return 0;
        }
        $this->ci->db->select('sum(amount_given) as amount');
        $this->ci->db->from('petty_cash_history');
        $this->ci->db->where('transaction_pettycash_account = "' . $check_record->id . '" and debit_credit = "D" and created_at >="' . $date . ' 00:00:00" and  created_at <="' . $date . '  23:59:59"');
        $row = $this->ci->db->get()->row();
        return $row ? $row->amount : 0;
    }

    public function get_sent($pettycashid, $date)
    {
        $check_record = $this->ci->db->get_where('petty_cash_college_wise', array('id' => $pettycashid))->row();
        if (!$check_record) {
            return 0;
        }
        $this->ci->db->select('sum(amount_given) as amount');
        $this->ci->db->from('petty_cash_history');
        $this->ci->db->where('transaction_pettycash_account = "' . $check_record->id . '" and debit_credit = "C" and created_at >="' . $date . ' 00:00:00" and  created_at <="' . $date . '  23:59:59"');
        $row = $this->ci->db->get()->row();
        return $row ? $row->amount : 0;
    }

    private function _campus_closing_summary($today)
    {
        $this->ci->db->select('*,closing_persons.campus_id as campus_id');
        $this->ci->db->from('closing_persons');
        $this->ci->db->join('campuses', 'campuses.campus_id = closing_persons.campus_id', 'left');
        $this->ci->db->join('users', 'users.user_id = closing_persons.user_id', 'left');
        $this->ci->db->where('closing_persons.active_status = "1"');
        $dataclose = $this->ci->db->get()->result_array();

        $sq = 'select closing_perday.campus_id,campus_name,
                (select for_day from closing_perday where campus_id = campuses.campus_id order by closing_perday.for_year desc, closing_perday.for_month desc, closing_perday.for_day desc LIMIT 1) as day,
		        (select for_month from closing_perday where campus_id = campuses.campus_id order by closing_perday.for_year desc, closing_perday.for_month desc, closing_perday.for_day desc LIMIT 1) as month,
		        MAX(for_year) as year from closing_perday 
		        left join campuses on campuses.campus_id = closing_perday.campus_id 
		        where (select count(*) from closing_persons where closing_persons.campus_id = closing_perday.campus_id and closing_persons.active_status = 1) > 0
		        GROUP by closing_perday.campus_id';
        $campusclosings = array_values($this->ci->db->query($sq)->result_array());

        $sq = 'select closing_perday.campus_id,campus_name,
		(select for_day   from closing_perday where campus_id = campuses.campus_id and checked_by = "1" order by closing_perday.for_year desc, closing_perday.for_month desc, closing_perday.for_day desc LIMIT 1) as day,
		(select for_month from closing_perday where campus_id = campuses.campus_id and checked_by = "1" order by closing_perday.for_year desc, closing_perday.for_month desc, closing_perday.for_day desc LIMIT 1) as month,
		MAX(for_year) as year from closing_perday 
		left join campuses on campuses.campus_id = closing_perday.campus_id
		where (select count(*) from closing_persons where closing_persons.campus_id = closing_perday.campus_id and closing_persons.active_status = 1) > 0
        GROUP by closing_perday.campus_id';
        $campusclosingverified = $this->ci->db->query($sq)->result_array();

        foreach ($dataclose as $key => $closing) {
            $sq = "select * from closing_perday where campus_id = '" . $closing['campus_id'] . "' and for_month = '" . date('m', strtotime($today)) . "' and for_day = '" . date('d', strtotime($today)) . "'and for_year = '" . date('Y', strtotime($today)) . "'";
            $closed = $this->ci->db->query($sq)->result_array();

            if (count($closed) > 0) {
                $this->ci->db->select('*');
                $this->ci->db->from('payments');
                $this->ci->db->where('closing_id = "' . $closed[0]['campus_closing_id'] . '"');
                $query = $this->ci->db->get()->result_array();
                $value = array_sum(array_column($query, 'actual_amount'));
                $dataclose[$key]['closing_amount'] = $value;
                $dataclose[$key]['closed_status'] = '1';
                $dataclose[$key]['closing_id'] = $closed[0]['id'];
                $dataclose[$key]['transaction_no'] = $closed[0]['transaction_no'];
                $dataclose[$key]['close_type'] = $closed[0]['close_type'];
                $dataclose[$key]['closed_by'] = $closed[0]['closed_by'];
                $dataclose[$key]['checked_by'] = $closed[0]['checked_by'];
                $dataclose[$key]['partialy_closed_image'] = $closed[0]['partialy_closed_image'];
            } else {
                $this->ci->db->select('*');
                $this->ci->db->from('payments');
                $this->ci->db->where('submitted_fee_campus_id', $closing['campus_id']);
                $this->ci->db->where('merged_challan IS NOT NULL and actual_amount > 0');
                $this->ci->db->where('fee_pay_through = "college"');
                $this->ci->db->where('actual_paid_date = "' . $today . '"');
                $this->ci->db->group_by("CASE WHEN merged_challan IS NOT NULL THEN merged_challan else '' end", false);
                $query = $this->ci->db->get()->result_array();

                $this->ci->db->select('*');
                $this->ci->db->from('payments');
                $this->ci->db->where('submitted_fee_campus_id', $closing['campus_id']);
                $this->ci->db->where('merged_challan is null');
                $this->ci->db->where('fee_pay_through = "college"');
                $this->ci->db->where('actual_paid_date = "' . $today . '"');
                $this->ci->db->where('payments.paid', 1);
                $query2 = $this->ci->db->get()->result_array();

                $yesterday = date('Y-m-d', strtotime($today . ' - 1 days'));
                $sq = "select * from closing_perday where campus_id = '" . $closing['campus_id'] . "' and for_month = '" . date('m', strtotime($yesterday)) . "' and for_day = '" . date('d', strtotime($yesterday)) . "'and for_year = '" . date('Y', strtotime($yesterday)) . "'";
                $closed_y = $this->ci->db->query($sq)->result_array();

                if (count($closed_y) > 0) {
                    $this->ci->db->select('*');
                    $this->ci->db->from('payments');
                    $this->ci->db->join('students', 'students.student_id = payments.student_id', 'left');
                    $this->ci->db->join('courses', 'courses.course_id=students.course_id', 'inner');
                    $this->ci->db->where('submitted_fee_campus_id', $closing['campus_id']);
                    $this->ci->db->where('merged_challan IS NOT NULL and actual_amount > 0');
                    $this->ci->db->where('fee_pay_through = "college"');
                    $this->ci->db->where('actual_paid_date = "' . $yesterday . '"');
                    $this->ci->db->where('closing_id IS NULL');
                    $this->ci->db->group_by("CASE WHEN merged_challan IS NOT NULL THEN merged_challan else '' end", false);
                    $query3 = $this->ci->db->get()->result_array();

                    $this->ci->db->select('*');
                    $this->ci->db->from('payments');
                    $this->ci->db->join('students', 'students.student_id = payments.student_id', 'left');
                    $this->ci->db->join('courses', 'courses.course_id=students.course_id', 'inner');
                    $this->ci->db->where('submitted_fee_campus_id', $closing['campus_id']);
                    $this->ci->db->where('merged_challan is null');
                    $this->ci->db->where('fee_pay_through = "college"');
                    $this->ci->db->where('actual_paid_date = "' . $yesterday . '"');
                    $this->ci->db->where('closing_id IS NULL');
                    $this->ci->db->where('payments.paid', 1);
                    $query4 = $this->ci->db->get()->result_array();
                    $final = array_merge($query3, $query4, $query, $query2);
                } else {
                    $final = array_merge($query, $query2);
                }

                $value = array_sum(array_column($final, 'actual_amount'));

                $this->ci->db->select('sum(asset_sales.sale_amount) as total');
                $this->ci->db->from('asset_sales');
                $this->ci->db->join('products', 'products.product_id = asset_sales.product_id', 'inner');
                $this->ci->db->where("asset_sales.sold_date >= '$today 00:00:00' and asset_sales.sold_date <= '$today 23:59:59' and products.campus_id = '" . $closing['campus_id'] . "'");
                $asset_sales_sum_today = $this->ci->db->get()->result_array();

                $this->ci->db->select('sum(asset_sales.sale_amount) as total');
                $this->ci->db->from('asset_sales');
                $this->ci->db->join('products', 'products.product_id = asset_sales.product_id', 'inner');
                $this->ci->db->where("asset_sales.sold_date >= '$yesterday 00:00:00' and asset_sales.sold_date <= '$yesterday 23:59:59' and products.campus_id = '" . $closing['campus_id'] . "' and asset_sales.closing_id IS NULL");
                $asset_sales_sum_yesterday = $this->ci->db->get()->result_array();

                $asset_sale_amount = $asset_sales_sum_today[0]['total'] + $asset_sales_sum_yesterday[0]['total'];

                $this->ci->db->select('sum(sales_payments.payment_amount) as total');
                $this->ci->db->from('sales');
                $this->ci->db->join('sales_payments', 'sales_payments.sale_id=sales.sale_id', 'left');
                $this->ci->db->join('people', 'people.person_id  = sales.customer_id', 'inner');
                $this->ci->db->where("sales.sale_time >= '$today 00:00:00' and sales.sale_time <= '$today 23:59:59' and sales.campus_id = '" . $closing['campus_id'] . "'");
                $sales_sum = $this->ci->db->get()->result_array();

                $this->ci->db->select('sum(sales_payments.payment_amount) as total');
                $this->ci->db->from('sales');
                $this->ci->db->join('sales_payments', 'sales_payments.sale_id=sales.sale_id', 'left');
                $this->ci->db->join('people', 'people.person_id  = sales.customer_id', 'inner');
                $this->ci->db->where("sales.sale_time >= '$yesterday 00:00:00' and sales.sale_time <= '$yesterday 23:59:59' and sales.campus_id = '" . $closing['campus_id'] . "'");
                $sales_sum_yesterday = $this->ci->db->get()->result_array();

                $sale_amount = $sales_sum[0]['total'] + $sales_sum_yesterday[0]['total'];

                $dataclose[$key]['closing_amount'] = $value + $sale_amount + $asset_sale_amount;
                $dataclose[$key]['closed_status'] = '0';
                $dataclose[$key]['closing_id'] = '';
                $dataclose[$key]['close_type'] = '0';
                $dataclose[$key]['transaction_no'] = '';
                $dataclose[$key]['closed_by'] = '';
                $dataclose[$key]['checked_by'] = '';
                $dataclose[$key]['partialy_closed_image'] = @$closed_y[0]['partialy_closed_image'];
            }
        }

        return array(
            'closings' => $dataclose,
            'campusclosings' => $campusclosings,
            'campusclosingverified' => $campusclosingverified,
        );
    }

    public function coo_cash_report($user, $date)
    {
        $perms = $this->permissions($user);
        if (!$perms['coo_cash_report']) {
            return array('success' => false, 'message' => 'Access denied');
        }

        $acc = $this->_access_row($user);
        $today = $date ?: date('Y-m-d');

        $this->ci->db->select('petty_cash_college_wise.*, campuses.campus_name, users.first_name, users.last_name, designations.designation_name');
        $this->ci->db->from('petty_cash_college_wise');
        $this->ci->db->join('campuses', 'campuses.campus_id = petty_cash_college_wise.campus_id', 'left');
        $this->ci->db->join('users', 'users.user_id = petty_cash_college_wise.assign_to', 'left');
        $this->ci->db->join('designations', 'designations.designation_id = users.designation_id', 'left');
        $this->ci->db->where('petty_cash_college_wise.petty_status', '1');
        if (!$this->_is_admin($user) && !empty($acc['petty_cash_users'])) {
            $ids = array_values(array_filter(array_map('intval', explode(',', $acc['petty_cash_users']))));
            if (!empty($ids)) {
                $this->ci->db->where_in('petty_cash_college_wise.id', $ids);
            }
        }
        $petty = $this->ci->db->get()->result_array();

        foreach ($petty as $index => $row) {
            $petty[$index]['opening_balance'] = $this->get_opening_balance($row['id'], $today);
            $petty[$index]['remaining_balance'] = pettycash_statement($row['id']);
            $petty[$index]['expenses'] = $this->get_expenses($row['id'], $today);
            $petty[$index]['reversal'] = $this->get_expenses_reversals($row['id'], $today);
            $petty[$index]['received'] = $this->get_received($row['id'], $today);
            $petty[$index]['sent'] = $this->get_sent($row['id'], $today);
        }

        $closing = $this->_campus_closing_summary($today);
        $last = $this->ci->db->order_by('id', 'DESC')->get('accounts_daily_closing')->row();

        return array(
            'success' => true,
            'data' => array(
                'selected_date' => $today,
                'petty_cash' => $petty,
                'closings' => $closing['closings'],
                'campusclosings' => $closing['campusclosings'],
                'campusclosingverified' => $closing['campusclosingverified'],
                'last_closing' => $last ? $last->closing_date : null,
            ),
        );
    }
}
