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
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i>All Topics
							</div>
						</div>
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
                        
                            <div class="col-md-4">
                                <label><strong>Filter by Chapter</strong></label>
                                <select id="chapter_filter" class="form-control" multiple>
                                    <?php
                                        $chapters_list = $this->db
                                            ->select('chapter_id, course_id, course_subject_id, chapter_name')
                                            ->order_by('chapter_name','ASC')
                                            ->get('chapters')
                                            ->result_array();
                        
                                        foreach($chapters_list as $chapter):
                                    ?>
                                        <option value="<?php echo $chapter['chapter_id']; ?>"
                                                data-course-id="<?php echo $chapter['course_id']; ?>"
                                                data-subject-id="<?php echo $chapter['course_subject_id']; ?>">
                                            <?php echo $chapter['chapter_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
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
									 Topic Name
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($topics as $topic):
							?>
                            <tr class="odd gradeX topic-row"
                                data-course-id="<?php echo $topic['course_id']; ?>"
                                data-subject-id="<?php echo $topic['course_subject_id']; ?>"
                                data-chapter-id="<?php echo $topic['chapter_id']; ?>">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $topic['topic_id']?>
								</td>
								<td>
									<?php echo $topic['course_name']?>
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
                                    	if(@$myAccess[0]['course_management_edit_topic']==1 || $this->session->userdata('role')=='Admin'):
									?>
									<a href="<?php echo site_url().'/topics/edit_topic/'.$topic['topic_id'];?>" title="Edit" class="btn blue"><i class="fa fa-edit"></i></a>
									<?php
										endif;
									?>
									<?php
                                    	if(@$myAccess[0]['course_management_delete_topic']==1 || $this->session->userdata('role')=='Admin'):
									?>
                                    <a onclick="return confirm('Are you sure you want to delete this Topic?')" href="<?php echo site_url().'/topics/delete/'.$topic['topic_id'];?>" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>
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
        var allChapters = [];

        $('#subject_filter option').each(function () {
            allSubjects.push({
                id: $(this).val(),
                text: $(this).text(),
                course_id: $(this).data('course-id').toString()
            });
        });

        $('#chapter_filter option').each(function () {
            allChapters.push({
                id: $(this).val(),
                text: $(this).text(),
                course_id: $(this).data('course-id').toString(),
                subject_id: $(this).data('subject-id').toString()
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

            $('#chapter_filter').select2({
                placeholder: 'Select Chapters',
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

        function rebuildChapterDropdown() {
            var selectedCourses = $('#course_filter').val() || [];
            var selectedSubjects = $('#subject_filter').val() || [];

            $('#chapter_filter').empty();

            allChapters.forEach(function (chapter) {

                var courseMatched = selectedCourses.length === 0 || selectedCourses.indexOf(chapter.course_id) !== -1;
                var subjectMatched = selectedSubjects.length === 0 || selectedSubjects.indexOf(chapter.subject_id) !== -1;

                if (courseMatched && subjectMatched) {
                    $('#chapter_filter').append(
                        $('<option>', {
                            value: chapter.id,
                            text: chapter.text
                        })
                        .attr('data-course-id', chapter.course_id)
                        .attr('data-subject-id', chapter.subject_id)
                    );
                }
            });

            $('#chapter_filter').val(null).trigger('change');
        }

        function filterTopicsTable() {
            var selectedCourses = $('#course_filter').val() || [];
            var selectedSubjects = $('#subject_filter').val() || [];
            var selectedChapters = $('#chapter_filter').val() || [];

            $('#sample_2 tbody tr.topic-row').each(function () {

                var rowCourseId = $(this).data('course-id').toString();
                var rowSubjectId = $(this).data('subject-id').toString();
                var rowChapterId = $(this).data('chapter-id').toString();

                var courseMatched = selectedCourses.length === 0 || selectedCourses.indexOf(rowCourseId) !== -1;
                var subjectMatched = selectedSubjects.length === 0 || selectedSubjects.indexOf(rowSubjectId) !== -1;
                var chapterMatched = selectedChapters.length === 0 || selectedChapters.indexOf(rowChapterId) !== -1;

                if (courseMatched && subjectMatched && chapterMatched) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }

        $('#course_filter').on('change', function () {
            rebuildSubjectDropdown();
            rebuildChapterDropdown();
            filterTopicsTable();
        });

        $('#subject_filter').on('change', function () {
            rebuildChapterDropdown();
            filterTopicsTable();
        });

        $('#chapter_filter').on('change', function () {
            filterTopicsTable();
        });

    }

});
</script>
	