
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
								<i class="fa fa-plus"></i> Add Vendor
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/inventory/insert_vendor" enctype="multipart/form-data">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="campus_id" class="form-control input-inline input-large" required>
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
									<div class="form-group">
										<label class="col-md-3 control-label">Vendor Products <span class="required">*</span></label>
										<div class="col-md-9">
											<select class="form-control select2"  name="product_name_ids[]" multiple>
												<?php
												foreach($product_names as $product_name):
													?>
													<option value="<?php echo $product_name['product_name_id'];?>">
														<?php echo $product_name['product_name'];?>
													</option>
												<?php
												endforeach;
												?>
											</select>
											<!--<span class="help-inline"></span>-->
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-3 control-label">Name <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-large" name="name" placeholder="Enter Vendor Name" value="" required>
											<span class="help-inline"></span>
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-3 control-label">Shop Name <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-large" name="shop_name" placeholder="Enter Vendor's Shop Name" value="" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Phone <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-large" name="phone" placeholder="Enter Vendor's Phone Number" value="" required>
											<span class="help-inline"></span>
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-3 control-label">Address <span class="required">*</span></label>
										<div class="col-md-9">
											<textarea class="form-control input-inline input-large" rows="3" name="address" required></textarea>
										</div>
									</div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Image </label>
                                        <div class="col-md-9">
                                            <input type="file" name="image"  value="" />
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
									<div class="form-group">
										<label class="col-md-3 control-label">Type</label>
										<div class="col-md-9 radio-list">
											<label class="radio-inline">
												<input type="radio" name="status" value="active" checked >Active</label>
											<label class="radio-inline">
												<input type="radio" name="status" value="inactive">Inactive</label>
										</div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Add Vendor</button>
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