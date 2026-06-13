	
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
								<i class="fa fa-money"></i> Update Student Requests
							</div>
						</div>
						<div class="portlet-body">
							<table  class="table table-striped table-bordered table-hover" id="sample_16">
							<thead>
							<tr>
                                <th class="hidden">
                                	Hidden
                                </th>
                                <th>
                                	Request Date
                                </th>
                                <th>
                                	Name
                                </th>
                                <th>
                                	Father Name
                                </th>
                                <th>
                                	Roll No
                                </th>
                                <th>
                                	Password
                                </th>
                                <th>
                                	Gender
                                </th>
								<th>
									Qualification
								</th>
								<th>
									Caste
								</th>
								<th>
									Religion
								</th>
                                <th>
                                	Email
                                </th>
                                <th>
                                	CNIC
                                </th>
                                <th>
                                	Blood Group
                                </th>
                                <th>
                                	Date of birth
                                </th>
                                <th>
                                	Registration Date
                                </th>
                                <th>
                                	Total Fee
                                </th>
                                <th>
                                	City
                                </th>
                                <th>
                                	Address
                                </th>
                                <th>
                                	Mobile
                                </th>
                                <th>
                                	Emergency No
                                </th>
                                <th>
                                	Class
                                </th>
                                <th>
                                	Contractor
                                </th>
                                <th>
                                	1st Year Books
                                </th>
                                <th>
                                	2nd Year Books
                                </th>
                                <th>
                                	Student Card
                                </th>
                                <th>
                                	Board
                                </th>
                                <th>
                                	Shift
                                </th>
                                <th>
                                	Student Type
                                </th>
                                <th>
                                	Section
                                </th>
                                <th>
                                	Status
                                </th>
								<th>
                                	Student Delete Request
                                </th>
                                <th>
                                	Add By
                                </th>
                                <th>
                                	Last Edit
                                </th>
                                <th>
                                	Action
                                </th>
							</tr>
							</thead>
							<tbody>
                            	<?php
                                	$i=1;
									foreach($students_edit_requests as $students_edit_request):
									$studentDetail = getStudentDetails($students_edit_request['student_id']);
								?>
                                <tr>
                                	<td class="hidden">
                                		<?php echo $i;?>
                                    </td>
                                    <td>
                                        <?php echo $students_edit_request['update_date'];?>
                                    </td>
                                    <td>
                                        <?php echo $students_edit_request['first_name'].' '.$students_edit_request['last_name'];?>
                                        <hr />
                                        <span class="<?php if($students_edit_request['first_name']==$studentDetail[0]['first_name']){echo '';}else{echo 'alert-danger';}?>"><?php echo $studentDetail[0]['first_name'];?></span> <span class="<?php if($students_edit_request['last_name']==$studentDetail[0]['last_name']){echo '';}else{echo 'alert-danger';}?>"><?php echo $studentDetail[0]['last_name'];?></span>
                                    </td>
                                    <td>
                                        <?php echo $students_edit_request['father_name'];?>
                                        <hr />
                                        <span class="<?php if($students_edit_request['father_name']==$studentDetail[0]['father_name']){echo '';}else{echo 'alert-danger';}?>"><?php echo $studentDetail[0]['father_name'];?></span>
                                    </td>
                                    <td>
                                        <?php echo $students_edit_request['roll_no'];?>
                                        <hr />
                                        <span class="<?php if($students_edit_request['roll_no']==$studentDetail[0]['roll_no']){echo '';}else{echo 'alert-danger';}?>"><?php echo $studentDetail[0]['roll_no'];?></span>
                                    </td>
                                    <td>
                                        <?php echo $students_edit_request['password'];?>
                                        <hr />
                                        <span class="<?php if($students_edit_request['password']==$studentDetail[0]['password']){echo '';}else{echo 'alert-danger';}?>"><?php echo $studentDetail[0]['password'];?></span>
                                    </td>
                                    <td>
                                        <?php echo $students_edit_request['gender'];?>
                                        <hr />
                                        <span class="<?php if($students_edit_request['gender']==$studentDetail[0]['gender']){echo '';}else{echo 'alert-danger';}?>"><?php echo $studentDetail[0]['gender'];?></span>
                                    </td>
									<td>
                                        <?php echo $students_edit_request['qualification'];?>
                                        <hr />
                                        <span class="<?php if($students_edit_request['qualification']==$studentDetail[0]['qualification']){echo '';}else{echo 'alert-danger';}?>"><?php echo $studentDetail[0]['qualification'];?></span>
                                    </td>
									<td>
                                        <?php echo $students_edit_request['caste'];?>
                                        <hr />
                                        <span class="<?php if($students_edit_request['caste']==$studentDetail[0]['caste']){echo '';}else{echo 'alert-danger';}?>"><?php echo $studentDetail[0]['caste'];?></span>
                                    </td>
									<td>
                                        <?php echo $students_edit_request['religion'];?>
                                        <hr />
                                        <span class="<?php if($students_edit_request['religion']==$studentDetail[0]['religion']){echo '';}else{echo 'alert-danger';}?>"><?php echo $studentDetail[0]['religion'];?></span>
                                    </td>
                                    <td>
                                        <?php echo $students_edit_request['email'];?>
                                        <hr />
                                        <span class="<?php if($students_edit_request['email']==$studentDetail[0]['email']){echo '';}else{echo 'alert-danger';}?>"><?php echo $studentDetail[0]['email'];?></span>
                                    </td>
                                    <td>
                                        <?php echo $students_edit_request['cnic'];?>
                                        <hr />
                                        <span class="<?php if($students_edit_request['cnic']==$studentDetail[0]['cnic']){echo '';}else{echo 'alert-danger';}?>"><?php echo $studentDetail[0]['cnic'];?></span>
                                    </td>
                                    <td>
                                        <?php echo $students_edit_request['blood_group'];?>
                                        <hr />
                                        <span class="<?php if($students_edit_request['blood_group']==$studentDetail[0]['blood_group']){echo '';}else{echo 'alert-danger';}?>"><?php echo $studentDetail[0]['blood_group'];?></span>
                                    </td>
                                    <td>
                                        <?php echo $students_edit_request['date_of_birth'];?>
                                        <hr />
                                        <span class="<?php if($students_edit_request['date_of_birth']==$studentDetail[0]['date_of_birth']){echo '';}else{echo 'alert-danger';}?>"><?php echo $studentDetail[0]['date_of_birth'];?></span>
                                    </td>
                                    <td>
                                        <?php echo $students_edit_request['registration_date'];?>
                                        <hr />
                                        <span class="<?php if($students_edit_request['registration_date']==$studentDetail[0]['registration_date']){echo '';}else{echo 'alert-danger';}?>"><?php echo $studentDetail[0]['registration_date'];?></span>
                                    </td>
                                    <td>
                                        <?php echo $students_edit_request['total_fee'];?>
                                        <hr />
                                        <span class="<?php if($students_edit_request['total_fee']==$studentDetail[0]['total_fee']){echo '';}else{echo 'alert-danger';}?>"><?php echo $studentDetail[0]['total_fee'];?></span>
                                    </td>
                                    <td>
                                        <?php echo $students_edit_request['city'];?>
                                        <hr />
                                        <span class="<?php if($students_edit_request['city']==$studentDetail[0]['city']){echo '';}else{echo 'alert-danger';}?>"><?php echo $studentDetail[0]['city'];?></span>
                                    </td>
                                    <td>
                                        <?php echo $students_edit_request['address'];?>
                                        <hr />
                                        <span class="<?php if($students_edit_request['address']==$studentDetail[0]['address']){echo '';}else{echo 'alert-danger';}?>"><?php echo $studentDetail[0]['address'];?></span>
                                    </td>
                                    <td>
                                        <?php echo $students_edit_request['mobile'];?>
                                        <hr />
                                        <span class="<?php if($students_edit_request['mobile']==$studentDetail[0]['mobile']){echo '';}else{echo 'alert-danger';}?>"><?php echo $studentDetail[0]['mobile'];?></span>
                                    </td>
                                    <td>
                                        <?php echo $students_edit_request['emergency_no'];?>
                                        <hr />
                                        <span class="<?php if($students_edit_request['emergency_no']==$studentDetail[0]['emergency_no']){echo '';}else{echo 'alert-danger';}?>"><?php echo $studentDetail[0]['emergency_no'];?></span>
                                    </td>
                                    <td>
                                        <?php echo $students_edit_request['class_name'];?>
                                        <hr />
                                        <span class="<?php if($students_edit_request['class_name']==$studentDetail[0]['class_name']){echo '';}else{echo 'alert-danger';}?>"><?php echo $studentDetail[0]['class_name'];?></span>
                                    </td>
                                    <td>
                                        <?php echo @$this->db->get_where('contractors',array('contractor_id'=>$students_edit_request['contractor_id']))->row()->name;?>
                                        <hr />
                                        <span class="<?php if($students_edit_request['contractor_id']==$studentDetail[0]['contractor_id']){echo '';}else{echo 'alert-danger';}?>"><?php echo @$this->db->get_where('contractors',array('contractor_id'=>$studentDetail[0]['contractor_id']))->row()->name;?></span>
                                        <hr />
                                        <?php echo @$this->db->get_where('contracts',array('contract_id'=>$students_edit_request['contract_id']))->row()->contract_name;?>
                                        <hr />
                                        <span class="<?php if($students_edit_request['contract_id']==$studentDetail[0]['contract_id']){echo '';}else{echo 'alert-danger';}?>"><?php echo @$this->db->get_where('contracts',array('contract_id'=>$studentDetail[0]['contract_id']))->row()->contract_name;?></span>
                                    </td>
                                    <td>
                                        <?php 
											if($students_edit_request['books_1']==0)
											{
												echo 'No';
											}
											else
											{
												echo 'Yes';
											}
										?>
                                        <hr />
                                        <?php 
											if($studentDetail[0]['books_1']==0)
											{
												if($students_edit_request['books_1']==$studentDetail[0]['books_1'])
												{
													echo '<span>No</span>';
												}
												else
												{
													echo '<span class="alert-danger">No</span>';
												}
											}
											else
											{
												if($students_edit_request['books_1']==$studentDetail[0]['books_1'])
												{
													echo '<span>Yes</span>';
												}
												else
												{
													echo '<span class="alert-danger">Yes</span>';
												}
											}
										?>
                                    </td>
                                    <td>
                                        <?php 
											if($students_edit_request['books_2']==0)
											{
												echo 'No';
											}
											else
											{
												echo 'Yes';
											}
										?>
                                        <hr />
										<?php 
											if($studentDetail[0]['books_2']==0)
											{
												if($students_edit_request['books_2']==$studentDetail[0]['books_2'])
												{
													echo '<span>No</span>';
												}
												else
												{
													echo '<span class="alert-danger">No</span>';
												}
											}
											else
											{
												if($students_edit_request['books_2']==$studentDetail[0]['books_2'])
												{
													echo '<span>Yes</span>';
												}
												else
												{
													echo '<span class="alert-danger">Yes</span>';
												}
											}
										?>
                                    </td>
                                    <td>
                                        <?php 
											if($students_edit_request['student_card']==0)
											{
												echo 'No';
											}
											else
											{
												echo 'Yes';
											}
										?>
                                        <hr />
										<?php 
											if($studentDetail[0]['student_card']==0)
											{
												if($students_edit_request['student_card']==$studentDetail[0]['student_card'])
												{
													echo '<span>No</span>';
												}
												else
												{
													echo '<span class="alert-danger">No</span>';
												}
											}
											else
											{
												if($students_edit_request['student_card']==$studentDetail[0]['student_card'])
												{
													echo '<span>Yes</span>';
												}
												else
												{
													echo '<span class="alert-danger">Yes</span>';
												}
											}
										?>
                                    </td>
                                    <td>
                                        <?php echo $students_edit_request['board'];?>
                                        <hr />
                                        <span class="<?php if($students_edit_request['board']==$studentDetail[0]['board']){echo '';}else{echo 'alert-danger';}?>"><?php echo $studentDetail[0]['board'];?></span>
                                    </td>
                                    <td>
                                        <?php echo @$this->db->get_where('shifts',array('id'=>@$students_edit_request['shift']))->row()->name;?>
                                        <hr />
                                        <span class="<?php if($students_edit_request['shift']==$studentDetail[0]['shift']){echo '';}else{echo 'alert-danger';}?>"><?php echo $shift = @$this->db->get_where('shifts',array('id'=>$studentDetail[0]['shift']))->row()->name;?></span>
                                    </td>
                                    <td>
                                        <?php echo $this->db->get_where('study_type',array('id'=>@$students_edit_request['study_type']))->row()->name;?>
                                        <hr />
                                        <span class="<?php if($students_edit_request['study_type']==@$studentDetail[0]['study_type']){echo '';}else{echo 'alert-danger';}?>"><?php echo $study_type = @$this->db->get_where('study_type',array('id'=>@$studentDetail[0]['study_type']))->row()->name; if(@$study_type==''){echo 'N/A';}?></span>
                                    </td>
                                    <td>
                                        <?php echo $students_edit_request['section'];?>
                                        <hr />
                                        <span class="<?php if($students_edit_request['section']==$studentDetail[0]['section']){echo '';}else{echo 'alert-danger';}?>"><?php echo $studentDetail[0]['section'];?></span>
                                    </td>
                                    <td>
                                    	<?php if($students_edit_request['status']==1){echo 'active';}else{echo 'deactive';}?>
                                        <hr />
                                        <span class="<?php if($students_edit_request['status']==$studentDetail[0]['status']){echo '';}else{echo 'alert-danger';}?>"><?php if($studentDetail[0]['status']==1){echo 'active';}else{echo 'deactive';}?></span>
                                    </td>
									<td>
										<?php
											if($students_edit_request['status']==0):
											$delete_reasons = $this->db->get_where('deleted_students',array('student_id'=>$studentDetail[0]['student_id']))->result_array();
											
											foreach($delete_reasons as $delete_reason)
											{
												echo 'Delete Type : '.$delete_reason['delete_type'].'<br />';
												echo 'Delete Date : '.date('M d, Y',strtotime($delete_reason['date'])).'<br />';
												echo 'Delete By : '.$delete_reason['deleted_by'].'<br />';
												echo 'Reason : '.$delete_reason['reason'].'<br />';
												echo 'Reason Detail : '.$delete_reason['reason_detail'].'<br />';
												echo 'Refund Amount : Rs '.$delete_reason['refund_amount'].'<br />';
												if($delete_reason['image']!='')
												{
													echo '<a target="_blank" href="'.base_url().'uploads/'.$delete_reason['image'].'" class="btn green">Image</a>';
												}
											}
										
											endif;
										?>
									</td>
                                    <td>
                                        <?php echo $students_edit_request['add_by'];?>
                                    </td>
                                    <td>
                                        <?php echo $students_edit_request['last_edit'];?>
                                    </td>
                                    <td>
                                        <a class="btn green" href="<?php echo site_url();?>/dashboard/clear_student_update/<?php echo $students_edit_request['student_id'];?>" onclick="return confirm('Are you sure ?')">Clear</a>
										<br /><br />
										<a class="btn red" href="<?php echo site_url();?>/dashboard/reject_student_update/<?php echo $students_edit_request['student_id'];?>" onclick="return confirm('Are you sure ?')">Reject</a>
                                    </td>
                                </tr>
                                <?php
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