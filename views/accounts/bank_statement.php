
<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
    <div class="page-content">
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
        <?php
            if($this->session->userdata('role')=='Admin'):
        ?>
        <div class="row">
            <div class="col-md-12 ">
                <!-- BEGIN SAMPLE FORM PORTLET-->
                <div class="portlet box green ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i> Bank Reconciliation
                        </div>
                    </div>
                    <div class="portlet-body form">
                    <div class="col-md-6">
                        <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/accounts/upload_bank_statement" enctype="multipart/form-data">
                            <div class="form-body">
                                <div class="row">

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">Upload Csv File <span class="required">*</span></label>
                                            <div class="col-md-9">
                                                <input type="file" class="form-control" name="statement" value="" required />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <select class="form-control" name="account_id" required>
                                                <option value="">Select Account</option>
                                                <?php
                                                foreach($accounts as $campus):
                                                    ?>
                                                    <option value="<?php echo $campus['id'];?>"><?php echo $campus['account_title'].' <strong>'.$campus['account_name'].'</strong>'?></option>
                                                <?php
                                                endforeach;
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <button type="submit" class="btn green">Upload CSV File</button>
                                        <button onclick="location.href = '<?php echo site_url();?>'" type="button" class="btn default">Cancel</button>
                                        <button onclick="location.href = '<?php echo site_url()."/accounts/statements_record";?>'" type="button" class="btn default">Upload History</button>
                                        <button onclick="location.href = '<?php echo site_url()."/accounts/yearly_tax_return_report";?>'" type="button" class="btn default">Tax Return Report</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                     <div class="col-md-6">
                         <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/accounts/uploadstatement" enctype="multipart/form-data">
                            <div class="form-body">

                                <div class="form-group">
                                    <label class="control-label col-md-3">From Date</label>
                                    <div class="col-md-3">
                                        <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                            <input type="text" name="from_date" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
                                            <span class="input-group-btn">
                                            <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">To Date</label>
                                    <div class="col-md-3">
                                        <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                            <input type="text" name="to_date" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
                                            <span class="input-group-btn">
                                            <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label class="control-label col-md-3">Select Account</label>
                                    <div class="form-group col-md-8">
                                        <select class="form-control select2" name="account_id[]" id="select2_sample1" multiple required>
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
                                <div class="form-group">
                                    <label class="control-label col-md-3">Select Entry Type</label>
                                    <div class="form-group col-md-6">
                                        <select class="form-control" name="tag_type" required>
                                            <option value="2">ALL Entries</option>
                                            <option value="0">Bank UNTagged Entries</option>
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
                    </div>
                </div>

                <div class="row">
                </div>
                <!-- END SAMPLE FORM PORTLET-->
            </div>
        </div>
        <?php
            endif;
        ?>
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
                                Statement ID
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
                                 Debit
                            </th>
                            <th>
                                 Credit
                            </th>
                            <th>
                                 Balance
                            </th>
                            <th>
                                 Payment Relate to
                            </th>
                            <th>
                                 Tagged Amount
                            </th>

                        </tr>
                        </thead>
                        <tbody>
                        <?php
                            $i=0;
                            foreach($entries as $closing_rule): ?>
                        <tr>
                            <td >
                                <?php echo $i;?>
                            </td>
                            <td>
                                 <?php echo $closing_rule['statement_no']?>
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
                                 <?php echo $closing_rule['debit'];?>
                            </td>
                            <td>
                                 <?php echo $closing_rule['credit'];?>
                            </td>
                            <td>
                                 <?php echo $closing_rule['balance'];?>
                            </td>
                            <td>
                                <div id="row-<?php echo $i;?>">
                                <?php
                                     echo $closing_rule['trans_id']. ' '.$closing_rule['str_id'].'<br>';

                                     if($closing_rule['statement_id'] != '' || $closing_rule['statement_id'] != NULL)
                                     {
                                            $this->db->select( 'payments.actual_amount,students.first_name as first_name, students.last_name as last_name, students.mobile as mobile, students.emergency_no as emergency_no,students.cnic as cnic, students.roll_no as roll_no, students.father_name as father_name, classes.name as class_name, campuses.bank_name, courses.course_name as course_name, campuses.account_no, campuses.address, campuses.campus_name, campuses.note, campuses.campus_id,payments.challan_no,payments.paid_date,payments.tid_no,payments.paid_date,payments.paid_challans,payments.contract_id');
                                            $this->db->from('payments');
                                            $this->db->join('students', 'students.student_id=payments.student_id', 'inner');
                                            $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
                                            $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
                                            $this->db->join('courses', 'courses.course_id=students.course_id', 'left');
                                            //$this->db->group_by('payments.contract_id');
                                            $student=$this->db->where_in('payments.statement_id', $closing_rule['statement_id'])->get()->result_array();

                                            foreach ($student as $dat_payments):
                                                if($dat_payments['contract_id']==0):
                                         ?>
                                                <strong>Challan No : </strong><?php if($dat_payments['paid_challans']==''){echo $dat_payments['challan_no'];}else{echo $dat_payments['paid_challans'];}?> <br>
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
                                                else:
                                                    //GET CONTRACTOR DETAILS
                                                    $this->db->select('*');
                                                    $this->db->from('contracts');
                                                    $this->db->join('contractors','contractors.contractor_id=contracts.contractor_id');
                                                    $this->db->where('contracts.contract_id',$dat_payments['contract_id']);
                                                    $contractor = $this->db->get()->result_array();

                                                    //GET STUDENTS DETAILS
                                                    $this->db->select('*');
                                                    $this->db->from('payments');
                                                    $this->db->join('students','students.student_id=payments.student_id','inner');
                                                    $this->db->where_in('payments.statement_id', $closing_rule['statement_id']);
                                                    $students = $this->db->get()->result_array();
                                        ?>
                                                <strong>Total Paid Amount : </strong><?php echo $dat_payments['actual_amount'];?> <br>
                                                <strong>Challan TID : </strong><strong style="color: #00CC00"><?php echo $dat_payments['tid_no'];?> </strong><br>
                                                <strong>Challan Paid Date : </strong><?php echo $dat_payments['paid_date'];?> <br>
                                                <strong>Contractor Name : </strong><?php echo $contractor[0]['name'];?> <br>
                                                <strong>CNIC : </strong><?php echo $contractor[0]['cnic']?> <br>
                                                <strong>Contact Details : </strong><?php echo $contractor[0]['mobile'];?> <br>
                                                <strong>Emergency Contact : </strong><?php echo $contractor[0]['emergency_no'];?><br />
                                                <?php
                                                    foreach($students as $student):
                                                        echo '<strong>Student Name: </strong>'.$student['first_name'].' '.$student['last_name'].' ('.$student['roll_no'].') <strong>Challan #: </strong>'.$student['challan_no'].' <strong>Amount: </strong>'.$student['amount'];
                                                        echo '<br />';
                                                    endforeach;
                                                ?>
                                        <?php
                                                endif;
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

                                         <strong> Transferred to Account : </strong><?php echo $expense->account_name;?> <br>

                                            <?php else: ?>
                                         <strong> Received From Account : </strong><?php echo $expense->account_name;?> <br>
                                            <?php endif; ?>
                                         <strong> Date : </strong><?php echo $expense->trans_date;?> <br>
                                         <strong> Amount : </strong><?php echo $expense->credit.''.$expense->debit;?> <br>
                                         <a onclick="untag_bank_entry(<?php echo $i ?>,<?php echo $closing_rule['trans_id'] ?>)">
                                             <button type="button" class="btn btn-danger"><i class="fa fa-trash"></i> Untag</button>
                                         </a>
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
                                         $this->db->from('bank_reconciliation_statement');
                                         $this->db->join('pay_pro_settlement','pay_pro_settlement.id = bank_reconciliation_statement.paypro_id');
                                         $this->db->where(array('pay_pro_settlement.id'=>$closing_rule['paypro_id']));
                                         $closings = $this->db->get()->result_array();

                                         foreach ($closings as $closing) {
                                             echo '<strong> Settlement Date : </strong>' . $closing['settlement_date'] . '<br>
                                                      <strong> Tagged Amount : </strong>' . $closing['credit'] . ' <br>
                                                      <strong> Received Amount : </strong>' . $closing['paid_amount'] . ' <br>
                                                      <strong> Total 1-Link Amount : </strong>' . $closing['link_amount'] . ' <br>
                                                      <strong> Total Debit/Credit Card Amount : </strong>' . $closing['card_amount']. '<br /><br />';

                                         }

                                     }

                                     elseif ( @$closing_rule['salary_expense_ids'] != '' || @$closing_rule['salary_expense_ids'] != NULL )
                                     {
                                         $this->db->select('*');
                                         $this->db->from('expenses');
                                         $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
                                         $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
                                         $salary_expenses=$this->db->where(array('bank_statement_id'=>$closing_rule['trans_id']))->get()->result_array();

                                         foreach ($salary_expenses as $sals): ?>
                                             <strong>Expense Category : </strong><?php echo $sals['name'];?> <br>
                                             <strong>Campus : </strong><strong style="color: #00CC00"><?php echo $sals['campus_name'];?> </strong><br>
                                             <strong>Amount : </strong><?php echo $sals['amount'];?> <br>
                                             <strong>Add By : </strong><?php echo $sals['add_by']?>
                                             <a data-toggle="modal" data-exp_id="<?php echo $sals['expense_id']?>" data-trans_id="<?php echo $closing_rule['trans_id'] ?>" title="View Salary Persons" class="open_salary_view" href="#salary_reverse_modal">
                                                 <i class="fa fa-eye"></i>
                                             </a>
                                             <br>
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

                                     }

                                     elseif ( @$closing_rule['loan_id'] != '' || @$closing_rule['loan_id'] != NULL )
                                     {
                                         $this->db->select('users.*,loans.*,expenses.date as expense_date');
                                         $this->db->from('loans');
                                         $this->db->join('users','users.user_id = loans.user_id','left');
										 $this->db->join('expenses','expenses.loan_id = loans.id','left');
                                         $this->db->where("loans.id",$closing_rule['loan_id']);
                                         $entry = $this->db->get()->row();

                                         echo '<strong> Loan ID : </strong>loan-'. $entry->id.'<br>
                                              <strong> Given To : </strong>'.$entry->first_name.' '.$entry->last_name.'<br>
                                              <strong> Amount : </strong>'.$entry->cash_given.'<br>
                                              <strong> Months : </strong>'.$entry->months_approved.'<br>
											  <strong> Expense Date : </strong>'.$entry->expense_date.'<br>';

                                     }

                                     elseif ( @$closing_rule['reversal_payroll_trans_id'] != '' || @$closing_rule['reversal_payroll_trans_id'] != NULL )
                                     {
                                         $this->db->select('*');
                                         $this->db->from('bank_reconciliation_statement');
                                         $this->db->join('accounts','accounts.id = bank_reconciliation_statement.account_id');
                                         $this->db->where("bank_reconciliation_statement.id",$closing_rule['reversal_payroll_trans_id']);
                                         $entry = $this->db->get()->row();

                                         $this->db->select('*');
                                         $this->db->from('payroll');
                                         $this->db->join('users','users.user_id = payroll.user_id');
                                         $this->db->where("payroll.id",$closing_rule['reversal_payroll_id']);
                                         $payroll = $this->db->get()->row();


                                            ?>
                                               <strong> Reverse against Salary  From  Account : </strong><?php echo $entry->account_name;?> <br>
                                               <strong> Date : </strong><?php echo date('Y-m-d',strtotime($entry->created_at));?> <br>
                                               <strong> Amount : </strong><?php echo $entry->debit;?> <br><br>

                                         <strong> Salary Person: </strong><?php echo $payroll->first_name. ' '.$payroll->last_name;?> <br>
                                         <strong> MONTH : </strong><?php echo $payroll->payroll_year.'-'.$payroll->payroll_month;?> <br>
                                         <strong> Amount : </strong><?php echo $payroll->earned_salary;?> <br><br>
                                     <?php
                                     }

                                     else
                                     {
                                         if ($closing_rule['credit'] == NULL || $closing_rule['credit'] == "" || $closing_rule['credit'] == 0 ){?>
                                         <select class="form-control" id="trans_type<?php echo $closing_rule['trans_id'] ?>" name="trans_type" onchange="function_changed(<?php echo $closing_rule['trans_id']?>,<?php echo $i?>,<?php echo ((int)str_replace(',', '', $closing_rule['debit']))?>,<?php echo ((int)str_replace(',', '', $closing_rule['credit']))?>)">
                                                <option value="">Select Fee Collected</option>
                                                <option value="expense">Expense</option>
                                                <option value="bank">Transfer to Other Bank</option>
                                                <option value="cash">Cash Withdrawal</option>
                                                <option value="council_fee">Council Sequence Expense</option>
                                                <option value="expense_tag">Tag Salary Expense</option>
                                                <option value="share_distribution">Share Profit Distribution</option>
                                                <option value="tag_loan">Tag Loan</option>
                                         </select>
                                     <?php
                                         }
                                         else{
                                             ?>
                                         <select class="form-control" id="trans_type<?php echo $closing_rule['trans_id'] ?>" name="trans_type" onchange="function_changed(<?php echo $closing_rule['trans_id']?>,<?php echo $i?>,<?php echo ((int)str_replace(',', '', $closing_rule['debit']))?>,<?php echo ((int)str_replace(',', '', $closing_rule['credit']))?>,'<?php echo $closing_rule['trans_date']?>')">
                                             <option value="">Select Fee Collected</option>
                                             <option value="pay_pro">Tag with PayPro</option>
                                             <option value="cash_deposit">Cash Deposit</option>
                                             <option value="salary_reverse">Salary Reversal</option>
                                         </select>
                                     <?php
                                         }
                                     }
                                  ?>
                                </div>
                                <div id="div-show-<?php echo $i;?>" style="display: none;">
                                    <?php
                                    if ($closing_rule['credit'] == NULL || $closing_rule['credit'] == "" || $closing_rule['credit'] == 0 ){?>
                                        <select class="form-control" id="trans_type<?php echo $closing_rule['trans_id'] ?>" name="trans_type" onchange="function_changed(<?php echo $closing_rule['trans_id']?>,<?php echo $i?>,<?php echo ((int)str_replace(',', '', $closing_rule['debit']))?>,<?php echo ((int)str_replace(',', '', $closing_rule['credit']))?>)">
                                            <option value="">Select Fee Collected</option>
                                            <option value="expense">Expense</option>
                                            <option value="bank">Transfer to Other Bank</option>
                                            <option value="cash">Cash Withdrawal</option>
                                            <option value="council_fee">Council Sequence Expense</option>
                                            <option value="expense_tag">Tag Salary Expense</option>
                                            <option value="share_distribution">Share Profit Distribution</option>
                                            <option value="tag_loan">Tag Loan</option>
                                        </select>
                                        <?php
                                    }else{
                                        ?>
                                        <select class="form-control" id="trans_type<?php echo $closing_rule['trans_id'] ?>" name="trans_type" onchange="function_changed(<?php echo $closing_rule['trans_id']?>,<?php echo $i?>,<?php echo ((int)str_replace(',', '', $closing_rule['debit']))?>,<?php echo ((int)str_replace(',', '', $closing_rule['credit']))?>,'<?php echo $closing_rule['trans_date']?>')">
                                            <option value="">Select Fee Collected</option>
                                            <option value="pay_pro">Tag with PayPro</option>
                                            <option value="cash_deposit">Cash Deposit</option>
                                            <option value="salary_reverse">Salary Reversal</option>
                                        </select>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </td>
                            <td>
                                 <?php echo $closing_rule['tagged_amount'];?>
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
`
    <div id="addexpense" class="modal fade" tabindex="-1" role="dialog" data-width="1200">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title">Expense Tag</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <form action="<?php echo site_url();?>/accounts/add_expense" id="add_expense_form" method="post" enctype="multipart/form-data">
                        <div class="form-group" style="margin-top: 5px;">
                                <label class="col-md-3 control-label">Expense Campus <span class="required">*</span></label>
                                <div class="col-md-9">
                                    <select class="form-control campus" name="ac_campus_id" id="ac_campus_id" required>
                                        <option value="">Select Campus</option>
                                        <?php
                                        foreach($allcampuses as $campus):
                                            ?>
                                            <option value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name'];?></option>
                                        <?php
                                        endforeach;
                                        ?>
                                    </select>
                                    <!--<span class="help-inline"></span>-->
                                </div>
                            </div>
                        <div class="form-group exp_details">
                            <div class="exp_cats">
                                <div class="form-group" style="margin-top: 5px;"  id="div-0">
                                    <label class="col-md-3 control-label">Expense Category <span class="required">*</span></label>
                                    <div class="col-md-9">
                                        <select class="form-control select2  exps"  data-count="0" name="expense_category_id[]" id="category_id" required>
                                            <option value="">Select expense category</option>
                                            <?php
                                            foreach($categories as $category):
                                                ?>
                                                <option value="<?php echo $category['expense_category_id'];?>"><?php echo $category['name'];?></option>
                                            <?php
                                            endforeach;
                                            ?>
                                        </select>
                                        <!--<span class="help-inline"></span>-->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Title <span class="required">*</span></label>
                            <div class="col-md-9">
                                <input type="text" class="form-control input-inline input-medium custom_title" name="title" placeholder="Enter title" value="" required>
                                <span class="help-inline"></span>
                            </div>
                        </div>
                        <div class="form-group" style="margin-top: 5px;">
                            <label class="col-md-3 control-label">Description <span class="required">*</span></label>
                            <div class="col-md-9">
                                <textarea name="reason_disc" class="form-control" rows="5" required></textarea>
                                <span class="help-inline"></span>
                            </div>
                        </div>
                        <div class="form-group" style="margin-top: 5px;">
                            <label class="col-md-3 control-label">Image</label>
                            <div class="col-md-9">
                                <input type="file" name="image" class="form-control"  />
                            </div>
                        </div>


                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-3 col-md-9">

                                    <input type="hidden" name="trans_id" id="trans_id" />
                                    <input type="hidden" name="amount" id="amount"  />
                                    <button type="submit" id="ajax_post_expense" class="btn red">Add Expense</button>

                                </div>
                            </div>
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
    <div class="modal fade" id="bank_transfer" tabindex="-1" role="dialog"   data-width="1200" >

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title">Bank Transfer</h4>
        </div>
        <div class="modal-body">

            <div class="row">
                <div class="col-md-6">
                    <form id="find_statement"  method="post" enctype="multipart/form-data">
                        <div class="form-group" style="margin-top: 5px;">
                            <label class="col-md-3 control-label">Expense Campus <span class="required">*</span></label>
                            <div class="col-md-9">
                                <select class="form-control" name="bank_id" required>
                                    <option value="">Select Account</option>
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
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-3 col-md-9">
                                    <input type="hidden" name="bank_trans_id" class="bank_trans_id" />
                                    <button type="button" id="find_trans" class="btn red">Find Transaction</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div>
            </div>

            <form class="form-horizontal" enctype="multipart/form-data" role="form" id="submit_trans_form" method="post" action="<?php echo site_url();?>/accounts/tag_bank_trans">
                <div class="form-body" style="margin-top: 20px;">
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover" id="histtable">
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
                                    Debit
                                </th>
                                <th>
                                    Credit
                                </th>
                                <th>
                                    Balance
                                </th>
                            </tr>

                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
                <input type="hidden" class="bank_trans_id" name="bank_trans_id" required/>
                <button type="submit" id="ajax_submit_trans" class="btn blue" style="display: none;">Submit</button>
            </form>
        </div>
    </div>
    <div id="cashinhand" class="modal fade" tabindex="-1" role="dialog" data-width="760">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title">Cash in Hand</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <form action="<?php echo site_url();?>/accounts/add_cash_in_hand" id="add_cashinhand_form" method="post" enctype="multipart/form-data">
                        <div class="form-group" style="margin-top: 5px;">
                            <label class="col-md-3 control-label">Select Cash Account <span class="required">*</span></label>
                            <div class="col-md-9">
                                <select class="form-control" name="cash_account_id" required>
                                    <option value="">Select Account</option>
                                    <?php
                                    foreach($cash_accounts as $campus):
                                        ?>
                                        <option value="<?php echo $campus['id'];?>"><?php echo $campus['account_title'].' '.$campus['account_name']?></option>
                                    <?php
                                    endforeach;
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group" style="margin-top: 5px;">
                            <label class="col-md-3 control-label">Description <span class="required">*</span></label>
                            <div class="col-md-9">
                                <textarea name="reason_disc" class="form-control" rows="5" required></textarea>
                                <span class="help-inline"></span>
                            </div>
                        </div>
                        <div class="form-group" style="margin-top: 5px;">
                            <label class="col-md-3 control-label">Image</label>
                            <div class="col-md-9">
                                <input type="file" name="image" class="form-control"  />
                            </div>
                        </div>
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-3 col-md-9">
                                    <input type="hidden" name="cash_trans_id" id="cash_trans_id" />
                                    <input type="hidden" name="amount" id="cash_amount"  />
                                    <button type="submit" id="ajax_submit_cash" class="btn red">Submit</button>
                                </div>
                            </div>
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
    <div id="council_fee" class="modal fade" tabindex="-1" role="dialog" data-width="760">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title">Cash in Hand</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <form action="<?php echo site_url();?>/accounts/add_council_fee" method="post" enctype="multipart/form-data">

                        <div class="form-group" style="margin-top: 5px;">
                            <label class="col-md-12 control-label">Do you Really want to Set this Entry with Amount of <span id="amount_council" style="font-weight: bolder"></span> as Council Fee</label>
                        </div>
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-3 col-md-9">
                                    <input type="hidden" name="trans_id" id="council_trans_id" />
                                    <input type="hidden" name="amount" id="council_amount"  />
                                    <button type="button"  id="ajax_submit_counsil_fee" class="btn red">Submit</button>
                                </div>
                            </div>
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
    <div class="modal fade" id="pay_pro_tag" tabindex="-1" role="dialog"   data-width="1200" >
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title">Select PayPro</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6">
                    <form id="find_statement"  method="post" enctype="multipart/form-data">
                        <div class="form-actions">
                            <div class="row">
                                <div class="form-group">
                                    <label class="control-label col-md-3">Date</label>
                                    <div class="col-md-3">
                                        <div class="input-group input-medium date date-picker" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                            <input type="text" name="from_date" class="form-control" id="pay_pro_date" value="" readonly>
                                            <span class="input-group-btn">
                                                <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-offset-3 col-md-9">
                                    <input type="hidden" name="bank_trans_id" class="bank_trans_id" value=""/>
                                    <input type="hidden" id="paypro_amount" name="amount" value=""/>
                                    <button type="button" id="find_paypro_trans" class="btn red">Find Transaction</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div>
            </div>
            <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" id="tag_paypro_trans_form" action="<?php echo site_url();?>/accounts/tag_paypro_trans">
                <div class="form-body" style="margin-top: 20px;">
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover" id="payprohisttable">
                            <thead>
                            <tr>
                                <th>
                                    ID
                                </th>
                                <th>
                                    Settlement Date
                                </th>
                                <th>
                                    Total Payments Amount
                                </th>
                                <th>
                                    Received Amount
                                </th>
                                <th>
                                    1-LINK Amount
                                </th>
                                <th>
                                    Debit/Credit Card Amount
                                </th>
                                <th>
                                    Created Date
                                </th>
                            </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
                <input type="hidden" class="bank_trans_id" name="bank_trans_id" />
                <input type="hidden" id="paypro_set_amount" name="amount" value=""/>
                <button type="submit" id="ajax_submit_paypro" style="display: none" class="btn blue">Submit</button>
            </form>
        </div>
    </div>
    <div class="modal fade" id="expense_tag" tabindex="-1" role="dialog"   data-width="1200" >

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title">Select Expense</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6">
                    <form id="find_statement"  method="post" enctype="multipart/form-data">
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-3 col-md-9">
                                    <input type="hidden" name="bank_trans_id" class="bank_trans_id" value=""/>
                                    <input type="hidden" id="expense_amount" name="amount" value=""/>
                                    <button type="button" id="find_expenses" class="btn red">Find Expenses</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div>
            </div>
            <form class="form-horizontal" enctype="multipart/form-data" role="form" id="salary_expense_form" method="post" action="<?php echo site_url();?>/accounts/tag_expense_trans">
                <div class="form-body" style="margin-top: 20px;">
                    <div class="portlet-body" id="checkboxes">
                        <table class="table table-striped table-bordered table-hover" id="expense_hist_table">
                            <thead>
                                <tr>
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
                                        Add By
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-2 control-label">Statement Amount </label>
                    <input class="col-md-3 control-input" name="statement_amount" id="statement_amount" value="">
                    <label class="col-md-2 control-label">Expense Amount </label>
                    <input class="col-md-3 control-input" name="expense_amount" id="expense_tag_amount" value="">
                </div>
                <input type="hidden" class="bank_trans_id" name="bank_trans_id" />
                <input type="hidden" id="expense_user_ids" name="expense_user_ids" required/>
                <button type="submit" id="submit_expense_trans" style="display: none" class="btn blue">Submit</button>
            </form>
        </div>
    </div>
    <div id="cash_deposit" class="modal fade" tabindex="-1" role="dialog" data-width="760">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Cash in Hand</h4>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <form action="<?php echo site_url();?>/accounts/add_cash_deposit" id="add_cash_deposit_form" method="post" enctype="multipart/form-data">
                    <div class="form-group" style="margin-top: 5px;">
                        <label class="col-md-3 control-label">Select Cash Account <span class="required">*</span></label>
                        <div class="col-md-9">
                            <select class="form-control" name="cash_account_id" required>
                                <option value="">Select Account</option>
                                <?php
                                foreach($cash_accounts as $campus):
                                    ?>
                                    <option value="<?php echo $campus['id'];?>"><?php echo $campus['account_title'].' '.$campus['account_name']?></option>
                                <?php
                                endforeach;
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" style="margin-top: 5px;">
                        <label class="col-md-3 control-label">Description <span class="required">*</span></label>
                        <div class="col-md-9">
                            <textarea name="reason_disc" class="form-control" rows="5" required></textarea>
                            <span class="help-inline"></span>
                        </div>
                    </div>
                    <div class="form-group" style="margin-top: 5px;">
                        <label class="col-md-3 control-label">Amount <span class="required">*</span></label>
                        <div class="col-md-9">
                            <input  class="form-control" value="" name="cash_deposit_amount" id="cash_deposit_amount" readonly/>
                            <span class="help-inline"></span>
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-9">
                                <input type="hidden" name="cash_trans_id" id="cash_deposit_trans_id" />
                                <button type="submit" id="ajax_submit_cash_deposit" class="btn red">Submit</button>
                            </div>
                        </div>
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
    <div class="modal fade" id="tag_share_distribution" tabindex="-1" role="dialog"   data-width="1200" >
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Select PayPro</h4>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6">
                <form id="find_statement"  method="post" enctype="multipart/form-data">
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-9">
                                <input type="hidden" id="bank_trans_id_dist" name="bank_trans_id" class="bank_trans_id" value=""/>
                                <input type="hidden" class="bank_trans_amount_dist" name="amount" value=""/>
                                <button type="button" id="find_profit_distribution" class="btn red">Find Profit Distribution</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="clearfix"></div>
        </div>
        <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" id="tag_profit_trans_form" action="<?php echo site_url();?>/accounts/tag_paypro_trans">
            <div class="form-body" style="margin-top: 20px;">
                <div class="portlet-body">
                    <table class="table table-striped table-bordered table-hover" id="profihisttable">
                        <thead>
                        <tr>
                            <th>
                                ID
                            </th>
                            <th>
                                From Date
                            </th>
                            <th>
                                To Date
                            </th>
                            <th>
                                User
                            </th>
                            <th>
                                Campus
                            </th>
                            <th>
                                Amount
                            </th>
                            <th>
                                Percentage
                            </th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
            <input type="hidden" class="bank_trans_id" name="bank_trans_id" />
            <input type="hidden" class="bank_trans_amount_dist" name="amount" />
            <button type="submit" id="ajax_submit_profit" style="display: none" class="btn blue">Submit</button>
        </form>
    </div>
</div>
    <div class="modal fade" id="tag_loan_modal" tabindex="-1" role="dialog"   data-width="1200" >
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title">Select Pending Approved Loans</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6">
                    <form id="find_statement"  method="post" enctype="multipart/form-data">
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-3 col-md-9">
                                    <input type="hidden" id="bank_trans_id_loan" name="bank_trans_id" class="bank_trans_id" value=""/>
                                    <input type="hidden" class="bank_trans_amount_loan" name="amount" value=""/>
                                    <button type="button" id="find_loans" class="btn red">Find Pending Approved Loans</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div>
            </div>
            <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" id="tag_loan_trans_form" action="<?php echo site_url();?>/accounts/tag_loan_trans">
                <div class="form-body" style="margin-top: 20px;">
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover" id="loanhisttable">
                            <thead>
                            <tr>
                                <th>
                                    ID
                                </th>
                                <th>
                                    Name
                                </th>
                                <th>
                                    CNIC
                                </th>
                                <th>
                                    Loan Type
                                </th>
                                <th>
                                    Approved Amount
                                </th>
                                <th>
                                    Approved Months
                                </th>
                                <th>
                                    Created at
                                </th>
                            </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
                <input type="hidden" class="bank_trans_id" name="bank_trans_id" />
                <input type="hidden" class="bank_trans_amount_dist" name="amount" />
                <button type="submit" id="ajax_submit_loan" style="display: none" class="btn blue">Submit</button>
            </form>
        </div>
    </div>
    <div class="modal fade" id="salary_reverse_modal" tabindex="-1" role="dialog"   data-width="1200" >
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title">Salary Details</h4>
        </div>
        <div class="modal-body">
            <div id="salary_spinner"><i class="fa fa-spin fa-3x"></i></div>
        <div id="salary_div_find" style="display: none;">
            <div class="row">
                <div class="col-md-10">
                    <form id="find_statement_reverse"  method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label class="control-label col-md-3">From Date</label>
                                <div class="col-md-3">
                                    <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                        <input type="text" name="from_date" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
                                        <span class="input-group-btn">
                                            <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                            </span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3">To Date</label>
                                <div class="col-md-3">
                                    <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                        <input type="text" name="to_date" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
                                        <span class="input-group-btn">
                                            <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <br />
                            <div class="form-group">
                                <label class="control-label col-md-3">Select Salary To Reverse</label>
                                <div class="col-md-9">
                                    <table class="table table-striped table-bordered table-hover col-md-10" id="salary_table">
                                    <thead>
                                    <tr>
                                        <th>
                                            Sr
                                        </th>
                                        <th>
                                            Person Name
                                        </th>
                                        <th>
                                            Salary Month
                                        </th>
                                        <th>
                                            Amount
                                        </th>
                                        <th>
                                            date
                                        </th>
                                    </tr>

                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                                </div>
                            </div>
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-3 col-md-9">
                                    <input type="hidden" name="bank_trans_id" class="bank_trans_id" />
                                    <button type="button" id="find_trans_reverse" class="btn red">Find Transaction</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div>
            </div>
            <form class="form-horizontal" enctype="multipart/form-data" role="form" id="submit_trans_salary_form" method="post" action="<?php echo site_url();?>/accounts/tag_salary_reverse">
                <div class="form-body" style="margin-top: 20px;">
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover" id="salary_histtable">
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
                                    Debit
                                </th>
                                <th>
                                    Credit
                                </th>
                                <th>
                                    Balance
                                </th>
                            </tr>

                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
                <input type="hidden" class="bank_trans_id" name="bank_trans_id" required/>
                <button type="submit" id="ajax_submit_salary_reverse" class="btn blue" style="display: none;">Submit</button>
            </form>
            </div>
        </div>
    </div>
<script>
        var count = 0;
        var index = -1;
        function function_changed(id,ind,amount,credit = "",date = "") {
            index = ind;
            if( $('#trans_type'+id).val() === 'expense' ) {
                $(".modal-body #trans_id").val( id );
                $(".modal-body #amount").val( amount );
                $("#addexpense").modal('toggle');
            }
            else if ($('#trans_type'+id).val() === 'bank') {
                $(".modal-body .bank_trans_id").val( id );
                $("#bank_transfer").modal('toggle');
            }
            else if ($('#trans_type'+id).val() === 'cash') {
                $(".modal-body #cash_trans_id").val( id );
                $(".modal-body #cash_amount").val( amount );
                $("#cashinhand").modal('toggle');
            }
            else if ($('#trans_type'+id).val() === 'council_fee') {
                $(".modal-body #council_trans_id").val( id );
                $(".modal-body #amount_council").html( amount );
                $("#council_fee").modal('toggle');
            }
            else if ($('#trans_type'+id).val() === 'pay_pro') {
                $(".modal-body .bank_trans_id").val( id );
                $(".modal-body #paypro_amount").val( credit );
                $(".modal-body #paypro_set_amount").val( credit );
                $(".modal-body #pay_pro_date").val( date );
                $("#pay_pro_tag").modal('toggle');
                $(".modal-body #payprohisttable tbody").html("");
            }
            else if ($('#trans_type'+id).val() === 'expense_tag') {
                $(".modal-body .bank_trans_id").val( id );
                $(".modal-body #expense_amount").val( amount );
                $(".modal-body #statement_amount").val( amount );
                $("#expense_tag").modal('toggle');
                $(".modal-body #expense_hist_table tbody").html("");
                $("#submit_expense_trans").hide();
            }
            else if ($('#trans_type'+id).val() === 'cash_deposit') {
                $(".modal-body #cash_deposit_trans_id").val( id );
                $(".modal-body #cash_deposit_amount").val( credit );
                $("#cash_deposit").modal('toggle');
            }
            else if ($('#trans_type'+id).val() === 'share_distribution') {
                $(".modal-body .bank_trans_id").val( id );
                $(".modal-body .bank_trans_amount_dist").val( amount );
                $("#tag_share_distribution").modal('toggle');
            }
            else if ($('#trans_type'+id).val() === 'tag_loan') {
                $(".modal-body .bank_trans_id").val( id );
                $(".modal-body .bank_trans_amount_loan").val( amount );
                $("#tag_loan_modal").modal('toggle');
            }
            else if ($('#trans_type'+id).val() === 'salary_reverse') {
                $(".modal-body .bank_trans_id").val( id );
                // $(".modal-body .bank_trans_amount_loan").val( amount );
                $("#salary_reverse_modal").modal('toggle');
            }
        }
        function getselected() {

            var selected = [];
            var payroll_ids = [];
            $('div#checkboxes input[type=checkbox]').each(function () {
                if ($(this).is(":checked")) {
                    selected.push($(this).attr('value'));
                }
            });

            var amounts = 0;
            for (let i=0;i<selected.length;i++){
                amounts+=parseFloat($('#exp_'+selected[i]).html());
                payroll_ids.push(selected[i]);
            }
            if ($("#statement_amount").val() == amounts){
                $("#submit_expense_trans").show();;
            }else
                $("#submit_expense_trans").hide();;
            $('#expense_tag_amount').val(amounts);
            $('#expense_user_ids').val(payroll_ids.join(','));
        }
        function untag_entry(ind,id) {
            index = ind;
            if (confirm('Are you sure you want to Untag this entry?')) {
                $.ajax({
                    url: "<?php echo site_url();?>/accounts/untagentry/" + id,
                    type: 'GET',
                    processData: false,
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    contentType: false,
                    dataType: 'text',
                    success: function (data) {
                        $("#row-" + index).html('');
                        $("#div-show-" + index).show();
                    }
                });
            }else {

            }
        }
        function untag_bank_entry(ind,id) {
            index = ind;
            if (confirm('Are you sure you want to Untag this Bank entry?')) {
                $.ajax({
                    url: "<?php echo site_url();?>/accounts/untag_bank_entry/" + id,
                    type: 'GET',
                    processData: false,
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    contentType: false,
                    dataType: 'text',
                    success: function (data) {
                        $("#row-" + index).html('');
                        $("#div-show-" + index).show();
                    }
                });
            }else {

            }
        }
		
		function doWork(id)
		{
			//alert('Doing Work Please Wait.')
			setTimeout('$("#'+id+'").show()', 1500);
		}
		
        document.addEventListener( "DOMContentLoaded", function(){

			$("#ajax_submit_cash").on("click", function() {
				$(this).hide();
				doWork('ajax_submit_cash'); //this method contains your logic
			});
			$("#ajax_post_expense").on("click", function() {
				$(this).hide();
				doWork('ajax_post_expense'); //this method contains your logic
			});
            
			$("#add_expense_form").validate({
                submitHandler: function(form) {
                    var data = new FormData(form);
                    $.ajax({
                        url: "<?php echo site_url();?>/accounts/add_expense",
                        type: 'POST',
                        data: data,
                        processData: false,
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        contentType: false,
                        dataType: 'text',
                        success: function (data) {
                            $("#row-" + index).html('');
                            $("#row-" + index).html(data);
                            // $("#ac_campus_id").val("").trigger('change');
                            // $("#category_id").val("").trigger('change');
                            // $("#add_expense_form").trigger("reset")
                            $("#addexpense").modal('toggle');
                        }
                    });
                }
            });

            $("#submit_trans_form").validate({
                submitHandler: function(form) {
                    var data = new FormData(form);
                    $.ajax({
                        url: "<?php echo site_url();?>/accounts/tag_bank_trans",
                        type: 'POST',
                        data: data,
                        processData: false,
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        contentType: false,
                        dataType: 'text',
                        success: function (data) {
                            $("#row-"+index).html('');
                            $("#row-"+index).html(data);
                            $("#bank_transfer").modal('toggle');
                        }
                    });
                }
            });

            $("#add_cashinhand_form").validate({
                submitHandler: function(form) {
                    var data = new FormData(form);
                    $.ajax({
                        url: "<?php echo site_url();?>/accounts/add_cash_in_hand",
                        type: 'POST',
                        data: data,
                        processData: false,
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        contentType: false,
                        dataType: 'text',
                        success: function (data) {
                            $("#row-"+index).html('');
                            $("#row-"+index).html(data);
                            $("#add_cashinhand_form").trigger("reset")
                            $("#cashinhand").modal('toggle');
                        }
                    });
                }
            });

            $("#tag_paypro_trans_form").validate({
                submitHandler: function(form) {
                    var data = new FormData(form);
                    $.ajax({
                        url: "<?php echo site_url();?>/accounts/tag_paypro_trans",
                        type: 'POST',
                        data: data,
                        processData: false,
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        contentType: false,
                        dataType: 'text',
                        success: function (data) {
                            $("#row-"+index).html('');
                            $("#row-"+index).html(data);
                            $("#pay_pro_tag").modal('toggle');
                        }
                    });
                }
            });

            $("#salary_expense_form").validate({
                submitHandler: function(form) {
                    var data = new FormData(form);
                    $.ajax({
                        url: "<?php echo site_url();?>/accounts/tag_expense_trans",
                        type: 'POST',
                        data: data,
                        processData: false,
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        contentType: false,
                        dataType: 'text',
                        success: function (data) {
                            $("#row-"+index).html('');
                            $("#row-"+index).html(data);
                            $("#expense_tag").modal('toggle');
                        }
                    });
                }
            });

            $("select").select2("destroy").select2();

            $("#find_trans").click(function() {
                var data = new FormData(this.form);
                $.ajax({
                    url: "<?php echo site_url();?>/accounts/find_transactions",
                    type: 'POST',
                    data: data,
                    processData: false,
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    contentType: false,
                    dataType: 'text',
                    success: function (data) {
                        $(".modal-body #histtable tbody").html(data);
                        if (data != '')
                            $('#ajax_submit_trans').show();
                        else
                            $('#ajax_submit_trans').hide();
                    }
                });
                e.preventDefault();
            });

            $("#find_paypro_trans").click(function() {
                var data = new FormData(this.form);
                $.ajax({
                    url: "<?php echo site_url();?>/accounts/find_paypro_transactions",
                    type: 'POST',
                    data: data,
                    processData: false,
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    contentType: false,
                    dataType: 'text',
                    success: function (data) {
                        $(".modal-body #payprohisttable tbody").html(data);
                        if (data != '')
                            $('#ajax_submit_paypro').show();
                        else
                            $('#ajax_submit_paypro').hide();
                    }
                });
                e.preventDefault();
            });

            $("#find_expenses").click(function() {
                var data = new FormData(this.form);
                $.ajax({
                    url: "<?php echo site_url();?>/accounts/find_expense_transactions",
                    type: 'POST',
                    data: data,
                    processData: false,
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    contentType: false,
                    dataType: 'text',
                    success: function (data) {
                        $(".modal-body #expense_hist_table tbody").html(data);
                        $(".modal-body #submit_expense_trans tbody").hide();

                        if (data != '')
                            $('#submit_expense_trans').show();
                        else
                            $('#submit_expense_trans').hide();
                    }
                });
                e.preventDefault();
            });

            $("#ajax_submit_counsil_fee").click(function() {
                var data = new FormData(this.form);
                $.ajax({
                    url: "<?php echo site_url();?>/accounts/add_council_fee",
                    type: 'POST',
                    data: data,
                    processData: false,
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    contentType: false,
                    dataType: 'text',
                    success: function (data) {
                        $("#row-"+index).html('');
                        $("#row-"+index).html(data);
                        $("#council_fee").modal('toggle');
                    }
                });
                e.preventDefault();
            });

            $("#add_cash_deposit_form").validate({
                submitHandler: function(form) {
                    var data = new FormData(form);
                    $.ajax({
                        url: "<?php echo site_url();?>/accounts/add_cash_deposit",
                        type: 'POST',
                        data: data,
                        processData: false,
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        contentType: false,
                        dataType: 'text',
                        success: function (data) {
                            $("#row-"+index).html('');
                            $("#row-"+index).html(data);
                            $("#add_cash_deposit_form").trigger("reset")
                            $("#cash_deposit").modal('toggle');
                        }
                    });
                }
            });

            $("#find_profit_distribution").click(function() {
                var data = new FormData(this.form);
                $.ajax({
                    url: "<?php echo site_url();?>/accounts/find_profit_transactions",
                    type: 'POST',
                    data: data,
                    processData: false,
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    contentType: false,
                    dataType: 'text',
                    success: function (data) {
                        $(".modal-body #profihisttable tbody").html(data);
                        if (data != '')
                            $('#ajax_submit_profit').show();
                        else
                            $('#ajax_submit_profit').hide();
                    }
                });
                e.preventDefault();
            });

            $("#tag_profit_trans_form").validate({
                submitHandler: function(form) {
                    var data = new FormData(form);
                    $.ajax({
                        url: "<?php echo site_url();?>/accounts/add_profit_deposit",
                        type: 'POST',
                        data: data,
                        processData: false,
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        contentType: false,
                        dataType: 'text',
                        success: function (data) {
                            $("#row-"+index).html('');
                            $("#row-"+index).html(data);
                            $("#tag_share_distribution").modal('toggle');
                        }
                    });
                }
            });

            $("#find_loans").click(function() {
                var data = new FormData(this.form);
                $.ajax({
                    url: "<?php echo site_url();?>/accounts/find_loan_transactions",
                    type: 'POST',
                    data: data,
                    processData: false,
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    contentType: false,
                    dataType: 'text',
                    success: function (data) {
                        $(".modal-body #loanhisttable tbody").html(data);
                        if (data != '')
                            $('#ajax_submit_loan').show();
                        else
                            $('#ajax_submit_loan').hide();
                    }
                });
                e.preventDefault();
            });

            $("#tag_loan_trans_form").validate({
                submitHandler: function(form) {
                    var data = new FormData(form);
                    $.ajax({
                        url: "<?php echo site_url();?>/accounts/add_loan_deposit",
                        type: 'POST',
                        data: data,
                        processData: false,
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        contentType: false,
                        dataType: 'text',
                        success: function (data) {
                            $("#row-"+index).html('');
                            $("#row-"+index).html(data);
                            $("#tag_loan_modal").modal('toggle');
                        }
                    });
                }
            });

            $("#find_trans_reverse").click(function() {
                var data = new FormData(this.form);
                $.ajax({
                    url: "<?php echo site_url();?>/accounts/find_reverse_transactions",
                    type: 'POST',
                    data: data,
                    processData: false,
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    contentType: false,
                    dataType: 'text',
                    success: function (data) {
                        $(".modal-body #salary_histtable tbody").html(data);
                        $(".modal-body #ajax_submit_salary_reverse tbody").hide();

                        if (data != '')
                            $('#ajax_submit_salary_reverse').show();
                        else
                            $('#ajax_submit_salary_reverse').hide();
                    }
                });
                e.preventDefault();
            });

            $("#submit_trans_salary_form").validate({
                submitHandler: function(form) {
                    var data = new FormData(form);
                    $.ajax({
                        url: "<?php echo site_url();?>/accounts/tag_salary_reverse",
                        type: 'POST',
                        data: data,
                        processData: false,
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        contentType: false,
                        dataType: 'text',
                        success: function (data) {
                            if (data === 'success') {
                                alert('Salary Reversed Need to Reload Page');
                                location.reload();
                            }
                        }
                    });
                }
            });

            $(document).on("click", ".open_salary_view", function () {
                var myBookId = $(this).data('exp_id');
                var id = $(this).data('trans_id');
                $(".modal-body .bank_trans_id").val( id );
                $(".modal-body #salary_div_find").hide();
                $(".modal-body #salary_spinner").show();
                $(".modal-body #salary_histtable tbody").html('');

                $.ajax({
                    url: "<?php echo site_url();?>/accounts/find_salaries/"+myBookId,
                    type: 'GET',
                    processData: false,
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    contentType: false,
                    dataType: 'text',
                    success: function (data) {
                        $(".modal-body #salary_table tbody").html(data);
                        // $(".modal-body #submit_expense_trans tbody").hide();

                        if (data != '') {
                            $(".modal-body #salary_div_find").show();
                            $(".modal-body #salary_spinner").hide();
                        }
                        else {
                            $(".modal-body #salary_div_find").hide();
                            $(".modal-body #salary_spinner").hide();
                        }
                    }
                });
            });

            $(document).on('change', '.exps', function (e) {
                var exp_id = this.value;
                var con = $(this).data('count');
                jQuery.ajax({
                    type: "post",
                    async: false,
                    url: '<?php echo site_url()?>/expenses/getSubExpenses',
                    data: {
                        campus_id : exp_id,
                        expense_id : exp_id,
                        count : con,
                    },
                    success: function(data) {
                        if (data !="") {
                            con++;
                            for (let n=con;n<=count;n++){
                                console.log($('#div-'+n));
                                $('#div-'+n).remove();
                            }
                            jQuery('.exp_cats').append(data);
                            count = con;
                            $('#category_id'+(con)).select2();
                        }else {
                            con++;
                            for (let n=con;n<=count;n++){
                                jQuery('#div-'+n).remove();
                            }
                            count = con;
                        }
                    }
                });

                var category_val = jQuery(this).val();
                if(category_val==1)
                {
                    jQuery('.rickshaw').show();
                    jQuery('.custom_title').val('Auto Rickshaw');
                    jQuery('.rickshaw_column').attr('required','required');
                }
                else
                {
                    jQuery('.rickshaw').hide();
                    jQuery('.custom_title').val('');
                    jQuery('.rickshaw_column').val('');
                    jQuery('.rickshaw_column').removeAttr('required');

                }

                if(category_val==9)
                {
                    jQuery('.salary').show();
                    jQuery('.month_selector').show();

                    jQuery('#month_selector').attr('required','required');
                }
                else
                {
                    jQuery('.salary').hide();
                    jQuery('.month_selector').hide();
                    jQuery('#user_id').removeAttr('required');
                    jQuery('#month_selector').removeAttr('required');

                }
                if(category_val==13)
                {
                    jQuery('.type').show();
                    jQuery('.council_fee').show();
                    jQuery('.students_list').show();
                    jQuery('.amount').attr('placeholder','Enter per student council fee');
                }
                else
                {
                    jQuery('.type').hide();
                    jQuery('.council_fee').hide();
                    jQuery('.students_list').hide();
                    jQuery('.student_ids').val('');
                    jQuery('.amount').attr('placeholder','Enter Expense Amount');
                }

            });

            //$("#ajax_submit_paypro").click(function() {
            //    var data = new FormData(this.form);
            //    $.ajax({
            //        url: "<?php //echo site_url();?>///accounts/tag_paypro_trans",
            //        type: 'POST',
            //        data: data,
            //        processData: false,
            //        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            //        contentType: false,
            //        dataType: 'text',
            //        success: function (data) {
            //            $("#row-"+index).html('');
            //            $("#row-"+index).html(data);
            //            $("#pay_pro_tag").modal('toggle');
            //        }
            //    });
            //    e.preventDefault();
            //});
            //$("#submit_expense_trans").click(function() {
            //    var data = new FormData(this.form);
            //    $.ajax({
            //        url: "<?php //echo site_url();?>///accounts/tag_expense_trans",
            //        type: 'POST',
            //        data: data,
            //        processData: false,
            //        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            //        contentType: false,
            //        dataType: 'text',
            //        success: function (data) {
            //            $("#row-"+index).html('');
            //            $("#row-"+index).html(data);
            //            $("#expense_tag").modal('toggle');
            //        }
            //    });
            //    e.preventDefault();
            //});

        }, false );
</script>