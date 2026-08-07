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
			array('key' => 'student_shifts', 'label' => 'Student Shifts', 'enabled' => true),
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
		$rows = $this->db
			->select('study_type.*, courses.course_name')
			->from('study_type')
			->join('courses', 'courses.course_id = study_type.course_id', 'left')
			->order_by('courses.course_name', 'ASC')
			->order_by('study_type.name', 'ASC')
			->get()
			->result_array();
		$data = array();
		foreach ($rows as $row) {
			$label = $row['name'];
			if (!empty($row['course_name'])) {
				$label .= ' — ' . $row['course_name'];
			}
			$data[] = array(
				'id' => (int)$row['id'],
				'name' => $row['name'],
				'course_name' => isset($row['course_name']) ? $row['course_name'] : '',
				'label' => $label,
			);
		}
		$this->_json(array('success' => true, 'data' => $data));
	}

	/** Study types for attendance filter — optional campus scope via shifts table. */
	public function attendance_study_types_lookup()
	{
		if (!$this->db->table_exists('study_type')) {
			$this->_json(array('success' => true, 'data' => array()));
		}
		$campus_id = $this->input->get('campus_id');

		$this->db->select('study_type.id, study_type.name, courses.course_name');
		$this->db->from('study_type');
		$this->db->join('courses', 'courses.course_id = study_type.course_id', 'left');

		if ($campus_id !== null && $campus_id !== '') {
			$cid = (int)$campus_id;
			$this->db->join('shifts', 'shifts.study_type_id = study_type.id', 'inner');
			$this->db->group_start();
			$this->db->where('shifts.campus_id', $cid);
			$this->db->or_where('FIND_IN_SET(' . $cid . ', shifts.campus_id) >', 0, false);
			$this->db->group_end();
			$this->db->group_by('study_type.id, study_type.name, courses.course_name');
		}

		$this->db->order_by('courses.course_name', 'ASC');
		$this->db->order_by('study_type.name', 'ASC');
		$rows = $this->db->get()->result_array();

		$data = array();
		foreach ($rows as $row) {
			$label = $row['name'];
			if (!empty($row['course_name'])) {
				$label .= ' — ' . $row['course_name'];
			}
			$data[] = array(
				'id' => (int)$row['id'],
				'label' => $label,
			);
		}
		$this->_json(array('success' => true, 'data' => $data));
	}

	/** Student shifts for attendance filter — scoped by campus + study type (ports Timetable::getShifts). */
	public function attendance_shifts_lookup()
	{
		if (!$this->db->table_exists('shifts')) {
			$this->_json(array('success' => true, 'data' => array()));
		}

		$campus_id = $this->input->get('campus_id');
		$study_type_id = $this->input->get('study_type_id');
		$study_type_ids = $this->input->get('study_type_ids');

		$this->db->select('shifts.id, shifts.name, study_type.name as study_type_name, courses.course_name');
		$this->db->from('shifts');
		$this->db->join('study_type', 'study_type.id = shifts.study_type_id', 'left');
		$this->db->join('courses', 'courses.course_id = study_type.course_id', 'left');

		if ($campus_id !== null && $campus_id !== '') {
			$cid = (int)$campus_id;
			$this->db->group_start();
			$this->db->where('shifts.campus_id', $cid);
			$this->db->or_where('FIND_IN_SET(' . $cid . ', shifts.campus_id) >', 0, false);
			$this->db->group_end();
		}

		$typeIds = array();
		if ($study_type_ids !== null && $study_type_ids !== '') {
			$typeIds = array_values(array_filter(array_map('intval', explode(',', (string)$study_type_ids))));
		} elseif ($study_type_id !== null && $study_type_id !== '') {
			$typeIds = array((int)$study_type_id);
		}
		if (count($typeIds)) {
			$this->db->where_in('shifts.study_type_id', $typeIds);
		}

		$this->db->order_by('shifts.name', 'ASC');
		$rows = $this->db->get()->result_array();

		$data = array();
		foreach ($rows as $row) {
			$parts = array($row['name']);
			if (!empty($row['study_type_name'])) $parts[] = $row['study_type_name'];
			if (!empty($row['course_name'])) $parts[] = $row['course_name'];
			$data[] = array(
				'id' => (int)$row['id'],
				'name' => $row['name'],
				'label' => implode(' — ', $parts),
			);
		}
		$this->_json(array('success' => true, 'data' => $data));
	}

	/** Student shift names for attendance report filter (legacy `shifts` table). */
	public function shifts_lookup()
	{
		if (!$this->db->table_exists('shifts')) {
			$this->_json(array('success' => true, 'data' => array()));
		}
		$rows = $this->db->order_by('name', 'ASC')->get('shifts')->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	/** Courses for student shift form (legacy timetable/shifts). */
	public function courses_lookup()
	{
		if (!$this->db->table_exists('courses')) {
			$this->_json(array('success' => true, 'data' => array()));
		}
		$rows = $this->db->order_by('course_name', 'ASC')->get('courses')->result_array();
		$data = array();
		foreach ($rows as $r) {
			$data[] = array(
				'id' => (int)$r['course_id'],
				'label' => $r['course_name'],
			);
		}
		$this->_json(array('success' => true, 'data' => $data));
	}

	/** Study types filtered by course (legacy Timetable::getCourseStudyTypes). */
	public function study_types_by_course()
	{
		$course_id = (int)$this->input->get('course_id');
		if (!$this->db->table_exists('study_type')) {
			$this->_json(array('success' => true, 'data' => array()));
		}
		$this->db->select('id, name, course_id');
		$this->db->from('study_type');
		if ($course_id > 0) {
			$this->db->where('course_id', $course_id);
		}
		$this->db->order_by('name', 'ASC');
		$rows = $this->db->get()->result_array();
		$data = array();
		foreach ($rows as $r) {
			$data[] = array(
				'id' => (int)$r['id'],
				'label' => $r['name'],
			);
		}
		$this->_json(array('success' => true, 'data' => $data));
	}

	/** Student shifts list (legacy timetable/shifts — `shifts` table). */
	public function student_shifts()
	{
		if (!$this->db->table_exists('shifts')) {
			$this->_json(array('success' => true, 'data' => array()));
		}

		$this->db->select('shifts.*, users.first_name, users.last_name, study_type.name as study_type_name, courses.course_id as shift_course, courses.course_name');
		$this->db->from('shifts');
		$this->db->join('users', 'users.user_id = shifts.created_by', 'left');
		$this->db->join('study_type', 'study_type.id = shifts.study_type_id', 'left');
		$this->db->join('courses', 'study_type.course_id = courses.course_id', 'left');
		$this->db->order_by('shifts.name', 'ASC');
		$rows = $this->db->get()->result_array();

		$campusMap = array();
		foreach ($this->db->get('campuses')->result_array() as $c) {
			$campusMap[(int)$c['campus_id']] = $c['campus_name'];
		}

		$data = array();
		foreach ($rows as $r) {
			$campusNames = array();
			$campusIds = array();
			foreach (array_filter(explode(',', (string)$r['campus_id'])) as $cid) {
				$cid = (int)$cid;
				if ($cid <= 0) continue;
				$campusIds[] = $cid;
				if (isset($campusMap[$cid])) $campusNames[] = $campusMap[$cid];
			}
			$data[] = array(
				'id' => (int)$r['id'],
				'name' => $r['name'],
				'start_time' => $r['start_time'],
				'end_time' => $r['end_time'],
				'study_type_id' => (int)$r['study_type_id'],
				'study_type_name' => isset($r['study_type_name']) ? $r['study_type_name'] : '',
				'shift_course' => isset($r['shift_course']) ? (int)$r['shift_course'] : 0,
				'course_name' => isset($r['course_name']) ? $r['course_name'] : '',
				'campus_id' => $r['campus_id'],
				'campus_ids' => $campusIds,
				'campus_names' => $campusNames,
				'created_by_name' => trim($r['first_name'] . ' ' . $r['last_name']),
				'created_at' => isset($r['created_at']) ? $r['created_at'] : '',
			);
		}
		$this->_json(array('success' => true, 'data' => $data));
	}

	/** Student shift CRUD (legacy timetable/insert_shift, update_shift, delete_shift). */
	public function student_shift($id = 0)
	{
		$id = (int)$id;
		$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

		if (!$this->db->table_exists('shifts')) {
			$this->_json(array('success' => false, 'message' => 'shifts table missing'), 500);
		}

		if ($method === 'GET') {
			if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
			$this->db->select('shifts.*, study_type.course_id as shift_course');
			$this->db->from('shifts');
			$this->db->join('study_type', 'study_type.id = shifts.study_type_id', 'left');
			$this->db->where('shifts.id', $id);
			$row = $this->db->get()->row_array();
			if (!$row) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
			$campusIds = array_values(array_filter(array_map('intval', explode(',', (string)$row['campus_id']))));
			$row['campus_ids'] = $campusIds;
			$row['shift_course'] = isset($row['shift_course']) ? (int)$row['shift_course'] : 0;
			$this->_json(array('success' => true, 'data' => $row));
		}

		if ($method === 'POST') {
			$body = $this->_body();
			$name = isset($body['name']) ? trim((string)$body['name']) : '';
			$startTime = isset($body['start_time']) ? trim((string)$body['start_time']) : '';
			$endTime = isset($body['end_time']) ? trim((string)$body['end_time']) : '';
			$studyTypeId = isset($body['study_type_id']) ? (int)$body['study_type_id'] : 0;
			$campusIds = isset($body['campus_ids']) ? (array)$body['campus_ids'] : array();

			if ($name === '' || $startTime === '' || $endTime === '' || $studyTypeId <= 0) {
				$this->_json(array('success' => false, 'message' => 'name, start_time, end_time and study_type_id required'), 422);
			}
			if (!count($campusIds)) {
				$this->_json(array('success' => false, 'message' => 'At least one campus required'), 422);
			}

			$campusCsv = implode(',', array_values(array_filter(array_map('intval', $campusIds))));

			if ($id === 0) {
				$this->db->set('name', $name);
				$this->db->set('start_time', $startTime);
				$this->db->set('end_time', $endTime);
				$this->db->set('campus_id', $campusCsv);
				$this->db->set('study_type_id', $studyTypeId);
				$this->db->set('created_by', (int)$this->current_user['user_id']);
				$this->db->insert('shifts');
				$id = (int)$this->db->insert_id();
			} else {
				$exists = $this->db->get_where('shifts', array('id' => $id))->row_array();
				if (!$exists) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
				$this->db->set('name', $name);
				$this->db->set('start_time', $startTime);
				$this->db->set('end_time', $endTime);
				$this->db->set('campus_id', $campusCsv);
				$this->db->set('study_type_id', $studyTypeId);
				$this->db->where('id', $id);
				$this->db->update('shifts');
			}

			$this->_json(array('success' => true, 'id' => $id));
		}

		if ($method === 'DELETE') {
			if (!$id) $this->_json(array('success' => false, 'message' => 'id required'), 422);
			$this->db->where('id', $id);
			$this->db->delete('shifts');
			$this->_json(array('success' => true));
		}

		$this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
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
			$r['combo_label'] = staff_shift_label($r);
			$r['timing_configured'] = $r['timing_count'] > 0;
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

		$campusMap = array();
		foreach ($this->db->get('campuses')->result_array() as $c) {
			$campusMap[(int)$c['campus_id']] = $c['campus_name'];
		}
		$staffTypeMap = array();
		foreach ($this->db->get('staff_type')->result_array() as $st) {
			$staffTypeMap[(int)$st['staff_type_id']] = $st['staff_type_name'];
		}

		$data = array();
		foreach ($rows as $row) {
			$campusNames = array();
			foreach (array_filter(explode(',', (string)$row['campus_ids'])) as $cid) {
				if (isset($campusMap[(int)$cid])) $campusNames[] = $campusMap[(int)$cid];
			}
			$staffTypeNames = array();
			foreach (array_filter(explode(',', (string)$row['staff_type_ids'])) as $sid) {
				if (isset($staffTypeMap[(int)$sid])) $staffTypeNames[] = $staffTypeMap[(int)$sid];
			}
			$shiftLabels = array();
			$shiftIds = array_filter(explode(',', (string)$row['shift_ids']));
			if (count($shiftIds)) {
				$this->db->select('shifts.name, study_type.name as study_type_name, courses.course_name');
				$this->db->from('shifts');
				$this->db->join('study_type', 'study_type.id = shifts.study_type_id', 'left');
				$this->db->join('courses', 'courses.course_id = study_type.course_id', 'left');
				$this->db->where_in('shifts.id', $shiftIds);
				foreach ($this->db->get()->result_array() as $sh) {
					$parts = array($sh['name']);
					if (!empty($sh['study_type_name'])) $parts[] = $sh['study_type_name'];
					if (!empty($sh['course_name'])) $parts[] = $sh['course_name'];
					$shiftLabels[] = implode(' - ', $parts);
				}
			}
			$row['campus_names'] = $campusNames;
			$row['staff_type_names'] = $staffTypeNames;
			$row['shift_labels'] = $shiftLabels;
			$row['date_label'] = date('F d, Y', strtotime($row['date']));
			$data[] = $row;
		}

		$this->_json(array('success' => true, 'from' => $from, 'to' => $to, 'data' => $data));
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

		$reason = isset($body['reason_detail']) ? $body['reason_detail'] : (isset($body['reason']) ? $body['reason'] : '');

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

	public function holiday_find_staff()
	{
		$body = $this->_body();
		$campus_ids = isset($body['campus_ids']) ? (array)$body['campus_ids'] : array();
		$staff_type_ids = isset($body['staff_type_ids']) ? (array)$body['staff_type_ids'] : array();
		if (!count($campus_ids) || !count($staff_type_ids)) {
			$this->_json(array('success' => true, 'data' => array()));
		}

		$users = $this->db->query(
			'SELECT user_id, first_name, last_name FROM users WHERE status=1 AND campus_id IN (' .
			implode(',', array_map('intval', $campus_ids)) . ') AND staff_type_id IN (' .
			implode(',', array_map('intval', $staff_type_ids)) . ') ORDER BY first_name ASC'
		)->result_array();

		$data = array();
		foreach ($users as $u) {
			$data[] = array(
				'user_id' => (int)$u['user_id'],
				'label' => trim($u['first_name'] . ' ' . $u['last_name']),
			);
		}
		$this->_json(array('success' => true, 'data' => $data));
	}

	public function holiday_find_shifts()
	{
		$body = $this->_body();
		$campus_ids = isset($body['campus_ids']) ? (array)$body['campus_ids'] : array();
		if (!count($campus_ids)) {
			$this->_json(array('success' => true, 'data' => array()));
		}

		$shifts = $this->db->query(
			'SELECT shifts.id, shifts.name, study_type.name as study_type_name, courses.course_name
			FROM shifts
			LEFT JOIN study_type ON study_type.id = shifts.study_type_id
			LEFT JOIN courses ON courses.course_id = study_type.course_id
			WHERE shifts.campus_id IN (' . implode(',', array_map('intval', $campus_ids)) . ')
			ORDER BY shifts.name ASC'
		)->result_array();

		$data = array();
		foreach ($shifts as $shift) {
			$parts = array($shift['name']);
			if (!empty($shift['study_type_name'])) $parts[] = $shift['study_type_name'];
			if (!empty($shift['course_name'])) $parts[] = $shift['course_name'];
			$data[] = array(
				'id' => (int)$shift['id'],
				'label' => implode(' - ', $parts),
			);
		}
		$this->_json(array('success' => true, 'data' => $data));
	}

	public function holiday_find_shift_students()
	{
		$body = $this->_body();
		$shift_ids = isset($body['shift_ids']) ? (array)$body['shift_ids'] : array();
		if (!count($shift_ids)) {
			$this->_json(array('success' => true, 'student_ids' => '', 'count' => 0));
		}

		$students = $this->db->query(
			'SELECT student_id FROM students WHERE status=1 AND shift IN (' .
			implode(',', array_map('intval', $shift_ids)) . ')'
		)->result_array();

		$ids = array();
		foreach ($students as $s) $ids[] = (int)$s['student_id'];
		$this->_json(array(
			'success' => true,
			'student_ids' => implode(',', $ids),
			'count' => count($ids),
		));
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
	// Attendance — ports Attendence.php (grid report, people, manual add)
	// ------------------------------------------------------------------

	public function attendance_sessions()
	{
		$campus_id = $this->input->get('campus_id');
		if ($campus_id === null || $campus_id === '') {
			$this->_json(array('success' => true, 'data' => array()));
		}
		$this->db->distinct();
		$this->db->select('session');
		$this->db->where('campus_id', (int)$campus_id);
		$this->db->order_by('session', 'ASC');
		$sessions = array();
		foreach ($this->db->get('classes')->result_array() as $r) {
			if ($r['session'] !== '' && $r['session'] !== null) {
				$sessions[] = $r['session'];
			}
		}
		$this->_json(array('success' => true, 'data' => $sessions));
	}

	public function attendance_people()
	{
		$type = $this->input->get('type');
		$campus_id = $this->input->get('campus_id');
		if ($type !== 'student') $type = 'staff';
		$data = array();

		if ($type === 'staff') {
			$this->db->select('users.first_name, users.last_name, machine_data.machine_id');
			$this->db->from('users');
			$this->db->join('campuses', 'campuses.campus_id=users.campus_id', 'inner');
			$this->db->join('machine_data', 'machine_data.teacher_student_id=users.user_id', 'inner');
			$this->db->where(array('users.status' => '1', 'machine_data.type' => 'teacher'));
			if ($campus_id !== null && $campus_id !== '') {
				$this->db->where('campuses.campus_id', (int)$campus_id);
			}
			foreach ($this->db->get()->result_array() as $r) {
				$data[] = array(
					'machine_id' => (int)$r['machine_id'],
					'label' => trim($r['first_name'] . ' ' . $r['last_name']),
				);
			}
		} else {
			$this->db->select('students.first_name, students.last_name, students.roll_no, machine_data.machine_id');
			$this->db->from('students');
			$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
			$this->db->join('campuses', 'campuses.campus_id=classes.campus_id', 'inner');
			$this->db->join('machine_data', 'machine_data.teacher_student_id=students.student_id', 'inner');
			$this->db->where(array('students.status' => '1', 'machine_data.type' => 'student'));
			if ($campus_id !== null && $campus_id !== '') {
				$this->db->where('students.study_campus', (int)$campus_id);
			}
			foreach ($this->db->get()->result_array() as $r) {
				$data[] = array(
					'machine_id' => (int)$r['machine_id'],
					'label' => trim($r['first_name'] . ' ' . $r['last_name']) . ' (' . $r['roll_no'] . ')',
				);
			}
		}

		$this->_json(array('success' => true, 'data' => $data));
	}

	public function attendance_report()
	{
		if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
			$this->_attendance_report_grid($this->_body());
			return;
		}

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

	public function attendance_create()
	{
		$body = $this->_body();
		$datetime = isset($body['datetime']) ? trim((string)$body['datetime']) : '';
		$campus_id = isset($body['campus_id']) ? (int)$body['campus_id'] : 0;
		$machine_user_ids = isset($body['machine_user_ids']) ? $body['machine_user_ids'] : array();
		if (is_string($machine_user_ids) && $machine_user_ids !== '') {
			$machine_user_ids = array_filter(explode(',', $machine_user_ids));
		}
		if ($datetime === '' || !$campus_id || !count($machine_user_ids)) {
			$this->_json(array('success' => false, 'message' => 'datetime, campus_id and machine_user_ids required'), 422);
		}

		$campus = $this->db->get_where('campuses', array('campus_id' => $campus_id))->row_array();
		if (!$campus) $this->_json(array('success' => false, 'message' => 'Campus not found'), 404);
		$campus_code = isset($campus['campus_code']) ? $campus['campus_code'] : '';

		$ts = strtotime(str_replace('T', ' ', $datetime));
		if ($ts === false) {
			$this->_json(array('success' => false, 'message' => 'Invalid datetime'), 422);
		}
		$attendence_time = date('Y-m-d H:i:s', $ts);

		$created = 0;
		foreach ($machine_user_ids as $machine_user_id) {
			$machine_user_id = (int)$machine_user_id;
			if (!$machine_user_id) continue;
			$this->db->set('time', $attendence_time);
			$this->db->set('machine_user_id', $machine_user_id);
			$this->db->set('campus_code', $campus_code);
			$this->db->set('created_by', $this->_current_user_name());
			$this->db->insert('attendence');
			$created++;
		}

		$this->_json(array('success' => true, 'message' => 'Attendance added', 'created' => $created));
	}

	public function attendance_delete()
	{
		$body = $this->_body();
		$machine_user_id = isset($body['machine_user_id']) ? (int)$body['machine_user_id'] : 0;
		$date = isset($body['date']) ? $body['date'] : '';
		if (!$machine_user_id || $date === '') {
			$this->_json(array('success' => false, 'message' => 'machine_user_id and date required'), 422);
		}
		if (!$this->_attendance_can_manage()) {
			$this->_json(array('success' => false, 'message' => 'Not allowed'), 403);
		}
		$this->db->where(array(
			'machine_user_id' => $machine_user_id,
			'time >=' => $date . ' 00:00:00',
			'time <=' => $date . ' 23:59:59',
		));
		$this->db->delete('attendence');
		$this->_json(array('success' => true, 'message' => 'Attendance deleted'));
	}

	public function attendance_halfday()
	{
		$body = $this->_body();
		$machine_user_id = isset($body['machine_user_id']) ? (int)$body['machine_user_id'] : 0;
		$date = isset($body['date']) ? $body['date'] : '';
		if (!$machine_user_id || $date === '') {
			$this->_json(array('success' => false, 'message' => 'machine_user_id and date required'), 422);
		}
		if (!$this->_attendance_can_manage()) {
			$this->_json(array('success' => false, 'message' => 'Not allowed'), 403);
		}
		$this->db->set('halfday', 1);
		$this->db->where(array(
			'machine_user_id' => $machine_user_id,
			'time >=' => $date . ' 00:00:00',
			'time <=' => $date . ' 23:59:59',
		));
		$this->db->update('attendence');
		$this->_json(array('success' => true, 'message' => 'Marked half day'));
	}

	private function _attendance_can_manage()
	{
		$role = isset($this->current_user['role']) ? $this->current_user['role'] : '';
		$user_id = isset($this->current_user['user_id']) ? (int)$this->current_user['user_id'] : 0;
		return $role === 'Admin' || $user_id === 77;
	}

	private function _create_date_range($from, $to)
	{
		$ary = array();
		$fromTs = strtotime($from);
		$toTs = strtotime($to);
		if ($fromTs === false || $toTs === false || $toTs < $fromTs) return $ary;
		$cursor = $fromTs;
		while ($cursor <= $toTs) {
			$ary[] = date('Y-m-d', $cursor);
			$cursor += 86400;
		}
		return $ary;
	}

	private function _attendance_string_list($value)
	{
		if (is_array($value)) return array_values(array_filter(array_map('strval', $value)));
		if (is_string($value) && $value !== '') return array_values(array_filter(explode(',', $value)));
		return array();
	}

	private function _attendance_int_list($value)
	{
		if (is_array($value)) return array_values(array_filter(array_map('intval', $value)));
		if (is_string($value) && $value !== '') return array_values(array_filter(array_map('intval', explode(',', $value))));
		return array();
	}

	private function _attendance_resolve_shift_ids($value)
	{
		$ids = $this->_attendance_int_list($value);
		if (count($ids)) return $ids;
		$names = $this->_attendance_string_list($value);
		if (!count($names) || !$this->db->table_exists('shifts')) return array();
		$this->db->where_in('name', $names);
		$rows = $this->db->get('shifts')->result_array();
		$out = array();
		foreach ($rows as $r) $out[] = (int)$r['id'];
		return array_values(array_unique($out));
	}

	private function _attendance_resolve_study_type_ids($value)
	{
		$ids = $this->_attendance_int_list($value);
		if (count($ids)) return $ids;
		$names = $this->_attendance_string_list($value);
		if (!count($names) || !$this->db->table_exists('study_type')) return array();
		$this->db->where_in('name', $names);
		$rows = $this->db->get('study_type')->result_array();
		$out = array();
		foreach ($rows as $r) $out[] = (int)$r['id'];
		return array_values(array_unique($out));
	}

	private function _attendance_resolve_machine_ids($type, $campus_id, $class_session, $shift_ids, $study_type_ids)
	{
		$ids = array();
		if ($type === 'staff') {
			$this->db->select('machine_data.machine_id');
			$this->db->from('machine_data');
			$this->db->join('users', 'users.user_id=machine_data.teacher_student_id and machine_data.type = "teacher"', 'inner');
			$this->db->join('campuses', 'campuses.campus_id=users.campus_id', 'inner');
			$this->db->where(array('users.status' => 1, 'machine_data.type' => 'teacher'));
			if ($campus_id !== null && $campus_id !== '') {
				$this->db->where('users.campus_id', (int)$campus_id);
			}
			foreach ($this->db->get()->result_array() as $r) {
				$ids[] = (int)$r['machine_id'];
			}
		} else {
			$this->db->select('machine_data.machine_id');
			$this->db->from('machine_data');
			$this->db->join('students', 'students.student_id=machine_data.teacher_student_id', 'inner');
			$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
			$this->db->join('campuses', 'campuses.campus_id=classes.campus_id', 'inner');
			$this->db->where(array('machine_data.type' => 'student', 'students.status' => 1));
			if ($campus_id !== null && $campus_id !== '') {
				$this->db->where('campuses.campus_id', (int)$campus_id);
			}
			if ($class_session !== null && $class_session !== '') {
				$this->db->where('classes.session', $class_session);
			}
			if (count($shift_ids)) {
				$this->db->where_in('students.shift', $shift_ids);
			}
			if (count($study_type_ids)) {
				$this->db->where_in('students.study_type', $study_type_ids);
			}
			foreach ($this->db->get()->result_array() as $r) {
				$ids[] = (int)$r['machine_id'];
			}
		}
		return array_values(array_unique($ids));
	}

	private function _attendance_person_info($machine_user_id, $type)
	{
		$machine_user_id = (int)$machine_user_id;
		if ($type === 'staff') {
			$this->db->select('users.*, campuses.campus_name, machine_data.machine_id');
			$this->db->from('machine_data');
			$this->db->join('users', 'users.user_id=machine_data.teacher_student_id', 'inner');
			$this->db->join('campuses', 'campuses.campus_id=users.campus_id', 'inner');
			$this->db->where(array('machine_data.machine_id' => $machine_user_id, 'machine_data.type' => 'teacher'));
			$row = $this->db->get()->row_array();
			if (!$row) return null;
			return array(
				'machine_user_id' => $machine_user_id,
				'name' => trim($row['first_name'] . ' ' . $row['last_name']),
				'campus_name' => $row['campus_name'],
			);
		}

		$this->db->select('students.*, campuses.campus_name, classes.session, machine_data.machine_id');
		$this->db->from('machine_data');
		$this->db->join('students', 'students.student_id=machine_data.teacher_student_id', 'inner');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
		$this->db->join('campuses', 'campuses.campus_id=classes.campus_id', 'inner');
		$this->db->where(array('machine_data.type' => 'student', 'machine_data.machine_id' => $machine_user_id));
		$row = $this->db->get()->row_array();
		if (!$row) return null;

		$study_campus_name = '';
		if (!empty($row['study_campus'])) {
			$c = $this->db->get_where('campuses', array('campus_id' => $row['study_campus']))->row_array();
			if ($c) $study_campus_name = $c['campus_name'];
		}

		return array(
			'machine_user_id' => $machine_user_id,
			'name' => trim($row['first_name'] . ' ' . $row['last_name']),
			'campus_name' => $row['campus_name'],
			'roll_no' => isset($row['roll_no']) ? $row['roll_no'] : '',
			'study_type' => isset($row['study_type']) ? $row['study_type'] : '',
			'shift' => isset($row['shift']) ? $row['shift'] : '',
			'mobile' => isset($row['mobile']) ? $row['mobile'] : '',
			'emergency_no' => isset($row['emergency_no']) ? $row['emergency_no'] : '',
			'study_campus_name' => $study_campus_name,
			'class_name' => isset($row['section']) ? $row['section'] : '',
			'session' => isset($row['session']) ? $row['session'] : '',
		);
	}

	private function _attendance_day_row($machine_user_id, $date, $type, $person)
	{
		$row = array(
			'machine_user_id' => (int)$machine_user_id,
			'date' => $date,
			'date_label' => date('F d, Y', strtotime($date)),
			'campus_name' => $person['campus_name'],
			'name' => $person['name'],
			'is_holiday' => false,
			'type' => $type,
		);

		if ($type === 'student') {
			$row['roll_no'] = $person['roll_no'];
			$row['study_type'] = $person['study_type'];
			$row['shift'] = $person['shift'];
			$row['mobile'] = $person['mobile'];
			$row['emergency_no'] = $person['emergency_no'];
			$row['study_campus_name'] = $person['study_campus_name'];
			$row['class_name'] = $person['class_name'];
			$row['session'] = $person['session'];
		}

		$holiday = $this->db->get_where('holidays', array('date' => $date))->result_array();
		if (count($holiday) > 0) {
			$row['is_holiday'] = true;
			if ($type === 'student') {
				$row['present_absent'] = 'Holiday';
			} else {
				$row['check_in'] = 'Holiday';
				$row['check_out'] = 'Holiday';
				$row['mark'] = 'holiday';
			}
			return $row;
		}

		$checkin = $this->db->query(
			'SELECT * FROM attendence WHERE machine_user_id=' . (int)$machine_user_id .
			' AND time>="' . $this->db->escape_str($date) . ' 00:00:00" AND time<"' . $this->db->escape_str($date) . ' 23:59:59" ORDER BY time ASC LIMIT 1'
		)->row_array();
		$checkout = $this->db->query(
			'SELECT * FROM attendence WHERE machine_user_id=' . (int)$machine_user_id .
			' AND time>="' . $this->db->escape_str($date) . ' 00:00:00.00" AND time<"' . $this->db->escape_str($date) . ' 23:59:59.999" ORDER BY time DESC LIMIT 1'
		)->row_array();

		if ($type === 'student') {
			$row['present_absent'] = $checkin ? 'Present' : 'Absent';
			return $row;
		}

		$row['check_in'] = $checkin ? date('h:i:s A', strtotime($checkin['time'])) : 'Absent';
		$row['check_out'] = $checkout ? date('h:i:s A', strtotime($checkout['time'])) : 'Absent';

		if ($checkin) {
			$half = $this->db->query(
				'SELECT * FROM attendence WHERE machine_user_id=' . (int)$machine_user_id .
				' AND time>="' . $this->db->escape_str($date) . ' 00:00:00.00" AND time<"' . $this->db->escape_str($date) . ' 23:59:59.999" AND halfday=1 ORDER BY time DESC LIMIT 1'
			)->row_array();
			$row['mark'] = $half ? 'halfday' : 'fullday';
		} else {
			$row['mark'] = 'absent';
		}

		$row['can_manage'] = $this->_attendance_can_manage() && (bool)$checkin;
		return $row;
	}

	private function _attendance_report_grid($body)
	{
		$type = (isset($body['type']) && $body['type'] === 'student') ? 'student' : 'staff';
		$from = isset($body['from']) && $body['from'] !== '' ? $body['from'] : date('Y-m-d');
		$to = isset($body['to']) && $body['to'] !== '' ? $body['to'] : date('Y-m-d');
		$campus_id = isset($body['campus_id']) ? $body['campus_id'] : '';
		$class_session = isset($body['class_session']) ? $body['class_session'] : '';

		$shift_ids = $this->_attendance_int_list(isset($body['shift_ids']) ? $body['shift_ids'] : array());
		if (!count($shift_ids) && isset($body['shift'])) {
			$shift_ids = $this->_attendance_resolve_shift_ids($body['shift']);
		}

		$study_type_ids = $this->_attendance_int_list(isset($body['study_type_ids']) ? $body['study_type_ids'] : array());
		if (!count($study_type_ids) && isset($body['study_type'])) {
			$study_type_ids = $this->_attendance_resolve_study_type_ids($body['study_type']);
		}

		$machine_user_ids = $this->_attendance_int_list(isset($body['machine_user_ids']) ? $body['machine_user_ids'] : array());

		$dates = $this->_create_date_range($from, $to);
		if (!count($machine_user_ids)) {
			$machine_user_ids = $this->_attendance_resolve_machine_ids($type, $campus_id, $class_session, $shift_ids, $study_type_ids);
		}

		$data = array();
		foreach ($machine_user_ids as $machine_user_id) {
			$person = $this->_attendance_person_info($machine_user_id, $type);
			if (!$person) continue;
			foreach ($dates as $date) {
				$data[] = $this->_attendance_day_row($machine_user_id, $date, $type, $person);
			}
		}

		$this->_json(array(
			'success' => true,
			'type' => $type,
			'from' => $from,
			'to' => $to,
			'data' => $data,
			'count' => count($data),
		));
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
		$q = trim((string)$this->input->get('q'));
		$campus_id = $this->input->get('campus_id');

		$this->db->select('users.*, campuses.campus_name, staff_type.staff_type_name, departments.department_name, machine_data.machine_id');
		$this->db->from('users');
		$this->db->join('campuses', 'campuses.campus_id=users.campus_id', 'left');
		$this->db->join('staff_type', 'staff_type.staff_type_id=users.staff_type_id', 'left');
		$this->db->join('departments', 'departments.department_id=users.department_id', 'left');
		$this->db->join('machine_data', 'machine_data.teacher_student_id=users.user_id AND machine_data.type="teacher"', 'left');
		$this->db->where('users.status', '1');
		if ($campus_id !== null && $campus_id !== '') {
			$this->db->where('users.campus_id', (int)$campus_id);
		}
		if ($q !== '') {
			$this->db->group_start();
			$this->db->like('users.first_name', $q);
			$this->db->or_like('users.last_name', $q);
			$this->db->or_like('users.cnic', $q);
			$this->db->or_like('users.email', $q);
			$this->db->or_like('users.username', $q);
			$this->db->group_end();
		}
		$this->db->group_by('users.user_id');
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

		$allowed_roles = array('Teacher', 'Principal', 'Accountant', 'Guard', 'Admin');
		if ($this->_is_admin()) {
			$role = isset($body['role']) && trim((string)$body['role']) !== '' ? trim((string)$body['role']) : 'Teacher';
			if (!in_array($role, $allowed_roles, true)) {
				$role = 'Teacher';
			}
		} elseif ($id === 0) {
			$role = 'Teacher';
		} else {
			$existing = $this->db->get_where('users', array('user_id' => $id))->row_array();
			if (!$existing) {
				$this->_json(array('success' => false, 'message' => 'Not found'), 404);
			}
			$role = isset($existing['role']) ? $existing['role'] : 'Teacher';
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
			'maritual_status' => isset($body['maritual_status']) ? $body['maritual_status'] : '',
			'blood_group' => isset($body['blood_group']) ? $body['blood_group'] : '',
			'date_of_birth' => isset($body['date_of_birth']) && $body['date_of_birth'] !== '' ? $body['date_of_birth'] : null,
			'salary' => isset($body['salary']) ? $body['salary'] : 0,
			'gross_salary' => isset($body['gross_salary']) ? $body['gross_salary'] : (isset($body['salary']) ? $body['salary'] : 0),
			'salary_adjustment' => isset($body['salary_adjustment']) ? $body['salary_adjustment'] : 0,
			'apply_statutory_rules' => isset($body['apply_statutory_rules']) ? (int)$body['apply_statutory_rules'] : 1,
			'designation' => isset($body['designation']) ? $body['designation'] : '',
			'city' => isset($body['city']) ? $body['city'] : '',
			'address' => isset($body['address']) ? $body['address'] : '',
			'emergency_no' => isset($body['emergency_no']) ? $body['emergency_no'] : '',
			'note' => isset($body['note']) ? $body['note'] : '',
			'username' => isset($body['username']) ? $body['username'] : '',
			'role' => $role,
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

			$campus_id = (int)$fields['campus_id'];
			if ($campus_id > 0) {
				$campus = $this->db->get_where('campuses', array('campus_id' => $campus_id))->row_array();
				$last = $this->db->query(
					'SELECT machine_id FROM machine_data WHERE campus_id=' . $campus_id . ' ORDER BY machine_id DESC LIMIT 1'
				)->row_array();
				$last_machine_id = 0;
				if ($last && !empty($last['machine_id']) && !empty($campus['campus_code'])) {
					$last_machine_id = (int)substr($last['machine_id'], 0, -strlen($campus['campus_code']));
				}
				$new_machine_id = ($last_machine_id + 1) . ($campus ? $campus['campus_code'] : '');
				$this->db->insert('machine_data', array(
					'teacher_student_id' => $id,
					'machine_id' => $new_machine_id,
					'type' => 'teacher',
					'campus_id' => $campus_id,
				));
			}
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

		if (isset($body['phones']) && is_array($body['phones'])) {
			$this->db->where('user_id', $id);
			$this->db->delete('users_phones');
			foreach ($body['phones'] as $phone) {
				$phone = trim((string)$phone);
				if ($phone === '') continue;
				$this->db->insert('users_phones', array('user_id' => $id, 'phone' => $phone));
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

	/** List uploaded documents for a staff member (legacy teachers/upload_documents). */
	public function staff_documents($user_id = 0)
	{
		$user_id = (int)$user_id;
		if (!$user_id) $this->_json(array('success' => false, 'message' => 'user_id required'), 422);
		$user = $this->db->get_where('users', array('user_id' => $user_id))->row_array();
		if (!$user) $this->_json(array('success' => false, 'message' => 'Staff not found'), 404);
		$rows = $this->db
			->where('teacher_id', $user_id)
			->order_by('id', 'DESC')
			->get('teacher_documents')
			->result_array();
		$this->_json(array(
			'success' => true,
			'staff' => array(
				'user_id' => (int)$user['user_id'],
				'first_name' => $user['first_name'],
				'last_name' => $user['last_name'],
			),
			'data' => $rows,
		));
	}

	/** Upload staff document (multipart: type, teacher_document). */
	public function staff_document_upload($user_id = 0)
	{
		$user_id = (int)$user_id;
		if (!$user_id) $this->_json(array('success' => false, 'message' => 'user_id required'), 422);
		$user = $this->db->get_where('users', array('user_id' => $user_id))->row_array();
		if (!$user) $this->_json(array('success' => false, 'message' => 'Staff not found'), 404);

		$type = isset($_POST['type']) ? trim((string)$_POST['type']) : '';
		if ($type === '') $this->_json(array('success' => false, 'message' => 'Document type required'), 422);

		if (empty($_FILES['teacher_document']['name']) || !is_uploaded_file($_FILES['teacher_document']['tmp_name'])) {
			$this->_json(array('success' => false, 'message' => 'File required'), 422);
		}

		$ext = strtolower(pathinfo($_FILES['teacher_document']['name'], PATHINFO_EXTENSION));
		$allowed = array('gif', 'jpg', 'jpeg', 'png', 'pdf', 'webp');
		if ($ext !== '' && !in_array($ext, $allowed, true)) {
			$this->_json(array('success' => false, 'message' => 'Invalid file type'), 422);
		}
		if ($_FILES['teacher_document']['size'] > 8 * 1024 * 1024) {
			$this->_json(array('success' => false, 'message' => 'File too large (max 8MB)'), 422);
		}

		$dir = FCPATH . 'uploads/';
		if (!is_dir($dir)) {
			@mkdir($dir, 0755, true);
		}
		$filename = uniqid('staff_doc_', true) . ($ext !== '' ? '.' . $ext : '');
		if (!move_uploaded_file($_FILES['teacher_document']['tmp_name'], $dir . $filename)) {
			$this->_json(array('success' => false, 'message' => 'Upload failed'), 500);
		}

		$this->db->insert('teacher_documents', array(
			'teacher_id' => $user_id,
			'image' => $filename,
			'type' => $type,
		));
		$id = (int)$this->db->insert_id();
		$this->_json(array('success' => true, 'id' => $id, 'image' => $filename));
	}

	/** Delete a staff document by id. */
	public function staff_document_delete($user_id = 0, $doc_id = 0)
	{
		$user_id = (int)$user_id;
		$doc_id = (int)$doc_id;
		if (!$user_id || !$doc_id) $this->_json(array('success' => false, 'message' => 'user_id and doc_id required'), 422);
		$row = $this->db->get_where('teacher_documents', array('id' => $doc_id, 'teacher_id' => $user_id))->row_array();
		if (!$row) $this->_json(array('success' => false, 'message' => 'Document not found'), 404);
		$this->db->where('id', $doc_id);
		$this->db->delete('teacher_documents');
		$this->_json(array('success' => true));
	}

	/** Single-staff attendance report (legacy teachers/check_attendence). */
	public function staff_attendance($user_id = 0)
	{
		$user_id = (int)$user_id;
		if (!$user_id) $this->_json(array('success' => false, 'message' => 'user_id required'), 422);

		$user = $this->db->get_where('users', array('user_id' => $user_id))->row_array();
		if (!$user) $this->_json(array('success' => false, 'message' => 'Staff not found'), 404);

		$machine = $this->db
			->get_where('machine_data', array('teacher_student_id' => $user_id, 'type' => 'teacher'))
			->row_array();
		if (!$machine) {
			$this->_json(array('success' => false, 'message' => 'Kindly set machine id of this user.'), 422);
		}

		$from = $this->input->get('from') ? trim((string)$this->input->get('from')) : date('Y-m-d');
		$to = $this->input->get('to') ? trim((string)$this->input->get('to')) : date('Y-m-d');
		$dates = $this->_date_range($from, $to);
		$machineId = (int)$machine['machine_id'];

		$rows = array();
		foreach ($dates as $date) {
			$holiday = $this->db->get_where('holidays', array('date' => $date))->result_array();
			if (count($holiday) > 0) {
				$rows[] = array(
					'date' => $date,
					'is_holiday' => true,
					'checkin' => null,
					'checkout' => null,
				);
				continue;
			}

			$checkinRow = $this->db
				->where('machine_user_id', $machineId)
				->where('time >=', $date . ' 00:00:00')
				->where('time <', $date . ' 23:59:59')
				->order_by('time', 'ASC')
				->limit(1)
				->get('attendence')
				->row_array();

			$checkoutRow = $this->db
				->where('machine_user_id', $machineId)
				->where('time >=', $date . ' 00:00:00')
				->where('time <', $date . ' 23:59:59.999')
				->order_by('time', 'DESC')
				->limit(1)
				->get('attendence')
				->row_array();

			$rows[] = array(
				'date' => $date,
				'is_holiday' => false,
				'checkin' => $checkinRow ? date('h:i:s A', strtotime($checkinRow['time'])) : 'Absent',
				'checkout' => $checkoutRow ? date('h:i:s A', strtotime($checkoutRow['time'])) : 'Absent',
			);
		}

		$this->_json(array(
			'success' => true,
			'from' => $from,
			'to' => $to,
			'machine_id' => $machineId,
			'staff' => array(
				'user_id' => (int)$user['user_id'],
				'first_name' => $user['first_name'],
				'last_name' => $user['last_name'],
			),
			'data' => $rows,
		));
	}

	/** Save or delete per-day timings for a staff shift (legacy save_staff_timing / delete_staff_timing). */
	public function staff_shift_timings($shift_id = 0)
	{
		$shift_id = (int)$shift_id;
		if (!$shift_id) $this->_json(array('success' => false, 'message' => 'shift_id required'), 422);

		$shift = $this->db->get_where('staff_shifts', array('staff_shift_id' => $shift_id))->row_array();
		if (!$shift) $this->_json(array('success' => false, 'message' => 'Shift not found'), 404);

		$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

		if ($method === 'DELETE') {
			$this->db->where('staff_shift_id', $shift_id);
			$this->db->delete('staff_timing');
			$this->_json(array('success' => true));
		}

		if ($method === 'POST') {
			$body = $this->_body();
			$timings = isset($body['timings']) && is_array($body['timings']) ? $body['timings'] : array();
			foreach ($timings as $t) {
				$day = isset($t['day']) ? trim((string)$t['day']) : '';
				if ($day === '') continue;

				$payload = array(
					'day' => $day,
					'checkin_timing' => isset($t['checkin_timing']) ? $t['checkin_timing'] : '00:00:00',
					'checkout_timing' => isset($t['checkout_timing']) ? $t['checkout_timing'] : '00:00:00',
					'half_day_on' => isset($t['half_day_on']) ? $t['half_day_on'] : '00:00:00',
					'full_day_on' => isset($t['full_day_on']) ? $t['full_day_on'] : '00:00:00',
					'staff_shift_id' => $shift_id,
					'staff_id' => 0,
				);

				$existing = $this->db
					->where('staff_shift_id', $shift_id)
					->where('day', $day)
					->get('staff_timing')
					->row_array();

				if ($existing) {
					$this->db->where('staff_shift_id', $shift_id);
					$this->db->where('day', $day);
					$this->db->update('staff_timing', $payload);
				} else {
					$this->db->insert('staff_timing', $payload);
				}
			}
			$this->_json(array('success' => true));
		}

		$this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
	}

	private function _date_range($from, $to)
	{
		$out = array();
		$iFrom = mktime(1, 0, 0, (int)substr($from, 5, 2), (int)substr($from, 8, 2), (int)substr($from, 0, 4));
		$iTo = mktime(1, 0, 0, (int)substr($to, 5, 2), (int)substr($to, 8, 2), (int)substr($to, 0, 4));
		if ($iTo >= $iFrom) {
			$out[] = date('Y-m-d', $iFrom);
			while ($iFrom < $iTo) {
				$iFrom += 86400;
				$out[] = date('Y-m-d', $iFrom);
			}
		}
		return $out;
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

	/** Flat expense categories for statutory rule/slab pickers. */
	public function expense_categories_lookup()
	{
		if (!$this->db->table_exists('expense_category')) {
			$this->_json(array('success' => true, 'data' => array()));
		}
		$this->db->select('expense_category_id, name, sub_of', false);
		$this->db->from('expense_category');
		if ($this->db->field_exists('status', 'expense_category')) {
			$this->db->group_start();
			$this->db->where('status', 'active');
			$this->db->or_where('status', 1);
			$this->db->or_where('status IS NULL', null, false);
			$this->db->or_where('status', '');
			$this->db->group_end();
		}
		$this->db->order_by('name', 'ASC');
		$rows = $this->db->get()->result_array();
		foreach ($rows as &$r) {
			$r['expense_category_id'] = (int)$r['expense_category_id'];
			$sub = isset($r['sub_of']) ? $r['sub_of'] : null;
			$r['sub_of'] = ($sub === null || $sub === '' || (int)$sub === 0) ? null : (int)$sub;
		}
		unset($r);
		$this->_json(array('success' => true, 'data' => $rows));
	}

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
	// Salary (ports Salary.php — generate, reports, disburse)
	// ------------------------------------------------------------------

	private function _payroll_service()
	{
		if (!isset($this->payroll_service)) {
			$this->load->library('Payroll_service');
			$this->payroll_service = $this->payroll_service;
		}
		return $this->payroll_service;
	}

	private function _actor_from_session()
	{
		$uid = isset($this->current_user['user_id']) ? (int) $this->current_user['user_id'] : null;
		$name = '';
		if ($uid) {
			$row = $this->db->select('first_name, last_name')->get_where('users', array('user_id' => $uid))->row_array();
			if ($row) {
				$name = trim($row['first_name'] . ' ' . $row['last_name']);
			}
		}
		return array($uid, $name);
	}

	public function salary_list()
	{
		$campus_id = $this->input->get('campus_id');
		$month = $this->input->get('month');
		$year = $this->input->get('year');
		if ($month === null || $month === '') $month = date('M', strtotime('-1 months'));
		if ($year === null || $year === '') $year = date('Y', strtotime('-1 months'));

		$this->db->select(
			'users.user_id, users.first_name, users.last_name, users.mobile, users.salary, users.gross_salary, users.campus_id, campuses.campus_name,'
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
			$r['count'] = $r['earned_salary'];
		}
		unset($r);

		$this->_json(array('success' => true, 'month' => $month, 'year' => $year, 'data' => $rows));
	}

	public function generate_salary($user_id = 0, $campus_id = 0, $month = '', $year = '')
	{
		$user_id = (int) $user_id;
		$campus_id = (int) $campus_id;
		$month = $month !== '' ? $month : $this->input->get('month');
		$year = $year !== '' ? $year : $this->input->get('year');
		if (!$user_id || !$campus_id || $month === '' || $year === '') {
			$this->_json(array('success' => false, 'message' => 'user_id, campus_id, month and year required'), 422);
		}
		try {
			$data = $this->_payroll_service()->build_generate_salary_payload($user_id, $campus_id, $month, $year);
			$this->_json(array('success' => true, 'data' => $data));
		} catch (Exception $e) {
			$this->_json(array('success' => false, 'message' => $e->getMessage()), 500);
		}
	}

	public function salary_view($user_id = 0, $month = '', $year = '')
	{
		$user_id = (int) $user_id;
		$month = $month !== '' ? $month : $this->input->get('month');
		$year = $year !== '' ? $year : $this->input->get('year');
		if (!$user_id || $month === '' || $year === '') {
			$this->_json(array('success' => false, 'message' => 'user_id, month and year required'), 422);
		}
		$data = $this->_payroll_service()->fetch_salary_slip_data($user_id, $month, $year);
		if (!$data) {
			$this->_json(array('success' => false, 'message' => 'Payroll not found'), 404);
		}
		$this->_json(array('success' => true, 'data' => $data));
	}

	public function store_payroll()
	{
		$body = $this->_body();
		list($uid, $name) = $this->_actor_from_session();
		$result = $this->_payroll_service()->storepayroll_from_body($body, $uid, $name);
		if (empty($result['success'])) {
			$this->_json(array('success' => false, 'message' => isset($result['message']) ? $result['message'] : 'Save failed'), 422);
		}
		$this->_json(array('success' => true, 'id' => isset($result['id']) ? $result['id'] : null, 'message' => $result['message']));
	}

	public function salary_report()
	{
		$campus_id = $this->input->get('campus_id');
		$to_date = $this->input->get('to_date');
		if ($to_date === null || $to_date === '') {
			$month = $this->input->get('month');
			$year = $this->input->get('year');
			if ($month !== null && $month !== '' && $year !== null && $year !== '') {
				$to_date = date('Y-m-t', strtotime($year . '-' . $month . '-01'));
			} else {
				$to_date = date('Y-m-d');
			}
		}
		$data = $this->_payroll_service()->fetch_salary_report_data($campus_id, $to_date, false);
		$this->_json(array_merge(array('success' => true), $data, array('data' => $data['salary'])));
	}

	public function disburse_salary_report()
	{
		$campus_id = $this->input->get('campus_id');
		$to_date = $this->input->get('to_date');
		if ($to_date === null || $to_date === '') {
			$month = $this->input->get('month');
			$year = $this->input->get('year');
			if ($month !== null && $month !== '' && $year !== null && $year !== '') {
				$to_date = date('Y-m-t', strtotime($year . '-' . $month . '-01'));
			} else {
				$to_date = date('Y-m-d');
			}
		}
		$data = $this->_payroll_service()->fetch_salary_report_data($campus_id, $to_date, true);
		$this->_json(array_merge(array('success' => true), $data, array('data' => $data['salary'])));
	}

	public function salary_disburse()
	{
		$body = $this->_body();
		list($uid, $name) = $this->_actor_from_session();
		$result = $this->_payroll_service()->insert_expense_from_body($body, $uid, $name);
		if (empty($result['success'])) {
			$this->_json(array('success' => false, 'message' => isset($result['message']) ? $result['message'] : 'Disburse failed'), 422);
		}
		$this->_json($result);
	}

	public function salary_remove_contributions()
	{
		$result = $this->_payroll_service()->remove_contributions_from_body($this->_body());
		if (empty($result['success'])) {
			$this->_json(array('success' => false, 'message' => isset($result['message']) ? $result['message'] : 'Failed'), 422);
		}
		$this->_json(array('success' => true));
	}

	public function salary_add_contribution_expense()
	{
		$body = $this->_body();
		list($uid, $name) = $this->_actor_from_session();
		$result = $this->_payroll_service()->add_contribution_expense_from_body($body, $_FILES, $uid, $name);
		if (empty($result['success'])) {
			$this->_json(array('success' => false, 'message' => isset($result['message']) ? $result['message'] : 'Failed'), 422);
		}
		$this->_json($result);
	}

	public function salary_delete_expense($exp_id = 0)
	{
		$exp_id = (int) $exp_id;
		if (!$exp_id) {
			$this->_json(array('success' => false, 'message' => 'expense id required'), 422);
		}
		$result = $this->_payroll_service()->delete_expense_from_body($exp_id);
		if (empty($result['success'])) {
			$this->_json(array('success' => false, 'message' => isset($result['message']) ? $result['message'] : 'Failed'), 422);
		}
		$this->_json($result);
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
