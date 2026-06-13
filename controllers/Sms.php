<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sms extends CI_Controller {

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
	
	public function index()
	{
		$access = checkUserAccess();
		$class_ids = @explode(',',$access[0]['class_ids']);
		
		$this->db->select('*');
		$this->db->from('classes');
		$this->db->where(array('status'=>1));
		if($this->session->userdata('role')!='Admin'){
			$this->db->where_in('class_id', $class_ids);
		}
		$data['classes'] = $this->db->get()->result_array();
		
		$data['campuses'] = $this->db->get('campuses')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('sms/send_sms', $data);
		$this->load->view('inc/footer');
	}
	
	public function advertisement_sms()
	{
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('sms/advertisement_sms');
		$this->load->view('inc/footer');
	}
	
	public function setup()
	{
		$data['campuses'] = $this->db->get('campuses')->result_array();
		$data['sms_gateways'] = $this->db->get('sms_gateway')->result_array();
		$data['advertisement_sms_gateways'] = $this->db->get('advertisement_sms_gateway')->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('sms/setup', $data);
		$this->load->view('inc/footer');
	}
	
	public function edit_sms_gateway($id)
	{
		$data['campuses'] = $this->db->get('campuses')->result_array();
		$data['sms_gateways'] = $this->db->get_where('sms_gateway', array('id'=>$id))->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('sms/edit_sms_gateway', $data);
		$this->load->view('inc/footer');
	}
	
	public function edit_advertisement_sms_gateway($id)
	{
		$data['campuses'] = $this->db->get('campuses')->result_array();
		$data['sms_gateways'] = $this->db->get_where('advertisement_sms_gateway', array('id'=>$id))->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('sms/edit_advertisement_sms_gateway', $data);
		$this->load->view('inc/footer');
	}
	
	public function gateway_update($id)
	{
		$data = $this->input->post();
		foreach(@$data as $k=>$value){
			if($k!=='campus_id'):
			$this->db->set(''.$k.'', $value);
			endif;
		}
		$this->db->where('id', $id);
		$this->db->update('sms_gateway');
		$this->session->set_flashdata('message', 'Setting updated successfully.');
		redirect('sms/setup');
	}
	
	public function advertisement_gateway_update($id)
	{
		$data = $this->input->post();
		foreach(@$data as $k=>$value){
			if($k!=='campus_id'):
			$this->db->set(''.$k.'', $value);
			endif;
		}
		$this->db->where('id', $id);
		$this->db->update('advertisement_sms_gateway');
		$this->session->set_flashdata('message', 'Setting updated successfully.');
		redirect('sms/setup');
	}
	
	public function gateway_add()
	{
		$data = $this->input->post();

		foreach(@$data as $k=>$value){
			$this->db->set(''.$k.'', $value);
		}
		$this->db->insert('sms_gateway');

		$this->session->set_flashdata('message', 'Setting updated successfully.');
		redirect('sms/setup');
	}
	
	public function advertisement_gateway_add()
	{
		$data = $this->input->post();

		foreach(@$data as $k=>$value){
			$this->db->set(''.$k.'', $value);
		}
		$this->db->insert('advertisement_sms_gateway');

		$this->session->set_flashdata('message', 'Setting updated successfully.');
		redirect('sms/setup');
	}
	
	public function all_sms()
	{
		$from_date = $this->input->post('from_date');
		$to_date = $this->input->post('to_date');
		if(@$from_date!=='' && @$to_date!='')
		{
			$data['smss'] = $this->db->get_where('sms', array('date>='=>$from_date, 'date<='=>$to_date))->result_array();
		}
		else
		{
			$data['smss'] = array();
		}
		
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('sms/all_sms', $data);
		$this->load->view('inc/footer');
	}
	
	public function device_sms($device_id)
	{
		$from_date = $this->input->post('from_date');
		$to_date = $this->input->post('to_date');
		if(@$from_date!=='' && @$to_date!='')
		{
			$data['smss'] = $this->db->get_where('sms', array('date>='=>$from_date, 'date<='=>$to_date, 'device_id'=>$device_id))->result_array();
		}
		else
		{
			$data['smss'] = array();
		}
		
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('sms/all_sms', $data);
		$this->load->view('inc/footer');
	}
	
	public function send_sms()
	{
		$type = $this->input->post('type');
		$message = $this->input->post('message');
		$add_by = $this->input->post('add_by');
		$campus_id = $this->input->post('campus_id');
		$device_id = $this->db->get_where('sms_gateway', array('campus_id'=>$campus_id))->row()->id;
		if(@!$device_id)
		{
			$this->session->set_flashdata('error', 'Campus Device not set.');
			redirect('sms');
		}
		//CUSTOME
		if($type=='custom')
		{
			$numbers = explode(' ',trim($this->input->post('number')));
		}
		//WHOLE CLASS
		elseif($type=='class')
		{
			$class_id = $this->input->post('class_id');
			$numbers = array();
			if($this->input->post('contractor_students')==1 && $this->input->post('students')==1)
			{
				$students = $this->db->get_where('students', array('class_id'=>$class_id, 'status'=>1))->result_array();
				
				foreach($students as $student)
				{
					array_push($numbers, $student['mobile']);
					array_push($numbers, $student['emergency_no']);
				}
			}
			else if($this->input->post('contractor_students')==1 && $this->input->post('students')=='')
			{
				$students = $this->db->get_where('students', array('class_id'=>$class_id, 'contractor_id!='=>0))->result_array();
				foreach($students as $student)
				{
					array_push($numbers, $student['mobile']);
					array_push($numbers, $student['emergency_no']);
				}
			}
			else if($this->input->post('contractor_students')=='' && $this->input->post('students')==1)
			{
				$students = $this->db->get_where('students', array('class_id'=>$class_id, 'contractor_id'=>0))->result_array();
				foreach($students as $student)
				{
					array_push($numbers, $student['mobile']);
					array_push($numbers, $student['emergency_no']);
				}
			}
			else
			{
				$numbers = $numbers;
			}
		}
		//PHARMACY COUNCIL
		else if($type=='council')
		{
			$numbers = array();
			
			$class = $this->input->post('class');
			$council_exam_no = $this->input->post('council_exam_no');
			
			$this->db->select('students.*');
			$this->db->from('expenses');
			$this->db->join('students','expenses.student_id=students.student_id','inner');
			$this->db->where(array('expenses.class'=>$class,'expenses.council_exam_no'=>$council_exam_no));
			$students = $this->db->get()->result_array();
			foreach($students as $student)
			{
				array_push($numbers, $student['mobile']);
				array_push($numbers, $student['emergency_no']);
			}
			
		}
		
		//WHOLE COLLEGE
		else if($type=='college')
		{
			$numbers = array();
			if($this->input->post('contractor_students')==1 && $this->input->post('students')==1)
			{
				$students = $this->db->get('students')->result_array();
				foreach($students as $student)
				{
					array_push($numbers, $student['mobile']);
					array_push($numbers, $student['emergency_no']);
				}
			}
			else if($this->input->post('contractor_students')==1 && $this->input->post('students')=='')
			{
				$students = $this->db->get_where('students', array('contractor_id!='=>0, 'status'=>1))->result_array();
				foreach($students as $student)
				{
					array_push($numbers, $student['mobile']);
					array_push($numbers, $student['emergency_no']);
				}
			}
			else if($this->input->post('contractor_students')=='' && $this->input->post('students')==1)
			{
				$students = $this->db->get_where('students', array('contractor_id'=>0))->result_array();
				foreach($students as $student)
				{
					array_push($numbers, $student['mobile']);
					array_push($numbers, $student['emergency_no']);
				}
			}
			else
			{
				$numbers = $numbers;
			}
		}
		
		$deviceID = $this->db->get_where('sms_gateway', array('id'=>$device_id))->row()->device_id;
		
		$options = array(
    					'send_at' => strtotime('+10 seconds'), // Send the message in 10 minutes
    					'expires_at' => strtotime('+1 hour') // Cancel the message in 1 hour if the message is not yet sent
						);
						
		
		foreach($numbers as $number)
		{
			$this->db->set('number', str_replace('+92','0',$number));
			$this->db->set('message', $message);
			$this->db->set('status', '');
			$this->db->set('date', date('Y-m-d H:i:s'));
			$this->db->set('chk', '0');
			$this->db->set('add_by', $add_by);
			$this->db->set('device_id', $device_id);
			$this->db->insert('sms');
		}
		$this->session->set_flashdata('message', 'Message has been sent.');
		redirect('sms');
	}
	
	public function sendMessageToNumber($to, $message, $device, $options=array()) 
	{
		$query = array_merge(array('number'=>$to, 'message'=>$message, 'device' => $device), $options);
		return $this->makeRequest('/api/v3/messages/send','POST',$query);
	}
	
	private function makeRequest ($url, $method, $fields) 
	{
		$baseUrl = "https://smsgateway.me";
		$fields['email'] = $this->email;
		$fields['password'] = $this->password;
		
		$url = $baseUrl.$url;
		
		$fieldsString = http_build_query($fields);
		
		
		$ch = curl_init();
		
		if($method == 'POST')
		{
			curl_setopt($ch,CURLOPT_POST, count($fields));
			curl_setopt($ch,CURLOPT_POSTFIELDS, $fieldsString);
		}
		else
		{
		$url .= '?'.$fieldsString;
		}
		
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_HEADER , false);  // we want headers
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		$result = curl_exec ($ch);
		
		$return['response'] = json_decode($result,true);
		
		if($return['response'] == false)
		$return['response'] = $result;
		
		$return['status'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		curl_close ($ch);
		
		return $return;
	}
	
	public function send_advertisement_sms()
	{
		$type = $this->input->post('type');
		$message = $this->input->post('message');
		$add_by = $this->input->post('add_by');
		$device_id = $this->input->post('device_id');
		//CUSTOME
		if($type=='advertisement')
		{
			$numbers = explode(' ',trim($this->input->post('number')));
		}
		
		$deviceID = $this->db->get_where('sms_gateway', array('id'=>1))->row()->device_id;
		
		$options = array(
    					'send_at' => strtotime('+10 seconds'), // Send the message in 10 minutes
    					'expires_at' => strtotime('+1 hour') // Cancel the message in 1 hour if the message is not yet sent
						);
						
		
		foreach($numbers as $number)
		{
			$this->db->set('number', $number);
			$this->db->set('message', $message);
			$this->db->set('status', '');
			$this->db->set('date', date('Y-m-d H:i:s'));
			$this->db->set('chk', '0');
			$this->db->set('add_by', $add_by);
			$this->db->set('device_id', $device_id);
			$this->db->insert('sms');
		}
		$this->session->set_flashdata('message', 'Message has been sent.');
		redirect('sms/advertisement_sms');
	}
	
	public function getPaidCouncliFeeStudents()
	{
		$campus_id = $this->input->post('campus_id');
		$council_exam_no = $this->input->post('council_exam_no');
		$class = $this->input->post('campus_class');
		if($class==1)
		{
			$class='1st';
		}
		else
		{
			$class='2nd';
		}
		
		$this->db->select('*');
		$this->db->from('payments');
		$this->db->join('students','students.student_id=payments.custom_student_id','inner');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
		$this->db->where('campuses.campus_id',$campus_id);
		$this->db->like('payments.payment_comment', 'This fee for next exam # '.$council_exam_no.' '.$class.' Year', 'both');
		$results = $this->db->get()->result_array();
		
		$html='';
		$i=1;
		foreach($results as $result)
		{
			$this->db->select('*');
			$this->db->from('students');
			$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
			$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
			$this->db->where('students.student_id', $result['custom_student_id']);
			$student_data = $this->db->get()->result_array();
			if($result['paid']==1)
			{
				$paid='Submitted';
			}
			else
			{
				$paid='Not Submitted';
			}
			$checkAddedFee = $this->db->get_where('expenses',array('student_id'=>@$student_data[0]['student_id'],'council_exam_no'=>$council_exam_no,'class'=>$this->input->post('campus_class')))->result_array();
			
			if(count($checkAddedFee)>0)
			{
				$html.='<tr class="alert-success">';
				$html.='<td>'.@$student_data[0]['name'].'</td>';
				$html.='<td>'.@$student_data[0]['first_name'].' '.@$student_data[0]['last_name'].'</td>';
				$html.='<td>'.@$this->db->get_where('contractors', array('contractor_id'=>$student_data[0]['contractor_id']))->row()->name.'</td>';
				$html.='<td>'.@$student_data[0]['cnic'].'</td>';
				$html.='<td>'.@$student_data[0]['roll_no'].'</td>';
				$html.='<td>'.$result['payment_comment'].'</td>';
				$html.='<td>'.$result['amount'].'</td>';
				$html.='<td>'.$result['add_by'].'</td>';
				$html.='<td>'.$paid.'</td>';
				$html.='</tr>';
				$i++;
			}
			
		}
		echo $html;
	}
	
}
