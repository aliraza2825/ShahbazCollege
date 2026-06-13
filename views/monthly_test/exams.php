<?php
$exp_categories = $this->db->get_where('expense_category', "sub_of is NULL")->result_array();
?>
<div class="page-content-wrapper">
    <div class="page-content">

        <div class="row">
            <div class="col-md-12">

                <div class="portlet box green">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-file-text"></i> Monthly Test Exams
                        </div>
                    </div>

                    <div class="portlet-body table-responsive">

                        <button class="btn green" id="addExamBtn" style="margin-bottom:10px;">
                            Add Exam
                        </button>

                        <table class="table table-bordered table-hover">
                            <thead>
                            <tr>
                                <th>Exam Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Created Date</th>
                                <th>Action</th>
                            </tr>
                            </thead>

                            <tbody>
                            <?php foreach($exams as $exam): ?>
                                <tr>
                                    <td><?php echo $exam['exam_name']; ?></td>
                                    <td><?php echo $exam['description']; ?></td>
                                    <td>
                                        <?php if($exam['status'] == 1): ?>
                                            <span class="label label-success">Active</span>
                                        <?php else: ?>
                                            <span class="label label-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $exam['created_at']; ?></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm editExamBtn" data-id="<?php echo $exam['id']; ?>">
                                            Edit
                                        </button>

                                        <button class="btn btn-info btn-sm manageImprovementRulesBtn"
                                                data-id="<?php echo $exam['id']; ?>"
                                                data-name="<?php echo $exam['exam_name']; ?>">
                                            Improvement Rules
                                        </button>

                                        <button class="btn btn-warning btn-sm manageRewardRulesBtn"
                                                data-id="<?php echo $exam['id']; ?>"
                                                data-name="<?php echo $exam['exam_name']; ?>">
                                            Reward Rules
                                        </button>

                                        <button class="btn btn-danger btn-sm deleteExamBtn" data-id="<?php echo $exam['id']; ?>">
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

<!-- Exam Modal -->
<div class="modal fade" id="examModal" tabindex="-1" data-width="850">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"></button>
        <h4 class="modal-title">Exam Detail</h4>
    </div>

    <div class="modal-body">
        <form id="examForm" class="form-horizontal">

            <input type="hidden" name="id" id="exam_id">

            <div class="form-group">
                <label class="col-md-4 control-label">Exam Name</label>
                <div class="col-md-8">
                    <input type="text" name="exam_name" id="exam_name" class="form-control" required>
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
                <label class="col-md-4 control-label">Description</label>
                <div class="col-md-8">
                    <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-4 control-label">Status</label>
                <div class="col-md-8">
                    <select name="status" id="exam_status" class="form-control">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>

        </form>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn default" data-dismiss="modal">Close</button>
        <button type="button" class="btn red" id="saveExamBtn">Save Exam</button>
    </div>
</div>

<!-- Improvement Rules Modal -->
<div class="modal fade" id="improvementRulesModal" tabindex="-1" data-width="1000">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"></button>
        <h4 class="modal-title">
            Improvement Rules: <span id="improvement_exam_title"></span>
        </h4>
    </div>

    <div class="modal-body">

        <input type="hidden" id="current_improvement_exam_id">

        <button class="btn green" id="addImprovementRuleBtn" style="margin-bottom:10px;">
            Add Improvement Rule
        </button>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th>Attempt No</th>
                    <th>Attempt Name</th>
                    <th>Min %</th>
                    <th>Max %</th>
                    <th>Improvement Required</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                </thead>

                <tbody id="improvementRulesTableBody"></tbody>
            </table>
        </div>

    </div>

    <div class="modal-footer">
        <button type="button" class="btn default" data-dismiss="modal">Close</button>
    </div>
</div>

