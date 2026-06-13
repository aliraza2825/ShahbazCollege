
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
								<i class="fa fa-edit"></i> Edit Course (<?php echo $course[0]['course_name'];?>)
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/mobile_app/update_course/<?php echo $course[0]['course_id'];?>" enctype="multipart/form-data">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Course Content <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <div class="col-md-12">
                                                <textarea class="wysihtml5 form-control" rows="15" name="content"><?php echo $course[0]['content'];?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">For Mobile App <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="checkbox" class="form-control input-inline input-medium" name="mobile_status" value="1" <?php if ($course[0]['mobile_status'] == 1) echo "checked";?>/>
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Update Course</button>
											<button onclick="location.href = '<?php echo site_url();?>/mobile_app/manage_courses'" type="button" class="btn default">Cancel</button>
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