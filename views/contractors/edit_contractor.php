
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Edit Class <small>You can edit this class</small>
			</h3>-->
            <!-- BEGIN DASHBOARD STATS -->
			<div class="row">
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat blue-madison">
						<div class="visual">
							<i class="icon-map"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php echo $count;?>
							</div>
							<div class="desc">
								 Contractors
							</div>
						</div>
						<!--<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
-->					</div>
				</div>
			</div>
			<!-- END DASHBOARD STATS -->
            
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
								<i class="fa fa-edit"></i> Edit Contractor
							</div>
						</div>
						<div class="portlet-body form">
                        	<?php 
								foreach($contractor as $contractr):
							?>
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/contractors/update/<?php echo $this->uri->segment(3);?>">
								<div class="form-body">
									<div class="row">
                                    	<div class="col-md-12">
                                        	<h2>Contactor Personal Information</h2>
                                            <hr />
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Contractor ID <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-large" name="contractor_id_from_college" placeholder="Enter contractor ID from college" value="<?php echo $contractr['contractor_id_from_college']?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Password <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-large" name="password" placeholder="Enter contractor password" value="" required />
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Name <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-large" name="name" placeholder="Enter contractor name" value="<?php echo $contractr['name']?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Father Name <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-large" name="father_name" placeholder="Enter contractor's father name" value="<?php echo $contractr['father_name']?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Gender <span class="required">*</span></label>
                                                <div class="col-md-5">
                                                    <select class="form-control" name="gender" required>
                                                        <option <?php if($contractr['gender']=='Male'){echo 'selected';}?>>Male</option>
                                                        <option <?php if($contractr['gender']=='Female'){echo 'selected';}?>>Female</option>
                                                    </select>
                                                    <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Email</label>
                                                <div class="col-md-9">
                                                    <input type="email" class="form-control input-inline input-large" name="email" placeholder="Enter contractor's email" value="<?php echo $contractr['email']?>" >
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">CNIC <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-large" name="cnic" placeholder="Enter contractor's cnic" id="cnic" value="<?php echo $contractr['cnic']?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label col-md-3">Date of birth</label>
                                                <div class="col-md-3">
                                                    <div class="input-group input-large date date-picker" data-date="<?php echo $contractr['dob']?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                        <input type="text" name="dob" class="form-control" value="<?php echo $contractr['dob']?>" readonly>
                                                        <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Mobile <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-large" name="mobile" placeholder="Enter contractor's mobile" value="<?php echo $contractr['mobile']?>" required />
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Emergency No. <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-large" name="emergency_no" placeholder="Enter contractor's emergency no" value="<?php echo $contractr['emergency_no']?>" required />
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Address</label>
                                                <div class="col-md-9">
                                                    <textarea class="form-control" name="address"><?php echo $contractr['address']?></textarea>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                    	<div class="col-md-12">
                                        	<hr />
                                            <h2>Contactor Profession Information</h2>
                                            <hr />
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">College Name <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-large" name="college_name" placeholder="Enter college name" value="<?php echo $contractr['college_name']?>" required />
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">College Address <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-large" name="college_address" placeholder="Enter college address" value="<?php echo $contractr['college_address']?>" required />
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">College Phone1 <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-large" name="college_phone1" placeholder="Enter college phone 1" value="<?php echo $contractr['college_phone1']?>" required />
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">College Phone2 <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-large" name="college_phone2" placeholder="Enter college phone 2" value="<?php echo $contractr['college_phone2']?>" required />
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">College City <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-large" name="college_city" placeholder="Enter college city" value="<?php echo $contractr['college_city']?>" required />
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">College Email</label>
                                                <div class="col-md-9">
                                                    <input type="email" class="form-control input-inline input-large" name="college_email" placeholder="Enter college phone 2" value="<?php echo $contractr['college_email']?>" />
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">College Website </label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-large" name="college_website" placeholder="Enter college website" value="<?php echo $contractr['college_website']?>" />
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                    	<div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Contractor Documents </label>
                                                <div class="col-md-9">
                                                    <input type="file" name="contractor_documents[]" multiple="" />
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                            	<?php
                                                	foreach($contractor_documents as $contractor_document):
												?>
                                                <a href="<?php echo base_url();?>contract_images/<?php echo $contractor_document['image']?>" target="_blank"><img width="100" src="<?php echo base_url();?>contract_images/<?php echo $contractor_document['image']?>" alt="" /></a>
                                                <?php
                                                	endforeach;
												?>
                                            </div>
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Update Contractor</button>
											<button onclick="location.href = '<?php echo site_url()?>/contractors/all_contractors'" type="button" class="btn default">Cancel</button>
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