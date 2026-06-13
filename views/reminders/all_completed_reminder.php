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
								<i class="fa fa-list"></i>All Completed Reminders
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
									<?php echo $reminder['status'];?>
								</td>
                                <td>
									<?php echo $reminder['add_by']?>
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