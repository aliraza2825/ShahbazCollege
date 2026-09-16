<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** JSON API for the legacy Fees Dues screen. */
class Feesapi extends CI_Controller {
	private $current_user = null;
	private $access_row = array();

	public function __construct()
	{
		parent::__construct();
		$this->_cors();
		if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
		$this->current_user = $this->_auth_user();
		if (!$this->current_user) $this->_json(array('success' => false, 'message' => 'Unauthorized'), 401);
		$row = $this->db->get_where('access', array('user_id' => (int)$this->current_user['user_id']))->row_array();
		$this->access_row = $row ? $row : array();
		if (!$this->_is_admin() && empty($this->access_row['fee_due_sidebar'])) $this->_json(array('success' => false, 'message' => 'Fees Dues access required'), 403);
	}

	private function _cors() {
		$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
		$allowed = array('https://pos.shahbazcollegeofpharmacy.edu.pk', 'http://pos.shahbazcollegeofpharmacy.edu.pk', 'http://localhost:5173', 'http://localhost:4173');
		if ($origin === '*' || in_array($origin, $allowed)) header('Access-Control-Allow-Origin: ' . ($origin === '*' ? '*' : $origin));
		header('Access-Control-Allow-Methods: GET, POST, OPTIONS'); header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Pos-Token'); header('Access-Control-Allow-Credentials: true');
	}
	private function _json($data, $code = 200) { http_response_code($code); header('Content-Type: application/json; charset=utf-8'); echo json_encode($data); exit; }
	private function _body() { $body = json_decode(file_get_contents('php://input'), true); return is_array($body) ? $body : $this->input->post(); }
	private function _auth_user() {
		$token = isset($_SERVER['HTTP_X_POS_TOKEN']) ? $_SERVER['HTTP_X_POS_TOKEN'] : $this->input->get_request_header('X-Pos-Token', TRUE);
		if (!$token) return null; $tokenRow = $this->db->get_where('pos_api_tokens', array('token' => $token))->row_array();
		if (!$tokenRow || strtotime($tokenRow['expires_at']) < time()) return null;
		return $this->db->get_where('users', array('user_id' => $tokenRow['user_id'], 'status' => '1'))->row_array();
	}
	private function _is_admin() { return isset($this->current_user['role']) && $this->current_user['role'] === 'Admin'; }
	private function _class_ids() { return $this->_is_admin() ? array() : array_filter(array_map('intval', explode(',', isset($this->access_row['class_ids']) ? $this->access_row['class_ids'] : ''))); }

	public function meta() {
		$this->db->select('campus_id, campus_name')->from('campuses')->where('status', '1')->order_by('campus_name');
		$campuses = $this->db->get()->result_array();
		$this->db->select('class_id, name, campus_id, session')->from('classes')->where('status', '1'); $ids = $this->_class_ids(); if (count($ids)) $this->db->where_in('class_id', $ids); $this->db->order_by('class_id');
		$this->_json(array('success' => true, 'data' => array('campuses' => $campuses, 'classes' => $this->db->get()->result_array(), 'is_admin' => $this->_is_admin())));
	}

	public function dues() {
		$classId = (int)$this->input->get('class_id'); $campusId = (int)$this->input->get('campus_id'); $type = $this->input->get('type') === 'contractors' ? 'contractors' : 'students'; $todayWise = $this->input->get('today_wise') === '1';
		if ($type === 'contractors') { $this->_json(array('success' => true, 'type' => $type, 'rows' => $this->_contractor_dues($campusId))); }
		$allowed = $this->_class_ids(); if (!$classId || (count($allowed) && !in_array($classId, $allowed))) $this->_json(array('success' => false, 'message' => 'Select an allowed class'), 422);
		$this->db->select('p.id AS fee_id, s.student_id, s.first_name, s.last_name, s.mobile, s.emergency_no, s.cnic, s.roll_no, c.name AS class_name, c.campus_id, ca.campus_name, co.course_name')
			->from('payments p')->join('students s', 's.student_id=p.student_id')->join('classes c', 'c.class_id=s.class_id')->join('campuses ca', 'ca.campus_id=c.campus_id')->join('courses co', 'co.course_id=s.course_id')
			->where(array('s.status' => 1, 'c.class_id' => $classId, 'p.paid' => 0))->where('p.dead_line <=', date('Y-m-d', strtotime('+1 week')))->order_by('s.roll_no');
		if ($todayWise) { $this->db->join('machine_data md', 'md.teacher_student_id=s.student_id')->join('attendence a', 'a.machine_user_id=md.machine_id')->where('a.time >=', date('Y-m-d').' 00:00:00')->where('a.time <=', date('Y-m-d').' 23:59:59')->group_by('s.student_id'); }
		$rows = $this->db->get()->result_array(); foreach ($rows as &$row) $row = array_merge($row, $this->_student_summary((int)$row['student_id'])); unset($row);
		$this->_json(array('success' => true, 'type' => $type, 'rows' => $rows));
	}

