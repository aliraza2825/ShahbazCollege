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
                                <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/students/checkstudentstatus" enctype="multipart/form-data">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-md-3 control-label">Any Query <span class="required">*</span></label>
                                                    <div class="col-md-9">
                                                        <input type="text" class="form-control input-inline input-medium" name="search" placeholder="Enter student's data" value="" required>
                                                        <span class="help-inline"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-actions">
                                        <div class="row">
                                            <div class="col-md-offset-3 col-md-9">

                                                <input type="submit" class="btn green" name="student_check" value="Search Student" />
                                            </div>
                                        </div>
                                    </div>
                                </form>
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
                                            Machine ID
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
                                                    if($student['status']==0)
                                                    {
                                                        echo '<br /><span class="blink_me" style="font-weight:bold;font-size:18px;color:#F00;">DELETED STUDENT</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php $student_image = $this->db->get_where('student_documents',array('student_id'=>$student['student_id'],'type'=>'Photo'))->result_array();?>
                                                    <img height="100" src="<?php echo base_url().'uploads/'.@$student_image[0]['image'];?>" alt="" />
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
                                                <td>
                                                    <span class="bold"><?php echo $student['machine_id'];?></span>
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
                                                    $this->db->where(array('student_id'=>$student['student_id']));
                                                    $council_fees = $this->db->get()->result_array();

                                                    foreach($council_fees as $council_fee)
                                                    {
                                                        echo 'Exam No. : '.$council_fee['council_exam_no'];
                                                        echo '<br />';
                                                        echo 'Submit Date : '.$council_fee['date'];
                                                        echo '<br />';
                                                        echo 'Amount : '.$council_fee['amount'];
                                                        echo '<br /><hr />';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    if($student['status']==1):
                                                        ?>
                                                        <a title="Struck of Student" href="<?php echo site_url().'/Students/struckofstudentview/'.$student['student_id'];?>" class="btn blue"><i class="fa fa-ban"></i></a>

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
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->
