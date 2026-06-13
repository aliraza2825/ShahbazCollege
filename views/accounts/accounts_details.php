<?php
	$myAccess = checkUserAccess();
?>	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			All Teachers <small>Here you can find all teachers</small>
			</h3>-->
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
			<div class="row">
                <div class="col-md-12">
                    <a data-toggle="modal"  title="Add this item" style="float: right; margin: 15px" class="open-AddBookDialog btn btn-primary" href="#addaccounts">
                        <i class="fa fa-home"> Add Account</i>
                    </a>
                    <a data-toggle="modal"  title="Add this item" style="float: right; margin: 15px" class="open-AddBookDialog btn btn-primary" href="#fundtransfer">
                        <i class="fa fa-home"> Funds Transfer </i>
                    </a>
                    <div class="clearfix"></div>
                </div>
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> Cash Accounts
							</div>

						</div>

						<div class="portlet-body">
							<table class="table table-striped table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
                                <th>
									 ID
								</th>
								<th>
                                    Bank
                                </th>
                                <th>
                                    Title
                                </th>
								
								<th>
									 Account No
								</th>
                                <th>
                                    Account Limit
                                </th>
                                <th>
									 Amount
								</th>
								<th>
									 Shiftable Amount
								</th>
								<th>
									 Created Date
								</th>
                                <th>
                                     Taxable
                                </th>
                                <th>
                                     For Closing
                                </th>
                                <th>
                                     Last Account Updated
                                </th>
								 <th>
                                     Action
                                </th>

							</tr>
							</thead>
							<tbody>
                                <?php
                                    foreach($cash_accounts as $key=>$account):
                                ?>
                                    <tr class="odd gradeX">

                                        <td>
                                            <?php echo ($key+1)?>

                                        </td>
										<td>
                                            <?php echo substr($account['account_name'],0,stripos($account['account_name'],'('))?>
                                        </td>
										<td>
                                            <?php echo $account['account_title']?>
                                        </td>


                                        <td>
                                            <?php

												$bank = $account['account_name'];
												
												$bank = substr($bank,stripos($account['account_name'],'(') , stripos($account['account_name'],')'));
												$bank=str_replace('(',' ',$bank);
												$bank=str_replace(')',' ',$bank);
												
												echo $bank;

											?>
                                        </td>
                                        <td style="text-align: right; font-weight: bold">
                                            <?php echo $account['account_limit']?>
                                        </td>
                                        <td style="text-align: right; font-weight: bolder">
                                            <?php echo $account['amount']?>
                                        </td>
										<td style="text-align: right; font-weight: bolder">
                                            <?php 
											
											if($account['amount'] > $account['account_limit'])
												echo ($account['amount']-$account['account_limit']);
											else
												echo "0"	;										
												
												?>
                                        </td>
                                        <td>
                                            <?php echo $account['updated_at']?>
                                        </td>
                                        <td>
                                            <?php echo $account['taxable'] == "0" ? "NO" : "YES"?>
                                        </td>
                                        <td>
                                            <?php echo $account['for_closing'] == "0" ? "NO" : "YES"?>
                                        </td>
										<td>
                                            <a data-toggle="modal" data-id="<?php echo $key ?>"  title="Add this item" style="float: right; margin: 15px" class="open-Editacount btn btn-primary" href="#editaccounts">
                                                <i class="fa fa-home"> Edit</i>
                                            </a>
                                        </td>
										<td>
                                            <a title="View" style="float: right; margin: 15px" class="open-Editacount btn btn-primary" href="<?php echo site_url().'/accounts/cashaccountreport/'.$account['id'] ?>" target="_blank">
                                                <i class="fa fa-home"> View Statement</i>
                                            </a>
                                        </td>
                                    </tr>

                                <?php
                                    endforeach;
                                ?>
							</tbody>
							</table>
						</div>
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>

                <div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> Bank Accounts
							</div>

						</div>

						<div class="portlet-body">
							<table class="table table-striped table-bordered table-hover" id="sample_10">
							<thead>
							<tr>
                                <th>
									 ID
								</th>
								<th>
                                    Bank
                                </th>
                                <th>
                                    Title
                                </th>
								
								<th>
									 Account No
								</th>
                                <th>
                                    Account Limit
                                </th>
                                <th>
									 Amount
								</th>
								<th>
									 Shiftable Amount
								</th>
								<th>
									 Created Date
								</th>
                                <th>
                                     Taxable
                                </th>
                                <th>
                                     For Closing
                                </th>
                                <th>
                                     Last Account Updated
                                </th>
								 <th>
                                     Action
                                </th>

							</tr>
							</thead>
							<tbody>
                                <?php
                                    foreach($bank_accounts as $key=>$account):
                                ?>
                                    <tr class="odd gradeX">

                                        <td>
                                            <?php echo ($key+1)?>

                                        </td>
										<td>
                                            <?php echo substr($account['account_name'],0,stripos($account['account_name'],'('))?>
                                        </td>
										<td>
                                            <?php echo $account['account_title']?>
                                        </td>


                                        <td>
                                            <?php

												$bank = $account['account_name'];
												
												$bank = substr($bank,stripos($account['account_name'],'(') , stripos($account['account_name'],')'));
												$bank=str_replace('(',' ',$bank);
												$bank=str_replace(')',' ',$bank);
												
												echo $bank;

											?>
                                        </td>
                                        <td style="text-align: right; font-weight: bold">
                                            <?php echo $account['account_limit']?>
                                        </td>
                                        <td style="text-align: right; font-weight: bolder">
                                            <?php echo $account['amount']?>
                                        </td>
										<td style="text-align: right; font-weight: bolder">
                                            <?php 
											
											if($account['amount'] > $account['account_limit'])
												echo ($account['amount']-$account['account_limit']);
											else
												echo "0"	;										
												
												?>
                                        </td>
                                        <td>
                                            <?php echo $account['updated_at']?>
                                        </td>
                                        <td>
                                            <?php echo $account['taxable'] == "0" ? "NO" : "YES"?>
                                        </td>
                                        <td>
                                            <?php echo $account['for_closing'] == "0" ? "NO" : "YES"?>
                                        </td>
										<td>
                                            <a data-toggle="modal" data-id="<?php echo $key ?>"  title="Add this item" style="float: right; margin: 15px" class="open-Editacount btn btn-primary" href="#editaccounts">
                                                <i class="fa fa-home"> Edit</i>
                                            </a>
                                        </td>
										<td>
                                            <a title="View" style="float: right; margin: 15px" class="open-Editacount btn btn-primary" href="<?php echo site_url().'/accounts/cashaccountreport/'.$account['id'] ?>" target="_blank">
                                                <i class="fa fa-home"> View Statement</i>
                                            </a>
                                        </td>
                                    </tr>

                                <?php
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


