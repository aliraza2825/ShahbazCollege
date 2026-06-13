<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Locations extends CI_Controller {
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
	
	public function check()
	{	
		if(@$this->input->post('from_date') && @$this->input->post('to_date'))
		{
			$this->db->select('*');
			$this->db->from('locations');
			$this->db->join('users','users.user_id=locations.user_id','inner');
			$data['locations'] = $this->db->get()->result_array();
		}
		else
		{
			$data['locations']= array();
		}
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('locations/check',$data);
		$this->load->view('inc/footer');
	}
	
}