<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Move_images extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
	}
	
	public function fee_challan()
	{	
//		$challans = $this->db->get('payments')->result_array();
//		foreach($challans as $challan)
//        {
//            if($challan['scan_challan']!='')
//            {
//                if($challan['student_id']==0)
//                {
//                    if (!is_dir('student_fee_challans/contractor_'.$challan['contractor_id'])) {
//                        mkdir('./student_fee_challans/contractor_'.$challan['contractor_id'], 0777, TRUE);
//                    }
//                    rename('student_fee_challans/'.$challan['scan_challan'], 'student_fee_challans/contractor_'.$challan['contractor_id'].'/'.$challan['scan_challan']);
//                }
//                else
//                {
//                    if (!is_dir('student_fee_challans/student_'.$challan['student_id'])) {
//                        mkdir('./student_fee_challans/student_'.$challan['student_id'], 0777, TRUE);
//                    }
//                    rename('student_fee_challans/'.$challan['scan_challan'], 'student_fee_challans/student_'.$challan['student_id']);
//                }
//            }
//            if($challan['fine_application']!='')
//            {
//                if($challan['student_id']==0)
//                {
//                    if (!is_dir('student_fee_challans/contractor_'.$challan['contractor_id'])) {
//                        mkdir('./student_fee_challans/contractor_'.$challan['contractor_id'], 0777, TRUE);
//                    }
//                    rename('student_fee_challans/'.$challan['fine_application'], 'student_fee_challans/contractor_'.$challan['fine_application']);
//                }
//                else
//                {
//                    if (!is_dir('student_fee_challans/student_'.$challan['student_id'])) {
//                        mkdir('./student_fee_challans/student_'.$challan['student_id'], 0777, TRUE);
//                    }
//                    rename('student_fee_challans/'.$challan['fine_application'], 'student_fee_challans/student_'.$challan['fine_application']);
//                }
//
//            }
//        }
	}

	public function missing_challans()
    {
        $this->db->select('*');
		$this->db->from('payments');
		$this->db->join('students','students.student_id=payments.student_id','left');
		$this->db->join('classes','classes.class_id=students.class_id','left');
		$this->db->join('campuses','campuses.campus_id=classes.campus_id','left');
		$this->db->order_by('campuses.campus_name','ASC');
		$this->db->order_by('students.roll_no','ASC');
		$this->db->where(array('payments.scan_challan!=' => '','payments.online_scan_challan'=>'','payments.paid'=>1));
        $payments = $this->db->get()->result_array();

        echo '<table border="1">';
		foreach ($payments as $payment) {
            if (file_exists(FCPATH . "uploads/" . $payment['scan_challan'])) {

            } else {
				echo '<tr>';
				echo '<td>'.$payment['campus_name'].'</td>';
				echo '<td>'.$payment['name'].'</td>';
                echo '<td>'.$payment['challan_no'] . '</td>';
				echo '<td>'.$payment['paid_date'] . '</td>';
				echo '<td>'.$payment['actual_amount'] . '</td>';
				echo '<td>'.$payment['roll_no'].'</td>';
				echo '<td>'.$payment['first_name'].' '.$payment['last_name'].'</td>';
				echo '<td>'.$payment['mobile'].'</td>';
				echo '<td>'.$payment['emergency_no'].'</td>';
				echo '</tr>';
            }
        }
		echo '</table>';
    }
}