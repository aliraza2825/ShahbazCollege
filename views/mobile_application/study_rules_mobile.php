
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
                                Mobile Syllabus Rules
                            </div>
                        </div>
                        <div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/mobile_application/insert_study_rule"  enctype="multipart/form-data">
                                <div class="form-body row">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Teacher <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="course_id" class="form-control input-inline input-large select2" id="select2_sample2" required>
                                                <option value="">Select Course</option>
                                                <?php
                                                foreach ($courses as $data):
                                                    ?>
                                                    <option value="<?php echo $data['course_id']?>"><?php echo $data['course_name'];?></option>
                                                <?php
                                                endforeach;
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Months <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input name="months" style="width: 80%;" class="form-control" >
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Test After Lectures<span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input name="test_after_lectures" style="width: 80%;" class="form-control" >
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
                                <i class="fa fa-list"></i> Study Rules Course Wise
                            </div>
                        </div>
                        <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover" id="sample_2">
                                <thead>
                                <tr>
                                    <th>
                                        Sr.No
                                    </th>
                                    <th>
                                        Course
                                    </th>
                                    <th>
                                        Months
                                    </th>
                                    <th>
                                        Test After Lectures
                                    </th>
                                    <th>
                                        Status
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
                                foreach($rules as $list):
                                ?>
                                <tr class="odd gradeX">
                                    <td>
                                        <?php echo $i+1;?>
                                    </td>
                                    <td>
                                        <?php echo $list['course_name'];?>
                                    </td>
                                    <td>
                                        <?php echo $list['months'];?>
                                    </td>
                                    <td>
                                        <?php echo $list['test_after_lectures'];?>
                                    </td>
                                    <td>
                                        <?php echo $list['status'];?>
                                    </td>
                                    <td>
                                        <?php echo $list['created_at'];?>
                                    </td>
                                    <td>
                                        <?php if ($list['status'] == 'active'): ?>
                                            <a href="<?php echo site_url().'/mobile_application/change_study_status/'.$list['id'];?>/inactive" class="btn red">Deactivate</a>
                                        <?php else: ?>
                                            <a href="<?php echo site_url().'/mobile_application/change_study_status/'.$list['id'];?>/active" class="btn green">Activate</a>
                                        <?php endif; ?>
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