	private function _student_summary($studentId) {
		$payments = $this->db->order_by('dead_line', 'ASC')->get_where('payments', array('student_id' => $studentId))->result_array();
		$total = 0; $council = 0; $councilPaid = 0; $due = 0; $paid = 0; $unpaid = array(); $paidLines = array();
		foreach ($payments as $p) { $amount=(float)$p['amount']; $isCouncil=(isset($p['payment_plan']) && $p['payment_plan']==='consulation fee'); if (!$isCouncil) $total += $amount; else { $council += $amount; if (!empty($p['paid'])) $councilPaid += (float)$p['actual_amount']; }
			if ($p['dead_line'] < date('Y-m-d')) { $due += $amount; if (empty($p['paid'])) $unpaid[] = array('amount'=>$amount,'dead_line'=>$p['dead_line']); } if (!empty($p['paid']) && !$isCouncil) { $paid += (float)$p['actual_amount']; $paidLines[] = array('amount'=>(float)$p['actual_amount'],'date'=>$p['paid_date']); }
		}
		return array('total_fee'=>$total, 'council_fee'=>$council, 'council_paid'=>$councilPaid, 'fee_due_to_date'=>$due, 'fee_paid'=>$paid, 'remaining_due'=>$due-$paid, 'unpaid_count'=>count($unpaid), 'paid_lines'=>$paidLines, 'unpaid_lines'=>$unpaid);
	}

	private function _contractor_dues($campusId) {
		$this->db->select('p.id AS fee_id,p.amount,p.extra_amount,p.dead_line,co.name,co.mobile,co.emergency_no,ca.campus_name')->from('payments p')->join('contracts ct','ct.contract_id=p.contract_id')->join('contractors co','co.contractor_id=ct.contractor_id')->join('campuses ca','ca.campus_id=ct.campus_id')->where(array('p.paid'=>0))->where('p.dead_line <=', date('Y-m-t')); if ($campusId) $this->db->where('ct.campus_id',$campusId);
		$rows=$this->db->get()->result_array(); foreach ($rows as &$row) $row['remarks']=$this->db->order_by('fee_remarks_id','DESC')->get_where('fees_remarks',array('fee_id'=>$row['fee_id']))->result_array(); unset($row); return $rows;
	}

	public function comment() {
		$b=$this->_body(); $feeId=(int)(isset($b['fee_id'])?$b['fee_id']:0); $comment=trim(isset($b['comment'])?$b['comment']:''); $next=isset($b['next_due_date'])?$b['next_due_date']:'';
		if (!$feeId || $comment==='' || !$next) $this->_json(array('success'=>false,'message'=>'Comment and next due date are required'),422);
		$p=$this->db->get_where('payments',array('id'=>$feeId))->row_array(); if (!$p) $this->_json(array('success'=>false,'message'=>'Fee not found'),404);
		$actor=trim($this->current_user['first_name'].' '.$this->current_user['last_name']); $this->db->insert('fees_remarks',array('fee_id'=>$feeId,'comment'=>$comment,'paid_on_date'=>$next,'add_by'=>$actor,'clear_status'=>'1','date'=>date('Y-m-d H:i:s')));
		$this->_json(array('success'=>true,'message'=>'Comment saved'));
	}
}
