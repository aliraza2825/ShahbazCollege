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
            if(@$myAccess[0]['dashboard_check_student_box']==1 || $this->session->userdata('role')=='Admin'):
                ?>
                <div class="row">

                    <div class="col-md-12 ">
                        <!-- BEGIN SAMPLE FORM PORTLET-->
                        <div class="portlet box green ">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-user"></i> Student
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

                            <div class="col-md-12" style="margin: 7px">
                                 <row>
                                        <input type="submit" class="btn yellow" name="student_check" value="Add Details" data-toggle="modal" href="#basic" />
                                 </row>
                            </div>


                            <?php

                                if (@$freezedata):

                            ?>
                            <div class="portlet-body table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th class="hidden">
                                            Index
                                        </th>
                                        <th>
                                            Student
                                        </th>
                                        <th>
                                            Freeze By
                                        </th>

                                        <th>
                                            Re-join Date
                                        </th>
                                        <th>
                                            Fee Amount
                                        </th>
										 <th>
                                            Challan
                                        </th>
                                        <th>
                                            Reason
                                        </th>
                                        <th>
                                            Created Date
                                        </th>
                                        <th>
                                            Proof Image
                                        </th>

                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $i=0;

                                    foreach($freezedata as $data):

                                        ?>
                                        <tr class="odd gradeX <?php echo @$payment_alert;?>">
                                            <td class="hidden">
                                                <?php echo $i;?>
                                            </td>

                                            <td>
                                                <?php
                                                    $contactby = $this->db->get_where('students', array('student_id'=>$data['student_id']))->result_array();
                                                    echo $contactby[0]['first_name']." ".$contactby[0]['last_name'];
                                                ?>

                                            </td>

                                            <td>
                                                <?php echo $data['created_by']?>
                                            </td>
                                            <td>
                                                <?php echo $data['rejoin_date']?>
                                            </td>
                                            <td>
                                                <?php echo $data['fee_amount'];?>
                                            </td>
											<td>

                                                <a class="btn green" href="<?php echo site_url();?>/students/print_college_challan/<?php echo $data['challan_id']?>" target="_blank">
                                                  <i class="fa fa-image"></i>  See Challan
                                                </a>

                                            </td>
                                            <td>
                                                <?php echo $data['reason'];?>
                                            </td>
                                            <td>
                                                <?php echo $data['created_at'];?>
                                            </td>

                                            <td>

                                                <a class="btn green" href="<?php echo base_url();?>uploads/<?php echo $data['image_proof']?>" target="_blank">
                                                  <i class="fa fa-image"></i>  Show Image
                                                </a>

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
                                endif;    ?>
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
                    <h4 class="modal-title">Please enter Freeze details</h4>
                </div>
                <div class="modal-body">
                     <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/students/addfreezedetails/<?php echo $studentid?>">
                        <div class="form-body">

							<div class="form-group">
                                        <label class="col-md-3 control-label">Freezing Campus <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control" name="campus_id">
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
                                    <label class="col-md-4 control-label">Freeze Fee</label>
                                    <div class="col-md-8">
                                        <input type="number"  name="fee" class="form-control mobile" value="<?php echo $students[0]['freeze_fee'] ?>">
                                    </div>
                                </div>


                            <div class="form-group">
                                <label class="col-md-4 control-label">Rejoin Date</label>
                                <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                    <input type="text" name="from_date" id="selctedfrom" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
                                    <span class="input-group-btn">
                                       <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-4 control-label">Application Image</label>
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
