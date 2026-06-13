<!-- BEGIN CONTENT -->
<?php
$myAccess = checkUserAccess();
?>
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
            <?php endif;

            if (@$myAccess[0]['agent_view_statement'] == '1' || @$this->session->userdata('role') == 'Admin'):
            ?>
            <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> Bank Statement Here
							</div>
						</div>
						<div class="portlet-body">
                            <table class="table table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
                                <th>
									 Sr
								</th>
								<th>
									 Bank Name
								</th>
                                <th>
									 Transaction Date
								</th>
                                <th>
									 Transaction Type
								</th>
                               
                                <th>
									 Credit
								</th>
                               
                                <th>
									 Payment Relate to
								</th>
                                
							</tr>
							</thead>
							<tbody>
							<?php
								$i=0;
								foreach($entries as $closing_rule):
							?>
                            <tr>
                                <td >
                                	<?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $closing_rule['account_title'].' '.$closing_rule['account_name']?>
								</td>
                                <td>
									 <?php echo $closing_rule['trans_date']?>
								</td>
                                <td>
									 <?php echo $closing_rule['description'].' '.$closing_rule['reference_no'];?>
								</td>
								
								<td>
									 <?php echo $closing_rule['credit'];?>
								</td>
								
								<td>
									 <?php
									 echo $closing_rule['trans_id'].'<br />';
									  $this->db->select( 'payments.actual_amount,students.first_name as first_name, students.last_name as last_name, students.mobile as mobile, students.emergency_no as emergency_no,students.cnic as cnic, students.roll_no as roll_no, students.father_name as father_name, classes.name as class_name, campuses.bank_name, courses.course_name as course_name, campuses.account_no, campuses.address, campuses.campus_name, campuses.note, campuses.campus_id,payments.challan_no,payments.paid_date,payments.tid_no,payments.paid_date');
                                            $this->db->from('payments');
                                            $this->db->join('students', 'students.student_id=payments.student_id', 'left');
                                            $this->db->join('classes', 'classes.class_id=students.class_id', 'left');
                                            $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
											$this->db->join('courses', 'courses.course_id=students.course_id', 'left');
											$student=$this->db->where('payments.statement_id', $closing_rule['trans_id'])->get()->result_array();

									 if(count($student) > 0)
									 {
                                     foreach ($student as $dat_payments):
                                         ?>
                                         <strong>Challan No : </strong><?php echo $dat_payments['challan_no'];?> <br>
                                         <strong>Amount : </strong><?php echo $dat_payments['actual_amount'];?> <br>
                                         <strong>Challan TID : </strong><strong style="color: #00CC00"><?php echo $dat_payments['tid_no'];?> </strong><br>
                                         <strong>Challan Paid Date : </strong><?php echo $dat_payments['paid_date'];?> <br>
                                         <strong>Roll No : </strong><?php echo $dat_payments['roll_no'];?> <br>
                                         <strong>Name : </strong><?php echo $dat_payments['first_name'].' '.$dat_payments['last_name'];?> <br>
                                         <strong>CNIC : </strong><?php echo $dat_payments['cnic']?> <br>
                                         <strong>Contact Details : </strong><?php echo $dat_payments['mobile'];?> <br>
                                         <strong>Emergency Contact : </strong><?php echo $dat_payments['emergency_no'];?><br />
                                         <strong>Campus : </strong><?php echo $dat_payments['campus_name'];?> <br>
                                         <strong>Class : </strong><?php echo $dat_payments['class_name'];?> <br>
                                         <strong>Course : </strong><?php echo $dat_payments['course_name'];?> <br><br>
                                     <?php
                                     endforeach;
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
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>
			<?php
            endif;?>
            <!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->