<?php
$myAccess = checkUserAccess();
?>
<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
			<h3 class="page-title">
			Invoice
			</h3>
			<!-- END PAGE HEADER-->
			<!-- BEGIN PAGE CONTENT-->
			<div class="invoice">
				<div class="row invoice-logo">
					<div class="col-xs-6 invoice-logo-space">
						<!--<img src="<?php echo base_url();?>assets/admin/pages/media/invoice/walmart.png" class="img-responsive" alt=""/>-->
                        <p style="text-align:left;"><?php echo $campus_name;?></p>
					</div>
					<div class="col-xs-6">
						<p>
							Invoice # <?php echo $invoice_no = $campus_code.'-'.strtotime(date('Y-m-d H:i:s'));?>
                            <span class="muted"><?php echo date('d F, Y');?></span>
						</p>
					</div>
				</div>
				<hr/>
				<div class="row">
					<div class="col-xs-6">
						<h3>Customer Details:</h3>
						<ul class="list-unstyled">
							<li>
								 <?php echo $name;?>
							</li>
							<li>
								 <?php echo $phone;?>
							</li>
							<li>
								 <?php echo $address;?>
							</li>
						</ul>
					</div>
					<div class="col-xs-6 invoice-payment">
						<h3>Payment Details:</h3>
						<ul class="list-unstyled">
							<li>
								<strong>Payment:</strong> Cash
							</li>
							<li>
								<strong>Officer Name:</strong> <?php echo $this->session->userdata('name');?>
							</li>
							<li>
								<strong>Campus:</strong> <?php echo $campus_name;?>
							</li>
						</ul>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12">
						<table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>
                                        #
                                    </th>
                                    <th>
                                        Item
                                    </th>
                                    <th class="hidden-480">
                                        Unit Price (PKR)
                                    </th>
                                    <th class="hidden-480">
                                        Quantity
                                    </th>
                                    <th>
                                        Total (PKR)
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $i=1;
                                    $total=0;
                                    foreach($products as $product):
                                ?>
                                <tr>
                                    <td>
                                        <?php echo $i;?>
                                    </td>
                                    <td>
                                        <?php echo $product['product_name'];?>
                                    </td>
                                    <td class="hidden-480">
                                        <?php echo $product['product_unit_price'];?>
                                    </td>
                                    <td class="hidden-480">
                                        <?php echo $product['product_quantity'];?>
                                    </td>
                                    <td>
                                        <?php echo $total_price = $product['product_unit_price']*$product['product_quantity'];?>
                                    </td>
                                </tr>
                                <?php
                                    $total += $total_price;
                                    $i++;
                                    endforeach;
                                ?>
                            </tbody>
						</table>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-4">
						<div class="well">
							<address>
                                <strong><?php echo $campus_name;?></strong><br/>
                                <?php echo $campus_address;?><br />
                                <abbr title="Phone">P:</abbr> <?php echo $campus_phone;?></abbr>
                            </address>
						</div>
					</div>
					<div class="col-xs-8 invoice-block">
					    <!--<form method="post" action="<?php echo site_url();?>/pos/submit_invoice">
                            <ul class="list-unstyled amounts">
                                <li>
                                    <strong>Sub - Total Amount:</strong> PKR <?php echo $total;?>
                                </li>
                                <li>
                                    <strong>VAT:</strong> PKR 0
                                </li>
                                <li>
                                    <strong>Grand Total:</strong> PKR <?php echo $total;?>
                                </li>
                            </ul>
                            <br/>-->
                            <a class="btn btn-lg blue hidden-print margin-bottom-5" onclick="javascript:window.print();">
                            Print <i class="fa fa-print"></i>
                            </a>
                            
                            <button type="button" class="btn btn-lg green pay_now" data-amount="<?php echo $total;?>" data-button-number="" data-toggle="modal" href="#payfee">
                                <i class="fa fa-cloud-upload"></i> Pay Now
                            </button>
                            <!--
                            <input type="hidden" name="data" value='<?php echo json_encode($products);?>' />
                            <input type="hidden" name="invoice" value='<?php echo $invoice_no;?>' />
                            <input type="hidden" name="student_id" value='<?php echo $student_id;?>' />
                            <input type="hidden" name="purchaser_name" value='<?php echo $name;?>' />
                            <input type="hidden" name="purchaser_phone" value='<?php echo $phone;?>' />
                            <button type="submit" class="btn btn-lg green hidden-print margin-bottom-5">
                                <i class="fa fa-money"></i> Pay By Cash
                            </button>
                            <button type="button" class="btn btn-lg green hidden-print margin-bottom-5">
                                <i class="fa fa-bank"></i> Pay By Bank
                            </button>
                        </form>-->
					</div>
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->
	
