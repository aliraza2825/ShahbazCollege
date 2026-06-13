
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
			<div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
                            	<?php
									$comission = $this->db->get_where('recovery_management',array('recovery_management_id'=>$this->uri->segment(3)))->result_array();
									$campus_ids = explode(',',$comission[0]['campus_ids']);
									$this->db->where_in('campus_id',$campus_ids);
									$campuses = $this->db->get('campuses')->result_array();
								?>
								<i class="fa fa-user"></i> <?php echo $user[0]['first_name'].' '.$user[0]['last_name'];?> 
                                ( 
								<?php foreach($campuses as $campus)
                                {
                                    echo $campus['campus_name'];
                                    echo ' | ';
                                }
                                ?>)
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/recovery_management/check_recovery/<?php echo $this->uri->segment(3);?>/<?php echo $this->uri->segment(4);?>">
								<div class="form-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                           <div class="row">
                                               <div class="col-md-12">
                                                   Assign Courses : 
                                                   <?php
                    									$course_ids = explode(',',$comission[0]['course_id']);
                    									$this->db->where_in('course_id',$course_ids);
                    									$courses = $this->db->get('courses')->result_array();
                    								?> 
                                                    ( 
                    								<?php foreach($courses as $course)
                                                    {
                                                        echo '<strong>'.$course['course_name'].'</strong>';
                                                        echo ' | ';
                                                    }
                                                    ?>)
                                               </div>
                                               <div class="col-md-12">
                                                   <div class="row">
                                                       <h2 class="col-md-12" style=" margin-top: 0px; margin-bottom: 0px;">Insentive Details</h2>
                                                   </div>
                                                   <div class="row" style="border: 1px solid black; padding: 5px; margin: 7px">
                                                    <?php
            											$delete_entries=0;
            											$shifted_entries=0;
            											foreach($shifted_payments_students as $shifted_payment)
            											{
            												if($shifted_payment['del']==1)
            												{
            													$delete_entries++;
            												}
            												else
            												{
            													$shifted_entries++;
            												}
            											}
            											foreach($shifted_payments_contracts as $shifted_payment)
            											{
            												if($shifted_payment['del']==1)
            												{
            													$delete_entries++;
            												}
            												else
            												{
            													$shifted_entries++;
            												}
            											}
            										?>
                                                   <?php
                                                   $rules = $this->db->get_where('recovery_management_rules',array('recovery_management_id'=>$this->uri->segment(3)))->result_array();
                                                   $comission = $this->db->get_where('recovery_management',array('recovery_management_id'=>$this->uri->segment(3)))->result_array();
												   $total_entries = (count($unpaid_payments_students)+count($unpaid_payments_contracts)+count($paid_payments_students)+count($paid_payments_contracts)+count($shifted_payments_students)+count($shifted_payments_contracts)+count($unpaid_payments_students_during_last_month));
                                                    
                                                    $total_entries = $total_entries-$delete_entries;
                                                    
													$total_entries_in_percent = $total_entries/100;
													$paid_entries = count($paid_payments_students)+count($paid_payments_contracts);
													$paid_entries -= count($unverified_paid_count_students);
													if($total_entries>0)
                                                    {
                                                        $submitted_fee_percentage = round(($paid_entries/$total_entries)*100,2);

                                                    }
                                                    else
                                                    {
                                                        $submitted_fee_percentage=0;

                                                    }

													echo '<div class="col-md-12">
												
													<div class="col-md-2">Total Entries</div>
													<div class="col-md-2">From %</div>
													<div class="col-md-2">To %</div>
													<div class="col-md-2">Amount</div>
													<div class="col-md-2">Minimum Amount</div>
													<div class="col-md-2">Maximum Amount</div>
													</div>';

                                                   foreach($rules as $rule)
                                                   {
													    echo '<div class="col-md-12">
														
														<div class="col-md-2">'. $total_entries.'</div>
														<div class="col-md-2">'. $rule['start'].'</div>
													    <div class="col-md-2">'. $rule['end'].'</div>
													    <div class="col-md-2">'. $rule['comission'].'</div>
													    <div class="col-md-2">'. $total_entries_in_percent*$rule['comission']*$rule['start'].'</div>
													    <div class="col-md-2">'. $total_entries_in_percent*$rule['comission']*$rule['end'].'</div>
													    </div>';
                                                   }
                                                   ?>

                                                   </div>
                                               </div>

                                           </div>
										   <div class="col-md-12">

                                           <div class="row">
                                               <div class="col-md-6">
                                                   <label class="control-label col-md-3">From Date</label>
                                                   <div class="input-group input-medium date date-picker col-md-9" data-date="<?php echo $from_date;?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                       <input type="text" name="from_date" class="form-control" value="<?php echo $from_date;?>" readonly>
                                                       <span class="input-group-btn">
                                                            <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                            </span>
                                                   </div>
                                               </div>

                                               <div class="col-md-6">
                                                   <label class="control-label col-md-3">To Date</label>
                                                   <div class="input-group input-medium date date-picker col-md-9" data-date="<?php echo $to_date;?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                       <input type="text" name="to_date" class="form-control" value="<?php echo $to_date;?>" readonly>
                                                       <span class="input-group-btn">
                                                            <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                            </span>
                                                   </div>
                                               </div>

                                           </div>

                                        </div>

                                        </div>


                                    </div>
                                    <div class="form-actions">
                                        <div class="row">
                                            <div class="col-md-offset-3 col-md-9">
                                                <button type="submit" class="btn green">Check</button>
                                                <button onclick="location.href = '<?php echo site_url();?>/recovery_management/all_assign_task'" type="button" class="btn default">Cancel</button>
                                            </div>
                                        </div>
                                    </div>
                            </form>
                                <hr/>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="alert alert-info">
                                                Note: Fee Recovered Percentage and in Total Entries doesn't included deleted fee.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        
                                        <div class="col-md-2" style="height: 130px;">
                                            <div class="dashboard-stat yellow-lemon">
                                                <div class="visual">
                                                    <i class="fa fa-list"></i>
                                                </div>
                                                <div class="details">
                                                    <div class="number">
                                                        <?php echo $total_entries;?>
                                                    </div>
                                                    <div class="desc" style="font-size: small">
                                                         Total Entries
                                                    </div>
                                                </div>
												<?php $recovery_management_id = $this->uri->segment(3);
															$this->db->order_by('start','ASC');
															//$this->db->limit(1);
															$comission_rule = $this->db->get_where('recovery_management_rules',array('recovery_management_id'=>$recovery_management_id,'start<='=>$submitted_fee_percentage,'end>'=>$submitted_fee_percentage))->result_array();
		
															if(count($comission_rule)>0)
															{
																$installment_comission =  $comission_rule[0]['comission'];
																
															}
															else
															{
																$installment_comission=0;
																
															} ?>
                                                <a class="more" href="<?php echo site_url();?>/recovery_management/all_entries/<?php echo $recoveryid ?>/1/<?php echo $installment_comission.'/'.$from_date.'/'.$to_date; ?>">
                                                    View more <i class="m-icon-swapright m-icon-white"></i>
                                                </a>
                                            </div>
                                            <br />
                                        </div>

                                        <div class="col-md-2" style="height: 130px;">
                                            <div class="dashboard-stat blue-madison">
                                                <div class="visual">
                                                    <i class="fa fa-list"></i>
                                                </div>
                                                <div class="details">
                                                    <div class="number">
                                                        <?php $total_paidunpaid_students = count($fee_dues_students_count)+count($fee_dues_contractors_count)+count($paid_count_students)+count($paid_count_contracts);
														echo $total_paidunpaid_students;?>
                                                    </div>
                                                    <div class="desc" style="font-size: small">
                                                        Total Students
                                                    </div>
                                                </div>
                                                <a class="more" href="#">
                                                    View more <i class="m-icon-swapright m-icon-white"></i>
                                                </a>
                                            </div>
                                            <br />
                                        </div>
                                        
                                        <div class="col-md-2" style="height: 130px;">
                                            <div class="dashboard-stat blue-madison">
                                                <div class="visual">
                                                    <i class="fa fa-forward"></i>
                                                </div>
                                                <div class="details">
                                                    <div class="number">
                                                        <?php
															echo $shifted_entries;
														?>
                                                    </div>
                                                    <div class="desc" style="font-size: small">
                                                         Total Fee Shifted
                                                    </div>
                                                </div>
                                                <a class="more" href="#">
                                                    View more <i class="m-icon-swapright m-icon-white"></i>
                                                </a>
                                            </div>
                                            <br />
                                        </div>
                                        
                                        <div class="col-md-2" style="height: 130px;">
                                            <div class="dashboard-stat blue-madison">
                                                <div class="visual">
                                                    <i class="fa fa-trash"></i>
                                                </div>
                                                <div class="details">
                                                    <div class="number">
                                                        <?php echo $delete_entries;?>
                                                    </div>
                                                    <div class="desc" style="font-size: small">
                                                         Total Fee Deleted
                                                    </div>
                                                </div>
                                                <a class="more" href="#">
                                                    View more <i class="m-icon-swapright m-icon-white"></i>
                                                </a>
                                            </div>
                                            <br />
                                        </div>
                                        
                                        <div class="col-md-2" style="height: 130px;">
                                            <div class="dashboard-stat blue-madison">
                                                <div class="visual">
                                                    <i class="fa fa-bank"></i>
                                                </div>
                                                <div class="details">
                                                    <div class="number">
                                                        <?php
															$paid_entries = count($paid_payments_students)+count($paid_payments_contracts)-count($unverified_paid_count_students);
															echo $paid_entries;
														?>
                                                    </div>
                                                    <div class="desc" style="font-size: small">
                                                        Total Paid Entries
                                                    </div>
                                                </div>
                                                <a class="more" href="<?php echo site_url();?>/recovery_management/all_paid_entries/<?php echo $recoveryid ?>/1/<?php echo $installment_comission.'/'.$from_date.'/'.$to_date; ?>">
                                                    View more <i class="m-icon-swapright m-icon-white"></i>
                                                </a>
                                            </div>
                                            <br />
                                        </div>

                                        <div class="col-md-2" style="height: 130px;">
                                            <div class="dashboard-stat blue-madison">
                                                <div class="visual">
                                                    <i class="fa fa-bank"></i>
                                                </div>
                                                <div class="details">
                                                    <div class="number">
                                                        <?php
															$paid_unverified_entries = count($unverified_paid_count_students);
															echo $paid_unverified_entries;
														?>
                                                    </div>
                                                    <div class="desc" style="font-size: small">
                                                        Total Paid Unverified Entries
                                                    </div>
                                                </div>
                                                <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/recovery_management/fine_data"  target="_blank">

                                                    <input type="hidden" name="fine_data[][]" value='<?php echo json_encode($unverified_paid_count_students) ?>'>


                                                    <button class="more" type="submit" style="width: 100%; text-align: left">
                                                        view more <i class="m-icon-swapright m-icon-white"></i>
                                                    </button>

                                                </form>
