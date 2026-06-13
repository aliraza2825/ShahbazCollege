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
                                <i class="fa fa-user"></i> Study Type
                            </div>
                        </div>



                        <div class="portlet-body table-responsive">

                            <input type="submit" class="btn green" style="margin: 10px" name="student_check" value="Add Study Type" data-toggle="modal" href="#insertloanmodal" />

                            <table class="table table-bordered table-hover" >
                                <thead>
                                <tr>
                                    <th >
                                        Sr
                                    </th>
                                    <th>
                                        Course
                                    </th>
                                    <th>
                                        Name
                                    </th>
                                    <th>
                                        Days
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

                                        <td><?php  echo $loan ['course_name']  ?></td>
                                        <td><?php  echo $loan ['name']  ?></td>

                                        <td> <?php
                                            $days = explode(',',$loan ['days']);
                                            foreach ($days as $day)
                                            {

                                                echo  "<label class='btn blue'  style='width: 100px'>$day</label>";
                                            }

                                            ?></td>

                                        <td><?php  echo $loan ['first_name'].' '.$loan ['last_name']  ?></td>
                                        <td><?php  echo $loan ['created_at']  ?></td>
                                        <td>
                                            <a data-toggle="modal" data-id="<?php echo $i ?>" title="Add this item" class="open-EditLoan btn btn-primary" href="#updateloanmodal">
                                                <i class="fa fa-edit"> Edit</i>
                                            </a>
                                            <?php
                                                if($this->session->userdata('role')=='Admin'):
                                            ?>
                                            <a title="Delete" onclick="return confirm('Are you sure you want to delete this Study Type?')" class="btn red" href="<?php echo site_url();?>/timetable/delete/<?php echo $loan['id'];?>">
                                                <i class="fa fa-trash"> Delete</i>
                                            </a>
                                            <?php
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
        <h4 class="modal-title">Add Study Type</h4>
    </div>

    <div class="modal-body">
        <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/timetable/insert">

            <div class="form-body">

                <div class="form-group">
                    <label class="col-md-3 control-label"> Course <span class="required">*</span></label>
                    <div class="col-md-9">
                        <select name="course_id" class="form-control input-inline input-large" required>
                            <option value="">SELECT COURSE</option>
                            <?php
                                foreach($courses as $course):
                            ?>
                            <option value="<?php echo $course['course_id']?>"><?php echo $course['course_name']?></option>
                            <?php
                                endforeach;
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Name</label>
                    <div class="col-md-9">
                        <input type="text"  name="name" class="form-control mobile" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label"> Days <span class="required">*</span></label>
                    <div class="col-md-9">
                        <select name="days[]" class="form-control input-inline input-large designation_id select2" id="select2_sample1" multiple required>

                            <option value="monday"> <?php echo 'Monday';?></option>
                            <option value="tuesday"> <?php echo 'Tuesday';?></option>
                            <option value="wednesday"> <?php echo 'Wednesday';?></option>
                            <option value="thursday"> <?php echo 'Thursday';?></option>
                            <option value="friday"> <?php echo 'Friday';?></option>
                            <option value="saturday"> <?php echo 'Saturday';?></option>
                            <option value="sunday"> <?php echo 'Sunday';?></option>

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
        <h4 class="modal-title">Update Study Type</h4>
    </div>
    <div class="modal-body">
        <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/timetable/update">
            <div class="form-body">

                <div class="form-group">
                    <label class="col-md-3 control-label"> Course <span class="required">*</span></label>
                    <div class="col-md-9">
                        <select name="course_id" id="course_id" class="form-control input-inline course_id input-large" required>
                            <option value="">SELECT COURSE</option>
                            <?php
                                foreach($courses as $course):
                            ?>
                            <option value="<?php echo $course['course_id']?>"><?php echo $course['course_name']?></option>
                            <?php
                                endforeach;
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Name</label>
                    <div class="col-md-9">
                        <input type="text"  name="name" id="name" class="form-control mobile" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label"> Days <span class="required">*</span></label>
                    <div class="col-md-9">
                        <select name="days[]" class="form-control input-inline input-large days select2" id="select2_sample2" multiple required>

                            <option value="monday"> <?php echo 'Monday';?></option>
                            <option value="tuesday"> <?php echo 'Tuesday';?></option>
                            <option value="wednesday"> <?php echo 'Wednesday';?></option>
                            <option value="thursday"> <?php echo 'Thursday';?></option>
                            <option value="friday"> <?php echo 'Friday';?></option>
                            <option value="saturday"> <?php echo 'Saturday';?></option>
                            <option value="sunday"> <?php echo 'Sunday';?></option>

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
