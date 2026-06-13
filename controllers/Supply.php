<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Supply extends CI_Controller {

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
		$this->load->model('suply');
	}
	
	public function index()
	{
		$data['students'] = $this->suply->getAllStudents();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('supply/index', $data);
		$this->load->view('inc/footer');
	}
	
	public function insert()
	{
		$data = $this->input->post();
		foreach($data['students'] as $student_id)
		{
			//ADD NUMBER OF SUPPLY TIMES
			$this->suply->addSupplyStudentTimes($student_id);
			//ADD CONSULTATION FEES
			$student_status = $this->suply->checkStudentStatus($student_id);
			if($student_status[0]['contractor_id']==0)
			{
				$dead_line = $this->input->post('dead_line');
				$challan_no = $this->getChallanNo($student_id, $dead_line);
				
				$this->db->set('amount', $this->input->post('fee_for_students'));
				$this->db->set('dead_line', $dead_line);
				$this->db->set('student_id', $student_id);
				$this->db->set('payment_plan', 'consulation fee');
				$this->db->set('challan_no', $challan_no);
				$this->db->insert('payments');
			}
			else
			{
				$dead_line = $this->input->post('dead_line');
				$challan_no = $this->getChallanNo($student_status[0]['contractor_id'], $dead_line);
				
				$this->db->set('amount', $this->input->post('fee_for_contractors'));
				$this->db->set('dead_line', $dead_line);
				$this->db->set('contractor_id', $student_status[0]['contractor_id']);
				$this->db->set('payment_plan', 'consulation fee');
				$this->db->set('challan_no', $challan_no);
				$this->db->insert('payments');
			}
		}
		$this->session->set_flashdata('message', 'Extra Punjab Consulation Fee has been added.');
		redirect('supply');
	}
	
	public function getChallanNo($id, $dead_line)
	{
		$d = date('Ymd', strtotime($dead_line));
		$challan_no = $id.$d;
		return $challan_no;
	}
}
