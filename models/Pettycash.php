<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Pettycash extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
	}
	
	public function index()
	{

        $this->db->select('*');
        $this->db->from('campuses');
        $data['campuses'] = $this->db->get()->result_array();

        $this->db->select('*');
        $this->db->from('petty_cash_college_wise');
        $this->db->join('campuses','campuses.campus_id = petty_cash_college_wise.campus_id','left');
        $this->db->join('users','users.user_id = petty_cash_college_wise.assign_to','left');
        $this->db->join('designations','designations.designation_id = users.designation_id','left');
        $data['Pettycashs'] = $this->db->get()->result_array();


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

		$this->db->set('campus_id',$campus_id);
		$this->db->set('recovery_from',$recovery_from);
		$this->db->set('assign_to',$user);
		$this->db->set('amount',$amount);
		$this->db->set('remaining_amount',$amount);
		$this->db->set('created_by',$created_by);
		$this->db->insert('petty_cash_college_wise');

		
		$this->session->set_flashdata('message', 'Petty Cash Added successfully');
		
		redirect(site_url().'/pettycash/index');
	}
	
	public function add_transaction()
    {

        $campus_id = $this->input->post('campus_id');
        $trans_type = $this->input->post('trans_type');
        $amount = $this->input->post('amount');
        $user = $this->input->post('user_id');
        $trans_by = $this->session->userdata('name');

        if ($trans_type == 'D')
		{

            $this->db->set('remaining_amount', 'remaining_amount +'. $amount .'',false);
            $this->db->set('status', '1');
            $this->db->where('assign_to', $user);
            $this->db->update('petty_cash_college_wise');
			
			$this->db->set('amount', 'amount -'. $amount .'',false);
			$this->db->where('id', '1');
			$this->db->update('accounts');
			
			$pettyid = $this->db->get_where('petty_cash_college_wise', 'assign_to ='.$user)->result_array();
			$pettyid = $pettyid[0]['id'];
			
			
			$this->db->set('petty_cash_id',$pettyid);
			$this->db->set('from_account_id','1');
			$this->db->set('debit_credit','C');
			$this->db->set('amount',$amount);
			$this->db->set('reason','Petty Cash Entry');
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
		$data['openbalance']=$check_record->opening_balance;
	
        $this->db->select('concat(expense_category.name," ",expenses.title) as detail,expenses.amount as amount,"C" as debit_credit,expenses.actual_date as created_at,expenses.image');
        $this->db->from('expenses');
        $this->db->join('expense_category','expense_category.expense_category_id  = expenses.expense_category_id','left');
        $this->db->join('users','concat(users.first_name," ",users.last_name) = expenses.add_by','left');
        $this->db->where('users.user_id = "'.$check_record->assign_to.'" and approved_status != "2" and expenses.date >= "'.$check_record->given_date.'"');
        $expenses = $this->db->get()->result_array();


        $this->db->select('transactions_history.reason as detail,transactions_history.amount as amount,"D" as debit_credit,transactions_history.trans_date as created_at,"" as image ');
        $this->db->from('transactions_history');
        $this->db->where('transactions_history.petty_cash_id = "'.$check_record->id.'"');
        $trans = $this->db->get()->result_array();

        $data['Pettycashs']=array_merge($expenses,$trans);
	
        usort($data['Pettycashs'], function($a, $b) {
            return strtotime($a['created_at']) - strtotime($b['created_at']);
        });

		
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('petty_cash/pettycash_statement', $data);
        $this->load->view('inc/footer');

    }

	

}