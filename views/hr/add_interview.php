
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
								<i class="fa fa-user"></i> Add Interview
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/hr/insert_interview" enctype="multipart/form-data">
								<div class="form-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                                <div class="col-md-5">
                                                    <select class="form-control" name="campus_id">
                                                        <?php
                                                            foreach($campuses as $campus):
                                                        ?>
                                                        <option value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name'];?></option>
                                                        <?php
                                                            endforeach;
                                                        ?>
                                                    </select>
                                                    <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Name <span class="required">*</span></label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control input-inline input-large" name="name" placeholder="Enter Name" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Address <span class="required">*</span></label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control input-inline input-large" name="address" placeholder="Enter Address" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Qualification <span class="required">*</span></label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control input-inline input-large" name="qualification" placeholder="Enter Qualification" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Timing <span class="required">*</span></label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control input-inline input-large" name="timing" placeholder="Enter Timing" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Personality <span class="required">*</span></label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control input-inline input-large" name="personality" placeholder="Enter Personality" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">IQ Level <span class="required">*</span></label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control input-inline input-large" name="iq_level" placeholder="Enter IQ Level" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Salary Offer Responce <span class="required">*</span></label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control input-inline input-large" name="salary_offer_responce" placeholder="Enter Salary Responce" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Salary Demand <span class="required">*</span></label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control input-inline input-large" name="salary_demand" placeholder="Enter Salary Demand" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Other Current Job <span class="required">*</span></label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control input-inline input-large" name="other_current_job" placeholder="Enter Current job" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Previous Experience <span class="required">*</span></label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control input-inline input-large" name="previous_experience" placeholder="Enter Previous Experience" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                            <label class="col-md-4 control-label">Gender</label>
                                            <div class="col-md-8 radio-list">
                                                <label class="radio-inline">
                                                <input type="radio" name="gender" id="optionsRadios4" value="male" checked> Male </label>
                                                <label class="radio-inline">
                                                <input type="radio" name="gender" id="optionsRadios5" value="female"> Female </label>
                                            </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                            <label class="col-md-4 control-label">Marital Status</label>
                                            <div class="col-md-8 radio-list">
                                                <label class="radio-inline">
                                                <input type="radio" name="marital_status" id="optionsRadios4" value="single" checked> Single </label>
                                                <label class="radio-inline">
                                                <input type="radio" name="marital_status" id="optionsRadios5" value="married"> Married </label>
                                            </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                            <label class="col-md-4 control-label">Grantable</label>
                                            <div class="col-md-8 radio-list">
                                                <label class="radio-inline">
                                                <input type="radio" name="grantable" id="optionsRadios4" value="yes" checked> Yes </label>
                                                <label class="radio-inline">
                                                <input type="radio" name="grantable" id="optionsRadios5" value="no"> No </label>
                                            </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Guarantee Person <span class="required">*</span></label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control input-inline input-large" name="guarantee_person" placeholder="Enter Guarantee Person" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Father's Occupation <span class="required">*</span></label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control input-inline input-large" name="father_occupation" placeholder="Enter Father's Occupation" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Job Post Wanted <span class="required">*</span></label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control input-inline input-large" name="job_post_wanted" placeholder="Enter Job Post Wanted" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                            <label class="col-md-4 control-label">Residence</label>
                                            <div class="col-md-8 radio-list">
                                                <label class="radio-inline">
                                                <input type="radio" name="residence" id="optionsRadios4" value="own" checked> Own </label>
                                                <label class="radio-inline">
                                                <input type="radio" name="residence" id="optionsRadios5" value="rental"> Rental </label>
                                            </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                            <label class="col-md-4 control-label">Expert IN</label>
                                            <div class="col-md-8 radio-list">
                                                <label class="radio-inline">
                                                <input type="checkbox" name="expert_in[]" id="optionsRadios4" value="word" checked> Word </label>
                                                <label class="radio-inline">
                                                <input type="checkbox" name="expert_in[]" id="optionsRadios5" value="excel"> Excel </label>
                                                <label class="radio-inline">
                                                <input type="checkbox" name="expert_in[]" id="optionsRadios5" value="power point"> Power Point </label>
                                            </div>
                                            </div>
                                        </div>
                                        <br style="clear:both" />
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Cell Number <span class="required">*</span></label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control input-inline input-large" name="cell_number" placeholder="Enter Cell Number" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Upload CV </label>
                                                <div class="col-md-8">
                                                    <input type="file" class="form-control input-inline input-large" name="cv" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-2 control-label">Suggestion of Interview <span class="required">*</span></label>
                                                <div class="col-md-10">
                                                    <textarea name="suggestion" rows="10" class="form-control"></textarea>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                            <label class="col-md-4 control-label">Reviews</label>
                                            <div class="col-md-8 radio-list">
                                                <label class="radio-inline">
                                                <input type="radio" name="reviews" id="optionsRadios4" value="interested" checked> Interested </label>
                                                <label class="radio-inline">
                                                <input type="radio" name="reviews" id="optionsRadios5" value="not interested"> Not Interested </label>
                                            </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                            <label class="col-md-4 control-label">Your Opinion</label>
                                            <div class="col-md-8 radio-list">
                                                <label class="radio-inline">
                                                <input type="radio" name="your_opinion" id="optionsRadios5" value="final" checked> Final </label>
                                                <label class="radio-inline">
                                                <input type="radio" name="your_opinion" id="optionsRadios5" value="rejected"> Rejected </label>
                                                <label class="radio-inline">
                                                <input type="radio" name="your_opinion" id="optionsRadios5" value="stay for future"> Stay For Future </label>
                                            </div>
                                            </div>
                                        </div>
                                    </div>
								</div>
                                
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<input type="hidden" name="add_by" value="<?php echo $this->session->userdata('name');?>" />
                                            <input type="hidden" name="date" value="<?php echo date('Y-m-d');?>" />
                                            <button type="submit" class="btn green">Add Interview</button>
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