<div class="modal fade" id="editaccounts" tabindex="-1"   data-width="600" >

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Add Account</h4>
    </div>
    <div class="modal-body">
        <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/accounts/edit">
            <div class="form-body">

                <div class="form-group">
                    <label class="col-md-3 control-label">Bank Name<span class="required">*</span></label>
                    <div class="col-md-9">

                        <textarea class="form-control remarks" rows="1" name="bank" id="edbank" ></textarea>

                    </div>
                </div>


                <div class="form-group">
                    <label class="col-md-3 control-label">Account Title<span class="required">*</span></label>
                    <div class="col-md-9">

                        <textarea class="form-control remarks" rows="1" name="title" id="edtitle" required></textarea>

                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label">Account Number<span class="required">*</span></label>
                    <div class="col-md-9">

                        <input type="number"  name="accountno" id="edaccountno"  class="form-control mobile" required/>

                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label">Account Type<span class="required">*</span></label>
                    <div class="col-md-9">
                        <select class="form-control" name="account_type" id="account_type" required>
                            <option value="">Select Account Type</option>
                            <option value="0">CASH</option>
                            <option value="1">BANK</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Account Taxable<span class="required">*</span></label>
                    <div class="col-md-9">
                        <select class="form-control" name="account_taxable" id="account_taxable" required>
                            <option value="">Select Account Type</option>
                            <option value="0">NO</option>
                            <option value="1">YES</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Account For Closing<span class="required">*</span></label>
                    <div class="col-md-9">
                        <select class="form-control" name="for_closing" id="account_closing" required>
                            <option value="">Select for Closing Type</option>
                            <option value="0">NO</option>
                            <option value="1">YES</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">AMOUNT LIMIT</label>
                    <div class="col-md-9">

                        <input type="number"  name="amount_limit" id="edamount_limit" class="form-control mobile" required/>

                    </div>
                </div>

            </div>

            <div class="form-actions">
                <div class="row">
                    <div class="col-md-offset-3 col-md-9">

                        <input name="daccount_id" id="daccount_id" type="hidden" value="">
                        <button type="submit" class="btn red">Update Account</button>

                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn default close_button" data-dismiss="modal">Close</button>
    </div>



</div>

