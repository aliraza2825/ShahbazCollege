
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
								<i class="fa fa-user"></i> Add Visitor
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/visitors/insert">
								<div class="form-body">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control" name="campus">
                                                <?php
													foreach($campuses as $campus):
												?>
                                                <option value="<?php echo $campus['campus_name'];?>"><?php echo $campus['campus_name'];?></option>
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
											<input type="text" class="form-control input-inline input-medium" name="name" placeholder="Enter name" value="" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Father Name</label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="father_name" placeholder="Enter father name" value="">
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Email </label>
										<div class="col-md-9">
											<input type="email" class="form-control input-inline input-medium" name="email" placeholder="Enter email" value="">
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Phone <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="phone" placeholder="Enter phone #" value="" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">City </label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium profile_password" name="city" placeholder="Enter city" value="" >
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Address </label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium profile_password" name="address" placeholder="Retype address" value="">
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Priority</label>
										<div class="col-md-9 radio-list">
											<label class="radio-inline">
											<input type="radio" name="priority" id="optionsRadios4" value="1" checked> Yes </label>
											<label class="radio-inline">
											<input type="radio" name="priority" id="optionsRadios5" value="2"> No </label>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Note</label>
										<div class="col-md-9">
											<div class="col-md-6">
                                            	<textarea class="form-control" rows="3" name="note"></textarea>
                                            </div>
										</div>
									</div>
								</div>
                                
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<input type="hidden" name="add_by" value="<?php echo $this->session->userdata('name');?>" />
                                            <input type="hidden" name="date" value="<?php echo date('Y-m-d H:m:i');?>" />
                                            <button type="submit" class="btn green">Add Visitor</button>
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