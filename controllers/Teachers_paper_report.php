<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Teachers_paper_report extends CI_Controller {

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
	public function index()
	{
		$data['teachers'] = $this->db->get_where('users', array('role'=>'Teacher'))->result_array();
		
		$teacher_id = $this->input->post('teacher_id');
		if($teacher_id)
		{
			$this->db->select('*');
			$this->db->from('papers');
			$this->db->join('users', 'users.user_id=papers.teacher_id', 'inner');
			$this->db->join('subjects', 'subjects.subject_id=papers.subject_id', 'inner');
			$this->db->where('papers.teacher_id', $teacher_id);
			$data['teachers_data'] = $this->db->get()->result_array();
		}
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('teachers_paper_report/index', $data);
		$this->load->view('inc/footer');
	}
	
	/*public function updateChallanNo()
	{
		$payments = $this->db->get('payments')->result_array();
		$i=1;
		foreach($payments as $payment)
		{
			$this->change($payment['id']);
			$i++;
		}
		echo $i;
	}
	
	public function change($id)
	{
		$random_number = $this->generateRandom();
		
		$this->db->set('challan_no', $random_number);
		$this->db->where('id', $id);
		$this->db->update('payments');
	}
	
	public function generateRandom()
	{
		$random_number = rand(100000000, 999999999);
		$check_challan_no = $this->db->get_where('payments', array('challan_no'=>$random_number))->result_array();
		if(count($check_challan_no)>0)
		{
			$random_number = $this->generateRandom();
		}
		else
		{
			return $random_number;
		}
	}*/
	
	/*public function checkStudentsChallan()
	{
		$payments = $this->db->get('payments')->result_array();
		
		$x=0;
		foreach($payments as $payment)
		{
			$challan = $this->db->get_where('payments', array('challan_no'=>$payment['challan_no'], 'id!='=>$payment['id']))->result_array();
			if(count($challan)>0)
			{
				$x++;
			}
		}
		echo $x;
	}*/
}
