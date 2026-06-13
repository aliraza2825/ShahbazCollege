<?php
	$myAccess = checkUserAccess();
?>
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
								<i class="fa fa-plus"></i> Filter Students
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/documents/students_documents">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control campus" name="campus_id">
                                                <option value="">ALL CAMPUS</option>
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
									<div class="form-group">
                                        <label class="col-md-3 control-label">Course <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control course_id" name="course_id">
                                                <option value="">ALL COURSE</option>
												<?php
													foreach($courses as $course):
												?>
                                                <option value="<?php echo $course['course_id'];?>"><?php echo $course['course_name'];?></option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                        </div>
                                    </div>
							
									<div class="class">
										<div class="form-group">
											<label class="col-md-3 control-label">Class <span class="required">*</span></label>
											<div class="col-md-5">
												<select class="form-control classes" name="class_id">
												</select>
												<!--<span class="help-inline"></span>-->
											</div>
										</div>
								</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                            <input name="form_submit" type="hidden" value="1" />
                                            <button type="submit" class="btn green">Check</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
            <?php
            	if(@$this->input->post('form_submit')):
			?>
            <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i>All Students
							</div>
						</div>
						<div class="portlet-body">
							<div class="row">
								<div class="col-md-4 text-center" style="margin-bottom:15px">
									<form method="post" action="<?php echo site_url();?>/documents/print_college_admission_letters" target="_blank">
										<input type="hidden" name="student_ids" class="student_ids form-group" value="" required />
										<input type="submit" class="btn blue" value="Get Selected Students College Admissions Letter" />
									</form>
								</div>
								<div class="col-md-4 text-center" style="margin-bottom:15px">
									<form method="post" action="<?php echo site_url();?>/documents/print_council_admission_forms" target="_blank">
										<input type="hidden" name="student_ids" class="student_ids form-group" value="" required />
										<input type="submit" class="btn purple" value="Get Selected Students Council Admissions Form" />
									</form>
								</div>
								<div class="col-md-4 text-center" style="margin-bottom:15px">
									<form method="post" action="<?php echo site_url();?>/documents/print_struck_off_letters" target="_blank">
										<input type="hidden" name="student_ids" class="student_ids form-group" value="" required />
										<input type="submit" class="btn red" value="Get Selected Students Struckoff Letters" />
									</form>
								</div>
								<div class="col-md-4 text-center" style="margin-bottom:15px">
									<form method="post" action="<?php echo site_url();?>/documents/print_college_student_cards" target="_blank">
										<input type="hidden" name="student_ids" class="student_ids form-group" value="" required />
										<input type="submit" class="btn green" value="Get Selected Students College Card" />
									</form>
								</div>
								<div class="col-md-4 text-center" style="margin-bottom:15px">
									<form method="post" action="<?php echo site_url();?>/documents/print_student_character_certificates" target="_blank">
										<input type="hidden" name="student_ids" class="student_ids form-group" value="" required />
										<input type="submit" class="btn yellow" value="Get Selected Students Character Certificate" />
									</form>
								</div>
                                <div class="col-md-4 text-center" style="margin-bottom:15px">
                                    <form method="post" action="<?php echo site_url();?>/documents/print_student_checklist" target="_blank">
                                        <input type="hidden" name="student_ids" class="student_ids form-group" value="" required />
                                        <input type="submit" class="btn yellow" value="Get Selected Students CheckList Form" />
                                    </form>
                                </div>
                                <input style="margin-left: 20px;" type="checkbox" id="checkAll" class="all_selection" name="all_selection"/>
                                <label for="vehicle1"> Select All</label><br>

                                <br />
							</div>
							<table class="table table-bordered table-hover">
							<thead>
							<tr>
                                <th class="hidden">
									 Hidden
								</th>
								<th>
									Selection
								</th>
                                <th>
                                	Campus
                                </th>
                                <th>
                                	Course
                                </th>
                                <th>
									 Roll #
								</th>
								<th>
									 Name
								</th>
                                <th>
									 CNIC
								</th>
                                <th>
									 Class
								</th>
                                <th>
									 Mobile
								</th>
                                <th>
									 Contractor
								</th>
                                <th>
									 Type
								</th>
                                <th>
									 Documents
								</th>
								<th>
									 Result Remarks
								</th>
                                <th>
									 Last Edit
								</th>
                                <th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=0;
								foreach($students as $student):
								$payment_plan = $this->db->get_where('payments', array('student_id'=>$student['student_id'], 'contractor_id'=>0))->result_array();
								if(count($payment_plan)>0)
								{
									$payment_alert='';
								}
								elseif($student['contractor_id']>0)
								{
									$payment_alert='';
								}
								else
								{
									$payment_alert='alert alert-danger';
								}
							?>
                            <tr class="odd gradeX <?php echo $payment_alert;?>">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
								<td>
									 <input type="checkbox" class="selection" name="selection" value="<?php echo $student['student_id'];?>" />
								</td>
                                <td>
									 <?php echo $student['campus_name'];?>
								</td>
                                <td>
									 <?php echo $student['course_name'];?>
								</td>
                                <td>
									 <?php echo $student['roll_no'];?>
								</td>
								<td>
									<?php echo $student['first_name'].' '.$student['last_name'];?> <?php if($student['gender']=='Male'){echo 'S/O';}else{echo 'D/O';}?> <?php echo $student['father_name'];?>
								</td>
                                <td>
									<?php echo $student['cnic']?>
								</td>
                                <td>
									<?php echo $student['class_name']?>
								</td>
                                <td>
									<?php echo $student['mobile'];?>
                                    <br />
                                    <?php echo $student['emergency_no'];?>
								</td>
                                <td>
									<?php 
										if($student['contractor_id']==0)
										{
											echo 'N/A';
										}
										else
										{
											$contractor = $this->db->get_where('contractors', array('contractor_id'=>$student['contractor_id']))->result_array();
											echo $contractor[0]['name'].' ('.$contractor[0]['date'].')';
										}
									?>
								</td>
                                <td>
                                	<?php echo $student['section'];?>
                                    <br />
                                    <?php echo $student['shift'];?>
                                    <br />
                                    <?php echo $student['study_type'];?>
                                    <br />
                                    <?php
                                    	if($student['student_card']==1)
										{
											echo 'Student Card Taken';
										}
									?>
                                </td>
                                <td style="text-align:center">
                                	<?php
                                    	$id_card = $this->db->get_where('student_documents', array('type'=>'ID Card', 'student_id'=>$student['student_id']))->result_array();
										if(count($id_card)>0):
									?>
                                    <i class="fa fa-check"></i> ID card
                                    <br />
                                    <?php
                                    	endif;
									?>
                                    <?php
                                    	$photo = $this->db->get_where('student_documents', array('type'=>'Photo', 'student_id'=>$student['student_id']))->result_array();
										if(count($photo)>0):
									?>
                                    <i class="fa fa-check"></i> Photo
                                    <br />
                                    <?php
                                    	endif;
									?>
                                    <?php
                                    	$result_card = $this->db->get_where('student_documents', array('type'=>'Result Card', 'student_id'=>$student['student_id']))->result_array();
										if(count($result_card)>0):
									?>
                                    <i class="fa fa-check"></i> Result Card
                                    <?php
                                    	endif;
									?>
                                    
                                </td>
                                <td>
                                	<?php getStudentResultRemarks($student['cnic']);?>
                                </td>
                                <td>
                                	<?php echo $student['last_edit'];?>
                                </td>
								<td>
                                	<a target="_blank" style="margin-bottom:5px;" href="<?php echo site_url();?>/documents/print_admission_form/<?php echo $student['student_id'];?>" class="btn blue"><i class="fa fa-print"> College Admission Letter</i></a>
									<br />
									<a target="_blank" style="margin-bottom:5px;" href="<?php echo site_url();?>/documents/print_council_admission_form/<?php echo $student['student_id'];?>" class="btn purple"><i class="fa fa-print"> Council Admission Form</i></a>
									<br />
									<a target="_blank" style="margin-bottom:5px;" href="<?php echo site_url();?>/documents/print_struck_off_notice/<?php echo $student['student_id'];?>" class="btn red"><i class="fa fa-print"> Struckoff Letter</i></a>
									<br />
									<?php
                                    	if(@$myAccess[0]['student_college_card']==1 || $this->session->userdata('role')=='Admin'):
									?>
									<a target="_blank" style="margin-bottom:5px;" href="<?php echo site_url();?>/documents/print_student_card/<?php echo $student['student_id'];?>" class="btn green"><i class="fa fa-print"> Student College Card</i></a>
									<br />
									<?php
										endif;
									?>
									<a target="_blank" style="margin-bottom:5px;" href="<?php echo site_url();?>/documents/print_student_character_certificate/<?php echo $student['student_id'];?>" class="btn yellow"><i class="fa fa-print"> Student Character Certificate</i></a>
									<br />
                                    <a target="_blank" style="margin-bottom:5px;" href="<?php echo site_url();?>/documents/student_all_document/<?php echo $student['student_id'];?>" class="btn purple"><i class="fa fa-print">Student All Document</i></a>
                                    <br>
                                </td>
							</tr>
                            <?php
								$i++;
                            	endforeach;
							?>
							</tbody>
							</table>
						</div>
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>
            <?php
            	endif;
			?>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->
<script>
    document.addEventListener( "DOMContentLoaded", function(){
        $("#checkAll").click(function(){
            alert("Hello");
            $('.selection').trigger('click');
        });
    }, false );
</script>