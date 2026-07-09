
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Add Teacher <small>You can add teacher here</small>
			</h3>-->
			<!-- END PAGE HEADER-->
            <!-- BEGIN DASHBOARD STATS -->
			<div class="row">
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat blue-madison">
						<div class="visual">
							<i class="icon-users"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php echo $count;?>
							</div>
							<div class="desc">
								 Staff
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
								<i class="fa fa-edit"></i> Edit Staff
							</div>
						</div>
                        <?php
                        	foreach($teachers as $teacher):
						?>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/teachers/update/<?php echo $this->uri->segment(3);?>" enctype="multipart/form-data">
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
                                                        <option value="<?php echo $campus['campus_id'];?>" <?php if($campus['campus_id']==$teacher['campus_id']){echo 'selected';}?>><?php echo $campus['campus_name'];?></option>
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
                                                        <option value="<?php echo $staff_type['staff_type_id'];?>" <?php if($staff_type['staff_type_id']==$teacher['staff_type_id']){echo 'selected';}?>><?php echo $staff_type['staff_type_name'];?></option>
                                                        <?php
                                                            endforeach;
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label"> Staff Shift (Shift + Study Type) <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select name="staff_shift_id" class="form-control input-inline input-large" required>
                                                        <option value="">SELECT STAFF SHIFT</option>
														<?php foreach($staff_shifts as $staff_shift): ?>
                                                        <option value="<?php echo $staff_shift['staff_shift_id'];?>" <?php if($staff_shift['staff_shift_id']==$teacher['staff_shift_id']){echo 'selected';}?>><?php echo staff_shift_label($staff_shift); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label"> Department <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select name="department_id" class="form-control input-inline input-large department_id" required>
                                                        <option value="">SELECT DEPARTMENT</option>
														<?php
                                                            foreach($departments as $department):
                                                        ?>
                                                        <option value="<?php echo $department['department_id'];?>" <?php if($department['department_id']==$teacher['department_id']){echo 'selected';}?>><?php echo $department['department_name'];?></option>
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
                                                        <option value="<?php echo $designation['designation_id'];?>" <?php if(in_array($designation['designation_id'], explode(',',@$teacher['designation_id']))){echo 'selected';}?>><?php echo $designation['designation_name'];?></option>
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
                                                    <input type="text" class="form-control input-inline input-medium" name="first_name" placeholder="Enter teacher first name" value="<?php echo $teacher['first_name'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Last Name <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="last_name" placeholder="Enter teacher last name" value="<?php echo $teacher['last_name'];?>" required>
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
                                                    <input type="text" class="form-control input-inline input-medium" name="father_name" placeholder="Enter teacher's father name" value="<?php echo $teacher['father_name'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Gender <span class="required">*</span></label>
                                                <div class="col-md-5">
                                                    <select class="form-control" name="gender">
                                                        <option <?php if($teacher['gender']=='Male'){echo 'selected=selected';}?>>Male</option>
                                                        <option <?php if($teacher['gender']=='Female'){echo 'selected=selected';}?>>Female</option>
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
                                                    <input type="email" class="form-control input-inline input-medium" name="email" placeholder="Enter teacher's email" value="<?php echo $teacher['email']?>">
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">CNIC # <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="cnic" id="cnic" placeholder="Enter teacher's CNIC" value="<?php echo $teacher['cnic']?>" required>
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
                                                    	<option <?php if($teacher['maritual_status']=='Married'){echo 'selected=selected';}?>>Married</option>
                                                        <option <?php if($teacher['maritual_status']=='Single'){echo 'selected=selected';}?>>Single</option>
                                                        <option <?php if($teacher['maritual_status']=='Divorced'){echo 'selected=selected';}?>>Divorced</option>
                                                    </select>
                                                    <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Blood Group </label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="blood_group" placeholder="Enter teacher's blood group" value="<?php echo $teacher['blood_group']?>">
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
                                                        <input type="text" name="date_of_birth" class="form-control" value="<?php echo $teacher['date_of_birth'];?>" readonly>
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
                                                        <input type="text" name="joining_date" class="form-control" value="<?php echo $teacher['joining_date'];?>" readonly>
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
                                                <label class="col-md-3 control-label">Type</label>
                                                <div class="col-md-9 radio-list">
                                                    <label class="radio-inline">
                                                        <input type="radio" name="type" id="optionsRadios1" value="regular" <?php if($teacher['type']=='regular'){echo 'checked';}?> /> Regular </label>
                                                    <label class="radio-inline">
                                                        <input type="radio" name="type" id="optionsRadios2" value="daily" <?php if($teacher['type']=='daily'){echo 'checked';}?> /> Daily </label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Designation </label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="designation" placeholder="Enter teacher's designation" value="<?php echo $teacher['designation']?>" >
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
                                                    <input type="text" class="form-control input-inline input-medium" name="city" placeholder="Enter teacher's city" value="<?php echo $teacher['city']?>">
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Address </label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="address" placeholder="Enter teacher's address" value="<?php echo $teacher['address']?>">
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
                                                    <input type="text" class="form-control input-inline input-medium" name="mobile" placeholder="Enter teacher's mobile" value="<?php echo $teacher['mobile']?>">
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Emergency No. </label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="emergency_no" placeholder="Enter teacher's emergency number" value="<?php echo $teacher['emergency_no']?>">
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bank_details">
                                        <div class="banks">
                                            <?php $numbers = $this->db->get_where("users_phones","user_id = '".$teacher['user_id']."'")->result_array();
                                                foreach ($numbers as $number):
                                            ?>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">College Mobiles</label>
                                                <div class="col-md-9">
                                                    <input type="number" class="form-control input-inline input-large" name="phones[]" placeholder="Enter Phone No" value="<?php echo $number['phone'] ?>" />
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <button type="button" class="btn green add_more_phone"><i class="fa fa-plus"></i> Add More Phone</button>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Note</label>
                                                <div class="col-md-9">
                                                    <div class="col-md-6">
                                                        <textarea class="form-control" rows="3" name="note"><?php echo $teacher['note']?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <hr />
                                            <h2>Salary & Allowance</h2>
                                            <hr />
                                        </div>
                                        <div class="col-md-6">
