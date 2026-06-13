
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
								<i class="fa fa-edit"></i> Edit Free Items Rule
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/rules/update_free_item_rules/<?php echo $this->uri->segment(3);?>" enctype="multipart/form-data">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control campus_id select2" name="campus_ids[]" multiple required>
												<?php
													foreach($campuses as $campus):
												?>
                                                <option value="<?php echo $campus['campus_id'];?>" <?php if(in_array($campus['campus_id'],explode(',',$free_item_rule[0]['campus_ids']))){echo 'selected';}?>><?php echo $campus['campus_name'];?></option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                            <!--<span class="help-inline"></span>-->
                                        </div>
                                    </div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Classes <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control classes select2" name="class_ids[]" multiple required>
												<?php
													foreach($classess as $classs):
												?>
                                                <option value="<?php echo $classs['class_id'];?>" <?php if(in_array($classs['class_id'],explode(',',$free_item_rule[0]['class_ids']))){echo 'selected';}?>><?php echo $classs['name'];?></option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                            <!--<span class="help-inline"></span>-->
                                        </div>
                                    </div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Products <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control select2" name="product_name_ids[]" multiple required>
												<?php
													foreach($product_names as $product_name):
												?>
                                                <option value="<?php echo $product_name['product_name_id'];?>" <?php if(in_array($product_name['product_name_id'],explode(',',$free_item_rule[0]['product_name_ids']))){echo 'selected';}?>><?php echo $product_name['product_name'];?></option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                            <!--<span class="help-inline"></span>-->
                                        </div>
                                    </div>
									<div class="form-group">
										<label class="control-label col-md-3">Free Item Till Date <span class="required">*</span></label>
										<div class="col-md-3">
											<div class="input-group input-medium date date-picker" data-date="<?php echo $free_item_rule[0]['till_date']?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
												<input type="text" name="till_date" class="form-control" value="<?php echo $free_item_rule[0]['till_date'];?>" readonly>
												<span class="input-group-btn">
												<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
												</span>
											</div>
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-md-3">Student Admission Date <span class="required">*</span></label>
										<div class="col-md-6">
											<div class="input-group input-medium date date-picker" data-date="<?php echo $free_item_rule[0]['student_admission_date'];?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
												<input type="text" name="student_admission_date" class="form-control" value="<?php echo $free_item_rule[0]['student_admission_date'];?>" readonly>
												<span class="input-group-btn">
												<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
												</span>
											</div>
											<span class="help-inline">Free Item for students whose admission date is greater than selected date</span>
										</div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Update Rule</button>
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
	<script>
		document.addEventListener( "DOMContentLoaded", function(){
			//$('.select2').select2();
			$('.campus_id').on('change',function (e) {
					var campus_id = $(this).select2('val');
					var selected_classes = jQuery('.classes').select2('val');
					$('.select2').select2('destroy');
					if(campus_id!='')
					{
						jQuery.ajax({
							type: "post",
							async: false,
							url: '<?php echo site_url()?>/rules/getCampusClasses',
							data: {
								campus_id : campus_id,
								selected_classes : selected_classes
							},
							success: function(data) {
								$('.classes').html(data);
								$('.select2').select2();
								//console.log(data);
							}
						});
					}
				});
		}, false );
	</script>