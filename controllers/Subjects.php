<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Subjects extends CI_Controller {
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
		$this->load->model('subject');
	}
	
	public function insert()
	{
		$data = $this->input->post();
		$this->subject->storeSubject($data);
		$this->session->set_flashdata('message', 'Subject added successfully!');
		redirect('subjects/add_subject');
	}
	
	public function update($id)
	{
		$data = $this->input->post();
		$this->subject->updateSubject($data);
		$this->session->set_flashdata('message', 'Subject updated successfully!');
		redirect('subjects/edit_subject/'.$id);
	}
	
	public function delete($id)
	{
		$this->subject->deleteSubject($id);
		$this->session->set_flashdata('message', 'Subject deleted successfully!');
		redirect('subjects/all_subjects');
	}
	
	public function index()
	{
		/*$data['classes'] = $this->clas->getTeacherClasses();
	//	$data['count'] = $this->clas->getTeacherClasses();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('classes/all_classes', $data);
		$this->load->view('inc/footer');*/
	}
	
	public function add_subject()
	{
		$data['count'] = $this->subject->getSubjectsCount();
		
		$data['courses'] = $this->db->get('courses')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('subjects/add_subject', $data);
		$this->load->view('inc/footer');
	}
	
	public function edit_subject($id)
	{
		$data['current_subject'] = $this->subject->editSubject($id);
		$data['count'] = $this->subject->getSubjectsCount();
		$data['subjects'] = $this->subject->getAllSubject();
		$data['courses'] = $this->db->get('courses')->result_array();
		$data['current_course'] = $this->db->get_where('courses',array('course_id'=>$data['current_subject'][0]['course_id']))->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('subjects/edit_subject', $data);
		$this->load->view('inc/footer');
	}
	
	public function all_subjects()
	{
		$data['count'] = $this->subject->getSubjectsCount();
		$data['subjects'] = $this->subject->getAllSubject();
		
    
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('subjects/all_subjects', $data);
		$this->load->view('inc/footer');
	}
	
	public function students($class_id)
	{
		$data['students'] = $this->clas->getStudents($class_id);
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('classes/students', $data);
		$this->load->view('inc/footer');
	}
	
	public function getCourseDetails()
	{
		$course_id = $this->input->post('course_id');
		$course = $this->db->get_where('courses',array('course_id'=>$course_id))->result_array();
		$html='';
		if($course[0]['course_type']=='Annual')
		{
			$html.='<div class="form-group"><label class="col-md-3 control-label">Select Subject Year <span class="required">*</span></label><div class="col-md-9"><select name="subject_year" class="form-control input-inline input-medium" required><option value="">Select Subject Year</option>';
			$years = $course[0]['course_duration_year'];
			for($i=1;$i<=$years;$i++)
			{
				$html.='<option value="'.$i.'">'.$i.' Year</option>';
			}
			$html.='</select></div></div>';
			$html.='<input type="hidden" name="subject_semester" value="0" />';
			echo $html;
		}
		if($course[0]['course_type']=='Semester')
		{
			$html.='<div class="form-group"><label class="col-md-3 control-label">Select Subject Semester <span class="required">*</span></label><div class="col-md-9"><select name="subject_semester" class="form-control input-inline input-medium" required><option value="">Select Subject Semester</option>';
			$semesters = $course[0]['course_semester'];
			for($i=1;$i<=$semesters;$i++)
			{
				$html.='<option value="'.$i.'">'.$i.' Semester</option>';
			}
			$html.='</select></div></div>';
			$html.='<input type="hidden" name="subject_year" value="0" />';
			echo $html;
		}
	}
}
