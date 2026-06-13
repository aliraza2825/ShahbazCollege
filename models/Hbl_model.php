<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hbl_model extends CI_Model
{
    public function get_bill_by_consumer_no($consumerNumber)
    {
        $this->db->select("
            payments.id,
            payments.student_id,
            payments.amount,
            payments.dead_line,
            payments.paid,
            payments.paid_date,
            payments.actual_paid_date,
            payments.tid_no,
            payments.bank_challan_no,
            payments.payment_comment,
            students.first_name,
            students.last_name
        ");
        $this->db->from('payments');
        $this->db->join('students', 'students.student_id = payments.student_id', 'left');
        $this->db->where('payments.id', $consumerNumber);
        $row = $this->db->get()->row_array();

        if (!empty($row)) {
            $first = isset($row['first_name']) ? trim($row['first_name']) : '';
            $last  = isset($row['last_name']) ? trim($row['last_name']) : '';
            $row['customer_name'] = trim($first . ' ' . $last);
        }

        return $row;
    }

    public function get_by_transaction_id($transactionId)
    {
        return $this->db
            ->where('tid_no', $transactionId)
            ->get('payments')
            ->row_array();
    }

    public function get_by_reference_number($referenceNumber)
    {
        return $this->db
            ->where('bank_challan_no', $referenceNumber)
            ->get('payments')
            ->row_array();
    }

    public function mark_bill_paid($consumerNumber, $data)
    {
        $this->db->where('id', $consumerNumber);
        $this->db->where('paid', 0);
        $this->db->update('payments', $data);

        return ($this->db->affected_rows() > 0);
    }

    public function mark_bill_unpaid_by_transaction_id($transactionId, $data)
    {
        $this->db->where('tid_no', $transactionId);
        $this->db->where('paid', 1);
        $this->db->update('payments', $data);

        return ($this->db->affected_rows() > 0);
    }

    public function reverse_id_exists($reverseTransactionId)
    {
        return $this->db
            ->where('reverse_transaction_id', $reverseTransactionId)
            ->get('hbl_reverse_logs')
            ->row_array();
    }

    public function insert_reverse_log($data)
    {
        return $this->db->insert('hbl_reverse_logs', $data);
    }
}