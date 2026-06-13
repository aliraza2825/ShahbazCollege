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
        <!-- BEGIN DASHBOARD STATS -->
        <!-- END DASHBOARD STATS -->
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
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet box grey-cascade">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i> Point of Sale
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form class="form-horizontal" id="pos_form" role="form" method="post" action="<?php echo site_url();?>/pos/invoice" target="_blank">
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Purchaser Type <span class="required">*</span></label>
                                    <div class="col-md-9 radio-list">
                                        <label class="radio-inline">
                                        <input type="radio" name="type" id="optionsRadios1" value="student" checked> Student </label>
                                        <label class="radio-inline">
                                        <input type="radio" name="type" id="optionsRadios2" value="other"> Other </label>
                                    </div>
                                </div>
                                <div class="row student">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select class="form-control input-inline input-large select2 purchaser_campus_id">
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
                                        <label class="col-md-3 control-label">Course <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select class="form-control input-inline input-large select2 purchaser_course_id">
                                                <option value="">SELECT COURSE</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Class <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select class="form-control input-inline input-large select2 purchaser_class_id">
                                                <option value="">SELECT CLASS</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Student <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="student_id" class="form-control input-inline input-large select2 purchaser_student_id" required>
                                                <option value="">SELECT STUDENT</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group other" style="display:none;">
                                    <label class="col-md-3 control-label">Name <span class="required">*</span></label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control input-inline input-large purchaser_name" name="name" placeholder="Enter Purchaser Name" value="">
                                        <span class="help-inline"></span>
                                    </div>
                                </div>
                                <div class="form-group other" style="display:none;">
                                    <label class="col-md-3 control-label">Phone <span class="required">*</span></label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control input-inline input-large purchaser_phone" name="phone" placeholder="Enter Purchaser Phone" value="">
                                        <span class="help-inline"></span>
                                    </div>
                                </div>

                                <hr />
                                <h3 class="text-center">SELECT PRODUCTS</h3>
                                <hr />
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Campus Name</th>
                                            <th>Room</th>
                                            <th>Subroom</th>
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                            <th>Sub Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="items">
                                        <!--<tr class="item">
                                            <td>
                                                <select name="campus_id[]" data-row-id="1" class="form-control input-inline input-medium select2 campus_id campus_id_1" required>
                                                    <option value="">SELECT CAMPUS</option>
                                                    <?php
                                                        //foreach($campuses as $campus):
                                                    ?>
                                                    <option value="<?php //echo $campus['campus_id'];?>"><?php //echo $campus['campus_name'];?></option>
                                                    <?php
                                                        //endforeach;
                                                    ?>
                                                </select>
                                            </td>
                                            <td>
                                                <select name="room_id[]" data-row-id="1" class="form-control input-inline input-medium select2 room_id room_id_1" required>
                                                    
                                                </select>
                                            </td>
                                            <td>
                                                <select name="subroom_id[]" data-row-id="1" class="form-control input-inline input-small select2 subroom_id subroom_id_1">
                                                <option value="0">SELECT SUBROOM</option>
                                                </select>
                                            </td>
                                            <td>
                                                <select name="product_name_id[]" data-row-id="1" class="form-control input-inline input-medium select2 product_name_id product_name_id_1" required>
                                                    <option value="">SELECT PRODUCT</option>
                                                    
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" data-row-id="1" class="form-control input-inline product_quantity input-small product_quantity_1" name="quantity[]" placeholder="Enter Quantity" value="" min="1" required>
                                            </td>
                                            <td>
                                                <span class="product_price product_price_1"></span><span class="note_1"></span>
                                            </td>
                                            <td>
                                                <span class="product_subtotal product_subtotal_1"></span>
                                            </td>
                                        </tr>-->
                                    </tbody>
                                </table>
                                <button type="button" class="btn green add_more_item"><i class="fa fa-plus"></i> Add Item</button>
                                <button type="button" class="btn red remove_item"><i class="fa fa-trash"></i> Remove Item</button>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <!--<button type="submit" class="btn green">Create Invoice</button>-->
                                        <button type="button" class="btn btn-lg green pay_now" data-amount="0" data-button-number="" data-toggle="modal" href="#payfee">
                                            <i class="fa fa-cloud-upload"></i> Pay Now
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- END EXAMPLE TABLE PORTLET-->
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 ">
                <!-- BEGIN SAMPLE FORM PORTLET-->
                <div class="portlet box green ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-search"></i> Search Saleable Available Product
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form class="form-horizontal" role="form" method="post" action="#">
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label col-md-3">Campus <span class="required">*</span></label>
                                            <div class="col-md-6">
                                                <select name="campus_id" class="form-control input-inline input-large select2 search_campus_id" required>
                                                    <option value="">SELECT CAMPUS</option>
                                                    <?php
                                                        foreach($campuses as $campus):
                                                    ?>
                                                    <option data-campus-id="<?php echo $campus['campus_id'];?>" value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name'];?></option>
                                                    <?php
                                                        endforeach;
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label col-md-3">Product <span class="required">*</span></label>
                                            <div class="col-md-6">
                                                <select name="product_name_id" class="form-control input-inline input-large select2 search_product_name_id" required>
                                                    <option value="">SELECT PRODUCT</option>
                                                    <?php
                                                        foreach($product_names as $product_name):
                                                    ?>
                                                    <option data-product-name-id="<?php echo $product_name['product_name_id'];?>" value="<?php echo $product_name['product_name_id'];?>"><?php echo $product_name['product_name'];?></option>
                                                    <?php
                                                        endforeach;
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Campus</th>
                                                    <th>Room</th>
                                                    <th>Subroom</th>
                                                    <th>Product</th>
                                                    <th>Quantity</th>
                                                    <th>Sale Price</th>
                                                </tr>
                                            </thead>
                                            <tbody class="products">
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- END SAMPLE FORM PORTLET-->
            </div>
        </div>


        <div class="row">
            <div class="col-md-12 ">
                <!-- BEGIN SAMPLE FORM PORTLET-->
                <div class="portlet box green ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i> Product Selling History
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form class="form-horizontal" role="form" method="post" action="#">
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label col-md-3">From </label>
                                            <div class="col-md-3">
                                                <div class="input-group input-medium date date-picker" data-date="<?php echo $from_date;?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                    <input type="text" name="from_date" class="form-control from_date" value="<?php echo $from_date;?>" readonly>
                                                    <span class="input-group-btn">
                                                    <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label col-md-3">To</label>
                                            <div class="col-md-3">
                                                <div class="input-group input-medium date date-picker" data-date="<?php echo $to_date;?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                    <input type="text" name="to_date" class="form-control to_date" value="<?php echo $to_date;?>" readonly>
                                                    <span class="input-group-btn">
                                                    <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 text-center">
                                        <button type="button" class="btn green check_sold_products">Check</button>
                                    </div>
                                    
                                    <div class="col-md-12">
                                    <br /><br /><br />
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Invoice #</t>
                                                    <th>Sold Date</t>
                                                    <th>Product</th>
                                                    <th>Campus / Room / Subroom</th>
                                                    <th>Sale Amount</th>
                                                    <th>Return Amount</th>
                                                    <th>Sold To</th>
                                                    <th>Sold By</th>
                                                    <th>View</th>
                                                </tr>
                                            </thead>
                                            <tbody class="sold_products">
                                                <?php
                                                    foreach($sold_products as $sold_product):
                                                        $this->db->select('*');
                                                        $this->db->from('products');
                                                        $this->db->join('product_names','product_names.product_name_id=products.product_name_id','inner');
                                                        $this->db->where('invoice_no',$sold_product['invoice_no']);
                                                        $products = $this->db->get()->result_array();

                                                        $product_names = '';
                                                        $sale_amount = 0;
                                                        $return_amount = 0;
                                                        foreach($products as $product)
                                                        {
                                                            if($product['return_status']==0)
                                                            {
                                                                $product_names .= $product['product_name'].' (Rs '.$product['sold_amount'].')<br />';
                                                                $sale_amount += $product['sold_amount'];
                                                            }
                                                            else
                                                            {
                                                                $product_names .= $product['product_name'].' (Rs '.$product['sold_amount'].') <span class="label label-sm label-danger">Returned</span><br />';
                                                                $return_amount += $product['sold_amount'];
                                                            }
                                                        }
                                                ?>
                                                <tr>
                                                    <td>
                                                        <?php echo $sold_product['invoice_no'];?>
                                                    </td>
                                                    <td>
                                                        <?php echo $sold_product['sold_date'];?>
                                                    </td>
                                                    <td>
                                                        <?php echo $product_names;?>
                                                    </td>
                                                    <td>
                                                        <?php echo $sold_product['campus_name'];?>
                                                        <br />
                                                        <?php echo $sold_product['room_name'];?>
                                                        <br />
                                                        <?php echo $sold_product['subroom_name'];?>
                                                    </td>
                                                    <td>
                                                        <?php echo 'Rs '.$sale_amount;?>
                                                    </td>
                                                    <td>
                                                        <?php echo 'Rs '.$return_amount;?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                            if($sold_product['student_id']==0)
                                                            {
                                                                echo 'Name : '.$sold_product['purchaser_name'].'<br />Phone : '.$sold_product['purchaser_phone'];
                                                            }
                                                            else
                                                            {
                                                                echo 'Student Name : '.$sold_product['purchaser_name'].'<br />Student Phone : '.$sold_product['purchaser_phone'];
                                                            }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $sold_product['first_name'].' '.$sold_product['last_name'];?>
                                                    </td>
                                                    <td>
                                                        <a data-toggle="modal" href="#view_order" class="btn red view_invoice" data-invoice-no="<?php echo $sold_product['invoice_no'];?>"><i class="fa fa-sign-out"></i> Return Product?</a>
                                                    </td>
                                                </tr>
                                                <?php
                                                    endforeach;
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- END SAMPLE FORM PORTLET-->
            </div>
        </div>
        <!-- END PAGE CONTENT-->
    </div>
