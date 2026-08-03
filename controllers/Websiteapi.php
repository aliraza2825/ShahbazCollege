<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Website JSON API for React POS shell.
 * Base: /index.php/websiteapi/{method}
 */
class Websiteapi extends CI_Controller {

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
		$this->load->library('website_service');
		$this->service = $this->website_service;
		if (!$this->service->can_manage($this->current_user)) {
			$this->_json(array('success' => false, 'message' => 'Website access required'), 403);
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

	private function _fail($result)
	{
		$this->_json($result, 422);
	}

	public function meta()
	{
		$this->_json(array('success' => true, 'data' => $this->service->meta()));
	}

	public function how_to_use()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$result = $this->service->how_to_use_add($this->current_user);
			if (empty($result['success'])) {
				$this->_fail($result);
			}
			$this->_json($result);
		}
		$this->_json(array('success' => true, 'data' => $this->service->how_to_use_list()));
	}

	public function how_to_use_delete($id = null)
	{
		$result = $this->service->how_to_use_delete($id);
		if (empty($result['success'])) {
			$this->_fail($result);
		}
		$this->_json($result);
	}

	public function downloads()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$result = $this->service->download_add($this->_body());
			if (empty($result['success'])) {
				$this->_fail($result);
			}
			$this->_json($result);
		}
		$this->_json(array('success' => true, 'data' => $this->service->downloads_list($this->current_user)));
	}

	public function download_delete($id = null)
	{
		$result = $this->service->download_delete($id, $this->current_user);
		if (empty($result['success'])) {
			$this->_fail($result);
		}
		$this->_json($result);
	}

	public function events()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$result = $this->service->event_save($this->_body());
			if (empty($result['success'])) {
				$this->_fail($result);
			}
			$this->_json($result);
		}
		$this->_json(array('success' => true, 'data' => $this->service->events_list($this->current_user)));
	}

	public function event($id = null)
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$result = $this->service->event_save($this->_body(), (int) $id);
			if (empty($result['success'])) {
				$this->_fail($result);
			}
			$this->_json($result);
		}
		$row = $this->service->event_get($id);
		if (!$row) {
			$this->_json(array('success' => false, 'message' => 'Not found'), 404);
		}
		$this->_json(array('success' => true, 'data' => $row));
	}

	public function event_delete($id = null)
	{
		$result = $this->service->event_delete($id, $this->current_user);
		if (empty($result['success'])) {
			$this->_fail($result);
		}
		$this->_json($result);
	}

	public function event_images()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$result = $this->service->event_image_add($this->_body());
			if (empty($result['success'])) {
				$this->_fail($result);
			}
			$this->_json($result);
		}
		$this->_json(array('success' => true, 'data' => $this->service->event_images_list($this->current_user)));
	}

	public function event_image_delete($id = null)
	{
		$result = $this->service->event_image_delete($id);
		if (empty($result['success'])) {
			$this->_fail($result);
		}
		$this->_json($result);
	}

	public function slider_images()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$result = $this->service->slider_save($this->_body());
			if (empty($result['success'])) {
				$this->_fail($result);
			}
			$this->_json($result);
		}
		$this->_json(array('success' => true, 'data' => $this->service->slider_images_list($this->current_user)));
	}

	public function slider($id = null)
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$result = $this->service->slider_save($this->_body(), (int) $id);
			if (empty($result['success'])) {
				$this->_fail($result);
			}
			$this->_json($result);
		}
		$row = $this->service->slider_get($id);
		if (!$row) {
			$this->_json(array('success' => false, 'message' => 'Not found'), 404);
		}
		$this->_json(array('success' => true, 'data' => $row));
	}

	public function slider_delete($id = null)
	{
		$result = $this->service->slider_delete($id, $this->current_user);
		if (empty($result['success'])) {
			$this->_fail($result);
		}
		$this->_json($result);
	}

	public function website_news()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$result = $this->service->website_news_add($this->_body());
			if (empty($result['success'])) {
				$this->_fail($result);
			}
			$this->_json($result);
		}
		$this->_json(array('success' => true, 'data' => $this->service->website_news_list($this->current_user)));
	}

	public function website_news_delete($id = null)
	{
		$result = $this->service->website_news_delete($id, $this->current_user);
		if (empty($result['success'])) {
			$this->_fail($result);
		}
		$this->_json($result);
	}

	public function faqs()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$result = $this->service->faq_add($this->_body());
			if (empty($result['success'])) {
				$this->_fail($result);
			}
			$this->_json($result);
		}
		$this->_json(array('success' => true, 'data' => $this->service->faqs_list()));
	}

	public function videos()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$result = $this->service->video_add($this->_body());
			if (empty($result['success'])) {
				$this->_fail($result);
			}
			$this->_json($result);
		}
		$this->_json(array('success' => true, 'data' => $this->service->videos_list()));
	}

	public function video_delete($id = null)
	{
		$result = $this->service->video_delete($id);
		if (empty($result['success'])) {
			$this->_fail($result);
		}
		$this->_json($result);
	}

	public function home_page()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$result = $this->service->home_page_save($this->_body());
			if (empty($result['success'])) {
				$this->_fail($result);
			}
			$this->_json($result);
		}
		$this->_json(array('success' => true, 'data' => $this->service->home_page_list()));
	}

	public function home_page_item($id = null)
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$result = $this->service->home_page_save($this->_body(), (int) $id);
			if (empty($result['success'])) {
				$this->_fail($result);
			}
			$this->_json($result);
		}
		$row = $this->service->home_page_get($id);
		if (!$row) {
			$this->_json(array('success' => false, 'message' => 'Not found'), 404);
		}
		$this->_json(array('success' => true, 'data' => $row));
	}

	public function zoom()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$result = $this->service->zoom_update($this->_body());
			if (empty($result['success'])) {
				$this->_fail($result);
			}
			$this->_json($result);
		}
		$this->_json(array('success' => true, 'data' => $this->service->zoom_list()));
	}
}
