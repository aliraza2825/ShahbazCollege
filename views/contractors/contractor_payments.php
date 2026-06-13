
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Add Teacher <small>You can add teacher here</small>
			</h3>-->
			<!-- END PAGE HEADER-->
            <!-- BEGIN DASHBOARD STATS -->
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
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-money"></i> Create Contract Payment (<?php echo $contract[0]['contract_name']; ?>)
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/contractors/add_contract_payment_plan/<?php echo $this->uri->segment(3)?>" enctype="multipart/form-data">
								<div class="form-body">
                                    <div class="row">
                                    	<div class="col-md-12">
                                        	<h2>Punjab Consulation Fee</h2>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                            	<label class="col-md-4 control-label">1st Year Consulation Fee</label>
                                            	<div class="col-md-8">
                                            		<input type="text" class="form-control input-inline input-medium" name="consulation_fee_1" value="" required/>
                                            	</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                            	<label class="col-md-4 control-label">Dead Line</label>
                                            	<div class="col-md-8">
                                            		<div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d');?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                            			<input type="text" name="consulation_dead_line_1" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
                                            			<span class="input-group-btn">
                                            			<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                            			</span>
                                                    </div>
                                            	</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                            	<label class="col-md-4 control-label">Council Exam #</label>
                                            	<div class="col-md-8">
                                            		<input type="number" min="0" class="form-control input-inline input-medium" name="consulation_payment_plan_1" value="" placeholder="Enter Council Exam #" required/>
                                            	</div>
                                            </div>
                                        </div>
                                        <div style="clear:both;"></div>
                                    </div>
                                    <div class="row">
                                    	<div class="col-md-12">
                                        	<h2>Select Fee Plan</h2>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Select Payment Plan</label>
                                                <div class="col-md-8">
                                                    <select class="form-control" name="payment_plan" id="payment_plan">
                                                        <option>Select Payment Plan</option>
                                                        <option>Custom Plan</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row payment_html">
                                    	
                                    </div>
                                    <div class="row custom_plan">
                                        
                                    </div>
                                    <div class="row custom_plan_add" style="display:none;">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                	<label class=" col-md-4 control-label">No of Istallments</label>
                                                <div class="col-md-4">
                                                	<input type="number" value="" class="form-control no_of_installments" /> 
                                                </div>
                                                <div class="col-md-4">
                                                	<button type="button" class="btn green add_payment_row"> Add</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Submit</button>
											<button onclick="location.href = '<?php echo site_url()?>/students/all_students'" type="button" class="btn default">Cancel</button>
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