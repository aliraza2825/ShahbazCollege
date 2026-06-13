<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Archive extends CI_Controller {
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
		$this->load->model('archives');
	}
	
	public function teachers()
	{
		$data['teachers'] = $this->archives->teachers();
		$data['count'] = $this->archives->getTeachersCount();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('archive/teachers', $data);
		$this->load->view('inc/footer');
	}
	
	public function restore_teacher($id)
	{
		$this->archives->restoreTeacher($id);
		redirect('archive/teachers');
	}
	
	public function delete_teacher($id)
	{
		$this->archives->deleteTeacher($id);
		redirect('archive/teachers');
	}
	
	public function campuses()
	{
		$data['campuses'] = $this->db->get_where('campuses',array('status'=>0))->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('archive/campuses', $data);
		$this->load->view('inc/footer');
	}
	
	public function restore_campus($campus_id)
	{
		$this->db->set('status',1);
		$this->db->where('campus_id',$campus_id);
		$this->db->update('campuses');
		
		$this->session->set_flashdata('message', 'Campus restore successfully.');
		redirect('archive/campuses');
	}
	
	public function delete_campus($campus_id)
	{
		$this->db->where('campus_id',$campus_id);
		$this->db->delete('campuses');
		
		$this->session->set_flashdata('message', 'Campus deleted successfully.');
		redirect('archive/campuses');
	}
	
	public function classes()
	{
		$data['classes'] = $this->archives->classes();
		$data['count'] = $this->archives->getClassesCount();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('archive/classes', $data);
		$this->load->view('inc/footer');
	}
	
	public function restore_class($id)
	{
		$this->archives->restoreClass($id);
		redirect('archive/classes');
	}
	
	public function delete_class($id)
	{
		$this->archives->deleteClass($id);
		redirect('archive/classes');
	}
	
	public function subjects()
	{
		$data['subjects'] = $this->archives->subjects();
		$data['count'] = $this->archives->getSubjectsCount();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('archive/subjects', $data);
		$this->load->view('inc/footer');
	}
	
	public function restore_subject($id)
	{
		$this->archives->restoreSubject($id);
		redirect('archive/subjects');
	}
	
	public function delete_subject($id)
	{
		$this->archives->deleteSubject($id);
		redirect('archive/subjects');
	}
	
	public function students()
	{
		$data['students'] = $this->archives->students();
		$data['count'] = $this->archives->getStudentsCount();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('archive/students', $data);
		$this->load->view('inc/footer');
	}
	
	public function restore_student($id)
	{
		$this->archives->restoreStudent($id);
		$this->session->set_flashdata('message', 'Student Restored Request Submitted Successfully.');
		redirect('students/all_students');
	}
	
	public function delete_student($id)
	{
		$this->archives->deleteStudent($id);
		redirect('archive/students');
	}
	
}
