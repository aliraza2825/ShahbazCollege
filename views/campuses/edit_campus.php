
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
								<i class="fa fa-edit"></i> Edit Campus
							</div>
						</div>
						<div class="portlet-body form">
                            <?php
                            	foreach($campuses as $campus):
							?>
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/campuses/update/<?php echo $campus['campus_id'];?>" enctype="multipart/form-data">
								<div class="form-body">
									<div class="form-group">
										<label class="col-md-3 control-label">Campus Logo <span class="required">*</span></label>
										<div class="col-md-9">
											<?php
                                            	if($campus['logo']!==''):
											?>
                                            <div class="add_logo" style="display:none;">
                                            <input type="file" class="form-control input-inline input-medium" name="logo" value="" />
											<span class="help-inline"></span>
                                            </div>
                                            <img width="300" class="campus_logo" src="<?php echo base_url();?>uploads/<?php echo $campus['logo'];?>" />
                                            <button type="button" class="btn red remove_logo"><i class="fa fa-trash"></i> Remove Logo</button>
                                            <?php
                                            	else:
											?>
                                            <input type="file" class="form-control input-inline input-medium" name="logo" value="" required>
											<span class="help-inline"></span>
                                            <?php
                                            	endif;
											?>
                                            <input type="hidden" name="old_logo" value="<?php echo $campus['logo'];?>" />
										</div>
									</div>

                                    <div class="form-group">
										<label class="col-md-3 control-label">Campus Code <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="campus_code" placeholder="Enter campus code" value="<?php echo $campus['campus_code'];?>" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Campus Roll No Code <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="roll_no_code" placeholder="Enter campus code" value="<?php echo $campus['roll_no_code'];?>" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Campus Name <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="campus_name" placeholder="Enter campus name" value="<?php echo $campus['campus_name'];?>" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Campus Website <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="website" placeholder="Enter campus website" value="<?php echo $campus['website'];?>" required>
											<span class="help-inline"></span>
										</div>
									</div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Campus Email <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control input-inline input-medium" name="email" placeholder="Enter campus Email" value="<?php echo $campus['email'];?>" required>
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Address <span class="required">*</span></label>
										<div class="col-md-9">
                                            <textarea class="form-control" rows="3" name="address" required><?php echo $campus['address'];?></textarea>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Advertising SMS <span class="required">*</span></label>
										<div class="col-md-9">
                                            <textarea class="form-control" rows="3" name="sms" required><?php echo $campus['sms'];?></textarea>
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-3 control-label">SMS Messanger Facebook API <span class="required">*</span></label>
										<div class="col-md-9">
                                            <textarea class="form-control" rows="3" name="facebook_api" ><?php echo $campus['facebook_api'];?></textarea>
										</div>
									</div>

                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Campus Stamp <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <?php
                                            if($campus['stamp']!== null):
                                                ?>
                                                <div class="add_stamp" style="display:none;">
                                                    <input type="file" class="form-control input-inline input-medium" name="stamp" value="" />
                                                    <span class="help-inline"></span>
                                                </div>
                                                <img width="300" class="campus_stamp" src="<?php echo base_url();?>uploads/<?php echo $campus['stamp'];?>" />
                                                <button type="button" class="btn red remove_stamp"><i class="fa fa-trash"></i> Remove Stamp</button>

                                            <?php
                                            else:
                                                ?>
                                                <input type="file" class="form-control input-inline input-medium" name="stamp" value="" required>
                                                <span class="help-inline"></span>
                                            <?php
                                            endif;
                                            ?>
                                            <input type="hidden" name="old_stamp" value="<?php echo $campus['stamp'];?>" />
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Campus Head Stamp <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <?php
                                            if($campus['head_stamp']!== null):
                                                ?>
                                                <div class="add_head_stamp" style="display:none;">
                                                    <input type="file" class="form-control input-inline input-medium" name="head_stamp" value="" />
                                                    <span class="help-inline"></span>
                                                </div>
                                                <img width="300" class="head_stamp" src="<?php echo base_url();?>uploads/<?php echo $campus['head_stamp'];?>" />
                                                <button type="button" class="btn red remove_head_stamp"><i class="fa fa-trash"></i> Remove Stamp</button>

                                            <?php
                                            else:
                                                ?>
                                                <input type="file" class="form-control input-inline input-medium" name="head_stamp" value="" required>
                                                <span class="help-inline"></span>
                                            <?php
                                            endif;
                                            ?>
                                            <input type="hidden" name="old_head_stamp" value="<?php echo $campus['head_stamp'];?>" />
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <h2>Numbers For SMS</h2>
                                        <hr />
                                    </div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Phone For Website </label>
										<div class="col-md-9">
                                            <input type="text" class="form-control input-inline input-medium" name="phone" placeholder="Enter phone for website" value="<?php echo $campus['phone'];?>" />
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Phone For Expense </label>
										<div class="col-md-9">
                                            <input type="text" class="form-control input-inline input-medium" name="phone1" placeholder="Enter phone 1" value="<?php echo $campus['phone1'];?>" />
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Phone For Expense</label>
										<div class="col-md-9">
                                            <input type="text" class="form-control input-inline input-medium" name="phone2" placeholder="Enter phone 2" value="<?php echo $campus['phone2'];?>" />
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Phone For Visitors to contact at</label>
										<div class="col-md-9">
                                            <input type="text" class="form-control input-inline input-medium" name="phone3" placeholder="Enter phone 3" value="<?php echo $campus['phone3'];?>" />
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Phone For Staff login Sms Further Query</label>
										<div class="col-md-9">
                                            <input type="text" class="form-control input-inline input-medium" name="phone4" placeholder="Enter phone 4" value="<?php echo $campus['phone4'];?>" />
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Phone For Daily Report</label>
										<div class="col-md-9">
                                            <input type="text" class="form-control input-inline input-medium" name="phone5" placeholder="Enter phone 5" value="<?php echo $campus['phone5'];?>" />
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Phone For Student Fee,admission,struk off Alert further information, Website Online apllication alert</label>
										<div class="col-md-9">
                                            <input type="text" class="form-control input-inline input-medium" name="phone6" placeholder="Enter phone 6" value="<?php echo $campus['phone6'];?>" />
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Phone For CV Management</label>
										<div class="col-md-9">
                                            <input type="text" class="form-control input-inline input-medium" name="phone7" placeholder="Enter phone 7" value="<?php echo $campus['phone7'];?>" />
										</div>
									</div>


                                    <div class="row">
                                    	<div class="col-md-12">
                                        	<h2>For Challan Deatils</h2>
                                            <hr />
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">Bank Name </label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control input-inline input-medium" name="bank_name" placeholder="Bank Name" value="<?php echo $campus['bank_name'];?>" />
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">Account Number </label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control input-inline input-medium" name="account_no" placeholder="Account Number" value="<?php echo $campus['account_no'];?>" />
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">Challan Bottom Note </label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control input-inline input-medium" name="note" placeholder="Challan Bottom account note" value="<?php echo $campus['note'];?>" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">For Mobile Application</label>
                                        <div class="col-md-9">
                                            <input type="checkbox" class="form-control input-inline input-medium" name="for_mobile_application" value="1" <?php if ($campus['for_mobile_application'] == 1) echo "checked";?>/>
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Update Campus</button>
											<button onclick="location.href = '<?php echo site_url()?>'" type="button" class="btn default">Cancel</button>
										</div>
									</div>
								</div>
							</form>
                            <?php
                            	endforeach;
							?>
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

        jQuery(document).ready(function(){
            jQuery('.remove_head_stamp').click(function(){
                jQuery('.head_stamp').remove();
                jQuery(this).remove();
                jQuery('.add_head_stamp').show();
            });
        });

    }, false );
	</script>