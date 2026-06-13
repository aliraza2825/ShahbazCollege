
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
								<i class="fa fa-envelope"></i> Send SMS
							</div>
						</div>
						<!--SINGLE START-->
                        <div class="portlet-body form single">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/sms/send_advertisement_sms">
								<div class="form-body">
									<div class="form-group">
										<label class="col-md-3 control-label">Numbers <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="number" placeholder="eg 03001234567 0321123456" value="" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Message <span class="required">*</span></label>
										<div class="col-md-9">
                                            	<textarea class="form-control" rows="3" name="message" maxlength="250" id="length_counter"></textarea>
                                                <span class="help-inline"><span class="length_counter">0</span> words</span>
										</div>
									</div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Device <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control" name="device_id">
                                                <?php
                                                	$sms_gateways = $this->db->get('sms_gateway')->result_array();
													foreach($sms_gateways as $sms_gateway):
												?>
                                                <option value="<?php echo $sms_gateway['id'];?>"><?php echo $sms_gateway['device_id'];?></option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<input type="hidden" name="add_by" value="<?php echo $this->session->userdata('name');?>" />
                                            <input type="hidden" name="type" value="advertisement" />
                                            <button type="submit" class="btn green">Send Message</button>
										</div>
									</div>
								</div>
							</form>
						</div>
                        <!--SINGLE END-->
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->