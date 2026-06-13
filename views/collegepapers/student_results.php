
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
								<i class="fa fa-list"></i> Check Result
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/collegepapers/student_results">
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
                                        <label class="col-md-3 control-label">Student <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="student_id" class="form-control input-inline input-large students">
                                                
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
									 <?php echo $result['date'];?>
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
									<?php echo $this->db->get_where('campuses',array('campus_id'=>$result['campus_id']))->row()->campus_name;;?>
								</td>
                                <td>
									<?php echo $result['campus_name']?>
								</td>
								<td>
									<?php echo $result['class_name']?>
								</td>
								<td>
									<?php echo $this->db->get_where('course_subjects',array('course_subject_id'=>$result['subject_id']))->row()->subject_name;;?>
								</td>
								<td>
									<?php
										$topics = explode(',',$result['topic_ids']);
										foreach($topics as $topic)
										{
											echo @$this->db->get_where('topics',array('topic_id'=>$topic))->row()->topic_name.'<br />';
										}
									?>
									<?php
										$practicals = explode(',',$result['practicals']);
										foreach($practicals as $practical)
										{
											echo @$this->db->get_where('practicals',array('practical_id'=>$practical))->row()->practical_name.'<br />';
										}
									?>
								</td>
								<td>
									<?php echo $result['obtain_marks']?> / <?php echo $result['total_marks']?>
								</td>
								<td>
									<?php echo round($result['obtain_marks']/$result['total_marks']*100).'%';?>
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