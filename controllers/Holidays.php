<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Holidays extends CI_Controller {

	
	public function __construct()
	{
		parent::__construct();
		$this->load->model('holiday');
	}
	
	public function index()
	{
		$data['campuses'] = $this->holiday->getCampuses();
		$data['types'] = $this->db->get('staff_type')->result_array();

		if($this->input->post('from_date'))
		{
			$data['from_date'] = $this->input->post('from_date');
			$data['to_date'] = $this->input->post('to_date');
		}
		else
		{
			$data['from_date'] = date('Y-m-d');
			$data['to_date'] = date("Y-m-d", strtotime("+1 month", strtotime(date('Y-m-d'))));
		}

		$data['holidays'] = $this->holiday->getHolidays($data['from_date'],$data['to_date']);
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('holidays/index', $data);
		$this->load->view('inc/footer');
	}
	
	public function insert()
	{
			
		$data = $this->input->post();
		$this->holiday->insertHoliday($data);
		redirect('holidays');
	}
	
	public function delete($holiday_id)
	{
		$this->db->where('holiday_id', $holiday_id);
		$this->db->delete('holidays');
		$this->session->set_flashdata('message', 'Holiday deleted successfully');
		redirect('holidays');
	}

	public function cancel_holiday()
	{
		$holiday_id = $this->input->post('holiday_id');
		$cancel_reason = $this->input->post('cancel_reason');

		$this->db->set('cancel',1);
		$this->db->set('cancel_reason',$cancel_reason);
		$this->db->where('holiday_id',$holiday_id);
		$this->db->update('holidays');

		$this->session->set_flashdata('message', 'Holiday cancelled successfully');
		redirect('holidays');
	}
	
	public function getStaff()
	{
		$campus_id = $this->input->post('campus_id');
		$staffs = $this->db->get_where('users', array('campus_id'=>$campus_id))->result_array();
		$html = '';
		$html .= '<option value="all">ALL</option>';
		foreach($staffs as $staff)
		{
			$html .= '<option value="'.$staff['user_id'].'">'.$staff['first_name'].' '.$staff['last_name'].'</option>';
		}
		echo $html;
	}

	public function findStaff()
	{
		$campus_ids = implode(',',$this->input->post('campus_ids'));
		$staff_type_ids = implode(',',$this->input->post('staff_type_ids'));

		$qry = 'SELECT * FROM users WHERE status=1 AND campus_id IN ('.$campus_ids.') AND staff_type_id IN ('.$staff_type_ids.')';
		$users = $this->db->query($qry)->result_array();

		$html='';
		foreach($users as $user)
		{
			$html.='<option value="'.$user['user_id'].'" selected="selected">'.$user['first_name'].' '.$user['last_name'].'</option>';
		}
		echo $html;
	}

	public function findShifts()
	{
		$campus_ids = implode(',',$this->input->post('campus_ids'));

		$qry = 'SELECT shifts.*, study_type.name as study_type_name, courses.course_name
			FROM shifts
			LEFT JOIN study_type ON study_type.id = shifts.study_type_id
			LEFT JOIN courses ON courses.course_id = study_type.course_id
			WHERE shifts.campus_id IN ('.$campus_ids.')';
		$shifts = $this->db->query($qry)->result_array();

		$html='';
		foreach($shifts as $shift)
		{
			$parts = array($shift['name']);
			if(!empty($shift['study_type_name']))
			{
				$parts[] = $shift['study_type_name'];
			}
			if(!empty($shift['course_name']))
			{
				$parts[] = $shift['course_name'];
			}
			$label = implode(' - ', $parts);
			$html.='<option value="'.$shift['id'].'" selected="selected">'.htmlspecialchars($label).'</option>';
		}
		echo $html;
	}

	public function findShiftStudents()
	{
		$shift_ids = implode(',',$this->input->post('shift_ids'));

		$qry = 'SELECT student_id FROM students WHERE status=1 AND shift IN ('.$shift_ids.')';
		$students = $this->db->query($qry)->result_array();

		$student_ids = array();
		foreach($students as $student)
		{
			$student_ids[] = $student['student_id'];
		}

		echo json_encode(array(
			'student_ids' => implode(',', $student_ids),
			'count' => count($student_ids)
		));
	}
}
