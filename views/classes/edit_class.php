
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Edit Class <small>You can edit this class</small>
			</h3>-->
            <!-- BEGIN DASHBOARD STATS -->
			<div class="row">
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div style="display:none;" class="dashboard-stat blue-madison">
						<div class="visual">
							<i class="fa fa-child"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php //echo $count;?>
							</div>
							<div class="desc">
								 Students
							</div>
						</div>
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
								<i class="fa fa-edit"></i> Edit Class
							</div>
						</div>
						<div class="portlet-body form">
                        	<div class="alert alert-info">
							    You can change uneditable fields from course session.
							</div>
                        	<?php 
								foreach($class as $clas):
							?>
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/classes/update/<?php echo $this->uri->segment(3);?>">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Class Campus <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="campus_id" class="form-control input-inline input-large campus_id">
                                                <?php
                                                	foreach($campuses as $campus):
												?>
                                                <option value="<?php echo $campus['campus_id'];?>" <?php if($campus['campus_id']==$clas['campus_id']){echo 'selected';}?>><?php echo $campus['campus_name'];?></option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                        </div>
									</div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Course Name <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="course_id" class="form-control input-inline input-large course_id" required>
                                                <option value="">SELECT COURSE</option>
												<?php
                                                	foreach($courses as $course):
												?>
                                                <option value="<?php echo $course['course_id'];?>" <?php if($course['course_id']==$clas['course_id']){echo 'selected';}?>><?php echo $course['course_name'];?></option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                        </div>
									</div>
									<div class="form-group">
										<label class="col-md-3 control-label">Session <span class="required">*</span></label>
										<div class="col-md-9">
											<select name="session" class="form-control input-inline input-large course_session" required>
												<option value="">SELECT SESSION</option>
												<?php
													foreach($course_sessions as $course_session):
												?>
												<option value="<?php echo $course_session['session_name'];?>" <?php if($course_session['session_name']==$clas['session']){echo 'selected';}?>><?php echo $course_session['session_name'];?></option>
												<?php
													endforeach;
												?>
											</select>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Class Name <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-large" name="name" placeholder="Enter class name" value="<?php echo $clas['name']?>" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Class Badge No <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control input-inline input-large" name="badge_no" placeholder="Enter class name" value="<?php echo $clas['badge_no']?>" required>
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Number of seats <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-large" name="seats" placeholder="Enter class seats" value="<?php echo $clas['seats']?>" required>
											<span class="help-inline"></span>
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-3 control-label">Online Study</label>
										<div class="col-md-9 radio-list">
											<label class="radio-inline">
											<input type="radio" name="online_study" id="optionsRadios4" value="1" <?php if($clas['online_study']==1){echo 'checked';}?>> Yes </label>
											<label class="radio-inline">
											<input type="radio" name="online_study" id="optionsRadios5" value="0" <?php if($clas['online_study']==0){echo 'checked';}?>> No </label>
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-3 control-label">Per Student Fee <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-large per_student_fee" name="class_fee" placeholder="Enter per student fee" value="<?php echo $clas['class_fee'];?>" required readonly>
											<span class="help-inline"></span>
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-3 control-label">Minimum Installment Fee <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-large minimum_installment_fee" name="minimum_installment_fee" placeholder="Enter minimum per month fee" value="<?php echo $clas['minimum_installment_fee'];?>" required readonly>
											<span class="help-inline">Minimum Per month installment fee</span>
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-3 control-label">Dead Line For Add / Edit Student <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-large dead_line_add_edit_student" name="dead_line_entry" placeholder="" value="<?php echo $clas['dead_line_entry'];?>" required readonly>
											<span class="help-inline"></span>
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-3 control-label">Maximum Last Date Fee <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-large last_date_auto_fee_installment" name="maximum_fee_last_date" placeholder="" value="<?php echo $clas['maximum_fee_last_date'];?>" required readonly>
											<span class="help-inline"></span>
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-3 control-label">Maximum Days Difference between installments <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-large maximum_difference_installments" name="maximum_difference_installments" placeholder="Enter Maximum Difference in days" value="<?php echo $clas['maximum_difference_installments'];?>" required readonly>
											<span class="help-inline">Enter the number of days allowed between two installments for fee delete request.</span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">1st Time Council Exam Number <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-large first_council_exam_no" name="exam_no" placeholder="Enter exam number" value="<?php echo $clas['exam_no'];?>" required readonly>
											<span class="help-inline"></span>
										</div>
									</div>
                                    
									<!--
                                    <div class="form-group">
										<label class="col-md-3 control-label">Phone 1 (Receive Fee Notification) + Fee Dues Information Number</label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-large" name="phone1" placeholder="Enter phone 1" value="" />
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Phone 2 (Receive Fee Notification)</label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-large" name="phone2" placeholder="Enter phone 2" value="" />
											<span class="help-inline"></span>
										</div>
									</div>
									-->
                                    <div class="form-group">
                                            <label class="control-label col-md-3">Freeze Fee</label>
                                            <div class="col-md-3">
                                                <input type="number" class="form-control input-inline input-large freeze_fee" name="freeze_fee" placeholder="Freeze Amount" min="0" value="<?php echo $clas['freeze_fee'];?>" required readonly>
                                                <span class="help-inline"></span>

                                            </div>
                                    </div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Maximum Freeze Date <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-large freeze_last_date" name="freeze_last_date" placeholder="" value="<?php echo $clas['freeze_last_date'];?>" required readonly>
											<span class="help-inline"></span>
										</div>
									</div>
									<div class="form-group">
                                            <label class="control-label col-md-3">Re Admission Fee</label>
                                            <div class="col-md-3">
                                                <input type="number" class="form-control input-inline input-large re_admission_fee" name="admission_fee" placeholder="Re Admission Amount" min="0" value="<?php echo $clas['admission_fee'];?>" required readonly>
                                                <span class="help-inline"></span>

                                            </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                        	<input type="hidden" name="status" value="1" />
											<button type="submit" class="btn green">Update Class</button>
											<button onclick="location.href = '<?php echo site_url()?>/classes/all_classes'" type="button" class="btn default">Cancel</button>
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
	
