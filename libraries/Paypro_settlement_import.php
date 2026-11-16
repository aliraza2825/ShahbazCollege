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
                $r = (int)$row['r']; if ($r < 1) continue;
                $cells = array_fill(0,20,'');
                foreach ($row->xpath('./*[local-name()="c"]') as $cell) {
                    if (!preg_match('/^([A-Z]+)[0-9]+$/',(string)$cell['r'],$match)) continue;
                    $c = 0; foreach (str_split($match[1]) as $letter) $c = $c*26+ord($letter)-64;
                    if ($c > 20) continue;
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
        for ($r = 1; $r <= $sheet->getHighestRow(); $r++) {
            $cells = array();
            for ($c = 0; $c < 20; $c++) $cells[] = trim((string)$sheet->getCellByColumnAndRow($c,$r)->getValue());
            $input[$r] = $cells;
        }
        return $this->parse_rows($input);
    }

    private function parse_rows($input)
    {
        $rows = array(); $date = null; $via_col = 9; $delay_col = 10;
        if (isset($input[1])) foreach ($input[1] as $col => $heading) {
            $heading = strtolower(preg_replace('/[^a-z0-9]/i', '', $heading));
            if ($heading === 'paidvia') $via_col = $col;
            if (strpos($heading, 'delay') === 0) $delay_col = $col;
        }
        foreach ($input as $r => $cells) {
            if ($r < 2) continue;
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
                'received_amount'=>(float)$amount, 'paid_via'=>isset($cells[$via_col]) ? $cells[$via_col] : '', 'paid_after_days'=>isset($cells[$delay_col]) ? $cells[$delay_col] : '');
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

    public function compare($existing, $incoming)
    {
        $by_id = array(); $added = array(); $same = 0; $conflicts = array();
        foreach ($existing as $row) $by_id[(string)$row['paypro_id']] = $row;
        foreach ($incoming as $row) {
            $key = (string)$row['paypro_id'];
            if (isset($by_id[$key])) {
                $old = $by_id[$key]; $equal = true;
                foreach (array('order_no','paid_date','received_date','paid_via') as $field) {
                    if (strcasecmp(trim((string)$old[$field]),trim((string)$row[$field])) !== 0) $equal = false;
                }
                foreach (array('paid_amount','received_amount') as $field) if (abs((float)$old[$field]-(float)$row[$field]) > 0.005) $equal=false;
                if ($equal) $same++; else $conflicts[] = $key;
            } else { $added[]=$row; $by_id[$key]=$row; }
        }
        return array('added'=>$added,'unchanged'=>$same,'conflicts'=>$conflicts);
    }

    public function save($db, $parsed, $user_id, $preview = false, $reconciler = null)
    {
        $lock = 'paypro_settlement_'.$parsed['date'];
        $got = $db->query('SELECT GET_LOCK(?, 10) AS acquired',array($lock))->row_array();
        if (empty($got['acquired'])) throw new Exception('Another settlement upload is in progress. Please try again.');
        $started = false;
        try {
            $existing = $db->get_where('pay_pro_settlement',array('settlement_date'=>$parsed['date']))->row_array();
            $old_rows = $existing ? $db->get_where('settlement_payments',array('settlement_id'=>$existing['id']))->result_array() : array();
            $plan = $this->compare($old_rows,$parsed['rows']);
            if ($preview) {
                $db->query('SELECT RELEASE_LOCK(?)',array($lock));
                return array('success'=>true,'preview'=>true,'settlement_date'=>$parsed['date'],'new_count'=>count($plan['added']),'unchanged_count'=>$plan['unchanged'],'conflicts'=>$plan['conflicts']);
            }
            if ($plan['conflicts']) throw new Exception('Conflicting receipt details for PayPro ID(s): '.implode(', ',$plan['conflicts']).'. Existing entries were preserved.');
            $db->trans_begin(); $started = true;
            // Capture unambiguous legacy allocations before new rows change
            // channel totals, so their remainder stays correct after re-upload.
            if ($existing && $reconciler) $reconciler->groups($db,(int)$existing['id'],true);
            $total = 0; $link = 0; $card = 0;
            foreach ($plan['added'] as $row) {
                $total += $row['paid_amount'];
                if ($row['paid_via'] === '1Link') $link += $row['paid_amount'];
                if ($row['paid_via'] === 'Debit/Credit') $card += $row['paid_amount'];
            }
            if ($existing) {
                $id = (int)$existing['id'];
                $db->where('id',$id)->update('pay_pro_settlement',array(
                    'total_amount'=>(float)$existing['total_amount']+$total,'paid_amount'=>(float)$existing['paid_amount']+$total,
                    'link_amount'=>(float)$existing['link_amount']+$link,'card_amount'=>(float)$existing['card_amount']+$card));
            } else {
                $db->insert('pay_pro_settlement',array('settlement_date'=>$parsed['date'], 'total_amount'=>$total,
                    'paid_amount'=>$total, 'link_amount'=>$link, 'card_amount'=>$card, 'created_by'=>(int)$user_id));
                $id = (int)$db->insert_id();
            }
            foreach ($plan['added'] as $row) {
                $row['settlement_id'] = $id;
                $db->insert('settlement_payments',$row);
                $payment_id = (int)$db->insert_id();
                $tags = array('settlement_id'=>$id,'settlement_payment_id'=>$payment_id);
                $db->where('connect_pay_id',$row['paypro_id'])->group_start()->where('settlement_payment_id',null)->or_where('settlement_payment_id',0)->group_end()->update('students_payments',$tags);
                $entries = $db->get_where('students_payments',array('connect_pay_id'=>$row['paypro_id']))->result_array();
                foreach ($entries as $entry) {
                    if ($entry['type'] !== 'student') continue;
                    $challans = array_values(array_filter(array_map('trim',explode(',',$entry['challan_ids']))));
                    if ($challans) $db->where_in('challan_no',$challans)->group_start()->where('settlement_payment_id',null)->or_where('settlement_payment_id',0)->group_end()->update('payments',$tags);
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
        return array('success'=>true,'id'=>$id,'entry_count'=>count($plan['added']),'unchanged_count'=>$plan['unchanged'], 'message'=>'Settlement updated. Existing entries and tags preserved.');
    }
}
