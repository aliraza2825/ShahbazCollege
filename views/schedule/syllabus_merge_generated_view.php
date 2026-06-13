<?php
$myAccess = checkUserAccess();
?>
<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">

    <div class="page-content">

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

                    <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/schedule">

                        <div class="form-body">

                            <!-- BEGIN SAMPLE FORM PORTLET-->
                            <div class="portlet box green ">

                                <div class="portlet-title">
                                    <div class="caption">
                                        <i class="fa fa-user"></i> Syllabus View
                                        <?php  ?>
                                    </div>

                                </div>



                                <div class="portlet-body table-responsive">


                                    <table class="table table-bordered table-hover" >
                                        <thead>
                                        <tr>
                                            <th >
                                                Sr
                                            </th>

                                            <th>
                                                Day
                                            </th>

                                            <th>
                                                Date
                                            </th>

                                            <th>
                                                Topics
                                            </th>


                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $i=0;
                                        $t=1;

                                        foreach($lectures as $key=>$lecs):

                                            ?>


                                            <tr class="odd gradeX">
                                                <td >
                                                    <?php echo $key+1;?>
                                                </td>

                                                <td><?php  echo $lecs ['day']  ?></td>
                                                <td><?php  echo $lecs ['date']  ?></td>
                                                <td>
                                                    <?php

                                                    if($lecs ['is_quiz'] == '1')
                                                    {
                                                        if ($lecs ['is_half'] == '1')
                                                        {
                                                            echo "<strong>Half Book Test</strong><br /><br />";
                                                        }else {
                                                            echo "<strong>Quiz</strong><br /><br />";
                                                        }
                                                            $t=1;
                                                    }
                                                    else
                                                    {
                                                        echo "<strong>Topic</strong><br /><br />";
                                                    }

                                                    $topicss =$this->db->where('lecture_id = "'.$lecs ['lecture_id'].'" and date like "'.$lecs ['date'].'" and topic_ids != ""')->get('session_syllabus')->result_array();


                                                    foreach ($topicss as $topic)
                                                    {
                                                        $topics = $topic['topic_ids'];
                                                        $ext_topics = explode(',',$topics);
                                                        $topic_text = "";
                                                        $lecture_topics = $this->db->where_in('topic_id', explode(',',$topics))->get('topics')->result_array();
                                                        $topic_text = "";
                                                        $lectbefore = array();
                                                        $lectafter  = array();

                                                        foreach ($lecture_topics as $key => $teps)
                                                        {
                                                            $lectbefore = array();
                                                            $lectafter  = array();
                                                            $topic_text = "";
                                                            if($lecs ['is_quiz'] == '1')
                                                            {
                                                                $topic_text = "";
                                                                $lectbefore = array();
                                                                $lectafter  = array();
                                                            }
                                                            else
                                                            {
                                                                $query = 'SELECT * FROM session_syllabus where id < '.$topic ['id'].' and  subject_id = "'.$topic ['subject_id'].'" and lecture_id = "'.$topic ['lecture_id'].'" and syllabus_id= "'.$topic ['syllabus_id'].'" and is_quiz = "0" and is_half ="0" and FIND_IN_SET("'.$ext_topics[0].'",topic_ids)';
                                                                $query2 = 'SELECT * FROM session_syllabus where id > '.$topic ['id'].' and  subject_id = "'.$topic ['subject_id'].'" and lecture_id = "'.$topic ['lecture_id'].'" and syllabus_id= "'.$topic ['syllabus_id'].'" and is_quiz = "0" and is_half ="0" and FIND_IN_SET("'.$ext_topics[0].'",topic_ids)';
                                                                $lectbefore = $this->db->query($query)->result_array();
                                                                $lectafter  = $this->db->query($query2)->result_array();

                                                                $topic_text = "  ".(count($lectbefore)+1).' of '.((count($lectbefore)+1)+(count($lectafter)));
                                                            }
                                                                echo '<strong>'.$this->db->where('course_subject_id', $topic['subject_id'])->get('course_subjects')->row()->subject_name.'</strong> - '.$this->db->where('unique_syllabus_id', $topic['syllabus_id'])->get('syllabus')->row()->syllabus_name.' - <strong>'.$teps['topic_name'] .'</strong>'.$topic_text.' <br />';
                                                        }
                                                    }
                                                    $topicss =$this->db->where('lecture_id = "'.$lecs ['lecture_id'].'" and date like "'.$lecs ['date'].'" and practical_ids != ""')->get('session_syllabus')->result_array();
                                                    if(count($topicss) > 0)
                                                        echo "<strong>Practicals</strong><br />";
                                                    foreach ($topicss as $topic)
                                                    {
                                                        $topics = explode(",", $topic ['practical_ids']);
                                                        $lecture_topics = $this->db->where_in('practical_id ', $topics)->get('practicals')->result_array();

                                                        foreach ($lecture_topics as $key => $teps) {

                                                            echo '<strong>'.$this->db->where('course_subject_id', $topic['subject_id'])->get('course_subjects')->row()->subject_name.'</strong> - '.$this->db->where('unique_syllabus_id', $topic['syllabus_id'])->get('syllabus')->row()->syllabus_name.' - <strong>'.$teps['practical_name'].'</strong><br />';

                                                        }
                                                    }

                                                    $i++;
                                                    $t++;
                                                    ?>
                                                </td>


                                            </tr>
                                        <?php

                                        endforeach;

                                        ?>

                                        <tr class="odd gradeX">
                                            <td >
                                                <?php echo $key+1;?>
                                            </td>

                                            <td><?php  echo $lectures[0] ['day']  ?></td>
                                            <td><?php  echo $lectures[0] ['date']  ?></td>
                                            <td>
                                                <?php
                                                echo "<strong>Full Book Test</strong><br /><br />";
                                                ?>
                                            </td>


                                        </tr>
                                        </tbody>
                                    </table>
                                </div>


                            </div>


                    </form>

                </div>

            </div>


    </div>

</div>
<!-- END CONTENT -->
