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
								<i class="fa fa-list"></i> Manage Campuses
							</div>
						</div>
						<div class="portlet-body">
							<table class="table table-bordered table-hover" id="sample_3">
							<thead>
							<tr>
                                <th class="hidden">
									Hidden
								</th>
								<th class="hidden">
									Hidden
								</th>
								<th>
									Sr No.
								</th>
                                <th>
                                	Campus Name
                                </th>
								<th>
                                	Google Map Link
                                </th>
								<th>
                                	Campus Image
                                </th>
                                <th>
									Status For Mobile App
								</th>
                                <th>
									Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=1;
								foreach($campuses as $campus):
							?>
                            <tr class="odd gradeX">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
								<td class="hidden">
                                	<?php echo $i;?>
                                </td>
								<td>
									<?php echo $i;?>
								</td>
                                <td>
									<?php echo $campus['campus_name']?>
								</td>
								<td>
									<?php echo $campus['google_map_link']?>
								</td>
								<td>
									<?php
										if($campus['campus_image']!='')
										{
											echo '<img src="'.$campus['campus_image'].'" width="100" />';
										}
									?>
								</td>
                                <td>
									 <?php
									 	if($campus['mobile_status']==1)
										{
											echo '<button type="button" class="btn green">Active</button>';
										}
										else
										{
											echo '<button type="button" class="btn red">Deactive</button>';
										}
									 ?>
								</td>
								<td>
                                    <a href="<?php echo site_url().'/mobile_app/edit_campus/'.$campus['campus_id'];?>" title="Edit Campus" class="btn blue"><i class="fa fa-edit"></i></a>
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