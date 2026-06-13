
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
								<i class="fa fa-user"></i> Edit Profile
							</div>
						</div>
						<div class="portlet-body form">
							<?php
								foreach ($users as $user):
							?>
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/profile/update">
								<div class="form-body">
									<div class="form-group">
										<label class="col-md-3 control-label">First Name <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="first_name" placeholder="Enter first name" value="<?php echo @$user['first_name']?>" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Last Name <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="last_name" placeholder="Enter last name" value="<?php echo @$user['last_name']?>" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Email <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="email" class="form-control input-inline input-medium" name="email" placeholder="Enter email" value="<?php echo @$user['email']?>" readonly="readonly" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Username <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="username" placeholder="Enter username" value="<?php echo @$user['username']?>" readonly="readonly" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Password <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="password" class="form-control input-inline input-medium profile_password" name="password" placeholder="Enter password" value="" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Retype Password <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="password" class="form-control input-inline input-medium profile_password" name="r-password" placeholder="Retype password" value="" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Designations <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <?php 
                                                $designations = $this->db->where_in('designation_id', explode(",",$user['designation_id']))
                                                        ->get('designations')
                                                        ->result_array();
                                                foreach($designations as $designation):
                                            ?>
                                            <input type="text" class="form-control input-inline input-medium" name="role"  value="<?php echo @$designation['designation_name'].' '.$designation['description']?>" readonly="readonly" required>
                                            <?php 
                                            endforeach;
                                            ?>
                                        </div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                        	<input type="hidden" name="status" value="1" />
											<button type="submit" class="btn green">Update Profile</button>
											<button onclick="location.href = '<?php echo site_url()?>'" type="button" class="btn default">Cancel</button>
										</div>
									</div>
								</div>
							</form>
                            <?php
                            	endforeach;
							?>
						</div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->