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

            <?php
            if(@$myAccess[0]['dashboard_check_student_box']==1 || $this->session->userdata('role')=='Admin'):
                ?>
                <div class="row">

                    <div class="col-md-12 ">
                        <!-- BEGIN SAMPLE FORM PORTLET-->
                        <div class="portlet box green ">

                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-user"></i> Leaves List
                                </div>
                            </div>



                            <div class="portlet-body table-responsive">

                                <input type="submit" class="btn green" style="margin: 10px" name="student_check" value="Apply Leave" data-toggle="modal" href="#insertleavemodal" />

                                <table class="table table-bordered table-hover" id="sample_2" >
                                    <thead>
                                    <tr>
                                        <th class="hidden">
                                            Hidden
                                        </th>
                                        <th>
                                            Leave Type
                                        </th>

                                        <th>
                                            From date
                                        </th>

                                        <th>

                                            To Date

                                        </th>

                                        <th>

                                            No of Leaves

                                        </th>

                                        <th>

                                            Status

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

                                        foreach($leaves as $leave):

                                            ?>
                                            <tr class="odd gradeX">
                                                <td class="hidden">
                                                    <?php echo $i;?>
                                                </td>

                                                <td><?php  echo $leave ['leavetypename']  ?></td>
                                                <td><?php  echo $leave ['fromdate']  ?></td>
                                                <td><?php  echo $leave ['todate']  ?></td>
                                                <td><?php  echo $leave ['leaves_value'] ?></td>
                                                <td> <?php
                                                    if ($leave['status'] == '0')
                                                        echo  "<input class='btn green' value='PENDING' style='width: 100px'/>";

                                                    elseif ($leave['status'] == '2')

                                                        echo  "<input class='btn blue' value='Rejected' style='width: 100px'/>";
                                                    else
                                                        echo " <input class='btn red' value='Approved' style='width: 100px'/>";

                                                    ?></td>
                                                <td><?php  echo $leave ['created_at']  ?></td>
                                                <td>
                                                    <?php if ($leave['status'] == '0'): ?>
                                                    <a data-toggle="modal" data-id="<?php echo $i ?>" title="Add this item" class="open-AddBookDialog btn btn-primary" href="#updateleavemodal">
                                                        <i class="fa fa-edit"> Edit</i>
                                                    </a>

                                                    <?php endif ?>
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

            <?php
            endif;
            ?>

            <!-- Struck of Details-->

		</div>

	</div>
	<!-- END CONTENT -->

    <div class="modal fade" id="insertleavemodal" tabindex="-1"   data-width="600" >


                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">Apply Leave</h4>
                </div>
                <div class="modal-body">
                    <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/leaves/insert_apply_leave">
                        <div class="form-body">



                            <div class="form-group">
                                <label class="col-md-6 control-label">LEAVE TYPE <span class="required">*</span></label>
                                <div class="col-md-6">
                                    <select class="form-control" name="in_leave_id" id="in_leave_id" required>
                                        <option value="0">SELECT LEAVE TYPE</option>
                                        <?php
                                        foreach($leavestype as $leavetype):
                                            ?>
                                            <option value="<?php echo $leavetype['leave_type_id'];?>"><?php echo $leavetype['leavetypename'];?></option>

                                        <?php
                                        endforeach;
                                        ?>
                                    </select>
                                    <!--<span class="help-inline"></span>-->
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-6 control-label">Leaves Remaining</label>
                                <div class="col-md-6">

                                    <textarea readonly class="form-control" rows="1" id="no_of_leav" name="no_of_leav" ></textarea>

                                </div>
                            </div>

                            <div class="form-group" id="half">
                                <label class="col-md-6 control-label">Day Type</label>
                                <div class="col-md-6 radio-list">
                                    <label class="radio-inline">
                                        <input type="radio" class="day_type" name="day_type" id="day_type_full" value="1" checked >Full Day</label>
                                        <label class="radio-inline">
                                        <input type="radio" class="day_type" name="day_type" id="day_type_half" value="0.5">Half Day</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-6">From Date</label>
                                <div class="col-md-6">
                                    <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                        <input type="text" name="from_date" id="selctedfrom" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
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
                                <label class="control-label col-md-6">To Date</label>
                                <div class="col-md-6">
                                    <div class="input-group input-medium date date-picker" id="to_to" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                        <input type="text" name="to_date" id="to_date" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
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
                                <label class="col-md-6 control-label">Description <span class="required">*</span></label>
                                <div class="col-md-6">

                                    <textarea class="form-control remarks" rows="3" name="description" required></textarea>

                                </div>
                            </div>


                        </div>

                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-3 col-md-9">

                                    <input type="hidden" id="leave_assign_id" name="leave_assign_id" value="" />
                                    <button type="submit" class="btn red">Apply Leave</button>

                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                <button type="button" class="btn default close_button" data-dismiss="modal">Close</button>
            </div>


    </div>

    <div class="modal fade" id="updateleavemodal" tabindex="-1"   data-width="600" >

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title">Update Leave</h4>
        </div>
        <div class="modal-body">
            <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/leaves/insert_edit_apply_leave">
                <div class="form-body">



                    <div class="form-group">
                        <label class="col-md-6 control-label">LEAVE TYPE <span class="required">*</span></label>
                        <div class="col-md-6">
                            <select class="form-control" name="upin_leave_id" id="upin_leave_id" required>
                                <option value="0">SELECT LEAVE TYPE</option>
                                <?php
                                foreach($leavestype as $leavetype):
                                    ?>
                                    <option value="<?php echo $leavetype['leave_type_id'];?>"><?php echo $leavetype['leavetypename'];?></option>

                                <?php
                                endforeach;
                                ?>
                            </select>
                            <!--<span class="help-inline"></span>-->
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-6 control-label">Leaves Remaining</label>
                        <div class="col-md-6">

                            <textarea readonly class="form-control" rows="1" id="upno_of_leav" name="upno_of_leav" ></textarea>

                        </div>
                    </div>

                    <div class="form-group" id="uphalf">
                        <label class="col-md-6 control-label">Day Type</label>
                        <div class="col-md-6 radio-list" name="radiolist" id="radiolist">
                            <label class="radio-inline">
                                <input type="radio" class="upday_type" name="upday_type" id="mmfull"  value="1" >Full Day</label>
                            <label class="radio-inline">
                                <input type="radio" class="upday_type" name="upday_type" id="mmhalf"  value="0.5">Half Day</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-6">From Date</label>
                        <div class="col-md-6">
                            <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                <input type="text" name="upfrom_date" id="upselctedfrom" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
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
                        <label class="control-label col-md-6">To Date</label>
                        <div class="col-md-6">
                            <div class="input-group input-medium date date-picker" id="upto_to" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                <input type="text" name="upto_date" id="upto_date" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
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
                        <label class="col-md-6 control-label">Description <span class="required">*</span></label>
                        <div class="col-md-6">

                            <textarea class="form-control remarks" rows="3" name="updescription" id="updescription" required></textarea>

                        </div>
                    </div>


                </div>

                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-offset-3 col-md-9">

                            <input type="hidden" id="upleave_assign_id" name="upleave_assign_id" value="" />
                            <input type="hidden" id="leave_id" name="leave_id" value="" />
                            <button type="submit" class="btn red">Update Leave</button>

                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn default close_button" data-dismiss="modal">Close</button>
    </div>

    <!-- /.modal-dialog -->
