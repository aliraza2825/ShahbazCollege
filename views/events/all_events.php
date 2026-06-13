	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
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
								<i class="fa fa-list"></i>All Events
							</div>
						</div>
						<div class="portlet-body">
							<table class="table table-striped table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
                                <th class="hidden">
									 Hidden
								</th>
								<th>
									 Campuses
								</th>
                                <th>
									 Event Name
								</th>
                                <th>
									 Show On Website
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=1;
								foreach($events as $event):
								//CHECK CAMPUSES OF THIS USER
								$access = checkUserAccess();
								$campus_ids = @explode(',',$access[0]['campus_ids']);
								
								$campuses = explode(',',$event['campus_ids']);
								$result=array_intersect($campus_ids,$campuses);
								if($this->session->userdata('role')!='Admin' && count($result)>0):
							?>
                            <tr class="odd gradeX">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
								<td>
									<?php
                                    	$campuses = explode(',',$event['campus_ids']);
										foreach($campuses as $campus)
										{
											echo $this->db->get_where('campuses', array('campus_id'=>$campus))->row()->campus_name;
											echo '<br />';
										}
									?>
								</td>
                                <td>
									<?php echo $event['name'];?>
								</td>
                                <td>
									<?php
                                    	if($event['show_on_website']==1)
										{
											echo 'Yes';
										}
										else
										{
											echo 'No';
										}
									?>
								</td>
                                
								<td>
                                    <a title="Edit" href="<?php echo site_url().'/events/edit_event/'.$event['event_id'];?>" class="btn blue"><i class="fa fa-edit"></i></a>
                                    <a title="Delete" onclick="return confirm('Are you sure you want to delete this Event?')" href="<?php echo site_url().'/events/delete/'.$event['event_id'];?>" class="btn red"><i class="fa fa-trash"></i></a>
								</td>
							</tr>
                            <?php
                            	elseif($this->session->userdata('role')=='Admin'):
							?>
                            <tr class="odd gradeX">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
								<td>
									<?php
                                    	$campuses = explode(',',$event['campus_ids']);
										foreach($campuses as $campus)
										{
											echo $this->db->get_where('campuses', array('campus_id'=>$campus))->row()->campus_name;
											echo '<br />';
										}
									?>
								</td>
                                <td>
									<?php echo $event['name'];?>
								</td>
                                <td>
									<?php
                                    	if($event['show_on_website']==1)
										{
											echo 'Yes';
										}
										else
										{
											echo 'No';
										}
									?>
								</td>
                                
								<td>
                                    <a title="Edit" href="<?php echo site_url().'/events/edit_event/'.$event['event_id'];?>" class="btn blue"><i class="fa fa-edit"></i></a>
                                    <a title="Delete" onclick="return confirm('Are you sure you want to delete this Event?')" href="<?php echo site_url().'/events/delete/'.$event['event_id'];?>" class="btn red"><i class="fa fa-trash"></i></a>
								</td>
							</tr>
                            <?php
								endif;
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