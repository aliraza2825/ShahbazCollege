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


                                <table class="table table-bordered table-hover" id="sample_3">
                                    <thead>
                                    <tr>
                                        <th class="hidden">
                                            Hidden
                                        </th>
                                        <th>
                                            Employee
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
                                                <td>
                                                    <strong>Name : </strong><?php echo $leave['first_name'].' '.$leave['last_name'];?> <br>
                                                    <strong>CNIC : </strong><?php echo $leave['cnic']?> <br>
                                                    <strong>Contact Details : </strong><?php echo $leave['mobile'];?> <br>
                                                    <strong>Emergency Contact : </strong><?php echo $leave['emergency_no'];?><br />
                                                </td>
                                                <td><?php  echo $leave ['leavetypename']  ?></td>
                                                <td><?php  echo $leave ['fromdate']  ?></td>
                                                <td><?php  echo $leave ['todate']  ?></td>
                                                <td><?php  echo $leave ['leaves_value'] ?></td>
                                                <td> <?php
                                                    if ($leave['status'] == '0')
                                                        echo  "<a data-toggle='modal' data-id='$i' class='open-AddBookDialog btn btn-primary' style='width: 100px' href='#updateleavemodal' >PENDING</a>";
                                                    elseif ($leave['status'] == '2')
                                                        echo  "<a data-toggle='modal' class='btn red'  style='width: 100px'  >REJECTED</a>";
                                                    else
                                                        echo " <a data-toggle='modal' class='btn green' style='width: 100px' >APPROVED</a>";
                                                    ?>
                                                </td>
                                                <td><?php  echo $leave ['created_at']  ?></td>
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

    <div class="modal fade" id="updateleavemodal" tabindex="-1"   data-width="600" >

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title">Approval</h4>
        </div>
        <div class="modal-body">
            <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/leaves/update_employee_leave">
                <div class="form-body">


                    <div class="form-group">
                        <label class="col-md-6 control-label">Employee</label>
                        <div class="col-md-6">

                            <textarea readonly class="form-control" rows="3" id="empinfo" name="empinfo" ></textarea>

                        </div>
                    </div>


                    <div class="form-group">
                        <label class="col-md-6 control-label">Leaves Required</label>
                        <div class="col-md-6">

                            <textarea readonly class="form-control" rows="2" id="no_of_leav" name="no_of_leav" ></textarea>

                        </div>
                    </div>


                    <div class="form-group">
                        <label class="col-md-6 control-label">Description <span class="required">*</span></label>
                        <div class="col-md-6">

                            <textarea readonly class="form-control remarks" rows="3" name="description" id="description" ></textarea>

                        </div>
                    </div>


                    <div class="form-group" >
                        <label class="col-md-6 control-label">Accept or Reject this leave</label>
                        <div class="col-md-6 radio-list" name="radiolist" id="radiolist">
                            <label class="radio-inline">
                                <input type="radio" class="status" name="status"  value="1" >Accept</label>
                            <label class="radio-inline">
                                <input type="radio" class="status" name="status"  value="2">Reject</label>
                        </div>
                    </div>


                </div>

                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-offset-3 col-md-9">

                            <input type="hidden" id="leave_id" name="leave_id" value="" />
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
    <!-- /.modal-dialog -->
<script>
    document.addEventListener( "DOMContentLoaded", function(){
        var leaves = <?php echo json_encode($leaves) ?>;

        $(document).on("click", ".open-AddBookDialog", function () {
            var myBookId = $(this).data('id');
            var info = 'Name : '+leaves[myBookId].first_name+" "+leaves[myBookId].last_name +'\n'+
                       'CNIC : '+leaves[myBookId].cnic +'\n'+
                       'MOBILE : '+leaves[myBookId].mobile+'\n';
            $(".modal-body #empinfo").html(info);
            $(".modal-body #no_of_leav").html("From : "+leaves[myBookId].fromdate+"\nTo : "+leaves[myBookId].todate);
            $(".modal-body #description").html(leaves[myBookId].description);
            $(".modal-body #leave_id").val(leaves[myBookId].id);
        });
    }, false );
</script>