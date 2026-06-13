
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
								<i class="fa fa-edit"></i> Edit Sequence
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/councils/update_sequence/<?php echo $council_sequence[0]['council_sequence_id'] ?>">
								<div class="form-body">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Select Council <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select class="form-control input-inline input-large councils" name="council_id" required>
                                                <option value="">SELECT COUNCIL</option>
                                                <?php
                                                foreach($councils as $council):
                                                    ?>
                                                    <option value="<?php echo $council['council_id'];?>" <?php if($council['council_id'] == $council_sequence[0]['council_id']) echo "selected" ?>><?php echo $council['name'];?></option>
                                                <?php
                                                endforeach;
                                                ?>
                                            </select>
                                        </div>
                                    </div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Select Courses <span class="required">*</span></label>
                                        <div class="col-md-6">
                                            <select class="form-control input-inline input-large courses" name="course_id" required>
                                                <?php
                                                foreach($courses as $course):
                                                ?>
                                                    <option value="<?php echo $course['course_id'];?>" <?php if($course['course_id'] == $council_sequence[0]['course_id']) echo "selected" ?>><?php echo $course['course_name'];?></option>
                                                <?php
                                                endforeach;
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="type_container">
                                        <div class="single_type_container">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Council Fee <span class="required">*</span></label>
                                                <div class="col-md-3">
                                                    <input type="text" class="form-control input-inline input-large" name="type_name" placeholder="Enter Sequence Type" value="<?php echo $council_sequence[0]['type_name'] ?>" required>
                                                    <span class="help-inline">Write the fee type</span>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="input-group input-large date date-picker" data-date="<?php echo $council_sequence[0]['last_date'];?>" data-date-format="dd/mm" data-date-viewmode="years" data-date-minviewmode="months">
                                                        <input type="text" class="form-control" name="date" value="<?php echo $council_sequence[0]['last_date'];?>" readonly required>
                                                        <span class="input-group-btn">
        												<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
        												</span>
                                                    </div>
                                                    <span class="help-inline">Select date and month of fee</span>
                                                </div>
                                                
                                                <div class="col-md-3">
                                                    <select class="form-control input-large pull-left" name="fee" required>
                                                        <option value="">Select Option</option>
                                                        <option value="1" <?php if($council_sequence[0]['fee'] != "0") echo 'selected';?>>Yes</option>
                                                        <option value="0" <?php if($council_sequence[0]['fee'] == "0") echo 'selected';?>>No</option>
                                                    </select>
                                                    <span class="help-inline">Select Yes if fee is applicable, otherwise No.</span>
                                                </div>
                                                <div class="col-md-3"></div>
                                                <div class="col-md-3">
                                                    <select class="form-control input-large" name="recurring" required>
                                                        <option value="">Select Fee Type</option>
                                                        <option value="One Time" <?php if($council_sequence[0]['recurring'] == "One Time" ) echo "selected" ?>>One Time</option>
                                                        <option value="Every Semester" <?php if($council_sequence[0]['recurring'] == "Every Semester" ) echo "selected" ?>>Every Semester</option>
                                                        <option value="Each Exam" <?php if($council_sequence[0]['recurring'] == "Each Exam" ) echo "selected" ?>>Each Exam</option>
                                                        <option value="After Chances" <?php if($council_sequence[0]['recurring'] == "After Chances" ) echo "selected" ?>>After Chances</option>
                                                        <option value="End of Degree" <?php if($council_sequence[0]['recurring'] == "End of Degree" ) echo "selected" ?>>End of Degree</option>
                                                    </select>
                                                    <span class="help-inline">Select Fee type</span>
                                                </div>
                                                <div class="col-md-3">
                                                    <select class="form-control input-large" name="action_type" required>
                                                        <option value="fee" <?php if($council_sequence[0]['action_type'] == 'fee' ) echo "selected" ?>>Only Fee</option>
                                                        <option value="information" <?php if($council_sequence[0]['action_type'] == 'information' ) echo "selected" ?>>Add Information</option>
                                                        <option value="add_roll_no" <?php if($council_sequence[0]['action_type'] == 'add_roll_no' ) echo "selected" ?>>Add Roll No</option>
                                                        <option value="add_result" <?php if($council_sequence[0]['action_type'] == 'add_result' ) echo "selected" ?>>Add Result</option>
                                                    </select>
                                                    <span class="help-inline">Select Action Type</span>
                                                </div>
                                                <div class="col-md-3">
                                                    <select class="form-control input-large expense" name="have_expense" data-number="1" required>
                                                        <option value="">Select Expense</option>
                                                        <option value="1" <?php if($council_sequence[0]['has_expense'] == 1 ) echo "selected" ?>>Yes</option>
                                                        <option value="0" <?php if($council_sequence[0]['has_expense'] == 0 ) echo "selected" ?>>No</option>
                                                    </select>
                                                    <span class="help-inline">Expense against this fee</span>
                                                </div>
                                                
                                                <div class="col-md-3"></div>
                                                <div class="col-md-3" style="display:none">
                                                    <div class="form-control input-group input-large date date-picker" data-date="<?php echo $council_sequence[0]['expense_date'];?>" data-date-format="dd/mm" data-date-viewmode="years" data-date-minviewmode="months">
                                                        <input type="text" class="form-control" name="expense_date" value="<?php echo $council_sequence[0]['expense_date'];?>" readonly required>
                                                        <span class="input-group-btn">
        												<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
        												</span>
                                                    </div>
                                                    <span class="help-inline">Select date and month of expense</span>
                                                </div>
                                                
                                                <div class="col-md-3" style="display:none">
                                                    <input type="number" class="form-control input-large pull-left" name="expense_fee" min="0" placeholder="Enter Expense Fees" value="<?php echo $council_sequence[0]['expense_fee'] ?>">
                                                    <span class="help-inline">Write the expense fee here. If there is no expense fee then write 0.</span>
                                                </div>
                                                
                                                <div class="col-md-6 expense_field_1" style="display:<?php if($council_sequence[0]['has_expense'] == 1) {echo "block";} else echo "none" ?>;">
                                                    <?php
                                                        function getCategoryChain($category_id, $db)
                                                        {
                                                            $chain = [];
                                                        
                                                            while ($category_id != NULL) {
                                                        
                                                                $cat = $db->get_where('expense_category', [
                                                                    'expense_category_id' => $category_id
                                                                ])->row_array();
                                                        
                                                                if (!$cat) break;
                                                        
                                                                array_unshift($chain, $cat); // start me add karo
                                                        
                                                                $category_id = $cat['sub_of']; // move to parent
                                                            }
                                                        
                                                            return $chain;
                                                        }
                                                        
                                                        // 👇 last selected id (DB se)
                                                        $selected_last_id = $council_sequence[0]['exp_category_id'];
                                                        if($council_sequence[0]['has_expense'] == 1) {
                                                        // 👇 full chain
                                                            $category_chain = getCategoryChain($selected_last_id, $this->db);
                                                    ?>
                                                    <span class="help-inline">Select Expense Category</span>
                                                    <div class="exp_details">
                                                        <div class="exp_cats">
                                                        <?php foreach($category_chain as $index => $cat): ?>
                                                        
                                                            <?php
                                                            // parent id (top level ke liye NULL)
                                                            $parent_id = $cat['sub_of'];
                                                        
                                                            // current level ki categories
                                                            if ($parent_id == NULL) {
                                                                $this->db->where('sub_of IS NULL', null, false);
                                                            } else {
                                                                $this->db->where('sub_of', $parent_id);
                                                            }
                                                        
                                                            $this->db->where('status', 'active');
                                                            $categories = $this->db->get('expense_category')->result_array();
                                                            ?>
                                                        
                                                            <div class="form-group" id="div-<?php echo $index; ?>">
                                                                <div class="col-md-6">
                                                                    <select class="form-control Select2 exps"
                                                                            data-count="<?php echo $index; ?>"
                                                                            name="expense_category_id[]"
                                                                            id="category_id<?php echo $index; ?>">
                                                        
                                                                        <option value="">Select category</option>
                                                        
                                                                        <?php foreach($categories as $c): ?>
                                                                            <option value="<?php echo $c['expense_category_id']; ?>"
                                                                                <?php if($c['expense_category_id'] == $cat['expense_category_id']) echo "selected"; ?>>
                                                                                <?php echo $c['name']; ?>
                                                                            </option>
                                                                        <?php endforeach; ?>
                                                        
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        
                                                        <?php endforeach; 
                                                        }else {?>
                                                        <span class="help-inline">Select Expense Category</span>
                                                        <div class="exp_details">
                                                            <div class="exp_cats">
                                                            <?php 
                                                            
                                                                $this->db->where('status', 'active');
                                                                $categories = $this->db->get('expense_category')->result_array();
                                                                ?>
                                                            
                                                                <div class="form-group" id="div-0">
                                                                    <div class="col-md-6">
                                                                        <select class="form-control Select2 exps"
                                                                                data-count="0"
                                                                                name="expense_category_id[]"
                                                                                id="category_id">
                                                            
                                                                            <option value="">Select category</option>
                                                            
                                                                            <?php foreach($categories as $c): ?>
                                                                                <option value="<?php echo $c['expense_category_id']; ?>">
                                                                                    <?php echo $c['name']; ?>
                                                                                </option>
                                                                            <?php endforeach; ?>
                                                            
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            
                                                            <?php
                                                        }?>
                                                        
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-3 expense_field_2" style="display:<?php if($council_sequence[0]['recurring'] == 'After Chances') {echo "block";} else echo "none" ?>;">
                                                    <span class="help-inline">No of Chances</span>
                                                    <div>
                                                        <div class="form-group">
                                                            <div class="col-md-6">
                                                                <input type="number" class="form-control input-large pull-left" name="no_of_chances" min="0" placeholder="Enter Number of Chances" value="<?php echo $council_sequence[0]['no_of_chances']; ?>">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr />
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Update Sequence</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
		</div>
	</div>
    <script>
        document.addEventListener( "DOMContentLoaded", function(){
            // 1. Expense dropdown show/hide trigger
            $('.expense').trigger('change');
        
            // 2. Har selected category pe change trigger karo
            $('.exps').each(function(){
                if($(this).val() !== ''){
                    $(this).trigger('change');
                }
            });
            var count = 0;
            jQuery(document).ready(function(){
                jQuery('.add_more').click(function(){
                    var counter = jQuery('.single_type_container').length;
                    var current_counter = counter+1;
                    var html = '<div class="single_type_container"><div class="form-group"><label class="col-md-3 control-label">Council Fee <span class="required">*</span></label><div class="col-md-3"><input type="text" class="form-control input-inline input-large" name="type_name[]" placeholder="Enter Sequence Type" value="" required><span class="help-inline">Write the fee type</span></div><div class="col-md-3"><div class="input-group input-large date date-picker" data-date="<?php echo date('Y-m-d');?>" data-date-format="dd/mm" data-date-viewmode="years" data-date-minviewmode="months"><input type="text" class="form-control" name="date[]" readonly required><span class="input-group-btn"><button class="btn default" type="button"><i class="fa fa-calendar"></i></button></span></div><span class="help-inline">Select date and month of fee</span></div><div class="col-md-3"><input type="number" class="form-control input-large pull-left" name="fee[]" min="0" placeholder="Enter Fees" value="" required><span class="help-inline">Write the fee here. If there is no fee then write 0.</span></div><div class="col-md-3"></div><div class="col-md-3"><select class="form-control input-large expense" name="have_expense[]" data-number="'+current_counter+'" required><option value="">Select Expense</option><option value="1">Yes</option><option value="0">No</option></select><span class="help-inline">Expense against this fee</span></div><div class="col-md-3 expense_field_'+current_counter+'" style="display:none;"><div class="input-group input-large date date-picker" data-date="<?php echo date('Y-m-d');?>" data-date-format="dd/mm" data-date-viewmode="years" data-date-minviewmode="months"><input type="text" class="form-control" name="expense_date[]" readonly required><span class="input-group-btn"><button class="btn default" type="button"><i class="fa fa-calendar"></i></button></span></div><span class="help-inline">Select date and month of expense</span></div><div class="col-md-3 expense_field_'+current_counter+'" style="display:none;"><input type="number" class="form-control input-large pull-left" name="expense_fee[]" min="0" placeholder="Enter Expense Fees" value="" required><span class="help-inline">Write the expense fee here. If there is no expense fee then write 0.</span></div><div class="col-md-3 expense_field_'+current_counter+'" style="display:none;"></div><div class="col-md-3"><select class="form-control input-large" name="recurring" required><option value="">Select Fee Type</option><option value="One Time">One Time</option><option value="Every Semester">Every Semester</option></select><span class="help-inline">Select Fee type</span></div></div><hr /></div>';
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
                $('.Select2').select2();
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
	<!-- END CONTENT -->