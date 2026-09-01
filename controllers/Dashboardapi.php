<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dashboard JSON API — fee status (paginated) for React dashboard.
 * Base: /index.php/dashboardapi/{method}
 */
class Dashboardapi extends CI_Controller {

	private $current_user = null;
	private $service = null;
	private $details = null;

	public function __construct()
	{
		parent::__construct();
		$this->_cors();
		if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
			http_response_code(204);
			exit;
		}
		$this->current_user = $this->_auth_user();
		if (!$this->current_user) {
			$this->_json(array('success' => false, 'message' => 'Unauthorized'), 401);
		}
		$this->load->library('dashboard_service');
		$this->load->library('dashboard_details_service');
		$this->service = $this->dashboard_service;
		$this->details = $this->dashboard_details_service;
	}

	private function _cors()
	{
		$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
		$allowed = array(
			'http://localhost:5173', 'http://localhost:4173', 'http://127.0.0.1:5173',
			'https://pos.shahbazcollegeofpharmacy.edu.pk', 'http://pos.shahbazcollegeofpharmacy.edu.pk',
		);
		if ($origin === '*' || in_array($origin, $allowed)) {
			header('Access-Control-Allow-Origin: ' . ($origin === '*' ? '*' : $origin));
		} elseif (preg_match('/^https?:\\/\\/(localhost|127\\.0\\.0\\.1)(:\\d+)?$/', $origin)) {
			header('Access-Control-Allow-Origin: ' . $origin);
		}
		header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
		header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Pos-Token');
		header('Access-Control-Allow-Credentials: true');
	}

	private function _json($data, $code = 200)
	{
		http_response_code($code);
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($data);
		exit;
	}

	private function _body()
	{
		$raw = file_get_contents('php://input');
		$json = json_decode($raw, true);
		if (is_array($json) && count($json)) return $json;
		return $this->input->post() ? $this->input->post() : array();
	}

	private function _auth_user()
	{
		$token = isset($_SERVER['HTTP_X_POS_TOKEN']) ? $_SERVER['HTTP_X_POS_TOKEN'] : '';
		if ($token === '' && isset($_SERVER['HTTP_AUTHORIZATION']) && preg_match('/Bearer\\s+(\\S+)/i', $_SERVER['HTTP_AUTHORIZATION'], $m)) {
			$token = $m[1];
		}
		if ($token === '') $token = $this->input->get_request_header('X-Pos-Token', TRUE);
		if ($token === '' || $token === null) $token = $this->input->get('pos_token');
		if (!$token) return null;
		$row = $this->db->get_where('pos_api_tokens', array('token' => $token))->row_array();
		if (!$row || strtotime($row['expires_at']) < time()) return null;
		return $this->db->get_where('users', array('user_id' => $row['user_id'], 'status' => '1'))->row_array();
	}

	private function _require_fee_status()
	{
		if (!$this->service->can_view_fee_status($this->current_user)) {
			$this->_json(array('success' => false, 'message' => 'Fee status access required'), 403);
		}
	}

	private function _require_perm($perm_key)
	{
		$perms = $this->service->permissions($this->current_user);
		if (!$perms['is_admin'] && empty($perms[$perm_key])) {
			$this->_json(array('success' => false, 'message' => 'Access denied'), 403);
		}
	}

	private function _action_result($result)
	{
		if (empty($result['success'])) {
			$this->_json($result, 422);
		}
		$this->_json($result);
	}

	public function meta()
	{
		$this->_json(array('success' => true, 'data' => $this->service->meta($this->current_user)));
	}

	public function clear_procedure()
	{
		$this->_require_fee_status();
		$this->_json(array('success' => true, 'data' => $this->service->clear_procedure($this->current_user)));
	}

	public function fee_status()
	{
		$this->_require_fee_status();
		$campus_id = $this->input->get('campus_id');
		$kind = $this->input->get('kind');
		$page = (int) $this->input->get('page');
		$page_size = (int) $this->input->get('page_size');
		if ($page < 1) $page = 1;
		if ($page_size < 1) $page_size = 25;
		if ($page_size > 5000) $page_size = 5000;
		$filters = array(
			'date_from' => $this->input->get('date_from'),
			'date_to' => $this->input->get('date_to'),
			'date_field' => $this->input->get('date_field'),
			'clear_status' => $this->input->get('clear_status'),
		);
		$this->_json(array(
			'success' => true,
			'data' => $this->service->fee_status_page($this->current_user, $kind, $campus_id, $page, $page_size, $filters),
		));
	}

	public function fee_status_detail($payment_id = null)
	{
		$this->_require_fee_status();
		$data = $this->service->fee_status_detail((int) $payment_id);
		if (!$data) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		$this->_json(array('success' => true, 'data' => $data));
	}

	public function clear_fee()
	{
		$this->_require_fee_status();
		$body = $this->_body();
		$payment_id = isset($body['payment_id']) ? (int) $body['payment_id'] : 0;
		$result = $this->service->clear_fee($this->current_user, $payment_id);
		if (empty($result['success'])) $this->_json($result, 422);
		$this->_json($result);
	}

	public function home_clear_procedure()
	{
		$this->_json(array('success' => true, 'data' => $this->service->home_clear_procedure($this->current_user)));
	}

	public function home_pending_tasks()
	{
		$this->_json(array('success' => true, 'data' => $this->service->home_pending_tasks($this->current_user)));
	}

	public function home_statistics()
	{
		$this->_json(array('success' => true, 'data' => $this->service->home_statistics($this->current_user)));
	}

	public function home_reminders()
	{
		$this->_json(array('success' => true, 'data' => $this->service->home_reminders($this->current_user)));
	}

	public function complete_home_reminder()
	{
		$body = json_decode(file_get_contents('php://input'), true);
		if (!is_array($body)) {
			$body = $this->input->post();
		}
		$reminder_id = isset($body['reminder_id']) ? (int) $body['reminder_id'] : 0;
		$result = $this->service->complete_home_reminder($this->current_user, $reminder_id);
		$code = !empty($result['success']) ? 200 : 400;
		$this->_json($result, $code);
	}

	public function home_lectures()
	{
		$this->_json(array('success' => true, 'data' => $this->service->home_lectures($this->current_user)));
	}

	public function campus_status()
	{
		if (!$this->service->permissions($this->current_user)['campus_status']) {
			$this->_json(array('success' => false, 'message' => 'Campus status access required'), 403);
		}
		$this->_json(array(
			'success' => true,
			'data' => $this->service->campus_status(
				$this->current_user,
				$this->input->get('from_date'),
				$this->input->get('to_date'),
				$this->input->get('date_type')
			),
		));
	}

	public function home()
	{
		$this->_json(array('success' => true, 'data' => $this->service->home($this->current_user)));
	}

	public function new_admission_entries()
	{
		$this->_require_perm('new_admission_entries');
		$page = (int) $this->input->get('page');
		$page_size = (int) $this->input->get('page_size');
		$this->_json(array(
			'success' => true,
			'data' => $this->details->new_admission_entries(
				$this->current_user,
				$this->input->get('campus_id'),
				$page,
				$page_size
			),
		));
	}

	public function clear_new_admission()
	{
		$this->_require_perm('new_admission_entries');
		$body = $this->_body();
		$this->_action_result($this->details->clear_new_admission($this->current_user, isset($body['student_id']) ? $body['student_id'] : 0));
	}

	public function new_expense_entries()
	{
		$this->_require_perm('new_expense_entries');
		$page = (int) $this->input->get('page');
		$page_size = (int) $this->input->get('page_size');
		$this->_json(array(
			'success' => true,
			'data' => $this->details->new_expense_entries(
				$this->current_user,
				$this->input->get('campus_id'),
				$page,
				$page_size
			),
		));
	}

	public function clear_new_expense()
	{
		$this->_require_perm('new_expense_entries');
		$body = $this->_body();
		$this->_action_result($this->details->clear_new_expense($this->current_user, isset($body['expense_id']) ? $body['expense_id'] : 0));
	}

	public function update_fee_requests()
	{
		$this->_require_perm('update_fee_requests');
		$this->_json(array(
			'success' => true,
			'data' => $this->details->update_fee_requests($this->current_user, $this->input->get('campus_id'), $this->input->get('kind') ?: 'student'),
		));
	}

	public function approve_fee_update()
	{
		$this->_require_perm('update_fee_requests');
		$body = $this->_body();
		$this->_action_result($this->details->approve_fee_update($this->current_user, isset($body['request_id']) ? $body['request_id'] : 0));
	}

	public function reject_fee_update()
	{
		$this->_require_perm('update_fee_requests');
		$body = $this->_body();
		$this->_action_result($this->details->reject_fee_update($this->current_user, isset($body['request_id']) ? $body['request_id'] : 0));
	}

	public function discount_requests()
	{
		$this->_require_perm('discount_requests');
		$this->_json(array('success' => true, 'data' => $this->details->discount_requests($this->current_user, $this->input->get('campus_id'))));
	}

	public function approve_discount()
	{
		$this->_require_perm('discount_requests');
		$body = $this->_body();
		$this->_action_result($this->details->approve_discount($this->current_user, isset($body['id']) ? $body['id'] : 0));
	}

	public function reject_discount()
	{
		$this->_require_perm('discount_requests');
		$body = $this->_body();
		$this->_action_result($this->details->reject_discount($this->current_user, isset($body['id']) ? $body['id'] : 0));
	}

	public function student_edit_requests()
	{
		$this->_require_perm('update_student_requests');
		$this->_json(array('success' => true, 'data' => $this->details->student_edit_requests($this->current_user, $this->input->get('campus_id'))));
	}

	public function approve_student_update()
	{
		$this->_require_perm('update_student_requests');
		$body = $this->_body();
		$this->_action_result($this->details->approve_student_update($this->current_user, isset($body['student_id']) ? $body['student_id'] : 0));
	}

	public function reject_student_update()
	{
		$this->_require_perm('update_student_requests');
		$body = $this->_body();
		$this->_action_result($this->details->reject_student_update($this->current_user, isset($body['student_id']) ? $body['student_id'] : 0));
	}

	public function pending_questions()
	{
		$this->_require_perm('test_engine_questions');
		$this->_json(array('success' => true, 'data' => $this->details->pending_questions($this->current_user)));
	}

	public function approve_question()
	{
		$this->_require_perm('test_engine_questions');
		$body = $this->_body();
		$this->_action_result($this->details->approve_question($this->current_user, isset($body['question_id']) ? $body['question_id'] : 0));
	}

	public function delete_question()
	{
		$this->_require_perm('test_engine_questions');
		$body = $this->_body();
		$this->_action_result($this->details->delete_question($this->current_user, isset($body['question_id']) ? $body['question_id'] : 0));
	}

	public function uncheck_assignments()
	{
		$this->_require_perm('uncheck_assignments');
		$this->_json(array('success' => true, 'data' => $this->details->uncheck_assignments($this->current_user)));
	}

	public function fee_reversal_requests()
	{
		$this->_require_perm('fee_reversal_requests');
		$this->_json(array(
			'success' => true,
			'data' => $this->details->fee_reversal_requests($this->current_user, $this->input->get('from_date'), $this->input->get('to_date')),
		));
	}

	public function approve_fee_reversal()
	{
		$this->_require_perm('fee_reversal_requests');
		$body = $this->_body();
		$this->_action_result($this->details->approve_fee_reversal($this->current_user, isset($body['id']) ? $body['id'] : 0, isset($body['approve_status']) ? $body['approve_status'] : 1));
	}

	public function delete_fee_reversal()
	{
		$this->_require_perm('fee_reversal_requests');
		$body = $this->_body();
		$this->_action_result($this->details->delete_fee_reversal($this->current_user, isset($body['id']) ? $body['id'] : 0));
	}

	public function struck_off_list()
	{
		$mode = $this->input->get('mode') === 'final' ? 'final' : 'inquiry';
		if ($mode === 'final') {
			$this->_require_perm('struck_off_final');
		} else {
			$this->_require_perm('struck_off_inquiry');
		}
		$this->_json(array('success' => true, 'data' => $this->details->struck_off_list($this->current_user, $mode)));
	}

	public function new_admissions_report()
	{
		$this->_require_perm('new_admissions_month');
		$this->_json(array(
			'success' => true,
			'data' => $this->details->new_admissions_report($this->current_user, $this->input->get('month'), $this->input->get('year')),
		));
	}

	public function submit_fees_report()
	{
		$this->_require_perm('month_earning');
		$this->_json(array(
			'success' => true,
			'data' => $this->details->submit_fees_report($this->current_user, $this->input->get('start_date'), $this->input->get('end_date'), $this->input->get('date_type') ?: 'actual_paid_date'),
		));
	}

	public function expenses_report()
	{
		$this->_require_perm('month_expense');
		$this->_json(array(
			'success' => true,
			'data' => $this->details->expenses_report($this->current_user, $this->input->get('start_date'), $this->input->get('end_date'), $this->input->get('date_type') ?: 'actual_date'),
		));
	}

	public function profit_report()
	{
		$this->_require_perm('month_profit');
		$this->_json(array(
			'success' => true,
			'data' => $this->details->profit_report($this->current_user, $this->input->get('start_date'), $this->input->get('end_date'), $this->input->get('date_type') ?: 'date'),
		));
	}

	public function classes_status_report()
	{
		$this->_require_perm('classes_status');
		$this->_json(array('success' => true, 'data' => $this->details->classes_status_report($this->current_user)));
	}
}
