	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			All Classes <small>Here you can find all classes</small>
			</h3>-->
            <!-- END PAGE HEADER-->
            <!-- BEGIN DASHBOARD STATS -->
			<div class="row">
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat blue-madison">
						<div class="visual">
							<i class="icon-map"></i>
						</div>
						<div  class="details">
							<div class="number">
								 <?php echo count($campuses);?>
							</div>
							<div class="desc">
								 Campuses
							</div>
						</div>
						<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
			</div>
			<!-- END DASHBOARD STATS -->
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
								<i class="fa fa-list"></i>All Campuses
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
									 Campus Code
								</th>
                                <th>
									 Campus Name
								</th>
                                <th>
									 Phones
								</th>
                                <th>
									 Address
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($campuses as $campus):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $campus['campus_code'];?>
								</td>
                                <td>
									 <?php echo $campus['campus_name'];?>
								</td>
                                <td>
									 <?php echo $campus['phone1'].' / '.$campus['phone2'];?>
								</td>
								<td>
									<?php echo $campus['address'];?>
								</td>
								<td>
                                    <a href="<?php echo site_url().'/archive/restore_campus/'.$campus['campus_id'];?>" title="Restore" class="btn blue"><i class="fa fa-refresh"></i></a>
									
									<?php $classes_attached = $this->db->get_where('classes',array('campus_id'=>$campus['campus_id']))->result_array();?>
									
                                    <a onclick="return confirm('Are you sure you want to delete this Campus Permanently? There are <?php echo count($classes_attached);?> classes attached with this campus.')" href="<?php echo site_url().'/archive/delete_campus/'.$campus['campus_id'];?>" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>
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