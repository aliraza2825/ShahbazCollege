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
                    <!-- END EXAMPLE TABLE PORTLET-->
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
