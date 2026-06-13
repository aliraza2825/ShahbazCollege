<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Excel_import extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('excel_import_model');
		$this->load->library('excel');
        $this->load->library('PHPExcel', NULL, 'excel');
	}

	function index()
    {
        $from_date = @$this->input->post('from_date');
        $to_date   = @$this->input->post('to_date');

        if ($from_date) {
            $data['settlements'] = $this->db->select("pay_pro_settlement.*,CONCAT(users.first_name,' ',users.last_name) as created_by")
                ->join("users", "users.user_id = pay_pro_settlement.created_by", "left")
                ->get_where("pay_pro_settlement", array("pay_pro_settlement.settlement_date >=" => $from_date, "pay_pro_settlement.settlement_date <=" => $to_date))->result_array();
            $data['from_date'] = $from_date;
            $data['to_date'] = $to_date;
        }else{
            $data['settlements'] = $this->db->select("pay_pro_settlement.*,CONCAT(users.first_name,' ',users.last_name) as created_by")
                ->join("users","users.user_id = pay_pro_settlement.created_by","left")->order_by("pay_pro_settlement.settlement_date","DESC")->limit(5)->get("pay_pro_settlement")->result_array();
            $data['from_date'] = date("Y-m-d");
            $data['to_date'] = date("Y-m-d");
        }


        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('pay_pro/excel_import',$data);
        $this->load->view('inc/footer');
    }
	
	function fetch()
	{
		$data = $this->excel_import_model->select();
		$output = '
		<h3 align="center">Total Data - '.$data->num_rows().'</h3>
		<table class="table table-striped table-bordered">
			<tr>
				<th>USER CNIC</th>
				<th>DATE</th>
				<th>TIME</th>
				<th>OUTTIME</th>
			</tr>
		';
		foreach($data->result() as $row)
		{
			$output .= '
			<tr>
				<td>'.$row->CustomerName.'</td>
				<td>'.$row->Address.'</td>
				<td>'.$row->City.'</td>
				<td>'.$row->PostalCode.'</td>
				<td>'.$row->Country.'</td>
			</tr>
			';
		}
		$output .= '</table>';
		echo $output;
	}

	function import()
	{
		if(isset($_FILES["file"]["name"])) {

			$path = $_FILES["file"]["tmp_name"];
			$object = PHPExcel_IOFactory::load($path);

			$total_paid_amount = 0;
			$received_paid_amount = 0;
			$one_link_amount = 0;
			$card_amount = 0;
			$total_settle_date = "";
            $insert_id = "";
            if (count($object->getWorksheetIterator()) > 0) {
                $objWorksheet = $object->setActiveSheetIndex(1);
                $check_date = $objWorksheet->getCellByColumnAndRow(4, 2)->getValue();
                $check_date =date_format(date_create_from_format('d-m-Y',$check_date), 'Y-m-d');
                $checks = $this->db->get_where("pay_pro_settlement","settlement_date = '$check_date'")->result_array();
                if (count($checks)>0){
                    $this->session->set_flashdata('error', 'Settlement Details Already Uploaded');
                    redirect(site_url().'/excel_import/index');
                }
                $this->db->set("settlement_date", $check_date);
                $this->db->set("total_amount","0");
                $this->db->set("paid_amount","0");
                $this->db->set("link_amount","0");
                $this->db->set("card_amount","0");
                $this->db->set("created_by",$this->session->userdata("user_id"));
                $this->db->insert("pay_pro_settlement");
                $insert_id = $this->db->insert_id();
                foreach ($object->getWorksheetIterator() as $sheet_no => $worksheet) {
                    if ($sheet_no == 1) {
                        $highestRow = $worksheet->getHighestRow();
                        $highestColumn = $worksheet->getHighestColumn();
                        $received_paid_amount =0;
                        for ($row = 2; $row <= $highestRow; $row++) {
                            $company = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
                            $customer = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                            $paypro_id = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                            $order_no = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                            $settle_date = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
                            $payment_date = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
                            $actual_amount = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
                            $paid_amount = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
                            $paid_via = $worksheet->getCellByColumnAndRow(9, $row)->getValue();
                            $delay_time = $worksheet->getCellByColumnAndRow(10, $row)->getValue();
                            $total_settle_date = date_format(date_create_from_format('d-m-Y',$settle_date), 'Y-m-d');
                            //$paid_amount = round($paid_amount);

                            $total_paid_amount += str_replace(',', '', $actual_amount);
                            
                            $received_paid_amount += str_replace(',','',$paid_amount);
                            
                            if ($paid_via == 'Debit/Credit')
                            {
                                $card_amount+=$received_paid_amount;
                            }
                            elseif($paid_via == '1Link'){
                                $one_link_amount += str_replace(',','',$paid_amount);
                            }

                            
                            $this->db->set('settlement_id', $insert_id);
                            $this->db->set('customer', $customer);
                            $this->db->set('paypro_id', $paypro_id);
                            $this->db->set('order_no', $order_no);
                            $this->db->set('paid_date', date_format(date_create_from_format('d-m-Y',$payment_date), 'Y-m-d'));
                            $this->db->set('received_date', $total_settle_date);
                            $this->db->set('paid_amount', str_replace(',', '', $actual_amount));
                            $this->db->set('received_amount', str_replace(',', '', $paid_amount));
                            $this->db->set('paid_via', $paid_via);
                            $this->db->set('paid_after_days', $delay_time);
                            $this->db->insert('settlement_payments');

                            $st_payment_id = $this->db->insert_id();

                            $this->db->set('settlement_id',$insert_id);
                            $this->db->set('settlement_payment_id',$st_payment_id);
                            $this->db->where('connect_pay_id', $paypro_id);
                            $this->db->update("students_payments");

                            $entry = $this->db->get_where("students_payments","connect_pay_id = '$paypro_id'")->row();

                            if ($entry->type == "student") {
                                $this->db->select('*');
                                $this->db->from('payments');
                                $this->db->where_in('challan_no', explode(',', $entry->challan_ids));
                                $fees = $this->db->get()->result_array();
                                if (count($fees) > 0) {
                                    foreach ($fees as $fee) {
                                        $this->db->set('settlement_id', $insert_id);
                                        $this->db->set('settlement_payment_id', $st_payment_id);
                                        $this->db->where('id', $fee['id']);
                                        $this->db->update('payments');
                                    }
                                }
                            }else{

                            }
                        }
                    }
                }
            }
            if ($insert_id != "") {
                $this->db->set("settlement_date", $total_settle_date);
                $this->db->set("total_amount", $total_paid_amount);
                $this->db->set("paid_amount", $received_paid_amount);
                $this->db->set("link_amount", $one_link_amount);
                $this->db->set("card_amount", $card_amount);
                $this->db->where("id", $insert_id);
                $this->db->update("pay_pro_settlement");
            }
            redirect('excel_import/index');
		}
		else {

            $data['settlements'] = $this->db->select("pay_pro_settlement.*,CONCAT(users.first_name,' ',users.last_name) as created_by")->join("users","users.user_id = pay_pro_settlement.created_by","left")->get("pay_pro_settlement")->result_array();

            $this->load->view('inc/header');
            $this->load->view('inc/sidebar');
            $this->load->view('pay_pro/excel_import',$data);
            $this->load->view('inc/footer');
        }
	}

    function entries($id) {

        $data['settlements'] = $this->db
            ->join("payments","payments.settlement_payment_id = settlement_payments.id","left")
            ->join("students","students.student_id = payments.student_id","left")
            ->join("classes","classes.class_id = students.class_id","left")
            ->join("courses","courses.course_id = classes.course_id","left")
            ->join("campuses","campuses.campus_id = classes.campus_id","left")
            ->group_by("CASE WHEN payments.settlement_payment_id IS NOT NULL THEN payments.settlement_payment_id else settlement_payments.id end",false)
            ->get_where("settlement_payments","settlement_payments.settlement_id = '$id'")
            ->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('pay_pro/excel_entries',$data);
        $this->load->view('inc/footer');
    }

    function unpaid_entries() {

        $data['settlements'] = $this->db
            ->join("students","students.student_id = students_payments.student_id","left")
            ->join("classes","classes.class_id = students.class_id","left")
            ->join("courses","courses.course_id = classes.course_id","left")
            ->join("campuses","campuses.campus_id = classes.campus_id","left")
            ->get_where("students_payments","students_payments.transaction_status = 'PAID' and students_payments.settlement_id IS NULL")
            ->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('pay_pro/unpaid_entries',$data);
        $this->load->view('inc/footer');
    }

    function manual_unpay($payment_id)
    {
        $this->db->set('transaction_status','UNPAID');
        $this->db->where('payment_id',$payment_id);
        $this->db->update('students_payments');

        $this->session->set_flashdata('message','Payment UNPAID successfully.');
        redirect('excel_import/unpaid_entries');
    }

    public function delete($id)
    {
        $this->db->where("settlement_id",$id)->delete('settlement_payments');
        $this->db->where("id",$id)->delete('pay_pro_settlement');

        $this->db->set('paypro_id',NULL);
        $this->db->where('paypro_id',$id);
        $this->db->update('bank_reconciliation_statement');

        $this->db->set('clear_college_fee',0);
        $this->db->set('settlement_id',NULL);
        $this->db->set('settlement_payment_id',NULL);
        $this->db->where('settlement_id',$id);
        $this->db->update('payments');

        $this->session->set_flashdata('message','Statement Deleted Successfully.');
        redirect('excel_import/index');
    }
    
    public function paypro_entries()
    {
        if(@$this->input->post('from_date') && @$this->input->post('to_date'))
        {
            $data['from_date'] = $from_date = $this->input->post('from_date');
            $data['to_date'] = $to_date = $this->input->post('to_date');
        }
        else
        {
            $data['from_date'] = $from_date = date('Y-m-d');
            $data['to_date'] = $to_date = date('Y-m-d');
        }
        
        $this->db->select('*');
        $this->db->from('students_payments');
        $this->db->join('students','students.student_id=students_payments.student_id','inner');
        $this->db->where(array('students_payments.created_on>='=>$from_date,'students_payments.created_on<='=>$to_date));
        $data['payments'] = $this->db->get()->result_array();
        
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('pay_pro/paypro_entries',$data);
        $this->load->view('inc/footer');
    }
    
    public function manual_pay($payment_id)
    {
        $payment = $this->db->get_where('students_payments',array('payment_id'=>$payment_id))->row();
        
        $challans = explode(',',$payment->challan_ids);
        
        foreach($challans as $challan)
        {
            if($challan!='')
            {
                $this->db->set('paid',1);
                $this->db->where('challan_no',$challan);
                $this->db->update('payments');
            }
        }
        
        $this->db->set('transaction_status','PAID');
        $this->db->set('updated_by',$this->session->userdata('name'));
        $this->db->where('payment_id',$payment_id);
        $this->db->update('students_payments');
        
        $this->session->set_flashdata('message','Payment Status Updated Successfully.');
        redirect('excel_import/paypro_entries');
    }
    
    public function manual_unpay_transaction($payment_id)
    {
        $payment = $this->db->get_where('students_payments',array('payment_id'=>$payment_id))->row();
        
        $challans = explode(',',$payment->challan_ids);
        
        foreach($challans as $challan)
        {
            if($challan!='')
            {
                $this->db->set('paid',0);
                $this->db->where('challan_no',$challan);
                $this->db->update('payments');
            }
        }
        
        $this->db->set('transaction_status','UNPAID');
        $this->db->set('updated_by',$this->session->userdata('name'));
        $this->db->where('payment_id',$payment_id);
        $this->db->update('students_payments');
        
        $this->session->set_flashdata('message','Payment Status Updated Successfully.');
        redirect('excel_import/paypro_entries');
    }
}