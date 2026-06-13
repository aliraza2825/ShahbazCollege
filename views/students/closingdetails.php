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

        <button class="btn green" id="print" onclick="printContent('printtable');" >Print</button>
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
                                                echo 'Closing ID : '.$closed[0]['campus_closing_id'];}
                                            else
                                                echo 'Closing ID : '.$closingid->closing_id;
                                            ?>
                                        </label>
                                        <label style="margin-left: 120px; font-size: large; font-weight: bold"><?php echo 'Closing Date : '.$day.'-'.$month.'-'.$year ?></label>
                                        <label style="margin-left: 120px; font-size: large; font-weight: bold"><?php echo 'Closing Amount  :  '.$total_amount ?></label>
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

                                                    Name : <?php echo $payment['first_name'].' '.$payment['last_name']?> <br />
                                                    Rollno : <?php echo $payment['roll_no']?> <br />
                                                    CNIC : <?php echo $payment['cnic']?> <br />


                                                </td>

                                                <td>
                                                    <?php echo $payment['course_name']?> <br />
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

                                                        <?php if($myAccess[0]['dailyclosing'] == '1' && $closing_status == '0'):  ?>
                                                    <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/closing/transfer_fee/<?php echo $payment['id'] ?>">
                                                        <div class="form-body">
                                                            <label class="col-md-3 control-label">Transfer to Campus <span class="required">*</span></label>
                                                            <select name="course_id" class="form-control input-inline input-large course_id" required>
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
                                                                    <button type="submit" class="btn green">Transfer</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>

                                                        <?php endif; ?>

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

                        Amount Receivable for closing :  <input type="text"  STYLE="text-align: center; margin-left: 20px; font-weight: bolder; font-size: large" value="<?php echo $total_amount ?>" id="receivable_amount" name="receivable_amount" readonly>
                        <br />
                        <br />
                        <br />
                        <input type="hidden" id="close_type" name="close_type"  >
                        <?php if($myAccess[0]['dailyclosing'] == '1' && $closing_status == '0'):  ?>
                            <button type="submit" id="cash" class="btn green">Close by Cash</button>
                        <?php endif; ?>

                        <?php if($myAccess[0]['dailybankclosing'] == '1' && $closing_status == '0'):  ?>
                            <button type="submit" id="bank" class="btn green">Close by Bank</button>
                        <?php endif; ?>

                    </div>
                </div>
            </form>

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


    $('#bank').click(function(){


        $('#close_type').val('1');

        return false;
    });

    $('#cash').click(function(){


        $('#close_type').val('2');

        return false;
    });


</script>