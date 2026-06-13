<?php
class Attendence extends CI_Model {
	
	public function storeAttendenceData($data)
	{
		foreach(@$data as $k=>$value){
			$this->db->set(''.$k.'', $value);
		}
		$this->db->insert('machine_data');
	}
	
	/*public function updateAttendenceData($data)
	{
		foreach(@$data as $k=>$value){
			$this->db->set(''.$k.'', $value);
		}
		$this->db->where('id', $this->uri->segment(3));
		$this->db->update('machine_data');
	}*/
	
	public function checkAttendenceData($data)
	{
		$query = $this->db->get_where('machine_data', array('teacher_student_id'=>$data['teacher_student_id'], 'type'=>$data['type']))->result_array();
		return $query;
	}
	
	public function updateAttendenceData($data)
	{
		foreach(@$data as $k=>$value){
			$this->db->set(''.$k.'', $value);
		}
		$this->db->where('id', $this->uri->segment(3));
		$this->db->update('machine_data');
		
	}
	
	public function getUsers($type)
	{
		$this->db->select('machine_data.id, machine_data.machine_id, machine_data.type, users.first_name, users.last_name, users.cnic');
		$this->db->from('machine_data');
		$this->db->join('users', 'users.user_id=machine_data.teacher_student_id', 'inner');
		$this->db->where('machine_data.type', $type);
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getStudents()
	{
		$this->db->select('machine_data.id, machine_data.machine_id, machine_data.type, students.first_name, students.last_name, students.cnic, students.roll_no');
		$this->db->from('machine_data');
		$this->db->join('students', 'students.student_id=machine_data.teacher_student_id', 'inner');
		$this->db->where('type', 'student');
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getSingleUser($id)
	{
		$query = $this->db->get_where('machine_data', array('id'=>$id))->result_array();
		return $query;
	}
			
}
?>