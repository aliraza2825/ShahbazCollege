<?php
$myAccess = checkUserAccess();
?>
<style>

    .radio-group{
        position: relative;
    }

    .radio{
        display:inline-block;
        border-radius: 2px;
        width: 120px;
        border: 2px solid lightblue;
        cursor:pointer;
        margin: 5px 0;
        background: cadetblue;
    }

    .radio.selected{
        border-color: cadetblue;
        background-color: darkgreen;
        color: white;
    }

</style>

	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			

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
								<i class="fa fa-money"></i> Add Student's (<?php echo $student[0]['first_name'].' '.$student[0]['last_name']; ?>) Payments Plan
							</div>
						</div>
                        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

                        <div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/students/add_payment_plan/<?php echo $this->uri->segment(3)?>" enctype="multipart/form-data">
								<div class="form-body">


                                    <div class="radio-group" required >

                                        <?php $i=0; foreach ($plans as $plan): ?>

                                        <div class="col-md-12 col-sm-12 col-xs-12 form-group " style="border: 2px solid black; margin: 5px">


                                            <div class='radio' data-index="<?php echo $i ?>" data-value="<?php echo $plan['fee_rule_id']?>" style="
                                                width: 120px;
                                                text-align: center;
                                                font-size: large;
                                                padding: 0px;
                                            "> PLAN <?php echo ($i+1)?></div>
                                            <br>
                                            <div class="col-md-4 col-sm-4 col-xs-4 form-group ">
                                                            <label class="col-md-5 control-label">Total Fee :</label>
                                                            <div class="col-md-7">
                                                                <input type="text" class="form-control input-inline " name="total_fee" placeholder="" value="<?php echo $plan['total_fee'] ?>" readonly>
                                                                <span class="help-inline"></span>
                                                            </div>
                                                        </div>
                                            <div class="col-md-4 col-sm-4 col-xs-4 form-group ">
                                                            <label class="col-md-5 control-label">Admission Fee :</label>
                                                            <div class="col-md-7">
                                                                <input type="text" class="form-control input-inline " name="first_installment" placeholder="" value="<?php echo $plan['installment_on_admission'] ?>" readonly>
                                                                <span class="help-inline"></span>
                                                            </div>
                                                        </div>
                                            <div class="col-md-4 col-sm-4 col-xs-4 form-group ">
                                                            <label class="col-md-5 control-label">Fee Per Installment :</label>
                                                            <div class="col-md-7">
                                                                <input type="text" class="form-control input-inline " name="per_installment" placeholder="" value="<?php echo $plan['per_installment_fee'] ?>" readonly>
                                                                <span class="help-inline"></span>
                                                            </div>
                                                        </div>
                                            <div class="col-md-4 col-sm-4 col-xs-4 form-group ">
                                                <label class="col-md-5 control-label">Installment After Months:</label>
                                                <div class="col-md-7">
                                                    <input type="text" class="form-control input-inline " name="after_month" placeholder="" value="<?php echo $plan['difference_in_installments_months'] ?>" readonly>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-4 col-xs-4 form-group ">
                                                <label class="col-md-5 control-label">Last Day of Each Installment:</label>
                                                <div class="col-md-7">
                                                    <input type="text" class="form-control input-inline " name="lastday" placeholder="" value="<?php echo $plan['paid_date_each_installment'] ?>" readonly>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-4 col-xs-4 form-group ">
                                                <label class="col-md-5 control-label">Per day Fine:</label>
                                                <div class="col-md-7">
                                                    <input type="text" class="form-control input-inline " name="fine_perday" placeholder="" value="<?php echo $plan['late_fee_per_day_fine'] ?>" readonly>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-4 col-xs-4 form-group ">
                                                <label class="col-md-5 control-label">Council Fee:</label>
                                                <div class="col-md-7">
                                                    <input type="text" class="form-control input-inline " name="council_fee" placeholder="" value="<?php echo $plan['council_board_fee'] ?>" readonly>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-4 col-xs-4 form-group ">
                                                <label class="col-md-5 control-label">Council Last Date:</label>
                                                <div class="col-md-7">
                                                    <input type="text" class="form-control input-inline " name="council_last_date" placeholder="" value="<?php echo $plan['last_date_council_fee'] ?>" readonly>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-4 col-xs-4 form-group ">
                                                <label class="col-md-5 control-label">No of installments:</label>
                                                <div class="col-md-7">
                                                    <input type="text" class="form-control input-inline " name="council_last_date" placeholder="" value="<?php echo $plan['no_of_installments'] ?>" readonly>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-4 col-xs-4 form-group ">
                                                <label class="col-md-5 control-label">Discount Given on Per Merge Installment:</label>
                                                <div class="col-md-7">
                                                    <input type="text" class="form-control input-inline " name="council_last_date" placeholder="" value="<?php echo $plan['disc_per_inst'] ?>" readonly>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>


                                        </div>
                                            <br/>
                                        <?php $i++; endforeach; ?>


                                    </div>

                                </div>
								<div class="form-actions">
                                   
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
<div id="applyplan" class="modal fade" tabindex="-1" data-width="760">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Payment PLan</h4>
    </div>
    <div class="modal-body">


        <div class="row">
            <div class="col-md-12">
                <form action="<?php echo site_url();?>/students/add_payment_plan/<?php echo $this->uri->segment(3)?>" method="post" enctype="multipart/form-data">



                    <div class="form-group">
                        <label class="col-md-6 control-label">Discount</label>
                        <div class="col-md-6">
                            <input class="form-control input-inline " type="text" pattern="\d*" maxlength="5" max="10000" name="discount" value="0" id="discount">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-6 control-label">Max Date for Installment</label>
                        <div class="col-md-6">
                            <input class="form-control input-inline " type="text" pattern="\d*" maxlength="2" max="30" value="10" name="instdate" id="instdate" <?php if(@$myAccess[0]['installment_date']!=1 && $this->session->userdata('role') != "Admin") echo "readonly" ?>>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-6 control-label">No of Installments</label>
                        <div class="col-md-6">
                            <input class="form-control input-inline " type="text" pattern="\d*"  maxlength="2" name="installments" id="installments">
                        </div>
                    </div>

                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-9">

                                <input type="hidden"  name="plan_id" id="plan_id"  class="form-control mobile" value="" />

                                <button type="submit" class="btn red create_plan hideAfterClick">Create Plan</button>

                            </div>
                        </div>
                    </div>


                </form>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default">Close</button>
    </div>
