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
                                    <i class="fa fa-user"></i> Check Student Struck of Record
                                </div>
                            </div>
                            <div class="portlet-body form">
                                <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/students/all_struckofstudent_report" enctype="multipart/form-data">
                                    <div class="form-body">

                                        <div class="form-group">
                                            <label class="col-md-3 control-label">Struck of Details for</label>
                                            <div class="col-md-5">
                                                <select class="form-control input-large" name="strucktype">
                                                    <option value="">SELECT Struck Of Type</option>

                                                        <option value="0">Pending Struck of Students</option>
                                                        <option value="1">Strucked of Students</option>
                                                        <option value="2">Rejected Struck of Requests</option>

                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3">From Date</label>
                                            <div class="col-md-3">
                                                <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                    <input type="text" name="from_date" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
                                                    <span class="input-group-btn">
												<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
												</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3">To Date</label>
                                            <div class="col-md-3">
                                                <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                    <input type="text" name="to_date" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
                                                    <span class="input-group-btn">
												<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
												</span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="form-actions">
                                        <div class="row">
                                            <div class="col-md-offset-3 col-md-9">
                                                <input type="hidden" name="submit" value="1" />
                                                <button type="submit" class="btn green">Submit</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="portlet-body table-responsive">
                                <?php

                                if (@$students):

                                ?>
                                <table class="table table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th class="hidden">
                                            Hidden
                                        </th>
                                        <th>
                                            View
                                        </th>

                                        <th>
                                            Inquiry By
                                        </th>

                                        <th>
                                            Roll #
                                        </th>
                                        <th>
                                            Student Name
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
                                            Struck of Reason &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        </th>

                                        <th>
                                            Struck of By &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        </th>

                                        <th>
                                            Status
                                        </th>

                                        <th>
                                            Struck of Date
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
                                                    <a class="btn green" href="<?php echo site_url().'/Students/struckofstudentview/'.$student['student_id'];?>" >
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                </td>

                                                <td>
                                                    <?php echo $student['inquiry']?>
                                                </td>

                                                <td>
                                                    <?php echo $student['roll_no'];?>
                                                </td>
                                                <td>
                                                    <?php echo $student['first_name'].' '.$student['last_name'];?>

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
                                                    <?php echo $student['reason']?>
                                                </td>

                                                <td>
                                                    <?php echo $student['updated_by']?>
                                                </td>

                                                <td>
                                                    <?php
                                                    if ($student['status'] == "0")
                                                        echo  "<input class='btn green' value='PENDING' style='width: 100px'/>";

                                                    elseif ($student['status'] == '2')

                                                        echo  "<input class='btn blue' value='Rejected' style='width: 100px'/>";
                                                    else
                                                        echo " <input class='btn red' value='STRUCKED OF' style='width: 100px' />";

                                                    ?>
                                                </td>

                                                <td>
                                                    <?php echo $student['created']?>
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

                                            </tr>
                                            <?php
                                            $i++;
                                        endforeach;

                                    ?>
                                    </tbody>
                                </table>

                                <?php

                                endif;?>
                            </div>




                        </div>
                        <!-- END SAMPLE FORM PORTLET-->
                    </div>
                </div>
            <?php
            endif;
            ?>

            <!-- Struck of Details-->




			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->
