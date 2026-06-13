
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
								<i class="fa fa-plus"></i> Add Course
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/courses/insert_course">
								<div class="form-body">
								
									<div class="col-md-12">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select class="form-control" id="select2_sample2" name="campus_ids[]" multiple required>
                                                        <?php
                                                            foreach($campuses as $campus):
                                                        ?>
                                                        <option value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name'];?></option>
                                                        <?php
                                                            endforeach;
                                                        ?>
                                                    </select>
                                                    <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                        </div>
								
								
									<div class="form-group">
										<label class="col-md-3 control-label">Course Name <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="course_name" placeholder="Enter course name" value="" required>
											<span class="help-inline"></span>
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-3 control-label">Course Code <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="course_code" placeholder="Enter course code" value="" required>
											<span class="help-inline"></span>
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-3 control-label">Course Type</label>
										<div class="col-md-9 radio-list">
											<label class="radio-inline">
											<input type="radio" name="course_type" id="optionsRadios4" value="Annual" checked> Annual </label>
											<label class="radio-inline">
											<input type="radio" name="course_type" id="optionsRadios5" value="Semester"> Semester </label>
										</div>
									</div>
									<div class="annual">
										<div class="form-group">
											<label class="col-md-3 control-label">Course Duration (Years) <span class="required">*</span></label>
											<div class="col-md-9">
												<input type="number" min="0" class="form-control input-inline input-medium" name="course_duration_year" placeholder="Enter Course Duration Years" value="" required />
												<span class="help-inline"></span>
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label">Course Duration (Months) <span class="required">*</span></label>
											<div class="col-md-9">
												<input type="number" min="0" class="form-control input-inline input-medium" name="course_duration_month" placeholder="Enter Course Duration Months" value="" required />
												<span class="help-inline"></span>
											</div>
										</div>
									</div>
									<div class="semester" style="display:none;">
										<div class="form-group">
											<label class="col-md-3 control-label">Course Semesters <span class="required">*</span></label>
											<div class="col-md-9">
												<input type="number" min="0" class="form-control input-inline input-medium course_semester" name="course_semester" placeholder="Enter Course Semester" value="" />
												<span class="help-inline"></span>
											</div>
										</div>
									</div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Free Mobile Study <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="checkbox" class="form-control input-inline input-medium" name="free" value="1"/>
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Regular Mobile Study <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="checkbox" class="form-control input-inline input-medium" name="regular" value="1" />
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Demo Mobile Study <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="checkbox" class="form-control input-inline input-medium" name="demo" value="1" />
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Paid Mobile Study <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="checkbox" class="form-control input-inline input-medium" name="paid" value="1" />
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Not assigned Mobile Study <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="checkbox" class="form-control input-inline input-medium" name="not_assigned" value="1"/>
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                            <input type="hidden" name="add_by" value="<?php echo $this->session->userdata('name');?>" />
                                            <input type="hidden" name="last_edit" value="<?php echo $this->session->userdata('name');?>" />
                                            <input type="hidden" name="status" value="1" />
											<button type="submit" class="btn green">Add Course</button>
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