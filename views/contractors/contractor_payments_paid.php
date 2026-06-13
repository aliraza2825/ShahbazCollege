<style>
	.btn{
		margin-bottom:10px;
	}

    .break-word {
        word-wrap: break-word;
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


                <div class="header" id="myHeader" style="margin-bottom:10px; z-index: 100">

                        <button type="button" class="btn green pay_payment" data-button-number="" onclick="getselected();" data-toggle="modal" href="#payfee"><i class="fa fa-cloud-upload"></i> Pay Installment</button>
                        <button type="button" class="btn purple bank_fee" data-button-number="" onclick="openchallan();" <i class="fa fa-print"></i> Bank Challan</button>

                </div>


				<div class="col-md-12 ">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-money"></i> Payment Plan &nbsp;&nbsp;&nbsp;&nbsp; Contractor Name : <?php echo $contract[0]['name']; ?> &nbsp;&nbsp;&nbsp;&nbsp; Contract Name : <?php echo $contract[0]['contract_name']; ?>
							</div>
						</div>
						<div class="portlet-body" id="checkboxes">
							<table  class="table table-striped table-bordered table-hover" id="sample_3">
							<thead>
							<tr>
                                <th>
									 Sr #
								</th>
                                <th>
									 Payment Details &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								</th>
								<th>
									 Dead Line
								</th>
                                <th>
									 Paid Status &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								</th>
                                <th style="max-width: 200px">
                                	Fee Edit Comments
                                </th>
                                <th style="max-width: 200px" >
                                	Fee Due Comments
                                </th>
								<th style="width:300px;">
                                	Action
                                </th>
							</tr>
							</thead>
                                <tbody>
                                <?php
                                $i=0;
                                $showed=false;
                                foreach($payments as $payment):
                                    ?>
                                    <tr class="odd gradeX" >
                                        <td>
                                            <?php echo $i+1;  ?>
                                            <?php if($payment['paid']==0): ?>
                                                <input type="checkbox"  class="selection" id="check_<?php echo $i ?>" name="selection" value="<?php echo $i;?>" />
                                            <?php   endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $totalpayable=0;
                                            $totalfine=0;
                                            if ($payment['merged_challan'] != null && $payment['actual_amount'] > 0){
                                                $payment_ids = rtrim($payment['paid_challans'], ", ");

                                                $this->db->select('payments.*, students.first_name as first_name, students.last_name as last_name, students.roll_no as roll_no, students.father_name as father_name, classes.name as class_name, campuses.bank_name, campuses.account_no, campuses.address, campuses.campus_name, campuses.note, campuses.campus_id');
                                                $this->db->from('payments');
                                                $this->db->join('students', 'payments.custom_student_id=students.student_id or payments.custom_student_id=students.student_id', 'inner');
                                                $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
                                                $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
                                                $mergchalans=$this->db->where_in('payments.challan_no', explode(',', $payment_ids))->get()->result_array();

                                                foreach ($mergchalans as $merg){
                                                    $totalpayable+=$merg['amount'];
                                                    $challan_date = date_create($merg['dead_line']);
                                                    $paid_date = date_create($merg['paid_date']);
                                                    $diff=date_diff($challan_date,$paid_date);
                                                    $difference = $diff->format("%R%a");
                                                    if($difference>0) {
                                                        $totalfine += $difference*50;
                                                    } ?>
													<strong>Student Name : <?php echo @$merg['first_name'].' '.@$merg['last_name'];?></strong>
													<br />
													<?php echo $merg['payment_comment'] ?>
													<br />
													<strong>Student Roll # : <?php echo $merg['roll_no']?></strong>
                                                    <br />
                                                    Merged Challan # : <?php echo $merg['challan_no'];?>   <?php
                                                    ?> <br />
                                                    <strong>Merged Amount : <?php echo $merg['amount'];?> </strong>
                                                    <?php
                                                }
                                            }
                                            else {

                                                $this->db->select('payments.*, students.first_name as first_name, students.last_name as last_name, students.roll_no as roll_no, students.father_name as father_name, classes.name as class_name, campuses.bank_name, campuses.account_no, campuses.address, campuses.campus_name, campuses.note, campuses.campus_id');
                                                $this->db->from('payments');
                                                $this->db->join('students', 'payments.student_id=students.student_id or payments.custom_student_id=students.student_id', 'inner');
                                                $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
                                                $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
                                                $paeys=$this->db->where('payments.challan_no',$payment['challan_no'])->get()->result_array();


                                                $totalpayable=$payment['amount'];
                                                if ($paeys){
                                                ?>
                                                <strong>Student Name : <?php echo @$paeys[0]['first_name'].' '.@$paeys[0]['last_name'];?></strong>

                                                <br />
                                                <strong>Student Roll # : <?php echo $paeys[0]['roll_no']?></strong>
                                                <br />
                                                <strong>Installment Amount : <?php echo $payment['amount'];?></strong>
												<br />
                                            <?php
                                                }
                                            }?>
                                            Challan # : <?php echo $payment['challan_no'];?>   <?php echo $payment['payment_comment']; ?>
                                            <br />
                                            Discount : <?php echo $payment['discount'];?>
                                            <br />
                                            Previous Installment Amount : <?php echo $payment['remaining_installment_amount'];?>
                                            <br />
                                            Previous Fine Amount : <?php echo $payment['extra_amount'];?>
                                            <br />
                                            <hr />
                                            Installment Status : <?php if($payment['paid']==1){echo 'Paid';}else{echo 'Unpaid';}?>
                                            <br />
                                            <?php
                                            if($payment['paid']==1):
                                                $challan_date = date_create($payment['dead_line']);
                                                $paid_date = date_create($payment['paid_date']);
                                                $diff=date_diff($challan_date,$paid_date);
                                                $difference = $diff->format("%R%a");

                                                if($difference>0)
                                                {
                                                    if($payment['payment_plan']=='24 Installments')
                                                    {
                                                        $fee_fine = $difference*10;
                                                    }
                                                    else
                                                    {
                                                        $fee_fine = $difference*50;
                                                    }
                                                }
                                                else
                                                {
                                                    $fee_fine = 0;
                                                }

                                                echo 'Late Fee Fine : '.$totalfine.'<br />';
                                                echo 'Removed Fine : '.$payment['removed_fine'].'<br />';

                                                echo '<strong>Payable Amount : '.($totalpayable+$payment['remaining_installment_amount']+$payment['extra_amount']+$totalfine).'</strong><br />';
                                                echo '<strong>Paid Amount : '.$payment['actual_amount'].'</strong><br />';
                                            endif;
                                            ?>
                                            <?php
                                            if($payment['paid']==0):
                                                $challan_date = date_create($payment['dead_line']);
                                                $today_date = date_create(date('Y-m-d'));
                                                $diff=date_diff($challan_date,$today_date);
                                                $difference = $diff->format("%R%a");

                                                if($difference>0)
                                                {
                                                    if($payment['payment_plan']=='24 Installments')
                                                    {
                                                        $fee_fine = $difference*10;
                                                    }
                                                    else
                                                    {
                                                        $fee_fine = $difference*50;
                                                    }
                                                }
                                                else
                                                {
                                                    $fee_fine = 0;
                                                }
                                                if($difference>0)
                                                {
                                                    echo 'Late Fee Days : '.str_replace('+','',$difference).'<br />';
                                                }
                                                echo 'Late Fee Amount : '.$fee_fine.'<br />';
                                                echo '<strong>Payable Amount : '.($payment['amount']+$payment['remaining_installment_amount']+$payment['extra_amount']+$fee_fine).'</strong>';
                                            endif;
                                            ?>
                                        </td>
                                        <td>
                                            <?php echo $payment['dead_line'];?>
                                        </td>
                                        <td>
                                            <?php
                                            if($payment['paid']==1):
                                                ?>
                                                Paid Amount : <?php echo $payment['actual_amount'];?>
                                                <br />
                                                <?php
                                                if($payment['shifted_installment']>0):
                                                    ?>
                                                    Shifted Previous Installment Amount : <?php echo $payment['shifted_installment'];?>
                                                    <br />
                                                <?php
                                                endif;
                                                ?>
                                                <?php
                                                if($payment['shifted_previous_fine']>0):
                                                    ?>
                                                    Shifted Previous Installment Fine : <?php echo $payment['shifted_previous_fine'];?>
                                                    <br />
                                                <?php
                                                endif;
                                                ?>
                                                <?php
                                                if($payment['shifted_fine']>0):
                                                    ?>
                                                    Shifted Current Installment Fine : <?php echo $payment['shifted_fine'];?>
                                                    <br />
                                                <?php
                                                endif;
                                                ?>
                                                <?php
                                                if($payment['removed_previous_fine']>0):
                                                    ?>
                                                    Removed Previous Installment Fine : <?php echo $payment['removed_previous_fine'];?>
                                                    <br />
                                                <?php
                                                endif;
                                                ?>
                                                <?php
                                                if($payment['removed_fine']>0):
                                                    ?>
                                                    Removed Current Installment Fine : <?php echo $payment['removed_fine'];?>
                                                    <br />
                                                <?php
                                                endif;
                                                ?>
                                                Paid Date : <?php echo $payment['paid_date'];?>
                                                <br />
                                                Paid Date System : <?php echo $payment['updated_at'];?>
                                                <br />
                                                Fee Pay Through : <?php echo $payment['fee_pay_through'];?>
                                                <br />
                                                <?php
                                                if($payment['fee_pay_through']=='bank'):
                                                    ?>
                                                    Bank : <?php echo $payment['bank_details'];?>
                                                    <br />
                                                    Bank Challan / TID No. : <?php echo $payment['tid_no'];?>
                                                    <br />

                                                    Merged against Challan. : <?php echo $payment['paid_challans'];?>
                                                <?php
                                                endif;
                                                ?>
                                                <?php
                                                if($payment['fee_pay_through']=='college' && $payment['fee_submit_type']=='receipt_book'):
                                                    ?>
                                                    Pad of : <?php echo @$this->db->get_where('campuses',array('campus_id'=>$payment['submitted_fee_campus_id']))->row()->campus_name;?>
                                                    <br />
                                                    Book No. : <?php echo $payment['book_no'];?>
                                                    <br />
                                                    Receipt No. : <?php echo $payment['receipt_no'];?>
                                                    <br />
                                                    Merged against Challan. : <?php echo $payment['paid_challans'];?>
                                                <?php
                                                endif;
                                                ?>
                                                <?php
                                                if($payment['fee_pay_through']=='college' && $payment['fee_submit_type']=='computer_challan'):
                                                    ?>
                                                    Pay by : Computer Challan
                                                    <br />
                                                    Merged against Challan. : <?php echo $payment['paid_challans'];?>
                                                <?php
                                                endif;
                                                ?>
                                                <div class="clearfix"></div>
                                                <br />
                                                <?php
                                                if($payment['scan_challan']=='')
                                                {

                                                }
                                                elseif($payment['scan_challan']!='' )
                                                {
                                                    if($payment['online_scan_challan']=='')
                                                    {
                                                        echo '<a href="'.base_url().'uploads/'.$payment['scan_challan'].'" target="_blank" class="btn purple pull-left">See Challan</a> <br />';
                                                    }
                                                    else
                                                    {
                                                        echo '<a href="'.str_replace($bucket_address,$cloudfront_address,$payment['online_scan_challan']).'" target="_blank" class="btn purple pull-left">See Challan</a> <br />';
                                                    }
                                                }

                                                if($payment['fee_pay_through']=='college' && $payment['fee_submit_type']=='computer_challan')
                                                {

                                                    echo '<a href="'.site_url().'/students/print_contractor_college_challan/'.$payment['id'].'" target="_blank" class="btn blue college_fee_'.$i.'"><i class="fa fa-print"></i> See Challan</a> <br />';
                                                }
                                                ?>
                                                <?php
                                                if($payment['fine_application']=='' && $payment['paid']==0)
                                                {

                                                }
                                                else if($payment['fine_application']!='' && $payment['paid']==1)
                                                {
                                                    if($payment['online_fine_application']=='')
                                                    {
                                                        echo '<a href="'.base_url().'uploads/'.$payment['fine_application'].'" target="_blank" class="btn purple pull-left">See Application</a> <br />';
                                                    }
                                                    else
                                                    {
                                                        echo '<a href="'.str_replace($bucket_address,$cloudfront_address,$payment['fine_application']).'" target="_blank" class="btn purple pull-left">See Application</a> <br />';
                                                    }
                                                }
                                                else
                                                {

                                                }
                                                ?>
                                                <div class="clearfix"></div>
                                            <?php
                                            endif;
                                            ?>
                                        </td>
                                        <td class="break-word" style="max-width: 200px;"  >
                                            <?php
                                            $fee_remarks = $this->db->get_where('fees_remarks', array('fee_id'=>$payment['id']))->result_array();
                                            foreach($fee_remarks as $fee_remark)
                                            {
                                                echo $fee_remark['comment'];
                                                echo '<br />';
                                                echo 'Add By : '.$fee_remark['add_by'].'<br />';
                                                echo 'Comment Date : '.$fee_remark['date'].'<br />';
                                                echo 'Next Date : '.$fee_remark['paid_on_date'];
                                                echo '<hr />';
                                            }
                                            ?>
                                            <br />

                                            <?php
                                            $update_requests = $this->db->get_where('update_payment_requests', array('id'=>$payment['id']))->result_array();
                                            foreach($update_requests as $update_request)
                                            {
                                                if($update_request['ok_by_admin']==1)
                                                {
                                                    $status = '<span class="alert-success">Approved</span>';
                                                }
                                                else
                                                {
                                                    $status = '<span class="alert-danger">Pending</span>';
                                                }
                                                echo 'Date : '.date('F d,Y',strtotime($update_request['update_date'])).'<br />';
                                                echo 'Reason : '.$update_request['reason'].'<br />';
                                                echo 'Clear By : '.$update_request['clear_by'].'<br />';
                                                echo 'Status : '.$status;
                                                echo '<hr />';
                                            }
                                            ?>
                                        </td>
                                        <td class="break-word" style="max-width: 200px;" >
                                            <?php echo $payment['system_comment'];?>

                                            <br />
                                            <?php
                                            $remarks = $this->db->get_where('fees_remarks', array('fee_id'=>$payment['id']))->result_array();
                                            foreach($remarks as $remark)
                                            {
                                                echo 'Paid on Date : '.$remark['paid_on_date'].'<br />';
                                                echo 'Remarks : '.$remark['comment'].'<br />';
                                                echo 'Contact By : '.$remark['add_by'];
                                                echo '<hr />';
                                            }
                                            ?>
                                        </td>
                                        <td>

                                                <?php
                                                if($payment['paid']==0 ):
                                                    ?>
                                                    <?php
                                                    if($payment['payment_plan']!='consulation fee'):
                                                        ?>
                                                        <?php
                                                        if($payment['split']<1):
                                                            ?>
                                                            <button type="button" class="btn red" data-toggle="modal" href="#split_fee_<?php echo $i;?>"><i class="fa fa-code"></i> Split Installment</button>
                                                        <?php
                                                        endif;
                                                        ?>
                                                        <div id="split_fee_<?php echo $i;?>" class="modal fade" tabindex="-1" data-width="760">
                                                            <div class="modal-header">
                                                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                                                                <h4 class="modal-title">Split Installment</h4>
                                                            </div>
                                                            <div class="modal-body">
                                                                <form action="<?php echo site_url();?>/students/split_payment/<?php echo $this->uri->segment(3);?>" method="post">
                                                                    <div class="row">
                                                                        <div class="alert alert-danger">
                                                                            <p class="text-center">You can split maximum 50% of fee amount. You can create new installment within the date of one month.</p>
                                                                        </div>
                                                                        <div class="col-md-6" style="border-right:1px dotted #CCC;">
                                                                            <h2 class="text-center">Current Installment</h2>
                                                                            <br />
                                                                            <div class="form-group">
                                                                                <label class="col-md-4 control-label">Installment Amount <span class="required">*</span></label>
                                                                                <div class="col-md-8">
                                                                                    <input type="number" min="<?php echo $payment['amount']/2;?>" max="<?php echo $payment['amount'];?>" class="form-control input-inline input-small current_amount current_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" data-value="<?php echo $payment['amount'];?>"  name="current_amount" placeholder="Enter student installment amount" value="<?php echo $payment['amount'];?>" required>
                                                                                    <span class="help-inline"></span>
                                                                                </div>
                                                                                <div class="clearfix"></div>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label class="col-md-4 control-label">Previous Installment Amount <span class="required">*</span></label>
                                                                                <div class="col-md-8">
                                                                                    <input type="number" min="<?php echo $payment['remaining_installment_amount']/2;?>" max="<?php echo $payment['remaining_installment_amount'];?>" class="form-control input-inline input-small current_remaining_installment_amount current_remaining_installment_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" data-value="<?php echo $payment['remaining_installment_amount'];?>" name="current_remaining_installment_amount" placeholder="Enter student installment amount" value="<?php echo $payment['remaining_installment_amount'];?>" required>
                                                                                    <span class="help-inline"></span>
                                                                                </div>
                                                                                <div class="clearfix"></div>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label class="col-md-4 control-label">Previous Fine Amount <span class="required">*</span></label>
                                                                                <div class="col-md-8">
                                                                                    <input type="number" min="<?php echo $payment['extra_amount']/2;?>" max="<?php echo $payment['extra_amount'];?>" class="form-control input-inline input-small current_extra_amount current_extra_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" data-value="<?php echo $payment['extra_amount'];?>" name="current_extra_amount" placeholder="Enter student fine amount" value="<?php echo $payment['extra_amount'];?>" required>
                                                                                    <span class="help-inline"></span>
                                                                                </div>
                                                                                <div class="clearfix"></div>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label class="col-md-4 control-label">Dead Line <span class="required">*</span></label>
                                                                                <div class="col-md-8">
                                                                                    <?php echo $payment['dead_line']?>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <h2 class="text-center">New Installment</h2>
                                                                            <br />
                                                                            <div class="form-group">
                                                                                <label class="col-md-4 control-label">Installment Amount <span class="required">*</span></label>
                                                                                <div class="col-md-8">
                                                                                    <input type="number" max="<?php echo $payment['amount']/2;?>" class="form-control input-inline input-small new_amount new_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" name="new_amount" placeholder="Enter student installment amount" value="0" readonly required />
                                                                                    <span class="help-inline"></span>
                                                                                </div>
                                                                                <div class="clearfix"></div>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label class="col-md-4 control-label">Previous Installment Amount <span class="required">*</span></label>
                                                                                <div class="col-md-8">
                                                                                    <input type="number" max="<?php echo $payment['remaining_installment_amount']/2;?>" class="form-control input-inline input-small new_remaining_installment_amount new_remaining_installment_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" name="new_remaining_installment_amount" placeholder="Enter student installment amount" value="0" readonly required />
                                                                                    <span class="help-inline"></span>
                                                                                </div>
                                                                                <div class="clearfix"></div>
                                                                            </div>




                                                                            <div class="form-group">
                                                                                <label class="col-md-4 control-label">Previous Fine Amount <span class="required">*</span></label>
                                                                                <div class="col-md-8">
                                                                                    <input type="number" max="<?php echo $payment['extra_amount']/2;?>" class="form-control input-inline input-small new_extra_amount new_extra_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" name="new_extra_amount" placeholder="Enter student fine amount" value="0" readonly required>
                                                                                    <span class="help-inline"></span>
                                                                                </div>
                                                                                <div class="clearfix"></div>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label class="control-label col-md-4">Dead Line <span class="required">*</span></label>
                                                                                <?php
                                                                                $payment_dead_line=date_create($payment['dead_line']);
                                                                                $today_date=date_create(date('Y-m-d'));
                                                                                $diff=date_diff($today_date,$payment_dead_line);
                                                                                if($diff->invert==1)
                                                                                {
                                                                                    $days='-'.$diff->days;
                                                                                }
                                                                                else
                                                                                {
                                                                                    $days='+'.$diff->days;
                                                                                }
                                                                                $start_days = $days;
                                                                                $end_days = $days+32;
                                                                                if($end_days>=0)
                                                                                {
                                                                                    $end_days='+'.$end_days;
                                                                                }
                                                                                else
                                                                                {
                                                                                    $end_days=$end_days;
                                                                                }
                                                                                ?>
                                                                                <div class="col-md-8">
                                                                                    <div class="input-group input-small date date-picker" data-date="<?php echo $payment['dead_line']?>" data-date-format="yyyy-mm-dd" data-date-start-date="<?php echo $start_days;?>d" data-date-end-date="<?php echo $end_days;?>d" data-date-viewmode="years">
                                                                                        <input type="text" name="new_dead_line" class="form-control" value="<?php echo $payment['dead_line']?>" readonly>
                                                                                        <span class="input-group-btn">
                                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                                    </span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-12 text-center">
                                                                            <input type="hidden" name="current_dead_line" value="<?php echo $payment['dead_line']?>">
                                                                            <input type="hidden" name="current_id" value="<?php echo $payment['id']?>">

                                                                            <button type="submit" class="btn red"><i class="fa fa-code"></i> Split Installment</button>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" data-dismiss="modal" class="btn btn-default">Close</button>
                                                            </div>
                                                        </div>
                                                    <?php
                                                    endif;
                                                    ?>
                                                    <div id="responsive<?php echo $i;?>" class="modal fade" tabindex="-1" data-width="760">
                                                        <div class="modal-header">
                                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                                                            <h4 class="modal-title">Fee Submission</h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <strong>Challan #</strong> : <?php echo $payment['challan_no'];?>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <strong>Installment Amount</strong> : <?php echo $payment['amount'];?>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <strong>Dead Line</strong> : <?php echo $payment['dead_line'];?>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <strong>Fee Type</strong> : <?php echo $payment['payment_comment'];?>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <strong>Previous Installment Amount</strong> : <?php echo $payment['remaining_installment_amount'];?>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <strong>Previous Fine Amount</strong> : <?php echo $payment['extra_amount'];?>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <strong>Late Fee Amount</strong> : <?php echo $fee_fine;?>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <strong>Late Fee Days</strong> : <?php echo str_replace('+','',$difference).' Days';?>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <strong>Payable Amount</strong> : <?php echo $payable_amount = ($payment['amount']+$payment['remaining_installment_amount']+$payment['extra_amount']+$fee_fine);?>
                                                                </div>

                                                                <div class="clearfix"></div>
                                                                <br />
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <form action="<?php echo site_url().'/students/paid_payment_action/'.$this->uri->segment(3);?>" method="post" enctype="multipart/form-data">
                                                                        <div class="form-group">
                                                                            <label class="col-md-4 control-label">Fee Pay Through <span class="required">*</span></label>
                                                                            <div class="col-md-8 radio-list">
                                                                                <label class="radio-inline">
                                                                                    <input type="radio" class="submit_in" name="fee_pay_through" id="optionsRadios5" value="bank" checked /> Bank </label>
                                                                                <label class="radio-inline">
                                                                                    <input type="radio"  class="submit_in" name="fee_pay_through" id="optionsRadios4" value="college" /> College </label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="clearfix"></div>
                                                                        <br />
                                                                        <div class="bank">
                                                                            <div class="form-group">
                                                                                <label class="col-md-4 control-label">Select Bank <span class="required">*</span></label>
                                                                                <div class="col-md-8">
                                                                                    <select class="form-control bank_details" name="bank_details" required>
                                                                                        <option value="">SELECT BANK</option>
                                                                                        <?php
                                                                                        foreach($account_numbers as $bank_names):
                                                                                            ?>
                                                                                            <option value="<?php echo $bank_names['account_name'].'';?>"><?php echo $bank_names['account_name'].'';?></option>
                                                                                        <?php
                                                                                        endforeach;
                                                                                        ?>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                            <br />
                                                                            <div class="form-group">
                                                                                <label class="col-md-4 control-label">Bank Challan / TID No.</label>
                                                                                <div class="col-md-8">
                                                                                    <input type="text" class="form-control input-inline input-medium" name="tid_no" placeholder="Enter TID Number" value="" >
                                                                                    <span class="help-inline"></span>
                                                                                </div>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>
                                                                        <div class="clearfix"></div>
                                                                        <div class="form-group college" style="display:none;">
                                                                            <label class="col-md-4 control-label">Fee Submit Type <span class="required">*</span></label>
                                                                            <div class="col-md-8 radio-list">
                                                                                <label class="radio-inline">
                                                                                    <input type="radio" class="pay_by" name="fee_submit_type" id="optionsRadios5" value="computer_challan" checked /> Pay By Computer Challan </label>

                                                                                <label class="radio-inline">
                                                                                    <input type="radio" class="pay_by" name="fee_submit_type" id="optionsRadios4" value="receipt_book" /> Pay By Receipt Book </label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="clearfix"></div>
                                                                        <div class="receipt_book" style="display:none;">
                                                                            <div class="clearfix"></div>
                                                                            <br />
                                                                            <div class="form-group">
                                                                                <label class="col-md-4 control-label">Pad of <span class="required">*</span></label>
                                                                                <div class="col-md-8">
                                                                                    <select class="form-control submitted_fee_campus_id" name="submitted_fee_campus_id">
                                                                                        <option value="">SELECT CAMPUS</option>
                                                                                        <?php
                                                                                        foreach($campuses as $campus):
                                                                                            ?>
                                                                                            <option value="<?php echo $campus['campus_id'];?>" <?php if($campus['campus_id']==$this->session->userdata('user_campus_id')){echo 'selected';}?>><?php echo $campus['campus_name'];?></option>
                                                                                        <?php
                                                                                        endforeach;
                                                                                        ?>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                            <br />
                                                                            <div class="form-group">
                                                                                <label class="col-md-4 control-label">Book No. <span class="required">*</span></label>
                                                                                <div class="col-md-8">
                                                                                    <input type="number" min="1" class="form-control input-inline input-medium book_no" name="book_no" placeholder="Enter Receipt Book Number" value="">
                                                                                    <span class="help-inline"></span>
                                                                                </div>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                            <br />
                                                                            <div class="form-group">
                                                                                <label class="col-md-4 control-label">Receipt No. <span class="required">*</span></label>
                                                                                <div class="col-md-8">
                                                                                    <input type="number" min="1" class="form-control input-inline input-medium receipt_no" name="receipt_no" placeholder="Enter Receipt Number" value="">
                                                                                    <span class="help-inline"></span>
                                                                                </div>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>
                                                                        <div class="clearfix"></div>
                                                                        <br />
                                                                        <div class="form-group challan">
                                                                            <label class="col-md-4 control-label">Challan Image <span class="required">*</span></label>
                                                                            <div class="col-md-8">
                                                                                <input class="scan_challan" type="file" name="scan_challan" value="" required />
                                                                                <span class="help-inline"></span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="clearfix"></div>
                                                                        <div class="form-group">
                                                                            <label class="col-md-4 control-label">Application Image</label>
                                                                            <div class="col-md-8">
                                                                                <input type="file" class="application" name="fine_application" value="" />
                                                                                <span class="help-inline"></span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="clearfix"></div>
                                                                        <div class="form-group">
                                                                            <label class="col-md-4 control-label">Paid Date <span class="required">*</span></label>
                                                                            <div class="col-md-8">
                                                                                <!-- CLASS date date-picker-->
                                                                                <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d');?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                                                    <input type="text" name="paid_date" data-dead-line="<?php echo $payment['dead_line'];?>" class="form-control paid_date" value="<?php echo date('Y-m-d');?>" required readonly>
                                                                                    <span class="input-group-btn">
                                                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label class="col-md-4 control-label">Installment Amount <span class="required">*</span></label>
                                                                            <div class="col-md-8">
                                                                                <input type="number" min="1" class="form-control input-inline input-medium amount amount_<?php echo $i;?>" data-number="<?php echo $i;?>" name="amount" placeholder="Enter student installment amount" value="<?php echo $payment['amount'];?>" readonly required>
                                                                                <span class="help-inline"></span>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label class="col-md-4 control-label">Previous Installment Amount <span class="required">*</span></label>
                                                                            <div class="col-md-8">
                                                                                <input type="number" min="0" max="<?php echo $payment['remaining_installment_amount'];?>" class="form-control input-inline input-medium remaining_installment_amount remaining_installment_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" data-value="<?php echo $payment['remaining_installment_amount'];?>" name="remaining_installment_amount" placeholder="Enter student previous installment amount" value="<?php echo $payment['remaining_installment_amount'];?>" readonly required>
                                                                                <?php
                                                                                if($payment['remaining_installment_amount']>0):
                                                                                    ?>
                                                                                    <label class="checkbox-inline">
                                                                                        <input type="checkbox" id="inlineCheckbox1" data-number="<?php echo $i;?>" class="remove_split_remaining_installment_amount remove_split_remaining_installment_amount_<?php echo $i;?>" name="split_remaining_installment_amount" value="1" /> Edit </label>
                                                                                <?php
                                                                                endif;
                                                                                ?>
                                                                                <span class="help-inline"></span>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>

                                                                        <div class="form-group remove_remaining_installment_amount_action_<?php echo $i;?>" style="display:none;">
                                                                            <label class="col-md-4 control-label"> Amount <span class="required">*</span></label>
                                                                            <div class="col-md-8">
                                                                                <input type="number" min="0" class="form-control input-inline input-medium remove_remaining_installment_amount remove_remaining_installment_amount_<?php echo $i;?>" name="remove_remaining_installment_amount" placeholder="" value="0" readonly required>
                                                                                <span class="help-inline"></span>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>
                                                                        <div class="form-group remove_remaining_installment_amount_action_<?php echo $i;?>" style="display:none;">
                                                                            <label class="col-md-4 control-label">Action <span class="required">*</span></label>
                                                                            <div class="col-md-8 radio-list">
                                                                                <label class="radio-inline">
                                                                                    <input type="radio"  class="prev_installment_status" name="prev_installment_status" data-number="<?php echo $i;?>" id="optionsRadios3" value="shift" /> Shift </label>
                                                                                <label class="radio-inline">
                                                                                    <input type="radio"  class="prev_installment_status" name="prev_installment_status" data-number="<?php echo $i;?>" id="optionsRadios3" value="new" /> New Installment </label>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label class="col-md-4 control-label">Previous Fine Amount <span class="required">*</span></label>
                                                                            <div class="col-md-8">
                                                                                <input type="number" min="0" max="<?php echo $payment['extra_amount'];?>" class="form-control input-inline input-medium previous_extra_amount previous_extra_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" data-value="<?php echo $payment['extra_amount'];?>" name="extra_amount" placeholder="Enter student previous fine amount" value="<?php echo $payment['extra_amount'];?>" readonly required>
                                                                                <?php
                                                                                if($payment['extra_amount']>0):
                                                                                    ?>
                                                                                    <label class="checkbox-inline">
                                                                                        <input type="checkbox" id="inlineCheckbox1" data-number="<?php echo $i;?>" class="remove_split_previous_fine_amount remove_split_previous_fine_amount_<?php echo $i;?>" name="split_remaining_fine_amount" value="1" /> Edit </label>
                                                                                <?php
                                                                                endif;
                                                                                ?>
                                                                                <span class="help-inline"></span>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>
                                                                        <div class="form-group remove_split_previous_fine_amount_action_<?php echo $i;?>" style="display:none;">
                                                                            <label class="col-md-4 control-label"> Amount <span class="required">*</span></label>
                                                                            <div class="col-md-8">
                                                                                <input type="number" min="0" class="form-control input-inline input-medium remove_previous_fine_amount remove_previous_fine_amount_<?php echo $i;?>" name="remove_previous_fine_amount" placeholder="" value="0" readonly required>
                                                                                <span class="help-inline"></span>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>
                                                                        <div class="form-group remove_split_previous_fine_amount_action_<?php echo $i;?>" style="display:none;">
                                                                            <label class="col-md-4 control-label">Action <span class="required">*</span></label>
                                                                            <div class="col-md-8 radio-list">
                                                                                <label class="radio-inline">
                                                                                    <input type="radio"  class="prev_fine_status" name="prev_fine_status" data-number="<?php echo $i;?>" id="optionsRadios2" value="remove" /> Remove </label>
                                                                                <label class="radio-inline">
                                                                                    <input type="radio"  class="prev_fine_status" name="prev_fine_status" data-number="<?php echo $i;?>" id="optionsRadios3" value="shift" /> Shift </label>
                                                                                <label class="radio-inline">
                                                                                    <input type="radio"  class="prev_fine_status" name="prev_fine_status" data-number="<?php echo $i;?>" id="optionsRadios4" value="new" /> New Installment </label>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label class="col-md-4 control-label">Late Fee Fine Amount <span class="required">*</span></label>
                                                                            <div class="col-md-8">
                                                                                <input type="number" min="0" max="<?php echo $fee_fine;?>" class="form-control input-inline input-medium fine_amount fine_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" data-value="<?php echo $fee_fine;?>" name="fine_amount" placeholder="Enter student previous fine amount" value="<?php echo $fee_fine;?>" readonly required>
                                                                                <?php
                                                                                if($fee_fine>0):
                                                                                    ?>
                                                                                    <label class="checkbox-inline">
                                                                                        <input type="checkbox" id="inlineCheckbox1" data-number="<?php echo $i;?>" class="remove_split_fine_amount remove_split_fine_amount_<?php echo $i;?>" name="split_fine_amount" value="1" /> Edit </label>
                                                                                <?php
                                                                                endif;
                                                                                ?>
                                                                                <span class="help-inline"></span>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>

                                                                        <div class="form-group remove_split_fine_amount_action_<?php echo $i;?>" style="display:none;">
                                                                            <label class="col-md-4 control-label"> Amount <span class="required">*</span></label>
                                                                            <div class="col-md-8">
                                                                                <input type="number" min="0" class="form-control input-inline input-medium remove_fine_amount remove_fine_amount_<?php echo $i;?>" name="remove_fine_amount"  placeholder="" value="0" readonly required>
                                                                                <span class="help-inline"></span>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>
                                                                        <div class="form-group remove_split_fine_amount_action_<?php echo $i;?>" style="display:none;">
                                                                            <label class="col-md-4 control-label">Action <span class="required">*</span></label>
                                                                            <div class="col-md-8 radio-list">
                                                                                <label class="radio-inline">
                                                                                    <input type="radio"  class="late_fee_fine_status" data-number="<?php echo $i;?>" name="late_fee_fine_status" id="optionsRadios2" value="remove" /> Remove </label>
                                                                                <label class="radio-inline">
                                                                                    <input type="radio"  class="late_fee_fine_status" data-number="<?php echo $i;?>" name="late_fee_fine_status" id="optionsRadios3" value="shift" /> Shift </label>
                                                                                <label class="radio-inline">
                                                                                    <input type="radio"  class="late_fee_fine_status" data-number="<?php echo $i;?>" name="late_fee_fine_status" id="optionsRadios4" value="new" /> New Installment </label>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label class="col-md-4 control-label">Paid Amount <span class="required">*</span></label>
                                                                            <div class="col-md-8">
                                                                                <input type="number" min="1" class="form-control input-inline input-medium actual_amount actual_amount_<?php echo $i?>" name="actual_amount" placeholder="Enter student paid amount" value="<?php echo $payable_amount;?>" readonly required>
                                                                                <span class="help-inline"></span>
                                                                            </div>
                                                                            <div class="clearfix"></div>
                                                                        </div>
                                                                        <div class="form-group new_installment_date_section" style="display:none;">
                                                                            <label class="col-md-4 control-label">New Installment Paid Date <span class="required">*</span></label>
                                                                            <div class="col-md-8">
                                                                                <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d');?>" data-date-format="yyyy-mm-dd" data-date-start-date="+0d" data-date-end-date="+30d" data-date-viewmode="years">
                                                                                    <input type="text" name="new_dead_line" class="form-control" value="<?php echo date('Y-m-d');?>" required readonly>
                                                                                    <span class="input-group-btn">
																	<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
																	</span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-4"></div>
                                                                        <div class="col-md-8">
                                                                            <br />
                                                                            <input type="hidden" name="last_edit" value="<?php echo $this->session->userdata('name');?>" />
                                                                            <input type="hidden" name="fee_amount" value="<?php echo $payment['amount']+$payment['extra_amount'];?>" />
                                                                            <input type="hidden" name="id" value="<?php echo $payment['id'];?>" />
                                                                            <input type="hidden" name="dead_line" value="<?php echo $payment['dead_line'];?>" />
                                                                            <input type="hidden" name="payment_plan" value="<?php echo $payment['payment_plan'];?>" />
                                                                            <input type="hidden" name="college_fee" class="hidden_college_fee" value="0" />
                                                                            <button class="btn green">Pay Fee</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                                <div class="clearfix"></div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" data-dismiss="modal" class="btn btn-default">Close</button>
                                                        </div>
                                                    </div>

                                                    <a href="<?php echo site_url().'/students/print_college_challan/'.$payment['id'];?>" target="_blank" class="btn blue college_fee_<?php echo $i;?>" style="display:none;"><i class="fa fa-print"></i> College Challan</a>
                                                <?php
                                                endif;
                                                ?>
                                                <?php
                                                if(@$myAccess[0]['student_payment_edit']==1 || $this->session->userdata('role')=='Admin')
                                                {
                                                    echo '<a href="'.site_url().'/students/edit_payment/'.$payment['id'].'/'.($i+1).'/1" class="btn yellow"><i class="fa fa-edit"></i> Edit</a>';
                                                }
                                                if( $this->session->userdata('role')=='Admin')
                                                {
                                                    echo '<a href="'.site_url().'/students/delete_payment/'.$payment['id'].'/'.$this->uri->segment(3).'" onclick="return confirm(\'Are you sure you want to delete this Transaction?\')" class="btn red"><i class="fa fa-trash"></i> Delete</a>';
                                                }
                                                ?>



                                            <br />
                                            <?php
                                            echo 'Fee Add By : '.$payment['add_by'].'<br />';
                                            echo 'Fee Last Edit : '.$payment['last_edit'].'<br />';
                                            if($payment['paid']==1)
                                            {
                                                echo 'Fee Submitted By : '.@$payment['paid_by'].'<br />';
                                                echo 'Fee Cleared By : '.@$payment['clear_by'];
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
                                        <div class="col-md-4">
                                            <div class="form-group">
                                            	<label class="col-md-4 control-label">Extra Fee</label>
                                            	<div class="col-md-8">
                                            		<input type="text" class="form-control input-inline input-medium" name="extra_fee" value="" required/>
                                            	</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
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
                                        <div class="col-md-4">
                                            <div class="form-group">
                                            	<label class="col-md-4 control-label">Purpose</label>
                                            	<div class="col-md-8">
                                            		<input type="text" class="form-control input-inline input-medium" name="payment_comment" value="" required/>
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
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Class <span class="required">*</span></label>
                                                <div class="col-md-8 radio-list">
                                                    <label class="radio-inline">
                                                        <input type="radio" name="class" id="optionsRadios4" value="1st Year" checked> First Year </label>
                                                    <label class="radio-inline">
                                                        <input type="radio" name="class" id="optionsRadios5" value="2nd Year"> Second Year </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 " >
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Council Exam No. <span class="required">*</span></label>
                                                <div class="col-md-8">
                                                    <input type="text" class="form-control input-inline input-medium exam_no" name="exam_no" value="" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                            	<label class="col-md-4 control-label">Purpose</label>
                                            	<div class="col-md-8">
                                            		<input type="text" class="form-control input-inline input-medium" name="payment_comment" value="consulation fee" required/>
                                            	</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                            	<label class="col-md-4 control-label">Select Student</label>
                                            	<div class="col-md-8">

                                                    <select class="form-control select2" name="student_id" id="select2_sample1" required>
                                                        <option value="">Select Student</option>
                                                        <?php foreach ($students as $student) {

                                                            echo "<option value='".$student['student_id']."'>".$student['first_name']." ".$student['last_name']. '  '.$student['roll_no']."</option>";

                                                        } ?>


                                                    </select>
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


<div id="payfee" class="modal fade" tabindex="-1" data-width="760">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Fee Submission</h4>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-4" style="word-wrap:break-word;">
                <strong>Challan #</strong> : <label id="chalans"></label>
            </div>
            <div class="col-md-4" style="word-wrap:break-word;">
                <strong>Installment Amount</strong> : <label id="amounts"></label>
            </div>
            <div class="col-md-4">
                <strong>Dead Line</strong> : <label id="deadlines"></label>
            </div>
            <div class="col-md-4">
                <strong>Fee Type</strong> : <label id="type"></label>
            </div>
            <div class="col-md-4">
                <strong>Previous Installment Amount</strong> : <label id="remainings"> </label>
            </div>
            <div class="col-md-4">
                <strong>Previous Fine Amount</strong> : <label id="extraamount"></label>
            </div>
            <div class="col-md-4">
                <strong>Late Fee Amount</strong> : <label id="fine"></label>
            </div>
            <div class="col-md-4">
                <strong>Late Fee Days</strong> : <label id="latedays"> </label>
            </div>
            <div class="col-md-4">
                <strong>Payable Amount</strong> : <label id="amounttotal"></label>
            </div>

            <div class="clearfix"></div>
            <br />
        </div>
        <div class="row">
            <div class="col-md-12">
                <form action="<?php echo site_url().'/students/paid_payment_action/'.$this->uri->segment(3);?>" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Fee Pay Through <span class="required">*</span></label>
                        <div class="col-md-8 radio-list">
                            <label class="radio-inline">
                                <input type="radio" class="submit_in" name="fee_pay_through" id="optionsRadios5" value="bank" checked /> Bank </label>
                            <label class="radio-inline">
                                <input type="radio"  class="submit_in" name="fee_pay_through" id="optionsRadios4" value="college" /> College </label>
                        </div>
                    </div>


                    <div class="clearfix"></div>
                    <br />
                    <div class="form-group">
                        <label class="col-md-4 control-label">Paid at Campus <span class="required">*</span></label>
                        <div class="col-md-8">
                            <select class="form-control submitted_fee_campus_id" name="submitted_fee_campus_id" required>
                                <option value="">SELECT CAMPUS</option>
                                <?php
                                foreach($campuses as $campus):
                                    ?>
                                    <option value="<?php echo $campus['campus_id'];?>" <?php if($campus['campus_id']==$this->session->userdata('user_campus_id')){echo 'selected';}?>><?php echo $campus['campus_name'];?></option>
                                <?php
                                endforeach;
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <br />




                    <div class="bank">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Select Bank <span class="required">*</span></label>
                            <div class="col-md-8">
                                <select class="form-control bank_details" id="select_bank" name="bank_details" required>
                                    <option value="">SELECT BANK</option>
                                    <?php
                                    $count = count($account_numbers);
                                    for($a=0;$a<$count;$a++):
                                        ?>
                                        <option value="<?php echo $account_numbers[$a]['account_name'].'';?>"><?php echo $account_numbers[$a]['account_name'].'';?></option>
                                    <?php
                                    endfor;
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <br />
                        <div class="form-group">
                            <label class="col-md-4 control-label">Bank Challan / TID No.</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control input-inline input-medium" name="tid_no" id="tid_no" placeholder="Enter TID Number" value="" >
                                <button name="verify" class="btn btn-primary" type="button"  id="verify_now">Verify Now</button>
                                <div class="text-danger" id="error_verify" ></div>
                                <span class="help-inline"></span>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="form-group college" style="display:none;">
                        <label class="col-md-4 control-label">Fee Submit Type <span class="required">*</span></label>
                        <div class="col-md-8 radio-list">
                            <label class="radio-inline">
                                <input type="radio" class="pay_by" name="fee_submit_type" id="optionsRadios5" value="computer_challan" checked /> Pay By Computer Challan </label>

                            <label class="radio-inline">
                                <input type="radio" class="pay_by" name="fee_submit_type" id="optionsRadios4" value="receipt_book" /> Pay By Receipt Book </label>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="receipt_book">

                        <div class="clearfix"></div>
                        <br />
                        <div class="form-group">
                            <label class="col-md-4 control-label">Book No. <span class="required">*</span></label>
                            <div class="col-md-8">
                                <input type="number" min="1" class="form-control input-inline input-medium book_no" name="book_no" placeholder="Enter Receipt Book Number" value="">
                                <span class="help-inline"></span>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <br />
                        <div class="form-group">
                            <label class="col-md-4 control-label">Receipt No. <span class="required">*</span></label>
                            <div class="col-md-8">
                                <input type="number" min="1" class="form-control input-inline input-medium receipt_no" name="receipt_no" placeholder="Enter Receipt Number" value="">
                                <span class="help-inline"></span>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="clearfix"></div>
                    <br />
                    <div class="form-group challan">
                        <label class="col-md-4 control-label">Challan Image <span class="required">*</span></label>
                        <div class="col-md-8">
                            <input class="scan_challan" type="file" name="scan_challan" value="" required />
                            <span class="help-inline"></span>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Application Image</label>
                        <div class="col-md-8">
                            <input type="file" class="application" name="fine_application" value="" />
                            <span class="help-inline"></span>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Paid Date <span class="required">*</span></label>
                        <div class="col-md-8">
                            <!-- CLASS date date-picker-->
                            <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d');?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                <input type="text" name="paid_date"  id="paid_date" class="form-control paid_date" value="<?php echo date('Y-m-d');?>" required readonly>
                                <span class="input-group-btn">
																	<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
																	</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Installment Amount <span class="required">*</span></label>
                        <div class="col-md-8">
                            <input type="number" min="1" id="instamount" class="form-control input-inline input-medium amount amount_<?php echo $i;?>" data-number="<?php echo $i;?>" name="amount" placeholder="Enter student installment amount" value="<?php echo $payment['amount'];?>" readonly required>
                            <span class="help-inline"></span>
                        </div>
                        <div class="clearfix"></div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-4 control-label">Previous Installment Amount <span class="required">*</span></label>
                        <div class="col-md-8">
                            <input type="number" min="0" max="" class="form-control input-inline input-medium remaining_installment_amount remaining_installment_amount_<?php echo $i;?>" data-number="<?php echo $i;?>" data-value="" name="remaining_installment_amount" id="remaining_installment_amount" placeholder="Enter student previous installment amount" value="" readonly required>

                            <label class="checkbox-inline hidden" id="instabox"">
                            <input type="checkbox" id="inlineCheckbox1" data-number="<?php echo $i;?>" class="remove_split_remaining_installment_amount remove_split_remaining_installment_amount_<?php echo $i;?>" name="split_remaining_installment_amount" value="1" /> Edit </label>

                            <span class="help-inline"></span>
                        </div>
                        <div class="clearfix"></div>
                    </div>


                    <div class="form-group remove_remaining_installment_amount_action_<?php echo $i;?>" style="display:none;">
                        <label class="col-md-4 control-label"> Amount <span class="required">*</span></label>
                        <div class="col-md-8">
                            <input type="number" min="0" class="form-control input-inline input-medium remove_remaining_installment_amount remove_remaining_installment_amount_<?php echo $i;?>" name="remove_remaining_installment_amount" placeholder="" value="0" readonly required>
                            <span class="help-inline"></span>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="form-group remove_remaining_installment_amount_action_<?php echo $i;?>" style="display:none;">
                        <label class="col-md-4 control-label">Action <span class="required">*</span></label>
                        <div class="col-md-8 radio-list">
                            <label class="radio-inline">
                                <input type="radio"  class="prev_installment_status" name="prev_installment_status" data-number="<?php echo $i;?>" id="optionsRadios3" value="shift" /> Shift </label>
                            <label class="radio-inline">
                                <input type="radio"  class="prev_installment_status" name="prev_installment_status" data-number="<?php echo $i;?>" id="optionsRadios3" value="new" /> New Installment </label>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Previous Fine Amount <span class="required">*</span></label>
                        <div class="col-md-8">
                            <input type="number" min="0" id="previous_extra_amounts" max="<?php echo $payment['extra_amount'];?>"
                                   class="form-control input-inline input-medium previous_extra_amount previous_extra_amount_<?php echo $i;?>"
                                   data-number="<?php echo $i;?>" data-value="<?php echo $payment['extra_amount'];?>"
                                   name="extra_amount" placeholder="Enter student previous fine amount"
                                   value="" readonly required>

                            <label class="checkbox-inline hidden" id="prevbox"">
                            <input type="checkbox" id="inlineCheckbox1" data-number="<?php echo $i;?>" class="remove_split_previous_fine_amount " name="split_remaining_fine_amount" value="1" /> Edit </label>

                            <span class="help-inline"></span>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="form-group remove_split_previous_fine_amount_action" style="display:none;">
                        <label class="col-md-4 control-label"> Amount <span class="required">*</span></label>
                        <div class="col-md-8">
                            <input type="number" min="0" class="form-control input-inline input-medium remove_previous_fine_amount remove_previous_fine_amount_<?php echo $i;?>" name="remove_previous_fine_amount" placeholder="" value="0" readonly required>
                            <span class="help-inline"></span>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="form-group remove_split_previous_fine_amount_action" style="display:none;">
                        <label class="col-md-4 control-label">Action <span class="required">*</span></label>
                        <div class="col-md-8 radio-list">
                            <label class="radio-inline">
                                <input type="radio"  class="prev_fine_status" name="prev_fine_status" data-number="<?php echo $i;?>" id="optionsRadios2" value="remove" /> Remove </label>
                            <label class="radio-inline">
                                <input type="radio"  class="prev_fine_status" name="prev_fine_status" data-number="<?php echo $i;?>" id="optionsRadios3" value="shift" /> Shift </label>
                            <label class="radio-inline">
                                <input type="radio"  class="prev_fine_status" name="prev_fine_status" data-number="<?php echo $i;?>" id="optionsRadios4" value="new" /> New Installment </label>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Late Fee Fine Amount <span class="required">*</span></label>
                        <div class="col-md-8">
                            <input type="number" min="0" max="" class="form-control input-inline input-medium fine_amount fine_amount"
                                   name="fine_amount" id="fine_amount" placeholder="Enter student previous fine amount"  readonly required>

                            <label class="checkbox-inline hidden" id="finebox">
                                <input type="checkbox" id="inlineCheckbox1" data-number="<?php echo $i;?>" class="remove_split_fine_amounts" name="split_fine_amount"  value="1" /> Edit </label>

                            <span class="help-inline"></span>
                        </div>
                        <div class="clearfix"></div>
                    </div>

                    <div class="form-group remove_split_fine_amount_action" style="display:none;">
                        <label class="col-md-4 control-label"> Amount <span class="required">*</span></label>
                        <div class="col-md-8">
                            <input type="number" min="0" class="form-control input-inline input-medium remove_fine_amount remove_fine_amount_<?php echo $i;?>" name="remove_fine_amount" id="remove_fine_amount" placeholder="" value="0" readonly required>
                            <span class="help-inline"></span>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="form-group remove_split_fine_amount_action" style="display:none;">
                        <label class="col-md-4 control-label">Action <span class="required">*</span></label>
                        <div class="col-md-8 radio-list">
                            <label class="radio-inline">
                                <input type="radio"  class="late_fee_fine_status" data-number="<?php echo $i;?>" name="late_fee_fine_status" id="optionsRadios2" value="remove" /> Remove </label>
                            <label class="radio-inline">
                                <input type="radio"  class="late_fee_fine_status" data-number="<?php echo $i;?>" name="late_fee_fine_status" id="optionsRadios3" value="shift" /> Shift </label>
                            <label class="radio-inline">
                                <input type="radio"  class="late_fee_fine_status" data-number="<?php echo $i;?>" name="late_fee_fine_status" id="optionsRadios4" value="new" /> New Installment </label>
                        </div>
                        <div class="clearfix"></div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-4 control-label">Discount Amount <span class="required">*</span></label>
                        <div class="col-md-8">
                            <input type="number" min="1" class="form-control input-inline input-medium" name="discount" id="discount" required>
                            <span class="help-inline"></span>
                        </div>
                        <div class="clearfix"></div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-4 control-label">Paid Amount <span class="required">*</span></label>
                        <div class="col-md-8">
                            <input type="number" min="1" class="form-control input-inline input-medium" name="actual_amount" id="actual_amount" placeholder="Enter student paid amount" value="" readonly required>
                            <span class="help-inline"></span>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="form-group new_installment_date_section" style="display:none;">
                        <label class="col-md-4 control-label">New Installment Paid Date <span class="required">*</span></label>
                        <div class="col-md-8">
                            <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d');?>" data-date-format="yyyy-mm-dd" data-date-start-date="+0d" data-date-end-date="+30d" data-date-viewmode="years">
                                <input type="text" name="new_dead_line" class="form-control" value="<?php echo date('Y-m-d');?>" required readonly>
                                <span class="input-group-btn">
																	<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
																	</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4"></div>
                    <div class="col-md-8">
                        <br />
                        <input type="hidden" name="last_edit" value="<?php echo $this->session->userdata('name');?>" />
                        <input type="hidden" name="fee_amount" id="fee_amount" value="" />
                        <input type="hidden" name="challans" id="challans" value="" />
                        <input type="hidden" name="fee_ids" id="fee_ids" value="" />
                        <input type="hidden" name="dead_line" value="<?php echo $payment['dead_line'];?>" />
                        <input type="hidden" name="payment_plan" value="<?php echo $payment['payment_plan'];?>" />
                        <input type="hidden" name="college_fee" class="hidden_college_fee" value="0" />
                        <button class="btn green">Pay Fee</button>
                    </div>
                </form>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default">Close</button>
    </div>
</div>


<script>

    document.addEventListener("DOMContentLoaded", function(event) {
        // $('#payfeebtn').hide();
        $('#verify_now').click(function () {

            var tid = $('#tid_no').val();
            var bank = $('#select_bank').val();
            var paid_date = $('#paid_date').val();
            var amount = $('#actual_amount').val();

            if (bank.length < 6) {
                $('#error_verify').text('Select Bank');
            } else {

                if (tid.length < 6) {
                    $('#error_verify').text('Minimum Tid Length will be 5');
                }
                else {

                    $.ajax({
                        type: "post",
                        async: false,
                        url: '<?php echo site_url()?>/students/verify_fee',
                        data: {
                            tid: tid,
                            bank: bank,
                            amount: amount,
                            paid_date: paid_date
                        },
                        success: function (data) {
                            if (data === 'success') {
                                $('#payfeebtn').show();
                                $('#error_verify').html('');
                                $('#error_verify').html(data);
                            } else {
                                $('#error_verify').html(data);
                                $('#payfeebtn').hide();
                            }
                        }
                    });
                }
            }
        });
    });

    function getselected() {

        var selected = [];
        $('div#checkboxes input[type=checkbox]').each(function () {
            if ($(this).is(":checked")) {
                selected.push($(this).attr('value'));
            }

        });

        let loans = <?php echo json_encode($payments) ?>;

        var chalans='';
        var amounts=0;
        var deadlines='';
        var type='';
        var remainings =0;
        var extraamount=0;
        var fine=0;
        var amounttotal=0;
        var latedays='';
        var feeids='';
        var disc=0;



        for (let i=0;i<selected.length;i++){

            chalans+=(loans[selected[i]].challan_no+',');
            amounts+=parseFloat(loans[selected[i]].amount);
            deadlines+=loans[selected[i]].dead_line;
            type+=loans[selected[i]].payment_comment+',';
            remainings+=parseFloat(loans[selected[i]].remaining_installment_amount);
            extraamount+=parseFloat(loans[selected[i]].extra_amount);

            let fee_fine=0;
            let m = moment(loans[selected[i]].dead_line,"YYYY-MM-DD");  // or whatever start date you have
            let todays = moment().startOf('day');

            let days = Math.round(moment.duration(todays - m).asDays());

            let diff=parseInt(days);



            if (diff === 'NaN'){

                fee_fine = 0;
                diff=0;


            }else {

                if (diff > 1)
                {

                    fee_fine = diff * 50;
                    latedays +=diff;

                } else
                {
                    fee_fine = 0;
                    diff=0;
                    var dateofvisit = moment(loans[selected[i]].dead_line, 'YYYY-MM-DD');
                    var today = moment();

                    if (today.diff(dateofvisit, 'days')<-30 && disc <10)
                    {
                        disc += parseFloat(loans[selected[i]].disc_per_inst);

                    }

                }

            }



            fine += fee_fine;
            feeids += ','+loans[selected[i]].id;


        }

        installment=parseFloat(amounts);
        amountstotal=parseFloat(amounts+remainings+extraamount+fine);
        discount=parseFloat(disc*(amountstotal/100));
        amounttotal=parseFloat((amounts+remainings+extraamount+fine)-discount);


        if (remainings >0 ){

            $(".modal-body #instabox").removeClass('hidden');

        }
        if (extraamount >0 ){

            $(".modal-body #prevbox").removeClass('hidden');

        }
        if (fine >0 ){

            $(".modal-body #finebox").removeClass('hidden');

        }



        $('.modal-body #paid_date').attr('data-dead-line',loans[selected[0]].dead_line);
        $(".modal-body #chalans").text( chalans);
        $(".modal-body #amounts").text( amounts);
        $(".modal-body #deadlines").text( deadlines);
        $(".modal-body #type").text( type);
        $(".modal-body #remainings").text( remainings);
        $(".modal-body #remaining_installment_amount").val( remainings);
        $(".modal-body #extraamount").text( extraamount);
        $(".modal-body #previous_extra_amounts").val( extraamount);
        $(".modal-body #fine").text( fine);
        $(".modal-body #fine_amount").val( fine);
        $(".modal-body #fine_amount").attr('max',fine);
        $('.modal-body #fine_amount').attr('data-fine',fine);
        $(".modal-body #latedays").text( latedays);
        $(".modal-body #amounttotal").text( amounttotal);
        $(".modal-body #instamount").val( installment);
        $(".modal-body #fee_amount").val( amounttotal);
        $(".modal-body #actual_amount").val( amounttotal);
        $(".modal-body #fee_ids").val( feeids);
        $(".modal-body #discount").val( discount);
        $('.modal-body #discount').attr('data-discount',discount);
        $(".modal-body #discount").attr('max',discount);
        $(".modal-body #discount").attr('min',0);
        $(".modal-body #remove_fine_amount").val( 0);
        $(".modal-body #challans").val( chalans);


        $('.modal-body #discount').on( 'keyup change', function () {


            let olddiscount = $('.modal-body #discount').data('discount');
            let newdiscount = $('.modal-body #discount').val();

            if(newdiscount > olddiscount || newdiscount <0){
                $(".modal-body #discount").val(olddiscount);
            }else{


            }

            installment=parseFloat(amounts);
            amountstotal=parseFloat(amounts+remainings+extraamount+fine);
            discount=$('.modal-body #discount').val();
            amounttotal=parseFloat((amounts+remainings+extraamount+fine)-discount);

            $('.modal-body #paid_date').attr('data-dead-line',loans[selected[0]].dead_line);
            $(".modal-body #chalans").text( chalans);
            $(".modal-body #amounts").text( amounts);
            $(".modal-body #deadlines").text( deadlines);
            $(".modal-body #type").text( type);
            $(".modal-body #remainings").text( remainings);
            $(".modal-body #remaining_installment_amount").val( remainings);
            $(".modal-body #extraamount").text( extraamount);
            $(".modal-body #previous_extra_amounts").val( extraamount);
            $(".modal-body #fine").text( fine);
            $(".modal-body #fine_amount").val( fine);
            $(".modal-body #fine_amount").attr('max',fine);
            $('.modal-body #fine_amount').attr('data-fine',fine);
            $(".modal-body #latedays").text( latedays);
            $(".modal-body #amounttotal").text( amounttotal);
            $(".modal-body #instamount").val( installment);
            $(".modal-body #fee_amount").val( amounttotal);
            $(".modal-body #actual_amount").val( amounttotal);
            $(".modal-body #fee_ids").val( feeids);
            $(".modal-body #discount").val( discount);
            $(".modal-body #remove_fine_amount").val( 0);
            $(".modal-body #challans").val( chalans);


        });


    }

    window.onscroll = function() {myFunction()};

    var header = document.getElementById("myHeader");

    var sticky = header.offsetTop;

    function myFunction() {
        if (window.pageYOffset > sticky) {
            header.classList.add("sticky");
            header.style.marginTop   = "50px";
        } else {
            header.classList.remove("sticky");
            header.style.marginTop   = "0px";
        }
    }

    function openchallan() {

        var selected = [];
        $('div#checkboxes input[type=checkbox]').each(function () {
            if ($(this).is(":checked")) {
                selected.push($(this).attr('value'));
            }

        });



        let loans = <?php echo json_encode($payments) ?>;
        //var leave = leaves[myBookId];

        var chalanids='';




        for (let i=0;i<selected.length;i++){

            chalanids+=(loans[selected[i]].id+',');

        }

        let link = <?php echo json_encode(site_url()); ?>;

        link=link+'/students/print_contractor_challan/'+chalanids;

        window.open(link,"_blank");

    }



</script>