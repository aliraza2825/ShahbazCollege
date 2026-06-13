	
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
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i>Students Due Fees Status Clear
							</div>
						</div>
						<div class="portlet-body">
                            <table class="table table-bordered table-hover" id="sample_19">
							<thead>
							<tr>
                                <th class="hidden">
									 Hidden
								</th>
								<th>
                                	Campus
                                </th>
                                <th>
                                	Class Name
                                </th>
                                <th>
									 Roll No
								</th>
                                <th>
									 Name
								</th>
                                <th>
									 Mobile
								</th>
                                <th>
									 Fees
								</th>
                                <th>
									 Remaining Dues
								</th>
                                <th>
									 Fee Last Date
								</th>
                                <th>
									 Extend Fee Date
								</th>
                                <th>
                                	Result Remarks
                                </th>
                                <th>
									 Manual Remarks
								</th>
                                <th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=0;
								foreach($fee_dues_clear_comments as $due):
								$class = '';
								if(date('Y-m-d')<= $due['dead_line'])
								{
									$class = 'alert alert-success';
								}
								else
								{
									$class = 'alert alert-danger';
								}
								$this->db->order_by('fee_remarks_id','ASC');
								$remarks = $this->db->get_where('fees_remarks', array('fee_id'=>$due['fee_id']))->result_array();
							?>
                            <tr class="<?php echo $class;?>">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
                                <td>
									<?php echo $due['campus_name']?>
								</td>
                                <td>
									<?php echo $due['class_name']?>
								</td>
                                <td>
									<?php echo $due['roll_no']?>
								</td>
								<td>
									<?php echo $due['first_name'].' '.$due['last_name'];?>
								</td>
                                <td>
									<?php echo $due['mobile'];?> <hr /> <?php echo $due['emergency_no'];?>
								</td>
                                <td>
                                	<?php echo $due['amount'];?>
                                </td>
                                <td>
                                	<?php echo $due['extra_amount'];?>
                                </td>
								<td>
									<strong><?php echo date('d F, Y', strtotime($due['dead_line']));?></strong>
								</td>
                                <td>
                                	<?php 
										$remark_last_key = end($remarks);
										
										$extended_date = $remark_last_key['paid_on_date'];
										if($extended_date==$due['dead_line'])
										{
											$blinking = '';
										}
										else
										{
											$blinking = 'blink_me';
										}
										echo '<span class="'.$blinking.'"><strong>'.date('d F, Y',strtotime($extended_date)).'</strong></span>';
									?>
                                </td>
                                <td>
                                	<?php getStudentResultRemarks($due['cnic']);?>
                                </td>
                                <td>
									<div class="fee_<?php echo $due['fee_id'];?>">
										<?php
                                            foreach($remarks as $remark):
                                        ?>
                                            <?php
                                                echo '<p>'.@$remark['comment'].'<br /> ('.@$remark['add_by'].' on '.@date('d M, Y H:i:s A',strtotime(@$remark['date'])).')</p>';
                                            ?>
                                        <?php
                                            endforeach;
                                        ?>
                                    </div>
								</td>
                                <td>
									<a target="_blank" href="<?php echo site_url();?>/students/payments_paid/<?php echo $due['student_id'];?>" class="btn blue">View</a>
									<br /><br />
                                	<a onclick="return confirm('Are you sure ?')" href="<?php echo site_url();?>/dashboard/clear_comment/<?php echo $due['fee_id'];?>/<?php echo $remark['paid_on_date'];?>" class="btn green">Clear</a>
									<br /><br />
									<a onclick="return confirm('Are you sure ?')" href="<?php echo site_url();?>/dashboard/delete_comment/<?php echo $due['fee_id'];?>/<?php echo $remark['paid_on_date'];?>" class="btn red">Reject</a>
                                </td>
							</tr>
                            <?php
								$i++;
                            	endforeach;
							?>
                            
                            
                            <?php
								$i=0;
								foreach($contractors_fee_dues_clear_comments as $due):
								$class = '';
								if(date('Y-m-d')<= $due['dead_line'])
								{
									$class = 'alert alert-success';
								}
								else
								{
									$class = 'alert alert-danger';
								}
								$this->db->order_by('fee_remarks_id','ASC');
								$remarks = $this->db->get_where('fees_remarks', array('fee_id'=>$due['fee_id']))->result_array();
							?>
                            <tr class="<?php echo $class;?>">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
                                <td>
									<?php echo $due['campus_name']?>
								</td>
                                <td>
									<?php echo 'N/A';?>
								</td>
                                <td>
									<?php echo $due['contractor_id_from_college']?>
								</td>
								<td>
									<?php echo $due['name'];?>
								</td>
                                <td>
									<?php echo $due['mobile'];?> <hr /> <?php echo $due['emergency_no'];?>
								</td>
                                <td>
                                	<?php echo $due['amount'];?>
                                </td>
                                <td>
                                	<?php echo $due['extra_amount'];?>
                                </td>
								<td>
									<strong><?php echo date('d F, Y', strtotime($due['dead_line']));?></strong>
								</td>
                                <td>
                                	<?php 
										$remark_last_key = end($remarks);
										$extended_date = $remark_last_key['paid_on_date'];
										if($extended_date==$due['dead_line'])
										{
											$blinking = '';
										}
										else
										{
											$blinking = 'blink_me';
										}
										echo '<span class="'.$blinking.'"><strong>'.date('d F, Y',strtotime($extended_date)).'</strong></span>';
									?>
                                </td>
                                <td>
                                	<?php getStudentResultRemarks($due['cnic']);?>
                                </td>
                                <td>
									<div class="fee_<?php echo $due['fee_id'];?>">
										<?php
                                            foreach($remarks as $remark):
                                        ?>
                                            <?php
                                                echo '<p>'.@$remark['comment'].'<br /> ('.@$remark['add_by'].' on '.@date('d M, Y H:i:s A',strtotime(@$remark['date'])).')</p>';
                                            ?>
                                        <?php
                                            endforeach;
                                        ?>
                                    </div>
								</td>
                                <td>
									<a target="_blank" href="<?php echo site_url();?>/contractors/contract_payments_paid/<?php echo $due['contract_id'];?>" class="btn blue">View</a>
									<br /><br />
                                	<a onclick="return confirm('Are you sure ?')" href="<?php echo site_url();?>/dashboard/clear_comment/<?php echo $due['fee_id'];?>/<?php echo $remark['paid_on_date'];?>" class="btn green">Clear</a>
									<br /><br />
									<a onclick="return confirm('Are you sure ?')" href="<?php echo site_url();?>/dashboard/delete_comment/<?php echo $due['fee_id'];?>/<?php echo $remark['paid_on_date'];?>" class="btn red">Reject</a>
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