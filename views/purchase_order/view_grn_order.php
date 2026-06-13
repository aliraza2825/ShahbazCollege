
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
            <div class="form-horizontal" id='DivIdToPrint'>
			<div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title" style="text-align: center">
							<div class="caption" style="text-align: center; width: 100%">
								<i style="text-align: center"></i><?php echo "GRN-".$purchase_order->id ?>
							</div>
						</div>
						<div class="portlet-body form">

                            <div class="form-group col-md-6">
                                <label class="col-md-6 control-label" style="font-weight: bolder; text-align: left;">Receiver </label>
                                <label class="col-md-6 control-label" style="text-align: left"><?php echo $purchase_order->created_by;?> </label>

                            </div>
                            <div class="form-group col-md-6">
                                <label class="col-md-6 control-label" style="font-weight: bolder; text-align: left;">GRN NO </label>
                                <label class="col-md-6 control-label" style="text-align: left"><?php echo " <strong>GRN-".$purchase_order->id."</strong>"?></label>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="col-md-6 control-label" style="font-weight: bolder; text-align: left;">Purchase Order No </label>
                                <label class="col-md-6 control-label" style="text-align: left"><?php echo " <strong>PO-".$purchase_order->purchase_order_no."</strong>"?></label>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="col-md-6 control-label" style="font-weight: bolder; text-align: left;">Received Date </label>
                                <label class="col-md-6 control-label" style="text-align: left"><?php echo $purchase_order->created_at?></label>
                            </div>

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
								<i class="fa fa-list"></i>Products Detail
							</div>
						</div>
						<div class="portlet-body">
							<table class="table table-striped table-bordered table-hover" id="product_table">
							<thead>
							<tr>
								<th class="hidden">
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

							</tr>
							</thead>
							<tbody>
                            <?php
                                $data = $this->db->join('product_names','product_names.product_name_id = grn_products.product_id')->get_where('grn_products',array('grn_products.grn_id'=>$purchase_order->id))->result_array();

                                foreach ($data as $key=>$prods):
                                    $count++;  ?>

                                <tr id="tr<?php echo $count; ?>">
                                    <td class="hidden"><?php echo $count; ?></td>
                                    <td>

                                           <?php echo  $prods['product_name']; ?>

                                        </select>
                                    </td>
                                    <td>
                                        <label  class="form-control input-inline input-large"  ><?php echo $prods['qty'] ?> </label>
                                    </td>
                                    <td>
                                        <label  class="form-control input-inline input-large" ><?php echo $prods['price_per'] ?></label>
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
                        <button type="button" onclick='printDiv();' class="btn green">Print</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END CONTENT -->
    <script>
        function printDiv(){
            var printContents = document.getElementById("DivIdToPrint").innerHTML;
            var originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
        }
    </script>