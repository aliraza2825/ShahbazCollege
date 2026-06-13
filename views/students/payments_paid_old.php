<?php
    $myAccess = checkUserAccess();
?>
<style>
    .btn{
        margin-bottom:10px;
    }
</style>
<?php
    $student_fees = $this->db->get_where('students', array('student_id'=>$this->uri->segment(3)))->result_array();
    $student_fee = $student_fees[0]['total_fee'];

    $total_fee = $this->db->get_where('fee_rules',array('course_id'=>$student_fees[0]['course_id']))->row()->total_fee;
    $this->db->select('sum(discount) as special_disc');
    $this->db->where('status = "1" and student_id = "'.$this->uri->segment(3).'"');
    $specialdisc=$this->db->get('discounts_approval')->result_array();
    $specialdisc=$specialdisc[0]['special_disc'];
?>
<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row">
            <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
                <div class="dashboard-stat blue-madison">
                    <div class="visual">
                        <i class="fa fa-money"></i>
                    </div>
                    <div class="details">
                        <div class="number">
                            <?php echo $total_fee;?>
                        </div>
                        <div class="desc">
                            Total Fees
                        </div>
                    </div>
                    <!--<a class="more" href="javascript:;">
                    View more <i class="m-icon-swapright m-icon-white"></i>
                    </a>-->
                </div>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
                <div class="dashboard-stat blue-madison">
                    <div class="visual">
                        <i class="fa fa-money"></i>
                    </div>
                    <div class="details">
                        <div class="number">
                            <?php echo $student_fee;?>
                        </div>
                        <div class="desc">
                            Student Fees
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
                <div class="dashboard-stat red">
                    <div class="visual">
                        <i class="fa fa-money"></i>
                    </div>
                    <div class="details">
					
						<div class="details-text" style="color:#fff; font-size:12px; text-align:center">
                            <br />
                            Disc on Admission : <?php echo $total_fee-$student_fee-$specialdisc;?>
                            <br />
                            Special Disc : <?php echo $specialdisc;?>
							<br />
                            Dis on Fee Merged : <?php echo $discountfee;?>
							<br />
                            Total Disc : <?php echo $total_fee-$student_fee+$discountfee;?>
                        </div>
					
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
                <div class="dashboard-stat green">
                    <div class="visual">
                        <i class="fa fa-money"></i>
                    </div>
                    <div class="details">
                        <div class="number">
                            <?php echo $discount[0]['total_paid'];?>
                        </div>
                        <div class="desc">
                            Fee Created
                        </div>
                    </div>
                    <!--<a class="more" href="javascript:;">
                    View more <i class="m-icon-swapright m-icon-white"></i>
                    </a>-->
                </div>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
                <div class="dashboard-stat red">
                    <div class="visual">
                        <i class="fa fa-money"></i>
                    </div>
                    <div class="details">
                        <div class="number">
                            <?php echo ($student_fee-$discount[0]['total_paid']);?>
                        </div>
                        <div class="desc">
                            Fee Not Created
                        </div>
                    </div>
                    <!--<a class="more" href="javascript:;">
                    View more <i class="m-icon-swapright m-icon-white"></i>
                    </a>-->
                </div>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
                <div class="dashboard-stat yellow">
                    <div class="visual">
                        <i class="fa fa-money"></i>
                    </div>
                    <div class="details">
                        <div class="number">
                            <?php echo $fee_should_pay[0]['fee_should_pay'];?>
                        </div>
                        <div class="desc">
                            Fee Should Pay
                        </div>
                    </div>
                    <!--<a class="more" href="javascript:;">
                    View more <i class="m-icon-swapright m-icon-white"></i>
                    </a>-->
                </div>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
                <div class="dashboard-stat green">
                    <div class="visual">
                        <i class="fa fa-money"></i>
                    </div>
                    <div class="details">
                        <div class="number">
                            <?php echo ($paid_fee[0]['paid_fee']-$discountfee);?>
                        </div>
                        <div class="desc">
                            Fee Paid
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
                            <?php if(($fee_should_pay[0]['fee_should_pay']-$paid_fee[0]['paid_fee'])>0)
							{ 
								echo ($fee_should_pay[0]['fee_should_pay']-$paid_fee[0]['paid_fee']);
							}else{
								echo "0";
							}?>
                        </div>
                        <div class="desc">
                            Remaining fee till date
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
                            <?php echo $remaining_fee;?>
                        </div>
                        <div class="desc">
                            Total Remaining Fee
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
                            <?php
                            $percent = (($paid_fee[0]['paid_fee']/$student_fee)*100);
                            echo round($percent);
                            ?>%
                        </div>
                        <div class="desc">
                            Fee Paid
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
                            <?php echo $total_fine+$removed_fine;?>
                        </div>
                        <div class="desc">
                            Total Fine
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
                <div class="dashboard-stat purple">
                    <div class="visual">
                        <i class="fa fa-money"></i>
                    </div>
                    <div class="details">
                        <div class="number">
                            <?php echo $removed_fine;?>
                        </div>
                        <div class="desc">
                            Removed Fine
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
                            <?php echo $fine_paid;?>
                        </div>
                        <div class="desc">
                            Fine Paid
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
                            <?php echo $fine_should_pay-$fine_paid;?>
                        </div>
                        <div class="desc">
                            remaining fine till date
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
                            <?php echo $total_fine-$fine_paid;?>
                        </div>
                        <div class="desc">
                            Total Remaining Fine
                        </div>
                    </div>
                    <!--<a class="more" href="javascript:;">
                    View more <i class="m-icon-swapright m-icon-white"></i>
                    </a>-->
                </div>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
                <div class="dashboard-stat red">
                    <div class="visual">
                        <i class="fa fa-money"></i>
                    </div>
                    <div class="details">
                        <div class="details-text" style="color:#fff; font-size:16px; text-align:center">
                            <br />
                            No. of CF : <?php echo count($this->db->get_where('payments',array('student_id'=>$this->uri->segment(3),'payment_plan'=>'consulation fee'))->result_array());?>
                            <br />
                            Total CF : <?php echo $consulation_fee[0]['consulation_fee'];?>
                        </div>
                    </div>
                    <!--<a class="more" href="javascript:;">
                    View more <i class="m-icon-swapright m-icon-white"></i>
                    </a>-->
                </div>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
                <div class="dashboard-stat blue">
                    <div class="visual">
                        <i class="fa fa-money"></i>
                    </div>
                    <div class="details">
                        <div class="details-text" style="color:#fff; font-size:16px; text-align:center">
                            <br />
                            CF should pay : <?php echo $consulation_fee_should_pay[0]['consulation_fee_should_pay'];?>
                            <br />
                            CF Paid : <?php if($consulation_fee_paid[0]['consulation_fee_paid']==''){echo '0';}else{echo $consulation_fee_paid[0]['consulation_fee_paid'];}?>
                        </div>
                    </div>
                    <!--<a class="more" href="javascript:;">
                    View more <i class="m-icon-swapright m-icon-white"></i>
                    </a>-->
                </div>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
                <div class="dashboard-stat blue">
                    <div class="visual">
                        <i class="fa fa-money"></i>
                    </div>
                    <div class="details">
                        <div class="details-text" style="color:#fff; font-size:16px; text-align:center">
                            <br />
                            CF rem.. till date : <?php echo $consulation_fee_should_pay[0]['consulation_fee_should_pay']-$consulation_fee_paid[0]['consulation_fee_paid'];?>
                            <br />
                            total CF rem.. : <?php echo $consulation_fee[0]['consulation_fee']-$consulation_fee_paid[0]['consulation_fee_paid'];?>
                        </div>
                        <!--<div class="number">
								 <?php echo $consulation_fee_paid[0]['consulation_fee_paid'];?>
							</div>
							<div class="desc">
								 Consulation Fee Paid
							</div>-->
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
                            <?php echo $total_extra_fee;?>
                        </div>
                        <div class="desc">
                            Total Extra Fee
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
                            <?php echo $extra_fee_paid_till_date;?>
                        </div>
                        <div class="desc">
                            Extra Fee Paid Till Date
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
                            <?php echo $extra_fee_remaining_till_date;?>
                        </div>
                        <div class="desc">
                            Remaining Extra Fee Till Date
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
                            <?php echo $total_extra_fee-$total_extra_paid_fee;?>
                        </div>
                        <div class="desc">
                            Total Remaining Extra Fee
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
                        <i class="fa fa-phone"></i>
                    </div>
                    <div class="details">
                        <div class="number">
                            <?php echo $total_calls;?>
                        </div>
                        <div class="desc">
                            Total Calls
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
                            <?php echo $shift_delete_fee;?>
                        </div>
                        <div class="desc">
                            Total Fee Delete / Shift
                        </div>
                    </div>
                    <!--<a class="more" href="javascript:;">
                    View more <i class="m-icon-swapright m-icon-white"></i>
                    </a>-->
                </div>
            </div>
        </div>

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
            <div class="header" id="myHeader" style="margin-bottom:10px; z-index: 100"></div>
            <div class="col-md-12 ">
                <br />
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet box grey-cascade">
                    <div class="portlet-title ">
                        <div class="caption">
                            <i class="fa fa-money"></i> Student's Info (<?php echo $student[0]['first_name'].' '.$student[0]['last_name'].' '.$student[0]['roll_no']; ?>) Payment. Cell # <?php echo $student[0]['mobile'];?> - <?php echo $student[0]['emergency_no'];?>
                        </div>
                    </div>
                    <div class="portlet-body" id="checkboxes">
                        <table  class="table table-striped table-bordered table-hover" id="sample_3">
                            <thead>
                            <tr>
                                <th>
                                    Sr #
                                </th>
                                <th>
                                    Payment Details &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </th>
                                <th>
                                    Dead Line
                                </th>
                                <th>
                                    Paid Status
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </th>
                                <th>
                                    Fee Due Comments
                                </th>
                                <th>
                                    System Comments
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $i=0;
							$showed=false;
                            foreach($payments as $payment):
                                ?>
                                <tr class="odd gradeX" >
                                    <td>
                                        <?php echo $i+1;  ?>
                                    </td>
                                    <td>
                                        <?php
                                        $totalpayable=0;
										$totalfine=0;
                                        if ($payment['merged_challan'] != null && $payment['actual_amount'] > 0){
                                            $payment_ids = rtrim($payment['paid_challans'], ", ");

                                            $this->db->select('payments.*, students.first_name as first_name, students.last_name as last_name, students.roll_no as roll_no, students.father_name as father_name, classes.name as class_name, campuses.bank_name, campuses.account_no, campuses.address, campuses.campus_name, campuses.note, campuses.campus_id');
                                            $this->db->from('payments');
                                            $this->db->join('students', 'payments.student_id=students.student_id', 'inner');
                                            $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
                                            $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
                                            $mergchalans=$this->db->where_in('payments.challan_no', explode(',', $payment_ids))->get()->result_array();


                                            foreach ($mergchalans as $merg){

                                                $totalpayable+=$merg['amount'];
												$challan_date = date_create($merg['dead_line']);
                                                $paid_date = date_create($merg['paid_date']);
                                                $diff=date_diff($challan_date,$paid_date);
                                                $difference = $diff->format("%R%a");

                                                if($difference>0)
                                                {
                                                    $totalfine += $difference*50;
                                                }

                                                ?>
                                                <br />
                                                Merged Challan # : <?php echo $merg['challan_no'];?>   <?php
											echo $merg['payment_comment'];
                                        ?> <br />
                                                <strong>Merged Amount : <?php echo $merg['amount'];?> </strong>

                                         <?php

                                            }
                                        }
                                        else {
                                                $totalpayable=$payment['amount'];
                                                ?>

                                                Challan # : <?php echo $payment['challan_no'];?>   <?php echo $payment['payment_comment']; ?>
                                                <br />
                                                <strong>Installment Amount : <?php echo $payment['amount'];?></strong>

                                        <?php } ?>

                                        <br />
                                        Discount : <?php echo $payment['discount'];?>
                                        <br />
                                        Previous Installment Amount : <?php echo $payment['remaining_installment_amount'];?>
                                        <br />
                                        Previous Fine Amount : <?php echo $payment['extra_amount'];?>
                                        <br />
                                        <hr />
                                        Installment Status : <?php if($payment['paid']==1){echo 'Paid';}else{echo 'Unpaid';}?>
                                        <br />
                                        <?php
                                        if($payment['paid']==1):
                                            $challan_date = date_create($payment['dead_line']);
                                            $paid_date = date_create($payment['paid_date']);
                                            $diff=date_diff($challan_date,$paid_date);
                                            $difference = $diff->format("%R%a");

                                            if($difference>0)
                                            {
                                                if($payment['payment_plan']=='24 Installments')
                                                {
                                                    $fee_fine = $difference*10;
                                                }
                                                else
                                                {
                                                    $fee_fine = $difference*50;
                                                }
                                            }
                                            else
                                            {
                                                $fee_fine = 0;
                                            }

                                            echo 'Late Fee Fine : '.$totalfine.'<br />';
                                            echo 'Removed Fine : '.$payment['removed_fine'].'<br />';

                                            echo '<strong>Payable Amount : '.($totalpayable+$payment['remaining_installment_amount']+$payment['extra_amount']+$totalfine).'</strong><br />';
                                            echo '<strong>Paid Amount : '.$payment['actual_amount'].'</strong><br />';
                                        endif;
                                        ?>
                                        <?php
                                        if($payment['paid']==0):
                                            $challan_date = date_create($payment['dead_line']);
                                            $today_date = date_create(date('Y-m-d'));
                                            $diff=date_diff($challan_date,$today_date);
                                            $difference = $diff->format("%R%a");

                                            if($difference>0)
                                            {
                                                if($payment['payment_plan']=='24 Installments')
                                                {
                                                    $fee_fine = $difference*10;
                                                }
                                                else
                                                {
                                                    $fee_fine = $difference*50;
                                                }
                                            }
                                            else
                                            {
                                                $fee_fine = 0;
                                            }
                                            if($difference>0)
                                            {
                                                echo 'Late Fee Days : '.str_replace('+','',$difference).'<br />';
                                            }
                                            echo 'Late Fee Amount : '.$fee_fine.'<br />';
                                            echo '<strong>Payable Amount : '.($payment['amount']+$payment['remaining_installment_amount']+$payment['extra_amount']+$fee_fine).'</strong>';
                                        endif;
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo $payment['dead_line'];?>
                                    </td>
                                    <td>
                                        <?php
                                        if($payment['paid']==1):
                                            ?>
											
											Paid Campus : <?php echo @$this->db->get_where('campuses',array('campus_id'=>$payment['submitted_fee_campus_id']))->row()->campus_name;?>
											<br />
                                            Paid Amount : <?php echo $payment['actual_amount'];?>
                                            <br />
                                            <?php
                                            if($payment['shifted_installment']>0):
                                                ?>
                                                Shifted Previous Installment Amount : <?php echo $payment['shifted_installment'];?>
                                                <br />
                                            <?php
                                            endif;
                                            ?>
                                            <?php
                                            if($payment['shifted_previous_fine']>0):
                                                ?>
                                                Shifted Previous Installment Fine : <?php echo $payment['shifted_previous_fine'];?>
                                                <br />
                                            <?php
                                            endif;
                                            ?>
                                            <?php
                                            if($payment['shifted_fine']>0):
                                                ?>
                                                Shifted Current Installment Fine : <?php echo $payment['shifted_fine'];?>
                                                <br />
                                            <?php
                                            endif;
                                            ?>
                                            <?php
                                            if($payment['removed_previous_fine']>0):
                                                ?>
                                                Removed Previous Installment Fine : <?php echo $payment['removed_previous_fine'];?>
                                                <br />
                                            <?php
                                            endif;
                                            ?>
                                            <?php
                                            if($payment['removed_fine']>0):
                                                ?>
                                                Removed Current Installment Fine : <?php echo $payment['removed_fine'];?>
                                                <br />
                                            <?php
                                            endif;
                                            ?>
                                            Paid Date : <?php echo $payment['paid_date'];?>
                                            <br />
                                            Paid Date System : <?php echo $payment['updated_at'];?>
                                            <br />
                                            Fee Pay Through : <?php echo $payment['fee_pay_through'];?>
                                            <br />
                                            <?php
                                            if($payment['fee_pay_through']=='bank'):
                                                ?>
                                                Bank : <?php echo $payment['bank_details'];?>
                                                <br />
                                                Bank Challan / TID No. : <?php echo $payment['tid_no'];?>
                                                <br />

                                                Merged against Challan. : <?php echo $payment['paid_challans'];?>
                                            <?php
                                            endif;
                                            ?>
                                            <?php
                                            if($payment['fee_pay_through']=='college' && $payment['fee_submit_type']=='receipt_book'):
                                                ?>
                                                Pad of : <?php echo @$this->db->get_where('campuses',array('campus_id'=>$payment['submitted_fee_campus_id']))->row()->campus_name;?>
                                                <br />
                                                Book No. : <?php echo $payment['book_no'];?>
                                                <br />
                                                Receipt No. : <?php echo $payment['receipt_no'];?>
                                                <br />
                                                Merged against Challan. : <?php echo $payment['paid_challans'];?>
                                            <?php
                                            endif;
                                            ?>
                                            <?php
                                            if($payment['fee_pay_through']=='college' && $payment['fee_submit_type']=='computer_challan'):
                                                ?>
                                                Pay by : Computer Challan
                                                <br />
                                                Merged against Challan. : <?php echo $payment['paid_challans'];?>
                                            <?php
                                            endif;
                                            ?>
                                            <div class="clearfix"></div>
                                            <br />
                                            <?php
                                            if($payment['scan_challan']=='')
                                            {

                                            }
                                            elseif($payment['scan_challan']!='' )
                                            {
                                                echo '<a href="'.base_url().'uploads/'.$payment['scan_challan'].'" target="_blank" class="btn purple pull-left">See Challan</a> <br />';
                                            }
                                            
                                            if($payment['fee_pay_through']=='college' && $payment['fee_submit_type']=='computer_challan')
                                            {
                                                echo '<a href="'.site_url().'/students/print_college_challan/'.$payment['id'].'" target="_blank" class="btn blue college_fee_'.$i.'"><i class="fa fa-print"></i> See Challan</a> <br />';
                                            }
                                            ?>
                                            <?php
                                            if($payment['fine_application']=='' && $payment['paid']==0)
                                            {

                                            }
                                            else if($payment['fine_application']!='' && $payment['paid']==1)
                                            {
                                                echo '<a href="'.base_url().'uploads/'.$payment['fine_application'].'" target="_blank" class="btn purple pull-left">See Application</a> <br />';
                                            }
                                            else
                                            {

                                            }
                                            ?>
                                            <div class="clearfix"></div>
                                        <?php
                                        endif;
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $fee_remarks = $this->db->get_where('fees_remarks', array('fee_id'=>$payment['id']))->result_array();
                                        foreach($fee_remarks as $fee_remark)
                                        {
                                            echo $fee_remark['comment'];
                                            echo '<br />';
                                            echo 'Add By : '.$fee_remark['add_by'].'<br />';
                                            echo 'Comment Date : '.$fee_remark['date'].'<br />';
                                            echo 'Next Date : '.$fee_remark['paid_on_date'];
                                            echo '<hr />';
                                        }
                                        ?>
										<br />
										
										<?php
                                        $update_requests = $this->db->get_where('update_payment_requests', array('id'=>$payment['id']))->result_array();
                                        foreach($update_requests as $update_request)
                                        {
                                            if($update_request['ok_by_admin']==1)
                                            {
                                                $status = '<span class="alert-success">Approved</span>';
                                            }
                                            else
                                            {
                                                $status = '<span class="alert-danger">Pending</span>';
                                            }
                                            echo 'Date : '.date('F d,Y',strtotime($update_request['update_date'])).'<br />';
                                            echo 'Reason : '.$update_request['reason'].'<br />';
                                            echo 'Clear By : '.$update_request['clear_by'].'<br />';
                                            echo 'Status : '.$status;
                                            echo '<hr />';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo $payment['system_comment'];?>
										
										<br />
										 <?php
                                        $remarks = $this->db->get_where('fees_remarks', array('fee_id'=>$payment['id']))->result_array();
                                        foreach($remarks as $remark)
                                        {
                                            echo 'Paid on Date : '.$remark['paid_on_date'].'<br />';
                                            echo 'Remarks : '.$remark['comment'].'<br />';
                                            echo 'Contact By : '.$remark['add_by'];
                                            echo '<hr />';
                                        }
                                        ?>
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
    </div>
</div>
<!-- END CONTENT -->