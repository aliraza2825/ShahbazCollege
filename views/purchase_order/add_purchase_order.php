
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Add Class <small>add your desire class</small>
			</h3>-->
			<!-- END PAGE HEADER-->
			<!-- BEGIN PAGE CONTENT-->
            <?php
                $count = 0;

            if(@$this->session->userdata('message')):?>
                <div class="alert alert-success">
                    <button class="close" data-close="alert"></button>
                    <span>
                    <?php echo $this->session->userdata('message');?> </span>
                </div>
            <?php endif;?>
            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/purchase_order/insert_order">
			<div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-plus"></i> Add Purchase Order
							</div>
						</div>
						<div class="portlet-body form">
                            <br />
                            <div class="form-group">
                                <label class="col-md-3 control-label">Purchaser <span class="required">*</span></label>
                                <div class="col-md-9">
                                    <select name="purchase_user_id" class="form-control input-inline input-large select2" id="select2_sample1" required>
                                        <?php
                                        foreach ($users as $user):
                                            ?>
                                            <option value="<?php echo $user['user_id']?>"><?php echo $user['first_name'].' '.$user['last_name'].' ( '.$user['designation_name'] .' )'?></option>
                                        <?php
                                        endforeach;
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Select Vendor<span class="required">*</span></label>
                                <div class="col-md-9">
                                    <select name="vendor_id" class="form-control input-inline input-large select2 vendor_id" id="select2_sample2" required>
                                        <option value="">Select Vendor</option>
                                        <?php
                                        foreach ($vendors as $key=>$vendor):
                                            ?>
                                            <option value="<?php echo $key?>"><?php echo $vendor['name'].' ( '.$vendor['phone'] .' )'?></option>
                                        <?php
                                        endforeach;
                                        ?>
                                    </select>
                                    <input type="hidden" class="form-control input-inline input-large" name="my_vendor_id" id="my_vendor_id" placeholder="Enter Vendor Name" value="" required>
                                    <input type="hidden" class="form-control input-inline input-large" name="vendor_name" id="vendor_name" placeholder="Enter Vendor Name" value="" required>
                                    <input type="hidden" class="form-control input-inline input-large" name="vendor_address" id="vendor_address" placeholder="Enter Vendor address" value="" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Total Price <span class="required">*</span></label>
                                <div class="col-md-9">
                                    <input type="number" class="form-control input-inline input-large" name="amount" id="require_amount" placeholder="Enter Purchase Amount" value="0" readonly required>
                                    <span class="help-inline"></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Description <span class="required">*</span></label>
                                <div class="col-md-9">
                                    <textarea class="form-control input-inline input-large" rows="3" name="description" placeholder="Enter description" required></textarea>
                                    <span class="help-inline"></span>
                                </div>
                            </div>
                            <br />
                        </div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
            <div class="form-group">
                <div class="col-md-12">
                        <button type="button" class="btn green add_line"><i class="fa fa-plus"></i> Add Product</button>
                </div>
            </div>
            <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i>All Products Name
							</div>
						</div>
						<div class="portlet-body" style="overflow-x: scroll">
							<table class="table table-striped table-bordered table-hover" id="product_table">
							<thead>
							<tr>
								<th>
                                	 ID
                                </th>
								<th>
									 Product Name
								</th>
								<th>
									 Quantity
								</th>
								<th>
									 Price Per Unit
								</th>
                                <th>
                                    Total Price
                                </th>
                                <th>
                                    Purchase For
                                </th>
                                <th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>

							</tbody>
							</table>
						</div>
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
            <div class="form-actions">
                <div class="row">
                    <div class="col-md-offset-3 col-md-9">
                        <button type="submit" class="btn green">Add Purchase Order</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END CONTENT -->
    <script>

        var index = 1;

        var vendors = <?php echo json_encode($vendors);?>;

        document.addEventListener( "DOMContentLoaded", function() {

            jQuery('.add_line').click(function () {


                var str = '<tr id="tr' + index + '">' +
                    '<td>' + index + '</td>' +
                    '<td><select name="category_id[]" class="form-control input-inline input-large select2" required>';

                str += '<?php
                    $html = "";
                    $count++;

                    foreach ($products as $product):

                        $html .= '<option value="' . $product['product_name_id'] . '">' . $product['product_name'] . '</option>';

                    endforeach;

                    echo $html;
                    ?>';

                str += '</select></td>' +
                    '<td><input type="number" class="form-control input-inline input-large quantity" data-rowid= "' + index + '" id="qty' + index + '" name="quantity[]" placeholder="Enter Quantity" value="0" required></td>' +
                    '<td><input type="number" class="form-control input-inline input-large unit_price" data-rowid= "' + index + '" id="unt' + index + '" name="unit_price[]" placeholder="Enter Unit Price" value="0" required></td>' +
                    '<td><input type="number" class="form-control input-inline input-large total_price" data-old_price="0" id="tot' + index + '" name="total_price[]" placeholder="Enter Total Price" value="0" readonly></td>' +
                    '<td><select class="form-control input-inline input-medium" name="inv_type[]"><option value="asset">Asset</option><option value="inventory">Inventory</option></select></td>' +
                    '<td><a onclick="removerow(' + index + ')" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>\n' +
                    '</td></tr>';


                $('#product_table tr:last').after(str);
                index++;
                $("select").select2("destroy").select2();
            });

            $('#product_table').on('keyup change', '.unit_price', function(){
                var id = $(this).data("rowid");
                var qty = $('#qty'+id).val();
                var tot_amt = qty * $('#unt'+id).val();
                $('#tot'+id).val(tot_amt);
                var require_amount = $('#require_amount').val();
                require_amount = require_amount-$('#tot'+id).data('old_price');
                var require_met = Number(require_amount) + Number(tot_amt);
                if (require_met>0)
                    $('#require_amount').val(require_met);
                else
                    $('#require_amount').val("0");

                $('#tot'+id).data('old_price',tot_amt);
            });

            $('#product_table').on('keyup change', '.quantity', function(){
                var id = $(this).data("rowid");
                var qty = $('#qty'+id).val();
                var tot_amt = qty * $('#unt'+id).val();
                $('#tot'+id).val(tot_amt);
                var require_amount = $('#require_amount').val();
                require_amount = require_amount-$('#tot'+id).data('old_price');
                var require_met = Number(require_amount) + Number(tot_amt);
                if (require_met>0)
                    $('#require_amount').val(require_met);
                else
                    $('#require_amount').val("0");
                $('#tot'+id).data('old_price',tot_amt);
            });

            $('.vendor_id').change(function() {
                $('#my_vendor_id').val(vendors[$(this).val()]['id']);
                $('#vendor_name').val(vendors[$(this).val()]['name']);
                $('#vendor_address').val(vendors[$(this).val()]['address']);
            });

        }, false );

        function removerow(id) {
            var require_amount = $('#require_amount').val();
            var require_met    = Number(require_amount) - Number($('#tot'+id).val());
            $('#require_amount').val(require_met);
            $('#tr' + id).remove();
            index--;
        }

    </script>