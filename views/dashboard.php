<?php
	$myAccess = checkUserAccess();
?>
<style>

.radio-group{
        position: relative;
    }

    .radio{
        display:inline-block;
        border-radius: 2px;
        width: 120px;
        border: 2px solid lightblue;
        cursor:pointer;
        margin: 5px 0;
        background: cadetblue;
    }

    .radio.selected{
        border-color: cadetblue;
        background-color: darkgreen;
        color: white;
    }

    /* Style the tab */
    .tab {
        overflow: hidden;
        border: 1px solid #ccc;
        background-color: #f1f1f1;
    }

    /* Style the buttons that are used to open the tab content */
    .tab button {
        background-color: inherit;
        float: left;
        border: none;
        outline: none;
        cursor: pointer;
        padding: 14px 16px;
        transition: 0.3s;
    }

    /* Change background color of buttons on hover */
    .tab button:hover {
        background-color: #ddd;
    }

    /* Create an active/current tablink class */
    .tab button.active {
        background-color: #26a69a;
    }

    /* Style the tab content */
    .tabcontent {
        display: none;
        padding: 6px 12px;
        border: 1px solid #ccc;
        border-top: none;
    }
    #accountsModal .modal-dialog {
        width: 90%;
        max-width: 1200px;
    }

    .report-loader{
        display:none;
        text-align:center;
        padding:40px 20px;
    }

    .report-loader .spinner{
        width:50px;
        height:50px;
        border:5px solid #ddd;
        border-top:5px solid #26a69a;
        border-radius:50%;
        animation:spin 1s linear infinite;
        margin:0 auto 15px;
    }

    @keyframes spin{
        100%{ transform:rotate(360deg); }
    }
