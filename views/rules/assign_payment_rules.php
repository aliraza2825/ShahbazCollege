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
                                    <i class="fa fa-user"></i> Payment Rules List
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
                                            Name
                                        </th>
                                        <th>
                                            Course
                                        </th>
                                        <th>
                                            Amount
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

                                        foreach($payment_rules as $leave):

                                            ?>
                                            <tr class="odd gradeX">
                                                <td class="hidden">
                                                    <?php echo $i;?>
                                                </td>

                                                <td><?php  echo $leave ['name']  ?></td>
                                                <td><?php  echo $leave ['course_name']  ?></td>
                                                <td><?php  echo $leave ['amount']  ?></td>
                                                <td> <?php
                                                    if ($leave['status'] == 'active')
                                                        echo  "<input class='btn blue' value='Active' style='width: 100px'/>";
                                                    else
                                                        echo " <input class='btn red' value='Inactive' style='width: 100px'/>";
                                                    ?></td>
                                                <td><?php  echo $leave ['created_by']  ?></td>
                                                <td>
                                                    <a data-toggle="modal" data-id="<?php echo $i ?>" title="Add this item" class="open-AddBookDialog btn btn-primary" href="#updateleavemodal">
                                                        <i class="fa fa-edit"> Edit</i>
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
            <h4 class="modal-title">Insert Payment Rule</h4>
        </div>
        <div class="modal-body">
            <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/rules/insert_payment_rule">
                <div class="form-body">
                    <div class="form-group">
                        <label class="col-md-6 control-label">Course <span class="required">*</span></label>
                        <div class="col-md-6">
                            <select class="form-control" name="course_id" id="course_id" required>
                                <option value="0">SELECT Course</option>
                                <?php
                                foreach($courses as $course):
                                    ?>
                                    <option value="<?php echo $course['course_id'];?>"><?php echo $course['course_name'];?></option>
                                <?php
                                endforeach;
                                ?>
                            </select>
                            <!--<span class="help-inline"></span>-->
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-6 control-label">Name</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="name" required/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-6 control-label">Payment Amount</label>
                        <div class="col-md-6">
                            <input type="number" class="form-control" name="amount" required/>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-offset-3 col-md-9">
                            <input type="hidden"  name="created_by" value="<?php echo $this->session->userdata('name');?>" />
                            <button type="submit" class="btn red">Save</button>
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
            <h4 class="modal-title">Update Payment Rule</h4>
        </div>
        <div class="modal-body">
            <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/rules/update_payment_rule">
                <div class="form-body">
                    <div class="form-group">
                        <label class="col-md-6 control-label">Course <span class="required">*</span></label>
                        <div class="col-md-6">
                            <select class="form-control" name="course_id" id="in_course_id" required>
                                <option value="0">SELECT Course</option>
                                <?php
                                foreach($courses as $course):
                                    ?>
                                    <option value="<?php echo $course['course_id'];?>"><?php echo $course['course_name'];?></option>
                                <?php
                                endforeach;
                                ?>
                            </select>
                            <!--<span class="help-inline"></span>-->
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-6 control-label">Name</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="payment_name" name="name" required/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-6 control-label">Payment Amount</label>
                        <div class="col-md-6">
                            <input type="number" class="form-control" id="payment_amount" name="amount" required/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-6 control-label">Status <span class="required">*</span></label>
                        <div class="col-md-6">
                            <select class="form-control" name="status" id="in_payment_status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <!--<span class="help-inline"></span>-->
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-offset-3 col-md-9">
                            <input type="hidden"  name="created_by" value="<?php echo $this->session->userdata('name');?>" />
                            <input type="hidden"  name="rule_id" value="" id="payment_rule_id" />
                            <button type="submit" class="btn red">Update</button>
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
    document.addEventListener( "DOMContentLoaded", function()
    {
        $(document).on("click", ".open-AddBookDialog", function () {
            var index = $(this).data('id');
            var loans = <?php echo json_encode($payment_rules) ?>;


            $(".modal-body #in_course_id").val( loans[index].course_id);
            $(".modal-body #payment_name").val( loans[index].name);
            $(".modal-body #payment_amount").val( loans[index].amount);
            $(".modal-body #in_payment_status").val( loans[index].status);
            $(".modal-body #payment_rule_id").val( loans[index].id);
        });

    }, false );
</script>
