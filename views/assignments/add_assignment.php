
<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
    <div class="page-content">


        <!-- BEGIN PAGE HEADER-->
        <!--<h3 class="page-title">
        Edit Profile <small>You can edit your profile here</small>
        </h3>-->

        <!-- END PAGE HEADER-->
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
                            <i class="fa fa-plus"></i> Add Assignment
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/assignments/center" target="_blank">
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
                                        <select name="chapter_id" class="form-control input-inline input-large chapter_id" required>

                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Topics <span class="required">*</span></label>
                                    <div class="col-md-9 checkbox-list topic_ids">

                                    </div>
                                </div>
                                <div class="mcqs" style="display:none;">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Number of MCQs <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="number" min="0" class="form-control input-inline input-large" name="mcqs" placeholder="Enter number of mcqs" value="0">
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Marks per MCQ <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="number" min="0" class="form-control input-inline input-large" name="marks_mcq" placeholder="Enter per mcq marks" value="0">
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Number of Short Questions <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="number" min="0" class="form-control input-inline input-large" name="short_questions" placeholder="Enter number of short questions" value="0">
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Marks per Short Question <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="number" min="0" class="form-control input-inline input-large" name="short_question_mcq" placeholder="Enter per short question marks" value="0">
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Practicals <span class="required">*</span></label>
                                    <div class="col-md-9 checkbox-list practical_ids">

                                    </div>
                                </div>
                                <div class="practicals" style="display:none;">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Marks per Practical <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="number" min="0" class="form-control input-inline input-large" name="marks_practical" placeholder="Enter per practical marks" value="0">
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Start Date</label>
                                    <div class="col-md-3">
                                        <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                            <input type="text" name="start_date" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
                                            <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                        </div>
                                        <!-- /input-group -->
                                        <!--<span class="help-block">
                                        Select date </span>-->
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">End Date</label>
                                    <div class="col-md-3">
                                        <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                            <input type="text" name="end_date" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
                                            <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                        </div>
                                        <!-- /input-group -->
                                        <!--<span class="help-block">
                                        Select date </span>-->
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <button type="submit" class="btn green">Create Assignment</button>
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