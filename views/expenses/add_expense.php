
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
								<i class="fa fa-plus"></i> Add Expense &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Remaining Petty Cash =  <h><?php echo $pettycash ?></h>
							</div>

						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" id="myForm" action="<?php echo site_url();?>/expenses/insert" enctype="multipart/form-data">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control campus" name="campus_id" id="campus_id" required>
                                                <option value="">Select Campus</option>
												<?php
													foreach($campuses as $campus):
												?>
                                                <option value="<?php echo $campus['campus_id'];?>" selected><?php echo $campus['campus_name'];?></option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                            <!--<span class="help-inline"></span>-->
                                        </div>
                                    </div>

                                    <div class="exp_details">
                                        <div class="exp_cats">
                                            <div class="form-group" id="div-0">
                                                <label class="col-md-3 control-label">Expense Category <span class="required">*</span></label>
                                                <div class="col-md-5">
                                                    <select class="form-control Select2 exps" data-count="0" name="expense_category_id[]" id="category_id" required>
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
                                    
                                    <div class="salary" style="display:none;">
                                    	<div class="form-group">
                                            <label class="col-md-3 control-label">Select Teacher <span class="required">*</span></label>
                                            <div class="col-md-5">
                                                <select class="form-control" name="user_id" id="user_id" >
                                                    
                                                </select>
                                            </div>
                                        </div>	
                                    </div>
									
									<div class="form-group month_selector" style="display:none;">
										<label class="control-label col-md-3">Months Only</label>
										<div class="col-md-3">
											<div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m')?>" data-date-format="yyyy-mm" data-date-viewmode="years" data-date-minviewmode="months">
												<input type="text" id="month_selector" name="month_year" class="form-control" readonly>
												<span class="input-group-btn">
												<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
												</span>
											</div>
										</div>
									</div>
									
                                    <div class="rickshaw" style="display:none;">
										<div class="form-group">
											<label class="col-md-3 control-label">Rickshaw Number <span class="required">*</span></label>
											<div class="col-md-9">
												<input type="text" class="form-control input-inline input-medium rickshaw_column" name="rickshaw_number" placeholder="Enter title" value="" >
												<span class="help-inline"></span>
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label">Driver Cell no <span class="required">*</span></label>
											<div class="col-md-9">
												<input type="text" class="form-control input-inline input-medium rickshaw_column" name="driver_phone" placeholder="Enter title" value="" >
												<span class="help-inline"></span>
											</div>
										</div>
                                    </div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Title <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium custom_title" name="title" placeholder="Enter title" value="" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
                                        <label class="control-label col-md-3">Date <span class="required">*</span></label>
                                        <div class="col-md-3">
                                            <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-end-date="+0d" data-date-viewmode="years">
                                                <input type="text" name="date" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
                                                <span class="input-group-btn">
                                                <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Amount <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="number" class="form-control input-inline input-medium amount" name="amount" placeholder="Enter expense amount" id="amount" value="" onkeydown="myFunction()" onkeyup="myFunction()" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Purpose</label>
										<div class="col-md-9">
                                            <textarea class="form-control" rows="3" name="purpose"></textarea>
										</div>
									</div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Expense Image </label>
                                        <div class="col-md-9">
                                            <input type="file" name="image"  value="" />
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
                                    <div class="type" style="display:none;">
                                    	<div class="form-group">
											<label class="col-md-3 control-label">Type <span class="required">*</span></label>
											<div class="col-md-9">
												<label class="radio-inline">
												<input type="radio" name="type" id="optionsRadios1" value="result" checked="chcecked" /> According to Result </label>
												<label class="radio-inline">
												<input type="radio" name="type" id="optionsRadios2" value="class" /> According to Class </label>
											</div>
										</div>
                                    </div>
									<div class="council_fee_by_class" style="display:none;">
                                    	<div class="form-group">
                                            <label class="col-md-3 control-label">Class <span class="required">*</span></label>
                                            <div class="col-md-5">
                                                <select name="class_id" class="form-control classes">
                                                    
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
											<label class="col-md-3 control-label">Class <span class="required">*</span></label>
											<div class="col-md-9">
												<label class="radio-inline">
												<input type="radio" name="class" id="optionsRadios4" value="1" checked /> 1st year </label>
												<!--<label class="radio-inline">
												<input type="radio" name="class" id="optionsRadios5" value="2" /> 2nd Year </label>-->
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label">Exam # <span class="required">*</span></label>
											<div class="col-md-9">
												<input type="hidden" name="class_council_exam_no" class="class_council_exam_no" value=""/>
                                                <input type="number" class="form-control input-inline input-medium class_council_exam_no" id="class_council_exam_no" placeholder="Enter Council Exam" value="" disabled />
											</div>
										</div>

                                    </div>
                                    <div class="council_fee" style="display:none;">
										<div class="form-group">
											<label class="col-md-3 control-label">Class <span class="required">*</span></label>
											<div class="col-md-9">
												<label class="radio-inline">
												<input type="radio" name="class" id="optionsRadios4" value="1" <?php if(@$this->input->post('class')==1){echo 'checked';}?> /> 1st year </label>
												<label class="radio-inline">
												<input type="radio" name="class" id="optionsRadios5" value="2" <?php if(@$this->input->post('class')==2){echo 'checked';}?> /> 2nd Year </label>
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label">Exam # <span class="required">*</span></label>
											<div class="col-md-9">
												<select name="result_council_exam_no" class="form-control input-inline input-medium council_exam_no">
												</select>
											</div>
										</div>

									</div>
                                </div>
                                <div class="students_list" style="display:none;">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Select Payment Type <span class="required">*</span></label>
                                        <div class="col-md-9">

                                            <select class="form-control" name="payment_type" id="payment_type" required>
                                                <option value="cash">Cash</option>
                                                <?php
                                                foreach($council_ids as $council_id):
                                                    ?>
                                                    <option value="<?php echo $council_id['id'];?>"><?php echo $council_id['description']." ( ".$council_id['debit']." - ".$council_id['tagged_amount']." )";?></option>
                                                <?php
                                                endforeach;
                                                ?>
                                            </select>

                                        </div>
                                    </div>
                                    <div class="myalert"></div>
                                	<div class="table" style="border:1px solid #DDD;">
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Sr no.</th>
                                                    <th>Class</th>
                                                    <th>Student Name</th>
                                                    <th>Contractor</th>
                                                    <th>CNIC</th>
                                                    <th>Roll No</th>
                                                    <th>Fee Remarks</th>
                                                    <th>Submit Fee</th>
                                                    <th>Fee Created By</th>
                                                    <th>Fee Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <!--<input style="text-align: right;" type="checkbox" id="checkAll" class="all_selection" name="all_selection"/>
                                            <label for="vehicle1"> Select All</label><br>-->
                                            <tbody class="council_students">
                                                <div>



                                                </div>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<input type="hidden" name="add_by" value="<?php echo $this->session->userdata('name');?>" />
											<input type="hidden" name="add_by_id" value="<?php echo $this->session->userdata('user_id');?>" />
                                            <input type="hidden" name="last_edit" value="<?php echo $this->session->userdata('name');?>" />
											<input type="hidden" class="student_ids" name="student_ids" value="" />
                                            <button type="submit" id="submitbtn" class="btn green">Add Expense</button>
											<button onclick="location.href = '<?php echo site_url();?>/expenses/all_expenses'" type="button" class="btn default">Cancel</button>
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
		document.addEventListener('DOMContentLoaded', () => {
		    var count = 0;
            $("#checkAll").click(function(){
                alert("Hello");
                $('.student_id').trigger('click');
            });
            $('.Select2').select2();
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
        });

        function myFunction() {
            var payment_type = document.getElementById("payment_type").value;
            //alert(payment_type);
            if(payment_type=='cash')
            {
                let tot=<?php echo $pettycash ?>;
                let x = document.getElementById("amount").value;
                if (x>tot){
                    alert('Your Petty cash is low you cannot add this expense ');
                    document.getElementById("amount").value = 0;
                }
            }
        }
    </script>