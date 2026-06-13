	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
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
								<i class="fa fa-edit"></i> Edit Website Content
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/pages/update/<?php echo $content[0]['website_content_id']?>" enctype="multipart/form-data">
								<div class="form-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                        	<div class="form-group">
                                                <label class="col-md-2 control-label">Campus Name <span class="required">*</span></label>
                                                <div class="col-md-10">
                                                    <select class="form-control" name="campus_id" required>
                                                        <option value="">Select Campus</option>
														<?php
                                                            foreach($campuses as $campus):
                                                        ?>
                                                        <option value="<?php echo $campus['campus_id'];?>" <?php if($campus['campus_id']==$content[0]['campus_id']){echo 'selected';}?>><?php echo $campus['campus_name'];?></option>
                                                        <?php
                                                            endforeach;
                                                        ?>
                                                    </select>
                                                    <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <h2>Home Page Text</h2>
                                            <hr />
                                        </div>
                                        <div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Point 1 <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-large" name="point_1" placeholder="Enter point 1" value="<?php echo $content[0]['point_1'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Point 1 Explanation <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <textarea class="form-control input-inline input-large" name="point_1_explanation" required><?php echo $content[0]['point_1_explanation'];?></textarea>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Point 2 <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-large" name="point_2" placeholder="Enter point 2" value="<?php echo $content[0]['point_2'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Point 2 Explanation <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <textarea class="form-control input-inline input-large" name="point_2_explanation" required><?php echo $content[0]['point_2_explanation'];?></textarea>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4"></div>
                                        <div class="col-md-4">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Image <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="file" name="point_center_image"  value=""  />
                                                    <span class="help-inline"><img src="<?php echo base_url().'uploads/'.$content[0]['point_center_image'];?>" width="100" /></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4"></div>
                                        <div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Point 3 <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-large" name="point_3" placeholder="Enter point 3" value="<?php echo $content[0]['point_3'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Point 3 Explanation <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <textarea class="form-control input-inline input-large" name="point_3_explanation" required><?php echo $content[0]['point_3_explanation'];?></textarea>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Point 4 <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-large" name="point_4" placeholder="Enter point 4" value="<?php echo $content[0]['point_4'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Point 4 Explanation <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <textarea class="form-control input-inline input-large" name="point_4_explanation" required><?php echo $content[0]['point_4_explanation'];?></textarea>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Image Left<span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="file" name="home_left_image"  value=""  />
                                                    <span class="help-inline"><img src="<?php echo base_url().'uploads/'.$content[0]['home_left_image'];?>" width="100" /></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                        	<div class="form-group">
                                                <label class="col-md-2 control-label">Heading <span class="required">*</span></label>
                                                <div class="col-md-10">
                                                    <input type="text" class="form-control input-inline input-large" name="home_right_heading" placeholder="Heading" value="<?php echo $content[0]['home_right_heading'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-2 control-label">Paragraph 1 <span class="required">*</span></label>
                                                <div class="col-md-10">
                                                    <textarea class="form-control" name="home_right_paragraph" required><?php echo $content[0]['home_right_paragraph'];?></textarea>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label class="col-md-2 control-label">Paragraph 2 <span class="required">*</span></label>
                                                <div class="col-md-10">
                                                    <textarea class="form-control" name="home_left_paragraph" required><?php echo $content[0]['home_left_paragraph'];?></textarea>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Image Right<span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="file" name="home_right_image"  value=""  />
                                                    <span class="help-inline"><img src="<?php echo base_url().'uploads/'.$content[0]['home_right_image'];?>" width="100" /></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <h2></h2>
                                            <hr />
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                        	<input type="hidden" name="point_center_image_old" value="<?php echo $content[0]['point_center_image'];?>" />
                                            <input type="hidden" name="home_left_image_old" value="<?php echo $content[0]['home_left_image'];?>" />
                                            <input type="hidden" name="home_right_image_old" value="<?php echo $content[0]['home_right_image'];?>" />
											<button type="submit" class="btn green">Update Content</button>
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