<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Accounts JSON API for React POS shell
 * Base: /index.php/accountsapi/{method}
 * Auth: same X-Pos-Token as Posapi / Inventoryapi
 */
class Accountsapi extends CI_Controller {

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
		$this->_access();
		if (!$this->_can_accounts()) {
			$this->_json(array('success' => false, 'message' => 'No accounts access'), 403);
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
		if (!$token) return null;
		$row = $this->db->query('SELECT * FROM pos_api_tokens WHERE token = ? LIMIT 1', array($token))->row_array();
		if (!$row || strtotime($row['expires_at']) < time()) return null;
		return $this->db->query(
			'SELECT * FROM users WHERE user_id = ? AND status = ? LIMIT 1',
			array((int)$row['user_id'], '1')
		)->row_array();
	}

	private function _access()
	{
		if ($this->access_row !== null) return $this->access_row;
		$uid = (int)$this->current_user['user_id'];
		$row = $this->db->query('SELECT * FROM access WHERE user_id = ? LIMIT 1', array($uid))->row_array();
		$this->access_row = $row ? $row : array();
		return $this->access_row;
	}

	private function _is_admin()
	{
		return isset($this->current_user['role']) && $this->current_user['role'] === 'Admin';
	}

	private function _actor_name()
	{
		$name = trim($this->current_user['first_name'] . ' ' . $this->current_user['last_name']);
		return $name !== '' ? $name : 'POS';
	}

	private function _can_accounts()
	{
		if ($this->_is_admin()) return true;
		$row = $this->_access();
		if (!$row) return false;
		return !empty($row['accounts_sidebar'])
			|| !empty($row['account_details'])
			|| !empty($row['campus_petty_cash']);
	}

	/** null = admin (all); array of string ids for non-admin */
	private function _access_id_list($field)
	{
		if ($this->_is_admin()) return null;
		$row = $this->_access();
		if (!$row || empty($row[$field])) return array();
		$ids = array();
		foreach (explode(',', $row[$field]) as $id) {
			$id = trim($id);
			if ($id !== '') $ids[] = $id;
		}
		return array_values(array_unique($ids));
	}

	private function _filter_by_ids($rows, $idField, $accessField)
	{
		$allowed = $this->_access_id_list($accessField);
		if ($allowed === null) return $rows;
		if (!count($allowed)) return array();
		$out = array();
		foreach ($rows as $row) {
			if (!isset($row[$idField])) continue;
			if (in_array((string)$row[$idField], $allowed, true)) {
				$out[] = $row;
			}
		}
		return $out;
	}

	private function _feature($field)
	{
		if ($this->_is_admin()) return true;
		$row = $this->_access();
		return $row && !empty($row[$field]);
	}

	private function _can_access_account_id($accountId, $accessField)
	{
		$allowed = $this->_access_id_list($accessField);
		if ($allowed === null) return true;
		return in_array((string)$accountId, $allowed, true);
	}

	private function _can_edit_account_id($accountId)
	{
		if (!$this->_feature('account_edit')) return false;
		if ($this->_is_admin()) return true;
		return $this->_can_access_account_id($accountId, 'allowed_cash_account_ids')
			|| $this->_can_access_account_id($accountId, 'allowed_bank_account_ids');
	}

	/** Save multipart image/proof/record to uploads/; returns filename or '' */
	private function _upload_proof()
	{
		$fileKey = null;
		if (!empty($_FILES['image']['name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
			$fileKey = 'image';
		} elseif (!empty($_FILES['proof']['name']) && is_uploaded_file($_FILES['proof']['tmp_name'])) {
			$fileKey = 'proof';
		} elseif (!empty($_FILES['record']['name']) && is_uploaded_file($_FILES['record']['tmp_name'])) {
			$fileKey = 'record';
		}
		if (!$fileKey) return '';

		$dir = FCPATH . 'uploads/';
		if (!is_dir($dir)) {
			@mkdir($dir, 0777, true);
		}
		$ext = pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION);
		$filename = 'proof_' . date('YmdHis') . '_' . mt_rand(1000, 9999);
		if ($ext !== '') $filename .= '.' . preg_replace('/[^a-zA-Z0-9]/', '', $ext);
		$dest = $dir . $filename;
		if (!move_uploaded_file($_FILES[$fileKey]['tmp_name'], $dest)) {
			return '';
		}
		return $filename;
	}

	private function _parse_account_name($accountName)
	{
		$bank = '';
		$accountno = '';
		$name = (string)$accountName;
		$pos = strpos($name, '(');
		if ($pos !== false) {
			$bank = trim(substr($name, 0, $pos));
			$end = strpos($name, ')', $pos);
			if ($end !== false) {
				$accountno = trim(substr($name, $pos + 1, $end - $pos - 1));
			}
		} else {
			$bank = trim($name);
		}
		return array($bank, $accountno);
	}

	private function _table_exists($table)
	{
		return $this->db->table_exists($table);
	}

	private function _fy_range($from = null, $to = null)
	{
		if (!$from || !$to) {
			$year = (int)date('Y');
			if ((int)date('m') < 7) {
				$fromYear = $year - 1;
				$toYear = $year;
			} else {
				$fromYear = $year;
				$toYear = $year + 1;
			}
			$from = $fromYear . '-07-01';
			$to = $toYear . '-06-30';
		}
		return array($from, $to);
	}

	private function _money_row($title, $credit, $debit, $balance, $type, $group, $url = '', $opening = 0)
	{
		return array(
			'title' => $title,
			'opening' => (float)$opening,
			'credit' => (float)$credit,
			'debit' => (float)$debit,
			'balance' => (float)$balance,
			'type' => $type,
			'group' => $group,
			'url' => $url,
		);
	}

	private function _account_activity($accountId, $from, $to)
	{
		$activity = array('credit' => 0, 'debit' => 0);
		if (!$this->_table_exists('transactions_history')) return $activity;
		$row = $this->db->query(
			"SELECT
				SUM(CASE WHEN debit_credit = 'D' THEN amount ELSE 0 END) as credit_amount,
				SUM(CASE WHEN debit_credit = 'C' THEN amount ELSE 0 END) as debit_amount
			 FROM transactions_history
			 WHERE transaction_account_id = ?
			   AND created_at >= ?
			   AND created_at <= ?",
			array((int)$accountId, $from . ' 00:00:00', $to . ' 23:59:59')
		)->row_array();
		$activity['credit'] = (float)(isset($row['credit_amount']) ? $row['credit_amount'] : 0);
		$activity['debit'] = (float)(isset($row['debit_amount']) ? $row['debit_amount'] : 0);
		return $activity;
	}

	private function _account_opening_balance($accountId, $from)
	{
		if (!$this->_table_exists('transactions_history')) return 0;
		$row = $this->db->query(
			"SELECT
				SUM(CASE WHEN debit_credit = 'D' THEN amount ELSE 0 END) as debit_amount,
				SUM(CASE WHEN debit_credit = 'C' THEN amount ELSE 0 END) as credit_amount
			 FROM transactions_history
			 WHERE transaction_account_id = ?
			   AND created_at < ?",
			array((int)$accountId, $from . ' 00:00:00')
		)->row_array();
		return (float)(isset($row['debit_amount']) ? $row['debit_amount'] : 0)
			- (float)(isset($row['credit_amount']) ? $row['credit_amount'] : 0);
	}

	private function _bank_statement_balance_as_of($accountId, $to)
	{
		if (!$this->_table_exists('bank_reconciliation_statement')) {
			$activity = $this->_account_activity($accountId, '1970-01-01', $to);
			return $activity['credit'] - $activity['debit'];
		}
		$row = $this->db->query(
			"SELECT balance FROM bank_reconciliation_statement
			 WHERE account_id = ?
			   AND balance IS NOT NULL AND balance != ''
			   AND trans_date <= ?
			 ORDER BY trans_date DESC, id DESC LIMIT 1",
			array((int)$accountId, $to)
		)->row_array();
		if (!$row || !isset($row['balance'])) return 0;
		return (float)str_replace(',', '', $row['balance']);
	}

	private function _bank_statement_activity($accountId, $from, $to)
	{
		$activity = array('credit' => 0, 'debit' => 0);
		if (!$this->_table_exists('bank_reconciliation_statement')) return $activity;
		$rows = $this->db->query(
			"SELECT description, trans_date, credit, debit
			 FROM bank_reconciliation_statement
			 WHERE account_id = ? AND trans_date >= ? AND trans_date <= ?
			 GROUP BY description, trans_date, credit, debit",
			array((int)$accountId, $from, $to)
		)->result_array();
		foreach ($rows as $row) {
			$activity['credit'] += (float)str_replace(',', '', isset($row['credit']) ? $row['credit'] : 0);
			$activity['debit'] += (float)str_replace(',', '', isset($row['debit']) ? $row['debit'] : 0);
		}
		return $activity;
	}

	private function _petty_cash_balance_as_of($petty, $date)
	{
		$balance = (float)(isset($petty['opening_balance']) ? $petty['opening_balance'] : 0);
		$end = $date . ' 23:59:59';
		$givenDate = !empty($petty['given_date']) ? $petty['given_date'] : '1970-01-01';
		$assignTo = (int)$petty['assign_to'];
		$pettyId = (int)$petty['id'];

		if ($this->_table_exists('expenses')) {
			$row = $this->db->query(
				"SELECT SUM(amount) as amount FROM expenses
				 WHERE add_by_id = ?
				   AND actual_date >= ?
				   AND actual_date <= ?
				   AND paid_type = 'cash'
				   AND expense_id NOT IN (SELECT expense_id FROM bank_reconciliation_statement WHERE expense_id IS NOT NULL)",
				array($assignTo, $givenDate, $end)
			)->row_array();
			$balance -= (float)(isset($row['amount']) ? $row['amount'] : 0);
		}

		if ($this->_table_exists('cash_reversal')) {
			$row = $this->db->query(
				"SELECT SUM(cash_reversal.amount) as amount
				 FROM cash_reversal
				 INNER JOIN expenses ON expenses.expense_id = cash_reversal.expense_id
				 WHERE expenses.add_by_id = ?
				   AND cash_reversal.created_at >= ?
				   AND cash_reversal.created_at <= ?",
				array($assignTo, $givenDate . ' 00:00:00', $end)
			)->row_array();
			$balance += (float)(isset($row['amount']) ? $row['amount'] : 0);
		}

		if ($this->_table_exists('petty_cash_history')) {
			$row = $this->db->query(
				"SELECT
					SUM(CASE WHEN debit_credit = 'D' THEN amount_given ELSE 0 END) as debit_amount,
					SUM(CASE WHEN debit_credit = 'C' THEN amount_given ELSE 0 END) as credit_amount
				 FROM petty_cash_history
				 WHERE transaction_pettycash_account = ?
				   AND created_at <= ?",
				array($pettyId, $end)
			)->row_array();
			$balance += (float)(isset($row['debit_amount']) ? $row['debit_amount'] : 0);
			$balance -= (float)(isset($row['credit_amount']) ? $row['credit_amount'] : 0);
		}

		return $balance;
	}

	private function _petty_cash_activity($petty, $from, $to)
	{
		$activity = array('credit' => 0, 'debit' => 0);
		$assignTo = (int)$petty['assign_to'];
		$pettyId = (int)$petty['id'];

		if ($this->_table_exists('petty_cash_history')) {
			$row = $this->db->query(
				"SELECT
					SUM(CASE WHEN debit_credit = 'D' THEN amount_given ELSE 0 END) as credit_amount,
					SUM(CASE WHEN debit_credit = 'C' THEN amount_given ELSE 0 END) as debit_amount
				 FROM petty_cash_history
				 WHERE transaction_pettycash_account = ?
				   AND created_at >= ? AND created_at <= ?",
				array($pettyId, $from . ' 00:00:00', $to . ' 23:59:59')
			)->row_array();
			$activity['credit'] += (float)(isset($row['credit_amount']) ? $row['credit_amount'] : 0);
			$activity['debit'] += (float)(isset($row['debit_amount']) ? $row['debit_amount'] : 0);
		}

		if ($this->_table_exists('expenses')) {
			$row = $this->db->query(
				"SELECT SUM(amount) as amount FROM expenses
				 WHERE add_by_id = ?
				   AND actual_date >= ? AND actual_date <= ?
				   AND paid_type = 'cash'
				   AND expense_id NOT IN (SELECT expense_id FROM bank_reconciliation_statement WHERE expense_id IS NOT NULL)",
				array($assignTo, $from . ' 00:00:00', $to . ' 23:59:59')
			)->row_array();
			$activity['debit'] += (float)(isset($row['amount']) ? $row['amount'] : 0);
		}

		if ($this->_table_exists('cash_reversal')) {
			$row = $this->db->query(
				"SELECT SUM(cash_reversal.amount) as amount
				 FROM cash_reversal
				 INNER JOIN expenses ON expenses.expense_id = cash_reversal.expense_id
				 WHERE expenses.add_by_id = ?
				   AND cash_reversal.created_at >= ? AND cash_reversal.created_at <= ?",
				array($assignTo, $from . ' 00:00:00', $to . ' 23:59:59')
			)->row_array();
			$activity['credit'] += (float)(isset($row['amount']) ? $row['amount'] : 0);
		}

		return $activity;
	}

	/** Live balance matching helper pettycash_statement() */
	private function _petty_live_balance($pettyId)
	{
		$petty = $this->db->query(
			'SELECT * FROM petty_cash_college_wise WHERE id = ? LIMIT 1',
			array((int)$pettyId)
		)->row_array();
		if (!$petty) return 0;

		$fromDate = date('Y-m-d');
		$open = (float)$petty['opening_balance'];
		$assignTo = (int)$petty['assign_to'];
		$givenDate = !empty($petty['given_date']) ? $petty['given_date'] : '1970-01-01';

		$expenseRow = $this->db->query(
			"SELECT SUM(amount) as amount FROM expenses
			 WHERE add_by_id = ?
			   AND actual_date >= ?
			   AND actual_date < ?
			   AND paid_type = 'cash'
			   AND expense_id NOT IN (SELECT expense_id FROM bank_reconciliation_statement WHERE expense_id IS NOT NULL)",
			array($assignTo, $givenDate, $fromDate . ' 23:59:59')
		)->row_array();
		$expenseAmt = (float)(isset($expenseRow['amount']) ? $expenseRow['amount'] : 0);

		$revRow = $this->db->query(
			"SELECT SUM(cash_reversal.amount) as amount
			 FROM cash_reversal
			 INNER JOIN expenses ON expenses.expense_id = cash_reversal.expense_id
			 WHERE expenses.add_by_id = ?
			   AND cash_reversal.created_at >= ?
			   AND cash_reversal.created_at < ?",
			array($assignTo, $givenDate, $fromDate . ' 23:59:59')
		)->row_array();
		$revAmt = (float)(isset($revRow['amount']) ? $revRow['amount'] : 0);

		$hist = $this->db->query(
			"SELECT debit_credit, amount_given as amount
			 FROM petty_cash_history
			 WHERE transaction_pettycash_account = ?
			   AND created_at <= ?",
			array((int)$pettyId, $fromDate . ' 23:59:59')
		)->result_array();
		$debit = 0;
		$credit = 0;
		foreach ($hist as $tran) {
			if ($tran['debit_credit'] === 'C') $credit += (float)$tran['amount'];
			else $debit += (float)$tran['amount'];
		}

		return ($open + $debit + $revAmt) - $credit - $expenseAmt;
	}

	private function _expense_head_name($category, $categories)
	{
		$current = $category;
		while (!empty($current['sub_of']) && isset($categories[$current['sub_of']])) {
			$current = $categories[$current['sub_of']];
		}
		return isset($current['name']) ? $current['name'] : 'Expense';
	}

	private function _section_defs()
	{
		return array(
			array('key' => 'how_to_use', 'label' => 'How To Use', 'access' => null),
			array('key' => 'account_details', 'label' => 'Accounts', 'access' => 'account_details'),
			array('key' => 'chart_of_accounts', 'label' => 'Chart of Accounts', 'access' => 'chart_of_accounts'),
			array('key' => 'profit_distribution', 'label' => 'Profit Distribution Campus Wise', 'access' => 'profit_distribution'),
			array('key' => 'campus_petty_cash', 'label' => 'Campus PettyCash', 'access' => 'campus_petty_cash'),
			array('key' => 'advance_system', 'label' => 'Advance System', 'access' => 'advance_system'),
			array('key' => 'loan_approval_accounts', 'label' => 'Loans Approval Accounts', 'access' => 'loan_approval_accounts'),
			array('key' => 'dailyclosing', 'label' => 'Daily Closings', 'access' => 'dailyclosing'),
			array('key' => 'closing_reconcile', 'label' => 'Closings conciliation', 'access' => 'closing_reconcile'),
			array('key' => 'misc_income', 'label' => 'Miscellaneous Income', 'access' => 'misc_income'),
			array('key' => 'bank_reconciliation', 'label' => 'Statement Reconciliation', 'access' => 'bank_reconciliation'),
			array('key' => 'paypro_settlements', 'label' => 'PayPro Statement Reconciliation', 'access' => 'bank_reconciliation'),
			array('key' => 'paypro_untagged', 'label' => 'Untagged Paypro Entries', 'access' => 'bank_reconciliation'),
			array('key' => 'paypro_transactions', 'label' => 'PayPro Transactions', 'access' => 'bank_reconciliation'),
			array('key' => 'day_closing_report', 'label' => 'Day Closing', 'access' => 'bank_reconciliation'),
			array('key' => 'bulk_fee_meta', 'label' => 'Add Bulk Student Payments', 'access' => null),
		);
	}

	/* ===================== Public endpoints ===================== */

	public function meta()
	{
		$access = $this->_access();
		$sections = array();
		foreach ($this->_section_defs() as $def) {
			$enabled = $this->_is_admin();
			if (!$enabled) {
				if ($def['access'] === null) {
					$enabled = !empty($access['accounts_sidebar']) || !empty($access['account_details']);
				} else {
					$enabled = !empty($access[$def['access']]);
				}
			}
			$sections[] = array(
				'key' => $def['key'],
				'label' => $def['label'],
				'enabled' => (bool)$enabled,
			);
		}

		$this->_json(array(
			'success' => true,
			'can_add_account' => $this->_feature('account_add_account'),
			'can_funds_transfer' => $this->_feature('account_funds_transfer'),
			'can_edit_account' => $this->_feature('account_edit'),
			'can_petty' => $this->_is_admin() || !empty($access['campus_petty_cash']),
			'is_admin' => $this->_is_admin(),
			'sections' => $sections,
		));
	}

	public function cash_accounts()
	{
		$rows = $this->db->query(
			'SELECT * FROM accounts WHERE type = 0 ORDER BY id ASC'
		)->result_array();
		$rows = $this->_filter_by_ids($rows, 'id', 'allowed_cash_account_ids');

		$out = array();
		foreach ($rows as $row) {
			list($bank, $accountno) = $this->_parse_account_name(isset($row['account_name']) ? $row['account_name'] : '');
			$amount = (float)$row['amount'];
			$limit = (float)$row['account_limit'];
			$shiftable = $amount > $limit ? ($amount - $limit) : 0;
			$row['bank'] = $bank;
			$row['accountno'] = $accountno;
			$row['shiftable_amount'] = $shiftable;
			$out[] = $row;
		}

		$this->_json(array('success' => true, 'accounts' => $out));
	}

	public function transfer_targets()
	{
		$accounts = $this->db->query(
			'SELECT * FROM accounts ORDER BY account_title ASC, id ASC'
		)->result_array();
		$accounts = $this->_filter_by_ids($accounts, 'id', 'funds_transfer_account_ids');

		$petty = $this->db->query(
			"SELECT petty_cash_college_wise.*, campuses.campus_name,
					users.first_name, users.last_name, designations.designation_name
			 FROM petty_cash_college_wise
			 LEFT JOIN campuses ON campuses.campus_id = petty_cash_college_wise.campus_id
			 LEFT JOIN users ON users.user_id = petty_cash_college_wise.assign_to
			 LEFT JOIN designations ON designations.designation_id = users.designation_id
			 WHERE petty_cash_college_wise.petty_status = 1
			 ORDER BY campuses.campus_name ASC"
		)->result_array();
		$petty = $this->_filter_by_ids($petty, 'id', 'account_details_pettycash_ids');

		$this->_json(array(
			'success' => true,
			'accounts' => $accounts,
			'petty_cash' => $petty,
		));
	}

	public function add_account()
	{
		if (!$this->_feature('account_add_account')) {
			$this->_json(array('success' => false, 'message' => 'No permission to add accounts'), 403);
		}
		$b = $this->_body();
		$bank = isset($b['bank']) ? trim($b['bank']) : '';
		$title = isset($b['title']) ? trim($b['title']) : '';
		$accountno = isset($b['accountno']) ? trim($b['accountno']) : '';
		$amount = isset($b['amount']) ? (float)$b['amount'] : 0;
		$amount_limit = isset($b['amount_limit']) ? (float)$b['amount_limit'] : 0;
		$taxable = isset($b['taxable']) ? (int)$b['taxable'] : (isset($b['account_taxable']) ? (int)$b['account_taxable'] : 1);
		$for_closing = isset($b['for_closing']) ? (int)$b['for_closing'] : 1;
		// SPA cash accounts: default type 0 (legacy add_account hardcoded 1 is a bug)
		$type = isset($b['type']) ? (int)$b['type'] : (isset($b['account_type']) ? (int)$b['account_type'] : 0);

		if ($bank === '' || $title === '') {
			$this->_json(array('success' => false, 'message' => 'bank and title required'), 422);
		}

		$account_name = $bank . ' (' . $accountno . ')';
		$this->db->query(
			'INSERT INTO accounts (account_name, account_title, amount, type, taxable, for_closing, account_limit)
			 VALUES (?, ?, ?, ?, ?, ?, ?)',
			array($account_name, $title, $amount, $type, $taxable, $for_closing, $amount_limit)
		);
		$id = (int)$this->db->insert_id();

		$this->_json(array('success' => true, 'id' => $id, 'message' => 'Account added'));
	}

	public function edit_account()
	{
		$b = $this->_body();
		$id = isset($b['daccount_id']) ? (int)$b['daccount_id'] : (isset($b['id']) ? (int)$b['id'] : 0);
		if ($id <= 0) {
			$this->_json(array('success' => false, 'message' => 'daccount_id required'), 422);
		}
		if (!$this->_can_edit_account_id($id)) {
			$this->_json(array('success' => false, 'message' => 'No permission to edit this account'), 403);
		}

		$bank = isset($b['bank']) ? trim($b['bank']) : '';
		$title = isset($b['title']) ? trim($b['title']) : '';
		$accountno = isset($b['accountno']) ? trim($b['accountno']) : '';
		$amount_limit = isset($b['amount_limit']) ? (float)$b['amount_limit'] : 0;
		$type = isset($b['type']) ? (int)$b['type'] : (isset($b['account_type']) ? (int)$b['account_type'] : 0);
		$taxable = isset($b['taxable']) ? (int)$b['taxable'] : (isset($b['account_taxable']) ? (int)$b['account_taxable'] : 1);
		$for_closing = isset($b['for_closing']) ? (int)$b['for_closing'] : 1;

		$account_name = $bank . ' (' . $accountno . ')';
		// No amount update (matches legacy Accounts::edit)
		$this->db->query(
			'UPDATE accounts
			 SET account_name = ?, account_title = ?, account_limit = ?, type = ?, taxable = ?, for_closing = ?
			 WHERE id = ?',
			array($account_name, $title, $amount_limit, $type, $taxable, $for_closing, $id)
		);

		$this->_json(array('success' => true, 'message' => 'Account updated'));
	}

	public function transfer_funds()
	{
		if (!$this->_feature('account_funds_transfer')) {
			$this->_json(array('success' => false, 'message' => 'No permission to transfer funds'), 403);
		}
		$b = $this->_body();
		$petty_account = isset($b['petty_account']) ? (string)$b['petty_account'] : '0';
		$from = isset($b['from_account']) ? (int)$b['from_account'] : 0;
		$to = isset($b['to_account']) ? (int)$b['to_account'] : 0;
		$pettyid = isset($b['petty_account_id']) ? (int)$b['petty_account_id'] : 0;
		$accountamount = isset($b['sentamount']) ? (float)$b['sentamount'] : 0;
		$trasfer_reason = isset($b['trasfer_reason']) ? trim($b['trasfer_reason']) : '';

		if ($from <= 0 || $accountamount <= 0) {
			$this->_json(array('success' => false, 'message' => 'from_account and sentamount required'), 422);
		}

		if ($petty_account === '0') {
			if ($to <= 0) {
				$this->_json(array('success' => false, 'message' => 'to_account required'), 422);
			}
			if (!$this->_can_access_account_id($to, 'funds_transfer_account_ids')) {
				$this->_json(array('success' => false, 'message' => 'Invalid to account selected'), 403);
			}
		} else {
			if ($pettyid <= 0) {
				$this->_json(array('success' => false, 'message' => 'petty_account_id required'), 422);
			}
			if (!$this->_can_access_account_id($pettyid, 'account_details_pettycash_ids')) {
				$this->_json(array('success' => false, 'message' => 'Invalid petty cash account selected'), 403);
			}
		}

		$image = '';
		if (!empty($b['proof_image'])) {
			$image = basename((string)$b['proof_image']);
		} else {
			$image = $this->_upload_proof();
		}

		$actor = $this->_actor_name();
		$now = date('Y-m-d H:i:s');

		if ($petty_account === '0') {
			$this->db->query('UPDATE accounts SET amount = amount + ? WHERE id = ?', array($accountamount, $to));
			$this->db->query('UPDATE accounts SET amount = amount - ? WHERE id = ?', array($accountamount, $from));

			$this->db->query(
				"INSERT INTO transactions_history
				 (from_account_id, to_account_id, transaction_account_id, amount, debit_credit, transaction_by, reason, proof_image, created_at)
				 VALUES (?, ?, ?, ?, 'C', ?, ?, ?, ?)",
				array($from, $to, $from, $accountamount, $actor, 'Funds Transfer ' . $trasfer_reason, $image, $now)
			);
			$this->db->query(
				"INSERT INTO transactions_history
				 (from_account_id, to_account_id, transaction_account_id, amount, debit_credit, transaction_by, reason, proof_image, created_at)
				 VALUES (?, ?, ?, ?, 'D', ?, ?, ?, ?)",
				array($from, $to, $to, $accountamount, $actor, 'Funds Receive ' . $trasfer_reason, $image, $now)
			);
		} else {
			$this->db->query('UPDATE accounts SET amount = amount - ? WHERE id = ?', array($accountamount, $from));

			$this->db->query(
				"INSERT INTO transactions_history
				 (from_account_id, to_pettycash_id, transaction_account_id, amount, debit_credit, transaction_by, reason, proof_image, created_at)
				 VALUES (?, ?, ?, ?, 'C', ?, ?, ?, ?)",
				array($from, $pettyid, $from, $accountamount, $actor, 'Funds Transfer ' . $trasfer_reason, $image, $now)
			);

			$this->db->query(
				'UPDATE petty_cash_college_wise SET remaining_amount = remaining_amount + ? WHERE id = ?',
				array($accountamount, $pettyid)
			);

			$this->db->query(
				"INSERT INTO petty_cash_history
				 (debit_credit, amount_given, from_account, to_pettycash_id, transaction_pettycash_account, status, reason, proof_image, transaction_by, created_at)
				 VALUES ('D', ?, ?, ?, ?, '1', ?, ?, ?, ?)",
				array($accountamount, $from, $pettyid, $pettyid, $trasfer_reason, $image, $actor, $now)
			);
		}

		$this->_json(array('success' => true, 'message' => 'Funds transferred', 'proof_image' => $image));
	}

	public function cash_statement()
	{
		$account_id = (int)$this->input->get('account_id');
		$from_date = $this->input->get('from_date');
		$to_date = $this->input->get('to_date');
		if ($account_id <= 0) {
			$this->_json(array('success' => false, 'message' => 'account_id required'), 422);
		}
		if (!$from_date) $from_date = date('Y-m-01');
		if (!$to_date) $to_date = date('Y-m-d');

		if (!$this->_is_admin()
			&& !$this->_can_access_account_id($account_id, 'allowed_cash_account_ids')
			&& !$this->_can_access_account_id($account_id, 'allowed_bank_account_ids')
			&& !$this->_can_access_account_id($account_id, 'funds_transfer_account_ids')
		) {
			$this->_json(array('success' => false, 'message' => 'No access to this account'), 403);
		}

		$account = $this->db->query('SELECT * FROM accounts WHERE id = ? LIMIT 1', array($account_id))->row_array();
		if (!$account) {
			$this->_json(array('success' => false, 'message' => 'Account not found'), 404);
		}

		$before = $this->db->query(
			"SELECT * FROM transactions_history
			 WHERE transaction_account_id = ?
			   AND created_at < ?
			 ORDER BY created_at ASC, id ASC",
			array($account_id, $from_date . ' 00:00:00')
		)->result_array();

		$debit = 0;
		$credit = 0;
		foreach ($before as $tran) {
			if ($tran['debit_credit'] === 'C') $credit += (float)$tran['amount'];
			else $debit += (float)$tran['amount'];
		}
		$opening = $debit - $credit;

		$rows = $this->db->query(
			"SELECT * FROM transactions_history
			 WHERE transaction_account_id = ?
			   AND created_at >= ? AND created_at <= ?
			 ORDER BY created_at ASC, id ASC",
			array($account_id, $from_date . ' 00:00:00', $to_date . ' 23:59:59')
		)->result_array();

		$balance = $opening;
		$out = array();
		foreach ($rows as $tran) {
			$amt = (float)$tran['amount'];
			if ($tran['debit_credit'] === 'D') $balance += $amt;
			else $balance -= $amt;
			$tran['running_balance'] = $balance;
			$tran['debit'] = $tran['debit_credit'] === 'D' ? $amt : 0;
			$tran['credit'] = $tran['debit_credit'] === 'C' ? $amt : 0;
			$out[] = $tran;
		}

		$this->_json(array(
			'success' => true,
			'account' => $account,
			'from_date' => $from_date,
			'to_date' => $to_date,
			'opening_balance' => $opening,
			'closing_balance' => $balance,
			'rows' => $out,
		));
	}

	public function chart_of_accounts()
	{
		list($from, $to) = $this->_fy_range($this->input->get('from_date'), $this->input->get('to_date'));
		$rows = array();
		$totals = array('opening' => 0, 'credit' => 0, 'debit' => 0, 'balance' => 0);

		$accounts = $this->db->query(
			'SELECT * FROM accounts ORDER BY type ASC, account_title ASC'
		)->result_array();
		foreach ($accounts as $account) {
			$isBank = ((int)$account['type'] === 1);
			$activity = $isBank
				? $this->_bank_statement_activity($account['id'], $from, $to)
				: $this->_account_activity($account['id'], $from, $to);
			$group = $isBank ? 'Main Accounts - Bank' : 'Main Accounts - Cash';
			$title = trim($account['account_title'] . ' ' . $account['account_name']);
			if ($isBank) {
				$balance = $this->_bank_statement_balance_as_of($account['id'], $to);
				$opening = $balance - $activity['credit'] + $activity['debit'];
				if ($opening < 0) {
					$activity['debit'] += abs($opening);
					$opening = 0;
				}
			} else {
				$opening = $this->_account_opening_balance($account['id'], $from);
				$balance = $opening + $activity['credit'] - $activity['debit'];
			}
			$row = $this->_money_row($title, $activity['credit'], $activity['debit'], $balance, 'Asset', $group, '', $opening);
			$row['account_id'] = (int)$account['id'];
			$rows[] = $row;
			$totals['opening'] += $row['opening'];
			$totals['credit'] += $row['credit'];
			$totals['debit'] += $row['debit'];
			$totals['balance'] += $row['balance'];
		}

		if ($this->_table_exists('petty_cash_college_wise')) {
			$pettyCash = $this->db->query(
				"SELECT petty_cash_college_wise.*, campuses.campus_name, users.first_name, users.last_name
				 FROM petty_cash_college_wise
				 LEFT JOIN campuses ON campuses.campus_id = petty_cash_college_wise.campus_id
				 LEFT JOIN users ON users.user_id = petty_cash_college_wise.assign_to
				 WHERE petty_cash_college_wise.petty_status = 1
				 ORDER BY campuses.campus_name ASC"
			)->result_array();
			foreach ($pettyCash as $petty) {
				$openingDate = date('Y-m-d', strtotime($from . ' -1 day'));
				$opening = $this->_petty_cash_balance_as_of($petty, $openingDate);
				$activity = $this->_petty_cash_activity($petty, $from, $to);
				$balance = $opening + $activity['credit'] - $activity['debit'];
				$title = trim(
					(isset($petty['campus_name']) ? $petty['campus_name'] : '') . ' - ' .
					(isset($petty['first_name']) ? $petty['first_name'] : '') . ' ' .
					(isset($petty['last_name']) ? $petty['last_name'] : '')
				);
				$row = $this->_money_row($title, $activity['credit'], $activity['debit'], $balance, 'Asset', 'Petty Cash Accounts', '', $opening);
				$row['petty_id'] = (int)$petty['id'];
				$rows[] = $row;
				$totals['opening'] += $row['opening'];
				$totals['credit'] += $row['credit'];
				$totals['debit'] += $row['debit'];
				$totals['balance'] += $row['balance'];
			}
		}

		if ($this->_table_exists('payments')) {
			$feeIncome = $this->db->query(
				"SELECT SUM(CASE WHEN actual_amount > 0 THEN actual_amount ELSE amount END) as total
				 FROM payments
				 WHERE paid = 1 AND actual_paid_date >= ? AND actual_paid_date <= ?",
				array($from, $to)
			)->row_array();
			$total = (float)(isset($feeIncome['total']) ? $feeIncome['total'] : 0);
			$row = $this->_money_row('Student Fee / Recovery Received', $total, 0, $total, 'Income', 'Credit / Income');
			$rows[] = $row;
			$totals['credit'] += $row['credit'];
			$totals['balance'] += $row['balance'];
		}

		if ($this->_table_exists('misc_incomes') && $this->_table_exists('transactions_history')) {
			$miscIncome = $this->db->query(
				"SELECT SUM(misc_incomes.amount) as amount
				 FROM misc_incomes
				 INNER JOIN transactions_history ON transactions_history.misc_id = misc_incomes.id
				 WHERE transactions_history.created_at >= ? AND transactions_history.created_at <= ?",
				array($from . ' 00:00:00', $to . ' 23:59:59')
			)->row_array();
			$amt = (float)(isset($miscIncome['amount']) ? $miscIncome['amount'] : 0);
			$row = $this->_money_row('Miscellaneous Income', $amt, 0, $amt, 'Income', 'Credit / Income');
			$rows[] = $row;
			$totals['credit'] += $row['credit'];
			$totals['balance'] += $row['balance'];
		}

		if ($this->_table_exists('expense_category') && $this->_table_exists('expenses')) {
			$categoryRows = $this->db->query('SELECT * FROM expense_category')->result_array();
			$categories = array();
			foreach ($categoryRows as $category) {
				$categories[$category['expense_category_id']] = $category;
			}

			$expenseRows = $this->db->query(
				"SELECT expense_category.expense_category_id, expense_category.name, expense_category.sub_of,
						SUM(expenses.amount) as total_amount
				 FROM expenses
				 LEFT JOIN expense_category ON expense_category.expense_category_id = expenses.expense_category_id
				 WHERE expenses.date >= ? AND expenses.date <= ? AND expenses.approved_status = 1
				 GROUP BY expense_category.expense_category_id",
				array($from, $to)
			)->result_array();

			$expenseHeads = array();
			foreach ($expenseRows as $expense) {
				if (!isset($categories[$expense['expense_category_id']])) continue;
				$head = $this->_expense_head_name($categories[$expense['expense_category_id']], $categories);
				if (!isset($expenseHeads[$head])) $expenseHeads[$head] = 0;
				$expenseHeads[$head] += (float)$expense['total_amount'];
			}
			ksort($expenseHeads);
			foreach ($expenseHeads as $head => $amount) {
				$row = $this->_money_row($head, 0, $amount, $amount, 'Expense', 'Debit / Expense Heads');
				$rows[] = $row;
				$totals['debit'] += $row['debit'];
				$totals['balance'] += $row['balance'];
			}

			$pendingExpense = $this->db->query(
				"SELECT SUM(amount) as amount FROM expenses
				 WHERE date >= ? AND date <= ? AND approved_status = 0",
				array($from, $to)
			)->row_array();
			$pendingAmt = (float)(isset($pendingExpense['amount']) ? $pendingExpense['amount'] : 0);
			if ($pendingAmt > 0) {
				$row = $this->_money_row('Pending / Unapproved Expenses', 0, 0, $pendingAmt, 'Liability', 'Liabilities / Payables', '', $pendingAmt);
				$rows[] = $row;
				$totals['opening'] += $row['opening'];
				$totals['balance'] += $row['balance'];
			}
		}

		if ($this->_table_exists('loans') && $this->_table_exists('loan_plan')) {
			$loanRow = $this->db->query(
				"SELECT SUM(loan_plan.amount - IFNULL(loan_plan.amount_paid, 0)) as remaining
				 FROM loan_plan
				 INNER JOIN loans ON loans.id = loan_plan.loan_id
				 WHERE loans.status = 1"
			)->row_array();
			$remaining = (float)(isset($loanRow['remaining']) ? $loanRow['remaining'] : 0);
			if ($remaining > 0) {
				$row = $this->_money_row('Staff Loans / Advances Receivable', 0, 0, $remaining, 'Asset', 'Loans / Advances', '', $remaining);
				$rows[] = $row;
				$totals['opening'] += $row['opening'];
				$totals['balance'] += $row['balance'];
			}
		}

		$this->_json(array(
			'success' => true,
			'from_date' => $from,
			'to_date' => $to,
			'rows' => $rows,
			'totals' => $totals,
		));
	}

	public function petty_cash()
	{
		$status = $this->input->get('status');
		if ($status === null || $status === '') $status = '1';
		$status = (int)$status;

		$rows = $this->db->query(
			"SELECT petty_cash_college_wise.*, campuses.campus_name,
					users.first_name, users.last_name, designations.designation_name
			 FROM petty_cash_college_wise
			 LEFT JOIN campuses ON campuses.campus_id = petty_cash_college_wise.campus_id
			 LEFT JOIN users ON users.user_id = petty_cash_college_wise.assign_to
			 LEFT JOIN designations ON designations.designation_id = users.designation_id
			 WHERE petty_cash_college_wise.petty_status = ?
			 ORDER BY campuses.campus_name ASC, petty_cash_college_wise.id DESC",
			array($status)
		)->result_array();

		$rows = $this->_filter_by_ids($rows, 'id', 'petty_cash_users');

		$out = array();
		foreach ($rows as $row) {
			$live = $this->_petty_live_balance($row['id']);
			$row['live_balance'] = $live;
			$row['remaining_amount_db'] = (float)$row['remaining_amount'];
			$row['remaining_amount'] = $live;
			$row['require_amount'] = (float)$row['amount'] - (float)$row['remaining_amount_db'];
			$row['assign_to_name'] = trim(
				(isset($row['first_name']) ? $row['first_name'] : '') . ' ' .
				(isset($row['last_name']) ? $row['last_name'] : '')
			);
			$row['recovery_from_label'] = ((string)$row['recovery_from'] === '1') ? 'Cash in Hand' : 'Campus Recovery';
			$out[] = $row;
		}

		$activeCount = 0;
		$inactiveCount = 0;
		$counts = $this->db->query(
			'SELECT petty_status, COUNT(*) as cnt FROM petty_cash_college_wise GROUP BY petty_status'
		)->result_array();
		$allowed = $this->_access_id_list('petty_cash_users');
		if ($allowed === null) {
			foreach ($counts as $c) {
				if ((int)$c['petty_status'] === 1) $activeCount = (int)$c['cnt'];
				if ((int)$c['petty_status'] === 0) $inactiveCount = (int)$c['cnt'];
			}
		} else {
			if (count($allowed)) {
				$in = implode(',', array_map('intval', $allowed));
				$cRows = $this->db->query(
					"SELECT petty_status, COUNT(*) as cnt FROM petty_cash_college_wise
					 WHERE id IN ($in) GROUP BY petty_status"
				)->result_array();
				foreach ($cRows as $c) {
					if ((int)$c['petty_status'] === 1) $activeCount = (int)$c['cnt'];
					if ((int)$c['petty_status'] === 0) $inactiveCount = (int)$c['cnt'];
				}
			}
		}

		$this->_json(array(
			'success' => true,
			'status' => $status,
			'petty_cash' => $out,
			'active_count' => $activeCount,
			'inactive_count' => $inactiveCount,
			'can_add' => $this->_feature('add_pettycash'),
			'can_transfer' => $this->_feature('pettycash_funds_trasfer'),
			'can_change_rule' => $this->_feature('change_pettycash'),
		));
	}

	public function petty_cash_add()
	{
		if (!$this->_feature('add_pettycash') && !$this->_is_admin()) {
			$this->_json(array('success' => false, 'message' => 'No permission to add petty cash'), 403);
		}
		$b = $this->_body();
		$campus_id = isset($b['campus_id']) ? (int)$b['campus_id'] : 0;
		$recovery_from = isset($b['recovery_from']) ? $b['recovery_from'] : '';
		$user_id = isset($b['user_id']) ? (int)$b['user_id'] : 0;
		$amount = isset($b['amount']) ? (float)$b['amount'] : 0;
		$opening_balance = isset($b['opening_balance']) ? (float)$b['opening_balance'] : 0;

		if ($campus_id <= 0 || $user_id <= 0) {
			$this->_json(array('success' => false, 'message' => 'campus_id and user_id required'), 422);
		}

		$now = date('Y-m-d H:i:s');
		$given = date('Y-m-d') . ' 00:00:00';
		$this->db->query(
			"INSERT INTO petty_cash_college_wise
			 (campus_id, recovery_from, assign_to, amount, remaining_amount, opening_balance, petty_status, created_by, created_at, given_date)
			 VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, ?)",
			array(
				$campus_id,
				$recovery_from,
				$user_id,
				$amount,
				$opening_balance,
				$opening_balance,
				(int)$this->current_user['user_id'],
				$now,
				$given,
			)
		);

		$this->_json(array(
			'success' => true,
			'id' => (int)$this->db->insert_id(),
			'message' => 'Petty cash added',
		));
	}

