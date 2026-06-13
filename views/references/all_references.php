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
								<i class="fa fa-list"></i> All References
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
									 ID
								</th>
                                <th>
									 Name
								</th>
								<th>
									 Phone
								</th>
                                <th>
									 Note
								</th>
								<th>
									 Total Students
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
								foreach($references as $reference):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $reference['reference_user_id']?>
								</td>
								<td>
									<?php echo $reference['name']?>
								</td>
                                <td>
									<?php echo $reference['phone']?>
								</td>
                                <td>
									<?php echo $reference['note']?>
								</td>
								<td>
									<a href="<?php echo site_url();?>/references/students/<?php echo $reference['reference_user_id']?>" class="btn purple"><?php echo $reference['total_students']?> Students</a>
								</td>
								<td>
									<?php
										if($reference['status']==1)
										{
											echo '<button type="button" class="btn green">Active</button>';
										}
										else
										{
											echo '<button type="button" class="btn red">Deactive</button>';
										}
									?>
								</td>
								<td>
                                    <a href="<?php echo site_url().'/references/edit_reference/'.$reference['reference_user_id'];?>" title="Edit" class="btn blue"><i class="fa fa-edit"></i></a>
                                    <a onclick="return confirm('Are you sure you want to delete this Reference User?')" href="<?php echo site_url().'/references/delete_reference/'.$reference['reference_user_id'];?>" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>
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