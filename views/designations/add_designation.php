	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			All Teachers <small>Here you can find all teachers</small>
			</h3>-->
            <!-- BEGIN DASHBOARD STATS -->
			<!-- END DASHBOARD STATS -->
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
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-plus"></i> Add Designation 
							</div>
						</div>
                        
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" enctype="multipart/form-data" action="<?php echo site_url();?>/designations/insert">
								<div class="form-body">
                                	<div class="row">
                                        <div class="col-md-12">
											<div class="form-group">
                                                <label class="col-md-3 control-label">Department <span class="required">*</span></label>
                                                <div class="col-md-5">
                                                    <select class="form-control input-large" name="department_id" required>
                                                        <option value="">SELECT DEPARTMENT</option>
														<?php
															foreach($departments as $department):
														?>
														<option value="<?php echo $department['department_id'];?>"><?php echo $department['department_name'];?></option>
														<?php
															endforeach;
														?>
                                                    </select>
                                                </div>
                                            </div>
											<div class="form-group">
                                                <label class="col-md-3 control-label">Designation Name <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-large" name="designation_name" placeholder="Enter designation name" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Description <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <textarea class="form-control remarks" rows="4" name="description" required></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
 											<button type="submit" class="btn green">Add Designation</button>
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
	
	
