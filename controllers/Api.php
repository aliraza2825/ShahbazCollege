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
	
	public function GetAdmissionCourses(){

		$qry = "SELECT * FROM courses";

		$data = $this->db->query($qry)->result_array();

		foreach ($data as $key=>$ab)
            {
				
				if($ab['course_type']=='Annual'){
					$qry1 = "SELECT course_id, subject_year, subject_semester FROM course_subjects WHERE course_id = '".$ab['course_id']."' GROUP BY subject_year";
				} else {
                	$qry1 = "SELECT course_id, subject_year, subject_semester FROM course_subjects WHERE course_id = '".$ab['course_id']."' GROUP BY subject_semester";
				}
				$duration = $this->db->query($qry1)->result_array();
				
				
				if(count($duration)>0){

					$data[$key]['duration_data'] = $duration;
				}else{
					$data[$key]['duration_data'] = $duration;				
				}
				
				
					
            }

		$result = array(
			'status'=>'SUCCESS',
			'response_code'=>'1',
			'message'=>'Data found!',
			'courses_data_response'=>$data
			);		   
		echo json_encode($result);

	}
	

	public function GetLearnCourses(){

		$user_id = $this->input->post('user_id');
		$session_no = $this->input->post('session_no');

		$qry = "SELECT * FROM (SELECT courses.course_id, courses.course_name, courses.course_type, courses.course_duration_year, COUNT(DISTINCT(questions.question_id)) as total_questions, (SELECT COUNT(*) FROM questions q WHERE FIND_IN_SET(q.question_id, GROUP_CONCAT(DISTINCT(ls.understand_questions)))) as completed_questions FROM courses
                    LEFT JOIN learn_result ls ON ls.course_id = courses.course_id AND ls.user_id = $user_id AND ls.session_id = $session_no
                    LEFT JOIN questions ON questions.course_id = courses.course_id
                    GROUP BY courses.course_id) tbl WHERE total_questions > 1";

		$data = $this->db->query($qry)->result_array();

		foreach ($data as $key=>$ab)
            {
				
				if($ab['course_type']=='Annual'){
					$qry1 = "SELECT course_id, subject_year, subject_semester FROM course_subjects WHERE course_id = '".$ab['course_id']."' GROUP BY subject_year";
				} else {
                	$qry1 = "SELECT course_id, subject_year, subject_semester FROM course_subjects WHERE course_id = '".$ab['course_id']."' GROUP BY subject_semester";
				}
				$duration = $this->db->query($qry1)->result_array();
				
				
				if(count($duration)>0){

					$data[$key]['duration_data'] = $duration;
				}else{
					$data[$key]['duration_data'] = $duration;				
				}
				
				
					
            }

		$result = array(
			'status'=>'SUCCESS',
			'response_code'=>'1',
			'message'=>'Data found!',
			'learn_courses_response'=>$data
			);		   
		echo json_encode($result);

	}

	public function GetCoursesProgressByID(){

		$user_id = $this->input->post('user_id');
		$course_id = $this->input->post('course_id');

		$qry = "SELECT course_id, course_name, course_type, total_subjects, SUM(NOT_READ) as not_readed_subjects, SUM(IN_PROGRESS) as inprogress_subjects, SUM(COMPLETED) as completed_subjects, SUM(remaining_questions) as remaining_questions, SUM(total_questions_num) as total_questions_num

		FROM (SELECT *, CASE WHEN remaining_questions = total_questions_num THEN 1 ELSE 0 END as NOT_READ, CASE WHEN remaining_questions != 0 AND remaining_questions != total_questions_num THEN 1 ELSE 0 END as IN_PROGRESS, CASE WHEN remaining_questions = 0 THEN 1 ELSE 0 END as COMPLETED FROM
		
		(SELECT subs.course_subject_id, subs.subject_name, courses.course_id, courses.course_name, courses.course_type, (SELECT COUNT(*) FROM course_subjects WHERE course_subjects.course_id = courses.course_id) as total_subjects,
		
		(SELECT COUNT(*) FROM det_questions
		 WHERE det_questions.course_subject_id = subs.course_subject_id AND type IN ('multiple', 'radio') AND NOT FIND_IN_SET(question_id, (CASE WHEN (SELECT GROUP_CONCAT(question_id) FROM det_online_result WHERE det_online_result.subject_id = subs.course_subject_id AND user_id = '".$user_id."' AND answer_status = 'Correct') IS NULL THEN 0 ELSE (SELECT GROUP_CONCAT(question_id) FROM det_online_result WHERE det_online_result.subject_id = subs.course_subject_id AND user_id = '".$user_id."' AND answer_status = 'Correct') END))) AS remaining_questions,
		
		(SELECT COUNT(*) FROM det_questions WHERE det_questions.course_subject_id = subs.course_subject_id AND type IN ('multiple', 'radio')) AS total_questions_num
		
		FROM courses 
		LEFT JOIN course_subjects as subs ON subs.course_id = courses.course_id 
		WHERE courses.course_id = '".$course_id."'
		GROUP BY subs.course_subject_id) as tbl WHERE total_questions_num != 0) as tbl1 GROUP BY course_id";

		$data = $this->db->query($qry)->result_array();

		// $data= $this->db->get()->result_array();

		$result = array(
			'status'=>'SUCCESS',
			'response_code'=>'1',
			'message'=>'Data found!',
			'courses_progress_data_response'=>$data
			);		   
		echo json_encode($result);

	}

	public function GetCourseSubjectsByID(){

		$user_id = $this->input->post('user_id');
		$subject_id = $this->input->post('subject_id');

		$qry = "SELECT *, SUM(NOT_READ) as not_readed_subjects, SUM(IN_PROGRESS) as inprogress_subjects, SUM(COMPLETED) as completed_subjects, SUM(remaining_questions) as remaining_questions, SUM(total_questions_num) as total_questions_num

		FROM (SELECT *, CASE WHEN remaining_questions = total_questions_num THEN 1 ELSE 0 END as NOT_READ, CASE WHEN remaining_questions != 0 AND remaining_questions != total_questions_num THEN 1 ELSE 0 END as IN_PROGRESS, CASE WHEN remaining_questions = 0 THEN 1 ELSE 0 END as COMPLETED FROM
		
		(SELECT chaps.course_subject_id, chaps.chapter_name, course_subjects.subject_name, (SELECT COUNT(*) FROM chapters WHERE chapters.course_subject_id = course_subjects.course_subject_id) as total_chapters,
		
		(SELECT COUNT(*) FROM det_questions
		 WHERE det_questions.chapter_id = chaps.chapter_id AND type IN ('multiple', 'radio') AND NOT FIND_IN_SET(question_id, (CASE WHEN (SELECT GROUP_CONCAT(question_id) FROM det_online_result WHERE det_online_result.chapter_id = chaps.chapter_id AND user_id = '".$user_id."' AND answer_status = 'Correct') IS NULL THEN 0 ELSE (SELECT GROUP_CONCAT(question_id) FROM det_online_result WHERE det_online_result.chapter_id = chaps.chapter_id AND user_id = '".$user_id."' AND answer_status = 'Correct') END))) AS remaining_questions,
		
		(SELECT COUNT(*) FROM det_questions WHERE det_questions.chapter_id = chaps.chapter_id AND type IN ('multiple', 'radio')) AS total_questions_num
		
		FROM course_subjects
		LEFT JOIN chapters as chaps ON chaps.course_subject_id = course_subjects.course_subject_id 
		 WHERE course_subjects.course_subject_id = '".$subject_id."'
		GROUP BY chaps.chapter_id) as tbl WHERE total_questions_num != 0 ) as tbl1";

		$data = $this->db->query($qry)->result_array();

		// $data= $this->db->get()->result_array();

		$result = array(
			'status'=>'SUCCESS',
			'response_code'=>'1',
			'message'=>'Data found!',
			'subjects_by_id_data_response'=>$data
			);		   
		echo json_encode($result);

	}

	public function GetCourseSubjectsProgress(){

		$user_id = $this->input->post('user_id');
		$course_id = $this->input->post('course_id');

		$qry = "SELECT *, SUM(NOT_READ) as not_readed_subjects, SUM(IN_PROGRESS) as inprogress_subjects, SUM(COMPLETED) as completed_subjects, SUM(remaining_questions) as remaining_questions, SUM(total_questions_num) as total_questions_num

		FROM (SELECT *, CASE WHEN remaining_questions = total_questions_num THEN 1 ELSE 0 END as NOT_READ, CASE WHEN remaining_questions != 0 AND remaining_questions != total_questions_num THEN 1 ELSE 0 END as IN_PROGRESS, CASE WHEN remaining_questions = 0 THEN 1 ELSE 0 END as COMPLETED FROM
		
		(SELECT chaps.course_subject_id, chaps.chapter_name, course_subjects.subject_name, (SELECT COUNT(*) FROM chapters WHERE chapters.course_subject_id = course_subjects.course_subject_id) as total_chapters,
		
		(SELECT COUNT(*) FROM det_questions
		 WHERE det_questions.chapter_id = chaps.chapter_id AND type IN ('multiple', 'radio') AND NOT FIND_IN_SET(question_id, (CASE WHEN (SELECT GROUP_CONCAT(question_id) FROM det_online_result WHERE det_online_result.chapter_id = chaps.chapter_id AND user_id = '".$user_id."' AND answer_status = 'Correct') IS NULL THEN 0 ELSE (SELECT GROUP_CONCAT(question_id) FROM det_online_result WHERE det_online_result.chapter_id = chaps.chapter_id AND user_id = '".$user_id."' AND answer_status = 'Correct') END))) AS remaining_questions,
		
		(SELECT COUNT(*) FROM det_questions WHERE det_questions.chapter_id = chaps.chapter_id AND type IN ('multiple', 'radio')) AS total_questions_num
		
		FROM course_subjects
		LEFT JOIN chapters as chaps ON chaps.course_subject_id = course_subjects.course_subject_id 
		 WHERE course_subjects.course_id = '".$course_id."'
		GROUP BY chaps.chapter_id) as tbl WHERE total_questions_num != 0 ) as tbl1 GROUP BY course_subject_id";

		$data = $this->db->query($qry)->result_array();

		// $data= $this->db->get()->result_array();

		$result = array(
			'status'=>'SUCCESS',
			'response_code'=>'1',
			'message'=>'Data found!',
			'subjects_data_response'=>$data
			);		   
		echo json_encode($result);

	}

	public function GetChapters(){

		$subject_id = $this->input->post('subject_id');

		$user_id = $this->input->post('user_id');

		$qry = "SELECT *, SUM(NOT_READ) as not_readed_subjects, SUM(IN_PROGRESS) as inprogress_subjects, SUM(COMPLETED) as completed_subjects, SUM(remaining_questions) as remaining_questions, SUM(total_questions_num) as total_questions_num

		FROM (SELECT *, CASE WHEN remaining_questions = total_questions_num THEN 1 ELSE 0 END as NOT_READ, CASE WHEN remaining_questions != 0 AND remaining_questions != total_questions_num THEN 1 ELSE 0 END as IN_PROGRESS, CASE WHEN remaining_questions = 0 THEN 1 ELSE 0 END as COMPLETED FROM
		
		(SELECT topics.topic_id, chapters.chapter_name, chapters.chapter_id, (SELECT COUNT(*) FROM topics WHERE topics.chapter_id = chapters.chapter_id) as total_topics,
		
		(SELECT COUNT(*) FROM det_questions
		 WHERE det_questions.topic_id = topics.topic_id AND type IN ('multiple', 'radio') AND NOT FIND_IN_SET(question_id, (CASE WHEN (SELECT GROUP_CONCAT(question_id) FROM det_online_result WHERE det_online_result.topic_id = topics.topic_id AND user_id = '".$user_id."' AND answer_status = 'Correct') IS NULL THEN 0 ELSE (SELECT GROUP_CONCAT(question_id) FROM det_online_result WHERE det_online_result.topic_id = topics.topic_id AND user_id = '".$user_id."' AND answer_status = 'Correct') END))) AS remaining_questions,
		
		(SELECT COUNT(*) FROM det_questions WHERE det_questions.topic_id = topics.topic_id AND type IN ('multiple', 'radio')) AS total_questions_num
		
		FROM chapters
		LEFT JOIN topics ON topics.chapter_id = chapters.chapter_id 
		WHERE chapters.course_subject_id = '".$subject_id."'
		GROUP BY topics.topic_id) as tbl WHERE total_questions_num != 0 ) as tbl1 GROUP BY chapter_id";

		$data = $this->db->query($qry)->result_array();

		$result = array(
			'status'=>'SUCCESS',
			'response_code'=>'1',
			'message'=>'Data found!',
			'chapters_data_response'=>$data
			);		   
		echo json_encode($result);
	}
	
	public function GetCourseSubjects(){

		$courseID = $this->input->post('course_id');
		$user_id = $this->input->post('user_id');
		$session_no = $this->input->post('session_no');
		$courseTYPE = $this->input->post('course_type');

		if ($courseTYPE=='Annual'){
			$qry = "SELECT * FROM (SELECT course_subjects.course_subject_id, course_subjects.subject_name, course_subjects.subject_semester, course_subjects.subject_year, COUNT(DISTINCT(questions.question_id)) as total_questions, (SELECT COUNT(*) FROM questions q WHERE FIND_IN_SET(q.question_id, GROUP_CONCAT(DISTINCT(ls.understand_questions)))) as completed_questions FROM course_subjects
                    LEFT JOIN learn_result ls ON ls.subject_id = course_subjects.course_subject_id AND ls.user_id = $user_id AND ls.session_id = $session_no
                    LEFT JOIN questions ON questions.subject_id = course_subjects.course_subject_id
                    WHERE course_subjects.course_id = $courseID
                    GROUP BY course_subjects.course_subject_id) tbl WHERE total_questions > 1";
		} else {
			$qry = "SELECT * FROM (SELECT course_subjects.course_subject_id, course_subjects.subject_name, course_subjects.subject_semester, course_subjects.subject_year, COUNT(DISTINCT(questions.question_id)) as total_questions, (SELECT COUNT(*) FROM questions q WHERE FIND_IN_SET(q.question_id, GROUP_CONCAT(DISTINCT(ls.understand_questions)))) as completed_questions FROM course_subjects
                    LEFT JOIN learn_result ls ON ls.subject_id = course_subjects.course_subject_id AND ls.user_id = $user_id AND ls.session_id = $session_no
                    LEFT JOIN questions ON questions.subject_id = course_subjects.course_subject_id
                    WHERE course_subjects.course_id = $courseID
                    GROUP BY course_subjects.course_subject_id) tbl WHERE total_questions > 1";
		}
		
		$data = $this->db->query($qry)->result_array();

		foreach ($data as $key=>$ab)
            {
				$qry1 = "SELECT chapters.chapter_id, chapters.chapter_name, chapters.course_id, chapters.course_subject_id, COUNT(*) as total_topics FROM chapters LEFT JOIN topics ON topics.chapter_id = chapters.chapter_id WHERE chapters.course_subject_id = '".$ab['course_subject_id']."' GROUP BY chapters.chapter_id";
                // $this->db->select('*');
                // $this->db->from('chapters');
                // $this->db->where('course_subject_id', $ab['course_subject_id']);
                $chapters= $this->db->query($qry1)->result_array();
				
				
				if(count($chapters)>0){

					// foreach ($chapters as $key1=>$xy)
					// {
						
					// 	$this->db->select('*');
					// 	$this->db->from('topics');
					// 	$this->db->where('topics.chapter_id', $xy['chapter_id']);
					// 	$topics= $this->db->get()->result_array();
						
						
					// 	if(count($topics)>0)
					// 		$chapters[$key1]['topics_data'] = $topics;
					// 	else
					// 		$chapters[$key1]['topics_data'] = $topics;				
		
						
					// }

					$data[$key]['chapters_data'] = $chapters;
				}else{
					$data[$key]['chapters_data'] = $chapters;				
				}
				
				
					
            }

		$result = array(
			'status'=>'SUCCESS',
			'response_code'=>'1',
			'message'=>'Data Found!',
			'course_subjects_response'=>$data
			);		   
		echo json_encode($result);
	}

	public function GetTopics(){

		$chapter_id = $this->input->post('chapter_id');
		$user_id = $this->input->post('user_id');
		$session_no = $this->input->post('session_no');

		$qry = "SELECT * FROM (SELECT topics.topic_id, topics.topic_name, topics.course_id, topics.chapter_id, topics.course_subject_id, topics.status, COUNT(DISTINCT(questions.question_id)) as total_questions, (SELECT COUNT(*) FROM questions q WHERE FIND_IN_SET(q.question_id, GROUP_CONCAT(DISTINCT(ls.understand_questions)))) as completed_questions FROM topics
                LEFT JOIN learn_result ls ON ls.topic_id = topics.topic_id AND ls.user_id = $user_id AND ls.session_id = $session_no
                LEFT JOIN questions ON questions.topic_id = topics.topic_id
                WHERE topics.chapter_id = $chapter_id
                GROUP BY topics.topic_id) tbl WHERE total_questions > 1";
		$data= $this->db->query($qry)->result_array();

		$result = array(
			'status'=>'SUCCESS',
			'response_code'=>'1',
			'message'=>'Data found!',
			'topics_data_response'=>$data
			);		   
		echo json_encode($result);
	}

	public function GetQNA(){

		$chapter_id = $this->input->post('chapter_id');

		$qry = "SELECT application_qna.*, topics.topic_name, chapters.chapter_id, chapters.chapter_name, CONCAT(students.first_name, ' ' ,students.last_name) as name, CONCAT(users.first_name, ' ' ,users.last_name) as employee FROM `application_qna` INNER JOIN topics ON topics.topic_id = application_qna.qna_topic_id INNER JOIN chapters ON chapters.chapter_id = topics.chapter_id LEFT JOIN students ON students.student_id = application_qna.qna_student_id LEFT JOIN users ON users.user_id = application_qna.qna_staff_id WHERE origin_status != 0 AND chapters.chapter_id = '".$chapter_id."'";

		$data = $this->db->query($qry)->result_array();

		// $data= $this->db->get()->result_array();

		foreach ($data as $key=>$ab)
            {
				
				$qry1 = "SELECT application_qna.*, topics.topic_name, chapters.chapter_id, chapters.chapter_name, CONCAT(students.first_name, ' ' ,students.last_name) as name, CONCAT(users.first_name, ' ' ,users.last_name) as employee FROM `application_qna` INNER JOIN topics ON topics.topic_id = application_qna.qna_topic_id INNER JOIN chapters ON chapters.chapter_id = topics.chapter_id LEFT JOIN students ON students.student_id = application_qna.qna_student_id LEFT JOIN users ON users.user_id = application_qna.qna_staff_id WHERE application_qna.quoted_qna_id = '".$ab['qna_id']."'";


				$comments= $this->db->query($qry1)->result_array();
				
				
				if(count($comments)>0){
					$data[$key]['comment_data'] = $comments;
				}else{
					$data[$key]['comment_data'] = $comments;				
				}
				
				
					
            }

		$result = array(
			'status'=>'SUCCESS',
			'response_code'=>'1',
			'message'=>'Data found!',
			'qna_data_response'=>$data
			);		   
		echo json_encode($result);
	}
	
	public function AskQNA(){

		$quoted_qna_id = $this->input->post('quoted_qna_id');
		$qna_student_id = $this->input->post('qna_student_id');
		$qna_staff_id = $this->input->post('qna_staff_id');
		$qna_topic_id = $this->input->post('qna_topic_id');
		$qna_question_id = $this->input->post('qna_question_id');
		$qna_text = $this->input->post('qna_text');
		$qna_file = $this->input->post('qna_file');
		$qna_mime = $this->input->post('qna_mime');
		$origin_status = $this->input->post('origin_status');


		$this->db->set(array(
			'quoted_qna_id'=>$quoted_qna_id,
			'qna_student_id'=>$qna_student_id,
			'qna_staff_id'=>$qna_staff_id,
			'qna_topic_id'=>$qna_topic_id,
			'qna_question_id'=>$qna_question_id,
			'qna_text'=>$qna_text,
			'qna_file'=>$qna_file,
			'qna_mime'=>$qna_mime,
			'origin_status'=>$origin_status
		));
	
	
			$results=$this->db->insert('application_qna');

			if($results){
				
				$result = array(
				'status'=>'SUCCESS',
				'response_code'=>'1',
				'message'=>'Sucessfully added',
				);		   
				echo json_encode($result);
				
			}else{
				
				
				$result = array(
				'status'=>'SUCCESS',
				'response_code'=>'0',
				'message'=>'Failed',
				);		   
				echo json_encode($result);
				
				
			}
	}

	 public function UploadFile()
	{
		$target_dir = "uploads/";  
		$target_file_name = $target_dir .basename($_FILES["file"]["name"]);  
		$response = array();  
		
		// Check if image file is an actual image or fake image  
		if (isset($_FILES["file"]))   
		{  
		if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file_name))   
		{  
			$success = 1;  
			$message = "Successfully Uploaded";  
		}  
		else   
		{  
			$success = 1;  
			$message = "Error while uploading";  
		}  
		}  
		else   
		{  
			$success = 0;  
			$message = "Required Field Missing";  
		}  
		
		$result = array(
			'status'=>'SUCCESS',
			'response_code'=>$success,
			'message'=>$message,
			);		   
			echo json_encode($result);
	}

	public function GetMcqsSummary(){

		$topic_id = $this->input->post('topic_id');
		$user_id = $this->input->post('user_id');

		$qry = "SELECT online_test_result_detail_id, online_test_result_id, SUM(total_timespent_millis) as total_timespent_millis, user_id, topic_id, SUM(total_questions) as total_questions_attended, SUM(correct_answers) as correct_answers_given, SUM(percentage) as total_percentage, (SELECT COUNT(*) FROM questions WHERE topic_id = '".$topic_id."' AND type IN ('multiple', 'radio') AND NOT FIND_IN_SET(question_id, (CASE WHEN (SELECT GROUP_CONCAT(question_id) FROM online_result_detail WHERE topic_id = '".$topic_id."' AND user_id = '".$user_id."' AND answer_status = 'Correct') IS NULL THEN 0 ELSE (SELECT GROUP_CONCAT(question_id) FROM online_result_detail WHERE topic_id = '".$topic_id."' AND user_id = '".$user_id."' AND answer_status = 'Correct') END))) AS remaining_questions, (SELECT COUNT(*) FROM questions WHERE topic_id = '".$topic_id."' AND type IN ('multiple', 'radio')) AS total_questions_num FROM online_result WHERE user_id = '".$user_id."' AND topic_id = '".$topic_id."'";
		$data = $this->db->query($qry)->result_array();

		$result = array(
			'status'=>'SUCCESS',
			'response_code'=>'1',
			'message'=>'Data found!',
			'mcqs_summary_response'=>$data
			);		   
		echo json_encode($result);
	}

	public function GetQuestions(){

		$topic_id = $this->input->post('topic_id');
		$user_id = $this->input->post('user_id');
		$user_type = $this->input->post('user_type');
		$exam_mode = $this->input->post('exam_mode');
		$questions_count = $this->input->post('count');

		$qry = "SELECT * FROM questions WHERE topic_id = '".$topic_id."' AND type IN ('multiple', 'radio') AND NOT FIND_IN_SET(question_id, (CASE WHEN (SELECT GROUP_CONCAT(question_id) FROM online_result_detail WHERE topic_id = '".$topic_id."' AND user_id = '".$user_id."' AND answer_status = 'Correct') IS NULL THEN 0 ELSE (SELECT GROUP_CONCAT(question_id) FROM online_result_detail WHERE topic_id = '".$topic_id."' AND user_id = '".$user_id."' AND answer_status = 'Correct') END)) ORDER BY RAND() LIMIT ".$questions_count."";
		$data = $this->db->query($qry)->result_array();

		$this->db->set('user_id', $user_id);
		$this->db->set('topic_id', $topic_id);
		$this->db->set('quiz_mode', $exam_mode);
		$this->db->set('user_type', $user_type);
		$this->db->set('total_questions', $questions_count);
		$inserted = $this->db->insert('online_test_results_test');
		$insert_id = $this->db->insert_id();

		$result = array(
			'status'=>'SUCCESS',
			'response_code'=>'1',
			'message'=>'Data found!',
			'ID'=>$insert_id,
			'questions_response'=>$data
			);		   
		echo json_encode($result);

		// $qry = "SELECT question_id, question, option_1 as A, option_2 as B, option_3 AS C, option_4 AS D, answer, right_answer FROM `questions` WHERE type = 'multiple'";
		// $data= $this->db->query($qry)->result_array();

		// foreach ($data as $key=>$ab)
        //     {
				
		// 		if($data[$key]['answer']=='A'){
		// 			$this->db->set('right_answer', $data[$key]['A']);
		// 			$this->db->where('question_id', $data[$key]['question_id']);
		// 			$updated=$this->db->update('questions');
		// 		} else if($data[$key]['answer']=='B'){
		// 			$this->db->set('right_answer', $data[$key]['B']);
		// 			$this->db->where('question_id', $data[$key]['question_id']);
		// 			$updated=$this->db->update('questions');
		// 		} else if($data[$key]['answer']=='C'){
		// 			$this->db->set('right_answer', $data[$key]['C']);
		// 			$this->db->where('question_id', $data[$key]['question_id']);
		// 			$updated=$this->db->update('questions');
		// 		} else if($data[$key]['answer']=='D'){
		// 			$this->db->set('right_answer', $data[$key]['D']);
		// 			$this->db->where('question_id', $data[$key]['question_id']);
		// 			$updated=$this->db->update('questions');
		// 		} 
					
        //     }

		

	}

	public function DetermineSession(){

		$topic_id = $this->input->post('topic_id');
		$user_id = $this->input->post('user_id');
		$quiz_type = $this->input->post('quiz_type');

		$session=1;

		if($quiz_type=='mcqs'){
			$qry = "SELECT COUNT(*) c FROM `questions` WHERE NOT FIND_IN_SET(question_id, IFNULL((SELECT understand_questions FROM learn_result WHERE learn_result.user_id = '".$user_id."' AND questions_type = 'multiple' AND learn_result.topic_id = '".$topic_id."' AND learn_result.session_id = 1),0)) AND topic_id = '".$topic_id."' AND type IN ('radio', 'multiple') ORDER BY RAND() LIMIT 5";

			$data = $this->db->query($qry)->result_array();

			if($data[0]['c']>0){
				$result = array(
					'status'=>'SUCCESS',
					'response_code'=>'1',
					'message'=>'Data found!',
					'session_no'=>'1',
					'session_status'=>'In_Progress'
					);		   
				echo json_encode($result);
			} else {
				$qry1 = "SELECT COUNT(*) c FROM learn_result WHERE learn_result.user_id = '".$user_id."' AND questions_type = 'multiple' AND learn_result.topic_id = '".$topic_id."' AND learn_result.session_id = 2";
				$data1 = $this->db->query($qry1)->result_array();

				if($data1[0]['c']==0){
					$result = array(
						'status'=>'SUCCESS',
						'response_code'=>'1',
						'message'=>'Data found!',
						'session_no'=>'2',
						'session_status'=>'Not_Started'
						);		   
					echo json_encode($result);
				} else {
					$result = array(
						'status'=>'SUCCESS',
						'response_code'=>'1',
						'message'=>'Data found!',
						'session_no'=>'2',
						'session_status'=>'In_Progress'
						);		   
					echo json_encode($result);
				}
			}
			
		} else if ($quiz_type=='short') {
			$qry = "SELECT * FROM `questions` WHERE NOT FIND_IN_SET(question_id, (SELECT understand_questions FROM learn_result WHERE learn_result.user_id = '".$user_id."' AND questions_type = 'short' AND learn_result.topic_id = '".$topic_id."')) AND topic_id = '".$topic_id."' AND type IN ('short-question') ORDER BY RAND() LIMIT 5";
		} else {
			$qry = "SELECT * FROM `questions` WHERE NOT FIND_IN_SET(question_id, (SELECT understand_questions FROM learn_result WHERE learn_result.user_id = '".$user_id."' AND questions_type = 'meaning' AND learn_result.topic_id = '".$topic_id."')) AND topic_id = '".$topic_id."' AND type IN ('word-meaning') ORDER BY RAND() LIMIT 5";
		}

	}

	public function GetQuestionsLearn(){

		$topic_id = $this->input->post('topic_id');
		$user_id = $this->input->post('user_id');
		$quiz_type = $this->input->post('quiz_type');
		$session_no = $this->input->post('session_no');

		if($quiz_type=='mcqs'){
			$qry = "SELECT * FROM `questions` WHERE NOT FIND_IN_SET(question_id, IFNULL((SELECT understand_questions FROM learn_result WHERE learn_result.user_id = '".$user_id."' AND questions_type = 'MCQs' AND learn_result.topic_id = '".$topic_id."' AND learn_result.session_id = '".$session_no."'),0)) AND topic_id = '".$topic_id."' AND type IN ('radio', 'multiple') ORDER BY RAND() LIMIT 5";
		} else if ($quiz_type=='short') {
			$qry = "SELECT * FROM `questions` WHERE NOT FIND_IN_SET(question_id, IFNULL((SELECT understand_questions FROM learn_result WHERE learn_result.user_id = '".$user_id."' AND questions_type = 'short' AND learn_result.topic_id = '".$topic_id."' AND learn_result.session_id = '".$session_no."'),0)) AND topic_id = '".$topic_id."' AND type IN ('short-question') ORDER BY RAND() LIMIT 5";
			//$qry = "SELECT * FROM `questions` WHERE NOT FIND_IN_SET(question_id, IFNULL((SELECT understand_questions FROM learn_result WHERE learn_result.user_id = '".$user_id."' AND questions_type = 'short' AND learn_result.topic_id = '".$topic_id."'),0)) AND topic_id = '".$topic_id."' AND type IN ('short-question') ORDER BY RAND() LIMIT 5";
		} else {
			$qry = "SELECT * FROM `questions` WHERE NOT FIND_IN_SET(question_id, IFNULL((SELECT understand_questions FROM learn_result WHERE learn_result.user_id = '".$user_id."' AND questions_type = 'meaning' AND learn_result.topic_id = '".$topic_id."'),0)) AND topic_id = '".$topic_id."' AND type IN ('word-meaning') ORDER BY RAND() LIMIT 5";
		}
		// $qry = "SELECT * FROM `questions` WHERE NOT FIND_IN_SET(question_id, (SELECT understand_questions FROM learn_result WHERE learn_result.user_id = '".$user_id."')) AND topic_id = '".$topic_id."' AND type IN ('radio', 'multiple') ORDER BY RAND() LIMIT 5";
		$data = $this->db->query($qry)->result_array();

		// $this->db->set('user_id', $user_id);
		// $this->db->set('topic_id', $topic_id);
		// $this->db->set('quiz_mode', $exam_mode);
		// $this->db->set('user_type', $user_type);
		// $this->db->set('total_questions', $questions_count);
		// $inserted = $this->db->insert('online_test_results_test');
		// $insert_id = $this->db->insert_id();

		$result = array(
			'status'=>'SUCCESS',
			'response_code'=>'1',
			'message'=>'Data found!',
			'learn_questions_response'=>$data
			);		   
		echo json_encode($result);

	}

	public function PostLearnQuestions(){

		$user_id = $this->input->post('user_id');
		$user_type = $this->input->post('user_type');
		$course_id = $this->input->post('course_id');
		$subject_id = $this->input->post('subject_id');
		$chapter_id = $this->input->post('chapter_id');
		$topic_id = $this->input->post('topic_id');
		$session_no = $this->input->post('session_no');
		$question_type = $this->input->post('question_type');
		$understand_questions = $this->input->post('understand_questions');

		$qry = "SELECT * FROM learn_result WHERE learn_result.user_id = '".$user_id."' AND learn_result.topic_id = '".$topic_id."' AND questions_type = '".$question_type."' AND session_id = '".$session_no."'";
		$data = $this->db->query($qry)->result_array();

		if (count($data)>0){
			$this->db->set('understand_questions', $data[0]['understand_questions'].','.$understand_questions);
			$this->db->where(array('id'=>$data[0]['id']));
			$updated=$this->db->update('learn_result');
		} else {
			$this->db->set('user_id', $user_id);
			$this->db->set('course_id', $course_id);
			$this->db->set('subject_id', $subject_id);
			$this->db->set('chapter_id', $chapter_id);
			$this->db->set('topic_id', $topic_id);
			$this->db->set('user_type', $user_type);
			$this->db->set('session_id', $session_no);
			$this->db->set('questions_type', $question_type);
			$this->db->set('understand_questions', $understand_questions);
			$inserted = $this->db->insert('learn_result');
		}

		$result = array(
			'status'=>'SUCCESS',
			'response_code'=>'1',
			'message'=>'Posted_successfully'
			);		   
		echo json_encode($result);

	}

	public function SubmitMCQs(){

		$online_test_result_id = $this->input->post('online_test_result_id');
		$question_id = $this->input->post('question_id');
		$answer_given = $this->input->post('answer_given');
		$timespent_in_millis = $this->input->post('timespent_in_millis');


		$this->db->set(array(
			'online_test_result_id'=>$online_test_result_id,
			'question_id'=>$question_id,
			'answer_given'=>$answer_given,
			'timespent_in_millis'=>$timespent_in_millis
		));
	
	
			$results=$this->db->insert('online_test_results_details');

			if($results){
				
				$result = array(
				'status'=>'SUCCESS',
				'response_code'=>'1',
				'message'=>'Sucessfully added'
				);		   
				echo json_encode($result);
				
			}else{
				
				
				$result = array(
					'status'=>'SUCCESS',
					'response_code'=>'0',
					'message'=>'Failed'
				);		   
				echo json_encode($result);
				
				
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
					'response'=>$updated
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

	public function validate_student_exist()
	{	
		//echo 'success';
		$cnic = $this->input->post('cnic');
		
		$this->db->select('first_name,last_name,status');
		$this->db->from('students');
		$this->db->where(array('students.cnic'=>$cnic));
		$active_student = $this->db->get()->result_array();
		
	
		
		if(count($active_student)>0)
		{
			
			
			$result = array(
				'status'=>'FOUND',
				'response_code'=>'1',
				'message'=>'Student already exist',
				'response'=>$active_student
			);		   
			echo json_encode($result);
		}
		
		else
		{
			$result=array(
				'status'=>'NOT FOUND',
				'response_code'=>'0',
				'message'=>'No User Found'
			);                        
			echo json_encode($result);
		}
	}
	
	public function StudentLogin(){
	    $roll_no = $this->input->post('roll_no');
		$password = md5($this->input->post('password'));

        $qry = "SELECT students.student_id, students.first_name, students.last_name, students.father_name, students.roll_no, students.gender, students.religion, students.cnic, students.blood_group, students.date_of_birth, students.mobile, students.emergency_no, courses.course_name, classes.name as class_name, campuses.campus_name FROM `students`  INNER JOIN courses ON students.course_id = courses.course_id  INNER JOIN classes ON classes.class_id = students.class_id INNER JOIN campuses ON campuses.campus_id = classes.campus_id WHERE roll_no = '".$roll_no."' AND password = '".$password."'";
		$query = $this->db->query($qry)->result_array();
		
		if(count($query)>0){
			//GET STUDENT DOCUMENTS
			$documents = $this->db->get_where('student_documents',array('student_id'=>$query[0]['student_id']))->result_array();
			$student_documents = array();
			foreach($documents as $document)
			{
				if($document['online_image']!='')
				{
					$bucket_address = 'https://shahbazcollegebucket.s3.ca-central-1.amazonaws.com';
					$cloudfront_address = 'https://d10iw6eujrfvyr.cloudfront.net';
					$link = str_replace($bucket_address,$cloudfront_address,$document['online_image']);
					$links = array($document['type'],$link);
					//GET STUDENT PHOTO
					if($document['type']=='Photo')
					{
						$query[0]['student_photo'] = $link;
					}
				}
				else
				{
					$link=base_url().'uploads/'.$document['image'];
					$links = array($document['type'],$link);
					//GET STUDENT PHOTO
					if($document['type']=='Photo')
					{
						$query[0]['student_photo'] = $link;
					}
				}
				array_push($student_documents,$links);
			}
			//GET COURSES
			$this->db->select('courses.course_id,courses.course_name,students.status');
			$this->db->from('students');
			$this->db->join('classes','classes.class_id=students.class_id','INNER');
			$this->db->join('courses','classes.course_id=courses.course_id','INNER');
			$this->db->where('students.cnic',$query[0]['cnic']);
			$courses = $this->db->get()->result_array();

		    $result = array(
				'status'=>'FOUND',
				'response_code'=>'1',
				'message'=>'Login Successful',
				'login_response'=>$query,
				'documents'=>$student_documents,
				'courses'=>$courses
				);		   
				echo json_encode($result);
		} else{
		    $result = array(
				'status'=>'NOT FOUND',
				'response_code'=>'0',
				'message'=>'Login Failed',
				'login_response'=>'null'
				);		   
				echo json_encode($result);
		}
	}
	
	public function GetPlans(){

        $course_id = $this->input->post('course_id');

		$this->db->select(' * ');
		$this->db->from('plans');
		$this->db->where(array('plan_course_id'=>$course_id));
		$plans = $this->db->get()->result_array();

		if(count($plans)>0){
			
			$result = array(
			'status'=>'SUCCESS',
			'response_code'=>'1',
			'message'=>'Found',
			'plans_response'=>$plans
			);		   
			echo json_encode($result);
			
		}else{
			
			
			$result = array(
			'status'=>'Failed',
			'response_code'=>'0',
			'message'=>'No Data Found',
			'plans_response'=>$plans
			);		   
			echo json_encode($result);
			
			
		}

	}
	
	public function GetRegularRules(){

        $course_id = $this->input->post('course_id');
        //$session = $this->input->post('session');

		$this->db->limit(1);
		$this->db->order_by('session','DESC');
		$sessionCheck  = $this->db->get_where('fee_rules',array('course_id'=>$course_id,'status'=>'active'))->result_array();
		$latestSession = @$sessionCheck[0]['session'];

		$this->db->select('*');
		$this->db->from('fee_rules');
		$this->db->where(array('session'=>$latestSession,'course_id'=>$course_id,'status'=>'active'));
		$plans = $this->db->get()->result_array();

		if(count($plans)>0){
			
			$result = array(
			'status'=>'SUCCESS',
			'response_code'=>'1',
			'message'=>'Found',
			'regular_rules_response'=>$plans
			);		   
			echo json_encode($result);
			
		}else{
			
			
			$result = array(
			'status'=>'Failed',
			'response_code'=>'0',
			'message'=>'No Data Found',
			'regular_rules_response'=>$plans
			);		   
			echo json_encode($result);
			
			
		}

	}
	
	public function GetIssueTypes(){

		$this->db->select(' * ');
		$this->db->from('issue_types');
		$issue_types = $this->db->get()->result_array();

		if(count($issue_types)>0){
			
			$result = array(
			'status'=>'SUCCESS',
			'response_code'=>'1',
			'message'=>'Found',
			'issue_types_response'=>$issue_types
			);		   
			echo json_encode($result);
			
		}else{
			
			
			$result = array(
			'status'=>'Failed',
			'response_code'=>'0',
			'message'=>'No Data Found',
			'issue_types_response'=>$issue_types
			);		   
			echo json_encode($result);
			
			
		}

	}
	
	/*
	public function GetChats(){

        $student_id = $this->input->post('student_id');

		$this->db->select(' * ');
		$this->db->from('chats');
		$this->db->where(array('student_id'=>$student_id));
		$chats = $this->db->get()->result_array();

		if(count($chats)>0){
			
			$result = array(
			'status'=>'SUCCESS',
			'response_code'=>'1',
			'message'=>'Found',
			'chats_response'=>$chats
			);		   
			echo json_encode($result);
			
		}else{
			
			
			$result = array(
			'status'=>'Failed',
			'response_code'=>'0',
			'message'=>'No Data Found',
			'chats_response'=>$chats
			);		   
			echo json_encode($result);
			
			
		}

	}
	*/
	
	public function GetComplaints(){

        $student_id = $this->input->post('student_id');

		$this->db->select(' * ');
		$this->db->from('complaints');
		$this->db->where(array('student_id'=>$student_id));
		$complaints = $this->db->get()->result_array();

		if(count($complaints)>0){
			
			$result = array(
			'status'=>'SUCCESS',
			'response_code'=>'1',
			'message'=>'Found',
			'complaints_response'=>$complaints
			);		   
			echo json_encode($result);
			
		}else{
			
			
			$result = array(
			'status'=>'Failed',
			'response_code'=>'0',
			'message'=>'No Data Found',
			'complaints_response'=>$complaints
			);		   
			echo json_encode($result);
			
			
		}

	}
	
	public function GetOnlineFeeRule(){

        $fee_rule_course_id = $this->input->post('fee_rule_course_id');

		$this->db->select(' * ');
		$this->db->from('online_fee_rules');
		$this->db->where(array('fee_rule_course_id'=>$fee_rule_course_id));
		$fee_rules = $this->db->get()->result_array();

		if(count($fee_rules)>0){
			
			$result = array(
			'status'=>'SUCCESS',
			'response_code'=>'1',
			'message'=>'Found',
			'fee_rule_response'=>$fee_rules
			);		   
			echo json_encode($result);
			
		}else{
			
			
			$result = array(
			'status'=>'Failed',
			'response_code'=>'0',
			'message'=>'No Data Found',
			'fee_rule_response'=>$fee_rules
			);		   
			echo json_encode($result);
			
			
		}

	}

	public function GetCampuses()
	{


		$this->db->select(' * ');
		$this->db->from('campuses');
 		//$this->db->where(array('mobile_availibility'=>1));
		$campuses = $this->db->get()->result_array();

		if(count($campuses)>0){
			
			$result = array(
			'status'=>'SUCCESS',
			'response_code'=>'1',
			'message'=>'Found',
			'campuses_response'=>$campuses
			);		   
			echo json_encode($result);
			
		}else{
			
			
			$result = array(
			'status'=>'Failed',
			'response_code'=>'0',
			'message'=>'No Data Found',
			'campuses_response'=>$campuses
			);		   
			echo json_encode($result);
			
			
		}

	}
	
	public function GetShifts(){

        $campus_id = $this->input->post('campus_id');

		$this->db->select(' * ');
		$this->db->from('shifts');
		$this->db->where(array('campus_id'=>$campus_id));
		$shifts = $this->db->get()->result_array();

		if(count($shifts)>0){
			
			$result = array(
			'status'=>'SUCCESS',
			'response_code'=>'1',
			'message'=>'Found',
			'shifts_response'=>$shifts
			);		   
			echo json_encode($result);
			
		}else{
			
			
			$result = array(
			'status'=>'Failed',
			'response_code'=>'0',
			'message'=>'No Data Found',
			'shits_response'=>$shifts
			);		   
			echo json_encode($result);
			
			
		}
	}
	
	public function GetStudyTypes(){


		$this->db->select(' * ');
		$this->db->from('study_type');
 		//$this->db->where(array('campus_id'=>$campus_id));
		$study_types = $this->db->get()->result_array();

		if(count($study_types)>0){
			
			$result = array(
			'status'=>'SUCCESS',
			'response_code'=>'1',
			'message'=>'Found',
			'study_types_response'=>$study_types
			);		   
			echo json_encode($result);
			
		}else{
			
			
			$result = array(
			'status'=>'Failed',
			'response_code'=>'0',
			'message'=>'No Data Found',
			'study_types_response'=>$study_types
			);		   
			echo json_encode($result);
			
			
		}
	}

    public function CheckAdmissionApplication(){
        
        $cnic = $this->input->post('cnic');

// 		$this->db->select(' * ');
// 		$this->db->from('admission_applications');
// 		$this->db->where(array('cnic'=>$cnic));
// 		$results = $this->db->get()->result_array();

        $qry = "SELECT admission_applications.*, courses.course_name, campuses.campus_name FROM `admission_applications` INNER JOIN courses ON courses.course_id = admission_applications.course_id INNER JOIN campuses ON campuses.campus_id = admission_applications.campus_id WHERE cnic = '".$cnic."' AND admission_applications.status != 4";
		$results = $this->db->query($qry)->result_array();

		if(count($results)>0){
			
			$result = array(
			'status'=>'IN_PROGRESS',
			'response_code'=>'1',
			'message'=>'Application already in progress',
			'continue_application_response'=>$results
			);		   
			echo json_encode($result);
			
		}else{
		    
		    $this->db->select(' * ');
		    $this->db->from('students');
		    $this->db->where(array('cnic'=>$cnic));
		    $results_student = $this->db->get()->result_array();
			
			
			if(count($results_student)>0){
			
    			$result = array(
    			'status'=>'REGISTERED',
    			'response_code'=>'2',
    			'message'=>'Student is already registered with institute',
    			'continue_application_response'=>$results
    			);		   
    			echo json_encode($result);
			
		    }else{
    			
    			$result = array(
    			'status'=>'NOT REGISTERED',
    			'response_code'=>'0',
    			'message'=>'No Data Found',
    			'continue_application_response'=>$results
    			);		   
			echo json_encode($result);
			
			
		}
			
			
		}
	}
	
	public function DeleteAdmissionApplication(){
        
        $application_id = $this->input->post('application_id');

		$results=$this->db->delete('admission_applications', array('application_id' => $application_id));

		if($results){
			
			$result = array(
			'status'=>'DELETED',
			'response_code'=>'1',
			'message'=>'Deleted Successfully',
			);		   
			echo json_encode($result);
			
		}else{
			
			
			$result = array(
			'status'=>'FAILED',
			'response_code'=>'0',
			'message'=>'User not exist/Failed',
			);		   
			echo json_encode($result);
			
			
		}
	}
	
	public function InitiateTransaction(){
	    
	    $application_id = $this->input->post('application_id');
	    $student_id = $this->input->post('student_id');
	    $ipg_list = $this->input->post('ipg_list');
	    $transaction_status = $this->input->post('transaction_status');
	    $order_amount = $this->input->post('order_amount');
	    $description = $this->input->post('description');
	    $BAF_charge = $this->input->post('BAF_charge');
	    $one_link_charge = $this->input->post('one_link_charge');
	    $created_on = $this->input->post('created_on');
	    $consumer_code = $this->input->post('consumer_code');
	    $click2pay = $this->input->post('click2pay');
	    $connect_pay_id = $this->input->post('connect_pay_id');
	    $order_type = $this->input->post('order_type');
	    $connect_pay_fee = $this->input->post('connect_pay_fee');
	    $bill_url = $this->input->post('bill_url');
	    $order_number = $this->input->post('order_number');
	    $is_fee_applied = $this->input->post('is_fee_applied');
	    
	    $this->db->set(array(
				'application_id'=>$application_id,
				'student_id'=>$student_id,
				'ipg_list'=>$ipg_list,
				'transaction_status'=>$transaction_status,
				'order_amount'=>$order_amount,
				'description'=>$description,
				'BAF_charge'=>$BAF_charge,
				'one_link_charge'=>$one_link_charge,
				'created_on'=>$created_on,
				'consumer_code'=>$consumer_code,
				'click2pay'=>$click2pay,
				'connect_pay_id'=>$connect_pay_id,
				'order_type'=>$order_type,
				'connect_pay_fee'=>$connect_pay_fee,
				'bill_url'=>$bill_url,
				'order_number'=>$order_number,
				'is_fee_applied'=>$is_fee_applied
			));
			
			$results=$this->db->insert('students_payments');
			$insert_id = $this->db->insert_id();
			
	    if (@$results){
			$result = array(
			'status'=>'SUCCESS',
			'response_code'=>'1',
			'message'=>'Submitted Successfully',
			'initializalition_response'=>$results
			);		   
			echo json_encode($result);
			
		}else{
			
			
			$result = array(
			'status'=>'FAILED',
			'response_code'=>'0',
			'message'=>'Not submitted',
			'initializalition_response'=>$results
			);		   
			echo json_encode($result);
			
			
		}
	    
	}
	
	public function RegistrationTransaction(){
        
        $application_id = $this->input->post('application_id');
        $password = md5($this->input->post('password'));

		$qry = "SELECT * FROM `admission_applications` WHERE application_id = '".$application_id."'";
		$results = $this->db->query($qry)->result_array();

        $course_id = $results[0]['course_id'];
        $device_id = $results[0]['device_id'];
        $campus_id = $results[0]['campus_id'];
        $first_name = $results[0]['first_name'];
        $last_name = $results[0]['last_name'];
        $father_name = $results[0]['father_name'];
        $plan_id = $results[0]['plan_id'];
        $gender = $results[0]['gender'];
        $qualification = $results[0]['qualification'];
        $caste = $results[0]['caste'];
        $religion = $results[0]['religion'];
        $email = $results[0]['email'];
        $cnic = $results[0]['cnic'];
        $blood_group = $results[0]['blood_group'];
        $date_of_birth = $results[0]['date_of_birth'];
        $city = $results[0]['city'];
        $address = $results[0]['address'];
        $mobile = $results[0]['mobile'];
        $emergency_no = $results[0]['emergency_no'];
        $class_id = $results[0]['class_id'];
        $district = $results[0]['district'];
        $tehsil = $results[0]['tehsil'];
        $mark_of_identification = $results[0]['mark_of_identification'];
        $place_of_birth = $results[0]['place_of_birth'];
        $board = $results[0]['board'];
        $shift = $results[0]['shift'];
        $study_type = $results[0]['study_type'];

		if(count($results)>0){
		    
		    $this->db->set(array(
				'course_id'=>$course_id,
				'device_id'=>$device_id,
				'study_campus'=>$campus_id,
				'first_name'=>$first_name,
				'last_name'=>$last_name,
				'father_name'=>$father_name,
				'plan_id'=>$plan_id,
				'gender'=>$gender,
				'password'=>$password,
				'qualification'=>$qualification,
				'caste'=>$caste,
				'religion'=>$religion,
				'email'=>$email,
				'cnic'=>$cnic,
				'blood_group'=>$blood_group,
				'date_of_birth'=>$date_of_birth,
				'city'=>$city,
				'address'=>$address,
				'mobile'=>$mobile,
				'emergency_no'=>$emergency_no,
				'class_id'=>$class_id,
				'district'=>$district,
				'tehsil'=>$tehsil,
				'mark_of_identification'=>$mark_of_identification,
				'place_of_birth'=>$place_of_birth,
				'board'=>$board,
				'shift'=>$shift,
				'study_type'=>$study_type,
			));
			
			
			$admission_result=$this->db->insert('students');
			$insert_id = $this->db->insert_id();
		    
		    $this->db->set(array(
    				'status'=>'4',
    			));
		    
		    $this->db->where('application_id',$application_id);
		    $status_results=$this->db->update('admission_applications');
		    
		    
		    
		    
		    
		    
		    
		    
		    
		    
			
			$result = array(
			'status'=>'SUCCESS',
			'response_code'=>'1',
			'message'=>'Submitted Successfully',
			);		   
			echo json_encode($result);
			
		}else{
			
			
			$result = array(
			'status'=>'FAILED',
			'response_code'=>'0',
			'message'=>'Not submitted',
			);		   
			echo json_encode($result);
			
			
		}
	}

    public function SubmitAdmissionApplication(){

		$application_id = $this->input->post('application_id');
		$course_id = $this->input->post('course_id');
		$campus_id = $this->input->post('campus_id');
		$first_name = $this->input->post('first_name');
		$last_name = $this->input->post('last_name');
		$father_name = $this->input->post('father_name');
		$plan_id = $this->input->post('plan_id');
		$gender = $this->input->post('gender');
		$qualification = $this->input->post('qualification');
		$caste = $this->input->post('caste');
		$religion = $this->input->post('religion');
		$email = $this->input->post('email');
		$cnic = $this->input->post('cnic');
		$blood_group = $this->input->post('blood_group');
		$date_of_birth = $this->input->post('date_of_birth');
		$city = $this->input->post('city');
		$address = $this->input->post('address');
		$mobile = $this->input->post('mobile');
		$emergency_no = $this->input->post('emergency_no');
		$district = $this->input->post('district');
		$tehsil = $this->input->post('tehsil');
		$mark_of_identification = $this->input->post('mark_of_identification');
		$place_of_birth = $this->input->post('place_of_birth');
		$board = $this->input->post('board');
		$shift = $this->input->post('shift');
		$study_type = $this->input->post('study_type');
		$fill_status = $this->input->post('fill_status');
		$status = $this->input->post('status');
		$reference_no = $this->input->post('reference_no');
		$device_id = $this->input->post('device_id');
		$class_id = $this->input->post('class_id');
		
		if($status==1){
            		    $qry = "SELECT * FROM `users` WHERE designation_id IN (7,4)";
            		$device_array = $this->db->query($qry)->result_array();
            		
            		$ids = array();
            		
            		
            		foreach($device_array as $device)
		            {
		                array_push($ids, $device['device_id']);
            			
		            }
            		    
                $url = 'https://fcm.googleapis.com/fcm/send';
            
                 $api_key = 'AAAAiFb3m_A:APA91bGUYX7ggNRcv9tboFgdbwbBNhtYglWmXpMDESLE1QXheIn5h_3BsOiWnh6iX83b-y2yhk88h7SFnUIeuQvZ5GYShuwER6UPfsC3YxDF9Ri7e7ND0R2yAYe07NsfQiE1hd87-t88';
                  
                         
                $fields = array (
                    // 'to'        => $device_array[0]["device_id"],
                    'registration_ids' => $ids,
                    'data' => array (
                            "title" => "$first_name $last_name Submitted Application",
                            "message" => "An admission application from $first_name $last_name. Please Review it."
                    )
                );
                  
            
                //header includes Content type and api key
                $headers = array(
                    'Content-Type:application/json',
                    'Authorization:key='.$api_key
                );
                            
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                $result = curl_exec($ch);
                if ($result === FALSE) {
                    die('FCM Send Error: ' . curl_error($ch));
                }
                curl_close($ch);
		}
		
		if($application_id==0){
			$this->db->set(array(
				'course_id'=>$course_id,
				'campus_id'=>$campus_id,
				'first_name'=>$first_name,
				'last_name'=>$last_name,
				'father_name'=>$father_name,
				'plan_id'=>$plan_id,
				'gender'=>$gender,
				'qualification'=>$qualification,
				'caste'=>$caste,
				'religion'=>$religion,
				'email'=>$email,
				'cnic'=>$cnic,
				'blood_group'=>$blood_group,
				'date_of_birth'=>$date_of_birth,
				'city'=>$city,
				'address'=>$address,
				'mobile'=>$mobile,
				'emergency_no'=>$emergency_no,
				'district'=>$district,
				'tehsil'=>$tehsil,
				'mark_of_identification'=>$mark_of_identification,
				'place_of_birth'=>$place_of_birth,
				'board'=>$board,
				'shift'=>$shift,
				'study_type'=>$study_type,
				'fill_status'=>$fill_status,
				'status'=>$status,
				'reference_no'=>$reference_no,
				'device_id'=>$device_id,
				'class_id'=>$class_id
			));
			
			
			$results=$this->db->insert('admission_applications');
			$insert_id = $this->db->insert_id();
		} else {
			$this->db->set(array(
				'course_id'=>$course_id,
				'campus_id'=>$campus_id,
				'first_name'=>$first_name,
				'last_name'=>$last_name,
				'father_name'=>$father_name,
				'plan_id'=>$plan_id,
				'gender'=>$gender,
				'qualification'=>$qualification,
				'caste'=>$caste,
				'religion'=>$religion,
				'email'=>$email,
				'cnic'=>$cnic,
				'blood_group'=>$blood_group,
				'date_of_birth'=>$date_of_birth,
				'city'=>$city,
				'address'=>$address,
				'mobile'=>$mobile,
				'emergency_no'=>$emergency_no,
				'district'=>$district,
				'tehsil'=>$tehsil,
				'mark_of_identification'=>$mark_of_identification,
				'place_of_birth'=>$place_of_birth,
				'board'=>$board,
				'shift'=>$shift,
				'study_type'=>$study_type,
				'fill_status'=>$fill_status,
				'status'=>$status,
				'reference_no'=>$reference_no,
				'device_id'=>$device_id,
				'class_id'=>$class_id
			));
	
				$this->db->where('application_id',$application_id);
				$results=$this->db->update('admission_applications');
				$insert_id = $application_id;
		}
		
			

			$this->db->select('*');
			$this->db->from('admission_applications');
			$this->db->where(array('application_id'=>$insert_id));
			$application = $this->db->get()->result_array();
			

			if($results){
				
				$result = array(
				'status'=>'SUCCESS',
				'response_code'=>'1',
				'message'=>'Admission Fetched Successfully',
				'response'=>$insert_id,
				'application_response'=>$application
				);		   
				echo json_encode($result);
				
			}else{
				
				
				$result = array(
				'status'=>'FAILED',
				'response_code'=>'0',
				'message'=>'Not submitted',
				'response'=>$insert_id,
				'application_response'=>$application
				);		   
				echo json_encode($result);
				
				
			}
	}
	
	public function SubmitDocuments()
	{
		$target_dir = "uploads/";  
		
		$application_id = $this->input->post('application_id');
		$type = $this->input->post('type');
		$student_image_file_name = $target_dir .basename($_FILES["student_image"]["name"]);
		$matriculation_result_image_file_name = $target_dir .basename($_FILES["matriculation_result_image"]["name"]);
		
		$this->db->select('*');
	    $this->db->from('student_docs');
		$this->db->where(array('student_docs.application_id'=>$application_id));
		$applicationDocuments = $this->db->get()->result_array();
		
// 		$result = array(
// 			'status'=>'SUCCESS',
// 			'response_code'=>1,
// 			'message'=>count($applicationDocuments),
// 			);		   
// 		echo json_encode($result);
// 		die;
// 		return;
		
		if(count($applicationDocuments)>0) {
		    
		    if (isset($_FILES["student_image"])){  
		    
		    if ($type=='cnic'){
		        
		        $cnic_front_image_file_name = $target_dir .basename($_FILES["cnic_front_image"]["name"]);
		$cnic_back_image_file_name = $target_dir .basename($_FILES["cnic_back_image"]["name"]);
		        
        		if (move_uploaded_file($_FILES["student_image"]["tmp_name"], $student_image_file_name) && move_uploaded_file($_FILES["matriculation_result_image"]["tmp_name"], $matriculation_result_image_file_name) && move_uploaded_file($_FILES["cnic_front_image"]["tmp_name"], $cnic_front_image_file_name) && move_uploaded_file($_FILES["cnic_back_image"]["tmp_name"], $cnic_back_image_file_name)){  
        		    
        		    
        		    
        			$success = 1;  
        			$message = "Successfully Uploaded";  
        			
        			$this->db->set(array(
        				'application_id'=>$application_id,
        				'student_image'=>$student_image_file_name,
        				'matriculation_result'=>$matriculation_result_image_file_name,
        				'cnic_front'=>$cnic_front_image_file_name,
        				'cnic_back'=>$cnic_back_image_file_name
        			));
        			$this->db->where('application_id',$application_id);
				    $results=$this->db->update('student_docs');
        			
        		}else{  
        		    
        			$success = 1;  
        			$message = "Error while uploading"; 
        			
        		}  
		    } else {
		        
		        $b_form_image_file_name = $target_dir .basename($_FILES["b_form_image"]["name"]);
		        
		        if (move_uploaded_file($_FILES["student_image"]["tmp_name"], $student_image_file_name) && move_uploaded_file($_FILES["matriculation_result_image"]["tmp_name"], $matriculation_result_image_file_name) && move_uploaded_file($_FILES["b_form_image"]["tmp_name"], $b_form_image_file_name)){  
        		    
        			$success = 1;  
        			$message = "Successfully Uploaded"; 
        			
        			$this->db->set(array(
        				'application_id'=>$application_id,
        				'student_image'=>$student_image_file_name,
        				'matriculation_result'=>$matriculation_result_image_file_name,
        				'b_form'=>$b_form_image_file_name
        			));
        			$this->db->where('application_id',$application_id);
				    $results=$this->db->update('student_docs');
        			
        		}else{  
        		    
        			$success = 1;  
        			$message = "Error while uploading"; 
        			
        		}
		    }
		}else{ 
		    
			$success = 0;  
			$message = "Required Field Missing";  
			
		}  
		
		$result = array(
			'status'=>'SUCCESS',
			'response_code'=>$success,
			'message'=>$message,
			);		   
			echo json_encode($result);
		    
		} else {
		    
		    if (isset($_FILES["student_image"])){  
		    
		    if ($type=='cnic'){
		        
		        $cnic_front_image_file_name = $target_dir .basename($_FILES["cnic_front_image"]["name"]);
		$cnic_back_image_file_name = $target_dir .basename($_FILES["cnic_back_image"]["name"]);
		        
        		if (move_uploaded_file($_FILES["student_image"]["tmp_name"], $student_image_file_name) && move_uploaded_file($_FILES["matriculation_result_image"]["tmp_name"], $matriculation_result_image_file_name) && move_uploaded_file($_FILES["cnic_front_image"]["tmp_name"], $cnic_front_image_file_name) && move_uploaded_file($_FILES["cnic_back_image"]["tmp_name"], $cnic_back_image_file_name)){  
        		    
        			$success = 1;  
        			$message = "Successfully Uploaded";  
        			
        			$this->db->set(array(
        				'application_id'=>$application_id,
        				'student_image'=>$student_image_file_name,
        				'matriculation_result'=>$matriculation_result_image_file_name,
        				'cnic_front'=>$cnic_front_image_file_name,
        				'cnic_back'=>$cnic_back_image_file_name
        			));
        			$results=$this->db->insert('student_docs');
        			
        		}else{  
        		    
        			$success = 1;  
        			$message = "Error while uploading"; 
        			
        		}  
		    } else {
		        
		        		$b_form_image_file_name = $target_dir .basename($_FILES["b_form_image"]["name"]);

		        
		        if (move_uploaded_file($_FILES["student_image"]["tmp_name"], $student_image_file_name) && move_uploaded_file($_FILES["matriculation_result_image"]["tmp_name"], $matriculation_result_image_file_name) && move_uploaded_file($_FILES["b_form_image"]["tmp_name"], $b_form_image_file_name)){  
        		    
        			$success = 1;  
        			$message = "Successfully Uploaded"; 
        			
        			$this->db->set(array(
        				'application_id'=>$application_id,
        				'student_image'=>$student_image_file_name,
        				'matriculation_result'=>$matriculation_result_image_file_name,
        				'b_form'=>$b_form_image_file_name
        			));
        			$results=$this->db->insert('student_docs');
        			
        		}else{  
        		    
        			$success = 1;  
        			$message = "Error while uploading"; 
        			
        		}
		    }
		}else{ 
		    
			$success = 0;  
			$message = "Required Field Missing";  
			
		}  
		
		    $result = array(
			'status'=>'SUCCESS',
			'response_code'=>$success,
			'message'=>$message,
			);		   
			echo json_encode($result);
		    
		}
		
		
	}
	
	public function UpdateDocuments()
	{
		$target_dir = "uploads/";  
		
		$application_id = $this->input->post('application_id');
		$type = $this->input->post('type');
		$student_image_file_name = $target_dir .basename($_FILES["student_image"]["name"]);
		$matriculation_result_image_file_name = $target_dir .basename($_FILES["matriculation_result_image"]["name"]);
		
	
		
		if (isset($_FILES["student_image"])){  
		    
		    if ($type=='cnic'){
		        
		        $cnic_front_image_file_name = $target_dir .basename($_FILES["cnic_front_image"]["name"]);
		$cnic_back_image_file_name = $target_dir .basename($_FILES["cnic_back_image"]["name"]);
		        
        		if (move_uploaded_file($_FILES["student_image"]["tmp_name"], $student_image_file_name) && move_uploaded_file($_FILES["matriculation_result_image"]["tmp_name"], $matriculation_result_image_file_name) && move_uploaded_file($_FILES["cnic_front_image"]["tmp_name"], $cnic_front_image_file_name) && move_uploaded_file($_FILES["cnic_back_image"]["tmp_name"], $cnic_back_image_file_name)){  
        		    
        		    
        		    
        			$success = 1;  
        			$message = "Successfully Uploaded";  
        			
        			$this->db->set(array(
        				'application_id'=>$application_id,
        				'student_image'=>$student_image_file_name,
        				'matriculation_result'=>$matriculation_result_image_file_name,
        				'cnic_front'=>$cnic_front_image_file_name,
        				'cnic_back'=>$cnic_back_image_file_name
        			));
        			$this->db->where('application_id',$application_id);
				    $results=$this->db->update('student_docs');
        			
        		}else{  
        		    
        			$success = 1;  
        			$message = "Error while uploading"; 
        			
        		}  
		    } else {
		        
		        $b_form_image_file_name = $target_dir .basename($_FILES["b_form_image"]["name"]);
		        
		        if (move_uploaded_file($_FILES["student_image"]["tmp_name"], $student_image_file_name) && move_uploaded_file($_FILES["matriculation_result_image"]["tmp_name"], $matriculation_result_image_file_name) && move_uploaded_file($_FILES["b_form_image"]["tmp_name"], $b_form_image_file_name)){  
        		    
        			$success = 1;  
        			$message = "Successfully Uploaded"; 
        			
        			$this->db->set(array(
        				'application_id'=>$application_id,
        				'student_image'=>$student_image_file_name,
        				'matriculation_result'=>$matriculation_result_image_file_name,
        				'b_form'=>$b_form_image_file_name
        			));
        			$this->db->where('application_id',$application_id);
				    $results=$this->db->update('student_docs');
        			
        		}else{  
        		    
        			$success = 1;  
        			$message = "Error while uploading"; 
        			
        		}
		    }
		}else{ 
		    
			$success = 0;  
			$message = "Required Field Missing";  
			
		}  
		
		$result = array(
			'status'=>'SUCCESS',
			'response_code'=>$success,
			'message'=>$message,
			);		   
			echo json_encode($result);
	}
	
	public function UpdateSignature()
	{
		$target_dir = "uploads/";  
		
		$application_id = $this->input->post('application_id');
		$signatures = $target_dir .basename($_FILES["signatures"]["name"]);
		
	
		
		if (isset($_FILES["signatures"])){  
		    
		    
		        if (move_uploaded_file($_FILES["signatures"]["tmp_name"], $signatures)){  
        		    
        			$success = 1;  
        			$message = "Successfully Uploaded"; 
        			
        			$this->db->set(array(
        				'signature'=>$signatures
        			));
        			$this->db->where('application_id',$application_id);
				    $results=$this->db->update('student_docs');
        			
        		}else{  
        		    
        			$success = 1;  
        			$message = "Error while uploading"; 
        			
        		}
        		
		}else{ 
		    
			$success = 0;  
			$message = "Required Field Missing";  
			
		}  
		
		$result = array(
			'status'=>'SUCCESS',
			'response_code'=>$success,
			'message'=>$message,
			);		   
			echo json_encode($result);
	}
	
	public function StartChat(){
		$category = $this->input->post('category');
		$message = $this->input->post('message');
		$student_id = $this->input->post('student_id');
	    
	    $this->db->set(array(
				'category'=>$category,
				'message'=>$message,
				'student_id'=>$student_id,
			));
	
	
			$results=$this->db->insert('chats');
			$insert_id = $this->db->insert_id();
			
			if($results){
				
				$result = array(
				'status'=>'SUCCESS',
				'response_code'=>'1',
				'message'=>'Chat Started',
				'chat_id'=>$insert_id,
				);		   
				echo json_encode($result);
				
			}else{
				
				
				$result = array(
				'status'=>'FAILED',
				'response_code'=>'0',
				'message'=>'Unable to start chat',
				'chat_id'=>$insert_id,
				);		   
				echo json_encode($result);
				
				
			}
	}
	
// 	public function StartChat(){
// 		$category = $this->input->post('category');
// 		$message = $this->input->post('message');
// 		$student_id = $this->input->post('student_id');
	    
// 	    $this->db->set(array(
// 				'category'=>$category,
// 				'message'=>$message,
// 				'student_id'=>$student_id,
// 			));
	
	
// 			$results=$this->db->insert('chats');
// 			$insert_id = $this->db->insert_id();
			
// 			if($results){
				
// 				$result = array(
// 				'status'=>'SUCCESS',
// 				'response_code'=>'1',
// 				'message'=>'Chat Started',
// 				'chat_id'=>$insert_id,
// 				);		   
// 				echo json_encode($result);
				
// 			}else{
				
				
// 				$result = array(
// 				'status'=>'FAILED',
// 				'response_code'=>'0',
// 				'message'=>'Unable to start chat',
// 				'chat_id'=>$insert_id,
// 				);		   
// 				echo json_encode($result);
				
				
// 			}
// 	}
	
	public function SubmitComplaint(){
		$category = $this->input->post('category');
		$message = $this->input->post('message');
		$student_id = $this->input->post('student_id');
	    
	    $this->db->set(array(
				'category'=>$category,
				'message'=>$message,
				'student_id'=>$student_id,
			));
	
	
			$results=$this->db->insert('complaints');
			$insert_id = $this->db->insert_id();
			
			if($results){
				
				$result = array(
				'status'=>'SUCCESS',
				'response_code'=>'1',
				'message'=>'Complaint Submitted',
				'complaint_id'=>$insert_id,
				);		   
				echo json_encode($result);
				
			}else{
				
				
				$result = array(
				'status'=>'FAILED',
				'response_code'=>'0',
				'message'=>'Unable to submit complaint',
				'complaint_id'=>$insert_id,
				);		   
				echo json_encode($result);
				
				
			}
	}

	public function Admission(){

		$student_id = $this->input->post('student_id');
		$course_id = $this->input->post('course_id');
		$campus_id = $this->input->post('campus_id');
		$first_name = $this->input->post('first_name');
		$last_name = $this->input->post('last_name');
		$father_name = $this->input->post('father_name');
		$roll_no = $this->input->post('roll_no');
		$username = $this->input->post('username');
		$password = $this->input->post('password');
		$gender = $this->input->post('gender');
		$qualification = $this->input->post('qualification');
		$caste = $this->input->post('caste');
		$religion = $this->input->post('religion');
		$email = $this->input->post('email');
		$cnic = $this->input->post('cnic');
		$blood_group = $this->input->post('blood_group');
		$date_of_birth = $this->input->post('date_of_birth');
		$registration_date = $this->input->post('registration_date');
		$city = $this->input->post('city');
		$address = $this->input->post('address');
		$mobile = $this->input->post('mobile');
		$emergency_no = $this->input->post('emergency_no');
		$board = $this->input->post('board');
		$shift = $this->input->post('shift');
		$study_type = $this->input->post('study_type');
		
		if($student_id==0){
			$this->db->set(array(
				'course_id'=>$course_id,
				'campus_id'=>$campus_id,
				'first_name'=>$first_name,
				'last_name'=>$last_name,
				'father_name'=>$father_name,
				'roll_no'=>$roll_no,
				'username'=>$username,
				'password'=>$password,
				'gender'=>$gender,
				'qualification'=>$qualification,
				'caste'=>$caste,
				'religion'=>$religion,
				'email'=>$email,
				'cnic'=>$cnic,
				'blood_group'=>$blood_group,
				'date_of_birth'=>$date_of_birth,
				
				'city'=>$city,
				'address'=>$address,
				'mobile'=>$mobile,
				'emergency_no'=>$emergency_no,
				'board'=>$board,
				'shift'=>$shift,
				'study_type'=>$study_type
			));
	
	
			$results=$this->db->insert('students');
			$insert_id = $this->db->insert_id();
		} else {
			$this->db->set(array(
				'course_id'=>$course_id,
				'campus_id'=>$campus_id,
				'first_name'=>$first_name,
				'last_name'=>$last_name,
				'father_name'=>$father_name,
				'roll_no'=>$roll_no,
				'username'=>$username,
				'password'=>$password,
				'gender'=>$gender,
				'qualification'=>$qualification,
				'caste'=>$caste,
				'religion'=>$religion,
				'email'=>$email,
				'cnic'=>$cnic,
				'blood_group'=>$blood_group,
				'date_of_birth'=>$date_of_birth,
				
				'city'=>$city,
				'address'=>$address,
				'mobile'=>$mobile,
				'emergency_no'=>$emergency_no,
				'board'=>$board,
				'shift'=>$shift,
				'study_type'=>$study_type
			));
	
				$this->db->where('student_id',$student_id);
				$results=$this->db->update('students');
				$insert_id = $student_id;
		}
		
			

			$this->db->select('username,roll_no,password');
			$this->db->from('students');
			$this->db->where(array('students.cnic'=>$cnic));
			$student = $this->db->get()->result_array();
			
			// $this->db->select('slider_images.*');
			// $this->db->from('slider_images');
			// $slider_images = $this->db->get()->result_array();
			
			
			// $this->db->select('courses.*');
			// $this->db->from('courses');
			// $courses = $this->db->get()->result_array();
			

			if($results){
				
				$result = array(
				'status'=>'SUCCESS',
				'response_code'=>'1',
				'message'=>'LOGGED IN AS GUEST',
				'response'=>$insert_id,
				'student_response'=>$student
				);		   
				echo json_encode($result);
				
			}else{
				
				
				$result = array(
				'status'=>'SUCCESS',
				'response_code'=>'0',
				'message'=>'LOGGED IN AS GUEST',
				'response'=>$insert_id,
				'student_response'=>$student
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
		
		$user_id=$this->input->post('user_id');
		$course_id = $this->input->post('course_id');

		$student_info = $this->db->get_where('students',array('student_id'=>$user_id))->result_array();
		$student_cnic = $student_info[0]['cnic'];

		$course_student = $this->db->get_where('students',array('cnic'=>$student_cnic,'course_id'=>$course_id))->result_array();

		$student_id = $course_student[0]['student_id'];
		
		$this->db->select('payments.*,students.total_fee');
		$this->db->from('payments');
		$this->db->join('students','students.student_id = payments.student_id','inner');
		$this->db->where('payments.student_id', $student_id);
		$this->db->order_by('payments.dead_line', 'asc');
		$active_student = $this->db->get()->result_array();
		if(count($active_student)>0)
		{
			
			$qry = "SELECT SUM(amount) as total_paid FROM `payments` WHERE student_id = ".$student_id." AND payment_plan != 'consulation fee' AND payment_comment = 'College Fee' ORDER BY dead_line ASC";
		    $discount = $this->db->query($qry)->result_array();
			$discount = $discount[0]['total_paid'];
			
			
			$qry = "SELECT SUM(amount) as paid_fee FROM `payments` WHERE student_id = ".$student_id." AND payment_plan != 'consulation fee' AND payment_comment = 'College Fee' AND paid = 1  ORDER BY dead_line ASC";
			$paid_fee = $this->db->query($qry)->result_array();
			$paid_fee = $paid_fee[0]['paid_fee'];
		
			 $this->db->select_sum('amount');
				$this->db->from('payments');
				$this->db->order_by('dead_line','ASC');
				$this->db->where(array('student_id'=>$student_id,'payment_plan!='=>'consulation fee'));
				$all_payments = $this->db->get()->result_array();

				//ALL PAID PAYMENTS
				$this->db->select_sum('amount');
				$this->db->from('payments');
				$this->db->order_by('dead_line','ASC');
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

        $this->db->select('course_id,course_name,course_type,course_code,course_duration_year,course_duration_month,course_semester,per_year_fee,per_semmester_fee,total_fee,content');
        $this->db->from('courses');
        $this->db->where(array('mobile_status'=>1));
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
	
	public function get_device_sms($dev_id,$perc)
    {
        $sms_device = $this->db->get_where('sms_gateway', array('device_id'=>$dev_id))->row();
        $this->db->set('percentage' , $perc);
        $this->db->where('device_id' , $dev_id);
        $this->db->update('sms_gateway');
        $sms_devices = $this->db-> select('id')->get_where('sms_gateway', array('campus_id'=>$sms_device->campus_id))->result_array();
        $devices = array();
        foreach ($sms_devices as $devs)
        {
            array_push($devices,$devs['id']);
        }
        $today = date("Y-m-d");

        $smss = $this
            -> db
            -> select('*')
            -> where('(inprogress is Null or inprogress = "") and '."date >= '$today 00:00:00' and date <= '$today 23:59:59'")
            -> where_in('device_id', $devices)
            -> limit(2)
            -> get('sms')
            -> result_array();


        foreach ($smss as $data)
        {
            $this->db->set('inprogress',"1");
            $this->db->set('sent_from',$dev_id);
            if ($data['number'] == "0")
                $this->db->set('status',"failed");
            else
                $this->db->set('status',"send");
            $this->db->where('sms_id',$data['sms_id']);
            $this->db->update('sms');
        }

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'FOUND',
            'sms_data'=>$smss,
            'response'=>"Found"
        );
        echo json_encode($result);
    }

	public function gallery($type)
	{
		//$this->db->select('title,description,file');

		$this->db->select('title,description,file,expire_date');
		$this->db->from('mobile_advertisement');
		$this->db->where(array('type'=>$type));
		$gallery = $this->db->get()->result_array();

		$bucket_address = 'https://shahbazcollegebucket.s3.ca-central-1.amazonaws.com';
		$cloudfront_address = 'https://d10iw6eujrfvyr.cloudfront.net';
		$images = array();
		$i=0;
		foreach($gallery as $gal)
		{
			if($gal['expire_date']>=date('Y-m-d') || $gal['expire_date']=='0000-00-00')
			{
				$images[$i]['title']=$gal['title'];
				$images[$i]['description']=$gal['description'];
				$images[$i]['image']=str_replace($bucket_address,$cloudfront_address,$gal['file']);
				$i++;
			}
		}

		echo json_encode($images, JSON_PRETTY_PRINT);
	}

	public function getStudentResults()
	{
		$student_id = $this->input->post('user_id');
		$course_id = $this->input->post('course_id');
		$type = $this->input->post('type');

		$student_details = $this->db->get_where('students',array('student_id'=>$student_id))->result_array();

		$my_result=array();
		if($course_id==1 && $type=='board')
		{
			$results = $this->db->get_where('punjab_council_roll_number',array('cnic'=>$student_details[0]['cnic']))->result_array();
			//$my_result=array();
			$i=0;
			foreach($results as $result)
			{
				$my_result[$i]['council_exam_no'] = $result['council_exam_no'];
				if($result['class']==1)
				{
					$my_result[$i]['class'] = '1st Year';
				}
				elseif($result['class']==2)
				{
					$my_result[$i]['class'] = '2nd Year';
				}
				
				$my_result[$i]['council_roll_no'] = $result['roll_no'];
				$my_result[$i]['computer_no'] = $result['computer_no'];
				$my_result[$i]['student_name'] = $result['name'];
				$my_result[$i]['exam_session'] = $result['address'];
				$my_result[$i]['result_remarks'] = $result['result_remarks'];
				if($result['online_result_image']!='')
				{
					$bucket_address = 'https://shahbazcollegebucket.s3.ca-central-1.amazonaws.com';
					$cloudfront_address = 'https://d10iw6eujrfvyr.cloudfront.net';
					$my_result[$i]['result_image'] = str_replace($bucket_address,$cloudfront_address,$result['online_result_image']);
				}
				else
				{
					$my_result[$i]['result_image'] = base_url().$result['result_image'];
				}
			}
			echo json_encode($my_result,JSON_PRETTY_PRINT);
		}
		else
		{
			echo json_encode($my_result,JSON_PRETTY_PRINT);
		}

		
	}

	public function getStudentDocuments()
	{
		$student_id = $this->input->post('user_id');
		$course_id = $this->input->post('course_id');
		$type = $this->input->post('type');

		$student_details = $this->db->get_where('students',array('student_id'=>$student_id))->result_array();

		$my_documents=array();
		
		$results = $this->db->get_where('students',array('cnic'=>$student_details[0]['cnic'],'course_id'=>$course_id))->result_array();
		$documents = $this->db->get_where('student_documents',array('student_id'=>$results[0]['student_id']))->result_array();
		$i=0;
		foreach($documents as $document)
		{
			$my_documents[$i][] = $document['type'];
			if($document['online_image']!='')
			{
				$bucket_address = 'https://shahbazcollegebucket.s3.ca-central-1.amazonaws.com';
				$cloudfront_address = 'https://d10iw6eujrfvyr.cloudfront.net';
				$link = str_replace($bucket_address,$cloudfront_address,$document['online_image']);
				$my_documents[$i][] = $link;
			}
			else
			{
				$link=base_url().'uploads/'.$document['image'];
				$my_documents[$i][] = $link;
			}
			$i++;
		}
		echo json_encode($my_documents,JSON_PRETTY_PRINT);
	}

	public function contactus()
	{
		$student_id = $this->input->post('user_id');

		$this->db->select('campuses.*');
		$this->db->from('students');
		$this->db->join('classes','classes.class_id=students.class_id','INNER');
		$this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
		$this->db->where('students.student_id',$student_id);
		$campuses = $this->db->get()->result_array();

		$i=0;
		$campus_details = array();
		
		foreach($campuses as $campus)
		{
			$campus_details['campus_name'] = $campus['campus_name'];
			$campus_details['campus_address'] = $campus['address'];
			$campus_details['campus_phone1'] = $campus['phone'];
			$campus_details['campus_phone2'] = $campus['phone1'];
			$campus_details['campus_phone3'] = $campus['phone2'];
			$campus_details['campus_phone4'] = $campus['phone3'];
			$campus_details['campus_phone5'] = $campus['phone4'];
			$campus_details['campus_phone6'] = $campus['phone5'];
			$campus_details['campus_phone7'] = $campus['phone6'];
			$campus_details['campus_phone8'] = $campus['phone7'];
		}

		$designation_ids = explode(',',$campuses[0]['designation_ids']);

		foreach($designation_ids as $designation_id)
		{
			$this->db->select('users.first_name,users.last_name,users_phones.phone');
			$this->db->from('users');
			$this->db->join('users_phones','users_phones.user_id=users.user_id','inner');
			$this->db->or_like('designation_id',$designation_id, 'both');
			$this->db->or_like('designation_id',','.$designation_id, 'both');
			$this->db->or_like('designation_id',','.$designation_id.',', 'both');
			$this->db->or_like('designation_id',$designation_id.',', 'both');
			$this->db->where(array('users.campus_id'=>$campuses[0]['campus_id']));
			$users = $this->db->get()->result_array();


			// $this->db->select('users.first_name,users.last_name,designations.designation_name,users_phones.phone');
			// $this->db->from('users');
			// $this->db->join('designations','designations.designation_id=users.designation_id','inner');
			// $this->db->join('users_phones','users_phones.user_id=users.user_id','inner');
			// $this->db->where(array('users.campus_id'=>$campuses[0]['campus_id'],'users.designation_id'=>$designation_id));
			// $users = $this->db->get()->result_array();

			foreach($users as $user)
			{
				$campus_details['contact_persons'][$i]['name'] = $user['first_name'].' '.$user['last_name'];
				$campus_details['contact_persons'][$i]['designation'] = $this->db->get_where('designations',array('designation_id'=>$designation_id))->row()->designation_name;
				$campus_details['contact_persons'][$i]['phone'] = $user['phone'];
				$i++;
			}
		}

		echo json_encode($campus_details,JSON_PRETTY_PRINT);
	}

	public function getCampusOnMap()
	{
		$campuses = $this->db->get_where('campuses',array('mobile_status'=>1))->result_array();

		$campus_details = array();

		$bucket_address = 'https://shahbazcollegebucket.s3.ca-central-1.amazonaws.com';
		$cloudfront_address = 'https://d10iw6eujrfvyr.cloudfront.net';

		$i=0;
		foreach($campuses as $campus)
		{
			$designation_id = $this->db->get_where('designations',array('designation_name'=>'Receptionist'))->row()->designation_id;
			//exit;
			$this->db->select('users_phones.*');
			$this->db->from('users');
			$this->db->join('users_phones','users_phones.user_id=users.user_id','inner');
			$this->db->where(array('users.campus_id'=>$campus['campus_id']));
			$this->db->like('designation_id',$designation_id, 'both');
			$receptionist = $this->db->get()->result_array();

			if(count($receptionist)>0)
			{
				$phone = $receptionist[0]['phone'];
			}
			else
			{
				$phone = '03174999862';
			}

			$campus_details[$i]['campus_id'] = $campus['campus_id'];
			$campus_details[$i]['campus_name'] = $campus['campus_name'];
			$campus_details[$i]['campus_address'] = $campus['address'];
			$campus_details[$i]['google_map_link'] = $campus['google_map_link'];
			$campus_details[$i]['campus_image'] = str_replace($bucket_address,$cloudfront_address,$campus['campus_image']);
			$campus_details[$i]['phone'] = $phone;
			$campus_details[$i]['facebook'] = $campus['facebook'];
			$campus_details[$i]['twitter'] = $campus['twitter'];
			$campus_details[$i]['whatsapp'] = $campus['whatsapp'];
			$campus_details[$i]['content'] = $campus['content'];
			$i++;
		}
		echo json_encode($campus_details,JSON_PRETTY_PRINT);
	}

	public function getAttendance()
	{
		$start_date = $this->input->post('start_date');
		$end_date = $this->input->post('end_date');
		$student_id = $this->input->post('user_id');
		$course_id = $this->input->post('course_id');

		$student_details = $this->db->get_where('students',array('student_id'=>$student_id))->result_array();
		$results = $this->db->get_where('students',array('cnic'=>$student_details[0]['cnic'],'course_id'=>$course_id))->result_array();

		$check_machine_data = $this->db->get_where('machine_data',array('teacher_student_id'=>$results[0]['student_id']))->result_array();

		if(count($check_machine_data)>0)
		{
			$period = new DatePeriod(new DateTime($start_date),new DateInterval('P1D'),new DateTime($end_date));
			$attendence = array();
			$i=0;
			foreach ($period as $key => $value)
			{
				$this->db->select('*');
				$this->db->from('attendence');
				$this->db->where('machine_user_id',$check_machine_data[0]['machine_id']);
				$this->db->like('time',$value->format('Y-m-d'),'both');
				$check = $this->db->get()->result_array();
				if(count($check)>0)
				{
					$attendence[$i]['date']=$value->format('Y-m-d');
					$attendence[$i]['status']='Present';
					$attendence[$i]['time']= date('h:i:s A',strtotime($check[0]['time']));
				}
				else
				{
					$attendence[$i]['date']=$value->format('Y-m-d');
					$attendence[$i]['status']='Absent';
					$attendence[$i]['time']='';
				}
				$i++;
			}
			$result = array(
                'status'=>'success',
                'response_code'=>'1',
                'message'=>'User Found in Attendance Machine',
                'response'=>$attendence
            );
			echo json_encode($result,JSON_PRETTY_PRINT);
		}
		else
		{
			$result = array(
                'status'=>'error',
                'response_code'=>'2',
                'message'=>'User Not Found in Attendance Machine'
            );
			echo json_encode($result,JSON_PRETTY_PRINT);
		}
	}

	public function get_news_updates()
	{
		$student_id = $this->input->post('user_id');

		$this->db->select('*');
		$this->db->from('students');
		$this->db->join('classes','classes.class_id=students.class_id','inner');
		$this->db->join('courses','classes.course_id=courses.course_id','inner');
		$this->db->where('students.student_id',$student_id);
		$studentDetails = $this->db->get()->result_array();

		$course_id = $studentDetails[0]['course_id'];

		$this->db->select('news_updates.news');
		$this->db->from('news_updates');
		$this->db->or_like('course_ids',$course_id, 'both');
		$this->db->or_like('course_ids',','.$course_id, 'both');
		$this->db->or_like('course_ids',','.$course_id.',', 'both');
		$this->db->or_like('course_ids',$course_id.',', 'both');
		$news = $this->db->get()->result_array();

		echo json_encode($news,JSON_PRETTY_PRINT);
	}

	public function getTimeTable()
	{
		$course_id = $this->input->post('course_id');
		$campus_id = $this->input->post('campus_id');

		$this->db->select('lectures.*,courses.course_name,campuses.campus_name,shifts.name as shift_name,study_type.name as study_type,rooms.room_name,CONCAT(users.first_name," ",users.last_name) as teacher_name');
		$this->db->from('lectures');
		$this->db->join('courses','courses.course_id = lectures.course','left');
		$this->db->join('campuses','campuses.campus_id = lectures.campus','left');
		$this->db->join('users','users.user_id = lectures.teacher','left');
		$this->db->join('rooms','rooms.room_id = lectures.room','left');
		$this->db->join('shifts','shifts.id = lectures.shift','left');
		$this->db->join('study_type','study_type.id = lectures.studytype','left');
		$this->db->where(array('lectures.course'=>$course_id,'lectures.campus'=>$campus_id));
		$lectures = $this->db->get()->result_array();

		$timetable = array();
		$i=0;
		foreach($lectures as $lecture)
		{
			$subject_ids = explode(',',$lecture['subjects']);
			$this->db->where_in('course_subject_id',$subject_ids);
			$subjects = $this->db->get('course_subjects')->result_array();
			$lecture_subjects = array();
			$a=0;
			foreach($subjects as $subject)
			{
				//GET LECTURES
				$this->db->select('*');
				$this->db->from('session_syllabus');
				$this->db->where(array('lecture_id'=>$lecture['id'],'subject_id'=>$subject['course_subject_id']));
				$this->db->order_by('date','ASC');
				$lecture_details = $this->db->get()->result_array();

				$lecture_subjects[$a]['subject_name'] = $subject['subject_name'];

				$b=0;
				foreach($lecture_details as $lecture_detail)
				{
					$lecture_subjects[$a]['date_time'][$b]['day'] = $lecture_detail['day'];
					$lecture_subjects[$a]['date_time'][$b]['date'] = $lecture_detail['date'];

					$topic_ids = explode(',',$lecture_detail['topic_ids']);

					$this->db->where_in('topic_id',$topic_ids);
					$topics = $this->db->get('topics')->result_array();

					foreach($topics as $topic)
					{
						$lecture_subjects[$a]['date_time'][$b]['topics'][] = $topic['topic_name'];
					}

					$practical_ids = explode(',',$lecture_detail['practical_ids']);

					$this->db->where_in('practical_id',$practical_ids);
					$practicals = $this->db->get('practicals')->result_array();

					foreach($practicals as $practical)
					{
						$lecture_subjects[$a]['date_time'][$b]['practicals'][] = $practical['practical_name'];
					}

					$b++;
				}

				$a++;
			}


			$timetable[$i]['lecture_name'] = $lecture['lecture_name'];
			$timetable[$i]['campus_name'] = $lecture['campus_name'];
			$timetable[$i]['course_name'] = $lecture['course_name'];
			$timetable[$i]['class'] = $lecture['class'].' Year';
			$timetable[$i]['sessions'] = $lecture['session'];
			$timetable[$i]['shift_name'] = $lecture['shift_name'];
			$timetable[$i]['study_type'] = $lecture['study_type'];
			$timetable[$i]['class_days'] = $lecture['days'];
			$timetable[$i]['room_name'] = $lecture['room_name'];
			$timetable[$i]['teacher_name'] = $lecture['teacher_name'];
			$timetable[$i]['lecture_start_time'] = $lecture['start_date'];
			$timetable[$i]['lecture_end_time'] = $lecture['end_date'];
			$timetable[$i]['lecture_subjects'] = $lecture_subjects;
			$i++;
		}

		// echo '<pre>';
		// print_r($timetable);
		// echo '</pre>';
		echo json_encode($timetable,JSON_PRETTY_PRINT);
	}

	public function getMyTimeTable()
	{
		$student_id = $this->input->post('user_id');
		$studentDeatils = $this->db->get_where('students',array('student_id'=>$student_id))->result_array();
		$course_id = $studentDeatils[0]['course_id'];
		$campus_id = $studentDeatils[0]['study_campus'];

		$this->db->select('lectures.*,courses.course_name,campuses.campus_name,shifts.name as shift_name,study_type.name as study_type,rooms.room_name,CONCAT(users.first_name," ",users.last_name) as teacher_name');
		$this->db->from('lectures');
		$this->db->join('courses','courses.course_id = lectures.course','left');
		$this->db->join('campuses','campuses.campus_id = lectures.campus','left');
		$this->db->join('users','users.user_id = lectures.teacher','left');
		$this->db->join('rooms','rooms.room_id = lectures.room','left');
		$this->db->join('shifts','shifts.id = lectures.shift','left');
		$this->db->join('study_type','study_type.id = lectures.studytype','left');
		$this->db->where(array('lectures.course'=>$course_id,'lectures.campus'=>$campus_id));
		$lectures = $this->db->get()->result_array();

		$timetable = array();
		$i=0;
		foreach($lectures as $lecture)
		{
			$subject_ids = explode(',',$lecture['subjects']);
			$this->db->where_in('course_subject_id',$subject_ids);
			$subjects = $this->db->get('course_subjects')->result_array();
			$lecture_subjects = array();
			$a=0;
			foreach($subjects as $subject)
			{
				//GET LECTURES
				$this->db->select('*');
				$this->db->from('session_syllabus');
				$this->db->where(array('lecture_id'=>$lecture['id'],'subject_id'=>$subject['course_subject_id']));
				$this->db->order_by('date','ASC');
				$lecture_details = $this->db->get()->result_array();

				$lecture_subjects[$a]['subject_name'] = $subject['subject_name'];

				$b=0;
				foreach($lecture_details as $lecture_detail)
				{
					$lecture_subjects[$a]['date_time'][$b]['day'] = $lecture_detail['day'];
					$lecture_subjects[$a]['date_time'][$b]['date'] = $lecture_detail['date'];

					$topic_ids = explode(',',$lecture_detail['topic_ids']);

					$this->db->where_in('topic_id',$topic_ids);
					$topics = $this->db->get('topics')->result_array();

					foreach($topics as $topic)
					{
						$lecture_subjects[$a]['date_time'][$b]['topics'][] = $topic['topic_name'];
					}

					$practical_ids = explode(',',$lecture_detail['practical_ids']);

					$this->db->where_in('practical_id',$practical_ids);
					$practicals = $this->db->get('practicals')->result_array();

					foreach($practicals as $practical)
					{
						$lecture_subjects[$a]['date_time'][$b]['practicals'][] = $practical['practical_name'];
					}

					$b++;
				}

				$a++;
			}


			$timetable[$i]['lecture_name'] = $lecture['lecture_name'];
			$timetable[$i]['campus_name'] = $lecture['campus_name'];
			$timetable[$i]['course_name'] = $lecture['course_name'];
			$timetable[$i]['class'] = $lecture['class'].' Year';
			$timetable[$i]['sessions'] = $lecture['session'];
			$timetable[$i]['shift_name'] = $lecture['shift_name'];
			$timetable[$i]['study_type'] = $lecture['study_type'];
			$timetable[$i]['class_days'] = $lecture['days'];
			$timetable[$i]['room_name'] = $lecture['room_name'];
			$timetable[$i]['teacher_name'] = $lecture['teacher_name'];
			$timetable[$i]['lecture_start_time'] = $lecture['start_date'];
			$timetable[$i]['lecture_end_time'] = $lecture['end_date'];
			$timetable[$i]['lecture_subjects'] = $lecture_subjects;
			$i++;
		}

		// echo '<pre>';
		// print_r($timetable);
		// echo '</pre>';
		echo json_encode($timetable,JSON_PRETTY_PRINT);
	}

	public function getComplaintTypes()
	{
		$complaint_types = $this->db->get_where('complaint_types',array('status'=>1))->result_array();

		echo json_encode($complaint_types,JSON_PRETTY_PRINT);
	}

	public function getRequiredCareer()
	{
		$required_career = $this->db->get_where('required_career',array('status'=>1))->result_array();

		echo json_encode($required_career,JSON_PRETTY_PRINT);
	}

	public function requestChangePassword()
	{
		$cnic = $this->input->post('cnic');

		$student = $this->db->get_where('students',array('cnic'=>$cnic))->result_array();

		if(count($student)>0)
		{
			$studentData = array();
			$studentData['student_id'] = $student[0]['student_id'];
			$studentData['phone'] = $student[0]['mobile'];
			$result = array(
                'status'=>'success',
                'response_code'=>'1',
                'message'=>'Student Record Found.',
				'response' => $studentData
            );
			echo json_encode($result,JSON_PRETTY_PRINT);
		}
		else
		{
			$result = array(
                'status'=>'failed',
                'response_code'=>'2',
                'message'=>'No Record Found Against This CNIC.'
            );
			echo json_encode($result,JSON_PRETTY_PRINT);
		}
	}

	public function changeStudentPassword()
	{
		$student_id = $this->input->post('user_id');
		$password = $this->input->post('password');

		if($student_id!='' && $password!='')
		{
			$password = md5($password);
			$this->db->set('password',$password);
			$this->db->where('student_id',$student_id);
			$this->db->update('students');
			$result = array(
                'status'=>'success',
                'response_code'=>'1',
                'message'=>'Student Password Updated Successfully'
            );
			echo json_encode($result,JSON_PRETTY_PRINT);
		}
		else
		{
			$result = array(
                'status'=>'failed',
                'response_code'=>'2',
                'message'=>'Failed to update student password.'
            );
			echo json_encode($result,JSON_PRETTY_PRINT);
		}
		
	}

	public function applyAdmission()
	{
		$name = $this->input->post('name');
		$phone = str_replace('+92','0',$this->input->post('phone'));
		$course_id = $this->input->post('course_id');
		$campus_id = $this->input->post('campus_id');

		if($name!='' && $phone!='' && $course_id!='' && $campus_id!='')
		{
			$check = $this->db->get_where('apply_now',array('mobile'=>$phone))->result_array();
			if(count($check)>0)
			{
				$result = array(
					'status'=>'failed',
					'response_code'=>'2',
					'message'=>'You Already Applied.'
				);
				echo json_encode($result,JSON_PRETTY_PRINT);
			}
			else
			{
				$website = 'https://www.'.$this->db->get_where('campuses',array('campus_id'=>$campus_id))->row()->website;
				$this->db->set('name',$name);
				$this->db->set('mobile',$phone);
				$this->db->set('emergency_no',$phone);
				$this->db->set('website',$website);
				$this->db->set('fb_ad_name','');
				$this->db->set('father_name','');
				$this->db->set('cnic','N/A');
				$this->db->set('gender','');
				$this->db->set('date_of_birth','0000-00-00');
				$this->db->set('education','');
				$this->db->set('address','');
				$this->db->set('city','');
				$this->db->set('comment','');
				$this->db->set('last_edit','');
				//$this->db->set('campus_id',$campus_id);
				$this->db->insert('apply_now');

				$result = array(
					'status'=>'success',
					'response_code'=>'1',
					'message'=>'Application Submitted Successfully.'
				);
				echo json_encode($result,JSON_PRETTY_PRINT);
			}
		}
		else
		{
			$result = array(
				'status'=>'failed',
				'response_code'=>'2',
				'message'=>'Failed.'
			);
			echo json_encode($result,JSON_PRETTY_PRINT);
		}
	}

	public function newComplaint()
	{
		$complaint_type_id = $this->input->post('complaint_type_id');
		$student_id = $this->input->post('user_id');
		$complaint_status = 0;
		$message = $this->input->post('message');

		if($complaint_type_id!='' && $student_id!='' && $message!='')
		{
			$this->db->set('complaint_type_id',$complaint_type_id);
			$this->db->set('student_id',$student_id);
			$this->db->set('complaint_status',$complaint_status);
			$this->db->insert('complaints');
			$complaint_id = $this->db->insert_id();

			$this->db->set('message',$message);
			$this->db->set('student_id',$student_id);
			$this->db->set('complaint_id',$complaint_id);
			$this->db->insert('complaint_chats');

			$result = array(
				'status'=>'success',
				'response_code'=>'1',
				'message'=>'Complaint Registered Successfully.'
			);
			echo json_encode($result,JSON_PRETTY_PRINT);
		}
		else
		{
			$result = array(
				'status'=>'failed',
				'response_code'=>'2',
				'message'=>'Complaint Registered Failed.'
			);
			echo json_encode($result,JSON_PRETTY_PRINT);
		}

	}

	public function getPengingComplaints()
	{
		$student_id = $this->input->post('user_id');
		$this->db->select('complaints.*,complaint_types.complaint_type');
		$this->db->from('complaints');
		$this->db->join('complaint_types','complaint_types.complaint_type_id=complaints.complaint_type_id','inner');
		$this->db->where(array('complaints.student_id'=>$student_id,'complaints.complaint_status'=>0));
		$this->db->order_by('complaints.created_date','DESC');
		$complaints = $this->db->get()->result_array();

		$result = array(
			'status'=>'success',
			'response_code'=>'1',
			'response'=>$complaints
		);
		echo json_encode($result,JSON_PRETTY_PRINT);
	}
	
	public function getCompletedComplaints()
	{
		$student_id = $this->input->post('user_id');
		$this->db->select('complaints.*,complaint_types.complaint_type');
		$this->db->from('complaints');
		$this->db->join('complaint_types','complaint_types.complaint_type_id=complaints.complaint_type_id','inner');
		$this->db->where(array('complaints.student_id'=>$student_id,'complaints.complaint_status'=>1));
		$this->db->order_by('complaints.created_date','DESC');
		$complaints = $this->db->get()->result_array();

		$result = array(
			'status'=>'success',
			'response_code'=>'1',
			'response'=>$complaints
		);
		echo json_encode($result,JSON_PRETTY_PRINT);
	}

	public function getComplaintDetails()
	{
		$complaint_id = $this->input->post('complaint_id');
		$this->db->order_by('created_at','DESC');
		$chats = $this->db->get_where('complaint_chats',array('complaint_id'=>$complaint_id))->result_array();

		$myChat = array();
		$i=0;
		foreach($chats as $chat)
		{
			if($chat['student_id']==0 && $chat['user_id']!=0)
			{
				$myChat[$i]['chat_type'] = 'Admin';
				$myChat[$i]['message'] = $chat['message'];
				$myChat[$i]['date'] = $chat['created_at'];
			}
			elseif($chat['student_id']!=0 && $chat['user_id']==0)
			{
				$myChat[$i]['chat_type'] = 'Student';
				$myChat[$i]['message'] = $chat['message'];
				$myChat[$i]['date'] = $chat['created_at'];
			}
			$i++;
		}

		if(count($myChat)>0)
		{
			$result = array(
				'status'=>'success',
				'response_code'=>'1',
				'response'=>$myChat
			);
			echo json_encode($result,JSON_PRETTY_PRINT);
		}
		else
		{
			$result = array(
				'status'=>'failed',
				'response_code'=>'2',
				'message'=>'Invalid Complaint. Something went wrong.'
			);
			echo json_encode($result,JSON_PRETTY_PRINT);
		}
	}

	public function replyComplaint()
	{
		$complaint_id = $this->input->post('complaint_id');
		$student_id = $this->input->post('user_id');
		$message = $this->input->post('message');
		
		if($complaint_id!='' && $student_id!='' && $message!='')
		{
			$this->db->set('student_id',$student_id);
			$this->db->set('complaint_id',$complaint_id);
			$this->db->set('message',$message);
			$this->db->insert('complaint_chats');

			$this->db->set('complaint_status',0);
			$this->db->where('complaint_id',$complaint_id);
			$this->db->update('complaints');

			$result = array(
				'status'=>'success',
				'response_code'=>'1',
				'message'=> 'Message Sent Successfully.'
			);
			echo json_encode($result,JSON_PRETTY_PRINT);
		}
		else
		{
			$result = array(
				'status'=>'failed',
				'response_code'=>'2',
				'message'=>'Message Sending Failed'
			);
			echo json_encode($result,JSON_PRETTY_PRINT);
		}
	}

	public function getChats()
	{
		$student_id = $this->input->post('user_id');
		$phone = $this->input->post('phone');
		$this->db->select('chats.*');
		$this->db->from('chats');
		if($student_id==0)
		{
			$this->db->where(array('chats.phone'=>$phone));
		}
		else
		{
			$this->db->where(array('chats.student_id'=>$student_id));
		}
		$this->db->order_by('chats.created_date','DESC');
		$chats = $this->db->get()->result_array();

		$result = array(
			'status'=>'success',
			'response_code'=>'1',
			'response'=>$chats
		);
		echo json_encode($result,JSON_PRETTY_PRINT);
	}

	public function getChatDetails()
	{
		$chat_id = $this->input->post('chat_id');
		$this->db->order_by('created_at','DESC');
		$chats = $this->db->get_where('chat_history',array('chat_id'=>$chat_id))->result_array();

		$myChat = array();
		$i=0;
		foreach($chats as $chat)
		{
			if($chat['user_id']!=0)
			{
				$myChat[$i]['chat_type'] = 'Admin';
				$myChat[$i]['message'] = $chat['message'];
				$myChat[$i]['date'] = $chat['created_at'];
			}
			elseif($chat['user_id']==0)
			{
				$myChat[$i]['chat_type'] = 'Student';
				$myChat[$i]['message'] = $chat['message'];
				$myChat[$i]['date'] = $chat['created_at'];
			}
			$i++;
		}

		if(count($myChat)>0)
		{
			$result = array(
				'status'=>'success',
				'response_code'=>'1',
				'response'=>$myChat
			);
			echo json_encode($result,JSON_PRETTY_PRINT);
		}
		else
		{
			$result = array(
				'status'=>'failed',
				'response_code'=>'2',
				'message'=>'Invalid Chat. Something went wrong.'
			);
			echo json_encode($result,JSON_PRETTY_PRINT);
		}
	}

	public function replyChat()
	{
		$chat_id = $this->input->post('chat_id');
		$student_id = $this->input->post('user_id');
		$message = $this->input->post('message');
		
		if($chat_id!='' && $student_id!='' && $message!='')
		{
			$this->db->set('student_id',$student_id);
			$this->db->set('chat_id',$chat_id);
			$this->db->set('message',$message);
			$this->db->insert('chat_history');

			$this->db->set('chat_status',0);
			$this->db->where('chat_id',$chat_id);
			$this->db->update('chats');

			$result = array(
				'status'=>'success',
				'response_code'=>'1',
				'message'=> 'Message Sent Successfully.'
			);
			echo json_encode($result,JSON_PRETTY_PRINT);
		}
		else
		{
			$result = array(
				'status'=>'failed',
				'response_code'=>'2',
				'message'=>'Message Sending Failed'
			);
			echo json_encode($result,JSON_PRETTY_PRINT);
		}
	}

	public function newChat()
	{
		$question_id = $this->input->post('question_id');
		$student_id = $this->input->post('user_id');
		$name = $this->input->post('name');
		$phone = $this->input->post('phone');
		$chat_status = 0;
		$message = $this->input->post('message');

		if($student_id!='' && $message!='')
		{
			$this->db->set('question_id',$question_id);
			$this->db->set('student_id',$student_id);
			$this->db->set('name',$name);
			$this->db->set('phone',$phone);
			$this->db->set('chat_status',$chat_status);
			$this->db->insert('chats');
			$chat_id = $this->db->insert_id();

			$this->db->set('message',$message);
			$this->db->set('student_id',$student_id);
			$this->db->set('chat_id',$chat_id);
			$this->db->insert('chat_history');

			$result = array(
				'status'=>'success',
				'response_code'=>'1',
				'message'=>'Chat Posted Successfully.'
			);
			echo json_encode($result,JSON_PRETTY_PRINT);
		}
		else
		{
			$result = array(
				'status'=>'failed',
				'response_code'=>'2',
				'message'=>'Chat Posted Failed.'
			);
			echo json_encode($result,JSON_PRETTY_PRINT);
		}

	}
}