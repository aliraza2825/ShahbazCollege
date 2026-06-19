<?php
$myAccess = checkUserAccess();
?>

<div class="page-content-wrapper">
    <div class="page-content">

        <div class="row">
            <div class="col-md-12">

                <div class="portlet box green">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-money"></i> Payroll Statutory Rules
                        </div>
                    </div>

                    <div class="portlet-body table-responsive">

                        <button class="btn green" id="addRuleBtn" style="margin-bottom:10px;">
                            Add Rule
                        </button>

                        <table class="table table-bordered table-hover">
                            <thead>
                            <tr>
                                <th>Rule Name</th>
                                <th>Code</th>
                                <th>Type</th>
                                <th>Base</th>
                                <th>Wage Contribution Cap</th>
                                <th>Status</th>
                                <th>Effective From</th>
                                <th>Effective To</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($rules as $rule): ?>
                                <tr>
                                    <td><?php echo $rule['rule_name']; ?></td>
                                    <td><?php echo $rule['rule_code']; ?></td>
                                    <td><?php echo ucwords(str_replace('_', ' ', $rule['rule_type'])); ?></td>
                                    <td><?php echo ucwords(str_replace('_', ' ', $rule['calculation_base'])); ?></td>
                                    <td><?php echo !empty($rule['wage_contribution_cap']) ? round($rule['wage_contribution_cap'], 2) : '-'; ?></td>
                                    <td>
                                        <?php if($rule['status'] == 1): ?>
                                            <span class="label label-success">Active</span>
                                        <?php else: ?>
                                            <span class="label label-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $rule['effective_from']; ?></td>
                                    <td><?php echo $rule['effective_to']; ?></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm editRuleBtn" data-id="<?php echo $rule['id']; ?>">
                                            Edit
                                        </button>

                                        <button class="btn btn-info btn-sm manageSlabsBtn"
                                                data-id="<?php echo $rule['id']; ?>"
                                                data-name="<?php echo $rule['rule_name']; ?>">
                                            Slabs
                                        </button>

                                        <button class="btn btn-danger btn-sm deleteRuleBtn" data-id="<?php echo $rule['id']; ?>">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>

                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<!-- Rule Modal -->
<div class="modal fade" id="ruleModal" tabindex="-1" data-width="700">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"></button>
        <h4 class="modal-title">Payroll Rule</h4>
    </div>

    <div class="modal-body">
        <form id="ruleForm" class="form-horizontal">

            <input type="hidden" name="id" id="rule_id">

            <div class="form-group">
                <label class="col-md-4 control-label">Rule Name</label>
                <div class="col-md-8">
                    <input type="text" name="rule_name" id="rule_name" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-4 control-label">Rule Code</label>
                <div class="col-md-8">
                    <input type="text" name="rule_code" id="rule_code" class="form-control" required>
                    <small>Example: EOBI, SS</small>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-4 control-label">Rule Type</label>
                <div class="col-md-8">
                    <select name="rule_type" id="rule_type" class="form-control">
                        <option value="eobi">EOBI</option>
                        <option value="social_security">Social Security</option>
                        <option value="income_tax">Income Tax</option>
                        <option value="provident_fund">Provident Fund</option>
                        <option value="loan">Loan</option>
                        <option value="advance">Advance</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-4 control-label">Calculation Base</label>
                <div class="col-md-8">
                    <select name="calculation_base" id="calculation_base" class="form-control">
                        <option value="gross_salary">Gross Salary</option>
                        <option value="basic_salary">Basic Salary</option>
                        <option value="net_salary">Net Salary</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-4 control-label">Wage to Pay Contribution Cap</label>
                <div class="col-md-8">
                    <input type="number" step="0.01" name="wage_contribution_cap" id="wage_contribution_cap" class="form-control">
                    <small>Example: EOBI portal mein 44397. Blank/0 ho to full selected wage use hoga.</small>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-4 control-label">Effective From</label>
                <div class="col-md-8">
                    <input type="date" name="effective_from" id="effective_from" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-4 control-label">Effective To</label>
                <div class="col-md-8">
                    <input type="date" name="effective_to" id="effective_to" class="form-control">
                </div>
            </div>
            
            <div class="form-group">
                <label class="col-md-4 control-label">Select Expense Category</label>
                <div class="exp_details col-md-8">
                    <div class="exp_cats">
                        <div class="form-group" id="div-0">
                            <div>
                                <select class="form-control Select2 exps" data-count="0" name="expense_category_id[]" id="category_id">
                                    <option value="">Select expense category</option>
                                    <?php
                                        foreach($exp_categories as $category):
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
            </div>

            <div class="form-group">
                <label class="col-md-4 control-label">Status</label>
                <div class="col-md-8">
                    <select name="status" id="rule_status" class="form-control">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>

        </form>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn default" data-dismiss="modal">Close</button>
        <button type="button" class="btn red" id="saveRuleBtn">Save Rule</button>
    </div>
