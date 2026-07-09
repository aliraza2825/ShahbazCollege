<?php

function totalStudentsFee($class_id)
{
    $ci =& get_instance();
    $class_students_ids = $ci->db->get_where('students', array('class_id'=>$class_id,'status'=>1))->result_array();
    $class_students_ids_arrays = array();
    $class_contractors_ids_arrays = array();
    //SELECT STUDENTS IDS
    foreach($class_students_ids as $class_students_id)
    {
        array_push($class_students_ids_arrays, $class_students_id['student_id']);
    }

    if(count($class_students_ids_arrays)>0)
    {
        echo getTotalStudentsAmount($class_students_ids_arrays);
    }
    else
    {
        echo 0;
    }
}

function totalDeactiveStudentsFee($class_id)
{
    $ci =& get_instance();
    $class_students_ids = $ci->db->get_where('students', array('class_id'=>$class_id,'status'=>0))->result_array();
    $class_students_ids_arrays = array();
    $class_contractors_ids_arrays = array();
    //SELECT STUDENTS IDS
    foreach($class_students_ids as $class_students_id)
    {
        array_push($class_students_ids_arrays, $class_students_id['student_id']);
    }

    if(count($class_students_ids_arrays)>0)
    {
        echo getTotalStudentsAmount($class_students_ids_arrays);
    }
    else
    {
        echo 0;
    }
}

function getTotalStudentsAmount($class_students_ids_arrays)
{
    $ci =& get_instance();
    $ci->db->select_sum('amount');
    $ci->db->where_in('student_id', $class_students_ids_arrays);
    $query = $ci->db->get('payments')->row()->amount;
    return $query;
}

function totalStudentsDecidedFee($class_id)
{
    $ci =& get_instance();
    $students = $ci->db->get_where('students', array('class_id'=>$class_id,'status'=>1))->result_array();

    $totalFee=0;
    foreach($students as $student)
    {
        $totalFee+=$student['total_fee'];
    }
    return $totalFee;
}

function totalDeactiveStudentsDecidedFee($class_id)
{
    $ci =& get_instance();
    $students = $ci->db->get_where('students', array('class_id'=>$class_id,'status'=>0))->result_array();

    $totalFee=0;
    foreach($students as $student)
    {
        $totalFee+=$student['total_fee'];
    }
    return $totalFee;
}

function totalContractorsFee($class_id)
{
    $ci =& get_instance();
    $class_students_ids = $ci->db->get_where('students', array('class_id'=>$class_id))->result_array();
    $class_contractors_ids_arrays = array();
    //SELECT CONTRACTOR IDS
    foreach($class_students_ids as $class_contractors_id)
    {
        if($class_contractors_id['contractor_id']>0){
            if(!in_array($class_contractors_id['contractor_id'], $class_contractors_ids_arrays))
            {
                array_push($class_contractors_ids_arrays, $class_contractors_id['contractor_id']);
            }
        }
    }

    if(count($class_contractors_ids_arrays)>0)
    {
        echo getTotalContractorsAmount($class_contractors_ids_arrays);
    }
    else
    {
        echo 0;
    }
}

function getTotalContractorsAmount($class_contractors_ids_arrays)
{
    $ci =& get_instance();
    $ci->db->select_sum('amount');
    $ci->db->where_in('contractor_id', $class_contractors_ids_arrays);
    $query = $ci->db->get('payments')->row()->amount;
    return $query;
}

function totalStudentsPaidFee($class_id)
{
    $ci =& get_instance();
    $class_students_ids = $ci->db->get_where('students', array('class_id'=>$class_id,'status'=>1))->result_array();
    $class_students_ids_arrays = array();
    foreach($class_students_ids as $class_students_id)
    {
        array_push($class_students_ids_arrays, $class_students_id['student_id']);
    }
    if(count($class_students_ids_arrays)>0)
    {
        echo getTotalPaidAmount($class_students_ids_arrays);
    }
    else
    {
        echo 0;
    }
}

function totalDeactiveStudentsPaidFee($class_id)
{
    $ci =& get_instance();
    $class_students_ids = $ci->db->get_where('students', array('class_id'=>$class_id,'status'=>0))->result_array();
    $class_students_ids_arrays = array();
    foreach($class_students_ids as $class_students_id)
    {
        array_push($class_students_ids_arrays, $class_students_id['student_id']);
    }
    if(count($class_students_ids_arrays)>0)
    {
        echo getTotalPaidAmount($class_students_ids_arrays);
    }
    else
    {
        echo 0;
    }
}

function getTotalPaidAmount($class_students_ids_arrays)
{
    $ci =& get_instance();
    $ci->db->select_sum('actual_amount');
    $ci->db->where_in('student_id', $class_students_ids_arrays);
    $query = $ci->db->get('payments')->row()->actual_amount;
    return $query;
}

function totalDeactiveStudents($class_id)
{
    $ci =& get_instance();
    $query = $ci->db->get_where('students',array('class_id'=>$class_id,'status'=>0))->result_array();
    return count($query);
}

function checkUserAccess()
{

    $ci =& get_instance();
    $user_id  = $ci->session->userdata('user_id');
    if($user_id=='')
    {
        redirect(base_url());
    }
    $query = $ci->db->get_where('access', array('user_id'=>$user_id))->result_array();
    return $query;
}

function getOriginalPayemntDetails($id)
{
    $ci =& get_instance();

    $query = $ci->db->get_where('payments', array('id'=>$id))->result_array();
    return $query;
}

function getStudentDetails($student_id)
{
    $ci =& get_instance();
    $ci->db->select('students.*, classes.name as class_name');
    $ci->db->from('students');
    $ci->db->join('classes', 'classes.class_id=students.class_id', 'inner');
    $ci->db->where(array('students.student_id'=>$student_id));
    $query = $ci->db->get()->result_array();
    return $query;
}

function totalExpense($campus_id, $from_date, $till_date)
{
    $ci =& get_instance();

    $special_expenses =0;

    //GET SPECIAL CATEGORIES
    $special_categories = $ci->db->get_where('campus_partners',array('campus_id'=>$campus_id))->result_array();
    if($special_categories[0]['special_expense_ids']!=NULL || $special_categories[0]['special_expense_ids']!=''):
        $ci->db->select_sum('amount');
        $ci->db->from('expenses');
        $ci->db->where(array('campus_id'=>$campus_id, 'actual_date>='=>$from_date, 'actual_date<='=>$till_date, 'approved_status'=> '1'));
        
        $special_categories_ids = implode(',',json_decode($special_categories[0]['special_expense_ids']));
        $ci->db->where_in('expense_category_id', $special_categories_ids);

        $query = $ci->db->get()->result_array();

        $special_expenses+= $query[0]['amount'];
    endif;


    //GET ALL EXPENSES
    $ci->db->select_sum('amount');
    $ci->db->from('expenses');
    $ci->db->where(array('campus_id'=>$campus_id, 'actual_date>='=>$from_date, 'actual_date<='=>$till_date, 'approved_status'=> '1'));
    if($special_categories[0]['special_expense_ids']!=NULL || $special_categories[0]['special_expense_ids']!=''):
        //$ci->db->where_not_in('expense_category_id', $special_categories_ids);
    endif;
    $query = $ci->db->get()->result_array();

    $all_expenses = $query[0]['amount'];

    //RESULT WITHOUT SPECIAL EXPENSE
    $total = ($all_expenses-$special_expenses);
    return $total;
}

function gettotalExpense($campus_id, $from_date, $till_date)
{
    $ci =& get_instance();
    
    $special_categories = $ci->db->get_where('campus_partners',array('campus_id'=>$campus_id))->result_array();

    //GET ALL EXPENSES
    $ci->db->select('*');
    $ci->db->from('expenses');
    $ci->db->join('campuses','campuses.campus_id=expenses.campus_id', 'inner');
    $ci->db->where(array('expenses.campus_id'=>$campus_id, 'expenses.actual_date>='=>$from_date, 'expenses.actual_date<='=>$till_date, 'expenses.approved_status'=> '1'));
    if($special_categories[0]['special_expense_ids']!=NULL || $special_categories[0]['special_expense_ids']!=''):
        $special_categories_ids = implode(',',json_decode($special_categories[0]['special_expense_ids']));
        $ci->db->where_not_in('expenses.expense_category_id', $special_categories_ids);
    endif;
    $query = $ci->db->get()->result_array();

    $all_expenses = $query;
    
    return $all_expenses;
}

function specialExpense($campus_id, $from_date, $till_date)
{
    $ci =& get_instance();
    
    //GET SPECIAL CATEGORIES
    $special_categories = $ci->db->get_where('campus_partners',array('campus_id'=>$campus_id))->result_array();

    //GET EXPENSES WITHOUT SPECIAL CATEGORIES
    if($special_categories[0]['special_expense_ids']!=NULL || $special_categories[0]['special_expense_ids']!=''):
        $ci->db->select_sum('amount');
        $ci->db->from('expenses');
        $ci->db->where(array('campus_id'=>$campus_id, 'actual_date>='=>$from_date, 'actual_date<='=>$till_date, 'approved_status'=> '1'));
        if($special_categories[0]['special_expense_ids']!=NULL || $special_categories[0]['special_expense_ids']!=''):
            $special_categories_ids = implode(',',json_decode($special_categories[0]['special_expense_ids']));
            $ci->db->where_in('expense_category_id', $special_categories_ids);
        endif;
        $query = $ci->db->get()->result_array();
        $amount = $query[0]['amount'];
    else:
        $amount = 0;
    endif;
    return $amount;
}

function totalRecovery($campus_id, $from_date, $till_date)
{
    $ci =& get_instance();
    $ci->db->select('payments.*, classes.name as class_name, campuses.campus_name, students.first_name, students.last_name, students.roll_no');
    $ci->db->from('payments');

    $ci->db->join('students', 'students.student_id=payments.student_id', 'inner');
    $ci->db->join('classes', 'classes.class_id=students.class_id', 'inner');
    $ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');

    $ci->db->where(array('classes.campus_id'=>$campus_id, 'payments.actual_paid_date>='=>$from_date, 'payments.actual_paid_date<='=>$till_date, 'payments.paid'=>1,'payments.contract_id'=>0));
    $ci->db->where('payments.merged_challan IS NOT NULL and payments.actual_amount > 0');
    $ci->db->group_by("CASE WHEN payments.merged_challan IS NOT NULL THEN payments.merged_challan else '' end",false);
    $query = $ci->db->get()->result_array();

    $ci->db->select('payments.*, classes.name as class_name, campuses.campus_name, students.first_name, students.last_name, students.roll_no');
    $ci->db->from('payments');

    $ci->db->join('students', 'students.student_id=payments.student_id', 'inner');
    $ci->db->join('classes', 'classes.class_id=students.class_id', 'inner');
    $ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');

    $ci->db->where(array('classes.campus_id'=>$campus_id, 'payments.actual_paid_date>='=>$from_date, 'payments.actual_paid_date<='=>$till_date, 'payments.paid'=>1,'payments.contract_id'=>0));
    $ci->db->where('payments.merged_challan is null');
    $ci->db->or_where('payments.merged_challan IS not NULL and payments.actual_amount = 0');
    $query2 = $ci->db->get()->result_array();

    $arr=array_merge($query,$query2);

    $amount = 0;
    foreach ($arr as $data)
    {
        $amount+=$data['actual_amount'];
    }
    return $amount;

}

function getNotApprovedExpenses($campus_id, $from_date, $till_date)
{
    $ci =& get_instance();

    $ci->db->select_sum('amount');
    $ci->db->from('expenses');
    $ci->db->where(array('campus_id'=>$campus_id, 'actual_date>='=>$from_date, 'actual_date<='=>$till_date, 'approved_status'=> '0'));
    $query = $ci->db->get()->result_array();
    if(count($query)>0)
    {
        $amount = $query[0]['amount'];
    }
    else
    {
        $amount = 0;
    }
    
    return $amount;
}

