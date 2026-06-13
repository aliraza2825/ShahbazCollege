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

			<?php if(@$this->session->userdata('error')):?>
                <div class="alert alert-danger">
                    <button class="close" data-close="alert"></button>
                    <span>
                    <?php echo $this->session->userdata('error');?> </span>
                </div>
            <?php endif;?>

			<div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> All Return Product Requests
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
									 Return Quantity
								</th>
								<th>
									 Purchased Price
								</th>
								<th>
									 Return Price
								</th>
								<th>
									 Return Product User
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($return_requests as $return_request):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $return_request['purchase_no']?>
								</td>
								<td>
									<?php echo $return_request['product_name']?>
								</td>
								<td>
									 <?php 
									 	echo $return_request['return_product_quantity'];
									 ?>
								</td>
								
                                <td>
									<?php echo $return_request['estimated_price']*$return_request['return_product_quantity'];?>
								</td>
								<td>
									<?php echo $return_request['amount']?>
								</td>
								<td>
									<?php echo $return_request['user_id']?>
								</td>
								<td>
									<a href="<?php echo site_url();?>/inventory/return_request_update/<?php echo $return_request['return_product_id']?>/1" class="btn green">Approve</a>
                                    <a href="<?php echo site_url();?>/inventory/return_request_update/<?php echo $return_request['return_product_id']?>/0" class="btn red">Reject</a>
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