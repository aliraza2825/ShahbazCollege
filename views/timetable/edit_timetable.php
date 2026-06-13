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
                                <h4 class="modal-title">Edit Lecture</h4>
                            </div>

                            <div class="modal-body">
                                <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/timetable/updatetimetable/<?php echo $this->uri->segment(3);?>">

                                    <div class="form-body">


                                        <div class="form-group">
                                            <label class="col-md-2 control-label"> Select Campuses <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <select name="campus" class="form-control input-inline input-large campus" required>
                                                    <option value=""> Select Campus </option>
                                                    <?php foreach ($campuses as $campus): ?>
                                                        <option value="<?php echo $campus['campus_id'];?>" <?php if($campus['campus_id']==$lecture[0]['campus']){echo 'selected';}?>> <?php echo $campus['campus_name'];?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-2 control-label"> Select Course <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <select name="course" class="form-control input-inline input-large courses" required>
                                                    <option value=""> Select Course </option>
                                                    <?php foreach ($courses as $course): ?>
                                                        <option value="<?php echo $course['course_id'];?>" <?php if($course['course_id']==$lecture[0]['course']){echo 'selected';}?>> <?php echo $course['course_name'];?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-2 control-label"> Select Class <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <select name="class" class="form-control input-inline input-large subjectyear" required>
                                                    <option value=""> Select Class </option>
                                                    <option value="1" <?php if($lecture[0]['class']==1){echo 'selected';}?>> First Year </option>
                                                    <option value="2" <?php if($lecture[0]['class']==2){echo 'selected';}?>> Second Year </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-2 control-label"> Select Session <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <?php $mysession = explode(',',$lecture[0]['session']);?>
                                                <select name="session[]" class="form-control input-inline input-large classes select2" required multiple>
                                                    <?php foreach ($sessions as $session): ?>
                                                        <option value="<?php echo $session['session_name'];?>" <?php if(in_array($session['session_name'],$mysession)){echo 'selected';}?>> <?php echo $session['session_name'];?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-2 control-label"> Select Subject <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <?php
                                                    // echo '<pre>';
                                                    // print_r($subjects);
                                                    // echo '</pre>';
                                                    $mysubjects = explode(',',$lecture[0]['subjects']);
                                                ?>
                                                <select name="subject[]"  class="form-control input-inline input-large course_subjects select2" multiple required>
                                                    <?php foreach ($subjects as $subject): ?>
                                                        <option value="<?php echo $subject['course_subject_id'];?>" <?php if(in_array($subject['course_subject_id'],$mysubjects)){echo 'selected';}?>> <?php echo $subject['subject_name'];?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>


                                        <div class="form-group">
                                            <label class="col-md-2 control-label"> Select Shift <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <select name="shift" class="form-control input-inline input-large shifts" required>
                                                    <option value=""> Select Shift </option>
                                                    <?php foreach ($shifts as $shift): ?>
                                                        <option value="<?php echo $shift['id'];?>" <?php if($shift['id']==$lecture[0]['shift']){echo 'selected';}?>> <?php echo $shift['name'];?></option>
                                                   <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-2 control-label"> Select Study Type <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <select name="studytype" class="form-control input-inline input-large studytype" required>
                                                    <option value=""> Select Study Type </option>
                                                    <?php foreach ($studytype as $type): ?>
                                                        <option value="<?php echo $type['id'] ?>" <?php if($type['id']==$lecture[0]['studytype']){echo 'selected';}?>> <?php echo $type['name'];?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
										<div class="form-group">
                                            <label class="col-md-2 control-label"> Select Days <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <select name="days[]" class="form-control input-inline input-large select2 days" required multiple>
                                                    <?php foreach ($days as $day): ?>
                                                        <option value="<?php echo $day;?>" <?php if(in_array($day,$days)){echo 'selected';}?>> <?php echo $day;?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>


                                        <div class="form-group">
                                            <label class="col-md-2 control-label"> Select Room <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <select name="room" class="form-control input-inline input-large room" required>
                                                    <?php foreach ($rooms as $room): ?>
                                                        <option value="<?php echo $room['room_id'] ?>" <?php if($room['room_id']==$lecture[0]['room']){echo 'selected';}?>> <?php echo $room['room_name'];?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-2 control-label"> Select Primary Teacher <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <select name="teacher" class="form-control input-inline input-large select2" id="select2_sample1"  required>
                                                    <option value=""> Select Teacher </option>
                                                    <?php foreach ($teachers as $teacher): ?>
                                                        <option value="<?php echo $teacher['user_id'] ?>" <?php if($teacher['user_id']==$lecture[0]['teacher']){echo 'selected';}?>> <?php echo $teacher['first_name'].' '.$teacher['last_name'].' ( '.$teacher['campus_name'].' ) ';?></option>
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
                                                        <option value="<?php echo $teacher['user_id'] ?>" <?php if($teacher['user_id']==$lecture[0]['second_teacher']){echo 'selected';}?>> <?php echo $teacher['first_name'].' '.$teacher['last_name'].' ( '.$teacher['campus_name'].' ) ';?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
										

                                        <div class="form-group">
                                            <label class="col-md-2 control-label">Start Time</label>
                                            <div class="col-md-9">
                                                <input type="time"  name="start_time" id="start_time" value="<?php echo $lecture[0]['start_date'];?>"  class="form-control mobile" />
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-2 control-label">End Time</label>
                                            <div class="col-md-9">
                                                <input type="time"  name="end_time" id="end_time" value="<?php echo $lecture[0]['end_date'];?>"  class="form-control mobile" />
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-2 control-label">Lecture Name</label>
                                            <div class="col-md-4">
                                                <input type="text"  name="lecture_name" value="<?php echo $lecture[0]['lecture_name'];?>" class="form-control" />
                                            </div>
                                        </div>

                                    </div>

                                    <div class="form-actions">
                                        <div class="row">
                                            <div class="col-md-offset-3 col-md-9">

                                                <button type="submit" class="btn red">Update</button>

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
        $('.campus').change(function(){
            var campus_id = $(this).val();
            jQuery.ajax({
                type: "post",
                async: false,
                url: '<?php echo site_url()?>/timetable/getShifts',
                data: {
                    campus_id : campus_id
                },
                success: function(data) {
                    $('.shifts').html(data);
                }
            });
        });
    });
</script>