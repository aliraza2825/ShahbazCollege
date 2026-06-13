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
								<i class="fa fa-list"></i>All Contracts
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
									 Contract ID
								</th>
								<th>
									 Contractor Name
								</th>
                                <th>
									 Campus Name
								</th>
                                <th>
									 Course
								</th>
                                <th>
                                	Contract Name
                                </th>
                                <th>
									 Session
								</th>
                                <th>
									 Contract Date
								</th>
                                <th>
									 Total Students
								</th>
                                <th>
									 Per Student Fee
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($contracts as $contract):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $contract['contract_id']?>
								</td>
                                <td>
									 <?php echo $contract['name']?>
								</td>
								<td>
									<?php echo $contract['campus_name']?>
								</td>
                                <td>
									<?php echo $contract['course_name']?>
								</td>
                                <td>
									<?php echo $contract['contract_name']?>
								</td>
                                <td>
                                	<?php echo $contract['session']?>
                                </td>
                                <td>
									<?php echo $contract['contract_date']?>
								</td>
                                <td>
									<?php echo $contract['total_students']?>
								</td>
                                <td>
									<?php echo $contract['per_student_fee']?>
								</td>
								<td>
                                    <a href="<?php echo site_url().'/contractors/edit_contract/'.$contract['contract_id'];?>" title="Edit" class="btn blue"><i class="fa fa-edit"></i></a>
                                    <a href="<?php echo site_url().'/contractors/contract_documents/'.$contract['contract_id'];?>" title="Documents" class="btn green"><i class="fa fa-image"></i></a>
                                    <a href="<?php echo site_url().'/contractors/contract_payments/'.$contract['contract_id'];?>" title="Payments" class="btn purple"><i class="fa fa-money"></i></a>
									<?php if($this->session->userdata('role')== 'Admin'): ?>
										<a onclick="return confirm('Are you sure you want to delete this Contractor?')" href="<?php echo site_url().'/contract/delete/'.$contract['contract_id'];?>" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>
										<a onclick="return confirm('Are you sure you want to reset this Contract fee plan?')" href="<?php echo site_url().'/contractors/contract_reset_plan/'.$contract['contract_id'];?>" title="Reset Plan" class="btn yellow"><i class="fa fa-refresh"></i></a>
									<?php endif; ?>
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