<!-- BEGIN CONTENT -->
<?php
$myAccess = checkUserAccess();
?>
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <!--<h3 class="page-title">			Add Teacher <small>You can add teacher here</small>			</h3>-->
        <!-- END PAGE HEADER-->
        <!-- BEGIN DASHBOARD STATS -->
        <!-- END DASHBOARD STATS -->
        <!-- BEGIN PAGE CONTENT-->
        <?php if(@$this->session->userdata('message')):?>
            <div class="alert alert-success">
                <button class="close" data-close="alert"></button> <span>                    <?php echo $this->session->userdata('message');?> </span> </div>
        <?php endif;?>
        <?php if(@$this->session->userdata('error')):?>
            <div class="alert alert-danger">
                <button class="close" data-close="alert"></button> <span>                    <?php echo $this->session->userdata('error');?> </span> </div>
        <?php endif;?>
        <div class="row">
            <div class="col-md-12 ">
                <!-- BEGIN SAMPLE FORM PORTLET-->
                <div class="portlet box green ">
                    <div class="portlet-title">
                        <div class="caption"> <i class="fa fa-edit"></i>
                            <?php                                	if($payments[0]['student_id']!=0):									$student = $this->db->get_where('students', array('student_id'=>$payments[0]['student_id']))->result_array();								?> Edit Payment (
                                <?php echo $student[0]['first_name'];?>
                                <?php echo $student[0]['last_name'];?>
                                <?php echo $student[0]['roll_no'];?> ) (
                                <?php echo $student[0]['mobile'];?> -
                                <?php echo $student[0]['emergency_no'];?> )
                            <?php                                	else:									$this->db->select('*');									$this->db->from('contracts');									$this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','INNER');									$this->db->where('contract_id',$payments[0]['contract_id']);									$contract_details = $this->db->get()->result_array();									//$contract = $this->db->get_where('contracts', array('contract_id'=>$payments[0]['contract_id']))->result_array();								?> Edit Payment - Contractor Name :
                                <?php echo $contract_details[0]['name'];?> &nbsp;&nbsp;&nbsp;&nbsp; Contract Name :
                                <?php echo $contract_details[0]['contract_name'];?>
                            <?php                                	endif;								?>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <?php foreach($payments as $payment):?>
                            <?php if($payment['paid']==1):?>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="col-md-3">
                                            <h4>Challan No. : <?php echo $payment['paid_challans'];?></h4> </div>
                                        <div class="col-md-3">
                                            <h4>Installment Amount : <?php echo $payment['amount'];?></h4> </div>
                                        <div class="col-md-3">
                                            <h4>Previous Installment Amount : <?php echo $payment['remaining_installment_amount'];?></h4> </div>
                                        <div class="col-md-3">
                                            <h4>Previous Fine Amount : <?php echo $payment['extra_amount'];?></h4> </div>
                                        <div class="col-md-3">
                                            <?php                                            $challan_date = date_create($payment['dead_line']);                                            $today_date = date_create($payment['paid_date']);                                            $diff=date_diff($challan_date,$today_date);                                            $difference = $diff->format("%R%a");                                            if($difference>0)                                            {                                                $fee_fine = $difference*50;                                            }                                            else                                            {                                                $fee_fine = 0;                                            }                                            ?>
                                            <h4>Fine Amount : <?php echo $fee_fine;?></h4> </div>
                                        <div class="col-md-3">
                                            <h4>Shifted Installment Amount : <?php echo $payment['shifted_installment'];?></h4> </div>
                                        <div class="col-md-3">
                                            <h4>Shifted Previous Fine Amount : <?php echo $payment['shifted_previous_fine'];?></h4> </div>
                                        <div class="col-md-3">
                                            <h4>Remove Previous Fine Amount : <?php echo $payment['removed_previous_fine'];?></h4> </div>
                                        <div class="col-md-3">
                                            <h4>Shifted Fine Amount : <?php echo $payment['shifted_fine'];?></h4> </div>
                                        <div class="col-md-3">
                                            <h4>Remove Fine Amount : <?php echo $payment['removed_fine'];?></h4> </div>
                                        <div class="col-md-3">
                                            <h4>Payable Amount : <?php echo $payment['amount']+$payment['remaining_installment_amount']+$payment['extra_amount']+$fee_fine;?></h4> </div>
                                        <div class="col-md-3">
                                            <h4>Paid Amount : <?php echo $payment['actual_amount'];?></h4> </div>
                                        <div class="col-md-3">
                                            <h4>Paid Date : <?php echo $payment['paid_date'];?></h4> </div>
                                        <div class="col-md-3">
                                            <h4>Dead Line : <?php echo $payment['dead_line'];?></h4> </div>
                                        <div class="col-md-3">
                                            <h4>Payment Type : <?php echo $payment['payment_comment'];?></h4> </div>
                                        <div class="col-md-3">
                                            <?php                                            if($payment['fee_submit_type']=='computer_challan'):                                            ?>
                                                <h4>Challan Image : <a href="<?php echo site_url();?>/students/print_college_challan/<?php echo $payment['id'];?>" target="_blank" class="btn red"><i class="fa fa-image"></i> Challan Image</a></h4>
                                            <?php                                            else:                                            ?>
                                                <h4>Challan Image : <a href="<?php echo base_url();?>uploads/<?php echo $payment['scan_challan'];?>" target="_blank" class="btn red"><i class="fa fa-image"></i> Challan Image</a></h4>
                                            <?php                                            endif;                                            ?>
                                        </div>
                                        <div class="col-md-3">
                                            <h4>Payment Paid By : <?php echo $payment['paid_by'];?></h4> </div>
                                        <div class="col-md-3">
                                            <h4>Payment Paid Through : <?php echo $payment['fee_pay_through'];?></h4> </div>
                                        <?php                                            if($payment['fee_pay_through']=='bank'):                                        ?>
                                            <div class="col-md-3">
                                                <h4>Bank Name : <?php echo $payment['bank_details'];?></h4> </div>
                                            <div class="col-md-3">
                                                <h4>Bank TID / Challan No. : <?php echo $payment['tid_no'];?></h4> </div>
                                        <?php                                            endif;                                        ?>
                                        <?php                                        if($payment['fee_pay_through']=='college' && $payment['fee_submit_type']=='computer_challan'):                                            ?>
                                            <div class="col-md-3">
                                                <h4>Pay Through : Computerized Challan</h4> </div>
                                        <?php                                        endif;                                        ?>
                                        <?php                                        if($payment['fee_pay_through']=='college' && $payment['fee_submit_type']!='computer_challan'):                                            ?>
                                            <div class="col-md-3">
                                                <h4>Pad Of : <?php echo $this->db->get_where('campuses',array('campus_id'=>$payment['submitted_fee_campus_id']))->row()->campus_name;?></h4> </div>
                                            <div class="col-md-3">
                                                <h4>Book No. : <?php echo $payment['book_no'];?></h4> </div>
                                            <div class="col-md-3">
                                                <h4>Receipt No. : <?php echo $payment['receipt_no'];?></h4> </div>
                                        <?php                                        endif;                                        ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <hr />
                                    <div class="col-md-12">
                                        <div class="col-md-3 text-center">
                                            <button type="button" class="btn green update" data-class="update_paid_date">Update Paid Date</button>
                                        </div>
                                    <?php if ($myAccess[0]['update_fee_submission'] == 1 || $this->session->userdata('role')=='Admin'):
                                            if($payment['fee_pay_through']=='bank'): 									?>
                                                <div class="col-md-3 text-center">
                                            <button type="button" class="btn green update" data-class="update_fee_submission_type">Update Fee Submission Bank</button>
                                        </div>
                                    <?php endif; endif; ?>
                                        <div class="col-md-3 text-center">
                                            <button type="button" class="btn green update" data-class="update_payment_paid_status">Update Paid Status</button>
                                        </div>
                                        <?php if($payment['fee_pay_through']!=='college' && $payment['fee_submit_type']!=='computer_challan'): ?>
                                        <div class="col-md-3 text-center">
                                            <button type="button" class="btn green update" data-class="update_challan_image">Update Challan Image</button>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="clearfix"></div>
                                    <hr /> </div>
                                <div class="row update_section update_payment_paid_status" style="display:none;">
                                    <div class="col-md-12">
                                        <form action="<?php echo site_url();?>/students/update_edit_payment/payment_status/<?php echo $payment['id'];?>" method="post">
                                            <div class="form-body">
                                                <div class="form-group">
                                                    <label class="col-md-3 control-label">Payment Status <span class="required">*</span></label>
                                                    <div class="col-md-9">
                                                        <select class="form-control input-medium" name="status" required>
                                                            <option value="">SELECT STATUS</option>
                                                            <option value="0">UNPAID</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>
                                                <br />
                                                <div class="form-group">
                                                    <label class="col-md-3 control-label">Reason <span class="required">*</span></label>
                                                    <div class="col-md-9">
                                                        <textarea name="reason" class="form-control" rows="5" required></textarea> <span class="help-inline"></span> </div>
                                                </div>
                                                <div class="clearfix"></div>
                                                <br /> </div>
                                            <div class="form-actions">
                                                <div class="row">
                                                    <div class="col-md-offset-3 col-md-9">
                                                        <button type="submit" class="btn green">Update Payment</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="row update_section update_challan_image" style="display:none;">
                                    <div class="col-md-12">
                                        <form action="<?php echo site_url();?>/students/update_edit_payment/payment_image/<?php echo $payment['id'];?>" method="post" enctype="multipart/form-data">
                                            <div class="form-body">
                                                <div class="form-group">
                                                    <label class="col-md-3 control-label">Fee Challan <span class="required">*</span></label>
                                                    <div class="col-md-9">
                                                        <input type="file" class="new_scan_challan" name="new_scan_challan" value="" /> <span class="help-inline"></span> </div>
                                                </div>
                                                <div class="clearfix"></div>
                                                <br />
                                                <div class="form-group">
                                                    <label class="col-md-3 control-label">Reason <span class="required">*</span></label>
                                                    <div class="col-md-9">
                                                        <textarea name="reason" class="form-control" rows="5" required></textarea> <span class="help-inline"></span> </div>
                                                </div>
                                                <div class="clearfix"></div>
                                                <br /> </div>
                                            <div class="form-actions">
                                                <div class="row">
                                                    <div class="col-md-offset-3 col-md-9">
                                                        <button type="submit" class="btn green">Update Payment</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="row update_section update_paid_date" style="display:none;">
                                    <div class="col-md-12">
                                        <form action="<?php echo site_url();?>/students/update_edit_payment/paid_date/<?php echo $payment['id'];?>" method="post">
                                            <div class="form-body">
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">Paid Date</label>
                                                    <div class="col-md-3">
                                                        <div class="input-group input-medium date date-picker" data-date="<?php echo $payment['paid_date'];?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                            <input type="text" name="paid_date" class="form-control" value="<?php echo $payment['paid_date'];?>" readonly> <span class="input-group-btn">                                                    <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>                                                    </span> </div>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>
                                                <br />
                                                <div class="form-group">
                                                    <label class="col-md-3 control-label">Reason <span class="required">*</span></label>
                                                    <div class="col-md-9">
                                                        <textarea name="reason" class="form-control" rows="5" required></textarea> <span class="help-inline"></span> </div>
                                                </div>
                                                <div class="clearfix"></div>
                                                <br /> </div>
                                            <div class="form-actions">
                                                <div class="row">
                                                    <div class="col-md-offset-3 col-md-9">
                                                        <button type="submit" class="btn green">Update Payment</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="row update_section update_fee_submission_type" style="display:none;">
                                    <div class="col-md-12">
                                        <form action="<?php echo site_url();?>/students/update_edit_payment/payment_method/<?php echo $payment['id'];?>" method="post">
                                            <div class="form-body">
                                                <div class="form-group">
                                                    <label class="col-md-3 control-label">Fee Pay Through <span class="required">*</span></label>
                                                    <div class="col-md-9 radio-list">
                                                        <label class="radio-inline">
                                                            <input type="radio" class="submit_in" name="fee_pay_through" id="optionsRadios5" value="bank" checked /> Bank </label>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>
                                                <br />
                                                <div class="bank">
                                                    <div class="form-group">
                                                        <label class="col-md-3 control-label">Select Bank <span class="required">*</span></label>
                                                        <div class="col-md-5">
                                                            <select class="form-control bank_details" id="select_bank" name="bank_details" required>
                                                                <option value="">SELECT BANK</option>
                                                                <?php
                                                                foreach($account_numbers as $bank):  ?>
                                                                <option value="<?php echo $bank['account_name'];?>"><?php echo $bank['account_name'].'';?></option>
                                                                <?php
                                                                endforeach;?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                    <br />
                                                    <div class="form-group">
                                                        <label class="col-md-3 control-label">Paid Date <span class="required">*</span></label>
                                                        <div class="col-md-9">
                                                            <!-- CLASS date date-picker-->
                                                            <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d');?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                                <input type="text" id="paid_date" name="paid_date" data-dead-line="<?php echo $payment['dead_line'];?>" class="form-control paid_date" value="<?php echo date('Y-m-d');?>" required readonly>
                                                                <span class="input-group-btn">
                                                                    <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                    <br />
                                                    <div class="form-group">
                                                        <label class="col-md-3 control-label">Bank Challan / TID No.</label>
                                                        <div class="col-md-9">
                                                            <input type="text" class="form-control input-inline input-medium" name="tid_no" id="tid_no" placeholder="Enter TID Number" value=""> <span class="help-inline"></span>
                                                            <button name="verify" class="btn btn-primary" type="button"  id="verify_now">Verify Now</button>
                                                            <div class="text-danger" id="error_verify" ></div>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                        <br />
                                                        <div class="form-group">
                                                            <label class="col-md-3 control-label">Challan Image <span class="required">*</span></label>
                                                            <div class="col-md-9">
                                                                <!-- CLASS date date-picker-->
                                                                <div class="form-group challan">
                                                                    <input class="scan_challan" type="file" name="scan_challan" value="" required />
                                                                    <span class="help-inline"></span>
                                                                </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="clearfix"></div>
                                                        <br />
                                                </div>
                                                <div class="clearfix"></div>

                                                <div class="form-group college" style="display:none;">
                                                    <label class="col-md-3 control-label">Fee Submit Type <span class="required">*</span></label>
                                                    <div class="col-md-9 radio-list">
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
                                                        <label class="col-md-3 control-label">Pad of <span class="required">*</span></label>
                                                        <div class="col-md-9">
                                                            <select class="form-control submitted_fee_campus_id" name="submitted_fee_campus_id">
                                                                <option value="">SELECT CAMPUS</option>
                                                                <?php                                                                foreach($campuses as $campus):                                                                    ?>
                                                                    <option value="<?php echo $campus['campus_id'];?>" <?php if($campus[ 'campus_id']==$this->session->userdata('user_campus_id')){echo 'selected';}?>>
                                                                        <?php echo $campus['campus_name'];?>
                                                                    </option>
                                                                <?php                                                                endforeach;                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                    <br />
                                                    <div class="form-group">
                                                        <label class="col-md-3 control-label">Book No. <span class="required">*</span></label>
                                                        <div class="col-md-9">
                                                            <input type="number" min="1" class="form-control input-inline input-medium book_no" name="book_no" placeholder="Enter Receipt Book Number" value=""> <span class="help-inline"></span> </div>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                    <br />
                                                    <div class="form-group">
                                                        <label class="col-md-3 control-label">Receipt No. <span class="required">*</span></label>
                                                        <div class="col-md-9">
                                                            <input type="number" min="1" class="form-control input-inline input-medium receipt_no" name="receipt_no" placeholder="Enter Receipt Number" value=""> <span class="help-inline"></span> </div>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </div>
                                                <div class="clearfix"></div>
                                                <br />
                                                <div class="form-group">
                                                    <label class="col-md-3 control-label">Reason <span class="required">*</span></label>
                                                    <div class="col-md-9">
                                                        <textarea name="reason" class="form-control" rows="5" required></textarea> <span class="help-inline"></span> </div>
                                                </div>
                                                <div class="clearfix"></div>
                                                <br /> </div>
                                            <div class="form-actions">
                                                <div class="row">
                                                    <div class="col-md-offset-3 col-md-9">
                                                        <button type="submit" id="payfeebtn" class="btn green">Update Payment</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            <?php endif;?>
                            <?php if($payment['paid']==0): ?>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="col-md-3">
                                            <h4>Challan No. : <?php echo $payment['challan_no'];?></h4> </div>
                                        <div class="col-md-3">
                                            <h4>Installment Amount : <?php echo $payment['amount'];?></h4> </div>
                                        <div class="col-md-3">
                                            <h4>Previous Installment Amount : <?php echo $payment['remaining_installment_amount'];?></h4> </div>
                                        <div class="col-md-3">
                                            <h4>Previous Fine Amount : <?php echo $payment['extra_amount'];?></h4> </div>
                                        <div class="col-md-3">
                                            <?php                                            $challan_date = date_create($payment['dead_line']);                                            $today_date = date_create($payment['paid_date']);                                            $diff=date_diff($challan_date,$today_date);                                            $difference = $diff->format("%R%a");                                            if($difference>0)                                            {                                                $fee_fine = $difference*50;                                            }                                            else                                            {                                                $fee_fine = 0;                                            }                                            ?>
                                            <h4>Fine Amount : <?php echo $fee_fine;?></h4> </div>
                                        <div class="col-md-3">
                                            <h4>Payable Amount : <?php echo $payment['amount']+$payment['remaining_installment_amount']+$payment['extra_amount']+$fee_fine;?></h4> </div>
                                        <div class="col-md-3">
                                            <h4>Paid Amount : <?php echo $payment['actual_amount'];?></h4> </div>
                                        <div class="col-md-3">
                                            <h4>Dead Line : <?php echo $payment['dead_line'];?></h4> </div>
                                        <div class="col-md-3">
                                            <h4>Payment Type : <?php echo $payment['payment_comment'];?></h4> </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <hr />
                                    <div class="col-md-12">
                                        <div class="col-md-4 text-center">
                                            <button type="button" class="btn green update" data-class="update_dead_line">Extend Installment Dead Line</button>
                                        </div>
                                        <?php if ($myAccess[0]['delete_users_payment'] == 1 || $this->session->userdata('role')=='Admin'): ?>
                                            <div class="col-md-4 text-center">
                                                <button type="button" class="btn green update" data-class="delete_payment">Delete Installment</button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="clearfix"></div>
                                    <hr /> </div>
                                <div class="row update_section update_dead_line" style="display:none;">
                                    <div class="col-md-12">
                                        <form action="<?php echo site_url();?>/students/update_edit_payment/extend_date/<?php echo $payment['id'];?>/<?php echo $this->uri->segment(4);?>" method="post">
                                            <div class="form-body">
                                                <div class="form-group">
                                                    <label class="control-label col-md-3">Extended Dead Line</label>
                                                    <?php
                                                        /*
                                                        if ($payment['first_deadline'] != NULL && $payment['first_deadline'] != '' )
                                                        {
                                                            $payment_dead_line=date_create($payment['first_deadline']);
                                                        }else {
                                                            $payment_dead_line = date_create($payment['dead_line']);
                                                        }
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
                                                        if ($payment)
                                                        $start_days = $days;
                                                        if ($instno == 2){
                                                            //CHECK ALREADY DATE EXTENDED OR NOT
                                                            $extendedDate = $this->db->get_where('update_payment_requests',array('student_id'=>$payment['student_id']))->result_array();
                                                            
                                                            if(count($extendedDate)<1)
                                                            {
                                                                //LAST LAST FEE DEAD LINE
                                                                $this->db->select('*');
                                                                $this->db->from('payments');
                                                                $this->db->order_by('dead_line','DESC');
                                                                $this->db->limit(1);
                                                                $this->db->where('student_id',$payment['student_id']);
                                                                $lastPayment = $this->db->get()->result_array();
    
                                                                $lastDeadLine = date_create($lastPayment[0]['dead_line']);
                                                                $finalDeadLine = date_diff($today_date,$lastDeadLine);
    
                                                                $end_days = $finalDeadLine->days+30;
                                                            }
                                                            else
                                                            {
                                                                $end_days = $days+120;
                                                            }
                                                        }
                                                        else{
                                                            $end_days = $days+120;
                                                        }
                                                        
                                                        if($end_days>=0)
                                                        {
                                                            $end_days='+'.$end_days;
                                                        }
                                                        else
                                                        {
                                                            $end_days=$end_days;
                                                        }
                                                        */
                                                        //DECIDED RULE IS YOU CAN EXTEND DATE OF ANY TRANSACTION UPTO 1 MONTH OF LAST TRANSACTION DATE AND ALSO NOT CROSSING THE DEAD LINE OF SESSION
                                                        //GET START DATE
                                                        $date1 = new DateTime($payment['dead_line']);
                                                        $date2 = new DateTime(date('Y-m-d'));
                                                        
                                                        $interval = $date1->diff($date2);
                                                        if($date2>$date1)
                                                        {
                                                            $start_date = '-'.$interval->days;
                                                        }
                                                        else
                                                        {
                                                            $start_date = '+'.$interval->days;
                                                        }
                                                        
                                                        //GET END DATE
                                                        //echo $payment['student_id'];
                                                        $this->db->select('dead_line');
                                                        $this->db->from('payments');
                                                        $this->db->where('student_id',$payment['student_id']);
                                                        $this->db->order_by('dead_line','DESC');
                                                        $this->db->limit(1);
                                                        $last_installment = $this->db->get()->result_array();
                                                        
                                                        $last_installment_date = $last_installment[0]['dead_line'];
                                                        //echo $last_installment_date;
                                                        
                                                        $this->db->select('*');
                                                        $this->db->from('students');
                                                        $this->db->join('classes','students.class_id=classes.class_id','inner');
                                                        $this->db->where('students.student_id',$payment['student_id']);
                                                        $maximum_fee_last_date = $this->db->get()->result_array();
                                                        
                                                        $maximum_fee_last_date = $maximum_fee_last_date[0]['maximum_fee_last_date'];
                                                        
                                                        if(new DateTime($maximum_fee_last_date)>new DateTime($last_installment_date))
                                                        {
                                                            $date1 = new DateTime($last_installment_date); // Given date
                                                            $date1 = $date1->modify("+1 month"); // Add one month
                                                            $date1 = $date1->format("Y-m-d");
                                                            
                                                            $date2 = new DateTime($maximum_fee_last_date);
                                                            if(new DateTime($date1)>new DateTime($last_installment_date))
                                                            {
                                                                $end_date = date('Y-m-d',strtotime($date1));
                                                            }
                                                            else
                                                            {
                                                                $end_date = date('Y-m-d',strtotime($date2->format("Y-m-d")));
                                                            }
                                                        }
                                                        else
                                                        {
                                                            $end_date = $maximum_fee_last_date;
                                                        }
                                                        //echo $end_date;
                                                        $date1 = new DateTime(date('Y-m-d'));
                                                        $date2 = new DateTime($end_date);
                                                        
                                                        $interval = $date1->diff($date2);
                                                        
                                                        $end_date = '+'.$interval->days;
                                                        
                                                        //echo $end_date;
                                                        
                                                    ?>
                                                    <div class="col-md-3">
                                                        <div class="input-group input-small date date-picker" data-date="<?php echo $payment['dead_line']?>" data-date-format="yyyy-mm-dd" data-date-start-date="<?php echo $start_date;?>d" data-date-end-date="<?php echo $end_date;?>d" data-date-viewmode="years">
                                                            <input type="text" name="new_dead_line" class="form-control" value="<?php echo $payment['dead_line']?>" readonly> <span class="input-group-btn">                                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>                                                                    </span> </div>
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>
                                                <br />
                                                <div class="form-group">
                                                    <label class="col-md-3 control-label">Reason <span class="required">*</span></label>
                                                    <div class="col-md-9">
                                                        <textarea name="reason" class="form-control" rows="5" required></textarea> <span class="help-inline"></span> </div>
                                                </div>
                                                <div class="clearfix"></div>
                                                <br /> </div>
                                            <div class="form-actions">
                                                <div class="row">
                                                    <div class="col-md-offset-3 col-md-9">
                                                        <button type="submit" class="btn green">Update Payment</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="row update_section delete_payment" style="display:none;">
                                    <?php
                                        //CHECK THIS USER HAS ANY PAID OR UNPAID TRANSACTION IN LAST 2 MONTHS
                                        $this->db->select('*');
                                        $this->db->from('payments');
                                        $this->db->where(array('student_id'=>$payment['student_id'],'payment_comment'=>'College Fee'));
                                        $this->db->where('dead_line<',$payment['dead_line']);
                                        $this->db->order_by('dead_line','DESC');
                                        $this->db->limit(1);
                                        $last_payment_dead_line = @$this->db->get()->row()->dead_line;
                                        
                                        if(count($last_payment_dead_line)>0)
                                        {
                                            $date1 = new DateTime($last_payment_dead_line);
                                            $date2 = new DateTime($payment['dead_line']);
                                            
                                            // Calculate the difference
                                            $interval = $date1->diff($date2);
                                            
                                            // Get the total number of days
                                            $days = $interval->days;
                                            
                                            $this->db->select('*');
                                            $this->db->from('students');
                                            $this->db->join('classes','classes.class_id=students.class_id','inner');
                                            $this->db->where('students.student_id',$payment['student_id']);
                                            $maximum_difference_installments = $this->db->get()->row()->maximum_difference_installments;    
                                        }
                                        
                                    ?>
                                    <div class="col-md-12">
                                        <?php
                                            if($days<=@$maximum_difference_installments || count($last_payment_dead_line)==0):
                                        ?>
                                        <form action="<?php echo site_url();?>/students/update_edit_payment/delete_payment/<?php echo $payment['id'];?>" method="post">
                                            <div class="form-body">
                                                <div class="form-group">
                                                    <label class="col-md-3 control-label">Reason <span class="required">*</span></label>
                                                    <div class="col-md-9">
                                                        <textarea name="reason" class="form-control" rows="5" required></textarea> <span class="help-inline"></span> </div>
                                                </div>
                                                <div class="clearfix"></div>
                                                <br /> </div>
                                            <div class="form-actions">
                                                <div class="row">
                                                    <div class="col-md-offset-3 col-md-9">
                                                        <button type="submit" class="btn red">Delete Payment</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                        <?php
                                            else:
                                        ?>
                                        <div class="alert alert-danger">
                                            Your cannot delete this transaction because this student has no any transaction within last 2 months.
                                        </div>
                                        <?php
                                            endif;
                                        ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <!-- END SAMPLE FORM PORTLET-->
            </div>
        </div>
        <!-- END PAGE CONTENT-->
    </div>
</div>
<!-- END CONTENT -->
<script>

    document.addEventListener("DOMContentLoaded", function(event) {
        $('#verify_now').click(function () {

            var tid = $('#tid_no').val();
            var bank = $('#select_bank').val();
            var paid_date = $('#paid_date').val();
            var amount = <?php echo $payment['actual_amount'] ?>;

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


</script>