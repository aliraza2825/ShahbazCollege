
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
								<i class="fa fa-plus"></i> Add Campus
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/campuses/insert" enctype="multipart/form-data">
								<div class="form-body">
									<div class="form-group">
										<label class="col-md-3 control-label">Campus Logo <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="file" class="form-control input-inline input-medium" name="logo" value="" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Campus Code <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="campus_code" placeholder="Enter campus code" value="" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Campus Roll No Code <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="roll_no_code" placeholder="Enter Roll No code" value="" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Campus Name <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="campus_name" placeholder="Enter campus name" value="" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Campus Website <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="website" placeholder="Enter campus website" value="" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Address <span class="required">*</span></label>
										<div class="col-md-9">
                                            <textarea class="form-control" rows="3" name="address" required></textarea>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Advertising SMS <span class="required">*</span></label>
										<div class="col-md-9">
                                            <textarea class="form-control" rows="3" name="sms" required></textarea>
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-3 control-label">SMS Messanger Facebook API <span class="required">*</span></label>
										<div class="col-md-9">
                                            <textarea class="form-control" rows="3" name="facebook_api" ><?php echo $campus['facebook_api'];?></textarea>
										</div>
									</div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Campus Stamp <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="file" class="form-control input-inline input-medium" name="stamp" value="" required>
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Campus Head Stamp <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="file" class="form-control input-inline input-medium" name="head_stamp" value="" required>
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <h2>Numbers For SMS</h2>
                                        <hr />
                                    </div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Phone For Website </label>
										<div class="col-md-9">
                                            <input type="text" class="form-control input-inline input-medium" name="phone" placeholder="Enter phone for website" value="" />
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Phone For Expense </label>
										<div class="col-md-9">
                                            <input type="text" class="form-control input-inline input-medium" name="phone1" placeholder="Enter phone 1" value="" />
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Phone For Expense</label>
										<div class="col-md-9">
                                            <input type="text" class="form-control input-inline input-medium" name="phone2" placeholder="Enter phone 2" value="" />
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Phone For Visitors to contact at</label>
										<div class="col-md-9">
                                            <input type="text" class="form-control input-inline input-medium" name="phone3" placeholder="Enter phone 3" value="" />
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Phone For Staff login Sms Further Query</label>
										<div class="col-md-9">
                                            <input type="text" class="form-control input-inline input-medium" name="phone4" placeholder="Enter phone 4" value="" />
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Phone For Daily Report</label>
										<div class="col-md-9">
                                            <input type="text" class="form-control input-inline input-medium" name="phone5" placeholder="Enter phone 5" value="" />
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Phone For Student Fee,admission,struk off Alert further information, Website Online apllication alert</label>
										<div class="col-md-9">
                                            <input type="text" class="form-control input-inline input-medium" name="phone6" placeholder="Enter phone 6" value="" />
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Phone For CV Management</label>
										<div class="col-md-9">
                                            <input type="text" class="form-control input-inline input-medium" name="phone7" placeholder="Enter phone 7" value="" />
										</div>
									</div>
                                    
                                    <div class="row">
                                    	<div class="col-md-12">
                                        	<h2>For Challan Deatils</h2>
                                            <hr />
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">Bank Name </label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control input-inline input-medium" name="bank_name" placeholder="Bank Name" value="" />
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">Account Number </label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control input-inline input-medium" name="account_no" placeholder="Account Number" value="" />
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">Challan Bottom Note </label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control input-inline input-medium" name="note" placeholder="Challan Bottom account note" value="" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">For Mobile Application</label>
                                        <div class="col-md-9">
                                            <input type="checkbox" class="form-control input-inline input-medium" name="for_mobile_application" value="1"/>
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Add Campus</button>
											<button onclick="location.href = '<?php echo site_url()?>'" type="button" class="btn default">Cancel</button>
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