function totalRecoveryContractors($campus_id, $from_date, $till_date)
{
    $ci =& get_instance();
    $ci->db->select('payments.*, campuses.campus_name, contractors.name, contractors.contractor_id_from_college,contracts.contract_name');
    $ci->db->from('payments');

    $ci->db->join('contracts', 'contracts.contract_id=payments.contract_id', 'inner');
    $ci->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'inner');
    $ci->db->join('campuses', 'contracts.campus_id=campuses.campus_id', 'inner');

    $ci->db->where(array('campuses.campus_id'=>$campus_id, 'payments.actual_paid_date>='=>$from_date, 'payments.actual_paid_date<='=>$till_date, 'payments.paid'=>1));
    $ci->db->where('payments.merged_challan IS NOT NULL and payments.actual_amount > 0');
    $ci->db->group_by("CASE WHEN payments.merged_challan IS NOT NULL THEN payments.merged_challan else '' end",false);

    $query = $ci->db->get()->result_array();


    $ci->db->select('payments.*, campuses.campus_name, contractors.name, contractors.contractor_id_from_college,contracts.contract_name');
    $ci->db->from('payments');

    $ci->db->join('contracts', 'contracts.contract_id=payments.contract_id', 'inner');
    $ci->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'inner');
    $ci->db->join('campuses', 'contracts.campus_id=campuses.campus_id', 'inner');

    $ci->db->where(array('campuses.campus_id'=>$campus_id, 'payments.actual_paid_date>='=>$from_date, 'payments.actual_paid_date<='=>$till_date, 'payments.paid'=>1));
    $ci->db->where('payments.merged_challan is null');
    $ci->db->or_where('payments.merged_challan IS not NULL and payments.actual_amount = 0');
    $query2 = $ci->db->get()->result_array();

    $arr=array_merge($query,$query2);
    $amount = 0;
    foreach ($arr as $data)
    {
        $amount+=$data['actual_amount'];
    }
    return $amount;
}

function getFromDateProfitDistribution($campus_id)
{
    $ci =& get_instance();
    $ci->db->select('*');
    $ci->db->from('profit_distribution_date');
    $ci->db->where('campus_id', $campus_id);
    $ci->db->order_by('date', 'DESC');
    $ci->db->limit(1);
    $query = $ci->db->get()->result_array();

    if(@$query[0]['date']=='')
    {
        return '2000-01-01';
    }
    else
    {
        $stop_date = new DateTime($query[0]['date']);
        //$stop_date->format('Y-m-d');
        $stop_date->modify('+1 day');
        return $stop_date->format('Y-m-d');
    }
}

function getPartners($campus_id)
{
    $ci =& get_instance();
    $ci->db->select('*');
    $ci->db->from('campus_partners');

    $ci->db->where(array('campus_id'=>$campus_id));
    $query = $ci->db->get()->result_array();

    if(count($query)>0)
    {
        $counter = 0;
        $partners = json_decode($query[0]['partners']);
        $partners_count = count($partners)/2;
        $mypartners = '';
        for($i=1; $i<=$partners_count; $i++):

            if($i!==1)
            {
                $counter++;
            }

            $mypartners.= $ci->db->get_where('users', array('user_id'=>$partners[$counter]))->row()->first_name;
            $mypartners.= ' ';
            $mypartners.= $ci->db->get_where('users', array('user_id'=>$partners[$counter]))->row()->last_name;
            $mypartners.= ' = ';
            $counter++;

            $mypartners.= $partners[$counter].' %';
            $mypartners.= '<br />';

        endfor;

        return $mypartners;

    }
    else
    {
        return 'N/A';
    }

}

function showPartnersProfit($campus_id, $net_profit)
{
    $ci =& get_instance();
    $ci->db->select('*');
    $ci->db->from('campus_partners');

    $ci->db->where(array('campus_id'=>$campus_id));
    $query = $ci->db->get()->result_array();

    if(count($query)>0)
    {
        $counter = 0;
        $partners = json_decode($query[0]['partners']);
        $partners_count = count($partners)/2;
        $mypartners = '';

        if($campus_id==7)
        {
            $mypartners.='<div class="col-md-12"><h3>Profit From 2nd Badge</h3></div>';
        }

        for($i=1; $i<=$partners_count; $i++):

            if($i!==1)
            {
                $counter++;
            }
            $mypartners.='<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12"><div class="dashboard-stat purple-plum"><div class="visual"><i class="fa fa-money"></i></div><div class="details"><div class="number">';
            $mypartners.= ($partners[$counter+1]/100)*$net_profit;
            $mypartners.= '</div><div class="desc">';
            $mypartners.= $ci->db->get_where('users', array('user_id'=>$partners[$counter]))->row()->first_name;
            $mypartners.= ' ';
            $mypartners.= $ci->db->get_where('users', array('user_id'=>$partners[$counter]))->row()->last_name;
            $mypartners.='</div></div></div></div>';
            $counter++;

        endfor;

        return $mypartners;

    }
    else
    {
        return '';
    }

}

function showPartnersProfitFirstBadge($campus_id, $net_profit)
{
    $ci =& get_instance();
    $mypartners = '';

    $mypartners.='<div class="col-md-12"><h3>Profit From 1st Badge (50% Shahbaz - 50% Zaheer)</h3></div>';

    $mypartners.='<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12"><div class="dashboard-stat purple-plum"><div class="visual"><i class="fa fa-money"></i></div><div class="details"><div class="number">';
    $mypartners.= (50/100)*$net_profit;
    $mypartners.= '</div><div class="desc">';
    $mypartners.= $ci->db->get_where('users', array('user_id'=>1))->row()->first_name;
    $mypartners.= ' ';
    $mypartners.= $ci->db->get_where('users', array('user_id'=>1))->row()->last_name;
    $mypartners.='</div></div></div></div>';

    $mypartners.='<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12"><div class="dashboard-stat purple-plum"><div class="visual"><i class="fa fa-money"></i></div><div class="details"><div class="number">';
    $mypartners.= (50/100)*$net_profit;
    $mypartners.= '</div><div class="desc">';
    $mypartners.= $ci->db->get_where('users', array('user_id'=>29))->row()->first_name;
    $mypartners.= ' ';
    $mypartners.= $ci->db->get_where('users', array('user_id'=>29))->row()->last_name;
    $mypartners.='</div></div></div></div>';

    $mypartners.='<div class="clearfix"></div>';

    return $mypartners;

}

function showPartnersProfitIslamabad($campus_id, $from_date, $till_date)
{
    $ci =& get_instance();
    $ci->db->select_sum('actual_amount');
    $ci->db->from('payments');
    $ci->db->join('students', 'students.student_id=payments.student_id', 'inner');
    $ci->db->join('classes', 'classes.class_id=students.class_id', 'inner');

    $ci->db->where(array('classes.campus_id'=>$campus_id, 'classes.class_id'=>11, 'payments.actual_paid_date>='=>$from_date, 'payments.actual_paid_date<='=>$till_date, 'payments.paid'=>1));
    $amountFromFirstBadgeIslamabad = $ci->db->get()->result_array();

    $amountFromFirstBadgeIslamabad = $amountFromFirstBadgeIslamabad[0]['actual_amount'];

    echo showPartnersProfitFirstBadge($campus_id, $amountFromFirstBadgeIslamabad);

    $ci =& get_instance();
    $ci->db->select_sum('actual_amount');
    $ci->db->from('payments');
    $ci->db->join('students', 'students.student_id=payments.student_id', 'inner');
    $ci->db->join('classes', 'classes.class_id=students.class_id', 'inner');

    $ci->db->where(array('classes.campus_id'=>$campus_id, 'classes.class_id!='=>11, 'payments.actual_paid_date>='=>$from_date, 'payments.actual_paid_date<='=>$till_date, 'payments.paid'=>1));
    $amountFromAllBadgeExceptBadge1stIslamabad = $ci->db->get()->result_array();

    $ci->db->select_sum('amount');
    $ci->db->from('expenses');
    $ci->db->where(array('date>='=>$from_date, 'date<='=>$till_date, 'campus_id'=>$campus_id));
    $expenses = $ci->db->get()->result_array();

    $expenses = $expenses[0]['amount'];

    //echo $expenses;

    $amountFromAllBadgeExceptBadge1stIslamabad = ($amountFromAllBadgeExceptBadge1stIslamabad[0]['actual_amount']-$expenses);

    //echo $amountFromAllBadgeExceptBadge1stIslamabad;

    echo showPartnersProfit($campus_id, $amountFromAllBadgeExceptBadge1stIslamabad);
}

function profitAmount($campus_id, $from_date, $till_date)
{
    $ci =& get_instance();
    $ci->db->select_sum('actual_amount');
    $ci->db->from('payments');
    $ci->db->join('students', 'students.student_id=payments.student_id', 'inner');
    $ci->db->join('classes', 'classes.class_id=students.class_id', 'inner');

    $ci->db->where(array('classes.campus_id'=>$campus_id, 'classes.class_id'=>11, 'payments.actual_paid_date>='=>$from_date, 'payments.actual_paid_date<='=>$till_date, 'payments.paid'=>1));
    $amountFromFirstBadgeIslamabad = $ci->db->get()->result_array();

    $amountFromFirstBadgeIslamabad = $amountFromFirstBadgeIslamabad[0]['actual_amount'];
    return $amountFromFirstBadgeIslamabad;
}

