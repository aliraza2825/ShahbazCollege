<?php
class Expense extends CI_Model {
	
	public function storeExpense($data, $image)
	{
		if(@$data['expense_category_id'][count($data['expense_category_id'])-1]==13)
		{
			$student_ids = explode(",",$data['student_ids']);
			foreach($student_ids as $key=>$student_id)
			{
			    $st_detail = $this->db->get_where("students","student_id = '".$student_id."'")->row();
				$this->db->set('campus_id',$data['campus_id']);
				$this->db->set('expense_category_id',$data['expense_category_id'][count($data['expense_category_id'])-1]);
				$this->db->set('title',$data['title']);
				$this->db->set('date',$data['date']);
				$this->db->set('amount',$data['amount']);
				$this->db->set('purpose',$data['purpose']);
				$this->db->set('month_year',$data['month_year']);
				$this->db->set('class',@$data['class']);

				if($data['type']=='result')
				{
					$this->db->set('council_exam_no',$data['result_council_exam_no']);
				}
				elseif($data['type']=='class')
				{
					$this->db->set('council_exam_no',$data['class_council_exam_no']);
				}
				
				$this->db->set('student_id',$student_id);
				$this->db->set('actual_date', date('Y-m-d H:i:s'));
				$this->db->set('image', $image);
				$this->db->set('payment_type', $data['payment_type']);
				$this->db->set('class_id', $st_detail->class_id);
				$this->db->set('roll_no', $st_detail->roll_no);
				$this->db->set('add_by', $this->session->userdata('name'));
				$this->db->set('last_edit', $this->session->userdata('name'));
				$this->db->set('add_by_id', $this->session->userdata('user_id'));
				$this->db->set('approved_status', 1);
				if($data['payment_type']=='cash')
				{
					$this->db->set('paid_type', 'cash');
				}
				else
				{
					$this->db->set('paid_type', 'bank');
				}
				$this->db->insert('expenses');
				$insert_id = $this->db->insert_id();
				if ($data['payment_type'] != "cash") {
                    $this->db->set('tagged_amount', 'tagged_amount +' .$data['amount']. '', false);
                    $this->db->set('expense_id',$insert_id);
                    $this->db->where('id', $data['payment_type']);
                    $this->db->update('bank_reconciliation_statement');
                }
			}
		}
		else
		{
			foreach(@$data as $k=>$value){
				if($k=='student_ids')
				{
					
				}
				else
				{
					if($k!='image' && $k!='type' && $k!='class_id' && $k!='class_council_exam_no' && $k!='msg' && $k!='expense_category_id')
					{
						$this->db->set(''.$k.'', $value);
					}
				}
			}
			$this->db->set('actual_date', date('Y-m-d H:i:s'));
			$this->db->set('image', $image);
			$this->db->set('expense_category_id', $data['expense_category_id'][count($data['expense_category_id'])-1]);
			$this->db->insert('expenses');
		}
	}
	
	public function updateExpense($data, $image)
	{
		foreach(@$data as $k=>$value){
			if($k!='msg' && $k!='expense_category_id'){
			if($k!='image')
			{
				$this->db->set(''.$k.'', $value);
			}
			if($image!='')
			{
				$this->db->set('image', $image);
			}
			}
		}
        $this->db->set('expense_category_id', $data['expense_category_id'][count($data['expense_category_id'])-1]);
		$this->db->where('expense_id', $this->uri->segment(3));
		$this->db->update('expenses');
		
	}
	
	public function deleteExpense($id)
	{
		$this->db->where('expense_id', $id);
		$exp = $this->db->get('bank_reconciliation_statement')->row_array();
		if($exp){
		    $this->db->where('id', $exp['id']);
		    $this->db->update('bank_reconciliation_statement',array('expense_id'=>null));
		}
		
		$this->db->where('expense_id', $id);
		$this->db->delete('expenses');
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
	
	public function getExpense()
	{
		$this->db->select('*');
		$this->db->from('expenses');
		$this->db->where('expense_id', $this->uri->segment(3));
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getCategories()
	{
		$this->db->order_by('name', 'ASC');
		$query = $this->db->get('expense_category')->result_array();
		return $query;
	}
	
	public function addExpenseCategories($data)
	{
		$this->db->set('name', $data['name']);
		$this->db->set('for_campus', implode(",",$data['campus_ids']));
		if($data['head_category'] != "" && $data['head_category'] != null)
		{
			$this->db->set('sub_of', $data['head_category']);
			$this->db->insert('expense_category');
			
			$this->db->set('has_sub',"1");
			$this->db->where('expense_category_id',$data['head_category']);
			$this->db->update('expense_category');
		}
		else
		{
			$this->db->insert('expense_category');
		}
	}
	
	public function getCategory($expense_category_id)
	{
		$query = $this->db->get_where('expense_category', array('expense_category_id'=>$expense_category_id))->result_array();
		return $query;
	}
			
}
?>