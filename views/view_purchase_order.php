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
								<i class="fa fa-eye"></i> Purchase Order (<?php echo $this->uri->segment(3);?>)
							</div>
						</div>
						<div class="portlet-body">
							<form action="<?php echo site_url();?>/inventory/insert_finalise_prices" method="post">
							<table class="table table-striped table-bordered table-hover">
							<thead>
							<tr>
								<th class="hidden">
                                	 hidden
                                </th>
                                <th>
									 Product Name
								</th>
								<th>
									 Quantity
								</th>
								<th>
									Paid Through
								</th>
								<th>
									 Price
								</th>
								<th>
									Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 1;
								$total_price=0;
								foreach($purchase_orders as $purchase_order):
								$total_price+=(int)$purchase_order['purchase_price'];
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $purchase_order['product_name']?>
								</td>
								<td>
									<?php echo $purchase_order['product_quantity']?>
								</td>
								<td>
									<select name="paid_type[]" class="form-control">
										<option value="cash" <?php if($purchase_order['paid_type']=='cash'){echo 'selected';}?>>Cash</option>
										<option value="bank" <?php if($purchase_order['paid_type']=='bank'){echo 'selected';}?>>Bank</option>
									</select>
								</td>
								<td>
									<input type="hidden" name="purchase_request_id[]" value="<?php echo $purchase_order['purchase_request_id'];?>" />
									<input type="number" min="1" name="purchase_price[]" max="<?php echo $purchase_order['price'];?>" class="form-control price price_<?php echo $i;?>" value="<?php echo $purchase_order['purchase_price'];?>" placeholder="Selected Price is <?php echo $purchase_order['price'];?>" />
								</td>
								<td>
									<?php if($this->session->userdata('role')=='Admin'):?>
									<a onclick="return confirm('Are you sure you want to delete this Purchase Order?')" href="<?php echo site_url();?>/inventory/delete_purchase_request/<?php echo $purchase_order['purchase_request_id'];?>" class="btn red"><i class="fa fa-trash"></i> Delete</a>
									<?php endif;?>
								</td>
							</tr>
                            <?php
								$i++;
                            	endforeach;
							?>	
							</tbody>
							</table>
							<div class="text-right">
								<h3>Total Price: <span class="total_price"><?php echo $total_price;?></span></h3>
								<button type="submit" class="btn green">Confirm Purchased</button>
							</div>
							</form>
						</div>
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->


	<script>
        //var index = 1;
        document.addEventListener( "DOMContentLoaded", function() {
            
            jQuery('.price').live('change',function(){
                var total_price_count = <?php echo $i-1;?>;
				var total_price = 0;
				jQuery('.price').each(function () {
					total_price += parseFloat(this.value) || 0;

				});
				jQuery('.total_price').html(total_price);
            });

        }, false );
    </script>