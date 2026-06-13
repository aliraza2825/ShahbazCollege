<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Api extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();
	
	}
	
	public function verify_student()
	{	
		//echo 'success';
		$roll_no = $this->input->post('roll_no');
		$password = md5($this->input->post('password'));
		
		$this->db->select("students.*,courses.course_name,classes.name as class_name,campuses.campus_name as campus_name,(select image from student_documents where student_documents.student_id=students.student_id and student_documents.type = 'Photo') as profile_image");
		$this->db->from('students');
		$this->db->join('courses','courses.course_id=students.course_id','INNER');
		$this->db->join('classes','classes.class_id=students.class_id','INNER');
		$this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
		$this->db->where(array('students.roll_no'=>$roll_no,'students.password'=>$password,'students.status'=>1));
		$active_student = $this->db->get()->result_array();
		
		$this->db->select('students.*,courses.course_name,classes.name as class_name,campuses.campus_name as campus_name,campuses.phone as campus_phone');
		$this->db->from('students');
		$this->db->join('courses','courses.course_id=students.course_id','INNER');
		$this->db->join('classes','classes.class_id=students.class_id','INNER');
		$this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
		$this->db->where(array('students.roll_no'=>$roll_no,'students.password'=>$password,'students.status'=>0));
		$deactive_student = $this->db->get()->result_array();
		
				$this->db->select('courses.*');
				$this->db->from('courses');
				$this->db->join('fee_rules','fee_rules.course_id=courses.course_id','INNER');
				$this->db->group_by('courses.course_id');
				$courses = $this->db->get()->result_array();
		
		
		if(count($active_student)>0)
		{
			$this->db->get_where('payments',array('student_id'=>$active_student[0]['student_id'],'dead_line>'=>date('Y-m-d'),'paid'=>0))->result_array();
			
			
			$this->db->select('slider_images.*');
			$this->db->from('slider_images');
			$slider_images = $this->db->get()->result_array();
			
			
			$result = array(
				'status'=>'approved',
				'response_code'=>'1',
				'message'=>'allow login',
				'sliderimages'=>$slider_images,
				'courses'=>$courses,
				'response'=>$active_student
			);		   
			echo json_encode($result);
		}
		elseif(count($deactive_student)>0)
		{
			$result=array(
				'status'=>'approved',
				'response_code'=>'2',
				'message'=>'Your are struck off from college. For further details make a call at '.$deactive_student[0]['campus_phone'].''
			);			 
			echo json_encode($result);
		}
		else
		{
			$result=array(
				'status'=>'approved',
				'response_code'=>'3',
				'message'=>'Wrong Email Or Password'
			);                        
			echo json_encode($result);
		}
	}
	
	
	public function forgetpassword()
	{
		$cnic = $this->input->post('cnic');
		$check_record = $student = $this->db->get_where('students',array('cnic'=>$cnic))->result_array();
		if(count($check_record)>0)
		{
			$passcode_date = $check_record[0]['passcode_date'];
			$today_date = date('Y-m-d');
			if($passcode_date<$today_date)
			{
				$passcode = $this->getpasscode();
				
				$this->db->set('passcode',$passcode);
				$this->db->set('passcode_date',$today_date);
				$this->db->where('cnic',$cnic);
				$this->db->update('students');
				
				//SEND PASSCODE
				
				$authToken = $this->db->get_where('sms_gateway', array('id'=>1))->row()->token;
				$deviceID  = $this->db->get_where('sms_gateway', array('id'=>1))->row()->device_id;
				
				// The data to send to the API
				
				$url = "https://semysms.net/api/3/sms.php";
				
				$data = array(
					"phone" => $student[0]['mobile'],
					"msg" => 'Dear '.$student[0]['first_name'].' '.$student[0]['last_name'].' ('.$student[0]['roll_no'].') You have submitted password change request. Your passcode is '.$passcode.'',
					"device" => $deviceID,
					"token" => $authToken
				);
			
				$curl = curl_init($url);
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);     
				$output = curl_exec($curl);
				curl_close($curl);
				
				$result=array(
				'status'=>'Sent',
				'response_code'=>'1',
				'message'=>'Passcode sent on your registered mobile number. Kindly enter the passcode to reset your password.'
				
			);			 
			echo json_encode($result);
			}
			else
			{
				
				$result=array(
				'status'=>'Already Sent',
				'response_code'=>'2',
				'message'=>'Passcode is already sent on your registered mobile number. Enter passcode to reset your password.'
				
			);			 
			echo json_encode($result);
			}
		}
		else{
			
			$result=array(
				'status'=>'ERROR',
				'response_code'=>'3',
				'message'=>'Your entered CNIC number is invalid. Kindly type correct CNIC number'
				
			);			 
			echo json_encode($result);
			}
		}
		
	public function getpasscode()
	{
		$passcode = RAND(100000,999999);
		$checker = $this->db->get_where('students',array('passcode'=>$passcode))->result_array();
		if(count($checker)>0)
		{
			$this->getpasscode();
		}
		return $passcode;
	}
	
	
	public function checkotp()
	{
		$passcode = $this->input->post('passcode');
		
		
		
			
			$pass=$this->db->get_where('students',array('passcode'=>$passcode))->result_array();
			
			if(count($pass)>0){
			
			
					$today_date = date('Y-m-d');
					$student = $this->db->get_where('students',array('passcode'=>$passcode,'passcode_date'=>$today_date))->result_array();
					
					if(count($student)>0)
					{
						$result=array(
						'status'=>'SUCCESS',
						'response_code'=>'1',
						'message'=>$student
						
					);			 
					echo json_encode($result);
					}
					else
					{
						
						$result=array(
						'status'=>'ERROR',
						'response_code'=>'2',
						'message'=>'Your entered passcode has been expired. Please generate new passcode.'
						
					);			 
					echo json_encode($result);
					}
					
			}else{
				
				
						$result=array(
						'status'=>'ERROR',
						'response_code'=>'3',
						'message'=>'Wrong PASSCODE'
						
					);			 
					echo json_encode($result);
				
				
			}
	}
	
	
	public function change_password()
	{
		$passcode = $this->input->post('passcode');
		$password = $this->input->post('password');
		
			$this->db->set('password',md5($password));
			$this->db->where('passcode',$passcode);
			$this->db->update('students');
			
			$today_date = date('Y-m-d');
			$student = $this->db->get_where('students',array('passcode'=>$passcode))->result_array();
			
			if(count($student)>0)
			{
				$this->db->set('passcode','');
				$this->db->where('passcode',$passcode);
				$this->db->update('students');
				
				$this->session->set_flashdata('success','Your password has been changed. Your Roll number is '.$student[0]['roll_no']);
				$result=array(
						'status'=>'SUCCESS',
						'response_code'=>'1',
						'message'=>'Your password has been changed. Your Roll number is '.$student[0]['roll_no'].''
						
					);			 
					echo json_encode($result);
				
			}
			else
			{
				    $result=array(
						'status'=>'ERROR',
						'response_code'=>'2',
						'message'=>'Wrong PASSCODE'
						
					);			 
					echo json_encode($result);
				
			}
		
	}
	
	
	public function send_sms(){
		
		
		        $deviceID = $this->input->post('deviceid');
				
				$this->db->limit(50);
				$getMessageDetails = $this->db->get_where('sms',array('chk'=>0))->result_array();
			
				//$sql_get_sms = 'SELECT * FROM sms WHERE chk=0 AND device_id="'.$deviceID.'" ORDER BY sms_id ASC LIMIT 50';
				//$getMessageDetails = $this->db->query($sql_get_sms)->result_array();
				$postData=array();
				//$getMessageDetails = mysqli_fetch_array($getMessageDetails);
				$i=0;
				foreach($getMessageDetails as $row)
				{
					
					
					$postData[$i]['phone'] = $row['number'];
					$postData[$i]['message'] = $row['message'];
					$postData[$i]['smsid'] = $row['sms_id'];
					
					
					
					
					/*---------------------------MY NEW CODE END--------------------*/
					
					
					//$update_sms_query = 'UPDATE sms SET status="send",chk=1 WHERE sms_id='.$sms_id;
				//	mysqli_query($conn, $update_sms_query);
				
				$this->db->set('status','send');
				$this->db->set('chk','1');
				$this->db->where('sms_id',$row['sms_id']);
				$this->db->update('sms');
				
				$i++;
				}
				
				echo json_encode($postData,TRUE);
			
					
		
		
	}
	
	
	public function guestlogin(){
		
		
		$name = $this->input->post('name');
		$phone = $this->input->post('phone');
		$address = $this->input->post('address');
		$qual = $this->input->post('qual');
		$cnic = $this->input->post('cnic');
		
		
		
		$this->db->from('guest');		
		$this->db->where(array('guest.cnic'=>$cnic));
		$alreadyfound = $this->db->get()->result_array();
		if(count($alreadyfound)>0)
		{
			
			
				$this->db->select('slider_images.*');
				$this->db->from('slider_images');
				$slider_images = $this->db->get()->result_array();
				
				
				$this->db->select('courses.*');
				$this->db->from('courses');
				$courses = $this->db->get()->result_array();
			
			
			
				$result = array(
				'status'=>'FOUND',
				'response_code'=>'2',
				'message'=>'ALREADY FOUND',
				'sliderimages'=>$slider_images,
				'courses'=>$courses,
				'response'=>$alreadyfound
				);		   
				echo json_encode($result);

		}else 
		{

				$this->db->set(array(
				'name'=>$name,
				'phone'=>$phone,
				'address'=>$address,
				'qualification'=>$qual,
				'cnic'=>$cnic));
				$results=$this->db->insert('guest');
				$insert_id = $this->db->insert_id();
				
				
				$this->db->select('slider_images.*');
				$this->db->from('slider_images');
				$slider_images = $this->db->get()->result_array();
				
				
				$this->db->select('courses.*');
				$this->db->from('courses');
				$courses = $this->db->get()->result_array();
				

				if($results){
					
					$result = array(
					'status'=>'SUCCESS',
					'response_code'=>'1',
					'message'=>'LOGGED IN AS GUEST',
					'sliderimages'=>$slider_images,
					'courses'=>$courses,
					'response'=>$insert_id
					);		   
					echo json_encode($result);
					
				}else{
					
					
					$result = array(
					'status'=>'SUCCESS',
					'response_code'=>'2',
					'message'=>'LOGGED IN AS GUEST',
					'sliderimages'=>$slider_images,
					'courses'=>$courses,
					'response'=>$insert_id
					);		   
					echo json_encode($result);
					
					
				}

		}			
					
		
		
	}
	
	
	public function getsubjects(){
		
		
		$user_id = $this->input->post('user_id');
		$courseID = $this->input->post('courseID');
		
		$this->db->select('*');
		$this->db->from('save_results');
		$this->db->where(array('student_id'=>$user_id));
		$all_questions = $this->db->get()->result_array();
		$quests="";
		
		if(count($all_questions)>0){
			
			foreach($all_questions as $que){
				
				if($quests == ""){
					$quests.=$que['right_answers'];
				}else{
					
					$quests.=(",".$que['right_answers']);
				}
				
			}
			
			
		}
		    
			
		    $this->db->select('chapters.*,chapters.*,course_subjects.*');
			$this->db->from('course_subjects');
            $this->db->join('chapters','chapters.course_subject_id=course_subjects.course_subject_id','INNER');

			$this->db->where( array('course_subjects.course_id'=>$courseID) )
                ->or_like(array('course_subjects.extra_course_ids'=>','.$courseID.','))
                ->or_like(array('course_subjects.extra_course_ids'=>','.$courseID))
                ->or_like(array('course_subjects.extra_course_ids'=>$courseID.','));
			$this->db->group_by('chapters.chapter_id');
			$data= $this->db->get()->result_array();

            foreach ($data as $key=>$ab)
            {
				
                $this->db->select('count(*) as total_questions');
                $this->db->from('questions');
                $this->db->join('topics','topics.topic_id=questions.topic_id','INNER');

                $this->db->where('topics.chapter_id', $ab['chapter_id']);
				if($quests != ""){
					
                    $this->db->where('questions.question_id not in('.$quests.')');
					
                }
               
                $count= $this->db->get()->result_array();
				
				
				if(count($count)>0)
					$data[$key]['questionsCount'] = $count[0]['total_questions'];
				else
					$data[$key]['questionsCount'] = 0;				

				
            }
            
			
		
		    $result = array(
					'status'=>'SUCCESS',
					'response_code'=>'1',
					'message'=>'LOGGED IN AS GUEST',
					'response'=>$data
					);		   
			echo json_encode($result);
		
	}
	
	
	public function get_questions()
	{
		
		$topic_id = $this->input->post('topic_id');
		$user_id = $this->input->post('user_id');
		$user_type = $this->input->post('user_type');
		$exam_mode = $this->input->post('exam_mode');
		$limit = $this->input->post('limit');
		
		$this->db->select('*');
		$this->db->from('save_results');
		$this->db->where(array('student_id'=>$user_id,'topic_id '=>$topic_id));
		$all_questions = $this->db->get()->result_array();
		$quests="";
		
		if(count($all_questions)>0){
			
			foreach($all_questions as $que){
				
				if($quests == ""){
					$quests.=$que['right_answers'];
				}else{
					
					$quests.=(",".$que['right_answers']);
				}
				
			}
			
			
		}


        $this->db->select('*');
        $this->db->from('topics');
        $this->db->where(array('chapter_id'=>$topic_id));
        $all_topics = $this->db->get()->result_array();

        $arr = array();
	    foreach ($all_topics as $top)
        {
            array_push($arr,$top['topic_id']);

        }
	
		//GET ALL QUESTIONS AND INSERT IN DATABASE
		$this->db->select('*');
		$this->db->from('questions');
		if($quests != ""){
			
			$this->db->where('question_id not in('.$quests.')');
			
		}
        $this->db->where_in('topic_id', $arr);
		$this->db->where(array( 'option_1!='=>'', 'option_2!='=>'', 'option_3!='=>'', 'option_4!='=>''));
		
		$this->db->order_by('rand()');
		$this->db->limit($limit);
		$all_questions = $this->db->get()->result_array();
		
		
		foreach($all_questions as $question)
		{
			$questions_array[$question['question_id']] = $question;
			$questions_array[$question['question_id']]['my_answer'] = '';
		}
	
		$this->db->set('user_id', $user_id);
		$this->db->set('topic_id', $topic_id);
		$this->db->set('quiz_mode', $exam_mode);
		$this->db->set('user_type', $user_type);
		$this->db->set('result', json_encode($questions_array));
		$inserted = $this->db->insert('online_test_results_test');
		$insert_id = $this->db->insert_id();
				
		if($inserted){
			
			$result = array(
					'status'=>'SUCCESS',
					'response_code'=>'1',
					'result_id'=>$insert_id,
					'response'=>$all_questions
					);	
			
			echo json_encode($result);
			
		}else{
			
			$result = array(
					'status'=>'ERROR',
					'response_code'=>'2',
					'result_id'=>'',
					'response'=>'Something Went Wrong'
					);	
			
			echo json_encode($result);
			
		}
		
		
	}
		
	
	
	
	
	
	
	public function update()
	{
		$user_id = $this->input->post('user_id');
		$data = $this->input->post('data');
		$resultid = $this->input->post('resultid');
		$timespent = $this->input->post('timespent');
		$right_ans = $this->input->post('right_ans');
		
		
		$this->db->select('*');
		$this->db->from('online_test_results_test');
		$this->db->where('user_id', $user_id);
		$this->db->order_by('online_test_result_id', 'DESC');
		$this->db->limit(1);
		$questions = $this->db->get()->result_array();
		
		
		$this->db->set('result', $data);
		$this->db->set('timespent', $timespent);
		$this->db->where('online_test_result_id', $resultid);
		$updated=$this->db->update('online_test_results_test');
		
		
		if($updated){
			
			if($right_ans != ""){
			$this->db->set('topic_id', $questions[0]['topic_id']);
			$this->db->set('number', '1');
			$this->db->set('student_id', $user_id);
			$this->db->set('right_answers', $right_ans);
			$this->db->insert('save_results');
			}
			
			$result = array(
					'status'=>'SUCCESS',
					'response_code'=>'1',
					'response'=>'Marks updated'
					);	
			
			echo json_encode($result);
			
		}else{
			
			$result = array(
					'status'=>'ERROR',
					'response_code'=>'2',
					'response'=>'Something Went Wrong'
					);	
			
			echo json_encode($result);
			
		}
		
		
	}
	
	
	public function get_results_user()
	{
	
	        $user_id = $this->input->post('user_id');
		
		    $this->db->select('online_test_results_test.*,topics.topic_name,course_subjects.subject_name');
			$this->db->from('online_test_results_test');
			$this->db->join('topics','topics.topic_id=online_test_results_test.topic_id','INNER');
			$this->db->join('course_subjects','topics.course_subject_id=course_subjects.course_subject_id','INNER');
			$this->db->where( array('online_test_results_test.user_id'=>$user_id));
			
			$data= $this->db->get()->result_array();
		
		
		    $result = array(
					'status'=>'SUCCESS',
					'response_code'=>'1',
					'message'=>'Result Data',
					'response'=>$data
					);		   
					echo json_encode($result);
		
	
	
	}

	
	public function Mark_attendance()
	{
	
	        $user_id = $this->input->post('user_id');
	        $campus_id = $this->input->post('campus');
		
		    $this->db->set('user_id',$user_id);
		    $this->db->set('machine_user_id',$user_id);
		    $this->db->set('campus_code',$campus_id);
			$updated=$this->db->insert('attendence');
			
		
		    if($updated){
		
		    $result = array(
					'status'=>'SUCCESS',
					'response_code'=>'1',
					'message'=>'Result Data',
					'response'=>'Added Successfully'
					);		   
					echo json_encode($result);
		
	
			}else{
				
				$result = array(
					'status'=>'FAILURE',
					'response_code'=>'2',
					'message'=>'Something Wrong',
					'response'=>$data
					);		   
					echo json_encode($result);
				
			}
	
	}


	public function get_shortquestions()
	{
		
		$topic_id = $this->input->post('topic_id');
		$user_id = $this->input->post('user_id');
		$user_type = $this->input->post('user_type');
		$exam_mode = $this->input->post('exam_mode');
		$limit = $this->input->post('limit');
		
		
		$this->db->select('*');
        $this->db->from('topics');
        $this->db->where(array('chapter_id'=>$topic_id));
        $all_topics = $this->db->get()->result_array();

        $arr = array();
	    foreach ($all_topics as $top)
        {
            array_push($arr,$top['topic_id']);

        }
		
		
		
	
		//GET ALL QUESTIONS AND INSERT IN DATABASE
		$this->db->select('*');
		$this->db->from('questions');
		$this->db->where_in('topic_id', $arr);
		$this->db->where(array('type' => 'short-question'));
		$this->db->order_by('rand()');
		$this->db->limit($limit);
		$all_questions = $this->db->get()->result_array();
		
		
		foreach($all_questions as $question)
		{
			$questions_array[$question['question_id']] = $question;
			$questions_array[$question['question_id']]['my_answer'] = '';
		}
	
		$this->db->set('user_id', $user_id);
		$this->db->set('topic_id', $topic_id);
		$this->db->set('quiz_mode', $exam_mode);
		$this->db->set('user_type', $user_type);
		$this->db->set('result', json_encode($questions_array));
		$inserted = $this->db->insert('online_test_results_test');
		$insert_id = $this->db->insert_id();
				
		if($inserted){
			
			$result = array(
					'status'=>'SUCCESS',
					'response_code'=>'1',
					'result_id'=>$insert_id,
					'response'=>$all_questions
					);	
			
			echo json_encode($result);
			
		}else{
			
			$result = array(
					'status'=>'ERROR',
					'response_code'=>'2',
					'result_id'=>'',
					'response'=>'Something Went Wrong'
					);	
			
			echo json_encode($result);
			
		}
		
		
	}


	public function check_student()
	{	
		//echo 'success';
		$roll_no = $this->input->post('cnic');
		
		$this->db->select('students.*,courses.course_name,classes.name as class_name,campuses.campus_name as campus_name,student_documents.online_image as profile_image');
		$this->db->from('students');
		$this->db->join('courses','courses.course_id=students.course_id','INNER');
		$this->db->join('student_documents','student_documents.student_id=students.student_id','INNER');
		$this->db->join('classes','classes.class_id=students.class_id','INNER');
		$this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
		$this->db->where(array('students.cnic'=>$roll_no,'students.status'=>1,'student_documents.type' =>'Photo'));
		$active_student = $this->db->get()->result_array();
		
	
		
		if(count($active_student)>0)
		{
			
			
			$result = array(
				'status'=>'FOUND',
				'response_code'=>'1',
				'message'=>'allow login',
				'response'=>$active_student
			);		   
			echo json_encode($result);
		}
		
		else
		{
			$result=array(
				'status'=>'ERROR',
				'response_code'=>'3',
				'message'=>'No User Found'
			);                        
			echo json_encode($result);
		}
	}

	
	public function check_staff()
	{	
		//echo 'success';
		$roll_no = $this->input->post('cnic');
		
	
		
		$this->db->select("users.*,campuses.campus_name as campus_name, (select image from teacher_documents where teacher_id=users.user_id 
		and type = 'Photo') as profile_image");
		$this->db->from('users');
		$this->db->join('campuses','users.campus_id=campuses.campus_id','INNER');
		$this->db->where('users.cnic = ',$roll_no);
		$active_student = $this->db->get()->result_array();
		
	
		
		if(count($active_student)>0)
		{
			
			
			$result = array(
				'status'=>'FOUND',
				'response_code'=>'1',
				'message'=>'allow login',
				'response'=>$active_student
			);		   
			echo json_encode($result);
		}
		
		else
		{
			$result=array(
				'status'=>'ERROR',
				'response_code'=>'3',
				'message'=>'No User Found'
			);                        
			echo json_encode($result);
		}
	}


	public function add_signature(){
		
		$user_id = $this->input->post('user_id');
		$type = $this->input->post('type');
		
		 //load the helper
        $this->load->helper('form');

        //Configure
        //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
        $config['upload_path'] = 'uploads/';

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
		
		if($type == 'Staff'){
			
			$this->db->set('teacher_id', $user_id);
			$this->db->set('image', $image);
			$this->db->set('type', 'sign');
			$updated=$this->db->insert('teacher_documents');
		
		}else {
			
			$this->db->set('student_id', $user_id);
			$this->db->set('image', $image);
			$this->db->set('type', 'sign');
			$updated=$this->db->insert('student_documents');
			
		}
		
		
		
		
		if($updated){
			
			$result = array(
					'status'=>'SUCCESS',
					'response_code'=>'1',
					'response'=>'Document Inserted'
					);	
			
			echo json_encode($result);
			
		}else{
			
			$result = array(
					'status'=>'ERROR',
					'response_code'=>'2',
					'response'=>'Something Went Wrong'
					);	
			
			echo json_encode($result);
			
		}
		
		
	}


	public function fee_details(){
		
		$student_id=$this->input->post('user_id');
		
		$this->db->select('payments.*,students.total_fee');
		$this->db->from('payments');
		$this->db->join('students','students.student_id = payments.student_id','inner');
		$this->db->where('payments.student_id', $student_id);
		$this->db->order_by('payments.dead_line', 'asc');
		$active_student = $this->db->get()->result_array();
		if(count($active_student)>0)
		{
			
			$qry = "SELECT SUM(amount) as total_paid FROM `payments` WHERE student_id = ".$student_id." AND payment_plan != 'consulation fee' AND payment_comment = 'College Fee'";
		    $discount = $this->db->query($qry)->result_array();
			$discount = $discount[0]['total_paid'];
			
			
			$qry = "SELECT SUM(amount) as paid_fee FROM `payments` WHERE student_id = ".$student_id." AND payment_plan != 'consulation fee' AND payment_comment = 'College Fee' AND paid = 1";
			$paid_fee = $this->db->query($qry)->result_array();
			$paid_fee = $paid_fee[0]['paid_fee'];
		
			 $this->db->select_sum('amount');
				$this->db->from('payments');
				$this->db->where(array('student_id'=>$student_id,'payment_plan!='=>'consulation fee'));
				$all_payments = $this->db->get()->result_array();

				//ALL PAID PAYMENTS
				$this->db->select_sum('amount');
				$this->db->from('payments');
				$this->db->where(array('student_id'=>$student_id,'payment_plan!='=>'consulation fee','paid'=>1));
				$all_paid_payments = $this->db->get()->result_array();

				$remaining_fee = $all_payments[0]['amount']-$all_paid_payments[0]['amount'];
				
				
				
				 $payments = $this->db->get_where('payments',array('student_id'=>$student_id))->result_array();
					$fine=0;
					foreach($payments as $payment)
					{
						$fine+=$payment['extra_amount'];
						if($payment['paid']==1)
						{
							$fine+=$payment['actual_amount']-$payment['amount'];
						}
						else
						{
							$challan_date = date_create($payment['dead_line']);
							$today_date = date_create(date('Y-m-d'));
							$diff=date_diff($challan_date,$today_date);
							$difference = $diff->format("%R%a");

							if($difference>0)
							{
								$fee_fine = $difference*50;
							}
							else
							{
								$fee_fine = 0;
							}
							$fine+=$fee_fine;
						}
					}
				$total_fine = $fine;
			
			
			$result = array(
				'status'=>'FOUND',
				'response_code'=>'1',
				'message'=>'fee_structure',
				'total_fee'=>$discount,
				'paid_fee'=> $paid_fee,
				'remaining_fee'=> $remaining_fee,
				'total_fine'=> $total_fine,
				'response'=>$active_student
			);		   
			echo json_encode($result);
		}
		
		else
		{
			$result=array(
				'status'=>'ERROR',
				'response_code'=>'2',
				'message'=>'No Fee Structure Found'
			);                        
			echo json_encode($result);
		}
		
		
	}


	public function get_assignments(){
		
		//GET STUDENT ASSIGNMENTS
		$student_id=$this->input->post('user_id');
		$student_details = $this->db->get_where('students',array('student_id'=>$student_id))->result_array();
		$student_class = $student_details[0]['section'];
		if($student_class=='First Year')
		{
			$student_class=1;
		}
		else
		{
			$student_class=2;
		}
		$course_id = $student_details[0]['course_id'];
		$date = date('Y-m-d');
				
		
		$this->db->select('asg.*,course_subjects.subject_name,(select checked from assignment_results where student_id = '.$student_id.' and assignment_id = asg.assignment_id) as status');
		$this->db->from('assignments asg');
		$this->db->join('course_subjects','course_subjects.course_subject_id = asg.subject_id','inner');
		$this->db->where("asg.class = '".$student_class."' and asg.course_id = '".$course_id."'");
		$this->db->order_by('asg.end_date', 'DESC');
		$assignments = $this->db->get()->result_array();
		
	
		
		if(count($assignments)>0)
		{
			
			$result = array(
				'status'=>'FOUND',
				'response_code'=>'1',
				'message'=>'Assignment List',
				'response'=>$assignments
			);		   
			echo json_encode($result);
		}
		
		else
		{
			$result=array(
				'status'=>'ERROR',
				'response_code'=>'3',
				'message'=>'No assignments Found'
			);                        
			echo json_encode($result);
		}
		
		
		
	}


	public function view_assignment(){
		
		$assignment_id=$this->input->post('assignment_id');
		
		$questions = "";
        $practicals = "";
		
		$assignment = $this->db->get_where('assignments',array('assignment_id'=>$assignment_id))->result_array();
		
		$mc=$assignment[0]['mcqs'];
		$sq=$assignment[0]['short_questions'];
		$pq=$assignment[0]['practicals'];

		$mcqs = $this->db->get_where('questions','question_id in ('.$mc.')')->result_array();
		
		if($sq != ""){
		
			$questions = $this->db->get_where('questions','question_id in ('.$sq.')')->result_array();
			
			$mcqs = array_merge($mcqs,$questions);
		
		}
		if($pq != ""){
		
			$practicals = $this->db->get_where('practicals','practical_id in ('.$pq.')')->result_array();
		
	
		}
       
		$result = array(
		
            'status'=>'FOUND',
            'response_code'=>'2',
            'message'=>'FOUND',
            'mcqs'=>$mcqs,
            'practicals'=>$practicals
			
        );
        echo json_encode($result);		
		
	}


	public function get_courses(){

        $this->db->select('courses.*');
        $this->db->from('courses');
        $this->db->where('courses.status = 1');
        $courses = $this->db->get()->result_array();

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'2',
            'message'=>'ALREADY FOUND',
            'courses'=>$courses,
            'response'=>"Found"
        );
        echo json_encode($result);


    }
	
	public function get_course_fee_plans($course_id){

        $this->db->select('*');
        $this->db->from('fee_rules');
        $this->db->join('courses','courses.course_id = fee_rules.course_id','left');
        $this->db->where('fee_rules.course_id = "'.$course_id.'"');
        $plans = $this->db->get()->result_array();

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'2',
            'message'=>'ALREADY FOUND',
            'plans'=>$plans,
            'response'=>"Found"
        );
        echo json_encode($result);


    }
	
	
	public function verify_user()
    {


        $username = $this->input->post('username');
        $password = md5($this->input->post('password'));

        $this->db->select("users.*,designations.*,departments.*");
        $this->db->from('users');
        $this->db->join('designations','designations.designation_id=users.designation_id','INNER');
        $this->db->join('departments','departments.department_id=users.department_id','INNER');
        $this->db->where(array('users.username'=>$username,'users.password'=>$password,'users.status'=>1));
        $active_student = $this->db->get()->result_array();



        if(count($active_student)>0)
        {
            $result = array(
                'status'=>'success',
                'response_code'=>'1',
                'message'=>'allow login',
                'response'=>$active_student
            );
            echo json_encode($result);
        }

        else
        {
            $result=array(
                'status'=>'error',
                'response_code'=>'2',
                'message'=>'Wrong Username Or Password'
            );
            echo json_encode($result);
        }
    }
	
	
	public function get_subjects(){

		$student_id=$this->input->post('course_id');
		
        $this->db->select('course_subjects.*');
        $this->db->from('course_subjects');
        $this->db->where("course_subjects.status = 1 and course_subjects.course_id = '".$student_id."'");
        $courses = $this->db->get()->result_array();

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'2',
            'message'=>'ALREADY FOUND',
            'courses'=>$courses,
            'response'=>"Found"
        );
        echo json_encode($result);


    }
	public function mark_machine_attendance()
	{ 
		
			
			#TURNING ERRORS ON
			ini_set('error_reporting',E_ALL);
			ini_set('display_errors',1);

			#SECURE HASH FOR REQUEST AUTHENTICITY
			$request_hash	=	"uS669WAeetw4bQ8";

			#THIS RETRIEVES DATA FROM VT BIOMETRIC MACHINE POST REQUEST
			
			
				$post_response	=	json_encode($this->input->raw_input_stream);
				
				$post_response = '';
				$output = '';
				parse_str(urldecode($this->input->raw_input_stream), $output);
				$post_response = $output;
				
				$post_response=json_decode($post_response['attendance']);
				
				
			
				#DATA VARIABLES, THESE CAN BE USED TO GET DATA FOR FURTHER PROCESSING
				$machine_id				= $post_response->device_id;
				$attendance_time		= $post_response->time;
				$user_bio_id			= $post_response->bio_id;

				$campus = $this->db->where('name',$post_response->device_id)->get('attendance_machine')->row()->campus_id;
			
				$this->db->set('time',$attendance_time);
				$this->db->set('machine_user_id',$user_bio_id);
				$this->db->set('machine_id',$machine_id);
				$this->db->set('campus_code',$campus);
				$this->db->insert('attendence');
			
			
					$result = array(
						'status'=>'Success',
						'response_code'=>'1',
						'message'=>'Attendance marked'
					);
					echo json_encode($result);
				
	}
	
	public function get_college_results()
	{

		$student_id = $this->input->post('user_id');

        $this->db->select('collegepapers.*,collegepaper_results.*,classes.name as class_name,campuses.campus_name, courses.course_name,students.roll_no,students.first_name,students.last_name');
			$this->db->from('collegepaper_results');
			$this->db->join('collegepapers','collegepapers.collegepaper_id=collegepaper_results.collegepaper_id','inner');
			$this->db->join('students','students.student_id=collegepaper_results.student_id','inner');
			$this->db->join('classes','students.class_id=classes.class_id','inner');
			$this->db->join('campuses','campuses.campus_id=classes.campus_id','inner');
			$this->db->join('courses','courses.course_id=students.course_id','inner');
			
			if($student_id!='')
			{
				$this->db->where('collegepaper_results.student_id',$student_id);
			}
			
		$plans = $this->db->get()->result_array();
		
		
		foreach($plans as $key=>$result)
		{
			$subjects = $this->db->where_in('course_subject_id',explode(',',$result['subject_id']))->get('course_subjects')->result_array();
			
			$subjects_names = array();

			foreach($subjects as $subject)
			{
				
				array_push($subjects_names,$subject['subject_name']);
				
				
			}
										
			$plans[$key]['subject_names'] = $subjects_names;
			
		}
		

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'FOUND',
            'results'=>$plans,
            'response'=>"Found"
        );
        echo json_encode($result);


    }
	
	public function get_payment_plan()
	{

		$id = $this->input->post('user_id');

       $this->db->select('*');
		$this->db->from('payments');
		$this->db->where('student_id', $id);
		$this->db->where('merged_challan IS NOT NULL and actual_amount > 0');
		$this->db->group_by("CASE WHEN merged_challan IS NOT NULL THEN merged_challan else '' end",false);
		$query = $this->db->get()->result_array();

        $this->db->select('*');
        $this->db->from('payments');
        $this->db->where('student_id', $id);
        $this->db->where('merged_challan is null');
		$this->db->or_where(' student_id = "'.$id.'" and merged_challan IS not NULL and actual_amount = 0');
        $query2 = $this->db->get()->result_array();
		
		$arr=array_merge($query,$query2);
        
        function date_compare($a, $b)
        {
            $t1 = strtotime($a['dead_line']);
            $t2 = strtotime($b['dead_line']);
            return $t1 - $t2;
        }

		usort($arr, 'date_compare');

       	

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'FOUND',
            'fee'=>$arr,
            'response'=>"Found"
        );
        echo json_encode($result);


    }
	public function get_timetable()
	{

		$id = $this->input->post('user_id');

        $this->db->select('*');
		$this->db->from('students');
		$this->db->join('classes','students.class_id=classes.class_id','inner');
		$this->db->where('student_id', $id);
        $student = $this->db->get()->row();
		
		
		$monday = strtotime('next Monday -1 week');
		$monday = date('w', $monday)==date('w') ? strtotime(date("Y-m-d",$monday)." +7 days") : $monday;
		$sunday = strtotime(date("Y-m-d",$monday)." +6 days");
		
		$this_week_sd = date("Y-m-d",$monday);
   		$this_week_ed = date("Y-m-d",$sunday);
		
		
		
		
		$this->db->select('*,concat(users.first_name," ",users.last_name) as teacher');
		$this->db->from('session_syllabus');
		$this->db->join('course_subjects','course_subjects.course_subject_id=session_syllabus.subject_id','left');
		$this->db->join('lectures','lectures.id=session_syllabus.lecture_id','left');
		$this->db->join('campuses','campuses.campus_id=lectures.campus','left');
		$this->db->join('users','users.user_id=lectures.teacher','left');
		$this->db->where('session_syllabus.sessions like "%'.$student->session.'%" and session_syllabus.date  BETWEEN "'.$this_week_sd.'" AND "'.$this_week_ed.'"');
        $arr = $this->db->get()->result_array();
		
		
		foreach($arr as $key=>$lectures)
		{
			$subjects = $this->db->where_in('topic_id',explode(',',$lectures['topic_ids']))->get('topics')->result_array();
			$practicals = $this->db->where_in('practical_id',explode(',',$lectures['practical_ids']))->get('practicals')->result_array();
			
			$subjects_names = array();
			$practicals_names=array();

			foreach($subjects as $subject)
			{
				
				array_push($subjects_names,$subject['topic_name']);
				
				
			}							
			$arr[$key]['topic_names'] = $subjects_names;
			
			
			foreach($practicals as $subject)
			{
				
				array_push($practicals_names,$subject['practical_name']);
				
				
			}
			$arr[$key]['practical_names'] = $practicals_names;

		}
	
       	

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'FOUND',
            'fee'=>$arr,
            'response'=>"Found"
        );
        echo json_encode($result);


    }
	
}