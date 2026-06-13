<?php
class Suply extends CI_Model {
	
	public function getAllStudents()
	{
		$query = $this->db->get_where('students', array('status'=>1))->result_array();
		return $query;
	}
	
	public function addSupplyStudentTimes($student_id)
	{
		$student = $this->db->get_where('students', array('student_id'=>$student_id))->result_array();
		$times = $student[0]['supply']+1;
		$this->updateSupply($student_id,$times);
	}
	
	public function updateSupply($student_id,$times)
	{
		$this->db->set('supply', $times);
		$this->db->where('student_id', $student_id);
		$this->db->update('students');
	}
	
	public function checkStudentStatus($student_id)
	{
		$query = $this->db->get_where('students', array('student_id'=>$student_id))->result_array();
		return $query;
	}
			
}
?>