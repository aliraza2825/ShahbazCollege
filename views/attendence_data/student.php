
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
								<i class="fa fa-user"></i> Student Attendence Detail
							</div>
						</div>
                        <?php
							if(count($students)>0):
						?>
                        <div class="portlet-body">
                            <div class="row">
								<div class="col-md-9">
									<div class="col-md-6">
										<p>Campus Name : <?php echo $students[0]['campus_name'];?></p>
									</div>
									<div class="col-md-6">
										<?php 
											$this->db->select('*');
											$this->db->from('courses');
											$this->db->where('course_id',$students[0]['course_id']);
											$course_name = $this->db->get()->result_array();
										?>
										<p>
											Course Name : <?php echo $course_name[0]['course_name'];?>
										</p>
									</div>
									<div class="col-md-6">
										<p>Student Name : <?php echo $students[0]['first_name'].' '.$students[0]['last_name'];?></p>
									</div>
									<div class="col-md-6">
										<p>Father Name : <?php echo $students[0]['father_name'];?></p>
									</div>
									<div class="col-md-6">
										<p>Student CNIC : <?php echo $students[0]['cnic'];?></p>
									</div>
									<div class="col-md-6">
										<p>Roll No : <?php echo $students[0]['roll_no'];?></p>
									</div>
									<div class="col-md-6">
										<p>Machine ID : <?php echo $students[0]['machine_id'];?></p>
									</div>
									<div class="col-md-6">
										<p>Student Type : <?php echo $students[0]['study_type'];?></p>
									</div>
									<div class="col-md-6">
										<p>Address : <?php echo $students[0]['address'];?></p>
									</div>
									<div class="col-md-6">
										<p>Shift : <?php echo $students[0]['shift'];?></p>
									</div>
									<div class="col-md-6">
										<p>Phone : <?php echo $students[0]['mobile'];?> | <?php echo $students[0]['emergency_no'];?></p>
									</div>
									<div class="col-md-6">
										<?php $contractor = $this->db->get_where('contractors',array('contractor_id'=>$students[0]['contractor_id']))->result_array();?>
										<p>Student of : <?php if(count($contractor)>0){echo $contractor[0]['name'].' (contractor)';}else{echo 'College';}?></p>
									</div>
									<div class="col-md-6">
										<p>Class : <?php echo $students[0]['name'];?></p>
									</div>
									<div class="col-md-6">
										<p>Session : <?php echo $students[0]['session'];?></p>
									</div>
								</div>
								<div class="col-md-3">
									<?php 
										$photo = $this->db->get_where('student_documents',array('student_id'=>$students[0]['student_id'],'type'=>'Photo'))->result_array();
									?>
									<img src="<?php echo base_url();?>uploads/<?php echo @$photo[0]['image'];?>" height="200" alt="Student Image" />
								</div>
							</div>
							<br />
							<br />
							<hr />
							<form action="<?php echo current_url();?>" method="post">
								<div class="row">
									<div class="col-md-6">
										<div class="form-group">
											<label class="control-label col-md-3">Start Date</label>
											<div class="col-md-3">
												<div class="input-group input-medium date date-picker" data-date="<?php echo $start_date?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years" data-date-start-date="-<?php echo $days?>d">
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
												<div class="input-group input-medium date date-picker" data-date="<?php echo $end_date?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years" data-date-start-date="-<?php echo $days?>d">
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
								<th class="hidden">
                                	 hidden
                                </th>
								<th>
									 Date
								</th>
                                <th>
									 Attendence Status
								</th>
								<th>
									Time
								</th>
							</tr>
							</thead>
							<tbody>
                            <?php
								$i=1;
								foreach($period as $key => $value):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
								<td>
									<?php echo $value->format('Y-m-d');?>
								</td>
                                <td>
									<?php 
										$this->db->where(array('time>='=>$value->format('Y-m-d').' 00:00:00','time<='=>$value->format('Y-m-d').' 23:59:00','machine_user_id'=>$students[0]['machine_id']));
										$attendence_time = $this->db->get('attendence')->result_array();
										if(count($attendence_time)>0)
										{
											echo 'Present';
										}
										else
										{
											echo 'Absent';
										}
									?>
								</td>
								<td>
									<?php 
										if(count($attendence_time)>0)
										{
											echo date("h:i:s A", strtotime($attendence_time[0]['time']));
										}
										else
										{
											echo 'N/A';
										}
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
                        <?php
							endif;
						?>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->