<?php
class Performance extends CI_Model {
	
	public function getStudentId($roll_no)
	{
		$this->db->select('*');
		$this->db->from('students');
		$this->db->where(array('roll_no'=>$roll_no));
		$query = $this->db->get()->result_array();
		$student_id = $query[0]['student_id'];
		return $student_id;
	}
	
	public function getReport($student_id)
	{
		$this->db->select(
							'students.first_name as first_name,
							students.last_name as last_name,
							subjects.name as subject,
							papers.date as date,
							papers.content as content,
							results.marks as marks,
							results.total_marks as total_marks,'
							);
		$this->db->from('results');
		$this->db->join('students', 'students.student_id=results.student_id', 'inner');
		$this->db->join('papers', 'papers.paper_id=results.paper_id', 'inner');
		$this->db->join('subjects', 'subjects.subject_id=papers.subject_id', 'inner');
		$this->db->where('results.student_id', $student_id);
		$this->db->order_by('papers.date', 'DESC');
		$query = $this->db->get()->result_array();
		return $query;
	}
}
?>