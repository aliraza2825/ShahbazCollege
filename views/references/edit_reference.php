
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
								<i class="fa fa-edit"></i> Edit Reference User
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/references/update/<?php echo $this->uri->segment(3);?>">
								<div class="form-body">
                                    <div class="form-group">
										<label class="col-md-3 control-label">Name <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="name" placeholder="Enter name" value="<?php echo $reference[0]['name'];?>" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Phone <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="phone" placeholder="Enter phone #" value="<?php echo $reference[0]['phone'];?>" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Note</label>
										<div class="col-md-9">
                                            	<textarea class="form-control" rows="3" name="note"><?php echo $reference[0]['note'];?></textarea>
										</div>
									</div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Status <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control input-inline input-medium" name="status">
                                                <option value="1" <?php if($reference[0]['status']==1){echo 'selected';}?>>Active</option>
												<option value="0" <?php if($reference[0]['status']==0){echo 'selected';}?>>Deactive</option>
                                            </select>
                                            <!--<span class="help-inline"></span>-->
                                        </div>
                                    </div>
								</div>
                                
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                            <button type="submit" class="btn green">Update Reference</button>
											<button onclick="location.href = '<?php echo site_url();?>'" type="button" class="btn default">Cancel</button>
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