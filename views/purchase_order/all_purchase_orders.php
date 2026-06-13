<?php
	$myAccess = checkUserAccess();
?>	
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
            <?php if(@$this->session->userdata('error')):?>
                <div class="alert alert-danger">
                    <button class="close" data-close="alert"></button>
                    <span>
                    <?php echo $this->session->userdata('error');?> </span>
                </div>
            <?php endif;?>
            <div class="row" style="margin-bottom:15px; padding:15px; margin-left: 0px; margin-right: 0px; border: 1px solid black">

                <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/purchase_order/all_orders">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label col-md-3">From Date <span class="required">*</span></label>
                                    <div class="col-md-3">
                                        <div class="input-group input-medium date date-picker" data-date="<?php echo $from_date;?>" data-date-format="yyyy-mm-dd" <?php if(@$myAccess[0]['expense_no_of_days']!=1 && $this->session->userdata('role') != 'Admin'): ?> data-date-start-date="-45d"  data-date-end-date="0d" <?php endif;?> data-date-viewmode="years">
                                            <input type="text" name="from_date" class="form-control" value="<?php echo $from_date;?>" readonly>
                                            <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label col-md-3">To Date <span class="required">*</span></label>
                                    <div class="col-md-3">
                                        <div class="input-group input-medium date date-picker" data-date="<?php echo $to_date;?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                            <input type="text" name="to_date" class="form-control" value="<?php echo $to_date;?>" readonly>
                                            <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-3">
                                        <button type="submit" class="btn green">Check Orders</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="col-md-2">

                    <form method="post" action="<?php echo site_url();?>/purchase_order/all_orders">
                        <input type="hidden"  name="from_date" value="<?php echo $from_date ?>" />
                        <input type="hidden"  name="to_date" value="<?php echo $to_date ?>"/>
                        <input type="submit" style="width: 100%;" name="setype" class="btn btn-success" value="Pending" />
                        <span class="badge" id="bcount" style="background-color: red; font-weight: bold"><?php echo $pending ?></span>
                    </form>

                </div>

                <div class="col-md-2">
                    <form method="post" action="<?php echo site_url();?>/purchase_order/all_orders">
                        <input type="hidden"  name="from_date" value="<?php echo $from_date ?>" />
                        <input type="hidden"  name="to_date" value="<?php echo $to_date ?>"/>
                        <input type="submit" style="width: 100%;" class="btn blue" name="setype" value="Approved" />
                        <span class="badge" id="bcount" style="background-color: red; font-weight: bold"><?php echo $approved ?></span>
                    </form>
                </div>

                <div class="col-md-2">
                    <form method="post" action="<?php echo site_url();?>/purchase_order/all_orders">
                        <input type="hidden"  name="from_date" value="<?php echo $from_date ?>" />
                        <input type="hidden"  name="to_date" value="<?php echo $to_date ?>"/>
                        <input type="submit" style="width: 100%;" class="btn red" name="setype" value="Rejected" />
                        <span class="badge" id="bcount" style="background-color: red; font-weight: bold"><?php echo $rejected ?></span>
                    </form>
                </div>

                <div class="col-md-2">
                    <form method="post" action="<?php echo site_url();?>/purchase_order/all_orders">
                        <input type="hidden"  name="from_date" value="<?php echo $from_date ?>" />
                        <input type="hidden"  name="to_date" value="<?php echo $to_date ?>"/>
                        <input type="submit" style="width: 100%;" class="btn purple" name="setype" value="Purchased" />
                        <span class="badge" id="bcount" style="background-color: red; font-weight: bold"><?php echo $purchased ?></span>
                    </form>
                </div>

            </div>
            <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i>All Purchase Orders
							</div>
						</div>
						<div class="portlet-body">
							<table class="table table-striped table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
								<th class="hidden">
                                	 hidden
                                </th>
                                <th>
									 Purchaser Name
								</th>
								<th>
									 Vendor Name
								</th>
                                <th>
                                    Vendor Address
								</th>
                                <th>
                                    Required Amount
								</th>
                                <th>
                                    Purchase Date
                                </th>
								<th>
									 Purchase Slip
								</th>
                                <th>
                                    Purchased Amount
                                </th>
                                <th>
                                    Status
                                </th>
                                <th>
                                    Paid Status
                                </th>
                                <th>
                                    Paid Amount
                                </th>
                                <th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($purchase_orders as $orders):

							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $orders['first_name'].' '.$orders['last_name']?>
								</td>
								<td>
                                    <?php echo $orders['vendor_name']?>
								</td>
                                <td>
                                    <?php echo $orders['vendor_address']?>
								</td>
                                <td>
									<?php echo $orders['total_amount']?>
								</td>
                                <td>
									<?php echo $orders['purchased_date']?>
								</td>
                                <td>
									<?php 
										if($orders['po_image']!='')
										{
											echo '<a class="btn red" target="_blank" href="'.base_url().'uploads/'.$orders['po_image'].'"><i class="fa fa-image"></i></a>';
										}
									?>
								</td>
                                <td>
                                    <?php echo $orders['purchased_amount']?>
                                </td>
                                <td>
                                    <?php
                                    if ($orders['p_status'] == "0")
                                        echo  "<a data-toggle='modal' data-id='$i' class='btn blue open-purchase_approval' style='width: 100px;' href='#historyexpense'>Pending</a>";

                                    elseif ($orders['p_status'] == '2')

                                        echo  "<input class='btn red' value='Rejected' style='width: 100px'/>";
                                    elseif ($orders['p_status'] == '3')

                                        echo  "<input class='btn green' value='Purchased' style='width: 100px'/>";
                                    else
                                        echo " <a data-toggle='modal' data-id='$i' href='#purchased' class='btn green purchased' style='width: 100px'>Approved</a>";
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if ($orders['paid_amount'] == "0")
                                        echo  "<a data-toggle='modal' data-id='$i' class='btn red' style='width: 100px;'>Unpaid</a>";

                                    elseif ($orders['paid_amount'] < $orders['purchased_amount'])

                                        echo  "<input class='btn blue' value='Partially Paid' style='width: 120px'/><br /><br />
                                                <a data-toggle='modal' data-id='$i' class='btn green add_detail' href='#add_detail' style='width: 120px'>Add Payment</a>";
                                    elseif ($orders['paid_amount'] == $orders['purchased_amount'])

                                        echo  "<input class='btn green' value='Paid' style='width: 100px'/>";
                                    ?>
                                </td>
                                <td>
                                    <?php echo $orders['paid_amount']?>
                                </td>
								<td>
                                    <?php
                                    if(@$myAccess[0]['update_purchase']==1):
                                    ?>
                                    <a href="<?php echo site_url().'/purchase_order/edit_order/'.$orders['id'];?>" title="Edit" class="btn blue"><i class="fa fa-edit"></i></a>
                                    <?php endif; ?>
                                    <a href="<?php echo site_url().'/purchase_order/view_order/'.$orders['id'];?>" title="Edit" class="btn green"><i class="fa fa-eye"></i></a>
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
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->

    <div class="modal fade" id="historyexpense" tabindex="-1"   data-width="1200" >

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Expense History</h4>
    </div>
    <div class="modal-body">

        <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/purchase_order/change_approve_status">
            <div class="form-body">


                <div class="form-group">

                    <div class="col-md-12">

                        <label class="form-control" style="text-align: center" >Do you Want to Approve this Purchase Order?</label>
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                            <tr>
                                <th>
                                    Purchaser Name
                                </th>
                                <th>
                                    Vendor Name
                                </th>
                                <th>
                                    Vendor Address
                                </th>
                                <th>
                                    Total Amount
                                </th>
                                <th>
                                    Requested Date
                                </th>
                            </tr>
                            </thead>
                            <tbody>

                                <tr class="odd gradeX">

                                    <td id="purchaser_name">
                                    </td>
                                    <td id="vendor_name">
                                    </td>
                                    <td id="vendor_address">
                                    </td>
                                    <td id="total_amount">
                                    </td>
                                    <td id="created_at">
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </div>

                <input type="hidden"  name="setype" value="<?php echo @$setype ?>" />

                <?php if ($myAccess[0]['expense_approval'] === '1'): ?>
                    <div id = "apvdiv">

                        <div class="form-group" >
                            <label class="col-md-6 control-label">Accept or Reject this Purchase Order</label>
                            <div class="col-md-6 radio-list" name="radiolist" id="radiolist">
                                <label class="radio-inline">
                                    <input type="radio" class="status" name="status"  value="1" >Accept</label>
                                <label class="radio-inline">
                                    <input type="radio" class="status" name="status"  value="2">Reject</label>
                            </div>
                        </div>

                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-12" style="text-align: center">

                                    <input type="hidden" id="expense_id" name="expense_id" value="" />
                                    <input type="hidden" name="last_edit" value="<?php echo $this->session->userdata('name');?>" />
                                    <button type="submit" class="btn red">Submit</button>

                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
        </form>

    </div>
