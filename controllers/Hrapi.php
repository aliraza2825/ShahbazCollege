<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Human Resource JSON API for React POS shell
 * Base: /index.php/hrapi/{method}
 * Auth: X-Pos-Token; Admin only for v1 (matches legacy HR sidebar).
 *
 * Ports the legacy HR sidebar (Hr.php, Departments.php, Designations.php,
 * Staff_type.php, Staff_shifts.php, Allownces.php, Holidays.php, Leaves.php,
 * Attendence.php, Myattendence.php, Teachers.php, Loans.php,
 * Payroll_statutory_rules.php, Payroll_income_tax.php, Salary.php) into a
 * single flat JSON API for the React shell.
 */
class Hrapi extends CI_Controller {

	private $current_user = null;

	public function __construct()
	{
		parent::__construct();
		if (is_cli()) {
			$this->current_user = array('user_id' => 1, 'role' => 'Admin', 'status' => '1');
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
		if (!$this->_is_admin()) {
			$this->_json(array('success' => false, 'message' => 'HR access required (Admin)'), 403);
		}
		if (function_exists('ensure_staff_shift_schema')) {
			ensure_staff_shift_schema();
		}
	}

	// ------------------------------------------------------------------
	// Skeleton (CORS / json / body / auth) - same as Incentiveapi.php
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

	private function _current_user_name()
	{
		$first = isset($this->current_user['first_name']) ? $this->current_user['first_name'] : '';
		$last = isset($this->current_user['last_name']) ? $this->current_user['last_name'] : '';
		return trim($first . ' ' . $last);
	}

	private function _csv($value)
	{
		if (is_array($value)) return implode(',', $value);
		return (string) $value;
	}

	// ------------------------------------------------------------------
	// Menu / meta
	// ------------------------------------------------------------------

	public function meta()
	{
		$sections = array(
			array('key' => 'locations', 'label' => 'Locations', 'enabled' => true),
			array('key' => 'interviews', 'label' => 'HR', 'enabled' => true),
			array('key' => 'attendance', 'label' => 'Attendence', 'enabled' => true),
			array('key' => 'leaves', 'label' => 'Leaves', 'enabled' => true),
			array('key' => 'my_attendance', 'label' => 'My Attendence', 'enabled' => true),
			array('key' => 'holidays', 'label' => 'Holidays', 'enabled' => true),
			array('key' => 'staff', 'label' => 'Staff', 'enabled' => true),
			array('key' => 'departments', 'label' => 'Departments', 'enabled' => true),
			array('key' => 'designations', 'label' => 'Designations', 'enabled' => true),
			array('key' => 'loans', 'label' => 'Loans', 'enabled' => true),
			array('key' => 'staff_type', 'label' => 'Staff Type', 'enabled' => true),
			array('key' => 'staff_shifts', 'label' => 'Staff Shifts', 'enabled' => true),
			array('key' => 'allowances', 'label' => 'Allownces', 'enabled' => true),
			array('key' => 'statutory', 'label' => 'Statutory Rules', 'enabled' => true),
			array('key' => 'income_tax', 'label' => 'Income Tax', 'enabled' => true),
			array('key' => 'salary', 'label' => 'Salary', 'enabled' => true),
		);

		$this->_json(array(
			'success' => true,
			'role' => isset($this->current_user['role']) ? $this->current_user['role'] : null,
			'can_access' => true,
			'sections' => $sections,
		));
	}

	/**
	 * Login GPS check-in report.
	 * Legacy: Locations::check — UI posts from/to but never filtered; we apply date filter.
	 * GET locations?from=YYYY-MM-DD&to=YYYY-MM-DD
	 * CLI: php index.php hrapi locations [from] [to]
	 */
	public function locations($cli_from = '', $cli_to = '')
	{
		$from = is_cli() && $cli_from !== '' && $cli_from !== '0'
			? $cli_from
			: $this->input->get('from');
		$to = is_cli() && $cli_to !== '' && $cli_to !== '0'
			? $cli_to
			: $this->input->get('to');
		if ($from === null || $from === '') $from = date('Y-m-d');
		if ($to === null || $to === '') $to = date('Y-m-d');

		if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
			$this->_json(array('success' => false, 'message' => 'Invalid date range'), 422);
		}
		if ($from > $to) {
			$this->_json(array('success' => false, 'message' => 'from must be on or before to'), 422);
		}

		if (!$this->db->table_exists('locations')) {
			$this->_json(array('success' => true, 'from' => $from, 'to' => $to, 'data' => array()));
		}

		$from_dt = $from . ' 00:00:00';
		$to_dt = $to . ' 23:59:59';

		$this->db->select('locations.*, users.first_name, users.last_name');
		$this->db->from('locations');
		$this->db->join('users', 'users.user_id=locations.user_id', 'inner');
		$this->db->where('locations.date >=', $from_dt);
		$this->db->where('locations.date <=', $to_dt);
		$this->db->order_by('locations.date', 'DESC');
		$rows = $this->db->get()->result_array();

		$data = array();
		foreach ($rows as $row) {
			$id = null;
			if (isset($row['id'])) $id = (int)$row['id'];
			elseif (isset($row['location_id'])) $id = (int)$row['location_id'];
			elseif (isset($row['locations_id'])) $id = (int)$row['locations_id'];

			$data[] = array(
				'id' => $id,
				'user_id' => isset($row['user_id']) ? (int)$row['user_id'] : null,
				'first_name' => isset($row['first_name']) ? $row['first_name'] : '',
				'last_name' => isset($row['last_name']) ? $row['last_name'] : '',
				'date' => isset($row['date']) ? $row['date'] : null,
				'url' => isset($row['url']) ? $row['url'] : '',
			);
		}

		$this->_json(array(
			'success' => true,
			'from' => $from,
			'to' => $to,
			'data' => $data,
		));
	}

	// ------------------------------------------------------------------
	// Shared lookups
	// ------------------------------------------------------------------

