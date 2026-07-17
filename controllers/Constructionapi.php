<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Lean Construction JSON API for React POS shell
 * Base: /index.php/constructionapi/{method}
 * Auth: X-Pos-Token; Admin only (for now)
 *
 * Daily expenses write to `expenses` with construction_* FK columns.
 * Misc / labour / contractor all use expense_category_id = 448 until separate IDs are provided.
 */
class Constructionapi extends CI_Controller {

	const EXPENSE_CATEGORY_ID = 448;

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
		if (!$this->_is_admin()) {
			$this->_json(array('success' => false, 'message' => 'Admin access required'), 403);
		}
		$this->_ensure_schema();
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
		if (!$token) return null;
		$row = $this->db->get_where('pos_api_tokens', array('token' => $token))->row_array();
		if (!$row || strtotime($row['expires_at']) < time()) return null;
		return $this->db->get_where('users', array('user_id' => $row['user_id'], 'status' => '1'))->row_array();
	}

	private function _is_admin()
	{
		return isset($this->current_user['role']) && $this->current_user['role'] === 'Admin';
	}

	private function _user_name()
	{
		return trim($this->current_user['first_name'] . ' ' . $this->current_user['last_name']);
	}

	private function _ensure_column($table, $column, $ddl)
	{
		if (!$this->db->table_exists($table)) return;
		if ($this->db->field_exists($column, $table)) return;
		$this->db->query("ALTER TABLE `$table` ADD `$column` $ddl");
	}

	private function _ensure_schema()
	{
		// Core tables (same as Construction::ensure_tables — minimal set)
		$this->db->query("CREATE TABLE IF NOT EXISTS construction_projects (
			id INT NOT NULL AUTO_INCREMENT,
			project_name VARCHAR(255) NOT NULL,
			location VARCHAR(255) NULL,
			client VARCHAR(255) NULL,
			start_date DATE NULL,
			expected_completion_date DATE NULL,
			budget DECIMAL(15,2) NOT NULL DEFAULT 0,
			status VARCHAR(30) NOT NULL DEFAULT 'Planning',
			project_manager_id INT NULL,
			campus_id INT NULL,
			created_by INT NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NULL,
			PRIMARY KEY (id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		$this->_ensure_column('construction_projects', 'campus_id', 'INT NULL');

		$this->db->query("CREATE TABLE IF NOT EXISTS construction_labours (
			id INT NOT NULL AUTO_INCREMENT,
			project_id INT NULL,
			labour_name VARCHAR(255) NOT NULL,
			cnic VARCHAR(30) NULL,
			mobile VARCHAR(30) NULL,
			designation VARCHAR(100) NULL,
			daily_wage DECIMAL(15,2) NOT NULL DEFAULT 0,
			status TINYINT(1) NOT NULL DEFAULT 1,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			KEY project_id (project_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		$this->db->query("CREATE TABLE IF NOT EXISTS construction_labour_advances (
			id INT NOT NULL AUTO_INCREMENT,
			labour_id INT NOT NULL,
			project_id INT NOT NULL,
			advance_date DATE NOT NULL,
			amount DECIMAL(15,2) NOT NULL DEFAULT 0,
			remarks TEXT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			KEY labour_id (labour_id),
			KEY project_id (project_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		$this->db->query("CREATE TABLE IF NOT EXISTS construction_contractors (
			id INT NOT NULL AUTO_INCREMENT,
			project_id INT NOT NULL,
			contractor_name VARCHAR(255) NOT NULL,
			contact_details TEXT NULL,
			contract_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
			advance_payment DECIMAL(15,2) NOT NULL DEFAULT 0,
			running_bills DECIMAL(15,2) NOT NULL DEFAULT 0,
			final_bill DECIMAL(15,2) NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			KEY project_id (project_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		$this->db->query("CREATE TABLE IF NOT EXISTS construction_contractor_payments (
			id INT NOT NULL AUTO_INCREMENT,
			contractor_id INT NOT NULL,
			project_id INT NOT NULL,
			payment_date DATE NOT NULL,
			amount DECIMAL(15,2) NOT NULL DEFAULT 0,
			payment_type VARCHAR(50) NULL,
			remarks TEXT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			KEY contractor_id (contractor_id),
			KEY project_id (project_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		// Link columns on shared expenses table
		if ($this->db->table_exists('expenses')) {
			$this->_ensure_column('expenses', 'construction_project_id', 'INT NULL DEFAULT NULL');
			$this->_ensure_column('expenses', 'construction_contractor_id', 'INT NULL DEFAULT NULL');
			$this->_ensure_column('expenses', 'construction_labour_id', 'INT NULL DEFAULT NULL');
			$this->_ensure_column('expenses', 'construction_source', "VARCHAR(30) NULL DEFAULT NULL");
			$this->_ensure_column('expenses', 'construction_ref_id', 'INT NULL DEFAULT 0');
		}
	}

	private function _project($id)
	{
		return $this->db->get_where('construction_projects', array('id' => (int)$id))->row_array();
	}

	private function _project_summary($project_id)
	{
		$project_id = (int)$project_id;
		$contractors = $this->db->get_where('construction_contractors', array('project_id' => $project_id))->result_array();
		$done = 0;
		foreach ($contractors as $c) {
			$final = (float)$c['final_bill'];
			$contract = (float)$c['contract_amount'];
			$done += $final > 0 ? $final : $contract;
		}
		$paid_row = $this->db->query(
			'SELECT COALESCE(SUM(amount),0) AS t FROM construction_contractor_payments WHERE project_id = ?',
			array($project_id)
		)->row_array();
		$contractor_paid = (float)$paid_row['t'];

		$labour_row = $this->db->query(
			"SELECT COALESCE(SUM(amount),0) AS t FROM expenses
			 WHERE construction_project_id = ? AND construction_source = 'labour'",
			array($project_id)
		)->row_array();
		$misc_row = $this->db->query(
			"SELECT COALESCE(SUM(amount),0) AS t FROM expenses
			 WHERE construction_project_id = ? AND construction_source = 'misc'",
			array($project_id)
		)->row_array();

		return array(
			'contractor_done' => $done,
			'contractor_paid' => $contractor_paid,
			'contractor_remaining' => max(0, $done - $contractor_paid),
			'labour_paid' => (float)$labour_row['t'],
			'misc_total' => (float)$misc_row['t'],
			'contractor_count' => count($contractors),
		);
	}

	private function _insert_expense($opts)
	{
		if (!$this->db->table_exists('expenses')) {
			$this->_json(array('success' => false, 'message' => 'expenses table missing'), 500);
		}
		$name = $this->_user_name();
		$row = array(
			'campus_id' => (int)$opts['campus_id'],
			'expense_category_id' => self::EXPENSE_CATEGORY_ID,
			'date' => $opts['date'],
			'actual_date' => date('Y-m-d H:i:s'),
			'amount' => (float)$opts['amount'],
			'purpose' => $opts['purpose'],
			'add_by' => $name,
			'last_edit' => $name,
			'add_by_id' => (int)$this->current_user['user_id'],
			'approved_status' => 1,
			'payment_type' => 'cash',
			'paid_type' => 'cash',
			'image' => '',
			'construction_project_id' => (int)$opts['project_id'],
			'construction_contractor_id' => isset($opts['contractor_id']) ? (int)$opts['contractor_id'] : null,
			'construction_labour_id' => isset($opts['labour_id']) ? (int)$opts['labour_id'] : null,
			'construction_source' => $opts['source'],
			'construction_ref_id' => isset($opts['ref_id']) ? (int)$opts['ref_id'] : 0,
		);
		if ($this->db->field_exists('title', 'expenses')) {
			$row['title'] = $opts['title'];
		} elseif ($this->db->field_exists('Title', 'expenses')) {
			$row['Title'] = $opts['title'];
		}
		if ($this->db->field_exists('month_year', 'expenses')) {
			$row['month_year'] = date('Y-m', strtotime($opts['date']));
		}
		$this->db->insert('expenses', $row);
		return (int)$this->db->insert_id();
	}

	// ─── Projects ───────────────────────────────────────────

	public function projects()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$body = $this->_body();
			$name = isset($body['project_name']) ? trim($body['project_name']) : '';
			if ($name === '') $this->_json(array('success' => false, 'message' => 'project_name required'), 422);
			$campus_id = isset($body['campus_id']) ? (int)$body['campus_id'] : (int)$this->current_user['campus_id'];
			if ($campus_id < 1) {
				$this->_json(array('success' => false, 'message' => 'Campus is required'), 422);
			}
			$this->db->insert('construction_projects', array(
				'project_name' => $name,
				'location' => isset($body['location']) ? trim($body['location']) : '',
				'client' => isset($body['client']) ? trim($body['client']) : '',
				'start_date' => !empty($body['start_date']) ? $body['start_date'] : null,
				'expected_completion_date' => !empty($body['expected_completion_date']) ? $body['expected_completion_date'] : null,
				'budget' => isset($body['budget']) ? (float)$body['budget'] : 0,
				'status' => isset($body['status']) ? $body['status'] : 'Active',
				'campus_id' => $campus_id,
				'created_by' => (int)$this->current_user['user_id'],
				'created_at' => date('Y-m-d H:i:s'),
			));
			$this->_json(array('success' => true, 'id' => (int)$this->db->insert_id()));
		}

		$campus_id = (int)$this->input->get('campus_id');
		if ($campus_id > 0 && $this->db->field_exists('campus_id', 'construction_projects')) {
			// Strict campus filter — only that campus's projects
			$this->db->where('campus_id', $campus_id);
		}
		$rows = $this->db->order_by('id', 'DESC')->get('construction_projects')->result_array();
		foreach ($rows as &$r) {
			$r['summary'] = $this->_project_summary($r['id']);
		}
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function project($id = 0)
	{
		$id = (int)$id;
		$project = $this->_project($id);
		if (!$project) $this->_json(array('success' => false, 'message' => 'Project not found'), 404);

		$contractors = $this->db->get_where('construction_contractors', array('project_id' => $id))->result_array();
		foreach ($contractors as &$c) {
			$final = (float)$c['final_bill'];
			$contract = (float)$c['contract_amount'];
			$c['done_amount'] = $final > 0 ? $final : $contract;
			$paid = $this->db->query(
				'SELECT COALESCE(SUM(amount),0) AS t FROM construction_contractor_payments WHERE contractor_id = ?',
				array((int)$c['id'])
			)->row_array();
			$c['paid_amount'] = (float)$paid['t'];
			$c['remaining'] = max(0, $c['done_amount'] - $c['paid_amount']);
		}
		$labours = $this->db->get_where('construction_labours', array('project_id' => $id, 'status' => 1))->result_array();
		if (!count($labours)) {
			$labours = $this->db->get_where('construction_labours', array('project_id' => $id))->result_array();
		}

		$this->_json(array(
			'success' => true,
			'data' => array(
				'project' => $project,
				'summary' => $this->_project_summary($id),
				'contractors' => $contractors,
				'labours' => $labours,
			),
		));
	}

	// ─── Contractors / Labours ──────────────────────────────

	public function contractors()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			$project_id = (int)$this->input->get('project_id');
			if (!$project_id) $this->_json(array('success' => false, 'message' => 'project_id required'), 422);
			$rows = $this->db->get_where('construction_contractors', array('project_id' => $project_id))->result_array();
			$this->_json(array('success' => true, 'data' => $rows));
		}

		$body = $this->_body();
		$project_id = (int)(isset($body['project_id']) ? $body['project_id'] : 0);
		$name = isset($body['contractor_name']) ? trim($body['contractor_name']) : '';
		if (!$project_id || $name === '') {
			$this->_json(array('success' => false, 'message' => 'project_id and contractor_name required'), 422);
		}
		if (!$this->_project($project_id)) $this->_json(array('success' => false, 'message' => 'Project not found'), 404);

		$this->db->insert('construction_contractors', array(
			'project_id' => $project_id,
			'contractor_name' => $name,
			'contact_details' => isset($body['contact_details']) ? $body['contact_details'] : '',
			'contract_amount' => isset($body['contract_amount']) ? (float)$body['contract_amount'] : 0,
			'advance_payment' => 0,
			'running_bills' => 0,
			'final_bill' => isset($body['final_bill']) ? (float)$body['final_bill'] : 0,
			'created_at' => date('Y-m-d H:i:s'),
		));
		$this->_json(array('success' => true, 'id' => (int)$this->db->insert_id()));
	}

	public function labours()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			$project_id = (int)$this->input->get('project_id');
			if (!$project_id) $this->_json(array('success' => false, 'message' => 'project_id required'), 422);
			$rows = $this->db->get_where('construction_labours', array('project_id' => $project_id))->result_array();
			$this->_json(array('success' => true, 'data' => $rows));
		}

		$body = $this->_body();
		$project_id = (int)(isset($body['project_id']) ? $body['project_id'] : 0);
		$name = isset($body['labour_name']) ? trim($body['labour_name']) : '';
		if (!$project_id || $name === '') {
			$this->_json(array('success' => false, 'message' => 'project_id and labour_name required'), 422);
		}
		if (!$this->_project($project_id)) $this->_json(array('success' => false, 'message' => 'Project not found'), 404);

		$this->db->insert('construction_labours', array(
			'project_id' => $project_id,
			'labour_name' => $name,
			'cnic' => isset($body['cnic']) ? $body['cnic'] : '',
			'mobile' => isset($body['mobile']) ? $body['mobile'] : '',
			'designation' => isset($body['designation']) ? $body['designation'] : '',
			'daily_wage' => isset($body['daily_wage']) ? (float)$body['daily_wage'] : 0,
			'status' => 1,
			'created_at' => date('Y-m-d H:i:s'),
		));
		$this->_json(array('success' => true, 'id' => (int)$this->db->insert_id()));
	}

	// ─── Daily expenses ─────────────────────────────────────

	public function daily_expenses()
	{
		$project_id = (int)$this->input->get('project_id');
		$date = trim((string)$this->input->get('date'));
		if (!$project_id) $this->_json(array('success' => false, 'message' => 'project_id required'), 422);
		if ($date === '') $date = date('Y-m-d');

		if (!$this->db->table_exists('expenses') || !$this->db->field_exists('construction_project_id', 'expenses')) {
			$this->_json(array('success' => true, 'data' => array(), 'day_total' => 0));
		}

		$this->db->select('expenses.*, construction_contractors.contractor_name, construction_labours.labour_name', false);
		$this->db->from('expenses');
		$this->db->join('construction_contractors', 'construction_contractors.id = expenses.construction_contractor_id', 'left');
		$this->db->join('construction_labours', 'construction_labours.id = expenses.construction_labour_id', 'left');
		$this->db->where('expenses.construction_project_id', $project_id);
		$this->db->where('expenses.date', $date);
		$this->db->order_by('expenses.expense_id', 'DESC');
		$rows = $this->db->get()->result_array();

		$total = 0;
		foreach ($rows as &$r) {
			$total += (float)$r['amount'];
			$src = isset($r['construction_source']) ? $r['construction_source'] : 'misc';
			if ($src === 'contractor') {
				$r['party_name'] = isset($r['contractor_name']) ? $r['contractor_name'] : 'Contractor';
			} elseif ($src === 'labour') {
				$r['party_name'] = isset($r['labour_name']) ? $r['labour_name'] : 'Labour';
			} else {
				$r['party_name'] = 'Misc';
			}
		}

		$this->_json(array('success' => true, 'data' => $rows, 'day_total' => $total, 'date' => $date));
	}

	public function add_daily_expense()
	{
		$body = $this->_body();
		$type = isset($body['type']) ? trim($body['type']) : '';
		$project_id = (int)(isset($body['project_id']) ? $body['project_id'] : 0);
		$amount = isset($body['amount']) ? (float)$body['amount'] : 0;
		$date = !empty($body['date']) ? $body['date'] : date('Y-m-d');
		$description = isset($body['description']) ? trim($body['description']) : '';

		if (!in_array($type, array('labour', 'contractor', 'misc'), true)) {
			$this->_json(array('success' => false, 'message' => 'type must be labour|contractor|misc'), 422);
		}
		if (!$project_id || $amount <= 0) {
			$this->_json(array('success' => false, 'message' => 'project_id and amount required'), 422);
		}
		$project = $this->_project($project_id);
		if (!$project) $this->_json(array('success' => false, 'message' => 'Project not found'), 404);

		$campus_id = !empty($project['campus_id'])
			? (int)$project['campus_id']
			: (int)$this->current_user['campus_id'];
		if (!$campus_id) $this->_json(array('success' => false, 'message' => 'campus_id required on project or user'), 422);

		$ref_id = 0;
		$contractor_id = null;
		$labour_id = null;
		$title = '';
		$purpose = '';

		if ($type === 'contractor') {
			$contractor_id = (int)(isset($body['contractor_id']) ? $body['contractor_id'] : 0);
			$c = $this->db->get_where('construction_contractors', array(
				'id' => $contractor_id,
				'project_id' => $project_id,
			))->row_array();
			if (!$c) $this->_json(array('success' => false, 'message' => 'contractor_id invalid for project'), 422);
			$payment_type = isset($body['payment_type']) ? trim($body['payment_type']) : 'Running Bill';
			$this->db->insert('construction_contractor_payments', array(
				'contractor_id' => $contractor_id,
				'project_id' => $project_id,
				'payment_date' => $date,
				'amount' => $amount,
				'payment_type' => $payment_type,
				'remarks' => $description,
				'created_at' => date('Y-m-d H:i:s'),
			));
			$ref_id = (int)$this->db->insert_id();
			$title = 'Construction · Contractor payment';
			$purpose = 'Construction ' . $project['project_name'] . ' · Contractor · ' . $c['contractor_name']
				. ($description !== '' ? ' · ' . $description : '');
		} elseif ($type === 'labour') {
			$labour_id = (int)(isset($body['labour_id']) ? $body['labour_id'] : 0);
			$l = $this->db->get_where('construction_labours', array(
				'id' => $labour_id,
				'project_id' => $project_id,
			))->row_array();
			if (!$l) $this->_json(array('success' => false, 'message' => 'labour_id invalid for project'), 422);
			$this->db->insert('construction_labour_advances', array(
				'labour_id' => $labour_id,
				'project_id' => $project_id,
				'advance_date' => $date,
				'amount' => $amount,
				'remarks' => $description !== '' ? $description : 'Labour payment',
				'created_at' => date('Y-m-d H:i:s'),
			));
			$ref_id = (int)$this->db->insert_id();
			$title = 'Construction · Labour payment';
			$purpose = 'Construction ' . $project['project_name'] . ' · Labour · ' . $l['labour_name']
				. ($description !== '' ? ' · ' . $description : '');
		} else {
			$title = 'Construction · Misc expense';
			$purpose = 'Construction ' . $project['project_name'] . ' · Misc'
				. ($description !== '' ? ' · ' . $description : '');
			// Optional mirror in construction_site_expenses for legacy views
			if ($this->db->table_exists('construction_site_expenses')) {
				$this->db->insert('construction_site_expenses', array(
					'project_id' => $project_id,
					'category' => 'Miscellaneous',
					'expense_date' => $date,
					'amount' => $amount,
					'description' => $description,
					'attachment' => '',
					'created_by' => (int)$this->current_user['user_id'],
					'created_at' => date('Y-m-d H:i:s'),
				));
				$ref_id = (int)$this->db->insert_id();
			}
		}

		$expense_id = $this->_insert_expense(array(
			'campus_id' => $campus_id,
			'project_id' => $project_id,
			'contractor_id' => $contractor_id,
			'labour_id' => $labour_id,
			'source' => $type,
			'ref_id' => $ref_id,
			'amount' => $amount,
			'date' => $date,
			'title' => $title,
			'purpose' => $purpose,
		));

		$this->_json(array(
			'success' => true,
			'expense_id' => $expense_id,
			'construction_ref_id' => $ref_id,
			'message' => 'Expense added',
		));
	}

	public function campuses()
	{
		$this->db->from('campuses');
		$this->db->where('status', 1);
		$this->db->order_by('campus_name', 'ASC');
		$this->_json(array('success' => true, 'data' => $this->db->get()->result_array()));
	}
}
