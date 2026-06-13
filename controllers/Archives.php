<?php
class Archives extends CI_Model 
{
	public function teachers()
	{
		$this->db->select('*');
		$this->db->from('users');
		$this->db->where(array('status'=>'0'));
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getTeachersCount()
	{
		$this->db->select('*');
		$this->db->from('users');
		$this->db->where(array('status'=>'0', 'role'=>'Teacher'));
		$query = $this->db->count_all_results();
		return $query;
	}
	
	public function restoreTeacher($id)
	{
		$this->db->set('status', '1');
		$this->db->where('user_id', $id);
		$this->db->update('users');
	}
	
	public function deleteTeacher($id)
	{
		$this->db->where('user_id', $id);
		$this->db->delete('users');
	}
	
	public function classes()
	{
		$this->db->select('*');
		$this->db->from('classes');
		$this->db->where(array('status'=>'0'));
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getClassesCount()
	{
		$this->db->select('*');
		$this->db->from('classes');
		$this->db->where(array('status'=>'0'));
		$query = $this->db->count_all_results();
		return $query;
	}
	
	public function restoreClass($id)
	{
		$this->db->set('status', '1');
		$this->db->where('class_id', $id);
		$this->db->update('classes');
	}
	
	public function deleteClass($id)
	{
		$this->db->where('class_id', $id);
		$this->db->delete('classes');
	}
	
	public function subjects()
	{
		$this->db->select('*');
		$this->db->from('course_subjects');
		$this->db->where(array('status'=>'0'));
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getSubjectsCount()
	{
		$this->db->select('*');
		$this->db->from('course_subjects');
		$this->db->where(array('status'=>'0'));
		$query = $this->db->count_all_results();
		return $query;
	}
	
	public function restoreSubject($id)
	{
		$this->db->set('status', '1');
		$this->db->where('course_subject_id', $id);
		$this->db->update('course_subjects');
	}
	
	public function deleteSubject($id)
	{
		$this->db->where('course_subject_id', $id);
		$this->db->delete('course_subjects');
	}
	
	public function students()
	{
		$this->db->select('students.*, classes.name as class_name, courses.course_name,campuses.campus_name');
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'left');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
		$this->db->join('courses', 'courses.course_id=students.course_id', 'left');
		$this->db->where(array('students.status'=>'0'));
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getStudentsCount()
	{
		$this->db->select('*');
		$this->db->from('students');
		$this->db->where(array('status'=>'0'));
		$query = $this->db->count_all_results();
		return $query;
	}
	
	public function restoreStudent($id)
	{
		$student=$this->db->get_where('students', array('student_id'=>$id))->result_array();
		
		$data = array(
				'course_id'			=> $student[0]['course_id'],
				'first_name'		=> $student[0]['first_name'],
				'last_name'			=> $student[0]['last_name'],
				'father_name'		=> $student[0]['father_name'],
				'gender'			=> $student[0]['gender'],
				'class_id'			=> $student[0]['class_id'],
				'roll_no'			=> $student[0]['roll_no'],
				'email'				=> $student[0]['email'],
				'cnic'				=> $student[0]['cnic'],
				'date_of_birth'		=> $student[0]['date_of_birth'],
				'registration_date'	=> $student[0]['registration_date'],
				'total_fee'			=> $student[0]['total_fee'],
				'blood_group'		=> $student[0]['blood_group'],
				'city'				=> $student[0]['city'],
				'address'			=> $student[0]['address'],
				'mobile'			=> $student[0]['mobile'],
				'emergency_no'		=> $student[0]['emergency_no'],
				'status' 			=> $student[0]['status'],
				'contractor_id'		=> $student[0]['contractor_id'],
				'board'				=> $student[0]['board'],
				'section'			=> $student[0]['section'],
				'shift'				=> $student[0]['shift'],
				'study_type'		=> $student[0]['study_type'],
				'books_1'			=> $student[0]['books_1'],
				'books_2'			=> $student[0]['books_2'],
				'student_card'		=> $student[0]['student_card'],
				'password'			=> $student[0]['password'],
				'notes'				=> $student[0]['notes'],
				'last_edit'			=> $student[0]['last_edit'],
				'status'			=> 1
				);
		$this->student->updateDeleteStudent($data, $student_id);
	}
	
	public function deleteStudent($id)
	{
		$this->db->where('student_id', $id);
		$this->db->delete('students');
	}
			
}
?>
