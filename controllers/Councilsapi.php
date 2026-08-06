<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Councils JSON API for React POS shell.
 * Base: /index.php/councilsapi/{method}
 */
class Councilsapi extends CI_Controller {

    private $current_user = null;
    private $service = null;
    private $drill_service = null;
    private $punjab_service = null;

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
        $this->load->library('councils_service');
        $this->load->library('councils_drill_service');
        $this->load->library('punjab_council_service');
        $this->service = $this->councils_service;
        $this->drill_service = $this->councils_drill_service;
        $this->punjab_service = $this->punjab_council_service;
        if (!$this->service->can_access($this->current_user)) {
            $this->_json(array('success' => false, 'message' => 'Councils access required'), 403);
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

    private function _require_admin_cap($key)
    {
        $p = $this->_perms();
        if (empty($p[$key])) {
            $this->_json(array('success' => false, 'message' => 'Permission denied'), 403);
        }
    }

    private function _require_council_report()
    {
        $p = $this->_perms();
        if (empty($p['council_report'])) {
            $this->_json(array('success' => false, 'message' => 'Council report permission required'), 403);
        }
    }

    private function _require_add_expense()
    {
        $p = $this->_perms();
        if (empty($p['council_report_add_expense'])) {
            $this->_json(array('success' => false, 'message' => 'Add expense permission required'), 403);
        }
    }

    private function _drill_params()
    {
        return array(
            'page' => $this->input->get('page'),
            'course_id' => $this->input->get('course_id'),
            'session' => $this->input->get('session'),
            'exam_no' => $this->input->get('exam_no'),
            'class' => $this->input->get('class'),
            'type' => $this->input->get('type'),
            'sequence_id' => $this->input->get('sequence_id'),
            'exam_sequence_id' => $this->input->get('exam_sequence_id'),
        );
    }

    private function _multipart_post()
    {
        return $this->input->post() ? $this->input->post() : array();
    }

    public function meta()
    {
        $this->_json(array('success' => true, 'data' => $this->service->meta($this->current_user)));
    }

    public function report()
    {
        $p = $this->_perms();
        if (empty($p['council_report'])) {
            $this->_json(array('success' => false, 'message' => 'Council report permission required'), 403);
        }
        $status = $this->input->get('status');
        if ($status === null || $status === '') {
            $status = isset($this->_body()['status']) ? $this->_body()['status'] : 'Active';
        }
        $result = $this->service->report($this->current_user, $status);
        $this->_json(array(
            'success' => true,
            'data' => $result['rows'],
            'total_liability' => $result['total_liability'],
        ));
    }

    public function report_index()
    {
        $p = $this->_perms();
        if (empty($p['council_report'])) {
            $this->_json(array('success' => false, 'message' => 'Council report permission required'), 403);
        }
        $status = $this->input->get('status');
        if ($status === null || $status === '') {
            $status = 'Active';
        }
        $this->_json(array(
            'success' => true,
            'data' => $this->service->report_index($this->current_user, $status),
        ));
    }

    public function report_row($id = null)
    {
        $p = $this->_perms();
        if (empty($p['council_report'])) {
            $this->_json(array('success' => false, 'message' => 'Council report permission required'), 403);
        }
        if (!$id) {
            $this->_json(array('success' => false, 'message' => 'Exam sequence id required'), 400);
        }
        $row = $this->service->report_row($this->current_user, (int) $id);
        if (!$row) {
            $this->_json(array('success' => false, 'message' => 'Exam sequence not found'), 404);
        }
        $this->_json(array('success' => true, 'data' => $row));
    }

    public function councils()
    {
        $this->_require_admin_cap('manage_councils');
        $this->_json(array('success' => true, 'data' => $this->service->list_councils()));
    }

    public function council($id = null)
    {
        $this->_require_admin_cap('manage_councils');
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $this->service->delete_council($id);
            $this->_json(array('success' => true, 'message' => 'Council deleted'));
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $body = $this->_body();
            if ($id) {
                $body['council_id'] = $id;
            }
            $saved = $this->service->save_council($body);
            $this->_json(array('success' => true, 'message' => $saved['message'], 'id' => $saved['council_id']));
        }
        $this->_json(array('success' => true, 'data' => $this->service->get_council($id)));
    }

