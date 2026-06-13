	<?php
    $myAccess = checkUserAccess();

    $this->db->select( 'remaining_amount');
            $this->db->from('petty_cash_college_wise');
            $petty=$this->db->where('assign_to', $this->session->userdata('user_id'))->get()->result_array();
			if(count($petty)>0){
			    $mypetty=$petty[0]['remaining_amount'];
			}else
			{
				$mypetty=0;
			}
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
								<i class="fa fa-money"></i> Update Fee / Reversal Requests
							</div>
						</div>
						<div class="portlet-body">
							<table  class="table table-striped table-bordered table-hover" id="sample_15">
							<thead>
							<tr>
                                <th class="hidden">
                                	Hidden
                                </th>
                                <th>
                                	Request Date
                                </th>
                                <th>
                                	Challan #
                                </th>
                                <th>
                                	Fee Amount
                                </th>
                                <th>
									 Paid Amount
								</th>
                                <th>
                                    Previous Installment Amount
                                </th>
                                <th>
									 Previous Fine Amount
								</th>
                                <th>
									 Student / Contractor
								</th>
								<th>
									 Dead Line
								</th>
                                <th>
									 Paid Date
								</th>
                                <th>
                                	Status
                                </th>
                                <th>
                                	Fee Type
                                </th>
                                <th>
									 Challan
								</th>
                                <th>
									 Add By
								</th>
                                <th>
									 Last Edit
								</th>
                                <th>
									 Reason
								</th>
								<th>
									 System Comment
								</th>
                                <th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
                            <?php
                            	$i=0;
								foreach($fee_requests as $fee_request):
                                    $originalDetail = getOriginalPayemntDetails($fee_request['id']);
                                    $show = 1;
                                    // $is_acount = 0;

                                    // $time=strtotime(@$originalDetail[0]['actual_paid_date']);
                                    // $day=(int)date("d",$time);
                                    // $month=(int)date("m",$time);
                                    // $year=(int)date("Y",$time);

                                    // $closing = $this->db->get_where('closing_perday',
                                    //     array('campus_id'=>@$originalDetail[0]['submitted_fee_campus_id'],
                                    //         'for_day'=>$day,
                                    //         'for_month'=>$month,
                                    //         'for_year'=>$year))->result_array();

                                    // if ((count($closing) > 0  && @$originalDetail[0]['fee_pay_through'] != 'bank'  ) || ( @$originalDetail[0]['fee_pay_through'] == 'bank' && @$originalDetail[0]['clear_by'] != '') || (@$originalDetail[0]['fee_pay_through'] == 'pay_pro'))
                                    //     $is_acount = 1;

                                    // if ($is_acount == 1) {
                                    //     if ($myAccess[0]['dashboard_update_reversal_payment_box'] == "1" || $this->session->userdata('role')=='Admin') {
                                    //         $show = 1;
                                    //     } else {
                                    //         $show = 0;
                                    //     }
                                    // }else{
                                    //     if ($myAccess[0]['dashboard_update_reversal_payment_box'] == "1" || $this->session->userdata('role')=='Admin')
                                    //         $show = 1;
                                    //     else
                                    //         $show = 0;
                                    // }
								    if ($show == 1): ?>
                                        <tr>
                            	<td class="hidden"><?php echo $i;?></td>
                                <td><?php echo $fee_request['update_date'];?>
                                </td>
                                <td><?php echo $fee_request['challan_no'];?>
                                </td>
                                <td>
									<?php echo $fee_request['amount'];?>
                                    <hr />
                                    <span class="<?php if($fee_request['amount']==$originalDetail[0]['amount']){echo '';}else{echo 'alert-danger';}?>"><?php echo $originalDetail[0]['amount'];?></span>
                                </td>
                                <td>
									<?php echo $fee_request['actual_amount'];?>
                                    <hr />
                                    <span class="<?php if($fee_request['actual_amount']==$originalDetail[0]['actual_amount']){echo '';}else{echo 'alert-danger';}?>"><?php echo $originalDetail[0]['actual_amount'];?></span>
                                </td>
                                <td>
                                    <?php echo $fee_request['remaining_installment_amount'];?>
                                    <hr />
                                    <span class="<?php if($fee_request['remaining_installment_amount']==$originalDetail[0]['remaining_installment_amount']){echo '';}else{echo 'alert-danger';}?>"><?php echo $originalDetail[0]['extra_amount'];?></span>
                                </td>
                                <td>
									<?php echo $fee_request['extra_amount'];?>
                                    <hr />
                                    <span class="<?php if($fee_request['extra_amount']==$originalDetail[0]['extra_amount']){echo '';}else{echo 'alert-danger';}?>"><?php echo $originalDetail[0]['extra_amount'];?></span>
                                </td>
                                <td><?php echo $fee_request['first_name'].' '.$fee_request['last_name'].' ('.$fee_request['roll_no'].')';?></td>
                                <td>
									<?php echo $fee_request['dead_line'];?>
                                    <hr />
                                    <span class="<?php if($fee_request['dead_line']==$originalDetail[0]['dead_line']){echo '';}else{echo 'alert-danger';}?>"><?php echo $originalDetail[0]['dead_line'];?></span>
                                </td>
                                <td>
									<?php echo $fee_request['paid_date'];?>
                                    <hr />
                                    <span class="<?php if($fee_request['paid_date']==$originalDetail[0]['paid_date']){echo '';}else{echo 'alert-danger';}?>"><?php echo $originalDetail[0]['paid_date'];?></span>
                                </td>
                                <td>
                                	<?php
                                    	if($fee_request['paid']==1)
										{
											echo 'Paid';
										}
										else
										{
											echo 'Unpaid';
										}
									?>
                                    <?php
                                    	if($originalDetail[0]['paid']==1)
										{
											if($fee_request['paid']==$originalDetail[0]['paid'])
											{
												echo '<span class="">Paid</span>';
											}
											else
											{
												echo '<span class="alert-danger">Paid</span>';
											}
										}
										else
										{
											if($fee_request['paid']==$originalDetail[0]['paid'])
											{
												echo '<span class="">Unpaid</span>';
											}
											else
											{
												echo '<span class="alert-danger">Unpaid</span>';
											}
										}
									?>
                                </td>
                                <td>
                                	<?php
                                    	if($fee_request['college_fee']==1)
										{
											echo 'College Fee';
										}
										else
										{
											echo 'Bank Fee';
										}
									?>
									<hr />
                                    <?php
                                    	if($originalDetail[0]['college_fee']!=$fee_request['college_fee'])
										{
											if($originalDetail[0]['college_fee']==1)
											{
												echo '<span class="alert-danger">College Fee</span>';
											}
											else
											{
												echo '<span class="alert-danger">Bank Fee</span>';
											}
										}
										else
										{
											if($originalDetail[0]['college_fee']==1)
											{
												echo '<span>College Fee</span>';
											}
											else
											{
												echo '<span>Bank Fee</span>';
											}
										}
									?>
                                </td>
                                <td>
                                	<?php
                                    	if($fee_request['scan_challan']!=''):
									?>
                                    <a href="<?php echo base_url();?>uploads/<?php echo $fee_request['scan_challan'];?>" class="btn purple" target="_blank">Challan</a>
                                    <?php
                                    	endif;
									?>
                                    <?php
                                    	if($fee_request['scan_challan']==$originalDetail[0]['scan_challan']):
										else:
									?>
                                    <a href="<?php echo base_url();?>uploads/<?php echo $originalDetail[0]['scan_challan'];?>" class="btn red" target="_blank">Challan</a>
                                    <?php
                                    	endif;
									?>
                                </td>
                                <td><?php echo $fee_request['add_by'];?></td>
                                <td><?php echo $fee_request['last_edit'];?></td>
                                <td><?php echo $fee_request['reason'];?></td>
								<td>
									<?php 
										if($fee_request['del']==1)
										{
											echo 'Delete Payment';
										}
									?>
								</td>
                                <td>
                                	<a class="btn blue" target="_blank" href="<?php echo site_url();?>/students/payments_paid/<?php echo $fee_request['student_id'];?>">View</a>
                                    <br /><br />
									<?php 
											$time=strtotime($originalDetail[0]['actual_paid_date']);
												$day=(int)date("d",$time);
												$month=(int)date("m",$time);
												$year=(int)date("Y",$time);
										 $closing = $this->db->get_where('closing_perday',
											array('campus_id'=>$originalDetail[0]['submitted_fee_campus_id'],
												'for_day'=>$day,
												'for_month'=>$month,
												'for_year'=>$year))->result_array();
											
									if($fee_request['paid']!=$originalDetail[0]['paid'] && $originalDetail[0]['closing_id'] != NULL)
									{
										if($mypetty > $originalDetail[0]['actual_amount']):?>
										<a class="btn green" href="<?php echo site_url();?>/dashboard/clear_fee_update/<?php echo $fee_request['id'];?>" onclick="return confirm('Are you sure ?')">Clear</a>
											
									<?php	endif;
									}else{
											
										if($mypetty > $originalDetail[0]['actual_amount'] || $fee_request['paid']==$originalDetail[0]['paid'] || $originalDetail[0]['clear_college_fee'] == '0' || count($closing) < 1): ?>
											<a class="btn green" href="<?php echo site_url();?>/dashboard/clear_fee_update/<?php echo $fee_request['id'];?>" onclick="return confirm('Are you sure ?')">Clear</a>
										<?php endif;
									
									}?>
									<br /><br />
									<a class="btn red" href="<?php echo site_url();?>/dashboard/reject_clear_fee_update/<?php echo $fee_request['id'];?>" onclick="return confirm('Are you sure ?')">Reject</a>
                                </td>
                            </tr>
                                <?php
                                    endif;
                            	    $i++;
								endforeach;
                            	$i=0;
								foreach($fee_requests_contractors as $fee_request):
								$originalDetail = getOriginalPayemntDetails($fee_request['id']);
							?>
                            <tr>
                            	<td class="hidden"><?php echo $i;?></td>
                                <td><?php echo $fee_request['update_date'];?>
                                </td>
                                <td><?php echo $fee_request['challan_no'];?>
                                </td>
                                <td>
									<?php echo $fee_request['amount'];?>
                                    <hr />
                                    <span class="<?php if($fee_request['amount']==$originalDetail[0]['amount']){echo '';}else{echo 'alert-danger';}?>"><?php echo $originalDetail[0]['amount'];?></span>
                                </td>
                                <td>
									<?php echo $fee_request['actual_amount'];?>
                                    <hr />
                                    <span class="<?php if($fee_request['actual_amount']==$originalDetail[0]['actual_amount']){echo '';}else{echo 'alert-danger';}?>"><?php echo $originalDetail[0]['actual_amount'];?></span>
                                </td>
                                <td>
									<?php echo $fee_request['extra_amount'];?>
                                    <hr />
                                    <span class="<?php if($fee_request['extra_amount']==$originalDetail[0]['extra_amount']){echo '';}else{echo 'alert-danger';}?>"><?php echo $originalDetail[0]['extra_amount'];?></span>
                                </td>
                                <td><?php echo $fee_request['name'].' ('.$fee_request['contract_name'].')';?></td>
                                <td>
									<?php echo $fee_request['dead_line'];?>
                                    <hr />
                                    <span class="<?php if($fee_request['dead_line']==$originalDetail[0]['dead_line']){echo '';}else{echo 'alert-danger';}?>"><?php echo $originalDetail[0]['dead_line'];?></span>
                                </td>
                                <td>
									<?php echo $fee_request['paid_date'];?>
                                    <hr />
                                    <span class="<?php if($fee_request['paid_date']==$originalDetail[0]['paid_date']){echo '';}else{echo 'alert-danger';}?>"><?php echo $originalDetail[0]['paid_date'];?></span>
                                </td>
                                <td>
                                	<?php
                                    	if($fee_request['paid']==1)
										{
											echo 'Paid';
										}
										else
										{
											echo 'Unpaid';
										}
									?>
                                    <?php
                                    	if($originalDetail[0]['paid']==1)
										{
											if($fee_request['paid']==$originalDetail[0]['paid'])
											{
												echo '<span class="">Paid</span>';
											}
											else
											{
												echo '<span class="alert-danger">Paid</span>';
											}
										}
										else
										{
											if($fee_request['paid']==$originalDetail[0]['paid'])
											{
												echo '<span class="">Unpaid</span>';
											}
											else
											{
												echo '<span class="alert-danger">Unpaid</span>';
											}
										}
									?>
                                </td>
                                <td>
                                	<?php
                                    	if($fee_request['college_fee']==1)
										{
											echo 'College Fee';
										}
										else
										{
											echo 'Bank Fee';
										}
									?>
									<hr />
                                    <?php
                                    	if($originalDetail[0]['college_fee']!=$fee_request['college_fee'])
										{
											if($originalDetail[0]['college_fee']==1)
											{
												echo '<span class="alert-danger">College Fee</span>';
											}
											else
											{
												echo '<span class="alert-danger">Bank Fee</span>';
											}
										}
										else
										{
											if($originalDetail[0]['college_fee']==1)
											{
												echo '<span>College Fee</span>';
											}
											else
											{
												echo '<span>Bank Fee</span>';
											}
										}
									?>
                                </td>
                                <td>
                                	<?php
                                    	if($fee_request['scan_challan']!=''):
									?>
                                    <a href="<?php echo base_url();?>uploads/<?php echo $fee_request['scan_challan'];?>" class="btn purple" target="_blank">Challan</a>
                                    <?php
                                    	endif;
									?>
                                    <?php
                                    	if($fee_request['scan_challan']==$originalDetail[0]['scan_challan']):
										else:
									?>
                                    <a href="<?php echo base_url();?>uploads/<?php echo $originalDetail[0]['scan_challan'];?>" class="btn red" target="_blank">Challan</a>
                                    <?php
                                    	endif;
									?>
                                </td>
                                <td><?php echo $fee_request['add_by'];?></td>
                                <td><?php echo $fee_request['last_edit'];?></td>
                                <td><?php echo $fee_request['reason'];?></td>
								<td>
									<?php 
										if($fee_request['del']==1)
										{
											echo 'Delete Payment';
										}
										else
										{
											echo 'N/A';
										}

										if ($originalDetail[0]['clear_by'] == "" )
										    $text = "Are you sure";
										else
                                            $text = "Your PettyCash will be deducted";

									?>
								</td>
                                <td>
                                	<a class="btn blue" target="_blank" href="<?php echo site_url();?>/students/payments_paid/<?php echo $fee_request['student_id'];?>">View</a>
                                    <br /><br />
                                    <a class="btn green" href="<?php echo site_url();?>/dashboard/clear_fee_update/<?php echo $fee_request['id'];?>" onclick="return confirm('Are you sure ?')">Clear</a>
									<br /><br />
									<a class="btn red" href="<?php echo site_url();?>/dashboard/reject_clear_fee_update/<?php echo $fee_request['id'];?>" onclick="return confirm('Are you sure ?')">Reject</a>
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
