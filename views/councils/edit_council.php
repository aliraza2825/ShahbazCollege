
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
								<i class="fa fa-edit"></i> Edit Council
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/councils/update/<?php echo $council[0]['council_id'];?>">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Select Courses <span class="required">*</span></label>
                                        <div class="col-md-6">
                                            <select class="form-control select2" name="course_ids[]" id="select2_sample1" multiple required>
                                                <?php
                                                $selected_courses = explode(',',$council[0]['course_ids']);
                                                foreach($courses as $course):
                                                ?>
                                                    <option value="<?php echo $course['course_id'];?>" <?php if(in_array($course['course_id'], $selected_courses)){echo 'selected';}?>><?php echo $course['course_name'];?></option>
                                                <?php
                                                endforeach;
                                                ?>
                                            </select>
                                        </div>
                                    </div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Council Name <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control input-inline input-large" name="name" placeholder="Enter council name" value="<?php echo $council[0]['name'];?>" required>
											<span class="help-inline"></span>
                                        </div>
									</div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Council Code <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control input-inline input-large" name="code" placeholder="Enter council code" value="<?php echo $council[0]['code'];?>" required>
											<span class="help-inline"></span>
                                        </div>
									</div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Council Phone <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control input-inline input-large" name="phone" placeholder="Enter council phone" value="<?php echo $council[0]['phone'];?>" required>
											<span class="help-inline"></span>
                                        </div>
									</div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Council Address <span class="required">*</span></label>
                                        <div class="col-md-6">
                                            <textarea class="form-control" rows="3" name="address"><?php echo $council[0]['address'];?></textarea>
                                        </div>
									</div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Google Map Location <span class="required">*</span></label>
                                        <div class="col-md-6">
                                            <textarea class="form-control" rows="3" name="location"><?php echo $council[0]['location'];?></textarea>
                                        </div>
									</div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Comment <span class="required">*</span></label>
                                        <div class="col-md-6">
                                            <textarea class="form-control" rows="3" name="comment"><?php echo $council[0]['comment'];?></textarea>
                                        </div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Update Council</button>
											<button onclick="location.href = '<?php echo site_url()?>/councils/all_councils'" type="button" class="btn default">Cancel</button>
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