<?php
class Visitor extends CI_Model {
	
	public function storeVisitor($data)
	{
		foreach(@$data as $k=>$value){
			$this->db->set(''.$k.'', $value);
		}
		$this->db->insert('visitors');
		
	}
	
	public function updateVisitor($data)
	{
		foreach(@$data as $k=>$value){
			$this->db->set(''.$k.'', $value);
		}
		$this->db->where('visitor_id', $this->uri->segment(3));
		$this->db->update('visitors');
		
	}
	
	public function getVisitor($id)
	{
		$this->db->select('*');
		$this->db->from('visitors');
		$this->db->where(array('visitor_id'=>$id));
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getVisitors()
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['campus_ids']);
		
		$campus_array = array();
		foreach($campus_ids as $campus_id)
		{
			$campus_name = $this->db->get_where('campuses', array('campus_id'=>$campus_id))->result_array();
			$campus_name = @$campus_name[0]['campus_name'];
			array_push($campus_array, $campus_name);
		}
		
		$this->db->select('*');
		$this->db->from('visitors');
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campus', $campus_array);
		}
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getCampus()
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['campus_ids']);
		
		$this->db->select('*');
		$this->db->from('campuses');
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campus_id', $campus_ids);
		}
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getAllInterviews()
	{
		$this->db->select('*');
		$this->db->from('interview');
		$this->db->join('campuses', 'campuses.campus_id=interview.campus_id', 'inner');
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getInterview($interview_id)
	{
		$this->db->select('*');
		$this->db->from('interview');
		$this->db->where('interview_id', $interview_id);
		$query = $this->db->get()->result_array();
		return $query;
	}

			
}
?>

