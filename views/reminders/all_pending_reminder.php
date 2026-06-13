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
								<i class="fa fa-list"></i>All Pending Reminders
							</div>
						</div>
						<div class="portlet-body">
							<table class="table table-bordered table-hover" id="sample_3">
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
									Date
								</th>
								<th>
									Note
								</th>
                                <th>
									Image
								</th>
								<th>
									Status
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
								foreach($reminders as $reminder):
							?>
                            <tr class="odd gradeX <?php if($reminder['status']=='Completed'){echo 'success';}?>">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
								<td>
									<?php echo $i;?>
								</td>
                                <td>
									<?php echo $this->db->get_where('users',array('user_id'=>$reminder['user_id']))->row()->first_name;?>
									<?php echo $this->db->get_where('users',array('user_id'=>$reminder['user_id']))->row()->last_name;?>
								</td>
                                <td>
									<?php echo $reminder['date'];?>
								</td>
								<td>
									<?php echo $reminder['note'];?>
								</td>
								<td>
								<?php 
										if(@$reminder['image']):
											if(@$reminder['online_image']==''):
									?>
										<a class="btn green" href="<?php echo base_url().'reminder_images/'.$reminder['image'];?>" target="_blank">Image</a>
									<?php
											else:
									?>
										<a class="btn green" href="<?php echo str_replace($bucket_address,$cloudfront_address,$reminder['online_image']);?>" target="_blank">Image</a>
									<?php
											endif;
										endif;
									?>
								</td>
								<td>
									<?php 
										if($reminder['status']=='Completed')
										{
											echo $reminder['status'].' (Under Review)';
										}
										else
										{
											echo $reminder['status'];
										}
									?>
								</td>
                                <td>
									<?php echo $reminder['add_by']?>
								</td>
                                
								<td>
                                    <?php
										if($reminder['status']=='Completed'):
									?>
									<a href="<?php echo site_url().'/reminders/update/'.$reminder['reminder_id'];?>" title="Okay" class="btn green"><i class="fa fa-check"></i></a>
									<?php
										endif;
									?>
									<a onclick="return confirm('Are you sure you want to delete this reminder?')" href="<?php echo site_url().'/reminders/delete_reminder/'.$reminder['reminder_id'];?>" title="Delete Reminder" class="btn red"><i class="fa fa-trash"></i></a>
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