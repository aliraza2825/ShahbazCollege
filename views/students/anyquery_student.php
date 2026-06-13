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
                                <i class="fa fa-user"></i> Check Student Record
                            </div>
                        </div>
                        <div class="portlet-body form">

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
                                        Details
                                    </th>
                                    <th>
                                        Council Track
                                    </th>
                                    <th>
                                        Action
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $i=0;
                                if($this->input->post('search')):
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
                                                <?php echo $student['class_name'].'<br>Exam No : '.$student['student_exam']?>
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
                                            <td style="text-align:center">
                                                <?php echo $student['section'];?>
                                                <br />
                                                <?php
                                                    $st_shift = $this->db->get_where('shifts','id = '.$student['shift'])->row_array();
                                                    echo $st_shift['name'];?>
                                                <br />
                                                <?php
                                                    $st_study = $this->db->get_where('study_type','name = "'.$student['study_type'].'"')->row_array();
                                                echo $st_study['name'];?>
                                                <br />
                                                <?php
                                                if($student['student_card']==1)
                                                {
                                                    echo 'Student Card Taken';
                                                }
                                                ?>
                                                <div style="display:flex; align-items:center; border-bottom:1px solid #ddd; padding:3px 0;"></div>
                                                <span class="bold"><?php echo 'M.ID '.$student['machine_id'];?></span>
                                            <div style="display:flex; align-items:center; border-bottom:1px solid #ddd; padding:3px 0;"></div>
                                            
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
                                                <?php 
                                                    getStudentResultRemarks($student['cnic'],$student['course_id']);
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
                                                        <a href="javascript:;" 
                                                           title="College Email"
                                                           class="btn red showCollegeEmail"
                                                           data-student-id="<?php echo $student['student_id']; ?>">
                                                           <i class="fa fa-envelope-o"></i>
                                                        </a>
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
                                                    <a title="Purchased Products" href="<?php echo site_url().'/students/purchased_products/'.$student['student_id'];?>" class="btn green"><i class="fa fa-shopping-cart"></i></a>
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
                                                    <a title="Purchased Products" href="<?php echo site_url().'/students/purchased_products/'.$student['student_id'];?>" class="btn green"><i class="fa fa-shopping-cart"></i></a>
                                                <?php
                                                endif;
                                                ?>
                                            </td>
                                        </tr>
                                        <?php
                                        $i++;
                                    endforeach;
                                endif;
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
<div class="modal fade" id="collegeEmailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"></button>
                <h4 class="modal-title">Student College Email</h4>
            </div>

            <div class="modal-body" id="collegeEmailModalBody">
                Loading...
            </div>

        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {

    $(document).on('click', '.showCollegeEmail', function () {

        var student_id = $(this).data('student-id');

        $('#collegeEmailModal').modal('show');
        $('#collegeEmailModalBody').html('Loading...');

        $.ajax({
            url: "<?php echo site_url('students/college_email_detail'); ?>",
            type: "POST",
            data: {
                student_id: student_id
            },
            success: function (res) {
                $('#collegeEmailModalBody').html(res);
            }
        });
    });

    $(document).on('click', '#generateCollegeEmailBtn', function () {

        var student_id = $(this).data('student-id');

        $('#collegeEmailModalBody').html('Generating email, please wait...');

        $.ajax({
            url: "<?php echo site_url('students/generate_college_email'); ?>",
            type: "POST",
            data: {
                student_id: student_id
            },
            success: function (res) {
                $('#collegeEmailModalBody').html(res);
            }
        });
    });

});
</script>