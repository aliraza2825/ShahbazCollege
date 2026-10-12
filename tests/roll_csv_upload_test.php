<?php
// Isolated import regression test; never connects to a database or S3.
define('BASEPATH', __DIR__);
class CsvTestLoader { function helper($x) {} function model($x) {} function library($x) { throw new Exception('Persistent uploader must not be called'); } }
class CsvTestDb {
    public $writes = array(); public $existing = false;
    function get_where($table,$where) { return $this; }
    function result_array() { return $this->existing ? array(array('id'=>12)) : array(); }
    function where($key,$value) { return $this; }
    function insert($table,$data) { $this->writes[]=array('insert',$data); }
    function update($table,$data) { $this->writes[]=array('update',$data); }
}
$ci = new stdClass; $ci->load=new CsvTestLoader; $ci->db=new CsvTestDb;
function &get_instance() { global $ci; return $ci; }
require __DIR__.'/../libraries/Punjab_council_service.php';
function check($ok,$label) { if(!$ok)throw new Exception($label);echo 'PASS: '.$label.PHP_EOL; }
$service=new Punjab_council_service;
$post=array('council_exam_no'=>27,'class'=>2,'course_id'=>1);
$tmp=tempnam(sys_get_temp_dir(),'roll-test-');
try {
    $fp=fopen($tmp,'w');
    fputcsv($fp,array('Roll','B','C','D','Computer','CNIC','Name','Address','I','Remarks'));
    fputcsv($fp,array('R-001','','','','COMP-1','TEST-CNIC','Test Student','Test address','','Ready'));
    fclose($fp);
    $_FILES=array('roll_no'=>array('tmp_name'=>$tmp,'error'=>UPLOAD_ERR_OK));
    $result=$service->upload_roll_csv($post);
    check($result['success'] && count($ci->db->writes)===1,'temp CSV imports without persistent upload');
    check($ci->db->writes[0][1]['roll_no']==='R-001' && $ci->db->writes[0][1]['council_exam_no']==='27','column mapping and exam preserved');
    $ci->db->existing=true;$ci->db->writes=array();
    $result=$service->upload_roll_csv($post);
    check($result['success'] && $ci->db->writes[0][0]==='update','existing roll updates');
    foreach(array(UPLOAD_ERR_PARTIAL,UPLOAD_ERR_INI_SIZE,UPLOAD_ERR_NO_FILE) as $error) {
        $ci->db->writes=array();$_FILES['roll_no']['error']=$error;
        check(!$service->upload_roll_csv($post)['success'] && !$ci->db->writes,'upload error '.$error.' makes no writes');
    }
    $_FILES=array();check(!$service->upload_roll_csv($post)['success'],'missing upload rejected');
    $_FILES=array('roll_no'=>array('error'=>UPLOAD_ERR_OK,'tmp_name'=>$tmp.'.missing'));
    check(!$service->upload_roll_csv($post)['success'],'missing temp file rejected');
} finally { unlink($tmp); }
