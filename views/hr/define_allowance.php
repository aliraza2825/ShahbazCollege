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
                                    <i class="fa fa-user"></i> Allownces Types
                                </div>
                            </div>

                            <div class="portlet-body table-responsive">
                                <input type="submit" class="btn green" style="margin: 10px" name="student_check" value="Add Allowance" data-toggle="modal" href="#insertleavemodal" />

                                <table class="table table-bordered table-hover" >
                                    <thead>
                                    <tr>
                                        <th class="hidden">
                                            Hidden
                                        </th>
                                        <th>
                                            Allowance Name
                                        </th>

                                        <th>
                                            Allowance Type
                                        </th>

                                        <th>
                                            Percent
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

                                        foreach($allownces as $allowance):

                                            ?>
                                            <tr class="odd gradeX">
                                                <td class="hidden">
                                                    <?php echo $i;?>
                                                </td>

                                                <td><?php  echo $allowance ['name']  ?></td>
                                                <td><?php
                                                    if($allowance ['type']==0)
                                                    {
                                                        echo 'Earning';
                                                    }
                                                    else
                                                    {
                                                        echo 'Deduction';

                                                    }
                                                    ?></td>
                                                <td><?php
                                                    echo $allowance ['percent']. " %"
                                                    ?></td>
                                                <td><?php  echo $allowance ['created_at']  ?></td>
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
                    <h4 class="modal-title">Allowances Types details</h4>
                </div>
                <div class="modal-body">
                    <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/Allownces/insert_allowance">
                        <div class="form-body">

                            <div class="form-group">
                                <label class="col-md-4 control-label">Allowance Name</label>
                                <div class="col-md-8">

                                    <textarea class="form-control remarks" rows="1" name="allownce"></textarea>

                                </div>
                            </div>


                            <div class="form-group">
                                <label class="col-md-4 control-label">Allownce Type</label>
                                <div class="col-md-8 radio-list">
                                    <label class="radio-inline">
                                        <input type="radio" class="is_half_allowed" name="type" id="type" value="1" checked >Deduction</label>
                                        <label class="radio-inline">
                                        <input type="radio" class="is_half_allowed" name="type" id="type2" value="0">Earning</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-6 control-label">Percentage %</label>
                                <div class="col-md-6">
                                    <input type="number"  name="perc" id="perc"  class="form-control mobile" value="" />
                                </div>
                            </div>

                        </div>

                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-3 col-md-9">
                                    <button type="submit" class="btn red">Add Allowance Type</button>
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
        <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/Allownces/update_allowance">
            <div class="form-body">

                <div class="form-group">
                    <label class="col-md-4 control-label">Allowance Name</label>
                    <div class="col-md-8">

                        <textarea class="form-control remarks" rows="1" name="allownce" id="allownce"></textarea>

                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label">Allownce Type</label>
                    <div class="col-md-8 radio-list">
                        <label class="radio-inline">
                            <input type="radio" class="is_half_allowed" name="type" id="type" value="1" checked >Deduction</label>
                        <label class="radio-inline">
                            <input type="radio" class="is_half_allowed" name="type" id="type2" value="0">Earning</label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-6 control-label">Percentage %</label>
                    <div class="col-md-6">
                        <input type="number"  name="perc" id="perc"  class="form-control mobile" value="" />
                    </div>
                </div>


            </div>

            <div class="form-actions">
                <div class="row">
                    <div class="col-md-offset-3 col-md-9">

                        <input type="hidden" name="allid" id="allid" value="" />


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
