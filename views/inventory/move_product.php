
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
								<i class="fa fa-edit"></i> Edit Product
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/inventory/update_move_product/<?php echo $this->uri->segment(3);?>" enctype="multipart/form-data">
								<div class="form-body">
									<div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select name="campus_id" class="form-control input-inline input-large campus" required>
                                                        <option value="">SELECT CAMPUS</option>
                                                        <?php
                                                            foreach($campuses as $campus):
                                                        ?>
                                                        <option value="<?php echo $campus['campus_id'];?>" <?php if($products[0]['campus_id']==$campus['campus_id']){echo 'selected';}?>><?php echo $campus['campus_name'];?></option>
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
                                                    <select name="room_id" class="form-control input-inline input-large rooms" required>
                                                        <option value="0" <?php if($products[0]['room_id']==0){echo 'selected';}?>>Personal Use</option>
                                                        <?php
                                                            foreach($rooms as $room):
                                                        ?>
                                                        <option value="<?php echo $room['room_id'];?>" <?php if($products[0]['room_id']==$room['room_id']){echo 'selected';}?>><?php echo $room['room_name'];?></option>
                                                        <?php
                                                            endforeach;
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Sub-Room </label>
                                                <div class="col-md-9">
                                                    <select name="subroom_id" class="form-control input-inline input-large subrooms">
														<option value="0" <?php if($products[0]['subroom_id']==0){echo 'selected';}?>>Select</option>
                                                        <?php
                                                            foreach($subrooms as $subroom):
                                                        ?>
                                                        <option value="<?php echo $subroom['subroom_id'];?>" <?php if($products[0]['subroom_id']==$subroom['subroom_id']){echo 'selected';}?>><?php echo $subroom['subroom_name'];?></option>
                                                        <?php
                                                            endforeach;
                                                        ?>
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
                                                    <select name="product_name_id" class="form-control input-inline input-large select2" required>
                                                        <option value="">SELECT PRODUCT</option>
                                                        <?php
                                                            foreach($product_names as $product_name):
                                                        ?>
                                                        <option value="<?php echo $product_name['product_name_id'];?>" <?php if($product_name['product_name_id']==$products[0]['product_name_id']){echo 'selected';}?>><?php echo $product_name['product_name'];?></option>
                                                        <?php
                                                            endforeach;
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Purchase Slip</label>
                                                <div class="col-md-9">
                                                    <input type="hidden" name="purchase_slip" value="<?php echo $products[0]['purchase_slip'];?>" />
                                                    <input type="hidden" name="po_no" value="<?php echo $products[0]['po_no'];?>" />
                                                    <span class="help-inline">
                                                    	<?php
                                                        	if($products[0]['purchase_slip']!=''):
                                                                if ($products[0]['po_no'] == 0):
														?>
                                                                    <a href="<?php echo base_url();?>inventory_images/<?php echo $products[0]['purchase_slip'];?>" target="_blank"><img src="<?php echo base_url();?>inventory_images/<?php echo $products[0]['purchase_slip'];?>" width="100" /></a>
                                                        <?php
                                                                else:
                                                        ?>
                                                                    <a href="<?php echo base_url();?>uploads/<?php echo $products[0]['purchase_slip'];?>" target="_blank"><img src="<?php echo base_url();?>uploads/<?php echo $products[0]['purchase_slip'];?>" width="100" /></a>
                                                                <?php
                                                        	endif;
                                                        	endif;
														?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Product Quantity <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="number" class="form-control input-inline input-large" name="product_quantity" placeholder="Enter Product Quantity" value="<?php echo $products[0]['product_quantity'];?>" required>
                                                    <input type="hidden" class="form-control input-inline input-large" name="total_quantity"  value="<?php echo $products[0]['product_quantity'];?>" required>
                                                    <span class="help-inline"></span>
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
                                            <button type="submit" class="btn green">Move Product</button>
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