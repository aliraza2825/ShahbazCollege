<?php
class Supplies extends CI_Model {
	
	public function storeVisitor($data)
	{
		foreach(@$data as $k=>$value){
			$this->db->set(''.$k.'', $value);
		}
		$this->db->insert('visitors');
		
	}
			
}
?>