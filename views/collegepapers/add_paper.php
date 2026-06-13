<?php $monthly_test_exams = $this->db->get('monthly_test_exams')->result_array(); ?>
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
								<i class="fa fa-plus"></i> Create College Test
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/collegepapers/center" enctype="multipart/form-data" target="_blank">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Campuses <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="campus_id" class="form-control input-inline input-large campus_id" required>
                                                <option value="">Select Campus</option>
												<?php
                                                	foreach ($campuses as $campus):
												?>
                                                <option value="<?php echo $campus['campus_id']?>"><?php echo $campus['campus_name']?></option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                        </div>
									</div>
									
									<div class="form-group">
                                        <label class="col-md-3 control-label">Select Exam <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="exam_id" class="form-control input-inline input-large" required>
                                                <option value="">Select Exam</option>
												<?php
                                                	foreach ($monthly_test_exams as $exam):
												?>
                                                        <option value="<?php echo $exam['id']?>"><?php echo $exam['exam_name']?></option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                        </div>
									</div>

									<div class="form-group">
                                        <label class="col-md-3 control-label">Session <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="class_session[]" class="form-control input-inline input-large session select2" multiple required>
                                                
                                            </select>
                                        </div>
									</div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Course <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="course_id" class="form-control input-inline input-large course_id " required>
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
									<div class="class">
									</div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Subject <span class="required">*</span></label>
                                        <div class="col-md-9 checkbox-list">
                                            <select name="subject_id[]" class="form-control input-inline input-large subject_id select2" multiple required>
                                                
                                            </select>
                                        </div>
									</div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Chapter <span class="required">*</span></label>
                                        <div class="col-md-9 checkbox-list">
                                            <select name="chapter_id[]" class="form-control input-inline input-large chapter_id select2" multiple required>
                                                
                                            </select>
                                        </div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Topics <span class="required">*</span></label>
										<div class="col-md-9 checkbox-list topic_ids">
										
										</div>
									</div>
									<div class="mcqs" style="display:none;">
										<div class="form-group">
											<label class="col-md-3 control-label">Number of MCQs <span class="required">*</span></label>
											<div class="col-md-9">
												<input type="number" min="0" class="form-control input-inline input-large" name="mcqs" placeholder="Enter number of mcqs" value="0">
												<span class="help-inline"></span>
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label">Marks per MCQ <span class="required">*</span></label>
											<div class="col-md-9">
												<input type="number" min="0" class="form-control input-inline input-large" name="marks_mcq" placeholder="Enter per mcq marks" value="0">
												<span class="help-inline"></span>
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label">Number of Short Questions <span class="required">*</span></label>
											<div class="col-md-9">
												<input type="number" min="0" class="form-control input-inline input-large" name="short_questions" placeholder="Enter number of short questions" value="0">
												<span class="help-inline"></span>
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label">Marks per Short Question <span class="required">*</span></label>
											<div class="col-md-9">
												<input type="number" min="0" class="form-control input-inline input-large" name="short_question_mcq" placeholder="Enter per short question marks" value="0">
												<span class="help-inline"></span>
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label">Lines per Short Question <span class="required">*</span></label>
											<div class="col-md-9">
												<input type="number" min="0" class="form-control input-inline input-large" name="short_question_lines" placeholder="Enter per short question lines" value="0">
												<span class="help-inline"></span>
											</div>
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-3 control-label">Practicals <span class="required">*</span></label>
										<div class="col-md-9 checkbox-list practical_ids">
										
										</div>
									</div>
									<div class="practicals" style="display:none;">
										<div class="form-group">
											<label class="col-md-3 control-label">Marks per Practical <span class="required">*</span></label>
											<div class="col-md-9">
												<input type="number" min="0" class="form-control input-inline input-large" name="marks_practical" placeholder="Enter per practical marks" value="0">
												<span class="help-inline"></span>
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label">Lines per Practical <span class="required">*</span></label>
											<div class="col-md-9">
												<input type="number" min="0" class="form-control input-inline input-large" name="practical_lines" placeholder="Enter per practical lines" value="0">
												<span class="help-inline"></span>
											</div>
										</div>
									</div>
									
									<div class="form-group">
										<label class="col-md-3 control-label">Attendence Wise</label>
										<div class="col-md-9 checkbox-list">
											<label class="checkbox-inline">
											<input type="checkbox" id="inlineCheckbox1" name="attendence_wise" value="1" /> Print Paper Today Attendence Wise </label>
										</div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                            <button type="submit" class="btn green">Create Paper</button>
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