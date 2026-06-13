	
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
								<i class="fa fa-list"></i> Unclear Products
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
								foreach($unclear_products as $product):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php 
									 	echo $this->db->get_where('campuses',array('campus_id'=>$product['campus_id']))->row()->campus_name;
									 ?>
								</td>
								<td>
									<?php 
										if($product['room_id']!=0)
										{
											echo $this->db->get_where('rooms',array('room_id'=>$product['room_id']))->row()->room_name;
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
											echo $this->db->get_where('subrooms',array('subroom_id'=>$product['subroom_id']))->row()->subroom_name;
										}
										else
										{
											echo 'N/A';
										}
									?>
								</td>
                                <td>
									<?php echo $product['product_name']?>
								</td>
								<td>
									<?php echo $product['remarks']?>
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
								</td>
								<td>
									<?php echo $product['product_quantity']?>
								</td>
                                <td>
									<?php if($product['product_guarantee']==1){echo 'Yes';}else{echo 'No';}?>
								</td>
                                <td>
									<?php if($product['product_guarantee']==1){echo $product['product_guarantee_start_date'];}else{echo 'N/A';}?>
								</td>
                                <td>
									<?php if($product['product_guarantee']==1){echo $product['product_guarantee_end_date'];}else{echo 'N/A';}?>
								</td>
                                <td>
									<?php 
										$responsible_user = $this->db->get_where('users',array('user_id'=>$product['user_id']))->result_array();
										echo $responsible_user[0]['first_name'].' '.$responsible_user[0]['last_name'];
									?>
								</td>
                                <td>
									<?php 
										$responsibility_user = $this->db->get_where('users',array('user_id'=>$product['reponsilble_user_id']))->result_array();
										echo $responsibility_user[0]['first_name'].' '.$responsibility_user[0]['last_name'];
									?>
								</td>
                                <td>
									<?php echo $product['add_by']?>
								</td>
                                <td>
									<?php echo $product['last_edit']?>
								</td>
								<td>
                                    <a onclick="return confirm('Are you sure you want to clear this Product?')" href="<?php echo site_url().'/dashboard/clear_product/'.$product['product_id'];?>" title="Clear" class="btn green"><i class="fa fa-eye"></i> Clear</a>
                                    <a onclick="return confirm('Are you sure you want to delete this Product?')" href="<?php echo site_url().'/dashboard/delete_product/'.$product['product_id'];?>" title="Deleet" class="btn red"><i class="fa fa-trash"></i> Delete</a>
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