<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hbl_model extends CI_Model
{
    private function ensure_invoice_table()
    {
        if ($this->db->table_exists('hbl_payment_invoices')) return;
        $this->db->query("CREATE TABLE IF NOT EXISTS `hbl_payment_invoices` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `consumer_number` VARCHAR(64) NOT NULL,
            `student_id` INT NOT NULL,
            `challan_ids` TEXT NOT NULL,
            `amount` DECIMAL(14,2) NOT NULL DEFAULT 0,
            `status` VARCHAR(20) NOT NULL DEFAULT 'PENDING',
            `transaction_id` VARCHAR(150) NULL,
            `reference_number` VARCHAR(150) NULL,
            `created_at` DATETIME NOT NULL,
            `paid_at` DATETIME NULL,
            `reversed_at` DATETIME NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_hbl_consumer` (`consumer_number`),
            KEY `idx_hbl_transaction` (`transaction_id`),
            KEY `idx_hbl_reference` (`reference_number`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function create_invoice($data)
    {
        $this->ensure_invoice_table();
        $this->db->insert('hbl_payment_invoices', $data);
        return $this->db->insert_id();
    }

    public function get_invoice_by_consumer_no($consumerNumber)
    {
        $this->ensure_invoice_table();
        $this->db->select('hbl_payment_invoices.*, students.first_name, students.last_name');
        $this->db->from('hbl_payment_invoices');
        $this->db->join('students', 'students.student_id = hbl_payment_invoices.student_id', 'left');
        $this->db->where('hbl_payment_invoices.consumer_number', $consumerNumber);
        $row = $this->db->get()->row_array();
        if ($row) $row['customer_name'] = trim($row['first_name'] . ' ' . $row['last_name']);
        return $row;
    }

    public function get_invoice_by_transaction_id($transactionId)
    {
        $this->ensure_invoice_table();
        return $this->db->get_where('hbl_payment_invoices', array('transaction_id' => $transactionId))->row_array();
    }

    public function get_invoice_by_reference_number($referenceNumber)
    {
        $this->ensure_invoice_table();
        return $this->db->get_where('hbl_payment_invoices', array('reference_number' => $referenceNumber))->row_array();
    }

    public function mark_invoice_paid($invoice, $transactionId, $referenceNumber, $paidDate)
    {
        $challans = array_values(array_filter(array_map('trim', explode(',', (string)$invoice['challan_ids']))));
        if (!count($challans)) return false;
        $this->db->trans_begin();
        $rows = $this->db->where_in('challan_no', $challans)
            ->where('student_id', (int)$invoice['student_id'])->where('paid', 0)
            ->get('payments')->result_array();
        if (count($rows) !== count($challans)) {
            $this->db->trans_rollback();
            return false;
        }
        $merged = count($challans) > 1 ? $invoice['consumer_number'] : null;
        foreach ($rows as $row) {
            $this->db->where('id', $row['id'])->update('payments', array(
                'paid' => 1,
                'paid_date' => $paidDate,
                'actual_paid_date' => $paidDate,
                'merged_challan' => $merged,
                'paid_challans' => $invoice['challan_ids'],
                'tid_no' => $transactionId,
                'bank_challan_no' => $referenceNumber,
                'fee_pay_through' => 'HBL',
                'bank_details' => 'HBL API',
                'paid_by' => 'HBL',
            ));
        }
        $this->db->where('id', $invoice['id'])->update('hbl_payment_invoices', array(
            'status' => 'PAID', 'transaction_id' => $transactionId,
            'reference_number' => $referenceNumber, 'paid_at' => date('Y-m-d H:i:s'),
        ));
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }
        $this->db->trans_commit();
        return true;
    }

    public function reverse_invoice($invoice)
    {
        $this->db->trans_begin();
        $this->db->where('merged_challan', $invoice['consumer_number'])->update('payments', array(
            'paid' => 0,
            'paid_date' => null,
            'actual_paid_date' => null,
            'merged_challan' => null,
            'paid_challans' => null,
            'tid_no' => null,
            'bank_challan_no' => null,
            'bank_details' => 'HBL API REVERSED',
            'paid_by' => null,
        ));
        // A single-challan HBL invoice has no merged_challan marker.
        if ($this->db->affected_rows() === 0) {
            $challans = array_values(array_filter(array_map('trim', explode(',', (string)$invoice['challan_ids']))));
            if (count($challans)) {
                $this->db->where_in('challan_no', $challans)
                    ->where('student_id', (int)$invoice['student_id'])
                    ->where('tid_no', $invoice['transaction_id'])
                    ->update('payments', array(
                        'paid' => 0, 'paid_date' => null, 'actual_paid_date' => null,
                        'paid_challans' => null, 'tid_no' => null, 'bank_challan_no' => null,
                        'bank_details' => 'HBL API REVERSED', 'paid_by' => null,
                    ));
            }
        }
        $this->db->where('id', $invoice['id'])->update('hbl_payment_invoices', array(
            'status' => 'REVERSED', 'reversed_at' => date('Y-m-d H:i:s'),
        ));
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }
        $this->db->trans_commit();
        return true;
    }

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
        // HBL sends the college challan number as p_ConsumerNumber.
        $this->db->where('payments.challan_no', $consumerNumber);
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
        // Keep the payment update on the same HBL consumer identifier used at inquiry.
        $this->db->where('challan_no', $consumerNumber);
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
