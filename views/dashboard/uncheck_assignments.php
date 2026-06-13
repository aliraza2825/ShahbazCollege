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
								<i class="fa fa-list"></i> All Unchecked Assignments
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
                                    Course Name &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </th>
                                <th>
                                    Campus Name &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </th>
                                <th>
                                    Class Name &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </th>
                                <th>
                                    Section
                                </th>
                                <th>
                                    Contact Numbers
                                </th>
                                <th>
                                    Subject Name &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </th>
                                <th>
                                    Chapter Name
                                </th>
                                <th>
                                    Topics &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </th>
								<th>
									 Students Details &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								</th>
                                <th>
                                    Submission Assignment Date
                                </th>
                                <th>
                                    Assignment Dead Line
                                </th>
                                <th>
                                    Student Submitted Assignment
                                </th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 1;
								foreach($assignments as $assignment):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
                                    <?php echo $i;?>
                                </td>
                                <td>
                                    <?php echo $assignment['course_name'];?>
                                </td>
                                <td>
                                    <?php echo $assignment['campus_name'];?>
                                </td>
                                <td>
                                    <?php echo $assignment['class_name'];?>
                                </td>
                                <td>
                                    <?php echo $assignment['class'];?>
                                </td>
                                <td>
                                    <?php echo $assignment['mobile'];?>  <?php echo $assignment['emergency_no'];?>
                                </td>
                                <td>
                                    <?php echo $assignment['subject_name'];?>
                                </td>
                                <td>
                                    <?php echo $assignment['chapter_name'];?>
                                </td>
                                <td>
                                    <?php
                                        $topic_ids = explode(',',$assignment['topic_ids']);
                                        $this->db->where_in('topic_id',$topic_ids);
                                        $topics=$this->db->get('topics')->result_array();
                                        foreach($topics as $topic)
                                        {
                                            echo $topic['topic_name'].'<br />';
                                        }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    echo 'Name : '.$assignment['first_name'].' '.$assignment['last_name'].'<br />';
                                    echo 'Roll No. : '.$assignment['roll_no'].'<br />';
                                    ?>
                                </td>
                                <td>
                                    <?php echo $assignment['date'];?>
                                </td>
                                <td>
                                    <?php echo $assignment['end_date'];?>
                                </td>
                                <td>
                                    <a href="<?php echo site_url();?>/assignments/solvedassignment/<?php echo $assignment['assignment_id'];?>/<?php echo $assignment['student_id'];?>" class="btn blue" target="_blank"><i class="fa fa-file"></i> Check Assignment</a>
                                </td>
								<td>
                                    <a href="<?php echo site_url();?>/assignments/viewassignment/<?php echo $assignment['assignment_id'];?>" class="btn green" target="_blank"><i class="fa fa-eye"></i> View Assignment</a>
									<a href="<?php echo site_url();?>/assignments/viewsolveassignment/<?php echo $assignment['assignment_id'];?>" class="btn yellow" target="_blank"><i class="fa fa-eye"></i> View Solve Assignment</a>

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