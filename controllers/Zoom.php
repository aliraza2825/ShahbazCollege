<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Zoom extends CI_Controller {

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
	public function index()
	{
		$data['zooms'] = $this->db->get('zoom')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('zoom/index',$data);
		$this->load->view('inc/footer');
	}
	
	public function update()
	{
		//load the helper
		$this->load->helper('form');

		//Configure
		//set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
		$config['upload_path'] = 'zoom_images/';
		
    	// set the filter image types
		$config['allowed_types'] = 'gif|jpg|png';
		
		//load the upload library
		$this->load->library('upload', $config);
    
		$this->upload->initialize($config);
		
		$this->upload->set_allowed_types('*');

		$data['upload_data'] = '';
    
		//if not successful, set the error message
		if (!$this->upload->do_upload('image')) {
			$data = array('msg' => $this->upload->display_errors());
			$image = '';

		} 
		else 
		{ 
			//else, set the success message
      		$data['upload_data'] = $this->upload->data();
			if($data['upload_data']['file_name']){
				$image = $data['upload_data']['file_name'];
			}
		}
		
		$personal_meeting_ids = $this->input->post('personal_meeting_ids');
		$admission_timing = $this->input->post('admission_timing');
		$image = $image;
		$i=1;
		foreach($personal_meeting_ids as $personal_meeting_id):
			//echo $personal_meeting_id;
			$this->db->set('personal_meeting_id',$personal_meeting_id);
			$this->db->set('admission_timing',$admission_timing);
			if($image!='')
			{
				$this->db->set('image',$image);
			}
			$this->db->where('zoom_id',$i);
			$this->db->update('zoom');
			$i++;
		endforeach;
		
		$this->session->set_flashdata('message', 'Zoom Setting Updated Successfully!');
		redirect('zoom');
	}
}
