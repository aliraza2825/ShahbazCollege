
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Add Teacher <small>You can add teacher here</small>
			</h3>-->
			<!-- END PAGE HEADER-->
            <!-- BEGIN DASHBOARD STATS -->
			<!-- END DASHBOARD STATS -->
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
								<i class="fa fa-edit"></i> Edit News &amp; Updates
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/mobile_app/update_news_updates/<?php echo $this->uri->segment(3);?>" >
								<div class="form-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Courses <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select class="form-control" id="select2_sample2" name="course_ids[]" multiple required>
                                                        <?php
                                                            foreach($courses as $course):
                                                        ?>
                                                        <option value="<?php echo $course['course_id'];?>" <?php if(in_array($course['course_id'], explode(',',$news_update[0]['course_ids']))){echo 'selected';}?>><?php echo $course['course_name'];?></option>
                                                        <?php
                                                            endforeach;
                                                        ?>
                                                    </select>
                                                    <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">News <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <textarea class="wysihtml5 form-control" rows="6" name="news"><?php echo $news_update[0]['news']?></textarea>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Update News &amp; Updates</button>
											<button onclick="location.href = '<?php echo site_url()?>/mobile_app/manage_news_updates'" type="button" class="btn default">Cancel</button>
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