<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class References extends CI_Controller {
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

	public function add_reference()
	{
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('references/add_reference');
		$this->load->view('inc/footer');
	}
	
	public function insert()
	{
		$name = $this->input->post('name');
		$phone = $this->input->post('phone');
		$note = $this->input->post('note');
		$status = 1;

		$check = $this->db->get_where('reference_users',array('phone'=>$phone))->result_array();

		if(count($check)>0)
		{
			$this->session->set_flashdata('error', 'Reference User Already Exists!');
			redirect('references/add_reference');
		}
		else
		{
			$this->db->set('name',$name);
			$this->db->set('phone',$phone);
			$this->db->set('note',$note);
			$this->db->set('status',$status);
			$this->db->insert('reference_users');

			$this->session->set_flashdata('message', 'Reference User added successfully!');
			redirect('references/add_reference');
		}
	}

	public function all_references()
	{
		$this->db->select('reference_users.*,count(students.student_id) as total_students');
		$this->db->from('reference_users');
		$this->db->join('students','students.reference_user_id=reference_users.reference_user_id','left');
		$this->db->group_by('reference_users.reference_user_id');
		$data['references'] = $this->db->get()->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('references/all_references',$data);
		$this->load->view('inc/footer');
	}

	public function edit_reference($reference_user_id)
	{
		$data['reference'] = $this->db->get_where('reference_users',array('reference_user_id'=>$reference_user_id))->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('references/edit_reference',$data);
		$this->load->view('inc/footer');
	}

	public function update($reference_user_id)
	{
		$name = $this->input->post('name');
		$phone = $this->input->post('phone');
		$note = $this->input->post('note');
		$status = $this->input->post('status');
		
		$this->db->set('name',$name);
		$this->db->set('phone',$phone);
		$this->db->set('note',$note);
		$this->db->set('status',$status);
		$this->db->where('reference_user_id',$reference_user_id);
		$this->db->update('reference_users');

		$this->session->set_flashdata('message', 'Reference User Updated successfully!');
		redirect('references/all_references');
	}
	
	public function students($reference_user_id)
	{
		$this->db->select('students.*, classes.name as class_name');
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
		$this->db->where('reference_user_id',$reference_user_id);
		$data['students'] = $this->db->get()->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('references/students',$data);
		$this->load->view('inc/footer');
	}
}
