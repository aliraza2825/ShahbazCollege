<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Lean Construction JSON API for React POS shell
 * Base: /index.php/constructionapi/{method}
 * Auth: X-Pos-Token; Admin or construction access flags
 *
 * Daily expenses write to `expenses` with construction_* FK columns.
 * Misc / labour / contractor all use expense_category_id = 448 until separate IDs are provided.
 */
class Constructionapi extends CI_Controller {

	const EXPENSE_CATEGORY_ID = 448;

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
		$this->access_row = $this->_load_access_row();
		if (!$this->_is_admin() && !$this->_can_construction() && !$this->_can_verify_expense()) {
			$this->_json(array('success' => false, 'message' => 'Construction access required'), 403);
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

	private function _load_access_row()
	{
		if (!$this->db->table_exists('access')) return null;
		return $this->db->get_where('access', array('user_id' => (int)$this->current_user['user_id']))->row_array();
	}

	private function _access_flag($col)
	{
		if ($this->_is_admin()) return true;
		if (!$this->access_row || !isset($this->access_row[$col])) return false;
		$v = $this->access_row[$col];
		return $v !== null && $v !== '' && (string)$v !== '0';
	}

	private function _can_construction()
	{
		return $this->_access_flag('construction_sidebar')
			|| $this->_access_flag('construction_site_expense')
			|| $this->_access_flag('construction_projects');
	}

	private function _can_verify_expense()
	{
		return $this->_access_flag('construction_expense_verify');
	}

	private function _assert_verify_expense()
	{
		if (!$this->_can_verify_expense()) {
			$this->_json(array('success' => false, 'message' => 'Verify construction expense access required'), 403);
		}
	}

	private function _assert_manage()
	{
		if ($this->_is_admin() || $this->_can_construction()) return;
		$this->_json(array('success' => false, 'message' => 'Construction manage access required'), 403);
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

	/** Make an existing column nullable (used to detach contractors from projects). */
	private function _ensure_nullable($table, $column)
	{
		if (!$this->db->table_exists($table) || !$this->db->field_exists($column, $table)) return;
		$row = $this->db->query(
			"SELECT IS_NULLABLE AS n FROM INFORMATION_SCHEMA.COLUMNS
			 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
			array($table, $column)
		)->row_array();
		if ($row && strtoupper($row['n']) === 'YES') return;
		$this->db->query("ALTER TABLE `$table` MODIFY `$column` INT NULL DEFAULT NULL");
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
		$this->_ensure_column('construction_projects', 'progress_percent', 'DECIMAL(5,2) NOT NULL DEFAULT 0');

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
			project_id INT NULL,
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
		// Contractor is master data — project link lives on contracts only
		$this->_ensure_nullable('construction_contractors', 'project_id');
		$this->_ensure_column('construction_contractors', 'image', 'VARCHAR(255) NULL DEFAULT NULL');
		$this->_ensure_column('construction_contractors', 'mobile', 'VARCHAR(30) NULL DEFAULT NULL');
		$this->_ensure_column('construction_contractors', 'cnic', 'VARCHAR(30) NULL DEFAULT NULL');
		$this->_ensure_column('construction_contractors', 'address', 'TEXT NULL');

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

		$this->_ensure_column('construction_contractor_payments', 'installment_id', 'INT NULL DEFAULT NULL');
		$this->_ensure_column('construction_contractor_payments', 'contract_id', 'INT NULL DEFAULT NULL');

		$this->db->query("CREATE TABLE IF NOT EXISTS construction_contracts (
			id INT NOT NULL AUTO_INCREMENT,
			contractor_id INT NOT NULL,
			project_id INT NOT NULL,
			title VARCHAR(255) NOT NULL,
			total_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
			start_date DATE NULL,
			status VARCHAR(30) NOT NULL DEFAULT 'active',
			notes TEXT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			KEY contractor_id (contractor_id),
			KEY project_id (project_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		$this->db->query("CREATE TABLE IF NOT EXISTS construction_contract_installments (
			id INT NOT NULL AUTO_INCREMENT,
			contract_id INT NOT NULL,
			contractor_id INT NOT NULL,
			project_id INT NOT NULL,
			installment_no INT NOT NULL DEFAULT 1,
			due_date DATE NOT NULL,
			amount DECIMAL(15,2) NOT NULL DEFAULT 0,
			paid TINYINT(1) NOT NULL DEFAULT 0,
			paid_date DATE NULL,
			paid_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
			payment_id INT NULL DEFAULT NULL,
			expense_id INT NULL DEFAULT NULL,
			label VARCHAR(255) NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			KEY contract_id (contract_id),
			KEY contractor_id (contractor_id),
			KEY paid (paid)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		$this->db->query("CREATE TABLE IF NOT EXISTS construction_contract_images (
			id INT NOT NULL AUTO_INCREMENT,
			contract_id INT NOT NULL,
			contractor_id INT NOT NULL,
			filename VARCHAR(255) NOT NULL,
			caption VARCHAR(255) NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			KEY contract_id (contract_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		// Link columns on shared expenses table
		if ($this->db->table_exists('expenses')) {
			$this->_ensure_column('expenses', 'construction_project_id', 'INT NULL DEFAULT NULL');
			$this->_ensure_column('expenses', 'construction_contractor_id', 'INT NULL DEFAULT NULL');
			$this->_ensure_column('expenses', 'construction_labour_id', 'INT NULL DEFAULT NULL');
			$this->_ensure_column('expenses', 'construction_source', "VARCHAR(30) NULL DEFAULT NULL");
			$this->_ensure_column('expenses', 'construction_ref_id', 'INT NULL DEFAULT 0');
			$this->_ensure_column('expenses', 'construction_installment_id', 'INT NULL DEFAULT NULL');
			$this->_ensure_column('expenses', 'construction_contract_id', 'INT NULL DEFAULT NULL');
			$this->_ensure_column('expenses', 'construction_closing_id', 'INT NULL DEFAULT NULL');
			$this->_ensure_column('expenses', 'approved_by', 'VARCHAR(120) NULL DEFAULT NULL');
			$this->_ensure_column('expenses', 'approved_at', 'DATETIME NULL DEFAULT NULL');
		}

		$this->db->query("CREATE TABLE IF NOT EXISTS construction_expense_closings (
			id INT NOT NULL AUTO_INCREMENT,
			for_date DATE NOT NULL,
			total_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
			expense_count INT NOT NULL DEFAULT 0,
			verified_by VARCHAR(120) NULL,
			verified_by_id INT NULL,
			verified_at DATETIME NOT NULL,
			notes TEXT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY for_date (for_date)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		// Permission column for verify access
		foreach (array('access', 'access_rules') as $tbl) {
			if ($this->db->table_exists($tbl) && !$this->db->field_exists('construction_expense_verify', $tbl)) {
				$this->db->query("ALTER TABLE `$tbl` ADD `construction_expense_verify` TINYINT(1) NULL DEFAULT NULL");
			}
		}
	}

	private function _asset_base()
	{
		return rtrim(base_url(), '/');
	}

	private function _contractor_image_url($filename)
	{
		if (!$filename) return null;
		if (preg_match('/^https?:\\/\\//i', $filename)) return $filename;
		return $this->_asset_base() . '/uploads/construction/' . rawurlencode($filename);
	}

	/** Legacy expenses store files under /uploads/ (not construction/). */
	private function _expense_image_url($filename)
	{
		if (!$filename) return null;
		if (preg_match('/^https?:\\/\\//i', $filename)) return $filename;
		return $this->_asset_base() . '/uploads/' . rawurlencode($filename);
	}

	private function _upload_expense_image($field = 'image')
	{
		if (empty($_FILES[$field]['name'])) return '';
		$dir = FCPATH . 'uploads/';
		if (!is_dir($dir)) @mkdir($dir, 0775, true);
		$this->load->library('upload');
		$config = array(
			'upload_path' => $dir,
			'allowed_types' => 'gif|jpg|jpeg|png|webp',
			'encrypt_name' => true,
			'max_size' => 5120,
		);
		$this->upload->initialize($config);
		if (!$this->upload->do_upload($field)) return '';
		$d = $this->upload->data();
		return $d['file_name'];
	}

	private function _upload_construction_image($field = 'image')
	{
		if (empty($_FILES[$field]['name'])) return '';
		$dir = FCPATH . 'uploads/construction/';
		if (!is_dir($dir)) @mkdir($dir, 0775, true);
		$this->load->library('upload');
		$config = array(
			'upload_path' => $dir,
			'allowed_types' => 'gif|jpg|jpeg|png|webp',
			'encrypt_name' => true,
			'max_size' => 5120,
		);
		$this->upload->initialize($config);
		if (!$this->upload->do_upload($field)) return '';
		$d = $this->upload->data();
		return $d['file_name'];
	}

	private function _project($id)
	{
		return $this->db->get_where('construction_projects', array('id' => (int)$id))->row_array();
	}

	private function _project_summary($project_id)
	{
		$project_id = (int)$project_id;
		$done_row = $this->db->query(
			"SELECT COALESCE(SUM(total_amount),0) AS t FROM construction_contracts
			 WHERE project_id = ? AND status != 'cancelled'",
			array($project_id)
		)->row_array();
		$done = (float)$done_row['t'];
		$paid_row = $this->db->query(
			'SELECT COALESCE(SUM(amount),0) AS t FROM construction_contractor_payments WHERE project_id = ?',
			array($project_id)
		)->row_array();
		$contractor_paid = (float)$paid_row['t'];
		$count_row = $this->db->query(
			'SELECT COUNT(DISTINCT contractor_id) AS n FROM construction_contracts WHERE project_id = ?',
			array($project_id)
		)->row_array();

		// Include purchase installment expenses linked via purchase_no → project_id
		// (same rule as daily closing / expenses report).
		$pr_join = $this->_purchase_project_join_sql();
		$by_src = array('labour' => 0, 'contractor' => 0, 'misc' => 0, 'purchase' => 0);
		$expense_total = 0;
		if ($pr_join) {
			$rows = $this->db->query(
				"SELECT src, COALESCE(SUM(amount),0) AS t FROM (
					SELECT e.amount,
						CASE
							WHEN e.construction_source IN ('contractor','labour','misc','purchase') THEN e.construction_source
							WHEN pr.project_id IS NOT NULL AND pr.project_id > 0 THEN 'purchase'
							ELSE COALESCE(NULLIF(e.construction_source,''), 'misc')
						END AS src
					FROM expenses e
					LEFT JOIN {$pr_join} pr ON pr.purchase_no = e.purchase_no
					WHERE COALESCE(e.construction_project_id, pr.project_id) = ?
				) x GROUP BY src",
				array($project_id)
			)->result_array();
		} elseif ($this->db->table_exists('expenses') && $this->db->field_exists('construction_project_id', 'expenses')) {
			$rows = $this->db->query(
				"SELECT COALESCE(NULLIF(construction_source,''), 'misc') AS src, COALESCE(SUM(amount),0) AS t
				 FROM expenses
				 WHERE construction_project_id = ?
				 GROUP BY src",
				array($project_id)
			)->result_array();
		} else {
			$rows = array();
		}
		foreach ($rows as $r) {
			$src = isset($r['src']) ? $r['src'] : 'misc';
			if (!isset($by_src[$src])) $src = 'misc';
			$amt = (float)$r['t'];
			$by_src[$src] += $amt;
			$expense_total += $amt;
		}

		return array(
			'contractor_done' => $done,
			'contractor_paid' => $contractor_paid,
			'contractor_remaining' => max(0, $done - $contractor_paid),
			'labour_paid' => $by_src['labour'],
			'misc_total' => $by_src['misc'],
			'contractor_expense' => $by_src['contractor'],
			'purchase_expense' => $by_src['purchase'],
			'expense_total' => $expense_total,
			'contractor_count' => (int)$count_row['n'],
		);
	}

	/** Contractors linked to a project via contracts (not contractor.project_id). */
	private function _contractors_for_project($project_id)
	{
		$project_id = (int)$project_id;
		$this->db->select('construction_contractors.*', false);
		$this->db->from('construction_contractors');
		$this->db->join(
			'construction_contracts',
			'construction_contracts.contractor_id = construction_contractors.id',
			'inner'
		);
		$this->db->where('construction_contracts.project_id', $project_id);
		$this->db->group_by('construction_contractors.id');
		$this->db->order_by('construction_contractors.contractor_name', 'ASC');
		return $this->db->get()->result_array();
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
			'image' => !empty($opts['image']) ? $opts['image'] : '',
			'construction_project_id' => (int)$opts['project_id'],
			'construction_contractor_id' => isset($opts['contractor_id']) ? (int)$opts['contractor_id'] : null,
			'construction_labour_id' => isset($opts['labour_id']) ? (int)$opts['labour_id'] : null,
			'construction_source' => $opts['source'],
			'construction_ref_id' => isset($opts['ref_id']) ? (int)$opts['ref_id'] : 0,
		);
		if ($this->db->field_exists('construction_installment_id', 'expenses') && !empty($opts['installment_id'])) {
			$row['construction_installment_id'] = (int)$opts['installment_id'];
		}
		if ($this->db->field_exists('construction_contract_id', 'expenses') && !empty($opts['contract_id'])) {
			$row['construction_contract_id'] = (int)$opts['contract_id'];
		}
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
			$status = $this->_normalize_project_status(isset($body['status']) ? $body['status'] : 'Active');
			$progress = isset($body['progress_percent']) ? (float)$body['progress_percent'] : 0;
			if ($progress < 0) $progress = 0;
			if ($progress > 100) $progress = 100;
			$insert = array(
				'project_name' => $name,
				'location' => isset($body['location']) ? trim($body['location']) : '',
				'client' => isset($body['client']) ? trim($body['client']) : '',
				'start_date' => !empty($body['start_date']) ? $body['start_date'] : null,
				'expected_completion_date' => !empty($body['expected_completion_date']) ? $body['expected_completion_date'] : null,
				'budget' => isset($body['budget']) ? (float)$body['budget'] : 0,
				'status' => $status,
				'campus_id' => $campus_id,
				'created_by' => (int)$this->current_user['user_id'],
				'created_at' => date('Y-m-d H:i:s'),
			);
			if ($this->db->field_exists('progress_percent', 'construction_projects')) {
				$insert['progress_percent'] = $progress;
			}
			$this->db->insert('construction_projects', $insert);
			$this->_json(array('success' => true, 'id' => (int)$this->db->insert_id()));
		}

		$campus_id = (int)$this->input->get('campus_id');
		$status_filter = trim((string)$this->input->get('status'));
		if ($campus_id > 0 && $this->db->field_exists('campus_id', 'construction_projects')) {
			// Strict campus filter — only that campus's projects
			$this->db->where('campus_id', $campus_id);
		}
		if ($status_filter !== '' && strtolower($status_filter) !== 'all') {
			$norm = $this->_normalize_project_status($status_filter);
			$aliases = $this->_project_status_aliases($norm);
			$this->db->where_in('status', $aliases);
		}
		$rows = $this->db->order_by('id', 'DESC')->get('construction_projects')->result_array();
		foreach ($rows as &$r) {
			$r['status'] = $this->_normalize_project_status(isset($r['status']) ? $r['status'] : 'Active');
			$sum = $this->_project_summary($r['id']);
			$r['summary'] = $sum;
			$budget = (float)(isset($r['budget']) ? $r['budget'] : 0);
			$expense = (float)$sum['expense_total'];
			$r['expense_total'] = $expense;
			$r['remaining'] = $budget - $expense;
			$r['utilization_pct'] = $budget > 0 ? round(($expense / $budget) * 100, 1) : 0;
			$prog = isset($r['progress_percent']) ? (float)$r['progress_percent'] : 0;
			if ($prog < 0) $prog = 0;
			if ($prog > 100) $prog = 100;
			$r['progress_percent'] = $prog;
		}
		unset($r);
		$this->_json(array('success' => true, 'data' => $rows));
	}

	/** Allowed project statuses: Active | Inactive | Completed */
	private function _normalize_project_status($status)
	{
		$s = strtolower(trim((string)$status));
		if (in_array($s, array('inactive', 'paused', 'on hold', 'onhold', 'hold'), true)) {
			return 'Inactive';
		}
		if (in_array($s, array('completed', 'complete', 'done', 'finished'), true)) {
			return 'Completed';
		}
		// Planning / Running / Active / blank → Active
		return 'Active';
	}

	/** DB values that map to a normalized status (legacy rows included). */
	private function _project_status_aliases($normalized)
	{
		$normalized = $this->_normalize_project_status($normalized);
		if ($normalized === 'Inactive') {
			return array('Inactive', 'inactive', 'Paused', 'paused', 'On Hold', 'on hold', 'Hold', 'hold');
		}
		if ($normalized === 'Completed') {
			return array('Completed', 'completed', 'Complete', 'complete', 'Done', 'done', 'Finished', 'finished');
		}
		return array(
			'Active', 'active', 'Planning', 'planning', 'Running', 'running',
			'In Progress', 'in progress', 'Open', 'open', '',
		);
	}

	public function project($id = 0)
	{
		$id = (int)$id;
		$project = $this->_project($id);
		if (!$project) $this->_json(array('success' => false, 'message' => 'Project not found'), 404);

		$method = $_SERVER['REQUEST_METHOD'];
		if ($method === 'PUT' || $method === 'POST') {
			$this->_assert_manage();
			$body = $this->_body();
			$upd = array();
			if (array_key_exists('project_name', $body)) {
				$name = trim((string)$body['project_name']);
				if ($name === '') $this->_json(array('success' => false, 'message' => 'project_name required'), 422);
				$upd['project_name'] = $name;
			}
			if (array_key_exists('location', $body)) $upd['location'] = trim((string)$body['location']);
			if (array_key_exists('client', $body)) $upd['client'] = trim((string)$body['client']);
			if (array_key_exists('budget', $body)) $upd['budget'] = (float)$body['budget'];
			if (array_key_exists('start_date', $body)) {
				$upd['start_date'] = !empty($body['start_date']) ? $body['start_date'] : null;
			}
			if (array_key_exists('expected_completion_date', $body)) {
				$upd['expected_completion_date'] = !empty($body['expected_completion_date'])
					? $body['expected_completion_date']
					: null;
			}
			if (array_key_exists('status', $body)) {
				$upd['status'] = $this->_normalize_project_status($body['status']);
			}
			if (array_key_exists('progress_percent', $body)
				&& $this->db->field_exists('progress_percent', 'construction_projects')) {
				$prog = (float)$body['progress_percent'];
				if ($prog < 0) $prog = 0;
				if ($prog > 100) $prog = 100;
				$upd['progress_percent'] = $prog;
			}
			if (array_key_exists('campus_id', $body) && (int)$body['campus_id'] > 0) {
				$upd['campus_id'] = (int)$body['campus_id'];
			}
			if (!count($upd)) {
				$this->_json(array('success' => false, 'message' => 'Nothing to update'), 422);
			}
			$upd['updated_at'] = date('Y-m-d H:i:s');
			$this->db->where('id', $id)->update('construction_projects', $upd);
			$project = $this->_project($id);
			$project['status'] = $this->_normalize_project_status(isset($project['status']) ? $project['status'] : 'Active');
			$this->_json(array('success' => true, 'message' => 'Project updated', 'data' => $project));
		}

		$project['status'] = $this->_normalize_project_status(isset($project['status']) ? $project['status'] : 'Active');

		$contractors = $this->_contractors_for_project($id);
		foreach ($contractors as &$c) {
			$c = $this->_enrich_contractor($c);
		}
		$labours = $this->db->get_where('construction_labours', array('project_id' => $id, 'status' => 1))->result_array();
		if (!count($labours)) {
			$labours = $this->db->get_where('construction_labours', array('project_id' => $id))->result_array();
		}
		foreach ($labours as &$l) {
			$l = $this->_enrich_labour($l);
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

	/**
	 * Detail rows for project summary tiles.
	 * GET project_ledger/{id}?type=expense_total|contractor_done|contractor_paid|remaining|labour|misc
	 */
	public function project_ledger($id = 0)
	{
		$id = (int)$id;
		if (!$this->_project($id)) $this->_json(array('success' => false, 'message' => 'Project not found'), 404);
		$type = trim((string)$this->input->get('type'));
		$allowed = array('expense_total', 'contractor_done', 'contractor_paid', 'remaining', 'labour', 'misc');
		if (!in_array($type, $allowed, true)) {
			$this->_json(array('success' => false, 'message' => 'type required: ' . implode('|', $allowed)), 422);
		}

		$rows = array();
		$total = 0.0;
		$title = '';

		if ($type === 'expense_total' || $type === 'labour' || $type === 'misc') {
			$source = $type === 'labour' ? 'labour' : ($type === 'misc' ? 'misc' : '');
			$this->db->select(
				'expenses.*, construction_contractors.contractor_name, construction_labours.labour_name, construction_projects.project_name',
				false
			);
			$this->db->from('expenses');
			$this->db->join('construction_contractors', 'construction_contractors.id = expenses.construction_contractor_id', 'left');
			$this->db->join('construction_labours', 'construction_labours.id = expenses.construction_labour_id', 'left');
			$this->db->join('construction_projects', 'construction_projects.id = expenses.construction_project_id', 'left');
			$this->db->where('expenses.construction_project_id', $id);
			if ($source !== '') $this->db->where('expenses.construction_source', $source);
			$this->db->order_by('expenses.date', 'DESC');
			$this->db->order_by('expenses.expense_id', 'DESC');
			$rows = $this->db->get()->result_array();
			foreach ($rows as &$r) {
				$total += (float)$r['amount'];
				$this->_decorate_expense_row($r);
				$r['row_kind'] = 'expense';
			}
			$title = $type === 'labour' ? 'Labour expenses' : ($type === 'misc' ? 'Misc expenses' : 'All expenses');
		} elseif ($type === 'contractor_done') {
			$this->db->select(
				'construction_contracts.*, construction_contractors.contractor_name',
				false
			);
			$this->db->from('construction_contracts');
			$this->db->join(
				'construction_contractors',
				'construction_contractors.id = construction_contracts.contractor_id',
				'left'
			);
			$this->db->where('construction_contracts.project_id', $id);
			$this->db->where('construction_contracts.status !=', 'cancelled');
			$this->db->order_by('construction_contracts.id', 'DESC');
			$contracts = $this->db->get()->result_array();
			foreach ($contracts as $ct) {
				$ct = $this->_enrich_contract($ct);
				$total += (float)$ct['total_amount'];
				$rows[] = array(
					'row_kind' => 'contract',
					'id' => (int)$ct['id'],
					'title' => $ct['title'],
					'contractor_name' => isset($ct['contractor_name']) ? $ct['contractor_name'] : '',
					'contractor_id' => (int)$ct['contractor_id'],
					'total_amount' => (float)$ct['total_amount'],
					'paid_amount' => (float)$ct['paid_amount'],
					'remaining' => (float)$ct['remaining'],
					'installments_count' => (int)$ct['installments_count'],
					'start_date' => $ct['start_date'],
					'amount' => (float)$ct['total_amount'],
					'party_name' => isset($ct['contractor_name']) ? $ct['contractor_name'] : '',
					'date' => $ct['start_date'],
					'purpose' => 'Contract · Paid ' . number_format((float)$ct['paid_amount'], 2) . ' · Rem ' . number_format((float)$ct['remaining'], 2),
				);
			}
			$title = 'Contracts (done amount)';
		} elseif ($type === 'contractor_paid') {
			$this->db->select(
				'construction_contractor_payments.*, construction_contractors.contractor_name, construction_contracts.title as contract_title',
				false
			);
			$this->db->from('construction_contractor_payments');
			$this->db->join(
				'construction_contractors',
				'construction_contractors.id = construction_contractor_payments.contractor_id',
				'left'
			);
			$this->db->join(
				'construction_contracts',
				'construction_contracts.id = construction_contractor_payments.contract_id',
				'left'
			);
			$this->db->where('construction_contractor_payments.project_id', $id);
			$this->db->order_by('construction_contractor_payments.payment_date', 'DESC');
			$this->db->order_by('construction_contractor_payments.id', 'DESC');
			$pays = $this->db->get()->result_array();
			foreach ($pays as $p) {
				$total += (float)$p['amount'];
				$rows[] = array(
					'row_kind' => 'payment',
					'id' => (int)$p['id'],
					'date' => $p['payment_date'],
					'amount' => (float)$p['amount'],
					'party_name' => $p['contractor_name'],
					'contractor_id' => (int)$p['contractor_id'],
					'payment_type' => $p['payment_type'],
					'purpose' => trim(
						($p['contract_title'] ? $p['contract_title'] . ' · ' : '')
						. ($p['payment_type'] ?: '')
						. ($p['remarks'] ? ' · ' . $p['remarks'] : '')
					),
					'construction_source' => 'contractor',
				);
			}
			$title = 'Contractor payments';
		} else {
			// remaining — unpaid installments
			$this->db->select(
				'construction_contract_installments.*, construction_contracts.title as contract_title, construction_contractors.contractor_name',
				false
			);
			$this->db->from('construction_contract_installments');
			$this->db->join(
				'construction_contracts',
				'construction_contracts.id = construction_contract_installments.contract_id',
				'left'
			);
			$this->db->join(
				'construction_contractors',
				'construction_contractors.id = construction_contract_installments.contractor_id',
				'left'
			);
			$this->db->where('construction_contract_installments.project_id', $id);
			$this->db->where('construction_contract_installments.paid', 0);
			$this->db->order_by('construction_contract_installments.due_date', 'ASC');
			$insts = $this->db->get()->result_array();
			foreach ($insts as $i) {
				$total += (float)$i['amount'];
				$rows[] = array(
					'row_kind' => 'installment',
					'id' => (int)$i['id'],
					'date' => $i['due_date'],
					'amount' => (float)$i['amount'],
					'party_name' => $i['contractor_name'],
					'contractor_id' => (int)$i['contractor_id'],
					'purpose' => trim(
						($i['contract_title'] ?: 'Contract')
						. ' · '
						. ($i['label'] ?: ('Inst #' . $i['installment_no']))
					),
					'construction_source' => 'contractor',
				);
			}
			$title = 'Unpaid installments (remaining)';
		}

		$this->_json(array(
			'success' => true,
			'data' => $rows,
			'total' => $total,
			'type' => $type,
			'title' => $title,
		));
	}

	private function _enrich_contractor($c)
	{
		$cid = (int)$c['id'];
		$contracts_total = $this->db->query(
			'SELECT COALESCE(SUM(total_amount),0) AS t FROM construction_contracts WHERE contractor_id = ? AND status != ?',
			array($cid, 'cancelled')
		)->row_array();
		$contracts_sum = (float)$contracts_total['t'];
		$final = (float)$c['final_bill'];
		$contract = (float)$c['contract_amount'];
		$c['done_amount'] = $contracts_sum > 0
			? $contracts_sum
			: ($final > 0 ? $final : $contract);
		$paid = $this->db->query(
			'SELECT COALESCE(SUM(amount),0) AS t FROM construction_contractor_payments WHERE contractor_id = ?',
			array($cid)
		)->row_array();
		$c['paid_amount'] = (float)$paid['t'];
		$c['remaining'] = max(0, $c['done_amount'] - $c['paid_amount']);
		$c['image_url'] = $this->_contractor_image_url(isset($c['image']) ? $c['image'] : '');
		$c['contracts_count'] = (int)$this->db->where('contractor_id', $cid)->count_all_results('construction_contracts');
		$unpaid = $this->db->query(
			'SELECT COALESCE(SUM(amount),0) AS t FROM construction_contract_installments WHERE contractor_id = ? AND paid = 0',
			array($cid)
		)->row_array();
		$c['unpaid_installments_amount'] = (float)$unpaid['t'];
		$projects = $this->db->query(
			"SELECT GROUP_CONCAT(DISTINCT construction_projects.project_name ORDER BY construction_projects.project_name SEPARATOR ', ') AS names
			 FROM construction_contracts
			 LEFT JOIN construction_projects ON construction_projects.id = construction_contracts.project_id
			 WHERE construction_contracts.contractor_id = ?",
			array($cid)
		)->row_array();
		$c['project_names'] = $projects && $projects['names'] ? $projects['names'] : '';
		$c['project_name'] = $c['project_names']; // list column compatibility
		return $c;
	}

	private function _enrich_labour($l)
	{
		$paid = $this->db->query(
			'SELECT COALESCE(SUM(amount),0) AS t FROM construction_labour_advances WHERE labour_id = ?',
			array((int)$l['id'])
		)->row_array();
		$l['paid_amount'] = (float)$paid['t'];
		$l['payment_count'] = (int)$this->db->where('labour_id', (int)$l['id'])->count_all_results('construction_labour_advances');
		return $l;
	}

	private function _decorate_expense_row(&$r)
	{
		if (!empty($r['construction_source_resolved'])) {
			$r['construction_source'] = $r['construction_source_resolved'];
		}
		if (empty($r['construction_project_id']) && !empty($r['linked_project_id'])) {
			$r['construction_project_id'] = (int)$r['linked_project_id'];
		}
		$src = isset($r['construction_source']) ? $r['construction_source'] : 'misc';
		if ($src === 'contractor') {
			$r['party_name'] = !empty($r['contractor_name']) ? $r['contractor_name'] : 'Contractor';
		} elseif ($src === 'labour') {
			$r['party_name'] = !empty($r['labour_name']) ? $r['labour_name'] : 'Labour';
		} elseif ($src === 'purchase') {
			$r['party_name'] = !empty($r['vendor_name']) ? $r['vendor_name'] : 'Purchase';
		} else {
			$r['party_name'] = 'Misc';
		}
		$r['image_url'] = $this->_expense_image_url(isset($r['image']) ? $r['image'] : '');
		$closing_id = isset($r['construction_closing_id']) ? (int)$r['construction_closing_id'] : 0;
		$r['is_verified'] = $closing_id > 0 || !empty($r['approved_by']);
	}

	/** Subquery: purchase_no → project_id (one row per PR). */
	private function _purchase_project_join_sql()
	{
		if (!$this->db->table_exists('purchase_requests')
			|| !$this->db->field_exists('project_id', 'purchase_requests')
			|| !$this->db->field_exists('purchase_no', 'expenses')) {
			return null;
		}
		return "(SELECT purchase_no, MIN(project_id) AS project_id
			FROM purchase_requests
			WHERE project_id IS NOT NULL AND project_id > 0
			GROUP BY purchase_no)";
	}

	private function _last_verified_expense_date()
	{
		if (!$this->db->table_exists('construction_expense_closings')) return null;
		$row = $this->db->order_by('for_date', 'DESC')->limit(1)->get('construction_expense_closings')->row_array();
		return $row && !empty($row['for_date']) ? $row['for_date'] : null;
	}

	/** Next calendar day after last verify; if never verified, start from today. */
	private function _next_pending_expense_date()
	{
		$today = date('Y-m-d');
		$last = $this->_last_verified_expense_date();
		if ($last) {
			$next = date('Y-m-d', strtotime($last . ' +1 day'));
			return $next <= $today ? $next : null;
		}
		return $today;
	}

	private function _expense_day_is_verified($date)
	{
		$date = trim((string)$date);
		if ($date === '' || !$this->db->table_exists('construction_expense_closings')) return false;
		$row = $this->db->get_where('construction_expense_closings', array('for_date' => $date))->row_array();
		return !empty($row);
	}

	private function _assert_expense_day_open($date)
	{
		if ($this->_expense_day_is_verified($date)) {
			$this->_json(array(
				'success' => false,
				'message' => 'This day is already verified. Changes are locked.',
			), 400);
		}
	}

	private function _day_expense_totals($date)
	{
		$totals = array(
			'total_amount' => 0,
			'expense_count' => 0,
			'contractor_total' => 0,
			'labour_total' => 0,
			'misc_total' => 0,
			'purchase_total' => 0,
		);
		if (!$this->db->table_exists('expenses')) return $totals;

		$pr_join = $this->_purchase_project_join_sql();
		if ($pr_join) {
			$sql = "SELECT src, COALESCE(SUM(amount),0) AS t, COUNT(*) AS n FROM (
				SELECT e.amount,
					CASE
						WHEN e.construction_source IN ('contractor','labour','misc','purchase') THEN e.construction_source
						WHEN pr.project_id IS NOT NULL AND pr.project_id > 0 THEN 'purchase'
						ELSE COALESCE(NULLIF(e.construction_source,''), 'misc')
					END AS src
				FROM expenses e
				LEFT JOIN {$pr_join} pr ON pr.purchase_no = e.purchase_no
				WHERE e.date = ?
				  AND (
					(e.construction_project_id IS NOT NULL AND e.construction_project_id > 0)
					OR (pr.project_id IS NOT NULL AND pr.project_id > 0)
				  )
			) x GROUP BY src";
			$rows = $this->db->query($sql, array($date))->result_array();
		} elseif ($this->db->field_exists('construction_project_id', 'expenses')) {
			$rows = $this->db->query(
				"SELECT construction_source AS src, COALESCE(SUM(amount),0) AS t, COUNT(*) AS n
				 FROM expenses
				 WHERE construction_project_id IS NOT NULL AND construction_project_id > 0
				   AND date = ?
				 GROUP BY construction_source",
				array($date)
			)->result_array();
		} else {
			return $totals;
		}

		foreach ($rows as $r) {
			$amt = (float)$r['t'];
			$n = (int)$r['n'];
			$totals['total_amount'] += $amt;
			$totals['expense_count'] += $n;
			$src = isset($r['src']) ? $r['src'] : 'misc';
			if ($src === 'contractor') $totals['contractor_total'] += $amt;
			elseif ($src === 'labour') $totals['labour_total'] += $amt;
			elseif ($src === 'purchase') $totals['purchase_total'] += $amt;
			else $totals['misc_total'] += $amt;
		}
		return $totals;
	}

	private function _day_expense_rows($date)
	{
		if (!$this->db->table_exists('expenses')) return array();

		$pr_join = $this->_purchase_project_join_sql();
		if ($pr_join) {
			$sql = "SELECT e.*,
					cc.contractor_name, cl.labour_name,
					COALESCE(cp.project_name, cp2.project_name) AS project_name,
					COALESCE(e.construction_project_id, pr.project_id) AS linked_project_id,
					CASE
						WHEN e.construction_source IN ('contractor','labour','misc','purchase') THEN e.construction_source
						WHEN pr.project_id IS NOT NULL AND pr.project_id > 0 THEN 'purchase'
						ELSE COALESCE(NULLIF(e.construction_source,''), 'misc')
					END AS construction_source_resolved,
					(SELECT v.name FROM payment_aggrements pa
					 LEFT JOIN vendors v ON v.id = pa.vendor_id
					 WHERE pa.purchase_no = e.purchase_no
					 ORDER BY pa.paid DESC, pa.date DESC LIMIT 1) AS vendor_name
				FROM expenses e
				LEFT JOIN construction_contractors cc ON cc.id = e.construction_contractor_id
				LEFT JOIN construction_labours cl ON cl.id = e.construction_labour_id
				LEFT JOIN construction_projects cp ON cp.id = e.construction_project_id
				LEFT JOIN {$pr_join} pr ON pr.purchase_no = e.purchase_no
				LEFT JOIN construction_projects cp2 ON cp2.id = pr.project_id
				WHERE e.date = ?
				  AND (
					(e.construction_project_id IS NOT NULL AND e.construction_project_id > 0)
					OR (pr.project_id IS NOT NULL AND pr.project_id > 0)
				  )
				ORDER BY e.expense_id DESC";
			$rows = $this->db->query($sql, array($date))->result_array();
		} elseif ($this->db->field_exists('construction_project_id', 'expenses')) {
			$this->db->select(
				'expenses.*, construction_contractors.contractor_name, construction_labours.labour_name, construction_projects.project_name',
				false
			);
			$this->db->from('expenses');
			$this->db->join('construction_contractors', 'construction_contractors.id = expenses.construction_contractor_id', 'left');
			$this->db->join('construction_labours', 'construction_labours.id = expenses.construction_labour_id', 'left');
			$this->db->join('construction_projects', 'construction_projects.id = expenses.construction_project_id', 'left');
			$this->db->where('expenses.construction_project_id IS NOT NULL', null, false);
			$this->db->where('expenses.construction_project_id >', 0);
			$this->db->where('expenses.date', $date);
			$this->db->order_by('expenses.expense_id', 'DESC');
			$rows = $this->db->get()->result_array();
		} else {
			return array();
		}

		foreach ($rows as &$r) {
			$this->_decorate_expense_row($r);
		}
		return $rows;
	}

	/** Stamp approved_by / closing_id on all construction (+ project purchase) expenses for a day. */
	private function _stamp_day_expenses_verified($for_date, $closing_id, $name, $now)
	{
		if (!$this->db->table_exists('expenses')) return;
		$upd = array();
		if ($this->db->field_exists('construction_closing_id', 'expenses')) {
			$upd['construction_closing_id'] = (int)$closing_id;
		}
		if ($this->db->field_exists('approved_by', 'expenses')) {
			$upd['approved_by'] = $name;
		}
		if ($this->db->field_exists('approved_at', 'expenses')) {
			$upd['approved_at'] = $now;
		}
		if (!count($upd)) return;

		$ids = array();
		foreach ($this->_day_expense_rows($for_date) as $r) {
			if (!empty($r['expense_id'])) $ids[] = (int)$r['expense_id'];
		}
		$ids = array_values(array_unique($ids));
		if (!count($ids)) return;
		$this->db->where_in('expense_id', $ids)->update('expenses', $upd);

		// Also backfill construction_project_id / source on purchase cash-outs when missing
		if ($this->db->field_exists('construction_project_id', 'expenses')) {
			$pr_join = $this->_purchase_project_join_sql();
			if ($pr_join) {
				$this->db->query(
					"UPDATE expenses e
					 INNER JOIN {$pr_join} pr ON pr.purchase_no = e.purchase_no
					 SET e.construction_project_id = pr.project_id,
					     e.construction_source = COALESCE(NULLIF(e.construction_source,''), 'purchase')
					 WHERE e.expense_id IN (" . implode(',', array_map('intval', $ids)) . ")
					   AND (e.construction_project_id IS NULL OR e.construction_project_id = 0)"
				);
			}
		}
	}

	// ─── Contractors / Labours ──────────────────────────────

	public function contractors()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			$project_id = (int)$this->input->get('project_id');
			if ($project_id > 0) {
				$rows = $this->_contractors_for_project($project_id);
			} else {
				$rows = $this->db->order_by('id', 'DESC')->get('construction_contractors')->result_array();
			}
			foreach ($rows as &$c) {
				$c = $this->_enrich_contractor($c);
			}
			$this->_json(array('success' => true, 'data' => $rows));
		}

		$body = $this->_body();
		$name = isset($body['contractor_name']) ? trim($body['contractor_name']) : '';
		if ($name === '') {
			$this->_json(array('success' => false, 'message' => 'contractor_name required'), 422);
		}

		$this->db->insert('construction_contractors', array(
			'project_id' => null,
			'contractor_name' => $name,
			'contact_details' => isset($body['contact_details']) ? $body['contact_details'] : '',
			'mobile' => isset($body['mobile']) ? trim($body['mobile']) : '',
			'cnic' => isset($body['cnic']) ? trim($body['cnic']) : '',
			'address' => isset($body['address']) ? trim($body['address']) : '',
			'contract_amount' => isset($body['contract_amount']) ? (float)$body['contract_amount'] : 0,
			'advance_payment' => 0,
			'running_bills' => 0,
			'final_bill' => isset($body['final_bill']) ? (float)$body['final_bill'] : 0,
			'created_at' => date('Y-m-d H:i:s'),
		));
		$this->_json(array('success' => true, 'id' => (int)$this->db->insert_id()));
	}

	public function contractor($id = 0)
	{
		$id = (int)$id;
		$row = $this->db->get_where('construction_contractors', array('id' => $id))->row_array();
		if (!$row) $this->_json(array('success' => false, 'message' => 'Contractor not found'), 404);

		$method = $_SERVER['REQUEST_METHOD'];
		if ($method === 'PUT' || $method === 'POST') {
			$body = $this->_body();
			$update = array();
			if (isset($body['contractor_name'])) {
				$name = trim($body['contractor_name']);
				if ($name === '') $this->_json(array('success' => false, 'message' => 'contractor_name required'), 422);
				$update['contractor_name'] = $name;
			}
			if (array_key_exists('contact_details', $body)) $update['contact_details'] = $body['contact_details'];
			if (array_key_exists('mobile', $body)) $update['mobile'] = trim((string)$body['mobile']);
			if (array_key_exists('cnic', $body)) $update['cnic'] = trim((string)$body['cnic']);
			if (array_key_exists('address', $body)) $update['address'] = trim((string)$body['address']);
			if (isset($body['contract_amount'])) $update['contract_amount'] = (float)$body['contract_amount'];
			if (isset($body['final_bill'])) $update['final_bill'] = (float)$body['final_bill'];
			// project_id on contractor is ignored — link is via contracts
			if (!count($update)) $this->_json(array('success' => false, 'message' => 'Nothing to update'), 422);
			$this->db->where('id', $id)->update('construction_contractors', $update);
			$this->_json(array('success' => true, 'message' => 'Updated'));
		}

		if ($method === 'DELETE') {
			$pay_count = (int)$this->db->where('contractor_id', $id)->count_all_results('construction_contractor_payments');
			if ($pay_count > 0) {
				$this->_json(array('success' => false, 'message' => 'Cannot delete: contractor has payments. Remove payments first.'), 422);
			}
			$ct_count = (int)$this->db->where('contractor_id', $id)->count_all_results('construction_contracts');
			if ($ct_count > 0) {
				$this->_json(array('success' => false, 'message' => 'Cannot delete: contractor has contracts. Remove contracts first.'), 422);
			}
			$this->db->where('id', $id)->delete('construction_contractors');
			$this->_json(array('success' => true, 'message' => 'Deleted'));
		}

		$row = $this->_enrich_contractor($row);

		$payments = $this->db->order_by('payment_date', 'DESC')
			->order_by('id', 'DESC')
			->get_where('construction_contractor_payments', array('contractor_id' => $id))
			->result_array();

		$contracts = $this->db->order_by('id', 'DESC')
			->get_where('construction_contracts', array('contractor_id' => $id))
			->result_array();
		foreach ($contracts as &$ct) {
			$ct = $this->_enrich_contract($ct);
		}

		$this->_json(array(
			'success' => true,
			'data' => array(
				'contractor' => $row,
				'payments' => $payments,
				'contracts' => $contracts,
				'asset_base' => $this->_asset_base(),
			),
		));
	}

	private function _enrich_contract($ct)
	{
		$cid = (int)$ct['id'];
		$paid = $this->db->query(
			'SELECT COALESCE(SUM(paid_amount),0) AS t, COUNT(*) AS n,
			 SUM(CASE WHEN paid=0 THEN 1 ELSE 0 END) AS unpaid_n
			 FROM construction_contract_installments WHERE contract_id = ?',
			array($cid)
		)->row_array();
		$ct['paid_amount'] = (float)$paid['t'];
		$ct['installments_count'] = (int)$paid['n'];
		$ct['unpaid_count'] = (int)$paid['unpaid_n'];
		$ct['remaining'] = max(0, (float)$ct['total_amount'] - $ct['paid_amount']);
		$ct['installments'] = $this->db->order_by('installment_no', 'ASC')
			->get_where('construction_contract_installments', array('contract_id' => $cid))
			->result_array();
		$images = $this->db->order_by('id', 'DESC')
			->get_where('construction_contract_images', array('contract_id' => $cid))
			->result_array();
		foreach ($images as &$img) {
			$img['url'] = $this->_contractor_image_url($img['filename']);
		}
		$ct['images'] = $images;
		$ct['images_count'] = count($images);
		$project = $this->_project((int)$ct['project_id']);
		$ct['project_name'] = $project ? $project['project_name'] : '';
		return $ct;
	}

	/** POST multipart contractor/{id}/image — field name: image */
	public function contractor_image($id = 0)
	{
		$id = (int)$id;
		$row = $this->db->get_where('construction_contractors', array('id' => $id))->row_array();
		if (!$row) $this->_json(array('success' => false, 'message' => 'Contractor not found'), 404);
		$file = $this->_upload_construction_image('image');
		if ($file === '') $this->_json(array('success' => false, 'message' => 'Image upload failed'), 422);
		$this->db->where('id', $id)->update('construction_contractors', array('image' => $file));
		$this->_json(array(
			'success' => true,
			'image' => $file,
			'image_url' => $this->_contractor_image_url($file),
		));
	}

	/**
	 * POST multipart contract_images/{contract_id}
	 * Fields: image (single) and/or images[] (multiple)
	 */
	public function contract_images($contract_id = 0)
	{
		$contract_id = (int)$contract_id;
		$contract = $this->db->get_where('construction_contracts', array('id' => $contract_id))->row_array();
		if (!$contract) $this->_json(array('success' => false, 'message' => 'Contract not found'), 404);

		if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
			$body = $this->_body();
			$image_id = isset($body['image_id']) ? (int)$body['image_id'] : (int)$this->input->get('image_id');
			$row = $this->db->get_where('construction_contract_images', array(
				'id' => $image_id,
				'contract_id' => $contract_id,
			))->row_array();
			if (!$row) $this->_json(array('success' => false, 'message' => 'Image not found'), 404);
			$path = FCPATH . 'uploads/construction/' . $row['filename'];
			if (is_file($path)) @unlink($path);
			$this->db->where('id', $image_id)->delete('construction_contract_images');
			$this->_json(array('success' => true, 'message' => 'Image deleted'));
		}

		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			$images = $this->db->order_by('id', 'DESC')
				->get_where('construction_contract_images', array('contract_id' => $contract_id))
				->result_array();
			foreach ($images as &$img) {
				$img['url'] = $this->_contractor_image_url($img['filename']);
			}
			$this->_json(array('success' => true, 'data' => $images));
		}

		$saved = array();
		// Single file field "image"
		if (!empty($_FILES['image']['name']) && !is_array($_FILES['image']['name'])) {
			$file = $this->_upload_construction_image('image');
			if ($file !== '') {
				$this->db->insert('construction_contract_images', array(
					'contract_id' => $contract_id,
					'contractor_id' => (int)$contract['contractor_id'],
					'filename' => $file,
					'caption' => $this->input->post('caption') ?: '',
					'created_at' => date('Y-m-d H:i:s'),
				));
				$saved[] = array(
					'id' => (int)$this->db->insert_id(),
					'filename' => $file,
					'url' => $this->_contractor_image_url($file),
				);
			}
		}
		// Multiple: images[]
		if (!empty($_FILES['images']['name']) && is_array($_FILES['images']['name'])) {
			$count = count($_FILES['images']['name']);
			for ($i = 0; $i < $count; $i++) {
				if (empty($_FILES['images']['name'][$i])) continue;
				$_FILES['image_tmp'] = array(
					'name' => $_FILES['images']['name'][$i],
					'type' => $_FILES['images']['type'][$i],
					'tmp_name' => $_FILES['images']['tmp_name'][$i],
					'error' => $_FILES['images']['error'][$i],
					'size' => $_FILES['images']['size'][$i],
				);
				$file = $this->_upload_construction_image('image_tmp');
				if ($file === '') continue;
				$this->db->insert('construction_contract_images', array(
					'contract_id' => $contract_id,
					'contractor_id' => (int)$contract['contractor_id'],
					'filename' => $file,
					'caption' => '',
					'created_at' => date('Y-m-d H:i:s'),
				));
				$saved[] = array(
					'id' => (int)$this->db->insert_id(),
					'filename' => $file,
					'url' => $this->_contractor_image_url($file),
				);
			}
		}

		if (!count($saved)) {
			$this->_json(array('success' => false, 'message' => 'No images uploaded'), 422);
		}
		$this->_json(array('success' => true, 'data' => $saved, 'message' => count($saved) . ' image(s) added'));
	}

	/** GET/POST construction contracts for a contractor */
	public function contracts($contractor_id = 0)
	{
		$contractor_id = (int)$contractor_id;
		$c = $this->db->get_where('construction_contractors', array('id' => $contractor_id))->row_array();
		if (!$c) $this->_json(array('success' => false, 'message' => 'Contractor not found'), 404);

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			$rows = $this->db->order_by('id', 'DESC')
				->get_where('construction_contracts', array('contractor_id' => $contractor_id))
				->result_array();
			foreach ($rows as &$ct) $ct = $this->_enrich_contract($ct);
			$this->_json(array('success' => true, 'data' => $rows));
		}

		$body = $this->_body();
		$title = isset($body['title']) ? trim($body['title']) : '';
		$total = isset($body['total_amount']) ? (float)$body['total_amount'] : 0;
		$project_id = (int)(isset($body['project_id']) ? $body['project_id'] : 0);
		if ($title === '' || $total <= 0) {
			$this->_json(array('success' => false, 'message' => 'title and total_amount required'), 422);
		}
		if (!$project_id) {
			$this->_json(array('success' => false, 'message' => 'project_id required — contract links contractor to a project'), 422);
		}
		if (!$this->_project($project_id)) {
			$this->_json(array('success' => false, 'message' => 'Project not found'), 404);
		}
		$start = !empty($body['start_date']) ? $body['start_date'] : date('Y-m-d');
		$this->db->insert('construction_contracts', array(
			'contractor_id' => $contractor_id,
			'project_id' => $project_id,
			'title' => $title,
			'total_amount' => $total,
			'start_date' => $start,
			'status' => 'active',
			'notes' => isset($body['notes']) ? $body['notes'] : '',
			'created_at' => date('Y-m-d H:i:s'),
		));
		$contract_id = (int)$this->db->insert_id();

		// Manual installment rows: [{due_date, amount, label?}]
		$rows = (isset($body['installment_rows']) && is_array($body['installment_rows']))
			? $body['installment_rows']
			: array();
		if (count($rows)) {
			$sum = 0.0;
			$clean = array();
			foreach ($rows as $i => $r) {
				$amt = isset($r['amount']) ? (float)$r['amount'] : 0;
				$due = !empty($r['due_date']) ? $r['due_date'] : '';
				if ($amt <= 0 || $due === '') {
					$this->_json(array(
						'success' => false,
						'message' => 'Each installment needs due_date and amount > 0',
					), 422);
				}
				$sum += $amt;
				$clean[] = array(
					'due_date' => $due,
					'amount' => $amt,
					'label' => !empty($r['label']) ? trim($r['label']) : ('Installment ' . ($i + 1)),
				);
			}
			if ($sum > $total + 0.009) {
				$this->_json(array(
					'success' => false,
					'message' => 'Installments total (' . number_format($sum, 2) . ') cannot exceed contract total (' . number_format($total, 2) . ')',
				), 422);
			}
			foreach ($clean as $i => $row) {
				$this->db->insert('construction_contract_installments', array(
					'contract_id' => $contract_id,
					'contractor_id' => $contractor_id,
					'project_id' => $project_id,
					'installment_no' => $i + 1,
					'due_date' => $row['due_date'],
					'amount' => $row['amount'],
					'paid' => 0,
					'paid_amount' => 0,
					'label' => $row['label'],
					'created_at' => date('Y-m-d H:i:s'),
				));
			}
		} else {
			// Legacy: auto-split by count
			$installments = isset($body['installments']) ? (int)$body['installments'] : 0;
			$installment_day = isset($body['installment_day']) ? (int)$body['installment_day'] : 1;
			if ($installments > 0) {
				$this->_generate_installments($contract_id, $contractor_id, $project_id, $total, $start, $installments, $installment_day);
			}
		}

		// Keep contractor.contract_amount as rollup of active contracts
		$sum = $this->db->query(
			'SELECT COALESCE(SUM(total_amount),0) AS t FROM construction_contracts WHERE contractor_id = ? AND status != ?',
			array($contractor_id, 'cancelled')
		)->row_array();
		$this->db->where('id', $contractor_id)->update('construction_contractors', array(
			'contract_amount' => (float)$sum['t'],
		));

		$this->_json(array('success' => true, 'id' => $contract_id));
	}

