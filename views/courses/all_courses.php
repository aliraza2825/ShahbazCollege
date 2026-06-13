
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Add Class <small>add your desire class</small>
			</h3>-->
			<!-- END PAGE HEADER-->
            <!-- BEGIN DASHBOARD STATS -->
			
			<!-- END DASHBOARD STATS -->
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
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i>All Courses
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
									 ID
								</th>
								<th>
									 Campuses
								</th>
								<th>
									 Course Name
								</th>
								<th>
									 Course Code
								</th>
                                <th>
									 Course Type
								</th>
								<th>
									 Course Duration
								</th>
								<th>
									 Course Semester
								</th>
                                <th>
									 Add By
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
                            	$i = 0;
								foreach($courses as $course):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
                                    <?php echo $course['course_id']?>
                                </td>
								<td>
									 <?php 
                                    	$campuses = explode(',',$course['campus_ids']);
										foreach($campuses as $campus)
										{
											echo $this->db->get_where('campuses', array('campus_id'=>$campus))->row()->campus_name;
											echo '<br />';
										}
									?>
								</td>
                                <td>
                                    <?php echo $course['course_name']?>
                                </td>
								<td>
									<?php echo $course['course_code']?>
								</td>
                                <td>
									<?php echo $course['course_type']?>
								</td>
								<td>
									<?php echo $course['course_duration_year'].' Years '.$course['course_duration_month'].' Months';?>
								</td>
								<td>
									<?php echo $course['course_semester']?>
								</td>
                                <td>
									<?php echo $course['add_by']?>
								</td>
                                <td>
									<?php echo $course['last_edit']?>
								</td>
								<td>
									<?php
                                    	if(@$myAccess[0]['course_management_edit_course']==1 || $this->session->userdata('role')=='Admin'):
									?>
									<a class="btn blue" href="<?php echo site_url();?>/courses/edit_course/<?php echo $course['course_id']?>"><i class="fa fa-edit"></i></a>
									<?php
										endif;
									?>
									<?php
                                    	if(@$myAccess[0]['course_management_delete_course']==1 || $this->session->userdata('role')=='Admin'):
									?>
                                    <a class="btn red" onclick="return confirm('Are you sure you want to delete this Course?')" href="<?php echo site_url();?>/courses/delete_course/<?php echo $course['course_id']?>"><i class="fa fa-trash"></i></a>
									<?php
										endif;
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
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->