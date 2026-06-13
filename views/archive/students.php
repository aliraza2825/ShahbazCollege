	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			All Teachers <small>Here you can find all teachers</small>
			</h3>-->
            <!-- BEGIN DASHBOARD STATS -->
			<div class="row">
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat blue-madison">
						<div class="visual">
							<i class="fa fa-graduation-cap"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php echo @$count;?>
							</div>
							<div class="desc">
								 Students
							</div>
						</div>
						<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
			</div>
			<!-- END DASHBOARD STATS -->
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
								<i class="fa fa-list"></i>All Students
							</div>
						</div>
						<div class="portlet-body">
							<table class="table table-striped table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
                                <th class="hidden">
									Sr
								</th>
                                <th>
                                    View
                                </th>

                                <th>
									 Campus
								</th>
								<th>
									 Course
								</th>
								<th>
									 Roll #
								</th>
								<th>
									 Name
								</th>
                                <th>
									 CNIC
								</th>
                                <th>
									 Mobile
								</th>
                                <th>
									 Class
								</th>
								<th>
									Reason
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
								$i=1;
								foreach($students as $student):
							?>
                            <tr class="odd gradeX">
                                <td class="hidden">
									 <?php echo $i;?>
								</td>
                                <td>
                                    <a class="btn green" href="<?php echo site_url().'/Students/struckofstudentview/'.$student['student_id'];?>" >
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </td>
								<td>
									 <?php echo $student['campus_name'];?>
								</td>
								<td>
									 <?php echo $student['course_name'];?>
								</td>
								<td>
									 <?php echo $student['roll_no'];?>
								</td>
								<td>
									<?php echo $student['first_name'].' '.$student['last_name'];?>
								</td>
                                <td>
									<?php echo $student['cnic']?>
								</td>
                                <td>
									<?php echo $student['mobile']?>
								</td>
                                <td>
									<?php echo $student['class_name']?>
								</td>
								<td>
									<?php 
										$delete_student_data = $this->db->get_where('deleted_students', array('student_id'=>$student['student_id'],'status'=>1))->result_array();
										foreach($delete_student_data as $delete):
									?>
									Delete Type : <?php echo $delete['delete_type'];?>
									<br />
									Delete By : <?php echo $delete['deleted_by'];?>
									<br />
									Approve By : <?php echo $delete['approve_by'];?>
									<br />
									Delete Date : <?php echo $delete['date'];?>
									<br />
									Reason : <?php echo $delete['reason'];?>
									<br />
									Reason Detail : <?php echo $delete['reason_detail'];?>
									<br />
									<?php if($delete['refund_amount']>0):?>
									Refund Amount : <?php echo $delete['refund_amount'];?>
									<?php endif;?>
									<?php if($delete['image']):?>
									<a class="btn green" target="_blank" href="<?php echo base_url();?>uploads/<?php echo $delete['image'];?>"><i class="fa fa-image"></i> Image</a>
									<?php endif;?>
									<?php
										endforeach;
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
											echo '<br /><hr />';
										}
									?>
                                </td>
								<td>
									<a href="<?php echo site_url().'/students/edit_student/'.$student['student_id'];?>" class="btn blue"><i class="fa fa-edit"></i></a>
									
									<?php
                                    	if(@$student['contract_id']==0):
                                    	if(@$myAccess[0]['student_payments']==1 || $this->session->userdata('role')=='Admin'):
									?>
                                    <a title="Payments" href="<?php echo site_url().'/students/payments/'.$student['student_id'];?>" class="btn purple"><i class="fa fa-money"></i></a>
                                    <?php
                                    	endif;
										endif;
									?>
									
                                    <a href="<?php echo site_url().'/archive/restore_student/'.$student['student_id'];?>" class="btn green"><i class="fa fa-refresh"></i></a>
                                    
									<a onclick="return confirm('Are you sure you want to delete this Student permanently?')" href="<?php echo site_url().'/archive/delete_student/'.$student['student_id'];?>" class="btn red"><i class="fa fa-trash"></i></a>
								</td>
							</tr>
                            <?php
								//$i++;
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