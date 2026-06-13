
<div class="page-content-wrapper">
    <div class="page-content">


        <!-- BEGIN PAGE HEADER-->
        <!--<h3 class="page-title">
        Add Teacher <small>You can add teacher here</small>
        </h3>-->
        <!-- END PAGE HEADER-->
        <!-- BEGIN DASHBOARD STATS -->
        <div class="row">

            <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
                <div class="dashboard-stat blue-madison">
                    <div class="visual">
                        <i class="fa fa-money"></i>
                    </div>
                    <div class="details">
                        <div class="number">
                            <?php echo $total_loan;?>
                        </div>
                        <div class="desc">
                            Total Loan
                        </div>
                    </div>
                    <!--<a class="more" href="javascript:;">
                    View more <i class="m-icon-swapright m-icon-white"></i>
                    </a>-->
                </div>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
                <div class="dashboard-stat purple">
                    <div class="visual">
                        <i class="fa fa-money"></i>
                    </div>
                    <div class="details">
                        <div class="number">
                            <?php echo $remaining_loan?>
                        </div>
                        <div class="desc">
                            Remaining Loan
                        </div>
                    </div>
                    <!--<a class="more" href="javascript:;">
                    View more <i class="m-icon-swapright m-icon-white"></i>
                    </a>-->
                </div>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
                <div class="dashboard-stat purple">
                    <div class="visual">
                        <i class="fa fa-money"></i>
                    </div>
                    <div class="details">
                        <div class="number">
                            <?php echo $paid_loan;?>
                        </div>
                        <div class="desc">
                            Total Paid Loan
                        </div>
                    </div>
                    <!--<a class="more" href="javascript:;">
                    View more <i class="m-icon-swapright m-icon-white"></i>
                    </a>-->
                </div>
            </div>

        </div>
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
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet box grey-cascade">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-money"> Loan Details</i>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table  class="table table-striped table-bordered table-hover" >
                            <thead>
                            <tr>
                                <th>
                                    Sr #
                                </th>
                                <th>
                                    User Details &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </th>
                                <th>
                                    Amount
                                </th>
                                <th>
                                    Paid Amount
                                </th>
                                <th>
                                    Dead Line
                                </th>
                                <th>
                                    Paid Details
                                </th>
                                <th>
                                    Paid Status
                                </th>
                                <th>
                                    Paid on
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $i=1;
                            foreach($loans as $payment):
                                $clas = '';
                                if ($payment['paid_at'] != null && $payment['paid_at'] != '' ){
                                    $clas = 'success';
                                }
                                ?>
                                <tr class="odd gradeX <?php echo $clas ?>">

                                    <td>
                                        <?php echo $i;?>
                                    </td>
                                    <td>
                                        <?php echo $payment['first_name'].' '.$payment['last_name'].' S/O '.$payment['father_name'];?>
                                        <br />
                                        <?php echo $payment['cnic']?>
                                        <br />
                                        <?php echo $payment['mobile'];?>
                                        <br />
                                        <?php echo $payment['emergency_no'];?>
                                    </td>
                                    <td>
                                        <?php echo $payment['amount'];?>
                                    </td>
                                    <td>
                                        <?php echo $payment['amount_paid'];?>
                                    </td>
                                    <td>
                                        <?php echo $payment['due_date'];?>
                                    </td>
                                    <td>
                                        <?php
                                        if($payment['amount_paid'] >0 && $payment['paid_at'] == 'salary') {
                                            $ddata = $this->db->get_where("payroll", "id = '" . $payment['payroll_id'] . "'")->row();
                                            if ($ddata)
                                                echo $ddata->payroll_year . ' ' . $ddata->payroll_month;
                                        }elseif($payment['amount_paid'] >0 && $payment['paid_at'] == 'cash') {
                                            $closing_ata = $this->db->get_where("closing_perday", "campus_closing_id = '" . $payment['closing_id'] . "'")->row();
                                            if ($closing_ata)
                                                echo 'Closing : '.$closing_ata->campus_closing_id;
                                            else
                                                echo "<p class='_blink' style='color: red'>Daily Cash Closing Not Closed</p>";
                                        }
                                        ?>

                                    </td>
                                    <td>
                                        <?php
                                        if($payment['amount_paid'] >0){
                                            echo "PAID";
                                        }else{
                                            echo "NOT PAID";
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                            if ($payment['paid_at'] != "")
                                                echo $payment['paid_at'];
                                            else {
                                            ?>
                                            <input type="checkbox" class="selection" name="selection" data-amount="<?php echo $payment['amount'];?>" value="<?php echo $payment['loan_plan_id'];?>" />
                                        <?php } ?>
                                    </td>
                                </tr>
                                <?php
                                $i++;
                            endforeach;
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- END EXAMPLE TABLE PORTLET-->
            </div>
        </div>
        <!-- END PAGE CONTENT-->

        <form class="form-horizontal" id="new-payroll-form" role="form" method="post" action="<?php echo site_url();?>/loans/pay_now/<?php echo $this->uri->segment(3);?>">
            <div class="row">
                <input type="hidden"  value="" id="installment_ids" name="installment_ids">
                <div class="form-group">
                    <label class="col-md-2 control-label">Select Closing Campus <span class="required">*</span></label>
                    <div class="col-md-4"><input type="text"  STYLE="text-align: center; margin-left: 20px; font-weight: bolder; font-size: large" value="<?php echo 0 ?>" id="receivable_amount" name="receivable_amount" readonly></div>
                </div>
                <?php $closing_campuses = $this->db->join('campuses','campuses.campus_id = closing_persons.campus_id')->group_by('closing_persons.campus_id')->get_where("closing_persons","active_status = 1")->result_array(); ?>
                <br />
                <div class="form-group">
                    <label class="col-md-2 control-label">Select Closing Campus <span class="required">*</span></label>
                    <div class="col-md-4">
                        <select class="form-control bank_details" id="select_bank" name="campus_id" required>
                            <option value="">SELECT Campus</option>
                            <?php
                            $count = count($closing_campuses);
                            for($a=0;$a<$count;$a++):
                                ?>
                                <option value="<?php echo $closing_campuses[$a]['campus_id'].'';?>"><?php echo $closing_campuses[$a]['campus_name'].'';?></option>
                            <?php
                            endfor;
                            ?>
                        </select>
                    </div>
                </div>
                <br />
                <br />
                <button type="submit" id="bank" class="btn green">Pay Now</button>
            </div>
        </form>

    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        jQuery('.selection').change(function(){
            var ids = [];
            var amount = 0;
            jQuery.each(jQuery("input[name='selection']:checked"), function(){
                ids.push(jQuery(this).val());
                amount+=$(this).data('amount')
            });
            jQuery('#installment_ids').val(ids.join(","));
            jQuery('#receivable_amount').val(amount);
        });
    });
</script>