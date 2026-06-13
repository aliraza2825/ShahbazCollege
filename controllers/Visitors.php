<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Visitors extends CI_Controller {
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
		$this->load->model('visitor');	}
	
	public function insert()
	{
		$data = $this->input->post();
		$this->visitor->storeVisitor($data);
		$this->sendSMStoVisitor($data);
		$this->session->set_flashdata('message', 'Visitor added successfully!');
		redirect('visitors/add_visitor');
	}
	
	public function sendSMStoVisitor($data)
	{
		$number = $data['phone'];
		$campus = $this->db->get_where('campuses', array('campus_name'=>$data['campus']))->result_array();
		$campus_number = $campus[0]['phone3'];
		
		$message = 'Dear '.$data['name'].'
Welcome to '.$data['campus'].'. For further inquiry kindly contact us at '.$campus_number.'

From 
'.$data['campus'].'';
		$this->db->set('number', $number);
		$this->db->set('message', $message);
		$this->db->set('status', '');
		$this->db->set('date', date('Y-m-d'));
		$this->db->set('chk', '0');
		$this->db->set('add_by', 'System');
		$this->db->insert('sms');
	}
	
	public function update($id)
	{
		$data = $this->input->post();
		$this->visitor->updateVisitor($data);
		$this->session->set_flashdata('message', 'Visitor updated successfully!');
		redirect('visitors/edit_visitor/'.$id);
	}
	
	public function delete($id)
	{
		/*$this->clas->deleteClass($id);
		$this->session->set_flashdata('message', 'Class deleted successfully!');
		redirect('classes/all_classes');*/
	}
	
	public function index()
	{
		/*$data['classes'] = $this->clas->getTeacherClasses();
	//	$data['count'] = $this->clas->getTeacherClasses();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('classes/all_classes', $data);
		$this->load->view('inc/footer');*/
	}
	
	public function add_visitor()
	{
		$data['campuses'] = $this->visitor->getCampus();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('visitors/add_visitor', $data);
		$this->load->view('inc/footer');
	}
	
	public function edit_visitor($id)
	{
		$data['visitors'] = $this->visitor->getVisitor($id);
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('visitors/edit_visitor', $data);
		$this->load->view('inc/footer');
	}
	
	public function all_visitors()
	{
		$data['visitors'] = $this->visitor->getVisitors();
		
    
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('visitors/all_visitors', $data);
		$this->load->view('inc/footer');
	}
}
