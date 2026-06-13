
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Add Class <small>add your desire class</small>
			</h3>-->
			<!-- END PAGE HEADER-->
			<!-- BEGIN PAGE CONTENT-->
            <?php
                $count = 0;

            if(@$this->session->userdata('message')):?>
                <div class="alert alert-success">
                    <button class="close" data-close="alert"></button>
                    <span>
                    <?php echo $this->session->userdata('message');?> </span>
                </div>
            <?php endif;?>
            <div class="form-horizontal" id='DivIdToPrint'>
			<div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title" style="text-align: center">
							<div class="caption" style="text-align: center; width: 100%">
								<i style="text-align: center"></i> Purchase Order # <?php echo "PO - ".$purchase_order->id ?>
							</div>
						</div>
						<div class="portlet-body form">

                            <div class="form-group col-md-6">
                                <label class="col-md-6 control-label" style="font-weight: bolder; text-align: left;">Purchaser </label>
                                <label class="col-md-6 control-label" style="text-align: left"><?php echo $purchase_order->first_name.' '.$purchase_order->last_name.' ( '.$purchase_order->designation_name.' )'?> </label>

                            </div>
                            <div class="form-group col-md-6">
                                <label class="col-md-6 control-label" style="font-weight: bolder; text-align: left;">Vendor Name </label>
                                <label class="col-md-6 control-label" style="text-align: left"><?php echo $purchase_order->vendor_name?> </label>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="col-md-6 control-label" style="font-weight: bolder; text-align: left;">Vendor Address </label>
                                <label class="col-md-6 control-label" style="text-align: left"><?php echo $purchase_order->vendor_address?></label>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="col-md-6 control-label" style="font-weight: bolder; text-align: left;">description </label>
                                <label class="col-md-6 control-label" style="text-align: left"><?php echo $purchase_order->description?></label>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="col-md-6 control-label" style="font-weight: bolder; text-align: left;">Order Amount </label>
                                <label class="col-md-6 control-label" style="text-align: left"><?php echo $purchase_order->total_amount?></label>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="col-md-6 control-label" style="font-weight: bolder; text-align: left;">Purchased Amount </label>
                                <label class="col-md-6 control-label" style="text-align: left"><?php echo $purchase_order->purchased_amount?></label>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="col-md-6 control-label" style="font-weight: bolder; text-align: left;">Paid Amount </label>
                                <label class="col-md-6 control-label" style="font-weight: bolder; text-align: left"> PKR <?php echo $purchase_order->paid_amount?></label>
                            </div>

						</div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>

            <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i>Products Detail
							</div>
						</div>
						<div class="portlet-body">
							<table class="table table-striped table-bordered table-hover" id="product_table">
							<thead>
							<tr>
								<th class="hidden">
                                	 ID
                                </th>
								<th>
									 Product Name
								</th>
								<th>
									 Quantity
								</th>
								<th>
									 Price Per Unit
								</th>
                                <th>
                                    Total Price
                                </th>

							</tr>
							</thead>
							<tbody>
                            <?php
                                $data = $this->db->join('product_names','product_names.product_name_id = po_products.item_id')->get_where('po_products',array('po_products.po_id'=>$purchase_order->id))->result_array();

                                foreach ($data as $key=>$prods):
                                    $count++;  ?>

                                <tr id="tr<?php echo $count; ?>">
                                    <td class="hidden"><?php echo $count; ?></td>
                                    <td>

                                           <?php echo  $prods['product_name']; ?>

                                        </select>
                                    </td>
                                    <td>
                                        <label  class="form-control input-inline input-large"  ><?php echo $prods['quantity'] ?> </label>
                                    </td>
                                    <td>
                                        <label  class="form-control input-inline input-large" ><?php echo $prods['per_item_price'] ?></label>
                                    </td>
                                    <td>
                                        <label  class="form-control input-inline input-large" ><?php echo $prods['total_price'] ?></label>
                                    </td>

                                </tr>


                            <?php endforeach;

                            $count++;
                            ?>
							</tbody>
							</table>
						</div>
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->

                <?php

                $expenses = $this->db->join("campuses","campuses.campus_id = expenses.campus_id")->get_where("expenses","po_no = '".$purchase_order->id."'")->result_array();
                if(count($expenses)>0 ):
                    ?>
                    <div class="portlet-body">
                        <div class="alert alert-success">
                            <p>Total expense </p>
                        </div>
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                            <tr>
                                <th class="hidden">
                                    hidden
                                </th>
                                <th>
                                    Campus
                                </th>
                                <th>
                                    Title
                                </th>
                                <th>
                                    Purpose
                                </th>
                                <th>
                                    Amount
                                </th>
                                <th>
                                    Date
                                </th>
                                <th>
                                    Receipt
                                </th>
                                <th>
                                    Add By
                                </th>
                                <th>
                                    Last Edit
                                </th>
                                <th>
                                    Status
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $i = 0;
                            foreach($expenses as $expense):
                                ?>
                                <tr class="odd gradeX">
                                    <td class="hidden">
                                        <?php echo $i;?>
                                    </td>
                                    <td>
                                        <?php echo $expense['campus_name']?>
                                    </td>
                                    <td>
                                        <?php echo $expense['title']?>
                                        <?php
                                        if($expense['expense_category_id']==1):
                                            ?>
                                            <br />
                                            Rickshaw Number : <?php echo $expense['rickshaw_number'];?>
                                            <br />
                                            Rickshaw Driver No : <?php echo $expense['driver_phone'];?>
                                        <?php
                                        endif;
                                        ?>
                                        <?php
                                        if($expense['expense_category_id']==13 && $expense['student_id']!=NULL):
                                            $student_data = $this->db->get_where('students',array('student_id'=>$expense['student_id']))->result_array();
                                            ?>
                                            Name : <?php echo $student_data[0]['first_name'];?> <?php echo $student_data[0]['last_name'];?> (<?php echo $student_data[0]['cnic'];?>)
                                            <br />
                                            Class : <?php echo $expense['class'];?> Year
                                            <br />
                                            Exam Number : <?php echo $expense['council_exam_no'];?>
                                        <?php
                                        endif;
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo $expense['purpose']?>
                                    </td>
                                    <td>
                                        <?php echo $expense['amount']?>
                                    </td>
                                    <td>
                                        <?php echo $expense['date']?>
                                    </td>
                                    <td>
                                        <?php
                                        if($expense['image']!='' && $expense['online_image']==''):
                                            ?>
                                            <a href="<?php echo base_url().'uploads/'.$expense['image'];?>" target="_blank">
                                                <button type="button" class="btn btn-default"><i class="fa fa-image"></i> Image</button>
                                            </a>
                                        <?php
                                        endif;
                                        ?>
                                        <?php
                                        if($expense['image']!='' && $expense['online_image']!=''):
                                            ?>
                                            <a href="<?php echo $expense['online_image'];?>" target="_blank">
                                                <button type="button" class="btn btn-default"><i class="fa fa-image"></i> Image</button>
                                            </a>
                                        <?php
                                        endif;
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo $expense['add_by']?>
                                    </td>
                                    <td>
                                        <?php echo $expense['last_edit']?>
                                    </td>

                                    <td> <?php
                                        if ($expense['approved_status'] == '0')
                                            echo  "<a data-toggle='modal' data-id='$i' class='btn btn-primary' style='width: 100px'>PENDING</a>";

                                        elseif ($expense['approved_status'] == '2')
                                            echo  "<a data-toggle='modal' class='btn red'  style='width: 100px'  >REJECTED</a>";

                                        elseif ($expense['approved_status'] == '1')
                                        {
                                            echo " <a data-toggle='modal' class='btn green' style='width: 100px' >APPROVED</a>  ";
                                        }
                                        else
                                            echo " <a data-toggle='modal' class='btn yellow' style='width: 100px' >Reversed </a>";
                                        ?></td>

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

            <div class="form-actions">
                <div class="row">
                    <div class="col-md-offset-3 col-md-9">
                        <button type="button" onclick='printDiv();' class="btn green">Print</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END CONTENT -->
    <script>
        function printDiv(){
            var printContents = document.getElementById("DivIdToPrint").innerHTML;
            var originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
        }
    </script>