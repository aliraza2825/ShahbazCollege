<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Rules JSON API for React POS shell.
 * Base: /index.php/rulesapi/{method}
 */
class Rulesapi extends CI_Controller {

	private $current_user = null;

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
		// Legacy Rules sidebar is Admin-only.
		if (!$this->_is_admin()) {
			$this->_json(array('success' => false, 'message' => 'Admin access required'), 403);
		}
	}

	private function _cors()
	{
		$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
		$allowed = array(
			'http://localhost:5173', 'http://localhost:4173', 'http://127.0.0.1:5173',
			'https://pos.shahbazcollegeofpharmacy.edu.pk', 'http://pos.shahbazcollegeofpharmacy.edu.pk',
			'https://shahbazcollegeofpharmacy.edu.pk', 'http://shahbazcollegeofpharmacy.edu.pk',
		);
		if ($origin !== '' && (in_array($origin, $allowed) || preg_match('/^https?:\\/\\/([a-z0-9-]+\\.)?shahbazcollegeofpharmacy\\.edu\\.pk(:\\d+)?$/i', $origin))) {
			header('Access-Control-Allow-Origin: ' . $origin);
		} elseif ($origin === '' || $origin === '*') {
			header('Access-Control-Allow-Origin: *');
		} elseif (preg_match('/^https?:\\/\\/(localhost|127\\.0\\.0\\.1)(:\\d+)?$/', $origin)) {
			header('Access-Control-Allow-Origin: ' . $origin);
		}
		header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
		header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Pos-Token');
		header('Access-Control-Allow-Credentials: true');
	}

	private function _eligibility_table()
	{
		foreach (array('eligibilty_admission_rules', 'eligibility_admission_rules') as $table) {
			if ($this->_table_exists($table)) {
				return $table;
			}
		}
		return null;
	}

	private function _safe_query($callback, $fallback = array())
	{
		try {
			return $callback();
		} catch (Exception $e) {
			return $fallback;
		}
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

	private function _is_admin()
	{
		return isset($this->current_user['role']) && $this->current_user['role'] === 'Admin';
	}

	private function _user_name()
	{
		$name = trim($this->current_user['first_name'] . ' ' . $this->current_user['last_name']);
		return $name !== '' ? $name : 'User';
	}

	private function _table_exists($table)
	{
		return $this->db->table_exists($table);
	}

	private function _field_exists($table, $field)
	{
		return $this->_table_exists($table) && $this->db->field_exists($field, $table);
	}

	private function _expense_category_label($expense_category_id)
	{
		$child = $this->db->get_where('expense_category', array('expense_category_id' => $expense_category_id))->row_array();
		if (!$child) {
			return '';
		}
		if (!empty($child['sub_of']) && (int)$child['sub_of'] !== 0) {
			$main = $this->db->get_where('expense_category', array('expense_category_id' => $child['sub_of']))->row_array();
			if ($main) {
				return $main['name'] . ' => ' . $child['name'];
			}
		}
		return $child['name'];
	}

	private function _body_val($body, $key, $default = null)
	{
		return isset($body[$key]) ? $body[$key] : $default;
	}

	private function _implode_field($value)
	{
		if (is_array($value)) {
			return implode(',', $value);
		}
		return $value;
	}

	public function meta()
	{
		$teachers = $this->db
			->select('users.*, designations.designation_name, departments.department_name')
			->join('designations', 'designations.designation_id = users.designation_id', 'left')
			->join('departments', 'departments.department_id = users.department_id', 'left')
			->get_where('users', 'users.status = 1 and departments.department_id = 13')
			->result_array();

		$this->_json(array(
			'success' => true,
			'permissions' => array(
				'is_admin' => $this->_is_admin(),
				'sidebar' => $this->_is_admin(),
			),
			'campuses' => $this->db->get_where('campuses', array('status' => 1))->result_array(),
			'courses' => $this->db->get('courses')->result_array(),
			'accounts' => $this->db->get_where('accounts', array('id >' => '0', 'type' => 0))->result_array(),
			'teachers' => $teachers,
			'product_names' => $this->db->get_where('product_names', array('has_sub' => 0))->result_array(),
			'legacy_root' => base_url(),
			'user_name' => $this->_user_name(),
			'is_admin' => $this->_is_admin(),
		));
	}

	public function campus_rules()
	{
		$this->db->select('campus_rules.*, campuses.campus_name');
		$this->db->from('campus_rules');
		$this->db->join('campuses', 'campuses.campus_id = campus_rules.campus_id', 'left');
		$rows = $this->db->get()->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function campus_rule($id = null)
	{
		$id = (int)$id;
		$row = $this->db->get_where('campus_rules', array('campus_rule_id' => $id))->row_array();
		if (!$row) {
			$this->_json(array('success' => false, 'message' => 'Not found'), 404);
		}
		$this->_json(array('success' => true, 'data' => $row));
	}

	public function save_campus_rules()
	{
		$body = $this->_body();
		$campus_id = $this->_body_val($body, 'campus_id');
		if (!$campus_id) {
			$this->_json(array('success' => false, 'message' => 'campus_id required'), 422);
		}

		$course_ids = $this->_body_val($body, 'course_ids', array());
		$bank_name = $this->_body_val($body, 'bank_name', array());
		$account_title = $this->_body_val($body, 'account_title', array());
		$account_number = $this->_body_val($body, 'account_number', array());
		$start_time = $this->_body_val($body, 'start_time', array());
		$end_time = $this->_body_val($body, 'end_time', array());

		$this->db->set('campus_id', $campus_id);
		$this->db->set('campus_property', $this->_body_val($body, 'campus_property'));
		$this->db->set('campus_property_rent', $this->_body_val($body, 'campus_property_rent'));
		$this->db->set('campus_property_rent_increase_after', $this->_body_val($body, 'campus_property_rent_increase_after'));
		$this->db->set('campus_property_rent_increase_percentage', $this->_body_val($body, 'campus_property_rent_increase_percentage'));
		$this->db->set('campus_property_rent_increase_month', $this->_body_val($body, 'campus_property_rent_increase_month'));
		$this->db->set('bank_fee', $this->_body_val($body, 'bank_fee'));
		$this->db->set('college_fee', $this->_body_val($body, 'college_fee'));
		$this->db->set('course_ids', $this->_implode_field($course_ids));
		$this->db->set('start_time', json_encode($start_time));
		$this->db->set('end_time', json_encode($end_time));
		$this->db->set('bank_name', $this->_implode_field($bank_name));
		$this->db->set('account_title', $this->_implode_field($account_title));
		$this->db->set('account_number', $this->_implode_field($account_number));

		$check_campus = $this->db->get_where('campus_rules', array('campus_id' => $campus_id))->result_array();
		if (count($check_campus) > 0) {
			$this->db->where('campus_id', $campus_id);
			$this->db->update('campus_rules');
		} else {
			$this->db->insert('campus_rules');
		}

		$this->_json(array('success' => true, 'message' => 'Campus Rules Updated Successfully.'));
	}

	public function course_sessions()
	{
		$course_id = $this->input->get('course_id');
		if (!$course_id) {
			$this->_json(array('success' => false, 'message' => 'course_id required'), 422);
		}
		$this->db->group_by('classes.session');
		$rows = $this->db->get_where('classes', array('course_id' => $course_id))->result_array();
		$sessions = array();
		foreach ($rows as $row) {
			if (isset($row['session'])) {
				$sessions[] = $row['session'];
			}
		}
		$this->_json(array('success' => true, 'data' => $sessions));
	}

	public function fee_rule()
	{
		$courses = $this->db->get('courses')->result_array();
		$id = $this->input->get('id');
		$feerule = null;
		if ($id !== null && $id !== '') {
			$ruls = $this->db->get_where('fee_rules', array('fee_rule_id' => $id))->result_array();
			if (count($ruls) > 0) {
				$feerule = $ruls[0];
			}
		}
		$this->_json(array('success' => true, 'data' => array(
			'feerule' => $feerule,
			'courses' => $courses,
		)));
	}

	public function fee_plans()
	{
		$this->db->select('*, fee_rules.status as status, fee_rules.total_fee as total_fee');
		$this->db->from('fee_rules');
		$this->db->join('courses', 'courses.course_id = fee_rules.course_id', 'left');
		$plans = $this->db->get()->result_array();
		$this->_json(array('success' => true, 'data' => $plans));
	}

	public function save_fee_rule()
	{
		$body = $this->_body();
		$class_id = $this->_body_val($body, 'class_id');
		$course_id = $this->_body_val($body, 'course_id');
		$fee_rule_type = $this->_body_val($body, 'fee_rule_type');
		$last_date = $this->_body_val($body, 'last_date');
		$total_fee = $this->_body_val($body, 'total_fee');
		$installment_on_admission = $this->_body_val($body, 'installment_on_admission');
		$per_installment_fee = $this->_body_val($body, 'per_installment_fee');
		$difference_in_installments_months = $this->_body_val($body, 'difference_in_installments_months');
		$paid_date_each_installment = $this->_body_val($body, 'paid_date_each_installment');
		$late_fee_per_day_fine = $this->_body_val($body, 'late_fee_per_day_fine');
		$holiday_fine_remove = $this->_body_val($body, 'holiday_fine_remove');
		$council_board_fee = $this->_body_val($body, 'council_board_fee');
		$no_of_installment = $this->_body_val($body, 'no_of_installment');
		$disc_per_inst = $this->_body_val($body, 'disc_per_inst');
		$max_inst = $this->_body_val($body, 'max_install_extend');
		$max_discount = $this->_body_val($body, 'max_discount');
		$max_discount_merge = $this->_body_val($body, 'max_discount_merge');
		$max_incentive_no = $this->_body_val($body, 'max_incentive_no');

		if ($council_board_fee === 'Yes') {
			$last_date_council_fee = $this->_body_val($body, 'last_date_council_fee');
		}

		$session = $this->db->get_where('classes', array('session' => $class_id, 'course_id' => $course_id))->row_array();
		$exam_sequence = $this->db->get_where('exam_sequence', array(
			'course_id' => $course_id,
			'first_year' => $session['exam_no'],
			'class' => 1,
			'status' => 'Active',
		))->row_array();
		$fee_rul = null;
		if ($exam_sequence && isset($exam_sequence['id'])) {
			$fee_rul = $this->db->order_by('from_date', 'ASC')
				->get_where('council_sequence_fee_rules', 'exam_sequence_id = ' . (int)$exam_sequence['id'])
				->row_array();
		}

		$this->db->set('fee_rule_type', $fee_rule_type);
		$this->db->set('last_date', $last_date);
		$this->db->set('total_fee', $total_fee);
		$this->db->set('installment_on_admission', $installment_on_admission);
		$this->db->set('per_installment_fee', $per_installment_fee);
		$this->db->set('difference_in_installments_months', $difference_in_installments_months);
		$this->db->set('paid_date_each_installment', $paid_date_each_installment);
		$this->db->set('late_fee_per_day_fine', $late_fee_per_day_fine);
		$this->db->set('holiday_fine_remove', $holiday_fine_remove);
		$this->db->set('council_board_fee', $fee_rul ? 'Yes' : 'No');
		$this->db->set('no_of_installments', $no_of_installment);
		$this->db->set('disc_per_inst', $disc_per_inst);
		$this->db->set('max_install_extend', $max_inst);
		$this->db->set('max_discount', $max_discount);
		$this->db->set('max_comision', $max_incentive_no);
		$this->db->set('max_discount_merge', $max_discount_merge);
		$this->db->set('freeze_amount', $this->_body_val($body, 'freeze_fee'));

		if ($council_board_fee === 'Yes') {
			$this->db->set('first_time_council_fee', $fee_rul ? $fee_rul['exam_fee'] : '');
			$this->db->set('last_date_council_fee', isset($last_date_council_fee) ? $last_date_council_fee : '');
		} else {
			$this->db->set('first_time_council_fee', '');
			$this->db->set('last_date_council_fee', '0000-00-00');
		}

		$rule_id = $this->_body_val($body, 'rule_id');
		if ($rule_id !== null && $rule_id !== '') {
			$this->db->where('fee_rule_id', $rule_id);
			$this->db->update('fee_rules');
		} else {
			$this->db->set('session', $class_id);
			$this->db->set('course_id', $course_id);
			$this->db->insert('fee_rules');
		}

		$this->_json(array('success' => true, 'message' => 'Fee Rules Updated Successfully.'));
	}

	public function fee_plan_status($id = null, $status = null)
	{
		$body = $this->_body();
		if (!$id) {
			$id = $this->_body_val($body, 'id');
		}
		if ($status === null || $status === '') {
			$status = $this->_body_val($body, 'status');
		}
		if (!$id) {
			$this->_json(array('success' => false, 'message' => 'id required'), 422);
		}
		$this->db->set('status', $status);
		$this->db->where('fee_rule_id', $id);
		$this->db->update('fee_rules');
		$this->_json(array('success' => true, 'message' => 'Status updated.'));
	}

	public function extra_fee_meta()
	{
		$this->_json(array('success' => true, 'data' => array(
			'campuses' => $this->db->get('campuses')->result_array(),
			'courses' => $this->db->get('courses')->result_array(),
		)));
	}

	public function save_extra_fee_rule()
	{
		$body = $this->_body();
		$campus_id = $this->_body_val($body, 'campus_id');
		$course_id = $this->_body_val($body, 'course_id');
		if (!$campus_id || !$course_id) {
			$this->_json(array('success' => false, 'message' => 'campus_id and course_id required'), 422);
		}

		$council_board_fee = $this->_body_val($body, 'council_board_fee', 'No');
		$fields = array(
			'last_date' => $this->_body_val($body, 'last_date'),
			'total_fee' => $this->_body_val($body, 'total_fee'),
			'installment_on_admission' => $this->_body_val($body, 'installment_on_admission'),
			'per_installment_fee' => $this->_body_val($body, 'per_installment_fee'),
			'no_of_installments' => $this->_body_val($body, 'no_of_installment'),
			'difference_in_installments_months' => $this->_body_val($body, 'difference_in_installments_months'),
			'paid_date_each_installment' => $this->_body_val($body, 'paid_date_each_installment'),
			'late_fee_per_day_fine' => $this->_body_val($body, 'late_fee_per_day_fine'),
			'holiday_fine_remove' => $this->_body_val($body, 'holiday_fine_remove', 'Yes'),
			'council_board_fee' => $council_board_fee,
			'disc_per_inst' => $this->_body_val($body, 'disc_per_inst'),
			'max_discount_merge' => $this->_body_val($body, 'max_discount_merge'),
			'max_install_extend' => $this->_body_val($body, 'max_install_extend'),
			'max_discount' => $this->_body_val($body, 'max_discount'),
			'max_comision' => $this->_body_val($body, 'max_incentive_no'),
		);

		if ($this->_field_exists('fee_rules', 'max_discount_no')) {
			$fields['max_discount_no'] = $this->_body_val($body, 'max_discount_no');
		}

		if ($council_board_fee === 'Yes') {
			$fields['first_time_council_fee'] = $this->_body_val($body, 'first_time_council_fee');
			$fields['last_date_council_fee'] = $this->_body_val($body, 'last_date_council_fee');
		} else {
			$fields['first_time_council_fee'] = '';
			$fields['last_date_council_fee'] = '0000-00-00';
		}

		if ($this->_table_exists('online_fee_rules')) {
			foreach ($fields as $k => $v) {
				if ($this->_field_exists('online_fee_rules', $k)) {
					$this->db->set($k, $v);
				}
			}
			if ($this->_field_exists('online_fee_rules', 'campus_id')) {
				$this->db->set('campus_id', $campus_id);
			}
			if ($this->_field_exists('online_fee_rules', 'course_id')) {
				$this->db->set('course_id', $course_id);
			}
			if ($this->_field_exists('online_fee_rules', 'fee_rule_course_id')) {
				$this->db->set('fee_rule_course_id', $course_id);
			}

			$where = array();
			if ($this->_field_exists('online_fee_rules', 'campus_id')) {
				$where['campus_id'] = $campus_id;
			}
			if ($this->_field_exists('online_fee_rules', 'course_id')) {
				$where['course_id'] = $course_id;
			} elseif ($this->_field_exists('online_fee_rules', 'fee_rule_course_id')) {
				$where['fee_rule_course_id'] = $course_id;
			}

			$existing = count($where) ? $this->db->get_where('online_fee_rules', $where)->result_array() : array();
			if (count($existing) > 0) {
				$this->db->where($where);
				$this->db->update('online_fee_rules');
			} else {
				$this->db->insert('online_fee_rules');
			}
		} else {
			foreach ($fields as $k => $v) {
				if ($this->_field_exists('fee_rules', $k)) {
					$this->db->set($k, $v);
				}
			}
			$this->db->set('course_id', $course_id);
			if ($this->_field_exists('fee_rules', 'campus_id')) {
				$this->db->set('campus_id', $campus_id);
			}
			if ($this->_field_exists('fee_rules', 'fee_rule_type')) {
				$this->db->set('fee_rule_type', 'course');
			}

			$where = array('course_id' => $course_id);
			if ($this->_field_exists('fee_rules', 'campus_id')) {
				$where['campus_id'] = $campus_id;
			}
			$existing = $this->db->get_where('fee_rules', $where)->result_array();
			if (count($existing) > 0) {
				$this->db->where($where);
				$this->db->update('fee_rules');
			} else {
				$this->db->insert('fee_rules');
			}
		}

		$this->_json(array('success' => true, 'message' => 'Extra Fee Rule saved successfully.'));
	}

	public function online_study_rules()
	{
		$data = array(
			'campuses' => $this->db->get('campuses')->result_array(),
			'courses' => $this->db->get('courses')->result_array(),
			'rules' => array(),
		);
		if ($this->_table_exists('online_study_rules')) {
			$data['rules'] = $this->db->get('online_study_rules')->result_array();
		}
		$this->_json(array('success' => true, 'data' => $data));
	}

	public function save_online_study_rules()
	{
		$body = $this->_body();
		$campus_id = $this->_body_val($body, 'campus_id');
		$course_id = $this->_body_val($body, 'course_id');
		$emergency_no = $this->_body_val($body, 'emergency_no');

		$row = array(
			'campus_id' => $campus_id,
			'course_id' => $course_id,
			'emergency_no' => $emergency_no,
			'struck_off_notification' => !empty($body['struck_off_notification']) ? 1 : 0,
			'late_fee_notification' => !empty($body['late_fee_notification']) ? 1 : 0,
			'student_pass_notification' => !empty($body['student_pass_notification']) ? 1 : 0,
		);

		if (!$this->_table_exists('online_study_rules')) {
			$this->_json(array('success' => true, 'message' => 'Online study rules saved (table not present).', 'data' => $row));
		}

		foreach ($row as $field => $value) {
			if ($this->_field_exists('online_study_rules', $field)) {
				$this->db->set($field, $value);
			}
		}

		$where = array();
		if ($this->_field_exists('online_study_rules', 'campus_id')) {
			$where['campus_id'] = $campus_id;
		}
		if ($this->_field_exists('online_study_rules', 'course_id')) {
			$where['course_id'] = $course_id;
		}

		if (count($where)) {
			$existing = $this->db->get_where('online_study_rules', $where)->result_array();
			if (count($existing) > 0) {
				$this->db->where($where);
				$this->db->update('online_study_rules');
			} else {
				$this->db->insert('online_study_rules');
			}
		} else {
			$this->db->insert('online_study_rules');
		}

		$this->_json(array('success' => true, 'message' => 'Online Study Rules saved successfully.'));
	}

	public function closing_rules()
	{
		if (!$this->_table_exists('college_closing_rules')) {
			$this->_json(array('success' => true, 'data' => array()));
		}
		$this->db->select('college_closing_rules.*, campuses.campus_name, accounts.account_name');
		$this->db->from('college_closing_rules');
		$this->db->join('campuses', 'campuses.campus_id = college_closing_rules.campus_id', 'left');
		$this->db->join('accounts', 'accounts.id = college_closing_rules.account_id', 'left');
		$closing_rules = $this->db->get()->result_array();
		$this->_json(array('success' => true, 'data' => $closing_rules));
	}

	public function save_closing_rule()
	{
		$body = $this->_body();
		$campus_id = $this->_body_val($body, 'campus_id');
		$account_id = $this->_body_val($body, 'account_id');
		if (!$campus_id) {
			$this->_json(array('success' => false, 'message' => 'campus_id required'), 422);
		}

		$this->db->set('campus_id', $campus_id);
		$this->db->set('account_id', $account_id);

		$check_campus = $this->db->get_where('college_closing_rules', array('campus_id' => $campus_id))->result_array();
		if (count($check_campus) > 0) {
			$this->db->where('campus_id', $campus_id);
			$this->db->update('college_closing_rules');
		} else {
			$this->db->insert('college_closing_rules');
		}

		$this->_json(array('success' => true, 'message' => 'Closing Rule Updated Successfully.'));
	}

	public function council_rules()
	{
		$rows = $this->_table_exists('council_rules') ? $this->db->get('council_rules')->result_array() : array();
		$feerule = count($rows) > 0 ? $rows[0] : null;
		$this->_json(array('success' => true, 'rule' => $feerule));
	}

	public function save_council_rules()
	{
		$body = $this->_body();
		$this->db->set('total_fee', $this->_body_val($body, 'total_fee'));
		$this->db->set('no_of_exams', $this->_body_val($body, 'no_of_exams'));
		$this->db->set('min_council_fee', $this->_body_val($body, 'min_council_fee'));
		$this->db->set('max_council_fee', $this->_body_val($body, 'max_council_fee'));
		$this->db->where('id', '1');
		$this->db->update('council_rules');
		$this->_json(array('success' => true, 'message' => 'Council Rule Updated Successfully.'));
	}

	public function quiz_rules()
	{
		$rows = $this->_table_exists('quiz_rules') ? $this->db->get('quiz_rules')->result_array() : array();
		$this->_json(array('success' => true, 'rule' => count($rows) > 0 ? $rows[0] : null));
	}

	public function save_quiz_rules()
	{
		$body = $this->_body();
		$this->db->set('id', '1');
		$this->db->set('no_of_mcqs', $this->_body_val($body, 'mcqs'));
		$this->db->set('mark_per_mcqs', $this->_body_val($body, 'marks_mcq'));
		$this->db->set('no_of_short_questions', $this->_body_val($body, 'short_questions'));
		$this->db->set('mark_per_short_question', $this->_body_val($body, 'short_question_mcq'));
		$this->db->set('no_of_practicals', $this->_body_val($body, 'practicals'));
		$this->db->set('mark_per_practicals', $this->_body_val($body, 'marks_practical'));
		$this->db->insert('quiz_rules');
		$this->_json(array('success' => true, 'message' => 'Quiz Rule Created Successfully.'));
	}

	public function question_rules()
	{
		$question_rules = $this->_table_exists('question_rules')
			? $this->db
				->select('question_rules.*, users.first_name, users.last_name')
				->join('users', 'users.user_id = question_rules.teacher_id', 'left')
				->get('question_rules')
				->result_array()
			: array();

		$this->_json(array('success' => true, 'data' => $question_rules));
	}

	public function save_question_rule()
	{
		$body = $this->_body();
		$teacher_id = $this->_body_val($body, 'teacher_id');
		$qty = $this->_body_val($body, 'qty');
		if (!$teacher_id) {
			$this->_json(array('success' => false, 'message' => 'teacher_id required'), 422);
		}
		$this->db->set('teacher_id', $teacher_id);
		$this->db->set('no_of_qst', $qty);
		$this->db->insert('question_rules');
		$this->_json(array('success' => true, 'message' => 'Question Rule Added Successfully.'));
	}

	public function delete_question_rule($id = null)
	{
		if (!in_array($_SERVER['REQUEST_METHOD'], array('DELETE', 'POST'), true)) {
			$this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
		}
		$id = (int)$id;
		if (!$id) {
			$this->_json(array('success' => false, 'message' => 'id required'), 422);
		}
		$this->db->where('id', $id)->delete('question_rules');
		$this->_json(array('success' => true, 'message' => 'Rule Deleted Successfully.'));
	}

	public function loan_rules()
	{
		$rows = $this->_table_exists('loan_settings') ? $this->db->get('loan_settings')->result_array() : array();
		$this->_json(array('success' => true, 'settings' => count($rows) > 0 ? $rows[0] : null));
	}

	public function save_loan_rules()
	{
		$body = $this->_body();
		$this->db->set('max_months', $this->_body_val($body, 'max_months'));
		$this->db->set('max_multiply_salary', $this->_body_val($body, 'max_multiply_salary'));
		$this->db->set('loan_after_months', $this->_body_val($body, 'loan_after_months'));
		$this->db->set('avail_after_join', $this->_body_val($body, 'avail_after_join'));
		$this->db->where('id', '1');
		$this->db->update('loan_settings');
		$this->_json(array('success' => true, 'message' => 'Loan Rule Updated Successfully.'));
	}

	public function admission_regulations()
	{
		$rows = $this->_table_exists('admission_rules_regulations')
			? $this->db->get('admission_rules_regulations')->result_array()
			: array();
		$html = count($rows) > 0 ? (isset($rows[0]['rules']) ? $rows[0]['rules'] : '') : '';
		$this->_json(array(
			'success' => true,
			'rules_html' => $html,
			'add_by' => count($rows) > 0 && isset($rows[0]['created_by']) ? $rows[0]['created_by'] : '',
		));
	}

	public function save_admission_regulations()
	{
		$body = $this->_body();
		$rules = $this->_body_val($body, 'data');
		$add_by = $this->_body_val($body, 'max_months', $this->_user_name());
		$this->db->set('rules', $rules);
		$this->db->set('created_by', $add_by);
		$this->db->where('id', '1');
		$this->db->update('admission_rules_regulations');
		$this->_json(array('success' => true, 'message' => 'Updated Successfully.'));
	}

	public function payment_rules()
	{
		$payment_rules = $this->_table_exists('payment_rules')
			? $this->db
				->select('payment_rules.*, course_name')
				->join('courses', 'courses.course_id = payment_rules.course_id', 'left')
				->get('payment_rules')
				->result_array()
			: array();
		$courses = $this->db->get('courses')->result_array();
		$this->_json(array(
			'success' => true,
			'data' => $payment_rules,
			'courses' => $courses,
		));
	}

	public function insert_payment_rule()
	{
		$body = $this->_body();
		$this->db->set('name', $this->_body_val($body, 'name'));
		$this->db->set('amount', $this->_body_val($body, 'amount'));
		$this->db->set('course_id', $this->_body_val($body, 'course_id'));
		$this->db->set('created_by', $this->_body_val($body, 'created_by', $this->_user_name()));
		$this->db->insert('payment_rules');
		$this->_json(array('success' => true, 'message' => 'Rule Added Successfully.'));
	}

	public function update_payment_rule()
	{
		if (!in_array($_SERVER['REQUEST_METHOD'], array('PUT', 'POST'), true)) {
			$this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
		}
		$body = $this->_body();
		$rule_id = $this->_body_val($body, 'rule_id');
		if (!$rule_id) {
			$this->_json(array('success' => false, 'message' => 'rule_id required'), 422);
		}
		$this->db->set('name', $this->_body_val($body, 'name'));
		$this->db->set('amount', $this->_body_val($body, 'amount'));
		$this->db->set('course_id', $this->_body_val($body, 'course_id'));
		$this->db->set('created_by', $this->_body_val($body, 'created_by'));
		$this->db->set('status', $this->_body_val($body, 'status'));
		$this->db->where('id', $rule_id);
		$this->db->update('payment_rules');
		$this->_json(array('success' => true, 'message' => 'Payment Rule Updated Successfully.'));
	}

	public function inventory_rules()
	{
		$campuses = $this->db->get_where('campuses', array('status' => 1))->result_array();
		$exp_categories = $this->db->get_where('expense_category', 'sub_of is NULL')->result_array();
		$default_expense = $this->db->get('default_expense_category_inventory')->result_array();
		$default_return = $this->db->get('default_return_category_inventory')->result_array();

		$default_room_rules = array();
		foreach ($campuses as $campus) {
			$campus_id = $campus['campus_id'];
			$rooms = $this->db->get_where('rooms', array('campus_id' => $campus_id))->result_array();
			$default = $this->db->get_where('default_room_rules', array('campus_id' => $campus_id))->row_array();
			$default_room_id = $default ? $default['room_id'] : null;
			$default_subroom_id = $default ? $default['subroom_id'] : null;
			$subrooms = array();
			if ($default_room_id !== null && $default_room_id !== '' && (int)$default_room_id !== 0) {
				$subrooms = $this->db->get_where('subrooms', array('room_id' => $default_room_id))->result_array();
			}
			$default_room_rules[] = array(
				'campus_id' => $campus_id,
				'campus_name' => $campus['campus_name'],
				'rooms' => $rooms,
				'subrooms' => $subrooms,
				'default_room_id' => $default_room_id,
				'default_subroom_id' => $default_subroom_id,
			);
		}

		$expense_label = '';
		if (count($default_expense) > 0) {
			$expense_label = $this->_expense_category_label($default_expense[0]['expense_category_id']);
		}
		$return_label = '';
		if (count($default_return) > 0) {
			$return_label = $this->_expense_category_label($default_return[0]['expense_category_id']);
		}

		$default_rooms = array();
		foreach ($campuses as $campus) {
			$campus_id = $campus['campus_id'];
			$default = $this->_table_exists('default_room_rules')
				? $this->db->get_where('default_room_rules', array('campus_id' => $campus_id))->row_array()
				: null;
			if ($default) {
				$default_rooms[] = $default;
			}
		}

		$this->_json(array('success' => true, 'data' => array(
			'campuses' => $campuses,
			'exp_categories' => $exp_categories,
			'default_rooms' => $default_rooms,
			'default_expense_category' => count($default_expense) > 0
				? array('id' => $default_expense[0]['expense_category_id'], 'label' => $expense_label)
				: null,
			'default_return_category' => count($default_return) > 0
				? array('id' => $default_return[0]['expense_category_id'], 'label' => $return_label)
				: null,
			'default_room_rules' => $default_room_rules,
			'default_expense_category_inventory' => $default_expense,
			'default_return_category_inventory' => $default_return,
			'default_expense_category_label' => $expense_label,
			'default_return_category_label' => $return_label,
		)));
	}

	public function rooms_for_campus()
	{
		$campus_id = $this->input->get('campus_id');
		if (!$campus_id) {
			$this->_json(array('success' => false, 'message' => 'campus_id required'), 422);
		}
		$rooms = $this->db->get_where('rooms', array('campus_id' => $campus_id))->result_array();
		$this->_json(array('success' => true, 'data' => $rooms));
	}

	public function subrooms_for_room()
	{
		$room_id = $this->input->get('room_id');
		if (!$room_id) {
			$this->_json(array('success' => false, 'message' => 'room_id required'), 422);
		}
		$subrooms = $this->db->get_where('subrooms', array('room_id' => $room_id))->result_array();
		$this->_json(array('success' => true, 'data' => $subrooms));
	}

	public function save_inventory_rooms()
	{
		$body = $this->_body();
		$campus_ids = $this->_body_val($body, 'campus_id', array());
		$room_ids = $this->_body_val($body, 'room_id', array());
		$subroom_ids = $this->_body_val($body, 'subroom_id', array());

		if (!is_array($campus_ids)) {
			$campus_ids = array($campus_ids);
		}
		if (!is_array($room_ids)) {
			$room_ids = array($room_ids);
		}
		if (!is_array($subroom_ids)) {
			$subroom_ids = array($subroom_ids);
		}

		$total = count($campus_ids);
		for ($i = 0; $i < $total; $i++) {
			$check = $this->db->get_where('default_room_rules', array('campus_id' => $campus_ids[$i]))->result_array();
			$this->db->set('campus_id', $campus_ids[$i]);
			$this->db->set('room_id', isset($room_ids[$i]) ? $room_ids[$i] : '');
			$this->db->set('subroom_id', isset($subroom_ids[$i]) ? $subroom_ids[$i] : '');
			if (count($check) > 0) {
				$this->db->where('campus_id', $campus_ids[$i]);
				$this->db->update('default_room_rules');
			} else {
				$this->db->insert('default_room_rules');
			}
		}

		$this->_json(array('success' => true, 'message' => 'Default Room Update Successfully.'));
	}

	public function save_inventory_expense_rule()
	{
		$body = $this->_body();
		$expense_category_id = $this->_body_val($body, 'expense_category_id');
		if (is_array($expense_category_id)) {
			$expense_category_id = $expense_category_id[count($expense_category_id) - 1];
		}
		if (!$expense_category_id) {
			$this->_json(array('success' => false, 'message' => 'expense_category_id required'), 422);
		}

		$check = $this->db->get('default_expense_category_inventory')->result_array();
		if (count($check) > 0) {
			$this->db->set('expense_category_id', $expense_category_id);
			$this->db->where('id', $check[0]['id']);
			$this->db->update('default_expense_category_inventory');
		} else {
			$this->db->set('expense_category_id', $expense_category_id);
			$this->db->insert('default_expense_category_inventory');
		}

		$this->_json(array('success' => true, 'message' => 'Default Expense Category Selected.'));
	}

	public function save_inventory_return_rule()
	{
		$body = $this->_body();
		$expense_category_id = $this->_body_val($body, 'expense_category_id');
		if (is_array($expense_category_id)) {
			$expense_category_id = $expense_category_id[count($expense_category_id) - 1];
		}
		if (!$expense_category_id) {
			$this->_json(array('success' => false, 'message' => 'expense_category_id required'), 422);
		}

		$check = $this->db->get('default_return_category_inventory')->result_array();
		if (count($check) > 0) {
			$this->db->set('expense_category_id', $expense_category_id);
			$this->db->where('id', $check[0]['id']);
			$this->db->update('default_return_category_inventory');
		} else {
			$this->db->set('expense_category_id', $expense_category_id);
			$this->db->insert('default_return_category_inventory');
		}

		$this->_json(array('success' => true, 'message' => 'Default Return Category Selected.'));
	}

	public function backup_rules()
	{
		$row = $this->db->get_where('backups', array('backup_id' => 1))->row_array();
		$email = $row ? $row['email'] : '';
		$this->_json(array('success' => true, 'email' => $email));
	}

	public function save_backup_email()
	{
		$body = $this->_body();
		$email = $this->_body_val($body, 'email');
		$this->db->set('email', $email);
		$this->db->where('backup_id', 1);
		$this->db->update('backups');
		$this->_json(array('success' => true, 'message' => 'Backup email updated successfully.'));
	}

	public function free_items()
	{
		$rules = $this->_table_exists('free_item_rules') ? $this->db->get('free_item_rules')->result_array() : array();
		$this->_json(array('success' => true, 'data' => $rules));
	}

	public function save_free_item()
	{
		$body = $this->_body();
		$campus_ids = $this->_body_val($body, 'campus_ids', array());
		$class_ids = $this->_body_val($body, 'class_ids', array());
		$product_name_ids = $this->_body_val($body, 'product_name_ids', array());

		$this->db->set('campus_ids', $this->_implode_field($campus_ids));
		$this->db->set('class_ids', $this->_implode_field($class_ids));
		$this->db->set('product_name_ids', $this->_implode_field($product_name_ids));
		$this->db->set('till_date', $this->_body_val($body, 'till_date'));
		$this->db->set('student_admission_date', $this->_body_val($body, 'student_admission_date'));
		$this->db->insert('free_item_rules');
		$this->_json(array('success' => true, 'message' => 'Rule Added successfully.'));
	}

	public function update_free_item($id = null)
	{
		if (!in_array($_SERVER['REQUEST_METHOD'], array('PUT', 'POST'), true)) {
			$this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
		}
		$body = $this->_body();
		if (!$id) {
			$id = $this->_body_val($body, 'free_item_rule_id');
		}
		$campus_ids = $this->_body_val($body, 'campus_ids', array());
		$class_ids = $this->_body_val($body, 'class_ids', array());
		$product_name_ids = $this->_body_val($body, 'product_name_ids', array());

		$this->db->set('campus_ids', $this->_implode_field($campus_ids));
		$this->db->set('class_ids', $this->_implode_field($class_ids));
		$this->db->set('product_name_ids', $this->_implode_field($product_name_ids));
		$this->db->set('till_date', $this->_body_val($body, 'till_date'));
		$this->db->set('student_admission_date', $this->_body_val($body, 'student_admission_date'));
		$this->db->where('free_item_rule_id', $id);
		$this->db->update('free_item_rules');
		$this->_json(array('success' => true, 'message' => 'Rule Updated successfully.'));
	}

	public function delete_free_item($id = null)
	{
		if (!in_array($_SERVER['REQUEST_METHOD'], array('DELETE', 'POST'), true)) {
			$this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
		}
		$id = (int)$id;
		if (!$id) {
			$this->_json(array('success' => false, 'message' => 'id required'), 422);
		}
		$this->db->where('free_item_rule_id', $id);
		$this->db->delete('free_item_rules');
		$this->_json(array('success' => true, 'message' => 'Deleted successfully.'));
	}

	public function campus_classes()
	{
		$body = $this->_body();
		$campus_ids = $this->_body_val($body, 'campus_ids', array());
		$selected_classes = $this->_body_val($body, 'selected_classes', array());

		if (!is_array($campus_ids)) {
			$campus_ids = array($campus_ids);
		}
		if (!is_array($selected_classes)) {
			$selected_classes = array($selected_classes);
		}
		$selected_classes = array_map('intval', $selected_classes);

		if (!count($campus_ids)) {
			$this->_json(array('success' => true, 'data' => array()));
		}

		$this->db->where_in('campus_id', $campus_ids);
		$this->db->where('status', 1);
		$classes = $this->db->get('classes')->result_array();

		$result = array();
		foreach ($classes as $class) {
			$class['selected'] = in_array((int)$class['class_id'], $selected_classes, true);
			$result[] = $class;
		}

		$this->_json(array('success' => true, 'data' => $result));
	}

	public function eligibility_rules()
	{
		$table = $this->_eligibility_table();
		$rules = array();
		if ($table) {
			$this->db->select('*');
			$this->db->from($table);
			$this->db->join('courses', 'courses.course_id = ' . $table . '.course_id', 'inner');
			$rules = $this->db->get()->result_array();
		}
		$this->_json(array('success' => true, 'data' => $rules));
	}

	public function insert_eligibility_rules()
	{
		$table = $this->_eligibility_table();
		if (!$table) {
			$this->_json(array('success' => false, 'message' => 'Eligibility rules table is not configured on this server.'), 422);
		}
		$body = $this->_body();
		$course_id = $this->_body_val($body, 'course_id');
		$rules = $this->_body_val($body, 'rule', array());
		if (!$course_id) {
			$this->_json(array('success' => false, 'message' => 'course_id required'), 422);
		}
		if (!is_array($rules)) {
			$rules = array($rules);
		}
		foreach ($rules as $rule) {
			if ($rule === null || $rule === '') {
				continue;
			}
			$this->db->set('course_id', $course_id);
			$this->db->set('rule', $rule);
			$this->db->insert($table);
		}
		$this->_json(array('success' => true, 'message' => 'Rules Added Successfully.'));
	}

	public function delete_eligibility_rule($id = null)
	{
		if (!in_array($_SERVER['REQUEST_METHOD'], array('DELETE', 'POST'), true)) {
			$this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
		}
		$table = $this->_eligibility_table();
		if (!$table) {
			$this->_json(array('success' => false, 'message' => 'Eligibility rules table is not configured on this server.'), 422);
		}
		$id = (int)$id;
		if (!$id) {
			$this->_json(array('success' => false, 'message' => 'id required'), 422);
		}
		$pk = $this->_field_exists($table, 'eligibilty_admission_rule_id')
			? 'eligibilty_admission_rule_id'
			: 'eligibility_admission_rule_id';
		$this->db->where($pk, $id)->delete($table);
		$this->_json(array('success' => true, 'message' => 'Rule Deleted Successfully.'));
	}

	// ── Frontend endpoint aliases (rulesApi.ts) ─────────────────────────────

	public function campus_rules_form_meta()
	{
		$this->_json(array(
			'success' => true,
			'campuses' => $this->db->get('campuses')->result_array(),
			'courses' => $this->db->get('courses')->result_array(),
		));
	}

	public function course_timings()
	{
		$body = $this->_body();
		$course_ids = $this->_body_val($body, 'course_ids', array());
		if (!is_array($course_ids)) {
			$course_ids = array($course_ids);
		}
		$blocks = array();
		foreach ($course_ids as $course_id) {
			$course = $this->db->get_where('courses', array('course_id' => $course_id))->row_array();
			if ($course) {
				$blocks[] = array(
					'course_id' => $course_id,
					'course_name' => $course['course_name'],
					'start_time' => '',
					'end_time' => '',
				);
			}
		}
		$this->_json(array('success' => true, 'blocks' => $blocks));
	}

	public function fee_plan_form_meta()
	{
		$id = $this->input->get('fee_rule_id');
		if ($id === null || $id === '') {
			$id = $this->input->get('id');
		}
		$courses = $this->db->get('courses')->result_array();
		$feerule = null;
		if ($id !== null && $id !== '') {
			$ruls = $this->db->get_where('fee_rules', array('fee_rule_id' => $id))->result_array();
			if (count($ruls) > 0) {
				$feerule = $ruls[0];
			}
		}
		$this->_json(array('success' => true, 'courses' => $courses, 'feerule' => $feerule));
	}

	public function fee_plan_sessions()
	{
		$this->course_sessions();
	}

	public function save_fee_plan()
	{
		$this->save_fee_rule();
	}

	public function all_fee_plans()
	{
		$this->db->select('*, fee_rules.status as status, fee_rules.total_fee as total_fee');
		$this->db->from('fee_rules');
		$this->db->join('courses', 'courses.course_id = fee_rules.course_id', 'left');
		$plans = $this->db->get()->result_array();
		$active = array();
		$inactive = array();
		foreach ($plans as $plan) {
			if (isset($plan['status']) && strtolower((string)$plan['status']) === 'active') {
				$active[] = $plan;
			} else {
				$inactive[] = $plan;
			}
		}
		$this->_json(array('success' => true, 'active' => $active, 'inactive' => $inactive));
	}

	public function extra_fee_plan_meta()
	{
		$this->_json(array(
			'success' => true,
			'campuses' => $this->db->get('campuses')->result_array(),
			'feerule' => null,
		));
	}

	public function extra_fee_plan_courses()
	{
		$campus_id = $this->input->get('campus_id');
		$this->db->from('courses');
		if ($campus_id) {
			$this->db->join('classes', 'classes.course_id = courses.course_id', 'inner');
			$this->db->where('classes.campus_id', $campus_id);
			$this->db->group_by('courses.course_id');
		}
		$courses = $this->db->get()->result_array();
		$this->_json(array('success' => true, 'data' => $courses));
	}

	public function save_extra_fee_plan()
	{
		$this->save_extra_fee_rule();
	}

	public function online_study_rules_meta()
	{
		$campuses = $this->db->get('campuses')->result_array();
		$courses = $this->db->get('courses')->result_array();
		$rule = null;
		if ($this->_table_exists('online_study_rules')) {
			$rows = $this->db->get('online_study_rules')->result_array();
			if (count($rows) > 0) {
				$rule = $rows[0];
			}
		}
		$this->_json(array('success' => true, 'campuses' => $campuses, 'courses' => $courses, 'rule' => $rule));
	}

	public function daily_closing_rules()
	{
		$this->closing_rules();
	}

	public function daily_closing_accounts()
	{
		$this->_json(array(
			'success' => true,
			'campuses' => $this->db->get('campuses')->result_array(),
			'accounts' => $this->db->get_where('accounts', array('id >' => '0', 'type' => 0))->result_array(),
		));
	}

	public function save_daily_closing_rule()
	{
		$this->save_closing_rule();
	}

	public function question_rules_meta()
	{
		$users = $this->db
			->select('users.*, designations.designation_name, departments.department_name')
			->join('designations', 'designations.designation_id = users.designation_id', 'left')
			->join('departments', 'departments.department_id = users.department_id', 'left')
			->get_where('users', 'users.status = 1 and departments.department_id = 13')
			->result_array();
		$this->_json(array('success' => true, 'teachers' => $users));
	}

	public function save_payment_rule()
	{
		$this->insert_payment_rule();
	}

	public function inventory_rooms()
	{
		$this->rooms_for_campus();
	}

	public function inventory_subrooms()
	{
		$this->subrooms_for_room();
	}

	public function expense_subcategories()
	{
		$category_id = $this->input->get('expense_category_id');
		if (!$category_id) {
			$this->_json(array('success' => false, 'message' => 'expense_category_id required'), 422);
		}
		$children = $this->db->get_where('expense_category', array('sub_of' => $category_id))->result_array();
		$this->_json(array(
			'success' => true,
			'data' => $children,
			'has_children' => count($children) > 0,
		));
	}

	public function save_inventory_default_rooms()
	{
		$this->save_inventory_rooms();
	}

	public function free_product_rules()
	{
		$this->free_items();
	}

	public function free_product_form_meta()
	{
		$this->_json(array(
			'success' => true,
			'campuses' => $this->db->get_where('campuses', array('status' => 1))->result_array(),
			'product_names' => $this->db->get_where('product_names', array('has_sub' => 0))->result_array(),
		));
	}

	public function free_product_campus_classes()
	{
		$this->campus_classes();
	}

	public function save_free_product_rule()
	{
		$this->save_free_item();
	}

	public function delete_free_product_rule($id = null)
	{
		if (!in_array($_SERVER['REQUEST_METHOD'], array('DELETE', 'POST'), true)) {
			$this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
		}
		$this->delete_free_item($id);
	}

	public function free_product_rule($id = null)
	{
		$id = (int)$id;
		$rule = $this->db->get_where('free_item_rules', array('free_item_rule_id' => $id))->row_array();
		$classes = array();
		if ($rule && !empty($rule['campus_ids'])) {
			$this->db->where_in('campus_id', explode(',', $rule['campus_ids']));
			$this->db->where('status', 1);
			$classes = $this->db->get('classes')->result_array();
		}
		$this->_json(array('success' => true, 'rule' => $rule, 'classes' => $classes));
	}

	public function update_free_product_rule()
	{
		$body = $this->_body();
		$id = $this->_body_val($body, 'free_item_rule_id');
		if (!$id) {
			$this->_json(array('success' => false, 'message' => 'free_item_rule_id required'), 422);
		}
		$this->update_free_item($id);
	}

	public function insert_eligibility_rule()
	{
		$this->insert_eligibility_rules();
	}

	// ── Structured admission criteria (generic Admin builder) ───────────────

	private function _admission_criteria()
	{
		$this->load->library('Admission_criteria_service', null, 'admission_criteria');
		return $this->admission_criteria;
	}

	public function admission_criteria_meta()
	{
		$svc = $this->_admission_criteria();
		$svc->seed_defaults();
		$this->_json(array(
			'success' => true,
			'courses' => $this->db->order_by('course_name', 'ASC')->get('courses')->result_array(),
			'sets' => $svc->list_sets_summary(),
			'templates' => $svc->template_keys(),
			'qualification_options' => $svc->qualification_options(),
			'subject_options' => $svc->subject_options(),
			'rule_types' => array(
				array('value' => 'qualification', 'label' => 'Qualification'),
				array('value' => 'group', 'label' => 'Group / stream'),
				array('value' => 'min_percent', 'label' => 'Overall minimum %'),
				array('value' => 'subject_min_percent', 'label' => 'Subject-wise minimum %'),
				array('value' => 'required_subjects', 'label' => 'Required subjects'),
				array('value' => 'age_range', 'label' => 'Age range'),
				array('value' => 'gender', 'label' => 'Gender'),
				array('value' => 'document', 'label' => 'Document checklist'),
				array('value' => 'boolean', 'label' => 'Yes / No'),
				array('value' => 'number', 'label' => 'Number'),
				array('value' => 'text', 'label' => 'Text note'),
			),
		));
	}

	public function admission_criteria_get()
	{
		$svc = $this->_admission_criteria();
		$course_id = (int)$this->input->get('course_id');
		$set_id = (int)$this->input->get('set_id');
		if ($set_id > 0) {
			$set = $svc->get_set($set_id);
			if (!$set) {
				$this->_json(array('success' => false, 'message' => 'Set not found'), 404);
			}
			$set['rules'] = $svc->list_rules($set_id);
			$this->_json(array('success' => true, 'data' => $set));
		}
		if ($course_id <= 0) {
			$this->_json(array('success' => false, 'message' => 'course_id required'), 422);
		}
		$set = $svc->get_set_for_course($course_id, true);
		$this->_json(array('success' => true, 'data' => $set));
	}

	public function admission_criteria_create()
	{
		$svc = $this->_admission_criteria();
		$body = $this->_body();
		$course_id = (int)$this->_body_val($body, 'course_id', 0);
		$title = trim((string)$this->_body_val($body, 'title', 'Admission criteria'));
		$governing = trim((string)$this->_body_val($body, 'governing_body', ''));
		$res = $svc->create_blank_set($course_id, $title, $governing, $this->_user_name());
		if (empty($res['success'])) {
			$this->_json($res, 422);
		}
		$set = $svc->get_set_for_course($course_id, true);
		$this->_json(array('success' => true, 'set_id' => $res['set_id'], 'data' => $set));
	}

	public function admission_criteria_save()
	{
		$svc = $this->_admission_criteria();
		$body = $this->_body();
		$set_id = (int)$this->_body_val($body, 'set_id', 0);
		if ($set_id <= 0) {
			$this->_json(array('success' => false, 'message' => 'set_id required'), 422);
		}
		$svc->update_set($set_id, array(
			'title' => $this->_body_val($body, 'title'),
			'governing_body' => $this->_body_val($body, 'governing_body'),
			'is_active' => $this->_body_val($body, 'is_active', 1),
			'soft_fail' => $this->_body_val($body, 'soft_fail', 0),
		), $this->_user_name());
		$rules = $this->_body_val($body, 'rules', array());
		$res = $svc->save_rules($set_id, $rules, $this->_user_name());
		if (empty($res['success'])) {
			$this->_json($res, 422);
		}
		$set = $svc->get_set($set_id);
		$set['rules'] = $svc->list_rules($set_id);
		$this->_json(array('success' => true, 'message' => 'Saved', 'data' => $set));
	}

	public function admission_criteria_delete()
	{
		if (!in_array($_SERVER['REQUEST_METHOD'], array('DELETE', 'POST'), true)) {
			$this->_json(array('success' => false, 'message' => 'Method not allowed'), 405);
		}
		$svc = $this->_admission_criteria();
		$body = $this->_body();
		$set_id = (int)$this->_body_val($body, 'set_id', 0);
		if (!$set_id) {
			$set_id = (int)$this->input->get('set_id');
		}
		if ($set_id <= 0) {
			$this->_json(array('success' => false, 'message' => 'set_id required'), 422);
		}
		$svc->delete_set($set_id);
		$this->_json(array('success' => true, 'message' => 'Deleted'));
	}

	public function admission_criteria_seed()
	{
		$svc = $this->_admission_criteria();
		$body = $this->_body();
		$force = (int)$this->_body_val($body, 'course_id', 0);
		$res = $svc->seed_defaults($force);
		$this->_json(array('success' => true, 'message' => 'Seed complete', 'data' => $res, 'sets' => $svc->list_sets_summary()));
	}

	public function admission_criteria_apply_template()
	{
		$svc = $this->_admission_criteria();
		$body = $this->_body();
		$course_id = (int)$this->_body_val($body, 'course_id', 0);
		$template_course_id = (int)$this->_body_val($body, 'template_course_id', 0);
		$replace = !empty($this->_body_val($body, 'replace', false));
		$res = $svc->apply_template_to_course($course_id, $template_course_id, $this->_user_name(), $replace);
		if (empty($res['success'])) {
			$this->_json($res, 422);
		}
		$set = $svc->get_set_for_course($course_id, true);
		$this->_json(array('success' => true, 'message' => $res['message'], 'data' => $set));
	}

	public function admission_criteria_preview()
	{
		$svc = $this->_admission_criteria();
		$body = $this->_body();
		$course_id = (int)$this->_body_val($body, 'course_id', 0);
		$answers = $this->_body_val($body, 'answers', array());
		$eval = $svc->evaluate($course_id, is_array($answers) ? $answers : array());
		$this->_json(array('success' => true, 'data' => $eval));
	}
}
