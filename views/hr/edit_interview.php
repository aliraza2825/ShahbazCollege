
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
								<i class="fa fa-edit"></i> Edit Interview
							</div>
						</div>
						<div class="portlet-body form">
                            <?php
                            	foreach($interviews as $interview):
							?>
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/hr/update_interview/<?php echo $interview['interview_id']?>" enctype="multipart/form-data">
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
                                                        <option value="<?php echo $campus['campus_id'];?>" <?php if($interview['campus_id']==$campus['campus_id']){echo 'selected';}?>><?php echo $campus['campus_name'];?></option>
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
                                                    <input type="text" class="form-control input-inline input-large" name="name" placeholder="Enter Name" value="<?php echo $interview['name'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Address <span class="required">*</span></label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control input-inline input-large" name="address" placeholder="Enter Address" value="<?php echo $interview['address'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Qualification <span class="required">*</span></label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control input-inline input-large" name="qualification" placeholder="Enter Qualification" value="<?php echo $interview['qualification'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Timing <span class="required">*</span></label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control input-inline input-large" name="timing" placeholder="Enter Timing" value="<?php echo $interview['timing'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Personality <span class="required">*</span></label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control input-inline input-large" name="personality" placeholder="Enter Personality" value="<?php echo $interview['personality'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">IQ Level <span class="required">*</span></label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control input-inline input-large" name="iq_level" placeholder="Enter IQ Level" value="<?php echo $interview['iq_level'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Salary Offer Responce <span class="required">*</span></label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control input-inline input-large" name="salary_offer_responce" placeholder="Enter Salary Responce" value="<?php echo $interview['salary_offer_responce'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Salary Demand <span class="required">*</span></label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control input-inline input-large" name="salary_demand" placeholder="Enter Salary Demand" value="<?php echo $interview['salary_demand'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Other Current Job <span class="required">*</span></label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control input-inline input-large" name="other_current_job" placeholder="Enter Current job" value="<?php echo $interview['other_current_job'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Previous Experience <span class="required">*</span></label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control input-inline input-large" name="previous_experience" placeholder="Enter Previous Experience" value="<?php echo $interview['previous_experience'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                            <label class="col-md-4 control-label">Gender</label>
                                            <div class="col-md-8 radio-list">
                                                <label class="radio-inline">
                                                <input type="radio" name="gender" id="optionsRadios4" value="male" <?php if($interview['gender']=='male'){echo 'checked';}?>> Male </label>
                                                <label class="radio-inline">
                                                <input type="radio" name="gender" id="optionsRadios5" value="female" <?php if($interview['gender']=='female'){echo 'checked';}?>> Female </label>
                                            </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                            <label class="col-md-4 control-label">Marital Status</label>
                                            <div class="col-md-8 radio-list">
                                                <label class="radio-inline">
                                                <input type="radio" name="marital_status" id="optionsRadios4" value="single" <?php if($interview['marital_status']=='single'){echo 'checked';}?>> Single </label>
                                                <label class="radio-inline">
                                                <input type="radio" name="marital_status" id="optionsRadios5" value="married" <?php if($interview['marital_status']=='married'){echo 'checked';}?>> Married </label>
                                            </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                            <label class="col-md-4 control-label">Grantable</label>
                                            <div class="col-md-8 radio-list">
                                                <label class="radio-inline">
                                                <input type="radio" name="grantable" id="optionsRadios4" value="yes" <?php if($interview['grantable']=='yes'){echo 'checked';}?>> Yes </label>
                                                <label class="radio-inline">
                                                <input type="radio" name="grantable" id="optionsRadios5" value="no" <?php if($interview['grantable']=='no'){echo 'checked';}?>> No </label>
                                            </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Guarantee Person <span class="required">*</span></label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control input-inline input-large" name="guarantee_person" placeholder="Enter Guarantee Person" value="<?php echo $interview['guarantee_person']?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Father's Occupation <span class="required">*</span></label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control input-inline input-large" name="father_occupation" placeholder="Enter Father's Occupation" value="<?php echo $interview['father_occupation']?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Job Post Wanted <span class="required">*</span></label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control input-inline input-large" name="job_post_wanted" placeholder="Enter Job Post Wanted" value="<?php echo $interview['job_post_wanted']?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                            <label class="col-md-4 control-label">Residence</label>
                                            <div class="col-md-8 radio-list">
                                                <label class="radio-inline">
                                                <input type="radio" name="residence" id="optionsRadios4" value="own" <?php if($interview['residence']=='own'){echo 'checked';}?>> Own </label>
                                                <label class="radio-inline">
                                                <input type="radio" name="residence" id="optionsRadios5" value="rental" <?php if($interview['residence']=='rental'){echo 'checked';}?>> Rental </label>
                                            </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                            <label class="col-md-4 control-label">Expert IN</label>
                                            <div class="col-md-8 radio-list">
                                                <label class="radio-inline">
                                                <input type="checkbox" name="expert_in[]" id="optionsRadios4" value="word" <?php if(in_array('word',explode(',',$interview['expert_in']))){echo 'checked';}?>> Word </label>
                                                <label class="radio-inline">
                                                <input type="checkbox" name="expert_in[]" id="optionsRadios5" value="excel" <?php if(in_array('excel',explode(',',$interview['expert_in']))){echo 'checked';}?>> Excel </label>
                                                <label class="radio-inline">
                                                <input type="checkbox" name="expert_in[]" id="optionsRadios5" value="power point" <?php if(in_array('power point',explode(',',$interview['expert_in']))){echo 'checked';}?>> Power Point </label>
                                            </div>
                                            </div>
                                        </div>
                                        <br style="clear:both" />
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Cell Number <span class="required">*</span></label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control input-inline input-large" name="cell_number" placeholder="Enter Cell Number" value="<?php echo $interview['cell_number'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Upload CV </label>
                                                <div class="col-md-8">
                                                    <input type="file" class="form-control input-inline input-large" name="cv" value="">
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-2 control-label">Suggestion of Interview <span class="required">*</span></label>
                                                <div class="col-md-10">
                                                    <textarea name="suggestion" rows="10" class="form-control"><?php echo $interview['suggestion'];?></textarea>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                            <label class="col-md-4 control-label">Reviews</label>
                                            <div class="col-md-8 radio-list">
                                                <label class="radio-inline">
                                                <input type="radio" name="reviews" id="optionsRadios4" value="interested" <?php if($interview['reviews']=='interested'){echo 'checked';}?>> Interested </label>
                                                <label class="radio-inline">
                                                <input type="radio" name="reviews" id="optionsRadios5" value="not interested" <?php if($interview['reviews']=='not interested'){echo 'checked';}?>> Not Interested </label>
                                            </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                            <label class="col-md-4 control-label">Your Opinion</label>
                                            <div class="col-md-8 radio-list">
                                                <label class="radio-inline">
                                                <input type="radio" name="your_opinion" id="optionsRadios5" value="final" <?php if($interview['your_opinion']=='final'){echo 'checked';}?>> Final </label>
                                                <label class="radio-inline">
                                                <input type="radio" name="your_opinion" id="optionsRadios5" value="rejected" <?php if($interview['your_opinion']=='rejected'){echo 'checked';}?>> Rejected </label>
                                                <label class="radio-inline">
                                                <input type="radio" name="your_opinion" id="optionsRadios5" value="stay for future" <?php if($interview['your_opinion']=='stay for future'){echo 'checked';}?>> Stay For Future </label>
                                            </div>
                                            </div>
                                        </div>
                                    </div>
								</div>
                                
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                            <input type="hidden" name="old_cv" value="<?php echo $interview['cv'];?>" />
                                            <button type="submit" class="btn green">Update Interview</button>
										</div>
									</div>
								</div>
							</form>
                            <?php
                            	endforeach;
							?>
						</div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->