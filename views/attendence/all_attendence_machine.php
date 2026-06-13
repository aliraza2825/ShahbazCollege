	
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
								<i class="fa fa-list"></i>All Attendance Machines
							</div>
						</div>
						<div class="portlet-body">
							<div class="table-toolbar">
								<div class="row">
									<div class="col-md-6">
										<div class="btn-group">
											<button onclick="location.href = '<?php echo site_url()?>/attendence/add_attendence_machine'" class="btn green">
											Add New <i class="fa fa-plus"></i>
											</button>
										</div>
									</div>
								</div>
							</div>
							<table class="table table-striped table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
                                <th>
									 ID
								</th>
                                
                                <th>
									 Machine ID
								</th>
                                <th>
									 Campus
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=1;
								foreach($machines as $machine):
								
							?>
                            <tr class="odd gradeX">
                                
                                <td>
									 <?php echo $i;?>
								</td>
                                <td>
									<?php echo  $machine['name']?>
								</td>
                               
								<td>
                                	<?php echo  $machine['campus_name']?>
                                    
								</td>
								<td>
								
                                	<a title="Edit" href="<?php echo site_url().'/attendence/edit_attendence_machine/'.$machine['id'];?>" class="btn blue"><i class="fa fa-edit"></i></a>
                                   
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