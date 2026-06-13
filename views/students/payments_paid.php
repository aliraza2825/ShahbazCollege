<?php
$myAccess = checkUserAccess();
?>
<style>
    .btn{
        margin-bottom:10px;
    }
</style>
<?php
//GET STUDENT DETAILS
$student_fees = $this_student = $this->db->get_where('students', array('student_id'=>$this->uri->segment(3)))->result_array();

//GET COURSE TOTAL FEE

$student_fee_plan = $this->db->get_where('fee_rules',array('fee_rule_id'=>$this_student[0]['plan_id']))->result_array();
if(count($student_fee_plan)>0)
{
    $total_fee = $student_fee_plan[0]['total_fee'];
}
else
{
    $total_fee = $this->db->get_where('fee_rules',array('course_id'=>$student_fees[0]['course_id']))->row()->total_fee;
}

$course = $this->db->get_where('courses', array('course_id'=>$student_fees[0]['course_id']))->result_array();

$course_type = $course[0]['course_type'] == 'Annual' ? 'Year' : $course[0]['course_type'];

//GET STUDENT SPECIAL DISCOUNT
$this->db->select('sum(discount) as special_disc');
$this->db->where('status = "1" and student_id = "'.$this->uri->segment(3).'"');
$specialdisc=$this->db->get('discounts_approval')->result_array();
if(count($specialdisc)>0)
{
    $specialdisc=$specialdisc[0]['special_disc'];
}
else
{
    $specialdisc=0;
}


//GET THIS STUDENT DECIDED FEE
$student_fee = $this_student[0]['total_fee'];


//GET FEE ON TIME OF ADMISSION
$total_fee = $this_student[0]['current_session_fee'];

//GET PAYMENTS RULES
$payment_rules = $this->db->get_where("payment_rules","status = 1 and course_id = '".$this_student[0]['course_id']."'")->result_array();
$reverse_amount = $this->db->select_sum('reverse_amount')->get_where("fee_reversals","status = 'completed' and student_id = '".$this->uri->segment(3)."'")->row();
$council_sequences = $this->db->get_where('council_sequence',array('course_id'=>$this_student[0]['course_id'],'action_type'=>'fee'))->result_array();
$student_class = $this->db->get_where('classes',array('class_id'=>$student[0]['class_id']))->row_array();
$all_exam_sequence = $this->db->get_where('exam_sequence',array('course_id'=>$this_student[0]['course_id'],'status'=>'Active','first_year >= '=>$student_class['exam_no']))->result_array();

function getOrdinal($number)
    {
        
        $suffixes = ['th','st','nd','rd','th','th','th','th','th','th'];
    
        if (($number % 100) >= 11 && ($number % 100) <= 13) {
            return $number . 'th';
        }
    
        return $number . $suffixes[$number % 10];
    }


