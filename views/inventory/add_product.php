
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
								<i class="fa fa-plus"></i> Add Product
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/inventory/insert_product" enctype="multipart/form-data">
								<div class="form-body">
									<div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select name="campus_id" class="form-control input-inline input-large select2 campus" required>
                                                        <option value="">SELECT CAMPUS</option>
                                                        <?php
                                                            foreach($campuses as $campus):
                                                        ?>
                                                        <option value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name'];?></option>
                                                        <?php
                                                            endforeach;
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Room <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select name="room_id" class="form-control input-inline select2 input-large rooms" required>
                                                    
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Sub-Room</label>
                                                <div class="col-md-9">
                                                    <select name="subroom_id" class="form-control input-inline input-large select2 subrooms">
                                                    
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Product Name <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select name="product_name_id" class="form-control input-inline input-large select2 product_name_id" required>
                                                        <option value="">SELECT PRODUCT</option>
                                                        <?php
                                                            foreach($product_names as $product_name):
                                                        ?>
                                                        <option value="<?php echo $product_name['product_name_id'];?>"><?php echo $product_name['product_name'];?></option>
                                                        <?php
                                                            endforeach;
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Product QR Code <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-large qr_code" placeholder="Enter Product QR Code" value="" min="1" disabled required>
                                                    <input type="hidden" name="qr_code" class="qr_code" val="" />
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Estimate Purchased Price <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="number" class="form-control input-inline input-large" name="estimated_price" placeholder="Enter Product Estimate Price" value="" min="1" required>
                                                    <span class="help-inline">Note: Kindly Enter 1 Unit Price in estimate price.</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Product Image</label>
                                                <div class="col-md-9">
                                                    <input type="file" name="product_image" />
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Purchase Slip</label>
                                                <div class="col-md-9">
                                                    <input type="file" name="purchase_slip" />
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Product Quantity <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="number" class="form-control input-inline input-large" name="product_quantity" placeholder="Enter Product Quantity" value="1" min="1" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Consumeable <span class="required">*</span></label>
                                                <div class="col-md-9 radio-list">
                                                    <label class="radio-inline">
                                                    <input type="radio" name="consumeable" id="optionsRadios1" value="1"> Yes </label>
                                                    <label class="radio-inline">
                                                    <input type="radio" name="consumeable" id="optionsRadios2" value="0" checked> No </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Saleable <span class="required">*</span></label>
                                                <div class="col-md-9 radio-list">
                                                    <label class="radio-inline">
                                                    <input type="radio" name="saleable" id="optionsRadios1" value="1"> Yes </label>
                                                    <label class="radio-inline">
                                                    <input type="radio" name="saleable" id="optionsRadios2" value="0" checked> No </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 sale_amount" style="display:none;">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Sale Amount <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="number" class="form-control input-inline input-large sales_amount" name="sale_amount" placeholder="Enter Product Sale Amount" value="1" min="1">
                                                    <span class="help-inline">Note: Kindly Enter 1 Unit Price in Sale Amount.</span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Returnable <span class="required">*</span></label>
                                                <div class="col-md-9 radio-list">
                                                    <label class="radio-inline">
                                                    <input type="radio" name="returnable" id="optionsRadios1" value="1"> Yes </label>
                                                    <label class="radio-inline">
                                                    <input type="radio" name="returnable" id="optionsRadios2" value="0" checked> No </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Product Expiry <span class="required">*</span></label>
                                                <div class="col-md-9 radio-list">
                                                    <label class="radio-inline">
                                                    <input type="radio" name="expire" id="optionsRadios1" value="1"> Yes </label>
                                                    <label class="radio-inline">
                                                    <input type="radio" name="expire" id="optionsRadios2" value="0" checked> No </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 product_expire_date" style="display:none;">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Expire Date</label>
                                                <div class="col-md-9">
                                                    <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years" data-date-start-date="+0d">
                                                        <input type="text" name="expire_date" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
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
                                                    <input type="radio" name="product_guarantee" id="optionsRadios1" value="1"> Yes </label>
                                                    <label class="radio-inline">
                                                    <input type="radio" name="product_guarantee" id="optionsRadios2" value="0" checked> No </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row guarantee_dates" style="display:none;">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Guarantee Start</label>
                                                <div class="col-md-9">
                                                    <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                        <input type="text" name="product_guarantee_start_date" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
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
                                                    <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                        <input type="text" name="product_guarantee_end_date" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
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
                                                        <textarea class="form-control" rows="3" name="remarks"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Add Product</button>
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
    <script>
		document.addEventListener( "DOMContentLoaded", function(){
			$('.product_name_id').on('change',function (e) {
					var product_name_id = $(this).select2('val');
					if(product_name_id!='')
					{
						jQuery.ajax({
							type: "post",
							async: false,
							url: '<?php echo site_url()?>/inventory/getProductQR',
							data: {
								product_name_id : product_name_id
							},
							success: function(data) {
								$('.qr_code').val(data);
							}
						});
					}
				});
		}, false );
	</script>