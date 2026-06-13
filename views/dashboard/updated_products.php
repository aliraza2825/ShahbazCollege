	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE CONTENT-->
            <?php if(@$this->session->userdata('message')):?>
                <div class="alert alert-success">
                    <button class="close" data-close="alert"></button>
                    <span>
                    <?php echo $this->session->userdata('message');?> </span>
                </div>
            <?php endif;?>
			<div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> Updated Products Requests
							</div>
						</div>
						<div class="portlet-body">
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
									 Purchase Slip
								</th>
                                <th>
									 Product Quantity
								</th>
                                <th>
									 Product Guarantee
								</th>
                                <th>
									 Guarantee Start Date
								</th>
                                <th>
									 Guarantee End Date
								</th>
                                <th>
									 Responsible Person
								</th>
                                <th>
									 Reponsibility Person
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
								foreach($updated_products as $product):
								//GET ORIGINAL PRODUCT BEFORE CHANGE
								$this->db->select('*');
								$this->db->from('products');
								$this->db->join('product_names','products.product_name_id=product_names.product_name_id','left');
								$this->db->where('products.product_id',$product['product_id']);
								$original_product = $this->db->get()->result_array();
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php 
									 	$campus_name = $this->db->get_where('campuses',array('campus_id'=>$product['campus_id']))->row()->campus_name;
										echo $campus_name;
									 ?>
									 <hr />
									 <br />
									 <?php 
									 	$old_campus_name = $this->db->get_where('campuses',array('campus_id'=>$original_product[0]['campus_id']))->row()->campus_name;
										if($campus_name!=$old_campus_name)
										{
											echo '<span class="alert-danger">'.$old_campus_name.'</span>';
										}
										else
										{
											echo $old_campus_name;
										}
									 ?>
								</td>
								<td>
									<?php 
										if($product['room_id']!=0)
										{
											$room_name = $this->db->get_where('rooms',array('room_id'=>$product['room_id']))->row()->room_name;
											echo $room_name;
										}
										else
										{
											echo 'Personal Use';
										}
									?>
									<hr />
									<?php 
										if($original_product[0]['room_id']!=0)
										{
											$old_room_name = $this->db->get_where('rooms',array('room_id'=>$original_product[0]['room_id']))->row()->room_name;
											if($room_name!=$old_room_name)
											{
												echo '<span class="alert-danger">'.$old_room_name.'</span>';
											}
											else
											{
												echo $old_room_name;
											}
										}
										else
										{
											echo 'Personal Use';
										}
									?>
								</td>
                                <td>
									<?php 
										if($product['subroom_id']!=0)
										{
											$subroom_name = $this->db->get_where('subrooms',array('subroom_id'=>$product['subroom_id']))->row()->subroom_name;
											echo $subroom_name;
										}
										else
										{
											echo 'N/A';
										}
									?>
									<hr />
									<?php 
										if($original_product[0]['subroom_id']!=0)
										{
											$old_subroom_name = $this->db->get_where('subrooms',array('subroom_id'=>$product['subroom_id']))->row()->subroom_name;
											if($subroom_name!=$old_subroom_name)
											{
												echo '<span class="danger">'.$old_subroom_name.'</span>';
											}
											else
											{
												echo $old_subroom_name;
											}
										}
										else
										{
											echo 'N/A';
										}
									?>
								</td>
                                <td>
									<?php 
										$product_name = $product['product_name'];
										echo $product_name;
									?>
									<hr />
									<?php 
										$old_product_name = $original_product[0]['product_name'];
										if($old_product_name!=$product_name)
										{
											echo '<span class="alert-danger">'.$old_product_name.'</span>';
										}
										else{
											echo $old_product_name;
										}
									?>
								</td>
								<td>
									<?php 
										$product_remarks = $product['remarks'];
										echo $product_remarks;
									?>
									<hr />
									<?php 
										$old_product_remarks = $original_product[0]['remarks'];
										if($product_remarks!=$old_product_remarks)
										{
											echo '<span class="alert-danger">'.$old_product_remarks.'</span>';
										}
										else
										{
											echo $old_product_remarks;
										}
									?>
								</td>
                                <td>
									<?php 
										if($product['purchase_slip']!='')
										{
											$ext = substr(strrchr($product['purchase_slip'], '.'), 1);
											if($ext=='jpg' || $ext=='jpeg' || $ext=='JPG' || $ext=='JPEG' || $ext=='png' || $ext=='PNG' )
											{
												echo '<a target="_blank" href="'.base_url().'/inventory_images/'.$product['purchase_slip'].'"><img src="'.base_url().'inventory_images/'.$product['purchase_slip'].'" width="100" /></a>';
											}
											else
											{
												echo '<a target="_blank" href="'.base_url().'/inventory_images/'.$product['purchase_slip'].'" class="btn green">Document</a>';
											}
										}
									?>
									<hr />
									<?php 
										if($original_product[0]['purchase_slip']!='')
										{
											$ext = substr(strrchr($original_product[0]['purchase_slip'], '.'), 1);
											if($ext=='jpg' || $ext=='jpeg' || $ext=='JPG' || $ext=='JPEG' || $ext=='png' || $ext=='PNG' )
											{
												echo '<a target="_blank" href="'.base_url().'/inventory_images/'.$original_product[0]['purchase_slip'].'"><img src="'.base_url().'inventory_images/'.$original_product[0]['purchase_slip'].'" width="100" /></a>';
											}
											else
											{
												echo '<a target="_blank" href="'.base_url().'/inventory_images/'.$original_product[0]['purchase_slip'].'" class="btn green">Document</a>';
											}
										}
									?>
								</td>
								<td>
									<?php 
										$product_quantity = $product['product_quantity'];
										echo $product_quantity;
									?>
									<hr />
									<?php 
										$old_product_quantity = $original_product[0]['product_quantity'];
										if($product_quantity!=$old_product_quantity)
										{
											echo '<span class="alert-danger">'.$old_product_quantity.'</span>';
										}
										else
										{
											echo $old_product_quantity;
										}
									?>
								</td>
                                <td>
									<?php 
										$product_guarantee = $product['product_guarantee'];
										if($product_guarantee==1)
										{echo 'Yes';}
										else{echo 'No';}
									?>
									<hr />
									<?php 
										$old_product_guarantee = $original_product[0]['product_guarantee'];
										if($product_guarantee!=$old_product_guarantee)
										{
											if($old_product_guarantee==1)
											{echo '<span class="alert-danger">Yes</span>';}
											else{echo '<span class="alert-danger">No</span>';}
										}
										else
										{
											if($old_product_guarantee==1)
											{echo 'Yes';}
											else{echo 'No';}
										}
									?>
								</td>
                                <td>
									<?php 
										$product_guarantee_start_date = $product['product_guarantee_start_date'];
										echo $product_guarantee_start_date;
									?>
									<hr />
									<?php 
										$old_product_guarantee_start_date = $original_product[0]['product_guarantee_start_date'];
										if($product_guarantee_start_date!=$old_product_guarantee_start_date)
										{
											echo '<span class="alert-danger">'.$original_product[0]['product_guarantee_start_date'].'</span>';
										}
										else
										{
											echo $original_product[0]['product_guarantee_start_date'];
										}
									?>
								</td>
                                <td>
									<?php 
										$product_guarantee_end_date = $product['product_guarantee_end_date'];
										echo $product_guarantee_end_date;
									?>
									<hr />
									<?php 
										$old_product_guarantee_end_date = $original_product[0]['product_guarantee_end_date'];
										if($product_guarantee_end_date!=$old_product_guarantee_end_date)
										{
											echo '<span class="alert-danger">'.$original_product[0]['product_guarantee_end_date'].'</span>';
										}
										else
										{
											echo $original_product[0]['product_guarantee_end_date'];
										}
									?>
								</td>
                                <td>
									<?php 
										$responsible_user = $this->db->get_where('users',array('user_id'=>$product['user_id']))->result_array();
										$responsible_user_name = $responsible_user[0]['first_name'].' '.$responsible_user[0]['last_name'];
										echo $responsible_user_name;
									?>
									<hr />
									<?php 
										$old_responsible_user = $this->db->get_where('users',array('user_id'=>$original_product[0]['user_id']))->result_array();
										$old_responsible_user_name = $old_responsible_user[0]['first_name'].' '.$old_responsible_user[0]['last_name'];
										if($responsible_user_name!=$old_responsible_user_name)
										{
											echo '<span class="alert-danger">'.$old_responsible_user_name.'</span>';
										}
										else
										{
											echo $old_responsible_user_name;
										}
									?>
								</td>
                                <td>
									<?php 
										$responsibility_user = $this->db->get_where('users',array('user_id'=>$product['reponsilble_user_id']))->result_array();
										$responsibility_user_name = $responsibility_user[0]['first_name'].' '.$responsibility_user[0]['last_name'];
										echo $responsibility_user_name;
									?>
									<hr />
									<?php 
										$old_responsibility_user = $this->db->get_where('users',array('user_id'=>$original_product[0]['reponsilble_user_id']))->result_array();
										$old_responsibility_user_name = $old_responsibility_user[0]['first_name'].' '.$old_responsibility_user[0]['last_name'];
										if($responsibility_user_name!=$old_responsibility_user_name)
										{
											echo '<span class="alert-danger">'.$old_responsibility_user_name.'</span>';
										}
										else
										{
											echo $old_responsibility_user_name;
										}
									?>
								</td>
                                <td>
									<?php 
										$add_by = $product['add_by'];
										echo $add_by;
									?>
									<hr />
									<?php 
										$add_by = $product['add_by'];
										echo $add_by;
									?>
								</td>
                                <td>
									<?php 
										$last_edit = $product['last_edit'];
										echo $last_edit;
									?>
									<hr />
									<?php 
										$old_last_edit = $original_product[0]['last_edit'];
										if($last_edit!=$old_last_edit)
										{
											echo '<span class="alert-danger">'.$old_last_edit.'</span>';
										}
										else
										{
											echo $old_last_edit;
										}
									?>
								</td>
								<td>
                                    <!--<a onclick="return confirm('Are you sure you want to clear this Product?')" href="<?php echo site_url().'/dashboard/clear_product/'.$product['product_id'];?>" title="Clear" class="btn green"><i class="fa fa-eye"></i> Clear</a>
                                    <a onclick="return confirm('Are you sure you want to delete this Product?')" href="<?php echo site_url().'/dashboard/delete_product/'.$product['product_id'];?>" title="Deleet" class="btn red"><i class="fa fa-trash"></i> Delete</a>-->
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
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->