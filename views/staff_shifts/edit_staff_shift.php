	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
            <?php if(@$this->session->userdata('message')):?>
                <div class="alert alert-success">
                    <button class="close" data-close="alert"></button>
                    <span><?php echo $this->session->userdata('message');?></span>
                </div>
            <?php endif;?>
			<div class="row">
				<div class="col-md-12">
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-edit"></i> Edit Staff Shift
							</div>
						</div>
						<div class="portlet-body form">
                            <?php foreach($staff_shift as $shift): ?>
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/staff_shifts/update/<?php echo $shift['staff_shift_id']; ?>">
								<div class="form-body">
                                	<div class="row">
                                        <div class="col-md-12">
											<div class="form-group">
                                                <label class="col-md-3 control-label">Shift Name <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-large" name="shift_name" value="<?php echo $shift['shift_name']; ?>" required>
                                                </div>
                                            </div>
											<div class="form-group">
                                                <label class="col-md-3 control-label">Study Type <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select name="study_type_id" class="form-control input-inline input-large" required>
                                                        <option value="">SELECT STUDY TYPE</option>
                                                        <?php foreach($study_types as $study_type): ?>
                                                            <option value="<?php echo $study_type['id']; ?>" <?php if((int) $shift['study_type_id'] === (int) $study_type['id']){echo 'selected';} ?>><?php echo $study_type['name']; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
											<div class="form-group">
                                                <label class="col-md-3 control-label">Description</label>
                                                <div class="col-md-9">
                                                    <textarea class="form-control" name="description" rows="3"><?php echo $shift['description']; ?></textarea>
                                                </div>
                                            </div>
											<div class="form-group">
                                                <label class="col-md-3 control-label">Status <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select name="status" class="form-control input-inline input-medium" required>
                                                        <option value="1" <?php if((int) $shift['status'] === 1){echo 'selected';}?>>Active</option>
                                                        <option value="0" <?php if((int) $shift['status'] === 0){echo 'selected';}?>>Inactive</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-offset-3 col-md-9">
                                            <button type="submit" class="btn green">Update Shift</button>
                                        </div>
                                    </div>
                                </div>
							</form>
                            <?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