	private function _generate_installments($contract_id, $contractor_id, $project_id, $total, $start, $count, $day = 1)
	{
		$count = max(1, (int)$count);
		$day = max(1, min(28, (int)$day));
		$each = round($total / $count, 2);
		for ($i = 0; $i < $count; $i++) {
			$amt = ($i === $count - 1) ? ($total - $each * ($count - 1)) : $each;
			$dl = date('Y-m-d', strtotime($start . ' +' . $i . ' month'));
			$dl = date('Y-m-', strtotime($dl)) . str_pad((string)$day, 2, '0', STR_PAD_LEFT);
			$this->db->insert('construction_contract_installments', array(
				'contract_id' => $contract_id,
				'contractor_id' => $contractor_id,
				'project_id' => $project_id,
				'installment_no' => $i + 1,
				'due_date' => $dl,
				'amount' => $amt,
				'paid' => 0,
				'paid_amount' => 0,
				'label' => 'Installment ' . ($i + 1),
				'created_at' => date('Y-m-d H:i:s'),
			));
		}
	}

	public function contract($id = 0)
	{
		$id = (int)$id;
		$row = $this->db->get_where('construction_contracts', array('id' => $id))->row_array();
		if (!$row) $this->_json(array('success' => false, 'message' => 'Contract not found'), 404);

		$method = $_SERVER['REQUEST_METHOD'];
		if ($method === 'DELETE') {
			$paid_n = (int)$this->db->where(array('contract_id' => $id, 'paid' => 1))
				->count_all_results('construction_contract_installments');
			if ($paid_n > 0) {
				$this->_json(array('success' => false, 'message' => 'Cannot delete: some installments are paid'), 422);
			}
			$imgs = $this->db->get_where('construction_contract_images', array('contract_id' => $id))->result_array();
			foreach ($imgs as $img) {
				$path = FCPATH . 'uploads/construction/' . $img['filename'];
				if (is_file($path)) @unlink($path);
			}
			$this->db->where('contract_id', $id)->delete('construction_contract_images');
			$this->db->where('contract_id', $id)->delete('construction_contract_installments');
			$this->db->where('id', $id)->delete('construction_contracts');
			$sum = $this->db->query(
				'SELECT COALESCE(SUM(total_amount),0) AS t FROM construction_contracts WHERE contractor_id = ? AND status != ?',
				array((int)$row['contractor_id'], 'cancelled')
			)->row_array();
			$this->db->where('id', (int)$row['contractor_id'])->update('construction_contractors', array(
				'contract_amount' => (float)$sum['t'],
			));
			$this->_json(array('success' => true, 'message' => 'Deleted'));
		}

		if ($method === 'PUT' || $method === 'POST') {
			$body = $this->_body();
			// Generate / regenerate installments if requested
			if (!empty($body['generate_installments'])) {
				$paid_n = (int)$this->db->where(array('contract_id' => $id, 'paid' => 1))
					->count_all_results('construction_contract_installments');
				if ($paid_n > 0) {
					$this->_json(array('success' => false, 'message' => 'Cannot regenerate: paid installments exist'), 422);
				}
				$this->db->where('contract_id', $id)->delete('construction_contract_installments');
				$count = isset($body['installments']) ? (int)$body['installments'] : 1;
				$day = isset($body['installment_day']) ? (int)$body['installment_day'] : 1;
				$start = !empty($body['start_date']) ? $body['start_date'] : ($row['start_date'] ?: date('Y-m-d'));
				$total_for_gen = isset($body['total_amount']) ? (float)$body['total_amount'] : (float)$row['total_amount'];
				$this->_generate_installments(
					$id,
					(int)$row['contractor_id'],
					(int)$row['project_id'],
					$total_for_gen,
					$start,
					$count,
					$day
				);
				$this->_json(array('success' => true, 'message' => 'Installments generated'));
			}

			$upd = array();
			if (isset($body['title'])) {
				$title = trim($body['title']);
				if ($title === '') $this->_json(array('success' => false, 'message' => 'title required'), 422);
				$upd['title'] = $title;
			}
			if (array_key_exists('notes', $body)) $upd['notes'] = $body['notes'];
			if (isset($body['status'])) $upd['status'] = $body['status'];
			if (isset($body['start_date'])) $upd['start_date'] = $body['start_date'];
			if (isset($body['total_amount'])) {
				$new_total = (float)$body['total_amount'];
				if ($new_total <= 0) $this->_json(array('success' => false, 'message' => 'total_amount must be > 0'), 422);
				$upd['total_amount'] = $new_total;
			}
			if (isset($body['project_id'])) {
				$pid = (int)$body['project_id'];
				if (!$this->_project($pid)) $this->_json(array('success' => false, 'message' => 'Project not found'), 404);
				$upd['project_id'] = $pid;
			}

			$existing = $this->db->get_where('construction_contract_installments', array('contract_id' => $id))->result_array();
			$by_id = array();
			foreach ($existing as $e) $by_id[(int)$e['id']] = $e;

			$to_delete = (isset($body['installment_delete']) && is_array($body['installment_delete']))
				? array_map('intval', $body['installment_delete'])
				: array();
			$to_update = (isset($body['installment_updates']) && is_array($body['installment_updates']))
				? $body['installment_updates']
				: array();
			$to_add = (isset($body['installment_add']) && is_array($body['installment_add']))
				? $body['installment_add']
				: array();

			// Simulate final installment amounts for validation
			$sim = array();
			foreach ($existing as $e) {
				$eid = (int)$e['id'];
				if (in_array($eid, $to_delete, true)) {
					if ((int)$e['paid'] === 1) {
						$this->_json(array('success' => false, 'message' => 'Cannot delete paid installment'), 422);
					}
					continue;
				}
				$sim[$eid] = array(
					'amount' => (float)$e['amount'],
					'paid' => (int)$e['paid'],
					'due_date' => $e['due_date'],
					'label' => $e['label'],
				);
			}
			foreach ($to_update as $u) {
				$iid = isset($u['id']) ? (int)$u['id'] : 0;
				if (!isset($sim[$iid])) {
					$this->_json(array('success' => false, 'message' => 'Installment not found: ' . $iid), 404);
				}
				$is_paid = $sim[$iid]['paid'] === 1;
				// Paid: only label may change. Unpaid: amount/date/label.
				if ($is_paid) {
					$touching_money = isset($u['amount']) || !empty($u['due_date']);
					if ($touching_money) {
						$this->_json(array('success' => false, 'message' => 'Cannot change amount/date of paid installment — only label'), 422);
					}
					if (array_key_exists('label', $u)) {
						$sim[$iid]['label'] = trim((string)$u['label']);
					}
					continue;
				}
				if (isset($u['amount'])) {
					$amt = (float)$u['amount'];
					if ($amt <= 0) $this->_json(array('success' => false, 'message' => 'Installment amount must be > 0'), 422);
					$sim[$iid]['amount'] = $amt;
				}
				if (!empty($u['due_date'])) $sim[$iid]['due_date'] = $u['due_date'];
				if (array_key_exists('label', $u)) $sim[$iid]['label'] = trim((string)$u['label']);
			}
			$new_rows = array();
			foreach ($to_add as $a) {
				$amt = isset($a['amount']) ? (float)$a['amount'] : 0;
				$due = !empty($a['due_date']) ? $a['due_date'] : '';
				if ($amt <= 0 || $due === '') {
					$this->_json(array('success' => false, 'message' => 'New installment needs due_date and amount > 0'), 422);
				}
				$new_rows[] = array(
					'amount' => $amt,
					'due_date' => $due,
					'label' => !empty($a['label']) ? trim($a['label']) : '',
				);
			}

			$inst_sum = 0.0;
			foreach ($sim as $s) $inst_sum += $s['amount'];
			foreach ($new_rows as $nr) $inst_sum += $nr['amount'];
			$final_total = isset($upd['total_amount']) ? (float)$upd['total_amount'] : (float)$row['total_amount'];
			if ($inst_sum > $final_total + 0.009) {
				$this->_json(array(
					'success' => false,
					'message' => 'Installments total (' . number_format($inst_sum, 2) . ') cannot exceed contract total (' . number_format($final_total, 2) . ')',
				), 422);
			}

			// Apply deletes
			foreach ($to_delete as $del_id) {
				if (!isset($by_id[$del_id])) continue;
				$this->db->where('id', $del_id)->delete('construction_contract_installments');
			}
			// Apply updates
			foreach ($to_update as $u) {
				$iid = (int)$u['id'];
				if (!isset($sim[$iid])) continue;
				$iu = array(
					'amount' => $sim[$iid]['amount'],
					'due_date' => $sim[$iid]['due_date'],
					'label' => $sim[$iid]['label'],
				);
				$this->db->where('id', $iid)->update('construction_contract_installments', $iu);
			}
			// Apply adds
			$max_row = $this->db->select_max('installment_no')
				->where('contract_id', $id)
				->get('construction_contract_installments')
				->row_array();
			$max_no = $max_row && $max_row['installment_no'] !== null ? (int)$max_row['installment_no'] : 0;
			$project_id_for_new = isset($upd['project_id']) ? (int)$upd['project_id'] : (int)$row['project_id'];
			foreach ($new_rows as $nr) {
				$max_no++;
				$this->db->insert('construction_contract_installments', array(
					'contract_id' => $id,
					'contractor_id' => (int)$row['contractor_id'],
					'project_id' => $project_id_for_new,
					'installment_no' => $max_no,
					'due_date' => $nr['due_date'],
					'amount' => $nr['amount'],
					'paid' => 0,
					'paid_amount' => 0,
					'label' => $nr['label'] !== '' ? $nr['label'] : ('Installment ' . $max_no),
					'created_at' => date('Y-m-d H:i:s'),
				));
			}

			if (isset($upd['project_id'])) {
				$this->db->where(array('contract_id' => $id, 'paid' => 0))
					->update('construction_contract_installments', array('project_id' => (int)$upd['project_id']));
			}

			if (count($upd)) $this->db->where('id', $id)->update('construction_contracts', $upd);

			$sum = $this->db->query(
				'SELECT COALESCE(SUM(total_amount),0) AS t FROM construction_contracts WHERE contractor_id = ? AND status != ?',
				array((int)$row['contractor_id'], 'cancelled')
			)->row_array();
			$this->db->where('id', (int)$row['contractor_id'])->update('construction_contractors', array(
				'contract_amount' => (float)$sum['t'],
			));

			$fresh = $this->db->get_where('construction_contracts', array('id' => $id))->row_array();
			$this->_json(array(
				'success' => true,
				'message' => 'Updated',
				'data' => $this->_enrich_contract($fresh),
			));
		}

		$this->_json(array('success' => true, 'data' => $this->_enrich_contract($row)));
	}

