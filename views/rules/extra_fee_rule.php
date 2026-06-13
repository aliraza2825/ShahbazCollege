
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
								<i class="fa fa-money"></i> Extra Fee Rules
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/rules/add_extra_fee_rules" enctype="multipart/form-data">
								<div class="form-body">
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                            <div class="col-md-5">
                                                <select class="form-control campus" name="campus_id">
                                                    <option value="">SELECT CAMPUS</option>
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
                                            <label class="col-md-3 control-label">Course <span class="required">*</span></label>
                                            <div class="col-md-5">
                                                <select class="form-control course" name="course_id">
                                                </select>
                                                <!--<span class="help-inline"></span>-->
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3">Last Date of auto fee installment</label>
                                            <div class="col-md-3">
                                                <div class="input-group input-large date date-picker" data-date="<?php echo @$feerule['last_date']?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                    <input type="text" name="last_date" class="form-control" value="<?php echo @$feerule['last_date']?>" readonly>
                                                    <span class="input-group-btn">
                                                    <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">Total Fee <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <input type="number" class="form-control input-inline input-large" name="total_fee" id="total_fee" placeholder="Enter Total Fee" value="<?php echo @$feerule['total_fee'] ?>" min="0" <?php if (@$feerule['total_fee']) echo "readonly";?>  required>
                                                <span class="help-inline"></span>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">Installment on Admission <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <input type="number" class="form-control input-inline input-large" name="installment_on_admission" id="installment_on_admission" placeholder="Enter Installment on Admission" value="<?php echo @$feerule['installment_on_admission'] ?>" min="0" required>
                                                <span class="help-inline"></span>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">Per Installment Fee <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <input type="number" class="form-control input-inline input-large" name="per_installment_fee" id="per_installment_fee" placeholder="Enter Per Installment Fee" value="<?php echo @$feerule['per_installment_fee'] ?>" min="0" readonly required>
                                                <span class="help-inline"></span>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">No of Installments <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <input type="number" class="form-control input-inline input-large" name="no_of_installment" id="no_of_installment" placeholder="Enter no of Installments" value="<?php echo @$feerule['no_of_installments'] ?>" min="0"  required>
                                                <span class="help-inline"></span>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">Difference in installment (months) <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <input type="number" class="form-control input-inline input-large" name="difference_in_installments_months" id="difference_in_installments_months" placeholder="Difference in installment (months)" min="0" value="<?php echo @$feerule['difference_in_installments_months'] ?>" required>
                                                <span class="help-inline"></span>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">Paid date of each installment <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <input type="number" class="form-control input-inline input-large" name="paid_date_each_installment" placeholder="Paid date of each installment" min="1" max="30" value="<?php echo @$feerule['paid_date_each_installment'] ?>" required>
                                                <span class="help-inline"></span>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">Late fee per day fine <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <input type="number" class="form-control input-inline input-large" name="late_fee_per_day_fine" placeholder="Late fee per day fine" min="0" value="<?php echo @$feerule['late_fee_per_day_fine'] ?>" required>
                                                <span class="help-inline"></span>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                                <label class="col-md-3 control-label">Holiday Fine Remove</label>
                                                <div class="col-md-9 radio-list">
                                                <label class="radio-inline">
                                                <input type="radio" name="holiday_fine_remove" id="optionsRadios1" value="Yes" checked> Yes </label>
                                                <label class="radio-inline">
                                                <input type="radio" name="holiday_fine_remove" id="optionsRadios2" value="No"> No </label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                                <label class="col-md-3 control-label">Council/board Fee</label>
                                                <div class="col-md-9 radio-list">
                                                <label class="radio-inline">
                                                <input type="radio" name="council_board_fee" id="optionsRadios3" value="Yes" checked> Yes </label>
                                                <label class="radio-inline">
                                                <input type="radio" name="council_board_fee" id="optionsRadios4" value="No"> No </label>
                                            </div>
                                        </div>
                                        <div class="councli_date_fee">
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">First Time Council Fee <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <input type="number" class="form-control input-inline input-large" name="first_time_council_fee" placeholder="Enter First Time Council Fee" value="<?php echo @$feerule['first_time_council_fee'] ?>" min="0" required>
                                                <span class="help-inline"></span>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3">Last Date of Council Fee</label>
                                            <div class="col-md-3">
                                                <div class="input-group input-large date date-picker" data-date="<?php echo @$feerule['last_date_council_fee']?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                    <input type="text" name="last_date_council_fee" class="form-control" value="<?php echo @$feerule['last_date_council_fee']?>" readonly>
                                                    <span class="input-group-btn">
                                                    <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label col-md-3">Per installment Discount in % on merge</label>
                                            <div class="col-md-9">
                                                <input type="number" class="form-control input-inline input-large" name="disc_per_inst" placeholder="Enter Discount per Fee" value="<?php echo @$feerule['disc_per_inst'] ?>" min="0" required>
                                                <span class="help-inline"></span>
                                            </div>
                                            </div>
                                        </div>
										
										 <div class="form-group">
                                            <label class="control-label col-md-3">Maximum Discount on fee merge in %</label>
                                            <div class="col-md-3">
                                                <input type="number" class="form-control input-inline input-large" name="max_discount_merge" placeholder="Max discount" min="0" value="<?php echo @$feerule['max_discount_merge'] ?>" required>
                                                <span class="help-inline"></span>

                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label col-md-3">Max installment can increase by user</label>
                                            <div class="col-md-9">
                                                <input type="number" class="form-control input-inline input-large" name="max_install_extend" placeholder="Enter Discount per Fee" value="<?php echo @$feerule['max_install_extend'] ?>" min="0" required>
                                                <span class="help-inline"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                            <label class="control-label col-md-3">Max Discount for student at admission time in Rupees</label>
                                            <div class="col-md-3">
                                                <input type="number" class="form-control input-inline input-large" name="max_discount" placeholder="Max discount" min="0" value="<?php echo @$feerule['max_discount'] ?>" required>
                                                <span class="help-inline"></span>

                                            </div>
                                        </div>
                                    <div class="form-group" style="background-color:yellow;">
                                            <label class="control-label col-md-3">Total Discounts per User in number</label>
                                            <div class="col-md-3">
                                                <input type="number" class="form-control input-inline input-large" name="max_discount_no" placeholder="Max discount" min="0" value="<?php echo @$feerule['max_discount_no'] ?>" required>
                                                <span class="help-inline"></span>

                                            </div>
                                        </div>

                               <div class="form-group" ">
                                            <label class="control-label col-md-3">Total Incentive on Admission in %</label>
                                            <div class="col-md-3">
                                                <input type="number" class="form-control input-inline input-large" name="max_incentive_no" placeholder="Max Incentive" min="0" value="<?php echo @$feerule['max_comision'] ?>" required>
                                                <span class="help-inline"></span>

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
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->