<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class News_updates extends CI_Controller {

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
	
	public function add_news()
	{
		$data['campuses'] = $this->clas->getCampuses();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('news_updates/add_news', $data);
		$this->load->view('inc/footer');
	}
	
	public function insert()
	{
		$campus_ids = $this->input->post('campus_ids');
		$campus_ids = implode(',',$campus_ids);
		
		$this->db->set('campus_ids', $campus_ids);
		$this->db->set('news', $this->input->post('news'));
		$this->db->insert('news_updates');
		$this->session->set_flashdata('message', 'News &amp; Updates added successfully.');
		redirect('news_updates/add_news');
	}
	
	public function all_news()
	{
		$data['news'] = $this->db->get('news_updates')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('news_updates/all_news', $data);
		$this->load->view('inc/footer');
	}
	
	public function delete($id)
	{
		
		if($this->session->userdata('role')!='Admin')
		{
			$access = checkUserAccess();
			$campus_ids = @explode(',',$access[0]['campus_ids']);
			
			$current_news = $this->db->get_where('news_updates', array('news_id'=>$id))->result_array();
			$campus_id_news_updates = explode(',',$current_news[0]['campus_ids']);
			
			foreach($campus_ids as $campus_id):
				if (($key = array_search($campus_id, $campus_id_news_updates)) !== false) {
					unset($campus_id_news_updates[$key]);
				}
			endforeach;
			
			$this->db->set('campus_ids', implode(',', $campus_id_news_updates));
			$this->db->where('news_id', $id);
			$this->db->update('news_updates');
			$this->session->set_flashdata('message', 'Your news has been removed.');
		
		redirect('news_updates/all_news');
		}
		else
		{
			$this->db->where('news_id', $id);
			$this->db->delete('news_updates');
			
			$this->session->set_flashdata('message', 'Your news has been removed.');
			
			redirect('news_updates/all_news');
		}
	}
}
