<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Access management JSON API for React POS shell.
 * Base: /index.php/accessapi/{method}
 * Auth: X-Pos-Token — Admin only
 */
class Accessapi extends CI_Controller {

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
		$this->load->library('access_service');
		$this->service = $this->access_service;
		if (!$this->service->assert_admin($this->current_user)) {
			$this->_json(array('success' => false, 'message' => 'Admin access required'), 403);
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
		$this->_json(array(
			'success' => true,
			'meta' => $this->service->get_meta(),
			'schema' => $this->service->parse_schema(),
		));
	}

	public function schema()
	{
		$this->_json(array('success' => true, 'schema' => $this->service->parse_schema()));
	}

	public function users()
	{
		$campus_id = $this->input->get('campus_id');
		if ($campus_id === null || $campus_id === '') {
			$this->_json(array('success' => false, 'message' => 'campus_id required'), 422);
		}
		$this->_json(array('success' => true, 'data' => $this->service->get_users($campus_id)));
	}

	public function designations()
	{
		$department_id = $this->input->get('department_id');
		if ($department_id === null || $department_id === '') {
			$this->_json(array('success' => false, 'message' => 'department_id required'), 422);
		}
		$this->_json(array('success' => true, 'data' => $this->service->get_designations($department_id)));
	}

	public function load_user($user_id = 0)
	{
		$data = $this->service->load_user($user_id);
		if (!$data) {
			$this->_json(array('success' => false, 'message' => 'User not found'), 404);
		}
		$this->_json(array_merge(array('success' => true), $data));
	}

	public function load_designation($designation_id = 0)
	{
		$data = $this->service->load_designation($designation_id);
		if (!$data) {
			$this->_json(array('success' => false, 'message' => 'Designation not found'), 404);
		}
		$this->_json(array_merge(array('success' => true), $data));
	}

	public function save()
	{
		$result = $this->service->save_from_body($this->_body());
		if (empty($result['success'])) {
			$this->_json($result, 422);
		}
		$this->_json($result);
	}
}
