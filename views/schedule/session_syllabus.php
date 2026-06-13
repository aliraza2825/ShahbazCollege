
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			

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
								<i class="fa fa-filter"></i> Filter
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/schedule/session_syllabus">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control" name="campus_id" required>
                                                <option value="">Select Campus</option>
												<?php 
                                                    foreach($campuses as $campus):
                                                ?>
                                                <option value="<?php echo $campus['campus_id'];?>">
                                                    <?php echo $campus['campus_name'];?>
                                                </option>
                                                <?php
                                                    endforeach;
                                                ?>
                                            </select>
                                            <!--<span class="help-inline"></span>-->
                                        </div>
                                    </div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Courses <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control" name="course_id" required>
                                                <option value="">Select Course</option>
												<?php 
                                                    foreach($courses as $course):
                                                ?>
                                                <option value="<?php echo $course['course_id'];?>">
                                                    <?php echo $course['course_name'];?>
                                                </option>
                                                <?php
                                                    endforeach;
                                                ?>
                                            </select>
                                            <!--<span class="help-inline"></span>-->
                                        </div>
                                    </div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Shift <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control" name="shift_id" required>
                                                <option value="">Select Shift</option>
												<?php 
                                                    foreach($shifts as $shift):
                                                ?>
                                                <option value="<?php echo $shift['id'];?>">
                                                    <?php echo $shift['name'];?>
                                                </option>
                                                <?php
                                                    endforeach;
                                                ?>
                                            </select>
                                            <!--<span class="help-inline"></span>-->
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green"> Check Syllabus</button>
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
								<i class="fa fa-list"></i>All Seesion Subject Wise Syllabus
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
									Sr no.
							   </th>
								<th>
									 Course
								</th>
								<th>
									 Campus
								</th>
                                <th>
									 Subject
								</th>
                                <th>
                                    Lecture
                                </th>
								 <th>
									 Sessions
								</th>
								<th>
									 Shift
								</th>
								<th>
									 Study Type
								</th>
								<th>
									 Type
								</th>
                                <th>
									 Start Date
								</th>
								<th>
									 End Date
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 1;
								foreach($syllabuss as $syllabus):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
								<td>
                                	 <?php echo $i;?>
                                </td>
                                <td>
									<?php
										echo $syllabus['course_name'];
									?>
								</td>
								<td>
									<?php
										echo $syllabus['campus_name'];
									?>
								</td>
								<td>
									<?php
										echo $syllabus['subject_name'];
									?>
								</td>
                                <td>
                                    <?php
                                    echo $syllabus['lecture_name'].' '.$syllabus['lecture_id'];
                                    ?>
                                </td>
								<td>
									<?php
										echo $syllabus['sessions'];
									?>
								</td>
								<td>
									<?php
										echo $syllabus['shift_name'];
									?>
								</td>
								<td>
									<?php
										echo $syllabus['study_type'];
									?>
								</td>
								<td>
									<?php
										if($syllabus['revision'] == 0)
										{
											echo 'Regular';
										}
										else
										{
											echo 'Revision '.$syllabus['revision'];
										}
									?>
								</td>
								<td>
									<?php
										$this->db->select('session_syllabus.date');
										$this->db->from('session_syllabus');
										$this->db->join('course_subjects', 'course_subjects.course_subject_id=session_syllabus.subject_id', 'inner');
										$this->db->where(array('session_syllabus.lecture_id'=>$syllabus['lecture_id'],'session_syllabus.subject_id'=>$syllabus['subject_id']));
										$this->db->order_by('session_syllabus.date','ASC');
										$this->db->limit(1);
										$start_date = $this->db->get()->row()->date;
										echo $start_date;
									?>
								</td>
								<td>
									<?php
										$this->db->select('session_syllabus.date');
										$this->db->from('session_syllabus');
										$this->db->join('course_subjects', 'course_subjects.course_subject_id=session_syllabus.subject_id', 'inner');
										$this->db->where(array('session_syllabus.lecture_id'=>$syllabus['lecture_id'],'session_syllabus.subject_id'=>$syllabus['subject_id']));
										$this->db->order_by('session_syllabus.date','DESC');
										$this->db->limit(1);
										$end_date = $this->db->get()->row()->date;
										echo $end_date;
									?>
								</td>
								<td>
									<a href="<?php echo site_url().'/schedule/view_session_syllabus/'.$syllabus['subject_id'].'/'.$syllabus['lecture_id'];?>" title="View" class="btn blue"><i class="fa fa-eye"></i></a>
									<a href="<?php echo site_url().'/schedule/delete_session_syllabus/'.$syllabus ['subject_id'].'/'.$syllabus['lecture_id'].'/'.$syllabus['syllabus_id'];?>" title="Delete" class="btn red"><i class="fa fa-trash"></i>Delete</a>
												
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