
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
								<i class="fa fa-edit"></i> Edit Exam Sequence
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/councils/update_exam_sequence" enctype="multipart/form-data">
								<div class="form-body">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Select Courses <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select class="form-control input-inline input-large select2" name="course_id" id="course_id" required>
                                                <option value="">SELECT COURSE</option>
                                                <?php
                                                foreach($courses as $course):
                                                ?>
                                                    <option value="<?php echo $course['course_id'];?>" data-duration="<?php echo $course['course_duration_year'];?>"
                                                                data-type="<?php echo $course['course_type'];?>" <?php if($seq->course_id==$course['course_id']){echo 'selected';}?>><?php echo $course['course_name'];?></option>
                                                <?php
                                                endforeach;
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <hr />
                                    <?php $course = $this->db->get_where('courses','course_id = '.$seq->course_id)->row_array(); ?>
                                    <div class="form-group">
                                            <label class="col-md-3 control-label"> Select Exam <?php echo $course['course_type'] ?> <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <select class="form-control input-inline input-large select2" name="exam_year" id="exam_year" required>
                                                    <option value="">SELECT EXAM</option>
                                                    <?php
                                                        
                                                        $type = $course['course_type'] == 'Annual' ? 'Class' : $course['course_type'];
                                                    for($i = 1;$i <= $course['course_duration_year']; $i++){?>
                                                        <option value="<?php echo $i;?>" <?php if($seq->class == $i) echo 'selected'; ?>><?php echo $type.' '.$i ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">First Year Exam Type <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <label class="radio-inline">
                                            <input type="radio" name="first_year_type" id="optionsRadios4" value="supplementary" <?php if($seq->first_year_type=='supplementary'){echo 'checked';}?>> Supplementary </label>
                                            <label class="radio-inline">
                                            <input type="radio" name="first_year_type" id="optionsRadios5" value="annual" <?php if($seq->first_year_type=='annual'){echo 'checked';}?>> Annual </label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label"> Next Exam No First Year<span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="number" class="form-control input-inline input-large" name="first_year" placeholder="Enter Exam No" value="<?php echo $seq->first_year?>" required>
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
                                </div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                            <input value = "<?php echo @$seq->id?>" name="seq_id" type="hidden"  />
                                            <input value = "1" name="submit" type="hidden" />
                                            <button type = "submit" class="btn green">Update Sequence</button>
											<button onclick="location.href = '<?php echo site_url().'/councils/council_exam_sequence';?>'" type="button" class="btn default">Cancel</button>
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
            	if(count(@$sequences)>0):
			?>
            <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> All Exams Sequences
							</div>
						</div>
						<div class="portlet-body">
                            <table class="table table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
                                <th class="hidden">
									 Hidden
								</th>
								<th>
									 Course Name
								</th>
								<th>
									 Type
								</th>
								<th>
									 First Year Exam No
								</th>
								<th>
									 Second Year Exam No
								</th>
                                <th>
                                    First Year FILES
                                </th>
                                <th>
                                    Second Year FILES
                                </th>
								<th>
									 Action
								</th>

							</tr>
							</thead>
							<tbody>
							<?php
								$i=0;
								foreach ($sequences as $sequence):
							?>
                            <tr>
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
                                <td>
									<?php echo $sequence['course_name'];?>
								</td>
                                <td>
									<?php echo  strtoupper($sequence['type']);?>
								</td>
                                <td>
									<?php echo  $sequence['first_year'];?>
								</td>
                                <td>
									<?php echo  $sequence['second_year'];?>
								</td>
                                <td>
									<?php
                                        if ($sequence['first_year_roll_no'] != null)
                                            echo '<a href="'.base_url().'exam_sequence_documents/'.$sequence['first_year_roll_no'].'" target="_blank" class="btn btn-info">Roll No Slips</a>';
                                        if ($sequence['first_year_date_sheet'] != null)
                                            echo '<a href="'.base_url().'exam_sequence_documents/'.$sequence['first_year_date_sheet'].'" target="_blank" class="btn btn-info">Council Datesheet</a>';
                                        if ($sequence['first_year_date_sheet_nts'] != null)
                                            echo '<a href="'.base_url().'exam_sequence_documents/'.$sequence['first_year_date_sheet_nts'].'" target="_blank" class="btn btn-info">NTS Datesheet</a>';
                                        if ($sequence['first_year_result'] != null)
                                            echo '<a href="'.base_url().'exam_sequence_documents/'.$sequence['first_year_result'].'" target="_blank" class="btn btn-info">Result</a>';

                                    ?>
								</td>
                                <td>
									<?php
                                        if ($sequence['first_year_roll_no'] != null)
                                            echo '<a href="'.base_url().'exam_sequence_documents/'.$sequence['second_year_roll_no'].'" target="_blank" class="btn btn-info">Roll No Slips</a>';
                                        if ($sequence['second_year_date_sheet'] != null)
                                            echo '<a href="'.base_url().'exam_sequence_documents/'.$sequence['second_year_date_sheet'].'" target="_blank" class="btn btn-info">Council Datesheet</a>';
                                        if ($sequence['second_year_date_sheet_nts'] != null)
                                            echo '<a href="'.base_url().'exam_sequence_documents/'.$sequence['second_year_date_sheet_nts'].'" target="_blank" class="btn btn-info">NTS Datesheet</a>';
                                        if ($sequence['second_year_result'] != null)
                                            echo '<a href="'.base_url().'exam_sequence_documents/'.$sequence['second_year_result'].'" target="_blank" class="btn btn-info">Result</a>';

                                    ?>
								</td>
                                <td>
                                    <a href="<?php echo site_url().'/councils/edit_council_exam_sequence/'.$sequence['id']?>" class="btn btn-info">
                                        Edit
                                    </a>
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
    document.addEventListener( "DOMContentLoaded", function(){
        jQuery(document).ready(function(){
            jQuery('.select2').select2();
            $('#course_id').on('change', function(){

                var duration = $(this).find(':selected').data('duration');
                var type = $(this).find(':selected').data('type');
                if(type == 'Annual'){
                    type = 'Class';
                }
            
                var html = '<option value="">SELECT EXAM</option>';
            
                if(duration)
                {
                    for(var i = 1; i <= duration; i++)
                    {
                        html += '<option value="'+i+'">'+type+' '+i+'</option>';
                    }
                }
            
                $('#exam_year').html(html);
            
            });
        });
    }, false );
</script>