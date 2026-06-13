<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Events extends CI_Controller {

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
	
	public function add_event()
	{
		$data['campuses'] = $this->clas->getCampuses();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('events/add', $data);
		$this->load->view('inc/footer');
	}
	
	public function edit_event($event_id)
	{
		$data['campuses'] = $this->clas->getCampuses();
		$data['events'] = $this->db->get_where('events', array('event_id'=>$event_id))->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('events/edit', $data);
		$this->load->view('inc/footer');
	}
	
	public function insert()
	{	
		$campus_ids = $this->input->post('campus_ids');
		$campus_ids = implode(',',$campus_ids);
		
		$this->db->set('campus_ids', $campus_ids);
		$this->db->set('name', $this->input->post('name'));
		$this->db->set('show_on_website', $this->input->post('show_on_website'));
		$this->db->insert('events');
		
		$this->session->set_flashdata('message', 'Event Added Successfully.');
		redirect('events/add_event');
	}
	
	public function update($event_id)
	{	
		$campus_ids = $this->input->post('campus_ids');
		$campus_ids = implode(',',$campus_ids);
		
		$this->db->set('campus_ids', $campus_ids);
		$this->db->set('name', $this->input->post('name'));
		$this->db->set('show_on_website', $this->input->post('show_on_website'));
		$this->db->where('event_id', $event_id);
		$this->db->update('events');
		
		$this->session->set_flashdata('message', 'Event Updated Successfully.');
		redirect('events/edit_event/'.$event_id);
	}
	
	public function all_events()
	{
		$data['events'] = $this->db->get('events')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('events/all_events', $data);
		$this->load->view('inc/footer');
	}
	
	public function delete($id)
	{
		if($this->session->userdata('role')!='Admin')
		{
			$access = checkUserAccess();
			$campus_ids = @explode(',',$access[0]['campus_ids']);
			
			$current_event = $this->db->get_where('events', array('event_id'=>$id))->result_array();
			$campus_id_events = explode(',',$current_event[0]['campus_ids']);
			
			foreach($campus_ids as $campus_id):
				if (($key = array_search($campus_id, $campus_id_events)) !== false) {
					unset($campus_id_events[$key]);
				}
			endforeach;
			
			$this->db->set('campus_ids', implode(',', $campus_id_events));
			$this->db->where('event_id', $id);
			$this->db->update('events');
			$this->session->set_flashdata('message', 'Your Event has been removed.');
		
			redirect('events/all_events');
		}
		else
		{
			$this->db->where('event_id', $id);
			$this->db->delete('events');
			
			$this->session->set_flashdata('message', 'Your Event has been removed.');
			
			redirect('events/all_events');
		}
	}
}
