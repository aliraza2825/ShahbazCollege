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
                                <i class="fa fa-list"></i> Check Attendance
                            </div>
                        </div>
                        <div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo current_url();?>">
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label col-md-3">From Date <span class="required">*</span></label>
                                                <div class="col-md-3">
                                                    <div class="input-group input-medium date date-picker" data-date="<?php echo $from_date;?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                        <input type="text" name="from_date" class="form-control from_date" value="<?php echo $from_date;?>" readonly>
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                            </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label col-md-3">To Date <span class="required">*</span></label>
                                                <div class="col-md-3">
                                                    <div class="input-group input-medium date date-picker" data-date="<?php echo $to_date;?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                        <input type="text" name="to_date" class="form-control to_date" value="<?php echo $to_date;?>" readonly>
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                            </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label col-md-3">Study Type <span class="required">*</span></label>
                                                <div class="col-md-3">
                                                    <select class="form-control input-medium study_type" name="study_type" required>
                                                        <option value="">Select Study Type</option>
        												<?php
        													foreach($study_types as $study_type):
        												?>
                                                        <option value="<?php echo $study_type['id'];?>" <?php if($study_type['id']==$selected_study_type){echo 'selected';}?>><?php echo $study_type['name'];?></option>
                                                        <?php
                                                        	endforeach;
        												?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label col-md-3">Shift <span class="required">*</span></label>
                                                <div class="col-md-3">
                                                    <select class="form-control input-medium shift" name="shift" required>
                                                        <option value="">Select Shift</option>
        												<?php
        													foreach($shifts as $shift):
        												?>
                                                        <option value="<?php echo $shift['id'];?>" <?php if($shift['id']==$selected_shift){echo 'selected';}?>><?php echo $shift['name'];?></option>
                                                        <?php
                                                        	endforeach;
        												?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label col-md-3">Class <span class="required">*</span></label>
                                                <div class="col-md-3">
                                                    <select class="form-control input-medium" name="section" required>
                                                        <option value="">Select Class</option>
                                                        <option value="First Year" <?php if($section=='First Year'){echo 'selected';}?>>First Year</option>
                                                        <option value="Second Year" <?php if($section=='Second Year'){echo 'selected';}?>>Second Year</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-offset-3 col-md-9">
                                            <input type="hidden" name="submit" value="1" />
                                            <button type="submit" class="btn green">Check Attendance</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- END EXAMPLE TABLE PORTLET-->
                </div>
            </div>
            <?php
                $days = explode(',',@$lectures[0]['days']);
            ?>
			<div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> Students List
							</div>
						</div>
						<div class="portlet-body">
						    <?php
						        $begin = new DateTime($from_date);
                                $end = new DateTime($to_date);
                                
                                $interval = DateInterval::createFromDateString('1 day');
                                $period = new DatePeriod($begin, $interval, $end);
						    ?>
							<table class="table table-striped table-bordered table-hover" id="sample_3">
    							<thead>
        							<tr>
                                        <th class="hidden">
        									 Hidden
        								</th>
                                        <th>
        									 ID
        								</th>
                                        <th>
        									 Student Name / Roll No.
        								</th>
        								<?php
        								    foreach ($period as $dt):
        								        if (in_array(strtolower($dt->format("l")), $days)):
        								?>
        								<th>
        								    <?php echo $dt->format("l Y-m-d");?>
        								</th>
        								<?php
        								        endif;
        								    endforeach;
        								?>
        							</tr>
    							</thead>
    							<tbody>
    							    <?php
    							        $i=1;
    							        foreach($students as $student):
    							    ?>
                                    <tr class="odd gradeX">
        								<td class="hidden">
        								    <?php echo $i;?>
        								</td>
        								<td>
        								    <?php echo $i;?>
        								</td>
        								<td>
        								    <?php echo $student['first_name'].' '.$student['last_name'].' / '.$student['roll_no'];?>
        								</td>
        								<?php
        								    foreach ($period as $dt):
        								        if (in_array(strtolower($dt->format("l")), $days)):
        								?>
        								<td>
        								    <table class="table table-bordered">
        								        <tbody>
        								            <tr>
        								                <?php
            								                for($a=0;$a<count($lectures);$a++):
            								                    $this->db->where_in('course_subject_id',explode(',',$lectures[$a]['subjects']));
            								                    $subjects = $this->db->get_where('course_subjects')->result_array();
            								                    $lecture_subjects = '';
            								                    foreach($subjects as $subject)
            								                    {
            								                        $lecture_subjects .=$subject['subject_name'].'|';
            								                    }
            								            ?>
        								                <td title="<?php echo rtrim($lecture_subjects, '|');?>"><?php echo $a+1;?></td>
        								                <?php
            								                endfor;
            								            ?>
            								            <td>Attendance %</td>
        								            </tr>
        								            <tr>
        								                <?php
            								                $present=0;
            								                for($a=0;$a<count($lectures);$a++):
            								            ?>
        								                <td>
        								                    <?php
        								                        $check = $this->db->get_where('lecture_wise_attendance',array('lecture_id'=>$lectures[$a]['id'],'student_id'=>$student['student_id'],'date'=>$dt->format("Y-m-d")))->result_array();
        								                        if(count($check)>0)
        								                        {
        								                            echo 'P';
        								                            $present++;
        								                        }
        								                        else
        								                        {
        								                            echo 'A';
        								                        }
        								                    ?>
        								                </td>
        								                <?php
            								                endfor;
            								            ?>
            								            <td>
        								                    <?php
        								                        if($present==0)
        								                        {
        								                            echo '0%';
        								                        }
        								                        else
        								                        {
        								                            $total_lectures = count($lectures);
        								                            echo round($present/$total_lectures*100).'%';
        								                        }
        								                    ?>
        								                </td>
        								            </tr>
        								        </tbody>
        								    </table>
        								</td>
        								<?php
        								        endif;
        								    endforeach;
        								?>
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