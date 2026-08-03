<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Fee creation engine ported from Punjab_council_roll_number controller.
 */
class Punjab_council_fee_engine {
    /** @var CI_Controller */
    private $ci;
    private $post_data = array();
    private $user_name = 'System';

    public function __construct()
    {
        $this->ci =& get_instance();
    }

    public function set_post(array $data)
    {
        $this->post_data = $data;
    }

    public function set_user_name($name)
    {
        $this->user_name = $name ? $name : 'System';
    }

    private function post($key = null, $xss = true)
    {
        if ($key === null) return $this->post_data;
        return isset($this->post_data[$key]) ? $this->post_data[$key] : null;
    }

    private function session_user($key)
    {
        if ($key === 'name') return $this->user_name;
        return null;
    }

    public function run()
    {
        $data = $this->post();

        //GET ALL POSTS
        $id= @$data['id'];
        $cnic= @$data['cnic'];
        $students_cnic= $data['students_cnic'];
        $students_result= $data['students_result'];
        $result_remarks= @$data['result_remarks'];

        $fee_for_students = $data['fee_for_students'];
        $fee_for_contractors = $data['fee_for_contractors'];
        $dead_line = $data['dead_line'];
        $class= $data['class'];
        $council_exam_no= $data['council_exam_no'];
        if ($class == 1)
            @$type = $this->ci->db->get_where("exam_sequence","first_year = '$council_exam_no'")->row()->type;
        else
            @$type = $this->ci->db->get_where("exam_sequence","second_year = '$council_exam_no'")->row()->type;
        $first_year = $this->post('exam_sequence_first');
        $second_year = $this->post('exam_sequence_second');

        $seq_supplementary = $this->ci->db->get_where('exam_sequence',"id = '$first_year'")->result_array();
        $seq_annual = $this->ci->db->get_where('exam_sequence',"id = '$second_year'")->result_array();

        $total_records = count($this->post('id'));

        for($i=1; $i<=$total_records; $i++)
        {
            //echo $cnic[$i-1];
            if($cnic[$i-1]!='')
            {
                $student = $this->ci->db->get_where('students', array('cnic'=>$cnic[$i-1]))->result_array();
                if(count($student)>0)
                {
                    //ADD COUNCIL FEE OF THIS STUDENT
                    if($student[0]['contractor_id']==0)
                    {
                        //FEE ADD ACCORDING TO STUDENT

                        if($result_remarks[$i-1]=='Pass' && $class==2)
                        {

                        }
                        elseif($result_remarks[$i-1]=='Pass*' && $class==2)
                        {

                        }
                        elseif($result_remarks[$i-1]!='Pass' && $result_remarks[$i-1]!='' && $result_remarks[$i-1]!='Pass*' && $class==1)
                        {
                            //CUSTOM COMMENT FAIL IN 1st YEAR
                            if ($this->post('next_exam') == 'supplementary') {
                                $next_council_exam_no = $seq_supplementary[0]['first_year'];
                                $next_council_exam_id = $seq_supplementary[0]['id'];
                            }
                            else {
                                $next_council_exam_no = $seq_annual[0]['first_year'];
                                $next_council_exam_id = $seq_annual[0]['id'];
                            }
                            $challan_no = $this->get_challan_no();

                            //CUSTOME COMMENT FAIL IN 1st YEAR
                            $custom_comment = 'Fail in Council exam # '.$council_exam_no.' This fee for next exam # '.($next_council_exam_no).' 1st Year';
                            
                            $check = $this->ci->db->get_where('payments',array('payment_plan'=>'consulation fee','student_id'=>$student[0]['student_id'],'payment_comment'=>$custom_comment))->result_array();

                            if(count($check)==0)
                            {
                                $this->ci->db->set('amount', $fee_for_students);
                                $this->ci->db->set('dead_line', $dead_line);
                                $this->ci->db->set('student_id', $student[0]['student_id']);
                                $this->ci->db->set('payment_plan', 'consulation fee');
                                $this->ci->db->set('payment_comment', $custom_comment);
                                $this->ci->db->set('add_by', 'System');
                                $this->ci->db->set('last_edit', 'System');
                                $this->ci->db->set('challan_no', $challan_no);
                                $this->ci->db->set('exam_class', "1");
                                $this->ci->db->set('exam_sequence_id', $next_council_exam_id);
                                $this->ci->db->set('exam_sequence_no', $next_council_exam_no);
                                $this->ci->db->set('custom_student_id', $student[0]['student_id']);
                                $this->ci->db->insert('payments');
                                $insert_id = $this->ci->db->insert_id();
    
                                $this->ci->db->set('fee_id', $insert_id);
                                $this->ci->db->set('amount', $fee_for_students);
                                $this->ci->db->set('dead_line', $dead_line);
                                $this->ci->db->where('id', $id[$i-1]);
                                $this->ci->db->update('punjab_council_roll_number');
                            }
                        }
                        elseif($result_remarks[$i-1]!='Pass' && $result_remarks[$i-1]!='' && $result_remarks[$i-1]!='Pass*' && $class==2)
                        {
                            //CUSTOME COMMENT FAIL IN 2nd YEAR
                            //CUSTOM COMMENT FAIL IN 1st YEAR
                            //CUSTOM COMMENT FAIL IN 1st YEAR
                            if ($this->post('next_exam') == 'supplementary') {
                                $next_council_exam_no = $seq_supplementary[0]['second_year'];
                                $next_council_exam_id = $seq_supplementary[0]['id'];
                            }
                            else {
                                $next_council_exam_no = $seq_annual[0]['second_year'];
                                $next_council_exam_id = $seq_annual[0]['id'];
                            }
                            $challan_no = $this->get_challan_no();

                            //CUSTOME COMMENT FAIL IN 1st YEAR
                            $custom_comment = 'Fail in Council exam # '.$council_exam_no.' This fee for next exam # '.($next_council_exam_no).' 2nd Year';
                            
                            $check = $this->ci->db->get_where('payments',array('payment_plan'=>'consulation fee','student_id'=>$student[0]['student_id'],'payment_comment'=>$custom_comment))->result_array();

                            if(count($check)==0)
                            {
                                $this->ci->db->set('amount', $fee_for_students);
                                $this->ci->db->set('dead_line', $dead_line);
                                $this->ci->db->set('student_id', $student[0]['student_id']);
                                $this->ci->db->set('payment_plan', 'consulation fee');
                                $this->ci->db->set('payment_comment', $custom_comment);
                                $this->ci->db->set('add_by', 'System');
                                $this->ci->db->set('last_edit', 'System');
                                $this->ci->db->set('challan_no', $challan_no);
                                $this->ci->db->set('exam_class', "2");
                                $this->ci->db->set('exam_sequence_id', $next_council_exam_id);
                                $this->ci->db->set('exam_sequence_no', $next_council_exam_no);
                                $this->ci->db->set('custom_student_id', $student[0]['student_id']);
                                $this->ci->db->insert('payments');
                                $insert_id = $this->ci->db->insert_id();
    
                                $this->ci->db->set('fee_id', $insert_id);
                                $this->ci->db->set('amount', $fee_for_students);
                                $this->ci->db->set('dead_line', $dead_line);
                                $this->ci->db->where('id', $id[$i-1]);
                                $this->ci->db->update('punjab_council_roll_number');
                            }
                        }
                        elseif(($result_remarks[$i-1]=='Pass' || $result_remarks[$i-1]=='Pass*') && $class==1)
                        {
                            $challan_no = $this->get_challan_no();

                            $next_council_exam_no = $seq_annual[0]['second_year'];
                            $next_council_exam_id = $seq_annual[0]['id'];
                            $custom_comment = 'Pass in Council exam # '.$council_exam_no.' This fee for next exam # '.($next_council_exam_no).' 2nd Year';
                            
                            $check = $this->ci->db->get_where('payments',array('payment_plan'=>'consulation fee','student_id'=>$student[0]['student_id'],'payment_comment'=>$custom_comment))->result_array();

                            if(count($check)==0)
                            {
                                $this->ci->db->set('amount', $fee_for_students);
                                $this->ci->db->set('dead_line', $dead_line);
                                $this->ci->db->set('student_id', $student[0]['student_id']);
                                $this->ci->db->set('payment_plan', 'consulation fee');
                                $this->ci->db->set('payment_comment', $custom_comment);
                                $this->ci->db->set('add_by', 'System');
                                $this->ci->db->set('last_edit', 'System');
                                $this->ci->db->set('challan_no', $challan_no);
                                $this->ci->db->set('exam_class', "2");
                                $this->ci->db->set('exam_sequence_id', $next_council_exam_id);
                                $this->ci->db->set('exam_sequence_no', $next_council_exam_no);
                                $this->ci->db->set('custom_student_id', $student[0]['student_id']);
                                $this->ci->db->insert('payments');
                                $insert_id = $this->ci->db->insert_id();
    
                                $this->ci->db->set('fee_id', $insert_id);
                                $this->ci->db->set('amount', $fee_for_students);
                                $this->ci->db->set('dead_line', $dead_line);
                                $this->ci->db->where('id', $id[$i-1]);
                                $this->ci->db->update('punjab_council_roll_number');
                            }
                        }
                        
                        //CREATE DIPLOMA CHARGES
                        
                        if(($result_remarks[$i-1]=='Pass' || $result_remarks[$i-1]=='Pass*') && $class==2)
                        {
                            $diploma_fee = $this->post('diploma_fee');
                            $comm = 'Extra Fee For Diploma';
                            
                            $check = $this->ci->db->get_where('payments',array('payment_plan'=>'extra fee','student_id'=>$student[0]['student_id'],'payment_comment'=>$comm))->result_array();

                            if(count($check)==0)
                            {
                                $this->ci->db->set('amount', $diploma_fee);
                                $this->ci->db->set('dead_line', $dead_line);
                                $this->ci->db->set('student_id', $student[0]['student_id']);
                                $this->ci->db->set('payment_plan', 'extra fee');
                                $this->ci->db->set('payment_comment', $comm);
                                $this->ci->db->set('add_by', 'System');
                                $this->ci->db->set('last_edit', 'System');
                                $this->ci->db->set('challan_no', $this->get_challan_no());
                                $this->ci->db->set('custom_student_id', $student[0]['student_id']);
                                $this->ci->db->insert('payments');
                            }
                        }
                    }
                    elseif($student[0]['contractor_id']!=0)
                    {

                        if($result_remarks[$i-1]=='Pass' && $class==2)
                        {

                        }
                        elseif($result_remarks[$i-1]=='Pass*' && $class==2)
                        {

                        }
                        elseif($result_remarks[$i-1]!='Pass' && $result_remarks[$i-1]!='Pass*'  && $result_remarks[$i-1]!='' && $class==1)
                        {
                            //CUSTOM COMMENT FAIL IN 1st YEAR
                            if ($this->post('next_exam') == 'supplementary') {
                                $next_council_exam_no = $seq_supplementary[0]['first_year'];
                                $next_council_exam_id = $seq_supplementary[0]['id'];
                            }
                            else {
                                $next_council_exam_no = $seq_annual[0]['first_year'];
                                $next_council_exam_id = $seq_annual[0]['id'];
                            }

                            $challan_no = $this->get_challan_no();

                            //CUSTOME COMMENT FAIL IN 1st YEAR
                            $custom_comment = 'Fail in Council exam # '.$council_exam_no.' This fee for next exam # '.($next_council_exam_no).' 1st Year';
                            
                            $check = $this->ci->db->get_where('payments',array('payment_plan'=>'consulation fee','student_id'=>$student[0]['student_id'],'payment_comment'=>$custom_comment))->result_array();

                            if(count($check)==0)
                            {
                                $this->ci->db->set('amount', $fee_for_contractors);
                                $this->ci->db->set('dead_line', $dead_line);
                                $this->ci->db->set('contract_id', $student[0]['contract_id']);
                                $this->ci->db->set('payment_plan', 'consulation fee');
                                $this->ci->db->set('payment_comment', $custom_comment);
                                $this->ci->db->set('add_by', 'System');
                                $this->ci->db->set('last_edit', 'System');
                                $this->ci->db->set('challan_no', $challan_no);
                                $this->ci->db->set('exam_class', "1");
                                $this->ci->db->set('exam_sequence_id', $next_council_exam_id);
                                $this->ci->db->set('exam_sequence_no', $next_council_exam_no);
                                $this->ci->db->set('custom_student_id', $student[0]['student_id']);
                                $this->ci->db->insert('payments');
                                $insert_id = $this->ci->db->insert_id();
    
                                $this->ci->db->set('fee_id', $insert_id);
                                $this->ci->db->set('amount', $fee_for_contractors);
                                $this->ci->db->set('dead_line', $dead_line);
                                $this->ci->db->where('id', $id[$i-1]);
                                $this->ci->db->update('punjab_council_roll_number');
                            }
                        }
                        elseif($result_remarks[$i-1]!='Pass' && $result_remarks[$i-1]!='Pass*' && $result_remarks[$i-1]!='' && $class==2)
                        {
                            $challan_no = $this->get_challan_no();
                            if ($this->post('next_exam') == 'supplementary') {
                                $next_council_exam_no = $seq_supplementary[0]['second_year'];
                                $next_council_exam_id = $seq_supplementary[0]['id'];
                            }
                            else {
                                $next_council_exam_no = $seq_annual[0]['second_year'];
                                $next_council_exam_id = $seq_annual[0]['id'];
                            }

                            //CUSTOME COMMENT FAIL IN 1st YEAR
                            $custom_comment = 'Fail in Council exam # '.$council_exam_no.' This fee for next exam # '.($council_exam_no+1).' 2nd Year';
                            
                            $check = $this->ci->db->get_where('payments',array('payment_plan'=>'consulation fee','student_id'=>$student[0]['student_id'],'payment_comment'=>$custom_comment))->result_array();

                            if(count($check)==0)
                            {
                                $this->ci->db->set('amount', $fee_for_contractors);
                                $this->ci->db->set('dead_line', $dead_line);
                                $this->ci->db->set('contract_id', $student[0]['contract_id']);
                                $this->ci->db->set('payment_plan', 'consulation fee');
                                $this->ci->db->set('payment_comment', $custom_comment);
                                $this->ci->db->set('add_by', 'System');
                                $this->ci->db->set('last_edit', 'System');
                                $this->ci->db->set('challan_no', $challan_no);
                                $this->ci->db->set('exam_class', "2");
                                $this->ci->db->set('exam_sequence_id', $next_council_exam_id);
                                $this->ci->db->set('exam_sequence_no', $next_council_exam_no);
                                $this->ci->db->set('custom_student_id', $student[0]['student_id']);
                                $this->ci->db->insert('payments');
                                $insert_id = $this->ci->db->insert_id();
    
                                $this->ci->db->set('fee_id', $insert_id);
                                $this->ci->db->set('amount', $fee_for_contractors);
                                $this->ci->db->set('dead_line', $dead_line);
                                $this->ci->db->where('id', $id[$i-1]);
                                $this->ci->db->update('punjab_council_roll_number');
                            }
                        }
                        elseif(($result_remarks[$i-1]=='Pass' || $result_remarks[$i-1]=='Pass*') && $class==1)
                        {
                            $challan_no = $this->get_challan_no();

                            //CUSTOME COMMENT FAIL IN 1st YEAR
                            $next_council_exam_no = $seq_annual[0]['second_year'];
                            $next_council_exam_id = $seq_annual[0]['id'];
                            $custom_comment = 'Pass in Council exam # '.$council_exam_no.' This fee for next exam # '.($next_council_exam_no).' 2nd Year';
                            
                            $check = $this->ci->db->get_where('payments',array('payment_plan'=>'consulation fee','student_id'=>$student[0]['student_id'],'payment_comment'=>$custom_comment))->result_array();

                            if(count($check)==0)
                            {
                                $this->ci->db->set('amount', $fee_for_contractors);
                                $this->ci->db->set('dead_line', $dead_line);
                                $this->ci->db->set('contract_id', $student[0]['contract_id']);
                                $this->ci->db->set('payment_plan', 'consulation fee');
                                $this->ci->db->set('payment_comment', $custom_comment);
                                $this->ci->db->set('add_by', 'System');
                                $this->ci->db->set('last_edit', 'System');
                                $this->ci->db->set('challan_no', $challan_no);
                                $this->ci->db->set('exam_class', "2");
                                $this->ci->db->set('exam_sequence_id', $next_council_exam_id);
                                $this->ci->db->set('exam_sequence_no', $next_council_exam_no);
                                $this->ci->db->set('custom_student_id', $student[0]['student_id']);
                                $this->ci->db->insert('payments');
                                $insert_id = $this->ci->db->insert_id();
    
                                $this->ci->db->set('fee_id', $insert_id);
                                $this->ci->db->set('amount', $fee_for_contractors);
                                $this->ci->db->set('dead_line', $dead_line);
                                $this->ci->db->where('id', $id[$i-1]);
                                $this->ci->db->update('punjab_council_roll_number');
                            }
                        }
                        //CREATE DIPLOMA CHARGES
                        
                        if(($result_remarks[$i-1]=='Pass' || $result_remarks[$i-1]=='Pass*') && $class==2)
                        {
                            $diploma_fee = $this->post('diploma_fee');
                            $comm = 'Extra Fee For Diploma';
                            
                            $check = $this->ci->db->get_where('payments',array('payment_plan'=>'extra fee','student_id'=>$student[0]['student_id'],'payment_comment'=>$comm))->result_array();

                            if(count($check)==0)
                            {
                                $this->ci->db->set('amount', $diploma_fee);
                                $this->ci->db->set('dead_line', $dead_line);
                                $this->ci->db->set('student_id', $student[0]['student_id']);
                                $this->ci->db->set('payment_plan', 'extra fee');
                                $this->ci->db->set('payment_comment', $comm);
                                $this->ci->db->set('add_by', 'System');
                                $this->ci->db->set('last_edit', 'System');
                                $this->ci->db->set('challan_no', $this->get_challan_no());
                                $this->ci->db->set('custom_student_id', $student[0]['student_id']);
                                $this->ci->db->insert('payments');
                            }
                        }
                    }
                }
                else
                {
                    //echo 'Ni Mila <br/>';
                }
            }
        }

        // COMMENT BELOW LINE FOR EXTRA FEES NOT CREATED
        $this->create_extra_fee($students_result,$students_cnic,$council_exam_no,$class,$dead_line);
        if (@$this->post("coming_from"))
            /* redirect fee_not_created */;
        else
            /* redirect add_council_fee */;


    }
    public function create_extra_fee(array $students_result,array $students_cnic,$council_exam_no,$class,$dead_line)
    {
        $rule = $this->ci->db->get('council_rules')->row();
        $extra_fee=$rule->total_fee;
        $no_of_exams=$rule->no_of_exams;

        foreach($students_cnic as $key=>$cnic){

            $student = $this->ci->db->join('classes','classes.class_id = students.class_id')->where( array('cnic'=>$cnic))->get('students')->result_array();
            /*
            $this->ci->db->select('sum(counted) as total_count');
            $this->ci->db->from('council_exam_count');
            $this->ci->db->where('student_id = "'.$student[0]['student_id'].'"');
            $counted_from_table=$this->ci->db->get()->result_array();
            */
            if(count($student)>0)
            {
                if($student[0]['contractor_id']==0)
                {
                    $council_fee_count = $this->ci->db->get_where('payments',array('student_id'=>$student[0]['student_id'],'payment_plan'=>'consulation fee'))->result_array();
                    $deleted_council_fee_count = $this->ci->db->get_where('archive_payments',array('student_id'=>$student[0]['student_id'],'payment_plan'=>'consulation fee'))->result_array();
                    
                    $count_council_fees = count($council_fee_count)+count($deleted_council_fee_count);
                    
                    //CHECK FEE ALREADY CREATED OR NOT
                    $comm = 'This Extra Fee Created Against Council '.$this->addOrdinalSuffix((count($council_fee_count))).' Fee.';
                    $check = $this->ci->db->get_where('payments',array('student_id'=>$student[0]['student_id'],'payment_comment'=>$comm))->result_array();
                    
                    //CHECK STUDENT IS PASSED OR NOT IN 2ND YEAR
                    $this->ci->db->select('*');
                    $this->ci->db->from('punjab_council_roll_number');
                    $this->ci->db->where('class',2);
                    $this->ci->db->where('cnic',$cnic);
                    $this->ci->db->like('result_remarks','Pass');
                    $check_pass_status = $this->ci->db->get()->result_array();
                    
                    if(count($check)==0 && count($check_pass_status)==0)
                    {
                        //CREATE EXTRA FEE IF STUDENT COUNCIL FEE COUNT GREATER THAN 4
                        if($count_council_fees>$no_of_exams)
                        {
                            $this->ci->db->set('amount', $extra_fee);
                            $this->ci->db->set('dead_line', date('Y-m-d', strtotime('-1 day', strtotime($dead_line))));
                            $this->ci->db->set('student_id', $student[0]['student_id']);
                            $this->ci->db->set('payment_plan', 'extra fee');
                            $this->ci->db->set('payment_comment', $comm);
                            $this->ci->db->set('add_by', 'System');
                            $this->ci->db->set('last_edit', 'System');
                            $this->ci->db->set('challan_no', $this->get_challan_no());
                            $this->ci->db->set('custom_student_id', $student[0]['student_id']);
                            $this->ci->db->insert('payments');
                        }
                    }
                    
                    /*
                    //FEE ADD ACCORDING TO STUDENT
                    if($students_result[$key]=='Pass' && $class==2){}
                    elseif($students_result[$key]=='Pass*' && $class==2)
                    {

                    }
                    elseif($students_result[$key]!='Pass' && $students_result[$key]!='Pass*' && $class==1)
                    {
                        $created = false;
                        //CUSTOME COMMENT FAIL IN 1st YEAR
                        $comm = 'Extra Fee due to Attached with College for more then 2 Years';
                        $custom_comment = 'Fail in Council exam # '.$council_exam_no.' This fee for next exam # '.($council_exam_no+1).' 1st Year';
                        if((($council_exam_no-$student[0]['exam_no'])+2)>$no_of_exams)
                        {
                            $willcreate=((($council_exam_no+1)-($student[0]['exam_no']-1))+1)-$no_of_exams;

                            $willcreate=$willcreate-$counted_from_table[0]['total_count'];
                            if($willcreate > 0){
                                for($x=0;$x<$willcreate;$x++)
                                {
                                    $this->ci->db->set('amount', $extra_fee);
                                    $this->ci->db->set('dead_line', $dead_line);
                                    $this->ci->db->set('student_id', $student[0]['student_id']);
                                    $this->ci->db->set('payment_plan', 'extra fee');
                                    $this->ci->db->set('payment_comment', $comm);
                                    $this->ci->db->set('add_by', 'System');
                                    $this->ci->db->set('last_edit', 'System');
                                    $this->ci->db->set('challan_no', $this->get_challan_no());
                                    $this->ci->db->set('custom_student_id', $student[0]['student_id']);
                                    $this->ci->db->insert('payments');

                                    $this->ci->db->set('counted', '1');
                                    $this->ci->db->set('student_id', $student[0]['student_id']);
                                    $this->ci->db->set('created_by', $this->session_user('name'));
                                    $this->ci->db->insert('council_exam_count');
                                    $created = true;
                                }
                            }
                        }
                        if ($created == false)
                        {
                            $this->ci->db->select('count(*) as total_count');
                            $this->ci->db->from('punjab_council_roll_number');
                            $this->ci->db->where("cnic = '$cnic' and class = '1'");
                            $counted_fee=$this->ci->db->get()->row();
                            if ($counted_fee)
                            {
                                if (count($counted_fee) == 3)
                                {
                                    $this->ci->db->set('amount', $extra_fee);
                                    $this->ci->db->set('dead_line', $dead_line);
                                    $this->ci->db->set('student_id', $student[0]['student_id']);
                                    $this->ci->db->set('payment_plan', 'extra fee');
                                    $this->ci->db->set('payment_comment', $comm);
                                    $this->ci->db->set('add_by', 'System');
                                    $this->ci->db->set('last_edit', 'System');
                                    $this->ci->db->set('challan_no', $this->get_challan_no());
                                    $this->ci->db->set('custom_student_id', $student[0]['student_id']);
                                    $this->ci->db->insert('payments');
                                }
                            }
                        }
                    }
                    elseif($students_result[$key]!='Pass' && $students_result[$key]!='Pass*' && $class==2)
                    {


                        //CUSTOME COMMENT FAIL IN 1st YEAR
                        $custom_comment = 'Fail in Council exam # '.$council_exam_no.' This fee for next exam # '.($council_exam_no+1).' 2nd Year';

                        $counts=(($council_exam_no+1)-($student[0]['exam_no']-1))+2;



                        if($counts>$no_of_exams)
                        {

                            $willcreate=($counts-$no_of_exams);
                            $comm = 'Extra Fee due to Attached with College for more then 2 Years';

                            $willcreate=$willcreate-$counted_from_table[0]['total_count'];

                            if($willcreate > 0){
                                for($x=0;$x<$willcreate;$x++)
                                {
                                    $this->ci->db->set('amount', $extra_fee);
                                    $this->ci->db->set('dead_line', $dead_line);
                                    $this->ci->db->set('student_id', $student[0]['student_id']);
                                    $this->ci->db->set('payment_plan', 'extra fee');
                                    $this->ci->db->set('payment_comment', $comm);
                                    $this->ci->db->set('add_by', 'System');
                                    $this->ci->db->set('last_edit', 'System');
                                    $this->ci->db->set('challan_no', $this->get_challan_no());
                                    $this->ci->db->set('custom_student_id', $student[0]['student_id']);
                                    $this->ci->db->insert('payments');

                                    $this->ci->db->set('counted', '1');
                                    $this->ci->db->set('student_id', $student[0]['student_id']);
                                    $this->ci->db->set('created_by', $this->session_user('name'));
                                    $this->ci->db->insert('council_exam_count');

                                }
                            }


                        }



                    }
                    elseif(($students_result[$key]=='Pass' || $students_result[$key]=='Pass*') && $class==1)
                    {
                        //CUSTOME COMMENT FAIL IN 1st YEAR
                        if($council_exam_no % 2 == 0){
                            $next_council_exam_no = $council_exam_no-1;
                        }
                        else{
                            $next_council_exam_no = $council_exam_no;
                        }
                        $custom_comment = 'Pass in Council exam # '.$council_exam_no.' This fee for next exam # '.($next_council_exam_no).' 2nd Year';

                        if(((($council_exam_no+1)-($student[0]['exam_no']-1))+1)>$no_of_exams)
                        {
                            $willcreate=(((($council_exam_no+1)-($student[0]['exam_no']-1))+1)-$no_of_exams);
                            $comm = 'Extra Fee due to Attached with College for more then 2 Years';
                            $willcreate=$willcreate-$counted_from_table[0]['total_count'];

                            if($willcreate > 0){
                                for($x=0;$x<$willcreate;$x++)
                                {
                                    $this->ci->db->set('amount', $extra_fee);
                                    $this->ci->db->set('dead_line', $dead_line);
                                    $this->ci->db->set('student_id', $student[0]['student_id']);
                                    $this->ci->db->set('payment_plan', 'extra fee');
                                    $this->ci->db->set('payment_comment', $comm);
                                    $this->ci->db->set('add_by', 'System');
                                    $this->ci->db->set('last_edit', 'System');
                                    $this->ci->db->set('challan_no', $this->get_challan_no());
                                    $this->ci->db->set('custom_student_id', $student[0]['student_id']);
                                    $this->ci->db->insert('payments');

                                    $this->ci->db->set('counted', '1');
                                    $this->ci->db->set('student_id', $student[0]['student_id']);
                                    $this->ci->db->set('created_by', $this->session_user('name'));
                                    $this->ci->db->insert('council_exam_count');
                                }
                            }
                        }
                    }
                    */
                }
            }
        }
    }
    public function get_challan_no()
    {
        $random_number = rand(1000, 999999999);
        $check_challan_no = $this->ci->db->get_where('payments', array('challan_no'=>$random_number))->result_array();
        if(count($check_challan_no)>0)
        {
            $random_number = $this->get_challan_no();
        }
        else
        {
            return $random_number;
        }
    }
    function addOrdinalSuffix($number) {
        if (!is_numeric($number)) {
            return $number; // Return as is if not a number
        }
        
        $suffixes = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
        
        if (($number % 100) >= 11 && ($number % 100) <= 13) {
            return $number . 'th';
        }
        
        return $number . $suffixes[$number % 10];
    }
}
