<style>

    
    /* Style the tab */
    .tab {
        overflow: hidden;
        border: 1px solid #ccc;
        background-color: #f1f1f1;
    }

    /* Style the buttons that are used to open the tab content */
    .tablinks {
        background-color: inherit;
        float: left;
        border: none;
        outline: none;
        cursor: pointer;
        padding: 14px 16px;
        transition: 0.3s;
    }

    /* Change background color of buttons on hover */
    .tab button:hover {
        background-color: #ddd;
    }

    /* Create an active/current tablink class */
    .tablinks.active {
        background-color: #26a69a;
    }

    /* Style the tab content */
    .tabcontent {
        display: none;
        padding: 6px 12px;
        border: 1px solid #ccc;
        border-top: none;
    }

</style>
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
								<i class="fa fa-list"></i> Add Exam Sequence
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/councils/insert_exam_sequence" enctype="multipart/form-data">
								<div class="form-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Select Courses <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select class="form-control input-inline input-large select2" id="course_id" name="course_id" required>
                                                        <option value="">SELECT COURSE</option>
                                                    
                                                        <?php foreach($courses as $course): ?>
                                                    
                                                            <option 
                                                                value="<?php echo $course['course_id'];?>"
                                                                data-duration="<?php echo $course['course_duration_year'];?>"
                                                                data-type="<?php echo $course['course_type'];?>"
                                                            >
                                                                <?php echo $course['course_name'];?>
                                                            </option>
                                                    
                                                        <?php endforeach; ?>
                                                    
                                                    </select>
                                                </div>
                                            </div>
                                            <hr />
                                            <div class="form-group">
                                                <label class="col-md-3 control-label"> Select Exam <?php echo $course['course_type'] ?> <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select class="form-control input-inline input-large select2" name="exam_year" id="exam_year" required>
                                                        <option value="">SELECT EXAM</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">First Year Exam Type <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <label class="radio-inline">
                                                    <input type="radio" name="first_year_type" id="optionsRadios4" value="supplementary" checked> Supplementary </label>
                                                    <label class="radio-inline">
                                                    <input type="radio" name="first_year_type" id="optionsRadios5" value="annual"> Annual </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label"> Next Exam No First Year<span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="number" class="form-control input-inline input-large" name="first_year" placeholder="Enter Exam No" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                    </div>
                                </div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                            <button type = "submit" class="btn green">Add Exam Sequence</button>
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
            <div class="row tab">
                <button class="tablinks <?php if($this->uri->segment(3) == 'Active') echo 'active'; ?>" style="margin-left: 10px;" onclick="location.href = '<?php echo site_url().'/councils/council_exam_sequence/Active';?>'">Active</button>
                <button class="tablinks <?php if($this->uri->segment(3) == 'InActive') echo 'active'; ?>" onclick="location.href = '<?php echo site_url().'/councils/council_exam_sequence/InActive';?>'">Inactive</button>
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
								<th>
									 Course Name
								</th>
								<th>
									 Details
								</th>
								<th>
                                    Course Fee Rules
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
                            <tr class="odd gradeX">
                                
                                <td>
									<?php echo $sequence['course_name'];?>
								</td>
                                <td>
									Exam Number : <?php echo  $sequence['first_year'];?>
									<br />
									Exam Type : <?php echo  $sequence['first_year_type'];?>
									<br />
									Class/Semmester : <?php echo  $sequence['class'];?>
								</td>
								<td style="min-width:320px;">

                                    <?php
                                    $this->db->order_by('last_date',"ASC");
                                    $exam_fees = $this->db->get_where(
                                        'council_sequence',
                                        array(
                                            'course_id' => $sequence['course_id'],
                                            'has_fee' => '1'
                                        )
                                    )->result_array();
                                    
                                    foreach($exam_fees as $fee)
                                    {
                                        echo '<div style="border:1px solid #ddd; padding:10px; margin-bottom:10px; background:#f9f9f9;">';
                                    
                                        echo '<div style="font-weight:bold; color:#2c3e50; margin-bottom:6px;">
                                                '.$fee['type_name'].' ( '.$fee['recurring'].' )
                                              </div>';
                                        echo '<button 
                                                class="btn btn-xs btn-success open-fee-modal"
                                                data-fee="'.$fee['council_sequence_id'].'"
                                                data-sequence_exam_fee_id="'.$sequence['id'].'">
                                                Add Rule
                                              </button>';
                                    
                                        $this->db->order_by('from_date',"ASC");
                                        $rules = $this->db->get_where(
                                            'council_sequence_fee_rules',
                                            [
                                                'sequence_fee_id' => $fee['council_sequence_id'],
                                                'exam_sequence_id' => $sequence['id']
                                            ]
                                        )->result_array();
                                    
                                        if(!empty($rules))
                                        {
                                            echo '<div class="nested-table-wrapper">';
                                            echo '<table class="table table-condensed table-bordered inner-table" style="margin-bottom:0;">';
                                            echo '<thead>
                                                    <tr style="background:#eee;">
                                                        <th>From</th>
                                                        <th>To</th>
                                                        <th>Exam Fee</th>
                                                        <th>Expense</th>
                                                        <th>Action</th>
                                                    </tr>
                                                  </thead>';
                                            echo '<tbody>';
                                    
                                            foreach($rules as $rule)
                                            {
                                                echo '<tr>';
                                                echo '<td>'.$rule['from_date'].'</td>';
                                                echo '<td>'.$rule['to_date'].'</td>';
                                                echo '<td>Rs '.$rule['exam_fee'].'</td>';
                                                echo '<td>Rs '.$rule['expense_fee'].'</td>';
                                                echo '<td>
                                                        <button type="button"
                                                            class="btn btn-xs btn-info edit-fee-rule"
                                                            data-id="'.$rule['id'].'"
                                                            data-sequence_fee_id="'.$rule['sequence_fee_id'].'"
                                                            data-sequence_exam_fee_id="'.$rule['exam_sequence_id'].'"
                                                            data-from_date="'.$rule['from_date'].'"
                                                            data-to_date="'.$rule['to_date'].'"
                                                            data-exam_fee="'.$rule['exam_fee'].'"
                                                            data-expense_fee="'.$rule['expense_fee'].'"
                                                            data-has_first_time_fee="'.$rule['has_first_time_fee'].'"
                                                            data-first_time_fee="'.$rule['first_time_fee'].'"
                                                            data-first_time_expense="'.$rule['first_time_expense'].'">
                                                            Edit
                                                        </button>
                                                
                                                        <a href="'.site_url('councils/delete_fee_rule/'.$rule['id']).'"
                                                           class="btn btn-xs btn-danger"
                                                           onclick="return confirm(\'Are you sure you want to delete this rule?\')">
                                                           Delete
                                                        </a>
                                                      </td>';
                                            
                                                echo '</tr>';
                                            }
                                    
                                            echo '</tbody>
                                        </table>';
                                        echo '</div>';
                                        }
                                        else
                                        {
                                            echo '<div style="color:#999;">No fee rules added</div>';
                                        }
                                    
                                        echo '</div>';
                                    }
                                    ?>
                                    
                                </td>
                                <td>
                                    <a href="<?php echo site_url().'/councils/edit_council_exam_sequence/'.$sequence['id']?>" class="btn btn-info">
                                        Edit
                                    </a>
                                    <?php if ($sequence['status'] == 'Active') { ?>
                                        
                                        <a href="<?= site_url().'/councils/save_council_exam/'.$sequence['id'].'/InActive'; ?>" 
                                           class="btn btn-success btn-sm"
                                           onclick="return confirm('Are you sure you want to deactivate this Exam?')">
                                            Active To Inactive
                                        </a>
                                    
                                    <?php } else { ?>
                                    
                                        <a href="<?= site_url().'/councils/save_council_exam/'.$sequence['id'].'/Active'; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to activate this Exam?')">
                                            Inactive To Active
                                        </a>
                                    
                                    <?php } ?>
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
	<div id="feeModal" class="modal fade" tabindex="-1" data-width="760">
        <form method="post" action="<?php echo site_url('councils/save_fee_rule'); ?>">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title" id="feeModalTitle">Add Fee Rule</h4>
            </div>
    
            <div class="modal-body">
    
                <input type="hidden" name="fee_rule_id" id="fee_rule_id">
                <input type="hidden" name="sequence_fee_id" id="sequence_fee_id">
                <input type="hidden" name="sequence_exam_fee_id" id="sequence_exam_fee_id">
    
                <div class="form-group">
                    <label>From Date</label>
                    <input type="date" name="from_date" id="from_date" class="form-control" required>
                </div>
    
                <div class="form-group">
                    <label>To Date</label>
                    <input type="date" name="to_date" id="to_date" class="form-control" required>
                </div>
    
                <div class="form-group">
                    <label>Exam Fee</label>
                    <input type="number" name="exam_fee" id="exam_fee" class="form-control" step="0.01" required>
                </div>
    
                <div class="form-group">
                    <label>Expense Amount</label>
                    <input type="number" name="expense_fee" id="expense_fee" class="form-control" step="0.01" required>
                </div>
    
                <div class="form-group">
                    <label>Has Difference in First Time Fee</label>
                    <select name="has_first_time_fee" id="has_first_time_fee" class="form-control">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>
    
                <div id="firstTimeFeeFields" style="display:none;">
                    <div class="form-group">
                        <label>First Time Fee</label>
                        <input type="number" name="first_time_fee" id="first_time_fee" class="form-control" step="0.01" value="0">
                    </div>
    
                    <div class="form-group">
                        <label>First Time Expense</label>
                        <input type="number" name="first_time_expense" id="first_time_expense" class="form-control" step="0.01" value="0">
                    </div>
                </div>
    
            </div>
    
            <div class="modal-footer">
                <button type="submit" class="btn btn-success" id="feeModalSubmitBtn">Save</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
	<!-- END CONTENT -->
    <script>
        document.addEventListener( "DOMContentLoaded", function(){
                
                $(document).on('click', '.open-fee-modal', function () {

                    $('#feeModalTitle').text('Add Fee Rule');
                    $('#feeModalSubmitBtn').text('Save');
                
                    $('#fee_rule_id').val('');
                    $('#sequence_fee_id').val($(this).data('fee'));
                    $('#sequence_exam_fee_id').val($(this).data('sequence_exam_fee_id'));
                
                    $('#from_date').val('');
                    $('#to_date').val('');
                    $('#exam_fee').val('');
                    $('#expense_fee').val('');
                    $('#has_first_time_fee').val('0');
                    $('#first_time_fee').val('0');
                    $('#first_time_expense').val('0');
                    $('#firstTimeFeeFields').hide();
                
                    $('#feeModal').modal('show');
                });
                
                $(document).on('click', '.edit-fee-rule', function () {
                
                    $('#feeModalTitle').text('Edit Fee Rule');
                    $('#feeModalSubmitBtn').text('Update');
                
                    $('#fee_rule_id').val($(this).data('id'));
                    $('#sequence_fee_id').val($(this).data('sequence_fee_id'));
                    $('#sequence_exam_fee_id').val($(this).data('sequence_exam_fee_id'));
                
                    $('#from_date').val($(this).data('from_date'));
                    $('#to_date').val($(this).data('to_date'));
                    $('#exam_fee').val($(this).data('exam_fee'));
                    $('#expense_fee').val($(this).data('expense_fee'));
                
                    var hasFirstTimeFee = $(this).data('has_first_time_fee');
                
                    $('#has_first_time_fee').val(hasFirstTimeFee);
                
                    if (hasFirstTimeFee == 1 || hasFirstTimeFee == '1') {
                        $('#firstTimeFeeFields').show();
                        $('#first_time_fee').val($(this).data('first_time_fee'));
                        $('#first_time_expense').val($(this).data('first_time_expense'));
                    } else {
                        $('#firstTimeFeeFields').hide();
                        $('#first_time_fee').val('0');
                        $('#first_time_expense').val('0');
                    }
                
                    $('#feeModal').modal('show');
                });
                
                $('#has_first_time_fee').on('change', function () {
                    if ($(this).val() == '1') {
                        $('#firstTimeFeeFields').show();
                    } else {
                        $('#firstTimeFeeFields').hide();
                        $('#first_time_fee').val('0');
                        $('#first_time_expense').val('0');
                    }
                });
                
                $(document).ready(function(){
                    $('.select2').select2();
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
                    $('#sample_2').DataTable({
                        "order": [],
                        "autoWidth": false,
                        "destroy": true,
                        "columnDefs": [
                            { "targets": [2, 3], "orderable": false },
                            { "targets": [3], "searchable": false }
                        ]
                    });
                    $('#has_first_time_fee').on('change', function () {
                        if ($(this).val() == '1') {
                            $('#firstTimeFeeFields').show();
                        } else {
                            $('#firstTimeFeeFields').hide();
                            $('#firstTimeFeeFields').find('input').val(0);
                        }
                    });
            }, false );
        });
    </script>