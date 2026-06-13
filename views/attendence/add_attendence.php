
<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
    <div class="page-content">
        <?php
        $access_values = $this->db->get_where('access',array('user_id'=>$this->session->userdata('user_id')))->result_array();
        ?>
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
                            <i class="fa fa-plus"></i> Add Attendence
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/attendence/insert" enctype="multipart/form-data">
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Staff / Students <span class="required">*</span></label>
                                    <div class="col-md-5">
                                        <select class="form-control input-large type" name="type">
                                            <?php
                                            foreach(@$access_values[0]['attendence_add_types'] ? explode(",",$access_values[0]['attendence_add_types']) : [] as $type):?>
                                                <option value="<?php echo strtolower($type);?>"><?php echo $type;?></option>
                                            <?php
                                            endforeach;
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Study Campus<span class="required">*</span></label>
                                    <div class="col-md-5">
                                        <select class="form-control input-large campus_id" name="find_campus" required>
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
                                    <label class="col-md-3 control-label">Attendence Machine Campus<span class="required">*</span></label>
                                    <div class="col-md-5">
                                        <select class="form-control input-large" name="campus_id" required>
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
                                    <label class="col-md-3 control-label">Staff / Student Name <span class="required">*</span></label>
                                    <div class="col-md-5">
                                        <select class="form-control input-large staff" id="select2_sample4" name="machine_user_ids[]" multiple>

                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Checkin / Checkout</label>
                                    <div class="col-md-9 radio-list">
                                        <label class="radio-inline">
                                            <input type="radio" name="section" id="optionsRadios4" value="checkin" checked> Checkin </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="section" id="optionsRadios5" value="checkout"> Checkout </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Date / Time</label>
                                    <div class="col-md-4">
                                        <div class="input-group input-large date form_datetime">
                                            <input type="text" name="datetime" size="16" readonly class="form-control">
                                            <span class="input-group-btn">
												<button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
												</span>
                                        </div>
                                        <!-- /input-group -->
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <button type="submit" class="btn green">Add Attendence</button>
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