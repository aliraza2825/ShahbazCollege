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
			
			<?php
				$i=1;
				foreach($purchase_orders as $purchase_order):

					$this->db->select('product_names.product_name,purchase_request_prices.price,purchase_requests.product_quantity');
					$this->db->from('purchase_requests');
					$this->db->join('purchase_request_prices','purchase_request_prices.purchase_request_id=purchase_requests.purchase_request_id','inner');
					$this->db->join('product_names','product_names.product_name_id=purchase_requests.product_name_id','inner');
					$this->db->join('vendors','purchase_request_prices.vendor_id=vendors.id','inner');
					$this->db->where(array('purchase_requests.purchase_no'=>$purchase_order['purchase_no'],'purchase_requests.product_quantity>'=>0,'purchase_request_prices.approve'=>1,'vendors.id'=>$purchase_order['id']));
					$products = $this->db->get()->result_array();
			?>
			<div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-money"></i> Create Payment Aggrement
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/inventory/insert_payment_aggrement" enctype="multipart/form-data">
								<div class="form-body">
									<div class="row">
                                        <div class="col-md-12">
											<h4>Vendor Name : <?php echo $purchase_order['name'];?></h4>
											<h4>Vendor phone : <?php echo $purchase_order['phone'];?></h4>
											<h4>Vendor Address : <?php echo $purchase_order['address'];?></h4>
											<h4>Purchased Products : <?php foreach($products as $product){echo '<button class="btn grey">'.$product['product_name'].' x '.$product['product_quantity'].' = '.$product['price'].'</button>';}?></h4>
											<h4>Total Price : <?php echo $purchase_order['total'];?></h4>
                                        </div>
                                    </div>
									<div class="row">
										<div class="col-md-12">
											<table class="table table-bordered">
												<thead>
													<tr>
														<th>Amount</th>
														<th>Date</th>
														<th>Comment</th>
														<th>Action</th>
													</tr>
												</thead>
												<tbody class="payment_installment_<?php echo $i;?>">
													<?php
														//CHECK PAYMENT AGGREMENT ADDED OR NOT
														$this->db->select('*');
														$this->db->from('payment_aggrements');
														$this->db->where(array('purchase_no'=>$purchase_order['purchase_no'],'vendor_id'=>$purchase_order['vendor_id']));
														$payment_aggrements = $this->db->get()->result_array();
													?>
													<?php
														if(count($payment_aggrements)>0):
													?>
													<?php
														foreach($payment_aggrements as $payment_aggrement):
													?>
													<tr class="installment row-1">
														<td>
															<?php echo $payment_aggrement['amount'];?>
														</td>
														<td>
															<?php echo $payment_aggrement['date'];?>
														</td>
														<td>
															<?php echo $payment_aggrement['comment'];?>
														</td>
														<td>
															<?php
																if($payment_aggrement['paid']==1)
																{
																	echo '<button type="button" class="btn green">Paid</button>';
																}
																else
																{
																	echo '<button type="button" class="btn red">Not Paid</button>';
																}
															?>
														</td>
													</tr>
													<?php
														endforeach;
													?>
													<?php
														else:
													?>
												
													<tr class="installment row-1">
														<td>
															<input type="number" name="installment[]" class="form-control installment" value="" placeholder="Enter Installment Amount" />
														</td>
														<td>
															<div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
																<input type="text" name="date[]" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
																<span class="input-group-btn">
																<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
																</span>
															</div>
														</td>
														<td>
															<input type="text" class="form-control" name="comment[]" value="" placeholder="Enter Comment" />
														</td>
														<td>
															<button type="button" class="btn red delete_installment" data-installment-number="1"><i class="fa fa-trash"></i></button>
														</td>
													</tr>
													<?php
														endif;
													?>
												</tbody>
											</table>
										</div>
									</div>
									<?php
										if(count($payment_aggrements)<1):
									?>
									<div class="row">
										<div class="col-md-12">
											<br />
											<button type="button" class="btn green add_payment_installment" data-portion-number="<?php echo $i;?>"><i class="fa fa-plus"></i> Add More</button>
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-3 control-label">Aggrement Image </label>
										<div class="col-md-9">
											<input type="file" class="form-control input-inline input-medium" name="aggrement_image" value="" />
											<span class="help-inline"></span>
										</div>
									</div>
									<?php
										else:
									?>
									<div class="row">
										<div class="col-md-12">
											<?php
												if($payment_aggrements[0]['image']!=''):
											?>
												<a href="<?php echo base_url();?>uploads/<?php echo $payment_aggrements[0]['image'];?>" target="_blank" class="btn blue">View Image</a>
											<?php
												endif;
											?>
										</div>
									</div>
									<?php
										endif;
									?>
								</div>
								<?php
									if(count($payment_aggrements)<1):
								?>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<input type="hidden" name="vendor_id" value="<?php echo $purchase_order['id'];?>" />
											<input type="hidden" name="purchase_no" value="<?php echo $purchase_order['purchase_no'];?>" />
											<input type="hidden" name="total_amount" value="<?php echo $purchase_order['total'];?>" />
											<button type="submit" class="btn green">Create Aggrement</button>
										</div>
									</div>
								</div>
								<?php
									endif;
								?>
							</form>
						</div>
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>
			<?php
				$i++;
				endforeach;
			?>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->


	<script>
        //var index = 1;
        document.addEventListener( "DOMContentLoaded", function() {
            
            jQuery('.add_payment_installment').live('click',function(){
				var portion_number = jQuery(this).data('portion-number');
				var total_installments = jQuery('.payment_installment_'+portion_number).children('.installment').length;
				var installment_no =total_installments+1;
                var html = '<tr class="installment row-'+installment_no+'"><td><input type="number" name="installment[]" class="form-control installment" value="" placeholder="Enter Installment Amount" /></td><td><div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years"><input type="text" name="date[]" class="form-control" value="<?php echo date('Y-m-d');?>" readonly><span class="input-group-btn"><button class="btn default" type="button"><i class="fa fa-calendar"></i></button></span></div></td><td><input type="text" class="form-control" value="" name="comment[]" placeholder="Enter Comment" /></td><td><button type="button" class="btn red delete_installment" data-installment-number="'+installment_no+'"><i class="fa fa-trash"></i></button></td></tr>';
				jQuery('.payment_installment_'+portion_number).append(html);
				ComponentsPickers.init();
            });

			jQuery('.delete_installment').live('click',function(){
				var installment_no = jQuery(this).data('installment-number');
				jQuery('.row-'+installment_no).remove();
			});

			// jQuery('.installment').live('change',function(){
			// 	var total_amount = 0;
			// 	jQuery('.installment').each(function(){
			// 		total_amount += +jQuery(this).val();
			// 	});

			// 	jQuery(':button[type="submit"]').prop('disabled', true);
			// 	alert(total_amount);
			// })

        }, false );
    </script>