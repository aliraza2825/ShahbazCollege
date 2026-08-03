<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Reminders JSON API for React POS shell.
 * Base: /index.php/remindersapi/{method}
 */
class Remindersapi extends CI_Controller {

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
		$this->load->library('reminders_service');
		$this->service = $this->reminders_service;
		if (!$this->service->can_manage($this->current_user)) {
			$this->_json(array('success' => false, 'message' => 'Reminders access required'), 403);
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

	private function _require_perm($key)
	{
		$perms = $this->service->permissions($this->current_user);
		if (empty($perms[$key])) {
			$this->_json(array('success' => false, 'message' => 'Permission denied'), 403);
		}
	}

	public function meta()
	{
		$this->_json(array('success' => true, 'data' => $this->service->meta($this->current_user)));
	}

	public function campus_users()
	{
		$campus_id = $this->input->get('campus_id');
		$this->_json(array('success' => true, 'data' => $this->service->campus_users($campus_id)));
	}

	public function rules()
	{
		$this->_require_perm('all_rules');
		$this->_json(array('success' => true, 'data' => $this->service->rules_list()));
	}

	public function pending()
	{
		$this->_require_perm('all_pending');
		$campus_id = $this->input->get('campus_id');
		$this->_json(array('success' => true, 'data' => $this->service->pending_list($this->current_user, $campus_id)));
	}

	public function completed()
	{
		$this->_require_perm('all_completed');
		$campus_id = $this->input->get('campus_id');
		$this->_json(array('success' => true, 'data' => $this->service->completed_list($this->current_user, $campus_id)));
	}

	public function insert()
	{
		$this->_require_perm('add_rules');
		$result = $this->service->insert_reminder($this->current_user);
		if (empty($result['success'])) {
			$this->_json($result, 422);
		}
		$this->_json($result);
	}

	public function delete_rule($id = null)
	{
		$this->_require_perm('all_rules');
		$result = $this->service->delete_rule($id);
		if (empty($result['success'])) $this->_json($result, 422);
		$this->_json($result);
	}

	public function delete_instance($id = null)
	{
		$this->_require_perm('all_pending');
		$result = $this->service->delete_instance($id);
		if (empty($result['success'])) $this->_json($result, 422);
		$this->_json($result);
	}

	public function approve($id = null)
	{
		$this->_require_perm('all_pending');
		$result = $this->service->approve_instance($id);
		if (empty($result['success'])) $this->_json($result, 422);
		$this->_json($result);
	}
}
