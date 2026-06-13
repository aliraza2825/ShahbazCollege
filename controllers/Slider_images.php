<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Slider_images extends CI_Controller {

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
	
	public function add_slider_image()
	{
		$data['campuses'] = $this->clas->getCampuses();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('slider_images/add', $data);
		$this->load->view('inc/footer');
	}
	
	public function edit_slider_image($id)
	{
		$data['campuses'] = $this->clas->getCampuses();
		$data['slider_image'] = $this->db->get_where('slider_images', array('id'=>$id))->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('slider_images/edit', $data);
		$this->load->view('inc/footer');
	}
	
	public function upload()
	{
		$campus_ids = $this->input->post('campus_ids');
		$campus_ids = implode(',',$campus_ids);
		
		//load the helper
		$this->load->helper('form');
		//Configure
		//set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
		$config['upload_path'] = 'slider_images/';
		
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
		
		$this->db->set('campus_ids', $campus_ids);
		$this->db->set('image', $url);
		$this->db->insert('slider_images');
		
		$this->session->set_flashdata('message', 'Image Uploaded Successfully.');
		redirect('slider_images/add_slider_image');
	}
	
	public function update($id)
	{
		$campus_ids = $this->input->post('campus_ids');
		$campus_ids = implode(',',$campus_ids);
		
		//load the helper
		$this->load->helper('form');
		//Configure
		//set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
		$config['upload_path'] = 'slider_images/';
		
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
			$url = $this->input->post('old_image');

		} 
		else 
		{ 
			//else, set the success message
      		$data['upload_data'] = $this->upload->data();
			if($data['upload_data']['file_name']){
				$url = $data['upload_data']['file_name'];
			}
		}
		
		$this->db->set('campus_ids', $campus_ids);
		$this->db->set('image', $url);
		$this->db->where('id', $id);
		$this->db->update('slider_images');
		
		$this->session->set_flashdata('message', 'Image Updated Successfully.');
		redirect('slider_images/edit_slider_image/'.$id);
	}
	
	public function all_images()
	{
		$data['images'] = $this->db->get('slider_images')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('slider_images/all_images', $data);
		$this->load->view('inc/footer');
	}
	
	public function delete($id)
	{
		if($this->session->userdata('role')!='Admin')
		{
			$access = checkUserAccess();
			$campus_ids = @explode(',',$access[0]['campus_ids']);
			
			$current_slider_image = $this->db->get_where('slider_images', array('id'=>$id))->result_array();
			$campus_id_slider_images = explode(',',$current_slider_image[0]['campus_ids']);
			
			foreach($campus_ids as $campus_id):
				if (($key = array_search($campus_id, $campus_id_slider_images)) !== false) {
					unset($campus_id_slider_images[$key]);
				}
			endforeach;
			
			$this->db->set('campus_ids', implode(',', $campus_id_slider_images));
			$this->db->where('id', $id);
			$this->db->update('slider_images');
			$this->session->set_flashdata('message', 'Your images has been removed.');
		
			redirect('slider_images/all_images');
		}
		else
		{
			$this->db->where('id', $id);
			$this->db->delete('slider_images');
			
			$this->session->set_flashdata('message', 'Your images has been removed.');
			
			redirect('slider_images/all_images');
		}
	}
}
