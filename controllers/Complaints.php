<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Complaints extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		//$this->load->library('Email_reader');	
	}
	public function pending()
	{
		$this->db->select('complaints.*,students.first_name,students.last_name,students.roll_no,complaint_types.complaint_type');
		$this->db->from('complaints');
		$this->db->join('complaint_types','complaint_types.complaint_type_id=complaints.complaint_type_id','inner');
		$this->db->join('students','students.student_id=complaints.student_id','inner');
		$this->db->where('complaints.complaint_status',0);
		$data['complaints']=$this->db->get()->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('complaints/pending',$data);
		$this->load->view('inc/footer');
	}

	public function completed()
	{
		$this->db->select('complaints.*,students.first_name,students.last_name,students.roll_no,complaint_types.complaint_type');
		$this->db->from('complaints');
		$this->db->join('complaint_types','complaint_types.complaint_type_id=complaints.complaint_type_id','inner');
		$this->db->join('students','students.student_id=complaints.student_id','inner');
		$this->db->where('complaints.complaint_status',1);
		$data['complaints']=$this->db->get()->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('complaints/completed',$data);
		$this->load->view('inc/footer');
	}

	public function view_complaint($complaint_id)
	{
		$this->db->select('*');
		$this->db->from('complaint_chats');
		$this->db->where('complaint_id',$complaint_id);
		$this->db->order_by('created_at','ASC');
		$data['chats'] = $this->db->get()->result_array();

		//GET STUDENT PICTURE
		$photo = $this->db->get_where('student_documents',array('student_id'=>$data['chats'][0]['student_id'],'type'=>'Photo'))->result_array();
		if(count($photo)>0)
		{
			if($photo[0]['online_image']!='')
			{
				$data['student_photo'] = $photo[0]['online_image'];
			}
			else
			{
				$data['student_photo'] = base_url().'uploads/'.$photo[0]['image'];
			}
		}
		else
		{
			$data['student_photo'] = '';
		}
		//GET STUDENT INFORMATION
		$data['student_details'] = $this->db->get_where('students',array('student_id'=>$data['chats'][0]['student_id']))->result_array();

		//GET COMPLAINT STATUS
		$data['complaint'] = $this->db->get_where('complaints',array('complaint_id'=>$complaint_id))->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('complaints/view_complaint',$data);
		$this->load->view('inc/footer');
	}

	public function solved($complaint_id)
	{
		$this->db->set('complaint_status',1);
		$this->db->where('complaint_id',$complaint_id);
		$this->db->update('complaints');

		$this->session->set_flashdata('message','Complaint has been resolved.');
		redirect('complaints/view_complaint/'.$complaint_id);
	}

	public function replyComplaint($complaint_id)
	{
		$this->db->set('user_id',$this->session->userdata('user_id'));
		$this->db->set('message',$this->input->post('message'));
		$this->db->set('complaint_id',$complaint_id);
		$this->db->insert('complaint_chats');

		$this->session->set_flashdata('message','Reply posted successfully.');
		redirect('complaints/view_complaint/'.$complaint_id);
	}
}
