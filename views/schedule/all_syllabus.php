
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
								<i class="fa fa-plus"></i> Check Syllabus
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/schedule/all_syllabus">
								<div class="form-body">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Courses <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control courses" name="course_id" id="course_id" required>
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
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Study Type <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control study_type" name="studytype" required>
                                                
                                            </select>
                                            <!--<span class="help-inline"></span>-->
                                        </div>
                                    </div>
                                    <!-- <div class="form-group">
                                        <label class="col-md-3 control-label">Syllabus Type <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control" name="revision" required>
                                                <option value="0">Regular Study</option>
                                                <option value="1">Revision</option>
                                            </select>
                                        </div>
                                    </div> -->

								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<input type="hidden" name="submit" value="1" />
											<button type="submit" class="btn green">Check Syllabus</button>
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
				if(@$this->input->post('submit')==1):
			?>
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
									 Course
								</th>
								<th>
									 Subject
								</th>
                                <th>
                                    Syllabus Name
                                </th>
                                <th>
									 Study Type
								</th>
                                <th>
									 Syllabus Type
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
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									<?php
										echo $syllabus['course_name'];
									?>
								</td>
								<td>
									<?php
										echo $syllabus['subject_name'];
									?>
								</td>
                                <td>
                                    <?php echo $syllabus['syllabus_name']?>
                                </td>
								<td>
									<?php echo $syllabus['study_type']?>
								</td>
                                <td>
									<?php 
										if($syllabus['revision'] == 0)
										{
											echo "Regular";
										}
										else
										{
											echo "Revision".($i+1);
										}
									?>
								</td>
								<td>

                                    <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/schedule/view_syllabus/<?php echo $this->input->post('subject_id') ?>/<?php echo $syllabus['unique_syllabus_id']?>">
                                        <div class="form-body">
										
											<input type="hidden" name="start_date" value="<?php echo date('Y-m-d')?>">
											<input type="hidden" name="test_after" value="3">
											
                                        </div>
                                        <div class="form-actions">
                                            <div class="row">
                                                <div class="col-md-offset-3 col-md-9">
                                                    <button type="submit" class="fa fa-eye"></button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
									<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/schedule/deleteSyllabus">
                                        <div class="form-body">
											<br />
                                                <input type="hidden" name="unique_syllabus_id" class="form-control" value="<?php echo $syllabus['unique_syllabus_id']?>">
                                             
                                            </div>
											<br />
                                        </div>
                                        <div class="form-actions">
                                            <div class="row">
                                                <div class="col-md-offset-3 col-md-9">
                                                    <button type="submit" class="btn red" onclick="return confirm('Are you sure you want to delete this Syllabus?')">Delete</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>

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
        $('.courses').change(function(){
            var course_id = $(this).val();
            jQuery.ajax({
                type: "post",
                async: false,
                url: '<?php echo site_url()?>/timetable/getCourseStudyTypes',
                data: {
                    course_id : course_id
                },
                success: function(data) {
                    $('.study_type').html(data);
                }
            });
        });
    });
</script>