</style>
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			<!-- END PAGE HEADER-->
			<!-- BEGIN DASHBOARD STATS -->
            
			<!-- END DASHBOARD STATS -->
			<div class="clearfix">
			</div>
            <?php
            	if(@$this->session->flashdata('message')):
			?>
            <div class="alert alert-success">
            	<p><?php echo $this->session->flashdata('message');?></p>
            </div>
            <?php 
				endif;
			?>
            <?php
            	if(@$this->session->flashdata('error')):
			?>
            <div class="alert alert-danger">
            	<p><?php echo $this->session->flashdata('error');?></p>
            </div>
            <?php 
				endif;
			?>
            
            <?php
				if(@$myAccess[0]['dashboard_check_student_box']==1 || $this->session->userdata('role')=='Admin'):
			?>
            <div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-user"></i> Check Student Record
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/dashboard/index" enctype="multipart/form-data">
								<div class="form-body">
									<div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Any Query <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="search" placeholder="Enter student's data" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<input type="submit" class="btn green" name="fee" value="Check Fee" />
                                            <input type="submit" class="btn green" name="student_check" value="Check Complete Status" />
										</div>
									</div>
								</div>
							</form>
						</div>
                        
                        <div class="portlet-body table-responsive">
							<table class="table table-bordered table-hover">
							<thead>
							<tr>
                                <th class="hidden">
									 Hidden
								</th>
                                <th>
									 Roll #
								</th>
								<th>
									 Name
								</th>
								<th>
									 Student Image
								</th>
                                <th>
									 CNIC
								</th>
                                <th>
									 Class
								</th>
                                <th>
									 Mobile
								</th>
                                <th>
									 Contractor
								</th>
                                <th>
									 Type
								</th>
								<th>
									Machine ID
								</th>
                                <th>
									 Documents
								</th>
                                <th>
                                	Result Remarks
                                </th>
								<th>
                                	Council Fee Remarks
                                </th>
                                <th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=0;
								if($this->input->post('search')):
								foreach($students as $student):
								$payment_plan = $this->db->get_where('payments', array('student_id'=>$student['student_id'], 'contract_id'=>0))->result_array();
								if(count($payment_plan)>0)
								{
									$payment_alert='';
								}
								elseif($student['contractor_id']>0)
								{
									$payment_alert='';
								}
								else
								{
									$payment_alert='alert alert-danger';
								}
							?>
                            <tr class="odd gradeX <?php echo $payment_alert;?>">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $student['roll_no'];?>
								</td>
								<td>
									<?php echo $student['first_name'].' '.$student['last_name'];?>
                                    <?php
                                    	if($student['status']==0)
										{
											$this->db->select('*');
											$this->db->from('freeze_student');
											$this->db->where("(freeze_student.student_id = '".$student['student_id']."')", NULL, FALSE);
											$freezedata = $this->db->get()->result_array();
											
											if(count($freezedata)>0)
											{
												echo '<br /><span class="blink_me" style="font-weight:bold;font-size:18px;color: blue;">FREEZED STUDENT</span>';
											}else{
												echo '<br /><span class="blink_me" style="font-weight:bold;font-size:18px;color:#F00;">DELETED STUDENT</span>';
											}
										}
									?>
								</td>
                                <td>
									<?php $student_image = $this->db->get_where('student_documents',array('student_id'=>$student['student_id'],'type'=>'Photo'))->result_array();?>
									<?php
										if(@$student_image[0]['online_image']==''):
									?>
									<img height="100" src="<?php echo base_url();?>uploads/<?php echo @$student_image[0]['image'];?>" alt="" />
									<?php
										else:
									?>
									<img height="100" src="<?php echo str_replace($bucket_address,$cloudfront_address,@$student_image[0]['online_image']);?>" alt="" />
									<?php
										endif;
									?>
								</td>
								<td>
									<?php echo $student['cnic']?>
								</td>
                                <td>
									<?php echo $student['class_name']?>
								</td>
                                <td>
									<?php echo $student['mobile'];?>
                                    <br />
                                    <?php echo $student['emergency_no'];?>
								</td>
                                <td>
									<?php 
										if($student['contract_id']==0)
										{
											echo 'N/A';
										}
										else
										{
											$contract = $this->db->get_where('contracts', array('contract_id'=>$student['contract_id']))->result_array();
											echo @$contract[0]['contract_name'].' ('.@$contract[0]['contract_date'].')';
										}
									?>
								</td>
                                <td>
                                	<?php echo $student['section'];?>
                                    <br />
                                    <?php echo $student['shift'];?>
                                    <br />
                                    <?php echo $student['study_type'];?>
                                    <br />
                                    <?php
                                    	if($student['student_card']==1)
										{
											echo 'Student Card Taken';
										}
									?>
                                </td>
								<td>
									<span class="bold"><?php echo $student['machine_id'];?></span>
								</td>
                                <td style="text-align:center">
                                	<?php
                                    	$id_card = $this->db->get_where('student_documents', array('type'=>'ID Card', 'student_id'=>$student['student_id']))->result_array();
										if(count($id_card)>0):
									?>
                                    <i class="fa fa-check"></i> ID card
                                    <br />
                                    <?php
                                    	endif;
									?>
                                    <?php
                                    	$photo = $this->db->get_where('student_documents', array('type'=>'Photo', 'student_id'=>$student['student_id']))->result_array();
										if(count($photo)>0):
									?>
                                    <i class="fa fa-check"></i> Photo
                                    <br />
                                    <?php
                                    	endif;
									?>
                                    <?php
                                    	$result_card = $this->db->get_where('student_documents', array('type'=>'Result Card', 'student_id'=>$student['student_id']))->result_array();
										if(count($result_card)>0):
									?>
                                    <i class="fa fa-check"></i> Result Card
                                    <?php
                                    	endif;
									?>
                                    
                                </td>
                                <td>
                                	<?php getStudentResultRemarks($student['cnic']);?>
                                </td>
								<td>
                                	<?php 
										$this->db->select('*');
										$this->db->from('expenses');
										$this->db->where(array('student_id'=>$student['student_id']));
										$council_fees = $this->db->get()->result_array();
										
										foreach($council_fees as $council_fee)
										{
											echo 'Exam No. : '.$council_fee['council_exam_no'];
											echo '<br />';
											echo 'Submit Date : '.$council_fee['date'];
											echo '<br />';
											echo 'Amount : '.$council_fee['amount'];
											echo '<br />';
											
											$this->db->select('*');
											$this->db->from('campuses');
											$this->db->where(array('campus_id'=>$council_fee['campus_id']));
											$camp = $this->db->get()->result_array();
											
											echo 'Campus : '.$camp[0]['campus_name'];
											echo '<br /><hr />';
										}
									?>
                                </td>
								<td>
                                	<?php
									if($student['status']==1):
									?>
									<a title="Attendence" href="<?php echo site_url().'/attendence_data/student/'.$student['student_id'];?>" class="btn blue"><i class="fa fa-calendar"></i></a>
									<?php
                                    	if(@$myAccess[0]['student_edit']==1 || $this->session->userdata('role')=='Admin'):
									?>
									<a title="SMS" href="<?php echo site_url().'/students/sms/'.$student['student_id'];?>" class="btn yellow"><i class="fa fa-envelope"></i></a>
                                    <?php
                                    	endif;
									?>
									<?php
                                    	if(@$myAccess[0]['student_edit']==1 || $this->session->userdata('role')=='Admin'):
									?>
									<a title="Edit" href="<?php echo site_url().'/students/edit_student/'.$student['student_id'];?>" class="btn blue"><i class="fa fa-edit"></i></a>
                                    <?php
                                    	endif;
									?>
                                    <?php
                                    	if(@$myAccess[0]['student_upload_documents']==1 || $this->session->userdata('role')=='Admin'):
									?>
                                    <a title="Documents" href="<?php echo site_url().'/students/upload_documents/'.$student['student_id'];?>" class="btn green"><i class="fa fa-image"></i></a>
                                    <?php
                                    	endif;
									?>
                                    
                                    <?php
                                    	if(@$student['contractor_id']==0):
									?>
                                    <?php
                                    	if(@$myAccess[0]['student_payments']==1 || $this->session->userdata('role')=='Admin'):
									?>
                                    <a title="Payments" href="<?php echo site_url().'/students/payments/'.$student['student_id'];?>" class="btn purple"><i class="fa fa-money"></i></a>
                                    <?php
                                    	endif;
									?>
                                    <?php
                                    	if(@$myAccess[0]['student_payment_reset']==1 || $this->session->userdata('role')=='Admin'):
									?>
                                    <a onclick="return confirm('Are you sure you want to reset this Student Fee Plan?')" href="<?php echo site_url().'/students/reset_plan/'.$student['student_id'];?>" title="Reset Plan" class="btn yellow"><i class="fa fa-refresh"></i></a>
                                    <?php
                                    	endif;
									?>
									<?php
                                    	endif;
									?>
                                    <?php
                                    	if(@$myAccess[0]['can_student_struckof']==1 || $this->session->userdata('role')=='Admin'):
										
										$this->db->select_sum('actual_amount');
										$this->db->where('student_id',$student['student_id']);
										$result = $this->db->get('payments')->row();  
										$total_fee_submit = $result->actual_amount;
										if($total_fee_submit=='')
										{
											$total_fee_submit=0;
										}
									?>
                                     <a title="Struck of Student" href="<?php echo site_url().'/Students/struckofstudentview/'.$student['student_id'];?>" class="btn blue"><i class="fa fa-ban"></i></a>
                                      <?php
                                    	endif;
									?>
									
									<?php
                                    if(@$myAccess[0]['can_student_struckof']==1 || $this->session->userdata('role')=='Admin'):
                                        ?>
                                        <a title="Freeze" href="<?php echo site_url().'/students/freezestudentview/'.$student['student_id'];?>" class="btn blue"><i class="fa fa-fire"></i></a>
                                    <?php
                                    endif;
                                    ?>
									
									
                                    <?php
                                    	endif;
									?>
								</td>
							</tr>
                            <?php
								$i++;
                            	endforeach;
								endif;
							?>
							</tbody>
							</table>
						</div>
                        
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
            <?php
            	endif;
			?>
			

			<div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> My Reminders
							</div>
						</div>
						<div class="portlet-body table-responsive">
							<table class="table table-bordered table-hover">
							<thead>
							<tr>
                                <th class="hidden">
									 Hidden
								</th>
                                <th>
                                	Date
                                </th>
                                <th>
                                	Note
                                </th>
                                <th>
									Image
								</th>
								<th>
									Assign By
								</th>
                                <th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=0;
								foreach($reminders as $reminder):
							?>
                            <tr class="odd gradeX">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $reminder['date'];?>
								</td>
                                <td>
									 <?php echo $reminder['note'];?>
								</td>
                                <td>
									<?php 
										if(@$reminder['image']):
											if(@$reminder['online_image']==''):
									?>
										<a class="btn green" href="<?php echo base_url().'reminder_images/'.$reminder['image'];?>" target="_blank">Image</a>
										<?php
											else:
										?>
										<a class="btn green" href="<?php echo str_replace($bucket_address,$cloudfront_address,@$reminder['online_image']);?>" target="_blank">Image</a>
									<?php
											endif;
										endif;
									?>
								</td>
								<td>
									<?php echo $reminder['add_by'];?>
								</td>
								<td>
									<?php
										if($reminder['status']=='Pending'):
									?>
									<a class="btn green" href="<?php echo site_url();?>/dashboard/update_reminder/<?php echo $reminder['reminder_id'];?>/Completed">Completed</a>
									<?php
										else:
									?>
									Under Review
									<?php
										endif;
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
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>
			<!----REMINDERS---->
			<!----MY LECTURE---->
			<?php
				if(@$myAccess[0]['council_report']==1 || $this->session->userdata('role')=='Admin'):?>
				<div class="row tab">
                    <button id='active_tab' class="tablinks active" style="margin-left: 10px;" onclick="loadReport('Active','active_tab');">Active</button>
                    <button id='inactive_tab' class="tablinks" onclick="loadReport('InActive','inactive_tab');">Inactive</button>
        
                    <div class="col-md-12">
                        <div class="portlet box grey-cascade">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-list"></i> Report Result
                                </div>
                                <button style="float:right; background-color: #26a69a; font-size: medium;" type="button" class="btn btn-primary btn-sm" id="total_liablity">
                                    Total Liablity : 0.00
                                </button>
                            </div>
        
                            <div class="portlet-body">
                                <div class="report-loader" id="reportLoader">
                                    <div class="spinner"></div>
                                    <div>Loading report, please wait...</div>
                                </div>
        
                                <div id="reportResult"></div>
                            </div>
                        </div>
                    </div>
                </div>
			<?php endif; ?>
			
			
			<?php
				if(count($lectures)>0):
			?>
			<div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-user"></i> My Lectures
							</div>
						</div>
						<div class="portlet-body table-responsive">
							<table class="table table-bordered table-hover">
								<thead>
								<tr>
									<th >
										ID
									</th>
									<th>
										Information
									</th>
									<th>
										Lecture Days
									</th>
									<th>
										Zoom ID
									</th>
									<th>
										Action
									</th>
								</tr>
								</thead>
								<tbody>
								<?php
								$i=0;
								foreach($lectures as $loan):
								?>
									<tr class="odd gradeX">
										<td >
											<?php echo $loan ['id'];?>
										</td>
										<td>
											<strong>Campus Name : </strong><?php  echo $loan ['campus_name'];?><br />
											<strong>Course Name : </strong><?php  echo $loan ['course_name'];?><br />
											<strong>Lecture Name : </strong><?php  echo $loan ['lecture_name'];?><br />
											<strong>Class Name : </strong><?php  echo $loan ['class'];?><br />
											<strong>Study Session : </strong><?php  echo $loan ['session'];?><br />
											<strong>Shift : </strong><?php  echo $shift_name = $this->db->get_where('shifts','id = "'.$loan ['shift'].'"')->row()->name;?><br />
											<strong>Study Type : </strong><?php  echo $study_type = $this->db->get_where('study_type','id = "'.$loan ['studytype'].'"')->row()->name;?><br />
											<strong>Room : </strong><?php  echo $loan ['room_name'];?><br />
											<strong>Subjects : </strong>
											<?php  $subjects = $this->db->where_in('course_subject_id',explode(',',$loan ['subjects']))->get('course_subjects')->result_array();
											foreach ($subjects as $subj) {
												echo $subj['subject_name'].' ';
											};?><br />
											<strong>Lecture Timing : </strong><?php  echo $loan ['start_date']. ' - ' .$loan ['end_date'];?><br />
											<strong>Teacher Name : </strong><?php  echo $loan ['first_name'].' '.$loan ['last_name'];?><br />
										</td>
										<td>
											<?php   $days = explode(',',$loan ['days']);
											foreach ($days as $day)
											{
												echo  "<label class='btn blue'  style='width: 100px'>".ucfirst($day)."</label>";
											}  ?>
										</td>
										<td><?php  echo $loan ['zoom_id'];  ?></td>
										<td>
											<?php
												$arr = explode(',',$loan ['subjects']);
												foreach($arr as $subs)
												{
		
													$sbd = $this->db->where('lecture_id = "'.$loan['id'].'" and subject_id = "'.$subs.'"')->get('session_syllabus')->result_array();
											?>
													<a class="btn btn-info" href="<?php echo site_url().'/schedule/view_session_syllabus/'.$subs.'/'.$loan['id'];?>" title="View" ><i class="fa fa-eye"></i> <?php echo $this->db->where('course_subject_id',$subs)->get('course_subjects')->row()->subject_name ?> Syllabus</a>
											<?php
		
												}
											?>
											<br />
											<br />
											<a href="<?php echo site_url();?>/schedule/view_merged_session_syllabus/<?php echo $loan['id'];?>" class="btn yellow" ><i class="fa fa-eye"></i> View Complete Syllabus (Merge)</a>
											<br />
											<br />
											<a href="<?php echo site_url();?>/timetable/today_lecture/<?php echo $loan['id'];?>" class="btn purple" ><i class="fa fa-table"></i> Today's Lecture</a>
											<a href="<?php echo site_url();?>/timetable/all_lectures/<?php echo $loan['id'];?>" class="btn green" ><i class="fa fa-table"></i> All Lecture</a>
											<br />
											<br />
											
											<a href="<?php echo site_url();?>/timetable/session_wise_students/<?php echo $loan ['id'];?>" class="btn red" ><i class="fa fa-calendar"></i> Mark Student Attendance</a>

											<?php if ($loan['zoom_id']): ?>
											<a data-toggle="modal" data-id="<?php echo $i ?>"  class="Open-assign_zoom btn btn-success" href="https://zoom.us/wc/<?php echo $loan ['zoom_id']; ?>/join">
												<i class="fa fa-fire"> Join Meeting</i>
											</a>
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
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
			<?php
				endif;
			?>
			<!----MY LECTURE---->
            <?php
				if(@$myAccess[0]['dashboard_campus_status_box']==1 || @$this->session->userdata('role')=='Admin'):
			?>
            <div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-building"></i> Campus Status
							</div>
						</div>
                        <div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/dashboard/index" enctype="multipart/form-data">
								<div class="form-body">
									<div class="row">
                                        <div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="control-label col-md-3">From Date</label>
                                                <div class="col-md-3">
                                                    <div class="input-group input-medium date date-picker" data-date="<?php echo $from_date;?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                        <input type="text" name="from_date" class="form-control" value="<?php echo $from_date;?>" readonly>
                                                        <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                                    </div>
                                                    <!-- /input-group -->
                                                    <!--<span class="help-block">
                                                    Select date </span>-->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="control-label col-md-3">To Date</label>
                                                <div class="col-md-3">
                                                    <div class="input-group input-medium date date-picker" data-date="<?php echo $to_date;?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                        <input type="text" name="to_date" class="form-control" value="<?php echo $to_date;?>" readonly>
                                                        <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                                    </div>
                                                    <!-- /input-group -->
                                                    <!--<span class="help-block">
                                                    Select date </span>-->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Date Type</label>
                                                <div class="col-md-6 radio-list">
                                                    <label class="radio-inline">
                                                    <input type="radio" name="date_type" id="optionsRadios4" value="paid_date" <?php if($date_type=='paid_date'){echo 'checked';}?> /> Submit Date </label>
                                                    <label class="radio-inline">
                                                    <input type="radio" name="date_type" id="optionsRadios5" value="actual_paid_date" <?php if($date_type=='actual_paid_date'){echo 'checked';}?> /> Upload Date </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<input type="submit" class="btn green" value="Submit" />
										</div>
									</div>
								</div>
							</form>
						</div>
                        <div class="portlet-body table-responsive">
							<table class="table table-bordered table-hover">
							<thead>
							<tr>
                                <th class="hidden">
									 Hidden
								</th>
                                <th>
									 Campus Name
								</th>
								<th>
									 New Admissions
								</th>
                                <th>
									 Fee Collect in Bank
								</th>
                                <th>
									 Fee Collect in College
								</th>
                                <th>
									 Total Amount
								</th>
                                <th>
									 Total Expense
								</th>
                                <th>
									 Net Profit
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=1;
                            $total_admissions = 0;
                            $cash_fee = 0;
                            $bank_fee = 0;
                            $total_fee = 0;
                            $total_expense = 0;
                            $total_profit = 0;
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
                                            $adss = getNewAdmissions($campus['campus_id'], $from_date, $to_date, $date_type);
                                            $total_admissions+=$adss;
                                            echo $adss
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $collegeFeeCollection=0;
                                            $collegeFeeCollection=getFeeCollectinCollege($campus['campus_id'], $from_date, $to_date, $date_type);
                                            $cash_fee += $collegeFeeCollection;
                                            echo 'Rs '.$collegeFeeCollection;
                                            ?>
                                        </td>
										<td>
                                            <?php
                                            $bankFeeCollection=0;
                                            $bankFeeCollection=getFeeCollectinBank($campus['campus_id'], $from_date, $to_date, $date_type);
                                            $bank_fee += $bankFeeCollection;
                                            echo 'Rs '.$bankFeeCollection;
                                            ?>
                                        </td>
                                        <td>
                                            Rs <?php
                                            $total_fee += ($bankFeeCollection+$collegeFeeCollection);
                                            echo ($bankFeeCollection+$collegeFeeCollection);?>
                                        </td>
                                        <td>
                                            <?php
                                            $totalExpenses=0;
                                            $totalExpenses=getCampusTotalExpense($campus['campus_id'], $from_date, $to_date, $date_type);
                                            $total_expense+=$totalExpenses;
                                            echo 'Rs '.$totalExpenses;
                                            ?>
                                        </td>
                                        <td>
                                            Rs <?php
                                            $total_profit += ($bankFeeCollection+$collegeFeeCollection-$totalExpenses);
                                            echo $bankFeeCollection+$collegeFeeCollection-$totalExpenses;
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                    $i++;
                                endforeach;
                            ?>
                            <tr class="odd gradeX">
                                <td class="hidden">
                                    <?php echo $i;?>
                                </td>
                                <td>

                                </td>
                                <td>
                                    <?php echo $total_admissions;?>
                                </td>
                                <td>
                                    <?php echo $cash_fee;?>
                                </td>
                                <td>
                                    <?php echo $bank_fee;?>
                                </td>
                                <td>
                                    <?php echo $total_fee;?>
                                </td>
                                <td>
                                    <?php echo $total_expense;?>
                                </td>
                                <td>
                                    <?php echo $total_profit;?>
                                </td>
                            </tr>

                            </tbody>
							</table>
						</div>
                        
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
            <?php
            	endif;
			?>
            
            <!--------->
            <div class="clearfix"></div>
			<hr />
			<h3 class="page-title">
			Clear Procedure <small>reports & statistics</small>
			</h3>
			<div class="portlet-body table-responsive">
				<table class="table table-bordered table-hover table-striped">
					<thead>
						<tr>
							<th>Campus Name</th>
							<?php
								if(@$myAccess[0]['dashboard_new_admisssion_entries_box']==1 || @$this->session->userdata('role')=='Admin'):
							?>
							<th>New Admissions</th>
							<?php
								endif;
							?>
							<?php
								if(@$myAccess[0]['dashboard_new_expense_entries_box']==1 || @$this->session->userdata('role')=='Admin'):
							?>
							<th>New Expense Entries</th>
							<?php
								endif;
							?>
							<?php
								if(@$myAccess[0]['dashboard_fee_status']==1 || $this->session->userdata('role')=='Admin'):
							?>
							<th>Fee Status</th>
							<?php
								endif;
							?>
							<?php
								if(@$myAccess[0]['dashboard_update_payment_box']==1 || $this->session->userdata('role')=='Admin'):
							?>
							<th>Update Fee Requests</th>
							<?php
								endif;
							?>
							<?php
								if(@$myAccess[0]['dashboard_update_student_box']==1 || $this->session->userdata('role')=='Admin'):
							?>
							<th>Update Students Requests</th>
							<?php
								endif;
							?>
							<?php
								if(@$myAccess[0]['dashboard_reminders_status']==1 || $this->session->userdata('role')=='Admin'):
							?>
							<th>Reminder Pending / UnderReview</th>
							<?php
								endif;
							?>
							<?php
								if(@$myAccess[0]['online_application_new_admissions']==1 || $this->session->userdata('role')=='Admin'):
							?>
							<th>New Applications</th>
							<th>Pending Applications</th>
							<?php
								endif;
							?>
						</tr>
					</thead>
					<tbody>
						<?php
							foreach($campuses as $campus):
						?>
						<tr>
							<td><?php echo $campus['campus_name'];?></td>
							<?php
								if(@$myAccess[0]['dashboard_new_admisssion_entries_box']==1 || @$this->session->userdata('role')=='Admin'):
							?>
							<?php 
								$dashboardNewAdmissions = count(dashboardNewAdmissions($campus['campus_id']));
							?>
							<td>
								<?php
									if($dashboardNewAdmissions>0):
								?>
								<a href="<?php echo site_url();?>/dashboard/new_admission_entries/<?php echo $campus['campus_id'];?>" class="btn red"><?php echo $dashboardNewAdmissions;?></a>
								<?php
									endif;
								?>
							</td>
							<?php
								endif;
							?>
							<?php
								if(@$myAccess[0]['dashboard_new_expense_entries_box']==1 || @$this->session->userdata('role')=='Admin'):
							?>
							<?php
								$dashboardNewExpenseEntries = count(dashboardNewExpenseEntries($campus['campus_id']));
							?>
							<td>
								<?php
									if($dashboardNewExpenseEntries>0):
								?>
								<a href="<?php echo site_url();?>/dashboard/new_expense_entries/<?php echo $campus['campus_id'];?>" class="btn red"><?php echo $dashboardNewExpenseEntries;?></a>
								<?php
									endif;
								?>
							</td>
							<?php
								endif;
							?>
							<?php
								if(@$myAccess[0]['dashboard_fee_status']==1 || $this->session->userdata('role')=='Admin'):
							?>
							<?php
								$feecount = count(dashboardFeeStaus($campus['campus_id']))+count(dashboardFeeStausContractors($campus['campus_id']));
							?>
							<td>
								<?php
									if($feecount>0):
								?>
								<a href="<?php echo site_url();?>/dashboard/fee_status/<?php echo $campus['campus_id'];?>" class="btn red"><?php echo $feecount;?></a>
								<?php
									endif;
								?>
							</td>
							<?php
								endif;
							?>
							<?php
								if(@$myAccess[0]['dashboard_update_payment_box']==1 || $this->session->userdata('role')=='Admin'):
							?>
							<?php
								$updatefeerequest = count(dashboardUpdateFeeRequests($campus['campus_id']))+count(dashboardUpdateFeeRequestsContractors($campus['campus_id']));
							?>
							<td>
								<?php
									if($updatefeerequest>0):
								?>
								<a href="<?php echo site_url();?>/dashboard/update_fee_status/<?php echo $campus['campus_id'];?>" class="btn red"><?php echo $updatefeerequest;?></a>
								<?php
									endif;
								?>
							</td>
							<?php
								endif;
							?>
							<?php
								if(@$myAccess[0]['dashboard_update_student_box']==1 || $this->session->userdata('role')=='Admin'):
							?>
							<?php
								$dashboardUpdateStudentRequests = count(dashboardUpdateStudentRequests($campus['campus_id']));
							?>
							<td>
								<?php
									if($dashboardUpdateStudentRequests>0):
								?>
								<a href="<?php echo site_url();?>/dashboard/students_edit_requests/<?php echo $campus['campus_id'];?>" class="btn red"><?php echo $dashboardUpdateStudentRequests;?></a>
								<?php
									endif;
								?>
							</td>
							<?php
								endif;
							?>
							<?php
								if(@$myAccess[0]['dashboard_reminders_status']==1 || $this->session->userdata('role')=='Admin'):
							?>
							<?php
								$dashboardPendingReminders = count(dashboardPendingReminders($campus['campus_id']));
								$dashboardRemindersUnderReview = count(dashboardRemindersUnderReview($campus['campus_id']));
							?>
							<td>
								<?php
									if($dashboardPendingReminders>0 || $dashboardRemindersUnderReview>0):
								?>
								<a href="<?php echo site_url();?>/reminders/all_pending_reminder/<?php echo $campus['campus_id'];?>" class="btn red"><?php echo $dashboardPendingReminders;?> / <?php echo $dashboardRemindersUnderReview;?></a>
								<?php
									endif;
								?>
							</td>
							<?php
								endif;
							?>
							
							<?php
								if(@$myAccess[0]['online_application_new_admissions']==1 || $this->session->userdata('role')=='Admin'):
							?>
							<?php
								$dashboardNewApplications = dashboardNewApplications($campus['campus_id']);
								$dashboardPendingApplications = dashboardPendingApplications($campus['campus_id']);
							?>
							<td>
								<?php
									if($dashboardNewApplications>0):
								?>
								<a href="<?php echo site_url();?>/online_application/new_applications/<?php echo $campus['campus_id'];?>" class="btn red"><?php echo $dashboardNewApplications;?></a>
								<?php
									endif;
								?>
							</td>
							<td>
								<?php
									if($dashboardPendingApplications>0):
								?>
								<a href="<?php echo site_url();?>/online_application/pending_applications/<?php echo $campus['campus_id'];?>" class="btn red"><?php echo $dashboardPendingApplications;?></a>
								<?php
									endif;
								?>
							</td>
							<?php
								endif;
							?>
						</tr>
						<?php
							endforeach;
						?>
					</tbody>
				</table>
			</div>
            <?php
				if(@$myAccess[0]['dashboard_new_admisssion_entries_box']==1 || @$this->session->userdata('role')=='Admin'):
			?>
            	<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat purple-plum">
						<div class="visual">
							<i class="fa fa-users"></i>
						</div>
						<div class="details">
							<div class="number">
								<?php echo count($new_student_entries);?>
							</div>
							<div class="desc">
								New Admissions Entries
							</div>
						</div>
						<a class="more" href="<?php echo site_url();?>/dashboard/new_admission_entries">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
            <?php
            	endif;
			?>
            
            <!--------->
            <?php
				if(@$myAccess[0]['dashboard_new_expense_entries_box']==1 || @$this->session->userdata('role')=='Admin'):
			?>
            	<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat purple-plum">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								<?php echo count($new_expense_entries);?>
							</div>
							<div class="desc">
								New Expense Entries
							</div>
						</div>
						<a class="more" href="<?php echo site_url();?>/dashboard/new_expense_entries">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
            <?php
            	endif;
			?>
            <!---------->
            
            <?php
				if(@$myAccess[0]['dashboard_fee_status']==1 || $this->session->userdata('role')=='Admin'):
			?>
            	<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat purple-plum">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								<?php echo count($college_fee)+count($contractor_fees);?>
							</div>
							<div class="desc">
								Fee Status
							</div>
						</div>
						<a class="more" href="<?php echo site_url();?>/dashboard/fee_status">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
            <?php
            	endif;
			?>
            <?php
				if(@$myAccess[0]['dashboard_update_payment_box']==1 || $this->session->userdata('role')=='Admin'):
			?>
            	<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat purple-plum">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								<?php echo count($fee_requests)+count($fee_requests_contractors);?>
							</div>
							<div class="desc">
								Update Fee Requests
							</div>
						</div>
						<a class="more" href="<?php echo site_url();?>/dashboard/update_fee_status">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
            <?php
            	endif;
			?>
			
			
			<?php
            if(@$myAccess[0]['dashboard_update_discount_box']==1 || $this->session->userdata('role')=='Admin'):
                ?>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <div class="dashboard-stat purple-plum">
                        <div class="visual">
                            <i class="fa fa-money"></i>
                        </div>
                        <div class="details">
                            <div class="number">
                                <?php echo count($discount_requests);?>
                            </div>
                            <div class="desc">
                                Fee Discount Requests
                            </div>
                        </div>
                        <a class="more" href="<?php echo site_url();?>/dashboard/discount_fee_status">
                            View more <i class="m-icon-swapright m-icon-white"></i>
                        </a>
                    </div>
                </div>
            <?php
            endif;
            ?>

			
			
			
			
			
			
			
			
			
			
            <?php
				if(@$myAccess[0]['dashboard_update_student_box']==1 || $this->session->userdata('role')=='Admin'):
			?>
            	<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat purple-plum">
						<div class="visual">
							<i class="fa fa-users"></i>
						</div>
						<div class="details">
							<div class="number">
								<?php echo count($students_edit_requests);?>
							</div>
							<div class="desc">
								Update Student Requests
							</div>
						</div>
						<a class="more" href="<?php echo site_url();?>/dashboard/students_edit_requests">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
            <?php
            	endif;
			?>
			
            <!---END CLASSES STATUS--->
            <?php
				//if(@$myAccess[0]['dashboard_students_due_fees']==1 || $this->session->userdata('role')=='Admin'):
			?>
            	<!--<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat purple-plum">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								<?php //echo $fee_dues_students_count+$fee_dues_contractors_count.'/'.(count($fee_dues_comments)+count($contracts_fee_dues_comments));?>
							</div>
							<div class="desc">
								Students / Contractor Due Fees
							</div>
						</div>
						<a class="more" href="<?php echo site_url();?>/dashboard/fee_dues_comments">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>-->
            <?php
            	//endif;
			?>
            <?php
				//if(@$myAccess[0]['dashboard_students_due_fees_status']==1 || $this->session->userdata('role')=='Admin'):
			?>
            	<!--<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat purple-plum">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								<?php //echo count($fee_dues_clear_comments)+count($contractors_fee_dues_clear_comments);?>
							</div>
							<div class="desc">
								Students Due Fees Status Clear
							</div>
						</div>
						<a class="more" href="<?php //echo site_url();?>/dashboard/fee_dues_clear_comments">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>-->
            <?php
            	//endif;
			?>
			<?php
				if(@$myAccess[0]['dashboard_reminders_status']==1 || $this->session->userdata('role')=='Admin'):
			?>
            	<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat purple-plum">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								<?php echo count($pending_reminders);?> / <?php echo count($under_review_reminders);?>
							</div>
							<div class="desc">
								Reminders Pending / Under Review
							</div>
						</div>
						<a class="more" href="<?php echo site_url();?>/reminders/all_pending_reminder">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
            <?php
            	endif;
			?>
			<?php
				//if($this->session->userdata('role')=='Admin'):
			?>
            	<!--<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat purple-plum">
						<div class="visual">
							<i class="fa fa-file"></i>
						</div>
						<div class="details">
							<div class="number">
								<?php //echo count($unclear_products);?>
							</div>
							<div class="desc">
								Unclear Products
							</div>
						</div>
						<a class="more" href="<?php //echo site_url();?>/dashboard/unclear_products">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
				
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat purple-plum">
						<div class="visual">
							<i class="fa fa-file"></i>
						</div>
						<div class="details">
							<div class="number">
								<?php //echo count($updated_products);?>
							</div>
							<div class="desc">
								Updated Products
							</div>
						</div>
						<a class="more" href="<?php //echo site_url();?>/dashboard/updated_products">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
				
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat purple-plum">
						<div class="visual">
							<i class="fa fa-file"></i>
						</div>
						<div class="details">
							<div class="number">
								<?php //echo count($unclear_documents);?>
							</div>
							<div class="desc">
								Unclear Documents
							</div>
						</div>
						<a class="more" href="<?php //echo site_url();?>/dashboard/unclear_documents">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
				
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat purple-plum">
						<div class="visual">
							<i class="fa fa-file"></i>
						</div>
						<div class="details">
							<div class="number">
								<?php //echo 0;?>
							</div>
							<div class="desc">
								Update Documents
							</div>
						</div>
						<a class="more" href="<?php //echo site_url();?>/dashboard/updated_documents">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>-->
			<?php
            	//endif;
			?>
			<?php
				if(@$myAccess[0]['online_application_new_admissions']==1 || @$this->session->userdata('role')=='Admin'):
			?>
			<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
				<div class="dashboard-stat purple-plum">
					<div class="visual">
						<i class="fa fa-users"></i>
					</div>
					<div class="details">
						<div class="number">
							<?php echo newApplicationsCount();?>
						</div>
						<div class="desc">
							New Applications
						</div>
					</div>
					<a class="more" href="<?php echo site_url();?>/online_application/new_applications">
					View more <i class="m-icon-swapright m-icon-white"></i>
					</a>
				</div>
			</div>
			
			<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
				<div class="dashboard-stat purple-plum">
					<div class="visual">
						<i class="fa fa-users"></i>
					</div>
					<div class="details">
						<div class="number">
							<?php echo pendingApplicationsCount();?>
						</div>
						<div class="desc">
							Pending Applications
						</div>
					</div>
					<a class="more" href="<?php echo site_url();?>/online_application/pending_applications">
					View more <i class="m-icon-swapright m-icon-white"></i>
					</a>
				</div>
			</div>
			<?php
				endif;
			?>
			<?php
				if(@$myAccess[0]['dashboard_students_fees_reversal']==1 || @$this->session->userdata('role')=='Admin'):
			?>
			<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
				<div class="dashboard-stat purple-plum">
					<div class="visual">
						<i class="fa fa-money"></i>
					</div>
					<div class="details">
						<div class="number">
							<?php echo newFeeReversalCount();?>
						</div>
						<div class="desc">
							Fee Reversal Requests
						</div>
					</div>
					<a class="more" href="<?php echo site_url();?>/dashboard/fee_reversal_requests">
					View more <i class="m-icon-swapright m-icon-white"></i>
					</a>
				</div>
			</div>
			<?php
				endif;
			?>
			<?php
				if(@$myAccess[0]['expense_second_approval']==1 || @$this->session->userdata('role')=='Admin'):
			?>
			<div class="row">
                <div class="col-md-12">
                    <div class="portlet box grey-cascade">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-list"></i> All Expense Reversals
                            </div>
                        </div>
                        <div class="portlet-body expense_body">
                            
                        </div>
                        <div class="portlet-body processing" style="display:none;">
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <i class="fa fa-spinner fa-spin fa-4x" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
			<?php endif; ?>
			<div class="clearfix"></div>
			<hr />
			<h3 class="page-title">
			Pending Tasks
			</h3>

			<div class="row">
                <?php
                if(@$myAccess[0]['dashboard_test_engine_questions']==1 || @$this->session->userdata('role')=='Admin'):
                ?>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat purple-plum">
						<div class="visual">
							<i class="fa fa-question"></i>
						</div>
						<div class="details">
							<div class="number">
								<?php echo count($pending_questions);?>
							</div>
							<div class="desc">
								Test Engine Questions
							</div>
						</div>
						<a class="more" href="<?php echo site_url();?>/dashboard/pending_questions">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
                <?php
                endif;
                ?>

                <?php
                if(@$myAccess[0]['dashboard_uncheck_assignment']==1 || @$this->session->userdata('role')=='Admin'):
                ?>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <div class="dashboard-stat purple-plum">
                        <div class="visual">
                            <i class="fa fa-file"></i>
                        </div>
                        <div class="details">
                            <div class="number">
                                <?php echo count($uncheck_assignments);?>
                            </div>
                            <div class="desc">
                                Uncheck Assignments
                            </div>
                        </div>
                        <a class="more" href="<?php echo site_url();?>/dashboard/uncheck_assignments">
                            View more <i class="m-icon-swapright m-icon-white"></i>
                        </a>
                    </div>
                </div>
                <?php
                endif;
                ?>
				
				
				<?php
                if(@$myAccess[0]['student_struck_off_list']==1 || @$this->session->userdata('role')=='Admin'):
                    ?>
                    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                        <div class="dashboard-stat purple-plum">
                            <div class="visual">
                                <i class="fa fa-file"></i>
                            </div>
                            <div class="details">

                                <div class="desc" style="padding: 3px; font-size: small ">
                                    Strucked of Students  <strong> <?php echo count($struckedofcount);?> </strong>
                                </div>
                                <div class="desc" style="padding: 3px; font-size: small">
                                    Final Struck of Students  <strong> <?php echo count($struckofcountp);?> </strong>
                                </div>
                                <div class="desc">
                                    Struck of Students in Inquiry  <span style="font-size: xx-large"> <?php echo count($struckofcount);?> </span>
                                </div>
                            </div>
                            <a class="more" href="<?php echo site_url();?>/students/all_struckofstudent/0">
                                View more <i class="m-icon-swapright m-icon-white"></i>
                            </a>
                        </div>
                    </div>
                <?php
                endif;
                ?>


                <?php
                if(@$myAccess[0]['student_delete']==1 || @$this->session->userdata('role')=='Admin'):
                    ?>
                    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                        <div class="dashboard-stat purple-plum">
                            <div class="visual">
                                <i class="fa fa-file"></i>
                            </div>
                            <div class="details">
                                <div class="number">
                                    <?php echo count($struckofcountp);?>
                                </div>
                                <div class="desc">
                                    Final Struck of Students
                                </div>
                            </div>
                            <a class="more" href="<?php echo site_url();?>/students/all_struckofstudent/1">
                                View more <i class="m-icon-swapright m-icon-white"></i>
                            </a>
                        </div>
                    </div>
                <?php
                endif;
                ?>

				 <?php
                if(@$myAccess[0]['expense_approval']==1 || @$this->session->userdata('role')=='Admin'):
                    ?>
                    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                        <div class="dashboard-stat red-intense">
                            <div class="visual">
                                <i class="fa fa-file"></i>
                            </div>
                            <div class="details">
                                <div class="number">
                                    <?php echo $expenseapprovalcount;?>
                                </div>
                                <div class="desc">
                                    Pending Expense Approvals
                                </div>
                            </div>
                            <a class="more" href="<?php echo site_url();?>/expenses/all_expenses/1">
                                View more <i class="m-icon-swapright m-icon-white"></i>
                            </a>
                        </div>
                    </div>
                <?php
                endif;
                ?>
				
				
			</div>

			
            <div class="clearfix"></div>
			<hr />
			<h3 class="page-title">
			Dashboard <small>reports & statistics</small>
			</h3>
			<div class="row">
				<?php
					if(@$myAccess[0]['dashboard_total_student_box']==1 || $this->session->userdata('role')=='Admin'):
				?>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat blue-madison">
						<div class="visual">
							<i class="fa fa-graduation-cap"></i>
						</div>
						<div class="details">
							<div class="number">
								<?php echo $total_students;?>
							</div>
							<div class="desc">
								 Total Students
							</div>
						</div>
						<a class="more" href="<?php echo site_url();?>/students/all_students">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
                <?php
                	endif;
				?>
                <?php
					if(@$myAccess[0]['dashboard_total_teacher_box']==1 || $this->session->userdata('role')=='Admin'):
				?>
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat green-haze">
						<div class="visual">
							<i class="icon-users"></i>
						</div>
						<div class="details">
							<div class="number">
								<?php echo $total_teachers;?>
							</div>
							<div class="desc">
								 Total Staff
							</div>
						</div>
						<a class="more" href="<?php echo site_url();?>/teachers/all_teachers">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
                <?php
                	endif;
				?>
                <?php
					if(@$myAccess[0]['dashboard_new_admission']==1 || $this->session->userdata('role')=='Admin'):
				?>
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat purple-plum">
						<div class="visual">
							<i class="fa fa-graduation-cap"></i>
						</div>
						<div class="details">
							<div class="number">
								<?php echo $new_students_this_month;?>
							</div>
							<div class="desc">
								New Admissions this month
							</div>
						</div>
						<a class="more" href="<?php echo site_url();?>/dashboard/new_students">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
                <?php
                	endif;
				?>
                <?php
					if(@$myAccess[0]['dashboard_month_earning']==1 || $this->session->userdata('role')=='Admin'):
				?>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat grey-gallery">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								Rs <?php print_r($this_month_earning[0]['total_submitted_fee']);?>
							</div>
							<div class="desc">
								This Month Earning
							</div>
						</div>
						<a class="more" href="<?php echo site_url();?>/dashboard/new_submit_fees">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
                <?php
                	endif;
				?>
                <?php
					if(@$myAccess[0]['dashboard_month_expense']==1 || $this->session->userdata('role')=='Admin'):
				?>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat red-intense">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								Rs <?php print_r($this_month_expense[0]['this_month_expense']);?>
							</div>
							<div class="desc">
								This Month Expense
							</div>
						</div>
						<a class="more" href="<?php echo site_url();?>/dashboard/new_expenses">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
                <?php
                	endif;
				?>
                <?php
					if(@$myAccess[0]['dashboard_month_profit']==1 || $this->session->userdata('role')=='Admin'):
				?>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat yellow-lemon">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								Rs <?php print_r($this_month_earning[0]['total_submitted_fee']-$this_month_expense[0]['this_month_expense']);?>
							</div>
							<div class="desc">
								This Month Profit
							</div>
						</div>
						<a class="more" href="<?php echo site_url();?>/dashboard/profit">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
                <?php
                	endif;
				?>
				<?php
					if(@$myAccess[0]['dashboard_classes_status']==1 || $this->session->userdata('role')=='Admin'):
				?>
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat purple-plum">
						<div class="visual">
							<i class="fa fa-file"></i>
						</div>
						<div class="details">
							<div class="number">
								<?php echo count($classes_status);?>
							</div>
							<div class="desc">
								All Classes Status
							</div>
						</div>
						<a class="more" href="<?php echo site_url();?>/dashboard/classes_status">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
				<?php
                	endif;
				?>
			</div>
			
			
		</div>
	</div>
	<!-- END CONTENT -->
