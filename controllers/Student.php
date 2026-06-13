<?php
class Student extends CI_Model {
	
	public function storeStudent($data)
	{
		foreach(@$data as $k=>$value){
			$this->db->set(''.$k.'', $value);
		}
		$this->db->insert('students');
		$insert_id = $this->db->insert_id();

		return  $insert_id;
	}
	
	public function updateStudent($data)
	{
		foreach(@$data as $k=>$value){
			$this->db->set(''.$k.'', $value);
		}
		$this->db->set('student_id', $this->uri->segment(3));
		$this->db->insert('update_student_requests');
		
	}
	
	public function updateDeleteStudent($data,$student_id)
	{
		foreach(@$data as $k=>$value){
			$this->db->set(''.$k.'', $value);
		}
		$this->db->set('student_id', $student_id);
		$this->db->insert('update_student_requests');
		
	}
	
	public function deleteStudent($id)
	{
		$this->db->set('status', '0');
		$this->db->where('student_id', $id);
		$this->db->update('students');
		
	}
	
	public function getStudents()
	{
		$access = checkUserAccess();
		$class_ids = @explode(',',$access[0]['class_ids']);
		
			if(@$this->input->post('search_type')=='councilwise')
		{
			$this->db->select('students.*,classes.name as class_name,classes.session as session, campuses.campus_name, courses.course_name,machine_data.machine_id');
			$this->db->from('students');
			$this->db->join('payments', 'payments.custom_student_id=students.student_id', 'left');
			$this->db->join('classes', 'classes.class_id=students.class_id', 'left');
			$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
			$this->db->join('courses', 'courses.course_id=students.course_id', 'left');
			$this->db->join('machine_data', 'machine_data.teacher_student_id=students.student_id', 'left');
			$this->db->like('payments.payment_comment', 'This fee for next exam # '.$this->input->post('council_exam_no'), 'both');
			$this->db->where(array('students.status'=>'1','payments.paid'=>'1'));
			
			if(@$this->input->post('campus_id'))
			{	
				$this->db->where('classes.campus_id', $this->input->post('campus_id'));
			}
			if(@$this->input->post('course_id'))
			{
				$this->db->where('courses.course_id', $this->input->post('course_id'));
			}
			
			if(@$this->input->post('class_id'))
			{
				$this->db->where('classes.class_id', $this->input->post('class_id'));
			}
			
			
			if($this->session->userdata('role')!='Admin'){
				$this->db->where_in('students.class_id', $class_ids);
			}
			$this->db->order_by('students.roll_no', 'asc');
			$query = $this->db->get()->result_array();
			return $query;
		}
		else
		{		
			$this->db->select('students.*,classes.name as class_name,,classes.session as session ,campuses.campus_name, courses.course_name,machine_data.machine_id');
			$this->db->from('students');
			$this->db->join('classes', 'classes.class_id=students.class_id', 'left');
			$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
			$this->db->join('courses', 'courses.course_id=students.course_id', 'left');
			$this->db->join('payments', 'payments.student_id = students.student_id', 'left');
			$this->db->join('machine_data', 'machine_data.teacher_student_id=students.student_id', 'left');
			$this->db->where(array('students.status'=>'1'));
			
			if(@$this->input->post('campus_id'))
			{	
				$this->db->where('classes.campus_id', $this->input->post('campus_id'));
			}
			if(@$this->input->post('course_id'))
			{
				$this->db->where('courses.course_id', $this->input->post('course_id'));
			}
			
			if(@$this->input->post('class_id'))
			{
				$this->db->where('classes.class_id', $this->input->post('class_id'));
			}

            if(@$this->input->post('type')== 'blacklisted')
            {
              $this->db->where("(payments.paid = '0' and payments.dead_line < now())", NULL, FALSE);
            }

			
			if($this->session->userdata('role')!='Admin'){
				$this->db->where_in('students.class_id', $class_ids);
			}
            $this->db->group_by('students.student_id', 'asc');
			$this->db->order_by('students.roll_no', 'asc');
			$query = $this->db->get()->result_array();
			return $query;
		}
		
		
	}
	
	
	
