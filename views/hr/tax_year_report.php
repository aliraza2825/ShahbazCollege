<div class="page-content-wrapper">
    <div class="page-content">

        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-calculator"></i> Employee Wise Income Tax Report
                </div>
            </div>

            <div class="portlet-body">

                <form method="post" action="<?php echo site_url(); ?>/Payroll_income_tax/tax_year_report">
                    <div class="row">

                        <div class="col-md-4">
                            <label>Tax Year</label>
                            <select name="tax_year_id" class="form-control" required>
                                <option value="">Select Tax Year</option>

                                <?php foreach($tax_years as $year): ?>
                                    <option value="<?php echo $year['id']; ?>"
                                        <?php echo ($selected_tax_year_id == $year['id']) ? 'selected' : ''; ?>>
                                        <?php echo $year['tax_year']; ?>
                                        (<?php echo $year['start_date']; ?> to <?php echo $year['end_date']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4" style="margin-top:25px;">
                            <button type="submit" class="btn green">
                                Search
                            </button>
                        </div>

                    </div>
                </form>

                <hr>

                <?php if(!empty($tax_year)): ?>
                    <h4>
                        Tax Year:
                        <strong><?php echo $tax_year['tax_year']; ?></strong>
                        <small>
                            (<?php echo $tax_year['start_date']; ?> to <?php echo $tax_year['end_date']; ?>)
                        </small>
                    </h4>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="sample_2">
                        <thead>
                        <tr>
                            <th>Sr No.</th>
                            <th>Employee Name</th>
                            <th>CNIC</th>
                            <th>College Name</th>
                            <th>Total Salary Amount</th>
                            <th>Total Salary Paid</th>
                            <th>Total Tax Deducted</th>
                            <th>Detail</th>
                        </tr>
                        </thead>

                        <tbody>
                        <?php if(!empty($report)): ?>

                            <?php
                            $grand_salary_amount = 0;
                            $grand_salary_paid = 0;
                            $grand_tax = 0;
                            ?>

                            <?php foreach($report as $index => $row): ?>

                                <?php
                                $grand_salary_amount += $row['total_salary_amount'];
                                $grand_salary_paid += $row['total_salary_paid'];
                                $grand_tax += $row['total_tax'];

                                $collapseId = 'tax_detail_' . $row['user_id'];
                                ?>

                                <tr style="font-weight:bold;">
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo $row['employee_name']; ?></td>
                                    <td><?php echo $row['cnic']; ?></td>
                                    <td><?php echo $row['college_name']; ?></td>
                                    <td><?php echo number_format($row['total_salary_amount'], 2); ?></td>
                                    <td><?php echo number_format($row['total_salary_paid'], 2); ?></td>
                                    <td>
                                        <span class="label label-success" style="font-size:12px;">
                                            <?php echo number_format($row['total_tax'], 2); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-xs blue taxDetailBtn"
        type="button"
        data-employee="<?php echo $row['employee_name']; ?>"
        data-details='<?php echo htmlspecialchars(json_encode($row["details"]), ENT_QUOTES, "UTF-8"); ?>'>
    View Months
</button>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No record found</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>

                        <?php if(!empty($report)): ?>
                            <tfoot>
                            <tr style="font-weight:bold; background:#f5f5f5;">
                                <td colspan="4" class="text-right">Grand Total</td>
                                <td><?php echo number_format($grand_salary_amount, 2); ?></td>
                                <td><?php echo number_format($grand_salary_paid, 2); ?></td>
                                <td><?php echo number_format($grand_tax, 2); ?></td>
                                <td></td>
                            </tr>
                            </tfoot>
                        <?php endif; ?>

                    </table>
                </div>

            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="taxDetailModal" tabindex="-1" data-width="900">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"></button>
        <h4 class="modal-title">
            Tax Detail - <span id="tax_employee_name"></span>
        </h4>
    </div>

    <div class="modal-body table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Basic Salary</th>
                    <th>Earnings</th>
                    <th>On Salary Amount</th>
                    <th>Salary Paid</th>
                    <th>Tax Deduction</th>
                </tr>
            </thead>
            <tbody id="taxDetailBody"></tbody>
        </table>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn default" data-dismiss="modal">Close</button>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    if (typeof jQuery === 'undefined') {
        console.log('jQuery not loaded');
        return;
    }

    jQuery(document).on('click', '.taxDetailBtn', function () {

        var employee = jQuery(this).data('employee');
        var details = jQuery(this).attr('data-details');

        try {
            details = JSON.parse(details);
        } catch (e) {
            details = [];
        }

        jQuery('#tax_employee_name').text(employee);

        var html = '';

        if (details.length > 0) {
            jQuery.each(details, function (i, row) {
                html += '<tr>';
                html += '<td>' + row.month + '</td>';
                html += '<td>' + parseFloat(row.basic_salary || 0).toFixed(2) + '</td>';
                html += '<td>' + parseFloat(row.earnings || 0).toFixed(2) + '</td>';
                html += '<td>' + parseFloat(row.on_salary_amount || 0).toFixed(2) + '</td>';
                html += '<td>' + parseFloat(row.salary_paid || 0).toFixed(2) + '</td>';
                html += '<td>' + parseFloat(row.tax_amount || 0).toFixed(2) + '</td>';
                html += '</tr>';
            });
        } else {
            html = '<tr><td colspan="6" class="text-center">No detail found</td></tr>';
        }

        jQuery('#taxDetailBody').html(html);
        jQuery('#taxDetailModal').modal('show');
    });

});
</script>