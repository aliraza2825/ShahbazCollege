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
        <?php if(@$this->session->userdata('error')):?>
            <div class="alert alert-danger">
                <button class="close" data-close="alert"></button>
                <span>
                    <?php echo $this->session->userdata('error');?> </span>
            </div>
        <?php endif;?>

        <!-- Student Data-->
            <div class="row">

                <div class="col-md-12 ">
                    <!-- BEGIN SAMPLE FORM PORTLET-->
                    <div class="portlet box green ">

                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-user"></i> Time Table
                            </div>
                        </div>



                        <div class="portlet-body table-responsive">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                                <h4 class="modal-title">Add Lecture</h4>
                            </div>

                            <div class="modal-body">
                                <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/timetable/inserttimetable">

                                    <div class="form-body">


                                        <div class="form-group">
                                            <label class="col-md-2 control-label"> Select Campuses <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <select name="campus" class="form-control input-inline input-large campus" required>
                                                    <option value=""> Select Campus </option>
                                                    <?php foreach ($campuses as $campus): ?>
                                                        <option value="<?php echo $campus['campus_id'] ?>"> <?php echo $campus['campus_name'];?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-2 control-label"> Select Course <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <select name="course" class="form-control input-inline input-large courses" required>

                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-2 control-label"> Select Class <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <select name="class" class="form-control input-inline input-large subjectyear" required>
                                                    <option value=""> Select Class </option>
                                                    <option value="1"> First Year </option>
                                                    <option value="2"> Second Year </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-2 control-label"> Select Session <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <select name="session[]" class="form-control input-inline input-large classes select2" required multiple>


                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-2 control-label"> Select Subject <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <select name="subject[]"  class="form-control input-inline input-large course_subjects select2" multiple required>

                                                </select>
                                            </div>
                                        </div>


                                        <div class="form-group">
                                            <label class="col-md-2 control-label"> Select Study Type <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <select name="studytype" class="form-control input-inline input-large studytype" required>
                                                    <!--<option value=""> Select Study Type </option>
                                                    <?php //foreach ($studytype as $type): ?>
                                                        <option value="<?php //echo $type['id'] ?>"> <?php //echo $type['name'];?></option>
                                                    <?php //endforeach; ?>-->
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-2 control-label"> Select Shift <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <select name="shift" class="form-control input-inline input-large shifts" required>
                                                    <option value=""> Select Shift </option>
                                                </select>
                                            </div>
                                        </div>

                                        
										<div class="form-group">
                                            <label class="col-md-2 control-label"> Select Days <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <select name="days[]" class="form-control input-inline input-large days" required multiple>
                                                    
                                                   
                                                </select>
                                            </div>
                                        </div>


                                        <div class="form-group">
                                            <label class="col-md-2 control-label"> Select Room <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <select name="room" class="form-control input-inline input-large room" required>
                                                    <option value=""> Select Room </option>
                                                   
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-2 control-label"> Select Primary Teacher <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <select name="teacher" class="form-control input-inline input-large select2" id="select2_sample1"  required>
                                                    <option value=""> Select Teacher </option>
                                                    <?php foreach ($teachers as $teacher): ?>
                                                        <option value="<?php echo $teacher['user_id'] ?>"> <?php echo $teacher['first_name'].' '.$teacher['last_name'].' ( '.$teacher['campus_name'].' ) ';?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
										
										<div class="form-group">
                                            <label class="col-md-2 control-label"> Select Secondary Teacher </label>
                                            <div class="col-md-9">
                                                <select name="s_teacher" class="form-control input-inline input-large select2" id="select2_sample2"  required>
                                                    <option value=""> Select Teacher </option>
                                                    <?php foreach ($teachers as $teacher): ?>
                                                        <option value="<?php echo $teacher['user_id'] ?>"> <?php echo $teacher['first_name'].' '.$teacher['last_name'].' ( '.$teacher['campus_name'].' ) ';?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
										

                                        <div class="form-group">
                                            <label class="col-md-2 control-label">Start Time</label>
                                            <div class="col-md-9">
                                                <input type="time"  name="start_time" id="start_time"  class="form-control mobile" />
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-2 control-label">End Time</label>
                                            <div class="col-md-9">
                                                <input type="time"  name="end_time" id="end_time"  class="form-control mobile" />
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-2 control-label">Lecture Name</label>
                                            <div class="col-md-4">
                                                <input type="text"  name="lecture_name" class="form-control" />
                                            </div>
                                        </div>

                                    </div>

                                    <div class="form-actions">
                                        <div class="row">
                                            <div class="col-md-offset-3 col-md-9">

                                                <button type="submit" class="btn red">Submit</button>

                                            </div>
                                        </div>
                                    </div>

                                </form>
                            </div>


                        </div>


                    </div>
                    <!-- END SAMPLE FORM PORTLET-->
                </div>

            </div>

        <!-- Struck of Details-->

    </div>

</div>
<!-- END CONTENT -->
<script>
    window.addEventListener('DOMContentLoaded',function () {
        $('.studytype').change(function(){
            var campus_id = $('.campus').val();
            var study = $(this).val();
            jQuery.ajax({
                type: "post",
                async: false,
                url: '<?php echo site_url()?>/timetable/getShifts',
                data: {
                    campus_id : campus_id,
                    study_type : study
                },
                success: function(data) {
                    $('.shifts').html(data);
                }
            });
        });
        
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
                    $('.studytype').html(data);
                }
            });
        });
    });
</script>