
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
                                <i class="fa fa-mobile" aria-hidden="true"></i>
                                Add Miscellaneous Income
                            </div>
                        </div>
                        <div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/accounts/insert_misc_income"  enctype="multipart/form-data">
                                <div class="form-body row">
                                    <div class="form-group">
                                        <label class="control-label col-md-3">Select Account</label>
                                        <div class="form-group col-md-9">
                                            <select class="form-control select2" name="account_id" id="select2_sample1" required>
                                                <?php
                                                foreach($accounts as $campus):
                                                    ?>
                                                    <option value="<?php echo $campus['id'];?>"><?php echo $campus['account_title'].' '.$campus['account_name']?></option>
                                                <?php
                                                endforeach;
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Title <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="text" style="width: 80%;" name="title" class="form-control" value="" required/>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Description <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <textarea name="description" style="width: 80%;" rows="5" class="form-control"></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Amount <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="number" style="width: 80%;" name="amount" class="form-control" value="" required/>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">File <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="file" name="picture" accept="image/png, image/gif, image/jpeg" required>
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-offset-9 col-md-3" style="text-align: right">
                                            <button type="submit" class="btn green">Add</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>
                    <!-- END SAMPLE FORM PORTLET-->
                </div>
            </div>

<!--            <div class="row">-->
<!--                <div class="col-md-12">-->
<!--                    <!-- BEGIN EXAMPLE TABLE PORTLET-->-->
<!--                    <div class="portlet box grey-cascade">-->
<!--                        <div class="portlet-title">-->
<!--                            <div class="caption">-->
<!--                                <i class="fa fa-list"></i> All Products-->
<!--                            </div>-->
<!--                        </div>-->
<!--                        <div class="portlet-body">-->
<!--                            <table class="table table-striped table-bordered table-hover" id="sample_2">-->
<!--                                <thead>-->
<!--                                <tr>-->
<!--                                    <th>-->
<!--                                        Sr.No-->
<!--                                    </th>-->
<!--                                    <th>-->
<!--                                        Account-->
<!--                                    </th>-->
<!--                                    <th>-->
<!--                                        Title-->
<!--                                    </th>-->
<!--                                    <th>-->
<!--                                        Description-->
<!--                                    </th>-->
<!--                                    <th>-->
<!--                                        File-->
<!--                                    </th>-->
<!--                                    <th>-->
<!--                                        Amount-->
<!--                                    </th>-->
<!--                                    <th>-->
<!--                                        Created At-->
<!--                                    </th>-->
<!--                                </tr>-->
<!--                                </thead>-->
<!--                                <tbody>-->
<!--                                --><?php
//                                $i=0;
//                                foreach(@$misc_incomes as $list):
//                                ?>
<!--                                <tr class="odd gradeX">-->
<!--                                    <td>-->
<!--                                        --><?php //echo $i+1;?>
<!--                                    </td>-->
<!--                                    <td>-->
<!--                                        --><?php //echo $list['account_name'];?>
<!--                                    </td>-->
<!--                                    <td>-->
<!--                                        --><?php //echo $list['title'];?>
<!--                                    </td>-->
<!--                                    <td>-->
<!--                                        --><?php //echo $list['description'];?>
<!--                                    </td>-->
<!--                                    <td>-->
<!--                                        <img height="70" src="--><?php //echo base_url();?><!--uploads/--><?php //echo @$list['image'];?><!--" alt="" />-->
<!--                                    </td>-->
<!--                                    <td>-->
<!--                                        --><?php //echo $list['amount'];?>
<!--                                    </td>-->
<!--                                    <td>-->
<!--                                        --><?php //echo date('F d, Y', strtotime($list['created_at']));?>
<!--                                    </td>-->
<!--                                </tr>-->
<!--                                --><?php
//                                $i++;
//                                endforeach;
//                                ?>
<!--                                </tbody>-->
<!--                            </table>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                    <!-- END EXAMPLE TABLE PORTLET-->-->
<!--                </div>-->
<!--            </div>-->
    </div>
</div>
<!-- END CONTENT -->