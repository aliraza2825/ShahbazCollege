<style>
    .button-tag {
        background-color: #e3a600;
        border: none;
        color: white;
        padding: 12px 24px;
        font-size: 16px;
        border-radius: 4px;
        margin: 20px;
    }
    .fata {
        margin-left: -12px;
        margin-right: 8px;
    }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-6" style="background-color: white;padding: 20px 30px;    border-radius: 20px!important;box-shadow: 10px 10px 5px -8px rgba(0,0,0,0.75);">
                <h3 style="margin:5px 0px 20px 0px;text-align: center;font-weight: bold">Select Campus To Generate Salary</h3>
                <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/salary/salary_report">
                    <div class="form-body">
                        <div class="form-group">
                            <label class="col-md-2 control-label">Campus <span class="required">*</span></label>
                            <div class="col-md-8">
                                <select class="form-control campus" name="campus_id">
                                    <option value="">Select CAMPUS</option>
                                    <?php
                                    foreach($campuses as $campus):
                                        ?>
                                        <option value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name'];?></option>
                                    <?php
                                    endforeach;
                                    ?>
                                </select>
                                <!--<span class="help-inline"></span>-->
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label class="control-label col-md-3">Salary Month</label>
                        <div class="input-group input-medium date date-picker col-md-9"
                             data-date="<?php echo @$to_date; ?>"
                             data-date-format="yyyy-mm"
                             data-date-viewmode="years"
                             data-date-minviewmode="months">
                             
                            <input type="text" name="to_date" class="form-control" value="<?php echo @$to_date; ?>" readonly>
                            
                            <span class="input-group-btn">
                                <button class="btn default" type="button">
                                    <i class="fa fa-calendar"></i>
                                </button>
                            </span>
                        </div>
                    </div>

                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-12" style="text-align: center">
                                <button type="submit" class="btn green">Check</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php if(@$salary != ""){
            $advance=0;
            $user_alownce=0;
            $loan=0;
            $gross=0;
            $deductions=0;
            $earnings=0;
            $earnedsalary=0;
            $grosssals=0;
            $specials=0;
            $exp_ids = array();
            $pending = 0;
            $cash = 0;
            $bank = 0;
            ?>

            <button class="btn green" id="print" onclick="printContent('printtable');" >Print</button>

            <div class="row" style="margin-top: 20px">
                <div class="col-md-12" id="printtable">
                    <!-- BEGIN EXAMPLE TABLE PORTLET-->
                    <div class="portlet box grey-cascade">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-list"></i> Staff Salary List ( <?php echo @$salary[0]['campus_name'];?> ) For The Month of <?php echo $month.' - '.$year ?>

                            </div>
                        </div>
                        <div class="portlet-body"  id="checkboxes">
                            <button type="button" class="btn red" id="removeContributionBtn">
                                Remove Contribution
                            </button>
                            
                            <button type="button" class="btn green" id="addContributionExpenseBtn">
                                Add Expense
                            </button>
                            <br><br>
                            <table class="table table-striped table-bordered table-hover" id="">
                                <thead>
                                <tr>
                                    <th>
                                        Staff.No
                                    </th>
                                    <th>
                                        Campus Name
                                    </th>
                                    <th>
                                        Name
                                    </th>
                                    <th>
                                        Desigination
                                    </th>
									<th>
                                        Department
                                    </th>
									<th>
                                        Basic Salary
                                    </th>
									<th>
                                        Allownces
                                    </th>
                                    <th>
                                        Salary
                                    </th>
                                    <th>
                                        No of Days
                                    </th>
                                    <th>
                                        Earned Salary
                                    </th>
									<th>
                                        Incentive
                                    </th>
                                    <!--<th>
                                        Bonus
                                    </th>-->
									<!--<th>
                                        Gross Earnings
                                    </th>-->
                                    <th>
                                        Special Deductions
                                    </th>
                                    <!--<th>
                                        Actual Salary
                                    </th>-->
                                    <th>
                                        Advance Salary
                                    </th>
                                    <th>
                                        Loan
                                    </th>
                                    <th>
                                        Tax
                                    </th>
                                    <th>
                                        Total Earned Salary
                                    </th>
                                    <th>
                                        Disburse Status
                                    </th>
                                    <?php foreach($stat_rules as $rul): ?>
                                    <th>
                                        <?php echo $rul['rule_name']; ?>
                                    </th>
                                    <?php endforeach ?>
                                    <!--<th>-->
                                    <!--    Action-->
                                    <!--</th>-->

                                </tr>
                                </thead>
                                <tr>
                                    <?php
                                    $i=0;
                                    $thisMonthTotalBasicSalary=0;
                                    foreach($salary as $list):
                                        if ($list['expense_id'] != NULL)
                                            array_push($exp_ids,$list['expense_id']);
                                    ?>
                                    <tr class="odd gradeX">
                                        <td >
                                        <?php if($list['disburse_through'] == "pending"):
                                            $pending+=$list['earned_salary'];
                                            ?>
                                            <input type="checkbox"  class="selection "  onchange="getselected()" name="selection" value="<?php echo $i;?>" />
                                        <?php endif;
                                            if($list['disburse_through'] == "cash")
                                                $cash+=$list['earned_salary'];

                                            if($list['disburse_through'] == "bank")
                                                $bank+=$list['earned_salary'];

                                        ?>
                                            <?php echo $i+1;?>
                                        </td>
                                        <td>
                                            <?php echo $list['campus_name'];?>
                                        </td>
                                        <td>
                                            <?php echo $list['first_name'];?> <?php echo $list['last_name'];?>
                                        </td>
                                        <td>
                                            <?php echo $list['designation'];?>
                                        </td>
										<td>
                                            <?php echo $list['department'];?>
                                        </td>
										<td style="text-align: right;">
                                            <?php echo round($list['basic_salary']);?>
                                            <?php $gross+=round($list['basic_salary']); ?>
                                        </td>
										<td style="text-align: right;">
                                            <?php echo $list['new_user_alownce'];?>
                                            <?php $earnings+=round($list['new_user_alownce']); ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <?php echo round($list['basic_salary'] + $list['new_user_alownce']);?>
                                            <?php $grosssals+=round($list['gross_salary']);?>
                                        </td>
                                        <td style="text-align: right;">
                                            <?php echo $list['no_of_days'];?>
                                        </td>
                                        <td>
                                            <?php
                                                $month = date('m',strtotime($this->input->post('to_date')));
                                                $year = date('Y',strtotime($this->input->post('to_date')));
                                                $daysthismonth=cal_days_in_month(CAL_GREGORIAN,$month,$year);
                                                $oneDaySalary = ($list['basic_salary'] + $list['new_user_alownce'])/$daysthismonth;
                                                $presentDaysSalary = $oneDaySalary*$list['no_of_days'];
                                                echo number_format($presentDaysSalary);
                                                $thisMonthTotalBasicSalary+=$presentDaysSalary;
                                            ?>
                                        </td>
										<td style="text-align: right;">
                                            <?php
                                            if ($list['earnings']-$list['new_user_alownce'] > 0):
                                                echo round($list['earnings']-$list['new_user_alownce']);?>
                                                <?php $user_alownce+=round(($list['earnings']-$list['new_user_alownce']));
                                                else:
                                                echo "0";
                                            endif;?>
                                        </td>
                                        <!--<td>
                                            <?php //echo $list['special'];?>
                                            <?php //$specials+=$list['special']; ?>
                                        </td>-->
										<!--<td style="text-align: right;">
                                            <?php //echo ($list['earnings']-$list['user_alownce'])+$list['gross_salary'];?>
                                        </td>-->
                                        <td style="text-align: right;">
                                            <?php echo round($list['deductions']-$list['advance']-$list['loan']);?>
                                            <?php $deductions+=round($list['deductions']-$list['advance']-$list['loan']);?>
                                        </td>
                                        <!--<td>

                                        </td>-->
                                        <td style="text-align: right;">
                                            <?php echo $list['advance'];?>
                                            <?php $advance+=$list['advance'];?>
                                        </td>
                                        <td style="text-align: right;">
                                            <?php echo $list['loan'];?>
                                            <?php $loan+=$list['loan']; ?>
                                        </td>
                                        <td>
                                            <?php echo $list['tax']; ?>
                                        </td>
<!--                                        --><?php //if($iscampus == 'false'): ?>

<!--										--><?php //endif; ?>
                                        <td >
<!--										--><?php //if($iscampus == 'false'){
//												echo ($list['earned_salary']);
//										}else{
											echo $list['earned_salary'];
//										}?>
                                            <?php $earnedsalary+=$list['earned_salary']; ?>
                                        </td>
                                        <td>
                                            <?php echo strtoupper($list['disburse_through']);?>
                                        </td>
                                        <?php foreach($stat_rules as $rul): 
                                            $contribution = $this->db->get_where('payroll_statutory_contributions', array(
                                                "payroll_id" => $list['id'],
                                                "rule_id" => $rul['id']
                                            ))->row_array();
                                        ?>
                                        <td>
                                            <?php if($contribution): 
                                                $expense = $this->db->get_where('expenses', array(
                                                    "expense_id" => $contribution['expense_id']
                                                ))->row_array();
                                                if(!$expense):
                                            ?>
                                                    <label>
                                                        <input 
                                                            type="checkbox"
                                                            class="contribution-checkbox"
                                                            value="<?php echo $contribution['id']; ?>"
                                                            data-payroll-id="<?php echo $list['id']; ?>"
                                                            data-rule-id="<?php echo $rul['id']; ?>"
                                                            data-user-name="<?php echo $list['first_name'].' '.$list['last_name']; ?>"
                                                            data-rule-name="<?php echo $rul['rule_name']; ?>"
                                                            data-amount="<?php echo $contribution['employer_amount']; ?>"
                                                        >
                                                    <?php echo round($contribution['employer_amount']); ?>
                                                </label>
                                                <?php else: ?>
                                                    <label>Expense Added : <?php echo $contribution['employer_amount']; ?></label>
                                            <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <?php endforeach ?>
                                        <!--<td>-->
                                        <!--    <a href="<?php echo site_url().'/salary/salary_view/'.$list['user_id'].'/'.$month.'/'.$year;?>" class="btn green"><i class="fa fa-eye" aria-hidden="true"></i></a>-->
                                        <!--</td>-->
                                    </tr>
                                    <?php
                                    $i++;
                                endforeach;
                                ?>
                                <tr>
                                    <th>

                                    </th>

                                    <th>

                                    </th>
                                    <th>

                                    </th>

                                    <th>

                                    </th>
                                    <th>

                                    </th>
                                    <th style = "font-weight:bold; text-align: right;">
                                        <?php echo $gross ?>
                                    </th>
                                    <th style = "font-weight:bold; text-align: right;">
                                        <?php echo $earnings ?>
                                    </th>
                                    <th style = "font-weight:bold; text-align: right;">
                                        <?php echo $grosssals ?>
                                    </th>
                                    <th style = "font-weight:bold; text-align: right;">
                                        <?php //echo $user_alownce ?>
                                    </th>
                                    <th>
                                        <?php echo number_format($thisMonthTotalBasicSalary);//$specials ?>
                                    </th>
                                    <th style = "font-weight:bold; text-align: right;">
                                        <?php echo $user_alownce;//$user_alownce+$grosssals ?>
                                    </th>
                                    <th style = "font-weight:bold; text-align: right;">
                                        <?php echo $deductions; ?>
                                    </th>
                                    <th style = "font-weight:bold; text-align: right;">
                                        <?php echo $advance ?>
                                    </th>
                                    <th style = "font-weight:bold; text-align: right;">
                                        <?php echo $loan ?>
                                    </th>
                                    <th style = "font-weight:bold; text-align: right;">
                                    <?php echo $earnedsalary ?>
                                </th>
                                </tr>

                                </tbody>
                            </table>
                        </div>

                    </div>
                    <!-- END EXAMPLE TABLE PORTLET-->
                </div>
            </div>

            <div class="portlet-body">
                <table class="table table-striped table-bordered table-hover" id="">
                    <thead>
                    <tr>

                        <th style ="font-weight:bold; text-align: center;">
                            Pending Amount
                        </th>
                        <th style ="font-weight:bold; text-align: center;">
                            Cash Disbursed
                        </th>
                        <th style ="font-weight:bold; text-align: center;">
                            Bank Disbursed
                        </th>
                        <th style ="font-weight:bold; text-align: center;">
                            Total Disbursed
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr>
                        <th style = "font-weight:bold; text-align: center;">
                            <?php echo $pending ?>
                        </th>
                        <th style = "font-weight:bold; text-align: center;">
                            <?php echo $cash ?>
                        </th>
                        <th style = "font-weight:bold; text-align: center;">
                            <?php echo $bank ?>
                        </th>
                        <th style = "font-weight:bold; text-align: center;">
                            <?php echo $cash+$bank ?>
                        </th>
                    </tbody>
                </table>
            </div>

            <?php if ($iscampus == 'true'):
                if (count($exp_ids)>0):
                        $expenses = $this->db
                                    ->select('expenses.*,accounts.account_name,bank_reconciliation_statement.description,campuses.campus_name,bank_reconciliation_statement.debit')
                                    ->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left')
                                    ->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left')
                                    ->join('bank_reconciliation_statement', 'bank_reconciliation_statement.id=expenses.bank_statement_id', 'left')
                                    ->join('accounts', 'accounts.id=bank_reconciliation_statement.account_id', 'left')
                                    ->where_in("expenses.expense_id",$exp_ids)->get("expenses")->result_array();  
                                    
                        // echo '<pre>';
                        // print_r($salary);
                        // echo '</pre>';
                        ?>

                <?php
                if(count($expenses)>0 ):
                    ?>
                    <div class="portlet-body">
                        <div class="alert alert-success">
                            <p>Total expense for this Salary Are</p>
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
                                    Given To
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
                                <th>
                                    Type
                                </th>
                                <th>
                                    Bank Detail
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
                                if ($expense['amount'] >0):
                                ?>
                                <tr class="odd gradeX">

                                    <td class="hidden">
                                        <?php echo $i;?>
                                    </td>

                                    <td>
                                        <?php echo $expense['campus_name'].' '.$expense['expense_id'];?>
                                    </td>

                                    <td>
                                        <?php
                                        if($expense['expense_category_id']==9):
                                            echo $expense['name'];
                                            echo '<br />';
                                            $user = $this->db->get_where('users', array('user_id'=>$expense['user_id']))->result_array();
                                            echo @$user[0]['first_name'].' '.@$user[0]['last_name'];
                                        elseif($expense['expense_category_id']==36):
                                            $payrolls = $this->db->join('users','users.user_id = payroll.user_id')->get_where('payroll', array('expense_id'=>$expense['expense_id']))->result_array();
                                            foreach($payrolls as $payroll){
                                                echo $payroll['first_name'].' '.$payroll['last_name'].' ( '.$payroll['earned_salary'].' )'.'<br>';
                                            }
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
                                        <?php echo $expense['add_by']?>
                                    </td>

                                    <td>
                                        <?php echo strtoupper($expense['paid_type'])?>
                                    </td>

                                    <td>
                                        <?php if ($expense['bank_statement_id'] != NULL){

                                            echo '<strong> Account Detail : </strong>'.$expense['account_name'].'<br>'.
                                                '<strong> Description : </strong>'.$expense['description'].'<br>'.
                                                '<strong> Amount : </strong>'.$expense['debit'].'<br>'
                                            ;

                                            $this->db->select('*');
                                            $this->db->from('expenses');
                                            $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
                                            $this->db->join('campuses', 'campuses.campus_id=expenses.campus_id', 'left');
                                            $salary_expenses=$this->db->where(array('bank_statement_id'=>$expense['bank_statement_id']))->get()->result_array();

                                            foreach ($salary_expenses as $sals): ?>
                                                <strong>Expense Category : </strong><?php echo $sals['name'];?> <br>
                                                <strong>Campus : </strong><strong style="color: #00CC00"><?php echo $sals['campus_name'];?> </strong><br>
                                                <strong>Amount : </strong><?php echo $sals['amount'];?> <br>
                                                <strong>Add By : </strong><?php echo $sals['add_by']?>
<!--                                                <a data-toggle="modal" data-exp_id="--><?php //echo $sals['expense_id']?><!--" data-trans_id="--><?php //echo $closing_rule['trans_id'] ?><!--" title="View Salary Persons" class="open_salary_view" href="#salary_reverse_modal">-->
<!--                                                    <i class="fa fa-eye"></i>-->
<!--                                                </a>-->
                                                <br>
                                            <?php
                                            endforeach;

                                        }?>
                                    </td>

                                    <td>
                                        <?php
//                                        echo @$myAccess[0]['salary_expense_delete']." Hello";
//                                        if(@$myAccess[0]['salary_expense_delete']==1 || $this->session->userdata('role')=='Admin'):  ?>
                                            <a onclick="return confirm('Are you sure you want to delete this Expense?')" href="<?php echo site_url();?>/salary/delete_expense/<?php echo $expense['expense_id'];?>" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>
                                        <?php
//                                        endif;
                                        ?>
                                    </td>

                                </tr>
                                <?php
                                $i++;
                                endif;
                            endforeach;
                            ?>
                            </tbody>
                        </table>
                    </div>
                <?php
                endif;
                ?>
            <?php
            endif;
            endif;
            if ($iscampus == 'true'):
            if (count($disbursed)==0):?>
                <form class="form-horizontal" id="new-payroll-form" role="form" method="post">
                    <div class="row">
                        <input type="hidden"  value="<?php echo $month ?>" id="month" name="month" readonly>
                        <input type="hidden"  value="<?php echo $year ?>" id="year" name="year" readonly>
                        <input type="hidden"  value="<?php echo $my_campus; ?>" id="campus" name="campus" readonly>
                        <input type="hidden"  value="<?php echo 0; ?>" id="salary_ids" name="payroll_ids" readonly>
                        <input type="hidden"  value="0" id="disburse_amount" name="disburse_amount" readonly>

                        Disburse Salary :  <input type="text"  STYLE="text-align: center; margin-left: 20px; font-weight: bolder; font-size: large" value="<?php echo 0 ?>" id="receivable_amount" name="receivable_amount" readonly>
                        <br />
                        <br />
                        <br />
                        <button type="button" id="cash" class="btn green">Disburse by Cash</button>
                        <button id="loading_button" style="display: none" class="button-tag" type="button">
                            <i class="fata fa fa-spinner fa-spin"></i>Submitting
                        </button>
                        <button type="button" id="bank" class="btn green">Disburse by Bank</button>
                    </div>
                </form>
            <?php
                else:
                    echo '<button type="Button" id="cash" class="btn btn-primary">Disbursed with '.$disbursed[0]['amount'].'</button>';
                endif;
            endif;?>

        <?php
        } ?>
    </div>
