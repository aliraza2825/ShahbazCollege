
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
								<i class="fa fa-edit"></i> Edit Campus Rules
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/rules/update_campus_rules" enctype="multipart/form-data">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control campus" name="campus_id" required>
                                                <option value="">Select Campus</option>
												<?php
													foreach($campuses as $campus):
												?>
                                                <option value="<?php echo $campus['campus_id'];?>" <?php if($campus['campus_id']==$campus_rule[0]['campus_id']){echo 'selected';}?>><?php echo $campus['campus_name'];?></option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                            <!--<span class="help-inline"></span>-->
                                        </div>
                                    </div>
									<?php
										$course_ids = $campus_rule[0]['course_ids'];
									?>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Courses <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select id="select2_sample_modal_2" class="form-control courses select2" name="course_ids[]" multiple required>
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
									<div class="timing">
									
									</div>
									<div class="form-group">
										<label class="col-md-3 control-label">Campus Property</label>
										<div class="col-md-9 radio-list">
											<label class="radio-inline">
											<input type="radio" class="campus_property" name="campus_property" id="optionsRadios1" value="own" checked> Own </label>
											<label class="radio-inline">
											<input type="radio" class="campus_property" name="campus_property" id="optionsRadios2" value="rent"> Rent </label>
										</div>
									</div>
									<div class="campus_rent_amount" style="display:none;">
										<div class="form-group">
											<label class="col-md-3 control-label">Campus Rent</label>
											<div class="col-md-9">
												<input type="text" class="form-control input-inline input-large" name="campus_property_rent" placeholder="Enter Campus Property Rent" value="" />
												<span class="help-inline"></span>
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label">Campus Rent Increase After (Years)</label>
											<div class="col-md-9">
												<input type="text" class="form-control input-inline input-large" name="campus_property_rent_increase_after" placeholder="Enter Campus Rent Increase After Years" value="" />
												<span class="help-inline"></span>
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label">Campus Rent Increase Percentage</label>
											<div class="col-md-9">
												<input type="text" class="form-control input-inline input-large" name="campus_property_rent_increase_percentage" placeholder="Enter Campus Rent Increase Percentage" value="" />
												<span class="help-inline"></span>
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label">Campus Rent Increase Month</label>
											<div class="col-md-9">
												<select class="form-control input-large campus" name="campus_property_rent_increase_month">
													<option value="">Select Month</option>
													<option value="January">January</option>
													<option value="February">February</option>
													<option value="March">March</option>
													<option value="April">April</option>
													<option value="May">May</option>
													<option value="June">June</option>
													<option value="July">July</option>
													<option value="August">August</option>
													<option value="September">September</option>
													<option value="October">October</option>
													<option value="November">November</option>
													<option value="December">December</option>
												</select>
												<!--<span class="help-inline"></span>-->
											</div>
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-3 control-label">Fee Submit in Bank</label>
										<div class="col-md-9 checkbox-list">
											<label class="checkbox-inline">
											<input type="checkbox" class="bank_fee" id="inlineCheckbox1" name="bank_fee" value="1" /></label>
										</div>
									</div>
									<div class="bank_details" style="display:none;">
										<div class="banks">
											<div class="form-group">
												<label class="col-md-3 control-label">Bank Name</label>
												<div class="col-md-9">
													<input type="text" class="form-control input-inline input-large" name="bank_name[]" placeholder="Enter Bank Name" value="" />
													<span class="help-inline"></span>
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">Account Title</label>
												<div class="col-md-9">
													<input type="text" class="form-control input-inline input-large" name="account_title[]" placeholder="Enter Account Title" value="" />
													<span class="help-inline"></span>
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">Account Number</label>
												<div class="col-md-9">
													<input type="text" class="form-control input-inline input-large" name="account_number[]" placeholder="Enter Account Number" value="" />
													<span class="help-inline"></span>
												</div>
											</div>
										</div>
										<button type="button" class="btn green add_more_bank"><i class="fa fa-plus"></i> Add More Bank</button>
									</div>
									<div class="form-group">
										<label class="col-md-3 control-label">Fee Submit in College</label>
										<div class="col-md-9 checkbox-list">
											<label class="checkbox-inline">
											<input type="checkbox" id="inlineCheckbox2" name="college_fee" value="1" /></label>
										</div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Add Rule</button>
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