<?php
$myAccess = checkUserAccess();

function hue2rgb($t)
{
    if ($t < 0) $t += 1;
    if ($t > 1) $t -= 1;
    if ($t < 1/6) return 6 * $t;
    if ($t < 1/2) return 1;
    if ($t < 2/3) return (2/3 - $t) * 6;
    return 0;
}
function color_hex($hue)
{
    return sprintf('#%02x%02x%02x',
        round(255 * hue2rgb($hue + 1/3)),
        round(255 * hue2rgb($hue)),
        round(255 * hue2rgb($hue - 1/3))
    );
}
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
                            <i class="fa fa-plus"></i> Filter Campus Wise
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/timetable/view_timetable">
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                    <div class="col-md-5">
                                        <select class="form-control" name="campus_id_search">
                                            <option value="all">ALL CAMPUSES</option>
                                            <?php
                                            foreach($campuses as $campus):
                                                ?>
                                                <option value="<?php echo $campus['campus_id'];?>" <?php if($campus['campus_id']==$this->input->post('campus_id_search')){echo 'selected';}?>><?php echo $campus['campus_name'];?></option>
                                            <?php
                                            endforeach;
                                            ?>
                                        </select>
                                        <!--<span class="help-inline"></span>-->
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <input name="form_submit" type="hidden" value="1" />
                                        <button type="submit" class="btn green">Check</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- END SAMPLE FORM PORTLET-->
            </div>
        </div>

        <label style="font-size: x-large; font-weight: bolder">SELECT FILTER FROM HERE</label>
        <div class="row" style="margin-bottom:15px; padding:15px; margin-left: 0px; margin-right: 0px; border: 1px solid black">

            <?php foreach ($groups as $i=>$group): ?>
                <div class="col-md-5" style="margin: 2px;">
                    <form method="post" action="<?php echo site_url();?>/timetable/view_timetable/<?php echo $group['shift'] ?>/<?php echo $group['studytype'] ?>/<?php echo $group['campus'] ?>">
                        <?php $shifs = $this->db->get_where('shifts','id = "'.$group ['shift'].'"')->result_array();
                        $study_type = $this->db->get_where('study_type','id = "'.$group ['studytype'].'"')->result_array();;?>
                        <input type="submit" style="width: 100%; font-size: 13px; font-weight: bolder; background: #00aaff" class="btn yellow" value="<?php echo $shifs[0]['name'].' - '.$study_type[0]['name'].' - '.$group ['course_name'].' - '.$group ['campus_name'] ?>" />
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="row">
            <?php if(@$this->uri->segment(3)):
                $shifs = $this->db->get_where('shifts','id = "'.$this->uri->segment(3).'"')->result_array();
                $study_type = $this->db->get_where('study_type','id = "'.$this->uri->segment(4).'"')->result_array();
                ?>
                <a href="<?php echo site_url();?>/timetable/session_wise_students/<?php echo $shifs[0]['name'];?>/<?php echo $study_type[0]['name'];?>/<?php echo $this->uri->segment(5);?>" class="btn blue" ><i class="fa fa-eye"></i>View All Students</a>
                <br />
            <?php endif; ?>
            <div class="col-md-12 ">
                <!-- BEGIN SAMPLE FORM PORTLET-->
                <div class="portlet box green ">

                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-user"></i> Time Table
                        </div>
                    </div>



                    <div class="portlet-body table-responsive">

                        <table class="table table-bordered table-hover" id="sample_2">
                            <thead>
                            <tr>
                                <th >
                                    ID
                                </th>
                                <th>
                                    Lecture Name
                                </th>
                                <th>
                                    Campus Name
                                </th>

                                <th>
                                    Course Name
                                </th>
                                <th>
                                    Class Name
                                </th>
                                <th>
                                    Sessions
                                </th>
                                <th>
                                    Subjects
                                </th>
                                <th>
                                    Shift
                                </th>
                                <th>
                                    Study Type
                                </th>
                                <th>
                                    Days
                                </th>
                                <th>
                                    Room
                                </th>
                                <th>
                                    Teacher
                                </th>
                                <th>
                                    Time
                                </th>
                                <th>
                                    Zoom ID
                                </th>
                                <th>
                                    Action
                                </th>


                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $i=0;

                            foreach($lectures as $loan):

                                ?>
                                <tr class="odd gradeX">
                                    <td >
                                        <?php echo $loan ['id'];?>
                                    </td>


                                    <td><?php  echo $loan ['lecture_name']  ?></td>

                                    <td><?php  echo $loan ['campus_name']  ?></td>
                                    <td><?php  echo $loan ['course_name']  ?></td>
                                    <td><?php  echo $loan ['class']   ?> Year</td>
                                    <td><?php  echo $loan ['session']  ?></td>
                                    <td><?php

                                        $subjects = $this->db->where_in('course_subject_id',explode(',',$loan ['subjects']))->get('course_subjects')->result_array();

                                        foreach ($subjects as $subj) {
                                            echo $subj['subject_name'].' ';
                                        }

                                        ?></td>
                                    <td><?php

                                        $shifs = $this->db->get_where('shifts','id = "'.$loan ['shift'].'"')->result_array();

                                        echo $shifs[0]['name']  ?>
                                    </td>

                                    <td><?php
                                        $study_type = $this->db->get_where('study_type','id = "'.$loan ['studytype'].'"')->result_array();

                                        echo $study_type[0]['name']  ;
                                        ?>
                                    </td>
                                    <td>
                                        <?php   $days = explode(',',$loan ['days']);
                                        foreach ($days as $day)
                                        {

                                            echo  "<label class='btn blue'  style='width: 100px'>$day</label>";
                                        }  ?>
                                    </td>
                                    <td><?php  echo $loan ['room_name']  ?></td>
                                    <td><?php  echo $loan ['first_name']. ' - ' .$loan ['last_name']  ?></td>
                                    <td><?php  echo $loan ['start_date']. ' - ' .$loan ['end_date']  ?></td>
                                    <td><?php  echo $loan ['zoom_id'];  ?></td>
                                    <td>
                                        <?php
                                        $arr = explode(',',$loan ['subjects']);
                                        $merge = false;
                                        foreach($arr as $subs)
                                        {

                                            $sbd = $this->db->where('lecture_id = "'.$loan['id'].'" and subject_id = "'.$subs.'"')->get('session_syllabus')->result_array();
                                            if(count($sbd) > 0):
                                                $merge = true;
                                                ?>
                                                <div>
                                                <a href="<?php echo site_url().'/schedule/view_session_syllabus/'.$subs.'/'.$loan['id'];?>" title="View" ><i class="fa fa-eye"></i></a>
                                            <?php
                                            endif;
                                            ?>
                                            <a data-toggle="modal" data-id="<?php echo $i ?>" data-lecture-id="<?php echo $loan['id'];?>" data-subject="<?php echo $subs ?>" title="Add this item" class="Open-CreateSyllabus btn btn-primary" href="#create_syllabus">
                                                <i class="fa fa-book"> Generate Syllabus <?php echo $this->db->where('course_subject_id',$subs)->get('course_subjects')->row()->subject_name ?></i>
                                            </a>
                                            </div>
                                            <?php

                                        }
                                        ?>

                                        <br />
                                        <?php if ($merge): ?>
                                            <a href="<?php echo site_url();?>/schedule/view_merged_session_syllabus/<?php echo $loan['id'];?>" class="btn blue" ><i class="fa fa-eye"></i> Merged</a>
                                        <?php endif; ?>

                                        <br />
                                        <a data-toggle="modal" data-id="<?php echo $i ?>" title="Add this item" class="Open-assign_zoom btn btn-success" href="#assign_zoom">
                                            <i class="fa fa-table"> Assign Zoom</i>
                                        </a>
                                        <br />
                                        <a href="<?php echo site_url();?>/timetable/today_lecture/<?php echo $loan['id'];?>" class="btn purple" ><i class="fa fa-table"></i> Today's Lecture</a>
                                        <br />
                                        <a href="<?php echo site_url();?>/timetable/all_lectures/<?php echo $loan['id'];?>" class="btn grey-cascade" ><i class="fa fa-table"></i> All Lecture</a>
                                        <br />
                                        <a href="<?php echo site_url();?>/timetable/session_wise_students/<?php echo $loan['id'];?>" class="btn green">Add Students Attendance</a>
                                        <br />
                                        <a href="<?php echo site_url();?>/timetable/edit_timetable/<?php echo $loan['id'];?>" class="btn yellow" ><i class="fa fa-edit"></i> Edit</a>
                                        <a onclick="return confirm('Are you sure you want to delete this Lecture?')" href="<?php echo site_url();?>/timetable/delete_table/<?php echo $loan['id'];?>" class="btn red" ><i class="fa fa-trash"></i> Delete</a>
                                        <?php if ($loan['zoom_id']): ?>
                                        <br />
                                        <a data-toggle="modal" data-id="<?php echo $i ?>"  class="Open-assign_zoom btn btn-success" href="https://zoom.us/wc/<?php echo $loan ['zoom_id']; ?>/join">
                                            <i class="fa fa-fire"> Join Meeting</i>
                                        </a>
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
                <!-- END SAMPLE FORM PORTLET-->
            </div>
        </div>
        <!-- Struck of Details-->
    </div>

