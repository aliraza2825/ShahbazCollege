<?php
class Dashboards extends CI_Model {
	
	public function total_students()
	{
		$access = checkUserAccess();
		$campuses = @explode(',',$access[0]['campus_ids']);
		
		$this->db->select('*');
		$this->db->from('students');
		$this->db->join('classes','classes.class_id=students.class_id','inner');
		$this->db->join('campuses','classes.campus_id=campuses.campus_id','inner');
		$this->db->where(array('students.status'=>'1'));
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campuses.campus_id', $campuses);
		}
		$query = $this->db->count_all_results();
		return $query;
	}
	
	public function total_teachers()
	{
		$access = checkUserAccess();
		$campuses = @explode(',',$access[0]['campus_ids']);
		
		$this->db->select('*');
		$this->db->from('users');
		$this->db->where(array('status'=>'1', 'role!='=>'Admin'));
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campus_id', $campuses);
		}
		$query = $this->db->count_all_results();
		return $query;
	}
	
	public function new_students_this_month()
	{
		$access = checkUserAccess();
		$campuses = @explode(',',$access[0]['campus_ids']);
		
		$start_date = date('Y-m-01');
		$end_date = date('Y-m-t');
		
		$this->db->select('*');
		$this->db->from('students');
		$this->db->join('classes','classes.class_id=students.class_id','inner');
		$this->db->join('campuses','classes.campus_id=campuses.campus_id','inner');
		$this->db->where(array('students.status'=>'1', 'students.registration_date>='=>$start_date, 'students.registration_date<='=>$end_date));
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campuses.campus_id', $campuses);
		}
		$query = $this->db->count_all_results();
		return $query;
	}
	
	public function thisMonthEarning()
	{
		$access = checkUserAccess();
		$campuses = @explode(',',$access[0]['campus_ids']);
		
		if($this->session->userdata('role')!='Admin')
		{
			$qry = "SELECT SUM(p.actual_amount) as this_month_earning FROM payments as p INNER JOIN students as s ON p.student_id=s.student_id INNER JOIN classes as c ON c.class_id=s.class_id INNER JOIN campuses as camp ON camp.campus_id=c.campus_id WHERE p.paid=1 AND month(p.paid_date)=".date('m')." AND year(p.paid_date)=".date('Y')." AND camp.campus_id IN (".@$access[0]['campus_ids'].") ";
		}
		else
		{
			$qry = "SELECT SUM(actual_amount) as this_month_earning FROM payments WHERE paid=1 AND paid_date >= '".date('Y-m-01')."' AND paid_date <= '".date('Y-m-d')."'";
		}
		$query = $this->db->query($qry)->result_array();
		return $query;
	}
	
	public function thisMonthExpense()
	{
		$access = checkUserAccess();
		
		if($this->session->userdata('role')!='Admin')
		{
			$qry = "SELECT SUM(e.amount) as this_month_expense FROM expenses as e INNER JOIN campuses as c ON e.campus_id=c.campus_id WHERE month(e.date)=".date('m')." AND year(e.date)=".date('Y')." AND c.campus_id IN (".@$access[0]['campus_ids'].")";
			
		}
		else
		{
			$qry = "SELECT SUM(amount) as this_month_expense FROM expenses WHERE month(date)=".date('m')." AND year(date)=".date('Y')."";
		}
		$query = $this->db->query($qry)->result_array();
		return $query;
	}
	
	public function unpaidCollegeFee($campus_id=NULL)
	{
		$access = checkUserAccess();
		$campuses = @explode(',',$access[0]['campus_ids']);
		
		$this->db->select('students.*,courses.* ,classes.name,payments.*,campuses.*');
		$this->db->from('payments');
		$this->db->join('students', 'payments.student_id=students.student_id', 'inner');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
        $this->db->join('courses', 'courses.course_id=classes.course_id', 'left');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
		if(@$campus_id!=NULL)
		{
			$this->db->where('campuses.campus_id',$campus_id);
		}
		$this->db->where(array('students.contract_id'=>0,'payments.paid'=> 1, 'payments.clear_college_fee'=>0,'payments.fee_submit_type !='=>'computer_challan'));
		$this->db->group_by("CASE WHEN payments.merged_challan IS NOT NULL THEN payments.merged_challan ELSE payments.challan_no END",false);

		$this->db->group_by('payments.paid_challans');
		$this->db->order_by('payments.paid_date', 'DESC');
		$query = $this->db->get()->result_array();
		
		return $query;
	}
	
	public function unpaidContractorsCollegeFee($campus_id=NULL)
	{
		$access = checkUserAccess();
		$campuses = @explode(',',$access[0]['campus_ids']);
		
		$this->db->select('*');
		$this->db->from('payments');
		$this->db->join('contracts', 'contracts.contract_id=payments.contract_id', 'inner');
		$this->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'inner');
		$this->db->join('campuses', 'contracts.campus_id=campuses.campus_id', 'inner');
		$this->db->where(array('payments.paid'=> 1, 'payments.clear_college_fee'=>0,"fee_submit_type != "=>'computer_challan'));
        $this->db->group_by("CASE WHEN payments.merged_challan IS NOT NULL THEN payments.merged_challan ELSE payments.challan_no END",false);
		if(@$campus_id!=NULL){
			$this->db->where('campuses.campus_id',$campus_id);
		}
		if($this->session->userdata('role')!='Admin'){
			$this->db->where_in('campuses.campus_id', $campuses);
		}
		$this->db->order_by('payments.paid_date', 'ASC');
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function clearUnpaidFee($payment_id)
	{
	    $fee = $this->db->get_where("payments","id = '$payment_id'")->result_array();

	    if ($fee[0]['merged_challan'] != NULL) {
            $this->db->set('clear_college_fee', '1');
            $this->db->set('clear_by',$this->session->userdata('name'));
            $this->db->where('merged_challan',$fee[0]['merged_challan']);
            $this->db->update('payments');
        }else{
            $this->db->set('clear_college_fee', '1');
            $this->db->set('clear_by',$this->session->userdata('name'));
            $this->db->where('id',$payment_id);
            $this->db->update('payments');
        }
	}
	
	public function classesStatus()
	{	
		$access = checkUserAccess();
		$campuses = @explode(',',$access[0]['campus_ids']);
		
		if($this->session->userdata('role')!='Admin')
		{
		
			$sql = "SELECT MAX(classes.name) AS name, COUNT(students.class_id) AS total_students, classes.class_id, classes.seats
					FROM classes
					LEFT JOIN students ON students.class_id = classes.class_id
					LEFT JOIN campuses ON campuses.campus_id = classes.campus_id
					WHERE classes.status=1 AND students.status=1 AND campuses.campus_id IN (".@$access[0]['campus_ids'].")
					GROUP BY classes.class_id";
		}
		else
		{
			$sql = "SELECT MAX(classes.name) AS name, COUNT(students.class_id) AS total_students, classes.class_id, classes.seats
					FROM classes
					LEFT JOIN students ON students.class_id = classes.class_id
					WHERE classes.status=1 AND students.status=1
					GROUP BY classes.class_id";
		}
		$query = $this->db->query($sql)->result_array();
		return $query;
	}
	
	public function newSubmitFees($start_date, $end_date, $date_type)
	{
		$access = checkUserAccess();
		$campuses = @explode(',',$access[0]['campus_ids']);
		
		if($this->session->userdata('role')!='Admin')
		{
			$qry = "SELECT p.* FROM payments as p INNER JOIN students as s ON s.student_id=p.student_id 
			INNER JOIN classes as c ON c.class_id=s.class_id INNER JOIN campuses as camp ON camp.campus_id=c.campus_id WHERE p.paid=1 AND p.".$date_type.">='$start_date' 
			AND p.".$date_type."<='$end_date' AND camp.campus_id IN (".@$access[0]['campus_ids'].") group by p.merged_challan
			union all
			SELECT p.* FROM payments as p INNER JOIN students as s ON s.student_id=p.student_id 
			INNER JOIN classes as c ON c.class_id=s.class_id INNER JOIN campuses as camp ON camp.campus_id=c.campus_id WHERE p.paid=1 AND p.".$date_type.">='$start_date' 
			AND p.".$date_type."<='$end_date' AND camp.campus_id IN (".@$access[0]['campus_ids'].") and  p.merged_challan is null";
		}
		else
		{
			$qry = "SELECT p.* FROM payments as p WHERE p.paid=1 AND p.".$date_type.">='$start_date' AND p.".$date_type."<='$end_date' group by p.merged_challan
					union all
					SELECT p.* FROM payments as p WHERE p.paid=1 AND p.".$date_type.">='$start_date' AND p.".$date_type."<='$end_date' and  p.merged_challan is null"
					;
		}
		$query = $this->db->query($qry)->result_array();
		return $query; 
	}
	
	public function getTotalSubmittedFee($start_date, $end_date, $date_type)
	{
		$access = checkUserAccess();
		$class_ids = @explode(',',$access[0]['class_ids']);
		
		if($this->session->userdata('role')!='Admin')
		{
			$qry = "SELECT sum(p.actual_amount) as total_submitted_fee FROM payments as p INNER JOIN students as s ON s.student_id=p.student_id INNER JOIN classes as c ON c.class_id=s.class_id INNER JOIN campuses as camp ON camp.campus_id=c.campus_id WHERE p.paid=1 AND p.".$date_type.">='$start_date' AND p.".$date_type."<='$end_date' AND camp.campus_id IN (".@$access[0]['campus_ids'].")";
		}
		else
		{
			$qry = "SELECT sum(amount+fine_amount) as total_submitted_fee FROM payments as p WHERE p.paid=1 AND p.".$date_type.">='$start_date' AND p.".$date_type."<='$end_date'";
		}
		$query = $this->db->query($qry)->result_array();
		return $query;
	}
	
	public function newExpenses($start_date, $end_date,$date_type)
	{
		$access = checkUserAccess();
		
		if($this->session->userdata('role')!='Admin')
		{
			$qry = "SELECT e.*, c.campus_name as campus_name FROM expenses as e INNER JOIN campuses as c ON c.campus_id=e.campus_id WHERE e.".$date_type.">='$start_date' AND e.".$date_type."<='$end_date' AND c.campus_id IN (".@$access[0]['campus_ids'].")";
		}
		else
		{
			$qry = "SELECT e.*, c.campus_name as campus_name FROM expenses as e INNER JOIN campuses as c ON c.campus_id=e.campus_id WHERE e.".$date_type.">='$start_date' AND e.".$date_type."<='$end_date'";
		}
		$query = $this->db->query($qry)->result_array();
		return $query;
	}
	
	public function getTotalExpenses($start_date, $end_date)
	{
		$access = checkUserAccess();
		
		if($this->session->userdata('role')!='Admin')
		{
			$qry = "SELECT sum(e.amount) as total_expenses FROM expenses as e INNER JOIN campuses as c ON c.campus_id=e.campus_id WHERE e.date>='$start_date' AND e.date<='$end_date' AND c.campus_id IN (".@$access[0]['campus_ids'].")";
		}
		else
		{
			$qry = "SELECT sum(amount) as total_expenses FROM expenses WHERE date>='$start_date' AND date<='$end_date'";
		}
		$query = $this->db->query($qry)->result_array();
		return $query;
	}
	
	public function getSelectiveExpenses($start_date, $end_date, $date_type)
	{
		$access = checkUserAccess();
		
		if($date_type=='date')
		{
			$date_type='date';
		}
		else
		{
			$date_type='actual_date';
		}
		
		if($this->session->userdata('role')!='Admin')
		{
			$qry = "SELECT sum(e.amount) as total_expenses FROM expenses as e INNER JOIN campuses as c ON c.campus_id=e.campus_id WHERE e.".$date_type.">='$start_date' AND e.".$date_type."<='$end_date' AND c.campus_id IN (".@$access[0]['campus_ids'].")";
		}
		else
		{
			$qry = "SELECT sum(amount) as total_expenses FROM expenses WHERE ".$date_type.">='$start_date' AND ".$date_type."<='$end_date'";
		}
		$query = $this->db->query($qry)->result_array();
		return $query;
	}
	
	public function getSelectiveProfits($start_date, $end_date, $date_type)
	{
		$access = checkUserAccess();
		
		if($date_type=='date')
		{
			$date_type='paid_date';
		}
		else
		{
			$date_type='actual_paid_date';
		}
		
		if($this->session->userdata('role')!='Admin')
		{
			$qry = "SELECT sum(p.actual_amount) as total_submitted_fee FROM payments as p INNER JOIN students as s ON s.student_id=p.student_id INNER JOIN classes as c ON c.class_id=s.class_id INNER JOIN campuses as camp ON camp.campus_id=c.campus_id WHERE p.".$date_type.">='$start_date' AND p.".$date_type."<='$end_date' AND camp.campus_id IN (".@$access[0]['campus_ids'].")";
		}
		else
		{
			$qry = "SELECT sum(actual_amount) as total_submitted_fee FROM payments WHERE ".$date_type.">='$start_date' AND ".$date_type."<='$end_date'";
		}
		$query = $this->db->query($qry)->result_array();
		return $query;
	}
	
	public function getNewStudents($month, $year)
	{
		//$start_date = date('Y-m-01');
		//$end_date = date('Y-m-t');
		
		$access = checkUserAccess();
		$class_ids = @explode(',',$access[0]['class_ids']);
		
		if($this->session->userdata('role')!='Admin')
		{
			$qry = "SELECT s.*, c.name as class_name,c.session as session ,camp.campus_name as campus_name,cour.course_name FROM students as s LEFT JOIN classes as c ON c.class_id=s.class_id LEFT JOIN campuses as camp ON c.campus_id=camp.campus_id LEFT JOIN courses as cour ON cour.course_id=c.course_id WHERE s.status=1 AND month(s.registration_date)=".$month."   AND year(s.registration_date)=".$year."  AND camp.campus_id IN (".@$access[0]['campus_ids'].")";
		}
		else
		{
            $qry = "SELECT s.*, c.name as class_name,c.session as session ,camp.campus_name as campus_name,cour.course_name FROM students as s LEFT JOIN classes as c ON c.class_id=s.class_id LEFT JOIN campuses as camp ON c.campus_id=camp.campus_id LEFT JOIN courses as cour ON cour.course_id=c.course_id WHERE s.status=1 AND month(s.registration_date)=".$month."   AND year(s.registration_date)=".$year;

       }
		$query = $this->db->query($qry)->result_array();
		return $query;
	}
	
	public function getFeeRequest($campus_id=NULL)
	{
		$access = checkUserAccess();
		$campuses = @explode(',',$access[0]['campus_ids']);
		
		$this->db->select('update_payment_requests.*, students.first_name, students.last_name, students.roll_no');
		$this->db->from('update_payment_requests');
		$this->db->join('students', 'students.student_id=update_payment_requests.student_id', 'left');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'left');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campuses.campus_id', $campuses);
		}
		$this->db->where('update_payment_requests.ok_by_admin', '0');
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getContractsFeeRequest($campus_id=NULL)
	{
		$access = checkUserAccess();
		$campuses = @explode(',',$access[0]['campus_ids']);
		
		if($this->session->userdata('role')=='Admin')
		{
			$this->db->select('update_payment_requests.*, contractors.name, contracts.contract_name');
			$this->db->from('update_payment_requests');
			$this->db->join('contracts', 'contracts.contract_id=update_payment_requests.contract_id', 'inner');
			$this->db->join('campuses', 'contracts.campus_id=campuses.campus_id', 'inner');
			$this->db->join('contractors', 'contracts.contractor_id=contractors.contractor_id', 'inner');
			if(@$campus_id!=NULL)
			{
				$this->db->where('campuses.campus_id',$campus_id);
			}
			$this->db->where('update_payment_requests.ok_by_admin', '0');
			$query = $this->db->get()->result_array();
			return $query;
		}
		else
		{
			$this->db->select('update_payment_requests.*, contractors.name, contracts.contract_name');
			$this->db->from('update_payment_requests');
			$this->db->join('contracts', 'contracts.contract_id=update_payment_requests.contract_id', 'inner');
			$this->db->join('campuses', 'contracts.campus_id=campuses.campus_id', 'inner');
			$this->db->join('contractors', 'contracts.contractor_id=contractors.contractor_id', 'inner');
			if(@$campus_id!=NULL)
			{
				$this->db->where('campuses.campus_id',$campus_id);
			}
			$this->db->where('update_payment_requests.ok_by_admin', '0');
			if($this->session->userdata('role')!='Admin')
			{
				$this->db->where_in('campuses.campus_id', $campuses);
			}
			$query = $this->db->get()->result_array();
			return $query;
		}
	}
	
	public function updatePayment($update_request)
	{
		if($update_request[0]['del']==1)
		{
			$this->db->where('id',$update_request[0]['id']);
			$this->db->delete('payments');
		}
		else
		{
            $payms = $this->db->get_where('payments',
                array('id'=>$update_request[0]['id']))->result_array();

			//UPDATE TAGGED AMOUNT IN BANK STATEMENT
			if($payms[0]['paid']==1 && $update_request[0]['paid']==0)
			{
				$statement = $this->db->get_where('bank_reconciliation_statement',array('id'=>$payms[0]['statement_id']))->result_array();
				$tagged_amount = $statement[0]['tagged_amount']-$payms[0]['actual_amount'];
				$this->db->set('tagged_amount',$tagged_amount);
				$this->db->where('id',$payms[0]['statement_id']);
				$this->db->update('bank_reconciliation_statement');
			}

			foreach($update_request[0] as $k=>$value)
			{
				if($k=='actual_paid_date' || $k=='actual_amount' || $k=='discount' || $k=='paid' || $k=='paid_date' || $k=='dead_line')
				{
					$this->db->set(''.$k.'', $value);
				}
			}
			if ($payms[0]['first_deadline'] == NULL || $payms[0]['first_deadline'] == '' )
            {

                $this->db->set('first_deadline', $payms[0]['dead_line']);

            }
			$this->db->set('statement_id', NULL);
			$this->db->set('tid_no', '');


			if ($payms[0]['paid_challans'] == null) {
                $this->db->where('id', $update_request[0]['id']);
                $this->db->update('payments');
            }
			else {
                $this->db->where('paid_challans', $payms[0]['paid_challans']);
                $this->db->update('payments');
            }
		}
	}
	
	public function updateClearPayment($payment_id)
	{
		$this->db->set('ok_by_admin', '1');
        $this->db->set('clear_by', $this->session->userdata('name'));
		$this->db->where(array('id'=>$payment_id,'ok_by_admin'=>0));
		$this->db->update('update_payment_requests');
	}
	
	public function getStudentsEditRequest($campus_id=NULL)
	{
		$access = checkUserAccess();
		$campuses = @explode(',',$access[0]['campus_ids']);
		
		$this->db->select('update_student_requests.*, classes.name as class_name');
		$this->db->from('update_student_requests');
		$this->db->join('classes', 'classes.class_id=update_student_requests.class_id', 'inner');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
		if(@$campus_id!=NULL)
		{
			$this->db->where('campuses.campus_id',$campus_id);
		}
		$this->db->where('update_student_requests.ok_by_admin', '0');
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campuses.campus_id', $campuses);
		}
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function updateStudent($update_request)
	{
	    $student = $this->db->get_where('students', array('student_id'=>$update_request[0]['student_id']))->result_array();
	    //CHECK STUDENT ADDED AGAINST CNIC MORE THAN ONE TIME IN OUR SYSTEM
	    if($update_request[0]['cnic'] != $student[0]['cnic'])
	    {
	        $check = $this->db->get_where('students', array('cnic'=>$update_request[0]['cnic'],'course_id'=>$update_request[0]['course_id']))->result_array();
	        if(count($check)>0)
	        {
	            return 'failed';
	        }
	    }
	    foreach($update_request[0] as $k=>$value)
    		{
    			if($k!='ok_by_admin' && $k!='student_id' && $k!='update_date')
    			{
    				$this->db->set(''.$k.'', $value);
    			}
    		}
    		$this->db->where('student_id',$update_request[0]['student_id']);
    		$this->db->update('students');
    		
    
    		if($update_request[0]['class_id'] != $student[0]['class_id'])
            {
                $lastclass = $this->db->get_where('classes', array('class_id'=>$student[0]['class_id']))->result_array();
                $newclass  = $this->db->get_where('classes', array('class_id'=>$update_request[0]['class_id']))->result_array();
    
                if($lastclass[0]['campus_id'] != $newclass[0]['campus_id'])
                {
                    $fr_date = getFromDateProfitDistribution($lastclass[0]['campus_id']);
                    $from_record = $this->db->select('challan_no')->get_where('payments', array('actual_paid_date <'=>$fr_date,'student_id'=>$update_request[0]['student_id'],'paid'=>"1"))->result_array();
    
                    $from_arr = array_column($from_record, "challan_no");
                    $from_total = $this->db->select('sum((amount+fine_amount)-discount) as amount')->get_where('payments', array('actual_paid_date <' => $fr_date, 'student_id' => $update_request[0]['student_id'], 'paid' => "1"))->result_array();
    
                    $to_date = getFromDateProfitDistribution($newclass[0]['campus_id']);
                    $to_record = $this->db->select('challan_no')->get_where('payments', array('actual_paid_date <'=>$to_date,'student_id'=>$update_request[0]['student_id'],'paid'=>"1"))->result_array();
    
                    $to_arr = array_column($to_record, "challan_no");
                    $to_total = $this->db->select('sum((amount+fine_amount)-discount) as amount')->get_where('payments', array('actual_paid_date <' => $to_date, 'student_id' => $update_request[0]['student_id'], 'paid' => "1"))->result_array();
    
                    $this->db->set('student_id', $update_request[0]['student_id']);
                    $this->db->set('from_badge', $student[0]['class_id']);
                    $this->db->set('to_badge', $update_request[0]['class_id']);
                    $this->db->set('from_class', $lastclass[0]['campus_id']);
                    $this->db->set('to_class', $newclass[0]['campus_id']);
                    $this->db->set('from_fee_ids', json_encode($from_arr));
                    $this->db->set('expense_amount', @$from_total[0]['amount'] ? $from_total[0]['amount'] : 0);
                    $this->db->set('to_fee_ids', json_encode($to_arr));
                    $this->db->set('earned_amount', @$to_total[0]['amount'] ? $to_total[0]['amount'] : 0);
                    $this->db->set('from_closing_date', $fr_date);
                    $this->db->set('to_closing_date', $to_date);
                    $this->db->set('status', "0");
                    $this->db->set('created_by', $this->session->userdata('user_id'));
                    $this->db->insert('student_shift_details');
    				
    				//CHANGE STUDENT ROLL NUMBER ACCORDING TO CAMPUS
    				$course = $this->db->get_where("courses","course_id = '".$update_request[0]['course_id']."'")->row();
    				$class = $this->db->get_where("classes","class_id = '".$update_request[0]['class_id']."'")->row();
    				$campus = $this->db->get_where("campuses","campus_id = '".$class->campus_id."'")->row();
    				
    				//$student_count = $this->db->select('max(count) as count')->get_where("students","class_id = '".$update_request[0]['class_id']."'")->row();
    				//RESET ROLL NO
    				$this->db->set('roll_no',0);
    				$this->db->where('student_id',$update_request[0]['student_id']);
    				$this->db->update('students');
    				//GET MAX ROLL NO
    				$sql = "SELECT MAX(CAST(SUBSTRING_INDEX(roll_no, '-', 1) AS UNSIGNED)) AS max_number FROM students WHERE class_id=".$update_request[0]['class_id'];
    				$student_count = $this->db->query($sql)->result_array();
    				if ($student_count[0]['max_number'] == null || $student_count[0]['max_number'] == null)
    				{
    				    $ro = 1;
    				}
    				else
    				{
    				    $ro=$student_count[0]['max_number']+1;
    				}
    
    				$roll_no = $ro.'-'.$class->badge_no.'-'.$course->course_code.'-'.$campus->roll_no_code;
    				$this->db->set('roll_no',$roll_no);
    				$this->db->where('student_id',$update_request[0]['student_id']);
    				$this->db->update('students');
                }
            }
            
    		if($update_request[0]['status']==0)
    		{
    			$this->updateDeletedStudent($update_request[0]['student_id']);
    		}
    		
    		return 'success';
	}
	
	public function updateDeletedStudent($student_id)
	{
		$this->db->set('approve_by',$this->session->userdata('name'));
		$this->db->set('status',1);
		$this->db->where('student_id',$student_id);
		$this->db->update('deleted_students');
	}
	
	public function updateStudentRequest($student_id)
	{
		$this->db->set('ok_by_admin', '1');
		$this->db->where('student_id', $student_id);
		$this->db->update('update_student_requests');
	}
	
	private function applyOnlineApplicationCampusAccess($table = 'apply_now')
	{
		if ($this->session->userdata('role') == 'Admin') {
			return true;
		}

		$campus_ids = getUserOnlineApplicationCampusIds();
		if (empty($campus_ids)) {
			return false;
		}

		if ($table === 'apply_now') {
			$this->db->join(
				'campuses',
				"campuses.website = LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(apply_now.website, 'https://www.', ''), 'http://www.', ''), 'https://', ''), 'http://', ''), '/', ''))",
				'inner',
				false
			);
			$this->db->where_in('campuses.campus_id', $campus_ids);
		} else {
			$this->db->where_in('campus_id', $campus_ids);
		}

		return true;
	}

	private function joinLatestApplicationComment()
	{
		$this->db->join(
			'(SELECT c1.apply_now_id, c1.next_date_for_call, c1.add_by, c1.interest_type, c1.comment, c1.add_date_time
				FROM online_application_comments c1
				INNER JOIN (
					SELECT apply_now_id, MAX(online_application_comment_id) AS max_id
					FROM online_application_comments
					GROUP BY apply_now_id
				) c2 ON c1.online_application_comment_id = c2.max_id
			) AS latest_comment',
			'latest_comment.apply_now_id = apply_now.apply_now_id',
			'inner',
			false
		);
	}

	private function releaseExpiredPendingApplications()
	{
		$this->db->query("
			UPDATE apply_now
			INNER JOIN (
				SELECT c1.apply_now_id, c1.next_date_for_call
				FROM online_application_comments c1
				INNER JOIN (
					SELECT apply_now_id, MAX(online_application_comment_id) AS max_id
					FROM online_application_comments
					GROUP BY apply_now_id
				) c2 ON c1.online_application_comment_id = c2.max_id
			) latest_comment ON latest_comment.apply_now_id = apply_now.apply_now_id
			SET apply_now.pending_status = NULL
			WHERE apply_now.pending_status = 1
				AND apply_now.status = 0
				AND latest_comment.next_date_for_call != '0000-00-00'
				AND latest_comment.next_date_for_call IS NOT NULL
				AND latest_comment.next_date_for_call < DATE_SUB(CURDATE(), INTERVAL 7 DAY)
		");
	}

	public function getNewAdmisssions($campus_id=NULL){
		$this->releaseExpiredPendingApplications();

		if ($this->session->userdata('role') != 'Admin' && empty(getUserOnlineApplicationCampusIds())) {
			return array();
		}

		$this->db->select('apply_now.*');
		$this->db->from('apply_now');
		$this->applyOnlineApplicationCampusAccess('apply_now');

		if (@$campus_id != NULL) {
			if ($this->session->userdata('role') == 'Admin') {
				$this->db->join(
					'campuses',
					"campuses.website = LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(apply_now.website, 'https://www.', ''), 'http://www.', ''), 'https://', ''), 'http://', ''), '/', ''))",
					'inner',
					false
				);
			}
			$this->db->where('campuses.campus_id', $campus_id);
		}

		$this->db->where(array('apply_now.status'=>0,'apply_now.clear_by_admin'=>0,'apply_now.pending_status'=>NULL));
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getPendingAdmisssions($campus_id=NULL, $call_type='today')
	{
		$this->releaseExpiredPendingApplications();

		if ($this->session->userdata('role') != 'Admin' && empty(getUserOnlineApplicationCampusIds())) {
			return array();
		}

		$this->db->select('apply_now.*, latest_comment.next_date_for_call AS latest_next_date_for_call, latest_comment.add_by AS latest_comment_by');
		$this->db->from('apply_now');
		$this->joinLatestApplicationComment();
		$this->applyOnlineApplicationCampusAccess('apply_now');

		$this->db->where(array('apply_now.pending_status'=>1, 'apply_now.status'=>0));

		if ($this->session->userdata('role') != 'Admin') {
			$this->db->where('latest_comment.add_by', $this->session->userdata('name'));
		}

		if ($call_type == 'today') {
			$this->db->group_start();
			$this->db->where('latest_comment.next_date_for_call <=', date('Y-m-d'));
			$this->db->or_where('latest_comment.next_date_for_call', '0000-00-00');
			$this->db->group_end();
		} elseif ($call_type == 'future') {
			$this->db->where('latest_comment.next_date_for_call >', date('Y-m-d'));
			$this->db->where('latest_comment.next_date_for_call !=', '0000-00-00');
		}

		if (@$campus_id != NULL) {
			if ($this->session->userdata('role') == 'Admin') {
				$this->db->join(
					'campuses',
					"campuses.website = LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(apply_now.website, 'https://www.', ''), 'http://www.', ''), 'https://', ''), 'http://', ''), '/', ''))",
					'inner',
					false
				);
			}
			$this->db->where('campuses.campus_id', $campus_id);
		}

		$this->db->order_by('latest_comment.next_date_for_call', 'ASC');
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getNewClearAdmisssions()
	{
		$this->db->where(array('status'=>1,'clear_by_admin'=>0));
		$query = $this->db->get('apply_now')->result_array();
		return $query;
	}

    public function getNewMobileAdmisssions($campus_id=NULL)
    {
        if ($this->session->userdata('role') != 'Admin' && empty(getUserOnlineApplicationCampusIds())) {
            return array();
        }

        $this->applyOnlineApplicationCampusAccess('admission_applications');

        if (@$campus_id != NULL) {
            $this->db->where('campus_id', $campus_id);
        }

        $this->db->where(array('status != '=>3));
        $query = $this->db->get('admission_applications')->result_array();
        return $query;
    }

	public function getAllAdmisssions()
	{
		$this->db->where(array('status'=>1,'clear_by_admin'=>1));
		$query = $this->db->get('apply_now')->result_array();
		return $query;
	}
	
	public function getAllConfirmedAdmisssions()
	{
		$this->db->select('apply_now.*');
		$this->db->from('apply_now');
		$this->db->join('students', 'students.cnic=apply_now.cnic', 'INNER');
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getNewStudentEntries($campus_id=NULL)
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['campus_ids']);
		
		$this->db->select('campuses.campus_name,classes.name as class_name, students.*');
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
		
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campuses.campus_id', $campus_ids);
		}
		if($campus_id!=NULL)
		{
			$this->db->where('campuses.campus_id',$campus_id);
		}
		
		$this->db->where('students.clear_status', 0);
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getCampuses()
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['campus_ids']);
		
		$this->db->select('*');
		$this->db->from('campuses');
		
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campuses.campus_id', $campus_ids);
		}
		
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getNewExpenseEntries($campus_id=NULL)
	{
		
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['campus_ids']);
		
		$this->db->select('*');
		$this->db->from('expenses');
		$this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
		$this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
		
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campuses.campus_id', $campus_ids);
		}
		
		
		if($campus_id!=NULL)
		{
			$this->db->where('campuses.campus_id',$campus_id);
		}
		
		$this->db->where('expenses.clear_status = 0 and expenses.approved_status = 1');
		
		$query = $this->db->get()->result_array();
		return $query;
		
	}
	
	public function getFeeDuesComments($campus_id=NULL)
	{
		$access = checkUserAccess();
		$fee_recovery_class_ids = @explode(',',$access[0]['fee_recovery_class_ids']);
		
		$this->db->select('payments.id as fee_id,payments.amount, payments.dead_line, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee');
		$this->db->from('payments');
		$this->db->join('students', 'payments.student_id=students.student_id', 'inner');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
		if($campus_id!=NULL)
		{
			$this->db->where('campuses.campus_id',$campus_id);
		}
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('classes.class_id', $fee_recovery_class_ids);
		}
		$this->db->where(array('students.status'=> 1, 'payments.paid'=>'0', 'payments.dead_line<='=>date('Y-m-d', strtotime("+1 week"))));
		$this->db->order_by('students.roll_no', 'asc');
        $this->db->group_by('students.student_id');
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getContractFeeDuesComments($campus_id=NULL)
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['campus_ids']);
		
		$this->db->select('*,payments.id as fee_id');
		$this->db->from('payments');
		$this->db->join('contracts','contracts.contract_id=payments.contract_id','inner');
		$this->db->join('contractors','contracts.contractor_id=contractors.contractor_id','inner');
		$this->db->join('campuses','contracts.campus_id=campuses.campus_id','inner');
		if($campus_id!=NULL)
		{
			$this->db->where('campuses.campus_id',$campus_id);
		}
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campuses.campus_id', $campus_ids);
		}
		$this->db->where(array('payments.paid'=>'0', 'payments.dead_line<='=>date('Y-m-d', strtotime("+1 week"))));
        $this->db->group_by('contracts.contract_id');
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getFeeDuesContractorsCount($campus_id=NULL)
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['campus_ids']);
		
		$this->db->select('*,payments.id as fee_id');
		$this->db->from('payments');
		$this->db->join('contracts','contracts.contract_id=payments.contract_id','inner');
		$this->db->join('contractors','contracts.contractor_id=contractors.contractor_id','inner');
		$this->db->join('campuses','contracts.campus_id=campuses.campus_id','inner');
		if($campus_id!=NULL)
		{
			$this->db->where('campuses.campus_id',$campus_id);
		}
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campuses.campus_id', $campus_ids);
		}
		$this->db->where(array('payments.paid'=>'0', 'payments.dead_line<='=>date('Y-m-d', strtotime("+1 week"))));
		$this->db->group_by('contractors.contractor_id');
		$query = $this->db->get()->result_array();
		return count($query);
	}
	
	public function getFeeDuesStudentsCount($campus_id=NULL)
	{
		$access = checkUserAccess();
		$fee_recovery_class_ids = @explode(',',$access[0]['fee_recovery_class_ids']);
		
		$this->db->select('payments.id as fee_id,payments.amount, payments.dead_line, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name');
		$this->db->from('payments');
		$this->db->join('students', 'payments.student_id=students.student_id', 'inner');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
		if($campus_id!=NULL)
		{
			$this->db->where('campuses.campus_id',$campus_id);
		}
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('classes.class_id', $fee_recovery_class_ids);
		}
		$this->db->where(array('students.status'=> 1, 'payments.paid'=>'0', 'payments.dead_line<='=>date('Y-m-d', strtotime("+1 week"))));
		$this->db->order_by('students.roll_no', 'asc');
		$this->db->group_by('students.student_id');
		$query = $this->db->get()->result_array();
		return count($query);
	}
	
	public function getFeeDuesClearComments($campus_id=NULL)
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['fee_dues_campus_ids']);
		
		$this->db->select('payments.id as fee_id,payments.amount, payments.dead_line, payments.extra_amount, students.student_id, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name');
		$this->db->from('payments');
		$this->db->join('fees_remarks', 'fees_remarks.fee_id=payments.id', 'inner');
		$this->db->join('students', 'payments.student_id=students.student_id', 'inner');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
		if($campus_id!=NULL)
		{
			$this->db->where('campuses.campus_id',$campus_id);
		}
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campuses.campus_id', $campus_ids);
		}
		$this->db->where(array('fees_remarks.clear_status'=>0, 'students.status'=> 1, 'payments.paid'=>'0', 'payments.dead_line<='=>date('Y-m-d', strtotime("+1 week"))));
		$this->db->order_by('students.roll_no', 'asc');
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getFeeDuesContractorsClearComments($campus_id=NULL)
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['fee_dues_campus_ids']);
		
		$this->db->select('payments.id as fee_id,payments.amount, payments.dead_line, payments.extra_amount,campuses.campus_name,contractors.*,contracts.*');
		$this->db->from('payments');
		$this->db->join('fees_remarks', 'fees_remarks.fee_id=payments.id', 'inner');
		$this->db->join('contracts', 'payments.contract_id=contracts.contract_id', 'inner');
		$this->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'inner');
		$this->db->join('campuses', 'contracts.campus_id=campuses.campus_id', 'inner');
		if($campus_id!=NULL)
		{
			$this->db->where('campuses.campus_id',$campus_id);
		}
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campuses.campus_id', $campus_ids);
		}
		$this->db->where(array('fees_remarks.clear_status'=>0, 'payments.paid'=>'0', 'payments.dead_line<='=>date('Y-m-d', strtotime("+1 week"))));
		$this->db->order_by('contractors.contractor_id_from_college', 'asc');
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getReminders()
	{
		$this->db->select('*');
		$this->db->from('reminder');
		$this->db->where('user_id',$this->session->userdata('user_id'));
		$this->db->where('check_by_admin',0);
		$this->db->where('date<=',date('Y-m-d'));
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getPendingReminders()
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['fee_dues_campus_ids']);
		
		$this->db->select('*');
		$this->db->from('reminder');
		$this->db->join('users','users.user_id=reminder.user_id','INNER');
		$this->db->join('campuses','users.campus_id=campuses.campus_id','INNER');
		$this->db->where('reminder.status','Pending');
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campuses.campus_id', $campus_ids);
		}
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getUnderReviewReminders()
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['fee_dues_campus_ids']);
		
		$this->db->select('*');
		$this->db->from('reminder');
		$this->db->join('users','users.user_id=reminder.user_id','INNER');
		$this->db->join('campuses','users.campus_id=campuses.campus_id','INNER');
		$this->db->where(array('reminder.status'=>'Completed','reminder.check_by_admin'=>0));
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campuses.campus_id', $campus_ids);
		}
		$query = $this->db->get()->result_array();
		
		//$query = $this->db->get_where('reminder',array('status'=>'Completed','check_by_admin'=>0))->result_array();
		return $query;
	}
	
	public function getUnclearProducts($campus_id=NULL)
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['campus_ids']);
		
		$this->db->select('*');
		$this->db->from('products');
		$this->db->join('product_names','products.product_name_id=product_names.product_name_id','left');
		$this->db->join('campuses','products.campus_id=campuses.campus_id','left');
		if(@$campus_id!=NULL)
		{
			$this->db->where('campuses.campus_id',$campus_id);
		}
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campuses.campus_id', $campus_ids);
		}
		$this->db->where('products.status',0);
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getUpdatedProducts($campus_id=NULL)
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['campus_ids']);
		
		$this->db->select('*');
		$this->db->from('update_product_requests');
		$this->db->join('product_names','update_product_requests.product_name_id=product_names.product_name_id','left');
		$this->db->join('campuses','update_product_requests.campus_id=campuses.campus_id','left');
		if(@$campus_id!=NULL)
		{
			$this->db->where('campuses.campus_id',$campus_id);
		}
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campuses.campus_id', $campus_ids);
		}
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getUnclearDocuments($campus_id=NULL)
	{
		$access = checkUserAccess();
		$campus_ids = @explode(',',$access[0]['campus_ids']);
		
		$this->db->select('*');
		$this->db->from('documents');
		$this->db->join('document_names','documents.document_id=document_names.document_name_id','left');
		$this->db->join('campuses','documents.campus_id=campuses.campus_id','left');
		if(@$campus_id!=NULL)
		{
			$this->db->where('campuses.campus_id',$campus_id);
		}
		if($this->session->userdata('role')!='Admin')
		{
			$this->db->where_in('campuses.campus_id', $campus_ids);
		}
		$this->db->where('documents.status',0);
		$query = $this->db->get()->result_array();
		return $query;
	}

	public function getUncheckAssignments()
	{
		$access = checkUserAccess();
		$subject_ids = @explode(',',$access[0]['assignment_subject_ids']);

		$this->db->select('*');
		$this->db->from('assignments');
		$this->db->join('assignment_results','assignment_results.assignment_id=assignments.assignment_id','inner');
		if($this->session->userdata('role')!='Admin'){
			$this->db->where_in('assignments.subject_id', $subject_ids);
		}
		$this->db->where('assignment_results.checked',0);
		$query = $this->db->get()->result_array();
		return $query;
	}

	public function getMyLectures()
	{
		$this->db->select('lectures.id as lecture_id, lectures.session, lectures.studytype, lectures.room, lectures.teacher, lectures.subjects, lectures.shift, lectures.class, lectures.lecture_name, lectures.start_date, lectures.end_date, session_syllabus.date, session_syllabus.topic_ids, session_syllabus.practical_ids, session_syllabus.is_quiz, session_syllabus.is_half, session_syllabus.id as session_syllabus_id, courses.course_name, campuses.campus_name, course_subjects.subject_name');
		$this->db->from('session_syllabus');
		$this->db->join('lectures','session_syllabus.lecture_id=lectures.id','inner');
		$this->db->join('courses','courses.course_id=lectures.course','inner');
		$this->db->join('campuses','campuses.campus_id=lectures.campus','inner');
		$this->db->join('course_subjects','course_subjects.course_subject_id=session_syllabus.subject_id','inner');
		$this->db->where(array('lectures.teacher'=>$this->session->userdata('user_id'),'session_syllabus.date<='=>date('Y-m-d',strtotime("+5 day", strtotime(date('Y-m-d'))))));
		$this->db->group_by('session_syllabus.date');
		$query = $this->db->get()->result_array();
		
		return $query;
	}
}
?>