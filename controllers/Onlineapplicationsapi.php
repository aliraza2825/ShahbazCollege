<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Online Applications JSON API for React POS shell.
 * Base: /index.php/onlineapplicationsapi/{method}
 * Auth: X-Pos-Token
 */
class Onlineapplicationsapi extends CI_Controller {

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
		$this->load->library('online_application_service');
		$this->service = $this->online_application_service;
		$this->service->bind_session($this->current_user);
		$perms = $this->service->permissions($this->current_user);
		if (empty($perms['has_access'])) {
			$this->_json(array('success' => false, 'message' => 'No online applications access'), 403);
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

	private function _perms()
	{
		return $this->service->permissions($this->current_user);
	}

	private function _assert_new_admissions()
	{
		$p = $this->_perms();
		if (empty($p['new_admissions'])) {
			$this->_json(array('success' => false, 'message' => 'No new admissions access'), 403);
		}
	}

	private function _assert_all()
	{
		$p = $this->_perms();
		if (empty($p['all'])) {
			$this->_json(array('success' => false, 'message' => 'No all applications access'), 403);
		}
	}

	private function _assert_facebook()
	{
		$p = $this->_perms();
		if (empty($p['facebook_leads'])) {
			$this->_json(array('success' => false, 'message' => 'No facebook leads access'), 403);
		}
	}

	public function meta()
	{
		$perms = $this->_perms();
		$counts = $this->service->counts();
		$campuses = $this->service->campuses_for_user($this->current_user);
		$legacy_root = rtrim(base_url(), '/');
		$this->_json(array(
			'success' => true,
			'permissions' => $perms,
			'counts' => $counts,
			'campuses' => $campuses,
			'legacy_root' => $legacy_root,
		));
	}

	public function new_applications()
	{
		$this->_assert_new_admissions();
		$campus_id = $this->input->get('campus_id');
		$campus_id = ($campus_id !== null && $campus_id !== '') ? $campus_id : null;
		$this->_json(array(
			'success' => true,
			'data' => $this->service->new_applications($campus_id),
		));
	}

	public function pending_applications()
	{
		$this->_assert_new_admissions();
		$campus_id = $this->input->get('campus_id');
		$campus_id = ($campus_id !== null && $campus_id !== '') ? $campus_id : null;
		$data = $this->service->pending_applications($campus_id);
		$this->_json(array_merge(array('success' => true), $data));
	}

	public function checked_applications()
	{
		$p = $this->_perms();
		if (empty($p['checked_admissions']) && empty($p['all']) && empty($p['is_admin'])) {
			$this->_json(array('success' => false, 'message' => 'No checked applications access'), 403);
		}
		$this->_json(array(
			'success' => true,
			'data' => $this->service->checked_applications(),
		));
	}

	public function all_applications()
	{
		$this->_assert_all();
		$filters = array(
			'campus_id' => $this->input->get('campus_id'),
			'date_from' => $this->input->get('date_from'),
			'date_to' => $this->input->get('date_to'),
		);
		$this->_json(array(
			'success' => true,
			'data' => $this->service->all_applications($filters),
		));
	}

	public function confirmed_admissions()
	{
		$this->_assert_all();
		$this->_json(array(
			'success' => true,
			'data' => $this->service->confirmed_admissions(),
		));
	}

	public function add_comment()
	{
		$this->_assert_new_admissions();
		$result = $this->service->add_comment($this->_body(), $this->current_user);
		if (empty($result['success'])) {
			$this->_json($result, 422);
		}
		$this->_json($result);
	}

	public function clear_pending()
	{
		$this->_assert_new_admissions();
		$body = $this->_body();
		$id = isset($body['apply_now_id']) ? $body['apply_now_id'] : 0;
		$result = $this->service->clear_pending($id);
		if (empty($result['success'])) {
			$this->_json($result, 422);
		}
		$this->_json($result);
	}

	public function clear_checked()
	{
		$p = $this->_perms();
		if (empty($p['checked_admissions']) && empty($p['all']) && empty($p['is_admin'])) {
			$this->_json(array('success' => false, 'message' => 'No checked applications access'), 403);
		}
		$body = $this->_body();
		$id = isset($body['apply_now_id']) ? $body['apply_now_id'] : 0;
		$result = $this->service->clear_checked($id);
		if (empty($result['success'])) {
			$this->_json($result, 422);
		}
		$this->_json($result);
	}

	public function dynamic_submission_checked($submission_id = 0)
	{
		$this->_assert_new_admissions();
		$result = $this->service->mark_dynamic_checked($submission_id);
		if (empty($result['success'])) {
			$this->_json($result, 422);
		}
		$this->_json($result);
	}

	public function dynamic_forms()
	{
		$this->_assert_all();
		$forms = $this->service->dynamic_forms_list();
		foreach ($forms as &$form) {
			$form['public_url'] = $this->service->public_form_url($form['slug']);
		}
		unset($form);
		$this->_json(array('success' => true, 'data' => $forms));
	}

	public function dynamic_form($id = 0)
	{
		$this->_assert_all();
		$data = $this->service->dynamic_form_get($id);
		if (!$data) {
			$this->_json(array('success' => false, 'message' => 'Form not found'), 404);
		}
		$data['form']['public_url'] = $this->service->public_form_url($data['form']['slug']);
		$this->_json(array('success' => true, 'data' => $data));
	}

	public function save_dynamic_form()
	{
		$this->_assert_all();
		$result = $this->service->save_dynamic_form($this->_body(), $this->current_user);
		if (empty($result['success'])) {
			$this->_json($result, 422);
		}
		$result['public_url'] = $this->service->public_form_url($result['slug']);
		$this->_json($result);
	}

	public function delete_dynamic_form($id = 0)
	{
		$this->_assert_all();
		$result = $this->service->delete_dynamic_form($id);
		if (empty($result['success'])) {
			$this->_json($result, 422);
		}
		$this->_json($result);
	}

	public function upload_fb_leads()
	{
		$this->_assert_facebook();
		$website = $this->input->post('website');
		if ($website === null || $website === '') {
			$body = $this->_body();
			$website = isset($body['website']) ? $body['website'] : '';
		}
		if (empty($_FILES['fb_file']['tmp_name']) || !is_uploaded_file($_FILES['fb_file']['tmp_name'])) {
			$this->_json(array('success' => false, 'message' => 'CSV file required'), 422);
		}
		$result = $this->service->upload_fb_leads($website, $_FILES['fb_file']['tmp_name']);
		if (empty($result['success'])) {
			$this->_json($result, 422);
		}
		$this->_json($result);
	}

	public function how_to_use()
	{
		if ($this->current_user['role'] !== 'Admin') {
			$this->_json(array('success' => false, 'message' => 'Admin only'), 403);
		}
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$result = $this->service->how_to_use_add($this->_body());
			if (empty($result['success'])) {
				$this->_json($result, 422);
			}
			$this->_json($result);
		}
		$this->_json(array(
			'success' => true,
			'data' => $this->service->how_to_use_list(),
		));
	}
}
