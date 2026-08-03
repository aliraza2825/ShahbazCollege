<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Council List JSON API for React POS shell.
 * Base: /index.php/councillistapi/{method}
 */
class Councillistapi extends CI_Controller {

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
		$this->load->library('council_list_service');
		$this->service = $this->council_list_service;
		if (!$this->service->can_manage($this->current_user)) {
			$this->_json(array('success' => false, 'message' => 'Council List access required'), 403);
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

	public function classes()
	{
		$campus_id = $this->input->get('campus_id');
		$course_id = $this->input->get('course_id');
		$this->_json(array(
			'success' => true,
			'data' => $this->service->classes_for_filter($this->current_user, $campus_id, $course_id),
		));
	}

	public function students()
	{
		$class_id = $this->input->get('class_id');
		$this->_json(array('success' => true, 'data' => $this->service->students_for_class($class_id)));
	}

	public function create_export()
	{
		$this->_require_perm('create_council_list');
		$body = $this->_body();
		$class_id = isset($body['class_id']) ? $body['class_id'] : '';
		$result = $this->service->create_list_export($class_id);
		if (empty($result['success'])) {
			$this->_json($result, 422);
		}
		$this->_json($result);
	}

	public function fee_export_start()
	{
		$this->_require_perm('create_council_list_with_fee');
		$body = $this->_body();
		$result = $this->service->start_fee_export(
			isset($body['class_id']) ? $body['class_id'] : '',
			isset($body['course_id']) ? $body['course_id'] : '',
			isset($body['campus_id']) ? $body['campus_id'] : ''
		);
		if (empty($result['success'])) {
			$this->_json($result, 422);
		}
		$this->_json($result);
	}

	public function fee_export_process()
	{
		$this->_require_perm('create_council_list_with_fee');
		$body = $this->_body();
		$token = isset($body['token']) ? $body['token'] : '';
		$result = $this->service->process_fee_export($token);
		if (empty($result['success'])) {
			$this->_json($result, 422);
		}
		$this->_json($result);
	}

	public function fee_export_download($token = '')
	{
		$this->_require_perm('create_council_list_with_fee');
		$info = $this->service->fee_export_download_info($token);
		if (!$info) {
			show_error('Export is not ready yet.', 404);
			return;
		}
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="'.$info['download_name'].'"');
		header('Content-Length: ' . filesize($info['file_path']));
		header('Pragma: no-cache');
		header('Expires: 0');
		readfile($info['file_path']);
		exit;
	}
}
