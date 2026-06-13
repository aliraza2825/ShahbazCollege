	
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
								<i class="icon-map"></i> All Classes Status
							</div>
						</div>
						<div class="portlet-body">
							<table  class="table table-striped table-bordered table-hover" id="sample_17">
							<thead>
							<tr>
                                <th>
									 Sr #
								</th>
                                <th>
									 Class &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								</th>
                                <th>
									 Total Active Students
								</th>
                                <th>
									 Total Seats
								</th>
                                <th>
									 Available Seats
								</th>
                                <th>
									 Total Decided Fees
								</th>
								<th>
									 Total Created Fees
								</th>
                                <th>
									 Total Paid Fees
								</th>
								<th>
									 Total Deactive Students
								</th>
								<th>
									 Total Decided Fees of Deactive Students
								</th>
								<th>
									 Total Created Fees of Deactive Students
								</th>
                                <th>
									 Total Paid Fees of Deactive Students
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=1;
								foreach($classes_status as $class_status):
							?>
                            <tr class="odd gradeX">
                                <td>
                                	<?php echo $i;?>
                                </td>
                                <td>
									 <a href="<?php echo site_url();?>/classes/students/<?php echo $class_status['class_id'];?>"><?php echo $class_status['name'];?></a>
								</td>
                                <td>
                                	<?php echo $class_status['total_students'];?>
								</td>
                                <td>
									<?php echo $class_status['seats'];?>
								</td>
                                <td>
                                	<?php echo $class_status['seats']-$class_status['total_students'];?>
								</td>
								<td>
                                	<?php echo totalStudentsDecidedFee($class_status['class_id']);?>
								</td>
                                <td>
                                    <?php
										echo totalStudentsFee($class_status['class_id']);
									?>
								</td>
                                <td>
                                    <?php
										echo totalStudentsPaidFee($class_status['class_id']);
									?>
								</td>
								<td>
                                    <?php
										echo totalDeactiveStudents($class_status['class_id']);
									?>
								</td>
								<td>
                                	<?php echo totalDeactiveStudentsDecidedFee($class_status['class_id']);?>
								</td>
								<td>
                                    <?php
										echo totalDeactiveStudentsFee($class_status['class_id']);
									?>
								</td>
								<td>
                                    <?php
										echo totalDeactiveStudentsPaidFee($class_status['class_id']);
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
                </div>
            </div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->