
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
                            <i class="fa fa-plus"></i> Add Quiz Rule
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/mobile_application/insert_points_rules">
                            <div class="form-body">
                              
                                <div class="mcqs" >
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Points on Install <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="number" min="0" class="form-control input-inline input-large" name="install_points" placeholder="Enter number of Points on Install" value="<?php echo @$invite_rules->install_points;?>" required>
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Points on Admission <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="number" min="0" class="form-control input-inline input-large" name="admission_points" placeholder="Enter number of Points on Admission" value="<?php echo @$invite_rules->admission_points;?>" required>
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">How Many Points to One Rupee <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="number" min="0" class="form-control input-inline input-large" name="points_to_rupees" placeholder="How Many Points to One Rupee" value="<?php echo @$invite_rules->points_to_rupees;?>" required>
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
								</div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <button type="submit" class="btn green">Create Rule</button>
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