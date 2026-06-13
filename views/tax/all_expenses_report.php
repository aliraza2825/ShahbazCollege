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
                            <i class="fa fa-list"></i>All Expenses
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/tax/expense_report_college_headwise">
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label col-md-3">From Date <span class="required">*</span></label>
                                            <div class="col-md-3">
                                                <div class="input-group input-medium date date-picker" data-date="<?php if(@$this->input->post('from_date')){echo $this->input->post('from_date');}else{echo date('Y-m-d');}?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                    <input type="text" name="from_date" class="form-control" value="<?php if(@$this->input->post('from_date')){echo $this->input->post('from_date');}else{echo date('Y-m-d');}?>" readonly>
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
                                                <div class="input-group input-medium date date-picker" data-date="<?php if(@$this->input->post('to_date')){echo $this->input->post('to_date');}else{echo date('Y-m-d');}?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                    <input type="text" name="to_date" class="form-control" value="<?php if(@$this->input->post('to_date')){echo $this->input->post('to_date');}else{echo date('Y-m-d');}?>" readonly>
                                                    <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">Campus</label>
                                            <div class="col-md-9">
                                                <select name="campus_ids[]" id="select2_sample4" class="form-control input-inline input-large select2" multiple>
                                                    <?php
                                                    foreach($campuses as $campus):
                                                        ?>
                                                        <option value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name'];?></option>
                                                    <?php
                                                    endforeach;
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">Categories</label>
                                            <div class="col-md-9">
                                                <select name="categories[]" id="select2_sample1" class="form-control input-inline input-large select2" multiple>
                                                    <?php
                                                    foreach($categories as $campus):
                                                        ?>
                                                        <option value="<?php echo $campus['expense_category_id'];?>"><?php echo $campus['name'];?></option>
                                                    <?php
                                                    endforeach;
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <button type="submit" class="btn green">Check Expense</button>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>


                    <?php
                    if(@count(@$expenses)>0 ):
                        ?>
                        <div class="portlet-body">
                            <div class="alert alert-success">

                            </div>
                            <table class="table table-striped table-bordered table-hover" id="sample_2">
                                <thead>
                                <tr>
                                    <th>
                                        Sr
                                    </th>
                                    <th>
                                        Campus
                                    </th>
                                    <th>
                                        Category
                                    </th>

                                    <th>
                                        Total Expense
                                    </th>
                                    <th>
                                        View
                                    </th>

                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $i = 1;
                                $total=0;
                                foreach($expenses as $expense):
                                    ?>
                                    <tr class="odd gradeX">
                                        <td >
                                            <?php echo $i;?>
                                        </td>
                                        <td>
                                            <?php echo $expense['campus_name']?>
                                        </td>

                                        <td>
                                            <?php

                                            echo $expense['name'];

                                            ?>
                                        </td>
                                        <td>
                                            <?php echo $expense['total_amount'];
                                            $total+=$expense['total_amount'];
                                            ?>
                                        </td>

                                        <td>
                                            <a href="<?php echo site_url().'/tax/all_expenses_details/'.$from_date.'/'.$to_date.'/'.$expense['campus_id'].'/'.$expense['expense_category_id'];?>" target="_blank">
                                                <button type="button" class="btn btn-default"><i class="fa fa-eye"></i> View Details</button>
                                            </a>
                                        </td>

                                    </tr>
                                    <?php
                                    $i++;
                                endforeach;
                                ?>

                                <tr>
                                    <th>

                                    </th>
                                    <th>

                                    </th>
                                    <th>

                                    </th><th>
                                        Total Amount
                                    </th>

                                    <th>
                                        <?php echo $total;
                                        ?>
                                    </th>

                                </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php
                    endif;
                    ?>
                </div>
                <!-- END EXAMPLE TABLE PORTLET-->
            </div>
        </div>
        <!-- END PAGE CONTENT-->
    </div>
</div>
<!-- END CONTENT -->