<?php
	$myAccess = checkUserAccess();
?>
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<h3 class="page-title">
			Online Applications <small>reports & statistics</small>
			</h3>
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



            <div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-user"></i> Online Applications for Admission
							</div>
						</div>
                        <div class="portlet-body">
							<table class="table table-bordered table-hover" id="sample_10">
							<thead>
							<tr>
                                <th class="hidden">
									 Hidden
								</th>
                                <th>
									 Date
								</th>
                                <th>
                                	Time
                                </th>
                                <th>
									 Website
								</th>
                                <th>
                                    Mobile Name
                                </th>
                                <th>
									 Name
								</th>
								<th>
									 Father Name
								</th>
								<th>
									System Comment
								</th>
								<th>
									About Student
								</th>
								<th>
									Previous Comment
								</th>
                                <th>
									 CNIC
								</th>
                                <th>
									 Gender
								</th>
                                <th>
									 Date of Birth
								</th>
                                <th>
									 Education
								</th>
                                <th>
                                	City
                                </th>
                                <th>
									 Address
								</th>
                                <th>
									 Mobile
								</th>
                                <th>
									 Emergency Number
								</th>
                                <th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=1;
								//echo count($admissions);
								foreach($admissions as $admission):
								$show=1;
//								if($this->session->userdata('role')!='Admin')
//								{
//									$campus_id = @$this->db->get_where('campuses', array('website'=>str_replace('/','',str_replace('https://www.','',$admission['website']))))->row()->campus_id;
//
//									$check_access = $this->db->get_where('online_application_access',array('campus_id'=>$campus_id,'city'=>$admission['city'],'user_id'=>$this->session->userdata('user_id')))->result_array();
//									//print_r($check_access);
//									if(count($check_access)>0)
//									{
//										$show=1;
//									}
//									if($show==0)
//									{
//										$second_check = $this->db->get_where('online_application_access',array('campus_id'=>$campus_id,'all_cities'=>1,'user_id'=>$this->session->userdata('user_id')))->result_array();
//										if(count($second_check)>0)
//										{
//											$show=1;
//										}
//									}
//								}
								//CNIC CHECK
								if($admission['cnic']!='')
								{
									$cnic_check = $this->db->get_where('students', array('cnic'=>$admission['cnic']))->result_array();
								}
								else
								{
									$cnic_check = array();
								}
								if($admission['cnic']=='')
                                {
                                    $mobile = trim($admission['mobile']);
                                
                                    if($mobile != '' && preg_match('/^[0-9+\-\s]+$/', $mobile))
                                    {
                                        $this->db->group_start();
                                        $this->db->where('mobile', $mobile);
                                        $this->db->or_where('emergency_no', $mobile);
                                        $this->db->group_end();
                                
                                        $mobile_check = $this->db->get('students')->result_array();
                                    }
                                    else
                                    {
                                        $mobile_check = array();
                                    }
                                }
                                else
                                {
                                    $mobile_check = array();
                                }
								
								
								//SYSTEM COMMENT SECTION
								$msg='';
								$msg = '';
                                $mobile = trim($admission['mobile']);
                                
                                if($mobile != '' && preg_match('/^[0-9+\-\s]+$/', $mobile))
                                {
                                    $this->db->group_start();
                                    $this->db->where('mobile', $mobile);
                                    $this->db->or_where('emergency_no', $mobile);
                                    $this->db->group_end();
                                
                                    $check_double_entry = $this->db->get('apply_now')->result_array();
                                }
                                else
                                {
                                    $check_double_entry = array();
                                }
								
								if(count($check_double_entry)>1)
								{
									$apply_now_ids = array();
									foreach($check_double_entry as $comment)
									{
										array_push($apply_now_ids,$comment['apply_now_id']);
									}
									
									$this->db->where_in('apply_now_id',$apply_now_ids);
									$comments = @$this->db->get('online_application_comments')->result_array();
									
									$this->db->where('apply_now_id!=',$admission['apply_now_id']);
									$this->db->where("(mobile='".$admission['mobile']."' OR emergency_no='".$admission['mobile']."')", NULL, FALSE);
									$entries = $this->db->get('apply_now')->result_array();
									foreach($entries as $entry)
									{
										$campus_name = @$this->db->get_where('campuses',array('website'=>str_replace('/','',str_replace('https://www.','',$entry['website']))))->row()->campus_name;
										$msg.='Student Already apply in '.$campus_name.' on '.date('Y-m-d',strtotime($entry['date'])).'<br /><hr />';
									}
								}
								if($show==1 || $this->session->userdata('role')=='Admin'):
							?>
                            <tr class="odd gradeX <?php if(count($cnic_check)>0){echo 'alert-success';}?> <?php if(count($mobile_check)>0){echo 'alert-success';}?>">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
                                <td>
									 <?php echo date('Y-m-d',strtotime($admission['date']));?>
								</td>
                                <td>
									 <?php echo date('H:i:s A',strtotime($admission['date']));?>
								</td>
                                <td>
									 <?php echo $admission['website'];?> 
								</td>
                                <td>
                                    <?php echo $admission['mobile_name'];?>
                                </td>
                                <td>
									 <?php echo $admission['name'];?>
								</td>
								<td>
									<?php echo $admission['father_name'];?>
								</td>
                                <td>
									<?php echo $msg;?>
								</td>
								<td>
									<?php
										if(count($cnic_check)>0)
										{
											foreach($cnic_check as $student)
											{
												if($student['status']==1)
												{
													$status='Active';
												}
												else
												{
													$status='Deactive';
												}
												echo 'Student already exist against roll no <strong>'.$student['roll_no'].'</strong> and status is <strong>'.$status.'</strong> and admission date is '.$student['registration_date'].'<br /><hr />';
											}
										}
										if(count($mobile_check)>0)
										{
											foreach($mobile_check as $student)
											{
												if($student['status']==1)
												{
													$status='Active';
												}
												else
												{
													$status='Deactive';
												}
												echo 'Student already exist against roll no <strong>'.$student['roll_no'].'</strong> and status is <strong>'.$status.'</strong> and admission date is '.$student['registration_date'].'<br /><hr />';
											}
										}
									?>
								</td>
								<td>
									<?php 
										if(count($check_double_entry)>1):
										foreach($comments as $comment):
									?>
									<p>
										Type : <strong><?php echo @$comment['interest_type'];?></strong><br />
										<?php if(@$comment['next_date_for_call']!='0000-00-00'):?>
										Next Call Date : <strong><?php echo @$comment['next_date_for_call'];?></strong><br />
										<?php endif;?>
										Comment : <?php echo @$comment['comment'];?><br />
										Add by : <?php echo @$comment['add_by'];?><br />
									</p>
									<?php
										endforeach;
										endif;
									?>
								</td>
								<td>
									<?php echo $admission['cnic']?>
								</td>
                                <td>
									<?php echo $admission['gender']?>
								</td>
                                <td>
									<?php echo $admission['date_of_birth'];?>
								</td>
                                <td>
                                	<?php echo $admission['education'];?>
								</td>
                                <td>
                                	<?php echo $admission['city'];?>
                                </td>
                                <td>
                                	<?php echo $admission['address'];?>
                                </td>
                                <td>
                                	<?php echo $admission['mobile'];?>
                                </td>
								<td>
                                	<?php echo $admission['emergency_no'];?>
								</td>
                                <td>
                                    <button type="button" class="btn green add_comment" data-apply-now-id="<?php echo $admission['apply_now_id'];?>" data-toggle="modal" href="#basic">Add Comment</button>
								</td>
							</tr>
                            <?php
								//endif;
								$i++;
								endif;
                            	endforeach;

                                foreach($mobile_admissions as $admission):
                                    $show = 1;
