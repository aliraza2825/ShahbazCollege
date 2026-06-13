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
								<i class="fa fa-list"></i>All Visitors
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
									 Date
								</th>
								<th>
									 Name
								</th>
                                <th>
									 Phone
								</th>
                                <th>
									 Campus
								</th>
                                <th>
									 Note
								</th>
                                <th>
									 Priority
								</th>
                                <th>
									 Add By
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($visitors as $visitor):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $visitor['visitor_id']?>
								</td>
                                <td>
									 <?php echo date('d M, Y H:m:i a',strtotime($visitor['date']));?>
								</td>
								<td>
									<?php echo $visitor['name']?>
								</td>
                                <td>
									<?php echo $visitor['phone']?>
								</td>
                                <td>
									<?php echo $visitor['campus']?>
								</td>
                                <td>
									<?php echo $visitor['note']?>
								</td>
                                <td>
									<?php if($visitor['priority']=='1'){echo 'Yes';}else{echo 'No';}?>
								</td>
                                <td>
									<?php echo $visitor['add_by']?>
								</td>
								<td>
                                    <?php
                                    	if(@$myAccess[0]['visitor_edit']==1 || $this->session->userdata('role')=='Admin'):
									?>
                                    <a href="<?php echo site_url().'/visitors/edit_visitor/'.$visitor['visitor_id'];?>" title="Edit" class="btn blue"><i class="fa fa-edit"></i></a>
                                    <?php
                                    	endif;
									?>
                                    <?php
                                    	if(@$myAccess[0]['visitor_delete']==1 || $this->session->userdata('role')=='Admin'):
									?>
                                    <a onclick="return confirm('Are you sure you want to delete this Visitor?')" href="#" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>
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