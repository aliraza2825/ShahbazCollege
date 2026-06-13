
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
								<i class="fa fa-edit"></i> Edit Topic
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/topics/update/<?php echo $current_topic[0]['topic_id'];?>">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Course <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="course_id" class="form-control input-inline input-medium course_id" required>
                                                <option value="">Select Course</option>
												<?php
                                                	foreach ($courses as $course):
												?>
                                                <option value="<?php echo $course['course_id'];?>" <?php if($course['course_id']==$current_topic[0]['course_id']){echo 'selected';}?>><?php echo $course['course_name']?></option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                        </div>
									</div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Subject <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="course_subject_id" class="form-control input-inline input-medium subject_id" required>
                                                <?php
                                                	foreach ($subjects as $subject):
												?>
                                                <option value="<?php echo $subject['course_subject_id'];?>" <?php if($subject['course_subject_id']==$current_topic[0]['course_subject_id']){echo 'selected';}?>><?php echo $subject['subject_name']?></option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                        </div>
									</div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Chapter <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select class="form-control input-inline input-medium chapter_id" name="chapter_id" required>
												<?php
                                                	foreach ($chapters as $chapter):
												?>
                                                <option value="<?php echo $chapter['chapter_id'];?>" <?php if($chapter['chapter_id']==$current_topic[0]['chapter_id']){echo 'selected';}?>><?php echo $chapter['chapter_name']?></option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Topic Name <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="topic_name" placeholder="Enter course name" value="<?php echo $current_topic[0]['topic_name'];?>" required>
											<span class="help-inline"></span>
										</div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                            <input type="hidden" name="last_edit" value="<?php echo $this->session->userdata('name');?>" />
                                            <input type="hidden" name="status" value="1" />
											<button type="submit" class="btn green">Update Topic</button>
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