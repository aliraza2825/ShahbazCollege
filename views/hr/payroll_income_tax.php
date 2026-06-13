<div class="page-content-wrapper">
    <div class="page-content">

        <div class="row">
            <div class="col-md-12">

                <div class="portlet box green">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-calculator"></i> Payroll Income Tax
                        </div>
                    </div>

                    <div class="portlet-body table-responsive">

                        <button class="btn green" id="addTaxYearBtn" style="margin-bottom:10px;">
                            Add Tax Year
                        </button>

                        <table class="table table-bordered table-hover">
                            <thead>
                            <tr>
                                <th>Tax Year</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Created Date</th>
                                <th>Action</th>
                            </tr>
                            </thead>

                            <tbody>
                            <?php foreach($tax_years as $year): ?>
                                <tr>
                                    <td><?php echo $year['tax_year']; ?></td>
                                    <td><?php echo $year['start_date']; ?></td>
                                    <td><?php echo $year['end_date']; ?></td>
                                    <td>
                                        <?php if($year['status'] == 1): ?>
                                            <span class="label label-success">Active</span>
                                        <?php else: ?>
                                            <span class="label label-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $year['created_at']; ?></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm editTaxYearBtn" data-id="<?php echo $year['id']; ?>">
                                            Edit
                                        </button>

                                        <button class="btn btn-info btn-sm manageSlabsBtn"
                                                data-id="<?php echo $year['id']; ?>"
                                                data-name="<?php echo $year['tax_year']; ?>">
                                            Slabs
                                        </button>

                                        <button class="btn btn-danger btn-sm deleteTaxYearBtn" data-id="<?php echo $year['id']; ?>">
                                            Delete
                                        </button>
                                        <form method="post" action="<?php echo site_url(); ?>/Payroll_income_tax/tax_year_report">
                                            
                                            <input type="hidden" name="tax_year_id" class="form-control" value="<?php echo $year['id']; ?>">
                                                
                                            <button type="submit" class="btn green">
                                                Report
                                            </button>
                                                
                                        </form>
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

<!-- Tax Year Modal -->
<div class="modal fade" id="taxYearModal" tabindex="-1" data-width="650">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"></button>
        <h4 class="modal-title">Tax Year</h4>
    </div>

    <div class="modal-body">
        <form id="taxYearForm" class="form-horizontal">

            <input type="hidden" name="id" id="tax_year_id">

            <div class="form-group">
                <label class="col-md-4 control-label">Tax Year</label>
                <div class="col-md-8">
                    <input type="text" name="tax_year" id="tax_year" class="form-control" placeholder="2025-2026" required>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-4 control-label">Start Date</label>
                <div class="col-md-8">
                    <input type="date" name="start_date" id="start_date" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-4 control-label">End Date</label>
                <div class="col-md-8">
                    <input type="date" name="end_date" id="end_date" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-4 control-label">Status</label>
                <div class="col-md-8">
                    <select name="status" id="tax_year_status" class="form-control">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>

        </form>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn default" data-dismiss="modal">Close</button>
        <button type="button" class="btn red" id="saveTaxYearBtn">Save Tax Year</button>
    </div>
</div>

<!-- Slabs Modal -->
<div class="modal fade" id="taxSlabsModal" tabindex="-1" data-width="1050">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"></button>
        <h4 class="modal-title">
            Income Tax Slabs: <span id="tax_year_title"></span>
        </h4>
    </div>

    <div class="modal-body">

        <input type="hidden" id="current_tax_year_id">

        <button class="btn green" id="addTaxSlabBtn" style="margin-bottom:10px;">
            Add Tax Slab
        </button>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th>Min Annual Income</th>
                    <th>Max Annual Income</th>
                    <th>Fixed Tax</th>
                    <th>Taxable Above</th>
                    <th>Tax %</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                </thead>

                <tbody id="taxSlabsTableBody"></tbody>
            </table>
        </div>

    </div>

    <div class="modal-footer">
        <button type="button" class="btn default" data-dismiss="modal">Close</button>
    </div>
</div>

