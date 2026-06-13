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
            <input type="submit" class="btn green col-md-4" style="margin: 10px; width: 150px;" name="student_check" value="Add Closing Person" data-toggle="modal" href="#insertloanmodal" />

            <?php
            if(@$myAccess[0]['dashboard_check_student_box']==1 || $this->session->userdata('role')=='Admin'):
                ?>
                <div class="row">

                    <div class="col-md-12 ">
                        <!-- BEGIN SAMPLE FORM PORTLET-->


                        <div class="portlet box green ">

                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-user"></i> Petty Cash List
                                </div>
                            </div>


                            <div class="portlet-body table-responsive">

                                <table class="table table-bordered table-hover" id="sample_2">
                                    <thead>
                                    <tr>
                                        <th class="hidden">
                                            Hidden
                                        </th>

                                        <th>
                                            Campus Name
                                        </th>
										<th>
                                            Recovery Person
                                        </th>
                                        <th>
                                            Last Updated
                                        </th>
										<th>
                                            Status
                                        </th>
										<th>
                                            Remaining Closing Amount
                                        </th>
                                        <th>
                                            Action
                                        </th>


                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $i=0;

                                        foreach($Persons as $person):

                                            ?>
                                            <tr class="odd gradeX">
                                                <td class="hidden">
                                                    <?php echo $i;?>
                                                </td>

                                                <td>
                                                    <?php  echo $person ['campus_name']  ?>
                                                </td>
												<td>
                                                    <?php  

														echo $person['first_name'].' '.$person['last_name'];
													?>
                                                </td>

                                                <td><?php  echo $person ['created_at'] ?></td>
                                                <td><?php  if($person ['active_status'] == 1) { echo 'Active'; } else { echo 'In-Active'; }?></td>
                                                <td><?php if($person ['active_status'] == 1) {
															$this->db->select('sum(amount+fine_amount) as amount');
												$amount_count=$this->db->get_where('payments',array('submitted_fee_campus_id' => $person['campus_id'],'paid' => '1','fee_pay_through' => 'college','closing_id' => NULL))->row();
												echo $amount_count->amount;
												}?></td>
												<td>

                                                    <a data-toggle="modal" data-id="<?php echo $i ?>" title="Add this item" class="open-EditLoan btn btn-primary" href="#editloanmodal">
                                                        <i class="fa fa-edit"> Edit </i>
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

    <div class="modal fade" id="insertloanmodal" tabindex="-1"   data-width="600" >


                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">+ Campus Closing Account</h4>
                </div>

                <div class="modal-body">
                    <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/closing/add_closing_person">

                        <div class="form-body">

                            <div class="form-group">
                                <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                <div class="col-md-9">
                                    <select name="campus_id" id="campus_id" class="form-control input-inline input-large campus_id" required>
                                        <option value="">SELECT CAMPUS</option>
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
                                <label class="col-md-3 control-label">Department <span class="required">*</span></label>
                                <div class="col-md-9">
                                    <select name="department_id" class="form-control input-inline input-large department_id" required>

                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">Designation <span class="required">*</span></label>
                                <div class="col-md-9">
                                    <select name="designation_id" id="designation_id" class="form-control input-inline input-large designation_id" required>

                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">User <span class="required">*</span></label>
                                <div class="col-md-9">
                                    <select name="user_id" class="form-control input-inline input-large user_id" required>

                                    </select>
                                </div>
                            </div>



                        </div>

                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-3 col-md-9">

                                    <button type="submit" class="btn red">Add Closing Person</button>

                                </div>
                            </div>
                        </div>

                    </form>
                </div>

                <div class="modal-footer">
                <button type="button" class="btn default close_button" data-dismiss="modal">Close</button>
            </div>


    </div>


    <div class="modal fade" id="editloanmodal" tabindex="-1"   data-width="600" >


    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Change Closing Person </h4>
    </div>

    <div class="modal-body">
        <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/closing/edit_closing_person">

            <div class="form-body">


                <div class="form-group">
                    <label class="col-md-3 control-label">User Detail <span class="required">*</span></label>
                    <div class="col-md-9">
                       <input type="hidden" name="closing_id" id="eduser_id">
                       <input type="text" id="user_detail" value="" readonly>
                    </div>
                </div>


                <div class="form-group">
                    <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                    <div class="col-md-9">
                        <select name="campus_id" id="edcampus_id" class="form-control input-inline input-large" required>
                            <option value="">SELECT CAMPUS</option>
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
                    <label class="col-md-3 control-label">Status <span class="required">*</span></label>
                    <div class="col-md-9">
                        <select name="active_status" id="active_status" class="form-control input-inline input-large" required>
							
                            <option value="">Select Status</option>
                            <option value="1">Active</option>
                            <option value="0">In-Active</option>
                            
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


<!-- /.modal-dialog -->
