<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pages extends CI_Controller {

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
		//$this->load->library('Email_reader');	
	}
	
	public function home_page()
	{
		$data['campuses'] = $this->db->get('campuses')->result_array();
		$data['contents'] = $this->db->get('website_content')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('pages/home_page', $data);
		$this->load->view('inc/footer');
	}
	
	public function edit_home_page($website_content_id)
	{
		$data['campuses'] = $this->db->get('campuses')->result_array();
		$data['content'] = $this->db->get_where('website_content', array('website_content_id'=>$website_content_id))->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('pages/edit_home_page', $data);
		$this->load->view('inc/footer');
	}
	
	public function insert()
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
		if (!$this->upload->do_upload('point_center_image')) {
			$data = array('msg' => $this->upload->display_errors());
			$point_center_image = '';

		} 
		else 
		{ 
			//else, set the success message
      		$data['upload_data'] = $this->upload->data();
			if($data['upload_data']['file_name']){
				$point_center_image = $data['upload_data']['file_name'];
			}
		}
		
		//if not successful, set the error message
		if (!$this->upload->do_upload('home_left_image')) {
			$data = array('msg' => $this->upload->display_errors());
			$home_left_image = '';

		} 
		else 
		{ 
			//else, set the success message
      		$data['upload_data'] = $this->upload->data();
			if($data['upload_data']['file_name']){
				$home_left_image = $data['upload_data']['file_name'];
			}
		}
		
		//if not successful, set the error message
		if (!$this->upload->do_upload('home_right_image')) {
			$data = array('msg' => $this->upload->display_errors());
			$home_right_image = '';

		} 
		else 
		{ 
			//else, set the success message
      		$data['upload_data'] = $this->upload->data();
			if($data['upload_data']['file_name']){
				$home_right_image = $data['upload_data']['file_name'];
			}
		}
		
		$data = $this->input->post();
		
		$check_record = $this->db->get_where('website_content', array('campus_id'=>$data['campus_id']))->result_array();
		if(count($check_record)>0)
		{
			//update query
			$this->db->set('campus_id', $data['campus_id']);
			$this->db->set('point_1', $data['point_1']);
			$this->db->set('point_1_explanation', $data['point_1_explanation']);
			$this->db->set('point_2', $data['point_2']);
			$this->db->set('point_2_explanation', $data['point_2_explanation']);
			$this->db->set('point_3', $data['point_3']);
			$this->db->set('point_3_explanation', $data['point_3_explanation']);
			$this->db->set('point_4', $data['point_4']);
			$this->db->set('point_4_explanation', $data['point_4_explanation']);
			$this->db->set('point_center_image', $point_center_image);
			$this->db->set('home_left_image', $home_left_image);
			$this->db->set('home_right_heading', $data['home_right_heading']);
			$this->db->set('home_right_paragraph', $data['home_right_paragraph']);
			$this->db->set('home_right_image', $home_right_image);
			$this->db->set('home_left_paragraph', $data['home_left_paragraph']);
			
			$this->db->where('campus_id', $data['campus_id']);
			$this->db->update('website_content');
		}
		else
		{
			//insert query
			$this->db->set('campus_id', $data['campus_id']);
			$this->db->set('point_1', $data['point_1']);
			$this->db->set('point_1_explanation', $data['point_1_explanation']);
			$this->db->set('point_2', $data['point_2']);
			$this->db->set('point_2_explanation', $data['point_2_explanation']);
			$this->db->set('point_3', $data['point_3']);
			$this->db->set('point_3_explanation', $data['point_3_explanation']);
			$this->db->set('point_4', $data['point_4']);
			$this->db->set('point_4_explanation', $data['point_4_explanation']);
			$this->db->set('point_center_image', $point_center_image);
			$this->db->set('home_left_image', $home_left_image);
			$this->db->set('home_right_heading', $data['home_right_heading']);
			$this->db->set('home_right_paragraph', $data['home_right_paragraph']);
			$this->db->set('home_right_image', $home_right_image);
			$this->db->set('home_left_paragraph', $data['home_left_paragraph']);
			
			$this->db->insert('website_content');
		}
		
		$this->session->set_flashdata('message', 'Content Updated SuccessFully');
		redirect('pages/home_page');
		
	}
	
	public function update($website_content_id)
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
		if (!$this->upload->do_upload('point_center_image')) {
			$data = array('msg' => $this->upload->display_errors());
			$point_center_image = $this->input->post('point_center_image_old');

		} 
		else 
		{ 
			//else, set the success message
      		$data['upload_data'] = $this->upload->data();
			if($data['upload_data']['file_name']){
				$point_center_image = $data['upload_data']['file_name'];
			}
		}
		
		//if not successful, set the error message
		if (!$this->upload->do_upload('home_left_image')) {
			$data = array('msg' => $this->upload->display_errors());
			$home_left_image = $this->input->post('home_left_image_old');

		} 
		else 
		{ 
			//else, set the success message
      		$data['upload_data'] = $this->upload->data();
			if($data['upload_data']['file_name']){
				$home_left_image = $data['upload_data']['file_name'];
			}
		}
		
		//if not successful, set the error message
		if (!$this->upload->do_upload('home_right_image')) {
			$data = array('msg' => $this->upload->display_errors());
			$home_right_image = $this->input->post('home_right_image_old');

		} 
		else 
		{ 
			//else, set the success message
      		$data['upload_data'] = $this->upload->data();
			if($data['upload_data']['file_name']){
				$home_right_image = $data['upload_data']['file_name'];
			}
		}
		
		$data = $this->input->post();
		
		//update query
		//$this->db->set('campus_id', $data['campus_id']);
		$this->db->set('point_1', $data['point_1']);
		$this->db->set('point_1_explanation', $data['point_1_explanation']);
		$this->db->set('point_2', $data['point_2']);
		$this->db->set('point_2_explanation', $data['point_2_explanation']);
		$this->db->set('point_3', $data['point_3']);
		$this->db->set('point_3_explanation', $data['point_3_explanation']);
		$this->db->set('point_4', $data['point_4']);
		$this->db->set('point_4_explanation', $data['point_4_explanation']);
		$this->db->set('point_center_image', $point_center_image);
		$this->db->set('home_left_image', $home_left_image);
		$this->db->set('home_right_heading', $data['home_right_heading']);
		$this->db->set('home_right_paragraph', $data['home_right_paragraph']);
		$this->db->set('home_right_image', $home_right_image);
		$this->db->set('home_left_paragraph', $data['home_left_paragraph']);
		
		$this->db->where('website_content_id', $website_content_id);
		$this->db->update('website_content');
			
		$this->session->set_flashdata('message', 'Content Updated SuccessFully');
		redirect(site_url().'/pages/edit_home_page/'.$website_content_id);
		
	}
}
