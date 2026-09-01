<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Expenses JSON API for React POS shell.
 * Base: /index.php/expensesapi/{method}
 */
class Expensesapi extends CI_Controller {

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
        $this->load->library('expenses_service');
        $this->service = $this->expenses_service;
        if (!$this->service->can_access($this->current_user)) {
            $this->_json(array('success' => false, 'message' => 'Expenses access required'), 403);
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
            'add_campuses' => $full['add_campuses'],
            'legacy_root' => $full['legacy_root'],
        ));
    }

    public function list_expenses()
    {
        $p = $this->_perms();
        if (empty($p['all'])) {
            $this->_json(array('success' => false, 'message' => 'All Expenses permission required'), 403);
        }

        $filters = array_merge(
            array(
                'from_date' => $this->input->get('from_date'),
                'to_date' => $this->input->get('to_date'),
                'campus_id' => $this->input->get('campus_id'),
                'setype' => $this->input->get('setype'),
            ),
            $this->_body()
        );

        $result = $this->service->list_expenses($this->current_user, $filters);
        $this->_json(array(
            'success' => true,
            'data' => $result,
        ));
    }

    public function dashboard()
    {
        $p = $this->_perms();
        if (empty($p['all'])) {
            $this->_json(array('success' => false, 'message' => 'All Expenses permission required'), 403);
        }

        $filters = array_merge(
            array(
                'from_date' => $this->input->get('from_date'),
                'to_date' => $this->input->get('to_date'),
                'campus_id' => $this->input->get('campus_id'),
            ),
            $this->_body()
        );

        try {
            $data = $this->service->dashboard_analytics($this->current_user, $filters);
            $this->_json(array(
                'success' => true,
                'data' => $data,
            ));
        } catch (Exception $e) {
            $this->_json(array(
                'success' => false,
                'message' => 'Dashboard failed: ' . $e->getMessage(),
            ), 500);
        }
    }

    public function expense_history()
    {
        $p = $this->_perms();
        if (empty($p['all'])) {
            $this->_json(array('success' => false, 'message' => 'All Expenses permission required'), 403);
        }

        $campus_id = $this->input->get('campus_id');
        $category_id = $this->input->get('category_id');
        $this->_json(array(
            'success' => true,
            'data' => $this->service->expense_history($campus_id, $category_id),
        ));
    }

    public function change_approve_status()
    {
        $body = $this->_body();
        $result = $this->service->change_approve_status(
            $this->current_user,
            isset($body['expense_id']) ? $body['expense_id'] : 0,
            isset($body['status']) ? $body['status'] : '',
            isset($body['last_edit']) ? $body['last_edit'] : $this->current_user['first_name'] . ' ' . $this->current_user['last_name']
        );
        if (empty($result['success'])) {
            $this->_json($result, 400);
        }
        $this->_json($result);
    }

    public function request_reverse()
    {
        $body = $this->_body();
        $result = $this->service->request_reverse(
            $this->current_user,
            isset($body['expense_id']) ? $body['expense_id'] : 0,
            isset($body['reason']) ? $body['reason'] : ''
        );
        if (empty($result['success'])) {
            $this->_json($result, 400);
        }
        $this->_json($result);
    }

    public function delete_expense()
    {
        $body = $this->_body();
        $expense_id = isset($body['expense_id']) ? $body['expense_id'] : $this->input->get('expense_id');
        $result = $this->service->delete_expense($this->current_user, $expense_id);
        if (empty($result['success'])) {
            $this->_json($result, 400);
        }
        $this->_json($result);
    }

    public function add_form_meta()
    {
        $p = $this->_perms();
        if (empty($p['add'])) {
            $this->_json(array('success' => false, 'message' => 'Add Expense permission required'), 403);
        }
        $this->_json(array_merge(array('success' => true), $this->service->add_form_meta($this->current_user)));
    }

    public function campus_classes()
    {
        $campus_id = $this->input->get('campus_id');
        if ($campus_id === null || $campus_id === '') {
            $body = $this->_body();
            $campus_id = isset($body['campus_id']) ? $body['campus_id'] : 0;
        }
        $this->_json(array(
            'success' => true,
            'data' => $this->service->campus_classes($campus_id),
        ));
    }

    public function campus_staff()
    {
        $campus_id = $this->input->get('campus_id');
        if ($campus_id === null || $campus_id === '') {
            $body = $this->_body();
            $campus_id = isset($body['campus_id']) ? $body['campus_id'] : 0;
        }
        $this->_json(array(
            'success' => true,
            'data' => $this->service->campus_staff($campus_id),
        ));
    }

    public function categories_for_campus()
    {
        $campus_id = $this->input->get('campus_id');
        if ($campus_id === null || $campus_id === '') {
            $body = $this->_body();
            $campus_id = isset($body['campus_id']) ? $body['campus_id'] : 0;
        }
        $this->_json(array(
            'success' => true,
            'data' => $this->service->categories_for_campus($campus_id),
        ));
    }

    public function council_exam_numbers()
    {
        $selected_class = $this->input->get('selected_class');
        if ($selected_class === null || $selected_class === '') {
            $body = $this->_body();
            $selected_class = isset($body['selected_class']) ? $body['selected_class'] : 1;
        }
        $this->_json(array(
            'success' => true,
            'data' => $this->service->council_exam_numbers($selected_class),
        ));
    }

    public function council_exam_no_for_class()
    {
        $class_id = $this->input->get('class_id');
        if ($class_id === null || $class_id === '') {
            $body = $this->_body();
            $class_id = isset($body['class_id']) ? $body['class_id'] : 0;
        }
        $this->_json(array(
            'success' => true,
            'data' => $this->service->council_exam_no_for_class($class_id),
        ));
    }

    public function council_fee_students_result()
    {
        $body = array_merge($this->_body(), array(
            'campus_id' => $this->input->get('campus_id'),
            'council_exam_no' => $this->input->get('council_exam_no'),
            'campus_class' => $this->input->get('campus_class'),
        ));
        $this->_json(array(
            'success' => true,
            'data' => $this->service->council_fee_students_result(
                isset($body['campus_id']) ? $body['campus_id'] : 0,
                isset($body['council_exam_no']) ? $body['council_exam_no'] : '',
                isset($body['campus_class']) ? $body['campus_class'] : '1'
            ),
        ));
    }

    public function council_fee_students_class()
    {
        $body = array_merge($this->_body(), array(
            'campus_id' => $this->input->get('campus_id'),
            'class_id' => $this->input->get('class_id'),
            'exam_no' => $this->input->get('exam_no'),
        ));
        $this->_json(array(
            'success' => true,
            'data' => $this->service->council_fee_students_class(
                isset($body['campus_id']) ? $body['campus_id'] : 0,
                isset($body['class_id']) ? $body['class_id'] : 0,
                isset($body['exam_no']) ? $body['exam_no'] : ''
            ),
        ));
    }

    public function insert_expense()
    {
        $p = $this->_perms();
        if (empty($p['add'])) {
            $this->_json(array('success' => false, 'message' => 'Add Expense permission required'), 403);
        }

        $image = '';
        if (!empty($_FILES['image']['name'])) {
            $config['upload_path'] = 'uploads/';
            $config['allowed_types'] = 'gif|jpg|jpeg|png';
            $this->load->library('upload', $config);
            $this->upload->initialize($config);
            $this->upload->set_allowed_types('*');
            if ($this->upload->do_upload('image')) {
                $upload_data = $this->upload->data();
                if (!empty($upload_data['file_name'])) {
                    $image = $upload_data['file_name'];
                }
            }
        }

        $data = $this->input->post();
        if (empty($data)) {
            $data = $this->_body();
        }

        if (isset($data['expense_category_id']) && !is_array($data['expense_category_id'])) {
            $decoded = json_decode($data['expense_category_id'], true);
            if (is_array($decoded)) {
                $data['expense_category_id'] = $decoded;
            } else {
                $data['expense_category_id'] = array_values(array_filter(array_map('intval', explode(',', (string) $data['expense_category_id']))));
            }
        }

        $result = $this->service->insert_expense($this->current_user, $data, $image);
        if (empty($result['success'])) {
            $this->_json($result, 400);
        }
        $this->_json($result);
    }

    public function get_expense()
    {
        $p = $this->_perms();
        if (empty($p['edit'])) {
            $this->_json(array('success' => false, 'message' => 'Expense edit permission required'), 403);
        }
        $expense_id = $this->input->get('expense_id');
        if ($expense_id === null || $expense_id === '') {
            $body = $this->_body();
            $expense_id = isset($body['expense_id']) ? $body['expense_id'] : 0;
        }
        $result = $this->service->get_expense_detail($this->current_user, $expense_id);
        if (empty($result['success'])) {
            $this->_json($result, 400);
        }
        $this->_json($result);
    }

    public function update_expense()
    {
        $p = $this->_perms();
        if (empty($p['edit'])) {
            $this->_json(array('success' => false, 'message' => 'Expense edit permission required'), 403);
        }

        $image = '';
        if (!empty($_FILES['img']['name'])) {
            $config['upload_path'] = 'uploads/';
            $config['allowed_types'] = 'gif|jpg|jpeg|png';
            $this->load->library('upload', $config);
            $this->upload->initialize($config);
            $this->upload->set_allowed_types('*');
            if ($this->upload->do_upload('img')) {
                $upload_data = $this->upload->data();
                if (!empty($upload_data['file_name'])) {
                    $image = $upload_data['file_name'];
                }
            }
        }

        $data = $this->input->post();
        if (empty($data)) {
            $data = $this->_body();
        }

        $expense_id = isset($data['expense_id']) ? $data['expense_id'] : 0;
        if (isset($data['expense_category_id']) && !is_array($data['expense_category_id'])) {
            $decoded = json_decode($data['expense_category_id'], true);
            if (is_array($decoded)) {
                $data['expense_category_id'] = $decoded;
            } else {
                $data['expense_category_id'] = array_values(array_filter(array_map('intval', explode(',', (string) $data['expense_category_id']))));
            }
        }

        $result = $this->service->update_expense($this->current_user, $expense_id, $data, $image);
        if (empty($result['success'])) {
            $this->_json($result, 400);
        }
        $this->_json($result);
    }

    public function report_meta()
    {
        if ($this->current_user['role'] !== 'Admin') {
            $this->_json(array('success' => false, 'message' => 'Admin access required'), 403);
        }
        $this->_json(array_merge(array('success' => true), $this->service->report_meta($this->current_user)));
    }

    public function advertising_expenses()
    {
        if ($this->current_user['role'] !== 'Admin') {
            $this->_json(array('success' => false, 'message' => 'Admin access required'), 403);
        }
        $body = array_merge($this->_body(), array(
            'from_date' => $this->input->get('from_date'),
            'to_date' => $this->input->get('to_date'),
        ));
        $this->_json($this->service->advertising_expenses(
            $this->current_user,
            isset($body['from_date']) ? $body['from_date'] : '',
            isset($body['to_date']) ? $body['to_date'] : ''
        ));
    }

    public function report_headwise()
    {
        if ($this->current_user['role'] !== 'Admin') {
            $this->_json(array('success' => false, 'message' => 'Admin access required'), 403);
        }
        $body = $this->_body();
        $filters = array(
            'from_date' => isset($body['from_date']) ? $body['from_date'] : $this->input->get('from_date'),
            'to_date' => isset($body['to_date']) ? $body['to_date'] : $this->input->get('to_date'),
            'campus_ids' => isset($body['campus_ids']) ? $body['campus_ids'] : array(),
            'categories' => isset($body['categories']) ? $body['categories'] : array(),
        );
        $this->_json($this->service->report_headwise($this->current_user, $filters));
    }

    public function report_headwise_details()
    {
        if ($this->current_user['role'] !== 'Admin') {
            $this->_json(array('success' => false, 'message' => 'Admin access required'), 403);
        }
        $body = $this->_body();
        $filters = array(
            'from_date' => isset($body['from_date']) ? $body['from_date'] : $this->input->get('from_date'),
            'to_date' => isset($body['to_date']) ? $body['to_date'] : $this->input->get('to_date'),
            'campus_id' => isset($body['campus_id']) ? $body['campus_id'] : $this->input->get('campus_id'),
            'category_id' => isset($body['category_id']) ? $body['category_id'] : $this->input->get('category_id'),
        );
        $result = $this->service->report_headwise_details($this->current_user, $filters);
        if (empty($result['success'])) {
            $this->_json($result, 400);
        }
        $this->_json($result);
    }

    public function report_subhead()
    {
        if ($this->current_user['role'] !== 'Admin') {
            $this->_json(array('success' => false, 'message' => 'Admin access required'), 403);
        }
        $body = $this->_body();
        $filters = array(
            'from_date' => isset($body['from_date']) ? $body['from_date'] : $this->input->get('from_date'),
            'to_date' => isset($body['to_date']) ? $body['to_date'] : $this->input->get('to_date'),
            'campus_id' => isset($body['campus_id']) ? $body['campus_id'] : $this->input->get('campus_id'),
            'category_id' => isset($body['category_id']) ? $body['category_id'] : (isset($body['categories']) ? $body['categories'] : $this->input->get('category_id')),
        );
        $this->_json($this->service->report_subhead($this->current_user, $filters));
    }

    public function list_categories()
    {
        $campus_id = $this->input->get('campus_id');
        if ($campus_id === null || $campus_id === '') {
            $body = $this->_body();
            $campus_id = isset($body['campus_id']) ? $body['campus_id'] : null;
        }
        $result = $this->service->list_categories($this->current_user, $campus_id);
        if (empty($result['success'])) {
            $this->_json($result, 403);
        }
        $this->_json($result);
    }

    public function add_expense_category()
    {
        $body = $this->_body();
        $result = $this->service->add_expense_category($this->current_user, $body);
        if (empty($result['success'])) {
            $this->_json($result, 400);
        }
        $this->_json($result);
    }
}