//                                    if($this->session->userdata('role')!='Admin')
//                                    {
//                                        $campus_id = @$this->db->get_where('campuses', array('website'=>str_replace('/','',str_replace('https://www.','',$admission['website']))))->row()->campus_id;
//
//                                        $check_access = $this->db->get_where('online_application_access',array('campus_id'=>$campus_id,'city'=>$admission['city'],'user_id'=>$this->session->userdata('user_id')))->result_array();
//                                        //print_r($check_access);
//                                        if(count($check_access)>0)
//                                        {
//                                            $show=1;
//                                        }
//                                        if($show==0)
//                                        {
//                                            $second_check = $this->db->get_where('online_application_access',array('campus_id'=>$campus_id,'all_cities'=>1,'user_id'=>$this->session->userdata('user_id')))->result_array();
//                                            if(count($second_check)>0)
//                                            {
//                                                $show=1;
//                                            }
//                                        }
//                                    }
                                    if ($show == 1):
                                    ?>
                                    <tr class="odd gradeX">
                                        <td class="hidden">
                                            <?php echo $i;?>
                                        </td>
                                        <td>
                                            <?php echo date('Y-m-d',strtotime($admission['entry_date']));?>
                                        </td>
                                        <td>
                                            <?php echo date('H:i:s A',strtotime($admission['entry_date']));?>
                                        </td>
                                        <td>
                                            Mobile App
                                        </td>
                                        <td>

                                        </td>
                                        <td>
                                            <?php echo $admission['first_name'].' '.$admission['last_name'];?>
                                        </td>
                                        <td>
                                            <?php echo $admission['father_name'];?>
                                        </td>
                                        <td>

                                        </td>
                                        <td>

                                        </td>
                                        <td>

                                        </td>
                                        <td>
                                            <?php echo $admission['cnic']?>
                                        </td>
                                        <td>
                                            <?php echo $admission['gender']?>
                                        </td>
                                        <td>
                                            <?php echo $admission['date_of_birth'];?>
                                        </td>
                                        <td>
                                            <?php
                                                $qual = json_decode($admission['qualification'],true);
                                                if (@$qual['Qualification']) {
                                                    if ($qual['Qualification']['Matriculation'] == true) {
                                                        echo "Matric <br/>";
                                                    }
                                                    if ($qual['Qualification']['Intermediate'] == true) {
                                                        echo "Intermediate";
                                                    }
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <?php echo $admission['city'];?>
                                        </td>
                                        <td>
                                            <?php echo $admission['address'];?>
                                        </td>
                                        <td>
                                            <?php echo $admission['mobile'];?>
                                        </td>
                                        <td>
                                            <?php echo $admission['emergency_no'];?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn green add_comment" data-apply-now-id="<?php echo $admission['application_id'];?>" data-toggle="modal" href="#basic">Add Comment</button>
                                        </td>
                                    </tr>
                                    <?php
                                    endif;
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
            
            <?php if(!empty($dynamic_submissions)): ?>
            <div class="row">
                <div class="col-md-12 ">
                    <div class="portlet box green ">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-list"></i> Dynamic Form Submissions
                            </div>
                        </div>
                        <div class="portlet-body table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Form</th>
                                    <th>Submitted Data</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach($dynamic_submissions as $submission): ?>
                                    <?php
                                        $values = $this->db
                                            ->where('submission_id', $submission['id'])
                                            ->order_by('id', 'ASC')
                                            ->get('dynamic_form_submission_values')
                                            ->result_array();
                                    ?>
                                    <tr>
                                        <td><?php echo $submission['id']; ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($submission['created_at'])); ?></td>
                                        <td><?php echo date('H:i:s A', strtotime($submission['created_at'])); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($submission['form_title']); ?><br>
                                            <small><?php echo htmlspecialchars($submission['slug']); ?></small>
                                        </td>
                                        <td>
                                            <?php foreach($values as $value): ?>
                                                <strong><?php echo htmlspecialchars($value['field_label']); ?>:</strong>
                                                <?php if($value['field_type'] == 'file' && $value['value'] != ''): ?>
                                                    <a href="<?php echo base_url();?>uploads/<?php echo htmlspecialchars($value['value']); ?>" target="_blank">View File</a>
                                                <?php else: ?>
                                                    <?php echo nl2br(htmlspecialchars($value['value'])); ?>
                                                <?php endif; ?>
                                                <br>
                                            <?php endforeach; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo site_url();?>/online_application/dynamic_submission_checked/<?php echo $submission['id']; ?>" class="btn green" onclick="return confirm('Mark this submission as checked?')">Mark Checked</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!---END CLASSES STATUS--->
		</div>
	</div>
	<!-- END CONTENT -->
