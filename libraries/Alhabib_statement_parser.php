<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Reads the new Al Habib CSV without evaluating Excel-style text wrappers. */
class Alhabib_statement_parser {
    private function text($value) {
        $value = trim(str_replace("\xEF\xBB\xBF", '', (string)$value));
        if (preg_match('/^="([^"]*)"$/D', $value, $match)) return $match[1];
        return $value;
    }

    private function amount($value) {
        $value = $this->text($value);
        if ($value === '' || $value === '-') return '';
        if (!preg_match('/^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:\.\d{1,2})?$/D', $value)) return false;
        return str_replace(',', '', $value);
    }

    // null means this is not the new format: let the existing legacy reader handle it.
    public function parse($filepath) {
        $fh = @fopen($filepath, 'r');
        if (!$fh) return array('error'=>'Unable to read CSV file.');
        $header = null; $lines = array(); $row = 0;
        $required = array('DATE','VALUE DATE','INSTRUMENT/DOC NO','DETAILS','DEBIT','CREDIT','BALANCE');
        try {
            while (($cells = fgetcsv($fh)) !== false) {
                $row++;
                $cells = array_map(array($this, 'text'), $cells);
                if (!count(array_filter($cells, function ($v) { return $v !== ''; }))) continue;
                if ($header === null) {
                    $labels = array_map('strtoupper', $cells);
                    if (!in_array('DATE',$labels,true) || !in_array('DETAILS',$labels,true)) continue;
                    if (count(array_intersect($required,$labels)) !== count($required) || count(array_unique($labels)) !== count($labels)) return array('error'=>'Incomplete or duplicate Al Habib CSV columns.');
                    $header = array_flip($labels);
                    continue;
                }
                $first = strtoupper($cells[0]);
                if ($first === '**OPENING BALANCE' || $first === '**CLOSING BALANCE') continue;
                if (isset($cells[$header['DETAILS']]) && strtoupper($cells[$header['DETAILS']]) === 'TOTALS:' && $cells[$header['DATE']] === '') continue;
                if (count($cells) !== count($header)) return array('error'=>'Invalid Al Habib CSV row '.$row.': incorrect column count.');
                $date = $cells[$header['DATE']];
                if (!preg_match('/^([1-9]\d{3})-(\d{2})-(\d{2})$/D',$date,$m) || !checkdate((int)$m[2],(int)$m[3],(int)$m[1])) return array('error'=>'Invalid transaction date at CSV row '.$row.'.');
                $description = $cells[$header['DETAILS']];
                $debit = $this->amount($cells[$header['DEBIT']]);
                $credit = $this->amount($cells[$header['CREDIT']]);
                $balance = $this->amount($cells[$header['BALANCE']]);
                if ($description === '' || $debit === false || $credit === false || $balance === false || $balance === '' || (float)$debit < 0 || (float)$credit < 0 || ((float)$debit > 0 && (float)$credit > 0) || ((float)$debit == 0 && (float)$credit == 0)) return array('error'=>'Invalid description or amount at CSV row '.$row.'.');
                $lines[] = array('trans_date'=>$date,'description'=>$description,'reference_no'=>$cells[$header['INSTRUMENT/DOC NO']],'debit'=>$debit,'credit'=>$credit,'balance'=>$balance);
            }
        } finally { fclose($fh); }
        if ($header === null) return null;
        return count($lines) ? $lines : array('error'=>'No transactions found in Al Habib CSV.');
    }
}
