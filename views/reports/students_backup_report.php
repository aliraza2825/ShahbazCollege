
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Edit Profile <small>You can edit your profile here</small>
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
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-calendar"></i> Select Backup Date
							</div>
						</div>
						<?php
							$date1=date_create("2023-11-10");
							$date2=date_create(date('Y-m-d'));
							$diff=date_diff($date2,$date1);
							$backupstart_date = $diff->format("%R%ad");
						?>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/reports/students_backup_report">
								<div class="form-body">
									<div class="row">
										<div class="col-md-12">
											<div class="form-group">
												<label class="control-label col-md-3">BackUp Date <span class="required">*</span></label>
												<div class="col-md-3">
													<div class="input-group input-medium date date-picker" data-date="<?php echo $backup_date;?>" data-date-format="yyyy-mm-dd" data-date-start-date="<?php echo $backupstart_date;?>" data-date-viewmode="years">
														<input type="text" name="backup_date" class="form-control" value="<?php echo $backup_date;?>" readonly>
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
											<button type="submit" class="btn green">Check Backup</button>
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
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-refresh"></i> Classes Backup
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
									 ID
								</th>
                                <th>
									 Campus
								</th>
								<th>
									 Course
								</th>
								<th>
									 Class Name
								</th>
								<th>
									 Status
								</th>
								<th>
									 Last Backup Date
								</th>
								<th>
									 Download
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i = 1;
								foreach($classes as $class):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $i;?>
								</td>
                                <td>
									 <?php echo $class['campus_name']?>
								</td>
								<td>
									 <?php echo $class['course_name']?>
								</td>
								<td>
									<?php echo $class['name']?>
								</td>
								<td>
									<?php
										if($class['status']==1)
										{
											echo '<button type="button" class="btn green">Active</button>';
										}
										else
										{
											echo '<button type="button" class="btn red">Inactive</button>';
										}
									?>
								</td>
								<td>
									<?php echo date('d M, Y h:i:s A',strtotime($class['backup_time']));?>
								</td>
								<td>
									<?php
										$filename = $class['name'].'('.date('Y-m-d').').csv';
										if($class['backup_date']==date('Y-m-d'))
										{
											echo '<a href="https://shahbazcollegebucket.s3.ca-central-1.amazonaws.com/backup/'.$backup_date.'/'.str_replace(' ','%2520',$class['name']).'('.$backup_date.').csv" target="_blank" class="btn green">Download</a>';
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
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->