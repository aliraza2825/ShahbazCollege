
<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
    <div class="page-content">


        <!-- BEGIN PAGE HEADER-->
        <!--<h3 class="page-title">
        Edit Profile <small>You can edit your profile here</small>
        </h3>-->

        <!-- END PAGE HEADER-->
        <!-- BEGIN PAGE CONTENT-->
        <?php if(@$this->session->userdata('message')):?>
            <div class="alert alert-success">
                <button class="close" data-close="alert"></button>
                <span>
                    <?php echo $this->session->userdata('message');?> </span>
            </div>
        <?php endif;?>
        <?php if(@$this->session->userdata('error')):?>
            <div class="alert alert-danger">
                <button class="close" data-close="alert"></button>
                <span>
                    <?php echo $this->session->userdata('error');?> </span>
            </div>
        <?php endif;?>
        <div class="row">
            <div class="col-md-12 ">
                <!-- BEGIN SAMPLE FORM PORTLET-->
                <div class="portlet box green ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-file"></i> Student Assignment
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/assignments/donechecking">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="col-md-12">
                                        <?php
                                        $mcqs = explode(',',$assignment[0]['mcqs']);
                                        if(count($mcqs)>0)
                                        {
                                            echo '<h2>MCQs</h2>';
                                            $i=1;
                                            $key=0;
                                            $per_question_marks = $assignment[0]['mcqs_marks']/count($mcqs);
                                            $mcqs_data = json_decode($assignment[0]['mcqs_data']);
                                            foreach($mcqs as $mcq)
                                            {
                                                $mcq = $this->db->get_where('questions',array('question_id'=>$mcq))->result_array();
                                                echo '<h4>'.strip_tags($mcq[0]['question']).'</h4>';
                                                echo '<p>A. '.strip_tags($mcq[0]['option_1']).'</p>';
                                                echo '<p>B. '.strip_tags($mcq[0]['option_2']).'</p>';
                                                echo '<p>C. '.strip_tags($mcq[0]['option_3']).'</p>';
                                                echo '<p>D. '.strip_tags($mcq[0]['option_4']).'</p>';
                                                if(strip_tags($mcq[0]['answer'])==$mcqs_data[$key])
                                                {
                                                    echo '<div class="alert alert-success">Correct Answer : '.strip_tags($mcq[0]['answer']).'<br />Student Answer : '.$mcqs_data[$key].'</div>';
                                                    echo '<input type="hidden" name="obtain_mcqs_marks[]" value="'.$per_question_marks.'" />';
                                                }
                                                else
                                                {
                                                    echo '<div class="alert alert-danger">Correct Answer : '.strip_tags($mcq[0]['answer']).'<br />Student Answer : '.$mcqs_data[$key].'</div>';
                                                    echo '<input type="hidden" name="obtain_mcqs_marks[]" value="0" />';
                                                }
                                                $key++;
                                                $i++;
                                            }
                                        }
                                        ?>

                                        <?php
                                        if($assignment[0]['short_questions']!='')
                                        {
                                            $short_questions = explode(',',$assignment[0]['short_questions']);
                                            if(count($short_questions)>0)
                                            {
                                                echo '<h2>Short Questions</h2>';
                                                $i=1;
                                                $key=0;
                                                $per_question_marks = $assignment[0]['short_questions_marks']/count($short_questions);
                                                $short_questions_data = json_decode($assignment[0]['short_questions_data']);
                                                $obtain_short_questions_marks = json_decode($assignment[0]['obtain_short_questions_marks']);
                                                foreach($short_questions as $short_question)
                                                {
                                                    $short_question = $this->db->get_where('questions',array('question_id'=>$short_question))->result_array();
                                                    echo '<h4>Question '.$i.' : '.strip_tags($short_question[0]['question']).'</h4>';
                                                    echo '<p>'.$short_questions_data[$key].'</p>';
                                                    ?>
                                                    <div class="form-group">
                                                        <div class="col-md-12">
                                                            <input type="number" min="0" max="<?php echo $per_question_marks;?>" class="form-control input-inline input-medium" name="obtain_short_questions_marks[]" placeholder="Enter marks out of <?php echo $per_question_marks;?>" value="<?php echo @$obtain_short_questions_marks[$key]?>" required>
                                                            <span class="help-inline"></span>
                                                        </div>
                                                    </div>
                                                    <?php
                                                    echo '<hr />';
                                                    $key++;
                                                    $i++;
                                                }
                                            }
                                        }
                                        ?>

                                        <?php
                                        if($assignment[0]['practicals']!='')
                                        {
                                            $practicals = explode(',',$assignment[0]['practicals']);
                                            if(count($practicals)>0)
                                            {
                                                echo '<h2>Practicals</h2>';
                                                $i=1;
                                                $key=0;
                                                $per_question_marks = $assignment[0]['practical_marks']/count($practicals);
                                                $practicals_data = json_decode($assignment[0]['practicals_data']);
                                                $obtain_practical_marks = json_decode($assignment[0]['obtain_practical_marks']);
                                                foreach($practicals as $practical)
                                                {
                                                    $practical = $this->db->get_where('practicals',array('practical_id'=>$practical))->result_array();
                                                    echo '<h4>Practical '.$i.' : '.@$practical[0]['practical_name'].'</h4>';
                                                    echo '<p>'.$practicals_data[$key].'</p>';
                                                    ?>
                                                    <div class="form-group">
                                                        <div class="col-md-12">
                                                            <input type="number" min="0" max="<?php echo $per_question_marks;?>" class="form-control input-inline input-medium" name="obtain_practical_marks[]" placeholder="Enter marks out of <?php echo $per_question_marks;?>" value="<?php echo @$obtain_practical_marks[$key]?>" required>
                                                            <span class="help-inline"></span>
                                                        </div>
                                                    </div>
                                                    <?php
                                                    echo '<hr />';
                                                    $key++;
                                                    $i++;
                                                }
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <input type="hidden" name="check_assignment" value="1" />
                                        <input type="hidden" name="student_id" value="<?php echo $this->uri->segment(4);?>" />
                                        <input type="hidden" name="assignment_id" value="<?php echo $this->uri->segment(3);?>" />
                                        <button type="submit" class="btn green">Done Checking</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- END SAMPLE FORM PORTLET-->
            </div>
        </div>
        <!-- END PAGE CONTENT-->
    </div>
</div>
<!-- END CONTENT -->