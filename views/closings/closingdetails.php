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
        <?php if(@$this->session->userdata('error')):?>
            <div class="alert alert-danger">
                <button class="close" data-close="alert"></button>
                <span>
                    <?php echo $this->session->userdata('error');?> </span>
            </div>
        <?php endif;?>

        <?php if($closing_status != '0'):  ?>
            <button class="btn green" id="print" onclick="printContent('printtable');" >Print</button>
        <?php endif;?>
        <div id="printtable">
            <div class="row">
                <?php $sq = 'select * from campuses where campus_id="'.$campus_id.'"';
                $campus = $this->db->query($sq)->result_array(); ?>
                <img class="col-md-2" src="<?php echo base_url();?>/uploads/<?php echo @$campus[0]['logo'] ?>" style="width: 90px; height: 60px">
                <h3 class="col-md-10" style="margin:5px 0px 20px 0px;text-align: center;font-weight: bold"><?php

                    echo @$campus[0]['campus_name'] ?></h3></div>
            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/closing/closenow">
                <div class="form-body">
                    <div class="row">
                        <div class="col-md-12">
                            <!-- BEGIN EXAMPLE TABLE PORTLET-->
                            <div class="portlet box grey-cascade">
                                <div class="portlet-title">
                                    <div class="caption">
                                        <i class="fa fa-list"></i>Fee Details&nbsp;<?php $sqlclosingid="SELECT concat( LEFT(`campus_closing_id`,3),MAX(CAST(SUBSTRING(`campus_closing_id`, 4, length(`campus_closing_id`)-2) AS UNSIGNED))+1) as closing_id FROM `closing_perday` WHERE `campus_id` = '".$campus_id."'";
                                        $closingid=$this->db->query($sqlclosingid)->row();
                                        ?>
                                        <label style="margin-left: 120px; font-size: large; font-weight: bold"><?php
                                            if(count($closed) > 0 )
                                            {
                                                echo 'Closing ID : '.$closed[0]['campus_closing_id'];
                                                $stclosing = $closed[0]['campus_closing_id'];
                                            }
                                            else {
                                                echo 'Closing ID : ' . $closingid->closing_id;
                                                $stclosing = $closingid->closing_id;
                                            }
                                            ?>
                                        </label>
                                        <label style="margin-left: 120px; font-size: large; font-weight: bold"><?php echo 'Closing Date : '.$day.'-'.$month.'-'.$year ?></label>
                                        <label style="margin-left: 120px; font-size: large; font-weight: bold">
                                        <?php
                                            $am = $total_amount;
                                            echo 'Closing Amount  :  '.$total_amount; ?></label>
                                    </div>
                                </div>

                                <div class="portlet-body">
                                    <div class="alert alert-success">

                                    </div>
                                    <table class="table table-striped table-bordered table-hover">
                                        <thead>
                                        <tr>
                                            <th class="hidden">
                                                hidden
                                            </th>
                                            <th>
                                                Student Details
                                            </th>
                                            <th>
                                                Student Course
                                            </th>
                                            <th>
                                                Challan No
                                            </th>
                                            <th>
                                                Payment type
                                            </th>
                                            <th>
                                                Amount
                                            </th>
                                            <th>
                                                Paid Date
                                            </th>
                                            <th>
                                                Upload Date
                                            </th>
                                            <th>
                                                Paid By
                                            </th>

                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $i = 0;
                                        $feeids='';
                                        foreach($payments as $payment):
                                            ?>
                                            <tr class="odd gradeX">
                                                <td class="hidden">
                                                    <?php echo $i;
                                                    $feeids.=($payment['id'].',');
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php if($payment['contract_id'] == 0){ ?>
                                                        Name : <?php echo $payment['first_name'].' '.$payment['last_name']?> <br />
                                                        Rollno : <?php echo $payment['roll_no']?> <br />
                                                        CNIC : <?php echo $payment['cnic']?> <br />
                                                    <?php }else {
                                                        $contractor = $this->db->join('contractors','contractors.contractor_id = contracts.contractor_id')->join('courses','courses.course_id = contracts.course_id')->get_where('contracts','contract_id = '.$payment['contract_id'])->row_array();
                                                    ?>
                                                        Name : <?php echo $contractor['name'];?> <br />
                                                        Contract ID : <?php echo $contractor['contract_name']?> <br />
                                                        CNIC : <?php echo $contractor['cnic']?> <br />
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                    <?php if($payment['contract_id'] == 0) {  echo $payment['course_name'];} else{ echo $contractor['course_name']; }?> <br />
                                                </td><td>
                                                    <?php echo $payment['challan_no']?>
                                                </td>
                                                <td>
                                                    <?php

                                                    if ($payment['fee_submit_type'] == 'receipt_book'){
                                                        echo '<a href="'.base_url().'uploads/'.$payment['scan_challan'].'" target="_blank" class="pull-left">Receipt Book</a> <br />';
                                                    }else
                                                        echo '<a href="'.site_url().'/students/print_college_challan/'.$payment['id'].'" target="_blank" class="college_fee_'.$i.'"><i class=""></i> Computer Challan</a> <br />';?><br />

                                                </td>
                                                <td>
                                                    <?php echo ($payment['actual_amount'])?>
                                                </td>
                                                <td>
                                                    <?php echo $payment['paid_date']?>
                                                </td>
                                                <td>
                                                    <?php echo $payment['updated_at']?>
                                                </td>
                                                <td>
                                                    <?php echo $payment['paid_by']?>

                                                    <?php if($myAccess[0]['dailyclosing'] == '1' && $closing_status == '0'){  ?>
                                                        <?php if($i == 0): ?>
                                                            <form></form>
                                                        <?php endif; ?>
                                                        <form role="form" method="post" action="<?php echo site_url();?>/closing/transfer_fee/<?php echo $payment['id'] ?>">
                                                            <div class="form-body">
                                                                <label class="col-md-3 control-label">Transfer to Campus <span class="required">*</span></label>
                                                                <select name="course_id" id="change_campus_<?php echo $payment['id']; ?>" class="form-control input-inline input-large">
                                                                    <option value="">Select Campus</option>
                                                                    <?php
                                                                    foreach ($campuses as $course):
                                                                        ?>
                                                                        <option value="<?php echo $course['campus_id']?>"><?php echo $course['campus_name']?></option>
                                                                    <?php
                                                                    endforeach;
                                                                    ?>
                                                                </select>
                                                            </div>
                                                            <div class="form-actions">
                                                                <div class="row">
                                                                    <div class="col-md-offset-3 col-md-9">
                                                                        <button type="submit" id="change_button_<?php echo $payment['id']; ?>" class="btn green">Transfer</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <script>
                                                                document.addEventListener( "DOMContentLoaded", function(){
                                                                    $("#change_button_<?php echo $payment['id']; ?>").hide();
                                                                    $("#change_campus_<?php echo $payment['id']; ?>").on('change', function(){    // 2nd way
                                                                        if ($(this).val() == "")
                                                                        {
                                                                            $("#change_button_<?php echo $payment['id']; ?>").hide();
                                                                        }else {
                                                                            $("#change_button_<?php echo $payment['id']; ?>").show();
                                                                        }
                                                                    });
                                                                }, false );
                                                            </script>
                                                        </form>
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
                        <div class="col-md-12">
                            <!-- BEGIN EXAMPLE TABLE PORTLET-->
                            <div class="portlet box grey-cascade">
                                <div class="portlet-title">
                                    <div class="caption">
                                        <i class="fa fa-list"></i>Asset Sale Details <label style="margin-left: 120px; font-size: large; font-weight: bold"><?php
                                            echo 'Sale Amount  :  '.$asset_sales_sum[0]['total'] ?></label>
                                    </div>
                                </div>
                                <div class="portlet-body">
                                    <div class="alert alert-success"></div>
                                    <table class="table table-striped table-bordered table-hover">
                                        <thead>
                                        <tr>
                                            <th class="hidden">
                                                hidden
                                            </th>
                                            <th>
                                                Sale Product
                                            </th>
                                            <th>
                                                Sale Quantity
                                            </th>
                                            <th>
                                                Purchaser Name
                                            </th>
                                            <th>
                                                Purchaser Phone
                                            </th>
                                            <th>
                                                Sale Amount
                                            </th>
                                            <th>
                                                Sold By
                                            </th>
                                            <th>
                                                Sale Date
                                            </th>

                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $i = 0;
                                        $saleids = '';
                                        foreach(@$asset_sales as $sale):
                                            ?>
                                            <tr class="odd gradeX">
                                                <td class="hidden">
                                                    <?php echo $i;
                                                    $saleids.=($sale['id'].',');?>
                                                </td>
                                                <td>
                                                    <?php echo $sale['product_name']?>
                                                </td>
                                                <td>
                                                    <?php echo $sale['quantity']?>
                                                </td>
                                                <td>
                                                    <?php echo $sale['purchaser_name']?> <br />
                                                </td>
                                                <td>
                                                    <?php echo $sale['purchaser_contact']?>
                                                </td>
                                                <td>
                                                    <?php echo ($sale['sale_amount'])?>
                                                </td>
                                                <td>
                                                    <?php echo $sale['sold_by']?>
                                                </td>
                                                <td>
                                                    <?php echo $sale['sale_date']?>
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
                        <div class="col-md-12">
                            <!-- BEGIN EXAMPLE TABLE PORTLET-->
                            <div class="portlet box grey-cascade">
                                <div class="portlet-title">
                                    <div class="caption">
                                        <i class="fa fa-list"></i>Bookstore POS Sales <label style="margin-left: 120px; font-size: large; font-weight: bold"><?php
                                            echo 'Sale Amount  :  '.@$pos_sales_sum[0]['total'] ?></label>
                                    </div>
                                </div>
                                <div class="portlet-body">
                                    <div class="alert alert-success"></div>
                                    <table class="table table-striped table-bordered table-hover">
                                        <thead>
                                        <tr>
                                            <th class="hidden">
                                                hidden
                                            </th>
                                            <th>
                                                Invoice No
                                            </th>
                                            <th>
                                                Product
                                            </th>
                                            <th>
                                                Purchaser Name
                                            </th>
                                            <th>
                                                Purchaser Phone
                                            </th>
                                            <th>
                                                Sale Amount
                                            </th>
                                            <th>
                                                Sold By
                                            </th>
                                            <th>
                                                Sale Date
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $i = 0;
                                        foreach(@$pos_sales as $sale):
                                            ?>
                                            <tr class="odd gradeX">
                                                <td class="hidden">
                                                    <?php echo $i; ?>
                                                </td>
                                                <td>
                                                    <?php echo $sale['invoice_no']?>
                                                </td>
                                                <td>
                                                    <?php echo $sale['product_name']?>
                                                </td>
                                                <td>
                                                    <?php echo $sale['purchaser_name']?>
                                                </td>
                                                <td>
                                                    <?php echo $sale['purchaser_phone']?>
                                                </td>
                                                <td>
                                                    <?php echo $sale['sold_amount']?>
                                                </td>
                                                <td>
                                                    <?php echo $sale['sold_by_name']?>
                                                </td>
                                                <td>
                                                    <?php echo $sale['sold_date']?>
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
                        <div class="col-md-12">
                            <!-- BEGIN EXAMPLE TABLE PORTLET-->
                            <div class="portlet box grey-cascade">
                                <div class="portlet-title">
                                    <div class="caption">
                                        <i class="fa fa-list"></i>Sale Details <label style="margin-left: 120px; font-size: large; font-weight: bold"><?php
                                            echo 'Sale Amount  :  '.$sales_sum[0]['total'] ?></label>
                                    </div>
                                </div>
                                <div class="portlet-body">
                                    <div class="alert alert-success"></div>
                                    <table class="table table-striped table-bordered table-hover">
                                        <thead>
                                        <tr>
                                            <th class="hidden">
                                                hidden
                                            </th>
                                            <th>
                                                Customer Name
                                            </th>
                                            <th>
                                                Customer Phone
                                            </th>
                                            <th>
                                                Invoice No
                                            </th>
                                            <th>
                                                Sale Amount
                                            </th>
                                            <th>
                                                Sale Date
                                            </th>

                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $i = 0;
                                        $saleids = '';
                                        foreach(@$sales as $sale):
                                            ?>
                                            <tr class="odd gradeX">
                                                <td class="hidden">
                                                    <?php echo $i;
                                                    $saleids.=($sale['id'].',');?>
                                                </td>
                                                <td>
                                                    <?php echo $sale['first_name']." ".$sale['last_name']?>
                                                </td>
                                                <td>
                                                    <?php echo $sale['phone_number']?>
                                                </td>
                                                <td>
                                                    <?php echo $sale['invoice_number']?> <br />
                                                </td>
                                                <td>
                                                    <?php echo $sale['payment_amount']?>
                                                </td>
                                                <td>
                                                    <?php echo $sale['sale_time']?>
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
                        <div class="col-md-12">
                            <!-- BEGIN EXAMPLE TABLE PORTLET-->
                            <div class="portlet box grey-cascade">
                                <div class="portlet-title">
                                    <div class="caption">
                                        <i class="fa fa-list"></i>Loan Details <label style="margin-left: 120px; font-size: large; font-weight: bold"><?php
                                            echo 'Amount  :  '.array_sum(array_column($loans,'amount_paid')) ?></label>
                                    </div>
                                </div>
                                <div class="portlet-body">
                                    <div class="alert alert-success"></div>
                                    <table class="table table-striped table-bordered table-hover">
                                        <thead>
                                        <tr>
                                            <th>
                                                Sr #
                                            </th>
                                            <th>
                                                Loan Details
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
                                                Paid on
                                            </th>

                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $i = 0;
                                        $loanids = '';
                                        foreach(@$loans as $payment):
                                            ?>
                                            <tr class="odd gradeX">
                                                <td>
                                                    <?php echo $i+1;
                                                    $loanids.=($payment['id'].',');?>
                                                </td>
                                                <td>
                                                    <?php echo 'loan-'.$payment['loan_id'].'<br />'.$payment['cash_given'];?>
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
                                                    <?php echo $payment['paid_date'];?>
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
                </div>
                <div class="form-actions" style="margin-left: 20px;">
                    <div class="row">
                        <input type="hidden"  value="<?php echo $month ?>" id="month" name="month" readonly>
                        <input type="hidden"  value="<?php echo $day ?>" id="day" name="day" readonly>
                        <input type="hidden"  value="<?php echo $year ?>" id="year" name="year" readonly>
                        <input type="hidden"  value="<?php echo $feeids ?>" id="feeids" name="feeids" readonly>
                        <input type="hidden"  value="<?php echo $campus_id ?>" id="campus_id" name="campus_id" readonly>
                        <input type="hidden"  value="<?php echo $saleids ?>" id="sale_ids" name="sale_ids" readonly>
                        <input type="hidden"  value="<?php echo $loanids ?>" id="loan_ids" name="loan_ids" readonly>

                        Amount Receivable for closing :  <input type="text"  STYLE="text-align: center; margin-left: 20px; font-weight: bolder; font-size: large" value="<?php echo $am+$asset_sales_sum[0]['total']+@$pos_sales_sum[0]['total'] ?>" id="receivable_amount" name="receivable_amount" readonly>
                        <br />
                        <br />
                        <br />
                        <input type="hidden" id="close_type" name="close_type"  >
                        <?php if($myAccess[0]['dailyclosing'] == '1' && $closing_status == '0' && count($closed) == 0):  ?>
                            <button type="submit" id="cash" class="btn green">Close by Cash</button>
                        <?php endif; ?>
                        <?php if($myAccess[0]['dailybankclosing'] == '1' && $closing_status == '0' && count($closed) == 0):  ?>
                            <button type="submit" id="bank" class="btn green">Close by Bank</button>
                        <?php endif; ?>
<!--                        --><?php //if($myAccess[0]['dailybankclosing'] == '1' && $closing_status == '0' && count($closed) == 0):  ?>
<!--                            <button type="submit" id="paypro" class="btn green">Close by PayPro</button>-->
<!--                        --><?php //endif; ?>
                        <?php if(count($closed) > 0): ?>
                            <div class="alert alert-info" style="display:inline-block; margin:0;">
                                Closing already completed for this campus on this date.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
            <?php if(@$myAccess[0]['dailyclosing'] == '1'  && @$closed[0]['close_type'] == '1'):  ?>
                <a href="<?php echo site_url().'/closing/print_bank_challan/'.$stclosing.'/'.$campus_id.'/'.($am+$asset_sales_sum[0]['total']+@$pos_sales_sum[0]['total']);?>" target="_blank" ><i class="btn btn-primary"></i> Bank Challan</a>
            <?php endif; ?>
            <?php if (count($closed) > 0):
                if ($closed[0]['checked_by'] != "1"):?>
                    <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/closing/updatenow">
                        <div class="form-body">

                        </div>
                        <div class="form-actions" style="margin-left: 20px;">
                            <div class="row">
                                <input type="hidden" id="upd_close_type" name="close_type" value="<?php if ($closed[0]['close_type'] == '1') echo '2'; else echo '1' ?>"  >
                                <input type="hidden" name="closing_id" value="<?php echo $closed[0]['id']; ?>"  >
                                <?php if($myAccess[0]['dailyclosing'] == '1' && $closed[0]['close_type'] == '1'):  ?>
                                    <button type="submit" onclick="return confirm('Are you sure?')" id="update_cash" class="btn green autohide">Close by Cash</button>
                                    <button type="submit" onclick="return confirm('Are you sure ?')" id="update_paypro" class="btn green autohide">Close by PayPro</button>
                                <?php endif; ?>
                                <?php if($myAccess[0]['dailybankclosing'] == '1' && $closed[0]['close_type'] == '2'):  ?>
                                    <button type="submit" onclick="return confirm('Are you sure ?')" id="update_bank" class="btn green autohide">Close by Bank</button>
                                    <button type="submit" onclick="return confirm('Are you sure ?')" id="update_paypro" class="btn green autohide">Close by PayPro</button>
                                <?php endif; ?>
                                <?php if($myAccess[0]['dailybankclosing'] == '1' && $closed[0]['close_type'] == '3'):  ?>
                                    <button type="submit" onclick="return confirm('Are you sure ?')" id="update_bank" class="btn green autohide">Close by Bank</button>
                                    <button type="submit" onclick="return confirm('Are you sure ?')" id="update_cash" class="btn green autohide">Close by Cash</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>

                <?php
                endif;
            endif;?>
        </div>
        <!-- END PAGE CONTENT-->
    </div>
</div>
<!-- END CONTENT -->
<script>
    function printContent(el){
        var restorepage = $('body').html();
        var printcontent = $('#' + el).clone();
        $('body').empty().html(printcontent);
        window.print();
        $('body').html(restorepage);
    };

    document.addEventListener( "DOMContentLoaded", function(){

        var closingFormSubmitted = false;

        function disableClosingButtons() {
            $('#cash, #bank, #paypro').prop('disabled', true).addClass('disabled');
        }

        $('#cash').on('click', function(){
            $('#close_type').val('2');
        });

        $('#bank').on('click', function(){
            $('#close_type').val('1');
        });

        $('#paypro').on('click', function(){
            $('#close_type').val('3');
        });

        $('form[action*="/closing/closenow"]').on('submit', function(e){
            if (closingFormSubmitted) {
                e.preventDefault();
                return false;
            }
            if (!$('#close_type').val()) {
                e.preventDefault();
                alert('Please select Close by Cash, Bank or PayPro.');
                return false;
            }
            closingFormSubmitted = true;
            disableClosingButtons();
        });

        $('#update_paypro').click(function(){
            $('#upd_close_type').val('3');
        });

        $('#update_bank').click(function(){
            $('#upd_close_type').val('1');
        });

        $('#update_cash').click(function(){
            $('#upd_close_type').val('2');
        });

        $(document).ready(function(){
            $('.autohide').click(function(){
                $(this).hide();
            });
        });

    }, false );
    
</script>