<?php
	$myAccess = checkUserAccess();
?>
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
            <!-- Student Data-->
            <?php
            if(@$myAccess[0]['dashboard_check_student_box']==1 || $this->session->userdata('role')=='Admin'):
                ?>
                <div class="col-md-6" style="background-color: white;padding: 10px 20px;    border-radius: 20px!important;box-shadow: 10px 10px 5px -8px rgba(0,0,0,0.75);">
                    <h3 style="margin:5px 0px 20px 0px;font-weight: bold">Select Date To view Accounts closing sheet</h3>
                    <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/reports/PettyCashReport">
                        <div class="form-body">
                            <div class="form-group">
                                <label class="control-label col-md-3">Closing Date</label>
                                <div class="input-group input-medium date date-picker col-md-9" data-date="<?php echo @$selected_date;?>" data-date-format="yyyy-mm-dd" data-date-end-date="0d" data-date-viewmode="years">
                                    <input type="text" name="to_date" class="form-control" value="<?php echo @$selected_date;?>" readonly>
                                    <span class="input-group-btn">
                                    <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-12" style="text-align: center">
                                    <button type="submit" class="btn green">Check</button>
                                    <div class="form-group">
                                        <label class="control-label col-md-12" style="font-weight: bolder">Last Closing Date : <?php echo $last_closing?></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="row" id="quotation-details">

                    <div class="col-md-6" style="background-color: white;padding: 20px 30px;    border-radius: 20px!important;box-shadow: 10px 10px 5px -8px rgba(0,0,0,0.75);">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-list"></i> Last Closing/Verification Date
                            </div>
                        </div>

                        <table class="table table-striped table-bordered table-hover" id="">
                            <thead>
                            <tr>
                                <th>
                                    College
                                </th>

                                <th>
                                    Closing Date
                                </th>

                                <th>
                                    Verification Date
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $allowed_closing = true;
                            $i=1;
                            foreach($campusclosings as $key=>$closes):
                                if ($selected_date != ($campusclosingverified[$key]['year'].'-'.$campusclosingverified[$key]['month'].'-'.$campusclosingverified[$key]['day']))
                                    $allowed_closing = false;
                                ?>
                                <tr class="odd gradeX">

                                    <td>
                                        <?php echo $closes['campus_name'];?>
                                    </td>

                                    <td>
                                        <?php echo $closes['day'] .'-'.$closes['month'].'-'.$closes['year'] ;?>
                                    </td>
                                    <td>
                                        <?php echo $campusclosingverified[$key]['day'] .'-'.$campusclosingverified[$key]['month'].'-'.$campusclosingverified[$key]['year'] ;?>
                                    </td>

                                </tr>
                                <?php
                                $i++;
                            endforeach;
                            ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-12">
                        <!-- BEGIN EXAMPLE TABLE PORTLET-->
                        <div class="portlet box grey-cascade">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-list"></i> Daily Campus closing sheet
                                </div>
                            </div>
                            <div class="portlet-body">
                                <table class="table table-striped table-bordered table-hover" id="">
                                    <thead>
                                    <tr>
                                        <th >
                                            Sr
                                        </th>
                                        <th>
                                            Date
                                        </th>
                                        <th>
                                            College
                                        </th>

                                        <th>
                                            Received Amount
                                        </th>

                                        <th>
                                            Closing Person
                                        </th>

                                        <th>
                                            Closed By
                                        </th>
                                        <th>
                                            Image
                                        </th>

                                        <th>
                                            Status
                                        </th>
                                        <th>
                                            Accounts Status
                                        </th>
                                        <th>
                                            View
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    <?php
                                    $i=0;
                                    $cash_req = 0;
                                    $cash_receive = 0;
                                    foreach($closings as $list):
                                        ?>
                                        <tr class="odd gradeX">
                                            <td >
                                                <?php echo $i+1;?>
                                            </td>
                                            <td>
                                                <?php echo $selected_date;?>
                                            </td>
                                            <td>
                                                <?php echo $list['campus_name'];?>
                                            </td>
                                            <th>
                                                <?php
                                                $cash_receive += $list['closing_amount'];
                                                echo $list['closing_amount'];?>
                                            </th>
                                            <td>
                                                <?php echo $list['first_name']. ' '.$list['last_name'];?>
                                            </td>
                                            <td>
                                                <?php echo $list['closed_by'];?>
                                            </td>
                                            <td>
                                                <?php
                                                if( $list['partialy_closed_image']!=''):
                                                    ?>
                                                    <a href="<?php echo base_url().'uploads/'.$list['partialy_closed_image'];?>" target="_blank">
                                                        <button type="button" class="btn btn-default"><i class="fa fa-image"></i> Image</button>
                                                    </a>
                                                <?php
                                                endif;
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                if ($list['closed_status'] == '0')
                                                    echo  "<a data-toggle='modal' data-id='$i' class='btn btn-primary' style='width: 100px'>OPEN</a>";

                                                else {

                                                    if ($list['close_type'] == '2' )
                                                        echo " <a data-toggle='modal' class='btn green' style='width: 100px' >Closed</a>";
                                                    else {

                                                        if ($list['transaction_no'] == NULL)
                                                            echo '<a data-toggle="modal" data-id="'.$i.'" title="Add this item" class="open-AddClosing btn btn-warning" href="#closingdetails">
                                                        <i class="fa fa-dollar"> Partially Closed</i>
                                                    </a>';
                                                        else{
                                                            echo " <a data-toggle='modal' class='btn green' style='width: 100px' >Closed</a>";

                                                        }
                                                    }
                                                }

                                                ?>
                                            </td>
                                            <td>
                                                <?php

                                                if($list['closed_status'] != '0'){
                                                    if ($list['checked_by'] == 'NULL' || $list['checked_by'] == '' )
                                                        echo  "<a  style='width: 100px; color:red;' >UnVerified</a>";

                                                    else {

                                                        echo  "<a  style='width: 100px; color:green;' >Verified</a>";
                                                    }
                                                }

                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $key = array_search($list['campus_id'], array_column($campusclosings, 'campus_id'));

                                                $reqdate=$campusclosings[$key]['year'] .'-'.$campusclosings[$key]['month'].'-'.$campusclosings[$key]['day'].'';
                                                $stop_date = date('Y-m-d', strtotime($reqdate . ' +1 day'));
                                                if ($selected_date == $stop_date): ?>
                                                    <a href="<?php  echo site_url().'/closing/viewclosing/'.$selected_date.'/'.$list['campus_id'].'/'.$list['closing_amount'].'/'.$list['closed_status']?>"> VIEW </a>
                                                <?php else: ?>
                                                    <a href="<?php  echo site_url().'/closing/viewclosing/'.$selected_date.'/'.$list['campus_id'].'/'.$list['closing_amount'].'/1'?>"> VIEW </a>

                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php
                                        $i++;
                                    endforeach;
                                    ?>
                                    </tbody>
                                    <tfoot>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th><?php echo  $cash_receive;?></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <!-- END EXAMPLE TABLE PORTLET-->
                    </div>
                    <div class="col-md-12">
                        <!-- BEGIN SAMPLE FORM PORTLET-->
                        <div class="portlet box green ">

                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-user"></i> Petty Cash Accounts Closing
                                </div>
                            </div>
                            <div class="portlet-body table-responsive">
                                <div class="col-md-12 ">
                                    <div class="col-md-4">
									</div>
                                </div>
                                <table class="table table-bordered table-hover" >
                                    <thead>
                                    <tr>
                                        <th>
                                            User
                                        </th>

                                        <th>
                                            Opening Balance
                                        </th>

                                        <th>
                                            Received Amount
                                        </th>

                                        <th>
                                            Transfer Amount
                                        </th>

                                        <th>
                                            Expense Amount
                                        </th>
                                        <th>
                                            Reversal Amount
                                        </th>
                                        <th>
                                            Balance
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $i=0;
                                    $opening_bal    = 0;
                                    $receive_bal    = 0;
                                    $sent_bal       = 0;
                                    $expenses_bal   = 0;
                                    $reversal_bal   = 0;
                                    $closing_bal    = 0;
                                        foreach($Pettycashs as $Pettycash):
                                            $opening_bal    += $Pettycash ['opening_balance'];
                                            $receive_bal    += $Pettycash ['received'];
                                            $sent_bal       += $Pettycash ['sent'];
                                            $expenses_bal   += $Pettycash ['expenses'];
                                            $reversal_bal   += $Pettycash ['reversal'];
                                            $closing_bal    += ($Pettycash ['opening_balance']+$Pettycash ['received']+$Pettycash ['reversal'])-$Pettycash ['sent']-$Pettycash ['expenses'];
                                            ?>
                                            <tr class="odd gradeX">
                                                <td><?php  echo $Pettycash ['first_name'].' '.$Pettycash ['last_name'];  ?></td>
                                                <td><?php  echo $Pettycash ['opening_balance'] ?></td>
												<td><a href="<?php echo site_url();?>/reports/view_received_report/<?php echo $selected_date;?>/<?php echo $Pettycash['id'];?>/received"><?php echo $Pettycash ['received'];?></a></td>
												<td><a href="<?php echo site_url();?>/reports/view_received_report/<?php echo $selected_date;?>/<?php echo $Pettycash['id'];?>/sent"><?php echo $Pettycash ['sent'];?></a></td>
												<td><a href="<?php echo site_url();?>/reports/view_expense_report/<?php echo $selected_date;?>/<?php echo $Pettycash['id'];?>/expense"><?php echo $Pettycash ['expenses'];?></a></td>
												<td><a href="<?php echo site_url();?>/reports/view_expense_report/<?php echo $selected_date;?>/<?php echo $Pettycash['id'];?>/reversal"><?php echo $Pettycash ['reversal'];?></a></td>
                                                <td><?php  echo ($Pettycash ['opening_balance']+$Pettycash ['received']+$Pettycash ['reversal'])-$Pettycash ['sent']-$Pettycash ['expenses']?></td>
                                            </tr>
                                            <?php
                                            $i++;
                                        endforeach;
                                    ?>
                                    </tbody>
                                    <tfoot>
                                        <th></th>
                                        <th><?php echo  $opening_bal?></th>
                                        <th><?php echo  $receive_bal?></th>
                                        <th><?php echo  $sent_bal?></th>
                                        <th><a href="<?php echo site_url();?>/reports/view_expense_report/<?php echo $selected_date;?>/0/expense"><?php echo  $expenses_bal?></a></th>
                                        <th><?php echo  $reversal_bal?></th>
                                        <th><?php echo  $closing_bal?></th>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <!-- END SAMPLE FORM PORTLET-->
                    </div>
                    <div class="col-md-12">
                        <!-- BEGIN EXAMPLE TABLE PORTLET-->
                        <div class="portlet box grey-cascade">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-list"></i> Cash Accounts
                                </div>
                            </div>
                            <div class="portlet-body">
                                <table class="table table-striped table-bordered table-hover" id="">
                                    <thead>
                                    <tr>
                                        <th>
                                            Sr
                                        </th>
                                        <th>
                                            Date
                                        </th>
                                        <th>
                                            Account
                                        </th>
                                        <th>
                                            Opening Balance
                                        </th>
                                        <th>
                                            Received Amount
                                        </th>
                                        <th>
                                            Sent Amount
                                        </th>
                                        <th>
                                            Balance
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    <?php
                                    $i=0;
                                    $opening_balance = 0;
                                    $received = 0;
                                    $sent = 0;
                                    $remaining_balance = 0;
                                    foreach($accounts as $account):
                                        ?>
                                        <tr class="odd gradeX">
                                            <td >
                                                <?php echo $i+1;?>
                                            </td>
                                            <td>
                                                <?php echo $selected_date;?>
                                            </td>
                                            <td>
                                                <?php echo $account['account_name'].' '.$account['account_title']?>
                                            </td>
                                            <td>
                                                <?php
                                                $opening_balance+=$account['opening_balance'];
                                                echo $account['opening_balance'];?>
                                            </td>
                                            <th>
                                                <?php
                                                $received+=$account['received'];
                                                echo $account['received'];?>
                                            </th>
                                            <td>
                                                <?php
                                                $sent += $account['sent'];
                                                echo $account['sent'];?>
                                            </td>
                                            <td>
                                                <?php
                                                $remaining_balance += $account['remaining_balance'];
                                                echo $account['remaining_balance'];?>
                                            </td>
                                        </tr>
                                        <?php
                                        $i++;
                                    endforeach;
                                    ?>
                                    </tbody>
                                    <tfoot>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th><?php echo  $opening_balance;?></th>
                                    <th><?php echo  $received;?></th>
                                    <th><?php echo  $sent;?></th>
                                    <th><?php echo  $remaining_balance;?></th>
                                    </tfoot>
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
                                    <i class="fa fa-list"></i> Bank Account Details
                                </div>
                            </div>
                            <div class="portlet-body">
                                <table class="table table-striped table-bordered table-hover" id="">
                                    <thead>
                                    <tr>
                                        <th>
                                            Sr
                                        </th>
                                        <th>
                                            Account
                                        </th>
                                        <th>
                                            Expense Today
                                        </th>
                                        <th>
                                            Tagged Debit Entries
                                        </th>
                                        <th>
                                            Untagged Debit Entries
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    <?php
                                    $i=0;
                                    foreach($bank_accounts as $bank_account):
                                        ?>
                                        <tr class="odd gradeX">
                                            <td >
                                                <?php echo $i+1;?>
                                            </td>
                                            <td>
                                                <?php echo $bank_account['account_name'].' '.$bank_account['account_title']?>
                                            </td>
                                            <td>
                                                <a href="<?php echo site_url();?>/reports/view_expense_bank_report/<?php echo $selected_date;?>/<?php echo $bank_account['id'];?>/expense"><?php echo $bank_account['expenses']?></a>
                                            </td>
                                            <td>
                                                <?php echo $bank_account['tagged']?>
                                            </td>
                                            <td>
                                                <?php
                                                if ($bank_account['untagged'] > 0)
                                                    $allowed_closing = false;
                                                echo $bank_account['untagged']; ?>
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
                                    <i class="fa fa-list"></i> Today Admissions
                                </div>
                            </div>
                            <div class="portlet-body">
                                <table class="table table-striped table-bordered table-hover" id="admission_table">
                                    <thead>
                                    <tr>
                                        <th>

                                        </th>
                                        <?php
                                            foreach($courses as $course):?>
                                        <th>
                                            <?php echo $course['course_code'];?>
                                        </th>
                                            <?php endforeach; ?>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    <?php
                                    $i=0;
                                    foreach($admissions as $admission):
                                        ?>
                                        <tr class="odd gradeX">
                                            <td>
                                                <?php echo $admission['campus_name'];?>
                                            </td>
                                            <?php
                                            $i=0;
                                            foreach($admission['courses'] as $key=>$course):
                                            ?>
                                            <td>
                                                <?php
                                                $courses[$key]['admissions_count']+=$course['admissions_count'];
                                                if ($course['admissions_count'] > 0): ?>
                                                    <a href="<?php  echo site_url().'/reports/view_admissions_report/'.$selected_date.'/'.$admission['campus_id'].'/'.$course['course_id'];?>" target="_blank"> <?php echo $course['admissions_count']?> </a>
                                                <?php else:
                                                    echo "0";
                                                endif;

                                                endforeach;
                                                ?>
                                            </td>
                                        </tr>
                                        <?php
                                        $i++;
                                    endforeach;
                                    ?>
                                    </tbody>
                                    <tfoot>
                                        <th></th>
                                        <?php  foreach($courses as $course):?>
                                        <th><? echo $course['admissions_count']; ?></th>
                                        <?php endforeach; ?>
                                    </tfoot>
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
                                    <i class="fa fa-list"></i> PayPro Entries
                                </div>
                            </div>
                            <div class="portlet-body">
                                <table class="table table-striped table-bordered table-hover" id="">
                                    <thead>
                                    <tr>
                                        <th>
                                            Untagged Settlements
                                        </th>
                                        <th>
                                            <?php
                                            if (count($settlements) > 0)
                                                $allowed_closing = false;?>
                                            <a href="<?php  echo site_url()?>/excel_import/unpaid_entries" target="_blank"> <?php echo count($settlements)?> </a>
                                        </th>
                                    </tr>
                                    </thead>
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
                                    <i class="fa fa-list"></i> Discounts Today
                                </div>
                            </div>
                            <div class="portlet-body">
                                <table class="table table-striped table-bordered table-hover" id="">
                                    <thead>
                                    <tr>
                                        <th >
                                            Sr
                                        </th>
                                        <th>
                                            Name
                                        </th>
                                        <th>
                                            Roll #
                                        </th>

                                        <th>
                                            Phone No
                                        </th>
                                        <th>
                                            Course
                                        </th>
                                        <th>
                                            Campus
                                        </th>
                                        <th>
                                            Discount
                                        </th>
                                        <th>
                                            Requested By
                                        </th>
                                        </th>
                                        <th>
                                            Reason
                                        </th>

                                    </tr>
                                    </thead>
                                    <tbody>

                                    <?php
                                    $i=0;
                                    foreach($discounts as $list):
                                        ?>
                                        <tr class="odd gradeX">
                                            <td >
                                                <?php echo $i+1;?>
                                            </td>
                                            <td>
                                                <?php echo $list['first_name'].' '.$list['last_name'];?>
                                            </td>
                                            <td>
                                                <?php echo $list['roll_no'];?>
                                            </td>
                                            <th>
                                                <?php echo $list['mobile'];?>
                                            </th>
                                            <td>
                                                <?php echo $list['course_name'];?>
                                            </td>
                                            <td>
                                                <?php echo $list['campus_name'];?>
                                            </td>
                                            <td>
                                                <?php echo $list['discount'];?>
                                            </td>
                                            <td>
                                                <?php echo $list['created_by'];?>
                                            </td>
                                            <td>
                                                <?php echo $list['reason'];?>
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
                                    <i class="fa fa-list"></i> Struck Of Students Today
                                </div>
                            </div>
                            <div class="portlet-body">
                                <table class="table table-striped table-bordered table-hover" id="">
                                    <thead>
                                    <tr>
                                        <th >
                                            Sr
                                        </th>
                                        <th>
                                            Name
                                        </th>
                                        <th>
                                            Roll #
                                        </th>

                                        <th>
                                            Phone No
                                        </th>
                                        <th>
                                            Course
                                        </th>
                                        <th>
                                            Campus
                                        </th>
                                        </th>
                                        <th>
                                            Reason
                                        </th>
                                        <th>
                                            Deleted By
                                        </th>

                                    </tr>
                                    </thead>
                                    <tbody>

                                    <?php
                                    $i=0;
                                    foreach($struck_of as $list):
                                        ?>
                                        <tr class="odd gradeX">
                                            <td >
                                                <?php echo $i+1;?>
                                            </td>
                                            <td>
                                                <?php echo $list['first_name'].' '.$list['last_name'];?>
                                            </td>
                                            <td>
                                                <?php echo $list['roll_no'];?>
                                            </td>
                                            <th>
                                                <?php echo $list['contact_to_no'];?>
                                            </th>
                                            <td>
                                                <?php echo $list['course_name'];?>
                                            </td>
                                            <td>
                                                <?php echo $list['campus_name'];?>
                                            </td>
                                            <td>
                                                <?php echo $list['reason'];?>
                                            </td>
                                            <td>
                                                <?php echo $list['updated_by'];?>
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
                                    <i class="fa fa-list"></i> Revived(Active) Students Today
                                </div>
                            </div>
                            <div class="portlet-body">
                                <table class="table table-striped table-bordered table-hover" id="">
                                    <thead>
                                    <tr>
                                        <th >
                                            Sr
                                        </th>
                                        <th>
                                            Name
                                        </th>
                                        <th>
                                            Roll #
                                        </th>

                                        <th>
                                            Phone No
                                        </th>
                                        <th>
                                            Course
                                        </th>
                                        <th>
                                            Campus
                                        </th>
                                        </th>
                                        <th>
                                            Amount Taken
                                        </th>
                                        <th>
                                            Revive By
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    <?php
                                    $i=0;
                                    foreach($revive_of as $list):
                                        ?>
                                        <tr class="odd gradeX">
                                            <td >
                                                <?php echo $i+1;?>
                                            </td>
                                            <td>
                                                <?php echo $list['first_name'].' '.$list['last_name'];?>
                                            </td>
                                            <td>
                                                <?php echo $list['roll_no'];?>
                                            </td>
                                            <th>
                                                <?php echo $list['mobile'];?>
                                            </th>
                                            <td>
                                                <?php echo $list['course_name'];?>
                                            </td>
                                            <td>
                                                <?php echo $list['campus_name'];?>
                                            </td>
                                            <td>
                                                <?php echo $list['amount'];?>
                                            </td>
                                            <td>
                                                <?php echo $list['add_by'];?>
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
                                    <i class="fa fa-list"></i> Update Student Requests
                                </div>
                            </div>
                            <div class="portlet-body">
                                <table class="table table-striped table-bordered table-hover" id="">
                                    <thead>
                                    <tr>
                                        <th>
                                            Total Student Requests
                                        </th>
                                        <th>
                                            <?php
                                            if ( @$student_requests->total > 0 ){?>
                                                <a href="<?php  echo site_url()?>/dashboard/students_edit_requests" target="_blank"> <?php echo $student_requests->total?> </a>
                                            <?php
                                            }
                                            else
                                                echo 0;
                                                ?>

                                        </th>
                                    </tr>
                                    </thead>
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
                                    <i class="fa fa-list"></i> Update Fee Requests
                                </div>
                            </div>
                            <div class="portlet-body">
                                <table class="table table-striped table-bordered table-hover" id="">
                                <thead>
                                    <tr>
                                        <th class="hidden">
                                            Hidden
                                        </th>
                                        <th>
                                            Request Date
                                        </th>
                                        <th>
                                            Challan #
                                        </th>
                                        <th>
                                            Fee Amount
                                        </th>
                                        <th>
                                            Paid Amount
                                        </th>
                                        <th>
                                            Previous Installment Amount
                                        </th>
                                        <th>
                                            Previous Fine Amount
                                        </th>
                                        <th>
                                            Student / Contractor
                                        </th>
                                        <th>
                                            Dead Line
                                        </th>
                                        <th>
                                            Paid Date
                                        </th>
                                        <th>
                                            Status
                                        </th>
                                        <th>
                                            Fee Type
                                        </th>
                                        <th>
                                            Challan
                                        </th>
                                        <th>
                                            Last Edit
                                        </th>
                                        <th>
                                            Reason
                                        </th>
                                        <th>
                                            System Comment
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        $i=0;
                                        foreach($fee_requests as $fee_request):
                                    ?>
                                        <tr class="<?php if($fee_request['del']==1){echo 'alert-danger';}?>">
                                            <td class="hidden">
                                                <?php echo $i;?>
                                            </td>
                                            <td>
                                                <?php echo $fee_request['update_date'];?>
                                            </td>
                                            <td>
                                                <?php echo $fee_request['challan_no'];?>
                                            </td>
                                            <td>
                                                <?php echo $fee_request['amount'];?>
                                            </td>
                                            <td>
                                                <?php echo $fee_request['actual_amount'];?>
                                            </td>
                                            <td>
                                                <?php echo $fee_request['remaining_installment_amount'];?>
                                            </td>
                                            <td>
                                                <?php echo $fee_request['extra_amount'];?>
                                            </td>
                                            <td><?php echo $fee_request['first_name'].' '.$fee_request['last_name'].' ('.$fee_request['roll_no'].')';?></td>
                                            <td>
                                                <?php 
                                                    if($fee_request['dead_line']!=$fee_request['old_dead_line']):
                                                        echo '<strong>'.$fee_request['dead_line'].'</strong>';
                                                        echo '<hr />';
                                                        echo $fee_request['old_dead_line'];
                                                    else:
                                                        echo $fee_request['dead_line'];
                                                    endif;
                                                ?>    
                                            </td>
                                            <td>
                                                <?php echo $fee_request['paid_date'];?>
                                            </td>
                                            <td>
                                                <?php
                                                    if($fee_request['paid']==1)
                                                    {
                                                        echo 'Paid';
                                                    }
                                                    else
                                                    {
                                                        echo 'Unpaid';
                                                    }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                    if($fee_request['college_fee']==1)
                                                    {
                                                        echo 'College Fee';
                                                    }
                                                    else
                                                    {
                                                        echo 'Bank Fee';
                                                    }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                    if($fee_request['scan_challan']!=''):
                                                ?>
                                                <a href="<?php echo base_url();?>uploads/<?php echo $fee_request['scan_challan'];?>" class="btn purple" target="_blank">Challan</a>
                                                <?php
                                                    endif;
                                                ?>
                                            </td>
                                            <td><?php echo $fee_request['last_edit'];?></td>
                                            <td><?php echo $fee_request['reason'];?></td>
                                            <td>
                                                <?php 
                                                    if($fee_request['del']==1)
                                                    {
                                                        echo 'Delete Payment';
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
                    <div class="col-md-12">
                        <!-- BEGIN EXAMPLE TABLE PORTLET-->
                        <div class="portlet box grey-cascade">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-list"></i> Staff Attendance Today
                                </div>
                            </div>
                            <div class="portlet-body">
                                <table class="table table-striped table-bordered table-hover" id="">
                                    <thead>
                                    <tr>
                                        <th>
                                            Campus
                                        </th>
                                        <th>
                                            Present
                                        </th>

                                        <th>
                                            Absent
                                        </th>
                                        <th>
                                            Total
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    <?php
                                    $i=0;
                                    foreach($campuses_attendance as $list):
                                        ?>
                                        <tr class="odd gradeX">
                                            <td>
                                                <?php echo $list['campus_name'];?>
                                            </td>
                                            <td>
                                                <a href="<?php  echo site_url().'/reports/view_attendance/'.$selected_date.'/'.$list['campus_id'].'/present';?>" target="_blank"><?php echo $list['present'];?></a>
                                            </td>
                                            <th>
                                                <a href="<?php  echo site_url().'/reports/view_attendance/'.$selected_date.'/'.$list['campus_id'].'/absent';?>" target="_blank"><?php echo $list['absents'];?></a>
                                            </th>
                                            <td>
                                                <?php echo $list['present']+$list['absents'];?>
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
                                    <i class="fa fa-list"></i> All Campus Details
                                </div>
                            </div>
                            <div class="portlet-body">
                                <table class="table table-striped table-bordered table-hover" id="">
                                    <thead>
                                    <tr>
                                        <th>
                                            Campus
                                        </th>
                                        <th>
                                            Total Expense
                                        </th>
                                        <th>
                                            Total Recovery
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    <?php
                                    $i=0;
                                    $ttexpense = 0;
                                    $ttrec = 0;
                                    foreach($campuses_attendance as $list):
                                        ?>
                                        <tr class="odd gradeX">
                                            <td>
                                                <?php echo $list['campus_name'];?>
                                            </td>
                                            <td>
                                                <?php $amnt = $this->db->select("sum(amount) as total")->get_where("expenses",array('campus_id' => $list['campus_id']
                                                ,"actual_date >=" => $selected_date.' 00:00:00'
                                                ,"actual_date <=" => $selected_date.' 23:59:59'
                                                ))->row();
                                                if ($amnt) {
                                                    echo '<a href="'.site_url().'/reports/view_all_expense_report/'.$selected_date.'/'.$list['campus_id'].'/single">'.$amnt->total.'</a>';
                                                    $ttexpense += $amnt->total;
                                                }else {
                                                    $ttexpense += $amnt->total;
                                                    echo 0;
                                                }
                                                ?>
                                            </td>
                                            <th>
                                                <?php
                                                        $this->db->select('*');
                                                        $this->db->from('payments');
                                                        $this->db->join('students', 'students.student_id = payments.student_id', 'inner');
                                                        $this->db->join('classes', 'classes.class_id = students.class_id', 'inner');
                                                        $this->db->join('campuses', 'classes.campus_id = campuses.campus_id', 'inner');
                                                        $this->db->where(array('campuses.campus_id' => $list['campus_id']
                                                                                ,"payments.actual_paid_date >=" => $selected_date
                                                                                ,"payments.actual_paid_date <=" => $selected_date
                                                                                ,"payments.paid <=" => 1
                                                        ));
                                                        $this->db->group_by("CASE WHEN payments.merged_challan IS NOT NULL THEN payments.merged_challan ELSE payments.challan_no END",false);
                                                        $tats = $this->db->get()->result_array();
                                                        $tot = 0;
                                                        foreach ($tats as $tat)
                                                            $tot+=$tat['actual_amount'];

                                                        $ttrec += $tot;
                                                        echo $tot;
                                                ?>
                                            </th>
                                        </tr>
                                        <?php
                                        $i++;
                                    endforeach;
                                    ?>
                                    </tbody>
                                    <tfoot>
                                    <th></th>
                                    <th><a href="<?php echo site_url();?>/reports/view_all_expense_report/<?php echo $selected_date;?>/<?php echo $list['campus_id'];?>/all"><?php echo $ttexpense;?></a></th>
                                    <th><a href="<?php echo site_url();?>/reports/view_expense_report/<?php echo $selected_date;?>/<?php echo $Pettycash['id'];?>/all"><?php echo $ttrec;?></a></th>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <!-- END EXAMPLE TABLE PORTLET-->
                    </div>
                </div>
                <button id="print-btn" type="button" class="btn btn-primary btn-sm d-print-none"><i class="dripicons-print"></i> Print</button>
                <?php if ($allowed_closing): ?>
                    <button type="button" class="btn btn-success btn-sm d-print-none"><i class="close"></i> Close Now</button>
                <?php
                    endif;
                    endif;
                ?>
            <!-- Struck of Details-->
		</div>
	</div>
	<!-- END CONTENT -->
<script>
    document.addEventListener( "DOMContentLoaded", function(){
        $("#print-btn").on("click", function(){
            var divToPrint=document.getElementById('quotation-details');
            var newWin=window.open('','Print-Window');
            newWin.document.open();
            newWin.document.write('<link rel="stylesheet" href="<?php echo base_url();?>assets/global/plugins/bootstrap/css/bootstrap.min.css" type="text/css"><style type="text/css">@media print {a[href]:after {content: "";} }</style><body onload="window.print()">'+divToPrint.innerHTML+'</body>');
            newWin.document.close();
        });
    }, false );

</script>