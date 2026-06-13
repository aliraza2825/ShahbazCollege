<?php ?>
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Add Class <small>add your desire class</small>
			</h3>-->
			<!-- END PAGE HEADER-->
			<!-- BEGIN PAGE CONTENT-->
            <?php
                $count = 0;

            if(@$this->session->userdata('message')):?>
                <div class="alert alert-success">
                    <button class="close" data-close="alert"></button>
                    <span>
                    <?php echo $this->session->userdata('message');?> </span>
                </div>
            <?php endif;?>
            <div class="row">
                <div class="col-md-12 ">
                    <!-- BEGIN SAMPLE FORM PORTLET-->
                    <div class="portlet box green ">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-mobile" aria-hidden="true"></i>
                                Teachers Added Questions
                            </div>
                        </div>
                        <div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/reports/teacher_questions">
                                <div class="form-body row">

                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Course <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="course_id" class="form-control input-inline input-medium course_id" required>
                                                <option value="">Select Course</option>
                                                <?php
                                                foreach ($courses as $course):
                                                    ?>
                                                    <option value="<?php echo $course['course_id']?>"><?php echo $course['course_name']?></option>
                                                <?php
                                                endforeach;
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Subject <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="subject_id" class="form-control input-inline input-medium subject_id" required>
                                                <option value="">Select Subject</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Teacher <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="teacher_id" class="form-control input-inline input-medium teacher_id" required>
                                                <option value="">Select Teacher</option>
                                                <?php
                                                foreach ($users as $data):
                                                    ?>
                                                    <option value="<?php echo $data['user_id']?>"><?php echo $data['first_name']." ".$data['last_name']." ( ".$data['designation_name']." )"?></option>
                                                <?php
                                                endforeach;
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">From Date <span class="required">*</span></label>
                                                    <div class="col-md-3">
                                                        <div class="input-group input-medium date date-picker" data-date="<?php echo $from_date;?>" data-date-format="yyyy-mm-dd" <?php if(@$myAccess[0]['expense_no_of_days']!=1 && $this->session->userdata('role') != 'Admin'): ?> data-date-start-date="-45d"  data-date-end-date="0d" <?php endif;?> data-date-viewmode="years">
                                                            <input type="text" name="from_date" class="form-control" value="<?php echo $from_date;?>" readonly>
                                                            <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">To Date <span class="required">*</span></label>
                                                    <div class="col-md-3">
                                                        <div class="input-group input-medium date date-picker" data-date="<?php echo $to_date;?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                            <input type="text" name="to_date" class="form-control" value="<?php echo $to_date;?>" readonly>
                                                            <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-offset-9 col-md-3" style="text-align: right">
                                            <button type="submit" class="btn green">Submit</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>
                    <!-- END SAMPLE FORM PORTLET-->
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <!-- BEGIN EXAMPLE TABLE PORTLET-->
                    <div class="portlet box grey-cascade">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-list"></i> All Teachers Questions
                            </div>
                        </div>
                        <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover" id="sample_2">
                                <thead>
                                <tr>
                                    <th class="hidden">
                                        Sr.No
                                    </th>
                                    <th>
                                        Date
                                    </th>
                                    <th>
                                        Course
                                    </th>
                                    <th>
                                        Subject
                                    </th>
                                    <th>
                                        Teacher
                                    </th>
                                    <th>
                                        Total Mcqs
                                    </th>
                                    <th>
                                        Total short
                                    </th>
                                    <th>
                                        Total Long
                                    </th>
                                    <th>
                                        Total Word Meaning
                                    </th>
                                    <th>
                                        Total Videos
                                    </th>
                                    <th>
                                        Action
                                    </th>
                                </tr>
                                </thead>
                                <tbody>

                                <?php
                                if ($my_course != ""):
                                $i=0;
                                foreach($dates as $date):
                                ?>
                                <tr class="odd gradeX">
                                    <td class="hidden">
                                        <?php echo $i;?>
                                    </td>
                                    <td>
                                        <?php echo $date;?>
                                    </td>
                                    <td>
                                        <?php echo $my_course->course_name;?>
                                    </td>
                                    <td>
                                        <?php echo $my_subject->subject_name;?>
                                    </td>
                                    <td>
                                        <?php echo $my_teacher->first_name." ".$my_teacher->last_name;?>
                                    </td>
                                    <td>
                                        <?php $this->db->select('*');
                                        $this->db->from('questions');
                                        $this->db->where_in("topic_id",$my_topics);
                                        $this->db->where("option_1!='' and created_at >='$date 00:00:00' and created_at <='$date 23:59:59' and add_by = '$my_teacher->first_name $my_teacher->last_name'");
                                        $questions = $this->db->get()->result_array();
                                        echo count($questions);
                                         ?>
                                    </td>
                                    <td>
                                        <?php
                                        $shortquestions = $this->db->where_in("topic_id",$my_topics)->get_where('questions', "type = 'short-question' and created_at >='$date 00:00:00' and created_at <='$date 23:59:59' and add_by = '$my_teacher->first_name $my_teacher->last_name'")->result_array();
                                        echo count($shortquestions);
                                         ?>
                                    </td>
                                    <td>
                                        <?php
                                        $longquestions = $this->db->where_in("topic_id",$my_topics)->get_where('questions', "type = 'long-question' and created_at >='$date 00:00:00' and created_at <='$date 23:59:59' and add_by = '$my_teacher->first_name $my_teacher->last_name'")->result_array();
                                        echo count($longquestions);
                                         ?>
                                    </td>
                                    <td>
                                        <?php
                                        $wordmeanings = $this->db->where_in("topic_id",$my_topics)->get_where('questions', "type = 'word-meaning' and created_at >='$date 00:00:00' and created_at <='$date 23:59:59' and add_by = '$my_teacher->first_name $my_teacher->last_name'")->result_array();
                                        echo count($wordmeanings);
                                         ?>
                                    </td>
                                    <td>
                                        <?php
                                        $videos = $this->db->where_in("topic_id",$my_topics)->get_where('question_videos', "created_at >='$date 00:00:00' and created_at <='$date 23:59:59' and created_by = '$my_teacher->first_name $my_teacher->last_name'")->result_array();
                                        echo count($videos);
                                        ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo site_url().'/reports/show_question_data/'.$my_subject->course_subject_id."/".$my_teacher->user_id;?>" class="btn blue"><i class="fa fa-eye"></i></a>
                                    </td>
                                </tr>
                                <?php
                                $i++;
                                endforeach;
                                endif;
                                ?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- END EXAMPLE TABLE PORTLET-->
                </div>
            </div>
    </div>
</div>
<!-- END CONTENT -->
    <script>
        document.addEventListener( "DOMContentLoaded", function(){
            jQuery('.course_id').live('change',function(){
                var course_id = jQuery('.course_id').val();
                jQuery.ajax({
                    type: "post",
                    async: false,
                    url: '<?php echo site_url()?>/chapters/getSubjects',
                    data: {
                        course_id : course_id
                    },
                    success: function(data) {
                        jQuery('.subject_id').html(data).trigger('change');
                    }
                });
            });
        }, false );

    </script>