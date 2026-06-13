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
								<i class="fa fa-list"></i> Check Purchase Orders
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/inventory/purchase_orders">
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
											<button type="submit" class="btn green">Check Purchase Orders</button>
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
								<i class="fa fa-list"></i> All Purchase Orders
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
									 Title
								</th>
								<th>
									 Approve Qoutation Date
								</th>
								<th>
									 Request By
								</th>
								<th>
									 Request Approve By
								</th>
								<th>
									 Qoutation Approve By
								</th>
								<th>
									 Status
								</th>
								<th>
									Products
								</th>
								<th>
									View
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
									<?php echo $purchase_request['title']?>
								</td>
								<td>
									 <?php 
									 	echo date('F d,Y',strtotime($purchase_request['final_approve_at']));
									 ?>
								</td>
								
                                <td>
									<?php echo $purchase_request['add_by']?>
								</td>
								<td>
									<?php echo $purchase_request['approve_by']?>
								</td>
								<td>
									<?php echo $purchase_request['qoutation_approve_by']?>
								</td>
								<td>
									Purchase Order
								</td>
								<td>
									<?php
										$this->db->select('product_names.product_name,product_names.product_name_id,purchase_requests.purchased');
										$this->db->from('purchase_requests');
										$this->db->join('product_names','product_names.product_name_id=purchase_requests.product_name_id', 'inner');
										$this->db->where(array('purchase_requests.purchase_no'=>$purchase_request['purchase_no']));
										$products = $this->db->get()->result_array();
										
										foreach($products as $product)
										{
											if($product['purchased']==1)
											{
												echo '<i class="fa fa-check"></i> '.$product['product_name'].'<br />';
											}
											else
											{
												echo '<i class="fa fa-close"></i> '.$product['product_name'].' <a href="'.site_url().'/inventory/delete_product_purchase_request/'.$product['product_name_id'].'/'.$purchase_request['purchase_no'].'"><i class="fa fa-trash"></i></a><br />';
											}
										}
									?>
								</td>
								<td>
									<a href="<?php echo site_url();?>/inventory/view_purchase_order/<?php echo $purchase_request['purchase_no'];?>" class="btn blue"><i class="fa fa-money"></i> View Payment Aggrement</a>
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