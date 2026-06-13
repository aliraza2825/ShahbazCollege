<?php
class Clas extends CI_Model {
	
	public function storeClass($data)
	{
		foreach(@$data as $k=>$value){
			$this->db->set(''.$k.'', $value);
		}
		$this->db->insert('classes');
		
	}
	
	public function updateClass($data)
	{
		foreach(@$data as $k=>$value){
			$this->db->set(''.$k.'', $value);
		}
		$this->db->where('class_id', $this->uri->segment(3));
		$this->db->update('classes');
		
	}
	
	public function deleteClass($id)
	{
		$this->db->set('status', '0');
		$this->db->where('class_id', $id);
		$this->db->update('classes');
		
	}
	
	public function getAllClasses()
	{
		$this->db->select('classes.*, users.name as teacher_name');
		$this->db->from('classes');
		$this->db->join('users', 'users.id = classes.teacher_id', 'inner');
		$this->db->where(array('classes.status'=>'1'));
		$this->db->order_by('classes.id', 'asc');
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getClasses()
	{
		$access = checkUserAccess();
		$class_ids = @explode(',',$access[0]['class_ids']);
		
		$this->db->select('classes.*, campuses.campus_id,campuses.campus_id,campuses.campus_name,courses.course_name');
		$this->db->from('classes');
		$this->db->join('campuses', 'campuses.campus_id=classes.campus_id', 'left');
		$this->db->join('courses', 'courses.course_id=classes.course_id', 'left');
		$this->db->where(array('classes.status'=>'1'));
		
		if($this->session->userdata('role')!='Admin'){
			$this->db->where_in('classes.class_id', $class_ids);
		}
		$this->db->order_by('classes.class_id', 'asc');
		$query = $this->db->get()->result_array();
		return $query;
	}

	public function getAllClassesActiveInactive()
	{
		$access = checkUserAccess();
		$class_ids = @explode(',',$access[0]['class_ids']);
		
		$this->db->select('classes.*, campuses.campus_id,campuses.campus_id,campuses.campus_name,courses.course_name');
		$this->db->from('classes');
		$this->db->join('campuses', 'campuses.campus_id=classes.campus_id', 'left');
		$this->db->join('courses', 'courses.course_id=classes.course_id', 'left');
		
		if($this->session->userdata('role')!='Admin'){
			$this->db->where_in('classes.class_id', $class_ids);
		}
		$this->db->order_by('classes.class_id', 'asc');
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getCampuses()
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['campus_ids']);
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campus_id', $campus_ids);
		}
		$this->db->where('campuses.status',1);
		$query = $this->db->get('campuses')->result_array();
		return $query;
	}
	
	
	public function getTeacherClasses()
	{
		$user_id = $this->session->userdata('user_id');
		
		$this->db->select('classes.*, subjects.name as subject_name');
		$this->db->from('subjects');
		$this->db->join('classes', 'classes.class_id=subjects.class_id', 'inner');
		$this->db->where(array('subjects.status'=>'1','subjects.teacher_id'=>$user_id));
		$query = $this->db->get()->result_array();
		
		return $query;
	}
	
	public function editClass($id)
	{
		$this->db->select('*');
		$this->db->from('classes');
		$this->db->where('class_id', $id);
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getStudents($class_id)
	{
		$this->db->select('students.*, classes.name as class_name, campuses.campus_name, courses.course_name, machine_data.machine_id');
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
		$this->db->join('campuses', 'campuses.campus_id=classes.campus_id', 'inner');
		$this->db->join('courses', 'courses.course_id=students.course_id', 'inner');
		$this->db->join('machine_data', 'machine_data.teacher_student_id=students.student_id', 'inner');
		$this->db->where(array('students.status'=>'1', 'students.class_id'=>$class_id));
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
	
	public function getClassesCount()
	{
		$this->db->select('classes.*,courses.course_name');
		$this->db->from('classes');
		$this->db->join('courses','courses.course_id=classes.course_id','inner');
		$this->db->where(array('classes.status'=>'1'));
		$query = $this->db->count_all_results();
		return $query;
	}
	
	public function getCourses()
	{
		$this->db->select('*');
		$this->db->from('courses');
		$query = $this->db->get()->result_array();
		return $query;
	}
			
}
?>