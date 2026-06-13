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
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/next_council_admissions/index">
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
                                    <!--<div class="form-group">
                                        <label class="col-md-3 control-label">Council Exam # <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <input type="number" min="0" class="form-control classes" name="council_exam_no" />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Class <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <label class="radio-inline">
                                            <input type="radio" name="class" id="optionsRadios4" value="1" <?php //if(@$this->input->post('class')==1){echo 'checked';}?> /> 1st year </label>
                                            <label class="radio-inline">
                                            <input type="radio" name="class" id="optionsRadios5" value="2" <?php //if(@$this->input->post('class')==2){echo 'checked';}?> /> 2nd Year </label>
                                        </div>
                                    </div>-->
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
							<table class="table table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
                                <th class="hidden">
									 Hidden
								</th>
                                <th>
                                	Campus
                                </th>
                                <th>
                                	Class
                                </th>
                                <th>
									 Roll #
								</th>
								<th>
									 Name
								</th>
                                <th>
									 Mobile
								</th>
                                <th>
									 Contractor
								</th>
                                <th>
                                	Council Remarks
                                </th>
                                <th>
                                	Fee Status
                                </th>
                                <th>
                                	Manual Remarks
                                </th>
                                <th>
                                	Add Remarks
                                </th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=0;
								foreach($students as $student):
							?>
                            <tr class="odd gradeX">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
                                <td>
                                	<?php echo $student['campus_name'];?>
                                </td>
                                <td>
									<?php echo $student['class_name']?>
								</td>
                                <td>
									 <?php echo $student['roll_no'];?>
								</td>
								<td>
									<?php echo $student['first_name'].' '.$student['last_name'].' S/O '.$student['father_name'];?>
								</td>
                                <td>
									<?php echo $student['mobile'];?>
                                    <br />
                                    <?php echo $student['emergency_no'];?>
								</td>
                                <td>
									<?php 
										if($student['contract_id']==0)
										{
											echo 'N/A';
										}
										else
										{
											$this->db->select('*');
											$this->db->from('contracts');
											$this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','INNER');
											$this->db->where('contracts.contract_id',$student['contract_id']);
											$contractor_details = $this->db->get()->result_array();
											
											echo $contractor_details[0]['name'];
											echo '<br />';
											echo $contractor_details[0]['contract_name'];
										}
									?>
								</td>
                                <td>
                                	<?php getStudentResultRemarks($student['cnic']);?>
                                </td>
                                <td>
                                	<?php
                                    	$fee_status = $this->db->get_where('payments', array('custom_student_id'=>$student['student_id']))->result_array();
										if(count($fee_status)>0)
										{
											if($fee_status[0]['paid']==1)
											{
												$status='Paid';
											}
											else
											{
												$status='Unpaid';
											}
											echo $fee_status[0]['payment_comment'].'<br />';
											echo 'Status : '.$status.'<br />';
											echo 'Fee : '.$fee_status[0]['amount'];
										}
									?>
                                </td>
                                <td>
                                	<?php
                                    	$remarks = $this->db->get_where('send_next_admissions', array('student_id'=>$student['student_id']))->result_array();
										foreach($remarks as $remark)
										{
											echo $remark['remarks'].'<br />'.'('.$remark['add_date'].') '.$remark['add_by'].'<hr />';
										}
									?>
                                	
                                </td>
                                <td>
                                	<button type="button" class="btn green manual-remarks" data-student-id="<?php echo $student['student_id']?>" data-council-exam-no="<?php echo $this->input->post('council_exam_no');?>" data-toggle="modal" href="#basic">Add Remarks</button>
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
                                                            <input type="radio" class="next_admission" name="next_admission" id="optionsRadios1" value="0" checked> No </label>
                                                            
                                                            <label class="radio-inline">
                                                            <input type="radio" class="next_admission" name="next_admission" id="optionsRadios2" value="1"> Yes </label>
                                                            
                                                            <label class="radio-inline">
                                                            <input type="radio" class="next_admission" name="next_admission" id="optionsRadios3" value="2"> Not Confirm </label>
                                                            <label class="radio-inline">
                                                            <input type="radio" class="next_admission" name="next_admission" id="optionsRadios4" value="3"> Already Fee Created </label>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">Class </label>
                                                        <div class="col-md-8 radio-list">
                                                            <label class="radio-inline">
                                                            <input type="radio" name="class" id="optionsRadios4" value="1" checked="checked" /> 1st year </label>
                                                            <label class="radio-inline">
                                                            <input type="radio" name="class" id="optionsRadios5" value="2" /> 2nd Year </label>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">Current Study Status </label>
                                                        <div class="col-md-8 radio-list">
                                                            <label class="radio-inline">
                                                            <input type="radio" name="student_status" id="optionsRadios1" value="Study" checked="checked" /> Study </label>
                                                            <label class="radio-inline">
                                                            <input type="radio" name="student_status" id="optionsRadios2" value="Pending" /> Pending </label>
                                                            <label class="radio-inline">
                                                            <input type="radio" name="student_status" id="optionsRadios3" value="Leave" /> Leave </label>
                                                            <label class="radio-inline">
                                                            <input type="radio" name="student_status" id="optionsRadios4" value="Struck Off" /> Struck Off </label>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">Council Exam No</label>
                                                        <div class="col-md-8">
                                                                <input type="number" name="council_exam_no" class="form-control council_exam_no" value="" />
                                                        </div>
                                                    </div>
                                                    <div class="okadmission" style="display:none;">
                                                    
                                                    <div class="form-group">
                                                        <label class="control-label col-md-4">Date of Submit Fee</label>
                                                        <div class="col-md-8">
                                                            <div class="input-group input-medium date date-picker fee_submission_dates" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                                <input type="text" name="fee_submission_date" class="form-control fee_submission_date" value="<?php echo date('Y-m-d');?>" readonly>
                                                                <span class="input-group-btn">
                                                                <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                                </span>
                                                            </div>
                                                            <!-- /input-group -->
                                                            <!--<span class="help-block">
                                                            Select date </span>-->
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">Council Fee</label>
                                                        <div class="col-md-8">
                                                                <input type="number" min="4500" name="amount" class="form-control amount" value="6000" />
                                                        </div>
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
                                                            <input type="hidden" name="student_id" class="student_id" value="" />
                                                            <input type="hidden" name="council_exam_no" class="council_exam_no" value="<?php echo $this->input->post('council_exam_no');?>" />
                                                            <input type="hidden" name="clas" class="clas" value="<?php echo $this->input->post('class');?>" />
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