</div>
<!-- END CONTENT -->
<div class="modal fade" id="create_syllabus" tabindex="-1"   data-width="600" >

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Generate Syllabus</h4>
    </div>
    <div class="modal-body">
        <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/schedule/generate_table">
            <div class="form-body">
                <br />
                <div class="input-group"  >
                    <label> Syllabus Type</label>
                    <div id="sylls">

                    </div>
                </div>
                <br />
                <label> Start Date</label>
                <div class="input-group input-small"  data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                    <input type="date" name="start_date" style="width : 220px;" class="form-control start_date" value="" min="" required>
                </div>
                <br />
                <div class="input-group"  >
                    <label> Test After Lectures</label>
                    <input type="number" name="test_after" class="form-control" value="" required>

                </div>
                <br />
            </div>
            <div class="form-actions">
                <div class="row">
                    <div class="col-md-offset-3 col-md-9">
                        <input type="hidden" name="subject" id="subject" value="">
                        <input type="hidden" name="study_type" id="study_type" value="">
                        <input type="hidden" name="lecture_id" id="lecture_id" value="">
                        <button type="submit" class="btn green">Submit</button>
                    </div>
                </div>
            </div>
        </form>

    </div>
    <div class="modal-footer">
        <button type="button" class="btn default close_button" data-dismiss="modal">Close</button>
    </div>



