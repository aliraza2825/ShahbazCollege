<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Reminders extends CI_Controller {
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
		//$this->load->model('account');	
	}
	
	public function add_reminder()
	{
		$data['campuses'] = $this->db->get('campuses')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('reminders/add_reminder', $data);
		$this->load->view('inc/footer');
	}
	
	public function all_reminders()
	{
		$data['reminder_rules'] = $this->db->get('reminders')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('reminders/all_reminders', $data);
		$this->load->view('inc/footer');
	}
	
	public function all_pending_reminder($campus_id=NULL)
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['campus_ids']);
		
		$this->db->select('reminder.*');
		$this->db->from('reminder');
		$this->db->join('users','users.user_id=reminder.user_id','INNER');
		$this->db->join('campuses','users.campus_id=campuses.campus_id','INNER');
		if($campus_id!=NULL)
		{
			$this->db->where('campuses.campus_id',$campus_id);
		}
		$this->db->where('reminder.check_by_admin',0);
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campuses.campus_id', $campus_ids);
		}
		$data['reminders'] = $this->db->get()->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('reminders/all_pending_reminder', $data);
		$this->load->view('inc/footer');
	}
	
	public function all_completed_reminder()
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['campus_ids']);
		
		$this->db->select('reminder.*');
		$this->db->from('reminder');
		$this->db->join('users','users.user_id=reminder.user_id','INNER');
		$this->db->join('campuses','users.campus_id=campuses.campus_id','INNER');
		if(@$campus_ids!=NULL)
		{
			$this->db->where('campuses.campus_id',$campus_ids);
		}
		$this->db->where('reminder.check_by_admin',1);
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campuses.campus_id', $campus_ids);
		}
		$data['reminders'] = $this->db->get()->result_array();
		
		//$data['reminders'] = $this->db->get_where('reminder',array('check_by_admin'=>1))->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('reminders/all_completed_reminder', $data);
		$this->load->view('inc/footer');
	}
	
	public function delete_reminder_rule($reminder_id)
	{
		$this->db->where('reminder_id',$reminder_id);
		$this->db->delete('reminders');
		
		$this->session->set_flashdata('message','Reminder Deleted Successfully!');
		redirect('reminders/all_reminders');
	}
	
	public function delete_reminder($reminder_id)
	{
		$this->db->where('reminder_id',$reminder_id);
		$this->db->delete('reminder');
		
		$this->session->set_flashdata('message','Reminder Deleted Successfully!');
		redirect('reminders/all_pending_reminder');
	}
	
	public function getUsers()
	{
		$campus_id = $this->input->post('campus_id');
		$users = $this->db->get_where('users',array('campus_id'=>$campus_id,'status'=>1))->result_array();
		$html='';
		$i=1;
		foreach($users as $user)
		{
			$html.='<div class="clearfix"></div><label class="checkbox-inline"><input class="topic_id" type="checkbox" id="inlineCheckbox'.$i.'" name="user_ids[]" value="'.$user['user_id'].'" /> '.$user['first_name'].' '.$user['last_name'].'. </label><br />';
			$i++;
		}
		echo $html;
		exit();
	}
	
	public function update($reminder_id)
	{
		$this->db->set('check_by_admin',1);
		$this->db->where('reminder_id',$reminder_id);
		$this->db->update('reminder');
		
		$this->session->set_flashdata('message', 'Updated Successfully.');
		redirect('reminders/all_pending_reminder');
	}
	
	public function insert_reminder()
	{
		//load the helper
		$this->load->helper('form');

		//Configure
		//set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
		$config['upload_path'] = 'reminder_images/';
		
    	// set the filter image types
		$config['allowed_types'] = 'gif|jpg|png';
		
		//load the upload library
		$this->load->library('upload', $config);
    
		$this->upload->initialize($config);
		
		$this->upload->set_allowed_types('*');

		$data['upload_data'] = '';
    
		//if not successful, set the error message
		if (!$this->upload->do_upload('image')) {
			$data = array('msg' => $this->upload->display_errors());
			$image = '';

		} 
		else 
		{ 
			//else, set the success message
      		$data['upload_data'] = $this->upload->data();
			if($data['upload_data']['file_name']){
				$image = $data['upload_data']['file_name'];
			}
		}
		
		//GET VARIABLES
		$campus_id = $this->input->post('campus_id');
		$this->db->where_in('user_id',$this->input->post('user_ids'));
		$users = $this->db->get('users')->result_array();
		$type = $this->input->post('type');
		$note = $this->input->post('note');
		
		if($type=='once')
		{
			$once_date = $this->input->post('once_date');
			foreach($users as $user)
			{
				/*$this->db->set('type',$type);
				$this->db->set('user_id',$user['user_id']);
				$this->db->set('once_date',$once_date);
				$this->db->set('note',$note);
				$this->db->set('image',$image);
				$this->db->set('add_by',$this->session->userdata('name'));
				$this->db->insert('reminders');*/
				
				$this->db->set('user_id',$user['user_id']);
				$this->db->set('date',$once_date);
				$this->db->set('note',$note);
				$this->db->set('image',$image);
				$this->db->set('add_by',$this->session->userdata('name'));
				$this->db->set('status','Pending');
				$this->db->insert('reminder');
			}
		}
		if($type=='daily')
		{
			foreach($users as $user)
			{
				$this->db->set('type',$type);
				$this->db->set('user_id',$user['user_id']);
				$this->db->set('note',$note);
				$this->db->set('image',$image);
				$this->db->set('add_by',$this->session->userdata('name'));
				$this->db->insert('reminders');
			}
		}
		if($type=='weekly')
		{
			$weekly_days = $this->input->post('weekly_days');
			foreach($weekly_days as $weekly_day)
			{
				foreach($users as $user)
				{
					$this->db->set('type',$type);
					$this->db->set('user_id',$user['user_id']);
					$this->db->set('weekly_days',$weekly_day);
					$this->db->set('note',$note);
					$this->db->set('image',$image);
					$this->db->set('add_by',$this->session->userdata('name'));
					$this->db->insert('reminders');
				}
			}
		}
		if($type=='monthly')
		{
			$monthly_dates = $this->input->post('monthly_dates');
			foreach($monthly_dates as $monthly_date)
			{
				foreach($users as $user)
				{
					$this->db->set('type',$type);
					$this->db->set('user_id',$user['user_id']);
					$this->db->set('monthly_dates',$monthly_date);
					$this->db->set('note',$note);
					$this->db->set('image',$image);
					$this->db->set('add_by',$this->session->userdata('name'));
					$this->db->insert('reminders');
				}
			}
		}
		if($type=='yearly')
		{
			$yearly_date = $this->input->post('yearly_date');
			$yearly_month = $this->input->post('yearly_month');
			foreach($users as $user)
			{
				$this->db->set('type',$type);
				$this->db->set('user_id',$user['user_id']);
				$this->db->set('yearly_date',$yearly_date);
				$this->db->set('yearly_month',$yearly_month);
				$this->db->set('note',$note);
				$this->db->set('image',$image);
				$this->db->set('add_by',$this->session->userdata('name'));
				$this->db->insert('reminders');
			}
		}
		
		$this->session->set_flashdata('message','Reminder Assigned Successfully!');
		redirect('reminders/add_reminder');
	}
	
	public function createreminder()
	{
		$reminders = $this->db->get('reminders')->result_array();
		$today_date = date('Y-m-d');
		$today_day = date('l');
		$today_date_number = date('d');
		$month = date('F');
		foreach($reminders as $reminder)
		{
			
			//CHECK DAILY Reminder
			if($reminder['type']=='daily')
			{
				$this->db->set('user_id',$reminder['user_id']);
				$this->db->set('date',$today_date);
				$this->db->set('note',$reminder['note']);
				$this->db->set('image',$reminder['image']);
				$this->db->set('add_by',$reminder['add_by']);
				$this->db->set('status','Pending');
				$this->db->insert('reminder');
			}
			//CHECK WEEKLY Reminder
			if($reminder['type']=='weekly')
			{
				if($reminder['weekly_days']==$today_day)
				{
					$this->db->set('user_id',$reminder['user_id']);
					$this->db->set('date',$today_date);
					$this->db->set('note',$reminder['note']);
					$this->db->set('image',$reminder['image']);
					$this->db->set('add_by',$reminder['add_by']);
					$this->db->set('status','Pending');
					$this->db->insert('reminder');
				}
			}
			//CHECK MONTHLY Reminder
			if($reminder['type']=='monthly')
			{
				if($reminder['monthly_dates']==$today_date_number)
				{
					$this->db->set('user_id',$reminder['user_id']);
					$this->db->set('date',$today_date);
					$this->db->set('note',$reminder['note']);
					$this->db->set('image',$reminder['image']);
					$this->db->set('add_by',$reminder['add_by']);
					$this->db->set('status','Pending');
					$this->db->insert('reminder');
				}
			}
			//CHECK YEARLY Reminder
			if($reminder['type']=='yearly')
			{
				if($reminder['yearly_date']==$today_date_number && $reminder['yearly_month']==$month)
				{
					$this->db->set('user_id',$reminder['user_id']);
					$this->db->set('date',$today_date);
					$this->db->set('note',$reminder['note']);
					$this->db->set('image',$reminder['image']);
					$this->db->set('add_by',$reminder['add_by']);
					$this->db->set('status','Pending');
					$this->db->insert('reminder');
				}
			}
		}
	}
	
}
