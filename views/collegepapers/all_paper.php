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
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> Check Paper
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/collegepapers/all_paper">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Campuses <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="campus_id" class="form-control input-inline input-large campus_id" required>
                                                <option value="">Select Campus</option>
												<?php
                                                	foreach ($campuses as $campus):
												?>
                                                <option value="<?php echo $campus['campus_id']?>"><?php echo $campus['campus_name']?></option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                        </div>
									</div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Course <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="course_id" class="form-control input-inline input-large course_id" required>
                                                <option value="">Select Course</option>
												<?php
                                                	foreach ($courses as $course):
												?>
                                                <option value="<?php echo $course['course_id']?>"><?php echo $course['course_name']?></option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                        </div>
									</div>
									<div class="class">
									</div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Subject <span class="required">*</span></label>
                                        <div class="col-md-9 checkbox-list">
                                            <select name="subject_id" class="form-control input-inline input-large subject_id">
                                                
                                            </select>
                                        </div>
									</div>
									
									<div class="form-group">
										<label class="control-label col-md-3">Starting Date</label>
										<div class="col-md-3">
											<div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
												<input type="text" name="start_date" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
												<span class="input-group-btn">
												<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
												</span>
											</div>
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-md-3">End Date</label>
										<div class="col-md-3">
											<div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
												<input type="text" name="end_date" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
												<span class="input-group-btn">
												<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
												</span>
											</div>
										</div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<input type="hidden" name="submit" value="1" />
                                            <button type="submit" class="btn green">Check</button>
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
				if($this->input->post('submit')==1):
			?>
			<div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> All papers
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
									 Date
								</th>
                                <th>
									 Campus
								</th>
                                <th>
									 Course
								</th>
								<th>
									Subject
								</th>
								<th>
									Topics + Practicals
								</th>
								<th>
									Class
								</th>
								<th>
									Paper Details
								</th>
								<th>
									Total Marks
								</th>
								<th>
									Print Type
								</th>
								<th>
									Schedule / Surprise
								</th>
								<th>
									Uploaded Result Roll Numbers
								</th>
								<th>
									Add By
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
									 <?php echo $paper['collegepaper_id']?>
								</td>
								<td>
									<?php echo $paper['date']?>
								</td>
                                <td>
									<?php echo $paper['campus_name']?>
								</td>
                                <td>
									<?php echo $paper['course_name']?>
								</td>
								<td>
									<?php
										$subject_id = explode(',',$paper['subject_id']);
										foreach($subject_id as $topic)
										{
											echo @$this->db->get_where('course_subjects',array('course_subject_id'=>$topic))->row()->subject_name.'<br />';
										}
									?>
								
								</td>
								<td>
									<?php
										$topics = explode(',',$paper['topic_ids']);
										foreach($topics as $topic)
										{
											echo @$this->db->get_where('topics',array('topic_id'=>$topic))->row()->topic_name.'<br />';
										}
									?>
									<?php
										$practicals = explode(',',$paper['practicals']);
										foreach($practicals as $practical)
										{
											echo @$this->db->get_where('practicals',array('practical_id'=>$practical))->row()->practical_name.'<br />';
										}
									?>
								</td>
								<td>
									<?php echo $paper['class']?>
								</td>
								<td>
									MCQs : <?php echo count(explode(',',$paper['mcqs']));?>
									<br />
									Mcqs Marks : <?php echo $paper['mcqs_marks'];?>
									<br />
									Short Questions : <?php echo count(explode(',',$paper['short_questions']));?>
									<br />
									Mcqs Marks : <?php echo $paper['short_questions_marks'];?>
									<br />
									Practicals : <?php echo count(explode(',',$paper['practicals']));?>
									<br />
									Practicals Marks : <?php echo $paper['practical_marks'];?>
								</td>
								<td>
									<?php echo $paper['total_marks']?>
								</td>
								<td>
									<?php echo $paper['print_type']?>
								</td>
								<td>
									<?php //echo $paper['total_marks']?>
								</td>
								<td>
									<?php
										$this->db->select('students.roll_no');
										$this->db->from('collegepaper_results');
										$this->db->join('collegepapers','collegepapers.collegepaper_id=collegepaper_results.collegepaper_id','inner');
										$this->db->join('students','students.student_id=collegepaper_results.student_id','inner');
										$this->db->where('collegepapers.collegepaper_id',$paper['collegepaper_id']);
										$students = $this->db->get()->result_array();
										echo '<strong>Total Results :</strong>'.count($students).'<br />';
										echo '<strong>Roll Numbers :</strong> ';
										foreach($students as $student)
										{
											echo $student['roll_no'].', ';
										}
									?>
								</td>
								<td>
									<?php echo $paper['add_by']?>
								</td>
								<td>
									<?php
										if(@$myAccess[0]['papers_results_view_paper']==1 || $this->session->userdata('role')=='Admin'):
									?>
									<a href="<?php echo site_url();?>/collegepapers/viewpaper/<?php echo $paper['collegepaper_id'];?>" class="btn green" target="_blank"><i class="fa fa-eye"></i> View Paper</a>
									
									<a href="<?php echo site_url();?>/collegepapers/viewsolvepaper/<?php echo $paper['collegepaper_id'];?>" class="btn yellow" target="_blank"><i class="fa fa-eye"></i> View Solve Paper</a>
									<?php 
										endif;
									?>
									<?php
										if(@$myAccess[0]['papers_results_add_result']==1 || $this->session->userdata('role')=='Admin'):
									?>
									<a href="<?php echo site_url();?>/collegepapers/add_result/<?php echo $paper['collegepaper_id'];?>" class="btn purple" target="_blank"><i class="fa fa-file"></i> Add &amp; View Result</a>
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
			<?php
				endif;
			?>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->