<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hr extends CI_Controller {

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
		$this->load->model('visitor');
	}
	public function index()
	{
		
	}
	
	public function add_interview()
	{
		$data['campuses'] = $this->visitor->getCampus();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('hr/add_interview', $data);
		$this->load->view('inc/footer');
	}
	
	public function insert_interview()
	{
		//load the helper
		$this->load->helper('form');

		//Configure
		//set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
		$config['upload_path'] = 'uploads/';
		
    	// set the filter image types
		$config['allowed_types'] = 'gif|jpg|png';
		
		//load the upload library
		$this->load->library('upload', $config);
    
		$this->upload->initialize($config);
		
		$this->upload->set_allowed_types('*');

		$data['upload_data'] = '';
    
		//if not successful, set the error message
		if (!$this->upload->do_upload('cv')) {
			$data = array('msg' => $this->upload->display_errors());
			$cv = '';

		} 
		else 
		{ 
			//else, set the success message
      		$data['upload_data'] = $this->upload->data();
			if($data['upload_data']['file_name']){
				$cv = $data['upload_data']['file_name'];
			}
		}
		
		$data = $this->input->post();
		foreach(@$data as $k=>$value){
			if($k!='cv'){
				if($k=='expert_in')
				{
					$value = implode(',', $value);
				}
				$this->db->set(''.$k.'', $value);
			}
		}
		//$this->db->set('expert_in', $this->db->post('expert_in'));
		$this->db->set('cv', $cv);
		$this->db->insert('interview');
		
		$this->session->set_flashdata('message', 'Interview Added Successfully.');
		redirect('hr/add_interview');
	}
	
	public function update_interview($interview_id)
	{
		//load the helper
		$this->load->helper('form');

		//Configure
		//set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
		$config['upload_path'] = 'uploads/';
		
    	// set the filter image types
		$config['allowed_types'] = 'gif|jpg|png';
		
		//load the upload library
		$this->load->library('upload', $config);
    
		$this->upload->initialize($config);
		
		$this->upload->set_allowed_types('*');

		$data['upload_data'] = '';
    
		//if not successful, set the error message
		if (!$this->upload->do_upload('cv')) {
			$data = array('msg' => $this->upload->display_errors());
			$cv = $this->input->post('old_cv');

		} 
		else 
		{ 
			//else, set the success message
      		$data['upload_data'] = $this->upload->data();
			if($data['upload_data']['file_name']){
				$cv = $data['upload_data']['file_name'];
			}
		}
		
		$data = $this->input->post();
		foreach(@$data as $k=>$value){
			if($k!='cv' && $k!='old_cv'){
				if($k=='expert_in')
				{
					$value = implode(',', $value);
				}
				$this->db->set(''.$k.'', $value);
			}
		}
		//$this->db->set('expert_in', $this->db->post('expert_in'));
		$this->db->set('cv', $cv);
		$this->db->where('interview_id', $interview_id);
		$this->db->update('interview');
		
		$this->session->set_flashdata('message', 'Interview Updated Successfully.');
		redirect(site_url().'/hr/edit_interview/'.$interview_id);
	}
	
	public function all_interviews()
	{
		$data['interviews'] = $this->visitor->getAllInterviews();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('hr/all_interviews', $data);
		$this->load->view('inc/footer');
	}
	
	public function edit_interview($interview_id)
	{
		$data['campuses'] = $this->visitor->getCampus();
		$data['interviews'] = $this->visitor->getInterview($interview_id);
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('hr/edit_interview', $data);
		$this->load->view('inc/footer');
	}
	
	public function delete($interview_id)
	{
		$this->db->where('interview_id', $interview_id);
		$this->db->delete('interview');
		$this->session->set_flashdata('message', 'Interview Deleted Successfully.');
		redirect('hr/all_interviews');
	}
}
