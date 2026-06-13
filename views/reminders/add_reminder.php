
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
								<i class="fa fa-plus"></i> Add Reminder
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/reminders/insert_reminder" enctype="multipart/form-data">
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
									<div class="form-group users_container" style="display:none;">
										<label class="col-md-3 control-label">Users <span class="required">*</span></label>
										<div class="col-md-9 checkbox-list user_ids">
										
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-3 control-label">Reminder Type <span class="required">*</span></label>
										<div class="col-md-9 radio-list">
											<label class="radio-inline">
											<input type="radio" name="type" id="optionsRadios1" value="once" checked> Once </label>
											<label class="radio-inline">
											<input type="radio" name="type" id="optionsRadios2" value="daily"> Daily </label>
											<label class="radio-inline">
											<input type="radio" name="type" id="optionsRadios3" value="weekly"> Weekly </label>
											<label class="radio-inline">
											<input type="radio" name="type" id="optionsRadios4" value="monthly"> Monthly </label>
											<label class="radio-inline">
											<input type="radio" name="type" id="optionsRadios5" value="yearly"> Yearly </label>
										</div>
									</div>
									<div class="form-group type once">
										<label class="control-label col-md-3">Reminder Date <span class="required">*</span></label>
										<div class="col-md-3">
											<div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
												<input type="text" name="once_date" class="form-control" value="<?php echo date('Y-m-d');?>" readonly required />
												<span class="input-group-btn">
												<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
												</span>
											</div>
										</div>
									</div>
									<div class="form-group type daily" style="display:none;">
										<label class="control-label col-md-3"></label>
										<div class="col-md-9">
											Reminder Generate Daily.
										</div>
									</div>
									<div class="form-group type weekly" style="display:none;">
                                        <label class="col-md-3 control-label">Days </label>
                                        <div class="col-md-5">
                                            <select id="select2_sample_modal_2" class="form-control select2" name="weekly_days[]" multiple>
												<option value="Monday">Monday</option>
												<option value="Tuesday">Tuesday</option>
												<option value="Wednesday">Wednesday</option>
												<option value="Thursday">Thursday</option>
												<option value="Friday">Friday</option>
												<option value="Saturday">Saturday</option>
												<option value="Sunday">Sunday</option>
                                            </select>
                                            <!--<span class="help-inline"></span>-->
                                        </div>
                                    </div>
									<div class="form-group type monthly" style="display:none;">
                                        <label class="col-md-3 control-label">Dates </label>
                                        <div class="col-md-5">
                                            <select id="select2_sample_modal_1" class="form-control select2" name="monthly_dates[]" multiple>
												<?php
													for($i=1;$i<=28;$i++):
												?>
												<option value="<?php echo $i?>"><?php echo $i?></option>
												<?php
													endfor;
												?>
                                            </select>
                                            <!--<span class="help-inline"></span>-->
                                        </div>
                                    </div>
									<div class="form-group type yearly" style="display:none;">
                                        <label class="col-md-3 control-label">Date &amp; Month</label>
                                        <div class="col-md-4">
                                            <select class="form-control" name="yearly_date">
												<?php
													for($i=1;$i<=28;$i++):
												?>
												<option value="<?php echo $i?>"><?php echo $i?></option>
												<?php
													endfor;
												?>
                                            </select>
                                            <!--<span class="help-inline"></span>-->
                                        </div>
										<div class="col-md-4">
                                            <select class="form-control" name="yearly_month" >
												<option value="January">January</option>
												<option value="February">February</option>
												<option value="March">March</option>
												<option value="April">April</option>
												<option value="May">May</option>
												<option value="June">June</option>
												<option value="July">July</option>
												<option value="August">August</option>
												<option value="September">September</option>
												<option value="October">October</option>
												<option value="November">November</option>
												<option value="December">December</option>
                                            </select>
                                            <!--<span class="help-inline"></span>-->
                                        </div>
                                    </div>
									
									<div class="form-group">
										<label class="col-md-3 control-label">Note <span class="required">*</span></label>
										<div class="col-md-9">
                                            	<textarea class="form-control wysihtml5" rows="3" name="note"></textarea>
										</div>
									</div>
									
									<div class="form-group">
										<label class="col-md-3 control-label">Image </label>
										<div class="col-md-9">
                                            	<input type="file" name="image"  value="" />
										</div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Add Reminder</button>
											<button onclick="location.href = '<?php echo site_url()?>'" type="button" class="btn default">Cancel</button>
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