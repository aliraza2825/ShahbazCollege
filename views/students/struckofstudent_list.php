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
                                    <i class="fa fa-user"></i> Struck Of Students List
                                </div>
                            </div>
                            <div class="portlet-body table-responsive">
                                <table class="table table-bordered table-hover" id="sample_2">
                                    <thead>
                                    <tr>
                                        <th>
                                            No.
                                        </th>
                                        <th>
                                            View
                                        </th>
                                        <th>
                                            Campus
                                        </th>
										<th>
											Details
										</th>
                                        <th>
                                            Roll #
                                        </th>
                                        <th>
                                            Student Name
                                        </th>
                                        <!--<th>
                                            Student Image
                                        </th>-->
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
                                            Inquiry By
                                        </th>
                                        <th>
                                            Struck of By &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        </th>
                                        <th>
                                            Status
                                        </th>
                                        <th>
                                            Council Fee Status
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        $i=1;
                                        foreach($students as $student):
                                            $payment_plan = $this->db->get_where('payments', array('student_id'=>$student['student_id'], 'contract_id'=>0))->result_array();
                                            if(count($payment_plan)>0) {
                                                $payment_alert='';
                                            }
                                            elseif($student['contractor_id']>0) {
                                                $payment_alert='';
                                            }
                                            else {
                                                $payment_alert='alert alert-danger';
                                            }
                                            ?>
                                            <tr class="odd gradeX <?php echo $payment_alert;?>">
                                                <td>
                                                    <?php echo $i;?>
                                                </td>
                                                <td>
                                                    <a class="btn green" target="_blank" href="<?php echo site_url().'/Students/struckofstudentview/'.$student['student_id'];?>" >
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                </td>
												<td>
                                                    <?php echo $student['campus_name']?>
                                                </td>
												<td>
                                                    <?php 
                                                        $now = time(); // or your date as well
                                                        $your_date = strtotime($student['created_at']);
                                                        $datediff = $now - $your_date;
                                                        $numberDays= round($datediff / (60 * 60 * 24));

                                                        if( $numberDays >=7 && @$student['entriescount'] < 2 && @$student['action_type'] != 'immediate'){
                                                            echo "ENTER 2nd DETAILS NOW";
                                                        }elseif ($numberDays >=14 && @$student['entriescount']<3 && @$student['action_type'] != 'immediate'){
                                                            echo "ENTER 3rd DETAILS NOW";
                                                        }elseif ( $student['action_type'] == 'immediate'){
                                                            echo "Imediate Action";
                                                        }
													?>
                                                </td>
                                                <td>
                                                    <?php echo $student['roll_no'];?>
                                                </td>
                                                <td>
                                                    <?php echo $student['first_name'].' '.$student['last_name'];?>
                                                </td>
                                                <?php /*
                                                <td>
                                                    <?php $student_image = $this->db->get_where('student_documents',array('student_id'=>$student['student_id'],'type'=>'Photo'))->result_array();?>
                                                    <?php
                                                        if(@$student_image[0]['online_image']==''):
                                                    ?>
                                                    <img height="100" src="<?php echo base_url().'uploads/'.@$student_image[0]['image'];?>" alt="" />
                                                    <?php
                                                        else:
                                                    ?>
                                                    <img height="100" src="<?php echo str_replace($bucket_address, $cloudfront_address,@$student_image[0]['online_image']);?>" alt="" />
                                                    <?php
                                                        endif;
                                                    ?>
                                                </td>
                                                    */
                                                ?>
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
                                                    <?php echo $student['createdby']?>
                                                </td>
                                                <td>
                                                    <?php echo $student['approval_by']?>
                                                </td>
                                                <td>
                                                    <?php
                                                    if ($student['status'] == "pending")
                                                        echo  "<input class='btn green' value='PENDING' style='width: 100px'/>";
                                                    elseif ($student['status'] == 'reject')
                                                        echo  "<input class='btn blue' value='Rejected' style='width: 100px'/>";
                                                    else
                                                        echo " <input class='btn red' value='STRUCKED OF' style='width: 100px' />";
                                                    ?>
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
                            </div>
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
