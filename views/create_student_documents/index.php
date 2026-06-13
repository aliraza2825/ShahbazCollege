<?php
$myAccess = checkUserAccess();
?>
<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <!--<h3 class="page-title">
        All Classes <small>Here you can find all classes</small>
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
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet box grey-cascade">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i> Create Student Documents (For Punjab Pharmacy Council)
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form class="form-horizontal" role="form" method="post" action="#">
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                    <div class="col-md-9">
                                        <select name="campus_id" class="form-control input-inline input-large campus_id" required>
                                            <option value="">SELECT CAMPUS</option>
                                            <?php
                                                foreach($campuses as $campus):
                                            ?>
                                            <option value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name'];?></option>
                                            <?php
                                                endforeach;
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Class <span class="required">*</span></label>
                                    <div class="col-md-9">
                                        <select name="class_id" class="form-control input-inline input-large class_id" required>
                                            
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Students Count <span class="required">*</span></label>
                                    <div class="col-md-9">
                                        <select name="students_count" class="form-control input-inline input-large students_count" required>
                                            
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <button type="button" class="btn green create_documents">Create Documents</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- END EXAMPLE TABLE PORTLET-->
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="portlet box grey-cascade">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i> Response
                        </div>
                    </div>
                    <div class="portlet-body response">
                        
                    </div>
                    <div class="portlet-body processing" style="display:none;">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <i class="fa fa-spinner fa-spin fa-4x" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END PAGE CONTENT-->
    </div>
</div>
<!-- END CONTENT -->