
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
								<i class="fa fa-plus"></i> Add Attendence
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/attendence/insert_machine" enctype="multipart/form-data">
								<div class="form-body">
									
									<div class="form-group">
										<label class="col-md-3 control-label">Machine Campus<span class="required">*</span></label>
										<div class="col-md-5">
											<select class="form-control input-large" name="campus_id" required>
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
										<label class="control-label col-md-3">Machine ID</label>
										<div class="col-md-4">
											<div class="input-group input-large">
												<input type="text" name="machine_id"  class="form-control">
												
											</div>
											<!-- /input-group -->
										</div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Add Machine</button>
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