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
			<div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-search"></i> Search Document
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/inventory/all_documents">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="campus_id" class="form-control input-inline input-large campus">
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
                                        <label class="col-md-3 control-label">Room <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="room_id" class="form-control input-inline input-large rooms" >
                                            
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Sub-Room <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="subroom_id" class="form-control input-inline input-large subrooms" >
                                            
                                            </select>
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
			</div>
            <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i>All Documents
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
									 Clear By
								</th>
                                <th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($documents as $document):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $document['campus_name']?>
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
											echo '<a target="_blank" href="'.base_url().'/inventory_images/'.$document['picture'].'" class="btn green">Image</a>';
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
									<?php echo $document['clear_by']?>
								</td>
								<td>
                                    <a href="<?php echo site_url().'/inventory/edit_document/'.$document['document_id'];?>" title="Edit" class="btn blue"><i class="fa fa-edit"></i></a>
                                    <a onclick="return confirm('Are you sure you want to delete this Document?')" href="<?php echo site_url().'/inventory/delete_document/'.$document['document_id'];?>" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>
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