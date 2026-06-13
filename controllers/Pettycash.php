<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Pettycash extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		require_once("vendor/autoload.php");
	}
	
	public function index($petystatus = '1')
    {

		$myAccess = checkUserAccess();

        $this->db->select('*');
        $this->db->from('campuses');
        $data['campuses'] = $this->db->get()->result_array();

        $this->db->select('*');
        $this->db->from('accounts');
        $this->db->where('type = "0"');
        $data['accounts'] = $this->db->get()->result_array();
		
        $this->db->select('*');
        $this->db->from('petty_cash_college_wise');
        $this->db->join('campuses','campuses.campus_id = petty_cash_college_wise.campus_id','left');
        $this->db->join('users','users.user_id = petty_cash_college_wise.assign_to','left');
        $this->db->join('designations','designations.designation_id = users.designation_id','left');
        $this->db->where ('petty_cash_college_wise.petty_status',$petystatus);
		
		if($this->session->userdata('role')!='Admin'){
			$this->db->where_in('petty_cash_college_wise.id',explode(',',$myAccess[0]['petty_cash_users']));
		}
        $data['Pettycashs'] = $this->db->get()->result_array();
		foreach ($data['Pettycashs'] as $index=>$petts){
            $data['Pettycashs'][$index]['remaining_amount'] = pettycash_statement($petts ['id']);
        }
		
        $this->db->select('count(*) as active');
        $this->db->from('petty_cash_college_wise');
        $this->db->where ('petty_cash_college_wise.petty_status','1');
		if($this->session->userdata('role')!='Admin'){
			$this->db->where_in('petty_cash_college_wise.id',explode(',',$myAccess[0]['petty_cash_users']));
		}
        $data['active'] = $this->db->get()->result_array();
        $data['active'] = $data['active'][0]['active'];

        $this->db->select('count(*) as inactive');
        $this->db->from('petty_cash_college_wise');
        $this->db->where ('petty_cash_college_wise.petty_status','0');
		if($this->session->userdata('role')!='Admin'){
			$this->db->where_in('petty_cash_college_wise.id',explode(',',$myAccess[0]['petty_cash_users']));
		}
        $data['inactive'] = $this->db->get()->result_array();
        $data['inactive'] = $data['inactive'][0]['inactive'];


        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('petty_cash/add_pettycash', $data);
        $this->load->view('inc/footer');

    }

	public function add()
	{	


		$recovery_from = $this->input->post('recovery_from');
		$campus_id = $this->input->post('campus_id');
		$amount = $this->input->post('amount');
		$user = $this->input->post('user_id');
		$created_by = $this->session->userdata('user_id');
		$opening_balance = $this->input->post('opening_balance');

		$this->db->set('campus_id',$campus_id);
		$this->db->set('recovery_from',$recovery_from);
		$this->db->set('assign_to',$user);
		$this->db->set('amount',$amount);
		$this->db->set('remaining_amount',$opening_balance);
		$this->db->set('opening_balance',$opening_balance);
		$this->db->set('petty_status','1');
		$this->db->set('created_by',$created_by);
		$this->db->set('created_at',date('Y-m-d H:i:s'));
		$this->db->set('given_date',date('Y-m-d').' 00:00:00');
		$this->db->insert('petty_cash_college_wise');

		
		$this->session->set_flashdata('message', 'Petty Cash Added successfully');
		
		redirect(site_url().'/pettycash/index');
	}
	
	public function add_transaction()
    {

        $reason = $this->input->post('reason');
        $campus_id = $this->input->post('campus_id');
        $trans_type = $this->input->post('trans_type');
        $amount = $this->input->post('amount');
        $user = $this->input->post('user_id');
        $trans_by = $this->session->userdata('name');
        $fromaccount = $this->input->post('account_id');

        if ($trans_type == 'D')
		{

            $this->db->set('remaining_amount', 'remaining_amount +'. $amount .'',false);
            $this->db->set('status', '1');
            $this->db->where('assign_to', $user);
            $this->db->update('petty_cash_college_wise');
			
			$this->db->set('amount', 'amount -'. $amount .'',false);
			$this->db->where('id', $fromaccount);
			$this->db->update('accounts');
			
			$pettyid = $this->db->get_where('petty_cash_college_wise', 'assign_to ='.$user)->result_array();
			$pettyid = $pettyid[0]['id'];
			
			
			$this->db->set('petty_cash_id',$pettyid);
			$this->db->set('from_account_id',$fromaccount);
			$this->db->set('debit_credit','C');
			$this->db->set('amount',$amount);
			$this->db->set('reason','Petty Cash Entry '.$reason);
            $this->db->set('campus_id',$campus_id);
            $this->db->insert('transactions_history');			
			
        }

        else
		{

            $this->db->set('amount', 'amount -'. $amount .'',false);
            $this->db->where('assign_to', $user);
            $this->db->update('petty_cash_college_wise');
			
			$this->db->set('status', '0');
			$this->db->set('campus_id',$campus_id);
			$this->db->set('user_id',$user);
			$this->db->set('amount_given',$amount);
			$this->db->set('debit_credit',$trans_type);
			$this->db->set('transaction_by',$trans_by);
			$this->db->insert('petty_cash_history');

        }


        $this->session->set_flashdata('message', 'Transaction Successfull');

        redirect(site_url().'/pettycash/index');

    }

	public function update_cash()
    {

        $campus_id = $this->input->post('campus_id');
        $amount = $this->input->post('amount');
        $user = $this->input->post('user_id');

        $this->db->set('amount', $amount );
        $this->db->where('assign_to', $user);
        $this->db->update('petty_cash_college_wise');


        $this->session->set_flashdata('message', 'Transaction Successfull');

        redirect(site_url().'/pettycash/index');

    }
	
    public function pettycash_statement($pettycashid)
    {

		$check_record = $this->db->get_where('petty_cash_college_wise', array('id'=>$pettycashid))->row();
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
            $data['to_date'] = date('Y/m/d');
        }


        
        $data['check_record'] = $check_record;
        $data['openbalance']=$check_record->opening_balance;



        $this->db->select('sum(amount) as amount');
        $this->db->from('expenses');
        $this->db->where('add_by_id = "'.$check_record->assign_to.'"  and actual_date >= "'.$check_record->given_date.'"  and actual_date < "'.$data['from_date'].'" and paid_type = "cash" and expense_id NOT IN (select expense_id from bank_reconciliation_statement where expense_id IS NOT NULL)');
        $expenseamount = $this->db->get()->row();

        $this->db->select('sum(cash_reversal.amount) as amount');
        $this->db->from('cash_reversal');
        $this->db->join('expenses','expenses.expense_id = cash_reversal.expense_id');
        $this->db->where('expenses.add_by_id = "'.$check_record->assign_to.'"  and cash_reversal.created_at < "'.$data['from_date'].' 00:00:00"');
        $expensereverseamount = $this->db->get()->row();

        $this->db->select('id as trans_id,"receive from" as detail,"trans" as trans_type,amount_given as amount,"" as expstatus, debit_credit,created_at,"" as image,transaction_by as trans_by ');
        $this->db->from('petty_cash_history');
        $this->db->where('transaction_pettycash_account = "'.$check_record->id.'" and created_at < "'.$data['from_date'].'" ');
        $trans_petty_cash = $this->db->get()->result_array();

        $debit=0;
        $credit=0;

        foreach ($trans_petty_cash as $tran)
        {
            if ($tran['debit_credit']  == 'C' )  {
                $credit+=$tran['amount'];
            }
            else    {
                $debit+=$tran['amount'];
            }
        }

        $data['openbalance'] = ($data['openbalance']+$debit+$expensereverseamount->amount)-$credit-$expenseamount->amount;

        $this->db->select('expenses.expense_id as trans_id,expenses.user_id as user_id,concat(expense_category.name," - ",expenses.title," - ",expenses.purpose," - ",campuses.campus_name," - ",expenses.date) as detail,"exp" as trans_type,expenses.amount as amount,"C" as debit_credit,expenses.approved_status as expstatus,expenses.actual_date as created_at,"" as reason,expenses.image,expenses.add_by as trans_by');
        $this->db->from('expenses');
        $this->db->join('expense_category','expense_category.expense_category_id  = expenses.expense_category_id','left');
        $this->db->join('users','users.user_id = expenses.add_by_id','left');
        $this->db->join('campuses','campuses.campus_id = expenses.campus_id','left');
        $this->db->where('expenses.add_by_id = "'.$check_record->assign_to.'"  and expenses.actual_date >= "'.$data['from_date'].' 00:00:00"    and expenses.actual_date <= "'.$data['to_date'].' 23:59:59" and paid_type = "cash"   and expense_id NOT IN (select expense_id from bank_reconciliation_statement where expense_id IS NOT NULL)');
        $expenses = $this->db->get()->result_array();

        $this->db->select('id as trans_id,expenses.user_id as user_id,concat("Reversal against ",expense_category.name," - ",expenses.title," - ",expenses.purpose," - ",campuses.campus_name," - ",expenses.date) as detail,"exp" as trans_type,cash_reversal.amount as amount,"D" as debit_credit,"Reversal" as expstatus,cash_reversal.created_at as created_at,"Reversal against Expense" as reason,expenses.image,expenses.add_by as trans_by');
        $this->db->from('cash_reversal');
        $this->db->join('expenses','expenses.expense_id = cash_reversal.expense_id','left');
        $this->db->join('expense_category','expense_category.expense_category_id  = expenses.expense_category_id','left');
        $this->db->join('users','users.user_id = expenses.add_by_id','left');
        $this->db->join('campuses','campuses.campus_id = expenses.campus_id','left');
        $this->db->where('expenses.add_by_id = "'.$check_record->assign_to.'" and cash_reversal.created_at >="'.date("Y-m-d",strtotime($data['from_date'])).' 00:00:00" and cash_reversal.created_at <="'.date("Y-m-d",strtotime($data['to_date'])).' 23:59:59"');