	public function campuses()
	{
		$rows = $this->db->get_where('campuses', array('status' => 1))->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function departments_lookup()
	{
		$rows = $this->db->get('departments')->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function designations_lookup($department_id = null)
	{
		if ($department_id === null || $department_id === '') $department_id = $this->input->get('department_id');
		if ($department_id !== null && $department_id !== '') {
			$rows = $this->db->get_where('designations', array('department_id' => (int)$department_id))->result_array();
		} else {
			$rows = $this->db->get('designations')->result_array();
		}
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function staff_types_lookup()
	{
		$rows = $this->db->get('staff_type')->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function staff_shifts_lookup()
	{
		$rows = get_staff_shifts_with_study_type(true);
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function study_types_lookup()
	{
		if (!$this->db->table_exists('study_type')) {
			$this->_json(array('success' => true, 'data' => array()));
		}
		$rows = $this->db->order_by('name', 'ASC')->get('study_type')->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function allowances_lookup()
	{
		$rows = $this->db->get('allownces')->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	/** Staff eligible for HR actions (payroll / holidays / attendance assignment). */
	public function staff_users_lookup()
	{
		$roles = array('Teacher', 'Principal', 'Accountant', 'Guard', 'Admin');

		$this->db->select('user_id, first_name, last_name, campus_id, salary, gross_salary');
		$this->db->from('users');
		$this->db->where('status', '1');
		$this->db->group_start();
		$this->db->where_in('role', $roles);
		$this->db->or_where('type', 'regular');
		$this->db->group_end();
		$this->db->order_by('first_name', 'ASC');
		$rows = $this->db->get()->result_array();

		$data = array();
		foreach ($rows as $r) {
			$data[] = array(
				'id' => (int)$r['user_id'],
				'name' => trim($r['first_name'] . ' ' . $r['last_name']),
				'campus_id' => (int)$r['campus_id'],
				'salary' => $r['salary'],
				'gross_salary' => $r['gross_salary'],
			);
		}
		$this->_json(array('success' => true, 'data' => $data));
	}

	// ------------------------------------------------------------------
	// Departments CRUD
	// ------------------------------------------------------------------

	public function departments()
	{
		$rows = $this->db->get('departments')->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function department($id = 0)
	{
		$id = (int)$id;
		$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

		if ($method === 'GET') {
			if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
			$row = $this->db->get_where('departments', array('department_id' => $id))->row_array();
			if (!$row) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
			$this->_json(array('success' => true, 'data' => $row));
		}

		if ($method === 'POST') {
			$body = $this->_body();
			$name = isset($body['department_name']) ? trim($body['department_name']) : '';
			if ($name === '') $this->_json(array('success' => false, 'message' => 'department_name required'), 422);

			if ($id === 0) {
				$check = $this->db->get_where('departments', array('department_name' => $name))->result_array();
				if (count($check) > 0) $this->_json(array('success' => false, 'message' => 'Department already added'), 422);
				$this->db->set('department_name', $name);
				$this->db->insert('departments');
				$this->_json(array('success' => true, 'id' => (int)$this->db->insert_id()));
			}

			$exists = $this->db->get_where('departments', array('department_id' => $id))->row_array();
			if (!$exists) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
			$this->db->set('department_name', $name);
			$this->db->where('department_id', $id);
			$this->db->update('departments');
			$this->_json(array('success' => true, 'id' => $id));
		}

		if ($method === 'DELETE') {
			if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
			$this->db->where('department_id', $id);
			$this->db->delete('departments');
			$this->_json(array('success' => true));
		}

		$this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
	}

	// ------------------------------------------------------------------
	// Designations CRUD
	// ------------------------------------------------------------------

	public function designations()
	{
		$this->db->select('designations.*, departments.department_name');
		$this->db->from('designations');
		$this->db->join('departments', 'departments.department_id=designations.department_id', 'left');
		$rows = $this->db->get()->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function designation($id = 0)
	{
		$id = (int)$id;
		$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

		if ($method === 'GET') {
			if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
			$row = $this->db->get_where('designations', array('designation_id' => $id))->row_array();
			if (!$row) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
			$this->_json(array('success' => true, 'data' => $row));
		}

		if ($method === 'POST') {
			$body = $this->_body();
			$department_id = isset($body['department_id']) ? $body['department_id'] : null;
			$designation_name = isset($body['designation_name']) ? trim($body['designation_name']) : '';
			$description = isset($body['description']) ? $body['description'] : '';
			if ($designation_name === '') $this->_json(array('success' => false, 'message' => 'designation_name required'), 422);

			if ($id === 0) {
				$check = $this->db->get_where('designations', array('designation_name' => $designation_name))->result_array();
				if (count($check) > 0) $this->_json(array('success' => false, 'message' => 'Designation already added'), 422);
				$this->db->set('department_id', $department_id);
				$this->db->set('designation_name', $designation_name);
				$this->db->set('description', $description);
				$this->db->insert('designations');
				$this->_json(array('success' => true, 'id' => (int)$this->db->insert_id()));
			}

			$exists = $this->db->get_where('designations', array('designation_id' => $id))->row_array();
			if (!$exists) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
			$this->db->set('department_id', $department_id);
			$this->db->set('designation_name', $designation_name);
			$this->db->set('description', $description);
			$this->db->where('designation_id', $id);
			$this->db->update('designations');
			$this->_json(array('success' => true, 'id' => $id));
		}

		if ($method === 'DELETE') {
			if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
			$this->db->where('designation_id', $id);
			$this->db->delete('designations');
			$this->_json(array('success' => true));
		}

		$this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
	}

	// ------------------------------------------------------------------
	// Staff type CRUD
	// ------------------------------------------------------------------

	public function staff_types()
	{
		$rows = $this->db->get('staff_type')->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function staff_type($id = 0)
	{
		$id = (int)$id;
		$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

		if ($method === 'GET') {
			if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
			$row = $this->db->get_where('staff_type', array('staff_type_id' => $id))->row_array();
			if (!$row) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
			$this->_json(array('success' => true, 'data' => $row));
		}

		if ($method === 'POST') {
			$body = $this->_body();
			$name = isset($body['staff_type_name']) ? trim($body['staff_type_name']) : '';
			if ($name === '') $this->_json(array('success' => false, 'message' => 'staff_type_name required'), 422);

			if ($id === 0) {
				$check = $this->db->get_where('staff_type', array('staff_type_name' => $name))->result_array();
				if (count($check) > 0) $this->_json(array('success' => false, 'message' => 'Staff type already added'), 422);
				$this->db->set('staff_type_name', $name);
				$this->db->insert('staff_type');
				$this->_json(array('success' => true, 'id' => (int)$this->db->insert_id()));
			}

			$exists = $this->db->get_where('staff_type', array('staff_type_id' => $id))->row_array();
			if (!$exists) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
			$this->db->set('staff_type_name', $name);
			$this->db->where('staff_type_id', $id);
			$this->db->update('staff_type');
			$this->_json(array('success' => true, 'id' => $id));
		}

		if ($method === 'DELETE') {
			if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
			$this->db->where('staff_type_id', $id);
			$this->db->delete('staff_type');
			$this->_json(array('success' => true));
		}

		$this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
	}

	// ------------------------------------------------------------------
	// Staff shifts (+ timings)
	// ------------------------------------------------------------------

	public function staff_shifts()
	{
		$rows = get_staff_shifts_with_study_type(false);

		$timingRows = $this->db
			->select('staff_shift_id, COUNT(*) as total')
			->where('staff_shift_id IS NOT NULL', null, false)
			->group_by('staff_shift_id')
			->get('staff_timing')
			->result_array();

		$timingMap = array();
		foreach ($timingRows as $t) $timingMap[$t['staff_shift_id']] = (int)$t['total'];

		foreach ($rows as &$r) {
			$r['timing_count'] = isset($timingMap[$r['staff_shift_id']]) ? $timingMap[$r['staff_shift_id']] : 0;
		}
		unset($r);

		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function staff_shift($id = 0)
	{
		$id = (int)$id;
		$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

		if ($method === 'GET') {
			if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
			$this->db->select('staff_shifts.*, study_type.name as study_type_name');
			$this->db->from('staff_shifts');
			$this->db->join('study_type', 'study_type.id = staff_shifts.study_type_id', 'left');
			$this->db->where('staff_shifts.staff_shift_id', $id);
			$row = $this->db->get()->row_array();
			if (!$row) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
			$timings = $this->db->where('staff_shift_id', $id)->get('staff_timing')->result_array();
			$this->_json(array('success' => true, 'data' => $row, 'timings' => $timings));
		}

		if ($method === 'POST') {
			$body = $this->_body();
			$shiftName = isset($body['shift_name']) ? trim($body['shift_name']) : '';
			$studyTypeId = isset($body['study_type_id']) ? (int)$body['study_type_id'] : 0;
			if ($shiftName === '' || $studyTypeId <= 0) {
				$this->_json(array('success' => false, 'message' => 'shift_name and study_type_id required'), 422);
			}

			$this->db->where('shift_name', $shiftName);
			$this->db->where('study_type_id', $studyTypeId);
			if ($id > 0) $this->db->where('staff_shift_id !=', $id);
			$dupe = $this->db->get('staff_shifts')->result_array();
			if (count($dupe) > 0) {
				$this->_json(array('success' => false, 'message' => 'This shift and study type combination already exists'), 422);
			}

			$description = isset($body['description']) ? $body['description'] : '';
			$status = isset($body['status']) ? (int)$body['status'] : 1;

			if ($id === 0) {
				$this->db->set('shift_name', $shiftName);
				$this->db->set('study_type_id', $studyTypeId);
				$this->db->set('description', $description);
				$this->db->set('status', $status);
				$this->db->set('created_at', date('Y-m-d H:i:s'));
				$this->db->insert('staff_shifts');
				$id = (int)$this->db->insert_id();
			} else {
				$exists = $this->db->get_where('staff_shifts', array('staff_shift_id' => $id))->row_array();
				if (!$exists) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
				$this->db->set('shift_name', $shiftName);
				$this->db->set('study_type_id', $studyTypeId);
				$this->db->set('description', $description);
				$this->db->set('status', $status);
				$this->db->set('updated_at', date('Y-m-d H:i:s'));
				$this->db->where('staff_shift_id', $id);
				$this->db->update('staff_shifts');
			}

			if (isset($body['timings']) && is_array($body['timings'])) {
				$this->db->where('staff_shift_id', $id);
				$this->db->delete('staff_timing');
				foreach ($body['timings'] as $t) {
					$day = isset($t['day']) ? $t['day'] : '';
					if ($day === '') continue;
					$this->db->insert('staff_timing', array(
						'day' => $day,
						'checkin_timing' => isset($t['checkin_timing']) ? $t['checkin_timing'] : '00:00:00',
						'checkout_timing' => isset($t['checkout_timing']) ? $t['checkout_timing'] : '00:00:00',
						'half_day_on' => isset($t['half_day_on']) ? $t['half_day_on'] : '00:00:00',
						'full_day_on' => isset($t['full_day_on']) ? $t['full_day_on'] : '00:00:00',
						'staff_shift_id' => $id,
						'staff_id' => 0,
					));
				}
			}

			$this->_json(array('success' => true, 'id' => $id));
		}

		if ($method === 'DELETE') {
			if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
			$this->db->where('staff_shift_id', $id);
			$this->db->delete('staff_timing');
			$this->db->where('staff_shift_id', $id);
			$this->db->delete('staff_shifts');
			$this->_json(array('success' => true));
		}

		$this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
	}

	// ------------------------------------------------------------------
	// Allowances
	// ------------------------------------------------------------------

	public function allowances()
	{
		$rows = $this->db->get('allownces')->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function allowance($id = 0)
	{
		$id = (int)$id;
		$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

		if ($method === 'GET') {
			if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
			$row = $this->db->get_where('allownces', array('id' => $id))->row_array();
			if (!$row) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
			$this->_json(array('success' => true, 'data' => $row));
		}

		if ($method === 'POST') {
			$body = $this->_body();
			$name = isset($body['name']) ? $body['name'] : (isset($body['allownce']) ? $body['allownce'] : '');
			$type = isset($body['type']) ? $body['type'] : 0;
			$percent = isset($body['percent']) ? $body['percent'] : 0;
			if (trim((string)$name) === '') $this->_json(array('success' => false, 'message' => 'name required'), 422);

			if ($id === 0) {
				$this->db->set('name', $name);
				$this->db->set('type', $type);
				$this->db->set('percent', $percent);
				$this->db->insert('allownces');
				$this->_json(array('success' => true, 'id' => (int)$this->db->insert_id()));
			}

			$exists = $this->db->get_where('allownces', array('id' => $id))->row_array();
			if (!$exists) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
			$this->db->set('name', $name);
			$this->db->set('type', $type);
			$this->db->set('percent', $percent);
			$this->db->where('id', $id);
			$this->db->update('allownces');
			$this->_json(array('success' => true, 'id' => $id));
		}

		$this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
	}

	// ------------------------------------------------------------------
	// Holidays
	// ------------------------------------------------------------------

	public function holidays()
	{
		$from = $this->input->get('from');
		$to = $this->input->get('to');
		if ($from === null || $from === '') $from = date('Y-m-d');
		if ($to === null || $to === '') $to = date('Y-m-d', strtotime('+1 month'));

		$this->db->order_by('holiday_id', 'DESC');
		$this->db->where('date >=', $from);
		$this->db->where('date <=', $to);
		$rows = $this->db->get('holidays')->result_array();

		$this->_json(array('success' => true, 'from' => $from, 'to' => $to, 'data' => $rows));
	}

	/** Port of Holiday::insertHoliday() — inserts one row per date. */
	public function holiday_create()
	{
		$body = $this->_body();

		$campus_ids = isset($body['campus_ids']) ? (array)$body['campus_ids'] : array();
		$staff_type_ids = isset($body['staff_type_ids']) ? (array)$body['staff_type_ids'] : array();
		$user_ids = isset($body['user_ids']) ? (array)$body['user_ids'] : array();
		$shift_ids = isset($body['shift_ids']) ? (array)$body['shift_ids'] : array();
		$student_ids = isset($body['student_ids']) ? $body['student_ids'] : '';
		if (is_array($student_ids)) $student_ids = implode(',', $student_ids);

		$dates = array();
		if (isset($body['dates']) && is_array($body['dates'])) $dates = $body['dates'];
		elseif (isset($body['date']) && is_array($body['date'])) $dates = $body['date'];

		$reason = isset($body['reason']) ? $body['reason'] : '';

		if (!count($dates)) $this->_json(array('success' => false, 'message' => 'dates required'), 422);

		$inserted = 0;
		foreach ($dates as $date) {
			if ($date === '' || $date === null) continue;
			$this->db->set('date', $date);
			$this->db->set('campus_ids', implode(',', $campus_ids));
			$this->db->set('staff_type_ids', implode(',', $staff_type_ids));
			$this->db->set('user_ids', implode(',', $user_ids));
			$this->db->set('shift_ids', implode(',', $shift_ids));
			$this->db->set('student_ids', $student_ids);
			$this->db->set('reason', $reason);
			$this->db->set('add_by', $this->_current_user_name());
			$this->db->insert('holidays');
			$inserted++;
		}

		$this->_json(array('success' => true, 'inserted' => $inserted));
	}

	public function holiday_cancel($id = 0)
	{
		$id = (int)$id;
		if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
		$body = $this->_body();
		$cancel_reason = isset($body['cancel_reason']) ? $body['cancel_reason'] : '';

		$this->db->set('cancel', 1);
		$this->db->set('cancel_reason', $cancel_reason);
		$this->db->where('holiday_id', $id);
		$this->db->update('holidays');
		$this->_json(array('success' => true));
	}

	// ------------------------------------------------------------------
	// Interviews (ports Hr::insert_interview / update_interview / delete)
	// ------------------------------------------------------------------

	public function interviews()
	{
		$this->db->select('interview.*, campuses.campus_name');
		$this->db->from('interview');
		$this->db->join('campuses', 'campuses.campus_id=interview.campus_id', 'left');
		$this->db->order_by('interview.interview_id', 'DESC');
		$rows = $this->db->get()->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function interview($id = 0)
	{
		$id = (int)$id;
		$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

		if ($method === 'GET') {
			if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
			$row = $this->db->get_where('interview', array('interview_id' => $id))->row_array();
			if (!$row) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
			$this->_json(array('success' => true, 'data' => $row));
		}

		if ($method === 'POST') {
			$body = $this->_body();
			$fields = array(
				'campus_id', 'name', 'address', 'qualification', 'timing', 'personality', 'iq_level',
				'salary_offer_responce', 'salary_demand', 'other_current_job', 'previous_experience',
				'gender', 'marital_status', 'grantable', 'guarantee_person', 'father_occupation',
				'job_post_wanted', 'residence', 'cell_number', 'suggestion', 'reviews', 'your_opinion',
			);
			foreach ($fields as $f) {
				$this->db->set($f, isset($body[$f]) ? $body[$f] : '');
			}
			$expert_in = isset($body['expert_in']) ? $body['expert_in'] : array();
			$this->db->set('expert_in', is_array($expert_in) ? implode(',', $expert_in) : $expert_in);
			$cv = isset($body['cv_url']) ? $body['cv_url'] : (isset($body['cv']) ? $body['cv'] : '');
			$this->db->set('cv', $cv);

			if ($id === 0) {
				$this->db->set('add_by', $this->_current_user_name());
				$this->db->set('date', date('Y-m-d'));
				$this->db->insert('interview');
				$this->_json(array('success' => true, 'id' => (int)$this->db->insert_id()));
			}

			$exists = $this->db->get_where('interview', array('interview_id' => $id))->row_array();
			if (!$exists) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
			$this->db->where('interview_id', $id);
			$this->db->update('interview');
			$this->_json(array('success' => true, 'id' => $id));
		}

		if ($method === 'DELETE') {
			if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
			$this->db->where('interview_id', $id);
			$this->db->delete('interview');
			$this->_json(array('success' => true));
		}

		$this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
	}

	// ------------------------------------------------------------------
	// Leaves
	// ------------------------------------------------------------------

	public function leave_types()
	{
		$rows = $this->db->get('tblleavetype')->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function leave_type($id = 0)
	{
		$id = (int)$id;
		$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
		if ($method !== 'POST') {
			$this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
		}

		$body = $this->_body();
		$leavetype = isset($body['leavetype']) ? $body['leavetype'] : '';
		$no_of_leaves = isset($body['no_of_leaves']) ? $body['no_of_leaves'] : 0;
		$is_half_allowed = isset($body['is_half_allowed']) ? $body['is_half_allowed'] : 0;
		$description = isset($body['description']) ? $body['description'] : '';

		if ($id === 0) {
			$this->db->set('leavetype', $leavetype);
			$this->db->set('no_of_leaves', $no_of_leaves);
			$this->db->set('is_half_allowed', $is_half_allowed);
			$this->db->set('description', $description);
			$this->db->insert('tblleavetype');
			$this->_json(array('success' => true, 'id' => (int)$this->db->insert_id()));
		}

		$exists = $this->db->get_where('tblleavetype', array('id' => $id))->row_array();
		if (!$exists) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		$this->db->set('leavetype', $leavetype);
		$this->db->set('no_of_leaves', $no_of_leaves);
		$this->db->set('is_half_allowed', $is_half_allowed);
		$this->db->set('description', $description);
		$this->db->where('id', $id);
		$this->db->update('tblleavetype');
		$this->_json(array('success' => true, 'id' => $id));
	}

	/** Port of Leaves::leave_list() — GET leave_approvals?status= (omit status for all). */
	public function leave_approvals()
	{
		$status = $this->input->get('status');

		$this->db->select('tblleaves.*, tblleavetype.leavetype as leavetype_name, users.first_name, users.last_name');
		$this->db->from('tblleaves');
		$this->db->join('tblleavetype', 'tblleavetype.id = tblleaves.leavetype', 'left');
		$this->db->join('users', 'tblleaves.empid = users.user_id', 'left');
		if ($status !== null && $status !== '') {
			$this->db->where('tblleaves.status', $status);
		}
		$this->db->order_by('tblleaves.status', 'ASC');
		$this->db->order_by('tblleaves.id', 'DESC');
		$rows = $this->db->get()->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function leave_approve($id = 0)
	{
		$id = (int)$id;
		if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
		$body = $this->_body();
		$status = isset($body['status']) ? (int)$body['status'] : 0;
		if (!in_array($status, array(1, 2), true)) {
			$this->_json(array('success' => false, 'message' => 'status must be 1 (approve) or 2 (reject)'), 422);
		}

		$this->db->set('status', $status);
		$this->db->set('updated_by', $this->current_user['user_id']);
		$this->db->where('id', $id);
		$this->db->update('tblleaves');
		$this->_json(array('success' => true));
	}

	// ------------------------------------------------------------------
	// Attendance machines (ports Attendence::insert_machine / update_machine)
	// ------------------------------------------------------------------

	public function attendance_machines()
	{
		$this->db->select('attendance_machine.*, campuses.campus_name');
		$this->db->from('attendance_machine');
		$this->db->join('campuses', 'campuses.campus_id=attendance_machine.campus_id', 'left');
		$rows = $this->db->get()->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function attendance_machine($id = 0)
	{
		$id = (int)$id;
		$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

		if ($method === 'GET') {
			if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
			$row = $this->db->get_where('attendance_machine', array('id' => $id))->row_array();
			if (!$row) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
			$this->_json(array('success' => true, 'data' => $row));
		}

		if ($method === 'POST') {
			$body = $this->_body();
			$campus_id = isset($body['campus_id']) ? $body['campus_id'] : '';
			$name = isset($body['machine_id']) ? $body['machine_id'] : (isset($body['name']) ? $body['name'] : '');
			if (trim((string)$name) === '') $this->_json(array('success' => false, 'message' => 'machine_id required'), 422);

			if ($id === 0) {
				$this->db->set('name', $name);
				$this->db->set('campus_id', $campus_id);
				$this->db->set('created_by', $this->_current_user_name());
				$this->db->insert('attendance_machine');
				$this->_json(array('success' => true, 'id' => (int)$this->db->insert_id()));
			}

			$exists = $this->db->get_where('attendance_machine', array('id' => $id))->row_array();
			if (!$exists) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
			$this->db->set('name', $name);
			$this->db->set('campus_id', $campus_id);
			$this->db->where('id', $id);
			$this->db->update('attendance_machine');
			$this->_json(array('success' => true, 'id' => $id));
		}

		$this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
	}

	// ------------------------------------------------------------------
	// Attendance report — simplified punch list for a date range
	// ------------------------------------------------------------------

	public function attendance_report()
	{
		$from = $this->input->get('from');
		$to = $this->input->get('to');
		$campus_id = $this->input->get('campus_id');
		$type = $this->input->get('type');
		if ($type !== 'student') $type = 'staff';
		if ($from === null || $from === '') $from = date('Y-m-01');
		if ($to === null || $to === '') $to = date('Y-m-d');

		if ($type === 'student') {
			$this->db->select('attendence.id, attendence.time, attendence.halfday, students.student_id as person_id, students.first_name, students.last_name, students.roll_no, classes.campus_id as campus_id');
			$this->db->from('attendence');
			$this->db->join('machine_data', 'machine_data.machine_id=attendence.machine_user_id and machine_data.type="student"', 'inner');
			$this->db->join('students', 'students.student_id=machine_data.teacher_student_id', 'inner');
			$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
			$campusColumn = 'classes.campus_id';
		} else {
			$this->db->select('attendence.id, attendence.time, attendence.halfday, users.user_id as person_id, users.first_name, users.last_name, users.campus_id as campus_id');
			$this->db->from('attendence');
			$this->db->join('machine_data', 'machine_data.machine_id=attendence.machine_user_id and machine_data.type="teacher"', 'inner');
			$this->db->join('users', 'users.user_id=machine_data.teacher_student_id', 'inner');
			$campusColumn = 'users.campus_id';
		}

		$this->db->where('attendence.time >=', $from . ' 00:00:00');
		$this->db->where('attendence.time <=', $to . ' 23:59:59');
		if ($campus_id !== null && $campus_id !== '') {
			$this->db->where($campusColumn, $campus_id);
		}
		$this->db->order_by('attendence.time', 'ASC');
		$rows = $this->db->get()->result_array();

		$data = array();
		foreach ($rows as $r) {
			$data[] = array(
				'id' => (int)$r['id'],
				'person_id' => (int)$r['person_id'],
				'name' => trim($r['first_name'] . ' ' . $r['last_name']),
				'roll_no' => isset($r['roll_no']) ? $r['roll_no'] : null,
				'campus_id' => isset($r['campus_id']) ? (int)$r['campus_id'] : null,
				'time' => $r['time'],
				'date' => date('Y-m-d', strtotime($r['time'])),
				'halfday' => ((int)$r['halfday']) === 1,
			);
		}

		$this->_json(array('success' => true, 'from' => $from, 'to' => $to, 'type' => $type, 'data' => $data));
	}

	// ------------------------------------------------------------------
	// My attendance — ports Myattendence.php date-range punch list
	// ------------------------------------------------------------------

	public function my_attendance()
	{
		$from = $this->input->get('from');
		$to = $this->input->get('to');
		if ($from === null || $from === '') $from = date('Y-m-01');
		if ($to === null || $to === '') $to = date('Y-m-d');

		$user_id = (int)$this->current_user['user_id'];

		$machine = $this->db->get_where('machine_data', array('teacher_student_id' => $user_id, 'type' => 'teacher'))->row_array();
		if (!$machine) {
			$this->_json(array('success' => true, 'from' => $from, 'to' => $to, 'data' => array(), 'message' => 'No attendance machine mapping found for this user'));
		}
		$machine_user_id = (int)$machine['machine_id'];

		$campus_id = isset($this->current_user['campus_id']) ? $this->current_user['campus_id'] : 0;
		$staff_type_id = isset($this->current_user['staff_type_id']) ? $this->current_user['staff_type_id'] : '';

		$holiday_dates = array();
		if ($staff_type_id !== '' && $staff_type_id !== null && $this->db->table_exists('holidays')) {
			$holiday_qry = "SELECT `date` FROM holidays
				WHERE `date` >= " . $this->db->escape($from) . "
				AND `date` <= " . $this->db->escape($to) . "
				AND (cancel = 0 OR cancel IS NULL OR cancel = '')
				AND FIND_IN_SET(" . $this->db->escape($campus_id) . ", campus_ids)
				AND FIND_IN_SET(" . $this->db->escape($staff_type_id) . ", staff_type_ids)
				AND FIND_IN_SET(" . $this->db->escape($user_id) . ", user_ids)";
			$holiday_rows = $this->db->query($holiday_qry)->result_array();
			foreach ($holiday_rows as $hr) $holiday_dates[$hr['date']] = true;
		}

		$dates = array();
		$cursor = strtotime($from);
		$end = strtotime($to);
		while ($cursor <= $end) {
			$dates[] = date('Y-m-d', $cursor);
			$cursor += 86400;
		}

		$data = array();
		foreach ($dates as $date) {
			$row = array(
				'date' => $date,
				'in_time' => '',
				'out_time' => '',
				'is_holiday' => isset($holiday_dates[$date]),
			);

			$first = $this->db->query('SELECT time FROM attendence WHERE machine_user_id=' . $machine_user_id . ' AND time>="' . $date . ' 00:00:00" AND time<"' . $date . ' 23:59:59" ORDER BY time ASC LIMIT 1')->row_array();
			if ($first) $row['in_time'] = date('h:i:s A', strtotime($first['time']));

			$last = $this->db->query('SELECT time FROM attendence WHERE machine_user_id=' . $machine_user_id . ' AND time>="' . $date . ' 00:00:00" AND time<"' . $date . ' 23:59:59" ORDER BY time DESC LIMIT 1')->row_array();
			if ($last) {
				$out_time = date('h:i:s A', strtotime($last['time']));
				$row['out_time'] = ($out_time !== $row['in_time']) ? $out_time : '';
			}

			$data[] = $row;
		}

		$this->_json(array('success' => true, 'from' => $from, 'to' => $to, 'data' => $data));
	}

	// ------------------------------------------------------------------
	// Staff (ports Teachers.php add/edit/delete)
	// ------------------------------------------------------------------

	public function staff()
	{
		$this->db->select('users.*, campuses.campus_name, staff_type.staff_type_name, departments.department_name');
		$this->db->from('users');
		$this->db->join('campuses', 'campuses.campus_id=users.campus_id', 'left');
		$this->db->join('staff_type', 'staff_type.staff_type_id=users.staff_type_id', 'left');
		$this->db->join('departments', 'departments.department_id=users.department_id', 'left');
		$this->db->where('users.status', '1');
		$this->db->order_by('users.first_name', 'ASC');
		$rows = $this->db->get()->result_array();

		$designationMap = $this->_designation_name_map();

		$data = array();
		foreach ($rows as $r) {
			$r['designation_names'] = $this->_designation_names($r['designation_id'], $designationMap);
			unset($r['password']);
			$data[] = $r;
		}

		$this->_json(array('success' => true, 'data' => $data));
	}

	private function _designation_name_map()
	{
		$rows = $this->db->get('designations')->result_array();
		$map = array();
		foreach ($rows as $d) $map[(int)$d['designation_id']] = $d['designation_name'];
		return $map;
	}

	private function _designation_names($csv, $map)
	{
		$names = array();
		foreach (explode(',', (string)$csv) as $did) {
			$did = trim($did);
			if ($did !== '' && isset($map[(int)$did])) $names[] = $map[(int)$did];
		}
		return $names;
	}

	public function staff_get($id = 0)
	{
		$id = (int)$id;
		if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
		$row = $this->db->get_where('users', array('user_id' => $id))->row_array();
		if (!$row) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		unset($row['password']);

		$phones = $this->db->get_where('users_phones', array('user_id' => $id))->result_array();

		$this->db->select('user_allowances.*, allownces.name, allownces.type, allownces.percent');
		$this->db->from('user_allowances');
		$this->db->join('allownces', 'allownces.id=user_allowances.allowance_id', 'inner');
		$this->db->where('user_allowances.user_id', $id);
		$allowances = $this->db->get()->result_array();

		$this->_json(array('success' => true, 'data' => $row, 'phones' => $phones, 'allowances' => $allowances));
	}

	public function staff_save($id = 0)
	{
		$id = (int)$id;
		$body = $this->_body();

		$designation_id = isset($body['designation_id']) ? $body['designation_id'] : '';
		if (is_array($designation_id)) $designation_id = implode(',', $designation_id);

		$password = null;
		if (isset($body['password']) && trim((string)$body['password']) !== '') {
			$password = md5($body['password']);
		}

		$fields = array(
			'campus_id' => isset($body['campus_id']) ? $body['campus_id'] : 0,
			'staff_type_id' => isset($body['staff_type_id']) ? $body['staff_type_id'] : 0,
			'staff_shift_id' => isset($body['staff_shift_id']) && $body['staff_shift_id'] !== '' ? $body['staff_shift_id'] : null,
			'department_id' => isset($body['department_id']) ? $body['department_id'] : 0,
			'designation_id' => $designation_id,
			'first_name' => isset($body['first_name']) ? $body['first_name'] : '',
			'last_name' => isset($body['last_name']) ? $body['last_name'] : '',
			'father_name' => isset($body['father_name']) ? $body['father_name'] : '',
			'gender' => isset($body['gender']) ? $body['gender'] : '',
			'email' => isset($body['email']) ? $body['email'] : '',
			'cnic' => isset($body['cnic']) ? $body['cnic'] : '',
			'salary' => isset($body['salary']) ? $body['salary'] : 0,
			'gross_salary' => isset($body['gross_salary']) ? $body['gross_salary'] : 0,
			'username' => isset($body['username']) ? $body['username'] : '',
			'role' => isset($body['role']) ? $body['role'] : '',
			'type' => isset($body['type']) ? $body['type'] : 'regular',
			'mobile' => isset($body['mobile']) ? $body['mobile'] : '',
			'joining_date' => isset($body['joining_date']) && $body['joining_date'] !== '' ? $body['joining_date'] : date('Y-m-d'),
			'status' => isset($body['status']) ? $body['status'] : 1,
		);

		if ($id === 0) {
			$cnic = isset($body['cnic']) ? $body['cnic'] : '';
			if (trim((string)$cnic) !== '') {
				$dupe = $this->db->get_where('users', array('cnic' => $cnic))->result_array();
				if (count($dupe) > 0) $this->_json(array('success' => false, 'message' => 'Staff with this CNIC already exists'), 422);
			}
			$fields['password'] = $password !== null ? $password : md5('123456');
			foreach ($fields as $k => $v) $this->db->set($k, $v);
			$this->db->insert('users');
			$id = (int)$this->db->insert_id();
		} else {
			$exists = $this->db->get_where('users', array('user_id' => $id))->row_array();
			if (!$exists) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
			if ($password !== null) $fields['password'] = $password;
			foreach ($fields as $k => $v) $this->db->set($k, $v);
			$this->db->where('user_id', $id);
			$this->db->update('users');
		}

		if (isset($body['allowance_ids']) && is_array($body['allowance_ids'])) {
			$this->db->where('user_id', $id);
			$this->db->delete('user_allowances');

			$allowances = $this->db->get('allownces')->result_array();
			$allowanceMap = array();
			foreach ($allowances as $a) $allowanceMap[(int)$a['id']] = $a;

			$gross = (float)$fields['gross_salary'];
			foreach ($body['allowance_ids'] as $aid) {
				$aid = (int)$aid;
				if (!isset($allowanceMap[$aid])) continue;
				$percent = (float)$allowanceMap[$aid]['percent'];
				$amount = $gross * ($percent / 100);
				$this->db->insert('user_allowances', array(
					'allowance_id' => $aid,
					'user_id' => $id,
					'amount' => $amount,
					'created_by' => $this->current_user['user_id'],
					'created_at' => date('Y-m-d H:i:s'),
				));
			}
		}

		$this->_json(array('success' => true, 'id' => $id));
	}

	public function staff_delete($id = 0)
	{
		$id = (int)$id;
		if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
		$this->db->set('status', '0');
		$this->db->where('user_id', $id);
		$this->db->update('users');
		$this->_json(array('success' => true));
	}

	// ------------------------------------------------------------------
	// Loans (ports Loans::insert_loan / loans_approval)
	// ------------------------------------------------------------------

	public function loans()
	{
		$status = $this->input->get('status');

		$this->db->select('loans.*, users.first_name, users.last_name');
		$this->db->from('loans');
		$this->db->join('users', 'loans.user_id=users.user_id', 'inner');
		if ($status !== null && $status !== '') {
			$this->db->where('loans.status', $status);
		}
		$this->db->order_by('loans.id', 'DESC');
		$rows = $this->db->get()->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function loan_apply()
	{
		$body = $this->_body();
		$loan_type = isset($body['loan_type']) ? $body['loan_type'] : 'LOAN';
		$user_id = isset($body['user_id']) ? (int)$body['user_id'] : 0;
		$in_month = isset($body['in_month']) ? (int)$body['in_month'] : 1;
		$amount = isset($body['amount']) ? (float)$body['amount'] : 0;
		$reason = isset($body['reason']) ? $body['reason'] : '';

		if (!$user_id || $amount <= 0) $this->_json(array('success' => false, 'message' => 'user_id and amount required'), 422);

		$already = $this->db->get_where('loans', "user_id = $user_id and (status = 0 or (status = 1 and cash_given IS NULL))")->result_array();
		if (count($already) > 0) $this->_json(array('success' => false, 'message' => 'This user already has a pending loan application'), 422);

		$this->db->set('type', $loan_type);
		$this->db->set('user_id', $user_id);
		$this->db->set('amount_applied', $amount);
		$this->db->set('months', $in_month);
		$this->db->set('reason', $reason);
		$this->db->set('created_by', $this->current_user['user_id']);
		$this->db->set('undertaken_img', '');
		$this->db->insert('loans');
		$this->_json(array('success' => true, 'id' => (int)$this->db->insert_id()));
	}

	public function loan_approve($id = 0)
	{
		$id = (int)$id;
		if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
		$body = $this->_body();
		$amount = isset($body['amount']) ? (float)$body['amount'] : 0;
		$in_month = isset($body['in_month']) ? (int)$body['in_month'] : 0;
		$status = isset($body['status']) ? (int)$body['status'] : 0;
		if (!in_array($status, array(1, 2), true)) {
			$this->_json(array('success' => false, 'message' => 'status must be 1 (approve) or 2 (reject)'), 422);
		}

		$this->db->set('amount_approved', $amount);
		$this->db->set('months_approved', $in_month);
		$this->db->set('status', $status);
		$this->db->set('updated_by', $this->current_user['user_id']);
		$this->db->where('id', $id);
		$this->db->update('loans');
		$this->_json(array('success' => true));
	}

	// ------------------------------------------------------------------
	// Statutory rules (ports Payroll_statutory_rules.php)
	// ------------------------------------------------------------------

	public function statutory_rules()
	{
		$rows = $this->db->order_by('id', 'DESC')->get('payroll_statutory_rules')->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function statutory_rule($id = 0)
	{
		$id = (int)$id;
		$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

		if ($method === 'GET') {
			if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
			$row = $this->db->get_where('payroll_statutory_rules', array('id' => $id))->row_array();
			if (!$row) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
			$slabs = $this->db->order_by('min_salary', 'ASC')->get_where('payroll_statutory_rule_slabs', array('rule_id' => $id))->result_array();
			$this->_json(array('success' => true, 'data' => $row, 'slabs' => $slabs));
		}

		if ($method === 'POST') {
			$body = $this->_body();
			$data = array(
				'rule_name' => isset($body['rule_name']) ? $body['rule_name'] : '',
				'rule_code' => isset($body['rule_code']) ? $body['rule_code'] : '',
				'rule_type' => isset($body['rule_type']) ? $body['rule_type'] : 'other',
				'calculation_base' => isset($body['calculation_base']) ? $body['calculation_base'] : 'gross_salary',
				'wage_contribution_cap' => isset($body['wage_contribution_cap']) ? $body['wage_contribution_cap'] : 0,
				'status' => isset($body['status']) ? $body['status'] : 1,
				'effective_from' => (isset($body['effective_from']) && $body['effective_from'] !== '') ? $body['effective_from'] : null,
				'effective_to' => (isset($body['effective_to']) && $body['effective_to'] !== '') ? $body['effective_to'] : null,
				'expense_category' => isset($body['expense_category_id']) ? json_encode($body['expense_category_id']) : '[]',
				'updated_at' => date('Y-m-d H:i:s'),
			);

			if ($id === 0) {
				$data['created_at'] = date('Y-m-d H:i:s');
				$this->db->insert('payroll_statutory_rules', $data);
				$this->_json(array('success' => true, 'id' => (int)$this->db->insert_id()));
			}

			$exists = $this->db->get_where('payroll_statutory_rules', array('id' => $id))->row_array();
			if (!$exists) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
			$this->db->where('id', $id);
			$this->db->update('payroll_statutory_rules', $data);
			$this->_json(array('success' => true, 'id' => $id));
		}

		if ($method === 'DELETE') {
			if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
			$this->db->where('rule_id', $id);
			$this->db->delete('payroll_statutory_rule_slabs');
			$this->db->where('id', $id);
			$this->db->delete('payroll_statutory_rules');
			$this->_json(array('success' => true));
		}

		$this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
	}

	public function statutory_slab($id = 0)
	{
		$id = (int)$id;
		$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

		if ($method === 'POST') {
			$body = $this->_body();
			$data = array(
				'rule_id' => isset($body['rule_id']) ? $body['rule_id'] : 0,
				'min_salary' => isset($body['min_salary']) ? $body['min_salary'] : 0,
				'max_salary' => (isset($body['max_salary']) && $body['max_salary'] !== '') ? $body['max_salary'] : null,
				'employee_applicable' => !empty($body['employee_applicable']) ? 1 : 0,
				'employee_calculation_type' => isset($body['employee_calculation_type']) ? $body['employee_calculation_type'] : 'none',
				'employee_value' => isset($body['employee_value']) ? $body['employee_value'] : 0,
				'employer_applicable' => !empty($body['employer_applicable']) ? 1 : 0,
				'employer_calculation_type' => isset($body['employer_calculation_type']) ? $body['employer_calculation_type'] : 'none',
				'employer_value' => isset($body['employer_value']) ? $body['employer_value'] : 0,
				'expense_category_id' => isset($body['expense_category_id']) ? json_encode($body['expense_category_id']) : null,
				'status' => isset($body['status']) ? $body['status'] : 1,
				'updated_at' => date('Y-m-d H:i:s'),
			);
			if ($data['employee_applicable'] == 0) {
				$data['employee_calculation_type'] = 'none';
				$data['employee_value'] = 0;
			}
			if ($data['employer_applicable'] == 0) {
				$data['employer_calculation_type'] = 'none';
				$data['employer_value'] = 0;
			}

			if ($id === 0) {
				$data['created_at'] = date('Y-m-d H:i:s');
				$this->db->insert('payroll_statutory_rule_slabs', $data);
				$this->_json(array('success' => true, 'id' => (int)$this->db->insert_id()));
			}

			$this->db->where('id', $id);
			$this->db->update('payroll_statutory_rule_slabs', $data);
			$this->_json(array('success' => true, 'id' => $id));
		}

		if ($method === 'DELETE') {
			if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
			$this->db->where('id', $id);
			$this->db->delete('payroll_statutory_rule_slabs');
			$this->_json(array('success' => true));
		}

		$this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
	}

	// ------------------------------------------------------------------
	// Income tax (ports Payroll_income_tax.php)
	// ------------------------------------------------------------------

	public function tax_years()
	{
		$rows = $this->db->order_by('id', 'DESC')->get('payroll_tax_years')->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function tax_year($id = 0)
	{
		$id = (int)$id;
		$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

		if ($method === 'GET') {
			if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
			$row = $this->db->get_where('payroll_tax_years', array('id' => $id))->row_array();
			if (!$row) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
			$slabs = $this->db->order_by('min_annual_income', 'ASC')->get_where('payroll_income_tax_slabs', array('tax_year_id' => $id))->result_array();
			$this->_json(array('success' => true, 'data' => $row, 'slabs' => $slabs));
		}

		if ($method === 'POST') {
			$body = $this->_body();
			$data = array(
				'tax_year' => isset($body['tax_year']) ? $body['tax_year'] : '',
				'start_date' => isset($body['start_date']) ? $body['start_date'] : null,
				'end_date' => isset($body['end_date']) ? $body['end_date'] : null,
				'status' => isset($body['status']) ? $body['status'] : 1,
				'updated_at' => date('Y-m-d H:i:s'),
			);

			if ($id === 0) {
				$data['created_at'] = date('Y-m-d H:i:s');
				$this->db->insert('payroll_tax_years', $data);
				$this->_json(array('success' => true, 'id' => (int)$this->db->insert_id()));
			}

			$exists = $this->db->get_where('payroll_tax_years', array('id' => $id))->row_array();
			if (!$exists) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
			$this->db->where('id', $id);
			$this->db->update('payroll_tax_years', $data);
			$this->_json(array('success' => true, 'id' => $id));
		}

		if ($method === 'DELETE') {
			if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
			$this->db->where('tax_year_id', $id);
			$this->db->delete('payroll_income_tax_slabs');
			$this->db->where('id', $id);
			$this->db->delete('payroll_tax_years');
			$this->_json(array('success' => true));
		}

		$this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
	}

	public function tax_slab($id = 0)
	{
		$id = (int)$id;
		$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

		if ($method === 'POST') {
			$body = $this->_body();
			$data = array(
				'tax_year_id' => isset($body['tax_year_id']) ? $body['tax_year_id'] : 0,
				'min_annual_income' => isset($body['min_annual_income']) ? $body['min_annual_income'] : 0,
				'max_annual_income' => (isset($body['max_annual_income']) && $body['max_annual_income'] !== '') ? $body['max_annual_income'] : null,
				'fixed_tax' => isset($body['fixed_tax']) ? $body['fixed_tax'] : 0,
				'taxable_amount_above' => isset($body['taxable_amount_above']) ? $body['taxable_amount_above'] : 0,
				'tax_percentage' => isset($body['tax_percentage']) ? $body['tax_percentage'] : 0,
				'status' => isset($body['status']) ? $body['status'] : 1,
				'updated_at' => date('Y-m-d H:i:s'),
			);

			if ($id === 0) {
				$data['created_at'] = date('Y-m-d H:i:s');
				$this->db->insert('payroll_income_tax_slabs', $data);
				$this->_json(array('success' => true, 'id' => (int)$this->db->insert_id()));
			}

			$this->db->where('id', $id);
			$this->db->update('payroll_income_tax_slabs', $data);
			$this->_json(array('success' => true, 'id' => $id));
		}

		if ($method === 'DELETE') {
			if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
			$this->db->where('id', $id);
			$this->db->delete('payroll_income_tax_slabs');
			$this->_json(array('success' => true));
		}

		$this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
	}

	// ------------------------------------------------------------------
	// Salary (ports Salary::salary_list / salary_report / delete_salary)
	// ------------------------------------------------------------------

	public function salary_list()
	{
		$campus_id = $this->input->get('campus_id');
		$month = $this->input->get('month');
		$year = $this->input->get('year');
		if ($month === null || $month === '') $month = date('M', strtotime('-1 months'));
		if ($year === null || $year === '') $year = date('Y', strtotime('-1 months'));

		$this->db->select(
			'users.user_id, users.first_name, users.last_name, users.salary, users.gross_salary, users.campus_id, campuses.campus_name,'
			. '(select earned_salary from payroll where user_id = users.user_id and payroll_month = ' . $this->db->escape($month) . ' and payroll_year = ' . $this->db->escape($year) . ' limit 1) as earned_salary'
		);
		$this->db->from('users');
		$this->db->join('campuses', 'users.campus_id=campuses.campus_id', 'left');
		$this->db->where('users.status', 1);
		if ($campus_id !== null && $campus_id !== '') $this->db->where('users.campus_id', $campus_id);
		$this->db->order_by('users.first_name', 'ASC');
		$rows = $this->db->get()->result_array();

		foreach ($rows as &$r) {
			$r['payroll_exists'] = $r['earned_salary'] !== null;
		}
		unset($r);

		$this->_json(array('success' => true, 'month' => $month, 'year' => $year, 'data' => $rows));
	}

	public function salary_report()
	{
		$campus_id = $this->input->get('campus_id');
		$to_date = $this->input->get('to_date');
		if ($to_date === null || $to_date === '') $to_date = date('Y-m-d');
		$month = date('M', strtotime($to_date));
		$year = date('Y', strtotime($to_date));

		$this->db->select('payroll.*, users.first_name, users.last_name, users.designation_id, campuses.campus_name, departments.department_name');
		$this->db->from('payroll');
		$this->db->join('users', 'payroll.user_id=users.user_id', 'inner');
		$this->db->join('campuses', 'users.campus_id=campuses.campus_id', 'left');
		$this->db->join('departments', 'departments.department_id=users.department_id', 'left');
		$this->db->where('payroll.payroll_month', $month);
		$this->db->where('payroll.payroll_year', $year);
		if ($campus_id !== null && $campus_id !== '') $this->db->where('users.campus_id', $campus_id);
		$rows = $this->db->get()->result_array();

		$designationMap = $this->_designation_name_map();
		foreach ($rows as &$r) {
			$r['designation_names'] = $this->_designation_names($r['designation_id'], $designationMap);
		}
		unset($r);

		$this->_json(array('success' => true, 'month' => $month, 'year' => $year, 'to_date' => $to_date, 'data' => $rows));
	}

	/** Mirrors Salary::delete_salary($user_id,$month,$year). */
	public function salary_delete($user_id = 0, $month = '', $year = '')
	{
		$user_id = (int)$user_id;
		if (!$user_id || $month === '' || $year === '') {
			$this->_json(array('success' => false, 'message' => 'user_id, month and year required'), 422);
		}

		$payroll = $this->db->get_where('payroll', array('user_id' => $user_id, 'payroll_month' => $month, 'payroll_year' => $year))->row_array();
		if (!$payroll) $this->_json(array('success' => false, 'message' => 'Payroll not found'), 404);

		$this->db->set('amount_paid', 0);
		$this->db->set('paid_at', null);
		$this->db->set('payroll_id', null);
		$this->db->where('payroll_id', $payroll['id']);
		$this->db->update('loan_plan');

		$this->db->where('id', $payroll['id']);
		$this->db->delete('payroll');

		$this->_json(array('success' => true));
	}
}
