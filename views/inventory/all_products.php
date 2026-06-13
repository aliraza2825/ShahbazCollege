<?php
	$myAccess = checkUserAccess();
?>	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			All Classes <small>Here you can find all classes</small>
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
				<div class="col-md-6">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-search"></i> Search Product
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/inventory/all_products">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Campus</label>
                                        <div class="col-md-9">
                                            <select name="campus_id" class="form-control input-inline input-large select2 campus">
                                                <option value="">SELECT CAMPUS</option>
												<?php
                                                	foreach($campuses as $campus):
												?>
                                                <option value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name'];?></option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                        </div>
									</div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Room</label>
                                        <div class="col-md-9">
                                            <select name="room_id" class="form-control input-inline input-large select2 rooms" >
                                            
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Sub-Room</label>
                                        <div class="col-md-9">
                                            <select name="subroom_id" class="form-control input-inline input-large select2 subrooms" >
                                            
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Products Wise</label>
                                        <div class="col-md-6 radio-list">
                                            <label class="radio-inline">
                                                <input type="radio" name="type" id="optionsRadios4" value="single" checked /> Single Item </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="type" id="optionsRadios5" value="group"  /> Group Wise </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="type" id="optionsRadios6" value="consumed" /> Consumed Items </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="type" id="optionsRadios7" value="sold"  /> Sold Items </label>
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<input type="hidden" name="submit" value="1" />
											<button type="submit" class="btn green">Search</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>

                <div class="col-md-6">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-search"></i> Search Product
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/inventory/all_products">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Campus</label>
                                        <div class="col-md-9">
                                            <select name="campus_id" class="form-control input-inline input-large select2">
                                                <option value="">SELECT CAMPUS</option>
												<?php
                                                	foreach($campuses as $campus):
												?>
                                                <option value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name'];?></option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                        </div>
									</div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Product</label>
                                        <div class="col-md-9">
                                            <select name="product_name_id" class="form-control input-inline input-large select2" required>
                                                <option value="">SELECT PRODUCT</option>
												<?php
                                                	foreach($product_names as $product_name):
												?>
                                                <option value="<?php echo $product_name['product_name_id'];?>"><?php echo $product_name['product_name'];?></option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                        </div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<input type="hidden" name="productwise_submit" value="1" />
											<button type="submit" class="btn green">Search</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>

            <?php
                if(@$this->input->post('submit')==1):
            ?>

            <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i>All Products
							</div>
                            <button id="print-btn" type="button" class="btn btn-primary btn-sm d-print-none"><i class="dripicons-print"></i> Print</button>
						</div>
						<div class="portlet-body" id="printable_body">
                            <?php if ($this->input->post('type') == 'single'): ?>
							    <table class="table table-striped table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
								<th class="hidden">
                                	 hidden
                                </th>
                                <th>
									 Campus Name
								</th>
								<th>
									 Room Name
								</th>
                                <th>
									 Sub-Room Name
								</th>
                                <th>
									 Product Name
								</th>
								<th>
									 Product Remarks
								</th>
                                <th>
									 Product QR
								</th>
                                <th>
									 Status
								</th>
                                <th>
									 Add By
								</th>
                                <th>
									 Edit By
								</th>
                                <th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($products as $product):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $product['campus_name']?>
								</td>
								<td>
                                    <?php
                                        echo $product['room_name'];
                                    ?>
                                    </td>
                                    <td>
                                        <?php
                                            echo $product['subroom_name'];
                                        ?>
                                    </td>
                                <td>
									<?php echo $product['product_name']?>
								</td>
								<td>
									<?php echo $product['remarks']?>
								</td>
								<td>
									<?php echo $product['qr_code']?>
								</td>
                                <td>
                                    <?php
                                        if($product['consume']==1)
                                        {
                                            echo '<button type="button" class="btn yellow-crusta">Consumed</button>';
                                        }
                                        elseif($product['sold']==1)
                                        {
                                            echo '<button type="button" class="btn red">Sold</button>';
                                        }
                                        else
                                        {
                                            echo '<button type="button" class="btn green">Active</button>';
                                        }
                                    ?>
                                </td>
                                <td>
									<?php echo $product['add_by']?>
								</td>
                                <td>
									<?php echo $product['last_edit']?>
								</td>
								<td>
                                    <?php
                                        if($product['consume']==0 && $product['sold']==0):
                                    ?>
                                        <a href="<?php echo site_url().'/inventory/edit_product/'.$product['product_id'];?>" title="Edit" class="btn blue"><i class="fa fa-edit"></i></a>
                                        <!--<a  href="<?php echo site_url().'/inventory/move_product/'.$product['product_id'];?>" title="Move Item" class="btn green"><i class="fa fa-arrow-down"></i>Move Item</a>-->
                                        <a  data-toggle='modal' data-product-id='<?php echo $product['product_id'];?>' data-quantity='1' href='#move_item' title="Move Item" class="btn yellow move_item"><i class="fa fa-exchange"></i> Move Item</a>

                                        <a  data-toggle='modal' data-product-id='<?php echo $product['product_id'];?>' href='#move_item_history' title="Move Item Hsitory" class="btn green move_item_history"><i class="fa fa-history"></i> History</a>
                                        <?php
                                            if($product['consumeable']==1 && $product['consume']==0):
                                        ?>

                                        <!--<a onclick="return confirm('Are you sure this product has been consumed?')" href="<?php echo site_url().'/inventory/consume_product/'.$product['product_id'];?>" title="Consume" class="btn yellow-crusta"><i class="fa fa-ban"></i> Consume</a>-->

                                        <a  data-toggle='modal' data-product-id='<?php echo $product['product_id'];?>' data-quantity='1' href='#consume_products' title="Consume" class="btn yellow-crusta consume_products"><i class="fa fa-ban"></i> Consume</a>

                                        <a  data-toggle='modal' data-product-id='<?php echo $product['product_id'];?>'  href='#consume_item_history' title="Consume Item Hsitory" class="btn green consume_item_history"><i class="fa fa-history"></i> Consume History</a>
                                        <?php
                                            endif;
                                        ?>
                                        <a onclick="return confirm('Are you sure you want to delete this Product?')" href="<?php echo site_url().'/inventory/delete_product/'.$product['product_id'];?>" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>
                                        
                                        <?php
                                            if($product['product_image']):
                                                if($product['online_product_image']!=''):
                                                    echo '<a class="btn purple" href="'.str_replace($bucket_address,$cloudfront_address,$product['online_product_image']).'" target="_blank"><i class="fa fa-image"></i></a>';
                                                else:
                                                    echo '<a class="btn purple" href="'.base_url().'inventory_images/'.$product['product_image'].'" target="_blank"><i class="fa fa-image"></i></a>';
                                                endif;
                                            endif;
                                        ?>
                                        <?php
                                            if($product['purchase_slip']):
                                                if($product['online_purchase_slip']!=''):
                                                    echo '<a class="btn purple" href="'.str_replace($bucket_address,$cloudfront_address,$product['online_purchase_slip']).'" target="_blank"><i class="fa fa-image"></i></a>';
                                                else:
                                                    echo '<a class="btn purple" href="'.base_url().'inventory_images/'.$product['purchase_slip'].'" target="_blank"><i class="fa fa-image"></i></a>';
                                                endif;
                                            endif;
                                        ?>
                                    <?php
                                        elseif($product['consume']==1 && $product['sold']==0):
                                    ?>
                                        <a  data-toggle='modal' data-product-id='<?php echo $product['product_id'];?>' href='#move_item_history' title="Move Item Hsitory" class="btn green move_item_history"><i class="fa fa-history"></i> History</a>

                                        <a  data-toggle='modal' data-product-id='<?php echo $product['product_id'];?>'  href='#consume_item_history' title="Consume Item Hsitory" class="btn green consume_item_history"><i class="fa fa-history"></i> Consume History</a>
                                    <?php
                                        //elseif($product['consume']==0 && $product['sold']==1):
                                    ?>
                                        <!--<a  data-toggle='modal' data-product-id='<?php //echo $product['product_id'];?>' href='#move_item_history' title="Move Item Hsitory" class="btn green move_item_history"><i class="fa fa-history"></i> History</a>-->
                                    <?php
                                        endif;
                                    ?>
                                    <!--<a  data-toggle='modal' data-id='<?php echo $i;?>' href='#purchased' title="Sale Product" class="btn btn-warning open-purchase_approval"><i class="fa fa-shopping-cart"></i>Sale Product</a>-->
                                </td>
							</tr>
                            <?php
								$i++;
                            	endforeach;
							?>
							</tbody>
							</table>
						    <?php elseif($this->input->post('type') == 'group'): ?>
                                <table class="table table-striped table-bordered table-hover" id="sample_3">
                                <thead>
                                <tr>
                                    <th class="hidden">
                                        hidden
                                    </th>
                                    <th>
                                        Campus Name
                                    </th>
                                    <th>
                                        Room Name
                                    </th>
                                    <th>
                                        Sub-Room Name
                                    </th>
                                    <th>
                                        Product Name
                                    </th>
                                    <th>
                                        Product Quantity
                                    </th>
                                    <th>
                                        Status
                                    </th>
                                    <th>
                                        Action
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $i = 0;
                                foreach($products as $product):
                                    ?>
                                    <tr class="odd gradeX">
                                        <td class="hidden">
                                            <?php echo $i;?>
                                        </td>
                                        <td>
                                            <?php echo $product['campus_name']?>
                                        </td>
                                        <td>
                                            <?php
                                                echo $product['room_name'];
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                                echo $product['subroom_name'];
                                            ?>
                                        </td>
                                        <td>
                                            <?php echo $product['product_name']?>
                                        </td>
                                        <td>
                                            <?php echo $product['remaining_quantity']?>
                                        </td>
                                        <td>
                                            <?php
                                                if($product['consume']==1)
                                                {
                                                    echo '<button type="button" class="btn yellow-crusta">Consumed</button>';
                                                }
                                                elseif($product['sold']==1)
                                                {
                                                    echo '<button type="button" class="btn red">Sold</button>';
                                                }
                                                else
                                                {
                                                    echo '<button type="button" class="btn green">Active</button>';
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                                if($product['consume']==0 && $product['sold']==0):
                                            ?>
                                                <a href="<?php echo site_url();?>/inventory/edit_bulk_products/<?php echo $product['product_id'];?>" title="Edit Items" class="btn blue"><i class="fa fa-edit"></i> Edit</a>
                                                
                                                <a  data-toggle='modal' data-product-id='<?php echo $product['product_id'];?>' data-quantity='<?php echo $product['remaining_quantity']?>' href='#move_item' title="Move Item" class="btn yellow move_item"><i class="fa fa-exchange"></i> Move Item</a>

                                                <a  data-toggle='modal' data-product-id='<?php echo $product['product_id'];?>' href='#move_item_history' title="Move Item Hsitory" class="btn green move_item_history"><i class="fa fa-history"></i> History</a>
                                                
                                                <?php
                                                    if($product['consumeable']==1 && $product['consume']==0):
                                                ?>
                                                <a  data-toggle='modal' data-product-id='<?php echo $product['product_id'];?>' data-quantity='<?php echo $product['remaining_quantity']?>' href='#consume_products' title="Consume" class="btn yellow-crusta consume_products"><i class="fa fa-ban"></i> Consume</a>
                                                <?php
                                                    endif;
                                                ?>
                                            <?php
                                                elseif($product['consume']==1 && $product['sold']==0):
                                            ?>
                                                <a  data-toggle='modal' data-product-id='<?php echo $product['product_id'];?>' href='#move_item_history' title="Move Item Hsitory" class="btn green move_item_history"><i class="fa fa-history"></i> Move History</a>

                                                <a  data-toggle='modal' data-product-id='<?php echo $product['product_id'];?>'  href='#consume_item_history' title="Consume Item Hsitory" class="btn green consume_item_history"><i class="fa fa-history"></i> Consume History</a>
                                            <?php
                                                //elseif($product['consume']==0 && $product['sold']==1):
                                            ?>
                                                <!--<a  data-toggle='modal' data-product-id='<?php echo $product['product_id'];?>' href='#move_item_history' title="Move Item Hsitory" class="btn green move_item_history"><i class="fa fa-history"></i> Move History</a>-->
                                            <?php
                                                endif;
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                    $i++;
                                endforeach;
                                ?>
                                </tbody>
                            </table>
                            <?php elseif($this->input->post('type') == 'consumed'): ?>
                                <table class="table table-striped table-bordered table-hover" id="sample_3">
                                <thead>
                                <tr>
                                    <th class="hidden">
                                        hidden
                                    </th>
                                    <th>
                                        Campus Name
                                    </th>
                                    <th>
                                        Room Name
                                    </th>
                                    <th>
                                        Sub-Room Name
                                    </th>
                                    <th>
                                        Product Name
                                    </th>
                                    <th>
                                        Product Quantity
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $i = 0;
                                foreach($products as $product):
                                    ?>
                                    <tr class="odd gradeX">
                                        <td class="hidden">
                                            <?php echo $i;?>
                                        </td>
                                        <td>
                                            <?php echo $product['campus_name']?>
                                        </td>
                                        <td>
                                            <?php
                                                echo $product['room_name'];
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                                echo $product['subroom_name'];
                                            ?>
                                        </td>
                                        <td>
                                            <?php echo $product['product_name']?>
                                        </td>
                                        <td>
                                            <?php echo $product['remaining_quantity']?>
                                        </td>
                                    </tr>
                                    <?php
                                    $i++;
                                endforeach;
                                ?>
                                <?php elseif($this->input->post('type') == 'sold'): ?>
                                <table class="table table-striped table-bordered table-hover" id="sample_3">
                                <thead>
                                <tr>
                                    <th class="hidden">
                                        hidden
                                    </th>
                                    <th>
                                        Campus Name
                                    </th>
                                    <th>
                                        Room Name
                                    </th>
                                    <th>
                                        Sub-Room Name
                                    </th>
                                    <th>
                                        Product Name
                                    </th>
                                    <th>
                                        Product Quantity
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $i = 0;
                                foreach($products as $product):
                                    ?>
                                    <tr class="odd gradeX">
                                        <td class="hidden">
                                            <?php echo $i;?>
                                        </td>
                                        <td>
                                            <?php echo $product['campus_name']?>
                                        </td>
                                        <td>
                                            <?php
                                                echo $product['room_name'];
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                                echo $product['subroom_name'];
                                            ?>
                                        </td>
                                        <td>
                                            <?php echo $product['product_name']?>
                                        </td>
                                        <td>
                                            <?php echo $product['remaining_quantity']?>
                                        </td>
                                    </tr>
                                    <?php
                                    $i++;
                                endforeach;
                                ?>
                                </tbody>
                            </table>
                            <?php endif; ?>
                        </div>
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>
            <?php
                endif;
            ?>
            <?php
                if(@$this->input->post('productwise_submit')==1):
            ?>
            <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i>All Products
							</div>
                            <button id="print-btn" type="button" class="btn btn-primary btn-sm d-print-none"><i class="dripicons-print"></i> Print</button>
						</div>
						<div class="portlet-body" id="printable_body">
						    <?php //elseif($this->input->post('type') == 'group'): ?>
                                <table class="table table-striped table-bordered table-hover" id="sample_3">
                                <thead>
                                <tr>
                                    <th class="hidden">
                                        hidden
                                    </th>
                                    <th>
                                        Campus Name
                                    </th>
                                    <th>
                                        Room Name
                                    </th>
                                    <th>
                                        Sub-Room Name
                                    </th>
                                    <th>
                                        Product Name
                                    </th>
                                    <th>
                                        Product Quantity
                                    </th>
                                    <th>
                                        Status
                                    </th>
                                    <th>
                                        Action
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $i = 0;
                                foreach($products as $product):
                                    ?>
                                    <tr class="odd gradeX">
                                        <td class="hidden">
                                            <?php echo $i;?>
                                        </td>
                                        <td>
                                            <?php echo $product['campus_name']?>
                                        </td>
                                        <td>
                                            <?php
                                                echo $product['room_name'];
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                                echo $product['subroom_name'];
                                            ?>
                                        </td>
                                        <td>
                                            <?php echo $product['product_name']?>
                                        </td>
                                        <td>
                                            <?php echo $product['remaining_quantity']?>
                                        </td>
                                        <td>
                                            <?php
                                                if($product['consume']==1)
                                                {
                                                    echo '<button type="button" class="btn yellow-crusta">Consumed</button>';
                                                }
                                                elseif($product['sold']==1)
                                                {
                                                    echo '<button type="button" class="btn red">Sold</button>';
                                                }
                                                else
                                                {
                                                    echo '<button type="button" class="btn green">Active</button>';
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                                if($product['consume']==0 && $product['sold']==0):
                                            ?>
                                                <a href="<?php echo site_url();?>/inventory/edit_bulk_products/<?php echo $product['product_id'];?>" title="Edit Items" class="btn blue"><i class="fa fa-edit"></i> Edit</a>
                                                
                                                <a  data-toggle='modal' data-product-id='<?php echo $product['product_id'];?>' data-quantity='<?php echo $product['remaining_quantity']?>' href='#move_item' title="Move Item" class="btn yellow move_item"><i class="fa fa-exchange"></i> Move Item</a>

                                                <a  data-toggle='modal' data-product-id='<?php echo $product['product_id'];?>' href='#move_item_history' title="Move Item Hsitory" class="btn green move_item_history"><i class="fa fa-history"></i> History</a>
                                                
                                                <?php
                                                    if($product['consumeable']==1 && $product['consume']==0):
                                                ?>
                                                <a  data-toggle='modal' data-product-id='<?php echo $product['product_id'];?>' data-quantity='<?php echo $product['remaining_quantity']?>' href='#consume_products' title="Consume" class="btn yellow-crusta consume_products"><i class="fa fa-ban"></i> Consume</a>
                                                <?php
                                                    endif;
                                                ?>
                                            <?php
                                                elseif($product['consume']==1 && $product['sold']==0):
                                            ?>
                                                <a  data-toggle='modal' data-product-id='<?php echo $product['product_id'];?>' href='#move_item_history' title="Move Item Hsitory" class="btn green move_item_history"><i class="fa fa-history"></i> Move History</a>

                                                <a  data-toggle='modal' data-product-id='<?php echo $product['product_id'];?>'  href='#consume_item_history' title="Consume Item Hsitory" class="btn green consume_item_history"><i class="fa fa-history"></i> Consume History</a>
                                            <?php
                                                elseif($product['consume']==0 && $product['sold']==1):
                                            ?>
                                                <a  data-toggle='modal' data-product-id='<?php echo $product['product_id'];?>' href='#move_item_history' title="Move Item Hsitory" class="btn green move_item_history"><i class="fa fa-history"></i> Move History</a>
                                            <?php
                                                endif;
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                    $i++;
                                endforeach;
                                ?>
                                </tbody>
                            </table>
                        </div>
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>
            <?php
                endif;
            ?>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->
<div class="modal fade" id="purchased" tabindex="-1"   data-width="600" >
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Sale Item</h4>
    </div>
    <div class="modal-body">
        <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/inventory/sale_item_details">
            <div class="form-body">
                <div class="form-group">
                    <div class="col-md-12">
                        <label class="form-control" style="text-align: center" >Do you want to Sale this Item?</label>
                    </div>
                </div>

                <?php if ($myAccess[0]['sale_product'] === '1'): ?>
                    <div id = "apvdiv">
                        <div class="form-group" >
                            <label class="col-md-6 control-label">Item</label>
                            <div class="col-md-6" >
                                <label id="sale_item"></label>
                            </div>
                        </div>
                        <div class="form-group" >
                            <label class="col-md-6 control-label">Purchaser Name</label>
                            <div class="col-md-6" >
                                <input type="text" name="purchaser_name" required/>
                            </div>
                        </div>
                        <div class="form-group" >
                            <label class="col-md-6 control-label">Purchaser Contact</label>
                            <div class="col-md-6" >
                                <input type="number" name="purchaser_contact" required/>
                            </div>
                        </div>
                        <div class="form-group" >
                            <label class="col-md-6 control-label">Sale Amount</label>
                            <div class="col-md-6" >
                                <input type="number" name="sale_amount" required/>
                            </div>
                        </div>
                        <div class="form-group" >
                            <label class="col-md-6 control-label">Sale Quantity</label>
                            <div class="col-md-6" >
                                <input type="number" onchange="myFunction();" id="sale_qty" name="sale_qty" required/>
                            </div>
                        </div>
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-12" style="text-align: center">
                                    <input type="hidden" id="item_id" name="item_id" value="" />
                                    <input type="hidden" id="total_qty" name="total_qty" value="" />
                                    <input type="hidden" name="last_edit" value="<?php echo $this->session->userdata('name');?>" />
                                    <button type="submit" class="btn red">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
        </form>
    </div>
</div>


    <div class="modal fade" id="move_item" tabindex="-1" role="dialog"   data-width="600" >
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title">Move Item To</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <form  method="post" action="<?php echo site_url();?>/inventory/move" enctype="multipart/form-data">
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="campus_id" class="form-control input-inline input-large select2 campus" required>
                                                <option value="">SELECT CAMPUS</option>
                                                <?php
                                                    foreach($campuses as $campus):
                                                ?>
                                                <option value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name'];?></option>
                                                <?php
                                                    endforeach;
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <br /><br />
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Room <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="room_id" class="form-control input-inline select2 input-large rooms" required>
                                            
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <br /><br />
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Sub-Room</label>
                                        <div class="col-md-9">
                                            <select name="subroom_id" class="form-control input-inline input-large select2 subrooms">
                                            
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <br /><br />
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Quantity</label>
                                        <div class="col-md-9">
                                            <input type="number" min="1" max="" name="quantity" class="form-control quantity" value="" />
                                        </div>
                                    </div>
                                </div>
                                <br /><br /><br />
                                <div class="col-md-3">
                                    <input type="hidden" class="product_id" name="product_id" value=""/>
                                </div>
                                <div class="col-md-9">
                                    <button type="submit" class="btn green">Move Item</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="consume_products" tabindex="-1" role="dialog"   data-width="600" >
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title">Consume Products</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <form  method="post" action="<?php echo site_url();?>/inventory/consume_products" enctype="multipart/form-data">
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Reason</label>
                                        <div class="col-md-9">
                                            <input type="text" name="consume_reason" class="form-control consume_reason" value="" required/>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Quantity</label>
                                        <div class="col-md-9">
                                            <input type="number" min="1" max="" name="quantity" class="form-control quantity" value="" required/>
                                        </div>
                                    </div>
                                </div>
                                <br /><br /><br />
                                <div class="col-md-3">
                                    <input type="hidden" class="product_id" name="product_id" value=""/>
                                </div>
                                <div class="col-md-9">
                                    <button type="submit" class="btn green">Consume</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="move_item_history" tabindex="-1" role="dialog"   data-width="800" >
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title">Move Item History</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="co-md-12">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>Campus</th>
                            <th>Room</th>
                            <th>Subroom</th>
                            <th>Move By</th>
                            <th>Move On Date</th>
                        </tr>
                        </thead>
                        <tbody class="product_history">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="consume_item_history" tabindex="-1" role="dialog"   data-width="800" >
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title">Consume Item History</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="co-md-12">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>Campus</th>
                            <th>Room</th>
                            <th>Subroom</th>
                            <th>Consume Date</th>
                            <th>Consume Reason</th>
                        </tr>
                        </thead>
                        <tbody class="consume_history">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<script>

    function myFunction() {

        let tot=document.getElementById("total_qty").value;
        let x = document.getElementById("sale_qty").value;
        if (x>tot){
            alert('Sale Quantity is greater the Item Quantity ');
            document.getElementById("sale_qty").value = 0;
        }
    }
    document.addEventListener( "DOMContentLoaded", function(){
        $("#print-btn").on("click", function(){
            var divToPrint=document.getElementById('printable_body');
            var newWin=window.open('','Print-Window');
            newWin.document.open();
            newWin.document.write('<link rel="stylesheet" href="<?php echo base_url();?>assets/global/plugins/bootstrap/css/bootstrap.min.css" type="text/css"><style type="text/css">@media print {a[href]:after {content: "";} }</style><body onload="window.print()">'+divToPrint.innerHTML+'</body>');
            newWin.document.close();
        });
        var loans = <?php echo json_encode($products);?>;
        $(document).on("click", ".open-purchase_approval", function () {
            var myBookId = $(this).data('id');

            $(".modal-body #item_id").val(loans[myBookId].product_id );
            $(".modal-body #sale_item").html( loans[myBookId].product_name);
            $(".modal-body #sale_qty").val( loans[myBookId].product_quantity);
            $(".modal-body #total_qty").val( loans[myBookId].product_quantity);
        });

        $('.move_item').on('click',function(){
            var product_id = $(this).data('product-id');
            var quantity = $(this).data('quantity');

            $('.product_id').val(product_id);
            $('.campus').select2("val", "");
            $('.rooms').select2("val", "");
            $('.subrooms').select2("val", "");
            $('.quantity').removeAttr('max');
            $('.quantity').attr('max',quantity);
            $('.quantity').val(quantity);
        });

        $('.consume_products').on('click',function(){
            var product_id = $(this).data('product-id');
            var quantity = $(this).data('quantity');

            $('.product_id').val(product_id);
            $('.quantity').removeAttr('max');
            $('.quantity').attr('max',quantity);
            $('.quantity').val(quantity);
            $('.consume_reason').val('');
        });

        $('.move_item_history').on('click',function(){
            var product_id = $(this).data('product-id');
            jQuery.ajax({
                    type: "post",
                    async: false,
                    url: '<?php echo site_url()?>/inventory/getProductHistory',
                    data: {
                        product_id : product_id
                    },
                    success: function(data) {
                        jQuery('.product_history').html(data);
                    }
                });
        });

        $('.consume_item_history').on('click',function(){
            var product_id = $(this).data('product-id');
            jQuery.ajax({
                    type: "post",
                    async: false,
                    url: '<?php echo site_url()?>/inventory/getProductConsumeHistory',
                    data: {
                        product_id : product_id
                    },
                    success: function(data) {
                        jQuery('.consume_history').html(data);
                    }
                });
        });

    }, false );
</script>
