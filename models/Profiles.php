<?php
class Profiles extends CI_Model {
	
	public function getCurrentUser($user_id)
	{
		$this->db->select('*');
		$this->db->from('users');
		$this->db->where(array('user_id'=>$user_id));
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function updateCurrentUser($data)
	{
		$user_id = $this->session->userdata('user_id');
		foreach(@$data as $k=>$value){
			$this->db->set(''.$k.'', $value);
		}
		$this->db->where('user_id', $user_id);
		$this->db->update('users');
	}
			
}
?>
