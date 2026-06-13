
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Add Class <small>add your desire class</small>
			</h3>-->
			<!-- END PAGE HEADER-->
			<!-- BEGIN PAGE CONTENT-->
			<div class="row">
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat red">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								<?php echo $unpaid_payments_till_date[0]['amount'];?>
							</div>
							<div class="desc">
								Unpaid Payments
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat blue">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								<?php echo $paid_payments_till_date[0]['amount'];?>
							</div>
							<div class="desc">
								Paid Payments
							</div>
						</div>
					</div>
				</div>
			</div>
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
								<i class="fa fa-calendar"></i> Select Date
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/inventory/payments" enctype="multipart/form-data">
								<div class="form-body">
									<div class="form-group">
										<label class="control-label col-md-3">Till Date</label>
										<div class="col-md-3">
											<div class="input-group input-medium date date-picker" data-date="<?php echo $till_date;?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
												<input type="text" name="till_date" class="form-control" value="<?php echo $till_date;?>" readonly>
												<span class="input-group-btn">
												<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
												</span>
											</div>
										</div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Check</button>
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
								<i class="fa fa-list"></i> Unpaid Payment Till Date (<?php echo $till_date;?>)
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
									 Payment Date
								</th>
								<th>
									 Vendor Details
								</th>
                                <th>
									 Product Details
								</th>
                                <th>
									 Purchase #
								</th>
                                <th>
									 Amount
								</th>
                                <th>
									 Payment Comment
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($unpaid_payments as $payment):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
								<td>
                                	 <?php echo $payment['date'];?>
                                </td>
								<td>
                                	 <?php 
									 	echo 'Vendor Name : '.$payment['vendor_name'];
										echo '<br />';
										echo 'Shop Name : '.$payment['shop_name'];
										echo '<br />';
										echo 'Phone : '.$payment['vendor_phone'];
										echo '<br />';
										echo 'Address : '.$payment['vendor_address'];
									 ?>
                                </td>
								<td>
                                	<?php
										$this->db->select('product_names.*');
										$this->db->from('product_names');
										$this->db->join('purchase_requests','purchase_requests.product_name_id=product_names.product_name_id','inner');
										$this->db->where(array('purchase_requests.purchase_from'=>$payment['vendor_id'],'purchase_no'=>$payment['purchase_no']));
										$products = $this->db->get()->result_array();

										foreach($products as $product)
										{
											echo '<button class="btn grey">'.$product['product_name'].'</button>';
										}
									?>
                                </td>
								<td>
									<?php echo $payment['purchase_no'];?>
                                </td>
								<td>
									<?php echo $payment['amount'];?>
                                </td>
								<td>
									<?php echo $payment['comment'];?>
                                </td>
								<td>
                                	<button type="button" class="btn blue pay_now" data-payment-aggrement-id="<?php echo $payment['payment_aggrement_id'];?>" data-amount="<?php echo $payment['amount'];?>">Pay Amount</button>
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

	<div class="modal fade" id="basic" tabindex="-1" role="basic" aria-hidden="true" data-width="auto">
		<div class="modal-dialog" style="margin:0;">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
					<h4 class="modal-title">Paid Amount (<span class="installment_amount"></span>)</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<form method="post" action="<?php echo site_url();?>/inventory/payment_paid" enctype="multipart/form-data">
							<div class="form-group">
								<label class="col-md-3 control-label">Campus <span class="required">*</span></label>
								<div class="col-md-9">
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
							<br /><br /><br />
							<div class="form-group">
								<label class="col-md-3 control-label">Expense Date <span class="required">*</span></label>
								<div class="col-md-9">
									<div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
										<input type="text" name="date" class="form-control expense_date" value="<?php echo date('Y-m-d');?>" readonly>
										<span class="input-group-btn">
										<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
										</span>
									</div>
								</div>
							</div>
							
							<br /><br /><br />
							<div class="form-group">
								<label class="col-md-3 control-label">Paid Type <span class="required">*</span></label>
								<div class="col-md-9">
									<select class="form-control paid_type" name="paid_type">
										<option value="">Select</option>
										<option value="bank">Bank</option>
										<option value="cash">Cash</option>
									</select>
								</div>
							</div>
							<br /><br /><br />
							<div class="form-group">
								<div class="col-md-12 transactions">

								</div>
							</div>
							<br /><br /><br />
							<div class="form-group">
								<label class="col-md-3 control-label">Receipt</label>
								<div class="col-md-9">
									<input type="file" name="image" />
								</div>
							</div>
							<br /><br />
							<div class="form-group">
								<label class="col-md-3 control-label"></label>
								<div class="col-md-9">
									<input type="hidden" class="payment_aggrement_id" name="payment_aggrement_id" value="" />
									<button type="submit" class="btn green">Submit</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
			<!-- /.modal-content -->
		</div>
		<!-- /.modal-dialog -->
	</div>

	<script>
        //var index = 1;
        document.addEventListener( "DOMContentLoaded", function() {
            
            jQuery('.pay_now').click(function(){
				var payment_aggrement_id = jQuery(this).data('payment-aggrement-id');
				var amount = jQuery(this).data('amount');
				jQuery('#basic').modal('show');
				jQuery('.installment_amount').html(amount);
				jQuery('.payment_aggrement_id').val(payment_aggrement_id);
			});

			jQuery('.expense_date').change(function(){
				var expense_date = jQuery(this).val();
				var paid_type = jQuery('.paid_type').val();
				var expense_amount = jQuery('.installment_amount').html();
				//alert(expense_amount);
				if(paid_type=='bank')
				{
					jQuery.ajax({
						type: "post",
						async: false,
						url: '<?php echo site_url()?>/inventory/getTransaction',
						data: {
							expense_date : expense_date,
							expense_amount : expense_amount
						},
						success: function(data) {
							jQuery('.transactions').html(data);
						}
					});
				}
				else
				{
					jQuery('.transactions').html('');
				}
			});

			jQuery('.paid_type').change(function(){
				var expense_date = jQuery('.expense_date').val();;
				var paid_type = jQuery('.paid_type').val();
				var expense_amount = jQuery('.installment_amount').html();
				if(paid_type=='bank')
				{
					jQuery.ajax({
						type: "post",
						async: false,
						url: '<?php echo site_url()?>/inventory/getTransaction',
						data: {
							expense_date : expense_date,
							expense_amount : expense_amount
						},
						success: function(data) {
							jQuery('.transactions').html(data);
						}
					});
				}
				else
				{
					jQuery('.transactions').html('');
				}
			});

        }, false );
    </script>