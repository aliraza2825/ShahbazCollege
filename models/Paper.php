<?php
class Paper extends CI_Model {
	
	public function storePaper($data)
	{
		foreach(@$data as $k=>$value){
			$this->db->set(''.$k.'', $value);
		}
		$this->db->insert('papers');
		
	}
	
	public function deletePaper($id)
	{
		$this->db->where('paper_id', $id);
		$this->db->delete('papers');
	}
	
	public function updatePaper($data)
	{
		foreach(@$data as $k=>$value){
			if($value!='')
			{
				$this->db->set(''.$k.'', $value);
			}
		}
		$this->db->where('paper_id', $this->uri->segment(3));
		$this->db->update('papers');
		
	}
	
	public function getTeacherPapers()
	{
		$this->db->select('papers.*, subjects.name as subject_name');
		$this->db->from('papers');
		$this->db->join('subjects', 'papers.subject_id=subjects.subject_id', 'inner');
		$this->db->order_by('paper_id', 'DESC');
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getPaper($id)
	{
		$this->db->select('*');
		$this->db->from('papers');
		$this->db->where('paper_id', $id);
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getTeacherSubjects($teacher_id)
	{
		$query = $this->db->get_where('subjects', array('teacher_id'=>$teacher_id))->result_array();
		return $query;
	}
	
	public function getStudents($paper_id)
	{
		$subject = $this->db->get_where('papers', array('paper_id'=>$paper_id))->result_array();
		$subject_id = $subject[0]['subject_id'];
		
		$class = $this->db->get_where('subjects', array('subject_id'=>$subject_id))->result_array();
		$class_id = $class[0]['class_id'];
		
		$students = $this->db->get_where('students', array('class_id'=>$class_id, 'contractor_id'=>0, 'status'=>1))->result_array();
		
		return $students;
	}
	
	public function addResult($data)
	{
		$count = count($data['student_id']);
		//echo $count;
		
		for($i=0; $i<$count; $i++)
		{
			if($data['marks'][$i]!='')
			{
				$marks = $data['marks'][$i];
			}
			else
			{
				$marks = 'Absent';
			}
			$this->db->set('student_id', $data['student_id'][$i]);
			$this->db->set('marks', $marks);
			$this->db->set('total_marks', $data['total_marks']);
			$this->db->set('paper_id', $this->uri->segment(3));
			$this->db->insert('results');
		}
	}
	
	public function updateResult($data)
	{
		$count = count($data['student_id']);
		
		for($i=0; $i<$count; $i++)
		{
			if($data['marks'][$i]!='')
			{
				$marks = $data['marks'][$i];
			}
			else
			{
				$marks = 'Absent';
			}
			$this->db->set('student_id', $data['student_id'][$i]);
			$this->db->set('marks', $marks);
			$this->db->set('total_marks', $data['total_marks']);
			$this->db->set('paper_id', $this->uri->segment(3));
			$this->db->where(array('student_id'=>$data['student_id'][$i], 'paper_id'=>$this->uri->segment(3)));
			$this->db->update('results');
		}
	}
	
	public function getStudentsResult($paper_id)
	{
		$this->db->select('*');
		$this->db->from('results');
		$this->db->join('students', 'results.student_id=students.student_id', 'inner');
		$this->db->where('results.paper_id', $paper_id);
		$query = $this->db->get()->result_array();
		return $query;
	}
			
}
?>