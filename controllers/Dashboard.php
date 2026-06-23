<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Dashboard extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();
		$this->load->model('dashboards');
		$this->load->model('clas');
		global $bucket_address;
		
	}
	public function index()
	{
	    $access = checkUserAccess();
		$class_ids = @explode(',',$access[0]['class_ids']);
		if($access && $access[0]['campus_ids']!=NULL)
        {
            $campuses_ids = @explode(',',$access[0]['campus_ids']);
        }
        else
        {
            $campuses_ids = array();
        }
		
		$val = @$this->input->post('search');
		
		
		if(@$val!=''){
			if($this->input->post('fee')=='Check Fee')
			{
				$this->db->select('students.*, classes.name as class_name');
				$this->db->from('students');
				$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
				//$this->db->like('students.roll_no', $val);
				//$this->db->like('students.cnic', $val);
				$this->db->where("(students.roll_no LIKE '%".$val."%' OR students.cnic LIKE '%".$val."%' OR students.mobile LIKE '%".$val."%' OR students.emergency_no LIKE '%".$val."%' OR students.first_name LIKE '%".$val."%' OR students.last_name LIKE '%".$val."%' OR students.father_name LIKE '%".$val."%')", NULL, FALSE);
				//$this->db->where(array('students.status'=>1));
				
				/*if($this->session->userdata('role')!='Admin'){
					$this->db->where_in('classes.class_id', $class_ids);
				}*/
				
				$data['students'] = $this->db->get()->result_array();
				
				if(count($data['students'])>0)
				{
					redirect(site_url().'/students/payments_paid/'.$data['students'][0]['student_id']);
				}
				else
				{
					$student_id = $this->db->get_where('payments', array('challan_no'=>$val))->row()->student_id;
					if($student_id!='')
					{
						redirect(site_url().'/students/payments_paid/'.$student_id);
					}
					else
					{
						$this->session->set_flashdata('error', 'Nothing Match.');
						redirect('dashboard');
					}
				}
			}
			else
			{
				$this->db->select('students.*, classes.name as class_name,machine_data.machine_id');
				$this->db->from('students');
				$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
				$this->db->join('machine_data', 'machine_data.teacher_student_id=students.student_id', 'inner');
				//$this->db->like('students.roll_no', $val);
				//$this->db->like('students.cnic', $val);
				//$this->db->where(array('students.status'=>1));
				$this->db->where("(students.roll_no LIKE '%".$val."%' OR students.cnic LIKE '%".$val."%' OR students.mobile LIKE '%".$val."%' OR students.emergency_no LIKE '%".$val."%' OR students.first_name LIKE '%".$val."%' OR students.last_name LIKE '%".$val."%' OR students.father_name LIKE '%".$val."%')", NULL, FALSE);
				
				if($this->session->userdata('role')!='Admin'){
					$this->db->where_in('classes.class_id', $class_ids);
				}
				
				$data['students'] = $this->db->get()->result_array();
			}
		}
		
		if(@$this->input->post('comment'))
		{
			$this->db->set('comment', $this->input->post('comment'));
			$this->db->set('status', 1);
			$this->db->set('last_edit', $this->input->post('last_edit'));
			$this->db->where('apply_now_id', $this->input->post('apply_now_id'));
			$this->db->update('apply_now');
			$this->session->set_flashdata('message', 'Your Request Submit Successfully.');
		}
		
		if(@$this->input->post('clear_new_admission'))
		{
			$this->db->set('clear_by_admin', 1);
			$this->db->where('apply_now_id', $this->input->post('apply_now_id'));
			$this->db->update('apply_now');
			$this->session->set_flashdata('message', 'Your Request Clear Submit Successfully.');
		}
		
		$data['total_students'] = $this->dashboards->total_students();
		$data['total_teachers'] = $this->dashboards->total_teachers();
		$data['new_students_this_month'] = $this->dashboards->new_students_this_month();
        if(count($campuses_ids)>0 || $this->session->userdata('role')=='Admin'):
		
			$data['start_date'] = date('Y-m-01');
			$data['end_date'] = date('Y-m-d');
			$data['date_type'] = 'actual_paid_date';
		
		
			$data['this_month_earning'] = $this->dashboards->getTotalSubmittedFee($data['start_date'], $data['end_date'], $data['date_type']);
			

		
		    $data['this_month_expense'] = $this->dashboards->thisMonthExpense();
            $data['classes_status'] = $this->dashboards->classesStatus();
		endif;
		$data['college_fee'] = $this->dashboards->unpaidCollegeFee();
		$data['contractor_fees'] = $this->dashboards->unpaidContractorsCollegeFee();
		$data['fee_requests'] = $this->dashboards->getFeeRequest();
		$data['discount_requests'] = $this->db->get_where('discounts_approval','status = 0')->result_array();
		$data['fee_requests_contractors'] = $this->dashboards->getContractsFeeRequest();
		$data['students_edit_requests'] = $this->dashboards->getStudentsEditRequest();
		$data['admissions'] = $this->dashboards->getNewAdmisssions();
		$data['clear_admissions'] = $this->dashboards->getNewClearAdmisssions();
		$data['new_student_entries'] = $this->dashboards->getNewStudentEntries();
		$data['new_expense_entries'] = $this->dashboards->getNewExpenseEntries();
		$data['campuses'] = $this->clas->getCampuses();
		//$data['fee_dues_comments'] = $this->dashboards->getFeeDuesComments();
		//$data['contracts_fee_dues_comments'] = $this->dashboards->getContractFeeDuesComments();
		//$data['fee_dues_students_count'] = $this->dashboards->getFeeDuesStudentsCount();
		//$data['fee_dues_contractors_count'] = $this->dashboards->getFeeDuesContractorsCount();
		//$data['fee_dues_clear_comments'] = $this->dashboards->getFeeDuesClearComments();
		//$data['contractors_fee_dues_clear_comments'] = $this->dashboards->getFeeDuesContractorsClearComments();
		$data['reminders'] = $this->dashboards->getReminders();
		$data['pending_reminders'] = $this->dashboards->getPendingReminders();
		$data['under_review_reminders'] = $this->dashboards->getUnderReviewReminders();
		//$data['unclear_products'] = $this->dashboards->getUnclearProducts();
		//$data['updated_products'] = $this->dashboards->getUpdatedProducts();
		//$data['unclear_documents'] = $this->dashboards->getUnclearDocuments();
		$data['pending_questions'] = $this->db->get_where('questions',array('status'=>0))->result_array();
        $data['uncheck_assignments'] = $this->dashboards->getUncheckAssignments();
		//$data['my_lectures'] = $this->dashboards->getMyLectures();
		
		$data['lectures'] = $this->db
                ->join('courses','courses.course_id = lectures.course','left')
                ->join('campuses','campuses.campus_id = lectures.campus','left')
                ->join('users','users.user_id = lectures.teacher','left')
                ->join('rooms','rooms.room_id = lectures.room','left')
				->where('users.user_id',$this->session->userdata('user_id'))
                ->get('lectures')->result_array();
		
		//CLEAR PROCEDURES
		$data['campuses'] = $this->dashboards->getCampuses();
		
		//CAMPUS WISE STATUSES
		if(@$this->input->post('from_date'))
		{
			$data['from_date'] = $from_date = $this->input->post('from_date');
		}
		else
		{
			$data['from_date'] = $from_date = date('Y-m-01');
		}
		if(@$this->input->post('to_date'))
		{
			$data['to_date'] = $to_date = $this->input->post('to_date');
		}
		else
		{
			$data['to_date'] = $to_date = date('Y-m-d');
		}
		if(@$this->input->post('date_type'))
		{
			$data['date_type'] = $date_type = $this->input->post('date_type');
		}
		else
		{
			$data['date_type'] = $date_type = 'actual_paid_date';
		}
		
		//CHECK struck of count list
		        $this->db->select('count(struckofdetails_students.student_id)');        
				$this->db->from('struckofdetails_students');        
				$this->db->where("(struckofdetails_students.status = '0')", NULL, FALSE);        
				$this->db->group_by("struckofdetails_students.student_id");        
				$data['struckofcount'] = $this->db->get()->result_array();
		
		
		//CHECK struck of count pending
		        $this->db->select('count(ast.student_id)');        
				$this->db->from('struckofdetails_students ast'); 
				
				$this->db->where('(ast.status = 0 and (select COUNT(student_id) from struckofdetails_students where student_id = ast.student_id) = 3) or (ast.action_type = 1 and ast.status = 0)' );        
				$this->db->group_by("ast.student_id");       
				$data['struckofcountp'] = $this->db->get()->result_array();
		
		//CHECK strucked of students count list
		        $this->db->select('count(struckofdetails_students.student_id)');        
				$this->db->from('struckofdetails_students');        
				$this->db->where("(struckofdetails_students.status = '1')", NULL, FALSE);        
				$this->db->group_by("struckofdetails_students.student_id");        
				$data['struckedofcount'] = $this->db->get()->result_array();
		
		//CHECK PENDING EXPENSES FOR APPROVAL
		
		 $this->db->select('count(*) as count');        
				$this->db->from('expenses');        
				$this->db->where("(expenses.approved_status = '0' and expenses.add_by != 'Muhammad Irfan')", NULL, FALSE);       
				$data['expenseapprovalcount'] = $this->db->get()->result_array();
				$data['expenseapprovalcount']=$data['expenseapprovalcount'][0];
				$data['expenseapprovalcount']=$data['expenseapprovalcount']['count'];
				
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('dashboard', $data);
		$this->load->view('inc/footer');
	}
	
	public function clear_new_student_entries($student_id)
	{
		$this->db->set('clear_status',1);
		$this->db->set('clear_by', $this->session->userdata('name').' '.date('Y-m-d h:i:s A'));
		$this->db->where('student_id', $student_id);
		$this->db->update('students');
		$this->session->set_flashdata('message', 'New Admisssion clear successfully.');
		redirect('dashboard/new_admission_entries');
	}
	
	public function clear_new_expense_entries($expense_id)
	{
		$this->db->set('clear_status',1);
		$this->db->set('clear_by', $this->session->userdata('name').' '.date('Y-m-d h:i:s A'));
		$this->db->where('expense_id', $expense_id);
		$this->db->update('expenses');
		$this->session->set_flashdata('message', 'New Expense clear successfully.');
		redirect('dashboard/new_expense_entries');
	}
	
	public function clear_unpaid_fee()
	{
		$payment_id = $this->input->post('payment_id');
		$this->dashboards->clearUnpaidFee($payment_id);
		$this->session->set_flashdata('message', 'Fee clear successfully.');
		redirect('dashboard/fee_status');
	}
	
	public function new_submit_fees()
	{
		if($this->input->post('start_date') && $this->input->post('end_date'))
		{
			$data['start_date'] = $this->input->post('start_date');
			$data['end_date'] = $this->input->post('end_date');
			$data['date_type'] = $this->input->post('date_type');
			$data['fees'] = $this->dashboards->newSubmitFees($data['start_date'], $data['end_date'], $data['date_type']);
		}
		else
		{
			$data['start_date'] = date('Y-m-01');
			$data['end_date'] = date('Y-m-t');
			$data['date_type'] = 'actual_paid_date';
			$data['fees'] = $this->dashboards->newSubmitFees($data['start_date'], $data['end_date'], $data['date_type']);
		}
		
		$data['total_submitted_fee'] = $this->dashboards->getTotalSubmittedFee($data['start_date'], $data['end_date'], $data['date_type']);
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('dashboard/new_submit_fees', $data);
		$this->load->view('inc/footer');
	}
	
	public function new_expenses()
	{
		if($this->input->post('start_date')&&$this->input->post('end_date'))
		{
			$data['start_date'] = $this->input->post('start_date');
			$data['end_date'] = $this->input->post('end_date');
			$data['date_type'] = $this->input->post('date_type');
			$data['fees'] = $data['expenses'] = $this->dashboards->newExpenses($data['start_date'], $data['end_date'],$data['date_type']);
		}
		else
		{
			$data['start_date'] = date('Y-m-01');
			$data['end_date'] = date('Y-m-t');
			$data['date_type'] = 'actual_date';
			$data['fees'] = $data['expenses'] = $this->dashboards->newExpenses($data['start_date'], $data['end_date'],$data['date_type']);
		}
		
		$data['total_expenses'] = $this->dashboards->getTotalExpenses($data['start_date'], $data['end_date']);
		
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('dashboard/new_expenses', $data);
		$this->load->view('inc/footer');
	}
	
	public function profit()
	{
		if($this->input->post('start_date')&&$this->input->post('end_date'))
		{
			$data['start_date'] = $this->input->post('start_date');
			$data['end_date'] = $this->input->post('end_date');
			$data['date_type'] = $this->input->post('date_type');
		}
		else
		{
			$data['start_date'] = date('Y-m-01');
			$data['end_date'] = date('Y-m-t');
			$data['date_type'] = 'date';
		}
		
		$data['expense'] = $this->dashboards->getSelectiveExpenses($data['start_date'], $data['end_date'], $data['date_type']);
		$data['profit'] = $this->dashboards->getSelectiveProfits($data['start_date'], $data['end_date'], $data['date_type']);
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('dashboard/profit', $data);
		$this->load->view('inc/footer');
	}
	
	public function new_students()
	{
		if($this->input->post('month') && $this->input->post('year'))
		{
			$data['month'] = $month = $this->input->post('month');
			$data['year'] = $year = $this->input->post('year');
			$data['students'] = $this->dashboards->getNewStudents($month, $year);
		}
		else
		{
			$data['month'] = $month = date('m');
			$data['year'] = $year = date('Y');
			$data['students'] = $this->dashboards->getNewStudents($month, $year);
		}
		
		//$data['students'] = $this->dashboards->getNewStudents($data['start_date'], $data['end_date']);
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('dashboard/new_admissions',$data);
		$this->load->view('inc/footer');
	}
	
	public function clear_fee_update($payment_id)
	{
		$update_request = $this->db->get_where('update_payment_requests', array('id'=>$payment_id, 'ok_by_admin'=>0))->result_array();
		$this->dashboards->updatePayment($update_request);
		$this->dashboards->updateClearPayment($update_request[0]['id']);
		$this->session->set_flashdata('message', 'Fee Updated successfully.');
		redirect('dashboard/update_fee_status');
	}
	
	public function clear_student_update($student_id)
	{
		$update_request = $this->db->get_where('update_student_requests', array('student_id'=>$student_id, 'ok_by_admin'=>0))->result_array();
		$request = $this->dashboards->updateStudent($update_request);
		$this->dashboards->updateStudentRequest($update_request[0]['student_id']);
		
		
		if($request=='success')
		{
		    if($update_request[0]['status']==0)
    		{
    			$check_refund = $this->db->get_where('deleted_students', array('student_id'=>$update_request[0]['student_id'],'status'=>0))->result_array();
    			
    			if($check_refund[0]['refund_amount']>0)
    			{
    				//GET STUDENT DETAILS
    				$this->db->select('campuses.campus_id');
    				$this->db->from('students');
    				$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
    				$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
    				$this->db->where('students.student_id', $update_request[0]['student_id']);
    				$student_detail = $this->db->get()->result_array();
    				
    				//ADD REFUND AMOUNT IN EXPENSES
    				$this->db->set('date', date('Y-m-d'));
    				$this->db->set('actual_date', date('Y-m-d'));
    				$this->db->set('purpose', 'Refund issue and approved by '.$this->session->userdata('name').'');
    				$this->db->set('title', 'Student Refund');
    				$this->db->set('amount', $check_refund[0]['refund_amount']);
    				$this->db->set('add_by', $this->session->userdata('name'));
    				$this->db->set('last_edit', $this->session->userdata('name'));
    				$this->db->set('campus_id', $student_detail[0]['campus_id']);
    				$this->db->set('expense_category_id', 7);
    				$this->db->insert('expenses');
    				
    				//DELETED STUDENT STATUS UPDATE
    				$this->db->set('status',1);
    				$this->db->set('approve_by',$this->session->userdata('name'));
    				$this->db->where('id',$check_refund[0]['id']);
    				$this->db->update('deleted_students');
    				
    			}
    		}
    		
    		$this->session->set_flashdata('message', 'Student Updated successfully.');
    		redirect('dashboard/students_edit_requests');
		}
		else
		{
		    $this->session->set_flashdata('error', 'Student cannot be updated due to same CNIC added in our system against same course.');
    		redirect('dashboard/students_edit_requests');
		}
	}
	
	public function reject_student_update($student_id)
	{
		$this->db->where('student_id',$student_id);
		$this->db->where('ok_by_admin',0);
		$this->db->delete('update_student_requests');
		
		$check_refund = $this->db->get_where('deleted_students', array('student_id'=>$student_id,'status'=>0))->result_array();
		if(count($check_refund)>0)
		{
			$this->db->where('student_id',$student_id);
			$this->db->delete('deleted_students');
		}
		
		$this->session->set_flashdata('message', 'Student Request Rejected.');
		redirect('dashboard/students_edit_requests');
	}
	
	/*public function test()
	{
		$payments = $this->db->get('payments')->result_array();
		foreach($payments as $payment)
		{
			$this->db->set('actual_paid_date', $payment['paid_date']);
			$this->db->where('id', $payment['id']);
			$this->db->update('payments');
		}
	}*/
	
	public function clear_comment($fee_id, $date)
	{
		$this->db->set('clear_status', 1);
		$this->db->where('fee_id', $fee_id);
		$this->db->update('fees_remarks');
		
		//
		$this->db->set('dead_line', $date);
		$this->db->where('id', $fee_id);
		$this->db->update('payments');
		
		$this->session->set_flashdata('message', 'Status Updated successfully.');
		redirect('dashboard/fee_dues_clear_comments');
	}
	
	public function delete_comment($fee_id, $date)
	{
		$this->db->where('fee_id', $fee_id);
		$this->db->delete('fees_remarks');
		
		
		$this->session->set_flashdata('message', 'Status Deleted successfully.');
		redirect('dashboard/fee_dues_clear_comments');
	}
	
	public function new_admission_entries($campus_id=NULL)
	{
		$data['new_student_entries'] = $this->dashboards->getNewStudentEntries($campus_id);
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('dashboard/new_admission_entries', $data);
		$this->load->view('inc/footer');
	}
	
	public function new_expense_entries($campus_id=NULL)
	{
		$data['new_expense_entries'] = $this->dashboards->getNewExpenseEntries($campus_id);
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('dashboard/new_expense_entries', $data);
		$this->load->view('inc/footer');
	}
	
	public function fee_status($campus_id=NULL)
	{
		$data['college_fee'] = $this->dashboards->unpaidCollegeFee($campus_id);
		$data['contractor_fees'] = $this->dashboards->unpaidContractorsCollegeFee($campus_id);
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('dashboard/fee_status', $data);
		$this->load->view('inc/footer');
	}
	
	public function update_fee_status($campus_id=NULL)
	{
		$data['fee_requests'] = $this->dashboards->getFeeRequest($campus_id);
		$data['fee_requests_contractors'] = $this->dashboards->getContractsFeeRequest($campus_id);
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('dashboard/update_fee_status', $data);
		$this->load->view('inc/footer');
	}
	
	public function students_edit_requests($campus_id=NULL)
	{
		$data['students_edit_requests'] = $this->dashboards->getStudentsEditRequest($campus_id);
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('dashboard/students_edit_requests', $data);
		$this->load->view('inc/footer');
	}
	
	public function classes_status()
	{
		$data['classes_status'] = $this->dashboards->classesStatus();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('dashboard/classes_status', $data);
		$this->load->view('inc/footer');
	}
	
	public function fee_dues_comments($campus_id=NULL)
	{	
		$data['fee_dues_comments'] = $this->dashboards->getFeeDuesComments($campus_id);
		$data['contracts_fee_dues_comments'] = $this->dashboards->getContractFeeDuesComments($campus_id);	
		$data['fee_dues_students_count'] = $this->dashboards->getFeeDuesStudentsCount($campus_id);
		$data['fee_dues_contractors_count'] = $this->dashboards->getFeeDuesContractorsCount($campus_id);
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('dashboard/fee_dues_comments', $data);
		$this->load->view('inc/footer');
	}
	
	public function fee_dues_clear_comments($campus_id=NULL)
	{
		$data['fee_dues_clear_comments'] = $this->dashboards->getFeeDuesClearComments($campus_id);
		$data['contractors_fee_dues_clear_comments'] = $this->dashboards->getFeeDuesContractorsClearComments($campus_id);
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('dashboard/fee_dues_clear_comments', $data);
		$this->load->view('inc/footer');
	}
	
	public function reject_clear_fee_update($fee_id)
	{
		$this->db->where(array('id'=>$fee_id,'ok_by_admin'=>0));
		$this->db->delete('update_payment_requests');
		
		$this->session->set_flashdata('message', 'Fee Request Rejected.');
		redirect('dashboard/update_fee_status');
	}
	
	public function update_reminder($reminder_id,$status)
	{
		$this->db->set('status',$status);
		$this->db->where('reminder_id',$reminder_id);
		$this->db->update('reminder');
		
		$this->session->set_flashdata('message', 'Status Updated Successfully.');
		redirect('dashboard');
	}
	
	public function unclear_products($campus_id=NULL)
	{
		$data['unclear_products'] = $this->dashboards->getUnclearProducts($campus_id);
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('dashboard/unclear_products', $data);
		$this->load->view('inc/footer');
	}
	
	public function clear_product($product_id)
	{
		$this->db->set('clear_by',$this->session->userdata('name'));
		$this->db->set('status',1);
		$this->db->where('product_id',$product_id);
		$this->db->update('products');
		
		$this->session->set_flashdata('message', 'Status Updated Successfully.');
		redirect('dashboard/unclear_products');
	}
	
	public function delete_product($product_id)
	{
		$this->db->where('product_id',$product_id);
		$this->db->delete('products');
		
		$this->session->set_flashdata('message', 'Product Deleted Successfully.');
		redirect('dashboard/unclear_products');
	}
	
	public function updated_products($campus_id=NULL)
	{
		$data['updated_products'] = $this->dashboards->getUpdatedProducts($campus_id);
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('dashboard/updated_products', $data);
		$this->load->view('inc/footer');
	}
	
	public function unclear_documents($campus_id=NULL)
	{
		$data['unclear_documents'] = $this->dashboards->getUnclearDocuments($campus_id);
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('dashboard/unclear_documents', $data);
		$this->load->view('inc/footer');
	}
	
	public function clear_document($document_id)
	{
		$this->db->set('clear_by',$this->session->userdata('name'));
		$this->db->set('status',1);
		$this->db->where('document_id',$document_id);
		$this->db->update('documents');
		
		$this->session->set_flashdata('message', 'Status Updated Successfully.');
		redirect('dashboard/unclear_documents');
	}
	
	public function delete_document($document_id)
	{
		$this->db->where('document_id',$document_id);
		$this->db->delete('documents');
		
		$this->session->set_flashdata('message', 'Document Deleted Successfully.');
		redirect('dashboard/unclear_documents');
	}
	
	public function pending_questions()
	{
		//MCQs QUESTIONS
	    $this->db->select('*,questions.add_by as add_by,questions.last_edit as last_edit');
		$this->db->from('questions');
		$this->db->join('topics','topics.topic_id=questions.topic_id','INNER');
        $this->db->join('course_subjects','topics.course_subject_id=course_subjects.course_subject_id','INNER');
        $this->db->join('courses','courses.course_id=course_subjects.course_id','INNER');
		$this->db->where(array('questions.option_1!='=>'','questions.status'=>0));
		$data['questions'] = $this->db->get()->result_array();

		//SHORT QUESTIONS
        $this->db->select('*,questions.add_by as add_by,questions.last_edit as last_edit');
        $this->db->from('questions');
        $this->db->join('topics','topics.topic_id=questions.topic_id','INNER');
        $this->db->join('course_subjects','topics.course_subject_id=course_subjects.course_subject_id','INNER');
        $this->db->join('courses','courses.course_id=course_subjects.course_id','INNER');
        $this->db->where(array('questions.type'=>'short-question','questions.status'=>0));
        $data['shortquestions'] = $this->db->get()->result_array();

        //LONG QUESTIONS
       $this->db->select('*,questions.add_by as add_by,questions.last_edit as last_edit');
        $this->db->from('questions');
        $this->db->join('topics','topics.topic_id=questions.topic_id','INNER');
        $this->db->join('course_subjects','topics.course_subject_id=course_subjects.course_subject_id','INNER');
        $this->db->join('courses','courses.course_id=course_subjects.course_id','INNER');
        $this->db->where(array('questions.type'=>'long-question','questions.status'=>0));
        $data['longquestions'] = $this->db->get()->result_array();

        //WORD MEANINGS
       $this->db->select('*,questions.add_by as add_by,questions.last_edit as last_edit');
        $this->db->from('questions');
        $this->db->join('topics','topics.topic_id=questions.topic_id','INNER');
        $this->db->join('course_subjects','topics.course_subject_id=course_subjects.course_subject_id','INNER');
        $this->db->join('courses','courses.course_id=course_subjects.course_id','INNER');
        $this->db->where(array('questions.type'=>'word-meaning','questions.status'=>0));
        $data['wordmeanings'] = $this->db->get()->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('dashboard/pending_questions', $data);
		$this->load->view('inc/footer');
	}
	
	public function approve_question($question_id)
	{
		$this->db->set('clear_by',$this->session->userdata('name'));
		$this->db->set('status',1);
		$this->db->where('question_id',$question_id);
		$this->db->update('questions');
		
		$this->session->set_flashdata('message','Question Approved.');
		redirect('dashboard/pending_questions');
	}
	
	public function delete_question($question_id)
	{
		$this->db->where('question_id',$question_id);
		$this->db->delete('questions');
		
		$this->session->set_flashdata('error','Question Rejected.');
		redirect('dashboard/pending_questions');
	}

	public function uncheck_assignments()
    {
        $access = checkUserAccess();
        $subject_ids = @explode(',',$access[0]['assignment_subject_ids']);
        $campus_ids = @explode(',',$access[0]['campus_ids']);

        $this->db->select('assignments.*,chapters.chapter_name,courses.course_name,course_subjects.subject_name as subject_name,campuses.campus_name, classes.name as class_name,students.*');
        $this->db->from('assignments');
        $this->db->join('chapters','chapters.chapter_id=assignments.chapter_id','inner');
        $this->db->join('courses','courses.course_id=assignments.course_id','inner');
        $this->db->join('course_subjects','course_subjects.course_subject_id=assignments.subject_id','inner');
        $this->db->join('assignment_results','assignment_results.assignment_id=assignments.assignment_id','inner');
        $this->db->join('students','students.student_id=assignment_results.student_id','inner');
        $this->db->join('classes','students.class_id=classes.class_id','inner');
        $this->db->join('campuses','campuses.campus_id=classes.campus_id','inner');
        if($this->session->userdata('role')!='Admin')
        {
            $this->db->where_in('assignments.subject_id', $subject_ids);
        }
        if($this->session->userdata('role')!='Admin')
        {
            $this->db->where_in('campuses.campus_id', $campus_ids);
        }
        $this->db->where('assignment_results.checked',0);

        $data['assignments'] = $this->db->get()->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('dashboard/uncheck_assignments',$data);
        $this->load->view('inc/footer');
    }
	
	public function clear_discount_update($discount_id)
    {
        if (!$this->db->field_exists('approved_by', 'discounts_approval')) {
            $this->db->query("ALTER TABLE discounts_approval ADD approved_by VARCHAR(255) NULL AFTER created_by");
        }
        if (!$this->db->field_exists('approved_at', 'discounts_approval')) {
            $this->db->query("ALTER TABLE discounts_approval ADD approved_at DATETIME NULL AFTER approved_by");
        }

        $update_request = $this->db->get_where('discounts_approval',
            array('id'=>$discount_id))->result_array();

		$discamount=$update_request[0]['discount'];


        $this->db->set('status',1);
        $this->db->set('approved_by', $this->session->userdata('name'));
        $this->db->set('approved_at', date('Y-m-d H:i:s'));
        $this->db->where(array('id'=>$discount_id));
        $this->db->update('discounts_approval');
		
		$this->db->set('total_fee', 'total_fee -' . $discamount . '', false);
        $this->db->where(array('student_id'=>$update_request[0]['student_id']));
        $this->db->update('students');

        $this->db->select('*');
        $this->db->from('payments');
        $this->db->where('student_id', $update_request[0]['student_id']);
        $this->db->where(array('paid'=>0,'payment_plan!='=>'consulation fee'));
        $this->db->order_by('id','desc');
        $query = $this->db->get()->result_array();

        

        foreach ($query as $payments){

            $paymentamount=$payments['amount'];
            if ($discamount>0) {
                if (($discamount - $paymentamount) >= 0) {

                    $this->db->where('id', $payments['id']);
                    $this->db->delete('payments');
                    $discamount = $discamount - $paymentamount;

                } else {

                    $newamount = $paymentamount - $discamount;
                    $this->db->set('amount', $newamount);
                    $this->db->where('id', $payments['id']);
                    $this->db->update('payments');
                    $discamount = $discamount - $paymentamount;

                }
            }


        }


        $this->dashboards->updateClearPayment($update_request[0]['id']);
        $this->session->set_flashdata('message', 'Discount Updated successfully.');
        redirect('dashboard/discount_fee_status');

    }
	public function discount_fee_status($campus_id=NULL)
    {
        $this->db->select('*');
        $this->db->from('discounts_approval');
        $this->db->join('students','students.student_id = discounts_approval.student_id','left');
        $this->db->join('classes', 'classes.class_id=students.class_id', 'left');
        $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
        $this->db->join('courses','courses.course_id=classes.course_id','left');
        $this->db->where('discounts_approval.status = 0');


        $data['fee_requests'] = $query = $this->db->get()->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('dashboard/update_discount_status', $data);
        $this->load->view('inc/footer');
    }
	public function reject_discount_fee_update($fee_id)
    {
        $this->db->set('status','2');
        $this->db->where('id',$fee_id);
        $this->db->update('discounts_approval');

        $this->session->set_flashdata('message', 'Fee Request Rejected.');
        redirect('dashboard/discount_fee_status');
    }

	public function fee_reversal_requests()
	{
		$this->db->select('*');
		$this->db->from('payments_reversal_requests');
		$this->db->where('done',0);
		$data['fee_reversal_requests']=$this->db->get()->result_array();


		$from_date = $this->input->post('from_date');
		$to_date = $this->input->post('to_date');

		if($from_date!='' && $to_date!='')
		{
			$data['from_date'] = $from_date;
			$data['to_date'] = $to_date;
		}
		else
		{
			$data['from_date'] = $from_date = date('Y-m-01');
			$data['to_date'] = $to_date = date('Y-m-t');
		}

		$this->db->select('*');
		$this->db->from('payments_reversal_requests');
		$this->db->where(array('status!='=>0,'done'=>1,'created_at>'=>$from_date.' 00:00:00','created_at<'=>$to_date.' 23:59:59'));
		$data['fee_reversal_approved_requests']=$this->db->get()->result_array();

		$this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('dashboard/fee_reversal_requests', $data);
        $this->load->view('inc/footer');
	}

	public function update_fee_reversal_request($payments_reversal_request_id,$approve_status)
	{
		$this->db->set('approve_status',$approve_status);
		$this->db->set('status',1);
		$this->db->where('payments_reversal_request_id',$payments_reversal_request_id);
		$this->db->update('payments_reversal_requests');

		$this->session->set_flashdata('message', 'Fee Reversal Request Updated.');
        redirect('dashboard/fee_reversal_requests');
	}

	public function delete_fee_reversal_request($payments_reversal_request_id)
	{
		$this->db->where(array('payments_reversal_request_id'=>$payments_reversal_request_id,'approve_status'=>0));
		$this->db->delete('payments_reversal_requests');

		$this->session->set_flashdata('message', 'Fee Reversal Request Deleted.');
        redirect('dashboard/fee_reversal_requests');
	}

	public function getPaymentDetails()
	{
		$payment_id = $this->input->post('payment_id');

		$this->db->select('*');
		$this->db->from('payments');
		$this->db->join('students','students.student_id=payments.student_id','inner');
		$this->db->where('payments.id',$payment_id);
		$fee_details = $this->db->get()->result_array();
		
		$html='';
		$html.='<div class="col-md-12"><strong>Student Name: </strong>'.$fee_details[0]['first_name'].' '.$fee_details[0]['last_name'].'</div>';
		
			if($fee_details[0]['paid']==1):
				
				$html.='<div class="col-md-12"><strong>Paid Campus : </strong> '.@$this->db->get_where('campuses',array('campus_id'=>$fee_details[0]['submitted_fee_campus_id']))->row()->campus_name.'</div>';
				$html.='<div class="col-md-12"><strong>Paid Amount : </strong> '.$fee_details[0]['actual_amount'].'</div>';
				
				if($fee_details[0]['shifted_installment']>0):
					$html.='<div class="col-md-12"><strong>Shifted Previous Installment Amount : </strong>'.$fee_details[0]['shifted_installment'].'</div>';
				endif;

				if($fee_details[0]['shifted_previous_fine']>0):
					$html.='<div class="col-md-12"><strong>Shifted Previous Installment Fine : </strong>'.$fee_details[0]['shifted_previous_fine'].'</div>';
				endif;
					
				if($fee_details[0]['shifted_fine']>0):
					$html.='<div class="col-md-12"><strong>Shifted Current Installment Fine : </strong>'.$fee_details[0]['shifted_fine'].'</div>';
				endif;
				
				if($fee_details[0]['removed_previous_fine']>0):
					$html.='<div class="col-md-12"><strong>Removed Previous Installment Fine : </strong>'.$fee_details[0]['removed_previous_fine'].'</div>';
				endif;

				if($fee_details[0]['removed_fine']>0):
					$html.='<div class="col-md-12"><strong>Removed Current Installment Fine : </strong>'.$fee_details[0]['removed_fine'].'</div>';
				endif;
			
				$html.='<div class="col-md-12"><strong>Paid Date : </strong>'.$fee_details[0]['paid_date'].'</div>';
				$html.='<div class="col-md-12"><strong>Paid Date System : <strong>'.$fee_details[0]['updated_at'].'</div>';
				$html.='<div class="col-md-12"><strong>Fee Pay Through : </strong>'.$fee_details[0]['fee_pay_through'].'</div>';
				
				if($fee_details[0]['fee_pay_through']=='bank'):
					$html.='<div class="col-md-12"><strong>Bank : </strong>'.$fee_details[0]['bank_details'].'</div>';
					$html.='<div class="col-md-12"><strong>Bank Challan / TID No. : </strong>'.$fee_details[0]['tid_no'].'</div>';
					$html.='<div class="col-md-12"><strong>Merged against Challan : </strong>'.$fee_details[0]['paid_challans'].'</div>';
				endif;
				
				if($fee_details[0]['fee_pay_through']=='college' && $fee_details[0]['fee_submit_type']=='receipt_book'):
					$html.='<div class="col-md-12"><strong>Pad of : </strong>'.@$this->db->get_where('campuses',array('campus_id'=>$fee_details[0]['submitted_fee_campus_id']))->row()->campus_name.'</div>';
					$html.='<div class="col-md-12"><strong>Book No. : </strong>'.$fee_details[0]['book_no'].'</div>';
					$html.='<div class="col-md-12"><strong>Receipt No. : </strong>'.$fee_details[0]['receipt_no'].'</div>';
					$html.='<div class="col-md-12"><strong>Merged against Challan : </strong>'.$fee_details[0]['paid_challans'].'</div>';
				endif;
				
				if($fee_details[0]['fee_pay_through']=='college' && $fee_details[0]['fee_submit_type']=='computer_challan'):
					$html.='<div class="col-md-12"><strong>Pay by : </strong>Computer Challan</div>';
					$html.='<div class="col-md-12"><strong>Merged against Challan : </strong>'.$fee_details[0]['paid_challans'].'</div>';
				endif;
				$html.='<div class="clearfix"></div>';
				$html.='<br />';
				
				if($fee_details[0]['scan_challan']=='')
				{

				}
				elseif($fee_details[0]['scan_challan']!='' )
				{
					if($fee_details[0]['online_scan_challan']!='')
					{
						echo '<a href="'.str_replace($bucket_address,$cloudfront_address,$fee_details[0]['online_scan_challan']).'" target="_blank" class="btn purple pull-left">See Challan</a>';
					}
					else
					{
						echo '<a href="'.base_url().'uploads/'.$fee_details[0]['scan_challan'].'" target="_blank" class="btn purple pull-left">See Challan</a> <br />';
					}
					
				}
				
				if($fee_details[0]['fee_pay_through']=='college' && $fee_details[0]['fee_submit_type']=='computer_challan')
				{
					echo '<a href="'.site_url().'/students/print_college_challan/'.$fee_details[0]['id'].'" target="_blank" class="btn blue college_fee_1"><i class="fa fa-print"></i> See Challan</a> <br />';
				}

				if($fee_details[0]['fine_application']=='' && $fee_details[0]['paid']==0)
				{

				}
				else if($fee_details[0]['fine_application']!='' && $fee_details[0]['paid']==1)
				{
					if($fee_details[0]['online_fine_application']!='')
					{
						echo '<a href="'.str_replace($bucket_address,$cloudfront_address,$fee_details[0]['online_fine_application']).'" target="_blank" class="btn purple pull-left">See Application</a> <br />';
					}
					else
					{
						echo '<a href="'.base_url().'uploads/'.$fee_details[0]['fine_application'].'" target="_blank" class="btn purple pull-left">See Application</a> <br />';
					}
				}
				else
				{

				}
				$html.='<div class="clearfix"></div>';
				$html.='<div class="col-md-12"><strong>Fee Add By : </strong>'.$fee_details[0]['add_by'].'</div>';
                $html.='<div class="col-md-12"><strong>Fee Last Edit : </strong>'.$fee_details[0]['last_edit'].'</div>';
				$html.='<div class="col-md-12"><strong>Fee Submitted By : </strong>'.$fee_details[0]['paid_by'].'</div>';
				$html.='<div class="col-md-12"><strong>Fee Cleared By : </strong>'.$fee_details[0]['clear_by'].'</div>';
			endif;

		echo $html;
	}

	public function insertTopicStudied()
	{
		$topic_id = $this->input->post('topic_id');
		$session_syllabus_id = $this->input->post('session_syllabus_id');
		$is_quiz = $this->input->post('is_quiz');
		$created_by = $this->session->userdata('name');

		$this->db->set('topic_id',$topic_id);
		$this->db->set('session_syllabus_id',$session_syllabus_id);
		$this->db->set('is_quiz',$is_quiz);
		$this->db->set('created_by',$created_by);
		$this->db->insert('study_by_teacher');
	}

	public function deleteTopicStudied()
	{
		$topic_id = $this->input->post('topic_id');
		$session_syllabus_id = $this->input->post('session_syllabus_id');
		$is_quiz = $this->input->post('is_quiz');

		$this->db->where(array('topic_id'=>$topic_id,'session_syllabus_id'=>$session_syllabus_id,'is_quiz'=>$is_quiz));
		$this->db->delete('study_by_teacher');
	}

	public function insertPracticalStudied()
	{
		$practical_id = $this->input->post('practical_id');
		$session_syllabus_id = $this->input->post('session_syllabus_id');
		$is_quiz = $this->input->post('is_quiz');
		$created_by = $this->session->userdata('name');

		$this->db->set('practical_id',$practical_id);
		$this->db->set('session_syllabus_id',$session_syllabus_id);
		$this->db->set('is_quiz',$is_quiz);
		$this->db->set('created_by',$created_by);
		$this->db->insert('study_by_teacher');
	}

	public function deletePracticalStudied()
	{
		$practical_id = $this->input->post('practical_id');
		$session_syllabus_id = $this->input->post('session_syllabus_id');
		$is_quiz = $this->input->post('is_quiz');

		$this->db->where(array('practical_id'=>$practical_id,'session_syllabus_id'=>$session_syllabus_id,'is_quiz'=>$is_quiz));
		$this->db->delete('study_by_teacher');
	}
}