	public function petty_cash_update_rule()
	{
		if (!$this->_feature('change_pettycash') && !$this->_is_admin()) {
			$this->_json(array('success' => false, 'message' => 'No permission to change petty cash rule'), 403);
		}
		$b = $this->_body();
		$amount = isset($b['amount']) ? (float)$b['amount'] : 0;
		$user_id = 0;
		if (isset($b['assign_to'])) $user_id = (int)$b['assign_to'];
		elseif (isset($b['user_id'])) $user_id = (int)$b['user_id'];
		$id = isset($b['id']) ? (int)$b['id'] : 0;

		if ($id > 0) {
			$this->db->query(
				'UPDATE petty_cash_college_wise SET amount = ? WHERE id = ?',
				array($amount, $id)
			);
		} elseif ($user_id > 0) {
			$this->db->query(
				'UPDATE petty_cash_college_wise SET amount = ? WHERE assign_to = ?',
				array($amount, $user_id)
			);
		} else {
			$this->_json(array('success' => false, 'message' => 'assign_to/user_id or id required'), 422);
		}

		$this->_json(array('success' => true, 'message' => 'Petty cash rule updated'));
	}

	public function petty_cash_set_status()
	{
		if (!$this->_feature('change_pettycash') && !$this->_is_admin()) {
			$this->_json(array('success' => false, 'message' => 'No permission to change petty status'), 403);
		}
		$b = $this->_body();
		$id = isset($b['id']) ? (int)$b['id'] : 0;
		$petty_status = isset($b['petty_status']) ? (int)$b['petty_status'] : -1;
		if ($id <= 0 || ($petty_status !== 0 && $petty_status !== 1)) {
			$this->_json(array('success' => false, 'message' => 'id and petty_status (0|1) required'), 422);
		}

		$petty = $this->db->query(
			'SELECT * FROM petty_cash_college_wise WHERE id = ? LIMIT 1',
			array($id)
		)->row_array();
		if (!$petty) {
			$this->_json(array('success' => false, 'message' => 'Petty cash not found'), 404);
		}

		if ($petty_status === 0) {
			$live = $this->_petty_live_balance($id);
			if ($live >= 1) {
				$this->_json(array(
					'success' => false,
					'message' => 'Cannot deactivate: remaining balance must be less than 1',
					'remaining_amount' => $live,
				), 422);
			}
		}

		$this->db->query(
			'UPDATE petty_cash_college_wise SET petty_status = ? WHERE id = ?',
			array($petty_status, $id)
		);

		$this->_json(array('success' => true, 'message' => 'Status updated'));
	}

	public function petty_cash_transfer()
	{
		if (!$this->_feature('pettycash_funds_trasfer') && !$this->_is_admin()) {
			$this->_json(array('success' => false, 'message' => 'No permission to transfer petty cash'), 403);
		}
		$b = $this->_body();
		$from_petty_id = isset($b['from_petty_id']) ? (int)$b['from_petty_id'] : (isset($b['from_account_funds']) ? (int)$b['from_account_funds'] : 0);
		$to_account_id = isset($b['to_account_id']) ? (int)$b['to_account_id'] : (isset($b['account_id']) ? (int)$b['account_id'] : 0);
		$to_petty_id = isset($b['to_petty_id']) ? (int)$b['to_petty_id'] : (isset($b['petty_account_id']) ? (int)$b['petty_account_id'] : 0);
		$amount = isset($b['amount']) ? (float)$b['amount'] : (isset($b['amount_transfer']) ? (float)$b['amount_transfer'] : 0);
		$reason = isset($b['reason']) ? trim($b['reason']) : (isset($b['trasfer_reason']) ? trim($b['trasfer_reason']) : '');

		if ($from_petty_id <= 0 || $amount <= 0) {
			$this->_json(array('success' => false, 'message' => 'from_petty_id and amount required'), 422);
		}
		if ($to_account_id <= 0 && $to_petty_id <= 0) {
			$this->_json(array('success' => false, 'message' => 'to_account_id or to_petty_id required'), 422);
		}

		$image = '';
		if (!empty($b['proof_image'])) {
			$image = basename((string)$b['proof_image']);
		} elseif (!empty($b['proof'])) {
			$image = basename((string)$b['proof']);
		} else {
			$image = $this->_upload_proof();
		}

		$actor = $this->_actor_name();
		$now = date('Y-m-d H:i:s');

		// Match Pettycash::funds_transfer — empty to_petty means transfer to cash account
		if ($to_petty_id <= 0) {
			$this->db->query(
				'UPDATE petty_cash_college_wise SET remaining_amount = remaining_amount - ? WHERE id = ?',
				array($amount, $from_petty_id)
			);
			$this->db->query(
				'UPDATE accounts SET amount = amount + ? WHERE id = ?',
				array($amount, $to_account_id)
			);
			$this->db->query(
				"INSERT INTO transactions_history
				 (from_pettycash_id, to_account_id, transaction_account_id, amount, debit_credit, transaction_by, reason, proof_image, created_at)
				 VALUES (?, ?, ?, ?, 'D', ?, ?, ?, ?)",
				array($from_petty_id, $to_account_id, $to_account_id, $amount, $actor, $reason, $image, $now)
			);
			$this->db->query(
				"INSERT INTO petty_cash_history
				 (debit_credit, amount_given, from_pettycash_id, to_account, transaction_pettycash_account, status, reason, proof_image, transaction_by, created_at)
				 VALUES ('C', ?, ?, ?, ?, '1', ?, ?, ?, ?)",
				array($amount, $from_petty_id, $to_account_id, $from_petty_id, $reason, $image, $actor, $now)
			);
		} else {
			$this->db->query(
				'UPDATE petty_cash_college_wise SET remaining_amount = remaining_amount + ? WHERE id = ?',
				array($amount, $to_petty_id)
			);
			$this->db->query(
				'UPDATE petty_cash_college_wise SET remaining_amount = remaining_amount - ? WHERE id = ?',
				array($amount, $from_petty_id)
			);
			$this->db->query(
				"INSERT INTO petty_cash_history
				 (debit_credit, amount_given, from_pettycash_id, to_pettycash_id, transaction_pettycash_account, status, reason, proof_image, transaction_by, created_at)
				 VALUES ('D', ?, ?, ?, ?, '1', ?, ?, ?, ?)",
				array($amount, $from_petty_id, $to_petty_id, $to_petty_id, $reason, $image, $actor, $now)
			);
			$this->db->query(
				"INSERT INTO petty_cash_history
				 (debit_credit, amount_given, from_pettycash_id, to_pettycash_id, transaction_pettycash_account, status, reason, proof_image, transaction_by, created_at)
				 VALUES ('C', ?, ?, ?, ?, '1', ?, ?, ?, ?)",
				array($amount, $from_petty_id, $to_petty_id, $from_petty_id, $reason, $image, $actor, $now)
			);
		}

		$this->_json(array('success' => true, 'message' => 'Petty cash transferred', 'proof_image' => $image));
	}

	public function petty_cash_statement()
	{
		$id = (int)$this->input->get('id');
		$from_date = $this->input->get('from_date');
		$to_date = $this->input->get('to_date');
		if ($id <= 0) {
			$this->_json(array('success' => false, 'message' => 'id required'), 422);
		}
		if (!$from_date) $from_date = date('Y-m-01');
		if (!$to_date) $to_date = date('Y-m-d');

		if (!$this->_is_admin() && !$this->_can_access_account_id($id, 'petty_cash_users')) {
			$this->_json(array('success' => false, 'message' => 'No access to this petty cash'), 403);
		}

		$check = $this->db->query(
			'SELECT * FROM petty_cash_college_wise WHERE id = ? LIMIT 1',
			array($id)
		)->row_array();
		if (!$check) {
			$this->_json(array('success' => false, 'message' => 'Petty cash not found'), 404);
		}

		$assignTo = (int)$check['assign_to'];
		$openbalance = (float)$check['opening_balance'];
		$givenDate = !empty($check['given_date']) ? $check['given_date'] : '1970-01-01';

		$expenseamount = $this->db->query(
			"SELECT SUM(amount) as amount FROM expenses
			 WHERE add_by_id = ?
			   AND actual_date >= ?
			   AND actual_date < ?
			   AND paid_type = 'cash'
			   AND expense_id NOT IN (SELECT expense_id FROM bank_reconciliation_statement WHERE expense_id IS NOT NULL)",
			array($assignTo, $givenDate, $from_date)
		)->row_array();

		$expensereverseamount = $this->db->query(
			"SELECT SUM(cash_reversal.amount) as amount
			 FROM cash_reversal
			 INNER JOIN expenses ON expenses.expense_id = cash_reversal.expense_id
			 WHERE expenses.add_by_id = ?
			   AND cash_reversal.created_at < ?",
			array($assignTo, $from_date . ' 00:00:00')
		)->row_array();

		$trans_before = $this->db->query(
			"SELECT id as trans_id, amount_given as amount, debit_credit, created_at
			 FROM petty_cash_history
			 WHERE transaction_pettycash_account = ? AND created_at < ?",
			array($id, $from_date)
		)->result_array();

		$debit = 0;
		$credit = 0;
		foreach ($trans_before as $tran) {
			if ($tran['debit_credit'] === 'C') $credit += (float)$tran['amount'];
			else $debit += (float)$tran['amount'];
		}

		$revBefore = (float)(isset($expensereverseamount['amount']) ? $expensereverseamount['amount'] : 0);
		$expBefore = (float)(isset($expenseamount['amount']) ? $expenseamount['amount'] : 0);
		$openbalance = ($openbalance + $debit + $revBefore) - $credit - $expBefore;

		$expenses = $this->db->query(
			"SELECT expenses.expense_id as trans_id, expenses.user_id as user_id,
					CONCAT(IFNULL(expense_category.name,''),' - ',IFNULL(expenses.title,''),' - ',IFNULL(expenses.purpose,''),' - ',IFNULL(campuses.campus_name,''),' - ',IFNULL(expenses.date,'')) as detail,
					'exp' as trans_type, expenses.amount as amount, 'C' as debit_credit,
					expenses.approved_status as expstatus, expenses.actual_date as created_at,
					'' as reason, expenses.image, expenses.add_by as trans_by
			 FROM expenses
			 LEFT JOIN expense_category ON expense_category.expense_category_id = expenses.expense_category_id
			 LEFT JOIN users ON users.user_id = expenses.add_by_id
			 LEFT JOIN campuses ON campuses.campus_id = expenses.campus_id
			 WHERE expenses.add_by_id = ?
			   AND expenses.actual_date >= ? AND expenses.actual_date <= ?
			   AND paid_type = 'cash'
			   AND expense_id NOT IN (SELECT expense_id FROM bank_reconciliation_statement WHERE expense_id IS NOT NULL)",
			array($assignTo, $from_date . ' 00:00:00', $to_date . ' 23:59:59')
		)->result_array();

		$reversals = $this->db->query(
			"SELECT cash_reversal.id as trans_id, expenses.user_id as user_id,
					CONCAT('Reversal against ',IFNULL(expense_category.name,''),' - ',IFNULL(expenses.title,''),' - ',IFNULL(expenses.purpose,''),' - ',IFNULL(campuses.campus_name,''),' - ',IFNULL(expenses.date,'')) as detail,
					'exp' as trans_type, cash_reversal.amount as amount, 'D' as debit_credit,
					'Reversal' as expstatus, cash_reversal.created_at as created_at,
					'Reversal against Expense' as reason, expenses.image, expenses.add_by as trans_by
			 FROM cash_reversal
			 LEFT JOIN expenses ON expenses.expense_id = cash_reversal.expense_id
			 LEFT JOIN expense_category ON expense_category.expense_category_id = expenses.expense_category_id
			 LEFT JOIN users ON users.user_id = expenses.add_by_id
			 LEFT JOIN campuses ON campuses.campus_id = expenses.campus_id
			 WHERE expenses.add_by_id = ?
			   AND cash_reversal.created_at >= ? AND cash_reversal.created_at <= ?",
			array($assignTo, date('Y-m-d', strtotime($from_date)) . ' 00:00:00', date('Y-m-d', strtotime($to_date)) . ' 23:59:59')
		)->result_array();

		$trans = $this->db->query(
			"SELECT id as trans_id, 'receive from' as detail, '0' as user_id, 'trans' as trans_type,
					amount_given as amount, '' as expstatus, debit_credit, created_at,
					proof_image as image, reason, transaction_by as trans_by
			 FROM petty_cash_history
			 WHERE transaction_pettycash_account = ?
			   AND created_at >= ? AND created_at <= ?",
			array($id, $from_date . ' 00:00:00', $to_date . ' 23:59:59')
		)->result_array();

		$merged = array_merge($expenses, $trans, $reversals);
		foreach ($merged as $key => $petty) {
			if (!empty($petty['user_id']) && (string)$petty['user_id'] !== '0') {
				$userdata = $this->db->query(
					'SELECT first_name, last_name FROM users WHERE user_id = ? LIMIT 1',
					array((int)$petty['user_id'])
				)->row_array();
				if ($userdata) {
					$merged[$key]['detail'] = $petty['detail'] . ' ' . $userdata['first_name'] . ' ' . $userdata['last_name'];
				}
			}
		}

		usort($merged, function ($a, $b) {
			$ta = strtotime($a['created_at']);
			$tb = strtotime($b['created_at']);
			if ($ta == $tb) return 0;
			return ($ta < $tb) ? -1 : 1;
		});

		$balance = $openbalance;
		$out = array();
		foreach ($merged as $row) {
			$amt = (float)$row['amount'];
			if ($row['debit_credit'] === 'D') $balance += $amt;
			else $balance -= $amt;
			$row['debit'] = $row['debit_credit'] === 'D' ? $amt : 0;
			$row['credit'] = $row['debit_credit'] === 'C' ? $amt : 0;
			$row['running_balance'] = $balance;
			$out[] = $row;
		}

		$this->_json(array(
			'success' => true,
			'petty' => $check,
			'from_date' => $from_date,
			'to_date' => $to_date,
			'opening_balance' => $openbalance,
			'closing_balance' => $balance,
			'rows' => $out,
		));
	}

