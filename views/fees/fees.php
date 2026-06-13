	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
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
								<i class="fa fa-list"></i>Students Due Fees
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/fees" enctype="multipart/form-data">
								<div class="form-body">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Type</label>
                                        <div class="col-md-9 radio-list">
                                            <label class="radio-inline">
                                            <input class="type" type="radio" name="type" id="optionsRadios1" value="students" checked> Students </label>
                                            <label class="radio-inline">
                                            <input class="type" type="radio" name="type" id="optionsRadios2" value="contractors"> Contractors </label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control campus" name="campus_id" required>
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
                                    <div class="content_type students">
                                        <div class="form-group class_section">
                                            <label class="col-md-3 control-label">Class <span class="required">*</span></label>
                                            <div class="col-md-5">
                                                <select class="form-control classes" name="class_id">
                                                </select>
                                                <!--<span class="help-inline"></span>-->
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">Today Attendence Wise Dues <span class="required">*</span></label>
                                            <div class="col-md-5">
                                                <input type="checkbox" class="form-control today_wise" name="today_wise" value="1" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="content_type contractors" style="display:none;">
                                        
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Get Dues</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>
            <?php
            	if(@$dues):
			?>
            <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i>Students Due Fees (<?php echo @$dues[0]['class_name'];?>)
							</div>
						</div>
						<div class="portlet-body">
							<div class="table-toolbar">
								<div class="row">
									<div class="col-md-6">
										<div class="btn-group">
                                            <form action="<?php echo site_url()?>/fees/prints" method="post" target="_blank">
                                                <input type="hidden" name="class_id" value="<?php echo $this->input->post('class_id');?>" />
                                                <button class="btn green" type="submit">
                                                Print <i class="fa fa-print"></i>
                                                </button>
                                            </form>
										</div>
									</div>
								</div>
							</div>
                            <table class="table table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
                                <th class="hidden">
									 Hidden
								</th>
								<th >
									 Campus
								</th>
							
								<?php
									if(@$this->input->post('today_wise')==1):
								?>
								<th>
									Today Present Campus
								</th>
								<?php
									endif;
								?>
								<th>
									 Course Name
								</th>
								
                                <th>
									 Picture
								</th>
                                <th>

                                    Student Details

                                </th>
								
								
                                <th>
                                    Fee Information&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </th>
                                <th>
                                    Paid Fee Details &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </th>
                                <th>
                                    Unpaid Fee Details&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=0;
								foreach($dues as $due):
								 $this->db->order_by('dead_line','ASC');
									
									$this->db->select('*');
									$this->db->from('payments');
									$this->db->where('student_id', $due['student_id']);
									$this->db->where('merged_challan IS NOT NULL and actual_amount > 0');
									$this->db->group_by("CASE WHEN merged_challan IS NOT NULL THEN merged_challan else '' end",false);
									$this->db->order_by('dead_line','ASC');
									$query = $this->db->get()->result_array();

									$this->db->select('*');
									$this->db->from('payments');
									$this->db->where('student_id', $due['student_id']);
									$this->db->where('merged_challan is null');
									$this->db->or_where('student_id = "'.$due['student_id'].'" and merged_challan IS not NULL and actual_amount = 0');
									$this->db->order_by('dead_line','ASC');
									$query2 = $this->db->get()->result_array();
									
									
                                    $payments =array_merge($query,$query2);
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


							?>
                            <tr>
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
                               <td>
									<?php echo $due['campus_name'];?>
								</td>
								<?php
									if(@$this->input->post('today_wise')==1):
								?>
								<td>
									<?php echo $this->db->get_where('campuses',array('campus_id'=>$this->input->post('campus_id')))->row()->campus_name;?>
								</td>
								<?php
									endif;
								?>
								<td>
									<?php echo $due['course_name']?>
								</td>
							
								<td>
								
									<img src="<?php echo base_url();?>uploads/<?php echo @$this->db->get_where('student_documents',array('student_id'=>$due['student_id'],'type'=>'Photo'))->row()->image;?>" width="100" />
								</td>
								 <td>
                                    
                                    Class : <?php echo $due['class_name'];?>
                                    <br />
                                    Student Name : <span class="bold"><?php echo $due['first_name'].' '.$due['last_name'];?></span>
                                    <br />
                                    CNIC : <?php echo $due['cnic'];?>
                                    <br />
                                    Roll # : <span class="bold"><?php echo $due['roll_no'];?></span>
                                    <br />
                                    Mobile : <span class="bold"><?php echo $due['mobile'];?> - <?php echo $due['emergency_no'];?></span>
                                </td>
								
                                <td>
                                    Total Fee : <?php echo $total_fee;?>
                                    <br />
                                    Total Created Fee : <?php echo $total_fee;?>
                                    <br />
                                    Total Created Council Fee : <?php echo $created_council_fee;?>
                                    <br />
                                    Total Submitted Council Fee : <?php echo $submitted_council_fee;?>
                                    <br />
                                    Fee Decided Current Time : <span class="bold"><?php echo $fee_decided_current_time;?></span>
                                    <br />
                                    Total Fee Submitted : <span class="bold"><?php echo $total_fee_submitted;?></span>
                                    <br />
                                    Remaining Fee Payable Current Time : <span class="bold"><?php echo $fee_decided_current_time-$total_fee_submitted;?></span>
                                    <br />
                                    Unpaid installments Current Time : <span class="bold"><?php echo $unpaid_installments_current_time;?></span>
                                    <br />
                                    Percentage Fee Received : <?php if ($total_fee_submitted > 0) {
                                        echo round(($total_fee_submitted / $total_fee) * 100) . '%';
                                    }else {
                                        echo "0 %";
                                    }
                                        ?>
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
            	endif;
			?>
            
            
            
            <?php
            	if(@$contractors_dues):
			?>
            <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> Contractors Due Fees of Campus (<?php echo @$contractors_dues[0]['campus_name'];?>)
							</div>
						</div>
						<div class="portlet-body">
							
                            <table class="table table-bordered table-hover" id="sample_3">
							<thead>
							<tr>
                                <th class="hidden">
									 Hidden
								</th>
								<th>
									Campus Name
								</th>
                                <th>
									 Contractor Name
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
									 Last Date
								</th>
                                <th>
                                	Add Comment
                                </th>
                                <th>
									 Manual Remarks
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=0;
								foreach($contractors_dues as $dues):
								$class = '';
								if(date('Y-m-d')<= $dues['dead_line'])
								{
									$class = 'alert alert-success';
								}
								else
								{
									$class = 'alert alert-danger';
								}
							?>
                            <tr class="<?php echo $class;?>">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
                                <td>
									<?php echo $dues['campus_name'];?>
								</td>
								<td>
									<?php echo $dues['name'];?>
								</td>
                                <td>
									<?php echo $dues['mobile'];?> <hr /> <?php echo $dues['emergency_no'];?>
								</td>
                                <td>
                                	<?php echo $dues['amount'];?>
                                </td>
                                <td>
                                	<?php echo $dues['extra_amount'];?>
                                </td>
								<td>
									<?php echo date('d F, Y', strtotime($dues['dead_line']));?>
								</td>
                                <td>
                                    <form action="#" name="fees_form">
                                    <label>Comment</label>
                                    <input class="form-control comment_box comment-<?php echo $dues['fee_id'];?>" type="text" name="comment-<?php echo $dues['fee_id'];?>" value="" />
                                    <label>Next Due Date</label>
                                    <div class="input-group input-small date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                        <input type="text" name="date_of_birth" class="form-control selected-date-<?php echo $dues['fee_id'];?>" value="<?php echo date('Y-m-d');?>" readonly>
                                        <span class="input-group-btn">
                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                        </span>
                                    </div>
                                    <button type="button" class="btn green submit_fee_dues_comment" data-fee-id="<?php echo $dues['fee_id'];?>">Submit</button>
                                    </form>
								</td>
                                <td>
									<div class="fee_<?php echo $dues['fee_id'];?>">
										<?php
                                            $remarks = $this->db->get_where('fees_remarks', array('fee_id'=>$dues['fee_id']))->result_array();
                                            foreach($remarks as $remark):
                                        ?>
                                            <?php
                                                echo '<p>'.@$remark['comment'].' ('.@$remark['paid_on_date'].') ('.@$remark['add_by'].' on '.@date('d M, Y H:i:s A',strtotime(@$remark['date'])).')</p>';
                                            ?>
                                        <?php
                                            endforeach;
                                        ?>
                                    </div>
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
            	endif;
			?>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->