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
            <!-- BEGIN DASHBOARD STATS -->
			<!--<div class="row">
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat blue-madison">
						<div class="visual">
							<i class="fa fa-graduation-cap"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php //echo count($students);?>
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
			</div>-->
			<!-- END DASHBOARD STATS -->
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
								<i class="fa fa-plus"></i> Filter Students
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal student_checker_form" role="form" method="post" action="<?php echo site_url();?>/students/all_students">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control campus" name="campus_id">
                                                <option value="">ALL CAMPUS</option>
												<?php
													foreach($campuses as $campus):
												?>
                                                <option value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name'];?></option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                            <!--<span class="help-inline"></span>-->
                                        </div>
                                    </div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Course <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control course_id" name="course_id">
                                                <option value="">ALL COURSE</option>
												<?php
													foreach($courses as $course):
												?>
                                                <option value="<?php echo $course['course_id'];?>"><?php echo $course['course_name'];?></option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                        </div>
                                    </div>
									
									<div class="search_type" style="display:none;">
										<div class="form-group">
											<label class="col-md-3 control-label">Search Type</label>
											<div class="col-md-9 radio-list">
												<label class="radio-inline">
												<input type="radio" name="search_type" id="optionsRadios1" value="classwise" checked> Class Wise </label>
												<label class="radio-inline">
												<input type="radio" name="search_type" id="optionsRadios2" value="councilwise"> Council Exam No. Wise (Student Submit fee in college)</label>
                                                <label class="radio-inline">
                                                    <input type="radio" name="search_type" id="optionsRadios3" value="councilwise_roll_no"> According to Roll no of Council(Fee/Papers/Information)</label>
											</div>
										</div>
									</div>
									
									<div class="council" style="display:none;">
										<div class="form-group">
											<label class="col-md-3 control-label">Coucil Exam No. <span class="required">*</span></label>
											<div class="col-md-9">
												<input type="text" class="form-control input-inline input-large" name="council_exam_no" placeholder="Enter Council Exam No." value="" >
												<span class="help-inline"></span>
											</div>
										</div>
									</div>

                                    <div class="according_to_roll_no" style="display:none;">
										<div class="form-group">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Type</label>
                                                <div class="col-md-9 radio-list">
                                                    <label class="radio-inline">
                                                        <input type="radio" name="class" id="optionsRadios1" value="1" checked> 1st Year </label>
                                                    <label class="radio-inline">
                                                        <input type="radio" name="class" id="optionsRadios2" value="2"> 2nd Year </label>
                                                </div>
                                            </div>
										</div>
									</div>
									
									<div class="class">
										<div class="form-group">
											<label class="col-md-3 control-label">Class <span class="required">*</span></label>
											<div class="col-md-5">
												<select class="form-control classes" name="class_id">
												</select>
												<!--<span class="help-inline"></span>-->
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label">Type</label>
											<div class="col-md-9 radio-list">
												<label class="radio-inline">
												<input type="radio" name="type" class="type" id="optionsRadios1" value="active" checked> Active Students </label>
												<label class="radio-inline">
												<input type="radio" name="type" class="type" id="optionsRadios2" value="pass"> Passed Students </label>
												<label class="radio-inline">
												<input type="radio" name="type" class="type" id="optionsRadios3" value="both"> Both </label>
                                                <label class="radio-inline">
												<input type="radio" name="type" class="type" id="optionsRadios4" value="blacklist"> Blacklist Students </label>
												<?php if(@$myAccess[0]['council_list_report']==1 || $this->session->userdata('role')=='Admin'): ?>
                                                <label class="radio-inline">
                                                <input type="radio" name="type" class="type" id="optionsRadios5" value="councel_list">Councel List</label>
                                                <?php endif; ?>
                                                <label class="radio-inline">
                                                <input type="radio" name="type" class="type" id="optionsRadios7" value="archived">Archive</label>
                                                
												<label class="radio-inline">
                                                <input type="radio" name="type" class="type" id="optionsRadios6" value="studentdetail">Student Full Detail Report</label>
												<label class="radio-inline">
                                                <input type="radio" name="type" class="type" id="optionsRadios8" value="shift">Shift Wise Report</label>
                                                <label class="radio-inline">
                                                <input type="radio" name="type" class="type" id="optionsRadios9" value="using_app">Using Mobile App</label>
                                                <label class="radio-inline">
                                                <input type="radio" name="type" class="type" id="optionsRadios10" value="attendance">Attendance</label>
												
											</div>
										</div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                            <input name="form_submit" type="hidden" value="1" />
                                            <button type="submit" class="btn green">Check</button>
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
            	if(@$this->input->post('form_submit')&&
                    @$this->input->post('search_type') != "councilwise_roll_no" && @$this->input->post('type')!='blacklist' &&
                    @$this->input->post('type')!='councel_list' && @$this->input->post('type')!='studentdetail' &&
                    @$this->input->post('type')!='archived' && @$this->input->post('type')!='shift' && @$this->input->post('type')!='using_app'):
			?>
                    <button id="print-btn" type="button" class="btn btn-primary btn-sm d-print-none"><i class="dripicons-print"></i> Print</button>
            <div class="row" id="print-div">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i>All Students
							</div>
						</div>
						<div class="portlet-body">
						
						 <div class="col-md-4 text-center" style="margin-bottom:15px">
                                <form method="post" action="<?php echo site_url();?>/documents/print_struck_off_letters" target="_blank">
                                    <input type="hidden" name="student_ids" class="student_ids form-group" value="" required />
                                    <input type="submit" class="btn red" value="Get Selected Students Struckoff Letters" />
                                </form>
                            </div>
						
							<table class="table table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
                                <th class="hidden">
									 Hidden
								</th>
                                <th>
                                    Selection
                                </th>
                                <th>
                                	Student Information &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </th>
								<th>
									 Student Image
								</th>
                                <th>
									 Contractor
								</th>
                                <th>
									 Extra Information &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								</th>
								<th>
									 Result Remarks
								</th>
								<th>
									 Council Fee Remarks
								</th>
								<th>
									 Add By / Last Edit
								</th>
                                <th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=0;
								foreach($students as $student):
								
									//CHECK ACTIVE OR PASS STUDENTS CHECK
									$this->db->select('*');
									$this->db->from('punjab_council_roll_number');
									$this->db->where('cnic', $student['cnic']);
									$this->db->order_by('id', "DESC");
									$results = $this->db->get()->result_array();
									
									$show=0;
									if(@$this->input->post('type')=='pass'):
										foreach($results as $result)
										{
											if($result['class']=='2' && $result['result_remarks']=='Pass')
											{
												$show=1;
											}
											if($result['class']=='2' && $result['result_remarks']=='Pass*')
											{
												$show=1;
											}
										}
									endif;
									
									if(@$this->input->post('type')=='active'):
//										if(count($results)<1)
//										{
											$show=1;
