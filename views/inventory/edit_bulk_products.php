
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
								<i class="fa fa-edit"></i> Edit Bulk Products
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/inventory/update_bulk_products/<?php echo $this->uri->segment(3);?>" enctype="multipart/form-data">
								<div class="form-body">
									<div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                        <?php
                                                            foreach($campuses as $campus):
                                                                if($products[0]['campus_id']==$campus['campus_id'])
                                                                {
                                                                    echo '<span class="form-control-static">'.$campus['campus_name'].'</span>';
                                                                }
                                                            endforeach;
                                                        ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Room <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                        <?php
                                                            foreach($rooms as $room):
                                                                if($products[0]['room_id']==$room['room_id'])
                                                                {
                                                                    echo '<span class="form-control-static">'.$room['room_name'].'</span>';
                                                                }
                                                            endforeach;
                                                        ?>
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Sub-Room </label>
                                                <div class="col-md-9">
                                                        <?php
                                                            foreach($subrooms as $subroom):
                                                                if($products[0]['subroom_id']==$subroom['subroom_id'])
                                                                {
                                                                    echo '<span class="form-control-static">'.$subroom['subroom_name'].'</span>';
                                                                }
                                                            endforeach;
                                                        ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Product Name <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                        <?php
                                                            foreach($product_names as $product_name):
                                                                if($product_name['product_name_id']==$products[0]['product_name_id'])
                                                                {
                                                                    echo '<span class="form-control-static">'.$product_name['product_name'].'</span>';
                                                                }
                                                            endforeach;
                                                        ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Product QR Code <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="number" class="form-control input-inline input-large" name="qr_code" placeholder="Enter Product QR Code" value="<?php echo str_replace('inv_qr-','',$products[0]['qr_code']);?>" min="1" required readonly>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Estimate Price <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="number" class="form-control input-inline input-large" name="estimated_price" placeholder="Enter Product Estimate Price" value="<?php echo $products[0]['estimated_price'];?>" min="1" required>
                                                    <span class="help-inline">Note: Kindly Enter 1 Unit Price in estimate price.</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Product Quantity <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="number" class="form-control input-inline input-large" name="product_quantity" placeholder="Enter Product Quantity" value="<?php echo $products[0]['product_quantity'];?>" required readonly>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Consumeable <span class="required">*</span></label>
                                                <div class="col-md-9 radio-list">
                                                    <label class="radio-inline">
                                                    <input type="radio" name="consumeable" id="optionsRadios1" value="1" <?php if($products[0]['consumeable']==1){echo 'checked';}?>> Yes </label>
                                                    <label class="radio-inline">
                                                    <input type="radio" name="consumeable" id="optionsRadios2" value="0" <?php if($products[0]['consumeable']==0){echo 'checked';}?>> No </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Saleable <span class="required">*</span></label>
                                                <div class="col-md-9 radio-list">
                                                    <label class="radio-inline">
                                                    <input type="radio" name="saleable" id="optionsRadios1" value="1" <?php if($products[0]['saleable']==1){echo 'checked';}?>> Yes </label>
                                                    <label class="radio-inline">
                                                    <input type="radio" name="saleable" id="optionsRadios2" value="0" <?php if($products[0]['saleable']==0){echo 'checked';}?>> No </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 sale_amount" <?php if($products[0]['saleable']==0){echo 'style="display:none;"';}?>>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Sale Amount <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="number" class="form-control input-inline input-large sales_amount" name="sale_amount" placeholder="Enter Product Sale Amount" value="<?php echo $products[0]['sale_amount'];?>" min="1">
                                                    <span class="help-inline">Note: Kindly Enter 1 Unit Price in Sale Amount.</span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Returnable <span class="required">*</span></label>
                                                <div class="col-md-9 radio-list">
                                                    <label class="radio-inline">
                                                    <input type="radio" name="returnable" id="optionsRadios1" value="1" <?php if($products[0]['returnable']==1){echo 'checked';}?>> Yes </label>
                                                    <label class="radio-inline">
                                                    <input type="radio" name="returnable" id="optionsRadios2" value="0" <?php if($products[0]['returnable']==0){echo 'checked';}?>> No </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Product Expiry <span class="required">*</span></label>
                                                <div class="col-md-9 radio-list">
                                                    <label class="radio-inline">
                                                    <input type="radio" name="expire" id="optionsRadios1" value="1" <?php if($products[0]['expire']==1){echo 'checked';}?>> Yes </label>
                                                    <label class="radio-inline">
                                                    <input type="radio" name="expire" id="optionsRadios2" value="0" <?php if($products[0]['expire']==0){echo 'checked';}?>> No </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 product_expire_date" <?php if($products[0]['expire']==0){echo 'style="display:none;"';}?>>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Expire Date</label>
                                                <div class="col-md-9">
                                                    <div class="input-group input-medium date date-picker" data-date="<?php echo $products[0]['expire_date'];?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years" data-date-start-date="+0d">
                                                        <input type="text" name="expire_date" class="form-control" value="<?php echo $products[0]['expire_date'];?>" readonly>
                                                        <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Product Guarantee <span class="required">*</span></label>
                                                <div class="col-md-9 radio-list">
                                                    <label class="radio-inline">
                                                    <input type="radio" name="product_guarantee" id="optionsRadios1" value="1" <?php if($products[0]['product_guarantee']==1){echo 'checked';}?> /> Yes </label>
                                                    <label class="radio-inline">
                                                    <input type="radio" name="product_guarantee" id="optionsRadios2" value="0" <?php if($products[0]['product_guarantee']==0){echo 'checked';}?> /> No </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row guarantee_dates" <?php if($products[0]['product_guarantee']==0){echo 'style="display:none;"';}?>>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Guarantee Start</label>
                                                <div class="col-md-9">
                                                    <div class="input-group input-medium date date-picker" data-date="<?php echo $products[0]['product_guarantee_start_date'];?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                        <input type="text" name="product_guarantee_start_date" class="form-control" value="<?php echo $products[0]['product_guarantee_start_date'];?>" readonly>
                                                        <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Guarantee End</label>
                                                <div class="col-md-9">
                                                    <div class="input-group input-medium date date-picker" data-date="<?php echo $products[0]['product_guarantee_end_date'];?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                        <input type="text" name="product_guarantee_end_date" class="form-control" value="<?php echo $products[0]['product_guarantee_end_date'];?>" readonly>
                                                        <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                    	<div class="col-md-12">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Remarks</label>
                                                <div class="col-md-9">
                                                    <div class="col-md-6">
                                                        <textarea class="form-control" rows="3" name="remarks"><?php echo $products[0]['remarks'];?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<input type="hidden" name="old_purchase_slip" value="<?php echo $products[0]['purchase_slip'];?>" />
                                            <input type="hidden" name="old_product_image" value="<?php echo $products[0]['product_image'];?>" />
                                            <button type="submit" class="btn green">Update Product</button>
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