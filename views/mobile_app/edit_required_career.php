
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Add Class <small>add your desire class</small>
			</h3>-->
			<!-- END PAGE HEADER-->
			<!-- BEGIN PAGE CONTENT-->
            <?php
                $count = 0;

            if(@$this->session->userdata('message')):?>
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
                                <i class="fa fa-edit" aria-hidden="true"></i>
                                Edit Required Career
                            </div>
                        </div>
                        <div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/mobile_app/update_required_career/<?php echo $required_career[0]['required_career_id'];?>"  enctype="multipart/form-data">
                                <div class="form-body row">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Required Career <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="text" name="required_career" class="form-control input-inline input-large" value="<?php echo $required_career[0]['required_career'];?>" required/>
                                        </div>
                                    </div>    
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Status <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="status" class="form-control input-inline input-large" required>
                                                <option value="">Select Status</option>
                                                <option value="1" <?php if($required_career[0]['status']==1){echo 'selected';}?>>Active</option>
                                                <option value="0" <?php if($required_career[0]['status']==0){echo 'selected';}?>>Deactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button  type="submit" class="btn green">Update</button>
										</div>
									</div>
								</div>
                            </form>
                        </div>

                    </div>
                    <!-- END SAMPLE FORM PORTLET-->
                </div>
            </div>
        </div>
    </div>
    <!-- END CONTENT -->