<!-- Tax Slab Form Modal -->
<div class="modal fade" id="taxSlabFormModal" tabindex="-1" data-width="750">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"></button>
        <h4 class="modal-title">Tax Slab Detail</h4>
    </div>

    <div class="modal-body">
        <form id="taxSlabForm" class="form-horizontal">

            <input type="hidden" name="id" id="tax_slab_id">
            <input type="hidden" name="tax_year_id" id="slab_tax_year_id">

            <div class="form-group">
                <label class="col-md-4 control-label">Min Annual Income</label>
                <div class="col-md-8">
                    <input type="number" step="0.01" name="min_annual_income" id="min_annual_income" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-4 control-label">Max Annual Income</label>
                <div class="col-md-8">
                    <input type="number" step="0.01" name="max_annual_income" id="max_annual_income" class="form-control">
                    <small>Empty rakho agar upper limit nahi hai.</small>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-4 control-label">Fixed Tax</label>
                <div class="col-md-8">
                    <input type="number" step="0.01" name="fixed_tax" id="fixed_tax" class="form-control" value="0">
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-4 control-label">Taxable Amount Above</label>
                <div class="col-md-8">
                    <input type="number" step="0.01" name="taxable_amount_above" id="taxable_amount_above" class="form-control" value="0">
                    <small>Example: slab 600001 se start ho to yahan 600000 likho.</small>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-4 control-label">Tax Percentage %</label>
                <div class="col-md-8">
                    <input type="number" step="0.01" name="tax_percentage" id="tax_percentage" class="form-control" value="0">
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-4 control-label">Status</label>
                <div class="col-md-8">
                    <select name="status" id="tax_slab_status" class="form-control">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>

        </form>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn default" data-dismiss="modal">Close</button>
        <button type="button" class="btn red" id="saveTaxSlabBtn">Save Tax Slab</button>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    if (typeof $ === 'undefined') {
        alert('jQuery load nahi hui. Pehle jQuery include karo.');
        return;
    }

    var baseUrl = "<?php echo site_url(); ?>";

    $('#addTaxYearBtn').on('click', function () {
        $('#taxYearForm')[0].reset();
        $('#tax_year_id').val('');
        $('#taxYearModal').modal('show');
    });

    $('#saveTaxYearBtn').on('click', function () {
        $.ajax({
            url: baseUrl + '/Payroll_income_tax/save_tax_year',
            type: 'POST',
            data: $('#taxYearForm').serialize(),
            dataType: 'json',
            success: function (res) {
                alert(res.message);
                location.reload();
            }
        });
    });

    $('.editTaxYearBtn').on('click', function () {
        var id = $(this).data('id');

        $.ajax({
            url: baseUrl + '/Payroll_income_tax/get_tax_year/' + id,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                $('#tax_year_id').val(data.id);
                $('#tax_year').val(data.tax_year);
                $('#start_date').val(data.start_date);
                $('#end_date').val(data.end_date);
                $('#tax_year_status').val(data.status);

                $('#taxYearModal').modal('show');
            }
        });
    });

    $('.deleteTaxYearBtn').on('click', function () {
        if (!confirm('Are you sure you want to delete this tax year?')) {
            return false;
        }

        var id = $(this).data('id');

        $.ajax({
            url: baseUrl + '/Payroll_income_tax/delete_tax_year/' + id,
            type: 'POST',
            dataType: 'json',
            success: function (res) {
                alert(res.message);
                location.reload();
            }
        });
    });

    $('.manageSlabsBtn').on('click', function () {
        var taxYearId = $(this).data('id');
        var taxYearName = $(this).data('name');

        $('#current_tax_year_id').val(taxYearId);
        $('#tax_year_title').text(taxYearName);

        loadTaxSlabs(taxYearId);

        $('#taxSlabsModal').modal('show');
    });

    function loadTaxSlabs(taxYearId) {
        $.ajax({
            url: baseUrl + '/Payroll_income_tax/get_slabs/' + taxYearId,
            type: 'GET',
            dataType: 'json',
            success: function (slabs) {
                var html = '';

                if (slabs.length == 0) {
                    html = '<tr><td colspan="7" class="text-center">No slabs found</td></tr>';
                }

                $.each(slabs, function (i, slab) {
                    html += '<tr>';
                    html += '<td>' + slab.min_annual_income + '</td>';
                    html += '<td>' + (slab.max_annual_income ? slab.max_annual_income : 'No Limit') + '</td>';
                    html += '<td>' + slab.fixed_tax + '</td>';
                    html += '<td>' + slab.taxable_amount_above + '</td>';
                    html += '<td>' + slab.tax_percentage + ' %</td>';
                    html += '<td>' + (slab.status == 1 ? 'Active' : 'Inactive') + '</td>';
                    html += '<td>';
                    html += '<button class="btn btn-primary btn-sm editTaxSlabBtn" data-id="' + slab.id + '">Edit</button> ';
                    html += '<button class="btn btn-danger btn-sm deleteTaxSlabBtn" data-id="' + slab.id + '">Delete</button>';
                    html += '</td>';
                    html += '</tr>';
                });

                $('#taxSlabsTableBody').html(html);
            }
        });
    }

    $('#addTaxSlabBtn').on('click', function () {
        $('#taxSlabForm')[0].reset();

        $('#tax_slab_id').val('');
        $('#slab_tax_year_id').val($('#current_tax_year_id').val());

        $('#fixed_tax').val(0);
        $('#taxable_amount_above').val(0);
        $('#tax_percentage').val(0);
        $('#tax_slab_status').val(1);

        $('#taxSlabFormModal').modal('show');
    });

    $('#saveTaxSlabBtn').on('click', function () {
        $.ajax({
            url: baseUrl + '/Payroll_income_tax/save_slab',
            type: 'POST',
            data: $('#taxSlabForm').serialize(),
            dataType: 'json',
            success: function (res) {
                alert(res.message);

                $('#taxSlabFormModal').modal('hide');

                loadTaxSlabs($('#current_tax_year_id').val());
            }
        });
    });

    $(document).on('click', '.editTaxSlabBtn', function () {
        var id = $(this).data('id');

        $.ajax({
            url: baseUrl + '/Payroll_income_tax/get_slab/' + id,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                $('#tax_slab_id').val(data.id);
                $('#slab_tax_year_id').val(data.tax_year_id);

                $('#min_annual_income').val(data.min_annual_income);
                $('#max_annual_income').val(data.max_annual_income);
                $('#fixed_tax').val(data.fixed_tax);
                $('#taxable_amount_above').val(data.taxable_amount_above);
                $('#tax_percentage').val(data.tax_percentage);
                $('#tax_slab_status').val(data.status);

                $('#taxSlabFormModal').modal('show');
            }
        });
    });

    $(document).on('click', '.deleteTaxSlabBtn', function () {
        if (!confirm('Are you sure you want to delete this tax slab?')) {
            return false;
        }

        var id = $(this).data('id');

        $.ajax({
            url: baseUrl + '/Payroll_income_tax/delete_slab/' + id,
            type: 'POST',
            dataType: 'json',
            success: function (res) {
                alert(res.message);
                loadTaxSlabs($('#current_tax_year_id').val());
            }
        });
    });

});
</script>