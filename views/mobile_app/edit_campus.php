
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
								<i class="fa fa-edit"></i> Edit Campus (<?php echo $campus[0]['campus_name'];?>)
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/mobile_app/update_campus/<?php echo $campus[0]['campus_id'];?>" enctype="multipart/form-data">
								<div class="form-body">
									<div class="form-group">
										<label class="col-md-3 control-label">Campus Picture <span class="required">*</span></label>
										<div class="col-md-9">
                                            <div class="add_logo" style="display:none;">
                                                <input type="file" class="form-control input-inline input-medium" name="logo" value="" />
                                                <span class="help-inline"></span>
                                            </div>
                                            <?php
                                                if($campus[0]['campus_image']!=''):
                                            ?>
                                            <img width="300" class="campus_logo" src="<?php echo $campus[0]['campus_image'];?>" />
                                            <?php
                                                endif;
                                            ?>
                                            <input type="file" class="form-control input-inline input-medium" name="campus_image" value="" />
											<span class="help-inline"></span>
                                            <input type="hidden" name="old_campus_image" value="<?php echo $campus[0]['campus_image'];?>" />
										</div>
									</div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Receptionist Number <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control input-inline input-large" value="<?php echo @$receptionist[0]['phone'];?>" readonly />
                                            <span class="help-inline">Important Note: Receptionist College Phone Number will show on All Campuses Mobile Screen.</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label"> Phone # By Designation <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="designation_id[]" class="form-control input-inline input-large designation_id select2" id="select2_sample1" multiple required>
                                                <?php
                                                    foreach($designations as $designation):
                                                ?>
                                                <option value="<?php echo $designation['designation_id'];?>" <?php if(in_array($designation['designation_id'], explode(',',@$campus[0]['designation_ids']))){echo 'selected';}?>><?php echo $designation['designation_name'];?></option>
                                                <?php
                                                    endforeach;
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Google Map Link <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control input-inline input-large" name="google_map_link" value="<?php echo $campus[0]['google_map_link'];?>" required />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Facebook Link</label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control input-inline input-large" name="facebook" value="<?php echo $campus[0]['facebook'];?>" />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Twitter Link</label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control input-inline input-large" name="twitter" value="<?php echo $campus[0]['twitter'];?>" />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Whatsapp Number</label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control input-inline input-large" placeholder="+92314-------" name="whatsapp" value="<?php echo str_replace('https://wa.me/','',$campus[0]['whatsapp']);?>" />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">For Mobile App <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="checkbox" class="form-control input-inline input-medium" name="mobile_status" value="1" <?php if ($campus[0]['mobile_status'] == 1) echo "checked";?>/>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Campus Details <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <div class="col-md-12">
                                                <textarea class="wysihtml5 form-control" rows="15" name="content"><?php echo $campus[0]['content'];?></textarea>
                                            </div>
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Update Campus</button>
											<button onclick="location.href = '<?php echo site_url();?>/mobile_app/manage_campuses'" type="button" class="btn default">Cancel</button>
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