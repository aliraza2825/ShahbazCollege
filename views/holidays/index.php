
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
								<i class="fa fa-calendar"></i> Add holiday
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/holidays/insert">
								<div class="form-body">
									
                                    <div class="form-group">
                                        <label class="col-md-3 control-label"> Campus <span class="required">*</span></label>
                                        <div class="col-md-6">
                                            <select name="campus_ids[]" class="form-control select2 campus_ids" id="select2_sample1"   multiple required>
												<?php
                                                    foreach($campuses as $campus):
                                                ?>
                                                <option value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name'];?></option>
                                                <?php
                                                    endforeach;
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label"> Staff Type <span class="required">*</span></label>
                                        <div class="col-md-6">
                                            <select class="form-control select2 staff_type_ids" name="staff_type_ids[]" id="select2_sample2"  multiple required>
                                                  
												<?php
                                                    foreach($types as $type):
                                                ?>
                                                <option value="<?php echo $type['staff_type_id'];?>"><?php echo $type['staff_type_name'];?></option>
                                                <?php
                                                    endforeach;
                                                ?>												
                                            </select>
                                        </div>
                                    </div>
									<div class="form-group">
                                        <label class="col-md-3 control-label"> Staff <span class="required">*</span></label>
                                        <div class="col-md-6">
                                            <select class="form-control select2 user_ids" name="user_ids[]" id="select2_sample3"  multiple required>

                                            </select>
                                        </div>
                                    </div>
									<div class="form-group">
                                        <label class="col-md-3 control-label"> Shift <span class="required">*</span></label>
                                        <div class="col-md-6">
                                            <select class="form-control select2 shift_ids" name="shift_ids[]" id="select2_sample4"  multiple required>

                                            </select>
                                        </div>
                                    </div>
									<div class="form-group">
                                        <label class="col-md-3 control-label"> Students <span class="required">*</span></label>
                                        <div class="col-md-6">
											<label class="mt-checkbox">
												<input type="checkbox" id="include_all_students" checked />
												Include all students of selected shifts
												<span></span>
											</label>
											<input type="hidden" name="student_ids" id="student_ids_hidden" value="" />
											<span class="help-block" id="students_count_label">0 students selected</span>
                                        </div>
                                    </div>
								</div>
								<div class="form-group">
                                        <label class="control-label col-md-3">Holiday Date</label>
                                        <div class="col-md-3">
                                            <div class="input-group" >
                                                <input type="date" name="date[]" class="form-control" value="" required />
                                            </div>
                                        </div>
                                    </div>
									<div class="date_area">

									</div>
									<button type="button" class="btn green add_date"><i class="fa fa-plus"></i> Add Date</button>
									<div class="form-group">
                                                        <label class="col-md-3 control-label">Reason in Details</label>
                                                        <div class="col-md-9">
                                                                <textarea class="form-control remarks" rows="3" name="reason_detail"></textarea>
                                                        </div>
                                                    </div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Add Holiday</button>
											<button onclick="location.href = '<?php echo site_url()?>'" type="button" class="btn default">Cancel</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> Check Holidays
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/holidays">
								<div class="form-body">
									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label class="control-label col-md-3">From Date <span class="required">*</span></label>
												<div class="col-md-3">
													<div class="input-group input-medium date date-picker" data-date="<?php echo $from_date;?>" data-date-format="yyyy-mm-dd" <?php if(@$myAccess[0]['expense_no_of_days']!=1 && $this->session->userdata('role') != 'Admin'): ?>   data-date-end-date="0d" <?php endif;?> data-date-viewmode="years">
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
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Check Holidays</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>


			<div class="row">
				<div class="col-md-12">
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> Holidays
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
									 Date
								</th>
								<th>
									 Reason
								</th>
								<th>
									 Campuses
								</th>
								<th>
									 Staff Types
								</th>
								<th>
									 Shifts
								</th>
								 <th>
									 Add By
								</th>
                                <th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
                            <?php
								$i=0;
								foreach($holidays as $holiday):
							?>
                            <tr class="odd gradeX <?php if($holiday['cancel']==1){echo 'danger';}?>">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
								<td>
									<?php echo date('F d, Y', strtotime($holiday['date']));?>
								</td>
								<td>
									<?php echo $holiday['reason'];?>
								</td>
								<td>
									<?php $campuses = $this->db->where_in('campus_id',explode(',',$holiday['campus_ids']))->get('campuses')->result_array();
										
										foreach($campuses as $campus)
										{
											echo $campus['campus_name'].'<br />';
										}
									
									?>
								</td>
								<td>
									<?php 
										$staff = $this->db->where_in('staff_type_id',explode(',',$holiday['staff_type_ids']))->get('staff_type')->result_array();
										foreach($staff as $campus)
										{
											echo $campus['staff_type_name'].'<br />';
										}
									
									?>
								</td>
								<td>
									<?php 
										$shift_ids = array_filter(explode(',',$holiday['shift_ids']));
										if(!empty($shift_ids))
										{
											$shifts = $this->db
												->select('shifts.name, study_type.name as study_type_name, courses.course_name')
												->from('shifts')
												->join('study_type', 'study_type.id = shifts.study_type_id', 'left')
												->join('courses', 'courses.course_id = study_type.course_id', 'left')
												->where_in('shifts.id', $shift_ids)
												->get()
												->result_array();
											foreach($shifts as $shift)
											{
												$parts = array($shift['name']);
												if(!empty($shift['study_type_name']))
												{
													$parts[] = $shift['study_type_name'];
												}
												if(!empty($shift['course_name']))
												{
													$parts[] = $shift['course_name'];
												}
												echo implode(' - ', $parts).'<br />';
											}
										}
									
									?>
								</td>
								<td>
									<?php echo $holiday['add_by'];?>
								</td>
                                <td>
                                	<?php
                                    	if($holiday['cancel']==0):
									?>
                                    <button data-holiday-id="<?php echo $holiday['holiday_id'];?>" title="cancel" class="btn yellow cancel_holiday" >Cancel Holiday</button>
									<?php
										else:
											echo '<strong>Cancel Reason :</strong>'.$holiday['cancel_reason'];
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
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>

