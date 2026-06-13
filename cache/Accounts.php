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
            $data['till_date'] = date('Y-m-d');
        }
        $data['campuses'] = $this->account->getCampus($campus_id);
        $data['profits'] = $this->account->getProfitDone($campus_id);

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('accounts/campus_profit', $data);
        $this->load->view('inc/footer');
    }

    public function shift_fee_recovery($campus_id)
    {
        $arr = array();
        $datax = $this->db->select("*")->get_where('student_shift_details', array('from_class'=>$campus_id,'status'=>"0"))->result_array();

        foreach ($datax as $entry)
        {
            $arr = array_merge($arr,json_decode($entry['fee_ids']));
        }

        $this->db->select('payments.*, classes.name as class_name, campuses.campus_name, students.first_name, students.last_name, students.roll_no');
        $this->db->from('payments');

        $this->db->join('students', 'students.student_id=payments.student_id', 'inner');
        $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
        $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');

        $this->db->where_in('payments.challan_no',$arr);
        $query = $this->db->get()->result_array();

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

        if($to_date>=date('Y-m-d'))
        {
            $this->session->set_flashdata('error', 'Date selection error!');
            redirect(site_url().'/accounts/campus_profit/'.$campus_id);
        }
        else
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
                $this->db->insert('profit_distribution');
            }

            $this->db->set('campus_id', $campus_id);
            $this->db->set('date', $to_date);
            $this->db->insert('profit_distribution_date');

            $id = $this->db->insert_id();
            $fees = $this->account->getPayments($from_date, $to_date, $campus_id);
            $contractorsfees = $this->account->getPaymentsContractors($from_date, $to_date, $campus_id);

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
        }
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
        $data['accounts'] = $this->db->get()->result_array();

        $this->db->select('*');
        $this->db->from('petty_cash_college_wise');
        $this->db->join('campuses','campuses.campus_id = petty_cash_college_wise.campus_id','left');
        $this->db->join('users','users.user_id = petty_cash_college_wise.assign_to','left');
        $this->db->join('designations','designations.designation_id = users.designation_id','left');
        $this->db->where('petty_cash_college_wise.petty_status = 1');
        $data['Pettycashs'] = $this->db->get()->result_array();


        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('accounts/accounts_details', $data);
        $this->load->view('inc/footer');

    }

    public function add_account()
    {

        $accounttitle = $this->input->post('title');
        $accountno = $this->input->post('accountno');
        $accountamount = $this->input->post('amount');
        $accountlimit = $this->input->post('amount_limit');
        $bank = $this->input->post('bank');

        $title = $bank.' ('.$accountno.')';

        $this->db->set('account_name',$title);
        $this->db->set('account_title',$accounttitle);
        $this->db->set('amount',$accountamount);
        $this->db->set('type','1');
        $this->db->set('account_limit',$accountlimit);
        $this->db->insert('accounts');

        redirect('accounts/account_details');

    }

    public function edit()
    {

        $accounttitle = $this->input->post('title');
        $accountno = $this->input->post('accountno');
        $accountamount = $this->input->post('amount');
        $accountlimit = $this->input->post('amount_limit');
        $bank = $this->input->post('bank');

        $title = $bank.' ('.$accountno.')';

        $this->db->set('account_name',$title);
        $this->db->set('account_title',$accounttitle);
        $this->db->set('account_limit',$accountlimit);
        $this->db->where('id',$this->input->post('daccount_id'));
        $this->db->update('accounts');

        redirect('accounts/account_details');

    }

    public function transfer_funds()
    {

        $petty_account = $this->input->post('petty_account');
        $from = $this->input->post('from_account');
        $to = $this->input->post('to_account');
        $pettyid = $this->input->post('petty_account_id');
        $accountamount = $this->input->post('sentamount');
        $trasfer_reason = $this->input->post('trasfer_reason');

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


        if(@$this->input->post('from_date'))
        {
            $data['from_date'] = $this->input->post('from_date');
        }

        else
        {
            $data['from_date'] = date('Y/m/1');
        }

        if(@$this->input->post('to_date'))
        {
            $data['to_date'] = $this->input->post('to_date');
        }

        else
        {
            $data['to_date'] = date('Y/m/30');
        }


        $this->db->select('*');
        $this->db->from('transactions_history');
        $this->db->where('transaction_account_id = "'.$account_id.'" and created_at < "'.$data['from_date'].' 00:00:00" ');
        $trans_petty_cash = $this->db->get()->result_array();

        $debit=0;
        $credit=0;

        foreach ($trans_petty_cash as $tran)
        {
            if ($tran['debit_credit']  == 'C' )
            {
                $credit+=$tran['amount'];
            }
            else
            {
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
            $this->db->group_by("bank_reconciliation_statement.description,bank_reconciliation_statement.reference_no");

            $data['entries']=$this->db->get()->result_array();
        }
        else  {
            $data['entries']=array();
        }

        $this->db->select('*');
        $this->db->from('campuses');
        $data['allcampuses'] = $this->db->get()->result_array();


        $data['categories'] = $this->expense->getCategories();

        $data['accounts'] = $this->db->query('SELECT * FROM `accounts` WHERE `type` = "1"')->result_array();
        $data['cash_accounts'] = $this->db->query('SELECT * FROM `accounts` WHERE `type` = "0"')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('accounts/bank_statement',$data);
        $this->load->view('inc/footer');
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
        else
        {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $file = $data['upload_data']['file_name'];
            }
        }

        if($file=='')
        {
            $this->session->set_flashdata('error', 'Problem in file uploading');
            redirect('accounts/uploadstatement');
        }
        else
        {

            $maxid = 0;
            $rowx = $this->db->query('SELECT MAX(statement_no) AS `maxid` FROM `bank_reconciliation_statement`')->row();
            if ($rowx) {

                $maxid = $rowx->maxid;
                if($maxid == '')
                {
                    $maxid = 0;
                }
            }else{
                $maxid = 0;
            }



            $check_record = $this->db->get_where('payments', array('paid'=>'1', 'fee_pay_through'=>'bank','tid_no !='=>''))->result_array();

            try{
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

                            $this->db->set('statement_no', $maxid);
                            $this->db->set('account_id', $this->input->post('account_id'));
                            $this->db->set('trans_date', $newDate);
                            $this->db->set('description', $index[2]);
                            $this->db->set('debit', $index[3]);
                            $this->db->set('credit', $index[4]);
                            $this->db->set('balance', $index[5]);


                            if ($index[2] == '' || $index[2] == NULL) {
                                break;
                            }

                            $this->db->insert('bank_reconciliation_statement');
                        }

                    }

                    elseif ($this->input->post('account_id') == '5' || $this->input->post('account_id') == '10' || $this->input->post('account_id') == '12')
                    {

                        if ($row==4 && $index[0]!='Date')
                        {
                            fclose($file);
                            $this->session->set_flashdata('error', 'Wrong Csv Format.');
                            redirect('accounts/uploadstatement');

                        }

                        if ($row > 4) {

                            if ($index[0] == '' || $index[0] == NULL) {
                                continue;
                            }

                            $newDate = date("Y-m-d", strtotime($index[0]));

                            $this->db->set('statement_no', $maxid);
                            $this->db->set('account_id', $this->input->post('account_id'));
                            $this->db->set('trans_date', $newDate);
                            $this->db->set('description', $index[1]);
                            $this->db->set('reference_no', $index[2]);
                            if ($index[5] == 'Dr')
                            {
                                $this->db->set('debit', $index[4]);
                                $this->db->set('credit', '');

                            }else
                            {
                                $this->db->set('debit','');
                                $this->db->set('credit',  $index[4]);

                            }


                            $this->db->set('balance', $index[7]);




                            $this->db->insert('bank_reconciliation_statement');
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

                            $this->db->set('statement_no', $maxid);
                            $this->db->set('account_id', $this->input->post('account_id'));
                            $this->db->set('trans_date', $newDate);
                            $this->db->set('description', $index[2].' '.$index[3]);
                            $this->db->set('reference_no', $index[1]);

                            $this->db->set('debit', $index[4]);
                            $this->db->set('credit',  $index[5]);

                            $this->db->set('balance', $index[6]);

                            $this->db->insert('bank_reconciliation_statement');
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


                            $newDate = date("Y-m-d", strtotime($index[0]));;

                            $this->db->set('statement_no', $maxid);
                            $this->db->set('account_id', $this->input->post('account_id'));
                            $this->db->set('trans_date', $newDate);
                            $this->db->set('description', $index[1]);
                            $this->db->set('reference_no', '');

                            $this->db->set('debit', $index[2]);
                            $this->db->set('credit',  $index[3]);

                            $this->db->set('balance', $index[4]);

                            $this->db->insert('bank_reconciliation_statement');
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

                            $this->db->set('statement_no', $maxid);
                            $this->db->set('account_id', $this->input->post('account_id'));
                            $this->db->set('trans_date', $newDate);
                            $this->db->set('description', $index[1]);
                            $this->db->set('reference_no', $index[2]);

                            $this->db->set('debit', $index[5]);
                            $this->db->set('credit',  $index[4]);

                            $this->db->set('balance', '');

                            $this->db->insert('bank_reconciliation_statement');
                        }

                    }

                    $row++;
                }
            }
            catch(Exception $exception)
            {
                fclose($file);
                $this->session->set_flashdata('message', 'CSV uploaded successfully.');
                redirect('accounts/uploadstatement');
                //                        foreach($check_record as $record){
//
//                            if(strpos($index[2],$record['tid_no']) !== false && $record['tid_no'] != ',' && $record['tid_no'] != ' ' && $record['tid_no'] != '.')
//                            {
//                                $this->db->set('payment_id', $record['id']);
//
//
//                            }
//
//
//
//                        }

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
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $image = $data['upload_data']['file_name'];
            }
        }

        $this->db->set('campus_id',$this->input->post('ac_campus_id'));
        $this->db->set('expense_category_id',$this->input->post('expense_category_id'));
        $this->db->set('title',$this->input->post('title'));
        $this->db->set('date',date('Y-m-d'));
        $this->db->set('amount',$this->input->post('amount'));
        $this->db->set('purpose',$this->input->post('reason_disc'));

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


        redirect(site_url().'/accounts/uploadstatement');

    }

    public function find_transactions()
    {

        $bank_trans_id = $this->input->post('bank_trans_id');
        $bank_id = $this->input->post('bank_id');

        $transaction=$this->db->get_where('bank_reconciliation_statement','id = '.$bank_trans_id)->row();

        $this->db->select('*,bank_reconciliation_statement.id as tidx');
        $this->db->from('bank_reconciliation_statement');
        $this->db->join('payments','payments.statement_id = bank_reconciliation_statement.id','left');
        $this->db->where("bank_reconciliation_statement.trans_date ='".$transaction->trans_date."' and account_id = '".$bank_id."' and credit = '".$transaction->debit."'");
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

    public function tag_bank_trans()
    {

        $tag_id=$this->input->post('tag_id');
        $trans_id=$this->input->post('bank_trans_id');

        $this->db->set('bank_transfer_id',$tag_id);
        $this->db->where('id',$trans_id);
        $this->db->update('bank_reconciliation_statement');

        $this->db->set('bank_transfer_id',$trans_id);
        $this->db->where('id',$tag_id);
        $this->db->update('bank_reconciliation_statement');

        redirect(site_url().'/accounts/uploadstatement');

    }

    public function tag_paypro_trans()
    {
        $tag_id=$this->input->post('tag_id');
        $trans_id=$this->input->post('bank_trans_id');

        $this->db->set('paypro_id',$tag_id);
        $this->db->where('id',$trans_id);
        $this->db->update('bank_reconciliation_statement');

        redirect(site_url().'/accounts/uploadstatement');
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
            //else, set the success message
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


        $this->db->set('statement_id', $this->db->insert_id());
        $this->db->where('id', $transaction->id);
        $this->db->update('bank_reconciliation_statement');


        redirect(site_url().'/accounts/uploadstatement');

    }

    public function add_council_fee()
    {

        $trans_id=$this->input->post('trans_id');

        $this->db->set('is_council_fee',"1");
        $this->db->where('id',$trans_id);
        $this->db->update('bank_reconciliation_statement');

        redirect(site_url().'/accounts/uploadstatement');

    }

    public function find_paypro_transactions()
    {
        $bank_trans_id = $this->input->post('bank_trans_id');
        $transaction=$this->db->get_where('bank_reconciliation_statement','id = '.$bank_trans_id)->row();

        $amount = (int)str_replace(",","",$transaction->credit);

        $this->db->select('*');
        $this->db->from('pay_pro_settlement');
        $this->db->where("pay_pro_settlement.settlement_date ='".$transaction->trans_date."' and (link_amount = '".$amount."' or card_amount = '".$amount."')");
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
        $this->db->where("paid_type = 'bank' and date>= '$date' and date<= '$enddate'");
        $entries = $this->db->get()->result_array();

        $html = '';
        $i=0;
        foreach($entries as $closing_rule):
            $f=$i+1;
            $html.=" <tr>
                <td>
                    $f
                     <input class='form-check-input' type='checkbox' name='tag_ids[]'  value='{$closing_rule['expense_id']}' required>
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
}
