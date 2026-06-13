<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Event_images extends CI_Controller {

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
		$this->load->model('clas');
	}
	
	public function add_event_image()
	{
		$data['campuses'] = $this->clas->getCampuses();
		$data['events'] = $this->db->get('events')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('event_images/add', $data);
		$this->load->view('inc/footer');
	}
	
	public function upload()
	{
		//load the helper
		$this->load->helper('form');

		//Configure
		//set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
		$config['upload_path'] = 'event_images/';
		
    	// set the filter image types
		$config['allowed_types'] = 'gif|jpg|png';
		
		//load the upload library
		$this->load->library('upload', $config);
    
		$this->upload->initialize($config);
		
		$this->upload->set_allowed_types('*');

		$data['upload_data'] = '';
    
		//if not successful, set the error message
		if (!$this->upload->do_upload('url')) {
			$data = array('msg' => $this->upload->display_errors());
			$url = '';

		} 
		else 
		{ 
			//else, set the success message
      		$data['upload_data'] = $this->upload->data();
			if($data['upload_data']['file_name']){
				$url = $data['upload_data']['file_name'];
			}
		}
		
		$this->db->set('campus_id', $this->input->post('campus_id'));
		$this->db->set('event_id', $this->input->post('event_id'));
		$this->db->set('show_on_website', $this->input->post('show_on_website'));
		$this->db->set('title', $this->input->post('title'));
		$this->db->set('url', $url);
		$this->db->insert('event_images');
		
		$this->session->set_flashdata('message', 'Image Uploaded Successfully.');
		redirect('event_images/add_event_image');
	}
	
	public function all_images()
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['campus_ids']);
		
		$this->db->select('*');
		$this->db->from('event_images');
		$this->db->join('campuses', 'event_images.campus_id=campuses.campus_id', 'inner');
		
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campuses.campus_id', $campus_ids);
		}
		
		$data['images'] = $this->db->get()->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('event_images/all_images', $data);
		$this->load->view('inc/footer');
	}
	
	public function delete($id)
	{
		$this->db->where('image_id', $id);
		$this->db->delete('event_images');
		
		$this->session->set_flashdata('message', 'Your images has been removed.');
		
		redirect('event_images/all_images');
	}
}