<div class="modal fade" id="cancel_holiday" tabindex="-1"   data-width="1200" >
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Cancel Holiday</h4>
    </div>
    <div class="modal-body">
		<form method="post" action="<?php echo site_url();?>/holidays/cancel_holiday">
			<div class="form-actions">
				<div class="row">
					<div class="form-group">
						<label class="control-label col-md-2">Cancel Reason <span class="required">*</span></label>
						<div class="col-md-10">
							<textarea name="cancel_reason" class="form-control" required></textarea>
						</div>
					</div>
					<div class="col-md-offset-2 col-md-9">
						<input type="hidden" name="holiday_id" class="holiday_id" value=""/>
						<button type="type" class="btn red">Submit</button>
					</div>
				</div>
			</div>
		</form>
    </div>
</div>
	
	
	<script>
	
	window.addEventListener('DOMContentLoaded',function () {
    //your code here

		var selectedShiftStudentIds = '';

		function syncStudentHiddenField() {
			if($('#include_all_students').is(':checked') && selectedShiftStudentIds !== '')
			{
				$('#student_ids_hidden').val(selectedShiftStudentIds);
				var count = selectedShiftStudentIds.split(',').filter(Boolean).length;
				$('#students_count_label').text(count + ' students selected');
			}
			else
			{
				$('#student_ids_hidden').val('');
				$('#students_count_label').text('0 students selected');
			}
		}

		$('.add_date').click(function(){
			var html = '<div class="comission"><div class="row"><div class="col-md-12"><div class="form-group"><label class="col-md-3 control-label">Select Date <span class="required">*</span></label>	<div class="col-md-3"><div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years"><input type="date" name="date[]" class="form-control" value="" required> </div></div>	<div class="col-md-3"><button type="button" class="btn red remove_line"><i class="fa fa-trash"></i> Remove</button></div></div></div>';
			$('.date_area').append(html)
		});

		$('.remove_line').live('click',function(){
			$(this).parents('.comission').remove();
		});
		$('.date-picker').datepicker({
			rtl: Metronic.isRTL(),
			autoclose: true
		});

		$('.campus_ids').change(function(){
			var staff_type_ids = $('.staff_type_ids').select2('val');
			var campus_ids = $('.campus_ids').select2('val');
			if(staff_type_ids && staff_type_ids.length > 0 && campus_ids && campus_ids.length > 0)
			{
				$.ajax({
					type: "post",
					async: false,
					url: '<?php echo site_url()?>/holidays/findStaff',
					data: {
						campus_ids : campus_ids,
						staff_type_ids : staff_type_ids
					},
					success: function(data) {
						//console.log(data);
						$('.user_ids').select2('destroy');
						$('.user_ids').html(data);
						$('.user_ids').select2();
					}
				});
			}

			if(campus_ids && campus_ids.length > 0)
			{
				$.ajax({
					type: "post",
					async: false,
					url: '<?php echo site_url()?>/holidays/findShifts',
					data: {
						campus_ids : campus_ids
					},
					success: function(data) {
						//console.log(data);
						$('.shift_ids').select2('destroy');
						$('.shift_ids').html(data);
						$('.shift_ids').select2();
						$('.shift_ids').trigger('change');
					}
				});
			}
			else
			{
				$('.shift_ids').select2('destroy');
				$('.shift_ids').html('');
				$('.shift_ids').select2();
				selectedShiftStudentIds = '';
				syncStudentHiddenField();
			}
		});

		$('.staff_type_ids').change(function(){
			var staff_type_ids = $('.staff_type_ids').select2('val');
			var campus_ids = $('.campus_ids').select2('val');

			if(staff_type_ids && staff_type_ids.length > 0 && campus_ids && campus_ids.length > 0)
			{
				$.ajax({
					type: "post",
					async: false,
					url: '<?php echo site_url()?>/holidays/findStaff',
					data: {
						campus_ids : campus_ids,
						staff_type_ids : staff_type_ids
					},
					success: function(data) {
						//console.log(data);
						$('.user_ids').select2('destroy');
						$('.user_ids').html(data);
						$('.user_ids').select2();
					}
				});
			}
		});

		$('#include_all_students').change(function(){
			syncStudentHiddenField();
		});

		$('.shift_ids').change(function(){
			var shift_ids = $('.shift_ids').select2('val');
			selectedShiftStudentIds = '';
			syncStudentHiddenField();

			if(shift_ids && shift_ids.length > 0)
			{
				$.ajax({
					type: "post",
					url: '<?php echo site_url()?>/holidays/findShiftStudents',
					dataType: 'json',
					data: {
						shift_ids : shift_ids,
					},
					success: function(data) {
						selectedShiftStudentIds = data.student_ids || '';
						syncStudentHiddenField();
					}
				});
			}
		});

		$('.cancel_holiday').click(function(){
			var holiday_id = $(this).data('holiday-id');
			$('.holiday_id').val(holiday_id);
			$('#cancel_holiday').modal('toggle');
		});
	});
	
	</script>
	
	
	<!-- END CONTENT -->