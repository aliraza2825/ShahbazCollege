
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Add Class <small>add your desire class</small>
			</h3>-->
			<!-- END PAGE HEADER-->
            <!-- BEGIN DASHBOARD STATS -->
			<div class="row">
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat blue-madison">
						<div class="visual">
							<i class="icon-map"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php echo $count;?>
							</div>
							<div class="desc">
								 Subjects
							</div>
						</div>
						<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
			</div>
			<!-- END DASHBOARD STATS -->
			<!-- BEGIN PAGE CONTENT-->
            <?php if(@$this->session->userdata('message')):?>
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
								<i class="fa fa-plus"></i> Add Subject
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/subjects/insert">
								<div class="form-body">
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
									<div class="col-md-3"></div>
									<div class="col-md-9"><button class="btn green extra_course_button" type="button">Add Another Course</button><br /><br /></div>
									<div class="extra_course">
									
									</div>
									<div class="form-group">
										<label class="col-md-3 control-label">Subject Name <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="subject_name" placeholder="Enter class name" value="" required>
											<span class="help-inline"></span>
										</div>
									</div>
									<div class="extra"> 
										
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                        	<input type="hidden" name="status" value="1" />
											<input type="hidden" name="add_by" value="<?php echo $this->session->userdata('name');?>" />
                                            <input type="hidden" name="last_edit" value="<?php echo $this->session->userdata('name');?>" />
											<button type="submit" class="btn green">Add Subject</button>
											<button onclick="location.href = '<?php echo site_url()?>/subjects/all_subjects'" type="button" class="btn default">Cancel</button>
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