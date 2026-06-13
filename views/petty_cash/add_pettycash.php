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
                                    <i class="fa fa-user"></i> Petty Cash List
                                </div>
                            </div>



                            <div class="portlet-body table-responsive">

                                <div class="col-md-12 ">
                                    <div class="col-md-4">
									<?php if(@$myAccess[0]['add_pettycash']==1 || $this->session->userdata('role')=='Admin'): ?>
                                        <input type="submit" class="btn green col-md-4" style="margin: 10px; width: 160px;" name="student_check" value="Add Pettycash" data-toggle="modal" href="#insertloanmodal" />
                                    <?php endif; ?>
									</div>
                                    <div class="col-md-4">
                                        <a class="btn green" style="margin-top: 10px; " href="<?php echo site_url();?>/pettycash/index/1" >Active Accounts</a>
                                        <br />
                                        <span class="badge" id="bcount" style="background-color: red; font-weight: bold"><?php echo $active ?></span>
                                    </div>
                                    <div class="col-md-4">
                                        <a class="btn yellow" style="margin-top: 10px;" href="<?php echo site_url();?>/pettycash/index/0" >Inactive Accounts</a>
                                        <br />
                                        <span class="badge" id="bcount" style="background-color: red; font-weight: bold"><?php echo $inactive ?></span>
                                    </div>
                                </div>
                                <table class="table table-bordered table-hover" >
                                    <thead>
                                    <tr>
                                        <th class="hidden">
                                            Hidden
                                        </th>

                                        <th>
                                            Campus Name
                                        </th>
										<th>
                                            Recovery From
                                        </th>
                                        <th>
                                            Petty Cash Amount
                                        </th>
										
										<th>
                                            Balance
                                        </th>
										<th>
                                            Require Amount
                                        </th>
										<th>
                                            Given To
                                        </th>
                                        <th>
                                            Designation
                                        </th>
                                        <th>
                                            For Month
                                        </th>
                                      
                                        <th>
                                            Action
                                        </th>
										
										 <th>
                                            View
                                        </th>


                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $i=0;
                                        foreach($Pettycashs as $Pettycash):
                                            ?>
                                            <tr class="odd gradeX">
                                                <td class="hidden">
                                                    <?php echo $i;?>
                                                </td>
                                                <td >
                                                    <?php  echo $Pettycash ['campus_name']  ?>
                                                </td>
                                                <td >
                                                    <?php  
													if($Pettycash['recovery_from'] == '1')
														echo 'Cash in Hand';
													else
														echo 'Campus Recovery';
													?>
                                                </td>
                                                <td><?php  echo $Pettycash ['amount'] ?></td>
												<td style="font-weight: bolder;"><?php  echo pettycash_statement($Pettycash ['id']); ?></td>
												<td><?php  echo ($Pettycash ['amount']-$Pettycash['remaining_amount']); ?></td>
												<td><?php  echo $Pettycash ['first_name'].' '.$Pettycash ['last_name'];?></td>
												<td><?php  echo $Pettycash ['designation_name'];?></td>
                                                <td><?php  echo date('M') ?></td>
                                                <td>
                                                    <?php if ($Pettycash ['petty_status'] != '0'):
                                                        ?>

													<?php if(@$myAccess[0]['pettycash_funds_trasfer']==1 || $this->session->userdata('role')=='Admin'): ?>

														<a data-toggle="modal" data-id="<?php echo $i ?>" title="Add this item" class="open-FundsTrasferDialog btn btn-warning" href="#fundstrasfer">
															<i class="fa fa-dollar"> Funds Transfer</i>
														</a>
													<?php endif; ?>
													
													<?php if(@$myAccess[0]['change_pettycash']==1 || $this->session->userdata('role')=='Admin'): ?>

                                                        <?php if ($Pettycash ['remaining_amount'] <1): ?>
                                                            <br /><br />
                                                            <a class="btn green" href="<?php echo site_url();?>/pettycash/account_active/0/<?php echo $Pettycash['id'];?>" onclick="return confirm('Are you sure ?')">Inactive</a>


														<?php endif;?>
												

														<a data-toggle="modal" data-id="<?php echo $i ?>" title="Add this item" class="open-AddBookDialog btn btn-primary" href="#addcash">
															<i class="fa fa-edit"> Change Petty Cash Rule</i>
														</a>
													<?php endif;?>
												<?php endif;?>
												<?php if ($Pettycash ['petty_status'] == '0'): ?>

                                                        <a class="btn green" href="<?php echo site_url();?>/pettycash/account_active/1/<?php echo $Pettycash['id'];?>" onclick="return confirm('Are you sure ?')">Activate</a>


														<?php endif; ?>

                                                </td>
                                                <td>
                                                    <a data-toggle="modal" data-id="<?php echo $i ?>" title="Add this item"  href="<?php echo site_url().'/pettycash/pettycash_statement/'.$Pettycash['id'] ?>">
                                                        <i class="fa fa-eye"></i>
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
                    <h4 class="modal-title">Add Petty Cash Account</h4>
                </div>

                <div class="modal-body">
                    <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/pettycash/add">

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
                                <label class="col-md-3 control-label">Recovery From Account <span class="required">*</span></label>
                                <div class="col-md-9">
                                    <select name="recovery_from" id="recovery_from" class="form-control input-inline input-large recovery_from" required>
                                        
                                        <option value="1">Cash in Hand</option>
                                       
                                      
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
                                    <select name="designation_id" class="form-control input-inline input-large designation_id" required>

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
                            <div class="form-group">
                            <div class="form-group">
                                <label class="col-md-3 control-label">Opening Balance</label>
                                <div class="col-md-9">
                                    <input type="number"  name="opening_balance" class="form-control mobile"/>
                                </div>
                            </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">Max Amount Rule</label>
                                    <div class="col-md-9">
                                        <input type="number"  name="amount" class="form-control mobile"/>
                                    </div>
                                </div>

                            <div class="form-group">
                                <label class="col-md-3 control-label">REASON <span class="required">*</span></label>
                                <div class="col-md-9">

                                    <textarea class="form-control remarks" rows="3" name="reason" required></textarea>

                                </div>
                            </div>


                        </div>

                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-3 col-md-9">

                                    <button type="submit" class="btn red">Add Petty Cash</button>

                                </div>
                            </div>
                        </div>

                    </form>
                </div>

                <div class="modal-footer">
                <button type="button" class="btn default close_button" data-dismiss="modal">Close</button>
            </div>


    </div>

    <div class="modal fade" id="addtransaction" tabindex="-1"   data-width="600" >

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title">Add Transaction to this Account</h4>
        </div>
        <div class="modal-body">
            <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/pettycash/add_transaction">
                <div class="form-body">


                    <div class="form-group">
                        <label class="col-md-3 control-label">Transaction Type <span class="required">*</span></label>
                        <div class="col-md-9">
                            <select name="trans_type" id="trans_type" class="form-control input-inline input-large " required>

                                <option value="">SELECT Transaction</option>
                                <option value="D">Debit</option>
                                <option value="C">Credit</option>

                            </select>
                        </div>
                    </div>

                    <div class="form-group" id="fraccount">
                        <label class="col-md-3 control-label">Accounts <span class="required">*</span></label>
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
                        <label class="col-md-3 control-label">AMOUNT</label>
                        <div class="col-md-9">
                            <input type="number"  name="amount" id="amount" class="form-control mobile"/>

                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">REASON <span class="required">*</span></label>
                        <div class="col-md-9">

                            <textarea class="form-control remarks" rows="3" name="reason" required></textarea>

                        </div>
                    </div>


                </div>

                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-offset-3 col-md-9">

                            <input type="hidden"  name="user_id" id="upuser_id" value="" class="form-control mobile"/>
                            <input type="hidden"  name="campus_id" id="upcampus_id" value="" class="form-control mobile"/>
                            <button type="submit" class="btn red">Add Transaction</button>

                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn default close_button" data-dismiss="modal">Close</button>
        </div>
    </div>

    <div class="modal fade" id="fundstrasfer" tabindex="-1"   data-width="600" >

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title">Funds Transfer</h4>
        </div>
        <div class="modal-body">
            <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/pettycash/funds_transfer">
                <div class="form-body">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Select Transfer Type</label>
                        <div class="col-md-8 radio-list">
                            <label class="radio-inline">
                                <input type="radio" class="petty_account" name="petty_account" id="pettyaccount" value="0" checked >Petty Cash to Petty Cash</label>
                            <label class="radio-inline">
                                <input type="radio" class="user_account" name="petty_account" id="user_account" value="1">Petty Cash to Cash Account</label>
                        </div>
                    </div>

                    <div class="form-group" id="foraccount">
                        <label class="col-md-3 control-label">Petty Cash Accounts <span class="required">*</span></label>
                        <div class="col-md-9">
                            <select name="petty_account_id" id="petty_account_id" class="form-control input-inline input-large" required>
                                <option value="">Select Account To</option>
                                <?php
                                foreach($Pettycashs as $pettycashaccount):
                                    ?>
                                    <option value="<?php echo $pettycashaccount['id'];?>"><?php echo $pettycashaccount['first_name'].' '.$pettycashaccount['last_name'].' ( '.$pettycashaccount['campus_name'].' )';?></option>
                                <?php
                                endforeach;
                                ?>
                            </select>
                        </div>
                    </div>


                    <div class="form-group" id="cashaccounts" style="display: none;">
                        <label class="col-md-3 control-label">Accounts <span class="required">*</span></label>
                        <div class="col-md-9">
                            <select name="account_id" id="funds_account_id" class="form-control input-inline input-large " >
                                <option value="">Select Account To</option>
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
                        <label class="col-md-3 control-label">AMOUNT</label>
                        <div class="col-md-9">
                            <input type="number"  name="amount_transfer" id="amount_transfer" class="form-control mobile" required/>

                        </div>
                    </div>
					<div class="form-group">
						<label class="col-md-3 control-label">SELECT IMAGE<span class="required">*</span></label>
						<div class="col-md-9">
							<input type="file"  name="image" id="file" required/>

						</div>
					</div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">REASON <span class="required">*</span></label>
                        <div class="col-md-9">

                            <textarea class="form-control remarks" rows="3" name="trasfer_reason" required></textarea>

                        </div>
                    </div>


                </div>

                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-offset-3 col-md-9">
                            <input type="hidden"  name="amamount" id="amamount" value="" class="form-control mobile"/>
                            <input type="hidden"  name="from_account_funds" id="from_account_funds" value="" class="form-control mobile"/>
                            <button type="submit" class="btn red">Transfer Now</button>

                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn default close_button" data-dismiss="modal">Close</button>
        </div>
    </div>

    <div class="modal fade" id="addcash" tabindex="-1"   data-width="600" >

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">updated Petty Cash Rule</h4>
    </div>
    <div class="modal-body">
        <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/pettycash/update_cash">
            <div class="form-body">

                <div class="form-group">
                    <label class="col-md-3 control-label">AMOUNT</label>
                    <div class="col-md-9">
                        <input type="number"  name="amount" id="amount" class="form-control mobile"/>

                    </div>
                </div>

               </div>

            <div class="form-actions">
                <div class="row">
                    <div class="col-md-offset-3 col-md-9">

                        <input type="hidden"  name="user_id" id="uuser_id" value="" class="form-control mobile"/>
                        <input type="hidden"  name="campus_id" id="ucampus_id" value="" class="form-control mobile"/>
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
   	

<script>


</script>
	
	
	
	<!-- /.modal-dialog -->
