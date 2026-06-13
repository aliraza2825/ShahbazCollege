<style>
	.btn{
		margin-bottom:10px;
	}
</style>
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Add Teacher <small>You can add teacher here</small>
			</h3>-->
			<!-- END PAGE HEADER-->
            <!-- BEGIN DASHBOARD STATS -->
            <div class="row">
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat blue-madison">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php 
								 	echo $total_contract_amount[0]['total_contract_amount'];
								 ?>
							</div>
							<div class="desc">
								 Total Contract Fee
							</div>
						</div>
						<!--<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>-->
					</div>
				</div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat yellow">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php echo $fee_should_pay[0]['fee_should_pay'];?>
							</div>
							<div class="desc">
								 Fee Should Pay
							</div>
						</div>
						<!--<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>-->
					</div>
				</div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat green">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php echo $paid_fee[0]['paid_fee'];?>
							</div>
							<div class="desc">
								 Fee Paid
							</div>
						</div>
						<!--<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>-->
					</div>
				</div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat purple">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php echo $remaining_fee[0]['remaining_fee'];?>
							</div>
							<div class="desc">
								 Remaining Fee
							</div>
						</div>
						<!--<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>-->
					</div>
				</div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat red">
						<div class="visual">
							<i class="fa fa-graduation-cap"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php echo $no_of_students[0]['no_of_students'];?>
							</div>
							<div class="desc">
								 Students
							</div>
						</div>
						<!--<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>-->
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
            <?php if(@$this->session->userdata('error')):?>
                <div class="alert alert-danger">
                    <button class="close" data-close="alert"></button>
                    <span>
                    <?php echo $this->session->userdata('error');?> </span>
                </div>
            <?php endif;?>
			<div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-money"></i>Add Contrator's (<?php echo $contractor[0]['name']; ?>) Payment
							</div>
						</div>
						<div class="portlet-body">
							<table  class="table table-striped table-bordered table-hover" id="sample_3">
							<thead>
							<tr>
                                <th>
									 Sr #
								</th>
                                <th>
									 Amount
								</th>
								<th>
									 Dead Line
								</th>
                                <th>
									 Clallan #
								</th>
                                <th>
									 College Fee
								</th>
                                <th>
									 Challan Image
								</th>
                                <th>
									 Application
								</th>
                                <th>
                                	Amount Paid
                                </th>
                                <th>
									 Student
								</th>
                                <th>
									 Paid Date
								</th>
                                <th>
                                	Status
                                </th>
								<th style="width:300px;">
                                	Action
                                </th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=1;
								foreach($payments as $payment):
							?>
                            <tr class="odd gradeX" <?php if($payment['amount']>$payment['actual_amount'] && $payment['paid']==1){echo 'style="background-color:#F2DEDE;"';}?>>
                            	<form action="<?php echo site_url().'/students/contractor_paid_payment_action/'.$this->uri->segment(3);?>" method="post" enctype="multipart/form-data">
                                <td>
                                	<?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $payment['amount']+$payment['extra_amount'];?>
								</td>
								<td>
									<?php echo $payment['dead_line'];?>
								</td>
                                <td>
									<?php echo $payment['challan_no'];?>
								</td>
                                <td>
									<input type="checkbox" value="1" class="college_fee" data-college-fee-id="college_fee_<?php echo $i;?>" data-bank-fee-id="bank_fee_<?php echo $i;?>" <?php if($payment['college_fee']==1){echo 'checked=checked';}?> />
								</td>
                                <td>
									<?php 
										if($payment['scan_challan']=='')
										{
									?>
                                    	<input type="file" class="hidden scan_challan_input" id="" name="scan_challan" value="" required />
                                        <button class="btn green scan_challan" id="">Upload</button>
                                    <?php
										}
										else
										{
											echo '<a href="'.base_url().'uploads/'.$payment['scan_challan'].'" target="_blank" class="btn purple">See Challan</a>';
										}
									?>
								</td>
                                <td>
									<?php 
										if($payment['fine_application']=='' && $payment['paid']==0)
										{
									?>
                                    	<input type="file" class="hidden fine_application_input" name="fine_application" id="" value="" />
                                        <button class="btn green fine_application" id="">Upload</button>
                                    <?php
										}
										else if($payment['fine_application']!='' && $payment['paid']==1)
										{
											echo '<a href="'.base_url().'uploads/'.$payment['fine_application'].'" target="_blank" class="btn purple">See Application</a>';
										}
										else
										{
											
										}
									?>
								</td>
								<td>
                                	<?php 
										if($payment['actual_amount']==0)
										{
									?>
                                    	<input type="number" class="form-control input-inline input-small" name="actual_amount" value="" min="0" required />
                                    <?php
										}
										else
										{
											echo $payment['actual_amount'];
										}
									?>
								</td>
                                <td>
                                	<?php
										$this_student = $this->db->get_where('students', array('student_id'=>$payment['custom_student_id']))->result_array();
										echo @$this_student[0]['roll_no'].'<hr />'.@$this_student[0]['first_name'].' '.@$this_student[0]['last_name'].'<hr />'.@$this_student[0]['cnic'].'<hr />'.@$this_student[0]['mobile'].'<hr />'.$payment['payment_comment'];
									?>
                                </td>
                                <td>
                                	<?php 
										if($payment['paid_date']=='0000-00-00')
										{
											echo '<div class="input-group input-medium date date-picker" data-date="'.date("Y-m-d").'" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                        <input type="text" name="paid_date" class="form-control" value="'.date("Y-m-d").'" readonly>
                                                        <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                                    </div>';
										}
										else
										{
											echo $payment['paid_date'];
										}
									?>
								</td>
                                <td>
                                	<?php if($payment['paid']==0){echo '<button class="btn red">Unpaid</button>';}else{echo '<button class="btn green">Paid</button>';}?>
								</td>
                                <td style="width:300px;">
                                	<?php
                                    	if($payment['paid']==0):
									?>
                                    <input type="hidden" name="fee_amount" value="<?php echo $payment['amount']+$payment['extra_amount'];?>" />
                                    <input type="hidden" name="id" value="<?php echo $payment['id'];?>" />
                                    <input type="hidden" name="dead_line" value="<?php echo $payment['dead_line'];?>" />
                                    <input type="hidden" name="payment_plan" value="<?php echo $payment['payment_plan'];?>" />
                                    <input type="hidden" name="college_fee" class="hidden_college_fee" value="0" />
                                    <button type="submit" class="btn green"><i class="fa fa-check"></i> Paid</button>
                                    <a href="<?php echo site_url().'/students/print_contractor_challan/'.$payment['id'];?>" target="_blank" class="btn purple bank_fee_<?php echo $i;?>"><i class="fa fa-print"></i> Bank Challan</a>
                                    <a href="<?php echo site_url().'/students/print_contractor_college_challan/'.$payment['id'];?>" target="_blank" class="btn blue college_fee_<?php echo $i;?>" style="display:none;"><i class="fa fa-print"></i> College Challan</a>
                                    <?php
                                    	endif;
									?>
                                    <?php
                                    	if($this->session->userdata('role')=='Admin')
										{
											echo '<a href="'.site_url().'/students/edit_payment/'.$payment['id'].'" class="btn yellow"><i class="fa fa-edit"></i> Edit</a>';
											echo '<a href="'.site_url().'/students/delete_contractor_payment/'.$payment['id'].'/'.$this->uri->segment(3).'" class="btn red" onclick="return confirm(\'Are you sure you want to delete this Transaction?\')"><i class="fa fa-trash"></i> Delete</a>';
										}
									?>
								</td>
                                </form>
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
            
            <div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-plus"></i> Add Extra Fee
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/contractors/add_extra_fee/<?php echo $this->uri->segment(3);?>" enctype="multipart/form-data">
								<div class="form-body">
									<div class="row">
                                    	<div class="col-md-12">
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                            	<label class="col-md-4 control-label">Extra Fee</label>
                                            	<div class="col-md-8">
                                            		<input type="text" class="form-control input-inline input-medium" name="extra_fee" value="" required/>
                                            	</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                            	<label class="col-md-4 control-label">Dead Line</label>
                                            	<div class="col-md-8">
                                            		<div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d');?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                            			<input type="text" name="extra_fee_dead_line" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
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
											<button type="submit" class="btn green">Add Extra Fee</button>
											<button onclick="location.href = '<?php echo site_url()?>/students/all_students'" type="button" class="btn default">Cancel</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
            
            <div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-plus"></i> Add Consultation Fee
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/contractors/add_extra_consulation_fee/<?php echo $this->uri->segment(3);?>" enctype="multipart/form-data">
								<div class="form-body">
									<div class="row">
                                    	<div class="col-md-12">
                                        	<h2>Punjab Consulation Fee</h2>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                            	<label class="col-md-4 control-label">Extra Year Consulation Fee</label>
                                            	<div class="col-md-8">
                                            		<input type="text" class="form-control input-inline input-medium" name="consulation_fee_1" value="" required/>
                                            	</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                            	<label class="col-md-4 control-label">Dead Line</label>
                                            	<div class="col-md-8">
                                            		<div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d');?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                            			<input type="text" name="consulation_dead_line_1" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
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
											<button type="submit" class="btn green">Add Consultation Fee</button>
											<button onclick="location.href = '<?php echo site_url()?>/students/all_students'" type="button" class="btn default">Cancel</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
		</div>
	</div>
	<!-- END CONTENT -->