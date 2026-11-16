<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** PayPro's original settlement format: second worksheet, columns A-K. */
class Paypro_settlement_import
{
    private function xml_part($zip, $path)
    {
        $raw = $zip->getFromName(ltrim($path,'/'));
        if ($raw === false || stripos($raw,'<!DOCTYPE') !== false || stripos($raw,'<!ENTITY') !== false) throw new Exception('Invalid Excel workbook XML.');
        $xml = simplexml_load_string($raw,'SimpleXMLElement',LIBXML_NONET);
        if ($xml === false) throw new Exception('Unable to read Excel workbook XML.');
        return $xml;
    }

    /** Read export XML by namespace URI, independent of its chosen prefix/theme. */
    public function read_xlsx($path)
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) throw new Exception('Unable to open Excel workbook.');
        try {
            $book = $this->xml_part($zip,'xl/workbook.xml');
            $sheets = $book->xpath('//*[local-name()="sheets"]/*[local-name()="sheet"]');
            if (count($sheets) < 2) throw new Exception('PayPro settlement file must contain the settlement worksheet.');
            $sheet = $sheets[1];
            foreach ($sheets as $candidate) if (strtolower((string)$candidate['name']) === 'settletransactions') $sheet = $candidate;
            $attrs = $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships');
            $rel_id = (string)$attrs['id']; $target = '';
            $rels = $this->xml_part($zip,'xl/_rels/workbook.xml.rels');
            foreach ($rels->xpath('//*[local-name()="Relationship"]') as $rel) {
                if ((string)$rel['Id'] === $rel_id && (string)$rel['TargetMode'] !== 'External') $target = (string)$rel['Target'];
            }
            if ($target === '' || strpos($target,'..') !== false) throw new Exception('Invalid settlement worksheet path.');
            $sheet_path = $target[0] === '/' ? ltrim($target,'/') : 'xl/'.$target;
            $strings = array();
            if ($zip->locateName('xl/sharedStrings.xml') !== false) {
                $shared = $this->xml_part($zip,'xl/sharedStrings.xml');
                foreach ($shared->xpath('//*[local-name()="si"]') as $si) {
                    $text = ''; foreach ($si->xpath('.//*[local-name()="t"]') as $t) $text .= (string)$t;
                    $strings[] = $text;
                }
            }
            $xml = $this->xml_part($zip,$sheet_path); $input = array();
            foreach ($xml->xpath('//*[local-name()="sheetData"]/*[local-name()="row"]') as $row) {
                $r = (int)$row['r']; if ($r < 2) continue;
                $cells = array_fill(0,11,'');
                foreach ($row->xpath('./*[local-name()="c"]') as $cell) {
                    if (!preg_match('/^([A-Z]+)[0-9]+$/',(string)$cell['r'],$match)) continue;
                    $c = 0; foreach (str_split($match[1]) as $letter) $c = $c*26+ord($letter)-64;
                    if ($c > 11) continue;
                    $values = $cell->xpath('./*[local-name()="v"]'); $value = $values ? (string)$values[0] : '';
                    if ((string)$cell['t'] === 's') {
                        if (!isset($strings[(int)$value])) throw new Exception('Invalid shared string in settlement worksheet.');
                        $value = $strings[(int)$value];
                    } elseif ((string)$cell['t'] === 'inlineStr') {
                        $value = ''; foreach ($cell->xpath('.//*[local-name()="t"]') as $t) $value .= (string)$t;
                    }
                    $cells[$c-1] = trim($value);
                }
                $input[$r] = $cells;
            }
        } catch (Exception $e) { $zip->close(); throw $e; }
        $zip->close();
        return $this->parse_rows($input);
    }

    public function parse($book)
    {
        if ($book->getSheetCount() < 2) throw new Exception('PayPro settlement file must contain the second worksheet.');
        $sheet = $book->getSheet(1);
        $input = array();
        for ($r = 2; $r <= $sheet->getHighestRow(); $r++) {
            $cells = array();
            for ($c = 0; $c <= 10; $c++) $cells[] = trim((string)$sheet->getCellByColumnAndRow($c,$r)->getValue());
            $input[$r] = $cells;
        }
        return $this->parse_rows($input);
    }

    private function parse_rows($input)
    {
        $rows = array(); $date = null;
        foreach ($input as $r => $cells) {
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
