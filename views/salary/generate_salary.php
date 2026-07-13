<style>
    .red{
        padding: 5px 10px!important;
    }
    .input-inline{
        font-size: 11px!important;
    }
</style>
<?php
$display_income_tax = (float) (@$income_tax ?: 0);
if ($display_income_tax <= 0) {
    $monthlyTaxableSalaryFallback = (float) @$staff->gross_salary;
    if ($monthlyTaxableSalaryFallback <= 0) {
        $monthlyTaxableSalaryFallback = (float) @$staff->salary;
    }

    if ($monthlyTaxableSalaryFallback > 0) {
        $payrollDateForTax = !empty($to_date) ? $to_date : date('Y-m-d');

        $taxYearFallback = $this->db
            ->where('start_date <=', $payrollDateForTax)
            ->where('end_date >=', $payrollDateForTax)
            ->where('status', 1)
            ->get('payroll_tax_years')
            ->row_array();

        if (!$taxYearFallback) {
            $today = date('Y-m-d');
            $taxYearFallback = $this->db
                ->where('start_date <=', $today)
                ->where('end_date >=', $today)
                ->where('status', 1)
                ->get('payroll_tax_years')
                ->row_array();
        }

        if ($taxYearFallback) {
            $annualIncomeFallback = $monthlyTaxableSalaryFallback * 12;

            $this->db->where('tax_year_id', $taxYearFallback['id']);
            $this->db->where('min_annual_income <=', $annualIncomeFallback);
            $this->db->group_start();
            $this->db->where('max_annual_income >=', $annualIncomeFallback);
            $this->db->or_where('max_annual_income IS NULL', null, false);
            $this->db->group_end();
            $this->db->where('status', 1);

            $taxSlabFallback = $this->db
                ->get('payroll_income_tax_slabs')
                ->row_array();

            if ($taxSlabFallback) {
                $annualTaxFallback = $taxSlabFallback['fixed_tax'] + (($annualIncomeFallback - $taxSlabFallback['taxable_amount_above']) * $taxSlabFallback['tax_percentage'] / 100);
                if ($annualTaxFallback < 0) {
                    $annualTaxFallback = 0;
                }
                $display_income_tax = round($annualTaxFallback / 12, 2);
            }
        }
    }
}
?>
<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="portlet-body form" >
            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/salary/storepayroll">
                <div class="form-body" style="
    padding: 0px;
