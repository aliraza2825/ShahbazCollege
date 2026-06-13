<?php
	$myAccess = checkUserAccess();
?>	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			All Classes <small>Here you can find all classes</small>
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

			<div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> Check Requests
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/inventory/all_purchase_requests">
								<div class="form-body">
									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label class="control-label col-md-3">From Date <span class="required">*</span></label>
												<div class="col-md-3">
													<div class="input-group input-medium date date-picker" data-date="<?php echo $from_date;?>" data-date-format="yyyy-mm-dd" <?php if(@$myAccess[0]['expense_no_of_days']!=1 && $this->session->userdata('role') != 'Admin'): ?>   data-date-end-date="0d" <?php endif;?> data-date-viewmode="years">
														<input type="text" name="from_date" class="form-control" value="<?php echo $from_date;?>" readonly>
														<span class="input-group-btn">
															<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
															</span>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label class="control-label col-md-3">To Date <span class="required">*</span></label>
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
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Check Purchase Requests</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>
        	<!-- END PAGE CONTENT-->


			<div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> All Purchase Requests / Add Qoutations
							</div>
						</div>
						<div class="portlet-body">
							<table class="table table-striped table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
								<th class="hidden">
                                	 hidden
                                </th>
                                <th>
									 PR no.
								</th>
								<th>
									 Request Date
								</th>
								<th>
									 Title
								</th>
								<th>
									 Request By
								</th>
								<th>
									 Status
								</th>
								<th>
									 Approve By
								</th>
								<th>
									 Approve Date
								</th>
								<th>
									Vendor
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($purchase_requests as $purchase_request):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $purchase_request['purchase_no']?>
								</td>
								<td>
									 <?php 
									 	echo date('F d,Y h:i:s A',strtotime($purchase_request['created_at']));
									 ?>
								</td>
								<td>
									<?php echo $purchase_request['title']?>
								</td>
                                <td>
									<?php echo $purchase_request['add_by']?>
								</td>
								<td>
									<?php
										if($purchase_request['status']==0)
										{
											echo '<button class="btn yellow">Pending</button>';
										}
										if($purchase_request['status']==1)
										{
											echo '<button class="btn green">Approved</button>';
										}
										if($purchase_request['status']==2)
										{
											echo '<button class="btn red">Rejected</button>';
										}
									?>
								</td>
								<td>
									<?php echo $purchase_request['approve_by']?>
								</td>
								<td>
									 <?php 
									 	if($purchase_request['status']==1):
									 	echo date('F d,Y h:i:s A',strtotime($purchase_request['approved_at']));
										endif;
									 ?>
								</td>
								<td>
									<?php
										if($purchase_request['status']==1)
										{
											if($myAccess[0]['add_qoutation']==1 || $this->session->userdata('role')=='Admin')
											{
												echo '<a class="btn purple" href="'.site_url().'/inventory/add_qoutation/'.$purchase_request['purchase_no'].'">Add Qoutation</a>';
											}
										}
									?>
								</td>
								<td>
									<?php
										if($myAccess[0]['edit_purchase_request']==1 || $this->session->userdata('role')=='Admin'):
									?>
                                    <a href="<?php echo site_url().'/inventory/edit_purchase_request/'.$purchase_request['purchase_no'];?>" title="Approve/Reject" class="btn blue"><i class="fa fa-eye"></i></a>
									<?php
										endif;
									?>
									<?php
										if($myAccess[0]['delete_purchase_request']==1 || $this->session->userdata('role')=='Admin'):
									?>
                                    <a onclick="return confirm('Are you sure you want to delete this Purchase Request?')" href="<?php echo site_url().'/inventory/delete_purchase_request/'.$purchase_request['purchase_request_id'];?>" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>
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