<?php
	$myAccess = checkUserAccess();
?>
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Add Class <small>add your desire class</small>
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
								<i class="fa fa-list"></i> Add Qoutation
							</div>
						</div>
						<div class="portlet-body">
							<div class="form-body">
								<div class="portlet-body">
									<table class="table table-striped table-bordered table-hover" id="histtable">
										<thead>
											<tr>
												<th>
													Product Detail
												</th>
												<th>
													Product Quantity
												</th>
												<th>
													Select Vendor
												</th>
												<th>
													Insert Price
												</th>
												<th>
													Action
												</th>
											</tr>
										</thead>
										<tbody>
											<?php
												$i=1;
												foreach($purchase_request as $pr):
											?>
											<tr>
												<td>
													<strong>Campus Name:</strong> <?php echo $this->db->get_where('campuses',array('campus_id'=>$pr['campus_id']))->row()->campus_name;?>
													<br />
													<strong>Product Name:</strong> <?php echo $this->db->get_where('product_names',array('product_name_id'=>$pr['product_name_id']))->row()->product_name;?>
												</td>
												<td>
													<?php echo $pr['product_quantity'];?>
												</td>
												<td>
													<?php
														$product_name_id = $pr['product_name_id'];
														$this->db->select('*');
														$this->db->from('vendors');
														$this->db->where("find_in_set($product_name_id, product_name_ids)");
														$vendors = $this->db->get()->result_array();
													?>
													<select name="vendor_id" class="form-control input-inline input-large vendor_id vendor_id_<?php echo $i;?>" data-number="<?php echo $i;?>" data-purchase-request-id="<?php echo $pr['purchase_request_id']?>">
														<option value="">Select Vendor</option>
														<?php foreach($vendors as $vendor):?>
														<option value="<?php echo $vendor['id']?>"><?php echo $vendor['name'];?> (<?php echo $vendor['shop_name'];?>)</option>
														<?php endforeach;?>
													</select>
													<?php
														if(count($vendors)==0)
														{
															echo '<span class="text-danger"><br />Kindly Add Vendor Against this Product Provider.</span>';
														}
													?>
												</td>
												<td>
													<input type="number" min="1" class="form-control input-inline input-medium price_<?php echo $i;?>" name="price" placeholder="Enter Price of <?php echo $pr['product_quantity']?> units" value="" required>
												</td>
												<td>
													<input type="hidden" name="purchase_request_id" class="purchase_request_id_<?php echo $i;?>" value="<?php echo $pr['purchase_request_id']?>" />
													<button type="button" data-button-id="<?php echo $i;?>" class="btn green submit_qoutation">Submit Price</button>
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
						</div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>

			<?php
				if($myAccess[0]['approve_qoutation']==1 || $this->session->userdata('role')=='Admin'):
			?>
			<div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> All Qoutations
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
									 Product Details
								</th>
								<th>
									 Vendor Details & Price
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($purchase_request as $pr):
									$str=rand();
									$str = md5($str);
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									<strong>Campus Name:</strong> <?php echo $this->db->get_where('campuses',array('campus_id'=>$pr['campus_id']))->row()->campus_name;?>
									<br />
									<strong>Product Name:</strong> <?php echo $this->db->get_where('product_names',array('product_name_id'=>$pr['product_name_id']))->row()->product_name;?>
								</td>
								<td>
									<?php
										$this->db->select('*');
										$this->db->from('purchase_request_prices');
										$this->db->join('vendors','vendors.id=purchase_request_prices.vendor_id','inner');
										$this->db->where('purchase_request_prices.purchase_request_id',$pr['purchase_request_id']);
										$requests = $this->db->get()->result_array();
									?>
									<?php
										$i=1;
										foreach($requests as $request)
										{
											echo '<div class="radio-list">';
											echo '<label class="radio-inline">';
											if($request['approve']==1)
											{
												
												echo '<input type="radio" name="'.$str.'" class="price_selection" value="'.$request['purchase_request_price_id'].'" checked/>';
											}
											else
											{
												echo '<input type="radio" name="'.$str.'" class="price_selection" value="'.$request['purchase_request_price_id'].'" />';
											}
											echo 'Price: '.$request['price'];
											echo '<br />';
											echo 'Vendor Phone: '.$request['phone'];
											echo '<br />';
											echo 'Vendor Address: '.$request['address'];
											echo '<br />';
											echo 'Vendor Name: '.$request['name'];
											echo '</label>';
											echo '</div>';
											echo '<hr />';
											$i++;
										}
									?>
								</td>
                                <td>
									
								</td>
							</tr>
                            <?php
								$i++;
                            	endforeach;
							?>
							</tbody>
							</table>
							<a class="btn green" href="<?php echo site_url();?>/inventory/finalise_qoutation/<?php echo $this->uri->segment(3);?>">Finalise This Qoutation</a>
						</div>
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>
			<?php
				endif;
			?>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->