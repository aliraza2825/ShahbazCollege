<?php
class Council extends CI_Model {
	
	public function getClasses()
	{
		$access = checkUserAccess();
		$class_ids = @explode(',',$access[0]['class_ids']);
		
		$this->db->select('*');
		$this->db->from('classes');
		$this->db->where(array('status'=>'1'));
		if($this->session->userdata('role')!='Admin'){
			$this->db->where_in('class_id', $class_ids);
		}
		$this->db->order_by('class_id', 'asc');
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getClassStudents($class_id)
	{
		$qry = 'SELECT student_id ,roll_no, cnic,gender, first_name, last_name, father_name, CONCAT(first_name," ", last_name, " S/O ", father_name) as name, address, mobile,emergency_no, board, 03158042977 as institute,gender FROM students WHERE class_id="'.$class_id.'" AND status=1 ORDER BY CAST(roll_no as SIGNED INTEGER) ASC';
		$query = $this->db->query($qry)->result_array();
		return $query;
	}

	public function getCouncilFeeStudents($class_id = '', $course_id = '', $campus_id = '', $include_inactive = true)
	{
		$this->db->select('students.student_id, students.roll_no, students.cnic, CONCAT(students.first_name," ", students.last_name, " S/O ", students.father_name) as name, students.address, students.mobile, students.board, 03158042977 as institute', false);
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'left');

		if ($class_id !== '') {
			$this->db->where('students.class_id', $class_id);
		}

		if ($course_id !== '') {
			$this->db->where('students.course_id', $course_id);
		}

		if ($campus_id !== '') {
			$this->db->where('classes.campus_id', $campus_id);
		}

		if (!$include_inactive) {
			$this->db->where('students.status', 1);
		}

		$this->db->order_by('CAST(students.roll_no as SIGNED INTEGER)', 'ASC', false);
		$this->db->order_by('students.student_id', 'ASC');

		return $this->db->get()->result_array();
	}

	public function countCouncilFeeStudents($class_id = '', $course_id = '', $campus_id = '', $include_inactive = true)
	{
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'left');

		if ($class_id !== '') {
			$this->db->where('students.class_id', $class_id);
		}

		if ($course_id !== '') {
			$this->db->where('students.course_id', $course_id);
		}

		if ($campus_id !== '') {
			$this->db->where('classes.campus_id', $campus_id);
		}

		if (!$include_inactive) {
			$this->db->where('students.status', 1);
		}

