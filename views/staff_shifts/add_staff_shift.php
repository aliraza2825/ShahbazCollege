	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
            <?php if(@$this->session->userdata('message')):?>
                <div class="alert alert-success">
                    <button class="close" data-close="alert"></button>
                    <span><?php echo $this->session->userdata('message');?></span>
                </div>
            <?php endif;?>
			<?php if(@$this->session->userdata('error')):?>
                <div class="alert alert-danger">
                    <button class="close" data-close="alert"></button>
                    <span><?php echo $this->session->userdata('error');?></span>
                </div>
            <?php endif;?>
			<div class="row">
				<div class="col-md-12">
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-plus"></i> Add Staff Shift
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/staff_shifts/insert">
								<div class="form-body">
                                	<div class="row">
                                        <div class="col-md-12">
											<div class="form-group">
                                                <label class="col-md-3 control-label">Shift Name <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-large" name="shift_name" placeholder="Enter shift name" required>
                                                </div>
                                            </div>
											<div class="form-group">
                                                <label class="col-md-3 control-label">Description</label>
                                                <div class="col-md-9">
                                                    <textarea class="form-control" name="description" rows="3" placeholder="Optional description"></textarea>
                                                </div>
                                            </div>
											<div class="form-group">
                                                <label class="col-md-3 control-label">Status <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select name="status" class="form-control input-inline input-medium" required>
                                                        <option value="1">Active</option>
                                                        <option value="0">Inactive</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-offset-3 col-md-9">
                                            <button type="submit" class="btn green">Add Shift</button>
                                        </div>
                                    </div>
                                </div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