</div>
<!-- END CONTAINER -->


<div class="modal fade" id="basic" tabindex="-1" role="basic" aria-hidden="true">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
											<h4 class="modal-title">Do you want to delete this student?</h4>
										</div>
										<div class="modal-body">
											 <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/students/delete">
                                                <div class="form-body">
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">Delete Type</label>
                                                        <div class="col-md-8 radio-list">
                                                            <label class="radio-inline">
                                                            <input type="radio" class="delete_type" name="delete_type" id="optionsRadios1" value="Delete" checked> Delete </label>
                                                            
                                                            <label class="radio-inline">
                                                            <input type="radio" class="delete_type" name="delete_type" id="optionsRadios2" value="Freeze"> Freeze </label>
                                                        </div>
                                                    </div>
													<div class="form-group">
                                                        <label class="col-md-4 control-label">Reason</label>
                                                        <div class="col-md-8 radio-list">
                                                            <label class="radio-inline">
                                                            <input type="radio" class="reason" name="reason" id="optionsRadios1" value="fee" checked> fee </label>
                                                            
                                                            <label class="radio-inline">
                                                            <input type="radio" class="reason" name="reason" id="optionsRadios2" value="discipline"> discipline </label>
                                                            
                                                            <label class="radio-inline">
                                                            <input type="radio" class="reason" name="reason" id="optionsRadios3" value="other"> other </label>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">Reason in Details</label>
                                                        <div class="col-md-8">
                                                                <textarea class="form-control remarks" rows="3" name="reason_detail"></textarea>
                                                        </div>
                                                    </div>
                                                    
													<?php
														if(@$myAccess[0]['student_issue_refund']==1 || $this->session->userdata('role')=='Admin'):
													?>
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">Refund Amount</label>
                                                        <div class="col-md-8">
                                                                <input type="number" max="0" name="refund_amount" class="form-control refund_amount" value="" />
                                                        </div>
                                                    </div>
													<?php
														endif;
													?>
                                                    
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">Image</label>
                                                        <div class="col-md-8">
                                                                <input type="file" name="image" class="form-control" />
                                                        </div>
                                                    </div>
                                                    
                                                </div>
                                                
                                                <div class="form-actions">
                                                    <div class="row">
                                                        <div class="col-md-offset-3 col-md-9">
                                                            <input type="hidden" name="add_by" value="<?php echo $this->session->userdata('name');?>" />
                                                            <input type="hidden" name="student_id" class="student_id" value="" />
                                                            <button type="submit" class="btn red">Delete Student</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn default close_button" data-dismiss="modal">Close</button>
										</div>
									</div>
									<!-- /.modal-content -->
								</div>
								<!-- /.modal-dialog -->
							</div>
							