		return (int) $this->db->count_all_results();
	}

	public function getCouncilFeeStudentsChunk($class_id = '', $course_id = '', $campus_id = '', $limit = 100, $offset = 0, $include_inactive = true)
	{
		$this->db->select('students.student_id, students.roll_no, students.cnic, CONCAT(students.first_name," ", students.last_name, " S/O ", students.father_name) as name, students.address, students.mobile, students.board, 03158042977 as institute', false);
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'left');

		if ($class_id !== '') {
			$this->db->where('students.class_id', $class_id);
		}

		if ($course_id !== '') {
			$this->db->where('students.course_id', $course_id);
		}

		if ($campus_id !== '') {
			$this->db->where('classes.campus_id', $campus_id);
		}

		if (!$include_inactive) {
			$this->db->where('students.status', 1);
		}

		$this->db->order_by('CAST(students.roll_no as SIGNED INTEGER)', 'ASC', false);
		$this->db->order_by('students.student_id', 'ASC');
		$this->db->limit((int) $limit, (int) $offset);

		return $this->db->get()->result_array();
	}

	public function getClassStudent($class_id)
	{
		$this->db->select('students.student_id,campuses.campus_name,courses.course_name,classes.name,students.status,students.roll_no,students.cnic,students.gender,students.first_name,students.last_name,students.father_name,CONCAT(students.first_name," ", students.last_name, " S/O ", students.father_name),students.address,students.mobile,students.emergency_no,students.board,03158042977 as institute');
		$this->db->from('students');
		$this->db->join('classes','classes.class_id=students.class_id','LEFT');
		$this->db->join('campuses','classes.campus_id=campuses.campus_id','LEFT');
		$this->db->join('courses','courses.course_id=students.course_id','LEFT');
		$this->db->where(array('students.class_id'=>$class_id));
		$query = $this->db->get()->result_array();

		return $query;
	}

    public function getClassStudentsDetails($class_id)
    {
        $qry = 'SELECT * FROM students WHERE class_id="'.$class_id.'" AND status=1 ORDER BY CAST(roll_no as SIGNED INTEGER) ASC';
        $query = $this->db->query($qry)->result_array();
        return $query;
    }

    public function getStudents($class_id)
	{
		$query = $this->db->get_where('students', array('class_id'=>$class_id, 'status'=>'1'))->result_array();
		return $query;
	}
	
	public function getRollnoAddedStudents($class_id)
	{
		$this->db->select('*');
		$this->db->from('students');
		$this->db->join('punjab_council_roll_number', 'students.student_id=punjab_council_roll_number.student_id', 'inner');
		$this->db->where('students.class_id', $class_id);
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function checkEntry($student_id, $computer_number, $roll_number, $year)
	{
		$query = $this->db->get_where('punjab_council_roll_number', array('student_id'=>$student_id, 'year'=>$year ))->result_array();
		return $query;
	}
	
	public function updateEntry($student_id, $computer_number, $roll_number, $year, $remarks)
	{
		$this->db->set('student_id', $student_id);
		$this->db->set('year', $year);
		$this->db->set('computer_no', $computer_number);
		$this->db->set('roll_no', $roll_number);
		$this->db->set('remarks', $remarks);
		$this->db->where('student_id', $student_id);
		$this->db->where('year', $year);
		$this->db->update('punjab_council_roll_number');
	}
	
	public function insertEntry($student_id, $computer_number, $roll_number, $year)
	{
		$this->db->set('student_id', $student_id);
		$this->db->set('year', $year);
		$this->db->set('computer_no', $computer_number);
		$this->db->set('roll_no', $roll_number);
		$this->db->insert('punjab_council_roll_number');
	}
	
	public function getPaidFeeDetail($student_id)
	{
		
		$this->db->select('*');
		$this->db->from('payments');
		$this->db->where('student_id', $student_id);
		$this->db->where('merged_challan IS NOT NULL and actual_amount > 0 and paid = "1"');
		$this->db->group_by("CASE WHEN merged_challan IS NOT NULL THEN merged_challan else '' end",false);
		$this->db->order_by('dead_line','ASC');
		$query = $this->db->get()->result_array();

        $this->db->select('*');
        $this->db->from('payments');
        $this->db->where('student_id', $student_id);
        $this->db->where('merged_challan is null and paid = "1"');
		$this->db->order_by('dead_line','ASC');
        $query2 = $this->db->get()->result_array();
		
		$arr=array_merge($query,$query2);
		
		
	
		return $arr;
	}
	
	public function getUnpaidFeeDetail($student_id)
	{
		$this->db->select('*');
		$this->db->from('payments');
		$this->db->order_by('dead_line', 'ASC');
		$this->db->where(array('student_id'=>$student_id,'paid'=>0));
		$this->db->order_by('dead_line','ASC');
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getUnpaidFeeDetailCurrentTime($student_id)
	{
		$this->db->select('*');
		$this->db->from('payments');
		$this->db->order_by('dead_line', 'ASC');
		$this->db->where(array('student_id'=>$student_id,'paid'=>0,'dead_line<'=>date('Y-m-d')));
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getFeeDecidedCurrentTime($student_id)
	{
		$this->db->select_sum('amount');
		$this->db->from('payments');
		$this->db->where(array('student_id'=>$student_id,'dead_line<'=>date('Y-m-d')));
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getTotalFeeDetail($student_id)
	{
		$this->db->select_sum('amount');
		$this->db->from('payments');
		$this->db->where('student_id', $student_id);
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getTotalPaidFeeDetail($student_id)
	{
		$this->db->select('*');
		$this->db->from('payments');
		$this->db->where('student_id', $student_id);
		$this->db->where('merged_challan IS NOT NULL and actual_amount > 0 and paid = "1"');
		$this->db->group_by("CASE WHEN merged_challan IS NOT NULL THEN merged_challan else '' end",false);
		$query = $this->db->get()->result_array();

        $this->db->select('*');
        $this->db->from('payments');
        $this->db->where('student_id', $student_id);
        $this->db->where('merged_challan is null');
		$this->db->or_where(' student_id = "'.$student_id.'" and merged_challan IS not NULL and actual_amount = 0 and paid = "0"');
        $query2 = $this->db->get()->result_array();
		
		$arr=array_merge($query,$query2);
	
		$totalpaid=0;
		
		foreach($arr as $a)
		{
			$totalpaid+=$a['actual_amount'];
			
			
		}
		return $totalpaid;
	}
	
	public function renewInstallments($student_id)
	{
		$payments = $this->db->get_where('payments', array('student_id'=>$student_id))->result_array();
		$student_payments = array();
		foreach($payments as $payment)
		{
			array_push($student_payments, $payment['id']);
		}
		if(count($student_payments)>0)
		{
			$this->db->select('*');
			$this->db->from('fees_remarks');
			$this->db->where_in('fee_id',$student_payments);
			$query = $this->db->get()->result_array();
			return $query;
		}
		else
		{
			$query=array();
			return $query;
		}
	}
	
	public function getCourseName($student_id)
	{
		$this->db->select('courses.course_name');
		$this->db->from('students');
		$this->db->join('courses','courses.course_id=students.course_id','INNER');
		$this->db->where('students.student_id',$student_id);
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getStudentData($student_id)
	{
		$query = $this->db->get_where('students',array('student_id'=>$student_id))->result_array();
		return $query;
	}
	
	public function getMachineID($student_id)
	{
		$this->db->select('machine_data.*');
		$this->db->from('students');
		$this->db->join('machine_data','machine_data.teacher_student_id=students.student_id','INNER');
		$this->db->where(array('students.student_id'=>$student_id,'machine_data.type'=>'student'));
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getStudentResultRemarksForExcelSheet($cnic)
	{
		$this->db->select('*');
		$this->db->from('punjab_council_roll_number');
		$this->db->where('cnic', $cnic);
		$results = $this->db->get()->result_array();
		
		$html = '';
		foreach($results as $result)
		{
			if($result['class']==1)
			{
				$class = '1st Year';
			}
			else
			{
				$class = '2nd Year';
			}
			
			$html.= 'Class : '. $class;
			$html.= ' | ';
			$html.= 'Exam Number : '. $result['council_exam_no'];
			$html.= ' | ';
			$html.= 'Roll Number : '.$result['roll_no'];
			$html.= ' | ';
			$html.= 'Roll Number Upload Date : '.date('Y-m-d',strtotime($result['date']));
			$html.= ' | ';
			if($result['result_remarks']!='')
			{
				$html.= 'Result Upload Date : '.date('Y-m-d',strtotime($result['result_update_date']));
				$html.= ' | ';
				$html.= 'Result Remarks : '.$result['result_remarks'];
			}
			
			$html.= ' --- ';
		}
		return $html;
	}
	
	public function getCampusName($student_id)
	{
		$this->db->select('campuses.*');
		$this->db->from('students');
		$this->db->join('classes','classes.class_id=students.class_id','INNER');
		$this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
		$this->db->where('students.student_id',$student_id);
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getClassName($student_id)
	{
		$this->db->select('classes.*');
		$this->db->from('students');
		$this->db->join('classes','classes.class_id=students.class_id','INNER');
		$this->db->where('students.student_id',$student_id);
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getStudentDocuments($student_id)
	{
		$documents = $this->db->get_where('student_documents',array('student_id'=>$student_id))->result_array();
		
		$html = '';
		foreach($documents as $document)
		{
			if($document['upload_image']==1)
			{
				$html.= $document['type'].' = '.$document['online_image'];
				$html.= ' | ';
			}
			else
			{
				$html.= $document['type'].' = '.base_url().'uploads/'.$document['image'];
				$html.= ' | ';
			}
		}
		return $html;
	}
}
?>