	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			All Students <small>Here you can find all students</small>
			</h3>-->
            <?php if(@$this->session->userdata('message')):?>
                <div class="alert alert-success">
                    <button class="close" data-close="alert"></button>
                    <span>
                    <?php echo $this->session->userdata('message');?> </span>
                </div>
            <?php endif;?>
			<!-- BEGIN PAGE CONTENT-->
			<div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i>All Students
							</div>
						</div>
						<div class="portlet-body">
							<?php
                            	if($this->session->userdata('role')=='Admin'):
							?>
                            <div class="table-toolbar">
								<div class="row">
									<div class="col-md-6">
										<div class="btn-group">
											<button onclick="location.href = '<?php echo site_url()?>/classes/all_classes'" class="btn green">
											<i class="fa fa-arrow-left"></i> Back 
											</button>
										</div>
									</div>
								</div>
							</div>
                            <?php
                            	endif;
							?>
							<table class="table table-striped table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
                                <th>
									 Roll #
								</th>
                                <th>
									 Name
								</th>
								<th>
									 Class
								</th>
                                <th>
									 CNIC
								</th>
                                <th>
									 Contact
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 1;
								foreach($students as $student):
							?>
                            <tr class="odd gradeX">
                                <td>
									<?php echo $student['roll_no']?>
								</td>
                                <td>
									 <?php echo $student['first_name'] . ' ' . $student['last_name'];?>
								</td>
								<td>
									<?php echo $student['class_name']?>
								</td>
                                <td>
									<?php echo $student['cnic']?>
								</td>
                                <td>
									<?php echo $student['mobile']?>
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