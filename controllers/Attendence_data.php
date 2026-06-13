<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Attendence_Data extends CI_Controller {
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
		$this->load->model('attendence');	
	}
	
	public function index()
	{
		//$data['staffs'] = $this->db->get('users')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('attendence_data/index');
		$this->load->view('inc/footer');
	}
	
	public function insert()
	{
		$data = $this->input->post();
		$exist = $this->attendence->checkAttendenceData($data);
		if(count($exist)<1)
		{
			$this->attendence->storeAttendenceData($data);
			$this->session->set_flashdata('message', 'Data inserted successfully!');
			redirect('attendence_data');
		}
		else
		{
			$this->session->set_flashdata('error', 'Data already exists!');
			redirect('attendence_data');
		}
	}
	
	public function update()
	{
		$data = $this->input->post();
		$this->attendence->updateAttendenceData($data);
		$this->session->set_flashdata('message', 'Data updated successfully!');
		redirect('attendence_data/edit/'.$this->uri->segment(3));
	}
	
	public function records()
	{
		$type = $this->input->post('type');
		if($type=='student')
		{
			$students = $this->db->get_where('students', array('status'=>'1'))->result_array();
			$students_array = '';
			foreach($students as $student)
			{
				$students_array .= '<option value="'.$student['student_id'].'">'.$student['first_name'].' '.$student['last_name'].' ('.$student['roll_no'].')</option>';
			}
			echo $students_array;
		}
		else if($type=='teacher')
		{
			$teachers = $this->db->get_where('users', array('role'=>'Teacher', 'status'=>'1'))->result_array();
			$teachers_array = '';
			foreach($teachers as $teacher)
			{
				$teachers_array .= '<option value="'.$teacher['user_id'].'">'.$teacher['first_name'].' '.$teacher['last_name'].'</option>';
			}
			echo $teachers_array;
		}
		else if($type=='admin')
		{
			$admins = $this->db->get_where('users', array('role'=>'Admin', 'status'=>'1'))->result_array();
			$admin_array = '';
			foreach($admins as $admin)
			{
				$admin_array .= '<option value="'.$admin['user_id'].'">'.$admin['first_name'].' '.$admin['last_name'].'</option>';
			}
			echo $admin_array;
		}
		else if($type=='principal')
		{
			$principals = $this->db->get_where('users', array('role'=>'Principal', 'status'=>'1'))->result_array();
			$principal_array = '';
			foreach($principals as $principal)
			{
				$principal_array .= '<option value="'.$principal['user_id'].'">'.$principal['first_name'].' '.$principal['last_name'].'</option>';
			}
			echo $principal_array;
		}
		else if($type=='accountant')
		{
			$accountants = $this->db->get_where('users', array('role'=>'Accountant', 'status'=>'1'))->result_array();
			$accountant_array = '';
			foreach($accountants as $accountant)
			{
				$accountant_array .= '<option value="'.$accountant['user_id'].'">'.$accountant['first_name'].' '.$accountant['last_name'].'</option>';
			}
			echo $accountant_array;
		}
		else if($type=='guard')
		{
			$guards = $this->db->get_where('users', array('role'=>'Guard', 'status'=>'1'))->result_array();
			$guard_array = '';
			foreach($guards as $guard)
			{
				$guard_array .= '<option value="'.$guard['user_id'].'">'.$guard['first_name'].' '.$guard['last_name'].'</option>';
			}
			echo $guard_array;
		}
	}
	
	public function users()
	{
		@$type = $this->input->post('type');
		if(@$type)
		{
			$data['users'] = $this->attendence->getUsers($type);
		}
		else
		{
			$data['users']=array();
		}
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('attendence_data/users', $data);
		$this->load->view('inc/footer');
	}
	
	public function students()
	{
		$data['users'] = $this->attendence->getStudents();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('attendence_data/students', $data);
		$this->load->view('inc/footer');
	}
	
	public function delete($id)
	{
		$this->db->where('id', $id);
		$this->db->delete('machine_data');
		$this->session->set_flashdata('message', 'Record deleted successfully');
		redirect('attendence_data/users');
	}
	
	public function edit($id)
	{
		$data['users'] = $this->attendence->getSingleUser($id);
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('attendence_data/edit', $data);
		$this->load->view('inc/footer');
	}
	
	public function staff_manual_attendence()
	{
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('attendence_data/staff_manual_attendence');
		$this->load->view('inc/footer');
	}
	
	public function add_manual_attendence()
	{
		$user_id = $this->input->post('user_id');
		
		
		$check_attendence_data = $this->db->get_where('machine_data', array('teacher_student_id'=>$user_id, 'type!='=>'student'))->result_array();
		if(count($check_attendence_data)>0)
		{
			$machine_id = $check_attendence_data[0]['machine_id'];
			$time = $this->input->post('date').' '.$this->input->post('time');
			$this->db->set('machine_user_id', $machine_id);
			$this->db->set('time', $time);
			$this->db->insert('attendence');
			$this->session->set_flashdata('message', 'Attendence added successfully.');
			redirect(site_url('attendence_data/staff_manual_attendence'));
		}
		else
		{
			$this->session->set_flashdata('error', 'User Machine ID isn\'t exist in database. Kindly add machine ID first.');
			redirect(site_url('attendence_data/staff_manual_attendence'));
		}
	}
	
	public function set_teacher_machine_id()
	{
		$this->db->select('users.*,campuses.campus_code');
		$this->db->from('users');
		$this->db->join('campuses', 'campuses.campus_id=users.campus_id', 'INNER');
		$this->db->where(array('campuses.status'=>1,'campus_code!='=>''));
		$users = $this->db->get()->result_array();
		foreach($users as $user)
		{
			$get_most_larger_machine_id = $this->db->get_where('machine_data',array('campus_id'=>$user['campus_id']))->result_array();
			if(count($get_most_larger_machine_id)<1)
			{
				$this->db->set('teacher_student_id',$user['user_id']);
				$this->db->set('machine_id','01'.$user['campus_code']);
				$this->db->set('type','teacher');
				$this->db->set('campus_id',$user['campus_id']);
				$this->db->insert('machine_data');
			}
			else
			{
				$exist_user = $this->db->get_where('machine_data',array('teacher_student_id'=>$user['user_id'],'type'=>'teacher'))->result_array();
				if(count($exist_user)<1)
				{
					$sql = 'SELECT machine_id FROM machine_data WHERE campus_id='.$user['campus_id'].' ORDER BY machine_id DESC LIMIT 1';
					$query = $this->db->query($sql)->result_array();
					$last_machine_id = substr($query[0]['machine_id'], 0, -2);
					
					$this->db->set('teacher_student_id',$user['user_id']);
					$this->db->set('machine_id',($last_machine_id+1).$user['campus_code']);
					$this->db->set('type','teacher');
					$this->db->set('campus_id',$user['campus_id']);
					$this->db->insert('machine_data');
				}
			}
		}
	}
	
	public function set_student_machine_id()
	{
		$this->db->select('students.*,campuses.campus_code,campuses.campus_id');
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
		$this->db->join('campuses', 'campuses.campus_id=classes.campus_id', 'INNER');
		$this->db->where(array('campuses.status'=>1,'campus_code!='=>''));
		$students = $this->db->get()->result_array();
		foreach($students as $student)
		{
			$get_most_larger_machine_id = $this->db->get_where('machine_data',array('campus_id'=>$student['campus_id']))->result_array();
			if(count($get_most_larger_machine_id)<1)
			{
				$this->db->set('teacher_student_id',$student['student_id']);
				$this->db->set('machine_id','1'.$student['campus_code']);
				$this->db->set('type','student');
				$this->db->set('campus_id',$student['campus_id']);
				$this->db->insert('machine_data');
			}
			else
			{
				$exist_user = $this->db->get_where('machine_data',array('teacher_student_id'=>$student['student_id'],'type'=>'student'))->result_array();
				if(count($exist_user)<1)
				{
					$sql = 'SELECT machine_id FROM machine_data WHERE campus_id='.$student['campus_id'].' ORDER BY machine_id DESC LIMIT 1';
					$query = $this->db->query($sql)->result_array();
					$last_machine_id = substr($query[0]['machine_id'], 0, -2);
					
					$this->db->set('teacher_student_id',$student['student_id']);
					$this->db->set('machine_id',($last_machine_id+1).$student['campus_code']);
					$this->db->set('type','student');
					$this->db->set('campus_id',$student['campus_id']);
					$this->db->insert('machine_data');
				}
			}
		}
	}
	
	public function student($student_id)
	{
		$this->db->select('students.*,classes.name,classes.session,campuses.campus_id,campuses.campus_name,machine_data.*');
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
		$this->db->join('machine_data', 'machine_data.teacher_student_id=students.student_id', 'inner');
		$this->db->where('students.student_id',$student_id);
		$data['students'] = $this->db->get()->result_array();
		
		if($this->input->post('start_date')=='' && $this->input->post('end_date')=='')
		{
			$data['start_date'] = date("Y-m-d", strtotime(date("Y-m-d")." -1 week"));
			$data['end_date'] = date("Y-m-d");
			
			$data['period'] = new DatePeriod(
				 new DateTime($data['start_date']),
				 new DateInterval('P1D'),
				 new DateTime($data['end_date'])
			);
		}
		else
		{
			$data['start_date'] = $this->input->post('start_date');
			$data['end_date'] = $this->input->post('end_date');
			
			$data['period'] = new DatePeriod(
				 new DateTime($data['start_date']),
				 new DateInterval('P1D'),
				 new DateTime($data['end_date'])
			);
		}
		
		

		$now = time(); // or your date as well
		$your_date = strtotime($data['students'][0]['registration_date']);
		$datediff = $now - $your_date;

		$data['days']= round($datediff / (60 * 60 * 24));
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('attendence_data/student',$data);
		$this->load->view('inc/footer');
	}
}
