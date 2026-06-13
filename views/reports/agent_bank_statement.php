<?php $myAccess = checkUserAccess(); ?>
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Edit Profile <small>You can edit your profile here</small>
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
            <?php endif;

            if (@$myAccess[0]['agent_view_statement'] == '1' || @$this->session->userdata('role') == 'Admin'):
            ?>
            <div class="col-md-12">
                <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/reports/agent_view_statement" enctype="multipart/form-data">
                    <div class="form-body">
                        <?php

$min_date = date('Y-m-01', strtotime('first day of 2 months ago'));

?>
                        <div class="form-group">
                            <label class="control-label col-md-3">Date</label>
                            <div class="col-md-3">
                                <div class="input-group input-medium date date-picker" data-date="<?php echo $from_date?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years" data-date-start-date="<?php echo $min_date; ?>">
                                    <input type="text" name="from_date" class="form-control" value="<?php echo $from_date;?>" readonly>
                                    <span class="input-group-btn">
                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3">Amount</label>
                            <div class="col-md-3">
                                <input type="text" name="amount" class="form-control" value="<?php echo $amount;?>">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="control-label col-md-3">Select Account</label>
                            <div class="form-group col-md-6">
                                <select class="form-control" name="account_id" id="select2_sample1" required>
                                    <?php
                                    foreach($accounts as $campus):
                                        ?>
                                        <option value="<?php echo $campus['id'];?>"><?php echo $campus['account_title'].' '.$campus['account_name']?></option>
                                    <?php
                                    endforeach;
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-9">
                                <input type="hidden" name="submit" value="1" />
                                <button type="submit" class="btn green">Check Statement</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> Bank Statement Here
							</div>
						</div>
						<div class="portlet-body">
                            <table class="table table-bordered table-hover" id="sample_2">
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
									 Credit
								</th>
                                <th>
									 Debit
								</th>
                               
                                <th>
									 Payment Relate to
								</th>
                                
							</tr>
							</thead>
							<tbody>
							<?php
								$i=0;
								foreach($entries as $closing_rule):
							?>
                            <tr>
                                <td >
                                	<?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $closing_rule['account_title'].' '.$closing_rule['account_name']?>
								</td>
                                <td>
									 <?php echo $closing_rule['trans_date']?>
								</td>
                                <td>
									 <?php echo $closing_rule['description'].' '.$closing_rule['reference_no'];?>
								</td>
								<td>
									 <?php echo $closing_rule['credit'];?>
								</td>
								<td>
									 <?php echo $closing_rule['debit'];?>
								</td>
								<td>
                                    <?php
                                    echo $closing_rule['trans_id']. ' '.$closing_rule['str_id'];

                                    if($closing_rule['statement_id'] != '' || $closing_rule['statement_id'] != NULL)
                                    {
                                    $this->db->select( 'payments.actual_amount,students.first_name as first_name, students.last_name as last_name, students.mobile as mobile, students.emergency_no as emergency_no,students.cnic as cnic, students.roll_no as roll_no, students.father_name as father_name, classes.name as class_name, campuses.bank_name, courses.course_name as course_name, campuses.account_no, campuses.address, campuses.campus_name, campuses.note, campuses.campus_id,payments.challan_no,payments.paid_date,payments.tid_no,payments.paid_date');
                                    $this->db->from('payments');
                                    $this->db->join('students', 'students.student_id=payments.student_id', 'inner');
                                    $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
                                    $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
                                    $this->db->join('courses', 'courses.course_id=students.course_id', 'left');
                                    $student=$this->db->where_in('payments.statement_id', $closing_rule['statement_id'])->get()->result_array();

                                    foreach ($student as $dat_payments):
                                    ?>
                                    <strong>Challan No : </strong><?php echo $dat_payments['challan_no'];?> <br>
                                    <strong>Amount : </strong><?php echo $dat_payments['actual_amount'];?> <br>
                                    <strong>Challan TID : </strong><strong style="color: #00CC00"><?php echo $dat_payments['tid_no'];?> </strong><br>
                                    <strong>Challan Paid Date : </strong><?php echo $dat_payments['paid_date'];?> <br>
                                    <strong>Roll No : </strong><?php echo $dat_payments['roll_no'];?> <br>
                                    <strong>Name : </strong><?php echo $dat_payments['first_name'].' '.$dat_payments['last_name'];?> <br>
                                    <strong>CNIC : </strong><?php echo $dat_payments['cnic']?> <br>
                                    <strong>Contact Details : </strong><?php echo $dat_payments['mobile'];?> <br>
                                    <strong>Emergency Contact : </strong><?php echo $dat_payments['emergency_no'];?><br />
                                    <strong>Campus : </strong><?php echo $dat_payments['campus_name'];?> <br>
                                    <strong>Class : </strong><?php echo $dat_payments['class_name'];?> <br>
                                    <strong>Course : </strong><?php echo $dat_payments['course_name'];?> <br><br>
                                    <?php
                                            endforeach;

                                     }

                                     elseif ($closing_rule['expense_id'] != '' || $closing_rule['expense_id'] != NULL)
                                     {
                                         if ($closing_rule['is_council_fee'] == "1")
                                         {
                                             $amnt = 0;
                                             $this->db->select('*,sum(amount) as grouped_amount,count(*) as total_students');
                                             $this->db->from('expenses');
                                             $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
                                             $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
                                             $this->db->where(array('expenses.payment_type'=>$closing_rule['trans_id']));
                                             $this->db->group_by("expenses.campus_id,expenses.class");
                                             $expenses = $this->db->get()->result_array();
                                             foreach ($expenses as $expense):
                                                 $amnt+= $expense['grouped_amount'];
                                             ?>
                                    <strong>Expense Category : </strong><?php echo $expense['name'];?> <br>
                                    <strong>Campus : </strong><strong style="color: #00CC00"><?php echo $expense['campus_name'];?> </strong><br>
                                    <strong>Title : </strong><?php echo $expense['title'];?> <br>
                                    <strong>Purpose : </strong><?php echo $expense['purpose'];?> <br>
                                    <strong>Amount : </strong><?php echo $expense['grouped_amount'];?> <br>
                                    <strong>Students : </strong><?php echo $expense['total_students'];?> <br>
                                    <strong>Add By : </strong><?php echo $expense['add_by']?> <br>
                                    <?php
                                    endforeach;?>
                                    <strong>Total AMount : </strong><?php echo $amnt;?> <br>
                                    <?php
                                    }else{
                                    $this->db->select('*');
                                    $this->db->from('expenses');
                                    $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
                                    $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
                                    $this->db->where(array('expenses.expense_id'=>$closing_rule['expense_id']));
                                    $expense = $this->db->get()->row();
                                    ?>
                                    <strong>Expense Category : </strong><?php echo $expense->name;?> <br>
                                    <strong>Campus : </strong><strong style="color: #00CC00"><?php echo $expense->campus_name;?> </strong><br>
                                    <strong>Title : </strong><?php echo $expense->title;?> <br>
                                    <strong>Purpose : </strong><?php echo $expense->purpose;?> <br>
                                    <strong>Amount : </strong><?php echo $expense->amount;?> <br>
                                    <strong>Add By : </strong><?php echo $expense->add_by?> <br>
                                    <?php
                                         }
                                     }

                                     elseif ($closing_rule['bank_transfer_id'] != '' || $closing_rule['bank_transfer_id'] != NULL)
                                     {
                                         $this->db->select('*');
                                         $this->db->from('bank_reconciliation_statement');
                                         $this->db->join('accounts','accounts.id=bank_reconciliation_statement.account_id','left');
                                         $this->db->where(array('bank_reconciliation_statement.id'=>$closing_rule['bank_transfer_id']));
                                         $expense = $this->db->get()->row();

                                            if ($expense->credit != NULL && $expense->credit != ''):
                                         ?>

                                    <strong> Transfered to Account : </strong><?php echo $expense->account_name;?> <br>

                                    <?php else: ?>
                                    <strong> Received From Account : </strong><?php echo $expense->account_name;?> <br>
                                    <?php endif; ?>
                                    <strong> Date : </strong><?php echo $expense->trans_date;?> <br>
                                    <strong> Amount : </strong><?php echo $expense->credit.''.$expense->debit;?> <br>

                                    <?php
                                     }

                                     elseif ($closing_rule['str_id'] != '' || $closing_rule['str_id'] != NULL)
                                     {
                                         if ($closing_rule['credit'] == "" || $closing_rule['credit'] == NULL ) {
                                             $this->db->select('*,transactions_history.amount as trans_amount');
                                             $this->db->from('transactions_history');
                                             $this->db->join('accounts', 'accounts.id=transactions_history.to_account_id', 'left');
                                             $this->db->where(array('transactions_history.id' => $closing_rule['str_id']));
                                             $expense = $this->db->get()->row();
                                         }else{
                                             $this->db->select('*,transactions_history.amount as trans_amount');
                                             $this->db->from('transactions_history');
                                             $this->db->join('accounts', 'accounts.id=transactions_history.from_account_id', 'left');
                                             $this->db->where(array('transactions_history.id' => $closing_rule['str_id']));
                                             $expense = $this->db->get()->row();
                                         }
                                         if ($closing_rule['credit'] == "" || $closing_rule['credit'] == NULL ):
                                         ?>

                                    <strong> Transferred to Account : </strong><?php echo $expense->account_name;?> <br>
                                    <?php else: ?>
                                    <strong> Received From Account : </strong><?php echo $expense->account_name;?> <br>
                                    <?php endif; ?>
                                    <strong> Date : </strong><?php echo date('Y-m-d',strtotime($expense->created_at));?> <br>
                                    <strong> Amount : </strong><?php echo $expense->trans_amount;?> <br>
                                    <a onclick="untag_entry(<?php echo $i ?>,<?php echo $closing_rule['trans_id'] ?>)">
                                        <button type="button" class="btn btn-danger"><i class="fa fa-trash"></i> Untag</button>
                                    </a>
                                    <?php
                                     }

                                     elseif ($closing_rule['closing_bank_id'] != '' || $closing_rule['closing_bank_id'] != NULL)
                                     {
                                         $this->db->select('*');
                                         $this->db->from('closing_perday');
                                         $this->db->join('campuses','campuses.campus_id=closing_perday.campus_id','left');
                                         $this->db->where(array('closing_perday.id'=>$closing_rule['closing_bank_id']));
                                         $closing = $this->db->get()->row();
                                         ?>
                                    <strong> Campus name : </strong><?php echo $closing->campus_name;?> <br>
                                    <strong> Closing ID : </strong><?php echo $closing->campus_closing_id;?> <br>
                                    <strong> Amount : </strong><?php echo $closing_rule['credit'];?> <br>
                                    <?php
                                     }

                                     elseif ($closing_rule['is_council_fee'] == "1" && ($closing_rule['expense_id'] == '' || $closing_rule['expense_id'] == NULL))
                                     {
                                         echo "Selected as Council Fee Not Tagged";
                                     }

                                     elseif ( @$closing_rule['paypro_id'] != '' || @$closing_rule['paypro_id'] != NULL )
                                     {
                                         $this->db->select('*');
                                         $this->db->from('pay_pro_settlement');
                                         $this->db->where(array('id'=>$closing_rule['paypro_id']));
                                         $closing = $this->db->get()->row();
                                         ?>
                                    <strong> Settlement Date : </strong><?php echo $closing->settlement_date;?> <br>
                                    <strong> Received Amount : </strong><?php echo $closing->paid_amount;?> <br>
                                    <strong> 1-Link Amount : </strong><?php echo $closing->link_amount;?> <br>
                                    <strong> Debit/Credit Card Amount : </strong><?php echo $closing->card_amount;?> <br>
                                    <?php
                                     }

                                     elseif ( @$closing_rule['salary_expense_ids'] != '' || @$closing_rule['salary_expense_ids'] != NULL )
                                     {
                                         $this->db->select('*');
                                         $this->db->from('expenses');
                                         $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
                                         $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
                                         $salary_expenses=$this->db->where(array('bank_statement_id'=>$closing_rule['trans_id']))->get()->result_array();

                                         foreach ($salary_expenses as $sals):
                                         ?>
                                    <strong>Expense Category : </strong><?php echo $sals['name'];?> <br>
                                    <strong>Campus : </strong><strong style="color: #00CC00"><?php echo $sals['campus_name'];?> </strong><br>
                                    <strong>Amount : </strong><?php echo $sals['amount'];?> <br>
                                    <strong>Add By : </strong><?php echo $sals['add_by']?> <br><br>
                                    <?php
                                         endforeach;

                                     }

                                     elseif ( @$closing_rule['profit_distribution_id'] != '' || @$closing_rule['profit_distribution_id'] != NULL )
                                     {
                                         $this->db->select('*');
                                         $this->db->from('profit_distribution');
                                         $this->db->join('campuses','campuses.campus_id = profit_distribution.campus_id');
                                         $this->db->join('users','users.user_id = profit_distribution.user_id');
                                         $this->db->where("profit_distribution_id",$closing_rule['profit_distribution_id']);
                                         $entry = $this->db->get()->row();

                                         echo '<strong> Profit To : </strong>'. $entry->first_name.' '.$entry->last_name.'<br>
                                              <strong> From Date : </strong>'.$entry->from_date.'<br>
                                              <strong> To Date : </strong>'.$entry->to_date.'<br>
                                              <strong> Campus : </strong>'. $entry->campus_name .' <br>';

                                     }?>
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
            <?php
            endif;?>
		</div>
	</div>
	<!-- END CONTENT -->