//        and cash_reversal.created_at >= "'.$check_record->given_date.'"
        $expensereverseamount = $this->db->get()->result_array();


        $this->db->select('id as trans_id,"receive from" as detail,"0" as user_id,"trans" as trans_type,amount_given as amount,"" as expstatus, debit_credit,created_at,proof_image as image,reason,transaction_by as trans_by ');
        $this->db->from('petty_cash_history');
        $this->db->where('transaction_pettycash_account = "'.$check_record->id.'"  and created_at >= "'.$data['from_date'].' 00:00:00" and  created_at <= "'.$data['to_date'].'  23:59:59"');
        $trans_petty_cash = $this->db->get()->result_array();

        $data['Pettycashs']=array_merge($expenses,$trans_petty_cash);
        $data['Pettycashs']=array_merge($data['Pettycashs'],$expensereverseamount);

		foreach($data['Pettycashs'] as $key=>$petty)
		{
			
			if($petty['user_id'] != '0' && $petty['user_id'] != NULL )
			{
				$userdata=$this->db->get_where('users','user_id = "'.$petty['user_id'].'"')->row();
				$data['Pettycashs'][$key]['detail'] = $petty['detail'].' '.$userdata->first_name.' '.$userdata->last_name;
			}
		}

		array_multisort(array_column($data['Pettycashs'], 'created_at'),  SORT_ASC,
                $data['Pettycashs']);

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('petty_cash/pettycash_statement', $data);
        $this->load->view('inc/footer');

    }

    public function funds_transfer()
    {

        $to_account = $this->input->post('petty_account_id');
        $to_cash_account = $this->input->post('account_id');

        $amount = $this->input->post('amount_transfer');
        $trans_by = $this->session->userdata('name');
        $fromaccount = $this->input->post('from_account_funds');
        $trasfer_reason = $this->input->post('trasfer_reason');
		
		
		//load the helper
		$this->load->helper('form');

		//Configure
		//set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
		$config['upload_path'] = 'uploads/';
		
    	// set the filter image types
		$config['allowed_types'] = 'gif|jpg|png|pdf';
		
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

        if ($to_account == ''){


            $this->db->set('remaining_amount', 'remaining_amount -' . $amount . '', false);
            $this->db->where('id', $fromaccount);
            $this->db->update('petty_cash_college_wise');


            $this->db->set('amount', 'amount +'. $amount .'',false);
            $this->db->where('id', $to_cash_account);
            $this->db->update('accounts');


            $this->db->set('from_pettycash_id',$fromaccount);
            $this->db->set('to_account_id',$to_cash_account);
            $this->db->set('transaction_account_id',$to_cash_account);
            $this->db->set('amount',$amount);
            $this->db->set('debit_credit','D');
            $this->db->set('transaction_by',$this->session->userdata('name'));
            $this->db->set('reason',$trasfer_reason);
			$this->db->set('proof_image',$image);
            $this->db->set('created_at',date('Y-m-d H:i:s'));
            $this->db->insert('transactions_history');


            $this->db->set('debit_credit', 'C');
            $this->db->set('amount_given', $amount);
			$this->db->set('from_pettycash_id', $fromaccount);
            $this->db->set('to_account', $to_cash_account);
            $this->db->set('transaction_pettycash_account', $fromaccount);
            $this->db->set('status', '1');
			$this->db->set('reason',$trasfer_reason);
			$this->db->set('proof_image',$image);
            $this->db->set('transaction_by', $trans_by);
			$this->db->set('created_at',date('Y-m-d H:i:s'));
            $this->db->insert('petty_cash_history');


        }
        
        else {

            $this->db->set('remaining_amount', 'remaining_amount +' . $amount . '', false);
            $this->db->where('id', $to_account);
            $this->db->update('petty_cash_college_wise');

            $this->db->set('remaining_amount', 'remaining_amount -' . $amount . '', false);
            $this->db->where('id', $fromaccount);
            $this->db->update('petty_cash_college_wise');



            $this->db->set('debit_credit', 'D');
            $this->db->set('amount_given', $amount);
            $this->db->set('from_pettycash_id', $fromaccount);
            $this->db->set('to_pettycash_id', $to_account);
            $this->db->set('transaction_pettycash_account', $to_account);
            $this->db->set('status', '1');
            $this->db->set('transaction_by', $trans_by);
			$this->db->set('created_at',date('Y-m-d H:i:s'));
			$this->db->set('reason',$trasfer_reason);
			$this->db->set('proof_image',$image);
            $this->db->insert('petty_cash_history');
            

            $this->db->set('debit_credit', 'C');
            $this->db->set('amount_given', $amount);
            $this->db->set('from_pettycash_id', $fromaccount);
            $this->db->set('to_pettycash_id', $to_account);
            $this->db->set('transaction_pettycash_account', $fromaccount);
            $this->db->set('status', '1');
			$this->db->set('reason',$trasfer_reason);
			$this->db->set('proof_image',$image);
            $this->db->set('transaction_by', $trans_by);
			$this->db->set('created_at',date('Y-m-d H:i:s'));
            $this->db->insert('petty_cash_history');


        }

        $this->session->set_flashdata('message', 'Transaction Successfull');

        redirect(site_url().'/pettycash/index');

    }

	public function account_active($status,$id)
    {


        $this->db->set('petty_status', $status );
        $this->db->where('id', $id);
        $this->db->update('petty_cash_college_wise');


        $this->session->set_flashdata('message', 'Transaction Successfull');

        redirect(site_url().'/pettycash/index');

    }

}