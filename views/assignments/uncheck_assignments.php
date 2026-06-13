<?php
	$myAccess = checkUserAccess();
?>	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE CONTENT-->
            <?php if(@$this->session->userdata('message')):?>
                <div class="alert alert-success">
                    <button class="close" data-close="alert"></button>
                    <span>
                    <?php echo $this->session->userdata('message');?> </span>
                </div>
            <?php endif;?>
			<div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> Uncheck Assignments
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/assignments/uncheck_assignments">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Course <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="course_id" class="form-control input-inline input-large course_id" required>
                                                <option value="">Select Course</option>
												<?php
                                                	foreach ($courses as $course):
												?>
                                                <option value="<?php echo $course['course_id']?>"><?php echo $course['course_name']?></option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                        </div>
									</div>
									<div class="class">
									</div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Subject <span class="required">*</span></label>
                                        <div class="col-md-9 checkbox-list">
                                            <select name="subject_id" class="form-control input-inline input-large subject_id">
                                                
                                            </select>
                                        </div>
									</div>
									
									<div class="form-group">
										<label class="control-label col-md-3">Starting Date</label>
										<div class="col-md-3">
											<div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
												<input type="text" name="start_date" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
												<span class="input-group-btn">
												<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
												</span>
											</div>
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-md-3">End Date</label>
										<div class="col-md-3">
											<div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
												<input type="text" name="end_date" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
												<span class="input-group-btn">
												<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
												</span>
											</div>
										</div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<input type="hidden" name="submit" value="1" />
                                            <button type="submit" class="btn green">Check</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
			
			<?php
				if($this->input->post('submit')==1):
			?>
			<div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> All Unchecked Assignments
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
									 Students Details
								</th>
                                <th>
                                    Solved Assignment
                                </th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 1;
								foreach($assignments as $assignment):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
                                    <?php echo $i;?>
                                </td>
                                <td>
									 <?php
                                     echo 'Name : '.$assignment['first_name'].' '.$assignment['last_name'].'<br />';
                                     echo 'Roll No. : '.$assignment['roll_no'].'<br />';
                                     ?>
								</td>
                                <td>
                                    <a href="<?php echo site_url();?>/assignments/solvedassignment/<?php echo $assignment['assignment_id'];?>/<?php echo $assignment['student_id'];?>" class="btn blue" target="_blank"><i class="fa fa-file"></i> Check Assignment</a>
                                </td>
								<td>
                                    <a href="<?php echo site_url();?>/assignments/viewassignment/<?php echo $assignment['assignment_id'];?>" class="btn green" target="_blank"><i class="fa fa-eye"></i> View Assignment</a>
									<a href="<?php echo site_url();?>/assignments/viewsolveassignment/<?php echo $assignment['assignment_id'];?>" class="btn yellow" target="_blank"><i class="fa fa-eye"></i> View Solve Assignment</a>

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
			<?php
				endif;
			?>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->