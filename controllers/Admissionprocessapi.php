<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Standalone admission-completion workflow. It never mutates legacy student data. */
class Admissionprocessapi extends CI_Controller
{
	private $current_user = null;
	private $access_row = array();
	private $sections = array('eligibility', 'student_detail', 'parents_detail', 'occupations', 'fee_plan', 'documents', 'verification');

	public function __construct()
	{
		parent::__construct();
		$this->_cors();
		if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
		$this->current_user = $this->_auth_user();
		if (!$this->current_user) $this->_json(array('success' => false, 'message' => 'Unauthorized'), 401);
		$this->_ensure_access_columns();
		$this->access_row = $this->db->get_where('access', array('user_id' => (int)$this->current_user['user_id']))->row_array();
		if (!$this->_can_view()) $this->_json(array('success' => false, 'message' => 'Students access required'), 403);
		$this->_ensure_schema();
	}

	private function _cors()
	{
		$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
		if ($origin === '*' || preg_match('/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/', $origin) || in_array($origin, array('https://pos.shahbazcollegeofpharmacy.edu.pk', 'http://pos.shahbazcollegeofpharmacy.edu.pk'))) header('Access-Control-Allow-Origin: ' . $origin);
		header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
		header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Pos-Token');
	}

	private function _json($data, $code = 200) { http_response_code($code); header('Content-Type: application/json; charset=utf-8'); echo json_encode($data); exit; }
	private function _body() { $v = json_decode(file_get_contents('php://input'), true); return is_array($v) ? $v : $this->input->post(); }
	private function _auth_user()
	{
		$token = isset($_SERVER['HTTP_X_POS_TOKEN']) ? $_SERVER['HTTP_X_POS_TOKEN'] : $this->input->get_request_header('X-Pos-Token', TRUE);
		if (!$token) return null;
		$row = $this->db->get_where('pos_api_tokens', array('token' => $token))->row_array();
		if (!$row || strtotime($row['expires_at']) < time()) return null;
		return $this->db->get_where('users', array('user_id' => $row['user_id'], 'status' => '1'))->row_array();
	}
	private function _is_admin() { return isset($this->current_user['role']) && $this->current_user['role'] === 'Admin'; }
	private function _ensure_access_columns()
	{
		$columns = array('admission_process_access', 'admission_process_edit', 'admission_process_verify', 'admission_process_report');
		foreach ($columns as $name) if (!$this->db->field_exists($name, 'access')) $this->db->query("ALTER TABLE `access` ADD `$name` TINYINT(1) NOT NULL DEFAULT 0");
		if (!$this->db->field_exists('admission_process_campus_ids', 'access')) $this->db->query("ALTER TABLE `access` ADD `admission_process_campus_ids` TEXT NULL");
	}
	private function _can_view()
	{
		if ($this->_is_admin()) return true;
		return !empty($this->access_row['admission_process_access']);
	}
	private function _campus_ids()
	{
		if ($this->_is_admin() || empty($this->access_row['admission_process_campus_ids'])) return null;
		return array_values(array_filter(array_map('intval', explode(',', $this->access_row['admission_process_campus_ids']))));
	}
	private function _actor() { $n = trim($this->current_user['first_name'] . ' ' . $this->current_user['last_name']); return $n !== '' ? $n : 'POS'; }