</div>


<div class="modal fade" id="view_order" tabindex="-1" role="dialog"   data-width="1200" >
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Invoice # <span class="invoice_no"></span></h4>
    </div>
    <div class="modal-body">
        <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/pos/returnItems">
            <div class="form-body" style="margin-top: 20px;">
                <div class="portlet-body">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>
                                Sr No.
                            </th>
                            <th>
                                Product Name
                            </th>
                            <th>
                                Product Quantity
                            </th>
                            <th>
                                Sold Price
                            </th>
                            <th>
                                Return Quantity
                            </th>
                        </tr>
                        </thead>
                        <tbody class="invoice_data">

                        </tbody>
                    </table>
                </div>
            </div>
            <input type="hidden" name="invoice_no" class="invoice" value="" />
            <button type="submit" class="btn blue">Return Products</button>
        </form>
    </div>
</div>

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
                                foreach($fee_campuses as $campus):
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
                            <input type="number" min="1" class="form-control input-inline input-medium" name="actual_amount" id="actual_amount" placeholder="Enter student paid amount" value="" readonly required>
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
                        <input type="hidden" name="fee_amount" id="fee_amount" value="" />
                        <input type="hidden" name="data" id="cart" value="" />
                        <input type="hidden" name="invoice" value="<?php echo $invoice_no = $campus_code.'-'.strtotime(date('Y-m-d H:i:s'));?>" />
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

        $(".select2").select2();

        $('.view_invoice').live('click',function(){
            var invoice_no = $(this).data('invoice-no');
            $('.invoice_no').html(invoice_no);
            $('.invoice').val(invoice_no);

            jQuery.ajax({
                type: "post",
                async: false,
                url: '<?php echo site_url()?>/pos/getOrderDetails',
                data: {
                    invoice_no : invoice_no
                },
                success: function(data) {
                    $('.invoice_data').html(data);
                }
            });
        });
        
        jQuery('.pay_now').live('click',function(){
            var amount = jQuery(this).data('amount');
            jQuery('#fee_amount').val(amount);
            jQuery('#actual_amount').val(amount);
            
            var formData = {};

            $('#pos_form').serializeArray().forEach(function(field) {
              if (field.value.trim() !== '') {
                formData[field.name] = field.value;
              }
            });
        
            jQuery('#cart').val(JSON.stringify(formData, null, 2));
        });
        
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

<!-- END CONTENT -->
	
	