<!-- Improvement Rule Form Modal -->
<div class="modal fade" id="improvementRuleFormModal" tabindex="-1" data-width="750">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"></button>
        <h4 class="modal-title">Improvement Rule Detail</h4>
    </div>

    <div class="modal-body">
        <form id="improvementRuleForm" class="form-horizontal">

            <input type="hidden" name="id" id="improvement_rule_id">
            <input type="hidden" name="exam_id" id="improvement_rule_exam_id">

            <div class="form-group">
                <label class="col-md-4 control-label">Attempt No</label>
                <div class="col-md-8">
                    <input type="number" name="attempt_no" id="attempt_no" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-4 control-label">Attempt Name</label>
                <div class="col-md-8">
                    <input type="text" name="attempt_name" id="attempt_name" class="form-control" placeholder="1st Time">
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-4 control-label">Min Percentage</label>
                <div class="col-md-8">
                    <input type="number" step="0.01" name="min_percentage" id="min_percentage" class="form-control" value="0">
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-4 control-label">Max Percentage</label>
                <div class="col-md-8">
                    <input type="number" step="0.01" name="max_percentage" id="max_percentage" class="form-control">
                    <small>Empty rakho agar upper limit nahi hai.</small>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-4 control-label">Improvement Required?</label>
                <div class="col-md-8">
                    <input type="checkbox" name="improvement_required" id="improvement_required" value="1">
                    Yes
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-4 control-label">Status</label>
                <div class="col-md-8">
                    <select name="status" id="improvement_rule_status" class="form-control">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>

        </form>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn default" data-dismiss="modal">Close</button>
        <button type="button" class="btn red" id="saveImprovementRuleBtn">Save Rule</button>
    </div>
</div>

<!-- Reward Rules Modal -->
<div class="modal fade" id="rewardRulesModal" tabindex="-1" data-width="900">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"></button>
        <h4 class="modal-title">
            Reward Rules: <span id="reward_exam_title"></span>
        </h4>
    </div>

    <div class="modal-body">

        <input type="hidden" id="current_reward_exam_id">

        <button class="btn green" id="addRewardRuleBtn" style="margin-bottom:10px;">
            Add Reward Rule
        </button>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th>Improvement Count</th>
                    <th>Certificate</th>
                    <th>Cash Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                </thead>

                <tbody id="rewardRulesTableBody"></tbody>
            </table>
        </div>

    </div>

    <div class="modal-footer">
        <button type="button" class="btn default" data-dismiss="modal">Close</button>
    </div>
</div>

