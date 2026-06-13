<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Paypro extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
    }

    public function uis(){

        $username=$this->input->get('username');
        $password=$this->input->get('password');
        $csvinvoiceids=$this->input->get('csvinvoiceids');

        $this->db->select('*');
        $this->db->from('paypro');
        $this->db->where('username = "'.$username.'" and password = "'.$password.'"');
        $paypro= $this->db->get()->result_array();

        $this->db->set(array(
            'student_id'=>'1',
            'name'=>$username.' '.$password,
            'payment'=>$csvinvoiceids,
        ));
        $this->db->insert('test_payment');

        if(count($paypro)>0){

            $resp =array();
            $this->db->select('*');
            $this->db->from('students_payments');
            $this->db->where_in('students_payments.order_number', explode(',', $csvinvoiceids));
            $invoices= $this->db->get()->result_array();

            if (count($invoices)>0) {
                $i = 0;
                foreach ($invoices as $payment){
                    if ($payment['type'] == "student") {
                        if ($payment['application_id'] != "0") {
                            $application_id = $payment['application_id'];
                            $qry = "SELECT * FROM `admission_applications` WHERE application_id = '" . $payment['application_id'] . "'";
                            $student = $this->db->query($qry)->result_array();

                            $course_id = $student[0]['course_id'];
                            $device_id = $student[0]['device_id'];
                            $campus_id = $student[0]['campus_id'];
                            $first_name = $student[0]['first_name'];
                            $last_name = $student[0]['last_name'];
                            $father_name = $student[0]['father_name'];
                            $plan_id = $student[0]['plan_id'];
                            $gender = $student[0]['gender'];
                            $qualification = $student[0]['qualification'];
                            $caste = $student[0]['caste'];
                            $religion = $student[0]['religion'];
                            $email = $student[0]['email'];
                            $cnic = $student[0]['cnic'];
                            $blood_group = $student[0]['blood_group'];
                            $date_of_birth = $student[0]['date_of_birth'];
                            $city = $student[0]['city'];
                            $address = $student[0]['address'];
                            $mobile = $student[0]['mobile'];
                            $emergency_no = $student[0]['emergency_no'];
                            $class_id = $student[0]['class_id'];
                            $district = $student[0]['district'];
                            $tehsil = $student[0]['tehsil'];
                            $mark_of_identification = $student[0]['mark_of_identification'];
                            $place_of_birth = $student[0]['place_of_birth'];
                            $board = $student[0]['board'];
                            $shift = $student[0]['shift'];
                            $study_type = $student[0]['study_type'];

                            if (count($student) > 0) {

                                $student_plan = $this->db->get_where("fee_rules", "fee_rule_id = '$plan_id'")->row();
                                $this->db->set(array(
                                    'course_id' => $course_id,
                                    'total_fee' => $student_plan->total_fee,
                                    'device_id' => $device_id,
                                    'study_campus' => $campus_id,
                                    'first_name' => $first_name,
                                    'last_name' => $last_name,
                                    'father_name' => $father_name,
                                    'plan_id' => $plan_id,
                                    'gender' => $gender,
                                    'password' => '827ccb0eea8a706c4c34a16891f84e7b',
                                    'qualification' => $qualification,
                                    'caste' => $caste,
                                    'religion' => $religion,
                                    'email' => $email,
                                    'cnic' => $cnic,
                                    'blood_group' => $blood_group,
                                    'date_of_birth' => $date_of_birth,
                                    'city' => $city,
                                    'address' => $address,
                                    'mobile' => $mobile,
                                    'emergency_no' => $emergency_no,
                                    'class_id' => $class_id,
                                    'district' => $district,
                                    'tehsil' => $tehsil,
                                    'mark_of_identification' => $mark_of_identification,
                                    'place_of_birth' => $place_of_birth,
                                    'board' => $board,
                                    'shift' => $shift,
                                    'study_type' => $study_type,
                                ));


                                $admission_result = $this->db->insert('students');
                                $insert_id = $this->db->insert_id();

                                $this->db->set(array(
                                    'status' => '4',
                                ));

                                $this->db->where('application_id', $application_id);
                                $status_results = $this->db->update('admission_applications');

                                $this->db->set(array(
                                    'transaction_status' => 'PAID',
                                ));

                                $this->db->where('payment_id', $payment['payment_id']);
                                $status_results = $this->db->update('students_payments');

                                $this->Generate_plan($plan_id, $insert_id, $payment['order_amount'], $payment['payment_id']);
                            }

                            $resp[$i]['StatusCode'] = '00';
                            $resp[$i]['InvoiceID'] = $payment['order_number'];
                            $resp[$i]['Description'] = $student[0]['first_name'];
                            $i++;
                        }
                        else {

                            $this->db->select('*');
                            $this->db->from('payments');
                            $this->db->where_in('challan_no', explode(',', $payment['challan_ids']));
                            $fees = $this->db->get()->result_array();
                            if (count($fees) > 0) {
                                foreach ($fees as $fee) {
                                    $this->db->set('paid', 1);
                                    if (count($fee) > 1) {
                                        $this->db->set('merged_challan', $payment['order_number']);
                                        $this->db->set('paid_challans', $payment['challan_ids']);
                                    } else {
                                        $this->db->set('paid_challans', $payment['challan_ids']);
                                    }
                                    $this->db->where('id', $fee['id']);
                                    $this->db->update('payments');
                                }
                            }
                            $this->db->set(array(
                                'transaction_status' => 'PAID',
                            ));

                            $this->db->where('payment_id', $payment['payment_id']);
                            $this->db->update('students_payments');

                            $resp[$i]['StatusCode'] = '00';
                            $resp[$i]['InvoiceID'] = $payment['order_number'];
                            $resp[$i]['Description'] = "SUCCESS";
                            $i++;
                        }
                    }else{
                        $this->db->set(array(
                            'transaction_status' => 'PAID',
                        ));

                        $this->db->where('payment_id', $payment['payment_id']);
                        $this->db->update('students_payments');

                        $resp[$i]['StatusCode'] = '00';
                        $resp[$i]['InvoiceID'] = $payment['order_number'];
                        $resp[$i]['Description'] = "SUCCESS";
                        $i++;
                    }
                }
                echo json_encode($resp);
            }
            else {
                $result = array(
                    'StatusCode' => '01',
                    'Description' => 'Invalid Data. Username or password is invalid',
                    'InvoiceID' => 'NULL',
                );
                echo json_encode([$result]);
            }
        }
        else {
            $result = array(
                'StatusCode' => '01',
                'Description' => 'Invalid Data. Username or password is invalid',
                'InvoiceID' => 'NULL',
            );
            echo json_encode([$result]);
        }
    }

    public function Generate_plan($plan_id,$student_id,$first_installment_amount,$pay_pro_id){

        if($plan_id == NULL || $student_id == NULL|| $first_installment_amount == NULL)
        {
            $result = array(
                'status'=>'Failed',
                'response_code'=>'0',
                'message'=>'Select Payment Plan',
                'plans_response'=>null
            );
            echo json_encode($result);
        }
        else {
            $payment_plan       = $plan_id;

            $plan = $this->db->get_where('fee_rules', array('fee_rule_id' => $payment_plan))->result_array();
            $plan = $plan[0];
            $instdate = $plan['paid_date_each_installment'];
            $extendinstallments = $plan['no_of_installments'];


            $this->db->set('total_fee', $plan['total_fee']);
            $this->db->set('plan_id', $payment_plan);
            $this->db->where('student_id', $student_id);
            $this->db->update('students');

            for ($i = 1; $i <= 1; $i++) {
                $dead_line = $plan['last_date_council_fee'];
                $challan_no = $this->getChallanNo();

                $this->db->set('amount', $plan['first_time_council_fee']);
                $this->db->set('disc_per_inst', $plan['disc_per_inst']);
                $this->db->set('dead_line', $dead_line);
                $this->db->set('student_id', $this->input->post('student_id'));
                $this->db->set('payment_plan', 'consulation fee');
                $this->db->set('payment_comment', 'consulation fee');
                $this->db->set('challan_no', $challan_no);
                $this->db->set('add_by', "Mobile App");
                $this->db->set('last_edit', "Mobile App");
                $this->db->insert('payments');
            }

            $dead_line = date("Y-m-d");
            $challan_no = $this->getChallanNo();
            $this->db->set('amount', $first_installment_amount);
            $this->db->set('dead_line', $dead_line);
            $this->db->set('student_id', $student_id);
            $this->db->set('payment_plan', "Auto");
            $this->db->set('disc_per_inst', $plan['disc_per_inst']);
            $this->db->set('challan_no', $challan_no);
            $this->db->set('payment_comment', 'College Fee');
            $this->db->set('add_by', "Mobile App");
            $this->db->set('last_edit', "Mobile App");
            $this->db->set('paid', "1");
            $this->db->set('paypro_payment_id', $pay_pro_id);
            $this->db->insert('payments');

            $totalamount = $plan['total_fee'] - $first_installment_amount;
            $permonth = $totalamount / $extendinstallments;
            for ($i = 1; $i <= $extendinstallments; $i++) {
                $dead_line = date("Y-m-" . $instdate, strtotime('first day of +' . $i . ' month'));

                $challan_no = $this->getChallanNo();

                $this->db->set('amount', $permonth);
                $this->db->set('dead_line', $dead_line);
                $this->db->set('student_id', $this->input->post('student_id'));
                $this->db->set('payment_plan', "Auto");
                $this->db->set('disc_per_inst', $plan['disc_per_inst']);
                $this->db->set('challan_no', $challan_no);
                $this->db->set('payment_comment', 'College Fee');
                $this->db->set('add_by', "Mobile App");
                $this->db->set('last_edit', "Mobile App");
                $this->db->insert('payments');
            }

            $fee = $this->db->order_by("id","ASC")->limit(1)->get_where("payments",array("student_id" => $this->input->post('student_id'),"payment_plan !=" => "consulation fee"))->row();
            $result = array(
                'status' => 'SUCCESS',
                'response_code' => '1',
                'message' => 'Found',
                'payment_plan_response' => $fee
            );
            echo json_encode($result);
        }

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

}