<div class="modal fade" id="addaccounts" tabindex="-1"   data-width="600" >

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Add Account</h4>
    </div>
    <div class="modal-body">
        <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/accounts/add_account">
            <div class="form-body">
			
			
				<div class="form-group">
                    <label class="col-md-3 control-label">Bank Name<span class="required">*</span></label>
                    <div class="col-md-9">
                        <textarea class="form-control remarks" rows="1" name="bank" ></textarea>
                    </div>
                </div>
			

                <div class="form-group">
                    <label class="col-md-3 control-label">Account Title<span class="required">*</span></label>
                    <div class="col-md-9">
                        <textarea class="form-control remarks" rows="1" name="title" required></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label">Account Number<span class="required">*</span></label>
                    <div class="col-md-9">
                        <input type="number"  name="accountno" id="accountno" class="form-control mobile" required/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Account Type<span class="required">*</span></label>
                    <div class="col-md-9">
                        <select class="form-control" name="account_type" required>
                            <option value="">Select Account Type</option>
                            <option value="0">CASH</option>
                            <option value="1">BANK</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Account Taxable<span class="required">*</span></label>
                    <div class="col-md-9">
                        <select class="form-control" name="account_taxable" required>
                            <option value="">Select Account Type</option>
                            <option value="0">NO</option>
                            <option value="1">YES</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Account For Closing<span class="required">*</span></label>
                    <div class="col-md-9">
                        <select class="form-control" name="for_closing" required>
                            <option value="">Select for Closing Type</option>
                            <option value="0">NO</option>
                            <option value="1">YES</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">AMOUNT</label>
                    <div class="col-md-9">
                        <input type="number"  name="amount" id="amount" class="form-control mobile" required/>
                    </div>
                </div>


                <div class="form-group">
                    <label class="col-md-3 control-label">AMOUNT LIMIT</label>
                    <div class="col-md-9">
                        <input type="number"  name="amount_limit" id="amount_limit" class="form-control mobile" required/>
                    </div>
                </div>

            </div>

            <div class="form-actions">
                <div class="row">
                    <div class="col-md-offset-3 col-md-9">
                        <button type="submit" class="btn red">Add Account</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn default close_button" data-dismiss="modal">Close</button>
    </div>
</div>

<div class="modal fade" id="fundtransfer" tabindex="-1"   data-width="600" >

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Add Account</h4>
    </div>
    <div class="modal-body">
        <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/accounts/transfer_funds">
            <div class="form-body">


                <div class="form-group">
                    <label class="col-md-4 control-label">Select Transfer Type</label>
                    <div class="col-md-8 radio-list">
                        <label class="radio-inline">
                            <input type="radio" class="petty_account" name="petty_account" id="account" value="0" checked >Account to Account</label>
                        <label class="radio-inline">
                            <input type="radio" class="user_account" name="petty_account" id="petty_account" value="1">Account to Petty Cash</label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label">From Account<span class="required">*</span></label>
                    <div class="col-md-9">
                        <select class="form-control bank_details" name="from_account" id="from_account" required>
                            <option value="">SELECT ACCOUNT</option>
                            <?php
                            foreach($accounts as $key=>$account):
                                ?>
                                <option value="<?php echo $account['id'];?>"><?php echo $account['account_name'];?></option>
                            <?php
                            endforeach;
                            ?>
                        </select>

                    </div>
                </div>


                <div class="form-group" id="cashaccounts">
                    <label class="col-md-3 control-label">To Account<span class="required">*</span></label>
                    <div class="col-md-9">

                        <select class="form-control bank_details" name="to_account" id="funds_account_id" required>
                            <option value="">SELECT ACCOUNT</option>
                            <?php
                            foreach($accounts as $key=>$account):
                                ?>
                                <option value="<?php echo $account['id'];?>"><?php echo $account['account_name'];?></option>
                            <?php
                            endforeach;
                            ?>
                        </select>

                    </div>
                </div>

                <div class="form-group" id="foraccount" style="display: none;">
                    <label class="col-md-3 control-label">Petty Cash Accounts <span class="required">*</span></label>
                    <div class="col-md-9">
                        <select name="petty_account_id" id="petty_account_id" class="form-control input-inline input-large">
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

                <div class="form-group">
                    <label class="col-md-3 control-label">AMOUNT</label>
                    <div class="col-md-9">
                        <input type="number"  name="sentamount" id="sentamount" class="form-control mobile"/>

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

                        <input type="hidden"  id="hidenamount" value="0" class="form-control mobile"/>
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

<!-- END CONTENT -->