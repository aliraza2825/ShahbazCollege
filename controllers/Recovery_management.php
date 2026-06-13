<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Recovery_management extends CI_Controller {
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
        $this->load->model('dashboards');
        $this->load->model('clas');
        $this->load->model('student');
	}
	
	public function assign_task()
	{	
		$data['campuses'] = $this->db->get_where('campuses',array('status'=>1))->result_array();
		$data['courses'] = $this->student->getCourses();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('recovery_management/assign_task', $data);
		$this->load->view('inc/footer');
	}
	
	public function all_assign_task()
	{	
		$this->db->select('*');
		$this->db->from('recovery_management');
		$this->db->join('designations','recovery_management.designation_id=designations.designation_id','INNER');
		$this->db->join('departments','designations.department_id=departments.department_id','INNER');
		$data['users'] = $this->db->get()->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('recovery_management/all_assign_task', $data);
		$this->load->view('inc/footer');
	}
	
	public function edit_assign_task($recovery_management_id)
	{	
		$data['campuses'] = $this->db->get_where('campuses',array('status'=>1))->result_array();
		
		$this->db->select('*');
		$this->db->from('recovery_management');
		$this->db->join('users','users.user_id=recovery_management.user_id','INNER');
		$this->db->join('campuses','users.campus_id=campuses.campus_id','INNER');
		$this->db->join('staff_type','users.staff_type_id=staff_type.staff_type_id','INNER');
		$this->db->join('departments','users.department_id=departments.department_id','INNER');
		$this->db->join('designations','users.designation_id=designations.designation_id','INNER');
		$this->db->where('recovery_management_id',$recovery_management_id);
		$data['users'] = $this->db->get()->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('recovery_management/edit_assign_task', $data);
		$this->load->view('inc/footer');
	}
	
	public function getDepartments()
	{
		$campus_id = $this->input->post('campus_id');
		$departments = $this->db->get('departments')->result_array();
		
		$html = '';
		$html .= '<option value="">SELECT DEPARTMENT</option>';
		foreach($departments as $department)
		{
			$html .= '<option value="'.$department['department_id'].'">'.$department['department_name'].'</option>';
		}
		echo $html;
	}
	
	public function getDesignations()
	{
		$department_id = $this->input->post('department_id');
		$designations = $this->db->get_where('designations',array('department_id'=>$department_id))->result_array();
		
		$html = '';
		$html .= '<option value="">SELECT DESIGNATION</option>';
		foreach($designations as $designation)
		{
			$html .= '<option value="'.$designation['designation_id'].'">'.$designation['designation_name'].'</option>';
		}
		echo $html;
	}
	
	public function getUsers()
	{
		$campus_id = $this->input->post('campus_id');
		$department_id = $this->input->post('department_id');
		$designation_id = $this->input->post('designation_id');
		
		$users = $this->db->get_where('users',array('campus_id'=>$campus_id,'department_id'=>$department_id,'designation_id'=>$designation_id,'status'=>'1'))->result_array();
		
		$html = '';
		$html .= '<option value="">SELECT USER</option>';
		foreach($users as $user)
		{
			$html .= '<option value="'.$user['user_id'].'">'.$user['first_name'].' '.$user['last_name'].'</option>';
		}
		echo $html;
	}
	
	public function set_comission()
	{
		$designation_id = $this->input->post('designation_id');
        $min_fine_amount = $this->input->post('min_fine_amount');
        $fine_amount_percentage = $this->input->post('fine_amount_percentage');
		$campus_ids = implode(',',$this->input->post('campus_ids'));
		$course_id = $this->input->post('course_id');
		if($course_id!='')
		{
			$course_id =  implode(",", $course_id);
		}
		
		$check = $this->db->get_where('recovery_management',array('designation_id'=>$designation_id))->result_array();
		if(count($check)==0)
		{
			$this->db->set('designation_id',$designation_id);
			$this->db->set('course_id',$course_id);
            $this->db->set('min_fine_amount',$min_fine_amount);
            $this->db->set('fine_amount_percentage',$fine_amount_percentage);
			$this->db->set('campus_ids',$campus_ids);
			$this->db->insert('recovery_management');
			$recovery_management_id = $this->db->insert_id();
		}
		else
		{
			//DELETE PREVIOUS DATA
			$this->db->where('recovery_management_id',$check[0]['recovery_management_id']);
			$this->db->delete('recovery_management_rules');
			$recovery_management_id = $check[0]['recovery_management_id'];
		}
		
		//INSERT NEW RULES DATA
		$from_percentage = $this->input->post('from_percentage');
		$to_percentage = $this->input->post('to_percentage');
		$comission = $this->input->post('comission');
		
		$count = count($comission);
		
		for($i=0;$i<$count;$i++)
		{
			$this->db->set('recovery_management_id',$recovery_management_id);
			$this->db->set('start',$from_percentage[$i]);
			$this->db->set('end',$to_percentage[$i]);
			$this->db->set('comission',$comission[$i]);
			$this->db->insert('recovery_management_rules');
		}
		
		$this->session->set_flashdata('message','Comission Rule Added Successfully.');
		redirect('recovery_management/assign_task');
	}
	
	public function update_comission($recovery_management_id)
	{
		$campus_ids = implode(',',$this->input->post('campus_ids'));
		//UPDATE FINE SECTION
        $min_fine_amount = $this->input->post('min_fine_amount');
        $fine_amount_percentage = $this->input->post('fine_amount_percentage');
        $this->db->set('min_fine_amount',$min_fine_amount);
        $this->db->set('fine_amount_percentage',$fine_amount_percentage);
		$this->db->set('campus_ids',$campus_ids);
        $this->db->where('recovery_management_id',$recovery_management_id);
        $this->db->update('recovery_management');

	    //DELETE PREVIOUS DATA
		$this->db->where('recovery_management_id',$recovery_management_id);
		$this->db->delete('recovery_management_rules');
		
		//INSERT NEW RULES DATA
		$from_percentage = $this->input->post('from_percentage');
		$to_percentage = $this->input->post('to_percentage');
		$comission = $this->input->post('comission');
		
		$count = count($comission);
		
		for($i=0;$i<$count;$i++)
		{
			$this->db->set('recovery_management_id',$recovery_management_id);
			$this->db->set('start',$from_percentage[$i]);
			$this->db->set('end',$to_percentage[$i]);
			$this->db->set('comission',$comission[$i]);
			$this->db->insert('recovery_management_rules');
		}
		
		$this->session->set_flashdata('message','Comission Rules Updated Successfully.');
		redirect('recovery_management/edit_assign_task/'.$recovery_management_id);
	}
	
	public function getUsersComission()
	{
		$user_id = $this->input->post('user_id');
		$recovery_management_id = @$this->db->get_where('recovery_management',array('user_id'=>$user_id))->row()->recovery_management_id;
		
		if($recovery_management_id!=''):		
			$rules = $this->db->get_where('recovery_management_rules',array('recovery_management_id'=>$recovery_management_id))->result_array();
			
			$html = '';
			foreach($rules as $rule)
			{
				$html.='<div class="comission"><div class="row"><div class="col-md-3"><div class="form-group"><label class="col-md-6 control-label">From (%) <span class="required">*</span></label><div class="col-md-6"><input type="text" class="form-control" name="from_percentage[]" placeholder="From %" value="'.$rule['start'].'" required><span class="help-inline"></span></div></div></div><div class="col-md-3"><div class="form-group"><label class="col-md-5 control-label">To (%) <span class="required">*</span></label><div class="col-md-7"><input type="text" class="form-control" name="to_percentage[]" placeholder="To %" value="'.$rule['end'].'" required><span class="help-inline"></span></div></div></div><div class="col-md-3"><div class="form-group"><label class="col-md-5 control-label">Comission per sale <span class="required">*</span></label><div class="col-md-7"><input type="text" class="form-control" name="comission[]" placeholder="Comission" value="'.$rule['comission'].'" required><span class="help-inline"></span></div></div></div><div class="col-md-3"><button type="button" class="btn red remove_line"><i class="fa fa-trash"></i> Remove</button></div></div></div>';
			}
			echo $html;
		endif;
	}
	
	public function delete($recovery_management_id)
	{
		$this->db->where('recovery_management_id',$recovery_management_id);
		$this->db->delete('recovery_management_rules');
		
		$this->db->where('recovery_management_id',$recovery_management_id);
		$this->db->delete('recovery_management');
		
		$this->session->set_flashdata('message','Comission Rule Deleted Successfully.');
		redirect('recovery_management/all_assign_task');
	}

    public function check_recovery($recovery_management_id,$user_id)
    {
		$from_date = @$this->input->post('from_date');
        $to_date = @$this->input->post('to_date');

        if(@$from_date==NULL && @$to_date==NULL)
        {
            $data['from_date']=date('Y-m-01');
            $data['to_date']=date('Y-m-t');
        }
        else {
            $data['from_date']=$from_date;
            $data['to_date']=$to_date;
        }

        //GET RECOVERY DETAILS
        $data['recoveryid'] = $recovery_management_id;
        $data['recovery'] = $this->db->get_where('recovery_management',array('recovery_management_id'=>$recovery_management_id))->result_array();
		$campus_ids = explode(',',$data['recovery'][0]['campus_ids']);
		$course_ids = explode(',',$data['recovery'][0]['course_id']);
		
		$user= $this->db->get_where('users',array('user_id'=>$user_id))->result_array();
        $full_name=$user[0]['first_name'].' '.$user[0]['last_name'];

        //GET USER DETAILS
        $this->db->select('*');
        $this->db->from('users');
        $this->db->join('campuses','campuses.campus_id=users.campus_id','INNER');
        $this->db->join('departments','departments.department_id=users.department_id','INNER');
        $this->db->join('designations','designations.designation_id=users.designation_id','INNER');
        $this->db->where('users.user_id',$user_id);
        $data['user'] = $this->db->get()->result_array();

        //GET ALL UNPAID FEE PAYMENTS DETAILS OF STUDENTS
        $this->db->select('payments.*');
        $this->db->from('payments');
        $this->db->join('students','students.student_id=payments.student_id','INNER');
        $this->db->join('classes','classes.class_id=students.class_id','INNER');
        $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=students.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
		$this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where(array('payments.dead_line<='=>$data['to_date'],'payments.paid'=>0,'students.status'=>1));
        $data['unpaid_payments_students'] = $this->db->get()->result_array();

        //GET ALL UNPAID FEE PAYMENTS DURING LAST MONTH
        $this->db->select('payments.*');
        $this->db->from('payments');
        $this->db->join('students','students.student_id=payments.student_id','INNER');
        $this->db->join('classes','classes.class_id=students.class_id','INNER');
        $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=students.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
		$this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where(array('payments.paid_date>'=>$data['to_date'],'payments.paid'=>1,'students.status'=>1));
        $data['unpaid_payments_students_during_last_month'] = $this->db->get()->result_array();

        //GET ALL UNPAID FEE PAYMENTS DETAILS OF CONTRACTS
        $this->db->select('payments.*');
        $this->db->from('payments');
        $this->db->join('contracts','contracts.contract_id=payments.contract_id','INNER');
        $this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','INNER');
        $this->db->join('campuses','contracts.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=contracts.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
		$this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where(array('payments.dead_line<='=>$data['to_date'],'payments.paid'=>0,'payments.payment_plan Not Like'=>'extra fee','payments.amount !='=>'4500'));
        $data['unpaid_payments_contracts'] = $this->db->get()->result_array();

        //GET ALL UNPAID STUDENTS COUNT
        $this->db->select('payments.*');
        $this->db->from('payments');
        $this->db->join('students','students.student_id=payments.student_id','INNER');
        $this->db->join('classes','classes.class_id=students.class_id','INNER');
        $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=students.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
        $this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where(array('payments.dead_line<='=>$data['to_date'],'payments.paid'=>0,'payments.payment_plan Not like'=>'extra fee','students.status'=>1));
        $this->db->group_by('students.student_id');
        $data['fee_dues_students_count'] = $this->db->get()->result_array();

        //GET ALL UNPAID COUNT CONTRACTS
        $this->db->select('payments.*');
        $this->db->from('payments');
        $this->db->join('contracts','contracts.contract_id=payments.contract_id','INNER');
        $this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','INNER');
        $this->db->join('campuses','contracts.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=contracts.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
        $this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where(array('payments.dead_line<='=>$data['to_date'],'payments.paid'=>0,'payments.payment_plan Not Like'=>'extra fee','payments.amount !='=>'4500'));
        $this->db->group_by('contracts.contract_id');
        $data['fee_dues_contractors_count'] = $this->db->get()->result_array();


        //GET FEE PAYMENTS DETAILS OF STUDENTS
        $this->db->select('payments.*');
        $this->db->from('payments');
        $this->db->join('students','students.student_id=payments.student_id','INNER');
        $this->db->join('classes','classes.class_id=students.class_id','INNER');
        $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=students.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
		$this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where(array('payments.actual_paid_date>='=>$data['from_date'],'payments.actual_paid_date<='=>$data['to_date'],'payments.paid'=>1,'students.status'=>1));
        $data['paid_payments_students'] = $this->db->get()->result_array();


        //GET PAID PAYMENTS COUNT OF STUDENTS
        $this->db->select('payments.*');
        $this->db->from('payments');
        $this->db->join('students','students.student_id=payments.student_id','INNER');
        $this->db->join('classes','classes.class_id=students.class_id','INNER');
        $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=students.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
        $this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where(array('payments.actual_paid_date>='=>$data['from_date'],'payments.actual_paid_date<='=>$data['to_date'],'payments.paid'=>1,'students.status'=>1));
        $this->db->group_by('students.student_id');
        $data['paid_count_students'] = $this->db->get()->result_array();


        //GET FEE PAYMENTS DETAILS OF CONTRACTS
        $this->db->select('payments.*');
        $this->db->from('payments');
        $this->db->join('contracts','contracts.contract_id=payments.contract_id','INNER');
        $this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','INNER');
        $this->db->join('campuses','contracts.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=contracts.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
		$this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where(array('payments.actual_paid_date>='=>$data['from_date'],'payments.actual_paid_date<='=>$data['to_date'],'payments.paid'=>1,'payments.amount !='=>'4500'));
        $data['paid_payments_contracts'] = $this->db->get()->result_array();


        //GET FEE PAID CONTRACTORS COUNT
        $this->db->select('payments.*');
        $this->db->from('payments');
        $this->db->join('contracts','contracts.contract_id=payments.contract_id','INNER');
        $this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','INNER');
        $this->db->join('campuses','contracts.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=contracts.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
        $this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where(array('payments.actual_paid_date>='=>$data['from_date'],'payments.actual_paid_date<='=>$data['to_date'],'payments.paid'=>1,'payments.amount !='=>'4500'));
        $this->db->group_by('contracts.contract_id');
        $data['paid_count_contracts'] = $this->db->get()->result_array();


        //GET SHIFTED PAYMENTS DETAILS OF STUDENTS
        $this->db->select('update_payment_requests.*');
        $this->db->from('update_payment_requests');
        $this->db->join('students','students.student_id=update_payment_requests.student_id','INNER');
        $this->db->join('classes','classes.class_id=students.class_id','INNER');
        $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=students.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
		$this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where(array('update_payment_requests.update_date>='=>$data['from_date'],'update_payment_requests.update_date<='=>$data['to_date'],'update_payment_requests.ok_by_admin'=>1,'update_payment_requests.amount !='=>'4500'));
        $data['shifted_payments_students'] = $this->db->get()->result_array();

        //GET SHIFTED PAYMENTS DETAILS OF CONTRACTS
        $this->db->select('update_payment_requests.*');
        $this->db->from('update_payment_requests');
        $this->db->join('contracts','contracts.contract_id=update_payment_requests.contract_id','INNER');
        $this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','INNER');
        $this->db->join('campuses','contracts.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=contracts.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
		$this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where(array('update_payment_requests.update_date>='=>$data['from_date'],'update_payment_requests.update_date<='=>$data['to_date'],'update_payment_requests.ok_by_admin'=>1,'update_payment_requests.amount !='=>'4500'));
        $data['shifted_payments_contracts'] = $this->db->get()->result_array();

        $this->db->select("payments.id as fee_id,'0' as isdel, 'UnPaid' as Fstatus,payments.split as split,payments.amount, payments.dead_line, payments.paid_challans, payments.merged_challan,payments.challan_no, payments.fine_amount, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee");
        $this->db->from('payments');
        $this->db->join('students','students.student_id=payments.student_id','INNER');
        $this->db->join('classes','classes.class_id=students.class_id','INNER');
        $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=students.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
        $this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where('payments.merged_challan IS NOT NULL');
        $this->db->where(array('payments.actual_paid_date>='=>$data['from_date'],'payments.actual_paid_date<='=>$data['to_date'],'payments.paid'=>1,'students.status'=>1));
        $this->db->group_by("payments.merged_challan");
        $datafine_students = $this->db->get()->result_array();

        $this->db->select("payments.id as fee_id,'0' as isdel, 'UnPaid' as Fstatus,payments.split as split,payments.amount, payments.merged_challan,payments.challan_no, payments.paid_challans, payments.dead_line, payments.fine_amount, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee");
        $this->db->from('payments');
        $this->db->join('students','students.student_id=payments.student_id','INNER');
        $this->db->join('classes','classes.class_id=students.class_id','INNER');
        $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=students.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
        $this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where('payments.merged_challan is null');
        $this->db->where(array('payments.actual_paid_date>='=>$data['from_date'],'payments.actual_paid_date<='=>$data['to_date'],'payments.paid'=>1,'students.status'=>1));
        $this->db->or_where('merged_challan IS not NULL and actual_amount = 0');
        $datapaid_payments_fine_students = $this->db->get()->result_array();
        $data['fine_students'] = array_merge($datafine_students,$datapaid_payments_fine_students);

        //GET PAID UNVERIFIED BANK PAYMENTS COUNT OF STUDENTS
        $this->db->select("payments.id as fee_id,'0' as isdel, 'UnPaid' as Fstatus,payments.split as split,payments.amount, payments.merged_challan,payments.challan_no, payments.paid_challans, payments.dead_line, payments.fine_amount, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee");
        $this->db->from('payments');
        $this->db->join('students','students.student_id=payments.student_id','INNER');
        $this->db->join('classes','classes.class_id=students.class_id','INNER');
        $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=students.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
        $this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where(array('payments.actual_paid_date>='=>$data['from_date'],'payments.actual_paid_date<='=>$data['to_date'],'payments.paid'=>1,'students.status'=>1,'payments.fee_pay_through'=>'bank','payments.clear_by'=>''));
        $this->db->group_by("paid_challans",false);
        $data['unverified_paid_count_students'] = $this->db->get()->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('recovery_management/check_recovery', $data);
        $this->load->view('inc/footer');
    }

    public function fee_dues_comments($recovery_management_id,$filter)
    {
		
		$from_date = @$this->input->post('from_date');
        $to_date = @$this->input->post('to_date');

        if(@$from_date==NULL && @$to_date==NULL)
        {
            $data['from_date']=date('Y-m-01');
            $data['to_date']=date('Y-m-t');
        }
        else
        {
            $data['from_date']=$from_date;
            $data['to_date']=$to_date;
        }
		

        //GET RECOVERY DETAILS
        $data['recovery'] = $this->db->get_where('recovery_management',array('recovery_management_id'=>$recovery_management_id))->result_array();
		$campus_ids = explode(',',$data['recovery'][0]['campus_ids']);
		$course_ids = explode(',',$data['recovery'][0]['course_id']);
		
		

        //GET ALL UNPAID FEE PAYMENTS DETAILS OF STUDENTS
        $this->db->select("payments.id as fee_id,'0' as isdel, 'UnPaid' as Fstatus,payments.amount, payments.dead_line, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee");
        $this->db->from('payments');
        $this->db->join('students','students.student_id=payments.student_id','INNER');
        $this->db->join('classes','classes.class_id=students.class_id','INNER');
        $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=students.course_id','INNER');
        $this->db->where_in('courses.course_id',$course_ids);
        $this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where(array('payments.dead_line<='=>$data['to_date'],'payments.paid'=>0,'students.status'=>1));
        $this->db->group_by('students.student_id');
        $unpaid_payments_students = $this->db->get()->result_array();

        //GET ALL UNPAID FEE PAYMENTS DETAILS OF CONTRACTS
        $this->db->select("*,payments.id as fee_id,'0' as isdel,'UnPaid' as Fstatus,contractors.name,contracts.contract_id,contracts.contract_name");
        $this->db->from('payments');
        $this->db->join('contracts','contracts.contract_id=payments.contract_id','INNER');
        $this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','INNER');
        $this->db->join('campuses','contracts.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=contracts.course_id','INNER');
        $this->db->where_in('courses.course_id',$course_ids);
        $this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where(array('payments.dead_line<='=>$data['to_date'],'payments.paid'=>0));
        $this->db->group_by('contracts.contract_id');
        $unpaid_payments_contracts = $this->db->get()->result_array();
      
        
        
        $data['fee_dues_comments'] = $unpaid_payments_students;
        $data['contracts_fee_dues_comments'] = $unpaid_payments_contracts;
        $data['fee_dues_students_count'] = count($unpaid_payments_students);
        $data['fee_dues_contractors_count'] = count($unpaid_payments_contracts);

        $countcall=0;
        $countwillpayon=0;
        $countwillpay=0;
        $countcelloff=0;
        $countstruckof=0;
        $countnew=0;

         foreach ($data['fee_dues_comments'] as $due) {

            $rem = $this->db->order_by('fees_remarks.fee_remarks_id ', 'desc')->limit(1)->get_where('fees_remarks', array('fee_id' => $due['fee_id']))->result_array();



            $filterd1 = "Call Not Attended";



            $filterd2 = "Will Pay On";


            $filterd3 = "Cell Off";


            $filterd4 = "Struck of now";


            $filterd5 = date("Y-m-d");


             if (count($rem) > 0) {

                if (@strpos($rem[0]['comment'], "$filterd1") !== false) {

                    $countcall++;


                }else if (@strpos($rem[0]['comment'], "$filterd2") !== false && $rem[0]['paid_on_date'] > "$filterd5") {


                    $countwillpay++;

                }else if (@strpos($rem[0]['comment'], "$filterd3") !== false) {

                    $countcelloff++;

                }else if (@strpos($rem[0]['comment'], "$filterd4") !== false){

                    $countstruckof++;

                }else if (@strpos($rem[0]['comment'], "$filterd2") !== false && $rem[0]['paid_on_date'] < "$filterd5") {


                    $countwillpayon++;

                }else{

                    $countnew++;

                }

            }else{

                $countnew++;

            }


        }

        
		
		 foreach ($data['contracts_fee_dues_comments'] as $due) {

            $rem = $this->db->order_by('fees_remarks.fee_remarks_id ', 'desc')->limit(1)->get_where('fees_remarks', array('fee_id' => $due['fee_id']))->result_array();



            $filterd1 = "Call Not Attended";



            $filterd2 = "Will Pay On";


            $filterd3 = "Cell Off";


            $filterd4 = "Struck of now";


            $filterd5 = date("Y-m-d");


            
               if (count($rem) > 0) {

                if (@strpos($rem[0]['comment'], "$filterd1") !== false) {

                    $countcall++;


                }else if (@strpos($rem[0]['comment'], "$filterd2") !== false && $rem[0]['paid_on_date'] > "$filterd5") {


                    $countwillpay++;

                }else if (@strpos($rem[0]['comment'], "$filterd3") !== false) {

                    $countcelloff++;

                }else if (@strpos($rem[0]['comment'], "$filterd4") !== false){

                    $countstruckof++;

                }else if (@strpos($rem[0]['comment'], "$filterd2") !== false && $rem[0]['paid_on_date'] < "$filterd5") {


                    $countwillpayon++;

                }else{

                    $countnew++;

                }

            }else{

                $countnew++;

            }



		 }
		
		
		
		$data['countcall'] = $countcall;
        $data['countwillpay'] = $countwillpay;
        $data['countwillpayon'] = $countwillpayon;
        $data['countcelloff'] = $countcelloff;
        $data['countstruckof'] = $countstruckof;
        $data['countnew'] = $countnew;

        $data['filter'] = $filter;
        $data['recovery_management_id'] = $recovery_management_id;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('dashboard/fee_dues_comments', $data);
        $this->load->view('inc/footer');

    }
	
	public function all_entries($recovery_management_id,$id,$commision,$from_date,$to_date)
    {
        if(@$from_date==NULL && @$from_date=='')
        {
            $data['from_date']=date('Y-m-01');
            $data['to_date']=date('Y-m-t');
        }
        else
        {
            $data['from_date']=$from_date;
            $data['to_date']=$to_date;
        }

        //GET RECOVERY DETAILS
        $data['recoveryid'] = $recovery_management_id;
        $data['recovery'] = $this->db->get_where('recovery_management',array('recovery_management_id'=>$recovery_management_id))->result_array();
        $campus_ids = explode(',',$data['recovery'][0]['campus_ids']);
        $course_ids = explode(',',$data['recovery'][0]['course_id']);

        //GET USER DETAILS
        $this->db->select('*');
        $this->db->from('users');
        $this->db->join('campuses','campuses.campus_id=users.campus_id','INNER');
        $this->db->join('departments','departments.department_id=users.department_id','INNER');
        $this->db->join('designations','designations.designation_id=users.designation_id','INNER');
        $this->db->where('users.user_id',$data['recovery'][0]['user_id']);
        $data['user'] = $this->db->get()->result_array();


        if ($id == 1) {

            //GET ALL UNPAID FEE PAYMENTS DETAILS OF STUDENTS
            $this->db->select("payments.id as fee_id,'0' as isdel, 'UnPaid' as Fstatus,payments.split as split,payments.amount, payments.dead_line, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee,payments.paid_date");
            $this->db->from('payments');
            $this->db->join('students','students.student_id=payments.student_id','INNER');
            $this->db->join('classes','classes.class_id=students.class_id','INNER');
            $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
            $this->db->join('courses','courses.course_id=students.course_id','INNER');
            $this->db->where_in('courses.course_id',$course_ids);
            $this->db->where_in('campuses.campus_id',$campus_ids);
            $this->db->where(array('payments.dead_line<='=>$data['to_date'],'payments.paid'=>0,'students.status'=>1));
            $unpaid_payments_students = $this->db->get()->result_array();

            //GET ALL PAID PAYMENT THAT ARE UNPAID DURING LAST MONTH
            $this->db->select("payments.id as fee_id,'0' as isdel, 'UnPaid' as Fstatus,payments.split as split,payments.amount, payments.dead_line, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee,payments.paid_date");
            $this->db->from('payments');
            $this->db->join('students','students.student_id=payments.student_id','INNER');
            $this->db->join('classes','classes.class_id=students.class_id','INNER');
            $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
            $this->db->join('courses','courses.course_id=students.course_id','INNER');
            $this->db->where_in('courses.course_id',$course_ids);
            $this->db->where_in('campuses.campus_id',$campus_ids);
            $this->db->where(array('payments.paid_date>'=>$data['to_date'],'payments.paid'=>1,'students.status'=>1));
            $unpaid_payments_students_during_last_month = $this->db->get()->result_array();

            //GET ALL UNPAID FEE PAYMENTS DETAILS OF CONTRACTS
            $this->db->select("*,payments.id as fee_id,'0' as isdel,'UnPaid' as Fstatus,payments.split as split");
            $this->db->from('payments');
            $this->db->join('contracts','contracts.contract_id=payments.contract_id','INNER');
            $this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','INNER');
            $this->db->join('campuses','contracts.campus_id=campuses.campus_id','INNER');
            $this->db->join('courses','courses.course_id=contracts.course_id','INNER');
            $this->db->where_in('courses.course_id',$course_ids);
            $this->db->where_in('campuses.campus_id',$campus_ids);
            $this->db->where(array('payments.dead_line<='=>$data['to_date'],'payments.paid'=>0,'payments.amount !='=>'4500'));
            $unpaid_payments_contracts = $this->db->get()->result_array();

            //GET FEE PAYMENTS DETAILS OF STUDENTS
            $this->db->select("payments.id as fee_id,'0' as isdel, 'Paid' as Fstatus,payments.amount,payments.split as split, payments.dead_line, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee,payments.paid_date");
            $this->db->from('payments');
            $this->db->join('students','students.student_id=payments.student_id','INNER');
            $this->db->join('classes','classes.class_id=students.class_id','INNER');
            $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
            $this->db->join('courses','courses.course_id=students.course_id','INNER');
            $this->db->where_in('courses.course_id',$course_ids);
            $this->db->where_in('campuses.campus_id',$campus_ids);
            $this->db->where(array('payments.actual_paid_date>='=>$data['from_date'],'payments.actual_paid_date<='=>$data['to_date'],'payments.paid'=>1,'students.status'=>1));
            $paid_payments_students = $this->db->get()->result_array();


            //GET FEE PAYMENTS DETAILS OF CONTRACTS
            $this->db->select("*,'0' as isdel,payments.id as fee_id,'Paid' as Fstatus,payments.split as split");
            $this->db->from('payments');
            $this->db->join('contracts','contracts.contract_id=payments.contract_id','INNER');
            $this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','INNER');
            $this->db->join('campuses','contracts.campus_id=campuses.campus_id','INNER');
            $this->db->join('courses','courses.course_id=contracts.course_id','INNER');
            $this->db->where_in('courses.course_id',$course_ids);
            $this->db->where_in('campuses.campus_id',$campus_ids);
            $this->db->where(array('payments.actual_paid_date>='=>$data['from_date'],'payments.actual_paid_date<='=>$data['to_date'],'payments.paid'=>1,'payments.amount !='=>'4500'));
            $paid_payments_contracts = $this->db->get()->result_array();

            //GET SHIFTED PAYMENTS DETAILS OF STUDENTS
            $this->db->select("update_payment_requests.add_by,update_payment_requests.last_edit,0 as split,update_payment_requests.del as isdel,update_payment_requests.reason as delreason,update_payment_requests.id as fee_id,'shifted' as Fstatus,update_payment_requests.amount, update_payment_requests.dead_line, update_payment_requests.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee,payments.paid_date");
            $this->db->from('update_payment_requests');
            $this->db->join('payments','payments.challan_no=update_payment_requests.challan_no','left');
            $this->db->join('students','students.student_id=update_payment_requests.student_id','INNER');
            $this->db->join('classes','classes.class_id=students.class_id','INNER');
            $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
            $this->db->join('courses','courses.course_id=students.course_id','INNER');
            $this->db->where_in('courses.course_id',$course_ids);
            $this->db->where_in('campuses.campus_id',$campus_ids);
            $this->db->where(array('update_payment_requests.update_date>='=>$data['from_date'],'update_payment_requests.update_date<='=>$data['to_date'],'update_payment_requests.ok_by_admin'=>1,'del'=>0));
            $shifted_payments_students = $this->db->get()->result_array();

            //GET SHIFTED PAYMENTS DETAILS OF CONTRACTS
            $this->db->select("update_payment_requests.add_by,update_payment_requests.last_edit,0 as split,update_payment_requests.del as isdel,update_payment_requests.reason as delreason,payments.id as fee_id,'shifted' as Fstatus");
            $this->db->from('update_payment_requests');
            $this->db->join('payments','payments.challan_no=update_payment_requests.challan_no','left');
            $this->db->join('contracts','contracts.contract_id=update_payment_requests.contract_id','INNER');
            $this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','INNER');
            $this->db->join('campuses','contracts.campus_id=campuses.campus_id','INNER');
            $this->db->join('courses','courses.course_id=contracts.course_id','INNER');
            $this->db->where_in('courses.course_id',$course_ids);
            $this->db->where_in('campuses.campus_id',$campus_ids);
            $this->db->where(array('update_payment_requests.update_date>='=>$data['from_date'],'update_payment_requests.update_date<='=>$data['to_date'],'update_payment_requests.ok_by_admin'=>1,'payments.amount !='=>'4500','del'=>0));
            $shifted_payments_contracts = $this->db->get()->result_array();
			
			
            $data['fee_dues_comments']=array_merge($unpaid_payments_students,$paid_payments_students,$shifted_payments_students,$unpaid_payments_students_during_last_month);
            $data['contracts_fee_dues_comments']=array_merge($unpaid_payments_contracts,$paid_payments_contracts,$shifted_payments_contracts);

        }


        if ($id == 2) {

            //GET PAID PAYMENTS COUNT OF STUDENTS
            $this->db->select("payments.id as fee_id,'Paid' as Fstatus,payments.amount, payments.dead_line, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee,payments.paid_date");
            $this->db->from('payments');
            $this->db->join('students','students.student_id=payments.student_id','INNER');
            $this->db->join('classes','classes.class_id=students.class_id','INNER');
            $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
            $this->db->join('courses','courses.course_id=students.course_id','INNER');
            $this->db->where_in('courses.course_id',$course_ids);
            $this->db->where_in('campuses.campus_id',$campus_ids);
            $this->db->where(array('payments.paid_date>='=>$data['from_date'],'payments.paid_date<='=>$data['to_date'],'payments.paid'=>1,'students.status'=>1));
           // $this->db->group_by('students.student_id');
            $data['fee_dues_comments'] = $this->db->get()->result_array();


            //GET FEE PAYMENTS DETAILS OF CONTRACTS
            $this->db->select("*,'Paid' as Fstatus,payments.id as fee_id");
            $this->db->from('payments');
            $this->db->join('contracts','contracts.contract_id=payments.contract_id','INNER');
            $this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','INNER');
            $this->db->join('campuses','contracts.campus_id=campuses.campus_id','INNER');
            $this->db->join('courses','courses.course_id=contracts.course_id','INNER');
            $this->db->where_in('courses.course_id',$course_ids);
            $this->db->where_in('campuses.campus_id',$campus_ids);
            $this->db->where(array('payments.paid_date>='=>$data['from_date'],'payments.paid_date<='=>$data['to_date'],'payments.paid'=>1));
            $data['contracts_fee_dues_comments'] = $this->db->get()->result_array();

        }

        elseif ($id == 3) {


            //GET SHIFTED PAYMENTS DETAILS OF STUDENTS
            $this->db->select("update_payment_requests.add_by,update_payment_requests.last_edit,update_payment_requests.del as isdel,update_payment_requests.reason as delreason,payments.id as fee_id,'shifted' as Fstatus,payments.amount, payments.dead_line, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee,payments.paid_date");
            $this->db->from('update_payment_requests');
            $this->db->join('payments','payments.challan_no=update_payment_requests.challan_no','left');
            $this->db->join('students','students.student_id=update_payment_requests.student_id','INNER');
            $this->db->join('classes','classes.class_id=students.class_id','INNER');
            $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
            $this->db->join('courses','courses.course_id=students.course_id','INNER');
            $this->db->where_in('courses.course_id',$course_ids);
            $this->db->where_in('campuses.campus_id',$campus_ids);
            $this->db->where(array('update_payment_requests.old_dead_line>='=>$data['from_date'],'update_payment_requests.old_dead_line<='=>$data['to_date'],'update_payment_requests.ok_by_admin'=>1,'del'=>0));
            $shifted_payments_students = $this->db->get()->result_array();

            //GET SHIFTED PAYMENTS DETAILS OF CONTRACTS
            $this->db->select("update_payment_requests.add_by,update_payment_requests.last_edit,update_payment_requests.del as isdel,update_payment_requests.reason as delreason,payments.id as fee_id,'shifted' as Fstatus,payments.paid_date");
            $this->db->from('update_payment_requests');
            $this->db->join('payments','payments.challan_no=update_payment_requests.challan_no','left');
            $this->db->join('contracts','contracts.contract_id=update_payment_requests.contract_id','INNER');
            $this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','INNER');
            $this->db->join('campuses','contracts.campus_id=campuses.campus_id','INNER');
            $this->db->join('courses','courses.course_id=contracts.course_id','INNER');
            $this->db->where_in('courses.course_id',$course_ids);
            $this->db->where_in('campuses.campus_id',$campus_ids);
            $this->db->where(array('update_payment_requests.old_dead_line>='=>$data['from_date'],'update_payment_requests.old_dead_line<='=>$data['to_date'],'update_payment_requests.ok_by_admin'=>1,'del'=>0));
            $shifted_payments_contracts = $this->db->get()->result_array();


            $data['fee_dues_comments']=$shifted_payments_students;
            $data['contracts_fee_dues_comments']=$shifted_payments_contracts;


        }

        elseif ($id == 4) {


            //GET SHIFTED PAYMENTS DETAILS OF STUDENTS
            $this->db->select("update_payment_requests.add_by,update_payment_requests.last_edit,update_payment_requests.del as isdel,update_payment_requests.reason as delreason,payments.id as fee_id,'shifted' as Fstatus,payments.amount, payments.dead_line, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee,payments.paid_date");
            $this->db->from('update_payment_requests');
            $this->db->join('payments','payments.challan_no=update_payment_requests.challan_no','left');
            $this->db->join('students','students.student_id=update_payment_requests.student_id','INNER');
            $this->db->join('classes','classes.class_id=students.class_id','INNER');
            $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
            $this->db->join('courses','courses.course_id=students.course_id','INNER');
            $this->db->where_in('courses.course_id',$course_ids);
            $this->db->where_in('campuses.campus_id',$campus_ids);
            $this->db->where(array('update_payment_requests.old_dead_line>='=>$data['from_date'],'update_payment_requests.old_dead_line<='=>$data['to_date'],'update_payment_requests.ok_by_admin'=>1,'del'=>0));
            $shifted_payments_students = $this->db->get()->result_array();

            //GET SHIFTED PAYMENTS DETAILS OF CONTRACTS
            $this->db->select("update_payment_requests.add_by,update_payment_requests.last_edit,update_payment_requests.del as isdel,update_payment_requests.reason as delreason,payments.id as fee_id,'shifted' as Fstatus,payments.paid_date");
            $this->db->from('update_payment_requests');
            $this->db->join('payments','payments.challan_no=update_payment_requests.challan_no','left');
            $this->db->join('contracts','contracts.contract_id=update_payment_requests.contract_id','INNER');
            $this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','INNER');
            $this->db->join('campuses','contracts.campus_id=campuses.campus_id','INNER');
            $this->db->join('courses','courses.course_id=contracts.course_id','INNER');
            $this->db->where_in('courses.course_id',$course_ids);
            $this->db->where_in('campuses.campus_id',$campus_ids);
            $this->db->where(array('update_payment_requests.old_dead_line>='=>$data['from_date'],'update_payment_requests.old_dead_line<='=>$data['to_date'],'update_payment_requests.ok_by_admin'=>1,'del'=>0));
            $shifted_payments_contracts = $this->db->get()->result_array();


            $data['fee_dues_comments']=$shifted_payments_students;
            $data['contracts_fee_dues_comments']=$shifted_payments_contracts;


        }




        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('recovery_management/fee_dues_comment_report', $data);
        $this->load->view('inc/footer');

    }
    
    
    public function all_paid_entries($recovery_management_id,$id,$commision,$from_date,$to_date)
    {
        if(@$from_date==NULL && @$from_date=='')
        {
            $data['from_date']=date('Y-m-01');
            $data['to_date']=date('Y-m-t');
        }
        else
        {
            $data['from_date']=$from_date;
            $data['to_date']=$to_date;
        }

        //GET RECOVERY DETAILS
        $data['recoveryid'] = $recovery_management_id;
        $data['recovery'] = $this->db->get_where('recovery_management',array('recovery_management_id'=>$recovery_management_id))->result_array();
        $campus_ids = explode(',',$data['recovery'][0]['campus_ids']);
        $course_ids = explode(',',$data['recovery'][0]['course_id']);

        //GET USER DETAILS
        $this->db->select('*');
        $this->db->from('users');
        $this->db->join('campuses','campuses.campus_id=users.campus_id','INNER');
        $this->db->join('departments','departments.department_id=users.department_id','INNER');
        $this->db->join('designations','designations.designation_id=users.designation_id','INNER');
        $this->db->where('users.user_id',$data['recovery'][0]['user_id']);
        $data['user'] = $this->db->get()->result_array();


        if ($id == 1) {

            //GET ALL PAID PAYMENT THAT ARE UNPAID DURING LAST MONTH
            $this->db->select("payments.id as fee_id,'0' as isdel, 'UnPaid' as Fstatus,payments.split as split,payments.amount, payments.dead_line, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee,payments.paid_date");
            $this->db->from('payments');
            $this->db->join('students','students.student_id=payments.student_id','INNER');
            $this->db->join('classes','classes.class_id=students.class_id','INNER');
            $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
            $this->db->join('courses','courses.course_id=students.course_id','INNER');
            $this->db->where_in('courses.course_id',$course_ids);
            $this->db->where_in('campuses.campus_id',$campus_ids);
            $this->db->where(array('payments.paid_date>'=>$data['to_date'],'payments.paid'=>1,'students.status'=>1));
            $unpaid_payments_students_during_last_month = $this->db->get()->result_array();

            //GET FEE PAYMENTS DETAILS OF STUDENTS
            $this->db->select("payments.id as fee_id,'0' as isdel, 'Paid' as Fstatus,payments.amount,payments.split as split, payments.dead_line, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee,payments.paid_date");
            $this->db->from('payments');
            $this->db->join('students','students.student_id=payments.student_id','INNER');
            $this->db->join('classes','classes.class_id=students.class_id','INNER');
            $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
            $this->db->join('courses','courses.course_id=students.course_id','INNER');
            $this->db->where_in('courses.course_id',$course_ids);
            $this->db->where_in('campuses.campus_id',$campus_ids);
            $this->db->where(array('payments.actual_paid_date>='=>$data['from_date'],'payments.actual_paid_date<='=>$data['to_date'],'payments.paid'=>1,'students.status'=>1));
            $paid_payments_students = $this->db->get()->result_array();


            //GET FEE PAYMENTS DETAILS OF CONTRACTS
            $this->db->select("*,'0' as isdel,payments.id as fee_id,'Paid' as Fstatus,payments.split as split");
            $this->db->from('payments');
            $this->db->join('contracts','contracts.contract_id=payments.contract_id','INNER');
            $this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','INNER');
            $this->db->join('campuses','contracts.campus_id=campuses.campus_id','INNER');
            $this->db->join('courses','courses.course_id=contracts.course_id','INNER');
            $this->db->where_in('courses.course_id',$course_ids);
            $this->db->where_in('campuses.campus_id',$campus_ids);
            $this->db->where(array('payments.actual_paid_date>='=>$data['from_date'],'payments.actual_paid_date<='=>$data['to_date'],'payments.paid'=>1,'payments.amount !='=>'4500'));
            $paid_payments_contracts = $this->db->get()->result_array();
			
			
            $data['fee_dues_comments']=array_merge($paid_payments_students,$unpaid_payments_students_during_last_month); 
            $data['contracts_fee_dues_comments']=array_merge($paid_payments_contracts);

        }


        if ($id == 2) {

            //GET PAID PAYMENTS COUNT OF STUDENTS
            $this->db->select("payments.id as fee_id,'Paid' as Fstatus,payments.amount, payments.dead_line, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee,payments.paid_date");
            $this->db->from('payments');
            $this->db->join('students','students.student_id=payments.student_id','INNER');
            $this->db->join('classes','classes.class_id=students.class_id','INNER');
            $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
            $this->db->join('courses','courses.course_id=students.course_id','INNER');
            $this->db->where_in('courses.course_id',$course_ids);
            $this->db->where_in('campuses.campus_id',$campus_ids);
            $this->db->where(array('payments.paid_date>='=>$data['from_date'],'payments.paid_date<='=>$data['to_date'],'payments.paid'=>1,'students.status'=>1));
           // $this->db->group_by('students.student_id');
            $data['fee_dues_comments'] = $this->db->get()->result_array();


            //GET FEE PAYMENTS DETAILS OF CONTRACTS
            $this->db->select("*,'Paid' as Fstatus,payments.id as fee_id");
            $this->db->from('payments');
            $this->db->join('contracts','contracts.contract_id=payments.contract_id','INNER');
            $this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','INNER');
            $this->db->join('campuses','contracts.campus_id=campuses.campus_id','INNER');
            $this->db->join('courses','courses.course_id=contracts.course_id','INNER');
            $this->db->where_in('courses.course_id',$course_ids);
            $this->db->where_in('campuses.campus_id',$campus_ids);
            $this->db->where(array('payments.paid_date>='=>$data['from_date'],'payments.paid_date<='=>$data['to_date'],'payments.paid'=>1));
            $data['contracts_fee_dues_comments'] = $this->db->get()->result_array();

        }

        elseif ($id == 3) {


        }

        elseif ($id == 4) {


        }




        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('recovery_management/fee_dues_comment_report', $data);
        $this->load->view('inc/footer');

    }

    public function fine_data()
    {
        $data['fine_students'] = $this->input->post('fine_data');
        $data['fine_students']=$data['fine_students'][0];
        $data['fine_students']= json_decode($data['fine_students'][0], true);

//        print_r($data['fine_students']);
//
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('recovery_management/check_fine_data', $data);
        $this->load->view('inc/footer');
    }


}