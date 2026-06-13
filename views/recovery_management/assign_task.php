
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
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
								<i class="fa fa-plus"></i> Assign Task
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/recovery_management/set_comission">
								<div class="form-body">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="campus_id" class="form-control input-inline input-large campus_id" required>
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
                                        <label class="col-md-3 control-label">Department <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="department_id" class="form-control input-inline input-large department_id" required>
                                                
                                            </select>
                                        </div>
									</div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Designation <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="designation_id" class="form-control input-inline input-large designation_id" required>
                                                
                                            </select>
                                        </div>
									</div>
									 <div class="form-group">
                                                <label class="col-md-3 control-label">Course <span class="required">*</span></label>
                                                <div class="col-md-5">
                                                    <select class="form-control  select2" id="select2_sample2" name="course_id[]" multiple  required>
                                                        
														<?php 
															foreach($courses as $course):
														?>
                                                        <option value="<?php echo $course['course_id'];?>"><?php echo $course['course_name'];?></option>
                                                        <?php
                                                        	endforeach;
														?>
                                                    </select>
                                                    <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Assign Campuses <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control select2" id="select2_sample1" name="campus_ids[]" multiple>
                                                <?php 
                                                    foreach($campuses as $campus):
                                                ?>
                                                <option value="<?php echo $campus['campus_id'];?>">
                                                    <?php echo $campus['campus_name'];?>
                                                </option>
                                                <?php
                                                    endforeach;
                                                ?>
                                            </select>
                                            <!--<span class="help-inline"></span>-->
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <hr />
                                            <h4>ASSIGN INSENTIVE ON FINE COLLECTION</h4>
                                            <hr />
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-5 control-label">Minimum Fine Amount <span class="required">*</span></label>
                                                <div class="col-md-7">
                                                    <input type="number" min="0" class="form-control input-inline input-medium" name="min_fine_amount" value="" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-5 control-label">Percentage Given On Fine Amount <span class="required">*</span></label>
                                                <div class="col-md-7">
                                                    <input type="number" min="0" class="form-control input-inline input-medium" name="fine_amount_percentage" value="" required />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
										<div class="col-md-12">
											<hr />
											<h4>ASSIGN INSENTIVE ON FEE INSTALLMENT PAID</h4>
											<hr />
										</div>
									</div>
									<div class="comission_area">
										
									</div>
									<button type="button" class="btn green add_line"><i class="fa fa-plus"></i> Add Rule</button>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Assign Task</button>
											<button onclick="location.href = '<?php echo base_url();?>'" type="button" class="btn default">Cancel</button>
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