<!-- Reward Rule Form Modal -->
<div class="modal fade" id="rewardRuleFormModal" tabindex="-1" data-width="700">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"></button>
        <h4 class="modal-title">Reward Rule Detail</h4>
    </div>

    <div class="modal-body">
        <form id="rewardRuleForm" class="form-horizontal">

            <input type="hidden" name="id" id="reward_rule_id">
            <input type="hidden" name="exam_id" id="reward_rule_exam_id">

            <div class="form-group">
                <label class="col-md-4 control-label">Improvement Count</label>
                <div class="col-md-8">
                    <input type="number" name="improvement_count" id="improvement_count" class="form-control" required>
                    <small>Example: 1, 2, 3 improvements</small>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-4 control-label">Certificate?</label>
                <div class="col-md-8">
                    <input type="checkbox" name="certificate" id="certificate" value="1">
                    Yes
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-4 control-label">Cash Amount</label>
                <div class="col-md-8">
                    <input type="number" step="0.01" name="cash_amount" id="cash_amount" class="form-control" value="0">
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-4 control-label">Status</label>
                <div class="col-md-8">
                    <select name="status" id="reward_rule_status" class="form-control">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>

        </form>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn default" data-dismiss="modal">Close</button>
        <button type="button" class="btn red" id="saveRewardRuleBtn">Save Rule</button>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    
    var count = 0;

    if (typeof $ === 'undefined') {
        alert('jQuery load nahi hui. Pehle jQuery include karo.');
        return;
    }

    var baseUrl = "<?php echo site_url(); ?>";

    $('#addExamBtn').on('click', function () {
        $('#examForm')[0].reset();
        $('#exam_id').val('');
        $('#exam_status').val(1);
    
        resetExpenseCategories();
    
        $('#examModal').modal('show');
    });

    $('#saveExamBtn').on('click', function () {
        $.ajax({
            url: baseUrl + '/Collegepapers/save_exam',
            type: 'POST',
            data: $('#examForm').serialize(),
            dataType: 'json',
            success: function (res) {
                location.reload();
            }
        });
    });

    $('.editExamBtn').on('click', function () {
        var id = $(this).data('id');

        $.ajax({
            url: baseUrl + '/Collegepapers/get_exam/' + id,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                $('#exam_id').val(data.id);
                $('#exam_name').val(data.exam_name);
                $('#description').val(data.description);
                $('#exam_status').val(data.status);
            
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
            }
        });
    });

    $('.deleteExamBtn').on('click', function () {
        if (!confirm('Are you sure you want to delete this exam?')) {
            return false;
        }

        var id = $(this).data('id');

        $.ajax({
            url: baseUrl + '/Collegepapers/delete_exam/' + id,
            type: 'POST',
            dataType: 'json',
            success: function (res) {
                
                location.reload();
            }
        });
    });

    $('.manageImprovementRulesBtn').on('click', function () {
        var examId = $(this).data('id');
        var examName = $(this).data('name');

        $('#current_improvement_exam_id').val(examId);
        $('#improvement_exam_title').text(examName);

        loadImprovementRules(examId);

        $('#improvementRulesModal').modal('show');
    });

    function loadImprovementRules(examId) {
        $.ajax({
            url: baseUrl + '/Collegepapers/get_improvement_rules/' + examId,
            type: 'GET',
            dataType: 'json',
            success: function (rules) {
                var html = '';

                if (rules.length == 0) {
                    html = '<tr><td colspan="7" class="text-center">No improvement rules found</td></tr>';
                }

                $.each(rules, function (i, rule) {
                    html += '<tr>';
                    html += '<td>' + rule.attempt_no + '</td>';
                    html += '<td>' + rule.attempt_name + '</td>';
                    html += '<td>' + rule.min_percentage + '</td>';
                    html += '<td>' + (rule.max_percentage ? rule.max_percentage : 'No Limit') + '</td>';
                    html += '<td>' + (rule.improvement_required == 1 ? 'Yes' : 'No') + '</td>';
                    html += '<td>' + (rule.status == 1 ? 'Active' : 'Inactive') + '</td>';
                    html += '<td>';
                    html += '<button class="btn btn-primary btn-sm editImprovementRuleBtn" data-id="' + rule.id + '">Edit</button> ';
                    html += '<button class="btn btn-danger btn-sm deleteImprovementRuleBtn" data-id="' + rule.id + '">Delete</button>';
                    html += '</td>';
                    html += '</tr>';
                });

                $('#improvementRulesTableBody').html(html);
            }
        });
    }

    $('#addImprovementRuleBtn').on('click', function () {
        $('#improvementRuleForm')[0].reset();

        $('#improvement_rule_id').val('');
        $('#improvement_rule_exam_id').val($('#current_improvement_exam_id').val());
        $('#improvement_required').prop('checked', false);
        $('#improvement_rule_status').val(1);

        $('#improvementRuleFormModal').modal('show');
    });

    $('#saveImprovementRuleBtn').on('click', function () {
        $.ajax({
            url: baseUrl + '/Collegepapers/save_improvement_rule',
            type: 'POST',
            data: $('#improvementRuleForm').serialize(),
            dataType: 'json',
            success: function (res) {
                
                $('#improvementRuleFormModal').modal('hide');
                loadImprovementRules($('#current_improvement_exam_id').val());
            }
        });
    });

    $(document).on('click', '.editImprovementRuleBtn', function () {
        var id = $(this).data('id');

        $.ajax({
            url: baseUrl + '/Collegepapers/get_improvement_rule/' + id,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                $('#improvement_rule_id').val(data.id);
                $('#improvement_rule_exam_id').val(data.exam_id);
                $('#attempt_no').val(data.attempt_no);
                $('#attempt_name').val(data.attempt_name);
                $('#min_percentage').val(data.min_percentage);
                $('#max_percentage').val(data.max_percentage);
                $('#improvement_required').prop('checked', data.improvement_required == 1);
                $('#improvement_rule_status').val(data.status);

                $('#improvementRuleFormModal').modal('show');
            }
        });
    });

    $(document).on('click', '.deleteImprovementRuleBtn', function () {
        if (!confirm('Are you sure you want to delete this improvement rule?')) {
            return false;
        }

        var id = $(this).data('id');

        $.ajax({
            url: baseUrl + '/Collegepapers/delete_improvement_rule/' + id,
            type: 'POST',
            dataType: 'json',
            success: function (res) {
                
                loadImprovementRules($('#current_improvement_exam_id').val());
            }
        });
    });

    $('.manageRewardRulesBtn').on('click', function () {
        var examId = $(this).data('id');
        var examName = $(this).data('name');

        $('#current_reward_exam_id').val(examId);
        $('#reward_exam_title').text(examName);

        loadRewardRules(examId);

        $('#rewardRulesModal').modal('show');
    });

    function loadRewardRules(examId) {
        $.ajax({
            url: baseUrl + '/Collegepapers/get_reward_rules/' + examId,
            type: 'GET',
            dataType: 'json',
            success: function (rules) {
                var html = '';

                if (rules.length == 0) {
                    html = '<tr><td colspan="5" class="text-center">No reward rules found</td></tr>';
                }

                $.each(rules, function (i, rule) {
                    html += '<tr>';
                    html += '<td>' + rule.improvement_count + '</td>';
                    html += '<td>' + (rule.certificate == 1 ? 'Yes' : 'No') + '</td>';
                    html += '<td>' + rule.cash_amount + '</td>';
                    html += '<td>' + (rule.status == 1 ? 'Active' : 'Inactive') + '</td>';
                    html += '<td>';
                    html += '<button class="btn btn-primary btn-sm editRewardRuleBtn" data-id="' + rule.id + '">Edit</button> ';
                    html += '<button class="btn btn-danger btn-sm deleteRewardRuleBtn" data-id="' + rule.id + '">Delete</button>';
                    html += '</td>';
                    html += '</tr>';
                });

                $('#rewardRulesTableBody').html(html);
            }
        });
    }

    $('#addRewardRuleBtn').on('click', function () {
        $('#rewardRuleForm')[0].reset();

        $('#reward_rule_id').val('');
        $('#reward_rule_exam_id').val($('#current_reward_exam_id').val());
        $('#certificate').prop('checked', false);
        $('#cash_amount').val(0);
        $('#reward_rule_status').val(1);

        $('#rewardRuleFormModal').modal('show');
    });

    $('#saveRewardRuleBtn').on('click', function () {
        $.ajax({
            url: baseUrl + '/Collegepapers/save_reward_rule',
            type: 'POST',
            data: $('#rewardRuleForm').serialize(),
            dataType: 'json',
            success: function (res) {
                
                $('#rewardRuleFormModal').modal('hide');
                loadRewardRules($('#current_reward_exam_id').val());
            }
        });
    });

    $(document).on('click', '.editRewardRuleBtn', function () {
        var id = $(this).data('id');

        $.ajax({
            url: baseUrl + '/Collegepapers/get_reward_rule/' + id,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                $('#reward_rule_id').val(data.id);
                $('#reward_rule_exam_id').val(data.exam_id);
                $('#improvement_count').val(data.improvement_count);
                $('#certificate').prop('checked', data.certificate == 1);
                $('#cash_amount').val(data.cash_amount);
                $('#reward_rule_status').val(data.status);

                $('#rewardRuleFormModal').modal('show');
            }
        });
    });

    $(document).on('click', '.deleteRewardRuleBtn', function () {
        if (!confirm('Are you sure you want to delete this reward rule?')) {
            return false;
        }

        var id = $(this).data('id');

        $.ajax({
            url: baseUrl + '/Collegepapers/delete_reward_rule/' + id,
            type: 'POST',
            dataType: 'json',
            success: function (res) {
                
                loadRewardRules($('#current_reward_exam_id').val());
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
        count = 0;
    
        $('.exp_cats').html(`
            <div class="form-group" id="div-0">
                <div>
                    <select class="form-control Select2 exps" data-count="0" name="expense_category_id[]" id="category_id0">
                        <option value="">Select expense category</option>
                        <?php foreach($exp_categories as $category): ?>
                            <option value="<?php echo $category['expense_category_id']; ?>">
                                <?php echo $category['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        `);
    
        $('.Select2').select2();
    }
    });
</script>