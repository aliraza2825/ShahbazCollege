<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Reports JSON API for React POS shell.
 * Base: /index.php/reportsapi/{method}
 */
class Reportsapi extends CI_Controller {

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
        $this->load->library('reports_service');
        $this->service = $this->reports_service;
        if (!$this->service->can_access($this->current_user)) {
            $this->_json(array('success' => false, 'message' => 'Reports access required'), 403);
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

    public function meta()
    {
        $this->_json(array('success' => true, 'data' => $this->service->meta($this->current_user)));
    }

    public function course_subjects($course_id)
    {
        $this->_json(array(
            'success' => true,
            'data' => $this->service->course_subjects($course_id),
        ));
    }

    public function students_fee_problem()
    {
        $result = $this->service->students_fee_problem($this->current_user);
        if (empty($result['success'])) {
            $this->_json($result, 403);
        }
        $this->_json($result);
    }

    public function campus_fee_problem($campus_id)
    {
        $result = $this->service->campus_fee_problem($this->current_user, $campus_id);
        if (empty($result['success'])) {
            $this->_json($result, 403);
        }
        $this->_json($result);
    }

    public function discount_report()
    {
        $body = $this->_body();
        $result = $this->service->discount_report(
            $this->current_user,
            isset($body['from_date']) ? $body['from_date'] : '',
            isset($body['to_date']) ? $body['to_date'] : ''
        );
        if (empty($result['success'])) {
            $this->_json($result, 403);
        }
        $this->_json($result);
    }

    public function sms_devices()
    {
        $this->_json($this->service->sms_devices_data());
    }

    public function teacher_questions()
    {
        $body = $this->_body();
        $result = $this->service->teacher_questions_report(
            isset($body['course_id']) ? $body['course_id'] : 0,
            isset($body['subject_id']) ? $body['subject_id'] : 0,
            isset($body['teacher_id']) ? $body['teacher_id'] : 0,
            isset($body['from_date']) ? $body['from_date'] : date('Y-m-d'),
            isset($body['to_date']) ? $body['to_date'] : date('Y-m-d')
        );
        if (empty($result['success'])) {
            $this->_json($result, 422);
        }
        $this->_json($result);
    }

    public function agent_statement()
    {
        $body = $this->_body();
        $result = $this->service->agent_view_statement(
            $this->current_user,
            isset($body['from_date']) ? $body['from_date'] : '',
            isset($body['amount']) ? $body['amount'] : '',
            isset($body['account_id']) ? $body['account_id'] : 0
        );
        if (empty($result['success'])) {
            $this->_json($result, 403);
        }
        $this->_json($result);
    }

    public function agent_statement_coo()
    {
        $body = $this->_body();
        $result = $this->service->agent_view_statement_coo(
            $this->current_user,
            isset($body['from_date']) ? $body['from_date'] : '',
            isset($body['to_date']) ? $body['to_date'] : '',
            isset($body['account_id']) ? $body['account_id'] : 0
        );
        if (empty($result['success'])) {
            $this->_json($result, 403);
        }
        $this->_json($result);
    }

    public function students_backup()
    {
        $body = $this->_body();
        $result = $this->service->students_backup_report(
            $this->current_user,
            isset($body['backup_date']) ? $body['backup_date'] : date('Y-m-d')
        );
        if (empty($result['success'])) {
            $this->_json($result, 403);
        }
        $this->_json($result);
    }

    public function struckoff_report()
    {
        $body = $this->_body();
        $result = $this->service->struckoff_report(
            $this->current_user,
            isset($body['strucktype']) ? $body['strucktype'] : '',
            isset($body['from_date']) ? $body['from_date'] : date('Y-m-d'),
            isset($body['to_date']) ? $body['to_date'] : date('Y-m-d')
        );
        if (empty($result['success'])) {
            $this->_json($result, 403);
        }
        $this->_json($result);
    }

    public function struckoff_student_view($student_id = 0)
    {
        $result = $this->service->struckoff_student_view($this->current_user, (int)$student_id);
        if (empty($result['success'])) {
            $this->_json($result, empty($result['message']) || $result['message'] === 'Access denied' ? 403 : 404);
        }
        $this->_json($result);
    }

    public function struckoff_process_detail($student_id = 0, $process_count = 0)
    {
        $result = $this->service->struckoff_process_detail(
            $this->current_user,
            (int)$student_id,
            (int)$process_count
        );
        if (empty($result['success'])) {
            $this->_json($result, empty($result['message']) || $result['message'] === 'Access denied' ? 403 : 404);
        }
        $this->_json($result);
    }

    public function struckoff_letter($student_id = 0)
    {
        $result = $this->service->struckoff_letter_html($this->current_user, (int)$student_id);
        if (empty($result['success'])) {
            $this->_json($result, empty($result['message']) || $result['message'] === 'Access denied' ? 403 : 404);
        }
        $this->_json($result);
    }

    public function coo_cash_report()
    {
        $body = $this->_body();
        $result = $this->service->coo_cash_report(
            $this->current_user,
            isset($body['to_date']) ? $body['to_date'] : date('Y-m-d')
        );
        if (empty($result['success'])) {
            $this->_json($result, 403);
        }
        $this->_json($result);
    }
}
