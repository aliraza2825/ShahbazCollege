<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Downloads extends CI_Controller {

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
	
	public function add_download()
	{
		$data['campuses'] = $this->clas->getCampuses();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('downloads/add_download', $data);
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
		$config['upload_path'] = 'downloads/';
		
    	// set the filter image types
		$config['allowed_types'] = '*';
		
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
		$this->db->set('title', $this->input->post('title'));
		$this->db->set('document', $url);
		$this->db->insert('downloads');
		
		$this->session->set_flashdata('message', 'Download Uploaded Successfully.');
		redirect('downloads/add_download');
	}
	
	public function all_downloads()
	{
		$data['downloads'] = $this->db->get('downloads')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('downloads/all_downloads', $data);
		$this->load->view('inc/footer');
	}
	
	public function delete($id)
	{
		if($this->session->userdata('role')!='Admin')
		{
			$access = checkUserAccess();
			$campus_ids = @explode(',',$access[0]['campus_ids']);
			
			$current_download = $this->db->get_where('downloads', array('download_id'=>$id))->result_array();
			$campus_id_downloads = explode(',',$current_download[0]['campus_ids']);
			
			foreach($campus_ids as $campus_id):
				if (($key = array_search($campus_id, $campus_id_downloads)) !== false) {
					unset($campus_id_downloads[$key]);
				}
			endforeach;
			
			$this->db->set('campus_ids', implode(',', $campus_id_downloads));
			$this->db->where('download_id', $id);
			$this->db->update('downloads');
			$this->session->set_flashdata('message', 'Your Download has been removed.');
		
			redirect('downloads/all_downloads');
		}
		else
		{
			$this->db->where('download_id', $id);
			$this->db->delete('downloads');
			
			$this->session->set_flashdata('message', 'Your Download has been removed.');
			
			redirect('downloads/all_downloads');
		}
	}
}
