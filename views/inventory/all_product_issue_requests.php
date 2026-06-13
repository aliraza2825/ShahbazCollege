
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
								<i class="fa fa-calendar"></i> Check Product Issue Requests
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/inventory/all_product_issue_requests">
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
				<div class="col-md-4 text-center">
					<form method="post" action="<?php echo site_url();?>/inventory/all_product_issue_requests">
						<input type="hidden" name="from_date" value="<?php echo $from_date?>" />
						<input type="hidden" name="to_date" value="<?php echo $to_date?>" />
						<input type="hidden" name="status" value="1" />
						<button type="submit" class="btn green">Approved <span class="badge badge-default"><?php echo $approved;?></span></button>
					</form>
				</div>
				<div class="col-md-4 text-center">
					<form method="post" action="<?php echo site_url();?>/inventory/all_product_issue_requests">
						<input type="hidden" name="from_date" value="<?php echo $from_date?>" />
						<input type="hidden" name="to_date" value="<?php echo $to_date?>" />
						<input type="hidden" name="status" value="0" />
						<button type="submit" class="btn yellow">Pending <span class="badge badge-default"><?php echo $pending;?></span></button>
					</form>
				</div>
				<div class="col-md-4 text-center">
					<form method="post" action="<?php echo site_url();?>/inventory/all_product_issue_requests">
						<input type="hidden" name="from_date" value="<?php echo $from_date?>" />
						<input type="hidden" name="to_date" value="<?php echo $to_date?>" />
						<input type="hidden" name="status" value="2" />
						<button type="submit" class="btn red">Rejected <span class="badge badge-default"><?php echo $rejected;?></span></button>
					</form>
				</div>
				<div class="col-md-12">
					<br />
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i>All Visitors
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
									 Product
								</th>
								<th>
									Quantity
								</th>
								<th>
									 Campus Name
								</th>
								<th>
									Room Name
								</th>
								<th>
									Subroom Name
								</th>
                                <th>
									 Comment
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
                            	$i = 0;
								foreach($requests as $request):
							?>
                            <tr class="odd gradeX">
								<form method="post" action="<?php echo site_url();?>/inventory/approve_product_issue_requests/<?php echo $request['require_product_request_id']?>">
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
										if($request['gin']==0):
									?>
									<select name="product_name_id" class="form-control input-inline input-medium select2" required>
										<option value="">SELECT PRODUCT</option>
										<?php
											foreach($product_names as $product_name):
										?>
										<option value="<?php echo $product_name['product_name_id'];?>" <?php if($product_name['product_name_id']==$request['product_name_id']){ echo 'selected';}?>><?php echo $product_name['product_name'];?></option>
										<?php
											endforeach;
										?>
									</select>
									<?php
										else:
											echo $request['product_name'];
										endif;
									?>
								</td>
								<td>
									<?php
										if($request['gin']==0):
									?>
									<input type="number" name="quantity" min="1" class="form-control input-small" value="<?php echo $request['quantity']?>" required />
									<?php
										else:
											echo $request['quantity'];
										endif;
									?>
								</td>
								<td>
									<?php
										if($request['gin']==0):
									?>
									<select name="campus_id" data-row-id="<?php echo $i;?>" class="form-control input-inline input-medium select2 product_issue_request_campus" required>
										<option value="">SELECT CAMPUS</option>
										<?php
											foreach($campuses as $campus):
										?>
										<option value="<?php echo $campus['campus_id'];?>" <?php if($campus['campus_id']==$request['campus_id']){ echo 'selected';}?>><?php echo $campus['campus_name'];?></option>
										<?php
											endforeach;
										?>
									</select>
									<?php
										else:
											echo $request['campus_name'];
										endif;
									?>
								</td>
								<td>
									<?php
										if($request['gin']==0):
									?>
									<select name="room_id" data-row-id="<?php echo $i;?>" class="form-control input-inline input-medium select2 product_issue_request_rooms product_issue_request_rooms_<?php echo $i;?>" >
										<option value="">SELECT ROOM</option>
										<?php
											$rooms = $this->db->get_where('rooms',array('campus_id'=>$request['campus_id']))->result_array();
											foreach($rooms as $room):
										?>
										<option value="<?php echo $room['room_id'];?>" <?php if($room['room_id']==$request['room_id']){ echo 'selected';}?>><?php echo $room['room_name'];?></option>
										<?php
											endforeach;
										?>
									</select>
									<?php
										else:
											echo $request['room_name'];
										endif;
									?>
								</td>
								<td>
									<?php
										if($request['gin']==0):
									?>
									<?php
										if($request['subroom_id']!=''):
									?>
									<select name="subroom_id" class="form-control input-inline input-medium select2 product_issue_request_subrooms_<?php echo $i;?>">
										<option value="">SELECT SUBROOM</option>
										<?php
											$subrooms = $this->db->get_where('subrooms',array('room_id'=>$request['room_id']))->result_array();
											foreach($subrooms as $subroom):
										?>
										<option value="<?php echo $subroom['subroom_id'];?>" <?php if($subroom['subroom_id']==$request['subroom_id']){ echo 'selected';}?>><?php echo $subroom['subroom_name'];?></option>
										<?php
											endforeach;
										?>
									</select>
									<?php
										endif;
									?>
									<?php
										else:
											echo $request['subroom_name'];
										endif;
									?>
								</td>
                                <td>
									<?php echo $request['comment']?>
								</td>
                                <td>
									<?php 
										if($request['status']=='0')
										{
											echo '<button type="button" class="btn yellow">Pending</button>';
										}
										elseif($request['status']=='1')
										{
											echo '<button type="button" class="btn green">Approved</button>';
										}
										elseif($request['status']=='2')
										{
											echo '<button type="button" class="btn red">Rejected</button>';
										}
										else
										{
											
										}
									?>
								</td>
								<td>
                                    <?php
										if($request['gin']=='0'):
									?>
									<select name="status" class="form-control input-inline input-medium" required>
										<option value="">SELECT STATUS</option>
										<option value="1">Approve</option>
										<option value="2">Reject</option>
									</select>
									<textarea class="form-control input-medium" name="approval_comment" required></textarea>
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
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->