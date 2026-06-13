
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
								<i class="fa fa-edit"></i> Edit Expense
							</div>
						</div>
                        <?php
                        	foreach($expenses as $expense):
						?>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/expenses/update/<?php echo $expense['expense_id'];?>" enctype="multipart/form-data">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control" name="campus_id">
                                                <?php
													foreach($campuses as $campus):
												?>
                                                <option value="<?php echo $campus['campus_id'];?>" <?php if($campus['campus_id']==$expense['campus_id']){echo 'selected';}?>><?php echo $campus['campus_name'];?></option>
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
                                                    <select class="form-control Select2 exps" data-count="0" name="expense_category_id" id="category_id" required>
                                                        <option value="">Select expense category</option>
                                                        <?php
                                                            foreach($categories as $category):
                                                        ?>
                                                        <option value="<?php echo $category['expense_category_id'];?>" <?php if($category['expense_category_id']==$expense['exp_category_id']){echo 'selected';}?>><?php echo $category['name'];?></option>
                                                        <?php
                                                            endforeach;
                                                        ?>
                                                    </select>
                                                    <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="rickshaw" <?php if($expense['expense_category_id']!=1){echo 'style="display:none;"';}?>>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Rickshaw Number <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium rickshaw_column" name="rickshaw_number" value="<?php echo $expense['rickshaw_number'];?>" placeholder="Enter title" >
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Driver Cell no <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium rickshaw_column" name="driver_phone" placeholder="Enter title" value="<?php echo $expense['driver_phone'];?>" >
											<span class="help-inline"></span>
										</div>
									</div>
                                    </div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Title <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="title" placeholder="Enter title" value="<?php echo $expense['title'];?>" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
                                        <label class="control-label col-md-3">Date <span class="required">*</span></label>
                                        <div class="col-md-3">
                                            <div class="input-group input-medium date date-picker" data-date="<?php echo $expense['date'];?>" data-date-format="yyyy-mm-dd" data-date-end-date="+0d" data-date-viewmode="years">
                                                <input type="text" name="date" class="form-control" value="<?php echo $expense['date'];?>" readonly>
                                                <span class="input-group-btn">
                                                <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Amount <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="number" class="form-control input-inline input-medium" name="amount" placeholder="Enter expense amount" value="<?php echo $expense['amount'];?>" readonly>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Purpose</label>
										<div class="col-md-9">
                                            <textarea class="form-control" rows="3" name="purpose"><?php echo $expense['purpose'];?></textarea>
										</div>
									</div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Expense Image </label>
                                        <div class="col-md-9">
                                            <input type="file" name="img"  value="" />
                                            <span class="help-inline">
                                            	<?php
                                                	if($expense['image']!=''):
												?>
                                                <img src="<?php echo base_url();?>uploads/<?php echo $expense['image'];?>" width="200" />
                                                <?php
                                                	endif;
												?>
                                            </span>
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                        	<input type="hidden" name="last_edit" value="<?php echo $this->session->userdata('name');?>" />
                                            <input type="hidden" name="image" value="<?php echo $expense['image'];?>" />
											<button type="submit" class="btn green">Update Expense</button>
											<button onclick="location.href = '<?php echo site_url();?>/expenses/all_expenses'" type="button" class="btn default">Cancel</button>
										</div>
									</div>
								</div>
							</form>
						</div>
                        <?php
                        	endforeach;
						?>
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
            $('.Select2').select2();
            $(document).on('change', '.exps', function (e) {
                var exp_id = this.value;
                var con = $(this).data('count');
                jQuery.ajax({
                    type: "post",
                    async: false,
                    url: '<?php echo site_url()?>/expenses/getSubExpenses',
                    data: {
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
            });
        });
    </script>