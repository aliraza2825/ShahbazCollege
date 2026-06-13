<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loans extends CI_Controller {

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

	public function index()
	{
		
	}

	public function apply_loan()
    {

        $this->db->select('loans.*,(select count(*) from loan_plan where loan_id = loans.id and amount_paid = 0) as remaining');
        $this->db->from('loans');
        $data['loans'] = $this->db->where("(loans.user_id=". $this->session->userdata('user_id').")")->get()->result_array();

        $this->db->select('loan_settings.*');
        $this->db->from('loan_settings');
        $loansetting = $this->db->get()->result_array();
        $data['loansetting']=$loansetting;

        $this->db->select('users.salary');
        $this->db->from('users');
        $this->db->where("(users.user_id ='".$this->session->userdata('user_id')."')");
        $maxamount = $this->db->get()->result_array();
        $maxamount=$maxamount[0]['salary'];


        $maxamount=($maxamount*30)*$loansetting[0]['max_multiply_salary'];
        $data['max_amount']=$maxamount;
        $data['max_months']=$loansetting[0]['max_months'];
        $access = checkUserAccess();
        $this->db->select('users.*');
        $this->db->from('users');

        if ($access && ($access[0]['apply_loan'] == 1 || $this->session->userdata('role') == 'Admin'))
            $this->db->where("(users.status ='1')");
        else
            $this->db->where("(users.user_id ='".$this->session->userdata('user_id')."' and users.status ='1')");
        $data['staffs'] = $this->db->get()->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('loans/apply_loan', $data);
        $this->load->view('inc/footer');

    }

    public function insert_loan()
    {
        $user_id=$this->input->post('user_id');
        $loan_type=$this->input->post('loan_type');
        $in_month=$this->input->post('in_month');
        $amount=$this->input->post('amount');
        $description=$this->input->post('reason');

        $already = $this->db->get_where('loans',"user_id = $user_id and (status = 0 or (status = 1 and cash_given IS NULL))")->result_array();

        if (count($already) > 0){
            $this->session->set_flashdata('error', 'You Have Already Applied for a Loan.');
            redirect('loans/apply_loan');
        }
        else {

            if ($this->session->userdata('role') != 'Admin') {
                $user = $this->db->get_where('users', "user_id = $user_id")->row();
                $setting = $this->db->get('loan_settings')->row();
                $can_apply_loan = $user->gross_salary * $setting->max_multiply_salary;

                $this->db->select('sum(cash_given) as total');
                $this->db->from('loans');
                $this->db->where("user_id = $user_id and loans.status = 1 and (select count(*) from loan_plan where loan_id = loans.id and amount_paid = 0) > 0");
                $already_two = $this->db->get()->row();

                if ($already_two) {
                    if ($already_two->total > $can_apply_loan || $amount > ($can_apply_loan - $already_two->total)) {
                        $this->session->set_flashdata('error', "You Have Reached Your Maximum Loan Limit of $already_two->total. You can Take Loan of ".($can_apply_loan - $already_two->total));
                        redirect('loans/apply_loan');
                    }
                }
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

            } else {
                //else, set the success message
                $data['upload_data'] = $this->upload->data();
                if ($data['upload_data']['file_name']) {
                    $image = $data['upload_data']['file_name'];
                }
            }


            $this->db->set('type', $loan_type);
            $this->db->set('user_id', $user_id);
            $this->db->set('amount_applied', $amount);
            $this->db->set('months', $in_month);
            $this->db->set('reason', $description);
            $this->db->set('created_by', $this->session->userdata('user_id'));
            $this->db->set('undertaken_img', $image);


            $ins = $this->db->insert('loans');
            if ($ins) {

                $this->session->set_flashdata('message', 'Successfully Applied.');
                redirect('loans/apply_loan');

            } else {
                $this->session->set_flashdata('error', 'error occured.');
                redirect('loans/apply_loan');
            }
        }
    }

    public function insert_edit_apply_loan()
    {

        $user_id=$this->session->userdata('user_id');
        $loan_type=$this->input->post('loan_type');
        $in_month=$this->input->post('in_month');
        $amount=$this->input->post('amount');
        $description=$this->input->post('reason');
        $loanid=$this->input->post('loan_id');

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

        $undertakenimage = '';

        //if not successful, set the error message
        if (!$this->upload->do_upload('student_document')) {
            $data = array('msg' => $this->upload->display_errors());
            $student_document = '';

        }
        else
        {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $undertakenimage = $data['upload_data']['file_name'];
            }
        }



        $this->db->set('type',$loan_type);
        $this->db->set('user_id',$user_id);
        $this->db->set('amount_applied',$amount);
        $this->db->set('months',$in_month);
        $this->db->set('reason',$description);
        $this->db->set('created_by',$user_id);

        if ($undertakenimage!= '') {
            $this->db->set('undertaken_img', $undertakenimage);
        }

        $this->db->where("(loans.id ='".$loanid."')");


        $ins = $this->db->update('loans');
        if ($ins){
            $this->apply_loan();

        }else{

            $this->session->set_flashdata('message', 'error occured.');
            redirect('loans/apply_loan');

        }

    }

    public function loans_list()
    {
        $type = $this->input->post('type');
        $find_users = $this->input->post('users');

        $this->db->select('users.*');
        $this->db->from('loans');
        $this->db->join('users','loans.user_id = users.user_id','inner');
        $this->db->group_by("loans.user_id");
        $data['users'] = $this->db->get()->result_array();

        $this->db->select('users.*,loans.*,(select count(*) from loan_plan where loan_id = loans.id and amount_paid = 0) as remaining');
        $this->db->from('loans');
        $this->db->join('users','loans.user_id = users.user_id','inner');
        if ($type) {

            if ($type == '1') {
                $this->db->where("loans.status", $type);
                $this->db->where("(select count(*) from loan_plan where loan_id = loans.id and amount_paid = 0) > 0");
            }
            elseif ($type == '3') {
                $this->db->where("loans.status", "1");
                $this->db->where("(select count(*) from loan_plan where loan_id = loans.id and amount_paid = 0) = 0");
            }
            else
                $this->db->where("loans.status", $type);
        }
        else{
            $this->db->where("loans.status", "0");
        }

        if ($find_users)
            $this->db->where_in("loans.user_id", $find_users);

        $this->db->order_by("loans.id", "Desc");
        $data['loans'] = $this->db->get()->result_array();

        $this->db->select('loan_settings.*');
        $this->db->from('loan_settings');
        $data['loansetting'] = $this->db->get()->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('loans/loans_approvel', $data);
        $this->load->view('inc/footer');
    }

    public function loans_approval()
    {

        $in_month=$this->input->post('in_month');
        $approve_amount=$this->input->post('amount');

        $this->db->set('amount_approved', $approve_amount);
        $this->db->set('months_approved', $in_month);


        $this->db->set('status', $this->input->post('status'));
        $this->db->set('updated_by', $this->session->userdata('user_id'));

        $this->db->where("(loans.id ='".$this->input->post('loan_id')."')");
        $this->db->update('loans');

        $this->session->set_flashdata('message', 'Successfully updated Loan Status');
        redirect('loans/loans_list');

    }

    public function accounts_loans_list()
    {
        $this->db->select('users.*,loans.*');
        $this->db->from('loans');
        $this->db->join('users','loans.user_id = users.user_id','inner');
        $this->db->where('loans.status = 1 and cash_given IS NULL and cash_given_by = 0');
        $this->db->order_by("loans.id", "Desc");
        $data['loans'] = $this->db->get()->result_array();

        $this->db->select('loan_settings.*');
        $this->db->from('loan_settings');
        $data['loansetting'] = $this->db->get()->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('loans/loans_approvel_accounts', $data);
        $this->load->view('inc/footer');

    }

    public function loans_accounts_approval()
    {

        $approve_amount=$this->input->post('amount_given');
        $months=$this->input->post('in_month');

        if (my_pettycash() >= $approve_amount) {

            $amountavg = $approve_amount / $months;

            $this->db->set('cash_given', $approve_amount);
            $this->db->set('give_through', 'cash');
            $this->db->set('cash_given_by', $this->session->userdata('user_id'));
            $this->db->where("(loans.id ='" . $this->input->post('loan_id') . "')");
            $this->db->update('loans');

            $time = date("Y-m-d");
            $my_loan = $this->db->get_where('loans', 'id = "' . $this->input->post('loan_id') . '"')->row();
            for ($i = 1; $i <= $months; $i++) {
                if ($my_loan->type == 'ADVANCE')
                    $dead_line = $time;
                else {
                    $time = strtotime(date("Y-m-d"));
                    $dead_line = date("Y-m-30", strtotime("+$i month", $time));
                }

                $this->db->set('amount', $amountavg);
                $this->db->set('due_date', $dead_line);
                $this->db->set('loan_id', $this->input->post('loan_id'));
                $this->db->set('created_by', $this->session->userdata('user_id'));
                $this->db->insert('loan_plan');
            }

            $mainloan = $this->db->get_where('loans', array('id' => $this->input->post('loan_id')))->row();

            $this->db->set('campus_id', $this->session->userdata('user_campus_id'));
            if ($mainloan->type == 'ADVANCE') {
                $this->db->set('expense_category_id', '30');
            } else {
                $this->db->set('expense_category_id', '31');
            }

            $this->db->set('title', 'Advance / Loan');
            $this->db->set('date', date('Y-m-d'));
            $this->db->set('amount', $approve_amount);
            $this->db->set('purpose', 'Advance / Loan Given to Employee');
            $this->db->set('user_id', $mainloan->user_id);
            $this->db->set('loan_id', $this->input->post('loan_id'));
            $this->db->set('actual_date', date('Y-m-d H:i:s'));
            $this->db->set('image', '');
            $this->db->set('approved_status', '1');
            $this->db->set('add_by_id', $this->session->userdata('user_id'));
            $this->db->set('add_by', $this->session->userdata('name'));
            $this->db->insert('expenses');

            $this->db->set('remaining_amount', 'remaining_amount -' . $approve_amount . '', false);
            $this->db->where('assign_to', $this->session->userdata('user_id'));
            $this->db->update('petty_cash_college_wise');
            $this->session->set_flashdata('message', 'Successfully updated Loan Status');
            redirect('loans/accounts_loans_list');
        }else{
            $this->session->set_flashdata('error', 'You Do Not Have Enough Petty Cash');
            redirect('loans/accounts_loans_list');
        }
    }

    public function loans_detail_view($loanid)
    {
        $this->db->select('loan_plan.*,users.*,loans.*,loan_plan.id as loan_plan_id');
        $this->db->from('loans');
        $this->db->join('users','loans.user_id = users.user_id','inner');
        $this->db->join('loan_plan','loan_plan.loan_id = loans.id','inner');
        $this->db->where('(loans.status = 1 and loan_plan.loan_id = '.$loanid.')');
        $this->db->order_by("loan_plan.due_date", "asc");
        $loans = $this->db->get()->result_array();
        $data['loans'] = $loans;

        $total_loan = $loans[0]['cash_given'];
        $paidloan=0;
        $unpaidloan=0;

        foreach ($loans as $da){
            if ($da['amount_paid']>0){
                $paidloan+=$da['amount_paid'];
            }
            else{
                $unpaidloan+=$da['amount'];
            }
        }

        $data['total_loan'] = $total_loan;
        $data['remaining_loan'] = $unpaidloan;
        $data['paid_loan'] = $paidloan;

        $this->db->select('loan_settings.*');
        $this->db->from('loan_settings');
        $data['loansetting'] = $this->db->get()->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('loans/loan_breakup', $data);
        $this->load->view('inc/footer');
    }

	public function loan_print_view($loanid)
    {

        $this->db->select('users.*,loans.*');
        $this->db->from('loans');
        $this->db->join('users','loans.user_id = users.user_id','inner');
        $this->db->where('(loans.status = 1 and loans.id = '.$loanid.')');
        $this->db->order_by("loans.created_at", "asc");
        $loans = $this->db->get()->result_array();
        $data['loans'] = $loans;

        $this->db->select('loan_settings.*');
        $this->db->from('loan_settings');
        $data['loansetting'] = $this->db->get()->result_array();




        $this->load->view('loans/loan_print', $data);


    }

    public function pay_now($loan_id)
    {
        $installment_ids = $this->input->post('installment_ids');
        $amount = $this->input->post('receivable_amount');
        $campus_id = $this->input->post('campus_id');

        $this->db->set('amount_paid', 'amount', false);
        $this->db->set('paid_at','cash');
        $this->db->set('paid_date',date("Y-m-d"));
        $this->db->set('campus_id',$campus_id);
        $this->db->where_in('id',explode(",",$installment_ids));
        $ins = $this->db->update('loan_plan');
        if ($ins){
            redirect("loans/loans_detail_view/$loan_id");
        }
        else{
            $this->session->set_flashdata('message', 'error occured.');
            redirect('loans/apply_loan');
        }
    }
    
}
