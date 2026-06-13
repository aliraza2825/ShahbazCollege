<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Autosms extends CI_Controller {

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
		echo 'Something';
	}

	public function send_sms()
	{
		$type = $this->input->post('type');
		$message = $this->input->post('message');
		//CUSTOME
		if($type=='custom')
		{
			$numbers = explode(',',trim($this->input->post('number')));
		}
		//WHOLE CLASS
		elseif($type=='class')
		{
			$class_id = $this->input->post('class_id');
			$numbers = array();
			if($this->input->post('contractor_students')==1 && $this->input->post('students')==1)
			{
				$students = $this->db->get_where('students', array('class_id'=>$class_id))->result_array();
				
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
				$students = $this->db->get_where('students', array('contractor_id!='=>0))->result_array();
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
		
		$deviceID = 51380;
		
		$options = array(
    					'send_at' => strtotime('+10 seconds'), // Send the message in 10 minutes
    					'expires_at' => strtotime('+1 hour') // Cancel the message in 1 hour if the message is not yet sent
						);
		foreach($numbers as $number)
		{
			$result = $this->sendMessageToNumber($number, $message, $deviceID);
			if(count($result['response']['result']['success'])>0)
			{
				$this->db->set('number', $result['response']['result']['success'][0]['contact']['number']);
				$this->db->set('message', $result['response']['result']['success'][0]['message']);
				$this->db->set('status', 'send');
				$this->db->set('date', date('Y-m-d'));
				$this->db->insert('sms');
			}
			else
			{
				$this->db->set('number', $result['response']['result']['fails'][0]['number']);
				$this->db->set('message', $result['response']['result']['fails'][0]['message']);
				$this->db->set('status', 'failed');
				$this->db->set('date', date('Y-m-d'));
				$this->db->insert('sms');
			}
			sleep(10);
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
	
	function autosmsbysystem()
	{
		$today_date = date_create(date('Y-m-d'));
		$unpaidStudentsDues = $this->db->group_by('student_id')->get_where('payments', array('paid'=>0, 'contractor_id'=>0))->result_array();
		
		foreach($unpaidStudentsDues as $unpaidStudentDues)
		{
			$fee_dead_line = date_create($unpaidStudentDues['dead_line']);
			$diff=date_diff($today_date,$fee_dead_line);
			$remaining_days = $diff->format("%R%a days");
			
			if($remaining_days=='+0 days')
			{
				$currentStudent = $this->db->get_where('students', array('student_id'=>$unpaidStudentDues['student_id'], 'status'=>1))->result_array();
				if(count($currentStudent)>0)
				{
					$mobile 			= $currentStudent[0]['mobile'];
					$emergency_number 	= $currentStudent[0]['emergency_no'];
					$first_name 		= $currentStudent[0]['first_name'];
					$last_name 			= $currentStudent[0]['last_name'];				
					$student_id 		= $currentStudent[0]['student_id'];
					$roll_number 		= $currentStudent[0]['roll_no'];
					
					$this->db->select('*');
					$this->db->from('students');
					$this->db->join('classes', 'students.class_id=classes.class_id', 'INNER');
					$this->db->join('sms_gateway', 'classes.campus_id=sms_gateway.campus_id', 'INNER');
					$this->db->where('students.student_id', $student_id);
					$getDeviceID = $this->db->get()->result_array();
					$DeviceID = $getDeviceID[0]['id'];
					if($DeviceID==NULL)
					{
						//PEHLE 1 tha 0 baad me kiya hai
						$DeviceID=0;
					}
					$accountant_phone = $getDeviceID[0]['phone1'];
                    $unpaidStudent_payment = $this->db->get_where('payments', array('paid'=>0, 'student_id'=>$unpaidStudentDues['student_id'], 'dead_line <= '=> date("Y-m-d"), 'challan_no != '=> $unpaidStudentDues['challan_no']))->result_array();

                    $text = '\n'."Dead Line : ".$unpaidStudentDues['dead_line'].'\n'."Amount : ".$unpaidStudentDues['amount'].'\n';
                    foreach ($unpaidStudent_payment as $pay)
                    {
                        $text.="Challan no : ".$pay['challan_no'].'\n'."Amount : ".$pay['amount'].'\n';
                    }
					$message = 'Dear '.$first_name.' '.$last_name.' ('.$roll_number.'),

Today is the last day of your fee installment '.$text.'. Kindly submit your fee otherwise fine will be charged.

For Further Details
'.$accountant_phone.'';
					
					$this->db->set('number', $mobile);
					$this->db->set('message', $message);
					$this->db->set('status', '');
					$this->db->set('date', date('Y-m-d H:i:s'));
					$this->db->set('chk', 0);
					$this->db->set('add_by', 'System');
					$this->db->set('device_id', $DeviceID);
					$this->db->insert('sms');
				}
			}
			elseif($remaining_days=='+1 days' || $remaining_days=='+2 days' || $remaining_days=='+3 days' || $remaining_days=='+4 days' || $remaining_days=='+5 days')
			{
				$currentStudent = $this->db->get_where('students', array('student_id'=>$unpaidStudentDues['student_id'], 'status'=>1))->result_array();
				if(count($currentStudent)>0)
				{
					$mobile 			= $currentStudent[0]['mobile'];
					$emergency_number 	= $currentStudent[0]['emergency_no'];
					$first_name 		= $currentStudent[0]['first_name'];
					$last_name 			= $currentStudent[0]['last_name'];				
					$student_id 		= $currentStudent[0]['student_id'];
					$roll_number 		= $currentStudent[0]['roll_no'];
					
					$this->db->select('*');
					$this->db->from('students');
					$this->db->join('classes', 'students.class_id=classes.class_id', 'INNER');
					$this->db->join('sms_gateway', 'classes.campus_id=sms_gateway.campus_id', 'INNER');
					$this->db->where('students.student_id', $student_id);
					$getDeviceID = $this->db->get()->result_array();
					$DeviceID = $getDeviceID[0]['id'];
					if($DeviceID==NULL)
					{
						//PEHLE 1 tha 0 baad me kiya hai
						$DeviceID=0;
					}
					$accountant_phone = $getDeviceID[0]['phone1'];
                    $unpaidStudent_payment = $this->db->get_where('payments', array('paid'=>0, 'student_id'=>$unpaidStudentDues['student_id'], 'dead_line <= '=> date("Y-m-d"), 'challan_no != '=> $unpaidStudentDues['challan_no']))->result_array();

                    $text = '\n'."Challan no : ".$unpaidStudentDues['dead_line'].'\n'."Amount : ".$unpaidStudentDues['amount'].'\n';
                    foreach ($unpaidStudent_payment as $pay)
                    {
                        $text.="Challan no : ".$pay['challan_no'].'\n'."Amount : ".$pay['amount'].'\n';
                    }
					
					$message = 'Dear '.$first_name.' '.$last_name.' ('.$roll_number.'),

Submit your fee of Rs '.$text.' as your last date of submitting '.$unpaidStudentDues['dead_line'].' has arrived. Submit your fee before due date ('.str_replace('+','',$remaining_days).' left).

For Further Details
'.$accountant_phone.'';
					
					$this->db->set('number', $mobile);
					$this->db->set('message', $message);
					$this->db->set('status', '');
					$this->db->set('date', date('Y-m-d H:i:s'));
					$this->db->set('chk', 0);
					$this->db->set('add_by', 'System');
					$this->db->set('device_id', $DeviceID);
					$this->db->insert('sms');
				}
			}
			elseif($remaining_days=='-1 days' || $remaining_days=='-2 days' || $remaining_days=='-3 days' || $remaining_days=='-4 days' || $remaining_days=='-5 days')
			{
				$currentStudent = $this->db->get_where('students', array('student_id'=>$unpaidStudentDues['student_id'], 'status'=>1))->result_array();
				if(count($currentStudent)>0)
				{
					$mobile 			= $currentStudent[0]['mobile'];
					$emergency_number 	= $currentStudent[0]['emergency_no'];
					$first_name 		= $currentStudent[0]['first_name'];
					$last_name 			= $currentStudent[0]['last_name'];				
					$student_id 		= $currentStudent[0]['student_id'];
					$roll_number 		= $currentStudent[0]['roll_no'];
					
					$this->db->select('*');
					$this->db->from('students');
					$this->db->join('classes', 'students.class_id=classes.class_id', 'INNER');
					$this->db->join('sms_gateway', 'classes.campus_id=sms_gateway.campus_id', 'INNER');
					$this->db->where('students.student_id', $student_id);
					$getDeviceID = $this->db->get()->result_array();
					$DeviceID = $getDeviceID[0]['id'];
					if($DeviceID==NULL)
					{
						//PEHLE 1 tha 0 baad me kiya hai
						$DeviceID=0;
					}
					$accountant_phone = $getDeviceID[0]['phone1'];
                    $unpaidStudent_payment = $this->db->get_where('payments', array('paid'=>0, 'student_id'=>$unpaidStudentDues['student_id'], 'dead_line <= '=> date("Y-m-d"), 'challan_no != '=> $unpaidStudentDues['challan_no']))->result_array();

                    $text = '\n'."Challan no : ".$unpaidStudentDues['dead_line'].'\n'."Amount : ".$unpaidStudentDues['amount'].'\n';
                    foreach ($unpaidStudent_payment as $pay)
                    {
                        $text.="Challan no : ".$pay['challan_no'].'\n'."Amount : ".$pay['amount'].'\n';
                    }
					
					$message = 'Dear '.$first_name.' '.$last_name.' ('.$roll_number.'),

Submission date of your dues '.$text.' is over, now your are charging fine of Rs 50 per day. 

For Further Details
'.$accountant_phone.'';
					
					$this->db->set('number', $mobile);
					$this->db->set('message', $message);
					$this->db->set('status', '');
					$this->db->set('date', date('Y-m-d H:i:s'));
					$this->db->set('chk', 0);
					$this->db->set('add_by', 'System');
					$this->db->set('device_id', $DeviceID);
					$this->db->insert('sms');
				}
			}
			elseif($remaining_days=='-6 days')
			{
				$currentStudent = $this->db->get_where('students', array('student_id'=>$unpaidStudentDues['student_id'], 'status'=>1))->result_array();
				if(count($currentStudent)>0)
				{
					$mobile 			= $currentStudent[0]['mobile'];
					$emergency_number 	= $currentStudent[0]['emergency_no'];
					$first_name 		= $currentStudent[0]['first_name'];
					$last_name 			= $currentStudent[0]['last_name'];				
					$student_id 		= $currentStudent[0]['student_id'];
					$roll_number 		= $currentStudent[0]['roll_no'];
					
					$this->db->select('*');
					$this->db->from('students');
					$this->db->join('classes', 'students.class_id=classes.class_id', 'INNER');
					$this->db->join('sms_gateway', 'classes.campus_id=sms_gateway.campus_id', 'INNER');
					$this->db->where('students.student_id', $student_id);
					$getDeviceID = $this->db->get()->result_array();
					$DeviceID = $getDeviceID[0]['id'];
					if($DeviceID==NULL)
					{
						//PEHLE 1 tha 0 baad me kiya hai
						$DeviceID=0;
					}
					$accountant_phone = $getDeviceID[0]['phone1'];
                    $unpaidStudent_payment = $this->db->get_where('payments', array('paid'=>0, 'student_id'=>$unpaidStudentDues['student_id'], 'dead_line <= '=> date("Y-m-d"), 'challan_no != '=> $unpaidStudentDues['challan_no']))->result_array();

                    $text = '\n'."Challan no : ".$unpaidStudentDues['dead_line'].'\n'."Amount : ".$unpaidStudentDues['amount'].'\n';
                    foreach ($unpaidStudent_payment as $pay)
                    {
                        $text.="Challan no : ".$pay['challan_no'].'\n'."Amount : ".$pay['amount'].'\n';
                    }
					
					$message = 'Dear '.$first_name.' '.$last_name.' ('.$roll_number.'),

Submit your fee '.$text.' within 3 days otherwise your name will be struck off.

For Further Details
'.$accountant_phone.'';
					
					$this->db->set('number', $mobile);
					$this->db->set('message', $message);
					$this->db->set('status', '');
					$this->db->set('date', date('Y-m-d H:i:s'));
					$this->db->set('chk', 0);
					$this->db->set('add_by', 'System');
					$this->db->set('device_id', $DeviceID);
					$this->db->insert('sms');
				}
			}
			elseif($remaining_days=='-7 days')
			{
				$currentStudent = $this->db->get_where('students', array('student_id'=>$unpaidStudentDues['student_id'], 'status'=>1))->result_array();
				if(count($currentStudent)>0)
				{
					$mobile 			= $currentStudent[0]['mobile'];
					$emergency_number 	= $currentStudent[0]['emergency_no'];
					$first_name 		= $currentStudent[0]['first_name'];
					$last_name 			= $currentStudent[0]['last_name'];				
					$student_id 		= $currentStudent[0]['student_id'];
					$roll_number 		= $currentStudent[0]['roll_no'];
					
					$this->db->select('*');
					$this->db->from('students');
					$this->db->join('classes', 'students.class_id=classes.class_id', 'INNER');
					$this->db->join('sms_gateway', 'classes.campus_id=sms_gateway.campus_id', 'INNER');
					$this->db->where('students.student_id', $student_id);
					$getDeviceID = $this->db->get()->result_array();
					$DeviceID = $getDeviceID[0]['id'];
					if($DeviceID==NULL)
					{
						//PEHLE 1 tha 0 baad me kiya hai
						$DeviceID=0;
					}
					$accountant_phone = $getDeviceID[0]['phone1'];
                    $unpaidStudent_payment = $this->db->get_where('payments', array('paid'=>0, 'student_id'=>$unpaidStudentDues['student_id'], 'dead_line <= '=> date("Y-m-d"), 'challan_no != '=> $unpaidStudentDues['challan_no']))->result_array();

                    $text = '\n'."Challan no : ".$unpaidStudentDues['dead_line'].'\n'."Amount : ".$unpaidStudentDues['amount'].'\n';
                    foreach ($unpaidStudent_payment as $pay)
                    {
                        $text.="Challan no : ".$pay['challan_no'].'\n'."Amount : ".$pay['amount'].'\n';
                    }
					
					$message = 'Dear '.$first_name.' '.$last_name.' ('.$roll_number.'),

Submit your fee '.$text.' within 2 days otherwise your name will be struck off.

For Further Details
'.$accountant_phone.'';
					
					$this->db->set('number', $mobile);
					$this->db->set('message', $message);
					$this->db->set('status', '');
					$this->db->set('date', date('Y-m-d H:i:s'));
					$this->db->set('chk', 0);
					$this->db->set('add_by', 'System');
					$this->db->set('device_id', $DeviceID);
					$this->db->insert('sms');
				}
			}
			elseif($remaining_days=='-8 days')
			{
				$currentStudent = $this->db->get_where('students', array('student_id'=>$unpaidStudentDues['student_id'], 'status'=>1))->result_array();
				if(count($currentStudent)>0)
				{
					$mobile 			= $currentStudent[0]['mobile'];
					$emergency_number 	= $currentStudent[0]['emergency_no'];
					$first_name 		= $currentStudent[0]['first_name'];
					$last_name 			= $currentStudent[0]['last_name'];				
					$student_id 		= $currentStudent[0]['student_id'];
					$roll_number 		= $currentStudent[0]['roll_no'];
					
					$this->db->select('*');
					$this->db->from('students');
					$this->db->join('classes', 'students.class_id=classes.class_id', 'INNER');
					$this->db->join('sms_gateway', 'classes.campus_id=sms_gateway.campus_id', 'INNER');
					$this->db->where('students.student_id', $student_id);
					$getDeviceID = $this->db->get()->result_array();
					$DeviceID = $getDeviceID[0]['id'];
					if($DeviceID==NULL)
					{
						//PEHLE 1 tha 0 baad me kiya hai
						$DeviceID=0;
					}
					$accountant_phone = $getDeviceID[0]['phone1'];
                    $unpaidStudent_payment = $this->db->get_where('payments', array('paid'=>0, 'student_id'=>$unpaidStudentDues['student_id'], 'dead_line <= '=> date("Y-m-d"), 'challan_no != '=> $unpaidStudentDues['challan_no']))->result_array();

                    $text = '\n'."Challan no : ".$unpaidStudentDues['dead_line'].'\n'."Amount : ".$unpaidStudentDues['amount'].'\n';
                    foreach ($unpaidStudent_payment as $pay)
                    {
                        $text.="Challan no : ".$pay['challan_no'].'\n'."Amount : ".$pay['amount'].'\n';
                    }
					
					$message = 'Dear '.$first_name.' '.$last_name.' ('.$roll_number.'),

Submit your fee '.$text.' within 1 days otherwise your name will be struck off.

For Further Details
'.$accountant_phone.'';
					
					$this->db->set('number', $mobile);
					$this->db->set('message', $message);
					$this->db->set('status', '');
					$this->db->set('date', date('Y-m-d H:i:s'));
					$this->db->set('chk', 0);
					$this->db->set('add_by', 'System');
					$this->db->set('device_id', $DeviceID);
					$this->db->insert('sms');
				}
			}
			elseif($remaining_days=='-9 days')
			{
				$currentStudent = $this->db->get_where('students', array('student_id'=>$unpaidStudentDues['student_id'], 'status'=>1))->result_array();
				if(count($currentStudent)>0)
				{
					$mobile 			= $currentStudent[0]['mobile'];
					$emergency_number 	= $currentStudent[0]['emergency_no'];
					$first_name 		= $currentStudent[0]['first_name'];
					$last_name 			= $currentStudent[0]['last_name'];				
					$student_id 		= $currentStudent[0]['student_id'];
					$roll_number 		= $currentStudent[0]['roll_no'];
					
					$this->db->select('*');
					$this->db->from('students');
					$this->db->join('classes', 'students.class_id=classes.class_id', 'INNER');
					$this->db->join('sms_gateway', 'classes.campus_id=sms_gateway.campus_id', 'INNER');
					$this->db->where('students.student_id', $student_id);
					$getDeviceID = $this->db->get()->result_array();
					$DeviceID = $getDeviceID[0]['id'];
					if($DeviceID==NULL)
					{
						//PEHLE 1 tha 0 baad me kiya hai
						$DeviceID=0;
					}
					$accountant_phone = $getDeviceID[0]['phone1'];
                    $unpaidStudent_payment = $this->db->get_where('payments', array('paid'=>0, 'student_id'=>$unpaidStudentDues['student_id'], 'dead_line <= '=> date("Y-m-d"), 'challan_no != '=> $unpaidStudentDues['challan_no']))->result_array();

                    $text = '\n'."Challan no : ".$unpaidStudentDues['dead_line'].'\n'."Amount : ".$unpaidStudentDues['amount'].'\n';
                    foreach ($unpaidStudent_payment as $pay)
                    {
                        $text.="Challan no : ".$pay['challan_no'].'\n'."Amount : ".$pay['amount'].'\n';
                    }
					
					$message = 'Dear '.$first_name.' '.$last_name.' ('.$roll_number.'),

This is the last date of your fee '.$text.' submission warning. You can submit your fee today with fine, otherwise your name will be struck off tomorrow.

For Further Details
'.$accountant_phone.'';
					
					$this->db->set('number', $mobile);
					$this->db->set('message', $message);
					$this->db->set('status', '');
					$this->db->set('date', date('Y-m-d H:i:s'));
					$this->db->set('chk', 0);
					$this->db->set('add_by', 'System');
					$this->db->set('device_id', $DeviceID);
					$this->db->insert('sms');
				}
			}
			elseif($remaining_days=='-10 days')
			{
				$currentStudent = $this->db->get_where('students', array('student_id'=>$unpaidStudentDues['student_id'], 'status'=>1))->result_array();
				if(count($currentStudent)>0) {
                    $unpaidStudent_payment = $this->db->get_where('payments', array('paid' => 0, 'student_id' => $unpaidStudentDues['student_id'], 'dead_line <= ' => date("Y-m-d")))->result_array();
                    if (count($unpaidStudent_payment) == 3) {
                        $mobile = $currentStudent[0]['mobile'];
                        $emergency_number = $currentStudent[0]['emergency_no'];
                        $first_name = $currentStudent[0]['first_name'];
                        $last_name = $currentStudent[0]['last_name'];
                        $student_id = $currentStudent[0]['student_id'];
                        $roll_number = $currentStudent[0]['roll_no'];

                        $this->db->select('*');
                        $this->db->from('students');
                        $this->db->join('classes', 'students.class_id=classes.class_id', 'INNER');
                        $this->db->join('sms_gateway', 'classes.campus_id=sms_gateway.campus_id', 'INNER');
                        $this->db->where('students.student_id', $student_id);
                        $getDeviceID = $this->db->get()->result_array();
                        $DeviceID = $getDeviceID[0]['id'];
                        if ($DeviceID == NULL) {
                            //PEHLE 1 tha 0 baad me kiya hai
                            $DeviceID = 0;
                        }
                        $accountant_phone = $getDeviceID[0]['phone1'];

                        $message = 'Dear ' . $first_name . ' ' . $last_name . ' (' . $roll_number . '),

You are struck off from college.

For Further Details
' . $accountant_phone . '';

                        $this->db->set('number', $mobile);
                        $this->db->set('message', $message);
                        $this->db->set('status', '');
                        $this->db->set('date', date('Y-m-d H:i:s'));
                        $this->db->set('chk', 0);
                        $this->db->set('add_by', 'System');
                        $this->db->set('device_id', $DeviceID);
                        $this->db->insert('sms');
                    }
                }elseif (count($unpaidStudent_payment) > 3)
                {


                }
				else
                {


                }
			}
			else
			{
				//DO NOTHING HERE
			}
			echo 'Success';
		}
	}

}
