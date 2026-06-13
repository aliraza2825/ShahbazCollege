<?php
$myAccess = checkUserAccess();
function remove_empty($array) {
    return array_filter($array, '_remove_empty_internal');
}

function _remove_empty_internal($value) {
    return !empty($value) || $value === 0;
}
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

                <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/schedule/insert_session_syllabus">

                    <div class="form-body">

                        <!-- BEGIN SAMPLE FORM PORTLET-->
                        <div class="portlet box green ">

                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-user"></i> Syllabus View
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
                                            Syllabus Name
                                        </th>
                                        <th>
                                            Lecture
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
                                    $syllabus_myid = "";

                                    foreach($lectures as $key=>$lecs):
                                        if ($syllabus_myid == "")
                                        {
                                            $syllabus_myid = $lecs['syllabus_id'];
                                        }

                                        if ($syllabus_myid != $lecs['syllabus_id'] || count($lectures) == $key+1):

                                            ?>
                                            <tr class="odd gradeX">
                                            <td >
                                        <?php echo $key+1;

                                        end($lectures);         // move the internal pointer to the end of the array
                                        $key = key($lectures);
                                        ?>
                                        </td>
                                        <td>
                                            <?php  $syllabus_name =$this->db->where('unique_syllabus_id ',$syllabus_myid)->get('syllabus')->row();
                                            $syllabus_myid = $lecs['syllabus_id'];
                                            echo $syllabus_name->syllabus_name; ?>
                                        </td>
                                        <td><?php  echo $lectures[0]['subject_name']  ?></td>
                                        <td><?php  echo $lectures[0]['day']  ?></td>
                                        <td><?php  echo date('Y-m-d', strtotime("+1 day", strtotime($lectures[count($lectures) - 1] ['date'])))  ?></td>
                                        <td>
                                            <?php
                                            echo "<strong>Full Book Test</strong><br /><br />";
                                            ?>
                                        </td>


                                        </tr>

                                        <?php
                                        else:?>
                                        <tr class="odd gradeX">
                                            <td >
                                                <?php echo $key+1;?>
                                            </td>
                                            <td><?php  $syllabus_name =$this->db->where('unique_syllabus_id ',$lecs ['syllabus_id'])->get('syllabus')->row();
                                                echo $syllabus_name->syllabus_name; ?></td>
                                            <td><?php  echo $lecs ['subject_name']  ?></td>
                                            <td><?php  echo $lecs ['day']  ?></td>
                                            <td><?php  echo $lecs ['date']  ?></td>
                                            <td>
                                                <?php

                                                $topics=explode(",",$lecs ['topic_ids']);

                                                $topic_text = "";

                                                $topics = remove_empty($topics);

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
                                                    $lectbefore =$this->db->where('id <'.$lecs ['id'].' and subject_id = "'.$lecs ['subject_id'].'" and lecture_id = "'.$lecs ['lecture_id'].'" and is_quiz ="0" and is_half ="0" and (topic_ids = "'.@$topics[0].'" or topic_ids like "%'.@$topics[0].',%"  or topic_ids like "%,'.@$topics[0].'%"  or topic_ids like "%,'.@$topics[0].',%")')->get('session_syllabus')->result_array();
                                                    $lectafter = $this->db->where('id >'.$lecs ['id'].' and subject_id = "'.$lecs ['subject_id'].'" and lecture_id = "'.$lecs ['lecture_id'].'" and is_quiz ="0" and is_half ="0" and (topic_ids = "'.@$topics[0].'" or topic_ids like "%'.@$topics[0].',%"  or topic_ids like "%,'.@$topics[0].'%"  or topic_ids like "%,'.@$topics[0].',%")')->get('session_syllabus')->result_array();

                                                    $topic_text = "  ".(count($lectbefore)+1).' of '.((count($lectbefore)+1)+(count($lectafter)));

                                                }
                                                
                                                
                                                if(count($topics)>0)
                                                {
                                                    $lecture_topics =$this->db->where_in('topic_id',$topics)->get('topics')->result_array();
                                                    echo "<strong>Lecture</strong><br />";
                                                    foreach ($lecture_topics as $key=>$teps)
                                                    {
                                                        echo $teps['topic_name'].$topic_text.'<br />';
                                                    }
                                                }

                                                $topics=explode(",",$lecs ['practical_ids']);
                                                $lecture_topics =$this->db->where_in('practical_id ',$topics)->get('practicals')->result_array();
                                                
                                                if(count($lecture_topics)>0)
                                                {
                                                    echo "<strong>Practicals</strong><br />";
                                                    foreach ($lecture_topics as $key=>$teps)
                                                    {
                                                        echo $teps['practical_name'].'<br />';
                                                    }
                                                }
                                                $i++;
                                                $t++;

                                                ?>

                                            </td>
                                        </tr>


                                    <?php
                                    endif;
                                    endforeach;

                                    ?>

                                    </tbody>
                                </table>
                            </div>


                        </div>


                </form>

            </div>


        </div>

    </div>
    <!-- END CONTENT -->
