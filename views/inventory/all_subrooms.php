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
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> All Sub-Rooms
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
									 Room No.
								</th>
                                <th>
                                	Sub-Room Name
                                </th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($subrooms as $subroom):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $subroom['campus_name']?>
								</td>
								<td>
									<?php echo $subroom['room_name']?>
								</td>
                                <td>
									<?php echo $subroom['room_no']?>
								</td>
                                <td>
									<?php echo $subroom['subroom_name']?>
								</td>
								<td>
									<?php
										if($myAccess[0]['edit_subroom']==1 || $this->session->userdata('role')=='Admin'):
									?>
                                    <a href="<?php echo site_url().'/inventory/edit_subroom/'.$subroom['subroom_id'];?>" title="Edit" class="btn blue"><i class="fa fa-edit"></i></a>
									<?php
										endif;
									?>
									<?php
										if($myAccess[0]['delete_subroom']==1 || $this->session->userdata('role')=='Admin'):
									?>
                                    <a onclick="return confirm('Are you sure you want to delete this Sub-Room?')" href="<?php echo site_url().'/inventory/delete_subroom/'.$subroom['subroom_id'];?>" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>
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
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->