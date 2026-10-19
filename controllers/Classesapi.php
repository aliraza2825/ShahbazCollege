<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Classes JSON API for React POS shell.
 * Base: /index.php/classesapi/{method}
 */
class Classesapi extends CI_Controller {

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
		$this->load->library('class_service');
		$this->service = $this->class_service;
		if (!$this->service->can_manage($this->current_user)) {
			$this->_json(array('success' => false, 'message' => 'Classes access required'), 403);
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

	private function _actor_name()
	{
		$name = trim($this->current_user['first_name'] . ' ' . $this->current_user['last_name']);
		return $name !== '' ? $name : 'POS';
	}

	public function index()
	{
		$this->_json(array('success' => true, 'data' => $this->service->list_all($this->current_user)));
	}

	public function meta()
	{
		$this->_json(array('success' => true, 'data' => $this->service->meta($this->current_user)));
	}

	public function courses()
	{
		$campus_id = (int) $this->input->get('campus_id');
		$this->_json(array('success' => true, 'data' => $this->service->courses_for_campus($campus_id)));
	}

	public function sessions()
	{
		$course_id = (int) $this->input->get('course_id');
		$this->_json(array('success' => true, 'data' => $this->service->sessions_for_course($course_id)));
	}

	public function session_detail()
	{
		$course_id = (int) $this->input->get('course_id');
		$session = $this->input->get('session');
		$row = $this->service->session_detail($course_id, $session);
		if (!$row) {
			$this->_json(array('success' => false, 'message' => 'Session not found'), 404);
		}
		$this->_json(array('success' => true, 'data' => $row));
	}

	public function class_item($id = 0)
	{
		$id = (int) $id;
		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			$row = $this->service->get($id, $this->current_user);
			if (!$row) {
				$this->_json(array('success' => false, 'message' => 'Not found'), 404);
			}
			$this->_json(array('success' => true, 'data' => $row));
		}
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$result = $this->service->update($id, $this->current_user, $this->_body());
			if (empty($result['success'])) {
				$this->_json($result, 422);
			}
			$this->_json($result);
		}
		$this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
	}

	public function create()
	{
		$result = $this->service->create($this->current_user, $this->_body());
		if (empty($result['success'])) {
			$this->_json($result, 422);
		}
		$this->_json($result);
	}

	public function delete_class($id = 0)
	{
		$result = $this->service->delete($id, $this->current_user, $this->_actor_name());
		if (empty($result['success'])) {
			$this->_json($result, 422);
		}
		$this->_json($result);
	}

	/** Local camera pilot. Uses the same login and class permissions as this API. */
	public function camera($action = 'status')
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			$this->_json(array('success' => false, 'message' => 'POST required'), 405);
		}
		if (!in_array(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '', array('127.0.0.1', '::1'))) {
			$this->_json(array('success' => false, 'message' => 'Camera pilot is available on the local computer only'), 403);
		}
		if (!in_array($action, array('status', 'frame', 'start', 'stop', 'enroll', 'forget', 'new_session'))) {
			$this->_json(array('success' => false, 'message' => 'Unknown camera action'), 404);
		}
		$body = $this->_body();
		$current = $this->_camera_request($action === 'frame' ? 'frame' : 'status', array());
		if (!empty($current['class_id']) && !$this->service->get((int)$current['class_id'], $this->current_user)) {
			$this->_json(array('success' => false, 'message' => 'Access to the active camera class is required'), 403);
		}
		if ($action === 'frame') {
			header('Cache-Control: no-store');
			if (empty($current['jpeg'])) { http_response_code(204); exit; }
			header('Content-Type: image/jpeg');
			header('X-Frame-Sequence: ' . (int)$current['sequence']);
			echo base64_decode($current['jpeg']);
			exit;
		}
		if ($action === 'start') {
			$class_id = isset($body['class_id']) ? (int)$body['class_id'] : 0;
			$roster = array();
			$class_name = 'Personal camera test';
			if ($class_id > 0) {
				$pack = $this->service->students_for_class($class_id, $this->current_user);
				if (!$pack) $this->_json(array('success' => false, 'message' => 'Class not found'), 404);
				$class_name = $pack['class']['name'];
				foreach ($pack['students'] as $s) {
					$roster[] = array('id' => (int)$s['student_id'], 'name' => trim($s['first_name'] . ' ' . $s['last_name']), 'roll' => isset($s['roll_no']) ? (string)$s['roll_no'] : (string)$s['student_id']);
				}
			} else {
				$name = isset($body['test_name']) ? trim((string)$body['test_name']) : '';
				if ($name === '' || strlen($name) > 80) $this->_json(array('success' => false, 'message' => 'Enter a test name (maximum 80 characters)'), 422);
				$roster[] = array('id' => (int)$this->current_user['user_id'], 'name' => $name, 'roll' => 'SELF-TEST');
			}
			$body = array('class_id' => $class_id, 'class_name' => $class_name, 'roster' => $roster, 'source' => isset($body['source']) ? (string)$body['source'] : '0', 'new_session' => !empty($body['new_session']));
		}
		$this->_json($this->_camera_request($action, $body));
	}

	private function _camera_request($action, $body)
	{
		$dir = getenv('ATTENDANCE_CAMERA_DATA');
		if (!$dir) $dir = getenv('HOME') . '/Library/Application Support/AttendanceCamera';
		$keyfile = $dir . '/service.token';
		if (!is_readable($keyfile)) $this->_json(array('success' => false, 'message' => 'Background camera service is not installed or its key is unavailable'), 503);
		$ch = curl_init('http://127.0.0.1:8765/' . $action);
		curl_setopt_array($ch, array(CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 2, CURLOPT_TIMEOUT => 6, CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'X-Camera-Key: ' . trim(file_get_contents($keyfile))), CURLOPT_POSTFIELDS => json_encode((object)$body)));
		$raw = curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		$result = json_decode($raw, true);
		if (!$result) $this->_json(array('success' => false, 'message' => 'Background camera service is offline. Start the local service and retry.'), 503);
		if ($code >= 400) $this->_json($result, $code);
		return $result;
	}

	public function class_students($class_id = 0)
	{
		$class_id = (int) $class_id;
		$pack = $this->service->students_for_class($class_id, $this->current_user);
		if (!$pack) {
			$this->_json(array('success' => false, 'message' => 'Class not found'), 404);
		}
		$this->_json(array(
			'success' => true,
			'class' => $pack['class'],
			'data' => $pack['students'],
			'count' => count($pack['students']),
		));
	}

	public function class_attendance($class_id = 0)
	{
		$class_id = (int) $class_id;
		$start = $this->input->get('start_date');
		$end = $this->input->get('end_date');
		$pack = $this->service->attendance_for_class($class_id, $this->current_user, $start, $end);
		if (!$pack) {
			$this->_json(array('success' => false, 'message' => 'Class not found'), 404);
		}
		$this->_json(array_merge(array('success' => true), $pack));
	}
}
