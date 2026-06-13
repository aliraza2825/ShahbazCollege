
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
                                Assign Teacher for Chat
                            </div>
                        </div>
                        <div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/mobile_application/insert_teacher">
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

                                </div>
                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-offset-9 col-md-3" style="text-align: right">
                                            <button type="submit" class="btn green">Add</button>
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
                                <i class="fa fa-list"></i> All Assigned Teachers
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
                                        Course
                                    </th>
                                    <th>
                                        Subject
                                    </th>
                                    <th>
                                        Teacher
                                    </th>
                                    <th>
                                        Created At
                                    </th>
                                    <th>
                                        Action
                                    </th>
                                </tr>
                                </thead>
                                <tbody>

                                <?php
                                $i=0;
                                foreach($teachers as $list):
                                ?>
                                <tr class="odd gradeX">
                                    <td class="hidden">
                                        <?php echo $i;?>
                                    </td>
                                    <td>
                                        <?php echo $list['course_name'];?>
                                    </td>
                                    <td>
                                        <?php echo $list['subject_name'];?>
                                    </td>
                                    <td>
                                        <?php echo $list['first_name']." ".$list['last_name'];?>
                                    </td>
                                    <td>
                                        <?php echo date('F d, Y', strtotime($list['created_at']));?>
                                    </td>
                                    <td>
                                        <a href="<?php echo site_url().'/mobile_application/delete_teacher/'.$list['id'];?>" class="btn red"><i class="fa fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php
                                $i++;
                                endforeach;
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