	/** Unpaid installments for a contractor (for expense picker) */
	public function unpaid_installments($contractor_id = 0)
	{
		$contractor_id = (int)$contractor_id;
		$project_id = (int)$this->input->get('project_id');
		$this->db->select(
			'construction_contract_installments.*, construction_contracts.title as contract_title, construction_projects.project_name',
			false
		);
		$this->db->from('construction_contract_installments');
		$this->db->join('construction_contracts', 'construction_contracts.id = construction_contract_installments.contract_id', 'left');
		$this->db->join('construction_projects', 'construction_projects.id = construction_contract_installments.project_id', 'left');
		$this->db->where('construction_contract_installments.contractor_id', $contractor_id);
		$this->db->where('construction_contract_installments.paid', 0);
		if ($project_id > 0) {
			$this->db->where('construction_contract_installments.project_id', $project_id);
		}
		$this->db->order_by('construction_contract_installments.due_date', 'ASC');
		$this->db->order_by('construction_contract_installments.installment_no', 'ASC');
		$rows = $this->db->get()->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	public function labours()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			$project_id = (int)$this->input->get('project_id');
			$this->db->select('construction_labours.*, construction_projects.project_name', false);
			$this->db->from('construction_labours');
			$this->db->join('construction_projects', 'construction_projects.id = construction_labours.project_id', 'left');
			if ($project_id > 0) {
				$this->db->where('construction_labours.project_id', $project_id);
			}
			$this->db->order_by('construction_labours.id', 'DESC');
			$rows = $this->db->get()->result_array();
			foreach ($rows as &$l) {
				$l = $this->_enrich_labour($l);
			}
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

	public function labour($id = 0)
	{
		$id = (int)$id;
		$row = $this->db->get_where('construction_labours', array('id' => $id))->row_array();
		if (!$row) $this->_json(array('success' => false, 'message' => 'Labour not found'), 404);

		$method = $_SERVER['REQUEST_METHOD'];
		if ($method === 'PUT' || $method === 'POST') {
			$body = $this->_body();
			$update = array();
			if (isset($body['labour_name'])) {
				$name = trim($body['labour_name']);
				if ($name === '') $this->_json(array('success' => false, 'message' => 'labour_name required'), 422);
				$update['labour_name'] = $name;
			}
			if (array_key_exists('cnic', $body)) $update['cnic'] = $body['cnic'];
			if (array_key_exists('mobile', $body)) $update['mobile'] = $body['mobile'];
			if (array_key_exists('designation', $body)) $update['designation'] = $body['designation'];
			if (isset($body['daily_wage'])) $update['daily_wage'] = (float)$body['daily_wage'];
			if (isset($body['status'])) $update['status'] = (int)$body['status'] ? 1 : 0;
			if (isset($body['project_id'])) {
				$pid = (int)$body['project_id'];
				if (!$this->_project($pid)) $this->_json(array('success' => false, 'message' => 'Project not found'), 404);
				$update['project_id'] = $pid;
			}
			if (!count($update)) $this->_json(array('success' => false, 'message' => 'Nothing to update'), 422);
			$this->db->where('id', $id)->update('construction_labours', $update);
			$this->_json(array('success' => true, 'message' => 'Updated'));
		}

		if ($method === 'DELETE') {
			$pay_count = (int)$this->db->where('labour_id', $id)->count_all_results('construction_labour_advances');
			if ($pay_count > 0) {
				$this->_json(array('success' => false, 'message' => 'Cannot delete: labour has payments. Remove payments first.'), 422);
			}
			$this->db->where('id', $id)->delete('construction_labours');
			$this->_json(array('success' => true, 'message' => 'Deleted'));
		}

		$project = $this->_project((int)$row['project_id']);
		$row = $this->_enrich_labour($row);
		$row['project_name'] = $project ? $project['project_name'] : '';

		$payments = $this->db->order_by('advance_date', 'DESC')
			->order_by('id', 'DESC')
			->get_where('construction_labour_advances', array('labour_id' => $id))
			->result_array();

		$this->_json(array(
			'success' => true,
			'data' => array(
				'labour' => $row,
				'payments' => $payments,
			),
		));
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

		$this->db->select('expenses.*, construction_contractors.contractor_name, construction_labours.labour_name, construction_projects.project_name', false);
		$this->db->from('expenses');
		$this->db->join('construction_contractors', 'construction_contractors.id = expenses.construction_contractor_id', 'left');
		$this->db->join('construction_labours', 'construction_labours.id = expenses.construction_labour_id', 'left');
		$this->db->join('construction_projects', 'construction_projects.id = expenses.construction_project_id', 'left');
		$this->db->where('expenses.construction_project_id', $project_id);
		$this->db->where('expenses.date', $date);
		$this->db->order_by('expenses.expense_id', 'DESC');
		$rows = $this->db->get()->result_array();

		$total = 0;
		foreach ($rows as &$r) {
			$total += (float)$r['amount'];
			$this->_decorate_expense_row($r);
		}

		$this->_json(array('success' => true, 'data' => $rows, 'day_total' => $total, 'date' => $date));
	}

	/** Global construction expenses list (all projects or filtered). */
	public function expenses()
	{
		if (!$this->db->table_exists('expenses') || !$this->db->field_exists('construction_project_id', 'expenses')) {
			$this->_json(array('success' => true, 'data' => array(), 'total' => 0));
		}

		$project_id = (int)$this->input->get('project_id');
		$source = trim((string)$this->input->get('source'));
		$date_from = trim((string)$this->input->get('date_from'));
		$date_to = trim((string)$this->input->get('date_to'));
		$date = trim((string)$this->input->get('date'));

		// Build a shared date WHERE + bindings for either exact day or range.
		$date_where = '';
		$date_bind = array();
		if ($date !== '') {
			$date_where = 'e.date = ?';
			$date_bind[] = $date;
		} else {
			$parts = array();
			if ($date_from !== '') {
				$parts[] = 'e.date >= ?';
				$date_bind[] = $date_from;
			}
			if ($date_to !== '') {
				$parts[] = 'e.date <= ?';
				$date_bind[] = $date_to;
			}
			$date_where = count($parts) ? implode(' AND ', $parts) : '1=1';
		}

		$pr_join = $this->_purchase_project_join_sql();
		$rows = array();

		if ($pr_join) {
			// Mirror _day_expense_rows: catch purchase installment expenses linked via
			// purchase_no → purchase_requests.project_id, and enrich with vendor name.
			$sql = "SELECT e.*,
					cc.contractor_name, cl.labour_name,
					COALESCE(cp.project_name, cp2.project_name) AS project_name,
					COALESCE(e.construction_project_id, pr.project_id) AS linked_project_id,
					CASE
						WHEN e.construction_source IN ('contractor','labour','misc','purchase') THEN e.construction_source
						WHEN pr.project_id IS NOT NULL AND pr.project_id > 0 THEN 'purchase'
						ELSE COALESCE(NULLIF(e.construction_source,''), 'misc')
					END AS construction_source_resolved,
					(SELECT v.name FROM payment_aggrements pa
					 LEFT JOIN vendors v ON v.id = pa.vendor_id
					 WHERE pa.purchase_no = e.purchase_no
					 ORDER BY pa.paid DESC, pa.date DESC LIMIT 1) AS vendor_name
				FROM expenses e
				LEFT JOIN construction_contractors cc ON cc.id = e.construction_contractor_id
				LEFT JOIN construction_labours cl ON cl.id = e.construction_labour_id
				LEFT JOIN construction_projects cp ON cp.id = e.construction_project_id
				LEFT JOIN {$pr_join} pr ON pr.purchase_no = e.purchase_no
				LEFT JOIN construction_projects cp2 ON cp2.id = pr.project_id
				WHERE {$date_where}
				  AND (
					(e.construction_project_id IS NOT NULL AND e.construction_project_id > 0)
					OR (pr.project_id IS NOT NULL AND pr.project_id > 0)
				  )";
			$bind = $date_bind;
			if ($project_id > 0) {
				$sql .= " AND COALESCE(e.construction_project_id, pr.project_id) = ?";
				$bind[] = $project_id;
			}
			$sql .= " ORDER BY e.date DESC, e.expense_id DESC";
			$rows = $this->db->query($sql, $bind)->result_array();
		} else {
			$this->db->select('expenses.*, construction_contractors.contractor_name, construction_labours.labour_name, construction_projects.project_name', false);
			$this->db->from('expenses');
			$this->db->join('construction_contractors', 'construction_contractors.id = expenses.construction_contractor_id', 'left');
			$this->db->join('construction_labours', 'construction_labours.id = expenses.construction_labour_id', 'left');
			$this->db->join('construction_projects', 'construction_projects.id = expenses.construction_project_id', 'left');
			$this->db->where('expenses.construction_project_id IS NOT NULL', null, false);
			$this->db->where('expenses.construction_project_id >', 0);
			if ($project_id > 0) $this->db->where('expenses.construction_project_id', $project_id);
			if ($date !== '') {
				$this->db->where('expenses.date', $date);
			} else {
				if ($date_from !== '') $this->db->where('expenses.date >=', $date_from);
				if ($date_to !== '') $this->db->where('expenses.date <=', $date_to);
			}
			$this->db->order_by('expenses.date', 'DESC');
			$this->db->order_by('expenses.expense_id', 'DESC');
			$rows = $this->db->get()->result_array();
		}

		$total = 0;
		$by_source = array('contractor' => 0, 'labour' => 0, 'misc' => 0, 'purchase' => 0);
		$filtered = array();
		foreach ($rows as &$r) {
			$this->_decorate_expense_row($r);
			$src = isset($r['construction_source']) ? $r['construction_source'] : 'misc';
			if (!isset($by_source[$src])) $src = 'misc';
			// Optional source filter (labour|contractor|misc|purchase)
			if ($source !== '' && in_array($source, array('labour', 'contractor', 'misc', 'purchase'), true)
				&& $src !== $source) {
				continue;
			}
			$amt = (float)$r['amount'];
			$total += $amt;
			$by_source[$src] += $amt;
			$filtered[] = $r;
		}
		unset($r);

		$this->_json(array(
			'success' => true,
			'data' => $filtered,
			'total' => $total,
			'contractor_total' => $by_source['contractor'],
			'labour_total' => $by_source['labour'],
			'misc_total' => $by_source['misc'],
			'purchase_total' => $by_source['purchase'],
			'date' => $date !== '' ? $date : null,
		));
	}

	public function expense($id = 0)
	{
		$id = (int)$id;
		if (!$this->db->table_exists('expenses')) {
			$this->_json(array('success' => false, 'message' => 'expenses table missing'), 500);
		}
		$row = $this->db->get_where('expenses', array('expense_id' => $id))->row_array();
		if (!$row || empty($row['construction_project_id'])) {
			$this->_json(array('success' => false, 'message' => 'Expense not found'), 404);
		}

		$method = $_SERVER['REQUEST_METHOD'];
		if ($method === 'PUT' || $method === 'POST') {
			$this->_assert_manage();
			$this->_assert_expense_day_open($row['date']);
			$body = $this->_body();
			$amount = isset($body['amount']) ? (float)$body['amount'] : (float)$row['amount'];
			$date = !empty($body['date']) ? $body['date'] : $row['date'];
			$description = array_key_exists('description', $body) ? trim($body['description']) : null;
			if ($amount <= 0) $this->_json(array('success' => false, 'message' => 'amount required'), 422);
			if ($date !== $row['date']) {
				$this->_assert_expense_day_open($date);
			}

			$project = $this->_project((int)$row['construction_project_id']);
			$src = isset($row['construction_source']) ? $row['construction_source'] : 'misc';
			$ref_id = (int)(isset($row['construction_ref_id']) ? $row['construction_ref_id'] : 0);
			$name = $this->_user_name();

			$purpose = isset($row['purpose']) ? $row['purpose'] : '';
			if ($description !== null) {
				$party = '';
				if ($src === 'contractor') {
					$c = $this->db->get_where('construction_contractors', array('id' => (int)$row['construction_contractor_id']))->row_array();
					$party = $c ? $c['contractor_name'] : 'Contractor';
					$purpose = 'Construction ' . ($project ? $project['project_name'] : '') . ' · Contractor · ' . $party
						. ($description !== '' ? ' · ' . $description : '');
				} elseif ($src === 'labour') {
					$l = $this->db->get_where('construction_labours', array('id' => (int)$row['construction_labour_id']))->row_array();
					$party = $l ? $l['labour_name'] : 'Labour';
					$purpose = 'Construction ' . ($project ? $project['project_name'] : '') . ' · Labour · ' . $party
						. ($description !== '' ? ' · ' . $description : '');
				} else {
					$purpose = 'Construction ' . ($project ? $project['project_name'] : '') . ' · Misc'
						. ($description !== '' ? ' · ' . $description : '');
				}
			}

			$exp_update = array(
				'amount' => $amount,
				'date' => $date,
				'purpose' => $purpose,
				'last_edit' => $name,
			);
			$this->db->where('expense_id', $id)->update('expenses', $exp_update);

			if ($src === 'contractor' && $ref_id > 0) {
				$pay_upd = array('amount' => $amount, 'payment_date' => $date);
				if ($description !== null) $pay_upd['remarks'] = $description;
				if (!empty($body['payment_type'])) $pay_upd['payment_type'] = trim($body['payment_type']);
				$this->db->where('id', $ref_id)->update('construction_contractor_payments', $pay_upd);
			} elseif ($src === 'labour' && $ref_id > 0) {
				$pay_upd = array('amount' => $amount, 'advance_date' => $date);
				if ($description !== null) $pay_upd['remarks'] = $description !== '' ? $description : 'Labour payment';
				$this->db->where('id', $ref_id)->update('construction_labour_advances', $pay_upd);
			} elseif ($src === 'misc' && $ref_id > 0 && $this->db->table_exists('construction_site_expenses')) {
				$site_upd = array('amount' => $amount, 'expense_date' => $date);
				if ($description !== null) $site_upd['description'] = $description;
				$this->db->where('id', $ref_id)->update('construction_site_expenses', $site_upd);
			}

			$this->_json(array('success' => true, 'message' => 'Updated'));
		}

		if ($method === 'DELETE') {
			$this->_assert_manage();
			$this->_assert_expense_day_open($row['date']);
			$src = isset($row['construction_source']) ? $row['construction_source'] : 'misc';
			$ref_id = (int)(isset($row['construction_ref_id']) ? $row['construction_ref_id'] : 0);
			if ($src === 'contractor' && $ref_id > 0) {
				$this->db->where('id', $ref_id)->delete('construction_contractor_payments');
			} elseif ($src === 'labour' && $ref_id > 0) {
				$this->db->where('id', $ref_id)->delete('construction_labour_advances');
			} elseif ($src === 'misc' && $ref_id > 0 && $this->db->table_exists('construction_site_expenses')) {
				$this->db->where('id', $ref_id)->delete('construction_site_expenses');
			}
			$this->db->where('expense_id', $id)->delete('expenses');
			$this->_json(array('success' => true, 'message' => 'Deleted'));
		}

		$this->db->select('expenses.*, construction_contractors.contractor_name, construction_labours.labour_name, construction_projects.project_name', false);
		$this->db->from('expenses');
		$this->db->join('construction_contractors', 'construction_contractors.id = expenses.construction_contractor_id', 'left');
		$this->db->join('construction_labours', 'construction_labours.id = expenses.construction_labour_id', 'left');
		$this->db->join('construction_projects', 'construction_projects.id = expenses.construction_project_id', 'left');
		$this->db->where('expenses.expense_id', $id);
		$full = $this->db->get()->row_array();
		$this->_decorate_expense_row($full);
		$this->_json(array('success' => true, 'data' => $full));
	}

	/** POST multipart expense_image/{expense_id} — field: image (optional attachment) */
	public function expense_image($id = 0)
	{
		$id = (int)$id;
		$this->_assert_manage();
		$row = $this->db->get_where('expenses', array('expense_id' => $id))->row_array();
		if (!$row || empty($row['construction_project_id'])) {
			$this->_json(array('success' => false, 'message' => 'Expense not found'), 404);
		}
		$this->_assert_expense_day_open($row['date']);
		$file = $this->_upload_expense_image('image');
		if ($file === '') $this->_json(array('success' => false, 'message' => 'Image upload failed'), 422);
		$this->db->where('expense_id', $id)->update('expenses', array('image' => $file));
		$this->_json(array(
			'success' => true,
			'image' => $file,
			'image_url' => $this->_expense_image_url($file),
		));
	}

	public function add_daily_expense()
	{
		$this->_assert_manage();
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
		$this->_assert_expense_day_open($date);
		$project = $this->_project($project_id);
		if (!$project) $this->_json(array('success' => false, 'message' => 'Project not found'), 404);

		$campus_id = !empty($project['campus_id'])
			? (int)$project['campus_id']
			: (int)$this->current_user['campus_id'];
		if (!$campus_id) $this->_json(array('success' => false, 'message' => 'campus_id required on project or user'), 422);

		$ref_id = 0;
		$contractor_id = null;
		$labour_id = null;
		$installment_id_paid = null;
		$contract_id_paid = null;
		$title = '';
		$purpose = '';

		if ($type === 'contractor') {
			$contractor_id = (int)(isset($body['contractor_id']) ? $body['contractor_id'] : 0);
			$c = $this->db->get_where('construction_contractors', array('id' => $contractor_id))->row_array();
			if (!$c) $this->_json(array('success' => false, 'message' => 'contractor_id invalid'), 422);

			$installment_id = isset($body['installment_id']) ? (int)$body['installment_id'] : 0;
			$installment = null;
			$contract_id = null;
			if ($installment_id > 0) {
				$installment = $this->db->get_where('construction_contract_installments', array(
					'id' => $installment_id,
					'contractor_id' => $contractor_id,
					'paid' => 0,
				))->row_array();
				if (!$installment) {
					$this->_json(array('success' => false, 'message' => 'installment_id invalid or already paid'), 422);
				}
				if ((int)$installment['project_id'] !== $project_id) {
					$this->_json(array('success' => false, 'message' => 'installment does not belong to this project'), 422);
				}
				$contract_id = (int)$installment['contract_id'];
				if ($amount <= 0) $amount = (float)$installment['amount'];
				$installment_id_paid = $installment_id;
				$contract_id_paid = $contract_id;
			}

			$payment_type = isset($body['payment_type']) ? trim($body['payment_type']) : 'Running Bill';
			if ($installment) {
				$payment_type = !empty($installment['label'])
					? $installment['label']
					: ('Installment #' . $installment['installment_no']);
			}
			$this->db->insert('construction_contractor_payments', array(
				'contractor_id' => $contractor_id,
				'project_id' => $project_id,
				'payment_date' => $date,
				'amount' => $amount,
				'payment_type' => $payment_type,
				'remarks' => $description,
				'installment_id' => $installment_id ?: null,
				'contract_id' => $contract_id,
				'created_at' => date('Y-m-d H:i:s'),
			));
			$ref_id = (int)$this->db->insert_id();

			if ($installment) {
				$this->db->where('id', $installment_id)->update('construction_contract_installments', array(
					'paid' => 1,
					'paid_date' => $date,
					'paid_amount' => $amount,
					'payment_id' => $ref_id,
				));
			}

			$title = 'Construction · Contractor payment';
			$purpose = 'Construction ' . $project['project_name'] . ' · Contractor · ' . $c['contractor_name'];
			if ($installment) {
				$purpose .= ' · ' . ($installment['label'] ?: ('Installment #' . $installment['installment_no']));
			}
			$purpose .= ($description !== '' ? ' · ' . $description : '');
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
			'installment_id' => $installment_id_paid,
			'contract_id' => $contract_id_paid,
		));

		if ($installment_id_paid) {
			$this->db->where('id', $installment_id_paid)->update('construction_contract_installments', array(
				'expense_id' => $expense_id,
			));
		}

		$this->_json(array(
			'success' => true,
			'expense_id' => $expense_id,
			'construction_ref_id' => $ref_id,
			'message' => 'Expense added',
		));
	}

