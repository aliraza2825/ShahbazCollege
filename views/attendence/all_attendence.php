
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
								<i class="fa fa-list"></i> All Attendence
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/attendence/all_attendence" enctype="multipart/form-data">
								<div class="form-body">
									<div class="form-group">
										<label class="col-md-3 control-label">Staff / Students <span class="required">*</span></label>
										<div class="col-md-5">
											<select class="form-control input-large type" name="type">
												<option value="staff">Staff</option>
												<option value="student">Student</option>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-3 control-label">Campus Name</label>
										<div class="col-md-5">
											<select class="form-control input-large campus_id" name="campus_id">
												<option value="">SELECT CAMPUS</option>
												<?php
													foreach($campuses as $campus):
												?>
												<option value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name'];?></option>
												<?php
													endforeach;
												?>
											</select>
										</div>
									</div>
									<?php $shift_types = $this->db->get('shifts')->result_array()  ?>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Shift</label>
										<div class="col-md-5">
											<select class="form-control input-large" id="select2_sample1" name="shift[]" multiple>
											
												<?php 
													foreach($shift_types as $shift_type):
												?>
													<option value="<?php echo $shift_type['name'];?>"><?php echo $shift_type['name'];?></option>
												<?php
													endforeach;
												?>
											</select>
										</div>
									</div>
										<?php $study_types = $this->db->get('study_type')->result_array()  ?>
									
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Study Type</label>
                                                <div class="col-md-5">
                                                    <select class="form-control input-large campus" id="select2_sample2" name="study_type[]" multiple>
                                                
														<?php 
															foreach($study_types as $study_type):
														?>
															<option value="<?php echo $study_type['name'];?>"><?php echo $study_type['name'];?></option>
                                                        <?php
                                                        	endforeach;
														?>
                                                    </select>
                                                </div>
                                            </div>
                                        
									<div class="form-group class_session_container" style="display:none;">
										<label class="col-md-3 control-label">Session</label>
										<div class="col-md-5">
											<select class="form-control input-large class_session" name="class_session">
												
											</select>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Staff / Student Name <span class="required">*</span></label>
										<div class="col-md-5">
											<select class="form-control input-large staff" id="select2_sample4" name="machine_user_ids[]" multiple>
												
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-md-3">From Date</label>
										<div class="col-md-3">
											<div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
												<input type="text" name="from_date" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
												<span class="input-group-btn">
												<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
												</span>
											</div>
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-md-3">To Date</label>
										<div class="col-md-3">
											<div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
												<input type="text" name="to_date" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
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
											<button type="submit" class="btn green">Check Attendence</button>
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
								<i class="fa fa-list"></i> All Attendence
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
									 Date
								</th>
								<th>
									Campus Name
								</th>
								<th>
									Staff / Student Name
								</th>
								<?php
									if($this->input->post('type')=='student'):
								?>
								<th>
									 Class
								</th>
								<th>
									 Session
								</th>
								<th>
									 Present / Absent
								</th>
								<?php
									endif;
								?>
								<?php
									if($this->input->post('type')=='staff'):
								?>
                                <th>
									 Check In Time
								</th>
                                <th>
									 Check Out Time
								</th>
								<th>
									 Mark Attendence
								</th>
								<th>
									Action
								</th>
								<?php
									endif;
								?>
							</tr>
							</thead>
							<tbody>
                            <?php
                            	foreach($machine_user_ids as $machine_user_id):
								foreach($dates as $date):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                </td>
								<td>
                                	<?php echo date('F d, Y', strtotime($date));?>
								</td>
                                <?php
                                	if($this->input->post('type')=='staff'):
										$this->db->select('users.*,campuses.campus_name');
										$this->db->from('machine_data');
										$this->db->join('users','users.user_id=machine_data.teacher_student_id','inner');
										$this->db->join('campuses','campuses.campus_id=users.campus_id','inner');
										$this->db->where(array('machine_data.machine_id'=>$machine_user_id));
										$user = $this->db->get()->result_array();
										$name = $user[0]['first_name'].' '.$user[0]['last_name'];
										$campus_name = $user[0]['campus_name'];
									elseif($this->input->post('type')=='student'):
										$this->db->select('students.*,campuses.campus_name,classes.session');
										$this->db->from('machine_data');
										$this->db->join('students','students.student_id=machine_data.teacher_student_id','inner');
										$this->db->join('classes','classes.class_id=students.class_id','inner');
										$this->db->join('campuses','campuses.campus_id=classes.campus_id','inner');
										$this->db->where(array('machine_data.type'=>'student','machine_data.machine_id'=>$machine_user_id));
										$user = $this->db->get()->result_array();
										$name = $user[0]['first_name'].' '.$user[0]['last_name'];
										$campus_name = $user[0]['campus_name'];
										$class_session = $user[0]['session'];
										$class = $user[0]['section'];
									else:
									endif;
										
									$holiday = $this->db->get_where('holidays', array('date'=>$date))->result_array();
									if(count($holiday)>0):
								?>
                                <td>
                                	<?php echo $campus_name;?>
                                </td>
								<td>
                                	<?php echo $name;?>
									
                                </td>
								<?php
									if($this->input->post('type')=='student'):
								?>
								<td>
									<?php
										echo $class;
									?>
								</td>
								<td>
									<?php
										echo $class_session;
									?>
								</td>
								<td>
									 <p style="text-align:center;">Holiday</p>
								</td>
								<?php
									endif;
								?>
								<?php
									if($this->input->post('type')=='staff'):
								?>
								<td class="alert alert-success">
                                	<p style="text-align:center;">Holiday</p>
                                </td>
								<td class="alert alert-success">
                                	<p style="text-align:center;">Holiday</p>
                                </td>
								<?php
									endif;
								?>
								<?php
                                	else:
								?>
								<td>
									<?php
										echo $campus_name;
									?>
								</td>
								<td>
									<?php
										echo $name;
									?>
									<?php
									if($this->input->post('type')=='student'){
									
										echo "<br /><strong>Roll No : </strong>".$user[0]['roll_no']; 
										echo "<br /><strong>Study Type : </strong>".$user[0]['study_type'];
										echo "<br /><strong>Shift : </strong>".$user[0]['shift'];
										echo "<br /><strong>Contact : </strong>".$user[0]['mobile'];
										echo "<br /><strong>Emergency Contact : </strong>".$user[0]['emergency_no'];
										echo "<br /><strong>Study Campus : </strong>".@$this->db->get_where('campuses',array('campus_id'=>$user[0]['study_campus']))->row()->campus_name;
									}
									
								?>
								</td>
								<?php
									if($this->input->post('type')=='student'):
								?>
								<td>
									<?php
										echo $class;
									?>
								</td>
								<td>
									<?php
										echo $class_session;
									?>
								</td>
								<td>
									 <?php 
										$qry = 'SELECT * FROM attendence WHERE machine_user_id='.$machine_user_id.' AND (time>="'.$date.' 00:00:00" AND time<"'.$date.' 23:59:59") ORDER BY time ASC LIMIT 1';
										$checkin_time = $this->db->query($qry)->result_array();
										if(count($checkin_time)>0)
										{
											echo 'Present';
										}
										else
										{
											echo 'Absent';
										}
									?>
								</td>
								<?php
									endif;
								?>
								<?php
									if($this->input->post('type')=='staff'):
								?>
                                <td>
                                	<?php
										$qry = 'SELECT * FROM attendence WHERE machine_user_id='.$machine_user_id.' AND (time>="'.$date.' 00:00:00" AND time<"'.$date.' 23:59:59") ORDER BY time ASC LIMIT 1';
										$checkin_time = $this->db->query($qry)->result_array();
										if(count($checkin_time)>0)
										{
											echo @date('h:i:s A', strtotime($checkin_time[0]['time']));
										}
										else
										{
											echo 'Absent';
										}
									?>
								</td>
                                <td>
                                	<?php
										$qry = 'SELECT * FROM attendence WHERE machine_user_id='.$machine_user_id.' AND (time>="'.$date.' 00:00:00.00" AND time<"'.$date.' 23:59:59.999") ORDER BY time DESC LIMIT 1';
										$checkout_time = $this->db->query($qry)->result_array();
										if(count($checkout_time)>0)
										{
											echo @date('h:i:s A', strtotime($checkout_time[0]['time']));
										}
										else
										{
											echo 'Absent';
										}
									?>
								</td>
								<td>
									<?php
										if(count($checkin_time)>0)
										{
											$qry = 'SELECT * FROM attendence WHERE machine_user_id='.$machine_user_id.' AND (time>="'.$date.' 00:00:00.00" AND time<"'.$date.' 23:59:59.999") AND halfday=1 ORDER BY time DESC LIMIT 1';
											$half_time = $this->db->query($qry)->result_array();
											if(count($half_time)>0)
											{
												echo '<button class="btn yellow">Halfday</a>';
											}
											else
											{
												echo '<button class="btn green">Fullday<a/>';
											}
										}
										else
										{
											echo '<button class="btn red">Absent</a>';
										}
									?>
								</td>
								<td>
									<?php
										if($this->session->userdata('role')=='Admin' || $this->session->userdata('user_id')==77 && count($checkin_time)>0):
									?>
									<a href="<?php echo site_url();?>/attendence/delete_attendence/<?php echo $machine_user_id;?>/<?php echo $date;?>" class="btn red" title="Delete"><i class="fa fa-trash"></i></a>
									<a href="<?php echo site_url();?>/attendence/halfday/<?php echo $machine_user_id;?>/<?php echo $date;?>" class="btn yellow" title="Mark Halfday"><i class="fa fa-clock-o"></i></a>
									<?php
										endif;
									?>
								</td>
								<?php
                                	endif;
								?>
								<?php
                                	endif;
								?>
							</tr>
                            <?php
                            	endforeach;
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