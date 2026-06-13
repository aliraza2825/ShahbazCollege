
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
								<i class="fa fa-plus"></i> Add Sequence
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/councils/insert_sequence">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Select Council <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select class="form-control input-inline input-large councils" name="council_id" required>
                                                <option value="">SELECT COUNCIL</option>
                                                <?php
                                                foreach($councils as $council):
                                                ?>
                                                    <option value="<?php echo $council['council_id'];?>"><?php echo $council['name'];?></option>
                                                <?php
                                                endforeach;
                                                ?>
                                            </select>
                                        </div>
                                    </div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Select Courses <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select class="form-control input-inline input-large courses" name="course_id" required>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="type_container">
                                        <div class="single_type_container">
                                            <div class="form-group">
                                                <div class="form-group">
                                                <label class="col-md-3 control-label">Council Fee Name <span class="required">*</span></label>
                                                <div class="col-md-3">
                                                    <input type="text" class="form-control input-inline input-large"  name="type_name[]" placeholder="Enter Sequence Type" value="" required>
        											    <span class="help-inline">Write the fee type</span>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="input-group input-large date date-picker" data-date="<?php echo date('Y-m-d');?>" data-date-format="dd/mm" data-date-viewmode="years" data-date-minviewmode="months">
        												<input type="text" class="form-control" name="date[]" readonly required>
        												<span class="input-group-btn">
        												<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
        												</span>
        											</div>
        											<span class="help-inline">Select date and month of fee</span>
                                                </div>
                                                <div class="col-md-3">
                                                    <select class="form-control input-large pull-left" name="fee[]" required>
                                                        <option value="">Select Option</option>
                                                        <option value="1">Yes</option>
                                                        <option value="0">No</option>
                                                    </select>
                                                    <span class="help-inline">Select Yes if fee is applicable, otherwise No.</span>
                                                </div>
                                                
                                                <div class="col-md-3" style="display:none;">
                                                    <div class="input-group input-large date date-picker" data-date="<?php echo date('Y-m-d');?>" data-date-format="dd/mm" data-date-viewmode="years" data-date-minviewmode="months">
        												<input type="text" class="form-control" name="expense_date[]" readonly required>
        												<span class="input-group-btn">
        												<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
        												</span>
        											</div>
        											<span class="help-inline">Select date and month of expense</span>
                                                </div>
                                                <div class="col-md-3" style="display:none;">
                                                    <input type="number" class="form-control input-large pull-left" name="expense_fee[]" min="0" placeholder="Enter Expense Fees" value="">
                                                    <span class="help-inline">Write the expense fee here. If there is no expense fee then write 0.</span>
                                                </div>
                                                <div class="col-md-3"></div>
                                                <div class="col-md-3">
                                                    <select class="form-control input-large" name="recurring[]" id="recurring" required>
                                                        <option value="">Select Fee Type</option>
                                                        <option value="One Time">One Time</option>
                                                        <option value="Every Semester">Every Semester</option>
                                                        <option value="Each Exam">Each Exam</option>
                                                        <option value="After Chances">After Chances</option>
                                                        <option value="End of Degree">End of Degree</option>
                                                        
                                                    </select>
                                                    <span class="help-inline">Select Fee type</span>
                                                </div>
                                                
                                                <div class="col-md-3">
                                                    <select class="form-control input-large" name="action_type[]" required>
                                                        <option value="fee">Only Fee</option>
                                                        <option value="information">Add Information</option>
                                                        <option value="add_roll_no">Add Roll No</option>
                                                        <option value="add_result">Add Result</option>
                                                    </select>
                                                    <span class="help-inline">Select Action Type</span>
                                                </div>
                                                <div class="col-md-3">
                                                    <select class="form-control input-large expense" name="have_expense[]" data-number="1" required>
                                                        <option value="">Select Expense</option>
                                                        <option value="1">Yes</option>
                                                        <option value="0">No</option>
                                                    </select>
                                                    <span class="help-inline">Expense against this fee</span>
                                                </div>
                                                <div class="col-md-3 expense_field_1" style="display:none;"></div>
                                                <div class="col-md-3 expense_field_1" style="display:none;">
                                                    <span class="help-inline">Select Expense Category</span>
                                                    <div class="exp_details">
                                                        <div class="exp_cats">
                                                            <div class="form-group" id="div-0">
                                                                <div class="col-md-6">
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
                                                
                                                <div class="col-md-3 expense_field_2" style="display:none;"></div>
                                                <div class="col-md-3 expense_field_2" style="display:none;">
                                                    <span class="help-inline">No of Chances</span>
                                                    <div>
                                                        <div class="form-group">
                                                            <div class="col-md-6">
                                                                <input type="number" class="form-control input-large pull-left" name="no_of_chances" min="0" placeholder="Enter Number of Chances" value="">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr />
                                        </div>
                                    </div>
                                    
                                    <!--
                                    <div class="type_container">
                                        <div class="single_type_container">
                                            <div class="form-group">
                                                <label class="col-md-1 control-label">Select Type <span class="required">*</span></label>
                                                <div class="col-md-2">
                                                    <input type="text" class="form-control input-inline input-medium" name="type_name[]" placeholder="Enter Sequence Type" value="" required>
    											    <span class="help-inline"></span>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="input-group input-small date date-picker" data-date="<?php echo date('Y-m-d');?>" data-date-format="dd/mm" data-date-viewmode="years" data-date-minviewmode="months">
        												<input type="text" class="form-control" name="date[]" readonly required>
        												<span class="input-group-btn">
        												<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
        												</span>
        											</div>
                                                </div>
                                                <div class="col-md-7">
                                                    <select class="form-control input-small pull-left" name="have_expense[]" required>
                                                        <option value="">Select Expense</option>
                                                        <option value="1">Yes</option>
                                                        <option value="0">No</option>
                                                    </select>
                                                    
                                                    <div class="radio-list pull-left">
                                                        <label class="radio-inline">
                                                        <input type="radio" name="have_fee[]" data-column-number="1" class="have_fee" value="1" checked> Fees </label>
                                                        <label class="radio-inline">
                                                        <input type="radio" name="have_fee[]" data-column-number="1" class="have_fee" value="0"> No Fees </label>
                                                    </div>
                                                    
                                                    <div class="fee_container_1 pull-left" style="margin-left:15px;">
                                                        <input type="number" class="form-control input-small pull-left" name="fee[]" min="0" placeholder="Enter Fees" value="" required>
                                                    </div>
                                                    
                                                    <select class="form-control input-medium pull-left" style="margin-left:15px;" name="recurring" required>
                                                        <option value="">Select Fee Type</option>
                                                        <option value="One Time">One Time</option>
                                                        <option value="Every Semester">Every Semester</option>
                                                    </select>
        										</div>
                                                
                                            </div>
                                        </div>
                                    </div>
                                    -->
                                    
                                    <div class="form-group">
                                        <div class="col-md-3"></div>
                                        <div class="col-md-9">
                                            <!--<button type="button" class="btn green add_more"><i class="fa fa-plus"></i> Add More</button>-->
                                            <!--<button type="button" class="btn red remove"><i class="fa fa-trash"></i> Remove</button>-->
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Add Sequence</button>
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
								<i class="fa fa-list"></i> Sequences
							</div>
						</div>
						<div class="portlet-body">
							<table class="table table-striped table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
								<th class="hidden">
                                	 hidden
                                </th>
                                <th>
									 ID
								</th>
								<th>
									 Council Name
								</th>
                                <th>
									 Course Name
								</th>
								<th>
									 Type Nmae
								</th>
								<th>
									 Last Date
								</th>
								<th>
									 Has Fee
								</th>
                                <th>
                                    Has Expense
                                </th>
								<th>
									 Fee
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i = 0;
								foreach($sequences as $sequence):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $sequence['council_sequence_id']?>
								</td>
                                <td>
									 <?php echo $sequence['council_name']?>
								</td>
                                <td>
									 <?php echo $sequence['course_name']?>
								</td>
								<td>
									 <?php echo $sequence['type_name']?>
								</td>
								<td>
									 <?php echo $sequence['last_date']?>
								</td>
								<td>
									 <?php 
									    if($sequence['has_fee']==1)
									    {
									        echo 'Yes';
									    }
									    else
									    {
									        echo 'No';
									    }
									 ?>
								</td>
                                <td>
                                    <?php
                                    if($sequence['has_expense']==1)
                                    {
                                        echo 'Yes';
                                        // echo "<br>Expense Date : ".$sequence['expense_date'];
                                        // echo "<br>Expense Fee : ".$sequence['expense_fee'];
                                        $category = $this->db->get_where("expense_category","expense_category_id = ".$sequence['exp_category_id'])->row();
                                        if($category)
                                            echo "<br> Expense Category : ".$category->name;
                                        else
                                            echo"<br>No Category Selected";
                                    }
                                    else
                                    {
                                        echo 'No';
                                    }
                                    ?>
                                </td>
								<td>
									 <?php echo $sequence['fee'] == 0 ? "No":"Yes"?>
								</td>
								<td>
									<a href="<?php echo site_url().'/councils/edit_sequence/'.$sequence['council_sequence_id'];?>" title="Edit" class="btn blue"><i class="fa fa-edit"></i></a>
                                    <a onclick="return confirm('Are you sure you want to delete this Sequence?')" href="<?php echo site_url().'/councils/delete_sequence/'.$sequence['council_sequence_id'];?>" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>
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
        var count = 0;
        jQuery(document).ready(function(){
            jQuery('.add_more').click(function(){
                var counter = jQuery('.single_type_container').length;
                var current_counter = counter+1;
                var html = '<div class="single_type_container"><div class="form-group"><label class="col-md-3 control-label">Council Fee <span class="required">*</span></label><div class="col-md-3"><input type="text" class="form-control input-inline input-large" name="type_name[]" placeholder="Enter Sequence Type" value="" required><span class="help-inline">Write the fee type</span></div><div class="col-md-3"><div class="input-group input-large date date-picker" data-date="<?php echo date('Y-m-d');?>" data-date-format="dd/mm" data-date-viewmode="years" data-date-minviewmode="months"><input type="text" class="form-control" name="date[]" readonly required><span class="input-group-btn"><button class="btn default" type="button"><i class="fa fa-calendar"></i></button></span></div><span class="help-inline">Select date and month of fee</span></div><div class="col-md-3"><input type="number" class="form-control input-large pull-left" name="fee[]" min="0" placeholder="Enter Fees" value="" required><span class="help-inline">Write the fee here. If there is no fee then write 0.</span></div><div class="col-md-3"></div><div class="col-md-3"><select class="form-control input-large expense" name="have_expense[]" data-number="'+current_counter+'" required><option value="">Select Expense</option><option value="1">Yes</option><option value="0">No</option></select><span class="help-inline">Expense against this fee</span></div><div class="col-md-3 expense_field_'+current_counter+'" style="display:none;"><div class="input-group input-large date date-picker" data-date="<?php echo date('Y-m-d');?>" data-date-format="dd/mm" data-date-viewmode="years" data-date-minviewmode="months"><input type="text" class="form-control" name="expense_date[]" readonly required><span class="input-group-btn"><button class="btn default" type="button"><i class="fa fa-calendar"></i></button></span></div><span class="help-inline">Select date and month of expense</span></div><div class="col-md-3 expense_field_'+current_counter+'" style="display:none;"><input type="number" class="form-control input-large pull-left" name="expense_fee[]" min="0" placeholder="Enter Expense Fees" value="" required><span class="help-inline">Write the expense fee here. If there is no expense fee then write 0.</span></div><div class="col-md-3 expense_field_'+current_counter+'" style="display:none;"></div><div class="col-md-3"><select class="form-control input-large" name="recurring" required><option value="">Select Fee Type</option><option value="One Time">One Time</option><option value="Every Semester">Every Semester</option><option value="Each Exam">Each Exam</option></select><span class="help-inline">Select Fee type</span></div></div><hr /></div>';
                jQuery('.type_container').append(html);
                ComponentsPickers.init();
            });
            jQuery('.remove').click(function(){
                jQuery('.single_type_container').last().remove();
            });
            jQuery('.have_fee').live('click',function(){
                var selectedValue = jQuery('.have_fee:checked').val();
                var column_number = jQuery(this).data('column-number');
                
                if(selectedValue==1)
                {
                    jQuery('.fee_container_'+column_number).show();
                    jQuery('.fee_container_'+column_number+' input').attr('required');
                }
                else
                {
                    jQuery('.fee_container_'+column_number).hide();
                    jQuery('.fee_container_'+column_number+' input').removeAttr('required');
                    jQuery('.fee_container_'+column_number+' input').val('0');
                }
            });
            jQuery('.expense').live('change',function(){
                var number = jQuery(this).data('number');
                var expense = jQuery(this).val();
                if(expense==1)
                {
                    jQuery('.expense_field_'+number).show();
                }
                else
                {
                    jQuery('.expense_field_'+number).hide();
                }
            });
            jQuery('#recurring').live('change',function(){
                var expense = jQuery(this).val();
                if(expense=='After Chances')
                {
                    jQuery('.expense_field_2').show();
                }
                else
                {
                    jQuery('.expense_field_2').hide();
                }
            });
            jQuery('.councils').change(function(){
                var council_id = jQuery(this).val();
                jQuery.ajax({
                    type: "post",
                    async: false,
                    url: '<?php echo site_url()?>/councils/getCouncilCourses',
                    data: {
                        council_id : council_id,
                    },
                    success: function(data) {
                        jQuery('.courses').html(data);
                    }

                });
            });
        });
        $(document).on('change', '.exps', function (e) {
                var exp_id = this.value;
                var con = $(this).data('count');
                jQuery.ajax({
                    type: "post",
                    async: false,
                    url: '<?php echo site_url()?>/expenses/getSubExpenses',
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
    }, false );
</script>