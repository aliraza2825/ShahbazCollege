
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
								<i class="fa fa-edit"></i> Edit Advertisement Gateway
							</div>
						</div>
						<div class="portlet-body form">
                            <?php
								foreach($sms_gateways as $sms):
							?>
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/sms/advertisement_gateway_update/<?php echo $sms['id']?>">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control campus" name="campus_id">
                                                <option value="">SELECT CAMPUS</option>
                                                <?php 
                                                    foreach($campuses as $campus):
                                                ?>
                                                <option value="<?php echo $campus['campus_id'];?>" <?php if($campus['campus_id']==$sms['campus_id']){echo 'selected';}?>><?php echo $campus['campus_name'];?></option>
                                                <?php
                                                    endforeach;
                                                ?>
                                            </select>
                                            <!--<span class="help-inline"></span>-->
                                        </div>
                                    </div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Email <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="email" class="form-control input-inline input-medium" name="email" placeholder="Enter email" value="<?php echo $sms['email'];?>" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Password</label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="password" placeholder="Enter password" value="<?php echo $sms['password'];?>">
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Device ID </label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="device_id" placeholder="Enter device ID" value="<?php echo $sms['device_id'];?>">
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">API Token </label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="token" placeholder="Enter API Token" value="<?php echo $sms['token'];?>">
											<span class="help-inline"></span>
										</div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Update</button>
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