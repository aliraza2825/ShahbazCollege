<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Allownces extends CI_Controller {
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
		$this->load->model('account');	
	}
	
	public function index()
	{
		$data['allownces'] = $this->db->get("allownces")->result_array();
		
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('hr/define_allowance', $data);
		$this->load->view('inc/footer');
	}

	
	public function insert_allowance()
	{
		
		$campus_id = $this->input->post('allownce');
		$from_date = $this->input->post('type');
        $perc = $this->input->post('perc');

				$this->db->set('name', $campus_id);
				$this->db->set('type', $from_date);
				$this->db->set('percent', $perc);

				$this->db->insert('allownces');

			
			$this->session->set_flashdata('message', 'Inserted Successfully!');
			redirect(site_url().'/Allownces/index');

	}

    public function update_allowance()
    {

        $campus_id = $this->input->post('allownce');
        $from_date = $this->input->post('type');
        $perc = $this->input->post('perc');
        $id = $this->input->post('allid');

        $this->db->set('name', $campus_id);
        $this->db->set('type', $from_date);
        $this->db->set('percent', $perc);
        $this->db->where('id', $id);
        $this->db->update('allownces');


        $this->session->set_flashdata('message', 'Updated Successfully!');
        redirect(site_url().'/Allownces/index');

    }

}
