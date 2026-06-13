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
										By Bank
									</th>
									<th>
										By Cash
									</th>
                                    <th>
                                        Amount
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
											<?php
												$this->db->select_sum('expenses.amount');
												$this->db->from('expenses');
												$this->db->join('bank_reconciliation_statement','bank_reconciliation_statement.expense_id=expenses.expense_id', 'INNER');
												if($this->uri->segment(7)=='actual_date')
												{
													$this->db->where(array('bank_reconciliation_statement.trans_date>='=>$this->uri->segment(3),'bank_reconciliation_statement.trans_date<='=>$this->uri->segment(4),'expenses.expense_category_id'=>$this->uri->segment(5),'expenses.campus_id'=>$expense['campus_id'],'expenses.paid_type'=>'bank'));
												}
												else
												{
													$this->db->where(array('expenses.date>='=>$this->uri->segment(3),'expenses.date<='=>$this->uri->segment(4),'expenses.expense_category_id'=>$this->uri->segment(5),'expenses.campus_id'=>$expense['campus_id'],'expenses.paid_type'=>'bank'));
												}
												$bank = $this->db->get()->row()->amount;
											?>
											<a href="<?php echo site_url();?>/tax/all_expenses_details/<?php echo $this->uri->segment(3);?>/<?php echo $this->uri->segment(4);?>/<?php echo $expense['campus_id']?>/<?php echo $expense['expense_category_id'];?>/bank/<?php echo $this->uri->segment(7);?>"><?php echo $bank;?></a>
										</td>
										<td>
											<?php
												$this->db->select_sum('amount');
												$this->db->from('expenses');
												if($this->uri->segment(7)=='actual_date')
												{
													$this->db->where(array('date>='=>$this->uri->segment(3),'date<='=>$this->uri->segment(4),'expense_category_id'=>$this->uri->segment(5),'campus_id'=>$expense['campus_id'],'paid_type'=>'cash'));
												}
												else
												{
													$this->db->where(array('actual_date>='=>$this->uri->segment(3).' 00:00:00','actual_date<='=>$this->uri->segment(4).' 23:59:59','expense_category_id'=>$this->uri->segment(5),'campus_id'=>$expense['campus_id'],'paid_type'=>'cash'));
												}
												$cash = $this->db->get()->row()->amount;
											?>
											<a href="<?php echo site_url();?>/tax/all_expenses_details/<?php echo $this->uri->segment(3);?>/<?php echo $this->uri->segment(4);?>/<?php echo $expense['campus_id']?>/<?php echo $expense['expense_category_id'];?>/cash/<?php echo $this->uri->segment(7);?>"><?php echo $cash;?></a>
										</td>
                                        <td>
                                            <a href="<?php echo site_url();?>/tax/all_expenses_details/<?php echo $this->uri->segment(3);?>/<?php echo $this->uri->segment(4);?>/<?php echo $expense['campus_id']?>/<?php echo $expense['expense_category_id'];?>/all/<?php echo $this->uri->segment(7);?>"><?php echo $expense['total_amount']?></a>
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