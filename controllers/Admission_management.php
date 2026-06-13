<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admission_management extends CI_Controller {


    public function __construct()
    {
        parent::__construct();
        $this->load->model('dashboards');
		$this->load->model('student');
        $this->load->model('clas');
    }

    public function assign_task()
    {
        $data['campuses'] = $this->db->get_where('campuses',array('status'=>1))->result_array();
		$data['courses'] = $this->student->getCourses();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('admission_management/assign_admission_task', $data);
        $this->load->view('inc/footer');
    }

    public function all_assign_task()
    {
        $this->db->select('*');
        $this->db->from('admission_management_incentives');
        $this->db->join('designations','designations.designation_id=admission_management_incentives.designation_id','INNER');
		$this->db->join('departments','departments.department_id=designations.department_id','INNER');
        $data['users'] = $this->db->get()->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('admission_management/all_admission_assign_task', $data);
        $this->load->view('inc/footer');
    }

    public function edit_assign_task($Incentive_id)
    {
        $data['campuses'] = $this->db->get_where('campuses',array('status'=>1))->result_array();

        $this->db->select('*');
        $this->db->from('admission_management_incentives');
        $this->db->join('users','users.user_id=admission_management_incentives.user_id','INNER');
        $this->db->join('campuses','users.campus_id=campuses.campus_id','INNER');
        $this->db->join('staff_type','users.staff_type_id=staff_type.staff_type_id','INNER');
        $this->db->join('departments','users.department_id=departments.department_id','INNER');
        $this->db->join('designations','users.designation_id=designations.designation_id','INNER');
        $this->db->where('incentive_id',$Incentive_id);
        $data['users'] = $this->db->get()->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('admission_management/edit_admission_assign_task', $data);
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

        $users = $this->db->get_where('users',array('campus_id'=>$campus_id,'department_id'=>$department_id,'designation_id'=>$designation_id))->result_array();

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
        $course_id = $this->input->post('course_id');
		if($course_id!='')
		{
			$course_id =  implode(",", $course_id);
		}
        $minimum_fee = $this->input->post('minimum_fee');
        $within_days = $this->input->post('within_days');
        $user_or_campus = $this->input->post('admission_type');
        $own_count = $this->input->post('own_count');
        $campus_ids = implode(',',$this->input->post('campus_ids'));


            $this->db->set('designation_id',$designation_id);
            $this->db->set('course_id',$course_id);
            $this->db->set('min_fee_amount',$minimum_fee);
            $this->db->set('with_in_days',$within_days);
            $this->db->set('campus_ids',$campus_ids);
            $this->db->set('user_or_campus',$user_or_campus);
            $this->db->set('own_count',$own_count);
            $this->db->insert('admission_management_incentives');
            $admission_management_id = $this->db->insert_id();
       

        //INSERT NEW RULES DATA
        $from_percentage = $this->input->post('from_percentage');
        $to_percentage = $this->input->post('to_percentage');
        $comission = $this->input->post('comission');

        $count = count($comission);

        for($i=0;$i<$count;$i++)
        {
            $this->db->set('admission_incentive_id',$admission_management_id);
            $this->db->set('start',$from_percentage[$i]);
            $this->db->set('end',$to_percentage[$i]);
            $this->db->set('comission',$comission[$i]);
            $this->db->insert('admission_management_rules');
        }

        $this->session->set_flashdata('message','Comission Rule for Admissions Added Successfully.');
        redirect('admission_management/assign_task');
    }

    public function update_comission($Incentive_id)
    {
        $campus_ids = implode(',',$this->input->post('campus_ids'));
        //UPDATE FINE SECTION

        $this->db->set('campus_ids',$campus_ids);
        $this->db->where('incentive_id',$Incentive_id);
        $this->db->update('admission_management_incentives');

        //DELETE PREVIOUS DATA
        $this->db->where('admission_incentive_id',$Incentive_id);
        $this->db->delete('admission_management_rules');

        //INSERT NEW RULES DATA
        //INSERT NEW RULES DATA
        $from_percentage = $this->input->post('from_percentage');
        $to_percentage = $this->input->post('to_percentage');
        $comission = $this->input->post('comission');

        $count = count($comission);

        for($i=0;$i<$count;$i++)
        {
            $this->db->set('admission_incentive_id',$Incentive_id);
            $this->db->set('start',$from_percentage[$i]);
            $this->db->set('end',$to_percentage[$i]);
            $this->db->set('comission',$comission[$i]);
            $this->db->insert('admission_management_rules');
        }

        $this->session->set_flashdata('message','Comission Rule for Admissions Added Successfully.');
        redirect('admission_management/all_assign_task');;
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

    public function delete($incentive_management_id)
    {
        $this->db->where('admission_incentive_id',$incentive_management_id);
        $this->db->delete('admission_management_rules');

        $this->db->where('incentive_id',$incentive_management_id);
        $this->db->delete('admission_management_incentives');

        $this->session->set_flashdata('message','Comission Rule Deleted Successfully.');
        redirect('admission_management/all_assign_task');
    }

    public function check_recovery($incentive_id,$user_id)
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
        $data['recoveryid'] = $incentive_id;
        $data['recovery'] = $this->db->get_where('admission_management_incentives',array('incentive_id'=>$incentive_id))->result_array();
        $campus_ids = explode(',',$data['recovery'][0]['campus_ids']);


        $user= $this->db->get_where('users',array('user_id'=>$user_id))->result_array();
        $full_name=$user[0]['first_name'].' '.$user[0]['last_name'];


		if($data['recovery'][0]['user_or_campus'] == '0')
		{
			
			 //GET ALL UNPAID FEE PAYMENTS DETAILS OF STUDENTS
				$this->db->select('payments.*,students.*');
				$this->db->from('payments');
				$this->db->join('students','students.student_id=payments.student_id','INNER');
				$this->db->join('classes','classes.class_id=students.class_id','INNER');
				$this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
				$this->db->where(
					array(
						
						'students.status'=>1,
						'students.entry_date>='=>$data['from_date'],
						'students.entry_date<='=>$data['to_date'],
						'students.add_by like'=>'%'.$full_name.'%'
					)
				);
				$this->db->group_by('payments.student_id');
				$data['total_paid_students'] = $this->db->get()->result_array();
				$data['total_unpaid_students'] = array();
			
			
			
		}else
		{
			
			 //GET ALL UNPAID FEE PAYMENTS DETAILS OF STUDENTS
				$this->db->select('payments.*,students.*');
				$this->db->from('payments');
				$this->db->join('students','students.student_id=payments.student_id','INNER');
				$this->db->join('classes','classes.class_id=students.class_id','INNER');
				$this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
				$this->db->where(
					array(
						
						'students.status'=>1,
						'students.entry_date>='=>$data['from_date'],
						'students.entry_date<='=>$data['to_date'],
					)
				);
				$this->db->where_in('campuses.campus_id',$campus_ids);
				
				if($data['recovery'][0]['own_count'] == '0')
				{
					$this->db->where('students.add_by not like "%'.$full_name.'%"');
				}
				
				$this->db->group_by('payments.student_id');
				$data['total_paid_students'] = $this->db->get()->result_array();
				$data['total_unpaid_students'] = array();
									
			
		}

      

        $counted=0;
        $uncounted=0;

        foreach ($data['total_paid_students'] as $paid){


            $this->db->select_sum('payments.actual_amount');
            $this->db->from('payments');
            $this->db->where("payments.student_id = '".$paid['student_id']."'");
            $tot=$this->db->get()->result_array();




            if (count($tot)>0) {
                if ($tot[0]['actual_amount'] > $data['recovery'][0]['min_fee_amount']) {

                    $counted++;

                } else {

                    $uncounted++;

                }
            }

        }

        $data['counted'] = $counted;
        $data['uncounted'] = $uncounted;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('admission_management/check_admission_incentive', $data);
        $this->load->view('inc/footer');


    }

    public function all_entries($recovery_management_id,$id,$user_id,$from_date,$to_date,$incamount)
    {

        

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
        $data['recoveryid'] = $recovery_management_id;
        $data['incamount'] = $incamount;
        $data['recovery'] = $this->db->get_where('admission_management_incentives',array('incentive_id'=>$recovery_management_id))->result_array();
		$campus_ids = explode(',',$data['recovery'][0]['campus_ids']);
		

        //GET USER DETAILS
        $this->db->select('*');
        $this->db->from('users');
        $this->db->join('campuses','campuses.campus_id=users.campus_id','INNER');
        $this->db->join('departments','departments.department_id=users.department_id','INNER');
        $this->db->join('designations','designations.designation_id=users.designation_id','left');
        $this->db->where('users.user_id',$user_id);
        $data['user'] = $this->db->get()->result_array();
		$full_name=$data['user'][0]['first_name'].' '.$data['user'][0]['last_name'];

			
		
		if($data['recovery'][0]['user_or_campus'] == '0')
		{
			
			 //GET ALL UNPAID FEE PAYMENTS DETAILS OF STUDENTS
				$this->db->select("*,classes.name as class_name");
				$this->db->from('students');
				$this->db->join('payments','payments.student_id=students.student_id','INNER');
				$this->db->join('classes','classes.class_id=students.class_id','INNER');
				$this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
				$this->db->join('courses','courses.course_id=students.course_id','INNER');
				$this->db->where(
					array(
						
						'students.status'=>1,
						'students.entry_date>='=>$data['from_date'],
						'students.entry_date<='=>$data['to_date'],
						'students.add_by like'=>'%'.$full_name.'%'
					)
				);
				$this->db->group_by('payments.student_id');
				$data['fee_dues_comments'] = $this->db->get()->result_array();
				
				
				$data['total_unpaid_students'] = array();
			
			
			
			
		}
		else
		{
			
			 //GET ALL UNPAID FEE PAYMENTS DETAILS OF STUDENTS
				$this->db->select("*,classes.name as class_name");
				$this->db->from('students');
				$this->db->join('payments','payments.student_id=students.student_id','left');
				$this->db->join('classes','classes.class_id=students.class_id','left');
				$this->db->join('campuses','classes.campus_id=campuses.campus_id','left');
				$this->db->join('courses','courses.course_id=students.course_id','left');
				$this->db->where(
					array(
						
						'students.status'=>1,
						'students.entry_date>='=>$data['from_date'],
						'students.entry_date<='=>$data['to_date'],
					)
				);
				$this->db->where_in('campuses.campus_id',$campus_ids);
				
				if($data['recovery'][0]['own_count'] == '0')
				{
					$this->db->where('students.add_by not like "%'.$full_name.'%"');
				}
				
				$this->db->group_by('payments.student_id');
				$data['fee_dues_comments'] = $this->db->get()->result_array();
				$data['total_unpaid_students'] = array();
									
			
		}
		


            $data['contracts_fee_dues_comments']=array();


        if ($id == 0) {

           

        }
		if ($id == 1) {
			foreach ($data['fee_dues_comments'] as $key=>$paid){


				$this->db->select_sum('payments.actual_amount');
				$this->db->from('payments');
				$this->db->where("payments.student_id = '".$paid['student_id']."'");
				$tot=$this->db->get()->result_array();




				if (count($tot)>0) {
					if ($tot[0]['actual_amount'] > $data['recovery'][0]['min_fee_amount']) {

						

					} else {

						 unset($data['fee_dues_comments'][$key]);

					}
				}

			}
		}
		if ($id == 2) {
			foreach ($data['fee_dues_comments'] as $key=>$paid){


				$this->db->select_sum('payments.actual_amount');
				$this->db->from('payments');
				$this->db->where("payments.student_id = '".$paid['student_id']."'");
				$tot=$this->db->get()->result_array();




				if (count($tot)>0) {
					if ($tot[0]['actual_amount'] > $data['recovery'][0]['min_fee_amount']) {

						unset($data['fee_dues_comments'][$key]);

					} else {

						

					}
				}

			}
		}



        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('admission_management/fee_dues_comment_report', $data);
        $this->load->view('inc/footer');

    }


}