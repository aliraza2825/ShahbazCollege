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
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet box grey-cascade">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i> Check Paypro Entries
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/excel_import/paypro_entries">
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label col-md-3">From Date <span class="required">*</span></label>
                                            <div class="col-md-3">
                                                <div class="input-group input-medium date date-picker" data-date="<?php echo $from_date;?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                    <input type="text" name="from_date" class="form-control from_date" value="<?php echo $from_date;?>" readonly>
                                                    <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label col-md-3">To Date <span class="required">*</span></label>
                                            <div class="col-md-3">
                                                <div class="input-group input-medium date date-picker" data-date="<?php echo $to_date;?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                    <input type="text" name="to_date" class="form-control to_date" value="<?php echo $to_date;?>" readonly>
                                                    <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <button type="submit" class="btn green check_expense">Check</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- END EXAMPLE TABLE PORTLET-->
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="portlet box grey-cascade">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i> All Paypro Entries
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover" id="sample_2">
                            <thead>
                            <tr>
                                <th class="hidden">
                                    hidden
                                </th>
                                <th>
                                    Student Information
                                </th>
                                <th>
                                    Connect Pay ID
                                </th>
                                <th>
                                    Challan IDs
                                </th>
                                <th>
                                    Fee Amount
                                </th>
                                <th>
                                    Bill URL
                                </th>
                                <th>
                                    Status
                                </th>
                                <th>
                                    Action
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $i = 0;
                            foreach($payments as $payment):
                                ?>
                                <tr class="odd gradeX">
                                    <td class="hidden">
                                        <?php echo $i;?>
                                    </td>
                                    <td>
                                        Name : <?php echo $payment['first_name'].' '.$payment['last_name'];?>
                                        <br />
                                        Father Name : <?php echo $payment['father_name'];?>
                                        <br />
                                        CNIC : <?php echo $payment['cnic'];?>
                                    </td>
                                    <td>
                                        <?php echo $payment['connect_pay_id'];?>
                                    </td>
                                    <td>
                                        <?php echo '<a href="'.site_url('students/payments_paid/'.$payment['student_id']).'">'.$payment['challan_ids'].'</a>';?>
                                    </td>
                                    <td>
                                        <?php echo $payment['order_amount'];?>
                                    </td>
                                    <td>
                                        <a href="<?php echo $payment['bill_url'];?>" class="btn green" target="_blank">Slip</a>
                                    </td>
                                    <td>
                                        <?php echo $payment['transaction_status'];?>
                                    </td>
                                    <td>
                                        <?php
                                            if($payment['transaction_status']=='UNPAID' && $this->session->userdata('role')=='Admin'):
                                        ?>
                                        <a href="<?php echo site_url();?>/excel_import/manual_pay/<?php echo $payment['payment_id'];?>" onclick="return confirm('Are you sure you want to mark this Fee Paid?')"  class="btn green"><i class="fa fa-check"></i> Mark As Paid</a>
                                        <?php
                                            else:
                                        ?>
                                        Paid By <?php echo $payment['updated_by'];?>
                                        <br />
                                        
                                        <a href="<?php echo site_url();?>/excel_import/manual_unpay_transaction/<?php echo $payment['payment_id'];?>" onclick="return confirm('Are you sure you want to mark this Fee UNPaid?')"  class="btn red"><i class="fa fa-cross"></i> Mark As UnPaid</a>
                                        <?php
                                            endif;
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
            </div>
		</div>
        <!-- END PAGE CONTENT-->
    </div>
</div>
<!-- END CONTENT -->