</div>

<!-- Slabs Modal -->
<div class="modal fade" id="slabsModal" tabindex="-1" data-width="1000">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"></button>
        <h4 class="modal-title">
            Rule Slabs: <span id="slab_rule_title"></span>
        </h4>
    </div>

    <div class="modal-body">

        <input type="hidden" id="current_rule_id">

        <button class="btn green" id="addSlabBtn" style="margin-bottom:10px;">
            Add Slab
        </button>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th>Min Salary</th>
                    <th>Max Salary</th>
                    <th>Employee</th>
                    <th>Employee Type</th>
                    <th>Employee Value</th>
                    <th>Employer</th>
                    <th>Employer Type</th>
                    <th>Employer Value</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody id="slabsTableBody"></tbody>
            </table>
        </div>

    </div>

    <div class="modal-footer">
        <button type="button" class="btn default" data-dismiss="modal">Close</button>
    </div>
</div>

<!-- Slab Form Modal -->
<div class="modal fade" id="slabFormModal" tabindex="-1" data-width="750">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"></button>
        <h4 class="modal-title">Slab Detail</h4>
    </div>

    <div class="modal-body">
        <form id="slabForm" class="form-horizontal">

            <input type="hidden" name="id" id="slab_id">
            <input type="hidden" name="rule_id" id="slab_rule_id">

            <div class="form-group">
                <label class="col-md-4 control-label">Min Salary</label>
                <div class="col-md-8">
                    <input type="number" step="0.01" name="min_salary" id="min_salary" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-4 control-label">Max Salary</label>
                <div class="col-md-8">
                    <input type="number" step="0.01" name="max_salary" id="max_salary" class="form-control">
                    <small>Empty rakho agar upper limit nahi hai.</small>
                </div>
            </div>

            <hr>

            <div class="form-group">
                <label class="col-md-4 control-label">Employee Deduction?</label>
                <div class="col-md-8">
                    <input type="checkbox" name="employee_applicable" id="employee_applicable" value="1">
                    Employee will pay
                </div>
            </div>

            <div id="employeeFields">
                <div class="form-group">
                    <label class="col-md-4 control-label">Employee Calculation Type</label>
                    <div class="col-md-8">
                        <select name="employee_calculation_type" id="employee_calculation_type" class="form-control">
                            <option value="none">None</option>
                            <option value="percentage">Percentage</option>
                            <option value="fixed">Fixed</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label">Employee Value</label>
                    <div class="col-md-8">
                        <input type="number" step="0.01" name="employee_value" id="employee_value" class="form-control">
                    </div>
                </div>
            </div>

            <hr>

            <div class="form-group">
                <label class="col-md-4 control-label">Employer Contribution?</label>
                <div class="col-md-8">
                    <input type="checkbox" name="employer_applicable" id="employer_applicable" value="1">
                    Company will pay
                </div>
            </div>

            <div id="employerFields">
                <div class="form-group">
                    <label class="col-md-4 control-label">Employer Calculation Type</label>
                    <div class="col-md-8">
                        <select name="employer_calculation_type" id="employer_calculation_type" class="form-control">
                            <option value="none">None</option>
                            <option value="percentage">Percentage</option>
                            <option value="fixed">Fixed</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label">Employer Value</label>
                    <div class="col-md-8">
                        <input type="number" step="0.01" name="employer_value" id="employer_value" class="form-control">
                    </div>
                </div>
            
                <div class="exp_details form-group">
                    <label class="col-md-4 control-label">Select Expense Category</label>
                    <div class="exp_cats">
                        <div id="div-0">
                            <div class="col-md-7">
                                <select class="form-control Select2 exps" data-count="0" name="expense_category_id[]" id="category_id">
                                    <option value="">Select expense category</option>
                                    <?php
                                        foreach($exp_categories as $category):
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
            </div>

            <div class="form-group">
                <label class="col-md-4 control-label">Status</label>
                <div class="col-md-8">
                    <select name="status" id="slab_status" class="form-control">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>

        </form>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn default" data-dismiss="modal">Close</button>
        <button type="button" class="btn red" id="saveSlabBtn">Save Slab</button>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    $(document).ready(function () {

        var baseUrl = "<?php echo site_url(); ?>";
        let count = 0;

        $('#addRuleBtn').on('click', function () {
            $('#ruleForm')[0].reset();
            $('#rule_id').val('');
            $('#ruleModal').modal('show');
        });

        $('#saveRuleBtn').on('click', function () {
            $.ajax({
                url: baseUrl + '/Payroll_statutory_rules/save_rule',
                type: 'POST',
                data: $('#ruleForm').serialize(),
                dataType: 'json',
                success: function (res) {
                    alert(res.message);
                    location.reload();
                }
            });
        });

        $('.editRuleBtn').on('click', function () {
            var id = $(this).data('id');

            $.ajax({
                url: baseUrl + '/Payroll_statutory_rules/get_rule/' + id,
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    $('#rule_id').val(data.id);
                    $('#rule_name').val(data.rule_name);
                    $('#rule_code').val(data.rule_code);
                    $('#rule_type').val(data.rule_type);
                    $('#calculation_base').val(data.calculation_base);
                    $('#wage_contribution_cap').val(data.wage_contribution_cap);
                    $('#effective_from').val(data.effective_from);
                    $('#effective_to').val(data.effective_to);
                    $('#rule_status').val(data.status);
                    resetExpenseCategories();
            
                    var selectedCategories = [];
                
                    if (data.expense_category) {
                        selectedCategories = JSON.parse(data.expense_category);
                    }
                
                    if (selectedCategories.length > 0) {
                        $.ajax({
                            url: baseUrl + '/Collegepapers/get_expense_category_chain',
                            type: 'POST',
                            data: {
                                selected_categories: selectedCategories
                            },
                            success: function (html) {
                                $('.exp_cats').html(html);
                                $('.Select2').select2();
                
                                count = $('.exp_cats .exps').length - 1;
                
                                $('#examModal').modal('show');
                            }
                        });
                    } else {
                        $('#examModal').modal('show');
                    }
                    $('#ruleModal').modal('show');
                }
            });
        });

        $('.deleteRuleBtn').on('click', function () {
            if (!confirm('Are you sure you want to delete this rule?')) {
                return false;
            }

            var id = $(this).data('id');

            $.ajax({
                url: baseUrl + '/Payroll_statutory_rules/delete_rule/' + id,
                type: 'POST',
                dataType: 'json',
                success: function (res) {
                    alert(res.message);
                    location.reload();
                }
            });
        });

        $('.manageSlabsBtn').on('click', function () {
            var ruleId = $(this).data('id');
            var ruleName = $(this).data('name');

            $('#current_rule_id').val(ruleId);
            $('#slab_rule_title').text(ruleName);

            loadSlabs(ruleId);

            $('#slabsModal').modal('show');
        });

        function loadSlabs(ruleId) {
            $.ajax({
                url: baseUrl + '/Payroll_statutory_rules/get_slabs/' + ruleId,
                type: 'GET',
                dataType: 'json',
                success: function (slabs) {
                    var html = '';

                    if (slabs.length == 0) {
                        html = '<tr><td colspan="10" class="text-center">No slabs found</td></tr>';
                    }

                    $.each(slabs, function (i, slab) {
                        html += '<tr>';
                        html += '<td>' + slab.min_salary + '</td>';
                        html += '<td>' + (slab.max_salary ? slab.max_salary : 'No Limit') + '</td>';
                        html += '<td>' + (slab.employee_applicable == 1 ? 'Yes' : 'No') + '</td>';
                        html += '<td>' + slab.employee_calculation_type + '</td>';
                        html += '<td>' + slab.employee_value + '</td>';
                        html += '<td>' + (slab.employer_applicable == 1 ? 'Yes' : 'No') + '</td>';
                        html += '<td>' + slab.employer_calculation_type + '</td>';
                        html += '<td>' + slab.employer_value + '</td>';
                        html += '<td>' + (slab.status == 1 ? 'Active' : 'Inactive') + '</td>';
                        html += '<td>';
                        html += '<button class="btn btn-primary btn-sm editSlabBtn" data-id="' + slab.id + '">Edit</button> ';
                        html += '<button class="btn btn-danger btn-sm deleteSlabBtn" data-id="' + slab.id + '">Delete</button>';
                        html += '</td>';
                        html += '</tr>';
                    });

                    $('#slabsTableBody').html(html);
                }
            });
        }

        $('#addSlabBtn').on('click', function () {

            resetExpenseCategories();
            $('#slabForm')[0].reset();

            $('#slab_id').val('');
            $('#slab_rule_id').val($('#current_rule_id').val());

            $('#employee_applicable').prop('checked', false);
            $('#employer_applicable').prop('checked', false);

            $('#employee_calculation_type').val('none');
            $('#employer_calculation_type').val('none');

            $('#slabFormModal').modal('show');
        });

        $('#saveSlabBtn').on('click', function () {
            $.ajax({
                url: baseUrl + '/Payroll_statutory_rules/save_slab',
                type: 'POST',
                data: $('#slabForm').serialize(),
                dataType: 'json',
                success: function (res) {
                    alert(res.message);

                    $('#slabFormModal').modal('hide');

                    loadSlabs($('#current_rule_id').val());
                }
            });
        });

        $(document).on('click', '.editSlabBtn', function () {
            var id = $(this).data('id');

            $.ajax({
                url: baseUrl + '/Payroll_statutory_rules/get_slab/' + id,
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    $('#slab_id').val(data.id);
                    $('#slab_rule_id').val(data.rule_id);

                    $('#min_salary').val(data.min_salary);
                    $('#max_salary').val(data.max_salary);

                    $('#employee_applicable').prop('checked', data.employee_applicable == 1);
                    $('#employee_calculation_type').val(data.employee_calculation_type);
                    $('#employee_value').val(data.employee_value);

                    $('#employer_applicable').prop('checked', data.employer_applicable == 1);
                    $('#employer_calculation_type').val(data.employer_calculation_type);
                    $('#employer_value').val(data.employer_value);

                    $('#slab_status').val(data.status);
                    if (data.expense_category_id != null && data.expense_category_id != '') {
                        let selectedExpenseCategories = JSON.parse(data.expense_category_id);
                        loadExpenseCategoryChain(selectedExpenseCategories);
                    } else {
                        resetExpenseCategories();
                    }

                    $('#slabFormModal').modal('show');
                }
            });
        });

        $(document).on('click', '.deleteSlabBtn', function () {
            if (!confirm('Are you sure you want to delete this slab?')) {
                return false;
            }

            var id = $(this).data('id');

            $.ajax({
                url: baseUrl + '/Payroll_statutory_rules/delete_slab/' + id,
                type: 'POST',
                dataType: 'json',
                success: function (res) {
                    alert(res.message);
                    loadSlabs($('#current_rule_id').val());
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
                            $('#category_id'+(con--)).select2();
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
            
        function resetExpenseCategories() {
            for (let n = 1; n <= count; n++) {
                $('#div-' + n).remove();
            }
        
            count = 0;
            $('#category_id').val('').trigger('change.select2');
        }
        
        function loadExpenseCategoryChain(categoryIds) {
            resetExpenseCategories();
        
            if (!categoryIds || categoryIds.length === 0) {
                return;
            }
        
            $('#category_id').val(categoryIds[0]).trigger('change.select2');
        
            let con = 0;
        
            function loadNext(index) {
                if (index >= categoryIds.length - 1) {
                    return;
                }
        
                let exp_id = categoryIds[index];
        
                $.ajax({
                    type: "post",
                    async: false,
                    url: '<?php echo site_url()?>/expenses/getSubExpenses',
                    data: {
                        campus_id: exp_id,
                        expense_id: exp_id,
                        count: con
                    },
                    success: function (html) {
                        if (html != "") {
                            con++;
                            $('.exp_cats').append(html);
                            count = con;
        
                            $('#category_id' + con).select2();
                            $('#category_id' + con).val(categoryIds[index + 1]).trigger('change.select2');
        
                            loadNext(index + 1);
                        }
                    }
                });
            }
        
            loadNext(0);
        }
        });
    });
</script>
