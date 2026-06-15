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
		$this->ensure_dynamic_form_tables();
	}

	private function ensure_dynamic_form_tables()
	{
		$this->db->query("CREATE TABLE IF NOT EXISTS dynamic_forms (
			id INT NOT NULL AUTO_INCREMENT,
			title VARCHAR(255) NOT NULL,
			slug VARCHAR(255) NOT NULL,
			description TEXT NULL,
			status TINYINT(1) NOT NULL DEFAULT 1,
			created_by INT NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NULL,
			PRIMARY KEY (id),
			UNIQUE KEY slug (slug)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		$this->db->query("CREATE TABLE IF NOT EXISTS dynamic_form_fields (
			id INT NOT NULL AUTO_INCREMENT,
			form_id INT NOT NULL,
			label VARCHAR(255) NOT NULL,
			field_name VARCHAR(255) NOT NULL,
			field_type VARCHAR(50) NOT NULL,
			options TEXT NULL,
			is_required TINYINT(1) NOT NULL DEFAULT 0,
			row_index INT NOT NULL DEFAULT 0,
			column_width INT NOT NULL DEFAULT 12,
			sort_order INT NOT NULL DEFAULT 0,
			PRIMARY KEY (id),
			KEY form_id (form_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		if (!$this->db->field_exists('row_index', 'dynamic_form_fields')) {
			$this->db->query("ALTER TABLE dynamic_form_fields ADD row_index INT NOT NULL DEFAULT 0 AFTER is_required");
		}

		if (!$this->db->field_exists('column_width', 'dynamic_form_fields')) {
			$this->db->query("ALTER TABLE dynamic_form_fields ADD column_width INT NOT NULL DEFAULT 12 AFTER row_index");
		}

		$this->db->query("CREATE TABLE IF NOT EXISTS dynamic_form_submissions (
			id INT NOT NULL AUTO_INCREMENT,
			form_id INT NOT NULL,
			status TINYINT(1) NOT NULL DEFAULT 0,
			ip_address VARCHAR(64) NULL,
			user_agent TEXT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			KEY form_id (form_id),
			KEY status (status)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		$this->db->query("CREATE TABLE IF NOT EXISTS dynamic_form_submission_values (
			id INT NOT NULL AUTO_INCREMENT,
			submission_id INT NOT NULL,
			field_id INT NOT NULL,
			field_label VARCHAR(255) NOT NULL,
			field_name VARCHAR(255) NOT NULL,
			field_type VARCHAR(50) NOT NULL,
			value TEXT NULL,
			PRIMARY KEY (id),
			KEY submission_id (submission_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	}

	private function make_slug($text)
	{
		$slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text), '-'));
		return $slug != '' ? $slug : 'form';
	}

	private function unique_form_slug($title, $ignore_id = 0)
	{
		$base = $this->make_slug($title);
		$slug = $base;
		$i = 2;

		while (true) {
			$this->db->where('slug', $slug);
			if ($ignore_id > 0) {
				$this->db->where('id !=', $ignore_id);
			}
			$exists = $this->db->get('dynamic_forms')->row_array();
			if (!$exists) {
				return $slug;
			}
			$slug = $base . '-' . $i;
			$i++;
		}
	}

	private function get_dynamic_form_submissions($status = 0)
	{
		$this->db->select('dynamic_form_submissions.*, dynamic_forms.title as form_title, dynamic_forms.slug');
		$this->db->from('dynamic_form_submissions');
		$this->db->join('dynamic_forms', 'dynamic_forms.id = dynamic_form_submissions.form_id', 'inner');
		$this->db->where('dynamic_form_submissions.status', $status);
		$this->db->order_by('dynamic_form_submissions.id', 'DESC');
		return $this->db->get()->result_array();
	}

	private function get_dynamic_submission_values($submission_id)
	{
		return $this->db
			->where('submission_id', $submission_id)
			->order_by('id', 'ASC')
			->get('dynamic_form_submission_values')
			->result_array();
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
		$data['dynamic_submissions'] = $this->get_dynamic_form_submissions(0);

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

	public function dynamic_forms()
	{
		$data['forms'] = $this->db->order_by('id', 'DESC')->get('dynamic_forms')->result_array();

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('online_application/dynamic_forms', $data);
		$this->load->view('inc/footer');
	}

	public function form_builder($id = NULL)
	{
		$data['form'] = array();
		$data['fields'] = array();

		if ($id != NULL) {
			$data['form'] = $this->db->get_where('dynamic_forms', array('id' => $id))->row_array();
			$data['fields'] = $this->db
				->where('form_id', $id)
				->order_by('row_index', 'ASC')
				->order_by('sort_order', 'ASC')
				->get('dynamic_form_fields')
				->result_array();
		}

		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('online_application/form_builder', $data);
		$this->load->view('inc/footer');
	}

	public function save_dynamic_form()
	{
		$form_id = (int) $this->input->post('form_id');
		$title = trim($this->input->post('title'));
		$description = $this->input->post('description');
		$status = $this->input->post('status') == '1' ? 1 : 0;

		if ($title == '') {
			$this->session->set_flashdata('error', 'Form title is required.');
			redirect('online_application/form_builder/' . ($form_id > 0 ? $form_id : ''));
			return;
		}

		$slug = trim($this->input->post('slug'));
		if ($slug == '') {
			$slug = $this->unique_form_slug($title, $form_id);
		} else {
			$slug = $this->unique_form_slug($slug, $form_id);
		}

		$form_data = array(
			'title' => $title,
			'slug' => $slug,
			'description' => $description,
			'status' => $status,
			'updated_at' => date('Y-m-d H:i:s')
		);

		if ($form_id > 0) {
			$this->db->where('id', $form_id)->update('dynamic_forms', $form_data);
		} else {
			$form_data['created_by'] = $this->session->userdata('user_id');
			$form_data['created_at'] = date('Y-m-d H:i:s');
			$this->db->insert('dynamic_forms', $form_data);
			$form_id = $this->db->insert_id();
		}

		$this->db->where('form_id', $form_id)->delete('dynamic_form_fields');

		$labels = $this->input->post('field_label') ?: array();
		$types = $this->input->post('field_type') ?: array();
		$options = $this->input->post('field_options') ?: array();
		$rows = $this->input->post('field_row') ?: array();
		$widths = $this->input->post('field_width') ?: array();
		$required = $this->input->post('field_required') ?: array();

		for ($i = 0; $i < count($labels); $i++) {
			$label = trim($labels[$i]);
			if ($label == '') {
				continue;
			}

			$field_name = $this->make_slug($label);
			$this->db->insert('dynamic_form_fields', array(
				'form_id' => $form_id,
				'label' => $label,
				'field_name' => $field_name,
				'field_type' => isset($types[$i]) ? $types[$i] : 'text',
				'options' => isset($options[$i]) ? $options[$i] : '',
				'is_required' => in_array((string) $i, $required) ? 1 : 0,
				'row_index' => isset($rows[$i]) ? (int) $rows[$i] : 0,
				'column_width' => isset($widths[$i]) ? (int) $widths[$i] : 12,
				'sort_order' => $i
			));
		}

		$this->session->set_flashdata('message', 'Dynamic form saved successfully.');
		redirect('online_application/dynamic_forms');
	}

	public function delete_dynamic_form($id)
	{
		$submissions = $this->db->select('id')->where('form_id', $id)->get('dynamic_form_submissions')->result_array();
		if (!empty($submissions)) {
			$submission_ids = array_column($submissions, 'id');
			$this->db->where_in('submission_id', $submission_ids)->delete('dynamic_form_submission_values');
			$this->db->where_in('id', $submission_ids)->delete('dynamic_form_submissions');
		}
		$this->db->where('form_id', $id)->delete('dynamic_form_fields');
		$this->db->where('id', $id)->delete('dynamic_forms');
		$this->session->set_flashdata('message', 'Dynamic form deleted successfully.');
		redirect('online_application/dynamic_forms');
	}

	public function public_form($slug)
	{
		$data['form'] = $this->db->get_where('dynamic_forms', array('slug' => $slug, 'status' => 1))->row_array();

		if (!$data['form']) {
			show_404();
			return;
		}

		$data['fields'] = $this->db
			->where('form_id', $data['form']['id'])
			->order_by('row_index', 'ASC')
			->order_by('sort_order', 'ASC')
			->get('dynamic_form_fields')
			->result_array();

		$this->load->view('online_application/public_dynamic_form', $data);
	}

	public function submit_dynamic_form($slug)
	{
		$form = $this->db->get_where('dynamic_forms', array('slug' => $slug, 'status' => 1))->row_array();
		if (!$form) {
			show_404();
			return;
		}

		$fields = $this->db
			->where('form_id', $form['id'])
			->order_by('row_index', 'ASC')
			->order_by('sort_order', 'ASC')
			->get('dynamic_form_fields')
			->result_array();

		foreach ($fields as $field) {
			if ($field['is_required'] == 1 && $field['field_type'] == 'file') {
				if (empty($_FILES['field_' . $field['id']]['name'])) {
					$this->session->set_flashdata('error', $field['label'] . ' is required.');
					redirect('online_application/public_form/' . $slug);
					return;
				}
			}

			if ($field['is_required'] == 1 && $field['field_type'] != 'file') {
				$value = $this->input->post('field_' . $field['id']);
				if (is_array($value)) {
					$value = implode(',', $value);
				}
				if (trim((string) $value) == '') {
					$this->session->set_flashdata('error', $field['label'] . ' is required.');
					redirect('online_application/public_form/' . $slug);
					return;
				}
			}
		}

		$this->db->insert('dynamic_form_submissions', array(
			'form_id' => $form['id'],
			'status' => 0,
			'ip_address' => $this->input->ip_address(),
			'user_agent' => $this->input->user_agent(),
			'created_at' => date('Y-m-d H:i:s')
		));
		$submission_id = $this->db->insert_id();

		foreach ($fields as $field) {
			$value = '';

			if ($field['field_type'] == 'file') {
				if (!empty($_FILES['field_' . $field['id']]['name'])) {
					$config['upload_path'] = 'uploads/';
					$config['allowed_types'] = '*';
					$config['encrypt_name'] = TRUE;
					$this->load->library('upload', $config);
					$this->upload->initialize($config);

					if ($this->upload->do_upload('field_' . $field['id'])) {
						$upload_data = $this->upload->data();
						$value = $upload_data['file_name'];
					}
				}
			} else {
				$value = $this->input->post('field_' . $field['id']);
				if (is_array($value)) {
					$value = implode(', ', $value);
				}
			}

			$this->db->insert('dynamic_form_submission_values', array(
				'submission_id' => $submission_id,
				'field_id' => $field['id'],
				'field_label' => $field['label'],
				'field_name' => $field['field_name'],
				'field_type' => $field['field_type'],
				'value' => $value
			));
		}

		$this->session->set_flashdata('message', 'Your form has been submitted successfully.');
		redirect('online_application/public_form/' . $slug);
	}

	public function dynamic_submission_checked($submission_id)
	{
		$this->db->where('id', $submission_id)->update('dynamic_form_submissions', array('status' => 1));
		$this->session->set_flashdata('message', 'Dynamic form submission marked as checked.');
		redirect('online_application/new_applications');
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
