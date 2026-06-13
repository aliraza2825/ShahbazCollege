	
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
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php echo @$total_submitted_fee[0]['total_submitted_fee'];?>
							</div>
							<div class="desc">
								 Total Fee
							</div>
						</div>
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
								<i class="fa fa-list"></i> Fees Submited
							</div>
						</div>
                        <div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/dashboard/new_submit_fees">
								<div class="form-body">
                                	<div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label col-md-3">Start Date</label>
                                                <div class="col-md-3">
                                                    <div class="input-group input-medium date date-picker" data-date="<?php echo $start_date;?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                        <input type="text" name="start_date" class="form-control" value="<?php echo $start_date;?>" readonly>
                                                        <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                                    </div>
                                                    <!-- /input-group -->
                                                    <!--<span class="help-block">
                                                    Select date </span>-->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label col-md-3">End Date</label>
                                                <div class="col-md-3">
                                                    <div class="input-group input-medium date date-picker" data-date="<?php echo $end_date;?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                        <input type="text" name="end_date" class="form-control" value="<?php echo $end_date;?>" readonly>
                                                        <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                                    </div>
                                                    <!-- /input-group -->
                                                    <!--<span class="help-block">
                                                    Select date </span>-->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Date Type</label>
                                                <div class="col-md-6 radio-list">
                                                    <label class="radio-inline">
                                                    <input type="radio" name="date_type" id="optionsRadios4" value="paid_date" <?php if($date_type=='paid_date'){echo 'checked';}?> /> Submit Date </label>
                                                    <label class="radio-inline">
                                                    <input type="radio" name="date_type" id="optionsRadios5" value="actual_paid_date" <?php if($date_type=='actual_paid_date'){echo 'checked';}?> /> Upload Date </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Check</button>
										</div>
									</div>
								</div>
                            </form>
                        </div>
						<div class="portlet-body">
							<table class="table table-striped table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
                                <th class="hidden">
									 Hidden
								</th>
                                <th>
									 Student / Contractor Name
								</th>
                                <th>
                                	 Course
                                </th>
                                <th>
                                	 Class
                                </th>
                                <th>
                                	 Campus
                                </th>
                                <th>
									 Fees
								</th>
                                <th>
									 Challan
								</th>
								<th>
									 Fee Comment
								</th>
                                <th>
									 Submit Date
								</th>
                                <th>
									 Upload Date
								</th>
                                <th>
									 Submitted
								</th>
                                <th>
									 Last Edit
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=0;
								foreach($fees as $fee):
							?>
                            <tr class="odd gradeX">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
                                <td>
									 <?php 
									 	if($fee['student_id']!=0)
										{
											echo $this->db->get_where('students', array('student_id'=>$fee['student_id']))->row()->first_name.' '.$this->db->get_where('students', array('student_id'=>$fee['student_id']))->row()->last_name.' ('.$this->db->get_where('students', array('student_id'=>$fee['student_id']))->row()->roll_no.') (Student)';
											//echo $fee['student_id'];
										}
										else
										{
											$this->db->select('*');
											$this->db->from('contracts');
											$this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','inner');
											$this->db->join('campuses','campuses.campus_id=contracts.campus_id','left');
											$this->db->where('contracts.contract_id',$fee['contract_id']);
											$contractor_details = $this->db->get()->result_array();
											
											echo 'Contractor Name : '.$contractor_details[0]['name'];
											echo '<br />';
											echo 'Contract Name : '.$contractor_details[0]['contract_name'];
										}
									 ?>
								</td>
                                <td>
									<?php 
										
										if($fee['student_id']!=0)
										{
											$class_id = $this->db->get_where('students', array('student_id'=>$fee['student_id']))->row()->course_id;
											echo $this->db->get_where('courses', array('course_id'=>$class_id))->row()->course_name;
										}
										else
										{
											echo 'N/A';
										}
									?>
								</td>
                                <td>
									<?php

										if($fee['student_id']!=0)
										{
											$class_id = $this->db->get_where('students', array('student_id'=>$fee['student_id']))->row()->class_id;
											echo $this->db->get_where('classes', array('class_id'=>$class_id))->row()->name;
										}
										else
										{
											echo 'N/A';
										}
									?>
								</td>
                                <td>
                                	<?php
                                    	//$class_id = $this->db->get_where('students', array('student_id'=>$fee['student_id']))->row()->class_id;
										
										if($fee['student_id']!=0)
										{
											$campus_id = $this->db->get_where('classes', array('class_id'=>$class_id))->row()->campus_id;
											echo $this->db->get_where('campuses', array('campus_id'=>$campus_id))->row()->campus_name;
										}
										else
										{
											echo @$contractor_details[0]['campus_name'];
										}
									?>
                                </td>
                                <td>
									<?php echo $fee['actual_amount'];?>
								</td>
                                <td>

                                    <?php
                                    if($fee['fee_pay_through']=='pay_pro'):
                                        $paypro_payment = $this->db->get_where("settlement_payments","id = '".$fee['settlement_payment_id']."'")->row();
                                        $stats = $this->db
                                            ->join('pay_pro_settlement',"pay_pro_settlement.id = bank_reconciliation_statement.paypro_id")
                                            ->join('accounts',"accounts.id = bank_reconciliation_statement.account_id")
                                            ->get_where("bank_reconciliation_statement","bank_reconciliation_statement.paypro_id = '".$fee['settlement_id']."'")
                                            ->result_array();
                                        foreach ($stats as $stat){
                                            if ($paypro_payment->paid_via == "1LINK") {
                                                if ((int)str_replace(",", "", $stat['credit']) == $stat ['link_amount']) {
                                                    echo "<strong>Bank Name</strong> : " . $stat['account_title'] . ' ' . $stat['account_name'] . "<br />" .
                                                        "<strong>Date</strong> : " . $stat['trans_date'] . "<br />" .
                                                        "<strong>Amount</strong> : " . $stat['link_amount'] . "<br />" .
                                                        "<strong>Description </strong>: " . $stat['description'] . "<br />";
                                                    $show = 1;
                                                }
                                            }else{
                                                if ((int)str_replace(",", "", $stat['credit']) == $stat ['card_amount']) {
                                                    echo "<strong>Bank Name</strong> : " . $stat['account_title'] . ' ' . $stat['account_name'] . "<br />" .
                                                        "<strong>Date</strong> : " . $stat['trans_date'] . "<br />" .
                                                        "<strong>Amount</strong> : " . $stat['card_amount'] . "<br />" .
                                                        "<strong>Description </strong>: " . $stat['description'] . "<br />";
                                                    $show = 1;
                                                }
                                            }
                                        }
                                        if ($fee['settlement_id'] != NULL)
                                            echo '<a href="'.site_url().'/excel_import/entries/'.$fee['settlement_id'].'" target="_blank" class="btn purple pull-left">See PayPro Details</a> <br />';

                                        ?>

                                    <?php
                                    endif;
                                    ?>
                                    <br />
                                    Paid BY. : <?php echo $fee['paid_by'];?>
                                    <div class="clearfix"></div>
                                    <br />
                                    <?php
                                    if($fee['scan_challan']=='')
                                    {

                                    }
                                    elseif($fee['scan_challan']!='' )
                                    {
                                        if($fee['online_scan_challan']!='')
										{
											echo '<a href="'.str_replace($bucket_address,$cloudfront_address,$fee['online_scan_challan']).'" target="_blank" class="btn purple pull-left">See Challan</a> <br />';
										}
										else
										{
											echo '<a href="'.base_url().'uploads/'.$fee['scan_challan'].'" target="_blank" class="btn purple pull-left">See Challan</a> <br />';
										}
                                    }

                                    if($fee['fee_pay_through']=='college' && $fee['fee_submit_type']=='computer_challan')
                                    {
                                        echo '<a href="'.site_url().'/students/print_college_challan/'.$fee['id'].'" target="_blank" class="btn blue college_fee_'.$i.'"><i class="fa fa-print"></i> See Challan</a> <br />';
                                    }
                                    ?>

<!--									<a href="--><?php //echo base_url();?><!--uploads/--><?php //echo $fee['scan_challan'];?><!--" target="_blank" class="btn green" >Challan</a>-->
								</td>
								<td>
								    <?php echo $fee['payment_comment'];?>
								</td>
                                <td>
									<?php echo date('d M, Y', strtotime($fee['paid_date']));?>
								</td>
                                <td>
									<?php echo date('d M, Y', strtotime($fee['actual_paid_date']));?>
								</td>
                                <td>
									<?php
										echo strtoupper($fee['fee_pay_through']);
									?>
								</td>
                                <td>
									<?php echo $fee['last_edit'];?>
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