	/**
	 * Construction ERP dashboard — one-shot rollups for owner / PM.
	 * GET dashboard
	 */
	public function dashboard()
	{
		$today = date('Y-m-d');
		$week_start = date('Y-m-d', strtotime('monday this week'));
		$month_start = date('Y-m-01');
		$year_start = date('Y-01-01');

		$projects = array();
		if ($this->db->table_exists('construction_projects')) {
			$projects = $this->db->order_by('id', 'DESC')->get('construction_projects')->result_array();
		}

		$total_budget = 0;
		$total_expenses = 0;
		$contractor_paid_all = 0;
		$contractor_remaining_all = 0;
		$contracts_value_all = 0;
		$active_projects = 0;
		$completed_projects = 0;
		$project_rows = array();
		$over_budget_names = array();

		foreach ($projects as $p) {
			$pid = (int)$p['id'];
			$budget = (float)$p['budget'];
			$sum = $this->_project_summary($pid);
			$expense = (float)$sum['expense_total'];
			$remaining = $budget - $expense;
			$util = $budget > 0 ? round(($expense / $budget) * 100, 1) : 0;
			$status = trim((string)$p['status']);
			$status_l = strtolower($status);
			$is_completed = in_array($status_l, array('completed', 'complete', 'done', 'finished'), true);
			$is_inactive = in_array($status_l, array('inactive', 'paused', 'on hold', 'onhold', 'hold'), true);
			$is_cancelled = in_array($status_l, array('cancelled', 'canceled'), true);
			if ($is_completed) $completed_projects++;
			elseif (!$is_cancelled && !$is_inactive) $active_projects++;
			// Normalize display status for dashboard rows
			if ($is_completed) $status = 'Completed';
			elseif ($is_inactive) $status = 'Inactive';
			else $status = 'Active';

			$health = 'on_track';
			if ($budget > 0 && $expense > $budget + 0.009) {
				$health = 'over_budget';
				$over_budget_names[] = $p['project_name'];
			} elseif (
				!$is_completed && !$is_cancelled
				&& !empty($p['expected_completion_date'])
				&& $p['expected_completion_date'] < $today
			) {
				$health = 'delayed';
			}

			$total_budget += $budget;
			$total_expenses += $expense;
			$contractor_paid_all += (float)$sum['contractor_paid'];
			$contractor_remaining_all += (float)$sum['contractor_remaining'];
			$contracts_value_all += (float)$sum['contractor_done'];

			$project_rows[] = array(
				'id' => $pid,
				'project_name' => $p['project_name'],
				'location' => isset($p['location']) ? $p['location'] : '',
				'client' => isset($p['client']) ? $p['client'] : '',
				'status' => $status,
				'budget' => $budget,
				'expense_total' => $expense,
				'remaining' => $remaining,
				'utilization_pct' => $util,
				'progress_percent' => isset($p['progress_percent']) ? (float)$p['progress_percent'] : 0,
				'contractor_paid' => (float)$sum['contractor_paid'],
				'contractor_remaining' => (float)$sum['contractor_remaining'],
				'expected_completion_date' => isset($p['expected_completion_date']) ? $p['expected_completion_date'] : null,
				'health' => $health,
			);
		}

		$total_contractors = 0;
		if ($this->db->table_exists('construction_contractors')) {
			$total_contractors = (int)$this->db->count_all('construction_contractors');
		}
		$total_labours = 0;
		$active_labours = 0;
		$labour_wages_paid = 0;
		if ($this->db->table_exists('construction_labours')) {
			$total_labours = (int)$this->db->count_all('construction_labours');
			$active_labours = (int)$this->db->where('status', 1)->count_all_results('construction_labours');
			if ($this->db->table_exists('expenses') && $this->db->field_exists('construction_source', 'expenses')) {
				$lw = $this->db->query(
					"SELECT COALESCE(SUM(amount),0) AS t FROM expenses
					 WHERE construction_project_id > 0 AND construction_source = 'labour'"
				)->row_array();
				$labour_wages_paid = (float)$lw['t'];
			}
		}

		$unpaid_installments = 0;
		if ($this->db->table_exists('construction_contract_installments')) {
			$ui = $this->db->query(
				'SELECT COALESCE(SUM(amount),0) AS t FROM construction_contract_installments WHERE paid = 0'
			)->row_array();
			$unpaid_installments = (float)$ui['t'];
		}

		$expense_periods = array('today' => 0, 'week' => 0, 'month' => 0, 'year' => 0);
		$expense_by_source = array('labour' => 0, 'contractor' => 0, 'misc' => 0, 'purchase' => 0);
		$monthly_expenses = array();
		$recent_expenses = array();

		if ($this->db->table_exists('expenses') && $this->db->field_exists('construction_project_id', 'expenses')) {
			$pr_join = $this->_purchase_project_join_sql();
			$period_sums = array(
				'today' => array($today, $today),
				'week' => array($week_start, $today),
				'month' => array($month_start, $today),
				'year' => array($year_start, $today),
			);

			if ($pr_join) {
				$resolved = "CASE
						WHEN e.construction_source IN ('contractor','labour','misc','purchase') THEN e.construction_source
						WHEN pr.project_id IS NOT NULL AND pr.project_id > 0 THEN 'purchase'
						ELSE COALESCE(NULLIF(e.construction_source,''), 'misc')
					END";
				$scope = "(
					(e.construction_project_id IS NOT NULL AND e.construction_project_id > 0)
					OR (pr.project_id IS NOT NULL AND pr.project_id > 0)
				)";

				foreach ($period_sums as $key => $range) {
					$row = $this->db->query(
						"SELECT COALESCE(SUM(e.amount),0) AS t
						 FROM expenses e
						 LEFT JOIN {$pr_join} pr ON pr.purchase_no = e.purchase_no
						 WHERE {$scope}
						   AND e.date >= ? AND e.date <= ?",
						$range
					)->row_array();
					$expense_periods[$key] = (float)$row['t'];
				}

				$src_rows = $this->db->query(
					"SELECT src, COALESCE(SUM(amount),0) AS t FROM (
						SELECT e.amount, {$resolved} AS src
						FROM expenses e
						LEFT JOIN {$pr_join} pr ON pr.purchase_no = e.purchase_no
						WHERE {$scope}
					) x GROUP BY src"
				)->result_array();

				// Last 12 calendar months (fill zeros)
				$month_map = array();
				for ($i = 11; $i >= 0; $i--) {
					$key = date('Y-m', strtotime(date('Y-m-01') . " -{$i} months"));
					$month_map[$key] = 0;
				}
				$from12 = date('Y-m-01', strtotime(date('Y-m-01') . ' -11 months'));
				$mrows = $this->db->query(
					"SELECT DATE_FORMAT(e.date, '%Y-%m') AS ym, COALESCE(SUM(e.amount),0) AS t
					 FROM expenses e
					 LEFT JOIN {$pr_join} pr ON pr.purchase_no = e.purchase_no
					 WHERE {$scope}
					   AND e.date >= ?
					 GROUP BY ym
					 ORDER BY ym ASC",
					array($from12)
				)->result_array();

				$recent_sql = "SELECT e.*,
						cc.contractor_name, cl.labour_name,
						COALESCE(cp.project_name, cp2.project_name) AS project_name,
						COALESCE(e.construction_project_id, pr.project_id) AS linked_project_id,
						{$resolved} AS construction_source_resolved,
						(SELECT v.name FROM payment_aggrements pa
						 LEFT JOIN vendors v ON v.id = pa.vendor_id
						 WHERE pa.purchase_no = e.purchase_no
						 ORDER BY pa.paid DESC, pa.date DESC LIMIT 1) AS vendor_name
					FROM expenses e
					LEFT JOIN construction_contractors cc ON cc.id = e.construction_contractor_id
					LEFT JOIN construction_labours cl ON cl.id = e.construction_labour_id
					LEFT JOIN construction_projects cp ON cp.id = e.construction_project_id
					LEFT JOIN {$pr_join} pr ON pr.purchase_no = e.purchase_no
					LEFT JOIN construction_projects cp2 ON cp2.id = pr.project_id
					WHERE {$scope}
					ORDER BY e.date DESC, e.expense_id DESC
					LIMIT 10";
				$recent_expenses = $this->db->query($recent_sql)->result_array();
			} else {
				foreach ($period_sums as $key => $range) {
					$row = $this->db->query(
						"SELECT COALESCE(SUM(amount),0) AS t FROM expenses
						 WHERE construction_project_id IS NOT NULL AND construction_project_id > 0
						   AND date >= ? AND date <= ?",
						$range
					)->row_array();
					$expense_periods[$key] = (float)$row['t'];
				}

				$src_rows = $this->db->query(
					"SELECT COALESCE(NULLIF(construction_source,''), 'misc') AS src, COALESCE(SUM(amount),0) AS t
					 FROM expenses
					 WHERE construction_project_id IS NOT NULL AND construction_project_id > 0
					 GROUP BY src"
				)->result_array();

				$month_map = array();
				for ($i = 11; $i >= 0; $i--) {
					$key = date('Y-m', strtotime(date('Y-m-01') . " -{$i} months"));
					$month_map[$key] = 0;
				}
				$from12 = date('Y-m-01', strtotime(date('Y-m-01') . ' -11 months'));
				$mrows = $this->db->query(
					"SELECT DATE_FORMAT(date, '%Y-%m') AS ym, COALESCE(SUM(amount),0) AS t
					 FROM expenses
					 WHERE construction_project_id IS NOT NULL AND construction_project_id > 0
					   AND date >= ?
					 GROUP BY ym
					 ORDER BY ym ASC",
					array($from12)
				)->result_array();

				$this->db->select(
					'expenses.*, construction_contractors.contractor_name, construction_labours.labour_name, construction_projects.project_name',
					false
				);
				$this->db->from('expenses');
				$this->db->join('construction_contractors', 'construction_contractors.id = expenses.construction_contractor_id', 'left');
				$this->db->join('construction_labours', 'construction_labours.id = expenses.construction_labour_id', 'left');
				$this->db->join('construction_projects', 'construction_projects.id = expenses.construction_project_id', 'left');
				$this->db->where('expenses.construction_project_id IS NOT NULL', null, false);
				$this->db->where('expenses.construction_project_id >', 0);
				$this->db->order_by('expenses.date', 'DESC');
				$this->db->order_by('expenses.expense_id', 'DESC');
				$this->db->limit(10);
				$recent_expenses = $this->db->get()->result_array();
			}

