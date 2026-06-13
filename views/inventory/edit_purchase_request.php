
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
			<div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-edit"></i> Edit Purchase Request
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/inventory/update_purchase_request/<?php echo $this->uri->segment(3);?>" enctype="multipart/form-data">
								<div class="form-body">
									<?php
										foreach($purchase_request as $pr):
									?>
									<div class="row">
                                        <div class="col-md-12 purchase_requests">
                                            <select name="campus_id[]" class="form-control input-inline input-large select2" required>
                                                <?php
                                                    foreach($campuses as $campus):
                                                ?>
                                                <option value="<?php echo $campus['campus_id'];?>" <?php if($campus['campus_id']==$pr['campus_id']){echo 'selected';}?>><?php echo $campus['campus_name']?></option>
                                                <?php
                                                    endforeach;
                                                ?>
                                            </select>
                                            
                                            <select name="product_name_id[]" class="form-control input-inline input-large select2" required>
                                                <?php
                                                    foreach ($product_names as $product_name):
                                                ?>
                                                <option value="<?php echo $product_name['product_name_id']?>" <?php if($product_name['product_name_id']==$pr['product_name_id']){echo 'selected';}?>><?php echo $product_name['product_name'];?></option>
                                                <?php
                                                    endforeach;
                                                ?>
                                            </select>
											<input type="hidden" name="purchase_request_id[]" value="<?php echo $pr['purchase_request_id']?>" />
                                            <input type="number" min="1" style="margin-left: 10px;" class="form-control input-inline input-medium" name="quantity[]" placeholder="Enter Product Quantity" value="<?php echo $pr['product_quantity']?>" required>
											<span class="help-inline">For Rejection set product quantity to 0.</span>
                                        </div>
                                    </div>
									<?php
										endforeach;
									?>
									<div class="row">
										<div class="col-md-12">
											<div class="form-group">
                                                <label class="col-md-3 control-label">Approve Status <span class="required">*</span></label>
                                                <div class="col-md-9">
													<select name="status" class="form-control input-inline input-large">
														<option value="0" <?php if($purchase_request[0]['status']==0){echo 'selected';}?>>Pending</option>
														<option value="1" <?php if($purchase_request[0]['status']==1){echo 'selected';}?>>Approve</option>
														<option value="2" <?php if($purchase_request[0]['status']==2){echo 'selected';}?>>Reject</option>
													</select>
                                                </div>
                                            </div>
										</div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<input type="hidden" name="purchase_no" value="<?php echo $this->uri->segment(3);?>" />
											<button type="submit" class="btn green">Update Purchase Request</button>
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