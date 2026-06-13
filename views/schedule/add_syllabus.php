
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Add Class <small>add your desire class</small>
			</h3>-->
			<!-- END PAGE HEADER-->
            <!-- BEGIN DASHBOARD STATS -->
			
			<!-- END DASHBOARD STATS -->
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
								<i class="fa fa-plus"></i> Add Syllabus
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/schedule/insert_syllabus">
								<div class="form-body">
                                   
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Courses <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control courses" name="course_id" id="course_id" required>
                                                <option value="">Select Course</option>
												<?php 
                                                    foreach($courses as $course):
                                                ?>
                                                <option value="<?php echo $course['course_id'];?>">
                                                    <?php echo $course['course_name'];?>
                                                </option>
                                                <?php
                                                    endforeach;
                                                ?>
                                            </select>
                                            <!--<span class="help-inline"></span>-->
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Subjects <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control course_subjects" name="subject_id" id="subject_id" required>
                                                
                                            </select>
                                        </div>
                                    </div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Study Type <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control study_type" name="studytype" required>
                                                
                                            </select>
                                            <!--<span class="help-inline"></span>-->
                                        </div>
                                    </div>
									
									<div class="form-group">
                                        <label class="col-md-3 control-label">Syllabus Type <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control" name="revision" required>
                                                <option value="0">Regular Study</option>
                                                <option value="1">Revision</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Syllabus Name <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <input type="text" class="form-control" name="syllabus_name" id="syllabus_name" required>
                                        </div>
                                    </div>

									<div class="form-group">
									<div class="col-md-12">
									<label class="col-md-3 control-label">Please Use ( - ) as Lectures Separartor's and lacture no. like <br />1<br />2-3-4-5-6-7<br />8<br />9</label>
                                    </div>
									</div>
									<div class="form-group">
                                        <div class="col-md-12 topics">
                                        
                                        </div>
										<div class="col-md-12 practicals">
                                        
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                        	<input type="hidden" name="status" value="1" />
                                            <input type="hidden" name="add_by" value="<?php echo $this->session->userdata('name');?>" />
                                            <input type="hidden" name="last_edit" value="<?php echo $this->session->userdata('name');?>" />
											<button type="submit" class="btn green">Add Syllabus</button>
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
	
<script>
    window.addEventListener('DOMContentLoaded',function () {
        $('.courses').change(function(){
            var course_id = $(this).val();
            jQuery.ajax({
                type: "post",
                async: false,
                url: '<?php echo site_url()?>/timetable/getCourseStudyTypes',
                data: {
                    course_id : course_id
                },
                success: function(data) {
                    $('.study_type').html(data);
                }
            });
        });
    });
</script>