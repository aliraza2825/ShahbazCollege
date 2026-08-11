<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Designations extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function add_designation()
	{
		$data['departments'] = $this->db->get('departments')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('designations/add_designation',$data);
		$this->load->view('inc/footer');
	}
	
	public function insert()
	{
		$department_id = $this->input->post('department_id');
		$designation_name = $this->input->post('designation_name');
		$description = $this->input->post('description');

		$check = $this->db->get_where('designations',array('designation_name'=>$designation_name))->result_array();
		
		if(count($check)>0)
		{
			$this->session->set_flashdata('error','Designation Already Added.');
			redirect('designations/add_designation');
		}
		else
		{
			$this->db->set('department_id', $department_id);
			$this->db->set('designation_name', $designation_name);
			$this->db->set('description', $description);
			$this->db->insert('designations');
			
			$this->session->set_flashdata('message','Designation Added Successfully');
			redirect('designations/add_designation');
		}
	}
	
	public function all_designations()
	{
		$this->db->select('*');
		$this->db->from('designations');
		$this->db->join('departments','departments.department_id=designations.department_id','inner');
		$data['designations'] = $this->db->get()->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('designations/all_designations',$data);
		$this->load->view('inc/footer');
	}
	
	public function edit_designation($designation_id)
	{
		$data['departments'] = $this->db->get('departments')->result_array();
		$data['designation'] = $this->db->get_where('designations',array('designation_id'=>$designation_id))->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('designations/edit_designation',$data);
		$this->load->view('inc/footer');
	}
	
	public function update($designation_id)
	{
		$department_id = $this->input->post('department_id');
		$designation_name = $this->input->post('designation_name');
        $description = $this->input->post('description');
		
		$this->db->set('department_id', $department_id);
		$this->db->set('designation_name', $designation_name);
		$this->db->where('designation_id',$designation_id);
        $this->db->set('description', $description);
		$this->db->update('designations');
		
		$this->session->set_flashdata('message','Designation Updated Successfully');
		redirect('designations/edit_designation/'.$designation_id);
	}

	/** JSON: responsibilities + access for the signed-in user's designation */
	public function detail($designation_id = 0)
	{
		header('Content-Type: application/json; charset=utf-8');
		if (!$this->session->userdata('user_id')) {
			echo json_encode(array('success' => false, 'message' => 'Unauthorized'));
			return;
		}

		$designation_id = (int) $designation_id;
		if ($designation_id <= 0) {
			echo json_encode(array('success' => false, 'message' => 'Invalid designation'));
			return;
		}

		$user_ids = array();
		$raw = (string) $this->session->userdata('designation_id');
		foreach (explode(',', $raw) as $id) {
			$id = (int) trim($id);
			if ($id > 0) {
				$user_ids[] = $id;
			}
		}
		if (!in_array($designation_id, $user_ids, true)) {
			echo json_encode(array('success' => false, 'message' => 'Designation not assigned to this user'));
			return;
		}

		$row = $this->db
			->select('designations.designation_id, designations.designation_name, designations.description, departments.department_name')
			->from('designations')
			->join('departments', 'departments.department_id = designations.department_id', 'inner')
			->where('designations.designation_id', $designation_id)
			->get()
			->row_array();
		if (!$row) {
			echo json_encode(array('success' => false, 'message' => 'Designation not found'));
			return;
		}

		$this->load->library('access_service');
		$loaded = $this->access_service->load_designation($designation_id);
		$access_sections = $this->access_service->summarize_granted_access(
			$loaded ? $loaded['values'] : array()
		);

		echo json_encode(array(
			'success' => true,
			'data' => array(
				'designation_id' => $designation_id,
				'designation_name' => $row['designation_name'],
				'department_name' => $row['department_name'],
				'description' => isset($row['description']) ? $row['description'] : '',
				'access_sections' => $access_sections,
			),
		));
	}
		
}
