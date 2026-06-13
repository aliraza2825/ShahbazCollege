<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Fees extends CI_Controller {
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
		$this->load->model('fee');
		$this->load->model('clas');	
	}
	
	public function index()
	{
		$data['campuses'] = $this->clas->getCampuses();
		
		$data['classes'] = $this->fee->getClasses();
		
		if(@$this->input->post('today_wise')==1):
			$campus_id=$this->input->post('campus_id');
			$campus_code = $this->db->get_where('campuses',array('campus_id'=>$campus_id))->row()->campus_code;
			
			$this->db->select('payments.id as fee_id,payments.amount, payments.dead_line, payments.extra_amount,students.student_id, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic,campuses.campus_name, courses.course_name');
			$this->db->from('payments');
			$this->db->join('students', 'payments.student_id=students.student_id', 'inner');
			$this->db->join('machine_data', 'machine_data.teacher_student_id=students.student_id', 'inner');
			$this->db->join('attendence', 'machine_data.machine_id=attendence.machine_user_id', 'inner');
			$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
			$this->db->join('campuses', 'campuses.campus_id=classes.campus_id', 'inner');
			$this->db->join('courses', 'courses.course_id=students.course_id', 'inner');
			$this->db->where(array('students.status'=> 1, 'payments.paid'=>'0', 'payments.dead_line<='=>date('Y-m-d'),'attendence.time>='=>date('Y-m-d').' 00:00:00', 'attendence.time<='=>date('Y-m-d').' 23:59:00', 'attendence.campus_code'=>$campus_code));
			$this->db->group_by('students.student_id', 'asc');
			$this->db->order_by('students.roll_no', 'asc');
			$data['dues'] = $this->db->get()->result_array();
			//return $query;
			
		endif;
		
		if(@$this->input->post('class_id')):
			$class_id = $this->input->post('class_id');
			$data['dues'] = $this->fee->getDueFees($class_id);
		endif;
		
		if(@$this->input->post('type')=='contractors'):
			$this->db->select('*,payments.id as fee_id');
			$this->db->from('payments');
			$this->db->join('contracts','contracts.contract_id=payments.contract_id','inner');
			$this->db->join('contractors','contracts.contractor_id=contractors.contractor_id','inner');
			$this->db->join('campuses','contracts.campus_id=campuses.campus_id','inner');
			$this->db->where(array('contracts.campus_id'=>$this->input->post('campus_id'),'payments.paid'=>'0', 'payments.dead_line<='=>date('Y-m-t')));
			$data['contractors_dues'] = $this->db->get()->result_array();
		endif;
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('fees/fees', $data);
		$this->load->view('inc/footer');
	}
	
	public function prints()
	{
		$class_id = $this->input->post('class_id');
		$data['dues'] = $this->fee->getDueFees($class_id);
		$this->load->view('fees/prints', $data);
	}
	
	public function comment()
	{
		$check_fee = $this->db->get_where('fees_remarks', array('fee_id'=>$this->input->post('fee_id')))->result_array();
        $original_fee_entry = $this->db->get_where('payments', array('id'=>$this->input->post('fee_id')))->result_array();

        $payments=$this->db->get_where('payments',array('student_id'=>$original_fee_entry[0]['student_id']))->result_array();


        $entries="";
        foreach ($payments as $astx){


            if( $astx['paid']==0  && $astx['dead_line']<date('Y-m-d'))
            {
                $entries .=$astx['challan_no'].",";
            }


        }
		
		$this->db->set('fee_id', $this->input->post('fee_id'));
		$this->db->set('comment', $this->input->post('comment')." " .$this->input->post('selected_date'). " ".$this->input->post('description')."  for Challan no (".$entries .") ");
		$this->db->set('paid_on_date', $this->input->post('selected_date'));
		$this->db->set('add_by', $this->session->userdata('name'));
		$this->db->set('clear_status', '1');
		$this->db->insert('fees_remarks');
		$this->session->set_flashdata('message', 'Comment added successfully.');
        redirect($_SERVER['HTTP_REFERER']);
	}
}
