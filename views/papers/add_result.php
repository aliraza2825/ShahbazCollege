
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
								<i class="fa fa-plus"></i> Add Result
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/papers/insert_result/<?php echo $this->uri->segment(3);?>">
								<div class="form-body">
									<div class="col-md-12">
                                        <h2>Paper Details</h2>
                                        <hr />
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Total Marks <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="number" class="form-control input-inline input-medium" name="total_marks" placeholder="Enter paper total marks" value="" required>
                                            <span class="help-inline"></span>
                                        </div>
									</div>
                                    <table class="table table-striped table-bordered table-hover">
                                    	<thead>
                                        	<tr>
                                            	<th>Student</th>
                                                <th>Obtain Marks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        	<?php
                                            	foreach($students as $student):
											?>
                                            <tr>
                                            	<td><?php echo $student['first_name'].' '.$student['last_name'].' ('.$student['roll_no'].')';?></td>
                                                <td>
                                                	<input type="text" class="form-control input-inline input-medium" name="marks[]" placeholder="Enter obtain marks" value="">
                                                    <input type="hidden" name="student_id[]" value="<?php echo $student['student_id'];?>" />
                                                </td>
                                            </tr>
                                            <?php 
												endforeach;
											?>
                                        </tbody>
                                    </table>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                            <button type="submit" class="btn green">Add Result</button>
											<button onclick="location.href = '<?php echo site_url();?>/papers/all_papers'" type="button" class="btn default">Cancel</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->