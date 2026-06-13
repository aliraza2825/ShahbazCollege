<?php
	$myAccess = checkUserAccess();
?>	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			All Teachers <small>Here you can find all teachers</small>
			</h3>-->
            <!-- BEGIN DASHBOARD STATS -->
			<!--<div class="row">
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat blue-madison">
						<div class="visual">
							<i class="fa fa-graduation-cap"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php //echo count($students);?>
							</div>
							<div class="desc">
								 Students
							</div>
						</div>
						<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
			</div>-->
			<!-- END DASHBOARD STATS -->
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
								<i class="fa fa-list"></i>All Reminder Rules
							</div>
						</div>
						<div class="portlet-body">
							<table class="table table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
                                <th class="hidden">
									Hidden
								</th>
								<th>
									Sr No.
								</th>
                                <th>
                                	Name
                                </th>
                                <th>
									Type
								</th>
								<th>
									Reminder Alert ON
								</th>
								<th>
									Note
								</th>
                                <th>
									Image
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
								$i=1;
								foreach($reminder_rules as $reminder_rule):
							?>
                            <tr class="odd gradeX">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
								<td>
									<?php echo $i;?>
								</td>
                                <td>
									<?php echo $this->db->get_where('users',array('user_id'=>$reminder_rule['user_id']))->row()->first_name;?>
									<?php echo $this->db->get_where('users',array('user_id'=>$reminder_rule['user_id']))->row()->last_name;?>
								</td>
                                <td>
									 <?php echo $reminder_rule['type'];?>
								</td>
                                <td>
									<?php
										//ON ONCE REMINDER
										if($reminder_rule['type']=='once')
										{
											echo $reminder_rule['once_date'];
										}
										//ON WEEKLY REMINDER
										if($reminder_rule['type']=='weekly')
										{
											echo $reminder_rule['weekly_days'];
										}
										//ON MONTHLY REMINDER
										if($reminder_rule['type']=='monthly')
										{
											echo $reminder_rule['monthly_dates'];
										}
										//ON YEARLY REMINDER
										if($reminder_rule['type']=='yearly')
										{
											echo $reminder_rule['yearly_date'].' '.$reminder_rule['yearly_month'];
										}
									?>
								</td>
								<td>
									<?php echo $reminder_rule['note'];?>
								</td>
								<td>
									<?php 
										if(@$reminder_rule['image']):
									?>
										<a class="btn green" href="<?php echo base_url().'reminder_images/'.$reminder_rule['image'];?>" target="_blank">Image</a>
									<?php
										endif;
									?>
								</td>
                                <td>
									<?php echo $reminder_rule['add_by']?>
								</td>
                                
								<td>
                                    <a onclick="return confirm('Are you sure you want to delete this reminder rule?')" href="<?php echo site_url().'/reminders/delete_reminder_rule/'.$reminder_rule['reminder_id'];?>" title="Delete Reminder Rule" class="btn red"><i class="fa fa-trash"></i></a>
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