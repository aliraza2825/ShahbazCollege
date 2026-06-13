<?php
	$myAccess = checkUserAccess();
?>
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<h3 class="page-title">
			Fee Reversal Requests
			</h3>
			<!-- END PAGE HEADER-->
			<!-- BEGIN DASHBOARD STATS -->
            
			<!-- END DASHBOARD STATS -->
			<div class="clearfix">
			</div>
            <?php
            	if(@$this->session->flashdata('message')):
			?>
            <div class="alert alert-success">
            	<p><?php echo $this->session->flashdata('message');?></p>
            </div>
            <?php 
				endif;
			?>
            <?php
            	if(@$this->session->flashdata('error')):
			?>
            <div class="alert alert-danger">
            	<p><?php echo $this->session->flashdata('error');?></p>
            </div>
            <?php 
				endif;
			?>
            <div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-money"></i> Fee Reversal Pending Requests
							</div>
						</div>
                        <div class="portlet-body">
							<table class="table table-bordered table-hover" id="sample_11">
							<thead>
								<tr>
									<th class="hidden">
										Hidden
									</th>
									<th>
										Date
									</th>
									<th>
										Reversal Amount
									</th>
									<th>
										Reversal Reason
									</th>
									<th>
										Reversal Application
									</th>
									<th>
										Fee Deatils
									</th>
									<th>
										Status
									</th>
									<th>
										Action
									</th>
								</tr>
							</thead>
							<tbody>
								<?php
									$i=1;
									foreach($fee_reversal_requests as $fee_reversal_request):
								?>
								<tr>
									<td class="hidden">
										<?php echo $i++;?>
									</td>
									<td>
										<?php echo $fee_reversal_request['created_at'];?>
									</td>
									<td>
										<?php echo 'Rs '.$fee_reversal_request['reversal_amount'];?>
									</td>
									<td>
										<?php echo $fee_reversal_request['reversal_reason'];?>
									</td>
									<td>
										<?php 
											if($fee_reversal_request['online_reversal_application']==''):
										?>
										<a href="<?php echo base_url().'uploads/'.$fee_reversal_request['reversal_application'];?>" class="btn btn-default" target="_blank">Image</a>
										<?php
											else:
										?>
										<a href="<?php echo str_replace($bucket_address,$cloudfront_address,$fee_reversal_request['online_reversal_application']);?>" class="btn btn-default" target="_blank">Image</a>
										<?php
											endif;
										?>
									</td>
									<td>
										<a href="#" class="btn btn-primary view_fee_details" data-payment-id="<?php echo $fee_reversal_request['payment_id'];?>">View Fee Details</a>
									</td>
									<td>
										<?php
											if($fee_reversal_request['approve_status']==0)
											{
												echo 'Pending';
											}
											else
											{
												echo 'Approved';
											}
										?>
									</td>
									<td>
										<a href="<?php echo site_url().'/dashboard/update_fee_reversal_request/'.$fee_reversal_request['payments_reversal_request_id'].'/1';?>" class="btn green">Approve</a>
										<a href="<?php echo site_url().'/dashboard/delete_fee_reversal_request/'.$fee_reversal_request['payments_reversal_request_id'];?>" class="btn red">Reject</a>
									</td>
								</tr>
								<?php
									endforeach;
								?>
							</tbody>
							</table>
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
								<i class="fa fa-money"></i> Fee Reversal Approved Requests
							</div>
						</div>
                        <div class="portlet-body">
							<div class="row">
								<form action="<?php echo site_url();?>/dashboard/fee_reversal_requests" method="post">
									<div class="col-md-5">
										<div class="form-group">
											<label class="control-label col-md-3">From Date</label>
											<div class="col-md-3">
												<div class="input-group input-medium date date-picker" data-date="<?php echo $from_date;?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
													<input type="text" name="from_date" class="form-control" value="<?php echo $from_date;?>" readonly>
													<span class="input-group-btn">
													<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
													</span>
												</div>
											</div>
										</div>
									</div>
									<div class="col-md-5">
										<div class="form-group">
											<label class="control-label col-md-3">To Date</label>
											<div class="col-md-3">
												<div class="input-group input-medium date date-picker" data-date="<?php echo $to_date;?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
													<input type="text" name="to_date" class="form-control" value="<?php echo $to_date;?>" readonly>
													<span class="input-group-btn">
													<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
													</span>
												</div>
											</div>
										</div>
									</div>
									<div class="col-md-2">
										<button type="submit" class="btn green">Search</button>
									</div>
								</form>
								<br /><br /><br /><br />
							</div>
							<table class="table table-bordered table-hover">
							<thead>
								<tr>
									<th class="hidden">
										Hidden
									</th>
									<th>
										Date
									</th>
									<th>
										Amount Reversed
									</th>
									<th>
										Reversal Reason
									</th>
									<th>
										Reversal Proof
									</th>
									<th>
										Payment Details
									</th>
									<th>
										Paid By
									</th>
								</tr>
							</thead>
							<tbody>
								<?php
									$i=1;
									foreach($fee_reversal_approved_requests as $fee_reversal_request):
								?>
								<tr>
									<td class="hidden">
										<?php echo $i++;?>
									</td>
									<td>
										<?php echo $fee_reversal_request['created_at'];?>
									</td>
									<td>
										<?php echo 'Rs '.$fee_reversal_request['reversal_amount'];?>
									</td>
									<td>
										<?php echo $fee_reversal_request['reversal_reason'];?>
										<?php 
											if($fee_reversal_request['online_reversal_application']==''):
										?>
										<a href="<?php echo base_url().'uploads/'.$fee_reversal_request['reversal_application'];?>" class="btn btn-default" target="_blank">Image</a>
										<?php
											else:
										?>
										<a href="<?php echo str_replace($bucket_address,$cloudfront_address,$fee_reversal_request['online_reversal_application']);?>" class="btn btn-default" target="_blank">Image</a>
										<?php
											endif;
										?>
									</td>
									<td>
										<?php 
											if($fee_reversal_request['online_proof_image']==''):
										?>
										<a href="<?php echo base_url().'uploads/'.$fee_reversal_request['proof_image'];?>" class="btn btn-default" target="_blank">Image</a>
										<?php
											else:
										?>
										<a href="<?php echo str_replace($bucket_address,$cloudfront_address,$fee_reversal_request['online_proof_image']);?>" class="btn btn-default" target="_blank">Image</a>
										<?php
											endif;
										?>
									</td>
									<td>
										<a href="#" class="btn btn-primary view_fee_details" data-payment-id="<?php echo $fee_reversal_request['payment_id'];?>">View Fee Details</a>
									</td>
									<td>
										<?php echo $fee_reversal_request['paid_by'];?>
									</td>
								</tr>
								<?php
									endforeach;
								?>
							</tbody>
							</table>
						</div>
                        
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
		</div>
	</div>
	<!-- END CONTENT --> 
</div>
<!-- END CONTAINER -->
<div id="fee_detail_modal" class="modal fade" tabindex="-1" data-width="760">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Fee Details</h4>
    </div>
    <div class="modal-body">
        <div class="row fee_data">
            
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default">Close</button>
    </div>
</div>