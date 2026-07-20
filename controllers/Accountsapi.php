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

	/** Save multipart image/proof to uploads/; returns filename or '' */
	private function _upload_proof()
	{
		$fileKey = null;
		if (!empty($_FILES['image']['name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
			$fileKey = 'image';
		} elseif (!empty($_FILES['proof']['name']) && is_uploaded_file($_FILES['proof']['tmp_name'])) {
			$fileKey = 'proof';
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

		// Fee payments (college) — today + yesterday orphans if yesterday was closed
		$dates = array($date);
		if ($yest_closed) $dates[] = $yesterday;

		foreach ($dates as $d) {
			$only_unclosed = ($d === $yesterday);
			$merged = $this->db->query(
				"SELECT payments.* FROM payments
				 WHERE submitted_fee_campus_id = ?
				   AND merged_challan IS NOT NULL AND actual_amount > 0
				   AND fee_pay_through = 'college'
				   AND actual_paid_date = ?"
				 . ($only_unclosed ? " AND closing_id IS NULL" : "")
				 . " GROUP BY CASE WHEN merged_challan IS NOT NULL THEN merged_challan ELSE '' END",
				array($campus_id, $d)
			)->result_array();
			$single = $this->db->query(
				"SELECT payments.* FROM payments
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
				$sql = "SELECT * FROM loan_plan
						WHERE paid_date = ? AND campus_id = ? AND paid_at = 'cash'";
				$params = array($d, $campus_id);
				if ($only_unclosed && $this->_field_exists('loan_plan', 'closing_id')) {
					$sql .= ' AND closing_id IS NULL';
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
		if ($yest_closed) {
			$pos_amount += $this->_pos_sales_sum($campus_id, $yesterday, true);
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
		$persons = $this->_closing_persons_rows(true);
		$out = array();
		foreach ($persons as $row) {
			$campus_id = (int)$row['campus_id'];
			$closed = $this->_closing_for_date($campus_id, $date);
			$item = array(
				'person_id' => isset($row['id']) ? (int)$row['id'] : 0,
				'campus_id' => $campus_id,
				'campus_name' => isset($row['campus_name']) ? $row['campus_name'] : '',
				'user_id' => isset($row['user_id']) ? (int)$row['user_id'] : 0,
				'person_name' => trim(isset($row['person_name']) ? $row['person_name'] : ''),
				'date' => $date,
			);
			if ($closed) {
				$item['closed'] = true;
				$item['closed_status'] = '1';
				$item['closing_id'] = (int)$closed['id'];
				$item['campus_closing_id'] = $closed['campus_closing_id'];
				$item['closing_amount'] = (float)$closed['closed_amount'];
				$item['close_type'] = $closed['close_type'];
				$item['closed_by'] = $closed['closed_by'];
				$item['checked_by'] = $closed['checked_by'];
				$item['account_id'] = $closed['account_id'];
				$item['transaction_no'] = $closed['transaction_no'];
			} else {
				$bundle = $this->_campus_closing_bundle($campus_id, $date);
				$item['closed'] = false;
				$item['closed_status'] = '0';
				$item['closing_id'] = null;
				$item['campus_closing_id'] = null;
				$item['closing_amount'] = $bundle['total'];
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
			}
			$out[] = $item;
		}
		$this->_json(array('success' => true, 'date' => $date, 'closings' => $out));
	}

	/**
	 * Legacy: Closing::closenow
	 * POST close_day — body campus_id, date, close_type?
	 */
	public function close_day()
	{
		$body = $this->_body();
		$campus_id = (int)(isset($body['campus_id']) ? $body['campus_id'] : 0);
		$date = isset($body['date']) ? $body['date'] : date('Y-m-d');
		$close_type = isset($body['close_type']) ? $body['close_type'] : '2';
		if ($campus_id <= 0) $this->_json(array('success' => false, 'message' => 'campus_id required'), 400);

		$existing = $this->_closing_for_date($campus_id, $date);
		if ($existing) {
			$this->_json(array('success' => false, 'message' => 'Closing already exists for this campus/date', 'closing_id' => (int)$existing['id']), 409);
		}

		$bundle = $this->_campus_closing_bundle($campus_id, $date);
		$total = isset($body['receivable_amount']) ? (float)$body['receivable_amount'] : $bundle['total'];
		$fee_ids = !empty($body['fee_ids']) ? $body['fee_ids'] : $bundle['fee_ids'];
		$sale_ids = !empty($body['sale_ids']) ? $body['sale_ids'] : $bundle['sale_ids'];
		$loan_ids = !empty($body['loan_ids']) ? $body['loan_ids'] : $bundle['loan_ids'];

		$day = date('d', strtotime($date));
		$month = date('m', strtotime($date));
		$year = date('Y', strtotime($date));
		$campus_closing_id = $this->_next_campus_closing_id($campus_id);

		$this->db->trans_start();

		if (count($fee_ids)) {
			$this->db->where_in('id', $fee_ids)->set('closing_id', $campus_closing_id)->update('payments');
			// Mark sibling merged challans
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
			'closed_amount' => $total,
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
			if ($this->_table_exists('asset_sales')) {
				$assets = $this->db->query(
					"SELECT asset_sales.* FROM asset_sales WHERE closing_id = ?",
					array($ccid)
				)->result_array();
			}
			$loans = array();
			if ($this->_table_exists('loan_plan')) {
				$loans = $this->db->query(
					"SELECT loan_plan.* FROM loan_plan WHERE closing_id = ?",
					array($ccid)
				)->result_array();
			}
			$this->_json(array(
				'success' => true,
				'closed' => true,
				'date' => $date,
				'campus_id' => $campus_id,
				'closing' => $closed,
				'fees' => $fees,
				'asset_sales' => $assets,
				'loans' => $loans,
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
	 * Legacy: Closing::accountsclosing (awaiting verify)
	 * GET closings_conciliation?date=
	 */
	public function closings_conciliation()
	{
		$date = $this->input->get('date');
		if (!$date) $date = date('Y-m-d');
		$from = $this->input->get('from_date');
		$to = $this->input->get('to_date');
		if (!$from) $from = $date;
		if (!$to) $to = $date;

		$sql = "SELECT closing_perday.*, campuses.campus_name
				FROM closing_perday
				LEFT JOIN campuses ON campuses.campus_id = closing_perday.campus_id
				WHERE STR_TO_DATE(CONCAT(closing_perday.for_year,'-',closing_perday.for_month,'-',closing_perday.for_day), '%Y-%m-%d') >= ?
				  AND STR_TO_DATE(CONCAT(closing_perday.for_year,'-',closing_perday.for_month,'-',closing_perday.for_day), '%Y-%m-%d') <= ?";
		$params = array($from, $to);
		if ($this->_field_exists('closing_perday', 'checked_by')) {
			$sql .= ' AND (closing_perday.checked_by IS NULL OR closing_perday.checked_by = \'\' OR closing_perday.checked_by = \'0\')';
		}
		$sql .= ' ORDER BY closing_perday.id DESC';
		$rows = $this->db->query($sql, $params)->result_array();
		$this->_json(array('success' => true, 'date' => $date, 'from_date' => $from, 'to_date' => $to, 'rows' => $rows));
	}

	/**
	 * Legacy: Closing::verify_closing_now
	 * POST verify_closing — closing_id or campus_id+date; amount optional
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

		$amount = isset($body['amount']) ? (float)$body['amount'] : (float)$check['closed_amount'];

		if ((string)$check['close_type'] === '2' && $this->_table_exists('college_closing_rules')) {
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

	/**
	 * Legacy: Accounts bank statement list / Reports bank recon views
	 * GET bank_statement?from_date&to_date&account_id&tagged=0|1|
	 */
	public function bank_statement()
	{
		$from = $this->input->get('from_date');
		$to = $this->input->get('to_date');
		$account_id = $this->input->get('account_id');
		$tagged = $this->input->get('tagged');
		if (!$from) $from = date('Y-m-01');
		if (!$to) $to = date('Y-m-d');

		if (!$this->_table_exists('bank_reconciliation_statement')) {
			$this->_json(array('success' => true, 'from_date' => $from, 'to_date' => $to, 'entries' => array()));
		}

		$sql = "SELECT brs.*, accounts.account_name
				FROM bank_reconciliation_statement brs
				LEFT JOIN accounts ON accounts.id = brs.account_id
				WHERE brs.trans_date >= ? AND brs.trans_date <= ?";
		$params = array($from, $to);
		if ($account_id !== null && $account_id !== '') {
			$sql .= ' AND brs.account_id = ?';
			$params[] = (int)$account_id;
		}
		if ($tagged === '0' || $tagged === 0) {
			$sql .= ' AND brs.closing_id IS NULL AND (brs.bank_transfer_id IS NULL OR brs.bank_transfer_id = 0)
					  AND (brs.statement_id IS NULL OR brs.statement_id = 0)
					  AND (brs.paypro_id IS NULL OR brs.paypro_id = 0)';
		} elseif ($tagged === '1' || $tagged === 1) {
			$sql .= ' AND (brs.closing_id IS NOT NULL OR brs.bank_transfer_id IS NOT NULL
					  OR brs.statement_id IS NOT NULL OR brs.paypro_id IS NOT NULL)';
		}
		$sql .= ' ORDER BY brs.trans_date ASC, brs.id ASC LIMIT 2000';
		$entries = $this->db->query($sql, $params)->result_array();
		$this->_json(array(
			'success' => true,
			'from_date' => $from,
			'to_date' => $to,
			'account_id' => $account_id,
			'tagged' => $tagged,
			'entries' => $entries,
		));
	}

	/**
	 * Legacy: Accounts::upload_bank_statement (JSON lines preferred for SPA)
	 * POST upload_bank_statement — multipart file+account_id OR JSON {account_id, lines:[{trans_date,description,debit,credit,balance}]}
	 */
	public function upload_bank_statement()
	{
		$body = $this->_body();
		$account_id = (int)(isset($body['account_id']) ? $body['account_id'] : $this->input->post('account_id'));
		if ($account_id <= 0) $this->_json(array('success' => false, 'message' => 'account_id required'), 400);

		$lines = array();
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
			@move_uploaded_file($tmp, $dir . $stored);

			// Simple CSV: trans_date,description,debit,credit,balance
			$fh = @fopen($dir . $stored, 'r');
			if ($fh) {
				$header = fgetcsv($fh);
				while (($row = fgetcsv($fh)) !== false) {
					if (!count(array_filter($row))) continue;
					// Heuristic: if first cell looks like date
					$date = isset($row[0]) ? date('Y-m-d', strtotime($row[0])) : null;
					if (!$date || $date === '1970-01-01') continue;
					$lines[] = array(
						'trans_date' => $date,
						'description' => isset($row[1]) ? $row[1] : (isset($row[2]) ? $row[2] : ''),
						'debit' => isset($row[2]) ? str_replace(array(',', '-'), '', $row[2]) : (isset($row[3]) ? str_replace(',', '', $row[3]) : ''),
						'credit' => isset($row[3]) ? str_replace(array(',', '-'), '', $row[3]) : (isset($row[4]) ? str_replace(',', '', $row[4]) : ''),
						'balance' => isset($row[4]) ? $row[4] : (isset($row[5]) ? $row[5] : ''),
					);
				}
				fclose($fh);
			}
			$file_name = $stored;
		} else {
			$this->_json(array('success' => false, 'message' => 'Provide lines[] JSON or CSV file'), 400);
		}

		$statement_no = null;
		if ($this->_table_exists('statement_upload_record')) {
			$this->db->insert('statement_upload_record', array(
				'date' => date('Y-m-d'),
				'account_id' => $account_id,
				'file' => isset($file_name) ? $file_name : 'json_upload',
				'add_by' => $this->_actor_name(),
			));
			$statement_no = $this->db->insert_id();
		}

		$inserted = 0;
		foreach ($lines as $line) {
			$trans_date = isset($line['trans_date']) ? date('Y-m-d', strtotime($line['trans_date'])) : null;
			if (!$trans_date) continue;
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
	 * Legacy: Closing::tag_bank_trans + Accounts::tag_bank_trans
	 * POST tag_bank_trans — {type, id/tag_id, closing_id/bank_trans_id, ...}
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

		if ($type === 'bank_transfer' || $type === 'transfer') {
			$trans_id = (int)(isset($body['bank_trans_id']) ? $body['bank_trans_id'] : (isset($body['related_id']) ? $body['related_id'] : 0));
			if ($tag_id <= 0 || $trans_id <= 0) {
				$this->_json(array('success' => false, 'message' => 'tag_id and bank_trans_id required'), 400);
			}
			$this->db->where('id', $trans_id)->update('bank_reconciliation_statement', array('bank_transfer_id' => $tag_id));
			$this->db->where('id', $tag_id)->update('bank_reconciliation_statement', array('bank_transfer_id' => $trans_id));
			$this->_json(array('success' => true, 'message' => 'Bank transfer tagged'));
		}

		if ($type === 'paypro') {
			$paypro_id = (int)(isset($body['paypro_id']) ? $body['paypro_id'] : (isset($body['related_id']) ? $body['related_id'] : 0));
			$amount = isset($body['amount']) ? (float)$body['amount'] : 0;
			if ($tag_id <= 0 || $paypro_id <= 0) {
				$this->_json(array('success' => false, 'message' => 'id and paypro_id required'), 400);
			}
			$this->db->where('id', $tag_id)->update('bank_reconciliation_statement', array('paypro_id' => $paypro_id));
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
		if (!$from) $from = date('Y-m-01');
		if (!$to) $to = date('Y-m-d');

		$expense = function_exists('totalExpense') ? (float)totalExpense($campus_id, $from, $to) : 0;
		$recovery = function_exists('totalRecovery') ? (float)totalRecovery($campus_id, $from, $to) : 0;
		$distributions = array();
		if ($this->_table_exists('profit_distribution')) {
			$distributions = $this->db->query(
				"SELECT profit_distribution.*, users.first_name, users.last_name
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
			'from' => $from,
			'to' => $to,
			'total_expense' => $expense,
			'total_recovery' => $recovery,
			'net_profit' => $recovery - $expense,
			'distributions' => $distributions,
		));
	}

	/**
	 * Legacy: Accounts::insert_campus_profit (simplified payload)
	 * POST insert_campus_profit — {campus_id, from_date, to_date, total_expense, total_recovery, net_profit, shares:[{user_id,amount,percentage}]}
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
		$shares = isset($body['shares']) && is_array($body['shares']) ? $body['shares'] : array();
		$type = isset($body['close_type']) ? $body['close_type'] : (isset($body['section']) ? $body['section'] : 'bank');
		$tagged = ($type === 'cash') ? 'yes' : 'no';

		$ids = array();
		foreach ($shares as $share) {
			$user_id = (int)(isset($share['user_id']) ? $share['user_id'] : 0);
			if ($user_id <= 0) continue;
			$this->db->insert('profit_distribution', array(
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
			));
			$ids[] = (int)$this->db->insert_id();
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
			"SELECT loans.*, users.first_name, users.last_name, users.campus_id AS user_campus_id,
					CONCAT(users.first_name,' ',users.last_name) AS staff_name
			 FROM loans
			 INNER JOIN users ON loans.user_id = users.user_id
			 WHERE loans.status = 1 AND loans.cash_given IS NULL
			 ORDER BY loans.id DESC"
		)->result_array();
		$this->_json(array('success' => true, 'loans' => $rows));
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

		$approve_amount = isset($body['amount_given']) ? (float)$body['amount_given'] : (float)$loan['amount'];
		$months = isset($body['in_month']) ? (int)$body['in_month'] : (isset($loan['in_month']) ? (int)$loan['in_month'] : 1);
		if ($months < 1) $months = 1;

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

