
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
								<i class="fa fa-edit"></i> Edit Attendence Record
							</div>
						</div>
						<div class="portlet-body form">
                            <?php
                            	foreach($users as $user):
							?>
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/attendence_data/update/<?php echo $user['id'];?>">
								<div class="form-body">
									<div class="form-group">
										<label class="col-md-3 control-label">Type <span class="required">*</span></label>
                                        <div class="col-md-3">
                                            <p class="form-control-static"><?php echo $user['type'];?></p>
                                        </div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Teacher/Admin/Student <span class="required">*</span></label>
                                        <div class="col-md-3">
                                            <p class="form-control-static">
												<?php 
													if($user['type']!='student'):
														echo $this->db->get_where('users', array('user_id'=>$user['teacher_student_id']))->row()->first_name.' ';
														echo $this->db->get_where('users', array('user_id'=>$user['teacher_student_id']))->row()->last_name;
													else:
														echo $this->db->get_where('students', array('student_id'=>$user['teacher_student_id']))->row()->first_name.' ';
														echo $this->db->get_where('students', array('student_id'=>$user['teacher_student_id']))->row()->last_name;
													endif;
												?>
												<?php //echo $user['teacher_student_id'];?>
                                            </p>
                                        </div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Machine ID </label>
										<div class="col-md-9">
											<input type="number" class="form-control input-inline input-medium" name="machine_id" placeholder="Enter machine ID" value="<?php echo $user['machine_id'];?>">
											<span class="help-inline"></span>
										</div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                        	<input type="hidden" name="id" value="<?php echo $user['id'];?>" />
											<button type="submit" class="btn green">Update Record</button>
											<button onclick="location.href = '<?php echo site_url()?>'" type="button" class="btn default">Cancel</button>
										</div>
									</div>
								</div>
							</form>
                            <?php
                            	endforeach;
							?>
						</div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->