<div id="payfee" class="modal fade" tabindex="-1" data-width="760">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Fee Submission</h4>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <form action="<?php echo site_url().'/pos/pay_invoice';?>" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="col-md-4 control-label">Fee Pay Through <span class="required">*</span></label>
                        <div class="col-md-8 radio-list">
                            <?php if ($myAccess[0]['fee_by_bank'] || $this->session->userdata('role')=='Admin'): ?>
                            <label class="radio-inline">
                                <input type="radio" class="submit_in fee_pay_through" name="fee_pay_through" id="optionsRadios5" value="bank" checked /> Bank </label>
                            <?php endif; ?>
                            <?php if ($myAccess[0]['fee_by_cash'] || $this->session->userdata('role')=='Admin'): ?>
                            <label class="radio-inline">
                                <input type="radio"  class="submit_in fee_pay_through" name="fee_pay_through" id="optionsRadios4" value="college" /> College </label>
                            <?php endif; ?>
                            <?php if ($myAccess[0]['fee_by_paypro'] || $this->session->userdata('role')=='Admin'): ?>
                            <label class="radio-inline">
                                <input type="radio"  class="submit_in fee_pay_through" name="fee_pay_through" id="optionsRadios6" value="pay_pro" /> PayPro </label>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="clearfix"></div>
                    <br />
                    <div class="form-group">
                        <label class="col-md-4 control-label">Paid at Campus <span class="required">*</span></label>
                        <div class="col-md-8">
                            <select class="form-control submitted_fee_campus_id" name="submitted_fee_campus_id" required>
                                <option value="">SELECT CAMPUS</option>
                                <?php
                                foreach($campuses as $campus):
                                ?>
                                    <option value="<?php echo $campus['campus_id'];?>" <?php if($campus['campus_id']==$this->session->userdata('user_campus_id')){echo 'selected';}?>><?php echo $campus['campus_name'];?></option>
                                <?php
                                endforeach;
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <br />

					<div class="form-group">
                        <label class="col-md-4 control-label">Paid Date <span class="required">*</span></label>
                        <div class="col-md-8">
                            <!-- CLASS date date-picker-->
                            <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d');?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                <input type="text" name="paid_date"  id="paid_date" class="form-control paid_date" value="<?php echo date('Y-m-d');?>" required readonly>
                                <span class="input-group-btn">
                                    <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="clearfix"></div>
                    <div class="form-group college" style="display:none;">
                        <label class="col-md-4 control-label">Fee Submit Type <span class="required">*</span></label>
                        <div class="col-md-8 radio-list">
                            <label class="radio-inline">
                                <input type="radio" class="pay_by" name="fee_submit_type" id="optionsRadios5" value="computer_challan" checked /> Pay By Computer Challan </label>

                            <label class="radio-inline">
                                <input type="radio" class="pay_by" name="fee_submit_type" id="optionsRadios4" value="receipt_book" /> Pay By Receipt Book </label>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="receipt_book" style="display:none;">

                        <div class="clearfix"></div>
                        <br />
                        <div class="form-group">
                            <label class="col-md-4 control-label">Book No. <span class="required">*</span></label>
                            <div class="col-md-8">
                                <input type="number" min="1" class="form-control input-inline input-medium book_no" name="book_no" placeholder="Enter Receipt Book Number" value="">
                                <span class="help-inline"></span>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <br />
                        <div class="form-group">
                            <label class="col-md-4 control-label">Receipt No. <span class="required">*</span></label>
                            <div class="col-md-8">
                                <input type="number" min="1" class="form-control input-inline input-medium receipt_no" name="receipt_no" placeholder="Enter Receipt Number" value="">
                                <span class="help-inline"></span>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="clearfix"></div>
                    <br />
                    <div class="form-group challan">
                        <label class="col-md-4 control-label">Challan Image <span class="required">*</span></label>
                        <div class="col-md-8">
                            <input class="scan_challan" type="file" name="scan_challan" value="" required />
                            <span class="help-inline"></span>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">Application Image</label>
                        <div class="col-md-8">
                            <input type="file" class="application" name="fine_application" value="" />
                            <span class="help-inline"></span>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    
                    
                    <div class="form-group">
                        <label class="col-md-4 control-label">Paid Amount <span class="required">*</span></label>
                        <div class="col-md-8">
                            <input type="number" min="1" class="form-control input-inline input-medium" name="actual_amount" id="actual_amount" placeholder="Enter student paid amount" value="<?php echo $total;?>" readonly required>
                            <span class="help-inline"></span>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    
                    <div class="bank">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Select Bank <span class="required">*</span></label>
                            <div class="col-md-8">
                                <select class="form-control bank_details" id="select_bank" name="bank_details" required>
                                    <option value="">SELECT BANK</option>
                                   <?php
                                    $count = count($account_numbers);
                                    for($a=0;$a<$count;$a++):
                                        ?>
                                        <option value="<?php echo $account_numbers[$a]['account_name'].'';?>"><?php echo $account_numbers[$a]['account_name'].'';?></option>
                                    <?php
                                    endfor;
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <br />
                        <div class="form-group">
                            <label class="col-md-4 control-label">Bank Challan / TID No.</label>
                            <div class="col-md-8">
                                <div class="text-danger" id="error_verify" >Soch Samajh ker Bank or Tid # likhein Bhaari jurmana ada karna parr sakta hai</div>
                                <input type="text" class="form-control input-inline input-medium" id="tid_no" minlength="5" name="tid_no" placeholder="Enter TID Number" value="" >
                                <button name="verify" class="btn btn-primary" type="button"  id="verify_now">Verify Now</button>
                                <div class="text-danger" id="error_verify" ></div>
                                <span class="help-inline"></span>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="col-md-4"></div>
                    <div class="col-md-8">
                        <br />
                        <input type="hidden" name="fee_amount" id="fee_amount" value="<?php echo $total;?>" />
                        <button class="btn green" id="payfeebtn" >Pay Fee</button>
                    </div>
                </form>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default">Close</button>
    </div>