function getStudentResultRemarks($cnic,$course_id = '')
{
    $bucket_address = 'https://shahbazcollegebucket.s3.ca-central-1.amazonaws.com';
    $cloudfront_address = 'https://d10iw6eujrfvyr.cloudfront.net';

    $ci =& get_instance();

    $ci->db->select('*');
    $ci->db->from('students');
    $ci->db->where('cnic', $cnic);
    if($course_id != '')
    $ci->db->where('course_id', $course_id);
    $student = $ci->db->get()->row_array();

    if (empty($student)) {
        echo '<div style="color:red;">Student not found.</div>';
        return;
    }

    $student_id = $student['student_id'];

    /*
     * 1) Roll number / result records
     */
    $ci->db->select('*');
    $ci->db->from('punjab_council_roll_number');
    $ci->db->where('cnic', $cnic);
    $roll_results = $ci->db->get()->result_array();

    /*
     * 2) Expense records
     * NOTE:
     * Agar expenses table mein council fee ko identify karne ke liye koi column hai,
     * jaise type/action_type/expense_type/council_sequence_id, to yahan where add kar dena.
     */
    $ci->db->select('expenses.*, classes.name as class_name, classes.session, campuses.campus_name as campus_name');
    $ci->db->from('expenses');
    $ci->db->join('classes', 'classes.class_id = expenses.class_id', 'left');
    $ci->db->join('campuses', 'campuses.campus_id = expenses.campus_id', 'left');
    $ci->db->where('expenses.student_id', $student_id);
    $ci->db->where('expenses.council_exam_no IS NOT NULL', null, false);
    $ci->db->where('expenses.council_exam_no !=', '');
    $expense_results = $ci->db->get()->result_array();

    /*
     * 3) Merge both tables by class + council_exam_no
     */
    $merged = array();

    foreach ($roll_results as $row) {

        $key = $row['class'] . '_' . $row['council_exam_no'];

        if (!isset($merged[$key])) {
            $merged[$key] = array(
                'class' => $row['class'],
                'council_exam_no' => $row['council_exam_no'],
                'roll_no' => '',
                'result_remarks' => '',
                'result_image' => '',
                'online_result_image' => '',
                'expense_date' => '',
                'expense_amount' => '',
                'expense_image' => ''
            );
        }

        $merged[$key]['roll_no'] = !empty($row['roll_no']) ? $row['roll_no'] : '';
        $merged[$key]['result_remarks'] = !empty($row['result_remarks']) ? $row['result_remarks'] : '';
        $merged[$key]['result_image'] = !empty($row['result_image']) ? $row['result_image'] : '';
        $merged[$key]['online_result_image'] = !empty($row['online_result_image']) ? $row['online_result_image'] : '';
    }

    foreach ($expense_results as $row) {

        /*
         * IMPORTANT:
         * Tumhare purane code mein expenses.class compare ho raha tha:
         * ->where('class'=>$result['class'])
         *
         * Is liye yahan bhi expenses.class use kiya hai.
         * Agar tumhare expenses table mein class column nahi hai aur class_id use hota hai,
         * to mujhe table structure bhej do.
         */
        $expense_class = !empty($row['class']) ? $row['class'] : '';

        $key = $expense_class . '_' . $row['council_exam_no'];

        if (!isset($merged[$key])) {
            $merged[$key] = array(
                'class' => $expense_class,
                'council_exam_no' => $row['council_exam_no'],
                'roll_no' => '',
                'result_remarks' => '',
                'result_image' => '',
                'online_result_image' => '',
                'expense_date' => '',
                'expense_amount' => '',
                'expense_image' => ''
            );
        }

        $merged[$key]['expense_date'] = !empty($row['date']) ? $row['date'] : '';
        $merged[$key]['expense_amount'] = !empty($row['amount']) ? $row['amount'] : '';
        $merged[$key]['expense_image'] = !empty($row['image']) ? $row['image'] : '';
    }

    /*
     * Optional sorting by exam no
     */
    uasort($merged, function ($a, $b) {

    if ($a['council_exam_no'] == $b['council_exam_no']) {

        if ($a['class'] == $b['class']) {
            return 0;
        }

        return ($a['class'] < $b['class']) ? -1 : 1;
    }

    return ($a['council_exam_no'] < $b['council_exam_no']) ? -1 : 1;
});

    if (empty($merged)) {
        echo '<div style="color:#856404; background:#fff3cd; border:1px solid #ffeeba; padding:6px; border-radius:3px;">
                No council roll/result or expense record found.
              </div>';
        return;
    }

    $counter = 1;

    foreach ($merged as $result) {

        if ($result['class'] == 1) {
            $class = '1st Year';
        } elseif ($result['class'] == 2) {
            $class = '2nd Year';
        } else {
            $class = '-';
        }

        /*
         * Result image link
         */
        $image_link = '';

        if (!empty($result['result_image'])) {

            if (empty($result['online_result_image'])) {
                $result_image_url = base_url() . $result['result_image'];
            } else {
                $result_image_url = str_replace(
                    $bucket_address,
                    $cloudfront_address,
                    $result['online_result_image']
                );
            }

            $image_link = ' 
                <a href="' . $result_image_url . '" target="_blank" title="View Result Image" style="margin-left:4px;">
                    <i class="fa fa-image" style="color:#337ab7;"></i>
                </a>';
        }

        /*
         * Expense image link
         */
        $expense_image_link = '';

        if (!empty($result['expense_image'])) {

            $expense_image_url = base_url() . $result['expense_image'];

            $expense_image_link = ' 
                <a href="' . $expense_image_url . '" target="_blank" title="View Expense Image" style="margin-left:4px;">
                    <i class="fa fa-image" style="color:#337ab7;"></i>
                </a>';
        }

        $expense_date = !empty($result['expense_date']) ? $result['expense_date'] : '-';
        $expense_amount = !empty($result['expense_amount']) ? $result['expense_amount'] : '-';
        $roll_no = !empty($result['roll_no']) ? $result['roll_no'] : '-';
        $result_remarks = !empty($result['result_remarks']) ? $result['result_remarks'] : 'Pending';

        echo '
        <div style="display:flex; align-items:center; border-bottom:1px solid #ddd; font-size:12px; padding:3px 0;">
            
            <div style="
                background:#13dce5;
                color:#000;
                font-weight:bold;
                min-width:28px;
                padding:3px 6px;
                margin-right:6px;
                border-radius:3px;
            ">
                ' . $counter . '
            </div>

            <div style="color:#000; line-height:20px;">
                ' . $class . ' , 
                exam no. <strong>' . $result['council_exam_no'] . '</strong> , 
                Expense Date. ' . $expense_date . ' , 
                Expense Amount. <strong>' . $expense_amount . '</strong> , 
                ' . $expense_image_link . '
                roll no. <strong>' . $roll_no . '</strong> , 
                result 
                <span style="
                    background:#fff3cd;
                    color:#856404;
                    font-weight:bold;
                    padding:2px 6px;
                    border-radius:3px;
                    border:1px solid #ffeeba;
                ">
                    ' . $result_remarks . '
                </span>
                ' . $image_link . '
            </div>

        </div>';

        $counter++;
    }
}

function getStudentResultDetail($cnic, $variable)
{
    $ci =& get_instance();
    $ci->db->select('*');
    $ci->db->from('punjab_council_roll_number');
    $ci->db->where('cnic', $cnic);
    $results = $ci->db->get()->result_array();

    foreach($results as $result)
    {
        if($variable=='class')
        {
            if($result['class']==1)
            {
                $class = '1st Year <hr />';
            }
            else
            {
                $class = '2nd Year <hr />';
            }
            echo $class;
        }
        elseif($variable=='exam_no')
        {
            echo $result['council_exam_no'].' <hr />';
        }
        elseif($variable=='roll_no_update_date')
        {
            echo date('Y-m-d',strtotime($result['date'])).' <hr />';
        }
        elseif($variable=='roll_no')
        {
            echo $result['roll_no'].' <hr />';
        }
        elseif($variable=='result_update_date')
        {
            if($result['result_update_date']=='0000-00-00')
            {
                echo 'Waiting <hr />';
            }
            else
            {
                echo date('Y-m-d',strtotime($result['result_update_date'])).' <hr />';
            }
        }
        elseif($variable=='result_remarks')
        {
            echo $result['result_remarks'].' <hr />';
        }
        elseif($variable=='paper1')
        {
            if($result['result_remarks']=='')
            {
                echo 'Waiting <hr />';
            }
            else
            {
                if($result['result_remarks']=='Fail')
                {
                    echo 'Fail <hr />';
                }
                elseif($result['result_remarks']=='Fail (must appear in all)')
                {
                    echo 'Fail <hr />';
                }
                elseif($result['result_remarks']=='Fail (must appear in all) Absent')
                {
                    echo 'Fail <hr />';
                }
                else
                {
                    if (strpos($result['result_remarks'], '1') !== false) {
                        echo 'Fail <hr />';
                    }
                    else
                    {
                        echo 'Pass <hr />';
                    }
                }
            }
        }
        elseif($variable=='paper2')
        {
            if($result['result_remarks']=='')
            {
                echo 'Waiting <hr />';
            }

            else
            {
                if($result['result_remarks']=='Fail')
                {
                    echo 'Fail <hr />';
                }
                elseif($result['result_remarks']=='Fail (must appear in all)')
                {
                    echo 'Fail <hr />';
                }
                elseif($result['result_remarks']=='Fail (must appear in all) Absent')
                {
                    echo 'Fail <hr />';
                }
                else
                {
                    if (strpos($result['result_remarks'], '2') !== false) {
                        echo 'Fail <hr />';
                    }
                    else
                    {
                        echo 'Pass <hr />';
                    }
                }
            }
        }
        elseif($variable=='paper3')
        {
            if($result['result_remarks']=='')
            {
                echo 'Waiting <hr />';
            }
            else
            {
                if($result['result_remarks']=='Fail')
                {
                    echo 'Fail <hr />';
                }
                elseif($result['result_remarks']=='Fail (must appear in all)')
                {
                    echo 'Fail <hr />';
                }
                elseif($result['result_remarks']=='Fail (must appear in all) Absent')
                {
                    echo 'Fail <hr />';
                }
                else
                {
                    if (strpos($result['result_remarks'], '3') !== false) {
                        echo 'Fail <hr />';
                    }
                    else
                    {
                        echo 'Pass <hr />';
                    }
                }
            }
        }
        elseif($variable=='paper4')
        {
            if($result['result_remarks']=='')
            {
                echo 'Waiting <hr />';
            }
            else
            {
                if($result['result_remarks']=='Fail')
                {
                    echo 'Fail <hr />';
                }
                elseif($result['result_remarks']=='Fail (must appear in all)')
                {
                    echo 'Fail <hr />';
                }
                elseif($result['result_remarks']=='Fail (must appear in all) Absent')
                {
                    echo 'Fail <hr />';
                }
                else
                {
                    if (strpos($result['result_remarks'], '4') !== false) {
                        echo 'Fail <hr />';
                    }
                    else
                    {
                        echo 'Pass <hr />';
                    }
                }
            }
        }
        elseif($variable=='paper5')
        {
            if($result['result_remarks']=='')
            {
                echo 'Waiting <hr />';
            }
            else
            {
                if($result['result_remarks']=='Fail')
                {
                    echo 'Fail <hr />';
                }
                elseif($result['result_remarks']=='Fail (must appear in all)')
                {
                    echo 'Fail <hr />';
                }
                elseif($result['result_remarks']=='Fail (must appear in all) Absent')
                {
                    echo 'Fail <hr />';
                }
                else
                {
                    if (strpos($result['result_remarks'], '5') !== false) {
                        echo 'Fail <hr />';
                    }
                    else
                    {
                        echo 'Pass <hr />';
                    }
                }
            }
        }
        elseif($variable=='paper6')
        {
            if($result['result_remarks']=='')
            {
                echo 'Waiting <hr />';
            }
            else
            {
                if($result['result_remarks']=='Fail')
                {
                    echo 'Fail <hr />';
                }
                elseif($result['result_remarks']=='Fail (must appear in all)')
                {
                    echo 'Fail <hr />';
                }
                elseif($result['result_remarks']=='Fail (must appear in all) Absent')
                {
                    echo 'Fail <hr />';
                }
                else
                {
                    if (strpos($result['result_remarks'], '6') !== false) {
                        echo 'Fail <hr />';
                    }
                    else
                    {
                        echo 'Pass <hr />';
                    }
                }
            }
        }
        elseif($variable=='pass-fail')
        {
            if($result['result_remarks']=='')
            {
                echo 'Waiting <hr />';
            }
            else
            {
                if($result['result_remarks']=='Pass')
                {
                    echo '<strong>Pass</strong> <hr />';
                }
                else
                {
                    echo '<strong>Fail</strong> <hr />';
                }
            }
        }
        elseif($variable=='chance')
        {
            if($result['result_remarks']=='')
            {
                echo 'Waiting <hr />';
            }
            else
            {
                if($result['class']==1)
                {
                    if($result['result_remarks']=='Pass')
                    {
                        echo 'Promote to 2nd Year <hr />';
                    }
                    else
                    {
                        if (strpos($result['result_remarks'], 'Next Two Chances') !== false) {
                            echo 'Next Two Chances <hr />';
                        }
                        elseif (strpos($result['result_remarks'], 'Last Chance') !== false) {
                            echo 'Last Chance <hr />';
                        }
                        else
                        {
                            echo 'Complete Fail <hr />';
                        }
                    }
                }
                elseif($result['class']==2)
                {
                    if($result['result_remarks']=='Pass')
                    {
                        echo 'Print Diploma <hr />';
                    }
                    else
                    {
                        if (strpos($result['result_remarks'], 'Next Two Chances') !== false) {
                            echo 'Next Two Chances <hr />';
                        }
                        elseif (strpos($result['result_remarks'], 'Last Chance') !== false) {
                            echo 'Last Chance <hr />';
                        }
                        else
                        {
                            echo 'Complete Fail <hr />';
                        }
                    }
                }
            }
        }
        elseif($variable=='manual-remarks')
        {
            if($result['id']=='')
            {

            }
            else
            {
                echo '<button type="button" class="btn green manual-remarks" data-result-id="'.$result['id'].'" data-toggle="modal" href="#basic">Add Remarks Paper # '.$result['council_exam_no'].'</button>';
            }
        }
        else
        {

        }
    }
}

