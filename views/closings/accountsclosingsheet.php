<?php
$myAccess = checkUserAccess();
?>
<style>
    .loader {
        border: 16px solid #f3f3f3; /* Light grey */
        border-top: 16px solid #3498db; /* Blue */
        border-radius: 50%;
        width: 120px;
        height: 120px;
        animation: spin 2s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <!--<h3 class="page-title">
        All Classes <small>Here you can find all classes</small>
        </h3>-->
        <!-- END PAGE HEADER-->
        <!-- BEGIN PAGE CONTENT-->
        <?php if(@$this->session->userdata('message')):?>
            <div class="alert alert-success">
                <button class="close" data-close="alert"></button>
                <span>
                    <?php echo $this->session->userdata('message');?> </span>
            </div>
        <?php endif;?>
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet box grey-cascade">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i>Daily Closing Conciliation
                        </div>
                    </div>
                    <div class="col-md-6" style="background-color: white;padding: 20px 30px;    border-radius: 20px!important;box-shadow: 10px 10px 5px -8px rgba(0,0,0,0.75);">
                        <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/closing/accountsclosing">
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label col-md-3">From<span class="required">*</span></label>
                                            <div class="col-md-3">
                                                <div class="input-group input-medium date date-picker" data-date="<?php if(@$this->input->post('from_date')){echo $this->input->post('from_date');}else{echo date('Y-m-d');}?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                    <input type="text" min="2021-05-24" name="from_date" class="form-control" value="<?php if(@$this->input->post('from_date')){echo $this->input->post('from_date');}else{echo date('Y-m-d');}?>" readonly>
                                                    <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label col-md-3">To<span class="required">*</span></label>
                                            <div class="col-md-3">
                                                <div class="input-group input-medium date date-picker" data-date="<?php if(@$this->input->post('to_date')){echo $this->input->post('to_date');}else{echo date('Y-m-d');}?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                    <input type="text" name="to_date" class="form-control" value="<?php if(@$this->input->post('to_date')){echo $this->input->post('to_date');}else{echo date('Y-m-d');}?>" readonly>
                                                    <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="form-group">
                                    <label class="col-md-2 control-label"> Select Campuses <span class="required">*</span></label>
                                    <div class="col-md-4">
                                        <select class="form-control" name="expense_campus_ids" >
                                            <option value="" >
                                               All Campuses
                                            </option>
                                            <?php
                                            foreach($campuses as $campus):
                                                ?>
                                                <option value="<?php echo $campus['campus_id'];?>" >
                                                    <?php echo $campus['campus_name'];?>
                                                </option>
                                            <?php
                                            endforeach;
                                            ?>
                                        </select>
                                        <!--<span class="help-inline"></span>-->
                                    </div>
                                    <label class="control-label col-md-2">Select Entry Type</label>
                                    <div class="form-group col-md-4">
                                        <select class="form-control" name="tag_type" required>
                                            <option value="2">ALL Entries</option>
                                            <option value="0">Bank UNTagged Entries</option>
                                        </select>
                                    </div>
                                </div>

                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <button type="submit" class="btn green">Check Closings</button>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>
				<div class="col-md-6" style="background-color: white;padding: 20px 30px;    border-radius: 20px!important;box-shadow: 10px 10px 5px -8px rgba(0,0,0,0.75);">
                <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i> Last Closing/Verification Date
                        </div>
                    </div>
                
                    <table class="table table-striped table-bordered table-hover" id="">
                            <thead>
                            <tr>
							  <th>
                                    College
                                </th>
								
                               <th>
                                    Closing Date
                               </th>
                              
                               <th>
                                    Verification Date
                               </th>
                              
								
                                
                            </tr>
                            </thead>
                            <tbody>

                            <?php
                            $i=1;
                            foreach($campusclosings as $key=>$closes):
                                ?>
                                <tr class="odd gradeX">
                                    
									 <td>
                                        <?php echo $closes['campus_name'];?>
                                    </td>
                                   
                                    <td>
                                        <?php echo $closes['day'] .'-'.$closes['month'].'-'.$closes['year'] ;?>
                                    </td>
									<td>
                                        <?php echo $campusclosingverified[$key]['day'] .'-'.$campusclosingverified[$key]['month'].'-'.$campusclosingverified[$key]['year'] ;?>
                                    </td>
                                   
                                </tr>
                                <?php
                                $i++;
                            endforeach;
                            ?>


                            </tbody>
                        </table>
                    
               
				
				
				
            </div>

                    <?php
                    if(@count($closings)>0 ):
                        ?>
                        <div class="portlet-body">
                            <div class="alert alert-success">

                            </div>
                            <table class="table table-striped table-bordered table-hover" id="sample_2">
                                <thead>
                                <tr>
                                    <th >
                                        hidden
                                    </th>
                                    <th>
                                        Campus
                                    </th>
                                    <th>
                                        Closing ID
                                    </th>
                                    <th>
                                        Date
                                    </th>
									 <th>
                                        Closed Amount
                                    </th>
                                    <th>
                                        Received Amount
                                    </th>
                                    <th>
                                        Closing Type
                                    </th>
                                    <th>
                                        Closed By
                                    </th>
                                    <th>
                                        Transaction NO
                                    </th>
                                    <th>
                                        Receipt Image
                                    </th>
                                    <th>
                                        Action
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $i = 0;
                                foreach($closings as $expense):
                                    ?>
                                    <tr class="odd gradeX">
                                        <td >
                                            <?php echo $i+1;?>
                                        </td>
                                        <td>
                                            <?php echo $expense['campus_name']?>
                                        </td>
                                        <td>
                                            <?php echo $expense['campus_closing_id']?>
                                        </td>

                                        <td>
                                            <?php echo $expense['for_day'] .'-'.$expense['for_month'].'-'.$expense['for_year'].'';?>
                                        </td>
                                        <td>
                                            <?php echo $expense['receivable_amount'];?>
                                        </td>
										<td>
                                            <?php echo $expense['closed_amount'];?>
                                        </td>
                                        <td>
                                            <?php
                                            if ($expense['close_type'] == '1')
                                                echo 'Bank Closed';
                                            elseif ($expense['close_type'] == '3')
                                                echo "PayPro Closed";
                                            else
                                                echo "Cash Closed";
                                            ?>
                                        </td>
                                        <td>
                                            <?php echo $expense['closed_by'];?>
                                        </td>
                                        <td>
                                            <?php echo $expense['transaction_no'];?>
                                        </td>
                                        <td>
                                            <?php
                                            if( $expense['partialy_closed_image']!=''):
                                                ?>
                                                <a href="<?php echo base_url().'uploads/'.$expense['partialy_closed_image'];?>" target="_blank">
                                                    <button type="button" class="btn btn-default"><i class="fa fa-image"></i> Image</button>
                                                </a>
                                            <?php
                                            endif;
                                            ?>
                                        </td>

                                        <td>
                                            <?php $selected_date=$expense['for_year'] .'-'.$expense['for_month'].'-'.$expense['for_day'].'';
                                            $last_date = "";
                                            foreach ($campusclosingverified as $key => $val) {
                                                if ($val['campus_id'] === $expense['campus_id']) {
                                                    $last_date = $campusclosingverified[$key]['year'].'-'.$campusclosingverified[$key]['month'].'-'.$campusclosingverified[$key]['day']  ;
                                                    $last_date =date('Y-m-d', strtotime($last_date . ' +1 day'));
                                                }
                                            }

                                            ?>
                                            <?php
                                                if($expense['close_type'] != '3'){
                                                    if (($expense['checked_by'] == NULL && $expense['close_type'] != '1') || ($expense['checked_by'] == NULL && $expense['close_type'] == '1' && $expense['partialy_closed_image']!='')):
                                                    $date1 = new DateTime($selected_date);
                                                    $date2 = new DateTime($last_date);

                                                    if ($date1 == $date2)
                                                    {
                                                        if(@$myAccess[0]['closing_conciliation_edit']==1 || $this->session->userdata('role')=='Admin')
                                                        {
                                                            if ($expense['close_type'] != '2'){
                                                        ?>
                                                                <a data-toggle="modal" data-id="<?php echo $i; ?>" title="Add this item" class="open-BankTransfer btn blue" href="#bank_transfer">
                                                                    <i class="fa fa-dollar"> Verify Bank</i>
                                                                </a>
                                                            <?php
                                                            }
                                                            else {?>
                                                                <a data-toggle="modal" data-id="<?php echo $i; ?>" data-closing-id="<?php echo $expense['id']; ?>" title="Add this item" class="open-EditCashClosing verify-cash-btn btn blue" href="#cashclosingdetails">
                                                                    <i class="fa fa-dollar"> Verify Cash</i>
                                                                </a>
                                                            <?php
                                                            }
                                                        }
                                                        else  { ?>
                                                            <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/closing/verify_closing_now">
                                                                <div class="form-body">


                                                                    <div class="form-group">
                                                                        <label class="col-md-3 control-label">Received Amount</label>
                                                                        <div class="col-md-9">
                                                                            <input type="hidden"  name="amount" id="cash_amount" class="form-control mobile" minlength="4" value="<?php echo $expense['closed_amount']; ?>"/>

                                                                        </div>
                                                                    </div>

                                                                </div>

                                                                <div class="form-actions">
                                                                    <div class="row">
                                                                        <div class="col-md-offset-3 col-md-9">

                                                                            <input type="hidden"  id="cash_closingid" name="closingid" value="<?php echo $expense['id']?>" class="form-control"/>
                                                                            <button type="submit" class="btn red">Verify Now</button>

                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        <?php }
                                                    }?>

                                                    <?php
                                                    elseif (($expense['checked_by'] != NULL && $expense['close_type'] == '1') || ($expense['checked_by'] != NULL && $expense['close_type'] == '1' && $expense['partialy_closed_image']!='')):?>
                                                        <a href="#" title="Verify Now" class="btn green">Verified</a>
                                                        <?php if ($expense['close_type'] == '1'):
                                                            echo '<a data-toggle="modal" data-id="'.$i.'" title="Add this item" class="open-EditClosing btn btn-warning" href="#closingdetails">
                                                                    <i class="fa fa-dollar"> Edit</i>
                                                                  </a>';
                                                                    $this->db->select('*');
                                                                    $this->db->from('bank_reconciliation_statement');
                                                                    $this->db->where(array('closing_id'=>$expense['id']));
                                                                    $closing = $this->db->get()->row();
                                                                    if ($closing != NULL):
                                                                        echo '<strong> Deposit Detail : </strong>'.@$closing->description.'<br>';
                                                                    else:
                                                                        ?>
                                                                        <a data-toggle="modal" data-id="<?php echo $i; ?>" title="Add this item" class="open-BankTransfer btn blue" href="#bank_transfer">
                                                                            <i class="fa fa-dollar"> Verify Bank</i>
                                                                        </a>
                                                        <?php
                                                                    endif;
                                                            endif;
                                                    elseif (($expense['checked_by'] == NULL && $expense['close_type'] == '1' && $expense['partialy_closed_image']=='')):?>
                                                        <!--
                                                        <a href="#" title="Un-verify" class="btn red">Un-Verified</a>
                                                        <a class="btn red" href="<?php echo site_url();?>/closing/deleteClosing/<?php echo $expense['id'];?>" onclick="return confirm('Are you sure you want to delete this Closing?')">Delete</a>
                                                        -->
                                                    <?php
                                                    else:
                                                        echo '<a href="#" title="Verify Now" class="btn green">Verified</a>';
                                                    endif;
                                                }
                                                else{
                                                    $status = @$this->db->get_where("students_payments","closing_id = '".$expense['id']."'")->row()->transaction_status;
                                                    if ($status == "UNPAID")
                                                        echo '<a href="#" title="Un-verify" class="btn red">'.$status.'</a>';
                                                    elseif ($status == "PAID")
                                                        echo '<a href="#" title="Un-verify" class="btn green">'.$status.'</a>';
                                                }
											?>
                                            <br />
                                            <a class="btn green" href="<?php  echo site_url().'/closing/viewclosing/'.$selected_date.'/'.$expense['campus_id'].'/'.$expense['closed_amount'].'/1'?>"> VIEW </a>

                                        </td>

                                    </tr>
                                    <?php
                                    $i++;
                                endforeach;
                                ?>
                                </tbody>
                            </table>
                        </div>
                    <?php
                    endif;
                    ?>
                </div>
                <!-- END EXAMPLE TABLE PORTLET-->
            </div>
        </div>
        <!-- END PAGE CONTENT-->
    </div>
</div>

<div class="modal fade" id="closingdetails" tabindex="-1"   data-width="600" >

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Add Closing Details</h4>
    </div>
    <div class="modal-body">
        <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/closing/add_closing_details">
            <div class="form-body">

				<div class="form-group" id="fraccount">
                    <label class="col-md-3 control-label">Select Bank <span class="required">*</span></label>
                    <div class="col-md-9">
                        <select name="account_id" id="account_id" class="form-control input-inline input-large " required>
                            <option value="">Select Account From</option>
                            <?php
                            foreach($accounts as $account):
                                ?>
                                <option value="<?php echo $account['id'];?>"><?php echo $account['account_title'].' '.$account['account_name'];?></option>
                            <?php
                            endforeach;
                            ?>
                        </select>
                    </div>
                </div>
               
                <div class="form-group">
                    <label class="col-md-3 control-label">Transaction ID</label>
                    <div class="col-md-9">
                        <input type="text"  name="trans_id" id="trans_id" class="form-control mobile" minlength="4" required/>

                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label">Transaction Image<span class="required">*</span></label>
                    <div class="col-md-9">
                        <input type="file"  name="image" id="file" required/>

                    </div>
                </div>


            </div>

            <div class="form-actions">
                <div class="row">
                    <div class="col-md-offset-3 col-md-9">

                        <input type="hidden"  id="closingid" name="closingid" value="" class="form-control mobile"/>
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

<div class="modal fade" id="cashclosingdetails" tabindex="-1"   data-width="600" >

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Confirm Closing Details</h4>
    </div>
    <div class="modal-body">
        <form id="verify_cash_form" class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/closing/verify_closing_now">
            <div class="form-body">
                <div class="form-group">
                    <label class="col-md-3 control-label">Received Amount</label>
                    <div class="col-md-9">
                        <input type="text"  name="amount" id="cash_amount" class="form-control mobile" minlength="4" required/>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <div class="row">
                    <div class="col-md-offset-3 col-md-9">
                        <input type="hidden"  id="cash_closingid" name="closingid" value="" class="form-control mobile"/>
                        <button type="submit" id="verify_cash_submit" class="btn red">Submit</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn default close_button" data-dismiss="modal">Close</button>
    </div>
</div>

<div class="modal fade" id="bank_transfer" tabindex="-1" role="dialog"  data-width="1200" >

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Bank Transfer</h4>
    </div>
    <div class="modal-body">

        <div class="row">
            <div class="col-md-6">
                <form id="find_statement"  method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="control-label col-md-3">From Date</label>
                        <div class="col-md-3">
                            <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                <input type="text" name="from_date" id="from_adv_date" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
                                <span class="input-group-btn">
                                    <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3">To Date</label>
                        <div class="col-md-3">
                            <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                <input type="text" name="to_date" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
                                <span class="input-group-btn">
                                    <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <br />
                    <div class="form-group" style="margin-top: 5px;">
                        <label class="col-md-3 control-label">Received Amount <span class="required">*</span></label>
                        <div class="col-md-9">
                            <input type="text"  name="amount" id="cash_bank_amount" class="form-control mobile" minlength="4" <?php if($this->session->userdata('role')=='Admin' || $myAccess[0]['closing_amount_edit'] == '1') echo ""; else echo  "readonly";?>/>
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-9">
                                <input type="hidden" name="bank_trans_id" id="bank_closing_id" />
                                <div class="loader" id="loader_div" style="width: 120px;"></div>
                                <button type="button" id="find_trans" class="btn red">Find Transaction</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="clearfix"></div>
        </div>

        <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/closing/tag_bank_trans">
            <div class="form-body" style="margin-top: 20px;">
                <div class="portlet-body">
                    <table class="table table-striped table-bordered table-hover" id="histtable">
                        <thead>
                            <tr>
                                <th>
                                    Sr
                                </th>
                                <th>
                                    Bank Name
                                </th>
                                <th>
                                    Transaction Date
                                </th>
                                <th>
                                    Transaction Type
                                </th>
                                <th>
                                    Debit
                                </th>
                                <th>
                                    Credit
                                </th>
                                <th>
                                    Balance
                                </th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>

            <input type="hidden" class="bank_trans_id" id="bank_trans_closing_id" name="bank_trans_id" />
            <button type="submit" id="submit_trans" class="btn blue">Submit</button>
        </form>

    </div>


</div>

<script>
    document.addEventListener( "DOMContentLoaded", function()
    {
        $('#loader_div').hide();
        $(document).on("click", ".open-EditCashClosing", function () {
            var myBookId = $(this).data('id');
            var plans = <?php echo json_encode(isset($closings) ? $closings : array()) ?>;

            $(".modal-body #cash_amount").val(plans[myBookId].closed_amount);
            $(".modal-body #cash_closingid").val(plans[myBookId].id);
        });
        $("#verify_cash_form").on("submit", function (e) {
            e.preventDefault();

            var $form = $(this);
            var $submitBtn = $("#verify_cash_submit");

            $submitBtn.prop("disabled", true);

            $.ajax({
                url: "<?php echo site_url();?>/closing/verify_closing_now",
                type: "POST",
                data: $form.serialize(),
                dataType: "json",
                success: function (response) {
                    if (response.success) {
                        $("#cashclosingdetails").modal("hide");
                        $('.verify-cash-btn[data-closing-id="' + response.closing_id + '"]').replaceWith('<a href="#" title="Verify Now" class="btn green">Verified</a>');
                    } else {
                        alert(response.message || "Verification failed.");
                    }
                    $submitBtn.prop("disabled", false);
                },
                error: function () {
                    alert("Something went wrong. Please try again.");
                    $submitBtn.prop("disabled", false);
                }
            });
        });
        $(document).on("click", ".open-BankTransfer", function () {
            var myBookId = $(this).data('id');
            var plans = <?php echo json_encode($closings) ?>;

            $(".modal-body #cash_bank_amount").val(plans[myBookId].closed_amount);
            $(".modal-body #bank_closing_id").val(plans[myBookId].id);
            $(".modal-body #bank_trans_closing_id").val(plans[myBookId].id);
            $(".modal-body #from_adv_date").val(plans[myBookId].for_year+"-"+plans[myBookId].for_month+"-"+plans[myBookId].for_day);
        });
        $("#find_trans").click(function() {
            $('#loader_div').show();
            var data = new FormData(this.form);
            $.ajax({
                url: "<?php echo site_url();?>/closing/find_transactions",
                type: 'POST',
                data: data,
                processData: false,
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                contentType: false,
                dataType: 'text',
                success: function (data) {
                    $(".modal-body #histtable tbody").html(data);
                    $('#loader_div').hide();
                },
                error: function (data)
                {
                    $('#loader_div').hide();
                }
            });
            e.preventDefault();
        });

    }, false );
</script>
<!-- END CONTENT -->
