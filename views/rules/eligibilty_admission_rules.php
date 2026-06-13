<div class="page-content-wrapper">
    <div class="page-content">

        <!-- Success or Error Message -->
        <?php if($this->session->userdata('message')): ?>
            <div class="alert alert-success">
                <button class="close" data-close="alert"></button>
                <span><?php echo $this->session->userdata('message'); ?></span>
            </div>
            <?php $this->session->unset_userdata('message'); ?>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <div class="portlet box green">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-plus" aria-hidden="true"></i> Add Admission Eligibility Rules
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form class="form-horizontal" role="form" method="post" action="<?php echo site_url('rules/insert_admission_rule'); ?>">
                            <div class="form-body row">
                                <!-- Course Dropdown -->
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Course <span class="required">*</span></label>
                                    <div class="col-md-9">
                                        <select name="course_id" class="form-control input-inline input-large course_id" required>
                                            <option value="">Select Course</option>
                                            <?php if (isset($courses)) {
                                                foreach ($courses as $course): ?>
                                                    <option value="<?php echo $course['course_id']; ?>"><?php echo $course['course_name']; ?></option>
                                                <?php endforeach;
                                            } ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- Rules Inputs -->
                                <div class="rules_rows">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Rule <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="text" name="rule[]" class="form-control input-inline input-large" required />
                                            <button type="button" class="btn green add_rule"><i class="fa fa-plus"></i></button>
                                            <button type="button" class="btn red delete_rule"><i class="fa fa-trash"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <button type="submit" class="btn green">Add</button>
                                        <button onclick="location.href = '<?php echo site_url(); ?>'" type="button" class="btn default">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div> <!-- End form -->
                </div> <!-- End portlet -->
                <div class="row">
                    <div class="col-md-12">
                        <!-- BEGIN EXAMPLE TABLE PORTLET-->
                        <div class="portlet box grey-cascade">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-list"></i> All Assigned Rules
                                </div>
                            </div>
                            <div class="portlet-body">
                                <table class="table table-striped table-bordered table-hover" id="sample_2">
                                    <thead>
                                        <tr>
                                            <th class="hidden">Sr.No</th>
                                            <th>Course Name</th>
                                            <th>Rules</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($rules)): ?>
                                            <?php $i = 1; foreach ($rules as $rule): ?>
                                                <tr class="odd gradeX">
                                                    <td class="hidden"><?= $i++; ?></td>
                                                    <td><?php echo $rule['course_name'];?></td>
                                                    <td><?php echo $rule['rule'];?></td>
                                                    <td>
                                                        <!-- Example Action Buttons -->
                                                         <a href="<?php echo site_url().'/rules/delete_eligibility_admission_rule/' . $rule['eligibilty_admission_rule_id'];?>" class="btn red"><i class="fa fa-trash"></i></a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center">No rules found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- END EXAMPLE TABLE PORTLET-->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Dynamic Rule Fields -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Add rule field
    jQuery(document).on('click', '.add_rule', function() {
        var rule = `
        <div class="form-group">
            <label class="col-md-3 control-label">Rule <span class="required">*</span></label>
            <div class="col-md-9">
                <input type="text" name="rule[]" class="form-control input-inline input-large" required />
                <button type="button" class="btn green add_rule"><i class="fa fa-plus"></i></button>
                <button type="button" class="btn red delete_rule"><i class="fa fa-trash"></i></button>
            </div>
        </div>`;
        jQuery('.rules_rows').append(rule);
    });

    // Delete rule field
    jQuery(document).on('click', '.delete_rule', function() {
        jQuery(this).closest('.form-group').remove();
    });
});
</script>
