
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
								<i class="fa fa-edit"></i> Edit Paper
							</div>
						</div>
						<div class="portlet-body form">
                            <?php
                            	foreach($papers as $paper):
							?>
                            <form class="form-horizontal" role="form" method="post" enctype="multipart/form-data" action="<?php echo site_url();?>/papers/update/<?php echo $paper['paper_id'];?>">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Subject <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="subject_id" class="form-control input-inline input-medium">
                                                <?php
                                                	foreach ($subjects as $subject):
												?>
                                                <option value="<?php echo $subject['subject_id']?>" <?php if($subject['subject_id']==$paper['subject_id']){echo 'selected=selected';}?>><?php echo $subject['name']?> (<?php echo $this->db->get_where('classes', array('class_id'=>$subject['class_id']))->row()->name;?>)</option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                        </div>
									</div>
                                    <div class="form-group">
                                        <label class="control-label col-md-3">Date <span class="required">*</span></label>
                                        <div class="col-md-3">
                                            <div class="input-group input-medium date date-picker" data-date="<?php echo $paper['date'];?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                <input type="text" name="date" class="form-control" value="<?php echo $paper['date'];?>" readonly>
                                                <span class="input-group-btn">
                                                <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
										<label class="control-label col-md-3">Upload Paper <span class="required">*</span></label>
										<div class="col-md-9">
											<button type="button" class="btn blue" onclick="location.href = '<?php echo base_url();?>uploads/<?php echo $paper['content'];?>'">Uploaded Paper</button>
                                            <br />
                                            <br />
                                            <input type="file" name="content"  value="" />
										</div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<input type="hidden" name="teacher_id" value="<?php echo $this->session->userdata('user_id');?>" />
                                            <button type="submit" class="btn green">Update Paper</button>
											<button onclick="location.href = '<?php echo site_url();?>/papers/all_papers'" type="button" class="btn default">Cancel</button>
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