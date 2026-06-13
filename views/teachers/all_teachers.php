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
			<div class="row">
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat blue-madison">
						<div class="visual">
							<i class="icon-users"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php echo count($teachers);?>
							</div>
							<div class="desc">
								 Staff
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
								<i class="fa fa-list"></i>All Staff
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
									 Campus
								</th>
								<th>
									 Staff Type
								</th>
								<th>
									 Department
								</th>
								<th>
									 Designation
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
                                	Role
                                </th>
								<th>
                                	Machine ID
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
									<?php echo $teacher['campus_name']?>
								</td>
								<td>
									<?php echo $teacher['staff_type_name']?>
								</td>
								<td>
									<?php echo $teacher['department_name']?>
								</td>
								<td>
									<?php
                                    $designations = $this->db->where_in('designation_id',explode(',',@$teacher['designation_id']))->get('designations')->result_array();
                                    foreach ($designations as $designation):
                                        echo $designation['designation_name'].",";
                                    endforeach;
                                    ?>
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
									<?php echo $teacher['role']?>
								</td>
								<td>
									<span class="bold"><?php echo $teacher['machine_id'];?></span>
								</td>
								<td>
									<?php
                                    	if(@$myAccess[0]['staff_edit']==1 || $this->session->userdata('role')=='Admin'):
									?>
                                    <a href="<?php echo site_url().'/teachers/edit_teacher/'.$teacher['user_id'];?>" class="btn blue"><i class="fa fa-edit"></i></a>
                                    <?php
                                    	endif;
									?>
                                    <?php
                                    	if(@$myAccess[0]['staff_upload_documents']==1 || $this->session->userdata('role')=='Admin'):
									?>
                                    <a href="<?php echo site_url().'/teachers/upload_documents/'.$teacher['user_id'];?>" class="btn green"><i class="fa fa-image"></i></a>
                                    <?php
                                    	endif;
									?>
                                    <?php
                                    	if(@$myAccess[0]['staff_delete']==1 || $this->session->userdata('role')=='Admin'):
									?>
                                    <a onclick="return confirm('Are you sure you want to delete this Teacher?')" href="<?php echo site_url().'/teachers/delete/'.$teacher['user_id'];?>" class="btn red"><i class="fa fa-trash"></i></a>
                                    <?php
                                    	endif;
									?>
                                    <?php
                                    	if(@$myAccess[0]['staff_attendence']==1 || $this->session->userdata('role')=='Admin'):
									?>
                                    <a href="<?php echo site_url().'/teachers/check_attendence/'.$teacher['user_id'];?>" class="btn purple"><i class="fa fa-pie-chart" title="Check Attendence"></i></a>
                                    <?php
                                    	endif;
									?>
                                    <?php
                                    	if($this->session->userdata('role')=='Admin' || @$myAccess[0]['staff_attendence']==1):
									?>
                                    <a href="<?php echo site_url().'/teachers/check_timing/'.$teacher['user_id'];?>" class="btn yellow"><i class="fa fa-clock-o" title="Check Timing"></i></a>
                                    <?php
                                    	endif;
									?>
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