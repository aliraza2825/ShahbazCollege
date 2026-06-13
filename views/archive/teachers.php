	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			All Teachers <small>Here you can find all teachers</small>
			</h3>-->
            <!-- BEGIN DASHBOARD STATS -->
			<div class="row">
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat blue-madison">
						<div class="visual">
							<i class="icon-users"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php echo $count;?>
							</div>
							<div class="desc">
								 Teachers
							</div>
						</div>
						<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
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
								<i class="fa fa-list"></i>All Teachers
							</div>
						</div>
						<div class="portlet-body">
							<table class="table table-striped table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
                                <th>
									 ID
								</th>
								<th>
									 Name
								</th>
                                <th>
									 CNIC
								</th>
                                <th>
									 Email
								</th>
                                <th>
									 Username
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								foreach($teachers as $teacher):
							?>
                            <tr class="odd gradeX">
                                <td>
									 <?php echo $teacher['user_id'];?>
								</td>
								<td>
									<?php echo $teacher['first_name'].' '.$teacher['last_name']?>
								</td>
                                <td>
									<?php echo $teacher['cnic']?>
								</td>
                                <td>
									<?php echo $teacher['email']?>
								</td>
                                <td>
									<?php echo $teacher['username']?>
								</td>
								<td>
									<a href="<?php echo site_url().'/teachers/edit_teacher/'.$teacher['user_id'];?>" class="btn blue"><i class="fa fa-edit"></i></a>
                                    <a href="<?php echo site_url().'/archive/restore_teacher/'.$teacher['user_id'];?>" class="btn green"><i class="fa fa-refresh"></i></a>
                                    <a onclick="return confirm('Are you sure you want to delete this Teacher permanently?')" href="<?php echo site_url().'/archive/delete_teacher/'.$teacher['user_id'];?>" class="btn red"><i class="fa fa-trash"></i></a>
								</td>
							</tr>
                            <?php
								//$i++;
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