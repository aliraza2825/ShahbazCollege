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
			<?php
				if(count($my_lectures)>0):
			?>
			<div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> All Lectures
							</div>
						</div>
						<div class="portlet-body table-responsive">
							<label><input type="checkbox" class="all_open" name="all_open" value="1" /> All Open</label>
							<table class="table table-bordered table-hover">
							<thead>
							<tr>
                                <th class="hidden">
									 Hidden
								</th>
                                <th>
                                	Sr. No
                                </th>
								<th>
                                	Information
                                </th>
								<th>
									Lecture Date
								</th>
								<th>
									Topics / Practicals to be studied
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=1;
								foreach($my_lectures as $lecture):
							?>
                            <tr class="odd gradeX <?php if(date('Y-m-d')==$lecture['date']){echo 'success';}?>">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
								<td>
                                	<?php echo $i;?>
                                </td>
                                <td>
									<strong>Campus Name : </strong><?php echo $lecture['campus_name'];?>
									<br />
									<strong>Course Name : </strong><?php echo $lecture['course_name'];?>
									<br />
									<strong>Lecture Name : </strong><?php echo $lecture['lecture_name'];?>
									<br />
									<strong>Class Name : </strong><?php if($lecture['class']==1){echo '1st Year';}else{echo '2nd Year';};?>
									<br />
									<strong>Session : </strong><?php echo $lecture['session'];?>
									<br />
									<strong>Shift : </strong><?php echo $this->db->get_where('shifts',array('id'=>$lecture['shift']))->row()->name;?>
									<br />
									<strong>Study Type : </strong><?php echo $this->db->get_where('study_type',array('id'=>$lecture['studytype']))->row()->name;?>
									<br />
									<strong>Room : </strong><?php echo $this->db->get_where('rooms',array('room_id'=>$lecture['room']))->row()->room_name;?>
									<br />
									<strong>Subjects : </strong>
									<?php
										$subject_ids = explode(',',$lecture['subjects']);
										foreach($subject_ids as $subject_id)
										{
											echo $this->db->get_where('course_subjects',array('course_subject_id'=>$subject_id))->row()->subject_name;
											echo ' , ';
										}
									?>
									<br />
									<strong>Lecture Timing : </strong><?php echo date('h:i A',strtotime($lecture['start_date']));?> - <?php echo date('h:i A',strtotime($lecture['end_date']));?>
									<br />
									<strong>Teacher Name : </strong><?php echo $this->db->get_where('users',array('user_id'=>$lecture['teacher']))->row()->first_name;?>
								</td>
								<td>
									 <?php echo date('F d, Y',strtotime($lecture['date']));?>
								</td>
								<td>
									<?php
										if($lecture ['is_quiz'] == '1')
										{
											if ($lecture ['is_half'] == '1')
											{
												echo "<strong>Half Book Test</strong><br /><br />";
											}else {
												echo "<strong>Quiz</strong><br /><br />";
											}
											$t=1;
										}
										else
										{
											echo "<strong>Topics</strong><br /><br />";
										}

										$topicss =$this->db->where('lecture_id = "'.$lecture['lecture_id'].'" and date like "'.$lecture['date'].'" and topic_ids != ""')->get('session_syllabus')->result_array();

										foreach ($topicss as $topic)
										{
											$topics = $topic['topic_ids'];
											$ext_topics = explode(',',$topics);
											$topic_text = "";
											$lecture_topics = $this->db->where_in('topic_id', explode(',',$topics))->get('topics')->result_array();
											$topic_text = "";
											$lectbefore = array();
											$lectafter  = array();

											foreach ($lecture_topics as $key => $teps)
											{
												$lectbefore = array();
												$lectafter  = array();
												$topic_text = "";
												if($lecture ['is_quiz'] == '1')
												{
													$topic_text = "";
													$lectbefore = array();
													$lectafter  = array();
												}
												else
												{
													$query = 'SELECT * FROM session_syllabus where id < '.$topic ['id'].' and  subject_id = "'.$topic ['subject_id'].'" and lecture_id = "'.$topic ['lecture_id'].'" and syllabus_id= "'.$topic ['syllabus_id'].'" and is_quiz = "0" and is_half ="0" and FIND_IN_SET("'.$ext_topics[0].'",topic_ids)';

													$query2 = 'SELECT * FROM session_syllabus where id > '.$topic ['id'].' and  subject_id = "'.$topic ['subject_id'].'" and lecture_id = "'.$topic ['lecture_id'].'" and syllabus_id= "'.$topic ['syllabus_id'].'" and is_quiz = "0" and is_half ="0" and FIND_IN_SET("'.$ext_topics[0].'",topic_ids)';

													$lectbefore = $this->db->query($query)->result_array();
													$lectafter  = $this->db->query($query2)->result_array();

													$topic_text = "  ".(count($lectbefore)+1).' of '.((count($lectbefore)+1)+(count($lectafter)));
												}
												$checkLectureTaken = $this->db->get_where('study_by_teacher',array('topic_id'=>$teps['topic_id'],'session_syllabus_id'=>$lecture['session_syllabus_id'],'is_quiz'=>$lecture['is_quiz']))->result_array();
																								
												echo '<strong>'.$this->db->where('course_subject_id', $topic['subject_id'])->get('course_subjects')->row()->subject_name.'</strong> - '.$this->db->where('unique_syllabus_id', $topic['syllabus_id'])->get('syllabus')->row()->syllabus_name.' - <strong>'.$teps['topic_name'] .'</strong>'.$topic_text;

												if(count($checkLectureTaken)> 0)
												{
													echo ' <i class="fa fa-info-circle" title="Studied By : '.$checkLectureTaken[0]['created_by'].' at '.$checkLectureTaken[0]['created_at'].'"></i> <span class="study_by alert-success" style="display:none;"> (Studied By : '.$checkLectureTaken[0]['created_by'].' at '.$checkLectureTaken[0]['created_at'].')</span><br />';
												}
												else
												{
													echo '<br />';
												}

											}
										}
									?> 
									<?php
										$topicss =$this->db->where('lecture_id = "'.$lecture['lecture_id'].'" and date like "'.$lecture['date'].'" and practical_ids != ""')->get('session_syllabus')->result_array();
										
										if(count($topicss)>0)
										{
											echo "<br /><strong>Practicals</strong><br /><br />";
										}

										foreach ($topicss as $topic)
										{
											$topics = explode(",", $topic ['practical_ids']);
											$lecture_topics = $this->db->where_in('practical_id ', $topics)->get('practicals')->result_array();

											foreach ($lecture_topics as $key => $teps)
											{
												$checkLectureTaken = $this->db->get_where('study_by_teacher',array('practical_id'=>$teps['practical_id'],'session_syllabus_id'=>$lecture['session_syllabus_id'],'is_quiz'=>$lecture['is_quiz']))->result_array();

												echo '<strong>'.$this->db->where('course_subject_id', $topic['subject_id'])->get('course_subjects')->row()->subject_name.'</strong> - '.$this->db->where('unique_syllabus_id', $topic['syllabus_id'])->get('syllabus')->row()->syllabus_name.' - <strong>'.$teps['practical_name'].'</strong>';

												if(count($checkLectureTaken)> 0)
												{
													echo ' <i class="fa fa-info-circle" title="Studied By : '.$checkLectureTaken[0]['created_by'].' at '.$checkLectureTaken[0]['created_at'].'"></i> <span class="study_by alert-success" style="display:none;"> (Studied By : '.$checkLectureTaken[0]['created_by'].' at '.$checkLectureTaken[0]['created_at'].')</span><br />';
												}
												else
												{
													echo '<br />';
												}

											}
										}
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
	<script>
window.addEventListener('DOMContentLoaded',function () {
	$('.all_open').click(function(){
		if($(this).prop('checked') == true)
		{
			$('.study_by').show();
		}
		else
		{
			$('.study_by').hide();
		}
	});
});
</script>