<?php
	$myAccess = checkUserAccess();
?>	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			All Teachers <small>Here you can find all teachers</small>
			</h3>-->
            <!-- BEGIN DASHBOARD STATS -->
			<!--<div class="row">
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat blue-madison">
						<div class="visual">
							<i class="fa fa-graduation-cap"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php //echo count($students);?>
							</div>
							<div class="desc">
								 Students
							</div>
						</div>
						<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
			</div>-->
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
								<i class="fa fa-plus"></i> Filter Students
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/punjab_council_roll_number/final_result">
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
                                        <label class="col-md-3 control-label">Class <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control classes" name="class_id">
                                            </select>
                                            <!--<span class="help-inline"></span>-->
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
								<i class="fa fa-list"></i> Result Pharmacy Technician
							</div>
						</div>
						<div class="portlet-body">
							<table class="table table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
                                <th class="hidden">
									 Hidden
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
									 Batch
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
                                	Class
                                </th>
                                <th>
                                	Exam Number
                                </th>
                                <th>
                                	Roll # Upload Date
                                </th>
                                <th>
                                	Roll #
                                </th>
                                <th>
                                	Result Upload Date
                                </th>
                                <th>
                                	Result Remarks
                                </th>
                                <th>
                                	Paper 1
                                </th>
                                <th>
                                	Paper 2
                                </th>
                                <th>
                                	Paper 3
                                </th>
                                <th>
                                	Paper 4
                                </th>
                                <th>
                                	Paper 5
                                </th>
                                <th>
                                	Paper 6
                                </th>
                                <th>
                                	Pass / Fail
                                </th>
                                <th>
                                	Next Chance
                                </th>
                                <th>
                                	Manual Remarks
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
									 <?php echo $student['roll_no'];?>
								</td>
								<td>
									<?php echo $student['first_name'].' '.$student['last_name'].' S/O '.$student['father_name'];?>
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
                                	<?php getStudentResultDetail($student['cnic'], 'class');?>
                                </td>
                                <td>
                                	<?php getStudentResultDetail($student['cnic'], 'exam_no');?>
                                </td>
                                <td>
                                	<?php getStudentResultDetail($student['cnic'], 'roll_no_update_date');?>
                                </td>
                                <td>
                                	<?php getStudentResultDetail($student['cnic'], 'roll_no');?>
                                </td>
                                <td>
                                	<?php getStudentResultDetail($student['cnic'], 'result_update_date');?>
                                </td>
                                <td>
                                	<?php getStudentResultDetail($student['cnic'], 'result_remarks');?>
                                </td>
                                <td>
                                	<?php getStudentResultDetail($student['cnic'], 'paper1');?>
                                </td>
                                <td>
                                	<?php getStudentResultDetail($student['cnic'], 'paper2');?>
                                </td>
                                <td>
                                	<?php getStudentResultDetail($student['cnic'], 'paper3');?>
                                </td>
                                <td>
                                	<?php getStudentResultDetail($student['cnic'], 'paper4');?>
                                </td>
                                <td>
                                	<?php getStudentResultDetail($student['cnic'], 'paper5');?>
                                </td>
                                <td>
                                	<?php getStudentResultDetail($student['cnic'], 'paper6');?>
                                </td>
                                <td>
                                	<?php getStudentResultDetail($student['cnic'], 'pass-fail');?>
                                </td>
                                <td>
                                	<?php getStudentResultDetail($student['cnic'], 'chance');?>
                                </td>
                                <td>
                                	<?php getStudentResultDetail($student['cnic'], 'manual-remarks');?>
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
    
    
    						<div class="modal fade" id="basic" tabindex="-1" role="basic" aria-hidden="true">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
											<h4 class="modal-title">Add Manual Remarks</h4>
										</div>
										<div class="modal-body">
											 <form class="form-horizontal" role="form" method="post" action="#">
                                                <div class="form-body">
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">Send Next Admission</label>
                                                        <div class="col-md-8 radio-list">
                                                            <label class="radio-inline">
                                                            <input type="radio" name="next_admission" id="optionsRadios4" value="1" checked> Yes </label>
                                                            <label class="radio-inline">
                                                            <input type="radio" name="next_admission" id="optionsRadios5" value="0"> No </label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">Remarks</label>
                                                        <div class="col-md-8">
                                                                <textarea class="form-control remarks" rows="3" name="remarks"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-actions">
                                                    <div class="row">
                                                        <div class="col-md-offset-3 col-md-9">
                                                            <input type="hidden" name="add_by" value="<?php echo $this->session->userdata('name');?>" />
                                                            <input type="hidden" name="id" class="result_id" value="" />
                                                            <button type="button" class="btn green add_remarks_button">Add Remarks</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn default close_button" data-dismiss="modal">Close</button>
										</div>
									</div>
									<!-- /.modal-content -->
								</div>
								<!-- /.modal-dialog -->
							</div>