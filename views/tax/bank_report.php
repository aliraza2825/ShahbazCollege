<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
    <div class="page-content">


        <!-- BEGIN PAGE HEADER-->
        <!--<h3 class="page-title">
			Add Class <small>add your desire class</small>
			</h3>-->
        <!-- END PAGE HEADER-->
        <!-- BEGIN PAGE CONTENT-->
        <?php
                $count = 0;
            if(@$this->session->userdata('message')):?>
        <div class="alert alert-success">
            <button class="close" data-close="alert"></button>
            <span>
                <?php echo $this->session->userdata('message');?> </span>
        </div>
        <?php endif;?>
        <div class="row">
            <div class="col-md-12 ">
                <!-- BEGIN SAMPLE FORM PORTLET-->
                <div class="portlet box green ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i> Bank Reconciliation
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form class="form-horizontal" role="form" method="post"
                            action="<?php echo site_url();?>/tax/bank_report" enctype="multipart/form-data">
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="control-label col-md-6">From Date</label>
                                            <div class="col-md-6">
                                                <div class="input-group input-medium date date-picker"
                                                    data-date="<?php echo $from_date; ?>" data-date-format="yyyy-mm-dd"
                                                    data-date-viewmode="years">
                                                    <input type="text" name="from_date" class="form-control"
                                                        value="<?php echo $from_date; ?>" readonly>
                                                    <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i
                                                                class="fa fa-calendar"></i></button>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="control-label col-md-6">To Date</label>
                                            <div class="col-md-6">
                                                <div class="input-group input-medium date date-picker"
                                                    data-date="<?php echo $to_date; ?>" data-date-format="yyyy-mm-dd"
                                                    data-date-viewmode="years">
                                                    <input type="text" name="to_date" class="form-control"
                                                        value="<?php echo $to_date;?>" readonly>
                                                    <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i
                                                                class="fa fa-calendar"></i></button>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="control-label col-md-3">Select Account</label>
                                        <div class="form-group col-md-6">
                                            <select class="form-control select2" name="account_id[]"
                                                id="select2_sample1" multiple required>
                                                <?php
                                                        foreach($accounts as $campus):
                                                            ?>
                                                <option value="<?php echo $campus['id'];?>">
                                                    <?php echo $campus['account_title'].' '.$campus['account_name']?>
                                                </option>
                                                <?php
                                                        endforeach;
                                                        ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <input type="hidden" name="submit" value="1" />
                                        <button type="submit" class="btn green">CHECK</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if(@$yearly_statement): ?>

                <div class="row">
                    <div class="col-md-12">
                        <!-- BEGIN EXAMPLE TABLE PORTLET-->
                        <div class="portlet box grey-cascade">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-list"></i> Yearly Statement Details
                                </div>
                            </div>
                            <div class="portlet-body">
                                <table class="table table-striped table-bordered table-hover" id="sample_2">
                                    <thead>
                                        <tr>
                                            <th class="hidden">
                                                cos
                                            </th>
                                            <th>
                                                Sr.No
                                            </th>
                                            <th>
                                                Account
                                            </th>
                                            <th>
                                                Opening Balance
                                            </th>
                                            <th>
                                                Debit
                                            </th>
                                            <th>
                                                Credit
                                            </th>
                                            <th>
                                                Balance
                                            </th>
                                            
                                            <th>
                                                Debit Own Bank Accounts
                                            </th>
                                            <th>
                                                Credit Own Bank Accounts
                                            </th>
											<th>
                                                Bank Account to Cash Accounts
                                            </th>
                                            <th>
                                                Cash Accounts to Bank Account
                                            </th>
											<th>
                                                Closing Credit in Bank
                                            </th>
											<th>
                                                Expenses
                                            </th>
											<th>
                                                Profit Distribute
                                            </th>
                                            <th>
                                                Un Tagged Debit
                                            </th>
                                            <th>
                                                Un Tagged Credit
                                            </th>
                                            <th>
                                                Action
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php
                                $i=1;
                                foreach($yearly_statement as $statement):
                                ?>
                                        <tr class="odd gradeX">
                                            <td class="hidden">
                                                <?php echo $i+1;?>
                                            </td>
                                            <td>
                                                <?php echo $i+1;?>
                                            </td>
                                            <td>
                                                <?php echo $statement['account_name'];?>
                                            </td>
                                            <td>
                                                <?php echo $statement['opening_balance'];?>
                                            </td>
                                            <td>
                                                <a
                                                    href="<?php echo site_url();?>/tax/view_return_report/<?php echo $from_date;?>/<?php echo $to_date;?>/<?php echo $statement['id'];?>/debit"><?php echo round($statement['debit'],2);?></a>
                                            </td>
                                            <td>
                                                <a
                                                    href="<?php echo site_url();?>/tax/view_return_report/<?php echo $from_date;?>/<?php echo $to_date;?>/<?php echo $statement['id'];?>/credit"><?php echo round($statement['credit'],2);?></a>
                                            </td>
                                            <td>
                                                <?php echo round($statement['credit']-$statement['debit']+$statement['opening_balance'],2);?>
                                            </td>
                                            <td>
                                                <a
                                                    href="<?php echo site_url();?>/tax/view_return_report/<?php echo $from_date;?>/<?php echo $to_date;?>/<?php echo $statement['id'];?>/sent"><?php echo round($statement['sent_own'],2);?></a>
                                            </td>
                                            <td>
                                                <a
                                                    href="<?php echo site_url();?>/tax/view_return_report/<?php echo $from_date;?>/<?php echo $to_date;?>/<?php echo $statement['id'];?>/received"><?php echo round($statement['received_own'],2);?></a>
                                            </td>
											<td>
												<a
                                                    href="<?php echo site_url();?>/tax/view_return_report/<?php echo $from_date;?>/<?php echo $to_date;?>/<?php echo $statement['id'];?>/bank_account_to_cash_accounts"><?php echo round($statement['bank_account_to_cash_account'],2);?></a>
											</td>
											<td>
												<a
                                                    href="<?php echo site_url();?>/tax/view_return_report/<?php echo $from_date;?>/<?php echo $to_date;?>/<?php echo $statement['id'];?>/cash_account_to_bank_accounts"><?php echo round($statement['cash_account_to_bank_account'],2);?></a>
											</td>
											<td>
												<a
                                                    href="<?php echo site_url();?>/tax/view_return_report/<?php echo $from_date;?>/<?php echo $to_date;?>/<?php echo $statement['id'];?>/closing_credit_in_bank"><?php echo round($statement['closing_credit_in_bank'],2);?></a>
											</td>
											<td>
												<a
                                                    href="<?php echo site_url();?>/tax/view_return_report/<?php echo $from_date;?>/<?php echo $to_date;?>/<?php echo $statement['id'];?>/expenses"><?php echo round($statement['expenses'],2);?></a>
											</td>
											<td>
                                                <a
                                                    href="<?php echo site_url();?>/tax/view_return_report/<?php echo $from_date;?>/<?php echo $to_date;?>/<?php echo $statement['id'];?>/profit"><?php echo round($statement['total_profit_given'],2);?></a>
                                            </td>
                                            <td>
                                                <a
                                                    href="<?php echo site_url();?>/tax/view_return_report/<?php echo $from_date;?>/<?php echo $to_date;?>/<?php echo $statement['id'];?>/uncount_debit"><?php echo round($statement['uncount_debit'],2);?></a>
                                            </td>
                                            <td>
                                                <a
                                                    href="<?php echo site_url();?>/tax/view_return_report/<?php echo $from_date;?>/<?php echo $to_date;?>/<?php echo $statement['id'];?>/uncount_credit"><?php echo round($statement['uncount_credit'],2);?></a>
                                            </td>
                                            <td>
                                                <a
                                                    href="<?php echo site_url();?>/tax/view_return_report/<?php echo $from_date;?>/<?php echo $to_date;?>/<?php echo $statement['id'];?>/all"><i
                                                        class="fa fa-eye"></i>view statement</a>
                                                <br />
                                                <a
                                                    href="<?php echo site_url();?>/tax/view_count_return_report/<?php echo $from_date;?>/<?php echo $to_date;?>/<?php echo $statement['id'];?>/all"><i
                                                        class="fa fa-eye"></i> view entry count </a>
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
                <?php endif; ?>
            </div>
        </div>
        <!-- END CONTENT -->
    </div>
</div>