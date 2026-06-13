<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Departments extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function add_department()
	{
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('departments/add_department');
		$this->load->view('inc/footer');
	}
	
	public function insert()
	{
		$department_name = $this->input->post('department_name');
		$check = $this->db->get_where('departments',array('department_name'=>$department_name))->result_array();
		
		if(count($check)>0)
		{
			$this->session->set_flashdata('error','Department Already Added.');
			redirect('departments/add_department');
		}
		else
		{
			$this->db->set('department_name',$department_name);
			$this->db->insert('departments');
			
			$this->session->set_flashdata('message','Department Added Successfully');
			redirect('departments/add_department');
		}
	}
	
	public function all_departments()
	{
		$data['departments'] = $this->db->get('departments')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('departments/all_departments',$data);
		$this->load->view('inc/footer');
	}
	
	public function edit_department($department_id)
	{
		$data['department'] = $this->db->get_where('departments',array('department_id'=>$department_id))->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('departments/edit_department',$data);
		$this->load->view('inc/footer');
	}
	
	public function update($department_id)
	{
		$department_name = $this->input->post('department_name');
		
		$this->db->set('department_name',$department_name);
		$this->db->where('department_id',$department_id);
		$this->db->update('departments');
		
		$this->session->set_flashdata('message','Department Updated Successfully');
		redirect('departments/edit_department/'.$department_id);
	}
	
	public function delete($department_id)
	{
		$this->db->where('department_id',$department_id);
		$this->db->delete('departments');
		
		$this->session->set_flashdata('message','Department Deleted Successfully');
		redirect('departments/all_departments');
	}
		
}