function getSalary($date, $day, $user_id, $this_day_checkin ,$this_day_checkout, $chutti)
{
    $ci =& get_instance();
    //GET SALARY
    $salary = $ci->db->get_where('users', array('user_id'=>$user_id))->row()->salary;
    //GET CHECKIN CHECKOUT TIMING
    $timingRow = get_staff_day_timing($user_id, $day);
    $day_checkin_timing = isset($timingRow['checkin_timing']) ? $timingRow['checkin_timing'] : '00:00:00';
    $day_checkout_timing = isset($timingRow['checkout_timing']) ? $timingRow['checkout_timing'] : '00:00:00';

    //CHECK CHECKIN CHECKOUT TIMING EXISTS OR NOT
    if(count($day_checkin_timing)<=0)
    {
        //echo 'N/A';
        //exit;
    }

    //GET PER DAY MINUTES
    $to_time = strtotime("$date $day_checkin_timing");
    $from_time = strtotime("$date $day_checkout_timing");
    $minutes = round(abs($to_time - $from_time) / 60,2);
    //GET PER MINUTE SALARY
    $per_minute_salary = round($salary/$minutes, 2);

    //INCASE OF HOLIDAY
    if($chutti==1)
    {
        return round($minutes*$per_minute_salary);
    }

    else if($this_day_checkin== $date.' 00:00:00.00' && $this_day_checkout== $date.' 00:00:00.00')
    {
        return 0;
    }
    else
    {
        //GET THIS DAY MINUTES

        $machine_to_time = strtotime($this_day_checkin);
        //IF SOME REACH EARLY TO TIME
        if($machine_to_time<$to_time)
        {
            $machine_to_time =$to_time;
        }

        //IF SOMEONE COME LATE WITHIN 15 MINUTES
        if($machine_to_time>$to_time)
        {
            $late_minutes = round(abs($machine_to_time-$to_time)/ 60,2);
            if($late_minutes<16)
            {
                $machine_to_time =$to_time;
            }
        }

        $machine_from_time = strtotime($this_day_checkout);
        //IF SOMEONE GO AFTER TIME
        if($machine_from_time>$from_time)
        {
            $machine_from_time = $from_time;
        }
        $minutes = round(abs($machine_to_time - $machine_from_time) / 60,2);

        return round($minutes*$per_minute_salary);
    }
}

function getNewAdmissions($campus_id, $from_date, $to_date, $date_type)
{
    $ci =& get_instance();
    $ci->db->select('*');
    $ci->db->from('students');
    $ci->db->join('classes', 'classes.class_id=students.class_id', 'inner');
    $ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
    $ci->db->where(array('students.status'=>1, 'students.registration_date>='=>$from_date, 'students.registration_date<='=>$to_date, 'campuses.campus_id'=>$campus_id));
    $results = $ci->db->get()->result_array();
    return count($results);
}

function getFeeCollectinBank($campus_id, $from_date, $to_date, $date_type)
{
    $ci =& get_instance();
    $ci->db->select_sum('payments.actual_amount');
    $ci->db->from('payments');
    $ci->db->join('students', 'payments.student_id=students.student_id', 'inner');
    $ci->db->join('classes', 'classes.class_id=students.class_id', 'inner');
    $ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
    if($date_type=='paid_date')
    {
        $ci->db->where(array('payments.paid_date>='=>$from_date, 'payments.paid_date<='=>$to_date, 'payments.paid'=>1, 'campuses.campus_id'=>$campus_id, 'payments.fee_pay_through !=' => 'college'));
    }
    else
    {
        $ci->db->where(array('payments.actual_paid_date>='=>$from_date, 'payments.actual_paid_date<='=>$to_date, 'payments.paid'=>1, 'campuses.campus_id'=>$campus_id, 'payments.fee_pay_through !=' => 'college'));
    }
    $ci->db->group_by("paid_challans");
    $results = $ci->db->get()->result_array();
    return array_sum(array_column($results,'actual_amount'));
}

function getFeeCollectinCollege($campus_id, $from_date, $to_date, $date_type)
{
    $ci =& get_instance();
    $ci->db->select('*');
    $ci->db->from('payments');
    $ci->db->join('students', 'payments.student_id=students.student_id', 'inner');
    $ci->db->join('classes', 'classes.class_id=students.class_id', 'inner');
    $ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
    if($date_type=='paid_date')
    {
        $ci->db->where(array('payments.paid_date>='=>$from_date, 'payments.paid_date<='=>$to_date, 'payments.paid'=>1, 'campuses.campus_id'=>$campus_id, 'payments.fee_pay_through' => 'college'));
    }
    else
    {
        $ci->db->where(array('payments.actual_paid_date>='=>$from_date, 'payments.actual_paid_date<='=>$to_date, 'payments.paid'=>1, 'campuses.campus_id'=>$campus_id, 'payments.fee_pay_through' => 'college'));
    }
    $ci->db->group_by("paid_challans");
    $results = $ci->db->get()->result_array();
    return array_sum(array_column($results,'actual_amount'));
}

function getCampusTotalExpense($campus_id, $from_date, $to_date, $date_type)
{
    $ci =& get_instance();
    $ci->db->select_sum('expenses.amount');
    $ci->db->from('expenses');
    $ci->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'inner');
    if($date_type=='paid_date')
    {
        $ci->db->where(array('expenses.date>='=>$from_date, 'expenses.date<='=>$to_date, 'campuses.campus_id'=>$campus_id));
    }
    else
    {
        $ci->db->where(array('expenses.actual_date>='=>$from_date, 'expenses.actual_date<='=>$to_date, 'campuses.campus_id'=>$campus_id));
    }
    $results = $ci->db->get()->result_array();
    return $results[0]['amount'];
}

function newApplicationsCount()
{
    $ci =& get_instance();

    $total = $ci->db->get_where('apply_now',array('status'=>0,'clear_by_admin'=>0,'pending_status'=>NULL))->result_array();
    $ci->db->where(array('status != '=>3));
    $query = $ci->db->get('admission_applications')->result_array();
    return count($total)+count($query);

}

function pendingApplicationsCount()
{
    $ci =& get_instance();

    $total = $ci->db->join("online_application_comments","online_application_comments.apply_now_id = apply_now.apply_now_id")->order_by('online_application_comments.online_application_comment_id','DESC')->group_by("apply_now.apply_now_id")->get_where('apply_now',array('apply_now.pending_status'=>1,'apply_now.status'=>0,'online_application_comments.add_by'=>$ci->session->userdata("name")))->result_array();
//        print_r($ci->db->last_query());
//        exit();
    //        $ci->db->where(array('apply_now.status != '=>3));
//        $query = $ci->db->get('admission_applications')->result_array();
    return count($total);

}

function dashboardNewAdmissions($campus_id)
{
    $ci =& get_instance();

    $access = checkUserAccess();
    $campus_ids = @explode(',',$access[0]['campus_ids']);

    $ci->db->select('*');
    $ci->db->from('students');
    $ci->db->join('classes', 'classes.class_id=students.class_id', 'inner');
    $ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
    if($ci->session->userdata('role')!='Admin')
    {
        $ci->db->where_in('campuses.campus_id', $campus_ids);
    }
    $ci->db->where(array('campuses.campus_id'=>$campus_id,'students.clear_status'=>0));
    $query = $ci->db->get()->result_array();
    return $query;
}

function dashboardNewExpenseEntries($campus_id)
{
    $ci =& get_instance();

    $ci->db->select('*');
    $ci->db->from('expenses');
    $ci->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
    $ci->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
    $ci->db->where(array('campuses.campus_id'=>$campus_id,'expenses.clear_status'=>0));
    $query = $ci->db->get()->result_array();
    return $query;
}

function dashboardFeeStaus($campus_id)
{
    $ci =& get_instance();

    // $ci->db->select('*');
    // $ci->db->from('payments');
    // $ci->db->join('students', 'payments.student_id=students.student_id', 'inner');
    // $ci->db->join('classes', 'classes.class_id=students.class_id', 'inner');
    // $ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
    // $ci->db->where(array('payments.paid'=> 1, 'payments.clear_college_fee'=>0,'campuses.campus_id'=>$campus_id,'payments.fee_submit_type !='=>'computer_challan'));
    // $ci->db->group_by("CASE WHEN payments.merged_challan IS NOT NULL THEN payments.merged_challan ELSE payments.challan_no END",false);
    // $query = $ci->db->get()->result_array();
    // return $query;

    $access = checkUserAccess();
    $campuses = @explode(',',$access[0]['campus_ids']);
    
    $ci->db->select('students.*,courses.* ,classes.name,payments.*,campuses.*');
    $ci->db->from('payments');
    $ci->db->join('students', 'payments.student_id=students.student_id', 'inner');
    $ci->db->join('classes', 'classes.class_id=students.class_id', 'inner');
    $ci->db->join('courses', 'courses.course_id=classes.course_id', 'left');
    $ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
    if(@$campus_id!=NULL)
    {
        $ci->db->where('campuses.campus_id',$campus_id);
    }
    $ci->db->where(array('students.contract_id'=>0,'payments.paid'=> 1, 'payments.clear_college_fee'=>0,'payments.fee_submit_type !='=>'computer_challan'));
    $ci->db->group_by("CASE WHEN payments.merged_challan IS NOT NULL THEN payments.merged_challan ELSE payments.challan_no END",false);

    $ci->db->group_by('payments.paid_challans');
    $ci->db->order_by('payments.paid_date', 'DESC');
    $query = $ci->db->get()->result_array();
    
    return $query;
}

function dashboardFeeStausContractors($campus_id)
{
    $ci =& get_instance();

    $access = checkUserAccess();
    $campuses = @explode(',',$access[0]['campus_ids']);
    
    $ci->db->select('*');
    $ci->db->from('payments');
    $ci->db->join('contracts', 'contracts.contract_id=payments.contract_id', 'inner');
    $ci->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'inner');
    $ci->db->join('campuses', 'contracts.campus_id=campuses.campus_id', 'inner');
    $ci->db->where(array('payments.paid'=> 1, 'payments.clear_college_fee'=>0,"fee_submit_type != "=>'computer_challan'));
    $ci->db->group_by("CASE WHEN payments.merged_challan IS NOT NULL THEN payments.merged_challan ELSE payments.challan_no END",false);
    if(@$campus_id!=NULL){
        $ci->db->where('campuses.campus_id',$campus_id);
    }
    if($ci->session->userdata('role')!='Admin'){
        $ci->db->where_in('campuses.campus_id', $campuses);
    }
    $ci->db->order_by('payments.paid_date', 'ASC');
    $query = $ci->db->get()->result_array();
    return $query;
}

function dashboardUpdateFeeRequests($campus_id)
{
    $ci =& get_instance();

    $ci->db->select('*');
    $ci->db->from('update_payment_requests');
    $ci->db->join('students', 'students.student_id=update_payment_requests.student_id', 'inner');
    $ci->db->join('classes', 'classes.class_id=students.class_id', 'inner');
    $ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
    $ci->db->where(array('update_payment_requests.ok_by_admin'=>'0','campuses.campus_id'=>$campus_id));
    $query = $ci->db->get()->result_array();
    return $query;
}

function dashboardUpdateFeeRequestsContractors($campus_id)
{
    $ci =& get_instance();

    $ci->db->select('*');
    $ci->db->from('update_payment_requests');
    $ci->db->join('contracts', 'contracts.contract_id=update_payment_requests.contract_id', 'inner');
    $ci->db->join('contractors', 'contracts.contractor_id=contractors.contractor_id', 'inner');
    $ci->db->join('campuses', 'campuses.campus_id=contracts.campus_id', 'inner');
    $ci->db->where(array('update_payment_requests.ok_by_admin'=>'0','campuses.campus_id'=>$campus_id));
    $query = $ci->db->get()->result_array();
    return $query;
}

function dashboardUpdateStudentRequests($campus_id)
{
    $ci =& get_instance();

    $ci->db->select('update_student_requests.*, classes.name as class_name');
    $ci->db->from('update_student_requests');
    $ci->db->join('classes', 'classes.class_id=update_student_requests.class_id', 'inner');
    $ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
    $ci->db->where(array('update_student_requests.ok_by_admin'=>'0','campuses.campus_id'=>$campus_id));
    $query = $ci->db->get()->result_array();
    return $query;
}

