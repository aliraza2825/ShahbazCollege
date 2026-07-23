<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Students JSON API for React POS shell
 * Base: /index.php/studentsapi/{method}
 * Auth: X-Pos-Token
 *
 * Faithfully ports All Students report types from Students::all_students + views.
 */
class Studentsapi extends CI_Controller {

	private $current_user = null;
	private $access_row = null;

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
		$this->_access();
		if (!$this->_can_students()) {
			$this->_json(array('success' => false, 'message' => 'No students access'), 403);
		}
		$this->load->model('student');
		$this->load->model('council');
		$this->_ensure_student_schema();
	}

	private function _ensure_student_schema()
	{
		if (!$this->db->table_exists('students')) return;
		$cols = array(
			'student_occupation_id' => 'INT NULL DEFAULT NULL',
			'father_occupation_id' => 'INT NULL DEFAULT NULL',
			'mother_occupation_id' => 'INT NULL DEFAULT NULL',
			'mother_name' => 'VARCHAR(255) NULL DEFAULT NULL',
			'extra_added_fee' => 'DECIMAL(12,2) NOT NULL DEFAULT 0',
		);
		foreach ($cols as $col => $ddl) {
			if (!$this->db->field_exists($col, 'students')) {
				$this->db->query("ALTER TABLE `students` ADD `$col` $ddl");
			}
		}
		$this->db->query("CREATE TABLE IF NOT EXISTS occupations (
			occupation_id INT NOT NULL AUTO_INCREMENT,
			occupation_name VARCHAR(255) NOT NULL,
			sub_of INT NOT NULL DEFAULT 0,
			has_sub TINYINT(1) NOT NULL DEFAULT 0,
			PRIMARY KEY (occupation_id),
			KEY sub_of (sub_of)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	}

	private function _actor_name()
	{
		$name = trim($this->current_user['first_name'] . ' ' . $this->current_user['last_name']);
		return $name !== '' ? $name : 'POS';
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
		// Printable HTML opens in a new tab — allow query token for GET print endpoints only
		if ($token === '' || $token === null) {
			$qt = $this->input->get('pos_token');
			if ($qt) $token = $qt;
		}
		if (!$token) return null;
		$row = $this->db->get_where('pos_api_tokens', array('token' => $token))->row_array();
		if (!$row || strtotime($row['expires_at']) < time()) return null;
		return $this->db->get_where('users', array('user_id' => $row['user_id'], 'status' => '1'))->row_array();
	}

	private function _access()
	{
		if ($this->access_row !== null) return $this->access_row;
		$uid = (int)$this->current_user['user_id'];
		$row = $this->db->get_where('access', array('user_id' => $uid))->row_array();
		$this->access_row = $row ? $row : array();
		return $this->access_row;
	}

	private function _is_admin()
	{
		return isset($this->current_user['role']) && $this->current_user['role'] === 'Admin';
	}

	private function _can_students()
	{
		if ($this->_is_admin()) return true;
		$row = $this->_access();
		return !empty($row['student_sidebar'])
			|| !empty($row['student_all'])
			|| !empty($row['student_add']);
	}

	private function _perm($key)
	{
		if ($this->_is_admin()) return true;
		$row = $this->_access();
		return !empty($row[$key]);
	}

	private function _class_ids()
	{
		if ($this->_is_admin()) return null;
		$row = $this->_access();
		if (empty($row['class_ids'])) return array();
		$ids = array();
		foreach (explode(',', $row['class_ids']) as $id) {
			$id = trim($id);
			if ($id !== '') $ids[] = $id;
		}
		return $ids;
	}

	private function _asset_base()
	{
		return rtrim(base_url(), '/');
	}

	private function _legacy_base()
	{
		return rtrim(site_url(), '/');
	}

	private function _doc_url($row)
	{
		if (!$row) return null;
		if (!empty($row['online_image'])) return $row['online_image'];
		if (!empty($row['image'])) return $this->_asset_base() . '/uploads/' . rawurlencode($row['image']);
		return null;
	}

	private function _permissions()
	{
		return array(
			'is_admin' => $this->_is_admin(),
			'student_sidebar' => $this->_perm('student_sidebar') || $this->_is_admin(),
			'student_add' => $this->_perm('student_add'),
			'student_all' => $this->_perm('student_all'),
			'student_edit' => $this->_perm('student_edit'),
			'student_upload_documents' => $this->_perm('student_upload_documents'),
			'student_payments' => $this->_perm('student_payments'),
			'student_payment_reset' => $this->_perm('student_payment_reset'),
			'student_payment_edit' => $this->_perm('student_payment_edit'),
			'can_student_struckof' => $this->_perm('can_student_struckof'),
			'student_issue_refund' => $this->_perm('student_issue_refund'),
			'council_list_report' => $this->_perm('council_list_report'),
			'extra_fee_access' => $this->_perm('extra_fee_access'),
			'fee_by_bank' => $this->_perm('fee_by_bank'),
			'fee_by_cash' => $this->_perm('fee_by_cash'),
			'fee_by_paypro' => $this->_perm('fee_by_paypro'),
			'fine_remove' => $this->_perm('fine_remove') || $this->_perm('remove_fine'),
			'remove_fine' => $this->_perm('remove_fine') || $this->_perm('fine_remove'),
			'change_exam_no_in_payments' => $this->_perm('change_exam_no_in_payments'),
		);
	}

	private function _require_student_payments()
	{
		if (!$this->_perm('student_payments') && !$this->_is_admin()) {
			$this->_json(array('success' => false, 'message' => 'No payments permission'), 403);
		}
	}

	private function _assert_pay_through($pay_through)
	{
		$map = array(
			'bank' => 'fee_by_bank',
			'college' => 'fee_by_cash',
			'pay_pro' => 'fee_by_paypro',
		);
		$key = isset($map[$pay_through]) ? $map[$pay_through] : null;
		if (!$key || !$this->_perm($key)) {
			$this->_json(array('success' => false, 'message' => 'No permission for pay-through: ' . $pay_through), 403);
		}
	}

	public function meta()
	{
		$campuses = $this->db->where('status', 1)->order_by('campus_name', 'ASC')->get('campuses')->result_array();
		$this->_json(array(
			'success' => true,
			'data' => array(
				'permissions' => $this->_permissions(),
				'campuses' => $campuses,
				'asset_base' => $this->_asset_base(),
				'legacy_base' => $this->_legacy_base(),
				'report_types' => array(
					array('id' => 'active', 'label' => 'Active Students'),
					array('id' => 'pass', 'label' => 'Passed Students'),
					array('id' => 'both', 'label' => 'Both'),
					array('id' => 'blacklist', 'label' => 'Blacklist Students'),
					array('id' => 'councel_list', 'label' => 'Councel List', 'needs_permission' => 'council_list_report'),
					array('id' => 'archived', 'label' => 'Archive'),
					array('id' => 'studentdetail', 'label' => 'Student Full Detail Report'),
					array('id' => 'shift', 'label' => 'Shift Wise Report'),
					array('id' => 'using_app', 'label' => 'Using Mobile App'),
					array('id' => 'attendance', 'label' => 'Attendance'),
				),
				'search_types' => array(
					array('id' => 'classwise', 'label' => 'Class Wise'),
					array('id' => 'councilwise', 'label' => 'Council Exam No. Wise (Student Submit fee in college)'),
					array('id' => 'councilwise_roll_no', 'label' => 'According to Roll no of Council(Fee/Papers/Information)'),
				),
			),
		));
	}

	public function courses()
	{
		$campus_id = (int)$this->input->get('campus_id');
		$cnic = trim((string)$this->input->get('cnic'));
		$exclude = array();
		if ($cnic !== '') {
			$enrolled = $this->db->query(
				"SELECT classes.course_id FROM students
				 JOIN classes ON classes.class_id = students.class_id
				 WHERE students.cnic = ?",
				array($cnic)
			)->result_array();
			foreach ($enrolled as $e) {
				if (!empty($e['course_id'])) $exclude[] = (int)$e['course_id'];
			}
		}
		$this->db->from('courses');
		if ($campus_id > 0) {
			$this->db->like('campus_ids', (string)$campus_id);
		}
		if (count($exclude)) {
			$this->db->where_not_in('course_id', $exclude);
		}
		$this->db->order_by('course_name', 'ASC');
		$rows = $this->db->get()->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function classes()
	{
		$campus_id = (int)$this->input->get('campus_id');
		$course_id = (int)$this->input->get('course_id');
		$this->db->from('classes');
		$this->db->where('status', 1);
		if ($campus_id > 0) $this->db->where('campus_id', $campus_id);
		if ($course_id > 0) $this->db->where('course_id', $course_id);
		$class_ids = $this->_class_ids();
		if (is_array($class_ids)) {
			if (!count($class_ids)) {
				$this->_json(array('success' => true, 'data' => array()));
			}
			$this->db->where_in('class_id', $class_ids);
		}
		$this->db->order_by('name', 'ASC');
		$rows = $this->db->get()->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	/**
	 * Main All Students report dispatcher (paginated + batched).
	 * Body: campus_id, course_id, class_id, type, search_type, council_exam_no, class,
	 *       page (1-based), page_size (default 25, max 100), q (optional search)
	 */
	public function all_students()
	{
		$body = $this->_body();
		$type = isset($body['type']) ? trim($body['type']) : 'active';
		$search_type = isset($body['search_type']) ? trim($body['search_type']) : 'classwise';
		$campus_id = isset($body['campus_id']) ? (int)$body['campus_id'] : 0;
		$course_id = isset($body['course_id']) ? (int)$body['course_id'] : 0;
		$class_id = isset($body['class_id']) ? (int)$body['class_id'] : 0;
		$council_exam_no = isset($body['council_exam_no']) ? trim($body['council_exam_no']) : '';
		$year_class = isset($body['class']) ? trim($body['class']) : '1';
		$q = isset($body['q']) ? trim($body['q']) : '';
		$page = max(1, isset($body['page']) ? (int)$body['page'] : 1);
		$page_size = isset($body['page_size']) ? (int)$body['page_size'] : 25;
		if ($page_size < 10) $page_size = 10;
		if ($page_size > 100) $page_size = 100;
		$offset = ($page - 1) * $page_size;

		$allowed = array(
			'active', 'pass', 'both', 'blacklist', 'councel_list', 'archived',
			'studentdetail', 'shift', 'using_app', 'attendance',
		);
		if (!in_array($type, $allowed, true)) {
			$this->_json(array('success' => false, 'message' => 'Invalid type'), 422);
		}
		if ($type === 'councel_list' && !$this->_perm('council_list_report')) {
			$this->_json(array('success' => false, 'message' => 'No council list permission'), 403);
		}

		if ($type === 'attendance') {
			if (!$class_id) $this->_json(array('success' => false, 'message' => 'class_id required for attendance'), 422);
			$this->_json(array(
				'success' => true,
				'type' => 'attendance',
				'redirect' => $this->_legacy_base() . '/students/check_attendance/' . $class_id,
				'spa_path' => '/students/attendance/' . $class_id,
			));
		}

		$pager = array(
			'page' => $page,
			'page_size' => $page_size,
			'total' => 0,
			'total_pages' => 0,
		);

		if ($search_type === 'councilwise_roll_no') {
			$pack = $this->_report_councilwise_roll_no($campus_id, $council_exam_no, $year_class, $offset, $page_size);
			$pager['total'] = $pack['total'];
			$pager['total_pages'] = $page_size > 0 ? (int)ceil($pack['total'] / $page_size) : 1;
			$this->_json($this->_report_response($type, $search_type, $pack['rows'], $body, array('pagination' => $pager)));
		}

		if ($type === 'archived') {
			$pack = $this->_fetch_archived($campus_id, $class_id, $q, $offset, $page_size);
			$rows = $this->_enrich_archived($pack['rows']);
			$pager['total'] = $pack['total'];
			$pager['total_pages'] = (int)ceil($pack['total'] / $page_size);
			$this->_json($this->_report_response($type, $search_type, $rows, $body, array('pagination' => $pager)));
		}

		if ($type === 'using_app') {
			$pack = $this->_fetch_active_basic($campus_id, $class_id, $q, $offset, $page_size);
			$rows = $this->_enrich_using_app($pack['rows']);
			$pager['total'] = $pack['total'];
			$pager['total_pages'] = (int)ceil($pack['total'] / $page_size);
			$this->_json($this->_report_response($type, $search_type, $rows, $body, array('pagination' => $pager)));
		}

		if ($type === 'councel_list') {
			if (!$class_id) $this->_json(array('success' => false, 'message' => 'class_id required for councel list'), 422);
			$pack = $this->_fetch_council_list($class_id, $q, $offset, $page_size);
			$rows = $this->_enrich_council_list($pack['rows']);
			$pager['total'] = $pack['total'];
			$pager['total_pages'] = (int)ceil($pack['total'] / $page_size);
			$this->_json($this->_report_response($type, $search_type, $rows, $body, array(
				'class_id' => $class_id,
				'campus_id' => $campus_id,
				'pagination' => $pager,
			)));
		}

		// Active / pass / both / blacklist / shift / studentdetail
		$fetch_type = $type;
		if ($type === 'both') $fetch_type = 'active';

		$extra = array();
		if ($type === 'shift') {
			$extra['shift_studytype_counts'] = $this->_shift_counts($campus_id, $course_id, $class_id, $search_type, $council_exam_no);
		}

		if ($type === 'studentdetail') {
			// Full detail is heavy — keep page small and only enrich the page
			$page_size = min($page_size, 50);
			$offset = ($page - 1) * $page_size;
			$pack = $this->_fetch_students_paged($campus_id, $course_id, $class_id, $search_type, $council_exam_no, 'active', $q, $offset, $page_size);
			$range = $this->_detail_date_range($class_id);
			$months = $this->_month_list($range['startdate'], $range['enddate']);
			// Cap months to avoid huge matrices
			if (count($months) > 36) $months = array_slice($months, -36);
			$detail = $this->_enrich_studentdetail($pack['rows'], $months);
			$pager['total'] = $pack['total'];
			$pager['page_size'] = $page_size;
			$pager['total_pages'] = (int)ceil($pack['total'] / $page_size);
			$this->_json($this->_report_response($type, $search_type, $detail['rows'], $body, array(
				'startdate' => $range['startdate'],
				'enddate' => $range['enddate'],
				'months' => $months,
				'footer_must' => $detail['footer_must'],
				'footer_paid' => $detail['footer_paid'],
				'pagination' => $pager,
			)));
		}

		$pack = $this->_fetch_students_paged(
			$campus_id, $course_id, $class_id, $search_type, $council_exam_no,
			$fetch_type === 'blacklist' ? 'blacklist' : ($fetch_type === 'pass' ? 'pass' : ($fetch_type === 'shift' ? 'shift' : 'active')),
			$q, $offset, $page_size
		);

		if ($type === 'blacklist') {
			$rows = $this->_enrich_blacklist_page($pack['rows']);
		} elseif ($type === 'shift') {
			$rows = $this->_enrich_shift($pack['rows']);
		} else {
			$rows = $this->_enrich_default($pack['rows']);
		}

		$pager['total'] = $pack['total'];
		$pager['total_pages'] = max(1, (int)ceil($pack['total'] / $page_size));
		$extra['pagination'] = $pager;
		$this->_json($this->_report_response($type, $search_type, $rows, $body, $extra));
	}

	private function _report_response($type, $search_type, $rows, $body, $extra = array())
	{
		$total = isset($extra['pagination']['total']) ? (int)$extra['pagination']['total'] : count($rows);
		return array_merge(array(
			'success' => true,
			'type' => $type,
			'search_type' => $search_type,
			'filters' => array(
				'campus_id' => isset($body['campus_id']) ? (int)$body['campus_id'] : 0,
				'course_id' => isset($body['course_id']) ? (int)$body['course_id'] : 0,
				'class_id' => isset($body['class_id']) ? (int)$body['class_id'] : 0,
				'council_exam_no' => isset($body['council_exam_no']) ? $body['council_exam_no'] : '',
			),
			'data' => $rows,
			'count' => $total,
			'permissions' => $this->_permissions(),
			'legacy_base' => $this->_legacy_base(),
			'asset_base' => $this->_asset_base(),
			'actions' => $this->_action_catalog(),
		), $extra);
	}

	private function _action_catalog()
	{
		$b = $this->_legacy_base();
		return array(
			'attendance' => '/students/attendance/student/{id}',
			'sms' => '/students/sms/{id}',
			'edit' => '/students/edit/{id}',
			'documents' => '/students/documents/{id}',
			'payments' => '/students/payments/{id}',
			'reset_plan' => 'api:reset_plan',
			'struckof' => '/students/struckof/{id}',
			'freeze' => '/students/freeze/{id}',
			'all_document' => '/students/all-docs/{id}',
			'purchased' => '/students/purchased/{id}',
			'struck_letters' => 'api:struck_letters',
			'restore' => 'api:restore',
			'revive' => '/students/revive/{id}',
			// File/print helpers still served by CI document controllers
			'council_print' => $b . '/council_list/get_print_of_concel_list',
			'council_print_new' => $b . '/council_list/get_print_of_new_concel_list',
			'delete_roll_no' => $b . '/punjab_council_roll_number/delete_roll_no',
		);
	}

	// ─── Fetchers (paginated, no cartesian payment joins) ───

	private function _apply_student_scope($campus_id, $course_id, $class_id)
	{
		if ($campus_id > 0) $this->db->where('classes.campus_id', $campus_id);
		if ($course_id > 0) $this->db->where('students.course_id', $course_id);
		if ($class_id > 0) $this->db->where('students.class_id', $class_id);
		$class_ids = $this->_class_ids();
		if (is_array($class_ids)) {
			if (!count($class_ids)) return false;
			$this->db->where_in('students.class_id', $class_ids);
		}
		return true;
	}

	private function _apply_search_q($q)
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

	private function _apply_type_sql($type)
	{
		if ($type === 'pass') {
			$this->db->where(
				"EXISTS (
					SELECT 1 FROM punjab_council_roll_number p
					WHERE p.cnic = students.cnic AND p.class = '2'
					AND p.result_remarks IN ('Pass','Pass*')
				)",
				null,
				false
			);
		} elseif ($type === 'blacklist') {
			$one_month = date('Y-m-d', strtotime('-1 month'));
			$this->db->where(
				"EXISTS (
					SELECT 1 FROM payments p
					WHERE p.student_id = students.student_id
					AND p.paid = 0 AND p.dead_line < " . $this->db->escape($one_month) . "
				)",
				null,
				false
			);
		} elseif ($type === 'shift') {
			// Hide if any non-2nd-year roll with remarks != Pass
			$this->db->where(
				"NOT EXISTS (
					SELECT 1 FROM punjab_council_roll_number p
					WHERE p.cnic = students.cnic
					AND p.class != '2'
					AND (p.result_remarks IS NULL OR p.result_remarks != 'Pass')
				)",
				null,
				false
			);
		}
	}

	/**
	 * @return array{rows: array, total: int}
	 */
	private function _fetch_students_paged($campus_id, $course_id, $class_id, $search_type, $council_exam_no, $type, $q, $offset, $limit)
	{
		$class_ids = $this->_class_ids();
		if (is_array($class_ids) && !count($class_ids)) {
			return array('rows' => array(), 'total' => 0);
		}

		// COUNT
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'left');
		if ($search_type === 'councilwise') {
			$this->db->join('payments', 'payments.custom_student_id=students.student_id', 'inner');
			$this->db->like('payments.payment_comment', 'This fee for next exam # ' . $council_exam_no, 'both');
			$this->db->where(array('students.status' => '1', 'payments.paid' => '1'));
			$this->db->group_by('students.student_id');
		} else {
			$this->db->where('students.status', '1');
		}
		if (!$this->_apply_student_scope($campus_id, $course_id, $class_id)) {
			return array('rows' => array(), 'total' => 0);
		}
		$this->_apply_type_sql($type);
		$this->_apply_search_q($q);

		if ($search_type === 'councilwise') {
			$total = count($this->db->select('students.student_id')->get()->result_array());
		} else {
			$total = (int)$this->db->count_all_results();
		}

		if ($total < 1) return array('rows' => array(), 'total' => 0);

		// PAGE ROWS — no payments join (avoids cartesian blow-up)
		$this->db->select('students.*, classes.name as class_name, classes.session as session, campuses.campus_name, courses.course_name, machine_data.machine_id', false);
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'left');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
		$this->db->join('courses', 'courses.course_id=students.course_id', 'left');
		$this->db->join('machine_data', 'machine_data.teacher_student_id=students.student_id AND machine_data.type="student"', 'left');
		if ($search_type === 'councilwise') {
			$this->db->join('payments', 'payments.custom_student_id=students.student_id', 'inner');
			$this->db->like('payments.payment_comment', 'This fee for next exam # ' . $council_exam_no, 'both');
			$this->db->where(array('students.status' => '1', 'payments.paid' => '1'));
			$this->db->group_by('students.student_id');
		} else {
			$this->db->where('students.status', '1');
		}
		$this->_apply_student_scope($campus_id, $course_id, $class_id);
		$this->_apply_type_sql($type);
		$this->_apply_search_q($q);
		$this->db->order_by('CAST(students.roll_no AS UNSIGNED)', 'ASC', false);
		$this->db->order_by('students.roll_no', 'ASC');
		$this->db->limit($limit, $offset);
		$rows = $this->db->get()->result_array();

		return array('rows' => $rows, 'total' => $total);
	}

	private function _fetch_archived($campus_id, $class_id, $q = '', $offset = 0, $limit = 25)
	{
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'left');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
		$this->db->where('students.status', '0');
		if ($class_id > 0) $this->db->where('students.class_id', $class_id);
		elseif ($campus_id > 0) $this->db->where('campuses.campus_id', $campus_id);
		$class_ids = $this->_class_ids();
		if (is_array($class_ids)) {
			if (!count($class_ids)) return array('rows' => array(), 'total' => 0);
			$this->db->where_in('students.class_id', $class_ids);
		}
		$this->_apply_search_q($q);
		$total = (int)$this->db->count_all_results();

		$this->db->select('students.*, classes.name as class_name, classes.admission_fee as freeze_fee, courses.course_name, campuses.campus_name', false);
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'left');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
		$this->db->join('courses', 'courses.course_id=students.course_id', 'left');
		$this->db->where('students.status', '0');
		if ($class_id > 0) $this->db->where('students.class_id', $class_id);
		elseif ($campus_id > 0) $this->db->where('campuses.campus_id', $campus_id);
		if (is_array($class_ids) && count($class_ids)) $this->db->where_in('students.class_id', $class_ids);
		$this->_apply_search_q($q);
		$this->db->order_by('students.roll_no', 'asc');
		$this->db->limit($limit, $offset);
		return array('rows' => $this->db->get()->result_array(), 'total' => $total);
	}

	private function _fetch_active_basic($campus_id, $class_id, $q = '', $offset = 0, $limit = 25)
	{
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'left');
		$this->db->join('campuses', 'campuses.campus_id = classes.campus_id', 'inner');
		$this->db->where('students.status', '1');
		if ($class_id > 0) $this->db->where('students.class_id', $class_id);
		elseif ($campus_id > 0) $this->db->where('campuses.campus_id', $campus_id);
		$class_ids = $this->_class_ids();
		if (is_array($class_ids)) {
			if (!count($class_ids)) return array('rows' => array(), 'total' => 0);
			$this->db->where_in('students.class_id', $class_ids);
		}
		$this->_apply_search_q($q);
		$total = (int)$this->db->count_all_results();

		$this->db->select('students.*, classes.name as class_name, classes.admission_fee as freeze_fee, courses.course_name, campuses.campus_name', false);
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'left');
		$this->db->join('campuses', 'campuses.campus_id = classes.campus_id', 'inner');
		$this->db->join('courses', 'courses.course_id = students.course_id', 'left');
		$this->db->where('students.status', '1');
		if ($class_id > 0) $this->db->where('students.class_id', $class_id);
		elseif ($campus_id > 0) $this->db->where('campuses.campus_id', $campus_id);
		if (is_array($class_ids) && count($class_ids)) $this->db->where_in('students.class_id', $class_ids);
		$this->_apply_search_q($q);
		$this->db->order_by('students.roll_no', 'asc');
		$this->db->limit($limit, $offset);
		return array('rows' => $this->db->get()->result_array(), 'total' => $total);
	}

	private function _fetch_council_list($class_id, $q, $offset, $limit)
	{
		$this->db->from('students');
		$this->db->where(array('class_id' => $class_id, 'status' => 1));
		if ($q !== '') {
			$this->db->group_start();
			$this->db->like('first_name', $q);
			$this->db->or_like('last_name', $q);
			$this->db->or_like('cnic', $q);
			$this->db->or_like('roll_no', $q);
			$this->db->or_like('mobile', $q);
			$this->db->group_end();
		}
		$total = (int)$this->db->count_all_results();

		$sql = 'SELECT student_id, roll_no, cnic, gender, first_name, last_name, father_name,
			CONCAT(first_name," ", last_name, " S/O ", father_name) as name, address, mobile, emergency_no, board
			FROM students WHERE class_id=? AND status=1';
		$params = array($class_id);
		if ($q !== '') {
			$sql .= ' AND (first_name LIKE ? OR last_name LIKE ? OR cnic LIKE ? OR roll_no LIKE ? OR mobile LIKE ?)';
			$like = '%' . $q . '%';
			$params = array_merge($params, array($like, $like, $like, $like, $like));
		}
		$sql .= ' ORDER BY CAST(roll_no as SIGNED INTEGER) ASC LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;
		$rows = $this->db->query($sql, $params)->result_array();
		return array('rows' => $rows, 'total' => $total);
	}

	private function _shift_counts($campus_id, $course_id, $class_id, $search_type, $council_exam_no)
	{
		// Light columns only — no enrichment, capped for safety
		$this->db->select('students.student_id, students.shift, students.study_type, students.cnic');
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'left');
		if ($search_type === 'councilwise') {
			$this->db->join('payments', 'payments.custom_student_id=students.student_id', 'inner');
			$this->db->like('payments.payment_comment', 'This fee for next exam # ' . $council_exam_no, 'both');
			$this->db->where(array('students.status' => '1', 'payments.paid' => '1'));
			$this->db->group_by('students.student_id');
		} else {
			$this->db->where('students.status', '1');
		}
		if (!$this->_apply_student_scope($campus_id, $course_id, $class_id)) return array();
		$this->_apply_type_sql('shift');
		$rows = $this->db->get()->result_array();

		$shift_names = array();
		$study_names = array();
		if ($this->db->table_exists('shifts')) {
			foreach ($this->db->get('shifts')->result_array() as $sh) $shift_names[$sh['id']] = $sh['name'];
		}
		if ($this->db->table_exists('study_type')) {
			foreach ($this->db->get('study_type')->result_array() as $st) $study_names[$st['id']] = $st['name'];
		}
		$counts = array();
		foreach ($rows as $s) {
			$shift_label = isset($shift_names[$s['shift']]) ? $shift_names[$s['shift']] : (string)$s['shift'];
			$study_label = isset($study_names[$s['study_type']]) ? $study_names[$s['study_type']] : (string)$s['study_type'];
			$combo = trim($shift_label . ' ' . $study_label);
			if ($combo === '') $combo = '(blank)';
			if (!isset($counts[$combo])) $counts[$combo] = 0;
			$counts[$combo]++;
		}
		return $counts;
	}

	private function _report_councilwise_roll_no($campus_id, $council_exam_no, $year_class, $offset = 0, $limit = 25)
	{
		$this->db->from('punjab_council_roll_number');
		$this->db->join('students', 'students.cnic=punjab_council_roll_number.cnic', 'left');
		$this->db->join('classes', 'students.class_id=classes.class_id', 'left');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
		$this->db->where('punjab_council_roll_number.council_exam_no', $council_exam_no);
		$this->db->where('punjab_council_roll_number.class', $year_class);
		if ($campus_id > 0) $this->db->where('campuses.campus_id', $campus_id);
		$total = (int)$this->db->count_all_results();

		$this->db->select('campuses.campus_id, campuses.campus_name, punjab_council_roll_number.*, students.class_id, classes.name as class_name, classes.session as session, students.mobile, students.emergency_no, contractors.name as contractor_name, students.*, courses.*', false);
		$this->db->from('punjab_council_roll_number');
		$this->db->join('students', 'students.cnic=punjab_council_roll_number.cnic', 'left');
		$this->db->join('classes', 'students.class_id=classes.class_id', 'left');
		$this->db->join('courses', 'courses.course_id=classes.course_id', 'left');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
		$this->db->join('contracts', 'students.contract_id=contracts.contract_id', 'left');
		$this->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'left');
		$this->db->where('punjab_council_roll_number.council_exam_no', $council_exam_no);
		$this->db->where('punjab_council_roll_number.class', $year_class);
		if ($campus_id > 0) $this->db->where('campuses.campus_id', $campus_id);
		$this->db->limit($limit, $offset);
		$rows = $this->db->get()->result_array();

		$ids = array();
		foreach ($rows as $r) {
			if (!empty($r['student_id'])) $ids[] = (int)$r['student_id'];
		}
		$pay_by = $this->_payment_stats_by_student($ids);
		$unpaid_by = $this->_unpaid_rows_by_student($ids);

		foreach ($rows as &$r) {
			$sid = (int)(isset($r['student_id']) ? $r['student_id'] : 0);
			$r['unpaid_payments'] = isset($unpaid_by[$sid]) ? $unpaid_by[$sid] : array();
			$r['payable_current'] = isset($pay_by[$sid]) ? (float)$pay_by[$sid]['unpaid_till_amount'] : 0;
			$r['result_remarks_list'] = array(); // keep light; column already has result_remarks
		}
		return array('rows' => $rows, 'total' => $total);
	}

	/** @deprecated kept for BC — use _fetch_students_paged */
	private function _fetch_students($campus_id, $course_id, $class_id, $search_type, $council_exam_no)
	{
		$pack = $this->_fetch_students_paged($campus_id, $course_id, $class_id, $search_type, $council_exam_no, 'active', '', 0, 10000);
		return $pack['rows'];
	}

	// ─── Enrichment (page-sized, batched — no N+1) ──────────

	private function _enrich_default($students)
	{
		if (!count($students)) return array();
		$ids = array_map(function ($s) { return (int)$s['student_id']; }, $students);
		$cnics = array_unique(array_map(function ($s) { return $s['cnic']; }, $students));

		$docs_by = $this->_docs_by_student($ids);
		$pay_stats = $this->_payment_stats_by_student($ids);
		$contract_by = $this->_contracts_map($students);
		$ref_by = $this->_references_map($students);
		$council_fee_by = $this->_council_fee_expenses($ids);
		$remarks_by = $this->_remarks_by_cnic($cnics);

		$out = array();
		foreach ($students as $s) {
			$sid = (int)$s['student_id'];
			$docs = isset($docs_by[$sid]) ? $docs_by[$sid] : array();
			$st = isset($pay_stats[$sid]) ? $pay_stats[$sid] : null;

			$has_plan = $st ? !empty($st['has_own_plan']) : false;
			$payment_alert = (!$has_plan && (int)$s['contractor_id'] <= 0);

			$photo = isset($docs['Photo'][0]) ? $docs['Photo'][0] : null;
			$s['image_url'] = $this->_doc_url($photo);
			$s['documents'] = array(
				'id_card' => !empty($docs['ID Card']),
				'b_form' => !empty($docs['B - FORM']),
				'photo' => !empty($docs['Photo']),
				'result_card' => !empty($docs['Result Card']),
			);
			$s['contractor_label'] = isset($contract_by[$sid]) ? $contract_by[$sid] : ((int)$s['contractor_id'] === 0 ? 'N/A' : '');
			$s['reference_label'] = isset($ref_by[$sid]) ? $ref_by[$sid] : null;
			$s['payment_alert'] = $payment_alert;
			$s['unpaid_installments'] = $st ? (int)$st['unpaid_count'] : 0;
			$s['unpaid_installments_till_date'] = $st ? (int)$st['unpaid_till_count'] : 0;
			$s['result_remarks'] = isset($remarks_by[$s['cnic']]) ? $remarks_by[$s['cnic']] : array();
			$s['council_fee_remarks'] = isset($council_fee_by[$sid]) ? $council_fee_by[$sid] : array();
			$s['has_contract'] = (int)$s['contract_id'] > 0;
			$out[] = $s;
		}
		return $out;
	}

	/** Blacklist page rows — already SQL-filtered; attach fee breakdown in one payments pass */
	private function _enrich_blacklist_page($students)
	{
		if (!count($students)) return array();
		$ids = array_map(function ($s) { return (int)$s['student_id']; }, $students);
		$pay_by = $this->_payments_by_student($ids);
		$docs_by = $this->_docs_by_student($ids);
		$today = date('Y-m-d');
		$out = array();
		foreach ($students as $s) {
			$sid = (int)$s['student_id'];
			$payments = isset($pay_by[$sid]) ? $pay_by[$sid] : array();
			$total_fee = 0;
			$fee_decided = 0;
			$total_submitted = 0;
			$created_council = 0;
			$submitted_council = 0;
			$unpaid_till = 0;
			$paid_rows = array();
			$unpaid_rows = array();
			foreach ($payments as $payment) {
				if ($payment['payment_plan'] != 'consulation fee') {
					$total_fee += (float)$payment['amount'];
				}
				if ($payment['payment_plan'] == 'consulation fee') {
					$created_council += (float)$payment['amount'];
					if ((int)$payment['paid'] === 1) $submitted_council += (float)$payment['actual_amount'];
				}
				if ($payment['dead_line'] < $today) {
					$fee_decided += (float)$payment['amount'];
					if ((int)$payment['paid'] === 0) $unpaid_till++;
				}
				if ((int)$payment['paid'] === 1 && $payment['payment_plan'] != 'consulation fee') {
					$total_submitted += (float)$payment['actual_amount'];
					$paid_rows[] = $payment;
				}
				if ((int)$payment['paid'] === 0) $unpaid_rows[] = $payment;
			}
			$docs = isset($docs_by[$sid]) ? $docs_by[$sid] : array();
			$photo = isset($docs['Photo'][0]) ? $docs['Photo'][0] : null;
			$s['image_url'] = $this->_doc_url($photo);
			$s['fee'] = array(
				'total_fee' => $total_fee,
				'fee_decided_current_time' => $fee_decided,
				'total_fee_submitted' => $total_submitted,
				'percent_paid' => $total_fee > 0 ? round(($total_submitted / $total_fee) * 100, 1) : 0,
				'created_council_fee' => $created_council,
				'submitted_council_fee' => $submitted_council,
				'unpaid_installments_current_time' => $unpaid_till,
				'paid_payments' => $paid_rows,
				'unpaid_payments' => $unpaid_rows,
			);
			$out[] = $s;
		}
		return $out;
	}

	private function _enrich_shift($students)
	{
		if (!count($students)) return array();
		$ids = array_map(function ($s) { return (int)$s['student_id']; }, $students);
		$docs_by = $this->_docs_by_student($ids);
		$shift_names = array();
		$study_names = array();
		if ($this->db->table_exists('shifts')) {
			foreach ($this->db->get('shifts')->result_array() as $sh) $shift_names[$sh['id']] = $sh['name'];
		}
		if ($this->db->table_exists('study_type')) {
			foreach ($this->db->get('study_type')->result_array() as $st) $study_names[$st['id']] = $st['name'];
		}
		foreach ($students as &$s) {
			$sid = (int)$s['student_id'];
			$docs = isset($docs_by[$sid]) ? $docs_by[$sid] : array();
			$photo = isset($docs['Photo'][0]) ? $docs['Photo'][0] : null;
			$s['image_url'] = $this->_doc_url($photo);
			$s['documents'] = array(
				'id_card' => !empty($docs['ID Card']),
				'b_form' => !empty($docs['B - FORM']),
				'photo' => !empty($docs['Photo']),
				'result_card' => !empty($docs['Result Card']),
			);
			$s['shift_name'] = isset($shift_names[$s['shift']]) ? $shift_names[$s['shift']] : (string)$s['shift'];
			$s['study_type_name'] = isset($study_names[$s['study_type']]) ? $study_names[$s['study_type']] : (string)$s['study_type'];
			$s['has_contract'] = (int)$s['contract_id'] > 0;
		}
		return $students;
	}

	private function _enrich_using_app($students)
	{
		if (!count($students)) return array();
		$ids = array_map(function ($s) { return (int)$s['student_id']; }, $students);
		$docs_by = $this->_docs_by_student($ids);
		$logins = $this->_last_logins_by_student($ids);
		$shift_names = array();
		$study_names = array();
		if ($this->db->table_exists('shifts')) {
			foreach ($this->db->get('shifts')->result_array() as $sh) $shift_names[$sh['id']] = $sh['name'];
		}
		if ($this->db->table_exists('study_type')) {
			foreach ($this->db->get('study_type')->result_array() as $st) $study_names[$st['id']] = $st['name'];
		}

		foreach ($students as &$s) {
			$sid = (int)$s['student_id'];
			$docs = isset($docs_by[$sid]) ? $docs_by[$sid] : array();
			$photo = isset($docs['Photo'][0]) ? $docs['Photo'][0] : null;
			$s['image_url'] = $this->_doc_url($photo);
			$s['shift_name'] = isset($shift_names[$s['shift']]) ? $shift_names[$s['shift']] : $s['shift'];
			$s['study_type_name'] = isset($study_names[$s['study_type']]) ? $study_names[$s['study_type']] : $s['study_type'];
			$s['portal_last_login'] = isset($logins[$sid]['portal']) ? $logins[$sid]['portal'] : null;
			$s['app_last_login'] = isset($logins[$sid]['app']) ? $logins[$sid]['app'] : null;
		}
		return $students;
	}

	private function _enrich_archived($students)
	{
		if (!count($students)) return array();
		$ids = array_map(function ($s) { return (int)$s['student_id']; }, $students);
		$docs_by = $this->_docs_by_student($ids);
		$cnics = array_unique(array_map(function ($s) { return $s['cnic']; }, $students));
		$remarks_by = $this->_remarks_by_cnic($cnics);
		$council_fee_by = $this->_council_fee_expenses($ids);
		$frozen = array();
		$deleted_by = array();
		if (count($ids) && $this->db->table_exists('freeze_student')) {
			foreach ($this->db->where_in('student_id', $ids)->get('freeze_student')->result_array() as $f) {
				$frozen[(int)$f['student_id']] = true;
			}
		}
		if (count($ids) && $this->db->table_exists('deleted_students')) {
			foreach ($this->db->where_in('student_id', $ids)->where('status', 1)->get('deleted_students')->result_array() as $d) {
				$deleted_by[(int)$d['student_id']] = isset($d['reason']) ? $d['reason'] : '';
			}
		}

		foreach ($students as &$s) {
			$sid = (int)$s['student_id'];
			$docs = isset($docs_by[$sid]) ? $docs_by[$sid] : array();
			$photo = isset($docs['Photo'][0]) ? $docs['Photo'][0] : null;
			$s['image_url'] = $this->_doc_url($photo);
			$is_freeze = !empty($frozen[$sid]);
			$s['archive_type'] = $is_freeze ? 'FREEZED' : 'DELETED';
			$s['is_freezed'] = $is_freeze;
			$s['delete_reason'] = isset($deleted_by[$sid]) ? $deleted_by[$sid] : '';
			$s['result_remarks'] = isset($remarks_by[$s['cnic']]) ? $remarks_by[$s['cnic']] : array();
			$s['has_contract'] = (int)$s['contract_id'] > 0;
			$s['council_fee_remarks'] = isset($council_fee_by[$sid]) ? $council_fee_by[$sid] : array();
		}
		return $students;
	}

	private function _enrich_council_list($rows)
	{
		foreach ($rows as &$r) {
			$sid = (int)$r['student_id'];
			$total = $this->council->getTotalFeeDetail($sid);
			$decided = $this->council->getFeeDecidedCurrentTime($sid);
			$paid_total = $this->council->getTotalPaidFeeDetail($sid);
			$unpaid = $this->council->getUnpaidFeeDetail($sid);
			$paid = $this->council->getPaidFeeDetail($sid);
			$renewed = $this->council->renewInstallments($sid);
			$docs = $this->db->select('type')->from('student_documents')->where('student_id', $sid)->get()->result_array();
			$types = array();
			foreach ($docs as $d) $types[$d['type']] = true;
			$r['fee_total'] = isset($total[0]['amount']) ? (float)$total[0]['amount'] : 0;
			$r['fee_decided'] = isset($decided[0]['amount']) ? (float)$decided[0]['amount'] : 0;
			$r['fee_paid_total'] = (float)$paid_total;
			$r['fee_payable'] = max(0, $r['fee_decided'] - $r['fee_paid_total']);
			$r['fee_unpaid_total'] = max(0, $r['fee_total'] - $r['fee_paid_total']);
			$r['paid_payments'] = $paid;
			$r['unpaid_payments'] = $unpaid;
			$r['renewed_installments'] = $renewed;
			$r['documents'] = array(
				'photo' => !empty($types['Photo']),
				'result_card' => !empty($types['Result Card']),
				'id_card' => !empty($types['ID Card']),
				'b_form' => !empty($types['B - FORM']),
			);
		}
		return $rows;
	}

	private function _detail_date_range($class_id)
	{
		$this->db->select('MIN(students.registration_date) AS startdate, MAX(dead_line) AS enddate', false);
		$this->db->from('students');
		$this->db->join('payments', 'payments.student_id = students.student_id', 'left');
		$this->db->where(array('students.status' => '1'));
		if ($class_id > 0) {
			$this->db->where('students.class_id', $class_id);
			$this->db->where("payments.payment_plan not like ('%consulation fee%')");
		}
		$row = $this->db->get()->row_array();
		return array(
			'startdate' => !empty($row['startdate']) ? $row['startdate'] : date('Y-m-01'),
			'enddate' => !empty($row['enddate']) ? $row['enddate'] : date('Y-m-d'),
		);
	}

	private function _month_list($start, $end)
	{
		$months = array();
		try {
			$startDt = new DateTime(date('Y-m-01', strtotime($start)));
			$endDt = new DateTime(date('Y-m-01', strtotime($end)));
			$endDt->modify('+1 month');
			$period = new DatePeriod($startDt, new DateInterval('P1M'), $endDt);
			foreach ($period as $dt) {
				$months[] = $dt->format('Y-m');
			}
		} catch (Exception $e) {
			$months[] = date('Y-m');
		}
		return $months;
	}

	private function _enrich_studentdetail($students, $months)
	{
		$out = array();
		$footer_must = array();
		$footer_paid = array();
		foreach ($months as $m) {
			$footer_must[$m] = 0;
			$footer_paid[$m] = 0;
		}
		if (!count($students)) {
			return array('rows' => array(), 'footer_must' => $footer_must, 'footer_paid' => $footer_paid);
		}

		$ids = array_map(function ($s) { return (int)$s['student_id']; }, $students);
		$pay_by = $this->_payments_by_student($ids);

		foreach ($students as $s) {
			$sid = (int)$s['student_id'];
			$payments = isset($pay_by[$sid]) ? $pay_by[$sid] : array();
			$by_month = array();
			foreach ($payments as $p) {
				$ym = substr($p['dead_line'], 0, 7);
				if (!isset($by_month[$ym])) $by_month[$ym] = array();
				$by_month[$ym][] = $p;
			}
			$month_cells = array();
			$row_must = 0;
			$row_paid = 0;
			foreach ($months as $ym) {
				$cells = array();
				foreach (isset($by_month[$ym]) ? $by_month[$ym] : array() as $p) {
					$amt = (float)$p['amount'];
					$act = (float)$p['actual_amount'];
					$row_must += $amt;
					$row_paid += $act;
					$footer_must[$ym] += $amt;
					$footer_paid[$ym] += $act;
					$cells[] = array(
						'amount' => $amt,
						'actual_amount' => $act,
						'unpaid_style' => $act == 0,
						'dead_line' => $p['dead_line'],
						'payment_plan' => $p['payment_plan'],
					);
				}
				$month_cells[$ym] = $cells;
			}
			$s['months'] = $month_cells;
			$s['must_paid'] = $row_must;
			$s['paid_total'] = $row_paid;
			$out[] = $s;
		}
		return array(
			'rows' => $out,
			'footer_must' => $footer_must,
			'footer_paid' => $footer_paid,
		);
	}

	private function _docs_by_student($ids)
	{
		$map = array();
		if (!count($ids)) return $map;
		$rows = $this->db->where_in('student_id', $ids)->get('student_documents')->result_array();
		foreach ($rows as $r) {
			$sid = (int)$r['student_id'];
			$type = $r['type'];
			if (!isset($map[$sid])) $map[$sid] = array();
			if (!isset($map[$sid][$type])) $map[$sid][$type] = array();
			$map[$sid][$type][] = $r;
		}
		return $map;
	}

	private function _payments_by_student($ids)
	{
		$map = array();
		if (!count($ids)) return $map;
		$rows = $this->db->where_in('student_id', $ids)->get('payments')->result_array();
		foreach ($rows as $r) {
			$sid = (int)$r['student_id'];
			if (!isset($map[$sid])) $map[$sid] = array();
			$map[$sid][] = $r;
		}
		return $map;
	}

	/**
	 * Lightweight payment aggregates for list views (no full row payload).
	 * @return array<int, array{unpaid_count:int, unpaid_till_count:int, has_own_plan:int, unpaid_till_amount:float}>
	 */
	private function _payment_stats_by_student($ids)
	{
		$map = array();
		if (!count($ids)) return $map;
		$today = date('Y-m-d');
		$sql = 'SELECT student_id,
			SUM(CASE WHEN paid = 0 THEN 1 ELSE 0 END) AS unpaid_count,
			SUM(CASE WHEN paid = 0 AND dead_line < ? THEN 1 ELSE 0 END) AS unpaid_till_count,
			SUM(CASE WHEN paid = 0 AND dead_line < ? THEN amount ELSE 0 END) AS unpaid_till_amount,
			SUM(CASE WHEN contract_id = 0 THEN 1 ELSE 0 END) AS has_own_plan
			FROM payments WHERE student_id IN (' . implode(',', array_map('intval', $ids)) . ')
			GROUP BY student_id';
		foreach ($this->db->query($sql, array($today, $today))->result_array() as $r) {
			$map[(int)$r['student_id']] = array(
				'unpaid_count' => (int)$r['unpaid_count'],
				'unpaid_till_count' => (int)$r['unpaid_till_count'],
				'unpaid_till_amount' => (float)$r['unpaid_till_amount'],
				'has_own_plan' => (int)$r['has_own_plan'] > 0 ? 1 : 0,
			);
		}
		return $map;
	}

	private function _unpaid_rows_by_student($ids)
	{
		$map = array();
		if (!count($ids)) return $map;
		$rows = $this->db->where_in('student_id', $ids)
			->where('paid', 0)
			->order_by('dead_line', 'ASC')
			->get('payments')
			->result_array();
		foreach ($rows as $r) {
			$sid = (int)$r['student_id'];
			if (!isset($map[$sid])) $map[$sid] = array();
			$map[$sid][] = $r;
		}
		return $map;
	}

	private function _last_logins_by_student($ids)
	{
		$map = array();
		if (!count($ids) || !$this->db->table_exists('students_login_tracking')) return $map;
		// Latest login per student+type via self-join on max id
		$sql = 'SELECT t.student_id, t.type, t.login_time
			FROM students_login_tracking t
			INNER JOIN (
				SELECT student_id, type, MAX(students_login_tracking_id) AS max_id
				FROM students_login_tracking
				WHERE student_id IN (' . implode(',', array_map('intval', $ids)) . ')
				AND type IN ("portal","app")
				GROUP BY student_id, type
			) x ON x.max_id = t.students_login_tracking_id';
		foreach ($this->db->query($sql)->result_array() as $r) {
			$sid = (int)$r['student_id'];
			if (!isset($map[$sid])) $map[$sid] = array();
			$map[$sid][$r['type']] = $r['login_time'];
		}
		return $map;
	}

	private function _contracts_map($students)
	{
		$map = array();
		$cids = array();
		foreach ($students as $s) {
			if ((int)$s['contract_id'] > 0) $cids[] = (int)$s['contract_id'];
		}
		$cids = array_unique($cids);
		if (!count($cids)) return $map;
		$this->db->select('contracts.contract_id, contracts.contract_name, contractors.name');
		$this->db->from('contracts');
		$this->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'inner');
		$this->db->where_in('contracts.contract_id', $cids);
		foreach ($this->db->get()->result_array() as $c) {
			$label = $c['name'] . ' (' . $c['contract_name'] . ')';
			foreach ($students as $s) {
				if ((int)$s['contract_id'] === (int)$c['contract_id']) {
					$map[(int)$s['student_id']] = $label;
				}
			}
		}
		return $map;
	}

	private function _references_map($students)
	{
		$map = array();
		$rids = array();
		foreach ($students as $s) {
			if (!empty($s['reference_user_id'])) $rids[] = (int)$s['reference_user_id'];
		}
		$rids = array_unique($rids);
		if (!count($rids)) return $map;
		$rows = $this->db->where_in('reference_user_id', $rids)->get('reference_users')->result_array();
		$by = array();
		foreach ($rows as $r) $by[(int)$r['reference_user_id']] = $r['name'] . ' - ' . $r['phone'];
		foreach ($students as $s) {
			$rid = (int)$s['reference_user_id'];
			if ($rid && isset($by[$rid])) $map[(int)$s['student_id']] = $by[$rid];
		}
		return $map;
	}

	private function _council_fee_expenses($ids)
	{
		$map = array();
		if (!count($ids)) return $map;
		$this->db->select('expenses.*, campuses.campus_name, classes.session');
		$this->db->from('expenses');
		$this->db->join('classes', 'classes.class_id=expenses.class_id', 'left');
		$this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
		$this->db->where_in('student_id', $ids);
		foreach ($this->db->get()->result_array() as $r) {
			$sid = (int)$r['student_id'];
			if (!isset($map[$sid])) $map[$sid] = array();
			$map[$sid][] = $r;
		}
		return $map;
	}

	private function _remarks_by_cnic($cnics)
	{
		$map = array();
		if (!count($cnics)) return $map;
		$rows = $this->db->where_in('cnic', $cnics)->get('punjab_council_roll_number')->result_array();
		foreach ($rows as $r) {
			$c = $r['cnic'];
			if (!isset($map[$c])) $map[$c] = array();
			$map[$c][] = $r;
		}
		return $map;
	}

	private function _result_remarks_data($cnic)
	{
		if ($cnic === '') return array();
		return $this->db->order_by('id', 'DESC')->get_where('punjab_council_roll_number', array('cnic' => $cnic))->result_array();
	}

	/** Reset fee plan — same as Students::reset_plan */
	public function reset_plan($student_id = 0)
	{
		if (!$this->_perm('student_payment_reset')) {
			$this->_json(array('success' => false, 'message' => 'No permission'), 403);
		}
		$student_id = (int)$student_id;
		if (!$student_id) $this->_json(array('success' => false, 'message' => 'student_id required'), 422);
		$this->db->where(array('student_id' => $student_id, 'contract_id' => 0))->delete('payments');
		$this->_json(array('success' => true, 'message' => 'Fee plan reset'));
	}

	// ─── Form lookups ───────────────────────────────────────

	public function form_meta()
	{
		$contractors = $this->db->order_by('name', 'ASC')->get('contractors')->result_array();
		$references = $this->db->get_where('reference_users', array('status' => 1))->result_array();
		$occupation_roots = $this->db->order_by('occupation_name', 'ASC')
			->get_where('occupations', array('sub_of' => 0))
			->result_array();
		$campuses = $this->db->where('status', 1)->order_by('campus_name', 'ASC')->get('campuses')->result_array();
		$this->_json(array(
			'success' => true,
			'data' => array(
				'permissions' => $this->_permissions(),
				'campuses' => $campuses,
				'contractors' => $contractors,
				'references' => $references,
				'occupation_roots' => $occupation_roots,
				'religions' => array('Islam', 'Christianity', 'Hinduism', 'Sikhism', 'Other'),
				'blood_groups' => array('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'),
				'genders' => array('Male', 'Female'),
				'sections' => array('First Year', 'Second Year'),
			),
		));
	}

	public function study_types()
	{
		$course_id = (int)$this->input->get('course_id');
		$this->db->from('study_type');
		if ($course_id > 0) $this->db->where('course_id', $course_id);
		$this->db->order_by('name', 'ASC');
		$this->_json(array('success' => true, 'data' => $this->db->get()->result_array()));
	}

	public function shifts()
	{
		$campus_id = (int)$this->input->get('campus_id');
		$study_type = (int)$this->input->get('study_type');
		$this->db->from('shifts');
		if ($campus_id > 0) {
			$this->db->where("FIND_IN_SET(" . $campus_id . ", campus_id)", null, false);
		}
		if ($study_type > 0) $this->db->where('study_type_id', $study_type);
		$this->db->order_by('name', 'ASC');
		$this->_json(array('success' => true, 'data' => $this->db->get()->result_array()));
	}

	public function course_sessions()
	{
		$course_id = (int)$this->input->get('course_id');
		$rows = array();
		if ($course_id > 0 && $this->db->table_exists('course_sessions')) {
			$rows = $this->db->get_where('course_sessions', array('course_id' => $course_id))->result_array();
		}
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function contracts()
	{
		$contractor_id = (int)$this->input->get('contractor_id');
		if (!$contractor_id) $this->_json(array('success' => true, 'data' => array()));
		$rows = $this->db->get_where('contracts', array('contractor_id' => $contractor_id))->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	/** Override classes() with for_add deadline filter via query param */
	public function classes_for_add()
	{
		$campus_id = (int)$this->input->get('campus_id');
		$course_id = (int)$this->input->get('course_id');
		$this->db->from('classes');
		$this->db->where('status', 1);
		$this->db->where('dead_line_entry >=', date('Y-m-d'));
		if ($campus_id > 0) $this->db->where('campus_id', $campus_id);
		if ($course_id > 0) $this->db->where('course_id', $course_id);
		$class_ids = $this->_class_ids();
		if (is_array($class_ids)) {
			if (!count($class_ids)) $this->_json(array('success' => true, 'data' => array()));
			$this->db->where_in('class_id', $class_ids);
		}
		$this->db->order_by('name', 'ASC');
		$this->_json(array('success' => true, 'data' => $this->db->get()->result_array()));
	}

	public function check_cnic()
	{
		$cnic = trim((string)$this->input->get('cnic'));
		$course_id = (int)$this->input->get('course_id');
		$exclude = (int)$this->input->get('exclude_student_id');
		if ($cnic === '' || !$course_id) {
			$this->_json(array('success' => true, 'exists' => false));
		}
		$rows = $this->student->checkStudentNIC($cnic, $course_id);
		if ($exclude > 0) {
			$rows = array_values(array_filter($rows, function ($r) use ($exclude) {
				return (int)$r['student_id'] !== $exclude;
			}));
		}
		$by_cnic = $this->db->get_where('students', array('cnic' => $cnic))->row_array();
		$this->_json(array(
			'success' => true,
			'exists' => count($rows) > 0,
			'existing_for_course' => $rows,
			'prefill' => $by_cnic ? $by_cnic : null,
		));
	}

	// ─── Occupations ────────────────────────────────────────

	public function occupations()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		if ($method === 'POST') {
			$body = $this->_body();
			$name = isset($body['occupation_name']) ? trim($body['occupation_name']) : '';
			$sub_of = isset($body['sub_of']) ? (int)$body['sub_of'] : 0;
			if ($name === '') $this->_json(array('success' => false, 'message' => 'occupation_name required'), 422);
			$this->db->insert('occupations', array(
				'occupation_name' => $name,
				'has_sub' => 0,
				'sub_of' => $sub_of,
			));
			$id = (int)$this->db->insert_id();
			if ($sub_of > 0) {
				$this->db->where('occupation_id', $sub_of)->update('occupations', array('has_sub' => 1));
			}
			$this->_json(array('success' => true, 'id' => $id));
		}

		$parent_id = $this->input->get('parent_id');
		$tree = $this->input->get('tree');
		if ($tree === '1' || $tree === 'true') {
			$roots = $this->db->order_by('occupation_name', 'ASC')
				->get_where('occupations', array('sub_of' => 0))
				->result_array();
			foreach ($roots as &$r) {
				$r['children'] = $this->_occupation_children((int)$r['occupation_id']);
			}
			$this->_json(array('success' => true, 'data' => $roots));
		}
		if ($parent_id === null || $parent_id === '') {
			$rows = $this->db->order_by('occupation_name', 'ASC')
				->get_where('occupations', array('sub_of' => 0))
				->result_array();
		} else {
			$rows = $this->db->order_by('occupation_name', 'ASC')
				->get_where('occupations', array('sub_of' => (int)$parent_id))
				->result_array();
		}
		$this->_json(array('success' => true, 'data' => $rows));
	}

	private function _occupation_children($parent_id)
	{
		$rows = $this->db->order_by('occupation_name', 'ASC')
			->get_where('occupations', array('sub_of' => $parent_id))
			->result_array();
		foreach ($rows as &$r) {
			$r['children'] = $this->_occupation_children((int)$r['occupation_id']);
		}
		return $rows;
	}

	public function occupation($id = 0)
	{
		$id = (int)$id;
		$row = $this->db->get_where('occupations', array('occupation_id' => $id))->row_array();
		if (!$row) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		$method = $_SERVER['REQUEST_METHOD'];
		if ($method === 'PUT' || $method === 'POST') {
			$body = $this->_body();
			$name = isset($body['occupation_name']) ? trim($body['occupation_name']) : '';
			if ($name === '') $this->_json(array('success' => false, 'message' => 'occupation_name required'), 422);
			$this->db->where('occupation_id', $id)->update('occupations', array('occupation_name' => $name));
			$this->_json(array('success' => true, 'message' => 'Updated'));
		}
		$path = $this->_occupation_path($id);
		$this->_json(array('success' => true, 'data' => $row, 'path' => $path));
	}

	private function _occupation_path($id)
	{
		$path = array();
		$cur = (int)$id;
		$guard = 0;
		while ($cur > 0 && $guard < 20) {
			$row = $this->db->get_where('occupations', array('occupation_id' => $cur))->row_array();
			if (!$row) break;
			array_unshift($path, $row);
			$cur = (int)$row['sub_of'];
			$guard++;
		}
		return $path;
	}

	private function _occupation_label($id)
	{
		if (!$id) return null;
		$path = $this->_occupation_path((int)$id);
		$names = array();
		foreach ($path as $p) $names[] = $p['occupation_name'];
		return count($names) ? implode(' → ', $names) : null;
	}

	// ─── Student create / read / update ─────────────────────

	public function create()
	{
		if (!$this->_perm('student_add')) {
			$this->_json(array('success' => false, 'message' => 'No permission to add student'), 403);
		}
		$body = $this->_body();
		$cnic = isset($body['cnic']) ? trim($body['cnic']) : '';
		$course_id = isset($body['course_id']) ? (int)$body['course_id'] : 0;
		$class_id = isset($body['class_id']) ? (int)$body['class_id'] : 0;
		$campus_id = isset($body['campus_id']) ? (int)$body['campus_id'] : 0;

		if ($cnic === '' || !$course_id || !$class_id || !$campus_id) {
			$this->_json(array('success' => false, 'message' => 'cnic, campus_id, course_id, class_id required'), 422);
		}
		$dup = $this->student->checkStudentNIC($cnic, $course_id);
		if (count($dup) > 0) {
			$this->_json(array('success' => false, 'message' => 'Student with CNIC ' . $cnic . ' is already added for this course'), 422);
		}

		$required = array('first_name', 'last_name', 'father_name', 'gender', 'study_campus', 'password', 'mobile', 'address');
		foreach ($required as $f) {
			if (!isset($body[$f]) || trim((string)$body[$f]) === '') {
				$this->_json(array('success' => false, 'message' => $f . ' is required'), 422);
			}
		}

		$course = $this->db->get_where('courses', array('course_id' => $course_id))->row();
		$class = $this->db->get_where('classes', array('class_id' => $class_id))->row();
		if (!$course || !$class) $this->_json(array('success' => false, 'message' => 'Invalid course/class'), 422);
		if (!empty($class->dead_line_entry) && $class->dead_line_entry < date('Y-m-d')) {
			$this->_json(array('success' => false, 'message' => 'Class admission deadline has passed'), 422);
		}
		$campus = $this->db->get_where('campuses', array('campus_id' => $class->campus_id))->row();
		$count_row = $this->db->select('MAX(count) as count', false)->get_where('students', array('class_id' => $class_id))->row();
		$student_count = ($count_row && $count_row->count !== null) ? ((int)$count_row->count + 1) : 1;
		$roll_no = $student_count . '-' . $class->badge_no . '-' . $course->course_code . '-' . $campus->roll_no_code;

		$contractor_id = isset($body['contractor_id']) ? (int)$body['contractor_id'] : 0;
		$contract_id = isset($body['contract_id']) ? (int)$body['contract_id'] : 0;
		if ($contractor_id === 0 || $contract_id === 0) {
			$contractor_id = 0;
			$contract_id = 0;
		}

		$notes = isset($body['notes']) ? $body['notes'] : (isset($body['note']) ? $body['note'] : array());
		if (!is_array($notes)) $notes = array($notes);

		$data = array(
			'course_id' => $course_id,
			'study_campus' => (int)$body['study_campus'],
			'first_name' => trim($body['first_name']),
			'last_name' => trim($body['last_name']),
			'father_name' => trim($body['father_name']),
			'mother_name' => isset($body['mother_name']) ? trim($body['mother_name']) : null,
			'gender' => $body['gender'],
			'caste' => isset($body['caste']) ? $body['caste'] : '',
			'religion' => isset($body['religion']) ? $body['religion'] : '',
			'qualification' => isset($body['qualification']) ? $body['qualification'] : '',
			'class_id' => $class_id,
			'roll_no' => $roll_no,
			'email' => isset($body['email']) ? $body['email'] : '',
			'cnic' => $cnic,
			'date_of_birth' => isset($body['date_of_birth']) ? $body['date_of_birth'] : null,
			'count' => $student_count,
			'district' => isset($body['district']) ? $body['district'] : '',
			'tehsil' => isset($body['tehsil']) ? $body['tehsil'] : '',
			'mark_of_identification' => isset($body['mark_of_identification']) ? $body['mark_of_identification'] : '',
			'place_of_birth' => isset($body['place_of_birth']) ? $body['place_of_birth'] : '',
			'registration_date' => !empty($body['registration_date']) ? $body['registration_date'] : date('Y-m-d'),
			'total_fee' => isset($body['total_fee']) ? (float)$body['total_fee'] : 0,
			'current_session_fee' => 0,
			'blood_group' => isset($body['blood_group']) ? $body['blood_group'] : '',
			'city' => isset($body['city']) ? $body['city'] : '',
			'address' => trim($body['address']),
			'mobile' => trim($body['mobile']),
			'emergency_no' => isset($body['emergency_no']) ? $body['emergency_no'] : '',
			'status' => '1',
			'contractor_id' => $contractor_id,
			'contract_id' => $contract_id,
			'board' => isset($body['board']) ? $body['board'] : '',
			'section' => isset($body['section']) ? $body['section'] : '',
			'shift' => isset($body['shift']) ? $body['shift'] : '',
			'study_type' => isset($body['study_type']) ? $body['study_type'] : '',
			'books_1' => !empty($body['books_1']) ? 1 : 0,
			'books_2' => !empty($body['books_2']) ? 1 : 0,
			'student_card' => !empty($body['student_card']) ? 1 : 0,
			'password' => md5((string)$body['password']),
			'notes' => json_encode($notes),
			'add_by' => $this->_actor_name(),
			'last_edit' => $this->_actor_name(),
			'entry_date' => date('Y-m-d'),
			'reference_user_id' => isset($body['reference_user_id']) ? (int)$body['reference_user_id'] : 0,
			'student_occupation_id' => !empty($body['student_occupation_id']) ? (int)$body['student_occupation_id'] : null,
			'father_occupation_id' => !empty($body['father_occupation_id']) ? (int)$body['father_occupation_id'] : null,
			'mother_occupation_id' => !empty($body['mother_occupation_id']) ? (int)$body['mother_occupation_id'] : null,
		);

		$student_id = $this->student->storeStudent($data);

		// Machine ID (legacy logic)
		$campus_row = $this->db->get_where('campuses', array('campus_id' => $campus_id))->row_array();
		if ($campus_row) {
			$sql = 'SELECT machine_id FROM machine_data WHERE campus_id=' . (int)$campus_id . ' ORDER BY machine_id DESC LIMIT 1';
			$query = $this->db->query($sql)->result_array();
			$last = 1;
			if (count($query) && !empty($query[0]['machine_id'])) {
				$last_machine_id = substr($query[0]['machine_id'], 0, -2);
				$last = ((int)$last_machine_id) + 1;
			}
			$this->db->insert('machine_data', array(
				'teacher_student_id' => $student_id,
				'machine_id' => $last . $campus_row['campus_code'],
				'type' => 'student',
				'campus_id' => $campus_id,
			));
		}

		$this->_json(array(
			'success' => true,
			'id' => (int)$student_id,
			'roll_no' => $roll_no,
			'message' => 'Student added successfully',
			'next' => '/students/payments/' . $student_id,
		));
	}

	public function student($id = 0)
	{
		$id = (int)$id;
		$row = $this->student->editStudent($id);
		if (!count($row)) $this->_json(array('success' => false, 'message' => 'Student not found'), 404);
		$s = $row[0];

		$method = $_SERVER['REQUEST_METHOD'];
		if ($method === 'PUT' || ($method === 'POST' && $this->input->get('update'))) {
			$this->_update_student($id, $this->_body(), $s);
		}

		$s['student_occupation_label'] = $this->_occupation_label(isset($s['student_occupation_id']) ? $s['student_occupation_id'] : 0);
		$s['father_occupation_label'] = $this->_occupation_label(isset($s['father_occupation_id']) ? $s['father_occupation_id'] : 0);
		$s['mother_occupation_label'] = $this->_occupation_label(isset($s['mother_occupation_id']) ? $s['mother_occupation_id'] : 0);
		$s['student_occupation_path'] = !empty($s['student_occupation_id']) ? $this->_occupation_path((int)$s['student_occupation_id']) : array();
		$s['father_occupation_path'] = !empty($s['father_occupation_id']) ? $this->_occupation_path((int)$s['father_occupation_id']) : array();
		$s['mother_occupation_path'] = !empty($s['mother_occupation_id']) ? $this->_occupation_path((int)$s['mother_occupation_id']) : array();

		$notes = array();
		if (!empty($s['notes'])) {
			$decoded = json_decode($s['notes'], true);
			$notes = is_array($decoded) ? $decoded : array($s['notes']);
		}
		$s['notes_list'] = $notes;

		$pending = $this->db->get_where('update_student_requests', array('student_id' => $id, 'ok_by_admin' => 0))->result_array();

		$this->_json(array(
			'success' => true,
			'data' => $s,
			'pending_update_request' => count($pending) > 0,
			'permissions' => $this->_permissions(),
		));
	}

	private function _update_student($id, $body, $current)
	{
		if (!$this->_perm('student_edit')) {
			$this->_json(array('success' => false, 'message' => 'No permission'), 403);
		}
		$pending = $this->db->get_where('update_student_requests', array('student_id' => $id, 'ok_by_admin' => 0))->result_array();
		if (count($pending) > 0) {
			$this->_json(array('success' => false, 'message' => 'This Student update Request already exists. Contact Control Center.'), 422);
		}

		$class_id = isset($body['class_id']) ? (int)$body['class_id'] : (int)$current['class_id'];
		$check_class = $this->db->get_where('classes', array('class_id' => $class_id))->row_array();
		if (!$check_class) $this->_json(array('success' => false, 'message' => 'Invalid class'), 422);

		$class_changed = ((int)$current['class_id'] !== $class_id);
		if ($class_changed && $check_class['dead_line_entry'] < date('Y-m-d')) {
			$this->_json(array('success' => false, 'message' => 'Cannot change class — admission deadline passed'), 422);
		}

		$contractor_id = isset($body['contractor_id']) ? (int)$body['contractor_id'] : 0;
		$contract_id = isset($body['contract_id']) ? (int)$body['contract_id'] : 0;
		if ($contractor_id === 0 || $contract_id === 0) {
			$contractor_id = 0;
			$contract_id = 0;
		}

		$password = !empty($body['password'])
			? md5((string)$body['password'])
			: (isset($current['password']) ? $current['password'] : '');

		$notes = isset($body['notes']) ? $body['notes'] : (isset($body['note']) ? $body['note'] : array());
		if (!is_array($notes)) $notes = array($notes);

		$roll_no = isset($body['roll_no']) ? $body['roll_no'] : $current['roll_no'];
		$count = isset($current['count']) ? $current['count'] : 0;
		if ($class_changed) {
			$course = $this->db->get_where('courses', array('course_id' => (int)$body['course_id']))->row();
			$class = $this->db->get_where('classes', array('class_id' => $class_id))->row();
			$campus = $this->db->get_where('campuses', array('campus_id' => $class->campus_id))->row();
			$count_row = $this->db->select('MAX(count) as count', false)->get_where('students', array('class_id' => $class_id))->row();
			$count = ($count_row && $count_row->count !== null) ? ((int)$count_row->count + 1) : 1;
			$roll_no = $count . '-' . $class->badge_no . '-' . $course->course_code . '-' . $campus->roll_no_code;
		}

		$data = array(
			'course_id' => isset($body['course_id']) ? (int)$body['course_id'] : $current['course_id'],
			'study_campus' => isset($body['study_campus']) ? (int)$body['study_campus'] : $current['study_campus'],
			'first_name' => isset($body['first_name']) ? trim($body['first_name']) : $current['first_name'],
			'last_name' => isset($body['last_name']) ? trim($body['last_name']) : $current['last_name'],
			'father_name' => isset($body['father_name']) ? trim($body['father_name']) : $current['father_name'],
			'mother_name' => array_key_exists('mother_name', $body) ? trim((string)$body['mother_name']) : (isset($current['mother_name']) ? $current['mother_name'] : null),
			'gender' => isset($body['gender']) ? $body['gender'] : $current['gender'],
			'caste' => isset($body['caste']) ? $body['caste'] : $current['caste'],
			'religion' => isset($body['religion']) ? $body['religion'] : $current['religion'],
			'qualification' => isset($body['qualification']) ? $body['qualification'] : $current['qualification'],
			'class_id' => $class_id,
			'email' => isset($body['email']) ? $body['email'] : $current['email'],
			'cnic' => isset($body['cnic']) ? trim($body['cnic']) : $current['cnic'],
			'roll_no' => $roll_no,
			'count' => $count,
			'date_of_birth' => isset($body['date_of_birth']) ? $body['date_of_birth'] : $current['date_of_birth'],
			'district' => isset($body['district']) ? $body['district'] : $current['district'],
			'tehsil' => isset($body['tehsil']) ? $body['tehsil'] : $current['tehsil'],
			'mark_of_identification' => isset($body['mark_of_identification']) ? $body['mark_of_identification'] : $current['mark_of_identification'],
			'place_of_birth' => isset($body['place_of_birth']) ? $body['place_of_birth'] : $current['place_of_birth'],
			'registration_date' => isset($body['registration_date']) ? $body['registration_date'] : $current['registration_date'],
			'total_fee' => isset($body['total_fee']) ? $body['total_fee'] : $current['total_fee'],
			'blood_group' => isset($body['blood_group']) ? $body['blood_group'] : $current['blood_group'],
			'city' => isset($body['city']) ? $body['city'] : $current['city'],
			'address' => isset($body['address']) ? $body['address'] : $current['address'],
			'mobile' => isset($body['mobile']) ? $body['mobile'] : $current['mobile'],
			'emergency_no' => isset($body['emergency_no']) ? $body['emergency_no'] : $current['emergency_no'],
			'status' => isset($body['status']) ? $body['status'] : $current['status'],
			'contractor_id' => $contractor_id,
			'contract_id' => $contract_id,
			'board' => isset($body['board']) ? $body['board'] : $current['board'],
			'section' => isset($body['section']) ? $body['section'] : $current['section'],
			'shift' => isset($body['shift']) ? $body['shift'] : $current['shift'],
			'study_type' => isset($body['study_type']) ? $body['study_type'] : $current['study_type'],
			'study_session' => isset($body['study_session']) ? $body['study_session'] : (isset($current['study_session']) ? $current['study_session'] : ''),
			'books_1' => !empty($body['books_1']) ? 1 : 0,
			'books_2' => !empty($body['books_2']) ? 1 : 0,
			'student_card' => !empty($body['student_card']) ? 1 : 0,
			'password' => $password,
			'notes' => json_encode($notes),
			'last_edit' => $this->_actor_name(),
			'reference_user_id' => isset($body['reference_user_id']) ? (int)$body['reference_user_id'] : $current['reference_user_id'],
			'student_occupation_id' => array_key_exists('student_occupation_id', $body)
				? (!empty($body['student_occupation_id']) ? (int)$body['student_occupation_id'] : null)
				: (isset($current['student_occupation_id']) ? $current['student_occupation_id'] : null),
			'father_occupation_id' => array_key_exists('father_occupation_id', $body)
				? (!empty($body['father_occupation_id']) ? (int)$body['father_occupation_id'] : null)
				: (isset($current['father_occupation_id']) ? $current['father_occupation_id'] : null),
			'mother_occupation_id' => array_key_exists('mother_occupation_id', $body)
				? (!empty($body['mother_occupation_id']) ? (int)$body['mother_occupation_id'] : null)
				: (isset($current['mother_occupation_id']) ? $current['mother_occupation_id'] : null),
		);

		$sensitive = (
			$current['cnic'] != $data['cnic']
			|| $current['class_id'] != $data['class_id']
			|| $current['first_name'] != $data['first_name']
			|| $current['last_name'] != $data['last_name']
			|| $current['contractor_id'] != $data['contractor_id']
			|| $current['contract_id'] != $data['contract_id']
		);

		if ($sensitive) {
			foreach ($data as $k => $value) {
				$this->db->set($k, $value);
			}
			$this->db->set('student_id', $id);
			$this->db->insert('update_student_requests');
			$this->_json(array(
				'success' => true,
				'request' => true,
				'message' => 'Student update request submitted successfully',
			));
		}

		$this->db->where('student_id', $id)->update('students', $data);
		$this->_json(array('success' => true, 'request' => false, 'message' => 'Student updated successfully'));
	}

	public function payments($student_id = 0)
	{
		$student_id = (int)$student_id;
		$student = $this->db->get_where('students', array('student_id' => $student_id))->row_array();
		if (!$student) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		$plan_rows = $this->student->payment_paid($student_id);
		$paid_rows = $this->db->get_where('payments', array('student_id' => $student_id, 'paid' => 1))->result_array();
		$reg_form = $this->db->get_where('student_documents', array('student_id' => $student_id, 'type' => 'Rules and Regulation Form'))->result_array();
		$has_plan = count($plan_rows) > 0;
		$needs_plan = !$has_plan;
		$plans = array();
		if (!empty($student['course_id'])) {
			$this->db->from('fee_rules');
			$this->db->where('course_id', $student['course_id']);
			if (!empty($student['session'])) $this->db->where('session', $student['session']);
			$this->db->where('status', 'active');
			$this->db->where('last_date >=', date('Y-m-d'));
			$plans = $this->db->get()->result_array();
			if (!count($plans)) {
				$plans = $this->db->get_where('fee_rules', array('course_id' => $student['course_id']))->result_array();
			}
		}
		$this->_json(array(
			'success' => true,
			'data' => array(
				'student' => $student,
				'payments' => $plan_rows,
				'plans' => $plans,
				'has_plan' => $has_plan,
				'needs_plan' => $needs_plan,
				'has_paid' => count($paid_rows) > 0,
				'has_rules_form' => count($reg_form) > 0,
			),
			'legacy_base' => $this->_legacy_base(),
		));
	}

	/** Full fee console payload — mirrors Students::payments_paid */
	public function payments_paid($student_id = 0)
	{
		$student_id = (int)$student_id;
		if (!$this->_perm('student_payments') && !$this->_is_admin()) {
			$this->_json(array('success' => false, 'message' => 'No payments permission'), 403);
		}
		$students = $this->student->getSingleStudent($student_id);
		if (!count($students)) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		$student = $students[0];

		$qry = "SELECT DISTINCT `merged_challan`, discount as discount FROM `payments` WHERE student_id=$student_id and merged_challan is not null GROUP by merged_challan UNION ALL SELECT DISTINCT `merged_challan`, sum(discount) as discount FROM `payments` WHERE student_id=$student_id and merged_challan is null and paid=1";
		$query = $this->db->query($qry)->result_array();
		$discountfee = 0;
		foreach ($query as $discs) $discountfee += (float)$discs['discount'];

		$specialdisc = 0;
		$sd = $this->db->select('sum(discount) as special_disc', false)
			->where('status', '1')
			->where('student_id', $student_id)
			->get('discounts_approval')
			->row_array();
		if ($sd && $sd['special_disc'] !== null) $specialdisc = (float)$sd['special_disc'];

		$total_fee = (float)(isset($student['current_session_fee']) ? $student['current_session_fee'] : 0)
			+ (float)(isset($student['extra_added_fee']) ? $student['extra_added_fee'] : 0);
		$student_fee = (float)(isset($student['total_fee']) ? $student['total_fee'] : 0)
			+ (float)(isset($student['extra_added_fee']) ? $student['extra_added_fee'] : 0);
		$fee_created = $this->student->getStudentDiscount($student_id);
		$paid_fee = $this->student->getStudentPaidFee($student_id);
		$remaining_fee = $this->student->getStudentRemainingFee($student_id);
		$fee_should_pay = $this->student->getStudentFeeShouldPay($student_id);
		$paid_val = isset($paid_fee[0]['paid_fee']) ? (float)$paid_fee[0]['paid_fee'] : $this->_scalar_fee($paid_fee);
		$should_val = isset($fee_should_pay[0]['fee_should_pay']) ? (float)$fee_should_pay[0]['fee_should_pay'] : $this->_scalar_fee($fee_should_pay);
		$created_val = is_numeric($fee_created) ? (float)$fee_created : $this->_scalar_fee($fee_created);

		$cf = $this->student->getStudentConsulationFee($student_id);
		$cf_should = $this->student->getStudentConsulationFeeShouldPay($student_id);
		$cf_paid = $this->student->getStudentConsulationFeePaid($student_id);
		$cf_unpaid = $this->student->getStudentConsulationFeeUnPaid($student_id);

		$plan = null;
		if (!empty($student['plan_id'])) {
			$plan = $this->db->get_where('fee_rules', array('fee_rule_id' => $student['plan_id']))->row_array();
		}
		$course = $this->db->get_where('courses', array('course_id' => $student['course_id']))->row_array();
		$class = $this->db->get_where('classes', array('class_id' => $student['class_id']))->row_array();

		$payments = $this->student->payment_paid($student_id);
		$deleted = $this->student->deleted_payment_paid($student_id);
		$enriched = array();
		$fine_per_day = ($plan && isset($plan['payment_plan']) && $plan['payment_plan'] === '24 Installments') ? 10 : 50;
		foreach ($payments as $idx => $p) {
			$enriched[] = $this->_enrich_payment_row($p, $idx, $student_id, $fine_per_day);
		}

		$freeze = $this->db->get_where('freeze_student', array('student_id' => $student_id))->result_array();
		$archive_type = null;
		if ((int)$student['status'] === 0) {
			$archive_type = count($freeze) > 0 ? 'FREEZED' : 'DELETED';
		}

		$this->db->select('campuses.*');
		$this->db->from('campus_rules');
		$this->db->join('campuses', 'campuses.campus_id=campus_rules.campus_id', 'inner');
		$this->db->join('closing_persons', 'campuses.campus_id=closing_persons.campus_id', 'inner');
		$this->db->where('campus_rules.college_fee', 1);
		$this->db->where('closing_persons.active_status', 1);
		$campuses = $this->db->get()->result_array();
		if (!count($campuses)) {
			$campuses = $this->db->where('status', 1)->get('campuses')->result_array();
		}

		$reversed = $this->db->select_sum('reversal_amount')->from('payments_reversal_requests')
			->where(array('student_id' => $student_id, 'done' => 1))->get()->row_array();

		$exam_sequences = array();
		if ($class && isset($class['exam_no'])) {
			$exam_sequences = $this->db->get_where('exam_sequence', array(
				'course_id' => $student['course_id'],
				'status' => 'Active',
				'first_year >=' => $class['exam_no'],
			))->result_array();
		}

		$payment_rules = $this->db->get_where('payment_rules', array(
			'status' => 1,
			'course_id' => $student['course_id'],
		))->result_array();

		$fee_percent = $student_fee > 0 ? round((($paid_val - $discountfee) / $student_fee) * 100, 1) : null;
		$remain_till = max(0, $should_val - $paid_val);

		$total_fine = $this->_scalar_fee($this->student->getStudentTotalFine($student_id));
		$removed_fine = $this->_scalar_fee($this->student->getStudentRemovedFine($student_id));
		$fine_should = $this->_scalar_fee($this->student->getStudentFineShouldPay($student_id));
		$fine_paid = $this->_scalar_fee($this->student->getStudentFinePaid($student_id));

		$this->_json(array(
			'success' => true,
			'data' => array(
				'student' => array_merge($student, array(
					'class_name' => $class ? $class['name'] : '',
					'session' => $class ? $class['session'] : (isset($student['session']) ? $student['session'] : ''),
					'course_name' => $course ? $course['course_name'] : '',
					'course_type' => $course ? $course['course_type'] : '',
					'campus_name' => isset($student['campus_name']) ? $student['campus_name'] : '',
				)),
				'plan' => $plan,
				'archive_type' => $archive_type,
				'is_active' => (int)$student['status'] === 1,
				'kpis' => array(
					'total_fees' => $total_fee,
					'admission_discount' => ($total_fee - $student_fee) - $specialdisc,
					'special_discount' => $specialdisc,
					'merged_discount' => $discountfee,
					'total_discount' => $total_fee - $student_fee + $discountfee,
					'student_fees' => $student_fee,
					'fee_created' => $created_val,
					'fee_not_created' => $student_fee - $created_val,
					'fee_should_pay' => $should_val,
					'fee_paid' => $paid_val - $discountfee,
					'remaining_fee_till_date' => $remain_till,
					'total_remaining_fee' => $this->_scalar_fee($remaining_fee),
					'fee_paid_percent' => $fee_percent,
					'total_fine' => $total_fine + $removed_fine,
					'removed_fine' => $removed_fine,
					'fine_paid' => $fine_paid,
					'remaining_fine_till_date' => max(0, $fine_should - $fine_paid),
					'total_remaining_fine' => max(0, $total_fine - $fine_paid),
					'council_fee_total' => $this->_scalar_fee($cf),
					'council_fee_should_pay' => $this->_scalar_fee($cf_should),
					'council_fee_paid' => $this->_scalar_fee($cf_paid),
					'council_fee_unpaid' => $this->_scalar_fee($cf_unpaid),
					'total_extra_fee' => $this->_scalar_fee($this->student->getStudentTotalExtraFee($student_id)),
					'extra_fee_paid_till_date' => $this->_scalar_fee($this->student->getStudentExtraFeePaidTillDate($student_id)),
					'extra_fee_remaining_till_date' => $this->_scalar_fee($this->student->getStudentExtraFeeRemainingTillDate($student_id)),
					'reversed_amount' => isset($reversed['reversal_amount']) ? (float)$reversed['reversal_amount'] : 0,
					'total_calls' => (int)$this->student->getStudentTotalCalls($student_id),
					'deleted_fees_count' => (int)$this->student->getCountDeletedFess($student_id),
					'shifted_fees_count' => (int)$this->student->getCountShiftedFess($student_id),
				),
				'payments' => $enriched,
				'deleted_payments' => $deleted,
				'old_plans' => $this->db->group_by('payment_id')->get_where('archive_payments', array('student_id' => $student_id))->result_array(),
				'account_numbers' => $this->db->get_where('accounts', array('type' => '1'))->result_array(),
				'campuses' => $campuses,
				'council_fees' => $this->db->get_where('council_sequence', array('course_id' => $student['course_id']))->result_array(),
				'council_sequences' => $this->db->get_where('council_sequence', array('course_id' => $student['course_id'], 'action_type' => 'fee'))->result_array(),
				'exam_sequences' => $exam_sequences,
				'payment_rules' => $payment_rules,
				'fee_rules_meta' => array(
					'disc_per_inst' => $plan && isset($plan['disc_per_inst']) ? (float)$plan['disc_per_inst'] : 0,
					'max_discount_merge' => $plan && isset($plan['max_discount_merge']) ? (float)$plan['max_discount_merge'] : 0,
					'plan_name' => $plan && isset($plan['payment_plan']) ? $plan['payment_plan'] : (isset($student['payment_plan']) ? $student['payment_plan'] : ''),
					'fine_per_day' => ($plan && isset($plan['payment_plan']) && $plan['payment_plan'] === '24 Installments') ? 10 : 50,
				),
				'permissions' => $this->_permissions(),
				'asset_base' => $this->_asset_base(),
			),
		));
	}

	private function _scalar_fee($v)
	{
		if ($v === null || $v === '') return 0;
		if (is_numeric($v)) return (float)$v;
		if (is_array($v)) {
			if (!count($v)) return 0;
			if (isset($v[0]) && is_array($v[0])) {
				$row = $v[0];
				foreach ($row as $x) {
					if (is_numeric($x)) return (float)$x;
				}
				return 0;
			}
			foreach ($v as $x) {
				if (is_numeric($x)) return (float)$x;
			}
		}
		return 0;
	}

	private function _enrich_payment_row($payment, $index, $student_id, $fine_per_day = 50)
	{
		$payment['_index'] = $index;
		$payment['is_special'] = in_array($payment['payment_comment'], array(
			'consulation fee', 'Extra Fee For Notes', 'Extra Fee For Books',
		), true);
		$payment['merged_children'] = array();
		if (!empty($payment['merged_challan']) && (float)$payment['actual_amount'] > 0 && !empty($payment['paid_challans'])) {
			$ids = array_filter(array_map('trim', explode(',', rtrim($payment['paid_challans'], ', '))));
			if (count($ids)) {
				$this->db->where_in('challan_no', $ids);
				$this->db->where('student_id', $student_id);
				$payment['merged_children'] = $this->db->order_by('payment_comment', 'DESC')->get('payments')->result_array();
			}
		}
		$payment['fees_remarks'] = array();
		if ($this->db->table_exists('fees_remarks')) {
			if ($this->db->field_exists('fee_remarks_id', 'fees_remarks')) {
				$this->db->order_by('fee_remarks_id', 'DESC');
			}
			$payment['fees_remarks'] = $this->db
				->get_where('fees_remarks', array('fee_id' => $payment['id']))
				->result_array();
		}
		$payment['update_requests'] = array();
		if ($this->db->table_exists('update_payment_requests')) {
			$payment['update_requests'] = $this->db
				->get_where('update_payment_requests', array('id' => $payment['id']))
				->result_array();
		}
		$today = new DateTime(date('Y-m-d'));
		$deadline = DateTime::createFromFormat('Y-m-d', $payment['dead_line']);
		$late_days = 0;
		if ($deadline && (int)$payment['paid'] === 0) {
			$diff = (int)$deadline->diff($today)->format('%r%a');
			if ($diff > 0) $late_days = $diff;
		}
		$payment['late_days'] = $late_days;
		$payment['computed_fine'] = $late_days > 0 ? $late_days * (int)$fine_per_day : 0;
		$campus = null;
		if (!empty($payment['submitted_fee_campus_id'])) {
			$campus = $this->db->get_where('campuses', array('campus_id' => $payment['submitted_fee_campus_id']))->row_array();
		}
		$payment['submitted_campus_name'] = $campus ? $campus['campus_name'] : '';
		return $payment;
	}

	public function documents($student_id = 0)
	{
		$student_id = (int)$student_id;
		$student = $this->db->get_where('students', array('student_id' => $student_id))->row_array();
		if (!$student) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		$docs = $this->db->get_where('student_documents', array('student_id' => $student_id))->result_array();
		foreach ($docs as &$d) {
			$d['url'] = $this->_doc_url($d);
		}
		$this->_json(array(
			'success' => true,
			'data' => array(
				'student' => $student,
				'documents' => $docs,
				'types' => array(
					'ID Card', 'B - FORM', 'Photo', 'Result Card', 'Signature', 'Thumb',
					'College Form', 'Rules and Regulation Form', 'Fee Structure Form', 'Other',
				),
			),
			'legacy_base' => $this->_legacy_base(),
		));
	}

	public function purchased($student_id = 0)
	{
		$student_id = (int)$student_id;
		$rows = array();
		if ($this->db->table_exists('orders')) {
			$this->db->from('orders');
			$this->db->where('student_id', $student_id);
			$this->db->order_by('order_id', 'DESC');
			$rows = $this->db->get()->result_array();
		}
		$this->_json(array(
			'success' => true,
			'data' => $rows,
			'legacy_base' => $this->_legacy_base(),
		));
	}

	// ─── Action screens (SPA — no legacy redirect) ───────────

	private function _student_profile($student_id)
	{
		$this->db->select('students.*, classes.name as class_name, classes.session, classes.freeze_fee, campuses.campus_id, campuses.campus_name, campuses.address as campus_address, campuses.phone, campuses.phone1, campuses.phone2, campuses.logo, campuses.stamp, campuses.website, courses.course_name, machine_data.machine_id', false);
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'left');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
		$this->db->join('courses', 'courses.course_id=students.course_id', 'left');
		$this->db->join('machine_data', 'machine_data.teacher_student_id=students.student_id AND machine_data.type="student"', 'left');
		$this->db->where('students.student_id', (int)$student_id);
		return $this->db->get()->row_array();
	}

	private function _fee_summary($student_id)
	{
		$paid = $this->db->order_by('paid_date', 'ASC')->get_where('payments', array('student_id' => (int)$student_id, 'paid' => 1))->result_array();
		$unpaid = $this->db->order_by('dead_line', 'ASC')->get_where('payments', array('student_id' => (int)$student_id, 'paid' => 0))->result_array();
		$paid_total = 0;
		$unpaid_total = 0;
		foreach ($paid as $p) $paid_total += (float)$p['actual_amount'];
		foreach ($unpaid as $p) $unpaid_total += (float)$p['amount'];
		return array(
			'paid' => $paid,
			'unpaid' => $unpaid,
			'paid_total' => $paid_total,
			'unpaid_total' => $unpaid_total,
		);
	}

	private function _new_challan_no()
	{
		for ($i = 0; $i < 20; $i++) {
			$n = (string)mt_rand(1000, 999999999);
			$exists = $this->db->get_where('payments', array('challan_no' => $n))->row_array();
			if (!$exists) return $n;
		}
		return (string)time() . mt_rand(10, 99);
	}

	public function attendance($student_id = 0)
	{
		$student_id = (int)$student_id;
		$student = $this->_student_profile($student_id);
		if (!$student) $this->_json(array('success' => false, 'message' => 'Student not found'), 404);

		$start = $this->input->get('start_date');
		$end = $this->input->get('end_date');
		if (!$start) $start = date('Y-m-d', strtotime('-7 days'));
		if (!$end) $end = date('Y-m-d');

		$days = array();
		$machine_id = isset($student['machine_id']) ? $student['machine_id'] : null;
		try {
			$period = new DatePeriod(
				new DateTime($start),
				new DateInterval('P1D'),
				(new DateTime($end))->modify('+1 day')
			);
			foreach ($period as $dt) {
				$d = $dt->format('Y-m-d');
				$times = array();
				$status = 'Absent';
				$first_time = null;
				if ($machine_id !== null && $machine_id !== '' && $this->db->table_exists('attendence')) {
					$rows = $this->db
						->where('machine_user_id', $machine_id)
						->where('time >=', $d . ' 00:00:00')
						->where('time <=', $d . ' 23:59:59')
						->order_by('time', 'ASC')
						->get('attendence')
						->result_array();
					if (count($rows)) {
						$status = 'Present';
						$first_time = $rows[0]['time'];
						foreach ($rows as $r) $times[] = $r['time'];
					}
				}
				$days[] = array(
					'date' => $d,
					'status' => $status,
					'time' => $first_time,
					'times' => $times,
				);
			}
		} catch (Exception $e) {
			$days = array();
		}

		$photo = $this->db->get_where('student_documents', array('student_id' => $student_id, 'type' => 'Photo'))->row_array();
		$this->_json(array(
			'success' => true,
			'data' => array(
				'student' => $student,
				'image_url' => $this->_doc_url($photo),
				'start_date' => $start,
				'end_date' => $end,
				'days' => $days,
			),
		));
	}

	public function sms($student_id = 0)
	{
		$student_id = (int)$student_id;
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$body = $this->_body();
			$message = isset($body['message']) ? trim($body['message']) : '';
			if ($message === '') $this->_json(array('success' => false, 'message' => 'Message required'), 422);
			$student = $this->db->get_where('students', array('student_id' => $student_id))->row_array();
			if (!$student) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
			$numbers = array();
			if (!empty($student['mobile'])) $numbers[] = $student['mobile'];
			if (!empty($student['emergency_no'])) $numbers[] = $student['emergency_no'];
			foreach ($numbers as $num) {
				$this->db->insert('sms', array(
					'number' => $num,
					'message' => $message,
					'status' => '',
					'date' => date('Y-m-d H:i:s'),
					'chk' => '0',
				));
			}
			$this->_json(array('success' => true, 'message' => 'SMS queued to student + emergency'));
		}

		$student = $this->_student_profile($student_id);
		if (!$student) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		$msgs = array();
		if ($this->db->table_exists('sms')) {
			$this->db->from('sms');
			$this->db->group_start();
			if (!empty($student['mobile'])) $this->db->or_where('number', $student['mobile']);
			if (!empty($student['emergency_no'])) $this->db->or_where('number', $student['emergency_no']);
			$this->db->group_end();
			$this->db->order_by('date', 'DESC');
			$this->db->limit(200);
			$msgs = $this->db->get()->result_array();
		}
		$this->_json(array(
			'success' => true,
			'data' => array(
				'student' => $student,
				'messages' => $msgs,
			),
		));
	}

	public function freeze($student_id = 0)
	{
		$student_id = (int)$student_id;
		if (!$this->_perm('can_student_struckof') && !$this->_is_admin()) {
			$this->_json(array('success' => false, 'message' => 'No permission'), 403);
		}

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$body = $this->_body();
			$reason = isset($body['reason']) ? trim($body['reason']) : '';
			$fee = isset($body['fee']) ? (float)$body['fee'] : 0;
			$from_date = isset($body['from_date']) ? trim($body['from_date']) : date('Y-m-d');
			$campus_id = isset($body['campus_id']) ? (int)$body['campus_id'] : 0;
			if ($reason === '') $this->_json(array('success' => false, 'message' => 'Reason required'), 422);

			$pending = $this->db->get_where('update_student_requests', array('student_id' => $student_id, 'ok_by_admin' => 0))->row_array();
			if ($pending) {
				$this->_json(array('success' => false, 'message' => 'Pending update request already exists for this student'), 422);
			}

			$student = $this->db->get_where('students', array('student_id' => $student_id))->row_array();
			if (!$student) $this->_json(array('success' => false, 'message' => 'Not found'), 404);

			$challan = $this->_new_challan_no();
			$this->db->insert('payments', array(
				'amount' => $fee,
				'actual_amount' => $fee,
				'dead_line' => date('Y-m-d'),
				'student_id' => $student_id,
				'payment_plan' => 'Custom Plan',
				'payment_comment' => 'Freeze Payment Fee',
				'challan_no' => $challan,
				'paid' => '1',
				'paid_date' => date('Y-m-d'),
				'actual_paid_date' => date('Y-m-d'),
				'fee_pay_through' => 'college',
				'fee_submit_type' => 'computer_challan',
				'submitted_fee_campus_id' => $campus_id,
				'add_by' => $this->_actor_name(),
				'last_edit' => $this->_actor_name(),
			));
			$payment_id = (int)$this->db->insert_id();

			if ($this->db->table_exists('freeze_student')) {
				$this->db->insert('freeze_student', array(
					'student_id' => $student_id,
					'reason' => $reason,
					'fee_amount' => $fee,
					'rejoin_date' => $from_date,
					'image_proof' => '',
					'challan_id' => $payment_id,
					'created_by' => $this->_actor_name(),
				));
			}

			$this->db->insert('deleted_students', array(
				'delete_type' => 'Freeze',
				'student_id' => $student_id,
				'date' => date('Y-m-d H:i:s'),
				'deleted_by' => $this->_actor_name(),
				'reason' => $reason,
				'reason_detail' => 'Freeze Paid Amount : ' . $fee,
				'image' => '',
				'status' => 1,
			));
			$this->db->where('student_id', $student_id)->update('students', array('status' => 0));

			$this->_json(array('success' => true, 'message' => 'Student frozen'));
		}

		$student = $this->_student_profile($student_id);
		if (!$student) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		$fee = $this->_fee_summary($student_id);
		$history = array();
		if ($this->db->table_exists('freeze_student')) {
			$history = $this->db->order_by('id', 'DESC')->get_where('freeze_student', array('student_id' => $student_id))->result_array();
		}
		$campuses = array();
		if ($this->db->table_exists('campus_rules')) {
			$this->db->select('campuses.*');
			$this->db->from('campus_rules');
			$this->db->join('campuses', 'campuses.campus_id=campus_rules.campus_id', 'inner');
			$this->db->where('campus_rules.college_fee', 1);
			$campuses = $this->db->get()->result_array();
		}
		if (!count($campuses)) {
			$campuses = $this->db->where('status', 1)->get('campuses')->result_array();
		}
		$this->_json(array(
			'success' => true,
			'data' => array(
				'student' => $student,
				'fee' => $fee,
				'history' => $history,
				'campuses' => $campuses,
				'default_fee' => isset($student['freeze_fee']) ? (float)$student['freeze_fee'] : 0,
			),
		));
	}

	public function struckof($student_id = 0)
	{
		$student_id = (int)$student_id;
		if (!$this->_perm('can_student_struckof') && !$this->_is_admin()) {
			$this->_json(array('success' => false, 'message' => 'No permission'), 403);
		}

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$body = $this->_body();
			$reason = isset($body['reason']) ? trim($body['reason']) : '';
			$from_no = isset($body['from_no']) ? trim($body['from_no']) : '';
			$to_no = isset($body['to_no']) ? trim($body['to_no']) : '';
			$amount = isset($body['amount']) ? (float)$body['amount'] : 0;
			$immediate = !empty($body['immediate']);
			if ($reason === '') $this->_json(array('success' => false, 'message' => 'Reason required'), 422);

			$student = $this->db->get_where('students', array('student_id' => $student_id))->row_array();
			if (!$student) $this->_json(array('success' => false, 'message' => 'Not found'), 404);

			$process_count = 0;
			if ($this->db->table_exists('struckof_procedures')) {
				$existing = $this->db->get_where('struckof_procedures', array('student_id' => $student_id))->result_array();
				$process_count = count($existing);
				$this->db->insert('struckof_procedures', array(
					'student_id' => $student_id,
					'process_count' => $process_count,
					'reason' => $reason,
					'action_type' => $immediate ? 'immediate' : 'process',
					'status' => $immediate ? 'complete' : 'pending',
					'created_by' => $this->_actor_name(),
					'created_at' => date('Y-m-d H:i:s'),
					'approval_by' => $immediate ? $this->_actor_name() : '',
				));
			}

			if ($this->db->table_exists('struckofdetails_students')) {
				$this->db->insert('struckofdetails_students', array(
					'student_id' => $student_id,
					'process_count' => $process_count,
					'from_no' => $from_no,
					'to_no' => $to_no,
					'amount' => $amount,
					'proof_image' => '',
					'status' => $immediate ? '1' : '0',
					'updated_by' => $immediate ? $this->_actor_name() : '',
				));
			}

			if ($immediate) {
				$this->db->insert('deleted_students', array(
					'delete_type' => 'Struck Of',
					'student_id' => $student_id,
					'date' => date('Y-m-d H:i:s'),
					'deleted_by' => $this->_actor_name(),
					'reason' => $reason,
					'reason_detail' => $reason,
					'refund_amount' => $amount,
					'image' => '',
					'status' => '1',
					'approve_by' => $this->_actor_name(),
				));
				$this->db->where('student_id', $student_id)->update('students', array('status' => 0));
			}

			$this->_json(array(
				'success' => true,
				'message' => $immediate ? 'Student struck off immediately' : 'Struck-off process started',
			));
		}

		$student = $this->_student_profile($student_id);
		if (!$student) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		$fee = $this->_fee_summary($student_id);
		$history = array();
		if ($this->db->table_exists('struckof_procedures')) {
			$history = $this->db->order_by('process_count', 'DESC')
				->get_where('struckof_procedures', array('student_id' => $student_id))
				->result_array();
		}
		$this->_json(array(
			'success' => true,
			'data' => array(
				'student' => $student,
				'fee' => $fee,
				'history' => $history,
			),
		));
	}

	public function restore($student_id = 0)
	{
		$student_id = (int)$student_id;
		if (!$this->_is_admin() && !$this->_perm('can_student_struckof')) {
			$this->_json(array('success' => false, 'message' => 'No permission'), 403);
		}
		$student = $this->db->get_where('students', array('student_id' => $student_id))->row_array();
		if (!$student) $this->_json(array('success' => false, 'message' => 'Not found'), 404);

		$pending = $this->db->get_where('update_student_requests', array('student_id' => $student_id, 'ok_by_admin' => 0))->row_array();
		if ($pending) {
			$this->_json(array('success' => false, 'message' => 'Restore request already pending'), 422);
		}

		$data = $student;
		$data['status'] = 1;
		unset($data['student_id']);
		$data['student_id'] = $student_id;
		$this->db->insert('update_student_requests', $data);
		$this->_json(array('success' => true, 'message' => 'Restore request submitted'));
	}

	public function revive($student_id = 0)
	{
		$student_id = (int)$student_id;
		if (!$this->_perm('can_student_struckof') && !$this->_is_admin()) {
			$this->_json(array('success' => false, 'message' => 'No permission'), 403);
		}

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$body = $this->_body();
			$fee = isset($body['fee']) ? (float)$body['fee'] : 0;
			$campus_id = isset($body['campus_id']) ? (int)$body['campus_id'] : 0;
			$student = $this->db->get_where('students', array('student_id' => $student_id))->row_array();
			if (!$student) $this->_json(array('success' => false, 'message' => 'Not found'), 404);

			$paid_count = $this->db->where(array('student_id' => $student_id, 'paid' => 1))->count_all_results('payments');
			if ($paid_count > 0 && $fee > 0) {
				$this->db->insert('payments', array(
					'amount' => $fee,
					'actual_amount' => $fee,
					'dead_line' => date('Y-m-d'),
					'student_id' => $student_id,
					'payment_plan' => 'Custom Plan',
					'payment_comment' => 'Re-Admission Fee',
					'challan_no' => $this->_new_challan_no(),
					'paid' => '1',
					'paid_date' => date('Y-m-d'),
					'actual_paid_date' => date('Y-m-d'),
					'fee_pay_through' => 'college',
					'fee_submit_type' => 'computer_challan',
					'submitted_fee_campus_id' => $campus_id,
					'add_by' => $this->_actor_name(),
					'last_edit' => $this->_actor_name(),
				));
			} elseif ($paid_count < 1) {
				$this->db->where('student_id', $student_id)->delete('payments');
			}

			$this->db->where('student_id', $student_id)->update('students', array('status' => 1));
			if ($this->db->table_exists('deleted_students')) {
				$this->db->where(array('student_id' => $student_id, 'status' => 1))->update('deleted_students', array('status' => 0));
			}
			if ($this->db->table_exists('freeze_student')) {
				$this->db->where('student_id', $student_id)->delete('freeze_student');
			}
			$this->_json(array('success' => true, 'message' => 'Student re-admitted'));
		}

		$student = $this->_student_profile($student_id);
		if (!$student) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		$campuses = $this->db->where('status', 1)->get('campuses')->result_array();
		$this->_json(array(
			'success' => true,
			'data' => array(
				'student' => $student,
				'campuses' => $campuses,
				'default_fee' => isset($student['freeze_fee']) ? (float)$student['freeze_fee'] : 0,
			),
		));
	}

	public function all_docs($student_id = 0)
	{
		$student_id = (int)$student_id;
		$student = $this->_student_profile($student_id);
		if (!$student) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		$types = array('Photo', 'ID Card', 'B - FORM', 'Result Card', 'Signature', 'Thumb');
		$docs = array();
		foreach ($types as $t) {
			$rows = $this->db->get_where('student_documents', array('student_id' => $student_id, 'type' => $t))->result_array();
			foreach ($rows as &$r) $r['url'] = $this->_doc_url($r);
			$docs[$t] = $rows;
		}
		$this->_json(array(
			'success' => true,
			'data' => array(
				'student' => $student,
				'documents' => $docs,
				'asset_base' => $this->_asset_base(),
			),
		));
	}

	public function generate_docs_list()
	{
		$body = $this->_body();
		$class_id = isset($body['class_id']) ? (int)$body['class_id'] : 0;
		if (!$class_id) $this->_json(array('success' => false, 'message' => 'class_id required'), 422);

		$sql = 'SELECT student_id, roll_no, cnic, gender, first_name, last_name, father_name, address, mobile, emergency_no, board
			FROM students WHERE class_id=? AND status=1 ORDER BY CAST(roll_no as SIGNED INTEGER) ASC';
		$students = $this->db->query($sql, array($class_id))->result_array();
		$ids = array_map(function ($s) { return (int)$s['student_id']; }, $students);
		$docs_by = $this->_docs_by_student($ids);

		$out = array();
		foreach ($students as $s) {
			$sid = (int)$s['student_id'];
			$docs = isset($docs_by[$sid]) ? $docs_by[$sid] : array();
			$has_id = !empty($docs['ID Card']) || !empty($docs['B - FORM']);
			$out[] = array(
				'student_id' => $sid,
				'roll_no' => $s['roll_no'],
				'name' => trim($s['first_name'] . ' ' . $s['last_name']),
				'father_name' => $s['father_name'],
				'gender' => $s['gender'],
				'cnic' => $s['cnic'],
				'address' => $s['address'],
				'mobile' => $s['mobile'],
				'documents' => array(
					'photo' => !empty($docs['Photo']),
					'id_card' => $has_id,
					'result_card' => !empty($docs['Result Card']),
				),
				'print_url' => '/students/all-docs/' . $sid,
			);
		}

		$this->_json(array(
			'success' => true,
			'data' => $out,
			'count' => count($out),
			'class_id' => $class_id,
			'downloads' => array(
				'csv' => true,
			),
		));
	}

	public function generate_docs_csv()
	{
		$body = $this->_body();
		$class_id = isset($body['class_id']) ? (int)$body['class_id'] : 0;
		if (!$class_id) $this->_json(array('success' => false, 'message' => 'class_id required'), 422);
		$sql = 'SELECT student_id, roll_no, cnic, gender, first_name, last_name, father_name, address
			FROM students WHERE class_id=? AND status=1 ORDER BY CAST(roll_no as SIGNED INTEGER) ASC';
		$students = $this->db->query($sql, array($class_id))->result_array();
		$rows = array();
		$i = 1;
		foreach ($students as $s) {
			$rel = ($s['gender'] === 'Female') ? 'D/O' : 'S/O';
			$name = ucwords(strtolower(trim($s['first_name'] . ' ' . $s['last_name']))) . ' ' . $rel . ' ' . ucwords(strtolower($s['father_name']));
			$rows[] = array(
				'sr' => $i++,
				'name' => $name,
				'cnic' => $s['cnic'],
				'address' => $s['address'],
				'roll_no' => $s['roll_no'],
			);
		}
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function campuses_for_course()
	{
		$course_id = (int)$this->input->get('course_id');
		if (!$course_id) $this->_json(array('success' => true, 'data' => array()));
		$course = $this->db->get_where('courses', array('course_id' => $course_id))->row_array();
		if (!$course || empty($course['campus_ids'])) $this->_json(array('success' => true, 'data' => array()));
		$ids = array_filter(array_map('intval', explode(',', $course['campus_ids'])));
		if (!count($ids)) $this->_json(array('success' => true, 'data' => array()));
		$rows = $this->db->where_in('campus_id', $ids)->order_by('campus_name', 'ASC')->get('campuses')->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function struck_letters()
	{
		$body = $this->_body();
		$ids = isset($body['student_ids']) ? $body['student_ids'] : array();
		if (is_string($ids)) $ids = array_filter(array_map('intval', explode(',', $ids)));
		if (!is_array($ids) || !count($ids)) {
			$this->_json(array('success' => false, 'message' => 'student_ids required'), 422);
		}
		$ids = array_map('intval', $ids);
		$this->db->select('students.*, classes.name as class_name, campuses.campus_name, campuses.address as campus_address, campuses.phone, campuses.stamp');
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'left');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
		$this->db->where_in('students.student_id', $ids);
		$rows = $this->db->get()->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	// ─── Payments console actions (SPA — no legacy UI) ───────

	public function payments_verify_fee()
	{
		$body = $this->_body();
		$tid = isset($body['tid']) ? preg_replace('/\s+/', '', trim($body['tid'])) : '';
		$bank = isset($body['bank']) ? $body['bank'] : '';
		$date = isset($body['paid_date']) ? $body['paid_date'] : '';
		$amount = isset($body['amount']) ? (float)$body['amount'] : 0;
		if ($tid === '' || $bank === '' || $date === '') {
			$this->_json(array('success' => false, 'message' => 'tid, bank, paid_date required'), 422);
		}
		$account = $this->db->get_where('accounts', array('account_name' => $bank))->row_array();
		if (!$account) $this->_json(array('success' => false, 'message' => 'Bank account not found'), 404);
		$acountid = $account['id'];
		$qry = "SELECT * FROM bank_reconciliation_statement WHERE account_id= ".$this->db->escape($acountid)." AND trans_date= ".$this->db->escape($date)." AND description LIKE '%".$this->db->escape_like_str($tid)."%' LIMIT 1";
		$transaction = $this->db->query($qry)->result_array();
		if (!count($transaction)) {
			$this->_json(array('success' => false, 'message' => 'Not Found in Statement', 'code' => 'not_found'));
		}
		$amount_tagged = (float)$transaction[0]['tagged_amount'];
		$credit_amount = (float)str_replace(array(',', '.00'), '', $transaction[0]['credit']);
		if (($credit_amount - $amount_tagged) >= $amount) {
			$this->_json(array('success' => true, 'message' => 'success', 'statement_id' => (int)$transaction[0]['id']));
		}
		$device = $this->db->get_where('payments', array('statement_id' => $transaction[0]['id']))->row_array();
		$this->_json(array(
			'success' => false,
			'message' => 'Already tagged',
			'code' => 'already_found',
			'challan_no' => $device ? $device['challan_no'] : null,
			'student_id' => $device ? (int)$device['student_id'] : null,
		));
	}

	public function payments_pay($student_id = 0)
	{
		$student_id = (int)$student_id;
		$this->_require_student_payments();
		$body = $this->_body();
		// Also accept multipart form fields
		if (!count($body)) $body = $this->input->post() ? $this->input->post() : array();

		$fee_ids_raw = isset($body['fee_ids']) ? $body['fee_ids'] : '';
		if (is_array($fee_ids_raw)) {
			$payment_ids = array_map('intval', $fee_ids_raw);
		} else {
			$fee_ids_raw = ltrim((string)$fee_ids_raw, ',');
			$payment_ids = array_filter(array_map('intval', explode(',', $fee_ids_raw)));
		}
		if (!count($payment_ids)) $this->_json(array('success' => false, 'message' => 'fee_ids required'), 422);

		$pay_through = isset($body['fee_pay_through']) ? $body['fee_pay_through'] : 'college';
		$this->_assert_pay_through($pay_through);

		if (
			(!empty($body['prev_fine_status']) && $body['prev_fine_status'] === 'remove')
			|| (!empty($body['late_fee_fine_status']) && $body['late_fee_fine_status'] === 'remove')
		) {
			if (!$this->_perm('fine_remove') && !$this->_perm('remove_fine')) {
				$this->_json(array('success' => false, 'message' => 'No permission to remove fine'), 403);
			}
		}

		$scan_challan = $this->_upload_field('scan_challan');
		$fine_application = $this->_upload_field('fine_application');

		$merged_challan = count($payment_ids) > 1 ? $this->_new_challan_no() : null;
		$newfeegenerated = 0;
		$newfeeshifted = 0;
		$actor = $this->_actor_name();
		$i = 0;

		foreach ($payment_ids as $payment_id) {
			$next_payment_id = $this->student->getNextPaymentId($payment_id, $student_id);
			if (
				(isset($body['prev_installment_status']) && $body['prev_installment_status'] === 'shift')
				|| (isset($body['prev_fine_status']) && $body['prev_fine_status'] === 'shift')
				|| (isset($body['late_fee_fine_status']) && $body['late_fee_fine_status'] === 'shift')
			) {
				if (!is_array($next_payment_id) || !count($next_payment_id)) {
					$this->_json(array('success' => false, 'message' => 'No remaining installment to shift dues'), 422);
				}
			}

			$shifted_installment = 0;
			$new_installment = 0;
			$removed_previous_fine = 0;
			$shifted_previous_fine = 0;
			$new_previous_fine = 0;
			$removed_fine = 0;
			$shifted_fine = 0;
			$new_fine = 0;

			if (!empty($body['split_remaining_installment_amount'])) {
				if (@$body['prev_installment_status'] === 'shift') $shifted_installment = (float)@$body['remove_remaining_installment_amount'];
				if (@$body['prev_installment_status'] === 'new') $new_installment = (float)@$body['remove_remaining_installment_amount'];
			}
			if (!empty($body['split_remaining_fine_amount'])) {
				if (@$body['prev_fine_status'] === 'remove') $removed_previous_fine = (float)@$body['remove_previous_fine_amount'];
				if (@$body['prev_fine_status'] === 'shift') $shifted_previous_fine = (float)@$body['remove_previous_fine_amount'];
				if (@$body['prev_fine_status'] === 'new') $new_previous_fine = (float)@$body['remove_previous_fine_amount'];
			}
			if (!empty($body['split_fine_amount'])) {
				if (@$body['late_fee_fine_status'] === 'remove') $removed_fine = (float)@$body['remove_fine_amount'];
				if (@$body['late_fee_fine_status'] === 'shift') $shifted_fine = (float)@$body['remove_fine_amount'];
				if (@$body['late_fee_fine_status'] === 'new') $new_fine = (float)@$body['remove_fine_amount'];
			}

			$data = array(
				'scan_challan' => $scan_challan,
				'merged_challan' => $merged_challan,
				'paid_challans' => isset($body['challans']) ? $body['challans'] : '',
				'fine_application' => $fine_application,
				'actual_amount' => isset($body['actual_amount']) ? $body['actual_amount'] : 0,
				'discount' => isset($body['discount']) ? $body['discount'] : 0,
				'id' => $payment_id,
				'paid_date' => isset($body['paid_date']) ? $body['paid_date'] : date('Y-m-d'),
				'paid' => 1,
				'actual_paid_date' => date('Y-m-d'),
				'college_fee' => 0,
				'last_edit' => $actor,
				'paid_by' => $actor,
				'fee_pay_through' => $pay_through,
				'fine_amount' => isset($body['fine_amount']) ? $body['fine_amount'] : 0,
				'shifted_installment' => $shifted_installment + $new_installment,
				'removed_previous_fine' => $removed_previous_fine,
				'shifted_previous_fine' => $shifted_previous_fine + $new_previous_fine,
				'removed_fine' => $removed_fine,
				'submitted_fee_campus_id' => isset($body['submitted_fee_campus_id']) ? $body['submitted_fee_campus_id'] : 0,
				'shifted_fine' => $shifted_fine + $new_fine,
				'closing_id' => NULL,
			);

			if ($pay_through === 'bank') {
				$account = $this->db->get_where('accounts', array('account_name' => @$body['bank_details']))->row_array();
				$statement_id = null;
				if ($account && $i === 0) {
					$acountid = $account['id'];
					$this->db->select('*');
					$this->db->from('bank_reconciliation_statement');
					$this->db->where('account_id', $acountid);
					$this->db->where('trans_date', $body['paid_date']);
					$this->db->group_start();
					$this->db->like('description', @$body['tid_no']);
					$this->db->or_like('reference_no', @$body['tid_no']);
					$this->db->group_end();
					$this->db->group_by('description');
					$concile = $this->db->get()->result_array();
					if (count($concile)) {
						$statement_id = $concile[0]['id'];
						$this->db->set('tagged_amount', 'tagged_amount +' . (float)$body['actual_amount'], false);
						$this->db->where('id', $statement_id);
						$this->db->update('bank_reconciliation_statement');
					}
				}
				$data['bank_details'] = @$body['bank_details'];
				$data['tid_no'] = @$body['tid_no'];
				$data['statement_id'] = $statement_id;
			} elseif ($pay_through === 'college') {
				$data['fee_submit_type'] = isset($body['fee_submit_type']) ? $body['fee_submit_type'] : 'computer_challan';
				if ($data['fee_submit_type'] === 'receipt_book') {
					$data['book_no'] = @$body['book_no'];
					$data['receipt_no'] = @$body['receipt_no'];
				}
			}

			$this->student->saveInstallment($data);

			if (($shifted_installment > 0 || $shifted_previous_fine > 0 || $shifted_fine > 0) && $newfeeshifted == 0 && is_array($next_payment_id) && count($next_payment_id)) {
				$npid = $next_payment_id[0]['id'];
				$current_challan = $this->db->get_where('payments', array('id' => $payment_id))->row()->challan_no;
				$this->student->addExtraChargesToNextInstallment(
					$npid,
					$shifted_previous_fine + $shifted_fine,
					$shifted_installment,
					$current_challan
				);
				$newfeeshifted = 1;
			}
			if (($new_installment > 0 || $new_previous_fine > 0 || $new_fine > 0) && $newfeegenerated == 0) {
				$current_challan = $this->db->get_where('payments', array('id' => $payment_id))->row()->challan_no;
				$this->student->addExtraChargesToNewInstallment(
					$student_id,
					isset($body['new_dead_line']) ? $body['new_dead_line'] : date('Y-m-d'),
					$new_installment,
					$new_previous_fine,
					$new_fine,
					$current_challan
				);
				$newfeegenerated = 1;
			}
			$i++;
		}

		$this->_json(array('success' => true, 'message' => 'Fee submitted successfully'));
	}

	private function _upload_field($field)
	{
		if (empty($_FILES[$field]['name'])) return '';
		$this->load->library('upload');
		$config = array(
			'upload_path' => 'uploads/',
			'allowed_types' => '*',
			'encrypt_name' => true,
		);
		$this->upload->initialize($config);
		if (!$this->upload->do_upload($field)) return '';
		$d = $this->upload->data();
		return $d['file_name'];
	}

	public function payments_split($student_id = 0)
	{
		$this->_require_student_payments();
		$student_id = (int)$student_id;
		$body = $this->_body();
		$current_id = isset($body['current_id']) ? (int)$body['current_id'] : 0;
		$is_council = !empty($body['consultation']);
		if (!$current_id) $this->_json(array('success' => false, 'message' => 'current_id required'), 422);

		$current = $this->db->get_where('payments', array('id' => $current_id, 'student_id' => $student_id))->row_array();
		if (!$current || (int)$current['paid'] === 1) {
			$this->_json(array('success' => false, 'message' => 'Invalid installment'), 422);
		}

		$new_amount = (float)@$body['new_amount'];
		$current_amount = (float)@$body['current_amount'];
		if ($current_amount < ((float)$current['amount'] * 0.2)) {
			$this->_json(array('success' => false, 'message' => 'Current amount must keep at least 20%'), 422);
		}

		$this->db->where('id', $current_id)->update('payments', array(
			'amount' => $current_amount,
			'remaining_installment_amount' => (float)@$body['current_remaining_installment_amount'],
			'extra_amount' => (float)@$body['current_extra_amount'],
			'split' => 1,
		));

		$challan = $this->_new_challan_no();
		$this->db->insert('payments', array(
			'student_id' => $student_id,
			'challan_no' => $challan,
			'amount' => $new_amount,
			'remaining_installment_amount' => (float)@$body['new_remaining_installment_amount'],
			'extra_amount' => (float)@$body['new_extra_amount'],
			'dead_line' => isset($body['new_dead_line']) ? $body['new_dead_line'] : $current['dead_line'],
			'payment_plan' => $current['payment_plan'],
			'payment_comment' => $current['payment_comment'],
			'paid' => 0,
			'contract_id' => $current['contract_id'],
			'council_sequence_id' => $current['council_sequence_id'],
			'exam_sequence_id' => $current['exam_sequence_id'],
			'split' => 1,
			'add_by' => $this->_actor_name(),
			'last_edit' => $this->_actor_name(),
		));

		$this->_json(array('success' => true, 'message' => $is_council ? 'Council fee split' : 'Installment split'));
	}

	public function payments_delete($student_id = 0, $payment_id = 0)
	{
		if (!$this->_is_admin()) $this->_json(array('success' => false, 'message' => 'Admin only'), 403);
		$student_id = (int)$student_id;
		$payment_id = (int)$payment_id;
		$row = $this->db->get_where('payments', array('id' => $payment_id, 'student_id' => $student_id))->row_array();
		if (!$row) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		if ((int)$row['paid'] === 1) $this->_json(array('success' => false, 'message' => 'Cannot delete paid installment'), 422);
		$this->db->where('id', $payment_id)->delete('payments');
		$this->db->where('id', $payment_id)->delete('update_payment_requests');
		$this->_json(array('success' => true, 'message' => 'Fee deleted'));
	}

	public function payments_comment($student_id = 0)
	{
		$this->_require_student_payments();
		$body = $this->_body();
		$payment_id = isset($body['payment_id']) ? (int)$body['payment_id'] : 0;
		$row = $this->db->get_where('payments', array('id' => $payment_id, 'student_id' => (int)$student_id))->row_array();
		if (!$row) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		$changing_exam = isset($body['exam_sequence_id']) || isset($body['council_sequence_id']) || isset($body['payment_comment']);
		if ($changing_exam && !$this->_perm('change_exam_no_in_payments')) {
			$this->_json(array('success' => false, 'message' => 'No permission to change exam/council comment'), 403);
		}
		if ($changing_exam && isset($row['payment_comment']) && $row['payment_comment'] === 'College Fee') {
			$this->_json(array('success' => false, 'message' => 'Cannot change College Fee exam comment'), 422);
		}
		$upd = array();
		if (isset($body['exam_sequence_id'])) $upd['exam_sequence_id'] = $body['exam_sequence_id'];
		if (isset($body['council_sequence_id'])) $upd['council_sequence_id'] = $body['council_sequence_id'];
		if (isset($body['payment_comment'])) $upd['payment_comment'] = $body['payment_comment'];
		if (count($upd)) $this->db->where('id', $payment_id)->update('payments', $upd);
		if (!empty($body['reason']) && $this->db->table_exists('fees_remarks')) {
			$this->db->insert('fees_remarks', array(
				'fee_id' => $payment_id,
				'comment' => $body['reason'],
				'paid_on_date' => date('Y-m-d'),
				'clear_status' => 0,
				'add_by' => $this->_actor_name(),
			));
		}
		$this->_json(array('success' => true, 'message' => 'Updated'));
	}

	public function payments_session_fee($student_id = 0)
	{
		if (!$this->_is_admin()) $this->_json(array('success' => false, 'message' => 'Admin only'), 403);
		$body = $this->_body();
		$fee = isset($body['current_session_fee']) ? (float)$body['current_session_fee'] : -1;
		if ($fee < 0) $this->_json(array('success' => false, 'message' => 'current_session_fee required'), 422);
		$this->db->where('student_id', (int)$student_id)->update('students', array('current_session_fee' => $fee));
		$this->_json(array('success' => true, 'message' => 'Session fee updated'));
	}

	public function payments_discount($student_id = 0)
	{
		$this->_require_student_payments();
		$body = $this->_body();
		$amount = isset($body['discount']) ? (float)$body['discount'] : 0;
		$reason = isset($body['reason']) ? trim($body['reason']) : '';
		$remaining = isset($body['remaining_fee']) ? (float)$body['remaining_fee'] : 0;
		if ($amount <= 0) $this->_json(array('success' => false, 'message' => 'discount required'), 422);

		$pending = $this->db->get_where('discounts_approval', array(
			'student_id' => (int)$student_id,
			'status' => '0',
		))->result_array();
		if (count($pending)) {
			$this->_json(array('success' => false, 'message' => 'Fee discount request already pending'), 422);
		}

		$application = $this->_upload_field('image');
		if (!$application) $application = $this->_upload_field('application');

		$this->db->insert('discounts_approval', array(
			'student_id' => (int)$student_id,
			'remaining_fee' => $remaining,
			'discount' => $amount,
			'reason' => $reason,
			'application' => $application,
			'status' => 0,
			'created_by' => $this->_actor_name(),
			'created_at' => date('Y-m-d H:i:s'),
		));
		$this->_json(array('success' => true, 'message' => 'Discount request submitted'));
	}

	public function payments_extra_fee($student_id = 0)
	{
		$this->_require_student_payments();
		$body = $this->_body();
		$amount = isset($body['amount']) ? (float)$body['amount'] : 0;
		$comment = isset($body['payment_comment']) ? $body['payment_comment'] : 'Extra Fee';
		$deadline = isset($body['dead_line']) ? $body['dead_line'] : date('Y-m-d');
		if ($amount <= 0) $this->_json(array('success' => false, 'message' => 'amount required'), 422);
		if ($comment === 'College Fee' && !$this->_perm('extra_fee_access')) {
			$this->_json(array('success' => false, 'message' => 'No permission to add College Fee as extra'), 403);
		}
		$challan = $this->_new_challan_no();
		$this->db->insert('payments', array(
			'student_id' => (int)$student_id,
			'challan_no' => $challan,
			'amount' => $amount,
			'dead_line' => $deadline,
			'payment_plan' => 'Custom Plan',
			'payment_comment' => $comment,
			'paid' => 0,
			'contract_id' => 0,
			'add_by' => $this->_actor_name(),
			'last_edit' => $this->_actor_name(),
		));
		$this->db->set('extra_added_fee', 'extra_added_fee + ' . $amount, false);
		$this->db->where('student_id', (int)$student_id);
		$this->db->update('students');
		$this->_json(array('success' => true, 'message' => 'Extra fee added', 'challan_no' => $challan));
	}

	public function payments_council_fee($student_id = 0)
	{
		$this->_require_student_payments();
		$body = $this->_body();
		$exam_sequence_id = isset($body['exam_sequence_id']) ? (int)$body['exam_sequence_id'] : 0;
		$amount = isset($body['amount']) ? (float)$body['amount'] : 0;
		$deadline = isset($body['dead_line']) ? $body['dead_line'] : date('Y-m-d');
		$council_sequence_id = isset($body['council_sequence_id']) ? (int)$body['council_sequence_id'] : 0;
		if ($amount <= 0) $this->_json(array('success' => false, 'message' => 'amount required'), 422);
		$challan = $this->_new_challan_no();
		$this->db->insert('payments', array(
			'student_id' => (int)$student_id,
			'challan_no' => $challan,
			'amount' => $amount,
			'dead_line' => $deadline,
			'payment_plan' => 'consulation fee',
			'payment_comment' => 'consulation fee',
			'paid' => 0,
			'contract_id' => 0,
			'exam_sequence_id' => $exam_sequence_id,
			'council_sequence_id' => $council_sequence_id,
			'add_by' => $this->_actor_name(),
			'last_edit' => $this->_actor_name(),
		));
		$this->_json(array('success' => true, 'message' => 'Council fee added', 'challan_no' => $challan));
	}

	public function payments_auto_fees($student_id = 0)
	{
		$this->_require_student_payments();
		$student_id = (int)$student_id;
		// Delegate to existing Students controller logic via shared student helpers if available
		$student = $this->db->get_where('students', array('student_id' => $student_id))->row_array();
		if (!$student) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		$created = (float)$this->student->getStudentDiscount($student_id);
		$student_fee = (float)(isset($student['total_fee']) ? $student['total_fee'] : 0)
			+ (float)(isset($student['extra_added_fee']) ? $student['extra_added_fee'] : 0);
		$remaining = $student_fee - $created;
		if ($remaining <= 0) $this->_json(array('success' => false, 'message' => 'No remaining fee to generate'), 422);
		$challan = $this->_new_challan_no();
		$this->db->insert('payments', array(
			'student_id' => $student_id,
			'challan_no' => $challan,
			'amount' => $remaining,
			'dead_line' => date('Y-m-d'),
			'payment_plan' => 'Custom Plan',
			'payment_comment' => 'College Fee',
			'paid' => 0,
			'contract_id' => 0,
			'add_by' => $this->_actor_name(),
			'last_edit' => $this->_actor_name(),
		));
		$this->_json(array('success' => true, 'message' => 'Remaining fee installment created', 'amount' => $remaining));
	}

	public function payments_installments_preview($student_id = 0)
	{
		$this->_require_student_payments();
		$body = $this->_body();
		$day = isset($body['installment_day']) ? (int)$body['installment_day'] : 1;
		$start = isset($body['start_month']) ? $body['start_month'] : date('Y-m');
		$end = isset($body['end_month']) ? $body['end_month'] : date('Y-m');
		$student = $this->db->get_where('students', array('student_id' => (int)$student_id))->row_array();
		$created = (float)$this->student->getStudentDiscount($student_id);
		$student_fee = (float)(isset($student['total_fee']) ? $student['total_fee'] : 0)
			+ (float)(isset($student['extra_added_fee']) ? $student['extra_added_fee'] : 0);
		$remaining = max(0, $student_fee - $created);
		$months = array();
		try {
			$cur = new DateTime($start . '-01');
			$last = new DateTime($end . '-01');
			while ($cur <= $last) {
				$months[] = $cur->format('Y-m');
				$cur->modify('+1 month');
			}
		} catch (Exception $e) {
			$months = array(date('Y-m'));
		}
		$count = max(1, count($months));
		$each = round($remaining / $count, 2);
		$rows = array();
		foreach ($months as $i => $ym) {
			$amt = ($i === $count - 1) ? ($remaining - $each * ($count - 1)) : $each;
			$d = min($day, 28);
			$rows[] = array(
				'dead_line' => $ym . '-' . str_pad((string)$d, 2, '0', STR_PAD_LEFT),
				'amount' => $amt,
				'payment_comment' => 'College Fee',
			);
		}
		$this->_json(array('success' => true, 'data' => $rows, 'remaining' => $remaining));
	}

	public function payments_installments($student_id = 0)
	{
		$this->_require_student_payments();
		$body = $this->_body();
		$preview = isset($body['rows']) && is_array($body['rows']) ? $body['rows'] : array();
		if (!count($preview)) {
			$day = isset($body['installment_day']) ? (int)$body['installment_day'] : 1;
			$start = isset($body['start_month']) ? $body['start_month'] : date('Y-m');
			$end = isset($body['end_month']) ? $body['end_month'] : date('Y-m');
			$student = $this->db->get_where('students', array('student_id' => (int)$student_id))->row_array();
			$created = (float)$this->student->getStudentDiscount($student_id);
			$student_fee = (float)(isset($student['total_fee']) ? $student['total_fee'] : 0)
				+ (float)(isset($student['extra_added_fee']) ? $student['extra_added_fee'] : 0);
			$remaining = max(0, $student_fee - $created);
			$months = array();
			try {
				$cur = new DateTime($start . '-01');
				$last = new DateTime($end . '-01');
				while ($cur <= $last) {
					$months[] = $cur->format('Y-m');
					$cur->modify('+1 month');
				}
			} catch (Exception $e) {
				$months = array(date('Y-m'));
			}
			$count = max(1, count($months));
			$each = round($remaining / $count, 2);
			foreach ($months as $i => $ym) {
				$amt = ($i === $count - 1) ? ($remaining - $each * ($count - 1)) : $each;
				$d = min($day, 28);
				$preview[] = array(
					'dead_line' => $ym . '-' . str_pad((string)$d, 2, '0', STR_PAD_LEFT),
					'amount' => $amt,
				);
			}
		}
		foreach ($preview as $r) {
			$this->db->insert('payments', array(
				'student_id' => (int)$student_id,
				'challan_no' => $this->_new_challan_no(),
				'amount' => (float)$r['amount'],
				'dead_line' => $r['dead_line'],
				'payment_plan' => 'Custom Plan',
				'payment_comment' => 'College Fee',
				'paid' => 0,
				'contract_id' => 0,
				'add_by' => $this->_actor_name(),
				'last_edit' => $this->_actor_name(),
			));
		}
		$this->_json(array('success' => true, 'message' => count($preview) . ' installment(s) added'));
	}

	public function payments_reversal($student_id = 0)
	{
		if (!$this->_is_admin() && !$this->_perm('student_issue_refund')) {
			$this->_json(array('success' => false, 'message' => 'No refund permission'), 403);
		}
		$body = $this->_body();
		$payment_id = isset($body['payment_id']) ? (int)$body['payment_id'] : 0;
		$amount = isset($body['reversal_amount']) ? (float)$body['reversal_amount'] : 0;
		$reason = isset($body['reason']) ? $body['reason'] : '';
		$row = $this->db->get_where('payments', array('id' => $payment_id, 'student_id' => (int)$student_id, 'paid' => 1))->row_array();
		if (!$row) $this->_json(array('success' => false, 'message' => 'Paid installment required'), 422);
		if ($amount <= 0 || $amount > (float)$row['actual_amount']) {
			$this->_json(array('success' => false, 'message' => 'Invalid reversal amount'), 422);
		}
		$this->db->insert('payments_reversal_requests', array(
			'student_id' => (int)$student_id,
			'payment_id' => $payment_id,
			'reversal_amount' => $amount,
			'reason' => $reason,
			'done' => 0,
			'add_by' => $this->_actor_name(),
			'created_at' => date('Y-m-d H:i:s'),
		));
		$this->_json(array('success' => true, 'message' => 'Reversal request submitted'));
	}

	public function payments_reversal_complete($student_id = 0)
	{
		if (!$this->_is_admin() && !$this->_perm('student_issue_refund')) {
			$this->_json(array('success' => false, 'message' => 'No permission'), 403);
		}
		$body = $this->_body();
		$req_id = isset($body['request_id']) ? (int)$body['request_id'] : 0;
		$req = $this->db->get_where('payments_reversal_requests', array(
			'id' => $req_id,
			'student_id' => (int)$student_id,
			'done' => 0,
		))->row_array();
		if (!$req) $this->_json(array('success' => false, 'message' => 'Request not found'), 404);
		$this->db->where('id', $req_id)->update('payments_reversal_requests', array(
			'done' => 1,
			'approved_by' => $this->_actor_name(),
			'approved_at' => date('Y-m-d H:i:s'),
		));
		$this->_json(array('success' => true, 'message' => 'Reversal completed'));
	}

	public function payments_print_challan($student_id = 0)
	{
		$ids = $this->input->get('ids');
		if (!$ids) $this->_json(array('success' => false, 'message' => 'ids required'), 422);
		$id_list = array_filter(array_map('intval', explode(',', $ids)));
		if (!count($id_list)) $this->_json(array('success' => false, 'message' => 'ids required'), 422);
		$rows = $this->db->where_in('id', $id_list)->where('student_id', (int)$student_id)->get('payments')->result_array();
		$student = $this->_student_profile($student_id);
		$total = 0;
		foreach ($rows as $r) $total += (float)$r['amount'];
		$html = '<!doctype html><html><head><title>Bank Challan</title>
			<style>body{font-family:sans-serif;padding:24px}table{width:100%;border-collapse:collapse}
			th,td{border:1px solid #333;padding:8px;text-align:left}h1{margin:0 0 8px}</style></head><body>';
		$html .= '<h1>Fee Challan</h1>';
		$html .= '<p><strong>' . htmlspecialchars(trim($student['first_name'] . ' ' . $student['last_name'])) . '</strong> · Roll '
			. htmlspecialchars((string)$student['roll_no']) . ' · ' . htmlspecialchars((string)$student['class_name']) . '</p>';
		$html .= '<table><thead><tr><th>Challan</th><th>Comment</th><th>Deadline</th><th>Amount</th></tr></thead><tbody>';
		foreach ($rows as $r) {
			$html .= '<tr><td>' . htmlspecialchars($r['challan_no']) . '</td><td>' . htmlspecialchars($r['payment_comment'])
				. '</td><td>' . htmlspecialchars($r['dead_line']) . '</td><td>' . number_format((float)$r['amount']) . '</td></tr>';
		}
		$html .= '</tbody></table><p><strong>Total: ' . number_format($total) . '</strong></p>';
		$html .= '<script>window.onload=function(){window.print()}<\/script></body></html>';
		header('Content-Type: text/html; charset=utf-8');
		echo $html;
		exit;
	}

	public function payments_add_plan($student_id = 0)
	{
		$this->_require_student_payments();
		$student_id = (int)$student_id;
		$body = $this->_body();
		$plan_id = isset($body['plan_id']) ? (int)$body['plan_id'] : 0;
		$discount = isset($body['discount']) ? (float)$body['discount'] : 0;
		$instdate = isset($body['instdate']) ? (int)$body['instdate'] : 1;
		if (!$plan_id) $this->_json(array('success' => false, 'message' => 'plan_id required'), 422);
		$plan = $this->db->get_where('fee_rules', array('fee_rule_id' => $plan_id))->row_array();
		if (!$plan) $this->_json(array('success' => false, 'message' => 'Plan not found'), 404);

		$total = (float)$plan['total_fee'] - $discount;
		$this->db->where('student_id', $student_id)->update('students', array(
			'total_fee' => $total,
			'plan_id' => $plan_id,
			'current_session_fee' => (float)$plan['total_fee'],
		));

		// Create installments from fee_rules schedule fields if present
		$installments = isset($body['installments']) ? (int)$body['installments'] : (isset($plan['no_of_installments']) ? (int)$plan['no_of_installments'] : 0);
		if ($installments < 1) $installments = 1;
		$each = round($total / $installments, 2);
		$start = isset($body['start_date']) ? $body['start_date'] : date('Y-m-d');
		for ($i = 0; $i < $installments; $i++) {
			$amt = ($i === $installments - 1) ? ($total - $each * ($installments - 1)) : $each;
			$dl = date('Y-m-d', strtotime($start . ' +' . $i . ' month'));
			if ($instdate >= 1 && $instdate <= 28) {
				$dl = date('Y-m-', strtotime($dl)) . str_pad((string)$instdate, 2, '0', STR_PAD_LEFT);
			}
			$this->db->insert('payments', array(
				'student_id' => $student_id,
				'challan_no' => $this->_new_challan_no(),
				'amount' => $amt,
				'dead_line' => $dl,
				'payment_plan' => isset($plan['payment_plan']) ? $plan['payment_plan'] : 'Custom Plan',
				'payment_comment' => 'College Fee',
				'paid' => 0,
				'contract_id' => 0,
				'add_by' => $this->_actor_name(),
				'last_edit' => $this->_actor_name(),
			));
		}
		$this->_json(array('success' => true, 'message' => 'Payment plan created'));
	}

	public function payments_council_date($student_id = 0, $fee_id = 0)
	{
		$fee_id = (int)$fee_id;
		$row = $this->db->get_where('payments', array('id' => $fee_id, 'student_id' => (int)$student_id))->row_array();
		if (!$row) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		$date = $row['dead_line'];
		if (!empty($row['exam_sequence_id'])) {
			$ex = $this->db->get_where('exam_sequence', array('id' => $row['exam_sequence_id']))->row_array();
			if ($ex && !empty($ex['council_fee_last_date'])) $date = $ex['council_fee_last_date'];
		}
		$this->_json(array('success' => true, 'date' => $date));
	}
}
