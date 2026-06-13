<?php
$myAccess = checkUserAccess();
?>
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
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-search"></i> Search
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" enctype="multipart/form-data" action="<?php echo site_url();?>/test_engine/topics">
								<div class="form-body">
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
                                            <select name="subject_id" class="form-control input-inline input-large subject_id" required>

                                                
                                            </select>
                                        </div>
									</div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Chapter <span class="required">*</span></label>
                                        <div class="col-md-9 checkbox-list">
                                            <select name="chapter_id" class="form-control input-inline input-large chapter_id">
											 <option value="">Select chapter</option>
                                                
                                            </select>
                                        </div>
									</div>
                                </div>
                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-offset-3 col-md-9">
											<input type="hidden" name="submit" value="1" />
                                            <button type="submit" class="btn green submit_button">Search</button>
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
								<i class="fa fa-add"></i> Add Questions of Topics
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
									 Subject Name
								</th>
								<th>
									 Chapter Name
								</th>
                                <th>
									 Topic Name
								</th>
                                <th>
									 Total MCQs
								</th>
								<th>
									 MCQs with Audio
								</th>
                                <th>
									 Total Short Questions
								</th>
								<th>
									 Short Questions with Audio
								</th>
								<th>
									 Total Long Questions
								</th>
								<th>
									 Long Questions with Audio
								</th>
                                <th>
									 Total Word Meanings
								</th>
								<th>
									 Word Meanings with Audio
								</th>
                                <th>
									 Add By
								</th>
                                <th>
									 Last Edit
								</th>
								<th>
									 Action &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($topics as $topic):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $topic['topic_id']?>
								</td>
								<td>
									<?php echo $topic['subject_name']?>
								</td>
								<td>
									<?php echo $topic['chapter_name']?>
								</td>
                                <td>
									<?php echo $topic['topic_name']?>
								</td>
                                <td>
									<?php
										$this->db->select('*');
										$this->db->from('questions');
										$this->db->where('topic_id', $topic['topic_id']);
										$where = '(type="radio" or type = "multiple")';
										$this->db->where($where);
										$sum = $this->db->get()->result_array();
										echo count($sum);
									?>
								</td>
								<td>
									<?php 
										$audio=0;
										foreach($sum as $question)
										{
											if($_SERVER['REMOTE_ADDR']=='::1')
											{
												$base_path = 'D:/server/htdocs/shahbaz/recording/';
											}
											else
											{
												$base_path = '/home/shahbazc/public_html/lahore-campus/recording/';
											}
											
											if(file_exists($base_path.$question['question_id'].'.ogg'))
											{
												$audio++;
											}
											if($question['audio']!='')
											{
												if(file_exists($base_path.$question['audio']))
												{
													$audio++;
												}
											}
										}
										echo $audio;
									?>
								</td>
                                <td>
                                	<?php
										$this->db->select('*');
										$this->db->from('questions');
										$this->db->where('topic_id', $topic['topic_id']);
										$this->db->where('type', 'short-question');
										$sum = $this->db->get()->result_array();
										echo count($sum);
									?>
                                </td>
								<td>
									<?php 
										$audio=0;
										foreach($sum as $question)
										{
											if($_SERVER['REMOTE_ADDR']=='::1')
											{
												$base_path = 'D:/server/htdocs/shahbaz/recording/';
											}
											else
											{
												$base_path = '/home/shahbazc/public_html/lahore-campus/recording/';
											}
											
											if(file_exists($base_path.$question['question_id'].'.ogg'))
											{
												$audio++;
											}
											if($question['audio']!='')
											{
												if(file_exists($base_path.$question['audio']))
												{
													$audio++;
												}
											}
										}
										echo $audio;
									?>
								</td>
                                <td>
                                	<?php
										$this->db->select('*');
										$this->db->from('questions');
										$this->db->where('topic_id', $topic['topic_id']);
										$this->db->where('type', 'long-question');
										$sum = $this->db->get()->result_array();
										echo count($sum);
									?>
                                </td>
								<td>
									<?php 
										$audio=0;
										foreach($sum as $question)
										{
											if($_SERVER['REMOTE_ADDR']=='::1')
											{
												$base_path = 'D:/server/htdocs/shahbaz/recording/';
											}
											else
											{
												$base_path = '/home/shahbazc/public_html/lahore-campus/recording/';
											}
											
											if(file_exists($base_path.$question['question_id'].'.ogg'))
											{
												$audio++;
											}
											if($question['audio']!='')
											{
												if(file_exists($base_path.$question['audio']))
												{
													$audio++;
												}
											}
										}
										echo $audio;
									?>
								</td>
								<td>
									<?php
										$this->db->select('*');
										$this->db->from('questions');
										$this->db->where('topic_id', $topic['topic_id']);
										$this->db->where('type', 'word-meaning');
										$sum = $this->db->get()->result_array();
										echo count($sum);
									?>
								</td>
								<td>
									<?php 
										$audio=0;
										foreach($sum as $question)
										{
											if($_SERVER['REMOTE_ADDR']=='::1')
											{
												$base_path = 'D:/server/htdocs/shahbaz/recording/';
											}
											else
											{
												$base_path = '/home/shahbazc/public_html/lahore-campus/recording/';
											}
											
											if(file_exists($base_path.$question['question_id'].'.ogg'))
											{
												$audio++;
											}
											if($question['audio']!='')
											{
												if(file_exists($base_path.$question['audio']))
												{
													$audio++;
												}
											}
										}
										echo $audio;
									?>
								</td>
                                <td>
									<?php echo $topic['add_by']?>
								</td>
                                <td>
									<?php echo $topic['last_edit']?>
								</td>
								<td>
									<?php
                                    	if(@$myAccess[0]['test_engine_add_questions']==1 || $this->session->userdata('role')=='Admin'):
									?>
									    <a title="Add Questions" class="btn green" href="<?php echo site_url();?>/test_engine/add_question/<?php echo $topic['topic_id']?>"><i class="fa fa-question"></i></a>
									<?php
										endif;
									?>
                                    <?php
                                    if($this->session->userdata('role')=='Admin'):
                                    ?>
                                    <a title="Re-Check Questions" class="btn blue" href="<?php echo site_url();?>/test_engine/recheck_question/<?php echo $topic['topic_id']?>"><i class="fa fa-refresh"></i></a>
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