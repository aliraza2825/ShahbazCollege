
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Edit Profile <small>You can edit your profile here</small>
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
								<i class="fa fa-calendar"></i> Check Approved Requests
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/inventory/manage_gin">
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
                                            <button type="submit" class="btn green">Check Requests</button>
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
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> All Approved Requests
							</div>
						</div>
						<div class="portlet-body">
							<table class="table table-striped table-bordered table-hover" id="sample_3">
							<thead>
							<tr>
								<th class="hidden">
                                	 hidden
                                </th>
								<th class="hidden">
                                	 hidden
                                </th>
                                <th>
									 ID
								</th>
								<th>
									 Request ID
								</th>
								<th>
									 Request By
								</th>
                                <th>
									 Product Details
								</th>
								<th>
									 Required on Place
								</th>
								<th>
									 Approve By
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($requests as $request):
							?>
                            <tr class="odd gradeX">
								<form method="post" action="<?php echo site_url();?>/inventory/issue_gin/<?php echo $request['require_product_request_id']?>">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $request['require_product_request_id']?>
								</td>
                                <td>
									<?php echo $request['request_no']?>
								</td>
								<td>
									<?php echo $request['user_name']?>
								</td>
								<td>
									<?php
										echo $request['product_name'];
										echo '<br />';
										echo 'Quantity: '.$request['quantity'];
									?>
								</td>
								<td>
									<?php
										echo $request['campus_name'];
										echo '<br />';
										echo $request['room_name'];
										echo '<br />';
										echo $request['subroom_name'];
									?>
								</td>
								<td>
                                    <?php
										$user = $this->db->get_where('users',array('user_id'=>$request['approved_by']))->result_array();
										echo '<strong>Approved By:</strong> '.$user[0]['first_name'].' '.$user[0]['last_name'];
										echo '<br />';
										echo '<strong>Comment:</strong> '.$request['approval_comment'];
									?>
								</td>
								<td>
                                    <?php
										if($request['gin']=='0'):
									?>
									<select data-row-id="<?php echo $i;?>" name="campus_id" class="form-control input-inline input-large select2 product_issue_request_campus" required>
										<option value="">SELECT CAMPUS</option>
										<?php
											foreach($campuses as $campus):
										?>
										<option data-campus-id="<?php echo $campus['campus_id'];?>" value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name'];?></option>
										<?php
											endforeach;
										?>
									</select>
									<br />
									<select name="room_id" class="form-control input-inline input-large select2 product_issue_request_rooms_<?php echo $i;?>" required>
										
									</select>
									<br />
									<textarea class="form-control input-large" name="gin_comment" required></textarea>
									<button type="submit" class="btn green">Submit</button>
									<?php
										else:
											$user = $this->db->get_where('users',array('user_id'=>$request['approved_by']))->result_array();
											echo '<strong>Approved By:</strong> '.$user[0]['first_name'].' '.$user[0]['last_name'];
											echo '<br />';
											echo '<strong>Comment:</strong> '.$request['approval_comment'];
										endif;
									?>
								</td>
								</form>
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

			<div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-search"></i> Search Available Product
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="#">
								<div class="form-body">
                                    <div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label class="control-label col-md-3">Campus <span class="required">*</span></label>
												<div class="col-md-6">
													<select name="campus_id" class="form-control input-inline input-large select2 search_campus_id" required>
														<option value="">SELECT CAMPUS</option>
														<?php
															foreach($campuses as $campus):
														?>
														<option data-campus-id="<?php echo $campus['campus_id'];?>" value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name'];?></option>
														<?php
															endforeach;
														?>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label class="control-label col-md-3">Product <span class="required">*</span></label>
												<div class="col-md-6">
													<select name="product_name_id" class="form-control input-inline input-large select2 search_product_name_id" required>
														<option value="">SELECT PRODUCT</option>
														<?php
															foreach($product_names as $product_name):
														?>
														<option data-product-name-id="<?php echo $product_name['product_name_id'];?>" value="<?php echo $product_name['product_name_id'];?>"><?php echo $product_name['product_name'];?></option>
														<?php
															endforeach;
														?>
													</select>
												</div>
											</div>
										</div>

										<div class="col-md-12">
											<table class="table">
												<thead>
													<tr>
														<th>Campus</th>
														<th>Product</th>
														<th>Room</th>
														<th>Subroom</th>
														<th>Quantity</th>
													</tr>
												</thead>
												<tbody class="products">
													<tr>
														<td></td>
														<td></td>
														<td></td>
														<td></td>
														<td></td>
													</tr>
												</tbody>
											</table>
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