</div>

<div class="modal fade" id="contributionExpenseModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document" style="width: 98%; max-width: 900px;">
        <form id="contributionExpenseForm" method="post" enctype="multipart/form-data">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title">Add Contribution Expense</h4>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="contribution_ids" id="modal_contribution_ids">
                    <input type="hidden" name="payroll_ids" id="modal_payroll_ids">
                    <input type="hidden" name="rule_ids" id="modal_rule_ids">

                    <div class="form-group">
                        <label>Selected Contributions</label>
                        <textarea class="form-control" id="selected_contribution_detail" readonly rows="5"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Total Amount</label>
                        <input type="text" name="amount" id="modal_total_amount" class="form-control" readonly>
                    </div>

                    <div class="form-group">
                        <label>Comment</label>
                        <textarea name="purpose" class="form-control" required></textarea>
                    </div>

                    <div class="form-group">
                        <label>Image / Receipt</label>
                        <input type="file" name="image" class="form-control" accept="image/*" required>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn green">Submit Expense</button>
                </div>

            </div>
        </form>
    </div>
</div>
<!-- END CONTENT -->

<script>
    function getselected() {
        var selected = [];
        var payroll_ids = [];
        $('div#checkboxes input[type=checkbox]').each(function () {
            if ($(this).is(":checked")) {
                selected.push($(this).attr('value'));
            }
        });

        let loans = <?php echo json_encode($salary) ?>;
        var amounts = 0;

        for (let i=0;i<selected.length;i++){
            amounts+=parseFloat(loans[selected[i]].earned_salary);
            payroll_ids.push(loans[selected[i]].id);
        }
        $('#disburse_amount').val(amounts);
        $('#receivable_amount').val(amounts);
        $('#salary_ids').val(payroll_ids.join(','));
    }

    function printContent(el){
        var restorepage = $('body').html();
        var printcontent = $('#' + el).clone();
        $('body').empty().html(printcontent);
        window.print();
        $('body').html(restorepage);
    }
    
    document.addEventListener("DOMContentLoaded", function(event) {
        
        function getCheckedContributions() {
            var contribution_ids = [];
            var payroll_ids = [];
            var rule_ids = [];
            var details = [];
            var total = 0;
        
            $('.contribution-checkbox:checked').each(function () {
                contribution_ids.push($(this).val());
                payroll_ids.push($(this).data('payroll-id'));
                rule_ids.push($(this).data('rule-id'));
        
                var amount = parseFloat($(this).data('amount')) || 0;
                total += amount;
        
                details.push(
                    $(this).data('user-name') + ' - ' +
                    $(this).data('rule-name') + ' : ' +
                    amount
                );
            });
        
            return {
                contribution_ids: contribution_ids,
                payroll_ids: payroll_ids,
                rule_ids: rule_ids,
                details: details,
                total: total
            };
        }

        $('#addContributionExpenseBtn').click(function () {
            var data = getCheckedContributions();
        
            if (data.contribution_ids.length === 0) {
                alert('Please select contribution first');
                return false;
            }
        
            $('#modal_contribution_ids').val(data.contribution_ids.join(','));
            $('#modal_payroll_ids').val(data.payroll_ids.join(','));
            $('#modal_rule_ids').val(data.rule_ids.join(','));
            $('#selected_contribution_detail').val(data.details.join("\n"));
            $('#modal_total_amount').val(data.total);
        
            $('#contributionExpenseModal').modal('show');
        });
        
        $('#removeContributionBtn').click(function () {
            var data = getCheckedContributions();
        
            if (data.contribution_ids.length === 0) {
                alert('Please select contribution first');
                return false;
            }
        
            if (!confirm('Are you sure you want to remove selected contributions?')) {
                return false;
            }
        
            $.ajax({
                url: '<?php echo site_url();?>/salary/remove_contributions',
                type: 'post',
                dataType: 'json',
                data: {
                    contribution_ids: data.contribution_ids.join(',')
                },
                success: function (res) {
                    if (res.error == '') {
                        location.reload();
                    } else {
                        alert(res.error);
                    }
                }
            });
        });
        
        $('#contributionExpenseForm').submit(function (e) {
            e.preventDefault();
        
            var formData = new FormData(this);
        
            $.ajax({
                url: '<?php echo site_url();?>/salary/add_contribution_expense',
                type: 'post',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (res) {
                    if (res.error == '') {
                        location.reload();
                    } else {
                        alert(res.error);
                    }
                }
            });
        });
        
        $('.date-picker').datepicker({
            format: 'yyyy-mm',
            viewMode: 'years',
            minViewMode: 'months',
            autoclose: true
        });
        
        $("#cash").click(function(e) {
            if ($('#salary_ids').val() != "" && $('#salary_ids').val() != "0") {
                $("#cash").hide();
                $("#bank").hide();
                $("#loading_button").show();
                var formData = new FormData($('#new-payroll-form')[0]);
                formData.append("type", "cash");
                jQuery.ajax({
                    url: '<?php echo site_url();?>/salary/insert_expense',
                    method: 'post',
                    processData: false,
                    contentType: false,
                    cache: false,
                    data: formData,
                    dataType: 'json',
                    success: function (data) {
                        if (data.error == ""){
                            location.reload();
                        }else {
                            alert(data.error);
                        }
                        $("#cash").show();
                        $("#bank").show();
                        $("#loading_button").hide();
                    },
                    complete: function (data) {

                    }
                });
                e.preventDefault();
            }else {
                alert("Please select Salaries");
            }
        });

        $("#bank").click(function(e) {
            if ($('#salary_ids').val() != "" && $('#salary_ids').val() != "0") {
                $("#cash").hide();
                $("#bank").hide();
                $("#loading_button").show();
                var formData = new FormData($('#new-payroll-form')[0]);
                formData.append("type", "bank");
                jQuery.ajax({
                    url: '<?php echo site_url();?>/salary/insert_expense',
                    method: 'post',
                    processData: false,
                    contentType: false,
                    data: formData,
                    dataType: 'json',
                    success: function (data) {
                        if (data.error == ""){
                            location.reload();
                        }else {
                            alert(data.error);
                        }
                        $("#cash").show();
                        $("#bank").show();
                        $("#loading_button").hide();
                    },
                    complete: function (data) {

                    }                });
                e.preventDefault();
            }else {
                alert("Please select Salaries");
            }
        });
    });
</script>