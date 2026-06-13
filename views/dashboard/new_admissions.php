	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			All Teachers <small>Here you can find all teachers</small>
			</h3>-->
            <!-- BEGIN DASHBOARD STATS -->
			<div class="row">
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat blue-madison">
						<div class="visual">
							<i class="fa fa-graduation-cap"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php echo count($students);?>
							</div>
							<div class="desc">
								 New Admissions
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
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i>All Students
							</div>
						</div>
                        <div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/dashboard/new_students">
								<div class="form-body">
                                	<div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Month <span class="required">*</span></label>
                                                <div class="col-md-5">
                                                    <select class="form-control" name="month" required>
                                                        <option value="01" <?php if($month==1){echo 'selected=selected';}?>>January</option>
                                                        <option value="02" <?php if($month==2){echo 'selected=selected';}?>>February</option>
                                                        <option value="03" <?php if($month==3){echo 'selected=selected';}?>>March</option>
                                                        <option value="04" <?php if($month==4){echo 'selected=selected';}?>>April</option>
                                                        <option value="05" <?php if($month==5){echo 'selected=selected';}?>>May</option>
                                                        <option value="06" <?php if($month==6){echo 'selected=selected';}?>>June</option>
                                                        <option value="07" <?php if($month==7){echo 'selected=selected';}?>>July</option>
                                                        <option value="08" <?php if($month==8){echo 'selected=selected';}?>>August</option>
                                                        <option value="09" <?php if($month==9){echo 'selected=selected';}?>>September</option>
                                                        <option value="10" <?php if($month==10){echo 'selected=selected';}?>>Octomer</option>
                                                        <option value="11" <?php if($month==11){echo 'selected=selected';}?>>November</option>
                                                        <option value="12" <?php if($month==12){echo 'selected=selected';}?>>December</option>
                                                        
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Year <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="year" placeholder="Enter Year" value="<?php echo $year;?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Check</button>
										</div>
									</div>
								</div>
                            </form>
                        </div>
						<div class="portlet-body">
							<div class="table-toolbar">
								<div class="row">
									<div class="col-md-6">
										<div class="btn-group">
											<button onclick="location.href = '<?php echo site_url()?>/students/add_student'" class="btn green">
											Add New <i class="fa fa-plus"></i>
											</button>
										</div>
									</div>
								</div>
							</div>
							<table class="table table-striped table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
                                <th class="hidden">
									 Hidden
								</th>
                                <th>
									 Student Information &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								</th>
                                <th>
									 Student Image
								</th>
                                <th>
									 Contractor
								</th>
                                <th>
									 Payment Details
								</th>
                                <th>
									 Paid Fee Details &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								</th>
                                <th>
									 Unpaid Fee Details &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								</th>
                                <th>
									 Add By
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
									//CHECK FEE
									$this->db->order_by('dead_line','ASC');
									$payments=$this->db->get_where('payments',array('student_id'=>$student['student_id']))->result_array();
									$total_fee = 0;
									$created_council_fee = 0;
									$submitted_council_fee = 0;
									$fee_decided_current_time = 0;
									$total_fee_submitted = 0;
									$unpaid_installments_current_time = 0;
									
									foreach($payments as $payment)
									{
										if($payment['payment_plan']!='consulation fee')
										{
											$total_fee+=$payment['amount'];
										}
										if($payment['payment_plan']=='consulation fee')
										{
											$created_council_fee+=$payment['amount'];
											if($payment['paid']==1)
											{
												$submitted_council_fee+=$payment['actual_amount'];
											}
										}
										if($payment['dead_line']<date('Y-m-d'))
										{
											$fee_decided_current_time+=$payment['amount'];
											if($payment['paid']==0)
											{
												$unpaid_installments_current_time++;
											}
										}
										if($payment['paid']==1 && $payment['payment_plan']!='consulation fee')
										{
											$total_fee_submitted+=$payment['actual_amount'];
										}
									}
							?>
                            <tr class="odd gradeX">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
                                <td>
                                	Campus : <?php echo $student['campus_name'];?>
                                    <br />
                                    Course : <?php echo $student['course_name'];?>
                                    <br />
                                    Session : <span class="bold"><?php echo $student['session'];?></span>
                                    <br />
                                    Class : <?php echo $student['class_name'];?>
                                    <br />
                                    Registration Date : <span class="bold"><?php echo $student['registration_date'];?></span>
                                    <br />
                                    Student Name : <span class="bold"><?php echo $student['first_name'].' '.$student['last_name'];?></span>
                                    <br />
                                    CNIC : <?php echo $student['cnic'];?>
                                    <br />
                                    Father Name : <?php echo $student['father_name'];?>
                                    <br />
                                    Roll # : <span class="bold"><?php echo $student['roll_no'];?></span>
                                    <br />
                                    Mobile : <span class="bold"><?php echo $student['mobile'];?> - <?php echo $student['emergency_no'];?></span>
                                </td>
                                
								<td>
									<?php $student_image = $this->db->get_where('student_documents',array('student_id'=>$student['student_id'],'type'=>'Photo'))->result_array();?>
                                    <?php
                                    	if(@$student_image[0]['image']!='' && @$student_image[0]['online_image']==''):
									?>
										<img width="100" src="<?php echo base_url().'uploads/'.@$student_image[0]['image'];?>" alt="" />
                                    <?php
                                    	endif;
									?>
                                    <?php
                                    	if(@$student_image[0]['image']!='' && @$student_image[0]['online_image']!=''):
									?>
										<img width="100" src="<?php echo @$student_image[0]['online_image'];?>" alt="" />
                                    <?php
                                    	endif;
									?>
                                    
                                    <br />
                                    <?php
                                    	$id_card = $this->db->get_where('student_documents', array('type'=>'ID Card', 'student_id'=>$student['student_id']))->result_array();
										if(count($id_card)>0):
									?>
                                    <i class="fa fa-check"></i> ID Card
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
                                	Total Fee : <?php echo $student['total_fee'];?>
                                    <br />
                                    Total Created Fee : <?php echo $total_fee;?>
                                    <br />
                                    Total Created Council Fee : <?php echo $created_council_fee;?>
                                    <br />
                                    Total Submitted Council Fee : <?php echo $submitted_council_fee;?>
                                    <br />
                                    Fee Decided Current Time : <span class="bold"><?php echo $fee_decided_current_time;?></span>
                                    <br />
                                    Total Fee Submitted : <span class="bold"><?php echo $total_fee_submitted;?></span>
                                    <br />
                                    Remaining Fee Payable Current Time : <span class="bold"><?php echo $fee_decided_current_time-$total_fee_submitted;?></span>
                                    <br />
                                    Unpaid installments Current Time : <span class="bold"><?php echo $unpaid_installments_current_time;?></span>
                                    <br />
                                    Percentage Fee Received : <?php if($total_fee>0){echo round(($total_fee_submitted/$total_fee)*100).'%';}else{echo '0%';}?>
                                    <br />
                                    Percentage Paid Fee According to Decision : <?php if($fee_decided_current_time>0){echo round(($total_fee_submitted/$fee_decided_current_time)*100).'%';}else{echo '0%';}?>
                                </td>
                                <td>
                                	<?php
                                    	foreach($payments as $payment)
										{
											if($payment['paid']==1)
											{
												echo $payment['actual_amount'].' Paid on '.$payment['paid_date'].'<br />';
											}
										}
									?>
                                </td>
                                <td>
                                	<?php
                                    	foreach($payments as $payment)
										{
											if($payment['paid']==0)
											{
												if($payment['dead_line']<date('Y-m-d'))
												{
													echo '<span class="bold">'.$payment['amount'].' Not Paid on '.$payment['dead_line'].'</span><br />';
												}
												else
												{
													echo $payment['amount'].' Not Paid on '.$payment['dead_line'].'<br />';
												}
											}
										}
									?>
                                </td>
								<td>
                                	<?php echo $student['add_by'];?>
                                </td>
                                <td>
									<a title="Edit" href="<?php echo site_url().'/students/edit_student/'.$student['student_id'];?>" class="btn blue"><i class="fa fa-edit"></i></a>
                                    <a title="Documents" href="<?php echo site_url().'/students/upload_documents/'.$student['student_id'];?>" class="btn green"><i class="fa fa-image"></i></a>
                                    <?php
                                    	if($student['contractor_id']==0):
									?>
                                    <a title="Payments" href="<?php echo site_url().'/students/payments/'.$student['student_id'];?>" class="btn purple"><i class="fa fa-money"></i></a>
                                    <?php
                                    	else:
									?>
                                    
                                    <?php
                                    	endif;
									?>
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
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->
    						<div class="modal fade" id="basic" tabindex="-1" role="basic" aria-hidden="true">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
											<h4 class="modal-title">Do you want to delete this student?</h4>
										</div>
										<div class="modal-body">
											 <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/students/delete">
                                                <div class="form-body">
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">Delete Type</label>
                                                        <div class="col-md-8 radio-list">
                                                            <label class="radio-inline">
                                                            <input type="radio" class="delete_type" name="delete_type" id="optionsRadios1" value="Delete" checked> Delete </label>
                                                            
                                                            <label class="radio-inline">
                                                            <input type="radio" class="delete_type" name="delete_type" id="optionsRadios2" value="Freeze"> Freeze </label>
                                                        </div>
                                                    </div>
													<div class="form-group">
                                                        <label class="col-md-4 control-label">Reason</label>
                                                        <div class="col-md-8 radio-list">
                                                            <label class="radio-inline">
                                                            <input type="radio" class="reason" name="reason" id="optionsRadios1" value="fee" checked> fee </label>
                                                            
                                                            <label class="radio-inline">
                                                            <input type="radio" class="reason" name="reason" id="optionsRadios2" value="discipline"> discipline </label>
                                                            
                                                            <label class="radio-inline">
                                                            <input type="radio" class="reason" name="reason" id="optionsRadios3" value="other"> other </label>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">Reason in Details</label>
                                                        <div class="col-md-8">
                                                                <textarea class="form-control remarks" rows="3" name="reason_detail"></textarea>
                                                        </div>
                                                    </div>
                                                    
													<?php
														if(@$myAccess[0]['student_issue_refund']==1 || $this->session->userdata('role')=='Admin'):
													?>
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">Refund Amount</label>
                                                        <div class="col-md-8">
                                                                <input type="number" max="0" name="refund_amount" class="form-control refund_amount" value="" />
                                                        </div>
                                                    </div>
													<?php
														endif;
													?>
                                                    
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">Image</label>
                                                        <div class="col-md-8">
                                                                <input type="file" name="image" class="form-control" />
                                                        </div>
                                                    </div>
                                                    
                                                </div>
                                                
                                                <div class="form-actions">
                                                    <div class="row">
                                                        <div class="col-md-offset-3 col-md-9">
                                                            <input type="hidden" name="add_by" value="<?php echo $this->session->userdata('name');?>" />
                                                            <input type="hidden" name="student_id" class="student_id" value="" />
                                                            <button type="submit" class="btn red">Delete Student</button>
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