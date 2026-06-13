
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
                            <i class="fa fa-plus"></i> Assign Leaves
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/leaves/insert_leaves" enctype="multipart/form-data">
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h2>Leaves Assign Details</h2>
                                        <hr />
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-md-3 control-label"> Staff Type </label>
                                            <div class="col-md-9">
                                                <select name="staff_type_id" class="form-control input-inline input-large">
                                                    <option value="">SELECT STAFF TYPE</option>
                                                    <?php
                                                    foreach($staff_types as $staff_type):
                                                        ?>
                                                        <option value="<?php echo $staff_type['staff_type_id'];?>"><?php echo $staff_type['staff_type_name'];?></option>
                                                    <?php
                                                    endforeach;
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-md-3 control-label"> Department </label>
                                            <div class="col-md-9">
                                                <select name="department_id" class="form-control input-inline input-large department_id">
                                                    <option value="">SELECT DEPARTMENT</option>
                                                    <?php
                                                    foreach($departments as $department):
                                                        ?>
                                                        <option value="<?php echo $department['department_id'];?>"><?php echo $department['department_name'];?></option>
                                                    <?php
                                                    endforeach;
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-md-3 control-label"> User </label>
                                            <div class="col-md-9">
                                                <select name="user_id" class="form-control input-inline input-large department_id" >
                                                    <option value="">SELECT USER</option>
                                                    <?php
                                                    foreach($users as $user):
                                                        ?>
                                                        <option value="<?php echo $user['user_id'];?>"><?php echo $user['username'].' ( '.$user['role'].' )';?></option>
                                                    <?php
                                                    endforeach;
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">Role </label>
                                            <div class="col-md-9">
                                                <select class="form-control input-inline input-medium" name="role" >
                                                    <option value="">Select Role</option>
                                                    <option value="Teacher">Teacher</option>
                                                    <option value="Principal">Principal</option>
                                                    <option value="Accountant">Accountant</option>
                                                    <option value="Guard">Guard</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="col-md-4 control-label">Select Leave Type <span class="required">*</span></label>
                                            <div class="col-md-8">
                                                <select class="form-control input-inline input-medium" name="leave_type" required>
                                                    <option value="">SELECT Leaves Type</option>
                                                    <?php
                                                    foreach($leaves as $leave):
                                                        ?>
                                                        <option value="<?php echo $leave['id'];?>"><?php echo $leave['leavetype'] . ' (No of leaves = '. $leave['no_of_leaves'] .' )';?></option>
                                                    <?php
                                                    endforeach;
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <input type="hidden" name="status" value="1" />
                                        <button type="submit" class="btn green">Assign Leaves Now</button>

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