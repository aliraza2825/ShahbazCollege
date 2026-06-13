
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
            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/purchase_order/add_grn">
			<div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-plus"></i> GRN For Purchase Order
							</div>
						</div>
						<div class="portlet-body form">
                            <br />
                             <div class="form-group">
                                    <label class="col-md-3 control-label">Purchaser <span class="required">*</span></label>
                                    <div class="col-md-9">
                                        <select name="purchase_order" class="form-control input-inline input-large select2" id="select2_sample1" required>
                                            <?php
                                            foreach ($purchase_orders as $purchase_order):
                                                ?>
                                                <option value="<?php echo $purchase_order['id']?>" <?php if($purchase_order['id'] == $pr_id) echo "selected";?>><?php echo "PO-".$purchase_order['id'];?></option>
                                            <?php
                                            endforeach;
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            <br />
                        </div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>

            <div class="form-actions">
                    <div class="row">
                        <div class="col-md-offset-3 col-md-9">
                            <button type="submit" class="btn green">Get Details</button>
                        </div>
                    </div>
            </div>
            </form>
            <br />
            <br />
            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/purchase_order/insert_grn/<?php echo @$pr_order[0]['id']; ?>">
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

							</tr>
							</thead>
							<tbody>
                            <?php
                            if ($pr_order != null):
                            $data = $this->db->join('product_names','product_names.product_name_id = po_products.item_id')->get_where('po_products',array('po_products.po_id'=>$pr_order[0]['id']))->result_array();

                            foreach ($data as $key=>$prods):
                                $count++;  ?>

                                <tr id="tr<?php echo $count; ?>">
                                    <td><?php echo $count; ?></td>
                                    <td>

                                        <label  class="form-control input-inline input-large"  ><?php echo  $prods['product_name']; ?> </label>
                                        <input type="hidden" name="product_id[]" value="<?php echo $prods['item_id'];?>">
                                        </select>
                                    </td>
                                    <td>
                                        <input class="form-control input-inline input-medium"  type="number" name="qty[]" value="<?php echo $prods['quantity'];?>">
                                    </td>
                                    <td>
                                        <input type="hidden" name="unit_price[]" value="<?php echo $prods['per_item_price'];?>">
                                        <label  class="form-control input-inline input-medium" ><?php echo $prods['per_item_price'] ?></label>
                                    </td>

                                </tr>


                            <?php endforeach;

                            $count++;
                            endif;
                            ?>
							</tbody>
							</table>
						</div>
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>

			</div>
                <div class="form-actions">
                    <div class="row">
                        <?php
                        if ($pr_order != null):?>
                        <div class="form-group">
                            <label class="col-md-3 control-label">GRN Campus <span class="required">*</span></label>
                            <div class="col-md-9">
                                <select name="campus_id" class="form-control input-inline input-large select2" id="select2_sample1" required>
                                    <option value="" >Select Campus</option>
                                    <?php
                                    $campuses = $this->db->get_where("campuses","status = 1")->result_array();
                                    foreach ($campuses as $campus):
                                        ?>
                                        <option value="<?php echo $campus['campus_id']?>" ><?php echo $campus['campus_name'];?></option>
                                    <?php
                                    endforeach;
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-offset-3 col-md-9">
                            <button type="submit" class="btn green">Generate Grn</button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
                <!-- END PAGE CONTENT-->


    </div>
</div>
<!-- END CONTENT -->