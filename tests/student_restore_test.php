<?php
// Runs the actual restore method against an isolated fake DB; no production writes.
$source = file_get_contents(__DIR__ . '/../controllers/Studentsapi.php');
$start = strpos($source, "\tpublic function restore(");
$end = strpos($source, "\n\tpublic function revive(", $start);
if ($start === false || $end === false) throw new Exception('Restore method not found');
class RestoreResponse extends Exception {
    public $data; public $status;
    function __construct($data, $status) { $this->data = $data; $this->status = $status; }
}
class RestoreDb {
    public $student = array('student_id'=>4058, 'first_name'=>'Test', 'status'=>0, 'device_id'=>'device', 'extra_added_fee'=>0, 'mother_name'=>null);
    public $pending = array(); public $writes = array(); public $insertOk = true; private $table;
    function get_where($table, $where) { $this->table=$table; return $this; }
    function row_array() { return $this->table==='students' ? $this->student : $this->pending; }
    function list_fields($table) { return array('student_id','first_name','status','mother_name','ok_by_admin'); }
    function insert($table,$data) { $this->writes[]=array($table,$data); return $this->insertOk; }
}
eval('class RestoreHarness { public $db; public $allowed=true;
    function _is_admin() { return $this->allowed; }
    function _perm($key) { return false; }
    function _json($data,$status=200) { throw new RestoreResponse($data,$status); }
' . substr($source,$start,$end-$start) . '}');
function check($ok,$label) { if (!$ok) throw new Exception($label); echo "PASS: $label\n"; }
function runRestore($h) { try { $h->restore(4058); } catch (RestoreResponse $r) { return $r; } throw new Exception('Missing response'); }
$h=new RestoreHarness; $h->db=new RestoreDb;
$r=runRestore($h);
check($r->data['success'] && $h->db->writes===array(array('update_student_requests',array('student_id'=>4058,'first_name'=>'Test','status'=>1,'mother_name'=>null))), 'supported fields and nulls preserved; student-only fields excluded');
check($h->db->student['status']===0, 'student remains unchanged pending approval');
$h->db=new RestoreDb; $h->db->pending=array('student_id'=>4058);
$r=runRestore($h); check($r->status===422 && !$h->db->writes,'duplicate pending request rejected');
$h->db=new RestoreDb; $h->db->student=array();
$r=runRestore($h); check($r->status===404 && !$h->db->writes,'missing student rejected');
$h->db=new RestoreDb; $h->allowed=false;
$r=runRestore($h); check($r->status===403 && !$h->db->writes,'permission required');
$h->db=new RestoreDb; $h->allowed=true; $h->db->insertOk=false;
$r=runRestore($h); check($r->status===500 && !$r->data['success'],'failed insert does not report success');
