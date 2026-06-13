
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Edit Profile <small>You can edit your profile here</small>
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
								<i class="fa fa-money"></i> Pharmacy Technician Extra Study Rule
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/rules/add_council_fee_rules" enctype="multipart/form-data">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label"> Fee Amount <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="number" class="form-control input-inline input-large" name="total_fee" id="total_fee" placeholder="Enter Total Fee" value="<?php echo @$feerule['total_fee'] ?>" min="0" required >
                                            <span class="help-inline">Extra Fee Created when council exam count</span>
                                        </div>
                                    </div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">No of Exams we Deal <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="number" class="form-control input-inline input-large" name="no_of_exams" id="no_of_exams" placeholder="Enter Exams we Deal" value="<?php echo @$feerule['no_of_exams'] ?>" min="0" required >
                                            <span class="help-inline">Extra Fee Exempted Upto Council Exam no.</span>
                                        </div>
                                    </div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Council Fee for Contractors <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="number" class="form-control input-inline input-large" name="min_council_fee" id="no_of_exams" placeholder="Enter Exams we Deal" value="<?php echo @$feerule['min_council_fee'] ?>" min="0" required>
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Council Fee for Students <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="number" class="form-control input-inline input-large" name="max_council_fee" id="no_of_exams" placeholder="Enter Exams we Deal" value="<?php echo @$feerule['max_council_fee'] ?>" min="0" required>
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
                                    <div class="form-actions">
                                        <div class="row">
                                            <div class="col-md-offset-3 col-md-9">
                                                <button type="submit" class="btn green">Add Rule</button>
                                                <button onclick="location.href = '<?php echo site_url()?>'" type="button" class="btn default">Cancel</button>
                                            </div>
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