<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Rules extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('clas');	
	}
	
	public function campus_rules()
	{
		$data['campuses'] = $this->db->get('campuses')->result_array();
		$data['courses'] = $this->db->get('courses')->result_array();
		$data['campus_rules'] = $this->db->get('campus_rules')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('rules/campus_rule',$data);
		$this->load->view('inc/footer');
	}
	
	public function edit_campus_rule($campus_rule_id)
	{
		$data['campuses'] = $this->db->get('campuses')->result_array();
		$data['courses'] = $this->db->get('courses')->result_array();
		$data['campus_rule'] = $this->db->get_where('campus_rules',array('campus_rule_id'=>$campus_rule_id))->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('rules/edit_campus_rule',$data);
		$this->load->view('inc/footer');
	}
	
	public function getCourseTimings()
	{
		
		$course_ids = $this->input->post('course_ids');
		if($course_ids==NULL)
		{
			exit();
		}
		$html='';
		$i=1;
		foreach($course_ids as $course_id)
		{
			$course_name = $this->db->get_where('courses',array('course_id'=>$course_id))->row()->course_name;
			$html.='<div class="form-group"><label class="col-md-3 control-label">'.$course_name.' Timing</label><div class="col-md-9"><div class="col-md-5"><div class="input-icon"><i class="fa fa-clock-o"></i><input type="text" name="start_time['.$i.'][]" class="form-control timepicker timepicker-default"></div><span class="help-inline">Start Time</span></div><div class="col-md-5"><div class="input-icon"><i class="fa fa-clock-o"></i><input type="text" name="end_time['.$i.'][]" class="form-control timepicker timepicker-default"></div><span class="help-inline">End Time</span></div><div class="col-md-2"><button type="button" class="btn green add_more_time" data-order="'.$i.'"><i class="fa fa-plus"></i></button></div></div></div><div class="more_timing_'.$i.'"></div>';
			$i++;
		}
		echo $html;
		exit();
	}
	
	public function add_campus_rules()
	{
		$campus_id = $this->input->post('campus_id');
		$campus_property = $this->input->post('campus_property');
		$campus_property_rent = $this->input->post('campus_property_rent');
		$campus_property_rent_increase_after = $this->input->post('campus_property_rent_increase_after');
		$campus_property_rent_increase_percentage = $this->input->post('campus_property_rent_increase_percentage');
		$campus_property_rent_increase_month = $this->input->post('campus_property_rent_increase_month');
		$bank_fee = $this->input->post('bank_fee');
		$college_fee = $this->input->post('college_fee');
		$course_ids = implode(',',$this->input->post('course_ids'));
		$start_time = json_encode($this->input->post('start_time'));
		$end_time = json_encode($this->input->post('end_time'));
		$bank_name = implode(',',$this->input->post('bank_name'));
		$account_title = implode(',',$this->input->post('account_title'));
		$account_number = implode(',',$this->input->post('account_number'));
		
		$this->db->set('campus_id',$campus_id);
		$this->db->set('campus_property',$campus_property);
		$this->db->set('campus_property_rent',$campus_property_rent);
		$this->db->set('campus_property_rent_increase_after',$campus_property_rent_increase_after);
		$this->db->set('campus_property_rent_increase_percentage',$campus_property_rent_increase_percentage);
		$this->db->set('campus_property_rent_increase_month',$campus_property_rent_increase_month);
		$this->db->set('bank_fee',$bank_fee);
		$this->db->set('college_fee',$college_fee);
		$this->db->set('course_ids',$course_ids);
		$this->db->set('start_time',$start_time);
		$this->db->set('end_time',$end_time);
		$this->db->set('bank_name',$bank_name);
		$this->db->set('account_title',$account_title);
		$this->db->set('account_number',$account_number);
		
		$check_campus = $this->db->get_where('campus_rules',array('campus_id'=>$campus_id))->result_array();
		if(count($check_campus)>0)
		{
			$this->db->where('campus_id',$campus_id);
			$this->db->update('campus_rules');
		}
		else
		{
			$this->db->insert('campus_rules');
		}
		
		$this->session->set_flashdata('message','Campus Rules Updated Successfully.');
		redirect('rules/campus_rules');
	}
	
	public function account_rules()
	{
		$data['campuses'] = $this->db->get('campuses')->result_array();
		$data['courses'] = $this->db->get('courses')->result_array();
		$data['campus_rules'] = $this->db->get('campus_rules')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('rules/campus_rule',$data);
		$this->load->view('inc/footer');
	}
	
	public function fee_rules($id = NULL)
	{
		$data['courses'] = $this->db->get('courses')->result_array();

		if ($id != null){

            $ruls=$this->db->get_where('fee_rules',array('fee_rule_id'=>$id))->result_array();

            $data['feerule']=$ruls[0];

        }
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('rules/fee_rule',$data);
		$this->load->view('inc/footer');
	}

    public function extra_fee_rules()
	{
		$data['campuses'] = $this->db->get('campuses')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('rules/extra_fee_rule',$data);
		$this->load->view('inc/footer');
	}

	public function fee_rules_status($id,$status)
	{
		$this->db->set("status",$status);
		$this->db->where("fee_rule_id" , $id);
		$this->db->update("fee_rules");

        redirect('rules/all_fee_rules');
	}

	public function all_fee_rules()
	{

        $this->db->select('*,fee_rules.status as status,fee_rules.total_fee as total_fee');
        $this->db->from('fee_rules');
        $this->db->join('courses','courses.course_id = fee_rules.course_id','left');
        $data['plans'] = $this->db->get()->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('rules/all_feerules', $data);
        $this->load->view('inc/footer');
	}

	public function getAllCourse()
	{
		$courses = $this->db->get('courses')->result_array();
		$html='';
		$html.='<option value="">Select Course</option>';
		foreach($courses as $course)
		{
			$html.='<option value="'.$course['course_id'].'">'.$course['course_name'].'</option>';
		}
		echo $html;
		exit();
	}
	
	public function getAllClasses()
	{
		$campus_id = $this->input->post('campus_id');
		$course_id = $this->input->post('course_id');
		
		$classes = $this->db ->group_by('classes.session')->get_where('classes',array('course_id'=>$course_id))
           ->result_array();
		$html='';
		$html.='<option value="">Select Class</option>';
		foreach($classes as $class)
		{
			$html.='<option value="'.$class['session'].'"> '.$class['session'] .' </option>';
		}
		echo $html;
		exit();
	}

    public function add_fee_rules()
    {
        $class_id = $this->input->post('class_id');
        $course_id = $this->input->post('course_id');
        $fee_rule_type = $this->input->post('fee_rule_type');
        $last_date = $this->input->post('last_date');
        $total_fee = $this->input->post('total_fee');
        $installment_on_admission = $this->input->post('installment_on_admission');
        $per_installment_fee = $this->input->post('per_installment_fee');
        $difference_in_installments_months = $this->input->post('difference_in_installments_months');
        $paid_date_each_installment = $this->input->post('paid_date_each_installment');
        $late_fee_per_day_fine = $this->input->post('late_fee_per_day_fine');
        $holiday_fine_remove = $this->input->post('holiday_fine_remove');
        $council_board_fee = $this->input->post('council_board_fee');
        $no_of_installment = $this->input->post('no_of_installment');
        $disc_per_inst = $this->input->post('disc_per_inst');
        $max_inst = $this->input->post('max_install_extend');
        $max_discount = $this->input->post('max_discount');
        //$max_discount_no = $this->input->post('max_discount_no');
        $max_discount_merge = $this->input->post('max_discount_merge');
        $max_incentive_no = $this->input->post('max_incentive_no');


        if($council_board_fee=='Yes')
        {
            $first_time_council_fee = $this->input->post('first_time_council_fee');
            $last_date_council_fee = $this->input->post('last_date_council_fee');
        }
        $session = $this->db->get_where("classes",array("session"=>$class_id,"course_id"=>$course_id))->row_array();
        $exam_sequence = $this->db->get_where("exam_sequence",array("course_id"=>$course_id , "first_year" => $session['exam_no'],"class" => 1,"status"=>"Active"))->row_array();
        $fee_rul = $this->db->order_by('from_date','ASC')->get_where("council_sequence_fee_rules","exam_sequence_id = ".$exam_sequence['id'])->row_array();
        

        $this->db->set('fee_rule_type',$fee_rule_type);
        $this->db->set('last_date',$last_date);
        $this->db->set('total_fee',$total_fee);
        $this->db->set('installment_on_admission',$installment_on_admission);
        $this->db->set('per_installment_fee',$per_installment_fee);
        $this->db->set('difference_in_installments_months',$difference_in_installments_months);
        $this->db->set('paid_date_each_installment',$paid_date_each_installment);
        $this->db->set('late_fee_per_day_fine',$late_fee_per_day_fine);
        $this->db->set('holiday_fine_remove',$holiday_fine_remove);
        $this->db->set('council_board_fee', $fee_rul ? 'Yes' : 'No');
        $this->db->set('no_of_installments',$no_of_installment);
        $this->db->set('disc_per_inst',$disc_per_inst);
        $this->db->set('max_install_extend',$max_inst);
        $this->db->set('max_discount',$max_discount);
        //$this->db->set('max_discount_no',$max_discount_no);
        $this->db->set('max_comision',$max_incentive_no);
        $this->db->set('max_discount_merge',$max_discount_merge);
        $this->db->set('freeze_amount',$this->input->post('freeze_fee'));


        if($council_board_fee=='Yes')
        {
            $this->db->set('first_time_council_fee',$fee_rul['exam_fee']);
            $this->db->set('last_date_council_fee',$last_date_council_fee);
        }
        else
        {
            $this->db->set('first_time_council_fee','');
            $this->db->set('last_date_council_fee','0000-00-00');
        }

        if ($this->input->post('rule_id')!=''){

            $this->db->where('fee_rule_id', $this->input->post('rule_id'));
            $this->db->update('fee_rules');


        }else{

            $this->db->set('session',$class_id);
            $this->db->set('course_id',$course_id);

            $this->db->insert('fee_rules');

        }


        $this->session->set_flashdata('message','Fee Rules Updated Successfully.');
        redirect('rules/fee_rules');
    }

    public function online_study_rules()
	{
		$data['campuses'] 	= $this->db->get('campuses')->result_array();
		$data['courses'] 	= $this->db->get('courses')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('rules/online_study_rules',$data);
		$this->load->view('inc/footer');
	}

	public function closing_rules()
    {

        
        $data['campuses'] = $this->db->get('campuses')->result_array();
        $data['accounts'] = $this->db->get_where('accounts',array('id >'=>'0'))->result_array();


        $this->db->select('*');
        $this->db->from('college_closing_rules');
        $this->db->join('campuses','campuses.campus_id = college_closing_rules.campus_id','left');
        $this->db->join('accounts','accounts.id = college_closing_rules.account_id','left');
        $data['closing_rules'] = $this->db->get()->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('rules/closing_rules',$data);
        $this->load->view('inc/footer');
    }

    public function add_closing_rule()
    {
        $campus_id = $this->input->post('campus_id');
        $account_id = $this->input->post('account_id');


        $this->db->set('campus_id',$campus_id);
        $this->db->set('account_id',$account_id);


        $check_campus = $this->db->get_where('college_closing_rules',array('campus_id'=>$campus_id))->result_array();
        if(count($check_campus)>0)
        {
            $this->db->where('campus_id',$campus_id);
            $this->db->update('college_closing_rules');
        }
        else
        {
            $this->db->insert('college_closing_rules');
        }

        $this->session->set_flashdata('message','Closing Rule Updated Successfully.');
        redirect('rules/closing_rules');
    }
	
	public function council_rules()
    {

        $data['feerule'] = $this->db->get('council_rules')->result_array();
        $data['feerule'] = $data['feerule'][0];

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('rules/council_rule',$data);
        $this->load->view('inc/footer');
    }
    
    public function add_council_fee_rules()
    {
        $total_fee = $this->input->post('total_fee');
        $no_of_exams = $this->input->post('no_of_exams');
        $min_council_fee = $this->input->post('min_council_fee');
        $max_council_fee = $this->input->post('max_council_fee');

        $this->db->set('total_fee',$total_fee);
        $this->db->set('no_of_exams',$no_of_exams);
        $this->db->set('min_council_fee',$min_council_fee);
        $this->db->set('max_council_fee',$max_council_fee);
        $this->db->where('id','1');
        $this->db->update('council_rules');
		
		$this->session->set_flashdata('message','Coucil Rule Updated Successfully.');
        redirect('rules/council_rules');
    }

	public function quiz_rules()
    {

        $data['quiz_rules'] = $this->db->get('quiz_rules')->result_array();
        

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('rules/add_quiz_rules',$data);
        $this->load->view('inc/footer');
    }

	public function add_quiz_rules()
    {

        $mcqs = $this->input->post('mcqs');
        $marks_mcq = $this->input->post('marks_mcq');
        $short_questions = $this->input->post('short_questions');
        $short_question_mcq = $this->input->post('short_question_mcq');
        $practicals = $this->input->post('practicals');
        $marks_practical = $this->input->post('marks_practical');

        $this->db->set('id','1');
        $this->db->set('no_of_mcqs',$mcqs);
        $this->db->set('mark_per_mcqs',$marks_mcq);
        $this->db->set('no_of_short_questions',$short_questions);
        $this->db->set('mark_per_short_question',$short_question_mcq);
        $this->db->set('no_of_practicals',$practicals);
        $this->db->set('mark_per_practicals',$marks_practical);

        $this->db->insert('quiz_rules');
		
		$this->session->set_flashdata('message','Quiz Rule Created Successfully.');
        redirect('rules/quiz_rules');

    }

	public function insert_question_rules()
    {

        $teacher_id = $this->input->post('teacher_id');
        $qty = $this->input->post('qty');

        $this->db->set('teacher_id',$teacher_id);
        $this->db->set('no_of_qst',$qty);

        $this->db->insert('question_rules');

		$this->session->set_flashdata('message','Question Rule Added Successfully.');
        redirect('rules/question_rules');

    }

    public function question_rules()
    {
        $data['users'] = $this->db->join('designations','designations.designation_id = users.designation_id')
            ->join('departments','departments.department_id = users.department_id')->get_where('users','users.status = 1 and departments.department_id = 13')->result_array();
        $data['question_rules'] = $this->db->join('users','users.user_id = question_rules.teacher_id')->get('question_rules')->result_array();


        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('rules/add_question_rules',$data);
        $this->load->view('inc/footer');
    }

    public function delete_question_rule($id)
    {

        $this->db->where("id",$id)->delete('question_rules');

        $this->session->set_flashdata('message','Rule Deleted Successfully.');
        redirect('rules/question_rules');
    }

	public function loan_rules()
    {

        $data['loan_settings'] = $this->db->get('loan_settings')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('rules/loan_rule',$data);
        $this->load->view('inc/footer');
    }

    public function add_loan_rules()
    {
        $max_months = $this->input->post('max_months');
        $max_multiply_salary = $this->input->post('max_multiply_salary');
        $avail_after_join = $this->input->post('avail_after_join');
        $loan_after_months = $this->input->post('loan_after_months');


        $this->db->set('max_months',$max_months);
        $this->db->set('max_multiply_salary',$max_multiply_salary);
        $this->db->set('loan_after_months',$loan_after_months);
        $this->db->set('avail_after_join',$avail_after_join);
        $this->db->where('id','1');

        $this->db->update('loan_settings');

        $this->session->set_flashdata('message','Coucil Rule Updated Successfully.');
        redirect('rules/loan_rules');
    }

    public function admission_rules_regulations()
    {
        $data['admission_rules_regulations'] = $this->db->get('admission_rules_regulations')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('rules/add_admission_rules',$data);
        $this->load->view('inc/footer');
    }

    public function insert_rules_regulations()
    {
        $rules = $this->input->post('data');
        $add_by = $this->input->post('max_months');


        $this->db->set('rules',$rules);
        $this->db->set('created_by',$add_by);
        $this->db->where('id','1');
        $this->db->update('admission_rules_regulations');

        $this->session->set_flashdata('message','Updated Successfully.');
        redirect('rules/admission_rules_regulations');
    }

    public function payment_rules()
    {
        $data['payment_rules'] = $this->db->select("payment_rules.*,course_name")->join("courses","courses.course_id = payment_rules.course_id")->get("payment_rules")->result_array();
        $data['courses'] = $this->db->get("courses")->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('rules/assign_payment_rules', $data);
        $this->load->view('inc/footer');
    }

    public function insert_payment_rule()
    {
        $this->db->set('name', $this->input->post('name'));
        $this->db->set('amount', $this->input->post('amount'));
        $this->db->set('course_id', $this->input->post('course_id'));
        $this->db->set('created_by', $this->input->post('created_by'));
        $this->db->insert('payment_rules');

        $this->session->set_flashdata('message', 'Rule Added Successfully.');
        redirect('rules/payment_rules');
    }

    public function update_payment_rule()
    {
        $this->db->set('name', $this->input->post('name'));
        $this->db->set('amount', $this->input->post('amount'));
        $this->db->set('course_id', $this->input->post('course_id'));
        $this->db->set('created_by', $this->input->post('created_by'));
        $this->db->set('status', $this->input->post('status'));
        $this->db->where("id",$this->input->post('rule_id'));
        $this->db->update('payment_rules');

        $this->session->set_flashdata('message', 'Interview Updated Successfully.');
        redirect('rules/payment_rules');
    }

    public function inventory_rules()
    {
        $data['campuses'] = $this->db->get_where('campuses',array('status'=>1))->result_array();
        $data['exp_categories'] = $this->db->get_where('expense_category', "sub_of is NULL")->result_array();
        $data['default_expense_category_inventory'] = $this->db->get('default_expense_category_inventory')->result_array();
        $data['default_return_category_inventory'] = $this->db->get('default_return_category_inventory')->result_array();
        
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('rules/inventory_rules',$data);
        $this->load->view('inc/footer');
    }

    public function update_inventory_default_campus_rooms()
    {
        $campus_ids = $this->input->post('campus_id');
        $room_ids = $this->input->post('room_id');
        $subroom_ids = $this->input->post('subroom_id');
        
        $total = count($campus_ids);

        for($i=0;$i<$total;$i++)
        {
            $check = $this->db->get_where('default_room_rules',array('campus_id'=>$campus_ids[$i]))->result_array();
            $this->db->set('campus_id',$campus_ids[$i]);
            $this->db->set('room_id',$room_ids[$i]);
            $this->db->set('subroom_id',$subroom_ids[$i]);
            if(count($check)>0)
            {
                $this->db->where('campus_id',$campus_ids[$i]);
                $this->db->update('default_room_rules');
            }
            else
            {
                $this->db->insert('default_room_rules');
            }
        }
        $this->session->set_flashdata('message','Default Room Update SUccessfully.');
        redirect('rules/inventory_rules');
    }

    public function update_inventory_expense_rule()
    {
        $expense_category_id = $this->input->post('expense_category_id')[count($this->input->post('expense_category_id'))-1];

        $check = $this->db->get('default_expense_category_inventory')->result_array();

        if(count($check)>0)
        {
            $this->db->set('expense_category_id',$expense_category_id);
            $this->db->where('id',$check[0]['id']);
            $this->db->update('default_expense_category_inventory');
        }
        else
        {
            $this->db->set('expense_category_id',$expense_category_id);
            $this->db->insert('default_expense_category_inventory');
        }

        $this->session->set_flashdata('message','Default Expense Category Selected.');
        redirect('rules/inventory_rules');
    }

    public function update_inventory_return_rule()
    {
        $expense_category_id = $this->input->post('expense_category_id')[count($this->input->post('expense_category_id'))-1];

        $check = $this->db->get('default_return_category_inventory')->result_array();

        if(count($check)>0)
        {
            $this->db->set('expense_category_id',$expense_category_id);
            $this->db->where('id',$check[0]['id']);
            $this->db->update('default_return_category_inventory');
        }
        else
        {
            $this->db->set('expense_category_id',$expense_category_id);
            $this->db->insert('default_return_category_inventory');
        }

        $this->session->set_flashdata('message','Default Expense Category Selected.');
        redirect('rules/inventory_rules');
    }

    public function backup_rules()
    {
        $data['email'] = $this->db->get_where('backups',array('backup_id'=>1))->row()->email;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('rules/backup_rules',$data);
        $this->load->view('inc/footer');
    }

    public function update_backup_email()
    {
        $email = $this->input->post('email');
        $this->db->set('email',$email);
        $this->db->where('backup_id',1);
        $this->db->update('backups');

        $this->session->set_flashdata('message','Backup email updated successfully.');
        redirect('rules/backup_rules');
    }

    public function free_items()
    {
        $data['campuses'] = $this->db->get_where('campuses',array('status'=>1))->result_array();
        $data['product_names'] = $this->db->get_where('product_names',array('has_sub'=>0,))->result_array();
        $data['rules'] = $this->db->get('free_item_rules')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('rules/free_items',$data);
        $this->load->view('inc/footer');
    }

    public function getCampusClasses()
    {
        $campus_ids = $this->input->post('campus_id');
        $selected_classes = $this->input->post('selected_classes');
        //print_r($campus_ids);
        //exit;
        $this->db->where_in('campus_id',$campus_ids);
        $this->db->where('status',1);
        $classes = $this->db->get('classes')->result_array();

        $html='';
        foreach($classes as $class)
        {
            if(in_array($class['class_id'],$selected_classes))
            {
                $html.='<option value="'.$class['class_id'].'" selected="selected">'.$class['name'].'</option>';
            }
            else
            {
                $html.='<option value="'.$class['class_id'].'">'.$class['name'].'</option>';
            }
        }
        echo $html;
    }

    public function add_free_item_rules()
    {
        $campus_ids = $this->input->post('campus_ids');
        $class_ids = $this->input->post('class_ids');
        $product_name_ids = $this->input->post('product_name_ids');
        $till_date = $this->input->post('till_date');
        $student_admission_date = $this->input->post('student_admission_date');

        $this->db->set('campus_ids',implode(',',$campus_ids));
        $this->db->set('class_ids',implode(',',$class_ids));
        $this->db->set('product_name_ids',implode(',',$product_name_ids));
        $this->db->set('till_date',$till_date);
        $this->db->set('student_admission_date',$student_admission_date);
        $this->db->insert('free_item_rules');

        $this->session->set_flashdata('message','Rule Added successfully.');
        redirect('rules/free_items');
    }

    public function update_free_item_rules($free_item_rule_id)
    {
        $campus_ids = $this->input->post('campus_ids');
        $class_ids = $this->input->post('class_ids');
        $product_name_ids = $this->input->post('product_name_ids');
        $till_date = $this->input->post('till_date');
        $student_admission_date = $this->input->post('student_admission_date');

        $this->db->set('campus_ids',implode(',',$campus_ids));
        $this->db->set('class_ids',implode(',',$class_ids));
        $this->db->set('product_name_ids',implode(',',$product_name_ids));
        $this->db->set('till_date',$till_date);
        $this->db->set('student_admission_date',$student_admission_date);
        $this->db->where('free_item_rule_id',$free_item_rule_id);
        $this->db->update('free_item_rules');

        $this->session->set_flashdata('message','Rule Updated successfully.');
        redirect('rules/free_items');
    }

    public function edit_free_item($free_item_rule_id)
    {
        $data['campuses'] = $this->db->get_where('campuses',array('status'=>1))->result_array();
        $data['product_names'] = $this->db->get_where('product_names',array('has_sub'=>0,))->result_array();
        $data['rules'] = $this->db->get('free_item_rules')->result_array();
        $data['free_item_rule'] = $this->db->get_where('free_item_rules',array('free_item_rule_id'=>$free_item_rule_id))->result_array();
        
        //GET CLASSES OF SELECTED CAMPUS
        $this->db->where_in('campus_id',explode(',',$data['free_item_rule'][0]['campus_ids']));
        $this->db->where('status',1);
        $data['classess'] = $this->db->get('classes')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('rules/edit_free_item',$data);
        $this->load->view('inc/footer');
    }

    public function delete_free_item($free_item_rule_id)
    {
        $this->db->where('free_item_rule_id',$free_item_rule_id);
        $this->db->delete('free_item_rules');

        $this->session->set_flashdata('message','Deleted successfully.');
        redirect('rules/free_items');
    }
    
    public function eligibilty_admission_rules()
    {
        $data['courses'] = $this->db->get_where('courses',array('status'=>1))->result_array();
		
        $this->db->select('*');
        $this->db->from('eligibilty_admission_rules');
        $this->db->join('courses','courses.course_id=eligibilty_admission_rules.course_id','inner');
        $data['rules'] = $this->db->get()->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('rules/eligibilty_admission_rules',$data);
        $this->load->view('inc/footer');
    }

    public function insert_admission_rule()
	{	
	    $course_id = $this->input->post('course_id');
	    $rules = $this->input->post('rule');

	    foreach($rules as $rule)
	    {
	    	$this->db->set('course_id',$course_id);
	    	$this->db->set('rule',$rule);
	    	$this->db->insert('eligibilty_admission_rules');
	    }

	    $this->session->set_flashdata('message','Rules Added Successfully.');
        redirect('rules/eligibilty_admission_rules');
	}

	public function delete_eligibility_admission_rule($eligibilty_admission_rule_id)
    {

        $this->db->where("eligibilty_admission_rule_id",$eligibilty_admission_rule_id)->delete('eligibilty_admission_rules');

        $this->session->set_flashdata('message','Rule Deleted Successfully.');
        redirect('rules/eligibilty_admission_rules');
    }


}