<?php
	$myAccess = checkUserAccess();
?>	
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
			<?php if(@$this->session->userdata('error')):?>
                <div class="alert alert-danger">
                    <button class="close" data-close="alert"></button>
                    <span>
                    <?php echo $this->session->userdata('error');?> </span>
                </div>
            <?php endif;?>
            <!-- Student Data-->
            <?php
            if(@$myAccess[0]['dashboard_check_student_box']==1 || $this->session->userdata('role')=='Admin'): ?>
                <div class="row">
                    <div class="col-md-12 ">
                        <!-- BEGIN SAMPLE FORM PORTLET-->
                        <div class="portlet box green ">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-user"></i> Struck Of Student
                                </div>
                            </div>
                            <div class="portlet-body table-responsive">
                                <table class="table table-bordered table-hover" >
                                    <thead>
                                    <tr>
                                        <th class="hidden">
                                            Hidden
                                        </th>
                                        <th>
                                            Student details
                                        </th>
                                        <th>
                                            Student Image
                                        </th>
                                        <th>
                                            Type &nbsp;&nbsp;&nbsp;
                                        </th>
                                        <th>
                                            Fee Paid &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        </th>
                                        <th>
                                            Fee Unpaid &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        </th>
                                        <th>
                                            Fee Structure
                                        </th>
                                        <th>
                                            Council Fee Status
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        $i=0;
                                        foreach($students as $student):
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
                                            ?>
                                            <tr class="odd gradeX <?php echo $payment_alert;?>">
                                                <td class="hidden">
                                                    <?php echo $i;?>
                                                </td>
                                                <td>
                                                <br>


                                                    <strong>Roll No : </strong><?php echo $student['roll_no'];?> <br>
                                                    <strong>Name : </strong><?php echo $student['first_name'].' '.$student['last_name'];?> <br>
                                                    <strong>CNIC : </strong><?php echo $student['cnic']?> <br>
                                                    <strong>Contact Details : </strong><?php echo $student['mobile'];?> <br>
                                                    <strong>Emergency Contact : </strong><?php echo $student['emergency_no'];?><br />
                                                    <strong>Campus : </strong><?php echo $student['campus_name'];?> <br>
                                                    <strong>Class : </strong><?php echo $student['class_name'];?> <br>
                                                    <strong>Course : </strong><?php echo $student['course_name'];?> <br>
                                                    <strong>Contractor : </strong> <?php


                                                    if($student['contract_id']==0)
                                                {
                                                    echo 'N/A';
                                                }
                                                else
                                                {
                                                    $contract = $this->db->get_where('contracts', array('contract_id'=>$student['contract_id']))->result_array();
                                                    echo @$contract[0]['contract_name'].' ('.@$contract[0]['contract_date'].')';
                                                }
                                                ?>

                                                </td>
                                                <td>
                                                    <?php $student_image = $this->db->get_where('student_documents',array('student_id'=>$student['student_id'],'type'=>'Photo'))->result_array();?>
                                                    <img height="100" src="<?php echo base_url().'uploads/'.@$student_image[0]['image'];?>" alt="" />
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
                                                <td><?php  echo $paid_fee   ?></td>
                                                <td><?php  echo $unpaid_fee   ?></td>
                                                <td>

                                                    <a class="btn green" href="<?php echo site_url();?>/students/payments_paid/<?php echo $student['student_id'];?>" target="_blank">
                                                        <i class="fa fa-money"></i>  Show Fee Structure
                                                    </a>

                                                </td>
                                                <td>
                                                    <?php
                                                    $this->db->select('*');
                                                    $this->db->from('expenses');
                                                    $this->db->where(array('student_id'=>$student['student_id']));
                                                    $council_fees = $this->db->get()->result_array();

                                                    foreach($council_fees as $council_fee)
                                                    {
                                                        echo '<strong>Roll No : </strong>'.$council_fee['council_exam_no'];
                                                        echo '<br />';
                                                        echo '<strong>Submit Date : </strong>'.$council_fee['date'];
                                                        echo '<br />';
                                                        echo '<strong>Amount : </strong>'.$council_fee['amount'];
                                                        echo '<br /><hr />';
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
                            <?php
                            $process = $this->db->get_where("struckof_procedures","student_id = '".$this->uri->segment(3)."' and process_count = '".$this->uri->segment(4)."'")->row();
                            if ( $students[0]['status']== '1'): ?>
                                <div class="col-md-12" style="margin: 7px">
                                        <row>
                                            <?php 
                                                if (( $process->action_type == 'immediate' && count($struckofdata) == 0 ) || ( $process->action_type == 'process' ))
                                                {
                                                    $status = $this->db->get_where('struckof_procedures',array('student_id'=>$this->uri->segment(3),'process_count'=>$this->uri->segment(4)))->row()->status;
                                                    if($status!='reject')
                                                    {
                                            ?>
                                                <input type="submit" class="btn yellow" name="student_check" value="Add Details" data-toggle="modal" href="#basic" />
                                            <?php
                                                    }
                                                }
                                                if (@$process->action_type == 'immediate'){
                                                    echo '<br /><span class="blink_me" style="font-weight:bold;font-size:18px;color:#F00; float: right">Imediate Action</span>';
                                                }
                                            ?>
                                        </row>
                                    </div>
                            <?php
                            endif;
                                echo '<a class="btn purple" style="margin: 10px" href="'.site_url().'/documents/print_struck_off_notice/'.$studentid.'" target="_blank">
                                        <i class="fa fa-print" ></i>  Struck of Letter
                                      </a>'; ?>
                            <?php
                                if ($struckofdata): ?>
                                    <div class="portlet-body table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th class="hidden">
                                            Index
                                        </th>
                                        <th>
                                            Contact By
                                        </th>

                                        <th>
                                            Contact From No
                                        </th>
                                        <th>
                                            Contact To No
                                        </th>

                                        <th>
                                            Date
                                        </th>
                                        <th>
                                            Struck of Reason
                                        </th>
                                        <th>
                                            Proof Image
                                        </th>
                                        <th>
                                            Status
                                        </th>
                                        <th>
                                            Details
                                        </th>

                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $i=0;

                                    foreach($struckofdata as $data):
                                        ?>
                                        <tr class="odd gradeX <?php echo @$payment_alert;?>">
                                            <td class="hidden">
                                                <?php echo $i;?>
                                            </td>
                                            <td>
                                                <?php
                                                $contactby = $this->db->get_where('users', array('user_id'=>$data['created_by']))->result_array();
                                                echo $contactby[0]['first_name']." ".$contactby[0]['last_name'];?>

                                            </td>
                                            <td>
                                                <?php echo $data['contact_from_no']?>
                                            </td>
                                            <td>
                                                <?php echo $data['contact_to_no']?>
                                            </td>
                                            <td>
                                                <?php echo $data['created_at'];?>
                                            </td>

                                            <td>
                                                <?php echo $data['reason'];?>
                                            </td>

                                            <td>
												<?php if ($data['proof_image'] != NULL && $data['proof_image'] != '' ): ?>
													<?php
                                                        if($data['online_proof_image']==''):
                                                    ?>
                                                    <a class="btn green" href="<?php echo base_url();?>uploads/<?php echo $data['proof_image']?>" target="_blank">
													  <i class="fa fa-image"></i>  Proof Image
													</a>
                                                    <?php
                                                        else:
                                                    ?>
                                                    <a class="btn green" href="<?php echo str_replace($bucket_address, $cloudfront_address, $data['online_proof_image']);?>" target="_blank">
													  <i class="fa fa-image"></i>  Proof Image
													</a>
                                                    <?php
                                                        endif;
                                                    ?>
												<?php endif; ?>
												<?php if ($data['post_receipt'] != NULL && $data['post_receipt'] != '' ): ?>
													<a class="btn green" href="<?php echo base_url();?>uploads/<?php echo $data['post_receipt']?>" target="_blank">
													  <i class="fa fa-image"></i>  Post receipt
													</a>
												<?php endif; ?>
												<?php if ($data['whatsapp_image'] != NULL && $data['whatsapp_image'] != '' ): ?>
													<a class="btn green" href="<?php echo base_url();?>uploads/<?php echo $data['whatsapp_image']?>" target="_blank">
													  <i class="fa fa-image"></i>  Whatsapp Image
													</a>
												<?php endif; ?>
												<?php if ($data['sms_image']!= NULL && $data['sms_image'] != '' ): ?>
													<a class="btn green" href="<?php echo base_url();?>uploads/<?php echo $data['sms_image']?>" target="_blank">
													  <i class="fa fa-image"></i>  SMS Image
													</a>
												<?php endif; ?>
												<?php if ($data['recording'] != NULL && $data['recording'] != '' ): ?>
													<audio
														controls
														src="<?php echo base_url();?>recording/<?php echo $data['recording']?>" type="audio/mpeg">
															Your browser does not support the
															<code>audio</code> element.
													</audio>
                                                    <a class="btn green" href="<?php echo base_url();?>recording/<?php echo $data['recording']?>" target="_blank">
                                                        <i class="fa fa-soundcloud"></i>  Download Audio
                                                    </a>
												<?php endif; ?>

                                            </td>

                                            <td>
                                                <?php
                                                if ($data['status'] == "0")
                                                    echo  "<input class='btn green' value='PENDING' style='width: 100px'/>";

                                                elseif ($data['status'] == '2')

                                                    echo  "<input class='btn blue' value='Rejected' style='width: 100px'/>";
                                                else
                                                    echo " <input class='btn red' value='STRUCKED OF' style='width: 100px'/>";

                                                ?>
                                            </td>

                                            <td>
                                                <?php echo $data['detail'];?>
                                            </td>

                                        </tr>
                                        <?php
                                        $i++;
                                    endforeach;

                                    ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php
                                endif;
                            ?>
                            <?php
                                if ( $students[0]['status'] == '1'  && (@$myAccess[0]['student_delete']==1 || $this->session->userdata('role')=='Admin')) {
                                    if ((($process->action_type == 'process' && count($struckofdata) > 2) || ($process->action_type == 'immediate' && count($struckofdata) > 0)) && $process->status == 'pending') {
                                        echo '  <div class="col-md-offset-11 col-md-11" style="margin: 7px">
                                            <a  class="btn red" name="Delete Student" value="Delete Student" href="' . site_url() . '/students/struckofstudent/' . $studentid . '/'.$this->uri->segment(4).'" >
                                                <i class="fa fa-trash"></i>  STRUCK OF STUDENT
                                            </a>
                                            <a  class="btn red" name="Reject Student" value="Reject Student" href="' . site_url() . '/students/rejectstruckofstudent/' . $studentid . '/'.$this->uri->segment(4).'" >
                                                <i class="fa fa-circle-o-notch"></i>  Reject Struck of
                                            </a>
                                        </div>';
                                    }
                                }
                            ?>
                        </div>
                        <!-- END SAMPLE FORM PORTLET-->
                    </div>
                </div>
            <?php
            endif;
            ?>
            <!-- Struck of Details-->
		</div>
	</div>
	<!-- END CONTENT -->

    <div class="modal fade" id="basic" tabindex="-1"   data-width="800" >
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">Please enter struck of details <span class="required">*</span></h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/students/addstruckofdetails/<?php echo $studentid?>/<?php echo sizeof($struckofdata)?>">
                    <div class="form-body">
                        <div class="form-group">
                                <label class="col-md-4 control-label">Contact From No <span class="required">*</span></label>
                                <div class="col-md-8">
                                    <input type="number"  name="fromno" maxlength="13" class="form-control mobile" value="" required/>
                                </div>
                            </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">Contact To No <span class="required">*</span></label>
                            <div class="col-md-8">
                                <input type="number"  name="tono" maxlength="13" class="form-control mobile" value="" required/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">Letter Image </label>
                            <div class="col-md-8">
                                <input type="file" name="image" class="form-control"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">Post Office Receipt </label>
                            <div class="col-md-8">
                                <input type="file" name="post_image" class="form-control"/>
                            </div>
                        </div>
                        <div class="form-group">
                                <label class="col-md-4 control-label">Amount </label>
                                <div class="col-md-4">
                                    <input type="number" name="amount" class="form-control"  />
                                </div>
                            <div class="col-md-4">

                                <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                    <input type="text" name="from_date" id="selctedfrom" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
                                    <span class="input-group-btn">
                                                    <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                    </span>
                                </div>
                                <!-- /input-group -->
                                <!--<span class="help-block">
                                Select date </span>-->
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">Whatsapp Image</label>
                            <div class="col-md-4">
                                <input type="file" name="whatsapp_image" class="form-control" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">SMS Image</label>
                            <div class="col-md-4">
                                <input type="file" name="sms_image" class="form-control" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">Call Recording</label>
                            <div class="col-md-8">
                                <input type="file" name="recording" class="form-control" />
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-9">
                                <input type="hidden" name="add_by" value="<?php echo $this->session->userdata('name');?>" />
                                <input type="hidden" name="process_id" value="<?php echo $this->uri->segment(4); ?>" />
                                <input type="hidden" name="delete_type" value="process" />
                                <input type="hidden" name="reason_detail" value="" />
                                <input type="hidden" name="count" value="<?php echo count($struckofdata) ?>" />
                                <input type="hidden" name="action_type" value="<?php echo $process->action_type ?>" />
                                <button type="submit" class="btn red">Add Detail</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn default close_button" data-dismiss="modal">Close</button>
            </div>
</div>
<!-- /.modal-dialog -->