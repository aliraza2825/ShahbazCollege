<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Online_application extends CI_Controller {
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
		$this->load->model('dashboards');
	}
	public function new_applications($campus_id=NULL)
	{		
		if(@$this->input->post('comment'))
		{
			$this->db->set('comment', $this->input->post('comment'));
			$this->db->set('status', 1);
			$this->db->set('last_edit', $this->input->post('last_edit'));
			$this->db->where('apply_now_id', $this->input->post('apply_now_id'));
			$this->db->update('apply_now');
			$this->session->set_flashdata('message', 'Your Request Submit Successfully.');
		}
		
		$data['admissions'] = $this->dashboards->getNewAdmisssions($campus_id);
		$data['clear_admissions'] = $this->dashboards->getNewClearAdmisssions($campus_id);
		$data['mobile_admissions'] = $this->dashboards->getNewMobileAdmisssions($campus_id);

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('online_application/new_applications', $data);
		$this->load->view('inc/footer');
	}
	
	public function pending_applications($campus_id=NULL)
	{
		if(@$this->input->post('clear_new_admission'))
		{
			$this->db->set('pending_status', 0);
			$this->db->set('status', 1);
			$this->db->where('apply_now_id', $this->input->post('apply_now_id'));
			$this->db->update('apply_now');
			$this->session->set_flashdata('message', 'Your Request Clear Submit Successfully.');
		}
		
		$data['clear_admissions'] = $this->dashboards->getPendingAdmisssions($campus_id);
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('online_application/pending_applications', $data);
		$this->load->view('inc/footer');
	}
	
	public function checked_applications()
	{
		$access = checkUserAccess();
		$class_ids = @explode(',',$access[0]['class_ids']);
		
		if(@$this->input->post('clear_new_admission'))
		{
			$this->db->set('clear_by_admin', 1);
			$this->db->where('apply_now_id', $this->input->post('apply_now_id'));
			$this->db->update('apply_now');
			$this->session->set_flashdata('message', 'Your Request Clear Submit Successfully.');
		}
		
		$data['admissions'] = $this->dashboards->getNewAdmisssions();
		$data['clear_admissions'] = $this->dashboards->getNewClearAdmisssions();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('online_application/checked_applications', $data);
		$this->load->view('inc/footer');
	}
	
	public function all_applications()
	{	
		$data['all_admissions'] = $this->dashboards->getAllAdmisssions();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('online_application/all_applications', $data);
		$this->load->view('inc/footer');
	}
	
	public function confirmed_admissions()
	{	
		$data['all_admissions'] = $this->dashboards->getAllConfirmedAdmisssions();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('online_application/confirmed_admissions', $data);
		$this->load->view('inc/footer');
	}
	
	public function add_comment()
	{
		$interest_type = $this->input->post('interest_type');
		$date = $this->input->post('date');
		$next_date_for_call = $this->input->post('next_date_for_call');
		$comment = $this->input->post('comment');
		$add_by = $this->input->post('add_by');
		
		$apply_now_id = $this->input->post('apply_now_id');
		
		$this->db->set('interest_type',$interest_type);
		if($date==1)
		{
			$this->db->set('next_date_for_call',$next_date_for_call);
		}
		else
		{
			$this->db->set('next_date_for_call','0000-00-00');
		}
		$this->db->set('comment',$comment);
		$this->db->set('add_by',$add_by);
		$this->db->set('apply_now_id',$apply_now_id);
		$this->db->set('add_date_time',date('Y-m-d H:i:s'));
		$this->db->insert('online_application_comments');
		
		
		if($interest_type=='Not Interested')
		{
			$this->db->set('pending_status','0');
			$this->db->set('status','1');
		}
		else
		{
			$this->db->set('pending_status','1');
			$this->db->set('status','0');
		}
		$this->db->set('last_edit', $add_by);
		$this->db->where('apply_now_id', $apply_now_id);
		$this->db->update('apply_now');
		
		$this->session->set_flashdata('message', 'Comment added successfully.');
		redirect('online_application/new_applications');
	}
	
	public function upload_fb_leads()
	{
		$data['campuses'] = $this->db->get_where('campuses',array('status'=>1))->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('online_application/upload_fb_leads',$data);
		$this->load->view('inc/footer');
	}
	
	public function upload_leads()
	{
		$website = $this->input->post('website');
		
		//echo $website;
		$campus_id = @$this->db->get_where('campuses',array('website'=>str_replace('/','',str_replace('https://www.','',$website))))->row()->campus_id;
		//exit();
		
		$file = fopen($_FILES['fb_file']['tmp_name'],"r");
		$row=1;
		while(! feof($file))
		{
			$index=fgetcsv($file);
			if($row!=1)
			{
				$phone = str_replace('p:+92','0',@$index[13]);
				$phone = str_replace('p:+','0',$phone);
				$phone = str_replace('p:0','0',@$phone);
				$date = date('Y-m-d H:i:s',strtotime(@$index[1]));
				if($phone!='')
				{
					$this->db->set('date',$date);
					$this->db->set('website',$website);
					$this->db->set('name',$index[12]);
					$this->db->set('fb_ad_name',$index[3]);
					$this->db->set('mobile_name',$index[12]);
					$this->db->set('campaign_name',$index[7]);
					$this->db->set('mobile',$phone);
					$this->db->set('emergency_no',$phone);
					$this->db->insert('apply_now');
				}
			}
			$row++;
		}

		fclose($file);
		$this->session->set_userdata('message','Facebook Leads Uploaded Successfully.');
		redirect('online_application/upload_fb_leads');
	}
	
	public function send_sms($campus_id,$name,$phone)
	{
		//SEND SMS
		
		$sms_gateway = $this->db->get_where('advertisement_sms_gateway', array('campus_id'=>$campus_id))->result_array();
		
		$gateway_email = $sms_gateway[0]['email'];
		$gateway_password = $sms_gateway[0]['password'];
		$gateway_device_id = $sms_gateway[0]['device_id'];
		$authToken = $gateway_api_token = $sms_gateway[0]['token'];
		
		$deviceID = $gateway_device_id;
		
		$web_data = $this->db->get_where('campuses',array('campus_id'=>$campus_id))->result_array();
		
		$message = 'Dear '.$name.'
Your Application has been submitted Successfully. We will contact you soon.
Regards
'.$web_data[0]['campus_name'];
		
		$url = "https://semysms.net/api/3/sms.php";
		
		// The data to send to the API
		$data = array(
				"phone" => $phone,
				"msg" => $message,
				"device" => $deviceID,
				"token" => $authToken
			);
		
		
		// Setup cURL
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);     
		$output = curl_exec($curl);
		curl_close($curl);
		
		$message = $web_data[0]['sms'];
		
		// The data to send to the API
		$data = array(
				"phone" => $phone,
				"msg" => $message,
				"device" => $deviceID,
				"token" => $authToken
			);
		
		// Setup cURL
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);     
		$output = curl_exec($curl);
		curl_close($curl);
	}
	
}
