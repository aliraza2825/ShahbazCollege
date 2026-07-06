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

	private function council_fee_export_headers()
	{
		return array(
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
	}

	private function sanitize_export_label($text, $default)
	{
		$text = trim((string) $text);
		if ($text === '') {
			return $default;
		}

		$text = preg_replace('/[^A-Za-z0-9\-_]+/', '-', $text);
		$text = trim($text, '-');
		return $text !== '' ? $text : $default;
	}

	private function resolve_council_fee_export_labels($class_id, $course_id, $campus_id)
	{
		$class_name = 'All-Classes';
		$course_name = 'All-Courses';
		$campus_name = 'All-Campuses';

		if ($class_id !== '') {
			$classRow = $this->db->get_where('classes', array('class_id' => $class_id))->row_array();
			$class_name = $this->sanitize_export_label(isset($classRow['name']) ? $classRow['name'] : '', 'All-Classes');
		}

		if ($course_id !== '') {
			$courseRow = $this->db->get_where('courses', array('course_id' => $course_id))->row_array();
			$course_name = $this->sanitize_export_label(isset($courseRow['course_name']) ? $courseRow['course_name'] : '', 'All-Courses');
		}

		if ($campus_id !== '') {
			$campusRow = $this->db->get_where('campuses', array('campus_id' => $campus_id))->row_array();
			$campus_name = $this->sanitize_export_label(isset($campusRow['campus_name']) ? $campusRow['campus_name'] : '', 'All-Campuses');
		}

		return array(
			'class_name' => $class_name,
			'course_name' => $course_name,
			'campus_name' => $campus_name
		);
	}

	private function council_fee_export_directory()
	{
		return FCPATH . 'downloads/council_exports';
	}

	private function council_fee_export_state_path($token)
	{
		return $this->council_fee_export_directory() . '/' . $token . '.json';
	}

	private function read_council_fee_export_state($token)
	{
		$statePath = $this->council_fee_export_state_path($token);
		if (!is_file($statePath)) {
			return null;
		}

		$state = json_decode((string) @file_get_contents($statePath), true);
		if (!is_array($state)) {
			return null;
		}

		return $state;
	}

	private function write_council_fee_export_state($token, $state)
	{
		$statePath = $this->council_fee_export_state_path($token);
		@file_put_contents($statePath, json_encode($state));
	}

	private function council_fee_export_row($student)
	{
		$totalStudentFee = $this->council->getTotalFeeDetail($student['student_id']);
		$totalFeeAmount = isset($totalStudentFee[0]['amount']) ? (float) $totalStudentFee[0]['amount'] : 0;
		array_push($student, $totalFeeAmount);

		$studentFeeDecidedCurrentTime = $this->council->getFeeDecidedCurrentTime($student['student_id']);
		$feeDecidedAmount = isset($studentFeeDecidedCurrentTime[0]['amount']) ? (float) $studentFeeDecidedCurrentTime[0]['amount'] : 0;
		array_push($student, $feeDecidedAmount);

		$totalStudentPaidFee = (float) $this->council->getTotalPaidFeeDetail($student['student_id']);
		array_push($student, $totalStudentPaidFee);
		array_push($student, $feeDecidedAmount - $totalStudentPaidFee);

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

		if($totalStudentPaidFee==0 || $totalFeeAmount==0)
		{
			array_push($student, 'N/A');
		}
		else
		{
			array_push($student, round($totalStudentPaidFee/$totalFeeAmount*100,2));
		}

		if($totalStudentPaidFee==0 || $feeDecidedAmount==0)
		{
			array_push($student, 'N/A');
		}
		else
		{
			array_push($student, round($totalStudentPaidFee/$feeDecidedAmount*100,2));
		}

		$renewInstallments = $this->council->renewInstallments($student['student_id']);
		array_push($student, count($renewInstallments));

		$course_name = $this->council->getCourseName($student['student_id']);
		array_push($student, isset($course_name[0]['course_name']) ? $course_name[0]['course_name'] : '');

		$studentData = $this->council->getStudentData($student['student_id']);
		$studentDataRow = isset($studentData[0]) ? $studentData[0] : array();
		array_push($student, isset($studentDataRow['caste']) ? $studentDataRow['caste'] : '');
		array_push($student, isset($studentDataRow['qualification']) ? $studentDataRow['qualification'] : '');

		$campus = $this->council->getCampusName($student['student_id']);
		array_push($student, isset($campus[0]['campus_name']) ? $campus[0]['campus_name'] : '');

		array_push($student, isset($studentDataRow['date_of_birth']) ? $studentDataRow['date_of_birth'] : '');
		array_push($student, isset($studentDataRow['email']) ? $studentDataRow['email'] : '');
		array_push($student, isset($studentDataRow['city']) ? $studentDataRow['city'] : '');

		$student_card = (isset($studentDataRow['student_card']) && $studentDataRow['student_card']==1) ? 'Yes' : 'No';
		array_push($student, $student_card);

		array_push($student, isset($studentDataRow['gender']) ? $studentDataRow['gender'] : '');
		array_push($student, isset($studentDataRow['religion']) ? $studentDataRow['religion'] : '');

		$class = $this->council->getClassName($student['student_id']);
		array_push($student, isset($class[0]['name']) ? $class[0]['name'] : '');

		array_push($student, isset($studentDataRow['registration_date']) ? $studentDataRow['registration_date'] : '');
		array_push($student, isset($studentDataRow['entry_date']) ? $studentDataRow['entry_date'] : '');
		array_push($student, isset($studentDataRow['blood_group']) ? $studentDataRow['blood_group'] : '');

		$book_1 = (isset($studentDataRow['books_1']) && $studentDataRow['books_1']==1) ? '1st Year Book : Taken' : '1st Year Book : Not Taken';
		$book_2 = (isset($studentDataRow['books_2']) && $studentDataRow['books_2']==1) ? '2nd Year Book : Taken' : '2nd Year Book : Not Taken';
		array_push($student, $book_1.' '.$book_2);

		array_push($student, isset($studentDataRow['emergency_no']) ? $studentDataRow['emergency_no'] : '');
		array_push($student, isset($studentDataRow['section']) ? $studentDataRow['section'] : '');
		array_push($student, isset($studentDataRow['study_type']) ? $studentDataRow['study_type'] : '');
		array_push($student, isset($studentDataRow['shift']) ? $studentDataRow['shift'] : '');

		$pharmacy_data = $this->council->getStudentResultRemarksForExcelSheet(isset($studentDataRow['cnic']) ? $studentDataRow['cnic'] : '');
		array_push($student, $pharmacy_data);

		$documents = $this->council->getStudentDocuments($student['student_id']);
		array_push($student, $documents);

		$machine_id = $this->council->getMachineID($student['student_id']);
		array_push($student, isset($machine_id[0]['machine_id']) ? $machine_id[0]['machine_id'] : '');

		return array_values($student);
	}

	public function start_council_fee_export()
	{
		$class_id = trim((string) $this->input->post('class_id'));
		$course_id = trim((string) $this->input->post('course_id'));
		$campus_id = trim((string) $this->input->post('campus_id'));

		$total = $this->council->countCouncilFeeStudents($class_id, $course_id, $campus_id, true);
		if ($total <= 0) {
			return $this->output
				->set_content_type('application/json')
				->set_output(json_encode(array(
					'success' => false,
					'message' => 'No students found for selected filters.'
				)));
		}

		$labels = $this->resolve_council_fee_export_labels($class_id, $course_id, $campus_id);
		if (function_exists('random_bytes')) {
			$token = bin2hex(random_bytes(16));
		} else {
			$token = md5(uniqid((string) mt_rand(), true));
		}
		$filename = $labels['campus_name'].'_'.$labels['course_name'].'_'.$labels['class_name'].'_'.date('Y-m-d_H-i-s').'.csv';

		$directory = $this->council_fee_export_directory();
		if (!is_dir($directory)) {
			@mkdir($directory, 0775, true);
		}

		$csvPath = $directory . '/' . $token . '.csv';
		$fp = @fopen($csvPath, 'w');
		if (!$fp) {
			return $this->output
				->set_content_type('application/json')
				->set_output(json_encode(array(
					'success' => false,
					'message' => 'Could not create export file on server.'
				)));
		}

		fputcsv($fp, $this->council_fee_export_headers());
		fclose($fp);

		$state = array(
			'token' => $token,
			'status' => 'processing',
			'class_id' => $class_id,
			'course_id' => $course_id,
			'campus_id' => $campus_id,
			'total' => $total,
			'processed' => 0,
			'chunk_size' => 20,
			'file_path' => $csvPath,
			'download_name' => $filename,
			'created_at' => date('Y-m-d H:i:s')
		);
		$this->write_council_fee_export_state($token, $state);

		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode(array(
				'success' => true,
				'token' => $token,
				'total' => $total,
				'processed' => 0,
				'download_url' => site_url('council_list/download_council_fee_export/' . $token)
			)));
	}

	public function process_council_fee_export()
	{
		$token = trim((string) $this->input->post('token'));
		if ($token === '' || !preg_match('/^[a-f0-9]{32}$/', $token)) {
			return $this->output
				->set_content_type('application/json')
				->set_output(json_encode(array(
					'success' => false,
					'message' => 'Invalid export token.'
				)));
		}

		$state = $this->read_council_fee_export_state($token);
		if (!$state) {
			return $this->output
				->set_content_type('application/json')
				->set_output(json_encode(array(
					'success' => false,
					'message' => 'Export session not found.'
				)));
		}

		if ($state['status'] === 'completed') {
			return $this->output
				->set_content_type('application/json')
				->set_output(json_encode(array(
					'success' => true,
					'completed' => true,
					'processed' => (int) $state['processed'],
					'total' => (int) $state['total'],
					'download_url' => site_url('council_list/download_council_fee_export/' . $token)
				)));
		}

		$offset = (int) $state['processed'];
		$limit = isset($state['chunk_size']) ? (int) $state['chunk_size'] : 20;
		if ($limit <= 0) {
			$limit = 20;
		}

		$students = $this->council->getCouncilFeeStudentsChunk(
			$state['class_id'],
			$state['course_id'],
			$state['campus_id'],
			$limit,
			$offset,
			true
		);

		$fp = @fopen($state['file_path'], 'a');
		if (!$fp) {
			return $this->output
				->set_content_type('application/json')
				->set_output(json_encode(array(
					'success' => false,
					'message' => 'Could not write export file.'
				)));
		}

		foreach ($students as $student) {
			fputcsv($fp, $this->council_fee_export_row($student));
		}
		fclose($fp);

		if (empty($students)) {
			$state['processed'] = (int) $state['total'];
			$state['status'] = 'completed';
			$state['completed_at'] = date('Y-m-d H:i:s');
			$this->write_council_fee_export_state($token, $state);

			return $this->output
				->set_content_type('application/json')
				->set_output(json_encode(array(
					'success' => true,
					'completed' => true,
					'processed' => (int) $state['processed'],
					'total' => (int) $state['total'],
					'download_url' => site_url('council_list/download_council_fee_export/' . $token)
				)));
		}

		$state['processed'] = $offset + count($students);
		if ($state['processed'] >= (int) $state['total']) {
			$state['processed'] = (int) $state['total'];
			$state['status'] = 'completed';
			$state['completed_at'] = date('Y-m-d H:i:s');
		}
		$this->write_council_fee_export_state($token, $state);

		$completed = ($state['status'] === 'completed');
		return $this->output
			->set_content_type('application/json')
			->set_output(json_encode(array(
				'success' => true,
				'completed' => $completed,
				'processed' => (int) $state['processed'],
				'total' => (int) $state['total'],
				'download_url' => site_url('council_list/download_council_fee_export/' . $token)
			)));
	}

	public function download_council_fee_export($token = '')
	{
		$token = trim((string) $token);
		if ($token === '' || !preg_match('/^[a-f0-9]{32}$/', $token)) {
			show_error('Invalid export token.', 400);
			return;
		}

		$state = $this->read_council_fee_export_state($token);
		if (!$state || !isset($state['status']) || $state['status'] !== 'completed') {
			show_error('Export is not ready yet.', 404);
			return;
		}

		$filePath = isset($state['file_path']) ? $state['file_path'] : '';
		if ($filePath === '' || !is_file($filePath)) {
			show_error('Export file not found.', 404);
			return;
		}

		$downloadName = isset($state['download_name']) && $state['download_name'] !== '' ? $state['download_name'] : 'council-fee-export.csv';

		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="'.$downloadName.'"');
		header('Content-Length: ' . filesize($filePath));
		header('Pragma: no-cache');
		header('Expires: 0');
		readfile($filePath);
		exit;
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
		@ini_set('max_execution_time', '0');
		@set_time_limit(0);
		@ini_set('memory_limit', '1024M');
		@ini_set('zlib.output_compression', '0');
		@ini_set('output_buffering', 'off');
		@ignore_user_abort(true);
		if (function_exists('apache_setenv')) {
			@apache_setenv('no-gzip', '1');
		}

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
			header('X-Accel-Buffering: no');
			// Write mysql headers to csv
			fputcsv($fp, $headers);
			if (function_exists('ob_flush')) {
				@ob_flush();
			}
			@flush();

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
				if (($row_tally % 25) === 0) {
					@fflush($fp);
					if (function_exists('ob_flush')) {
						@ob_flush();
					}
					@flush();
				}
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
