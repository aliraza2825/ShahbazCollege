	
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
								<i class="fa fa-list"></i> Unclear Documents
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
									 Document Name
								</th>
								<th>
									 Document Remarks
								</th>
								<th>
									 Picture
								</th>
                                <th>
									 Document Quantity
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
								foreach($unclear_documents as $document):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php 
									 	echo $this->db->get_where('campuses',array('campus_id'=>$document['campus_id']))->row()->campus_name;
									 ?>
								</td>
								<td>
									<?php 
										if($document['room_id']!=0)
										{
											echo $this->db->get_where('rooms',array('room_id'=>$document['room_id']))->row()->room_name;
										}
										else
										{
											echo 'Personal Use';
										}
									?>
								</td>
                                <td>
									<?php 
										if($document['subroom_id']!=0)
										{
											echo $this->db->get_where('subrooms',array('subroom_id'=>$document['subroom_id']))->row()->subroom_name;
										}
										else
										{
											echo 'N/A';
										}
									?>
								</td>
                                <td>
									<?php echo $document['document_name']?>
								</td>
								<td>
									<?php echo $document['remarks']?>
								</td>
                                <td>
									<?php 
										if($document['picture']!='')
										{
											$ext = substr(strrchr($document['picture'], '.'), 1);
											if($ext=='jpg' || $ext=='jpeg' || $ext=='JPG' || $ext=='JPEG' || $ext=='png' || $ext=='PNG' )
											{
												echo '<a target="_blank" href="'.base_url().'/inventory_images/'.$document['picture'].'"><img src="'.base_url().'inventory_images/'.$document['picture'].'" width="100" /></a>';
											}
											else
											{
												echo '<a target="_blank" href="'.base_url().'/inventory_images/'.$document['picture'].'" class="btn green">Document</a>';
											}
										}
									?>
								</td>
								<td>
									<?php echo $document['document_quantity']?>
								</td>
                                <td>
									<?php 
										$responsible_user = $this->db->get_where('users',array('user_id'=>$document['user_id']))->result_array();
										echo $responsible_user[0]['first_name'].' '.$responsible_user[0]['last_name'];
									?>
								</td>
                                <td>
									<?php 
										$responsibility_user = $this->db->get_where('users',array('user_id'=>$document['reponsilble_user_id']))->result_array();
										echo $responsibility_user[0]['first_name'].' '.$responsibility_user[0]['last_name'];
									?>
								</td>
                                <td>
									<?php echo $document['add_by']?>
								</td>
                                <td>
									<?php echo $document['last_edit']?>
								</td>
								<td>
                                    <a onclick="return confirm('Are you sure you want to clear this Document?')" href="<?php echo site_url().'/dashboard/clear_document/'.$document['document_id'];?>" title="Clear" class="btn green"><i class="fa fa-eye"></i> Clear</a>
                                    <a onclick="return confirm('Are you sure you want to delete this Document?')" href="<?php echo site_url().'/dashboard/delete_document/'.$document['document_id'];?>" title="Delete" class="btn red"><i class="fa fa-trash"></i> Delete</a>
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