	public function campuses()
	{
		$rows = $this->db->query(
			'SELECT campus_id, campus_name FROM campuses WHERE status = 1 ORDER BY campus_name ASC'
		)->result_array();
		$this->_json(array('success' => true, 'campuses' => $rows));
	}

	public function users()
	{
		$campus_id = (int)$this->input->get('campus_id');
		$sql = "SELECT user_id, first_name, last_name, campus_id, designation_id, department_id, status
				FROM users
				WHERE status = '1'";
		$params = array();
		if ($campus_id > 0) {
			$sql .= ' AND campus_id = ?';
			$params[] = $campus_id;
		}
		$sql .= ' ORDER BY first_name ASC, last_name ASC';
		$rows = $this->db->query($sql, $params)->result_array();
		foreach ($rows as &$r) {
			$r['name'] = trim($r['first_name'] . ' ' . $r['last_name']);
		}
		$this->_json(array('success' => true, 'users' => $rows));
	}

	/* ========================================================================
	 * Phase 0 dayops notes (documented, not a separate endpoint):
	 * - Daily closings: Closing::index / closenow / viewclosing / verify_closing_now
	 * - Closing persons: Closing::closing_person / add_closing_person
	 * - Day closing report: Reports::PettyCashReport (petty + campus closing snapshot)
	 * Phase 2 below ports those essentials for the Accounts SPA.
	 * ======================================================================== */

	private function _field_exists($table, $field)
	{
		return $this->_table_exists($table) && $this->db->field_exists($field, $table);
	}

	private function _closing_for_date($campus_id, $date, $lock = false)
	{
		$sql = "SELECT * FROM closing_perday
				WHERE campus_id = ?
				  AND for_day = ? AND for_month = ? AND for_year = ?";
		if ($lock) $sql .= ' FOR UPDATE';
		return $this->db->query($sql, array(
			(int)$campus_id,
			date('d', strtotime($date)),
			date('m', strtotime($date)),
			date('Y', strtotime($date)),
		))->row_array();
	}

	private function _pos_sales_sum($campus_id, $sold_date, $only_unclosed = true)
	{
		if (!$this->_table_exists('products') || !$this->_field_exists('products', 'sold_amount')) {
			return 0.0;
		}
		$sql = "SELECT COALESCE(SUM(sold_amount),0) AS total FROM products
				WHERE sold = 1 AND campus_id = ? AND sold_date = ?";
		$params = array((int)$campus_id, $sold_date);
		if ($only_unclosed && $this->_field_exists('products', 'closing_id')) {
			$sql .= " AND (closing_id IS NULL OR closing_id = '' OR closing_id = '0')";
		}
		$row = $this->db->query($sql, $params)->row_array();
		return (float)(isset($row['total']) ? $row['total'] : 0);
	}

	private function _pos_sales_rows($campus_id, $sold_date, $only_unclosed = true)
	{
		if (!$this->_table_exists('products')) return array();
		$sql = "SELECT products.*";
		if ($this->_table_exists('product_names')) {
			$sql .= ", product_names.product_name";
		}
		$sql .= " FROM products";
		if ($this->_table_exists('product_names')) {
			$sql .= " LEFT JOIN product_names ON product_names.product_name_id = products.product_name_id";
		}
		$sql .= " WHERE products.sold = 1 AND products.campus_id = ? AND products.sold_date = ?";
		$params = array((int)$campus_id, $sold_date);
		if ($only_unclosed && $this->_field_exists('products', 'closing_id')) {
			$sql .= " AND (products.closing_id IS NULL OR products.closing_id = '' OR products.closing_id = '0')";
		}
		return $this->db->query($sql, $params)->result_array();
	}

	private function _pos_sales_by_closing($campus_id, $campus_closing_id)
	{
		if (!$this->_table_exists('products') || !$this->_field_exists('products', 'closing_id')) {
			return array();
		}
		$sql = "SELECT products.*";
		if ($this->_table_exists('product_names')) {
			$sql .= ", product_names.product_name";
		}
		$sql .= " FROM products";
		if ($this->_table_exists('product_names')) {
			$sql .= " LEFT JOIN product_names ON product_names.product_name_id = products.product_name_id";
		}
		$sql .= " WHERE products.sold = 1 AND products.campus_id = ? AND products.closing_id = ?";
		return $this->db->query($sql, array((int)$campus_id, $campus_closing_id))->result_array();
	}

	private function _upload_url($filename)
	{
		if (!$filename) return null;
		if (preg_match('#^https?://#i', $filename)) return $filename;
		return base_url('uploads/' . ltrim($filename, '/'));
	}

	private function _closing_flags()
	{
		$is_admin = $this->_is_admin();
		$access = $this->_access();
		$can_cash = $is_admin || (!empty($access['dailyclosing']) && (string)$access['dailyclosing'] === '1');
		$can_bank = $is_admin || (!empty($access['dailybankclosing']) && (string)$access['dailybankclosing'] === '1');
		$can_conciliation_edit = $is_admin
			|| (!empty($access['closing_conciliation_edit']) && (string)$access['closing_conciliation_edit'] === '1');
		$can_closing_amount_edit = $is_admin
			|| (!empty($access['closing_amount_edit']) && (string)$access['closing_amount_edit'] === '1');
		return array(
			'is_admin' => $is_admin,
			'can_dailyclosing' => $can_cash,
			'can_dailybankclosing' => $can_bank,
			'can_conciliation_edit' => $can_conciliation_edit,
			'can_closing_amount_edit' => $can_closing_amount_edit,
		);
	}

	private function _bank_accounts_rows()
	{
		if (!$this->_table_exists('accounts')) return array();
		return $this->db->query(
			"SELECT id, account_name, account_title, amount, type
			 FROM accounts WHERE type = '1' OR type = 1
			 ORDER BY account_title ASC, account_name ASC"
		)->result_array();
	}

	/** Same SQL as Closing::index / accountsclosing side panels */
	private function _campus_last_closings()
	{
		if (!$this->_table_exists('closing_perday')) return array();
		$sql = 'SELECT closing_perday.campus_id, campus_name,
				(SELECT for_day FROM closing_perday WHERE campus_id = campuses.campus_id
					ORDER BY closing_perday.for_year DESC, closing_perday.for_month DESC, closing_perday.for_day DESC LIMIT 1) AS day,
				(SELECT for_month FROM closing_perday WHERE campus_id = campuses.campus_id
					ORDER BY closing_perday.for_year DESC, closing_perday.for_month DESC, closing_perday.for_day DESC LIMIT 1) AS month,
				MAX(for_year) AS year
				FROM closing_perday
				LEFT JOIN campuses ON campuses.campus_id = closing_perday.campus_id
				WHERE (SELECT COUNT(*) FROM closing_persons
					WHERE closing_persons.campus_id = closing_perday.campus_id
					  AND closing_persons.active_status = 1) > 0
				GROUP BY closing_perday.campus_id';
		return $this->db->query($sql)->result_array();
	}

	private function _campus_last_verified()
	{
		if (!$this->_table_exists('closing_perday')) return array();
		$sql = 'SELECT closing_perday.campus_id, campus_name,
				(SELECT for_day FROM closing_perday WHERE campus_id = campuses.campus_id AND checked_by = "1"
					ORDER BY closing_perday.for_year DESC, closing_perday.for_month DESC, closing_perday.for_day DESC LIMIT 1) AS day,
				(SELECT for_month FROM closing_perday WHERE campus_id = campuses.campus_id AND checked_by = "1"
					ORDER BY closing_perday.for_year DESC, closing_perday.for_month DESC, closing_perday.for_day DESC LIMIT 1) AS month,
				(SELECT for_year FROM closing_perday WHERE campus_id = campuses.campus_id AND checked_by = "1"
					ORDER BY closing_perday.for_year DESC, closing_perday.for_month DESC, closing_perday.for_day DESC LIMIT 1) AS year
				FROM closing_perday
				LEFT JOIN campuses ON campuses.campus_id = closing_perday.campus_id
				WHERE (SELECT COUNT(*) FROM closing_persons
					WHERE closing_persons.campus_id = closing_perday.campus_id
					  AND closing_persons.active_status = 1) > 0
				GROUP BY closing_perday.campus_id';
		return $this->db->query($sql)->result_array();
	}

	private function _ymd_from_parts($row)
	{
		if (!$row || empty($row['year']) || empty($row['month']) || empty($row['day'])) return null;
		return sprintf('%04d-%02d-%02d', (int)$row['year'], (int)$row['month'], (int)$row['day']);
	}

	private function _date_picker_min($verified_rows)
	{
		if ($this->_is_admin()) return null;
		$min = null;
		foreach ($verified_rows as $row) {
			$d = $this->_ymd_from_parts($row);
			if (!$d) continue;
			if ($min === null || $d < $min) $min = $d;
		}
		return $min;
	}

	private function _is_checked($val)
	{
		return $val !== null && $val !== '' && (string)$val !== '0' && strtoupper((string)$val) !== 'NULL';
	}

	private function _closing_status_label($closed, $close_type, $transaction_no)
	{
		if (!$closed) return 'OPEN';
		if ((string)$close_type !== '2' && ($transaction_no === null || $transaction_no === '')) {
			return 'PARTIALLY_CLOSED';
		}
		return 'CLOSED';
	}

	private function _accounts_status_label($closed, $checked_by)
	{
		if (!$closed) return null;
		return $this->_is_checked($checked_by) ? 'Verified' : 'UnVerified';
	}

	private function _next_verify_date($campus_id, $verified_rows)
	{
		foreach ($verified_rows as $row) {
			if ((int)$row['campus_id'] === (int)$campus_id) {
				$d = $this->_ymd_from_parts($row);
				if (!$d) return null;
				return date('Y-m-d', strtotime($d . ' +1 day'));
			}
		}
		return null;
	}

	/** Legacy closingsheet: close only when selected_date === last_closing + 1 day */
	private function _next_close_date($campus_id, $last_closings)
	{
		foreach ($last_closings as $row) {
			if ((int)$row['campus_id'] === (int)$campus_id) {
				$d = $this->_ymd_from_parts($row);
				if (!$d) return null;
				return date('Y-m-d', strtotime($d . ' +1 day'));
			}
		}
		return null;
	}

	private function _assert_sequential_verify($campus_id, $closing_date, $verified_rows)
	{
		$next = $this->_next_verify_date((int)$campus_id, $verified_rows);
		if ($next !== null && $closing_date !== $next) {
			$this->_json(array(
				'success' => false,
				'message' => 'Verify sequentially: next day after last verification is ' . $next,
			), 400);
		}
	}

	private function _closing_ymd_from_row($row)
	{
		return sprintf(
			'%04d-%02d-%02d',
			(int)$row['for_year'],
			(int)$row['for_month'],
			(int)$row['for_day']
		);
	}

	private function _user_can_close_campus($campus_id)
	{
		$persons = $this->_closing_persons_rows(true);
		foreach ($persons as $p) {
			if ((int)$p['campus_id'] === (int)$campus_id) return true;
		}
		return false;
	}

	/** Collect unclosed fee/asset/loan/pos lines + totals for a campus/date (Closing::index / viewclosing). */
	private function _campus_closing_bundle($campus_id, $date)
	{
		$campus_id = (int)$campus_id;
		$yesterday = date('Y-m-d', strtotime($date . ' -1 day'));
		$yest_closed = $this->_closing_for_date($campus_id, $yesterday);
		$fee_ids = array();
		$sale_ids = array();
		$loan_ids = array();
		$fee_amount = 0.0;
		$asset_amount = 0.0;
		$sale_amount = 0.0;
		$loan_amount = 0.0;
		$pos_amount = 0.0;
		$fee_lines = array();
		$asset_lines = array();
		$loan_lines = array();
		$pos_lines = array();

		// Fee payments (college) — today + yesterday orphans if yesterday was closed
		$dates = array($date);
		if ($yest_closed) $dates[] = $yesterday;

		foreach ($dates as $d) {
			$only_unclosed = ($d === $yesterday);
			$feeSelect = "SELECT payments.*, students.first_name, students.last_name, students.roll_no
				 FROM payments
				 LEFT JOIN students ON students.student_id = payments.student_id
				 WHERE submitted_fee_campus_id = ?
				   AND merged_challan IS NOT NULL AND actual_amount > 0
				   AND fee_pay_through = 'college'
				   AND actual_paid_date = ?"
				 . ($only_unclosed ? " AND closing_id IS NULL" : "")
				 . " GROUP BY CASE WHEN merged_challan IS NOT NULL THEN merged_challan ELSE '' END";
			$merged = $this->db->query($feeSelect, array($campus_id, $d))->result_array();
			$single = $this->db->query(
				"SELECT payments.*, students.first_name, students.last_name, students.roll_no
				 FROM payments
				 LEFT JOIN students ON students.student_id = payments.student_id
				 WHERE submitted_fee_campus_id = ?
				   AND merged_challan IS NULL
				   AND fee_pay_through = 'college'
				   AND actual_paid_date = ?
				   AND paid = 1"
				 . ($only_unclosed ? " AND closing_id IS NULL" : ""),
				array($campus_id, $d)
			)->result_array();
			foreach (array_merge($merged, $single) as $p) {
				$fee_ids[] = (int)$p['id'];
				$fee_amount += (float)$p['actual_amount'];
				$fee_lines[] = $p;
			}
		}

		if ($this->_table_exists('asset_sales')) {
			foreach ($dates as $d) {
				$only_unclosed = ($d === $yesterday);
				$sql = "SELECT asset_sales.* FROM asset_sales
						INNER JOIN products ON products.product_id = asset_sales.product_id
						WHERE products.campus_id = ?
						  AND asset_sales.sold_date >= ? AND asset_sales.sold_date <= ?";
				$params = array($campus_id, $d . ' 00:00:00', $d . ' 23:59:59');
				if ($only_unclosed && $this->_field_exists('asset_sales', 'closing_id')) {
					$sql .= ' AND asset_sales.closing_id IS NULL';
				}
				$rows = $this->db->query($sql, $params)->result_array();
				foreach ($rows as $r) {
					$sale_ids[] = (int)$r['id'];
					$asset_amount += (float)$r['sale_amount'];
					$asset_lines[] = $r;
				}
			}
		}

		if ($this->_table_exists('sales') && $this->_table_exists('sales_payments')) {
			foreach ($dates as $d) {
				$row = $this->db->query(
					"SELECT COALESCE(SUM(sales_payments.payment_amount),0) AS total
					 FROM sales
					 LEFT JOIN sales_payments ON sales_payments.sale_id = sales.sale_id
					 WHERE sales.campus_id = ?
					   AND sales.sale_time >= ? AND sales.sale_time <= ?",
					array($campus_id, $d . ' 00:00:00', $d . ' 23:59:59')
				)->row_array();
				$sale_amount += (float)(isset($row['total']) ? $row['total'] : 0);
			}
		}

		if ($this->_table_exists('loan_plan')) {
			foreach ($dates as $d) {
				$only_unclosed = ($d === $yesterday);
				$sql = "SELECT loan_plan.*";
				if ($this->_table_exists('loans') && $this->_table_exists('users')) {
					$sql .= ", users.first_name, users.last_name, loan_plan.id AS id
							 FROM loan_plan
							 LEFT JOIN loans ON loans.id = loan_plan.loan_id
							 LEFT JOIN users ON users.user_id = loans.user_id
							 WHERE loan_plan.paid_date = ? AND loan_plan.campus_id = ? AND loan_plan.paid_at = 'cash'";
				} else {
					$sql .= " FROM loan_plan WHERE paid_date = ? AND campus_id = ? AND paid_at = 'cash'";
				}
				$params = array($d, $campus_id);
				if ($only_unclosed && $this->_field_exists('loan_plan', 'closing_id')) {
					$sql .= ' AND loan_plan.closing_id IS NULL';
				}
				$rows = $this->db->query($sql, $params)->result_array();
				foreach ($rows as $r) {
					$loan_ids[] = (int)$r['id'];
					$loan_amount += (float)$r['amount_paid'];
					$loan_lines[] = $r;
				}
			}
		}

		$pos_amount = $this->_pos_sales_sum($campus_id, $date, true);
		$pos_lines = $this->_pos_sales_rows($campus_id, $date, true);
		if ($yest_closed) {
			$pos_amount += $this->_pos_sales_sum($campus_id, $yesterday, true);
			$pos_lines = array_merge($pos_lines, $this->_pos_sales_rows($campus_id, $yesterday, true));
		}

		$total = $fee_amount + $sale_amount + $asset_amount + $loan_amount + $pos_amount;
		return array(
			'total' => $total,
			'fee_amount' => $fee_amount,
			'sale_amount' => $sale_amount,
			'asset_amount' => $asset_amount,
			'loan_amount' => $loan_amount,
			'pos_amount' => $pos_amount,
			'fee_ids' => array_values(array_unique($fee_ids)),
			'sale_ids' => array_values(array_unique($sale_ids)),
			'loan_ids' => array_values(array_unique($loan_ids)),
			'fee_lines' => $fee_lines,
			'asset_lines' => $asset_lines,
			'loan_lines' => $loan_lines,
			'pos_lines' => $pos_lines,
			'yest_closed' => (bool)$yest_closed,
		);
	}

