	
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
								<i class="fa fa-pie-chart"></i>Report
							</div>
						</div>
                        <div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/students_performance">
								<div class="form-body">
                                  <div class="form-group">
                                      <label class="col-md-3 control-label">Roll Number <span class="required">*</span></label>
                                      <div class="col-md-9">
                                          <input type="text" class="form-control input-inline input-medium" name="roll_no" placeholder="Enter student's roll number" value="" required>
                                          <span class="help-inline"></span>
                                      </div>
                                  </div>
                                </div>
                                <div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Get Report</button>
										</div>
									</div>
								</div>
                            </form>
                        </div>    
                        <?php
							//if(count($reports)>0 && $this->input->post('roll_no')):
						?>
						<div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover">
							<thead>
							<tr>
								<th class="hidden">
                                	 hidden
                                </th>
								<th>
									 Student Name
								</th>
                                <th>
									 Date
								</th>
                                <th>
									 Subject
								</th>
								<th>
									 Result
								</th>
                                <th>
									 Paper
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($reports as $report):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
								<td>
									<?php echo $report['first_name'].' '.$report['last_name'];?>
								</td>
                                <td>
									<?php echo date('F d, Y', strtotime($report['date']));?>
								</td>
                                <td>
									<?php echo $report['subject'];?>
								</td>
								<td>
                                    <?php echo $report['marks'].'/'.$report['total_marks'];?>
								</td>
                                <td>
									<a target="_blank" href="<?php echo base_url().'uploads/'.$report['content'];?>"><button class="btn green"><i class="fa fa-eye"></i> View Paper</button></a>
								</td>
							</tr>
                            <?php
								$i++;
                            	endforeach;
							?>
							</tbody>
							</table>
						</div>
                        <?php
                        	//endif;
						?>
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->