
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
								<i class="fa fa-user"></i> Add Supply Students
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/supply/insert">
								<div class="form-body">
									<div class="form-group">
                                      <label class="control-label col-md-3">Students <span class="required">*</span></label>
                                      <div class="col-md-9">
                                          <select id="select2_sample_modal_2" class="form-control select2" name="students[]" multiple required>
                                              <?php
                                              	foreach($students as $student):
											  ?>
                                              <option value="<?php echo $student['student_id'];?>"><?php echo $student['first_name'].' '.$student['last_name'].' ('.$student['roll_no'].')';?></option>
                                              <?php
                                              	endforeach;
											  ?>
                                          </select>
                                      </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-3">Last Date <span class="required">*</span></label>
                                        <div class="col-md-3">
                                            <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                <input type="text" name="dead_line" class="form-control" value="<?php echo date('Y-m-d');?>" readonly required>
                                                <span class="input-group-btn">
                                                <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                </span>
                                            </div>
                                            <!-- /input-group -->
                                            <!--<span class="help-block">
                                            Select date </span>-->
                                        </div>
                                    </div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Fee for students <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="number" class="form-control input-inline input-medium" name="fee_for_students" placeholder="Enter fee for students" value="" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Fee for contractors <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="number" class="form-control input-inline input-medium" name="fee_for_contractors" placeholder="Enter fee for contractors" value="" required>
											<span class="help-inline"></span>
										</div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Add Fee</button>
											<button onclick="location.href = '<?php echo site_url()?>'" type="button" class="btn default">Cancel</button>
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