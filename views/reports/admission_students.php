<?php
	$myAccess = checkUserAccess();
?>	
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
                <div class="col-md-12">
                    <!-- BEGIN EXAMPLE TABLE PORTLET-->
                    <div class="portlet box grey-cascade">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-list"></i>New Students
                            </div>
                        </div>
                        <div class="portlet-body">
                            <table class="table table-bordered table-hover" id="sample_2">
                                <thead>
                                <tr>
                                    <th class="hidden">
                                        Hidden
                                    </th>
                                    <th>
                                        Student Information &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    </th>
                                    <th>
                                        Fee Information
                                    </th>
                                    <th>
                                        Paid Fee Details
                                    </th>
                                    <th>
                                        Unpaid Fee Details
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $i=0;
                                foreach($students as $student):
                                    $show=0;
                                    //CHECK FEE
                                    $this->db->order_by('dead_line','ASC');
                                    $payments=$this->db->get_where('payments',array('student_id'=>$student['student_id']))->result_array();
                                    $total_fee = 0;
                                    $created_council_fee = 0;
                                    $submitted_council_fee = 0;
                                    $fee_decided_current_time = 0;
                                    $total_fee_submitted = 0;
                                    $unpaid_installments_current_time = 0;
                                    foreach($payments as $payment)
                                    {
                                        if($payment['payment_plan']!='consulation fee')
                                        {
                                            $total_fee+=$payment['amount'];
                                        }
                                        if($payment['payment_plan']=='consulation fee')
                                        {
                                            $created_council_fee+=$payment['amount'];
                                            if($payment['paid']==1)
                                            {
                                                $submitted_council_fee+=$payment['actual_amount'];
                                            }
                                        }
                                        if($payment['dead_line']<date('Y-m-d'))
                                        {
                                            $fee_decided_current_time+=$payment['amount'];
                                            if($payment['paid']==0)
                                            {
                                                $unpaid_installments_current_time++;
                                            }
                                        }
                                        if($payment['paid']==1 && $payment['payment_plan']!='consulation fee')
                                        {
                                            $total_fee_submitted+=$payment['actual_amount'];
                                        }
                                        //CHECK ANY PAYMENT 1 MONTH BEFORE
                                        $oneMonthOldDate = date("Y-m-d", strtotime( date( "Y-m-d", strtotime( date("Y-m-d") ) ) . "-1 month" ) );
                                        if($payment['paid']==0 && $payment['dead_line']<$oneMonthOldDate)
                                        {
                                            $show=1;
                                        }
                                    }
                                    if($student['status']==0)
                                    {
                                        $show=0;
                                    }

                                        ?>
                                        <tr class="odd gradeX">
                                            <td class="hidden">
                                                <?php echo $i;?>
                                            </td>
                                            <td>
                                                Campus : <?php echo $student['campus_name'];?>
                                                <br />
                                                Course : <?php echo $student['course_name'];?>
                                                <br />
                                                Session : <span class="bold"><?php echo $student['session'];?></span>
                                                <br />
                                                Class : <?php echo $student['class_name'];?>
                                                <br />
                                                Registration Date : <span class="bold"><?php echo $student['registration_date'];?></span>
                                                <br />
                                                Student Name : <span class="bold"><?php echo $student['first_name'].' '.$student['last_name'];?></span>
                                                <br />
                                                CNIC : <?php echo $student['cnic'];?>
                                                <br />
                                                Father Name : <?php echo $student['father_name'];?>
                                                <br />
                                                Roll # : <span class="bold"><?php echo $student['roll_no'];?></span>
                                                <br />
                                                Mobile : <span class="bold"><?php echo $student['mobile'];?> - <?php echo $student['emergency_no'];?></span>
                                            </td>
                                            <td>
                                                Total Fee : <?php echo $student['total_fee'];?>
                                                <br />
                                                Total Created Fee : <?php echo $total_fee;?>
                                                <br />
                                                Total Created Council Fee : <?php echo $created_council_fee;?>
                                                <br />
                                                Total Submitted Council Fee : <?php echo $submitted_council_fee;?>
                                                <br />
                                                Fee Decided Current Time : <span class="bold"><?php echo $fee_decided_current_time;?></span>
                                                <br />
                                                Total Fee Submitted : <span class="bold"><?php echo ($total_fee_submitted+$submitted_council_fee);?></span>
                                                <br />
                                                Remaining Fee Payable Current Time : <span class="bold"><?php echo $fee_decided_current_time-$total_fee_submitted;?></span>
                                                <br />
                                                Unpaid installments Current Time : <span class="bold"><?php echo $unpaid_installments_current_time;?></span>
                                                <br />
                                                Percentage Fee Received : <?php echo round(($total_fee_submitted/$total_fee)*100).'%';?>
                                            </td>
                                            <td>
                                                <?php
                                                foreach($payments as $payment)
                                                {
                                                    if($payment['paid']==1)
                                                    {
                                                        echo $payment['actual_amount'].' Paid on '.$payment['paid_date'].'<br />';
                                                    }
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                foreach($payments as $payment)
                                                {
                                                    if($payment['paid']==0)
                                                    {
                                                        if($payment['dead_line']<date('Y-m-d'))
                                                        {
                                                            echo '<span class="bold">'.$payment['amount'].' Not Paid on '.$payment['dead_line'].'</span><br />';
                                                        }
                                                        else
                                                        {
                                                            echo $payment['amount'].' Not Paid on '.$payment['dead_line'].'<br />';
                                                        }
                                                    }
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
        </div>
    </div>