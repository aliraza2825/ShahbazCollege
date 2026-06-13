<?php
$myAccess = checkUserAccess();
?>
<style>
    .btn{
        margin-bottom:10px;
    }
</style>
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
        <!-- END PAGE CONTENT-->
        <?php if ($myAccess[0]['extra_fee_access'] == "1"):?>
        <div class="row">
            <div class="col-md-12 ">
                <!-- BEGIN SAMPLE FORM PORTLET-->
                <div class="portlet box green ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-plus"></i> Add Extra Fee
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/accounts/bulk_fee_creation" enctype="multipart/form-data">
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="col-md-4 control-label">Campus <span class="required">*</span></label>
                                            <div class="col-md-8">
                                                <select class="form-control input-large campus" name="campus_id">
                                                    <option value="">SELECT CAMPUS</option>
                                                    <?php 
                                                        foreach($campuses as $campus):
                                                    ?>
                                                    <option value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name'];?></option>
                                                    <?php
                                                        endforeach;
                                                    ?>
                                                </select>
                                                <!--<span class="help-inline"></span>-->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="col-md-4 control-label">Class <span class="required">*</span></label>
                                            <div class="col-md-8">
                                                <select class="form-control input-large classes" name="class_id">
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="col-md-4 control-label">Fee Type <span class="required">*</span></label>
                                            <div class="col-md-8">
                                                <select class="form-control input-medium fee_type" id="my_fee_type" name="fee_type" required>
                                                    <option value="">SELECT FEE TYPE</option>
                                                    <option value="College Fee">College Fee</option>
                                                    <option value="consulation fee">Council Fee</option>
                                                    <option value="Extra Fee">Extra Fee</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="col-md-4 control-label">Extra Fee Amount <span class="required">*</span></label>
                                            <div class="col-md-8">
                                                <input type="number" min="0" class="form-control input-inline input-large" name="extra_fee" id="extra_fee_amount" value="" placeholder="Enter Extra Fee Amount" required/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="col-md-4 control-label">Dead Line <span class="required">*</span></label>
                                            <div class="col-md-8">
                                                <div class="input-group input-large date date-picker" data-date="<?php echo date('Y-m-d');?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                    <input type="text" name="extra_fee_dead_line" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
                                                    <span class="input-group-btn">
                                                    <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="col-md-4 control-label">Purpose <span class="required">*</span></label>
                                            <div class="col-md-8">
                                                <input type="text" class="form-control input-inline input-medium payment_comment" name="payment_comment" value="" readonly required />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="col-md-2 control-label">Special Comment <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <textarea class="form-control" name="special_comment"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                    <div class="col-md-4 extra_fee" style="display:none;">
                                        <div class="form-group">
                                            <label class="col-md-4 control-label">Fee For <span class="required">*</span></label>
                                            <div class="col-md-8">
                                                <select class="form-control input-large fee_for" name="fee_for" required>
                                                    <option value="">SELECT FEE FOR</option>
                                                    <option value="Books">Books</option>
                                                    <option value="Notes">Notes</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 consulation_fee" style="display:none;">
                                        <div class="form-group">
                                            <label class="col-md-4 control-label">Class <span class="required">*</span></label>
                                            <div class="col-md-8 radio-list">
                                                <label class="radio-inline">
                                                    <input type="radio" name="class" id="optionsRadios4" value="1st Year" checked> First Year </label>
                                                <label class="radio-inline">
                                                    <input type="radio" name="class" id="optionsRadios5" value="2nd Year"> Second Year </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 consulation_fee" style="display:none;">
                                        <div class="form-group">
                                            <label class="col-md-4 control-label">Council Exam No. <span class="required">*</span></label>
                                            <div class="col-md-8">
                                                <input type="text" class="form-control input-inline input-large exam_no" name="exam_no" value="" required />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <button type="submit" class="btn green">Add Extra Fee</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- END SAMPLE FORM PORTLET-->
            </div>
        </div>
        <?php endif;?>
    </div>
</div>
<!-- END CONTENT -->
 
<script>
	document.addEventListener("DOMContentLoaded", function(event) {
        jQuery('.fee_type').change(function(){
            var fee_type = jQuery(this).val();
            //alert(fee_type);
            jQuery('.payment_comment').val(fee_type);
            if(fee_type=='College Fee')
            {
                jQuery('.fee_for').removeAttr('required');
                jQuery('.exam_no').removeAttr('required');
                jQuery('.extra_fee').hide();
                jQuery('.consulation_fee').hide();
            }
            if(fee_type=='consulation fee')
            {
                jQuery('.fee_for').removeAttr('required');
                jQuery('.exam_no').attr('required');
                jQuery('.extra_fee').hide();
                jQuery('.consulation_fee').show();
            }
            if(fee_type=='Extra Fee')
            {
                jQuery('.fee_for').attr('required');
                jQuery('.exam_no').removeAttr('required');
                jQuery('.extra_fee').show();
                jQuery('.consulation_fee').hide();
            }
        });
        jQuery('.campus').change(function(){
            var campus_id = jQuery(this).val();

            if(campus_id!='')
            {
                jQuery.ajax({
                    type: "post",
                    async: false,
                    url: '<?php echo site_url()?>/students/getCampusClasses',
                    data: {
                        campus_id : campus_id,
                    },
                    success: function(data) {
                        console.log(data);
                        jQuery('.classes').html(data);
                    }

                });
            }
        });
    });
</script>