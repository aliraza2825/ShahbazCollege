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
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet box grey-cascade">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i>All Expenses
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/expenses/all_expenses">
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label col-md-3">From Date <span class="required">*</span></label>
                                            <div class="col-md-3">
                                                <div class="input-group input-medium date date-picker" data-date="<?php if(@$this->input->post('from_date')){echo $this->input->post('from_date');}else{echo date('Y-m-d');}?>" data-date-format="yyyy-mm-dd" <?php if(@$myAccess[0]['expense_no_of_days']!=1 && $this->session->userdata('role') != 'Admin'): ?> data-date-start-date="-45d"  data-date-end-date="0d" <?php endif;?> data-date-viewmode="years">
                                                    <input type="text" name="from_date" class="form-control" value="<?php if(@$this->input->post('from_date')){echo $this->input->post('from_date');}else{echo date('Y-m-d');}?>" readonly>
                                                    <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label col-md-3">To Date <span class="required">*</span></label>
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
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <button type="submit" class="btn green">Check Expense</button>
                                    </div>
                                </div>
                            </div>
                        </form>



                        <div class="row" style="margin-bottom:15px; padding:15px; margin-left: 0px; margin-right: 0px; border: 1px solid black">

                            <div class="col-md-2">

                                <form method="post" action="<?php echo site_url();?>/expenses/all_expenses">
                                    <input type="hidden"  name="from_date" value="<?php echo $from_date ?>" />
                                    <input type="hidden"  name="to_date" value="<?php echo $to_date ?>"/>

                                    <input type="submit" style="width: 100%;" name="setype" class="btn btn-success" value="Pending" />
                                    <span class="badge" id="bcount" style="background-color: red; font-weight: bold"><?php echo $pending ?></span>
                                </form>

                            </div>

                            <div class="col-md-2">
                                <form method="post" action="<?php echo site_url();?>/expenses/all_expenses">
                                    <input type="hidden"  name="from_date" value="<?php echo $from_date ?>" />
                                    <input type="hidden"  name="to_date" value="<?php echo $to_date ?>"/>
                                    <input type="submit" style="width: 100%;" class="btn blue" name="setype" value="Approved" />
                                    <span class="badge" id="bcount" style="background-color: red; font-weight: bold"><?php echo $approved ?></span>
                                </form>
                            </div>

                            <div class="col-md-2">
                                <form method="post" action="<?php echo site_url();?>/expenses/all_expenses">
                                    <input type="hidden"  name="from_date" value="<?php echo $from_date ?>" />
                                    <input type="hidden"  name="to_date" value="<?php echo $to_date ?>"/>
                                    <input type="submit" style="width: 100%;" class="btn red" name="setype" value="Rejected" />
                                    <span class="badge" id="bcount" style="background-color: red; font-weight: bold"><?php echo $rejected ?></span>
                                </form>
                            </div>

                            <div class="col-md-2">
                                <form method="post" action="<?php echo site_url();?>/expenses/all_expenses">
                                    <input type="hidden"  name="from_date" value="<?php echo $from_date ?>" />
                                    <input type="hidden"  name="to_date" value="<?php echo $to_date ?>"/>

                                    <input type="submit" style="width: 100%;" class="btn purple" name="setype" value="Reversed" />
                                    <span class="badge" id="bcount" style="background-color: red; font-weight: bold"><?php echo $reversed ?></span>
                                </form>
                            </div>

                        </div>

                    </div>


                    <?php
                    if(count($expenses)>0 ):
                        ?>
                        <div class="portlet-body">
                            <div class="alert alert-success">
                                <p>Total expense from <?php echo date('d F, Y',strtotime($this->input->post('from_date')));?> to <?php echo date('d F, Y',strtotime($this->input->post('to_date')));?> is <?php echo $total_expense[0]['amount'];?></p>
                            </div>
                            <table class="table table-striped table-bordered table-hover" id="sample_2">
                                <thead>
                                <tr>
                                    <th class="hidden">
                                        hidden
                                    </th>
                                    <th>
                                        Campus
                                    </th>
                                    <th>
                                        History
                                    </th>
                                    <th>
                                        Category
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
                                    <th>
                                        Action
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
                                            <a data-toggle='modal' data-id='<?php echo $i ?>' class='open-exphistDialog btn btn-primary' style='width: 100px' href='#historyexpense' >History
                                            </a>
                                        </td>
                                        <td>
                                            <?php
                                            if($expense['expense_category_id']==9):
                                                echo $expense['name'];
                                                echo '<br />';
                                                $user = $this->db->get_where('users', array('user_id'=>$expense['user_id']))->result_array();
                                                echo @$user[0]['first_name'].' '.@$user[0]['last_name'];
                                            else:
                                                echo @$expense['name'];
                                            endif;
                                            ?>
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
                                                echo  "<a data-toggle='modal' data-id='$i' class='btn btn-primary' style='width: 100px'>PENDING
</a>";

                                            elseif ($expense['approved_status'] == '2')

                                                echo  "<a data-toggle='modal' class='btn red'  style='width: 100px'  >REJECTED
