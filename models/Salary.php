<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class Salary  extends CI_Controller{

    public function __construct()
    {
        parent::__construct();
        //$this->load->library('Email_reader');
    }

    public function salary_list(){

        $data['campuses'] = $this->db->get_where('campuses',array('status'=>1))->result_array();

        $course_id = $this->input->post('campus_id');
        $month = date("M", strtotime("-1 months"));

        $year = date("Y", strtotime("-1 months"));
        if($course_id != ''){
            $this->db->select('us.* , campuses.campus_name,(select earned_salary from payroll where 
                                                    user_id = us.user_id and payroll_month = "'.$month.'" and payroll_year = "'.$year.'" group by user_id) as count');
            $this->db->from('users us');
            $this->db->join('campuses','us.campus_id = campuses.campus_id ','inner');
            $this->db->where(array('us.campus_id'=>$course_id,'us.status'=>1));
            $data['staff'] = $this->db->get()->result_array();
            $data['campus_id'] = $course_id;

        }


        $data['month'] = $month;
        $data['year'] = $year;
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('salary/salary_list',$data);
        $this->load->view('inc/footer');
    }

    public function generate_salary($user_id,$campus_id){

        $data['from_date'] = date("Y-m-01", strtotime("-1 months"));
        $data['to_date']=date("Y-m-31", strtotime("-1 months"));

        $data['campus_id']= $campus_id;
        //GET RECOVERY ADMISSION INCENTIVE HERE;
        $user= $this->db->get_where('users',array('user_id'=>$user_id))->result_array();
        $desigs=explode (",", $user[0]['designation_id']);
        $admissioninc= $this->db->where_in('designation_id',$desigs)->get('admission_management_incentives')->result_array();

        if (count($admissioninc)>0) {

            $user = $this->db->get_where('users', array('user_id' => $user_id))->result_array();
            $full_name = $user[0]['first_name'];

            //GET ALL UNPAID FEE PAYMENTS DETAILS OF STUDENTS
            $this->db->select('payments.*');
            $this->db->from('payments');
            $this->db->join('students', 'students.student_id=payments.student_id', 'INNER');
            $this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
            $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
            $this->db->where(
                array(
                    'payments.dead_line<=' => $data['to_date'],
                    'payments.paid' => 1,
                    'students.status' => 1,
                    'students.entry_date>=' => $data['from_date'],
                    'students.entry_date<=' => $data['to_date'],
                    'students.add_by like' => '%' . $full_name . '%'
                )
            );
            $this->db->group_by('students.student_id');
            $data['total_paid_students'] = $this->db->get()->result_array();

            $counted = 0;
            $uncounted = 0;

            foreach ($data['total_paid_students'] as $paid) {

                $this->db->select_sum('payments.actual_amount');
                $this->db->from('payments');
                $this->db->where("payments.student_id = '" . $paid['student_id'] . "'");
                $tot = $this->db->get()->result_array();

                if (count($tot) > 0) {
                    if ($tot[0]['actual_amount'] >= 20000) {
                        $counted++;
                    } else {
                        $uncounted++;
                    }
                }
            }

            $recovery_management_id = $admissioninc[0]['incentive_id'];
            $this->db->order_by('start', 'ASC');

            $comission_rule = $this->db->get_where('admission_management_rules', array('admission_incentive_id' => $recovery_management_id, 'start<=' => $counted, 'end>' => $counted))->result_array();

            if (count($comission_rule) > 0) {
                $installment_comission = $comission_rule[0]['comission'] * $counted;
                $incentives_admission = $installment_comission;
            } else {
                $incentives_admission = 0;
            }
        }
        else{
            $incentives_admission = 0;
        }

        //GET RECOVERY INCENTIVE HERE
        $recoveryman = $this->db->get_where('recovery_management',array('designation_id'=>$user[0]['designation_id']))->result_array();

        if (count($recoveryman)>0) {
            $campus_ids = explode(',',$recoveryman[0]['campus_ids']);

            $recovery_management_id=$recoveryman[0]['recovery_management_id'];

            //GET FEE PAYMENTS DETAILS OF STUDENTS
            $this->db->select('payments.*');
            $this->db->from('payments');
            $this->db->join('students', 'students.student_id=payments.student_id', 'INNER');
            $this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
            $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
            $this->db->where_in('campuses.campus_id', $campus_ids);
            $this->db->where(array('payments.actual_paid_date>=' => $data['from_date'], 'payments.actual_paid_date<=' => $data['to_date'], 'payments.paid' => 1, 'students.status' => 1));
            $paid_payments_students = $this->db->get()->result_array();


            //GET FEE PAYMENTS DETAILS OF CONTRACTS
            $this->db->select('payments.*');
            $this->db->from('payments');
            $this->db->join('contracts', 'contracts.contract_id=payments.contract_id', 'INNER');
            $this->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'INNER');
            $this->db->join('campuses', 'contracts.campus_id=campuses.campus_id', 'INNER');
            $this->db->where_in('campuses.campus_id', $campus_ids);
            $this->db->where(array('payments.actual_paid_date>=' => $data['from_date'], 'payments.actual_paid_date<=' => $data['to_date'], 'payments.paid' => 1,'payments.amount !='=>'4500'));
            $paid_payments_contracts = $this->db->get()->result_array();


            //GET ALL UNPAID FEE PAYMENTS DETAILS OF STUDENTS
            $this->db->select('payments.*');
            $this->db->from('payments');
            $this->db->join('students', 'students.student_id=payments.student_id', 'INNER');
            $this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
            $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
            $this->db->where_in('campuses.campus_id', $campus_ids);
            $this->db->where(array('payments.dead_line<=' => $data['to_date'], 'payments.paid' => 0, 'students.status' => 1));
            $unpaid_payments_students = $this->db->get()->result_array();

            //GET ALL UNPAID FEE PAYMENTS DETAILS OF CONTRACTS
            $this->db->select('payments.*');
            $this->db->from('payments');
            $this->db->join('contracts', 'contracts.contract_id=payments.contract_id', 'INNER');
            $this->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'INNER');
            $this->db->join('campuses', 'contracts.campus_id=campuses.campus_id', 'INNER');
            $this->db->where_in('campuses.campus_id', $campus_ids);
            $this->db->where(array('payments.dead_line<='=>$data['to_date'],'payments.paid'=>0,'payments.payment_plan Not Like'=>'extra fee','payments.amount !='=>'4500'));
            $unpaid_payments_contracts = $this->db->get()->result_array();


            //GET SHIFTED PAYMENTS DETAILS OF STUDENTS
            $this->db->select('update_payment_requests.*');
            $this->db->from('update_payment_requests');
            $this->db->join('students','students.student_id=update_payment_requests.student_id','INNER');
            $this->db->join('classes','classes.class_id=students.class_id','INNER');
            $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
            $this->db->where_in('campuses.campus_id',$campus_ids);
            $this->db->where(array('update_payment_requests.update_date>='=>$data['from_date'],'update_payment_requests.update_date<='=>$data['to_date'],'update_payment_requests.ok_by_admin'=>1,'update_payment_requests.amount !='=>'4500'));
            $shifted_payments_students = $this->db->get()->result_array();

            //GET SHIFTED PAYMENTS DETAILS OF CONTRACTS
            $this->db->select('update_payment_requests.*');
            $this->db->from('update_payment_requests');
            $this->db->join('contracts','contracts.contract_id=update_payment_requests.contract_id','INNER');
            $this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','INNER');
            $this->db->join('campuses','contracts.campus_id=campuses.campus_id','INNER');
            $this->db->where_in('campuses.campus_id',$campus_ids);
            $this->db->where(array('update_payment_requests.update_date>='=>$data['from_date'],'update_payment_requests.update_date<='=>$data['to_date'],'update_payment_requests.ok_by_admin'=>1,'update_payment_requests.amount !='=>'4500'));
            $shifted_payments_contracts = $this->db->get()->result_array();


            $paid_entries = count($paid_payments_students) + count($paid_payments_contracts);



            $total_entries = (count($unpaid_payments_students) + count($unpaid_payments_contracts) + count($paid_payments_students) +
                count($paid_payments_contracts) + count($shifted_payments_students) + count($shifted_payments_contracts));


            if ($total_entries > 0) {
                $submitted_fee_percentage = round(($paid_entries / $total_entries) * 100, 2);

            } else {
                $submitted_fee_percentage = 0;

            }

            $comission_rule = $this->db->get_where('recovery_management_rules', array('recovery_management_id' => $recovery_management_id, 'start<=' => $submitted_fee_percentage, 'end>' => $submitted_fee_percentage))->result_array();
            $percent_amount = $comission_rule[0]['comission'];
            $amount = 0;

            if (count($comission_rule) > 0) {

                foreach($paid_payments_students as $due)
                {
                    if($due['split'] == '1' ){

                        $amount+=0.5*$percent_amount;
                    } else if($due['split'] == '2' ){

                        $amount+=0.25*$percent_amount;
                    }else{
                        $amount+=$percent_amount;
                    }
                }
                foreach($paid_payments_contracts as $due)
                {
                    if($due['split'] == '1' ){

                        $amount+=0.5*$percent_amount;
                    } else if($due['split'] == '2' ){

                        $amount+=0.25*$percent_amount;
                    }else{
                        $amount+=$percent_amount;
                    }
                }


                $installment_comission =  $amount;




            } else {
                $installment_comission = 0;

            }

            $this->db->select("payments.id as fee_id,'0' as isdel, 'UnPaid' as Fstatus,payments.split as split,payments.amount, payments.dead_line, payments.paid_challans, payments.merged_challan,payments.challan_no, payments.fine_amount, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee");
            $this->db->from('payments');
            $this->db->join('students','students.student_id=payments.student_id','INNER');
            $this->db->join('classes','classes.class_id=students.class_id','INNER');
            $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
            $this->db->where_in('campuses.campus_id',$campus_ids);
            $this->db->where('payments.merged_challan IS NOT NULL and payments.actual_amount > 0');
            $this->db->where(array('payments.actual_paid_date>='=>$data['from_date'],'payments.actual_paid_date<='=>$data['to_date'],'payments.paid'=>1,'students.status'=>1));
            $this->db->group_by("CASE WHEN payments.merged_challan IS NOT NULL THEN payments.merged_challan else '' end",false);
            $datafine_students = $this->db->get()->result_array();

            $this->db->select("payments.id as fee_id,'0' as isdel, 'UnPaid' as Fstatus,payments.split as split,payments.amount, payments.merged_challan,payments.challan_no, payments.paid_challans, payments.dead_line, payments.fine_amount, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee");
            $this->db->from('payments');
            $this->db->join('students','students.student_id=payments.student_id','INNER');
            $this->db->join('classes','classes.class_id=students.class_id','INNER');
            $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
            $this->db->where_in('campuses.campus_id',$campus_ids);
            $this->db->where(array('payments.actual_paid_date>='=>$data['from_date'],'payments.actual_paid_date<='=>$data['to_date'],'payments.paid'=>1,'students.status'=>1));
            $this->db->where('payments.merged_challan is null');
            $this->db->or_where('merged_challan IS not NULL and actual_amount = 0');
            $datapaid_payments_fine_students = $this->db->get()->result_array();

            $fine_students = array_merge($datafine_students,$datapaid_payments_fine_students);

            $collected_fine = 0;
            foreach ($fine_students as $paid_payments_student) {
                if($paid_payments_student['paid']='1')
                    $collected_fine += $paid_payments_student['fine_amount'] ;
            }



            $min_fine_amount = $recoveryman[0]['min_fine_amount'];
            $fine_amount_percentage = $recoveryman[0]['fine_amount_percentage'];
            if ($collected_fine > $min_fine_amount) {
                $fine_comission = ($collected_fine * $fine_amount_percentage) / 100;

            } else {
                $fine_comission = 0;

            }


            $totcommision=$installment_comission+$fine_comission;


        }
        else{
            $totcommision = 0;
        }

        $data['staff'] = $this->db->get_where('users',array('user_id'=>$user_id))->row();
        $data['user_id'] = $user_id;

        $this->db->select('allownces.name,allownces.type,user_allowances.amount');
        $this->db->from('user_allowances');
        $this->db->join('allownces', 'user_allowances.allowance_id=allownces.id', 'INNER');
        $this->db->where(array('user_allowances.user_id'=>$user_id));
        $alownce = $this->db->get()->result_array();

        if ($incentives_admission>0){

            array_push($alownce, array(
                'name' => 'Calculated Admission Incentive .( Counted Admissions ' .$counted . ' )',
                'type' => '0',
                'amount' => $incentives_admission,
            ));

        }

        if ($totcommision>0){

            array_push($alownce, array(
                'name' => 'Calculated Incentive Incentive .( Counted Incentive Recovery ' .$submitted_fee_percentage . ' ) | Fine Incentive : '.$fine_comission,
                'type' => '0',
                'amount' => $totcommision,
            ));

        }

        $date = date('Y-m-d');

        $current = date("m",strtotime($date));
        $next = date("m",strtotime($date."+1 month"));
        if($current==$next-1){
            $needed = date('Y-m-d',strtotime($date." +1 month"));
        }else{
            $needed = date('Y-m-d', strtotime("last day of next month",strtotime($date)));
        }

        $this->db->select('loan_plan.id as installment_id,loan_plan.*,loans.* ');
        $this->db->from('loans');
        $this->db->join('loan_plan', 'loan_plan.loan_id=loans.id', 'INNER');
        $this->db->where(array('loans.status'=>"1",
            'loan_plan.due_date <'=>$needed,
            'loan_plan.amount_paid <='=>'0',
            'loans.user_id'=>$user_id
        ));

        $this->db->order_by('loan_plan.id','asc');
        $data['loan'] = $this->db->get()->result_array();

        $data['user_allowances'] = $alownce;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('salary/generate_salary',$data);
        $this->load->view('inc/footer');
    }

    public function storepayroll(){

        $earnings =$this->input->post('earningstype');
        $earningsamount =$this->input->post('earningsValue');
        $deduct =$this->input->post('deductionstype');
        $deductamount =$this->input->post('deductionsValue');
        $loanallIds =$this->input->post('loanId');

        $this->db->set('user_id', $this->input->post('user_id'));
        $this->db->set('payroll_month', date("M", strtotime("-1 months")));
        $this->db->set('payroll_year', date("Y", strtotime("-1 months")));
        $this->db->set('basic_salary', $this->input->post('basic_salary'));
        $this->db->set('gross_salary', $this->input->post('gross_salary'));
        $this->db->set('earned_salary', $this->input->post('net_salary'));
        $this->db->set('deductions',$this->input->post('deduction_salary'));
        $this->db->set('earnings',$this->input->post('earing_salary'));
        $this->db->set('tax',$this->input->post('tax_salary'));
        $this->db->set('no_of_days',$this->input->post('total_days'));
        $this->db->set('no_of_absents',$this->input->post('total_absents'));
        $this->db->set('no_of_lates',$this->input->post('total_lates'));
        $this->db->set('created_by',$this->session->userdata('user_id'));

        $this->db->insert('payroll');
        $insertId = $this->db->insert_id();

        if($insertId != null){

            for ($x=0;$x<sizeof($earnings);$x++) {

                $this->db->set('payroll_id', $insertId);
                $this->db->set('name', $earnings[$x]);
                $this->db->set('type_id', '0');
                $this->db->set('amount', $earningsamount[$x]);
                $this->db->set('created_by', $this->session->userdata('user_id'));

                $this->db->insert('payroll_earn_deducs');
            }
            for ($x=0;$x<sizeof($deduct);$x++) {
                if ($loanallIds[$x] > 0){

                    $this->db->set('amount_paid', $deductamount[$x]);
                    $this->db->set('paid_at', 'salary');
                    $this->db->where("id = '$loanallIds[$x]'");
                    $this->db->update('loan_plan');
                }

                $this->db->set('payroll_id', $insertId);
                $this->db->set('name', $deduct[$x]);
                $this->db->set('type_id', '1');
                $this->db->set('amount', $deductamount[$x]);
                $this->db->set('created_by', $this->session->userdata('user_id'));

                $this->db->insert('payroll_earn_deducs');
            }
        }

        $course_id = $this->input->post('campus_id');
        $month = date("M", strtotime("-1 months"));

        $year = date("Y", strtotime("-1 months"));

        if($course_id != ''){
            $this->db->select('us.* , campuses.campus_name,(select earned_salary from payroll where 
                                                    user_id = us.user_id and payroll_month = "'.$month.'" and payroll_year = "'.$year.'" group by user_id) as count');
            $this->db->from('users us');
            $this->db->join('campuses','us.campus_id = campuses.campus_id ','inner');
            $this->db->where(array('us.campus_id'=>$course_id,'us.status'=>1));
            $data['staff'] = $this->db->get()->result_array();
            $data['campus_id'] = $course_id;
        }

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->session->set_flashdata('message', 'Payroll generated Successfully.');
        $this->load->view('salary/salary_list',$data);
        $this->load->view('inc/footer');
    }

    public function salary_view($id){


        $this->db->select('users.* , payroll.* , designations.designation_name , campuses.campus_name');
        $this->db->from('users');
        $this->db->join('payroll','payroll.user_id = users.user_id ','inner');
        $this->db->join('designations','designations.designation_id = users.designation_id ','inner');
        $this->db->join('campuses','campuses.campus_id = users.campus_id ','inner');
        $this->db->where(array('users.user_id'=>$id,'users.status'=>1));
        $data['sal'] = $this->db->get()->result_array();



        $this->load->view('salary/salary_view',$data);
    }

    public function salary_report(){

        $data['campuses'] = $this->db->get_where('campuses',array('status'=>1))->result_array();

        $course_id = $this->input->post('campus_id');
        $month = $this->input->post('to_date');

        $month = date("M", strtotime($month));
        $year = date("Y", strtotime($this->input->post('to_date')));



        $this->db->select('payroll.*,(select sum(amount) from payroll_earn_deducs where payroll_id=payroll.id and name like("%Loan installment%")) as loan,
            (select sum(amount) from payroll_earn_deducs where payroll_id=payroll.id and name like("%Advance installment%")) as advance,
			(select sum(amount) from payroll_earn_deducs where payroll_id=payroll.id and name like("%Special%")) as special,		
			users.first_name,users.last_name,campuses.campus_name,user_allowances.amount as user_alownce,designations.designation_name as designation,departments.department_name as department');
        $this->db->from('payroll');
        $this->db->join('users','payroll.user_id = users.user_id ','inner');
        $this->db->join('campuses','users.campus_id = campuses.campus_id ','inner');
        $this->db->join('user_allowances','user_allowances.user_id = users.user_id ','inner');
        $this->db->join('designations', 'designations.designation_id=users.designation_id', 'INNER');
        $this->db->join('departments', 'departments.department_id=users.department_id', 'INNER');
        $this->db->group_by('users.user_id');
        $this->db->where("payroll_month = '".$month."'");
        $this->db->where("payroll_year = '".$year."'");
        if($course_id != '' && $course_id != null){
            $this->db->where("users.campus_id = ".$course_id);
            $data['iscampus']='true';
        }else{
            $data['iscampus']='false';
        }


        $data['salary'] = $this->db->get()->result_array();





        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('salary/salary_list_report',$data);
        $this->load->view('inc/footer');
    }

    public function delete_salary($user_id){

        $this->db->where('user_id', $user_id);
        $this->db->where('payroll_month', date("M", strtotime("-1 months")));

        $this->db->delete('payroll');


        redirect('salary/salary_list');

    }

}