<?php
if (!function_exists('getOrdinal')) {
    function getOrdinal($number)
    {
        $suffixes = ['th','st','nd','rd','th','th','th','th','th','th'];

        if (($number % 100) >= 11 && ($number % 100) <= 13) {
            return $number . 'th';
        }

        return $number . $suffixes[$number % 10];
    }
}

if (!empty($roll_number_and_result['result_remarks'])) {

    $remarks = strtolower($roll_number_and_result['result_remarks']);
    $current_exam = $exam_no;

    if ($remarks == 'pass' || $remarks == 'pass*') {

        echo "Pass in Exam No ".$current_exam.' of '.getOrdinal($current_exam_sequence['class']).' '.$is_year.'<br><br>';

        if ($course['course_duration_year'] > $current_exam_sequence['class']) {

            $this->db->where('course_id', $course_id);
            $this->db->where('status', 'Active');
            $this->db->where('class', ($current_exam_sequence['class'] + 1));

            $type = $result_rules['annual_students_can_appear_in'];
            if (!empty($type) && $type != 'both') {
                $this->db->where('first_year_type', $type);
            }

            $next_exam = $this->db->get('exam_sequence')->row_array();

            if ($next_exam) {

                $tasks = $this->db
                    ->join('councils','councils.council_id = council_sequence.council_id','inner')
                    ->where('course_id', $next_exam['course_id'])
                    ->where('action_type', 'fee')
                    ->where_in('recurring', ['Each Exam','Every Semester','After Chances'])
                    ->order_by("STR_TO_DATE(last_date,'%d/%m')", "ASC", false)
                    ->get('council_sequence')
                    ->result_array();

                foreach ($tasks as $task) {

                    echo '<div style="border:1px solid #ddd; padding:10px; margin-bottom:10px; background:#f9f9f9;">';

                    $should_check_fee = true;
                    $session = 0;

                    if ($task['recurring'] == 'After Chances') {
                        $session_rows = $this->db
                            ->get_where('punjab_council_roll_number', ['cnic' => $student['cnic']])
                            ->result_array();

                        $session = count($session_rows);

                        if ($session < $task['no_of_chances']) {
                            $should_check_fee = false;
                        }
                    }

                    if ($should_check_fee) {

                        if ($task['recurring'] == 'After Chances') {
                            echo "<b>".$task['type_name']."</b> | <br>This Fee for ".$task['type_name']."<br>";
                        } else {
                            echo "<b>".$task['type_name']."</b> | <br>This Fee for ".
                                getOrdinal($current_exam_sequence['class'] + 1)." ".$is_year." Exam No ".
                                $next_exam['first_year'].' '.$next_exam['first_year_type']."<br>";
                        }

                        $today = date('Y-m-d');

                        $fee = $this->db
                            ->where('sequence_fee_id', $task['council_sequence_id'])
                            ->where('exam_sequence_id', $next_exam['id'])
                            ->where('to_date >=', $today)
                            ->order_by('from_date','ASC')
                            ->get('council_sequence_fee_rules')
                            ->row_array();

                        if ($fee) {

                            echo '<b>'.$fee['exam_fee'].' <br> ( '.$fee['from_date'].' - '.$fee['to_date'].' ) </b>';

                            $already = $this->db->get_where('payments', [
                                'student_id' => $student['student_id'],
                                'exam_sequence_id' => $next_exam['id'],
                                'exam_class' => $next_exam['class'],
                                'council_sequence_id' => $task['council_sequence_id']
                            ])->row_array();

                            if ($already) {
                                echo ' <span class="btn btn-success btn-xs" style="margin-top:5px;">Fee Created</span>';

                                if ($already['paid'] == 1) {
                                    echo '<br> Paid on : '.$already['updated_at'];
                                }
                            } else {
                                $fee_item = [
                                    'next_exam_sequence_id' => $next_exam['id'],
                                    'council_sequence_id' => $task['council_sequence_id'],
                                    'class' => $next_exam['class'],
                                    'dead_line' => $fee['from_date'],
                                    'course_type' => $is_year,
                                    'exam_no' => $next_exam['first_year'],
                                    'amount' => $fee['exam_fee'],
                                    'type' => 'College fee'
                                ];

                                if ($task['recurring'] == 'After Chances') {
                                    $fee_item['type'] = 'Extra fee';
                                    $fee_item['comment'] = 'This Fee For '.$task['type_name'];
                                }

                                $student_fee_items[] = $fee_item;
                            }

                        } else {

                            echo '<br><div class="alert alert-danger" style="margin:0;padding:5px;">
                                Fee is Not Created. Please create Fee for '.$task['type_name'].'.
                            </div>';

                            $student_has_error = true;
                            $bulk_fee_has_errors = true;
                        }
                    }

                    echo '</div>';
                }

            } else {

                echo '<br><div class="alert alert-danger" style="margin:0;padding:5px;">
                    Next Exam is Not Created. Please create Next Exam Sequence.
                </div>';

                $student_has_error = true;
                $bulk_fee_has_errors = true;
            }

        } else {

            echo "Your Degree is Clear.";

            $tasks = $this->db
                ->join('councils','councils.council_id = council_sequence.council_id','inner')
                ->where('course_id', $current_exam_sequence['course_id'])
                ->where('action_type', 'fee')
                ->where('recurring', 'End of Degree')
                ->order_by("STR_TO_DATE(last_date,'%d/%m')", "ASC", false)
                ->get('council_sequence')
                ->result_array();

            foreach ($tasks as $task) {

                echo '<div style="border:1px solid #ddd; padding:10px; margin-bottom:10px; background:#f9f9f9;">';
                echo "<b>".$task['type_name']."</b> | <br>This Fee for ".$task['type_name']." <br>";

                $today = date('Y-m-d');

                $fee = $this->db
                    ->where('sequence_fee_id', $task['council_sequence_id'])
                    ->where('exam_sequence_id', $current_exam_sequence['id'])
                    ->where('to_date >=', $today)
                    ->order_by('from_date','ASC')
                    ->get('council_sequence_fee_rules')
                    ->row_array();

                if ($fee) {

                    echo '<b>'.$fee['exam_fee'].' <br> ( '.$fee['from_date'].' - '.$fee['to_date'].' ) </b>';

                    $already = $this->db->get_where('payments', [
                        'student_id' => $student['student_id'],
                        'exam_sequence_id' => $current_exam_sequence['id'],
                        'exam_class' => $current_exam_sequence['class'],
                        'council_sequence_id' => $task['council_sequence_id']
                    ])->row_array();

                    if ($already) {
                        echo ' <span class="btn btn-success btn-xs" style="margin-top:5px;">Fee Created</span>';
                    } else {
                        $student_fee_items[] = [
                            'next_exam_sequence_id' => $current_exam_sequence['id'],
                            'council_sequence_id' => $task['council_sequence_id'],
                            'class' => $current_exam_sequence['class'],
                            'dead_line' => $fee['from_date'],
                            'course_type' => $is_year,
                            'exam_no' => $current_exam_sequence['first_year'],
                            'amount' => $fee['exam_fee'],
                            'type' => 'Extra fee',
                            'comment' => 'This Fee For '.$task['type_name']
                        ];
                    }

                } else {

                    echo '<br><div class="alert alert-danger" style="margin:0;padding:5px;">
                        Fee is Not Created. Please create Fee for '.$task['type_name'].'.
                    </div>';

                    $student_has_error = true;
                    $bulk_fee_has_errors = true;
                }

                echo '</div>';
            }
        }

    } elseif (stripos($remarks, 'fail') !== false || stripos($remarks, 'absent') !== false) {

        echo "Fail in Exam No ".$current_exam.' of '.getOrdinal($current_exam_sequence['class']).' '.$is_year.'<br><br>';

        $this->db->where('course_id', $course_id);
        $this->db->where('first_year >', $current_exam);
        $this->db->where('class', $current_exam_sequence['class']);

        $type = $result_rules['supplementary_students_can_appear_in'];
        if (!empty($type) && $type != 'both') {
            $this->db->where('first_year_type', $type);
        }

        $this->db->where('status', 'Active');
        $next_exam = $this->db->get('exam_sequence')->row_array();

        if ($next_exam) {

            $tasks = $this->db
                ->join('councils','councils.council_id = council_sequence.council_id','inner')
                ->where('course_id', $next_exam['course_id'])
                ->where('action_type', 'fee')
                ->where_in('recurring', ['Each Exam','After Chances'])
                ->order_by("STR_TO_DATE(last_date,'%d/%m')", "ASC", false)
                ->get('council_sequence')
                ->result_array();

            foreach ($tasks as $task) {

                echo '<div style="border:1px solid #ddd; padding:10px; margin-bottom:10px; background:#f9f9f9;">';

                $should_check_fee = true;
                $session = 0;

                if ($task['recurring'] == 'After Chances') {

                    $session_rows = $this->db
                        ->get_where('punjab_council_roll_number', ['cnic' => $student['cnic']])
                        ->result_array();

                    $session = count($session_rows);

                    if ($session < $task['no_of_chances']) {
                        $should_check_fee = false;
                    }
                }

                if ($should_check_fee) {

                    $today = date('Y-m-d');

                    $fee = $this->db
                        ->where('sequence_fee_id', $task['council_sequence_id'])
                        ->where('exam_sequence_id', $next_exam['id'])
                        ->where('to_date >=', $today)
                        ->order_by('from_date','ASC')
                        ->get('council_sequence_fee_rules')
                        ->row_array();

                    if ($fee) {

                        if ($task['recurring'] == 'After Chances') {
                            echo "<b>".$task['type_name']."</b> | <br>This Fee for ".$task['type_name']."<br>";
                        } else {
                            echo "<b>".$task['type_name']."</b> | <br>This Fee for ".
                                getOrdinal($current_exam_sequence['class'])." ".$is_year." Exam No ".
                                $next_exam['first_year'].' '.$next_exam['first_year_type']."<br>";
                        }

                        echo '<b>'.$fee['exam_fee'].' <br> ( '.$fee['from_date'].' - '.$fee['to_date'].' ) </b>';

                        $already = $this->db->get_where('payments', [
                            'student_id' => $student['student_id'],
                            'exam_sequence_id' => $next_exam['id'],
                            'exam_class' => $next_exam['class'],
                            'council_sequence_id' => $task['council_sequence_id']
                        ])->row_array();

                        if ($already) {
                            echo ' <span class="btn btn-success btn-xs" style="margin-top:5px;">Fee Created</span>';
                        } else {
                            if ($task['recurring'] == 'After Chances') {
                                $student_fee_items[] = [
                                    'next_exam_sequence_id' => $next_exam['id'],
                                    'council_sequence_id' => $task['council_sequence_id'],
                                    'class' => $next_exam['class'],
                                    'dead_line' => $fee['from_date'],
                                    'course_type' => $is_year,
                                    'exam_no' => $next_exam['first_year'],
                                    'amount' => $fee['exam_fee'],
                                    'type' => 'Extra fee',
                                    'comment' => 'This Fee For '.$task['type_name']
                                ];
                            } else {
                                $student_fee_items[] = [
                                    'next_exam_sequence_id' => $next_exam['id'],
                                    'council_sequence_id' => $task['council_sequence_id'],
                                    'class' => $next_exam['class'],
                                    'dead_line' => $fee['from_date'],
                                    'course_type' => $is_year,
                                    'exam_no' => $next_exam['first_year'],
                                    'amount' => $fee['exam_fee'],
                                    'type' => 'College fee'
                                ];
                            }
                        }

                    } else {

                        echo "<b>".$task['type_name']."</b> | <br>This Fee for ".
                            getOrdinal($current_exam_sequence['class'])." ".$is_year." Exam No ".
                            $next_exam['first_year'].' '.$next_exam['first_year_type'];

                        echo '<br><div class="alert alert-danger" style="margin:0;padding:5px;">
                            Fee is Not Created. Please create Fee for '.$task['type_name'].'.
                        </div>';

                        $student_has_error = true;
                        $bulk_fee_has_errors = true;
                    }

                    echo '</div>';
                }
            }

        } else {

            $student_has_error = true;
            $bulk_fee_has_errors = true;

            echo '<br><div class="alert alert-danger" style="margin:0;padding:5px;">
                Next Exam is Not Created. Please create Next Exam Sequence.
            </div>';
        }

        echo '<br>';

        if ($result_rules['promote_on_supplementary'] == 1) {

            $this->db->where('course_id', $course_id);
            $this->db->where('first_year >', $current_exam);
            $this->db->where('class', ($current_exam_sequence['class'] + 1));

            $type = $result_rules['annual_students_can_appear_in'];
            if (!empty($type) && $type != 'both') {
                $this->db->where('first_year_type', $type);
            }

            $next_exam = $this->db->get('exam_sequence')->row_array();

            if ($next_exam) {

                $tasks = $this->db
                    ->join('councils','councils.council_id = council_sequence.council_id','inner')
                    ->where('course_id', $next_exam['course_id'])
                    ->where('action_type', 'fee')
                    ->where_in('recurring', ['Each Exam','Every Semester'])
                    ->order_by("STR_TO_DATE(last_date,'%d/%m')", "ASC", false)
                    ->get('council_sequence')
                    ->result_array();

                foreach ($tasks as $task) {

                    echo '<div style="border:1px solid #ddd; padding:10px; margin-bottom:10px; background:#f9f9f9;">';

                    echo "<b>".$task['type_name']."</b> | <br>This Fee for ".
                        getOrdinal($current_exam_sequence['class'] + 1)." ".$is_year." Exam No ".
                        $next_exam['first_year'].' '.$next_exam['first_year_type']."<br>";

                    $today = date('Y-m-d');

                    $fee = $this->db
                        ->where('sequence_fee_id', $task['council_sequence_id'])
                        ->where('exam_sequence_id', $next_exam['id'])
                        ->where('to_date >=', $today)
                        ->order_by('from_date','ASC')
                        ->get('council_sequence_fee_rules')
                        ->row_array();

                    if ($fee) {

                        echo '<b>'.$fee['exam_fee'].' <br> ( '.$fee['from_date'].' - '.$fee['to_date'].' ) </b>';

                        $already = $this->db->get_where('payments', [
                            'student_id' => $student['student_id'],
                            'exam_sequence_id' => $next_exam['id'],
                            'exam_class' => $next_exam['class'],
                            'council_sequence_id' => $task['council_sequence_id']
                        ])->row_array();

                        if ($already) {
                            echo ' <span class="btn btn-success btn-xs" style="margin-top:5px;">Fee Created</span>';
                        } else {
                            $student_fee_items[] = [
                                'next_exam_sequence_id' => $next_exam['id'],
                                'council_sequence_id' => $task['council_sequence_id'],
                                'class' => $next_exam['class'],
                                'dead_line' => $fee['from_date'],
                                'course_type' => $is_year,
                                'exam_no' => $next_exam['first_year'],
                                'amount' => $fee['exam_fee'],
                                'type' => 'College fee'
                            ];
                        }

                    } else {

                        echo '<br><div class="alert alert-danger" style="margin:0;padding:5px;">
                            Fee is Not Created. Please create Fee for '.$task['type_name'].'.
                        </div>';

                        $student_has_error = true;
                        $bulk_fee_has_errors = true;
                    }

                    echo '</div>';
                }

            } else {

                echo '<br><div class="alert alert-danger" style="margin:0;padding:5px;">
                    Next Exam is Not Created. Please create Next Exam Sequence.
                </div>';

                $student_has_error = true;
                $bulk_fee_has_errors = true;
            }
        }

    } else {
        echo "-";
    }

} else {
    echo "-";
}
?>