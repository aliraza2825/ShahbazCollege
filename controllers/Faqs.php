<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Faqs extends CI_Controller {
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
		$data['faqs'] = $this->db->get('faqs')->result_array();
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('faqs/index', $data);
		$this->load->view('inc/footer');
	}
	
	public function insert()
	{
		$this->db->set('question', $this->input->post('question'));
		$this->db->set('slug', $this->input->post('slug'));
		$this->db->set('answer', $this->input->post('answer'));
		$this->db->insert('faqs');
		$this->session->set_flashdata('message', 'FAQs added successfully.');
		redirect('faqs');
	}
}