			foreach ($src_rows as $sr) {
				$src = $sr['src'];
				if (!isset($expense_by_source[$src])) $src = 'misc';
				$expense_by_source[$src] += (float)$sr['t'];
			}
			foreach ($mrows as $mr) {
				if (isset($month_map[$mr['ym']])) $month_map[$mr['ym']] = (float)$mr['t'];
			}
			foreach ($month_map as $ym => $t) {
				$monthly_expenses[] = array('month' => $ym, 'total' => $t);
			}
			foreach ($recent_expenses as &$re) {
				$this->_decorate_expense_row($re);
			}
			unset($re);
		}

		$contractors_top = array();
		if ($this->db->table_exists('construction_contractors')) {
			$top = $this->db->query(
				"SELECT c.id, c.contractor_name, c.image,
					COALESCE(SUM(p.amount),0) AS paid_amount
				 FROM construction_contractors c
				 LEFT JOIN construction_contractor_payments p ON p.contractor_id = c.id
				 GROUP BY c.id
				 ORDER BY paid_amount DESC
				 LIMIT 5"
			)->result_array();
			foreach ($top as $t) {
				$contractors_top[] = array(
					'id' => (int)$t['id'],
					'contractor_name' => $t['contractor_name'],
					'paid_amount' => (float)$t['paid_amount'],
					'image_url' => $this->_contractor_image_url(isset($t['image']) ? $t['image'] : ''),
				);
			}
		}

		$last_verified = $this->_last_verified_expense_date();
		$next_pending = $this->_next_pending_expense_date();

		$purchase_pipeline = array('count' => 0, 'amount' => 0);
		if ($this->db->table_exists('purchase_requests')
			&& $this->db->field_exists('project_id', 'purchase_requests')) {
			$pr = $this->db->query(
				"SELECT COUNT(DISTINCT purchase_no) AS n,
					COALESCE(SUM(product_quantity * IFNULL(purchase_price,0)),0) AS amt
				 FROM purchase_requests
				 WHERE project_id IS NOT NULL AND project_id > 0
				   AND (final = 0 OR purchased = 0 OR gate_approval = 0 OR approval = 0)
				   AND status != 2"
			)->row_array();
			if ($pr) {
				$purchase_pipeline['count'] = (int)$pr['n'];
				$purchase_pipeline['amount'] = (float)$pr['amt'];
			}
		}

		$alerts = array();
		foreach ($over_budget_names as $name) {
			$alerts[] = array(
				'level' => 'danger',
				'code' => 'over_budget',
				'message' => 'Budget exceeded: ' . $name,
			);
		}
		if ($unpaid_installments > 0.009) {
			$alerts[] = array(
				'level' => 'warning',
				'code' => 'unpaid_installments',
				'message' => 'Unpaid contractor installments: ' . number_format($unpaid_installments, 0),
			);
		}
		if ($next_pending) {
			$alerts[] = array(
				'level' => 'info',
				'code' => 'closing_pending',
				'message' => 'Expense closing pending for ' . $next_pending,
			);
		}
		if ($purchase_pipeline['count'] > 0) {
			$alerts[] = array(
				'level' => 'info',
				'code' => 'purchase_pipeline',
				'message' => $purchase_pipeline['count'] . ' project purchase(s) still in pipeline',
			);
		}

		$this->_json(array(
			'success' => true,
			'data' => array(
				'kpis' => array(
					'total_projects' => count($projects),
					'active_projects' => $active_projects,
					'completed_projects' => $completed_projects,
					'total_budget' => $total_budget,
					'total_expenses' => $total_expenses,
					'remaining_budget' => $total_budget - $total_expenses,
					'total_contractors' => $total_contractors,
					'total_labours' => $total_labours,
					'active_labours' => $active_labours,
					'labour_wages_paid' => $labour_wages_paid,
					'contractor_paid' => $contractor_paid_all,
					'contractor_remaining' => $contractor_remaining_all,
					'unpaid_installments_amount' => $unpaid_installments,
					'contracts_value' => $contracts_value_all,
				),
				'expense_periods' => $expense_periods,
				'expense_by_source' => $expense_by_source,
				'monthly_expenses' => $monthly_expenses,
				'projects' => $project_rows,
				'contractors_top' => $contractors_top,
				'recent_expenses' => $recent_expenses,
				'closing' => array(
					'last_verified_date' => $last_verified,
					'next_pending_date' => $next_pending,
				),
				'purchase_pipeline' => $purchase_pipeline,
				'alerts' => $alerts,
			),
		));
	}

	/**
	 * Dashboard KPI drill-down (all projects).
	 * GET dashboard_ledger?type=contractor_paid|contractor_remaining|unpaid_installments|contracts_value
	 */
	public function dashboard_ledger()
	{
		$type = trim((string)$this->input->get('type'));
		$allowed = array('contractor_paid', 'contractor_remaining', 'unpaid_installments', 'contracts_value');
		if (!in_array($type, $allowed, true)) {
			$this->_json(array('success' => false, 'message' => 'type required: ' . implode('|', $allowed)), 422);
		}

		$rows = array();
		$total = 0.0;
		$title = '';

		if ($type === 'contractor_paid') {
			if (!$this->db->table_exists('construction_contractor_payments')) {
				$this->_json(array('success' => true, 'data' => array(), 'total' => 0, 'type' => $type, 'title' => 'Contractor payments'));
			}
			$this->db->select(
				'construction_contractor_payments.*, construction_contractors.contractor_name,
				 construction_contracts.title as contract_title, construction_projects.project_name',
				false
			);
			$this->db->from('construction_contractor_payments');
			$this->db->join(
				'construction_contractors',
				'construction_contractors.id = construction_contractor_payments.contractor_id',
				'left'
			);
			$this->db->join(
				'construction_contracts',
				'construction_contracts.id = construction_contractor_payments.contract_id',
				'left'
			);
			$this->db->join(
				'construction_projects',
				'construction_projects.id = construction_contractor_payments.project_id',
				'left'
			);
			$this->db->order_by('construction_contractor_payments.payment_date', 'DESC');
			$this->db->order_by('construction_contractor_payments.id', 'DESC');
			$pays = $this->db->get()->result_array();
			foreach ($pays as $p) {
				$total += (float)$p['amount'];
				$rows[] = array(
					'row_kind' => 'payment',
					'id' => (int)$p['id'],
					'date' => $p['payment_date'],
					'amount' => (float)$p['amount'],
					'party_name' => $p['contractor_name'],
					'contractor_id' => (int)$p['contractor_id'],
					'project_id' => (int)$p['project_id'],
					'project_name' => isset($p['project_name']) ? $p['project_name'] : '',
					'payment_type' => $p['payment_type'],
					'purpose' => trim(
						(isset($p['project_name']) && $p['project_name'] ? $p['project_name'] . ' · ' : '')
						. ($p['contract_title'] ? $p['contract_title'] . ' · ' : '')
						. ($p['payment_type'] ?: 'payment')
						. ($p['remarks'] ? ' · ' . $p['remarks'] : '')
					),
					'construction_source' => 'contractor',
				);
			}
			$title = 'Contractor payments (paid)';
		} elseif ($type === 'unpaid_installments') {
			if (!$this->db->table_exists('construction_contract_installments')) {
				$this->_json(array('success' => true, 'data' => array(), 'total' => 0, 'type' => $type, 'title' => 'Unpaid installments'));
			}
			$this->db->select(
				'construction_contract_installments.*, construction_contracts.title as contract_title,
				 construction_contractors.contractor_name, construction_projects.project_name',
				false
			);
			$this->db->from('construction_contract_installments');
			$this->db->join(
				'construction_contracts',
				'construction_contracts.id = construction_contract_installments.contract_id',
				'left'
			);
			$this->db->join(
				'construction_contractors',
				'construction_contractors.id = construction_contract_installments.contractor_id',
				'left'
			);
			$this->db->join(
				'construction_projects',
				'construction_projects.id = construction_contract_installments.project_id',
				'left'
			);
			$this->db->where('construction_contract_installments.paid', 0);
			$this->db->order_by('construction_contract_installments.due_date', 'ASC');
			$this->db->order_by('construction_contract_installments.id', 'ASC');
			$insts = $this->db->get()->result_array();
			foreach ($insts as $i) {
				$total += (float)$i['amount'];
				$rows[] = array(
					'row_kind' => 'installment',
					'id' => (int)$i['id'],
					'date' => $i['due_date'],
					'amount' => (float)$i['amount'],
					'party_name' => $i['contractor_name'],
					'contractor_id' => (int)$i['contractor_id'],
					'project_id' => (int)$i['project_id'],
					'project_name' => isset($i['project_name']) ? $i['project_name'] : '',
					'purpose' => trim(
						(isset($i['project_name']) && $i['project_name'] ? $i['project_name'] . ' · ' : '')
						. ($i['contract_title'] ?: 'Contract')
						. ' · '
						. ($i['label'] ?: ('Inst #' . $i['installment_no']))
					),
					'construction_source' => 'contractor',
				);
			}
			$title = 'Unpaid installments';
		} elseif ($type === 'contractor_remaining') {
			// Match KPI: max(0, contract total − payments) per non-cancelled contract
			if (!$this->db->table_exists('construction_contracts')) {
				$this->_json(array('success' => true, 'data' => array(), 'total' => 0, 'type' => $type, 'title' => 'Contractor remaining'));
			}
			$this->db->select(
				'construction_contracts.*, construction_contractors.contractor_name, construction_projects.project_name',
				false
			);
			$this->db->from('construction_contracts');
			$this->db->join(
				'construction_contractors',
				'construction_contractors.id = construction_contracts.contractor_id',
				'left'
			);
			$this->db->join(
				'construction_projects',
				'construction_projects.id = construction_contracts.project_id',
				'left'
			);
			$this->db->where('construction_contracts.status !=', 'cancelled');
			$this->db->order_by('construction_contracts.id', 'DESC');
			$contracts = $this->db->get()->result_array();
			foreach ($contracts as $ct) {
				$cid = (int)$ct['id'];
				$paid_row = $this->db->query(
					'SELECT COALESCE(SUM(amount),0) AS t FROM construction_contractor_payments WHERE contract_id = ?',
					array($cid)
				)->row_array();
				$paid_amt = (float)$paid_row['t'];
				// Fallback: payments without contract_id linked only by contractor+project
				if ($paid_amt < 0.009) {
					$paid_row = $this->db->query(
						'SELECT COALESCE(SUM(amount),0) AS t FROM construction_contractor_payments
						 WHERE contractor_id = ? AND project_id = ? AND (contract_id IS NULL OR contract_id = 0)',
						array((int)$ct['contractor_id'], (int)$ct['project_id'])
					)->row_array();
					$paid_amt = (float)$paid_row['t'];
				}
				$contract_total = (float)$ct['total_amount'];
				$rem = max(0, $contract_total - $paid_amt);
				if ($rem <= 0.009) continue;
				$total += $rem;
				$rows[] = array(
					'row_kind' => 'contract_remaining',
					'id' => $cid,
					'date' => $ct['start_date'],
					'amount' => $rem,
					'party_name' => isset($ct['contractor_name']) ? $ct['contractor_name'] : '',
					'contractor_id' => (int)$ct['contractor_id'],
					'project_id' => (int)$ct['project_id'],
					'project_name' => isset($ct['project_name']) ? $ct['project_name'] : '',
					'title' => $ct['title'],
					'total_amount' => $contract_total,
					'paid_amount' => $paid_amt,
					'remaining' => $rem,
					'purpose' => trim(
						(isset($ct['project_name']) && $ct['project_name'] ? $ct['project_name'] . ' · ' : '')
						. ($ct['title'] ?: 'Contract')
						. ' · Contract ' . number_format($contract_total, 0)
						. ' · Paid ' . number_format($paid_amt, 0)
					),
					'construction_source' => 'contractor',
				);
			}
			$title = 'Contractor remaining (by contract)';
		} else {
			// contracts_value — all non-cancelled contracts
			if (!$this->db->table_exists('construction_contracts')) {
				$this->_json(array('success' => true, 'data' => array(), 'total' => 0, 'type' => $type, 'title' => 'Contracts value'));
			}
			$this->db->select(
				'construction_contracts.*, construction_contractors.contractor_name, construction_projects.project_name',
				false
			);
			$this->db->from('construction_contracts');
			$this->db->join(
				'construction_contractors',
				'construction_contractors.id = construction_contracts.contractor_id',
				'left'
			);
			$this->db->join(
				'construction_projects',
				'construction_projects.id = construction_contracts.project_id',
				'left'
			);
			$this->db->where('construction_contracts.status !=', 'cancelled');
			$this->db->order_by('construction_contracts.id', 'DESC');
			$contracts = $this->db->get()->result_array();
			foreach ($contracts as $ct) {
				$ct = $this->_enrich_contract($ct);
				$amt = (float)$ct['total_amount'];
				$total += $amt;
				$rows[] = array(
					'row_kind' => 'contract',
					'id' => (int)$ct['id'],
					'date' => $ct['start_date'],
					'amount' => $amt,
					'party_name' => isset($ct['contractor_name']) ? $ct['contractor_name'] : '',
					'contractor_id' => (int)$ct['contractor_id'],
					'project_id' => (int)$ct['project_id'],
					'project_name' => isset($ct['project_name']) ? $ct['project_name'] : '',
					'title' => $ct['title'],
					'total_amount' => $amt,
					'paid_amount' => (float)$ct['paid_amount'],
					'remaining' => (float)$ct['remaining'],
					'purpose' => trim(
						(isset($ct['project_name']) && $ct['project_name'] ? $ct['project_name'] . ' · ' : '')
						. ($ct['title'] ?: 'Contract')
						. ' · Paid ' . number_format((float)$ct['paid_amount'], 0)
						. ' · Rem ' . number_format((float)$ct['remaining'], 0)
					),
					'construction_source' => 'contractor',
				);
			}
			$title = 'Contracts value';
		}

		$this->_json(array(
			'success' => true,
			'data' => $rows,
			'total' => $total,
			'type' => $type,
			'title' => $title,
		));
	}

	public function campuses()
	{
		$this->db->from('campuses');
		$this->db->where('status', 1);
		$this->db->order_by('campus_name', 'ASC');
		$this->_json(array('success' => true, 'data' => $this->db->get()->result_array()));
	}

	/** GET expense_closing — last verified + next pending + optional ?date= day preview */
	public function expense_closing()
	{
		$last = $this->_last_verified_expense_date();
		$next = $this->_next_pending_expense_date();
		$last_row = null;
		if ($last) {
			$last_row = $this->db->get_where('construction_expense_closings', array('for_date' => $last))->row_array();
		}

		$view_date = trim((string)$this->input->get('date'));
		if ($view_date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $view_date)) {
			$view_date = $next ? $next : date('Y-m-d');
		}

		$selected_closing = null;
		if ($this->db->table_exists('construction_expense_closings')) {
			$selected_closing = $this->db->get_where('construction_expense_closings', array('for_date' => $view_date))->row_array();
		}
		$is_verified = !empty($selected_closing);
		$totals = $this->_day_expense_totals($view_date);
		$selected = array_merge(array('for_date' => $view_date), $totals, array(
			'expenses' => $this->_day_expense_rows($view_date),
			'is_verified' => $is_verified,
			'verified_by' => $selected_closing ? (isset($selected_closing['verified_by']) ? $selected_closing['verified_by'] : null) : null,
			'verified_at' => $selected_closing ? (isset($selected_closing['verified_at']) ? $selected_closing['verified_at'] : null) : null,
			'notes' => $selected_closing ? (isset($selected_closing['notes']) ? $selected_closing['notes'] : null) : null,
		));

		$can_verify_selected = $this->_can_verify_expense()
			&& $next !== null
			&& $view_date === $next
			&& !$is_verified;

		$this->_json(array(
			'success' => true,
			'date' => $view_date,
			'last_verified_date' => $last,
			'last_verified' => $last_row,
			'next_pending_date' => $next,
			'selected_day' => $selected,
			'next_pending' => $selected, // alias: day being viewed
			'can_verify' => $can_verify_selected,
			'can_verify_selected' => $can_verify_selected,
		));
	}

	/** GET expense_closings — verified history */
	public function expense_closings()
	{
		if (!$this->db->table_exists('construction_expense_closings')) {
			$this->_json(array('success' => true, 'data' => array()));
		}
		$limit = (int)$this->input->get('limit');
		if ($limit <= 0 || $limit > 200) $limit = 60;
		$rows = $this->db->order_by('for_date', 'DESC')->limit($limit)->get('construction_expense_closings')->result_array();
		$this->_json(array('success' => true, 'data' => $rows));
	}

	/**
	 * POST verify_expense_day
	 * Body: { for_date?: Y-m-d, notes?: string }
	 * Only the sequential next pending day (≤ today) can be verified.
	 */
	public function verify_expense_day()
	{
		$this->_assert_verify_expense();
		$body = $this->_body();
		$next = $this->_next_pending_expense_date();
		if (!$next) {
			$this->_json(array('success' => false, 'message' => 'Nothing pending to verify'), 400);
		}
		$for_date = !empty($body['for_date']) ? trim($body['for_date']) : $next;
		if ($for_date !== $next) {
			$this->_json(array(
				'success' => false,
				'message' => 'Verify sequentially: next pending day is ' . $next,
			), 400);
		}
		if ($this->_expense_day_is_verified($for_date)) {
			$this->_json(array('success' => false, 'message' => 'Day already verified'), 400);
		}

		$totals = $this->_day_expense_totals($for_date);
		$name = $this->_user_name();
		$now = date('Y-m-d H:i:s');
		$notes = isset($body['notes']) ? trim($body['notes']) : '';

		$this->db->insert('construction_expense_closings', array(
			'for_date' => $for_date,
			'total_amount' => $totals['total_amount'],
			'expense_count' => $totals['expense_count'],
			'verified_by' => $name,
			'verified_by_id' => (int)$this->current_user['user_id'],
			'verified_at' => $now,
			'notes' => $notes !== '' ? $notes : null,
		));
		$closing_id = (int)$this->db->insert_id();
		$this->_stamp_day_expenses_verified($for_date, $closing_id, $name, $now);

		$row = $this->db->get_where('construction_expense_closings', array('id' => $closing_id))->row_array();
		$this->_json(array(
			'success' => true,
			'message' => 'Day verified',
			'data' => $row,
			'last_verified_date' => $for_date,
			'next_pending_date' => $this->_next_pending_expense_date(),
		));
	}
}
