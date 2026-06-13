	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			All Teachers <small>Here you can find all teachers</small>
			</h3>-->
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
            <?php endif;

            $count = 0;?>
			<div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> Edit Expense Category 
							</div>
						</div>
                        <div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/expenses/update_category/<?php echo $this->uri->segment(3);?>">
								<div class="form-body">
                                	<div class="row">
                                        <div class="col-md-12">
                                            <div class="exp_details">
                                                <div class="exp_cats">
                                                    <div class="form-group" id="div-0">
                                                        <label class="col-md-3 control-label">Head Category <span class="required">*</span></label>
                                                        <div class="col-md-9">
                                                            <select class="form-control Select2 exps" data-count="0" name="expense_category_id[]" id="category_id">
                                                                <option value="">Select expense category</option>
                                                                <?php
                                                                foreach($exp_categories as $category):
                                                                    ?>
                                                                    <option value="<?php echo $category['expense_category_id'];?>"><?php echo $category['name'];?></option>
                                                                <?php
                                                                endforeach;
                                                                ?>
                                                            </select>
                                                            <!--<span class="help-inline"></span>-->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                	<div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Category <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <?php
                                                    if ($current_categories[0]['sub_of'] != NULL) {
                                                        getSubExpenses($current_categories[0]['sub_of']);
                                                    }
                                                    ?>
                                                    <input type="text" class="form-control input-inline input-medium" name="name" placeholder="Enter Category Name" value="<?php echo $current_categories[0]['name'];?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Type <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select class="col-md-3 form-control" name="type" >
                                                        <option value="">Select Type</option>
                                                        <option value="general" <?php if ($current_categories[0]['type'] == "general") echo "selected"?>>General</option>
                                                        <option value="advertisement" <?php if ($current_categories[0]['type'] == "advertisement") echo "selected"?>>Advertisement</option>
                                                    </select>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Type <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select class="col-md-3 form-control" name="status" >
                                                        <option value="">Select Type</option>
                                                        <option value="active" <?php if ($current_categories[0]['status'] == "active") echo "selected"?>>Active</option>
                                                        <option value="inactive" <?php if ($current_categories[0]['status'] == "inactive") echo "selected"?>>In-Active</option>
                                                    </select>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="col-md-3 control-label"> Select for Campus <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <select class="form-control select2" id="select2_sample2" name="campus_ids[]" multiple>
                                                    <?php
                                                    foreach($campuses as $campus):
                                                        ?>
                                                        <option value="<?php echo $campus['campus_id'];?>" <?php if(in_array($campus['campus_id'], explode(',',$current_categories[0]['for_campus']))){echo 'selected';}?>>
                                                            <?php echo $campus['campus_name']?>
                                                        </option>
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
											<button type="submit" class="btn green">Update</button>
										</div>
									</div>
								</div>
                            </form>
                        </div>
						<div class="portlet-body">
							<table class="table table-striped table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
                                <th class="hidden">
									 Hidden
								</th>
                                <th>
									 Name
								</th>
                                <th>
                                    Type
                                </th>
                                <th>
                                    Status
                                </th>
                                <th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=0;
								foreach($categories as $category):
							?>
                            <tr class="odd gradeX">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $category['name'];?>
								</td>
                                <td>
                                    <?php echo $category['type'];?>
                                </td>
                                <td>
                                    <?php echo $category['status'];?>
                                </td>
                                <td>
									<a href="<?php echo site_url();?>/expenses/edit_expense_category/<?php echo $category['expense_category_id'];?>" class="btn blue"><i class="fa fa-edit"></i></a>
                                    <a onclick="return confirm('Are you sure you want to delete this Category?')" href="<?php echo site_url();?>/expenses/delete_expense_category/<?php echo $category['expense_category_id'];?>" class="btn red"><i class="fa fa-trash"></i></a>
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
        document.addEventListener('DOMContentLoaded', () => {
            $('.Select2').select2();
            var count = 0;
            $(document).on('change', '.exps', function (e) {
                var exp_id = this.value;
                var con = $(this).data('count');
                jQuery.ajax({
                    type: "post",
                    async: false,
                    url: '<?php echo site_url()?>/expenses/getSubExpensesFree',
                    data: {
                        campus_id : exp_id,
                        expense_id : exp_id,
                        count : con,
                    },
                    success: function(data) {
                        if (data !="") {
                            con++;
                            for (let n=con;n<=count;n++){
                                console.log($('#div-'+n));
                                $('#div-'+n).remove();
                            }
                            jQuery('.exp_cats').append(data);
                            count = con;
                            $('#category_id'+(con--)).select2();
                        }else {
                            con++;
                            for (let n=con;n<=count;n++){
                                jQuery('#div-'+n).remove();
                            }
                            count = con;
                        }
                    }
                });

                var category_val = jQuery(this).val();
                if(category_val==1)
                {
                    jQuery('.rickshaw').show();
                    jQuery('.custom_title').val('Auto Rickshaw');
                    jQuery('.rickshaw_column').attr('required','required');
                }
                else
                {
                    jQuery('.rickshaw').hide();
                    jQuery('.custom_title').val('');
                    jQuery('.rickshaw_column').val('');
                    jQuery('.rickshaw_column').removeAttr('required');

                }

                if(category_val==9)
                {
                    jQuery('.salary').show();
                    jQuery('.month_selector').show();

                    jQuery('#month_selector').attr('required','required');
                }
                else
                {
                    jQuery('.salary').hide();
                    jQuery('.month_selector').hide();
                    jQuery('#user_id').removeAttr('required');
                    jQuery('#month_selector').removeAttr('required');

                }
                if(category_val==13)
                {
                    jQuery('.type').show();
                    jQuery('.council_fee').show();
                    jQuery('.students_list').show();
                    jQuery('.amount').attr('placeholder','Enter per student council fee');
                }
                else
                {
                    jQuery('.type').hide();
                    jQuery('.council_fee').hide();
                    jQuery('.students_list').hide();
                    jQuery('.student_ids').val('');
                    jQuery('.amount').attr('placeholder','Enter Expense Amount');
                }

            });
        });
    </script>