	private function _ensure_schema()
	{
		$this->db->query("CREATE TABLE IF NOT EXISTS student_admission_processes (
			id INT NOT NULL AUTO_INCREMENT, student_id INT NOT NULL, overall_status VARCHAR(30) NOT NULL DEFAULT 'draft',
			started_at DATETIME NULL, completed_at DATETIME NULL, completed_by INT NULL,
			created_by INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL,
			PRIMARY KEY (id), UNIQUE KEY uq_student_admission_process (student_id), KEY idx_admission_status (overall_status)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");
		$this->db->query("CREATE TABLE IF NOT EXISTS student_admission_process_sections (
			id INT NOT NULL AUTO_INCREMENT, process_id INT NOT NULL, section_key VARCHAR(40) NOT NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'pending', remarks TEXT NULL, updated_by INT NULL, updated_by_name VARCHAR(255) NULL, updated_at DATETIME NULL,
			PRIMARY KEY (id), UNIQUE KEY uq_process_section (process_id, section_key), KEY idx_section_process (process_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");
		$this->db->query("CREATE TABLE IF NOT EXISTS student_admission_process_logs (
			id INT NOT NULL AUTO_INCREMENT, process_id INT NOT NULL, section_key VARCHAR(40) NULL, action VARCHAR(50) NOT NULL,
			old_status VARCHAR(20) NULL, new_status VARCHAR(20) NULL, remarks TEXT NULL, user_id INT NULL, user_name VARCHAR(255) NULL, created_at DATETIME NOT NULL,
			PRIMARY KEY (id), KEY idx_process_log (process_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	}

	private function _process($student_id, $create = false)
	{
		$row = $this->db->get_where('student_admission_processes', array('student_id' => $student_id))->row_array();
		if (!$row && $create) {
			$now = date('Y-m-d H:i:s');
			$this->db->insert('student_admission_processes', array('student_id' => $student_id, 'overall_status' => 'draft', 'started_at' => $now, 'created_by' => (int)$this->current_user['user_id'], 'created_at' => $now, 'updated_at' => $now));
			$row = $this->db->get_where('student_admission_processes', array('id' => $this->db->insert_id()))->row_array();
		}
		return $row;
	}

	private function _sections_for($process_id)
	{
		$found = array();
		if ($process_id) foreach ($this->db->get_where('student_admission_process_sections', array('process_id' => $process_id))->result_array() as $r) $found[$r['section_key']] = $r;
		$out = array();
		foreach ($this->sections as $key) $out[] = isset($found[$key]) ? $found[$key] : array('section_key' => $key, 'status' => 'pending', 'remarks' => '', 'updated_at' => null, 'updated_by_name' => null);
		return $out;
	}

	public function meta()
	{
		$ids = $this->_campus_ids();
		$this->db->select('campus_id, campus_name')->order_by('campus_name');
		if (is_array($ids)) $this->db->where_in('campus_id', count($ids) ? $ids : array(0));
		$campuses = $this->db->get('campuses')->result_array();
		$this->db->select('class_id, name, campus_id')->where('status', 1)->order_by('name');
		if (is_array($ids)) $this->db->where_in('campus_id', count($ids) ? $ids : array(0));
		$classes = $this->db->get('classes')->result_array();
		$this->_json(array('success' => true, 'data' => array('permissions' => array('view' => true, 'edit' => $this->_is_admin() || !empty($this->access_row['admission_process_edit']), 'verify' => $this->_is_admin() || !empty($this->access_row['admission_process_verify']), 'report' => $this->_is_admin() || !empty($this->access_row['admission_process_report'])), 'campuses' => $campuses, 'classes' => $classes, 'sections' => $this->sections)));
	}

	public function students()
	{
		$q = trim((string)$this->input->get('q')); $campus = (int)$this->input->get('campus_id'); $class_id = (int)$this->input->get('class_id'); $status = trim((string)$this->input->get('status'));
		$date_from = trim((string)$this->input->get('date_from')); $date_to = trim((string)$this->input->get('date_to'));
		if ($date_from === '') $date_from = date('Y-m-01');
		if ($date_to === '') $date_to = date('Y-m-d');
		$this->db->select("students.student_id, students.first_name, students.last_name, students.roll_no, students.cnic, students.mobile, students.entry_date, classes.name AS class_name, campuses.campus_name, courses.course_name, ap.id AS process_id, COALESCE(ap.overall_status, 'not_started') AS overall_status, ap.updated_at", false);
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'left');
		$this->db->join('campuses', 'campuses.campus_id=classes.campus_id', 'left');
		$this->db->join('courses', 'courses.course_id=students.course_id', 'left');
		$this->db->join('student_admission_processes ap', 'ap.student_id=students.student_id', 'left');
		$this->db->where('students.status', '1');
		$allowed_campuses = $this->_campus_ids();
		if (is_array($allowed_campuses)) $this->db->where_in('classes.campus_id', count($allowed_campuses) ? $allowed_campuses : array(0));
		if ($campus) $this->db->where('classes.campus_id', $campus);
		if ($class_id) $this->db->where('students.class_id', $class_id);
		$this->db->where('students.entry_date >=', $date_from)->where('students.entry_date <=', $date_to);
		if ($status !== '') $status === 'not_started' ? $this->db->where('ap.id IS NULL', null, false) : $this->db->where('ap.overall_status', $status);
		if ($q !== '') { $this->db->group_start()->like('students.first_name', $q)->or_like('students.last_name', $q)->or_like('students.roll_no', $q)->or_like('students.cnic', $q)->or_like('students.mobile', $q)->group_end(); }
		$this->db->order_by('students.student_id', 'DESC')->limit(1000);
		$rows = $this->db->get()->result_array();
		$ids = array(); foreach ($rows as $r) if (!empty($r['process_id'])) $ids[] = (int)$r['process_id'];
		$counts = array();
		if (count($ids)) foreach ($this->db->select('process_id, SUM(status="complete") AS done', false)->where_in('process_id', $ids)->group_by('process_id')->get('student_admission_process_sections')->result_array() as $c) $counts[(int)$c['process_id']] = (int)$c['done'];
		foreach ($rows as &$r) { $done = isset($counts[(int)$r['process_id']]) ? $counts[(int)$r['process_id']] : 0; $r['completed_sections'] = $done; $r['progress'] = (int)round(($done / count($this->sections)) * 100); $r['student_name'] = trim($r['first_name'].' '.$r['last_name']); }
		$this->_json(array('success' => true, 'data' => $rows));
	}

	private function _student($id)
	{
		$this->db->select('students.*, classes.name AS class_name, classes.session, campuses.campus_id, campuses.campus_name, courses.course_name', false)->from('students')->join('classes', 'classes.class_id=students.class_id', 'left')->join('campuses', 'campuses.campus_id=classes.campus_id', 'left')->join('courses', 'courses.course_id=students.course_id', 'left')->where('students.student_id', $id);
		return $this->db->get()->row_array();
	}

	public function detail($student_id = 0)
	{
		$student_id = (int)$student_id; $student = $this->_student($student_id);
		if (!$student) $this->_json(array('success' => false, 'message' => 'Student not found'), 404);
		$allowed = $this->_campus_ids();
		if (is_array($allowed) && !in_array((int)$student['campus_id'], $allowed, true)) $this->_json(array('success' => false, 'message' => 'This campus is not assigned to you'), 403);
		$process = $this->_process($student_id, true); $sections = $this->_sections_for((int)$process['id']);
		$docs = $this->db->select('type')->where('student_id', $student_id)->get('student_documents')->result_array();
		$payments = $this->db->select('id, payment_plan, amount, paid, dead_line')->where('student_id', $student_id)->order_by('dead_line')->get('payments')->result_array();
		$logs = $this->db->where('process_id', (int)$process['id'])->order_by('id', 'DESC')->limit(30)->get('student_admission_process_logs')->result_array();
		$summary = array(
			'eligibility' => array('course' => isset($student['course_name']) ? $student['course_name'] : '', 'note' => 'Eligibility result is recorded during admission where criteria are configured.'),
			'student_detail' => array('name' => trim($student['first_name'].' '.$student['last_name']), 'cnic' => isset($student['cnic']) ? $student['cnic'] : '', 'mobile' => isset($student['mobile']) ? $student['mobile'] : '', 'roll_no' => isset($student['roll_no']) ? $student['roll_no'] : ''),
			'parents_detail' => array('father_name' => isset($student['father_name']) ? $student['father_name'] : '', 'mother_name' => isset($student['mother_name']) ? $student['mother_name'] : '', 'emergency_no' => isset($student['emergency_no']) ? $student['emergency_no'] : ''),
			'occupations' => array('student' => isset($student['student_occupation_id']) ? $student['student_occupation_id'] : null, 'father' => isset($student['father_occupation_id']) ? $student['father_occupation_id'] : null, 'mother' => isset($student['mother_occupation_id']) ? $student['mother_occupation_id'] : null),
			'fee_plan' => array('installments' => count($payments), 'paid' => count(array_filter($payments, function($p){ return (int)$p['paid'] === 1; })), 'payments' => $payments),
			'documents' => array('count' => count($docs), 'types' => array_values(array_unique(array_map(function($d){ return $d['type']; }, $docs)))),
			'verification' => array('completed_sections' => count(array_filter($sections, function($s){ return $s['section_key'] !== 'verification' && $s['status'] === 'complete'; })), 'required_sections' => 6),
		);
		$this->_json(array('success' => true, 'data' => array('student' => $student, 'process' => $process, 'sections' => $sections, 'summary' => $summary, 'logs' => $logs)));
	}

	public function save_section($student_id = 0, $section = '')
	{
		if (!$this->_is_admin() && empty($this->access_row['admission_process_edit']) && !($section === 'verification' && !empty($this->access_row['admission_process_verify']))) $this->_json(array('success' => false, 'message' => 'Admission process update permission required'), 403);
		if ($section === 'verification' && !$this->_is_admin() && empty($this->access_row['admission_process_verify'])) $this->_json(array('success' => false, 'message' => 'Final verification permission required'), 403);
		if (!in_array($section, $this->sections, true)) $this->_json(array('success' => false, 'message' => 'Invalid section'), 422);
		$body = $this->_body(); $status = isset($body['status']) ? $body['status'] : 'pending';
		if (!in_array($status, array('pending', 'complete', 'needs_attention'), true)) $this->_json(array('success' => false, 'message' => 'Invalid status'), 422);
		$process = $this->_process((int)$student_id, true); $existing = $this->db->get_where('student_admission_process_sections', array('process_id' => $process['id'], 'section_key' => $section))->row_array();
		if ($section === 'verification' && $status === 'complete') {
			$ready = $this->db->where('process_id', $process['id'])->where_in('section_key', array('eligibility', 'student_detail', 'parents_detail', 'occupations', 'fee_plan', 'documents'))->where('status', 'complete')->count_all_results('student_admission_process_sections');
			if ($ready < 6) $this->_json(array('success' => false, 'message' => 'Complete all six prerequisite sections before final verification'), 422);
		}
		$data = array('process_id' => $process['id'], 'section_key' => $section, 'status' => $status, 'remarks' => isset($body['remarks']) ? trim($body['remarks']) : '', 'updated_by' => (int)$this->current_user['user_id'], 'updated_by_name' => $this->_actor(), 'updated_at' => date('Y-m-d H:i:s'));
		$existing ? $this->db->where('id', $existing['id'])->update('student_admission_process_sections', $data) : $this->db->insert('student_admission_process_sections', $data);
		$this->db->insert('student_admission_process_logs', array('process_id' => $process['id'], 'section_key' => $section, 'action' => 'section_updated', 'old_status' => $existing ? $existing['status'] : 'pending', 'new_status' => $status, 'remarks' => $data['remarks'], 'user_id' => $data['updated_by'], 'user_name' => $data['updated_by_name'], 'created_at' => $data['updated_at']));
		$sections = $this->_sections_for((int)$process['id']); $done = count(array_filter($sections, function($s){ return $s['status'] === 'complete'; }));
		$overall = $done === count($this->sections) ? 'complete' : ($done > 0 ? 'in_progress' : 'draft');
		$this->db->where('id', $process['id'])->update('student_admission_processes', array('overall_status' => $overall, 'completed_at' => $overall === 'complete' ? date('Y-m-d H:i:s') : null, 'completed_by' => $overall === 'complete' ? (int)$this->current_user['user_id'] : null, 'updated_at' => date('Y-m-d H:i:s')));
		$this->_json(array('success' => true, 'message' => 'Section saved'));
	}
}