</div>

<script>
    document.addEventListener( "DOMContentLoaded", function(){
        jQuery(document).ready(function(){
            jQuery('#payfeebtn').hide();
            jQuery('.receipt_book').hide();
            jQuery('.update').click(function(){
                var clas = jQuery(this).data('class');
                jQuery('.update_section').hide();
                jQuery('.'+clas).show();
                jQuery('.'+clas).show();
            });
            jQuery('.submit_in').change(function(){
                var submit_in = jQuery(this).val();
                
                //CHECKING
                var pay_by = jQuery('.pay_by').val();
                if(pay_by=='computer_challan' && submit_in=='college')
                {
                    jQuery('.scan_challan').removeAttr('required');
                    jQuery('.challan').hide();
                }
                else
                {
                    jQuery('.scan_challan').attr('required','required');
                    jQuery('.challan').show();
                }
                
                if(submit_in=='college')
                {
                    jQuery('.college').show();
                    jQuery('.bank').hide();
                    jQuery('.bank_details').removeAttr('required');
                    jQuery('#payfeebtn').show();
                }
                else if(submit_in=='pay_pro')
                {
                    jQuery('.college').hide();
                    jQuery('.bank').hide();
                    jQuery('.bank_details').removeAttr('required');
                    jQuery('.scan_challan').removeAttr('required');
                    jQuery('.challan').hide();
                    jQuery('#payfeebtn').show();
                }
                else
                {
                    jQuery('.bank').show();
                    jQuery('.college').hide();
                    jQuery('.receipt_book').hide();
                    jQuery('.bank_details').attr('required','required');
                    jQuery('#payfeebtn').hide();
                }
            });
            jQuery('.pay_by').change(function(){
                var pay_by = jQuery(this).val();
                if(pay_by=='receipt_book')
                {
                    jQuery('.receipt_book').show();
                    jQuery('.submitted_fee_campus_id').attr('required','required');
                    jQuery('.book_no').attr('required','required');
                    jQuery('.receipt_no').attr('required','required');
                }
                else
                {
                    jQuery('.receipt_book').hide();
                    jQuery('.submitted_fee_campus_id').removeAttr('required');
                    jQuery('.book_no').removeAttr('required');
                    jQuery('.receipt_no').removeAttr('required');
                }
                //CHECKING
                if(pay_by=='computer_challan')
                {
                    jQuery('.scan_challan').removeAttr('required');
                    jQuery('.challan').hide();
                    //jQuery('.application').hide();
                }
                else
                {
                    jQuery('.scan_challan').attr('required','required');
                    jQuery('.challan').show();
                    //jQuery('.application').show();
                }
            });
            jQuery('.pay_now').click(function(){
                var amount = jQuery(this).data('amount');
                jQuery('#fee_amount').val(amount);
                jQuery('#actual_amount').val(amount);
            });
            
            $('#verify_now').click(function () {

                var tid = $('#tid_no').val();
                var bank = $('#select_bank').val();
                var paid_date = $('#paid_date').val();
                var amount = $('#actual_amount').val();
    
                if (bank.length < 6) {
                    $('#error_verify').text('Select Bank');
                } else {
    
                    if (tid.length < 4) {
                        $('#error_verify').text('Minimum Tid Length will be 4');
                    }
                    else {
    
                        $.ajax({
                            type: "post",
                            async: false,
                            url: '<?php echo site_url()?>/students/verify_fee',
                            data: {
                                tid: tid,
                                bank: bank,
                                amount: amount,
                                paid_date: paid_date
                            },
                            success: function (data) {
                                data = $.trim(data);
                                if (data === 'success') {
                                    $('#payfeebtn').show();
                                    $('#error_verify').html('');
                                    $('#error_verify').html(data);
                                } else {
                                    $('#error_verify').html(data);
                                    $('#payfeebtn').hide();
                                }
                            }
                        });
                    }
                }
            });
        });
    }, false );
</script>