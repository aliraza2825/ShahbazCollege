
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
            <!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Add Class <small>add your desire class</small>
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
			<div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-edit"></i> Edit Product Name
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/inventory/update_product_name/<?php echo $this->uri->segment(3);?>">
								<div class="form-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="exp_details">
                                                <div class="exp_cats">
                                                    <div class="form-group" id="div-0">
                                                        <label class="col-md-3 control-label">Head Category <span class="required">*</span></label>
                                                        <div class="col-md-9">
                                                            <select class="form-control Select2 exps" data-count="0" name="head_product_id[]" id="category_id">
                                                                <option value="">Select expense category</option>
                                                                <?php
                                                                foreach($product_names as $product_nam):
                                                                    ?>
                                                                    <option value="<?php echo $product_nam['product_name_id'];?>"><?php echo $product_nam['product_name'];?></option>
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
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Product Name <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <?php
                                            if ($product_name[0]['sub_of'] != NULL) {
                                                getSubProducts($product_name[0]['sub_of']);
                                                echo '<br />';
                                                echo '|<br />';
                                                echo 'v<br />';
                                            }
                                            ?>
                                            <input type="text" class="form-control input-inline input-large" name="product_name" placeholder="Enter Product Name" value="<?php echo $product_name[0]['product_name'];?>" required>
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
									<div class="form-group">
										<label class="col-md-3 control-label">Product Type</label>
										<div class="col-md-9 radio-list" required>
											<label class="radio-inline">
											<input type="radio" name="type" id="optionsRadios4" value="0" <?php if($product_name[0]['type'] == "0"){ echo "checked"; }?>> Inventory </label>
											<label class="radio-inline">
											<input type="radio" name="type" id="optionsRadios5" value="1" <?php if($product_name[0]['type'] == "1"){ echo "checked"; }?>> Asset </label>
										</div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Update Product Name</button>
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
            $('.Select2').select2();
            var count = 0;
            $(document).on('change', '.exps', function (e) {
                var exp_id = this.value;
                var con = $(this).data('count');
                jQuery.ajax({
                    type: "post",
                    async: false,
                    url: '<?php echo site_url()?>/Inventory/getSubExpensesFree',
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
            });
        });
    </script>