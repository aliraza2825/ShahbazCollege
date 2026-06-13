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
                                <i class="fa fa-user"></i> Shifts
                            </div>
                        </div>



                        <div class="portlet-body table-responsive">

                            <input type="submit" class="btn green" style="margin: 10px" name="student_check" value="Add shift" data-toggle="modal" href="#insertloanmodal" />

                            <table class="table table-bordered table-hover" >
                                <thead>
                                <tr>
                                    <th >
                                        Sr
                                    </th>

                                    <th>
                                        Shift Name
                                    </th>
                                    <th>
                                        Shift Type
                                    </th>
                                    <th>
                                        Start Time
                                    </th>
                                    <th>
                                        End Time
                                    </th>
                                    <th>
                                        Course
                                    </th>
                                    <th>
                                        Campus
                                    </th>
                                    <th>
                                        Created By
                                    </th>
                                    <th>
                                        Created Date
                                    </th>

                                    <th>
                                        Action
                                    </th>


                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $i=0;

                                foreach($studytype as $loan):

                                    ?>
                                    <tr class="odd gradeX">
                                        <td >
                                            <?php echo $i+1;?>
                                        </td>
                                        <td><?php  echo $loan ['name']  ?></td>
                                        <td><?php  echo $loan ['study_type_name']  ?></td>
                                        <td><?php  echo $loan ['start_time']  ?></td>
                                        <td><?php  echo $loan ['end_time']  ?></td>
                                        <td><?php  echo $loan ['course_name']  ?></td>
                                        <td>
                                            <?php  
                                                $campus_ids = explode(',',$loan['shift_campus']);
                                                foreach($campus_ids as $campus_id)
                                                {
                                                    echo $this->db->get_where('campuses',array('campus_id'=>$campus_id))->row()->campus_name.'<br />';
                                                }
                                            ?>
                                        </td>
                                        <td><?php  echo $loan ['first_name'].' '.$loan ['last_name']  ?></td>
                                        <td><?php  echo $loan ['created_at']  ?></td>
                                        <td>
                                            <a data-toggle="modal" data-id="<?php echo $i ?>" title="Add this item" class="open-EditLoan btn btn-primary" href="#updateloanmodal">
                                                <i class="fa fa-edit"> Edit</i>
                                            </a>
                                            <?php
                                                //if($this->session->userdata('role')=='Admin'):
                                            ?>
                                            <a title="Delete" onclick="return confirm('Are you sure you want to delete this Shift?')" class="btn red" href="<?php echo site_url();?>/timetable/delete_shift/<?php echo $loan['id'];?>">
                                                <i class="fa fa-trash"> Delete</i>
                                            </a>
                                            <?php
                                                //endif;
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
                    <!-- END SAMPLE FORM PORTLET-->
                </div>

            </div>

        <!-- Struck of Details-->

    </div>

</div>
<!-- END CONTENT -->

<div class="modal fade" id="insertloanmodal" tabindex="-1"   data-width="600" >


    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Add Shift</h4>
    </div>

    <div class="modal-body">
        <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/timetable/insert_shift">

            <div class="form-body">


                <div class="form-group">
                    <label class="col-md-3 control-label">Name</label>
                    <div class="col-md-9">
                        <input type="text"  name="name" class="form-control mobile" required />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label">Start Time</label>
                    <div class="col-md-9">
                        <input type="time"  name="start_time" class="form-control mobile" required />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label">End Time</label>
                    <div class="col-md-9">
                        <input type="time"  name="end_time" class="form-control mobile" required />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Campus</label>
                    <div class="col-md-9">
                        <select class="form-control" id="select2_sample2" name="campus_ids[]" multiple required>
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
                    <label class="col-md-3 control-label">Courses</label>
                    <div class="col-md-9">
                        <select class="form-control courses" name="course_id" required>
                            <option value="">SELECT COURSE</option>
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
                    <label class="col-md-3 control-label">Study Type</label>
                    <div class="col-md-9">
                        <select class="form-control studytype" name="study_type_id" required>
                            
                        </select>
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

    <div class="modal-footer">
        <button type="button" class="btn default close_button" data-dismiss="modal">Close</button>
    </div>


</div>

<div class="modal fade" id="updateloanmodal" tabindex="-1"   data-width="600" >

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Update Shift</h4>
    </div>
    <div class="modal-body">
        <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/timetable/update_shift">
            <div class="form-body">


                <div class="form-group">
                    <label class="col-md-3 control-label">Name</label>
                    <div class="col-md-9">
                        <input type="text"  name="name" id="name" class="form-control mobile" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label">Start Time</label>
                    <div class="col-md-9">
                        <input type="time"  name="start_time" id="start_time" class="form-control mobile" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label">End Time</label>
                    <div class="col-md-9">
                        <input type="time"  name="end_time" id="end_time" class="form-control mobile" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Campus</label>
                    <div class="col-md-9">
                        <select class="form-control select2 campus" name="campus_ids[]" id="upd_campus_id" multiple required>
                            <option value="">ALL CAMPUS</option>
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
                    <label class="col-md-3 control-label">Courses</label>
                    <div class="col-md-9">
                        <select class="form-control courses" name="course_id" id="upd_course_id" required>
                            <option value="">SELECT COURSE</option>
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
                    <label class="col-md-3 control-label">Study Type</label>
                    <div class="col-md-9">
                        <select class="form-control studytype" name="study_type_id"  id="study_type_id" required>
                            
                        </select>
                    </div>
                </div>


            </div>

            <div class="form-actions">
                <div class="row">
                    <div class="col-md-offset-3 col-md-9">

                        <input type="hidden" id="id" name="id" value="" />
                        <button type="submit" class="btn red">Submit</button>

                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn default close_button" data-dismiss="modal">Close</button>
    </div>

    <!-- /.modal-dialog -->
    
<script>
    window.addEventListener('DOMContentLoaded',function () {
        
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
