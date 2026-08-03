<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Chats JSON API for React POS shell.
 * Base: /index.php/chatsapi/{method}
 */
class Chatsapi extends CI_Controller {

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
		$this->load->library('chats_service');
		$this->service = $this->chats_service;
		if (!$this->service->can_manage($this->current_user)) {
			$this->_json(array('success' => false, 'message' => 'Chats access required'), 403);
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

	public function pending()
	{
		$pack = $this->service->list_by_status(0, $this->_page(), $this->_page_size());
		$this->_json(array('success' => true, 'data' => $pack['rows'], 'pagination' => $pack['pagination']));
	}

	public function completed()
	{
		$pack = $this->service->list_by_status(1, $this->_page(), $this->_page_size());
		$this->_json(array('success' => true, 'data' => $pack['rows'], 'pagination' => $pack['pagination']));
	}

	private function _page()
	{
		$page = (int) $this->input->get('page');
		return $page > 0 ? $page : 1;
	}

	private function _page_size()
	{
		$page_size = (int) $this->input->get('page_size');
		return $page_size > 0 ? $page_size : 25;
	}

	public function detail($chat_id = null)
	{
		$row = $this->service->get_detail($chat_id);
		if (!$row) {
			$this->_json(array('success' => false, 'message' => 'Not found'), 404);
		}
		$this->_json(array('success' => true, 'data' => $row));
	}

	public function reply($chat_id = null)
	{
		$body = $this->_body();
		$result = $this->service->reply($chat_id, isset($body['message']) ? $body['message'] : '', $this->current_user['user_id']);
		if (empty($result['success'])) {
			$this->_json($result, 422);
		}
		$this->_json($result);
	}

	public function close($chat_id = null)
	{
		$this->_json($this->service->mark_closed($chat_id));
	}
}
