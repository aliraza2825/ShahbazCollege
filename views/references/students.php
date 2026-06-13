<?php
$myAccess = checkUserAccess();
?>
<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
    <div class="page-content">

        <!-- END PAGE HEADER-->
        <!-- BEGIN DASHBOARD STATS -->

        <!-- END DASHBOARD STATS -->
        <div class="clearfix">
        </div>
        <?php
        if(@$this->session->flashdata('message')):
            ?>
            <div class="alert alert-success">
                <p><?php echo $this->session->flashdata('message');?></p>
            </div>
        <?php
        endif;
        ?>
        <?php
        if(@$this->session->flashdata('error')):
            ?>
            <div class="alert alert-danger">
                <p><?php echo $this->session->flashdata('error');?></p>
            </div>
        <?php
        endif;
        ?>

        <?php
        if(@$myAccess[0]['dashboard_check_student_box']==1 || $this->session->userdata('role')=='Admin'):
            ?>
            <div class="row">
                <div class="col-md-12 ">
                    <!-- BEGIN SAMPLE FORM PORTLET-->
                    <div class="portlet box green ">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-user"></i> Students
                            </div>
                        </div>

                        <div class="portlet-body table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th class="hidden">
                                        Hidden
                                    </th>
                                    <th>
                                        Roll #
                                    </th>
                                    <th>
                                        Name
                                    </th>
                                    <th>
                                        Student Image
                                    </th>
                                    <th>
                                        CNIC
                                    </th>
                                    <th>
                                        Class
                                    </th>
                                    <th>
                                        Mobile
                                    </th>
                                    <th>
                                        Contractor
                                    </th>
                                    <th>
                                        Type
                                    </th>
                                    <th>
                                        Documents
                                    </th>
                                    <th>
                                        Result Remarks
                                    </th>
                                    <th>
                                        Council Fee Remarks
                                    </th>
                                    <th>
                                        Action
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
                                                <?php echo $student['roll_no'];?>
                                            </td>
                                            <td>
                                                <?php echo $student['first_name'].' '.$student['last_name'];?>
                                                <?php
                                                    if($student['reference_user_id']!=NULL && $student['reference_user_id']!='' && $student['reference_user_id']!=0)    
                                                    {
                                                        $reference = $this->db->get_where('reference_users',array('reference_user_id'=>$student['reference_user_id']))->result_array();
                                                        echo '<br />Reference User : <span class="bold blink_me alert-success">'.@$reference[0]['name'].' - '.@$reference[0]['phone'].'</span>';
                                                    }
                                                ?>
                                                <?php
													if($student['status']==0)
													{
														$this->db->select('*');
														$this->db->from('freeze_student');
														$this->db->where("(freeze_student.student_id = '".$student['student_id']."')", NULL, FALSE);
														$freezedata = $this->db->get()->result_array();
														
														if(count($freezedata)>0)
														{
															echo '<br /><span class="blink_me" style="font-weight:bold;font-size:18px;color: blue;">FREEZED STUDENT</span>';
														}else{
															echo '<br /><span class="blink_me" style="font-weight:bold;font-size:18px;color:#F00;">DELETED STUDENT</span>';
														}
													}
												?>
                                            </td>
                                            <td>
                                                <?php $student_image = $this->db->get_where('student_documents',array('student_id'=>$student['student_id'],'type'=>'Photo'))->result_array();?>
                                                <?php
                                                    if(@$student_image[0]['online_image']==''):
                                                ?>
                                                <img height="100" src="<?php echo base_url();?>uploads/<?php echo @$student_image[0]['image'];?>" alt="" />
                                                <?php
                                                    else:
                                                ?>
                                                <img height="100" src="<?php echo str_replace($bucket_address,$cloudfront_address,@$student_image[0]['online_image']);?>" alt="" />
                                                <?php
                                                    endif;
                                                ?>
                                            </td>
                                            <td>
                                                <?php echo $student['cnic']?>
                                            </td>
                                            <td>
                                                <?php echo $student['class_name']?>
                                            </td>
                                            <td>
                                                <?php echo $student['mobile'];?>
                                                <br />
                                                <?php echo $student['emergency_no'];?>
                                            </td>
                                            <td>
                                                <?php
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
                                            <td style="text-align:center">
                                                <?php
                                                $id_card = $this->db->get_where('student_documents', array('type'=>'ID Card', 'student_id'=>$student['student_id']))->result_array();
                                                if(count($id_card)>0):
                                                    ?>
                                                    <i class="fa fa-check"></i> ID card
                                                    <br />
                                                <?php
                                                endif;
                                                ?>
                                                <?php
                                                $photo = $this->db->get_where('student_documents', array('type'=>'Photo', 'student_id'=>$student['student_id']))->result_array();
                                                if(count($photo)>0):
                                                    ?>
                                                    <i class="fa fa-check"></i> Photo
                                                    <br />
                                                <?php
                                                endif;
                                                ?>
                                                <?php
                                                $result_card = $this->db->get_where('student_documents', array('type'=>'Result Card', 'student_id'=>$student['student_id']))->result_array();
                                                if(count($result_card)>0):
                                                    ?>
                                                    <i class="fa fa-check"></i> Result Card
                                                <?php
                                                endif;
                                                ?>

                                            </td>
                                            <td>
                                                <?php getStudentResultRemarks($student['cnic']);?>
                                            </td>
                                            <td>
                                                <?php
                                                $this->db->select('*');
                                                $this->db->from('expenses');
                                                $this->db->join('classes', 'classes.class_id=expenses.class_id', 'left');
                                                $this->db->join('campuses','campuses.campus_id=expenses.campus_id', 'left');
                                                $this->db->where(array('student_id'=>$student['student_id']));
                                                $council_fees = $this->db->get()->result_array();

                                                foreach($council_fees as $council_fee)
                                                {
                                                    $class = "";
                                                    if ($council_fee['class'] == "1")
                                                    {
                                                        $class = "1st Year";
                                                    }
                                                    else
                                                    {
                                                        $class = "2nd Year";
                                                    }

                                                    echo 'Exam No. : '.$council_fee['council_exam_no'];
                                                    echo '<br />';
                                                    echo 'Submit Date : '.$council_fee['date'];
                                                    echo '<br />';
                                                    echo 'Amount : '.$council_fee['amount'];
                                                    echo '<br />';
                                                    echo 'Roll # : '.$council_fee['roll_no'];
                                                    echo '<br />';
                                                    echo 'Class  : '.$class;
                                                    echo '<br />';
                                                    echo 'Campus : '.$council_fee['campus_name'];
                                                    echo '<br />';
                                                    echo 'Session  : '.$council_fee['session'];
                                                    echo '<br /><hr />';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                if($student['status']==1):
                                                    ?>
                                                    <a title="Attendence" href="<?php echo site_url().'/attendence_data/student/'.$student['student_id'];?>" class="btn blue"><i class="fa fa-calendar"></i></a>
                                                    <?php
                                                    if(@$myAccess[0]['student_edit']==1 || $this->session->userdata('role')=='Admin'):
                                                        ?>
                                                        <a title="SMS" href="<?php echo site_url().'/students/sms/'.$student['student_id'];?>" class="btn yellow"><i class="fa fa-envelope"></i></a>
                                                    <?php
                                                    endif;
                                                    ?>
                                                    <?php
                                                    if(@$myAccess[0]['student_edit']==1 || $this->session->userdata('role')=='Admin'):
                                                        ?>
                                                        <a title="Edit" href="<?php echo site_url().'/students/edit_student/'.$student['student_id'];?>" class="btn blue"><i class="fa fa-edit"></i></a>
                                                    <?php
                                                    endif;
                                                    ?>
                                                    <?php
                                                    if(@$myAccess[0]['student_upload_documents']==1 || $this->session->userdata('role')=='Admin'):
                                                        ?>
                                                        <a title="Documents" href="<?php echo site_url().'/students/upload_documents/'.$student['student_id'];?>" class="btn green"><i class="fa fa-image"></i></a>
                                                    <?php
                                                    endif;
                                                    ?>

                                                    <?php
                                                    if(@$student['contractor_id']==0):
                                                        ?>
                                                        <?php
                                                        if(@$myAccess[0]['student_payments']==1 || $this->session->userdata('role')=='Admin'):
                                                            ?>
                                                            <a title="Payments" href="<?php echo site_url().'/students/payments/'.$student['student_id'];?>" class="btn purple"><i class="fa fa-money"></i></a>
                                                        <?php
                                                        endif;
                                                        ?>
                                                        <?php
                                                        if(@$myAccess[0]['student_payment_reset']==1 || $this->session->userdata('role')=='Admin'):
                                                            ?>
                                                            <a onclick="return confirm('Are you sure you want to reset this Student Fee Plan?')" href="<?php echo site_url().'/students/reset_plan/'.$student['student_id'];?>" title="Reset Plan" class="btn yellow"><i class="fa fa-refresh"></i></a>
                                                        <?php
                                                        endif;
                                                        ?>
                                                        <a  href="<?php echo site_url().'/students/add_student/'.$student['student_id'];?>" title="Assign New Course" class="btn yellow"><i class="fa fa-plus"></i></a>
                                                    <?php
                                                    endif;
                                                    ?>
                                                    <?php
                                                    if(@$myAccess[0]['can_student_struckof']==1 || $this->session->userdata('role')=='Admin'):

                                                        $this->db->select_sum('actual_amount');
                                                        $this->db->where('student_id',$student['student_id']);
                                                        $result = $this->db->get('payments')->row();
                                                        $total_fee_submit = $result->actual_amount;
                                                        if($total_fee_submit=='')
                                                        {
                                                            $total_fee_submit=0;
                                                        }
                                                        ?>
                                                        <a title="Struck of Student" href="<?php echo site_url().'/Students/struckofstudentview/'.$student['student_id'];?>" class="btn blue"><i class="fa fa-ban"></i></a>
                                                    <?php
                                                    endif;
                                                    ?>
                                                <?php
                                                endif;
                                                ?>
                                                <?php
                                                    if($student['status']==0):
                                                ?>
                                                    <?php
                                                    if(@$myAccess[0]['student_upload_documents']==1 || $this->session->userdata('role')=='Admin'):
                                                        ?>
                                                        <a title="Documents" href="<?php echo site_url().'/students/upload_documents/'.$student['student_id'];?>" class="btn green"><i class="fa fa-image"></i></a>
                                                    <?php
                                                    endif;
                                                    ?>

                                                    <?php
                                                    if(@$student['contractor_id']==0):
                                                        ?>
                                                        <?php
                                                        if(@$myAccess[0]['student_payments']==1 || $this->session->userdata('role')=='Admin'):
                                                            ?>
                                                            <a title="Payments" href="<?php echo site_url().'/students/payments/'.$student['student_id'];?>" class="btn purple"><i class="fa fa-money"></i></a>
                                                        <?php
                                                        endif;
                                                        ?>
                                                    <?php
                                                    endif;
                                                    ?>
                                                    <?php
                                                    if(@$myAccess[0]['can_student_struckof']==1 || $this->session->userdata('role')=='Admin'):

                                                        $this->db->select_sum('actual_amount');
                                                        $this->db->where('student_id',$student['student_id']);
                                                        $result = $this->db->get('payments')->row();
                                                        $total_fee_submit = $result->actual_amount;
                                                        if($total_fee_submit=='')
                                                        {
                                                            $total_fee_submit=0;
                                                        }
                                                        ?>
                                                        <a title="Struck of Student" href="<?php echo site_url().'/Students/struckofstudentview/'.$student['student_id'];?>" class="btn blue"><i class="fa fa-ban"></i></a>
                                                    <?php
                                                    endif;
                                                    ?>
                                                <?php
                                                endif;
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
                    <!-- END SAMPLE FORM PORTLET-->
                </div>
            </div>
        <?php
        endif;
        ?>

</div>
<!-- END CONTENT -->
</div>
<!-- END CONTAINER -->
