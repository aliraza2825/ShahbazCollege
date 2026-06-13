<?php
class Fee extends CI_Model {
	
	public function getDueFees($class_id)
	{
		$this->db->select('payments.id as fee_id,payments.amount, payments.dead_line, payments.extra_amount,students.student_id, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name, courses.course_name');
		$this->db->from('payments');
		$this->db->join('students', 'payments.student_id=students.student_id', 'inner');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
		$this->db->join('campuses', 'campuses.campus_id=classes.campus_id', 'inner');
		$this->db->join('courses', 'courses.course_id=students.course_id', 'inner');
		$this->db->where(array('students.status'=> 1, 'classes.class_id'=>$class_id, 'payments.paid'=>'0', 'payments.dead_line<='=>date('Y-m-d', strtotime("+1 week"))));
		$this->db->order_by('students.roll_no', 'asc');
		$query = $this->db->get()->result_array();
		return $query;
	}
	
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
}
?>