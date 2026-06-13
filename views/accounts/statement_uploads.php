
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
                <div class="col-md-12">
                    <div class="portlet box green ">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-list"></i> Bank Reconciliation
                            </div>
                        </div>
                        <div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/accounts/statements_record" enctype="multipart/form-data">
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="control-label col-md-6">From Date</label>
                                                <div class="col-md-6">
                                                    <div class="input-group input-medium date date-picker" data-date="<?php echo $from_date; ?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                        <input type="text" name="from_date" class="form-control" value="<?php echo $from_date; ?>" readonly>
                                                        <span class="input-group-btn">
                                                    <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="control-label col-md-6">To Date</label>
                                                <div class="col-md-6">
                                                    <div class="input-group input-medium date date-picker" data-date="<?php echo $to_date; ?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                        <input type="text" name="to_date" class="form-control" value="<?php echo $to_date;?>" readonly>
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
                                            <input type="hidden" name="submit" value="1" />
                                            <button type="submit" class="btn green">CHECK</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- BEGIN EXAMPLE TABLE PORTLET-->
                    <div class="portlet box grey-cascade">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-list"></i> Statement Upload History
                            </div>
                        </div>
                        <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover" id="sample_2">
                                <thead>
                                <tr>
                                    <th>
                                        Sr.No
                                    </th>
                                    <th>
                                        Date
                                    </th>
                                    <th>
                                        Account
                                    </th>
                                    <th>
                                        File
                                    </th>
                                    <th>
                                        Created At
                                    </th>
									<th>
                                        Add By
                                    </th>
                                    <th>
                                        Action
                                    </th>
                                </tr>
                                </thead>
                                <tbody>

                                <?php
                                $i=0;
                                foreach($upload_records as $list):
                                ?>
                                <tr class="odd gradeX">
                                    <td>
                                        <?php echo $list['id'];?>
                                    </td>
                                    <td>
                                        <?php echo $list['date'];?>
                                    </td>
                                    <td>
                                        <?php echo $list['account_name'];?>
                                    </td>
                                    <td>
                                        <?php
                                            if ($list['file'] != "")
                                                echo '<a href="'.base_url().'statements/'.$list['file'].'" target="_blank">'.$list['file'].'</a>';?>
                                    </td>
                                    <td>
                                        <?php echo date('F d, Y', strtotime($list['created_at']));?>
                                    </td>
									<td>
										<?php echo $list['add_by'];?>
									</td>
                                    <td>
                                        <a href="<?php echo site_url().'/Accounts/delete_statement/'.$list['id'];?>" class="btn red"><i class="fa fa-trash"></i></a>
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
<!-- END CONTENT -->