</div>

    <div class="modal fade" id="purchased" tabindex="-1"   data-width="600" >
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title">Purchased</h4>
        </div>
        <div class="modal-body">
            <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/purchase_order/change_purchased_status">
                <div class="form-body">
                    <div class="form-group">
                        <div class="col-md-12">
                            <label class="form-control" style="text-align: center" >Did you Purchased this Order?</label>
                        </div>
                    </div>

                    <?php if ($myAccess[0]['expense_approval'] === '1'): ?>
                        <div id = "apvdiv">

                            <div class="form-group" >
                                <label class="col-md-6 control-label">Purchased Amount</label>
                                <div class="col-md-6" >
                                        <input type="number" name="purchased_amount" required/>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-6 control-label">Image <span class="required">*</span></label>
                                <div class="col-md-6">
                                    <input type="file" name="picture" required>
                                    <span class="help-inline"></span>
                                </div>
                            </div>

                            <div class="form-group" >
                                <label class="col-md-6 control-label">Paid Amount</label>
                                <div class="col-md-6" >
                                    <input type="number" name="paid_amount" required/>
                                </div>
                            </div>

                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-12" style="text-align: center">
                                        <input type="hidden" id="expense_purch_id" name="expense_id" value="" />
                                        <input type="hidden" name="last_edit" value="<?php echo $this->session->userdata('name');?>" />
                                        <button type="submit" class="btn red">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="modal fade" id="add_detail" tabindex="-1"   data-width="600" >
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Purchased</h4>
    </div>
    <div class="modal-body">
        <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/purchase_order/add_payment_details">
            <div class="form-body">
                <div class="form-group">
                    <div class="col-md-12">
                        <label class="form-control" style="text-align: center" >Pay Now?</label>
                    </div>
                </div>

                <?php if ($myAccess[0]['expense_approval'] === '1'): ?>
                    <div id = "apvdiv">
                        <div class="form-group" >
                            <label class="col-md-6 control-label">Purchased Amount</label>
                            <div class="col-md-6" >
                                <label id="purchased_amount_detail"></label>
                            </div>
                        </div>
                        <div class="form-group" >
                            <label class="col-md-6 control-label">Already Paid Amount</label>
                            <div class="col-md-6" >
                                <label id="already_amount_detail"></label>
                            </div>
                        </div>
                        <div class="form-group" >
                            <label class="col-md-6 control-label">Paid Amount</label>
                            <div class="col-md-6" >
                                <input type="number" id="paid_amount_detail" name="paid_amount" required/>
                            </div>
                        </div>

                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-12" style="text-align: center">
                                    <input type="hidden" id="expense_detail_id" name="expense_id" value="" />
                                    <input type="hidden" name="last_edit" value="<?php echo $this->session->userdata('name');?>" />
                                    <button type="submit" class="btn red">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
        </form>
    </div>
