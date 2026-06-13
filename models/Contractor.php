<?php
class Contractor extends CI_Model {
	
	public function storeContractor($data)
	{
		foreach(@$data as $k=>$value){
			if($k!='contractor_documents')
			{
				if($k=='password')
				{
					$this->db->set(''.$k.'', md5($value));
				}
				else
				{
					$this->db->set(''.$k.'', $value);
				}
			}
		}
		$this->db->insert('contractors');
		
		return $this->db->insert_id();
	}
	
	public function updateContractor($data)
	{
		foreach(@$data as $k=>$value){
			if($k!='contractor_documents')
			{
				if($k=='password')
				{
					$this->db->set(''.$k.'', md5($value));
				}
				else
				{
					$this->db->set(''.$k.'', $value);
				}
			}
		}
		$this->db->where('contractor_id', $this->uri->segment(3));
		$this->db->update('contractors');
		
	}
	
	public function deleteContractor($id)
	{
		$this->db->where('contractor_id', $id);
		$this->db->delete('contractors');
		
	}
	
	public function getContractors()
	{
		$this->db->select('*');
		$this->db->from('contractors');
		$this->db->order_by('contractor_id', 'asc');
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function editContractor($id)
	{
		$this->db->select('*');
		$this->db->from('contractors');
		$this->db->where('contractor_id', $id);
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getContractorCount()
	{
		$this->db->select('*');
		$this->db->from('contractors');
		$query = $this->db->count_all_results();
		return $query;
	}
			
}
?>