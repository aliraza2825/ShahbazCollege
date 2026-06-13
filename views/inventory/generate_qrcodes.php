
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Add Class <small>add your desire class</small>
			</h3>-->
			<!-- END PAGE HEADER-->
			<!-- BEGIN PAGE CONTENT-->
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
								<i class="fa fa-plus"></i> Generate QR Codes
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" target="_blank" action="<?php echo site_url();?>/inventory/qr_code_generator">
								<div class="form-body">
									<div class="form-group">
										<label class="col-md-3 control-label">TYPE</label>
										<div class="col-md-9 radio-list">
											<label class="radio-inline">
												<input type="radio" class="type" name="type"  value="new" checked >New QR Codes</label>
											<label class="radio-inline">
												<input type="radio" class="type" name="type" value="old">Tagged QR Code in Quantity</label>
											<label class="radio-inline">
												<input type="radio" class="type" name="type" value="custom">Custom Range</label>
											<label class="radio-inline">
												<input type="radio" class="type" name="type" value="check">Check Tagged QR Code</label>
										</div>
									</div>
									<div class="new entries" >
										<div class="form-group">
											<label class="col-md-3 control-label">Quantity <span class="required">*</span></label>
											<div class="col-md-9">
												<input type="number" min="1" class="form-control input-inline input-large" name="new_quantity" placeholder="Enter QR Quantity" value="55" >
												<span class="help-inline">You can print maximum 55 QR Codes per page.</span>
											</div>
										</div>
                                    </div>
                                    <div class="old entries" style="display:none;" >
										<div class="form-group">
											<label class="col-md-3 control-label">QR Code Number <span class="required">*</span></label>
											<div class="col-md-9">
												<input type="number" min="1" class="form-control input-inline input-large" name="qr_number" placeholder="Enter QR Number" value="" >
												<span class="help-inline">Enter Tagged QR Code.</span>
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label">Quantity <span class="required">*</span></label>
											<div class="col-md-9">
												<input type="number" min="1" class="form-control input-inline input-large" name="quantity" placeholder="Enter QR Quantity" value="" >
												<span class="help-inline"></span>
											</div>
										</div>
                                    </div>
									<div class="check custom entries" style="display:none;" >
										<div class="form-group">
											<label class="col-md-3 control-label">From Number <span class="required">*</span></label>
											<div class="col-md-9">
												<input type="number" min="1" class="form-control input-inline input-large" name="from_number" placeholder="Enter QR Number" value="" >
												<span class="help-inline">Starting QR Code Number</span>
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label">To Number <span class="required">*</span></label>
											<div class="col-md-9">
												<input type="number" min="1" class="form-control input-inline input-large" name="to_number" placeholder="Enter QR Quantity" value="" >
												<span class="help-inline">Ending QR Code Number</span>
											</div>
										</div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Generate QRs</button>
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