<!--                                                <a class="more" href="#">-->
<!--                                                    View more <i class="m-icon-swapright m-icon-white"></i>-->
<!--                                                </a>-->
                                            </div>
                                            <br />
                                        </div>

                                        <div class="col-md-2" style="height: 130px;">
                                            <div class="dashboard-stat blue-madison">
                                                <div class="visual">
                                                    <i class="fa fa-bank"></i>
                                                </div>
                                                <div class="details">
                                                    <div class="number">
                                                        <?php
                                                        $paid_students = count($paid_count_students)+count($paid_count_contracts);
                                                        echo $paid_students;
                                                        ?>
                                                    </div>
                                                    <div class="desc" style="font-size: small">
                                                        Total Paid Students
                                                    </div>
                                                </div>
                                                <a class="more" href="#">
                                                    View more <i class="m-icon-swapright m-icon-white"></i>
                                                </a>
                                            </div>
                                            <br />
                                        </div>

                                        <div class="col-md-2" style="height: 130px;">
                                            <div class="dashboard-stat blue-madison">
                                                <div class="visual">
                                                    <i class="fa fa-cogs"></i>
                                                </div>
                                                <div class="details">
                                                    <div class="number">
                                                        <?php
															$unpaid_entries = count($unpaid_payments_students)+count($unpaid_payments_contracts)+count($unpaid_payments_students_during_last_month);
															echo $unpaid_entries;
														?>
                                                    </div>
                                                    <div class="desc" style="font-size: small">
                                                        Total UnPaid Entries
                                                    </div>
                                                </div>
                                                <a class="more" href="#">
                                                    View more <i class="m-icon-swapright m-icon-white"></i>
                                                </a>
                                            </div>
                                            <br />
                                        </div>

                                        <div class="col-md-2" style="height: 130px;">
                                                <div class="dashboard-stat red-intense">
                                                    <div class="visual">
                                                        <i class="fa fa-list"></i>
                                                    </div>
                                                    <div class="details">
                                                        <div class="number">
                                                            <?php echo $total_paidunpaid_students - (count($paid_count_students)+count($paid_count_contracts))?>
                                                        </div>
                                                        <div class="desc" style="font-size: small">
                                                            Total unpaid Students
                                                        </div>
                                                    </div>
                                                    <a class="more" href="<?php echo site_url();?>/recovery_management/fee_dues_comments/<?php echo $recoveryid ?>/0">
                                                    View more <i class="m-icon-swapright m-icon-white"></i>
                                                    </a>
                                                </div>
                                                <br />
                                            </div>
                                                    <?php
															$unpaid_splited_entries = 0;
															foreach($unpaid_payments_students  as $unpaid_payments_student)
															{
																if($unpaid_payments_student['split']>0)
																{
																	$unpaid_splited_entries++;
																}
															}
															foreach($unpaid_payments_contracts  as $unpaid_payments_contract)
															{
																if($unpaid_payments_contract['split']>0)
																{
																	$unpaid_splited_entries++;
																}
															}
															
														?>
                                                   
                                                    <?php
															$paid_splited_entries = 0;
															foreach($paid_payments_students  as $paid_payments_student)
															{
																if($paid_payments_student['split']>0)
																{
																	$paid_splited_entries++;
																}
															}
															foreach($paid_payments_contracts  as $paid_payments_contract)
															{
																if($paid_payments_contract['split']>0)
																{
																	$paid_splited_entries++;
																}
															}
															
														?>
                                        <div class="col-md-2" style="height: 130px;">
                                            <div class="dashboard-stat blue-madison">
                                                <div class="visual">
                                                    <i class="fa fa-chain"></i>
                                                </div>
                                                <div class="details">
                                                    <div class="number">
                                                        <?php
															if($total_entries>0)
															{
																$submitted_fee_percentage = round(($paid_entries/$total_entries)*100,2);
																echo $submitted_fee_percentage.'%';
															}
															else
															{
																$submitted_fee_percentage=0;
																echo '0%';
															}
														?>
                                                    </div>
                                                    <div class="desc" style="font-size: small">
                                                        Fee Recovered Percentage
                                                    </div>
                                                </div>
                                                <a class="more" href="#">
                                                __ <i class="m-icon-swapright m-icon-white"></i>
                                                </a>
                                            </div>
                                            <br />
                                        </div>

                                        <div class="col-md-2" style="height: 130px;">
                                            <div class="dashboard-stat blue-madison">
                                                <div class="visual">
                                                    <i class="fa fa-level-up"></i>
                                                </div>
                                                <div class="details">
                                                    <div class="number">
                                                        <?php
															$recovery_management_id = $this->uri->segment(3);
															$this->db->order_by('start','ASC');
															//$this->db->limit(1);
															$comission_rule = $this->db->get_where('recovery_management_rules',array('recovery_management_id'=>$recovery_management_id,'start<='=>$submitted_fee_percentage,'end>'=>$submitted_fee_percentage))->result_array();
															if(count($comission_rule)>0)
															{
																$amount=0;
																$percent_amount=$comission_rule[0]['comission'];
																foreach($paid_payments_students as $due)
																{
																	if($due['split'] == '1' ){
										
																		$amount+=0.5*$percent_amount;
																	} else if($due['split'] == '2' ){
																		
																		$amount+=0.25*$percent_amount;
																	}else{
																		$amount+=$percent_amount;
																	}
																}
																foreach($paid_payments_contracts as $due)
																{
																	if($due['split'] == '1' ){
										
																		$amount+=0.5*$percent_amount;
																	} else if($due['split'] == '2' ){
																		
																		$amount+=0.25*$percent_amount;
																	}else{
																		$amount+=$percent_amount;
																	}
																}
																
		
																$installment_comission =  $amount;
																echo 'Rs '.$installment_comission;
															}
															else
															{
																$installment_comission=0;
																echo 'N/A';
															}
														?>
                                                    </div>
                                                    <div class="desc" style="font-size: small">
                                                        Calculated Insentive of Installments
                                                    </div>
                                                </div>
                                                <a class="more" href="#">
                                                __ <i class="m-icon-swapright m-icon-white"></i>
                                                </a>
                                            </div>
                                            <br />
                                        </div>
                                        
                                        <div class="col-md-2" style="height: 130px;">
                                            <div class="dashboard-stat blue-madison">
                                                <div class="visual">
                                                    <i class="fa fa-money"></i>
                                                </div>
                                                <div class="details">
                                                    <div class="number">
                                                        <?php
															$collected_fine=0;
															foreach($fine_students as $paid_payments_student)
															{
																if($paid_payments_student['paid']='1')
																{
																	$collected_fine += $paid_payments_student['fine_amount'];
																}
															}
															echo 'Rs '.$collected_fine;
														?>
                                                    </div>
                                                    <div class="desc" style="font-size: small">
                                                        Collected Fine Amount
                                                    </div>
                                                </div>

                                                <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/recovery_management/fine_data"  target="_blank">

                                                    <input type="hidden" name="fine_data[][]" value='<?php echo json_encode($fine_students) ?>'>


                                                    <button class="more" type="submit" style="width: 100%; text-align: left">
                                                        view more <i class="m-icon-swapright m-icon-white"></i>
                                                    </button>

                                                </form>
                                            </div>
                                            <br />
                                        </div>
                                        
                                        <div class="col-md-2" style="height: 130px;">
                                            <div class="dashboard-stat blue-madison">
                                                <div class="visual">
                                                    <i class="fa fa-money"></i>
                                                </div>
                                                <div class="details">
                                                    <div class="number">
                                                        <?php
															if(count($comission)>0)
															{
																$min_fine_amount = $comission[0]['min_fine_amount'];
																$fine_amount_percentage = $comission[0]['fine_amount_percentage'];
																if($collected_fine>$min_fine_amount)
																{
																	$fine_comission =  ($collected_fine*$fine_amount_percentage)/100;
																	echo 'Rs '.$fine_comission;
																}
																else
																{
																	$fine_comission = 0;
																	echo 'Rs '.$fine_comission;
																}
			
															}
															else
															{
																echo 'N/A';
															}
														?>
                                                    </div>
                                                    <div class="desc" style="font-size: small">
                                                        Calculate Incentive of Fine
                                                    </div>
                                                </div>
                                                <a class="more" href="#">
                                                ___ <i class="m-icon-swapright m-icon-white"></i>
                                                </a>
                                            </div>
                                            <br />
                                        </div>
                                        
                                        <div class="col-md-2" style="height: 130px;">
                                            <div class="dashboard-stat blue-madison">
                                                <div class="visual">
                                                    <i class="fa fa-paper-plane"></i>
                                                </div>
                                                <div class="details">
                                                    <div class="number">
                                                        <?php
															 $insentive = $installment_comission+$fine_comission;
															 echo 'Rs '.$insentive;
														?>
                                                    </div>
                                                    <div class="desc" style="font-size: small">
                                                        Total Incentive Amount
                                                    </div>
                                                </div>
                                                <a class="more" href="#">
                                                ___ <i class="m-icon-swapright m-icon-white"></i>
                                                </a>
                                            </div>
                                            <br />
                                        </div>
                                        
                                    </div>


						</div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->
