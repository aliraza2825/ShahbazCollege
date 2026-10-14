<?php
define('BASEPATH',__DIR__);
require __DIR__.'/../libraries/Reports_service.php';
require __DIR__.'/../libraries/Batch_recovery_report.php';
class BatchAccessDb {
    public $access=array('reports_student_fee_problem'=>1,'campus_ids'=>'17','class_ids'=>'54');
    public $queries=array(); private $table; public $filters=array();
    function get_where($table,$where) { $this->table=$table; return $this; }
    function row_array() { return $this->access; }
    function distinct() { return $this; } function select($x) { return $this; }
    function from($x) { $this->table=$x; return $this; } function join($x,$on) { return $this; }
    function where_in($key,$value) { $this->filters[]=array($key,$value); return $this; }
    function where($key,$value) { $this->filters[]=array($key,$value); return $this; }
    function order_by($a,$b) { return $this; } function get() { return $this; }
    function result_array() { return $this->table==='campuses c' ? array(array('campus_id'=>17,'campus_name'=>'Test College')) : array(); }
    function query($sql,$params) { $this->queries[]=array($sql,$params); throw new Exception('Unexpected query'); }
}
class BatchAccessLoader { function library($x) {} }
$db=new BatchAccessDb; $ci=new stdClass; $ci->db=$db; $ci->load=new BatchAccessLoader; $ci->batch_recovery_report=new Batch_recovery_report;
$ref=new ReflectionClass('Reports_service'); $service=$ref->newInstanceWithoutConstructor();
$p=$ref->getProperty('ci'); $p->setAccessible(true); $p->setValue($service,$ci);
$user=array('user_id'=>10,'role'=>'Staff');
function checkAccess($ok,$label) { if (!$ok) throw new Exception($label); echo "PASS: $label\n"; }
$r=$service->batch_recovery($user,18,'2026-10','2026-10-31');
checkAccess(!$r['success'] && $r['code']===403,'unauthorized college denied');
$r=$service->batch_recovery($user,17,'2026-13','2026-10-31'); checkAccess($r['code']===422,'invalid month denied');
$r=$service->batch_recovery($user,17,'2026-10','2026-02-30'); checkAccess($r['code']===422,'invalid cutoff denied');
$r=$service->batch_recovery($user,17,'2026-10','2026-10-31');
checkAccess($r['success'] && !$r['data']['student_count'] && !$db->queries,'empty report avoids payment query');
checkAccess(in_array(array('cl.class_id',array(54)),$db->filters,true) && in_array(array('cl.campus_id',17),$db->filters,true),'student scope restricted by campus and class');
$db->access['class_ids']=''; $r=$service->batch_recovery_campuses($user); checkAccess(!$r['data'],'missing class scope exposes no colleges');
$db->access['reports_student_fee_problem']=0; $r=$service->batch_recovery($user,17,'2026-10','2026-10-31'); checkAccess($r['code']===403,'fee report permission required');
