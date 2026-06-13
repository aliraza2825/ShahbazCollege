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
                            <i class="fa fa-list"></i>All Expenses Detail
                        </div>
                    </div>

                    <?php
                    if(@count(@$expenses)>0 ):
                        ?>
                        <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover" id="sample_2">
                                <thead>
                                <tr>
                                    <th>
                                        Sr
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
                                        Paid Through
                                    </th>
									<?php
										if($this->uri->segment(7)=='bank' || $this->uri->segment(7)=='all'):
									?>
									<th>
                                        Bank Details
                                    </th>
									<th>
                                        Transaction Description
                                    </th>
									<th>
                                        Bank Transaction Date
                                    </th>
									<?php
										endif;
									?>
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
                                        Approved By
                                    </th>

                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $i = 1;
                                foreach($expenses as $expense):
                                    ?>
                                    <tr class="odd gradeX">
                                        <td >
                                            <?php echo $i;?>
                                        </td>
                                        <td>
                                            <?php echo $expense['campus_name']?>
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
                                            <?php echo $expense['purpose'];
                                                if ($expense['expense_category_id'] == '30' || $expense['expense_category_id'] == '31' ){
                                                    $loan = $this->db->get_where("users","user_id = '".$expense['user_id']."'")->row();
                                                    echo $loan->first_name.' '.$loan->last_name.' '.$loan->cnic;
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <?php echo $expense['amount']?>
                                        </td>
										<td>
                                            <?php echo $expense['paid_type']?>
                                        </td>
										<?php
											if($this->uri->segment(7)=='bank' || $this->uri->segment(7)=='all'):
										?>
										<td>
											<?php
												echo $expense['payment_type'];
												$this->db->select('*');
												$this->db->from('bank_reconciliation_statement');
												$this->db->join('accounts','accounts.id=bank_reconciliation_statement.account_id','left');
												$this->db->where('bank_reconciliation_statement.expense_id',$expense['expense_id']);
												$transaction_details = $this->db->get()->result_array();
												echo @$transaction_details[0]['account_name'];
											?>
										</td>
										<td>
											<?php
												echo @$transaction_details[0]['description'];
											?>
										</td>
										<td>
											<?php
												echo @$transaction_details[0]['trans_date'];
											?>
										</td>
										<?php
											endif;
										?>
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