	private function _next_campus_closing_id($campus_id)
	{
		$row = $this->db->query(
			"SELECT CONCAT(LEFT(`campus_closing_id`,3),
				MAX(CAST(SUBSTRING(`campus_closing_id`, 4, LENGTH(`campus_closing_id`)-2) AS UNSIGNED))+1
			) AS closing_id
			 FROM closing_perday WHERE campus_id = ?",
			array((int)$campus_id)
		)->row_array();
		if ($row && !empty($row['closing_id']) && $row['closing_id'] !== null) {
			return $row['closing_id'];
		}
		$prefix = str_pad((string)$campus_id, 3, '0', STR_PAD_LEFT);
		return $prefix . '1';
	}

	private function _closing_persons_rows($active_only = true)
	{
		if (!$this->_table_exists('closing_persons')) return array();
		$sql = "SELECT closing_persons.*, campuses.campus_name,
					users.first_name, users.last_name,
					CONCAT(COALESCE(users.first_name,''),' ',COALESCE(users.last_name,'')) AS person_name,
					closing_persons.campus_id AS campus_id
				FROM closing_persons
				LEFT JOIN campuses ON campuses.campus_id = closing_persons.campus_id
				LEFT JOIN users ON users.user_id = closing_persons.user_id
				WHERE 1=1";
		$params = array();
		if ($active_only) {
			$sql .= ' AND closing_persons.active_status = 1';
		}
		if (!$this->_is_admin()) {
			$uid = (int)$this->current_user['user_id'];
			$access = $this->_access();
			if (!empty($access['view_campus_closings']) && (string)$access['view_campus_closings'] === '1'
				&& !empty($access['campus_closing_ids'])) {
				$ids = array_filter(array_map('trim', explode(',', $access['campus_closing_ids'])));
				if ($ids) {
					$sql .= ' AND closing_persons.id IN (' . implode(',', array_map('intval', $ids)) . ')';
				} else {
					$sql .= ' AND 1=0';
				}
			} else {
				$sql .= ' AND closing_persons.user_id = ?';
				$params[] = $uid;
			}
		}
		$sql .= ' ORDER BY campuses.campus_name ASC';
		return $this->db->query($sql, $params)->result_array();
	}

	/**
	 * Legacy: Closing::index
	 * GET closings?date=YYYY-MM-DD
	 */
	public function closings()
	{
		$date = $this->input->get('date');
		if (!$date) $date = date('Y-m-d');
		$flags = $this->_closing_flags();
		$persons = $this->_closing_persons_rows(true);
		$campus_last_closings = $this->_campus_last_closings();
		$campus_last_verified = $this->_campus_last_verified();
		$bank_accounts = $this->_bank_accounts_rows();
		$out = array();

		foreach ($persons as $row) {
			$campus_id = (int)$row['campus_id'];
			$closed = $this->_closing_for_date($campus_id, $date);
			$person_name = trim(isset($row['person_name']) ? $row['person_name'] : '');
			$next_close = $this->_next_close_date($campus_id, $campus_last_closings);
			// Legacy: close buttons only when date is exactly last_closing+1 (or first close ever)
			$sequential_close_ok = ($next_close === null || $date === $next_close);
			$item = array(
				'person_id' => isset($row['id']) ? (int)$row['id'] : 0,
				'campus_id' => $campus_id,
				'campus_name' => isset($row['campus_name']) ? $row['campus_name'] : '',
				'user_id' => isset($row['user_id']) ? (int)$row['user_id'] : 0,
				'person_name' => $person_name,
				'date' => $date,
				'next_close_date' => $next_close,
				'can_close_sequential' => $sequential_close_ok,
			);

			if ($closed) {
				$close_type = isset($closed['close_type']) ? (string)$closed['close_type'] : '0';
				$txn = isset($closed['transaction_no']) ? $closed['transaction_no'] : null;
				$img = isset($closed['partialy_closed_image']) ? $closed['partialy_closed_image'] : '';
				$checked = isset($closed['checked_by']) ? $closed['checked_by'] : null;
				$unchecked = !$this->_is_checked($checked);
				$item['closed'] = true;
				$item['closed_status'] = '1';
				$item['closing_id'] = (int)$closed['id'];
				$item['campus_closing_id'] = $closed['campus_closing_id'];
				$item['closing_amount'] = (float)$closed['closed_amount'];
				$item['received_amount'] = isset($closed['receivable_amount'])
					? (float)$closed['receivable_amount']
					: (float)$closed['closed_amount'];
				$item['close_type'] = $close_type;
				$item['closed_by'] = $closed['closed_by'];
				$item['checked_by'] = $checked;
				$item['account_id'] = $closed['account_id'];
				$item['transaction_no'] = $txn;
				$item['partialy_closed_image'] = $img;
				$item['image_url'] = $this->_upload_url($img);
				$item['status_label'] = $this->_closing_status_label(true, $close_type, $txn);
				$item['accounts_status'] = $this->_accounts_status_label(true, $checked);
				$item['breakdown'] = null;
				$item['can_close_cash'] = false;
				$item['can_close_bank'] = false;
				$item['can_add_bank_details'] = ($close_type !== '2' && ($txn === null || $txn === ''));
				$item['can_edit_bank_details'] = ($close_type !== '2' && $txn !== null && $txn !== '' && $unchecked);
				// Legacy closingdetails updatenow — convert type while unverified
				$item['can_update_to_cash'] = $unchecked && !empty($flags['can_dailyclosing']) && $close_type !== '2';
				$item['can_update_to_bank'] = $unchecked && !empty($flags['can_dailybankclosing']) && $close_type !== '1';
				$item['can_update_to_paypro'] = $unchecked && (
					(!empty($flags['can_dailyclosing']) && $close_type === '1')
					|| (!empty($flags['can_dailybankclosing']) && ($close_type === '2' || $close_type === '3'))
				);
			} else {
				$bundle = $this->_campus_closing_bundle($campus_id, $date);
				$item['closed'] = false;
				$item['closed_status'] = '0';
				$item['closing_id'] = null;
				$item['campus_closing_id'] = null;
				$item['closing_amount'] = $bundle['total'];
				$item['received_amount'] = $bundle['total'];
				$item['breakdown'] = array(
					'fees' => $bundle['fee_amount'],
					'sales' => $bundle['sale_amount'],
					'asset_sales' => $bundle['asset_amount'],
					'loans' => $bundle['loan_amount'],
					'pos' => $bundle['pos_amount'],
				);
				$item['close_type'] = '0';
				$item['closed_by'] = '';
				$item['checked_by'] = '';
				$item['account_id'] = null;
				$item['transaction_no'] = null;
				$item['partialy_closed_image'] = '';
				$item['image_url'] = null;
				$item['status_label'] = 'OPEN';
				$item['accounts_status'] = null;
				$item['can_close_cash'] = $sequential_close_ok && !empty($flags['can_dailyclosing']);
				$item['can_close_bank'] = $sequential_close_ok && !empty($flags['can_dailybankclosing']);
				$item['can_add_bank_details'] = false;
				$item['can_edit_bank_details'] = false;
				$item['can_update_to_cash'] = false;
				$item['can_update_to_bank'] = false;
				$item['can_update_to_paypro'] = false;
			}
			$out[] = $item;
		}

		$this->_json(array(
			'success' => true,
			'date' => $date,
			'closings' => $out,
			'campus_last_closings' => $campus_last_closings,
			'campus_last_verified' => $campus_last_verified,
			'date_picker_min' => $this->_date_picker_min($campus_last_verified),
			'bank_accounts' => $bank_accounts,
			'flags' => $flags,
		));
	}

	/**
	 * Legacy: Closing::closenow
	 * POST close_day — body campus_id, date, close_type (1|2|3), receivable_amount?, fee_ids?, sale_ids?, loan_ids?
	 */
	public function close_day()
	{
		$body = $this->_body();
		$campus_id = (int)(isset($body['campus_id']) ? $body['campus_id'] : 0);
		$date = isset($body['date']) ? $body['date'] : date('Y-m-d');
		$close_type = isset($body['close_type']) ? (string)$body['close_type'] : '2';
		if ($campus_id <= 0) $this->_json(array('success' => false, 'message' => 'campus_id required'), 400);
		if (!in_array($close_type, array('1', '2', '3'), true)) {
			$this->_json(array('success' => false, 'message' => 'close_type must be 1 (Bank), 2 (Cash), or 3 (PayPro)'), 400);
		}
		if (!$this->_user_can_close_campus($campus_id)) {
			$this->_json(array('success' => false, 'message' => 'No closing access for this campus'), 403);
		}

		$flags = $this->_closing_flags();
		if ($close_type === '2' && empty($flags['can_dailyclosing'])) {
			$this->_json(array('success' => false, 'message' => 'No permission for cash closing'), 403);
		}
		if (($close_type === '1' || $close_type === '3') && empty($flags['can_dailybankclosing'])) {
			$this->_json(array('success' => false, 'message' => 'No permission for bank/PayPro closing'), 403);
		}

		$next_close = $this->_next_close_date($campus_id, $this->_campus_last_closings());
		if ($next_close !== null && $date !== $next_close) {
			$this->_json(array(
				'success' => false,
				'message' => 'Must close sequentially: next closing day for this campus is ' . $next_close,
			), 400);
		}

		$this->db->trans_start();
		$existing = $this->_closing_for_date($campus_id, $date, true);
		if ($existing) {
			$this->db->trans_complete();
			$this->_json(array(
				'success' => false,
				'message' => 'Closing already exists for this campus/date',
				'closing_id' => (int)$existing['id'],
			), 409);
		}

		$bundle = $this->_campus_closing_bundle($campus_id, $date);
		$total = isset($body['receivable_amount']) ? (float)$body['receivable_amount'] : $bundle['total'];
		$fee_ids = !empty($body['fee_ids']) ? $body['fee_ids'] : $bundle['fee_ids'];
		$sale_ids = !empty($body['sale_ids']) ? $body['sale_ids'] : $bundle['sale_ids'];
		$loan_ids = !empty($body['loan_ids']) ? $body['loan_ids'] : $bundle['loan_ids'];
		if (!is_array($fee_ids)) $fee_ids = array_filter(explode(',', (string)$fee_ids));
		if (!is_array($sale_ids)) $sale_ids = array_filter(explode(',', (string)$sale_ids));
		if (!is_array($loan_ids)) $loan_ids = array_filter(explode(',', (string)$loan_ids));
		$fee_ids = array_map('intval', $fee_ids);
		$sale_ids = array_map('intval', $sale_ids);
		$loan_ids = array_map('intval', $loan_ids);

		$day = date('d', strtotime($date));
		$month = date('m', strtotime($date));
		$year = date('Y', strtotime($date));
		$campus_closing_id = $this->_next_campus_closing_id($campus_id);

		if (count($fee_ids)) {
			$this->db->where_in('id', $fee_ids)->set('closing_id', $campus_closing_id)->update('payments');
			$merged = $this->db->query(
				"SELECT * FROM payments WHERE closing_id = ? AND merged_challan IS NOT NULL",
				array($campus_closing_id)
			)->result_array();
			foreach ($merged as $payment) {
				$this->db->set('closing_id', '0');
				$this->db->where('merged_challan', $payment['merged_challan']);
				$this->db->where('closing_id IS NULL', null, false);
				$this->db->update('payments');
			}
		}
		if (count($sale_ids) && $this->_table_exists('asset_sales')) {
			$this->db->where_in('id', $sale_ids)->set('closing_id', $campus_closing_id)->update('asset_sales');
		}
		if (count($loan_ids) && $this->_table_exists('loan_plan')) {
			$this->db->where_in('id', $loan_ids)->set('closing_id', $campus_closing_id)->update('loan_plan');
		}
		if ($this->_table_exists('products') && $this->_field_exists('products', 'closing_id')) {
			$yesterday = date('Y-m-d', strtotime($date . ' -1 day'));
			$pos_dates = array($date);
			if ($bundle['yest_closed']) $pos_dates[] = $yesterday;
			$this->db->set('closing_id', $campus_closing_id);
			$this->db->where('sold', 1);
			$this->db->where('campus_id', $campus_id);
			$this->db->where_in('sold_date', $pos_dates);
			$this->db->group_start();
			$this->db->where('closing_id IS NULL', null, false);
			$this->db->or_where('closing_id', '');
			$this->db->or_where('closing_id', '0');
			$this->db->group_end();
			$this->db->update('products');
		}

		$this->db->insert('closing_perday', array(
			'campus_id' => $campus_id,
			'for_day' => $day,
			'for_month' => $month,
			'for_year' => $year,
			'campus_closing_id' => $campus_closing_id,
			'closed_amount' => $total,
			'receivable_amount' => $total,
			'close_type' => $close_type,
			'closed_by' => $this->_actor_name(),
		));
		$inserted = $this->db->insert_id();
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->_json(array('success' => false, 'message' => 'Closing could not be saved'), 500);
		}
		$this->_json(array(
			'success' => true,
			'message' => 'Day closed',
			'closing_id' => (int)$inserted,
			'campus_closing_id' => $campus_closing_id,
			'close_type' => $close_type,
			'closed_amount' => $total,
		));
	}

	/**
	 * Legacy: Closing::add_closing_details
	 * POST add_closing_details — closing_id, account_id, transaction_no; proof_image filename OR multipart image
	 */
	public function add_closing_details()
	{
		$body = $this->_body();
		$closing_id = (int)(isset($body['closing_id']) ? $body['closing_id'] : (isset($body['closingid']) ? $body['closingid'] : 0));
		$account_id = (int)(isset($body['account_id']) ? $body['account_id'] : 0);
		$trans_no = isset($body['transaction_no'])
			? $body['transaction_no']
			: (isset($body['trans_id']) ? $body['trans_id'] : '');
		if ($closing_id <= 0) $this->_json(array('success' => false, 'message' => 'closing_id required'), 400);
		if ($account_id <= 0) $this->_json(array('success' => false, 'message' => 'account_id required'), 400);
		if ($trans_no === '' || $trans_no === null) {
			$this->_json(array('success' => false, 'message' => 'transaction_no required'), 400);
		}

		$row = $this->db->query('SELECT * FROM closing_perday WHERE id = ? LIMIT 1', array($closing_id))->row_array();
		if (!$row) $this->_json(array('success' => false, 'message' => 'Closing not found'), 404);
		if ((string)$row['close_type'] === '2') {
			$this->_json(array('success' => false, 'message' => 'Cash closings do not need bank details'), 400);
		}

		$image = $this->_upload_proof();
		if ($image === '' && !empty($body['proof_image'])) $image = $body['proof_image'];
		if ($image === '' && !empty($body['partialy_closed_image'])) $image = $body['partialy_closed_image'];

		$update = array(
			'account_id' => $account_id,
			'transaction_no' => $trans_no,
		);
		if ($image !== '') $update['partialy_closed_image'] = $image;
		$this->db->where('id', $closing_id)->update('closing_perday', $update);

		$this->_json(array(
			'success' => true,
			'message' => 'Closing bank details saved',
			'closing_id' => $closing_id,
			'partialy_closed_image' => $image !== '' ? $image : (isset($row['partialy_closed_image']) ? $row['partialy_closed_image'] : ''),
			'image_url' => $this->_upload_url($image !== '' ? $image : (isset($row['partialy_closed_image']) ? $row['partialy_closed_image'] : '')),
		));
	}

	/**
	 * Legacy: Closing::updatenow — change close_type while unverified
	 * POST update_closing_type — closing_id, close_type (1|2|3)
	 */
	public function update_closing_type()
	{
		$body = $this->_body();
		$closing_id = (int)(isset($body['closing_id']) ? $body['closing_id'] : 0);
		$close_type = isset($body['close_type']) ? (string)$body['close_type'] : '';
		if ($closing_id <= 0) $this->_json(array('success' => false, 'message' => 'closing_id required'), 400);
		if (!in_array($close_type, array('1', '2', '3'), true)) {
			$this->_json(array('success' => false, 'message' => 'close_type must be 1|2|3'), 400);
		}

		$row = $this->db->query('SELECT * FROM closing_perday WHERE id = ? LIMIT 1', array($closing_id))->row_array();
		if (!$row) $this->_json(array('success' => false, 'message' => 'Closing not found'), 404);
		if ($this->_is_checked(isset($row['checked_by']) ? $row['checked_by'] : null)) {
			$this->_json(array('success' => false, 'message' => 'Cannot change type of a verified closing'), 400);
		}
		if (!$this->_user_can_close_campus((int)$row['campus_id'])) {
			$this->_json(array('success' => false, 'message' => 'No closing access for this campus'), 403);
		}

		$flags = $this->_closing_flags();
		if ($close_type === '2' && empty($flags['can_dailyclosing'])) {
			$this->_json(array('success' => false, 'message' => 'No permission for cash closing'), 403);
		}
		if (($close_type === '1' || $close_type === '3') && empty($flags['can_dailybankclosing']) && empty($flags['can_dailyclosing'])) {
			$this->_json(array('success' => false, 'message' => 'No permission to update closing type'), 403);
		}

		$update = array('close_type' => $close_type);
		if ($close_type === '2') {
			$update['partialy_closed_image'] = null;
			$update['transaction_no'] = null;
			$update['account_id'] = null;
		}
		$this->db->where('id', $closing_id)->update('closing_perday', $update);

		$this->_json(array(
			'success' => true,
			'message' => 'Closing type updated',
			'closing_id' => $closing_id,
			'close_type' => $close_type,
		));
	}

	/**
	 * Legacy: Closing::viewclosing
	 * GET closing_detail?campus_id&date
	 */
	public function closing_detail()
	{
		$campus_id = (int)$this->input->get('campus_id');
		$date = $this->input->get('date');
		if (!$date) $date = date('Y-m-d');
		if ($campus_id <= 0) $this->_json(array('success' => false, 'message' => 'campus_id required'), 400);

		$closed = $this->_closing_for_date($campus_id, $date);
		if ($closed) {
			$ccid = $closed['campus_closing_id'];
			$fees = $this->db->query(
				"SELECT payments.*, students.first_name, students.last_name, students.roll_no
				 FROM payments
				 LEFT JOIN students ON students.student_id = payments.student_id
				 WHERE closing_id = ?",
				array($ccid)
			)->result_array();
			$assets = array();
			$asset_amount = 0.0;
			if ($this->_table_exists('asset_sales')) {
				$assets = $this->db->query(
					"SELECT asset_sales.* FROM asset_sales WHERE closing_id = ?",
					array($ccid)
				)->result_array();
				foreach ($assets as $a) $asset_amount += (float)$a['sale_amount'];
			}
			$loans = array();
			$loan_amount = 0.0;
			if ($this->_table_exists('loan_plan')) {
				$loans = $this->db->query(
					"SELECT loan_plan.* FROM loan_plan WHERE closing_id = ?",
					array($ccid)
				)->result_array();
				foreach ($loans as $l) $loan_amount += (float)$l['amount_paid'];
			}
			$pos = $this->_pos_sales_by_closing($campus_id, $ccid);
			$pos_amount = 0.0;
			foreach ($pos as $p) {
				$pos_amount += isset($p['sold_amount']) ? (float)$p['sold_amount'] : 0;
			}
			$fee_amount = 0.0;
			$fee_ids = array();
			foreach ($fees as $f) {
				$fee_amount += (float)$f['actual_amount'];
				$fee_ids[] = (int)$f['id'];
			}
			$sale_amount = 0.0;
			if ($this->_table_exists('sales') && $this->_table_exists('sales_payments')
				&& $this->_field_exists('sales', 'closing_id')) {
				$sale_row = $this->db->query(
					"SELECT COALESCE(SUM(sales_payments.payment_amount),0) AS total
					 FROM sales
					 LEFT JOIN sales_payments ON sales_payments.sale_id = sales.sale_id
					 WHERE sales.closing_id = ? AND sales.campus_id = ?",
					array($ccid, $campus_id)
				)->row_array();
				$sale_amount = (float)(isset($sale_row['total']) ? $sale_row['total'] : 0);
			}
			$sale_ids = array();
			foreach ($assets as $a) $sale_ids[] = (int)$a['id'];
			$loan_ids = array();
			foreach ($loans as $l) $loan_ids[] = (int)$l['id'];

			$img = isset($closed['partialy_closed_image']) ? $closed['partialy_closed_image'] : '';
			$closed['image_url'] = $this->_upload_url($img);

			$this->_json(array(
				'success' => true,
				'closed' => true,
				'date' => $date,
				'campus_id' => $campus_id,
				'closing' => $closed,
				'fees' => $fees,
				'asset_sales' => $assets,
				'loans' => $loans,
				'pos' => $pos,
				'fee_ids' => $fee_ids,
				'sale_ids' => $sale_ids,
				'loan_ids' => $loan_ids,
				'breakdown' => array(
					'fees' => $fee_amount,
					'sales' => $sale_amount,
					'asset_sales' => $asset_amount,
					'loans' => $loan_amount,
					'pos' => $pos_amount,
				),
				'total' => (float)$closed['closed_amount'],
			));
		}

		$bundle = $this->_campus_closing_bundle($campus_id, $date);
		$this->_json(array(
			'success' => true,
			'closed' => false,
			'date' => $date,
			'campus_id' => $campus_id,
			'closing' => null,
			'fees' => $bundle['fee_lines'],
			'asset_sales' => $bundle['asset_lines'],
			'loans' => $bundle['loan_lines'],
			'pos' => $bundle['pos_lines'],
			'breakdown' => array(
				'fees' => $bundle['fee_amount'],
				'sales' => $bundle['sale_amount'],
				'asset_sales' => $bundle['asset_amount'],
				'loans' => $bundle['loan_amount'],
				'pos' => $bundle['pos_amount'],
			),
			'total' => $bundle['total'],
			'fee_ids' => $bundle['fee_ids'],
			'sale_ids' => $bundle['sale_ids'],
			'loan_ids' => $bundle['loan_ids'],
		));
	}

	/**
	 * Legacy: Closing::accountsclosing
	 * GET closings_conciliation?from_date&to_date&campus_id&tag_type=
	 * tag_type empty/2 = ALL; tag_type=0 = Bank UNTagged
	 */
	public function closings_conciliation()
	{
		$date = $this->input->get('date');
		if (!$date) $date = date('Y-m-d');
		$from = $this->input->get('from_date');
		$to = $this->input->get('to_date');
		if (!$from) $from = $date;
		if (!$to) $to = $date;
		$campus_id = $this->input->get('campus_id');
		$tag_type = $this->input->get('tag_type');
		if ($tag_type === null || $tag_type === '') $tag_type = '2';

		$flags = $this->_closing_flags();
		$campus_last_closings = $this->_campus_last_closings();
		$campus_last_verified = $this->_campus_last_verified();
		$bank_accounts = $this->_bank_accounts_rows();

		$campuses = array();
		if ($this->_table_exists('closing_persons')) {
			$campuses = $this->db->query(
				"SELECT campuses.campus_id, campuses.campus_name
				 FROM closing_persons
				 JOIN campuses ON campuses.campus_id = closing_persons.campus_id
				 GROUP BY closing_persons.campus_id
				 ORDER BY campuses.campus_name ASC"
			)->result_array();
		}

		$sql = "SELECT closing_perday.*, campuses.campus_name,
					bank_reconciliation_statement.credit AS brs_credit,
					bank_reconciliation_statement.description AS brs_description,
					bank_reconciliation_statement.id AS brs_id
				FROM closing_perday
				LEFT JOIN campuses ON campuses.campus_id = closing_perday.campus_id
				LEFT JOIN bank_reconciliation_statement
					ON bank_reconciliation_statement.closing_id = closing_perday.id
				WHERE STR_TO_DATE(CONCAT(closing_perday.for_year,'-',closing_perday.for_month,'-',closing_perday.for_day), '%Y-%m-%d') >= ?
				  AND STR_TO_DATE(CONCAT(closing_perday.for_year,'-',closing_perday.for_month,'-',closing_perday.for_day), '%Y-%m-%d') <= ?";
		$params = array($from, $to);
		if ($campus_id !== null && $campus_id !== '' && (int)$campus_id > 0) {
			$sql .= ' AND closing_perday.campus_id = ?';
			$params[] = (int)$campus_id;
		}
		if ((string)$tag_type === '0') {
			$sql .= ' AND (closing_perday.close_type = 1 AND bank_reconciliation_statement.closing_id IS NULL)';
		}
		$sql .= ' ORDER BY closing_perday.id DESC';
		$rows = $this->db->query($sql, $params)->result_array();

		$out = array();
		foreach ($rows as $r) {
			$close_type = isset($r['close_type']) ? (string)$r['close_type'] : '2';
			$checked = isset($r['checked_by']) ? $r['checked_by'] : null;
			$img = isset($r['partialy_closed_image']) ? $r['partialy_closed_image'] : '';
			$closing_date = sprintf(
				'%04d-%02d-%02d',
				(int)$r['for_year'],
				(int)$r['for_month'],
				(int)$r['for_day']
			);
			$next_verify = $this->_next_verify_date((int)$r['campus_id'], $campus_last_verified);
			// Legacy accountsclosingsheet: verify button only when closing_date === last_verified+1 (no Admin bypass)
			$sequential_ok = ($next_verify === null || $closing_date === $next_verify);
			$unchecked = !$this->_is_checked($checked);

			if ($close_type === '1') {
				$close_type_label = 'Bank Closed';
			} elseif ($close_type === '3') {
				$close_type_label = 'PayPro Closed';
			} else {
				$close_type_label = 'Cash Closed';
			}

			$can_verify_cash = $unchecked && $close_type === '2' && $sequential_ok;
			$can_verify_bank = $unchecked && $close_type === '1' && $img !== '' && $sequential_ok;
			$can_edit_amount = !empty($flags['can_conciliation_edit']) || !empty($flags['can_closing_amount_edit']);

			$out[] = array(
				'id' => (int)$r['id'],
				'campus_id' => (int)$r['campus_id'],
				'campus_name' => isset($r['campus_name']) ? $r['campus_name'] : '',
				'for_day' => $r['for_day'],
				'for_month' => $r['for_month'],
				'for_year' => $r['for_year'],
				'closing_date' => $closing_date,
				'campus_closing_id' => $r['campus_closing_id'],
				'receivable_amount' => isset($r['receivable_amount']) ? (float)$r['receivable_amount'] : (float)$r['closed_amount'],
				'closed_amount' => (float)$r['closed_amount'],
				'close_type' => $close_type,
				'close_type_label' => $close_type_label,
				'transaction_no' => isset($r['transaction_no']) ? $r['transaction_no'] : null,
				'account_id' => isset($r['account_id']) ? $r['account_id'] : null,
				'partialy_closed_image' => $img,
				'image_url' => $this->_upload_url($img),
				'closed_by' => isset($r['closed_by']) ? $r['closed_by'] : '',
				'checked_by' => $checked,
				'accounts_status' => $this->_accounts_status_label(true, $checked),
				'brs_credit' => isset($r['brs_credit']) ? $r['brs_credit'] : null,
				'brs_description' => isset($r['brs_description']) ? $r['brs_description'] : null,
				'can_verify_cash' => $can_verify_cash,
				'can_verify_bank' => $can_verify_bank,
				'can_edit_amount' => $can_edit_amount && $can_verify_cash,
			);
		}

		$this->_json(array(
			'success' => true,
			'date' => $date,
			'from_date' => $from,
			'to_date' => $to,
			'tag_type' => $tag_type,
			'rows' => $out,
			'campus_last_closings' => $campus_last_closings,
			'campus_last_verified' => $campus_last_verified,
			'campuses' => $campuses,
			'bank_accounts' => $bank_accounts,
			'flags' => $flags,
		));
	}

	/**
	 * Legacy: Closing::verify_closing_now
	 * POST verify_closing — closing_id; amount optional (cash path)
	 */
	public function verify_closing()
	{
		$body = $this->_body();
		$id = (int)(isset($body['closing_id']) ? $body['closing_id'] : (isset($body['closingid']) ? $body['closingid'] : 0));
		if ($id <= 0 && !empty($body['campus_id']) && !empty($body['date'])) {
			$row = $this->_closing_for_date((int)$body['campus_id'], $body['date']);
			if ($row) $id = (int)$row['id'];
		}
		if ($id <= 0) $this->_json(array('success' => false, 'message' => 'closing_id required'), 400);

		$check = $this->db->query(
			"SELECT * FROM closing_perday WHERE id = ? AND (checked_by IS NULL OR checked_by = '' OR checked_by = '0') LIMIT 1",
			array($id)
		)->row_array();
		if (!$check) {
			$this->_json(array('success' => false, 'message' => 'Closing not found or already verified'), 404);
		}

		$close_type = (string)$check['close_type'];
		$flags = $this->_closing_flags();
		$amount = isset($body['amount']) ? (float)$body['amount'] : (float)$check['closed_amount'];
		$closing_date = $this->_closing_ymd_from_row($check);
		$this->_assert_sequential_verify((int)$check['campus_id'], $closing_date, $this->_campus_last_verified());

		if ($close_type === '1') {
			$tagged = false;
			if ($this->_table_exists('bank_reconciliation_statement')) {
				$brs = $this->db->query(
					'SELECT id FROM bank_reconciliation_statement WHERE closing_id = ? LIMIT 1',
					array($id)
				)->row_array();
				$tagged = (bool)$brs;
			}
			if (!$tagged) {
				$this->_json(array(
					'success' => false,
					'message' => 'Bank closing must be verified via tag_closing_bank (find and tag a bank deposit)',
				), 400);
			}
			$this->db->where('id', $id)->update('closing_perday', array(
				'checked_by' => '1',
				'created_at' => date('Y-m-d H:i:s'),
			));
			$this->_json(array('success' => true, 'message' => 'Closing verified successfully.', 'closing_id' => $id));
		}

		if ($close_type === '3') {
			$paid = true;
			if ($this->_table_exists('students_payments')) {
				$sp = $this->db->query(
					"SELECT transaction_status FROM students_payments WHERE closing_id = ? LIMIT 1",
					array($id)
				)->row_array();
				if ($sp && strtoupper((string)$sp['transaction_status']) !== 'PAID') {
					$paid = false;
				}
			}
			if (!$paid) {
				$this->_json(array('success' => false, 'message' => 'PayPro closing is not paid yet'), 400);
			}
			$this->db->where('id', $id)->update('closing_perday', array(
				'checked_by' => '1',
				'created_at' => date('Y-m-d H:i:s'),
			));
			$this->_json(array('success' => true, 'message' => 'Closing verified successfully.', 'closing_id' => $id));
		}

		// Cash path (close_type=2)
		if (isset($body['amount']) && empty($flags['can_conciliation_edit']) && empty($flags['is_admin'])) {
			$amount = (float)$check['closed_amount'];
		}

		if ($this->_table_exists('college_closing_rules')) {
			$rule = $this->db->query(
				'SELECT * FROM college_closing_rules WHERE campus_id = ? LIMIT 1',
				array((int)$check['campus_id'])
			)->row_array();
			$campus = $this->db->query(
				'SELECT campus_name FROM campuses WHERE campus_id = ? LIMIT 1',
				array((int)$check['campus_id'])
			)->row_array();
			if ($rule && !empty($rule['account_id'])) {
				$this->db->set('amount', 'amount + ' . $amount, false);
				$this->db->where('id', (int)$rule['account_id']);
				$this->db->update('accounts');

				$this->db->where('id', $id)->update('closing_perday', array(
					'closed_amount' => $amount,
					'created_at' => date('Y-m-d H:i:s'),
				));

				if ($this->_table_exists('transactions_history')) {
					$this->db->insert('transactions_history', array(
						'daily_closing_id' => $id,
						'to_account_id' => (int)$rule['account_id'],
						'amount' => $amount,
						'debit_credit' => 'D',
						'transaction_by' => $this->_actor_name(),
						'transaction_account_id' => (int)$rule['account_id'],
						'reason' => 'Funds Receive from Closing ' . $check['campus_closing_id'] . ' '
							. (isset($campus['campus_name']) ? $campus['campus_name'] : '') . ' '
							. $check['for_day'] . '-' . $check['for_month'] . '-' . $check['for_year'],
						'created_at' => date('Y-m-d H:i:s'),
					));
				}
			}
		}

		$this->db->where('id', $id)->update('closing_perday', array(
			'checked_by' => '1',
			'created_at' => date('Y-m-d H:i:s'),
		));
		$this->_json(array('success' => true, 'message' => 'Closing verified successfully.', 'closing_id' => $id));
	}

	/**
	 * Legacy: Closing::find_transactions
	 * POST find_closing_bank_transactions — closing_id, from_date, to_date, amount
	 */
	public function find_closing_bank_transactions()
	{
		$body = $this->_body();
		$closing_id = (int)(isset($body['closing_id']) ? $body['closing_id'] : (isset($body['bank_trans_id']) ? $body['bank_trans_id'] : 0));
		$from_date = isset($body['from_date']) ? date('Y-m-d', strtotime($body['from_date'])) : date('Y-m-d');
		$to_date = isset($body['to_date']) ? date('Y-m-d', strtotime($body['to_date'])) : date('Y-m-d');
		$amount = isset($body['amount']) ? $body['amount'] : 0;
		if ($closing_id <= 0) $this->_json(array('success' => false, 'message' => 'closing_id required'), 400);

		$closing = $this->db->query('SELECT * FROM closing_perday WHERE id = ? LIMIT 1', array($closing_id))->row_array();
		if (!$closing) $this->_json(array('success' => false, 'message' => 'Closing not found'), 404);
		if (empty($closing['account_id'])) {
			$this->_json(array('success' => false, 'message' => 'Closing has no bank account set'), 400);
		}
		if (!$this->_table_exists('bank_reconciliation_statement')) {
			$this->_json(array('success' => true, 'entries' => array()));
		}

		$amount_clean = str_replace(',', '', (string)$amount);
		$sql = "SELECT bank_reconciliation_statement.*, bank_reconciliation_statement.id AS tidx
				FROM bank_reconciliation_statement
				LEFT JOIN payments ON payments.statement_id = bank_reconciliation_statement.id
				WHERE bank_reconciliation_statement.trans_date >= ?
				  AND bank_reconciliation_statement.trans_date <= ?
				  AND bank_reconciliation_statement.account_id = ?
				  AND bank_reconciliation_statement.closing_id IS NULL
				  AND CONVERT(REPLACE(bank_reconciliation_statement.credit, ',', ''), SIGNED) = ?
				GROUP BY bank_reconciliation_statement.description
				ORDER BY bank_reconciliation_statement.trans_date ASC, bank_reconciliation_statement.id ASC";
		$entries = $this->db->query($sql, array(
			$from_date,
			$to_date,
			(int)$closing['account_id'],
			$amount_clean,
		))->result_array();

		$this->_json(array(
			'success' => true,
			'closing_id' => $closing_id,
			'account_id' => (int)$closing['account_id'],
			'from_date' => $from_date,
			'to_date' => $to_date,
			'amount' => $amount_clean,
			'entries' => $entries,
		));
	}

	/**
	 * Legacy: Closing::tag_bank_trans
	 * POST tag_closing_bank — closing_id, tag_id (BRS id)
	 */
	public function tag_closing_bank()
	{
		$body = $this->_body();
		$tag_id = (int)(isset($body['tag_id']) ? $body['tag_id'] : 0);
		$closing_id = (int)(isset($body['closing_id']) ? $body['closing_id'] : (isset($body['bank_trans_id']) ? $body['bank_trans_id'] : 0));
		if ($tag_id <= 0 || $closing_id <= 0) {
			$this->_json(array('success' => false, 'message' => 'tag_id and closing_id required'), 400);
		}

		$bank = $this->db->query('SELECT * FROM bank_reconciliation_statement WHERE id = ?', array($tag_id))->row_array();
		$closing = $this->db->query('SELECT * FROM closing_perday WHERE id = ?', array($closing_id))->row_array();
		if (!$bank || !$closing) $this->_json(array('success' => false, 'message' => 'Record not found'), 404);
		if ($this->_is_checked(isset($closing['checked_by']) ? $closing['checked_by'] : null)) {
			$this->_json(array('success' => false, 'message' => 'Closing already verified'), 400);
		}
		$closing_date = $this->_closing_ymd_from_row($closing);
		$this->_assert_sequential_verify((int)$closing['campus_id'], $closing_date, $this->_campus_last_verified());

		$credit = (int)str_replace(',', '', (string)$bank['credit']);
		$closed_amt = (int)str_replace(',', '', number_format((float)$closing['closed_amount']));
		if ($credit < $closed_amt && $this->_table_exists('expenses')) {
			$this->db->insert('expenses', array(
				'campus_id' => $closing['campus_id'],
				'expense_category_id' => 122,
				'title' => 'Expense against Closing ' . $closing['campus_closing_id'],
				'date' => date('Y-m-d'),
				'amount' => $closed_amt - $credit,
				'purpose' => 'Expense due to Cash short in Closing',
				'actual_date' => date('Y-m-d H:i:s'),
				'image' => '',
				'paid_type' => 'bank',
				'approved_status' => '1',
				'add_by_id' => (int)$this->current_user['user_id'],
				'add_by' => $this->_actor_name(),
			));
		}
		$this->db->where('id', $tag_id)->update('bank_reconciliation_statement', array('closing_id' => $closing_id));
		$this->db->where('id', $closing_id)->update('closing_perday', array(
			'checked_by' => '1',
			'created_at' => date('Y-m-d H:i:s'),
		));
		$this->_json(array('success' => true, 'message' => 'Bank deposit tagged and closing verified', 'closing_id' => $closing_id));
	}

	/**
	 * Legacy: Closing::closing_person / add_closing_person
	 * GET/POST closing_persons
	 */
	public function closing_persons()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$body = $this->_body();
			$user_id = (int)(isset($body['user_id']) ? $body['user_id'] : 0);
			$campus_id = (int)(isset($body['campus_id']) ? $body['campus_id'] : 0);
			if ($user_id <= 0 || $campus_id <= 0) {
				$this->_json(array('success' => false, 'message' => 'user_id and campus_id required'), 400);
			}
			$this->db->insert('closing_persons', array(
				'campus_id' => $campus_id,
				'user_id' => $user_id,
				'active_status' => 1,
				'created_at' => date('Y-m-d H:i:s'),
				'created_by' => $this->_actor_name(),
			));
			$this->_json(array('success' => true, 'id' => (int)$this->db->insert_id(), 'message' => 'Closing person added'));
		}
		$persons = $this->_closing_persons_rows(false);
		$this->_json(array('success' => true, 'persons' => $persons));
	}

	/**
	 * Legacy: Reports::PettyCashReport (simplified)
	 * GET day_closing_report?date=
	 */
	public function day_closing_report()
	{
		$date = $this->input->get('date');
		if (!$date) $date = date('Y-m-d');

		$petty = array();
		if ($this->_table_exists('petty_cash_college_wise')) {
			$rows = $this->db->query(
				"SELECT petty_cash_college_wise.*, campuses.campus_name,
						users.first_name, users.last_name,
						designations.designation_name
				 FROM petty_cash_college_wise
				 LEFT JOIN campuses ON campuses.campus_id = petty_cash_college_wise.campus_id
				 LEFT JOIN users ON users.user_id = petty_cash_college_wise.assign_to
				 LEFT JOIN designations ON designations.designation_id = users.designation_id
				 WHERE petty_cash_college_wise.petty_status = '1'
				 ORDER BY campuses.campus_name ASC"
			)->result_array();
			foreach ($rows as $p) {
				$assign = (int)$p['assign_to'];
				$exp = $this->db->query(
					"SELECT COALESCE(SUM(amount),0) AS amount FROM expenses
					 WHERE add_by_id = ? AND DATE(actual_date) = ? AND paid_type = 'cash'",
					array($assign, $date)
				)->row_array();
				$recv = $this->db->query(
					"SELECT COALESCE(SUM(amount_given),0) AS amount FROM petty_cash_history
					 WHERE transaction_pettycash_account = ? AND DATE(created_at) = ? AND debit_credit = 'D'",
					array((int)$p['id'], $date)
				)->row_array();
				$sent = $this->db->query(
					"SELECT COALESCE(SUM(amount_given),0) AS amount FROM petty_cash_history
					 WHERE transaction_pettycash_account = ? AND DATE(created_at) = ? AND debit_credit = 'C'",
					array((int)$p['id'], $date)
				)->row_array();
				$p['opening_balance_label'] = isset($p['opening_balance']) ? $p['opening_balance'] : 0;
				$p['expenses'] = (float)(isset($exp['amount']) ? $exp['amount'] : 0);
				$p['received'] = (float)(isset($recv['amount']) ? $recv['amount'] : 0);
				$p['sent'] = (float)(isset($sent['amount']) ? $sent['amount'] : 0);
				$p['live_balance'] = $this->_petty_live_balance((int)$p['id']);
				$p['assign_to_name'] = trim($p['first_name'] . ' ' . $p['last_name']);
				$petty[] = $p;
			}
		}

		$closings = array();
		foreach ($this->_closing_persons_rows(true) as $row) {
			$campus_id = (int)$row['campus_id'];
			$closed = $this->_closing_for_date($campus_id, $date);
			$closings[] = array(
				'campus_id' => $campus_id,
				'campus_name' => isset($row['campus_name']) ? $row['campus_name'] : '',
				'closed' => (bool)$closed,
				'closing_amount' => $closed
					? (float)$closed['closed_amount']
					: $this->_campus_closing_bundle($campus_id, $date)['total'],
				'closing_id' => $closed ? (int)$closed['id'] : null,
				'checked_by' => $closed ? $closed['checked_by'] : null,
				'closed_by' => $closed ? $closed['closed_by'] : null,
			);
		}

		$this->_json(array(
			'success' => true,
			'date' => $date,
			'report' => array(
				'petty' => $petty,
				'closings' => $closings,
			),
		));
	}

	/* ===================== Phase 3 — bank recon / PayPro ===================== */

	/* ─── Bank statement reconciliation helpers ─── */

	private function _brs_num($v)
	{
		if ($v === null || $v === '') return 0.0;
		return (float)str_replace(array(',', ' '), '', (string)$v);
	}

	private function _brs_empty($v)
	{
		return $v === null || $v === '' || $v === '0' || $v === 0 || $v === 'NULL';
	}

	private function _brs_cash_accounts()
	{
		if (!$this->_table_exists('accounts')) return array();
		return $this->db->query(
			"SELECT id, account_name, account_title, amount, type
			 FROM accounts WHERE type = '0' OR type = 0
			 ORDER BY account_title ASC, account_name ASC"
		)->result_array();
	}

	private function _brs_expense_categories()
	{
		if (!$this->_table_exists('expense_category')) return array();
		return $this->db->query(
			'SELECT expense_category_id, name, sub_of FROM expense_category ORDER BY name ASC'
		)->result_array();
	}

	private function _brs_campuses()
	{
		if (!$this->_table_exists('campuses')) return array();
		return $this->db->query(
			'SELECT campus_id, campus_name FROM campuses WHERE status = 1 ORDER BY campus_name ASC'
		)->result_array();
	}

	private function _brs_is_tagged_row($row, $has_payment = false)
	{
		if ($has_payment) return true;
		if (!$this->_brs_empty(isset($row['expense_id']) ? $row['expense_id'] : null)) return true;
		if (!$this->_brs_empty(isset($row['salary_expense_ids']) ? $row['salary_expense_ids'] : null)) return true;
		if (!$this->_brs_empty(isset($row['closing_id']) ? $row['closing_id'] : null)) return true;
		if (!$this->_brs_empty(isset($row['bank_transfer_id']) ? $row['bank_transfer_id'] : null)) return true;
		if (!$this->_brs_empty(isset($row['paypro_id']) ? $row['paypro_id'] : null)) return true;
		if (!$this->_brs_empty(isset($row['statement_id']) ? $row['statement_id'] : null)) return true;
		if (!$this->_brs_empty(isset($row['is_council_fee']) ? $row['is_council_fee'] : null)) return true;
		if (!$this->_brs_empty(isset($row['profit_distribution_id']) ? $row['profit_distribution_id'] : null)) return true;
		if (!$this->_brs_empty(isset($row['loan_id']) ? $row['loan_id'] : null)) return true;
		if (!$this->_brs_empty(isset($row['reversal_payroll_trans_id']) ? $row['reversal_payroll_trans_id'] : null)) return true;
		if (!$this->_brs_empty(isset($row['reversal_payroll_id']) ? $row['reversal_payroll_id'] : null)) return true;
		$related = isset($row['related_to']) ? $row['related_to'] : 0;
		if ($related !== null && $related !== '' && (int)$related !== 0) return true;
		return false;
	}

	private function _brs_payment_rows($brs_id)
	{
		if (!$this->_table_exists('payments') || (int)$brs_id <= 0) return array();
		// Legacy bank_statement.php — payments PK is `id` (not payment_id)
		return $this->db->query(
			"SELECT payments.id, payments.actual_amount, payments.challan_no,
					payments.paid_challans, payments.tid_no, payments.paid_date, payments.contract_id,
					students.first_name, students.last_name, students.roll_no, students.cnic, students.mobile,
					students.emergency_no, students.father_name,
					campuses.campus_name, classes.name AS class_name, courses.course_name
			 FROM payments
			 INNER JOIN students ON students.student_id = payments.student_id
			 LEFT JOIN classes ON classes.class_id = students.class_id
			 LEFT JOIN campuses ON campuses.campus_id = classes.campus_id
			 LEFT JOIN courses ON courses.course_id = students.course_id
			 WHERE payments.statement_id = ?
			 LIMIT 50",
			array((int)$brs_id)
		)->result_array();
	}

	private function _brs_enrich_entry($row)
	{
		$id = (int)$row['id'];
		$debit = $this->_brs_num(isset($row['debit']) ? $row['debit'] : 0);
		$credit = $this->_brs_num(isset($row['credit']) ? $row['credit'] : 0);
		$payments = $this->_brs_payment_rows($id);
		$has_payment = count($payments) > 0;
		$is_tagged = $this->_brs_is_tagged_row($row, $has_payment);

		$account_title = isset($row['account_title']) ? $row['account_title'] : '';
		$account_name = isset($row['account_name']) ? $row['account_name'] : '';
		$bank_label = trim($account_title . ' ' . $account_name);

		$relate = array(
			'relate_type' => 'none',
			'relate_label' => '',
			'relate_detail' => array(),
			'untag_type' => null,
		);

		if ($has_payment) {
			$detail = array();
			foreach ($payments as $p) {
				$detail[] = array(
					'id' => isset($p['id']) ? (int)$p['id'] : 0,
					'challan_no' => !empty($p['paid_challans']) ? $p['paid_challans'] : $p['challan_no'],
					'amount' => isset($p['actual_amount']) ? $p['actual_amount'] : 0,
					'tid_no' => $p['tid_no'],
					'paid_date' => $p['paid_date'],
					'roll_no' => $p['roll_no'],
					'name' => trim((isset($p['first_name']) ? $p['first_name'] : '') . ' ' . (isset($p['last_name']) ? $p['last_name'] : '')),
					'cnic' => isset($p['cnic']) ? $p['cnic'] : '',
					'mobile' => isset($p['mobile']) ? $p['mobile'] : '',
					'campus_name' => $p['campus_name'],
					'class_name' => $p['class_name'],
					'course_name' => $p['course_name'],
				);
			}
			$relate = array(
				'relate_type' => 'payment',
				'relate_label' => 'Fee payment',
				'relate_detail' => $detail,
				'untag_type' => 'payment',
			);
		} elseif (!$this->_brs_empty(isset($row['expense_id']) ? $row['expense_id'] : null)) {
			$expense = null;
			if ($this->_table_exists('expenses')) {
				$expense = $this->db->query(
					"SELECT expenses.*, expense_category.name AS category_name, campuses.campus_name
					 FROM expenses
					 LEFT JOIN expense_category ON expense_category.expense_category_id = expenses.expense_category_id
					 LEFT JOIN campuses ON campuses.campus_id = expenses.campus_id
					 WHERE expenses.expense_id = ? LIMIT 1",
					array((int)$row['expense_id'])
				)->row_array();
			}
			$is_council = !$this->_brs_empty(isset($row['is_council_fee']) ? $row['is_council_fee'] : null);
			$relate = array(
				'relate_type' => $is_council ? 'council_fee' : 'expense',
				'relate_label' => $is_council ? 'Council fee expense' : 'Expense',
				'relate_detail' => $expense ? array(
					'category' => isset($expense['category_name']) ? $expense['category_name'] : '',
					'campus_name' => isset($expense['campus_name']) ? $expense['campus_name'] : '',
					'title' => isset($expense['title']) ? $expense['title'] : '',
					'purpose' => isset($expense['purpose']) ? $expense['purpose'] : '',
					'amount' => isset($expense['amount']) ? $expense['amount'] : '',
					'add_by' => isset($expense['add_by']) ? $expense['add_by'] : '',
					'expense_id' => (int)$row['expense_id'],
				) : array('expense_id' => (int)$row['expense_id']),
				'untag_type' => 'expense',
			);
		} elseif (!$this->_brs_empty(isset($row['bank_transfer_id']) ? $row['bank_transfer_id'] : null)) {
			$other = $this->db->query(
				"SELECT brs.*, accounts.account_name, accounts.account_title
				 FROM bank_reconciliation_statement brs
				 LEFT JOIN accounts ON accounts.id = brs.account_id
				 WHERE brs.id = ? LIMIT 1",
				array((int)$row['bank_transfer_id'])
			)->row_array();
			$other_credit = $other ? $this->_brs_num($other['credit']) : 0;
			$relate = array(
				'relate_type' => 'bank',
				'relate_label' => ($other && $other_credit > 0) ? 'Transferred to account' : 'Received from account',
				'relate_detail' => $other ? array(
					'account_name' => isset($other['account_name']) ? $other['account_name'] : '',
					'account_title' => isset($other['account_title']) ? $other['account_title'] : '',
					'trans_date' => $other['trans_date'],
					'debit' => $other['debit'],
					'credit' => $other['credit'],
					'related_id' => (int)$row['bank_transfer_id'],
				) : array(),
				'untag_type' => 'bank',
			);
		} elseif (!$this->_brs_empty(isset($row['statement_id']) ? $row['statement_id'] : null) && $this->_table_exists('transactions_history')) {
			$join_col = ($credit <= 0) ? 'to_account_id' : 'from_account_id';
			$tx = $this->db->query(
				"SELECT th.*, th.amount AS trans_amount, accounts.account_name, accounts.account_title
				 FROM transactions_history th
				 LEFT JOIN accounts ON accounts.id = th.{$join_col}
				 WHERE th.id = ? LIMIT 1",
				array((int)$row['statement_id'])
			)->row_array();
			$relate = array(
				'relate_type' => ($credit <= 0) ? 'cash' : 'cash_deposit',
				'relate_label' => ($credit <= 0) ? 'Cash withdrawal' : 'Cash deposit',
				'relate_detail' => $tx ? array(
					'account_name' => isset($tx['account_name']) ? $tx['account_name'] : '',
					'date' => !empty($tx['created_at']) ? date('Y-m-d', strtotime($tx['created_at'])) : '',
					'amount' => isset($tx['trans_amount']) ? $tx['trans_amount'] : '',
					'reason' => isset($tx['reason']) ? $tx['reason'] : '',
					'history_id' => (int)$row['statement_id'],
				) : array('history_id' => (int)$row['statement_id']),
				'untag_type' => 'entry',
			);
		} elseif (!$this->_brs_empty(isset($row['closing_id']) ? $row['closing_id'] : null)) {
			$closing = null;
			if ($this->_table_exists('closing_perday')) {
				$closing = $this->db->query(
					"SELECT closing_perday.*, campuses.campus_name
					 FROM closing_perday
					 LEFT JOIN campuses ON campuses.campus_id = closing_perday.campus_id
					 WHERE closing_perday.id = ? LIMIT 1",
					array((int)$row['closing_id'])
				)->row_array();
			}
			$relate = array(
				'relate_type' => 'closing',
				'relate_label' => 'Closing deposit',
				'relate_detail' => $closing ? array(
					'campus_name' => $closing['campus_name'],
					'campus_closing_id' => $closing['campus_closing_id'],
					'amount' => isset($row['credit']) ? $row['credit'] : '',
					'closing_id' => (int)$row['closing_id'],
				) : array('closing_id' => (int)$row['closing_id']),
				'untag_type' => 'closing',
			);
		} elseif (!$this->_brs_empty(isset($row['is_council_fee']) ? $row['is_council_fee'] : null)) {
			$relate = array(
				'relate_type' => 'council_fee',
				'relate_label' => 'Council fee (not tagged)',
				'relate_detail' => array('message' => 'Selected as Council Fee Not Tagged'),
				'untag_type' => 'council_fee',
			);
		} elseif (!$this->_brs_empty(isset($row['paypro_id']) ? $row['paypro_id'] : null)) {
			$pp = null;
			if ($this->_table_exists('pay_pro_settlement')) {
				$pp = $this->db->query(
					'SELECT * FROM pay_pro_settlement WHERE id = ? LIMIT 1',
					array((int)$row['paypro_id'])
				)->row_array();
			}
			$relate = array(
				'relate_type' => 'pay_pro',
				'relate_label' => 'PayPro settlement',
				'relate_detail' => $pp ? array(
					'settlement_date' => $pp['settlement_date'],
					'tagged_credit' => isset($row['credit']) ? $row['credit'] : '',
					'paid_amount' => $pp['paid_amount'],
					'link_amount' => $pp['link_amount'],
					'card_amount' => $pp['card_amount'],
					'total_amount' => $pp['total_amount'],
					'paypro_id' => (int)$row['paypro_id'],
				) : array('paypro_id' => (int)$row['paypro_id']),
				'untag_type' => 'paypro',
			);
		} elseif (!$this->_brs_empty(isset($row['salary_expense_ids']) ? $row['salary_expense_ids'] : null)) {
			$salaries = array();
			if ($this->_table_exists('expenses')) {
				$salaries = $this->db->query(
					"SELECT expenses.expense_id, expenses.amount, expenses.add_by, expenses.title,
							expense_category.name AS category_name, campuses.campus_name
					 FROM expenses
					 LEFT JOIN expense_category ON expense_category.expense_category_id = expenses.expense_category_id
					 LEFT JOIN campuses ON campuses.campus_id = expenses.campus_id
					 WHERE expenses.bank_statement_id = ?",
					array($id)
				)->result_array();
			}
			$relate = array(
				'relate_type' => 'expense_tag',
				'relate_label' => 'Salary expense',
				'relate_detail' => array('expenses' => $salaries, 'salary_expense_ids' => $row['salary_expense_ids']),
				'untag_type' => 'salary_expense',
			);
		} elseif (!$this->_brs_empty(isset($row['profit_distribution_id']) ? $row['profit_distribution_id'] : null)) {
			$entry = null;
			if ($this->_table_exists('profit_distribution')) {
				$entry = $this->db->query(
					"SELECT profit_distribution.*, campuses.campus_name, users.first_name, users.last_name
					 FROM profit_distribution
					 LEFT JOIN campuses ON campuses.campus_id = profit_distribution.campus_id
					 LEFT JOIN users ON users.user_id = profit_distribution.user_id
					 WHERE profit_distribution_id = ? LIMIT 1",
					array((int)$row['profit_distribution_id'])
				)->row_array();
			}
			$relate = array(
				'relate_type' => 'share_distribution',
				'relate_label' => 'Share profit distribution',
				'relate_detail' => $entry ? array(
					'name' => trim((isset($entry['first_name']) ? $entry['first_name'] : '') . ' ' . (isset($entry['last_name']) ? $entry['last_name'] : '')),
					'from_date' => $entry['from_date'],
					'to_date' => $entry['to_date'],
					'campus_name' => $entry['campus_name'],
					'amount' => $entry['amount'],
					'profit_distribution_id' => (int)$row['profit_distribution_id'],
				) : array(),
				'untag_type' => 'profit',
			);
		} elseif (!$this->_brs_empty(isset($row['loan_id']) ? $row['loan_id'] : null)) {
			$entry = null;
			if ($this->_table_exists('loans')) {
				$entry = $this->db->query(
					"SELECT loans.*, users.first_name, users.last_name, expenses.date AS expense_date
					 FROM loans
					 LEFT JOIN users ON users.user_id = loans.user_id
					 LEFT JOIN expenses ON expenses.loan_id = loans.id
					 WHERE loans.id = ? LIMIT 1",
					array((int)$row['loan_id'])
				)->row_array();
			}
			$relate = array(
				'relate_type' => 'tag_loan',
				'relate_label' => 'Loan',
				'relate_detail' => $entry ? array(
					'loan_id' => (int)$entry['id'],
					'name' => trim((isset($entry['first_name']) ? $entry['first_name'] : '') . ' ' . (isset($entry['last_name']) ? $entry['last_name'] : '')),
					'amount' => $entry['cash_given'],
					'months' => $entry['months_approved'],
					'expense_date' => isset($entry['expense_date']) ? $entry['expense_date'] : '',
				) : array('loan_id' => (int)$row['loan_id']),
				'untag_type' => 'loan',
			);
		} elseif (!$this->_brs_empty(isset($row['reversal_payroll_trans_id']) ? $row['reversal_payroll_trans_id'] : null)
			|| !$this->_brs_empty(isset($row['reversal_payroll_id']) ? $row['reversal_payroll_id'] : null)) {
			$src = null;
			$payroll = null;
			if (!$this->_brs_empty(isset($row['reversal_payroll_trans_id']) ? $row['reversal_payroll_trans_id'] : null)) {
				$src = $this->db->query(
					"SELECT brs.*, accounts.account_name
					 FROM bank_reconciliation_statement brs
					 LEFT JOIN accounts ON accounts.id = brs.account_id
					 WHERE brs.id = ? LIMIT 1",
					array((int)$row['reversal_payroll_trans_id'])
				)->row_array();
			}
			if ($this->_table_exists('payroll') && !$this->_brs_empty(isset($row['reversal_payroll_id']) ? $row['reversal_payroll_id'] : null)) {
				$payroll = $this->db->query(
					"SELECT payroll.*, users.first_name, users.last_name
					 FROM payroll
					 LEFT JOIN users ON users.user_id = payroll.user_id
					 WHERE payroll.id = ? LIMIT 1",
					array((int)$row['reversal_payroll_id'])
				)->row_array();
			}
			$relate = array(
				'relate_type' => 'salary_reverse',
				'relate_label' => 'Salary reverse',
				'relate_detail' => array(
					'account_name' => $src ? $src['account_name'] : '',
					'amount' => $src ? $src['debit'] : '',
					'person' => $payroll ? trim($payroll['first_name'] . ' ' . $payroll['last_name']) : '',
					'month' => $payroll ? ($payroll['payroll_year'] . '-' . $payroll['payroll_month']) : '',
					'salary' => $payroll ? $payroll['earned_salary'] : '',
				),
				'untag_type' => 'salary_reverse',
			);
		}

		$can_tag_debit = array();
		$can_tag_credit = array();
		if (!$is_tagged) {
			if ($debit > 0 && $credit <= 0) {
				$can_tag_debit = array('expense', 'bank', 'cash', 'council_fee', 'expense_tag', 'share_distribution', 'tag_loan');
			}
			if ($credit > 0) {
				$can_tag_credit = array('pay_pro', 'cash_deposit', 'salary_reverse');
			}
		}

		return array(
			'id' => $id,
			'trans_id' => $id,
			'statement_no' => isset($row['statement_no']) ? $row['statement_no'] : null,
			'account_id' => isset($row['account_id']) ? (int)$row['account_id'] : 0,
			'account_name' => $account_name,
			'account_title' => $account_title,
			'bank_name' => $bank_label,
			'trans_date' => isset($row['trans_date']) ? $row['trans_date'] : '',
			'description' => isset($row['description']) ? $row['description'] : '',
			'reference_no' => isset($row['reference_no']) ? $row['reference_no'] : '',
			'transaction_type' => trim((isset($row['description']) ? $row['description'] : '') . ' ' . (isset($row['reference_no']) ? $row['reference_no'] : '')),
			'debit' => isset($row['debit']) ? $row['debit'] : '',
			'credit' => isset($row['credit']) ? $row['credit'] : '',
			'debit_num' => $debit,
			'credit_num' => $credit,
			'balance' => isset($row['balance']) ? $row['balance'] : '',
			'tagged_amount' => isset($row['tagged_amount']) ? $row['tagged_amount'] : 0,
			'expense_id' => isset($row['expense_id']) ? $row['expense_id'] : null,
			'closing_id' => isset($row['closing_id']) ? $row['closing_id'] : null,
			'bank_transfer_id' => isset($row['bank_transfer_id']) ? $row['bank_transfer_id'] : null,
			'paypro_id' => isset($row['paypro_id']) ? $row['paypro_id'] : null,
			'salary_expense_ids' => isset($row['salary_expense_ids']) ? $row['salary_expense_ids'] : null,
			'statement_id' => isset($row['statement_id']) ? $row['statement_id'] : null,
			'is_council_fee' => isset($row['is_council_fee']) ? $row['is_council_fee'] : null,
			'profit_distribution_id' => isset($row['profit_distribution_id']) ? $row['profit_distribution_id'] : null,
			'loan_id' => isset($row['loan_id']) ? $row['loan_id'] : null,
			'reversal_payroll_id' => isset($row['reversal_payroll_id']) ? $row['reversal_payroll_id'] : null,
			'reversal_payroll_trans_id' => isset($row['reversal_payroll_trans_id']) ? $row['reversal_payroll_trans_id'] : null,
			'payment_relate_to' => $relate,
			'relate_type' => $relate['relate_type'],
			'relate_label' => $relate['relate_label'],
			'relate_detail' => $relate['relate_detail'],
			'is_tagged' => $is_tagged,
			'can_tag_debit_actions' => $can_tag_debit,
			'can_tag_credit_actions' => $can_tag_credit,
			'can_untag' => $is_tagged && !empty($relate['untag_type']),
			'untag_type' => $relate['untag_type'],
		);
	}

	private function _brs_parse_csv_lines($account_id, $filepath)
	{
		$lines = array();
		$fh = @fopen($filepath, 'r');
		if (!$fh) return $lines;
		$row = 0;
		$account_id = (int)$account_id;
		while (($index = fgetcsv($fh)) !== false) {
			$row++;
			if (!is_array($index) || !count(array_filter($index, function ($v) { return $v !== null && $v !== ''; }))) {
				continue;
			}
			try {
				if ($account_id === 3) {
					if ($row == 14 && (!isset($index[0]) || $index[0] !== 'Transaction Date')) {
						fclose($fh);
						return array('error' => 'Wrong Csv Format.');
					}
					if ($row <= 14) continue;
					$newDate = date('Y-m-d', strtotime($index[0]));
					if ($newDate === date('Y-m-d') && date('Y-m-d', strtotime($index[0])) === date('Y-m-d')) {
						// allow same-day; legacy skipped when strtotime fails to today incorrectly — keep legacy skip when empty desc
					}
					if (!isset($index[2]) || $index[2] === '' || $index[2] === null) break;
					$parsed = date('Y-m-d', strtotime($index[0]));
					if (!$parsed || $parsed === '1970-01-01') continue;
					$lines[] = array(
						'trans_date' => $parsed,
						'description' => $index[2],
						'debit' => str_replace('-', '', isset($index[3]) ? $index[3] : ''),
						'credit' => str_replace('-', '', isset($index[4]) ? $index[4] : ''),
						'balance' => isset($index[5]) ? $index[5] : '',
					);
				} elseif (in_array($account_id, array(5, 10, 12), true)) {
					if ($row == 4 && (!isset($index[0]) || $index[0] !== 'Date')) {
						fclose($fh);
						return array('error' => 'Wrong Csv Format.');
					}
					if ($row <= 4) continue;
					if (!isset($index[0]) || $index[0] === '') continue;
					$parsed = date('Y-m-d', strtotime($index[0]));
					if (!$parsed || $parsed === '1970-01-01') continue;
					$dr = (isset($index[5]) && $index[5] === 'Dr');
					$amt = str_replace('-', '', isset($index[4]) ? $index[4] : '');
					$lines[] = array(
						'trans_date' => $parsed,
						'description' => isset($index[1]) ? $index[1] : '',
						'reference_no' => isset($index[2]) ? $index[2] : '',
						'debit' => $dr ? $amt : '',
						'credit' => $dr ? '' : $amt,
						'balance' => isset($index[7]) ? $index[7] : '',
					);
				} elseif ($account_id === 4) {
					if ($row == 4 && (!isset($index[0]) || $index[0] !== 'Date/Time')) {
						fclose($fh);
						return array('error' => 'Wrong Csv Format.');
					}
					if ($row <= 4) continue;
					if (!isset($index[0]) || $index[0] === '') continue;
					$dt = date_create_from_format('d/m/Y', $index[0]);
					if (!$dt) continue;
					$parsed = date_format($dt, 'Y-m-d');
					$lines[] = array(
						'trans_date' => $parsed,
						'description' => trim((isset($index[2]) ? $index[2] : '') . ' ' . (isset($index[3]) ? $index[3] : '')),
						'reference_no' => isset($index[1]) ? $index[1] : '',
						'debit' => str_replace('-', '', isset($index[4]) ? $index[4] : ''),
						'credit' => str_replace('-', '', isset($index[5]) ? $index[5] : ''),
						'balance' => isset($index[6]) ? $index[6] : '',
					);
				} elseif (in_array($account_id, array(6, 11), true)) {
					if ($row == 1 && (!isset($index[0]) || $index[0] !== 'Date')) {
						fclose($fh);
						return array('error' => 'Wrong Csv Format.');
					}
					if ($row <= 2) continue;
					if (!isset($index[0]) || $index[0] === '') continue;
					$parsed = date('Y-m-d', strtotime($index[0]));
					if (!$parsed || $parsed === '1970-01-01') continue;
					$lines[] = array(
						'trans_date' => $parsed,
						'description' => isset($index[1]) ? $index[1] : '',
						'debit' => str_replace('-', '', isset($index[2]) ? $index[2] : ''),
						'credit' => str_replace('-', '', isset($index[3]) ? $index[3] : ''),
						'balance' => isset($index[4]) ? $index[4] : '',
					);
				} elseif ($account_id === 7) {
					if ($row == 4 && (!isset($index[0]) || $index[0] !== 'Date/Time')) {
						fclose($fh);
						return array('error' => 'Wrong Csv Format.');
					}
					if ($row <= 4) continue;
					if (!isset($index[0]) || $index[0] === '') continue;
					$date = substr($index[0], 0, 10);
					$dt = date_create_from_format('d-m-Y', $date);
					if (!$dt) continue;
					$parsed = date_format($dt, 'Y-m-d');
					$lines[] = array(
						'trans_date' => $parsed,
						'description' => isset($index[1]) ? $index[1] : '',
						'reference_no' => isset($index[2]) ? $index[2] : '',
						'debit' => str_replace('-', '', isset($index[5]) ? $index[5] : ''),
						'credit' => str_replace('-', '', isset($index[4]) ? $index[4] : ''),
						'balance' => '',
					);
				} else {
					// Generic: date, description, debit, credit, balance (skip header-ish first row)
					if ($row === 1 && isset($index[0]) && stripos((string)$index[0], 'date') !== false) continue;
					$parsed = date('Y-m-d', strtotime($index[0]));
					if (!$parsed || $parsed === '1970-01-01') continue;
					$lines[] = array(
						'trans_date' => $parsed,
						'description' => isset($index[1]) ? $index[1] : (isset($index[2]) ? $index[2] : ''),
						'debit' => isset($index[2]) ? str_replace(array(',', '-'), '', $index[2]) : '',
						'credit' => isset($index[3]) ? str_replace(array(',', '-'), '', $index[3]) : '',
						'balance' => isset($index[4]) ? $index[4] : '',
					);
				}
			} catch (Exception $e) {
				continue;
			}
		}
		fclose($fh);
		return $lines;
	}

	/**
	 * GET bank_statement?from_date&to_date&account_id&tagged=0|1|
	 * Full legacy-shaped rows + accounts/campuses/categories for SPA tagging UI.
	 */
	public function bank_statement()
	{
		$from = $this->input->get('from_date');
		$to = $this->input->get('to_date');
		$account_id = $this->input->get('account_id');
		$tagged = $this->input->get('tagged');
		if (!$from) $from = date('Y-m-01');
		if (!$to) $to = date('Y-m-d');

		$meta = array(
			'accounts' => $this->_bank_accounts_rows(),
			'cash_accounts' => $this->_brs_cash_accounts(),
			'campuses' => $this->_brs_campuses(),
			'expense_categories' => $this->_brs_expense_categories(),
			'flags' => array(
				'is_admin' => $this->_is_admin(),
				'can_upload' => $this->_is_admin(),
			),
		);

		if (!$this->_table_exists('bank_reconciliation_statement')) {
			$this->_json(array_merge(array(
				'success' => true,
				'from_date' => $from,
				'to_date' => $to,
				'entries' => array(),
			), $meta));
		}

		$sql = "SELECT brs.*, brs.id AS trans_id, brs.statement_id AS str_id, brs.closing_id AS closing_bank_id,
					accounts.account_name, accounts.account_title
				FROM bank_reconciliation_statement brs
				LEFT JOIN accounts ON accounts.id = brs.account_id
				WHERE brs.trans_date >= ? AND brs.trans_date <= ?";
		$params = array($from, $to);
		if ($account_id !== null && $account_id !== '') {
			// support single id or comma list
			$ids = array_filter(array_map('intval', explode(',', (string)$account_id)));
			if (count($ids) === 1) {
				$sql .= ' AND brs.account_id = ?';
				$params[] = $ids[0];
			} elseif (count($ids) > 1) {
				$sql .= ' AND brs.account_id IN (' . implode(',', $ids) . ')';
			}
		}
		$sql .= ' ORDER BY brs.trans_date ASC, brs.id ASC LIMIT 3000';
		$raw = $this->db->query($sql, $params)->result_array();

		$entries = array();
		foreach ($raw as $row) {
			$enriched = $this->_brs_enrich_entry($row);
			if ($tagged === '0' || $tagged === 0) {
				if ($enriched['is_tagged']) continue;
			} elseif ($tagged === '1' || $tagged === 1) {
				if (!$enriched['is_tagged']) continue;
			}
			$entries[] = $enriched;
		}

		$this->_json(array_merge(array(
			'success' => true,
			'from_date' => $from,
			'to_date' => $to,
			'account_id' => $account_id,
			'tagged' => $tagged,
			'entries' => $entries,
		), $meta));
	}

	/**
	 * Legacy: Accounts::upload_bank_statement
	 * POST upload_bank_statement — multipart file+account_id OR JSON {account_id, lines:[...]}
	 */
	public function upload_bank_statement()
	{
		$body = $this->_body();
		$account_id = (int)(isset($body['account_id']) ? $body['account_id'] : $this->input->post('account_id'));
		if ($account_id <= 0) $this->_json(array('success' => false, 'message' => 'account_id required'), 400);

		$lines = array();
		$file_name = null;
		if (!empty($body['lines']) && is_array($body['lines'])) {
			$lines = $body['lines'];
		} elseif (!empty($_FILES['file']['tmp_name']) || !empty($_FILES['statement']['tmp_name'])) {
			$key = !empty($_FILES['file']['tmp_name']) ? 'file' : 'statement';
			$tmp = $_FILES[$key]['tmp_name'];
			$name = $_FILES[$key]['name'];
			$dir = FCPATH . 'statements/';
			if (!is_dir($dir)) @mkdir($dir, 0777, true);
			$ext = pathinfo($name, PATHINFO_EXTENSION);
			$stored = 'stmt_' . date('YmdHis') . '_' . mt_rand(1000, 9999) . ($ext ? '.' . $ext : '');
			if (!@move_uploaded_file($tmp, $dir . $stored)) {
				$this->_json(array('success' => false, 'message' => 'Failed to store upload'), 500);
			}
			$file_name = $stored;
			$parsed = $this->_brs_parse_csv_lines($account_id, $dir . $stored);
			if (isset($parsed['error'])) {
				$this->_json(array('success' => false, 'message' => $parsed['error']), 400);
			}
			$lines = $parsed;
		} else {
			$this->_json(array('success' => false, 'message' => 'Provide lines[] JSON or CSV file'), 400);
		}

		$statement_no = null;
		if ($this->_table_exists('statement_upload_record')) {
			$this->db->insert('statement_upload_record', array(
				'date' => date('Y-m-d'),
				'account_id' => $account_id,
				'file' => $file_name ? $file_name : 'json_upload',
				'add_by' => $this->_actor_name(),
			));
			$statement_no = $this->db->insert_id();
		}

		$inserted = 0;
		foreach ($lines as $line) {
			$trans_date = isset($line['trans_date']) ? date('Y-m-d', strtotime($line['trans_date'])) : null;
			if (!$trans_date || $trans_date === '1970-01-01') continue;
			$row = array(
				'account_id' => $account_id,
				'trans_date' => $trans_date,
				'description' => isset($line['description']) ? $line['description'] : '',
				'debit' => isset($line['debit']) ? str_replace(',', '', (string)$line['debit']) : '',
				'credit' => isset($line['credit']) ? str_replace(',', '', (string)$line['credit']) : '',
				'balance' => isset($line['balance']) ? $line['balance'] : '',
			);
			if ($statement_no) $row['statement_no'] = $statement_no;
			if (!empty($line['reference_no'])) $row['reference_no'] = $line['reference_no'];
			$this->db->insert('bank_reconciliation_statement', $row);
			$inserted++;
		}
		$this->_json(array(
			'success' => true,
			'message' => "Uploaded $inserted lines",
			'statement_no' => $statement_no,
			'inserted' => $inserted,
		));
	}

	/**
	 * Legacy: Closing::tag_bank_trans + Accounts::tag_bank_trans (mutual / paypro / closing)
	 * POST tag_bank_trans — kept for conciliation + mutual compatibility
	 */
	public function tag_bank_trans()
	{
		$body = $this->_body();
		$type = isset($body['type']) ? $body['type'] : 'closing';
		$tag_id = (int)(isset($body['tag_id']) ? $body['tag_id'] : (isset($body['id']) ? $body['id'] : 0));

		if ($type === 'closing' || $type === 'closing_deposit') {
			$closing_id = (int)(isset($body['closing_id']) ? $body['closing_id'] : (isset($body['bank_trans_id']) ? $body['bank_trans_id'] : 0));
			if ($tag_id <= 0 || $closing_id <= 0) {
				$this->_json(array('success' => false, 'message' => 'tag_id and closing_id required'), 400);
			}
			$bank = $this->db->query('SELECT * FROM bank_reconciliation_statement WHERE id = ?', array($tag_id))->row_array();
			$closing = $this->db->query('SELECT * FROM closing_perday WHERE id = ?', array($closing_id))->row_array();
			if (!$bank || !$closing) $this->_json(array('success' => false, 'message' => 'Record not found'), 404);

			$credit = (int)str_replace(',', '', (string)$bank['credit']);
			$closed_amt = (int)str_replace(',', '', number_format((float)$closing['closed_amount']));
			if ($credit < $closed_amt && $this->_table_exists('expenses')) {
				$this->db->insert('expenses', array(
					'campus_id' => $closing['campus_id'],
					'expense_category_id' => 122,
					'title' => 'Expense against Closing ' . $closing['campus_closing_id'],
					'date' => date('Y-m-d'),
					'amount' => $closed_amt - $credit,
					'purpose' => 'Expense due to Cash short in Closing',
					'actual_date' => date('Y-m-d H:i:s'),
					'image' => '',
					'paid_type' => 'bank',
					'approved_status' => '1',
					'add_by_id' => (int)$this->current_user['user_id'],
					'add_by' => $this->_actor_name(),
				));
			}
			$this->db->where('id', $tag_id)->update('bank_reconciliation_statement', array('closing_id' => $closing_id));
			$this->db->where('id', $closing_id)->update('closing_perday', array(
				'checked_by' => '1',
				'created_at' => date('Y-m-d H:i:s'),
			));
			$this->_json(array('success' => true, 'message' => 'Closing deposit tagged'));
		}

		if ($type === 'bank_transfer' || $type === 'transfer' || $type === 'bank') {
			$trans_id = (int)(isset($body['bank_trans_id']) ? $body['bank_trans_id'] : (isset($body['related_id']) ? $body['related_id'] : 0));
			if ($tag_id <= 0 || $trans_id <= 0) {
				$this->_json(array('success' => false, 'message' => 'tag_id and bank_trans_id required'), 400);
			}
			$this->db->where('id', $trans_id)->update('bank_reconciliation_statement', array('bank_transfer_id' => $tag_id));
			$this->db->where('id', $tag_id)->update('bank_reconciliation_statement', array('bank_transfer_id' => $trans_id));
			$this->_json(array('success' => true, 'message' => 'Bank transfer tagged'));
		}

		if ($type === 'paypro' || $type === 'pay_pro') {
			$paypro_id = (int)(isset($body['paypro_id']) ? $body['paypro_id'] : (isset($body['related_id']) ? $body['related_id'] : (isset($body['tag_id']) ? $body['tag_id'] : 0)));
			$trans_id = (int)(isset($body['bank_trans_id']) ? $body['bank_trans_id'] : (isset($body['id']) ? $body['id'] : $tag_id));
			$amount = isset($body['amount']) ? (float)$body['amount'] : 0;
			if ($trans_id <= 0 || $paypro_id <= 0) {
				$this->_json(array('success' => false, 'message' => 'bank_trans_id and paypro_id required'), 400);
			}
			$this->db->where('id', $trans_id)->update('bank_reconciliation_statement', array('paypro_id' => $paypro_id));
			if ($this->_table_exists('pay_pro_settlement') && $amount > 0) {
				$this->db->set('tagged_amount', 'tagged_amount + ' . $amount, false);
				$this->db->where('id', $paypro_id);
				$this->db->update('pay_pro_settlement');
			}
			$this->_json(array('success' => true, 'message' => 'PayPro tagged'));
		}

		$this->_json(array('success' => false, 'message' => 'Unknown type'), 400);
	}

	/**
	 * Legacy: Accounts::untag_bank_entry
	 * POST untag_bank_entry — {id}
	 */
	public function untag_bank_entry()
	{
		$body = $this->_body();
		$id = (int)(isset($body['id']) ? $body['id'] : $this->input->get('id'));
		if ($id <= 0) $this->_json(array('success' => false, 'message' => 'id required'), 400);
		$row = $this->db->query('SELECT * FROM bank_reconciliation_statement WHERE id = ?', array($id))->row_array();
		if (!$row) $this->_json(array('success' => false, 'message' => 'Not found'), 404);

		if (!empty($row['bank_transfer_id'])) {
			$this->db->where('id', (int)$row['bank_transfer_id'])->update('bank_reconciliation_statement', array('bank_transfer_id' => null));
			$this->db->where('id', $id)->update('bank_reconciliation_statement', array('bank_transfer_id' => null));
		}
		if (!empty($row['closing_id'])) {
			$this->db->where('id', $id)->update('bank_reconciliation_statement', array('closing_id' => null));
		}
		if (!empty($row['paypro_id'])) {
			$this->db->where('id', $id)->update('bank_reconciliation_statement', array('paypro_id' => null));
		}
		$this->_json(array('success' => true, 'message' => 'Successfully untagged'));
	}

	/**
	 * Unified untag — mirrors untag_payment / untag_bank_entry / untagentry / expense clears
	 * POST bank_untag — {statement_id, untag_type}
	 */
	public function bank_untag()
	{
		$body = $this->_body();
		$id = (int)(isset($body['statement_id']) ? $body['statement_id'] : (isset($body['id']) ? $body['id'] : 0));
		$type = isset($body['untag_type']) ? $body['untag_type'] : 'auto';
		if ($id <= 0) $this->_json(array('success' => false, 'message' => 'statement_id required'), 400);
		$row = $this->db->query('SELECT * FROM bank_reconciliation_statement WHERE id = ?', array($id))->row_array();
		if (!$row) $this->_json(array('success' => false, 'message' => 'Not found'), 404);

		if ($type === 'auto') {
			$enriched = $this->_brs_enrich_entry($row);
			$type = $enriched['untag_type'] ? $enriched['untag_type'] : '';
		}

		if ($type === 'payment') {
			if ($this->_table_exists('payments')) {
				$this->db->where('statement_id', $id)->update('payments', array(
					'paid' => 0,
					'paid_date' => null,
					'tid_no' => null,
					'paid_challans' => null,
					'merged_challan' => null,
					'statement_id' => null,
				));
			}
			$this->db->where('id', $id)->update('bank_reconciliation_statement', array('statement_id' => null));
			$this->_json(array('success' => true, 'message' => 'Payment untagged'));
		}

		if ($type === 'bank') {
			if (!empty($row['bank_transfer_id'])) {
				$this->db->where('id', (int)$row['bank_transfer_id'])->update('bank_reconciliation_statement', array('bank_transfer_id' => null));
			}
			$this->db->where('id', $id)->update('bank_reconciliation_statement', array('bank_transfer_id' => null));
			$this->_json(array('success' => true, 'message' => 'Bank transfer untagged'));
		}

		if ($type === 'entry') {
			if (empty($row['statement_id']) || !$this->_table_exists('transactions_history')) {
				$this->_json(array('success' => false, 'message' => 'No cash entry to untag'), 400);
			}
			$transaction = $this->db->query(
				'SELECT * FROM transactions_history WHERE id = ? LIMIT 1',
				array((int)$row['statement_id'])
			)->row_array();
			if (!$transaction) {
				$this->db->where('id', $id)->update('bank_reconciliation_statement', array('statement_id' => null));
				$this->_json(array('success' => true, 'message' => 'Cleared orphan statement link'));
			}
			$transaction_account_id = (int)$transaction['transaction_account_id'];
			$account = $this->db->query('SELECT * FROM accounts WHERE id = ? LIMIT 1', array($transaction_account_id))->row_array();
			$balance = $account ? (float)$account['amount'] : 0;
			$debit_amount = (int)str_replace(',', '', (string)$row['debit']);
			if ($debit_amount > 0 && $balance <= $debit_amount) {
				$this->_json(array('success' => false, 'message' => 'There is not enough amount in account.'), 400);
			}
			if ($debit_amount > 0) {
				$this->db->set('amount', "amount - {$debit_amount}", false);
				$this->db->where('id', $transaction_account_id);
				$this->db->update('accounts');
			} else {
				// cash deposit: credit entry — restore cash account
				$credit_amount = (int)str_replace(',', '', (string)$row['credit']);
				if ($credit_amount > 0) {
					$this->db->set('amount', 'amount + ' . $credit_amount, false);
					$this->db->where('id', $transaction_account_id);
					$this->db->update('accounts');
				}
			}
			$this->db->where('id', (int)$row['statement_id'])->delete('transactions_history');
			$this->db->where('id', $id)->update('bank_reconciliation_statement', array('statement_id' => null));
			$this->_json(array('success' => true, 'message' => 'Successfully untagged'));
		}

		if ($type === 'expense') {
			$this->db->where('id', $id)->update('bank_reconciliation_statement', array('expense_id' => null));
			$this->_json(array('success' => true, 'message' => 'Expense untagged'));
		}

		if ($type === 'salary_expense') {
			if ($this->_table_exists('expenses')) {
				$this->db->where('bank_statement_id', $id)->update('expenses', array('bank_statement_id' => null));
			}
			$this->db->where('id', $id)->update('bank_reconciliation_statement', array('salary_expense_ids' => null));
			$this->_json(array('success' => true, 'message' => 'Salary expenses untagged'));
		}

		if ($type === 'paypro') {
			$amount = $this->_brs_num($row['credit']);
			if (!empty($row['paypro_id']) && $this->_table_exists('pay_pro_settlement') && $amount > 0) {
				$this->db->set('tagged_amount', 'GREATEST(0, tagged_amount - ' . $amount . ')', false);
				$this->db->where('id', (int)$row['paypro_id']);
				$this->db->update('pay_pro_settlement');
			}
			$this->db->where('id', $id)->update('bank_reconciliation_statement', array('paypro_id' => null));
			$this->_json(array('success' => true, 'message' => 'PayPro untagged'));
		}

		if ($type === 'closing') {
			$this->db->where('id', $id)->update('bank_reconciliation_statement', array('closing_id' => null));
			$this->_json(array('success' => true, 'message' => 'Closing untagged'));
		}

		if ($type === 'council_fee') {
			$this->db->where('id', $id)->update('bank_reconciliation_statement', array('is_council_fee' => null));
			$this->_json(array('success' => true, 'message' => 'Council fee flag cleared'));
		}

		if ($type === 'profit') {
			if (!empty($row['profit_distribution_id']) && $this->_table_exists('profit_distribution')) {
				$this->db->where('profit_distribution_id', (int)$row['profit_distribution_id'])->update('profit_distribution', array(
					'tagged' => 'no',
					'take_profit' => 0,
				));
			}
			$this->db->where('id', $id)->update('bank_reconciliation_statement', array('profit_distribution_id' => null));
			$this->_json(array('success' => true, 'message' => 'Profit untagged'));
		}

		if ($type === 'loan') {
			$this->db->where('id', $id)->update('bank_reconciliation_statement', array('loan_id' => null));
			$this->_json(array('success' => true, 'message' => 'Loan link cleared (loan record left intact)'));
		}

		if ($type === 'salary_reverse') {
			$this->db->where('id', $id)->update('bank_reconciliation_statement', array(
				'reversal_payroll_id' => null,
				'reversal_payroll_expense_id' => null,
				'reversal_payroll_trans_id' => null,
			));
			$this->_json(array('success' => true, 'message' => 'Salary reverse cleared'));
		}

		$this->_json(array('success' => false, 'message' => 'Unknown or unsupported untag_type: ' . $type), 400);
	}

	/**
	 * Legacy: Accounts::add_expense
	 * POST bank_add_expense — multipart or JSON
	 */
	public function bank_add_expense()
	{
		$body = $this->_body();
		$trans_id = (int)(isset($body['trans_id']) ? $body['trans_id'] : (isset($body['bank_trans_id']) ? $body['bank_trans_id'] : $this->input->post('trans_id')));
		$campus_id = (int)(isset($body['ac_campus_id']) ? $body['ac_campus_id'] : (isset($body['campus_id']) ? $body['campus_id'] : $this->input->post('ac_campus_id')));
		$title = isset($body['title']) ? $body['title'] : $this->input->post('title');
		$purpose = isset($body['reason_disc']) ? $body['reason_disc'] : (isset($body['purpose']) ? $body['purpose'] : $this->input->post('reason_disc'));
		$amount = isset($body['amount']) ? $body['amount'] : $this->input->post('amount');

		$exp_cat = isset($body['expense_category_id']) ? $body['expense_category_id'] : $this->input->post('expense_category_id');
		if (is_array($exp_cat)) {
			$exp_cat = $exp_cat[count($exp_cat) - 1];
		}
		$exp_cat = (int)$exp_cat;

		if ($trans_id <= 0 || $campus_id <= 0 || $exp_cat <= 0 || $title === null || $title === '') {
			$this->_json(array('success' => false, 'message' => 'trans_id, campus, category, title required'), 400);
		}
		if (!$this->_table_exists('expenses')) {
			$this->_json(array('success' => false, 'message' => 'expenses table missing'), 500);
		}

		$brs = $this->db->query('SELECT * FROM bank_reconciliation_statement WHERE id = ?', array($trans_id))->row_array();
		if (!$brs) $this->_json(array('success' => false, 'message' => 'Statement row not found'), 404);
		if ($amount === null || $amount === '') {
			$amount = $this->_brs_num($brs['debit']);
		}

		$image = $this->_upload_proof();
		$this->db->insert('expenses', array(
			'campus_id' => $campus_id,
			'expense_category_id' => $exp_cat,
			'title' => $title,
			'date' => date('Y-m-d'),
			'amount' => $amount,
			'purpose' => $purpose,
			'paid_type' => 'bank',
			'actual_date' => date('Y-m-d H:i:s'),
			'image' => $image,
			'approved_status' => '1',
			'add_by_id' => (int)$this->current_user['user_id'],
			'add_by' => $this->_actor_name(),
		));
		$insert_id = (int)$this->db->insert_id();
		$this->db->where('id', $trans_id)->update('bank_reconciliation_statement', array('expense_id' => $insert_id));

		$expense = $this->db->query(
			"SELECT expenses.*, expense_category.name AS category_name, campuses.campus_name
			 FROM expenses
			 LEFT JOIN expense_category ON expense_category.expense_category_id = expenses.expense_category_id
			 LEFT JOIN campuses ON campuses.campus_id = expenses.campus_id
			 WHERE expenses.expense_id = ? LIMIT 1",
			array($insert_id)
		)->row_array();

		$this->_json(array(
			'success' => true,
			'message' => 'Expense tagged',
			'expense_id' => $insert_id,
			'expense' => $expense,
		));
	}

	/** POST bank_find_mutual — {bank_trans_id, bank_id} */
	public function bank_find_mutual()
	{
		$body = $this->_body();
		$bank_trans_id = (int)(isset($body['bank_trans_id']) ? $body['bank_trans_id'] : 0);
		$bank_id = (int)(isset($body['bank_id']) ? $body['bank_id'] : (isset($body['account_id']) ? $body['account_id'] : 0));
		if ($bank_trans_id <= 0 || $bank_id <= 0) {
			$this->_json(array('success' => false, 'message' => 'bank_trans_id and bank_id required'), 400);
		}
		$transaction = $this->db->query(
			'SELECT * FROM bank_reconciliation_statement WHERE id = ? LIMIT 1',
			array($bank_trans_id)
		)->row_array();
		if (!$transaction) $this->_json(array('success' => false, 'message' => 'Not found'), 404);

		$entries = $this->db->query(
			"SELECT brs.*, brs.id AS tidx, accounts.account_name, accounts.account_title
			 FROM bank_reconciliation_statement brs
			 LEFT JOIN accounts ON accounts.id = brs.account_id
			 WHERE brs.trans_date = ? AND brs.account_id = ? AND brs.debit = ?
			 GROUP BY brs.description, brs.id
			 ORDER BY brs.id ASC",
			array($transaction['trans_date'], $bank_id, $transaction['credit'])
		)->result_array();

		$out = array();
		foreach ($entries as $e) {
			$out[] = array(
				'id' => (int)$e['tidx'],
				'tidx' => (int)$e['tidx'],
				'trans_date' => $e['trans_date'],
				'description' => $e['description'],
				'debit' => $e['debit'],
				'credit' => $e['credit'],
				'balance' => $e['balance'],
				'account_name' => isset($e['account_name']) ? $e['account_name'] : '',
				'account_title' => isset($e['account_title']) ? $e['account_title'] : '',
			);
		}
		$this->_json(array('success' => true, 'entries' => $out));
	}

	/** POST bank_tag_mutual — {bank_trans_id, tag_id} */
	public function bank_tag_mutual()
	{
		$body = $this->_body();
		$trans_id = (int)(isset($body['bank_trans_id']) ? $body['bank_trans_id'] : 0);
		$tag_id = (int)(isset($body['tag_id']) ? $body['tag_id'] : 0);
		if ($trans_id <= 0 || $tag_id <= 0) {
			$this->_json(array('success' => false, 'message' => 'bank_trans_id and tag_id required'), 400);
		}
		$this->db->where('id', $trans_id)->update('bank_reconciliation_statement', array('bank_transfer_id' => $tag_id));
		$this->db->where('id', $tag_id)->update('bank_reconciliation_statement', array('bank_transfer_id' => $trans_id));
		$other = $this->db->query(
			"SELECT brs.*, accounts.account_name FROM bank_reconciliation_statement brs
			 LEFT JOIN accounts ON accounts.id = brs.account_id WHERE brs.id = ?",
			array($tag_id)
		)->row_array();
		$this->_json(array('success' => true, 'message' => 'Bank transfer tagged', 'related' => $other));
	}

	/** POST bank_find_expenses — {bank_trans_id} */
	public function bank_find_expenses()
	{
		$body = $this->_body();
		$bank_trans_id = (int)(isset($body['bank_trans_id']) ? $body['bank_trans_id'] : 0);
		if ($bank_trans_id <= 0) $this->_json(array('success' => false, 'message' => 'bank_trans_id required'), 400);
		$transaction = $this->db->query(
			'SELECT * FROM bank_reconciliation_statement WHERE id = ? LIMIT 1',
			array($bank_trans_id)
		)->row_array();
		if (!$transaction) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		if (!$this->_table_exists('expenses')) {
			$this->_json(array('success' => true, 'entries' => array()));
		}
		$time = strtotime($transaction['trans_date']);
		$date = date('Y-m-01', $time);
		$enddate = date('Y-m-t', $time);
		$entries = $this->db->query(
			"SELECT expenses.*, expense_category.name AS category_name, campuses.campus_name
			 FROM expenses
			 LEFT JOIN expense_category ON expense_category.expense_category_id = expenses.expense_category_id
			 LEFT JOIN campuses ON campuses.campus_id = expenses.campus_id
			 WHERE paid_type = 'bank' AND date >= ? AND date <= ? AND bank_statement_id IS NULL
			 ORDER BY expenses.date ASC, expenses.expense_id ASC",
			array($date, $enddate)
		)->result_array();
		$this->_json(array(
			'success' => true,
			'statement_amount' => $this->_brs_num($transaction['debit']),
			'month_start' => $date,
			'month_end' => $enddate,
			'entries' => $entries,
		));
	}

	/** POST bank_tag_expenses — {bank_trans_id, expense_ids:[]|comma} */
	public function bank_tag_expenses()
	{
		$body = $this->_body();
		$trans_id = (int)(isset($body['bank_trans_id']) ? $body['bank_trans_id'] : 0);
		$ids = isset($body['expense_ids']) ? $body['expense_ids'] : (isset($body['expense_user_ids']) ? $body['expense_user_ids'] : '');
		if (is_string($ids)) $ids = array_filter(array_map('intval', explode(',', $ids)));
		if (!is_array($ids)) $ids = array();
		$ids = array_values(array_filter(array_map('intval', $ids)));
		if ($trans_id <= 0 || !count($ids)) {
			$this->_json(array('success' => false, 'message' => 'bank_trans_id and expense_ids required'), 400);
		}
		$this->db->where_in('expense_id', $ids)->update('expenses', array('bank_statement_id' => $trans_id));
		$this->db->where('id', $trans_id)->update('bank_reconciliation_statement', array(
			'salary_expense_ids' => implode(',', $ids),
		));
		$salary_expenses = $this->db->query(
			"SELECT expenses.*, expense_category.name AS category_name, campuses.campus_name
			 FROM expenses
			 LEFT JOIN expense_category ON expense_category.expense_category_id = expenses.expense_category_id
			 LEFT JOIN campuses ON campuses.campus_id = expenses.campus_id
			 WHERE bank_statement_id = ?",
			array($trans_id)
		)->result_array();
		$this->_json(array('success' => true, 'message' => 'Salary expenses tagged', 'expenses' => $salary_expenses));
	}

	/** POST bank_find_paypro — {bank_trans_id, from_date} */
	public function bank_find_paypro()
	{
		$body = $this->_body();
		$bank_trans_id = (int)(isset($body['bank_trans_id']) ? $body['bank_trans_id'] : 0);
		$from_date = isset($body['from_date']) ? $body['from_date'] : date('Y-m-d');
		if ($bank_trans_id <= 0) $this->_json(array('success' => false, 'message' => 'bank_trans_id required'), 400);
		$transaction = $this->db->query(
			'SELECT * FROM bank_reconciliation_statement WHERE id = ? LIMIT 1',
			array($bank_trans_id)
		)->row_array();
		if (!$transaction) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		if (!$this->_table_exists('pay_pro_settlement')) {
			$this->_json(array('success' => true, 'entries' => array()));
		}
		$amount = (int)$this->_brs_num($transaction['credit']);
		$entries = $this->db->query(
			"SELECT * FROM pay_pro_settlement
			 WHERE settlement_date = ? AND (total_amount - tagged_amount) <= ?
			 ORDER BY id DESC",
			array($from_date, $amount)
		)->result_array();
		$this->_json(array(
			'success' => true,
			'amount' => $amount,
			'entries' => $entries,
		));
	}

	/** POST bank_tag_paypro — {bank_trans_id, tag_id/paypro_id, amount?} */
	public function bank_tag_paypro()
	{
		$body = $this->_body();
		$trans_id = (int)(isset($body['bank_trans_id']) ? $body['bank_trans_id'] : 0);
		$tag_id = (int)(isset($body['tag_id']) ? $body['tag_id'] : (isset($body['paypro_id']) ? $body['paypro_id'] : 0));
		$amount = isset($body['amount']) ? (float)$body['amount'] : 0;
		if ($trans_id <= 0 || $tag_id <= 0) {
			$this->_json(array('success' => false, 'message' => 'bank_trans_id and tag_id required'), 400);
		}
		if ($amount <= 0) {
			$brs = $this->db->query('SELECT credit FROM bank_reconciliation_statement WHERE id = ?', array($trans_id))->row_array();
			$amount = $brs ? $this->_brs_num($brs['credit']) : 0;
		}
		$this->db->where('id', $trans_id)->update('bank_reconciliation_statement', array('paypro_id' => $tag_id));
		if ($this->_table_exists('pay_pro_settlement') && $amount > 0) {
			$this->db->set('tagged_amount', 'tagged_amount + ' . $amount, false);
			$this->db->where('id', $tag_id);
			$this->db->update('pay_pro_settlement');
		}
		$this->_json(array('success' => true, 'message' => 'PayPro tagged'));
	}

	/** POST bank_cash_withdrawal — legacy add_cash_in_hand */
	public function bank_cash_withdrawal()
	{
		$body = $this->_body();
		$trans_id = (int)(isset($body['cash_trans_id']) ? $body['cash_trans_id'] : (isset($body['bank_trans_id']) ? $body['bank_trans_id'] : 0));
		$to = (int)(isset($body['cash_account_id']) ? $body['cash_account_id'] : 0);
		$accountamount = isset($body['amount']) ? (float)$body['amount'] : 0;
		$trasfer_reason = isset($body['reason_disc']) ? $body['reason_disc'] : (isset($body['reason']) ? $body['reason'] : '');
		if ($trans_id <= 0 || $to <= 0) {
			$this->_json(array('success' => false, 'message' => 'cash_trans_id and cash_account_id required'), 400);
		}
		$transaction = $this->db->query(
			'SELECT * FROM bank_reconciliation_statement WHERE id = ? LIMIT 1',
			array($trans_id)
		)->row_array();
		if (!$transaction) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		if ($accountamount <= 0) $accountamount = $this->_brs_num($transaction['debit']);

		$image = $this->_upload_proof();
		$this->db->set('amount', 'amount + ' . $accountamount, false);
		$this->db->where('id', $to);
		$this->db->update('accounts');

		$this->db->insert('transactions_history', array(
			'from_account_id' => $transaction['account_id'],
			'to_account_id' => $to,
			'transaction_account_id' => $to,
			'amount' => $accountamount,
			'debit_credit' => 'D',
			'transaction_by' => $this->_actor_name(),
			'reason' => 'Funds Receive ' . $trasfer_reason,
			'proof_image' => $image,
			'created_at' => $transaction['trans_date'] . ' ' . date('H:i:s'),
		));
		$idf = (int)$this->db->insert_id();
		$this->db->where('id', $trans_id)->update('bank_reconciliation_statement', array('statement_id' => $idf));
		$this->_json(array('success' => true, 'message' => 'Cash withdrawal tagged', 'history_id' => $idf));
	}

	/** POST bank_cash_deposit — legacy add_cash_deposit */
	public function bank_cash_deposit()
	{
		$body = $this->_body();
		$trans_id = (int)(isset($body['cash_trans_id']) ? $body['cash_trans_id'] : (isset($body['bank_trans_id']) ? $body['bank_trans_id'] : 0));
		$cash_account_id = (int)(isset($body['cash_account_id']) ? $body['cash_account_id'] : 0);
		$reason = isset($body['reason_disc']) ? $body['reason_disc'] : (isset($body['reason']) ? $body['reason'] : '');
		$cash_deposit_amount = isset($body['cash_deposit_amount']) ? (float)$body['cash_deposit_amount'] : (isset($body['amount']) ? (float)$body['amount'] : 0);
		if ($trans_id <= 0 || $cash_account_id <= 0) {
			$this->_json(array('success' => false, 'message' => 'cash_trans_id and cash_account_id required'), 400);
		}
		$transaction = $this->db->query(
			'SELECT * FROM bank_reconciliation_statement WHERE id = ? LIMIT 1',
			array($trans_id)
		)->row_array();
		if (!$transaction) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		if ($cash_deposit_amount <= 0) $cash_deposit_amount = $this->_brs_num($transaction['credit']);

		$this->db->insert('transactions_history', array(
			'from_account_id' => $cash_account_id,
			'to_account_id' => $transaction['account_id'],
			'transaction_account_id' => $cash_account_id,
			'amount' => $cash_deposit_amount,
			'debit_credit' => 'C',
			'transaction_by' => $this->_actor_name(),
			'reason' => 'Funds Transfer ' . $reason,
			'proof_image' => '',
			'created_at' => $transaction['trans_date'] . ' ' . date('H:i:s'),
		));
		$idf = (int)$this->db->insert_id();
		$this->db->where('id', $trans_id)->update('bank_reconciliation_statement', array('statement_id' => $idf));
		$this->db->set('amount', 'amount - ' . $cash_deposit_amount, false);
		$this->db->where('id', $cash_account_id);
		$this->db->update('accounts');
		$this->_json(array('success' => true, 'message' => 'Cash deposit tagged', 'history_id' => $idf));
	}

	/** POST bank_council_fee — {trans_id} */
	public function bank_council_fee()
	{
		$body = $this->_body();
		$trans_id = (int)(isset($body['trans_id']) ? $body['trans_id'] : (isset($body['bank_trans_id']) ? $body['bank_trans_id'] : 0));
		if ($trans_id <= 0) $this->_json(array('success' => false, 'message' => 'trans_id required'), 400);
		$this->db->where('id', $trans_id)->update('bank_reconciliation_statement', array('is_council_fee' => '1'));
		$this->_json(array('success' => true, 'message' => 'Selected as Council Fee Not Tagged'));
	}

	/** POST bank_find_profit — {bank_trans_id, amount?} */
	public function bank_find_profit()
	{
		$body = $this->_body();
		$bank_trans_id = (int)(isset($body['bank_trans_id']) ? $body['bank_trans_id'] : 0);
		if ($bank_trans_id <= 0) $this->_json(array('success' => false, 'message' => 'bank_trans_id required'), 400);
		$transaction = $this->db->query(
			'SELECT * FROM bank_reconciliation_statement WHERE id = ? LIMIT 1',
			array($bank_trans_id)
		)->row_array();
		if (!$transaction) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		$amount = isset($body['amount']) ? (int)$body['amount'] : (int)$this->_brs_num($transaction['debit']);
		if (!$this->_table_exists('profit_distribution')) {
			$this->_json(array('success' => true, 'entries' => array()));
		}
		$entries = $this->db->query(
			"SELECT profit_distribution.*, campuses.campus_name, users.first_name, users.last_name
			 FROM profit_distribution
			 JOIN campuses ON campuses.campus_id = profit_distribution.campus_id
			 JOIN users ON users.user_id = profit_distribution.user_id
			 WHERE close_type = 'bank' AND tagged = 'no' AND CAST(amount AS SIGNED INTEGER) = ?
			 ORDER BY profit_distribution_id DESC",
			array($amount)
		)->result_array();
		$this->_json(array('success' => true, 'amount' => $amount, 'entries' => $entries));
	}

	/** POST bank_tag_profit — {bank_trans_id, tag_id} */
	public function bank_tag_profit()
	{
		$body = $this->_body();
		$trans_id = (int)(isset($body['bank_trans_id']) ? $body['bank_trans_id'] : 0);
		$profit_id = (int)(isset($body['tag_id']) ? $body['tag_id'] : (isset($body['profit_distribution_id']) ? $body['profit_distribution_id'] : 0));
		if ($trans_id <= 0 || $profit_id <= 0) {
			$this->_json(array('success' => false, 'message' => 'bank_trans_id and tag_id required'), 400);
		}
		$this->db->where('id', $trans_id)->update('bank_reconciliation_statement', array('profit_distribution_id' => $profit_id));
		$this->db->where('profit_distribution_id', $profit_id)->update('profit_distribution', array(
			'tagged' => 'yes',
			'take_profit' => 1,
		));
		$this->_json(array('success' => true, 'message' => 'Profit distribution tagged'));
	}

	/** POST bank_find_loan — {bank_trans_id, amount?} */
	public function bank_find_loan()
	{
		$body = $this->_body();
		$bank_trans_id = (int)(isset($body['bank_trans_id']) ? $body['bank_trans_id'] : 0);
		if ($bank_trans_id <= 0) $this->_json(array('success' => false, 'message' => 'bank_trans_id required'), 400);
		$transaction = $this->db->query(
			'SELECT * FROM bank_reconciliation_statement WHERE id = ? LIMIT 1',
			array($bank_trans_id)
		)->row_array();
		if (!$transaction) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		$amount = isset($body['amount']) ? (float)$body['amount'] : $this->_brs_num($transaction['debit']);
		if (!$this->_table_exists('loans')) {
			$this->_json(array('success' => true, 'entries' => array()));
		}
		$entries = $this->db->query(
			"SELECT users.first_name, users.last_name, users.cnic, loans.*
			 FROM loans
			 INNER JOIN users ON loans.user_id = users.user_id
			 WHERE loans.status = 1 AND cash_given IS NULL AND amount_approved = ?
			 ORDER BY loans.id DESC",
			array($amount)
		)->result_array();
		$this->_json(array('success' => true, 'amount' => $amount, 'entries' => $entries));
	}

	/** POST bank_tag_loan — {bank_trans_id, tag_id} — mirrors add_loan_deposit */
	public function bank_tag_loan()
	{
		$body = $this->_body();
		$trans_id = (int)(isset($body['bank_trans_id']) ? $body['bank_trans_id'] : 0);
		$loan_id = (int)(isset($body['tag_id']) ? $body['tag_id'] : (isset($body['loan_id']) ? $body['loan_id'] : 0));
		if ($trans_id <= 0 || $loan_id <= 0) {
			$this->_json(array('success' => false, 'message' => 'bank_trans_id and tag_id required'), 400);
		}
		$my_loan = $this->db->query('SELECT * FROM loans WHERE id = ? LIMIT 1', array($loan_id))->row_array();
		if (!$my_loan) $this->_json(array('success' => false, 'message' => 'Loan not found'), 404);

		$this->db->where('id', $trans_id)->update('bank_reconciliation_statement', array('loan_id' => $loan_id));

		$approve_amount = (float)$my_loan['amount_approved'];
		$months = (int)$my_loan['months_approved'];
		if ($months < 1) $months = 1;
		$amountavg = $approve_amount / $months;

		$this->db->where('id', $loan_id)->update('loans', array(
			'cash_given' => $approve_amount,
			'give_through' => 'bank',
			'cash_given_by' => 1,
		));

		$entry = $this->db->query(
			"SELECT users.*, loans.* FROM loans
			 JOIN users ON users.user_id = loans.user_id WHERE loans.id = ?",
			array($loan_id)
		)->row_array();

		$time = date('Y-m-d');
		for ($i = 1; $i <= $months; $i++) {
			if ($my_loan['type'] === 'ADVANCE') {
				$dead_line = $time;
			} else {
				$ts = strtotime(date('Y-m-d'));
				$dead_line = date('Y-m-30', strtotime("+{$i} month", $ts));
			}
			$this->db->insert('loan_plan', array(
				'amount' => $amountavg,
				'due_date' => $dead_line,
				'loan_id' => $loan_id,
				'created_by' => (int)$this->current_user['user_id'],
			));
		}

		if ($this->_table_exists('expenses')) {
			$cat = (isset($my_loan['type']) && $my_loan['type'] === 'ADVANCE') ? '30' : '31';
			$this->db->insert('expenses', array(
				'campus_id' => isset($entry['campus_id']) ? $entry['campus_id'] : null,
				'expense_category_id' => $cat,
				'title' => 'Advance / Loan',
				'date' => date('Y-m-d'),
				'amount' => $approve_amount,
				'purpose' => 'Advance / Loan Given to Employee',
				'user_id' => $my_loan['user_id'],
				'actual_date' => date('Y-m-d H:i:s'),
				'image' => '',
				'approved_status' => '1',
				'paid_type' => 'bank',
				'loan_id' => $loan_id,
				'add_by_id' => (int)$this->current_user['user_id'],
				'add_by' => $this->_actor_name(),
			));
		}

		$this->_json(array(
			'success' => true,
			'message' => 'Loan tagged',
			'loan' => array(
				'id' => $loan_id,
				'name' => $entry ? trim($entry['first_name'] . ' ' . $entry['last_name']) : '',
				'amount' => $approve_amount,
				'months' => $months,
			),
		));
	}

	/** GET/POST bank_find_salaries?expense_id= — payrolls for a salary expense */
	public function bank_find_salaries()
	{
		$body = $this->_body();
		$exp_id = (int)(isset($body['expense_id']) ? $body['expense_id'] : $this->input->get('expense_id'));
		if ($exp_id <= 0) $this->_json(array('success' => false, 'message' => 'expense_id required'), 400);
		if (!$this->_table_exists('payroll')) {
			$this->_json(array('success' => true, 'entries' => array()));
		}
		$entries = $this->db->query(
			"SELECT payroll.*, users.first_name, users.last_name
			 FROM payroll
			 LEFT JOIN users ON users.user_id = payroll.user_id
			 WHERE payroll.expense_id = ?
			 ORDER BY payroll.id DESC",
			array($exp_id)
		)->result_array();
		$this->_json(array('success' => true, 'entries' => $entries));
	}

	/**
	 * POST bank_find_salary_reverse
	 * Without payroll_id: list payrolls matching credit amount of bank_trans_id
	 * With payroll_id + from/to: find matching bank credits (legacy find_reverse_transactions)
	 */
	public function bank_find_salary_reverse()
	{
		$body = $this->_body();
		$bank_trans_id = (int)(isset($body['bank_trans_id']) ? $body['bank_trans_id'] : 0);
		$payroll_id = (int)(isset($body['payroll_id']) ? $body['payroll_id'] : (isset($body['tag_id']) ? $body['tag_id'] : 0));
		$from_date = isset($body['from_date']) ? $body['from_date'] : date('Y-m-01');
		$to_date = isset($body['to_date']) ? $body['to_date'] : date('Y-m-d');
		$expense_id = (int)(isset($body['expense_id']) ? $body['expense_id'] : 0);

		if ($expense_id > 0 && $payroll_id <= 0) {
			if (!$this->_table_exists('payroll')) {
				$this->_json(array('success' => true, 'mode' => 'payrolls', 'entries' => array()));
			}
			$entries = $this->db->query(
				"SELECT payroll.*, users.first_name, users.last_name
				 FROM payroll
				 LEFT JOIN users ON users.user_id = payroll.user_id
				 WHERE payroll.expense_id = ?
				 ORDER BY payroll.id DESC",
				array($expense_id)
			)->result_array();
			$this->_json(array('success' => true, 'mode' => 'payrolls', 'entries' => $entries));
		}

		if ($payroll_id <= 0) {
			if ($bank_trans_id <= 0) $this->_json(array('success' => false, 'message' => 'bank_trans_id or payroll_id required'), 400);
			$transaction = $this->db->query(
				'SELECT * FROM bank_reconciliation_statement WHERE id = ? LIMIT 1',
				array($bank_trans_id)
			)->row_array();
			if (!$transaction) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
			$amount = (int)$this->_brs_num($transaction['credit']);
			$entries = array();
			if ($this->_table_exists('payroll')) {
				$entries = $this->db->query(
					"SELECT payroll.*, users.first_name, users.last_name
					 FROM payroll
					 LEFT JOIN users ON users.user_id = payroll.user_id
					 WHERE CAST(earned_salary AS SIGNED) = ? AND expense_id IS NOT NULL
					 ORDER BY payroll.id DESC LIMIT 100",
					array($amount)
				)->result_array();
			}
			$this->_json(array('success' => true, 'mode' => 'payrolls', 'amount' => $amount, 'entries' => $entries));
		}

		$entry = $this->db->query('SELECT * FROM payroll WHERE id = ? LIMIT 1', array($payroll_id))->row_array();
		if (!$entry) $this->_json(array('success' => false, 'message' => 'Payroll not found'), 404);
		$salary = (int)$this->_brs_num($entry['earned_salary']);
		$entries = $this->db->query(
			"SELECT brs.*, brs.id AS tidx, accounts.account_name, accounts.account_title
			 FROM bank_reconciliation_statement brs
			 LEFT JOIN accounts ON accounts.id = brs.account_id
			 WHERE brs.trans_date >= ? AND brs.trans_date <= ?
			   AND CAST(REPLACE(credit, ',', '') AS SIGNED INTEGER) = ?
			 GROUP BY brs.description, brs.id
			 ORDER BY brs.trans_date ASC",
			array($from_date, $to_date, $salary)
		)->result_array();
		$out = array();
		foreach ($entries as $e) {
			$out[] = array(
				'id' => (int)$e['tidx'],
				'tidx' => (int)$e['tidx'],
				'account_name' => isset($e['account_name']) ? $e['account_name'] : '',
				'account_title' => isset($e['account_title']) ? $e['account_title'] : '',
				'trans_date' => $e['trans_date'],
				'description' => $e['description'],
				'debit' => $e['debit'],
				'credit' => $e['credit'],
				'balance' => $e['balance'],
				'payroll_id' => $payroll_id,
			);
		}
		$this->_json(array(
			'success' => true,
			'mode' => 'bank_entries',
			'payroll' => $entry,
			'entries' => $out,
		));
	}

	/** POST bank_tag_salary_reverse — {bank_trans_id, tag_id, payroll_id} */
	public function bank_tag_salary_reverse()
	{
		$body = $this->_body();
		$trans_id = (int)(isset($body['bank_trans_id']) ? $body['bank_trans_id'] : 0);
		$tag_id = (int)(isset($body['tag_id']) ? $body['tag_id'] : 0);
		$payroll_id = (int)(isset($body['payroll_id']) ? $body['payroll_id'] : 0);
		if ($trans_id <= 0 || $tag_id <= 0 || $payroll_id <= 0) {
			$this->_json(array('success' => false, 'message' => 'bank_trans_id, tag_id, payroll_id required'), 400);
		}
		$entry = $this->db->query('SELECT * FROM payroll WHERE id = ? LIMIT 1', array($payroll_id))->row_array();
		if (!$entry) $this->_json(array('success' => false, 'message' => 'Payroll not found'), 404);

		$this->db->where('id', $tag_id)->update('bank_reconciliation_statement', array(
			'reversal_payroll_id' => $payroll_id,
			'reversal_payroll_expense_id' => $entry['expense_id'],
			'reversal_payroll_trans_id' => $trans_id,
		));
		$this->db->where('id', $payroll_id)->update('payroll', array(
			'expense_id' => null,
			'disburse_through' => 'pending',
		));
		$this->_json(array('success' => true, 'message' => 'Salary reverse tagged'));
	}

	/**
	 * Legacy: Excel_import::index
	 * GET paypro_settlements
	 */
	public function paypro_settlements()
	{
		if (!$this->_table_exists('pay_pro_settlement')) {
			$this->_json(array('success' => true, 'settlements' => array()));
		}
		$rows = $this->db->query(
			"SELECT pay_pro_settlement.*,
					CONCAT(users.first_name,' ',users.last_name) AS created_by_name
			 FROM pay_pro_settlement
			 LEFT JOIN users ON users.user_id = pay_pro_settlement.created_by
			 ORDER BY pay_pro_settlement.settlement_date DESC, pay_pro_settlement.id DESC
			 LIMIT 100"
		)->result_array();
		$this->_json(array('success' => true, 'settlements' => $rows));
	}

	/**
	 * Legacy: Excel_import::entries
	 * GET paypro_settlement_entries?id=
	 */
	public function paypro_settlement_entries()
	{
		$id = (int)$this->input->get('id');
		if ($id <= 0) $this->_json(array('success' => false, 'message' => 'id required'), 400);
		if (!$this->_table_exists('settlement_payments')) {
			$this->_json(array('success' => true, 'entries' => array()));
		}
		$rows = $this->db->query(
			"SELECT settlement_payments.* FROM settlement_payments
			 WHERE settlement_id = ? ORDER BY id ASC",
			array($id)
		)->result_array();
		$this->_json(array('success' => true, 'id' => $id, 'entries' => $rows));
	}

	/**
	 * Legacy: Excel_import::unpaid_entries
	 * GET paypro_untagged
	 */
	public function paypro_untagged()
	{
		if (!$this->_table_exists('students_payments')) {
			$this->_json(array('success' => true, 'entries' => array()));
		}
		$rows = $this->db->query(
			"SELECT students_payments.*, students.first_name, students.last_name, students.roll_no,
					campuses.campus_name, classes.name AS class_name
			 FROM students_payments
			 LEFT JOIN students ON students.student_id = students_payments.student_id
			 LEFT JOIN classes ON classes.class_id = students.class_id
			 LEFT JOIN campuses ON campuses.campus_id = classes.campus_id
			 WHERE students_payments.transaction_status = 'PAID'
			   AND students_payments.settlement_id IS NULL
			 ORDER BY students_payments.payment_id DESC
			 LIMIT 500"
		)->result_array();
		$this->_json(array('success' => true, 'entries' => $rows));
	}

	/**
	 * Legacy: Excel_import::manual_unpay
	 * POST paypro_manual_unpay — {payment_id}
	 */
	public function paypro_manual_unpay()
	{
		$body = $this->_body();
		$payment_id = (int)(isset($body['payment_id']) ? $body['payment_id'] : 0);
		if ($payment_id <= 0) $this->_json(array('success' => false, 'message' => 'payment_id required'), 400);
		$this->db->where('payment_id', $payment_id)->update('students_payments', array('transaction_status' => 'UNPAID'));
		$this->_json(array('success' => true, 'message' => 'Payment marked UNPAID'));
	}

	/**
	 * Legacy: Excel_import::manual_pay
	 * POST paypro_manual_pay — {payment_id}
	 */
	public function paypro_manual_pay()
	{
		$body = $this->_body();
		$payment_id = (int)(isset($body['payment_id']) ? $body['payment_id'] : 0);
		if ($payment_id <= 0) $this->_json(array('success' => false, 'message' => 'payment_id required'), 400);
		$payment = $this->db->query(
			'SELECT * FROM students_payments WHERE payment_id = ? LIMIT 1',
			array($payment_id)
		)->row_array();
		if (!$payment) $this->_json(array('success' => false, 'message' => 'Not found'), 404);
		if (!empty($payment['challan_ids'])) {
			foreach (explode(',', $payment['challan_ids']) as $challan) {
				$challan = trim($challan);
				if ($challan === '') continue;
				$this->db->where('challan_no', $challan)->update('payments', array('paid' => 1));
			}
		}
		$this->db->where('payment_id', $payment_id)->update('students_payments', array(
			'transaction_status' => 'PAID',
			'updated_by' => $this->_actor_name(),
		));
		$this->_json(array('success' => true, 'message' => 'Payment marked PAID'));
	}

	/**
	 * Legacy: Excel_import::paypro_entries
	 * GET paypro_transactions?from_date&to_date=
	 */
	public function paypro_transactions()
	{
		$from = $this->input->get('from_date');
		$to = $this->input->get('to_date');
		if (!$from) $from = date('Y-m-d');
		if (!$to) $to = date('Y-m-d');
		if (!$this->_table_exists('students_payments')) {
			$this->_json(array('success' => true, 'from_date' => $from, 'to_date' => $to, 'transactions' => array()));
		}
		$rows = $this->db->query(
			"SELECT students_payments.*, students.first_name, students.last_name, students.roll_no
			 FROM students_payments
			 INNER JOIN students ON students.student_id = students_payments.student_id
			 WHERE students_payments.created_on >= ? AND students_payments.created_on <= ?
			 ORDER BY students_payments.payment_id DESC
			 LIMIT 1000",
			array($from, $to)
		)->result_array();
		$this->_json(array(
			'success' => true,
			'from_date' => $from,
			'to_date' => $to,
			'transactions' => $rows,
		));
	}

	/* ===================== Phase 4 — profit / misc / loans / bulk / howto ===================== */

	/**
	 * Legacy: Accounts::index + accounts/index view (simplified campus cards)
	 * GET profit_campuses?from=&to=
	 */
	public function profit_campuses()
	{
		$from = $this->input->get('from');
		if (!$from) $from = $this->input->get('from_date');
		$to = $this->input->get('to');
		if (!$to) $to = $this->input->get('to_date');
		if (!$from) $from = date('Y-m-01');
		if (!$to) $to = date('Y-m-d');

		$campuses = $this->db->query(
			'SELECT campus_id, campus_name, campus_code FROM campuses WHERE status = 1 ORDER BY campus_name ASC'
		)->result_array();
		$out = array();
		foreach ($campuses as $c) {
			$cid = (int)$c['campus_id'];
			$expense = 0.0;
			$recovery = 0.0;
			if (function_exists('totalExpense')) {
				$expense = (float)totalExpense($cid, $from, $to);
			} else {
				$row = $this->db->query(
					"SELECT COALESCE(SUM(amount),0) AS amount FROM expenses
					 WHERE campus_id = ? AND actual_date >= ? AND actual_date <= ? AND approved_status = '1'",
					array($cid, $from, $to)
				)->row_array();
				$expense = (float)$row['amount'];
			}
			if (function_exists('totalRecovery')) {
				$recovery = (float)totalRecovery($cid, $from, $to);
			} else {
				$row = $this->db->query(
					"SELECT COALESCE(SUM(actual_amount),0) AS amount FROM payments
					 INNER JOIN students ON students.student_id = payments.student_id
					 INNER JOIN classes ON classes.class_id = students.class_id
					 WHERE classes.campus_id = ? AND payments.actual_paid_date >= ?
					   AND payments.actual_paid_date <= ? AND payments.paid = 1",
					array($cid, $from, $to)
				)->row_array();
				$recovery = (float)$row['amount'];
			}
			$out[] = array(
				'campus_id' => $cid,
				'campus_name' => $c['campus_name'],
				'campus_code' => isset($c['campus_code']) ? $c['campus_code'] : '',
				'total_expense' => $expense,
				'total_recovery' => $recovery,
				'net_profit' => $recovery - $expense,
				'from' => $from,
				'to' => $to,
			);
		}
		$this->_json(array('success' => true, 'from' => $from, 'to' => $to, 'campuses' => $out));
	}

	/**
	 * Legacy: Accounts::campus_profit
	 * GET campus_profit?campus_id&from&to
	 */
	public function campus_profit()
	{
		$campus_id = (int)$this->input->get('campus_id');
		$from = $this->input->get('from');
		if (!$from) $from = $this->input->get('from_date');
		$to = $this->input->get('to');
		if (!$to) $to = $this->input->get('to_date');
		if ($campus_id <= 0) $this->_json(array('success' => false, 'message' => 'campus_id required'), 400);
		if (!$from && function_exists('getFromDateProfitDistribution')) {
			$from = getFromDateProfitDistribution($campus_id);
		}
		if (!$from) $from = date('Y-m-01');
		if (!$to) $to = date('Y-m-d');

		$campus = $this->db->query(
			'SELECT campus_id, campus_name, campus_code FROM campuses WHERE campus_id = ? LIMIT 1',
			array($campus_id)
		)->row_array();

		$expense = function_exists('totalExpense') ? (float)totalExpense($campus_id, $from, $to) : 0;
		$recovery = function_exists('totalRecovery') ? (float)totalRecovery($campus_id, $from, $to) : 0;
		if (function_exists('totalRecoveryContractors')) {
			$recovery += (float)totalRecoveryContractors($campus_id, $from, $to);
		}
		$net = $recovery - $expense;
		$not_approved = 0.0;
		if (function_exists('getNotApprovedExpenses')) {
			$not_approved = (float)getNotApprovedExpenses($campus_id, $from, $to);
		}

		$partners = array();
		if ($this->_table_exists('campus_partners')) {
			$cp = $this->db->query(
				'SELECT partners FROM campus_partners WHERE campus_id = ? LIMIT 1',
				array($campus_id)
			)->row_array();
			$raw = ($cp && !empty($cp['partners'])) ? json_decode($cp['partners'], true) : null;
			if (is_array($raw)) {
				for ($i = 0; $i + 1 < count($raw); $i += 2) {
					$user_id = (int)$raw[$i];
					$pct = (float)$raw[$i + 1];
					if ($user_id <= 0) continue;
					$u = $this->db->query(
						'SELECT first_name, last_name FROM users WHERE user_id = ? LIMIT 1',
						array($user_id)
					)->row_array();
					$partners[] = array(
						'user_id' => $user_id,
						'name' => $u ? trim($u['first_name'] . ' ' . $u['last_name']) : ('User #' . $user_id),
						'percentage' => $pct,
						'amount' => round(($pct / 100.0) * $net, 2),
					);
				}
			}
		}

		$distributions = array();
		if ($this->_table_exists('profit_distribution')) {
			$distributions = $this->db->query(
				"SELECT profit_distribution.*, users.first_name, users.last_name,
						CONCAT(COALESCE(users.first_name,''),' ',COALESCE(users.last_name,'')) AS partner_name
				 FROM profit_distribution
				 LEFT JOIN users ON users.user_id = profit_distribution.user_id
				 WHERE profit_distribution.campus_id = ?
				   AND profit_distribution.to_date >= ? AND profit_distribution.to_date <= ?
				 ORDER BY profit_distribution.to_date DESC",
				array($campus_id, $from, $to)
			)->result_array();
		}
		$this->_json(array(
			'success' => true,
			'campus_id' => $campus_id,
			'campus_name' => $campus ? $campus['campus_name'] : '',
			'campus_code' => $campus && isset($campus['campus_code']) ? $campus['campus_code'] : '',
			'from' => $from,
			'to' => $to,
			'total_expense' => $expense,
			'total_recovery' => $recovery,
			'net_profit' => $net,
			'not_approved_expenses' => $not_approved,
			'can_distribute' => $net > 0 && $not_approved <= 0 && count($partners) > 0,
			'partners' => $partners,
			'distributions' => $distributions,
		));
	}

	/**
	 * Legacy: Accounts::insert_campus_profit (simplified payload)
	 * POST insert_campus_profit — {campus_id, from_date, to_date, total_expense, total_recovery, net_profit, shares:[{user_id,amount,percentage}]}
	 * Also accepts multipart FormData (optional record/image/proof file; shares as JSON string).
	 */
	public function insert_campus_profit()
	{
		$body = $this->_body();
		$campus_id = (int)(isset($body['campus_id']) ? $body['campus_id'] : 0);
		$from_date = isset($body['from_date']) ? $body['from_date'] : (isset($body['from']) ? $body['from'] : '');
		$to_date = isset($body['to_date']) ? $body['to_date'] : (isset($body['to']) ? $body['to'] : '');
		if ($campus_id <= 0 || !$from_date || !$to_date) {
			$this->_json(array('success' => false, 'message' => 'campus_id, from_date, to_date required'), 400);
		}
		$total_expense = (float)(isset($body['total_expense']) ? $body['total_expense'] : 0);
		$total_recovery = (float)(isset($body['total_recovery']) ? $body['total_recovery'] : 0);
		$net_profit = (float)(isset($body['net_profit']) ? $body['net_profit'] : ($total_recovery - $total_expense));
		$shares = isset($body['shares']) ? $body['shares'] : array();
		if (is_string($shares)) {
			$decoded = json_decode($shares, true);
			$shares = is_array($decoded) ? $decoded : array();
		}
		if (!is_array($shares)) $shares = array();
		$type = isset($body['close_type']) ? $body['close_type'] : (isset($body['section']) ? $body['section'] : 'bank');
		$tagged = ($type === 'cash') ? 'yes' : 'no';
		$record = $this->_upload_proof();

		$ids = array();
		foreach ($shares as $share) {
			$user_id = (int)(isset($share['user_id']) ? $share['user_id'] : 0);
			if ($user_id <= 0) continue;
			$row = array(
				'campus_id' => $campus_id,
				'from_date' => $from_date,
				'to_date' => $to_date,
				'total_expense' => $total_expense,
				'total_recovery' => $total_recovery,
				'net_profit' => $net_profit,
				'user_id' => $user_id,
				'amount' => (float)(isset($share['amount']) ? $share['amount'] : 0),
				'percentage' => (float)(isset($share['percentage']) ? $share['percentage'] : 0),
				'close_type' => $type,
				'tagged' => $tagged,
			);
			if ($record !== '') $row['record'] = $record;
			$this->db->insert('profit_distribution', $row);
			$ids[] = (int)$this->db->insert_id();
		}
		if (!count($ids)) {
			$this->_json(array('success' => false, 'message' => 'No partner shares to save'), 400);
		}
		if ($this->_table_exists('profit_distribution_date')) {
			$this->db->insert('profit_distribution_date', array(
				'campus_id' => $campus_id,
				'date' => $to_date,
			));
		}
		$this->_json(array('success' => true, 'message' => 'Profit distribution saved', 'ids' => $ids));
	}

	/**
	 * Legacy: Accounts::add_misc_income / insert_misc_income
	 * GET/POST misc_incomes
	 */
	public function misc_incomes()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$body = $this->_body();
			$account = (int)(isset($body['account_id']) ? $body['account_id'] : 0);
			$title = isset($body['title']) ? $body['title'] : '';
			$description = isset($body['description']) ? $body['description'] : '';
			$amount = (float)(isset($body['amount']) ? $body['amount'] : 0);
			if ($account <= 0 || $amount <= 0 || $title === '') {
				$this->_json(array('success' => false, 'message' => 'account_id, title, amount required'), 400);
			}
			$picture = $this->_upload_proof();
			$this->db->insert('misc_incomes', array(
				'title' => $title,
				'description' => $description,
				'amount' => $amount,
				'account_id' => $account,
				'image' => $picture,
			));
			$misc_id = $this->db->insert_id();
			$date = date('Y-m-d');
			if ($this->_table_exists('transactions_history')) {
				$this->db->insert('transactions_history', array(
					'misc_id' => $misc_id,
					'to_account_id' => $account,
					'amount' => $amount,
					'debit_credit' => 'D',
					'proof_image' => $picture,
					'transaction_by' => $this->_actor_name(),
					'transaction_account_id' => $account,
					'reason' => "Miscellaneous Income ($title - $description - $date)",
					'created_at' => date('Y-m-d H:i:s'),
				));
			}
			$this->db->set('amount', 'amount + ' . $amount, false);
			$this->db->where('id', $account);
			$this->db->update('accounts');
			$this->_json(array('success' => true, 'id' => (int)$misc_id, 'message' => 'Misc income added'));
		}

		if (!$this->_table_exists('misc_incomes')) {
			$this->_json(array('success' => true, 'incomes' => array()));
		}
		$rows = $this->db->query(
			"SELECT misc_incomes.*, misc_incomes.amount AS amount, accounts.account_name
			 FROM misc_incomes
			 LEFT JOIN accounts ON accounts.id = misc_incomes.account_id
			 ORDER BY misc_incomes.id DESC
			 LIMIT 200"
		)->result_array();
		$this->_json(array('success' => true, 'incomes' => $rows));
	}

	/**
	 * Legacy: Loans::accounts_loans_list
	 * GET loans_accounts_queue
	 */
	public function loans_accounts_queue()
	{
		if (!$this->_table_exists('loans')) {
			$this->_json(array('success' => true, 'loans' => array()));
		}
		$rows = $this->db->query(
			"SELECT loans.*,
					users.first_name, users.last_name, users.cnic, users.mobile, users.emergency_no,
					users.campus_id AS user_campus_id,
					CONCAT(users.first_name,' ',users.last_name) AS staff_name,
					CONCAT(COALESCE(approver.first_name,''),' ',COALESCE(approver.last_name,'')) AS approved_by_name
			 FROM loans
			 INNER JOIN users ON loans.user_id = users.user_id
			 LEFT JOIN users approver ON approver.user_id = loans.updated_by
			 WHERE loans.status = 1 AND loans.cash_given IS NULL
			 ORDER BY loans.id DESC"
		)->result_array();
		$out = array();
		foreach ($rows as $row) {
			$status = isset($row['status']) ? (string)$row['status'] : '';
			$status_label = 'PENDING';
			if ($status === '2') $status_label = 'REJECTED';
			elseif ($status === '1') $status_label = 'APPROVED';
			elseif ($status === '0') $status_label = 'PENDING';
			$row['status_label'] = $status_label;
			$row['months_applied'] = isset($row['months']) ? $row['months'] : (isset($row['in_month']) ? $row['in_month'] : null);
			$row['amount_applied'] = isset($row['amount_applied']) ? $row['amount_applied'] : (isset($row['amount']) ? $row['amount'] : 0);
			$row['detail_url'] = site_url('loans/loans_detail_view/' . (int)$row['id']);
			$out[] = $row;
		}
		$this->_json(array('success' => true, 'loans' => $out));
	}

	/**
	 * Legacy: Loans::loans_accounts_approval
	 * POST loans_accounts_approve — {id, amount_given?, in_month?}
	 */
	public function loans_accounts_approve()
	{
		$body = $this->_body();
		$loan_id = (int)(isset($body['id']) ? $body['id'] : (isset($body['loan_id']) ? $body['loan_id'] : 0));
		if ($loan_id <= 0) $this->_json(array('success' => false, 'message' => 'id required'), 400);

		$loan = $this->db->query('SELECT * FROM loans WHERE id = ? LIMIT 1', array($loan_id))->row_array();
		if (!$loan) $this->_json(array('success' => false, 'message' => 'Loan not found'), 404);

		$default_amount = isset($loan['amount_approved']) && $loan['amount_approved'] !== '' && $loan['amount_approved'] !== null
			? (float)$loan['amount_approved']
			: (isset($loan['amount']) ? (float)$loan['amount'] : 0);
		$default_months = isset($loan['months_approved']) && (int)$loan['months_approved'] > 0
			? (int)$loan['months_approved']
			: (isset($loan['months']) && (int)$loan['months'] > 0
				? (int)$loan['months']
				: (isset($loan['in_month']) ? (int)$loan['in_month'] : 1));
		$approve_amount = isset($body['amount_given']) ? (float)$body['amount_given'] : $default_amount;
		$months = isset($body['in_month']) ? (int)$body['in_month'] : $default_months;
		if ($months < 1) $months = 1;
		if ($approve_amount <= 0) {
			$this->_json(array('success' => false, 'message' => 'amount_given required'), 400);
		}

		$uid = (int)$this->current_user['user_id'];
		$petty = $this->db->query(
			"SELECT * FROM petty_cash_college_wise WHERE assign_to = ? AND petty_status = '1' LIMIT 1",
			array($uid)
		)->row_array();
		$petty_bal = $petty ? $this->_petty_live_balance((int)$petty['id']) : 0;
		if ($petty_bal < $approve_amount) {
			$this->_json(array('success' => false, 'message' => 'You do not have enough petty cash'), 400);
		}

		$amountavg = $approve_amount / $months;
		$this->db->where('id', $loan_id)->update('loans', array(
			'cash_given' => $approve_amount,
			'give_through' => 'cash',
			'cash_given_by' => $uid,
		));

		$time = date('Y-m-d');
		for ($i = 1; $i <= $months; $i++) {
			if (isset($loan['type']) && $loan['type'] === 'ADVANCE') {
				$dead_line = $time;
			} else {
				$dead_line = date('Y-m-d', strtotime("+$i month", strtotime($time)));
			}
			$this->db->insert('loan_plan', array(
				'amount' => $amountavg,
				'due_date' => $dead_line,
				'loan_id' => $loan_id,
				'created_by' => $uid,
			));
		}

		$campus_id = isset($this->current_user['campus_id']) ? (int)$this->current_user['campus_id'] : 0;
		$cat = (isset($loan['type']) && $loan['type'] === 'ADVANCE') ? '30' : '31';
		$this->db->insert('expenses', array(
			'campus_id' => $campus_id,
			'expense_category_id' => $cat,
			'title' => 'Advance / Loan',
			'date' => date('Y-m-d'),
			'amount' => $approve_amount,
			'purpose' => 'Advance / Loan Given to Employee',
			'user_id' => (int)$loan['user_id'],
			'loan_id' => $loan_id,
			'actual_date' => date('Y-m-d H:i:s'),
			'image' => '',
			'approved_status' => '1',
			'add_by_id' => $uid,
			'add_by' => $this->_actor_name(),
		));

		$this->db->set('remaining_amount', 'remaining_amount - ' . $approve_amount, false);
		$this->db->where('assign_to', $uid);
		$this->db->update('petty_cash_college_wise');

		$this->_json(array('success' => true, 'message' => 'Loan approved', 'id' => $loan_id));
	}

	/**
	 * Legacy: Accounts::advance
	 * GET advance_staff
	 */
	public function advance_staff()
	{
		$rows = $this->db->query(
			"SELECT user_id, first_name, last_name, campus_id, designation_id, department_id, status,
					CONCAT(first_name,' ',last_name) AS name
			 FROM users
			 WHERE status = '1'
			 ORDER BY first_name ASC, last_name ASC
			 LIMIT 2000"
		)->result_array();
		$this->_json(array('success' => true, 'staff' => $rows));
	}

	/**
	 * Legacy: Accounts::bulk_fee_creator
	 * GET bulk_fee_meta
	 */
	public function bulk_fee_meta()
	{
		$campuses = $this->db->query(
			'SELECT campus_id, campus_name FROM campuses WHERE status = 1 ORDER BY campus_name ASC'
		)->result_array();
		$classes = $this->db->query(
			'SELECT class_id, name, campus_id, course_id FROM classes ORDER BY name ASC'
		)->result_array();
		$this->_json(array(
			'success' => true,
			'meta' => array(
				'campuses' => $campuses,
				'classes' => $classes,
				'fee_types' => array('College Fee', 'Extra Fee', 'consulation fee'),
			),
		));
	}

	/**
	 * Legacy: Accounts::bulk_fee_creation
	 * POST bulk_fee_create — {class_id, fee_type, amount, dead_line, payment_comment?, special_comment?, fee_for?, exam_no?}
	 */
	public function bulk_fee_create()
	{
		$body = $this->_body();
		$class_id = (int)(isset($body['class_id']) ? $body['class_id'] : 0);
		$fee_type = isset($body['fee_type']) ? $body['fee_type'] : 'Extra Fee';
		$amount = (float)(isset($body['amount']) ? $body['amount'] : (isset($body['extra_fee']) ? $body['extra_fee'] : 0));
		$dead_line = isset($body['dead_line']) ? $body['dead_line'] : (isset($body['extra_fee_dead_line']) ? $body['extra_fee_dead_line'] : date('Y-m-d'));
		if ($class_id <= 0 || $amount <= 0) {
			$this->_json(array('success' => false, 'message' => 'class_id and amount required'), 400);
		}
		$students = $this->db->query(
			'SELECT student_id FROM students WHERE class_id = ?',
			array($class_id)
		)->result_array();
		$created = 0;
		foreach ($students as $student) {
			$challan = $this->_new_challan_no();
			$comment = isset($body['payment_comment']) ? $body['payment_comment'] : '';
			$plan = 'Custom Plan';
			if ($fee_type === 'Extra Fee' && !empty($body['fee_for'])) {
				$comment .= ' For ' . $body['fee_for'];
			}
			if ($fee_type === 'consulation fee') {
				$plan = 'consulation fee';
				$comment = 'This fee for next exam # ' . (isset($body['exam_no']) ? $body['exam_no'] : '') . ' ' . (isset($body['class']) ? $body['class'] : '');
			}
			$this->db->insert('payments', array(
				'amount' => $amount,
				'dead_line' => $dead_line,
				'student_id' => (int)$student['student_id'],
				'payment_plan' => $plan,
				'payment_comment' => $comment,
				'special_comment' => isset($body['special_comment']) ? $body['special_comment'] : '',
				'challan_no' => $challan,
				'add_by' => $this->_actor_name(),
				'last_edit' => $this->_actor_name(),
			));
			$created++;
		}
		$this->_json(array('success' => true, 'message' => "Created $created payments", 'created' => $created));
	}

	private function _new_challan_no()
	{
		for ($i = 0; $i < 20; $i++) {
			$n = mt_rand(1000, 999999999);
			$exists = $this->db->query('SELECT id FROM payments WHERE challan_no = ? LIMIT 1', array($n))->row_array();
			if (!$exists) return $n;
		}
		return mt_rand(100000000, 999999999);
	}

	/**
	 * Legacy: Mobile_application::add_how_to_use / insert_how_to_use (module=accounts)
	 * GET/POST how_to_use
	 */
	public function how_to_use()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$body = $this->_body();
			$title = isset($body['title']) ? $body['title'] : '';
			$detail = isset($body['detail']) ? $body['detail'] : (isset($body['description']) ? $body['description'] : '');
			if ($title === '') $this->_json(array('success' => false, 'message' => 'title required'), 400);
			$row = array(
				'module' => 'accounts',
				'title' => $title,
			);
			if ($this->_field_exists('how_to_use', 'detail')) $row['detail'] = $detail;
			elseif ($this->_field_exists('how_to_use', 'description')) $row['description'] = $detail;
			if ($this->_field_exists('how_to_use', 'created_at')) $row['created_at'] = date('Y-m-d H:i:s');
			$this->db->insert('how_to_use', $row);
			$this->_json(array('success' => true, 'id' => (int)$this->db->insert_id(), 'message' => 'Added'));
		}
		if (!$this->_table_exists('how_to_use')) {
			$this->_json(array('success' => true, 'items' => array()));
		}
		$rows = $this->db->query(
			"SELECT * FROM how_to_use WHERE module = 'accounts' ORDER BY id DESC"
		)->result_array();
		$this->_json(array('success' => true, 'items' => $rows));
	}
}

