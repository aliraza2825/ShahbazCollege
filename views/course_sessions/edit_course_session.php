
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
								<i class="fa fa-edit"></i> Edit Course Session
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/course_sessions/update_course_session/<?php echo $this->uri->segment(3);?>">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Course Name <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="course_id" class="form-control input-inline input-large course_id" required>
                                                <?php
                                                	foreach($courses as $course):
												?>
                                                <option value="<?php echo $course['course_id'];?>" <?php if($course['course_id']==$session[0]['course_id']){echo 'selected';}?>><?php echo $course['course_name'];?></option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                        </div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Session Name <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-large" name="session_name" placeholder="2022-2024" value="<?php echo $session[0]['session_name']?>" required>
											<span class="help-inline"></span>
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-md-3">Dead Line For Add / Edit Student <span class="required">*</span></label>
										<div class="col-md-3">
											<div class="input-group input-large date date-picker" data-date="<?php if($session[0]['dead_line_add_edit_student']=='0000-00-00'){echo date('Y-m-d');}else{echo $session[0]['dead_line_add_edit_student'];}?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
												<input type="text" name="dead_line_add_edit_student" class="form-control" value="<?php if($session[0]['dead_line_add_edit_student']=='0000-00-00'){echo date('Y-m-d');}else{echo $session[0]['dead_line_add_edit_student'];}?>" readonly>
												<span class="input-group-btn">
												<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
												</span>
											</div>
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-md-3">Last Date of auto fee installment <span class="required">*</span></label>
										<div class="col-md-9">
											<div class="input-group input-large date date-picker" data-date="<?php if($session[0]['last_date_auto_fee_installment']=='0000-00-00'){echo date('Y-m-d');}else{echo $session[0]['last_date_auto_fee_installment'];}?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
												<input type="text" name="last_date_auto_fee_installment" class="form-control" value="<?php if($session[0]['dead_line_add_edit_student']=='0000-00-00'){echo date('Y-m-d');}else{echo $session[0]['last_date_auto_fee_installment'];}?>" readonly>
												<span class="input-group-btn">
												    <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
												</span>
											</div>
											
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-3 control-label">Per Student Fee <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-large" name="per_student_fee" placeholder="Enter per student fee" value="<?php echo $session[0]['per_student_fee']?>" required>
											<span class="help-inline">If you change this fees you must update your payment plans</span>
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-md-3">Maximum Last Date of Fee This Session <span class="required">*</span></label>
										<div class="col-md-3">
											<div class="input-group input-large date date-picker" data-date="<?php if($session[0]['maximum_fee_last_date']=='0000-00-00'){echo date('Y-m-d');}else{echo $session[0]['maximum_fee_last_date'];}?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
												<input type="text" name="maximum_fee_last_date" class="form-control" value="<?php if($session[0]['maximum_fee_last_date']=='0000-00-00'){echo date('Y-m-d');}else{echo $session[0]['maximum_fee_last_date'];}?>" readonly>
												<span class="input-group-btn">
												<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
												</span>
											</div>
											<?php
												    $current_session = $session[0]['session_name'];
                                                    $years = explode("-", $current_session);
                                                    $lastYear = end($years);
												?>
												<span class="help-inline">Please select a date on or before 31-12-<?php echo $lastYear;?>.</span>
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-3 control-label">Minimum Student Installment Fee This Session <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-large" name="minimum_installment_fee" placeholder="Enter Minimum Student Installment Fee This Session" value="<?php echo $session[0]['minimum_installment_fee']?>" required>
											<span class="help-inline"></span>
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-3 control-label">Maximum Days Difference between installments <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-large" name="maximum_difference_installments" placeholder="Enter Maximum Difference in days" value="<?php echo $session[0]['maximum_difference_installments']?>" required>
											<span class="help-inline">Enter the number of days allowed between two installments for fee delete request.</span>
										</div>
									</div>
									<!--<div class="form-group">-->
         <!--                                   <label class="col-md-3 control-label">Council/board Fee</label>-->
         <!--                                   <div class="col-md-9 radio-list">-->
         <!--                                   <label class="radio-inline">-->
         <!--                                   <input type="radio" name="council_board_fee" id="optionsRadios3" class="council_board_fee" value="Yes" <?php if($session[0]['council_board_fee']=='Yes'){echo 'checked';}?>> Yes </label>-->
         <!--                                   <label class="radio-inline">-->
         <!--                                   <input type="radio" name="council_board_fee" id="optionsRadios4" class="council_board_fee" value="No" <?php if($session[0]['council_board_fee']=='no'){echo 'checked';}?>> No </label>-->
         <!--                               </div>-->
         <!--                           </div>-->
									<div class="council_date_fee" <?php if($session[0]['council_board_fee']=='No'){echo 'style="display:none;"';}?>>
    									<div class="form-group">
    										<label class="col-md-3 control-label">1st Time Council/Board Exam Number <span class="required">*</span></label>
    										<!--<div class="col-md-9">-->
    										<!--	<input type="text" class="form-control input-inline input-large" name="first_council_exam_no" placeholder="Enter exam number" value="<?php echo $session[0]['first_council_exam_no']?>" <?php if($session[0]['first_council_exam_no']=='Yes'){echo 'required';}?>>-->
    										<!--	<span class="help-inline"></span>-->
    										<!--</div>-->
    										<div class="col-md-9">
    											<!--<input type="text" class="form-control input-inline input-large" name="first_council_exam_no" placeholder="Enter exam number" value="" required>-->
    											<select name="first_council_exam_no" id="first_council_exam_no" class="form-control input-inline input-large exam_no" required>
    											    <option value="">SELECT EXAM</option>
    											    <?php
    											        foreach($exam_sequence as $first_council_exam_no):
    											    ?>
    											        <option value="<?php echo $first_council_exam_no['first_year'];?>" <?php if($first_council_exam_no['first_year'] == $session[0]['first_council_exam_no']){ echo 'selected';} ?>> <?php echo $first_council_exam_no['first_year'].' ( '.$first_council_exam_no['status']. ')'; ?></option>
    											    <?php endforeach; ?>
                                                </select>
    											<br>
    											<div class="council_sequence"></div>
    										</div>
    									</div>
    									<div class="form-group" style="display: none">
                                            <label class="col-md-3 control-label">First Time Council/Board Fee <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <input type="number" class="form-control input-inline input-large first_time_council_fee" name="first_time_council_fee" placeholder="Enter First Time Council Fee" value="<?php echo $session[0]['first_time_council_fee']?>" min="0" <?php if($session[0]['council_board_fee']=='Yes'){echo 'required';}?>>
                                                <span class="help-inline"></span>
                                            </div>
                                        </div>
    									<div class="form-group" style="display: none">
                                            <label class="control-label col-md-3">Last Date of Council/Board Fee</label>
                                            <div class="col-md-3">
                                                <div class="input-group input-large date date-picker" data-date="<?php if($session[0]['last_date_council_fee']=='0000-00-00'){echo date('Y-m-d');}else{echo $session[0]['last_date_council_fee'];}?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                    <input type="text" name="last_date_council_fee" class="form-control last_date_council_fee" value="<?php if($session[0]['last_date_council_fee']=='0000-00-00'){echo date('Y-m-d');}else{echo $session[0]['last_date_council_fee'];}?>" readonly>
                                                    <span class="input-group-btn">
                                                    <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
									<div class="form-group">
                                            <label class="control-label col-md-3">Re Admission Fee</label>
                                            <div class="col-md-3">
                                                <input type="number" class="form-control input-inline input-large" name="re_admission_fee" placeholder="Re Admission Amount" min="0" value="<?php echo $session[0]['re_admission_fee']?>" required>
                                                <span class="help-inline"></span>

                                            </div>
                                    </div>
									<div class="form-group">
                                            <label class="control-label col-md-3">Freeze Fee</label>
                                            <div class="col-md-3">
                                                <input type="number" class="form-control input-inline input-large" name="freeze_fee" placeholder="Freeze Amount" min="0" value="<?php echo $session[0]['freeze_fee']?>" required>
                                                <span class="help-inline"></span>
                                            </div>
                                    </div>
									<div class="form-group">
										<label class="control-label col-md-3">Maximum Student Freeze Date <span class="required">*</span></label>
										<div class="col-md-3">
											<div class="input-group input-large date date-picker" data-date="<?php if($session[0]['freeze_last_date']=='0000-00-00'){echo date('Y-m-d');}else{echo $session[0]['freeze_last_date'];}?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
												<input type="text" name="freeze_last_date" class="form-control" value="<?php if($session[0]['freeze_last_date']=='0000-00-00'){echo date('Y-m-d');}else{echo $session[0]['freeze_last_date'];}?>" readonly>
												<span class="input-group-btn">
												<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
												</span>
											</div>
										</div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Update Course Session</button>
											<button onclick="location.href = '<?php echo site_url()?>/course_sessions/all_course_sessions'" type="button" class="btn default">Cancel</button>
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
	document.addEventListener("DOMContentLoaded", function(event) {
	    
        $('.council_board_fee').change(function(){
            var council_board_fee = $(this).val();
            
            if(council_board_fee=='Yes')
            {
                $('.council_date_fee').show();
                $('.first_council_exam_no').attr('required','required');
                $('.last_date_council_fee').attr('required','required');
                $('.maximum_council_fee_last_date').attr('required','required');
            }
            else
            {
                $('.council_date_fee').hide();
                $('.first_council_exam_no').removeAttr('required');
                $('.last_date_council_fee').removeAttr('required');
                $('.maximum_council_fee_last_date').removeAttr('required');
            }
        });
        $('.course_id').change(function(){
            var course_id = jQuery(this).val();
            if(course_id!='')
            {
                jQuery.ajax({
                    type: "post",
                    async: false,
                    url: '<?php echo site_url()?>/course_sessions/getexams',
                    data: {
                        course_id : course_id
                    },
                    success: function(data) {
                        jQuery('.exam_no').html(data);
                    }

                });
            }
        });
        $('.exam_no').change(function(){
            var exam_no = $('.course_id').val();
            var exam_sequence_id = $(this).val();
            
            if(exam_no != '')
            {
                jQuery.ajax({
                    type: "post",
                    async: false,
                    url: '<?php echo site_url()?>/course_sessions/getcouncilsequence',
                    data: {
                        course_id : exam_no,
                        exam_sequence_id : exam_sequence_id,
                    },
                    success: function(data) {
                        jQuery('.council_sequence').html(data);
                    }

                });
            }
        });
        var exam_no = $('.course_id').val();
            var exam_sequence_id = $('.exam_no').val();
            
            if(exam_no != '')
            {
                jQuery.ajax({
                    type: "post",
                    async: false,
                    url: '<?php echo site_url()?>/course_sessions/getcouncilsequence',
                    data: {
                        course_id : exam_no,
                        exam_sequence_id : exam_sequence_id,
                    },
                    success: function(data) {
                        jQuery('.council_sequence').html(data);
                    }

                });
            }
    });

</script>