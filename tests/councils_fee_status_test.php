<?php
// Run with: php tests/councils_fee_status_test.php
define('BASEPATH', __DIR__);
require __DIR__ . '/../libraries/Councils_drill_service.php';

class FeeStatusResult {
    private $data;
    public function __construct($data) { $this->data = $data; }
    public function row_array() { return $this->data; }
    public function result_array() { return $this->data; }
}
class FeeStatusDb {
    public $rule;
    public $payment;
    public $paymentCriteria;
    public function where($key, $value) { return $this; }
    public function order_by($key, $direction) { return $this; }
    public function get($table) { return new FeeStatusResult($this->rule); }
    public function get_where($table, $criteria) {
        if ($table === 'punjab_council_roll_number') return new FeeStatusResult(array_fill(0, 4, array()));
        $this->paymentCriteria = $criteria;
        return new FeeStatusResult($this->payment);
    }
}
function check_fee_status($condition, $message) {
    if (!$condition) { fwrite(STDERR, "FAIL: $message\n"); exit(1); }
}
$serviceClass = new ReflectionClass('Councils_drill_service');
$service = $serviceClass->newInstanceWithoutConstructor();
$ciProperty = $serviceClass->getProperty('ci');
$ciProperty->setAccessible(true);
$db = new FeeStatusDb();
$ciProperty->setValue($service, (object) array('db' => $db));
$method = $serviceClass->getMethod('_fee_task_blocks');
$method->setAccessible(true);
$student = array('student_id' => 1196, 'cnic' => '33401-0560065-7');
$task = array('recurring' => 'After Chances', 'no_of_chances' => 4, 'type_name' => 'After 4 Chance fee', 'council_sequence_id' => 7);
$exam = array('id' => 29, 'class' => 2, 'first_year' => 29, 'first_year_type' => 'supplementary');
$current = array('class' => 2);
$rule = array('exam_fee' => 5000, 'from_date' => '2026-10-12', 'to_date' => '2026-10-31');
$run = function ($ruleValue, $payment) use ($db, $method, $service, $student, $task, $exam, $current) {
    $db->rule = $ruleValue; $db->payment = $payment; $items = array();
    $args = array($student, $task, $exam, $current, 'Year', &$items, false);
    $blocks = $method->invokeArgs($service, $args);
    return array($blocks[0], $items);
};
list($block, $items) = $run(null, array('paid' => 0));
check_fee_status($block['fee_created'] && !$block['paid'] && $block['error'] === null && !$items, 'expired rule must retain existing unpaid fee');
check_fee_status($db->paymentCriteria === array('student_id' => 1196, 'exam_sequence_id' => 29, 'exam_class' => 2, 'council_sequence_id' => 7), 'payment match must use student, exam, class and fee type');
list($block, $items) = $run(null, array('paid' => 1, 'updated_at' => '2026-10-20'));
check_fee_status($block['fee_created'] && $block['paid'] && $block['paid_at'] === '2026-10-20' && !$items, 'expired rule must retain existing paid fee');
list($block, $items) = $run(null, null);
check_fee_status(!$block['fee_created'] && $block['error'] !== null && !$items, 'missing fee and rule must remain an error');
list($block, $items) = $run($rule, null);
check_fee_status(!$block['fee_created'] && $block['error'] === null && count($items) === 1 && $items[0]['amount'] === 5000 && $items[0]['type'] === 'Extra fee', 'active rule must still prepare the missing fee');
list($block, $items) = $run($rule, array('paid' => 0));
check_fee_status($block['fee_created'] && $block['fee_rule']['exam_fee'] === 5000 && !$items, 'active rule and existing fee must not create duplicate');
echo "Councils fee status tests passed.\n";
