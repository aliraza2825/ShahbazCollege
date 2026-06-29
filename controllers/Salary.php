<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class Salary  extends CI_Controller{

    public function __construct()
    {
        parent::__construct();
        $this->ensure_salary_columns();
        //$this->load->library('Email_reader');
    }

    private function ensure_salary_columns()
    {
        if (!$this->db->field_exists('salary_adjustment', 'users')) {
            $this->db->query("ALTER TABLE users ADD salary_adjustment DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER gross_salary");
        }
        if (!$this->db->field_exists('apply_statutory_rules', 'users')) {
            $this->db->query("ALTER TABLE users ADD apply_statutory_rules TINYINT(1) NOT NULL DEFAULT 1 AFTER salary_adjustment");
        }
        if ($this->db->table_exists('payroll_statutory_rules') && !$this->db->field_exists('wage_contribution_cap', 'payroll_statutory_rules')) {
            $this->db->query("ALTER TABLE payroll_statutory_rules ADD wage_contribution_cap DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER calculation_base");
        }
    }

    private function user_applies_statutory_rules($user_id)
    {
        $row = $this->db
            ->select('apply_statutory_rules')
            ->where('user_id', $user_id)
            ->get('users')
            ->row_array();

        return !isset($row['apply_statutory_rules']) || (int) $row['apply_statutory_rules'] === 1;
    }

    public function salary_list(){

        $data['campuses'] = $this->db->get_where('campuses',array('status'=>1))->result_array();

        $course_id = $this->input->post('campus_id');

        if($this->input->post('date'))
        {
            $month = date("M", strtotime($this->input->post('date')));
            $year = date("Y", strtotime($this->input->post('date')));
        }
        else
        {
            $month = date("M", strtotime("-1 months"));
            $year = date("Y", strtotime("-1 months"));
        }

        
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

    public function generate_salary($user_id,$campus_id,$month,$year)
    {

        //$data['from_date'] = date("Y-m-01", strtotime("-1 months"));
        //$data['to_date'] = date("Y-m-t", strtotime($data['from_date']));

        $data['from_date'] = date("Y-m-d", strtotime($year.'-'.$month.'-01'));
        $data['to_date'] = date("Y-m-t", strtotime($year.'-'.$month.'-01'));

        $data['campus_id'] = $campus_id;
        //GET RECOVERY ADMISSION INCENTIVE HERE;
        $user = $this->db->get_where('users', array('user_id' => $user_id))->result_array();
        $desigs = explode(",", $user[0]['designation_id']);
        $admissioninc = $this->db->where_in('designation_id', $desigs)->get('admission_management_incentives')->result_array();

        if (count($admissioninc) > 0) {

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
        } else {
            $incentives_admission = 0;
        }

        //GET RECOVERY INCENTIVE HERE
        $recoveryman = $this->db->where_in('designation_id', $desigs)->get("recovery_management")->result_array();
        $totcommision_array = array();
        foreach ($recoveryman as $reco) {
            if (count($recoveryman) > 0) {
                $campus_ids = explode(',', $reco['campus_ids']);

                $recovery_management_id = $reco['recovery_management_id'];

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
                $this->db->where(array('payments.actual_paid_date>=' => $data['from_date'], 'payments.actual_paid_date<=' => $data['to_date'], 'payments.paid' => 1, 'payments.amount !=' => '4500'));
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
                $this->db->where(array('payments.dead_line<=' => $data['to_date'], 'payments.paid' => 0, 'payments.payment_plan Not Like' => 'extra fee', 'payments.amount !=' => '4500'));
                $unpaid_payments_contracts = $this->db->get()->result_array();


                //GET SHIFTED PAYMENTS DETAILS OF STUDENTS
                $this->db->select('update_payment_requests.*');
                $this->db->from('update_payment_requests');
                $this->db->join('students', 'students.student_id=update_payment_requests.student_id', 'INNER');
                $this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
                $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
                $this->db->where_in('campuses.campus_id', $campus_ids);
                $this->db->where(array('update_payment_requests.update_date>=' => $data['from_date'], 'update_payment_requests.update_date<=' => $data['to_date'], 'update_payment_requests.ok_by_admin' => 1, 'update_payment_requests.amount !=' => '4500'));
                $shifted_payments_students = $this->db->get()->result_array();

                //GET SHIFTED PAYMENTS DETAILS OF CONTRACTS
                $this->db->select('update_payment_requests.*');
                $this->db->from('update_payment_requests');
                $this->db->join('contracts', 'contracts.contract_id=update_payment_requests.contract_id', 'INNER');
                $this->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'INNER');
                $this->db->join('campuses', 'contracts.campus_id=campuses.campus_id', 'INNER');
                $this->db->where_in('campuses.campus_id', $campus_ids);
                $this->db->where(array('update_payment_requests.update_date>=' => $data['from_date'], 'update_payment_requests.update_date<=' => $data['to_date'], 'update_payment_requests.ok_by_admin' => 1, 'update_payment_requests.amount !=' => '4500'));
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
                
                $percent_amount = @$comission_rule[0]['comission'];
                
                $amount = 0;

                if (count($comission_rule) > 0) {

                    foreach ($paid_payments_students as $due) {
                        if ($due['split'] == '1') {

                            $amount += 0.5 * $percent_amount;
                        } else if ($due['split'] == '2') {

                            $amount += 0.25 * $percent_amount;
                        } else {
                            $amount += $percent_amount;
                        }
                    }
                    foreach ($paid_payments_contracts as $due) {
                        if ($due['split'] == '1') {

                            $amount += 0.5 * $percent_amount;
                        } else if ($due['split'] == '2') {

                            $amount += 0.25 * $percent_amount;
                        } else {
                            $amount += $percent_amount;
                        }
                    }

                    $installment_comission = $amount;
                } else {
                    $installment_comission = 0;
                }

                $this->db->select("payments.id as fee_id,'0' as isdel, 'UnPaid' as Fstatus,payments.split as split,payments.amount, payments.dead_line, payments.paid_challans, payments.merged_challan,payments.challan_no, payments.fine_amount, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee");
                $this->db->from('payments');
                $this->db->join('students', 'students.student_id=payments.student_id', 'INNER');
                $this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
                $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
                $this->db->where_in('campuses.campus_id', $campus_ids);
                $this->db->where('payments.merged_challan IS NOT NULL and payments.actual_amount > 0');
                $this->db->where(array('payments.actual_paid_date>=' => $data['from_date'], 'payments.actual_paid_date<=' => $data['to_date'], 'payments.paid' => 1, 'students.status' => 1));
                $this->db->group_by("CASE WHEN payments.merged_challan IS NOT NULL THEN payments.merged_challan else '' end", false);
                $datafine_students = $this->db->get()->result_array();

                $this->db->select("payments.id as fee_id,'0' as isdel, 'UnPaid' as Fstatus,payments.split as split,payments.amount, payments.merged_challan,payments.challan_no, payments.paid_challans, payments.dead_line, payments.fine_amount, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee");
                $this->db->from('payments');
                $this->db->join('students', 'students.student_id=payments.student_id', 'INNER');
                $this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
                $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
                $this->db->where_in('campuses.campus_id', $campus_ids);
                $this->db->where(array('payments.actual_paid_date>=' => $data['from_date'], 'payments.actual_paid_date<=' => $data['to_date'], 'payments.paid' => 1, 'students.status' => 1));
                $this->db->where('payments.merged_challan is null');
                $this->db->or_where('merged_challan IS not NULL and actual_amount = 0');
                $datapaid_payments_fine_students = $this->db->get()->result_array();

                $fine_students = array_merge($datafine_students, $datapaid_payments_fine_students);

                $collected_fine = 0;
                foreach ($fine_students as $paid_payments_student) {
                    if ($paid_payments_student['paid'] = '1')
                        $collected_fine += $paid_payments_student['fine_amount'];
                }
                $min_fine_amount = $reco['min_fine_amount'];
                $fine_amount_percentage = $reco['fine_amount_percentage'];
                if ($collected_fine > $min_fine_amount) {
                    $fine_comission = ($collected_fine * $fine_amount_percentage) / 100;

                } else {
                    $fine_comission = 0;
                }
                $totcommision = $installment_comission + $fine_comission;
                array_push($totcommision_array, array("commission" => $totcommision, "fine_commission" => $fine_comission, "fee_percent" => $submitted_fee_percentage));
            } else {
                $totcommision = 0;
                array_push($totcommision_array, array("commission" => $totcommision, "fine_commission" => 0, "fee_percent" => 0));
            }
        }

        $data['staff'] = $this->db->get_where('users', array('user_id' => $user_id))->row();
        $data['user_id'] = $user_id;
        $data['salary_pettycash'] = $this->db
            ->where('assign_to', $user_id)
            ->get('petty_cash_college_wise')
            ->row_array();

        $this->db->select('allownces.name,allownces.type,user_allowances.amount');
        $this->db->from('user_allowances');
        $this->db->join('allownces', 'user_allowances.allowance_id=allownces.id', 'INNER');
        $this->db->where(array('user_allowances.user_id' => $user_id));
        $alownce = $this->db->get()->result_array();

        if ($incentives_admission > 0) {

            array_push($alownce, array(
                'name' => 'Calculated Admission Incentive .( Counted Admissions ' . $counted . ' )',
                'type' => '0',
                'amount' => $incentives_admission,
            ));

        }

        foreach ($totcommision_array as $datas) {
            if ($datas['commission'] > 0) {
                array_push($alownce, array(
                    'name' => 'Calculated Incentive Incentive .( Counted Incentive Recovery ' . $datas['fee_percent'] . ' ) | Fine Incentive : ' . $datas['fine_commission'],
                    'type' => '0',
                    'amount' => $datas['commission'],
                ));
            }
        }

        $date = date("Y-m-t", strtotime($year.'-'.$month.'-01'));

        $current = date("m", strtotime($date));
        $next = date("m", strtotime($date . "+1 month"));
        if ($current == $next - 1) {
            $needed = date('Y-m-d', strtotime($date . " +1 month"));
        } else {
            $needed = date('Y-m-d', strtotime("last day of next month", strtotime($date)));
        }

        $this->db->select('loan_plan.id as installment_id,loan_plan.*,loans.* ');
        $this->db->from('loans');
        $this->db->join('loan_plan', 'loan_plan.loan_id=loans.id', 'INNER');
        $this->db->where(array('loans.status' => "1",
            'loan_plan.due_date <=' => $needed,
            'loan_plan.amount_paid <=' => '0',
            'loans.user_id' => $user_id
        ));

        $this->db->order_by('loan_plan.id', 'asc');
        $data['loan'] = $this->db->get()->result_array();
        
        $payroll_date = $data['to_date'];

        $basic_salary = (float) $data['staff']->salary;
        $gross_salary = (float) $data['staff']->gross_salary;
        
        if ($gross_salary <= 0) {
            $gross_salary = $basic_salary;
        }
        
        /*
         * EOBI / Social Security
         * Abhi calculation gross salary par kar rahe hain.
         * Agar kisi rule ka base basic salary chahiye ho to rule calculation_base se control kar sakte hain.
         */
        $applyStatutoryRules = $this->user_applies_statutory_rules($user_id);
        $statutory = $applyStatutoryRules
            ? $this->calculate_statutory_contributions($gross_salary, $payroll_date, $basic_salary)
            : array('employee_deductions' => array(), 'employer_contributions' => array());
        
        foreach ($statutory['employee_deductions'] as $deduction) {
            array_push($alownce, array(
                'name' => $deduction['name'],
                'type' => '1',
                'amount' => $deduction['amount'],
            ));
        }
        
        $data['employer_contributions'] = $statutory['employer_contributions'];
        $data['apply_statutory_rules'] = $applyStatutoryRules;
        
        $total_allownce = 0;

        foreach ($alownce as $item) {
            if ($item['type'] == 0) {
                $total_allownce += $item['amount'];
            }
        }
        
        /*
         * Income Tax
         * Ye taxable/gross salary par calculate kar rahe hain.
         */
        $income_tax = $this->calculate_income_tax_amount(($basic_salary+$total_allownce), $payroll_date);
        
        $data['income_tax'] = $income_tax['amount'];

        $data['user_allowances'] = $alownce;
        $strDateFrom = $data['from_date'];
        $strDateTo = $data['to_date'];
        $dates = $this->createDateRangeArray($strDateFrom, $strDateTo);

        $this->db->select('machine_data.*');
        $this->db->from('machine_data');
        $this->db->join('users', 'users.user_id=machine_data.teacher_student_id', 'inner');
        $this->db->where(array('users.status' => 1, 'users.user_id' => $user_id));
        $user = $this->db->get()->row();
        $my_attendances = array();

        if ($user != NULL) {
            $machine_user_id = $user->machine_id;
            foreach ($dates as $key => $date) {
                $array_date = array("date" => $date, "in_time" => "", "out_time" => "", "status" => "0");
                $qry = 'SELECT * FROM attendence WHERE machine_user_id=' . $machine_user_id . ' AND (time>="' . $date . ' 00:00:00" AND time<"' . $date . ' 23:59:59") ORDER BY time ASC LIMIT 1';
                $checkin_time = $this->db->query($qry)->result_array();
                if (count($checkin_time) > 0) {
                    $array_date['in_time'] = @date('h:i:s A', strtotime($checkin_time[0]['time']));
                    $array_date['status'] = "1";
                } else {
                    $array_date['in_time'] = '';
                    $array_date['status'] = "0";
                }

                $qry = 'SELECT * FROM attendence WHERE machine_user_id=' . $machine_user_id . ' AND (time>="' . $date . ' 00:00:00.00" AND time<"' . $date . ' 23:59:59.999") ORDER BY time DESC LIMIT 1';
                $checkout_time = $this->db->query($qry)->result_array();
                if (count($checkout_time) > 0) {
                    $my_checkout = @date('h:i:s A', strtotime($checkout_time[0]['time']));

                    if ($my_checkout != $array_date['in_time'])
                        $array_date['out_time'] = @date('h:i:s A', strtotime($checkout_time[0]['time']));
                    else
                        $array_date['out_time'] = '';
                } else {
                    $array_date['out_time'] = '';
                }
                $Day = date('l', strtotime($date));
                $status = $this->db->get_where("staff_timing", "staff_id = '$user_id' and day = '$Day'")->result_array();

                if (count($status) > 0 && $array_date['in_time'] != "") {
                    if (strtotime($array_date['in_time']) < strtotime($status[0]['half_day_on']))
                        $array_date['status'] = "1";
                    elseif (strtotime($array_date['in_time']) < strtotime($status[0]['full_day_on']))
                        $array_date['status'] = "2";
                    else
                        $array_date['status'] = "3";
                }
                if ($array_date['status'] == "0" || $array_date['status'] == "3") {
                    $event = $this->db->where("fromdate >=", $date)
                        ->where("todate <=", $date)
                        ->where("empid", $user_id)
                        ->where("status", "1")
                        ->get("tblleaves")->result_array();
                    if (count($event) > 0) {
                        $array_date['status'] = "4";
                    }
                    if (count($status) > 0) {
                        if ($status[0]['checkin_timing'] == '00:00:00') {
                            $array_date['status'] = "5";
                        }
                    }

                }
                array_push($my_attendances, $array_date);
            }
        }

        $data['present'] = 0;
        $data['absent'] = 0;
        $data['leaves'] = 0;
        $data['counted_days'] = cal_days_in_month(CAL_GREGORIAN, date("m", strtotime($data['from_date'])), date("Y", strtotime($data['from_date'])));

        foreach ($my_attendances as $at) {
            if ($at['status'] == "0" || $at['status'] == "3") {
                $data['absent'] += 1;
            } elseif ($at['status'] == "1" || $at['status'] == "5") {
                $data['present'] += 1;
            } elseif ($at['status'] == "4") {
                $data['leaves'] += 1;
            } elseif ($at['status'] == "2") {
                $data['present'] += 0.5;
            }
        }

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('salary/generate_salary', $data);
        $this->load->view('inc/footer');
    }
    
    private function statutory_rule_base_salary($rule, $gross_salary, $basic_salary = null, $net_salary = null)
    {
        $base = isset($rule['calculation_base']) ? $rule['calculation_base'] : 'gross_salary';

        if ($base === 'basic_salary') {
            return (float) $basic_salary;
        }

        if ($base === 'net_salary' && $net_salary !== null) {
            return (float) $net_salary;
        }

        return (float) $gross_salary;
    }

    private function wage_to_pay_contribution($selected_wage, $rule)
    {
        $selected_wage = (float) $selected_wage;
        $cap = isset($rule['wage_contribution_cap']) ? (float) $rule['wage_contribution_cap'] : 0;

        if ($cap > 0 && $selected_wage > $cap) {
            return $cap;
        }

        return $selected_wage;
    }

    private function calculate_statutory_contributions($base_salary, $payroll_date, $basic_salary = null, $net_salary = null)
    {
        $result = array(
            'employee_deductions' => array(),
            'employer_contributions' => array()
        );
    
        $rules = $this->db
            ->where('status', 1)
            ->group_start()
                ->where('effective_from IS NULL', null, false)
                ->or_where('effective_from <=', $payroll_date)
            ->group_end()
            ->group_start()
                ->where('effective_to IS NULL', null, false)
                ->or_where('effective_to >=', $payroll_date)
            ->group_end()
            ->get('payroll_statutory_rules')
            ->result_array();
    
        foreach ($rules as $rule) {
            $selectedBaseSalary = $this->statutory_rule_base_salary($rule, $base_salary, $basic_salary, $net_salary);
            $contributionBaseSalary = $this->wage_to_pay_contribution($selectedBaseSalary, $rule);
    
            $this->db->where('rule_id', $rule['id']);
            $this->db->where('min_salary <=', $contributionBaseSalary);
            $this->db->group_start();
                $this->db->where('max_salary >=', $contributionBaseSalary);
                $this->db->or_where('max_salary IS NULL', null, false);
            $this->db->group_end();
            $this->db->where('status', 1);
    
            $slab = $this->db
                ->get('payroll_statutory_rule_slabs')
                ->row_array();
    
            if (!$slab) {
                continue;
            }
    
            if ($slab['employee_applicable'] == 1) {
    
                $employee_amount = 0;
    
                if ($slab['employee_calculation_type'] == 'percentage') {
                    $employee_amount = ($contributionBaseSalary * $slab['employee_value']) / 100;
                } elseif ($slab['employee_calculation_type'] == 'fixed') {
                    $employee_amount = $slab['employee_value'];
                }
    
                if ($employee_amount > 0) {
                    $result['employee_deductions'][] = array(
                        'name' => $rule['rule_name'] . ' Employee Share',
                        'amount' => round($employee_amount, 2),
                        'rule_id' => $rule['id'],
                        'slab_id' => $slab['id'],
                        'base_salary' => $contributionBaseSalary
                    );
                }
            }
    
            if ($slab['employer_applicable'] == 1) {
    
                $employer_amount = 0;
    
                if ($slab['employer_calculation_type'] == 'percentage') {
                    $employer_amount = ($contributionBaseSalary * $slab['employer_value']) / 100;
                } elseif ($slab['employer_calculation_type'] == 'fixed') {
                    $employer_amount = $slab['employer_value'];
                }
    
                if ($employer_amount > 0) {
                    $result['employer_contributions'][] = array(
                        'name' => $rule['rule_name'] . ' Employer Share',
                        'amount' => round($employer_amount, 2),
                        'rule_id' => $rule['id'],
                        'slab_id' => $slab['id'],
                        'base_salary' => $contributionBaseSalary
                    );
                }
            }
        }
    
        return $result;
    }
    
    
    private function calculate_income_tax_amount($monthly_taxable_salary, $payroll_date)
    {
        $tax_year = $this->db
            ->where('start_date <=', $payroll_date)
            ->where('end_date >=', $payroll_date)
            ->where('status', 1)
            ->get('payroll_tax_years')
            ->row_array();
    
        if (!$tax_year) {
            return array(
                'amount' => 0,
                'tax_year_id' => null,
                'tax_slab_id' => null
            );
        }
    
        $annual_income = $monthly_taxable_salary * 12;
    
        $this->db->where('tax_year_id', $tax_year['id']);
        $this->db->where('min_annual_income <=', $annual_income);
        $this->db->group_start();
            $this->db->where('max_annual_income >=', $annual_income);
            $this->db->or_where('max_annual_income IS NULL', null, false);
        $this->db->group_end();
        $this->db->where('status', 1);
    
        $slab = $this->db
            ->get('payroll_income_tax_slabs')
            ->row_array();
    
        if (!$slab) {
            return array(
                'amount' => 0,
                'tax_year_id' => $tax_year['id'],
                'tax_slab_id' => null
            );
        }
    
        $annual_tax = $slab['fixed_tax'] + (($annual_income - $slab['taxable_amount_above']) * $slab['tax_percentage'] / 100);
    
        if ($annual_tax < 0) {
            $annual_tax = 0;
        }
    
        return array(
            'amount' => round($annual_tax / 12, 2),
            'tax_year_id' => $tax_year['id'],
            'tax_slab_id' => $slab['id']
        );
    }

    private function post_minimum_salary_adjustment_to_pettycash($user_id, $campus_id, $amount, $month, $year, $payroll_id, $is_reversal = false)
    {
        $amount = round((float) $amount, 2);
        if ($amount <= 0) {
            return;
        }

        $pettycash = $this->db
            ->where('assign_to', $user_id)
            ->get('petty_cash_college_wise')
            ->row_array();

        if (!$pettycash) {
            return;
        }

        if ($is_reversal) {
            $this->db->set('remaining_amount', 'remaining_amount - ' . $amount, false);
            $debitCredit = 'C';
            $reason = 'Reversal of salary adjustment for ' . $month . '-' . $year . ' Payroll ID ' . $payroll_id;
        } else {
            $this->db->set('remaining_amount', 'remaining_amount + ' . $amount, false);
            $debitCredit = 'D';
            $reason = 'Salary adjustment for ' . $month . '-' . $year . ' Payroll ID ' . $payroll_id;
        }

        $this->db->where('id', $pettycash['id']);
        $this->db->update('petty_cash_college_wise');

        $this->db->insert('petty_cash_history', array(
            'campus_id' => $campus_id,
            'user_id' => $user_id,
            'amount_given' => $amount,
            'debit_credit' => $debitCredit,
            'transaction_pettycash_account' => $pettycash['id'],
            'status' => '1',
            'reason' => $reason,
            'transaction_by' => $this->session->userdata('name'),
            'created_at' => date('Y-m-d H:i:s')
        ));
    }

    private function get_minimum_salary_adjustments_by_payroll($payrollIds)
    {
        $payrollIds = array_filter(array_map('intval', (array) $payrollIds));
        if (empty($payrollIds)) {
            return array();
        }

        $minimumAdjustments = $this->db
            ->select('payroll_id, SUM(amount) AS adjustment_amount')
            ->where_in('payroll_id', $payrollIds)
            ->where_in('name', array('Minimum Salary Adjustment', 'Salary Adjustment'))
            ->group_by('payroll_id')
            ->get('payroll_earn_deducs')
            ->result_array();

        $adjustmentByPayroll = array();
        foreach ($minimumAdjustments as $minimumAdjustment) {
            $adjustmentByPayroll[(int) $minimumAdjustment['payroll_id']] = (float) $minimumAdjustment['adjustment_amount'];
        }

        return $adjustmentByPayroll;
    }

    private function post_minimum_salary_adjustment_for_payroll_rows($payrollRows, $campus_id, $is_reversal = false)
    {
        if (empty($payrollRows)) {
            return;
        }

        $payrollIds = array();
        foreach ($payrollRows as $payrollRow) {
            if (!isset($payrollRow['id'])) {
                continue;
            }
            $payrollIds[] = (int) $payrollRow['id'];
        }

        $adjustmentByPayroll = $this->get_minimum_salary_adjustments_by_payroll($payrollIds);
        if (empty($adjustmentByPayroll)) {
            return;
        }

        foreach ($payrollRows as $payrollRow) {
            $payrollId = isset($payrollRow['id']) ? (int) $payrollRow['id'] : 0;
            if ($payrollId <= 0) {
                continue;
            }

            $adjustmentAmount = isset($adjustmentByPayroll[$payrollId]) ? (float) $adjustmentByPayroll[$payrollId] : 0;
            if ($adjustmentAmount <= 0) {
                continue;
            }

            $this->post_minimum_salary_adjustment_to_pettycash(
                (int) $payrollRow['user_id'],
                $campus_id,
                $adjustmentAmount,
                $payrollRow['payroll_month'],
                $payrollRow['payroll_year'],
                $payrollId,
                $is_reversal
            );
        }
    }
    
    

    public function storepayroll()
    {

        $user_id   = $this->input->post('user_id');
        $campus_id = $this->input->post('campus_id');
        $month     = $this->input->post('month');
        $year      = $this->input->post('year');
    
        $earnings       = $this->input->post('earningstype') ?: array();
        $earningsamount = $this->input->post('earningsValue') ?: array();
    
        $deduct       = $this->input->post('deductionstype') ?: array();
        $deductamount = $this->input->post('deductionsValue') ?: array();
        $loanallIds   = $this->input->post('loanId') ?: array();
        $applyStatutoryRules = $this->user_applies_statutory_rules($user_id);
    
        $employerNames   = $this->input->post('employer_contribution_name') ?: array();
        $employerAmounts = $this->input->post('employer_contribution_amount') ?: array();
        $employerRuleIds = $this->input->post('employer_contribution_rule_id') ?: array();
        $employerSlabIds = $this->input->post('employer_contribution_slab_id') ?: array();
        $employerBaseSalaries = $this->input->post('employer_contribution_base_salary') ?: array();

        if (!$applyStatutoryRules) {
            $statutoryRuleNames = $this->db
                ->select('rule_name')
                ->where('status', 1)
                ->get('payroll_statutory_rules')
                ->result_array();
            $statutoryNames = array();
            foreach ($statutoryRuleNames as $ruleName) {
                $statutoryNames[] = trim($ruleName['rule_name']);
                $statutoryNames[] = trim($ruleName['rule_name']) . ' Employee Share';
            }

            $filteredDeduct = array();
            $filteredDeductAmount = array();
            $filteredLoanIds = array();
            for ($x = 0; $x < count($deduct); $x++) {
                $name = isset($deduct[$x]) ? trim($deduct[$x]) : '';
                if (in_array($name, $statutoryNames)) {
                    continue;
                }
                $filteredDeduct[] = isset($deduct[$x]) ? $deduct[$x] : '';
                $filteredDeductAmount[] = isset($deductamount[$x]) ? $deductamount[$x] : 0;
                $filteredLoanIds[] = isset($loanallIds[$x]) ? $loanallIds[$x] : 0;
            }
            $deduct = $filteredDeduct;
            $deductamount = $filteredDeductAmount;
            $loanallIds = $filteredLoanIds;

            $employerNames = array();
            $employerAmounts = array();
            $employerRuleIds = array();
            $employerSlabIds = array();
            $employerBaseSalaries = array();
        }
    
        $basic_salary    = (float) $this->input->post('basic_salary');
        $gross_salary    = (float) $this->input->post('gross_salary');
        $earned_salary   = (float) $this->input->post('net_salary');
        $deductions      = (float) $this->input->post('deduction_salary');
        $earnings_total  = (float) $this->input->post('earing_salary');
        $tax_salary      = (float) $this->input->post('tax_salary');
        if (!$applyStatutoryRules) {
            $deductions = 0;
            foreach ($deductamount as $deductAmount) {
                $deductions += (float) $deductAmount;
            }
        }
        $employeeSalaryAdjustment = $this->db
            ->select('salary_adjustment')
            ->where('user_id', $user_id)
            ->get('users')
            ->row_array();
        $minimumSalaryAdjustment = (float) @$employeeSalaryAdjustment['salary_adjustment'];

        $salaryPettycash = $this->db
            ->where('assign_to', $user_id)
            ->get('petty_cash_college_wise')
            ->row_array();

        if ($minimumSalaryAdjustment > 0 && !$salaryPettycash) {
            $this->session->set_flashdata('error', 'Payroll not generated. Please create petty cash account for this employee first.');
            redirect(site_url() . '/salary/generate_salary/' . $user_id . '/' . $campus_id . '/' . $month . '/' . $year);
            return;
        }
    
        if ($gross_salary <= 0) {
            $gross_salary = $basic_salary + $earnings_total;
        }

        $payrollMonthDate = date('Y-m-d', strtotime($year . '-' . $month . '-01'));
        $daysInMonth = (int) date('t', strtotime($payrollMonthDate));
        if ($daysInMonth <= 0) {
            $daysInMonth = 30;
        }

        $countedDays = (float) $this->input->post('total_days');
        if ($countedDays > $daysInMonth) {
            $countedDays = $daysInMonth;
        }

        $earnedBeforeIncentives = ($gross_salary / $daysInMonth) * $countedDays;
        $earned_salary = round($earnedBeforeIncentives + $earnings_total + $minimumSalaryAdjustment - $deductions - $tax_salary);
    
        $this->db->trans_start();
    
        /*
         * Optional but recommended:
         * Agar same month/year/user payroll pehle se bana hua hai to duplicate na bane.
         */
        $oldPayroll = $this->db
            ->where('user_id', $user_id)
            ->where('payroll_month', $month)
            ->where('payroll_year', $year)
            ->get('payroll')
            ->row_array();
    
        if ($oldPayroll) {
            $oldPayrollId = $oldPayroll['id'];
            $oldPayrollWasDisbursed = !empty($oldPayroll['expense_id']) && isset($oldPayroll['disburse_through']) && $oldPayroll['disburse_through'] !== 'pending';
            if ($oldPayrollWasDisbursed) {
                $this->post_minimum_salary_adjustment_for_payroll_rows(array($oldPayroll), $campus_id, true);
            }
    
            $this->db->where('payroll_id', $oldPayrollId)->delete('payroll_earn_deducs');
            $this->db->where('payroll_id', $oldPayrollId)->delete('payroll_statutory_contributions');
            $this->db->where('payroll_id', $oldPayrollId)->delete('payroll_income_tax_calculations');
    
            $this->db
                ->where('payroll_id', $oldPayrollId)
                ->where('paid_at', 'salary')
                ->update('loan_plan', array(
                    'amount_paid' => 0,
                    'paid_at'    => NULL,
                    'payroll_id' => NULL
                ));
    
            $this->db->where('id', $oldPayrollId)->delete('payroll');
        }
    
        $payrollData = array(
            'user_id'        => $user_id,
            'payroll_month'  => $month,
            'payroll_year'   => $year,
            'basic_salary'   => $basic_salary,
            'gross_salary'   => $gross_salary,
            'earned_salary'  => $earned_salary,
            'deductions'     => $deductions,
            'earnings'       => $earnings_total,
            'tax'            => $tax_salary,
            'no_of_days'     => $this->input->post('total_days'),
            'no_of_absents'  => $this->input->post('total_absents'),
            'no_of_lates'    => $this->input->post('total_lates'),
            'created_by'     => $this->session->userdata('user_id')
        );
    
        $this->db->insert('payroll', $payrollData);
        $insertId = $this->db->insert_id();
    
        if ($insertId) {
            /*
             * Earnings Save
             */
            for ($x = 0; $x < count($earnings); $x++) {
    
                $name = trim($earnings[$x]);
                $amount = isset($earningsamount[$x]) ? (float) $earningsamount[$x] : 0;
    
                if ($name == '' && $amount <= 0) {
                    continue;
                }
    
                $this->db->insert('payroll_earn_deducs', array(
                    'payroll_id' => $insertId,
                    'name'       => $name,
                    'type_id'    => '0',
                    'amount'     => $amount,
                    'created_by' => $this->session->userdata('user_id')
                ));
            }
    
            /*
             * Deductions Save
             * Is mein EOBI Employee Share bhi aa jayega,
             * kyunki humne generate_salary view mein deductions array mein push kiya hai.
             */
            for ($x = 0; $x < count($deduct); $x++) {
    
                $name = trim($deduct[$x]);
                $amount = isset($deductamount[$x]) ? (float) $deductamount[$x] : 0;
                $loanId = isset($loanallIds[$x]) ? (int) $loanallIds[$x] : 0;
    
                if ($name == '' && $amount <= 0) {
                    continue;
                }
    
                if ($loanId > 0) {
                    $this->db->where('id', $loanId);
                    $this->db->update('loan_plan', array(
                        'amount_paid' => $amount,
                        'paid_at'     => 'salary',
                        'payroll_id'  => $insertId
                    ));
                }
    
                $this->db->insert('payroll_earn_deducs', array(
                    'payroll_id' => $insertId,
                    'name'       => $name,
                    'type_id'    => '1',
                    'amount'     => $amount,
                    'created_by' => $this->session->userdata('user_id')
                ));
            }
    
            /*
             * Income Tax ko deduction detail mein bhi save karo
             * agar amount > 0 hai.
             */
            if ($tax_salary > 0) {
                $this->db->insert('payroll_earn_deducs', array(
                    'payroll_id' => $insertId,
                    'name'       => 'Income Tax',
                    'type_id'    => '1',
                    'amount'     => $tax_salary,
                    'created_by' => $this->session->userdata('user_id')
                ));
            }

            if ($minimumSalaryAdjustment > 0) {
                $this->db->insert('payroll_earn_deducs', array(
                    'payroll_id' => $insertId,
                    'name'       => 'Salary Adjustment',
                    'type_id'    => '2',
                    'amount'     => $minimumSalaryAdjustment,
                    'created_by' => $this->session->userdata('user_id')
                ));
            }
    
            /*
             * Employer Contributions Save
             * Ye net salary se minus nahi honge.
             */
            for ($x = 0; $x < count($employerNames); $x++) {
    
                $name = trim($employerNames[$x]);
                $amount = isset($employerAmounts[$x]) ? (float) $employerAmounts[$x] : 0;
    
                if ($name == '' && $amount <= 0) {
                    continue;
                }
    
                $ruleId = isset($employerRuleIds[$x]) ? (int) $employerRuleIds[$x] : 0;
                $slabId = isset($employerSlabIds[$x]) && $employerSlabIds[$x] != '' ? (int) $employerSlabIds[$x] : NULL;
                $contributionBaseSalary = isset($employerBaseSalaries[$x]) ? (float) $employerBaseSalaries[$x] : $gross_salary;
    
                $rule = array();
                if ($ruleId > 0) {
                    $rule = $this->db
                        ->where('id', $ruleId)
                        ->get('payroll_statutory_rules')
                        ->row_array();
                }
    
                $this->db->insert('payroll_statutory_contributions', array(
                    'payroll_id'                => $insertId,
                    'user_id'                   => $user_id,
                    'rule_id'                   => $ruleId,
                    'slab_id'                   => $slabId,
                    'rule_name'                 => $name,
                    'rule_code'                 => isset($rule['rule_code']) ? $rule['rule_code'] : '',
                    'calculation_base'          => isset($rule['calculation_base']) ? $rule['calculation_base'] : 'gross_salary',
                    'base_salary'               => $contributionBaseSalary,
    
                    'employee_applicable'       => 0,
                    'employee_calculation_type' => 'none',
                    'employee_value'            => 0,
                    'employee_amount'           => 0,
    
                    'employer_applicable'       => 1,
                    'employer_calculation_type' => '',
                    'employer_value'            => 0,
                    'employer_amount'           => $amount,
    
                    'created_at'                => date('Y-m-d H:i:s')
                ));
            }
    
            /*
             * Income Tax Audit Save
             */
            if ($tax_salary > 0) {
    
                $payrollDate = date("Y-m-t", strtotime($year . '-' . $month . '-01'));
    
                $taxYear = $this->db
                    ->where('start_date <=', $payrollDate)
                    ->where('end_date >=', $payrollDate)
                    ->where('status', 1)
                    ->get('payroll_tax_years')
                    ->row_array();
    
                if ($taxYear) {
    
                    $annualIncome = $gross_salary * 12;
    
                    $this->db->where('tax_year_id', $taxYear['id']);
                    $this->db->where('min_annual_income <=', $annualIncome);
                    $this->db->group_start();
                        $this->db->where('max_annual_income >=', $annualIncome);
                        $this->db->or_where('max_annual_income IS NULL', null, false);
                    $this->db->group_end();
                    $this->db->where('status', 1);
    
                    $taxSlab = $this->db
                        ->get('payroll_income_tax_slabs')
                        ->row_array();
    
                    $this->db->insert('payroll_income_tax_calculations', array(
                        'payroll_id'              => $insertId,
                        'user_id'                 => $user_id,
                        'tax_year_id'             => $taxYear['id'],
                        'tax_slab_id'             => isset($taxSlab['id']) ? $taxSlab['id'] : NULL,
                        'monthly_taxable_salary'  => $gross_salary,
                        'projected_annual_income' => $annualIncome,
                        'fixed_tax'               => isset($taxSlab['fixed_tax']) ? $taxSlab['fixed_tax'] : 0,
                        'taxable_amount_above'    => isset($taxSlab['taxable_amount_above']) ? $taxSlab['taxable_amount_above'] : 0,
                        'tax_percentage'          => isset($taxSlab['tax_percentage']) ? $taxSlab['tax_percentage'] : 0,
                        'annual_tax_amount'       => $tax_salary * 12,
                        'monthly_tax_amount'      => $tax_salary,
                        'created_at'              => date('Y-m-d H:i:s')
                    ));
                }
            }

        }
    
        $this->db->trans_complete();
    
        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('error', 'Payroll not generated. Please try again.');
            redirect(site_url() . '/salary');
            return;
        }
    
        if ($campus_id != '') {
            $this->db->select('us.* , campuses.campus_name,(select earned_salary from payroll where 
                user_id = us.user_id and payroll_month = "'.$month.'" and payroll_year = "'.$year.'" group by user_id) as count');
            $this->db->from('users us');
            $this->db->join('campuses','us.campus_id = campuses.campus_id ','inner');
            $this->db->where(array('us.campus_id'=>$campus_id,'us.status'=>1));
            $data['staff'] = $this->db->get()->result_array();
            $data['campus_id'] = $campus_id;
        }
    
        $data['month'] = $month;
        $data['year'] = $year;
    
        $this->session->set_flashdata('message', 'Payroll generated Successfully.');
    
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('salary/salary_list', $data);
        $this->load->view('inc/footer');
    }

    public function salary_view($id, $month, $year)

    {

    // Agar numeric month ho to convert karo

    if (is_numeric($month)) {

        $month = date("M", mktime(0, 0, 0, (int)$month, 10));

    }
        $this->db->select('users.* , payroll.* , designations.designation_name , campuses.campus_name');
        $this->db->from('users');
        $this->db->join('payroll','payroll.user_id = users.user_id ','left');
        $this->db->join('designations','designations.designation_id = users.designation_id ','left');
        $this->db->join('campuses','campuses.campus_id = users.campus_id ','left');
        $this->db->where(array('users.user_id'=>$id,'users.status'=>1,'payroll.payroll_month'=>$month,'payroll.payroll_year'=>$year));
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
			(select sum(amount) from payroll_earn_deducs where payroll_id=payroll.id and type_id = 0) as earnings,
			(select sum(amount) from payroll_earn_deducs where payroll_id=payroll.id and name = "Allowances") as new_user_alownce,
            (select sum(amount) from payroll_earn_deducs where payroll_id=payroll.id and name in ("Minimum Salary Adjustment", "Salary Adjustment")) as minimum_salary_adjustment,
			users.first_name,users.last_name,campuses.campus_name,user_allowances.amount as user_alownce,designations.designation_name as designation,departments.department_name as department,campuses.campus_name');
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
        foreach ($data['salary'] as $key => $salary) {
            $minimumAdjustment = (float) @$salary['minimum_salary_adjustment'];
            if ($minimumAdjustment > 0) {
                $data['salary'][$key]['earned_salary'] = (float) $salary['earned_salary'] - $minimumAdjustment;
            }
        }
        $data['month'] = $month;
        $data['year'] = $year;
        $data['my_campus'] = $this->input->post('campus_id');

        $data['disbursed'] = $this->db->get_where("expenses","expenses.campus_id = '$course_id' and expenses.salary_year = '$year' and expenses.salary_month = '$month' and expenses.approved_status = '1'")->result_array();
        $data['stat_rules'] = $this->db
            ->order_by('id', 'DESC')
            ->get_where('payroll_statutory_rules','status = 1')
            ->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('salary/salary_list_report',$data);
        $this->load->view('inc/footer');
    }

    public function minimum_salary_adjustment_report(){

        $data['campuses'] = $this->db->get_where('campuses',array('status'=>1))->result_array();

        $course_id = $this->input->post('campus_id');
        $month = $this->input->post('to_date');

        $month = date("M", strtotime($month));
        $year = date("Y", strtotime($this->input->post('to_date')));

        $this->db->select('payroll.*,(select sum(amount) from payroll_earn_deducs where payroll_id=payroll.id and name like("%Loan installment%")) as loan,
            (select sum(amount) from payroll_earn_deducs where payroll_id=payroll.id and name like("%Advance installment%")) as advance,
            (select sum(amount) from payroll_earn_deducs where payroll_id=payroll.id and name like("%Special%")) as special,
            (select sum(amount) from payroll_earn_deducs where payroll_id=payroll.id and type_id = 0) as earnings,
            (select sum(amount) from payroll_earn_deducs where payroll_id=payroll.id and name = "Allowances") as new_user_alownce,
            (select sum(amount) from payroll_earn_deducs where payroll_id=payroll.id and name in ("Minimum Salary Adjustment", "Salary Adjustment")) as minimum_salary_adjustment,
            users.first_name,users.last_name,campuses.campus_name,user_allowances.amount as user_alownce,designations.designation_name as designation,departments.department_name as department,campuses.campus_name');
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
        $data['month'] = $month;
        $data['year'] = $year;
        $data['my_campus'] = $this->input->post('campus_id');
        $data['minimum_adjustment_report'] = true;

        $data['disbursed'] = $this->db->get_where("expenses","expenses.campus_id = '$course_id' and expenses.salary_year = '$year' and expenses.salary_month = '$month' and expenses.approved_status = '1'")->result_array();
        $data['stat_rules'] = $this->db
            ->order_by('id', 'DESC')
            ->get_where('payroll_statutory_rules','status = 1')
            ->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('salary/salary_list_report',$data);
        $this->load->view('inc/footer');
    }

    public function delete_salary($user_id,$month,$year){

        //$year = date("Y", strtotime("-1 months"));
        $payroll = $this->db->get_where('payroll',"payroll_month = '$month' and payroll_year = '$year' and user_id = '$user_id'")->row();
        if (!$payroll) {
            redirect('salary/salary_list');
            return;
        }

        if (!empty($payroll->expense_id) && isset($payroll->disburse_through) && $payroll->disburse_through !== 'pending') {
            $this->post_minimum_salary_adjustment_for_payroll_rows(array(array(
                'id' => $payroll->id,
                'user_id' => $payroll->user_id,
                'payroll_month' => $payroll->payroll_month,
                'payroll_year' => $payroll->payroll_year,
            )), $payroll->campus_id, true);
        }

        $this->db->set('amount_paid',"0");
        $this->db->set('paid_at',null);
        $this->db->set('payroll_id',null);
        $this->db->where('payroll_id',$payroll->id);
        $this->db->update('loan_plan');

        $this->db->where('id', $payroll->id);
        $this->db->delete('payroll');

        redirect('salary/salary_list');
    }

    public function delete_expense($exp_id){
        $payrollRows = $this->db
            ->select('id, user_id, payroll_month, payroll_year, campus_id')
            ->where('expense_id', $exp_id)
            ->get('payroll')
            ->result_array();

        if (!empty($payrollRows)) {
            $this->post_minimum_salary_adjustment_for_payroll_rows($payrollRows, $payrollRows[0]['campus_id'], true);
        }

        $this->db->set('disburse_through', 'pending');
        $this->db->set('expense_id', NULL);
        $this->db->where('expense_id', $exp_id);
        $this->db->update("payroll");

        $this->db->where('expense_id', $exp_id);
        $this->db->delete('expenses');
        
        $this->db->where('salary_expense_ids',$exp_id);
        $this->db->update('bank_reconciliation_statement',array('salary_expense_ids'=>null));
        
        redirect('salary/salary_report');
    }

    public function insert_expense()
    {

        $payroll_ids=$this->input->post('payroll_ids');
        $payrollIdList = array_filter(array_map('intval', explode(',', (string) $payroll_ids)));
        $type=$this->input->post('type');
        $month=$this->input->post('month');
        $year=$this->input->post('year');
        $campus=$this->input->post('campus');
        $receivable_amount=$this->input->post('disburse_amount');
        $pettycash = my_pettycash();
        if ($pettycash >= $receivable_amount || $type == "bank") {
            $this->db->set('title', 'Salary Disburse for ' . $month . '-' . $year);
            $this->db->set('date', date('Y-m-d'));
            $this->db->set('amount', $receivable_amount);
            $this->db->set('purpose', 'Salary Given to Employees');
            $this->db->set('actual_date', date('Y-m-d H:i:s'));
            $this->db->set('image', '');
            $this->db->set('expense_category_id', '36');
            $this->db->set('approved_status', '1');
            $this->db->set('paid_type', $type);
            $this->db->set('campus_id', $campus);
            $this->db->set('add_by_id', $this->session->userdata('user_id'));
            $this->db->set('add_by', $this->session->userdata('name'));
            $this->db->insert('expenses');

            $insert_id = $this->db->insert_id();
            $this->db->set("disburse_through",$type);
            $this->db->set("expense_id",$insert_id);
            $this->db->where_in("id",$payrollIdList);
            $this->db->update("payroll");

            if (!empty($payrollIdList)) {
                $payrollRows = $this->db
                    ->select('id, user_id, payroll_month, payroll_year')
                    ->where_in('id', $payrollIdList)
                    ->get('payroll')
                    ->result_array();

                $this->post_minimum_salary_adjustment_for_payroll_rows($payrollRows, $campus);
            }

            $data = array(
                'success' => 'Salary posted',
                'error' => '',
            );
            echo json_encode($data);
        }
        else  {
            $data = array(
                'error' => 'Disburse Amount is : '.$receivable_amount." Your Petty Cash is : ".$pettycash
            );
            echo json_encode($data);
        }
    }

    public function createDateRangeArray($strDateFrom,$strDateTo)
    {
        $aryRange=array();

        $iDateFrom=mktime(1,0,0,substr($strDateFrom,5,2),     substr($strDateFrom,8,2),substr($strDateFrom,0,4));
        $iDateTo=mktime(1,0,0,substr($strDateTo,5,2),     substr($strDateTo,8,2),substr($strDateTo,0,4));

        if ($iDateTo>=$iDateFrom)
        {
            array_push($aryRange,date('Y-m-d',$iDateFrom)); // first entry
            while ($iDateFrom<$iDateTo)
            {
                $iDateFrom+=86400; // add 24 hours
                array_push($aryRange,date('Y-m-d',$iDateFrom));
            }
        }
        return $aryRange;
    }
    
    public function remove_contributions()
    {
        $ids = $this->input->post('contribution_ids');
    
        if (empty($ids)) {
            echo json_encode(['error' => 'No contribution selected']);
            return;
        }
    
        $ids = explode(',', $ids);
    
        $this->db->where_in('id', $ids);
        $this->db->delete('payroll_statutory_contributions');
    
        echo json_encode(['error' => '']);
    }
    
    public function add_contribution_expense()
    {
        $contribution_ids=$this->input->post('contribution_ids');
        $payroll_ids=$this->input->post('payroll_ids');
        $modal_rule_ids=$this->input->post('rule_ids');
        $comment=$this->input->post('purpose');
        $receivable_amount=$this->input->post('amount');
        $rule_id = explode(',',$modal_rule_ids);
        $payroll_id = explode(',',$payroll_ids);
        
        $payroll = $this->db->get_where('payroll','id = '.$payroll_id[0])->row_array();
        $exp_cat = $this->db->get_where('payroll_statutory_rules','id = '.$rule_id[0])->row_array();
        $expense_category = json_decode($exp_cat['expense_category']);
        
        
        //load the helper
        $this->load->helper('form');

        //Configure
        //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
        $config['upload_path'] = 'uploads/';

        // set the filter image types
        $config['allowed_types'] = 'gif|jpg|png';

        //load the upload library
        $this->load->library('upload', $config);

        $this->upload->initialize($config);

        $this->upload->set_allowed_types('*');

        $data['upload_data'] = '';

        //if not successful, set the error message
        if (!$this->upload->do_upload('image')) {

            $image = '';

        }
        else
        {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $image = $data['upload_data']['file_name'];
            }
        }
        
        
        
        
        $pettycash = my_pettycash();
        if ($pettycash >= $receivable_amount || $type == "bank") {
            $this->db->set('title', $exp_cat['rule_name'].'Expense for ' . $payroll['payroll_month'] . '-' . $payroll['payroll_year']);
            $this->db->set('date', date('Y-m-d'));
            $this->db->set('amount', $receivable_amount);
            $this->db->set('purpose', $exp_cat['rule_name'].' Paid for Employees');
            $this->db->set('actual_date', date('Y-m-d H:i:s'));
            $this->db->set('image', $image);
            $this->db->set('expense_category_id', $expense_category[count($expense_category)-1]);
            $this->db->set('approved_status', '1');
            $this->db->set('paid_type', 'cash');
            $this->db->set('add_by_id', $this->session->userdata('user_id'));
            $this->db->set('add_by', $this->session->userdata('name'));
            $this->db->insert('expenses');
            $insert_id = $this->db->insert_id();
            
            $this->db->set('remaining_amount', 'remaining_amount -'.$receivable_amount .'',false);
            $this->db->where('assign_to', $this->session->userdata('user_id'));
            $this->db->update('petty_cash_college_wise');

            
            $this->db->set("expense_id",$insert_id);
            $this->db->where_in("id",explode(",",$contribution_ids));
            $this->db->update("payroll_statutory_contributions");

            $data = array(
                'success' => 'Salary posted',
                'error' => '',
            );
            echo json_encode($data);
        }
        else  {
            $data = array(
                'error' => 'Disburse Amount is : '.$receivable_amount." Your Petty Cash is : ".$pettycash
            );
            echo json_encode($data);
        }
    }
}