<script>
	document.addEventListener("DOMContentLoaded", function(event) {
        
        $('.campus_id').change(function(){
            var campus_id = $('.campus_id').val();
            var course_id = $('.course_id').val();
            var course_session = $('.course_session').val();
            collectData(campus_id,course_id,course_session);
        });
        
        $('.course_id').change(function(){
            var campus_id = $('.campus_id').val();
            var course_id = $('.course_id').val();
            var course_session = $('.course_session').val();
            collectData(campus_id,course_id,course_session);
        });
        
        $('.course_session').change(function(){
            var campus_id = $('.campus_id').val();
            var course_id = $('.course_id').val();
            var course_session = $('.course_session').val();
            collectData(campus_id,course_id,course_session);
        });
        
    });
    
    function collectData(campus_id,course_id,course_session)
    {
        if(campus_id!=null && course_id!=null && course_session!='')
        {
            $.ajax({
                type: "post",
                async: false,
                url: '<?php echo site_url()?>/classes/getCourseDetail',
                data: {
                    campus_id : campus_id,
                    course_id : course_id,
                    course_session : course_session
                },
                success: function (data) {
                    myData = JSON.parse(data);
                    //console.log(myData);
                    $('.per_student_fee').val(myData[0].per_student_fee);
                    $('.minimum_installment_fee').val(myData[0].minimum_installment_fee);
                    $('.dead_line_add_edit_student').val(myData[0].dead_line_add_edit_student);
                    $('.last_date_auto_fee_installment').val(myData[0].last_date_auto_fee_installment);
                    $('.maximum_difference_installments').val(myData[0].maximum_difference_installments);
                    $('.first_council_exam_no').val(myData[0].first_council_exam_no);
                    $('.freeze_fee').val(myData[0].freeze_fee);
                    $('.freeze_last_date').val(myData[0].freeze_last_date);
                    $('.re_admission_fee').val(myData[0].re_admission_fee);
                }
            });
        }
    }

</script>