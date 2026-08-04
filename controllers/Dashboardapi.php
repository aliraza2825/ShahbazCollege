<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dashboard JSON API — fee status (paginated) for React dashboard.
 * Base: /index.php/dashboardapi/{method}
 */
class Dashboardapi extends CI_Controller {

	private $current_user = null;
	private $service = null;

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
		$this->service = $this->dashboard_service;
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
		$this->_json(array(
			'success' => true,
			'data' => $this->service->fee_status_page($this->current_user, $kind, $campus_id, $page, $page_size),
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
}
