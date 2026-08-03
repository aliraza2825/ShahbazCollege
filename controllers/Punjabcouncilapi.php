<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Punjab Council JSON API for React POS shell.
 * Base: /index.php/punjabcouncilapi/{method}
 */
class Punjabcouncilapi extends CI_Controller {

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
		$this->load->library('punjab_council_service');
		$this->service = $this->punjab_council_service;
		if (!$this->service->can_manage($this->current_user)) {
			$this->_json(array('success' => false, 'message' => 'Punjab Council access required'), 403);
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

	public function meta()
	{
		$this->_json(array('success' => true, 'data' => $this->service->meta($this->current_user)));
	}

	public function how_to_use()
	{
		$this->_json(array('success' => true, 'data' => $this->service->how_to_use_list()));
	}

	public function how_to_use_add()
	{
		$this->_json($this->service->how_to_use_add($this->current_user));
	}

	public function how_to_use_delete($id = null)
	{
		$this->_json($this->service->how_to_use_delete($id));
	}

	public function exam_sequences()
	{
		$this->_json(array('success' => true, 'data' => $this->service->exam_sequences_list()));
	}

	public function exam_sequence($id = null)
	{
		$row = $this->service->exam_sequence_get($id);
		if (!$row) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		$this->_json(array('success' => true, 'data' => $row));
	}

	public function exam_sequence_save($id = null)
	{
		$body = $this->_body();
		$this->_json($this->service->exam_sequence_save($body, $id ? (int) $id : null));
	}

	public function roll_numbers_pending()
	{
		$this->_json(array('success' => true, 'data' => $this->service->roll_numbers_pending($this->current_user)));
	}

	public function upload_roll_csv()
	{
		$this->_json($this->service->upload_roll_csv($this->_body()));
	}

	public function upload_roll_slips()
	{
		$this->_json($this->service->upload_roll_slip_images($this->_body()));
	}

	public function results()
	{
		$exam = $this->input->get('council_exam_no');
		$class = $this->input->get('class');
		$this->_json(array('success' => true, 'data' => $this->service->results_list($exam, $class, $this->current_user)));
	}

	public function upload_result_csv()
	{
		$this->_json($this->service->upload_result_csv($this->_body()));
	}

	public function upload_result_cards()
	{
		$this->_json($this->service->upload_result_card_images($this->_body()));
	}

	public function update_cnic()
	{
		$body = $this->_body();
		$this->_json($this->service->update_cnic($body['id'], $body['cnic'], !empty($body['council_mistake'])));
	}

	public function update_computer_no()
	{
		$body = $this->_body();
		$this->_json($this->service->update_computer_no($body['id'], $body['computer_no']));
	}

	public function delete_roll_no($id = null)
	{
		$this->_json($this->service->delete_roll_no($id));
	}

	public function final_result()
	{
		$campus_id = $this->input->get('campus_id');
		$class_id = $this->input->get('class_id');
		$this->_json(array('success' => true, 'data' => $this->service->final_result_students($campus_id, $class_id, $this->current_user)));
	}

	public function manual_remarks()
	{
		$body = $this->_body();
		$this->_json($this->service->save_manual_remarks(
			$body['student_id'],
			isset($body['remarks']) ? $body['remarks'] : '',
			isset($body['next_admission']) ? $body['next_admission'] : '',
			$this->current_user
		));
	}

	public function council_fee_preview()
	{
		$this->_json(array('success' => true, 'data' => $this->service->council_fee_preview($this->_body(), $this->current_user)));
	}

	public function manual_fee_preview()
	{
		$this->_json(array('success' => true, 'data' => $this->service->manual_fee_missing_students($this->_body())));
	}

	public function create_fees()
	{
		$this->_json($this->service->create_council_fees($this->_body(), $this->current_user));
	}

	public function next_exam_status()
	{
		$campus_id = $this->input->get('campus_id');
		$exam = $this->input->get('council_exam_no');
		$class = $this->input->get('class');
		$this->_json(array('success' => true, 'data' => $this->service->next_exam_payments($campus_id, $exam, $class)));
	}

	public function status_report()
	{
		$class = $this->input->get('class');
		$exam = $this->input->get('council_exam_no');
		$this->_json(array('success' => true, 'data' => $this->service->status_report_rows($class, $exam)));
	}

	public function conciliation()
	{
		$this->_json(array('success' => true, 'data' => $this->service->conciliation_summary($this->_body())));
	}

	public function exam_numbers()
	{
		$class = $this->input->get('class');
		$this->_json(array('success' => true, 'data' => $this->service->exam_numbers_for_class($class)));
	}

	public function classes()
	{
		$campus_id = $this->input->get('campus_id');
		$this->_json(array('success' => true, 'data' => $this->service->classes_for_campus($campus_id)));
	}
}
