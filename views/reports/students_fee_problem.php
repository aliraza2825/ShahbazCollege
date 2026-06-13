	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			All Teachers <small>Here you can find all teachers</small>
			</h3>-->
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
                	<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-money"></i>Students Fee Problem
							</div>
						</div>
						<div class="portlet-body">
							<table  class="table table-striped table-bordered table-hover" id="sample_3">
							<thead>
							<tr>
                                <th class="hidden">
                                	Hidden
                                </th>
								<th>
									 Campus
								</th>
                                <th>
									 Students
								</th>
								<th>
									Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=1;
								foreach($campuses as $campus):
							?>
                            <tr class="odd gradeX">
                            	
                                <td class="hidden">
									 <?php echo $i;?>
								</td>
								<td>
									<?php echo $campus['campus_name'];?>
								</td>
                                <td>
									<?php
										$studentsFeeNotCreated = count(studentsFeeNotCreated($campus['campus_id']));
										echo $studentsFeeNotCreated;
									?>
								</td>
                                <td>
									<a target="_blank" href="<?php echo site_url();?>/reports/campus_fee_problem/<?php echo $campus['campus_id']?>" class="btn green">View</a>
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
                </div>
            </div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->