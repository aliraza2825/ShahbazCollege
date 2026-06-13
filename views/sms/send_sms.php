
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
            	<div class="col-md-12">
                	<button class="btn btn-default blue sms" id="single">Custom Message</button>
                    <button class="btn btn-default sms" id="single_class">Send Message to Class</button>
					<button class="btn btn-default sms" id="whole_college">Inform Students About Next Council Exams</button>
                    <?php if($this->session->userdata('role')=='Admin'):?>
                    <!--<button class="btn btn-default sms" id="whole_college">Send Message to Whole College</button>-->
                    <?php endif;?>
                    <!--<button class="btn btn-default sms" id="selective_students">Send Message to Selective Students</button>
                    <button class="btn btn-default sms" id="staff">Send Message to Staff</button>-->
                </div>
                <br /><br /><br />
            </div>
            
			<div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-envelope"></i> Send SMS
							</div>
						</div>
						<!--SINGLE START-->
                        <div class="portlet-body form single">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/sms/send_sms">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control campus" name="campus_id">
                                                <option value="">SELECT CAMPUS</option>
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
                                    <div class="form-group">
										<label class="col-md-3 control-label">Numbers <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="number" placeholder="eg 03001234567 0321123456" value="" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Message <span class="required">*</span></label>
										<div class="col-md-9">
                                            	<textarea class="form-control" rows="3" name="message" maxlength="250"></textarea>
										</div>
									</div>
                                    <!--<div class="form-group">
                                        <label class="col-md-3 control-label">Device <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control" name="device_id">
                                                <?php
                                                	$sms_gateways = $this->db->get('sms_gateway')->result_array();
													foreach($sms_gateways as $sms_gateway):
												?>
                                                <option value="<?php echo $sms_gateway['id'];?>"><?php echo $sms_gateway['device_id'];?></option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                        </div>
                                    </div>-->
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<input type="hidden" name="add_by" value="<?php echo $this->session->userdata('name');?>" />
                                            <input type="hidden" name="type" value="custom" />
                                            <button type="submit" class="btn green">Send Message</button>
										</div>
									</div>
								</div>
							</form>
						</div>
                        <!--SINGLE END-->
                        <!--SINGLE CLASS-->
                        <div class="portlet-body form single_class" style="display:none;">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/sms/send_sms">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control campus" name="campus_id" required>
                                                <option value="">SELECT CAMPUS</option>
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
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Class <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="class_id" class="form-control input-inline input-lg classes" required>
                                                
                                            </select>
                                        </div>
									</div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Include Contractor</label>
                                        <div class="col-md-9 checkbox-list">
                                            <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="contractor_students" value="1" /> Include Contractor Students </label>
                                            <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="students" value="1" checked="checked" /> Include Students </label>
                                        </div>
                                    </div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Message <span class="required">*</span></label>
										<div class="col-md-9">
                                            	<textarea class="form-control" rows="3" name="message" maxlength="250"></textarea>
										</div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                        	<input type="hidden" name="add_by" value="<?php echo $this->session->userdata('name');?>" />
                                            <input type="hidden" name="type" value="class" />
											<button type="submit" class="btn green">Send Message</button>
										</div>
									</div>
								</div>
							</form>
						</div>
                        <!--SINGLE CLASS-->
                        
                        <!--Whole College-->
                        <div class="portlet-body form whole_college" style="display:none;">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/sms/send_sms" enctype="multipart/form-data">
								<div class="form-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label"> Campus <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select name="campus_id" class="form-control input-inline input-large">
                                                        <?php
                                                            foreach($campuses as $campus):
                                                        ?>
                                                        <option value="<?php echo $campus['campus_id'];?>" <?php if($campus['campus_id']==@$this->input->post('campus_id')){echo 'selected';}?>><?php echo $campus['campus_name'];?></option>
                                                        <?php
                                                            endforeach;
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Class <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <label class="radio-inline">
                                                    <input type="radio" name="class" id="optionsRadios4" value="1" <?php if(@$this->input->post('class')==1){echo 'checked';}?> /> 1st year </label>
                                                    <label class="radio-inline">
                                                    <input type="radio" name="class" id="optionsRadios5" value="2" <?php if(@$this->input->post('class')==2){echo 'checked';}?> /> 2nd Year </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Exam # <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select name="council_exam_no" class="form-control input-inline input-medium council_exam_no">
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
										<div class="clearfix"></div>
										<div class="col-md-12">
											<div class="table" style="border:1px solid #DDD;">
												<table class="table table-bordered table-hover">
													<thead>
														<tr>
															<th>Class</th>
															<th>Student Name</th>
															<th>Contractor</th>
															<th>CNIC</th>
															<th>Roll No</th>
															<th>Fee Remarks</th>
															<th>Submit Fee</th>
															<th>Fee Created By</th>
															<th>Fee Status</th>
														</tr>
													</thead>
													<tbody class="council_students">
														
													</tbody>
												</table>
											</div>
										</div>
										<div class="col-md-12">
											<div class="form-group">
												<label class="col-md-3 control-label">Message <span class="required">*</span></label>
												<div class="col-md-9">
														<textarea class="form-control" rows="3" name="message" maxlength="250"></textarea>
												</div>
											</div>
										</div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                        	<input type="hidden" name="add_by" value="<?php echo $this->session->userdata('name');?>" />
                                            <input type="hidden" name="type" value="council" />
											<button type="submit" class="btn green">Send Message</button>
										</div>
									</div>
								</div>
							</form>
						</div>
                        <!--WHole College-->
                        <!--SELECTIVE STUDENTS-->
                        <div class="portlet-body form selective_students" style="display:none;">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/sms/send_sms">
								<div class="form-body">
									<div class="form-group">
										<label class="col-md-3 control-label">Number <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="number[]" placeholder="Enter number" value="" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Message <span class="required">*</span></label>
										<div class="col-md-9">
                                            	<textarea class="form-control" rows="3" name="message" maxlength="250"></textarea>
										</div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<input type="hidden" name="add_by" value="<?php echo $this->session->userdata('name');?>" />
                                            <button type="submit" class="btn green">Send Message</button>
										</div>
									</div>
								</div>
							</form>
						</div>
                        <!--SELECTIVE STUDENTS-->
                        <!--STAFF-->
                        <div class="portlet-body form staff" style="display:none;">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/sms/send_sms">
								<div class="form-body">
									<div class="form-group">
										<label class="col-md-3 control-label">Number <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="number[]" placeholder="Enter number" value="" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Message <span class="required">*</span></label>
										<div class="col-md-9">
                                            	<textarea class="form-control" rows="3" name="message" maxlength="250"></textarea>
										</div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<input type="hidden" name="add_by" value="<?php echo $this->session->userdata('name');?>" />
                                            <button type="submit" class="btn green">Send Message</button>
										</div>
									</div>
								</div>
							</form>
						</div>
                        <!--STAFF-->
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->