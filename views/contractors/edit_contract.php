
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Add Class <small>add your desire class</small>
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
			<div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-edit"></i> Edit Contract
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/contractors/update_contract/<?php echo $contract[0]['contract_id'];?>" enctype="multipart/form-data">
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Select Contractor <span class="required">*</span></label>
                                                <div class="col-md-5">
                                                    <select class="form-control input-large" name="contractor_id" required>
                                                        <option value="">Select Contractor</option>
                                                        <?php
                                                        	foreach($contractors as $contractor):
														?>
                                                        <option value="<?php echo $contractor['contractor_id'];?>" <?php if($contract[0]['contractor_id']==$contractor['contractor_id']){echo 'selected';}?>><?php echo $contractor['contractor_id_from_college'];?> &nbsp;&nbsp;&nbsp;&nbsp; <?php echo $contractor['name'];?></option>
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
                                                <label class="col-md-3 control-label">Select Course <span class="required">*</span></label>
                                                <div class="col-md-5">
                                                    <select class="form-control input-large" name="course_id" required>
                                                        <option value="">Select Course</option>
                                                        <?php
                                                        	foreach($courses as $course):
														?>
                                                        <option value="<?php echo $course['course_id'];?>" <?php if($contract[0]['course_id']==$course['course_id']){echo 'selected';}?>><?php echo $course['course_name'];?></option>
                                                        <?php
                                                        	endforeach;
														?>
                                                    </select>
                                                    <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Contract Name <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-large" name="contract_name" placeholder="Enter Contract Name" value="<?php echo $contract[0]['contract_name'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                                <div class="col-md-5">
                                                    <select class="form-control" name="campus_id">
                                                        <option value="">SELECT CAMPUS</option>
														<?php 
															foreach($campuses as $campus):
														?>
                                                        <option value="<?php echo $campus['campus_id'];?>" <?php if($campus['campus_id']==$contract[0]['campus_id']){echo 'selected';}?>><?php echo $campus['campus_name'];?></option>
                                                        <?php
                                                        	endforeach;
														?>
                                                    </select>
                                                    <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Session <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-large" name="session" placeholder="Enter session" value="<?php echo $contract[0]['session'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label col-md-3">Contract Date</label>
                                                <div class="col-md-3">
                                                    <div class="input-group input-large date date-picker" data-date="<?php echo $contract[0]['contract_date'];?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                        <input type="text" name="contract_date" class="form-control" value="<?php echo $contract[0]['contract_date'];?>" readonly>
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
                                                <label class="col-md-3 control-label">Total Students <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-large" name="total_students" placeholder="Enter number of students" value="<?php echo $contract[0]['total_students'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Per Student Fee <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-large" name="per_student_fee" placeholder="Enter single student fee" value="<?php echo $contract[0]['per_student_fee'];?>" required>
                                                    <span class="help-inline">Fee without board or council fee</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                    	<div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Contract Documents </label>
                                                <div class="col-md-9">
                                                    <input type="file" name="contract_documents[]" multiple="" />
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                            	<?php
                                                	foreach($contract_documents as $contract_document):
												?>
                                                <a href="<?php echo base_url();?>contract_images/<?php echo $contract_document['image']?>" target="_blank"><img width="100" src="<?php echo base_url();?>contract_images/<?php echo $contract_document['image']?>" alt="" /></a>
                                                <?php
                                                	endforeach;
												?>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Other Documents </label>
                                                <div class="col-md-9">
                                                    <input type="file" name="other_documents[]" multiple="" />
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                            	<?php
                                                	foreach($other_documents as $other_document):
												?>
                                                <a href="<?php echo base_url();?>contract_images/<?php echo $other_document['image']?>" target="_blank"><img width="100" src="<?php echo base_url();?>contract_images/<?php echo $other_document['image']?>" alt="" /></a>
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
						</div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->