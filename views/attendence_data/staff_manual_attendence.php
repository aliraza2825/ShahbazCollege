
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
								<i class="fa fa-user"></i> Add Attendence Record
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/attendence_data/add_manual_attendence">
								<div class="form-body">
									<div class="form-group">
										<label class="col-md-3 control-label">Type <span class="required">*</span></label>
                                        <div class="col-md-3">
                                            <select class="form-control" name="type" id="type">
                                                <option value="">Select Type</option>
                                                <!--<option value="student">Student</option>-->
                                                <option value="teacher">Teacher</option>
                                                <option value="principal">Principal</option>
                                                <option value="accountant">Accountant</option>
                                                <option value="guard">Guard</option>
                                                <option value="admin">Admin</option>
                                            </select>
                                            <!--<span class="help-inline"></span>-->
                                        </div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Teacher/Admin/Student <span class="required">*</span></label>
                                        <div class="col-md-3">
                                            <select class="form-control select2me" name="user_id" id="teacher_student_id" required>
                                            	<option value="">Select teacher/admin/student</option>
												<?php
                                                	foreach($staffs as $staff):
												?>
                                                <option value="<?php echo $staff['user_id'];?>"><?php echo $staff['first_name'].' '.$staff['last_name'];?></option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                            <!--<span class="help-inline"></span>-->
                                        </div>
									</div>
                                    <div class="form-group">
                                        <label class="control-label col-md-3">Date</label>
                                        <div class="col-md-3">
                                            <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                <input type="text" name="date" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
                                                <span class="input-group-btn">
                                                <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Time </label>
										<div class="col-md-3">
											<div class="input-group">
                                                <input type="text" class="form-control timepicker timepicker-24" name="time" value="<?php echo @$timings[0]['checkin_timing']?>" required />
                                                <span class="input-group-btn">
                                                    <button class="btn default" type="button">
                                                        <i class="fa fa-clock-o"></i>
                                                    </button>
                                                </span>
                                            </div>
										</div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Add Attendence</button>
											<button onclick="location.href = '<?php echo site_url()?>'" type="button" class="btn default">Cancel</button>
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