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
			<div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-users"></i> All User Admission Comission
							</div>
						</div>
						<div class="portlet-body">
							<table class="table table-striped table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
								<th class="hidden">
                                	 hidden
                                </th>
                                <th>
									 Assigned Campuses
								</th>
								<th>
									 Designation / Department
								</th>
								<th>
									 Information
								</th>
								<th>
									 Rules
								</th>
								
								<th>
									 Courses
								</th>
								<th>
									 Comission
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($users as $user):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php
									 	$campus_ids = explode(',',$user['campus_ids']);
									 	$this->db->where_in('campus_id',$campus_ids);
									 	$campuses = $this->db->get('campuses')->result_array();
										foreach($campuses as $campus)
										{
											echo $campus['campus_name'];
											echo '<br />';
										}
									 ?>
								</td>
								<td>
									<?php 
										echo 'Department : '.$user['department_name'].'<br />';
										echo 'Designation : '.$user['designation_name'].'<br />';
										
									?>
								</td>
								<td>
									 <?php 
										$rules = $this->db->get_where('users','(designation_id ="'.$user['designation_id'].'" or designation_id like "%'.$user['designation_id'].',%" or designation_id like "%,'.$user['designation_id'].'%") and status = "1"')->result_array();
										foreach($rules as $rule)
										{
											echo 'User : '.$rule['first_name'].'-'.$rule['last_name'].' <a target="_blank" href="'.site_url().'/admission_management/check_recovery/'.$user['incentive_id'].'/'.$rule['user_id'].'"><i class="fa fa-eye"></i></a>
                                           <br />';
										}
									 ?>
								</td>
								
								
								<td>
									 <?php 
										if($user['user_or_campus'] == '0')
										{
											echo "User Admission Count";
										}
										else
										{
											echo "College Admission Count";
											echo "<br />";
											
											if($user['own_count'] == '0')
											{
												echo "Own Admisions Not Count";
											}else
											{
												echo "Own Admisions Will Count";
											}
											
										}
									 ?>
								</td>
								
								<td>
									 <?php 
										$courses = explode(',',$user['course_id']);
									 	$this->db->where_in('course_id',$courses);
									 	$campuses = $this->db->get('courses')->result_array();
										foreach($campuses as $rule)
										{
											echo 'Course : '.$rule['course_name'].'<br />';
										}
									 ?>
								</td>
								<td>
									 <?php 
										$rules = $this->db->get_where('admission_management_rules',array('admission_incentive_id'=>$user['incentive_id']))->result_array();
										foreach($rules as $rule)
										{
											echo 'Comission : '.$rule['start'].'-'.$rule['end'].' = Rs '.$rule['comission'].'<br />';
										}
									 ?>
								</td>
								<td>
                                    <a href="<?php echo site_url().'/admission_management/check_recovery/'.$user['incentive_id'];?>" title="Check Recovery" class="btn purple"><i class="fa fa-money"></i></a>
                                   
                                    <a onclick="return confirm('Are you sure you want to delete this Rule?')" href="<?php echo site_url().'/admission_management/delete/'.$user['incentive_id'];?>" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>
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
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->