	public function editStudent($id)
	{
		$this->db->select('students.*, campuses.campus_id');
		$this->db->from('students');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'left');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
		$this->db->where('students.student_id', $id);
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getStudentsCount()
	{
		$this->db->select('*');
		$this->db->from('students');
		$this->db->where(array('status'=>'1'));
		$query = $this->db->count_all_results();
		return $query;
	}
	
	public function getNewStudentsCount()
	{
		$start_date = date('Y-m-01');
		$end_date = date('Y-m-t');
		
		$this->db->select('*');
		$this->db->from('students');
		$this->db->where(array('status'=>'1', 'registration_date>='=>$start_date, 'registration_date<='=>$end_date));
		$query = $this->db->count_all_results();
		return $query;
	}
	
	public function checkStudentNIC($cnic,$course_id)
	{
		$this->db->select('*');
		$this->db->from('students');
		$this->db->join('classes','classes.class_id=students.class_id','inner');
		$this->db->join('courses','courses.course_id=classes.course_id','inner');
		$this->db->where(array('students.cnic'=>$cnic,'courses.course_id'=>$course_id));
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function uploadDocument($id, $student_document, $type)
	{
		$this->db->set('student_id', $id);
		$this->db->set('image', $student_document);
		$this->db->set('type', $type);
		$this->db->insert('student_documents');
	}
	
	public function uploadedDocuments($id)
	{
		$this->db->select('*');
		$this->db->from('student_documents');
		$this->db->where('student_id', $id);
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function payment_paid($id)
	{
		$this->db->select('*');
		$this->db->from('payments');
		$this->db->where('student_id', $id);
		$this->db->where('merged_challan IS NOT NULL and actual_amount > 0');
		$this->db->group_by("CASE WHEN merged_challan IS NOT NULL THEN merged_challan else '' end",false);
		$query = $this->db->get()->result_array();

        $this->db->select('*');
        $this->db->from('payments');
        $this->db->where('student_id', $id);
        $this->db->where('merged_challan is null');
		$this->db->or_where(' student_id = "'.$id.'" and merged_challan IS not NULL and actual_amount = 0');
        $query2 = $this->db->get()->result_array();
		
		$arr=array_merge($query,$query2);
        
        function date_compare($a, $b)
        {
            $t1 = strtotime($a['dead_line']);
            $t2 = strtotime($b['dead_line']);
            return $t1 - $t2;
        }

		usort($arr, 'date_compare');

        return $arr;


	}
	
	public function contractor_payment_paid($contract_id)
	{
				
		$this->db->select('*');
		$this->db->from('payments');
		$this->db->where('contract_id', $contract_id);
		$this->db->where('merged_challan IS NOT NULL and actual_amount > 0');
		$this->db->group_by("CASE WHEN merged_challan IS NOT NULL THEN merged_challan else '' end",false);
		$query = $this->db->get()->result_array();

        $this->db->select('*');
        $this->db->from('payments');
        $this->db->where('contract_id', $contract_id);
        $this->db->where('merged_challan is null');
		$this->db->or_where(' contract_id = "'.$contract_id.'" and merged_challan IS not NULL and actual_amount = 0');
        $query2 = $this->db->get()->result_array();
		
		$arr=array_merge($query,$query2);
        
        function date_compare($a, $b)
        {
            $t1 = strtotime($a['dead_line']);
            $t2 = strtotime($b['dead_line']);
            return $t1 - $t2;
        }

		usort($arr, 'date_compare');

        return $arr;
		
		
		
		
		
		
	}
	
	public function saveInstallment($data)
	{
		/*$this->db->set('scan_challan', $data['scan_challan']);
		$this->db->set('fine_application', $data['fine_application']);
		$this->db->set('actual_amount', $data['actual_amount']);
		$this->db->set('paid_date', $data['paid_date']);
		$this->db->set('actual_paid_date', $data['actual_paid_date']);
		$this->db->set('paid', $data['paid']);
		$this->db->set('college_fee', $data['college_fee']);
		$this->db->set('last_edit', $this->session->userdata('name'));*/
		
		foreach(@$data as $k=>$value){
			if($k!='id')
			{
				$this->db->set(''.$k.'', $value);
			}
		}
		
		$this->db->where('id', $data['id']);
		$this->db->update('payments');
	}
	
	public function challan($challan_id)
	{


		$this->db->select('payments.*, students.first_name as first_name, students.last_name as last_name, students.roll_no as roll_no, students.father_name as father_name, classes.name as class_name, campuses.bank_name, campuses.account_no, campuses.address, campuses.campus_name, campuses.note, campuses.campus_id');
		$this->db->from('payments');
		$this->db->join('students', 'payments.student_id=students.student_id', 'inner');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
		$this->db->where_in('payments.id', explode(',', $challan_id));
		$query = $this->db->get()->result_array();


		return $query;

	}
	
	public function contractor_challan($challan_id)
	{
		$this->db->select('payments.*, contractors.name as contractor_name, contracts.contract_name, campuses.campus_name, campuses.address, campuses.bank_name, campuses.account_no, campuses.note');
		$this->db->from('payments');
		$this->db->join('contracts', 'payments.contract_id=contracts.contract_id', 'inner');
		$this->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'inner');
		$this->db->join('campuses','contracts.campus_id=campuses.campus_id','LEFT');
		$this->db->where('payments.id', $challan_id);
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getSingleStudent($id)
	{
		$this->db->select('*');
		$this->db->from('students');
        $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
		$this->db->where('student_id', $id);
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getSingleContract($contract_id)
	{
		$this->db->select('*');
		$this->db->from('contracts');
		$this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','inner');
		$this->db->where('contracts.contract_id', $contract_id);
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function getStudentDiscount($id)
	{
		$qry = "SELECT SUM(amount) as total_paid FROM `payments` WHERE student_id=$id AND payment_plan!='consulation fee' AND payment_comment='College Fee'";
		$query = $this->db->query($qry)->result_array();
		return $query;
	}
	
	public function getStudentPaidFee($id)
	{
		$qry = "SELECT SUM(amount) as paid_fee FROM `payments` WHERE student_id=$id AND payment_plan!='consulation fee' AND payment_comment='College Fee' AND paid=1";
		$query = $this->db->query($qry)->result_array();
		return $query;
	}

	public function getStudentTotalExtraFee($id)
    {
        $qry = "SELECT SUM(amount) as total_extra_fee FROM `payments` WHERE student_id=$id AND payment_plan!='consulation fee' AND payment_comment!='College Fee'";
        $query = $this->db->query($qry)->result_array();
        return $query[0]['total_extra_fee'];
    }

    public function getStudentTotalExtraPaidFee($id)
    {
        $qry = "SELECT SUM(amount) as total_paid_extra_fee FROM `payments` WHERE student_id=$id AND payment_plan!='consulation fee' AND payment_comment!='College Fee' AND paid=1";
        $query = $this->db->query($qry)->result_array();
        return $query[0]['total_paid_extra_fee'];
    }

    public function getStudentExtraFeePaidTillDate($id)
    {
        $qry = "SELECT SUM(amount) as extra_fee_paid_till_date FROM `payments` WHERE student_id=$id AND payment_plan!='consulation fee' AND payment_comment!='College Fee' AND paid=1 AND dead_line<= '".date('Y-m-d')."'";
        $query = $this->db->query($qry)->result_array();
        return $query[0]['extra_fee_paid_till_date'];
    }

    public function getStudentExtraFeeRemainingTillDate($id)
    {
        $qry = "SELECT SUM(amount) as extra_fee_remaining_till_date FROM `payments` WHERE student_id=$id AND payment_plan!='consulation fee' AND payment_comment!='College Fee' AND paid=0 AND dead_line<= '".date('Y-m-d')."'";
        $query = $this->db->query($qry)->result_array();
        return $query[0]['extra_fee_remaining_till_date'];
    }

    public function getStudentShiftDeleteFee($id)
    {
        $payments = $this->db->get_where('payments',array('student_id'=>$id))->result_array();
        $payments_ids = array();
        foreach($payments as $payment)
        {
            array_push($payments_ids,$payment['id']);
        }
        $this->db->select('*');
        $this->db->from('update_payment_requests');
        $this->db->where('ok_by_admin',1);
        $this->db->where_in('id',$payments_ids);
        $this->db->group_by('id');
        $update_delete_payment_requests = $this->db->get()->result_array();
        return count($update_delete_payment_requests);
    }
	
	public function getStudentRemainingFee($id)
	{
		//$qry = "SELECT SUM(amount)-SUM(actual_amount) as remaining_fee FROM `payments` where student_id=$id AND payment_plan!='consulation fee'";
		//$query = $this->db->query($qry)->result_array();
		//return $query;
        //ALL PAYMENTS
        $this->db->select_sum('amount');
        $this->db->from('payments');
        $this->db->where(array('student_id'=>$id,'payment_plan!='=>'consulation fee'));
        $all_payments = $this->db->get()->result_array();

        //ALL PAID PAYMENTS
        $this->db->select_sum('amount');
        $this->db->from('payments');
        $this->db->where(array('student_id'=>$id,'payment_plan!='=>'consulation fee','paid'=>1));
        $all_paid_payments = $this->db->get()->result_array();

        $remaining_fee = $all_payments[0]['amount']-$all_paid_payments[0]['amount'];
        return $remaining_fee;
	}		
	
	public function getStudentFeeShouldPay($id)
	{
		$qry = "SELECT SUM(amount) as fee_should_pay FROM `payments` where student_id=$id AND dead_line<= '".date('Y-m-d')."' AND payment_plan!='consulation fee' AND payment_comment='College Fee'";
		$query = $this->db->query($qry)->result_array();
		return $query;
	}
	
	
	public function getNextPaymentId($payment_id, $student_id)
	{
		$qry = "SELECT * FROM payments where id!=$payment_id and paid=0 and student_id=$student_id AND payment_plan!='consulation fee' ORDER BY dead_line ASC LIMIT 1";
		$query = $this->db->query($qry)->result_array();
		return $query;
	}
	
	public function getNextPaymentIdContractor($payment_id, $contract_id)
	{
		$qry = "SELECT * FROM payments where id>$payment_id and contract_id=$contract_id AND payment_plan!='consulation fee' LIMIT 1";
		$query = $this->db->query($qry)->result_array();
		return $query;
	}
	
	public function getStudentConsulationFee($id)
	{
		$qry = "SELECT SUM(amount) as consulation_fee FROM `payments` WHERE student_id=$id AND payment_plan='consulation fee'";
		$query = $this->db->query($qry)->result_array();
		return $query;
	}
	
	public function getStudentConsulationFeePaid($id)
	{
		$qry = "SELECT SUM(amount) as consulation_fee_paid FROM `payments` WHERE student_id=$id AND payment_plan='consulation fee' AND paid=1";
		$query = $this->db->query($qry)->result_array();
		return $query;
	}
	
	public function getStudentConsulationFeeUnPaid($id)
	{
		$qry = "SELECT SUM(amount) as consulation_fee_unpaid FROM `payments` WHERE student_id=$id AND payment_plan='consulation fee' AND paid=0";
		$query = $this->db->query($qry)->result_array();
		return $query;
	}
	
	/*public function getStudentTotalFine($id)
	{
		$qry = "SELECT SUM(extra_amount) as fine_fee FROM `payments` WHERE student_id=$id";
		$query = $this->db->query($qry)->result_array();
		return $query;
	}*/
	
	public function getStudentConsulationFeeShouldPay($id)
	{
		$qry = "SELECT SUM(amount) as consulation_fee_should_pay FROM `payments` where student_id=$id AND dead_line<= '".date('Y-m-d')."' AND payment_plan='consulation fee'";
		$query = $this->db->query($qry)->result_array();
		return $query;
	}
	
	public function addExtraChargesToNextInstallment($next_payment_id, $shift_fine_amount, $shift_installment_amount,$current_installment_challan)
	{
		$message = 'Amount '.($shift_installment_amount).' of fee challan # '.$current_installment_challan.' shifted in this installment.<br /><hr />Fine Amount '.($shift_fine_amount).' of fee challan # '.$current_installment_challan.' shifted in this installment.';
		
		$this->db->set('system_comment', $message);
		$this->db->set('remaining_installment_amount', $shift_installment_amount);
		$this->db->set('extra_amount', $shift_fine_amount);
		$this->db->where('id', $next_payment_id);
		$this->db->update('payments');
	}

    public function addExtraChargesToNewInstallment($student_id,$new_dead_line,$new_installment,$new_previous_fine,$new_fine,$current_installment_challan)
    {
        $message = 'Amount '.($new_installment).' of fee challan # '.$current_installment_challan.' added in this installment.<br /><hr />Fine Amount '.($new_previous_fine+$new_fine).' of fee challan # '.$current_installment_challan.' added in this installment.';

        $dead_line = $new_dead_line;
        $challan_no = $this->getChallanNo();

        $this->db->set('amount', 0);
        $this->db->set('system_comment', $message);
        $this->db->set('remaining_installment_amount', $new_installment);
        $this->db->set('extra_amount', $new_previous_fine+$new_fine);
        $this->db->set('dead_line', $dead_line);
        $this->db->set('student_id', $student_id);
        $this->db->set('payment_plan', 'Custom Plan');
        $this->db->set('payment_comment', 'College Fee');
        $this->db->set('challan_no', $challan_no);
        $this->db->set('add_by', $this->session->userdata('name'));
        $this->db->set('last_edit', $this->session->userdata('name'));
        $this->db->insert('payments');
    }
	
	public function getCompleteContractAmount($contract_id)
	{
		$qry = "SELECT SUM(amount) as total_contract_amount FROM `payments` where contract_id=$contract_id";
		$query = $this->db->query($qry)->result_array();
		return $query;
	}
	
	public function getContractFeeShouldPay($contract_id)
	{
		$qry = "SELECT SUM(amount) as fee_should_pay FROM `payments` where contract_id=$contract_id AND dead_line<= '".date('Y-m-d')."'";
		$query = $this->db->query($qry)->result_array();
		return $query;
	}
	
	public function getContractPaidFee($contract_id)
	{
		$qry = "SELECT SUM(actual_amount) as paid_fee FROM `payments` WHERE contract_id=$contract_id";
		$query = $this->db->query($qry)->result_array();
		return $query;
	}
	
	public function getContractRemainingFee($contract_id)
	{
		$qry = "SELECT SUM(amount)-SUM(actual_amount) as remaining_fee FROM `payments` where contract_id=$contract_id";
		$query = $this->db->query($qry)->result_array();
		return $query;
	}
	
	public function getContractStudents($contract_id)
	{
		$qry = "SELECT COUNT(student_id) as no_of_students FROM `students` WHERE contract_id=$contract_id and status=1";
		$query = $this->db->query($qry)->result_array();
		return $query;
	}
	
	public function editPayment($payment_id)
	{
		$this->db->select('*');
		$this->db->from('payments');
		//$this->db->join('students', 'payments.student_id=students.student_id', 'inner');
		$this->db->where('payments.id',$payment_id);
		$query = $this->db->get()->result_array();
		return $query;
	}
	
	public function updateEditPayment($data, $scan_challan)
	{
		$payment_id = $this->db->get_where('payments',array('id'=>$data['id']))->result_array();

		$date1=date_create($data['dead_line']);
        $date2=date_create($payment_id[0]['dead_line']);
        $diff=date_diff($date1,$date2)->days;

        $already_requested = $this->db->get_where('update_payment_requests',array('id'=>$data['id']))->result_array();

		if
        (
            $payment_id[0]['amount']==$data['amount']
            &&
            $payment_id[0]['actual_amount']==$data['actual_amount']
            &&
            $payment_id[0]['remaining_installment_amount']==$data['remaining_installment_amount']
            &&
            $payment_id[0]['paid']==$data['paid']
            &&
            $payment_id[0]['remaining_installment_amount']==$data['remaining_installment_amount']
            &&
            $payment_id[0]['paid_date']==$data['paid_date']
            &&
            $diff<32
            &&
            count($already_requested)<1
        )
        {
            //INSERT BACKEND RECORD
            foreach(@$data as $k=>$value){
                if($k!='new_scan_challan' || $k!='msg')
                {
                    $this->db->set(''.$k.'', $value);
                }
            }
            if($scan_challan!='')
            {
                $this->db->set('scan_challan', $scan_challan);
            }
            $this->db->set('clear_by','System');
            $this->db->set('ok_by_admin',1);
            $this->db->insert('update_payment_requests');

            //UPDATE PAYMENT WITHOUT VERIFICATION
            foreach(@$data as $k=>$value){
                if($k!='new_scan_challan' && $k!='msg' && $k!='reason' && $k!='old_dead_line' && $k!='reason' && $k!='id' && $k!='del')
                {
                    $this->db->set(''.$k.'', $value);
                }
            }
            if($scan_challan!='')
            {
                $this->db->set('scan_challan', $scan_challan);
            }
            $this->db->where('id',$data['id']);
            $this->db->update('payments');
        }
		else
        {
            foreach(@$data as $k=>$value){
                if($k!='new_scan_challan' || $k!='msg')
                {
                    $this->db->set(''.$k.'', $value);
                }
            }
            if($scan_challan!='')
            {
                $this->db->set('scan_challan', $scan_challan);
            }
            $this->db->insert('update_payment_requests');
        }
	}
	
	public function deleteDocument($photo_id)
	{
		$this->db->where('id', $photo_id);
		$this->db->delete('student_documents');
	}
	public function getSms($mobile)
	{
		//$this->db->select('*');
		//$this->db->from('sms');
		//$this->db->like('number', $mobile);
		$sql = 'SELECT * FROM sms where number='.$mobile;
		echo $sql;
		$query = $this->db->query($sql)->result_array();
		return $query;
	}
	public function checkChallanRequest($id)
	{
		$query = $this->db->get_where('update_payment_requests', array('id'=>$id, 'ok_by_admin'=>0))->result_array();
		return $query;
	}
	public function getCourses()
	{
		$query = $this->db->get('courses')->result_array();
		return $query;
	}
    public function getChallanNo()
    {
        $random_number = rand(1000, 999999999);
        $check_challan_no = $this->db->get_where('payments', array('challan_no'=>$random_number))->result_array();
        if(count($check_challan_no)>0)
        {
            $random_number = $this->getChallanNo();
        }
        else
        {
            return $random_number;
        }
    }

    public function getStudentTotalFine($student_id)
    {
        $this->db->select('*');
		$this->db->from('payments');
		$this->db->where('student_id', $student_id);
		$this->db->where('merged_challan IS NOT NULL and actual_amount > 0');
		$this->db->group_by("CASE WHEN merged_challan IS NOT NULL THEN merged_challan end",false);
		$query = $this->db->get()->result_array();

        $this->db->select('*');
        $this->db->from('payments');
        $this->db->where('student_id', $student_id);
        $this->db->where('merged_challan is null');
		$this->db->or_where(' student_id = "'.$student_id.'" and merged_challan IS not NULL and actual_amount = 0');
        $query2 = $this->db->get()->result_array();

		$payments= array_merge($query,$query2);
        $fine=0;
        foreach($payments as $payment)
        {
            $fine+=$payment['extra_amount'];
            if($payment['paid']==1)
            {
				$date_now = date("Y-m-d"); // this format is string comparable

						if ($date_now > '2021-02-15') {
							$fine+=$payment['fine_amount'];
						}else{
							$fine+=$payment['actual_amount']-$payment['amount'];
						}
                
            }
            else
            {
                $challan_date = date_create($payment['dead_line']);
                $today_date = date_create(date('Y-m-d'));
                $diff=date_diff($challan_date,$today_date);
                $difference = $diff->format("%R%a");

                if($difference>0)
                {
                    $fee_fine = $difference*50;
                }
                else
                {
                    $fee_fine = 0;
                }
                $fine+=$fee_fine;
            }
        }
        return $fine;
    }

    
	
	
	public function getStudentRemovedFine($student_id)
    {
        $this->db->select('*');
		$this->db->from('payments');
		$this->db->where('student_id', $student_id);
		$this->db->where('merged_challan IS NOT NULL and actual_amount > 0');
		$this->db->group_by("CASE WHEN merged_challan IS NOT NULL THEN merged_challan end",false);
		$query = $this->db->get()->result_array();

        $this->db->select('*');
        $this->db->from('payments');
        $this->db->where('student_id', $student_id);
        $this->db->where('merged_challan is null');
		$this->db->or_where(' student_id = "'.$student_id.'" and merged_challan IS not NULL and actual_amount = 0');
        $query2 = $this->db->get()->result_array();

		$payments= array_merge($query,$query2);
        $fine=0;
        foreach($payments as $payment)
        {
            $fine+=$payment['extra_amount'];
            if($payment['paid']==1)
            {
				$date_now = date("Y-m-d"); // this format is string comparable

						if ($date_now > '2021-02-15') {
							$fine+=$payment['removed_fine'];
						}else{
							$fine+=0;
						}
                
            }
            
        }
        return $fine;
    }

    
	
	
	
	
	
	public function getStudentFineShouldPay($student_id)
    {
        $this->db->select('*');
		$this->db->from('payments');
		$this->db->where('student_id', $student_id);
		$this->db->where('merged_challan IS NOT NULL and actual_amount > 0');
		$this->db->group_by("CASE WHEN merged_challan IS NOT NULL THEN merged_challan end",false);
		$query = $this->db->get()->result_array();

        $this->db->select('*');
        $this->db->from('payments');
        $this->db->where('student_id', $student_id);
        $this->db->where('merged_challan is null');
		$this->db->or_where(' student_id = "'.$student_id.'" and merged_challan IS not NULL and actual_amount = 0');
        $query2 = $this->db->get()->result_array();

		$payments= array_merge($query,$query2);
        $fine=0;
        foreach($payments as $payment)
        {
            $fine+=$payment['extra_amount'];
            if($payment['paid']==1)
            {
				$date_now = date("Y-m-d"); // this format is string comparable

						if ($date_now > '2021-02-15') {
							$fine+=$payment['fine_amount'];
						}else{
							$fine+=$payment['actual_amount']-$payment['amount'];
						}
                
            }
            else
            {
                $challan_date = date_create($payment['dead_line']);
                $today_date = date_create(date('Y-m-d'));
                $diff=date_diff($challan_date,$today_date);
                $difference = $diff->format("%R%a");

                if($difference>0)
                {
                    $fee_fine = $difference*50;
                }
                else
                {
                    $fee_fine = 0;
                }
                $fine+=$fee_fine;
            }
        }
        return $fine;
    }

    public function getStudentFinePaid($student_id)
    {
        $payments = $this->db->get_where('payments',array('student_id'=>$student_id,'paid'=>1))->result_array();
        $fine=0;
        foreach($payments as $payment)
        {
			$date_now = date("Y-m-d"); // this format is string comparable

						if ($date_now > '2021-02-15') {
							$fine+=$payment['fine_amount'];
						}else{
							$fine+=$payment['actual_amount']-$payment['amount'];
						}
                
            
        }
        return $fine;
    }

    
	
	public function getStudentTotalCalls($student_id)
    {
        $payments = $this->db->get_where('payments',array('student_id'=>$student_id))->result_array();
        $payment_ids=array();
        foreach($payments as $payment)
        {
            array_push($payment_ids,$payment['id']);
        }

        $this->db->where_in('fee_id',$payment_ids);
        $calls = $this->db->get('fees_remarks')->result_array();
        return count($calls);
    }
	
	public function verify_fee()
    {

        $tid=$this->input->post('tid');
        $bank=$this->input->post('bank');

        $tid=str_replace(' ', '', $tid);
        $tid=preg_replace('/\s+/', '', $tid);
        $tid=trim($tid);


        //GET DEVICE AND TOKEN
        $this->db->select('*');
        $this->db->from('payments');
        $this->db->where('bank_details like "%'.$bank.'%" and tid_no like "%'.$tid.'%"');
        $device_details = $this->db->get()->result_array();

        if (count($device_details) < 1) {

            echo 'success';

        }
        else
        {

            echo 'Already Found '. $device_details[0]['challan_no'] .'<br />'.
                '<a href="'.site_url().'/students/payments_paid/'.$device_details[0]['student_id'].'" target="_blank" class="btn red"></i> See Data</a> <br />';;

        }

    }
	
	
}
?>