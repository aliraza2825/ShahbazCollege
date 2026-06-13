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
                        <table class="table table-bordered table-hover" id="sample_ali">
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
                                ?>
                                <tr class="odd gradeX">
                                    <td class="hidden">
                                        <?php echo $i;?>
                                    </td>
                                    <td><?php  echo $settlement ['settlement_date']  ?></td>
                                    <td><?php  echo $settlement ['total_amount']  ?></td>
                                    <td><?php  echo $settlement ['paid_amount']  ?></td>
                                    <td><?php  echo $settlement ['total_amount'] - $settlement ['paid_amount']  ?></td>
                                    <td><?php  echo $settlement ['created_at']  ?></td>
                                    <td><?php  echo $settlement ['created_by']  ?></td>
                                    <td><a href="<?php echo site_url().'/excel_import/entries/'.$settlement ['id'];?>" class="btn blue"><i class="fa fa-eye"></i></a></td>
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

