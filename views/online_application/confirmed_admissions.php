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
            
            <?php
				//if(@$myAccess[0]['dashboard_check_new_admission_clear_box']==1 || $this->session->userdata('role')=='Admin'):
			?>
            <div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-user"></i> All Online Applications
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
									 Name
								</th>
								<th>
									 Father Name
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
							</tr>
							</thead>
							<tbody>
							<?php
								$i=1;
								foreach($all_admissions as $clear_admission):
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
								$cnic_check = $this->db->get_where('students', array('cnic'=>$clear_admission['cnic']))->result_array();
								if($show==1 || $this->session->userdata('role')=='Admin'):
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
									 <?php echo $clear_admission['name'];?>
								</td>
								<td>
									<?php echo $clear_admission['father_name'];?>
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
										$comments = $this->db->get_where('online_application_comments', array('apply_now_id'=>$clear_admission['apply_now_id']))->result_array();
										foreach($comments as $comment):
									?>
									<p>
										Interest Type : <?php echo $comment['interest_type'];?><br />
										<?php if($comment['next_date_for_call']!='0000-00-00'):?>
										Next Call Date : <?php echo $comment['next_date_for_call'];?><br />
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
							</tr>
                            <?php
								$i++;
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
            <?php
            	//endif;
			?>
            <!---END CLASSES STATUS--->
		</div>
	</div>
	<!-- END CONTENT -->
</div>
<!-- END CONTAINER -->
