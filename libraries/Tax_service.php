<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tax_service {

    /** @var CI_Controller */
    private $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->helper('custom');
    }

    public function can_manage($user)
    {
        return $user && $user['role'] === 'Admin';
    }

    public function meta()
    {
        $accounts = $this->ci->db->query('SELECT id, account_title, account_name FROM accounts WHERE type = "1"')->result_array();
        $campuses = $this->ci->db->where('status', 1)->order_by('campus_name', 'ASC')->get('campuses')->result_array();
        $head_categories = $this->ci->db->where('sub_of IS NULL', NULL, FALSE)->order_by('name', 'ASC')->get('expense_category')->result_array();
        $leaf_categories = $this->ci->db->where('has_sub', 0)->order_by('name', 'ASC')->get('expense_category')->result_array();
        return array(
            'accounts' => $accounts,
            'campuses' => $campuses,
            'head_categories' => $head_categories,
            'leaf_categories' => $leaf_categories,
        );
    }

    public function bank_report($from_date, $to_date, $account_ids)
    {
        if (!$from_date || !$to_date || empty($account_ids) || !is_array($account_ids)) {
            return array('success' => false, 'message' => 'From date, to date and accounts required');
        }
        $accounts = $this->ci->db->where_in('id', $account_ids)->get('accounts')->result_array();
        foreach ($accounts as $key => $account) {
            $day = date('d', strtotime($from_date));
            $tot_day = date('Y-m', strtotime($from_date));
            $account_id = $account['id'];
            for ($x = 0; $x < 30; $x++) {
                $num = sprintf('%02d', $day);
                $open = @$this->ci->db->order_by('id', 'DESC')->get_where(
                    'bank_reconciliation_statement',
                    "trans_date = '$tot_day-$num' and account_id = '$account_id'"
                )->row();
                if (count($open) > 0) {
                    $accounts[$key]['opening_balance'] = isset($open)
                        ? (str_replace(',', '', $open->balance) + str_replace(',', '', $open->debit)) - str_replace(',', '', $open->credit)
                        : 0;
                    break;
                }
                $day++;
            }
            if (!isset($accounts[$key]['opening_balance'])) {
                $accounts[$key]['opening_balance'] = 0;
            }

            $this->ci->db->select('bank_reconciliation_statement.*,payments.*,bank_reconciliation_statement.statement_id as str_id,bank_reconciliation_statement.id as sta_id,transactions_history.id thid,closing_perday.id as clid');
            $this->ci->db->from('bank_reconciliation_statement');
            $this->ci->db->join('payments', 'payments.statement_id = bank_reconciliation_statement.id', 'left');
            $this->ci->db->join('transactions_history', 'transactions_history.id = bank_reconciliation_statement.statement_id', 'left');
            $this->ci->db->join('closing_perday', 'closing_perday.id = bank_reconciliation_statement.closing_id', 'left');
            $this->ci->db->where("bank_reconciliation_statement.trans_date >= '" . $from_date . "' and bank_reconciliation_statement.trans_date <= '" . $to_date . "' and bank_reconciliation_statement.account_id = '$account_id'");
            $this->ci->db->group_by('bank_reconciliation_statement.description,bank_reconciliation_statement.trans_date,bank_reconciliation_statement.credit,bank_reconciliation_statement.debit');
            $statements = $this->ci->db->get()->result_array();

            $debit = 0.0;
            $credit = 0.0;
            $sent_own = 0.0;
            $receive_own = 0.0;
            $uncount_debit = 0;
            $uncount_credit = 0;
            $total_profit_given = 0;
            $bank_account_to_cash_account = 0;
            $cash_account_to_bank_account = 0;
            $closing_credit_in_bank = 0;
            $expenses = 0;

            foreach ($statements as $statement) {
                if ($statement['debit'] != '') {
                    $debit += strpos($statement['debit'], ',') !== false ? str_replace(',', '', $statement['debit']) : $statement['debit'];
                    if ($statement['payment_id'] == NULL && $statement['related_to'] == 0 && $statement['bank_transfer_id'] == NULL &&
                        $statement['expense_id'] == NULL && $statement['statement_id'] == NULL && $statement['str_id'] == NULL && $statement['closing_id'] == NULL &&
                        $statement['is_council_fee'] == NULL && $statement['paypro_id'] == NULL && $statement['salary_expense_ids'] == NULL && $statement['loan_id'] == NULL) {
                        $uncount_debit++;
                    }
                    if ($statement['bank_transfer_id'] != NULL) {
                        $sent_own += strpos($statement['debit'], ',') !== false ? str_replace(',', '', $statement['debit']) : $statement['debit'];
                    }
                    if ($statement['profit_distribution_id'] != NULL) {
                        $total_profit_given += strpos($statement['debit'], ',') !== false ? str_replace(',', '', $statement['debit']) : $statement['debit'];
                    }
                    if ($statement['thid'] != NULL) {
                        $bank_account_to_cash_account += strpos($statement['debit'], ',') !== false ? str_replace(',', '', $statement['debit']) : $statement['debit'];
                    }
                    if ($statement['expense_id'] != NULL) {
                        $expenses += strpos($statement['debit'], ',') !== false ? str_replace(',', '', $statement['debit']) : $statement['debit'];
                    }
                }
                if ($statement['credit'] != '') {
                    $credit += strpos($statement['credit'], ',') !== false ? str_replace(',', '', $statement['credit']) : $statement['credit'];
                    if ($statement['payment_id'] == NULL && $statement['related_to'] == 0 && $statement['bank_transfer_id'] == NULL &&
                        $statement['expense_id'] == NULL && $statement['statement_id'] == NULL && $statement['str_id'] == NULL && $statement['closing_id'] == NULL &&
                        $statement['is_council_fee'] == NULL && $statement['paypro_id'] == NULL && $statement['salary_expense_ids'] == NULL) {
                        $uncount_credit++;
                    }
                    if ($statement['bank_transfer_id'] != NULL) {
                        $this->ci->db->select('accounts.taxable');
                        $this->ci->db->from('bank_reconciliation_statement');
                        $this->ci->db->join('accounts', 'accounts.id = bank_reconciliation_statement.account_id');
                        $this->ci->db->where("bank_reconciliation_statement.id = '" . $statement['bank_transfer_id'] . "'");
                        $type = $this->ci->db->get()->row();
                        if ($type && $type->taxable == 1) {
                            $receive_own += strpos($statement['credit'], ',') !== false ? str_replace(',', '', $statement['credit']) : $statement['credit'];
                        }
                    }
                    if ($statement['thid'] != NULL) {
                        $cash_account_to_bank_account += strpos($statement['credit'], ',') !== false ? str_replace(',', '', $statement['credit']) : $statement['credit'];
                    }
                    if ($statement['clid'] != NULL) {
                        $closing_credit_in_bank += strpos($statement['credit'], ',') !== false ? str_replace(',', '', $statement['credit']) : $statement['credit'];
                    }
                }
            }

            $accounts[$key]['debit'] = $debit;
            $accounts[$key]['credit'] = $credit;
            $accounts[$key]['sent_own'] = $sent_own;
            $accounts[$key]['received_own'] = $receive_own;
            $accounts[$key]['uncount_debit'] = $uncount_debit;
            $accounts[$key]['uncount_credit'] = $uncount_credit;
            $accounts[$key]['total_profit_given'] = $total_profit_given;
            $accounts[$key]['bank_account_to_cash_account'] = $bank_account_to_cash_account;
            $accounts[$key]['cash_account_to_bank_account'] = $cash_account_to_bank_account;
            $accounts[$key]['closing_credit_in_bank'] = $closing_credit_in_bank;
            $accounts[$key]['expenses'] = $expenses;
        }

        return array(
            'success' => true,
            'data' => array(
                'from_date' => $from_date,
                'to_date' => $to_date,
                'statements' => $accounts,
            ),
        );
    }

    public function expense_college_report($from_date, $to_date, $campus_ids, $category_ids)
    {
        if (!$from_date || !$to_date) {
            return array('success' => false, 'message' => 'Date range required');
        }
        if (empty($campus_ids)) {
            $all = $this->ci->db->where('status', 1)->get('campuses')->result_array();
            $campus_ids = array_map(function ($c) { return $c['campus_id']; }, $all);
        }
        if (empty($category_ids)) {
            return array('success' => true, 'data' => array('rows' => array(), 'from_date' => $from_date, 'to_date' => $to_date));
        }
        $this->ci->db->select('sum(expenses.amount) as total_amount,campuses.campus_id,campuses.campus_name,expense_category.name,expense_category.expense_category_id');
        $this->ci->db->from('expenses');
        $this->ci->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
        $this->ci->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
        $this->ci->db->where(array('expenses.date>=' => $from_date, 'expenses.date<=' => $to_date));
        $this->ci->db->where_in('expenses.expense_category_id', $category_ids);
        $this->ci->db->where_in('expenses.campus_id', $campus_ids);
        $this->ci->db->group_by('expenses.campus_id,expenses.expense_category_id');
        $rows = $this->ci->db->get()->result_array();
        return array(
            'success' => true,
            'data' => array('rows' => $rows, 'from_date' => $from_date, 'to_date' => $to_date, 'campus_ids' => implode(',', $campus_ids)),
        );
    }

    public function expense_headwise_report($from_date, $to_date, $campus_ids, $date_type = 'actual_date', $campus_filter = null, $category_filter = null)
    {
        if (!$from_date || !$to_date) {
            return array('success' => false, 'message' => 'Date range required');
        }
        if (empty($campus_ids)) {
            $all = $this->ci->db->where('status', 1)->get('campuses')->result_array();
            $campus_ids = array_map(function ($c) { return $c['campus_id']; }, $all);
        }
        $campus_ids_str = implode(',', $campus_ids);
        if (!$date_type) {
            $date_type = 'actual_date';
        }

        $this->ci->db->order_by('name', 'ASC');
        if ($campus_filter) {
            $categories = $this->ci->db->where("find_in_set($campus_filter, for_campus)", NULL, FALSE)->get_where('expense_category', 'sub_of is NULL')->result_array();
        } elseif (!empty($category_filter)) {
            $this->ci->db->where_in('expense_category_id', $category_filter);
            $categories = $this->ci->db->get_where('expense_category', 'sub_of is NULL')->result_array();
        } else {
            $categories = $this->ci->db->get_where('expense_category', 'sub_of is NULL')->result_array();
        }

        $rows = array();
        foreach ($categories as $category) {
            $bank = (float) getBankExpense($category['expense_category_id'], $from_date, $to_date, $date_type, $campus_ids_str);
            $cash = (float) getCashExpense($category['expense_category_id'], $from_date, $to_date, $date_type, $campus_ids_str);
            $untagged = (float) notTaggedBankExpenses($category['expense_category_id'], $from_date, $to_date, $date_type, $campus_ids_str);
            $total = (float) getBothExpense($category['expense_category_id'], $from_date, $to_date, $date_type, $campus_ids_str);
            $rows[] = array(
                'expense_category_id' => $category['expense_category_id'],
                'name' => $category['name'],
                'has_sub' => $category['has_sub'],
                'bank' => $bank,
                'cash' => $cash,
                'untagged_bank' => $untagged,
                'total' => $total,
            );
        }

        return array(
            'success' => true,
            'data' => array(
                'rows' => $rows,
                'from_date' => $from_date,
                'to_date' => $to_date,
                'date_type' => $date_type,
                'campus_ids' => $campus_ids_str,
            ),
        );
    }

    public function tax_paid_list()
    {
        return $this->ci->db->order_by('tax_paid_id', 'DESC')->get('tax_paid')->result_array();
    }

    public function tax_paid_add($body)
    {
        $type = isset($body['type']) ? trim($body['type']) : '';
        $tax_year = isset($body['tax_year']) ? (int) $body['tax_year'] : 0;
        if ($type === '' || $tax_year <= 0) {
            return array('success' => false, 'message' => 'Type and tax year required');
        }
        $check = $this->ci->db->get_where('tax_paid', array('type' => $type, 'tax_year' => $tax_year))->result_array();
        if (count($check) > 0) {
            return array('success' => false, 'message' => 'This year tax already uploaded');
        }
        $file = $this->_upload('tax_document', 'tax_documents/');
        if ($file === false) {
            return array('success' => false, 'message' => 'Document upload failed');
        }
        if ($file === '') {
            return array('success' => false, 'message' => 'Tax document required');
        }
        $this->ci->db->insert('tax_paid', array(
            'type' => $type,
            'tax_year' => $tax_year,
            'tax_document' => $file,
        ));
        $tax_paid_id = $this->ci->db->insert_id();
        $this->_sync_tax_document($tax_paid_id);
        return array('success' => true, 'tax_paid_id' => $tax_paid_id);
    }

    public function tax_paid_delete($id)
    {
        $this->ci->db->delete('tax_paid', array('tax_paid_id' => (int) $id));
        return array('success' => true);
    }

    private function _upload($field, $dir)
    {
        if (empty($_FILES[$field]['name']) || !is_uploaded_file($_FILES[$field]['tmp_name'])) {
            return '';
        }
        $this->ci->load->library('upload');
        $path = rtrim(FCPATH, '/') . '/' . trim($dir, '/') . '/';
        if (!is_dir($path)) {
            @mkdir($path, 0777, true);
        }
        $config = array('upload_path' => $path, 'allowed_types' => '*', 'encrypt_name' => false);
        $this->ci->upload->initialize($config);
        if (!$this->ci->upload->do_upload($field)) {
            return false;
        }
        $data = $this->ci->upload->data();
        return !empty($data['file_name']) ? $data['file_name'] : false;
    }

    private function _sync_tax_document($tax_paid_id)
    {
        if (!function_exists('curl_init')) {
            return;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.shahbazcollegeofpharmacy.edu.pk/s3/upload_tax_document.php');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'tax_paid_id=' . (int) $tax_paid_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }
}
