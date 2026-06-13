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
								<i class="fa fa-eye"></i> Create Invoices (<?php echo $this->uri->segment(3);?>)
							</div>
						</div>
						<div class="portlet-body">
							<table class="table table-striped table-bordered table-hover">
							<thead>
							<tr>
								<th class="hidden">
                                	 hidden
                                </th>
                                <th>
									 Vendor Details
								</th>
								<th>
									Product Purchased
								</th>
								<th>
									Total Bill
								</th>
								<th>
									 Pay in Bank
								</th>
								<th>
									 Pay in Cash
								</th>
								<th>
									Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 1;
								foreach($invoices as $invoice):
							?>
							<form action="<?php echo site_url();?>/inventory/insert_finalise_prices" method="post">
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
								<td>
									<?php echo $invoice['name'];?>
									<br />
									<?php echo $invoice['shop_name'];?>
									<br />
									<?php echo $invoice['phone'];?>
								</td>
                                <td>
									<?php
										$this->db->select('product_names.product_name,purchase_requests.product_quantity,purchase_requests.purchase_price');
										$this->db->from('purchase_requests');
										$this->db->join('product_names','product_names.product_name_id=purchase_requests.product_name_id','inner');
										$this->db->where(array('purchase_requests.purchase_no'=>$this->uri->segment(3),'purchase_from'=>$invoice['purchase_from']));
										$products = $this->db->get()->result_array();

										foreach($products as $product)
										{
											echo '<button type="button" class="btn grey">'.$product['product_name'].' x '.$product['product_quantity'].' = '.$product['purchase_price'].'</button>';
										}
									?>
								</td>
								<td>
								<?php echo $invoice['total_bill'];?>
								</td>
								<td>
									<input class="form-control" type="number" name="pay_in_bank" placeholder="Enter Price Pay in Bank" />
								</td>
								<td>
									<input class="form-control" type="number" name="pay_in_cash" placeholder="Enter Price Pay in Cash" />
								</td>
								<td>
									<button class="btn green" type="submit">Create Invoice</button>
								</td>
							</tr>
							</form>
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