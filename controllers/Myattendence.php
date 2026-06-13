<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Myattendence extends CI_Controller {


	public function __construct()
	{
		parent::__construct();
		$this->load->model('teacher');
		$this->load->library('upload');	
	}
	
	public function index()
	{
		$user_id = $this->session->userdata('cnic');
		

//			$data['users'] = $this->teacher->getTeacher($user_id);
//			$type = strtolower($data['users'][0]['role']);
//
//			$machine_id = $this->db
//                ->get_where('machine_data', array('teacher_student_id'=>$user_id, 'type!='=>'student'))
//                ->result_array();
//
//			$data['machine_id'] = $machine_id[0]['machine_id'];


        if(@$this->input->post('from_date'))
			{
				$strDateFrom = $this->input->post('from_date');
			}
			else
			{
				$strDateFrom = date('Y-m-01');
			}
			if(@$this->input->post('to_date'))
			{
				$strDateTo = $this->input->post('to_date');
			}
			else
			{
				$strDateTo = date('Y-m-d');
			}
			$data['dates'] = $this->createDateRangeArray($strDateFrom,$strDateTo);
			$this->load->view('inc/header');
			$this->load->view('inc/sidebar');
			$this->load->view('myattendence/check_attendence', $data);
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



}