    public function paper_types()
    {
        $this->_require_admin_cap('manage_paper_types');
        $this->_json(array('success' => true, 'data' => $this->service->list_paper_types()));
    }

    public function paper_type($id = null)
    {
        $this->_require_admin_cap('manage_paper_types');
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $this->service->delete_paper_type($id);
            $this->_json(array('success' => true, 'message' => 'Paper type deleted'));
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $body = $this->_body();
            if ($id) {
                $body['paper_type_id'] = $id;
            }
            $saved = $this->service->save_paper_type($body);
            $this->_json(array('success' => true, 'message' => $saved['message'], 'id' => $saved['paper_type_id']));
        }
        $row = $this->db->get_where('paper_types', array('paper_type_id' => (int) $id))->row_array();
        $this->_json(array('success' => true, 'data' => $row));
    }

    public function council_exams()
    {
        $this->_require_admin_cap('manage_council_exams');
        $this->_json(array('success' => true, 'data' => $this->service->list_council_exams()));
    }

    public function council_exam($id = null)
    {
        $this->_require_admin_cap('manage_council_exams');
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $this->service->delete_council_exam($id);
            $this->_json(array('success' => true, 'message' => 'Council exam rule deleted'));
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $body = $this->_body();
            if ($id) {
                $body['council_exam_id'] = $id;
            }
            $saved = $this->service->save_council_exam($body);
            $this->_json(array('success' => true, 'message' => $saved['message'], 'id' => $saved['council_exam_id']));
        }
        $this->_json(array('success' => true, 'data' => $this->service->get_council_exam($id)));
    }

    public function result_rules()
    {
        $this->_require_admin_cap('manage_result_rules');
        $this->_json(array('success' => true, 'data' => $this->service->list_result_rules()));
    }

    public function result_rule($id = null)
    {
        $this->_require_admin_cap('manage_result_rules');
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $this->service->delete_result_rule($id);
            $this->_json(array('success' => true, 'message' => 'Result rule deleted'));
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $body = $this->_body();
            if ($id) {
                $body['council_result_rule_id'] = $id;
            }
            $saved = $this->service->save_result_rule($body);
            $this->_json(array('success' => true, 'message' => $saved['message'], 'id' => $saved['council_result_rule_id']));
        }
        $row = $this->db->get_where('council_result_rules', array('council_result_rule_id' => (int) $id))->row_array();
        $this->_json(array('success' => true, 'data' => $row));
    }

    public function sequences()
    {
        $this->_require_admin_cap('manage_sequences');
        $this->_json(array('success' => true, 'data' => $this->service->list_sequences()));
    }

    public function exam_sequences()
    {
        $this->_require_admin_cap('manage_exam_sequences');
        $status = $this->input->get('status');
        $this->_json(array('success' => true, 'data' => $this->service->list_exam_sequences($status)));
    }

    public function course_subjects($course_id = null)
    {
        $this->_require_admin_cap('manage_council_exams');
        $this->_json(array('success' => true, 'data' => $this->service->list_course_subjects($course_id)));
    }

    public function sequence($id = null)
    {
        $this->_require_admin_cap('manage_sequences');
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            try {
                $result = $this->service->delete_sequence($id);
                $this->_json(array('success' => true, 'message' => $result['message']));
            } catch (Exception $e) {
                $this->_json(array('success' => false, 'message' => $e->getMessage()), 400);
            }
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $body = $this->_body();
            if ($id) {
                $body['council_sequence_id'] = $id;
            }
            $saved = $this->service->save_sequence($body);
            $this->_json(array('success' => true, 'message' => $saved['message'], 'id' => $saved['council_sequence_id']));
            return;
        }
        $this->_json(array('success' => true, 'data' => $this->service->get_sequence($id)));
    }

    public function exam_sequence($id = null)
    {
        $this->_require_admin_cap('manage_exam_sequences');
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $result = $this->service->delete_exam_sequence($id);
            $this->_json(array('success' => true, 'message' => $result['message']));
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $body = $this->_body();
            if ($id) {
                $body['id'] = $id;
            }
            try {
                $saved = $this->service->save_exam_sequence($body);
                $this->_json(array('success' => true, 'message' => $saved['message'], 'id' => $saved['id']));
            } catch (Exception $e) {
                $this->_json(array('success' => false, 'message' => $e->getMessage()), 400);
            }
            return;
        }
        $this->_json(array('success' => true, 'data' => $this->service->get_exam_sequence($id)));
    }

    public function exam_sequence_status($id = null)
    {
        $this->_require_admin_cap('manage_exam_sequences');
        $body = $this->_body();
        $status = isset($body['status']) ? $body['status'] : 'Active';
        $result = $this->service->set_exam_sequence_status($id, $status);
        $this->_json(array('success' => true, 'message' => $result['message']));
    }

    public function fee_rule($id = null)
    {
        $this->_require_admin_cap('manage_exam_sequences');
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $result = $this->service->delete_fee_rule($id);
            $this->_json(array('success' => true, 'message' => $result['message']));
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $body = $this->_body();
            if ($id) {
                $body['fee_rule_id'] = $id;
            }
            $saved = $this->service->save_fee_rule($body);
            $this->_json(array('success' => true, 'message' => $saved['message'], 'id' => $saved['id']));
            return;
        }
        $this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
    }

    // ── Report drill-down pages ───────────────────────────────────────────────

    public function drill()
    {
        $this->_require_council_report();
        $params = $this->_drill_params();
        if (empty($params['page']) || empty($params['course_id']) || $params['exam_no'] === null || $params['exam_no'] === '' || $params['class'] === null || $params['class'] === '') {
            $this->_json(array('success' => false, 'message' => 'page, course_id, exam_no, and class are required'), 400);
        }
        $result = $this->drill_service->drill($this->current_user, $params);
        if (empty($result['success'])) {
            $this->_json($result, 400);
        }
        $this->_json($result);
    }

    public function student_fee_status()
    {
        $this->_require_council_report();
        $body = $this->_body();
        $required = array('student_id', 'course_id', 'exam_no', 'class', 'sequence_id', 'exam_sequence');
        foreach ($required as $key) {
            if (!isset($body[$key]) || $body[$key] === '') {
                $this->_json(array('success' => false, 'message' => $key . ' is required'), 400);
            }
        }
        $this->_json($this->drill_service->student_fee_status($this->current_user, $body));
    }

    public function add_expense()
    {
        $this->_require_council_report();
        $this->_require_add_expense();
        $data = $this->_multipart_post();
        $this->_json($this->drill_service->add_expense($this->current_user, $data));
    }

    public function save_informed()
    {
        $this->_require_council_report();
        $data = $this->_multipart_post();
        $this->_json($this->drill_service->save_informed($this->current_user, $data));
    }

    public function add_sequence_information()
    {
        $this->_require_council_report();
        $data = $this->_multipart_post();
        $this->_json($this->drill_service->add_sequence_information($this->current_user, $data));
    }

    public function save_roll_no()
    {
        $this->_require_council_report();
        $data = $this->_multipart_post();
        $this->_json($this->drill_service->save_roll_no($this->current_user, $data));
    }

    public function save_result()
    {
        $this->_require_council_report();
        $body = $this->_body();
        $this->_json($this->drill_service->save_result($this->current_user, $body));
    }

    public function create_fee_for_all()
    {
        $this->_require_council_report();
        $p = $this->_perms();
        if (empty($p['council_report_add_fee']) && empty($p['is_admin'])) {
            $this->_json(array('success' => false, 'message' => 'Council report add fee permission required'), 403);
        }
        $body = $this->_body();
        $this->_json($this->drill_service->create_fee_for_all($this->current_user, $body));
    }

    public function upload_roll_csv()
    {
        $this->_require_council_report();
        $this->_json($this->punjab_service->upload_roll_csv($this->_multipart_post()));
    }

    public function upload_roll_slips()
    {
        $this->_require_council_report();
        $this->_json($this->punjab_service->upload_roll_slip_images($this->_multipart_post()));
    }

    public function upload_result_csv()
    {
        $this->_require_council_report();
        $this->_json($this->punjab_service->upload_result_csv($this->_multipart_post()));
    }

    public function upload_result_cards()
    {
        $this->_require_council_report();
        $this->_json($this->punjab_service->upload_result_card_images($this->_multipart_post()));
    }
}