<div class="modal fade" id="accountsModal" tabindex="-1" role="dialog" aria-labelledby="accountsModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="accountsModalLabel">Accounts</h4>
            </div>

            <div class="modal-body" id="accountsModalBody">
                Loading...
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="expensereversalapproval" tabindex="-1"   data-width="1200" >
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Expense Reversal</h4>
    </div>
    <div class="modal-body">
        <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/expenses/request_reverse_approve">
            <div class="form-body">
                <div class="form-group">
                    <div class="col-md-12">
                        <label class="form-control" style="text-align: center" >Do you Want Reversal of this Expense?</label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">AMOUNT</label>
                    <div class="col-md-9">
                        <input type="number"  name="ap_amount" id="ap_amount" class="form-control mobile" readonly/>
                    </div>
                </div>
                <div class="form-group" id="bank_expense">
                    <label class="col-md-3 control-label">Reversal Reason</label>
                    <div class="col-md-9 radio-list" name="radiolist" id="radiolist">
                        <label class="radio-inline">
                            <input type="radio" class="status" name="rev_reason"  value="1" >Wrong Entry</label>
                        <label class="radio-inline">
                            <input type="radio" class="status" name="rev_reason"  value="2">Reverse through Cash in Hand</label>
                    </div>
                    <p class="col-md-3"></p>
                    <p class="col-md-9">1) Wrong Entry will Untag the Bank Entry and Delete the Expense</p>
                    <p class="col-md-3"></p>
                    <p class="col-md-9">2) Reverse through Cash in Hand will Reverse the Expense Amount to Your PettyCash </p>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">REASON <span class="required">*</span></label>
                    <div class="col-md-9">
                        <textarea class="form-control remarks" rows="3" id="ap_reason" name="reason" readonly></textarea>
                    </div>
                </div>
            </div>
            <?php if ($this->session->userdata('role')=='Admin' || $myAccess[0]['expense_second_approval'] === '1'): ?>
                <div class="form-group" >
                    <label class="col-md-6 control-label">Accept or Reject this Expense Reversal</label>
                    <div class="col-md-6 radio-list" name="radiolist" id="radiolist">
                        <label class="radio-inline">
                            <input type="radio" class="status" name="status"  value="1" >Accept</label>
                        <label class="radio-inline">
                            <input type="radio" class="status" name="status"  value="2">Reject</label>
                    </div>
                </div>
                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-12" style="text-align: center">
                            <input type="hidden" id="ap_expense_rev_id" name="expense_id" value="" />
                            <input type="hidden" id="ap_expense_pay_through" name="pay_through" value="" />
                            <input type="hidden" name="last_edit" value="<?php echo $this->session->userdata('name');?>" />
                            <button type="submit" class="btn red">Submit</button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>
