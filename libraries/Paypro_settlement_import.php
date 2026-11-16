<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** PayPro's original settlement format: second worksheet, columns A-K. */
class Paypro_settlement_import
{
    public function parse($book)
    {
        if ($book->getSheetCount() < 2) throw new Exception('PayPro settlement file must contain the second worksheet.');
        $sheet = $book->getSheet(1);
        $rows = array(); $date = null;
        for ($r = 2; $r <= $sheet->getHighestRow(); $r++) {
            $cells = array();
            for ($c = 0; $c <= 10; $c++) $cells[] = trim((string)$sheet->getCellByColumnAndRow($c,$r)->getValue());
            if (implode('', $cells) === '') continue;
            if ($cells[2] === '') throw new Exception('PayPro ID missing on row '.$r.'.');
            $settled = $this->date_value($cells[4], $r);
            $paid = $this->date_value($cells[5], $r);
            if ($date !== null && $date !== $settled) throw new Exception('Upload one settlement date per file.');
            $date = $settled;
            $amount = str_replace(',', '', $cells[6]);
            if (!is_numeric($amount) || (float)$amount < 0) throw new Exception('Invalid paid amount on row '.$r.'.');
            $rows[] = array('customer'=>$cells[1], 'paypro_id'=>$cells[2], 'order_no'=>$cells[3],
                'paid_date'=>$paid, 'received_date'=>$settled, 'paid_amount'=>(float)$amount,
                'received_amount'=>(float)$amount, 'paid_via'=>$cells[9], 'paid_after_days'=>$cells[10]);
        }
        if (!$rows) throw new Exception('No settlement entries found in the second worksheet.');
        return array('date'=>$date,'rows'=>$rows);
    }

    private function date_value($value, $row)
    {
        if (is_numeric($value) && (float)$value > 0) return gmdate('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP((float)$value));
        $date = DateTime::createFromFormat('!d-m-Y', $value);
        if (!$date || $date->format('d-m-Y') !== $value) throw new Exception('Invalid date on row '.$row.'. Expected DD-MM-YYYY.');
        return $date->format('Y-m-d');
    }

    public function save($db, $parsed, $user_id)
    {
        $lock = 'paypro_settlement_'.$parsed['date'];
        $got = $db->query('SELECT GET_LOCK(?, 10) AS acquired',array($lock))->row_array();
        if (empty($got['acquired'])) throw new Exception('Another settlement upload is in progress. Please try again.');
        $started = false;
        try {
            if ($db->get_where('pay_pro_settlement',array('settlement_date'=>$parsed['date']))->row_array()) {
                throw new Exception('Settlement Details Already Uploaded');
            }
            $db->trans_begin(); $started = true;
            $total = 0; $link = 0; $card = 0;
            foreach ($parsed['rows'] as $row) {
                $total += $row['paid_amount'];
                if ($row['paid_via'] === '1Link') $link += $row['paid_amount'];
                if ($row['paid_via'] === 'Debit/Credit') $card += $row['paid_amount'];
            }
            $db->insert('pay_pro_settlement',array('settlement_date'=>$parsed['date'], 'total_amount'=>$total,
                'paid_amount'=>$total, 'link_amount'=>$link, 'card_amount'=>$card, 'created_by'=>(int)$user_id));
            $id = (int)$db->insert_id();
            foreach ($parsed['rows'] as $row) {
                $row['settlement_id'] = $id;
                $db->insert('settlement_payments',$row);
                $payment_id = (int)$db->insert_id();
                $tags = array('settlement_id'=>$id,'settlement_payment_id'=>$payment_id);
                $db->where('connect_pay_id',$row['paypro_id'])->update('students_payments',$tags);
                $entries = $db->get_where('students_payments',array('connect_pay_id'=>$row['paypro_id']))->result_array();
                foreach ($entries as $entry) {
                    if ($entry['type'] !== 'student') continue;
                    $challans = array_values(array_filter(array_map('trim',explode(',',$entry['challan_ids']))));
                    if ($challans) $db->where_in('challan_no',$challans)->update('payments',$tags);
                }
            }
            if (!$db->trans_status()) throw new Exception('Settlement could not be saved. No entries were imported.');
            $db->trans_commit(); $started = false;
        } catch (Exception $e) {
            if ($started) $db->trans_rollback();
            $db->query('SELECT RELEASE_LOCK(?)',array($lock));
            throw $e;
        }
        $db->query('SELECT RELEASE_LOCK(?)',array($lock));
        return array('success'=>true,'id'=>$id,'entry_count'=>count($parsed['rows']), 'message'=>'PayPro settlement uploaded successfully.');
    }
}
