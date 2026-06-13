
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
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-user"></i> Check Records
							</div>
						</div>
                        <?php
							if(count($users)>0):
						?>
                        <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover" id="sample_10">
							<thead>
							<tr>
								<th class="hidden">
                                	 hidden
                                </th>
								<th>
									 Name
								</th>
                                <th>
									 CNIC
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
								foreach($users as $user):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
								<td>
									<?php echo $user['first_name'].' '.$user['last_name'].' ('.$user['roll_no'].')';?>
								</td>
                                <td>
									<?php echo $user['cnic']?>
								</td>
                                <td>
									<?php echo $user['machine_id']?>
								</td>
                                <td>
                                	<a onclick="return confirm('Are you sure you want to delete this Record?')" href="<?php echo site_url().'/attendence_data/delete/'.$user['id'];?>" class="btn red" title="delete"><i class="fa fa-trash"></i></a>
                                    <a href="<?php echo site_url().'/attendence_data/edit/'.$user['id'];?>" class="btn blue" title="edit"><i class="fa fa-edit"></i></a>
                                </td>
							</tr>
                            <?php
                            	endforeach;
							?>
							</tbody>
							</table>
						</div>
                        <?php
							endif;
						?>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->