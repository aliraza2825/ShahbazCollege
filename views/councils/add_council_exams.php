
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
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-plus"></i> Add Council Exam
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/councils/insert_council_exam">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Select Courses <span class="required">*</span></label>
                                        <div class="col-md-6">
                                            <select class="form-control input-inline input-large select2 course_id" data-placeholder="Select Course..." name="course_id" required>
                                                <option value="">Select Course</option>
                                                <?php
                                                foreach($courses as $course):
                                                ?>
                                                    <option value="<?php echo $course['course_id'];?>"><?php echo $course['course_name'];?></option>
                                                <?php
                                                endforeach;
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Select Subjects <span class="required">*</span></label>
                                        <div class="col-md-6">
                                            <select class="form-control input-inline input-large subjects" name="subject_ids[]"  multiple required>
                                                
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Select Paper Type <span class="required">*</span></label>
                                        <div class="col-md-6">
                                            <select class="form-control input-inline input-large" name="paper_type_id" required>
                                                <?php
                                                foreach($paper_types as $paper_type):
                                                ?>
                                                    <option value="<?php echo $paper_type['paper_type_id'];?>"><?php echo $paper_type['name'];?></option>
                                                <?php
                                                endforeach;
                                                ?>
                                            </select>
                                        </div>
                                    </div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Paper Name <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control input-inline input-large" name="paper_name" placeholder="Enter paper name" value="" required>
											<span class="help-inline"></span>
                                        </div>
									</div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Paper Marks <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control input-inline input-large" name="paper_no" placeholder="Enter paper no." value="" required>
											<span class="help-inline"></span>
                                        </div>
									</div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Passing Marks <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="number" class="form-control input-inline input-large" name="passing_marks" placeholder="Enter passing marks" value="" required>
											<span class="help-inline"></span>
                                        </div>
									</div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Passing Percentage <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="number" class="form-control input-inline input-large" name="passing_percentage" placeholder="Enter passing percentage" value="" required>
											<span class="help-inline"></span>
                                        </div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Add Council Exam</button>
											<button onclick="location.href = '<?php echo site_url()?>/councils/all_councils'" type="button" class="btn default">Cancel</button>
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
<script>
    document.addEventListener( "DOMContentLoaded", function()
    {
        $('.select2').select2();
        jQuery(document).ready(function(){
            jQuery('.course_id').change(function(){
                var course_id = jQuery(this).val();
                jQuery.ajax({
                    type: "post",
                    async: false,
                    url: '<?php echo site_url()?>/councils/getSubjects',
                    data: {
                        course_id : course_id,
                    },
                    success: function(data) {
                        jQuery('.subjects').html(data);
                        jQuery('.subjects').addClass('select2');
    					AfterLookUpLoad();
                    }
                });
            });
            function AfterLookUpLoad() {
    			$(".subjects").select2({
    				placeholder: "Select Type(Screen)",
    				allowClear: true
    			});
    		}
        });
    }, false );
</script>