<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Next_council_admissions extends CI_Controller {
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
	public function index()
	{
		if(@$this->input->post('form_submit'))
		{
			$class_id = $this->input->post('class_id');
			$this->db->select('students.*, classes.name as class_name, campuses.campus_name');
			$this->db->from('students');
			$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
			$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
			$this->db->where(array('classes.class_id'=>$class_id, 'students.status'=>1));
			$data['students'] = $this->db->get()->result_array();
		}
		
		$data['campuses'] = $this->clas->getCampuses();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('next_council_admissions/index', $data);
		$this->load->view('inc/footer');
	}
	
	public function add_manual_remarks()
	{
		if($this->input->post('next_admission')==1)
		{
			//CURRENT STUDENT
			$current_student = $this->db->get_where('students', array('student_id'=>$this->input->post('student_id')))->result_array();
			
			$challan_no = $this->getChallanNo();
			$this->db->set('amount', $this->input->post('amount'));
			$this->db->set('dead_line', $this->input->post('fee_submission_date'));
			$this->db->set('challan_no', $challan_no);
			if($current_student[0]['contract_id']==0)
			{
				$this->db->set('student_id', $this->input->post('student_id'));
			}
			$this->db->set('contract_id', $current_student[0]['contract_id']);
			$this->db->set('custom_student_id', $this->input->post('student_id'));
			$this->db->set('add_by', $this->session->userdata('name'));
			$this->db->set('last_edit', $this->session->userdata('name'));
			$this->db->set('payment_plan', 'consulation fee');
			if($this->input->post('clas')==1)
			{
				$class = '1st Year';
			}
			else
			{
				$class = '2nd Year';
			}
			$this->db->set('payment_comment', 'Manual Added fee. This fee for next exam # '.$this->input->post('council_exam_no').' '.$class.'');
			$this->db->insert('payments');
			
			$this->db->set('fee_submission_date', $this->input->post('fee_submission_date'));
		}
		else
		{
			$this->db->set('fee_submission_date', '0000-00-00');
		}
		$this->db->set('student_id', $this->input->post('student_id'));
		$this->db->set('council_exam_no', $this->input->post('council_exam_no'));
		$this->db->set('remarks', $this->input->post('remarks'));
		$this->db->set('send_admission', $this->input->post('next_admission'));
		$this->db->set('student_status', $this->input->post('student_status'));
		$this->db->set('class', $this->input->post('clas'));
		$this->db->set('add_by', $this->session->userdata('name'));
		
		$this->db->insert('send_next_admissions');
	}
	
	public function getChallanNo()
	{
		$random_number = rand(1000, 999999999);
		$check_challan_no = $this->db->get_where('payments', array('challan_no'=>$random_number))->result_array();
		if(count($check_challan_no)>0)
		{
			$random_number = $this->getChallanNo();
		}
		else
		{
			return $random_number;
		}
	}
}
