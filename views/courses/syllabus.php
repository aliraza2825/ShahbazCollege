
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
								<i class="fa fa-plus"></i> Add Syllabus
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/courses/insert_syllabus">
								<div class="form-body">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Classes <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control select2" id="select2_sample2" name="class_ids[]" multiple required>
                                                <?php 
                                                    foreach($classes as $class):
                                                ?>
                                                <option value="<?php echo $class['class_id'];?>" <?php if(in_array($class['class_id'], explode(',',@$access_values[0]['class_ids']))){echo 'selected';}?>>
                                                    <?php echo $class['name'];?>
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
                                            <select class="form-control" name="course_id" id="course_id" required>
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
                                        <label class="col-md-3 control-label">Subjects <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control course_subjects" name="subject_id" id="subject_id" required>
                                                
                                            </select>
                                            <!--<span class="help-inline"></span>-->
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-md-12 topics">
                                        
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                        	<input type="hidden" name="status" value="1" />
                                            <input type="hidden" name="add_by" value="<?php echo $this->session->userdata('name');?>" />
                                            <input type="hidden" name="last_edit" value="<?php echo $this->session->userdata('name');?>" />
											<button type="submit" class="btn green">Add Syllabus</button>
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
								<i class="fa fa-list"></i>All Syllabus
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
									 Campus Name
								</th>
								<th>
									 Class Name
								</th>
                                <th>
									 Course Name
								</th>
                                <th>
									 Subject Name
								</th>
                                <th>
									 Topic Name
								</th>
                                <th>
									 From Date
								</th>
                                <th>
									 To Date
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($syllabuss as $syllabus):
								
								$combinations = $this->db->get_where('syllabus', array('topic_id'=>$syllabus['topic_id'], 'from_date'=>$syllabus['from_date'], 'to_date'=>$syllabus['to_date']))->result_array();
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php 
										$class_ids = array();
										foreach($combinations as $combination):
											array_push($class_ids, $combination['class_id']);
                                    	endforeach;
										
										$this->db->select('*');
										$this->db->from('classes');
										$this->db->join('campuses', 'campuses.campus_id=classes.campus_id', 'inner');
										$this->db->where_in('classes.class_id', $class_ids);
										$this->db->group_by('classes.campus_id');
										$campuses = $this->db->get()->result_array();
										
										foreach($campuses as $campus)
										{
											echo $campus['campus_name'].'<br />';
										}
										$class_ids = array();
									?>
								</td>
								<td>
									<?php 
										foreach($combinations as $combination):
											echo $this->db->get_where('classes', array('class_id'=>$combination['class_id']))->row()->name.'<br />';
                                    	endforeach;
									?>
								</td>
                                <td>
									<?php echo $syllabus['course_name']?>
								</td>
                                <td>
									<?php echo $syllabus['subject_name']?>
								</td>
                                <td>
									<?php echo $syllabus['topic_name']?>
								</td>
                                <td>
									<?php echo $syllabus['from_date']?>
								</td>
                                <td>
									<?php echo $syllabus['to_date']?>
								</td>
								<td>
                                	<a class="btn blue" href="<?php echo site_url();?>/courses/edit_syllabus/<?php echo $syllabus['topic_id'].'/'.$syllabus['from_date'].'/'.$syllabus['to_date'];?>"><i class="fa fa-edit"></i></a>
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