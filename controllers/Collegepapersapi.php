<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Papers & Results JSON API for React POS shell.
 * Base: /index.php/collegepapersapi/{method}
 */
class Collegepapersapi extends CI_Controller {

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
        $this->load->library('collegepapers_service');
        $this->service = $this->collegepapers_service;
        if (!$this->service->can_access($this->current_user)) {
            $this->_json(array('success' => false, 'message' => 'Papers & Results access required'), 403);
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

    public function meta()
    {
        $full = $this->service->meta($this->current_user);
        $this->_json(array(
            'success' => true,
            'permissions' => $full['permissions'],
            'campuses' => $full['campuses'],
            'courses' => $full['courses'],
            'exams' => $full['exams'],
            'legacy_root' => $full['legacy_root'],
        ));
    }

    public function subjects()
    {
        $course_id = $this->input->get('course_id');
        $period = $this->input->get('period');
        $this->_json(array(
            'success' => true,
            'data' => $this->service->subjects_for_course($course_id, $period, $this->current_user),
        ));
    }

    public function students()
    {
        $course_id = $this->input->get('course_id');
        $this->_json(array(
            'success' => true,
            'data' => $this->service->students_for_course($course_id),
        ));
    }

    public function all_papers()
    {
        $p = $this->_perms();
        if (empty($p['all_paper'])) {
            $this->_json(array('success' => false, 'message' => 'All Papers permission required'), 403);
        }
        $filters = array_merge(
            array(
                'campus_id' => $this->input->get('campus_id'),
                'course_id' => $this->input->get('course_id'),
                'subject_id' => $this->input->get('subject_id'),
                'start_date' => $this->input->get('start_date'),
                'end_date' => $this->input->get('end_date'),
            ),
            $this->_body()
        );
        $this->_json(array(
            'success' => true,
            'data' => $this->service->all_papers($filters),
        ));
    }

    public function student_results()
    {
        $p = $this->_perms();
        if (empty($p['student_results'])) {
            $this->_json(array('success' => false, 'message' => 'Student Results permission required'), 403);
        }
        $filters = array_merge(
            array(
                'course_id' => $this->input->get('course_id'),
                'subject_id' => $this->input->get('subject_id'),
                'student_id' => $this->input->get('student_id'),
                'start_date' => $this->input->get('start_date'),
                'end_date' => $this->input->get('end_date'),
            ),
            $this->_body()
        );
        $this->_json(array(
            'success' => true,
            'data' => $this->service->student_results($filters),
        ));
    }

    public function sessions()
    {
        $p = $this->_perms();
        if (empty($p['add_paper'])) {
            $this->_json(array('success' => false, 'message' => 'Add Paper permission required'), 403);
        }
        $campus_id = $this->input->get('campus_id');
        $this->_json(array(
            'success' => true,
            'data' => $this->service->sessions_for_campus($campus_id),
        ));
    }

    public function course_period()
    {
        $p = $this->_perms();
        if (empty($p['add_paper'])) {
            $this->_json(array('success' => false, 'message' => 'Add Paper permission required'), 403);
        }
        $course_id = $this->input->get('course_id');
        $this->_json(array(
            'success' => true,
            'data' => $this->service->course_period_options($course_id),
        ));
    }

    public function chapters()
    {
        $p = $this->_perms();
        if (empty($p['add_paper'])) {
            $this->_json(array('success' => false, 'message' => 'Add Paper permission required'), 403);
        }
        $subject_ids = $this->input->get('subject_ids');
        if ($subject_ids === null || $subject_ids === '') {
            $body = $this->_body();
            $subject_ids = isset($body['subject_ids']) ? $body['subject_ids'] : array();
        }
        $this->_json(array(
            'success' => true,
            'data' => $this->service->chapters_for_subjects($subject_ids),
        ));
    }

    public function topics()
    {
        $p = $this->_perms();
        if (empty($p['add_paper'])) {
            $this->_json(array('success' => false, 'message' => 'Add Paper permission required'), 403);
        }
        $chapter_ids = $this->input->get('chapter_ids');
        if ($chapter_ids === null || $chapter_ids === '') {
            $body = $this->_body();
            $chapter_ids = isset($body['chapter_ids']) ? $body['chapter_ids'] : array();
        }
        $this->_json(array(
            'success' => true,
            'data' => $this->service->topics_for_chapters($chapter_ids),
        ));
    }

    public function practicals()
    {
        $p = $this->_perms();
        if (empty($p['add_paper'])) {
            $this->_json(array('success' => false, 'message' => 'Add Paper permission required'), 403);
        }
        $subject_ids = $this->input->get('subject_ids');
        if ($subject_ids === null || $subject_ids === '') {
            $body = $this->_body();
            $subject_ids = isset($body['subject_ids']) ? $body['subject_ids'] : array();
        }
        $this->_json(array(
            'success' => true,
            'data' => $this->service->practicals_for_subjects($subject_ids),
        ));
    }

    public function classes_for_filters()
    {
        $p = $this->_perms();
        if (empty($p['improvement_report'])) {
            $this->_json(array('success' => false, 'message' => 'Improvement Report permission required'), 403);
        }
        $campus_id = $this->input->get('campus_id');
        $course_id = $this->input->get('course_id');
        $this->_json(array(
            'success' => true,
            'data' => $this->service->classes_for_campus_course($campus_id, $course_id),
        ));
    }

    public function expense_categories()
    {
        $p = $this->_perms();
        if (empty($p['test_system'])) {
            $this->_json(array('success' => false, 'message' => 'Test System permission required'), 403);
        }
        $this->_json(array(
            'success' => true,
            'data' => $this->service->expense_categories_flat(),
        ));
    }

    public function prepare_create_paper()
    {
        $p = $this->_perms();
        if (empty($p['add_paper'])) {
            $this->_json(array('success' => false, 'message' => 'Add Paper permission required'), 403);
        }
        $url = $this->service->prepare_create_paper($this->current_user, $this->_body());
        $this->_json(array(
            'success' => true,
            'redirect_url' => $url,
        ));
    }

    public function exams()
    {
        $p = $this->_perms();
        if (empty($p['test_system'])) {
            $this->_json(array('success' => false, 'message' => 'Test System permission required'), 403);
        }
        $this->_json(array(
            'success' => true,
            'data' => $this->service->list_exams(),
        ));
    }

    public function exam($id = null)
    {
        $p = $this->_perms();
        if (empty($p['test_system'])) {
            $this->_json(array('success' => false, 'message' => 'Test System permission required'), 403);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $result = $this->service->delete_exam($id);
            $this->_json(array('success' => true, 'message' => $result['message']));
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $body = $this->_body();
            if ($id) {
                $body['id'] = $id;
            }
            $result = $this->service->save_exam($body);
            $this->_json(array('success' => true, 'message' => $result['message'], 'id' => $result['id']));
        }
        $this->_json(array(
            'success' => true,
            'data' => $this->service->get_exam($id),
        ));
    }

    public function improvement_rules($exam_id = null)
    {
        $p = $this->_perms();
        if (empty($p['test_system'])) {
            $this->_json(array('success' => false, 'message' => 'Test System permission required'), 403);
        }
        $this->_json(array(
            'success' => true,
            'data' => $this->service->get_improvement_rules($exam_id),
        ));
    }

    public function improvement_rule($id = null)
    {
        $p = $this->_perms();
        if (empty($p['test_system'])) {
            $this->_json(array('success' => false, 'message' => 'Test System permission required'), 403);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $result = $this->service->delete_improvement_rule($id);
            $this->_json(array('success' => true, 'message' => $result['message']));
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->service->save_improvement_rule($this->_body());
            $this->_json(array('success' => true, 'message' => $result['message'], 'id' => $result['id']));
        }
        $this->_json(array(
            'success' => true,
            'data' => $this->service->get_improvement_rule($id),
        ));
    }

    public function reward_rules($exam_id = null)
    {
        $p = $this->_perms();
        if (empty($p['test_system'])) {
            $this->_json(array('success' => false, 'message' => 'Test System permission required'), 403);
        }
        $this->_json(array(
            'success' => true,
            'data' => $this->service->get_reward_rules($exam_id),
        ));
    }

    public function reward_rule($id = null)
    {
        $p = $this->_perms();
        if (empty($p['test_system'])) {
            $this->_json(array('success' => false, 'message' => 'Test System permission required'), 403);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $result = $this->service->delete_reward_rule($id);
            $this->_json(array('success' => true, 'message' => $result['message']));
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->service->save_reward_rule($this->_body());
            $this->_json(array('success' => true, 'message' => $result['message'], 'id' => $result['id']));
        }
        $this->_json(array(
            'success' => true,
            'data' => $this->service->get_reward_rule($id),
        ));
    }

    public function improvement_report()
    {
        $p = $this->_perms();
        if (empty($p['improvement_report'])) {
            $this->_json(array('success' => false, 'message' => 'Improvement Report permission required'), 403);
        }
        $filters = array_merge(
            array(
                'exam_id' => $this->input->get('exam_id'),
                'campus_id' => $this->input->get('campus_id'),
                'course_id' => $this->input->get('course_id'),
                'class' => $this->input->get('class'),
                'badge' => $this->input->get('badge'),
                'search' => $this->input->get('search'),
            ),
            $this->_body()
        );
        if (!isset($filters['search']) && !empty($filters['course_id'])) {
            $filters['search'] = 1;
        }
        $result = $this->service->improvement_report($filters);
        $this->_json(array(
            'success' => true,
            'data' => $result['report'],
            'max_attempts' => $result['max_attempts'],
        ));
    }

    public function improvement_month_detail()
    {
        $p = $this->_perms();
        if (empty($p['improvement_report'])) {
            $this->_json(array('success' => false, 'message' => 'Improvement Report permission required'), 403);
        }
        $student_id = $this->input->get('student_id');
        $exam_id = $this->input->get('exam_id');
        $month_key = $this->input->get('month_key');
        $this->_json(array(
            'success' => true,
            'data' => $this->service->improvement_month_detail($student_id, $exam_id, $month_key),
        ));
    }

    public function overall_class_performance()
    {
        $p = $this->_perms();
        if (empty($p['improvement_report'])) {
            $this->_json(array('success' => false, 'message' => 'Improvement Report permission required'), 403);
        }
        $filters = array_merge(
            array(
                'exam_id' => $this->input->get('exam_id'),
                'campus_id' => $this->input->get('campus_id'),
                'course_id' => $this->input->get('course_id'),
                'class' => $this->input->get('class'),
                'badge' => $this->input->get('badge'),
            ),
            $this->_body()
        );
        $result = $this->service->overall_class_performance($filters);
        $this->_json(array(
            'success' => true,
            'data' => $result['report'],
        ));
    }

    public function give_monthly_test_reward()
    {
        $p = $this->_perms();
        if (empty($p['improvement_report'])) {
            $this->_json(array('success' => false, 'message' => 'Improvement Report permission required'), 403);
        }
        $result = $this->service->give_monthly_test_reward($this->current_user, $_POST, $_FILES);
        if (empty($result['success'])) {
            $this->_json(array('success' => false, 'message' => $result['message']), 400);
        }
        $this->_json(array('success' => true, 'message' => $result['message']));
    }
}
