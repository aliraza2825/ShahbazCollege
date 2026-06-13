	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			All Teachers <small>Here you can find all teachers</small>
			</h3>-->
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
								<i class="fa fa-list"></i>Fee Details
							</div>
						</div>
						<div class="portlet-body">
						
						<table class="table table-striped table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
                                <th >
									 Sr
								</th>
								<th>
									 Admission Date
								</th>
								<th>
									 Course Name
								</th>
                                <th>
									 Student Information &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								</th>
                                <th>
									 Student Image
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
									 Paid / Required Fee
								</th>
								<th>
									 Add By
								</th>
								<th>
									 Incentive Percentage
								</th>
                                
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=0;
								foreach($fee_dues_comments as $student):
									//CHECK FEE
									
									$incentive=$this->db->get_where('admission_management_incentives',array('incentive_id'=>$this->uri->segment(3)))->result_array();
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
										if($payment['payment_plan'])
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
										if($payment['paid']==1 )
										{
											$total_fee_submitted+=$payment['actual_amount'];
										}
									}
							?>
                            <tr class="odd gradeX">
                                <td >
                                	<?php echo $i+1;?>
                                </td>
								 <td >
                                	<?php echo $student['entry_date'];?>
                                </td>
								<td >
                                	<?php echo $student['course_name'];?>
                                </td>
                                <td>
                                	Campus : <?php echo $student['campus_name'];?>
                                    <br />
                                   
                                    Class : <?php echo $student['class_name'];?>
                                    <br />
                                    Registration Date : <span class="bold"><?php echo $student['entry_date'];?></span>
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
                                    	$id_card = $this->db->get_where('student_documents', array('type'=>'ID Card', 'student_id'=>$student['student_id']))->result_array();
										if(count($id_card)>0):
									?>
                                    <i class="fa fa-check"></i> ID Card
                                    <br />
									<?php
                                    	endif;
									?>
									<?php
                                    	$id_card = $this->db->get_where('student_documents', array('type'=>'B - FORM', 'student_id'=>$student['student_id']))->result_array();
										if(count($id_card)>0):
									?>
                                    <i class="fa fa-check"></i> B - Form
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
									<?php
                                    	$photo = $this->db->get_where('student_documents', array('type'=>'College Form', 'student_id'=>$student['student_id']))->result_array();
										if(count($photo)>0):
									?>
                                    <i class="fa fa-check"></i> College Form
                                    <br />
                                    <?php
                                    	endif;
									?>
									<?php
                                    	$photo = $this->db->get_where('student_documents', array('type'=>'Rules and Regulation Form', 'student_id'=>$student['student_id']))->result_array();
										if(count($photo)>0):
									?>
                                    <i class="fa fa-check"></i> Rules and Regulation Form
                                    <br />
                                    <?php
                                    	endif;
									?>
									<?php
                                    	$photo = $this->db->get_where('student_documents', array('type'=>'Fee Strcuture Form', 'student_id'=>$student['student_id']))->result_array();
										if(count($photo)>0):
									?>
                                    <i class="fa fa-check"></i> Fee Strcuture Form
                                    <br />
                                    <?php
                                    	endif;
									?>
                                    
								</td>
                               
                                <td>
                                	Total Fee : <?php echo $student['total_fee'];?>
                                    <br />
                                    Total Created Fee : <?php echo $total_fee;?>
                                    <br />
                                    Total Created Council Fee : <?php echo $created_council_fee;?>
                                   
                                    <br />
                                    Total Fee Submitted : <span class="bold"><?php echo $total_fee_submitted;?></span>
                                   
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
                                    	foreach($payments as $key=>$payment)
										{
											if($payment['paid']==0 && $key <5 )
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
                                	<?php
									
											$admissiondate = date_create($student['entry_date']);
                                            $paid_date = date_create(date('Y-m-d'));
											$diff=date_diff($admissiondate,$paid_date);
									
									
									
                                    	echo 'Required Amount : '.$incentive[0]['min_fee_amount']. ' in ' .$incentive[0]['with_in_days']. ' Days';
                                    	echo '<br />';
                                    	echo '<p style="font-weight:bold;"> Received Amount : '.$total_fee_submitted . ' Remaining Days : '.($incentive[0]['with_in_days']-$diff->days.'</p>');
									?>
                                </td>
								<td>
                                	<?php $studentf = $this->db->get_where('students', array('student_id'=>$student['student_id']))->row();
									echo $studentf->add_by?>
                                </td>
								<td>
                                	<?php $feerule = $this->db->get_where('fee_rules', array('fee_rule_id'=>$studentf->plan_id))->row();
										echo 'As Payment plan : '.$feerule->max_comision.' %';
										echo '<br />';
										echo 'Incentive Slap amount : '.$incamount;
										echo '<br />';
										echo 'Incentive for Student = '.(($feerule->max_comision/100)*$incamount);
									?>
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
    <div class="modal fade" id="insertcomment" tabindex="-1"   data-width="500" >


        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title">Add Comments</h4>
        </div>
        <div class="modal-body">
            <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/fees/comment">
                <div class="form-body">


                    <div class="form-group">
                        <label class="col-md-6 control-label">Select Comment</label>
                        <div class="col-md-6">

                            <select class="form-control comment_box comment?>" name="comment" id="comment" >

                                <option value="">Select Comment</option>
                                <option value="Call Not Attended">Call Not Attended</option>
                                <option value="Will Pay On">Will Pay On</option>
                                <option value="Paid">Cell Off</option>
                                <option value="Struck of now">Struck of now</option>

                            </select>

                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-6 control-label">Description <span class="required">*</span></label>
                        <div class="col-md-6">

                            <textarea class="form-control remarks" rows="3" name="description" required></textarea>

                        </div>
                    </div>


                    <div class="form-group" id="datesel">
                    <label class="col-md-6 control-label">Next Due Date</label>
                        <div class="col-md-6">
                            <div class="input-group input-small date date-picker" data-date="" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                        <input type="text" name="selected_date" class="form-control selected-date" value="" readonly>
                                        <span class="input-group-btn">
                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                        </span>
                            </div>
                        </div>

                    </div>

                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-offset-3 col-md-9">

                            <input type="hidden" id="fee_id" name="fee_id" value= $data-id />
                            <button type="submit" id="" class="btn red">Add Comment</button>

                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn default close_button" data-dismiss="modal">Close</button>
        </div>


    </div>