</div>
<!-- END CONTAINER -->
<div class="modal fade" id="basic" tabindex="-1" role="basic" aria-hidden="true">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
											<h4 class="modal-title">Add comment about this student.</h4>
										</div>
										<div class="modal-body">
											 <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/online_application/add_comment">
                                                <div class="form-body">
                                                    <div class="form-group">
                                                        <label class="col-md-4 control-label">Interest Type</label>
                                                        <div class="col-md-8 radio-list">
                                                            <label class="radio-inline">
                                                            <input type="radio" class="interest_type" name="interest_type" id="optionsRadios1" value="Interested" checked> Interested </label>
                                                            
                                                            <label class="radio-inline">
                                                            <input type="radio" class="interest_type" name="interest_type" id="optionsRadios2" value="Not Interested"> Not Interested </label>
															
															<label class="radio-inline">
                                                            <input type="radio" class="interest_type" name="interest_type" id="optionsRadios3" value="Stay For Future"> Stay For Future </label>
                                                        </div>
                                                    </div>
													<div class="form-group">
                                                        <label class="col-md-4 control-label">Next Date for Call</label>
                                                        <div class="col-md-8 checkbox-list">
															<label class="checkbox-inline">
															<input type="checkbox" id="inlineCheckbox1" class="date" name="date" value="1" checked="checked" /> Check to enter date </label>
														</div>
                                                    </div>
													
													<div class="form-group next_date_for_call">
														<label class="control-label col-md-4">Date</label>
														<div class="col-md-8">
															<div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
																<input type="text" name="next_date_for_call" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
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
                                                        <label class="col-md-4 control-label">Comments</label>
                                                        <div class="col-md-8">
                                                                <textarea class="form-control remarks" rows="3" name="comment"></textarea>
                                                        </div>
                                                    </div>
                                                    
                                                </div>
                                                
                                                <div class="form-actions">
                                                    <div class="row">
                                                        <div class="col-md-offset-3 col-md-9">
                                                            <input type="hidden" name="add_by" value="<?php echo $this->session->userdata('name');?>" />
															<input type="hidden" name="apply_now_id" class="apply_now_id" value="" />
                                                            <button type="submit" class="btn green">Add Comment</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn default close_button" data-dismiss="modal">Close</button>
										</div>
									</div>
									<!-- /.modal-content -->
								</div>
								<!-- /.modal-dialog -->
							</div>
