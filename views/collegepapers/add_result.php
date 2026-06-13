
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Add Teacher <small>You can add teacher here</small>
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
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-plus"></i> Add Result
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/collegepapers/insert_result/<?php echo $this->uri->segment(3);?>" enctype="multipart/form-data">
								<div class="form-body">
                                    <div class="row">
										<div class="col-md-12">
											<table class="table table-striped table-bordered">
												<tbody>
													<tr>
														<td>Course Name</td>
														<td><?php echo $this->db->get_where('courses',array('course_id'=>$papers[0]['course_id']))->row()->course_name;?></td>
													</tr>
													<tr>
														<td>Campus Name</td>
														<td><?php $campus_name = $this->db->get_where('campuses',array('campus_id'=>$papers[0]['campus_id']))->row()->campus_name; echo $campus_name;?></td>
													</tr>
													<tr>
														<td>Subject Name</td>
														<td><?php $subject_name =  $this->db->get_where('course_subjects',array('course_subject_id'=>$papers[0]['subject_id']))->row()->subject_name; echo $subject_name;?></td>
													</tr>
													<tr>
														<td>Total Marks</td>
														<td><?php echo $papers[0]['total_marks'];?></td>
													</tr>
												</tbody>
											</table>
										</div>
									</div>
									<div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class=" col-md-4 control-label">Total Results</label>
                                                <div class="col-md-4">
                                                	<input type="number" value="" class="form-control total_results" /> 
                                                </div>
                                                <div class="col-md-4">
                                                	<button type="button" class="btn green add_result_row"> Add</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
									<div class="row students_view">
										
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Submit</button>
											<button onclick="location.href = '<?php echo site_url()?>/students/all_students'" type="button" class="btn default">Cancel</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
			
			
			<div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> Result
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
									Paper Date
								</th>
                                <th>
									 Student Roll No.
								</th>
								<th>
									 Student Name
								</th>
								<th>
									 Course Name
								</th>
								<th>
									 Paper Campus Name
								</th>
								<th>
									 Student Campus Name
								</th>
								<th>
									 Class Name
								</th>
								<th>
									 Subject Name
								</th>
								<th>
									 Topics + Practicals
								</th>
                                <th>
									 Obtain Marks
								</th>
								<th>
									 Percentage
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($results as $result):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo date('Y-m-d',strtotime($papers[0]['date']));?>
								</td>
								<td>
									 <?php echo $result['roll_no']?>
								</td>
								<td>
									<?php echo $result['first_name']?> <?php echo $result['last_name']?>
								</td>
								<td>
									 <?php echo $result['course_name']?>
								</td>
                                <td>
									<?php echo $campus_name;?>
								</td>
                                <td>
									<?php echo $result['campus_name']?>
								</td>
								<td>
									<?php echo $result['class_name']?>
								</td>
								<td>
									<?php echo $subject_name?>
								</td>
								<td>
									<?php
										$topics = explode(',',$papers[0]['topic_ids']);
										foreach($topics as $topic)
										{
											echo @$this->db->get_where('topics',array('topic_id'=>$topic))->row()->topic_name.'<br />';
										}
									?>
									<?php
										$practicals = explode(',',$papers[0]['practicals']);
										foreach($practicals as $practical)
										{
											echo @$this->db->get_where('practicals',array('practical_id'=>$practical))->row()->practical_name.'<br />';
										}
									?>
								</td>
								<td>
									<?php echo $result['obtain_marks']?> / <?php echo $papers[0]['total_marks']?>
								</td>
								<td>
									<?php echo round($result['obtain_marks']/$papers[0]['total_marks']*100).'%';?>
								</td>
								<td>
									<a onclick="return confirm('Are you sure you want to delete this Result?')" href="<?php echo site_url();?>/collegepapers/delete_result/<?php echo $result['collegepaper_result_id'];?>/<?php echo $this->uri->segment(3);?>" class="btn red" ><i class="fa fa-trash"></i> Delete</a>
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