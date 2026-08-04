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
            'permissions' => array(
                'is_admin' => $user && $user['role'] === 'Admin',
                'fee_status' => $this->can_view_fee_status($user),
            ),
            'campuses' => $this->_campuses_for_user($user),
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

    public function fee_status_page($user, $kind, $campus_id, $page, $page_size)
    {
        $kind = ($kind === 'contractor') ? 'contractor' : 'student';
        if ($kind === 'contractor') {
            $total = $this->_count_contractor_fee_groups($campus_id, $user);
            $ids = $this->_contractor_fee_page_ids($campus_id, $user, $page, $page_size);
            $rows = $this->_load_contractor_fee_rows($ids);
        } else {
            $total = $this->_count_student_fee_groups($campus_id, $user);
            $ids = $this->_student_fee_page_ids($campus_id, $user, $page, $page_size);
            $rows = $this->_load_student_fee_rows($ids);
        }

        $total_pages = $page_size > 0 ? (int) ceil($total / $page_size) : 1;
        if ($total_pages < 1) $total_pages = 1;

        return array(
            'kind' => $kind,
            'rows' => $rows,
            'pagination' => array(
                'page' => $page,
                'page_size' => $page_size,
                'total' => $total,
                'total_pages' => $total_pages,
            ),
        );
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

    private function _student_fee_base_sql($campus_id, $user)
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
        return $sql;
    }

    private function _student_fee_group_by()
    {
        return " GROUP BY CASE WHEN payments.merged_challan IS NOT NULL THEN payments.merged_challan ELSE payments.challan_no END, payments.paid_challans ";
    }

    private function _count_student_fee_groups($campus_id, $user)
    {
        $sql = 'SELECT COUNT(*) AS c FROM (SELECT 1 '.$this->_student_fee_base_sql($campus_id, $user).$this->_student_fee_group_by().') t';
        $row = $this->ci->db->query($sql)->row_array();
        return $row ? (int) $row['c'] : 0;
    }

    private function _student_fee_page_ids($campus_id, $user, $page, $page_size)
    {
        $offset = ($page - 1) * $page_size;
        $sql = '
            SELECT MIN(payments.id) AS payment_id
            '.$this->_student_fee_base_sql($campus_id, $user).'
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
            if (isset($row['fee_pay_through']) && $row['fee_pay_through'] === 'pay_pro') {
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

    private function _contractor_fee_base_sql($campus_id, $user)
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
        return $sql;
    }

    private function _contractor_fee_group_by()
    {
        return " GROUP BY CASE WHEN payments.merged_challan IS NOT NULL THEN payments.merged_challan ELSE payments.challan_no END ";
    }

    private function _count_contractor_fee_groups($campus_id, $user)
    {
        $sql = 'SELECT COUNT(*) AS c FROM (SELECT 1 '.$this->_contractor_fee_base_sql($campus_id, $user).$this->_contractor_fee_group_by().') t';
        $row = $this->ci->db->query($sql)->row_array();
        return $row ? (int) $row['c'] : 0;
    }

    private function _contractor_fee_page_ids($campus_id, $user, $page, $page_size)
    {
        $offset = ($page - 1) * $page_size;
        $sql = '
            SELECT MIN(payments.id) AS payment_id
            '.$this->_contractor_fee_base_sql($campus_id, $user).'
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
                'can_clear' => true,
                'clear_block_reason' => '',
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
        if (isset($row['fee_pay_through']) && $row['fee_pay_through'] === 'pay_pro' && !empty($row['settlement_id'])) {
            $links[] = array('label' => 'PayPro Details', 'url' => site_url('excel_import/entries/'.$row['settlement_id']));
        }
        return $links;
    }

    private function _can_clear_payment($row)
    {
        if (isset($row['fee_pay_through']) && $row['fee_pay_through'] === 'pay_pro') {
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
}
