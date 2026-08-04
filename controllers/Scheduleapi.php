<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Schedule Management JSON API for React POS shell.
 * Base: /index.php/scheduleapi/{method}
 */
class Scheduleapi extends CI_Controller {

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
		$this->load->library('schedule_service');
		$this->service = $this->schedule_service;
		if (!$this->service->can_access($this->current_user)) {
			$this->_json(array('success' => false, 'message' => 'Schedule management access required'), 403);
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

	private function _require_make_syllabus()
	{
		if (!$this->service->permissions($this->current_user)['make_syllabus']) {
			$this->_json(array('success' => false, 'message' => 'Make syllabus permission required'), 403);
		}
	}

	private function _require_all_syllabus()
	{
		if (!$this->service->permissions($this->current_user)['all_syllabus']) {
			$this->_json(array('success' => false, 'message' => 'All syllabus permission required'), 403);
		}
	}

	private function _require_session_syllabus()
	{
		if (!$this->service->permissions($this->current_user)['session_syllabus']) {
			$this->_json(array('success' => false, 'message' => 'Session syllabus permission required'), 403);
		}
	}

	private function _require_view_timetable()
	{
		if (!$this->service->permissions($this->current_user)['view_timetable']) {
			$this->_json(array('success' => false, 'message' => 'View timetable permission required'), 403);
		}
	}

	private function _require_study_type()
	{
		if (!$this->service->permissions($this->current_user)['study_type']) {
			$this->_json(array('success' => false, 'message' => 'Study type permission required'), 403);
		}
	}

	private function _require_shifts()
	{
		if (!$this->service->permissions($this->current_user)['shifts']) {
			$this->_json(array('success' => false, 'message' => 'Shifts permission required'), 403);
		}
	}

	private function _require_add_timetable()
	{
		if (!$this->service->permissions($this->current_user)['add_timetable']) {
			$this->_json(array('success' => false, 'message' => 'Add timetable permission required'), 403);
		}
	}

	public function meta()
	{
		$this->_json(array('success' => true, 'data' => $this->service->meta($this->current_user)));
	}

	public function subjects()
	{
		$this->_json(array(
			'success' => true,
			'data' => $this->service->subjects($this->input->get('course_id')),
		));
	}

	public function study_types()
	{
		$this->_json(array(
			'success' => true,
			'data' => $this->service->study_types($this->input->get('course_id')),
		));
	}

	public function syllabus_content()
	{
		$this->_require_make_syllabus();
		$this->_json(array(
			'success' => true,
			'data' => $this->service->syllabus_content($this->input->get('subject_id')),
		));
	}

	public function validate_plan()
	{
		$this->_require_make_syllabus();
		$body = $this->_body();
		$items = isset($body['items']) ? $body['items'] : array();
		$this->_json(array('success' => true, 'data' => $this->service->validate_plan($items)));
	}

	public function save_syllabus()
	{
		$this->_require_make_syllabus();
		$result = $this->service->save_syllabus($this->current_user, $this->_body());
		if (empty($result['success'])) $this->_json($result, 422);
		$this->_json($result);
	}

	public function syllabus_plans()
	{
		$this->_require_all_syllabus();
		$filters = array(
			'course_id' => $this->input->get('course_id'),
			'subject_id' => $this->input->get('subject_id'),
			'studytype' => $this->input->get('studytype'),
		);
		$this->_json(array('success' => true, 'data' => $this->service->syllabus_plans($filters)));
	}

	public function syllabus_plan_detail($unique_syllabus_id = null)
	{
		$this->_require_all_syllabus();
		$data = $this->service->syllabus_plan_detail((int) $unique_syllabus_id);
		if (!$data) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		$this->_json(array('success' => true, 'data' => $data));
	}

	public function delete_syllabus_plan()
	{
		$this->_require_all_syllabus();
		$body = $this->_body();
		$id = isset($body['unique_syllabus_id']) ? (int) $body['unique_syllabus_id'] : 0;
		$result = $this->service->delete_syllabus_plan($id);
		if (empty($result['success'])) $this->_json($result, 422);
		$this->_json($result);
	}

	public function session_syllabus_list()
	{
		$this->_require_session_syllabus();
		$filters = array(
			'course_id' => $this->input->get('course_id'),
			'shift_id' => $this->input->get('shift_id'),
			'campus_id' => $this->input->get('campus_id'),
		);
		$this->_json(array('success' => true, 'data' => $this->service->session_syllabus_list($filters)));
	}

	public function session_syllabus_detail($subject_id = null, $lecture_id = null)
	{
		$this->_require_session_syllabus();
		$merged = $this->input->get('merged') === '1';
		$this->_json(array(
			'success' => true,
			'data' => $this->service->session_syllabus_detail((int) $subject_id, (int) $lecture_id, $merged),
		));
	}

	public function delete_session_syllabus()
	{
		$this->_require_session_syllabus();
		$body = $this->_body();
		$result = $this->service->delete_session_syllabus(
			isset($body['subject_id']) ? (int) $body['subject_id'] : 0,
			isset($body['lecture_id']) ? (int) $body['lecture_id'] : 0,
			isset($body['syllabus_id']) ? (int) $body['syllabus_id'] : 0
		);
		$this->_json($result);
	}

	public function timetable_lectures()
	{
		$this->_require_view_timetable();
		$this->_json(array(
			'success' => true,
			'data' => $this->service->timetable_lectures($this->input->get('campus_id')),
		));
	}

	public function available_syllabuses()
	{
		$this->_require_view_timetable();
		$this->_json(array(
			'success' => true,
			'data' => $this->service->available_syllabuses(
				$this->input->get('subject_id'),
				$this->input->get('study_type')
			),
		));
	}

	public function suggested_start_date()
	{
		$this->_require_view_timetable();
		$this->_json(array(
			'success' => true,
			'data' => array(
				'start_date' => $this->service->suggested_start_date(
					$this->input->get('subject_id'),
					$this->input->get('lecture_id')
				),
			),
		));
	}

	public function generate_preview()
	{
		$this->_require_view_timetable();
		$body = $this->_body();
		$result = $this->service->generate_session_preview(
			isset($body['lecture_id']) ? (int) $body['lecture_id'] : 0,
			isset($body['subject_id']) ? (int) $body['subject_id'] : 0,
			isset($body['unique_syllabus_id']) ? (int) $body['unique_syllabus_id'] : 0,
			isset($body['start_date']) ? $body['start_date'] : '',
			isset($body['test_after']) ? (int) $body['test_after'] : 3
		);
		if (empty($result['success'])) $this->_json($result, 422);
		$this->_json(array('success' => true, 'data' => $result));
	}

	public function save_session_syllabus()
	{
		$this->_require_view_timetable();
		$result = $this->service->save_session_syllabus($this->current_user, $this->_body());
		if (empty($result['success'])) $this->_json($result, 422);
		$this->_json($result);
	}

	// ── Timetable CRUD ───────────────────────────────────────────────────────

	public function study_type_list()
	{
		$this->_require_study_type();
		$this->_json(array('success' => true, 'data' => $this->service->study_type_list()));
	}

	public function save_study_type()
	{
		$this->_require_study_type();
		$result = $this->service->save_study_type($this->current_user, $this->_body());
		if (empty($result['success'])) $this->_json($result, 422);
		$this->_json($result);
	}

	public function update_study_type()
	{
		$this->_require_study_type();
		$result = $this->service->update_study_type($this->current_user, $this->_body());
		if (empty($result['success'])) $this->_json($result, 422);
		$this->_json($result);
	}

	public function delete_study_type()
	{
		$this->_require_study_type();
		$body = $this->_body();
		$id = isset($body['id']) ? (int) $body['id'] : 0;
		$result = $this->service->delete_study_type($id, $this->current_user);
		if (empty($result['success'])) $this->_json($result, 422);
		$this->_json($result);
	}

	public function shifts_list()
	{
		$this->_require_shifts();
		$this->_json(array('success' => true, 'data' => $this->service->shifts_list()));
	}

	public function save_shift()
	{
		$this->_require_shifts();
		$result = $this->service->save_shift($this->current_user, $this->_body());
		if (empty($result['success'])) $this->_json($result, 422);
		$this->_json($result);
	}

	public function update_shift()
	{
		$this->_require_shifts();
		$result = $this->service->update_shift($this->current_user, $this->_body());
		if (empty($result['success'])) $this->_json($result, 422);
		$this->_json($result);
	}

	public function delete_shift()
	{
		$this->_require_shifts();
		$body = $this->_body();
		$id = isset($body['id']) ? (int) $body['id'] : 0;
		$result = $this->service->delete_shift($id);
		if (empty($result['success'])) $this->_json($result, 422);
		$this->_json($result);
	}

	public function campus_rooms()
	{
		$this->_json(array(
			'success' => true,
			'data' => $this->service->campus_rooms($this->input->get('campus_id')),
		));
	}

	public function study_type_days()
	{
		$this->_json(array(
			'success' => true,
			'data' => $this->service->study_type_days($this->input->get('study_type_id')),
		));
	}

	public function course_sessions()
	{
		$this->_json(array(
			'success' => true,
			'data' => $this->service->course_sessions($this->input->get('course_id')),
		));
	}

	public function subjects_for_class()
	{
		$this->_json(array(
			'success' => true,
			'data' => $this->service->subjects_for_class(
				$this->input->get('course_id'),
				$this->input->get('class')
			),
		));
	}

	public function shifts_for_campus()
	{
		$this->_json(array(
			'success' => true,
			'data' => $this->service->shifts_for_campus(
				$this->input->get('campus_id'),
				$this->input->get('study_type_id')
			),
		));
	}

	public function timetable_detail($id = null)
	{
		if (!$this->service->permissions($this->current_user)['view_timetable']
			&& !$this->service->permissions($this->current_user)['add_timetable']) {
			$this->_json(array('success' => false, 'message' => 'Timetable permission required'), 403);
		}
		$data = $this->service->timetable_detail((int) $id);
		if (!$data) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		$this->_json(array('success' => true, 'data' => $data));
	}

	public function save_timetable()
	{
		$this->_require_add_timetable();
		$result = $this->service->save_timetable($this->current_user, $this->_body());
		if (empty($result['success'])) $this->_json($result, 422);
		$this->_json($result);
	}

	public function update_timetable($id = null)
	{
		$this->_require_add_timetable();
		$result = $this->service->update_timetable($this->current_user, (int) $id, $this->_body());
		if (empty($result['success'])) $this->_json($result, 422);
		$this->_json($result);
	}

	public function delete_timetable()
	{
		$this->_require_view_timetable();
		$body = $this->_body();
		$id = isset($body['id']) ? (int) $body['id'] : 0;
		$result = $this->service->delete_timetable($id);
		if (empty($result['success'])) $this->_json($result, 422);
		$this->_json($result);
	}

	public function timetable_groups()
	{
		$this->_require_view_timetable();
		$this->_json(array(
			'success' => true,
			'data' => $this->service->timetable_groups($this->input->get('campus_id')),
		));
	}

	public function assign_zoom()
	{
		$this->_require_view_timetable();
		$body = $this->_body();
		$result = $this->service->assign_zoom(
			isset($body['lecture_id']) ? (int) $body['lecture_id'] : 0,
			isset($body['zoom_id']) ? $body['zoom_id'] : '',
			isset($body['zoom_password']) ? $body['zoom_password'] : ''
		);
		if (empty($result['success'])) $this->_json($result, 422);
		$this->_json($result);
	}

	public function today_lectures($lecture_id = null)
	{
		$this->_require_view_timetable();
		$result = $this->service->lecture_sessions((int) $lecture_id, 'today');
		if (empty($result['success'])) $this->_json($result, 404);
		$this->_json(array('success' => true, 'data' => $result));
	}

	public function all_lectures($lecture_id = null)
	{
		$this->_require_view_timetable();
		$result = $this->service->lecture_sessions((int) $lecture_id, 'all');
		if (empty($result['success'])) $this->_json($result, 404);
		$this->_json(array('success' => true, 'data' => $result));
	}

	public function lecture_attendance()
	{
		$this->_require_view_timetable();
		$this->_json(array(
			'success' => true,
			'data' => $this->service->lecture_attendance_list(
				$this->input->get('lecture_id'),
				$this->input->get('date')
			),
		));
	}

	public function session_students($lecture_id = null)
	{
		$this->_require_view_timetable();
		$result = $this->service->session_students((int) $lecture_id);
		if (empty($result['success'])) $this->_json($result, 404);
		$this->_json(array('success' => true, 'data' => $result));
	}

	public function mark_student_attendance()
	{
		$this->_require_view_timetable();
		$body = $this->_body();
		$this->_json($this->service->mark_student_attendance(
			$this->current_user,
			isset($body['student_id']) ? (int) $body['student_id'] : 0,
			isset($body['lecture_id']) ? (int) $body['lecture_id'] : 0
		));
	}

	public function unmark_student_attendance()
	{
		$this->_require_view_timetable();
		$body = $this->_body();
		$this->_json($this->service->unmark_student_attendance(
			isset($body['student_id']) ? (int) $body['student_id'] : 0,
			isset($body['lecture_id']) ? (int) $body['lecture_id'] : 0
		));
	}

	public function save_absent_student_info()
	{
		$this->_require_view_timetable();
		$body = $this->_body();
		$result = $this->service->save_absent_student_info(
			$this->current_user,
			isset($body['student_id']) ? (int) $body['student_id'] : 0,
			isset($body['lecture_id']) ? (int) $body['lecture_id'] : 0,
			isset($body['info']) ? $body['info'] : ''
		);
		if (empty($result['success'])) $this->_json($result, 422);
		$this->_json($result);
	}

	public function mark_topic_studied()
	{
		$this->_require_view_timetable();
		$body = $this->_body();
		$this->_json($this->service->mark_topic_studied(
			$this->current_user,
			isset($body['topic_id']) ? (int) $body['topic_id'] : 0,
			isset($body['session_syllabus_id']) ? (int) $body['session_syllabus_id'] : 0,
			isset($body['is_quiz']) ? (int) $body['is_quiz'] : 0
		));
	}

	public function unmark_topic_studied()
	{
		$this->_require_view_timetable();
		$body = $this->_body();
		$this->_json($this->service->unmark_topic_studied(
			isset($body['topic_id']) ? (int) $body['topic_id'] : 0,
			isset($body['session_syllabus_id']) ? (int) $body['session_syllabus_id'] : 0,
			isset($body['is_quiz']) ? (int) $body['is_quiz'] : 0
		));
	}

	public function mark_practical_studied()
	{
		$this->_require_view_timetable();
		$body = $this->_body();
		$this->_json($this->service->mark_practical_studied(
			$this->current_user,
			isset($body['practical_id']) ? (int) $body['practical_id'] : 0,
			isset($body['session_syllabus_id']) ? (int) $body['session_syllabus_id'] : 0,
			isset($body['is_quiz']) ? (int) $body['is_quiz'] : 0
		));
	}

	public function unmark_practical_studied()
	{
		$this->_require_view_timetable();
		$body = $this->_body();
		$this->_json($this->service->unmark_practical_studied(
			isset($body['practical_id']) ? (int) $body['practical_id'] : 0,
			isset($body['session_syllabus_id']) ? (int) $body['session_syllabus_id'] : 0,
			isset($body['is_quiz']) ? (int) $body['is_quiz'] : 0
		));
	}
}
