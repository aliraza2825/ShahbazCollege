<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Public college website JSON API (no auth).
 * Base: /index.php/publicwebsiteapi/{method}
 *
 * Pass ?domain=shahbazcollegeofpharmacy.edu.pk (or rely on Origin / Referer / Host).
 */
class Publicwebsiteapi extends CI_Controller {

	/** @var Public_website_service */
	private $service;

	public function __construct()
	{
		parent::__construct();
		$this->_cors();
		if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
			http_response_code(204);
			exit;
		}
		$this->load->library('public_website_service');
		$this->service = $this->public_website_service;
	}

	private function _cors()
	{
		$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
		$allowed = array(
			'http://localhost:5174', 'http://localhost:5173', 'http://localhost:4173',
			'http://127.0.0.1:5174', 'http://127.0.0.1:5173',
			'https://www.shahbazcollegeofpharmacy.edu.pk',
			'https://shahbazcollegeofpharmacy.edu.pk',
		);
		if ($origin === '*' || in_array($origin, $allowed, true)) {
			header('Access-Control-Allow-Origin: ' . ($origin === '*' ? '*' : $origin));
		} elseif (preg_match('/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/', $origin)) {
			header('Access-Control-Allow-Origin: ' . $origin);
		} elseif (preg_match('/^https?:\/\/([a-z0-9.-]+\.)?shahbazcollegeofpharmacy\.edu\.pk$/i', $origin)) {
			header('Access-Control-Allow-Origin: ' . $origin);
		}
		header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
		header('Access-Control-Allow-Headers: Content-Type, Accept');
		header('Access-Control-Allow-Credentials: true');
		header('Vary: Origin');
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

	private function _domain()
	{
		$domain = $this->input->get_post('domain');
		if ($domain) {
			return $domain;
		}
		$body = $this->_body();
		if (!empty($body['domain'])) {
			return $body['domain'];
		}
		if (!empty($_SERVER['HTTP_ORIGIN'])) {
			return $_SERVER['HTTP_ORIGIN'];
		}
		if (!empty($_SERVER['HTTP_REFERER'])) {
			return $_SERVER['HTTP_REFERER'];
		}
		return isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
	}

	public function index()
	{
		$this->bootstrap();
	}

	public function bootstrap()
	{
		$result = $this->service->bootstrap($this->_domain());
		if (empty($result['success'])) {
			$this->_json($result, 404);
		}
		$this->_json($result);
	}

	public function downloads()
	{
		$campus = $this->service->resolve_campus($this->_domain());
		if (!$campus) {
			$this->_json(array('success' => false, 'message' => 'Campus not found'), 404);
		}
		$this->_json(array(
			'success' => true,
			'data' => $this->service->downloads_for_campus((int) $campus['campus_id']),
		));
	}

	public function gallery()
	{
		$campus = $this->service->resolve_campus($this->_domain());
		if (!$campus) {
			$this->_json(array('success' => false, 'message' => 'Campus not found'), 404);
		}
		$cid = (int) $campus['campus_id'];
		$this->_json(array(
			'success' => true,
			'data' => array(
				'events' => $this->service->events_for_campus($cid),
				'images' => $this->service->event_images_for_campus($cid),
			),
		));
	}

	public function courses()
	{
		$campus = $this->service->resolve_campus($this->_domain());
		if (!$campus) {
			$this->_json(array('success' => false, 'message' => 'Campus not found'), 404);
		}
		$this->_json(array(
			'success' => true,
			'data' => $this->service->courses_for_campus((int) $campus['campus_id']),
		));
	}

	public function apply()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			$this->_json(array('success' => false, 'message' => 'POST required'), 405);
		}
		$result = $this->service->submit_apply($this->_body(), $this->_domain());
		if (empty($result['success'])) {
			$this->_json($result, 422);
		}
		$this->_json($result);
	}

	public function career()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			$this->_json(array('success' => false, 'message' => 'POST required'), 405);
		}
		// Prefer multipart fields (CV upload)
		$body = $this->input->post() ? $this->input->post() : $this->_body();
		$result = $this->service->submit_career($body, $this->_domain());
		if (empty($result['success'])) {
			$this->_json($result, 422);
		}
		$this->_json($result);
	}
}
