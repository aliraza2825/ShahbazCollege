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
				<div class="col-md-12">
				    <div class="row" style="margin-bottom:15px;">
                        <div class="col-md-4">
                            <label><strong>Filter by Course</strong></label>
                            <select id="course_filter" class="form-control" multiple>
                                <?php
                                    $courses = $this->db->order_by('course_name','ASC')->get('courses')->result_array();
                                    foreach($courses as $course):
                                ?>
                                    <option value="<?php echo $course['course_id']; ?>">
                                        <?php echo $course['course_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    
                        <div class="col-md-4">
                            <label><strong>Filter by Subject</strong></label>
                            <select id="subject_filter" class="form-control" multiple>
                                <?php
                                    $subjects_list = $this->db
                                        ->select('course_subject_id, course_id, subject_name')
                                        ->order_by('subject_name','ASC')
                                        ->get('course_subjects')
                                        ->result_array();
                            
                                    foreach($subjects_list as $subject):
                                ?>
                                    <option value="<?php echo $subject['course_subject_id']; ?>"
                                            data-course-id="<?php echo $subject['course_id']; ?>">
                                        <?php echo $subject['subject_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i>All Chapters
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
									 Course Name
								</th>
								<th>
									 Subject Name
								</th>
								<th>
									 Chapter Name
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($chapters as $chapter):
							?>
                            <tr class="odd gradeX chapter-row"
    data-course-id="<?php echo $chapter['course_id']; ?>"
    data-subject-id="<?php echo $chapter['course_subject_id']; ?>">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $chapter['chapter_id']?>
								</td>
								<td>
									<?php echo $chapter['course_name']?>
								</td>
								<td>
									<?php echo $chapter['subject_name']?>
								</td>
								<td>
									<?php echo $chapter['chapter_name']?>
								</td>
								<td>
                                    <?php
                                    	if(@$myAccess[0]['course_management_edit_chapter']==1 || $this->session->userdata('role')=='Admin'):
									?>
									<a href="<?php echo site_url().'/chapters/edit_chapter/'.$chapter['chapter_id'];?>" title="Edit" class="btn blue"><i class="fa fa-edit"></i></a>
									<?php
										endif;
									?>
									<?php
                                    	if(@$myAccess[0]['course_management_delete_chapter']==1 || $this->session->userdata('role')=='Admin'):
									?>
                                    <a onclick="return confirm('Are you sure you want to delete this Chapter?')" href="<?php echo site_url().'/chapters/delete/'.$chapter['chapter_id'];?>" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>
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
    
            var allSubjects = [];
    
            $('#subject_filter option').each(function () {
                allSubjects.push({
                    id: $(this).val(),
                    text: $(this).text(),
                    course_id: $(this).data('course-id').toString()
                });
            });
    
            if ($.fn.select2) {
                $('#course_filter').select2({
                    placeholder: 'Select Courses',
                    allowClear: true,
                    width: '100%'
                });
    
                $('#subject_filter').select2({
                    placeholder: 'Select Subjects',
                    allowClear: true,
                    width: '100%'
                });
            }
    
            function rebuildSubjectDropdown() {
                var selectedCourses = $('#course_filter').val() || [];
    
                $('#subject_filter').empty();
    
                allSubjects.forEach(function (subject) {
    
                    if (selectedCourses.length === 0 || selectedCourses.indexOf(subject.course_id) !== -1) {
                        $('#subject_filter').append(
                            $('<option>', {
                                value: subject.id,
                                text: subject.text
                            }).attr('data-course-id', subject.course_id)
                        );
                    }
    
                });
    
                $('#subject_filter').val(null).trigger('change');
            }
    
            function filterChaptersTable() {
                var selectedCourses = $('#course_filter').val() || [];
                var selectedSubjects = $('#subject_filter').val() || [];
    
                $('#sample_2 tbody tr.chapter-row').each(function () {
    
                    var rowCourseId = $(this).data('course-id').toString();
                    var rowSubjectId = $(this).data('subject-id').toString();
    
                    var courseMatched = selectedCourses.length === 0 || selectedCourses.indexOf(rowCourseId) !== -1;
                    var subjectMatched = selectedSubjects.length === 0 || selectedSubjects.indexOf(rowSubjectId) !== -1;
    
                    if (courseMatched && subjectMatched) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
    
            $('#course_filter').on('change', function () {
                rebuildSubjectDropdown();
                filterChaptersTable();
            });
    
            $('#subject_filter').on('change', function () {
                filterChaptersTable();
            });
    
        }
    
    });
</script>