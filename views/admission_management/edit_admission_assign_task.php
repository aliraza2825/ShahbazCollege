
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE CONTENT-->
            <?php if(@$this->session->userdata('message')):?>
                <div class="alert alert-success">
                    <button class="close" data-close="alert"></button>
                    <span>
                    <?php echo $this->session->userdata('message');?> </span>
                </div>
            <?php endif;?>
			<div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-edit"></i> Edit Admission Assign Task
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/admission_management/update_comission/<?php echo $this->uri->segment(3);?>">
								<div class="form-body">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <h4><?php echo $users[0]['campus_name'];?></h4>
                                        </div>
									</div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Department <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <h4><?php echo $users[0]['department_name'];?></h4>
                                        </div>
									</div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Designation <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <h4><?php echo $users[0]['designation_name'];?></h4>
                                        </div>
									</div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">User <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <h4><?php echo $users[0]['first_name'].' '.$users[0]['last_name'];?></h4>
                                        </div>
									</div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Assign Campuses <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control select2" id="select2_sample1" name="campus_ids[]" multiple>
                                                <?php 
                                                    foreach($campuses as $campus):
                                                ?>
                                                <option value="<?php echo $campus['campus_id'];?>" <?php if(in_array($campus['campus_id'], explode(',',@$users[0]['campus_ids']))){echo 'selected';}?>>
                                                    <?php echo $campus['campus_name'];?>
                                                </option>
                                                <?php
                                                    endforeach;
                                                ?>
                                            </select>
                                            <!--<span class="help-inline"></span>-->
                                        </div>
                                    </div>

									<div class="row">
										<div class="col-md-12">
											<hr />
											<h4>ASSIGN COMISSION ON PENCENTAGE</h4>
											<hr />
										</div>
									</div>
									<div class="comission_area">
										<?php
											$rules = $this->db->get_where('admission_management_rules',array('admission_incentive_id'=>$users[0]['incentive_id']))->result_array();
											foreach($rules as $rule)
											{
												echo '<div class="comission"><div class="row"><div class="col-md-3"><div class="form-group"><label class="col-md-6 control-label">From (%) <span class="required">*</span></label><div class="col-md-6"><input type="text" class="form-control" name="from_percentage[]" placeholder="From %" value="'.$rule['start'].'" required><span class="help-inline"></span></div></div></div><div class="col-md-3"><div class="form-group"><label class="col-md-5 control-label">To (%) <span class="required">*</span></label><div class="col-md-7"><input type="text" class="form-control" name="to_percentage[]" placeholder="To %" value="'.$rule['end'].'" required><span class="help-inline"></span></div></div></div><div class="col-md-3"><div class="form-group"><label class="col-md-5 control-label">Comission per sale <span class="required">*</span></label><div class="col-md-7"><input type="text" class="form-control" name="comission[]" placeholder="Comission" value="'.$rule['comission'].'" required><span class="help-inline"></span></div></div></div><div class="col-md-3"><button type="button" class="btn red remove_line"><i class="fa fa-trash"></i> Remove</button></div></div></div>';
											}
										?>
									</div>
									<button type="button" class="btn green add_line"><i class="fa fa-plus"></i> Add Rule</button>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Update Task</button>
											<button onclick="location.href = '<?php echo base_url();?>'" type="button" class="btn default">Cancel</button>
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