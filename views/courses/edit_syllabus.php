
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
								<i class="fa fa-edit"></i> Edit Syllabus
							</div>
						</div>
						<div class="portlet-body form">
                            <?php
                            	$class_ids = array();
								$course_ids = array();
								foreach($syllabuses as $syllabus)
								{
									array_push($class_ids, $syllabus['class_id']);
									array_push($course_ids, $syllabus['course_id']);
								}
							?>
                            
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/courses/update_syllabus/<?php echo $this->uri->segment(3);?>/<?php echo $this->uri->segment(4);?>/<?php echo $this->uri->segment(5);?>">
								<div class="form-body">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Classes <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control select2" id="select2_sample2" name="class_ids[]" multiple required>
                                                <?php 
                                                    foreach($classes as $class):
                                                ?>
                                                <option value="<?php echo $class['class_id'];?>" <?php if(in_array($class['class_id'], $class_ids)){echo 'selected';}?>>
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
                                            <input type="text" class="form-control" value="<?php echo $this->db->get_where('courses', array('course_id'=>$syllabuses[0]['course_id']))->row()->course_name;?>" readonly="readonly" />
                                            <!--<span class="help-inline"></span>-->
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Subject <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <input type="text" class="form-control" value="<?php echo $this->db->get_where('course_subjects', array('course_subject_id'=>$syllabuses[0]['subject_id']))->row()->subject_name;?>" readonly="readonly" />
                                            <!--<span class="help-inline"></span>-->
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-md-12 topics">
                                        	<?php 
		$topics = $this->db->get_where('topics', array('topic_id'=>$syllabuses[0]['topic_id']))->result_array();
		$html='';
		$html.='<table class="table table-striped table-bordered table-hover"><thead><tr><th>Topic</th><th>From Date</th><th>To Date</th></tr></thead>';
		$html.='';
		foreach($topics as $topic)
		{
			$html.='<tr>';
			$html.='<td>'.$topic['topic_name'].'</td>';
			$html.='<td><div class="input-group input-medium date date-picker" data-date="'.$syllabuses[0]['from_date'].'" data-date-format="yyyy-mm-dd" data-date-viewmode="years"><input type="text" name="from_date" class="form-control" value="'.$syllabuses[0]['from_date'].'" readonly><span class="input-group-btn"><button class="btn default" type="button"><i class="fa fa-calendar"></i></button></span></div></td>';
			$html.='<td><div class="input-group input-medium date date-picker" data-date="'.$syllabuses[0]['to_date'].'" data-date-format="yyyy-mm-dd" data-date-viewmode="years"><input type="text" name="to_date" class="form-control" value="'.$syllabuses[0]['to_date'].'" readonly><span class="input-group-btn"><button class="btn default" type="button"><i class="fa fa-calendar"></i></button></span></div></td>';
			$html.='</tr>';
		}
		$html.='</table>';
		echo $html;
											?>
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                        	<input type="hidden" name="course_id" value="<?php echo $syllabuses[0]['course_id']?>" />
                                            <input type="hidden" name="subject_id" value="<?php echo $syllabuses[0]['subject_id']?>" />
                                            <input type="hidden" name="status" value="1" />
                                            <input type="hidden" name="last_edit" value="<?php echo $this->session->userdata('name');?>" />
											<button type="submit" class="btn green">Update Syllabus</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->