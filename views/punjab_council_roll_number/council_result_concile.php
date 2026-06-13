
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
								<i class="fa fa-list"></i> Council Result Conciliation
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/punjab_council_roll_number/council_result_concile" enctype="multipart/form-data">
								<div class="form-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group  col-md-6">
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
											 <div class="form-group col-md-6">
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
                                           
											
											<div class="form-group col-md-6">
                                                <label class="col-md-3 control-label"> Campus <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select name="campus_id" class="form-control input-inline input-large campus_id">
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
											
											<div class="form-group col-md-6">
												<label class="col-md-3 control-label">Session <span class="required">*</span></label>
												<div class="col-md-9">
													<select class="form-control classes" name="class_id">
													</select>
													
												</div>
											</div>
											
                                        </div>
                                        <div class="col-md-12">
                                            
                                        </div>
                                        
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<input type="hidden" name="add_council_fee" value="1" />
                                            <button type="submit" class="btn green">Check Result</button>
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
								<i class="fa fa-list"></i> Results
							</div>
						</div>
						<div class="portlet-body">
                           
                            <table class="table table-bordered table-hover" >
							<thead>
							<tr>
                                <th>
									 Sr
								</th>
								<th>
									 Campus
								</th>
                                <th>
									 Class
								</th>
								 <th>
									 Session
								</th>
                                <th>
                                    Exam #
                                </th>
                                <th>
									 PASS
								</th>
								<th>
									 FAIL
								</th>
                                <th>
									 Fail(Absent)
								</th>
                                <th>
                                	 Fail(Must Appear in all)
                                </th>
                                <th>
									 Fail(Paper 1)
								</th>
                                <th>
                                	 Fail(Paper 2)
                                </th>
                                <th>
									 Fail(Paper 3)
								</th>
                                <th>
									 Fail(Paper 4)
								</th>
                                <th>
									 Fail(Paper 5)
								</th>
                                <th>
									 Fail(Paper 6)
								</th> 
								<th>
									 Last Chance
								</th>
								<th>
									 Next two Chance
								</th>
								<th>
									 Only Fail In Theory
								</th>
								<th>
									 Only Fail in Practical
								</th>
                               
							</tr>
							</thead>
							<tbody>
                            
							<?php
								$i=0;
								$pass=0;
								$fail=0;
								$failabsent=0;
								$failall=0;
								$fail1=0;
								$fail2=0;
								$fail3=0;
								$fail4=0;
								$fail5=0;
								$fail6=0;
								$lastchance=0;
								$next2chance=0;
								$onfailinpractical=0;
								$onfailintheory=0;
								foreach(@$results as $result){

                                    $this->db->select('*');
                                    $this->db->from('students');
                                    $this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
                                    $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
                                    $this->db->where('students.cnic', $result['cnic']);
                                    $student_data = $this->db->get()->result_array();

                                    if((@$student_data[0]['campus_id']==@$this->input->post('campus_id') || @$student_data[0]['campus_id']=='') && (@$student_data[0]['class_id']==@$this->input->post('class_id') || @$student_data[0]['class_id']=='') ) {


                                        if (strpos($result['result_remarks'], 'Pass') !== false) {
                                            $pass++;
                                        } elseif ($result['result_remarks'] == 'Fail') {
                                            $fail++;
                                        } elseif (strpos($result['result_remarks'], 'Fail') !== false && strpos($result['result_remarks'], 'Absent') !== false) {
                                            $failabsent++;
                                        } elseif (strpos($result['result_remarks'], 'Fail') !== false && strpos($result['result_remarks'], 'appear in all') !== false) {
                                            $failall++;
                                        } elseif (strpos($result['result_remarks'], 'Fail') !== false && strpos($result['result_remarks'], '1') !== false) {
                                            $fail1++;
                                        } elseif (strpos($result['result_remarks'], 'Fail') !== false && strpos($result['result_remarks'], '2') !== false) {
                                            $fail2++;
                                        } elseif (strpos($result['result_remarks'], 'Fail') !== false && strpos($result['result_remarks'], '3') !== false) {
                                            $fail3++;
                                        } elseif (strpos($result['result_remarks'], 'Fail') !== false && strpos($result['result_remarks'], '4') !== false) {
                                            $fail4++;
                                        } elseif (strpos($result['result_remarks'], 'Fail') !== false && strpos($result['result_remarks'], '5') !== false) {
                                            $fail5++;
                                        } elseif (strpos($result['result_remarks'], 'Fail') !== false && strpos($result['result_remarks'], '6') !== false) {
                                            $fail6++;
                                        } elseif (strpos($result['result_remarks'], 'Fail') !== false && strpos($result['result_remarks'], 'Last Chance') !== false) {
                                            $lastchance++;
                                        } elseif (strpos($result['result_remarks'], 'Fail') !== false && strpos($result['result_remarks'], 'Next Two') !== false) {
                                            $next2chance++;
                                        }


                                        if (strpos($result['result_remarks'], 'Fail') !== false &&
                                            (strpos($result['result_remarks'], '3') !== false ||
                                                strpos($result['result_remarks'], '4') !== false ||
                                                strpos($result['result_remarks'], '5') !== false ||
                                                strpos($result['result_remarks'], '6') !== false)) {
                                            $onfailinpractical++;
                                        } else {

                                            $onfailintheory++;
                                        }
                                    }


								}

								$this->db->select('*');
								$this->db->from('students');
								$this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
								$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
								$this->db->where('students.cnic', $results[0]['cnic']);
								$student_data = $this->db->get()->result_array();


							?>
                            <tr>
                                <td>
                                	<?php echo $i+1;?>
                                </td>
                                <td>
									<?php echo @$student_data[0]['campus_name'];?>
								</td>

                                <td>
                                    <?php echo '1st Year';?>
                                </td> 
								
								<td>
                                    <?php echo @$student_data[0]['class_name'];?>
                                </td>
                                <td>
                                    <?php echo $this->input->post('council_exam_no');?>

                                </td>

                                <td>
									<?php echo $pass;?>

								</td>
                                <td>
                                    <?php echo $fail;?>

                                </td>
                                <td>
                                    <?php echo $failabsent;?>

                                </td>
                                <td>
                                    <?php echo $failall;?>

                                </td>
                                <td>
                                    <?php echo $fail1;?>

                                </td>
                                <td>
                                    <?php echo $fail2;?>

                                </td>
                                <td>
                                    <?php echo $fail3;?>

                                </td>
                                <td>
                                    <?php echo $fail4;?>

                                </td>
                                <td>
                                    <?php echo $fail5;?>

                                </td>
                                <td>
                                    <?php echo $fail6;?>

                                </td>
                                <td>
                                    <?php echo $lastchance;?>

                                </td>
                                <td>
                                    <?php echo $next2chance;?>

                                </td>
                                <td>
                                    <?php echo $onfailintheory;?>

                                </td>
                                <td>
                                    <?php echo $onfailinpractical;?>

                                </td>

                            </tr>

                            
							</tbody>
							</table>
                           
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