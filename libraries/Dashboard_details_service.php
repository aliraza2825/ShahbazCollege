<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dashboard detail pages — lists + clearance actions for React dashboard.
 */
class Dashboard_details_service {

    /** @var CI_Controller */
    private $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->model('dashboards');
        $this->ci->load->library('dashboard_service');
    }

    private function _boot($user)
    {
        $this->ci->dashboard_service->bootstrap_for_legacy($user);
    }

    private function _name($user)
    {
        return trim((isset($user['first_name']) ? $user['first_name'] : '').' '.(isset($user['last_name']) ? $user['last_name'] : ''));
    }

    private function _filter_campus($campus_id)
    {
        if ($campus_id === null || $campus_id === '' || $campus_id === 'all') {
            return null;
        }
        $id = (int) $campus_id;
        return $id > 0 ? $id : null;
    }

    private function _paginate($page, $page_size, $max = 200)
    {
        $page = (int) $page;
        $page_size = (int) $page_size;
        if ($page < 1) {
            $page = 1;
        }
        if ($page_size < 1) {
            $page_size = 50;
        }
        if ($page_size > $max) {
            $page_size = $max;
        }
        return array($page, $page_size);
    }

    private function _pagination_meta($page, $page_size, $total)
    {
        $total = (int) $total;
        $total_pages = $page_size > 0 ? (int) ceil($total / $page_size) : 1;
        if ($total_pages < 1) {
            $total_pages = 1;
        }
        return array(
            'page' => $page,
            'page_size' => $page_size,
            'total' => $total,
            'total_pages' => $total_pages,
        );
    }

    private function _campus_scope_for_user($user)
    {
        if ($user && isset($user['role']) && $user['role'] === 'Admin') {
            return null;
        }
        if (!$user || empty($user['user_id'])) {
            return array();
        }
        $acc = $this->ci->db->get_where('access', array('user_id' => (int) $user['user_id']))->row_array();
        if (!$acc || empty($acc['campus_ids'])) {
            return array();
        }
        return array_values(array_filter(array_map('intval', explode(',', $acc['campus_ids']))));
    }

    /** Shared filters for uncleared new admissions list (no checkUserAccess — avoids QB pollution). */
    private function _apply_new_admission_query($cid, $campus_scope)
    {
        $this->ci->db->from('students');
        $this->ci->db->join('classes', 'classes.class_id=students.class_id', 'inner');
        $this->ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
        if ($campus_scope !== null) {
            if (!count($campus_scope)) {
                $this->ci->db->where('1 = 0', null, false);
            } else {
                $this->ci->db->where_in('campuses.campus_id', $campus_scope);
            }
        }
        if ($cid) {
            $this->ci->db->where('campuses.campus_id', $cid);
        }
        $this->ci->db->where('students.clear_status', 0);
    }

    private function _batch_payments_by_student($student_ids)
    {
        if (!count($student_ids)) {
            return array();
        }
        $this->ci->db->reset_query();
        $this->ci->db->where_in('student_id', $student_ids);
        $this->ci->db->order_by('student_id', 'ASC');
        $this->ci->db->order_by('paid', 'DESC');
        $this->ci->db->order_by('id', 'ASC');
        $rows = $this->ci->db->get('payments')->result_array();
        $map = array();
        foreach ($rows as $p) {
            $sid = (int) $p['student_id'];
            if (!isset($map[$sid])) {
                $map[$sid] = array('paid' => array(), 'unpaid' => array());
            }
            if (!empty($p['paid'])) {
                $map[$sid]['paid'][] = $p;
            } else {
                $map[$sid]['unpaid'][] = $p;
            }
        }
        return $map;
    }

    private function _batch_documents_by_student($student_ids)
    {
        if (!count($student_ids)) {
            return array();
        }
        $this->ci->db->reset_query();
        $this->ci->db->select('student_id, type');
        $this->ci->db->from('student_documents');
        $this->ci->db->where_in('student_id', $student_ids);
        $this->ci->db->where('type !=', '');
        $rows = $this->ci->db->get()->result_array();
        $map = array();
        foreach ($rows as $d) {
            $sid = (int) $d['student_id'];
            if (!isset($map[$sid])) {
                $map[$sid] = array();
            }
            if (!empty($d['type'])) {
                $map[$sid][] = $d['type'];
            }
        }
        return $map;
    }

    private function _batch_contractors($contractor_ids)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $contractor_ids))));
        if (!count($ids)) {
            return array();
        }
        $this->ci->db->reset_query();
        $this->ci->db->where_in('contractor_id', $ids);
        $rows = $this->ci->db->get('contractors')->result_array();
        $map = array();
        foreach ($rows as $c) {
            $map[(int) $c['contractor_id']] = $c;
        }
        return $map;
    }

    private function _build_fee_summary($paid, $unpaid, $max_lines = 6)
    {
        $total_fee = 0;
        $fee_lines = array();
        foreach ($paid as $p) {
            $total_fee += (float) $p['amount'];
            if (count($fee_lines) < $max_lines) {
                $fee_lines[] = $p['amount'].' paid on '.$p['paid_date'];
            }
        }
        foreach ($unpaid as $p) {
            $total_fee += (float) $p['amount'];
            if (count($fee_lines) < $max_lines) {
                $fee_lines[] = $p['amount'].' due on '.$p['dead_line'];
            }
        }
        $extra = (count($paid) + count($unpaid)) - count($fee_lines);
        if ($extra > 0) {
            $fee_lines[] = '+'.$extra.' more payment(s)';
        }
        return array($fee_lines, $total_fee);
    }

    public function new_admission_entries($user, $campus_id = null, $page = 1, $page_size = 50)
    {
        $this->_boot($user);
        $cid = $this->_filter_campus($campus_id);
        list($page, $page_size) = $this->_paginate($page, $page_size);
        $campus_scope = $this->_campus_scope_for_user($user);

        $this->ci->db->reset_query();
        $this->_apply_new_admission_query($cid, $campus_scope);
        $total = (int) $this->ci->db->count_all_results();

        $this->ci->db->reset_query();
        $this->ci->db->select('campuses.campus_name, classes.name as class_name, students.*');
        $this->_apply_new_admission_query($cid, $campus_scope);
        $this->ci->db->order_by('students.registration_date', 'DESC');
        $this->ci->db->order_by('students.student_id', 'DESC');
        $this->ci->db->limit($page_size, ($page - 1) * $page_size);
        $rows = $this->ci->db->get()->result_array();

        $student_ids = array();
        $contractor_ids = array();
        foreach ($rows as $r) {
            $student_ids[] = (int) $r['student_id'];
            if (!empty($r['contractor_id'])) {
                $contractor_ids[] = (int) $r['contractor_id'];
            }
        }

        $payments_map = $this->_batch_payments_by_student($student_ids);
        $docs_map = $this->_batch_documents_by_student($student_ids);
        $contractors = $this->_batch_contractors($contractor_ids);

        $out = array();
        foreach ($rows as $r) {
            $sid = (int) $r['student_id'];
            $paid = isset($payments_map[$sid]['paid']) ? $payments_map[$sid]['paid'] : array();
            $unpaid = isset($payments_map[$sid]['unpaid']) ? $payments_map[$sid]['unpaid'] : array();
            list($fee_lines, $total_fee) = $this->_build_fee_summary($paid, $unpaid);
            $docs = isset($docs_map[$sid]) ? $docs_map[$sid] : array();

            $contractor = 'N/A';
            if (!empty($r['contractor_id'])) {
                $cid_contractor = (int) $r['contractor_id'];
                if (isset($contractors[$cid_contractor])) {
                    $c = $contractors[$cid_contractor];
                    $contractor = $c['name'].(isset($c['date']) ? ' ('.$c['date'].')' : '');
                }
            }

            $out[] = array(
                'student_id' => $sid,
                'campus_name' => isset($r['campus_name']) ? $r['campus_name'] : '',
                'class_name' => isset($r['class_name']) ? $r['class_name'] : '',
                'roll_no' => isset($r['roll_no']) ? $r['roll_no'] : '',
                'name' => trim($r['first_name'].' '.$r['last_name']),
                'cnic' => isset($r['cnic']) ? $r['cnic'] : '',
                'mobile' => isset($r['mobile']) ? $r['mobile'] : '',
                'emergency_no' => isset($r['emergency_no']) ? $r['emergency_no'] : '',
                'documents' => $docs,
                'fee_lines' => $fee_lines,
                'total_fee' => $total_fee,
                'registration_date' => isset($r['registration_date']) ? $r['registration_date'] : '',
                'add_by' => isset($r['add_by']) ? $r['add_by'] : '',
                'contractor' => $contractor,
                'section' => isset($r['section']) ? $r['section'] : '',
                'shift' => isset($r['shift']) ? $r['shift'] : '',
                'study_type' => isset($r['study_type']) ? $r['study_type'] : '',
                'student_card' => !empty($r['student_card']),
            );
        }

        return array(
            'rows' => $out,
            'pagination' => $this->_pagination_meta($page, $page_size, $total),
        );
    }

    public function clear_new_admission($user, $student_id)
    {
        $this->_boot($user);
        $this->ci->db->set('clear_status', 1);
        $this->ci->db->set('clear_by', $this->_name($user).' '.date('Y-m-d h:i:s A'));
        $this->ci->db->where('student_id', (int) $student_id);
        $this->ci->db->update('students');
        return array('success' => true, 'message' => 'New admission cleared');
    }

    private function _apply_new_expense_query($cid, $campus_scope)
    {
        $this->ci->db->from('expenses');
        $this->ci->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
        $this->ci->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
        if ($campus_scope !== null) {
            if (!count($campus_scope)) {
                $this->ci->db->where('1 = 0', null, false);
            } else {
                $this->ci->db->where_in('campuses.campus_id', $campus_scope);
            }
        }
        if ($cid) {
            $this->ci->db->where('campuses.campus_id', $cid);
        }
        $this->ci->db->where('expenses.clear_status = 0 and expenses.approved_status = 1');
    }

    public function new_expense_entries($user, $campus_id = null, $page = 1, $page_size = 50)
    {
        $this->_boot($user);
        $cid = $this->_filter_campus($campus_id);
        list($page, $page_size) = $this->_paginate($page, $page_size);
        $campus_scope = $this->_campus_scope_for_user($user);

        $this->ci->db->reset_query();
        $this->_apply_new_expense_query($cid, $campus_scope);
        $total = (int) $this->ci->db->count_all_results();

        $this->ci->db->reset_query();
        $this->ci->db->select('expenses.*, expense_category.name, campuses.campus_name');
        $this->_apply_new_expense_query($cid, $campus_scope);
        $this->ci->db->order_by('expenses.date', 'DESC');
        $this->ci->db->order_by('expenses.expense_id', 'DESC');
        $this->ci->db->limit($page_size, ($page - 1) * $page_size);
        $rows = $this->ci->db->get()->result_array();

        $out = array();
        foreach ($rows as $r) {
            $cat = isset($r['name']) ? $r['name'] : '';
            if (!empty($r['expense_category_id']) && (int) $r['expense_category_id'] === 9 && !empty($r['title'])) {
                $cat = $r['title'];
            }
            $out[] = array(
                'expense_id' => (int) $r['expense_id'],
                'campus_name' => isset($r['campus_name']) ? $r['campus_name'] : '',
                'category' => $cat,
                'title' => isset($r['title']) ? $r['title'] : '',
                'purpose' => isset($r['purpose']) ? $r['purpose'] : '',
                'amount' => isset($r['amount']) ? $r['amount'] : 0,
                'date' => isset($r['date']) ? $r['date'] : '',
                'actual_date' => isset($r['actual_date']) ? $r['actual_date'] : '',
                'receipt' => isset($r['receipt']) ? $r['receipt'] : '',
                'add_by' => isset($r['add_by']) ? $r['add_by'] : '',
                'last_edit' => isset($r['last_edit']) ? $r['last_edit'] : '',
            );
        }
        return array(
            'rows' => $out,
            'pagination' => $this->_pagination_meta($page, $page_size, $total),
        );
    }

    public function clear_new_expense($user, $expense_id)
    {
        $this->_boot($user);
        $this->ci->db->set('clear_status', 1);
        $this->ci->db->set('clear_by', $this->_name($user).' '.date('Y-m-d h:i:s A'));
        $this->ci->db->where('expense_id', (int) $expense_id);
        $this->ci->db->update('expenses');
        return array('success' => true, 'message' => 'Expense cleared');
    }

    public function update_fee_requests($user, $campus_id = null, $kind = 'student')
    {
        $this->_boot($user);
        $cid = $this->_filter_campus($campus_id);
        if ($kind === 'contractor') {
            $rows = $this->ci->dashboards->getContractsFeeRequest($cid);
        } else {
            $this->ci->db->select('update_payment_requests.*, students.first_name, students.last_name, students.roll_no, campuses.campus_name');
            $this->ci->db->from('update_payment_requests');
            $this->ci->db->join('students', 'students.student_id=update_payment_requests.student_id', 'left');
            $this->ci->db->join('classes', 'classes.class_id=students.class_id', 'left');
            $this->ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
            if ($cid) {
                $this->ci->db->where('campuses.campus_id', $cid);
            }
            if ($user['role'] !== 'Admin') {
                $acc = $this->ci->db->get_where('access', array('user_id' => $user['user_id']))->row_array();
                $campuses = ($acc && !empty($acc['campus_ids'])) ? explode(',', $acc['campus_ids']) : array();
                if (count($campuses)) {
                    $this->ci->db->where_in('campuses.campus_id', $campuses);
                }
            }
            $this->ci->db->where('update_payment_requests.ok_by_admin', '0');
            $rows = $this->ci->db->get()->result_array();
        }
        $out = array();
        foreach ($rows as $r) {
            $original = function_exists('getOriginalPayemntDetails') ? getOriginalPayemntDetails($r['id']) : array();
            $orig = count($original) ? $original[0] : array();
            $party = '';
            if (!empty($r['first_name'])) {
                $party = trim($r['first_name'].' '.$r['last_name']).(isset($r['roll_no']) ? ' ('.$r['roll_no'].')' : '');
            } elseif (!empty($r['name'])) {
                $party = $r['name'].(isset($r['contract_name']) ? ' — '.$r['contract_name'] : '');
            }
            $out[] = array(
                'request_id' => (int) $r['id'],
                'payment_id' => (int) $r['id'],
                'update_date' => isset($r['update_date']) ? $r['update_date'] : '',
                'challan_no' => isset($r['challan_no']) ? $r['challan_no'] : '',
                'amount' => isset($r['amount']) ? $r['amount'] : '',
                'actual_amount' => isset($r['actual_amount']) ? $r['actual_amount'] : '',
                'paid' => !empty($r['paid']),
                'dead_line' => isset($r['dead_line']) ? $r['dead_line'] : '',
                'paid_date' => isset($r['paid_date']) ? $r['paid_date'] : '',
                'party_label' => $party,
                'campus_name' => isset($r['campus_name']) ? $r['campus_name'] : '',
                'reason' => isset($r['reason']) ? $r['reason'] : '',
                'system_comment' => isset($r['system_comment']) ? $r['system_comment'] : '',
                'scan_challan' => isset($r['scan_challan']) ? $r['scan_challan'] : '',
                'original' => array(
                    'amount' => isset($orig['amount']) ? $orig['amount'] : '',
                    'actual_amount' => isset($orig['actual_amount']) ? $orig['actual_amount'] : '',
                    'paid' => !empty($orig['paid']),
                    'dead_line' => isset($orig['dead_line']) ? $orig['dead_line'] : '',
                    'paid_date' => isset($orig['paid_date']) ? $orig['paid_date'] : '',
                ),
            );
        }
        return array('kind' => $kind, 'rows' => $out);
    }

    public function approve_fee_update($user, $request_id)
    {
        $this->_boot($user);
        $update_request = $this->ci->db->get_where('update_payment_requests', array('id' => (int) $request_id, 'ok_by_admin' => 0))->result_array();
        if (!count($update_request)) {
            return array('success' => false, 'message' => 'Request not found');
        }
        $this->ci->dashboards->updatePayment($update_request);
        $this->ci->dashboards->updateClearPayment($update_request[0]['id']);
        return array('success' => true, 'message' => 'Fee update approved');
    }

    public function reject_fee_update($user, $request_id)
    {
        $this->_boot($user);
        $this->ci->db->where(array('id' => (int) $request_id, 'ok_by_admin' => 0));
        $this->ci->db->delete('update_payment_requests');
        return array('success' => true, 'message' => 'Fee update rejected');
    }

    public function discount_requests($user, $campus_id = null)
    {
        $this->_boot($user);
        $cid = $this->_filter_campus($campus_id);
        $this->ci->db->select('discounts_approval.*, students.first_name, students.last_name, students.roll_no, classes.name as class_name, campuses.campus_name, courses.course_name');
        $this->ci->db->from('discounts_approval');
        $this->ci->db->join('students', 'students.student_id = discounts_approval.student_id', 'left');
        $this->ci->db->join('classes', 'classes.class_id=students.class_id', 'left');
        $this->ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
        $this->ci->db->join('courses', 'courses.course_id=classes.course_id', 'left');
        if ($cid) {
            $this->ci->db->where('campuses.campus_id', $cid);
        }
        $this->ci->db->where('discounts_approval.status', 0);
        $rows = $this->ci->db->get()->result_array();
        $out = array();
        foreach ($rows as $r) {
            $out[] = array(
                'id' => (int) $r['id'],
                'student_id' => (int) $r['student_id'],
                'student_name' => trim($r['first_name'].' '.$r['last_name']),
                'roll_no' => isset($r['roll_no']) ? $r['roll_no'] : '',
                'campus_name' => isset($r['campus_name']) ? $r['campus_name'] : '',
                'class_name' => isset($r['class_name']) ? $r['class_name'] : '',
                'course_name' => isset($r['course_name']) ? $r['course_name'] : '',
                'discount' => isset($r['discount']) ? $r['discount'] : 0,
                'reason' => isset($r['reason']) ? $r['reason'] : '',
                'created_by' => isset($r['created_by']) ? $r['created_by'] : '',
                'created_at' => isset($r['created_at']) ? $r['created_at'] : '',
            );
        }
        return array('rows' => $out);
    }

    public function approve_discount($user, $discount_id)
    {
        $this->_boot($user);
        $update_request = $this->ci->db->get_where('discounts_approval', array('id' => (int) $discount_id))->result_array();
        if (!count($update_request)) {
            return array('success' => false, 'message' => 'Not found');
        }
        $discamount = $update_request[0]['discount'];
        $this->ci->db->set('status', 1);
        $this->ci->db->set('approved_by', $this->_name($user));
        $this->ci->db->set('approved_at', date('Y-m-d H:i:s'));
        $this->ci->db->where(array('id' => (int) $discount_id));
        $this->ci->db->update('discounts_approval');
        $this->ci->db->set('total_fee', 'total_fee -'.$discamount, false);
        $this->ci->db->where(array('student_id' => $update_request[0]['student_id']));
        $this->ci->db->update('students');
        $payments = $this->ci->db->get_where('payments', array('student_id' => $update_request[0]['student_id'], 'paid' => 0))->result_array();
        foreach ($payments as $p) {
            if ($discamount <= 0) break;
            $paymentamount = $p['amount'];
            if (($discamount - $paymentamount) >= 0) {
                $this->ci->db->where('id', $p['id']);
                $this->ci->db->delete('payments');
                $discamount -= $paymentamount;
            } else {
                $this->ci->db->set('amount', $paymentamount - $discamount);
                $this->ci->db->where('id', $p['id']);
                $this->ci->db->update('payments');
                $discamount = 0;
            }
        }
        return array('success' => true, 'message' => 'Discount approved');
    }

    public function reject_discount($user, $discount_id)
    {
        $this->_boot($user);
        $this->ci->db->set('status', 2);
        $this->ci->db->where('id', (int) $discount_id);
        $this->ci->db->update('discounts_approval');
        return array('success' => true, 'message' => 'Discount rejected');
    }

    public function student_edit_requests($user, $campus_id = null)
    {
        $this->_boot($user);
        $cid = $this->_filter_campus($campus_id);
        $rows = $this->ci->dashboards->getStudentsEditRequest($cid);
        $out = array();
        foreach ($rows as $req) {
            $student = $this->ci->db->get_where('students', array('student_id' => $req['student_id']))->row_array();
            $changes = array();
            $fields = array('first_name', 'last_name', 'father_name', 'roll_no', 'cnic', 'mobile', 'email', 'total_fee', 'status');
            foreach ($fields as $f) {
                $new = isset($req[$f]) ? (string) $req[$f] : '';
                $old = ($student && isset($student[$f])) ? (string) $student[$f] : '';
                if ($new !== $old) {
                    $changes[] = array('field' => $f, 'requested' => $new, 'current' => $old);
                }
            }
            $out[] = array(
                'student_id' => (int) $req['student_id'],
                'update_date' => isset($req['update_date']) ? $req['update_date'] : '',
                'name' => trim((isset($req['first_name']) ? $req['first_name'] : '').' '.(isset($req['last_name']) ? $req['last_name'] : '')),
                'roll_no' => isset($req['roll_no']) ? $req['roll_no'] : '',
                'class_name' => isset($req['class_name']) ? $req['class_name'] : '',
                'cnic' => isset($req['cnic']) ? $req['cnic'] : '',
                'mobile' => isset($req['mobile']) ? $req['mobile'] : '',
                'add_by' => isset($req['add_by']) ? $req['add_by'] : '',
                'last_edit' => isset($req['last_edit']) ? $req['last_edit'] : '',
                'status' => isset($req['status']) ? (int) $req['status'] : 1,
                'changes' => $changes,
            );
        }
        return array('rows' => $out);
    }

    public function approve_student_update($user, $student_id)
    {
        $this->_boot($user);
        $update_request = $this->ci->db->get_where('update_student_requests', array('student_id' => (int) $student_id, 'ok_by_admin' => 0))->result_array();
        if (!count($update_request)) {
            return array('success' => false, 'message' => 'Request not found');
        }
        $result = $this->ci->dashboards->updateStudent($update_request);
        if ($result === 'failed') {
            return array('success' => false, 'message' => 'Student cannot be updated — duplicate CNIC on same course');
        }
        $this->ci->dashboards->updateStudentRequest($update_request[0]['student_id']);
        if ($update_request[0]['status'] == 0) {
            $check_refund = $this->ci->db->get_where('deleted_students', array('student_id' => $update_request[0]['student_id'], 'status' => 0))->result_array();
            if (count($check_refund) && $check_refund[0]['refund_amount'] > 0) {
                $this->ci->db->select('campuses.campus_id');
                $this->ci->db->from('students');
                $this->ci->db->join('classes', 'classes.class_id=students.class_id', 'inner');
                $this->ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
                $this->ci->db->where('students.student_id', $update_request[0]['student_id']);
                $student_detail = $this->ci->db->get()->result_array();
                $this->ci->db->set('date', date('Y-m-d'));
                $this->ci->db->set('actual_date', date('Y-m-d'));
                $this->ci->db->set('purpose', 'Refund issue and approved by '.$this->_name($user));
                $this->ci->db->set('title', 'Student Refund');
                $this->ci->db->set('amount', $check_refund[0]['refund_amount']);
                $this->ci->db->set('add_by', $this->_name($user));
                $this->ci->db->set('last_edit', $this->_name($user));
                $this->ci->db->set('campus_id', $student_detail[0]['campus_id']);
                $this->ci->db->set('expense_category_id', 7);
                $this->ci->db->insert('expenses');
                $this->ci->db->set('status', 1);
                $this->ci->db->set('approve_by', $this->_name($user));
                $this->ci->db->where('id', $check_refund[0]['id']);
                $this->ci->db->update('deleted_students');
            }
        }
        return array('success' => true, 'message' => 'Student update approved');
    }

    public function reject_student_update($user, $student_id)
    {
        $this->_boot($user);
        $sid = (int) $student_id;
        $this->ci->db->where('student_id', $sid);
        $this->ci->db->where('ok_by_admin', 0);
        $this->ci->db->delete('update_student_requests');
        $check_refund = $this->ci->db->get_where('deleted_students', array('student_id' => $sid, 'status' => 0))->result_array();
        if (count($check_refund)) {
            $this->ci->db->where('student_id', $sid);
            $this->ci->db->delete('deleted_students');
        }
        return array('success' => true, 'message' => 'Student update rejected');
    }

    public function pending_questions($user)
    {
        $this->_boot($user);
        $sections = array(
            array('key' => 'mcq', 'label' => 'MCQs', 'where' => array('questions.option_1!=' => '', 'questions.status' => 0)),
            array('key' => 'short', 'label' => 'Short questions', 'where' => array('questions.type' => 'short-question', 'questions.status' => 0)),
            array('key' => 'long', 'label' => 'Long questions', 'where' => array('questions.type' => 'long-question', 'questions.status' => 0)),
            array('key' => 'word', 'label' => 'Word meanings', 'where' => array('questions.type' => 'word-meaning', 'questions.status' => 0)),
        );
        $groups = array();
        foreach ($sections as $sec) {
            $this->ci->db->select('questions.*, topics.topic_name, course_subjects.subject_name, courses.course_name, questions.add_by as add_by, questions.last_edit as last_edit');
            $this->ci->db->from('questions');
            $this->ci->db->join('topics', 'topics.topic_id=questions.topic_id', 'INNER');
            $this->ci->db->join('course_subjects', 'topics.course_subject_id=course_subjects.course_subject_id', 'INNER');
            $this->ci->db->join('courses', 'courses.course_id=course_subjects.course_id', 'INNER');
            $this->ci->db->where($sec['where']);
            $rows = $this->ci->db->get()->result_array();
            $items = array();
            foreach ($rows as $q) {
                $items[] = array(
                    'question_id' => (int) $q['question_id'],
                    'course_name' => isset($q['course_name']) ? $q['course_name'] : '',
                    'subject_name' => isset($q['subject_name']) ? $q['subject_name'] : '',
                    'topic_name' => isset($q['topic_name']) ? $q['topic_name'] : '',
                    'type' => isset($q['type']) ? $q['type'] : 'mcq',
                    'question' => isset($q['question']) ? $q['question'] : '',
                    'option_1' => isset($q['option_1']) ? $q['option_1'] : '',
                    'option_2' => isset($q['option_2']) ? $q['option_2'] : '',
                    'option_3' => isset($q['option_3']) ? $q['option_3'] : '',
                    'option_4' => isset($q['option_4']) ? $q['option_4'] : '',
                    'add_by' => isset($q['add_by']) ? $q['add_by'] : '',
                    'last_edit' => isset($q['last_edit']) ? $q['last_edit'] : '',
                );
            }
            $groups[] = array('key' => $sec['key'], 'label' => $sec['label'], 'rows' => $items);
        }
        return array('groups' => $groups);
    }

    public function approve_question($user, $question_id)
    {
        $this->_boot($user);
        $this->ci->db->set('clear_by', $this->_name($user));
        $this->ci->db->set('status', 1);
        $this->ci->db->where('question_id', (int) $question_id);
        $this->ci->db->update('questions');
        return array('success' => true, 'message' => 'Question approved');
    }

    public function delete_question($user, $question_id)
    {
        $this->_boot($user);
        $this->ci->db->where('question_id', (int) $question_id);
        $this->ci->db->delete('questions');
        return array('success' => true, 'message' => 'Question rejected');
    }

    public function uncheck_assignments($user)
    {
        $this->_boot($user);
        $access = checkUserAccess();
        $subject_ids = @explode(',', $access[0]['assignment_subject_ids']);
        $campus_ids = @explode(',', $access[0]['campus_ids']);
        $this->ci->db->select('assignments.*, chapters.chapter_name, courses.course_name, course_subjects.subject_name, campuses.campus_name, classes.name as class_name, students.first_name, students.last_name, students.roll_no, students.mobile, assignment_results.student_id, assignment_results.submitted_date, assignment_results.file');
        $this->ci->db->from('assignments');
        $this->ci->db->join('chapters', 'chapters.chapter_id=assignments.chapter_id', 'inner');
        $this->ci->db->join('courses', 'courses.course_id=assignments.course_id', 'inner');
        $this->ci->db->join('course_subjects', 'course_subjects.course_subject_id=assignments.subject_id', 'inner');
        $this->ci->db->join('assignment_results', 'assignment_results.assignment_id=assignments.assignment_id', 'inner');
        $this->ci->db->join('students', 'students.student_id=assignment_results.student_id', 'inner');
        $this->ci->db->join('classes', 'students.class_id=classes.class_id', 'inner');
        $this->ci->db->join('campuses', 'campuses.campus_id=classes.campus_id', 'inner');
        if ($user['role'] !== 'Admin') {
            if (count($subject_ids)) {
                $this->ci->db->where_in('assignments.subject_id', $subject_ids);
            }
            if (count($campus_ids)) {
                $this->ci->db->where_in('campuses.campus_id', $campus_ids);
            }
        }
        $this->ci->db->where('assignment_results.checked', 0);
        $rows = $this->ci->db->get()->result_array();
        $out = array();
        foreach ($rows as $r) {
            $out[] = array(
                'assignment_id' => (int) $r['assignment_id'],
                'student_id' => (int) $r['student_id'],
                'course_name' => isset($r['course_name']) ? $r['course_name'] : '',
                'campus_name' => isset($r['campus_name']) ? $r['campus_name'] : '',
                'class_name' => isset($r['class_name']) ? $r['class_name'] : '',
                'subject_name' => isset($r['subject_name']) ? $r['subject_name'] : '',
                'chapter_name' => isset($r['chapter_name']) ? $r['chapter_name'] : '',
                'student_name' => trim($r['first_name'].' '.$r['last_name']),
                'roll_no' => isset($r['roll_no']) ? $r['roll_no'] : '',
                'mobile' => isset($r['mobile']) ? $r['mobile'] : '',
                'dead_line' => isset($r['dead_line']) ? $r['dead_line'] : '',
                'submitted_date' => isset($r['submitted_date']) ? $r['submitted_date'] : '',
                'file' => isset($r['file']) ? $r['file'] : '',
            );
        }
        return array('rows' => $out);
    }

    public function fee_reversal_requests($user, $from_date = null, $to_date = null)
    {
        $this->_boot($user);
        if (!$from_date) {
            $from_date = date('Y-m-01');
        }
        if (!$to_date) {
            $to_date = date('Y-m-t');
        }
        $pending = $this->ci->db->get_where('payments_reversal_requests', array('done' => 0))->result_array();
        $this->ci->db->select('*');
        $this->ci->db->from('payments_reversal_requests');
        $this->ci->db->where(array('status!=' => 0, 'done' => 1, 'created_at>=' => $from_date.' 00:00:00', 'created_at<=' => $to_date.' 23:59:59'));
        $approved = $this->ci->db->get()->result_array();
        $map = function ($rows) {
            $out = array();
            foreach ($rows as $r) {
                $out[] = array(
                    'id' => (int) $r['payments_reversal_request_id'],
                    'payment_id' => (int) $r['payment_id'],
                    'created_at' => isset($r['created_at']) ? $r['created_at'] : '',
                    'reversal_amount' => isset($r['reversal_amount']) ? $r['reversal_amount'] : 0,
                    'reversal_reason' => isset($r['reversal_reason']) ? $r['reversal_reason'] : '',
                    'reversal_application' => isset($r['reversal_application']) ? $r['reversal_application'] : '',
                    'online_reversal_application' => isset($r['online_reversal_application']) ? $r['online_reversal_application'] : '',
                    'approve_status' => isset($r['approve_status']) ? (int) $r['approve_status'] : 0,
                    'status' => isset($r['status']) ? (int) $r['status'] : 0,
                );
            }
            return $out;
        };
        return array(
            'from_date' => $from_date,
            'to_date' => $to_date,
            'pending' => $map($pending),
            'approved' => $map($approved),
        );
    }

    public function approve_fee_reversal($user, $id, $approve_status = 1)
    {
        $this->_boot($user);
        $this->ci->db->set('approve_status', (int) $approve_status);
        $this->ci->db->set('status', 1);
        $this->ci->db->where('payments_reversal_request_id', (int) $id);
        $this->ci->db->update('payments_reversal_requests');
        return array('success' => true, 'message' => 'Fee reversal updated');
    }

    public function delete_fee_reversal($user, $id)
    {
        $this->_boot($user);
        $this->ci->db->where(array('payments_reversal_request_id' => (int) $id, 'approve_status' => 0));
        $this->ci->db->delete('payments_reversal_requests');
        return array('success' => true, 'message' => 'Fee reversal deleted');
    }

    public function struck_off_list($user, $mode = 'inquiry')
    {
        $this->_boot($user);
        $this->ci->db->select('students.student_id, students.first_name, students.last_name, students.roll_no, students.mobile, campuses.campus_name, courses.course_name, classes.name as class_name, ast.approval_by, ast.reason, ast.action_type, ast.status, ast.created_at, ast.created_by as createdby');
        $this->ci->db->from('struckof_procedures ast');
        $this->ci->db->join('students', 'ast.student_id=students.student_id', 'inner');
        $this->ci->db->join('classes', 'classes.class_id=students.class_id', 'inner');
        $this->ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
        $this->ci->db->join('courses', 'courses.course_id=students.course_id', 'left');
        $this->ci->db->where('students.status', 1);
        $this->ci->db->where("ast.status = 'pending'");
        if ($mode === 'final') {
            // Legacy final queue — students with 3-step process pending approval
            $this->ci->db->where('ast.need_approval', 1);
        }
        $this->ci->db->group_by('students.student_id');
        $rows = $this->ci->db->get()->result_array();
        $out = array();
        foreach ($rows as $r) {
            $out[] = array(
                'student_id' => (int) $r['student_id'],
                'name' => trim($r['first_name'].' '.$r['last_name']),
                'roll_no' => isset($r['roll_no']) ? $r['roll_no'] : '',
                'mobile' => isset($r['mobile']) ? $r['mobile'] : '',
                'campus_name' => isset($r['campus_name']) ? $r['campus_name'] : '',
                'course_name' => isset($r['course_name']) ? $r['course_name'] : '',
                'class_name' => isset($r['class_name']) ? $r['class_name'] : '',
                'reason' => isset($r['reason']) ? $r['reason'] : '',
                'created_at' => isset($r['created_at']) ? $r['created_at'] : '',
                'created_by' => isset($r['createdby']) ? $r['createdby'] : '',
            );
        }
        return array('mode' => $mode, 'rows' => $out);
    }

    public function new_admissions_report($user, $month = null, $year = null)
    {
        $this->_boot($user);
        if (!$month) {
            $month = date('m');
        }
        if (!$year) {
            $year = date('Y');
        }
        $rows = $this->ci->dashboards->getNewStudents((int) $month, (int) $year);
        $out = array();
        foreach ($rows as $r) {
            $out[] = array(
                'student_id' => (int) $r['student_id'],
                'name' => trim($r['first_name'].' '.$r['last_name']),
                'roll_no' => isset($r['roll_no']) ? $r['roll_no'] : '',
                'cnic' => isset($r['cnic']) ? $r['cnic'] : '',
                'mobile' => isset($r['mobile']) ? $r['mobile'] : '',
                'campus_name' => isset($r['campus_name']) ? $r['campus_name'] : '',
                'class_name' => isset($r['class_name']) ? $r['class_name'] : '',
                'course_name' => isset($r['course_name']) ? $r['course_name'] : '',
                'session' => isset($r['session']) ? $r['session'] : '',
                'registration_date' => isset($r['registration_date']) ? $r['registration_date'] : '',
                'add_by' => isset($r['add_by']) ? $r['add_by'] : '',
            );
        }
        return array('month' => (int) $month, 'year' => (int) $year, 'count' => count($out), 'rows' => $out);
    }

    public function submit_fees_report($user, $start_date = null, $end_date = null, $date_type = 'actual_paid_date')
    {
        $this->_boot($user);
        if (!$start_date) {
            $start_date = date('Y-m-01');
        }
        if (!$end_date) {
            $end_date = date('Y-m-t');
        }
        $rows = $this->ci->dashboards->newSubmitFees($start_date, $end_date, $date_type);
        $total = $this->ci->dashboards->getTotalSubmittedFee($start_date, $end_date, $date_type);
        $out = array();
        foreach ($rows as $r) {
            $student = null;
            if (!empty($r['student_id'])) {
                $student = $this->ci->db->get_where('students', array('student_id' => $r['student_id']))->row_array();
            }
            $out[] = array(
                'payment_id' => (int) $r['id'],
                'student_id' => isset($r['student_id']) ? (int) $r['student_id'] : null,
                'student_name' => $student ? trim($student['first_name'].' '.$student['last_name']) : '',
                'roll_no' => $student ? $student['roll_no'] : '',
                'amount' => isset($r['amount']) ? $r['amount'] : 0,
                'actual_amount' => isset($r['actual_amount']) ? $r['actual_amount'] : 0,
                'paid_date' => isset($r['paid_date']) ? $r['paid_date'] : '',
                'actual_paid_date' => isset($r['actual_paid_date']) ? $r['actual_paid_date'] : '',
                'challan_no' => isset($r['challan_no']) ? $r['challan_no'] : '',
            );
        }
        return array(
            'start_date' => $start_date,
            'end_date' => $end_date,
            'date_type' => $date_type,
            'total_submitted_fee' => isset($total[0]['total_submitted_fee']) ? (float) $total[0]['total_submitted_fee'] : 0,
            'rows' => $out,
        );
    }

    public function expenses_report($user, $start_date = null, $end_date = null, $date_type = 'actual_date')
    {
        $this->_boot($user);
        if (!$start_date) {
            $start_date = date('Y-m-01');
        }
        if (!$end_date) {
            $end_date = date('Y-m-t');
        }
        $rows = $this->ci->dashboards->newExpenses($start_date, $end_date, $date_type);
        $total = $this->ci->dashboards->getTotalExpenses($start_date, $end_date);
        $out = array();
        foreach ($rows as $r) {
            $out[] = array(
                'expense_id' => (int) $r['expense_id'],
                'campus_name' => isset($r['campus_name']) ? $r['campus_name'] : '',
                'title' => isset($r['title']) ? $r['title'] : '',
                'purpose' => isset($r['purpose']) ? $r['purpose'] : '',
                'amount' => isset($r['amount']) ? $r['amount'] : 0,
                'date' => isset($r['date']) ? $r['date'] : '',
                'actual_date' => isset($r['actual_date']) ? $r['actual_date'] : '',
                'add_by' => isset($r['add_by']) ? $r['add_by'] : '',
            );
        }
        return array(
            'start_date' => $start_date,
            'end_date' => $end_date,
            'date_type' => $date_type,
            'total_expenses' => isset($total[0]['total_expenses']) ? (float) $total[0]['total_expenses'] : 0,
            'rows' => $out,
        );
    }

    public function profit_report($user, $start_date = null, $end_date = null, $date_type = 'date')
    {
        $this->_boot($user);
        if (!$start_date) {
            $start_date = date('Y-m-01');
        }
        if (!$end_date) {
            $end_date = date('Y-m-t');
        }
        $expense = $this->ci->dashboards->getSelectiveExpenses($start_date, $end_date, $date_type);
        $profit = $this->ci->dashboards->getSelectiveProfits($start_date, $end_date, $date_type);
        $income = isset($profit[0]['total_submitted_fee']) ? (float) $profit[0]['total_submitted_fee'] : 0;
        $exp = isset($expense[0]['total_expenses']) ? (float) $expense[0]['total_expenses'] : 0;
        return array(
            'start_date' => $start_date,
            'end_date' => $end_date,
            'date_type' => $date_type,
            'total_income' => $income,
            'total_expense' => $exp,
            'total_profit' => $income - $exp,
        );
    }

    public function classes_status_report($user)
    {
        $this->_boot($user);
        $rows = $this->ci->dashboards->classesStatus();
        $out = array();
        foreach ($rows as $r) {
            $cid = (int) $r['class_id'];
            $active = (int) $r['total_students'];
            $seats = (int) $r['seats'];
            $out[] = array(
                'class_id' => $cid,
                'name' => isset($r['name']) ? $r['name'] : '',
                'total_students' => $active,
                'seats' => $seats,
                'available_seats' => $seats - $active,
                'decided_fee' => function_exists('totalStudentsDecidedFee') ? totalStudentsDecidedFee($cid) : 0,
                'created_fee' => function_exists('totalStudentsFee') ? totalStudentsFee($cid) : 0,
                'paid_fee' => function_exists('totalStudentsPaidFee') ? totalStudentsPaidFee($cid) : 0,
                'deactive_students' => function_exists('totalDeactiveStudents') ? totalDeactiveStudents($cid) : 0,
                'deactive_decided_fee' => function_exists('totalDeactiveStudentsDecidedFee') ? totalDeactiveStudentsDecidedFee($cid) : 0,
                'deactive_created_fee' => function_exists('totalDeactiveStudentsFee') ? totalDeactiveStudentsFee($cid) : 0,
                'deactive_paid_fee' => function_exists('totalDeactiveStudentsPaidFee') ? totalDeactiveStudentsPaidFee($cid) : 0,
            );
        }
        return array('rows' => $out);
    }
}
