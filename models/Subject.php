<?php
class Subject extends CI_Model {
	
	public function storeSubject($data)
	{
		foreach(@$data as $k=>$value){
			if($k!='extra_course_id')
			{
				$this->db->set(''.$k.'', $value);
			}
		}
		
		$extra_course_ids = implode(',',@$this->input->post('extra_course_ids'));
		
		$this->db->set('extra_course_ids',@$extra_course_ids);
		
		$this->db->insert('course_subjects');
		
	}
	
	public function updateSubject($data)
	{
		foreach(@$data as $k=>$value)
		{
			if($k!='extra_course_id')
			{
				$this->db->set(''.$k.'', $value);
			}
		}
		
		$extra_course_ids = implode(',',@$this->input->post('extra_course_ids'));
		
		$this->db->set('extra_course_ids',@$extra_course_ids);
		
		$this->db->where('course_subject_id', $this->uri->segment(3));
		$this->db->update('course_subjects');
		
	}
	
	public function deleteSubject($id)
	{
		$this->db->set('status', '0');
		$this->db->where('course_subject_id', $id);
		$this->db->update('course_subjects');
		
	}
	
	public function getAllSubject()
	{
		$this->db->select('*');
		$this->db->from('course_subjects');
		$this->db->join('courses', 'courses.course_id = course_subjects.course_id', 'left');
		$this->db->where(array('course_subjects.status'=>'1'));
		$this->db->order_by('course_subjects.course_subject_id', 'asc');
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getClasses()
	{
		$this->db->select('*');
		$this->db->from('classes');
		$this->db->where(array('status'=>'1'));
		$this->db->order_by('class_id', 'asc');
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	
	public function getTeacherClasses()
	{
		$user_id = $this->session->userdata('user_id');
	$this->db->select('*');
		$this->db->from('classes');
		$this->db->where(array('status'=>'1','teacher_id'=>$user_id));
		$this->db->order_by('id', 'asc');
		$query = $this->db->get()->result_array();
		//return $query;
		
		$classes = array();
		
		$i=0;
		foreach ($query as $k=>$value){
			$classes[$i]['class_info'] = $value;
			$classes[$i]['teacher_info'] = $this->getClassTeacher($value['teacher_id']);
			$classes[$i]['students_count'] = $this->getClassStudentsCount($value['id']);
			$classes[$i]['class_earning'] = $this->getEstimateClassEarnings($value['id']); 
			$i++;
		}
		
		return $classes;
	}
	
	public function editSubject($id)
	{
		$this->db->select('*');
		$this->db->from('course_subjects');
		$this->db->where('course_subject_id', $id);
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getStudents($class_id)
	{
		$this->db->select('students.*,classes.name as class_name');
		$this->db->from('students');
		$this->db->join('classes', 'classes.id = students.class_id', 'inner');
		$this->db->where(array('students.status'=>'1', 'class_id'=>$class_id));
		$this->db->order_by('students.surname', 'asc');
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getClassStudents($id)
	{
		$this->db->select('students.*,classes.name as class_name, payment_plans.name as payment_plan');
		$this->db->from('students');
		$this->db->join('classes', 'classes.id = students.class_id', 'inner');
		$this->db->join('payment_plans', 'payment_plans.id = students.payment_plan', 'inner');
		$this->db->where(array('students.status'=>'1','students.class_id'=>$id));
		$this->db->order_by('students.surname', 'asc');
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function classStudentsCount($id)
	{
		$this->db->select('students.*,classes.name as class_name, payment_plans.name as payment_plan');
		$this->db->from('students');
		$this->db->join('classes', 'classes.id = students.class_id', 'inner');
		$this->db->join('payment_plans', 'payment_plans.id = students.payment_plan', 'inner');
		$this->db->where(array('students.status'=>'1','students.class_id'=>$id));
		$query = $this->db->count_all_results();
		return $query;
	}
	/**
		 * @return mixed
     */
	public function getPopularClass()
	{
		//'select top 1 class, count(s.id)from class inner join student on c.id=s.cid group by s.cid order by count(s.id) desc'
		$query =$this->db->query("SELECT AVG(a.count) AS avg FROM ( SELECT count(*) AS count, MONTH(registration_date) as mnth FROM students GROUP BY mnth) AS a")->result_array();
		//$query = $this->db->get()->result_array();
		return $query;
	}
	public function getAverageStudentsPerClass()
	{
		//'select top 1 class, count(s.id)from class inner join student on c.id=s.cid group by s.cid order by count(s.id) desc'
		$query =$this->db->query("SELECT AVG(a.count) AS avg FROM ( SELECT count(*) AS count, students.class_id as st_class FROM students GROUP BY st_class ) AS a")->result_array();
		//$query = $this->db->get()->result_array();
		return $query;
	}
	public function getClassEarnings($id)
	{
		$this->db->select_sum('amount');
		$this->db->where(array('class_id'=>$id, 'amount!='=>'F'));
		$query = $this->db->get('payments')->result_array();
		return $query;
	}
	
	public function getSubjectsCount()
	{
		$this->db->select('*');
		$this->db->from('course_subjects');
		$this->db->where(array('status'=>'1'));
		$query = $this->db->count_all_results();
		return $query;
	}
	
	public function getTeachers(){
		$this->db->select('*');
		$this->db->from('users');
		$this->db->where(array('status'=>'1','role'=>'Teacher'));
		$query = $this->db->get()->result_array();
		return $query;
	}
			
}
?>