">

                    <div class="row" style="margin:0px">
                        <div class="col-md-12" style="background-color: #ffffff;padding: 0px 0px 0px 0px;    border-radius: 10px!important;box-shadow: 1px 1px 16px -4px rgba(0,0,0,0.75);">
                            <h3 style="margin:0px 0px 20px 0px;padding: 10px;text-align: center;font-weight: bold;background-color: #009018;border-radius: 10px!important;color: #ffffff"><?php echo $staff->first_name; ?> <?php echo $staff->last_name; ?> Salary</h3>

                            <div class="form-body row">

                                <div class="col-md-3 col-sm-6 col-xs-6 form-group ">
                                    <label class="col-md-5 control-label">Staff Name :</label>
                                    <div class="col-md-7">
                                        <input type="text" class="form-control input-inline " name="" placeholder="" value="<?php echo $staff->first_name; ?> <?php echo $staff->last_name; ?>" readonly>
                                        <span class="help-inline"></span>
                                    </div>
                                </div>

                                <div class="col-md-3 col-sm-6 col-xs-6 form-group ">
                                    <label class="col-md-5 control-label">Gender:</label>
                                    <div class="col-md-7">
                                        <input type="text" class="form-control input-inline " name="" placeholder="" value="<?php echo $staff->gender; ?>" readonly>
                                        <span class="help-inline"></span>
                                    </div>
                                </div>

                                <div class="col-md-3 col-sm-6 col-xs-6 form-group ">
                                    <label class="col-md-5 control-label">Mobile No:</label>
                                    <div class="col-md-7">
                                        <input type="text" class="form-control input-inline " name="" placeholder="" value="<?php echo $staff->mobile; ?>" readonly>
                                        <span class="help-inline"></span>
                                    </div>
                                </div>

                                <div class="col-md-3 col-sm-6 col-xs-6 form-group ">
                                    <label class="col-md-5 control-label">Cnic:</label>
                                    <div class="col-md-7">
                                        <input type="text" class="form-control input-inline " name="" placeholder="" value="<?php echo $staff->cnic; ?>" readonly>
                                        <span class="help-inline"></span>
                                    </div>
                                </div>

                                <div class="col-md-3 col-sm-6 col-xs-6 form-group ">
                                    <label class="col-md-5 control-label">Maritual:</label>
                                    <div class="col-md-7">
                                        <input type="text" class="form-control input-inline " name="" placeholder="" value="<?php echo $staff->maritual_status; ?>" readonly>
                                        <span class="help-inline"></span>
                                    </div>
                                </div>

                                <div class="col-md-3 col-sm-6 col-xs-6 form-group ">
                                    <label class="col-md-5 control-label">Father:</label>
                                    <div class="col-md-7">
                                        <input type="text" class="form-control input-inline " name="" placeholder="" value="<?php echo $staff->father_name; ?>" readonly>
                                        <span class="help-inline"></span>
                                    </div>
                                </div>

                                <div class="col-md-3 col-sm-6 col-xs-6 form-group ">
                                    <label class="col-md-5 control-label">DOB:</label>
                                    <div class="col-md-7">
                                        <input type="text" class="form-control input-inline " name="" placeholder="" value="<?php echo $staff->date_of_birth; ?>" readonly>
                                        <span class="help-inline"></span>
                                    </div>
                                </div>

                                <div class="col-md-3 col-sm-6 col-xs-6 form-group ">
                                    <label class="col-md-5 control-label">City:</label>
                                    <div class="col-md-7">
                                        <input type="text" class="form-control input-inline " name="" placeholder="" value="<?php echo $staff->city; ?>" readonly>
                                        <span class="help-inline"></span>
                                    </div>
                                </div>

                                <div class="col-md-3 col-sm-6 col-xs-6 form-group ">
                                    <label class="col-md-5 control-label">Address:</label>
                                    <div class="col-md-7">
                                        <textarea class="form-control input-inline " rows="6" style="width: 100%" readonly><?php echo $staff->address; ?></textarea>
                                        <!--                                <input type="text" class="form-control input-inline " name="" placeholder="" value="" readonly >-->
                                        <span class="help-inline"></span>
                                    </div>
                                </div>

                                <div class="col-md-3 col-sm-6 col-xs-6 form-group ">
                                    <label class="col-md-5 control-label">EMG No:</label>
                                    <div class="col-md-7">
                                        <input type="text" class="form-control input-inline " name="" placeholder="" value="<?php echo $staff->emergency_no; ?>" readonly>
                                        <span class="help-inline"></span>
                                    </div>
                                </div>

                                <div class=" col-md-6 col-sm-6 col-xs-6 form-group">

                                    <div class=" col-md-2 value ml-20" data-toggle="tooltip" title="" data-original-title="Attandance" style="text-align: right">
                                        <strong>Attandance</strong>
                                    </div>

                                    <div class=" col-md-2 value ml-20" data-toggle="tooltip" title="" style="text-align: center; color: red" data-original-title="Present!" >
                                        Present <br>
                                        <input type="text" class="form-control input-inline " name="presents" value="<?php echo $present; ?>" readonly>
                                        <small style="display:block; margin-top: 6px;">
                                            Machine: <?php echo $present_machine; ?> | Manual: <?php echo $present_manual; ?>
                                        </small>
                                    </div>
                                    <div class="col-md-2 value ml-20" data-toggle="tooltip" title="" style="text-align: center; color: red" data-original-title="Holidays!">
                                        Holidays <br> <input type="text" class="form-control input-inline "  name="total_holidays" value="<?php echo @$holidays; ?>" readonly>
                                    </div>
                                    <div class="col-md-2 value ml-20" data-toggle="tooltip" title="" style="text-align: center; color: red" data-original-title="Late!">
                                        Leaves <br> <input type="text" class="form-control input-inline "  name="total_lates" value="<?php echo $leaves; ?>" readonly>
                                    </div>
                                    <div class="col-md-2 value ml-20" data-toggle="tooltip" title="" style="text-align: center; color: red" data-original-title="Absent!">
                                        Absent <br> <input type="text" class="form-control input-inline "  name="total_absents" value="<?php echo $absent; ?>" readonly>
                                    </div>
                                    <div class="col-md-2 value ml-20" data-toggle="tooltip" title="" style="text-align: center; color: red" data-original-title="Absent!">
                                        Counted Days <br> <input type="text" class="form-control input-inline " style="text-align: center; font-weight: bold" name="total_days" id="total_days" value="<?php echo $counted_days-$leaves-$absent ?>">
                                    </div>
                                </div>

                            </div>
                            <!--                    <div class="form-actions">-->
                            <!--                        <div class="row">-->
                            <!--                            <div class="col-md-12" style="text-align: center">-->
                            <!--                                <button type="submit" class="btn green">Check</button>-->
                            <!--                            </div>-->
                            <!--                        </div>-->
                            <!--                    </div>-->
                        </div>
                    </div sty>
                    <div class="row" style="margin-top: 20px">
                        <div class="col-md-3">
                            <div class="row">
                                <div class="col-md-1"></div>
                                <div class="col-md-10" style="background-color: #ffffff;padding: 0px 0px 10px 0px;    border-radius: 10px!important;box-shadow: 1px 1px 16px -4px rgba(0,0,0,0.75);">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <h3 style="margin:10px 0px 10px 10px;font-weight: bold">Earnings</h3>
                                        </div>
                                        <div class="col-md-3" style="text-align: center">
                                            <button class="add_details btn green" autocomplete="false" type="button"  style="margin:10px 0px 10px 0px "> <i class="fa fa-plus-square-o" aria-hidden="true"></i></button>
                                        </div>
                                    </div>
                                    <div class="ern-details">
                                        <?php foreach ($user_allowances as $data):
                                            if ($data['type']=="0"):
                                                ?>
                                                <div class="form-body user_data  row" style="padding-left:10px;margin-top: 5px ">
                                                    <div class="col-md-5" style="padding-right: 0px!important;">
                                                        <textarea class="form-control input-inline " rows="4" name="earningstype[]" id="ern_type" placeholder="Type"  style="width: 100%;"><?php echo $data['name'] ?></textarea>
                                                        <!--                                        <input type="text" class="form-control input-inline "  style="width: 100%!important;">-->
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="number" class="form-control input-inline " name="earningsValue[]" id="ern_val" placeholder="Value" value="<?php echo $data['amount'] ?>" style="width: 100%!important;">
                                                    </div>
                                                    <div class="col-md-2">
                                                    </div>
                                                </div>
                                            <?php endif; endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="row">
                                <div class="col-md-1"></div>
                                <div class="col-md-10" style="background-color: #ffffff;padding: 0px 0px 10px 0px;    border-radius: 10px!important;box-shadow: 1px 1px 16px -4px rgba(0,0,0,0.75);">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <h3 style="margin:10px 0px 10px 10px;font-weight: bold">Deduction</h3>
                                        </div>
                                        <div class="col-md-3" style="text-align: center">
                                            <button class="add_details_de btn green" autocomplete="false" type="button"  style="margin:10px 0px 10px 0px "> <i class="fa fa-plus-square-o" aria-hidden="true"></i></button>
                                        </div>
                                    </div>
                                    <div class="de-details">
                                        <?php foreach ($user_allowances as $data):

                                            if ($data['type']=="1"):
                                                ?>
                                                <div class="form-body user_data_de  row" style="padding-left:10px;margin-top: 5px ">
                                                    <div class="col-md-5" style="padding-right: 0px!important;">
                                                        <textarea class="form-control input-inline " rows="4" name="deductionstype[]" id="de_type" placeholder="Type"  style="width: 100%;"><?php echo $data['name'] ?></textarea>
                                                        <!--                                        <input type="text" class="form-control input-inline " name="deductionstype[]" id="de_type" placeholder="Type" style="width: 100%!important;">-->
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="number" class="form-control input-inline " name="deductionsValue[]" id="de_val" placeholder="Value" value="<?php echo $data['amount'] ?>" style="width: 100%!important;">
                                                        <input type="hidden" class="form-control input-inline " name="loanId[]" id="de_val" placeholder="Value" value="0" style="width: 100%!important;">
                                                    </div>
                                                    <div class="col-md-2">
                                                    </div>
                                                </div>
                                            <?php endif; endforeach;
                                        ?>
                                        <?php foreach ($loan as $data):?>
                                            <div class="form-body user_data_de  row" style="padding-left:10px;margin-top: 5px ">
                                                <div class="col-md-5" style="padding-right: 0px!important;">
                                                    <textarea class="form-control input-inline " rows="4" name="deductionstype[]" id="de_type" placeholder="Type"  style="width: 100%;"><?php echo "Loan installment (".$data['due_date'].")" ?></textarea>
                                                    <!--                                        <input type="text" class="form-control input-inline " name="deductionstype[]" id="de_type" placeholder="Type" style="width: 100%!important;">-->
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="number" class="form-control input-inline " name="deductionsValue[]" id="de_val" placeholder="Value" value="<?php echo $data['amount'] ?>" style="width: 100%!important;">
                                                    <input type="hidden" class="form-control input-inline " name="loanId[]" id="de_val" placeholder="Value" value="<?php echo $data['installment_id'] ?>" style="width: 100%!important;">
                                                </div><button type="button" class="remove-btn btn red" data-toggle="tooltip" data-placement="top" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
                                                <div class="col-md-2">
                                                </div>
                                            </div>
                                        <?php  endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="row">
                                <div class="col-md-1"></div>
                                <div class="col-md-10" style="background-color: #ffffff;padding: 0px 0px 10px 0px;border-radius: 10px!important;box-shadow: 1px 1px 16px -4px rgba(0,0,0,0.75);">
                        
                                    <h3 style="margin:10px 0px 10px 10px;font-weight: bold">Employer Contributions</h3>
                        
                                    <?php if (!empty($employer_contributions)): ?>
                                        <?php foreach ($employer_contributions as $contribution): ?>
                                            <div class="form-body row" style="padding-left:10px;margin-top: 5px;">
                                                <div class="col-md-6" style="padding-right: 0px!important;">
                                                    <textarea class="form-control input-inline" rows="4" style="width: 100%;" readonly><?php echo $contribution['name']; ?></textarea>
                                                </div>
                        
                                                <div class="col-md-6">
                                                    <input type="number"
                                                           class="form-control input-inline"
                                                           name="employer_contribution_amount[]"
                                                           value="<?php echo $contribution['amount']; ?>"
                                                           readonly
                                                           style="width: 100%!important;">
                        
                                                    <input type="hidden" name="employer_contribution_name[]" value="<?php echo $contribution['name']; ?>">
                                                    <input type="hidden" name="employer_contribution_rule_id[]" value="<?php echo $contribution['rule_id']; ?>">
                                                    <input type="hidden" name="employer_contribution_slab_id[]" value="<?php echo $contribution['slab_id']; ?>">
                                                    <input type="hidden" name="employer_contribution_base_salary[]" value="<?php echo isset($contribution['base_salary']) ? $contribution['base_salary'] : 0; ?>">
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p style="padding-left:10px;">No employer contributions</p>
                                    <?php endif; ?>
                        
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="row">
                                <div class="col-md-1"></div>
                                <div class="col-md-10" style="background-color: #ffffff;padding: 0px 0px 10px 0px;    border-radius: 10px!important;box-shadow: 1px 1px 16px -4px rgba(0,0,0,0.75);">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <h3 style="margin:10px 0px 10px 10px;font-weight: bold">Calculate</h3>
                                        </div>

                                    </div>
                                    <div class="">
                                        <div class="form-body user_data_de  row" style="padding-left:10px;margin-top: 5px;padding-right: 10px ">
                                            <div class="col-md-4" style="margin-bottom: 10px">
                                                <lable>Basic Salary : </lable>
                                            </div>
                                            <div class="col-md-8"  style="margin-bottom: 10px">
                                                <input type="text" class="form-control input-inline " name="basic_salary" value="<?php echo $staff->salary; ?>" id="basicSalary" placeholder="Salary" style="width: 100%!important;">
                                            </div>
                                            
                                            <div class="col-md-4" style="margin-bottom: 10px">
                                                <lable>Allownces : </lable>
                                            </div>
                                            <div class="col-md-8"  style="margin-bottom: 10px">
                                                <input type="text" class="form-control input-inline " name="allownce" value="<?php echo ($staff->gross_salary-$staff->salary); ?>" id="allownce" placeholder="Salary" style="width: 100%!important;">
                                            </div>
                                            
                                            <div class="col-md-4" style="margin-bottom: 10px">
                                                <lable>Earned Salary : </lable>
                                            </div>
                                            <div class="col-md-8" style="margin-bottom: 10px">
                                                <input type="text"
                                                       class="form-control input-inline"
                                                       name="earned_salary"
                                                       id="earned_salary"
                                                       value="0"
                                                       placeholder="Earned Salary"
                                                       style="width: 100%!important;"
                                                       readonly>
                                            </div>
                                            <div class="col-md-4" style="margin-bottom: 10px">
                                                <lable>Gross Salary : </lable>
                                            </div>
                                            <div class="col-md-8" style="margin-bottom: 10px">
                                                <input type="text" class="form-control input-inline " name="gross_salary" id="final_gross_salary"  value="0" placeholder="Gross Salary" style="width: 100%!important;">
                                            </div>
                                            <div class="col-md-4" style="margin-bottom: 10px">
                                                <lable>Earnings : </lable>
                                            </div>
                                            <div class="col-md-8" style="margin-bottom: 10px">
                                                <input type="text" class="form-control input-inline " name="earing_salary" id="total_earnings" placeholder="Earningslary" style="width: 100%!important;">
                                            </div>
                                            <div class="col-md-4" style="margin-bottom: 10px">
                                                <lable>Deduction : </lable>
                                            </div>
                                            <div class="col-md-8" style="margin-bottom: 10px">
                                                <input type="text" class="form-control input-inline " name="deduction_salary" id="total_deduction" placeholder="Deduction" style="width: 100%!important;">
                                            </div>
                                            <div class="col-md-4" style="margin-bottom: 10px">
                                                <lable>Tax : </lable>
                                            </div>
                                            <div class="col-md-8" style="margin-bottom: 10px">
                                                <input type="text" class="form-control input-inline " name="tax_salary" id="tax" value="<?php echo $display_income_tax; ?>" placeholder="Tax" style="width: 100%!important;" readonly>
                                            </div>
                                            <div class="col-md-4" style="margin-bottom: 10px">
                                                <lable>Salary Adjustment : </lable>
                                            </div>
                                            <div class="col-md-8" style="margin-bottom: 10px">
                                                <input type="text" class="form-control input-inline " name="minimum_salary_adjustment" id="minimum_salary_adjustment" value="<?php echo isset($staff->salary_adjustment) ? $staff->salary_adjustment : 0; ?>" placeholder="Salary Special Allowance" style="width: 100%!important;" readonly>
                                            </div>
                                            <div class="col-md-4" style="margin-bottom: 10px">
                                                <lable>Gross Salary + Adjustment : </lable>
                                            </div>
                                            <div class="col-md-8" style="margin-bottom: 10px">
                                                <input type="text" class="form-control input-inline " name="gross_salary_adjustment" id="gross_salary_adjustment" value="0" placeholder="Gross Salary + Adjustment" style="width: 100%!important;" readonly>
                                            </div>
                                            <div class="col-md-4" style="margin-bottom: 10px">
                                                <lable>Net Salary : </lable>
                                            </div>
                                            <div class="col-md-8" style="margin-bottom: 10px">
                                                <input type="text" class="form-control input-inline " name="net_salary" id="net_salary" placeholder="Net salary" style="width: 100%!important;">
                                            </div>
                                            <div class="col-md-12" style="margin-bottom: 10px; text-align: center">
                                                <button class=" btn green"  onclick="calculateSalary()" autocomplete="false" type="button"  >CALCULATE SALARY NOW</i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6"></div>
                    <div class="col-md-6"></div>
                </div>
                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-offset-3 col-md-9">
                            <input type="hidden" name="user_id" class="user_id" value="<?php echo $user_id ?>" />
                            <input type="hidden" name="campus_id" class="campus_id" value="<?php echo $campus_id ?>" />
                            <input type="hidden" name="month" value="<?php echo $this->uri->segment(5); ?>" />
                            <input type="hidden" name="year" value="<?php echo $this->uri->segment(6); ?>" />
                            <button type="submit" id="generate_now_btn" class="btn green">Generate Now</button>
                            <div id="pettycash_adjustment_error" class="alert alert-danger" style="display: none; margin: 0;">
                                Petty cash account is not created for this employee. Please create petty cash first.
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener( "DOMContentLoaded", function(){

        // $("#my_month").val(daysInThisMonth());
        // $("#total_days").val(daysInThisMonth());

    }, false );
    
    function calculateSalary() {

        var basicSalary = parseFloat("<?php echo isset($staff->salary) && $staff->salary !== '' ? $staff->salary : 0; ?>") || 0;
        var staffGrossSalary = parseFloat("<?php echo isset($staff->gross_salary) && $staff->gross_salary !== '' ? $staff->gross_salary : 0; ?>") || 0;
        var staffSalaryAdjustment = parseFloat("<?php echo isset($staff->salary_adjustment) && $staff->salary_adjustment !== '' ? $staff->salary_adjustment : 0; ?>") || 0;
    
        var total_days = parseFloat($("#total_days").val()) || 0;
        var monthDays = daysInThisMonth();
    
        if (!monthDays || monthDays <= 0) {
            monthDays = 30;
        }
    
        if (total_days > monthDays) {
            alert("Days in a month must be less than or equal to " + monthDays);
            total_days = monthDays;
            $("#total_days").val(monthDays);
        }
    
        if (basicSalary <= 0) {
            alert('Please Add Employees Basic Salary from Staff Update Form First');
            return false;
        }
    
        var total_earnings = 0;
        var total_deduction = 0;
    
        var allownces = <?php echo ($staff->gross_salary-$staff->salary) ?>;
        var earningsValue = document.getElementsByName('earningsValue[]');
        var deductionsValue = document.getElementsByName('deductionsValue[]');
    
        for (var i = 0; i < earningsValue.length; i++) {
            var earningAmount = parseFloat(earningsValue[i].value) || 0;
            total_earnings += earningAmount;
        }
    
        for (var j = 0; j < deductionsValue.length; j++) {
            total_deduction += parseFloat(deductionsValue[j].value) || 0;
        }
    
        var monthlyTax = parseFloat("<?php echo $display_income_tax; ?>") || 0;
    
        var gross_salary = basicSalary + allownces;

        var perday = gross_salary / monthDays;
        
        // Earned salary as per working/counted days
        
        var earned_salary = perday * total_days;
        
        if (!earned_salary || earned_salary < 0) {
        
            earned_salary = 0;
        
        }
        
        var incentives = total_earnings - allownces;
        var salaryBeforeDeductions = earned_salary + incentives;
        var taxableBase = basicSalary + allownces;
        var adjustedTax = 0;
        if (taxableBase > 0) {
            adjustedTax = (monthlyTax * salaryBeforeDeductions) / taxableBase;
        }
        if (!adjustedTax || adjustedTax < 0) {
            adjustedTax = 0;
        }
        var tax = adjustedTax;
        var minimumSalaryAdjustment = staffSalaryAdjustment;

        var net_salary = salaryBeforeDeductions - total_deduction - tax;
        var adj = salaryBeforeDeductions + minimumSalaryAdjustment - total_deduction - tax;
        
        // console.log({
        //     basicSalary: basicSalary,
        //     total_earnings: total_earnings,
        //     gross_salary: gross_salary,
        //     total_deduction: total_deduction,
        //     tax: tax,
        //     leaveDeduct: leaveDeduct,
        //     net_salary: net_salary,
        //     total_days: total_days
        // });
    
        $("#total_earnings").val((total_earnings-allownces).toFixed(0));
        $("#total_deduction").val(total_deduction.toFixed(0));
        $("#final_gross_salary").val(gross_salary.toFixed(0));
        $("#minimum_salary_adjustment").val(minimumSalaryAdjustment.toFixed(0));
        $("#net_salary").val(net_salary.toFixed(0));
        $("#earned_salary").val(earned_salary.toFixed(0));
        $("#tax").val(tax.toFixed(0));

        $("#gross_salary_adjustment").val(adj.toFixed(0));

        togglePettycashAdjustmentError(minimumSalaryAdjustment);
    
        return false;
    }

    function togglePettycashAdjustmentError(minimumSalaryAdjustment) {
        var hasPettycash = <?php echo empty($salary_pettycash) ? 'false' : 'true'; ?>;

        if (!hasPettycash && minimumSalaryAdjustment > 0) {
            $("#generate_now_btn").hide();
            $("#pettycash_adjustment_error").show();
        } else {
            $("#generate_now_btn").show();
            $("#pettycash_adjustment_error").hide();
        }
    }
    
    function daysInThisMonth() {
        var month = '<?php echo $this->uri->segment(5); ?>';
        var year = parseInt('<?php echo $this->uri->segment(6); ?>');
    
        const monthIndex = new Date(`${month} 1, ${year}`).getMonth(); // 4
        console.log("Ali"+month);
        console.log("Ali"+monthIndex);
        return new Date(year, monthIndex + 1, 0).getDate();
    }


</script>
<!-- END CONTENT -->
