
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Edit Class <small>You can edit this class</small>
			</h3>-->
            
			<!-- BEGIN PAGE CONTENT-->
            <?php if(@$this->session->userdata('message')):?>
                <div class="alert alert-success">
                    <button class="close" data-close="alert"></button>
                    <span>
                    <?php echo $this->session->userdata('message');?> </span>
                </div>
            <?php endif;?>
			<div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-desktop"></i> Zoom Setup
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/zoom/update" enctype="multipart/form-data">
								<div class="form-body">
                                    <?php
										foreach($zooms as $zoom):
									?>
									<div class="form-group">
										<label class="col-md-3 control-label">Zoom Personal Meeting ID (<?php echo $zoom['title'];?>) <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-large" name="personal_meeting_ids[]" placeholder="Enter Zoom Personal Meeting ID" value="<?php echo $zoom['personal_meeting_id'];?>" required>
											<span class="help-inline"></span>
										</div>
									</div>
									<?php
										endforeach;
									?>
									<div class="form-group">
										<label class="col-md-3 control-label">Admisssion Timings <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-large" name="admission_timing" placeholder="Admission Timings" value="<?php echo $zooms[0]['admission_timing'];?>" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Upload Document / Syllabus </label>
										<div class="col-md-9">
											<input type="file" name="image"  value="" />
											<span class="help-inline">
												<?php
													if($zooms[0]['image']!=''):
												?>
												<a href="<?php echo base_url();?>zoom_images/<?php echo $zooms[0]['image'];?>" target="_blank">
													<img src="<?php echo base_url();?>zoom_images/<?php echo $zooms[0]['image'];?>" width="200" />
												</a>
												<?php
													endif;
												?>
											</span>
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
						</div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->