
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
			<?php if(@$this->session->userdata('error')):?>
                <div class="alert alert-danger">
                    <button class="close" data-close="alert"></button>
                    <span>
                    <?php echo $this->session->userdata('error');?> </span>
                </div>
            <?php endif;?>
			
			<div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> All Sessions
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
									 Course
								</th>
								<th>
									 Session
								</th>
								<th>
									 Dead Line For Add / Edit Student
								</th>
								<th>
									 Last Date of auto fee installment
								</th>
								<th>
									 Per Student Fee
								</th>
								<th>
									 Maximum Last Date of Fee This Session
								</th>
								<th>
									 Minimum Student Installment Fee This Session
								</th>
								<th>
									 Maximum Days Difference between installments
								</th>
								<!--<th>-->
								<!--	 Council/board Fee-->
								<!--</th>-->
								<th>
									 1st Time Council/Board Exam Number
								</th>
								<!--<th>-->
								<!--	 First Time Council/Board Fee-->
								<!--</th>-->
								<!--<th>-->
								<!--	 Last Date of Council/Board Fee-->
								<!--</th>-->
								<th>
									 Re Admission Fee
								</th>
								<th>
									 Freeze Fee
								</th>
								<th>
									 Maximum Student Freeze Date
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i = 0;
								foreach($sessions as $session):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $session['course_session_id']?>
								</td>
                                <td>
									 <?php echo $session['course_name']?>
								</td>
								<td>
									 <?php echo $session['session_name']?>
								</td>
								<td>
									 <?php echo $session['dead_line_add_edit_student']?>
								</td>
								<td>
									 <?php echo $session['last_date_auto_fee_installment']?>
								</td>
								<td>
									 <?php echo $session['per_student_fee']?>
								</td>
								<td>
									 <?php echo $session['maximum_fee_last_date']?>
								</td>
								<td>
									 <?php echo $session['minimum_installment_fee']?>
								</td>
								<td>
									 <?php echo $session['maximum_difference_installments']?>
								</td>
								<!--<td>-->
								<!--	 <?php echo $session['council_board_fee']?>-->
								<!--</td>-->
								<td>
									 <?php echo $session['first_council_exam_no']?>
								</td>
								<!--<td>-->
								<!--	 <?php echo $session['first_time_council_fee']?>-->
								<!--</td>-->
								<!--<td>-->
								<!--	 <?php echo $session['last_date_council_fee']?>-->
								<!--</td>-->
								<td>
									 <?php echo $session['re_admission_fee']?>
								</td>
								<td>
									 <?php echo $session['freeze_fee']?>
								</td>
								<td>
									 <?php echo $session['freeze_last_date']?>
								</td>
								<td>
									
                                    <a href="<?php echo site_url().'/course_sessions/edit_course_session/'.$session['course_session_id'];?>" title="Edit" class="btn blue"><i class="fa fa-edit"></i></a>
                                    
                                    <a onclick="return confirm('Are you sure you want to delete this Course Session?')" href="<?php echo site_url().'/course_sessions/delete_course_session/'.$session['course_session_id'];?>" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>
                                    
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