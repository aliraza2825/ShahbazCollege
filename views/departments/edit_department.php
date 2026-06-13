	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
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
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-edit"></i> Edit Department 
							</div>
						</div>
                        
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" enctype="multipart/form-data" action="<?php echo site_url();?>/departments/update/<?php echo $this->uri->segment(3);?>">
								<div class="form-body">
									<div class="form-group">
										<label class="col-md-3 control-label">Department Name <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="department_name" placeholder="Enter Department name" value="<?php echo $department[0]['department_name']?>" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                </div>
                                <div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
 											<button type="submit" class="btn green">Update Department</button>
										</div>
									</div>
								</div>
                            </form>
						</div>
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->
	
	
