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
            <!-- BEGIN DASHBOARD STATS -->
			<div class="row">
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div style="display: none;" class="dashboard-stat blue-madison">
						<div class="visual">
							<i class="icon-map"></i>
						</div>
						<div  class="details">
							<div class="number">
								 <?php echo 0;?>
							</div>
							<div class="desc">
								 Subjects
							</div>
						</div>
						<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
			</div>
			<!-- END DASHBOARD STATS -->
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
								<i class="fa fa-list"></i>All Subjects
							</div>
						</div>
						<div class="portlet-body">
							<?php
                            	if($this->session->userdata('role')=='Admin'):
							?>
                            <div class="table-toolbar">
								<div class="row">
									<div class="col-md-3">
										<div class="btn-group">
											<button onclick="location.href = '<?php echo site_url()?>/subjects/add_subject'" class="btn green">
											Add New <i class="fa fa-plus"></i>
											</button>
										</div>
									</div>
									<div class="row col-md-9" style="margin-bottom:15px;">
                                        <div class="col-md-4">
                                            <label><strong>Filter by Course</strong></label>
                                            <select id="course_filter" class="form-control" multiple>
                                                <?php
                                                    $all_courses = $this->db->order_by('course_name','ASC')->get('courses')->result_array();
                                                    foreach($all_courses as $course):
                                                ?>
                                                    <option value="<?php echo $course['course_id']; ?>">
                                                        <?php echo $course['course_name']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
								</div>
							</div>
                            <?php
                            	endif;
							?>
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
									 Subject Name
								</th>
								<th>
									 Subject Year / Subject Semester
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($subjects as $subject):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $subject['course_subject_id']?>
								</td>
								<td class="course-cell"
                                    data-course-ids="<?php echo $subject['course_id'].','.$subject['extra_course_ids']; ?>">
                                    
                                    <?php echo $subject['course_name']?>
                                    <br />
                                
                                    <?php
                                        $courses = explode(',', $subject['extra_course_ids']);
                                        foreach($courses as $course)
                                        {
                                            if($course != '')
                                            {
                                                echo @$this->db->get_where('courses', array('course_id'=>$course))->row()->course_name;
                                                echo '<br />';
                                            }
                                        }
                                    ?>
                                </td>
								<td>
									<?php echo $subject['subject_name']?>
								</td>
								<td>
									<?php 
										if($subject['subject_year']!=0)
										{
											echo $subject['subject_year'].' Year';
										}
										if($subject['subject_semester']!=0)
										{
											echo $subject['subject_semester'].' Semester';
										}
									?>
								</td>
								<td>
									<?php
                                    	if(@$myAccess[0]['course_management_edit_subject']==1 || $this->session->userdata('role')=='Admin'):
									?>
                                    <a href="<?php echo site_url().'/subjects/edit_subject/'.$subject['course_subject_id'];?>" title="Edit" class="btn blue"><i class="fa fa-edit"></i></a>
                                    <?php
                                    	endif;
									?>
                                    <?php
                                    	if(@$myAccess[0]['course_management_delete_subject']==1 || $this->session->userdata('role')=='Admin'):
									?>
                                    <a onclick="return confirm('Are you sure you want to delete this Subject?')" href="<?php echo site_url().'/subjects/delete/'.$subject['course_subject_id'];?>" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>
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
	
	<script>
        document.addEventListener('DOMContentLoaded', function () {
        
            if (typeof $ !== 'undefined') {
        
                $('#course_filter').on('change', function () {
        
                    var selectedCourses = $(this).val();
        
                    $('#sample_2 tbody tr').each(function () {
        
                        var rowCourseIds = $(this).find('.course-cell').data('course-ids');
        
                        if (!selectedCourses || selectedCourses.length === 0) {
                            $(this).show();
                            return;
                        }
        
                        rowCourseIds = rowCourseIds ? rowCourseIds.toString().split(',') : [];
        
                        var matched = selectedCourses.some(function (courseId) {
                            return rowCourseIds.indexOf(courseId) !== -1;
                        });
        
                        if (matched) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
        
                    });
        
                });
        
                // Agar Select2 use kar rahe ho
                if ($.fn.select2) {
                    $('#course_filter').select2({
                        placeholder: 'Select Courses',
                        allowClear: true,
                        width: '100%'
                    });
                }
        
            }
        
        });
    </script>