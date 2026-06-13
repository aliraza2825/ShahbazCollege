<?php
class Teacher extends CI_Model {
	
	public function storeTeacher($data)
	{
		foreach(@$data as $k=>$value){
			$this->db->set(''.$k.'', $value);
		}
		$this->db->insert('users');
		$insert_id = $this->db->insert_id();

		return  $insert_id;
		
	}
	
	public function updateTeacher($data)
	{
		foreach(@$data as $k=>$value){
			$this->db->set(''.$k.'', $value);
		}
		$this->db->where('user_id', $this->uri->segment(3));
		$this->db->update('users');
		
	}
	
	public function deleteTeacher($id)
	{
		$this->db->set('status', '0');
		$this->db->where('user_id', $id);
		$this->db->update('users');
		
	}
	
	public function getTeachers()
	{
		$access = checkUserAccess();
		$campuses = @explode(',',$access[0]['campus_ids']);
		
		$this->db->select('*');
		$this->db->from('users');
		$this->db->join('campuses', 'campuses.campus_id=users.campus_id', 'left');
		$this->db->join('machine_data', 'machine_data.teacher_student_id=users.user_id', 'left');
		$this->db->join('staff_type', 'staff_type.staff_type_id=users.staff_type_id', 'left');
		$this->db->join('departments', 'departments.department_id=users.department_id', 'left');
		$this->db->join('designations', 'designations.designation_id=users.designation_id', 'left');
		$this->db->where(array('users.role!='=>'Admin','users.status'=>'1'));
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('users.campus_id', $campuses);
		}
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function editTeacher($id)
	{
		$this->db->select('*');
		$this->db->from('users');
		$this->db->where('user_id', $id);
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getTeachersCount()
	{
		$this->db->select('*');
		$this->db->from('users');
		$this->db->where(array('status'=>'1', 'role!='=>'Admin'));
		$query = $this->db->count_all_results();
		return $query;
	}
	
	public function checkTeacherNIC($cnic)
	{
		$this->db->select('*');
		$this->db->from('users');
		$this->db->where(array('cnic'=>$cnic));
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function checkTeacherUsername($username)
	{
		$this->db->select('*');
		$this->db->from('users');
		$this->db->where(array('username'=>$username));
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function uploadDocument($id, $teacher_document)
	{
		$this->db->set('teacher_id', $id);
		$this->db->set('image', $teacher_document);
		$this->db->insert('teacher_documents');
	}
	
	public function uploadedDocuments($id)
	{
		$this->db->select('*');
		$this->db->from('teacher_documents');
		$this->db->where('teacher_id', $id);
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function checkUserTiming($user_id)
	{
		$query = $this->db->get_where('staff_timing', array('staff_id'=>$user_id))->result_array();
		return $query;
	}
	
	public function getTeacher($user_id)
	{
		$query = $this->db->get_where('users', array('user_id'=>$user_id))->result_array();
		return $query;
	}
	
	public function checkMachineUser($user_id)
	{
		$query = $this->db->get_where('machine_data', array('teacher_student_id'=>$user_id, 'type!='=>'teacher'))->result_array();
		return $query;
	}
	
	public function getCampuses()
	{
		$query = $this->db->get('campuses')->result_array();
		return $query;
	}
	
	public function updateTeacherTiming($data, $user_id)
	{
		$count = count($data['checkin_time']);
		for($i=0; $i<$count; $i++)
		{
			$checkStaffEntry = $this->db->get_where('staff_timing', array('staff_id'=>$user_id, 'day'=>$data['day'][$i]))->result_array();
			
			$this->db->set('day', $data['day'][$i]);
			$this->db->set('checkin_timing', $data['checkin_time'][$i]);
			$this->db->set('checkout_timing', $data['checkout_time'][$i]);
			$this->db->set('staff_id', $user_id);
			
			if(count($checkStaffEntry)>0)
			{
				$this->db->where(array('day'=>$data['day'][$i], 'staff_id'=>$user_id));
				$this->db->update('staff_timing');
			}
			else
			{
				$this->db->insert('staff_timing');
			}
		}
	}
			
}
?>
