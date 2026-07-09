
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
								<i class="fa fa-plus"></i> Add Closing Rules
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/rules/add_closing_rule" enctype="multipart/form-data">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control campus" name="campus_id" required>
                                                <option value="">Select Campus</option>
												<?php
													foreach($campuses as $campus):
												?>
                                                <option value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name'];?></option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                            <!--<span class="help-inline"></span>-->
                                        </div>
                                    </div>

										<div class="form-group">
											<label class="col-md-3 control-label">Select Closing Account</label>
											<div class="col-md-9">
												<select class="form-control input-large campus" name="account_id" required>
                                                    <option value="">Select Account</option>
                                                    <?php
                                                    foreach($accounts as $account):
                                                        ?>
                                                        <option value="<?php echo $account['id'];?>"><?php echo $account['account_name'].' '.$account['account_title'];?></option>
                                                    <?php
                                                    endforeach;
                                                    ?>
												</select>
												<!--<span class="help-inline"></span>-->
											</div>
										</div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Add Rule</button>
											<button onclick="location.href = '<?php echo site_url()?>'" type="button" class="btn default">Cancel</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>

			<div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i>Campus Rules
							</div>
						</div>
						<div class="portlet-body">
							<table class="table table-bordered table-hover table-responsive" id="sample_2">
							<thead>
							<tr>
                                <th class="hidden">
									 Hidden
								</th>
                                <th>
                                	Campus
                                </th>
								<th>
                                	Closing Account
                                </th>

								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=0;
								foreach($closing_rules as $closing_rule):
							?>
                            <tr class="odd gradeX">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $closing_rule['campus_name']?>
								</td>
                                <td>
									 <?php echo $closing_rule['account_name'];?>
								</td>
								<td>
									
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