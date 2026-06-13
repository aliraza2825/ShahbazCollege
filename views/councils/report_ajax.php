<?php 
function getOrdinal($number)
    {
        $suffixes = ['th','st','nd','rd','th','th','th','th','th','th'];
    
        if (($number % 100) >= 11 && ($number % 100) <= 13) {
            return $number . 'th';
        }
    
        return $number . $suffixes[$number % 10];
    }
?>

<table class="table table-striped table-bordered table-hover" id="sample_2">
    <thead>
    <tr>
        <th class="hidden">
            hidden
        </th>
        <th>
            Council+Council Name
        </th>

        <th>
            Details
        </th>
        <th>
            Total Students
        </th>
    </tr>
    </thead>
    <tbody>
    <?php
        $i=1;
        $final_liablity = 0;
        foreach($council_exams as $council_exam):
            $this->db->order_by('last_date','ASC');
            $tasks = $this->db
                ->join('councils','councils.council_id = council_sequence.council_id','inner')
                ->where('course_id', $council_exam['course_id'])
                ->order_by("STR_TO_DATE(last_date,'%d/%m')", "ASC", false)
                ->get('council_sequence')
                ->result_array();
            $fee_tasks = [];
            $other_tasks = [];

            foreach ($tasks as $task) {
                if ($task['action_type'] == 'fee') {
                    $fee_tasks[] = $task;
                } else {
                    $other_tasks[] = $task;
                }
            }
            $tasks = $fee_tasks;
            $task_wise_amounts = [];
            $course_type = $council_exam['course_type'] == 'Annual' ? 'Year' : $course['course_type'];
    ?>
    <tr class="sequence-row odd gradeX" data-status="<?= $council_exam['status']; ?>">

        <td class="hidden">
            <?php echo $i;?>
        </td>
        <td>
            <?php
            //GET ALL STUDENTS OF THIS EXAM NUMBER
            $this->db->select('classes.session,COUNT(DISTINCT students.student_id) as total_students');
            $this->db->from('payments');
            $this->db->join('students','students.student_id = COALESCE(NULLIF(payments.student_id, 0), payments.custom_student_id)','inner');
            $this->db->join('classes','classes.class_id=students.class_id','left');
            $this->db->where('students.status',1);
            $this->db->where('students.course_id',$council_exam['course_id']);
            $this->db->like('payments.payment_comment','This fee for next exam # '.$council_exam['first_year'].' '.getOrdinal($council_exam['class']).' '.$course_type,'both');
            $totalStudents = $this->db->get()->result_array();
            
            $total_students_count = 0;
            foreach($totalStudents as $student)
            {
                echo  "<strong>".$council_exam['course_name']."</strong><br><br>";
                $total_students_count +=$student['total_students'];
                //TASKS

                foreach($tasks as $task)
                {
                    $task_wise_counts = [];
                    $paid =0;
                    $unpaid=0;
                    $expense_done = 0;
                    $waiting_for_expense = 0;
                    $ids = [];
                    $total_fee_amount = 0;
                    $total_expense_amount = 0;
                    $total_profit_amount = 0;
                    $total_liability = 0;


                    //GET FEES CREATED AGAINST THIS TASK
                    $this->db->select('students.student_id,payments.paid,payments.amount,payments.paid_date');
                    $this->db->from('payments');
                    $this->db->join(
                        'students',
                        'students.student_id = COALESCE(NULLIF(payments.student_id, 0), payments.custom_student_id)',
                        'inner'
                    );
                    $this->db->join('classes','classes.class_id=students.class_id','inner');
                    $this->db->where('students.status',1);
                    $this->db->where('students.course_id',$council_exam['course_id']);
                    $this->db->where('payments.council_sequence_id',$task['council_sequence_id']);
                    $this->db->like('payments.payment_comment','This fee for next exam # '.$council_exam['first_year'].' '.getOrdinal($council_exam['class']).' '.$course_type,'both');
                    $this->db->group_by('students.student_id');
                    $taskStudents = $this->db->get()->result_array();
                    foreach($taskStudents as $taskStudent)
                    {
                        $ids[] = $taskStudent['student_id'];
                        if($task['has_expense'])
                            {
                                $expense = $this->db->get_where('expenses',array('student_id'=>$taskStudent['student_id'],'council_exam_no' => $council_exam['first_year'],'council_sequence_id' => $task['council_sequence_id'],'class' => $council_exam['class']))->result_array();
                                if( count($expense) > 0){
                                    $expense_done++;
                                    $fee_amount = $taskStudent['amount'];
                                    $expense_amount = (float)$expense[0]['amount'];
                                    $total_expense_amount += $expense_amount;
                                    $total_profit_amount += ($fee_amount - $expense_amount);
                                }else{
                                    $waiting_for_expense++;
                                    $expense = $this->db->order_by('from_date','ASC')->get_where('council_sequence_fee_rules',array('exam_sequence_id'=>$council_exam['id'], 'to_date >=' => $taskStudent['paid_date']))->row_array();
                                    $expense_amount =  (float)$expense['expense_fee'];
                                    $total_liability += $expense_amount;
                                }
                            }
                        if($taskStudent['paid']==1)
                        {
                            $paid++;
                            $fee_amount = (float)$taskStudent['amount'];
                            $total_fee_amount += $fee_amount;
                            
                        }
                        else
                        {
                            $unpaid++;
                        }
                    }



                    $feenotcreated = $student['total_students']-$paid-$unpaid;
                    $feecreated = abs($paid+$unpaid);
                    if($feenotcreated>0)
                    {
                        $feenotcreated = '<span class="alert-danger">'.$feenotcreated.'</span>';
                    }

                    $total = $student['total_students'];

                    $feeCreatedColor = ($feecreated == $total) ? 'green' : 'red';

                    $feeNotCreatedColor = ($feenotcreated > 0) ? 'red' : 'green';

                    $paidColor = ($paid == $total) ? 'green' : 'red';

                    $unpaidColor = ($unpaid == $total) ? 'green' : 'red';

                    $expenseDoneColor = ($expense_done == $total) ? 'green' : 'red';

                    $expenseWaitingColor = ($waiting_for_expense > 0) ? 'red' : 'green';

                    $baseUrl = site_url().'/councils/students/'.$council_exam['course_id'].'/0/'.$council_exam['first_year'].'/'.$council_exam['class'];
                    if($task['has_fee'] == 1){
                        if ($task['has_expense'] == 0) { ?>

                            <strong><?php echo $task['name'].' -'; ?></strong>
                            <strong><?php echo $task['type_name']; ?></strong>

                            <a style="color:<?php echo $feeCreatedColor ?>"
                            href="<?php echo $baseUrl; ?>/fee_created/<?php echo $task['council_sequence_id']?>"
                            target="_blank">
                            Fee Created (<?php echo $feecreated; ?>)
                            </a>
                            |

                            <a style="color:<?php echo $feeNotCreatedColor ?>"
                            href="<?php echo $baseUrl; ?>/fee_not_created/<?php echo $task['council_sequence_id']?>"
                            target="_blank">
                            Fee Not Created (<?php echo $feenotcreated; ?>)
                            </a>
                            |

                            <a style="color:<?php echo $paidColor ?>"
                            href="<?php echo $baseUrl; ?>/paid/<?php echo $task['council_sequence_id']?>"
                            target="_blank">
                            Paid In College (<?php echo $paid; ?>)
                            </a>
                            |

                            <a style="color:<?php echo $unpaidColor ?>"
                            href="<?php echo $baseUrl; ?>/unpaid/<?php echo $task['council_sequence_id']?>"
                            target="_blank">
                            Unpaid in College (<?php echo $unpaid; ?>) and add expense
                            </a>

                            <br><br>

                        <?php }
                        else { ?>
                            <strong><?php echo $task['name'].' -'; ?></strong>
                            <strong><?php echo $task['type_name']; ?></strong>

                            <a style="color:<?php echo $feeCreatedColor ?>"
                            href="<?php echo $baseUrl; ?>/fee_created/<?php echo $task['council_sequence_id']?>"
                            target="_blank">
                            Fee Created (<?php echo $feecreated; ?>)
                            </a>
                            |

                            <a style="color:<?php echo $feeNotCreatedColor ?>"
                            href="<?php echo $baseUrl; ?>/fee_not_created/<?php echo $task['council_sequence_id']?>"
                            target="_blank">
                            Fee Not Created (<?php echo $feenotcreated; ?>)
                            </a>
                            |

                            <a style="color:<?php echo $paidColor ?>"
                            href="<?php echo $baseUrl; ?>/paid/<?php echo $task['council_sequence_id']?>"
                            target="_blank">
                            Paid in College (<?php echo 'Paid( '.$paid.' )' .' and Unpaid ( '.$unpaid.' )' ?>) & Add Expense
                            </a>
                            |

                            <a style="color:<?php echo $unpaidColor ?>"
                            href="<?php echo $baseUrl; ?>/unpaid/<?php echo $task['council_sequence_id']?>"
                            target="_blank">
                            Unpaid in College (<?php echo $unpaid; ?>)
                            </a>
                            |

                            <a style="color:<?php echo $expenseDoneColor ?>"
                            href="<?php echo $baseUrl; ?>/paid/<?php echo $task['council_sequence_id']?>"
                            target="_blank">
                            Expense Done (<?php echo $expense_done; ?>)
                            </a>
                            |

                            <a style="color:<?php echo $expenseWaitingColor ?>"
                            href="<?php echo $baseUrl; ?>/paid/<?php echo $task['council_sequence_id']?>"
                            target="_blank">
                            Expense Waiting (<?php echo $waiting_for_expense; ?>)
                            </a>

                            <br><br>
                        <?php }
                        if(!isset($task_wise_amounts[$task['council_sequence_id']]))
                        {
                            $task_wise_amounts[$task['council_sequence_id']] = [
                                'task_name'            => $task['name'],
                                'type_name'            => $task['type_name'],
                                'fee_amount'           => 0,
                                'expense_amount'       => 0,
                                'profit_amount'        => 0,
                                'paid_students'        => 0,
                                'unpaid_students'      => 0,
                                'expense_done'         => 0,
                                'liability'            => 0,
                                'waiting_for_expense'  => 0
                            ];
                        }

                        $task_wise_amounts[$task['council_sequence_id']]['fee_amount'] += $total_fee_amount;
                        $task_wise_amounts[$task['council_sequence_id']]['expense_amount'] += $total_expense_amount;
                        $task_wise_amounts[$task['council_sequence_id']]['profit_amount'] += $total_profit_amount;
                        $task_wise_amounts[$task['council_sequence_id']]['paid_students'] += $paid;
                        $task_wise_amounts[$task['council_sequence_id']]['unpaid_students'] += $unpaid;
                        $task_wise_amounts[$task['council_sequence_id']]['expense_done'] += $expense_done;
                        $task_wise_amounts[$task['council_sequence_id']]['waiting_for_expense'] += $waiting_for_expense;
                        $task_wise_amounts[$task['council_sequence_id']]['liability'] += $total_liability;
                    }

                }

            }
            if($total_students_count>0)
            {
                echo '<strong>Total Students : '.$total_students_count.'</strong>'.' In-progress<br>';

            }
            ?>
            <button 
                type="button"
                class="btn btn-primary btn-sm view-accounts-btn"
                style="background-color: green;"
                data-toggle="modal"
                data-target="#accountsModal"
                data-title="<?php echo htmlspecialchars($council_exam['course_name'].' - Exam # '.$council_exam['first_year'], ENT_QUOTES, 'UTF-8'); ?>"
                data-accounts='<?php echo json_encode(array_values($task_wise_amounts), JSON_HEX_APOS | JSON_HEX_QUOT); ?>'
            >
                <?php $total_liability = array_sum(array_column($task_wise_amounts, 'liability'));
                    echo 'Total Liablity : '.$total_liability;  
                    $final_liablity += $total_liability;

                    if($i == count($council_exams) && $this->uri->segment(3) == 'Active'){
                        ?>
                        <script>
                            var el = document.getElementById('total_liablity');
                            if(el){
                                el.textContent = 'Total Liablity : <?php echo $final_liablity; ?>';
                            }
                        </script>
                        <?php
                    }       
                ?>
            </button>
        </td>

        <td>
            Exam No. : <strong><?php echo $council_exam['first_year'];?></strong><br>
            <?php echo $council_exam['first_year_type'];?><br>
            Class/Semmester : <?php echo  $council_exam['class'];?><br><br>
            <?php

                foreach($other_tasks as $task)
                {
                    $action_type = trim(strtolower($task['action_type']));

                    if ($action_type == 'information') {
                        $page = 'info_students';
                    } elseif ($action_type == 'add_roll_no') {
                        $page = 'documents_students';
                    } else {
                        $page = 'result_students';
                    }

                    $baseUrl = site_url().'/councils/'.$page.'/'.$council_exam['course_id'].'/0/'.$council_exam['first_year'].'/'.$council_exam['class'];
                    $done =0;
                    $not_done=0;
                    //GET FEES CREATED AGAINST THIS TASK
                    $this->db->select('students.*,payments.paid');
                    $this->db->from('payments');
                    $this->db->join(
    'students',
    'students.student_id = COALESCE(NULLIF(payments.student_id, 0), payments.custom_student_id)',
    'inner'
);
                    $this->db->join('classes','classes.class_id=students.class_id','inner');
                    $this->db->where('students.status',1);
                    $this->db->where('students.course_id',$council_exam['course_id']);
                    $this->db->like('payments.payment_comment','This fee for next exam # '.$council_exam['first_year'].' '.getOrdinal($council_exam['class']).' '.$course_type,'both');
                    $this->db->group_by('students.student_id');
                    $taskStudents = $this->db->get()->result_array();
                    $total_students = count($taskStudents); 
                    foreach($taskStudents as $taskStudent)
                    {
                        $row = $this->db->get_where(
                            'punjab_council_roll_number',
                            array(
                                'cnic' => $taskStudent['cnic'],
                                'council_exam_no' => $council_exam['first_year'],
                                'class' => $council_exam['class'],
                                'course_id' => $task['course_id']
                            )
                        )->row_array();

                        $found = false;
                        if($task['action_type'] == 'information'){
                            if(!empty($row) && !empty($row['extra_info']))
                            {
                                $extra_info = json_decode($row['extra_info'], true);

                                if (!empty($extra_info)) {

                                    $latestInfo = null;
                            
                                    foreach ($extra_info as $info) {
                            
                                        if ($info['council_sequence_id'] == $task['council_sequence_id']) {
                            
                                            if (
                            
                                                empty($latestInfo) ||
                            
                                                strtotime($info['informed_at']) > strtotime($latestInfo['informed_at'])
                            
                                            ) {
                            
                                                $latestInfo = $info;
                            
                                            }
                            
                                        }
                            
                                    }
                            
                                    if (!empty($latestInfo) && !empty($latestInfo['informed']) && $latestInfo['informed'] == 1) {
                            
                                        $found = true;
                            
                                    }
                            
                                }
                            }
                        }else{
                            if(!empty($row))
                            {
                                if($task['action_type'] == 'add_roll_no' && $row['roll_no'] != '' && $row['roll_no'] != null)
                                {
                                    $found = true;
                                }elseif($task['action_type'] == 'add_result' && $row['result_remarks'] != '' && $row['result_remarks'] != null)
                                {
                                    $found = true;
                                }
                            }
                        }

                        if($found){
                            $done++;
                        }else{
                            $not_done++;
                        }
                    }

                    echo ' <strong>'.$task['type_name'].'</strong> <a href="'.$baseUrl.'/done/'.$task['council_sequence_id'].'/'.$council_exam['id'].'" target="_blank">Done ('.$done.')</a> | <a href="'.$baseUrl.'/waiting/'.$task['council_sequence_id'].'/'.$council_exam['id'].'" target="_blank">Waiting ('.$not_done.')</a><br><br>';
                }
            ?>
        </td>

        <td>
            <?php
                //GET ALL STUDENTS OF THIS EXAM NUMBER
                $this->db->select('students.student_id,classes.session,COUNT(DISTINCT students.student_id) as total_students');
                $this->db->from('payments');
                $this->db->join(
    'students',
    'students.student_id = COALESCE(NULLIF(payments.student_id, 0), payments.custom_student_id)',
    'inner'
);
                $this->db->join('classes','classes.class_id=students.class_id','inner');
                $this->db->where('students.status',1);
                $this->db->where('students.course_id',$council_exam['course_id']);
                $this->db->like('payments.payment_comment','This fee for next exam # '.$council_exam['first_year'].' '.getOrdinal($council_exam['class']).' '.$course_type,'both');
                $this->db->group_by('classes.session');
                $totalStudents = $this->db->get()->result_array();
                $total_students_count = 0;
                foreach($totalStudents as $student)
                {
                    echo $student['session'].' (<a target="_blank" href="'.site_url().'/councils/students/'.$council_exam['course_id'].'/'.$student['session'].'/'.$council_exam['first_year'].'/1">Total Students: '.$student['total_students'].'</a>)<br />';
                    $total_students_count +=$student['total_students'];
                    //TASKS
                    $paid =0;
                    $unpaid=0;
                    foreach($tasks as $task)
                    {
                        //GET FEES CREATED AGAINST THIS TASK
                        $this->db->select('students.student_id,payments.paid');
                        $this->db->from('payments');
                        $this->db->join(
    'students',
    'students.student_id = COALESCE(NULLIF(payments.student_id, 0), payments.custom_student_id)',
    'inner'
);
                        $this->db->join('classes','classes.class_id=students.class_id','inner');
                        $this->db->where('students.status',1);
                        $this->db->where('students.course_id',$council_exam['course_id']);
                        $this->db->where('classes.session',$student['session']);
                        $this->db->where('payments.council_sequence_id',$task['council_sequence_id']);
                        $this->db->like('payments.payment_comment','This fee for next exam # '.$council_exam['first_year'].' '.getOrdinal($council_exam['class']).' '.$course_type,'both');
                        $taskStudents = $this->db->get()->result_array();
                        foreach($taskStudents as $taskStudent)
                        {
                            if($taskStudent['paid']==1)
                            {
                                $paid++;
                            }
                            else
                            {
                                $unpaid++;
                            }
                        }
                        $feenotcreated = $student['total_students']-$paid-$unpaid;
                        if($feenotcreated>0)
                        {
                            $feenotcreated = '<span class="alert-danger">'.$feenotcreated.'</span>';
                        }
                        echo ' | <strong>'.$task['type_name'].'</strong> Fee Not Created ('.$feenotcreated.') Paid in College ('.$paid.') & Unpaid ('.$unpaid.') & Add Expense<br />';
                    }
                }
                if($total_students_count>0)
                {
                    echo '<strong>Total Students : '.$total_students_count.'</strong>';
                }
            ?>
        </td>
    </tr>
    <?php
        $i++;
        endforeach;
    ?>
    </tbody>
</table>