<!--                                            <div class="form-group">-->
<!--                                                <label class="col-md-3 control-label">Basic Salary <span class="required">*</span></label>-->
<!--                                                <div class="col-md-9">-->
<!--                                                    <input type="text" class="form-control input-inline input-medium" name="salary" placeholder="Enter teacher's salary"  value="--><?php //echo $teacher['salary']?><!--" required>-->
<!--                                                    <span class="help-inline"></span>-->
<!--                                                </div>-->
<!--                                            </div>-->

                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Gross Salary <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="gross_salary" placeholder="Enter teacher's salary" value="<?php echo $teacher['gross_salary']?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Salary Adjustment</label>
                                                <div class="col-md-9">
                                                    <input type="number" step="0.01" min="0" class="form-control input-inline input-medium" name="salary_adjustment" placeholder="Enter salary adjustment" value="<?php echo isset($teacher['salary_adjustment']) ? $teacher['salary_adjustment'] : 0; ?>">
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Statutory Rules</label>
                                                <div class="col-md-9">
                                                    <select class="form-control input-inline input-medium" name="apply_statutory_rules">
                                                        <option value="1" <?php echo (!isset($teacher['apply_statutory_rules']) || (int) $teacher['apply_statutory_rules'] === 1) ? 'selected' : ''; ?>>Apply</option>
                                                        <option value="0" <?php echo (isset($teacher['apply_statutory_rules']) && (int) $teacher['apply_statutory_rules'] === 0) ? 'selected' : ''; ?>>Do Not Apply</option>
                                                    </select>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <style>
                                            .ex1 {
                                                background-color:#e1dee2;
                                                width: 500px;
                                                height: 150px;
                                                overflow: auto;
                                                border-radius: 5px!important;
                                                box-shadow: 10px 10px 9px -2px rgba(0,0,0,0.22);
                                            }
                                            .allow{
                                                padding: 5px 0px 5px 20px ;
                                            }
                                        </style>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-md-2">
                                                    <label class="control-label">Allowances</label>
                                                </div>
                                                <div class="col-md-10">
                                                    <div class="ex1">
                                                        <?php
                                                        $i = 0;
                                                        foreach( $allowances as $als){
                                                            ?>
                                                            <div class="allow" >
                                                                <input type="checkbox" class="form-control " name="allowance_id[]" <?php
                                                                foreach ($allowances_check as $ckal){
                                                                if(@$ckal['allowance_id']==$als['id']){ ?>checked<?php }
                                                                }
                                                                ?>   value="<?php  echo $i ?>" >
                                                                <label ><?php echo $als['name'] ?></label>
                                                            </div>


                                                        <?php $i++;  } ?>
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
                                                    <input type="text" class="form-control input-inline input-medium" name="username" placeholder="Enter teacher's username" value="<?php echo $teacher['username'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if($teacher['role']!='Admin'){?>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Password </label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="password" placeholder="Enter teacher's password" value="" >
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Role <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select class="form-control input-inline input-medium" name="role" required>
                                                        <option value="Admin" <?php if($teacher['role']=='Admin'){echo 'selected=selected';}?>>Admin</option>
                                                    	<option value="Teacher" <?php if($teacher['role']=='Teacher'){echo 'selected=selected';}?>>Teacher</option>
                                                        <option value="Principal" <?php if($teacher['role']=='Principal'){echo 'selected=selected';}?>>Principal</option>
                                                        <option value="Accountant" <?php if($teacher['role']=='Accountant'){echo 'selected=selected';}?>>Accountant</option>
                                                        <option value="Guard" <?php if($teacher['role']=='Guard'){echo 'selected=selected';}?>>Guard</option>
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
                                            <input type="hidden" name="hidden_password" value="<?php echo $teacher['password'];?>" />
											<button type="submit" class="btn green">Update Teacher</button>
											<button onclick="location.href = '<?php echo site_url()?>/teachers/all_teachers'" type="button" class="btn default">Cancel</button>
										</div>
									</div>
								</div>
							</form>
						</div>
                        <?php
                        	endforeach;
						?>
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