window.addEventListener('DOMContentLoaded',function () {
	$('.mytopic').click(function(e){
		e.preventDefault();
		if (confirm('Are you sure?')) 
		{
			var topic_id = $(this).data('topic-id');
			var session_syllabus_id = $(this).data('session-syllabus-id');
			var is_quiz = $(this).data('is-quiz');

			if($(this).prop('checked') == true)
			{
				jQuery.ajax({
					type: "post",
					async: false,
					url: '<?php echo site_url()?>/dashboard/insertTopicStudied',
					data: {
						topic_id : topic_id,
						session_syllabus_id : session_syllabus_id,
						is_quiz : is_quiz
					},
					success: function(data) {
						
					}
				});
			}
			else
			{
				jQuery.ajax({
					type: "post",
					async: false,
					url: '<?php echo site_url()?>/dashboard/deleteTopicStudied',
					data: {
						topic_id : topic_id,
						session_syllabus_id : session_syllabus_id,
						is_quiz : is_quiz
					},
					success: function(data) {
						
					}
				});
			}
		}
		else
		{
			//$(this).attr('checked', false);
			if($(this).parent('span').hasClass('checked'))
			{
				$(this).parent('span').removeClass('checked');
			}
			else
			{
				$(this).parent('span').addClass('checked');
			}
		}
	});

	$('.mypractical').click(function(){
		if (confirm('Are you sure?')) 
		{
			var practical_id = $(this).data('practical-id');
			var session_syllabus_id = $(this).data('session-syllabus-id');
			var is_quiz = $(this).data('is-quiz');

			if($(this).prop('checked') == true)
			{
				jQuery.ajax({
					type: "post",
					async: false,
					url: '<?php echo site_url()?>/dashboard/insertPracticalStudied',
					data: {
						practical_id : practical_id,
						session_syllabus_id : session_syllabus_id,
						is_quiz : is_quiz
					},
					success: function(data) {
						
					}
				});
			}
			else
			{
				jQuery.ajax({
					type: "post",
					async: false,
					url: '<?php echo site_url()?>/dashboard/deletePracticalStudied',
					data: {
						practical_id : practical_id,
						session_syllabus_id : session_syllabus_id,
						is_quiz : is_quiz
					},
					success: function(data) {
						
					}
				});
			}
		}
		else
		{
			if($(this).parent('span').hasClass('checked'))
			{
				$(this).parent('span').removeClass('checked');
			}
			else
			{
				$(this).parent('span').addClass('checked');
			}
		}
	});

	$('.all_open').click(function(){
		if($(this).prop('checked') == true)
		{
			$('.study_by').show();
		}
		else
		{
			$('.study_by').hide();
		}
	});
	
	$(document).ready(function () {
        
        loadReport('Active');
        <?php
				if(@$myAccess[0]['expense_second_approval']==1 || @$this->session->userdata('role')=='Admin'):
			?>
        jQuery.ajax({
                    type: "post",
                    async: true,
                    url: '<?php echo site_url()?>/expenses/getAllReversalExpenses',
                    data: {
                        
                    },
                    beforeSend: function(){
                        jQuery('.expense_body').empty();
                        jQuery('.processing').show();
                    },
                    complete: function(){
                        jQuery('.processing').hide();
                    },
                    success: function(response) {
                        jQuery('.expense_body').html(response);
                        
                    }
                });
                <?php endif; ?>
    
        $(document).on('click', '.view-accounts-btn', function () {
            var title = $(this).attr('data-title');
            var accounts = $(this).attr('data-accounts');
    
            try {
                accounts = JSON.parse(accounts);
            } catch (e) {
                accounts = [];
            }
    
            $('#accountsModalLabel').text(title);
    
            var totalFee = 0;
            var totalPaid = 0;
            var totalLiability = 0;
            var totalProfit = 0;
    
            var html = `
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Fee Received</th>
                                <th>Paid</th>
                                <th>Liability</th>
                                <th>Profit</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
    
            if (accounts.length > 0) {
                accounts.forEach(function(row) {
                    var fee = parseFloat(row.fee_amount || 0);
                    var paid = parseFloat(row.expense_amount || 0);
                    var liability = parseFloat(row.liability || 0);
                    var profit = parseFloat(row.profit_amount || 0);
    
                    totalFee += fee;
                    totalPaid += paid;
                    totalLiability += liability;
                    totalProfit += profit;
    
                    html += `
                        <tr>
                            <td>${row.task_name} - ${row.type_name}</td>
                            <td>${fee.toFixed(2)}</td>
                            <td>${paid.toFixed(2)}</td>
                            <td>${liability.toFixed(2)}</td>
                            <td>${profit.toFixed(2)}</td>
                        </tr>
                    `;
                });
            } else {
                html += `
                    <tr>
                        <td colspan="5" class="text-center">No data found</td>
                    </tr>
                `;
            }
    
            html += `
                        </tbody>
                        <tfoot>
                            <tr style="font-weight:bold; background:#f5f5f5;">
                                <td>Total</td>
                                <td>${totalFee.toFixed(2)}</td>
                                <td>${totalPaid.toFixed(2)}</td>
                                <td>${totalLiability.toFixed(2)}</td>
                                <td>${totalProfit.toFixed(2)}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            `;
    
            $('#accountsModalBody').html(html);
        });
    });
});
function loadReport(status,tab) {
        
        if(status == 'Active'){
            $('#active_tab').addClass('active');
            $('#inactive_tab').removeClass('active');
            $('#total_liablity').show();
        }else{
            $('#active_tab').removeClass('active');
            $('#inactive_tab').addClass('active');
            $('#total_liablity').hide();
        }
        $('#reportLoader').show();
        $('#reportResult').html('');

        $.ajax({
            url: "<?php echo site_url('councils/report_ajax'); ?>"+"/"+status,
            type: "GET",
            success: function(response) {
                $('#reportLoader').hide();
                $('#reportResult').html(response);

                if ($.fn.DataTable.isDataTable('#sample_2')) {
                    $('#sample_2').DataTable().destroy();
                }

                $('#sample_2').DataTable({
                    pageLength: -1
                });
            },
            error: function() {
                $('#reportLoader').hide();
                $('#reportResult').html('<div class="alert alert-danger">Report load nahi ho saki.</div>');
            }
        });
    }
</script>
