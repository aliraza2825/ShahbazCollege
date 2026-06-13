
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
            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/purchase_order/update_order/<?php echo $purchase_order->id ?>">
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

                                <div class="form-group">
                                    <label class="col-md-3 control-label">Purchaser <span class="required">*</span></label>
                                    <div class="col-md-9">
                                        <select name="purchase_user_id" class="form-control input-inline input-large select2" id="select2_sample1" required>
                                            <?php
                                            foreach ($users as $user):
                                                ?>
                                                <option value="<?php echo $user['user_id']?>" <?php if ($user['user_id'] == $purchase_order->purchaser){ echo "selected";} ?>><?php echo $user['first_name'].' '.$user['last_name'].' ( '.$user['designation_name'] .' )'?></option>
                                            <?php
                                            endforeach;
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Vendor Name <span class="required">*</span></label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control input-inline input-large" name="vendor_name" placeholder="Enter Vendor Name" value="<?php echo $purchase_order->vendor_name?>" required>
                                        <span class="help-inline"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Vendor Address <span class="required">*</span></label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control input-inline input-large" name="vendor_address" placeholder="Enter Vendor address" value="<?php echo $purchase_order->vendor_address?>" required>
                                        <span class="help-inline"></span>
                                    </div>
                                </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Total Price <span class="required">*</span></label>
                                <div class="col-md-9">
                                    <input type="number" class="form-control input-inline input-large" id="require_amount" name="amount" placeholder="Enter Purchase Amount" value="<?php echo $purchase_order->total_amount?>" required>
                                    <span class="help-inline"></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Description <span class="required">*</span></label>
                                <div class="col-md-9">
                                    <textarea class="form-control input-inline input-large" rows="3" name="description" placeholder="Enter description" required><?php echo $purchase_order->description?></textarea>
                                    <span class="help-inline"></span>
                                </div>
                            </div>
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
						<div class="portlet-body">
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
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
                            <?php
                                $data = $this->db->get_where('po_products',array('po_id'=>$purchase_order->id))->result_array();

                                foreach ($data as $key=>$prods):
                                    $count++;  ?>

                                <tr id="tr<?php echo $count; ?>">
                                    <td><?php echo $count; ?></td>
                                    <td>
                                       <select name="category_id[]" class="form-control input-inline input-large select2" id="select2_sample1" required>
                                           <?php
                                            foreach ($products as $product):
                                                if ($product['product_name_id'] == $prods['item_id']) {
                                                    $sel = "selected";
                                                }
                                                else  {
                                                    $sel = "";
                                                }
                                                echo  '<option value="'.$product['product_name_id'].'"'. $sel  .'>'.$product['product_name'].'</option>';
                                            endforeach;
                                            ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control input-inline input-large quantity" data-rowid= "<?php echo $count ?>" id="qty<?php echo $count ?>"  name="quantity[]" placeholder="Enter Quantity" value="<?php echo $prods['quantity'] ?>" required>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control input-inline input-large unit_price"  data-rowid= "<?php echo $count ?>" id="unt<?php echo $count ?>"  name="unit_price[]" placeholder="Enter Unit Price" value="<?php echo $prods['per_item_price'] ?>" required>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control input-inline input-large total_price" data-old_price="<?php echo $prods['total_price'] ?>" id="tot<?php echo $count ?>" name="total_price[]" placeholder="Enter Total Price" value="<?php echo $prods['total_price'] ?>" required>
                                    </td>
                                    <td>
                                        <a onclick="removerow(<?php echo $count ?>)" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach;
                            $count++;
                            ?>
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
                        <button type="submit" class="btn green">Edit Purchase Order</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END CONTENT -->
    <script>

        var index = <?php echo (count($data)+1); ?>;

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
                    '<td><input type="number" class="form-control input-inline input-large quantity" data-rowid= "' + index + '" id="qty' + index + '" name="quantity[]" placeholder="Enter Quantity" value="1" required></td>' +
                    '<td><input type="number" class="form-control input-inline input-large unit_price" data-rowid= "' + index + '" id="unt' + index + '" name="unit_price[]" placeholder="Enter Unit Price" value="1" required></td>' +
                    '<td><input type="number" class="form-control input-inline input-large total_price" data-old_price="0" id="tot' + index + '" name="total_price[]" placeholder="Enter Total Price" value="1" readonly></td>' +
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
                $('#require_amount').val(require_met);
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
                $('#require_amount').val(require_met);
                $('#tot'+id).data('old_price',tot_amt);
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