</a>";
                                            elseif ($expense['approved_status'] == '1') {
                                                echo " <a data-toggle='modal' class='btn green' style='width: 100px' >APPROVED</a>  ";

                                                if ($expense['rev_status'] === NULL || $expense['rev_status'] === '')
                                                    echo "<br /> <br />  <a data-toggle='modal' data-id='$i' class='open-expreversal btn btn-primary' style='width: 150px' href='#expensereversal' > Want Reverse?</a>";
                                                elseif( $expense['rev_status'] === '0')
                                                    echo "<br /> <br />  <a data-toggle='modal' data-id='$i' class='open-expreversalapproval btn btn-warning' style='width: 150px' href='#expensereversalapproval' >Reversal Requested</a>";
                                                elseif( $expense['rev_status'] === '2')
                                                    echo "<br /> <br />  <a data-toggle='modal' data-id='$i' class='open-expreversalapproval btn btn-danger' style='width: 150px' href='#expensereversalapproval' >Reversal Rejected</a>";


                                            }
                                            else
                                                echo " <a data-toggle='modal' class='btn yellow' style='width: 100px' >Reversed </a>";


                                            ?></td>

                                        <td>
                                            <?php
                                            if(@$myAccess[0]['expense_edit']==1 || $this->session->userdata('role')=='Admin'):
                                                ?>
                                                <a href="<?php echo site_url().'/expenses/edit_expense/'.$expense['expense_id'];?>" title="Edit" class="btn blue"><i class="fa fa-edit"></i></a>
                                            <?php
                                            endif;
                                            ?>
                                            <?php
                                            if(@$myAccess[0]['expense_delete']==1 || $this->session->userdata('role')=='Admin'):
                                                ?>
                                                <a onclick="return confirm('Are you sure you want to delete this Expense?')" href="<?php echo site_url();?>/expenses/delete/<?php echo $expense['expense_id'];?>" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>
                                            <?php
                                            endif;
                                            ?>
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
<!-- END CONTENT -->


<div class="modal fade" id="historyexpense" tabindex="-1"   data-width="1200" >

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Expense History</h4>
    </div>
    <div class="modal-body">
        <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/expenses/change_approve_status">
            <div class="form-body">

                <div class="portlet-body">

                    <table class="table table-striped table-bordered table-hover" id="histtable">
                        <thead>
                        <tr>
                            <th class="hidden">
                                hidden
                            </th>
                            <th>
                                Campus
                            </th>
                            <th>
                                Category
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

                        </tr>
                        </thead>
                        <tbody>



                        </tbody>
                    </table>
                </div>



            </div>


        </form>

        <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/expenses/change_approve_status">
            <div class="form-body">


                <div class="form-group">

                    <div class="col-md-12">

                        <label class="form-control" style="text-align: center" >Do you Want to Approve this Expense Request?</label>

                    </div>
                </div>

				<input type="hidden"  name="from_date" value="<?php echo $from_date ?>" />
                <input type="hidden"  name="to_date" value="<?php echo $to_date ?>"/>
                <input type="hidden"  name="setype" value="<?php @$this->input->post('setype') ?>" />


                <?php if ($myAccess[0]['expense_approval'] === '1'): ?>
				<div id = "apvdiv"> 

					<div class="form-group" >
						<label class="col-md-6 control-label">Accept or Reject this Expense</label>
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


<div class="modal fade" id="expensereversal" tabindex="-1"   data-width="1200" >

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Expense Reversal</h4>
    </div>
    <div class="modal-body">

        <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/expenses/request_reverse">
            <div class="form-body">


                <div class="form-group">

                    <div class="col-md-12">

                        <label class="form-control" style="text-align: center" >Do you Want Reversal of this Expense?</label>

                    </div>
                </div>




                <div class="form-group">
                    <label class="col-md-3 control-label">AMOUNT</label>
                    <div class="col-md-9">
                        <input type="number"  name="amount" id="amount" class="form-control mobile" readonly/>

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
                    <div class="col-md-12" style="text-align: center">

                        <input type="hidden" id="expense_rev_id" name="expense_rev_id" value="" />
                        <input type="hidden" name="last_edit" value="<?php echo $this->session->userdata('name');?>" />
                        <button type="submit" class="btn red">Submit</button>

                    </div>
                </div>
            </div>
        </form>

    </div>


</div>


<div class="modal fade" id="expensereversalapproval" tabindex="-1"   data-width="1200" >

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Expense Reversal</h4>
    </div>
    <div class="modal-body">

        <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/expenses/request_reverse_approve">
            <div class="form-body">


                <div class="form-group">

                    <div class="col-md-12">

                        <label class="form-control" style="text-align: center" >Do you Want Reversal of this Expense?</label>

                    </div>
                </div>




                <div class="form-group">
                    <label class="col-md-3 control-label">AMOUNT</label>
                    <div class="col-md-9">
                        <input type="number"  name="ap_amount" id="ap_amount" class="form-control mobile" readonly/>

                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label">REASON <span class="required">*</span></label>
                    <div class="col-md-9">

                        <textarea class="form-control remarks" rows="3" id="ap_reason" name="reason" readonly></textarea>

                    </div>
                </div>


            </div>
            <?php if ($myAccess[0]['expense_approval'] === '1'): ?>
                <div class="form-group" >
                    <label class="col-md-6 control-label">Accept or Reject this Expense Reversal</label>
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

                            <input type="hidden" id="ap_expense_rev_id" name="expense_id" value="" />
                            <input type="hidden" name="last_edit" value="<?php echo $this->session->userdata('name');?>" />
                            <button type="submit" class="btn red">Submit</button>

                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </form>

    </div>


</div>