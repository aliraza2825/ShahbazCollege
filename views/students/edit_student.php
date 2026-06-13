
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Add Teacher <small>You can add teacher here</small>
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
								<i class="fa fa-edit"></i> Edit Student
							</div>
						</div>
						<div class="portlet-body form">
							<?php
                            	foreach($student as $stud):
							?>
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/students/update/<?php echo $this->uri->segment(3);?>" enctype="multipart/form-data">
								<div class="form-body">
									<div class="row">
                                    	<div class="col-md-12">
                                        	<h2>Personal Details</h2>
                                            <hr />
                                        </div>
                                      <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                                <div class="col-md-5">
                                                    <select class="form-control campus" name="campus_id">
                                                        <option value="">SELECT CAMPUS</option>
														<?php
															foreach($campuses as $campus):
														?>
                                                        <option value="<?php echo $campus['campus_id'];?>" <?php if($campus['campus_id']==$stud['campus_id']){echo 'selected=selected';}?>><?php echo $campus['campus_name'];?></option>
                                                        <?php
                                                        	endforeach;
														?>
                                                    </select>
                                                    <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                        </div>

										 <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Study Campus <span class="required">*</span></label>
                                                <div class="col-md-5">
                                                    <select class="form-control study_campus" name="study_campus">
                                                        <option value="">SELECT STUDY CAMPUS</option>
														<?php
															foreach($campuses as $campus):
														?>
                                                        <option value="<?php echo $campus['campus_id'];?>" <?php if($campus['campus_id']==$stud['study_campus']){echo 'selected=selected';}?>><?php echo $campus['campus_name'];?></option>
                                                        <?php
                                                        	endforeach;
														?>
                                                    </select>
                                                    <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">First Name <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="first_name" placeholder="Enter student first name" value="<?php echo $stud['first_name'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Last Name <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="last_name" placeholder="Enter student last name" value="<?php echo $stud['last_name'];?>" required>
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
                                                    <input type="text" class="form-control input-inline input-medium" name="father_name" placeholder="Enter student's father name" value="<?php echo $stud['father_name'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Gender <span class="required">*</span></label>
                                                <div class="col-md-5">
                                                    <select class="form-control" name="gender" required>
                                                        <option <?php if($stud['gender']=='Male'){echo 'selected=selected';}?>>Male</option>
                                                        <option <?php if($stud['gender']=='Female'){echo 'selected=selected';}?>>Female</option>
                                                    </select>
                                                    <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
									<div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Caste <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="caste" placeholder="Enter student's caste" value="<?php echo $stud['caste'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Religion <span class="required">*</span></label>
                                                <div class="col-md-5">
                                                    <select class="form-control" name="religion">
                                                        <option value="">Select Religion</option>
														<option <?php if($stud['religion']=='Muslim'){echo 'selected';}?>>Muslim</option>
                                                        <option <?php if($stud['religion']=='Christian'){echo 'selected';}?>>Christian</option>
														<option <?php if($stud['religion']=='Hindu'){echo 'selected';}?>>Hindu</option>
														<option <?php if($stud['religion']=='Other'){echo 'selected';}?>>Other</option>
                                                    </select>
                                                    <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
									<div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Qualification <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="qualification" placeholder="Enter student's qualification" value="<?php echo $stud['qualification'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">

                                        </div>
                                    </div>
                                    <div class="row">

										<div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Course <span class="required">*</span></label>
                                                <div class="col-md-5">
                                                    <select class="form-control course_id" name="course_id" required>
                                                        <option value="">SELECT COURSE</option>
														<?php
															foreach($courses as $course):
														?>
                                                        <option value="<?php echo $course['course_id'];?>" <?php if($course['course_id']==$stud['course_id']){echo 'selected';}?>><?php echo $course['course_name'];?></option>
                                                        <?php
                                                        	endforeach;
														?>
                                                    </select>
                                                    <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Class <span class="required">*</span></label>
                                                <div class="col-md-5">
                                                    <select class="form-control classes" name="class_id" required>
                                                        <?php
															foreach($classes as $class):
														?>
                                                        <option value="<?php echo $class['class_id'];?>" <?php if($class['dead_line_entry']<(date('Y-m-d'))){if($class['class_id']!=$stud['class_id']){echo 'disabled';}}?> <?php if($stud['class_id']==$class['class_id']){echo 'selected=selected';}?>><?php echo $class['name'];?></option>
                                                        <?php
                                                        	endforeach;
														?>
                                                    </select>
                                                    <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                    	<div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Roll # <span class="required"></span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="roll_no" placeholder="Enter student's roll #" value="<?php echo $stud['roll_no'];?>" readonly>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Board <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="board" placeholder="Enter student's board" value="<?php echo $stud['board'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Email </label>
                                                <div class="col-md-9">
                                                    <input type="email" class="form-control input-inline input-medium" name="email" placeholder="Enter student's email" value="<?php echo $stud['email'];?>">
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">CNIC # <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" id="cnic" name="cnic" placeholder="Enter student's CNIC" value="<?php echo $stud['cnic'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
									<div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Mark of Identification<span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="mark_of_identification" value="<?php echo $stud['mark_of_identification'];?>" placeholder="Enter student's Mark of Identification" value="">
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Place of Birth <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" id="pob" name="place_of_birth" value="<?php echo $stud['place_of_birth'];?>" placeholder="Enter student's Place of Birth" value="" required>
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
                                                    <div class="input-group input-medium date date-picker" data-date="<?php echo $stud['date_of_birth'];?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                        <input type="text" name="date_of_birth" class="form-control" value="<?php echo $stud['date_of_birth'];?>" readonly>
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
                                                <label class="control-label col-md-3">Registration Date</label>
                                                <div class="col-md-3">
                                                    <div class="input-group input-medium">
                                                        <input type="text" name="registration_date" class="form-control" value="<?php echo $stud['registration_date'];?>" readonly>
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
                                            <input type="hidden" class="form-control input-inline input-medium" name="total_fee" value="<?php echo $stud['total_fee'];?>" />
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Blood Group </label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="blood_group" placeholder="Enter student's blood group" value="<?php echo $stud['blood_group'];?>">
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
                                                    <input type="text" class="form-control input-inline input-medium" name="city" placeholder="Enter student's city" value="<?php echo $stud['city'];?>">
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Address <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="address" placeholder="Enter student's address" value="<?php echo $stud['address'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

									<div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">District <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="district" value="<?php echo $stud['district'];?>" placeholder="Enter student's District" value="">
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Tehsil <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium"  name="tehsil" value="<?php echo $stud['tehsil'];?>" placeholder="Enter student's Place of Birth" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="row">
                                        <div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Mobile <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" maxlength="11" class="form-control input-inline input-medium" name="mobile" placeholder="Enter student's mobile" value="<?php echo $stud['mobile'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Emergency No. <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" maxlength="11" class="form-control input-inline input-medium" name="emergency_no" placeholder="Enter teacher's emergency number" value="<?php echo $stud['emergency_no'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                    	<div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Contractor <span class="required">*</span></label>
                                                <div class="col-md-5">
                                                    <select class="form-control contractor_id" name="contractor_id">
                                                    	<option value="0">Select Contractor</option>
                                                        <?php
															foreach($contractors as $contractor):
														?>
                                                        <option value="<?php echo $contractor['contractor_id'];?>" <?php if($contractor['contractor_id']==$stud['contractor_id']){echo 'selected=selected';}?>>
															<?php echo $contractor['name'];?>
                                                        </option>
                                                        <?php
                                                        	endforeach;
														?>
                                                    </select>
                                                    <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Select Contract <span class="required">*</span></label>
                                                <div class="col-md-5">
                                                    <select class="form-control contract_id" name="contract_id">
                                                    	<?php
															foreach($contracts as $contract):
														?>
                                                        <option value="<?php echo $contract['contract_id'];?>" <?php if($contract['contract_id']==$stud['contract_id']){echo 'selected=selected';}?>>
															<?php echo $contract['contract_name'];?>
                                                        </option>
                                                        <?php
                                                        	endforeach;
														?>
                                                    </select>
                                                    <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Books</label>
                                                <div class="col-md-9 checkbox-list">
                                                    <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="books_1" value="1" <?php if($stud['books_1']=='1'){echo 'checked=checked';}?> /> 1st Year </label>
                                                    <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="books_2" value="1" <?php if($stud['books_2']=='1'){echo 'checked=checked';}?> /> 2nd Year </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Student Card</label>
                                                <div class="col-md-9 checkbox-list">
                                                    <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="student_card" value="1" <?php if($stud['student_card']=='1'){echo 'checked=checked';}?> /> Take Student Card </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                   
                                    <div class="row">
                                        <div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Study Type</label>
                                                <div class="col-md-5 radio-list">
                                                    <select class="form-control studytype" name="study_type" required>
                                                        <option value="">SELECT STUDY TYPE</option>
														<?php
															foreach($study_types as $study_type):
														?>
															<option value="<?php echo $study_type['id'];?>" <?php if($study_type['id']==$stud['study_type']){echo 'selected=selected';}?>><?php echo $study_type['name'];?></option>
                                                        <?php
                                                        	endforeach;
														?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    	<div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Shift</label>
                                                <div class="col-md-5 radio-list">
                                                    <select class="form-control shifts" name="shift" >
                                                        <option value="">SELECT SHIFT TYPE</option>
														<?php
															foreach($shift_types as $shift_type):
														?>
															<option value="<?php echo $shift_type['id'];?>" <?php if($shift_type['id']==$stud['shift']){echo 'selected=selected';}?>><?php echo $shift_type['name'];?></option>
                                                        <?php
                                                        	endforeach;
														?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
										<?php $study_types = $this->db->get_where('study_type',array('course_id'=>$stud['course_id']))->result_array();?>
                                        
                                        <div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Study Session</label>
                                                <div class="col-md-5 radio-list">
                                                    <select class="form-control" name="study_session" required>
                                                        <option value="">SELECT SESSION</option>
														<?php
															foreach($course_sessions as $course_session):
														?>
															<option value="<?php echo $course_session['session_name'];?>" <?php if($course_session['session_name']==$stud['study_session']){echo 'selected=selected';}?>><?php echo $course_session['session_name'];?></option>
                                                        <?php
                                                        	endforeach;
														?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row" id="student_notes">
                                    	<div class="col-md-6">
										<?php
                                        	$notes = json_decode($stud['notes']);
											if($notes):
											foreach($notes as $note):
										?>

                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Note</label>
                                                <div class="col-md-9">
                                                    <div class="col-md-12">
                                                        <textarea class="form-control" rows="3" name="note[]"><?php echo $note;?></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                        <?php
                                        	endforeach;
											endif;
										?>
                                        </div>
                                        <div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Section</label>
                                                <div class="col-md-9 radio-list">
                                                    <label class="radio-inline">
                                                    <input type="radio" name="section" id="optionsRadios4" value="First Year" <?php if($stud['section']=='First Year'){echo 'checked';}?>> First Year </label>
                                                    <label class="radio-inline">
                                                    <input type="radio" name="section" id="optionsRadios5" value="Second Year" <?php if($stud['section']=='Second Year'){echo 'checked';}?>> Second Year </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                    	<div class="col-md-6">
                                            <div class="col-md-3">
                                            </div>
                                            <div class="col-md-9">
                                                <button type="button" class="btn blue" id="add_note">Add Note</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                    	<div class="col-md-12">
                                        	<h2>Student Reference</h2>
                                            <hr />
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Reference User</label>
                                                <div class="col-md-5">
                                                    <select class="form-control" name="reference_user_id">
                                                        <option value="">Select Reference User</option>
                                                        <?php
                                                            foreach($references as $reference):
                                                        ?>
                                                        <option value="<?php echo $reference['reference_user_id'];?>" <?php if($reference['reference_user_id']==$stud['reference_user_id']){echo 'selected';}?>><?php echo $reference['name'].' ('.$reference['phone'].')';?></option>
                                                        <?php
                                                            endforeach;
                                                        ?>
                                                    </select>
                                                    <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                    	<div class="col-md-12">
                                        	<h2>Student Login Password</h2>
                                            <hr />
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Student Login Password <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="password" placeholder="Enter student's login password" value="" >
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                        	<input type="hidden" name="add_by" value="<?php echo $stud['add_by'];?>" />
											<input type="hidden" name="last_edit" value="<?php echo $this->session->userdata('name');?>" />
                                            <input type="hidden" name="old_password" value="<?php echo $stud['password'];?>" />
                                            <input type="hidden" name="status" value="1" />
											<button type="submit" class="btn green">Update Student</button>
											<button onclick="location.href = '<?php echo site_url()?>/students/all_students'" type="button" class="btn default">Cancel</button>
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
	
	<script>
    window.addEventListener('DOMContentLoaded',function () {
        $('.studytype').change(function(){
            var campus_id = $('.campus').val();
            var study = $(this).val();
            jQuery.ajax({
                type: "post",
                async: false,
                url: '<?php echo site_url()?>/timetable/getShifts',
                data: {
                    campus_id : campus_id,
                    study_type : study
                },
                success: function(data) {
                    $('.shifts').html(data);
                }
            });
        });
        
    });
</script>
	<!-- END CONTENT -->