<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Profile extends CI_Controller {
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
		$this->load->model('profiles');
	}
	
	public function index()
	{
		$user_id = $this->session->userdata('user_id');
		$data['users'] = $this->profiles->getCurrentUser($user_id);
		
		$this->load->view('inc/header.php');
		$this->load->view('inc/sidebar.php');
		$this->load->view('profile/profile.php', $data);
		$this->load->view('inc/footer.php');
	}
	
	public function update()
	{
		$user_id = $this->session->userdata('user_id');
		if($this->input->post('password') == $this->input->post('r-password')){
			$password = md5($this->input->post('password'));
			$data = array(
					'first_name' => $this->input->post('first_name'),
					'last_name' => $this->input->post('last_name'),
					'email' => $this->input->post('email'),
					'username' => $this->input->post('username'),
					'password' => $password
					);
			$this->profiles->updateCurrentUser($data);
			$this->session->set_flashdata('message', 'Your profile update successfully!');
			redirect('profile');
		}else{
			$this->session->set_flashdata('error', 'Your password and retype password didn\'t match!');
			redirect('profile');
		}
		
		$data = $this->input->post();
		$this->clas->updateClass($data);
		$this->session->set_flashdata('message', 'Class updated successfully!');
		redirect('classes/edit_class/'.$id);
	}
	
}
