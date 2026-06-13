
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
                <form class="form-horizontal" role="form" target="_blank" method="post" action="<?php echo site_url();?>/students/add_student">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="col-md-3 control-label">CNIC # <span class="required">*</span></label>
                            <div class="col-md-9">
                                <input type="text" class="form-control input-inline input-medium" id="cnic2" name="cnic" placeholder="Enter student's CNIC" value="<?php echo @$students['cnic'];?>" required>
                                <span class="help-inline"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn green">Show</button>
                    </div>
                </form>
				    <div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-plus"></i> Add Student
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/students/insert" enctype="multipart/form-data">
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
                                                        <option value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name'];?></option>
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
                                                        <option value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name'];?></option>
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
                                                <label class="col-md-3 control-label">Course <span class="required">*</span></label>
                                                <div class="col-md-5">
                                                    <select class="form-control course_id" name="course_id" required>

                                                    </select>
                                                    <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Class <span class="required">*</span></label>
                                                <div class="col-md-5">
                                                    <select class="form-control classes" name="class_id">
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Study Type</label>
                                                <div class="col-md-5 radio-list">
                                                    <select class="form-control study_types" name="study_type" required>
                                                        
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Shift</label>
                                                <div class="col-md-5 radio-list">
                                                    <select class="form-control shifts" name="shift" required>

                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">First Name <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="first_name" placeholder="Enter student first name" value="<?php echo @$students['first_name'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Last Name <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="last_name" placeholder="Enter student last name" value="<?php echo @$students['last_name'];?>" required>
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
                                                    <input type="text" class="form-control input-inline input-medium" name="father_name" placeholder="Enter student's father name" value="<?php echo @$students['father_name'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Gender <span class="required">*</span></label>
                                                <div class="col-md-5">
                                                    <select class="form-control" name="gender" required>
                                                        <option value="">Select Gender</option>
                                                        <option <?php if(@$students['gender']=='Male'){echo 'selected=selected';}?>>Male</option>
                                                        <option <?php if(@$students['gender']=='Female'){echo 'selected=selected';}?>>Female</option>
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
                                                    <input type="text" class="form-control input-inline input-medium" name="caste" placeholder="Enter student's caste" value="<?php echo @$students['caste'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Religion <span class="required">*</span></label>
                                                <div class="col-md-5">
                                                    <select class="form-control" name="religion">
                                                        <option selected="selected">Muslim</option>
                                                        <option <?php if(@$students['religion']=='Muslim'){echo 'selected';}?>>Muslim</option>
                                                        <option <?php if(@$students['religion']=='Christian'){echo 'selected';}?>>Christian</option>
                                                        <option <?php if(@$students['religion']=='Hindu'){echo 'selected';}?>>Hindu</option>
                                                        <option <?php if(@$students['religion']=='Other'){echo 'selected';}?>>Other</option>
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
                                                    <input type="text" class="form-control input-inline input-medium" name="qualification" placeholder="Enter student's qualification" value="<?php echo @$students['qualification'];?>" required>
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
                                                <label class="col-md-3 control-label">Roll # <span class="required"></span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="roll_no" placeholder="Enter student's roll #" value="" readonly>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Board <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="board" placeholder="Enter student's board" value="<?php echo @$students['board'];?>" required>
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
                                                    <input type="email" class="form-control input-inline input-medium" name="email" placeholder="Enter student's email" value="<?php echo @$students['email'];?>">
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">CNIC # <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" id="cnic" name="cnic" placeholder="Enter student's CNIC" value="<?php echo @$students['cnic'];?>" required>
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
                                                    <input type="text" class="form-control input-inline input-medium" name="mark_of_identification" placeholder="Enter student's Mark of Identification" value="<?php echo @$students['mark_of_identification'];?>">
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Place of Birth <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" id="pob" name="place_of_birth" placeholder="Enter student's Place of Birth" value="<?php echo @$students['place_of_birth'];?>" required>
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
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="control-label col-md-3">Registration Date</label>
                                                <div class="col-md-3">
                                                    <div class="input-group input-medium">
                                                        <input type="text" name="registration_date" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
                                                       
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
                                                <label class="col-md-3 control-label">Total Fees <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="number" min="100000" class="form-control input-inline input-medium" name="total_fee" placeholder="Enter student's fees" value="0" readonly required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Blood Group </label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="blood_group" placeholder="Enter student's blood group" value="<?php echo @$students['blood_group'];?>">
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
                                                    <input type="text" class="form-control input-inline input-medium" name="city" placeholder="Enter student's city" value="<?php echo @$students['city'];?>">
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Address <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="address" placeholder="Enter student's address" value="<?php echo @$students['address'];?>" required>
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
                                                    <input type="text" class="form-control input-inline input-medium" name="district" value="<?php echo @$students['district'];?>" placeholder="Enter student's District" value="">
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Tehsil <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium"  name="tehsil" value="<?php echo @$students['tehsil'];?>" placeholder="Enter student's Place of Birth" value="" required>
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
                                                    <input type="text" maxlength="11" class="form-control input-inline input-medium" name="mobile" placeholder="Enter student's mobile" value="<?php echo @$students['mobile'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Emergency No. <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" maxlength="11" class="form-control input-inline input-medium" name="emergency_no" placeholder="Enter teacher's emergency number" value="<?php echo @$students['emergency_no'];?>" required>
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
                                                        <option value="<?php echo $contractor['contractor_id'];?>"><?php echo $contractor['name'];?></option>
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
                                                        <input type="checkbox" id="inlineCheckbox1" name="books_1" value="1" <?php if(@$students['books_1']=='1'){echo 'checked=checked';}?> /> 1st Year </label>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" id="inlineCheckbox2" name="books_2" value="1" <?php if(@$students['books_2']=='1'){echo 'checked=checked';}?> /> 2nd Year </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Student Card</label>
                                                <div class="col-md-9 checkbox-list">
                                                    <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="student_card" value="1" /> Take Student Card </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row" id="student_notes">
                                    </div>
                                    <div class="row">
                                    	<div class="col-md-6">
                                            <div class="col-md-3">
                                            </div>
                                            <div class="col-md-9">
                                                <button type="button" class="btn blue" id="add_note">Add Note</button>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Section</label>
                                                <div class="col-md-9 radio-list">
                                                    <label class="radio-inline">
                                                    <input type="radio" name="section" id="optionsRadios4" value="First Year" checked> First Year </label>
                                                    <label class="radio-inline">
                                                    <input type="radio" name="section" id="optionsRadios5" value="Second Year"> Second Year </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
									<?php $shift_types = $this->db->get('shifts')->result_array()  ?>
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
                                                        <option value="<?php echo $reference['reference_user_id'];?>"><?php echo $reference['name'].' ('.$reference['phone'].')';?></option>
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
                                        	<h2>Student Education</h2>
                                            <hr />
                                        </div>
                                        <div>
                                            <div class="col-md-12 student_education_area">
                                                <div class="form-group" id="div-0">
                                                    <label class="col-md-3 control-label"></label>
                                                    <div class="col-md-5">
                                                        <select class="form-control student_education" name="education[]" required>
                                                            <option value="">Select Education</option>
                                                            <?php
                                                                foreach($occupations as $occupation):
                                                            ?>
                                                            <option value="<?php echo $occupation['occupation_id'];?>"><?php echo $occupation['occupation_name'];?></option>
                                                            <?php
                                                                endforeach;
                                                            ?>
                                                        </select>
                                                        <!--<span class="help-inline"></span>-->
                                                    </div>
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
                                                    <input type="text" class="form-control input-inline input-medium" name="password" placeholder="Enter student's login password" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                        	<input type="hidden" name="add_by" value="<?php echo $this->session->userdata('name');?>" />
                                            <input type="hidden" name="last_edit" value="<?php echo $this->session->userdata('name');?>" />
                                        	<input type="hidden" name="status" value="1" />
											<button type="submit" class="btn green">Add Student</button>
											<button onclick="location.href = '<?php echo site_url()?>/students/all_students'" type="button" class="btn default">Cancel</button>
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
        $('.study_types').change(function(){
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
        $(document).on('change', '.student_education', function (e) {
                var occupation_id = this.value;
                var con = $(this).data('count');
                jQuery.ajax({
                    type: "post",
                    async: false,
                    url: '<?php echo site_url()?>/students/getSubOccupation',
                    data: {
                        occupation_id : occupation_id,
                        count : con,
                    },
                    success: function(data) {
                        if (data !="") {
                            con++;
                            for (let n=con;n<=count;n++){
                                //console.log($('#div-'+n));
                                $('#div-'+n).remove();
                            }
                            jQuery('.student_education_area').append(data);
                            count = con;
                            //$('#category_id'+(con--)).select2();
                        }else {
                            con++;
                            for (let n=con;n<=count;n++){
                                jQuery('#div-'+n).remove();
                            }
                            count = con;
                        }
                    }
                });
            });

    }, false );
</script>