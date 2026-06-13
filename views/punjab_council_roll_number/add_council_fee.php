<?php
    function addOrdinalSuffix($number) {
        if (!is_numeric($number)) {
            return $number; // Return as is if not a number
        }
        
        $suffixes = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
        
        if (($number % 100) >= 11 && ($number % 100) <= 13) {
            return $number . 'th';
        }
        
        return $number . $suffixes[$number % 10];
    }
?>
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
								<i class="fa fa-list"></i> Add Punjab Pharmacy Council Fee
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/punjab_council_roll_number/add_council_fee" enctype="multipart/form-data">
								<div class="form-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Council Exam # <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select class="form-control input-large" name="council_exam_no" required>
                                                    	<option value="">Select Exam #</option>
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
                                                <label class="col-md-3 control-label">Class <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <label class="radio-inline">
                                                    <input type="radio" name="class" id="optionsRadios4" value="1" checked> 1st year </label>
                                                    <label class="radio-inline">
                                                    <input type="radio" name="class" id="optionsRadios5" value="2"> 2nd Year </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label"> Campus <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select name="campus_ids[]" class="form-control input-inline input-large select2" id="select2_sample1" multiple required>
                                                        <?php
                                                            foreach($campuses as $campus):
                                                        ?>
                                                        <option value="<?php echo $campus['campus_id'];?>" <?php if($campus['campus_id']==@$this->input->post('campus_id')){echo 'selected';}?>><?php echo $campus['campus_name'];?></option>
                                                        <?php
                                                            endforeach;
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                        	<div class="form-group">
                                                <label class="control-label col-md-3">Council Fee Last Date <span class="required">*</span></label>
                                                <div class="col-md-3">
                                                    <div class="input-group input-large date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                        <input type="text" name="dead_line" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
                                                        <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                                    </div>
                                                    <!-- /input-group -->
                                                    <!--<span class="help-block">
                                                    Select date </span>-->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Fees for Students <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="number" class="form-control input-inline input-large" name="fee_for_students" placeholder="Enter student's fees" value="<?php echo @$this->input->post('fee_for_students');?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Fees for Contractors <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="number" class="form-control input-inline input-large" name="fee_for_contractors" placeholder="Enter student's fees" value="<?php echo @$this->input->post('fee_for_contractors');?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Diploma Issue Fee <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="number" class="form-control input-inline input-large" name="diploma_fee" placeholder="Enter Diploma fees" value="<?php echo @$this->input->post('diploma_fee');?>" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Council Exam Sequence Supplementary <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select class="form-control input-large" name="exam_sequence_first" required>
                                                        <option value="">Select Exam #</option>
                                                        <?php
                                                        foreach($sequences as $sequence):
                                                            if ($sequence['type'] == 'supplementary'):
                                                            ?>
                                                            <option value="<?php echo $sequence['id'];?>" <?php if($sequence['id'] == $this->input->post('exam_sequence')){echo 'selected';}?>><?php echo $sequence['type'].' - '.$sequence['first_year'].' - '.$sequence['second_year'] ?></option>
                                                        <?php
                                                            endif;
                                                        endforeach;
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Council Exam Sequence Annual<span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select class="form-control input-large" name="exam_sequence_second" required>
                                                        <option value="">Select Exam #</option>
                                                        <?php
                                                        foreach($sequences as $sequence):
                                                            if ($sequence['type'] != 'supplementary'):
                                                            ?>
                                                            <option value="<?php echo $sequence['id'];?>" <?php if($sequence['id'] == $this->input->post('exam_sequence')){echo 'selected';}?>><?php echo $sequence['type'].' - '.$sequence['first_year'].' - '.$sequence['second_year'] ?></option>
                                                        <?php
                                                        endif;
                                                        endforeach;
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Next Exam is? <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <label class="radio-inline">
                                                        <input type="radio" name="next_exam" id="optionsRadios4" value="annual" checked> Annual </label>
                                                    <label class="radio-inline">
                                                        <input type="radio" name="next_exam" id="optionsRadios5" value="supplementary"> Supplementary </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<input type="hidden" name="add_council_fee" value="1" />
                                            <button type="submit" class="btn green">Check Council Fee</button>
											<button onclick="location.href = '<?php echo site_url();?>'" type="button" class="btn default">Cancel</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
            <?php
            	if(count(@$results)>0):
			?>
            <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> Please Verify All Students
							</div>
						</div>
						<div class="portlet-body">
                            <form method="post" action="<?php echo site_url();?>/punjab_council_roll_number/test">
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
                                             Student Name
                                        </th>
                                        <th>
                                             CNIC
                                        </th>
                                        <th>
                                             College Roll No
                                        </th>
                                        <th>
                                            Council Roll No
                                        </th>
                                        <th>
                                             Exam #
                                        </th>
                                        <th>
                                            Name in Council
                                        </th>
                                        <th>
                                             Result Remarks
                                        </th>
                                        <th>
                                             Contractor
                                        </th>
                                        <th>
                                             Council Fee
                                        </th>
                                        <th>
                                             Dead Line
                                        </th>
                                        <th>
                                             Campus Address
                                        </th>
                                        <th>
                                             Submit Fee
                                        </th>
                                        <th>
                                             Fee Counts
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            $i=0;
                                            $counter=0;
                                            $class = $this->input->post('class');
                                            $council_exam_no = $this->input->post('council_exam_no');
                                            if ($class == 1)
                                                @$type = $this->db->get_where("exam_sequence","first_year = '$council_exam_no'")->row()->type;
                                            else
                                                @$type = $this->db->get_where("exam_sequence","second_year = '$council_exam_no'")->row()->type;

                                            if (@$type):
                                                foreach(@$results as $result):

                                                $this->db->select('*');
                                                $this->db->from('students');
                                                $this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
                                                $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
                                                $this->db->where('students.cnic', $result['cnic']);
                                                $student_data = $this->db->get()->result_array();

                                                if(in_array(@$student_data[0]['campus_id'],$this->input->post('campus_ids')) || @$student_data[0]['campus_id']==''):
                                                $counter++; ?>
                                                
                                                
                                                <tr>
                                                    <td class="hidden">
                                                        <?php echo $i;?>
                                                    </td>
                                                    <td>
                                                        <?php echo @$student_data[0]['campus_name'];?>
                                                    </td>
                                                    <td>
                                                        <?php echo @$student_data[0]['name'];?>
                                                    </td>
                                                    <td>
                                                        <?php echo @$student_data[0]['first_name'].' '.@$student_data[0]['last_name'];?>
                                                    </td>
                                                    <td>
                                                        <?php echo $result['cnic'];?>
                                                    </td>
                                                    <td>
                                                        <?php echo @$student_data[0]['roll_no'];?>
                                                    </td>
                                                    <td>
                                                        <?php echo $result['roll_no'];?>
                                                    </td>
                                                    <td>
                                                        <?php echo $result['council_exam_no'];?> (<?php echo $result['class'];?> Year)
                                                    </td>
                                                    <td>
                                                        <?php echo $result['name'];?>
                                                    </td>
                                                    <td>
                                                        <?php echo $result['result_remarks'];?>

                                                    </td>
                                                    <td>
                                                        <?php
                                                            if(@$student_data[0]['contract_id']==0)
                                                            {
                                                                echo 'N/A';
                                                            }
                                                            else
                                                            {
                                                                $this->db->select('*');
                                                                $this->db->from('contracts');
                                                                $this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','INNER');
                                                                $this->db->where('contracts.contract_id',$student_data[0]['contract_id']);
                                                                $contractor_details = $this->db->get()->result_array();

                                                                echo $contractor_details[0]['name'];
                                                                echo '<br />';
                                                                echo $contractor_details[0]['contract_name'];
                                                                //echo $this->db->get_where('contractors', array('contractor_id'=>$student_data[0]['contractor_id']))->row()->name;
                                                            }
                                                        ?>

                                                    </td>
                                                    <td>
                                                        <?php
                                                            if(@$student_data[0]['contractor_id']==0)
                                                            {
                                                                echo $this->input->post('fee_for_students');
                                                            }
                                                            else
                                                            {
                                                                echo $this->input->post('fee_for_contractors');
                                                            }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $this->input->post('dead_line');?>
                                                    </td>
                                                    <td>
                                                        <?php echo $result['address'];?>
                                                    </td>
                                                    <td>
                                                                <input type="hidden" name="students_cnic[]" value="<?php echo $result['cnic'];?>" />
                                                                <input type="hidden" name="students_result[]" value="<?php echo $result['result_remarks'];?>" />
                                                                <input type="hidden" name="fee_for_students" value="<?php echo $this->input->post('fee_for_students');?>" />
                                                                <input type="hidden" name="fee_for_contractors" value="<?php echo $this->input->post('fee_for_contractors');?>" />
                                                                <input type="hidden" name="dead_line" value="<?php echo $this->input->post('dead_line');?>" />
                                                                <input type="hidden" name="class" value="<?php echo $result['class'];?>" />
                                                                <input type="hidden" name="council_exam_no" value="<?php echo $result['council_exam_no'];?>" />
                                                                <input type="hidden" name="exam_sequence_first" value="<?php echo $this->input->post('exam_sequence_first');?>" />
                                                                <input type="hidden" name="exam_sequence_second" value="<?php echo $this->input->post('exam_sequence_second');?>" />
                                                                <input type="hidden" name="next_exam" value="<?php echo $this->input->post('next_exam');?>" />
                                                                <input type="hidden" name="diploma_fee" value="<?php echo $this->input->post('diploma_fee');?>" />
                                                        <?php
                                                        $cous = $this->db->get_where("payments","id = '".$result['fee_id']."'")->result_array();
                                                            if(count($cous) > 0)
                                                            {
                                                                echo 'Fee Created in System';
                                                            }
                                                            else
                                                            {
                                                                echo 'Fee Not Created in System';
                                                                ?>
                                                                <input type="hidden" name="id[]" value="<?php echo $result['id'];?>" />
                                                                <input type="hidden" name="cnic[]" value="<?php echo $result['cnic'];?>" />
                                                                <input type="hidden" name="result_remarks[]" value="<?php echo $result['result_remarks'];?>" />
                                                                <input type="hidden" name="contract_id[]" value="<?php echo @$student_data[0]['contract_id'];?>" />
                                                                <?php
                                                            }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        echo $result['result_remarks']."<br />";
                                                            $rule = $this->db->get('council_rules')->row();
                                                            $extra_fee=$rule->total_fee;
                                                            $no_of_exams=$rule->no_of_exams;
                                                            $dead_line=$this->input->post('dead_line');

                                                            $student = $this->db->join('classes','classes.class_id = students.class_id')->
                                                            where( array('cnic'=>$result['cnic']))->get('students')->result_array();
                                                            if(count($student)>0)
                                                            {
                                                                //ADD COUNCIL FEE OF THIS STUDENT
//                                                                if($student[0]['contractor_id']==0)
//                                                                {
                                                                    //FEE ADD ACCORDING TO STUDENT
                                                                    if($result['result_remarks']=='Pass' && $class==2)
                                                                    {

                                                                    }
                                                                    elseif($result['result_remarks']=='Pass*' && $class==2)
                                                                    {

                                                                    }
                                                                    elseif($result['result_remarks']!='Pass' && $result['result_remarks']!='Pass*' && $class==1)
                                                                    {
                                                                        //CUSTOM COMMENT FAIL IN 1st YEAR
                                                                        if ($this->input->post('next_exam') == 'supplementary')
                                                                            $next_council_exam_no = $seq_supplementary[0]['first_year'];
                                                                        else
                                                                            $next_council_exam_no = $seq_annual[0]['first_year'];
                                                                        $custom_comment = 'Fail in Council exam # '.$council_exam_no.' This fee for next exam # '.($next_council_exam_no).' 1st Year';
                                                                        echo $custom_comment.'<br /> <br /> First Exam no : '.$student[0]['exam_no'].' <br /> <br /> TOTAL COUNTS = '.(($council_exam_no-$student[0]['exam_no'])+2);
                                                                    }
                                                                    elseif($result['result_remarks']!='Pass' && $result['result_remarks']!='Pass*' && $class==2)
                                                                    {
                                                                        //CUSTOME COMMENT FAIL IN 2nd YEAR
                                                                        //CUSTOM COMMENT FAIL IN 1st YEAR
                                                                        if ($this->input->post('next_exam') == 'supplementary')
                                                                            $next_council_exam_no = $seq_supplementary[0]['second_year'];
                                                                        else
                                                                            $next_council_exam_no = $seq_annual[0]['second_year'];

                                                                        $custom_comment = 'Fail in Council exam # '.$council_exam_no.' This fee for next exam # '.($council_exam_no+1).' 2nd Year';
                                                                        $counts=(($council_exam_no+1)-($student[0]['exam_no']-1))+2;
                                                                        echo $custom_comment.'<br /> <br /> First Exam no : '.$student[0]['exam_no'].' <br /> <br /> TOTAL COUNTS = '.$counts;
                                                                    }
                                                                    elseif(($result['result_remarks']=='Pass' || $result['result_remarks']=='Pass*') && $class==1)
                                                                    {
                                                                        $next_council_exam_no = $seq_annual[0]['second_year'];
                                                                        $custom_comment = 'Pass in Council exam # '.$council_exam_no.' This fee for next exam # '.($next_council_exam_no).' 2nd Year';
                                                                        echo $custom_comment.'<br /> <br /> First Exam no : '.$student[0]['exam_no'];
                                                                    }
//                                                                }
                                                            }?>
                                                    </td>
                                                </tr>
                                                
                                                
                                                <?php
                                                    $this_student_id = @$student_data[0]['student_id'];
                                                    $council_fee_count = $this->db->get_where('payments',array('student_id'=>$this_student_id,'payment_plan'=>'consulation fee'))->result_array();
                                                    
                                                    $rule = $this->db->get('council_rules')->row();
                                                    $extra_fee=$rule->total_fee;
                                                    $no_of_exams=$rule->no_of_exams;
                                                    if(count($council_fee_count)>$no_of_exams):
                                                ?>
                                                <!-- START ADD EXTRA FEE IF COUNCIL EXAM COUNT GREATER THAN 4 -->
                                                <tr class="success">
                                                    <td class="hidden">
                                                        <?php echo $i;?>
                                                    </td>
                                                    <td>
                                                        <?php echo @$student_data[0]['campus_name'];?>
                                                    </td>
                                                    <td>
                                                        <?php echo @$student_data[0]['name'];?>
                                                    </td>
                                                    <td>
                                                        <?php echo @$student_data[0]['first_name'].' '.@$student_data[0]['last_name'];?>
                                                    </td>
                                                    <td>
                                                        <?php echo $result['cnic'];?>
                                                    </td>
                                                    <td>
                                                        <?php echo @$student_data[0]['roll_no'];?>
                                                    </td>
                                                    <td>
                                                        
                                                    </td>
                                                    <td>
                                                        
                                                    </td>
                                                    <td>
                                                        
                                                    </td>
                                                    <td>
                                                        
                                                    </td>
                                                    <td>
                                                        <?php
                                                            if(@$student_data[0]['contract_id']==0)
                                                            {
                                                                echo 'N/A';
                                                            }
                                                            else
                                                            {
                                                                $this->db->select('*');
                                                                $this->db->from('contracts');
                                                                $this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','INNER');
                                                                $this->db->where('contracts.contract_id',$student_data[0]['contract_id']);
                                                                $contractor_details = $this->db->get()->result_array();

                                                                echo $contractor_details[0]['name'];
                                                                echo '<br />';
                                                                echo $contractor_details[0]['contract_name'];
                                                                //echo $this->db->get_where('contractors', array('contractor_id'=>$student_data[0]['contractor_id']))->row()->name;
                                                            }
                                                        ?>

                                                    </td>
                                                    <td>
                                                        <?php echo $extra_fee;?>
                                                    </td>
                                                    <td>
                                                        <?php echo $this->input->post('dead_line');?>
                                                    </td>
                                                    <td>
                                                        <?php echo $result['address'];?>
                                                    </td>
                                                    <td>
                                                        
                                                        <?php
                                                            
                                                            $comm = 'This Extra Fee Created Agaist Council '.addOrdinalSuffix((count($council_fee_count))).' Fee.';
                                                            $check = $this->db->get_where('payments',array('student_id'=>$student[0]['student_id'],'payment_comment'=>$comm))->result_array();
                                                            
                                                            if(count($check) > 0)
                                                            {
                                                                echo 'Fee Created in System';
                                                            }
                                                            else
                                                            {
                                                                echo 'Fee Not Created in System';
                                                            }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php echo 'No of CF: '.count($council_fee_count);?>
                                                    </td>
                                                </tr>
                                                <?php
                                                    endif;
                                                ?>
                                                <!-- START ADD EXTRA FEE IF COUNCIL EXAM COUNT GREATER THAN 4 -->
                                                
                                                <!--- START DIPLOMA FEE SECTION ---->
                                                <?php
                                                    if(($result['result_remarks']=='Pass' || $result['result_remarks']=='Pass*') && $class==2):
                                                ?>
                                                <tr class="warning">
                                                    <td class="hidden">
                                                        <?php echo $i;?>
                                                    </td>
                                                    <td>
                                                        <?php echo @$student_data[0]['campus_name'];?>
                                                    </td>
                                                    <td>
                                                        <?php echo @$student_data[0]['name'];?>
                                                    </td>
                                                    <td>
                                                        <?php echo @$student_data[0]['first_name'].' '.@$student_data[0]['last_name'];?>
                                                    </td>
                                                    <td>
                                                        <?php echo $result['cnic'];?>
                                                    </td>
                                                    <td>
                                                        <?php echo @$student_data[0]['roll_no'];?>
                                                    </td>
                                                    <td>
                                                        
                                                    </td>
                                                    <td>
                                                        
                                                    </td>
                                                    <td>
                                                        
                                                    </td>
                                                    <td>
                                                        
                                                    </td>
                                                    <td>
                                                        <?php
                                                            if(@$student_data[0]['contract_id']==0)
                                                            {
                                                                echo 'N/A';
                                                            }
                                                            else
                                                            {
                                                                $this->db->select('*');
                                                                $this->db->from('contracts');
                                                                $this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','INNER');
                                                                $this->db->where('contracts.contract_id',$student_data[0]['contract_id']);
                                                                $contractor_details = $this->db->get()->result_array();

                                                                echo $contractor_details[0]['name'];
                                                                echo '<br />';
                                                                echo $contractor_details[0]['contract_name'];
                                                                //echo $this->db->get_where('contractors', array('contractor_id'=>$student_data[0]['contractor_id']))->row()->name;
                                                            }
                                                        ?>

                                                    </td>
                                                    <td>
                                                        <?php echo $this->input->post('diploma_fee');?>
                                                    </td>
                                                    <td>
                                                        <?php echo $this->input->post('dead_line');?>
                                                    </td>
                                                    <td>
                                                        <?php echo $result['address'];?>
                                                    </td>
                                                    <td>
                                                        
                                                        <?php
                                                            
                                                            $check = $this->db->get_where('payments',array('student_id'=>@$student_data[0]['student_id'],'payment_comment'=>'Extra Fee For Diploma'))->result_array();
                                                            
                                                            if(count($check) > 0)
                                                            {
                                                                echo 'Fee Created in System';
                                                            }
                                                            else
                                                            {
                                                                echo 'Fee Not Created in System';
                                                            }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        Diploma Fee
                                                    </td>
                                                </tr>
                                                <?php
                                                    endif;
                                                ?>
                                                <!-- END DIPLOMA FEE SECTION ----->
                                        <?php
                                                endif;
                                            $i++;
                                            endforeach;
                                            endif;
                                        ?>
                                    </tbody>
                                </table>
                            <input type="submit" class="btn green" value="Submit" />
                            </form>
						</div>
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>
            <?php
            	endif;
			?>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->