<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Council_list extends CI_Controller {
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
		$this->load->model('council');	
		$this->load->model('clas');
	}
	
	public function index()
	{
		$data['campuses'] = $this->clas->getCampuses();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('council_list/index', $data);
		$this->load->view('inc/footer');
	}
	public function fee_detail()
	{
		$data['campuses'] = $this->clas->getCampuses();
		$data['courses'] = $this->clas->getCourses();
		//$data['classes'] = $this->council->getClasses();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('council_list/fee_detail', $data);
		$this->load->view('inc/footer');
	}
	
	public function create()
	{
		$class_id = $this->input->post('class_id');
		$result = $this->council->getClassStudents($class_id);
		// Clear any previous output
		ob_end_clean();
		// I assume you already have your $result
		$num_fields = count($result);
		//Headings
		$heading = array(
					'Sr. #',
					'Student ID',
					'Roll #',
					'CNIC No.',
					'Name & Father Name',
					'Postal Address',
					'Student Mobile Number',
					'Board Name',
					'Institute Contact Number',
					);
		// Fetch MySQL result headers
		$headers = array();
		//$headers[] = "[Row]";
		for ($i = 0; $i <= 8; $i++) {
			$headers[] = $heading[$i];
		}
		
		// Filename with current date
		$filename = "Shahbaz-College-Council-List-of-Students.csv";
		
		// Open php output stream and write headers
		$fp = fopen('php://output', 'w');
		if ($fp && $result) {
			header('Content-Type: text/csv');
			header('Content-Disposition: attachment; filename='.$filename);
			header('Pragma: no-cache');
			header('Expires: 0');
			echo "List of Students \n\n";
			// Write mysql headers to csv
			fputcsv($fp, $headers);
			$row_tally = 0;
			// Write mysql rows to csv
			foreach($result as $student)
			{
				$row_tally++;
				echo $row_tally.",";
				fputcsv($fp, array_values($student));
			}
			die;
		}
	}

	public function print_councel(){
        $data['campuses'] = $this->clas->getCampuses();
        $class_id = $this->input->post('class_id');
        $campus_id = $this->input->post('campus_id');
        $data['class_id'] = $class_id;
        $data['campus_id'] = $campus_id;

        if( $class_id != ''){

            $data['result'] = $this->council->getClassStudents($class_id);
            // print_r($data['result']);
           // exit();
        }else{

        }


        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('council_list/print_councel', $data);
        $this->load->view('inc/footer');
    }
	public function get_print_of_concel_list($campus_id,$class_id){

        $this->db->select('*');
        $this->db->from('campuses');
        $this->db->where('campus_id', $campus_id);
        $data['campus'] = $this->db->get()->result_array();

        $this->db->select('classes.name , classes.exam_no ');
        $this->db->from('classes');
        $this->db->where('class_id', $class_id);
        $data['classess'] = $this->db->get()->result_array();

        $data['result'] = $this->council->getClassStudents($class_id);
        $this->load->view('council_list/get_print_of_concel_list', $data);
    }

    public function get_print_of_new_concel_list($campus_id,$class_id){

        $this->db->select('*');
        $this->db->from('campuses');
        $this->db->where('campus_id', $campus_id);
        $data['campus'] = $this->db->get()->result_array();

        $this->db->select('classes.name , classes.exam_no ');
        $this->db->from('classes');
        $this->db->where('class_id', $class_id);
        $data['classess'] = $this->db->get()->result_array();

        $data['result'] = $this->council->getClassStudentsDetails($class_id);
        $this->load->view('council_list/get_print_of_new_concel_list', $data);
    }

	public function create_council_fee()
	{
		$class_id = trim((string) $this->input->post('class_id'));
		$course_id = trim((string) $this->input->post('course_id'));
		$campus_id = trim((string) $this->input->post('campus_id'));
		$result = $this->council->getCouncilFeeStudents($class_id, $course_id, $campus_id, true);

		if (empty($result)) {
			$this->session->set_flashdata('error', 'No students found for selected filters.');
			redirect(site_url('council_list/fee_detail'));
			return;
		}

		$class_name = 'All-Classes';
		$course_name = 'All-Courses';
		$campus_name = 'All-Campuses';

		if ($class_id !== '') {
			$classRow = $this->db->get_where('classes', array('class_id' => $class_id))->row_array();
			if (!empty($classRow['name'])) {
				$class_name = preg_replace('/[^A-Za-z0-9\-_]+/', '-', trim($classRow['name']));
			}
		}

		if ($course_id !== '') {
			$courseRow = $this->db->get_where('courses', array('course_id' => $course_id))->row_array();
			if (!empty($courseRow['course_name'])) {
				$course_name = preg_replace('/[^A-Za-z0-9\-_]+/', '-', trim($courseRow['course_name']));
			}
		}

		if ($campus_id !== '') {
			$campusRow = $this->db->get_where('campuses', array('campus_id' => $campus_id))->row_array();
			if (!empty($campusRow['campus_name'])) {
				$campus_name = preg_replace('/[^A-Za-z0-9\-_]+/', '-', trim($campusRow['campus_name']));
			}
		}
		
		// Clear any previous output
		if (ob_get_level() > 0) {
			ob_end_clean();
		}
		// I assume you already have your $result
		$num_fields = count($result);
		//Headings
		$heading = array(
					'Student ID',
					'Roll #',
					'CNIC No.',
					'Name & Father Name',
					'Postal Address',
					'Student Mobile Number',
					'Board Name',
					'Institute Contact Number',
					'Total Fee',
					'Fee Decided Current Time',
					'Total Fee Submitted',
					'Remaining Fee Payable At Current Time',
					'Unpaid Installments AT Cuurent Time',
					'Fee Detail Paid',
					'Fee Detail Unpaid',
					'Percentage Fee Receive',
					'Percentage Paid Installments According to Decision',
					'Renew Installments',
					'Course Name',
					'Cast',
					'Qualification',
					'Campus',
					'Date of Birth',
					'Email',
					'City',
					'Student Card',
					'Gender',
					'Religion',
					'Class',
					'Registration Date',
					'System Registration Date',
					'Blood Group',
					'Books',
					'Emergency Number',
					'Section',
					'Student Type',
					'Shift',
					'Pharmacy Coucil Data',
					'Document Links',
					'Machine ID'
					);
		// Fetch MySQL result headers
		$headers = array();
		//$headers[] = "[Row]";
		for ($i = 0; $i <= 39; $i++) {
			$headers[] = $heading[$i];
		}
		
		// Filename with current date
		$filename = $campus_name.'_'.$course_name.'_'.$class_name.'_'.date('Y-m-d').'.csv';
		
		// Open php output stream and write headers
		//$fp = fopen(APPPATH . 'councilbackup/'.$filename, 'wb');
		$fp = fopen('php://output', 'w');
		if ($fp && $result) {
			header('Content-Type: text/csv');
			header('Content-Disposition: attachment; filename='.$filename);
			header('Pragma: no-cache');
			header('Expires: 0');
			// Write mysql headers to csv
			fputcsv($fp, $headers);
			$row_tally = 0;
			// Write mysql rows to csv
			foreach($result as $student)
			{
				$totalStudentFee = $this->council->getTotalFeeDetail($student['student_id']); 
				array_push($student, $totalStudentFee[0]['amount']);
				
				$studentFeeDecidedCurrentTime = $this->council->getFeeDecidedCurrentTime($student['student_id']);
				array_push($student, $studentFeeDecidedCurrentTime[0]['amount']);
				
				$totalStudentPaidFee = $this->council->getTotalPaidFeeDetail($student['student_id']); 
				array_push($student, $totalStudentPaidFee);
				
				array_push($student, $studentFeeDecidedCurrentTime[0]['amount']-$totalStudentPaidFee);
				
				$studentUnpaidFeeCurrentTime = $this->council->getUnpaidFeeDetailCurrentTime($student['student_id']);
				array_push($student, count($studentUnpaidFeeCurrentTime));
				
				$studentPaidFee = $this->council->getPaidFeeDetail($student['student_id']);
				$feeHTML = '';
				foreach($studentPaidFee as $feeStatus)
				{
						$feeHTML.= 'Rs '.$feeStatus['actual_amount'].' paid on '.$feeStatus['actual_paid_date'];
						$feeHTML.= ' | ';
				}
				array_push($student, $feeHTML);
				
				$studentUnpaidFee = $this->council->getUnpaidFeeDetail($student['student_id']);
				$feeHTML = '';
				foreach($studentUnpaidFee as $feeStatus)
				{
						$feeHTML.= 'Fee '.$feeStatus['amount'].' not paid on '.$feeStatus['dead_line'];
						$feeHTML.= ' | ';
				}
				array_push($student, $feeHTML);
				
				if($totalStudentPaidFee==0 || $totalStudentFee[0]['amount']==0)
				{
					array_push($student, 'N/A');	
				}
				else
				{
					array_push($student, round($totalStudentPaidFee/$totalStudentFee[0]['amount']*100,2));	
				}
				
				
				if($totalStudentPaidFee==0 || $studentFeeDecidedCurrentTime[0]['amount']==0)
				{
					array_push($student, 'N/A');
				}
				else
				{
					array_push($student, round($totalStudentPaidFee/$studentFeeDecidedCurrentTime[0]['amount']*100,2));
				}
				
				$renewInstallments = $this->council->renewInstallments($student['student_id']);
				array_push($student, count($renewInstallments));
				
				$course_name = $this->council->getCourseName($student['student_id']);
				array_push($student, $course_name[0]['course_name']);
				
				$studentData = $this->council->getStudentData($student['student_id']);
				array_push($student, $studentData[0]['caste']);
				
				array_push($student, $studentData[0]['qualification']);
				
				$campus = $this->council->getCampusName($student['student_id']);
				array_push($student, $campus[0]['campus_name']);
				
				array_push($student, $studentData[0]['date_of_birth']);
				
				array_push($student, $studentData[0]['email']);
				
				array_push($student, $studentData[0]['city']);
				
				if($studentData[0]['student_card']==1)
				{
					$student_card = 'Yes';
				}
				else
				{
					$student_card = 'No';
				}
				array_push($student, $student_card);
				
				array_push($student, $studentData[0]['gender']);
				
				array_push($student, $studentData[0]['religion']);
				
				$class = $this->council->getClassName($student['student_id']);
				array_push($student, $class[0]['name']);
				
				array_push($student, $studentData[0]['registration_date']);
				
				array_push($student, $studentData[0]['entry_date']);
				
				array_push($student, $studentData[0]['blood_group']);
				
				if($studentData[0]['books_1']==1)
				{
					$book_1 = '1st Year Book : Taken';
				}
				else
				{
					$book_1 = '1st Year Book : Not Taken';
				}
				if($studentData[0]['books_2']==1)
				{
					$book_2 = '2nd Year Book : Taken';
				}
				else
				{
					$book_2 = '2nd Year Book : Not Taken';
				}
				array_push($student, $book_1.' '.$book_2);
				
				array_push($student, $studentData[0]['emergency_no']);
				
				array_push($student, $studentData[0]['section']);
				
				array_push($student, $studentData[0]['study_type']);
				
				array_push($student, $studentData[0]['shift']);
				
				$pharmacy_data = $this->council->getStudentResultRemarksForExcelSheet($studentData[0]['cnic']);
				array_push($student, $pharmacy_data);
				
				$documents = $this->council->getStudentDocuments($student['student_id']);
				array_push($student, $documents);
				
				$machine_id = $this->council->getMachineID($student['student_id']);
				array_push($student, @$machine_id[0]['machine_id']);
				
				$row_tally++;
				fputcsv($fp, array_values($student));
			}
			
			fclose($fp);
			die;
		}
	}
	
	
	public function auto_create_council_fee()
	{
		$this->db->limit(1);
		$this->db->order_by('backup_date','ASC');
		$classes = $this->db->get('classes')->result_array();
		
		foreach($classes as $class)
		{
			$class_id = $class['class_id'];
			$result = $this->council->getClassStudent($class_id);
			
			$class_name = $this->db->get_where('classes',array('class_id'=>$class_id))->row()->name;

			$campus_name = $this->db->get_where('campuses',array('campus_id'=>$class['campus_id']))->row()->campus_name;

			$course_name = $this->db->get_where('courses',array('course_id'=>$class['course_id']))->row()->course_name;
			
			// Clear any previous output
			//ob_end_clean();
			// I assume you already have your $result
			$num_fields = count($result);
			//Headings
			
			$heading = array(
						'Student ID',
						'Campus Name',
						'Course Name',
						'Class Name',
						'Student Status',
						'Roll #',
						'CNIC No.',
						'Gender',
						'First Name',
						'Last Name',
						'Father Name',
						'Name & Father Name',
						'Postal Address',
						'Student Mobile Number',
						'Emergency Mobile Number',
						'Board Name',
						'Institute Contact Number',
						'Total Fee',
						'Fee Decided Current Time',
						'Total Fee Submitted',
						'Remaining Fee Payable At Current Time',
						'Unpaid Installments AT Cuurent Time',
						'Fee Detail Paid',
						'Fee Detail Unpaid',
						'Percentage Fee Receive',
						'Percentage Paid Installments According to Decision',
						'Renew Installments',
						'Cast',
						'Qualification',
						'Date of Birth',
						'Email',
						'City',
						'Student Card',
						'Religion',
						'Registration Date',
						'System Registration Date',
						'Blood Group',
						'Books',
						'Section',
						'Student Type',
						'Shift',
						'Pharmacy Coucil Data',
						'Document Links',
						'Machine ID'
						);
			// Fetch MySQL result headers
			$headers = array();
			//$headers[] = "[Row]";
			for ($i = 0; $i <= 43; $i++) {
				$headers[] = $heading[$i];
			}
			
			// Filename with current date
			$filename = $class_name.'('.date('Y-m-d').').csv';
			
			// Open php output stream and write headers
			$fp = fopen(FCPATH . 'councilbackup/'.$filename, 'w');
			//$fp = fopen('php://output', 'w');
			if ($fp && $result) 
			{
				echo "List of Students (Total Students ".count($result).") \n\n";
				// Write mysql headers to csv
				fputcsv($fp, $headers);
				//$row_tally = 1;
				// Write mysql rows to csv
				foreach($result as $student)
				{					
					$totalStudentFee = $this->council->getTotalFeeDetail($student['student_id']); 
					array_push($student, $totalStudentFee[0]['amount']);
					
					$studentFeeDecidedCurrentTime = $this->council->getFeeDecidedCurrentTime($student['student_id']);
					array_push($student, $studentFeeDecidedCurrentTime[0]['amount']);
					
					$totalStudentPaidFee = $this->council->getTotalPaidFeeDetail($student['student_id']); 
					array_push($student, $totalStudentPaidFee);
					
					array_push($student, $studentFeeDecidedCurrentTime[0]['amount']-$totalStudentPaidFee);
					
					$studentUnpaidFeeCurrentTime = $this->council->getUnpaidFeeDetailCurrentTime($student['student_id']);
					array_push($student, count($studentUnpaidFeeCurrentTime));
					
					$studentPaidFee = $this->council->getPaidFeeDetail($student['student_id']);
					$feeHTML = '';
					foreach($studentPaidFee as $feeStatus)
					{
						if($feeStatus['upload_scan_challan']==1)
						{
							$feeHTML.= 'Rs '.$feeStatus['actual_amount'].' paid on '.$feeStatus['actual_paid_date'].' Challan Link = '.$feeStatus['online_scan_challan'];
							$feeHTML.= ' | ';
						}
						else
						{
							$feeHTML.= 'Rs '.$feeStatus['actual_amount'].' paid on '.$feeStatus['actual_paid_date'].' Challan Link = '.base_url('uploads/'.$feeStatus['scan_challan']);
							$feeHTML.= ' | ';
						}
					}
					array_push($student, $feeHTML);
					
					$studentUnpaidFee = $this->council->getUnpaidFeeDetail($student['student_id']);
					$feeHTML = '';
					foreach($studentUnpaidFee as $feeStatus)
					{
							$feeHTML.= 'Fee '.$feeStatus['amount'].' not paid on '.$feeStatus['dead_line'];
							$feeHTML.= ' | ';
					}
					array_push($student, $feeHTML);
					
					if($totalStudentPaidFee==0 || $totalStudentFee[0]['amount']==0)
					{
						array_push($student, 'N/A');	
					}
					else
					{
						array_push($student, round($totalStudentPaidFee/$totalStudentFee[0]['amount']*100,2));	
					}
					
					
					if($totalStudentPaidFee==0 || $studentFeeDecidedCurrentTime[0]['amount']==0)
					{
						array_push($student, 'N/A');
					}
					else
					{
						array_push($student, round($totalStudentPaidFee/$studentFeeDecidedCurrentTime[0]['amount']*100,2));
					}
					
					$renewInstallments = $this->council->renewInstallments($student['student_id']);
					array_push($student, count($renewInstallments));
					
					// $course_name = $this->council->getCourseName($student['student_id']);
					// array_push($student, $course_name[0]['course_name']);
					
					$studentData = $this->council->getStudentData($student['student_id']);
					array_push($student, $studentData[0]['caste']);
					
					array_push($student, $studentData[0]['qualification']);
					
					// $campus = $this->council->getCampusName($student['student_id']);
					// array_push($student, $campus[0]['campus_name']);
					
					array_push($student, $studentData[0]['date_of_birth']);
					
					array_push($student, $studentData[0]['email']);
					
					array_push($student, $studentData[0]['city']);
					
					if($studentData[0]['student_card']==1)
					{
						$student_card = 'Yes';
					}
					else
					{
						$student_card = 'No';
					}
					array_push($student, $student_card);
					
					//array_push($student, $studentData[0]['gender']);
					
					array_push($student, $studentData[0]['religion']);
					
					// $class = $this->council->getClassName($student['student_id']);
					// array_push($student, $class[0]['name']);
					
					array_push($student, $studentData[0]['registration_date']);
					
					array_push($student, $studentData[0]['entry_date']);
					
					array_push($student, $studentData[0]['blood_group']);
					
					if($studentData[0]['books_1']==1)
					{
						$book_1 = '1st Year Book : Taken';
					}
					else
					{
						$book_1 = '1st Year Book : Not Taken';
					}
					if($studentData[0]['books_2']==1)
					{
						$book_2 = '2nd Year Book : Taken';
					}
					else
					{
						$book_2 = '2nd Year Book : Not Taken';
					}
					array_push($student, $book_1.' '.$book_2);
					
					//array_push($student, $studentData[0]['emergency_no']);
					
					array_push($student, $studentData[0]['section']);
					
					array_push($student, $studentData[0]['study_type']);
					
					array_push($student, $studentData[0]['shift']);
					
					$pharmacy_data = $this->council->getStudentResultRemarksForExcelSheet($studentData[0]['cnic']);
					array_push($student, $pharmacy_data);
					
					$documents = $this->council->getStudentDocuments($student['student_id']);
					array_push($student, $documents);
					
					$machine_id = $this->council->getMachineID($student['student_id']);
					array_push($student, @$machine_id[0]['machine_id']);
					
					fputcsv($fp, array_values($student));
					
				}
				
				fclose($fp);
				//die;

				$complete_name = $campus_name.' '.$course_name.' '.$class_name;
				
				$this->send_email($complete_name,$filename);
				sleep(5);
			}
			$this->db->set('backup_date',date('Y-m-d'));
			$this->db->set('backup_time',date('Y-m-d H:i:s'));
			$this->db->where('class_id',$class_id);
			$this->db->update('classes');
		}
	}
	
	public function send_email($class_name,$filename)
	{
		$backup_email = $this->db->get_where('backups',array('backup_id'=>1))->row()->email;

		$this->load->library('email');
		$this->email->set_newline("\r\n");
		$this->email->from('payments@shahbazcollegeofpharmacy.edu.pk');
		$this->email->to($backup_email);
		$this->email->subject('Backup '.$class_name);
		$this->email->message('');
		$this->email->attach(FCPATH.'councilbackup/'.$filename);
		$this->email->send();

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL,"https://www.shahbazcollegeofpharmacy.edu.pk/s3/upload_class_backup.php");
		curl_setopt($ch, CURLOPT_POST, 1);
		//curl_setopt($ch, CURLOPT_POSTFIELDS,"file_name=");

		// In real life you should use something like:
		curl_setopt($ch, CURLOPT_POSTFIELDS, 
		          http_build_query(array('file_name' => $filename)));

		// Receive server response ...
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$server_output = curl_exec($ch);

		curl_close($ch);
	}
	
	
	public function create_all($class_id)
	{
		$result = $this->council->getClassStudents($class_id);
		// Clear any previous output
		ob_end_clean();
		// I assume you already have your $result
		$num_fields = count($result);
		//Headings
		$heading = array(
					'Sr. #',
					'Student ID',
					'Roll #',
					'CNIC No.',
					'Name & Father Name',
					'Postal Address',
					'Student Mobile Number',
					'Board Name',
					'Institute Contact Number',
					);
		// Fetch MySQL result headers
		$headers = array();
		//$headers[] = "[Row]";
		for ($i = 0; $i <= 8; $i++) {
			$headers[] = $heading[$i];
		}
		
		// Filename with current date
		$filename = "Shahbaz-College-Council-List-of-Students.csv";
		
		// Open php output stream and write headers
		$fp = fopen('php://output', 'w');
		if ($fp && $result) {
			header('Content-Type: text/csv');
			header('Content-Disposition: attachment; filename='.$filename);
			header('Pragma: no-cache');
			header('Expires: 0');
			echo "List of Students \n\n";
			// Write mysql headers to csv
			fputcsv($fp, $headers);
			$row_tally = 0;
			// Write mysql rows to csv
			foreach($result as $student)
			{
				$row_tally++;
				echo $row_tally.",";
				fputcsv($fp, array_values($student));
			}
			die;
		}
	}
	
	
}