</div>


<div class="modal fade" id="assign_zoom" tabindex="-1"   data-width="600" >
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Generate Syllabus</h4>
    </div>
    <div class="modal-body">
        <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/schedule/assign_zoom">
            <div class="form-body">
                <div class="form-group">
                    <label class="col-md-3 control-label">Zoom ID <span class="required">*</span></label>
                    <div class="col-md-9">
                        <input type="text" name="zoom_id" id="zoom_id" style="width : 220px;" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Zoom Password <span class="required">*</span></label>
                    <div class="col-md-9">
                        <input type="text" name="zoom_password" id="zoom_password" class="form-control input-medium" value="" required>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <div class="row">
                    <div class="col-md-offset-3 col-md-9">
                        <input type="hidden" name="lecture_id" id="zoom_lecture_id" value="">
                        <button type="submit" class="btn green">Submit</button>
                    </div>
                </div>
            </div>
        </form>

    </div>
    <div class="modal-footer">
        <button type="button" class="btn default close_button" data-dismiss="modal">Close</button>
    </div>
</div>



<script>

    window.addEventListener('DOMContentLoaded',function () {

        var plans = <?php echo json_encode($lectures) ?>;
        $(document).on("click", ".Open-CreateSyllabus", function () {

            var myBookId = $(this).data('id');
            var subject = $(this).data('subject');
            var lecture_id = $(this).data('lecture-id');

            $(".modal-body #lecture_id").val(plans[myBookId].id);
            $(".modal-body #study_type").val(plans[myBookId].studytype);
            $(".modal-body #subject").val(subject);

            getSyllabuses(subject,plans[myBookId].studytype);
            getLastDate(subject,lecture_id)

        });

        $(document).on("click", ".Open-assign_zoom", function () {
            var myBookId = $(this).data('id');

            $(".modal-body #zoom_lecture_id").val(plans[myBookId].id);
            $(".modal-body #zoom_id").val(plans[myBookId].zoom_id);
            $(".modal-body #zoom_password").val(plans[myBookId].zoom_password);
        });


        function getSyllabuses(subject_id,study_type)
        {

            jQuery.ajax({
                type: "post",
                async: false,
                url: '<?php echo site_url()?>/schedule/Subject_Syllabuses',
                data: {
                    subject_id : subject_id,
                    study_type : study_type
                },
                success: function(data) {
                    jQuery('.modal-body #sylls').html(data);
                }
            });

        }

        function getLastDate(subject_id,lecture_id)
        {

            jQuery.ajax({
                type: "post",
                async: false,
                url: '<?php echo site_url()?>/schedule/getLastDate',
                data: {
                    subject_id : subject_id,
                    lecture_id : lecture_id
                },
                success: function(data) {
                    jQuery('.modal-body .start_date').val(data);
                    jQuery('.modal-body .start_date').attr('min',data);
                }
            });

        }

    });



</script>