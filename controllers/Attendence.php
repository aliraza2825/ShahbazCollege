<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Attendence extends CI_Controller {

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
		$this->load->model('clas');
	}
	//API CALL FOR HASSAN
	public function teacher()
	{
		$time = date('Y-m-d H:i:s',strtotime($this->input->get('time')));
		$machine_user_id = $this->input->get('machine_user_id');
		$campus_code = $this->input->get('campus_code');
		
		$result = $this->db->get_where('attendence',array('time'=>$time,'machine_user_id'=>$machine_user_id,'campus_code'=>$campus_code))->result_array();
		if(count($result)>0)
		{
			echo 'Result Already Added';
		}
		else
		{
			$this->db->set('time',$time);
			$this->db->set('machine_user_id',$machine_user_id);
			$this->db->set('campus_code',$campus_code);
			$this->db->insert('attendence');
			
			echo 'Success';
		}
	}
	
	public function add_attendence()
	{
		$data['campuses'] = $this->clas->getCampuses();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('attendence/add_attendence',$data);
		$this->load->view('inc/footer');
	}
	
	public function add_attendence_machine()
	{
		$data['campuses'] = $this->clas->getCampuses();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('attendence/add_attendence_machine',$data);
		$this->load->view('inc/footer');
	}
	
	public function getStaffStudents()
	{
		$type = $this->input->post('type');
		$campus_id = $this->input->post('campus_id');
		
		if($type=='staff')
		{
			$this->db->select('users.*,machine_data.machine_id');
			$this->db->from('users');
			$this->db->join('campuses','campuses.campus_id=users.campus_id','inner');
			$this->db->join('machine_data','machine_data.teacher_student_id=users.user_id','inner');
			$this->db->where(array('campuses.campus_id'=>$campus_id,'users.status'=>'1','machine_data.type'=>'teacher'));
			$staffs = $this->db->get()->result_array();
			
			$html='';
			$html.='<option value="">SELECT STAFF</option>';
			foreach($staffs as $staff)
			{
				$html.='<option value="'.$staff['machine_id'].'">'.$staff['first_name'].' '.$staff['last_name'].'</option>';
			}
			echo $html;
		}
		if($type=='student')
		{
			$this->db->select('students.*,machine_data.machine_id');
			$this->db->from('students');
			$this->db->join('classes','classes.class_id=students.class_id','inner');
			$this->db->join('campuses','campuses.campus_id=classes.campus_id','inner');
			$this->db->join('machine_data','machine_data.teacher_student_id=students.student_id','inner');
			$this->db->where(array('students.status'=>'1','machine_data.type'=>'student','students.study_campus'=>$campus_id));
			$students = $this->db->get()->result_array();
			
			$html='';
		
			foreach($students as $student)
			{
				$html.='<option value="'.$student['machine_id'].'">'.$student['first_name'].' '.$student['last_name'].' ('.$student['roll_no'].')</option>';
			}
			echo $html;
		}
	}
	
	public function insert()
	{
		$datetime = $this->input->post('datetime');
		$datetime = explode('-',$datetime);
		$date = date('Y-m-d',strtotime($datetime[0]));
		$time = date('H:i:s',strtotime($datetime[1]));
		
		$attendence_time = $date.' '.$time;
		$campus_id = $this->input->post('campus_id');
		$campus_code = $this->db->get_where('campuses',array('campus_id'=>$campus_id))->row()->campus_code;
		$machine_user_ids = $this->input->post('machine_user_ids');
		
		
		
		$i=0;
		foreach($machine_user_ids as $machine_user_id)
		{
			$this->db->set('time',$attendence_time);
			$this->db->set('machine_user_id',$machine_user_id);
			$this->db->set('campus_code',$campus_code);
			$this->db->set('created_by',$this->session->userdata('name'));
			$this->db->insert('attendence');
		}
		
		$this->session->set_flashdata('message','Attendence Update successfully.');
		redirect('attendence/add_attendence');
	}

	public function delete_attendence($machine_user_id,$date)
	{
		$this->db->where(array('machine_user_id'=>$machine_user_id,'time>='=>$date.' 00:00:00', 'time<='=>$date.' 23:59:59'));
		$this->db->delete('attendence');

		$this->session->set_flashdata('message','Attendence Deleted successfully.');
		redirect('attendence/all_attendence');
	}

	public function halfday($machine_user_id,$date)
	{
		$this->db->set('halfday',1);
		$this->db->where(array('machine_user_id'=>$machine_user_id,'time>='=>$date.' 00:00:00', 'time<='=>$date.' 23:59:59'));
		$this->db->update('attendence');

		$this->session->set_flashdata('message','Attendence Updated successfully.');
		redirect('attendence/all_attendence');
	}
	
	public function insert_machine()
	{
		$name = $this->input->post('machine_id');
		$campus_id = $this->input->post('campus_id');

			$this->db->set('name',$name);
			$this->db->set('campus_id',$campus_id);
			$this->db->insert('attendance_machine');

		$this->session->set_flashdata('message','Attendence Machine Added successfully.');
		redirect('attendence/all_attendence_machines');
	}

	public function update_machine($id)
	{
		$name = $this->input->post('machine_id');
		$campus_id = $this->input->post('campus_id');
		
			$this->db->set('name',$name);
			$this->db->set('campus_id',$campus_id);
			$this->db->where('id',$id);
			$this->db->update('attendance_machine');
		
		$this->session->set_flashdata('message','Attendence Machine Updated successfully.');
		redirect('attendence/all_attendence_machines');
	}
	
	public function all_attendence_machines()
	{
			$this->db->select('*');
			$this->db->from('attendance_machine');
			$this->db->join('campuses','campuses.campus_code=attendance_machine.campus_id','inner');
				
			$data['machines'] = $this->db->get()->result_array();
				
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('attendence/all_attendence_machine',$data);
		$this->load->view('inc/footer');
	}

	public function edit_attendence_machine($id)
	{
			$this->db->select('*');
			$this->db->from('attendance_machine');
			$this->db->where('attendance_machine.id',$id);	
			$data['machine'] = $this->db->get()->row();
				

		$data['campuses'] = $this->clas->getCampuses();
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('attendence/edit_attendence_machine',$data);
		$this->load->view('inc/footer');
	}

	public function all_attendence()
	{
		$data['campuses'] = $this->clas->getCampuses();
		
		if($this->input->post('submit')==1)
		{
			//DATES
			$strDateFrom = $this->input->post('from_date');
			$strDateTo = $this->input->post('to_date');
			$data['dates'] = $this->createDateRangeArray($strDateFrom,$strDateTo);
			//MACHINE IDS
			if(count($this->input->post('machine_user_ids'))<1)
			{
				if($this->input->post('type')=='staff')
				{
					$campus_id = @$this->input->post('campus_id');
					
					$this->db->select('machine_data.*');
					$this->db->from('machine_data');
					$this->db->join('users','users.user_id=machine_data.teacher_student_id and machine_data.type = "teacher"','inner');
					$this->db->join('campuses','campuses.campus_id=users.campus_id','inner');
					$this->db->where(array('users.status'=>1,'machine_data.type'=>'teacher'));
					if($campus_id!='')
					{
						$this->db->where('users.campus_id',$campus_id);
					}
					$users = $this->db->get()->result_array();
					$ids = array();
					foreach($users as $user)
					{
						array_push($ids,$user['machine_id']);
					}
					$data['machine_user_ids'] = $ids;
				}
				elseif($this->input->post('type')=='student')
				{
					$campus_id = @$this->input->post('campus_id');
					$class_session = @$this->input->post('class_session');
					$shift =$this->input->post('shift');
					$study_type =$this->input->post('study_type');
					
					$this->db->select('machine_data.*,classes.session');
					$this->db->from('machine_data');
					$this->db->join('students','students.student_id=machine_data.teacher_student_id','inner');
					$this->db->join('classes','classes.class_id=students.class_id','inner');
					$this->db->join('campuses','campuses.campus_id=classes.campus_id','inner');
					$this->db->where(array('machine_data.type'=>'student','students.status'=>1));
					if($campus_id!='')
					{
						$this->db->where('campuses.campus_id',$campus_id);
					}
					if($class_session!='')
					{
						$this->db->where('classes.session',$class_session);
					}
					if($shift!='')
					{
						$this->db->where_in('students.shift',$shift);
					}
					
					if($study_type!='')
					{
						$this->db->where_in('students.study_type',$study_type);
					}
					$users = $this->db->get()->result_array();
					
					$ids = array();
					foreach($users as $user)
					{
						array_push($ids,$user['machine_id']);
					}
					$data['machine_user_ids'] = $ids;
				}
			}
			else
			{
				$data['machine_user_ids'] = $this->input->post('machine_user_ids');
			}
		}
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('attendence/all_attendence',$data);
		$this->load->view('inc/footer');
	}
	
	public function createDateRangeArray($strDateFrom,$strDateTo)
	{
		$aryRange=array();
	
		$iDateFrom=mktime(1,0,0,substr($strDateFrom,5,2),     substr($strDateFrom,8,2),substr($strDateFrom,0,4));
		$iDateTo=mktime(1,0,0,substr($strDateTo,5,2),     substr($strDateTo,8,2),substr($strDateTo,0,4));
	
		if ($iDateTo>=$iDateFrom)
		{
			array_push($aryRange,date('Y-m-d',$iDateFrom)); // first entry
			while ($iDateFrom<$iDateTo)
			{
				$iDateFrom+=86400; // add 24 hours
				array_push($aryRange,date('Y-m-d',$iDateFrom));
			}
		}
		return $aryRange;
	}
	
	public function getSessions()
	{
		$campus_id = $this->input->post('campus_id');
		$this->db->group_by('session');
		$classes = $this->db->get_where('classes',array('campus_id'=>$campus_id))->result_array();
		
		$html='';
		$html.='<option value="">SELECT SESSION</option>';
		foreach($classes as $class)
		{
			$html.='<option value="'.$class['session'].'">'.$class['session'].'</option>';
		}
		echo $html;
	}

}