function dashboardStudentsContractorsCount($campus_id)
{
    $ci =& get_instance();

    $access = checkUserAccess();
    $fee_recovery_class_ids = @explode(',',$access[0]['fee_recovery_class_ids']);

    $ci->db->select('*');
    $ci->db->from('payments');
    $ci->db->join('students', 'payments.student_id=students.student_id', 'inner');
    $ci->db->join('classes', 'classes.class_id=students.class_id', 'inner');
    $ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
    if($ci->session->userdata('role')!='Admin')
    {
        $ci->db->where_in('classes.class_id', $fee_recovery_class_ids);
    }
    $ci->db->where(array('campuses.campus_id'=>$campus_id,'students.status'=> 1, 'payments.paid'=>'0', 'payments.dead_line<='=>date('Y-m-d', strtotime("+1 week"))));
    $ci->db->group_by('students.student_id');
    $students_count = $ci->db->get()->result_array();

    $ci->db->select('*');
    $ci->db->from('payments');
    $ci->db->join('contracts','contracts.contract_id=payments.contract_id','inner');
    $ci->db->join('contractors','contracts.contractor_id=contractors.contractor_id','inner');
    $ci->db->join('campuses','contracts.campus_id=campuses.campus_id','inner');
    $ci->db->where(array('campuses.campus_id'=>$campus_id,'payments.paid'=>'0', 'payments.dead_line<='=>date('Y-m-d', strtotime("+1 week"))));
    $ci->db->group_by('contractors.contractor_id');
    $contractor_count = $ci->db->get()->result_array();

    $total = count($students_count)+count($contractor_count);

    return $total;
}

function dashboardStudentsContractorsFeeDueComments($campus_id)
{
    $ci =& get_instance();

    $access = checkUserAccess();
    $campus_ids = @explode(',',$access[0]['fee_dues_campus_ids']);

    $ci->db->select('*');
    $ci->db->from('payments');
    $ci->db->join('students', 'payments.student_id=students.student_id', 'inner');
    $ci->db->join('classes', 'classes.class_id=students.class_id', 'inner');
    $ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
    if($ci->session->userdata('role')!='Admin')
    {
        $ci->db->where_in('campuses.campus_id', $campus_ids);
    }
    $ci->db->where(array('campuses.campus_id'=>$campus_id,'students.status'=> 1, 'payments.paid'=>'0', 'payments.dead_line<='=>date('Y-m-d', strtotime("+1 week"))));
    $ci->db->order_by('students.roll_no', 'asc');
    $query1 = $ci->db->get()->result_array();

    $ci->db->select('*');
    $ci->db->from('payments');
    $ci->db->join('contracts','contracts.contract_id=payments.contract_id','inner');
    $ci->db->join('contractors','contracts.contractor_id=contractors.contractor_id','inner');
    $ci->db->join('campuses','contracts.campus_id=campuses.campus_id','inner');
    $ci->db->where(array('campuses.campus_id'=>$campus_id,'payments.paid'=>'0', 'payments.dead_line<='=>date('Y-m-d', strtotime("+1 week"))));
    $query2 = $ci->db->get()->result_array();
    return count($query1)+count($query2);
}

function dashboardStudentsDueFeesStatusClear($campus_id)
{
    $ci =& get_instance();

    $ci->db->select('*');
    $ci->db->from('payments');
    $ci->db->join('fees_remarks', 'fees_remarks.fee_id=payments.id', 'inner');
    $ci->db->join('students', 'payments.student_id=students.student_id', 'inner');
    $ci->db->join('classes', 'classes.class_id=students.class_id', 'inner');
    $ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
    $ci->db->where(array('campuses.campus_id'=>$campus_id,'fees_remarks.clear_status'=>0, 'students.status'=> 1, 'payments.paid'=>'0', 'payments.dead_line<='=>date('Y-m-d', strtotime("+1 week"))));
    $ci->db->order_by('students.roll_no', 'asc');
    $query1 = $ci->db->get()->result_array();

    $ci->db->select('*');
    $ci->db->from('payments');
    $ci->db->join('fees_remarks', 'fees_remarks.fee_id=payments.id', 'inner');
    $ci->db->join('contracts', 'payments.contract_id=contracts.contract_id', 'inner');
    $ci->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'inner');
    $ci->db->join('campuses', 'contracts.campus_id=campuses.campus_id', 'inner');
    $ci->db->where(array('campuses.campus_id'=>$campus_id,'fees_remarks.clear_status'=>0, 'payments.paid'=>'0', 'payments.dead_line<='=>date('Y-m-d', strtotime("+1 week"))));
    $ci->db->order_by('contractors.contractor_id_from_college', 'asc');
    $query2 = $ci->db->get()->result_array();
    return count($query1)+count($query2);
}

function dashboardPendingReminders($campus_id)
{
    $ci =& get_instance();

    $ci->db->select('*');
    $ci->db->from('reminder');
    $ci->db->join('users','users.user_id=reminder.user_id','INNER');
    $ci->db->join('campuses','campuses.campus_id=users.campus_id','INNER');
    $ci->db->where(array('campuses.campus_id'=>$campus_id,'reminder.status'=>'Pending'));
    $query = $ci->db->get()->result_array();
    return $query;
}

function dashboardRemindersUnderReview($campus_id)
{
    $ci =& get_instance();

    $ci->db->select('*');
    $ci->db->from('reminder');
    $ci->db->join('users','users.user_id=reminder.user_id','INNER');
    $ci->db->join('campuses','campuses.campus_id=users.campus_id','INNER');
    $ci->db->where(array('campuses.campus_id'=>$campus_id,'reminder.status'=>'Completed','reminder.check_by_admin'=>0));
    $query = $ci->db->get()->result_array();
    return $query;
}

function dashboardUnclearProducts($campus_id)
{
    $ci =& get_instance();

    $ci->db->select('*');
    $ci->db->from('products');
    $ci->db->join('product_names','products.product_name_id=product_names.product_name_id','left');
    $ci->db->join('campuses','campuses.campus_id=products.campus_id','inner');
    $ci->db->where(array('campuses.campus_id'=>$campus_id,'products.status'=>0));
    $query = $ci->db->get()->result_array();
    return $query;
}

function dashboardUpdatedProducts($campus_id)
{
    $ci =& get_instance();

    $ci->db->select('*');
    $ci->db->from('update_product_requests');
    $ci->db->join('product_names','update_product_requests.product_name_id=product_names.product_name_id','left');
    $ci->db->join('campuses','campuses.campus_id=update_product_requests.campus_id','inner');
    $ci->db->where(array('campuses.campus_id'=>$campus_id));
    $query = $ci->db->get()->result_array();
    return $query;
}

function dashboardUnclearDocuments($campus_id)
{
    $ci =& get_instance();

    $ci->db->select('*');
    $ci->db->from('documents');
    $ci->db->join('document_names','documents.document_name_id=document_names.document_name_id','left');
    $ci->db->join('campuses','campuses.campus_id=documents.campus_id','inner');
    $ci->db->where(array('campuses.campus_id'=>$campus_id,'documents.status'=>0));
    $query = $ci->db->get()->result_array();
    return $query;
}

function dashboardNewApplications($campus_id)
{
    $ci =& get_instance();
    if($ci->session->userdata('role')=='Admin')
    {
        $applications = $ci->db->get_where('apply_now',array('status'=>0,'clear_by_admin'=>0,'pending_status'=>NULL))->result_array();
        $total=0;
        foreach($applications as $application)
        {
            $application_campus_id = @$ci->db->get_where('campuses', array('website'=>str_replace('/','',str_replace('https://www.','',$application['website']))))->row()->campus_id;
            if($application_campus_id==$campus_id)
            {
                $total++;
            }
        }
        return $total;
    }
    else
    {
        $applications = $ci->db->get_where('apply_now',array('status'=>0,'clear_by_admin'=>0,'pending_status'=>NULL))->result_array();
        $total=0;
        foreach($applications as $application)
        {
            $application_campus_id = @$ci->db->get_where('campuses', array('website'=>str_replace('/','',str_replace('https://www.','',$application['website']))))->row()->campus_id;

            $check_access = $ci->db->get_where('online_application_access',array('campus_id'=>$application_campus_id,'city'=>$application['city'],'user_id'=>$ci->session->userdata('user_id')))->result_array();

            if(count($check_access)>0)
            {
                if($application_campus_id == $campus_id)
                {
                    $total++;
                }
            }
            if(count($check_access)<1)
            {

                $second_check = $ci->db->get_where('online_application_access',array('campus_id'=>$application_campus_id,'city!='=>$application['city'],'all_cities'=>1,'user_id'=>$ci->session->userdata('user_id')))->result_array();
                if(count($second_check)>0)
                {
                    if($application_campus_id == $campus_id)
                    {
                        $total++;
                    }
                }
            }
        }
        return $total;
    }
}

function dashboardPendingApplications($campus_id)
{
    $ci =& get_instance();
    if($ci->session->userdata('role')=='Admin')
    {
        $applications = $ci->db->get_where('apply_now',array('pending_status'=>1,'status'=>0))->result_array();
        $total=0;
        foreach($applications as $application)
        {
            $application_campus_id = @$ci->db->get_where('campuses', array('website'=>str_replace('/','',str_replace('https://www.','',$application['website']))))->row()->campus_id;
            if($application_campus_id==$campus_id)
            {
                $total++;
            }
        }
        return $total;
    }
    else{
        $applications = $ci->db->get_where('apply_now',array('pending_status'=>1,'status'=>0))->result_array();
        $total=0;
        foreach($applications as $application)
        {
            $application_campus_id = @$ci->db->get_where('campuses', array('website'=>str_replace('/','',str_replace('https://www.','',$application['website']))))->row()->campus_id;

            $check_access = $ci->db->get_where('online_application_access',array('campus_id'=>$application_campus_id,'city'=>$application['city'],'user_id'=>$ci->session->userdata('user_id')))->result_array();

            if(count($check_access)>0)
            {
                if($application_campus_id == $campus_id)
                {
                    $total++;
                }
            }
            if(count($check_access)<1)
            {

                $second_check = $ci->db->get_where('online_application_access',array('campus_id'=>$application_campus_id,'city!='=>$application['city'],'all_cities'=>1,'user_id'=>$ci->session->userdata('user_id')))->result_array();
                if(count($second_check)>0)
                {
                    if($application_campus_id == $campus_id)
                    {
                        $total++;
                    }
                }
            }
        }
        return $total;
    }
}

function totalAmountSendToCouncil($campus_id, $class, $council_exam_no)
{
    $ci =& get_instance();
    $ci->db->select_sum('amount');
    $ci->db->select('image,date');
    $ci->db->from('expenses');
    $ci->db->where(array('campus_id'=>$campus_id,'class'=>$class,'council_exam_no'=>$council_exam_no));
    $ci->db->group_by('image');
    $query = $ci->db->get()->result_array();
    return $query;
}

function admissionsSendToCouncil($campus_id, $class, $council_exam_no)
{
    $ci =& get_instance();
    $ci->db->select('*');
    $ci->db->from('expenses');
    $ci->db->where(array('campus_id'=>$campus_id,'class'=>$class,'council_exam_no'=>$council_exam_no));
    $query = $ci->db->get()->result_array();
    return count($query);
}

function recognizedRollNoReceiveFromCouncil($campus_id, $class, $council_exam_no)
{
    $ci =& get_instance();
    $ci->db->select('*');
    $ci->db->from('punjab_council_roll_number');
    $ci->db->join('students','students.cnic=punjab_council_roll_number.cnic','left');
    $ci->db->join('classes','classes.class_id=students.class_id','left');
    $ci->db->join('campuses','campuses.campus_id=classes.campus_id','left');
    $ci->db->where(array('campuses.campus_id'=>$campus_id,'punjab_council_roll_number.class'=>$class,'punjab_council_roll_number.council_exam_no'=>$council_exam_no));
    $query = $ci->db->get()->result_array();
    return count($query);
}

function notRecognizedRollNoReceiveFromCouncil($campus_id, $class, $council_exam_no)
{
    $ci =& get_instance();
    $ci->db->select('*');
    $ci->db->from('punjab_council_roll_number');
    $ci->db->join('students','students.cnic=punjab_council_roll_number.cnic','left');
    $ci->db->join('classes','classes.class_id=students.class_id','left');
    $ci->db->join('campuses','campuses.campus_id=classes.campus_id','left');
    $ci->db->where(array('campuses.campus_id'=>$campus_id,'punjab_council_roll_number.class'=>$class,'punjab_council_roll_number.council_exam_no'=>$council_exam_no));
    $query = $ci->db->get()->result_array();

    $i=0;
    foreach($query as $rollno)
    {
        if($rollno['campus_name']=='')
        {
            $i++;
        }
    }
    return $i;
}

