
<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
    <div class="page-content">


        <!-- BEGIN PAGE HEADER-->
        <!--<h3 class="page-title">
        Edit Profile <small>You can edit your profile here</small>
        </h3>-->

        <!-- END PAGE HEADER-->
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
                <!-- BEGIN SAMPLE FORM PORTLET-->
                <div class="portlet box green ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-check"></i> Check Results
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/documents/diploma_documents" enctype="multipart/form-data">
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">Council Exam # <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <select class="form-control input-lg" name="council_exam_no">
                                                    <option value="" >SELECT EXAM NUMBER</option>
                                                    <?php
                                                    foreach($council_exam_numbers as $council_exam_number):
                                                        ?>
                                                        <option value="<?php echo $council_exam_number['council_exam_no'];?>" <?php if($council_exam_number['council_exam_no']==$this->input->post('council_exam_no')){echo 'selected';}?>>
                                                            <?php echo $council_exam_number['council_exam_no'];?> (<?php if($council_exam_number['class']==1){echo '1st Year';}else{ echo '2nd Year';}?>) (Roll Number Update Date : <?php echo $council_exam_number['date'];?>) (Result Update Date : <?php if($council_exam_number['result_update_date']=='0000-00-00'){echo 'Waiting';}else{ echo $council_exam_number['result_update_date'];}?>)
                                                        </option>
                                                    <?php
                                                    endforeach;
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <select class="form-control input-lg" name="campus_id">
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
                </div>
                <!-- END SAMPLE FORM PORTLET-->
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet box grey-cascade">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i> Results
                        </div>
                    </div>
                    <div class="portlet-body">

                        <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/documents/" enctype="multipart/form-data">
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">Select Document Type <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <select class="form-control input-lg document_type" name="document_type">
                                                    <option value="">SELECT DOCUMENT</option>
                                                    <option value="diploma">Diploma</option>
                                                    <option value="noc">NOC</option>
                                                    <option value="character_certifiacte">Character Certificate</option>
                                                    <option value="registration_form">Registration Form</option>
                                                    <option value="affidavit">Affidavit</option>
                                                </select>
                                                <!--<span class="help-inline"></span>-->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </form>
                        <div class="row all_selection_area" style="display:none;">
                            <div class="col-md-12">
                                <div class="form-group col-md-3">
                                    <label class="col-md-9 control-label">All Selection <span class="required">*</span></label>
                                    <div class="col-md-3">
                                        <input type="checkbox" class="select_all" name="select_all" value="1" />
                                    </div>
                                </div>
                                <div class="form-group col-md-9">
                                    <!--<div class="col-md-3 text-center" style="margin-bottom:15px">
                                        <form method="post" action="<?php echo site_url();?>/documents/print_diploma/0" target="_blank">
                                            <input type="hidden" name="student_ids" class="student_ids form-group" value="" required />
                                            <input type="submit" class="btn blue" value="Get Selected Students Diploma" />
                                        </form>
                                    </div>-->

                                    <div class="col-md-3 text-center" style="margin-bottom:15px">
                                        <form method="post" action="<?php echo site_url();?>/documents/print_noc/0" target="_blank">
                                            <input type="hidden" name="student_ids" class="student_ids form-group" value="" required />
                                            <input type="submit" class="btn blue" value="Get Selected Students NOC" />
                                        </form>
                                    </div>
                                    <div class="col-md-3 text-center" style="margin-bottom:15px">
                                        <form method="post" action="<?php echo site_url();?>/documents/print_character_certificate/0" target="_blank">
                                            <input type="hidden" name="student_ids" class="student_ids form-group" value="" required />
                                            <input type="submit" class="btn blue" value="Get Selected Students Character Certificate" />
                                        </form>
                                    </div>
                                </div>

                            </div>

                        </div>


                        <br />
                        <hr />




                        <table class="table table-bordered table-hover" id="sample_3">
                            <thead>
                            <tr>
                                <th class="hidden">
                                    Hidden
                                </th>
                                <th>
                                    Campus
                                </th>
                                <th>
                                    Class
                                </th>
                                <th>
                                    Council Exam No.
                                </th>
                                <th>
                                    Entry
                                </th>
                                <th>
                                    Roll No / Campus Roll No
                                </th>
                                <th>
                                    Computer #
                                </th>
                                <th>
                                    CNIC
                                </th>
                                <th>
                                    Name S/O Father Name
                                </th>
                                <th>
                                    Contractor
                                </th>
                                <th>
                                    Phone
                                </th>
                                <th>
                                    Address
                                </th>
                                <th>
                                    Previous Remarks
                                </th>
                                <th>
                                    Result Remarks
                                </th>
                                <th style="min-width:200px">
                                    Prints
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $i=0;
                            foreach($roll_numbers as $roll_number):


                                $fee = $this->db->get_where('payments','payments.student_id = '.$roll_number['student_id'].' and paid != "1"')->result_array();
                                $student_fee = $this->db->get_where('students',array('student_id'=>$roll_number['student_id']))->row()->total_fee;
                                
                                $all_created_fee_qry = "SELECT SUM(amount) as amount FROM payments WHERE student_id='".$roll_number['student_id']."' AND payment_plan!='consulation fee' AND payment_comment='College Fee'";
                        		$all_created_fee = $this->db->query($all_created_fee_qry)->result_array();
                        
                                $all_reversal_fee_qry = "SELECT SUM(prs.reversal_amount) as reversal_amount FROM `payments_reversal_requests` as prs INNER JOIN payments as p ON prs.payment_id=p.id WHERE prs.student_id='".$roll_number['student_id']."' AND prs.done=1 AND p.payment_comment='College Fee'";
                                $all_reversal_fee = $this->db->query($all_reversal_fee_qry)->result_array();
                        
                                if(count($all_reversal_fee)>0)
                                {
                                    $discount = $all_created_fee[0]['amount']-$all_reversal_fee[0]['reversal_amount'];
                                }
                                else
                                {
                                    $discount = $all_created_fee[0]['amount'];
                                }
                                
                                $remaining_fee_amount = $student_fee-$discount;
                                
                                $extra_check = $this->db->get_where('payments',array('student_id'=>$roll_number['student_id'],'paid'=>0))->result_array();
                                

                                ?>
                                <tr>
                                    <td class="hidden">
                                        <?php echo $i;?>
                                    </td>
                                    <td>
                                        <?php echo $roll_number['campus_name']?>
                                        <span class="select_checkbox diploma_checkbox_container" style="display:none;">
										<input style="display:none;" type="checkbox" class="diploma_checkbox" name="diploma" value="<?php echo $roll_number['student_id']?>" />
									</span>
                                    </td>
                                    <td>
                                        <?php echo $roll_number['class_name']?>
                                    </td>
                                    <td>
                                        <?php echo $roll_number['council_exam_no']?>
                                    </td>
                                    <td>
                                        <?php echo $roll_number['class'].' Year';?>
                                    </td>
                                    <td>
                                        <?php echo $roll_number['roll_no'].' / '.$roll_number['campus_roll_no']?>
                                    </td>
                                    <td>
                                        <?php echo $roll_number['computer_no'];?>
                                    </td>
                                    <td>
                                        <a href="#" class="table_cnic table_cnic_<?php echo $roll_number['id']?>" data-id="<?php echo $roll_number['id']?>">
                                            <?php
                                            if($roll_number['cnic']=='')
                                            {
                                                echo '00000-0000000-0';
                                            }
                                            else
                                            {
                                                echo $roll_number['cnic'];
                                            }
                                            ?>
                                        </a>
                                        <label class="council_mistake council_mistake_<?php echo $roll_number['id']?> hidden"><input type="checkbox" value="1" name="council_mistake" class="council_mistake_field_<?php echo $roll_number['id']?>" <?php if(@$roll_number['council_mistake']==1){echo 'checked';}?> /> Council Mistake</label>
                                        <input type="text" class="form-control cnic_field cnic_<?php echo $roll_number['id']?> hidden" value="<?php echo $roll_number['cnic']?>" data-roll-no-id="<?php echo $roll_number['id']?>" />
                                    </td>
                                    <td>
                                        <?php echo $roll_number['name']?>
                                    </td>
                                    <td>
                                        <?php
                                        if($roll_number['contractor_name']=='')
                                        {
                                            echo 'N/A';
                                        }
                                        else
                                        {
                                            echo $roll_number['contractor_name'];
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo $roll_number['mobile'].'<br />'.$roll_number['emergency_no'];?>
                                    </td>
                                    <td>
                                        <?php echo $roll_number['address']?>
                                    </td>
                                    <td>
                                        <?php echo $roll_number['remarks']?>
                                    </td>
                                    <td>
                                        <?php echo $roll_number['result_remarks']?>
                                    </td>
                                    <td>
                                        <?php if($remaining_fee_amount<1 && count($extra_check)<1): ?>

                                            <a target="_blank" href="<?php echo site_url();?>/documents/print_diploma/<?php echo $roll_number['student_id']?>" class="btn blue"><i class="fa fa-print">Diploma</i></a>


                                            <br />

                                            <a target="_blank" href="<?php echo site_url();?>/documents/print_noc/<?php echo $roll_number['student_id']?>" class="btn green"><i class="fa fa-print">NOC</i></a>

                                            <span class="select_checkbox noc_checkbox_container" style="display:none;">
										<input style="display:none;" type="checkbox" class="noc_checkbox" name="noc" value="<?php echo $roll_number['student_id']?>" />
									</span>
                                            <br />

                                            <a target="_blank" href="<?php echo site_url();?>/documents/print_character_certificate/<?php echo $roll_number['student_id']?>" class="btn yellow"><i class="fa fa-print">Character Certificate</i></a>

                                            <span class="select_checkbox character_certificate_checkbox_conatiner" style="display:none;">
										<input style="display:none;" type="checkbox" class="character_certificate_checkbox " name="character_certificate" value="<?php echo $roll_number['student_id']?>" />
									</span>
                                            <br />

                                            <a href="<?php echo site_url();?>/documents/print_council_registration_form/<?php echo $roll_number['student_id']?>" class="btn red"><i class="fa fa-print">Registration Form</i></a>

                                            <span class="select_checkbox registration_form_checkbox_container" style="display:none;">
										<input style="display:none;" type="checkbox" class="registration_form_checkbox" name="registration_form" value="<?php echo $roll_number['student_id']?>" />
									</span>
                                            <br />
                                            <a href="<?php echo site_url();?>/documents/print_admission_form/<?php echo $roll_number['student_id']?>" class="btn red"><i class="fa fa-print">Admission Letter</i></a>
                                            <br />
                                            <a href="<?php echo base_url($roll_number['result_image']); ?>" target="_blank">
                                                            <img src="<?php echo base_url($roll_number['result_image']); ?>" style="max-width:60px; cursor:pointer;">
                                                        </a>
                                            <br />
                                            
                                        <?php else: echo 'Fees Not Created Amount : '.$remaining_fee_amount.'<br> Please Clear all dues to show Diploma Documents';?>
                                        <?php endif; ?>
                                        <br />

                                            <a href="<?php echo site_url();?>/documents/print_affidavit/<?php echo $roll_number['student_id']?>" class="btn blue"><i class="fa fa-print">Affidavit</i></a>

                                            <span class="select_checkbox affidavit_checkbox_copntainer" style="display:none;">
        										<input style="display:none;" type="checkbox" class="affidavit_checkbox" name="affidavit" value="<?php echo $roll_number['student_id']?>" />
        									</span>
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