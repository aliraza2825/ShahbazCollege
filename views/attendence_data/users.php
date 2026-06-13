
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
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/attendence_data/users">
								<div class="form-body">
									<div class="form-group">
										<label class="col-md-3 control-label">Type <span class="required">*</span></label>
                                        <div class="col-md-3">
                                            <select class="form-control" name="type" id="type">
                                                <option value="">Select Type</option>
                                                <option value="teacher" <?php if(@$this->input->post('type')=='teacher'){echo 'selected=selected';}?>>Teacher</option>
                                                <option value="principal" <?php if(@$this->input->post('type')=='principal'){echo 'selected=selected';}?>>Principal</option>
                                                <option value="accountant" <?php if(@$this->input->post('type')=='accountant'){echo 'selected=selected';}?>>Accountant</option>
                                                <option value="guard" <?php if(@$this->input->post('type')=='guard'){echo 'selected=selected';}?>>Guard</option>
                                                <option value="admin" <?php if(@$this->input->post('type')=='admin'){echo 'selected=selected';}?>>Admin</option>
                                            </select>
                                            <!--<span class="help-inline"></span>-->
                                        </div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Check</button>
											<button onclick="location.href = '<?php echo site_url()?>'" type="button" class="btn default">Cancel</button>
										</div>
									</div>
								</div>
							</form>
						</div>
                        <?php
							if(count($users)>0):
						?>
                        <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover">
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
									<?php echo $user['first_name'].' '.$user['last_name'];?>
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