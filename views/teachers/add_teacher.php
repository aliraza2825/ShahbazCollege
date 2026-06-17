
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
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
								<i class="fa fa-plus"></i> Add Staff
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/teachers/insert" enctype="multipart/form-data">
								<div class="form-body">
									<div class="row">
										<div class="col-md-12">
											<h2>Staff Details</h2>
                                            <hr />
										</div>
										<div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label"> Campus <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select name="campus_id" class="form-control input-inline input-large" required>
                                                        <option value="">SELECT CAMPUS</option>
														<?php
                                                            foreach($campuses as $campus):
                                                        ?>
                                                        <option value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name'];?></option>
                                                        <?php
                                                            endforeach;
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label"> Staff Type <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select name="staff_type_id" class="form-control input-inline input-large" required>
                                                        <option value="">SELECT STAFF TYPE</option>
														<?php
                                                            foreach($staff_types as $staff_type):
                                                        ?>
                                                        <option value="<?php echo $staff_type['staff_type_id'];?>"><?php echo $staff_type['staff_type_name'];?></option>
                                                        <?php
                                                            endforeach;
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label"> Department <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select name="department_id" class="form-control input-inline input-large" required>
                                                        <option value="">SELECT DEPARTMENT</option>
														<?php
                                                            foreach($departments as $department):
                                                        ?>
                                                        <option value="<?php echo $department['department_id'];?>"><?php echo $department['department_name'];?></option>
                                                        <?php
                                                            endforeach;
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label"> Designation <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select name="designation_id[]" class="form-control input-inline input-large designation_id select2" id="select2_sample1" multiple required>
													<?php
                                                            foreach($designations as $designation):
                                                        ?>
                                                        <option value="<?php echo $designation['designation_id'];?>"> <?php echo $designation['designation_name'];?></option>
                                                        <?php
                                                            endforeach;
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
									</div>
									<div class="row">
                                    	<div class="col-md-12">
                                        	<hr />
											<h2>Personal Details</h2>
                                            <hr />
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">First Name <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="first_name" placeholder="Enter teacher first name" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Last Name <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="last_name" placeholder="Enter teacher last name" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Father Name <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="father_name" placeholder="Enter teacher's father name" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Gender <span class="required">*</span></label>
                                                <div class="col-md-5">
                                                    <select class="form-control" name="gender">
                                                        <option>Male</option>
                                                        <option>Female</option>
                                                    </select>
                                                    <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Email </label>
                                                <div class="col-md-9">
                                                    <input type="email" class="form-control input-inline input-medium" name="email" placeholder="Enter teacher's email" value="">
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">CNIC # <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="cnic" id="cnic" placeholder="Enter teacher's CNIC" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Maritual Status </label>
                                                <div class="col-md-5">
                                                    <select class="form-control" name="maritual_status">
                                                    	<option>Married</option>
                                                        <option>Single</option>
                                                        <option>Divorced</option>
                                                    </select>
                                                    <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Blood Group </label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="blood_group" placeholder="Enter teacher's blood group" value="">
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                    	<div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="control-label col-md-3">Date of birth</label>
                                                <div class="col-md-3">
                                                    <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                        <input type="text" name="date_of_birth" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
                                                        <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                                    </div>
                                                    <!-- /input-group -->
                                                    <!--<span class="help-block">
                                                    Select date </span>-->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="control-label col-md-3">Joining Date</label>
                                                <div class="col-md-3">
                                                    <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                        <input type="text" name="joining_date" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
                                                        <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                                    </div>
                                                    <!-- /input-group -->
                                                    <!--<span class="help-block">
                                                    Select date </span>-->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Salary Per Day <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="salary" placeholder="Enter teacher's salary" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Designation </label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="designation" placeholder="Enter teacher's designation" value="" >
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Salary Adjustment</label>
                                                <div class="col-md-9">
                                                    <input type="number" step="0.01" min="0" class="form-control input-inline input-medium" name="salary_adjustment" placeholder="Enter salary adjustment" value="0">
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">City </label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="city" placeholder="Enter teacher's city" value="">
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Address </label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="address" placeholder="Enter teacher's address" value="">
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Mobile </label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="mobile" placeholder="Enter teacher's mobile" value="">
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Emergency No. </label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="emergency_no" placeholder="Enter teacher's emergency number" value="">
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bank_details">
                                            <div class="banks">
                                                <div class="form-group">
                                                    <label class="col-md-3 control-label">College Mobiles</label>
                                                    <div class="col-md-9">
                                                        <input type="number" class="form-control input-inline input-large" name="phones[]" placeholder="Enter Phone No" value="" />
                                                        <span class="help-inline"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" class="btn green add_more_phone"><i class="fa fa-plus"></i> Add More Phone</button>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Type</label>
                                                <div class="col-md-9 radio-list">
                                                    <label class="radio-inline">
                                                    <input type="radio" name="type" id="optionsRadios1" value="regular" checked> Regular </label>
                                                    <label class="radio-inline">
                                                    <input type="radio" name="type" id="optionsRadios2" value="daily"> Daily </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Note</label>
                                                <div class="col-md-9">
                                                    <div class="col-md-6">
                                                        <textarea class="form-control" rows="3" name="note"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                        	<hr />
                                            <h2>Login Details</h2>
                                            <hr />
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Username <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="username" placeholder="Enter teacher's username" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Password <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="password" placeholder="Enter teacher's password" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Role <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select class="form-control input-inline input-medium" name="role" required>
                                                    	<option value="Teacher">Teacher</option>
                                                        <option value="Principal">Principal</option>
                                                        <option value="Accountant">Accountant</option>
                                                        <option value="Guard">Guard</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Attendance ID</label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="attandance_id" placeholder="Enter Attendance ID" value="" >
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                        	<input type="hidden" name="status" value="1" />
											<button type="submit" class="btn green">Add Teacher</button>
											<button onclick="location.href = '<?php echo site_url()?>/teachers/all_teachers'" type="button" class="btn default">Cancel</button>
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
        document.addEventListener( "DOMContentLoaded", function(){
            $('.add_more_phone').click(function(){
                var html = '<div class="form-group">\n' +
                    '                                                    <label class="col-md-3 control-label">College Mobiles</label>\n' +
                    '                                                    <div class="col-md-9">\n' +
                    '                                                        <input type="number" class="form-control input-inline input-large" name="phones[]" placeholder="Enter Phone No" value="" />\n' +
                    '                                                        <span class="help-inline"></span>\n' +
                    '                                                    </div>\n' +
                    '                                                </div>';
                jQuery('.banks').append(html);
            });
        }, false );
    </script>






















