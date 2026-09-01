<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Documents JSON API for React POS shell.
 * Base: /index.php/documentsapi/{method}
 */
class Documentsapi extends CI_Controller {

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
		$this->load->library('documents_service');
		$this->service = $this->documents_service;
		if (!$this->service->can_manage($this->current_user)) {
			$this->_json(array('success' => false, 'message' => 'Documents access required'), 403);
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
		$this->_json(array('success' => true, 'data' => $this->service->meta($this->current_user)));
	}

	public function classes()
	{
		$campus_id = $this->input->get('campus_id');
		$course_id = $this->input->get('course_id');
		$this->_json(array(
			'success' => true,
			'data' => $this->service->classes_for_filter($campus_id ?: null, $course_id ?: null),
		));
	}

	public function how_to_use()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$result = $this->service->how_to_use_add($this->_body(), $this->current_user);
			if (empty($result['success'])) {
				$this->_json($result, 422);
			}
			$this->_json($result);
		}
		$this->_json(array('success' => true, 'data' => $this->service->how_to_use_list()));
	}

	public function how_to_use_delete($id = null)
	{
		$result = $this->service->how_to_use_delete($id);
		if (empty($result['success'])) {
			$this->_json($result, 422);
		}
		$this->_json($result);
	}

	public function council_exams()
	{
		$this->_json(array('success' => true, 'data' => $this->service->council_exam_numbers()));
	}

	public function diploma_search()
	{
		$body = $this->_body();
		$exam = isset($body['council_exam_no']) ? $body['council_exam_no'] : $this->input->get('council_exam_no');
		$campus_id = isset($body['campus_id']) ? $body['campus_id'] : $this->input->get('campus_id');
		$this->_json(array(
			'success' => true,
			'data' => $this->service->diploma_search($exam, $campus_id ?: null),
		));
	}

	public function students_search()
	{
		$body = $this->_body();
		$this->_json(array(
			'success' => true,
			'data' => $this->service->students_search(
				isset($body['campus_id']) && $body['campus_id'] !== '' ? $body['campus_id'] : null,
				isset($body['course_id']) && $body['course_id'] !== '' ? $body['course_id'] : null,
				isset($body['class_id']) && $body['class_id'] !== '' ? $body['class_id'] : null
			),
		));
	}

	public function receipt_pads()
	{
		$this->_json(array('success' => true, 'data' => $this->service->receipt_pad_list()));
	}

	public function receipt_pad_prepare()
	{
		$body = $this->_body();
		$campus_code = isset($body['campus_code']) ? trim($body['campus_code']) : '';
		if ($campus_code === '') {
			$this->_json(array('success' => false, 'message' => 'Campus required'), 422);
		}
		$book = $this->service->next_book_number($campus_code);
		$this->_json(array('success' => true, 'data' => array('book' => $book, 'campus_code' => $campus_code)));
	}

	public function receipt_pad_store()
	{
		$body = $this->_body();
		$campus_code = isset($body['campus_code']) ? trim($body['campus_code']) : '';
		$book = isset($body['book']) ? (int) $body['book'] : 0;
		if ($campus_code === '' || $book <= 0) {
			$this->_json(array('success' => false, 'message' => 'Invalid data'), 422);
		}
		$result = $this->service->store_receipt_pad($campus_code, $book, $this->current_user['user_id']);
		$this->_json($result);
	}
}
