<?php
	$myAccess = checkUserAccess();
?>		
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
								<i class="fa fa-money"></i> Students Fee Problem
							</div>
						</div>
						<div class="portlet-body">
							<table  class="table table-bordered table-hover" id="sample_3">
							<thead>
							<tr>
                                <th class="hidden">
                                	Hidden
                                </th>
								<th>
									 Course Name
								</th>
								<th>
									 Campus Name
								</th>
								<th>
									 Class Name
								</th>
								<th>
									 Student Name
								</th>
                                <th>
									 Roll No.
								</th>
								<th>
									 CNIC
								</th>
								<th>
									 Phone No.
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
								$payment_plan = $this->db->get_where('payments', array('student_id'=>$student['student_id'], 'contract_id'=>0,'payment_plan!='=>'consulation fee'))->result_array();
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
									<?php echo $student['course_name'];?>
								</td>
								<td>
									<?php echo $student['campus_name'];?>
								</td>
								<td>
									<?php echo $student['class_name'];?>
								</td>
								<td>
									<?php echo $student['first_name'].' '. $student['last_name'];?>
								</td>
								<td>
									<?php echo $student['roll_no'];?>
								</td>
								<td>
									<?php echo $student['cnic'];?>
								</td>
								<td>
									<?php echo $student['mobile'];?>
									<hr />
									<?php echo $student['emergency_no'];?>
								</td>
								<td>
									<?php
                                    	if(@$myAccess[0]['student_edit']==1 || $this->session->userdata('role')=='Admin'):
									?>
									<a title="Edit" href="<?php echo site_url().'/students/edit_student/'.$student['student_id'];?>" class="btn blue"><i class="fa fa-edit"></i></a>
                                    <?php
                                    	endif;
									?>
									
									<?php
                                    	if(@$myAccess[0]['student_payments']==1 || $this->session->userdata('role')=='Admin'):
									?>
                                    <a title="Payments" href="<?php echo site_url().'/students/payments/'.$student['student_id'];?>" class="btn purple"><i class="fa fa-money"></i></a>
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
                </div>
            </div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->