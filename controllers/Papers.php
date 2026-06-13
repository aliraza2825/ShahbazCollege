<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Papers extends CI_Controller {
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
		$this->load->model('paper');
		$this->load->library('upload');
	}
	
	public function insert()
	{
		$data = $this->input->post();
		//load the helper
		$this->load->helper('form');

		//Configure
		//set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
		$config['upload_path'] = 'uploads/';
		
    	// set the filter image types
		$config['allowed_types'] = '*';
		
		//load the upload library
		$this->load->library('upload', $config);
    
		$this->upload->initialize($config);
		
		$this->upload->set_allowed_types('*');

		$upload_data = '';
    
		//if not successful, set the error message
		if (!$this->upload->do_upload('content')) {
			$data = array('msg' => $this->upload->display_errors());
			$data['content'] = '';

		} 
		else 
		{ 
			//else, set the success message
      		$upload_data = $this->upload->data();
			if($upload_data['file_name']){
				$data['content'] = $upload_data['file_name'];
			}
		}
		
		
		$this->paper->storePaper($data);
		$this->session->set_flashdata('message', 'Paper added successfully!');
		redirect('papers/add_paper');
	}
	
	public function update($id)
	{
		$data = $this->input->post();
		
		//load the helper
		$this->load->helper('form');

		//Configure
		//set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
		$config['upload_path'] = 'uploads/';
		
    	// set the filter image types
		$config['allowed_types'] = '*';
		
		//load the upload library
		$this->load->library('upload', $config);
    
		$this->upload->initialize($config);
		
		$this->upload->set_allowed_types('*');

		$upload_data = '';
    
		//if not successful, set the error message
		if (!$this->upload->do_upload('content')) {
			//$data = array('msg' => $this->upload->display_errors());
			$data['content'] = '';

		} 
		else 
		{ 
			//else, set the success message
      		$upload_data = $this->upload->data();
			if($upload_data['file_name']){
				$data['content'] = $upload_data['file_name'];
			}
		}
		
		$this->paper->updatePaper($data);
		$this->session->set_flashdata('message', 'Paper updated successfully!');
		redirect('papers/edit_paper/'.$id);
	}
	
	public function delete($id)
	{
		$this->paper->deletePaper($id);
		$this->session->set_flashdata('message', 'Paper deleted successfully!');
		redirect('papers/all_papers');
	}
	
	public function add_paper()
	{
		$teacher_id = $this->session->userdata('user_id');
		$data['subjects'] = $this->paper->getTeacherSubjects($teacher_id);
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('papers/add_paper', $data);
		$this->load->view('inc/footer');
	}
	
	public function edit_paper($id)
	{
		$teacher_id = $this->session->userdata('user_id');
		$data['subjects'] = $this->paper->getTeacherSubjects($teacher_id);
		$data['papers'] = $this->paper->getPaper($id);
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('papers/edit_paper', $data);
		$this->load->view('inc/footer');
	}
	
	public function all_papers()
	{
		$teacher_id = $this->session->userdata('user_id');
		$data['papers'] = $this->paper->getTeacherPapers($teacher_id);
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('papers/all_papers', $data);
		$this->load->view('inc/footer');
	}
	
	public function add_result($paper_id)
	{
		$data['students'] = $this->paper->getStudents($paper_id);
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('papers/add_result', $data);
		$this->load->view('inc/footer');
	}
	
	public function show_result($paper_id)
	{
		$data['students'] = $this->paper->getStudentsResult($paper_id);
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('papers/show_result', $data);
		$this->load->view('inc/footer');
	}
	
	public function insert_result($id)
	{
		$data = $this->input->post();
		$this->paper->addResult($data);
		$this->session->set_flashdata('message', 'Result has been added successfully.');
		redirect('papers/all_papers');
	}
	
	public function update_result($id)
	{
		$data = $this->input->post();
		$this->paper->updateResult($data);
		$this->session->set_flashdata('message', 'Result has been updated successfully.');
		redirect('papers/show_result/'.$id);
	}
}
