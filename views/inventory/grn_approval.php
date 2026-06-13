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
								<i class="fa fa-list"></i> GRN Approval
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
									 Product Name
								</th>
								<th>
									 Product Quantity
								</th>
								<th>
									 Purchaser
								</th>
								<th>
									Received
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 1;
								foreach($purchase_orders as $purchase_order):
							?>
                            <tr class="odd gradeX grn_gate_approval_<?php echo $i;?>">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $purchase_order['purchase_no']?>
								</td>
								<td>
									<?php echo $purchase_order['product_name']?>
								</td>
								<td>
									<?php echo $purchase_order['product_quantity']?>
								</td>
								
                                <td>
									<?php echo $purchase_order['purchased_by']?>
								</td>
								<td>
									<a href="<?php echo site_url();?>/inventory/add_purchased_product/<?php echo $purchase_order['purchase_request_id']?>" class="btn blue"><i class="fa fa-plus"></i> Add Product</a>
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