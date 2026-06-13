<?php
	$myAccess = checkUserAccess();
?>
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<h3 class="page-title">
			Pending Applications <small>reports & statistics</small>
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
								<i class="fa fa-user"></i> Today Calls for Pending Applications
							</div>
						</div>
                        <div class="portlet-body">
							<table class="table table-bordered table-hover" id="sample_11">
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
									 Comment
								</th>
                                <th>
									 Last Edit
								</th>
                                <th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=1;
								foreach($clear_admissions as $clear_admission):
                                    $this->db->order_by('next_date_for_call', 'DESC');
                                    $comments = $this->db->get_where('online_application_comments', array('apply_now_id'=>$clear_admission['apply_now_id']))->result_array();
                                    if($comments[0]['next_date_for_call']<=date('Y-m-d') && $comments[count($comments)-1]['add_by'] == $this->session->userdata('name')):
                                        $show=1;

                                        //CNIC CHECK
                                        if($clear_admission['cnic']!='')
                                        {
                                            $cnic_check = $this->db->get_where('students', array('cnic'=>$clear_admission['cnic']))->result_array();
                                        }
                                        else
                                        {
                                            $cnic_check = array();
                                        }
                                        //MOBILE CHECK
                                        if($clear_admission['cnic']=='')
                                        {
                                            $this->db->where("(mobile='".$clear_admission['mobile']."' OR emergency_no='".$clear_admission['mobile']."')", NULL, FALSE);
                                            $mobile_check = $this->db->get('students')->result_array();
                                        }
                                        else
                                        {
                                            $mobile_check = array();
                                        }
                                        if($show==1 || $this->session->userdata('role')=='Admin'):

                                            //SYSTEM COMMENT SECTION
                                            $msg='';
                                            $this->db->where("(mobile='".$clear_admission['mobile']."' OR emergency_no='".$clear_admission['mobile']."')", NULL, FALSE);
                                            $check_double_entry = $this->db->get('apply_now')->result_array();

                                            if(count($check_double_entry)>1)
								{
									$apply_now_ids = array();
									foreach($check_double_entry as $comment)
									{
										array_push($apply_now_ids,$comment['apply_now_id']);
									}
									
									$this->db->where_in('apply_now_id',$apply_now_ids);
									$comments = @$this->db->get('online_application_comments')->result_array();
									
									$this->db->where('apply_now_id!=',$clear_admission['apply_now_id']);
									$this->db->where("(mobile='".$clear_admission['mobile']."' OR emergency_no='".$clear_admission['mobile']."')", NULL, FALSE);
									$entries = $this->db->get('apply_now')->result_array();
									foreach($entries as $entry)
									{
										$campus_name = @$this->db->get_where('campuses',array('website'=>str_replace('/','',str_replace('https://www.','',$entry['website']))))->row()->campus_name;
										$msg.='Student Already apply in '.$campus_name.' on '.date('Y-m-d',strtotime($entry['date'])).'<br /><hr />';
									}
								}
							?>
                            <tr class="odd gradeX <?php if(count($cnic_check)>0){echo 'alert-success';}?> <?php if(count($mobile_check)>0){echo 'alert-success';}?>">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
                                <td>
									 <?php echo date('Y-m-d',strtotime($clear_admission['date']));?>
								</td>
                                <td>
									 <?php echo date('H:i:s A',strtotime($clear_admission['date']));?>
								</td>
                                <td>
									 <?php echo $clear_admission['website'];?>
								</td>
                                <td>
                                    <?php echo $clear_admission['mobile_name'];?>
                                </td>
                                <td>
									 <?php echo $clear_admission['name'];?>
								</td>
								<td>
									<?php echo $clear_admission['father_name'];?>
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
									<?php echo $clear_admission['cnic']?>
								</td>
                                <td>
									<?php echo $clear_admission['gender']?>
								</td>
                                <td>
									<?php echo $clear_admission['date_of_birth'];?>
								</td>
                                <td>
                                	<?php echo $clear_admission['education'];?>
								</td>
                                <td>
                                	<?php echo $clear_admission['city'];?>
								</td>
                                <td>
                                	<?php echo $clear_admission['address'];?>
                                </td>
                                <td>
                                	<?php echo $clear_admission['mobile'];?>
                                </td>
								<td>
                                	<?php echo $clear_admission['emergency_no'];?>
								</td>
                                <td>
                                	<?php echo $clear_admission['comment'];?>
									<hr />
									<?php
										//$comments = $this->db->get_where('online_application_comments', array('apply_now_id'=>$clear_admission['apply_now_id']))->result_array();
										foreach($comments as $comment):
									?>
									<p>
										Type : <strong><?php echo $comment['interest_type'];?></strong><br />
										<?php if($comment['next_date_for_call']!='0000-00-00'):?>
										Next Call Date : <strong><?php echo $comment['next_date_for_call'];?></strong><br />
										<?php endif;?>
										Comment : <?php echo $comment['comment'];?><br />
										Add by : <?php echo $comment['add_by'];?><br />
									</p>
									<?php
										endforeach;
									?>
								</td>
                                <td>
                                	<?php echo $clear_admission['last_edit'];?>
								</td>
                                <td>
                                	<button type="button" class="btn green add_comment" data-apply-now-id="<?php echo $clear_admission['apply_now_id'];?>" data-toggle="modal" href="#basic">Add Comment</button>
									<form action="<?php echo site_url();?>/online_application/pending_applications" method="post">
                                        <input type="hidden" name="apply_now_id" value="<?php echo $clear_admission['apply_now_id'];?>" />
                                        <input type="hidden" name="clear_new_admission" value="1" />
                                        <button type="submit" class="btn green">Clear</button>
                                    </form>
								</td>
							</tr>
                            <?php
                                            //endif;
                                            $i++;
                                        endif;
                                    endif;
                            	endforeach;
							?>
							</tbody>
							</table>
						</div>
                        
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
            <!---END CLASSES STATUS--->
			<div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-user"></i> Future Calls for Pending Applications
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
									 Comment
								</th>
                                <th>
									 Last Edit
								</th>
                                <th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=1;
								foreach($clear_admissions as $clear_admission):
								
								$this->db->order_by('next_date_for_call', 'DESC');
								$comments = $this->db->get_where('online_application_comments', array('apply_now_id'=>$clear_admission['apply_now_id']))->result_array();
								if($comments[0]['next_date_for_call']>date('Y-m-d')  && $comments[count($comments)-1]['add_by'] == $this->session->userdata('name')):
									
									$show=0;
									if($this->session->userdata('role')!='Admin')
									{
										$campus_id = @$this->db->get_where('campuses', array('website'=>str_replace('/','',str_replace('https://www.','',$clear_admission['website']))))->row()->campus_id;
										
										$check_access = $this->db->get_where('online_application_access',array('campus_id'=>$campus_id,'city'=>$clear_admission['city'],'user_id'=>$this->session->userdata('user_id')))->result_array();
										//print_r($check_access);
										if(count($check_access)>0)
										{
											$show=1;
										}
										if($show==0)
										{
											$first_check = $this->db->get_where('online_application_access',array('campus_id'=>$campus_id,'city'=>$clear_admission['city']))->result_array();
											if(count($first_check)<1)
											{
												$second_check = $this->db->get_where('online_application_access',array('campus_id'=>$campus_id,'city!='=>$clear_admission['city'],'all_cities'=>1,'user_id'=>$this->session->userdata('user_id')))->result_array();
												if(count($second_check)>0)
												{
													$show=1;
												}
											}
										}
									}
									//CNIC CHECK
								if($clear_admission['cnic']!='')
								{
									$cnic_check = $this->db->get_where('students', array('cnic'=>$clear_admission['cnic']))->result_array();
								}
								else
								{
									$cnic_check = array();
								}
								//MOBILE CHECK
								if($clear_admission['cnic']=='')
								{
									$this->db->where("(mobile='".$clear_admission['mobile']."' OR emergency_no='".$clear_admission['mobile']."')", NULL, FALSE);
									$mobile_check = $this->db->get('students')->result_array();
								}
								else
								{
									$mobile_check = array();
								}
								if($show==1 || $this->session->userdata('role')=='Admin'):
								
								//SYSTEM COMMENT SECTION
								$msg='';
								$this->db->where("(mobile='".$clear_admission['mobile']."' OR emergency_no='".$clear_admission['mobile']."')", NULL, FALSE);
								$check_double_entry = $this->db->get('apply_now')->result_array();
								
								if(count($check_double_entry)>1)
								{
									$apply_now_ids = array();
									foreach($check_double_entry as $comment)
									{
										array_push($apply_now_ids,$comment['apply_now_id']);
									}
									
									$this->db->where_in('apply_now_id',$apply_now_ids);
									$comments = @$this->db->get('online_application_comments')->result_array();
									
									$this->db->where('apply_now_id!=',$clear_admission['apply_now_id']);
									$this->db->where("(mobile='".$clear_admission['mobile']."' OR emergency_no='".$clear_admission['mobile']."')", NULL, FALSE);
									$entries = $this->db->get('apply_now')->result_array();
									foreach($entries as $entry)
									{
										$campus_name = @$this->db->get_where('campuses',array('website'=>str_replace('/','',str_replace('https://www.','',$entry['website']))))->row()->campus_name;
										$msg.='Student Already apply in '.$campus_name.' on '.date('Y-m-d',strtotime($entry['date'])).'<br /><hr />';
									}
								}
							?>
                            <tr class="odd gradeX <?php if(count($cnic_check)>0){echo 'alert-success';}?>">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
                                <td>
									 <?php echo date('Y-m-d',strtotime($clear_admission['date']));?>
								</td>
                                <td>
									 <?php echo date('H:i:s A',strtotime($clear_admission['date']));?>
								</td>
                                <td>
									 <?php echo $clear_admission['website'];?>
								</td>
                                <td>
                                    <?php echo $clear_admission['mobile_name'];?>
                                </td>
                                <td>
									 <?php echo $clear_admission['name'];?>
								</td>
								<td>
									<?php echo $clear_admission['father_name'];?>
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
									<?php echo $clear_admission['cnic']?>
								</td>
                                <td>
									<?php echo $clear_admission['gender']?>
								</td>
                                <td>
									<?php echo $clear_admission['date_of_birth'];?>
								</td>
                                <td>
                                	<?php echo $clear_admission['education'];?>
								</td>
                                <td>
                                	<?php echo $clear_admission['city'];?>
								</td>
                                <td>
                                	<?php echo $clear_admission['address'];?>
                                </td>
                                <td>
                                	<?php echo $clear_admission['mobile'];?>
                                </td>
								<td>
                                	<?php echo $clear_admission['emergency_no'];?>
								</td>
                                <td>
                                	<?php echo $clear_admission['comment'];?>
									<hr />
									<?php
										foreach($comments as $comment):
									?>
									<p>
										Type : <strong><?php echo $comment['interest_type'];?></strong><br />
										<?php if($comment['next_date_for_call']!='0000-00-00'):?>
										Next Call Date : <strong><?php echo $comment['next_date_for_call'];?></strong><br />
										<?php endif;?>
										Comment : <?php echo $comment['comment'];?><br />
										Add by : <?php echo $comment['add_by'];?><br />
									</p>
									<?php
										endforeach;
									?>
								</td>
                                <td>
                                	<?php echo $clear_admission['last_edit'];?>
								</td>
                                <td>
                                	<button type="button" class="btn green add_comment" data-apply-now-id="<?php echo $clear_admission['apply_now_id'];?>" data-toggle="modal" href="#basic">Add Comment</button>
									<!--<form action="<?php echo site_url();?>/online_application/pending_applications" method="post">
                                        <input type="hidden" name="apply_now_id" value="<?php echo $clear_admission['apply_now_id'];?>" />
                                        <input type="hidden" name="clear_new_admission" value="1" />
                                        <button type="submit" class="btn green">Clear</button>
                                    </form>-->
								</td>
							</tr>
                            <?php
								$i++;
								endif;
								endif;
                            	endforeach;
							?>
							</tbody>
							</table>
						</div>
                        
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
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
