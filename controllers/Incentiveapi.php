<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Incentive Management JSON API for React POS shell
 * Base: /index.php/incentiveapi/{method}
 * Auth: X-Pos-Token; Admin or recovery_portal/all_users_recovery access flags.
 *
 * Business logic is ported verbatim (including legacy quirks) from:
 *  - application/controllers/Recovery_management.php  (fee recovery incentives)
 *  - application/controllers/Admission_management.php (admission incentives)
 */
class Incentiveapi extends CI_Controller {

	private $current_user = null;
	private $access_row = null;

	public function __construct()
	{
		parent::__construct();
		// CLI parity / tooling: no CORS/auth (never exposed over HTTP)
		if (is_cli()) {
			$this->current_user = array('user_id' => 1, 'role' => 'Admin', 'status' => '1');
			$this->access_row = null;
			return;
		}
		$this->_cors();
		if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
			http_response_code(204);
			exit;
		}
		$this->current_user = $this->_auth_user();
		if (!$this->current_user) {
			$this->_json(array('success' => false, 'message' => 'Unauthorized'), 401);
		}
		$this->access_row = $this->_load_access_row();
		if (!$this->_is_admin() && !$this->_access_flag('recovery_portal') && !$this->_access_flag('all_users_recovery')) {
			$this->_json(array('success' => false, 'message' => 'Incentive access required'), 403);
		}
	}

	// ------------------------------------------------------------------
	// Skeleton cloned from Constructionapi.php (CORS / json / body / auth)
	// ------------------------------------------------------------------

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
		if ($token === '' || $token === null) {
			$token = $this->input->get('pos_token');
		}
		if (!$token) return null;
		$row = $this->db->get_where('pos_api_tokens', array('token' => $token))->row_array();
		if (!$row || strtotime($row['expires_at']) < time()) return null;
		return $this->db->get_where('users', array('user_id' => $row['user_id'], 'status' => '1'))->row_array();
	}

	private function _is_admin()
	{
		return isset($this->current_user['role']) && $this->current_user['role'] === 'Admin';
	}

	private function _load_access_row()
	{
		if (!$this->db->table_exists('access')) return null;
		return $this->db->get_where('access', array('user_id' => (int)$this->current_user['user_id']))->row_array();
	}

	private function _access_flag($col)
	{
		if ($this->_is_admin()) return true;
		if (!$this->access_row || !isset($this->access_row[$col])) return false;
		$v = $this->access_row[$col];
		return $v !== null && $v !== '' && (string)$v !== '0';
	}

	private function _can_manage()
	{
		return $this->_is_admin() || $this->_access_flag('all_users_recovery');
	}

	private function _assert_manage()
	{
		if ($this->_can_manage()) return;
		$this->_json(array('success' => false, 'message' => 'Incentive manage access required'), 403);
	}

	// ------------------------------------------------------------------
	// Small helpers
	// ------------------------------------------------------------------

	/** Turn a CSV column ("1,2,3") into a clean int array. Used for display/enrichment only. */
	private function _ids_from_csv($csv)
	{
		$out = array();
		foreach (explode(',', (string)$csv) as $v) {
			$v = trim($v);
			if ($v === '' || !is_numeric($v)) continue;
			$out[] = (int)$v;
		}
		return $out;
	}

	/** Build a clean CSV string from a JSON array of ids for storage (avoids legacy explode('')=>[''] quirk on write). */
	private function _csv_ids($arr)
	{
		if (!is_array($arr)) return '';
		$ids = array();
		foreach ($arr as $v) {
			if ($v === null || $v === '') continue;
			if (!is_numeric($v)) continue;
			$ids[] = (string)(int)$v;
		}
		return implode(',', $ids);
	}

	/** Same "users assigned to a designation" lookup used by all_assign_task.php / all_admission_assign_task.php views. */
	private function _matching_users($designation_id)
	{
		$where = '(designation_id ="' . $designation_id . '" or designation_id like "%' . $designation_id . ',%" or designation_id like "%,' . $designation_id . '%") and status = "1"';
		return $this->db->get_where('users', $where)->result_array();
	}

	private function _campus_names($campus_ids)
	{
		$names = array();
		if (count($campus_ids)) {
			$rows = $this->db->where_in('campus_id', $campus_ids)->get('campuses')->result_array();
			foreach ($rows as $r) $names[] = $r['campus_name'];
		}
		return $names;
	}

	private function _course_names($course_ids)
	{
		$names = array();
		if (count($course_ids)) {
			$rows = $this->db->where_in('course_id', $course_ids)->get('courses')->result_array();
			foreach ($rows as $r) $names[] = $r['course_name'];
		}
		return $names;
	}

	// ------------------------------------------------------------------
	// index / meta
	// ------------------------------------------------------------------

	public function index()
	{
		$this->meta();
	}

	public function meta()
	{
		$can_manage = $this->_can_manage();
		$can_portal = $this->_is_admin() || $this->_access_flag('recovery_portal') || $can_manage;

		$this->_json(array(
			'success' => true,
			'role' => isset($this->current_user['role']) ? $this->current_user['role'] : null,
			'can_manage' => $can_manage,
			'can_portal' => $can_portal,
			'sections' => array(
				array('key' => 'recovery', 'label' => 'Fee Recovery Incentives', 'enabled' => true),
				array('key' => 'admission', 'label' => 'Admission Incentives', 'enabled' => true),
			),
		));
	}

	// ------------------------------------------------------------------
	// Lookups
	// ------------------------------------------------------------------

	public function campuses()
	{
		$rows = $this->db->get_where('campuses', array('status' => 1))->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function departments()
	{
		$rows = $this->db->get('departments')->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function designations()
	{
		$department_id = $this->input->get('department_id');
		if ($department_id !== null && $department_id !== '') {
			$rows = $this->db->get_where('designations', array('department_id' => $department_id))->result_array();
		} else {
			$rows = $this->db->get('designations')->result_array();
		}
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function users_lookup()
	{
		$campus_id = $this->input->get('campus_id');
		$department_id = $this->input->get('department_id');
		$designation_id = $this->input->get('designation_id');

		$this->db->select('user_id, first_name, last_name, campus_id, department_id, designation_id');
		$this->db->where('status', '1');
		if ($campus_id !== null && $campus_id !== '') $this->db->where('campus_id', $campus_id);
		if ($department_id !== null && $department_id !== '') $this->db->where('department_id', $department_id);
		if ($designation_id !== null && $designation_id !== '') {
			$this->db->where("FIND_IN_SET(" . $this->db->escape_str($designation_id) . ", designation_id) >", 0, false);
		}
		$rows = $this->db->get('users')->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function courses()
	{
		if ($this->db->field_exists('status', 'courses')) {
			$this->db->where('status', 1);
		}
		$rows = $this->db->get('courses')->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	// ------------------------------------------------------------------
	// Recovery (fee) incentive tasks — ports Recovery_management.php
	// ------------------------------------------------------------------

	public function recovery_tasks()
	{
		$this->_assert_manage();

		$this->db->select('*');
		$this->db->from('recovery_management');
		$this->db->join('designations', 'recovery_management.designation_id=designations.designation_id', 'INNER');
		$this->db->join('departments', 'designations.department_id=departments.department_id', 'INNER');
		$rows = $this->db->get()->result_array();

		$out = array();
		foreach ($rows as $row) {
			$out[] = $this->_enrich_recovery_row($row);
		}

		$this->_json(array('success' => true, 'data' => $out));
	}

	private function _enrich_recovery_row($row)
	{
		$campus_ids = $this->_ids_from_csv($row['campus_ids']);
		$course_ids = $this->_ids_from_csv($row['course_id']);

		$rules = $this->db->get_where('recovery_management_rules', array('recovery_management_id' => $row['recovery_management_id']))->result_array();
		$users_raw = $this->_matching_users($row['designation_id']);
		$users = array();
		foreach ($users_raw as $u) {
			$users[] = array('user_id' => $u['user_id'], 'first_name' => $u['first_name'], 'last_name' => $u['last_name']);
		}

		return array(
			'recovery_management_id' => $row['recovery_management_id'],
			'designation_id' => $row['designation_id'],
			'designation_name' => $row['designation_name'],
			'department_name' => $row['department_name'],
			'campus_ids' => $row['campus_ids'],
			'campus_names' => $this->_campus_names($campus_ids),
			'course_id' => $row['course_id'],
			'course_names' => $this->_course_names($course_ids),
			'min_fine_amount' => $row['min_fine_amount'],
			'fine_amount_percentage' => $row['fine_amount_percentage'],
			'rules' => array_map(function ($r) {
				return array('start' => $r['start'], 'end' => $r['end'], 'comission' => $r['comission']);
			}, $rules),
			'users' => $users,
		);
	}

	public function recovery_task($id = 0)
	{
		$id = (int)$id;
		$method = $_SERVER['REQUEST_METHOD'];

		if ($method === 'GET') {
			$this->_assert_manage();
			if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
			$row = $this->db->get_where('recovery_management', array('recovery_management_id' => $id))->row_array();
			if (!$row) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
			$rules = $this->db->get_where('recovery_management_rules', array('recovery_management_id' => $id))->result_array();
			$row['campus_ids_arr'] = $this->_ids_from_csv($row['campus_ids']);
			$row['course_id_arr'] = $this->_ids_from_csv($row['course_id']);
			$this->_json(array('success' => true, 'data' => $row, 'rules' => $rules));
		}

		if ($method === 'POST') {
			$this->_assert_manage();
			$body = $this->_body();
			$rules_in = isset($body['rules']) && is_array($body['rules']) ? $body['rules'] : array();

			if ($id === 0) {
				// Port of Recovery_management::set_comission()
				$designation_id = isset($body['designation_id']) ? $body['designation_id'] : null;
				$min_fine_amount = isset($body['min_fine_amount']) ? $body['min_fine_amount'] : null;
				$fine_amount_percentage = isset($body['fine_amount_percentage']) ? $body['fine_amount_percentage'] : null;
				$campus_ids = $this->_csv_ids(isset($body['campus_ids']) ? $body['campus_ids'] : array());
				$course_id_in = isset($body['course_id']) ? $body['course_id'] : array();
				$course_id = is_array($course_id_in) ? $this->_csv_ids($course_id_in) : '';

				$check = $this->db->get_where('recovery_management', array('designation_id' => $designation_id))->result_array();
				if (count($check) == 0) {
					$this->db->set('designation_id', $designation_id);
					$this->db->set('course_id', $course_id);
					$this->db->set('min_fine_amount', $min_fine_amount);
					$this->db->set('fine_amount_percentage', $fine_amount_percentage);
					$this->db->set('campus_ids', $campus_ids);
					$this->db->insert('recovery_management');
					$recovery_management_id = $this->db->insert_id();
				} else {
					// DELETE PREVIOUS DATA (matches legacy: existing row's own fields are left untouched)
					$this->db->where('recovery_management_id', $check[0]['recovery_management_id']);
					$this->db->delete('recovery_management_rules');
					$recovery_management_id = $check[0]['recovery_management_id'];
				}

				foreach ($rules_in as $rule) {
					$this->db->set('recovery_management_id', $recovery_management_id);
					$this->db->set('start', isset($rule['start']) ? $rule['start'] : 0);
					$this->db->set('end', isset($rule['end']) ? $rule['end'] : 0);
					$this->db->set('comission', isset($rule['comission']) ? $rule['comission'] : 0);
					$this->db->insert('recovery_management_rules');
				}

				$this->_json(array('success' => true, 'id' => (int)$recovery_management_id));
			} else {
				// Port of Recovery_management::update_comission()
				$exists = $this->db->get_where('recovery_management', array('recovery_management_id' => $id))->row_array();
				if (!$exists) $this->_json(array('success' => false, 'message' => 'Not found'), 404);

				$campus_ids = $this->_csv_ids(isset($body['campus_ids']) ? $body['campus_ids'] : array());
				$min_fine_amount = isset($body['min_fine_amount']) ? $body['min_fine_amount'] : $exists['min_fine_amount'];
				$fine_amount_percentage = isset($body['fine_amount_percentage']) ? $body['fine_amount_percentage'] : $exists['fine_amount_percentage'];

				$this->db->set('min_fine_amount', $min_fine_amount);
				$this->db->set('fine_amount_percentage', $fine_amount_percentage);
				$this->db->set('campus_ids', $campus_ids);
				$this->db->where('recovery_management_id', $id);
				$this->db->update('recovery_management');

				$this->db->where('recovery_management_id', $id);
				$this->db->delete('recovery_management_rules');

				foreach ($rules_in as $rule) {
					$this->db->set('recovery_management_id', $id);
					$this->db->set('start', isset($rule['start']) ? $rule['start'] : 0);
					$this->db->set('end', isset($rule['end']) ? $rule['end'] : 0);
					$this->db->set('comission', isset($rule['comission']) ? $rule['comission'] : 0);
					$this->db->insert('recovery_management_rules');
				}

				$this->_json(array('success' => true, 'id' => $id));
			}
		}

		if ($method === 'DELETE') {
			$this->_assert_manage();
			if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
			$this->db->where('recovery_management_id', $id);
			$this->db->delete('recovery_management_rules');
			$this->db->where('recovery_management_id', $id);
			$this->db->delete('recovery_management');
			$this->_json(array('success' => true));
		}

		$this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
	}

	// ------------------------------------------------------------------
	// Recovery check / entries / dues / fine — ports check_recovery(), all_entries(),
	// all_paid_entries(), fee_dues_comments() from Recovery_management.php
	// ------------------------------------------------------------------

	public function recovery_check($recovery_id = 0, $user_id = 0)
	{
		$recovery_id = (int)$recovery_id;
		$user_id = (int)$user_id;
		if (!$recovery_id || !$user_id) $this->_json(array('success' => false, 'message' => 'recovery_id and user_id required'), 422);

		$from_date = $this->input->get('from');
		$to_date = $this->input->get('to');
		if ($from_date === null || $from_date === '') $from_date = date('Y-m-01');
		if ($to_date === null || $to_date === '') $to_date = date('Y-m-t');

		$d = $this->_recovery_check_data($recovery_id, $user_id, $from_date, $to_date);
		if (!$d) $this->_json(array('success' => false, 'message' => 'Recovery task not found'), 404);

		$kpi = $this->_recovery_kpi($d);

		$this->_json(array(
			'success' => true,
			'from_date' => $from_date,
			'to_date' => $to_date,
			'recovery' => $d['recovery'],
			'user' => $d['user'],
			'rules' => $d['rules'],
			'kpi' => $kpi,
			'fine_students' => $d['fine_students'],
			'unverified_paid_count_students' => $d['unverified_paid_count_students'],
		));
	}

	/** Exact port of Recovery_management::check_recovery() query set. */
	private function _recovery_check_data($recovery_id, $user_id, $from_date, $to_date)
	{
		$recovery_rows = $this->db->get_where('recovery_management', array('recovery_management_id' => $recovery_id))->result_array();
		if (!count($recovery_rows)) return null;
		$recovery = $recovery_rows[0];
		$campus_ids = explode(',', $recovery['campus_ids']);
		$course_ids = explode(',', $recovery['course_id']);

		// GET USER DETAILS
		$this->db->select('*');
		$this->db->from('users');
		$this->db->join('campuses', 'campuses.campus_id=users.campus_id', 'INNER');
		$this->db->join('departments', 'departments.department_id=users.department_id', 'INNER');
		$this->db->join('designations', 'designations.designation_id=users.designation_id', 'INNER');
		$this->db->where('users.user_id', $user_id);
		$user_rows = $this->db->get()->result_array();
		$user = count($user_rows) ? $user_rows[0] : null;

		// GET ALL UNPAID FEE PAYMENTS DETAILS OF STUDENTS
		$this->db->select('payments.*');
		$this->db->from('payments');
		$this->db->join('students', 'students.student_id=payments.student_id', 'INNER');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
		$this->db->join('courses', 'courses.course_id=students.course_id', 'INNER');
		$this->db->where_in('courses.course_id', $course_ids);
		$this->db->where_in('campuses.campus_id', $campus_ids);
		$this->db->where(array('payments.dead_line<=' => $to_date, 'payments.paid' => 0, 'students.status' => 1));
		$unpaid_payments_students = $this->db->get()->result_array();

		// GET ALL UNPAID FEE PAYMENTS DURING LAST MONTH
		$this->db->select('payments.*');
		$this->db->from('payments');
		$this->db->join('students', 'students.student_id=payments.student_id', 'INNER');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
		$this->db->join('courses', 'courses.course_id=students.course_id', 'INNER');
		$this->db->where_in('courses.course_id', $course_ids);
		$this->db->where_in('campuses.campus_id', $campus_ids);
		$this->db->where(array('payments.paid_date>' => $to_date, 'payments.paid' => 1, 'students.status' => 1));
		$unpaid_payments_students_during_last_month = $this->db->get()->result_array();

		// GET ALL UNPAID FEE PAYMENTS DETAILS OF CONTRACTS
		$this->db->select('payments.*');
		$this->db->from('payments');
		$this->db->join('contracts', 'contracts.contract_id=payments.contract_id', 'INNER');
		$this->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'INNER');
		$this->db->join('campuses', 'contracts.campus_id=campuses.campus_id', 'INNER');
		$this->db->join('courses', 'courses.course_id=contracts.course_id', 'INNER');
		$this->db->where_in('courses.course_id', $course_ids);
		$this->db->where_in('campuses.campus_id', $campus_ids);
		$this->db->where(array('payments.dead_line<=' => $to_date, 'payments.paid' => 0, 'payments.payment_plan Not Like' => 'extra fee', 'payments.amount !=' => '4500'));
		$unpaid_payments_contracts = $this->db->get()->result_array();

		// GET ALL UNPAID STUDENTS COUNT
		$this->db->select('payments.*');
		$this->db->from('payments');
		$this->db->join('students', 'students.student_id=payments.student_id', 'INNER');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
		$this->db->join('courses', 'courses.course_id=students.course_id', 'INNER');
		$this->db->where_in('courses.course_id', $course_ids);
		$this->db->where_in('campuses.campus_id', $campus_ids);
		$this->db->where(array('payments.dead_line<=' => $to_date, 'payments.paid' => 0, 'payments.payment_plan Not like' => 'extra fee', 'students.status' => 1));
		$this->db->group_by('students.student_id');
		$fee_dues_students_count = $this->db->get()->result_array();

		// GET ALL UNPAID COUNT CONTRACTS
		$this->db->select('payments.*');
		$this->db->from('payments');
		$this->db->join('contracts', 'contracts.contract_id=payments.contract_id', 'INNER');
		$this->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'INNER');
		$this->db->join('campuses', 'contracts.campus_id=campuses.campus_id', 'INNER');
		$this->db->join('courses', 'courses.course_id=contracts.course_id', 'INNER');
		$this->db->where_in('courses.course_id', $course_ids);
		$this->db->where_in('campuses.campus_id', $campus_ids);
		$this->db->where(array('payments.dead_line<=' => $to_date, 'payments.paid' => 0, 'payments.payment_plan Not Like' => 'extra fee', 'payments.amount !=' => '4500'));
		$this->db->group_by('contracts.contract_id');
		$fee_dues_contractors_count = $this->db->get()->result_array();

		// GET FEE PAYMENTS DETAILS OF STUDENTS
		$this->db->select('payments.*');
		$this->db->from('payments');
		$this->db->join('students', 'students.student_id=payments.student_id', 'INNER');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
		$this->db->join('courses', 'courses.course_id=students.course_id', 'INNER');
		$this->db->where_in('courses.course_id', $course_ids);
		$this->db->where_in('campuses.campus_id', $campus_ids);
		$this->db->where(array('payments.actual_paid_date>=' => $from_date, 'payments.actual_paid_date<=' => $to_date, 'payments.paid' => 1, 'students.status' => 1));
		$paid_payments_students = $this->db->get()->result_array();

		// GET PAID PAYMENTS COUNT OF STUDENTS
		$this->db->select('payments.*');
		$this->db->from('payments');
		$this->db->join('students', 'students.student_id=payments.student_id', 'INNER');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
		$this->db->join('courses', 'courses.course_id=students.course_id', 'INNER');
		$this->db->where_in('courses.course_id', $course_ids);
		$this->db->where_in('campuses.campus_id', $campus_ids);
		$this->db->where(array('payments.actual_paid_date>=' => $from_date, 'payments.actual_paid_date<=' => $to_date, 'payments.paid' => 1, 'students.status' => 1));
		$this->db->group_by('students.student_id');
		$paid_count_students = $this->db->get()->result_array();

		// GET FEE PAYMENTS DETAILS OF CONTRACTS
		$this->db->select('payments.*');
		$this->db->from('payments');
		$this->db->join('contracts', 'contracts.contract_id=payments.contract_id', 'INNER');
		$this->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'INNER');
		$this->db->join('campuses', 'contracts.campus_id=campuses.campus_id', 'INNER');
		$this->db->join('courses', 'courses.course_id=contracts.course_id', 'INNER');
		$this->db->where_in('courses.course_id', $course_ids);
		$this->db->where_in('campuses.campus_id', $campus_ids);
		$this->db->where(array('payments.actual_paid_date>=' => $from_date, 'payments.actual_paid_date<=' => $to_date, 'payments.paid' => 1, 'payments.amount !=' => '4500'));
		$paid_payments_contracts = $this->db->get()->result_array();

		// GET FEE PAID CONTRACTORS COUNT
		$this->db->select('payments.*');
		$this->db->from('payments');
		$this->db->join('contracts', 'contracts.contract_id=payments.contract_id', 'INNER');
		$this->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'INNER');
		$this->db->join('campuses', 'contracts.campus_id=campuses.campus_id', 'INNER');
		$this->db->join('courses', 'courses.course_id=contracts.course_id', 'INNER');
		$this->db->where_in('courses.course_id', $course_ids);
		$this->db->where_in('campuses.campus_id', $campus_ids);
		$this->db->where(array('payments.actual_paid_date>=' => $from_date, 'payments.actual_paid_date<=' => $to_date, 'payments.paid' => 1, 'payments.amount !=' => '4500'));
		$this->db->group_by('contracts.contract_id');
		$paid_count_contracts = $this->db->get()->result_array();

		// GET SHIFTED PAYMENTS DETAILS OF STUDENTS
		$this->db->select('update_payment_requests.*');
		$this->db->from('update_payment_requests');
		$this->db->join('students', 'students.student_id=update_payment_requests.student_id', 'INNER');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
		$this->db->join('courses', 'courses.course_id=students.course_id', 'INNER');
		$this->db->where_in('courses.course_id', $course_ids);
		$this->db->where_in('campuses.campus_id', $campus_ids);
		$this->db->where(array('update_payment_requests.update_date>=' => $from_date, 'update_payment_requests.update_date<=' => $to_date, 'update_payment_requests.ok_by_admin' => 1, 'update_payment_requests.amount !=' => '4500'));
		$shifted_payments_students = $this->db->get()->result_array();

		// GET SHIFTED PAYMENTS DETAILS OF CONTRACTS
		$this->db->select('update_payment_requests.*');
		$this->db->from('update_payment_requests');
		$this->db->join('contracts', 'contracts.contract_id=update_payment_requests.contract_id', 'INNER');
		$this->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'INNER');
		$this->db->join('campuses', 'contracts.campus_id=campuses.campus_id', 'INNER');
		$this->db->join('courses', 'courses.course_id=contracts.course_id', 'INNER');
		$this->db->where_in('courses.course_id', $course_ids);
		$this->db->where_in('campuses.campus_id', $campus_ids);
		$this->db->where(array('update_payment_requests.update_date>=' => $from_date, 'update_payment_requests.update_date<=' => $to_date, 'update_payment_requests.ok_by_admin' => 1, 'update_payment_requests.amount !=' => '4500'));
		$shifted_payments_contracts = $this->db->get()->result_array();

		$this->db->select("payments.id as fee_id,'0' as isdel, 'UnPaid' as Fstatus,payments.split as split,payments.amount, payments.dead_line, payments.paid_challans, payments.merged_challan,payments.challan_no, payments.fine_amount, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee");
		$this->db->from('payments');
		$this->db->join('students', 'students.student_id=payments.student_id', 'INNER');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
		$this->db->join('courses', 'courses.course_id=students.course_id', 'INNER');
		$this->db->where_in('courses.course_id', $course_ids);
		$this->db->where_in('campuses.campus_id', $campus_ids);
		$this->db->where('payments.merged_challan IS NOT NULL');
		$this->db->where(array('payments.actual_paid_date>=' => $from_date, 'payments.actual_paid_date<=' => $to_date, 'payments.paid' => 1, 'students.status' => 1));
		$this->db->group_by('payments.merged_challan');
		$datafine_students = $this->db->get()->result_array();

		$this->db->select("payments.id as fee_id,'0' as isdel, 'UnPaid' as Fstatus,payments.split as split,payments.amount, payments.merged_challan,payments.challan_no, payments.paid_challans, payments.dead_line, payments.fine_amount, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee");
		$this->db->from('payments');
		$this->db->join('students', 'students.student_id=payments.student_id', 'INNER');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
		$this->db->join('courses', 'courses.course_id=students.course_id', 'INNER');
		$this->db->where_in('courses.course_id', $course_ids);
		$this->db->where_in('campuses.campus_id', $campus_ids);
		$this->db->where('payments.merged_challan is null');
		$this->db->where(array('payments.actual_paid_date>=' => $from_date, 'payments.actual_paid_date<=' => $to_date, 'payments.paid' => 1, 'students.status' => 1));
		$this->db->or_where('merged_challan IS not NULL and actual_amount = 0');
		$datapaid_payments_fine_students = $this->db->get()->result_array();
		$fine_students = array_merge($datafine_students, $datapaid_payments_fine_students);

		// GET PAID UNVERIFIED BANK PAYMENTS COUNT OF STUDENTS
		$this->db->select("payments.id as fee_id,'0' as isdel, 'UnPaid' as Fstatus,payments.split as split,payments.amount, payments.merged_challan,payments.challan_no, payments.paid_challans, payments.dead_line, payments.fine_amount, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee");
		$this->db->from('payments');
		$this->db->join('students', 'students.student_id=payments.student_id', 'INNER');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
		$this->db->join('courses', 'courses.course_id=students.course_id', 'INNER');
		$this->db->where_in('courses.course_id', $course_ids);
		$this->db->where_in('campuses.campus_id', $campus_ids);
		$this->db->where(array('payments.actual_paid_date>=' => $from_date, 'payments.actual_paid_date<=' => $to_date, 'payments.paid' => 1, 'students.status' => 1, 'payments.fee_pay_through' => 'bank', 'payments.clear_by' => ''));
		$this->db->group_by('paid_challans', false);
		$unverified_paid_count_students = $this->db->get()->result_array();

		$rules = $this->db->get_where('recovery_management_rules', array('recovery_management_id' => $recovery_id))->result_array();

		return array(
			'recovery' => $recovery,
			'user' => $user,
			'rules' => $rules,
			'unpaid_payments_students' => $unpaid_payments_students,
			'unpaid_payments_students_during_last_month' => $unpaid_payments_students_during_last_month,
			'unpaid_payments_contracts' => $unpaid_payments_contracts,
			'fee_dues_students_count' => $fee_dues_students_count,
			'fee_dues_contractors_count' => $fee_dues_contractors_count,
			'paid_payments_students' => $paid_payments_students,
			'paid_count_students' => $paid_count_students,
			'paid_payments_contracts' => $paid_payments_contracts,
			'paid_count_contracts' => $paid_count_contracts,
			'shifted_payments_students' => $shifted_payments_students,
			'shifted_payments_contracts' => $shifted_payments_contracts,
			'fine_students' => $fine_students,
			'unverified_paid_count_students' => $unverified_paid_count_students,
		);
	}

	/** KPI formulas ported from application/views/recovery_management/check_recovery.php */
	private function _recovery_kpi($d)
	{
		$delete_entries = 0;
		$shifted_entries = 0;
		foreach ($d['shifted_payments_students'] as $row) {
			if (isset($row['del']) && $row['del'] == 1) $delete_entries++; else $shifted_entries++;
		}
		foreach ($d['shifted_payments_contracts'] as $row) {
			if (isset($row['del']) && $row['del'] == 1) $delete_entries++; else $shifted_entries++;
		}

		$total_entries = count($d['unpaid_payments_students']) + count($d['unpaid_payments_contracts'])
			+ count($d['paid_payments_students']) + count($d['paid_payments_contracts'])
			+ count($d['shifted_payments_students']) + count($d['shifted_payments_contracts'])
			+ count($d['unpaid_payments_students_during_last_month']);
		$total_entries = $total_entries - $delete_entries;

		$paid_entries = count($d['paid_payments_students']) + count($d['paid_payments_contracts']);
		$paid_entries -= count($d['unverified_paid_count_students']);

		if ($total_entries > 0) {
			$submitted_fee_percentage = round(($paid_entries / $total_entries) * 100, 2);
		} else {
			$submitted_fee_percentage = 0;
		}

		$recovery_id = $d['recovery']['recovery_management_id'];
		$this->db->order_by('start', 'ASC');
		$comission_rule = $this->db->get_where('recovery_management_rules', array(
			'recovery_management_id' => $recovery_id,
			'start<=' => $submitted_fee_percentage,
			'end>' => $submitted_fee_percentage,
		))->result_array();

		if (count($comission_rule) > 0) {
			$percent_amount = $comission_rule[0]['comission'];
			$commission_unit = (float)$percent_amount;
			$amount = 0;
			foreach ($d['paid_payments_students'] as $due) {
				if ($due['split'] == '1') $amount += 0.5 * $percent_amount;
				elseif ($due['split'] == '2') $amount += 0.25 * $percent_amount;
				else $amount += $percent_amount;
			}
			foreach ($d['paid_payments_contracts'] as $due) {
				if ($due['split'] == '1') $amount += 0.5 * $percent_amount;
				elseif ($due['split'] == '2') $amount += 0.25 * $percent_amount;
				else $amount += $percent_amount;
			}
			$installment_comission = $amount;
		} else {
			$installment_comission = 0;
			$commission_unit = 0;
		}

		// Legacy: `if ($row['paid'] = '1')` is an assignment, always true — every fine row is summed.
		$collected_fine = 0;
		foreach ($d['fine_students'] as $row) {
			$collected_fine += isset($row['fine_amount']) ? (float)$row['fine_amount'] : 0;
		}

		$min_fine_amount = $d['recovery']['min_fine_amount'];
		$fine_amount_percentage = $d['recovery']['fine_amount_percentage'];
		if ($collected_fine > $min_fine_amount) {
			$fine_comission = ($collected_fine * $fine_amount_percentage) / 100;
		} else {
			$fine_comission = 0;
		}

		$total_incentive = $installment_comission + $fine_comission;

		$total_paidunpaid_students = count($d['fee_dues_students_count']) + count($d['fee_dues_contractors_count'])
			+ count($d['paid_count_students']) + count($d['paid_count_contracts']);
		$paid_students = count($d['paid_count_students']) + count($d['paid_count_contracts']);
		$unpaid_entries = count($d['unpaid_payments_students']) + count($d['unpaid_payments_contracts']) + count($d['unpaid_payments_students_during_last_month']);
		$unpaid_students = $total_paidunpaid_students - $paid_students;

		return array(
			'total_entries' => $total_entries,
			'total_students' => $total_paidunpaid_students,
			'shifted_entries' => $shifted_entries,
			'delete_entries' => $delete_entries,
			'paid_entries' => $paid_entries,
			'unpaid_entries' => $unpaid_entries,
			'unpaid_students' => $unpaid_students,
			'paid_students' => $paid_students,
			'unverified_paid_count' => count($d['unverified_paid_count_students']),
			'recovered_percentage' => $submitted_fee_percentage,
			'installment_commission' => $installment_comission,
			/** Flat tier commission used in legacy all_entries URL segment */
			'commission_unit' => isset($commission_unit) ? $commission_unit : 0,
			'collected_fine' => $collected_fine,
			'fine_commission' => $fine_comission,
			'total_incentive' => $total_incentive,
		);
	}

	/** Student Full Detail Report for recovery Total Students KPI (paid + unpaid student rows). */
	public function recovery_students_detail($recovery_id = 0, $user_id = 0)
	{
		$recovery_id = (int)$recovery_id;
		$user_id = (int)$user_id;
		if (!$recovery_id || !$user_id) {
			$this->_json(array('success' => false, 'message' => 'recovery_id and user_id required'), 422);
		}

		$from_date = $this->input->get('from');
		$to_date = $this->input->get('to');
		if ($from_date === null || $from_date === '') $from_date = date('Y-m-01');
		if ($to_date === null || $to_date === '') $to_date = date('Y-m-t');

		$page = (int)$this->input->get('page');
		if ($page < 1) $page = 1;
		$page_size = (int)$this->input->get('page_size');
		if ($page_size <= 0) $page_size = 25;
		if ($page_size > 5000) $page_size = 5000;
		$q = trim((string)$this->input->get('q'));

		$d = $this->_recovery_check_data($recovery_id, $user_id, $from_date, $to_date);
		if (!$d) $this->_json(array('success' => false, 'message' => 'Recovery task not found'), 404);

		$student_ids = $this->_recovery_total_student_ids($d);
		$pack = $this->_fetch_students_by_ids($student_ids, $q, ($page - 1) * $page_size, $page_size);

		$this->load->library('student_detail_report');
		$range = $this->student_detail_report->date_range_for_students($student_ids);
		$months = $this->student_detail_report->month_list($range['startdate'], $range['enddate']);
		if (count($months) > 36) $months = array_slice($months, -36);
		$detail = $this->student_detail_report->enrich($pack['rows'], $months);

		$kpi = $this->_recovery_kpi($d);
		$total_pages = max(1, (int)ceil($pack['total'] / $page_size));

		$this->_json(array(
			'success' => true,
			'from_date' => $from_date,
			'to_date' => $to_date,
			'user' => $d['user'],
			'recovery' => $d['recovery'],
			'data' => $detail['rows'],
			'months' => $months,
			'footer_must' => $detail['footer_must'],
			'footer_paid' => $detail['footer_paid'],
			'startdate' => $range['startdate'],
			'enddate' => $range['enddate'],
			'kpi_total_students' => $kpi['total_students'],
			'student_count' => count($student_ids),
			'pagination' => array(
				'page' => $page,
				'page_size' => $page_size,
				'total' => $pack['total'],
				'total_pages' => $total_pages,
			),
		));
	}

	private function _recovery_total_student_ids($d)
	{
		$ids = array();
		foreach ($d['fee_dues_students_count'] as $row) {
			if (!empty($row['student_id'])) $ids[(int)$row['student_id']] = true;
		}
		foreach ($d['paid_count_students'] as $row) {
			if (!empty($row['student_id'])) $ids[(int)$row['student_id']] = true;
		}
		return array_keys($ids);
	}

	private function _fetch_students_by_ids($student_ids, $q, $offset, $limit)
	{
		if (!count($student_ids)) {
			return array('rows' => array(), 'total' => 0);
		}

		$this->db->from('students');
		$this->db->where_in('students.student_id', $student_ids);
		$this->db->where('students.status', '1');
		$this->_apply_student_search_q($q);
		$total = (int)$this->db->count_all_results();

		if ($total < 1) return array('rows' => array(), 'total' => 0);

		$this->db->select('students.*, classes.name as class_name, classes.session as session, campuses.campus_name, courses.course_name', false);
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'left');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
		$this->db->join('courses', 'courses.course_id=students.course_id', 'left');
		$this->db->where_in('students.student_id', $student_ids);
		$this->db->where('students.status', '1');
		$this->_apply_student_search_q($q);
		$this->db->order_by('CAST(students.roll_no AS UNSIGNED)', 'ASC', false);
		$this->db->order_by('students.roll_no', 'ASC');
		$this->db->limit($limit, $offset);
		return array('rows' => $this->db->get()->result_array(), 'total' => $total);
	}

	private function _apply_student_search_q($q)
	{
		if ($q === '') return;
		$this->db->group_start();
		$this->db->like('students.first_name', $q);
		$this->db->or_like('students.last_name', $q);
		$this->db->or_like('students.father_name', $q);
		$this->db->or_like('students.cnic', $q);
		$this->db->or_like('students.roll_no', $q);
		$this->db->or_like('students.mobile', $q);
		$this->db->group_end();
	}

	public function recovery_entries($recovery_id = 0)
	{
		$recovery_id = (int)$recovery_id;
		if (!$recovery_id) $this->_json(array('success' => false, 'message' => 'recovery_id required'), 422);

		$kind = $this->input->get('kind');
		if ($kind !== 'paid') $kind = 'all';
		$id = (int)$this->input->get('id');
		if (!$id) $id = 1;
		$commission = $this->input->get('commission');
		if ($commission === null) $commission = 0;

		$from_date = $this->input->get('from');
		$to_date = $this->input->get('to');
		if ($from_date === null || $from_date === '') $from_date = date('Y-m-01');
		if ($to_date === null || $to_date === '') $to_date = date('Y-m-t');

		$recovery_rows = $this->db->get_where('recovery_management', array('recovery_management_id' => $recovery_id))->result_array();
		if (!count($recovery_rows)) $this->_json(array('success' => false, 'message' => 'Recovery task not found'), 404);
		$campus_ids = explode(',', $recovery_rows[0]['campus_ids']);
		$course_ids = explode(',', $recovery_rows[0]['course_id']);

		if ($kind === 'paid') {
			$data = $this->_recovery_all_paid_entries($id, $course_ids, $campus_ids, $from_date, $to_date);
		} else {
			$data = $this->_recovery_all_entries($id, $course_ids, $campus_ids, $from_date, $to_date);
		}

		$this->_json(array(
			'success' => true,
			'data' => $data,
			'from_date' => $from_date,
			'to_date' => $to_date,
			'commission' => $commission,
		));
	}

	/** Port of Recovery_management::all_entries() id branches 1-4. */
	private function _recovery_all_entries($id, $course_ids, $campus_ids, $from_date, $to_date)
	{
		$students = array();
		$contracts = array();

		if ($id == 1) {
			$this->db->select("payments.id as fee_id,'0' as isdel, 'UnPaid' as Fstatus,payments.split as split,payments.amount, payments.dead_line, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee,payments.paid_date");
			$this->db->from('payments');
			$this->db->join('students', 'students.student_id=payments.student_id', 'INNER');
			$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
			$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
			$this->db->join('courses', 'courses.course_id=students.course_id', 'INNER');
			$this->db->where_in('courses.course_id', $course_ids);
			$this->db->where_in('campuses.campus_id', $campus_ids);
			$this->db->where(array('payments.dead_line<=' => $to_date, 'payments.paid' => 0, 'students.status' => 1));
			$unpaid_payments_students = $this->db->get()->result_array();

			$this->db->select("payments.id as fee_id,'0' as isdel, 'UnPaid' as Fstatus,payments.split as split,payments.amount, payments.dead_line, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee,payments.paid_date");
			$this->db->from('payments');
			$this->db->join('students', 'students.student_id=payments.student_id', 'INNER');
			$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
			$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
			$this->db->join('courses', 'courses.course_id=students.course_id', 'INNER');
			$this->db->where_in('courses.course_id', $course_ids);
			$this->db->where_in('campuses.campus_id', $campus_ids);
			$this->db->where(array('payments.paid_date>' => $to_date, 'payments.paid' => 1, 'students.status' => 1));
			$unpaid_payments_students_during_last_month = $this->db->get()->result_array();

			$this->db->select("*,payments.id as fee_id,'0' as isdel,'UnPaid' as Fstatus,payments.split as split");
			$this->db->from('payments');
			$this->db->join('contracts', 'contracts.contract_id=payments.contract_id', 'INNER');
			$this->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'INNER');
			$this->db->join('campuses', 'contracts.campus_id=campuses.campus_id', 'INNER');
			$this->db->join('courses', 'courses.course_id=contracts.course_id', 'INNER');
			$this->db->where_in('courses.course_id', $course_ids);
			$this->db->where_in('campuses.campus_id', $campus_ids);
			$this->db->where(array('payments.dead_line<=' => $to_date, 'payments.paid' => 0, 'payments.amount !=' => '4500'));
			$unpaid_payments_contracts = $this->db->get()->result_array();

			$this->db->select("payments.id as fee_id,'0' as isdel, 'Paid' as Fstatus,payments.amount,payments.split as split, payments.dead_line, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee,payments.paid_date");
			$this->db->from('payments');
			$this->db->join('students', 'students.student_id=payments.student_id', 'INNER');
			$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
			$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
			$this->db->join('courses', 'courses.course_id=students.course_id', 'INNER');
			$this->db->where_in('courses.course_id', $course_ids);
			$this->db->where_in('campuses.campus_id', $campus_ids);
			$this->db->where(array('payments.actual_paid_date>=' => $from_date, 'payments.actual_paid_date<=' => $to_date, 'payments.paid' => 1, 'students.status' => 1));
			$paid_payments_students = $this->db->get()->result_array();

			$this->db->select("*,'0' as isdel,payments.id as fee_id,'Paid' as Fstatus,payments.split as split");
			$this->db->from('payments');
			$this->db->join('contracts', 'contracts.contract_id=payments.contract_id', 'INNER');
			$this->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'INNER');
			$this->db->join('campuses', 'contracts.campus_id=campuses.campus_id', 'INNER');
			$this->db->join('courses', 'courses.course_id=contracts.course_id', 'INNER');
			$this->db->where_in('courses.course_id', $course_ids);
			$this->db->where_in('campuses.campus_id', $campus_ids);
			$this->db->where(array('payments.actual_paid_date>=' => $from_date, 'payments.actual_paid_date<=' => $to_date, 'payments.paid' => 1, 'payments.amount !=' => '4500'));
			$paid_payments_contracts = $this->db->get()->result_array();

			$this->db->select("update_payment_requests.add_by,update_payment_requests.last_edit,0 as split,update_payment_requests.del as isdel,update_payment_requests.reason as delreason,update_payment_requests.id as fee_id,'shifted' as Fstatus,update_payment_requests.amount, update_payment_requests.dead_line, update_payment_requests.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee,payments.paid_date");
			$this->db->from('update_payment_requests');
			$this->db->join('payments', 'payments.challan_no=update_payment_requests.challan_no', 'left');
			$this->db->join('students', 'students.student_id=update_payment_requests.student_id', 'INNER');
			$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
			$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
			$this->db->join('courses', 'courses.course_id=students.course_id', 'INNER');
			$this->db->where_in('courses.course_id', $course_ids);
			$this->db->where_in('campuses.campus_id', $campus_ids);
			$this->db->where(array('update_payment_requests.update_date>=' => $from_date, 'update_payment_requests.update_date<=' => $to_date, 'update_payment_requests.ok_by_admin' => 1, 'del' => 0));
			$shifted_payments_students = $this->db->get()->result_array();

			$this->db->select("update_payment_requests.add_by,update_payment_requests.last_edit,0 as split,update_payment_requests.del as isdel,update_payment_requests.reason as delreason,payments.id as fee_id,'shifted' as Fstatus");
			$this->db->from('update_payment_requests');
			$this->db->join('payments', 'payments.challan_no=update_payment_requests.challan_no', 'left');
			$this->db->join('contracts', 'contracts.contract_id=update_payment_requests.contract_id', 'INNER');
			$this->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'INNER');
			$this->db->join('campuses', 'contracts.campus_id=campuses.campus_id', 'INNER');
			$this->db->join('courses', 'courses.course_id=contracts.course_id', 'INNER');
			$this->db->where_in('courses.course_id', $course_ids);
			$this->db->where_in('campuses.campus_id', $campus_ids);
			$this->db->where(array('update_payment_requests.update_date>=' => $from_date, 'update_payment_requests.update_date<=' => $to_date, 'update_payment_requests.ok_by_admin' => 1, 'payments.amount !=' => '4500', 'del' => 0));
			$shifted_payments_contracts = $this->db->get()->result_array();

			$students = array_merge($unpaid_payments_students, $paid_payments_students, $shifted_payments_students, $unpaid_payments_students_during_last_month);
			$contracts = array_merge($unpaid_payments_contracts, $paid_payments_contracts, $shifted_payments_contracts);
		} elseif ($id == 2) {
			$this->db->select("payments.id as fee_id,'Paid' as Fstatus,payments.amount, payments.dead_line, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee,payments.paid_date");
			$this->db->from('payments');
			$this->db->join('students', 'students.student_id=payments.student_id', 'INNER');
			$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
			$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
			$this->db->join('courses', 'courses.course_id=students.course_id', 'INNER');
			$this->db->where_in('courses.course_id', $course_ids);
			$this->db->where_in('campuses.campus_id', $campus_ids);
			$this->db->where(array('payments.paid_date>=' => $from_date, 'payments.paid_date<=' => $to_date, 'payments.paid' => 1, 'students.status' => 1));
			$students = $this->db->get()->result_array();

			$this->db->select("*,'Paid' as Fstatus,payments.id as fee_id");
			$this->db->from('payments');
			$this->db->join('contracts', 'contracts.contract_id=payments.contract_id', 'INNER');
			$this->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'INNER');
			$this->db->join('campuses', 'contracts.campus_id=campuses.campus_id', 'INNER');
			$this->db->join('courses', 'courses.course_id=contracts.course_id', 'INNER');
			$this->db->where_in('courses.course_id', $course_ids);
			$this->db->where_in('campuses.campus_id', $campus_ids);
			$this->db->where(array('payments.paid_date>=' => $from_date, 'payments.paid_date<=' => $to_date, 'payments.paid' => 1));
			$contracts = $this->db->get()->result_array();
		} elseif ($id == 3 || $id == 4) {
			$this->db->select("update_payment_requests.add_by,update_payment_requests.last_edit,update_payment_requests.del as isdel,update_payment_requests.reason as delreason,payments.id as fee_id,'shifted' as Fstatus,payments.amount, payments.dead_line, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee,payments.paid_date");
			$this->db->from('update_payment_requests');
			$this->db->join('payments', 'payments.challan_no=update_payment_requests.challan_no', 'left');
			$this->db->join('students', 'students.student_id=update_payment_requests.student_id', 'INNER');
			$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
			$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
			$this->db->join('courses', 'courses.course_id=students.course_id', 'INNER');
			$this->db->where_in('courses.course_id', $course_ids);
			$this->db->where_in('campuses.campus_id', $campus_ids);
			$this->db->where(array('update_payment_requests.old_dead_line>=' => $from_date, 'update_payment_requests.old_dead_line<=' => $to_date, 'update_payment_requests.ok_by_admin' => 1, 'del' => 0));
			$students = $this->db->get()->result_array();

			$this->db->select("update_payment_requests.add_by,update_payment_requests.last_edit,update_payment_requests.del as isdel,update_payment_requests.reason as delreason,payments.id as fee_id,'shifted' as Fstatus,payments.paid_date");
			$this->db->from('update_payment_requests');
			$this->db->join('payments', 'payments.challan_no=update_payment_requests.challan_no', 'left');
			$this->db->join('contracts', 'contracts.contract_id=update_payment_requests.contract_id', 'INNER');
			$this->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'INNER');
			$this->db->join('campuses', 'contracts.campus_id=campuses.campus_id', 'INNER');
			$this->db->join('courses', 'courses.course_id=contracts.course_id', 'INNER');
			$this->db->where_in('courses.course_id', $course_ids);
			$this->db->where_in('campuses.campus_id', $campus_ids);
			$this->db->where(array('update_payment_requests.old_dead_line>=' => $from_date, 'update_payment_requests.old_dead_line<=' => $to_date, 'update_payment_requests.ok_by_admin' => 1, 'del' => 0));
			$contracts = $this->db->get()->result_array();
		}

		return array('students' => $students, 'contracts' => $contracts);
	}

	/** Port of Recovery_management::all_paid_entries() id branches 1-2 (3 & 4 are empty in legacy). */
	private function _recovery_all_paid_entries($id, $course_ids, $campus_ids, $from_date, $to_date)
	{
		$students = array();
		$contracts = array();

		if ($id == 1) {
			$this->db->select("payments.id as fee_id,'0' as isdel, 'UnPaid' as Fstatus,payments.split as split,payments.amount, payments.dead_line, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee,payments.paid_date");
			$this->db->from('payments');
			$this->db->join('students', 'students.student_id=payments.student_id', 'INNER');
			$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
			$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
			$this->db->join('courses', 'courses.course_id=students.course_id', 'INNER');
			$this->db->where_in('courses.course_id', $course_ids);
			$this->db->where_in('campuses.campus_id', $campus_ids);
			$this->db->where(array('payments.paid_date>' => $to_date, 'payments.paid' => 1, 'students.status' => 1));
			$unpaid_payments_students_during_last_month = $this->db->get()->result_array();

			$this->db->select("payments.id as fee_id,'0' as isdel, 'Paid' as Fstatus,payments.amount,payments.split as split, payments.dead_line, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee,payments.paid_date");
			$this->db->from('payments');
			$this->db->join('students', 'students.student_id=payments.student_id', 'INNER');
			$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
			$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
			$this->db->join('courses', 'courses.course_id=students.course_id', 'INNER');
			$this->db->where_in('courses.course_id', $course_ids);
			$this->db->where_in('campuses.campus_id', $campus_ids);
			$this->db->where(array('payments.actual_paid_date>=' => $from_date, 'payments.actual_paid_date<=' => $to_date, 'payments.paid' => 1, 'students.status' => 1));
			$paid_payments_students = $this->db->get()->result_array();

			$this->db->select("*,'0' as isdel,payments.id as fee_id,'Paid' as Fstatus,payments.split as split");
			$this->db->from('payments');
			$this->db->join('contracts', 'contracts.contract_id=payments.contract_id', 'INNER');
			$this->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'INNER');
			$this->db->join('campuses', 'contracts.campus_id=campuses.campus_id', 'INNER');
			$this->db->join('courses', 'courses.course_id=contracts.course_id', 'INNER');
			$this->db->where_in('courses.course_id', $course_ids);
			$this->db->where_in('campuses.campus_id', $campus_ids);
			$this->db->where(array('payments.actual_paid_date>=' => $from_date, 'payments.actual_paid_date<=' => $to_date, 'payments.paid' => 1, 'payments.amount !=' => '4500'));
			$paid_payments_contracts = $this->db->get()->result_array();

			$students = array_merge($paid_payments_students, $unpaid_payments_students_during_last_month);
			$contracts = array_merge($paid_payments_contracts);
		} elseif ($id == 2) {
			$this->db->select("payments.id as fee_id,'Paid' as Fstatus,payments.amount, payments.dead_line, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee,payments.paid_date");
			$this->db->from('payments');
			$this->db->join('students', 'students.student_id=payments.student_id', 'INNER');
			$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
			$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
			$this->db->join('courses', 'courses.course_id=students.course_id', 'INNER');
			$this->db->where_in('courses.course_id', $course_ids);
			$this->db->where_in('campuses.campus_id', $campus_ids);
			$this->db->where(array('payments.paid_date>=' => $from_date, 'payments.paid_date<=' => $to_date, 'payments.paid' => 1, 'students.status' => 1));
			$students = $this->db->get()->result_array();

			$this->db->select("*,'Paid' as Fstatus,payments.id as fee_id");
			$this->db->from('payments');
			$this->db->join('contracts', 'contracts.contract_id=payments.contract_id', 'INNER');
			$this->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'INNER');
			$this->db->join('campuses', 'contracts.campus_id=campuses.campus_id', 'INNER');
			$this->db->join('courses', 'courses.course_id=contracts.course_id', 'INNER');
			$this->db->where_in('courses.course_id', $course_ids);
			$this->db->where_in('campuses.campus_id', $campus_ids);
			$this->db->where(array('payments.paid_date>=' => $from_date, 'payments.paid_date<=' => $to_date, 'payments.paid' => 1));
			$contracts = $this->db->get()->result_array();
		}

		return array('students' => $students, 'contracts' => $contracts);
	}

	public function recovery_dues($recovery_id = 0)
	{
		$recovery_id = (int)$recovery_id;
		if (!$recovery_id) $this->_json(array('success' => false, 'message' => 'recovery_id required'), 422);

		$filter = (int)$this->input->get('filter');
		if ($this->input->get('filter') === null) $filter = 0;

		$from_date = $this->input->get('from');
		$to_date = $this->input->get('to');
		if ($from_date === null || $from_date === '') $from_date = date('Y-m-01');
		if ($to_date === null || $to_date === '') $to_date = date('Y-m-t');

		$page = max(1, (int)$this->input->get('page'));
		$page_size = (int)$this->input->get('page_size');
		if ($page_size <= 0) $page_size = 25;
		if ($page_size > 5000) $page_size = 5000;
		$q = strtolower(trim((string)$this->input->get('q')));

		$recovery_rows = $this->db->get_where('recovery_management', array('recovery_management_id' => $recovery_id))->result_array();
		if (!count($recovery_rows)) $this->_json(array('success' => false, 'message' => 'Recovery task not found'), 404);
		$campus_ids = explode(',', $recovery_rows[0]['campus_ids']);
		$course_ids = explode(',', $recovery_rows[0]['course_id']);

		$this->db->select("payments.id as fee_id,'0' as isdel, 'UnPaid' as Fstatus,payments.amount, payments.dead_line, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee");
		$this->db->from('payments');
		$this->db->join('students', 'students.student_id=payments.student_id', 'INNER');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
		$this->db->join('courses', 'courses.course_id=students.course_id', 'INNER');
		$this->db->where_in('courses.course_id', $course_ids);
		$this->db->where_in('campuses.campus_id', $campus_ids);
		$this->db->where(array('payments.dead_line<=' => $to_date, 'payments.paid' => 0, 'students.status' => 1));
		$this->db->group_by('students.student_id');
		$unpaid_payments_students = $this->db->get()->result_array();

		$this->db->select("*,payments.id as fee_id,'0' as isdel,'UnPaid' as Fstatus,contractors.name,contracts.contract_id,contracts.contract_name");
		$this->db->from('payments');
		$this->db->join('contracts', 'contracts.contract_id=payments.contract_id', 'INNER');
		$this->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'INNER');
		$this->db->join('campuses', 'contracts.campus_id=campuses.campus_id', 'INNER');
		$this->db->join('courses', 'courses.course_id=contracts.course_id', 'INNER');
		$this->db->where_in('courses.course_id', $course_ids);
		$this->db->where_in('campuses.campus_id', $campus_ids);
		$this->db->where(array('payments.dead_line<=' => $to_date, 'payments.paid' => 0));
		$this->db->group_by('contracts.contract_id');
		$unpaid_payments_contracts = $this->db->get()->result_array();

		$all_fee_ids = array_merge(
			array_column($unpaid_payments_students, 'fee_id'),
			array_column($unpaid_payments_contracts, 'fee_id')
		);
		$latest_remarks = $this->_batch_latest_fee_remarks($all_fee_ids);

		$counts = array('call' => 0, 'will_pay' => 0, 'will_pay_on' => 0, 'cell_off' => 0, 'struck_of' => 0, 'new' => 0);
		$this->_bucket_fee_dues_comments_map($unpaid_payments_students, $counts, $latest_remarks);
		$this->_bucket_fee_dues_comments_map($unpaid_payments_contracts, $counts, $latest_remarks);

		$combined = array();
		foreach ($unpaid_payments_students as $due) {
			if ($q !== '' && !$this->_fee_dues_search_match($due, 'student', $q)) continue;
			$latest = isset($latest_remarks[$due['fee_id']]) ? $latest_remarks[$due['fee_id']] : null;
			if ($this->_fee_dues_filter_match($filter, $latest)) {
				$combined[] = array('kind' => 'student', 'row' => $due, 'latest_remark' => $latest);
			}
		}
		foreach ($unpaid_payments_contracts as $due) {
			if ($q !== '' && !$this->_fee_dues_search_match($due, 'contract', $q)) continue;
			$latest = isset($latest_remarks[$due['fee_id']]) ? $latest_remarks[$due['fee_id']] : null;
			if ($this->_fee_dues_filter_match($filter, $latest)) {
				$combined[] = array('kind' => 'contract', 'row' => $due, 'latest_remark' => $latest);
			}
		}

		$total_filtered = count($combined);
		$total_pages = max(1, (int)ceil($total_filtered / $page_size));
		if ($page > $total_pages) $page = $total_pages;
		$page_slice = array_slice($combined, ($page - 1) * $page_size, $page_size);

		$page_student_ids = array();
		$page_contract_ids = array();
		$page_fee_ids = array();
		foreach ($page_slice as $item) {
			$page_fee_ids[] = (int)$item['row']['fee_id'];
			if ($item['kind'] === 'student') {
				$page_student_ids[] = (int)$item['row']['student_id'];
			} else {
				$page_contract_ids[] = (int)$item['row']['contract_id'];
			}
		}

		$student_payments_map = $this->_batch_student_payments_for_dues($page_student_ids);
		$contract_payments_map = $this->_batch_contract_payments($page_contract_ids);
		$remarks_map = $this->_batch_remarks_by_fee($page_fee_ids);

		$students_out = array();
		$contracts_out = array();
		foreach ($page_slice as $item) {
			$due = $item['row'];
			$kind = $item['kind'];
			$fee_id = (int)$due['fee_id'];
			if ($kind === 'student') {
				$sid = (int)$due['student_id'];
				$payments = isset($student_payments_map[$sid]) ? $student_payments_map[$sid] : array();
				$fee_info = $this->_compute_fee_dues_info($payments, false, $due['total_fee']);
				$students_out[] = array_merge($due, array(
					'kind' => 'student',
					'fee_info' => $fee_info,
					'remarks' => isset($remarks_map[$fee_id]) ? $remarks_map[$fee_id] : array(),
					'latest_remark' => $item['latest_remark'],
				));
			} else {
				$cid = (int)$due['contract_id'];
				$payments = isset($contract_payments_map[$cid]) ? $contract_payments_map[$cid] : array();
				$fee_info = $this->_compute_fee_dues_info($payments, true, isset($due['total_fee']) ? $due['total_fee'] : 0);
				$contracts_out[] = array_merge($due, array(
					'kind' => 'contract',
					'fee_info' => $fee_info,
					'remarks' => isset($remarks_map[$fee_id]) ? $remarks_map[$fee_id] : array(),
					'latest_remark' => $item['latest_remark'],
				));
			}
		}

		$this->_json(array(
			'success' => true,
			'from_date' => $from_date,
			'to_date' => $to_date,
			'filter' => $filter,
			'recovery_id' => $recovery_id,
			'students' => $students_out,
			'contracts' => $contracts_out,
			'fee_dues_students_count' => count($unpaid_payments_students),
			'fee_dues_contractors_count' => count($unpaid_payments_contracts),
			'total_fee_entries' => count($unpaid_payments_students) + count($unpaid_payments_contracts),
			'counts' => $counts,
			'pagination' => array(
				'page' => $page,
				'page_size' => $page_size,
				'total' => $total_filtered,
				'total_pages' => $total_pages,
			),
		));
	}

	public function recovery_dues_comment()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			$this->_json(array('success' => false, 'message' => 'POST required'), 405);
		}

		$body = $this->_body();
		$fee_id = (int)(isset($body['fee_id']) ? $body['fee_id'] : 0);
		$comment = trim((string)(isset($body['comment']) ? $body['comment'] : ''));
		$selected_date = trim((string)(isset($body['selected_date']) ? $body['selected_date'] : ''));
		$description = trim((string)(isset($body['description']) ? $body['description'] : ''));

		if (!$fee_id || $comment === '') {
			$this->_json(array('success' => false, 'message' => 'fee_id and comment required'), 422);
		}

		if ($comment === 'Will Pay On' && $selected_date === '') {
			$this->_json(array('success' => false, 'message' => 'Next due date is required for Will Pay On'), 422);
		}

		$paid_on_date = $selected_date !== '' ? $selected_date : date('Y-m-d');

		$original_fee_entry = $this->db->get_where('payments', array('id' => $fee_id))->row_array();
		if (!$original_fee_entry) {
			$this->_json(array('success' => false, 'message' => 'Fee entry not found'), 404);
		}

		$entries = '';
		if (!empty($original_fee_entry['student_id'])) {
			$this->db->select('challan_no');
			$this->db->from('payments');
			$this->db->where('student_id', (int)$original_fee_entry['student_id']);
			$this->db->where('paid', 0);
			$this->db->where('dead_line<', date('Y-m-d'));
			$payments = $this->db->get()->result_array();
			foreach ($payments as $astx) {
				if (!empty($astx['challan_no'])) $entries .= $astx['challan_no'] . ',';
			}
		}

		$add_by = isset($this->current_user['name']) ? $this->current_user['name'] : 'POS User';
		if (empty($add_by)) {
			$add_by = trim(
				(isset($this->current_user['first_name']) ? $this->current_user['first_name'] : '') . ' ' .
				(isset($this->current_user['last_name']) ? $this->current_user['last_name'] : '')
			);
		}
		if (empty($add_by)) $add_by = 'POS User';

		$full_comment = $comment . ' ' . $selected_date . ' ' . $description . '  for Challan no (' . $entries . ') ';
		$this->db->insert('fees_remarks', array(
			'fee_id' => $fee_id,
			'comment' => $full_comment,
			'paid_on_date' => $paid_on_date,
			'add_by' => $add_by,
			'clear_status' => '1',
			'date' => date('Y-m-d H:i:s'),
		));

		$remarks = $this->db->get_where('fees_remarks', array('fee_id' => $fee_id))->result_array();

		$this->_json(array(
			'success' => true,
			'fee_id' => $fee_id,
			'remarks' => $remarks,
		));
	}

	private function _fee_dues_search_match($due, $kind, $q)
	{
		$parts = array(
			isset($due['first_name']) ? $due['first_name'] : '',
			isset($due['last_name']) ? $due['last_name'] : '',
			isset($due['name']) ? $due['name'] : '',
			isset($due['contract_name']) ? $due['contract_name'] : '',
			isset($due['roll_no']) ? $due['roll_no'] : '',
			isset($due['cnic']) ? $due['cnic'] : '',
			isset($due['mobile']) ? $due['mobile'] : '',
			isset($due['campus_name']) ? $due['campus_name'] : '',
			isset($due['class_name']) ? $due['class_name'] : '',
		);
		$hay = strtolower(implode(' ', $parts));
		return strpos($hay, $q) !== false;
	}

	private function _batch_latest_fee_remarks($fee_ids)
	{
		$fee_ids = array_values(array_unique(array_map('intval', $fee_ids)));
		$fee_ids = array_filter($fee_ids, function ($id) { return $id > 0; });
		if (!count($fee_ids)) return array();

		$rows = $this->db->query(
			'SELECT fr.* FROM fees_remarks fr INNER JOIN (
				SELECT fee_id, MAX(fee_remarks_id) AS max_id
				FROM fees_remarks WHERE fee_id IN (' . implode(',', $fee_ids) . ')
				GROUP BY fee_id
			) t ON fr.fee_remarks_id = t.max_id'
		)->result_array();

		$map = array();
		foreach ($rows as $row) {
			$map[(int)$row['fee_id']] = $row;
		}
		return $map;
	}

	private function _batch_remarks_by_fee($fee_ids)
	{
		$fee_ids = array_values(array_unique(array_map('intval', $fee_ids)));
		$fee_ids = array_filter($fee_ids, function ($id) { return $id > 0; });
		if (!count($fee_ids)) return array();

		$this->db->where_in('fee_id', $fee_ids);
		$this->db->order_by('fee_remarks_id', 'ASC');
		$rows = $this->db->get('fees_remarks')->result_array();

		$map = array();
		foreach ($rows as $row) {
			$fid = (int)$row['fee_id'];
			if (!isset($map[$fid])) $map[$fid] = array();
			$map[$fid][] = $row;
		}
		return $map;
	}

	private function _group_student_payments_from_rows($rows)
	{
		$merged_paid = array();
		$others = array();
		foreach ($rows as $p) {
			$merged = $p['merged_challan'];
			$actual = (float)$p['actual_amount'];
			if ($merged !== null && $merged !== '' && $actual > 0) {
				if (!isset($merged_paid[$merged])) $merged_paid[$merged] = $p;
			} elseif ($merged === null || $merged === '' || ($merged !== null && $actual <= 0)) {
				$others[] = $p;
			}
		}
		$merged_vals = array_values($merged_paid);
		usort($merged_vals, function ($a, $b) { return strcmp($a['dead_line'], $b['dead_line']); });
		usort($others, function ($a, $b) { return strcmp($a['dead_line'], $b['dead_line']); });
		return array_merge($merged_vals, $others);
	}

	private function _batch_student_payments_for_dues($student_ids)
	{
		$student_ids = array_values(array_unique(array_map('intval', $student_ids)));
		$student_ids = array_filter($student_ids, function ($id) { return $id > 0; });
		if (!count($student_ids)) return array();

		$this->db->select('id, student_id, contract_id, amount, actual_amount, paid, paid_date, dead_line, payment_plan, merged_challan, challan_no');
		$this->db->from('payments');
		$this->db->where_in('student_id', $student_ids);
		$this->db->order_by('student_id', 'ASC');
		$this->db->order_by('dead_line', 'ASC');
		$all = $this->db->get()->result_array();

		$by_student = array();
		foreach ($all as $p) {
			$sid = (int)$p['student_id'];
			if (!isset($by_student[$sid])) $by_student[$sid] = array();
			$by_student[$sid][] = $p;
		}

		$out = array();
		foreach ($by_student as $sid => $rows) {
			$out[$sid] = $this->_group_student_payments_from_rows($rows);
		}
		return $out;
	}

	private function _batch_contract_payments($contract_ids)
	{
		$contract_ids = array_values(array_unique(array_map('intval', $contract_ids)));
		$contract_ids = array_filter($contract_ids, function ($id) { return $id > 0; });
		if (!count($contract_ids)) return array();

		$this->db->select('id, student_id, contract_id, amount, actual_amount, paid, paid_date, dead_line, payment_plan, merged_challan, challan_no');
		$this->db->from('payments');
		$this->db->where_in('contract_id', $contract_ids);
		$this->db->order_by('contract_id', 'ASC');
		$this->db->order_by('dead_line', 'ASC');
		$all = $this->db->get()->result_array();

		$out = array();
		foreach ($all as $p) {
			$cid = (int)$p['contract_id'];
			if (!isset($out[$cid])) $out[$cid] = array();
			$out[$cid][] = $p;
		}
		return $out;
	}

	private function _fee_dues_filter_label($filter)
	{
		if ($filter === 1) return 'Call Not Attended';
		if ($filter === 2) return 'Will Pay On';
		if ($filter === 3) return 'Cell Off';
		if ($filter === 4) return 'Struck of now';
		if ($filter === 6) return 'Will Pay On today';
		if ($filter === 5) return 'Fresh';
		return '';
	}

	/** Port of dashboard/fee_dues_comments.php row filter logic. */
	private function _fee_dues_filter_match($filter, $rem)
	{
		if ((int)$filter === 0) return true;

		$filterds = $this->_fee_dues_filter_label((int)$filter);
		$currentdatehere = date('Y-m-d');
		$comment = is_array($rem) && isset($rem['comment']) ? (string)$rem['comment'] : '';
		$paid_on = is_array($rem) && isset($rem['paid_on_date']) ? (string)$rem['paid_on_date'] : '';

		if ($filterds === 'Fresh') {
			if (!is_array($rem) || !isset($rem['comment']) || trim((string)$rem['comment']) === '') {
				return true;
			}
			return strpos($comment, 'Call Not Attended') === false
				&& strpos($comment, 'Will Pay On') === false
				&& strpos($comment, 'Cell Off') === false
				&& strpos($comment, 'Struck of now') === false;
		}

		if ($filterds === 'Will Pay On today') {
			return strpos($comment, 'Will Pay On') !== false && $paid_on !== '' && $paid_on < $currentdatehere;
		}

		if (strpos($comment, $filterds) !== false && $filterds !== 'Will Pay On today' && $filterds === 'Will Pay On') {
			return $paid_on !== '' && $paid_on > $currentdatehere;
		}

		if (strpos($comment, $filterds) !== false && $filterds !== 'Will Pay On today' && $filterds !== 'Will Pay On') {
			return true;
		}

		return false;
	}

	private function _increment_fee_dues_bucket(&$counts, $rem)
	{
		$filterd1 = 'Call Not Attended';
		$filterd2 = 'Will Pay On';
		$filterd3 = 'Cell Off';
		$filterd4 = 'Struck of now';
		$filterd5 = date('Y-m-d');

		if (is_array($rem) && isset($rem['comment'])) {
			$comment = (string)$rem['comment'];
			if (strpos($comment, $filterd1) !== false) {
				$counts['call']++;
			} elseif (strpos($comment, $filterd2) !== false && $rem['paid_on_date'] > $filterd5) {
				$counts['will_pay']++;
			} elseif (strpos($comment, $filterd3) !== false) {
				$counts['cell_off']++;
			} elseif (strpos($comment, $filterd4) !== false) {
				$counts['struck_of']++;
			} elseif (strpos($comment, $filterd2) !== false && $rem['paid_on_date'] < $filterd5) {
				$counts['will_pay_on']++;
			} else {
				$counts['new']++;
			}
		} else {
			$counts['new']++;
		}
	}

	private function _bucket_fee_dues_comments_map($rows, &$counts, $latest_remarks)
	{
		foreach ($rows as $due) {
			$fee_id = (int)$due['fee_id'];
			$rem = isset($latest_remarks[$fee_id]) ? $latest_remarks[$fee_id] : null;
			$this->_increment_fee_dues_bucket($counts, $rem);
		}
	}

	private function _compute_fee_dues_info($payments, $is_contract, $total_fee_field)
	{
		$total_fee = 0;
		$created_council_fee = 0;
		$submitted_council_fee = 0;
		$fee_decided_current_time = 0;
		$total_fee_submitted = 0;
		$unpaid_installments_current_time = 0;
		$paid_lines = array();
		$unpaid_lines = array();
		$today = date('Y-m-d');

		foreach ($payments as $payment) {
			if ($payment['payment_plan'] != 'consulation fee') {
				if ($is_contract) {
					if ($payment['paid'] == 1) {
						$total_fee += (float)$payment['actual_amount'];
					} else {
						$total_fee += (float)$payment['amount'];
					}
				} else {
					$total_fee += (float)$payment['amount'];
				}
			}
			if ($payment['payment_plan'] == 'consulation fee') {
				$created_council_fee += (float)$payment['amount'];
				if ($payment['paid'] == 1) {
					$submitted_council_fee += (float)$payment['actual_amount'];
				}
			}
			if ($payment['dead_line'] < $today) {
				$fee_decided_current_time += (float)$payment['amount'];
				if ($payment['paid'] == 0) {
					$unpaid_installments_current_time++;
				}
			}
			if ($payment['paid'] == 1 && $payment['payment_plan'] != 'consulation fee') {
				$total_fee_submitted += (float)$payment['actual_amount'];
				$paid_lines[] = $payment['actual_amount'] . ' Paid on ' . $payment['paid_date'];
			}
			if ($payment['paid'] == 0) {
				$overdue = $payment['dead_line'] < $today;
				$unpaid_lines[] = array(
					'text' => $payment['amount'] . ' Not Paid on ' . $payment['dead_line'],
					'overdue' => $overdue,
				);
			}
		}

		$remaining = $fee_decided_current_time - $total_fee_submitted;
		$pct_received = ($total_fee_submitted > 0 && $total_fee > 0)
			? round(($total_fee_submitted / $total_fee) * 100) : 0;
		$pct_decision = ($total_fee_submitted > 0 && $fee_decided_current_time > 0)
			? round(($total_fee_submitted / $fee_decided_current_time) * 100) : 0;

		return array(
			'total_fee' => $total_fee_field,
			'total_created_fee' => $total_fee,
			'total_created_council_fee' => $created_council_fee,
			'total_submitted_council_fee' => $submitted_council_fee,
			'fee_decided_current_time' => $fee_decided_current_time,
			'total_fee_submitted' => $total_fee_submitted,
			'remaining_fee_payable' => $remaining,
			'unpaid_installments_current_time' => $unpaid_installments_current_time,
			'percentage_fee_received' => $pct_received,
			'percentage_paid_according_to_decision' => $pct_decision,
			'paid_lines' => $paid_lines,
			'unpaid_lines' => $unpaid_lines,
		);
	}

	public function recovery_fine()
	{
		$method = $_SERVER['REQUEST_METHOD'];

		if ($method === 'POST') {
			$body = $this->_body();
			$rows = isset($body['rows']) && is_array($body['rows']) ? $body['rows'] : array();
			$collected_fine = 0;
			foreach ($rows as $row) {
				$collected_fine += isset($row['fine_amount']) ? (float)$row['fine_amount'] : 0;
			}
			$this->_json(array('success' => true, 'collected_fine' => $collected_fine, 'count' => count($rows)));
		}

		$recovery_id = (int)$this->input->get('recovery_id');
		$user_id = (int)$this->input->get('user_id');
		if (!$recovery_id || !$user_id) $this->_json(array('success' => false, 'message' => 'recovery_id and user_id required'), 422);

		$kind = $this->input->get('kind');
		if ($kind !== 'unverified') $kind = 'fine';

		$from_date = $this->input->get('from');
		$to_date = $this->input->get('to');
		if ($from_date === null || $from_date === '') $from_date = date('Y-m-01');
		if ($to_date === null || $to_date === '') $to_date = date('Y-m-t');

		$d = $this->_recovery_check_data($recovery_id, $user_id, $from_date, $to_date);
		if (!$d) $this->_json(array('success' => false, 'message' => 'Recovery task not found'), 404);

		if ($kind === 'unverified') {
			$rows = $d['unverified_paid_count_students'];
			$this->_json(array('success' => true, 'kind' => $kind, 'data' => $rows, 'count' => count($rows)));
		} else {
			$rows = $d['fine_students'];
			$collected_fine = 0;
			foreach ($rows as $row) {
				$collected_fine += isset($row['fine_amount']) ? (float)$row['fine_amount'] : 0;
			}
			$this->_json(array('success' => true, 'kind' => $kind, 'data' => $rows, 'count' => count($rows), 'collected_fine' => $collected_fine));
		}
	}

	// ------------------------------------------------------------------
	// Admission incentive tasks — ports Admission_management.php
	// ------------------------------------------------------------------

	public function admission_tasks()
	{
		$this->_assert_manage();

		$this->db->select('*');
		$this->db->from('admission_management_incentives');
		$this->db->join('designations', 'designations.designation_id=admission_management_incentives.designation_id', 'INNER');
		$this->db->join('departments', 'departments.department_id=designations.department_id', 'INNER');
		$rows = $this->db->get()->result_array();

		$out = array();
		foreach ($rows as $row) {
			$campus_ids = $this->_ids_from_csv($row['campus_ids']);
			$course_ids = $this->_ids_from_csv($row['course_id']);

			$rules = $this->db->get_where('admission_management_rules', array('admission_incentive_id' => $row['incentive_id']))->result_array();
			$users_raw = $this->_matching_users($row['designation_id']);
			$users = array();
			foreach ($users_raw as $u) {
				$users[] = array('user_id' => $u['user_id'], 'first_name' => $u['first_name'], 'last_name' => $u['last_name']);
			}

			$out[] = array(
				'incentive_id' => $row['incentive_id'],
				'designation_id' => $row['designation_id'],
				'designation_name' => $row['designation_name'],
				'department_name' => $row['department_name'],
				'campus_ids' => $row['campus_ids'],
				'campus_names' => $this->_campus_names($campus_ids),
				'course_id' => $row['course_id'],
				'course_names' => $this->_course_names($course_ids),
				'min_fee_amount' => $row['min_fee_amount'],
				'with_in_days' => $row['with_in_days'],
				'user_or_campus' => $row['user_or_campus'],
				'own_count' => $row['own_count'],
				'rules' => array_map(function ($r) {
					return array('start' => $r['start'], 'end' => $r['end'], 'comission' => $r['comission']);
				}, $rules),
				'users' => $users,
			);
		}

		$this->_json(array('success' => true, 'data' => $out));
	}

	public function admission_task($id = 0)
	{
		$id = (int)$id;
		$method = $_SERVER['REQUEST_METHOD'];

		if ($method === 'GET') {
			$this->_assert_manage();
			if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
			$row = $this->db->get_where('admission_management_incentives', array('incentive_id' => $id))->row_array();
			if (!$row) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
			$rules = $this->db->get_where('admission_management_rules', array('admission_incentive_id' => $id))->result_array();
			$row['campus_ids_arr'] = $this->_ids_from_csv($row['campus_ids']);
			$row['course_id_arr'] = $this->_ids_from_csv($row['course_id']);
			$this->_json(array('success' => true, 'data' => $row, 'rules' => $rules));
		}

		if ($method === 'POST') {
			$this->_assert_manage();
			$body = $this->_body();
			$rules_in = isset($body['rules']) && is_array($body['rules']) ? $body['rules'] : array();

			if ($id === 0) {
				// Port of Admission_management::set_comission()
				$designation_id = isset($body['designation_id']) ? $body['designation_id'] : null;
				$course_id_in = isset($body['course_id']) ? $body['course_id'] : array();
				$course_id = is_array($course_id_in) ? $this->_csv_ids($course_id_in) : '';
				$minimum_fee = isset($body['min_fee_amount']) ? $body['min_fee_amount'] : null;
				$within_days = isset($body['with_in_days']) ? $body['with_in_days'] : null;
				$user_or_campus = isset($body['user_or_campus']) ? $body['user_or_campus'] : null;
				$own_count = isset($body['own_count']) ? $body['own_count'] : null;
				$campus_ids = $this->_csv_ids(isset($body['campus_ids']) ? $body['campus_ids'] : array());

				$this->db->set('designation_id', $designation_id);
				$this->db->set('course_id', $course_id);
				$this->db->set('min_fee_amount', $minimum_fee);
				$this->db->set('with_in_days', $within_days);
				$this->db->set('campus_ids', $campus_ids);
				$this->db->set('user_or_campus', $user_or_campus);
				$this->db->set('own_count', $own_count);
				$this->db->insert('admission_management_incentives');
				$admission_management_id = $this->db->insert_id();

				foreach ($rules_in as $rule) {
					$this->db->set('admission_incentive_id', $admission_management_id);
					$this->db->set('start', isset($rule['start']) ? $rule['start'] : 0);
					$this->db->set('end', isset($rule['end']) ? $rule['end'] : 0);
					$this->db->set('comission', isset($rule['comission']) ? $rule['comission'] : 0);
					$this->db->insert('admission_management_rules');
				}

				$this->_json(array('success' => true, 'id' => (int)$admission_management_id));
			} else {
				// Port of Admission_management::update_comission(), extended to optionally
				// update the other assignment fields when the client sends them.
				$exists = $this->db->get_where('admission_management_incentives', array('incentive_id' => $id))->row_array();
				if (!$exists) $this->_json(array('success' => false, 'message' => 'Not found'), 404);

				$campus_ids = $this->_csv_ids(isset($body['campus_ids']) ? $body['campus_ids'] : array());
				$this->db->set('campus_ids', $campus_ids);
				if (isset($body['min_fee_amount'])) $this->db->set('min_fee_amount', $body['min_fee_amount']);
				if (isset($body['with_in_days'])) $this->db->set('with_in_days', $body['with_in_days']);
				if (isset($body['user_or_campus'])) $this->db->set('user_or_campus', $body['user_or_campus']);
				if (isset($body['own_count'])) $this->db->set('own_count', $body['own_count']);
				if (isset($body['course_id']) && is_array($body['course_id'])) $this->db->set('course_id', $this->_csv_ids($body['course_id']));
				$this->db->where('incentive_id', $id);
				$this->db->update('admission_management_incentives');

				$this->db->where('admission_incentive_id', $id);
				$this->db->delete('admission_management_rules');

				foreach ($rules_in as $rule) {
					$this->db->set('admission_incentive_id', $id);
					$this->db->set('start', isset($rule['start']) ? $rule['start'] : 0);
					$this->db->set('end', isset($rule['end']) ? $rule['end'] : 0);
					$this->db->set('comission', isset($rule['comission']) ? $rule['comission'] : 0);
					$this->db->insert('admission_management_rules');
				}

				$this->_json(array('success' => true, 'id' => $id));
			}
		}

		if ($method === 'DELETE') {
			$this->_assert_manage();
			if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
			$this->db->where('admission_incentive_id', $id);
			$this->db->delete('admission_management_rules');
			$this->db->where('incentive_id', $id);
			$this->db->delete('admission_management_incentives');
			$this->_json(array('success' => true));
		}

		$this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
	}

	public function admission_check($incentive_id = 0, $user_id = 0)
	{
		$incentive_id = (int)$incentive_id;
		$user_id = (int)$user_id;
		if (!$incentive_id || !$user_id) $this->_json(array('success' => false, 'message' => 'incentive_id and user_id required'), 422);

		$from_date = $this->input->get('from');
		$to_date = $this->input->get('to');
		if ($from_date === null || $from_date === '') $from_date = date('Y-m-01');
		if ($to_date === null || $to_date === '') $to_date = date('Y-m-t');

		$recovery_rows = $this->db->get_where('admission_management_incentives', array('incentive_id' => $incentive_id))->result_array();
		if (!count($recovery_rows)) $this->_json(array('success' => false, 'message' => 'Admission task not found'), 404);
		$recovery = $recovery_rows[0];
		$campus_ids = explode(',', $recovery['campus_ids']);

		$user_rows = $this->db->get_where('users', array('user_id' => $user_id))->result_array();
		if (!count($user_rows)) $this->_json(array('success' => false, 'message' => 'User not found'), 404);
		$user = $user_rows[0];
		$full_name = $user['first_name'] . ' ' . $user['last_name'];

		// Port of Admission_management::check_recovery()
		if ($recovery['user_or_campus'] == '0') {
			$this->db->select('payments.*,students.*');
			$this->db->from('payments');
			$this->db->join('students', 'students.student_id=payments.student_id', 'INNER');
			$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
			$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
			$this->db->where(array(
				'students.status' => 1,
				'students.entry_date>=' => $from_date,
				'students.entry_date<=' => $to_date,
				'students.add_by like' => '%' . $full_name . '%',
			));
			$this->db->group_by('payments.student_id');
			$total_paid_students = $this->db->get()->result_array();
			$total_unpaid_students = array();
		} else {
			$this->db->select('payments.*,students.*');
			$this->db->from('payments');
			$this->db->join('students', 'students.student_id=payments.student_id', 'INNER');
			$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
			$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
			$this->db->where(array(
				'students.status' => 1,
				'students.entry_date>=' => $from_date,
				'students.entry_date<=' => $to_date,
			));
			$this->db->where_in('campuses.campus_id', $campus_ids);
			if ($recovery['own_count'] == '0') {
				$this->db->where('students.add_by not like "%' . $full_name . '%"');
			}
			$this->db->group_by('payments.student_id');
			$total_paid_students = $this->db->get()->result_array();
			$total_unpaid_students = array();
		}

		$counted = 0;
		$uncounted = 0;
		foreach ($total_paid_students as $paid) {
			$this->db->select_sum('payments.actual_amount');
			$this->db->from('payments');
			$this->db->where("payments.student_id = '" . $paid['student_id'] . "'");
			$tot = $this->db->get()->result_array();

			if (count($tot) > 0) {
				if ($tot[0]['actual_amount'] > $recovery['min_fee_amount']) {
					$counted++;
				} else {
					$uncounted++;
				}
			}
		}

		$rules = $this->db->get_where('admission_management_rules', array('admission_incentive_id' => $incentive_id))->result_array();

		// Matches application/views/admission_management/check_admission_incentive.php
		$this->db->order_by('start', 'ASC');
		$comission_rule = $this->db->get_where('admission_management_rules', array(
			'admission_incentive_id' => $incentive_id,
			'start<=' => $counted,
			'end>=' => $counted,
		))->result_array();

		$com = 0;
		$incentive_amount = 0;
		if (count($comission_rule) > 0) {
			$com = $comission_rule[0]['comission'];
			$incentive_amount = $comission_rule[0]['comission'] * $counted;
		}

		$campuses = array();
		if (count($campus_ids)) {
			$campuses = $this->db->where_in('campus_id', $campus_ids)->get('campuses')->result_array();
		}

		$students = array();
		foreach ($total_paid_students as $s) {
			$students[] = array(
				'student_id' => $s['student_id'],
				'first_name' => $s['first_name'],
				'last_name' => $s['last_name'],
				'entry_date' => isset($s['entry_date']) ? $s['entry_date'] : null,
				'add_by' => isset($s['add_by']) ? $s['add_by'] : null,
			);
		}

		$this->_json(array(
			'success' => true,
			'from_date' => $from_date,
			'to_date' => $to_date,
			'recovery' => $recovery,
			'user' => $user,
			'counted' => $counted,
			'uncounted' => $uncounted,
			'total_students' => count($total_paid_students) + count($total_unpaid_students),
			'incentive_amount' => $incentive_amount,
			'commission_rate' => $com,
			'rules' => $rules,
			'campuses' => $campuses,
			'students' => $students,
		));
	}

	public function admission_entries($incentive_id = 0)
	{
		$incentive_id = (int)$incentive_id;
		if (!$incentive_id) $this->_json(array('success' => false, 'message' => 'incentive_id required'), 422);

		$id = $this->input->get('id');
		if ($id === null || $id === '') $id = 0;
		$id = (int)$id;
		$user_id = (int)$this->input->get('user_id');
		$incamount = $this->input->get('incamount');
		if ($incamount === null) $incamount = 0;

		$from_date = $this->input->get('from');
		$to_date = $this->input->get('to');
		if ($from_date === null || $from_date === '') $from_date = date('Y-m-01');
		if ($to_date === null || $to_date === '') $to_date = date('Y-m-t');

		$recovery_rows = $this->db->get_where('admission_management_incentives', array('incentive_id' => $incentive_id))->result_array();
		if (!count($recovery_rows)) $this->_json(array('success' => false, 'message' => 'Admission task not found'), 404);
		$recovery = $recovery_rows[0];
		$campus_ids = explode(',', $recovery['campus_ids']);

		// Port of Admission_management::all_entries()
		$this->db->select('*');
		$this->db->from('users');
		$this->db->join('campuses', 'campuses.campus_id=users.campus_id', 'INNER');
		$this->db->join('departments', 'departments.department_id=users.department_id', 'INNER');
		$this->db->join('designations', 'designations.designation_id=users.designation_id', 'left');
		$this->db->where('users.user_id', $user_id);
		$user_rows = $this->db->get()->result_array();
		$user = count($user_rows) ? $user_rows[0] : null;
		$full_name = $user ? ($user['first_name'] . ' ' . $user['last_name']) : '';

		if ($recovery['user_or_campus'] == '0') {
			$this->db->select('*,classes.name as class_name');
			$this->db->from('students');
			$this->db->join('payments', 'payments.student_id=students.student_id', 'INNER');
			$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
			$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
			$this->db->join('courses', 'courses.course_id=students.course_id', 'INNER');
			$this->db->where(array(
				'students.status' => 1,
				'students.entry_date>=' => $from_date,
				'students.entry_date<=' => $to_date,
				'students.add_by like' => '%' . $full_name . '%',
			));
			$this->db->group_by('payments.student_id');
			$rows = $this->db->get()->result_array();
		} else {
			$this->db->select('*,classes.name as class_name');
			$this->db->from('students');
			$this->db->join('payments', 'payments.student_id=students.student_id', 'left');
			$this->db->join('classes', 'classes.class_id=students.class_id', 'left');
			$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
			$this->db->join('courses', 'courses.course_id=students.course_id', 'left');
			$this->db->where(array(
				'students.status' => 1,
				'students.entry_date>=' => $from_date,
				'students.entry_date<=' => $to_date,
			));
			$this->db->where_in('campuses.campus_id', $campus_ids);
			if ($recovery['own_count'] == '0') {
				$this->db->where('students.add_by not like "%' . $full_name . '%"');
			}
			$this->db->group_by('payments.student_id');
			$rows = $this->db->get()->result_array();
		}

		if ($id == 1) {
			foreach ($rows as $key => $paid) {
				$this->db->select_sum('payments.actual_amount');
				$this->db->from('payments');
				$this->db->where("payments.student_id = '" . $paid['student_id'] . "'");
				$tot = $this->db->get()->result_array();
				if (count($tot) > 0) {
					if ($tot[0]['actual_amount'] > $recovery['min_fee_amount']) {
						// keep — counted admission
					} else {
						unset($rows[$key]);
					}
				}
			}
		} elseif ($id == 2) {
			foreach ($rows as $key => $paid) {
				$this->db->select_sum('payments.actual_amount');
				$this->db->from('payments');
				$this->db->where("payments.student_id = '" . $paid['student_id'] . "'");
				$tot = $this->db->get()->result_array();
				if (count($tot) > 0) {
					if ($tot[0]['actual_amount'] > $recovery['min_fee_amount']) {
						unset($rows[$key]);
					}
				}
			}
		}

		$rows = array_values($rows);

		$this->_json(array(
			'success' => true,
			'from_date' => $from_date,
			'to_date' => $to_date,
			'recoveryid' => $incentive_id,
			'incamount' => $incamount,
			'user' => $user,
			'data' => $rows,
			'contracts' => array(),
		));
	}

	/**
	 * CLI-only: dump recovery + admission KPIs for parity scripts.
	 * php index.php incentiveapi cli_verify [recovery_id] [user_id] [incentive_id] [adm_user_id] [from] [to]
	 */
	public function cli_verify($recovery_id = 106, $user_id = 175, $incentive_id = 12, $adm_user_id = 61, $from_date = '', $to_date = '')
	{
		if (!is_cli()) {
			show_404();
			return;
		}
		$recovery_id = (int)$recovery_id;
		$user_id = (int)$user_id;
		$incentive_id = (int)$incentive_id;
		$adm_user_id = (int)$adm_user_id;
		if ($from_date === '' || $from_date === '0') $from_date = date('Y-m-01');
		if ($to_date === '' || $to_date === '0') $to_date = date('Y-m-t');

		$out = array(
			'success' => true,
			'from_date' => $from_date,
			'to_date' => $to_date,
			'recovery' => null,
			'admission' => null,
		);

		$d = $this->_recovery_check_data($recovery_id, $user_id, $from_date, $to_date);
		if ($d) {
			$out['recovery'] = array(
				'recovery_id' => $recovery_id,
				'user_id' => $user_id,
				'kpi' => $this->_recovery_kpi($d),
				'bucket_counts' => array(
					'unpaid_students' => count($d['unpaid_payments_students']),
					'unpaid_contracts' => count($d['unpaid_payments_contracts']),
					'paid_students' => count($d['paid_payments_students']),
					'paid_contracts' => count($d['paid_payments_contracts']),
					'shifted_students' => count($d['shifted_payments_students']),
					'shifted_contracts' => count($d['shifted_payments_contracts']),
					'unpaid_last' => count($d['unpaid_payments_students_during_last_month']),
					'fine_rows' => count($d['fine_students']),
					'unverified' => count($d['unverified_paid_count_students']),
				),
			);
		}

		// Admission KPIs (same body as admission_check, without HTTP exit)
		$recovery_rows = $this->db->get_where('admission_management_incentives', array('incentive_id' => $incentive_id))->result_array();
		if (count($recovery_rows)) {
			$recovery = $recovery_rows[0];
			$campus_ids = explode(',', $recovery['campus_ids']);
			$user_rows = $this->db->get_where('users', array('user_id' => $adm_user_id))->result_array();
			if (count($user_rows)) {
				$user = $user_rows[0];
				$full_name = $user['first_name'] . ' ' . $user['last_name'];
				if ($recovery['user_or_campus'] == '0') {
					$this->db->select('payments.*,students.*');
					$this->db->from('payments');
					$this->db->join('students', 'students.student_id=payments.student_id', 'INNER');
					$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
					$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
					$this->db->where(array(
						'students.status' => 1,
						'students.entry_date>=' => $from_date,
						'students.entry_date<=' => $to_date,
						'students.add_by like' => '%' . $full_name . '%',
					));
					$this->db->group_by('payments.student_id');
					$total_paid_students = $this->db->get()->result_array();
				} else {
					$this->db->select('payments.*,students.*');
					$this->db->from('payments');
					$this->db->join('students', 'students.student_id=payments.student_id', 'INNER');
					$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
					$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
					$this->db->where(array(
						'students.status' => 1,
						'students.entry_date>=' => $from_date,
						'students.entry_date<=' => $to_date,
					));
					$this->db->where_in('campuses.campus_id', $campus_ids);
					if ($recovery['own_count'] == '0') {
						$this->db->where('students.add_by not like "%' . $full_name . '%"');
					}
					$this->db->group_by('payments.student_id');
					$total_paid_students = $this->db->get()->result_array();
				}
				$counted = 0;
				$uncounted = 0;
				foreach ($total_paid_students as $paid) {
					$this->db->select_sum('payments.actual_amount');
					$this->db->from('payments');
					$this->db->where("payments.student_id = '" . $paid['student_id'] . "'");
					$tot = $this->db->get()->result_array();
					if (count($tot) > 0) {
						if ($tot[0]['actual_amount'] > $recovery['min_fee_amount']) $counted++;
						else $uncounted++;
					}
				}
				$this->db->order_by('start', 'ASC');
				$comission_rule = $this->db->get_where('admission_management_rules', array(
					'admission_incentive_id' => $incentive_id,
					'start<=' => $counted,
					'end>=' => $counted,
				))->result_array();
				$com = 0;
				$incentive_amount = 0;
				if (count($comission_rule) > 0) {
					$com = $comission_rule[0]['comission'];
					$incentive_amount = $comission_rule[0]['comission'] * $counted;
				}
				$out['admission'] = array(
					'incentive_id' => $incentive_id,
					'user_id' => $adm_user_id,
					'counted' => $counted,
					'uncounted' => $uncounted,
					'total_students' => count($total_paid_students),
					'incentive_amount' => $incentive_amount,
					'commission_rate' => $com,
				);
			}
		}

		echo json_encode($out, JSON_PRETTY_PRINT) . "\n";
	}
}
