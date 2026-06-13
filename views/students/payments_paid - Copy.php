<?php
	$myAccess = checkUserAccess();
?>	
<style>
	.btn{
		margin-bottom:10px;
	}
</style>
	<?php
    	$student_fees = $this->db->get_where('students', array('student_id'=>$this->uri->segment(3)))->result_array();
		$student_fee = $student_fees[0]['total_fee'];
		$total_fee = $this->db->get_where('classes',array('class_id'=>$student_fees[0]['class_id']))->row()->class_fee;
	?>
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
				<div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
					<div class="dashboard-stat blue-madison">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php echo $total_fee;?>
							</div>
							<div class="desc">
								 Total Fees
							</div>
						</div>
						<!--<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>-->
					</div>
				</div>
				<div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
					<div class="dashboard-stat blue-madison">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php echo $student_fee;?>
							</div>
							<div class="desc">
								 Student Fees
							</div>
						</div>
					</div>
				</div>
                <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
					<div class="dashboard-stat red">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php echo $total_fee-$student_fee;?>
							</div>
							<div class="desc">
								 Discount
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
					<div class="dashboard-stat green">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php echo $discount[0]['total_paid'];?>
							</div>
							<div class="desc">
								 Fee Created
							</div>
						</div>
						<!--<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>-->
					</div>
				</div>
				<div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
					<div class="dashboard-stat red">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php echo ($student_fee-$discount[0]['total_paid']);?>
							</div>
							<div class="desc">
								 Fee Not Created
							</div>
						</div>
						<!--<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>-->
					</div>
				</div>
                <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
					<div class="dashboard-stat yellow">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php echo $fee_should_pay[0]['fee_should_pay'];?>
							</div>
							<div class="desc">
								 Fee Should Pay
							</div>
						</div>
						<!--<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>-->
					</div>
				</div>
                <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
					<div class="dashboard-stat green">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php echo $paid_fee[0]['paid_fee'];?>
							</div>
							<div class="desc">
								 Fee Paid
							</div>
						</div>
						<!--<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>-->
					</div>
				</div>
				<div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
					<div class="dashboard-stat purple">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php echo $fee_should_pay[0]['fee_should_pay']-$paid_fee[0]['paid_fee'];?>
							</div>
							<div class="desc">
								 Remaining fee till date
							</div>
						</div>
						<!--<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>-->
					</div>
				</div>
				<div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
					<div class="dashboard-stat purple">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php echo $remaining_fee[0]['remaining_fee'];?>
							</div>
							<div class="desc">
								 Total Remaining Fee
							</div>
						</div>
						<!--<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>-->
					</div>
				</div>
				<div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
					<div class="dashboard-stat purple">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php 
									$percent = (($paid_fee[0]['paid_fee']/$student_fee)*100);
									echo round($percent);
								 ?>%
							</div>
							<div class="desc">
								 Fee Paid
							</div>
						</div>
						<!--<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>-->
					</div>
				</div>
				<div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
					<div class="dashboard-stat purple">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								 0
							</div>
							<div class="desc">
								 Total Fine
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
					<div class="dashboard-stat purple">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								 0
							</div>
							<div class="desc">
								 fine should pay till date
							</div>
						</div>
						<!--<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>-->
					</div>
				</div>
				<div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
					<div class="dashboard-stat purple">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								 0
							</div>
							<div class="desc">
								 Fine Paid
							</div>
						</div>
						<!--<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>-->
					</div>
				</div>
				<div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
					<div class="dashboard-stat purple">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								 0
							</div>
							<div class="desc">
								 remaining fine till date
							</div>
						</div>
						<!--<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>-->
					</div>
				</div>
				<div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
					<div class="dashboard-stat purple">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								 0
							</div>
							<div class="desc">
								 Total Remaining Fine
							</div>
						</div>
						<!--<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>-->
					</div>
				</div>
                <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
					<div class="dashboard-stat red">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="details-text" style="color:#fff; font-size:16px; text-align:center">
								<br />
								No. of CF : <?php echo count($this->db->get_where('payments',array('student_id'=>$this->uri->segment(3),'payment_plan'=>'consulation fee'))->result_array());?>
								<br />
								Total CF : <?php echo $consulation_fee[0]['consulation_fee'];?>
							</div>
						</div>
						<!--<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>-->
					</div>
				</div>
                <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
					<div class="dashboard-stat blue">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="details-text" style="color:#fff; font-size:16px; text-align:center">
								<br />
								CF should pay : <?php echo $consulation_fee_should_pay[0]['consulation_fee_should_pay'];?>
								<br />
								CF Paid : <?php if($consulation_fee_paid[0]['consulation_fee_paid']==''){echo '0';}else{echo $consulation_fee_paid[0]['consulation_fee_paid'];}?>
							</div>
						</div>
						<!--<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>-->
					</div>
				</div>
				<div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
					<div class="dashboard-stat blue">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="details-text" style="color:#fff; font-size:16px; text-align:center">
								<br />
								CF rem.. till date : <?php echo $consulation_fee_should_pay[0]['consulation_fee_should_pay']-$consulation_fee_paid[0]['consulation_fee_paid'];?>
								<br />
								total CF rem.. : <?php echo $consulation_fee[0]['consulation_fee']-$consulation_fee_paid[0]['consulation_fee_paid'];?>
							</div>
							<!--<div class="number">
								 <?php echo $consulation_fee_paid[0]['consulation_fee_paid'];?>
							</div>
							<div class="desc">
								 Consulation Fee Paid
							</div>-->
						</div>
						<!--<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>-->
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
            <?php if(@$this->session->userdata('error')):?>
                <div class="alert alert-danger">
                    <button class="close" data-close="alert"></button>
                    <span>
                    <?php echo $this->session->userdata('error');?> </span>
                </div>
            <?php endif;?>
            <?php
				if($student[0]['status']==0):
			?>
            <div class="row">
            	<div class="col-md-12">
                	<p class="blink_me" style="font-weight:bold;font-size:28px;color:#F00;text-align:center">DELETED STUDENT</p>
                </div>
            </div>
            <?php
            	endif;
			?>
			<div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-money"></i> Student's Info (<?php echo $student[0]['first_name'].' '.$student[0]['last_name'].' '.$student[0]['roll_no']; ?>) Payment. Cell # <?php echo $student[0]['mobile'];?> - <?php echo $student[0]['emergency_no'];?>
							</div>
						</div>
						<div class="portlet-body">
							<table  class="table table-striped table-bordered table-hover" id="sample_3">
							<thead>
							<tr>
                                <th>
									 Sr #
								</th>
								<th>
									Payment Details &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								</th>
								<th>
									 Dead Line
								</th>
								<th>
									 Fee Type
								</th>
								<th>
									 Paid Status
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								</th>
                                <th>
                                	Due Dates Comments
                                </th>
								<th>
                                	Fee Edit Comments
                                </th>
                                <th>
                                	Fee Due Comments
                                </th>
								<th>
                                	System Comments
                                </th>
                                <th>
                                	Status &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </th>
								<th>
                                	Action
                                </th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=1;
								foreach($payments as $payment):
							?>
                            <tr class="odd gradeX" <?php if($payment['amount'] > $payment['actual_amount'] && $payment['paid']==1){echo 'style="background-color:#F2DEDE";';}?>>
                                <td>
                                	<?php echo $i;?>
                                </td>
								<td>
									Challan # : <?php echo $payment['challan_no'];?>
									<strong>Installment Amount : <?php echo $payment['amount'];?></strong>
									<br />
									Previous Installment Amount : <?php echo $payment['remaining_installment_amount'];?>
									<br />
									Previous Fine Amount : <?php echo $payment['extra_amount'];?>
									<br />
									<hr />
									Installment Status : <?php if($payment['paid']==1){echo 'Paid';}else{echo 'Unpaid';}?>
									<br />
									<?php
										if($payment['paid']==1):
											$challan_date = date_create($payment['dead_line']);
											$paid_date = date_create($payment['paid_date']);
											$diff=date_diff($challan_date,$paid_date);
											$difference = $diff->format("%R%a");
											
											if($difference>0)
											{
												if($payment['payment_plan']=='24 Installments')
												{
													$fee_fine = $difference*10;
												}
												else
												{
													$fee_fine = $difference*50;
												}
											}
											else
											{
												$fee_fine = 0;
											}
											
											echo 'Late Fee Fine : '.$fee_fine.'<br />';
										
											echo '<strong>Payable Amount : '.($payment['amount']+$payment['remaining_installment_amount']+$payment['extra_amount']+$fee_fine).'</strong><br />';
											echo '<strong>Paid Amount : '.$payment['actual_amount'].'</strong><br />';
										endif;
									?>
									<?php
										if($payment['paid']==0):
											$challan_date = date_create($payment['dead_line']);
											$today_date = date_create(date('Y-m-d'));
											$diff=date_diff($challan_date,$today_date);
											$difference = $diff->format("%R%a");
											
											if($difference>0)
											{
												if($payment['payment_plan']=='24 Installments')
												{
													$fee_fine = $difference*10;
												}
												else
												{
													$fee_fine = $difference*50;
												}
											}
											else
											{
												$fee_fine = 0;
											}
											if($difference>0)
											{
												echo 'Late Fee Days : '.str_replace('+','',$difference).'<br />';
											}
											echo 'Late Fee Amount : '.$fee_fine.'<br />';
											echo '<strong>Payable Amount : '.($payment['amount']+$payment['remaining_installment_amount']+$payment['extra_amount']+$fee_fine).'</strong>';
										endif;
									?>
								</td>
								<td>
									<?php echo $payment['dead_line'];?>
								</td>
								<td>
                                	<?php 
										echo $payment['payment_comment'];
									?>
                                </td>
								<td>
									<?php
										if($payment['paid']==1):
									?>
										Paid Amount : <?php echo $payment['actual_amount'];?>
										<br />
										Paid Date : <?php echo $payment['paid_date'];?>
										<br />
										Paid Date System : <?php echo $payment['actual_paid_date'];?>
										<br />
										Fee Pay Through : <?php echo $payment['fee_pay_through'];?>
										<br />
										<?php
											if($payment['fee_pay_through']=='bank'):
										?>
										Bank : <?php echo $payment['bank_details'];?>
										<br />
										TID No. : <?php echo $payment['tid_no'];?>
										<br />
										Bank Challan No. : <?php echo $payment['bank_challan_no'];?>
										<br />
										Fee Submission Time : <?php echo $payment['fee_submission_time'];?>
										<br />
										<?php
											endif;
										?>
										<?php
											if($payment['fee_pay_through']=='college' && $payment['fee_submit_type']=='receipt_book'):
										?>
										Pad of : <?php echo $this->db->get_where('campuses',array('campus_id'=>$payment['submitted_fee_campus_id']))->row()->campus_name;?>
										<br />
										Book No. : <?php echo $payment['book_no'];?>
										<br />
										Receipt No. : <?php echo $payment['receipt_no'];?>
										<br />
										<?php
											endif;
										?>
										<?php
											if($payment['fee_pay_through']=='college' && $payment['fee_submit_type']=='computer_challan'):
										?>
										Pay by : Computer Challan
										<br />
										<?php
											endif;
										?>
										<div class="clearfix"></div>
										<br />
										<?php 
											if($payment['scan_challan']=='')
											{
												
											}
											else
											{
												echo '<a href="'.base_url().'uploads/'.$payment['scan_challan'].'" target="_blank" class="btn purple pull-left">See Challan</a>';
											}
											if($payment['fee_submit_type']=='computer_challan')
											{
												echo '<a href="'.site_url().'/students/print_college_challan/'.$payment['id'].'" target="_blank" class="btn blue college_fee_'.$i.'"><i class="fa fa-print"></i> See Challan</a>';
											}
										?>
										<?php 
											if($payment['fine_application']=='' && $payment['paid']==0)
											{
												
											}
											else if($payment['fine_application']!='' && $payment['paid']==1)
											{
												echo '<a href="'.base_url().'uploads/'.$payment['fine_application'].'" target="_blank" class="btn purple pull-right">See Application</a>';
											}
											else
											{
												
											}
										?>
										<div class="clearfix"></div>
									<?php
										endif;
									?>
								</td>
                                <td>
                                	<?php
                                    	$remarks = $this->db->get_where('fees_remarks', array('fee_id'=>$payment['id']))->result_array();
										foreach($remarks as $remark)
										{
											echo 'Paid on Date : '.$remark['paid_on_date'].'<br />';
											echo 'Remarks : '.$remark['comment'].'<br />';
											echo 'Contact By : '.$remark['add_by'];
											echo '<hr />';
										}
									?>
                                </td>
								<td>
                                	<?php
                                    	$update_requests = $this->db->get_where('update_payment_requests', array('id'=>$payment['id']))->result_array();
										foreach($update_requests as $update_request)
										{
											if($update_request['ok_by_admin']==1)
											{
												$status = '<span class="alert-success">Approved</span>';
											}
											else
											{
												$status = '<span class="alert-danger">Pending</span>';
											}
											echo 'Date : '.date('F d,Y',strtotime($update_request['update_date'])).'<br />';
											echo 'Reason : '.$update_request['reason'].'<br />';
											echo 'Clear By : '.$update_request['clear_by'].'<br />';
											echo 'Status : '.$status;
											echo '<hr />';
										}
									?>
                                </td>
                                <td>
                                	<?php
										$fee_remarks = $this->db->get_where('fees_remarks', array('fee_id'=>$payment['id']))->result_array();
										foreach($fee_remarks as $fee_remark)
										{
											echo $fee_remark['comment'];
											echo '<br />';
											echo 'Add By : '.$fee_remark['add_by'].'<br />';
											echo 'Comment Date : '.$fee_remark['date'].'<br />';
											echo 'Next Date : '.$fee_remark['paid_on_date'];
											echo '<hr />';
										}
									?>
                                </td>
                                <td>
									<?php echo $payment['system_comment'];?>
								</td>
                                <td>
                                	<?php 
										echo 'Fee Add By : '.$payment['add_by'].'<br />';
										echo 'Fee Last Edit : '.$payment['last_edit'].'<br />';
										if($payment['paid']==1)
										{
											echo 'Fee Submitted By : '.@$payment['paid_by'].'<br />';
											echo 'Fee Cleared By : '.@$payment['clear_by']; 
										}
									?>
								</td>
                                <td>
                                	<?php
                                    	if($student[0]['status']==1):
									?>
									<?php
                                    	if($payment['paid']==0 ):
									?>
									<button type="button" class="btn green" data-toggle="modal" href="#responsive<?php echo $i;?>"><i class="fa fa-cloud-upload"></i> Actions</button>
                                    <?php
                                        if($payment['payment_plan']!='consulation fee'):
                                    ?>
                                    <button type="button" class="btn red" data-toggle="modal" href="#split_fee_<?php echo $i;?>"><i class="fa fa-code"></i> Split Installment</button>
                                    <div id="split_fee_<?php echo $i;?>" class="modal fade" tabindex="-1" data-width="760">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                                            <h4 class="modal-title">Split Installment</h4>
                                        </div>
                                        <div class="modal-body">
                                            <form action="<?php echo site_url();?>/students/split_payment/<?php echo $this->uri->segment(3);?>" method="post">
                                                <div class="row">
                                                    <div class="alert alert-danger">
                                                        <p class="text-center">You can split maximum 50% of fee amount. You can create new installment within the date of one month.</p>
                                                    </div>
                                                    <div class="col-md-6" style="border-right:1px dotted #CCC;">
                                                        <h2 class="text-center">Current Installment</h2>
                                                        <br />
                                                        <div class="form-group">
                                                            <label class="col-md-4 control-label">Installment Amount <span class="required">*</span></label>
                                                            <div class="col-md-8">
                                                                <input type="number" min="<?php echo $payment['amount']/2;?>" max="<?php echo $payment['amount'];?>" class="form-control input-inline input-small current_amount current_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" data-value="<?php echo $payment['amount'];?>"  name="current_amount" placeholder="Enter student installment amount" value="<?php echo $payment['amount'];?>" required>
                                                                <span class="help-inline"></span>
                                                            </div>
                                                            <div class="clearfix"></div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="col-md-4 control-label">Previous Installment Amount <span class="required">*</span></label>
                                                            <div class="col-md-8">
                                                                <input type="number" min="<?php echo $payment['remaining_installment_amount']/2;?>" max="<?php echo $payment['remaining_installment_amount'];?>" class="form-control input-inline input-small current_remaining_installment_amount current_remaining_installment_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" data-value="<?php echo $payment['remaining_installment_amount'];?>" name="current_remaining_installment_amount" placeholder="Enter student installment amount" value="<?php echo $payment['remaining_installment_amount'];?>" required>
                                                                <span class="help-inline"></span>
                                                            </div>
                                                            <div class="clearfix"></div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="col-md-4 control-label">Previous Fine Amount <span class="required">*</span></label>
                                                            <div class="col-md-8">
                                                                <input type="number" min="<?php echo $payment['extra_amount']/2;?>" max="<?php echo $payment['extra_amount'];?>" class="form-control input-inline input-small current_extra_amount current_extra_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" data-value="<?php echo $payment['extra_amount'];?>" name="current_extra_amount" placeholder="Enter student fine amount" value="<?php echo $payment['extra_amount'];?>" required>
                                                                <span class="help-inline"></span>
                                                            </div>
                                                            <div class="clearfix"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h2 class="text-center">New Installment</h2>
                                                        <br />
                                                        <div class="form-group">
                                                            <label class="col-md-4 control-label">Installment Amount <span class="required">*</span></label>
                                                            <div class="col-md-8">
                                                                <input type="number" max="<?php echo $payment['amount']/2;?>" class="form-control input-inline input-small new_amount new_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" name="new_amount" placeholder="Enter student installment amount" value="0" readonly required />
                                                                <span class="help-inline"></span>
                                                            </div>
                                                            <div class="clearfix"></div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="col-md-4 control-label">Previous Installment Amount <span class="required">*</span></label>
                                                            <div class="col-md-8">
                                                                <input type="number" max="<?php echo $payment['remaining_installment_amount']/2;?>" class="form-control input-inline input-small new_remaining_installment_amount new_remaining_installment_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" name="new_remaining_installment_amount" placeholder="Enter student installment amount" value="0" readonly required />
                                                                <span class="help-inline"></span>
                                                            </div>
                                                            <div class="clearfix"></div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="col-md-4 control-label">Previous Fine Amount <span class="required">*</span></label>
                                                            <div class="col-md-8">
                                                                <input type="number" max="<?php echo $payment['extra_amount']/2;?>" class="form-control input-inline input-small new_extra_amount new_extra_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" name="new_extra_amount" placeholder="Enter student fine amount" value="0" readonly required>
                                                                <span class="help-inline"></span>
                                                            </div>
                                                            <div class="clearfix"></div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="control-label col-md-4">Dead Line <span class="required">*</span></label>
                                                            <div class="col-md-8">
                                                                <div class="input-group input-small date date-picker" data-date="<?php echo $payment['dead_line']?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                                    <input type="text" name="new_dead_line" class="form-control" value="<?php echo $payment['dead_line']?>" readonly>
                                                                    <span class="input-group-btn">
                                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12 text-center">
                                                        <input type="hidden" name="current_dead_line" value="<?php echo $payment['dead_line']?>">
                                                        <input type="hidden" name="current_id" value="<?php echo $payment['id']?>">
                                                        <button type="submit" class="btn red"><i class="fa fa-code"></i> Split Installment</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" data-dismiss="modal" class="btn btn-default">Close</button>
                                        </div>
                                    </div>
                                    <?php
                                        endif;
                                    ?>
                                    <div id="responsive<?php echo $i;?>" class="modal fade" tabindex="-1" data-width="760">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
											<h4 class="modal-title">Fee Submission</h4>
										</div>
										<div class="modal-body">
											<div class="row">
												<div class="col-md-4">
													<strong>Challan #</strong> : <?php echo $payment['challan_no'];?>
												</div>
												<div class="col-md-4">
													<strong>Installment Amount</strong> : <?php echo $payment['amount'];?>
												</div>
												<div class="col-md-4">
													<strong>Dead Line</strong> : <?php echo $payment['dead_line'];?>
												</div>
												<div class="col-md-4">
													<strong>Fee Type</strong> : <?php echo $payment['payment_comment'];?>
												</div>
												<div class="col-md-4">
													<strong>Previous Installment Amount</strong> : <?php echo $payment['remaining_installment_amount'];?>
												</div>
												<div class="col-md-4">
													<strong>Previous Fine Amount</strong> : <?php echo $payment['extra_amount'];?>
												</div>
												<div class="col-md-4">
													<strong>Late Fee Amount</strong> : <?php echo $fee_fine;?>
												</div>
												<div class="col-md-4">
													<strong>Late Fee Days</strong> : <?php echo str_replace('+','',$difference).' Days';?>
												</div>
												<div class="col-md-4">
													<strong>Payable Amount</strong> : <?php echo $payable_amount = ($payment['amount']+$payment['remaining_installment_amount']+$payment['extra_amount']+$fee_fine);?>
												</div>
												
												<div class="clearfix"></div>
												<br />
											</div>
											<div class="row">
												<div class="col-md-12">
													<form action="<?php echo site_url().'/students/paid_payment_action/'.$this->uri->segment(3);?>" method="post" enctype="multipart/form-data">
														<div class="form-group">
															<label class="col-md-4 control-label">Fee Pay Through <span class="required">*</span></label>
															<div class="col-md-8 radio-list">
																<label class="radio-inline">
																<input type="radio" class="submit_in" name="fee_pay_through" id="optionsRadios5" value="bank" checked /> Bank </label>
																<label class="radio-inline">
																<input type="radio"  class="submit_in" name="fee_pay_through" id="optionsRadios4" value="college" /> College </label>
															</div>
														</div>
														<div class="clearfix"></div>
														<br />
														<div class="bank">
															<div class="form-group">
																<label class="col-md-4 control-label">Select Bank <span class="required">*</span></label>
																<div class="col-md-8">
																	<select class="form-control bank_details" name="bank_details" required>
																		<option value="">SELECT BANK</option>
																		<?php 
																			$count = count($account_numbers);
																			for($a=0;$a<$count;$a++):
																		?>
																		<option value="<?php echo $bank_names[$a].' ('.$account_numbers[$a].')';?>"><?php echo $bank_names[$a].' ('.$account_numbers[$a].')';?></option>
																		<?php
																			endfor;
																		?>
																	</select>
																</div>
															</div>
															<div class="clearfix"></div>
															<br />
															<div class="form-group">
																<label class="col-md-4 control-label">TID No.</label>
																<div class="col-md-8">
																	<input type="text" class="form-control input-inline input-medium" name="tid_no" placeholder="Enter TID Number" value="" >
																	<span class="help-inline"></span>
																</div>
															</div>
															<div class="clearfix"></div>
															<br />
															<div class="form-group">
																<label class="col-md-4 control-label">Bank Challan No.</label>
																<div class="col-md-8">
																	<input type="text" class="form-control input-inline input-medium" name="bank_challan_no" placeholder="Enter Bank Challan Number" value="" >
																	<span class="help-inline"></span>
																</div>
															</div>
															<div class="clearfix"></div>
															<br />
															<div class="form-group">
																<label class="col-md-4 control-label">Fee Submission Time</label>
																<div class="col-md-8">
																	<input type="text" class="form-control input-inline input-medium" name="fee_submission_time" placeholder="Enter Fee Submission Time" value="" >
																	<span class="help-inline"></span>
																</div>
															</div>
														</div>
														<div class="clearfix"></div>
														<div class="form-group college" style="display:none;">
															<label class="col-md-4 control-label">Fee Submit Type <span class="required">*</span></label>
															<div class="col-md-8 radio-list">
																<label class="radio-inline">
																<input type="radio" class="pay_by" name="fee_submit_type" id="optionsRadios5" value="computer_challan" checked /> Pay By Computer Challan </label>
																
																<label class="radio-inline">
																<input type="radio" class="pay_by" name="fee_submit_type" id="optionsRadios4" value="receipt_book" /> Pay By Receipt Book </label>
															</div>
														</div>
														<div class="clearfix"></div>
														<div class="receipt_book" style="display:none;">
															<div class="clearfix"></div>
															<br />
															<div class="form-group">
																<label class="col-md-4 control-label">Pad of <span class="required">*</span></label>
																<div class="col-md-8">
																	<select class="form-control submitted_fee_campus_id" name="submitted_fee_campus_id">
																		<option value="">SELECT CAMPUS</option>
																		<?php 
																			foreach($campuses as $campus):
																		?>
																		<option value="<?php echo $campus['campus_id'];?>" <?php if($campus['campus_id']==$this->session->userdata('user_campus_id')){echo 'selected';}?>><?php echo $campus['campus_name'];?></option>
																		<?php
																			endforeach;
																		?>
																	</select>
																</div>
															</div>
															<div class="clearfix"></div>
															<br />
															<div class="form-group">
																<label class="col-md-4 control-label">Book No. <span class="required">*</span></label>
																<div class="col-md-8">
																	<input type="number" min="1" class="form-control input-inline input-medium book_no" name="book_no" placeholder="Enter Receipt Book Number" value="">
																	<span class="help-inline"></span>
																</div>
															</div>
															<div class="clearfix"></div>
															<br />
															<div class="form-group">
																<label class="col-md-4 control-label">Receipt No. <span class="required">*</span></label>
																<div class="col-md-8">
																	<input type="number" min="1" class="form-control input-inline input-medium receipt_no" name="receipt_no" placeholder="Enter Receipt Number" value="">
																	<span class="help-inline"></span>
																</div>
															</div>
															<div class="clearfix"></div>
														</div>
														<div class="clearfix"></div>
														<br />
														<div class="form-group challan">
															<label class="col-md-4 control-label">Challan Image <span class="required">*</span></label>
															<div class="col-md-8">
																<input class="scan_challan" type="file" name="scan_challan" value="" required />
																<span class="help-inline"></span>
															</div>
														</div>
														<div class="clearfix"></div>
														<div class="form-group application">
															<label class="col-md-4 control-label">Application Image</label>
															<div class="col-md-8">
																<input type="file" name="fine_application" value="" />
																<span class="help-inline"></span>
															</div>
														</div>
														<div class="clearfix"></div>
														<div class="form-group">
															<label class="col-md-4 control-label">Paid Date <span class="required">*</span></label>
															<div class="col-md-8">
																<div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d');?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
																	<input type="text" name="paid_date" class="form-control" value="<?php echo date('Y-m-d');?>" required readonly>
																	<span class="input-group-btn">
																	<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
																	</span>
																</div>
															</div>
														</div>
                                                        <div class="form-group">
                                                            <label class="col-md-4 control-label">Installment Amount <span class="required">*</span></label>
                                                            <div class="col-md-8">
                                                                <input type="number" min="1" class="form-control input-inline input-medium amount amount_<?php echo $i;?>" data-number="<?php echo $i;?>" name="amount" placeholder="Enter student installment amount" value="<?php echo $payment['amount'];?>" readonly required>
                                                                <span class="help-inline"></span>
                                                            </div>
                                                            <div class="clearfix"></div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="col-md-4 control-label">Previous Installment Amount <span class="required">*</span></label>
                                                            <div class="col-md-8">
                                                                <input type="number" min="0" class="form-control input-inline input-medium remaining_installment_amount remaining_installment_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" name="remaining_installment_amount" placeholder="Enter student previous installment amount" value="<?php echo $payment['remaining_installment_amount'];?>" required>
                                                                <span class="help-inline"></span>
                                                            </div>
                                                            <div class="clearfix"></div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="col-md-4 control-label">Action <span class="required">*</span></label>
                                                            <div class="col-md-8 radio-list">
                                                                <label class="radio-inline">
                                                                    <input type="radio" class="prev_installment_status" name="prev_installment_status" id="optionsRadios1" value="pay" checked /> Pay </label>
                                                                <label class="radio-inline">
                                                                    <input type="radio"  class="prev_installment_status" name="prev_installment_status" id="optionsRadios3" value="shift" /> Shift </label>
                                                                <label class="radio-inline">
                                                                    <input type="radio"  class="prev_installment_status" name="prev_installment_status" id="optionsRadios3" value="new" /> New Installment </label>
                                                            </div>
                                                            <div class="clearfix"></div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="col-md-4 control-label">Previous Fine Amount <span class="required">*</span></label>
                                                            <div class="col-md-8">
                                                                <input type="number" min="0" class="form-control input-inline input-medium previous_extra_amount previous_extra_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" name="extra_amount" placeholder="Enter student previous fine amount" value="<?php echo $payment['extra_amount'];?>" required>
                                                                <span class="help-inline"></span>
                                                            </div>
                                                            <div class="clearfix"></div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="col-md-4 control-label">Action <span class="required">*</span></label>
                                                            <div class="col-md-8 radio-list">
                                                                <label class="radio-inline">
                                                                    <input type="radio" class="prev_fine_status" name="prev_fine_status" id="optionsRadios1" value="pay" checked /> Pay </label>
                                                                <label class="radio-inline">
                                                                    <input type="radio"  class="prev_fine_status" name="prev_fine_status" id="optionsRadios2" value="remove" /> Remove </label>
                                                                <label class="radio-inline">
                                                                    <input type="radio"  class="prev_fine_status" name="prev_fine_status" id="optionsRadios3" value="shift" /> Shift </label>
                                                                <label class="radio-inline">
                                                                    <input type="radio"  class="prev_fine_status" name="prev_fine_status" id="optionsRadios4" value="new" /> New Installment </label>
                                                            </div>
                                                            <div class="clearfix"></div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="col-md-4 control-label">Late Fee Fine Amount <span class="required">*</span></label>
                                                            <div class="col-md-8">
                                                                <input type="number" min="0" class="form-control input-inline input-medium fine_amount fine_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" name="extra_amount" placeholder="Enter student previous fine amount" value="<?php echo $fee_fine;?>" required>
                                                                <span class="help-inline"></span>
                                                            </div>
                                                            <div class="clearfix"></div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="col-md-4 control-label">Action <span class="required">*</span></label>
                                                            <div class="col-md-8 radio-list">
                                                                <label class="radio-inline">
                                                                    <input type="radio" class="late_fee_fine_status" name="late_fee_fine_status" id="optionsRadios1" value="pay" checked /> Pay </label>
                                                                <label class="radio-inline">
                                                                    <input type="radio"  class="late_fee_fine_status" name="late_fee_fine_status" id="optionsRadios2" value="remove" /> Remove </label>
                                                                <label class="radio-inline">
                                                                    <input type="radio"  class="late_fee_fine_status" name="late_fee_fine_status" id="optionsRadios3" value="shift" /> Shift </label>
                                                                <label class="radio-inline">
                                                                    <input type="radio"  class="late_fee_fine_status" name="late_fee_fine_status" id="optionsRadios4" value="new" /> New Installment </label>
                                                            </div>
                                                            <div class="clearfix"></div>
                                                        </div>
                                                        <div class="form-group">
															<label class="col-md-4 control-label">Paid Amount <span class="required">*</span></label>
															<div class="col-md-8">
																<input type="number" min="1" class="form-control input-inline input-medium actual_amount actual_amount_<?php echo $i?>" name="actual_amount" placeholder="Enter student paid amount" value="<?php echo $payable_amount;?>" readonly required>
																<span class="help-inline"></span>
															</div>
                                                            <div class="clearfix"></div>
														</div>
														<div class="col-md-4"></div>
														<div class="col-md-8">
															<br />
															<input type="hidden" name="last_edit" value="<?php echo $this->session->userdata('name');?>" />
															<input type="hidden" name="fee_amount" value="<?php echo $payment['amount']+$payment['extra_amount'];?>" />
															<input type="hidden" name="id" value="<?php echo $payment['id'];?>" />
															<input type="hidden" name="dead_line" value="<?php echo $payment['dead_line'];?>" />
															<input type="hidden" name="payment_plan" value="<?php echo $payment['payment_plan'];?>" />
															<input type="hidden" name="college_fee" class="hidden_college_fee" value="0" />
															<button class="btn green">Pay Fee</button>
														</div>
													</form>
												</div>
												<div class="clearfix"></div>
											</div>
										</div>
										<div class="modal-footer">
											<button type="button" data-dismiss="modal" class="btn btn-default">Close</button>
										</div>
									</div>
                                    <a href="<?php echo site_url().'/students/print_challan/'.$payment['id'];?>" target="_blank" class="btn purple bank_fee_<?php echo $i;?>"><i class="fa fa-print"></i> Bank Challan</a>
                                    <a href="<?php echo site_url().'/students/print_college_challan/'.$payment['id'];?>" target="_blank" class="btn blue college_fee_<?php echo $i;?>" style="display:none;"><i class="fa fa-print"></i> College Challan</a>
                                    <?php
                                    	endif;
									?>
                                    <?php
                                    	if(@$myAccess[0]['student_payment_edit']==1 || $this->session->userdata('role')=='Admin')
										{
											echo '<a href="'.site_url().'/students/edit_payment/'.$payment['id'].'" class="btn yellow"><i class="fa fa-edit"></i> Edit</a>';
										}
										if(@$myAccess[0]['student_payment_delete']==1 || $this->session->userdata('role')=='Admin')
										{
											//echo '<a href="'.site_url().'/students/delete_payment/'.$payment['id'].'/'.$this->uri->segment(3).'" onclick="return confirm(\'Are you sure you want to delete this Transaction?\')" class="btn red"><i class="fa fa-trash"></i> Delete</a>';
										}
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
            <div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-plus"></i> Add Extra Fee
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/students/add_extra_fee/<?php echo $this->uri->segment(3);?>" enctype="multipart/form-data">
								<div class="form-body">
									<div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                            	<label class="col-md-4 control-label">Extra Fee</label>
                                            	<div class="col-md-8">
                                            		<input type="text" class="form-control input-inline input-medium" name="extra_fee" value="" required/>
                                            	</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                            	<label class="col-md-4 control-label">Dead Line</label>
                                            	<div class="col-md-8">
                                            		<div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d');?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                            			<input type="text" name="extra_fee_dead_line" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
                                            			<span class="input-group-btn">
                                            			<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                            			</span>
                                                    </div>
                                            	</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                            	<label class="col-md-4 control-label">Purpose</label>
                                            	<div class="col-md-8">
                                            		<input type="text" class="form-control input-inline input-medium" name="payment_comment" value="" required />
                                            	</div>
                                            </div>
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Add Extra Fee</button>
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
            
            
            <div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-plus"></i> Add Consultation Fee
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/students/add_extra_consulation_fee/<?php echo $this->uri->segment(3);?>" enctype="multipart/form-data">
								<div class="form-body">
									<div class="row">
                                    	<div class="col-md-12">
                                        	<h2>Punjab Consulation Fee</h2>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                            	<label class="col-md-4 control-label">Extra Year Consulation Fee</label>
                                            	<div class="col-md-8">
                                            		<input type="text" class="form-control input-inline input-medium" name="consulation_fee_1" value="" required/>
                                            	</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                            	<label class="col-md-4 control-label">Dead Line</label>
                                            	<div class="col-md-8">
                                            		<div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d');?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                            			<input type="text" name="consulation_dead_line_1" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
                                            			<span class="input-group-btn">
                                            			<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                            			</span>
                                                    </div>
                                            	</div>
                                            </div>
                                        </div>
										<div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-4 control-label">Class</label>
                                                <div class="col-md-8 radio-list">
                                                    <label class="radio-inline">
                                                    <input type="radio" name="class" id="optionsRadios4" value="1st Year" checked> First Year </label>
                                                    <label class="radio-inline">
                                                    <input type="radio" name="class" id="optionsRadios5" value="2nd Year"> Second Year </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                            	<label class="col-md-4 control-label">Council Exam No.</label>
                                            	<div class="col-md-8">
                                            		<input type="text" class="form-control input-inline input-medium" name="exam_no" value="" required />
                                            	</div>
                                            </div>
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Add Consultation Fee</button>
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
		</div>
	</div>
	<!-- END CONTENT -->