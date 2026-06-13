	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			All Teachers <small>Here you can find all teachers</small>
			</h3>-->
            <!-- BEGIN DASHBOARD STATS -->
			<div class="row">
				<div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
					<div class="dashboard-stat blue-madison">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php echo @$profit[0]['total_submitted_fee']-@$expense[0]['total_expenses'];?>
							</div>
							<div class="desc">
								 Total Profit
							</div>
						</div>
					</div>
				</div>
                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
					<div class="dashboard-stat green-haze">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php echo @$profit[0]['total_submitted_fee'];?>
							</div>
							<div class="desc">
								 Total Income
							</div>
						</div>
					</div>
				</div>
                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
					<div class="dashboard-stat red-intense">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php echo @$expense[0]['total_expenses'];?>
							</div>
							<div class="desc">
								 Total Expense
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- END DASHBOARD STATS -->
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
								<i class="fa fa-money"></i> Profit 
							</div>
						</div>
                        <div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/dashboard/profit">
								<div class="form-body">
                                	<div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label col-md-3">Start Date</label>
                                                <div class="col-md-3">
                                                    <div class="input-group input-medium date date-picker" data-date="<?php echo $start_date;?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                        <input type="text" name="start_date" class="form-control" value="<?php echo $start_date;?>" readonly>
                                                        <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                                    </div>
                                                    <!-- /input-group -->
                                                    <!--<span class="help-block">
                                                    Select date </span>-->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label col-md-3">End Date</label>
                                                <div class="col-md-3">
                                                    <div class="input-group input-medium date date-picker" data-date="<?php echo $end_date;?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                        <input type="text" name="end_date" class="form-control" value="<?php echo $end_date;?>" readonly>
                                                        <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                                    </div>
                                                    <!-- /input-group -->
                                                    <!--<span class="help-block">
                                                    Select date </span>-->
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Date Type</label>
                                                <div class="col-md-6 radio-list">
                                                    <label class="radio-inline">
                                                    <input type="radio" name="date_type" id="optionsRadios4" value="date" <?php if($date_type=='date'){echo 'checked';}?> /> Submit Date </label>
                                                    <label class="radio-inline">
                                                    <input type="radio" name="date_type" id="optionsRadios5" value="actual" <?php if($date_type=='actual'){echo 'checked';}?> /> Upload Date </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Check</button>
										</div>
									</div>
								</div>
                            </form>
                        </div>
						<?php /*?><div class="portlet-body">
							<table class="table table-striped table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
                                <th class="hidden">
									 Hidden
								</th>
                                <th>
									 Title
								</th>
                                <th>
									 Amount
								</th>
                                <th>
									 Date
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=0;
								foreach($expenses as $expense):
							?>
                            <tr class="odd gradeX">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $expense['title'];?>
								</td>
                                <td>
									<?php echo $expense['amount'];?>
								</td>
                                <td>
									<?php echo date('d M, Y', strtotime($expense['date']));?>
								</td>
							</tr>
                            <?php
								$i++;
                            	endforeach;
							?>
							</tbody>
							</table>
						</div><?php */?>
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->