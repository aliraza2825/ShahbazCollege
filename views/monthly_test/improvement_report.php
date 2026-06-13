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

        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-line-chart"></i> Improvement System Report
                </div>
            </div>

            <div class="portlet-body">

                <form method="post" action="<?php echo site_url(); ?>/collegepapers/improvement_report">
                    <div class="row">

                        <div class="col-md-4">
                            <label>Exam</label>
                            <select name="exam_id" class="form-control">
                                <option value="">All Exams</option>
                                <?php foreach($exams as $exam): ?>
                                    <option value="<?php echo $exam['id']; ?>"
                                        <?php echo ($selected_exam_id == $exam['id']) ? 'selected' : ''; ?>>
                                        <?php echo $exam['exam_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group col-md-4">
                            <label class="control-label">Study Campus </label>
                            <div>
                                <select class="form-control campus" name="campus_id">
                                    <option value="">ALL CAMPUS</option>
									<?php
										foreach($campuses as $campus):
									?>
                                    <option value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name'];?></option>
                                    <?php
                                    	endforeach;
									?>
                                </select>
                                <!--<span class="help-inline"></span>-->
                            </div>
                        </div>
						<div class="form-group col-md-4">
                            <label class="control-label">Course <span class="required">*</span></label>
                            <div >
                                <select class="form-control course_id" name="course_id" required>
                                    <option value="">ALL COURSE</option>
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
                        <div class="col-md-4">
							
                            <div class="form-group">
                                <label class="col-md-3 control-label">Class</label>
                                <div class="col-md-9 radio-list">
                                    <label class="radio-inline">
                                        <input type="radio" name="class" id="optionsRadios1" value="1" checked> 1st Year </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="class" id="optionsRadios2" value="2"> 2nd Year </label>
                                </div>
                            </div>
							
						</div>
						<div class="col-md-4">
						    <div class="form-group">
								<label class="col-md-3 control-label">Badge </label>
								<div class="col-md-5">
									<select class="form-control classes" name="badge">
									</select>
									<!--<span class="help-inline"></span>-->
								</div>
							</div>
						</div>

                        <div class="col-md-4" style="margin-top:25px;">
                            <button type="submit" class="btn green">
                                Search
                            </button>
                            
                            <button type="submit"
                                    class="btn blue"
                                    formaction="<?php echo site_url(); ?>/collegepapers/overall_class_performance">
                                        Overall Class Performance
                            </button>

                            <a href="<?php echo site_url(); ?>/Monthly_test/improvement_report" class="btn default">
                                Reset
                            </a>
                        </div>

                    </div>
                </form>

                <hr>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="sample_2">
                        <thead>
                        <tr>
                            <th>Student</th>
                            <th>Class</th>
                            <th>Exam Name</th>

                            <?php if (!empty($max_attempts) && $max_attempts > 0): ?>
                                <?php for($i = 1; $i <= $max_attempts; $i++): ?>
                                    <th>Attempt <?php echo $i; ?></th>
                                <?php endfor; ?>
                            <?php endif; ?>

                            <th>Improvement Count</th>
                            <th>Reward</th>
                            <th>Reward Detail</th>
                            <th>Reward Given</th>
                        </tr>
                        </thead>

                        <tbody>
                        
                            <?php foreach($report as $row): ?>
                                <tr>
                                    <td><?php echo $row['student']; ?></td>
                                    <td><?php echo $row['class']; ?></td>
                                    <td><?php echo $row['exam_name']; ?></td>

                                    <?php for($i = 0; $i < $max_attempts; $i++): ?>
                                    <td>
                                        <?php if(isset($row['attempts'][$i])): ?>
                                        <a target="_blank"
                                           href="<?php echo site_url(); ?>/collegepapers/improvement_month_detail/<?php echo $row['student_id']; ?>/<?php echo $row['exam_id']; ?>/<?php echo $row['attempts'][$i]['month_key']; ?>"
                                           style="display:block; padding:8px; background:#eef5ff; border:1px solid #337ab7; border-radius:5px; text-decoration:none; color:#000;">
                
                                            <strong><?php echo $row['attempts'][$i]['month_name']; ?></strong>
                                            <br>
                                
                                            <?php echo $row['attempts'][$i]['percentage']; ?>%
                                
                                            <br>
                                
                                            <small>
                                                Total:
                                                <?php echo $row['attempts'][$i]['obtain_marks']; ?>
                                                /
                                                <?php echo $row['attempts'][$i]['total_marks']; ?>
                                            </small>
                                
                                            <br>
                                
                                            <small>
                                                Papers:
                                                <?php echo $row['attempts'][$i]['papers_count']; ?>
                                            </small>
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                <?php endfor; ?>

                                    <td><?php echo $row['improvement_count']; ?></td>

                                    <td>
                                        <?php if($row['reward'] == 'Eligible'): ?>
                                            <span class="label label-success">Eligible</span>
                                        <?php else: ?>
                                            <span class="label label-danger">Not Eligible</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php echo !empty($row['reward_text']) ? $row['reward_text'] : '-'; ?>
                                    </td>
                                    
                                    <td>

                                    <?php
                                    $rewardEvents = array();
                                    
                                    foreach($row['attempts'] as $attempt):
                                    
                                        if($attempt['is_improved'] == 1):
                                    
                                            $reward_rule = $attempt['reward_rule'];
                                    
                                            $rewardTitle = array();
                                    
                                            if(!empty($reward_rule)) {
                                    
                                                if($reward_rule['certificate'] == 1){
                                                    $rewardTitle[] = 'Certificate';
                                                }
                                    
                                                if($reward_rule['cash_amount'] > 0){
                                                    $rewardTitle[] = 'Cash: '.$reward_rule['cash_amount'];
                                                }
                                            }
                                    ?>
                                    
                                        <div style="
                                            border:1px solid #ddd;
                                            padding:8px;
                                            margin-bottom:8px;
                                            border-radius:5px;
                                            background:#fafafa;
                                        ">
                                    
                                            <strong>
                                                <?php echo $attempt['month_name']; ?>
                                            </strong>
                                    
                                            <br>
                                    
                                            Improvement #
                                            <?php echo $attempt['improvement_no']; ?>
                                    
                                            <br>
                                    
                                            <?php if(!empty($rewardTitle)): ?>
                                    
                                                <span class="label label-info">
                                                    <?php echo implode(' + ', $rewardTitle); ?>
                                                </span>
                                    
                                                <br><br>
                                    
                                                <?php if($attempt['reward_given'] == 1): ?>
                                    
                                                    <span class="label label-success">
                                                        Reward Given
                                                    </span>
                                    
                                                    <?php if(!empty($attempt['reward_given_data']['cash_amount'])): ?>
                                    
                                                        <br>
                                    
                                                        <small>
                                                            Amount:
                                                            <?php echo $attempt['reward_given_data']['cash_amount']; ?>
                                                        </small>
                                    
                                                    <?php endif; ?>
                                    
                                                    <?php if(!empty($attempt['reward_given_data']['proof_image'])): ?>
                                    
                                                        <br>
                                    
                                                        <a href="<?php echo base_url($attempt['reward_given_data']['proof_image']); ?>"
                                                           target="_blank">
                                                            View Proof
                                                        </a>
                                    
                                                    <?php endif; ?>
                                    
                                                <?php else: ?>
                                    
                                                    <button type="button"
                                                            class="btn btn-xs green giveRewardBtn"
                                    
                                                            data-student-id="<?php echo $row['student_id']; ?>"
                                    
                                                            data-exam-id="<?php echo $row['exam_id']; ?>"
                                    
                                                            data-month-key="<?php echo $attempt['month_key']; ?>"
                                    
                                                            data-reward-rule-id="<?php echo $reward_rule['id']; ?>"
                                    
                                                            data-improvement-count="<?php echo $attempt['improvement_no']; ?>">
                                                        Give Reward
                                                    </button>
                                    
                                                <?php endif; ?>
                                    
                                            <?php else: ?>
                                    
                                                <span class="label label-default">
                                                    No Reward Rule
                                                </span>
                                    
                                            <?php endif; ?>
                                    
                                        </div>
                                    
                                    <?php
                                        endif;
                                    endforeach;
                                    ?>
                                    
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
</div>
<div class="modal fade" id="giveRewardModal" tabindex="-1" data-width="600">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"></button>
        <h4 class="modal-title">Give Reward</h4>
    </div>

    <form method="post"
          enctype="multipart/form-data"
          action="<?php echo site_url(); ?>/collegepapers/give_monthly_test_reward">

        <div class="modal-body">

            <input type="hidden" name="student_id" id="reward_student_id">
            <input type="hidden" name="exam_id" id="reward_exam_id">
            <input type="hidden" name="month_key" id="reward_month_key">
            <input type="hidden" name="reward_rule_id" id="reward_rule_id">
            <input type="hidden" name="improvement_count" id="reward_improvement_count">

            <div class="form-group">
                <label>Proof Image / File</label>
                <input type="file" name="proof_image" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Remarks</label>
                <textarea name="remarks" class="form-control" rows="3"></textarea>
            </div>

        </div>

        <div class="modal-footer">
            <button type="button" class="btn default" data-dismiss="modal">Close</button>
            <button type="submit" class="btn green">Save Reward</button>
        </div>

    </form>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    jQuery(document).ready(function(){
            $(document).on('click', '.giveRewardBtn', function () {

                $('#reward_student_id').val($(this).data('student-id'));
            
                $('#reward_exam_id').val($(this).data('exam-id'));
            
                $('#reward_month_key').val($(this).data('month-key'));
            
                $('#reward_rule_id').val($(this).data('reward-rule-id'));
            
                $('#reward_improvement_count').val($(this).data('improvement-count'));
            
                $('#giveRewardModal').modal('show');
            
            });
                    
            jQuery('.campus').change(function(){
                var campus_id = jQuery(this).val();
                var cnic = jQuery('#cnic2').val();

                if(campus_id!='')
                {
                    jQuery.ajax({
                        type: "post",
                        async: false,
                        url: '<?php echo site_url()?>/students/getCampusCourses',
                        data: {
                            campus_id : campus_id,
                            cnic : cnic,
                        },
                        success: function(data) {
                            console.log(data);
                            jQuery('.course_id').html('');
                            jQuery('.course_id').html(data);
                            GetShifts(campus_id);
                        }

                    });
                }
            });
            jQuery('.course_id').change(function(){
                    var course_id = jQuery(this).val();
                    var campus_id = jQuery('.campus').val();

                    if(course_id!='' &&  campus_id!='' )
                    {
                        jQuery.ajax({
                            type: "post",
                            async: false,
                            url: '<?php echo site_url()?>/students/getCampusClass',
                            data: {
                                campus_id : campus_id,
                                course_id : course_id,
                            },
                            success: function(data) {
                                console.log(data);
                                jQuery('.classes').html('');
                                jQuery('.classes').html(data);
                            }

                        });
                    }
                });
    });
});
            
</script>