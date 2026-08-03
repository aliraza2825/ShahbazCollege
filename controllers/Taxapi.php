<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Tax JSON API for React POS shell.
 * Base: /index.php/taxapi/{method}
 */
class Taxapi extends CI_Controller {

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
		$this->load->library('tax_service');
		$this->service = $this->tax_service;
		if (!$this->service->can_manage($this->current_user)) {
			$this->_json(array('success' => false, 'message' => 'Tax access required'), 403);
		}
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
		if (is_array($json) && count($json)) {
			return $json;
		}
		return $this->input->post() ? $this->input->post() : array();
	}

	private function _auth_user()
	{
		$token = isset($_SERVER['HTTP_X_POS_TOKEN']) ? $_SERVER['HTTP_X_POS_TOKEN'] : '';
		if ($token === '' && isset($_SERVER['HTTP_AUTHORIZATION']) && preg_match('/Bearer\\s+(\\S+)/i', $_SERVER['HTTP_AUTHORIZATION'], $m)) {
			$token = $m[1];
		}
		if ($token === '') {
			$token = $this->input->get_request_header('X-Pos-Token', TRUE);
		}
		if ($token === '' || $token === null) {
			$token = $this->input->get('pos_token');
		}
		if (!$token) {
			return null;
		}
		$row = $this->db->get_where('pos_api_tokens', array('token' => $token))->row_array();
		if (!$row || strtotime($row['expires_at']) < time()) {
			return null;
		}
		return $this->db->get_where('users', array('user_id' => $row['user_id'], 'status' => '1'))->row_array();
	}

	public function meta()
	{
		$this->_json(array('success' => true, 'data' => $this->service->meta()));
	}

	public function bank_report()
	{
		$body = $this->_body();
		$result = $this->service->bank_report(
			isset($body['from_date']) ? $body['from_date'] : '',
			isset($body['to_date']) ? $body['to_date'] : '',
			isset($body['account_ids']) ? $body['account_ids'] : array()
		);
		if (empty($result['success'])) {
			$this->_json($result, 422);
		}
		$this->_json($result);
	}

	public function expense_college_report()
	{
		$body = $this->_body();
		$result = $this->service->expense_college_report(
			isset($body['from_date']) ? $body['from_date'] : '',
			isset($body['to_date']) ? $body['to_date'] : '',
			isset($body['campus_ids']) ? $body['campus_ids'] : array(),
			isset($body['category_ids']) ? $body['category_ids'] : array()
		);
		if (empty($result['success'])) {
			$this->_json($result, 422);
		}
		$this->_json($result);
	}

	public function expense_headwise_report()
	{
		$body = $this->_body();
		$result = $this->service->expense_headwise_report(
			isset($body['from_date']) ? $body['from_date'] : '',
			isset($body['to_date']) ? $body['to_date'] : '',
			isset($body['campus_ids']) ? $body['campus_ids'] : array(),
			isset($body['date_type']) ? $body['date_type'] : 'actual_date',
			isset($body['campus_id']) ? $body['campus_id'] : null,
			isset($body['category_ids']) ? $body['category_ids'] : null
		);
		if (empty($result['success'])) {
			$this->_json($result, 422);
		}
		$this->_json($result);
	}

	public function tax_paid()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$result = $this->service->tax_paid_add($this->_body());
			if (empty($result['success'])) {
				$this->_json($result, 422);
			}
			$this->_json($result);
		}
		$this->_json(array('success' => true, 'data' => $this->service->tax_paid_list()));
	}

	public function tax_paid_delete($id = null)
	{
		$this->_json($this->service->tax_paid_delete($id));
	}
}