</div>

<script>
    document.addEventListener( "DOMContentLoaded", function(){

        var loans = <?php echo json_encode($purchase_orders);?>;
        $(document).on("click", ".open-purchase_approval", function () {

            var myBookId = $(this).data('id');


            $(".modal-body #expense_id").val(loans[myBookId].id );
            $(".modal-body #purchaser_name").html( loans[myBookId].first_name +" "+loans[myBookId].last_name);
            $(".modal-body #vendor_name").html( loans[myBookId].vendor_name );
            $(".modal-body #vendor_address").html( loans[myBookId].vendor_address);
            $(".modal-body #total_amount").html( loans[myBookId].total_amount);
            $(".modal-body #created_at").html( loans[myBookId].created_at);

        });
        $(document).on("click", ".purchased", function () {
            var myBookId = $(this).data('id');
            $(".modal-body #expense_purch_id").val(loans[myBookId].id );
        });

        $(document).on("click", ".add_detail", function () {
            var myBookId = $(this).data('id');
            $(".modal-body #expense_detail_id").val(loans[myBookId].id );
            var rem_amount = loans[myBookId].purchased_amount - loans[myBookId].paid_amount;
            $(".modal-body #purchased_amount_detail").html(loans[myBookId].purchased_amount);
            $(".modal-body #already_amount_detail").html(loans[myBookId].paid_amount);
            $(".modal-body #paid_amount_detail").val(rem_amount);
        });

    }, false );
</script>