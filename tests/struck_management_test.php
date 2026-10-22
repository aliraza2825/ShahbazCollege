<?php
define('BASEPATH',__DIR__);
require __DIR__.'/../libraries/Reports_service.php';
class StruckManagementDb {
    public $access=array('can_student_struckof'=>1,'class_ids'=>'54,55','campus_ids'=>'17');
    public $queries=array(); public $total=61; private $accessQuery=false;
    function get_where($table,$where) { $this->accessQuery=true; return $this; }
    function row_array() { if ($this->accessQuery) { $this->accessQuery=false; return $this->access; } return array('total'=>$this->total); }
    function query($sql,$params) { $this->accessQuery=false; $this->queries[]=array($sql,$params); return $this; }
    function result_array() { return array(); }
}
$db=new StruckManagementDb; $ci=(object)array('db'=>$db);
$ref=new ReflectionClass('Reports_service'); $service=$ref->newInstanceWithoutConstructor();
$p=$ref->getProperty('ci'); $p->setAccessible(true); $p->setValue($service,$ci);
$user=array('user_id'=>10,'role'=>'Staff');
function verifyStruck($condition,$label) { if (!$condition) throw new Exception($label); echo "PASS: $label\n"; }
$r=$service->struck_management_list($user,array('page'=>2,'campus_id'=>17,'course_id'=>3,'class_id'=>54,'month'=>'2026-10'));
list($sql,$params)=$db->queries[1];
verifyStruck(strpos($sql,"FROM struckof_procedures p INNER JOIN students")!==false && strpos($sql,"WHERE p.status='pending'")!==false,'only existing pending processes enter the list');
verifyStruck(strpos($sql,'s.class_id IN (?,?)')!==false && $params===array(54,55,17,3,54,'2026-10-01','2026-11-01'),'permissions and all filters bind independently');
verifyStruck(strpos($sql,'LIMIT 25 OFFSET 25')!==false && $r['data']['pages']===3,'pagination applies on the server');
verifyStruck(strpos($sql,'d.process_count=p.process_count')!==false,'letter count belongs to each pending process');
$r=$service->struck_management_list($user,array('page'=>999,'per_page'=>50));
verifyStruck($r['data']['page']===2 && $r['data']['per_page']===50,'out-of-range page clamps to final page');
$r=$service->struck_management_list($user,array('month'=>'2026-13'));
verifyStruck($r['code']===422,'invalid month rejected');
$r=$service->struck_management_list($user,array('course_id'=>'1 OR 1=1'));
verifyStruck($r['code']===422,'invalid filter rejected');
$db->queries=array(); $service->struck_management_filters($user);
verifyStruck(count($db->queries)===2 && strpos($db->queries[0][0],"p.status='pending'")!==false && strpos($db->queries[1][0],"p.status='pending'")!==false,'dropdowns use the same scoped pending population');
$db->access['can_student_struckof']=0; $db->queries=array();
$r=$service->struck_management_list($user,array());
verifyStruck($r['code']===403 && !$db->queries,'missing permission denies data query');
$db->access['can_student_struckof']=1; $db->access['class_ids']=''; $db->access['campus_ids']='';
$r=$service->struck_management_list($user,array());
verifyStruck($r['code']===403,'staff without scope denied');
$db->total=0;
$r=$service->struck_management_list(array('role'=>'Admin','user_id'=>1),array());
verifyStruck($r['data']['total']===0 && $r['data']['pages']===1,'empty report has stable pagination');
