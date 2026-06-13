<?php
class Holiday extends CI_Model {
	
	public function getHolidays($from_date,$to_date)
	{
		$this->db->order_by('holiday_id', 'DESC');
		$this->db->where(array('date>='=>$from_date, 'date<='=> $to_date));
		$query = $this->db->get('holidays')->result_array();
		return $query;
	}
	
	public function insertHoliday($data)
	{
				
		if(isset($data['staff_types'])){
			$staff_types = implode(',',$data['staff_types']);
		}else
		{
			$staff_types = "";
		}
		
		foreach(@$data['date'] as $value)
		{			
			$this->db->set('date', $value);
			$this->db->set('campus_ids', implode(',',$data['campus_ids']));
			$this->db->set('staff_type_ids', implode(',',$data['staff_type_ids']));
			$this->db->set('user_ids', implode(',',$data['user_ids']));
			$this->db->set('shift_ids', implode(',',$data['shift_ids']));
			$this->db->set('student_ids', implode(',',$data['student_ids']));
			$this->db->set('reason', $data['reason_detail']);
			$this->db->set('add_by', $this->session->userdata('name'));
			
			$this->db->insert('holidays');
		}
	}
	
	public function checkHoliday($date)
	{
		$query = $this->db->get_where('holidays', array('date'=>$date))->result_array();
		return $query;
	}
	
	public function getCampuses()
	{
		$query = $this->db->get('campuses')->result_array();
		return $query;
	}
			
}
?>