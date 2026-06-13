
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Edit Profile <small>You can edit your profile here</small>
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
            <?php if(@$this->session->userdata('error')):?>
                <div class="alert alert-danger">
                    <button class="close" data-close="alert"></button>
                    <span>
                    <?php echo $this->session->userdata('error');?> </span>
                </div>
            <?php endif;?>
			<div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> Class Attendence Detail
							</div>
						</div>
                        <div class="portlet-body">
							<?php
								//echo '<pre>';
								//print_r($students);
								//echo '</pre>';
							?>
							<form action="<?php echo current_url();?>" method="post">
								<div class="row">
									<div class="col-md-6">
										<div class="form-group">
											<label class="control-label col-md-3">Start Date</label>
											<div class="col-md-3">
												<div class="input-group input-medium date date-picker" data-date="<?php echo $start_date?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
													<input type="text" name="start_date" class="form-control" value="<?php echo $start_date;?>" readonly>
													<span class="input-group-btn">
													<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
													</span>
												</div>
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label class="control-label col-md-3">End Date</label>
											<div class="col-md-3">
												<div class="input-group input-medium date date-picker" data-date="<?php echo $end_date?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
													<input type="text" name="end_date" class="form-control" value="<?php echo $end_date;?>" readonly>
													<span class="input-group-btn">
													<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
													</span>
												</div>
											</div>
										</div>
									</div>
								</div>
								<br />
								<div class="col-md-6">
									<div class="col-md-3"></div>
									<div class="col-md-9">
										<button type="submit" class="offset-3 btn green">Check</button>
									</div>
								</div>
							</form>
							<br class="clearfix" />
							<hr />
							<br />
							<table class="table table-striped table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
								<th>
                                	 Sr.
                                </th>
								<th>
									 Campus Name
								</th>
                                <th>
									 Course Name
								</th>
								<th>
									Class Name
								</th>
								<th>
									Student Image
								</th>
								<th>
									Roll No
								</th>
								<th>
									Student Name
								</th>
								<th>
									Mobile
								</th>
								<?php
									foreach($period as $key => $value):
								?>
								<th>
									<?php echo $value->format('Y-m-d');?>
								</th>
								<?php
									endforeach;
								?>
							</tr>
							</thead>
							<tbody>
                            <?php
								$i=1;
								foreach($students as $student):
							?>
                            <tr class="odd gradeX">
								<td>
                                	 <?php echo $i;?>
                                </td>
								<td>
									<?php echo $student['campus_name'];?>
								</td>
                                <td>
									<?php echo $student['course_name'];?>
								</td>
								<td>
									<?php echo $student['class_name'];?>
								</td>
								<td>
									<img src="<?php echo base_url();?>uploads/<?php echo @$this->db->get_where('student_documents',array('student_id'=>$student['student_id'],'type'=>'Photo'))->row()->image;?>" height="80" />
								</td>
								<td>
									<?php echo $student['roll_no'];?>
								</td>
								<td>
									<?php echo $student['first_name'].' '.$student['last_name'];?>
								</td>
								<td>
									<?php echo $student['mobile'].' | ',$student['emergency_no'];?>
								</td>
								<?php
									foreach($period as $key => $value):
								?>
									<?php 
										if($student['registration_date']<$value->format('Y-m-d'))
										{
											$this->db->where(array('time>='=>$value->format('Y-m-d').' 00:00:00','time<='=>$value->format('Y-m-d').' 23:59:00','machine_user_id'=>$student['machine_id']));
											$attendence_time = $this->db->get('attendence')->result_array();
											if(count($attendence_time)>0)
											{
												echo '<td class="success">Present <br />'.date('h:i:s A',strtotime($attendence_time[0]['time'])).' <br />'.$this->db->get_where('campuses',array('campus_code'=>$attendence_time[0]['campus_code']))->row()->campus_name.'</td>';
											}
											else
											{
												echo '<td class="danger">Absent</td>';
											}
										}
										else
										{
											echo '<td></td>';
										}
									?>
								<?php
									endforeach;
								?>
							</tr>
                            <?php
                            	$i++;
								endforeach;
							?>
							</tbody>
							</table>
						</div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->