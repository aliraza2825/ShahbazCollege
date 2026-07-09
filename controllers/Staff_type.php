<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Staff_type extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function add_staff_type()
	{
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('staff_type/add_staff_type');
		$this->load->view('inc/footer');
	}
	
	public function insert()
	{
		$staff_type_name = $this->input->post('staff_type_name');
		$check = $this->db->get_where('staff_type',array('staff_type_name'=>$staff_type_name))->result_array();
		
		if(count($check)>0)
		{
			$this->session->set_flashdata('error','Staff Type Already Added.');
			redirect('staff_type/add_staff_type');
		}
		else
		{
			$this->db->set('staff_type_name',$staff_type_name);
			$this->db->insert('staff_type');
			
			$this->session->set_flashdata('message','Staff Type Added Successfully');
			redirect('staff_type/add_staff_type');
		}
	}
	
	public function all_staff_type()
	{
		$data['staff_types'] = $this->db->get('staff_type')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('staff_type/all_staff_type',$data);
		$this->load->view('inc/footer');
	}
	
	public function edit_staff_type($staff_type_id)
	{
		$data['staff_type'] = $this->db->get_where('staff_type',array('staff_type_id'=>$staff_type_id))->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('staff_type/edit_staff_type',$data);
		$this->load->view('inc/footer');
	}
	
	public function update($staff_type_id)
	{
		$staff_type_name = $this->input->post('staff_type_name');
		
		$this->db->set('staff_type_name',$staff_type_name);
		$this->db->where('staff_type_id',$staff_type_id);
		$this->db->update('staff_type');
		
		$this->session->set_flashdata('message','Staff Type Updated Successfully');
		redirect('staff_type/edit_staff_type/'.$staff_type_id);
	}
	
	public function delete($staff_type_id)
	{
		$this->db->where('staff_type_id',$staff_type_id);
		$this->db->delete('staff_type');
		
		$this->session->set_flashdata('message','Staff Type Deleted Successfully');
		redirect('staff_type/all_staff_type');
	}
		
}
