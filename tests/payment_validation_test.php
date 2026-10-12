<?php
// Run with: php -n tests/payment_validation_test.php (no database or network).
define('BASEPATH', __DIR__);
require __DIR__ . '/../helpers/payment_validation_helper.php';
function check($ok, $label) {
    if (!$ok) throw new Exception('FAIL: ' . $label);
    echo 'PASS: ' . $label . PHP_EOL;
}
$normal = array('id'=>1,'student_id'=>123,'paid'=>0,'payment_comment'=>'College Fee','amount'=>7900,'remaining_installment_amount'=>0,'extra_amount'=>0,'fine_amount'=>0);
$readmission = array_merge($normal, array('payment_comment'=>'Re-Admission Fee','amount'=>0));
foreach (array(null, '', 'abc', array(), true, -1, 'NaN', 'INF', INF, '1e999', 0.1, 2147483648) as $bad) {
    check(student_payment_amount_error($bad, array($readmission)) !== null, 'reject malformed, missing, negative or unrepresentable amount ' . var_export($bad, true));
}
check(student_payment_amount_error(0, array($normal)) !== null, 'ordinary zero rejected');
check(student_payment_amount_error(7900, array($normal)) === null, 'ordinary positive accepted');
check(student_payment_amount_error('0', array($readmission)) === null, 'stored zero readmission accepted');
check(student_payment_amount_error(0, array(array_merge($readmission,array('amount'=>100)))) !== null, 'nonzero readmission cannot be waived');
foreach (array('remaining_installment_amount','extra_amount','fine_amount') as $field) {
    check(student_payment_amount_error(0, array(array_merge($readmission,array($field=>1)))) !== null, 'readmission with ' . $field . ' rejected');
}
check(student_payment_amount_error(0, array($readmission,$normal)) !== null, 'mixed merge cannot use exception');
check(student_payment_selection_error(array($normal),array(1,2),123) !== null, 'missing selected row rejected');
check(student_payment_selection_error(array($normal),array(1),124) !== null, 'another student rejected');
check(student_payment_selection_error(array(array_merge($normal,array('paid'=>1))),array(1),123) !== null, 'paid row overwrite rejected');

// Exercise the actual production action with a write-capturing database boundary.
class PaymentResponse extends Exception { public $status; public function __construct($data,$status){parent::__construct(json_encode($data));$this->status=$status;} }
class PaymentTestDb {
    public $rows;
    function __construct($rows){$this->rows=$rows;}
    function where_in($key,$ids){return $this;}
    function get($table){return $this;}
    function result_array(){return $this->rows;}
}
class PaymentTestStudent {
    public $saved=array();
    function getNextPaymentId($id,$student){return array();}
    function saveInstallment($data){$this->saved[]=$data;}
}
class PaymentTestLoader { function helper($name){} }
$source=file_get_contents(__DIR__.'/../controllers/Studentsapi.php');
$start=strpos($source,'public function payments_pay(');
$action=substr($source,$start,strpos($source,'private function _get_paypro_id()', $start)-$start);
eval('class PaymentTestAction {
 public $db; public $student; public $load; public $body; public $uploads=0;
 function __construct($body,$rows){$this->body=$body;$this->db=new PaymentTestDb($rows);$this->student=new PaymentTestStudent;$this->load=new PaymentTestLoader;}
 function _require_student_payments(){} function _body(){return $this->body;}
 function _assert_pay_through($p){} function _perm($p){return true;}
 function _upload_field($p){$this->uploads++;return "";} function _actor_name(){return "TEST";}
 function _new_challan_no(){return "TEST-MERGE";}
 function _json($data,$status=200){throw new PaymentResponse($data,$status);}
 '.$action.'}');
foreach (array(
 array('normal zero',array($normal),0,422),
 array('missing amount',array($readmission),null,422),
 array('normal positive',array($normal),7900,200),
 array('zero readmission',array($readmission),0,200),
 array('mixed zero',array($readmission,array_merge($normal,array('id'=>2))),0,422),
 array('positive merge',array($normal,array_merge($normal,array('id'=>2))),15800,200),
 array('already paid',array(array_merge($normal,array('paid'=>1))),7900,422),
 array('wrong student',array(array_merge($normal,array('student_id'=>999))),7900,422),
) as $case) {
    list($label,$rows,$amount,$expected)=$case;
    $body=array('fee_ids'=>implode(',',array_column($rows,'id')),'fee_pay_through'=>'college');
    if ($amount !== null) $body['actual_amount']=$amount;
    $action=new PaymentTestAction($body,$rows);
    try{$action->payments_pay(123);throw new Exception('No response');}catch(PaymentResponse $response){
        check($response->status===$expected,'API '.$label.' status');
        check(count($action->student->saved)===($expected===200?count($rows):0),'API '.$label.' writes');
        if($expected===422)check($action->uploads===0,'API '.$label.' rejected before side effects');
    }
}

class CI_Model { public $db; public $load; }
require __DIR__ . '/../models/Hbl_model.php';
class HblCaptureDb extends PaymentTestDb {
    public $writes=array(); public $rolledBack=false;
    function where($key,$value){return $this;}
    function update($table,$data){$this->writes[]=array('table'=>$table,'data'=>$data);return true;}
    function affected_rows(){return 1;}
    function trans_begin(){}
    function trans_rollback(){$this->rolledBack=true;}
    function trans_commit(){}
    function trans_status(){return true;}
}
foreach (array(0, null, -1, 7900) as $amount) {
    $hbl=new Hbl_model;$hbl->load=new PaymentTestLoader;$hbl->db=new HblCaptureDb(array());
    $ok=$hbl->mark_bill_paid('TEST',array('paid'=>1,'actual_amount'=>$amount));
    check($ok===($amount===7900),'HBL single validation '.var_export($amount,true));
    check(count($hbl->db->writes)===($amount===7900?1:0),'HBL invalid callback performs no writes');
}
foreach (array(0,15800) as $amount) {
    $hbl=new Hbl_model;$hbl->load=new PaymentTestLoader;$hbl->db=new HblCaptureDb(array($normal,array_merge($normal,array('id'=>2))));
    $ok=$hbl->mark_invoice_paid(array('id'=>1,'student_id'=>123,'challan_ids'=>'A,B','consumer_number'=>'MERGE','amount'=>$amount),'TXN','REF','2026-10-12');
    check($ok===($amount>0),'HBL merged validation '.$amount);
    if($amount>0) {
        foreach(array_slice($hbl->db->writes,0,2) as $write)check($write['data']['actual_amount']===15800.0,'HBL merged received total persisted');
    } else check(count($hbl->db->writes)===0,'HBL zero invoice no writes');
}
