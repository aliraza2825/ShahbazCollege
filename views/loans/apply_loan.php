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
                                    <i class="fa fa-user"></i> Advance/Loan List
                                </div>
                            </div>

                            <div class="portlet-body table-responsive">
                                <?php
                                $current_user = $this->db->get_where("users","user_id = '".$this->session->userdata("user_id")."'")->row();
                                $current_date = date("Y-m-d");
                                $joining_date = $current_user->joining_date;
                                $d1=new DateTime($joining_date);
                                $d2=new DateTime($current_date);
                                $Months = $d2->diff($d1);
                                $howeverManyMonths = (($Months->y) * 12) + ($Months->m);
                                if (@$myAccess[0]['apply_loan'] == 1 || $this->session->userdata('role') == 'Admin' || $howeverManyMonths > @$loansetting[0]['avail_after_join']):
                                ?>
                                <input type="submit" class="btn green" style="margin: 10px" name="student_check" value="Apply Loan" data-toggle="modal" href="#insertloanmodal" />
                                <?php else:
                                    $remainingMonths = @$loansetting[0]['avail_after_join'] - $howeverManyMonths;
                                    ?>

                                <h3>You can apply Loan after <?php echo $remainingMonths; ?> Months</h3>
                                <?php endif; ?>
                                <table class="table table-bordered table-hover" >
                                    <thead>
                                    <tr>
                                        <th class="hidden">
                                            Hidden
                                        </th>

                                        <th>
                                            View Loan Details
                                        </th>

                                        <th>
                                            Loan Type
                                        </th>

                                        <th>
                                            For Months
                                        </th>

                                        <th>
                                            Amount Applied
                                        </th>

                                        <th>
                                            Status
                                        </th>

                                        <th>
                                            Amount Approved
                                        </th>

                                        <th>
                                            For Months
                                        </th>

                                        <th>
                                            Undertaken
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

                                        foreach($loans as $loan):

                                            ?>
                                            <tr class="odd gradeX">
                                                <td class="hidden">
                                                    <?php echo $i;?>
                                                </td>

                                                <td >
                                                    <?php

                                                    if ($loan['cash_given'] > 0):?>

                                                        <a class="btn green" href="<?php echo site_url().'/loans/loans_detail_view/'.$loan['id'];?>" >
                                                            <i class="fa fa-eye"></i>
                                                        </a>

                                                    <?php

                                                    endif;
                                                    ?>
                                                </td>

                                                <td><?php  echo $loan ['type']  ?></td>
                                                <td><?php  echo $loan ['months']  ?></td>
                                                <td><?php  echo $loan ['amount_applied']  ?></td>
                                                <td> <?php
                                                    if ($loan['status'] == '0')
                                                        echo  "<a data-toggle='modal' data-id='$i' class='open-AddBookDialog btn btn-warning' style='width: 100px' href='#updateleavemodal' >PENDING</a>";
                                                    else{
                                                        if ($loan['status'] == '2')
                                                        {
                                                            echo "<a data-toggle='modal' class='btn btn-danger'  style='width: 100px'  >REJECTED</a>";
                                                        }
                                                        else
                                                        {
                                                            if ($loan['remaining'] == "0"){
                                                                if ($loan['cash_given'] != null){
                                                                    echo " <a data-toggle='modal' class='btn btn-success' style='width: 100px' >Cleared</a>";
                                                                }else{
                                                                    echo " <a data-toggle='modal' class='btn btn-success' style='width: 100px' >Approved</a>";
                                                                }
                                                            }

                                                            else
                                                                echo " <a data-toggle='modal' class='btn btn-primary' style='width: 100px' >Running</a>";

                                                        }
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php  echo $loan ['amount_approved']  ?></td>
                                                <td><?php  echo $loan ['months_approved']  ?></td>

                                                <td>
                                                    <?php
                                                    if ($loan['undertaken_img']!=''):

                                                    ?>
                                                    <a class="btn green" href="<?php echo base_url();?>uploads/<?php echo $loan['undertaken_img']?>" target="_blank">
                                                        <i class="fa fa-image"></i>  Show Image
                                                    </a>

                                                    <?php
                                                    endif;
                                                    ?>

                                                </td>
                                                <td><?php  echo $loan ['created_at']  ?></td>
                                                <td>
                                                    <?php if ($loan['status'] == '0'): ?>
                                                    <a data-toggle="modal" data-id="<?php echo $i ?>" title="Add this item" class="open-AddBookDialog btn btn-primary" href="#updateloanmodal">
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

            

            <!-- Struck of Details-->

		</div>

	</div>
	<!-- END CONTENT -->

    <div class="modal fade" id="insertloanmodal" tabindex="-1"   data-width="600" >


                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">Apply Advance/Loan</h4>
                </div>

                <div class="modal-body">
                    <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/loans/insert_loan">

                        <div class="form-body">

                            <div class="form-group" id="half">
                                <label class="col-md-6 control-label">LOAN TYPE</label>
                                <div class="col-md-6 radio-list">
                                    <label class="radio-inline">
                                        <input type="radio" class="loan_type" name="loan_type" value="LOAN" checked>LOAN</label>
                                    <label class="radio-inline">
                                        <input type="radio" class="loan_type" name="loan_type"  value="ADVANCE">ADVANCE</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-6 control-label"> Select Staff <span class="required">*</span></label>
                                <div class="col-md-6">
                                    <select class="form-control select2" name="user_id" id="user_id" required>

                                        <option value="">Select User</option>
                                        <?php foreach ($staffs as $staff): ?>
                                               <option value="<?php echo $staff['user_id'] ?>"> <?php echo $staff['first_name'] ?>  <?php echo $staff['last_name'] ?> </option>
                                        <?php endforeach; ?>

                                    </select>
                                    <!--<span class="help-inline"></span>-->
                                </div>
                            </div>

                            <div class="form-group" id="in_months_div">
                                <label class="col-md-6 control-label">FOR NO OF MONTHS <span class="required">*</span></label>
                                <div class="col-md-6">
                                    <select class="form-control" name="in_month" id="in_month" required>
                                        <?php for ($i=1;$i<=$max_months;$i++){
                                            echo "<option value='$i'>$i Months</option>";
                                        } ?>
                                    </select>
                                    <!--<span class="help-inline"></span>-->
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-6 control-label">AMOUNT</label>
                                <div class="col-md-6">
                                    <input type="number"  name="amount" class="form-control mobile" id="my_max_amount" max="" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-6 control-label">REASON <span class="required">*</span></label>
                                <div class="col-md-6">

                                    <textarea class="form-control remarks" rows="3" name="reason" required></textarea>

                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">Undertaken Image</label>
                                <div class="col-md-8">
                                    <input type="file" name="image" class="form-control" />
                                </div>
                            </div>


                        </div>

                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-3 col-md-9">

                                    <button type="submit" class="btn red">Apply Loan</button>

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
            <h4 class="modal-title">Update Leave</h4>
        </div>
        <div class="modal-body">
            <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/loans/insert_edit_apply_loan">
                <div class="form-body">
                    <div class="form-group" id="half">
                        <label class="col-md-6 control-label">LOAN TYPE</label>
                        <div class="col-md-6 radio-list">
                                <label class="radio-inline">
                                    <input type="radio" class="loan_type" name="loan_type" id="loan_type_advance" value="ADVANCE" checked >ADVANCE</label>
                                <label class="radio-inline">
                                    <input type="radio" class="loan_type" name="loan_type" id="loan_type_Loan" value="LOAN">LOAN</label>

                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-6 control-label">FOR NO OF MONTHS <span class="required">*</span></label>
                        <div class="col-md-6">
                            <select class="form-control" name="in_month" id="upin_month" required>
                                <?php for ($i=0;$i<$max_months;$i++){
                                    echo "<option value='$i'>$i Months</option>";
                                } ?>
                            </select>
                            <!--<span class="help-inline"></span>-->
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-6 control-label">AMOUNT</label>
                        <div class="col-md-6">
                            <input type="number"  name="amount" id="amount" max="<?php echo $max_amount ?>" class="form-control mobile" value="" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-6 control-label">REASON <span class="required">*</span></label>
                        <div class="col-md-6">

                            <textarea class="form-control remarks" rows="3" name="reason" id="reason" required></textarea>

                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-4 control-label">Undertaken Image</label>
                        <div class="col-md-8">
                            <input type="file" name="image" class="form-control" />
                        </div>
                    </div>


                </div>

                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-offset-3 col-md-9">

                            <input type="hidden" id="loan_id" name="loan_id" value="" />
                            <button type="submit" class="btn red">Update Leave</button>

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
        $("select").select2();
        var is_admin = '<?php echo $this->session->userdata('role');?>';
            if(is_admin != 'Admin') {
                $('#user_id').on('change', function () {

                    var plans = <?php echo json_encode($staffs) ?>;
                    var loan_setting = <?php echo json_encode($loansetting) ?>;
                    let i;
                    for (i = 0; i < plans.length; i++) {
                        if (plans[i].user_id === this.value) {
                            var maxamount = plans[i].gross_salary * parseInt(loan_setting[0].max_multiply_salary);
                            $(".modal-body #my_max_amount").attr('max', maxamount);

                            $(".modal-body #my_max_amount").keydown(function () {
                                // Save old value.
                                if (!$(this).val() || (parseInt($(this).val()) <= $(".modal-body #my_max_amount").attr('max') && parseInt($(this).val()) >= 0)) {
                                    $(this).data("old", $(this).val());
                                }
                            });
                            $(".modal-body #my_max_amount").keyup(function () {
                                // Check correct, else revert back to old value.
                                if (!$(this).val() || (parseInt($(this).val()) <= $(".modal-body #my_max_amount").attr('max') && parseInt($(this).val()) >= 0))
                                    ;
                                else {
                                    $(this).val($(this).data("old"));
                                    alert("You can't take Loan More Then " + maxamount)
                                }
                            });
                        }
                    }
                });
            }
    }, false );
</script>
