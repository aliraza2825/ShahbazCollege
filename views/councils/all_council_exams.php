<?php
	$myAccess = checkUserAccess();
?>	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			All Classes <small>Here you can find all classes</small>
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
			<div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i>All Council Exams
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
									 Course Name
								</th>
								<th>
									 Subjects
								</th>
								<th>
									 Paper Type
								</th>
								<th>
									 Paper Name
								</th>
								<th>
									 Paper Number
								</th>
								<th>
									 Passing Marks
								</th>
								<th>
									 Passing Percentage
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i = 0;
								foreach($papers as $paper):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $paper['council_exam_id']?>
								</td>
                                <td>
									 <?php echo $paper['course_name']?>
								</td>
								<td>
									 <?php
									    $subjects = explode(',',$paper['subject_ids']);
									    foreach($subjects as $subject)
									    {
									        echo $this->db->get_where('course_subjects',array('course_subject_id'=>$subject))->row()->subject_name.'<br />';
									    }
									 ?>
									 <?php //echo $paper['subject_ids']?>
								</td>
								<td>
									 <?php echo $paper['paper_type_name']?>
								</td>
								<td>
									 <?php echo $paper['paper_name']?>
								</td>
								<td>
									<?php echo $paper['paper_no']?>
								</td>
								<td>
									<?php echo $paper['passing_marks']?>
								</td>
								<td>
									<?php echo $paper['passing_percentage']?>
								</td>
								<td>
									<a href="<?php echo site_url().'/councils/edit_council_exam/'.$paper['council_exam_id'];?>" title="Edit" class="btn blue"><i class="fa fa-edit"></i></a>
                                    <a onclick="return confirm('Are you sure you want to delete this Council?')" href="<?php echo site_url().'/councils/delete_council_exam/'.$paper['council_exam_id'];?>" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>
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