?>
<!-- BEGIN CONTENT -->
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
            
            <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12" style="height: 120px;" >
                <div class="dashboard-stat red">
                    <div class="visual">
                        <i class="fa fa-money"></i>
                    </div>
                    <div class="details">
					
						<div class="details-text" style="color:#fff; font-size:12px; text-align:center">
                            <br />
                            Disc on Admission : <?php echo ($total_fee-$student_fee)-$specialdisc;?>
                            <br />
                            Special Disc : <?php echo $specialdisc;?>
							<br />
                            Dis on Fee Merged : <?php echo $discountfee;?>
							<br />
                            Total Disc : <?php echo $total_fee-$student_fee+$discountfee;?>
                        </div>
					
                    </div>
                    <a class="more" href="<?php echo site_url().'/students/all_discount_details/'.$this->uri->segment(3);?>">
                        View more <i class="m-icon-swapright m-icon-white"></i>
                    </a>
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
                            This Student Fees
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
                            <?php echo $discount;?>
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
                            <?php echo ($fee_not_created = $student_fee-$discount);?>
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
                            if($student_fee<1)
                            {
                                echo 'N/A';
                            }
                            else
                            {
                                $percent = ((@$paid_fee[0]['paid_fee']/@$student_fee)*100);
                                echo round(@$percent).'%';
                            }
                            ?>
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
                            No. of CF : <?php echo count($this->db->group_by('payment_comment')->get_where('payments',array('student_id'=>$this->uri->segment(3),'payment_plan'=>'consulation fee'))->result_array());?>
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
                            <?php echo @$reversed_amount[0]['reversal_amount'];?>
                        </div>
                        <div class="desc">
                            Reversed Amount
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
                        <div class="details-text" style="color:#fff; font-size:16px; text-align:center">
                            <br />
                            Total Fee Deleted : <?php echo $getCountDeletedFess;?>
                            <br />
                            Total Fee Shifted : <?php echo $getCountShiftedFess;?>
                        </div>
                        <div class="desc">
                            <!--Total Fee Delete / Shift-->
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
        <?php
			if($student_fees[0]['status']==0)
			{
				$this->db->select('*');
				$this->db->from('freeze_student');
				$this->db->where("(freeze_student.student_id = '".$student[0]['student_id']."')", NULL, FALSE);
				$freezedata = $this->db->get()->result_array();
				
				if(count($freezedata)>0)
				{
					echo '<br /><span class="blink_me" style="font-weight:bold;font-size:18px;color: blue;">FREEZED STUDENT</span>';
				}else{
					echo '<br /><span class="blink_me" style="font-weight:bold;font-size:18px;color:#F00;">DELETED STUDENT</span>';
				}
			}
		?>

        <div class="row">
            <div class="header" id="myHeader" style="margin-bottom:10px; z-index: 100">
                    <?php
                    if($student_fees[0]['status']!=0):
                        ?>
                        <div class="row">
                            <div class="col-md-9">
                            <button type="button" class="btn green pay_payment" data-button-number="" onclick="getselected();" data-toggle="modal" href="#payfee"><i class="fa fa-cloud-upload"></i> Pay Installment</button>
                            <button type="button" class="btn purple bank_fee" data-button-number="" onclick="openchallan();"> <i class="fa fa-print"></i> Bank Challan</button>
                            <button type="button" class="btn green discount_payment" data-button-number=""  data-toggle="modal" href="#applydiscount"><i class="fa fa-money"></i> Apply Discount </button>
                            <a class="btn red" href="<?php echo site_url() ?>/students/admission_letter_print/<?php echo $this->uri->segment(3);?>"><i class="fa fa-list"></i> Rules and Regulation Form </a>
                            <?php
                                if($fee_not_created>0):
                            ?>
                            <a class="btn yellow" href="<?php echo site_url() ?>/students/create_auto_fees/<?php echo $this->uri->segment(3);?>"><i class="fa fa-money"></i> Generate Remaining Fee </a>
                            <?php
                                endif;
                            ?>
                            <?php
                                if(count($deleted_payments)>0):
                            ?>
                            <button class="btn btn-danger view_deleted_installments" type="button"><i class="fa fa-money"></i> View Deleted Installments </button>
                            <button class="btn btn-info hide_deleted_installments" style="display:none;" type="button"><i class="fa fa-money"></i> Hide Deleted Installments </button>
                            <?php
                                endif;
                            ?>
                            </div>
                    <?php
                    endif;
                    ?>
                    <div class="col-md-3 row">
                        <?php
                            if(count($old_plans)>0):
                        ?>
                        <form class="form-horizontal" role="form" target="_blank" method="post" action="<?php echo site_url();?>/students/payments_paid_old/<?php echo $this->uri->segment(3);?>" enctype="multipart/form-data">
                        <div class="col-md-8">
                            <select class="form-control" id="old_plan" name="old_plan" required>
                                <option value="">SELECT Old Plan</option>
                                <?php
                                foreach ($old_plans as $key=>$plan):
                                    ?>
                                    <option value="<?php echo $plan['payment_id'];?>"><?php echo "Plan - ".($key+1);?></option>
    
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                        <button type="submit" class="btn green">Show</button>
                        </div>
                        </form>
                        <?php
                            endif;
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-md-12 ">
                <br />
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet box grey-cascade">
                    <div class="portlet-title ">
                        <div class="caption">
                            <i class="fa fa-money"></i> <?php echo $student[0]['first_name'].' '.$student[0]['last_name'].' '.$student[0]['roll_no']; ?> | Cell # <?php echo $student[0]['mobile'];?> - <?php echo $student[0]['emergency_no'];?> | Course : <?php echo $this->db->get_where('courses',array('course_id'=>$student[0]['course_id']))->row()->course_name;?> | Class : <?php echo $this->db->get_where('classes',array('class_id'=>$student[0]['class_id']))->row()->name;?> (Plan ID : <?php echo $student[0]['plan_id'];?>)
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
                                <!--<th>
                                    System Comments
                                </th>-->
                                
                                <th>
                                    Action
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                                $i=0;
    							$showed=false;
    							$first_unpaid_showed = false;
    							$normal_fee_index = 0;
                                foreach($payments as $payment):
                            ?>
                                <tr class="odd gradeX <?php if(($payment['payment_comment'] == 'consulation fee' || $payment['payment_plan'] == 'consulation fee') && $payment['paid']==0){echo 'info';}?> <?php if(($payment['payment_comment'] == 'consulation fee' || $payment['payment_plan'] == 'consulation fee') && $payment['paid']==1){echo 'success';}?>">

                                    <td>
                                        <?php echo $i+1;  ?>
                                        <?php
                                            $special_comments = [
                                                'consulation fee',
                                                'Extra Fee For Notes',
                                                'Extra Fee For Books'
                                            ];
                                            
                                            if ($payment['paid'] == 0):

                                                $is_special_fee = in_array($payment['payment_comment'], $special_comments);
                                            
                                                if ($is_special_fee) {
                                            
                                                    ?>
                                            
                                                    <input 
                                            
                                                        type="checkbox"
                                            
                                                        class="selection special-selection"
                                            
                                                        id="check_special_<?php echo $i; ?>"
                                            
                                                        name="selection[]"
                                            
                                                        value="<?php echo $i; ?>"
                                            
                                                    />
                                            
                                                    <?php
                                            
                                                } else {
                                            
                                                    $normal_fee_index++;
                                            
                                                    if ($first_unpaid_showed == false) {
                                            
                                                        ?>
                                            
                                                        <input 
                                            
                                                            type="checkbox"
                                            
                                                            class="selection normal-selection"
                                            
                                                            id="check_<?php echo $normal_fee_index; ?>"
                                            
                                                            data-payment-id="<?php echo $i; ?>"
                                            
                                                            name="selection[]"
                                            
                                                            value="<?php echo $i; ?>"
                                            
                                                            data-index="<?php echo $normal_fee_index; ?>"
                                            
                                                        />
                                            
                                                        <?php
                                            
                                                        $first_unpaid_showed = true;
                                            
                                                    } else {
                                            
                                                        ?>
                                            
                                                        <div id="box_<?php echo $normal_fee_index; ?>" class="test" style="display:none;">
                                            
                                                            <input 
                                            
                                                                type="checkbox"
                                            
                                                                class="selection normal-selection"
                                            
                                                                id="check_<?php echo $normal_fee_index; ?>"
                                            
                                                                data-payment-id="<?php echo $i; ?>"
                                            
                                                                name="selection[]"
                                            
                                                                value="<?php echo $i; ?>"
                                            
                                                                data-index="<?php echo $normal_fee_index; ?>"
                                            
                                                            />
                                            
                                                        </div>
                                            
                                                        <?php
                                            
                                                    }
                                            
                                                }
                                            
                                            endif;
                                            ?>

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
                                            $this->db->order_by('payment_comment','DESC');
                                            $mergchalans=$this->db->where_in('payments.challan_no', explode(',', $payment_ids))->get()->result_array();


                                            foreach ($mergchalans as $merg){
                                                $totalpayable+=$merg['amount'];
												$challan_date = date_create($merg['dead_line']);
                                                $paid_date = date_create($merg['paid_date']);
                                                $diff=date_diff($challan_date,$paid_date);
                                                $difference = $diff->format("%R%a");

                                                if($difference>0) {
                                                    $totalfine += $difference*50;
                                                }
                                                ?>
                                                <br />
                                                Merged Challan # : <?php echo $merg['challan_no']; ?>

                                                <span id="comment-text-<?php echo $merg['id']; ?>">
                                                    <?php echo htmlspecialchars($merg['payment_comment']); 
                                                    if( $merg['council_sequence_id'] && $merg['council_sequence_id'] != ''){
                                                        $sequence = $this->db->get_where('council_sequence','council_sequence_id ='.$merg['council_sequence_id'])->row_array();
                                                        $exam_sequence = $this->db->get_where('exam_sequence','id ='.$merg['exam_sequence_id'])->row_array();
                                                        echo ' '.$sequence['type_name'].' '.$exam_sequence['first_year_type'];
                                                    }?>
                                                </span>

                                                <!--<input type="text"-->
                                                <!--       id="comment-input-<?php echo $merg['id']; ?>"-->
                                                <!--       value="<?php echo htmlspecialchars($merg['payment_comment']); ?>"-->
                                                <!--       style="display:none; width:250px;" />-->
                                               <br>
                                               <select id="comment-input-<?php echo $merg['id']; ?>"
                                                        style="display:none; width:250px; margin-top:5px;">
                                                    <option value="">Select Exam No</option>
                                                    <?php foreach($all_exam_sequence as $seq): ?>
                                                        <option value="<?php echo $seq['id']; ?>"
                                                            <?php if($merg['exam_sequence_id'] == $seq['id']) echo 'selected'; ?>>
                                                            <?php echo $seq['first_year'].' - '.$seq['first_year_type'].' - '.getOrdinal($seq['class']).' '.$course_type; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                
                                                <select id="council-sequence-<?php echo $merg['id']; ?>"
                                                        style="display:none; width:250px; margin-top:5px;">
                                                    <option value="">Select Council Sequence</option>
                                                    <?php foreach($council_sequences as $seq): ?>
                                                        <option value="<?php echo $seq['council_sequence_id']; ?>"
                                                            <?php if($merg['council_sequence_id'] == $seq['council_sequence_id']) echo 'selected'; ?>>
                                                            <?php echo $seq['type_name']; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <br>
                                                <div class="form-group payment_for_container" style="display:none;" id="payment-comment-<?php echo $merg['id']; ?>"> 
                                                
                                                <label class="control-label">please Add Reason and Undertaken<span class="required">*</span></label>
                                                    <input type="text" class="form-control input-inline input-medium payment_for" id="payment-comment-text-<?php echo $merg['id']; ?>" name="payment_for" value="" /> 
                                                    <br>
                                                    <input type="file" name="image" id="council-image-<?php echo $merg['id']; ?>" class="form-control"/>
                                                </div>
                                                <?php if((@$myAccess[0]['change_exam_no_in_payments']==1 && $merg['payment_comment'] != 'College Fee' && $merg['paid'] == 0) || ($this->session->userdata('role')=='Admin'&& $merg['payment_comment'] != 'College Fee') ):?>
                                                <button type="button" onclick="editComment(<?php echo $merg['id']; ?>)">Edit</button>
                                                <?php endif; ?>
                                                
                                                <button type="button"
                                                        onclick="saveComment(<?php echo $merg['id']; ?>)"
                                                        id="save-btn-<?php echo $merg['id']; ?>"
                                                        style="display:none;">Save</button>

                                                (Dead Line : <?php echo $merg['dead_line']; ?>)

                                                <br />
                                                <strong>Merged Amount : <?php echo $merg['amount']; ?></strong>
                                         <?php
                                            }
                                        }

                                        else {
                                            $totalpayable=$payment['amount'];
                                            ?>
                                            Challan # : <?php echo $payment['challan_no'];?>  <span id="comment-text-<?php echo $payment['id']; ?>">
                                                    <?php echo htmlspecialchars($payment['payment_comment']); 
                                                    if( $payment['council_sequence_id'] && $payment['council_sequence_id'] != ''){
                                                        $sequence = $this->db->get_where('council_sequence','council_sequence_id = '.$payment['council_sequence_id'])->row_array();
                                                        $exam_sequenc = $this->db->get_where('exam_sequence','id ='.$payment['exam_sequence_id'])->row_array();
                                                        echo ' '.$sequence['type_name'].' '.$exam_sequenc['first_year_type'];
                                                        // echo ' '.$sequence['type_name'];
                                                    }
                                                    ?>
                                                </span>

                                            <select id="comment-input-<?php echo $payment['id']; ?>"
                                                        style="display:none; width:250px; margin-top:5px;">
                                                    <option value="">Select Exam No</option>
                                                    <?php foreach($all_exam_sequence as $seq): ?>
                                                        <option value="<?php echo $seq['id']; ?>"
                                                            <?php if($payment['exam_sequence_id'] == $seq['id']) echo 'selected'; ?>>
                                                            <?php echo $seq['first_year'].' - '.$seq['first_year_type'].' - '.getOrdinal($seq['class']).' '.$course_type; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            
                                            <select id="council-sequence-<?php echo $payment['id']; ?>"
                                                    style="display:none; width:250px; margin-top:5px;">
                                                <option value="">Select Council Sequence</option>
                                                <?php foreach($council_sequences as $seq): ?>
                                                    <option value="<?php echo $seq['council_sequence_id']; ?>"
                                                        <?php if($payment['council_sequence_id'] == $seq['council_sequence_id']) echo 'selected'; ?>>
                                                        <?php echo $seq['type_name']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <br>
                                            <div class="form-group payment_for_container" style="display:none;" id="payment-comment-<?php echo $payment['id']; ?>"> 
                                            <label class="control-label">please Add Reason and Undertaken<span class="required">*</span></label>
                                                <input type="text" class="form-control input-inline input-medium payment_for" id="payment-comment-text-<?php echo $payment['id']; ?>" name="payment_for" value="" /> 
                                                    <br>
                                                    <input type="file" name="image" id="council-image-<?php echo $payment['id']; ?>" class="form-control"/>
                                            </div>
                                            
                                            <?php if((@$myAccess[0]['change_exam_no_in_payments']==1 && $payment['payment_comment'] != 'College Fee' && $payment['paid'] == 0) || ($this->session->userdata('role')=='Admin'&& $payment['payment_comment'] != 'College Fee') ):?>
                                            <button type="button" onclick="editComment(<?php echo $payment['id']; ?>)">Edit</button>
                                            <?php endif; ?>
                                            
                                            <button type="button"
                                                    onclick="saveComment(<?php echo $payment['id']; ?>)"
                                                    id="save-btn-<?php echo $payment['id']; ?>"
                                                    style="display:none;">Save</button>
											<br />
                                            <strong>Installment Amount : <?php echo $payment['amount'];?></strong>

                                        <?php }
                                        ?>
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
                                    <td id="deadline_<?php echo $payment['id']; ?>">
                                        <?php 
                                        echo $payment['dead_line'];
                                        if(($payment['payment_comment'] == 'consulation fee' || $payment['payment_plan'] == 'consulation fee')) 
                                        { 
                                            echo '<br>Council Fee Last Date : '.get_council_date($payment['id']);
                                        }?>
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
                                                Bank Statement ID : <?php echo $payment['statement_id'];?>
                                                <br />
                                                Bank Statement no. : <?php echo @$this->db->get_where('bank_reconciliation_statement',array('id'=>$payment['statement_id']))->row()->statement_no;?>
                                                <br />
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
                                                if($payment['online_scan_challan']!='')
                                                {
                                                    if($payment['merged_challan']!='')
                                                    {
                                                        $this->db->order_by('id','desc');
                                                        $my_challan = $this->db->get_where('payments',array('merged_challan'=>$payment['merged_challan']))->result_array();
                                                        echo '<a href="'.str_replace($bucket_address,$cloudfront_address,$my_challan[0]['online_scan_challan']).'" target="_blank" class="btn purple pull-left">See Challan</a>';
                                                    }
                                                    else
                                                    {
                                                        echo '<a href="'.str_replace($bucket_address,$cloudfront_address,$payment['online_scan_challan']).'" target="_blank" class="btn purple pull-left">See Challan</a>';
                                                    }
                                                }
                                                else
                                                {
                                                    echo '<a href="'.base_url().'uploads/'.$payment['scan_challan'].'" target="_blank" class="btn purple pull-left">See Challan</a> <br />';
                                                }
                                            }
                                            
                                            if($payment['fee_pay_through']=='college' && $payment['fee_submit_type']=='computer_challan')
                                            {
                                                echo '<a href="'.site_url().'/students/print_college_challan/'.$payment['id'].'" target="_blank" class="btn blue college_fee_'.$i.'"><i class="fa fa-print"></i> See Challan</a> <br />';
                                            }
                                            
                                            if($payment['fee_pay_through']=='pay_pro' && $payment['paid']==1)
                                            {
                                                $this->db->select('*');
                                                $this->db->from('students_payments');
                                                $this->db->where('transaction_status','PAID');
                                                $this->db->like('challan_ids',$payment['paid_challans']);
                                                $link = @$this->db->get()->result_array();
                                                echo '<a href="'.$link[0]['bill_url'].'" target="_blank" class="btn grey"><i class="fa fa-print"></i> See Challan</a> <br />';
                                            }
                                            
                                            ?>
                                            <?php
                                            if($payment['fine_application']=='' && $payment['paid']==0)
                                            {

                                            }
                                            else if($payment['fine_application']!='' && $payment['paid']==1)
                                            {
                                                if($payment['online_fine_application']!='')
                                                {
                                                    echo '<a href="'.str_replace($bucket_address,$cloudfront_address,$payment['online_fine_application']).'" target="_blank" class="btn purple pull-left">See Application</a> <br />';
                                                }
                                                else
                                                {
                                                    echo '<a href="'.base_url().'uploads/'.$payment['fine_application'].'" target="_blank" class="btn purple pull-left">See Application</a> <br />';
                                                }
                                            }
                                            else
                                            {

                                            }

                                            if(($payment['fee_pay_through']=='bank' && @$payment['clear_by'] == "") || ($payment['fee_pay_through']=='pay_pro' && $payment['settlement_id'] == null) || ($payment['fee_pay_through']!='pay_pro' && $payment['fee_pay_through']!='bank' && $payment['closing_id'] == null))
                                            {

                                            }
                                            else {
                                                if(@$myAccess[0]['student_issue_refund']==1 || $this->session->userdata('role')=='Admin'):
                                                    //echo '<a onclick="open_reversal_modal('.$i.')" class="btn red"><i class="fa fa-recycle"></i> Want Reversal?</a>';
                                                    $reversal_checker = $this->db->get_where('payments_reversal_requests',array('payment_id'=>$payment['id']))->result_array();
                                                    if(count($reversal_checker)>0)
                                                    {
                                                        if($reversal_checker[0]['status']==0 && $reversal_checker[0]['approve_status']==0)
                                                        {
                                                            echo '<button type="button" class="btn red">Reversal Under Review <i class="fa fa-spinner fa-spin"></i></button>';
                                                        }
                                                        elseif($reversal_checker[0]['status']==1 && $reversal_checker[0]['approve_status']==0)
                                                        {
                                                            echo '<button type="button" class="btn red">Reversal Rejected <i class="fa fa-ban"></i></button>';   
                                                        }
                                                        elseif($reversal_checker[0]['status']==1 && $reversal_checker[0]['approve_status']==1 && $reversal_checker[0]['done']==0)
                                                        {
                                                            echo '<button onclick="fee_reversal_proof_modal('.$i.')" type="button" class="btn green">Reversal Approved <i class="fa fa-spinner fa-spin"></i></button>';   
                                                        }
                                                        elseif($reversal_checker[0]['status']==1 && $reversal_checker[0]['approve_status']==1 && $reversal_checker[0]['done']==1)
                                                        {
                                                            if($reversal_checker[0]['online_reversal_application']==''):
                                                                $reversal_application=base_url().'uploads/'.$reversal_checker[0]['reversal_application'];
                                                            else:
                                                                $reversal_application=str_replace($bucket_address,$cloudfront_address,$reversal_checker[0]['online_reversal_application']);
                                                            endif;
                                                            if($reversal_checker[0]['online_proof_image']==''):
                                                                $proof_image=base_url().'uploads/'.$reversal_checker[0]['proof_image'];
                                                            else:
                                                                $proof_image=str_replace($bucket_address,$cloudfront_address,$reversal_checker[0]['online_proof_image']);
                                                            endif;
                                                            echo '<br /><br /><br /><span class="blink_me" style="font-weight:bold;font-size:18px;color:#F00;">PAYMENT REFUNDED</span>';

                                                            echo '</br ><span class="alert-danger" style="font-size:14px; font-weight:bold;">Reversal Amount: '.$reversal_checker[0]['reversal_amount'].'<br />Reversal Reason: '.$reversal_checker[0]['reversal_reason'].'<br />Reversal Created By: '.$reversal_checker[0]['created_by'].'<br />Reversal Paid By: '.$reversal_checker[0]['paid_by'].'<br />Reversal Application: <a href="'.$reversal_application.'" target="_blank">Application</a><br />Reversal Proof: <a href="'.$proof_image.'" target="_blank">Proof</a></span>';                                                            
                                                        }
                                                    }
                                                    else
                                                    {
                                                        echo '<a onclick="fee_reversal_modal('.$i.')" class="btn red"><i class="fa fa-recycle"></i> Want Reversal?</a>';
                                                    }
                                                endif;
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
                                    <?php /*<td>
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
                                    */?>
                                    <td>
                                        <?php
                                        if($student_fees[0]['status']==1):
                                        ?>
                                            <?php
                                            if($payment['paid']==0 ):
                                            ?>
                                                <?php
                                                if($payment['payment_plan']!='consulation fee'):
                                                    ?>
                                                    <?php
                                                    if($payment['split']<1):
                                                        ?>
                                                        <button type="button" class="btn red" data-toggle="modal" href="#split_fee_<?php echo $i;?>"><i class="fa fa-code"></i> Split Installment</button>
                                                    <?php
                                                    endif;
                                                    ?>
                                                    <div id="split_fee_<?php echo $i;?>" class="modal fade" tabindex="-1" data-width="760">
                                                        <div class="modal-header">
                                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                                                            <h4 class="modal-title">Split Installment</h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <form action="<?php echo site_url();?>/students/split_payment/<?php echo $this->uri->segment(3);?>" method="post">
                                                                <div class="row">
                                                                    <div class="alert alert-danger">
                                                                        <p class="text-center">You can split maximum 80% of fee amount. You can create new installment within the date of one month.</p>
                                                                    </div>
                                                                    <div class="col-md-6" style="border-right:1px dotted #CCC;">
                                                                        <h2 class="text-center">Current Installment</h2>
                                                                        <br />
                                                                        <div class="form-group">
                                                                            <label class="col-md-4 control-label">Installment Amount <span class="required">*</span></label>
                                                                            <div class="col-md-8">
                                                                                <input type="number" min="<?php echo round($payment['amount']*20/100);?>" max="<?php echo $payment['amount'];?>" class="form-control input-inline input-small current_amount current_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" data-value="<?php echo $payment['amount'];?>"  name="current_amount" placeholder="Enter student installment amount" value="<?php echo $payment['amount'];?>" required>
                                                                                <span class="help-inline"></span>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label class="col-md-4 control-label">Previous Installment Amount <span class="required">*</span></label>
                                                                            <div class="col-md-8">
                                                                                <input type="number" min="<?php echo $payment['remaining_installment_amount']/2;?>" max="<?php echo $payment['remaining_installment_amount'];?>" class="form-control input-inline input-small current_remaining_installment_amount current_remaining_installment_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" data-value="<?php echo $payment['remaining_installment_amount'];?>" name="current_remaining_installment_amount" placeholder="Enter student installment amount" value="<?php echo $payment['remaining_installment_amount'];?>" required>
                                                                                <span class="help-inline"></span>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label class="col-md-4 control-label">Previous Fine Amount <span class="required">*</span></label>
                                                                            <div class="col-md-8">
                                                                                <input type="number" min="<?php echo $payment['extra_amount']/2;?>" max="<?php echo $payment['extra_amount'];?>" class="form-control input-inline input-small current_extra_amount current_extra_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" data-value="<?php echo $payment['extra_amount'];?>" name="current_extra_amount" placeholder="Enter student fine amount" value="<?php echo $payment['extra_amount'];?>" required>
                                                                                <span class="help-inline"></span>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label class="col-md-4 control-label">Dead Line <span class="required">*</span></label>
                                                                            <div class="col-md-8">
                                                                                <?php echo $payment['dead_line']?>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <h2 class="text-center">New Installment</h2>
                                                                        <br />
                                                                        <div class="form-group">
                                                                            <label class="col-md-4 control-label">Installment Amount <span class="required">*</span></label>
                                                                            <div class="col-md-8">
                                                                                <input type="number" max="<?php echo $payment['amount']/2;?>" class="form-control input-inline input-small new_amount new_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" name="new_amount" placeholder="Enter student installment amount" value="0" readonly required />
                                                                                <span class="help-inline"></span>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label class="col-md-4 control-label">Previous Installment Amount <span class="required">*</span></label>
                                                                            <div class="col-md-8">
                                                                                <input type="number" max="<?php echo $payment['remaining_installment_amount']/2;?>" class="form-control input-inline input-small new_remaining_installment_amount new_remaining_installment_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" name="new_remaining_installment_amount" placeholder="Enter student installment amount" value="0" readonly required />
                                                                                <span class="help-inline"></span>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label class="col-md-4 control-label">Previous Fine Amount <span class="required">*</span></label>
                                                                            <div class="col-md-8">
                                                                                <input type="number" max="<?php echo $payment['extra_amount']/2;?>" class="form-control input-inline input-small new_extra_amount new_extra_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" name="new_extra_amount" placeholder="Enter student fine amount" value="0" readonly required>
                                                                                <span class="help-inline"></span>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label class="control-label col-md-4">Dead Line <span class="required">*</span></label>
                                                                            <?php
                                                                            $payment_dead_line=date_create($payment['dead_line']);
                                                                            $today_date=date_create(date('Y-m-d'));
                                                                            $diff=date_diff($today_date,$payment_dead_line);
                                                                            if($diff->invert==1)
                                                                            {
                                                                                $days='-'.$diff->days;
                                                                            }
                                                                            else
                                                                            {
                                                                                $days='+'.$diff->days;
                                                                            }
                                                                            $start_days = $days;
                                                                            $end_days = $days+32;
                                                                            if($end_days>=0)
                                                                            {
                                                                                $end_days='+'.$end_days;
                                                                            }
                                                                            else
                                                                            {
                                                                                $end_days=$end_days;
                                                                            }
                                                                            ?>
                                                                            <div class="col-md-8">
                                                                                <div class="input-group input-small date date-picker" data-date="<?php echo $payment['dead_line']?>" data-date-format="yyyy-mm-dd" data-date-start-date="<?php echo $start_days;?>d" data-date-end-date="<?php echo $end_days;?>d" data-date-viewmode="years">
                                                                                    <input type="text" name="new_dead_line" class="form-control" value="<?php echo $payment['dead_line']?>" readonly>
                                                                                    <span class="input-group-btn">
                                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12 text-center">
                                                                        <input type="hidden" name="current_dead_line" value="<?php echo $payment['dead_line']?>">
                                                                        <input type="hidden" name="current_id" value="<?php echo $payment['id']?>">

                                                                        <button type="submit" class="btn red"><i class="fa fa-code"></i> Split Installment</button>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" data-dismiss="modal" class="btn btn-default">Close</button>
                                                        </div>
                                                    </div>
                                                <?php
                                                endif;
                                                ?>

                                                <?php
                                                if($payment['payment_plan']=='consulation fee'):
                                                    ?>
                                                    <?php
                                                    if($payment['split']<1):
                                                        ?>
                                                        <button type="button" class="btn red" data-toggle="modal" href="#split_fee_<?php echo $i;?>"><i class="fa fa-code"></i> Split Installment</button>
                                                    <?php
                                                    endif;
                                                    ?>
                                                    <div id="split_fee_<?php echo $i;?>" class="modal fade" tabindex="-1" data-width="760">
                                                        <div class="modal-header">
                                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                                                            <h4 class="modal-title">Split Installment</h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <form action="<?php echo site_url();?>/students/split_consulation_payment/<?php echo $this->uri->segment(3);?>" method="post">
                                                                <div class="row">
                                                                    <div class="alert alert-danger">
                                                                        <p class="text-center">You can split maximum 80% of fee amount. You can create new installment within the date of one month.</p>
                                                                    </div>
                                                                    <div class="col-md-6" style="border-right:1px dotted #CCC;">
                                                                        <h2 class="text-center">Current Installment</h2>
                                                                        <br />
                                                                        <div class="form-group">
                                                                            <label class="col-md-4 control-label">Installment Amount <span class="required">*</span></label>
                                                                            <div class="col-md-8">
                                                                                <input type="number" min="<?php echo round($payment['amount']*20/100);?>" max="<?php echo $payment['amount'];?>" class="form-control input-inline input-small current_amount current_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" data-value="<?php echo $payment['amount'];?>"  name="current_amount" placeholder="Enter student installment amount" value="<?php echo $payment['amount'];?>" required>
                                                                                <span class="help-inline"></span>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label class="col-md-4 control-label">Previous Installment Amount <span class="required">*</span></label>
                                                                            <div class="col-md-8">
                                                                                <input type="number" min="<?php echo round($payment['remaining_installment_amount']*20/100);?>" max="<?php echo $payment['remaining_installment_amount'];?>" class="form-control input-inline input-small current_remaining_installment_amount current_remaining_installment_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" data-value="<?php echo $payment['remaining_installment_amount'];?>" name="current_remaining_installment_amount" placeholder="Enter student installment amount" value="<?php echo $payment['remaining_installment_amount'];?>" required>
                                                                                <span class="help-inline"></span>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label class="col-md-4 control-label">Previous Fine Amount <span class="required">*</span></label>
                                                                            <div class="col-md-8">
                                                                                <input type="number" min="<?php echo round($payment['extra_amount']*20/100);?>" max="<?php echo $payment['extra_amount'];?>" class="form-control input-inline input-small current_extra_amount current_extra_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" data-value="<?php echo $payment['extra_amount'];?>" name="current_extra_amount" placeholder="Enter student fine amount" value="<?php echo $payment['extra_amount'];?>" required>
                                                                                <span class="help-inline"></span>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label class="col-md-4 control-label">Dead Line <span class="required">*</span></label>
                                                                            <div class="col-md-8">
                                                                                <?php echo $payment['dead_line']?>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <h2 class="text-center">New Installment</h2>
                                                                        <br />
                                                                        <div class="form-group">
                                                                            <label class="col-md-4 control-label">Installment Amount <span class="required">*</span></label>
                                                                            <div class="col-md-8">
                                                                                <input type="number" max="<?php echo round($payment['amount']*20/100);?>" class="form-control input-inline input-small new_amount new_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" name="new_amount" placeholder="Enter student installment amount" value="0" readonly required />
                                                                                <span class="help-inline"></span>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label class="col-md-4 control-label">Previous Installment Amount <span class="required">*</span></label>
                                                                            <div class="col-md-8">
                                                                                <input type="number" max="<?php echo round($payment['remaining_installment_amount']*20/100);?>" class="form-control input-inline input-small new_remaining_installment_amount new_remaining_installment_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" name="new_remaining_installment_amount" placeholder="Enter student installment amount" value="0" readonly required />
                                                                                <span class="help-inline"></span>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label class="col-md-4 control-label">Previous Fine Amount <span class="required">*</span></label>
                                                                            <div class="col-md-8">
                                                                                <input type="number" max="<?php echo round($payment['extra_amount']*20/100);?>" class="form-control input-inline input-small new_extra_amount new_extra_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" name="new_extra_amount" placeholder="Enter student fine amount" value="0" readonly required>
                                                                                <span class="help-inline"></span>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label class="control-label col-md-4">Dead Line <span class="required">*</span></label>
                                                                            <?php
                                                                            $payment_dead_line=date_create($payment['dead_line']);
                                                                            $today_date=date_create(date('Y-m-d'));
                                                                            $diff=date_diff($today_date,$payment_dead_line);
                                                                            if($diff->invert==1)
                                                                            {
                                                                                $days='-'.$diff->days;
                                                                            }
                                                                            else
                                                                            {
                                                                                $days='+'.$diff->days;
                                                                            }
                                                                            $start_days = $days;
                                                                            $end_days = $days+32;
                                                                            if($end_days>=0)
                                                                            {
                                                                                $end_days='+'.$end_days;
                                                                            }
                                                                            else
                                                                            {
                                                                                $end_days=$end_days;
                                                                            }
                                                                            ?>
                                                                            <div class="col-md-8">
                                                                                <div class="input-group input-small date date-picker" data-date="<?php echo $payment['dead_line']?>" data-date-format="yyyy-mm-dd" data-date-start-date="<?php echo $start_days;?>d" data-date-end-date="<?php echo $end_days;?>d" data-date-viewmode="years">
                                                                                    <input type="text" name="new_dead_line" class="form-control" value="<?php echo $payment['dead_line']?>" readonly>
                                                                                    <span class="input-group-btn">
                                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12 text-center">
                                                                        <input type="hidden" name="current_dead_line" value="<?php echo $payment['dead_line']?>">
                                                                        <input type="hidden" name="current_id" value="<?php echo $payment['id']?>">

                                                                        <button type="submit" class="btn red"><i class="fa fa-code"></i> Split Installment</button>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" data-dismiss="modal" class="btn btn-default">Close</button>
                                                        </div>
                                                    </div>
                                                <?php
                                                endif;
                                                ?>

                                                <div id="responsive<?php echo $i;?>" class="modal fade" tabindex="-1" data-width="760">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                                                        <h4 class="modal-title">Fee Submission</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <strong>Challan #</strong> : <?php echo $payment['challan_no'];?>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <strong>Installment Amount</strong> : <?php echo $payment['amount'];?>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <strong>Dead Line</strong> : <?php echo $payment['dead_line'];?>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <strong>Fee Type</strong> : <?php echo $payment['payment_comment'];?>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <strong>Previous Installment Amount</strong> : <?php echo $payment['remaining_installment_amount'];?>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <strong>Previous Fine Amount</strong> : <?php echo $payment['extra_amount'];?>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <strong>Late Fee Amount</strong> : <?php echo $fee_fine;?>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <strong>Late Fee Days</strong> : <?php echo str_replace('+','',$difference).' Days';?>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <strong>Payable Amount</strong> : <?php echo $payable_amount = ($payment['amount']+$payment['remaining_installment_amount']+$payment['extra_amount']+$fee_fine);?>
                                                            </div>

                                                            <div class="clearfix"></div>
                                                            <br />
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <form action="<?php echo site_url().'/students/paid_payment_action/'.$this->uri->segment(3);?>" method="post" enctype="multipart/form-data">
                                                                    <div class="form-group">
                                                                        <label class="col-md-4 control-label">Fee Pay Through <span class="required">*</span></label>
                                                                        <div class="col-md-8 radio-list">
                                                                            <label class="radio-inline">
                                                                                <input type="radio" class="submit_in" name="fee_pay_through" id="optionsRadios5" value="bank" checked /> Bank </label>
                                                                            <label class="radio-inline">
                                                                                <input type="radio"  class="submit_in" name="fee_pay_through" id="optionsRadios4" value="college" /> College </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="clearfix"></div>
                                                                    <br />
                                                                    <div class="bank">
                                                                        <div class="form-group">
                                                                            <label class="col-md-4 control-label">Select Bank <span class="required">*</span></label>
                                                                            <div class="col-md-8">
                                                                                <select class="form-control bank_details" name="bank_details" required>
                                                                                    <option value="">SELECT BANK</option>
                                                                                    <?php
                                                                                    $count = count($account_numbers);
                                                                                    for($a=0;$a<$count;$a++):
                                                                                        ?>
                                                                                        <option value="<?php echo $bank_names[$a].' ('.$account_numbers[$a].')';?>"><?php echo $bank_names[$a].' ('.$account_numbers[$a].')';?></option>
                                                                                    <?php
                                                                                    endfor;
                                                                                    ?>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="clearfix"></div>
                                                                        <br />
                                                                        <div class="form-group">
                                                                            <label class="col-md-4 control-label">Bank Challan / TID No.</label>
                                                                            <div class="col-md-8">
                                                                                <input type="text" class="form-control input-inline input-medium" name="tid_no" placeholder="Enter TID Number" value="" >
                                                                                <span class="help-inline"></span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="clearfix"></div>
                                                                    </div>
                                                                    <div class="clearfix"></div>
                                                                    <div class="form-group college" style="display:none;">
                                                                        <label class="col-md-4 control-label">Fee Submit Type <span class="required">*</span></label>
                                                                        <div class="col-md-8 radio-list">
                                                                            <label class="radio-inline">
                                                                                <input type="radio" class="pay_by" name="fee_submit_type" id="optionsRadios5" value="computer_challan" checked /> Pay By Computer Challan </label>

                                                                            <label class="radio-inline">
                                                                                <input type="radio" class="pay_by" name="fee_submit_type" id="optionsRadios4" value="receipt_book" /> Pay By Receipt Book </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="clearfix"></div>
                                                                    <div class="receipt_book" style="display:none;">
                                                                        <div class="clearfix"></div>
                                                                        <br />
                                                                        <div class="form-group">
                                                                            <label class="col-md-4 control-label">Pad of <span class="required">*</span></label>
                                                                            <div class="col-md-8">
                                                                                <select class="form-control submitted_fee_campus_id" name="submitted_fee_campus_id">
                                                                                    <option value="">SELECT CAMPUS</option>
                                                                                    <?php
                                                                                    foreach($campuses as $campus):
                                                                                        ?>
                                                                                        <option value="<?php echo $campus['campus_id'];?>" <?php if($campus['campus_id']==$this->session->userdata('user_campus_id')){echo 'selected';}?>><?php echo $campus['campus_name'];?></option>
                                                                                    <?php
                                                                                    endforeach;
                                                                                    ?>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="clearfix"></div>
                                                                        <br />
                                                                        <div class="form-group">
                                                                            <label class="col-md-4 control-label">Book No. <span class="required">*</span></label>
                                                                            <div class="col-md-8">
                                                                                <input type="number" min="1" class="form-control input-inline input-medium book_no" name="book_no" placeholder="Enter Receipt Book Number" value="">
                                                                                <span class="help-inline"></span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="clearfix"></div>
                                                                        <br />
                                                                        <div class="form-group">
                                                                            <label class="col-md-4 control-label">Receipt No. <span class="required">*</span></label>
                                                                            <div class="col-md-8">
                                                                                <input type="number" min="1" class="form-control input-inline input-medium receipt_no" name="receipt_no" placeholder="Enter Receipt Number" value="">
                                                                                <span class="help-inline"></span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="clearfix"></div>
                                                                    </div>
                                                                    <div class="clearfix"></div>
                                                                    <br />
                                                                    <div class="form-group challan">
                                                                        <label class="col-md-4 control-label">Challan Image <span class="required">*</span></label>
                                                                        <div class="col-md-8">
                                                                            <input class="scan_challan" type="file" name="scan_challan" value="" required />
                                                                            <span class="help-inline"></span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="clearfix"></div>
                                                                    <div class="form-group">
                                                                        <label class="col-md-4 control-label">Application Image</label>
                                                                        <div class="col-md-8">
                                                                            <input type="file" class="application" name="fine_application" value="" />
                                                                            <span class="help-inline"></span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="clearfix"></div>
                                                                    <div class="form-group">
                                                                        <label class="col-md-4 control-label">Paid Date <span class="required">*</span></label>
                                                                        <div class="col-md-8">
                                                                            <!-- CLASS date date-picker-->
                                                                            <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d');?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                                                <input type="text" name="paid_date" data-dead-line="<?php echo $payment['dead_line'];?>" class="form-control paid_date" value="<?php echo date('Y-m-d');?>" required readonly>
                                                                                <span class="input-group-btn">
																	<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
																	</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label class="col-md-4 control-label">Installment Amount <span class="required">*</span></label>
                                                                        <div class="col-md-8">
                                                                            <input type="number" min="1" class="form-control input-inline input-medium amount amount_<?php echo $i;?>" data-number="<?php echo $i;?>" name="amount" placeholder="Enter student installment amount" value="<?php echo $payment['amount'];?>" readonly required>
                                                                            <span class="help-inline"></span>
                                                                        </div>
                                                                        <div class="clearfix"></div>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label class="col-md-4 control-label">Previous Installment Amount <span class="required">*</span></label>
                                                                        <div class="col-md-8">
                                                                            <input type="number" min="0" max="<?php echo $payment['remaining_installment_amount'];?>" class="form-control input-inline input-medium remaining_installment_amount remaining_installment_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" data-value="<?php echo $payment['remaining_installment_amount'];?>" name="remaining_installment_amount" placeholder="Enter student previous installment amount" value="<?php echo $payment['remaining_installment_amount'];?>" readonly required>
                                                                            <?php
                                                                            if($payment['remaining_installment_amount']>0):
                                                                                ?>
                                                                                <label class="checkbox-inline">
                                                                                    <input type="checkbox" id="inlineCheckbox1" data-number="<?php echo $i;?>" class="remove_split_remaining_installment_amount remove_split_remaining_installment_amount_<?php echo $i;?>" name="split_remaining_installment_amount" value="1" /> Edit </label>
                                                                            <?php
                                                                            endif;
                                                                            ?>
                                                                            <span class="help-inline"></span>
                                                                        </div>
                                                                        <div class="clearfix"></div>
                                                                    </div>

                                                                    <div class="form-group remove_remaining_installment_amount_action_<?php echo $i;?>" style="display:none;">
                                                                        <label class="col-md-4 control-label"> Amount <span class="required">*</span></label>
                                                                        <div class="col-md-8">
                                                                            <input type="number" min="0" class="form-control input-inline input-medium remove_remaining_installment_amount remove_remaining_installment_amount_<?php echo $i;?>" name="remove_remaining_installment_amount" placeholder="" value="0" readonly required>
                                                                            <span class="help-inline"></span>
                                                                        </div>
                                                                        <div class="clearfix"></div>
                                                                    </div>
                                                                    <div class="form-group remove_remaining_installment_amount_action_<?php echo $i;?>" style="display:none;">
                                                                        <label class="col-md-4 control-label">Action <span class="required">*</span></label>
                                                                        <div class="col-md-8 radio-list">
                                                                            <label class="radio-inline">
                                                                                <input type="radio"  class="prev_installment_status" name="prev_installment_status" data-number="<?php echo $i;?>" id="optionsRadios3" value="shift" /> Shift </label>
                                                                            <label class="radio-inline">
                                                                                <input type="radio"  class="prev_installment_status" name="prev_installment_status" data-number="<?php echo $i;?>" id="optionsRadios3" value="new" /> New Installment </label>
                                                                        </div>
                                                                        <div class="clearfix"></div>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label class="col-md-4 control-label">Previous Fine Amount <span class="required">*</span></label>
                                                                        <div class="col-md-8">
                                                                            <input type="number" min="0" max="<?php echo $payment['extra_amount'];?>" class="form-control input-inline input-medium previous_extra_amount previous_extra_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" data-value="<?php echo $payment['extra_amount'];?>" name="extra_amount" placeholder="Enter student previous fine amount" value="<?php echo $payment['extra_amount'];?>" readonly required>
                                                                            <?php
                                                                            if($payment['extra_amount']>0 && @$myAccess[0]['remove_fine']==1):
                                                                                ?>
                                                                                <label class="checkbox-inline">
                                                                                    <input type="checkbox" id="inlineCheckbox1" data-number="<?php echo $i;?>" class="remove_split_previous_fine_amount remove_split_previous_fine_amount_<?php echo $i;?>" name="split_remaining_fine_amount" value="1" /> Edit </label>
                                                                            <?php
                                                                            endif;
                                                                            ?>
                                                                            <span class="help-inline"></span>
                                                                        </div>
                                                                        <div class="clearfix"></div>
                                                                    </div>
                                                                    <div class="form-group remove_split_previous_fine_amount_action_<?php echo $i;?>" style="display:none;">
                                                                        <label class="col-md-4 control-label"> Amount <span class="required">*</span></label>
                                                                        <div class="col-md-8">
                                                                            <input type="number" min="0" class="form-control input-inline input-medium remove_previous_fine_amount remove_previous_fine_amount_<?php echo $i;?>" name="remove_previous_fine_amount" placeholder="" value="0" readonly required>
                                                                            <span class="help-inline"></span>
                                                                        </div>
                                                                        <div class="clearfix"></div>
                                                                    </div>
                                                                    <div class="form-group remove_split_previous_fine_amount_action_<?php echo $i;?>" style="display:none;">
                                                                        <label class="col-md-4 control-label">Action <span class="required">*</span></label>
                                                                        <div class="col-md-8 radio-list">
                                                                            <label class="radio-inline">
                                                                                <input type="radio"  class="prev_fine_status" name="prev_fine_status" data-number="<?php echo $i;?>" id="optionsRadios2" value="remove" /> Remove </label>
                                                                            <label class="radio-inline">
                                                                                <input type="radio"  class="prev_fine_status" name="prev_fine_status" data-number="<?php echo $i;?>" id="optionsRadios3" value="shift" /> Shift </label>
                                                                            <label class="radio-inline">
                                                                                <input type="radio"  class="prev_fine_status" name="prev_fine_status" data-number="<?php echo $i;?>" id="optionsRadios4" value="new" /> New Installment </label>
                                                                        </div>
                                                                        <div class="clearfix"></div>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label class="col-md-4 control-label">Late Fee Fine Amount <span class="required">*</span></label>
                                                                        <div class="col-md-8">
                                                                            <input type="number" min="0" max="<?php echo $fee_fine;?>" class="form-control input-inline input-medium fine_amount fine_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" data-value="<?php echo $fee_fine;?>" name="fine_amount" placeholder="Enter student previous fine amount" value="<?php echo $fee_fine;?>" readonly required>
                                                                            <?php
                                                                            if($fee_fine>0 ):
                                                                                ?>
                                                                                <label class="checkbox-inline">
                                                                                    <input type="checkbox" id="inlineCheckbox1" data-number="<?php echo $i;?>" class="remove_split_fine_amount remove_split_fine_amount_<?php echo $i;?>" name="split_fine_amount" value="1" /> Edit </label>
                                                                            <?php
                                                                            endif;
                                                                            ?>
                                                                            <span class="help-inline"></span>
                                                                        </div>
                                                                        <div class="clearfix"></div>
                                                                    </div>

                                                                    <div class="form-group remove_split_fine_amount_action_<?php echo $i;?>" style="display:none;">
                                                                        <label class="col-md-4 control-label"> Amount <span class="required">*</span></label>
                                                                        <div class="col-md-8">
                                                                            <input type="number" min="0" class="form-control input-inline input-medium remove_fine_amount remove_fine_amount_<?php echo $i;?>" name="remove_fine_amount"  placeholder="" value="0" readonly required>
                                                                            <span class="help-inline"></span>
                                                                        </div>
                                                                        <div class="clearfix"></div>
                                                                    </div>
                                                                    <div class="form-group remove_split_fine_amount_action_<?php echo $i;?>" style="display:none;">
                                                                        <label class="col-md-4 control-label">Action <span class="required">*</span></label>
                                                                        <div class="col-md-8 radio-list">
                                                                            <label class="radio-inline">
                                                                                <input type="radio"  class="late_fee_fine_status" data-number="<?php echo $i;?>" name="late_fee_fine_status" id="optionsRadios2" value="remove" /> Remove </label>
                                                                            <label class="radio-inline">
                                                                                <input type="radio"  class="late_fee_fine_status" data-number="<?php echo $i;?>" name="late_fee_fine_status" id="optionsRadios3" value="shift" /> Shift </label>
                                                                            <label class="radio-inline">
                                                                                <input type="radio"  class="late_fee_fine_status" data-number="<?php echo $i;?>" name="late_fee_fine_status" id="optionsRadios4" value="new" /> New Installment </label>
                                                                        </div>
                                                                        <div class="clearfix"></div>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label class="col-md-4 control-label">Paid Amount <span class="required">*</span></label>
                                                                        <div class="col-md-8">
                                                                            <input type="number" min="1" class="form-control input-inline input-medium actual_amount actual_amount_<?php echo $i?>" name="actual_amount" placeholder="Enter student paid amount" value="<?php echo $payable_amount;?>" readonly required>
                                                                            <span class="help-inline"></span>
                                                                        </div>
                                                                        <div class="clearfix"></div>
                                                                    </div>
                                                                    <div class="form-group new_installment_date_section" style="display:none;">
                                                                        <label class="col-md-4 control-label">New Installment Paid Date <span class="required">*</span></label>
                                                                        <div class="col-md-8">
                                                                            <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d');?>" data-date-format="yyyy-mm-dd" data-date-start-date="+0d" data-date-end-date="+30d" data-date-viewmode="years">
                                                                                <input type="text" name="new_dead_line" class="form-control" value="<?php echo date('Y-m-d');?>" required readonly>
                                                                                <span class="input-group-btn">
																	<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
																	</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4"></div>
                                                                    <div class="col-md-8">
                                                                        <br />
                                                                        <input type="hidden" name="last_edit" value="<?php echo $this->session->userdata('name');?>" />
                                                                        <input type="hidden" name="fee_amount" value="<?php echo $payment['amount']+$payment['extra_amount'];?>" />
                                                                        <input type="hidden" name="id" value="<?php echo $payment['id'];?>" />
                                                                        <input type="hidden" name="dead_line" value="<?php echo $payment['dead_line'];?>" />
                                                                        <input type="hidden" name="payment_plan" value="<?php echo $payment['payment_plan'];?>" />
                                                                        <input type="hidden" name="college_fee" class="hidden_college_fee" value="0" />
                                                                        <button class="btn green">Pay Fee</button>
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

                                                <a href="<?php echo site_url().'/students/print_college_challan/'.$payment['id'];?>" target="_blank" class="btn blue college_fee_<?php echo $i;?>" style="display:none;"><i class="fa fa-print"></i> College Challan</a>
                                            <?php
                                            endif;
                                            ?>
                                            <?php

                                            if((@$payment['clear_by'] == "") || ($payment['fee_pay_through']=='pay_pro' && $payment['settlement_id'] == null) || ($payment['fee_pay_through']!='pay_pro' && $payment['closing_id'] == null && $payment['paid'] == 0)) 
                                            {
                                                if (@$myAccess[0]['student_payment_edit'] == 1 || $this->session->userdata('role') == 'Admin') {
                                                    echo '<a href="' . site_url() . '/students/edit_payment/' . $payment['id'] . '/' . ($i + 1) . '" class="btn yellow"><i class="fa fa-edit"></i> Edit</a>';
                                                }
                                            }
                                            if ($this->session->userdata('role') == 'Admin' && $payment['paid']==0) {
                                                echo '<a href="' . site_url() . '/students/delete_payment/' . $payment['id'] . '/' . $this->uri->segment(3) . '" onclick="return confirm(\'Are you sure you want to delete this Transaction?\')" class="btn red"><i class="fa fa-trash"></i> Delete</a>';
                                            }
                                            ?>
                                        <?php
                                        endif;
                                        ?>
									    <br />
										<?php
                                        echo 'Fee Add By : '.$payment['add_by'].'<br />';
                                        echo 'Fee Last Edit : '.$payment['last_edit'].'<br />';
                                        if($payment['paid']==1)
                                        {
                                            echo 'Fee Submitted By : '.@$payment['paid_by'].'<br />';
                                            echo 'Fee Cleared By : '.@$payment['clear_by'];
                                        }
                                        ?>
									
									</td>
                                </tr>
                                <?php
                                $i++;
                                endforeach;
                            ?>
                            <!-- DELETED PAYMENTS VIEW START -->
                            <?php
                                //$i=0;
    							$showed=false;
                                foreach($deleted_payments as $payment):
                            ?>
                                <tr class="odd gradeX danger deleted_installment" style="display:none;">

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

                                                if($difference>0) {
                                                    $totalfine += $difference*50;
                                                }
                                                ?>
                                                <br />
                                                Merged Challan # : <?php echo $merg['challan_no'];?>   <?php
                                                    echo $merg['payment_comment'].' (Dead Line : '.$merg['dead_line'].')';
                                                ?> <br />
                                                <span id="comment-text-<?php echo $merg['id']; ?>">
                                                    <?php echo htmlspecialchars($merg['payment_comment']); 
                                                    if( $merg['council_sequence_id'] && $merg['council_sequence_id'] != ''){
                                                        $sequence = $this->db->get_where('council_sequence','council_sequence_id = '.$merg['council_sequence_id'])->row_array();
                                                        echo ' '.$sequence['type_name'];
                                                    }?>
                                                </span>

                                                <select id="comment-input-<?php echo $merg['id']; ?>"
                                                        style="display:none; width:250px; margin-top:5px;">
                                                    <option value="">Select Exam No</option>
                                                    <?php foreach($all_exam_sequence as $seq): ?>
                                                        <option value="<?php echo $seq['id']; ?>"
                                                            <?php if($merg['exam_sequence_id'] == $seq['id']) echo 'selected'; ?>>
                                                            <?php echo $seq['first_year'].' - '.$seq['first_year_type'].' - '.getOrdinal($seq['class']).' '.$course_type; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            
                                            <select id="council-sequence-<?php echo $merg['id']; ?>"
                                                    style="display:none; width:250px; margin-top:5px;">
                                                <option value="">Select Council Sequence</option>
                                                <?php foreach($council_sequences as $seq): ?>
                                                    <option value="<?php echo $seq['council_sequence_id']; ?>"
                                                        <?php if($merg['council_sequence_id'] == $seq['council_sequence_id']) echo 'selected'; ?>>
                                                        <?php echo $seq['type_name']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <br>
                                            <div class="form-group payment_for_container" style="display:none;" id="payment-comment-<?php echo $merg['id']; ?>"> 
                                            <label class="control-label">please Add Reason and Undertaken<span class="required">*</span></label>
                                                    
                                                        <input type="text" class="form-control input-inline input-medium payment_for" id="payment-comment-text-<?php echo $merg['id']; ?>" name="payment_for" value="" /> 
                                                    <br>
                                                    <input type="file" name="image" id="council-image-<?php echo $merg['id']; ?>" class="form-control"/>
                                                    
                                                </div>
                                                <?php if((@$myAccess[0]['change_exam_no_in_payments']==1 && $merg['payment_comment'] != 'College Fee' && $merg['paid'] == 0) || ($this->session->userdata('role')=='Admin'&& $merg['payment_comment'] != 'College Fee') ):?>
                                        
                                            <button type="button" onclick="editComment(<?php echo $merg['id']; ?>)">Edit</button>
                                            <?php endif; ?>
                                            
                                            <button type="button"
                                                    onclick="saveComment(<?php echo $merg['id']; ?>)"
                                                    id="save-btn-<?php echo $merg['id']; ?>"
                                                    style="display:none;">Save</button>
                                                <strong>Merged Amount : <?php echo $merg['amount'];?> </strong>
                                         <?php
                                            }
                                        }

                                        else {
                                            $totalpayable=$payment['amount'];
                                            ?>
                                            Challan # : <?php echo $payment['challan_no'];?>   <?php echo $payment['payment_comment']; if( $payment['council_sequence_id'] && $payment['council_sequence_id'] != ''){
                                                        $sequence = $this->db->get_where('council_sequence','council_sequence_id = '.$payment['council_sequence_id'])->row_array();
                                                        echo ' '.$sequence['type_name'];
                                                    }?> <span id="comment-text-<?php echo $payment['id']; ?>">
                                                    <?php echo htmlspecialchars($payment['payment_comment']); ?>
                                                </span>

                                            <select id="comment-input-<?php echo $payment['id']; ?>"
                                                        style="display:none; width:250px; margin-top:5px;">
                                                    <option value="">Select Exam No</option>
                                                    <?php foreach($all_exam_sequence as $seq): ?>
                                                        <option value="<?php echo $seq['id']; ?>"
                                                            <?php if($payment['exam_sequence_id'] == $seq['id']) echo 'selected'; ?>>
                                                            <?php echo $seq['first_year'].' - '.$seq['first_year_type'].' - '.getOrdinal($seq['class']).' '.$course_type; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            
                                            <select id="council-sequence-<?php echo $payment['id']; ?>"
                                                    style="display:none; width:250px; margin-top:5px;">
                                                <option value="">Select Council Sequence</option>
                                                <?php foreach($council_sequences as $seq): ?>
                                                    <option value="<?php echo $seq['council_sequence_id']; ?>"
                                                        <?php if($payment['council_sequence_id'] == $seq['council_sequence_id']) echo 'selected'; ?>>
                                                        <?php echo $seq['type_name']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <br>
                                            <div class="form-group payment_for_container" style="display:none;" id="payment-comment-<?php echo $payment['id']; ?>"> 
                                                    <label class="control-label">please Add Reason and Undertaken<span class="required">*</span></label>
                                                        <input type="text" class="form-control input-inline input-medium payment_for" id="payment-comment-text-<?php echo $payment['id']; ?>" name="payment_for" value="" /> 
                                                    <br>
                                                    <input type="file" name="image" id="council-image-<?php echo $payment['id']; ?>" class="form-control"/>
                                                    
                                                </div>
                                            <?php if((@$myAccess[0]['change_exam_no_in_payments']==1 && $payment['payment_comment'] != 'College Fee' && $payment['paid'] == 0) || ($this->session->userdata('role')=='Admin' && $payment['payment_comment'] != 'College Fee') ):?>
                                            <button type="button" onclick="editComment(<?php echo $payment['id']; ?>)">Edit</button>
                                            <?php endif; ?>
                                            
                                            <button type="button"
                                                    onclick="saveComment(<?php echo $payment['id']; ?>)"
                                                    id="save-btn-<?php echo $payment['id']; ?>"
                                                    style="display:none;">Save</button>
											<br />
                                            <strong>Installment Amount : <?php echo $payment['amount'];?></strong>

                                        <?php }
                                        ?>
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
                                                Bank Statement ID : <?php echo $payment['statement_id'];?>
                                                <br />
                                                Bank Statement no. : <?php echo @$this->db->get_where('bank_reconciliation_statement',array('id'=>$payment['statement_id']))->row()->statement_no;?>
                                                <br />
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
                                                if($payment['online_scan_challan']!='')
                                                {
                                                    if($payment['merged_challan']!='')
                                                    {
                                                        $this->db->order_by('id','desc');
                                                        $my_challan = $this->db->get_where('payments',array('merged_challan'=>$payment['merged_challan']))->result_array();
                                                        echo '<a href="'.str_replace($bucket_address,$cloudfront_address,$my_challan[0]['online_scan_challan']).'" target="_blank" class="btn purple pull-left">See Challan</a>';
                                                    }
                                                    else
                                                    {
                                                        echo '<a href="'.str_replace($bucket_address,$cloudfront_address,$payment['online_scan_challan']).'" target="_blank" class="btn purple pull-left">See Challan</a>';
                                                    }
                                                }
                                                else
                                                {
                                                    echo '<a href="'.base_url().'uploads/'.$payment['scan_challan'].'" target="_blank" class="btn purple pull-left">See Challan</a> <br />';
                                                }
                                            }
                                            
                                            if($payment['fee_pay_through']=='college' && $payment['fee_submit_type']=='computer_challan')
                                            {
                                                echo '<a href="'.site_url().'/students/print_college_challan/'.$payment['id'].'" target="_blank" class="btn blue college_fee_'.$i.'"><i class="fa fa-print"></i> See Challan</a> <br />';
                                            }
                                            
                                            if($payment['fee_pay_through']=='pay_pro' && $payment['paid']==1)
                                            {
                                                $this->db->select('*');
                                                $this->db->from('students_payments');
                                                $this->db->where('transaction_status','PAID');
                                                $this->db->like('challan_ids',$payment['paid_challans']);
                                                $link = @$this->db->get()->result_array();
                                                echo '<a href="'.$link[0]['bill_url'].'" target="_blank" class="btn grey"><i class="fa fa-print"></i> See Challan</a> <br />';
                                            }
                                            
                                            ?>
                                            <?php
                                            if($payment['fine_application']=='' && $payment['paid']==0)
                                            {

                                            }
                                            else if($payment['fine_application']!='' && $payment['paid']==1)
                                            {
                                                if($payment['online_fine_application']!='')
                                                {
                                                    echo '<a href="'.str_replace($bucket_address,$cloudfront_address,$payment['online_fine_application']).'" target="_blank" class="btn purple pull-left">See Application</a> <br />';
                                                }
                                                else
                                                {
                                                    echo '<a href="'.base_url().'uploads/'.$payment['fine_application'].'" target="_blank" class="btn purple pull-left">See Application</a> <br />';
                                                }
                                            }
                                            else
                                            {

                                            }

                                            if(($payment['fee_pay_through']=='bank' && @$payment['clear_by'] == "") || ($payment['fee_pay_through']=='pay_pro' && $payment['settlement_id'] == null) || ($payment['fee_pay_through']!='pay_pro' && $payment['fee_pay_through']!='bank' && $payment['closing_id'] == null))
                                            {

                                            }
                                            else {
                                                if(@$myAccess[0]['student_issue_refund']==1 || $this->session->userdata('role')=='Admin'):
                                                    //echo '<a onclick="open_reversal_modal('.$i.')" class="btn red"><i class="fa fa-recycle"></i> Want Reversal?</a>';
                                                    $reversal_checker = $this->db->get_where('payments_reversal_requests',array('payment_id'=>$payment['id']))->result_array();
                                                    if(count($reversal_checker)>0)
                                                    {
                                                        if($reversal_checker[0]['status']==0 && $reversal_checker[0]['approve_status']==0)
                                                        {
                                                            echo '<button type="button" class="btn red">Reversal Under Review <i class="fa fa-spinner fa-spin"></i></button>';
                                                        }
                                                        elseif($reversal_checker[0]['status']==1 && $reversal_checker[0]['approve_status']==0)
                                                        {
                                                            echo '<button type="button" class="btn red">Reversal Rejected <i class="fa fa-ban"></i></button>';   
                                                        }
                                                        elseif($reversal_checker[0]['status']==1 && $reversal_checker[0]['approve_status']==1 && $reversal_checker[0]['done']==0)
                                                        {
                                                            echo '<button onclick="fee_reversal_proof_modal('.$i.')" type="button" class="btn green">Reversal Approved <i class="fa fa-spinner fa-spin"></i></button>';   
                                                        }
                                                        elseif($reversal_checker[0]['status']==1 && $reversal_checker[0]['approve_status']==1 && $reversal_checker[0]['done']==1)
                                                        {
                                                            if($reversal_checker[0]['online_reversal_application']==''):
                                                                $reversal_application=base_url().'uploads/'.$reversal_checker[0]['reversal_application'];
                                                            else:
                                                                $reversal_application=str_replace($bucket_address,$cloudfront_address,$reversal_checker[0]['online_reversal_application']);
                                                            endif;
                                                            if($reversal_checker[0]['online_proof_image']==''):
                                                                $proof_image=base_url().'uploads/'.$reversal_checker[0]['proof_image'];
                                                            else:
                                                                $proof_image=str_replace($bucket_address,$cloudfront_address,$reversal_checker[0]['online_proof_image']);
                                                            endif;
                                                            echo '<br /><br /><br /><span class="blink_me" style="font-weight:bold;font-size:18px;color:#F00;">PAYMENT REFUNDED</span>';

                                                            echo '</br ><span class="alert-danger" style="font-size:14px; font-weight:bold;">Reversal Amount: '.$reversal_checker[0]['reversal_amount'].'<br />Reversal Reason: '.$reversal_checker[0]['reversal_reason'].'<br />Reversal Created By: '.$reversal_checker[0]['created_by'].'<br />Reversal Paid By: '.$reversal_checker[0]['paid_by'].'<br />Reversal Application: <a href="'.$reversal_application.'" target="_blank">Application</a><br />Reversal Proof: <a href="'.$proof_image.'" target="_blank">Proof</a></span>';                                                            
                                                        }
                                                    }
                                                    else
                                                    {
                                                        echo '<a onclick="fee_reversal_modal('.$i.')" class="btn red"><i class="fa fa-recycle"></i> Want Reversal?</a>';
                                                    }
                                                endif;
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
                                    
									</td>
                                </tr>
                                <?php
                                $i++;
                            endforeach;
                            ?>
                            <!-- DELETED PAYMENTS VIEW END -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- END EXAMPLE TABLE PORTLET-->
            </div>
        </div>
        <!-- END PAGE CONTENT-->
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
                        <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/students/add_extra_fee/<?php echo $this->uri->segment(3);?>" enctype="multipart/form-data">
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="col-md-4 control-label">Fee Type <span class="required">*</span></label>
                                            <div class="col-md-8">
                                                <select class="form-control input-medium fee_type" id="my_fee_type" name="fee_type" required>
                                                    <option value="">SELECT FEE TYPE</option>
                                                    <?php if ($myAccess[0]['extra_fee_access'] == "1"):?><option value="College Fee">College Fee</option><?php endif;?>
                                                    <option value="Extra Fee">Extra Fee</option>
                                                    <?php foreach ($payment_rules as $k=>$payment_rule): ?>
                                                        <option value="<?php echo $k;?>"><?php echo $payment_rule['name'];?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="col-md-4 control-label">Extra Fee Amount <span class="required">*</span></label>
                                            <div class="col-md-8">
                                                <input type="number" min="0" class="form-control input-inline input-medium" name="extra_fee" id="extra_fee_amount" value="" placeholder="Enter Extra Fee Amount" required/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="col-md-4 control-label">Dead Line <span class="required">*</span></label>
                                            <div class="col-md-8">
                                                <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d');?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
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
                                    <div class="clearfix"></div>
                                    <div class="col-md-4 extra_fee" style="display:none;">
                                        <div class="form-group">
                                            <label class="col-md-4 control-label">Fee For <span class="required">*</span></label>
                                            <div class="col-md-8">
                                                <select class="form-control input-medium fee_for" name="fee_for" required>
                                                    <option value="">SELECT FEE FOR</option>
                                                    <option value="Books">Books</option>
                                                    <option value="Notes">Notes</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group payment_for_container" style="display:none;">
                                            <label class="col-md-4 control-label">Payment Comment <span class="required">*</span></label>
                                            <div class="col-md-8">
                                                <input type="text" class="form-control input-inline input-medium payment_for" name="payment_for" value="" />
                                                    <br>
                                                    <input type="file" name="image" class="form-control"/>
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
                                                <input type="text" class="form-control input-inline input-medium exam_no" name="exam_no" value="" required />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <button type="submit" class="btn green">Add Extra Fee</button>
                                        <button onclick="location.href = '<?php echo site_url()?>/students/all_students'" type="button" class="btn default">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- END SAMPLE FORM PORTLET-->
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 ">
                <!-- BEGIN SAMPLE FORM PORTLET-->
                <div class="portlet box green ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-plus"></i> Add Council Fee
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/students/add_council_fee/<?php echo $this->uri->segment(3);?>" enctype="multipart/form-data">
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="col-md-4 control-label">Council Fee Type <span class="required">*</span></label>
                                            <div class="col-md-8">
                                                <select name="exam_sequence" class="form-control input-medium fee_for">
                                                    <option value="">Select Exam No</option>
                                                    <?php foreach($all_exam_sequence as $seq): ?>
                                                            <option value="<?php echo $seq['id']; ?>">
                                                                <?php echo $seq['first_year'].' - '.$seq['first_year_type'].' - '.getOrdinal($seq['class']).' '.$course_type; ?>
                                                            </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <button type="submit" class="btn green">Add Council Fee</button>
                                        <button onclick="location.href = '<?php echo site_url()?>/students/all_students'" type="button" class="btn default">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- END SAMPLE FORM PORTLET-->
            </div>
        </div>
        
        
        <?php
            if($fee_not_created>0):
        ?>
        
        <div class="row">
            <div class="col-md-12 ">
                <!-- BEGIN SAMPLE FORM PORTLET-->
                <div class="portlet box green ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-plus"></i> Add Student Fee Installments
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/students/add_fee_installments/<?php echo $this->uri->segment(3);?>" enctype="multipart/form-data">
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-md-4 control-label">Installment Day Of Month <span class="required">*</span></label>
                                            <div class="col-md-8">
                                                <input type="number" min="1" max="30" class="form-control input-inline input-medium installment_day" name="installment_day" value="" placeholder="Enter Installment Day Of Month" required/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                    /*--- GET LAST DATE OF CLASS ---*/
                                    $this->db->select('*');
                                    $this->db->from('classes');
                                    $this->db->join('students','students.class_id=classes.class_id','inner');
                                    $this->db->where('students.student_id',$this->uri->segment(3));
                                    $class_data = $this->db->get()->result_array();
                                    
                                    $last_date = $class_data[0]['maximum_fee_last_date'];
                                    
                                    if($last_date>date('Y-m-d'))
                                    {
                                        $sample_date = date('Y-m-d');
                                        //GET DAYS DIFFERENCE
                                        $start_date = strtotime($last_date);
                                        $end_date = strtotime(date('Y-m-d'));
                                        $days = '+'.($start_date - $end_date)/60/60/24;
                                    }
                                    else
                                    {
                                        $sample_date = $last_date;
                                    }
                                ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-md-4 control-label">Installment Start Date <span class="required">*</span></label>
                                            <div class="col-md-8">
                                                <?php
                                                    if(date('Y-m-d')>@$sample_date):
                                                ?>
                                                <input type="text" class="form-control input-inline input-medium installment_start_month" name="installment_start_month" value="<?php echo $sample_date;?>" placeholder="" required readonly/>
                                                <?php
                                                    else:
                                                ?>
                                                <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d');?>" data-date-format="yyyy-mm-dd" data-date-start-date="0d" <?php if($this->session->userdata('role')!='Admin'):?>data-date-end-date="+60d"<?php endif;?> data-date-viewmode="years">
                                                    <input type="text" name="installment_start_month" class="form-control installment_start_month" value="<?php echo date('Y-m-d');?>" readonly>
                                                    <span class="input-group-btn">
                                                    <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                    </span>
                                                </div>
                                                <?php
                                                    endif;
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-md-4 control-label">Installment End Date <span class="required">*</span></label>
                                            <div class="col-md-8">
                                                <?php
                                                    if(date('Y-m-d')>@$sample_date):
                                                ?>
                                                <input type="text" class="form-control input-inline input-medium installment_end_month" name="installment_end_month" value="<?php echo $sample_date;?>" placeholder="" required readonly/>
                                                <?php
                                                    else:
                                                ?>
                                                <div class="input-group input-medium date date-picker disable" data-date="<?php echo $sample_date;?>" data-date-format="yyyy-mm-dd" data-date-start-date="0d" <?php if($this->session->userdata('role')!='Admin'):?>data-date-end-date="<?php echo $days;?>d"<?php endif;?> data-date-viewmode="years">
                                                    <input type="text" name="installment_end_month" class="form-control installment_end_month" value="<?php echo $sample_date;?>" readonly>
                                                    <span class="input-group-btn">
                                                    <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                    </span>
                                                </div>
                                                <?php
                                                    endif;
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 fee_data">
                                        
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <input type="hidden" name="fee_not_created" class="fee_not_created" value="<?php echo $fee_not_created;?>" />
                                        <button type="submit" class="btn green">Add Installments</button>
                                        <button onclick="location.href = '<?php echo site_url()?>/students/all_students'" type="button" class="btn default">Cancel</button>
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
<div id="payfee" class="modal fade" tabindex="-1" data-width="760">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Fee Submission</h4>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-4" style="word-wrap:break-word;">
                <strong>Challan #</strong> : <label id="chalans"></label>
            </div>
            <div class="col-md-4" style="word-wrap:break-word;">
                <strong>Installment Amount</strong> : <label id="amounts"></label>
            </div>
            <div class="col-md-4">
                <strong>Dead Line</strong> : <label id="deadlines"></label>
            </div>
            <div class="col-md-4">
                <strong>Fee Type</strong> : <label id="type"></label>
            </div>
            <div class="col-md-4">
                <strong>Previous Installment Amount</strong> : <label id="remainings"> </label>
            </div>
            <div class="col-md-4">
                <strong>Previous Fine Amount</strong> : <label id="extraamount"></label>
            </div>
            <div class="col-md-4">
                <strong>Late Fee Amount</strong> : <label id="fine"></label>
            </div>
            <div class="col-md-4">
                <strong>Late Fee Days</strong> : <label id="latedays"> </label>
            </div>
            <div class="col-md-4">
                <strong>Payable Amount</strong> : <label id="amounttotal"></label>
            </div>

            <div class="clearfix"></div>
            <br />
        </div>
        <div class="row">
            <div class="col-md-12">
                <form action="<?php echo site_url().'/students/paid_payment_action/'.$this->uri->segment(3);?>" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Fee Pay Through <span class="required">*</span></label>
                        <div class="col-md-8 radio-list">
                            <?php if ($myAccess[0]['fee_by_bank'] || $this->session->userdata('role')=='Admin'): ?>
                            <label class="radio-inline">
                                <input type="radio" class="submit_in fee_pay_through" name="fee_pay_through" id="optionsRadios5" value="bank" checked /> Bank </label>
                            <?php endif; ?>
                            <?php if ($myAccess[0]['fee_by_cash'] || $this->session->userdata('role')=='Admin'): ?>
                            <label class="radio-inline">
                                <input type="radio"  class="submit_in fee_pay_through" name="fee_pay_through" id="optionsRadios4" value="college" /> College </label>
                            <?php endif; ?>
                            <?php if ($myAccess[0]['fee_by_paypro'] || $this->session->userdata('role')=='Admin'): ?>
                            <label class="radio-inline">
                                <input type="radio"  class="submit_in fee_pay_through" name="fee_pay_through" id="optionsRadios6" value="pay_pro" /> PayPro </label>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="clearfix"></div>
                    <br />
                    <div class="form-group">
                        <label class="col-md-4 control-label">Paid at Campus <span class="required">*</span></label>
                        <div class="col-md-8">
                            <select class="form-control submitted_fee_campus_id" name="submitted_fee_campus_id" required>
                                <option value="">SELECT CAMPUS</option>
                                <?php
                                foreach($campuses as $campus):
                                    ?>
                                    <option value="<?php echo $campus['campus_id'];?>" <?php if($campus['campus_id']==$this->session->userdata('user_campus_id')){echo 'selected';}?>><?php echo $campus['campus_name'];?></option>
                                <?php
                                endforeach;
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <br />

					<div class="form-group">
                        <label class="col-md-4 control-label">Paid Date <span class="required">*</span></label>
                        <div class="col-md-8">
                            <!-- CLASS date date-picker-->
                            <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d');?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                <input type="text" name="paid_date"  id="paid_date" class="form-control paid_date" value="<?php echo date('Y-m-d');?>" required readonly>
                                <span class="input-group-btn">
                                    <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="clearfix"></div>
                    <div class="form-group college" style="display:none;">
                        <label class="col-md-4 control-label">Fee Submit Type <span class="required">*</span></label>
                        <div class="col-md-8 radio-list">
                            <label class="radio-inline">
                                <input type="radio" class="pay_by" name="fee_submit_type" id="optionsRadios5" value="computer_challan" checked /> Pay By Computer Challan </label>

                            <label class="radio-inline">
                                <input type="radio" class="pay_by" name="fee_submit_type" id="optionsRadios4" value="receipt_book" /> Pay By Receipt Book </label>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="receipt_book" style="disoplay:none;">

                        <div class="clearfix"></div>
                        <br />
                        <div class="form-group">
                            <label class="col-md-4 control-label">Book No. <span class="required">*</span></label>
                            <div class="col-md-8">
                                <input type="number" min="1" class="form-control input-inline input-medium book_no" name="book_no" placeholder="Enter Receipt Book Number" value="">
                                <span class="help-inline"></span>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <br />
                        <div class="form-group">
                            <label class="col-md-4 control-label">Receipt No. <span class="required">*</span></label>
                            <div class="col-md-8">
                                <input type="number" min="1" class="form-control input-inline input-medium receipt_no" name="receipt_no" placeholder="Enter Receipt Number" value="">
                                <span class="help-inline"></span>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="clearfix"></div>
                    <br />
                    <div class="form-group challan">
                        <label class="col-md-4 control-label">Challan Image <span class="required">*</span></label>
                        <div class="col-md-8">
                            <input class="scan_challan" type="file" name="scan_challan" value="" required />
                            <span class="help-inline"></span>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Application Image</label>
                        <div class="col-md-8">
                            <input type="file" class="application" name="fine_application" value="" />
                            <span class="help-inline"></span>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    
                    <div class="form-group">
                        <label class="col-md-4 control-label">Installment Amount <span class="required">*</span></label>
                        <div class="col-md-8">
                            <input type="number" min="1" id="instamount" class="form-control input-inline input-medium amount amount_<?php echo $i;?>" data-number="<?php echo $i;?>" name="amount" placeholder="Enter student installment amount" value="<?php echo $payment['amount'];?>" readonly required>
                            <span class="help-inline"></span>
                        </div>
                        <div class="clearfix"></div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-4 control-label">Previous Installment Amount <span class="required">*</span></label>
                        <div class="col-md-8">
                            <input type="number" min="0" max="" class="form-control input-inline input-medium remaining_installment_amount remaining_installment_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" data-value="" name="remaining_installment_amount" id="remaining_installment_amount" placeholder="Enter student previous installment amount" value="" readonly required>

                            <label class="checkbox-inline hidden" id="instabox"">
                            <input type="checkbox" id="inlineCheckbox1" data-number="<?php echo $i;?>" class="remove_split_remaining_installment_amount remove_split_remaining_installment_amount_<?php echo $i;?>" name="split_remaining_installment_amount" value="1" /> Edit </label>

                            <span class="help-inline"></span>
                        </div>
                        <div class="clearfix"></div>
                    </div>


                    <div class="form-group remove_remaining_installment_amount_action_<?php echo $i;?>" style="display:none;">
                        <label class="col-md-4 control-label"> Amount <span class="required">*</span></label>
                        <div class="col-md-8">
                            <input type="number" min="0" class="form-control input-inline input-medium remove_remaining_installment_amount remove_remaining_installment_amount_<?php echo $i;?>" name="remove_remaining_installment_amount" placeholder="" value="0" readonly required>
                            <span class="help-inline"></span>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="form-group remove_remaining_installment_amount_action_<?php echo $i;?>" style="display:none;">
                        <label class="col-md-4 control-label">Action <span class="required">*</span></label>
                        <div class="col-md-8 radio-list">
                            <label class="radio-inline">
                                <input type="radio"  class="prev_installment_status" name="prev_installment_status" data-number="<?php echo $i;?>" id="optionsRadios3" value="shift" /> Shift </label>
                            <label class="radio-inline">
                                <input type="radio"  class="prev_installment_status" name="prev_installment_status" data-number="<?php echo $i;?>" id="optionsRadios3" value="new" /> New Installment </label>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Previous Fine Amount <span class="required">*</span></label>
                        <div class="col-md-8">
                            <input type="number" min="0" id="previous_extra_amounts" max="<?php echo $payment['extra_amount'];?>"
                                   class="form-control input-inline input-medium previous_extra_amount previous_extra_amount_<?php echo $i;?>"
                                   data-number="<?php echo $i;?>" data-value="<?php echo $payment['extra_amount'];?>"
                                   name="extra_amount" placeholder="Enter student previous fine amount"
                                   value="" readonly required>

                            <label class="checkbox-inline hidden" id="prevbox"">
                            <input type="checkbox" id="inlineCheckbox1" data-number="<?php echo $i;?>" class="remove_split_previous_fine_amount " name="split_remaining_fine_amount" value="1" /> Edit </label>

                            <span class="help-inline"></span>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="form-group remove_split_previous_fine_amount_action" style="display:none;">
                        <label class="col-md-4 control-label"> Amount <span class="required">*</span></label>
                        <div class="col-md-8">
                            <input type="number" min="0" class="form-control input-inline input-medium remove_previous_fine_amount remove_previous_fine_amount_<?php echo $i;?>" name="remove_previous_fine_amount" placeholder="" value="0" readonly required>
                            <span class="help-inline"></span>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="form-group remove_split_previous_fine_amount_action" style="display:none;">
                        <label class="col-md-4 control-label">Action <span class="required">*</span></label>
                        <div class="col-md-8 radio-list">
                            <label class="radio-inline">
                                <input type="radio"  class="prev_fine_status" name="prev_fine_status" data-number="<?php echo $i;?>" id="optionsRadios2" value="remove" /> Remove </label>
                            <label class="radio-inline">
                                <input type="radio"  class="prev_fine_status" name="prev_fine_status" data-number="<?php echo $i;?>" id="optionsRadios3" value="shift" /> Shift </label>
                            <label class="radio-inline">
                                <input type="radio"  class="prev_fine_status" name="prev_fine_status" data-number="<?php echo $i;?>" id="optionsRadios4" value="new" /> New Installment </label>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Late Fee Fine Amount <span class="required">*</span></label>
                        <div class="col-md-8">
                            <input type="number" min="0" max="" class="form-control input-inline input-medium fine_amount fine_amount"
                                   name="fine_amount" id="fine_amount" placeholder="Enter student previous fine amount"  readonly required>
					<?php
                            if(@$myAccess[0]['fine_remove']== 1 || $this->session->userdata('role') == "Admin"):
                                                                                ?>
                            <label class="checkbox-inline hidden" id="finebox">
                                <input type="checkbox" id="inlineCheckbox1" data-number="<?php echo $i;?>" class="remove_split_fine_amounts" name="split_fine_amount"  value="1" /> Edit </label>
					<?php endif; ?>
                            <span class="help-inline"></span>
                        </div>
                        <div class="clearfix"></div>
                    </div>

                    <div class="form-group remove_split_fine_amount_action" style="display:none;">
                        <label class="col-md-4 control-label"> Amount <span class="required">*</span></label>
                        <div class="col-md-8">
                            <input type="number" min="0" class="form-control input-inline input-medium remove_fine_amount remove_fine_amount_<?php echo $i;?>" name="remove_fine_amount" id="remove_fine_amount" placeholder="" value="0" readonly required>
                            <span class="help-inline"></span>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="form-group remove_split_fine_amount_action" style="display:none;">
                        <label class="col-md-4 control-label">Action <span class="required">*</span></label>
                        <div class="col-md-8 radio-list">
                            <label class="radio-inline">
                                <input type="radio"  class="late_fee_fine_status" data-number="<?php echo $i;?>" name="late_fee_fine_status" id="optionsRadios2" value="remove" /> Remove </label>
                            <label class="radio-inline">
                                <input type="radio"  class="late_fee_fine_status" data-number="<?php echo $i;?>" name="late_fee_fine_status" id="optionsRadios3" value="shift" /> Shift </label>
                            <label class="radio-inline">
                                <input type="radio"  class="late_fee_fine_status" data-number="<?php echo $i;?>" name="late_fee_fine_status" id="optionsRadios4" value="new" /> New Installment </label>
                        </div>
                        <div class="clearfix"></div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-4 control-label">Discount Amount <span class="required">*</span></label>
                        <div class="col-md-8">
                            <input type="number" min="1" class="form-control input-inline input-medium" name="discount" id="discount" required>
                            <span class="help-inline"></span>
                        </div>
                        <div class="clearfix"></div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-4 control-label">Paid Amount <span class="required">*</span></label>
                        <div class="col-md-8">
                            <input type="number" min="1" class="form-control input-inline input-medium" name="actual_amount" id="actual_amount" placeholder="Enter student paid amount" value="" readonly required>
                            <span class="help-inline"></span>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="form-group new_installment_date_section" style="display:none;">
                        <label class="col-md-4 control-label">New Installment Paid Date <span class="required">*</span></label>
                        <div class="col-md-8">
                            <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d');?>" data-date-format="yyyy-mm-dd" data-date-start-date="+0d" data-date-end-date="+30d" data-date-viewmode="years">
                                <input type="text" name="new_dead_line" class="form-control" value="<?php echo date('Y-m-d');?>" required readonly>
                                <span class="input-group-btn">
                                    <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="bank">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Select Bank <span class="required">*</span></label>
                            <div class="col-md-8">
                                <select class="form-control bank_details" id="select_bank" name="bank_details" required>
                                    <option value="">SELECT BANK</option>
                                   <?php
                                    $count = count($account_numbers);
                                    for($a=0;$a<$count;$a++):
                                        ?>
                                        <option value="<?php echo $account_numbers[$a]['account_name'].'';?>"><?php echo $account_numbers[$a]['account_name'].'';?></option>
                                    <?php
                                    endfor;
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <br />
                        <div class="form-group">
                            <label class="col-md-4 control-label">Bank Challan / TID No.</label>
                            <div class="col-md-8">
                                <div class="text-danger" id="error_verify" >Soch Samajh ker Bank or Tid # likhein Bhaari jurmana ada karna parr sakta hai</div>
                                <input type="text" class="form-control input-inline input-medium" id="tid_no" minlength="5" name="tid_no" placeholder="Enter TID Number" value="" >
                                <button name="verify" class="btn btn-primary" type="button"  id="verify_now">Verify Now</button>
                                <div class="text-danger" id="error_verify" ></div>
                                <span class="help-inline"></span>

                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div id="council_fee_passed" style="display:none;">
                        <div class="alert alert-warning" style="display:block; margin-top:25px;">
                            <strong>Note:</strong><br>
                            Council fee ki last date <strong id="council_fee_last_date"></strong> hai.
                            Agar aap aaj ke din payment karna chahtay hain, to please council fee ko aaj ki fee ke hisaab se regenerate karein.
                    
                            <br><br>
                    
                            <button type="button" class="btn btn-warning btn-sm" id="regenerate_council_fee_btn">
                                Regenerate Council Fee
                            </button>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <br />
                        <input type="hidden" name="last_edit" value="<?php echo $this->session->userdata('name');?>" />
                        <input type="hidden" name="fee_amount" id="fee_amount" value="" />
                        <input type="hidden" name="challans" id="challans" value="" />
                        <input type="hidden" name="fee_ids" id="fee_ids" value="" />
                        <input type="hidden" name="dead_line" value="<?php echo $payment['dead_line'];?>" />
                        <input type="hidden" name="payment_plan" value="<?php echo $payment['payment_plan'];?>" />
                        <input type="hidden" name="college_fee" class="hidden_college_fee" value="0" />
                        <div id="council_fee_deadline_loader" style="display:none; margin-top:10px;">

    <i class="fa fa-spinner fa-spin"></i> Council fee deadline fetch ho rahi hai...

</div>
                        <button class="btn green"  id = "payfeebtn" >Pay Fee</button>
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

<div id="applydiscount" class="modal fade" tabindex="-1" data-width="760">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Payment PLan</h4>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <?php
                    if($fee_not_created>0):
                ?>
                <div class="alert alert-danger">
                    Kindly Create All Installments First then Apply Discount. <strong>Fee Not Created</strong> Must be 0.
                </div>
                <?php
                    else:
                ?>
                <form action="<?php echo site_url();?>/students/update_edit_payment/payment_discount/<?php echo $this->uri->segment(3);?>" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="col-md-6 control-label"> Remaining Fee</label>
                        <div class="col-md-6">
                            <input class="form-control input-inline " type="text" value="<?php echo $remaining_fee ?>" name="remainfee" id="remainfee" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-6 control-label"> Enter amount for Discount</label>
                        <div class="col-md-6">
                            <input class="form-control input-inline " type="number" pattern="\d*" maxlength="5" max="<?php echo $remaining_fee ?>" name="apldiscount" id="apldiscount" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Reason <span class="required">*</span></label>
                        <div class="col-md-9">
                            <textarea name="reason_disc" class="form-control" rows="5" required></textarea>
                            <span class="help-inline"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Undertaken Image</label>
                        <div class="col-md-8">
                            <input type="file" name="image" class="form-control" required />
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-9">
                                <button type="submit" class="btn red rmv">Apply Discount</button>
                            </div>
                        </div>
                    </div>
                </form>
                <?php
                    endif;
                ?>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default">Close</button>
    </div>
</div>

<!--<div id="fee_reversal" class="modal fade" tabindex="-1" data-width="760">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Fee Reversal Request</h4>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <form action="<?php echo site_url();?>/students/payment_reversal/<?php echo $this->uri->segment(3);?>" method="post">
                    <div class="form-group">
                        <label class="col-md-4 control-label"> Total Paid Fee</label>
                        <div class="col-md-8">
                            <input class="form-control input-inline " type="text" value="<?php echo ($paid_fee[0]['paid_fee']-$discountfee) ?>" name="reversible_amount" id="reversible_amount" readonly>
                        </div>
                    </div>
                    <div class="form-group" id="rev_details" style="display: none;">
                        <label class="col-md-4 control-label"> Reverse Details</label>
                        <div class="col-md-8">
                            <label class="control-label" id="rev_challans"></label><br />
                            <label class="control-label" id="rev_challans_amount"></label><br />
                            <label class="control-label" id="rev_challans_paid_date"></label><br />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label"> Enter amount for Reversal</label>
                        <div class="col-md-8">
                            <input class="form-control input-inline " type="text" name="rev_amount" id="rev_amount" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Reason <span class="required">*</span></label>
                        <div class="col-md-8">
                            <textarea name="reason" class="form-control" rows="5" required></textarea>
                            <span class="help-inline"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Undertaken Image</label>
                        <div class="col-md-8">
                            <input type="file" name="image" class="form-control" required />
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-9">
                                <input type="hidden" name="type" id="rev_type" class="form-control" required />
                                <button type="submit" class="btn red">Apply Reversal</button>
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
                            -->

<div id="fee_reversal_modal" class="modal fade" tabindex="-1" data-width="760">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Fee Reversal Request</h4>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <form action="<?php echo site_url();?>/students/payment_reversal_process/<?php echo $this->uri->segment(3);?>" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="col-md-4 control-label"> Installment Amount</label>
                        <div class="col-md-8">
                            <input class="form-control input-inline " type="text" value="" name="installment_amount" id="installment_amount" readonly>
                            <span class="help-inline"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label"> Enter amount for Reversal</label>
                        <div class="col-md-8">
                            <input class="form-control input-inline " min="1" max="" type="number" name="reversal_amount" id="rev_amount" required>
                            <span class="help-inline"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Reason <span class="required">*</span></label>
                        <div class="col-md-8">
                            <textarea name="reversal_reason" class="form-control" rows="5" required></textarea>
                            <span class="help-inline"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Undertaken Image</label>
                        <div class="col-md-8">
                            <input type="file" name="reversal_application" class="form-control" required />
                            <span class="help-inline"></span>
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-9">
                                <input type="hidden" name="payment_id" value="" id="payment_id" class="form-control" required />
                                <input type="hidden" name="student_id" value="<?php echo $this->uri->segment(3);?>" class="form-control" required />
                                <button type="submit" class="btn red">Apply Reversal</button>
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

<div id="fee_reversal_process_modal" class="modal fade" tabindex="-1" data-width="760">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Approved Fee Reversal Proof</h4>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <form action="<?php echo site_url();?>/students/payment_reversal_process_complete/<?php echo $this->uri->segment(3);?>" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Proof Image</label>
                        <div class="col-md-8">
                            <input type="file" name="proof" class="form-control" required />
                            <span class="help-inline"></span>
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-9">
                                <input type="hidden" name="payment_id" value="" class="payment_id" class="form-control" required />
                                <button type="submit" class="btn red">Submit</button>
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

    window.onscroll = function() {myFunction()};
    var header = document.getElementById("myHeader");
    var sticky = header.offsetTop;
    
    function getselected() {
        $('#council_fee_passed').hide();

        var selected = [];
    
        $('div#checkboxes input[type=checkbox]').each(function () {
            if ($(this).is(":checked")) {
                selected.push($(this).attr('value'));
            }
        });
    
        if (selected.length === 0) {
            alert('Please select at least one fee.');
            return false;
        }
    
        let loans = <?php echo json_encode($payments) ?>;
    
        var hasExpiredConsultationFee = false;
        var consultationFeeDeadline = '';
        var consultationFeeID = '';
    
        var chalans = '';
        var amounts = 0;
        var deadlines = '';
        var type = '';
        var remainings = 0;
        var extraamount = 0;
        var fine = 0;
        var amounttotal = 0;
        var amountstotal = 0;
        var installment = 0;
        var discount = 0;
        var latedays = '';
        var feeids = '';
        var disc = 0;
    
        <?php
            $student_plan = $this->db->get_where('fee_rules', array('fee_rule_id' => $student[0]['plan_id']))->result_array();
            if (count($student_plan) > 0) {
        ?>
            var disc_per_inst = <?php echo $student_plan[0]['disc_per_inst']; ?>;
            var max_discount_merge = <?php echo $student_plan[0]['max_discount_merge']; ?>;
        <?php
            } else {
        ?>
            var disc_per_inst = 0;
            var max_discount_merge = 0;
        <?php
            }
        ?>
    
        for (let i = 0; i < selected.length; i++) {
    
            let currentLoan = loans[selected[i]];
    
            if (!currentLoan) {
                continue;
            }
    
            let paymentComment = currentLoan.payment_comment;
            let paymentPlan = currentLoan.payment_plan;
            let paymentDeadline = currentLoan.dead_line;
            consultationFeeDeadline = fetchcouncildate(currentLoan.id);
                if(consultationFeeDeadline)
                    paymentDeadline = consultationFeeDeadline;
                    
            
    
            let isConsultationFee =
                paymentComment == 'consulation fee' ||
                paymentPlan == 'consulation fee';
    
            if (isConsultationFee && paymentDeadline) {
    
                
                let deadlineMoment = moment(paymentDeadline, "YYYY-MM-DD");
                let todayMoment = moment().startOf('day');
                $('.modal-body #paid_date')
                .attr('data-dead-line', paymentDeadline)
                .attr('max', paymentDeadline);
                
    
                if (deadlineMoment.isBefore(todayMoment, 'day')) {
    
                    hasExpiredConsultationFee = true;
                    consultationFeeID = currentLoan.id;
    
                    if (consultationFeeDeadline == '') {
                        consultationFeeDeadline = paymentDeadline;
                    } else {
                        if (
                            moment(paymentDeadline, "YYYY-MM-DD")
                                .isAfter(moment(consultationFeeDeadline, "YYYY-MM-DD"))
                        ) {
                            consultationFeeDeadline = paymentDeadline;
                        }
                    }
                }
                
                $(document).off('change', '.modal-body #paid_date').on('change', '.modal-body #paid_date', function () {

                    let maxDate = $(this).attr('data-dead-line');
                
                    let selectedDate = $(this).val();
                
                    if (maxDate && selectedDate > maxDate) {
                
                        alert('Paid date consultation fee deadline se zyada nahi ho sakti.');
                
                        $(this).val(maxDate);
                
                    }
                
                });
            }
            
    
            chalans += (currentLoan.challan_no + ',');
            amounts += parseFloat(currentLoan.amount || 0);
            deadlines += currentLoan.dead_line;
            type += currentLoan.payment_comment + ',';
            remainings += parseFloat(currentLoan.remaining_installment_amount || 0);
            extraamount += parseFloat(currentLoan.extra_amount || 0);
    
            let fee_fine = 0;
            let m = moment(currentLoan.dead_line, "YYYY-MM-DD");
            let todays = moment().startOf('day');
            let days = Math.round(moment.duration(todays - m).asDays());
            let diff = parseInt(days);
    
            if (isNaN(diff)) {
    
                fee_fine = 0;
                diff = 0;
    
            } else {
    
                if (diff > 1) {
    
                    fee_fine = diff * 50;
                    latedays += diff;
    
                } else {
    
                    fee_fine = 0;
                    diff = 0;
    
                    var dateofvisit = moment(currentLoan.dead_line, 'YYYY-MM-DD');
                    var today = moment();
    
                    if (today.diff(dateofvisit, 'days') < -30 && disc < max_discount_merge) {
                        disc += parseFloat(disc_per_inst);
                    }
                }
            }
    
            fine += fee_fine;
            feeids += ',' + currentLoan.id;
        }
    
        installment = parseFloat(amounts);
        amountstotal = parseFloat(amounts + remainings + extraamount + fine);
        discount = parseFloat(disc * (amountstotal / 100));
        amounttotal = parseFloat((amounts + remainings + extraamount + fine) - discount);
        amounttotal = Math.trunc(amounttotal);
    
        $(".modal-body #instabox").addClass('hidden');
        $(".modal-body #prevbox").addClass('hidden');
        $(".modal-body #finebox").addClass('hidden');
    
        if (remainings > 0) {
            $(".modal-body #instabox").removeClass('hidden');
        }
    
        if (extraamount > 0) {
            $(".modal-body #prevbox").removeClass('hidden');
        }
    
        if (fine > 0) {
            $(".modal-body #finebox").removeClass('hidden');
        }
    
        if (hasExpiredConsultationFee) {
    
            // $('.modal-body .date-picker').attr('data-date', consultationFeeDeadline);
    
            if ($.fn.datepicker) {
                try {
                    $('.modal-body .date-picker').datepicker('setEndDate', consultationFeeDeadline);
                } catch (e) {
                    console.log('datepicker setEndDate error:', e);
                }
            }
    
            $('.modal-body .fee_pay_through').each(function () {
    
                if ($(this).val() == 'bank') {
                    this.checked = true;
                    this.disabled = false;
                    $(this).closest('label').show();
                } else {
                    this.checked = false;
                    this.disabled = true;
                    $(this).closest('label').hide();
                }
    
            });
    
            $('.modal-body .bank').show();
            $('.modal-body .college').hide();
            $('.modal-body .receipt_book').hide();
    
            $('.modal-body .bank_details').attr('required', 'required');
            $('.modal-body #payfeebtn').hide();
    
            $('.modal-body .scan_challan').attr('required', 'required');
            $('.modal-body .challan').show();
            
            $('#council_fee_last_date').text(consultationFeeDeadline);
            $('#council_fee_passed').show();
            
            
            $(document).on('click', '#regenerate_council_fee_btn', function () {
                window.location.href = "<?php echo site_url('students/regenerate_council_fee'); ?>"+'/'+<?php echo $this->uri->segment(3);?>+"/"+consultationFeeID;
            });
    
        } else {
            // if (isConsultationFee) {
            //     $('.modal-body #paid_date')
            //         .attr('data-dead-line', paymentDeadline)
            //         .attr('max', paymentDeadline);
        
            //     if ($.fn.datepicker) {
            //         try {
            //             $('.modal-body .date-picker').datepicker('setEndDate', false);
            //         } catch (e) {
            //             console.log('datepicker reset error:', e);
            //         }
            //     }
            // }
    
            $('.modal-body .fee_pay_through').each(function () {
                this.disabled = false;
                $(this).closest('label').show();
            });
    
            $('.modal-body .fee_pay_through[value="bank"]').prop('checked', true);
            $('.modal-body .receipt_book').hide();
        }
    
        $(".modal-body #chalans").text(chalans);
        $(".modal-body #amounts").text(amounts);
        $(".modal-body #deadlines").text(deadlines);
        $(".modal-body #type").text(type);
        $(".modal-body #remainings").text(remainings);
        $(".modal-body #remaining_installment_amount").val(remainings);
        $(".modal-body #extraamount").text(extraamount);
        $(".modal-body #previous_extra_amounts").val(extraamount);
        $(".modal-body .previous_extra_amount").attr('data-value', extraamount);
        $(".modal-body .previous_extra_amount").attr('max', extraamount);
        $(".modal-body #fine").text(fine);
        $(".modal-body #fine_amount").val(fine);
        $(".modal-body #fine_amount").attr('max', fine);
        $('.modal-body #fine_amount').attr('data-fine', fine);
        $(".modal-body #latedays").text(latedays);
        $(".modal-body #amounttotal").text(amounttotal);
        $(".modal-body #instamount").val(installment);
        $(".modal-body #fee_amount").val(amounttotal);
        $(".modal-body #actual_amount").val(amounttotal);
        $(".modal-body #fee_ids").val(feeids);
        $(".modal-body #discount").val(discount);
        $('.modal-body #discount').attr('data-discount', discount);
        $(".modal-body #discount").attr('max', discount);
        $(".modal-body #discount").attr('min', 0);
        $(".modal-body #remove_fine_amount").val(0);
        $(".modal-body #challans").val(chalans);
    
        $('.modal-body #discount').off('keyup change').on('keyup change', function () {
    
            let olddiscount = parseFloat($('.modal-body #discount').data('discount') || 0);
            let newdiscount = parseFloat($('.modal-body #discount').val() || 0);
    
            if (newdiscount > olddiscount || newdiscount < 0) {
                $(".modal-body #discount").val(olddiscount);
                newdiscount = olddiscount;
            }
    
            installment = parseFloat(amounts);
            amountstotal = parseFloat(amounts + remainings + extraamount + fine);
            discount = newdiscount;
            amounttotal = parseFloat((amounts + remainings + extraamount + fine) - discount);
            amounttotal = Math.trunc(amounttotal);
    
            if (hasExpiredConsultationFee) {
                $('.modal-body #paid_date')
                    .val(consultationFeeDeadline)
                    .attr('data-dead-line', consultationFeeDeadline)
                    .attr('max', consultationFeeDeadline);
            } else {
                $('.modal-body #paid_date')
                    .attr('data-dead-line', loans[selected[0]].dead_line)
                    .removeAttr('max');
            }
    
            $(".modal-body #chalans").text(chalans);
            $(".modal-body #amounts").text(amounts);
            $(".modal-body #deadlines").text(deadlines);
            $(".modal-body #type").text(type);
            $(".modal-body #remainings").text(remainings);
            $(".modal-body #remaining_installment_amount").val(remainings);
            $(".modal-body #extraamount").text(extraamount);
            $(".modal-body #previous_extra_amounts").val(extraamount);
            $(".modal-body #fine").text(fine);
            $(".modal-body #fine_amount").val(fine);
            $(".modal-body #fine_amount").attr('max', fine);
            $('.modal-body #fine_amount').attr('data-fine', fine);
            $(".modal-body #latedays").text(latedays);
            $(".modal-body #amounttotal").text(amounttotal);
            $(".modal-body #instamount").val(installment);
            $(".modal-body #fee_amount").val(amounttotal);
            $(".modal-body #actual_amount").val(amounttotal);
            $(".modal-body #fee_ids").val(feeids);
            $(".modal-body #discount").val(discount);
            $(".modal-body #remove_fine_amount").val(0);
            $(".modal-body #challans").val(chalans);
    
        });
    
    }
    
    function fetchcouncildate(fee_id) {

        var to_date = '';
    
        $('#council_fee_deadline_loader').show();
    
        $.ajax({
            type: "GET",
            async: false,
            url: '<?php echo site_url(); ?>/students/get_council_date/<?php echo $this->uri->segment(3); ?>/' + fee_id,
            dataType: "json",
            success: function (data) {
    
                $('#council_fee_deadline_loader').hide();
    
                if (data && data.to_date) {
                    to_date = data.to_date;
                }
            },
            error: function (xhr) {
    
                $('#council_fee_deadline_loader').hide();
    
                console.log(xhr.responseText);
                alert('Council fee deadline fetch nahi hui.');
            }
        });
    
        return to_date;
    }
    
    function myFunction() {
        if (window.pageYOffset > sticky) {
            header.classList.add("sticky");
            header.style.marginTop   = "50px";
        } else {
            header.classList.remove("sticky");
            header.style.marginTop   = "0px";
        }
    }
    
    function openchallan() {

        var selected = [];
        $('div#checkboxes input[type=checkbox]').each(function () {
            if ($(this).is(":checked")) {
                selected.push($(this).attr('value'));
            }
        });

        let loans = <?php echo json_encode($payments) ?>;
        //var leave = leaves[myBookId];
        var chalanids='';
        for (let i=0;i<selected.length;i++){
            chalanids+=(loans[selected[i]].id+',');
        }
        let link = <?php echo json_encode(site_url()); ?>;
        link=link+'/students/print_challan/'+chalanids;
        window.open(link,"_blank");
    }
    
	document.addEventListener("DOMContentLoaded", function(event) {
	    
	    jQuery(document).ready(function () {

            jQuery('div#checkboxes').on('change', '.normal-selection', function () {
        
                let currentIndex = parseInt(jQuery(this).attr('data-index'));
        
                if (jQuery(this).is(':checked')) {
        
                    let nextIndex = currentIndex + 1;
                    jQuery('#box_' + nextIndex).show();
        
                } else {
        
                    jQuery('.normal-selection').each(function () {

                        let checkboxIndex = parseInt(jQuery(this).attr('data-index'));
        
                        if (checkboxIndex > currentIndex) {
        
                            this.checked = false;
        
                            jQuery(this).prop('checked', false);
        
                            // agar theme/plugin checked class lagata hai
        
                            jQuery(this).removeClass('checked active selected');
        
                            // parent/label/span par checked class remove
        
                            jQuery(this).closest('label').removeClass('checked active selected');
        
                            jQuery(this).closest('.checker').removeClass('checked active selected');
        
                            jQuery(this).closest('.icheckbox_minimal').removeClass('checked');
        
                            jQuery(this).closest('.icheckbox_square-blue').removeClass('checked');
        
                            jQuery(this).parent().removeClass('checked active selected');
        
                            jQuery('#box_' + checkboxIndex).hide();
        
                        }
        
                    });
        
                }
        
            });
        });
        
        jQuery('.submit_in').change(function(){
                var submit_in = jQuery(this).val();
                
                if(submit_in=='college')
                {
                    jQuery('.college').show();
                    jQuery('.bank').hide();
                    jQuery('.bank_details').removeAttr('required');
                    jQuery('#payfeebtn').show();
                }else if(submit_in=='pay_pro')
                {
                    jQuery('.college').hide();
                    jQuery('.bank').hide();
                    jQuery('.receipt_book').hide();
                    jQuery('.bank_details').removeAttr('required');
                    jQuery('#payfeebtn').show();
                }
                else
                {
                    jQuery('.bank').show();
                    jQuery('.college').hide();
                    jQuery('.receipt_book').hide();
                    jQuery('.bank_details').attr('required','required');
                    jQuery('#payfeebtn').hide();
                }

                //CHECKING
                var pay_by = jQuery('.pay_by').val();
                if(pay_by=='computer_challan' && submit_in=='college')
                {
                    jQuery('.scan_challan').removeAttr('required');
                    jQuery('.challan').hide();
                    //jQuery('.application').hide();
                }else if(submit_in=='pay_pro')  {
                    jQuery('.scan_challan').removeAttr('required');
                    jQuery('.challan').hide();
                    //jQuery('.application').hide();
                }
                else
                {
                    jQuery('.scan_challan').attr('required','required');
                    jQuery('.challan').show();
                    //jQuery('.application').show();
                }
            });
            
            jQuery('.pay_by').change(function(){
                var pay_by = jQuery(this).val();
                if(pay_by=='receipt_book')
                {
                    jQuery('.receipt_book').show();
                    jQuery('.submitted_fee_campus_id').attr('required','required');
                    jQuery('.book_no').attr('required','required');
                    jQuery('.receipt_no').attr('required','required');
                }
                else
                {
                    jQuery('.receipt_book').hide();
                    jQuery('.submitted_fee_campus_id').removeAttr('required');
                    jQuery('.book_no').removeAttr('required');
                    jQuery('.receipt_no').removeAttr('required');
                }
                //CHECKING
                if(pay_by=='computer_challan')
                {
                    jQuery('.scan_challan').removeAttr('required');
                    jQuery('.challan').hide();
                    //jQuery('.application').hide();
                }
                else
                {
                    jQuery('.scan_challan').attr('required','required');
                    jQuery('.challan').show();
                    //jQuery('.application').show();
                }
            });
	    
        $('.fee_for').change(function(){
            var fee_for = $(this).val();
            if(fee_for=='Other')
            {
                $('.payment_for_container').show();
                $('.payment_for').attr('required','required');
            }
            else
            {
                $('.payment_for_container').hide();
                $('.payment_for').removeAttr('required');
            }
        });
        
        $('#payfeebtn').hide();
        
        $('#verify_now').click(function () {

            var tid = $('#tid_no').val();
            var bank = $('#select_bank').val();
            var paid_date = $('#paid_date').val();
            var amount = $('#actual_amount').val();

            if (bank.length < 6) {
                $('#error_verify').text('Select Bank');
            } else {

                if (tid.length < 4) {
                    $('#error_verify').text('Minimum Tid Length will be 4');
                }
                else {

                    $.ajax({
                        type: "post",
                        async: false,
                        url: '<?php echo site_url()?>/students/verify_fee',
                        data: {
                            tid: tid,
                            bank: bank,
                            amount: amount,
                            paid_date: paid_date
                        },
                        success: function (data) {
                            data = $.trim(data);
                            if (data === 'success') {
                                $('#payfeebtn').show();
                                $('#error_verify').html('');
                                $('#error_verify').html(data);
                            } else {
                                $('#error_verify').html(data);
                                $('#payfeebtn').hide();
                            }
                        }
                    });
                }
            }
        });
        
        // Your code to run since DOM is loaded and ready
        var total_amount = <?php echo @$this->db->get('council_rules')->row()->max_council_fee;?>;
        
        $('#extra_fee_amount').on( 'keyup change', function () {

            let amount = $(this).val();

            if(amount > total_amount){
                if ($('#my_fee_type').val() == 'consulation fee') {
                    $("#extra_fee_amount").val(total_amount);
                }
            }
        });
        
        $('.fee_type').on( 'change', function () {
            let amount = $(this).val();
            if (amount == 'consulation fee') {
                $("#extra_fee_amount").val(total_amount);
                $("#extra_fee_amount").prop('readonly', true);
            }else {
                $("#extra_fee_amount").val('0');
                $("#extra_fee_amount").prop('readonly', false);
            }
            if(isNumeric(amount)) {

                var loans = <?php echo json_encode($payment_rules) ?>;

                $("#extra_fee_amount").val(loans[amount]['amount']);
                $("#extra_fee_amount").prop('readonly', true);
            }
        });

        var reversible_amount = <?php echo ($paid_fee[0]['paid_fee']-$discountfee)?>;
        $('#rev_amount').on( 'keyup change', function () {
            let amount = $(this).val();
            if(amount > reversible_amount){
                $("#rev_amount").val(reversible_amount);
            }
        });

        $('#fine_amount').on('change',function(){
            var submitin = $(".fee_pay_through:checked").val();
            if(submitin=='bank')
            {
                $('#error_verify').html('');
                $('#payfeebtn').hide();
            }
            else
            {
                $('#payfeebtn').show();
            }
        });

        $('#finebox').on('click',function(){
            var submitin = $(".fee_pay_through:checked").val();
            //alert(submitin);
            if(submitin=='bank')
            {
                $('#error_verify').html('');
                $('#payfeebtn').hide();
            }
            else
            {
                $('#payfeebtn').show();
            }
        });
        
        $('.installment_day').on('change',function(){
            var installment_day = jQuery('.installment_day').val();
            var installment_start_month = jQuery('.installment_start_month').val();
            var installment_end_month = jQuery('.installment_end_month').val();
            var fee_not_created = jQuery('.fee_not_created').val();
            $.ajax({
                type: "post",
                async: false,
                url: '<?php echo site_url();?>/students/check_add_fee_installments/<?php echo $this->uri->segment(3);?>',
                data: {
                    installment_day: installment_day,
                    installment_start_month: installment_start_month,
                    installment_end_month: installment_end_month,
                    fee_not_created: fee_not_created
                },
                success: function (data) {
                    jQuery('.fee_data').html(data);
                }
            });
        });
        
        $('.installment_start_month').on('change',function(){
            var installment_day = jQuery('.installment_day').val();
            var installment_start_month = jQuery('.installment_start_month').val();
            var installment_end_month = jQuery('.installment_end_month').val();
            var fee_not_created = jQuery('.fee_not_created').val();
            $.ajax({
                type: "post",
                async: false,
                url: '<?php echo site_url();?>/students/check_add_fee_installments/<?php echo $this->uri->segment(3);?>',
                data: {
                    installment_day: installment_day,
                    installment_start_month: installment_start_month,
                    installment_end_month: installment_end_month,
                    fee_not_created: fee_not_created
                },
                success: function (data) {
                    jQuery('.fee_data').html(data);
                }
            });
        });
        
        $('.installment_end_month').on('change',function(){
            var installment_day = jQuery('.installment_day').val();
            var installment_start_month = jQuery('.installment_start_month').val();
            var installment_end_month = jQuery('.installment_end_month').val();
            var fee_not_created = jQuery('.fee_not_created').val();
            $.ajax({
                type: "post",
                async: false,
                url: '<?php echo site_url();?>/students/check_add_fee_installments/<?php echo $this->uri->segment(3);?>',
                data: {
                    installment_day: installment_day,
                    installment_start_month: installment_start_month,
                    installment_end_month: installment_end_month,
                    fee_not_created: fee_not_created
                },
                success: function (data) {
                    jQuery('.fee_data').html(data);
                }
            });
        });
        
        $('.view_deleted_installments').click(function(){
            $('.deleted_installment').show();
            $(this).hide();
            $('.hide_deleted_installments').show();
        });
        
        $('.hide_deleted_installments').click(function(){
            $('.deleted_installment').hide();
            jQuery(this).hide();
            jQuery('.view_deleted_installments').show();
        });
        
        $('.rmv').click(function(){
            $(this).hide();
        });

    });

    function editComment(id) {
        $('#comment-text-' + id).hide();
        $('#comment-input-' + id).show();
        $('#council-sequence-' + id).show();
        $('#payment-comment-' + id).show();
        
        $('#save-btn-' + id).show();
    }

    function saveComment(id) {
        var comment = $('#comment-input-' + id).val();
        var sequence_id = $('#council-sequence-' + id).val();
        var payment_comment = $('#payment-comment-text-' + id).val();
    
        var imageInput = $('#council-image-' + id)[0];
    
        var formData = new FormData();
        formData.append('id', id);
        formData.append('payment_comment', comment);
        formData.append('sequence_id', sequence_id);
        formData.append('comment', payment_comment);
    
        if (imageInput.files.length > 0) {
            formData.append('image', imageInput.files[0]);
        }
    
        $.ajax({
            url: '/lahore-campus/index.php/students/update_payment_comment',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                $('#comment-text-' + id).text(response.payment_comment).show();
                $('#comment-input-' + id).hide();
                $('#council-sequence-' + id).hide();
                $('#save-btn-' + id).hide();
                $('#payment-comment-' + id).hide();
                location.reload();
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                alert('Image/comment save nahi hua.');
            }
        });
    }

    function isNumeric(n) {
        return !isNaN(parseFloat(n)) && isFinite(n);
    }

    function fee_reversal_modal(index) {
        let loans = <?php echo json_encode($payments) ?>;
        //var challans = loans[index].paid_challans;
        var amount = loans[index].actual_amount;
        var payment_id = loans[index].id;
        jQuery('#installment_amount').val(amount);
        jQuery('#rev_amount').val(amount);
        jQuery('#rev_amount').attr('max',amount);
        jQuery('#payment_id').val(payment_id);
        //alert(amount);

        //$('#rev_challans').html('<Strong> Paid Challans : </strong>:'+challans);
        //$('#rev_challans_amount').html('<Strong> Paid Amount : </strong>:'+amount);
        //$('#rev_challans_paid_date').html('<Strong> Paid Date : </strong>:'+loans[index].paid_date);
        //$('#rev_type').val(challans);
        //$('#rev_amount').val(amount);
        //$('#rev_amount').attr('readonly',true);
        //$('#rev_details').show();

        $('#fee_reversal_modal').modal('show');
    }

    function fee_reversal_proof_modal(index) {
        let loans = <?php echo json_encode($payments) ?>;
        //var challans = loans[index].paid_challans;
        var payment_id = loans[index].id;
        jQuery('.payment_id').val(payment_id);

        $('#fee_reversal_process_modal').modal('show');
    }

    function open_empty_reversal_modal() {

        $('#rev_challans').html('');
        $('#rev_challans_amount').html('');
        $('#rev_challans_paid_date').html('');
        $('#rev_type').val('');
        $('#rev_amount').val('');
        $('#rev_amount').attr('readonly',false);
        $('#rev_details').hide();

        $('#fee_reversal').modal('show');
    }

</script>