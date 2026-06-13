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
                                    <i class="fa fa-user"></i> Leaves Types
                                </div>
                            </div>

                            <div class="portlet-body table-responsive">
                                <input type="submit" class="btn green" style="margin: 10px" name="student_check" value="Add Details" data-toggle="modal" href="#insertleavemodal" />

                                <table class="table table-bordered table-hover" >
                                    <thead>
                                    <tr>
                                        <th class="hidden">
                                            Hidden
                                        </th>
                                        <th>
                                            Leave Type
                                        </th>

                                        <th>
                                            Leave Details
                                        </th>

                                        <th>

                                            Allowed per year

                                        </th>

                                        <th>

                                            Half Allowed

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

                                                <td><?php  echo $leave ['leavetype']  ?></td>
                                                <td><?php  echo $leave ['description']  ?></td>
                                                <td><?php  echo $leave ['no_of_leaves']  ?></td>
                                                <td><?php
                                                    if($leave ['is_half_allowed']==0)
                                                    {
                                                        echo 'Not Allowed';
                                                    }
                                                    else
                                                    {
                                                        echo 'Allowed';

                                                    }
                                                    ?></td>
                                                <td><?php  echo $leave ['created_at']  ?></td>
                                                <td>
                                                    <a data-toggle="modal" data-id="<?php echo $i ?>" title="Add this item" class="open-AddBookDialog btn btn-primary" href="#updateleavemodal">
                                                        <i class="fa fa-edit"> Edit</i></a>

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
                    <h4 class="modal-title">Leaves Types details</h4>
                </div>
                <div class="modal-body">
                    <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/leaves/insert_leave">
                        <div class="form-body">

                            <div class="form-group">
                                <label class="col-md-4 control-label">Leave Name</label>
                                <div class="col-md-8">

                                    <textarea class="form-control remarks" rows="1" name="leavetype"></textarea>

                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">Leaves Allowed Per Year</label>
                                <div class="col-md-8">

                                    <textarea class="form-control remarks" rows="1" id="no_of_leaves" name="no_of_leaves"></textarea>

                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">Half Day Allowed</label>
                                <div class="col-md-8 radio-list">
                                    <label class="radio-inline">
                                        <input type="radio" class="is_half_allowed" name="is_half_allowed" id="optionsRadios1" value="1" checked >Allowed</label>
                                        <label class="radio-inline">
                                        <input type="radio" class="is_half_allowed" name="is_half_allowed" id="optionsRadios2" value="0">Not Allowed</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">Description</label>
                                <div class="col-md-8">

                                    <textarea class="form-control remarks" rows="3" name="description"></textarea>
                                </div>
                            </div>


                        </div>

                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-3 col-md-9">
                                    <input type="hidden" name="add_by" value="<?php echo $this->session->userdata('name');?>" />

                                    <button type="submit" class="btn red">Add Leave Type</button>
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
        <h4 class="modal-title">Leaves Types details</h4>
    </div>
    <div class="modal-body">
        <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/leaves/update_leave">
            <div class="form-body">

                <div class="form-group">
                    <label class="col-md-4 control-label">Leave Name</label>
                    <div class="col-md-8">

                        <textarea class="form-control remarks" rows="1" id="leavetype" name="leavetype"></textarea>

                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label">Leaves Allowed Per Year</label>
                    <div class="col-md-8">

                        <textarea class="form-control remarks" rows="1" id="no_of_leaves" name="no_of_leaves"></textarea>

                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label">Half Day Allowed</label>
                    <div class="col-md-8 radio-list">
                        <label class="radio-inline">
                            <input type="radio" class="hallowed" name="hallowed" id="optionsRadios3" value="1" >Allowed</label>

                        <label class="radio-inline">
                            <input type="radio" class="hallowed" name="hallowed" id="optionsRadios4" value="0">Not Allowed</label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label">Description</label>
                    <div class="col-md-8">

                        <textarea class="form-control remarks" rows="3" name="description" id="description"></textarea>
                    </div>
                </div>


            </div>

            <div class="form-actions">
                <div class="row">
                    <div class="col-md-offset-3 col-md-9">
                        <input type="hidden" name="add_by" value="<?php echo $this->session->userdata('name');?>" />
                        <input type="hidden" name="leave_id" id="leave_id" value="" />
                        <input type="hidden" name="radiot" id="radiot" value="" />

                        <button type="submit" class="btn red">Update Leave Type</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn default close_button" data-dismiss="modal">Close</button>
    </div>




</div>

    <!-- /.modal-dialog -->
