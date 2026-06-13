
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Edit Class <small>You can edit this class</small>
			</h3>-->
            
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
								<i class="fa fa-edit"></i> Edit Subject
							</div>
						</div>
						<div class="portlet-body form">
                        	<?php 
								foreach($current_subject as $sub):
							?>
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/subjects/update/<?php echo $this->uri->segment(3);?>">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Course <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="course_id" class="form-control input-inline input-medium course_id" required>
                                                <option value="">Select Course</option>
												<?php
                                                	foreach ($courses as $course):
												?>
                                                <option value="<?php echo $course['course_id']?>" <?php if($course['course_id']==$sub['course_id']){echo 'selected';}?>><?php echo $course['course_name']?></option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                        </div>
									</div>
									<div class="col-md-3"></div>
									<div class="col-md-9"><button class="btn green extra_course_button" type="button">Add Another Course</button><br /><br /></div>
									<div class="extra_course">
										<?php
										$extra_course_ids = explode(',',$sub['extra_course_ids']);
										foreach($extra_course_ids as $extra_course_id)
										{
										?>
										<div class="form-group">
											<label class="col-md-3 control-label">Other Course <span class="required">*</span></label>
											<div class="col-md-9">
												<select name="extra_course_ids[]" class="form-control input-inline input-medium course_id">
													<option value="">Select Course</option>
													<?php
														foreach ($courses as $course):
													?>
													<option value="<?php echo $course['course_id'];?>" <?php if($course['course_id']==$extra_course_id){echo 'selected';}?>><?php echo $course['course_name']?></option>
													<?php
														endforeach;
													?>
												</select>
											</div>
										</div>
										<?php
										}
										?>
									</div>
									<div class="form-group">
										<label class="col-md-3 control-label">Subject Name <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="subject_name" placeholder="Enter subject name" value="<?php echo $sub['subject_name']?>" required>
											<span class="help-inline"></span>
										</div>
									</div>
									<div class="extra"> 
										<?php
											if($current_course[0]['course_type']=='Annual'):
										?>
										<div class="form-group">
											<label class="col-md-3 control-label">Select Subject Year <span class="required">*</span></label>
											<div class="col-md-9">
												<select name="subject_year" class="form-control input-inline input-medium" required>
													<option value="">Select Subject Year</option>
													<?php
														for($i=1;$i<=$current_course[0]['course_duration_year'];$i++):
													?>
													<option value="<?php echo $i;?>" <?php if($i==$sub['subject_year']){echo 'selected';}?>><?php echo $i;?> Year</option>
													<?php
														endfor;
													?>
												</select>
											</div>
										</div>
										<?php
											endif;
										?>
										<?php
											if($current_course[0]['course_type']=='Semester'):
										?>
										<div class="form-group">
											<label class="col-md-3 control-label">Select Subject Semester <span class="required">*</span></label>
											<div class="col-md-9">
												<select name="subject_semester" class="form-control input-inline input-medium" required>
													<option value="">Select Subject Semester</option>
													<?php
														for($i=1;$i<=$current_course[0]['course_semester'];$i++):
													?>
													<option value="<?php echo $i;?>" <?php if($i==$sub['subject_semester']){echo 'selected';}?>><?php echo $i;?> Semester</option>
													<?php
														endfor;
													?>
												</select>
											</div>
										</div>
										<?php
											endif;
										?>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                        	<input type="hidden" name="status" value="1" />
                                            <input type="hidden" name="last_edit" value="<?php echo $this->session->userdata('name');?>" />
											<button type="submit" class="btn green">Update Subject</button>
											<button onclick="location.href = '<?php echo site_url()?>/subjects/all_subjects'" type="button" class="btn default">Cancel</button>
										</div>
									</div>
								</div>
							</form>
                            <?php
                            	endforeach;
							?>
						</div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->