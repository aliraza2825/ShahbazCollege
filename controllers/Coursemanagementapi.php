<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Course Management JSON API for React POS shell.
 * Base: /index.php/coursemanagementapi/{method}
 */
class Coursemanagementapi extends CI_Controller {

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
        $this->load->library('course_management_service');
        $this->service = $this->course_management_service;
        if (!$this->service->can_access($this->current_user)) {
            $this->_json(array('success' => false, 'message' => 'Course management access required'), 403);
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

    private function _respond($result, $deny_code = 403, $validation_code = 422)
    {
        if (empty($result['success'])) {
            $code = $validation_code;
            if (isset($result['message']) && stripos($result['message'], 'permission') !== false) {
                $code = $deny_code;
            }
            $this->_json($result, $code);
        }
        $this->_json($result);
    }

    public function meta()
    {
        $this->_json(array('success' => true, 'data' => $this->service->meta($this->current_user)));
    }

    public function courses()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->_respond($this->service->list_courses($this->current_user));
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->_respond($this->service->create_course($this->current_user, $this->_body()));
        }
        $this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
    }

    public function course($id = 0)
    {
        $id = (int) $id;
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->_respond($this->service->get_course($this->current_user, $id));
        }
        if ($_SERVER['REQUEST_METHOD'] === 'PUT' || ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->input->get('_method') === 'PUT')) {
            $this->_respond($this->service->update_course($this->current_user, $id, $this->_body()));
        }
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE' || ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->input->get('_method') === 'DELETE')) {
            $this->_respond($this->service->delete_course($this->current_user, $id));
        }
        $this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
    }

    public function subjects()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $filters = array(
                'course_id' => $this->input->get('course_id'),
            );
            $this->_respond($this->service->list_subjects($this->current_user, $filters));
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->_respond($this->service->create_subject($this->current_user, $this->_body()));
        }
        $this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
    }

    public function subject($id = 0)
    {
        $id = (int) $id;
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->_respond($this->service->get_subject($this->current_user, $id));
        }
        if ($_SERVER['REQUEST_METHOD'] === 'PUT' || ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->input->get('_method') === 'PUT')) {
            $this->_respond($this->service->update_subject($this->current_user, $id, $this->_body()));
        }
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE' || ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->input->get('_method') === 'DELETE')) {
            $this->_respond($this->service->delete_subject($this->current_user, $id));
        }
        $this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
    }

    public function course_details($id = 0)
    {
        $this->_respond($this->service->course_details($id));
    }

    public function subjects_by_course($course_id = 0)
    {
        $period = $this->input->get('period');
        if ($period === null || $period === '') {
            $period = $this->input->get('class_id');
        }
        $this->_respond($this->service->subjects_for_course($this->current_user, $course_id, $period));
    }

    public function chapters()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $filters = array(
                'course_id' => $this->input->get('course_id'),
                'subject_id' => $this->input->get('subject_id'),
            );
            $this->_respond($this->service->list_chapters($this->current_user, $filters));
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->_respond($this->service->create_chapter($this->current_user, $this->_body()));
        }
        $this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
    }

    public function chapter($id = 0)
    {
        $id = (int) $id;
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->_respond($this->service->get_chapter($this->current_user, $id));
        }
        if ($_SERVER['REQUEST_METHOD'] === 'PUT' || ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->input->get('_method') === 'PUT')) {
            $this->_respond($this->service->update_chapter($this->current_user, $id, $this->_body()));
        }
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE' || ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->input->get('_method') === 'DELETE')) {
            $this->_respond($this->service->delete_chapter($this->current_user, $id));
        }
        $this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
    }

    public function chapters_by_subject($id = 0)
    {
        $this->_respond($this->service->chapters_for_subject($id));
    }

    public function topics()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $filters = array(
                'course_id' => $this->input->get('course_id'),
                'subject_id' => $this->input->get('subject_id'),
                'chapter_id' => $this->input->get('chapter_id'),
            );
            $this->_respond($this->service->list_topics($this->current_user, $filters));
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->_respond($this->service->create_topic($this->current_user, $this->_body()));
        }
        $this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
    }

    public function topic($id = 0)
    {
        $id = (int) $id;
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->_respond($this->service->get_topic($this->current_user, $id));
        }
        if ($_SERVER['REQUEST_METHOD'] === 'PUT' || ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->input->get('_method') === 'PUT')) {
            $this->_respond($this->service->update_topic($this->current_user, $id, $this->_body()));
        }
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE' || ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->input->get('_method') === 'DELETE')) {
            $this->_respond($this->service->delete_topic($this->current_user, $id));
        }
        $this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
    }

    public function practical_subjects()
    {
        $body = $this->_body();
        $course_id = isset($body['course_id']) ? $body['course_id'] : 0;
        $this->_respond($this->service->search_practical_subjects($this->current_user, $course_id));
    }

    public function practicals($subject_id = 0)
    {
        $subject_id = (int) $subject_id;
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->_respond($this->service->list_practicals($this->current_user, $subject_id));
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->_respond($this->service->create_practical($this->current_user, $subject_id, $this->_body()));
        }
        $this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
    }

    public function practical($subject_id = 0, $id = 0)
    {
        $subject_id = (int) $subject_id;
        $id = (int) $id;
        if ($_SERVER['REQUEST_METHOD'] === 'PUT' || ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->input->get('_method') === 'PUT')) {
            $this->_respond($this->service->update_practical($this->current_user, $subject_id, $id, $this->_body()));
        }
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE' || ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->input->get('_method') === 'DELETE')) {
            $this->_respond($this->service->delete_practical($this->current_user, $subject_id, $id));
        }
        $this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
    }

    public function book($subject_id = 0)
    {
        $result = $this->service->book_html($this->current_user, $subject_id);
        $this->_respond($result);
    }

    public function subject_questions($subject_id = 0)
    {
        $this->_respond($this->service->subject_all_questions($this->current_user, $subject_id));
    }

    public function question_topics()
    {
        $body = $this->_body();
        $class_id = null;
        if (isset($body['class_id']) && $body['class_id'] !== '') {
            $class_id = $body['class_id'];
        } elseif (isset($body['period']) && $body['period'] !== '') {
            $class_id = $body['period'];
        }
        $this->_respond($this->service->search_question_topics(
            $this->current_user,
            isset($body['course_id']) ? $body['course_id'] : 0,
            isset($body['subject_id']) ? $body['subject_id'] : 0,
            isset($body['chapter_id']) ? $body['chapter_id'] : 0,
            $class_id
        ));
    }

    public function question_hub($topic_id = 0)
    {
        $this->_respond($this->service->question_hub($this->current_user, $topic_id));
    }

    public function topic_data($topic_id = 0)
    {
        $body = $this->_body();
        $video = isset($body['video']) ? $body['video'] : null;
        $this->_respond($this->service->save_topic_data($this->current_user, $topic_id, $body, $video));
    }

    public function question($id = 0)
    {
        $id = (int) $id;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $body = $this->_body();
            $audio = $this->service->upload_recording('audio');
            $this->_respond($this->service->create_mcq($this->current_user, $id, $body, $audio));
        }
        if ($_SERVER['REQUEST_METHOD'] === 'PUT' || ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->input->get('_method') === 'PUT')) {
            $body = $this->_body();
            $audio = $this->service->upload_recording('audio');
            $this->_respond($this->service->update_question($this->current_user, $id, $body, $audio ? $audio : null));
        }
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE' || ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->input->get('_method') === 'DELETE')) {
            $this->_respond($this->service->delete_question($this->current_user, $id));
        }
        $this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
    }

    public function questions_bulk($topic_id = 0)
    {
        $body = $this->_body();
        $rows = array();

        if (isset($body['rows']) && is_array($body['rows'])) {
            $rows = $body['rows'];
        } else {
            $difficulty = isset($body['difficulty']) ? $body['difficulty'] : array();
            $question = isset($body['question']) ? $body['question'] : array();
            $explanation = isset($body['explanation']) ? $body['explanation'] : array();
            if (!is_array($difficulty)) {
                $difficulty = array($difficulty);
            }
            if (!is_array($question)) {
                $question = array($question);
            }
            if (!is_array($explanation)) {
                $explanation = array($explanation);
            }
            $count = max(count($difficulty), count($question), count($explanation));
            for ($i = 0; $i < $count; $i++) {
                $audio = $this->service->upload_recording('audio', $i);
                $rows[] = array(
                    'difficulty' => isset($difficulty[$i]) ? $difficulty[$i] : 'easy',
                    'question' => isset($question[$i]) ? $question[$i] : '',
                    'explanation' => isset($explanation[$i]) ? $explanation[$i] : '',
                    'audio' => $audio,
                );
            }
        }

        $this->_respond($this->service->create_short_questions_bulk($this->current_user, $topic_id, $rows));
    }

    public function word_meanings($topic_id = 0)
    {
        $body = $this->_body();
        $rows = isset($body['rows']) && is_array($body['rows']) ? $body['rows'] : (isset($body['items']) ? $body['items'] : array());
        if (empty($rows) && isset($body['word'])) {
            $rows = array($body);
        }
        $this->_respond($this->service->create_word_meanings($this->current_user, $topic_id, $rows));
    }

    public function videos($topic_id = 0)
    {
        $body = $this->_body();
        $rows = isset($body['rows']) && is_array($body['rows']) ? $body['rows'] : (isset($body['items']) ? $body['items'] : array());
        if (empty($rows) && (isset($body['title']) || isset($body['file']))) {
            $rows = array($body);
        }
        $this->_respond($this->service->create_videos($this->current_user, $topic_id, $rows));
    }

    public function import($topic_id = 0)
    {
        $body = $this->_body();
        $type = isset($body['type']) ? $body['type'] : $this->input->post('type');
        $tmp = isset($_FILES['csv']['tmp_name']) ? $_FILES['csv']['tmp_name'] : '';
        if ($type === '' || $tmp === '') {
            $this->_json(array('success' => false, 'message' => 'CSV file and type are required'), 422);
        }
        $this->_respond($this->service->import_csv($this->current_user, $topic_id, $type, $tmp));
    }

    public function word_meaning($id = 0)
    {
        $id = (int) $id;
        if ($_SERVER['REQUEST_METHOD'] === 'PUT' || ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->input->get('_method') === 'PUT')) {
            $this->_respond($this->service->update_word_meaning($this->current_user, $id, $this->_body()));
        }
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE' || ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->input->get('_method') === 'DELETE')) {
            $this->_respond($this->service->delete_word_meaning($this->current_user, $id));
        }
        $this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
    }

    public function video($id = 0)
    {
        $id = (int) $id;
        if ($_SERVER['REQUEST_METHOD'] === 'PUT' || ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->input->get('_method') === 'PUT')) {
            $this->_respond($this->service->update_video($this->current_user, $id, $this->_body()));
        }
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE' || ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->input->get('_method') === 'DELETE')) {
            $this->_respond($this->service->delete_video($this->current_user, $id));
        }
        $this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
    }

    public function recheck($topic_id = 0)
    {
        $this->_respond($this->service->recheck_topic($this->current_user, $topic_id));
    }

    public function question_status()
    {
        $body = $this->_body();
        $id = isset($body['id']) ? $body['id'] : 0;
        $status = isset($body['status']) ? $body['status'] : 0;
        $this->_respond($this->service->update_question_status($this->current_user, $id, $status));
    }

    public function upload_image()
    {
        $this->_respond($this->service->upload_image($this->current_user, 'image'));
    }
}