function studentsFeeNotCreated($campus_id)
{
    $ci =& get_instance();
    $ci->db->select('*');
    $ci->db->from('students');
    $ci->db->join('classes','classes.class_id=students.class_id','inner');
    $ci->db->join('campuses','campuses.campus_id=classes.campus_id','inner');
    $ci->db->where(array('campuses.campus_id'=>$campus_id,'students.status'=>1,'students.contract_id'=>0));
    $students = $ci->db->get()->result_array();

    $stud=array();
    foreach($students as $student)
    {
        $ci->db->select_sum('amount');
        $ci->db->from('payments');
        $ci->db->where(array('student_id'=>$student['student_id'],'payment_plan!='=>'consulation fee'));
        $payment = $ci->db->get()->result_array();
        //array_push($students,$student['student_id']);

        $ci->db->select_sum('reversal_amount');
        $ci->db->from('payments_reversal_requests');
        $ci->db->where(array('student_id'=>$student['student_id'],'done'=>1));
        $reversal_payment = $ci->db->get()->result_array();

        if(count($reversal_payment)>0)
        {
            $total_fee_created = ($payment[0]['amount'])-($reversal_payment[0]['reversal_amount']);
        }
        else
        {
            $total_fee_created = $payment[0]['amount'];
        }

        $total_fee_created = $payment[0]['amount'];

        if($student['total_fee']>@$total_fee_created)
        {
            array_push($stud,$student['student_id']);
        }
        if(count($payment)<1)
        {
            array_push($stud,$student['student_id']);
        }
    }
    return $stud;
}

function opennotification($noty_id)
{
    $ci =& get_instance();

    $ci->db->set('viewed','1');
    $ci->db->where("(notifications.id = '".$noty_id."')",NULL,FALSE);
    $ci->db->update('notifications');


    $notifications = $ci->db->get_where('notifications', array('id'=>$noty_id))->result_array();


    redirect(site_url().$notifications[0]['url']);

}

function getnotifications($user_id)
{
    $ci =& get_instance();
    $notifications = $ci->db->get_where('notifications', array('rel_id'=>$user_id,'viewed'=>0,'notification_date'=>date('Y-m-d')))->result_array();


    foreach($notifications as $notification)
    {
        echo '<li class="notification-box">
                <div class="row">
                    <div class="col-lg-3 col-sm-3 col-3 text-center">
                        <img src="https://img.icons8.com/dusk/64/000000/bell.png" class="w-50 rounded-circle">
                    </div>
                    
                    <div class="col-lg-8 col-sm-8 col-8">
                        <strong class="text-info"><a href='.site_url().'/Notification/opennotification/'.$notification["id"].'>' .$notification["notify_type"].'</strong>
                        
                        <div><a href='.site_url().'/Notification/opennotification/'.$notification["id"].'>
                            '.$notification["msg"].'
                        
                        </a>
                        </div>
                        <small class="text-warning">'.$notification["created_at"].'</small>
                    </div>
                </div>
            </li>
            <hr>';

    }
}

function getChallanNo()
{
    $ci =& get_instance();
    $random_number = rand(1000, 999999999);
    $check_challan_no = $ci->db->get_where('payments', array('challan_no'=>$random_number))->result_array();
    if(count($check_challan_no)>0)
    {
        $random_number = getChallanNo();
    }
    else
    {
        return $random_number;
    }
}

function getnotificationscount($user_id)
{
    $ci =& get_instance();
    $notifications = $ci->db->get_where('notifications', array('user_id'=>$user_id,'viewed'=>0,'notification_date'=>date('Y-m-d')))->result_array();

    $totalnoty=0;
    foreach($notifications as $notification)
    {
        $totalnoty++;
    }
    return $totalnoty;
}

function my_pettycash()
{
    $ci =& get_instance();
    $user_id = $ci->session->userdata('user_id');
    $cash = $ci->db->get_where("petty_cash_college_wise","assign_to = '$user_id'")->result_array();

    if (count($cash) > 0)
    {
        return pettycash_statement($cash[0]['id']);
    }
    else
        return 0;

}

function pettycash_statement($pettycashid)
{
    $ci =& get_instance();
    $check_record = $ci->db->get_where('petty_cash_college_wise', array('id'=>$pettycashid))->row();

    $data['from_date'] = date('Y-m-d');
    $data['to_date'] = date('Y-m-d');

    $data['check_record'] = $check_record;
    $data['openbalance']=$check_record->opening_balance;

    $ci->db->select('sum(amount) as amount');
    $ci->db->from('expenses');
    $ci->db->where('add_by_id = "'.$check_record->assign_to.'"  and actual_date >= "'.$check_record->given_date.'"  and actual_date < "'.$data['from_date'].' 23:59:59" and paid_type = "cash" and expense_id NOT IN (select expense_id from bank_reconciliation_statement where expense_id IS NOT NULL)');
    $expenseamount = $ci->db->get()->row();

    $ci->db->select('sum(cash_reversal.amount) as amount');
    $ci->db->from('cash_reversal');
    $ci->db->join('expenses','expenses.expense_id = cash_reversal.expense_id');
    $ci->db->where('expenses.add_by_id = "'.$check_record->assign_to.'"  and cash_reversal.created_at >= "'.$check_record->given_date.'"  and cash_reversal.created_at < "'.$data['from_date'].' 23:59:59"');
    $expensereverseamount = $ci->db->get()->row();

    $ci->db->select('id as trans_id,"receive from" as detail,"trans" as trans_type,amount_given as amount,"" as expstatus, debit_credit,created_at,"" as image,transaction_by as trans_by ');
    $ci->db->from('petty_cash_history');
    $ci->db->where('transaction_pettycash_account = "'.$check_record->id.'" and created_at <= "'.$data['from_date'].' 23:59:59" ');
    $trans_petty_cash = $ci->db->get()->result_array();

    $debit=0;
    $credit=0;

    foreach ($trans_petty_cash as $tran)
    {
        if ($tran['debit_credit']  == 'C' ){
            $credit+=$tran['amount'];
        }
        else  {
            $debit+=$tran['amount'];
        }
    }

    $data['openbalance'] = ($data['openbalance']+$debit+$expensereverseamount->amount)-$credit-$expenseamount->amount;
    return $data['openbalance'];
}

function accountCash_balance($account_id)
{
    $ci =& get_instance();
    $ci->db->select('sum(CASE WHEN debit_credit = "C" THEN amount ELSE 0 END) credit,sum(CASE WHEN debit_credit = "D" THEN amount ELSE 0 END) debit');
    $ci->db->from('transactions_history');
    $ci->db->where('transaction_account_id = "'.$account_id.'" and created_at <="'.date("Y-m-d").' 23:59:59"');
    $trans_petty_cash = $ci->db->get()->row();
    return $trans_petty_cash->debit - $trans_petty_cash->credit;
}

function getStudentResultRemarksLast($cnic)
{
    $ci =& get_instance();
    $ci->db->select('*');
    $ci->db->from('punjab_council_roll_number');
    $ci->db->where('cnic', $cnic);
    $ci->db->order_by('id', "DESC")->limit(2);
    $results = $ci->db->get()->result_array();

    foreach($results as $result)
    {
        if($result['class']==1)
        {
            $class = '1st Year';
        }
        else
        {
            $class = '2nd Year';
        }

        echo 'Class : '. $class;
        echo '<br />';
        echo 'Exam Number : '. $result['council_exam_no'];
        echo '<br />';
        echo 'Roll Number : '.$result['roll_no'];
        if($result['slip_image']!='' && $result['slip_image']!=NULL )
        {
            echo '<a href="'.base_url().'rollno_slips/'.$result['slip_image'].'" target="_blank">
                                                    <i class="fa fa-image"></i> 
                                                </a>';
        }
        echo '<br />';
        echo 'Roll Number Upload Date : '.date('Y-m-d',strtotime($result['date']));
        echo '<br />';
        if($result['result_remarks']!='')
        {
            echo 'Result Upload Date : '.date('Y-m-d',strtotime($result['result_update_date']));
            echo '<br />';
            echo 'Result Remarks : '.$result['result_remarks'];
        }

        if($result['result_image']!='' && $result['result_image']!=NULL )
        {
            echo '<a href="'.base_url().$result['result_image'].'" target="_blank">
                                                    <i class="fa fa-image"></i> 
                                                </a>';
        }


        echo '<hr />';
    }
}

function getStudentMyRemarks($cnic)
{
    $ci =& get_instance();
    $ci->db->select('*');
    $ci->db->from('punjab_council_roll_number');
    $ci->db->where('cnic', $cnic);
    $ci->db->order_by('id', "DESC")->limit(2);
    $results = $ci->db->get()->result_array();
    if(count($results)>1)    {
        return $results[1];
    }
    else    {
        return 0;
    }
}

function Print_expenses($exp_id,$index,$html = "",$campus_id = NULL)
{
    $ci =& get_instance();
    $ci->db->select('*');
    $ci->db->from('expense_category');
    $ci->db->where('sub_of', $exp_id);
    $results = $ci->db->get()->result_array();
    $newindex = $index+1;

    foreach ($results as $exp_cat){
        $name = $exp_cat['name'];
        $status = $exp_cat['status'];
        $id = $exp_cat['expense_category_id'];
        $hrf = site_url()."/expenses/edit_expense_category/".$id;
        if ($exp_cat['has_sub']== "0"){
            echo "<tr class='treegrid-$newindex treegrid-parent-$index'><td>$name</td><td>$status</td>
                    <td>
                        <a href='$hrf' class='btn blue'><i class='fa fa-edit'></i></a>
                        <a data-toggle='modal' data-id='".$exp_cat['expense_category_id']."' data-name='".$exp_cat['expense_category_id']."' class='open-expreversal btn btn-primary' style='width: 150px' href='#expense_category_create' > Add Category?</a>
                    </td>
                  </tr>";
        }else{
            echo "<tr class='treegrid-$newindex treegrid-parent-$index expanded'><td>$name</td><td>$status</td>
                    <td>
                        <a href='$hrf' class='btn blue'><i class='fa fa-edit'></i></a>
                        <a data-toggle='modal' data-id='".$exp_cat['expense_category_id']."' data-name='".$exp_cat['expense_category_id']."' class='open-expreversal btn btn-primary' style='width: 150px' href='#expense_category_create' > Add Category?</a>
                    </td>
                  </tr>";
            $as = Print_expenses($id,$newindex,$html,$campus_id);
            $newindex = $as['index'];
        }
        $newindex++;
    }
    return array("data"=>$html,"index"=>$newindex-1);
}

function Print_tax_expenses($exp_id,$index,$html = "",$campus_id = NULL,$from_date,$to_date,$date_type,$campus_ids)
{
    $ci =& get_instance();
    $ci->db->select('*');
    $ci->db->from('expense_category');
    $ci->db->where('sub_of', $exp_id);
    $results = $ci->db->get()->result_array();
    $newindex = $index+1;

    foreach ($results as $exp_cat){
        $name = $exp_cat['name'];
        $status = $exp_cat['status'];
        $id = $exp_cat['expense_category_id'];
        $hrf = site_url()."/expenses/edit_expense_category/".$id;
        if ($exp_cat['has_sub']== "0"){
            echo "<tr class='treegrid-$newindex treegrid-parent-$index'><td>$name</td>
                    <td>
                    ".getBankExpense($id,$from_date,$to_date,$date_type,$campus_ids)."
                    </td>
                    <td>
                    ".getCashExpense($id,$from_date,$to_date,$date_type,$campus_ids)."
                    </td>
                    <td>
                    ".notTaggedBankExpenses($id,$from_date,$to_date,$date_type,$campus_ids)."
                    </td>
                    <td>
                    ".getBothExpense($id,$from_date,$to_date,$date_type,$campus_ids)."
                    </td>
                  </tr>";
        }else{
            echo "<tr class='treegrid-$newindex treegrid-parent-$index expanded'><td>$name</td>
                    <td>
                    ".getBankExpense($id,$from_date,$to_date,$date_type,$campus_ids)."
                    </td>
                    <td>
                    ".getCashExpense($id,$from_date,$to_date,$date_type,$campus_ids)."
                    </td>
                    <td>
                    ".notTaggedBankExpenses($id,$from_date,$to_date,$date_type,$campus_ids)."
                    </td>
                    <td>
                    ".getBothExpense($id,$from_date,$to_date,$date_type,$campus_ids)."
                    </td>
                  </tr>";
            $as = Print_tax_expenses($id,$newindex,$html,$campus_id,$from_date,$to_date,$date_type,$campus_ids);
            $newindex = $as['index'];
        }
        $newindex++;
    }
    return array("data"=>$html,"index"=>$newindex-1);
}

