<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Tax extends CI_Controller {
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
	}
	
	public function bank_report()
    {
        set_time_limit(0);
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
               $this->db->select('bank_reconciliation_statement.*,payments.*,bank_reconciliation_statement.statement_id as str_id,bank_reconciliation_statement.id as sta_id,transactions_history.id thid,closing_perday.id as clid');
               $this->db->from('bank_reconciliation_statement');
               $this->db->join('payments','payments.statement_id = bank_reconciliation_statement.id','left');
               $this->db->join('transactions_history','transactions_history.id = bank_reconciliation_statement.statement_id','left');
               $this->db->join('closing_perday','closing_perday.id = bank_reconciliation_statement.closing_id','left');
               $this->db->where("bank_reconciliation_statement.trans_date >= '".$from_date."' and bank_reconciliation_statement.trans_date <= '".$to_date."' and bank_reconciliation_statement.account_id = '$account_id'");
               $this->db->group_by("bank_reconciliation_statement.description,bank_reconciliation_statement.trans_date,bank_reconciliation_statement.credit,bank_reconciliation_statement.debit");
               $statements=$this->db->get()->result_array();

            //    echo '<pre>';
            //    print_r($statements);
            //    echo '</pre>';
            //    exit;


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

               foreach ($statements as $statement){
                   if ($statement['debit'] != "") {
                       $debit += strpos($statement['debit'],',') ? str_replace(',', '', $statement['debit']) : $statement['debit'];
                       if ($statement['payment_id'] ==  NULL && $statement['related_to'] ==  0 && $statement['bank_transfer_id'] ==  NULL &&
                           $statement['expense_id'] ==  NULL && $statement['statement_id'] ==  NULL && $statement['str_id'] == NULL && $statement['closing_id'] ==  NULL &&
                           $statement['is_council_fee'] ==  NULL && $statement['paypro_id'] ==  NULL && $statement['salary_expense_ids'] ==  NULL && $statement['loan_id'] ==  NULL )
                       {
                           $uncount_debit++;
                       }
                       if ($statement['bank_transfer_id'] != NULL)
                            $sent_own += strpos($statement['debit'],',') ? str_replace(',', '', $statement['debit']) : $statement['debit'];

                       if ($statement['profit_distribution_id'] != NULL)
                           $total_profit_given += strpos($statement['debit'],',') ? str_replace(',', '', $statement['debit']) : $statement['debit'];

                        if ($statement['thid']!=NULL)
                           $bank_account_to_cash_account += strpos($statement['debit'],',') ? str_replace(',', '', $statement['debit']) : $statement['debit'];

                        if ($statement['expense_id']!=NULL)
                           $expenses += strpos($statement['debit'],',') ? str_replace(',', '', $statement['debit']) : $statement['debit'];

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
                       if ($statement['thid']!=NULL)
                           $cash_account_to_bank_account += strpos($statement['credit'],',') ? str_replace(',', '', $statement['credit']) : $statement['credit'];
                        
                        if ($statement['clid']!=NULL)
                           $closing_credit_in_bank += strpos($statement['credit'],',') ? str_replace(',', '', $statement['credit']) : $statement['credit'];
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
               sleep(5);
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
        $this->load->view('tax/bank_report',$data);
        $this->load->view('inc/footer');
    }

	public function view_return_report($from_date,$to_date,$account_id,$type)
    {
        $this->db->select('*,bank_reconciliation_statement.id as trans_id,bank_reconciliation_statement.statement_id as str_id,bank_reconciliation_statement.closing_id as closing_bank_id,closing_perday.campus_id as closing_campus_id,closing_perday.partialy_closed_image as closing_image,expenses.campus_id as expense_campus_id,expenses.expense_category_id,expenses.user_id,expenses.title as expense_title, expenses.image as expense_image,expenses.online_image as expense_online_image');
        $this->db->from('bank_reconciliation_statement');
        $this->db->join('payments','payments.statement_id = bank_reconciliation_statement.id','left');
        $this->db->join('transactions_history','transactions_history.id = bank_reconciliation_statement.statement_id','left');
        $this->db->join('closing_perday','closing_perday.id = bank_reconciliation_statement.closing_id','left');
        $this->db->join('expenses','expenses.expense_id = bank_reconciliation_statement.expense_id','left');
        $this->db->join('accounts','accounts.id = bank_reconciliation_statement.account_id');
        $this->db->where("bank_reconciliation_statement.trans_date >= '".$from_date."' and bank_reconciliation_statement.trans_date <= '".$to_date."' and bank_reconciliation_statement.account_id = '$account_id'");

        if ($type == 'debit' || $type == 'uncount_debit')
            $this->db->where("(bank_reconciliation_statement.debit != '' and bank_reconciliation_statement.debit is NOT NULL and bank_reconciliation_statement.loan_id is NOT NULL)");
        if ($type == 'credit' || $type == 'uncount_credit')
            $this->db->where("(bank_reconciliation_statement.credit != '' and bank_reconciliation_statement.credit is NOT NULL)");

        if ($type == 'sent')
            $this->db->where("(bank_reconciliation_statement.debit != '' and bank_reconciliation_statement.debit is NOT NULL and bank_reconciliation_statement.bank_transfer_id IS NOT NULL)");

        if ($type == 'received')
            $this->db->where("(bank_reconciliation_statement.credit != '' and bank_reconciliation_statement.credit is NOT NULL and bank_reconciliation_statement.bank_transfer_id IS NOT NULL)");


        if ($type == 'profit')
            $this->db->where("(bank_reconciliation_statement.debit != '' and bank_reconciliation_statement.debit is NOT NULL and bank_reconciliation_statement.profit_distribution_id IS NOT NULL)");
        
        if ($type == 'bank_account_to_cash_accounts')
            $this->db->where("(bank_reconciliation_statement.debit != '' and bank_reconciliation_statement.debit is NOT NULL and transactions_history.id IS NOT NULL)");

        if ($type == 'cash_account_to_bank_accounts')
            $this->db->where("(bank_reconciliation_statement.credit != '' and bank_reconciliation_statement.credit is NOT NULL and transactions_history.id IS NOT NULL)");

        if ($type == 'closing_credit_in_bank')
            $this->db->where("(bank_reconciliation_statement.credit != '' and bank_reconciliation_statement.credit is NOT NULL and closing_perday.id IS NOT NULL)");

        if ($type == 'expenses')
            $this->db->where("(bank_reconciliation_statement.debit != '' and bank_reconciliation_statement.debit is NOT NULL and bank_reconciliation_statement.expense_id IS NOT NULL)");

        $this->db->group_by("bank_reconciliation_statement.description,bank_reconciliation_statement.trans_date,bank_reconciliation_statement.credit,bank_reconciliation_statement.debit")
                 ->order_by("bank_reconciliation_statement.id","DESC");
        $statements=$this->db->get()->result_array();

        if ($type == 'uncount_debit'){
            foreach($statements as $key=>$entry) {
                if ($entry['debit'] != "")
                    if (
                        $entry['payment_id'] == NULL && $entry['related_to'] == 0 && $entry['bank_transfer_id'] == NULL &&
                        $entry['expense_id'] == NULL && $entry['statement_id']== NULL && $entry['str_id'] == NULL && $entry['closing_id'] == NULL &&
                        $entry['is_council_fee'] == NULL && $entry['paypro_id'] == NULL && $entry['salary_expense_ids'] == NULL && $entry['loan_id'] == NULL
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
        $this->load->view('tax/view_statement',$data);
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
        $this->load->view('tax/view_count_statement',$data);
        $this->load->view('inc/footer');
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

	public function expense_report_college_headwise()
    {
        $access = checkUserAccess();
        $campus_ids = @explode(',',$access[0]['campus_ids']);
        $data['campuses'] = $this->db->where_in( array('campus_id'=>$campus_ids))->get('campuses')->result_array();
        $data['categories'] = $this->db->get_where('expense_category','has_sub = 0')->result_array();

        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');


        if( $from_date == '' ){

            $from_date = date('Y-m-d');
            $to_date = date('Y-m-d');


        }else{

            $this->db->select('sum(expenses.amount) as total_amount,campuses.campus_id,campuses.campus_name,expense_category.name,expense_category.expense_category_id');
            $this->db->from('expenses');
            $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
            $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
            $this->db->where(array('expenses.date>='=>$from_date, 'expenses.date<='=>$to_date));
            $this->db->where_in('expenses.expense_category_id',$this->input->post('categories'));
            $this->db->where_in('expenses.campus_id',$this->input->post('campus_ids'));
            $this->db->group_by('expenses.campus_id,expenses.expense_category_id');
            $data['expenses']=$this->db->get()->result_array();

        }
        $data['from_date'] = $from_date;
        $data['to_date'] = $to_date;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('tax/all_expenses_report', $data);
        $this->load->view('inc/footer');
    }

	public function all_expenses_details($from_date,$to_date,$campus_id,$category_id,$paid_type,$date_type)
    {
        // $this->db->select('*');
        // $this->db->from('expenses');
        // $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'inner');
        // $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'inner');
        // $this->db->where(array('expenses.date>='=>$from_date, 'expenses.date<='=>$to_date));
        // $this->db->where(array('expenses.campus_id'=>$campus_id, 'expenses.expense_category_id'=>$category_id));
        // if($paid_type=='cash')
        // {
        //     $this->db->where('expenses.paid_type','cash');
        // }
        // elseif($paid_type=='bank')
        // {
        //     $this->db->where('expenses.paid_type','bank');
        // }
        // else
        // {

        // }
        // $data['expenses'] = $this->db->get()->result_array();


        if($paid_type=='cash')
        {
            $this->db->select('expenses.*,expense_category.*,campuses.*');
            $this->db->from('expenses');
            $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'inner');
            $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'inner');
            if($date_type=='actual_date')
            {
                $this->db->where(array('expenses.date>='=>$from_date, 'expenses.date<='=>$to_date));
            }
            else
            {
                $this->db->where(array('expenses.actual_date>='=>$from_date.' 00:00:00', 'expenses.actual_date<='=>$to_date.' 23:59:59'));
            }
            $this->db->where(array('expenses.campus_id'=>$campus_id, 'expenses.expense_category_id'=>$category_id));
            $this->db->where('expenses.paid_type','cash');
            $data['expenses'] = $this->db->get()->result_array();
        }
        elseif($paid_type=='bank')
        {
            $this->db->select('expenses.*,expense_category.*,campuses.*');
            $this->db->from('expenses');
            $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'inner');
            $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'inner');
            $this->db->join('bank_reconciliation_statement','bank_reconciliation_statement.expense_id=expenses.expense_id','INNER');
            if($date_type=='actual_date')
            {
                $this->db->where(array('bank_reconciliation_statement.trans_date>='=>$from_date, 'bank_reconciliation_statement.trans_date<='=>$to_date));
            }
            else
            {
                $this->db->where(array('expenses.actual_date>='=>$from_date.' 00:00:00', 'expenses.actual_date<='=>$to_date.' 23:59:59'));
            }
            $this->db->where(array('expenses.campus_id'=>$campus_id, 'expenses.expense_category_id'=>$category_id));
            $this->db->where('expenses.paid_type','bank');
            $data['expenses'] = $this->db->get()->result_array();
        }
        else
        {
            $this->db->select('expenses.*,expense_category.*,campuses.*');
            $this->db->from('expenses');
            $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'inner');
            $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'inner');
            if($date_type=='actual_date')
            {
                $this->db->where(array('expenses.date>='=>$from_date, 'expenses.date<='=>$to_date));
            }
            else
            {
                $this->db->where(array('expenses.actual_date>='=>$from_date.' 00:00:00', 'expenses.actual_date<='=>$to_date.' 23:59:59'));
            }
            $this->db->where(array('expenses.campus_id'=>$campus_id, 'expenses.expense_category_id'=>$category_id));
            $this->db->where('expenses.paid_type','cash');
            $array1 = $this->db->get()->result_array();

            $this->db->select('expenses.*,expense_category.*,campuses.*');
            $this->db->from('expenses');
            $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'inner');
            $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'inner');
            $this->db->join('bank_reconciliation_statement','bank_reconciliation_statement.expense_id=expenses.expense_id','INNER');
            if($date_type=='actual_date')
            {
                $this->db->where(array('bank_reconciliation_statement.trans_date>='=>$from_date, 'bank_reconciliation_statement.trans_date<='=>$to_date));
            }
            else
            {
                $this->db->where(array('expenses.actual_date>='=>$from_date.' 00:00:00', 'expenses.actual_date<='=>$to_date.' 23:59:59'));
            }
            $this->db->where(array('expenses.campus_id'=>$campus_id, 'expenses.expense_category_id'=>$category_id));
            $this->db->where('expenses.paid_type','bank');
            $array2 = $this->db->get()->result_array();

            $data['expenses'] = array_merge($array1,$array2);
        }


        $data['from_date'] = $from_date;
        $data['to_date'] = $to_date;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('tax/all_expenses_report_detail', $data);
        $this->load->view('inc/footer');
    }
	
	public function expense_report_headwise()
    {
        $access = checkUserAccess();
        $campus_ids = @explode(',',$access[0]['campus_ids']);
        $data['campuses'] = $this->db->where_in( array('campus_id'=>$campus_ids))->get('campuses')->result_array();
        $data['headCategories'] = $this->db->get_where('expense_category','sub_of IS NULL')->result_array();

        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');
        $date_type = $this->input->post('date_type');

        if( $from_date == '' ){

            $from_date = date('Y-m-d');
            $to_date = date('Y-m-d');
            $categories = array(0);
        }else{
            $categories = $this->input->post('categories');
        }


        // if( $from_date == '' ){

        //     $from_date = date('Y-m-d');
        //     $to_date = date('Y-m-d');
        // }else{
        //     $categories = $this->input->post('categories');
        //     $myexpenses = array();
        //     $data['expenses'] = array();

        //     if($categories=='')
        //     {
        //         $categories = $this->db->get('expense_category')->result_array();
        //         foreach($categories as $category)
        //         {
        //             $this->db->select('sum(expenses.amount) as total_amount,campuses.campus_id,campuses.campus_name,expense_category.name,expense_category.expense_category_id,paid_type');
        //             $this->db->from('expenses');
        //             $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
        //             $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
        //             $this->db->join('bank_reconciliation_statement','bank_reconciliation_statement.expense_id=expenses.expense_id','INNER');
        //             if($date_type=='actual_date')
        //             {
        //                 $this->db->where(array('bank_reconciliation_statement.trans_date>='=>$from_date, 'bank_reconciliation_statement.trans_date<='=>$to_date));
        //             }
        //             else
        //             {
        //                 $this->db->where(array('expenses.date>='=>$from_date, 'expenses.date<='=>$to_date));
        //             }
        //             $this->db->where('expenses.expense_category_id',$category['expense_category_id']);
        //             $this->db->where('expenses.paid_type','bank');
        //             $this->db->where_in('expenses.campus_id',$this->input->post('campus_ids'));
        //             $this->db->group_by('expenses.expense_category_id,expenses.paid_type');
        //             $bank_expenses = $this->db->get()->result_array();

        //             $this->db->select('sum(expenses.amount) as total_amount,campuses.campus_id,campuses.campus_name,expense_category.name,expense_category.expense_category_id,paid_type');
        //             $this->db->from('expenses');
        //             $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
        //             $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
        //             if($date_type=='actual_date')
        //             {
        //                 $this->db->where(array('expenses.date>='=>$from_date, 'expenses.date<='=>$to_date));
        //             }
        //             else
        //             {
        //                 $this->db->where(array('expenses.actual_date>='=>$from_date.' 00:00:00', 'expenses.actual_date<='=>$to_date.' 23:59:59'));
        //             }
        //             $this->db->where('expenses.expense_category_id',$category['expense_category_id']);
        //             $this->db->where('expenses.paid_type','cash');
        //             $this->db->where_in('expenses.campus_id',$this->input->post('campus_ids'));
        //             $this->db->group_by('expenses.expense_category_id,expenses.paid_type');
        //             $cash_expenses = $this->db->get()->result_array();

        //             $myexpenses['total_amount'] = @$bank_expenses[0]['total_amount']+@$cash_expenses[0]['total_amount'];

        //             if($myexpenses['total_amount']>0)
        //             {
        //                 if(@$bank_expenses[0]['campus_id']=='')
        //                 {
        //                     $myexpenses['campus_id'] = @$cash_expenses[0]['campus_id'];
        //                 }
        //                 else
        //                 {
        //                     $myexpenses['campus_id'] = @$bank_expenses[0]['campus_id'];
        //                 }
                        
        //                 if(@$bank_expenses[0]['campus_name']=='')
        //                 {
        //                     $myexpenses['campus_name'] = @$cash_expenses[0]['campus_name'];
        //                 }
        //                 else
        //                 {
        //                     $myexpenses['campus_name'] = @$bank_expenses[0]['campus_name'];
        //                 }

        //                 if(@$bank_expenses[0]['name']=='')
        //                 {
        //                     $myexpenses['name'] = @$cash_expenses[0]['name'];
        //                 }
        //                 else
        //                 {
        //                     $myexpenses['name'] = @$bank_expenses[0]['name'];
        //                 }

        //                 if(@$bank_expenses[0]['expense_category_id']=='')
        //                 {
        //                     $myexpenses['expense_category_id'] = @$cash_expenses[0]['expense_category_id'];
        //                 }
        //                 else
        //                 {
        //                     $myexpenses['expense_category_id'] = @$bank_expenses[0]['expense_category_id'];
        //                 }
        //                 //$myexpenses['expense_category_id'] = @$bank_expenses[0]['expense_category_id'];
        //                 $myexpenses['by_cash'] = @$cash_expenses[0]['total_amount'];
        //                 $myexpenses['by_bank'] = @$bank_expenses[0]['total_amount'];

        //                 array_push($data['expenses'],$myexpenses);
        //             }
        //         }
        //     }
        //     else
        //     {
        //         foreach($categories as $category)
        //         {
        //             $this->db->select('sum(expenses.amount) as total_amount,campuses.campus_id,campuses.campus_name,expense_category.name,expense_category.expense_category_id,paid_type');
        //             $this->db->from('expenses');
        //             $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
        //             $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
        //             $this->db->join('bank_reconciliation_statement','bank_reconciliation_statement.expense_id=expenses.expense_id','INNER');
        //             if($date_type=='actual_date')
        //             {
        //                 $this->db->where(array('bank_reconciliation_statement.trans_date>='=>$from_date, 'bank_reconciliation_statement.trans_date<='=>$to_date));
        //             }
        //             else
        //             {
        //                 $this->db->where(array('expenses.date>='=>$from_date, 'expenses.date<='=>$to_date));
        //             }
        //             $this->db->where('expenses.expense_category_id',$category);
        //             $this->db->where('expenses.paid_type','bank');
        //             $this->db->where_in('expenses.campus_id',$this->input->post('campus_ids'));
        //             $this->db->group_by('expenses.expense_category_id,expenses.paid_type');
        //             $bank_expenses = $this->db->get()->result_array();

        //             $this->db->select('sum(expenses.amount) as total_amount,campuses.campus_id,campuses.campus_name,expense_category.name,expense_category.expense_category_id,paid_type');
        //             $this->db->from('expenses');
        //             $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
        //             $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
        //             if($date_type=='actual_date')
        //             {
        //                 $this->db->where(array('expenses.date>='=>$from_date, 'expenses.date<='=>$to_date));
        //             }
        //             else
        //             {
        //                 $this->db->where(array('expenses.actual_date>='=>$from_date.' 00:00:00', 'expenses.actual_date<='=>$to_date.' 23:59:59'));
        //             }
        //             $this->db->where('expenses.expense_category_id',$category);
        //             $this->db->where('expenses.paid_type','cash');
        //             $this->db->where_in('expenses.campus_id',$this->input->post('campus_ids'));
        //             $this->db->group_by('expenses.expense_category_id,expenses.paid_type');
        //             $cash_expenses = $this->db->get()->result_array();

        //             $myexpenses['total_amount'] = @$bank_expenses[0]['total_amount']+@$cash_expenses[0]['total_amount'];
        //             if($myexpenses['total_amount']>0)
        //             {
        //                 if(@$bank_expenses[0]['campus_id']=='')
        //                 {
        //                     $myexpenses['campus_id'] = @$cash_expenses[0]['campus_id'];
        //                 }
        //                 else
        //                 {
        //                     $myexpenses['campus_id'] = @$bank_expenses[0]['campus_id'];
        //                 }
                        
        //                 if(@$bank_expenses[0]['campus_name']=='')
        //                 {
        //                     $myexpenses['campus_name'] = @$cash_expenses[0]['campus_name'];
        //                 }
        //                 else
        //                 {
        //                     $myexpenses['campus_name'] = @$bank_expenses[0]['campus_name'];
        //                 }

        //                 if(@$bank_expenses[0]['name']=='')
        //                 {
        //                     $myexpenses['name'] = @$cash_expenses[0]['name'];
        //                 }
        //                 else
        //                 {
        //                     $myexpenses['name'] = @$bank_expenses[0]['name'];
        //                 }

        //                 if(@$bank_expenses[0]['expense_category_id']=='')
        //                 {
        //                     $myexpenses['expense_category_id'] = @$cash_expenses[0]['expense_category_id'];
        //                 }
        //                 else
        //                 {
        //                     $myexpenses['expense_category_id'] = @$bank_expenses[0]['expense_category_id'];
        //                 }
        //                 //$myexpenses['expense_category_id'] = @$bank_expenses[0]['expense_category_id'];
        //                 $myexpenses['by_cash'] = @$cash_expenses[0]['total_amount'];
        //                 $myexpenses['by_bank'] = @$bank_expenses[0]['total_amount'];

        //                 array_push($data['expenses'],$myexpenses);
        //             }
        //         }
        //     }
        // }
        $data['from_date'] = $from_date;
        $data['to_date'] = $to_date;

        if($date_type=='')
        {
            $data['date_type'] = 'actual_date';
        }
        else
        {
            $data['date_type'] = $date_type;
        }

        if(@$this->input->post('campus_ids')=='')
        {
            $this->db->select('*');
            $allCampuses = $this->db->get('campuses')->result_array();

            $selected_campus_ids = array();
            foreach($allCampuses as $allCampus)
            {
                array_push($selected_campus_ids,$allCampus['campus_id']);
            }

            $selected_campus_ids = implode(',',$selected_campus_ids);
            $data['campus_ids'] = $selected_campus_ids;
        }
        else
        {
            $selected_campus_ids = implode(',',$this->input->post('campus_ids'));
            $data['campus_ids'] = $selected_campus_ids;
        }



        $campus = $this->input->post('campus_id');
        $this->db->order_by('name', 'ASC');
        if ($campus)
            $data['categories'] = $this->db->where("find_in_set($campus, for_campus)")->get_where('expense_category',"sub_of is NULL")->result_array();
        else
            $this->db->where_in('expense_category_id',@$categories);
            $data['categories'] = $this->db->get_where('expense_category',"sub_of is NULL")->result_array();
        $data['campuses'] = $this->db->get_where('campuses',"status = 1")->result_array();
        $data['my_campus'] = $campus;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('tax/all_expenses_report_headwise', $data);
        $this->load->view('inc/footer');
    }

	public function all_expenses_details_headwise($from_date,$to_date,$category_id,$campus_ids,$date_type)
    {
        $this->db->select('sum(expenses.amount) as total_amount,campuses.campus_id,campuses.campus_name,expense_category.name,expense_category.expense_category_id,expenses.payment_type');
        $this->db->from('expenses');
        $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'inner');
        $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'inner');
        $this->db->where(array('expenses.date>='=>$from_date, 'expenses.date<='=>$to_date));
        $this->db->where(array('expenses.expense_category_id'=>$category_id));
		$this->db->where_in('expenses.campus_id',explode(',',$campus_ids));
		$this->db->group_by('expenses.campus_id,expenses.expense_category_id');
        $data['expenses'] = $this->db->get()->result_array();

        $data['from_date'] = $from_date;
        $data['to_date'] = $to_date;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('tax/all_expenses_report_detail_headwise', $data);
        $this->load->view('inc/footer');
    }

	public function tax_paid()
	{
		$data['taxes']=$this->db->get('tax_paid')->result_array();
		$this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('tax/tax_paid',$data);
        $this->load->view('inc/footer');
	}

	public function insert_tax_paid()
	{
		$type = $this->input->post('type');
		$tax_year = $this->input->post('tax_year');

		//CHECK THIS YEAR TAX ALREADY ADDED
		$check = $this->db->get_where('tax_paid',array('type'=>$type,'tax_year'=>$tax_year))->result_array();

		if(count($check)>0)
		{
			$this->session->set_flashdata('error','This Year Tax Already Uploaded.');
			redirect('tax/tax_paid');
		}
		else
		{
			$config['upload_path'] = 'tax_documents/';

			// set the filter image types
			$config['allowed_types'] = '*';

			//load the upload library
			$this->load->library('upload', $config);

			$this->upload->initialize($config);

			$this->upload->set_allowed_types('*');

			$data['upload_data'] = '';

			//if not successful, set the error message
			if (!$this->upload->do_upload('tax_document')) {
				$data = array('msg' => $this->upload->display_errors());
				$tax_document = '';

			} else { //else, set the success message
				$data['upload_data'] = $this->upload->data();
				if($data['upload_data']['file_name']){
					$tax_document = $data['upload_data']['file_name'];
				}
			}

			$this->db->set('type',$type);
			$this->db->set('tax_year',$tax_year);
			$this->db->set('tax_document',$tax_document);
			$this->db->insert('tax_paid');
			$tax_paid_id = $this->db->insert_id();

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL,"https://www.shahbazcollegeofpharmacy.edu.pk/s3/upload_tax_document.php");
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,
						"tax_paid_id=".$tax_paid_id);

			// In real life you should use something like:
			// curl_setopt($ch, CURLOPT_POSTFIELDS, 
			//          http_build_query(array('postvar1' => 'value1')));

			// Receive server response ...
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$server_output = curl_exec($ch);

			curl_close($ch);
			

			$this->session->set_flashdata('message','Tax Document Uploaded Successfully.');
			redirect('tax/tax_paid');
		}

	}

	public function delete_tax($tax_paid_id)
	{
		$this->db->where('tax_paid_id',$tax_paid_id);
		$this->db->delete('tax_paid');
		
		$this->session->set_flashdata('message','Tax Document Deleted Successfully.');
		redirect('tax/tax_paid');
	}

    public function update_bank_expenses()
    {
        // $this->db->select('expenses.*');
        // $this->db->from('expenses');
        // $this->db->join('bank_reconciliation_statement','expenses.expense_id=bank_reconciliation_statement.expense_id','inner');
        // $this->db->where('expenses.paid_type','cash');
        // $expenses = $this->db->get()->result_array();
        // echo '<pre>';
        //     print_r($expenses);
        //     echo '</pre>';

        // foreach($expenses as $expense)
        // {
        //     $this->db->set('paid_type','bank');
        //     $this->db->where('expense_id',$expense['expense_id']);
        //     $this->db->update('expenses');
        // }
    }

    public function getCats($expense_category_id)
    {
        //$cats = getExpenseCategories($expense_category_id);

        echo '<pre>';
        print_r(getExpenseCategories($expense_category_id));
        echo '</pre>';
    }
}