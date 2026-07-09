<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Accounts extends CI_Controller {
    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     * 		http://example.com/index.php/welcome
     *	- or -
     * 		http://example.com/index.php/welcome/index
     *	- or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see https://codeigniter.com/user_guide/general/urls.html
     */

    public function __construct()
    {
        parent::__construct();
        $this->load->model('account');
        $this->load->model('expense');
        require_once("vendor/autoload.php");
    }

    public function index()
    {
        $data['campuses'] = $this->account->getCampuses();


        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('accounts/index', $data);
        $this->load->view('inc/footer');
    }

    public function advance()
    {
        $data['teachers'] = $this->account->getUsers();


        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('accounts/all_users', $data);
        $this->load->view('inc/footer');
    }

    public function campus_profit($campus_id)
    {
        if(@$this->input->post('till_date'))
        {
            $data['till_date'] = $this->input->post('till_date');
        }
        else
        {
            $data['till_date'] = date('Y-m-d', strtotime(date('Y-m-d'). ' -1 day'));
        }
        $data['campuses'] = $this->account->getCampus($campus_id);
        if($this->input->post('search_from_date'))
        {
            $data['search_from_date'] = $this->input->post('search_from_date');
            $data['search_to_date'] = $this->input->post('search_to_date');
            $this->db->select('*');
            $this->db->from('profit_distribution_date');
            $this->db->where('campus_id', $campus_id);
            $this->db->where("date >= '".$data['search_from_date']."' and date <= '".$data['search_to_date']."'");
            $data['profits'] = $this->db->get()->result_array();
            $arr = array_column($data['profits'],"date");

            $this->db->select('*');
            $this->db->from('profit_distribution');
            $this->db->join('users', 'users.user_id=profit_distribution.user_id', 'inner');
            $this->db->where('profit_distribution.campus_id', $campus_id);
            $this->db->where_in('to_date', $arr);
            $data['profits'] = $this->db->get()->result_array();
        }
        else
        {
            $data['search_from_date'] = date('Y-m-d');
            $data['search_to_date'] = date('Y-m-d');
            $this->db->select('*');
            $this->db->from('profit_distribution_date');
            $this->db->where('campus_id', $campus_id);
            $this->db->order_by('date','DESC');
            $this->db->limit(3);
            $data['profits'] = $this->db->get()->result_array();
            $arr = array_column($data['profits'],"date");

            $this->db->select('*');
            $this->db->from('profit_distribution');
            $this->db->join('users', 'users.user_id=profit_distribution.user_id', 'inner');
            $this->db->where('profit_distribution.campus_id', $campus_id);
            $this->db->where_in('to_date', $arr);
            $this->db->order_by('to_date','DESC');
            $data['profits'] = $this->db->get()->result_array();

        }

        $data['accounts'] = $this->db->query('SELECT * FROM `accounts` WHERE `type` = "0"')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('accounts/campus_profit', $data);
        $this->load->view('inc/footer');
    }
    
    public function total_expenses($from_date, $till_date, $campus_id)
    {
        $campuses = $this->account->getCampus($campus_id);
        $psart = 0;
        $seats = 0;
        $my_seats = 0;
        $partner = $this->db->get_where("campus_partners","campus_id = '".$campuses[0]['campus_id']."'")->row();

        $all_expenses = [];
        @$port_campuses = json_decode($partner->campus_share_ids);
        @$port_seats = json_decode($partner->no_of_seats);
        if ($port_campuses):
            foreach (@$port_campuses as $i=>$port_campus):

                if ($port_campus == $campuses[0]['campus_id']){
                    $my_seats = $port_seats[$i];
                }

                $seats += $port_seats[$i];

                $this_exp = gettotalExpense($port_campus, $from_date, $till_date);
                
                $all_expenses = array_merge($all_expenses, $this_exp);
                
            endforeach;
        endif;
        
        $data['expenses'] = $all_expenses;
        
        
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('accounts/total_expenses', $data);
        $this->load->view('inc/footer');
    }

    public function shift_fee_recovery($campus_id,$type)
    {
        $arr = array();
        $from_date = getFromDateProfitDistribution($campus_id);

        if ($type == 'deduction')
            $datax = $this->db->select("*")->get_where('student_shift_details', array('from_class'=>$campus_id,'status'=>"0"))->result_array();
        else
            $datax = $this->db->select("*")->get_where('student_shift_details', array('to_class'=>$campus_id,'status'=>"0",'received_status'=>0))->result_array();


        foreach ($datax as $entry)
        {
            if ($type == 'deduction')
                $arr = array_merge($arr,json_decode($entry['from_fee_ids']));
            else
                $arr = array_merge($arr,json_decode($entry['to_fee_ids']));
        }


        if (count($arr) > 0) {
            $this->db->select('payments.*, classes.name as class_name, campuses.campus_name, students.first_name, students.last_name, students.roll_no');
            $this->db->from('payments');
            $this->db->join('students', 'students.student_id=payments.student_id', 'inner');
            $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
            $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
            $this->db->where_in('payments.challan_no',$arr);
            $query = $this->db->get()->result_array();
        }
        else
            $query = array();

        $campus = $this->db->select("*")->get_where('campuses', array('campus_id'=>$campus_id))->result_array();

        $data['fees'] = $query;
        $data['details'] = $datax;
        $data['from_campus'] = $campus;
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('accounts/fee_recovery_shifted', $data);
        $this->load->view('inc/footer');
    }

    public function insert_campus_profit()
    {
        $count = count($this->input->post());
        $counter = ($count-6)/3;

        $campus_id = $this->input->post('campus_id');
        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');
        $total_expense = $this->input->post('total_expense');
        $total_recovery = $this->input->post('total_recovery');
        $net_profit = $this->input->post('net_profit');

//        if($to_date>=date('Y-m-d'))
//        {
//            $this->session->set_flashdata('error', 'Date selection error!');
//            redirect(site_url().'/accounts/campus_profit/'.$campus_id);
//        }
//        else
//        {
            $type = $this->input->post('section');
            if ($type == 'cash'){
                $tagged = 'yes';
                $account_id = $this->input->post('to_account');
                $account = accountCash_balance($account_id);
                $amnt = 0;
                for($i=1; $i<=$counter; $i++)
                {
                    $amnt+= $this->input->post('amount_'.$i);
                }
                if ($account<$amnt){
                    $this->session->set_flashdata('error', 'Account Balance is Low!');
                    redirect(site_url().'/accounts/campus_profit/'.$campus_id);
                }
            }else{
                $tagged = 'no';
            }
            //load the helper
            $this->load->helper('form');
            //Configure
            //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
            $config['upload_path'] = 'uploads/';
            // set the filter image types
            $config['allowed_types'] = 'gif|jpg|png';
            //load the upload library
            $this->load->library('upload', $config);
            $this->upload->initialize($config);
            $this->upload->set_allowed_types('*');
            $data['upload_data'] = '';


            //if not successful, set the error message
            if (!$this->upload->do_upload('record')) {
                $data = array('msg' => $this->upload->display_errors());
                $record = '';
            }
            else
            {
                //else, set the success message
                $data['upload_data'] = $this->upload->data();
                if($data['upload_data']['file_name']){
                    $record = $data['upload_data']['file_name'];
                }
            }

            for($i=1; $i<=$counter; $i++)
            {
                $user_id = $this->input->post('user_id_'.$i);
                $user = $this->db->get_where("users","users.user_id = $user_id")->row();
                $this->db->set('campus_id', $campus_id);
                $this->db->set('from_date', $from_date);
                $this->db->set('to_date', $to_date);
                $this->db->set('total_expense', $total_expense);
                $this->db->set('total_recovery', $total_recovery);
                $this->db->set('net_profit', $net_profit);
                $this->db->set('user_id', $this->input->post('user_id_'.$i));
                $this->db->set('amount', $this->input->post('amount_'.$i));
                $this->db->set('percentage', $this->input->post('percentage_'.$i));
                $this->db->set('record', $record);
                $this->db->set('close_type', $type);
                $this->db->set('tagged', $tagged);
                $this->db->insert('profit_distribution');
                if ($type == 'cash') {
                    $profit_distribution_id = $this->db->insert_id();
                    $account_id = $this->input->post('to_account');
                    $this->db->set('from_account_id', $account_id);
                    $this->db->set('profit_distribution_id', $profit_distribution_id);
                    $this->db->set('transaction_account_id', $account_id);
                    $this->db->set('amount', $this->input->post('amount_' . $i));
                    $this->db->set('debit_credit', 'C');
                    $this->db->set('transaction_by', $this->session->userdata('name'));
                    $this->db->set('reason', 'Funds Transfer Profit Share with ' . $user->first_name . ' ' . $user->last_name);
                    $this->db->set('proof_image', "");
                    $this->db->set('profit_taken', "1");
                    $this->db->set('created_at', date('Y-m-d H:i:s'));
                    $this->db->insert('transactions_history');
                }
            }

            $this->db->set('campus_id', $campus_id);
            $this->db->set('date', $to_date);
            $this->db->insert('profit_distribution_date');
            $id = $this->db->insert_id();
            $fees = $this->account->getPayments($from_date, $to_date, $campus_id);
            $contractorsfees = $this->account->getPaymentsContractors($from_date, $to_date, $campus_id);

            $this->db->set("status","1");
            $this->db->where( array('from_class' => $campus_id,'status'=>"0"));
            $this->db->update('student_shift_details');

            $this->db->set("received_status","1");
            $this->db->where( array('to_class' => $campus_id,'status'=>"0"));
            $this->db->update('student_shift_details');

            foreach ($fees as $data)
            {
                $this->db->set('settlement_id', $id);
                $this->db->where('challan_no', $data['challan_no']);
                $this->db->update('payments');
            }
            foreach ($contractorsfees as $data)
            {
                $this->db->set('settlement_id', $id);
                $this->db->where('challan_no', $data['challan_no']);
                $this->db->update('payments');
            }

            $this->session->set_flashdata('message', 'Successfully!');
            redirect(site_url().'/accounts/campus_profit/'.$campus_id);
//        }
    }

    public function expenses($from_date, $till_date, $campus_id)
    {
        $data['expenses'] = $this->account->getExpenses($from_date, $till_date, $campus_id);
        

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('accounts/expense', $data);
        $this->load->view('inc/footer');
    }

    public function fee_recovery($from_date, $till_date, $campus_id)
    {
        $data['fees'] = $this->account->getPayments($from_date, $till_date, $campus_id);
        $data['contractorsfees'] = $this->account->getPaymentsContractors($from_date, $till_date, $campus_id);

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('accounts/fee_recovery', $data);
        $this->load->view('inc/footer');
    }

    public function profit_recovery_details($from_date,$till_date,$campus_id)
    {
        $data['fees'] = $this->account->getPayments($from_date, $till_date, $campus_id);
        $data['contractorsfees'] = $this->account->getPaymentsContractors($from_date, $till_date, $campus_id);
        $data['campuses'] = $this->db->get_where('campuses',array('campus_id'=>$campus_id))->result_array();


        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('accounts/profit_recovery_details', $data);
        $this->load->view('inc/footer');
    }

    public function profit_expense_details($from_date,$till_date,$campus_id)
    {
        $data['expenses'] = $this->account->getExpenses($from_date, $till_date, $campus_id);

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('accounts/profit_expense_details', $data);
        $this->load->view('inc/footer');
    }

    public function account_details()
    {
        $this->db->select('*');
        $this->db->from('accounts');
        $this->db->where('type', 0);
        $cashAccounts = $this->db->get()->result_array();

//        $this->db->select('*');
//        $this->db->from('accounts');
//        $this->db->where('type', 1);
//        $bankAccounts = $this->db->get()->result_array();

        $data['cash_accounts'] = filterRecordsByAccessIds($cashAccounts, 'id', 'allowed_cash_account_ids');
        $data['bank_accounts'] = [];
        $data['accounts'] = filterRecordsByAccessIds(array_merge($cashAccounts, []), 'id', 'funds_transfer_account_ids');
        $data['edit_accounts'] = array_merge($data['cash_accounts'], []);

        $this->db->select('*');
        $this->db->from('petty_cash_college_wise');
        $this->db->join('campuses','campuses.campus_id = petty_cash_college_wise.campus_id','left');
        $this->db->join('users','users.user_id = petty_cash_college_wise.assign_to','left');
        $this->db->join('designations','designations.designation_id = users.designation_id','left');
        $this->db->where('petty_cash_college_wise.petty_status = 1');
        $pettyAccounts = $this->db->get()->result_array();
        $data['Pettycashs'] = filterRecordsByAccessIds($pettyAccounts, 'id', 'account_details_pettycash_ids');

        $data['can_add_account'] = hasAccountDetailsFeature('account_add_account');
        $data['can_funds_transfer'] = hasAccountDetailsFeature('account_funds_transfer');
        $data['can_edit_account'] = hasAccountDetailsFeature('account_edit');

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('accounts/accounts_details', $data);
        $this->load->view('inc/footer');
    }

    private function financial_year_range()
    {
        $from = $this->input->get('from_date');
        $to = $this->input->get('to_date');

        if (!$from || !$to) {
            $year = (int) date('Y');
            if ((int) date('m') < 7) {
                $fromYear = $year - 1;
                $toYear = $year;
            } else {
                $fromYear = $year;
                $toYear = $year + 1;
            }
            $from = $fromYear.'-07-01';
            $to = $toYear.'-06-30';
        }

        return array($from, $to);
    }

    private function table_exists_safe($table)
    {
        return $this->db->table_exists($table);
    }

    private function money_row($title, $credit, $debit, $balance, $type, $group, $url = '', $opening = 0)
    {
        return array(
            'title' => $title,
            'opening' => (float) $opening,
            'credit' => (float) $credit,
            'debit' => (float) $debit,
            'balance' => (float) $balance,
            'type' => $type,
            'group' => $group,
            'url' => $url
        );
    }

    private function account_activity($accountId, $from, $to)
    {
        $activity = array('credit' => 0, 'debit' => 0);
        if (!$this->table_exists_safe('transactions_history')) {
            return $activity;
        }

        $this->db->select("
            SUM(CASE WHEN debit_credit = 'D' THEN amount ELSE 0 END) as credit_amount,
            SUM(CASE WHEN debit_credit = 'C' THEN amount ELSE 0 END) as debit_amount
        ", false);
        $this->db->where('transaction_account_id', $accountId);
        $this->db->where('created_at >=', $from.' 00:00:00');
        $this->db->where('created_at <=', $to.' 23:59:59');
        $row = $this->db->get('transactions_history')->row_array();

        $activity['credit'] = (float) @$row['credit_amount'];
        $activity['debit'] = (float) @$row['debit_amount'];
        return $activity;
    }

    private function account_opening_balance($accountId, $from)
    {
        if (!$this->table_exists_safe('transactions_history')) {
            return 0;
        }

        $this->db->select('SUM(CASE WHEN debit_credit = "D" THEN amount ELSE 0 END) as debit_amount, SUM(CASE WHEN debit_credit = "C" THEN amount ELSE 0 END) as credit_amount', false);
        $this->db->where('transaction_account_id', $accountId);
        $this->db->where('created_at <', $from.' 00:00:00');
        $row = $this->db->get('transactions_history')->row_array();

        return (float) @$row['debit_amount'] - (float) @$row['credit_amount'];
    }

    private function account_current_balance($accountId, $to = null)
    {
        if ($to === null && function_exists('accountCash_balance')) {
            return (float) accountCash_balance($accountId);
        }

        $to = $to ?: date('Y-m-d');
        $activity = $this->account_activity($accountId, '1970-01-01', $to);
        return $activity['credit'] - $activity['debit'];
    }

    private function bank_statement_opening_balance($accountId, $from, $to)
    {
        if (!$this->table_exists_safe('bank_reconciliation_statement')) {
            return $this->account_opening_balance($accountId, $from);
        }

        $firstStatement = $this->db
            ->select('balance, debit, credit')
            ->where('account_id', $accountId)
            ->where('trans_date >=', $from)
            ->where('trans_date <=', $to)
            ->order_by('trans_date', 'ASC')
            ->order_by('id', 'ASC')
            ->limit(1)
            ->get('bank_reconciliation_statement')
            ->row_array();

        if (isset($firstStatement['balance'])) {
            return ((float) str_replace(',', '', $firstStatement['balance']) + (float) str_replace(',', '', $firstStatement['debit'])) - (float) str_replace(',', '', $firstStatement['credit']);
        }

        $statement = $this->db
            ->select('balance')
            ->where('account_id', $accountId)
            ->where('balance !=', '')
            ->where('balance IS NOT NULL', null, false)
            ->where('trans_date <', $from)
            ->order_by('trans_date', 'DESC')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('bank_reconciliation_statement')
            ->row_array();

        if (!isset($statement['balance'])) {
            return 0;
        }

        return (float) str_replace(',', '', $statement['balance']);
    }

    private function bank_statement_balance_as_of($accountId, $to)
    {
        if (!$this->table_exists_safe('bank_reconciliation_statement')) {
            return $this->account_current_balance($accountId, $to);
        }

        $statement = $this->db
            ->select('balance')
            ->where('account_id', $accountId)
            ->where('balance !=', '')
            ->where('balance IS NOT NULL', null, false)
            ->where('trans_date <=', $to)
            ->order_by('trans_date', 'DESC')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('bank_reconciliation_statement')
            ->row_array();

        if (!isset($statement['balance'])) {
            return 0;
        }

        return (float) str_replace(',', '', $statement['balance']);
    }

    private function bank_statement_activity($accountId, $from, $to)
    {
        $activity = array('credit' => 0, 'debit' => 0);
        if (!$this->table_exists_safe('bank_reconciliation_statement')) {
            return $activity;
        }

        $this->db->select('description, trans_date, credit, debit');
        $this->db->where('account_id', $accountId);
        $this->db->where('trans_date >=', $from);
        $this->db->where('trans_date <=', $to);
        $this->db->group_by('description,trans_date,credit,debit');
        $rows = $this->db->get('bank_reconciliation_statement')->result_array();

        foreach ($rows as $row) {
            $activity['credit'] += (float) str_replace(',', '', @$row['credit']);
            $activity['debit'] += (float) str_replace(',', '', @$row['debit']);
        }
        return $activity;
    }

    private function petty_cash_balance_as_of($petty, $date)
    {
        $balance = (float) @$petty['opening_balance'];
        $end = $date.' 23:59:59';
        $givenDate = @$petty['given_date'] ?: '1970-01-01';

        if ($this->table_exists_safe('expenses')) {
            $this->db->select_sum('amount');
            $this->db->where('add_by_id', $petty['assign_to']);
            $this->db->where('actual_date >=', $givenDate);
            $this->db->where('actual_date <=', $end);
            $this->db->where('paid_type', 'cash');
            $this->db->where('expense_id NOT IN (select expense_id from bank_reconciliation_statement where expense_id IS NOT NULL)', null, false);
            $expense = $this->db->get('expenses')->row_array();
            $balance -= (float) @$expense['amount'];
        }

        if ($this->table_exists_safe('cash_reversal')) {
            $this->db->select_sum('cash_reversal.amount', 'amount');
            $this->db->from('cash_reversal');
            $this->db->join('expenses', 'expenses.expense_id = cash_reversal.expense_id');
            $this->db->where('expenses.add_by_id', $petty['assign_to']);
            $this->db->where('cash_reversal.created_at >=', $givenDate.' 00:00:00');
            $this->db->where('cash_reversal.created_at <=', $end);
            $reversal = $this->db->get()->row_array();
            $balance += (float) @$reversal['amount'];
        }

        if ($this->table_exists_safe('petty_cash_history')) {
            $this->db->select('SUM(CASE WHEN debit_credit = "D" THEN amount_given ELSE 0 END) as debit_amount, SUM(CASE WHEN debit_credit = "C" THEN amount_given ELSE 0 END) as credit_amount', false);
            $this->db->where('transaction_pettycash_account', $petty['id']);
            $this->db->where('created_at <=', $end);
            $history = $this->db->get('petty_cash_history')->row_array();
            $balance += (float) @$history['debit_amount'];
            $balance -= (float) @$history['credit_amount'];
        }

        return $balance;
    }

    private function petty_cash_activity($petty, $from, $to)
    {
        $activity = array('credit' => 0, 'debit' => 0);

        if ($this->table_exists_safe('petty_cash_history')) {
            $this->db->select('SUM(CASE WHEN debit_credit = "D" THEN amount_given ELSE 0 END) as credit_amount, SUM(CASE WHEN debit_credit = "C" THEN amount_given ELSE 0 END) as debit_amount', false);
            $this->db->where('transaction_pettycash_account', $petty['id']);
            $this->db->where('created_at >=', $from.' 00:00:00');
            $this->db->where('created_at <=', $to.' 23:59:59');
            $history = $this->db->get('petty_cash_history')->row_array();
            $activity['credit'] += (float) @$history['credit_amount'];
            $activity['debit'] += (float) @$history['debit_amount'];
        }

        if ($this->table_exists_safe('expenses')) {
            $this->db->select_sum('amount');
            $this->db->where('add_by_id', $petty['assign_to']);
            $this->db->where('actual_date >=', $from.' 00:00:00');
            $this->db->where('actual_date <=', $to.' 23:59:59');
            $this->db->where('paid_type', 'cash');
            $this->db->where('expense_id NOT IN (select expense_id from bank_reconciliation_statement where expense_id IS NOT NULL)', null, false);
            $expense = $this->db->get('expenses')->row_array();
            $activity['debit'] += (float) @$expense['amount'];
        }

        if ($this->table_exists_safe('cash_reversal')) {
            $this->db->select_sum('cash_reversal.amount', 'amount');
            $this->db->from('cash_reversal');
            $this->db->join('expenses', 'expenses.expense_id = cash_reversal.expense_id');
            $this->db->where('expenses.add_by_id', $petty['assign_to']);
            $this->db->where('cash_reversal.created_at >=', $from.' 00:00:00');
            $this->db->where('cash_reversal.created_at <=', $to.' 23:59:59');
            $reversal = $this->db->get()->row_array();
            $activity['credit'] += (float) @$reversal['amount'];
        }

        return $activity;
    }

    private function expense_head_name($category, $categories)
    {
        $current = $category;
        while (!empty($current['sub_of']) && isset($categories[$current['sub_of']])) {
            $current = $categories[$current['sub_of']];
        }
        return $current['name'];
    }

    public function chart_of_accounts()
    {
        list($from, $to) = $this->financial_year_range();
        $data['from_date'] = $from;
        $data['to_date'] = $to;
        $data['rows'] = array();

        $totals = array(
            'opening' => 0,
            'credit' => 0,
            'debit' => 0,
            'balance' => 0
        );

        $accounts = $this->db->order_by('type', 'ASC')->order_by('account_title', 'ASC')->get('accounts')->result_array();
        foreach ($accounts as $account) {
            $isBank = ((int) $account['type'] === 1);
            $activity = $isBank ? $this->bank_statement_activity($account['id'], $from, $to) : $this->account_activity($account['id'], $from, $to);
            $group = $isBank ? 'Main Accounts - Bank' : 'Main Accounts - Cash';
            $title = trim($account['account_title'].' '.$account['account_name']);
            if ($isBank) {
                $balance = $this->bank_statement_balance_as_of($account['id'], $to);
                $opening = $balance - $activity['credit'] + $activity['debit'];
                if ($opening < 0) {
                    $activity['debit'] += abs($opening);
                    $opening = 0;
                }
            } else {
                $opening = $this->account_opening_balance($account['id'], $from);
                $balance = $opening + $activity['credit'] - $activity['debit'];
            }
            $row = $this->money_row($title, $activity['credit'], $activity['debit'], $balance, 'Asset', $group, site_url().'/accounts/cashaccountreport/'.$account['id'], $opening);
            $data['rows'][] = $row;
            $totals['opening'] += $row['opening'];
            $totals['credit'] += $row['credit'];
            $totals['debit'] += $row['debit'];
            $totals['balance'] += $row['balance'];
        }

        if ($this->table_exists_safe('petty_cash_college_wise')) {
            $pettyCash = $this->db
                ->select('petty_cash_college_wise.*, campuses.campus_name, users.first_name, users.last_name')
                ->join('campuses', 'campuses.campus_id = petty_cash_college_wise.campus_id', 'left')
                ->join('users', 'users.user_id = petty_cash_college_wise.assign_to', 'left')
                ->where('petty_cash_college_wise.petty_status', 1)
                ->order_by('campuses.campus_name', 'ASC')
                ->get('petty_cash_college_wise')
                ->result_array();
            foreach ($pettyCash as $petty) {
                $openingDate = date('Y-m-d', strtotime($from.' -1 day'));
                $opening = $this->petty_cash_balance_as_of($petty, $openingDate);
                $activity = $this->petty_cash_activity($petty, $from, $to);
                $balance = $opening + $activity['credit'] - $activity['debit'];
                $title = trim($petty['campus_name'].' - '.$petty['first_name'].' '.$petty['last_name']);
                $row = $this->money_row($title, $activity['credit'], $activity['debit'], $balance, 'Asset', 'Petty Cash Accounts', site_url().'/pettycash/pettycash_statement/'.$petty['id'], $opening);
                $data['rows'][] = $row;
                $totals['opening'] += $row['opening'];
                $totals['credit'] += $row['credit'];
                $totals['debit'] += $row['debit'];
                $totals['balance'] += $row['balance'];
            }
        }

        if ($this->table_exists_safe('payments')) {
            $this->db->select('SUM(CASE WHEN actual_amount > 0 THEN actual_amount ELSE amount END) as total', false);
            $this->db->where('paid', 1);
            $this->db->where('actual_paid_date >=', $from);
            $this->db->where('actual_paid_date <=', $to);
            $feeIncome = $this->db->get('payments')->row_array();
            $row = $this->money_row('Student Fee / Recovery Received', @$feeIncome['total'], 0, @$feeIncome['total'], 'Income', 'Credit / Income');
            $data['rows'][] = $row;
            $totals['credit'] += $row['credit'];
            $totals['balance'] += $row['balance'];
        }

        if ($this->table_exists_safe('misc_incomes') && $this->table_exists_safe('transactions_history')) {
            $this->db->select('SUM(misc_incomes.amount) as amount', false);
            $this->db->from('misc_incomes');
            $this->db->join('transactions_history', 'transactions_history.misc_id = misc_incomes.id', 'inner');
            $this->db->where('transactions_history.created_at >=', $from.' 00:00:00');
            $this->db->where('transactions_history.created_at <=', $to.' 23:59:59');
            $miscIncome = $this->db->get()->row_array();
            $row = $this->money_row('Miscellaneous Income', @$miscIncome['amount'], 0, @$miscIncome['amount'], 'Income', 'Credit / Income', site_url().'/accounts/add_misc_income');
            $data['rows'][] = $row;
            $totals['credit'] += $row['credit'];
            $totals['balance'] += $row['balance'];
        }

        if ($this->table_exists_safe('expense_category') && $this->table_exists_safe('expenses')) {
            $categoryRows = $this->db->get('expense_category')->result_array();
            $categories = array();
            foreach ($categoryRows as $category) {
                $categories[$category['expense_category_id']] = $category;
            }

            $this->db->select('expense_category.expense_category_id, expense_category.name, expense_category.sub_of, SUM(expenses.amount) as total_amount');
            $this->db->from('expenses');
            $this->db->join('expense_category', 'expense_category.expense_category_id = expenses.expense_category_id', 'left');
            $this->db->where('expenses.date >=', $from);
            $this->db->where('expenses.date <=', $to);
            $this->db->where('expenses.approved_status', 1);
            $this->db->group_by('expense_category.expense_category_id');
            $expenseRows = $this->db->get()->result_array();

            $expenseHeads = array();
            foreach ($expenseRows as $expense) {
                if (!isset($categories[$expense['expense_category_id']])) {
                    continue;
                }
                $head = $this->expense_head_name($categories[$expense['expense_category_id']], $categories);
                if (!isset($expenseHeads[$head])) {
                    $expenseHeads[$head] = 0;
                }
                $expenseHeads[$head] += (float) $expense['total_amount'];
            }
            ksort($expenseHeads);
            foreach ($expenseHeads as $head => $amount) {
                $row = $this->money_row($head, 0, $amount, $amount, 'Expense', 'Debit / Expense Heads');
                $data['rows'][] = $row;
                $totals['debit'] += $row['debit'];
                $totals['balance'] += $row['balance'];
            }

            $this->db->select_sum('amount');
            $this->db->where('date >=', $from);
            $this->db->where('date <=', $to);
            $this->db->where('approved_status', 0);
            $pendingExpense = $this->db->get('expenses')->row_array();
            if ((float) @$pendingExpense['amount'] > 0) {
                $row = $this->money_row('Pending / Unapproved Expenses', 0, 0, @$pendingExpense['amount'], 'Liability', 'Liabilities / Payables', '', @$pendingExpense['amount']);
                $data['rows'][] = $row;
                $totals['opening'] += $row['opening'];
                $totals['balance'] += $row['balance'];
            }
        }

        if ($this->table_exists_safe('loans') && $this->table_exists_safe('loan_plan')) {
            $loanRow = $this->db->query("
                SELECT SUM(loan_plan.amount - IFNULL(loan_plan.amount_paid, 0)) as remaining
                FROM loan_plan
                INNER JOIN loans ON loans.id = loan_plan.loan_id
                WHERE loans.status = 1
            ")->row_array();
            if ((float) @$loanRow['remaining'] > 0) {
                $row = $this->money_row('Staff Loans / Advances Receivable', 0, 0, @$loanRow['remaining'], 'Asset', 'Loans / Advances', site_url().'/loans/accounts_loans_list', @$loanRow['remaining']);
                $data['rows'][] = $row;
                $totals['opening'] += $row['opening'];
                $totals['balance'] += $row['balance'];
            }
        }

        $data['totals'] = $totals;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('accounts/chart_of_accounts', $data);
        $this->load->view('inc/footer');
    }

    public function add_account()
    {
        if (!hasAccountDetailsFeature('account_add_account')) {
            $this->session->set_userdata('error', 'You do not have permission to add accounts.');
            redirect('accounts/account_details');
            return;
        }

        $accounttitle = $this->input->post('title');
        $accountno = $this->input->post('accountno');
        $accountamount = $this->input->post('amount');
        $accountlimit = $this->input->post('amount_limit');
        $bank = $this->input->post('bank');
        $account_taxable = $this->input->post('account_taxable');
        $account_closing = $this->input->post('for_closing');

        $title = $bank.' ('.$accountno.')';
        $this->db->set('account_name',$title);
        $this->db->set('account_title',$accounttitle);
        $this->db->set('amount',$accountamount);
        $this->db->set('type','1');
        $this->db->set('taxable',$account_taxable);
        $this->db->set('for_closing',$account_closing);
        $this->db->set('account_limit',$accountlimit);
        $this->db->insert('accounts');

        redirect('accounts/account_details');
    }

    public function edit()
    {
        $accountId = $this->input->post('daccount_id');
        if (!userCanEditAccountId($accountId)) {
            $this->session->set_userdata('error', 'You do not have permission to edit this account.');
            redirect('accounts/account_details');
            return;
        }

        $accounttitle = $this->input->post('title');
        $accountno = $this->input->post('accountno');
        $accountamount = $this->input->post('amount');
        $accountlimit = $this->input->post('amount_limit');
        $account_type = $this->input->post('account_type');
        $account_taxable = $this->input->post('account_taxable');
        $account_closing = $this->input->post('for_closing');
        $bank = $this->input->post('bank');

        $title = $bank.' ('.$accountno.')';

        $this->db->set('account_name',$title);
        $this->db->set('account_title',$accounttitle);
        $this->db->set('account_limit',$accountlimit);
        $this->db->set('type',$account_type);
        $this->db->set('taxable',$account_taxable);
        $this->db->set('for_closing',$account_closing);
        $this->db->where('id',$this->input->post('daccount_id'));
        $this->db->update('accounts');

        redirect('accounts/account_details');

    }

    public function transfer_funds()
    {
        if (!hasAccountDetailsFeature('account_funds_transfer')) {
            $this->session->set_userdata('error', 'You do not have permission to transfer funds.');
            redirect('accounts/account_details');
            return;
        }

        $petty_account = $this->input->post('petty_account');
        $from = $this->input->post('from_account');
        $to = $this->input->post('to_account');
        $pettyid = $this->input->post('petty_account_id');
        $accountamount = $this->input->post('sentamount');
        $trasfer_reason = $this->input->post('trasfer_reason');

        if (!userCanAccessAccountId($from, 'funds_transfer_account_ids')) {
            $this->session->set_userdata('error', 'Invalid from account selected.');
            redirect('accounts/account_details');
            return;
        }

        if ($petty_account == '0') {
            if (!userCanAccessAccountId($to, 'funds_transfer_account_ids')) {
                $this->session->set_userdata('error', 'Invalid to account selected.');
                redirect('accounts/account_details');
                return;
            }
        } elseif (!userCanAccessAccountId($pettyid, 'account_details_pettycash_ids')) {
            $this->session->set_userdata('error', 'Invalid petty cash account selected.');
            redirect('accounts/account_details');
            return;
        }

        //load the helper
        $this->load->helper('form');

        //Configure
        //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
        $config['upload_path'] = 'uploads/';

        // set the filter image types
        $config['allowed_types'] = 'gif|jpg|png';

        //load the upload library
        $this->load->library('upload', $config);

        $this->upload->initialize($config);

        $this->upload->set_allowed_types('*');

        $data['upload_data'] = '';

        //if not successful, set the error message
        if (!$this->upload->do_upload('image')) {
            $data = array('msg' => $this->upload->display_errors());
            $image = '';
        }
        else
        {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $image = $data['upload_data']['file_name'];
            }
        }


        if ($petty_account == '0') {

            $this->db->set('amount', 'amount +' . $accountamount . '', false);
            $this->db->where('id', $to);
            $this->db->update('accounts');


            $this->db->set('amount', 'amount -' . $accountamount . '', false);
            $this->db->where('id', $from);
            $this->db->update('accounts');


            $this->db->set('from_account_id', $from);
            $this->db->set('to_account_id', $to);
            $this->db->set('transaction_account_id', $from);
            $this->db->set('amount', $accountamount);
            $this->db->set('debit_credit', 'C');
            $this->db->set('transaction_by', $this->session->userdata('name'));
            $this->db->set('reason', 'Funds Transfer '.$trasfer_reason);
            $this->db->set('proof_image',$image);
            $this->db->set('created_at',date('Y-m-d H:i:s'));
            $this->db->insert('transactions_history');


            $this->db->set('from_account_id', $from);
            $this->db->set('to_account_id', $to);
            $this->db->set('transaction_account_id', $to);
            $this->db->set('amount', $accountamount);
            $this->db->set('debit_credit', 'D');
            $this->db->set('transaction_by', $this->session->userdata('name'));
            $this->db->set('reason', 'Funds Receive '.$trasfer_reason);
            $this->db->set('proof_image',$image);
            $this->db->set('created_at',date('Y-m-d H:i:s'));
            $this->db->insert('transactions_history');



        }
        else{


            $this->db->set('amount', 'amount -' . $accountamount . '', false);
            $this->db->where('id', $from);
            $this->db->update('accounts');


            $this->db->set('from_account_id', $from);
            $this->db->set('to_pettycash_id', $pettyid);
            $this->db->set('transaction_account_id', $from);
            $this->db->set('amount', $accountamount);
            $this->db->set('debit_credit', 'C');
            $this->db->set('transaction_by', $this->session->userdata('name'));
            $this->db->set('reason', 'Funds Transfer '.$trasfer_reason);
            $this->db->set('proof_image',$image);
            $this->db->set('created_at',date('Y-m-d H:i:s'));
            $this->db->insert('transactions_history');



            $this->db->set('remaining_amount', 'remaining_amount +'. $accountamount .'',false);
            $this->db->where('id', $pettyid);
            $this->db->update('petty_cash_college_wise');

            $this->db->set('debit_credit', 'D');
            $this->db->set('amount_given', $accountamount);
            $this->db->set('from_account', $from);
            $this->db->set('to_pettycash_id', $pettyid);
            $this->db->set('transaction_pettycash_account', $pettyid);
            $this->db->set('status', '1');
            $this->db->set('reason', $trasfer_reason);
            $this->db->set('proof_image',$image);
            $this->db->set('transaction_by', $this->session->userdata('name'));
            $this->db->set('created_at',date('Y-m-d H:i:s'));
            $this->db->insert('petty_cash_history');



        }

        redirect('accounts/account_details');

    }

    public function CashaccountReport($account_id)
    {
        if(@$this->input->post('from_date')) {
            $data['from_date'] = $this->input->post('from_date');
        }
        else {
            $data['from_date'] = date('Y/m/1');
        }
        if(@$this->input->post('to_date')) {
            $data['to_date'] = $this->input->post('to_date');
        }
        else {
            $data['to_date'] = date('Y/m/d');
        }

        $this->db->select('*');
        $this->db->from('transactions_history');
        $this->db->where('transaction_account_id = "'.$account_id.'" and created_at < "'.$data['from_date'].' 00:00:00" ');
        $trans_petty_cash = $this->db->get()->result_array();

        $debit=0;
        $credit=0;

        foreach ($trans_petty_cash as $tran)
        {
            if ($tran['debit_credit']  == 'C' ) {
                $credit+=$tran['amount'];
            }
            else {
                $debit+=$tran['amount'];
            }
        }

        $data['openbalance'] = $debit-$credit;

        $this->db->select('*');
        $this->db->from('transactions_history');
        $this->db->where('transaction_account_id = "'.$account_id.'"');
        $this->db->where('created_at >="'.$data['from_date'].' 00:00:00" and created_at <= "'.$data['to_date'].' 23:59:59"');
        $trans_petty_cash = $this->db->get()->result_array();

        $data['accountstatement']=$trans_petty_cash;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('accounts/cashaccount_statement', $data);
        $this->load->view('inc/footer');
    }

    public function uploadstatement()
    {

        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');
        $account_id = $this->input->post('account_id');

        if ($from_date != '' || $from_date != NULL) {
            $this->db->select('*,bank_reconciliation_statement.id as trans_id,bank_reconciliation_statement.statement_id as str_id,bank_reconciliation_statement.closing_id as closing_bank_id');
            $this->db->from('bank_reconciliation_statement');
            $this->db->join('payments','payments.statement_id = bank_reconciliation_statement.id','left');
            $this->db->join('accounts','accounts.id=bank_reconciliation_statement.account_id','left');
            $this->db->where("bank_reconciliation_statement.trans_date >= '".$from_date."' and bank_reconciliation_statement.trans_date <= '".$to_date."'");

            if ($account_id != '' && $account_id != NULL )
            {
                $this->db->where_in("bank_reconciliation_statement.account_id", $account_id);
            }
            $this->db->group_by("bank_reconciliation_statement.description,bank_reconciliation_statement.trans_date,bank_reconciliation_statement.credit,bank_reconciliation_statement.debit")
                     ->order_by('bank_reconciliation_statement.trans_date','ASC');
            $data['entries']=$this->db->get()->result_array();
            if ($this->input->post('tag_type') == '0'){
                foreach($data['entries'] as $key=>$entry){
                    if (
                        $entry['payment_id'] ==  NULL && $entry['related_to'] ==  0 && $entry['bank_transfer_id'] ==  NULL &&
                        $entry['expense_id'] ==  NULL && $entry['statement_id'] ==  NULL && $entry['str_id'] == NULL && $entry['closing_id'] ==  NULL &&
                        $entry['is_council_fee'] ==  NULL && $entry['paypro_id'] ==  NULL && $entry['salary_expense_ids'] ==  NULL
                    ){
                    }else{

                        unset($data['entries'][$key]);
                    }
                }
            }
        }
        else  {
            $data['entries']=array();
        }

        $this->db->select('*');
        $this->db->from('campuses');
        $data['allcampuses'] = $this->db->get()->result_array();


        $data['categories'] = $this->db->get_where('expense_category', "sub_of is NULL")->result_array();

        $data['accounts'] = $this->db->query('SELECT * FROM `accounts` WHERE `type` = "1"')->result_array();
        $data['cash_accounts'] = $this->db->query('SELECT * FROM `accounts` WHERE `type` = "0"')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('accounts/bank_statement',$data);
        $this->load->view('inc/footer');
    }

    public function untag_payment($id)
    {
        $this->db->where('statement_id', $id);
        $this->db->update('payments', array(
            'paid'            => 0,
            'paid_date'         => NULL,
            'tid_no'            => NULL,
            'paid_challans'     => NULL,
            'merged_challan'   => NULL,
            'statement_id'      => NULL
        ));

        $this->db->where('id', $id);
        $this->db->update('bank_reconciliation_statement', array(
            'statement_id'  => NULL
        ));
        echo "Untagged Successfully";
    }

    public function upload_bank_statement()
    {
        //load the helper
        $this->load->helper('form');

        //Configure
        //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
        $config['upload_path'] = 'statements/';

        // set the filter image types
        $config['allowed_types'] = '*';

        //load the upload library
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        $this->upload->set_allowed_types('*');
        $data['upload_data'] = '';

        //if not successful, set the error message
        if (!$this->upload->do_upload('statement')) {
            $data = array('msg' => $this->upload->display_errors());
            $this->session->set_flashdata('error', $this->upload->display_errors());
            redirect('accounts/uploadstatement');
            $file = '';
        }
        else {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $file = $data['upload_data']['file_name'];
            }
        }

        if($file=='') {
            $this->session->set_flashdata('error', 'Problem in file uploading');
            redirect('accounts/uploadstatement');
        }
        else {
            $this->db->set("date",date("Y-m-d"));
            $this->db->set("account_id",$this->input->post('account_id'));
            $this->db->set("file",$file);
            $this->db->set("add_by",$this->session->userdata('name'));
            $this->db->insert("statement_upload_record");
            $maxid = $this->db->insert_id();

            try {
                $file = fopen($config['upload_path'].'/'.$file,"r");
                $row=1;
                while(! feof($file))
                {
                    $index=fgetcsv($file);
                    if ($this->input->post('account_id') == '3') {

                        if ($row==14 && $index[0]!='Transaction Date')
                        {
                            fclose($file);
                            $this->session->set_flashdata('error', 'Wrong Csv Format.');
                            redirect('accounts/uploadstatement');
                        }

                        if ($row > 14) {

                            $newDate = date("Y-m-d", strtotime($index[0]));
                            if ($newDate != date("Y-m-d")) {

                                $this->db->set('statement_no', $maxid);
                                $this->db->set('account_id', $this->input->post('account_id'));
                                $this->db->set('trans_date', $newDate);
                                $this->db->set('description', $index[2]);
                                $this->db->set('debit', str_replace('-', '', $index[3]));
                                $this->db->set('credit', str_replace('-', '', $index[4]));
                                $this->db->set('balance', $index[5]);
                                if ($index[2] == '' || $index[2] == NULL) {
                                    break;
                                }
                                $this->db->insert('bank_reconciliation_statement');
                            }
                        }
                    }
                    elseif ($this->input->post('account_id') == '5' || $this->input->post('account_id') == '10' || $this->input->post('account_id') == '12')
                    {
                        if ($row==4 && $index[0]!='Date') {
                            fclose($file);
                            $this->session->set_flashdata('error', 'Wrong Csv Format.');
                            redirect('accounts/uploadstatement');
                        }
                        if ($row > 4) {

                            if ($index[0] == '' || $index[0] == NULL) {
                                continue;
                            }
                            $newDate = date("Y-m-d", strtotime($index[0]));
                            if ($newDate != date("Y-m-d")) {

                                $this->db->set('statement_no', $maxid);
                                $this->db->set('account_id', $this->input->post('account_id'));
                                $this->db->set('trans_date', $newDate);
                                $this->db->set('description', $index[1]);
                                $this->db->set('reference_no', $index[2]);
                                if ($index[5] == 'Dr') {
                                    $this->db->set('debit', str_replace('-', '', $index[4]));
                                    $this->db->set('credit', '');
                                } else {
                                    $this->db->set('debit', '');
                                    $this->db->set('credit', str_replace('-', '', $index[4]));
                                }
                                $this->db->set('balance', $index[7]);
                                $this->db->insert('bank_reconciliation_statement');
                            }
                        }
                    }
                    elseif ($this->input->post('account_id') == '4')
                    {
                        if ($row==4 && $index[0]!='Date/Time')
                        {
                            fclose($file);
                            $this->session->set_flashdata('error', 'Wrong Csv Format.'.$index[0]);
                            redirect('accounts/uploadstatement');
                        }

                        if ($row > 4) {
                            if ($index[0] == '' || $index[0] == NULL) {
                                continue;
                            }

                            $date = $index[0];
                            $newDate = date_format(date_create_from_format('d/m/Y', $date), 'Y-m-d');
                            if ($newDate != date("Y-m-d")) {
                                $this->db->set('statement_no', $maxid);
                                $this->db->set('account_id', $this->input->post('account_id'));
                                $this->db->set('trans_date', $newDate);
                                $this->db->set('description', $index[2] . ' ' . $index[3]);
                                $this->db->set('reference_no', $index[1]);

                                $this->db->set('debit', str_replace('-', '', $index[4]));
                                $this->db->set('credit', str_replace('-', '', $index[5]));
                                $this->db->set('balance', $index[6]);
                                $this->db->insert('bank_reconciliation_statement');
                            }
                        }

                    }
                    elseif ($this->input->post('account_id') == '6' || $this->input->post('account_id') == '11' )
                    {
                        if ($row==1 && $index[0]!='Date')
                        {
                            fclose($file);
                            $this->session->set_flashdata('error', 'Wrong Csv Format.'.$index[0]);
                            redirect('accounts/uploadstatement');
                        }
                        if ($row > 2) {
                            if ($index[0] == '' || $index[0] == NULL) {
                                continue;
                            }

                            $newDate = date("Y-m-d", strtotime($index[0]));
                            if ($newDate != date("Y-m-d")) {
                                $this->db->set('statement_no', $maxid);
                                $this->db->set('account_id', $this->input->post('account_id'));
                                $this->db->set('trans_date', $newDate);
                                $this->db->set('description', $index[1]);
                                $this->db->set('reference_no', '');

                                $this->db->set('debit', str_replace('-', '', $index[2]));
                                $this->db->set('credit', str_replace('-', '', $index[3]));

                                $this->db->set('balance', $index[4]);

                                $this->db->insert('bank_reconciliation_statement');
                            }
                        }

                    }
                    elseif ($this->input->post('account_id') == '7')
                    {

                        if ($row==4 && $index[0]!='Date/Time')
                        {
                            fclose($file);
                            $this->session->set_flashdata('error', 'Wrong Csv Format.'.$index[0]);
                            redirect('accounts/uploadstatement');
                        }
                        if ($row > 4)
                        {
                            if ($index[0] == '' || $index[0] == NULL) {
                                continue;
                            }

                            $date = $index[0];
                            $date = substr($date,0,10);

                            $newDate = date_format(date_create_from_format('d-m-Y', $date), 'Y-m-d');
                            if ($newDate != date("Y-m-d")) {
                                $this->db->set('statement_no', $maxid);
                                $this->db->set('account_id', $this->input->post('account_id'));
                                $this->db->set('trans_date', $newDate);
                                $this->db->set('description', $index[1]);
                                $this->db->set('reference_no', $index[2]);
                                $this->db->set('debit', str_replace('-', '', $index[5]));
                                $this->db->set('credit', str_replace('-', '', $index[4]));
                                $this->db->set('balance', '');
                                $this->db->insert('bank_reconciliation_statement');
                            }
                        }

                    }
                    $row++;
                }
            }
            catch(Exception $exception) {
                fclose($file);
                $this->session->set_flashdata('message', 'CSV uploaded successfully.');
                redirect('accounts/uploadstatement');
            }

            fclose($file);
            $this->session->set_flashdata('message', 'CSV uploaded successfully.');
            redirect('accounts/uploadstatement');
        }
    }

    public function agent_statement($from_date,$account_id)
    {
        $this->db->select('*,bank_reconciliation_statement.id as trans_id');
        $this->db->from('bank_reconciliation_statement');
        $this->db->join('accounts','accounts.id=bank_reconciliation_statement.account_id','left');
        $this->db->where("bank_reconciliation_statement.trans_date = '".$from_date."' and bank_reconciliation_statement.account_id = '".$account_id."'");
        $this->db->group_by("bank_reconciliation_statement.description,bank_reconciliation_statement.reference_no");
        $data['entries']=$this->db->get()->result_array();



        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('accounts/agent_bank_statement',$data);
        $this->load->view('inc/footer');

    }

    public function add_expense()
    {
        /*$exp_cat = $this->input->post('expense_category_id');
        $exp_cat =$exp_cat[count($exp_cat)-1];
        echo $exp_cat;
        $data = $this->input->post();
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        exit();*/
        $image = '';
        //load the helper
        $this->load->helper('form');
        //Configure
        //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
        $config['upload_path'] = 'uploads/';
        // set the filter image types
        $config['allowed_types'] = 'gif|jpg|png';
        //load the upload library
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        $this->upload->set_allowed_types('*');
        $data['upload_data'] = '';
        //if not successful, set the error message
        if (!$this->upload->do_upload('image')) {
            $data = array('msg' => $this->upload->display_errors());
            $image = '';
        }
        else
        {
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $image = $data['upload_data']['file_name'];
            }
        }
        $exp_cat = $this->input->post('expense_category_id');
        $exp_cat =$exp_cat[count($exp_cat)-1];

        $this->db->set('campus_id',$this->input->post('ac_campus_id'));
        $this->db->set('expense_category_id',$exp_cat);
        $this->db->set('title',$this->input->post('title'));
        $this->db->set('date',date('Y-m-d'));
        $this->db->set('amount',$this->input->post('amount'));
        $this->db->set('purpose',$this->input->post('reason_disc'));
        $this->db->set('paid_type','bank');
        $this->db->set('actual_date', date('Y-m-d H:i:s'));
        $this->db->set('image', $image);
        $this->db->set('approved_status', '1');
        $this->db->set('add_by_id', $this->session->userdata('user_id'));
        $this->db->set('add_by', $this->session->userdata('name'));
        $this->db->insert('expenses');

        $insert_id=$this->db->insert_id();

        $this->db->set('expense_id',$insert_id);
        $this->db->where('id', $this->input->post('trans_id'));
        $this->db->update('bank_reconciliation_statement');

        $this->db->select('*');
        $this->db->from('expenses');
        $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
        $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
        $this->db->where(array('expenses.expense_id' => $insert_id));
        $expense = $this->db->get()->row();

        echo'<strong>Expense Category : </strong>'. $expense->name.'<br>
             <strong>Campus : </strong><strong style="color: #00CC00">'.$expense->campus_name.'</strong><br>
             <strong>Title : </strong>'  .$expense->title.  '<br>
             <strong>Purpose : </strong>'.$expense->purpose.'<br>
             <strong>Amount : </strong>' .$expense->amount. '<br>
             <strong>Add By : </strong>' .$expense->add_by. '<br>';
    }

    public function find_transactions()
    {
        $bank_trans_id = $this->input->post('bank_trans_id');
        $bank_id = $this->input->post('bank_id');

        $transaction=$this->db->get_where('bank_reconciliation_statement','id = '.$bank_trans_id)->row();

        $this->db->select('*,bank_reconciliation_statement.id as tidx');
        $this->db->from('bank_reconciliation_statement');
        $this->db->join('payments','payments.statement_id = bank_reconciliation_statement.id','left');
        $this->db->where("bank_reconciliation_statement.trans_date ='".$transaction->trans_date."' and account_id = '".$bank_id."' and debit = '".$transaction->credit."'");
        $this->db->group_by("bank_reconciliation_statement.description");
        $entries=$this->db->get()->result_array();

        $html = '';
        $i=0;
        foreach($entries as $closing_rule):

            $f=$i+1;
            $html.=" <tr>
                <td >
                    $f
                     <input class='form-check-input' type='radio' name='tag_id'  value='{$closing_rule['tidx']}' required>
                </td>
                <td>
                </td>
                <td>
                    {$closing_rule['trans_date']}
                </td>
                <td>
                    {$closing_rule['description']}
                </td>
                <td>
                    {$closing_rule['debit']}
                </td>
                <td>
                    {$closing_rule['credit']}
                </td>
                <td>
                    {$closing_rule['balance']}
                </td>
            </tr>";

            $i++;
        endforeach;

        echo $html;


    }

    public function tag_bank_trans() {
        $tag_id=$this->input->post('tag_id');
        $trans_id=$this->input->post('bank_trans_id');

        $this->db->set('bank_transfer_id',$tag_id);
        $this->db->where('id',$trans_id);
        $this->db->update('bank_reconciliation_statement');

        $this->db->set('bank_transfer_id',$trans_id);
        $this->db->where('id',$tag_id);
        $this->db->update('bank_reconciliation_statement');

        $this->db->select('*');
        $this->db->from('bank_reconciliation_statement');
        $this->db->join('accounts','accounts.id=bank_reconciliation_statement.account_id','left');
        $this->db->where(array('bank_reconciliation_statement.id'=> $tag_id));
        $expense = $this->db->get()->row();

        if ($expense->credit != NULL && $expense->credit != '')
            $text='<strong> Transferred to Account : </strong>'. $expense->account_name.' <br>';
        else
            $text='<strong> Received From Account : </strong>'.$expense->account_name.'<br>';

        $text.='<strong> Date : </strong>'. $expense->trans_date .' <br>
                <strong> Amount : </strong>'. $expense->credit." ".$expense->debit.'<br>';
        echo $text;
    }

    public function tag_paypro_trans()
    {
        $tag_id=$this->input->post('tag_id');
        $amount=$this->input->post('amount');
        $trans_id=$this->input->post('bank_trans_id');

        $this->db->set('paypro_id',$tag_id);
        $this->db->where('id',$trans_id);
        $this->db->update('bank_reconciliation_statement');

        $this->db->set('tagged_amount', 'tagged_amount +' . $amount . '', false);
        $this->db->where('id',$tag_id);
        $this->db->update('pay_pro_settlement');

        $this->db->select('*');
        $this->db->from('bank_reconciliation_statement');
        $this->db->join('pay_pro_settlement','pay_pro_settlement.id = bank_reconciliation_statement.paypro_id');
        $this->db->where(array('pay_pro_settlement.id'=>$tag_id));
        $closings = $this->db->get()->result_array();

        foreach ($closings as $closing) {
            echo '<strong> Settlement Date : </strong>' . $closing['settlement_date'] . '<br>
                <strong> Tagged Amount : </strong>' . $closing['credit'] . ' <br>
              <strong> Received Amount : </strong>' . $closing['paid_amount'] . ' <br>
              <strong> 1-Link Amount : </strong>' . $closing['link_amount'] . ' <br>
              <strong> Debit/Credit Card Amount : </strong>' . $closing['card_amount']. '<br /><br />';

        }
    }

    public function add_cash_in_hand()
    {
        $trans_id = $this->input->post('cash_trans_id');
        $transaction=$this->db->get_where('bank_reconciliation_statement','id = '.$trans_id)->row();

        $to = $this->input->post('cash_account_id');
        $accountamount = $this->input->post('amount');
        $trasfer_reason = $this->input->post('reason_disc');
        //load the helper
        $this->load->helper('form');
        //Configure
        //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
        $config['upload_path'] = 'uploads/';
        // set the filter image types
        $config['allowed_types'] = 'gif|jpg|png';
        //load the upload library
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        $this->upload->set_allowed_types('*');
        $data['upload_data'] = '';

        //if not successful, set the error message
        if (!$this->upload->do_upload('image')) {
            $data = array('msg' => $this->upload->display_errors());
            $image = '';
        }
        else
        {
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $image = $data['upload_data']['file_name'];
            }
        }

        $this->db->set('amount', 'amount +' . $accountamount . '', false);
        $this->db->where('id', $to);
        $this->db->update('accounts');

        $this->db->set('from_account_id', $transaction->account_id);
        $this->db->set('to_account_id', $to);
        $this->db->set('transaction_account_id', $to);
        $this->db->set('amount', $accountamount);
        $this->db->set('debit_credit', 'D');
        $this->db->set('transaction_by', $this->session->userdata('name'));
        $this->db->set('reason', 'Funds Receive '.$trasfer_reason);
        $this->db->set('proof_image',$image);
        $this->db->set('created_at',$transaction->trans_date.' '.date('H:i:s'));
        $this->db->insert('transactions_history');
        $idf = $this->db->insert_id();

        $this->db->set('statement_id', $idf);
        $this->db->where('id', $transaction->id);
        $this->db->update('bank_reconciliation_statement');

        $this->db->select('*,transactions_history.amount as trans_amount');
        $this->db->from('transactions_history');
        $this->db->join('accounts','accounts.id=transactions_history.to_account_id','left');
        $this->db->where(array('transactions_history.id' => $idf));
        $expense = $this->db->get()->row();

        echo '<strong> Transferred to Account : </strong>'. $expense->account_name.'<br>
              <strong> Date : </strong>'. date('Y-m-d',strtotime($expense->created_at)).'<br>
              <strong> Amount : </strong>'. $expense->trans_amount .' <br>';
    }

    public function add_council_fee()
    {
        $trans_id=$this->input->post('trans_id');
        $this->db->set('is_council_fee',"1");
        $this->db->where('id',$trans_id);
        $this->db->update('bank_reconciliation_statement');

        echo "Selected as Council Fee Not Tagged";
    }

    public function find_paypro_transactions()
    {
        $bank_trans_id = $this->input->post('bank_trans_id');
        $from_date = $this->input->post('from_date');
        $transaction=$this->db->get_where('bank_reconciliation_statement','id = '.$bank_trans_id)->row();

        $amount = (int)str_replace(",","",$transaction->credit);

        $this->db->select('*');
        $this->db->from('pay_pro_settlement');
        //$this->db->where("pay_pro_settlement.settlement_date",$from_date);
        $this->db->where("pay_pro_settlement.settlement_date ='".$from_date."' and (total_amount-tagged_amount) <= ".$amount);
        
//        $this->db->where("pay_pro_settlement.settlement_date ='".$from_date."' and (link_amount = '".$amount."' or card_amount = '".$amount."')");
        $entries = $this->db->get()->result_array();
		
		//print_r($entries);
		//exit;

        $html = '';
        $i=0;
            foreach($entries as $closing_rule):
            $f=$i+1;
            $html.=" <tr>
                <td>
                    $f
                     <input class='form-check-input' type='radio' name='tag_id'  value='{$closing_rule['id']}' required>
                </td>
               
                <td>
                    {$closing_rule['settlement_date']}
                </td>
                <td>
                    {$closing_rule['total_amount']}
                </td>
                <td>
                    {$closing_rule['paid_amount']}
                </td>
                <td>
                    {$closing_rule['link_amount']}
                </td>
                <td>
                    {$closing_rule['card_amount']}
                </td>
                <td>
                    {$closing_rule['created_at']}
                </td>
                
            </tr>";
            $i++;
            endforeach;
        echo $html;
    }

    public function find_expense_transactions()
    {
        $bank_trans_id = $this->input->post('bank_trans_id');
        $transaction=$this->db->get_where('bank_reconciliation_statement','id = '.$bank_trans_id)->row();
        $amount = (int)str_replace(",","",$transaction->credit);
        $time=strtotime($transaction->trans_date);
        $date = date("Y-m-01",$time);
        $enddate = date("Y-m-t",$time);

        $this->db->select('*');
        $this->db->from('expenses');
        $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
        $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
        $this->db->where("paid_type = 'bank' and date>= '$date' and date<= '$enddate' and bank_statement_id IS NULL");
        $entries = $this->db->get()->result_array();

        $html = '';
        $i=0;
        foreach($entries as $closing_rule):
            $f=$i+1;
            $html.=" <tr>
                <td>
                    $f
                     <input class='form-check-input' onchange='getselected()' type='checkbox' name='tag_ids[]'  value='{$closing_rule['expense_id']}' required>
                </td>
               
                <td>
                    {$closing_rule['campus_name']}
                </td>
                <td>
                    {$closing_rule['name']}
                </td>
                <td>
                    {$closing_rule['title']}
                </td>
                <td>
                    {$closing_rule['purpose']}
                </td>
                <td id='exp_{$closing_rule['expense_id']}'>
                    {$closing_rule['amount']}
                </td>
                <td>
                    {$closing_rule['date']}
                </td>
                <td>
                    {$closing_rule['add_by']}
                </td>
               
            </tr>";
            $i++;
        endforeach;
        echo $html;
    }

    public function tag_expense_trans()
    {
        $expense_ids=$this->input->post('expense_user_ids');
        $trans_id=$this->input->post('bank_trans_id');

        $this->db->set('bank_statement_id',$trans_id);
        $this->db->where_in('expense_id',explode(",",$expense_ids));
        $this->db->update('expenses');

        $this->db->set('salary_expense_ids',$expense_ids);
        $this->db->where('id',$trans_id);
        $this->db->update('bank_reconciliation_statement');

        $this->db->select('*');
        $this->db->from('expenses');
        $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
        $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
        $salary_expenses=$this->db->where(array('bank_statement_id'=> $trans_id ))->get()->result_array();
        $text = "";
        foreach ($salary_expenses as $sals):

            $text .= '<strong>Expense Category : </strong>'. $sals['name'].'<br>
                      <strong>Campus : </strong><strong style="color: #00CC00">'. $sals['campus_name'].'</strong><br>
                      <strong>Amount : </strong>'. $sals['amount'].' <br>
                      <strong>Add By : </strong>'. $sals['add_by'].'<br><br>';
        endforeach;
        echo $text;
    }

    public function add_misc_income()
    {
        $this->db->select('*');
        $this->db->from('accounts');
        $cashAccounts = $this->db->get()->result_array();
        $data['accounts'] = filterRecordsByAccessIds($cashAccounts, 'id', 'allowed_cash_account_ids');

        $this->db->select('*,misc_incomes.amount as amount');
        $this->db->from('misc_incomes');
        $this->db->join('accounts','accounts.id = misc_incomes.account_id');
        $data['misc_incomes'] = $this->db->get()->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('accounts/misc_income', $data);
        $this->load->view('inc/footer');
    }

    public function insert_misc_income()
    {

        //load the helper
        $this->load->helper('form');

        //Configure
        //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
        $config['upload_path'] = 'uploads/';

        // set the filter image types
        $config['allowed_types'] = 'gif|jpg|png';

        //load the upload library
        $this->load->library('upload', $config);

        $this->upload->initialize($config);

        $this->upload->set_allowed_types('*');

        $data['upload_data'] = '';

        //if not successful, set the error message
        if (!$this->upload->do_upload('picture')) {
            $data = array('msg' => $this->upload->display_errors());
            $picture = '';
        }
        else  {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $picture = $data['upload_data']['file_name'];
            }
        }

        $account = $this->input->post('account_id');
        $title = $this->input->post('title');
        $description = $this->input->post('description');
        $amount = $this->input->post('amount');
        $date = date("Y-m-d");

        $this->db->set('title',$title);
        $this->db->set('description',$description);
        $this->db->set('amount',$amount);
        $this->db->set('account_id',$account);
        $this->db->set('image',$picture);
        if ( $this->db->insert('misc_incomes')){
            $this->db->set('misc_id',$this->db->insert_id());
            $this->db->set('to_account_id',$account);
            $this->db->set('amount',$amount);
            $this->db->set('debit_credit','D');
            $this->db->set('proof_image',$picture);
            $this->db->set('transaction_by',$this->session->userdata('name'));
            $this->db->set('transaction_account_id',$account);
            $this->db->set('reason',"Miscellaneous Income ($title - $description - $date)");
            $this->db->set('created_at',date('Y-m-d H:i:s'));
            $this->db->insert('transactions_history');
            $this->db->set('amount', 'amount +' . $amount . '', false);
            $this->db->where('id', $account);
            $this->db->update('accounts');
            redirect('accounts/add_misc_income');
        }
    }

    public function statements_record() {
        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');

        if ($from_date != '' || $from_date != NULL) {
            $this->db->select('*,statement_upload_record.id as id');
            $this->db->from('statement_upload_record');
            $this->db->join('accounts', 'accounts.id = statement_upload_record.account_id');
            $this->db->where("statement_upload_record.created_at >= '$from_date 00:00:00' and created_at <= '$to_date 23:59:59'");
            $data['upload_records'] = $this->db->get()->result_array();
        }else
            $data['upload_records'] = array();

        $data['from_date'] = $from_date;
        $data['to_date']   = $to_date;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('accounts/statement_uploads', $data);
        $this->load->view('inc/footer');
    }

    public function delete_statement($id)
    {
        $this->db->where("statement_no",$id)->delete('bank_reconciliation_statement');
        $this->db->where("id",$id)->delete('statement_upload_record');
        $this->session->set_flashdata('message','Statement Deleted Successfully.');
        redirect('accounts/statements_record');
    }

    public function untagentry($id)
    {
        $council_data = $this->db->get_where("bank_reconciliation_statement","id = '$id'")->row();
        //CHECK AMOUNT IN ACCOUNT
        $transaction = $this->db->get_where('transactions_history',array('id'=>$council_data->statement_id))->row();
        $transaction_account_id = $transaction->transaction_account_id;
        //GET ACCOUNT DETAILS
        $account = $this->db->get_where('accounts',array('id'=>$transaction_account_id))->row();
        $balance = $account->amount;
        
        $debit_amount = (int)str_replace(',', '', $council_data->debit);
        if($balance>$debit_amount)
        {
            //CORRECT ACCOUNT BALANCE
            $this->db->set('amount', "amount - {$debit_amount}", FALSE);
            $this->db->where('id',$transaction_account_id);
            $this->db->update('accounts');
            
            $this->db->where("id",$council_data->statement_id)->delete('transactions_history');
            $this->db->set("statement_id",null);
            $this->db->where("id",$id);
            $this->db->update("bank_reconciliation_statement");
            echo "Successfully untagged";    
        }
        else
        {
            echo "There is not enough amount in account.";
        }
    }

    public function untag_bank_entry($id)
    {
        $council_data = $this->db->get_where("bank_reconciliation_statement","id = '$id'")->row();
        $this->db->set("bank_transfer_id",null);
        $this->db->where("id",$council_data->bank_transfer_id);
        $this->db->update("bank_reconciliation_statement");

        $this->db->set("bank_transfer_id",null);
        $this->db->where("id",$id);
        $this->db->update("bank_reconciliation_statement");

        echo "Successfully untagged";
    }

    public function yearly_tax_return_report()
    {
        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');
        $selected_accounts = $this->input->post('account_id');

        $year_start = date('Y', strtotime($from_date));

        if ($from_date != '' || $from_date != NULL) {
            $accounts = $this->db->where_in('id',$selected_accounts)->get('accounts')->result_array();
            foreach ($accounts as $key=>$account){
               $day = date("d",strtotime($from_date));
               $tot_day = date("Y-m",strtotime($from_date));
               $account_id = $account['id'];
               for($x=0;$x<30;$x++) {
                   $num = sprintf("%02d", $day);
                   $open = @$this->db->order_by("id","DESC")->get_where("bank_reconciliation_statement","trans_date = '$tot_day-$num' and account_id = '$account_id'")->row();
                   if (count($open) > 0) {
                       $accounts[$key]['opening_balance'] = isset($open) ? (str_replace(',', '', $open->balance) + str_replace(',', '', $open->debit)) - str_replace(',', '', $open->credit) : 0;
                       break;
                   }
                   $day++;
               }
               $this->db->select('*,bank_reconciliation_statement.statement_id as str_id,bank_reconciliation_statement.id as sta_id');
               $this->db->from('bank_reconciliation_statement');
               $this->db->join('payments','payments.statement_id = bank_reconciliation_statement.id','left');
               $this->db->where("bank_reconciliation_statement.trans_date >= '".$from_date."' and bank_reconciliation_statement.trans_date <= '".$to_date."' and account_id = '$account_id'");
               $this->db->group_by("bank_reconciliation_statement.description,bank_reconciliation_statement.trans_date,bank_reconciliation_statement.credit,bank_reconciliation_statement.debit");
               $statements=$this->db->get()->result_array();

               $debit = 0.0;
               $credit = 0.0;
               $sent_own = 0.0;
               $receive_own = 0.0;
               $uncount_debit = 0;
               $uncount_credit = 0;
               $total_profit_given = 0;

               foreach ($statements as $statement){
                   if ($statement['debit'] != "") {
                       $debit += strpos($statement['debit'],',') ? str_replace(',', '', $statement['debit']) : $statement['debit'];
                       if ($statement['payment_id'] ==  NULL && $statement['related_to'] ==  0 && $statement['bank_transfer_id'] ==  NULL &&
                           $statement['expense_id'] ==  NULL && $statement['statement_id'] ==  NULL && $statement['str_id'] == NULL && $statement['closing_id'] ==  NULL &&
                           $statement['is_council_fee'] ==  NULL && $statement['paypro_id'] ==  NULL && $statement['salary_expense_ids'] ==  NULL )
                       {
                           $uncount_debit++;
                       }
                       if ($statement['bank_transfer_id'] != NULL)
                            $sent_own += strpos($statement['debit'],',') ? str_replace(',', '', $statement['debit']) : $statement['debit'];

                       if ($statement['profit_distribution_id'] != NULL)
                           $total_profit_given += strpos($statement['debit'],',') ? str_replace(',', '', $statement['debit']) : $statement['debit'];

                   }
                   if ($statement['credit'] != "") {
                       $credit += strpos($statement['credit'],',') ? str_replace(',', '', $statement['credit']) : $statement['credit'];
                       if ($statement['payment_id'] ==  NULL && $statement['related_to'] ==  0 && $statement['bank_transfer_id'] ==  NULL &&
                           $statement['expense_id'] ==  NULL && $statement['statement_id'] ==  NULL && $statement['str_id'] == NULL && $statement['closing_id'] ==  NULL &&
                           $statement['is_council_fee'] ==  NULL && $statement['paypro_id'] ==  NULL && $statement['salary_expense_ids'] ==  NULL )
                       {
                           $uncount_credit++;
                       }
                       if ($statement['bank_transfer_id'] != NULL) {
                           $this->db->select('accounts.taxable');
                           $this->db->from('bank_reconciliation_statement');
                           $this->db->join('accounts','accounts.id = bank_reconciliation_statement.account_id');
                           $this->db->where("bank_reconciliation_statement.id = '".$statement['bank_transfer_id']."'");
                           $type = $this->db->get()->row()->taxable;
                           if ($type == 1)
                                $receive_own += strpos($statement['credit'], ',') ? str_replace(',', '', $statement['credit']) : $statement['credit'];
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
            }
           $data['yearly_statement'] = $accounts;
        }
        else  {
            $from_date = date("Y-m-d");
            $to_date = date("Y-m-d");
        }

        $data['from_date'] = $from_date;
        $data['to_date']   = $to_date;
        $data['accounts']   = $this->db->query('SELECT * FROM `accounts` WHERE `type` = "1"')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('accounts/yearly_statement',$data);
        $this->load->view('inc/footer');
    }

    public function view_return_report($from_date,$to_date,$account_id,$type)
    {
        $this->db->select('*,bank_reconciliation_statement.id as trans_id,bank_reconciliation_statement.statement_id as str_id,bank_reconciliation_statement.closing_id as closing_bank_id');
        $this->db->from('bank_reconciliation_statement');
        $this->db->join('payments','payments.statement_id = bank_reconciliation_statement.id','left');
        $this->db->join('accounts','accounts.id = bank_reconciliation_statement.account_id');
        $this->db->where("bank_reconciliation_statement.trans_date >= '".$from_date."' and bank_reconciliation_statement.trans_date <= '".$to_date."' and account_id = '$account_id'");

        if ($type == 'debit' || $type == 'uncount_debit')
            $this->db->where("(bank_reconciliation_statement.debit != '' and bank_reconciliation_statement.debit is NOT NULL)");
        if ($type == 'credit' || $type == 'uncount_credit')
            $this->db->where("(bank_reconciliation_statement.credit != '' and bank_reconciliation_statement.credit is NOT NULL)");

        if ($type == 'sent')
            $this->db->where("(bank_reconciliation_statement.debit != '' and bank_reconciliation_statement.debit is NOT NULL and bank_reconciliation_statement.bank_transfer_id IS NOT NULL)");

        if ($type == 'received')
            $this->db->where("(bank_reconciliation_statement.credit != '' and bank_reconciliation_statement.credit is NOT NULL and bank_reconciliation_statement.bank_transfer_id IS NOT NULL)");


        if ($type == 'profit')
            $this->db->where("(bank_reconciliation_statement.debit != '' and bank_reconciliation_statement.debit is NOT NULL and bank_reconciliation_statement.profit_distribution_id IS NOT NULL)");

        $this->db->group_by("bank_reconciliation_statement.description,bank_reconciliation_statement.trans_date,bank_reconciliation_statement.credit,bank_reconciliation_statement.debit")
                 ->order_by("bank_reconciliation_statement.id","DESC");
        $statements=$this->db->get()->result_array();

        if ($type == 'uncount_debit'){
            foreach($statements as $key=>$entry) {
                if ($entry['debit'] != "")
                    if (
                        $entry['payment_id'] == NULL && $entry['related_to'] == 0 && $entry['bank_transfer_id'] == NULL &&
                        $entry['expense_id'] == NULL && $entry['statement_id']== NULL && $entry['str_id'] == NULL && $entry['closing_id'] == NULL &&
                        $entry['is_council_fee'] == NULL && $entry['paypro_id'] == NULL && $entry['salary_expense_ids'] == NULL
                    ) {
                    } else {
                        unset($statements[$key]);
                    }
            }
        }

        if ($type == 'uncount_credit'){
            foreach($statements as $key=>$entry) {
                if ($entry['credit'] != "")
                    if (
                        $entry['payment_id'] == NULL && $entry['related_to'] == 0 && $entry['bank_transfer_id'] == NULL &&
                        $entry['expense_id'] == NULL && $entry['statement_id'] == NULL && $entry['closing_id'] == NULL &&
                        $entry['is_council_fee'] == NULL && $entry['paypro_id'] == NULL && $entry['salary_expense_ids'] == NULL
                    ) {
                    } else {
                        unset($statements[$key]);
                    }
            }
        }

        if ($type == 'sent' || $type == 'received'){
            foreach($statements as $key=>$entry) {
                if ($entry['credit'] != "")
                    if ($entry['bank_transfer_id'] != NULL) {
                        $this->db->select('accounts.taxable');
                        $this->db->from('bank_reconciliation_statement');
                        $this->db->join('accounts','accounts.id = bank_reconciliation_statement.account_id');
                        $this->db->where("bank_reconciliation_statement.id = '".$entry['bank_transfer_id']."'");
                        $type_tax = $this->db->get()->row()->taxable;
                        if ($type_tax != 1)
                            unset($statements[$key]);
                    }
            }
        }

        $data['statements'] = $statements;
        $data['type'] = $type;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('accounts/view_statement',$data);
        $this->load->view('inc/footer');
    }

    public function view_count_return_report($from_date,$to_date,$account_id,$type)
    {
        $this->db->select('*,bank_reconciliation_statement.id as trans_id,bank_reconciliation_statement.statement_id as str_id,bank_reconciliation_statement.closing_id as closing_bank_id');
        $this->db->from('bank_reconciliation_statement');
        $this->db->join('accounts','accounts.id = bank_reconciliation_statement.account_id');
        $this->db->where("bank_reconciliation_statement.trans_date >= '".$from_date."' and bank_reconciliation_statement.trans_date <= '".$to_date."' and account_id = '$account_id'");
        $this->db->group_by("bank_reconciliation_statement.description,bank_reconciliation_statement.trans_date,bank_reconciliation_statement.credit,bank_reconciliation_statement.debit")
            ->order_by("bank_reconciliation_statement.id","DESC");
        $statements=$this->db->get()->result_array();

        $data['type'] = $type;
        $out = array();
        foreach ($statements as $key => $value){
            $index = $value['trans_date'];
            $find_val = $this->findKeyInArray($out,'trans_date',$index);
            if ($find_val > -1){
                $out[$find_val]['count']++;
            } else {
                array_push($out,array("bank_name"=>$value['account_title'].' '.$value['account_name'],"trans_date"=>$value['trans_date'],"count"=>1));
            }
        }

        $columns = array_column($out, "trans_date");
        array_multisort($columns, SORT_ASC, $out);
        $data['statements'] = $out;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('accounts/view_count_statement',$data);
        $this->load->view('inc/footer');
    }

    public function delete_entry()
    {
        $id = $this->input->post('id');
        $transaction = $this->db->get_where('bank_reconciliation_statement',array('id'=>$id))->result_array();
        //CHECK AND DELETE EXPENSE IF ANY
        if($transaction[0]['expense_id']!=NULL)
        {
            $this->db->where('expense_id',$transaction[0]['expense_id']);
            $this->db->delete('expenses');
        }
        //CHECK AND UNTAG BANK TRANSFER
        if($transaction[0]['bank_transfer_id']!=NULL)
        {
            $this->db->set('bank_transfer_id',NULL);
            $this->db->where('id',$transaction[0]['bank_transfer_id']);
            $this->db->update('bank_reconciliation_statement');
        }
        
        //CHECK AND UNTAG FEE IF ANY
        $payment = $this->db->get_where('payments',array('statement_id'=>$id))->result_array();
        if(count($payment)>0)
        {
            $this->db->set('paid_challans','');
            $this->db->set('merged_challan',NULL);
            $this->db->set('scan_challan','');
            $this->db->set('online_scan_challan','');
            $this->db->set('upload_scan_challan','0');
            $this->db->set('delete_scan_challan','0');
            $this->db->set('fine_application','');
            $this->db->set('online_fine_application','');
            $this->db->set('upload_fine_application','0');
            $this->db->set('delete_fine_application','0');
            $this->db->set('actual_amount','0');
            $this->db->set('paid','0');
            $this->db->set('discount','0');
            $this->db->set('disc_per_inst','0');
            $this->db->set('paid_date','0000-00-00');
            $this->db->set('remaining_installment_amount','0');
            $this->db->set('extra_amount','0');
            $this->db->set('paid_by','');
            $this->db->set('actual_paid_date','0000-00-00');
            $this->db->set('clear_by','');
            $this->db->set('fee_pay_through','');
            $this->db->set('bank_details','');
            $this->db->set('tid_no','');
            $this->db->set('bank_challan_no','');
            $this->db->set('fee_submission_time','');
            $this->db->set('fee_submit_type','');
            $this->db->set('submitted_fee_campus_id','0');
            $this->db->set('book_no','');
            $this->db->set('receipt_no','');
            $this->db->set('settlement_id',NULL);
            $this->db->set('settlement_payment_id',NULL);
            $this->db->where('statement_id',$id);
            $this->db->update('payments');
        }
        $trans = $this->db->get_where("bank_reconciliation_statement","id = '$id'")->row();
        $this->db->where(array("trans_date"=>$trans->trans_date,"account_id"=>$trans->account_id
                                ,"description"=>$trans->description,"reference_no"=>$trans->reference_no
                                ,"debit"=>$trans->debit,"credit"=>$trans->credit
                                ,"balance"=>$trans->balance
        ))->delete('bank_reconciliation_statement');
        echo "Successfully Deleted";
    }

    function findKeyInArray($array, $keySearch,$value)
    {
        $index = -1;
        foreach ($array as $key => $item) {
            if ($item[$keySearch] === $value) {
                $index = $key;
            }
        }
        return $index;
    }

    public function view_details($newfrom_date = NULL,$newto_date = NULL,$newsetype = NULL)
    {
        $access = checkUserAccess();
        $campus_ids = @explode(',',$access[0]['campus_ids']);
        $setype='';


        if($newsetype != NULL && $newsetype != '')
        {

            $setype = $newsetype;
            $from_date = $newfrom_date;
            $to_date = $newto_date;

        }else{

            if($newto_date != NULL && $newto_date != '')
            {
                $from_date = $newfrom_date;
                $to_date = $newto_date;
            }else{
                $from_date = $this->input->post('from_date');
                $to_date = $this->input->post('to_date');

            }


            if ($this->input->post('setype') === 'Pending')
            {
                $setype='0';
            }
            if ($this->input->post('setype') === 'Approved')
            {
                $setype='1';
            }
            if ($this->input->post('setype') === 'Rejected')
            {
                $setype='2';
            }
            if ($this->input->post('setype') === 'Reversed')
            {
                $setype='3';
            }

        }



        if( $from_date == '' ){

            $from_date = date('Y-m-d');
            $to_date = date('Y-m-d');

        }


        //$data['expenses'] = $this->db->get_where('expenses', array('date>='=>$from_date, 'date<='=>$to_date))->result_array();
        $this->db->select('*');
        $this->db->from('expenses');
        $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
        $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
        $this->db->where(array('expenses.date>='=>$from_date, 'expenses.date<='=>$to_date));

        if ($setype !== '')
        {
            $this->db->where('expenses.approved_status', $setype);

        }
        if ($access[0]['expense_view_user'] !== '1' && $this->session->userdata('role')!='Admin')
        {
            $this->db->where('expenses.add_by_id', $this->session->userdata('user_id'));

        }

        if($this->session->userdata('role')!='Admin')
        {
            $this->db->where_in('expenses.campus_id', $campus_ids);
        }





        $data['expenses'] = $this->db->get()->result_array();
        $data['from_date'] = $from_date;
        $data['to_date'] = $to_date;
        $data['setype'] = $setype;


        //$query = 'SELECT sum(amount) as total_expenses FROM expenses WHERE date>="'.$from_date.'" AND date<="'.$to_date.'"';
        //$data['total_expense'] = $this->db->query($query)->result_array();
        $this->db->select_sum('amount');
        $this->db->from('expenses');
        if($this->session->userdata('role')!='Admin')
        {
            $this->db->where_in('campus_id', $campus_ids);
        }
        $this->db->where(array('date>='=>$from_date, 'date<='=>$to_date));
        $data['total_expense'] = $this->db->get()->result_array();

        $data['pending'] = 0;
        $data['approved'] = 0;
        $data['rejected'] = 0;
        $data['reversed'] = 0;

        foreach ($data['expenses'] as $exp)
        {
            if ($exp['approved_status'] === '0')
            {
                $data['pending']++;


            }elseif ($exp['approved_status'] === '1')
            {
                $data['approved']++;

            }elseif ($exp['approved_status'] === '2')
            {
                $data['rejected']++;

            }elseif ($exp['approved_status'] === '3')
            {
                $data['reversed']++;

            }

        }




        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('expenses/all_expenses', $data);
        $this->load->view('inc/footer');
    }

    public function add_cash_deposit()
    {
        $trans_id = $this->input->post('cash_trans_id');
        $transaction=$this->db->get_where('bank_reconciliation_statement','id = '.$trans_id)->row();
        $cash_account_id = $this->input->post('cash_account_id');
        $reason = $this->input->post('reason_disc');
        $cash_deposit_amount = $this->input->post('cash_deposit_amount');

        $this->db->set('from_account_id', $cash_account_id);
        $this->db->set('to_account_id', $transaction->account_id);
        $this->db->set('transaction_account_id', $cash_account_id);
        $this->db->set('amount', $cash_deposit_amount);
        $this->db->set('debit_credit', 'C');
        $this->db->set('transaction_by', $this->session->userdata('name'));
        $this->db->set('reason', 'Funds Transfer '.$reason);
        $this->db->set('proof_image','');
        $this->db->set('created_at',$transaction->trans_date.' '.date('H:i:s'));
        $this->db->insert('transactions_history');
        $idf = $this->db->insert_id();

        $this->db->set('statement_id', $idf);
        $this->db->where('id', $transaction->id);
        $this->db->update('bank_reconciliation_statement');

        $this->db->select('*,transactions_history.amount as trans_amount');
        $this->db->from('transactions_history');
        $this->db->join('accounts','accounts.id=transactions_history.from_account_id','left');
        $this->db->where(array('transactions_history.id' => $idf));
        $expense = $this->db->get()->row();

        $this->db->set('amount', 'amount -' . $cash_deposit_amount . '', false);
        $this->db->where('id', $cash_account_id);
        $this->db->update('accounts');

        echo '<strong> Received from Account : </strong>'. $expense->account_name.'<br>
              <strong> Date : </strong>'. date('Y-m-d',strtotime($expense->created_at)).'<br>
              <strong> Amount : </strong>'. $expense->trans_amount .' <br>';
    }

    public function find_profit_transactions()
    {
        $bank_trans_id = $this->input->post('bank_trans_id');
        $transaction=$this->db->get_where('bank_reconciliation_statement','id = '.$bank_trans_id)->row();
        $amount = $this->input->post('amount');

        $this->db->select('*');
        $this->db->from('profit_distribution');
        $this->db->join('campuses','campuses.campus_id = profit_distribution.campus_id');
        $this->db->join('users','users.user_id = profit_distribution.user_id');
        $this->db->where("close_type = 'bank' and tagged = 'no' and CAST(amount as SIGNED INTEGER) = $amount");
        $entries = $this->db->get()->result_array();

        $html = '';
        $i=0;
        foreach($entries as $closing_rule):
            $f=$i+1;
            $html.=" <tr>
                <td>
                    $f
                     <input class='form-check-input' type='radio' name='tag_id'  value='{$closing_rule['profit_distribution_id']}' required>
                </td>
                <td>
                    {$closing_rule['from_date']}
                </td>
                <td>
                    {$closing_rule['to_date']}
                </td>
                <td>
                    {$closing_rule['first_name']} {$closing_rule['last_name'] }
                </td>
                <td>
                    {$closing_rule['campus_name']}
                </td>
                <td>
                    {$closing_rule['amount']}
                </td>
                <td>
                    {$closing_rule['percentage']}
                </td>               
            </tr>";
            $i++;
        endforeach;
        echo $html;
    }

    public function add_profit_deposit()
    {
        $trans_id = $this->input->post('bank_trans_id');
        $profit_id = $this->input->post('tag_id');

        $this->db->set('profit_distribution_id', $profit_id);
        $this->db->where('id', $trans_id);
        $this->db->update('bank_reconciliation_statement');

        $this->db->set('tagged', 'yes');
        $this->db->set('take_profit', 1);
        $this->db->where('profit_distribution_id', $profit_id);
        $this->db->update('profit_distribution');

        $this->db->select('*');
        $this->db->from('profit_distribution');
        $this->db->join('campuses','campuses.campus_id = profit_distribution.campus_id');
        $this->db->join('users','users.user_id = profit_distribution.user_id');
        $this->db->where("profit_distribution_id",$profit_id);
        $entry = $this->db->get()->row();

        echo '<strong> Profit To : </strong>'. $entry->first_name.' '.$entry->last_name.'<br>
              <strong> From Date : </strong>'.$entry->from_date.'<br>
              <strong> To Date : </strong>'.$entry->to_date.'<br>
              <strong> Campus : </strong>'. $entry->campus_name .' <br>';
    }

    public function find_loan_transactions()
    {
        $bank_trans_id = $this->input->post('bank_trans_id');
        $transaction=$this->db->get_where('bank_reconciliation_statement','id = '.$bank_trans_id)->row();
        $amount = $this->input->post('amount');

        $this->db->select('users.*,loans.*');
        $this->db->from('loans');
        $this->db->join('users','loans.user_id = users.user_id','inner');
        $this->db->where("loans.status = 1 and cash_given IS NULL and amount_approved = $amount");
        $this->db->order_by("loans.id", "Desc");
        $entries = $this->db->get()->result_array();

        $html = '';
        $i=0;
        foreach($entries as $closing_rule):
            $f=$i+1;
            $html.=" <tr>
                <td>
                    $f
                     <input class='form-check-input' type='radio' name='tag_id'  value='{$closing_rule['id']}' required>
                </td>
                <td>
                    {$closing_rule['first_name']} {$closing_rule['last_name'] }
                </td>
                <td>
                    {$closing_rule['cnic']}
                </td>
                <td>
                    {$closing_rule['type']}
                </td>
                <td>
                    {$closing_rule['amount_approved']}
                </td>
                <td>
                    {$closing_rule['months_approved']}
                </td>
                <td>
                    {$closing_rule['created_at']}
                </td> 
                <td>
                    {$closing_rule['created_at']}
                </td>               
            </tr>";
            $i++;
        endforeach;
        echo $html;
    }

    public function add_loan_deposit()
    {
        $trans_id = $this->input->post('bank_trans_id');
        $loan_id = $this->input->post('tag_id');

        $this->db->set('loan_id', $loan_id);
        $this->db->where('id', $trans_id);
        $this->db->update('bank_reconciliation_statement');

        $my_loan = $this->db->get_where('loans','id = "'.$loan_id.'"')->row();
        $approve_amount=$my_loan->amount_approved;
        $months=$my_loan->months_approved;

        $amountavg=$approve_amount/$months;

        $this->db->set('cash_given', $approve_amount);
        $this->db->set('give_through', 'bank');
        $this->db->set('cash_given_by', 1);
        $this->db->where("(loans.id ='".$loan_id."')");
        $this->db->update('loans');

        $time = date("Y-m-d");
        for($i=1; $i<=$months; $i++)
        {
            if ($my_loan->type == 'ADVANCE')
                $dead_line = $time;
            else {
                $time = strtotime(date("Y-m-d"));
                $dead_line = date("Y-m-30", strtotime("+$i month", $time));
            }

            $this->db->set('amount', $amountavg);
            $this->db->set('due_date', $dead_line);
            $this->db->set('loan_id', $loan_id);
            $this->db->set('created_by',$this->session->userdata('user_id'));
            $this->db->insert('loan_plan');
        }
        $this->db->select('users.*,loans.*');
        $this->db->from('loans');
        $this->db->join('users','users.user_id = loans.user_id');
        $this->db->where("loans.id",$loan_id);
        $entry = $this->db->get()->row();
        $mainloan = $my_loan;
        $this->db->set('campus_id',$entry->campus_id);
        if($mainloan->type=='ADVANCE'){
            $this->db->set('expense_category_id','30');
        }else{
            $this->db->set('expense_category_id','31');
        }

        $this->db->set('title','Advance / Loan');
        $this->db->set('date',date('Y-m-d'));
        $this->db->set('amount',$approve_amount);
        $this->db->set('purpose','Advance / Loan Given to Employee');
        $this->db->set('user_id',$mainloan->user_id);
        $this->db->set('actual_date', date('Y-m-d H:i:s'));
        $this->db->set('image', '');
        $this->db->set('approved_status', '1');
        $this->db->set('paid_type', 'bank');
        $this->db->set('loan_id', $loan_id);
        $this->db->set('add_by_id', $this->session->userdata('user_id'));
        $this->db->set('add_by', $this->session->userdata('name'));
        $this->db->insert('expenses');



        echo '<strong> Loan ID : </strong>loan-'. $entry->id.'<br>
              <strong> Given To : </strong>'.$entry->first_name.' '.$entry->last_name.'<br>
              <strong> Amount : </strong>'.$entry->cash_given.'<br>
              <strong> Months : </strong>'.$entry->months_approved.'<br>';
    }

    public function find_salaries($exp_id)
    {

        $this->db->select('*');
        $this->db->from('payroll');
        $this->db->join('users','users.user_id = payroll.user_id','left');
        $this->db->where("payroll.expense_id ='".$exp_id."'");
        $entries=$this->db->get()->result_array();

        $html = '';
        $i=0;
        foreach($entries as $closing_rule):

            $f=$i+1;
            $html.=" <tr>
                <td >
                    $f
                     <input class='form-check-input' type='radio' name='tag_id'  value='{$closing_rule['id']}' required>
                </td>
                <td>
                    {$closing_rule['first_name']} {$closing_rule['last_name']}
                </td>
                <td>
                    {$closing_rule['payroll_month']} {$closing_rule['payroll_year']}
                </td>
                <td>
                    {$closing_rule['earned_salary']}
                </td>
                <td>
                    {$closing_rule['created_at']}
                </td>
               
            </tr>";

            $i++;
        endforeach;

        echo $html;


    }

    public function find_reverse_transactions()
    {
        $bank_trans_id = $this->input->post('bank_trans_id');
        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');
        $tag_id = $this->input->post('tag_id');
        $this->db->select('*');
        $this->db->from('payroll');
        $this->db->where("id ='".$tag_id."'");
        $entry=$this->db->get()->row();

        if ($entry) {

            $this->db->select('*,bank_reconciliation_statement.id as tidx');
            $this->db->from('bank_reconciliation_statement');
            $this->db->join('accounts', 'accounts.id = bank_reconciliation_statement.account_id', 'left');
            $this->db->where("bank_reconciliation_statement.trans_date >='" . $from_date . "' and bank_reconciliation_statement.trans_date <='" . $to_date . "' and CAST(REPLACE(credit,',','') as SIGNED INTEGER) = $entry->earned_salary");
            $this->db->group_by("bank_reconciliation_statement.description");
            $entries = $this->db->get()->result_array();

            $html = '';
            $i = 0;
            foreach ($entries as $closing_rule):

                $f = $i + 1;
                $html .= " <tr>
                <td >
                    $f
                     <input class='form-check-input' type='radio' name='tag_id'  value='{$closing_rule['tidx']}' required>
                     <input class='form-check-input' type='hidden' name='payroll_id' value='$tag_id' required>
                </td>
                <td>
                    {$closing_rule['account_title']} {$closing_rule['account_name']}
                </td>
                <td>
                    {$closing_rule['trans_date']}
                </td>
                <td>
                    {$closing_rule['description']}
                </td>
                <td>
                    {$closing_rule['debit']}
                </td>
                <td>
                    {$closing_rule['credit']}
                </td>
                <td>
                    {$closing_rule['balance']}
                </td>
            </tr>";

                $i++;
            endforeach;

            echo $html;
        }else
            echo '';
    }

    public function tag_salary_reverse()
    {
        $trans_id = $this->input->post('bank_trans_id');
        $tag_id = $this->input->post('tag_id');
        $payroll_id = $this->input->post('payroll_id');
        $this->db->select('*');
        $this->db->from('payroll');
        $this->db->where("id ='".$payroll_id."'");
        $entry=$this->db->get()->row();

        $this->db->set('reversal_payroll_id', $payroll_id);
        $this->db->set('reversal_payroll_expense_id', $entry->expense_id);
        $this->db->set('reversal_payroll_trans_id', $trans_id);
        $this->db->where('id', $tag_id);
        $this->db->update('bank_reconciliation_statement');

        $this->db->set('expense_id', null);
        $this->db->set('disburse_through', "pending");
        $this->db->where('id', $payroll_id);
        $this->db->update('payroll');

        echo 'success';
    }

    public function bulk_fee_creator()
    {
        $access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['campus_ids']);
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campus_id', $campus_ids);
		}
		$this->db->where('campuses.status',1);
		$data['campuses'] = $this->db->get('campuses')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('accounts/bulk_fee_creator', $data);
        $this->load->view('inc/footer');
    }

    public function bulk_fee_creation()
    {
        $fee_type = $this->input->post('fee_type');

        $class_id = $this->input->post('class_id');
        $students = $this->db->get_where('students',array('class_id'=>$class_id))->result_array();

        foreach($students as $student)
        {
            if($fee_type=='College Fee')
            {
                $dead_line = $this->input->post('extra_fee_dead_line');
                $challan_no = $this->getChallanNo();

                $this->db->set('amount', $this->input->post('extra_fee'));
                $this->db->set('dead_line', $dead_line);
                $this->db->set('student_id', $student['student_id']);
                $this->db->set('payment_plan', 'Custom Plan');
                $this->db->set('payment_comment', $this->input->post('payment_comment'));
                $this->db->set('special_comment', $this->input->post('special_comment'));
                $this->db->set('challan_no', @$challan_no);
                $this->db->set('add_by', $this->session->userdata('name'));
                $this->db->set('last_edit', $this->session->userdata('name'));
                $this->db->insert('payments');
            }

            if($fee_type=='Extra Fee')
            {
                $dead_line = $this->input->post('extra_fee_dead_line');
                $challan_no = $this->getChallanNo();

                $this->db->set('amount', $this->input->post('extra_fee'));
                $this->db->set('dead_line', $dead_line);
                $this->db->set('student_id', $student['student_id']);
                $this->db->set('payment_plan', 'Custom Plan');
                $this->db->set('payment_comment', $this->input->post('payment_comment').' For '.$this->input->post('fee_for'));
                $this->db->set('special_comment', $this->input->post('special_comment'));
                $this->db->set('challan_no', $challan_no);
                $this->db->set('add_by', $this->session->userdata('name'));
                $this->db->set('last_edit', $this->session->userdata('name'));
                $this->db->insert('payments');
            }

            if($fee_type=='consulation fee')
            {
                $dead_line = $this->input->post('extra_fee_dead_line');
                $challan_no = $this->getChallanNo();

                $this->db->set('amount', $this->input->post('extra_fee'));
                $this->db->set('dead_line', $dead_line);
                $this->db->set('student_id', $student['student_id']);
                $this->db->set('payment_plan', 'consulation fee');
                $this->db->set('payment_comment', 'This fee for next exam # '.$this->input->post('exam_no').' '.$this->input->post('class'));
                $this->db->set('special_comment', $this->input->post('special_comment'));
                $this->db->set('challan_no', $challan_no);
                $this->db->set('add_by', $this->session->userdata('name'));
                $this->db->set('last_edit', $this->session->userdata('name'));
                $this->db->insert('payments');
            }
        }
        $this->session->set_flashdata('message','Bulk Payments Created Successfully.');
        redirect('accounts/bulk_fee_creator');
    }

    public function getChallanNo()
    {
        $random_number = rand(1000, 999999999);
        $check_challan_no = $this->db->get_where('payments', array('challan_no'=>$random_number))->result_array();
        if(count($check_challan_no)>0)
        {
            $random_number = $this->getChallanNo();
        }
        else
        {
            return $random_number;
        }
    }
}