//										}

                                        //if(@$results[0]['class']=='2' && (@$results[0]['result_remarks']=='Pass' || @$results[0]['result_remarks']=='Pass*'))
                                        //{
                                        //    $show=0;
                                        //}

									endif;
									
									if(@$this->input->post('type')=='both'):
										$show=1;
									endif;

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
									
									if($show==1):
							?>
                            <tr class="odd gradeX <?php echo $payment_alert;?>">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
								
								<td>
                                    <input type="checkbox" class="selection" name="selection" value="<?php echo $student['student_id'];?>" />
                                </td>
								
                                <td>
                                	Campus : <?php echo $student['campus_name'];?>
                                    <br />
                                    Course : <?php echo $student['course_name'];?>
                                    <br />
                                    Session : <span class="bold"><?php echo $student['session'];?></span>
                                    <br />
                                    Class : <?php echo $student['class_name'];?>
                                    <br />
                                    Student Name : <span class="bold"><?php echo $student['first_name'].' '.$student['last_name'];?></span>
                                    <br />
                                    CNIC : <?php echo $student['cnic'];?>
                                    <br />
                                    Father Name : <?php echo $student['father_name'];?>
                                    <br />
                                    Roll # : <span class="bold"><?php echo $student['roll_no'];?></span>
                                    <br />
                                    Mobile : <span class="bold"><?php echo $student['mobile'];?> - <?php echo $student['emergency_no'];?></span>
                                    <br />
                                    <?php
                                        if($student['reference_user_id']!=NULL && $student['reference_user_id']!='' && $student['reference_user_id']!=0)
                                        {
                                            $reference = $this->db->get_where('reference_users',array('reference_user_id'=>$student['reference_user_id']))->result_array();
                                            echo 'Reference User : <span class="bold blink_me alert-success">'.@$reference[0]['name'].' - '.@$reference[0]['phone'].'</span>';
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
                                    <br />
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
                                    	$b_form = $this->db->get_where('student_documents', array('type'=>'B - FORM', 'student_id'=>$student['student_id']))->result_array();
										if(count($b_form)>0):
									?>
                                    <i class="fa fa-check"></i> B - FORM
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
									<?php 
										if($student['contractor_id']==0)
										{
											echo 'N/A';
										}
										else
										{
											$this->db->select('*');
											$this->db->from('contracts');
											$this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','INNER');
											$this->db->where('contract_id',$student['contract_id']);
											$contract_details = $this->db->get()->result_array();
											
											echo @$contract_details[0]['name'].' ('.@$contract_details[0]['contract_name'].')';
										}
									?>
								</td>
                                <td>
                                	Registration Date : <?php echo $student['registration_date'];?>
                                    <br />
                                    Machine ID : <?php echo $student['machine_id'];?>
                                    <br />
									Section : <?php echo $student['section'];?>
                                    <br />
                                    Shift : <?php echo $student['shift'];?>
                                    <br />
                                    Study Type : <?php echo $student['study_type'];?>
                                    <br />
                                    Student Card : <?php if($student['student_card']==1){echo '<i class="fa fa-check"></i>';}else{echo '<i class="fa fa-close"></i>';}?>
									<br />
                                    1st Year Books : <?php if($student['books_1']==1){echo '<i class="fa fa-check"></i>';}else{echo '<i class="fa fa-close"></i>';}?>
                                    <br />
                                    2nd Year Books : <?php if($student['books_2']==1){echo '<i class="fa fa-check"></i>';}else{echo '<i class="fa fa-close"></i>';}?>
                                    <br />
									<?php
										$payments = $this->db->get_where('payments',array('student_id'=>$student['student_id']))->result_array();
										$total_unpaid_payments=0;
										$total_unpaid_payments_till_date=0;
										
										foreach($payments as $payment)
										{
											if($payment['paid']==0)
											{
												$total_unpaid_payments++;
											}
											if($payment['paid']==0 && $payment['dead_line']<date('Y-m-d'))
											{
												$total_unpaid_payments_till_date++;
											}
										}
										
										if($total_unpaid_payments==0)
										{
											echo '<span class="alert-success">All Payments paid</span>';
										}
										else
										{
											echo 'Pending Installments = '.$total_unpaid_payments;
											echo '<br />';
											echo '<span class="bold">Pending Intallments Till Date = '.$total_unpaid_payments_till_date.'</span>';
										}
										
									?>
                                </td>
                                <td>
                                	<?php getStudentResultRemarks($student['cnic']);?>
                                </td>
								<td>
                                    <?php
                                    $this->db->select('*');
                                    $this->db->from('expenses');
                                    $this->db->join('classes', 'classes.class_id=expenses.class_id', 'left');
                                    $this->db->join('campuses','campuses.campus_id=expenses.campus_id', 'left');
                                    $this->db->where(array('student_id'=>$student['student_id']));
                                    $council_fees = $this->db->get()->result_array();

                                    foreach($council_fees as $council_fee)
                                    {
                                        $class = "";
                                        if ($council_fee['class'] == "1")
                                        {
                                            $class = "1st Year";
                                        }
                                        else
                                        {
                                            $class = "2nd Year";
                                        }

                                        echo 'Exam No. : '.$council_fee['council_exam_no'];
                                        echo '<br />';
                                        echo 'Submit Date : '.$council_fee['date'];
                                        echo '<br />';
                                        echo 'Amount : '.$council_fee['amount'];
                                        echo '<br />';
                                        echo 'Roll # : '.$council_fee['roll_no'];
                                        echo '<br />';
                                        echo 'Class  : '.$class;
                                        echo '<br />';
                                        echo 'Campus : '.$council_fee['campus_name'];
                                        echo '<br />';
                                        echo 'Session  : '.$council_fee['session'];
                                        echo '<br /><hr />';
                                    }
                                    ?>
                                </td>
                                <td>
                                	Add By : <?php echo $student['add_by'];?>
                                    <br />
                                    Last Edit : <?php echo $student['last_edit'];?>
                                </td>
								<td>
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
                                    	if(@$student['contract_id']==0):
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
                                    <a target="_blank" title="Struckof" class="btn red" href="<?php echo site_url();?>/students/struckofstudentview/<?php echo $student['student_id'];?>"><i class="fa fa-ban"></i></a>
                                    <?php
                                    	endif;
									?>
                                    <?php
                                    if(@$myAccess[0]['can_student_struckof']==1 || $this->session->userdata('role')=='Admin'):
                                        ?>
                                        <a title="Freeze" href="<?php echo site_url().'/students/freezestudentview/'.$student['student_id'];?>" class="btn blue"><i class="fa fa-ban"></i></a>
                                    <?php
                                    endif;
                                    ?>
                                    <a target="_blank" style="margin-bottom:5px;margin-top: 5px" href="<?php echo site_url();?>/documents/student_all_document/<?php echo $student['student_id'];?>" class="btn purple" ><i class="fa fa-print">Student All Document</i></a>
                                    <a title="Purchased Products" href="<?php echo site_url().'/students/purchased_products/'.$student['student_id'];?>" class="btn green"><i class="fa fa-shopping-cart"></i></a>
                                    <br>
								</td>
							</tr>
                            <?php
									endif;
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
            	endif;
			?>

            <?php
            	if(@$this->input->post('form_submit') && @$this->input->post('type')=='blacklist'):
			?>
            <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i>Blacklist Students
							</div>
						</div>
						<div class="portlet-body">
							<table class="table table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
                                <th class="hidden">
									 Hidden
								</th>
                                <th>
                                	Student Information &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </th>
								<th>
									 Fee Information
								</th>
                                <th>
									 Paid Fee Details
								</th>
                                <th>
									 Unpaid Fee Details
								</th>
                                <th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=0;
								foreach($students as $student):
									$show=0;
									//CHECK FEE
									$this->db->order_by('dead_line','ASC');
									$payments=$this->db->get_where('payments',array('student_id'=>$student['student_id']))->result_array();
									$total_fee = 0;
									$created_council_fee = 0;
									$submitted_council_fee = 0;
									$fee_decided_current_time = 0;
									$total_fee_submitted = 0;
									$unpaid_installments_current_time = 0;
									foreach($payments as $payment)
									{
										if($payment['payment_plan']!='consulation fee')
										{
											$total_fee+=$payment['amount'];
										}
										if($payment['payment_plan']=='consulation fee')
										{
											$created_council_fee+=$payment['amount'];
											if($payment['paid']==1)
											{
												$submitted_council_fee+=$payment['actual_amount'];
											}
										}
										if($payment['dead_line']<date('Y-m-d'))
										{
											$fee_decided_current_time+=$payment['amount'];
											if($payment['paid']==0)
											{
												$unpaid_installments_current_time++;
											}
										}
										if($payment['paid']==1 && $payment['payment_plan']!='consulation fee')
										{
											$total_fee_submitted+=$payment['actual_amount'];
										}
										//CHECK ANY PAYMENT 1 MONTH BEFORE
										$oneMonthOldDate = date("Y-m-d", strtotime( date( "Y-m-d", strtotime( date("Y-m-d") ) ) . "-1 month" ) );
										if($payment['paid']==0 && $payment['dead_line']<$oneMonthOldDate)
										{
											$show=1;
										}
									}
									if($student['status']==0)
									{
										$show=0;
									}
									
									if($show==1):
							?>
                            <tr class="odd gradeX">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
                                <td>
                                	Campus : <?php echo $student['campus_name'];?>
                                    <br />
                                    Course : <?php echo $student['course_name'];?>
                                    <br />
                                    Session : <span class="bold"><?php echo $student['session'];?></span>
                                    <br />
                                    Class : <?php echo $student['class_name'];?>
                                    <br />
                                    Registration Date : <span class="bold"><?php echo $student['registration_date'];?></span>
                                    <br />
                                    Student Name : <span class="bold"><?php echo $student['first_name'].' '.$student['last_name'];?></span>
                                    <br />
                                    CNIC : <?php echo $student['cnic'];?>
                                    <br />
                                    Father Name : <?php echo $student['father_name'];?>
                                    <br />
                                    Roll # : <span class="bold"><?php echo $student['roll_no'];?></span>
                                    <br />
                                    Mobile : <span class="bold"><?php echo $student['mobile'];?> - <?php echo $student['emergency_no'];?></span>
                                    <?php
                                        if($student['reference_user_id']!=NULL && $student['reference_user_id']!='' && $student['reference_user_id']!=0)
                                        {
                                            $reference = $this->db->get_where('reference_users',array('reference_user_id'=>$student['reference_user_id']))->result_array();
                                            echo 'Reference User : <span class="bold blink_me alert-success">'.@$reference[0]['name'].' - '.@$reference[0]['phone'].'</span>';
                                        }
                                    ?>
                                </td>
                                <td>
                                	Total Fee : <?php echo $student['total_fee'];?>
                                    <br />
                                    Total Created Fee : <?php echo $total_fee;?>
                                    <br />
                                    Total Created Council Fee : <?php echo $created_council_fee;?>
                                    <br />
                                    Total Submitted Council Fee : <?php echo $submitted_council_fee;?>
                                    <br />
                                    Fee Decided Current Time : <span class="bold"><?php echo $fee_decided_current_time;?></span>
                                    <br />
                                    Total Fee Submitted : <span class="bold"><?php echo ($total_fee_submitted+$submitted_council_fee);?></span>
                                    <br />
                                    Remaining Fee Payable Current Time : <span class="bold"><?php echo $fee_decided_current_time-$total_fee_submitted;?></span>
                                    <br />
                                    Unpaid installments Current Time : <span class="bold"><?php echo $unpaid_installments_current_time;?></span>
                                    <br />
                                    Percentage Fee Received : <?php echo round(($total_fee_submitted/$total_fee)*100).'%';?>
                                    <br />
                                    Percentage Paid Fee According to Decision : <?php echo round(($total_fee_submitted/$fee_decided_current_time)*100).'%';?>
                                </td>
                                <td>
                                	<?php
                                    	foreach($payments as $payment)
										{
											if($payment['paid']==1)
											{
												echo $payment['actual_amount'].' Paid on '.$payment['paid_date'].'<br />';
											}
										}
									?>
                                </td>
                                <td>
                                	<?php
                                    	foreach($payments as $payment)
										{
											if($payment['paid']==0)
											{
												if($payment['dead_line']<date('Y-m-d'))
												{
													echo '<span class="bold">'.$payment['amount'].' Not Paid on '.$payment['dead_line'].'</span><br />';
												}
												else
												{
													echo $payment['amount'].' Not Paid on '.$payment['dead_line'].'<br />';
												}
											}
										}
									?>
                                </td>
								<td>
                                	<?php
                                    	if(@$student['contract_id']==0):
									?>
										<?php
                                            if(@$myAccess[0]['student_payments']==1 || $this->session->userdata('role')=='Admin'):
                                        ?>
                                        <a title="Payments" href="<?php echo site_url().'/students/payments/'.$student['student_id'];?>" class="btn purple"><i class="fa fa-money"></i></a>
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
                                    <a target="_blank" title="Delete" class="btn red" href="<?php echo site_url();?>/students/struckofstudentview/<?php echo $student['student_id'];?>"><i class="fa fa-ban"></i></a>
                                    <?php
                                    	endif;
									?>
                                    <?php
                                    if(@$myAccess[0]['can_student_struckof']==1 || $this->session->userdata('role')=='Admin'):
                                        ?>
                                        <a title="Payments" href="<?php echo site_url().'/students/freezestudentview/'.$student['student_id'];?>" class="btn blue"><i class="fa fa-ban"></i></a>
                                    <?php
                                    endif;
                                    ?>
                                    <a target="_blank" style="margin-bottom:5px;margin-top: 5px" href="<?php echo site_url();?>/documents/student_all_document/<?php echo $student['student_id'];?>" class="btn purple" ><i class="fa fa-print">Student All Document</i></a>
                                    <br>
								</td>
							</tr>
                            <?php
									endif;
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
            	endif;
			?>

            <?php
                if(@$this->input->post('form_submit') && @$this->input->post('type')=='councel_list' ){
                ?>
                <div class="row" style="margin-bottom: 10px">
                    <div class="col-md-12">
                        <!--                <button  class="btn green">Print</button>-->
                        <!--                <button onclick="printDiv('printMe')">Print only the above div</button>-->
                        <a href="<?php echo site_url();?>/council_list/get_print_of_concel_list/<?php echo $campus_id;?>/<?php echo $class_id;?>" target="_blank" class="btn green">Print</a>
                        <a href="<?php echo site_url();?>/council_list/get_print_of_new_concel_list/<?php echo $campus_id;?>/<?php echo $class_id;?>" target="_blank" class="btn green">Print New Council List</a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <!-- BEGIN EXAMPLE TABLE PORTLET-->
                        <div class="portlet box grey-cascade">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-list"></i>All Students Councel List   <?php 
									$campus_nam=$this->db->get_where('campuses',array('campus_id'=>$campus_id))->row()->campus_name;
									$class_nam=$this->db->get_where('classes',array('class_id'=>$class_id))->row()->name;
									echo ' ('.@$campus_nam .' - ' .@$class_nam .' )   '.@$this->input->post('type')?>
                                </div>
                            </div>
                            <div class="portlet-body">

                                <div class="row">
                                    <div class="col-md-3 text-center" style="margin-bottom:15px">
                                        <form method="post" action="<?php echo site_url();?>/students/print_selected_student_councel_list" target="_blank">
                                            <input type="hidden" name="student_ids" class="student_ids form-group" value="" required />
                                            <input type="hidden" name="campus_id" class=" form-group" value="<?php echo $campus_id;?>" required />
                                            <input type="hidden" name="class_id" class=" form-group" value="<?php echo $class_id;?>" required />
                                            <button type="submit" class="btn green"><i class="fa fa-check-square-o" aria-hidden="true"></i> Students Councel List</button>
                                        </form>
                                        <a style="margin-top: 10px" href="<?php echo site_url();?>/council_list/get_print_of_concel_list/<?php echo $campus_id;?>/<?php echo $class_id;?>" target="_blank" class="btn green"><i class="fa fa-file-text-o" aria-hidden="true"></i> Students Councel List</a>
                                    </div>

                                    <div class="col-md-3 text-center" style="margin-bottom:15px">
                                        <form method="post" action="<?php echo site_url();?>/students/print_selected_student_councel_document" target="_blank">
                                            <input type="hidden" name="student_ids" class="student_ids form-group" value="" required />
                                            <button type="submit" class="btn green"><i class="fa fa-check-square-o" aria-hidden="true"></i> Students Councel Document</button>
                                        </form>
                                        <a style="margin-top: 10px" href="<?php echo site_url();?>/students/print_all_student_councel_document/<?php echo $class_id;?>" target="_blank" class="btn green"><i class="fa fa-file-text-o" aria-hidden="true"></i> Students Councel Document</a>
                                    </div>

                                    <div class="col-md-3">
                                        <button type="button" class="btn green" data-toggle="modal" data-target="#myModal"><i class="fa fa-bars" aria-hidden="true"></i> Students Selective Councel Document </button>
                                    </div>

                                    <!-- Modal -->
                                    <div id="myModal" class="modal fade" data-width="600" role="dialog">
                                        <div class="modal-dialog">

                                            <!-- Modal content-->
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    <h4 class="modal-title">Get Selected Students Document With Type</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <form  method="post" action="<?php echo site_url();?>/students/selected_documents_with_type">
                                                        <input type="hidden" name="student_ids" class="student_ids form-group" value="" required />
                                                        <div class="form-group row">
                                                            <label class="col-md-3 control-label">Type <span class="required">*</span></label>
                                                            <div class="col-md-5">
                                                                <select class="form-control" name="select_doc_type" id="select_doc_type" required>
                                                                    <option value="">Select Document Type</option>
                                                                    <option value="ID Card">ID Card</option>
                                                                    <option value="Photo">Photo</option>
                                                                    <option value="Result Card">Result Card</option>
                                                                    <option value="B-Form">B-Form</option>
                                                                    <option value="admission">Student Admission Letter</option>
                                                                    <option value="council">Council Admission Form</option>
                                                                    <option value="council_character_certificate">Council Character Certificate</option>
                                                                    <option value="council_admission_letter">Council Admission Letter</option>
                                                                    <option value="Other">Other</option>
                                                                </select>
                                                                <!--<span class="help-inline"></span>-->
                                                            </div>
                                                        </div>

                                                        <div class="form-group row">
                                                            <label class="col-md-3 control-label">Quantity <span class="required">*</span></label>
                                                            <div class="col-md-5">
                                                                <input type="text" class="form-control input-inline " style="width: 100%" name="qty" placeholder="Enter Document Quantity." />
                                                                <!--<span class="help-inline"></span>-->
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <button type="submit" class="btn green"><i class="fa fa-print" aria-hidden="true"></i> Print</button>

                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                <input type="checkbox" id="checkAll" class="all_selection" name="all_selection"/> Select All
                                </div>
                                <br /> <br />
                                <div class="row">
                                    <div class="col-md-3">
                                        <form method="post" action="<?php echo site_url();?>/students/download" target="_blank">
                                            <input type="hidden" name="student_ids" class="student_ids form-group" value="" required />
                                            <button type="submit" class="btn purple"><i class="fa fa-excel" aria-hidden="true"></i> Download Excel File</button>
                                        </form>
                                    </div>

                                    <div class="col-md-3">
                                        <form method="post" action="<?php echo site_url();?>/students/download_photos" target="_blank">
                                            <input type="hidden" name="student_ids" class="student_ids form-group" value="" required />
                                            <input type="hidden" name="class_id" class="form-group" value="<?php echo $this->input->post('class_id');?>" required />
                                            <button type="submit" class="btn purple"><i class="fa fa-excel" aria-hidden="true"></i> Download Photos</button>
                                        </form>
                                    </div>

                                    <div class="col-md-3">
                                        <form method="post" action="<?php echo site_url();?>/students/download_cnic" target="_blank">
                                            <input type="hidden" name="student_ids" class="student_ids form-group" value="" required />
                                            <input type="hidden" name="class_id" class="form-group" value="<?php echo $this->input->post('class_id');?>" required />
                                            <button type="submit" class="btn purple"><i class="fa fa-excel" aria-hidden="true"></i> Download CNIC</button>
                                        </form>
                                    </div>

                                    <div class="col-md-3">
                                        <form method="post" action="<?php echo site_url();?>/students/download_result_card" target="_blank">
                                            <input type="hidden" name="student_ids" class="student_ids form-group" value="" required />
                                            <input type="hidden" name="class_id" class="form-group" value="<?php echo $this->input->post('class_id');?>" required />
                                            <button type="submit" class="btn purple"><i class="fa fa-excel" aria-hidden="true"></i> Download Result Card</button>
                                        </form>
                                    </div>
                                    <br /><br />
                                </div>
                                <table class="table table-striped table-bordered table-hover" id="sample_ali">
                                        <thead>
                                        <tr>
                                            <th >
                                                Sr.No
                                            </th>
                                            <th>
                                                Roll #
                                            </th>
                                            <th>
                                                CNIC No
                                            </th>
                                            <th>
                                                Name & Father Name
                                            </th>
                                           
                                            <th>
                                                Mobile #
                                            </th>
											<th>
                                                Total Fee
                                            </th>
											<th>
                                                Fee Decided till Time
                                            </th>
                                            <th>
                                               Paid Fee
                                            </th>
                                            <th>
                                                Payable current Time
                                            </th>
											<th>
                                                Unpaid fee till
                                            </th>
											<th>
                                                Fee Paid Detail &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            </th>
											<th>
                                                Fee UnPaid Detail &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            </th>
                                            <th>
                                                Documents
                                            </th>
											<th>
                                                Renewed I
                                            </th>
                                            <th>
                                                Action
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $i=1;
                                        foreach($result as $list):
										$student=$list;
										
											$totalStudentFee = $this->council->getTotalFeeDetail($student['student_id']); 
												$totalfee= $totalStudentFee[0]['amount'];
												
												$studentFeeDecidedCurrentTime = $this->council->getFeeDecidedCurrentTime($student['student_id']);
												$totalfeedecided= $studentFeeDecidedCurrentTime[0]['amount'];
												
												$totalStudentPaidFee = $this->council->getTotalPaidFeeDetail($student['student_id']); 
												
												$totalStudentPaidFeecurrent= $studentFeeDecidedCurrentTime[0]['amount']-$totalStudentPaidFee;
												
												$studentUnpaidFeeCurrentTime = $this->council->getUnpaidFeeDetailCurrentTime($student['student_id']);
												$totalStudentunPaidFeecurrent= count($studentUnpaidFeeCurrentTime);
												
												$studentPaidFee = $this->council->getPaidFeeDetail($student['student_id']);
												$len = count($studentPaidFee);
												$feeHTML = '';
												foreach($studentPaidFee as $key=>$feeStatus)
												{
													
													if($key == $len-1){
														
														$feeHTML.= '<span class="bold"> Rs '.$feeStatus['actual_amount'].' paid on '.$feeStatus['actual_paid_date'];
														$feeHTML.= '</span><br />';
														
														
														
													}else
													{
														$feeHTML.= 'Rs '.$feeStatus['actual_amount'].' paid on '.$feeStatus['actual_paid_date'];
														$feeHTML.= '<br />';
													}
												}
												
												
												$studentUnpaidFee = $this->council->getUnpaidFeeDetail($student['student_id']);
												
												$feeHTMLUN = '';
												foreach($studentUnpaidFee as $payment)
												{
													
														if($payment['dead_line']<date('Y-m-d'))
														{
															$feeHTMLUN.= '<span class="bold">'.$payment['amount'].' Not Paid on '.$payment['dead_line'].'</span><br />';
														}
														else
														{
															$feeHTMLUN.= $payment['amount'].' Not Paid on '.$payment['dead_line'].'<br />';
														}
													
													
												}
												
												
																							
												$renewInstallments = $this->council->renewInstallments($student['student_id']);
												$renewInstallments=count($renewInstallments);
												
										
										
                                            ?>
                                            <tr class="odd gradeX">
                                                <td >
                                                    <?php echo $i;?>
                                                </td>
                                                <td>
                                                    <?php echo $list['roll_no'];?>
                                                </td>
                                                <td>
                                                    <?php echo $list['cnic'];?>
                                                </td>
                                                <td>
                                                    <?php echo $list['name'];?>
                                                </td>
                                               
                                                <td>
                                                    <?php echo $list['mobile'].'<br />'.$list['emergency_no'];?>
                                                </td>
                                                <td>
                                                    <?php echo 'RS '.$totalfee;?>
                                                </td>
												<td>
                                                    <?php echo 'RS '.$totalfeedecided;?>
                                                </td>
												<td>
                                                    <?php echo 'RS '.$totalStudentPaidFee;?>
                                                </td>
												<td>
                                                    <?php echo 'RS '.$totalStudentPaidFeecurrent;?>
                                                </td>
												<td>
                                                    <?php echo 'RS '.$totalStudentunPaidFeecurrent;?>
                                                </td>
												<td>
                                                    <?php echo $feeHTML;?>
                                                </td>
												<td>
                                                    <?php echo $feeHTMLUN;?>
                                                </td>
												<td>
                                                    <?php
                                                        //PHOTO CHECK
                                                        $photo = $this->db->get_where('student_documents',array('student_id'=>$student['student_id'],'type'=>'Photo'))->result_array();
                                                        if(count($photo)>0)
                                                        {
                                                            echo '<button class="btn green"><i class="fa fa-check"></i> Photo</button>';
                                                        }
                                                        else
                                                        {
                                                            echo '<button class="btn red"><i class="fa fa-remove"></i> Photo</button>';
                                                        }
                                                        //RESULT CARD CHECK
                                                        $result_card = $this->db->get_where('student_documents',array('student_id'=>$student['student_id'],'type'=>'Result Card'))->result_array();
                                                        if(count($result_card)>0)
                                                        {
                                                            echo '<button class="btn green"><i class="fa fa-check"></i> Result Card</button>';
                                                        }
                                                        else
                                                        {
                                                            echo '<button class="btn red"><i class="fa fa-remove"></i> Result Card</button>';
                                                        }
                                                        //ID CARD CHECK
                                                        $id_card = $this->db->get_where('student_documents',array('student_id'=>$student['student_id'],'type'=>'ID Card'))->result_array();
                                                        if(count($id_card)>0)
                                                        {
                                                            $identity = '<button class="btn green"><i class="fa fa-check"></i> Identity</button>';
                                                        }
                                                        else
                                                        {
                                                            $identity = '<button class="btn red"><i class="fa fa-remove"></i> Identity</button>';
                                                        }
                                                        //BFORM CHECK
                                                        if(count($id_card)<1)
                                                        {
                                                            $bform = $this->db->get_where('student_documents',array('student_id'=>$student['student_id'],'type'=>'B - FORM'))->result_array();
                                                            if(count($bform)>0)
                                                            {
                                                                $identity = '<button class="btn green"><i class="fa fa-check"></i> Identity</button>';
                                                            }
                                                            else
                                                            {
                                                                $identity = '<button class="btn red"><i class="fa fa-remove"></i> Identity</button>';
                                                            }
                                                        }
                                                        echo $identity;
                                                    ?>
                                                </td>
												<td>
                                                    <?php echo $renewInstallments;?>
                                                </td>
                                                <td>
                                                    <input type="checkbox" class="selection" name="selection" value="<?php echo $list['student_id'];?>" />
													<br />
													<br />
													<?php
															if(@$myAccess[0]['student_upload_documents']==1 || $this->session->userdata('role')=='Admin'):
														?>
														<a title="Documents" target="_blank" href="<?php echo site_url().'/students/upload_documents/'.$list['student_id'];?>" class="btn green"><i class="fa fa-image"></i></a>
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

            <?php } ?>

			<?php
                if(@$this->input->post('form_submit') && @$this->input->post('type')=='studentdetail'):
                ?>
                <div class="row">
                    <div class="col-md-12">
                        <button type="button" onclick="printDiv()">PRINT</button>
                        <!-- BEGIN EXAMPLE TABLE PORTLET-->
                        <div class="portlet box grey-cascade" id="printdivxx">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-list"></i>Student Full Details
                                </div>

                            </div>
                            <div class="portlet-body table-responsive">
                                <table class="table table-bordered table-hover" id="sample_2">
                                    <thead>
                                    <tr>
                                        <th class="hidden">
                                            Hidden
                                        </th>
                                        <th>
                                            Name
                                        </th>
                                        <th>
                                            Roll No
                                        </th>
                                        <th>
                                            Phone No
                                        </th>
                                        <th>
                                            CNIC
                                        </th>


                                        <?php

                                        $start    = (new DateTime($startdate))->modify('first day of this month');
                                        $end      = (new DateTime($enddate))->modify('first day of next month');
                                        $interval = DateInterval::createFromDateString('1 month');
                                        $period   = new DatePeriod($start, $interval, $end);


                                        $count=0;
                                        foreach ($period as $dt) {
                                            echo "<th>".$dt->format("Y-M") . "</th>";
                                            $count++;
                                        }


                                        $total_paid_array = array_fill(0,$count, 0);
                                        $total_unpaid_array = array_fill(0,$count, 0);

                                        ?>

                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $i=0;
                                    foreach($students as $student): ?>
                                        <tr class="odd gradeX">
                                            <td class="hidden">
                                                <?php echo $i;?>
                                            </td>

                                            <td> <span class="bold"><?php echo $student['first_name'].' '.$student['last_name'];?></span> </td>>
                                            <td> <span class="bold"><?php echo $student['roll_no'];?></span></span> </td>
                                            <td> <span class="bold"><span class="bold"><?php echo $student['mobile'];?> - <?php echo $student['emergency_no'];?> </td>
                                            <td> <?php echo $student['cnic'];?> </td>

                                            <?php
                                            $j=0;
                                            $paid=0;
                                            $unpaids=0;
                                            foreach ($period as $key=>$dt) {
                                                $payes=$this->db->order_by('dead_line','asc')->get_where('payments',array('student_id'=>$student['student_id'],'dead_line >='=>$dt->format("Y-m-01"),'dead_line <='=>$dt->format("Y-m-30")))->result_array();

                                                ?>

                                                <td style="width: 30%;">

                                                <?php
                                                    foreach ($payes as $pie){

                                                    ?>

                                                        <?php
                                                            if ($pie['actual_amount']>0) {

                                                                echo '<span style="font-weight: bolder">' . $pie['amount'] . '</span><br /> <br />'
                                                                    . $pie['actual_amount'] . '<br /><br />';
                                                            }else{
                                                                echo '<span style="font-weight: bolder; background-color: red; color: white">' . $pie['amount'] . '</span><br /> <br />'
                                                                    . $pie['actual_amount'] . '<br /><br />';

                                                            }


                                                        $paid+=$pie['amount'];
                                                        $unpaids+=$pie['actual_amount'];

                                                        $total_paid_array[$key]+=$pie['amount'];
                                                        $total_unpaid_array[$key]+=$pie['actual_amount'];


                                                        ?>


                                                <?php
                                                    }
                                                ?>
                                                </td>
                                            <?php
                                            }
                                            ?>
                                            <td style="font-weight: bolder">
                                                Must Paid : <?php echo $paid?> <br /> <br /> paid : <?php echo $unpaids?>

                                            </td>

                                        </tr>
                                        <?php

                                        $i++;
                                    endforeach;
                                    ?>
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td>TOTAL MUST PAID</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <?php
                                        foreach ($total_paid_array as $pie){

                                           echo '<td style="font-weight: bolder"> '.$pie.' </td>';


                                        }

                                        ?>
                                        <td><?php $con = 0; foreach ($total_paid_array as $id=>$value) {
                                                $con+=$value;
                                            }
                                            echo $con;
                                            ?></td>


                                    </tr>
                                    <tr>
                                        <td>TOTAL PAID</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <?php
                                        foreach ($total_unpaid_array as $pie){

                                            echo '<td style="font-weight: bold"> '.$pie.' </td>';


                                        }

                                        ?>
                                        <td><?php $con = 0; foreach ($total_unpaid_array as $id=>$value) {
                                                $con+=$value;
                                            }
                                            echo $con;
                                            ?></td>


                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <!-- END EXAMPLE TABLE PORTLET-->
                    </div>
                </div>
            <?php
            endif;
            ?>

			<?php
                if(@$this->input->post('form_submit') && @$this->input->post('type')=='archived'):
                ?>
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
									 Photo
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
									Type
								</th>
                                <th>
									Add By
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=0;
								foreach($students as $student):
							?>
                            <tr class="odd gradeX">
                                <td class="hidden">
									 <?php echo $i;
									 $this->db->select('*');
											$this->db->from('freeze_student');
											$this->db->where("(freeze_student.student_id = '".$student['student_id']."')", NULL, FALSE);
											$freezedata = $this->db->get()->result_array();
									 
									 ?>
								</td>
                                <td>
                                    <a class="btn green" href="<?php echo site_url().'/Students/struckofstudentview/'.$student['student_id'];?>" >
                                        <i class="fa fa-eye"></i>
                                    </a>
									
									<?php if(count($freezedata)>0): ?>
										<a class="btn blue" href="<?php echo site_url().'/Students/freezestudentview/'.$student['student_id'];?>" >
											<i class="fa fa-eye"></i>
										</a>
									<?php endif; ?>
									
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
								
									<?php 
									
																			
											if(count($freezedata)>0)
											{
												echo '<br /><span style="font-weight:bold;font-size:18px;color: blue;">FREEZED</span>';
											}else{
												echo '<br /><span style="font-weight:bold;font-size:18px;color:#F00;">DELETED</span>';
											}
									?>

                                    <?php
                                        if($student['reference_user_id']!=NULL && $student['reference_user_id']!='' && $student['reference_user_id']!=0)
                                        {
                                            $reference = $this->db->get_where('reference_users',array('reference_user_id'=>$student['reference_user_id']))->result_array();
                                            echo 'Reference User : <span class="bold blink_me alert-success">'.@$reference[0]['name'].' - '.@$reference[0]['phone'].'</span>';
                                        }
                                    ?>
									
								
								</td>

                                <td>
                                    <?php echo $student['add_by'];?>
                                </td>
								
								<td>
									<?php
                                        if($this->session->userdata('role')=='Admin'):
                                    ?>
                                    <a href="<?php echo site_url().'/students/edit_student/'.$student['student_id'];?>" class="btn blue"><i class="fa fa-edit"></i></a>
									<?php
                                        endif;
                                    ?>
									<?php
                                    	if(@$student['contract_id']==0):
                                    	if(@$myAccess[0]['student_payments']==1 || $this->session->userdata('role')=='Admin'):
									?>
                                    <a title="Payments" href="<?php echo site_url().'/students/payments/'.$student['student_id'];?>" class="btn purple"><i class="fa fa-money"></i></a>
                                    <a title="Documents" href="<?php echo site_url().'/students/upload_documents/'.$student['student_id'];?>" class="btn green"><i class="fa fa-image"></i></a>
                                    <a title="Struck of" href="<?php echo site_url().'/students/struckofstudentview/'.$student['student_id'];?>" class="btn blue"><i class="fa fa-ban"></i></a>
                                    <?php
                                    	endif;
										endif;
										
										if(count($freezedata)<1){
											
									?>
									
                                    <a data-toggle='modal' title="Activate Student Now" data-id='<?php echo $i ?>' class='open-ReAdmissionDialog btn btn-primary' href="#basicadmission" ><i class="fa fa-refresh"></i></a>
									
										<?php }else { ?>
                                    <a href="<?php echo site_url().'/archive/restore_student/'.$student['student_id'];?>" class="btn btn-primary"><i class="fa fa-refresh"></i></a>
                                    
										<?php }; ?>
                                    
                                    <a title="Purchased Products" href="<?php echo site_url().'/students/purchased_products/'.$student['student_id'];?>" class="btn green"><i class="fa fa-shopping-cart"></i></a>
								
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

			<?php endif; ?>

			<?php
                if(@$this->input->post('form_submit') && @$this->input->post('type')=='shift'):
                    $shift_counts = array();
                ?>
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
                                    Campus name
                                </th>

                                <th>
									 Study campus
								</th>
								<th>
									 Name 
								</th>
								<th>
									 Roll #
								</th>
								<th>
									 Image
								</th>
                                <th>
									 CNIC
								</th>
								<th>
									 CLASS
								</th>
								<th>
									 ADDRESS
								</th>
                                <th>
									 Mobile
								</th>
                               <th>
									 Documents
								</th>
								<th>
									Study Session
								</th>
								<th>
									Shift
								</th>
								
								<th>
									Study Type
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=0;
								foreach($students as $student):
								
								
								$this->db->select('*');
									$this->db->from('punjab_council_roll_number');
									$this->db->where('cnic', $student['cnic']);
									$results = $this->db->get()->result_array();
									
									$show=0;
								
									foreach($results as $result)
									{
										if($result['class']!='2' && $result['result_remarks']!='Pass')
										{
											$show=1;
										}
									}
								if($show == 0){
								    $shift_name = @$this->db->get_where('shifts', array('id' => $student['shift']))->row()->name;
                                    $study_type_name = @$this->db->get_where('study_type', array('id' => $student['study_type']))->row()->name;
                                
                                    $combo_name = trim($shift_name . ' ' . $study_type_name);
                                
                                    if (!isset($shift_studytype_counts[$combo_name])) {
                                        $shift_studytype_counts[$combo_name] = 0;
                                    }
                                    $shift_studytype_counts[$combo_name]++;
							?>
                            <tr class="odd gradeX">
                                <td class="hidden">
									 <?php echo $i;
									 $this->db->select('*');
											$this->db->from('freeze_student');
											$this->db->where("(freeze_student.student_id = '".$student['student_id']."')", NULL, FALSE);
											$freezedata = $this->db->get()->result_array();
									 
									 ?>
								</td>
                               
								<td>
									 <?php echo $student['campus_name'];?>
								</td>
								<td>
									 <?php $this->db->select('*');
											$this->db->from('campuses');
											$this->db->where("(campus_id = '".$student['study_campus']."')", NULL, FALSE);
											$us = $this->db->get()->row(); 
											echo @$us->campus_name;?>
								</td>
								<td>
									<?php echo $student['first_name'].' '.$student['last_name'];?>
								</td>
								<td>
									 <?php echo $student['roll_no'];?>
								</td>
								<td>
									<?php $student_image = $this->db->get_where('student_documents',array('student_id'=>$student['student_id'],'type'=>'Photo'))->result_array();?>
									<img height="100" src="<?php echo base_url();?>uploads/<?php echo @$student_image[0]['image'];?>" alt="" />
								</td>
                                <td>
									<?php echo $student['cnic']?>
								</td>
								<td>
									<?php echo $student['class_name']?>
								</td>
								<td>
									<?php echo $student['address']?>
								</td>
                                <td>
									<?php echo $student['mobile'].'<br />'.$student['emergency_no'];?>
								</td>
								<td>
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
								</td>
								<td>
									<?php echo $student['study_session'];?>
								</td>
                                <td>
                                    <?php echo $shift_name; ?>
								</td>
								<td>
									<?php echo $study_type_name; ?>
								</td>
								
								<td>
									<a href="<?php echo site_url().'/students/edit_student/'.$student['student_id'];?>" class="btn blue"><i class="fa fa-edit"></i></a>
                                    <a title="Purchased Products" href="<?php echo site_url().'/students/purchased_products/'.$student['student_id'];?>" class="btn green"><i class="fa fa-shopping-cart"></i></a>
								</td>
							</tr>
                            <?php
								}
								$i++;
                            	endforeach;
							?>
							</tbody>
							<tfoot>
                                <tr>
                                    <th class="hidden"></th>
                                    <th colspan="14" style="text-align:left;">
                                        <?php
                                            if (!empty($shift_studytype_counts)) {
                                                echo '<strong>Shift + Study Type Counts:</strong> ';
                                                $output = array();
                                                foreach ($shift_studytype_counts as $combo => $count) {
                                                    $output[] = $combo . ' (' . $count . ')';
                                                }
                                                echo implode(' | ', $output);
                                            } else {
                                                echo '<strong>Shift + Study Type Counts:</strong> 0';
                                            }
                                        ?>
                                    </th>
                                </tr>
                            </tfoot>
							</table>
						</div>
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>

			<?php endif; ?>

            <?php
                if(@$this->input->post('form_submit') && @$this->input->post('search_type')=='councilwise_roll_no'):
                ?>
                <div class="row">
                    <div class="col-md-12">
                        <!-- BEGIN EXAMPLE TABLE PORTLET-->
                        <div class="portlet box grey-cascade">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-list"></i> According to Roll no of Council(Fee/Papers/Information) Council Exam No <?php echo $this->input->post("council_exam_no");?> for  <?php echo $this->input->post("class"); ?>st Year
                                </div>
                            </div>
                            <div class="portlet-body">
                                <table class="table table-bordered table-hover" id="sample_2">
                                    <thead>
                                    <tr>
                                        <th class="hidden">
                                            Hidden
                                        </th>
                                        <th>
                                            Student Information &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        </th>
                                        <th>
                                            Payable current Time
                                        </th>
                                        <th>
                                            Unpaid fee Details
                                        </th>
                                        <th>
                                            Result Remarks
                                        </th>
                                        <th>
                                            Contractor
                                        </th>
                                        <th>
                                            FAIL
                                        </th>
                                        <th>
                                            Fail(Absent)
                                        </th>
                                        <th>
                                            Fail(Must Appear in all)
                                        </th>
                                        <th>
                                            P1
                                        </th>
                                        <th>
                                            P2
                                        </th>
                                        <th>
                                            P3
                                        </th>
                                        <th>
                                            p4
                                        </th>
                                        <th>
                                            p5
                                        </th>
                                        <th>
                                            p6
                                        </th>
                                        <th>
                                            Last Chance
                                        </th>
                                        <th>
                                            Next two Chance
                                        </th>
                                        <th>
                                            Fail In Theory
                                        </th>
                                        <th>
                                            Fail in Practical
                                        </th>
                                        <th>
                                            Remarks
                                        </th>
                                        <th>
                                            Action
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if($this->session->userdata('role')=='Admin'):
                                        ?>
                                        <?php
                                        $i=0;
                                        foreach(@$roll_numbers as $roll_number):
                                            $student=$roll_number;

                                            $totalStudentFee = $this->council->getTotalFeeDetail($student['student_id']);
                                            $totalfee= $totalStudentFee[0]['amount'];

                                            $studentFeeDecidedCurrentTime = $this->council->getFeeDecidedCurrentTime($student['student_id']);
                                            $totalfeedecided= $studentFeeDecidedCurrentTime[0]['amount'];

                                            $totalStudentPaidFee = $this->council->getTotalPaidFeeDetail($student['student_id']);

                                            $totalStudentPaidFeecurrent= $studentFeeDecidedCurrentTime[0]['amount']-$totalStudentPaidFee;

                                            $studentUnpaidFeeCurrentTime = $this->council->getUnpaidFeeDetailCurrentTime($student['student_id']);
                                            $totalStudentunPaidFeecurrent= count($studentUnpaidFeeCurrentTime);

                                            $studentPaidFee = $this->council->getPaidFeeDetail($student['student_id']);
                                            $len = count($studentPaidFee);
                                            $feeHTML = '';
                                            foreach($studentPaidFee as $key=>$feeStatus)
                                            {

                                                if($key == $len-1){

                                                    $feeHTML.= '<span class="bold"> Rs '.$feeStatus['actual_amount'].' paid on '.$feeStatus['actual_paid_date'];
                                                    $feeHTML.= '</span><br />';



                                                }else
                                                {
                                                    $feeHTML.= 'Rs '.$feeStatus['actual_amount'].' paid on '.$feeStatus['actual_paid_date'];
                                                    $feeHTML.= '<br />';
                                                }
                                            }


                                            $studentUnpaidFee = $this->council->getUnpaidFeeDetail($student['student_id']);

                                            $feeHTMLUN = '';
                                            foreach($studentUnpaidFee as $payment)
                                            {

                                                if($payment['dead_line']<date('Y-m-d'))
                                                {
                                                    $feeHTMLUN.= '<span class="bold">'.$payment['amount'].' Not Paid on '.$payment['dead_line'].'</span><br />';
                                                }
                                                else
                                                {
                                                    $feeHTMLUN.= $payment['amount'].' Not Paid on '.$payment['dead_line'].'<br />';
                                                }


                                            }
                                            $pass=0;
                                            $fail=0;
                                            $failabsent=0;
                                            $failall=0;
                                            $fail1=0;
                                            $fail2=0;
                                            $fail3=0;
                                            $fail4=0;
                                            $fail5=0;
                                            $fail6=0;
                                            $lastchance=0;
                                            $next2chance=0;
                                            $onfailinpractical=0;
                                            $onfailintheory=0;

                                            $result = getStudentMyRemarks($student['cnic']);

                                            if ($result) {

                                                if (strpos($result['result_remarks'], 'Pass') !== false) {
                                                    $pass++;
                                                } if ($result['result_remarks'] == 'Fail') {
                                                    $fail++;
                                                } if (strpos($result['result_remarks'], 'Fail') !== false && strpos($result['result_remarks'], 'Absent') !== false) {
                                                    $failabsent++;
                                                } if (strpos($result['result_remarks'], 'Fail') !== false && strpos($result['result_remarks'], 'appear in all') !== false) {
                                                    $failall++;
                                                } if (strpos($result['result_remarks'], 'Fail') !== false && strpos($result['result_remarks'], '1') !== false) {
                                                    $fail1++;
                                                } if (strpos($result['result_remarks'], 'Fail') !== false && strpos($result['result_remarks'], '2') !== false) {
                                                    $fail2++;
                                                } if (strpos($result['result_remarks'], 'Fail') !== false && strpos($result['result_remarks'], '3') !== false) {
                                                    $fail3++;
                                                } if (strpos($result['result_remarks'], 'Fail') !== false && strpos($result['result_remarks'], '4') !== false) {
                                                    $fail4++;
                                                } if (strpos($result['result_remarks'], 'Fail') !== false && strpos($result['result_remarks'], '5') !== false) {
                                                    $fail5++;
                                                } if (strpos($result['result_remarks'], 'Fail') !== false && strpos($result['result_remarks'], '6') !== false) {
                                                    $fail6++;
                                                } if (strpos($result['result_remarks'], 'Fail') !== false && strpos($result['result_remarks'], 'Last Chance') !== false) {
                                                    $lastchance++;
                                                } if (strpos($result['result_remarks'], 'Fail') !== false && strpos($result['result_remarks'], 'Next Two') !== false) {
                                                    $next2chance++;
                                                }


                                                if (strpos($result['result_remarks'], 'Fail') !== false &&
                                                    (strpos($result['result_remarks'], '3') !== false ||
                                                        strpos($result['result_remarks'], '4') !== false ||
                                                        strpos($result['result_remarks'], '5') !== false ||
                                                        strpos($result['result_remarks'], '6') !== false)) {
                                                    $onfailinpractical++;
                                                }

                                                if (strpos($result['result_remarks'], 'Fail') !== false &&
                                                    (strpos($result['result_remarks'], '1') !== false ||
                                                        strpos($result['result_remarks'], '2') !== false )) {

                                                    $onfailintheory++;
                                                }
                                            }

                                            ?>
                                            <tr class="<?php if(@$roll_number['council_mistake']==1){echo 'alert alert-danger';}?>">
                                                <td class="hidden">
                                                    <?php echo $i;?>
                                                </td>
                                                <td>
                                                    Campus : <?php echo $roll_number['campus_name'];?>
                                                    <br />
                                                    Course : <?php echo $roll_number['course_name'];?>
                                                    <br />
                                                    Session : <span class="bold"><?php echo $roll_number['session'];?></span>
                                                    <br />
                                                    Class : <?php echo $roll_number['class_name'];?>
                                                    <br />
                                                    Registration Date : <span class="bold"><?php echo $roll_number['registration_date'];?></span>
                                                    <br />
                                                    Student Name : <span class="bold"><?php echo $roll_number['first_name'].' '.$roll_number['last_name'];?></span>
                                                    <br />
                                                    CNIC : <?php echo $roll_number['cnic'];?>
                                                    <br />
                                                    Father Name : <?php echo $roll_number['father_name'];?>
                                                    <br />
                                                    Roll # : <span class="bold"><?php echo $roll_number['roll_no'];?></span>
                                                    <br />
                                                    Mobile : <span class="bold"><?php echo $roll_number['mobile'];?> - <?php echo $roll_number['emergency_no'];?></span>
                                                </td>
                                                <td>
                                                    <?php echo 'RS '.$totalStudentPaidFeecurrent;?>
                                                </td>
                                                <td>
                                                    <?php echo $feeHTMLUN;?>
                                                </td>
                                                <td>
                                                    <?php getStudentResultRemarksLast($student['cnic']);?>
                                                </td>

                                                <td>
                                                    <?php
                                                    if($roll_number['contractor_name']=='')
                                                    {
                                                        echo 'N/A';
                                                    }
                                                    else
                                                    {
                                                        echo $roll_number['contractor_name'];
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php echo $fail;?>

                                                </td>
                                                <td>
                                                    <?php echo $failabsent;?>

                                                </td>
                                                <td>
                                                    <?php echo $failall;?>

                                                </td>
                                                <td>
                                                    <?php echo $fail1;?>

                                                </td>
                                                <td>
                                                    <?php echo $fail2;?>

                                                </td>
                                                <td>
                                                    <?php echo $fail3;?>

                                                </td>
                                                <td>
                                                    <?php echo $fail4;?>

                                                </td>
                                                <td>
                                                    <?php echo $fail5;?>

                                                </td>
                                                <td>
                                                    <?php echo $fail6;?>

                                                </td>
                                                <td>
                                                    <?php echo $lastchance;?>

                                                </td>
                                                <td>
                                                    <?php echo $next2chance;?>

                                                </td>
                                                <td>
                                                    <?php echo $onfailintheory;?>

                                                </td>
                                                <td>
                                                    <?php echo $onfailinpractical;?>

                                                </td>
                                                <td>
                                                    <?php echo $roll_number['remarks']?>
                                                </td>
                                                <td>
                                                    <?php
                                                    if($this->session->userdata('role')=='Admin'):
                                                        ?>
                                                        <a onclick="return confirm('Are you sure you want to delete this Roll Number?')" class="btn red" href="<?php echo site_url();?>/punjab_council_roll_number/delete_roll_no/<?php echo $roll_number['id']?>"><i class="fa fa-trash"></i></a>
                                                    <?php
                                                    endif;
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php
                                            $i++;
                                        endforeach;
                                        ?>
                                    <?php
                                    endif;
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- END EXAMPLE TABLE PORTLET-->
                    </div>
                </div>

            <?php endif; ?>

            <?php
                if(@$this->input->post('form_submit') && @$this->input->post('type')=='using_app'):
                ?>
                <div class="row">
                    <div class="col-md-12">
                        <!-- BEGIN EXAMPLE TABLE PORTLET-->
                        <div class="portlet box grey-cascade">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-list"></i>All Students Using Mobile App
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
                                            Campus name
                                        </th>

                                        <th>
                                            Study campus
                                        </th>
                                        <th>
                                            Name
                                        </th>
                                        <th>
                                            Roll #
                                        </th>
                                        <th>
                                            Image
                                        </th>
                                        <th>
                                            CNIC
                                        </th>
                                        <th>
                                            CLASS
                                        </th>
                                        <th>
                                            ADDRESS
                                        </th>
                                        <th>
                                            Mobile
                                        </th>
                                        <th>
                                            Shift
                                        </th>
                                        <th>
                                            Study Type
                                        </th>
                                        <th>
                                            Portal Last Login
                                        </th>
                                        <th>
                                            Mobile App Last Login
                                        </th>

                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $i=0;
                                    foreach($students as $student):

                                            ?>
                                            <tr class="odd gradeX">
                                                <td class="hidden"> </td>

                                                <td>
                                                    <?php echo $student['campus_name'];?>
                                                </td>
                                                <td>
                                                    <?php $this->db->select('*');
                                                    $this->db->from('campuses');
                                                    $this->db->where("(campus_id = '".$student['study_campus']."')", NULL, FALSE);
                                                    $us = $this->db->get()->row();
                                                    echo @$us->campus_name;?>
                                                </td>
                                                <td>
                                                    <?php echo $student['first_name'].' '.$student['last_name'];?>
                                                </td>
                                                <td>
                                                    <?php echo $student['roll_no'];?>
                                                </td>
                                                <td>
                                                    <?php $student_image = $this->db->get_where('student_documents',array('student_id'=>$student['student_id'],'type'=>'Photo'))->result_array();?>
                                                    <img height="100" src="<?php echo base_url();?>uploads/<?php echo @$student_image[0]['image'];?>" alt="" />
                                                </td>
                                                <td>
                                                    <?php echo $student['cnic']?>
                                                </td>
                                                <td>
                                                    <?php echo $student['class_name']?>
                                                </td>
                                                <td>
                                                    <?php echo $student['address']?>
                                                </td>
                                                <td>
                                                    <?php echo $student['mobile'].'<br />'.$student['emergency_no'];?>
                                                </td>
                                                <td>
                                                    <?php echo $student['shift'];?>
                                                </td>
                                                <td>
                                                    <?php echo $student['study_type'];?>
                                                </td>
                                                <td>
                                                    <?php $this->db->select('*');
                                                    $this->db->from('students_login_tracking');
                                                    $this->db->where("(student_id = '".$student['student_id']."' and type = 'portal')", NULL, FALSE)->order_by("students_login_tracking_id","DESC");
                                                    $us = $this->db->get()->row();
                                                    echo @$us->login_time?>
                                                </td>
                                                <td>
                                                    <?php $this->db->select('*');
                                                    $this->db->from('students_login_tracking');
                                                    $this->db->where("(student_id = '".$student['student_id']."' and type = 'app')", NULL, FALSE)->order_by("students_login_tracking_id","DESC");
                                                    $us = $this->db->get()->row();
                                                    echo @$us->login_time?>
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

            <?php endif; ?>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->

    <div class="modal fade" id="basicadmission" tabindex="-1"   data-width="800" >
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title">Please enter Re-Admission details</h4>
        </div>
        <div class="modal-body">
            <div class="form-group" id="uphalf">
                <label class="col-md-6 control-label">Re-Admission with</label>
                <div class="col-md-6 radio-list" name="radiolist" id="radiolist">
                    <label class="radio-inline">
                        <input type="radio" class="plan_type" name="plan_type" id="mmfull"  value="1" >Current Plan</label>
                    <label class="radio-inline">
                        <input type="radio" class="plan_type" name="plan_type" id="mmhalf"  value="2">New Plan</label>
                </div>
            </div>
            <br />
            <div id="old_div" style="display: none;">
                <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/students/addrevivedetails">
                <div class="form-body">

                    <div class="form-group">
                        <label class="col-md-4 control-label">Payment Campus <span class="required">*</span></label>
                        <div class="col-md-8">
                            <select class="form-control" name="campus_id" required>
                                <?php
                                    foreach($campuses as $campus):
                                ?>
                                <option value="<?php echo $campus['campus_id'];?>">
                                    <?php echo $campus['campus_name'];?>
                                </option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                            <!--<span class="help-inline"></span>-->
                        </div>
                    </div>


                    <div class="form-group">
                        <label class="col-md-4 control-label">Reason in Details</label>
                        <div class="col-md-8">
                            <textarea class="form-control remarks" rows="3" name="reason"></textarea>
                        </div>
                    </div>


                        <div class="form-group">
                            <label class="col-md-4 control-label">Re Admission Fee</label>
                            <div class="col-md-8">
                                <input type="number"  name="fee" class="form-control mobile" value="<?php echo $students[0]['freeze_fee'] ?>" required>
                            </div>
                        </div>



                    <div class="form-group">
                        <label class="col-md-4 control-label">Application Image</label>
                        <div class="col-md-8">
                            <input type="file" name="image" class="form-control" required />
                        </div>
                    </div>


                </div>
                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-offset-3 col-md-9">
                            <input type="hidden" name="add_by" value="<?php echo $this->session->userdata('name');?>" />
                            <input type="hidden" name="student_id" id="student_id" value="" />
                            <button type="submit" class="btn red">Add Detail</button>
                        </div>
                    </div>
                </div>
                </form>
            </div>
            <br />
            <div id="new_div" style="display: none;">
                <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/students/addrevivedetails_new">
                    <div class="form-body">
                        <div class="form-group">
                            <label class="col-md-3 control-label">Class <span class="required">*</span></label>
                            <div class="col-md-5">
                                <select class="form-control" name="class_id" id="selected_session">
                                    <option value="">SELECT SESSION</option>
                                    <?php
                                    foreach($sessions as $session): ?>
                                        <option value="<?php echo $session['class_id'];?>"><?php echo $session['name'];?></option>
                                    <?php endforeach; ?>
                                </select>
                                <!--<span class="help-inline"></span>-->
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">Reason in Details</label>
                            <div class="col-md-8">
                                <textarea class="form-control remarks" rows="3" name="reason"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-9">
                                <input type="hidden" name="add_by" value="<?php echo $this->session->userdata('name');?>" />
                                <input type="hidden" name="student_id" id="student_id_up" value="" />
                                <button type="submit" class="btn red">Submit</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn default close_button" data-dismiss="modal">Close</button>
        </div>
    </div>

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
<script>
    document.addEventListener( "DOMContentLoaded", function(){
        $("#print-btn").on("click", function(){
            var divToPrint=document.getElementById('print-div');
            var newWin=window.open('','Print-Window');
            newWin.document.open();
            newWin.document.write('<link rel="stylesheet" href="<?php echo base_url();?>assets/global/plugins/bootstrap/css/bootstrap.min.css" type="text/css"><style type="text/css">@media print {}</style><body onload="window.print()">'+divToPrint.innerHTML+'</body>');
            newWin.document.close();
        });
        $("#checkAll").click(function(){
            $('.selection').trigger('click');
        });
        
        jQuery('.type').click(function(){
            var type = jQuery(this).val();
            if(type=='attendance')
            {
                jQuery('.student_checker_form').attr("target","_blank");
            }
            else
            {
                jQuery('.student_checker_form').removeAttr("target");
            }
        });

        var table = $('#sample_ali');
        var printCounter = 0;
        table.dataTable({

            // Internationalisation. For more info refer to http://datatables.net/manual/i18n
            "language": {
                "aria": {
                    "sortAscending": ": activate to sort column ascending",
                    "sortDescending": ": activate to sort column descending"
                },
                "emptyTable": "No data available in table",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "infoEmpty": "No entries found",
                "infoFiltered": "(filtered1 from _MAX_ total entries)",
                "lengthMenu": "Show _MENU_ entries",
                "search": "Search:",
                "zeroRecords": "No matching records found"
            },

            "buttons": [
                'copy',
                {
                    extend: 'excel',
                    messageTop: 'The information in this table is copyright to Sirius Cybernetics Corp.'
                },
                {
                    extend: 'pdf',
                    messageBottom: null
                },
                {
                    extend: 'print',
                    messageTop: function () {
                        printCounter++;

                        if ( printCounter === 1 ) {
                            return 'This is the first time you have printed this document.';
                        }
                        else {
                            return 'You have printed this document '+printCounter+' times';
                        }
                    },
                    messageBottom: null
                }
            ],
            "order": [
                [0, 'asc']
            ],
            "lengthMenu": [
                [-1],
                ["All"] // change per page values here
            ],

            // set the initial value
            "pageLength": -1,
            "dom": "<'row' <'col-md-12'T>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r><'table-scrollable't><'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>", // horizobtal scrollable datatable
            // Uncomment below line("dom" parameter) to fix the dropdown overflow issue in the datatable cells. The default datatable layout
            // setup uses scrollable div(table-scrollable) with overflow:auto to enable vertical scroll(see: assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js).
            // So when dropdowns used the scrollable div should be removed.
            //"dom": "<'row' <'col-md-12'T>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r>t<'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>",

            "tableTools": {
                "sSwfPath": "../../assets/global/plugins/datatables/extensions/TableTools/swf/copy_csv_xls_pdf.swf",
                "aButtons": [{
                    "sExtends": "pdf",
                    "sButtonText": "PDF"
                }, {
                    "sExtends": "csv",
                    "sButtonText": "CSV"
                }, {
                    "sExtends": "xls",
                    "sButtonText": "Excel"
                }, {
                    "sExtends": "print",
                    "sButtonText": "Print",
                    "sInfo": 'Please press "CTRL+P" to print or "ESC" to quit',
                    "sMessage": "Generated by DataTables"
                }, {
                    "sExtends": "copy",
                    "sButtonText": "Copy"
                }]
            }
        });

        var tableWrapper = $('#sample_ali_wrapper'); // datatable creates the table wrapper by adding with id {your_table_jd}_wrapper
        tableWrapper.find('.dataTables_length select').select2(); // initialize select2 dropdown

    }, false );
</script>