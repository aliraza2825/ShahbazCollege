<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Campuses JSON API for React POS shell.
 * Base: /index.php/campusesapi/{method}
 */
class Campusesapi extends CI_Controller {

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
		$this->load->library('campus_service');
		$this->service = $this->campus_service;
		if (!$this->service->can_manage($this->current_user)) {
			$this->_json(array('success' => false, 'message' => 'Campuses access required'), 403);
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

	public function index()
	{
		$this->_json(array('success' => true, 'data' => $this->service->list_active()));
	}

	public function campus($id = 0)
	{
		$id = (int) $id;
		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			$row = $this->service->get($id);
			if (!$row) {
				$this->_json(array('success' => false, 'message' => 'Not found'), 404);
			}
			$this->_json(array('success' => true, 'data' => $row));
		}
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$result = $this->service->update_from_request($id, $_POST);
			if (empty($result['success'])) {
				$this->_json($result, 422);
			}
			$this->_json($result);
		}
		if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
			$result = $this->service->soft_delete($id);
			if (empty($result['success'])) {
				$this->_json($result, 422);
			}
			$this->_json($result);
		}
		$this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
	}

	public function create()
	{
		$result = $this->service->create_from_request($_POST);
		if (empty($result['success'])) {
			$this->_json($result, 422);
		}
		$this->_json($result);
	}

	public function delete_campus($id = 0)
	{
		$result = $this->service->soft_delete($id);
		if (empty($result['success'])) {
			$this->_json($result, 422);
		}
		$this->_json($result);
	}

	public function profit_meta()
	{
		$this->_json(array('success' => true, 'data' => $this->service->profit_meta()));
	}

	public function partners()
	{
		$this->_json(array('success' => true, 'data' => $this->service->list_partners()));
	}

	public function partner($id = 0)
	{
		$id = (int) $id;
		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			$row = $this->service->get_partner($id);
			if (!$row) {
				$this->_json(array('success' => false, 'message' => 'Not found'), 404);
			}
			$this->_json(array('success' => true, 'data' => $row));
		}
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$result = $this->service->update_partner($id, $this->_body());
			if (empty($result['success'])) {
				$this->_json($result, 422);
			}
			$this->_json($result);
		}
		$this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
	}

	public function create_partner()
	{
		$result = $this->service->create_partner($this->_body());
		if (empty($result['success'])) {
			$this->_json($result, 422);
		}
		$this->_json($result);
	}

	public function delete_partner($id = 0)
	{
		$result = $this->service->delete_partner($id);
		if (empty($result['success'])) {
			$this->_json($result, 422);
		}
		$this->_json($result);
	}

	public function rooms()
	{
		$campus_id = (int) $this->input->get('campus_id');
		$this->_json(array('success' => true, 'data' => $this->service->list_rooms($campus_id)));
	}

	public function room($id = 0)
	{
		$id = (int) $id;
		$row = $this->service->get_room($id);
		if (!$row) {
			$this->_json(array('success' => false, 'message' => 'Not found'), 404);
		}
		$this->_json(array('success' => true, 'data' => $row));
	}

	public function save_rooms()
	{
		$result = $this->service->save_rooms($this->_body());
		if (empty($result['success'])) {
			$this->_json($result, 422);
		}
		$this->_json($result);
	}

	public function delete_room($id = 0)
	{
		$result = $this->service->delete_room($id);
		if (empty($result['success'])) {
			$this->_json($result, 422);
		}
		$this->_json($result);
	}

	public function subrooms()
	{
		$campus_id = (int) $this->input->get('campus_id');
		$room_id = (int) $this->input->get('room_id');
		$this->_json(array('success' => true, 'data' => $this->service->list_subrooms($campus_id, $room_id)));
	}

	public function save_subroom()
	{
		$result = $this->service->save_subroom($this->_body());
		if (empty($result['success'])) {
			$this->_json($result, 422);
		}
		$this->_json($result);
	}

	public function delete_subroom($id = 0)
	{
		$result = $this->service->delete_subroom($id);
		if (empty($result['success'])) {
			$this->_json($result, 422);
		}
		$this->_json($result);
	}
}
