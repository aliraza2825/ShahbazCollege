<?php
// Isolated parser tests: no constructor, database, S3 or import writes.
define('BASEPATH',__DIR__); define('APPPATH',dirname(__DIR__).'/');
class CI_Controller { public $db; }
require APPPATH.'controllers/Accountsapi.php';
class StatementTestDb {
    public $id;
    function get_where($table,$where) { $this->id=$where['id']; return $this; }
    function row_array() { return array('account_name'=>in_array($this->id,array(5,10,12,17,99)) ? 'Bank Alhabib Limited (test)' : 'Other Bank'); }
}
$r=new ReflectionClass('Accountsapi'); $controller=$r->newInstanceWithoutConstructor(); $controller->db=new StatementTestDb;
$method=$r->getMethod('_brs_parse_csv_lines'); $method->setAccessible(true);
function parseRows($rows,$id=5) { global $method,$controller; $p=tempnam(sys_get_temp_dir(),'bahl-'); try { $f=fopen($p,'w'); foreach($rows as $row) fputcsv($f,$row); fclose($f); return $method->invoke($controller,$id,$p); } finally { unlink($p); } }
function check($ok,$label) { if (!$ok) throw new Exception($label); echo "PASS: $label\n"; }
$header=array('DATE','VALUE DATE','INSTRUMENT/DOC NO','DETAILS','DEBIT','CREDIT','BALANCE');
$credit=array('="2026-10-16"','="2026-10-17"','00123','Test, credit','','="16000.00"','="1186241.01"');
$debit=array('2026-10-17','2026-10-17','','Test debit','="1,000.25"','','="1,185,240.76"');
$rows=array(array('Name:','Test'),array(),$header,array('**Opening Balance','="1170241.01"'),$credit,$debit,array('**Closing Balance','="1185240.76"'),array('','','','Totals:','="1000.25"','="16000.00"'));
foreach(array(5,10,12,17,99) as $id) { $out=parseRows($rows,$id); check(count($out)===2 && !isset($out['error']),'new format for Al Habib account '.$id); }
check($out[0]['trans_date']==='2026-10-16' && $out[0]['credit']==='16000.00' && $out[0]['reference_no']==='00123','wrappers decoded, DATE preferred, reference leading zeros kept');
check($out[1]['debit']==='1000.25' && $out[1]['credit']==='' && $out[1]['balance']==='1185240.76','debit, decimals and thousands separator preserved');
$reverse=array_reverse($header);$ordered=array_reverse($credit); $out=parseRows(array($reverse,$ordered)); check($out[0]['credit']==='16000.00','column mapping follows headers');
$bom=$header;$bom[0]="\xEF\xBB\xBFdate"; $out=parseRows(array($bom,$credit));check(count($out)===1,'BOM and case-insensitive headers');
foreach(array('date'=>array(0,'="2026-02-30"'),'formula'=>array(5,'=SUM(1,2)'),'bad amount'=>array(5,'12,34.00'),'missing balance'=>array(6,''),'negative credit'=>array(5,'-1'),'both debit and credit'=>array(4,'20'),'missing description'=>array(3,'')) as $label=>$change) { $bad=$credit;$bad[$change[0]]=$change[1];$out=parseRows(array($header,$credit,$bad));check(isset($out['error']),'reject entire file: '.$label); }
$bad=$credit;array_pop($bad);check(isset(parseRows(array($header,$bad))['error']),'short transaction row rejected');
$bad=$header;$bad[6]='OTHER';check(isset(parseRows(array($bad,$credit))['error']),'missing header column rejected');
check(isset(parseRows(array($header,array('**Closing Balance','0')))['error']),'empty statement rejected');
$zero=$credit;$zero[5]='0';check(isset(parseRows(array($header,$zero))['error']),'zero-value transaction rejected');
$legacy=array(array('Bank'),array('Account'),array('Period'),array('Date','Description','Reference','Unused','Amount','Type','Unused','Balance'),array('16-Oct-2026','Old credit','001','','200','Cr','','1200'),array('17-Oct-2026','Old debit','002','','100','Dr','','1100'));
foreach(array(5,10,12,17) as $id) { $out=parseRows($legacy,$id);check(count($out)===2 && $out[0]['credit']==='200' && $out[1]['debit']==='100','legacy Al Habib account '.$id); }
$out=parseRows(array(array('Date','Description','Debit','Credit','Balance'),array('2026-10-16','Other credit','','500','600')),18);check(count($out)===1 && $out[0]['credit']==='500','generic other bank unchanged');
$out=parseRows(array(array('Date'),array(),array('2026-10-16','Other bank debit','20','','500')),6);check(count($out)===1 && $out[0]['debit']==='20','existing account-specific other bank unchanged');
$bank3=array_fill(0,13,array('Metadata'));$bank3[]=array('Transaction Date','Value date','Description','Debit','Credit','Balance');$bank3[]=array('2026-10-16','','Bank 3','','300','900');
$out=parseRows($bank3,3);check(count($out)===1 && $out[0]['credit']==='300','bank 3 format unchanged');
$bank4=array(array('Metadata'),array('Metadata'),array('Metadata'),array('Date/Time'),array('16/10/2026','ref','Part one','Part two','10','20','900'));
$out=parseRows($bank4,4);check(count($out)===1 && $out[0]['trans_date']==='2026-10-16' && $out[0]['description']==='Part one Part two','bank 4 date and description unchanged');
$bank7=$bank4;$bank7[4][0]='16-10-2026 12:00';$out=parseRows($bank7,7);check(count($out)===1 && $out[0]['credit']==='10' && $out[0]['debit']==='20','bank 7 debit/credit order unchanged');
$out=parseRows(array(array('Date'),array(),array('2026-10-16','Bank 11','20','','500')),11);check(count($out)===1 && $out[0]['debit']==='20','bank 11 format unchanged');
$out=$method->invoke($controller,5,__DIR__.'/nonexistent-statement.csv');check(isset($out['error']),'unreadable file rejected');
if (isset($argv[1])) {
 $out=$method->invoke($controller,10,$argv[1]);
 check(!isset($out['error']) && count($out)===21,'provided CSV has 21 transactions');
 $creditTotal=0;$debitTotal=0;$balance=117024101;
 foreach($out as $line) { $c=(int)round((float)$line['credit']*100);$d=(int)round((float)$line['debit']*100);$creditTotal+=$c;$debitTotal+=$d;$balance+=$c-$d; if($balance!==(int)round((float)$line['balance']*100))throw new Exception('Running balance mismatch'); }
 check($creditTotal===21240000 && $debitTotal===0 && $balance===138264101,'provided CSV totals and all running balances reconcile');
}
