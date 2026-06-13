
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
								<i class="fa fa-plus"></i> Free Items Rule
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/rules/add_free_item_rules" enctype="multipart/form-data">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control campus_id select2" name="campus_ids[]" multiple required>
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
                                        <label class="col-md-3 control-label">Classes <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control classes select2" name="class_ids[]" multiple required>
												
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
                                                <option value="<?php echo $product_name['product_name_id'];?>"><?php echo $product_name['product_name'];?></option>
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
											<div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
												<input type="text" name="till_date" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
												<span class="input-group-btn">
												<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
												</span>
											</div>
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-md-3">Student Admission Date <span class="required">*</span></label>
										<div class="col-md-6">
											<div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
												<input type="text" name="student_admission_date" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
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
											<button type="submit" class="btn green">Add Rule</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
			
			<div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> Created Rules of Free Items
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
                                	Campus Names
                                </th>
								<th>
                                	Classes Names
                                </th>
                                <th>
                                	Product Names
                                </th>
                                <th>
									 Till Date
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=0;
								foreach($rules as $rule):
							?>
                            <tr class="odd gradeX">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
                                <td>
									<?php 
										$this->db->where_in('campus_id',explode(',',$rule['campus_ids']));
										$campuses = $this->db->get('campuses')->result_array();
										foreach($campuses as $campus)
										{
											echo $campus['campus_name'].'<br />';
										}
									?>
								</td>
                                <td>
									<?php 
										$this->db->where_in('class_id',explode(',',$rule['class_ids']));
										$classes = $this->db->get('classes')->result_array();
										foreach($classes as $class)
										{
											echo $class['name'].'<br />';
										}
									?>
								</td>
                                <td>
									<?php 
										$this->db->where_in('product_name_id',explode(',',$rule['product_name_ids']));
										$product_names = $this->db->get('product_names')->result_array();
										foreach($product_names as $product_name)
										{
											echo $product_name['product_name'].'<br />';
										}
									?>
								</td>
								<td>
									<?php echo $rule['till_date'];?>
								</td>
								<td>
									<a href="<?php echo site_url();?>/rules/edit_free_item/<?php echo $rule['free_item_rule_id'];?>" class="btn yellow"><i class="fa fa-edit"></i> Edit</a>
									<a href="<?php echo site_url();?>/rules/delete_free_item/<?php echo $rule['free_item_rule_id'];?>" onclick="return confirm(\'Are you sure you want to delete this Rule?\')" class="btn red"><i class="fa fa-trash"></i> Delete</a>
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
	<script>
		document.addEventListener( "DOMContentLoaded", function(){
			$('.select2').select2();
			$(document).on('change', '.campus_id', function (e) {
					var campus_id = $(this).select2('val');
					$('.select2').select2('destroy');
					if(campus_id!='')
					{
						jQuery.ajax({
							type: "post",
							async: false,
							url: '<?php echo site_url()?>/rules/getCampusClasses',
							data: {
								campus_id : campus_id
							},
							success: function(data) {
								$('.classes').html(data);
								$('.select2').select2();
								console.log(data);
							}
						});
					}
				});
		}, false );
	</script>