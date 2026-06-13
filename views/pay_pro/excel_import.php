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

        <div class="row">
            <div class="col-md-12 ">
                <!-- BEGIN SAMPLE FORM PORTLET-->
                <div class="portlet box green ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-user"></i> Upload Paypro Settlement
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <div class="col-md-6">
                            <form method="post"  action="<?php echo site_url();?>/excel_import/import" enctype="multipart/form-data">
                            <div class="form-body row">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Course <span class="required">*</span></label>
                                    <div class="col-md-9">
                                        <input type="file" name="file" id="file" required accept=".xls, .xlsx" /></p>
                                    </div>
                                </div>
                            </div>
                        <input type="submit" name="import" value="Import" class="btn green" />
                    </form>
                        </div>
                        <div class="col-md-6">
                            <form method="post"  action="<?php echo site_url();?>/excel_import/index" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label col-md-3">From Date <span class="required">*</span></label>
                                            <div class="col-md-3">
                                                <div class="input-group input-medium date date-picker" data-date="<?php echo $from_date;?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                    <input type="text" name="from_date" class="form-control" value="<?php echo $from_date;?>" readonly>
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
                                                    <input type="text" name="to_date" class="form-control" value="<?php echo $to_date;?>" readonly>
                                                    <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        <input type="submit" name="import" value="Find" class="btn green" />
                    </form>
                        </div>
                    </div>
                </div>
                <!-- END EXAMPLE TABLE PORTLET-->
            </div>
        </div>

        <div class="row">

            <div class="col-md-12 ">
                <!-- BEGIN SAMPLE FORM PORTLET-->
                <div class="portlet box green ">

                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-user"></i>PayPro Settlements
                        </div>
                    </div>
                    <div class="portlet-body table-responsive">
                        <table class="table table-bordered table-hover" id="sample_2">
                            <thead>
                            <tr>
                                <th class="hidden">
                                    Hidden
                                </th>
                                <th>
                                    Settlement Date
                                </th>
                                <th>
                                    Total Payments Amount
                                </th>
                                <th>
                                    Received Amount
                                </th>
                                <th>
                                    1-LINK Amount
                                </th>
                                <th>
                                    Debit/Credit Card Amount
                                </th>
                                <th>
                                    Deductions Amount
                                </th>
                                <th>
                                    Created Date
                                </th>
                                <th>
                                    Created By
                                </th>
                                <th>
                                    Action
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $i=0;
                            foreach($settlements as $settlement):
                                $stats = $this->db->join('accounts',"accounts.id = bank_reconciliation_statement.account_id")->get_where("bank_reconciliation_statement","bank_reconciliation_statement.paypro_id = '".$settlement['id']."'")->result_array();
                                ?>
                                <tr class="odd gradeX">
                                    <td class="hidden">
                                        <?php echo $i;?>
                                    </td>
                                    <td><?php  echo $settlement ['settlement_date']  ?></td>
                                    <td><?php  echo $settlement ['total_amount']  ?></td>
                                    <td><?php  echo $settlement ['paid_amount']  ?></td>
                                    <td><?php
                                        echo "<strong>".$settlement ['link_amount']." -/PKR</strong><br />";
                                        foreach ($stats as $stat){
                                            //if ((int)str_replace(",","",$stat['credit']) == $settlement ['link_amount'])    {
                                                echo "<strong>Bank Name</strong> : ".$stat['account_title'].' '.$stat['account_name']."<br />".
                                                "<strong>Date</strong> : ".$stat['trans_date']."<br />".
                                                    "<strong>Description </strong>: ".$stat['description']."<br />";
                                            //}
                                        } ?>
                                    </td>
                                    <td><?php
                                        echo "<strong>".$settlement ['card_amount']." -/PKR</strong><br />";
                                        foreach ($stats as $stat){
                                            if ((int)str_replace(",","",$stat['credit']) == $settlement ['card_amount'])    {
                                                echo "<strong>Bank Name</strong> : ".$stat['account_title'].' '.$stat['account_name']."<br />".
                                                    "<strong>Date</strong> : ".$stat['trans_date']."<br />".
                                                    "<strong>Description </strong>: ".$stat['description']."<br />";
                                            }
                                        }
                                    ?></td>
                                    <td><?php  echo $settlement ['total_amount'] - $settlement ['paid_amount']; ?></td>
                                    <td><?php  echo $settlement ['created_at']; ?></td>
                                    <td><?php  echo $settlement ['created_by'];?></td>
                                    <td><a href="<?php echo site_url().'/excel_import/entries/'.$settlement ['id'];?>" class="btn blue"><i class="fa fa-eye"></i></a><br />
                                        <?php if ($this->session->userdata('role')=='Admin'): ?>
                                            <a href="<?php echo site_url().'/excel_import/delete/'.$settlement ['id'];?>" class="btn red"><i class="fa fa-trash"></i></a></td>
                                        <?php endif; ?>
                                </tr>
                                <?php
                                $i++;
                            endforeach;
                            ?>
                            </tbody>
                        </table>
                    </div>


                </div>
                <!-- END SAMPLE FORM PORTLET-->
            </div>

        </div>

        <!-- END PAGE CONTENT-->
    </div>
</div>
<!-- END CONTENT -->