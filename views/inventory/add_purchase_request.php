
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
								<i class="fa fa-search"></i> Search Available Product
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="#">
								<div class="form-body">
                                    <div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label class="control-label col-md-3">Campus <span class="required">*</span></label>
												<div class="col-md-6">
													<select name="campus_id" class="form-control input-inline input-large select2 search_campus_id" required>
														<option value="">SELECT CAMPUS</option>
														<?php
															foreach($campuses as $campus):
														?>
														<option data-campus-id="<?php echo $campus['campus_id'];?>" value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name'];?></option>
														<?php
															endforeach;
														?>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label class="control-label col-md-3">Product <span class="required">*</span></label>
												<div class="col-md-6">
													<select name="product_name_id" class="form-control input-inline input-large select2 search_product_name_id" required>
														<option value="">SELECT PRODUCT</option>
														<?php
															foreach($product_names as $product_name):
														?>
														<option data-product-name-id="<?php echo $product_name['product_name_id'];?>" value="<?php echo $product_name['product_name_id'];?>"><?php echo $product_name['product_name'];?></option>
														<?php
															endforeach;
														?>
													</select>
												</div>
											</div>
										</div>

										<div class="col-md-12">
											<table class="table">
												<thead>
													<tr>
                                                        <th>Campus</th>
														<th>Product</th>
														<th>Room</th>
														<th>Subroom</th>
														<th>Quantity</th>
													</tr>
												</thead>
												<tbody class="products">
													<tr>
														<td></td>
														<td></td>
														<td></td>
														<td></td>
														<td></td>
													</tr>
												</tbody>
											</table>
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
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-plus"></i> Add Purchase Request
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/inventory/insert_purchase_request" enctype="multipart/form-data">
								<div class="form-body">
									<div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Title <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-large" name="title" placeholder="Enter Title" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 purchase_requests">

                                        </div>
                                        <div class="col-md-12">
                                            <button class="btn green add_purchase_request" type="button"><i class="fa fa-plus"></i> Add More</button>
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Add Purchase Request</button>
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
        //var index = 1;
        document.addEventListener( "DOMContentLoaded", function() {
            jQuery('.add_purchase_request').click(function () {
                var counter = jQuery('.purchases-rows').length;
                //alert(counter);
                var str = '<div class="purchases-rows" id="tr' + counter + '">' +
                    '<select name="campus_ids[]" class="form-control input-inline input-large select2 purchase_request_campus_id" data-row-number="'+counter+'" required>';
                str += '<?php
                    $html = "";
                        $html .= '<option value="">Select Campus</option>';
                    foreach ($campuses as $campus):
                        $html .= '<option value="' . $campus['campus_id'] . '">' . $campus['campus_name'] . '</option>';
                    endforeach;
                    echo $html;
                    ?>';
                str += '</select>';

                str +='&nbsp;&nbsp;&nbsp;&nbsp;';
                
                str += '<select name="room_ids[]" class="form-control input-inline input-medium select2 purchase_request_room_id room_'+counter+'" data-row-number="'+counter+'" required>';
                str += '<option value="">Select Room</option>';
                str += '</select>';

                str +='&nbsp;&nbsp;&nbsp;&nbsp;';

                str += '<select name="subroom_ids[]" class="form-control input-inline input-medium select2 subroom_'+counter+'">';
                str += '<option value="">Select SubRoom</option>';
                str += '</select>';

                str +='&nbsp;&nbsp;&nbsp;&nbsp;';

                str += '<select name="product_name_ids[]" class="form-control input-inline input-large select2" required>';
                str += '<?php
                    $html = '';
                    //$count++;
                    foreach ($product_names as $product_name):
                        $html .= '<option value="' . $product_name['product_name_id'] . '">' . $product_name['product_name'] . '</option>';
                    endforeach;
                    echo $html;
                    ?>';
                str += '</select>';
                str += ' <input type="number" min="1" style="margin-left: 10px;" class="form-control input-inline input-medium" name="quantity[]" placeholder="Enter Product Quantity" value="1" required>  <a onclick="removerow(' + counter + ')" title="Delete" class="btn red"><i class="fa fa-trash"></i></a></div>';
                $('.purchase_requests').append(str);
                $(".select2").select2('destroy');
                $(".select2").select2();
                index++;
            });

            jQuery('.purchase_request_campus_id').live('change',function(){
                var campus_id = jQuery(this).val();
                var row_number = jQuery(this).data('row-number');
                jQuery.ajax({
                    type: "post",
                    async: false,
                    url: '<?php echo site_url();?>/inventory/getCampusRooms',
                    data: {
                        campus_id : campus_id,
                    },
                    success: function(data) {
                        jQuery('.select2').select2('destroy');
                        jQuery('.room_'+row_number).html(data);
                        jQuery('.select2').select2();
                    }

                });
            });

            jQuery('.purchase_request_room_id').live('change',function(){
                var room_id = jQuery(this).val();
                var row_number = jQuery(this).data('row-number');
                jQuery.ajax({
                    type: "post",
                    async: false,
                    url: '<?php echo site_url();?>/inventory/getCampusSubrooms',
                    data: {
                        room_id : room_id,
                    },
                    success: function(data) {
                        jQuery('.select2').select2('destroy');
                        jQuery('.subroom_'+row_number).html(data);
                        jQuery('.select2').select2();
                    }

                });
            });

        }, false );

        function removerow(id) {
            $('#tr' + id).remove();
        }
    </script>