</div>


<script>

    $('.create_plan').click(function(){
        $(this).hide();
    });
    
    $('.radio-group .radio').click(function(){

        $('.radio-group').find('.radio').removeClass('selected');
        $(this).addClass('selected');
        var val = $(this).attr('data-value');
        let index = $(this).attr('data-index');
        // $('.form-actions').find('.plan_id').val(val);
        $("#applyplan").modal("show");

        $(".modal-body #plan_id").val(val);


        var plans = <?php echo json_encode($plans) ?>;
        var maxdicount=plans[index].max_discount;
        var maxinstallments=parseInt(plans[index].no_of_installments);
        var maxinstallmentsextend=parseInt(plans[index].max_install_extend);
        maxinstallments+=maxinstallmentsextend;

        $(".modal-body #instdate").val(plans[index].paid_date_each_installment);
        $(".modal-body #installments").val(plans[index].no_of_installments);

        $(".modal-body #discount").keydown(function () {
                // Save old value.
                if (!$(this).val() || (parseInt($(this).val()) <=maxdicount  && parseInt($(this).val()) >= 0))
                    $(this).data("old", $(this).val());
            });
        $(".modal-body #discount").keyup(function () {
                // Check correct, else revert back to old value.
                if (!$(this).val() || (parseInt($(this).val()) <= maxdicount && parseInt($(this).val()) >= 0))
                    ;
                else
                    $(this).val($(this).data("old"));
            });



        $(".modal-body #instdate").keydown(function () {
            // Save old value.
            if (!$(this).val() || (parseInt($(this).val()) <=30  && parseInt($(this).val()) > 0))
                $(this).data("old", $(this).val());
        });
        $(".modal-body #instdate").keyup(function () {
            // Check correct, else revert back to old value.
            if (!$(this).val() || (parseInt($(this).val()) <= 30 && parseInt($(this).val()) > 0))
                ;
            else
                $(this).val($(this).data("old"));
        });


        $(".modal-body #installments").keydown(function () {
            // Save old value.
            if (!$(this).val() || (parseInt($(this).val()) <=(maxinstallments)  && parseInt($(this).val()) >= 0))
                $(this).data("old", $(this).val());
        });
        $(".modal-body #installments").keyup(function () {
            // Check correct, else revert back to old value.
            if (!$(this).val() || (parseInt($(this).val()) <= (maxinstallments) && parseInt($(this).val()) >= 0))
                ;
            else
                $(this).val($(this).data("old"));
        });


    });
</script>