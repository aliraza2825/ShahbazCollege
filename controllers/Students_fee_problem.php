<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Students_fee_problem extends CI_Controller {
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
	}
	
	public function index()
	{	
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['campus_ids']);
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campus_id', $campus_ids);
		}
		$this->db->where('campuses.status',1);
		$data['campuses'] = $this->db->get('campuses')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('students_fee_problem/index', $data);
		$this->load->view('inc/footer');
	}
	
	public function campus($campus_id)
	{
		$this->db->select('*');
		$this->db->from('students');
		$this->db->join('classes','classes.class_id=students.class_id','inner');
		$this->db->join('campuses','campuses.campus_id=classes.campus_id','inner');
		$this->db->where(array('campuses.campus_id'=>$campus_id,'students.status'=>1,'students.contract_id'=>0));
		$students = $this->db->get()->result_array();
		
		$stud=array();
		foreach($students as $student)
		{
			$this->db->select_sum('amount');
			$this->db->from('payments');
			$this->db->where('student_id',$student['student_id']);
			$payment = $this->db->get()->result_array();
			
			if($payment[0]['amount']<$student['total_fee'])
			{
				array_push($stud,$student);
			}
			if(count($payment)<1)
			{
				array_push($stud,$student);
			}
		}
		$data['students'] = $stud;
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('students_fee_problem/campus', $data);
		$this->load->view('inc/footer');
	}
	
}