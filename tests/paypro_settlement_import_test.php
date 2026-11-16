<?php
define('BASEPATH',__DIR__);
date_default_timezone_set('Asia/Karachi');
require __DIR__.'/../libraries/Paypro_settlement_import.php';
class SettlementTestSheet {
    public $rows;
    function __construct($rows) { $this->rows=$rows; }
    function getHighestRow() { return count($this->rows)+1; }
    function getCellByColumnAndRow($c,$r) { return new SettlementTestCell(isset($this->rows[$r-2][$c]) ? $this->rows[$r-2][$c] : ''); }
}
class SettlementTestCell { private $v; function __construct($v){$this->v=$v;} function getValue(){return $this->v;} }
class SettlementTestBook {
    public $rows; public $count=2;
    function __construct($rows){$this->rows=$rows;}
    function getSheetCount(){return $this->count;}
    function getSheet($i){if($i!==1)throw new Exception('Wrong sheet');return new SettlementTestSheet($this->rows);}
}
class SettlementTestDb {
    public $table; public $inserts=array(); public $updates=array(); public $duplicate=false; public $valid=true; public $committed=false; public $rolledBack=false; public $released=false;
    function query($sql,$args){if(strpos($sql,'RELEASE_LOCK')!==false)$this->released=true;return $this;}
    function row_array(){return $this->table==='pay_pro_settlement' ? ($this->duplicate ? array('id'=>1) : array()) : ($this->table==='students_payments' ? array('type'=>'student','challan_ids'=>'111,222') : array('acquired'=>1));}
    function get_where($t,$args){$this->table=$t;return $this;}
    function result_array(){return array($this->row_array());}
    function trans_begin(){} function trans_commit(){$this->committed=true;} function trans_rollback(){$this->rolledBack=true;}
    function trans_status(){return $this->valid;}
    function insert($t,$row){$this->inserts[]=array($t,$row);}
    function insert_id(){return count($this->inserts);}
    function where($k,$v){return $this;} function where_in($k,$v){return $this;}
    function update($t,$row){$this->updates[]=array($t,$row);}
}
function verifySettlement($ok,$label){if(!$ok)throw new Exception($label);echo "PASS: $label\n";}
$service=new Paypro_settlement_import;
$rows=array(
    array('College','Student A','000123','ORD1','16-11-2026','15-11-2026','1,200.50','','','Debit/Credit','1'),
    array('College','Student B','000124','ORD2','16-11-2026','15-11-2026','200','','','Debit/Credit','1'),
    array('College','Student C','000125','ORD3','16-11-2026','15-11-2026','300','','','1Link','1'),
);
$parsed=$service->parse(new SettlementTestBook($rows));
verifySettlement($parsed['date']==='2026-11-16' && $parsed['rows'][0]['paypro_id']==='000123' && $parsed['rows'][0]['paid_amount']===1200.5,'original second-sheet mapping, IDs and comma amounts');
$db=new SettlementTestDb; $result=$service->save($db,$parsed,99);
verifySettlement($result['entry_count']===3 && $db->inserts[0][1]['total_amount']===1700.5 && $db->inserts[0][1]['card_amount']===1400.5 && $db->inserts[0][1]['link_amount']===300.0,'settlement totals sum each receipt once');
verifySettlement(count($db->updates)===6 && $db->committed && $db->released,'student payments and challans linked and transaction committed');
$db=new SettlementTestDb; $db->duplicate=true;
try{$service->save($db,$parsed,99);throw new RuntimeException('Duplicate accepted');}catch(RuntimeException $e){throw $e;}catch(Exception $e){verifySettlement(!$db->inserts && $db->released,'duplicate settlement rejected before writes');}
$db=new SettlementTestDb; $db->valid=false;
try{$service->save($db,$parsed,99);throw new RuntimeException('Failure committed');}catch(RuntimeException $e){throw $e;}catch(Exception $e){verifySettlement($db->rolledBack && !$db->committed && $db->released,'failed import rolls back and releases lock');}
foreach(array('missing_sheet','bad_date','mixed_dates','bad_amount') as $case){
    $bad=$rows; $book=new SettlementTestBook($bad);
    if($case==='missing_sheet')$book->count=1;
    if($case==='bad_date')$book->rows[0][4]='31-02-2026';
    if($case==='mixed_dates')$book->rows[1][4]='17-11-2026';
    if($case==='bad_amount')$book->rows[0][6]='oops';
    try{$service->parse($book);throw new RuntimeException('Invalid file accepted');}catch(RuntimeException $e){throw $e;}catch(Exception $e){verifySettlement(true,$case.' rejected before saving');}
}