function getExpenseCategories($expense_category_id)
{
    $ci =& get_instance();

    $cats = array();
    array_push($cats,$expense_category_id);

    $sub_cats_level1 = $ci->db->get_where('expense_category',array('sub_of'=>$expense_category_id))->result_array();
    foreach($sub_cats_level1 as $sub_cat)
    {
        array_push($cats,$sub_cat['expense_category_id']);
        if($sub_cat['has_sub']==1)
        {
            $sub_cats_level2 = $ci->db->get_where('expense_category',array('sub_of'=>$sub_cat['expense_category_id']))->result_array();
            foreach($sub_cats_level2 as $sub_cat)
            {
                array_push($cats,$sub_cat['expense_category_id']);
                if($sub_cat['has_sub']==1)
                {
                    $sub_cats_level3 = $ci->db->get_where('expense_category',array('sub_of'=>$sub_cat['expense_category_id']))->result_array();
                    foreach($sub_cats_level3 as $sub_cat)
                    {
                        array_push($cats,$sub_cat['expense_category_id']);
                        if($sub_cat['has_sub']==1)
                        {
                            $sub_cats_level4 = $ci->db->get_where('expense_category',array('sub_of'=>$sub_cat['expense_category_id']))->result_array();
                            foreach($sub_cats_level4 as $sub_cat)
                            {
                                array_push($cats,$sub_cat['expense_category_id']);
                                if($sub_cat['has_sub']==1)
                                {
                                    $sub_cats_level5 = $ci->db->get_where('expense_category',array('sub_of'=>$sub_cat['expense_category_id']))->result_array();
                                    foreach($sub_cats_level5 as $sub_cat)
                                    {
                                        array_push($cats,$sub_cat['expense_category_id']);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    return $cats;
}


function getBankExpense($category_id,$from_date,$to_date,$date_type,$campus_ids)
{
    $ci =& get_instance();

    $total = 0;

    $expense_categories = getExpenseCategories($category_id);

    $ci->db->select('sum(expenses.amount) as total_amount');
    $ci->db->from('expenses');
    $ci->db->join('bank_reconciliation_statement','bank_reconciliation_statement.expense_id=expenses.expense_id','INNER');
    $ci->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
    $ci->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
    
    $ci->db->where_in('expenses.expense_category_id',$expense_categories);
    $ci->db->where_in('expenses.campus_id',explode(',',$campus_ids));
    if($date_type=='actual_date')
    {
        $ci->db->where(array('bank_reconciliation_statement.trans_date>='=>$from_date, 'bank_reconciliation_statement.trans_date<='=>$to_date));
    }
    else
    {
        $ci->db->where(array('expenses.date>='=>$from_date, 'expenses.date<='=>$to_date));
    }
    
    $ci->db->where('expenses.paid_type','bank');
    $ci->db->where('bank_reconciliation_statement.is_council_fee',NULL);
    //$ci->db->group_by('expenses.expense_category_id,expenses.paid_type');
    $bank_expenses = $ci->db->get()->result_array();

    $total += @$bank_expenses[0]['total_amount'];


    //COUNCIL EXPENSES

    $ci->db->select('sum(expenses.amount) as total_amount');
    $ci->db->from('expenses');
    $ci->db->join('bank_reconciliation_statement','bank_reconciliation_statement.id=expenses.payment_type','inner');
    $ci->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
    $ci->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
    
    $ci->db->where_in('expenses.expense_category_id',$expense_categories);
    $ci->db->where_in('expenses.campus_id',explode(',',$campus_ids));
        $ci->db->where(array('expenses.date>='=>$from_date, 'expenses.date<='=>$to_date));
    
    $ci->db->where('expenses.paid_type','bank');
    //$ci->db->where('bank_reconciliation_statement.is_council_fee',1);
    //$ci->db->group_by('expenses.expense_category_id,expenses.paid_type');
    $council_bank_expenses = $ci->db->get()->result_array();

    $total += @$council_bank_expenses[0]['total_amount'];


    return $total;
}

function notTaggedBankExpenses($category_id,$from_date,$to_date,$date_type,$campus_ids)
{
    $ci =& get_instance();

    $total = 0;

    $expense_categories = getExpenseCategories($category_id);

    $query = 'SELECT SUM(e.amount) as total_amount FROM expenses as e LEFT JOIN bank_reconciliation_statement as s ON e.expense_id=s.expense_id WHERE e.paid_type="bank" AND e.date>="'.$from_date.'" AND e.date<="'.$to_date.'" AND e.expense_category_id IN ('.implode(',',$expense_categories).') AND e.campus_id IN ('.$campus_ids.') AND e.council_exam_no="" AND s.id IS NULL';

    $notTaggedExpenses = $ci->db->query($query)->result_array();

    $total += @$notTaggedExpenses[0]['total_amount'];

    return $total;
}

function getBothExpense($category_id,$from_date,$to_date,$date_type,$campus_ids)
{
    $bankExpense = getBankExpense($category_id,$from_date,$to_date,$date_type,$campus_ids);
    $cashExpense = getCashExpense($category_id,$from_date,$to_date,$date_type,$campus_ids);
    $notTaggedExpense = notTaggedBankExpenses($category_id,$from_date,$to_date,$date_type,$campus_ids);

    $bothExpenses = $bankExpense+$cashExpense+$notTaggedExpense;
    return $bothExpenses;
}

function getCashExpense($category_id,$from_date,$to_date,$date_type,$campus_ids)
{
    $ci =& get_instance();

    $total = 0;

    $expense_categories = getExpenseCategories($category_id);

    $ci->db->select('sum(expenses.amount) as total_amount');
    $ci->db->from('expenses');
    $ci->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
    $ci->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
    
    
    if($date_type=='actual_date')
    {
        $ci->db->where(array('expenses.date>='=>$from_date, 'expenses.date<='=>$to_date));
    }
    else
    {
        $ci->db->where(array('expenses.actual_date>='=>$from_date.' 00:00:00', 'expenses.actual_date<='=>$to_date.' 23:59:59'));
    }
    $ci->db->where('expenses.paid_type','cash');

    $ci->db->where_in('expenses.expense_category_id',$expense_categories);
    $ci->db->where_in('expenses.campus_id',explode(',',$campus_ids));
    //$ci->db->group_by('expenses.expense_category_id,expenses.paid_type');
    $cash_expenses = $ci->db->get()->result_array();

    $total+= @$cash_expenses[0]['total_amount'];

    return $total;
}

function printOccupations($occupation_id,$index,$html = "")
{
    $ci =& get_instance();
    $ci->db->select('*');
    $ci->db->from('occupations');
    $ci->db->where('sub_of', $occupation_id);
    $results = $ci->db->get()->result_array();
    $newindex = $index+1;

    foreach ($results as $occupation){
        $occupation_name = $occupation['occupation_name'];
        $status = '';
        $occupation_id = $occupation['occupation_id'];
        $hrf = site_url()."/students/edit_occupation/".$occupation_id;
        if ($occupation['has_sub']== "0"){
            echo "<tr class='treegrid-$newindex treegrid-parent-$index'><td>$occupation_name</td><td></td>
                    <td>
                        <a href='$hrf' class='btn blue'><i class='fa fa-edit'></i></a>
                        <a data-toggle='modal' data-id='".$occupation['occupation_id']."' data-name='".$occupation['occupation_name']."' class='open-expreversal btn btn-primary' style='width: 150px' href='#expense_category_create' > Add Category?</a>
                    </td>
                  </tr>";
        }else{
            echo "<tr class='treegrid-$newindex treegrid-parent-$index expanded'><td>$occupation_name</td><td></td>
                    <td>
                        <a href='$hrf' class='btn blue'><i class='fa fa-edit'></i></a>
                        <a data-toggle='modal' data-id='".$occupation['occupation_id']."' data-name='".$occupation['occupation_name']."' class='open-expreversal btn btn-primary' style='width: 150px' href='#expense_category_create' > Add Category?</a>
                    </td>
                  </tr>";
            $as = printOccupations($occupation_id,$newindex,$html);
            $newindex = $as['index'];
        }
        $newindex++;
    }
    return array("data"=>$html,"index"=>$newindex-1);
}

function Print_simple_expenses($exp_id,$index,$html = "",$campus_id = NULL)
{
    $ci =& get_instance();
    $ci->db->select('*');
    $ci->db->from('expense_category');
    $ci->db->where('sub_of', $exp_id);
    $results = $ci->db->get()->result_array();
    $newindex = $index+1;

    foreach ($results as $exp_cat){
        $name = $exp_cat['name'];
        $status = $exp_cat['status'];
        $id = $exp_cat['expense_category_id'];
        $hrf = site_url()."/expenses/edit_expense_category/".$id;
        if ($exp_cat['has_sub']== "0"){
            echo "<tr class='treegrid-$newindex treegrid-parent-$index'>
                    <td>$name</td>                    
                  </tr>";
        }else{
            echo "<tr class='treegrid-$newindex treegrid-parent-$index expanded'>
                    <td><strong>$name</strong></td>
                  </tr>";
            $as = Print_simple_expenses($id,$newindex,$html,$campus_id);
            $newindex = $as['index'];
        }
        $newindex++;
    }
    return array("data"=>$html,"index"=>$newindex-1);
}

function Print_expenses_report($exp_id,$index,$from_date,$to_date,$campus_id,$cash_sum,$bank_sum,$html = "")
{
    $ci =& get_instance();
    $ci->db->select('*');
    $ci->db->from('expense_category');
    $ci->db->where('sub_of', $exp_id);
    $results = $ci->db->get()->result_array();
    $newindex = $index+1;

    foreach ($results as $exp_cat){
        $name = $exp_cat['name'];
        $id = $exp_cat['expense_category_id'];
        if ($exp_cat['has_sub']== "0"){
            echo "<tr class='treegrid-$newindex treegrid-parent-$index expanded'><td>$name</td>
                  <td class='price'>";
            $account = $ci->db->select('sum(amount) as cash')->get_where("expenses","date >= '$from_date' and date <= '$to_date' and expense_category_id = '$id' and campus_id = '$campus_id' and expense_id NOT IN (select expense_id from bank_reconciliation_statement where expense_id IS NOT NULL)")->row();
            $cash_sum += $account->cash;
            echo $account->cash."</td><td class='price_bank'>";
            $account = $ci->db->select('sum(amount) as cash')->get_where("expenses","date >= '$from_date' and date <= '$to_date' and expense_category_id = '$id' and campus_id = '$campus_id' and expense_id IN (select expense_id from bank_reconciliation_statement where expense_id IS NOT NULL)")->row();
            $bank_sum += $account->cash;
            echo $account->cash."
                   </td>
                    <td>
                        <a href='".site_url()."/expenses/category_report_expenses/$from_date/$to_date/$id/$campus_id/cash' class='btn blue'><i class='fa fa-eye'></i>View Cash</a>
                        <a href='".site_url()."/expenses/category_report_expenses/$from_date/$to_date/$id/$campus_id/bank' class='btn green'><i class='fa fa-eye'></i>View Bank</a>
                    </td>
                  </tr>";

        }else{
            echo "<tr class='treegrid-$newindex treegrid-parent-$index expanded'>
                    <td>$name</td>
                    <td></td>
                    <td></td></tr>
                    ";
            $as = Print_expenses_report($id,$newindex,$from_date,$to_date,$campus_id,$cash_sum,$bank_sum,$html);
            $newindex = $as['index'];
        }
        $newindex++;
    }
    return array("data"=>$html,"index"=>$newindex-1,"cash_sum"=>$cash_sum,"bank_sum"=>$bank_sum);
}

function print_expenses_categories($exp_id,$count)
{
    $ci =& get_instance();
    $ci->db->select('*');
    $ci->db->from('expense_category');
    $ci->db->where('expense_category_id', $exp_id);
    $results = $ci->db->get()->row();
    if ($results->sub_of == NULL){
        echo $results->name.' / <br />';
    }else{
        $count++;
        echo $results->name.' / <br />';
        $ci->db->select('*');
        $ci->db->from('expense_category');
        $ci->db->where('expense_category_id', $results->sub_of);
        $results = $ci->db->get()->row();
        if ($results->sub_of == NULL){
            echo $results->name;
        }else{
            print_expenses_categories($results->expense_category_id,$count);
        }
    }
}

function getSubExpenses($course_id){
    $ci =& get_instance();
    $exp_cat = $ci->db->get_where("expense_category","expense_category_id = '$course_id'")->row();
    if ($exp_cat->sub_of == NULL)
        echo $exp_cat->name.'<br />';
    else
    {
        echo $exp_cat->name.'<br />';
        getSubExpenses($exp_cat->sub_of);
    }
}

function getSubOccupation($occupation_id)
{
    $ci =& get_instance();
    $occupation = $ci->db->get_where('occupations',array('occupation_id'=>$occupation_id))->row();
    if ($occupation->sub_of == 0)
        echo $occupation->occupation_name.'<br />';
    else
    {
        echo $occupation->occupation_name.'<br />';
        getSubOccupation($occupation->sub_of);
    }
}

function Print_products($exp_id,$index,$html = "")
{
    $ci =& get_instance();
    $ci->db->select('product_names.*,expense_category.name');
    $ci->db->from('product_names');
    $ci->db->join('expense_category','expense_category.expense_category_id = product_names.expense_category_id','left');
    $ci->db->where('product_names.sub_of', $exp_id);
    $results = $ci->db->get()->result_array();
    $newindex = $index+1;

    foreach ($results as $exp_cat){
        $name = $exp_cat['product_name'];
        $status = $exp_cat['type'];
        if($status == '0') {
            $status= "Inventory";
        }
        else {
            $status= "Asset";
        }
        $id = $exp_cat['product_name_id'];
        $cat_name = $exp_cat['name'];
        $hrf = site_url()."/inventory/edit_product_name/".$id;
        if ($exp_cat['has_sub']== "0"){
            $counts = $ci->db->get_where("products",array('product_name_id'=>$id,'consume'=>0,'sold'=>0))->num_rows();
            $ci->db->limit(1);
            $qr_code = @$ci->db->get_where('products',array('product_name_id'=>$id))->result_array();
            if(count($qr_code)>0)
            {
                $qr_code = $qr_code[0]['qr_code'];
            }
            else
            {
                $qr_code = '';
            }

            $ci->db->limit(1);
            $ci->db->order_by('product_id','DESC');
            $image = @$ci->db->get_where('products',array('product_name_id'=>$id,'product_image!='=>''))->result_array();
            if(count($image)>0)
            {
                if($image[0]['online_product_image']!='')
                {
                    $image = '<a class="btn green" href="'.$image[0]['online_product_image'].'" target="_blank"><i class="fa fa-image"></i></a>';
                }
                else
                {
                    $image = '<a class="btn green" href="'.base_url().'inventory_images/'.$image[0]['product_image'].'" target="_blank"><i class="fa fa-image"></i></a>';
                }
            }
            else
            {
                $image='';
            }

            echo "<tr class='treegrid-$newindex treegrid-parent-$index'><td>$name</td><td>$status</td><td>$qr_code</td><td>$counts</td>
                    <td>
                        $image
                        <a href='$hrf' class='btn blue'><i class='fa fa-edit'></i></a>
                        <a data-toggle='modal' data-id='".$id."' data-name='".$name."' class='open-expreversal btn btn-primary' style='width: 150px' href='#expense_category_create' > Add SubProduct?</a>
                    </td>
                  </tr>";
        }else{
            $ci->db->limit(1);
            $qr_code = @$ci->db->get_where('products',array('product_name_id'=>$id))->result_array();
            if(count($qr_code)>0)
            {
                $qr_code = $qr_code[0]['qr_code'];
            }
            else
            {
                $qr_code = '';
            }

            $ci->db->limit(1);
            $ci->db->order_by('product_id','DESC');
            $image = @$ci->db->get_where('products',array('product_name_id'=>$id,'product_image!='=>''))->result_array();
            if(count($image)>0)
            {
                if($image[0]['online_product_image']!='')
                {
                    $image = '<a class="btn green" href="'.$image[0]['online_product_image'].'" target="_blank"><i class="fa fa-image"></i></a>';
                }
                else
                {
                    $image = '<a class="btn green" href="'.base_url().'inventory_images/'.$image[0]['product_image'].'" target="_blank"><i class="fa fa-image"></i></a>';
                }
            }
            else
            {
                $image='';
            }
            echo "<tr class='treegrid-$newindex treegrid-parent-$index expanded'><td>$name</td><td>$status</td><td>$qr_code</td><td></td>
                    <td>
                        $image
                        <a href='$hrf' class='btn blue'><i class='fa fa-edit'></i></a>
                        <a data-toggle='modal' data-id='".$id."' data-name='".$name."' class='open-expreversal btn btn-primary' style='width: 150px' href='#expense_category_create' > Add SubProduct?</a>
                    </td>
                  </tr>";
            $as = Print_products($id,$newindex,$html);
            $newindex = $as['index'];
        }
        $newindex++;
    }
    return array("data"=>$html,"index"=>$newindex-1);
}

function getSubProducts($course_id){
    $ci =& get_instance();
    $exp_cat = $ci->db->get_where("product_names","product_name_id = '$course_id'")->row();
    if ($exp_cat->sub_of == NULL)
        echo $exp_cat->product_name;
    else
    {
        echo $exp_cat->product_name.'<----';
        getSubProducts($exp_cat->sub_of);
    }
}

function get_inner_expenses($cat,$from_date,$to_date,$campus_id){
    $ci =& get_instance();
    $sub_heads = $ci->db->get_where("expense_category","sub_of = '".$cat['expense_category_id']."'")->result_array();
    foreach ($sub_heads as $sub_head) {
        if ($sub_head['has_sub'] == 1) {
            get_inner_expenses($sub_head,$from_date,$to_date,$campus_id);
        } else {
            $ci->db->select('sum(expenses.amount) as total_amount,campuses.campus_id,campuses.campus_name,expense_category.name,expense_category.expense_category_id');
            $ci->db->from('expenses');
            $ci->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
            $ci->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
            $ci->db->where(array('expenses.date>=' => $from_date, 'expenses.date<=' => $to_date));
            $ci->db->where('expenses.expense_category_id', $sub_head['expense_category_id']);
            $ci->db->where('expenses.campus_id', $campus_id);
            $ci->db->group_by('expenses.campus_id,expenses.expense_category_id');
            $exp_data = $ci->db->get()->row();
            if ($exp_data)
                echo $exp_data->name.' : <strong>'.$exp_data->total_amount.'</strong>';
        }
    }
}

function newFeeReversalCount(){
    $ci =& get_instance();
    $feeReversalRequests = $ci->db->get_where('payments_reversal_requests',array('status'=>0))->result_array();
    return count($feeReversalRequests);
}

function getSubProductIds($product_name_id)
{
	$product_name_id = 419;
	$ci =& get_instance();
	$product = $ci->db->get_where('product_names',array('product_name_id'=>$product_name_id))->result_array();
	$ids = array();
	if($product[0]['has_sub']==1)
	{
		
	}
}

function pp($data)
{
	echo '<pre>';
	print_r($data);
	echo '</pre>';
}

function ensure_staff_shift_schema()
{
    $ci =& get_instance();
    if (!$ci->db->table_exists('staff_shifts')) {
        $ci->db->query("CREATE TABLE `staff_shifts` (
            `staff_shift_id` INT(11) NOT NULL AUTO_INCREMENT,
            `shift_name` VARCHAR(255) NOT NULL,
            `description` TEXT NULL,
            `status` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME NULL DEFAULT NULL,
            `updated_at` DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (`staff_shift_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    }
    if ($ci->db->table_exists('users') && !$ci->db->field_exists('staff_shift_id', 'users')) {
        $ci->db->query("ALTER TABLE `users` ADD `staff_shift_id` INT(11) NULL DEFAULT NULL AFTER `staff_type_id`");
    }
    if ($ci->db->table_exists('staff_timing') && !$ci->db->field_exists('staff_shift_id', 'staff_timing')) {
        $ci->db->query("ALTER TABLE `staff_timing` ADD `staff_shift_id` INT(11) NULL DEFAULT NULL AFTER `staff_id`");
    }
}

function get_staff_day_timing($user_id, $day)
{
    $ci =& get_instance();
    ensure_staff_shift_schema();

    $timing = $ci->db
        ->where('staff_id', $user_id)
        ->where('day', $day)
        ->get('staff_timing')
        ->row_array();

    if ($timing) {
        return $timing;
    }

    $user = $ci->db
        ->select('staff_shift_id')
        ->where('user_id', $user_id)
        ->get('users')
        ->row_array();

    $staffShiftId = isset($user['staff_shift_id']) ? (int) $user['staff_shift_id'] : 0;
    if ($staffShiftId <= 0) {
        return array();
    }

    return $ci->db
        ->where('staff_shift_id', $staffShiftId)
        ->where('day', $day)
        ->get('staff_timing')
        ->row_array();
}

function is_off_day_timing($timing)
{
    if (empty($timing)) {
        return false;
    }

    $checkinTiming = isset($timing['checkin_timing']) ? trim((string) $timing['checkin_timing']) : '';
    $checkoutTiming = isset($timing['checkout_timing']) ? trim((string) $timing['checkout_timing']) : '';

    if (strtoupper($checkinTiming) === 'OFF' || strtoupper($checkoutTiming) === 'OFF') {
        return true;
    }

    return $checkinTiming === '00:00:00' || $checkoutTiming === '00:00:00';
}

function isAccountsAccessAdmin()
{
    $ci =& get_instance();
    return $ci->session->userdata('role') == 'Admin';
}

function getAccessIdList($field)
{
    if (isAccountsAccessAdmin()) {
        return null;
    }
    $access = checkUserAccess();
    if (empty($access[0][$field])) {
        return array();
    }
    return array_values(array_filter(array_map('trim', explode(',', $access[0][$field]))));
}

function filterRecordsByAccessIds($records, $idField, $accessField)
{
    $allowed = getAccessIdList($accessField);
    if ($allowed === null) {
        return $records;
    }
    if (empty($allowed)) {
        return array();
    }
    return array_values(array_filter($records, function ($row) use ($allowed, $idField) {
        return in_array((string) $row[$idField], $allowed, true);
    }));
}

function hasAccountDetailsFeature($field)
{
    if (isAccountsAccessAdmin()) {
        return true;
    }
    $access = checkUserAccess();
    return !empty($access[0][$field]);
}

function userCanAccessAccountId($accountId, $accessField)
{
    $allowed = getAccessIdList($accessField);
    if ($allowed === null) {
        return true;
    }
    return in_array((string) $accountId, $allowed, true);
}

function userCanEditAccountId($accountId)
{
    if (!hasAccountDetailsFeature('account_edit')) {
        return false;
    }
    if (isAccountsAccessAdmin()) {
        return true;
    }
    return userCanAccessAccountId($accountId, 'allowed_cash_account_ids')
        || userCanAccessAccountId($accountId, 'allowed_bank_account_ids');
}

function get_council_date($fee_id)
    {
        $ci =& get_instance();
        $fee = $ci->db->get_where('payments',"id = ".$fee_id)->row_array();
        $student_fee_plan = $ci->db->order_by("from_date","ASC")
                    ->where('sequence_fee_id', $fee['council_sequence_id'])
                    ->where('exam_sequence_id', $fee['exam_sequence_id'])
                    ->where('to_date >=', $fee['dead_line'])
                    ->get('council_sequence_fee_rules')
                    ->row_array();
        
        
        return $student_fee_plan ? $student_fee_plan['to_date'] : "";
            
    }

?>