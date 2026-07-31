<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mobile App JSON API for React POS shell.
 * Base: /index.php/mobileappapi/{method}
 */
class Mobileappapi extends CI_Controller {

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
		$this->load->library('mobile_app_api_service');
		$this->service = $this->mobile_app_api_service;
		if (!$this->service->can_manage($this->current_user)) {
			$this->_json(array('success' => false, 'message' => 'Mobile App access required'), 403);
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
			'data' => array(
				'designations' => $this->service->designations_list(),
				'courses' => $this->service->courses_list(),
				'image_types' => array('advertisement', 'gallery', 'future_plan', 'noticeboard', 'downloads', 'news', 'promotion', 'slider', 'how_to_use'),
			),
		));
	}

	public function campuses()
	{
		$this->_json(array('success' => true, 'data' => $this->service->campuses_list()));
	}

	public function campus($campus_id = null)
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
			$result = $this->service->campus_update($campus_id, $this->_body());
			if (empty($result['success'])) {
				$this->_json($result, 422);
			}
			$this->_json($result);
		}
		$row = $this->service->campus_get($campus_id);
		if (!$row) {
			$this->_json(array('success' => false, 'message' => 'Not found'), 404);
		}
		$this->_json(array('success' => true, 'data' => $row));
	}

	public function courses()
	{
		$this->_json(array('success' => true, 'data' => $this->service->courses_list()));
	}

	public function course($course_id = null)
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
			$result = $this->service->course_update($course_id, $this->_body());
			if (empty($result['success'])) {
				$this->_json($result, 422);
			}
			$this->_json($result);
		}
		$row = $this->service->course_get($course_id);
		if (!$row) {
			$this->_json(array('success' => false, 'message' => 'Not found'), 404);
		}
		$this->_json(array('success' => true, 'data' => $row));
	}

	public function images()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$result = $this->service->image_add($this->_body());
			if (empty($result['success'])) {
				$this->_json($result, 422);
			}
			$this->_json($result);
		}
		$this->_json(array('success' => true, 'data' => $this->service->images_list()));
	}

	public function image_delete($id = null)
	{
		$this->_json($this->service->image_delete($id));
	}

	public function news()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$result = $this->service->news_save($this->_body());
			if (empty($result['success'])) {
				$this->_json($result, 422);
			}
			$this->_json($result);
		}
		$this->_json(array('success' => true, 'data' => $this->service->news_list()));
	}

	public function news_item($news_id = null)
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
			$result = $this->service->news_save($this->_body(), $news_id);
			if (empty($result['success'])) {
				$this->_json($result, 422);
			}
			$this->_json($result);
		}
		if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
			$this->_json($this->service->news_delete($news_id));
		}
		$row = $this->service->news_get($news_id);
		if (!$row) {
			$this->_json(array('success' => false, 'message' => 'Not found'), 404);
		}
		$this->_json(array('success' => true, 'data' => $row));
	}

	public function complaint_types()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$result = $this->service->complaint_type_save($this->_body());
			if (empty($result['success'])) {
				$this->_json($result, 422);
			}
			$this->_json($result);
		}
		$this->_json(array('success' => true, 'data' => $this->service->complaint_types_list()));
	}

	public function complaint_type($id = null)
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
			$result = $this->service->complaint_type_save($this->_body(), $id);
			if (empty($result['success'])) {
				$this->_json($result, 422);
			}
			$this->_json($result);
		}
		if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
			$this->_json($this->service->complaint_type_delete($id));
		}
		$this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
	}

	public function required_careers()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$result = $this->service->required_career_save($this->_body());
			if (empty($result['success'])) {
				$this->_json($result, 422);
			}
			$this->_json($result);
		}
		$this->_json(array('success' => true, 'data' => $this->service->required_careers_list()));
	}

	public function required_career($id = null)
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
			$result = $this->service->required_career_save($this->_body(), $id);
			if (empty($result['success'])) {
				$this->_json($result, 422);
			}
			$this->_json($result);
		}
		if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
			$this->_json($this->service->required_career_delete($id));
		}
		$this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
	}
}
