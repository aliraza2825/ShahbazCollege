<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Videos extends CI_Controller {
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
		//$this->load->model('fee');	
	}
	
	public function index()
	{
		$data['campuses'] = $this->db->get('campuses')->result_array();
		$data['videos'] = $this->db->get('videos')->result_array();
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('videos/index', $data);
		$this->load->view('inc/footer');
	}
	
	public function insert()
	{
		$this->db->set('campus_id', $this->input->post('campus_id'));
		$this->db->set('title', $this->input->post('title'));
		$this->db->set('url', $this->input->post('url'));
		$this->db->set('show_on_website', $this->input->post('show_on_website'));
		$this->db->set('for_apply_now', $this->input->post('for_apply_now'));
		$this->db->insert('videos');
		$this->session->set_flashdata('message', 'Video added successfully.');
		redirect('videos');
	}
	
	public function delete($video_id)
	{
		$this->db->where('video_id', $video_id);
		$this->db->delete('videos');
		$this->session->set_flashdata('message', 'Video deleted successfully.');
		redirect('videos');
	}
}
