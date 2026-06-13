<?php
	$myAccess = checkUserAccess();
?>	
    <!-- BEGIN CONTENT -->
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

            <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								Shift : <?php echo $lecture[0]['shift_name'];?>
								<br />
								Study Type : <?php echo $lecture[0]['study_type_name'];?>
								<br />
								Campus : <?php echo $lecture[0]['campus_name'];?>
							</div>
						</div>

						<div class="portlet-body table-responsive">
                            <table class="table table-striped table-bordered table-hover">
							<thead>
							<tr>
                                <th class="hidden">
									Sr
								</th>
                                <th>
                                    Selection
                                </th>
                                <th>
									 Student Picture
								</th>
								<th>
									 Roll #
								</th>
								<th>
									 Name
								</th>
                                <th>
									 Mobile
								</th>
								<th>
								    Attendance Added By Info
								</th>
								<th>Info</th>
                                <th>History</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=0;
								foreach($students as $student):
							?>
                            <tr class="odd gradeX">
                                <td class="hidden">
									 <?php echo $i;
									 $this->db->select('*');
											$this->db->from('freeze_student');
											$this->db->where("(freeze_student.student_id = '".$student['student_id']."')", NULL, FALSE);
											$freezedata = $this->db->get()->result_array();
									 ?>
								</td>
                                <td>
                                    <?php $check = $this->db->get_where('lecture_wise_attendance',array('student_id'=>$student['student_id'],'lecture_id'=>$this->uri->segment(3),'date'=>date('Y-m-d')))->result_array();?>
                                    <input type="checkbox" class="selection" name="selection" value="<?php echo $student['student_id'];?>" data-lecture-id="<?php echo $this->uri->segment(3);?>" <?php if(count($check)>0){echo 'checked';}?> />
                                </td>
                                <td>
                                    <?php
                                        $photo = $this->db->get_where('student_documents',array('student_id'=>$student['student_id'],'type'=>'Photo'))->result_array();
                                        if(@$photo[0]['online_image']=='' && count(@$photo)>0)
                                        {
                                            echo '<img src="'.base_url('uploads/'.$photo[0]['image']).'" height="100" />';
                                        }
                                        elseif(@$photo[0]['online_image']!='' && count(@$photo)>0)
                                        {
                                            echo '<img src="'.$photo[0]['online_image'].'" height="100" />';
                                        }
                                        else
                                        {
                                            
                                        }
                                    ?>
                                </td>
								<td>
									 <?php echo $student['roll_no'];?>
								</td>
								<td>
									<?php echo $student['first_name'].' '.$student['last_name'];?>
								</td>
								<td>
									 <?php echo $student['mobile'];?> - <?php echo $student['emergency_no'];?>
								</td>
								<td>
									 <?php 
									 if(count($check)>0){
									    echo 'Added By : '.$check[0]['add_by'].'<br>'.'Updated at : '.$check[0]['updated_at'];
									 } 
									 ?>
								</td>
								
								<?php
                                $absent_infos = $this->db
                                    ->where('student_id', $student['student_id'])
                                    ->where('lecture_id', $this->uri->segment(3))
                                    ->order_by('id', 'DESC')
                                    ->get('lecture_absent_student_logs')
                                    ->result_array();
                                ?>
                                
                                <td>
                                    <?php if(count($check) == 0): ?>
                                        <button 
                                            type="button"
                                            class="btn btn-xs blue add-info-btn"
                                            data-student-id="<?php echo $student['student_id']; ?>"
                                            data-lecture-id="<?php echo $this->uri->segment(3); ?>">
                                            Add Info
                                        </button>
                                    <?php else: ?>
                                        <span class="label label-success">Present</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td>
                                    <?php if(count($absent_infos) > 0): ?>
                                        <?php foreach($absent_infos as $info): ?>
                                            <div style="border-bottom:1px solid #ddd; padding:5px 0;">
                                                <strong><?php echo $info['add_by']; ?></strong>
                                                <small>
                                                    <?php echo date('d M Y h:i A', strtotime($info['created_at'])); ?>
                                                </small>
                                                <br>
                                                <?php echo nl2br($info['info']); ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">No history</span>
                                    <?php endif; ?>
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
	
    <div id="addInfoModal" class="modal fade" style="background-color:transparent;" tabindex="-1" data-width="600">
        <div class="modal-dialog" role="document">
            <form id="addInfoForm">
                <div class="modal-content">
    
                    <div class="modal-header">
                        <h4 class="modal-title">Add Student Absent Info</h4>
                    </div>
    
                    <div class="modal-body">
                        <input type="hidden" name="student_id" id="info_student_id">
                        <input type="hidden" name="lecture_id" id="info_lecture_id">
    
                        <div class="form-group">
                            <label>Info</label>
                            <textarea name="info" id="info_text" class="form-control" rows="5" required></textarea>
                        </div>
                    </div>
    
                    <div class="modal-footer">
                        <button type="button" class="btn default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn green">Save Info</button>
                    </div>
    
                </div>
            </form>
        </div>
    </div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        
        jQuery('.add-info-btn').click(function(){

        jQuery('#info_student_id').val(jQuery(this).data('student-id'));

        jQuery('#info_lecture_id').val(jQuery(this).data('lecture-id'));

        jQuery('#info_text').val('');

        jQuery('#addInfoModal').modal('show');

    });

    jQuery('#addInfoForm').submit(function(e){

        e.preventDefault();

        jQuery.ajax({

            type: "post",

            url: "<?php echo site_url(); ?>/schedule/save_absent_student_info",

            data: jQuery(this).serialize(),

            success: function(response){

                jQuery('#addInfoModal').modal('hide');

                location.reload();

            }

        });

    });
        
        
        jQuery('.selection').change(function(){
            var student_id = jQuery(this).val();
            var lecture_id = jQuery(this).data('lecture-id');
            if(jQuery(this).prop('checked') == true)
            {
                jQuery.ajax({
                    type: "post",
                    async: false,
                    url: '<?php echo site_url()?>/timetable/mark_student_lecture_attendance',
                    data: {
                        student_id : student_id,
                        lecture_id : lecture_id
                    },
                    success: function(data) {
                        
                    }

                });
            }
            else
            {
                jQuery.ajax({
                    type: "post",
                    async: false,
                    url: '<?php echo site_url()?>/timetable/unmark_student_lecture_attendance',
                    data: {
                        student_id : student_id,
                        lecture_id : lecture_id
                    },
                    success: function(data) {
                        
                    }

                });
            }
        });
    });
</script>