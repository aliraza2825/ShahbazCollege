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
            <!-- BEGIN DASHBOARD STATS -->
			<div class="row">
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat blue-madison">
						<div class="visual">
							<i class="icon-map"></i>
						</div>
						<div  class="details">
							<div class="number">
								 <?php echo $count;?>
							</div>
							<div class="desc">
								 Contractors
							</div>
						</div>
						<!--<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>-->
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
								<i class="fa fa-list"></i>All Contractors
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
									 Contractor ID
								</th>
								<th>
									 Contractor Name
								</th>
                                <th>
									 College Name
								</th>
                                <th>
									 Contractor Contact No.
								</th>
                                <th>
									 College Contact No.
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($contractors as $contractor):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $contractor['contractor_id']?>
								</td>
                                <td>
									 <?php echo $contractor['contractor_id_from_college']?>
								</td>
								<td>
									<?php echo $contractor['name']?>
								</td>
                                <td>
                                	<?php echo $contractor['college_name']?>
                                </td>
                                <td>
									<?php echo $contractor['mobile']?>
                                    <br />
                                    <?php echo $contractor['emergency_no']?>
								</td>
                                <td>
									<?php echo $contractor['college_phone1']?>
                                    <br />
                                    <?php echo $contractor['college_phone2']?>
								</td>
								<td>
									<?php
                                    	if(@$myAccess[0]['contractor_edit']==1 || $this->session->userdata('role')=='Admin'):
									?>
                                    <a href="<?php echo site_url().'/contractors/edit_contractor/'.$contractor['contractor_id'];?>" title="Edit" class="btn blue"><i class="fa fa-edit"></i></a>
                                    <?php
                                    	endif;
									?>
                                    
                                    <a href="<?php echo site_url().'/contractors/contractor_documents/'.$contractor['contractor_id'];?>" title="Documents" class="btn green"><i class="fa fa-image"></i></a>
                                    
                                    <?php
                                    	if(@$myAccess[0]['contractor_delete']==1 || $this->session->userdata('role')=='Admin'):
									?>
                                    <a onclick="return confirm('Are you sure you want to delete this Contractor?')" href="<?php echo site_url().'/contractors/delete/'.$contractor['contractor_id'];?>" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>
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