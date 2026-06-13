	
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
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-user"></i> New Admissions Entries
							</div>
						</div>
                        <div class="portlet-body">
							<table class="table table-bordered table-hover" id="sample_12">
							<thead>
							<tr>
                                <th class="hidden">
									 Hidden
								</th>
								<th>
									 Campus
								</th>
                                <th>
									 Class
								</th>
                                <th>
									 Roll #
								</th>
								<th>
									 Name
								</th>
								<!--<th>
									 Student Image
								</th>-->
								<th>
									 Documents
								</th>
                                <th>
									 CNIC
								</th>
                                <th>
									 Fee Information
								</th>
								<th>
									 Admission Information
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
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=0;
								foreach($new_student_entries as $student):
							?>
                            <tr class="odd gradeX">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
								<td>
									<?php echo $student['campus_name']?>
								</td>
                                <td>
									<?php echo $student['class_name']?>
								</td>
                                <td>
									 <?php echo $student['roll_no'];?>
								</td>
								<td>
									<?php echo $student['first_name'].' '.$student['last_name'];?>
								</td>
								<!--<td>
									<?php
										/*
									?>
									<?php
										$student_images = $this->db->get_where('student_documents',array('student_id'=>$student['student_id']))->result_array();
										
										foreach($student_images as $student_image):
											if($student_image['image']!='' && $student_image['online_image']==''):
											echo '<a target="_blank" href="'.base_url().'uploads/'.@$student_image['image'].'"><img height="100" src="'.base_url().'uploads/'.@$student_image['image'].'" alt="" /></a>';
											endif;
											if($student_image['image']!='' && $student_image['online_image']!=''):
											echo '<a target="_blank" href="'.$student_image['online_image'].'"><img height="100" src="'.@$student_image['online_image'].'" alt="" /></a>';
											endif;
										endforeach;
									?>
									<?php
										*/
									?>
								</td>-->
								<td style="text-align:center">
                                	<?php
                                	    $documents = $this->db->get_where('student_documents', array('student_id'=>$student['student_id']))->result_array();
                                	    foreach($documents as $document)
                                	    {
                                	        if($document['type']=='ID Card')
                                	        {
                                	            echo '<i class="fa fa-check"></i> ID card<br />';
                                	        }
                                	        if($document['type']=='Photo')
                                	        {
                                	            echo '<i class="fa fa-check"></i> Photo<br />';
                                	        }
                                	        if($document['type']=='Result Card')
                                	        {
                                	            echo '<i class="fa fa-check"></i> Result Card<br />';
                                	        }
                                	    }
									?>
                                </td>
                                <td>
									<?php echo $student['cnic']?>
								</td>
								<td>
									<?php 
										$paid_payments = $this->db->get_where('payments',array('student_id'=>$student['student_id'],'paid'=>1))->result_array();
										$unpaid_payments = $this->db->get_where('payments',array('student_id'=>$student['student_id'],'paid'=>0))->result_array();
										$total_fee=0;
										
										echo '<strong>Paid Payments</strong><br />';
										foreach($paid_payments as $paid_payment)
										{
											echo $paid_payment['amount'].' paid on '.$paid_payment['paid_date'].'<br />';
											$total_fee+=$paid_payment['amount'];
										}
										echo '<strong>UnPaid Payments</strong><br />';
										foreach($unpaid_payments as $unpaid_payment)
										{
											echo $unpaid_payment['amount'].' will pay on '.$unpaid_payment['dead_line'].'<br />';
											$total_fee+=$unpaid_payment['amount'];
										}
									?>
								</td>
                                <td>
									<?php 
										echo 'Admission Date : '.date('d F,Y',strtotime($student['registration_date'])).'<br />';
										echo 'Admission By : '.$student['add_by'].'<br />';
										echo '<strong>Total Fee : '.$total_fee.'</strong>';
									?>
								</td>
                                <td>
									<?php echo $student['mobile'];?>
                                    <br />
                                    <?php echo $student['emergency_no'];?>
								</td>
                                <td>
									<?php 
										if($student['contractor_id']==0)
										{
											echo 'N/A';
										}
										else
										{
											$contractor = $this->db->get_where('contractors', array('contractor_id'=>$student['contractor_id']))->result_array();
											echo $contractor[0]['name'].' ('.$contractor[0]['date'].')';
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
                                	<a href="<?php echo site_url();?>/dashboard/clear_new_student_entries/<?php echo $student['student_id']?>" target="_blank" class="btn green">Clear</a>
									<br />
									<br />
									<a href="<?php echo site_url();?>/students/payments/<?php echo $student['student_id']?>" target="_blank" class="btn blue">View Payments</a>
									<br />
									<br />
									<a href="<?php echo site_url();?>/students/upload_documents/<?php echo $student['student_id']?>" target="_blank" class="btn yellow">View Documents</a>
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
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->