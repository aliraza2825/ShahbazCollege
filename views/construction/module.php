<?php
    if (!function_exists('construction_project_options')) {
        function construction_project_options($projects, $selected = '') {
            $html = '<option value="">Select Project</option>';
            foreach ($projects as $project) {
                $sel = ((string) $selected === (string) $project['id']) ? ' selected' : '';
                $html .= '<option value="'.$project['id'].'"'.$sel.'>'.htmlspecialchars($project['project_name']).'</option>';
            }
            return $html;
        }
    }
    $project_costs = isset($project_costs) ? $project_costs : array();
?>
<div class="page-content-wrapper">
    <div class="page-content">
        <h3 class="page-title">Construction Management <small><?php echo ucwords(str_replace('_', ' ', $section)); ?></small></h3>

        <?php if($this->session->flashdata('message')): ?>
            <div class="alert alert-success"><?php echo $this->session->flashdata('message'); ?></div>
        <?php endif; ?>
        <?php if($this->session->flashdata('error')): ?>
            <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
        <?php endif; ?>

        <style>
            .construction-panel {
                border: 1px solid #d9e3ea;
                background: #fff;
                margin-bottom: 22px;
            }
            .construction-panel .portlet-title {
                border-bottom: 1px solid #eef1f5;
                padding: 14px 16px 8px;
            }
            .construction-panel .caption {
                font-weight: 600;
            }
            .construction-panel .actions {
                margin-top: -4px;
            }
            .construction-add-btn {
                margin-left: 4px;
                margin-bottom: 6px;
            }
            .construction-tabs {
                margin-bottom: 0;
                border-bottom-color: #dfe5ec;
            }
            .construction-tabs > li > a {
                font-weight: 600;
            }
            .construction-tab-body {
                border: 1px solid #dfe5ec;
                border-top: 0;
                background: #fff;
                padding: 16px;
                min-height: 220px;
            }
            .construction-tab-body h4 {
                margin-top: 0;
                font-weight: 600;
            }
            .construction-panel .table {
                margin-bottom: 0;
            }
            .construction-modal-form label {
                font-weight: 600;
            }
            .modal .construction-modal-form .modal-body {
                max-height: calc(100vh - 210px);
                overflow-y: auto;
                overflow-x: hidden;
                padding: 18px 24px;
            }
            .modal .construction-modal-form .modal-footer {
                position: relative;
                padding: 12px 24px;
                background: #fff;
            }
            .modal .construction-modal-form .row {
                display: flex;
                flex-wrap: wrap;
                margin-left: -8px;
                margin-right: -8px;
            }
            .modal .construction-modal-form [class*="col-md-"] {
                float: none;
                padding-left: 8px;
                padding-right: 8px;
                min-width: 0;
            }
            .modal .construction-modal-form .form-control,
            .modal .construction-modal-form select,
            .modal .construction-modal-form textarea,
            .modal .construction-modal-form input[type="text"],
            .modal .construction-modal-form input[type="number"],
            .modal .construction-modal-form input[type="date"] {
                width: 100% !important;
                max-width: 100%;
            }
            @media (min-width: 992px) {
                .modal .construction-modal-form .col-md-2 { width: 16.66666667%; }
                .modal .construction-modal-form .col-md-3 { width: 25%; }
                .modal .construction-modal-form .col-md-4 { width: 33.33333333%; }
                .modal .construction-modal-form .col-md-6 { width: 50%; }
            }
            @media (max-width: 991px) {
                .modal .construction-modal-form [class*="col-md-"] {
                    width: 100%;
                    margin-bottom: 12px;
                }
            }
            #boqModal .modal-dialog,
            #materialModal .modal-dialog,
            #attendanceModal .modal-dialog,
            #expenseModal .modal-dialog,
            #equipmentModal .modal-dialog,
            #progressModal .modal-dialog,
            #contractorModal .modal-dialog,
            .modal[id^="contractorPaymentModal"] .modal-dialog {
                width: 900px;
                max-width: calc(100% - 40px);
                margin: 30px auto;
            }
            #labourModal .modal-dialog,
            #contractorPaymentModal .modal-dialog {
                width: 650px;
                max-width: calc(100% - 40px);
                margin: 30px auto;
            }
        </style>

        <div class="row" style="margin-bottom:15px;">
            <div class="col-md-12">
                <a class="btn default" href="<?php echo site_url();?>/construction"><i class="fa fa-dashboard"></i> Dashboard</a>
                <a class="btn default" href="<?php echo site_url();?>/construction/projects"><i class="fa fa-building"></i> Projects</a>
                <a class="btn default" href="<?php echo site_url();?>/construction/boq"><i class="fa fa-list"></i> BOQ / Estimate</a>
                <a class="btn default" href="<?php echo site_url();?>/construction/work"><i class="fa fa-plus"></i> Site Work</a>
                <a class="btn default" href="<?php echo site_url();?>/construction/contractors"><i class="fa fa-briefcase"></i> Contractors</a>
                <a class="btn default" href="<?php echo site_url();?>/construction/reports"><i class="fa fa-file"></i> Reports</a>
            </div>
        </div>

        <?php if($section == 'dashboard'): ?>
            <div class="row">
                <?php
                    $cards = array(
                        'Total Projects' => @$total_projects,
                        'Total Budget' => round(@$total_budget),
                        'Total Cost' => round(@$total_expenses),
                        'Remaining Budget' => round(@$total_budget - @$total_expenses),
                        'Material Cost' => round(@$material_cost),
                        'Labour Cost' => round(@$labour_cost),
                        'Contractor Cost' => round(@$contractor_cost),
                        'Other Site Cost' => round(@$site_expense + @$equipment_cost)
                    );
                    foreach($cards as $label => $value):
                ?>
                    <div class="col-md-3">
                        <div class="dashboard-stat green">
                            <div class="visual"><i class="fa fa-building"></i></div>
                            <div class="details">
                                <div class="number"><?php echo $value; ?></div>
                                <div class="desc"><?php echo $label; ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="row">
                <div class="col-md-7">
                    <div class="portlet box green">
                        <div class="portlet-title"><div class="caption">Recent Project Costs</div></div>
                        <div class="portlet-body table-responsive">
                            <table class="table table-bordered table-hover">
                                <tr><th>Date</th><th>Project</th><th>Type</th><th>Detail</th><th>Amount</th></tr>
                                <?php foreach($recent_expenses as $row): ?>
                                    <tr>
                                        <td><?php echo $row['date']; ?></td>
                                        <td><?php echo htmlspecialchars($row['project_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['type']); ?></td>
                                        <td><?php echo htmlspecialchars($row['product_name'] ? $row['product_name'].' x '.$row['quantity'] : $row['description']); ?></td>
                                        <td><?php echo round($row['amount']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="portlet box green">
                        <div class="portlet-title"><div class="caption">Low Stock Materials</div></div>
                        <div class="portlet-body table-responsive">
                            <table class="table table-bordered">
                                <tr><th>Material</th><th>Available</th></tr>
                                <?php foreach($low_stock as $row): ?>
                                    <tr><td><?php echo htmlspecialchars($row['product_name']); ?></td><td><?php echo $row['stock_qty']; ?></td></tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if($section == 'projects'): ?>
            <div class="portlet box green">
                <div class="portlet-title"><div class="caption">Add Project</div></div>
                <div class="portlet-body form">
                    <form method="post" action="<?php echo site_url();?>/construction/save_project" class="form-horizontal">
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-4"><label>Project Name</label><input class="form-control" name="project_name" required></div>
                                <div class="col-md-4"><label>Location</label><input class="form-control" name="location"></div>
                                <div class="col-md-4"><label>Client</label><input class="form-control" name="client"></div>
                            </div><br>
                            <div class="row">
                                <div class="col-md-3"><label>Start Date</label><input type="date" class="form-control" name="start_date"></div>
                                <div class="col-md-3"><label>Expected Completion</label><input type="date" class="form-control" name="expected_completion_date"></div>
                                <div class="col-md-2"><label>Budget</label><input type="number" step="0.01" class="form-control" name="budget"></div>
                                <div class="col-md-2"><label>Status</label><select class="form-control" name="status"><option>Planning</option><option>Running</option><option>Completed</option></select></div>
                                <div class="col-md-2"><label>Manager</label><select class="form-control" name="project_manager_id"><option value="">Select</option><?php foreach($users as $user): ?><option value="<?php echo $user['user_id']; ?>"><?php echo htmlspecialchars($user['first_name'].' '.$user['last_name']); ?></option><?php endforeach; ?></select></div>
                            </div>
                        </div>
                        <div class="form-actions"><button class="btn green">Save Project</button></div>
                    </form>
                </div>
            </div>
            <div class="portlet box green">
                <div class="portlet-title"><div class="caption">Projects Summary</div></div>
                <div class="portlet-body table-responsive">
                    <table class="table table-bordered table-hover">
                        <tr><th>Name</th><th>Location</th><th>Client</th><th>Budget</th><th>Actual Cost</th><th>Remaining</th><th>Status</th></tr>
                        <?php foreach($project_costs as $project): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($project['project_name']); ?></td>
                                <td><?php echo htmlspecialchars($project['location']); ?></td>
                                <td><?php echo htmlspecialchars($project['client']); ?></td>
                                <td><?php echo round($project['budget']); ?></td>
                                <td><?php echo round($project['actual_cost']); ?></td>
                                <td><?php echo round($project['remaining_budget']); ?></td>
                                <td><?php echo htmlspecialchars($project['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <?php if($section == 'boq'): ?>
            <div class="portlet light bordered construction-panel">
                <div class="portlet-title">
                    <div class="caption"><i class="fa fa-list font-green"></i> BOQ / Estimate</div>
                    <div class="actions"><button class="btn green btn-sm" data-toggle="modal" data-target="#boqModal"><i class="fa fa-plus"></i> Add BOQ Item</button></div>
                </div>
                <div class="portlet-body table-responsive">
                    <table class="table table-bordered table-hover">
                        <tr><th>Project</th><th>Work Item</th><th>Qty</th><th>Unit</th><th>Unit Cost</th><th>Total Cost</th><th>Estimated Budget</th></tr>
                        <?php foreach($boq as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['project_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['work_item']); ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td><?php echo htmlspecialchars($row['unit']); ?></td>
                                <td><?php echo round($row['unit_cost']); ?></td>
                                <td><?php echo round($row['total_cost']); ?></td>
                                <td><?php echo round($row['estimated_budget']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
            <div class="modal fade" id="boqModal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form method="post" action="<?php echo site_url();?>/construction/save_boq" class="construction-modal-form">
                            <div class="modal-header"><button type="button" class="close" data-dismiss="modal"></button><h4 class="modal-title">Add BOQ Item</h4></div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-4"><label>Project</label><select class="form-control" name="project_id" required><?php echo construction_project_options($projects); ?></select></div>
                                    <div class="col-md-4"><label>Work Item</label><input class="form-control" name="work_item" required></div>
                                    <div class="col-md-2"><label>Qty</label><input type="number" step="0.01" class="form-control" name="quantity" required></div>
                                    <div class="col-md-2"><label>Unit</label><input class="form-control" name="unit"></div>
                                </div><br>
                                <div class="row">
                                    <div class="col-md-6"><label>Unit Cost</label><input type="number" step="0.01" class="form-control" name="unit_cost" required></div>
                                    <div class="col-md-6"><label>Estimated Budget</label><input type="number" step="0.01" class="form-control" name="estimated_budget"></div>
                                </div>
                            </div>
                            <div class="modal-footer"><button type="button" class="btn default" data-dismiss="modal">Close</button><button class="btn green">Save BOQ</button></div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if($section == 'work'): ?>
            <div class="portlet light bordered construction-panel">
                <div class="portlet-title">
                    <div class="caption"><i class="fa fa-tasks font-green"></i> Site Work</div>
                    <div class="actions">
                        <button class="btn green btn-sm construction-add-btn" data-target="#materialModal" data-toggle="modal"><i class="fa fa-plus"></i> Material</button>
                        <button class="btn green btn-sm construction-add-btn" data-target="#labourModal" data-toggle="modal"><i class="fa fa-plus"></i> Labour</button>
                        <button class="btn green btn-sm construction-add-btn" data-target="#attendanceModal" data-toggle="modal"><i class="fa fa-plus"></i> Attendance</button>
                        <button class="btn green btn-sm construction-add-btn" data-target="#expenseModal" data-toggle="modal"><i class="fa fa-plus"></i> Expense</button>
                        <button class="btn green btn-sm construction-add-btn" data-target="#equipmentModal" data-toggle="modal"><i class="fa fa-plus"></i> Equipment</button>
                        <button class="btn green btn-sm construction-add-btn" data-target="#progressModal" data-toggle="modal"><i class="fa fa-plus"></i> Progress</button>
                    </div>
                </div>
                <div class="portlet-body">
                    <ul class="nav nav-tabs construction-tabs">
                        <li class="active"><a href="#work_material" data-toggle="tab">Material</a></li>
                        <li><a href="#work_labour" data-toggle="tab">Labour</a></li>
                        <li><a href="#work_expense" data-toggle="tab">Expenses</a></li>
                        <li><a href="#work_equipment" data-toggle="tab">Equipment</a></li>
                        <li><a href="#work_progress" data-toggle="tab">Progress</a></li>
                    </ul>
                    <div class="tab-content construction-tab-body">
                        <div class="tab-pane active" id="work_material">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <tr><th>Date</th><th>Project</th><th>Material</th><th>Qty</th><th>Cost</th><th>Remarks</th></tr>
                                    <?php foreach($issues as $row): ?><tr><td><?php echo $row['issue_date']; ?></td><td><?php echo htmlspecialchars($row['project_name']); ?></td><td><?php echo htmlspecialchars($row['product_name']); ?></td><td><?php echo $row['quantity']; ?></td><td><?php echo round($row['total_cost']); ?></td><td><?php echo htmlspecialchars($row['remarks']); ?></td></tr><?php endforeach; ?>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane" id="work_labour">
                            <div class="row">
                                <div class="col-md-5">
                                    <h4>Labours</h4>
                                    <div class="table-responsive"><table class="table table-bordered table-hover"><tr><th>Project</th><th>Name</th><th>Designation</th><th>Daily Wage</th></tr><?php foreach($labours as $row): ?><tr><td><?php echo htmlspecialchars($row['project_name']); ?></td><td><?php echo htmlspecialchars($row['labour_name']); ?></td><td><?php echo htmlspecialchars($row['designation']); ?></td><td><?php echo round($row['daily_wage']); ?></td></tr><?php endforeach; ?></table></div>
                                </div>
                                <div class="col-md-7">
                                    <h4>Attendance / Payroll</h4>
                                    <div class="table-responsive"><table class="table table-bordered table-hover"><tr><th>Date</th><th>Project</th><th>Labour</th><th>Status</th><th>Overtime</th></tr><?php foreach($attendance as $row): ?><tr><td><?php echo $row['attendance_date']; ?></td><td><?php echo htmlspecialchars($row['project_name']); ?></td><td><?php echo htmlspecialchars($row['labour_name']); ?></td><td><?php echo htmlspecialchars($row['status']); ?></td><td><?php echo round($row['overtime_amount']); ?></td></tr><?php endforeach; ?></table></div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="work_expense">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <tr><th>Date</th><th>Project</th><th>Category</th><th>Amount</th><th>Description</th><th>Attachment</th></tr>
                                    <?php foreach($expenses as $row): ?><tr><td><?php echo $row['expense_date']; ?></td><td><?php echo htmlspecialchars($row['project_name']); ?></td><td><?php echo htmlspecialchars($row['category']); ?></td><td><?php echo round($row['amount']); ?></td><td><?php echo htmlspecialchars($row['description']); ?></td><td><?php if($row['attachment']): ?><a target="_blank" href="<?php echo base_url();?>uploads/<?php echo $row['attachment']; ?>">View</a><?php endif; ?></td></tr><?php endforeach; ?>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane" id="work_equipment">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <tr><th>Project</th><th>Equipment</th><th>Operator</th><th>Fuel</th><th>Maintenance</th><th>Repair</th><th>Usage</th></tr>
                                    <?php foreach($equipment as $row): ?><tr><td><?php echo htmlspecialchars($row['project_name']); ?></td><td><?php echo htmlspecialchars($row['equipment_name']); ?></td><td><?php echo htmlspecialchars($row['operator']); ?></td><td><?php echo round($row['fuel_consumption']); ?></td><td><?php echo round($row['maintenance_cost']); ?></td><td><?php echo round($row['repair_cost']); ?></td><td><?php echo htmlspecialchars($row['usage_history']); ?></td></tr><?php endforeach; ?>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane" id="work_progress">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <tr><th>Date</th><th>Project</th><th>Milestone</th><th>Foundation</th><th>Structure</th><th>Finishing</th><th>Overall</th><th>Remarks</th></tr>
                                    <?php foreach($progress as $row): ?><tr><td><?php echo $row['progress_date']; ?></td><td><?php echo htmlspecialchars($row['project_name']); ?></td><td><?php echo htmlspecialchars($row['milestone']); ?></td><td><?php echo $row['foundation_percent']; ?>%</td><td><?php echo $row['structure_percent']; ?>%</td><td><?php echo $row['finishing_percent']; ?>%</td><td><?php echo $row['overall_percent']; ?>%</td><td><?php echo htmlspecialchars($row['remarks']); ?></td></tr><?php endforeach; ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="materialModal" tabindex="-1" role="dialog"><div class="modal-dialog modal-lg"><div class="modal-content"><form method="post" action="<?php echo site_url();?>/construction/save_material_issue" class="construction-modal-form"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"></button><h4 class="modal-title">Issue Material From Inventory</h4></div><div class="modal-body"><label>Project</label><select class="form-control" name="project_id" required><?php echo construction_project_options($projects); ?></select><br><label>Inventory Product</label><select class="form-control" name="product_id" required><option value="">Select Product</option><?php foreach($products as $product): ?><option value="<?php echo $product['product_id']; ?>"><?php echo htmlspecialchars($product['product_name'].' | '.$product['campus_name'].' | '.$product['room_name'].' | Stock '.$product['stock_qty'].' | Unit '.$product['estimated_price']); ?></option><?php endforeach; ?></select><br><div class="row"><div class="col-md-4"><label>Quantity</label><input type="number" min="1" class="form-control" name="quantity" required></div><div class="col-md-4"><label>Date</label><input type="date" class="form-control" name="issue_date" value="<?php echo date('Y-m-d'); ?>" required></div><div class="col-md-4"><label>Remarks</label><input class="form-control" name="remarks"></div></div></div><div class="modal-footer"><button type="button" class="btn default" data-dismiss="modal">Close</button><button class="btn green">Issue Material</button></div></form></div></div></div>
            <div class="modal fade" id="labourModal" tabindex="-1" role="dialog"><div class="modal-dialog"><div class="modal-content"><form method="post" action="<?php echo site_url();?>/construction/save_labour" class="construction-modal-form"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"></button><h4 class="modal-title">Add Labour</h4></div><div class="modal-body"><label>Project</label><select class="form-control" name="project_id"><?php echo construction_project_options($projects); ?></select><br><label>Labour Name</label><input class="form-control" name="labour_name" required><br><label>Designation</label><input class="form-control" name="designation"><br><div class="row"><div class="col-md-4"><label>CNIC</label><input class="form-control" name="cnic"></div><div class="col-md-4"><label>Mobile</label><input class="form-control" name="mobile"></div><div class="col-md-4"><label>Daily Wage</label><input type="number" step="0.01" class="form-control" name="daily_wage"></div></div></div><div class="modal-footer"><button type="button" class="btn default" data-dismiss="modal">Close</button><button class="btn green">Save Labour</button></div></form></div></div></div>
            <div class="modal fade" id="attendanceModal" tabindex="-1" role="dialog"><div class="modal-dialog modal-lg"><div class="modal-content"><form method="post" action="<?php echo site_url();?>/construction/save_labour_attendance" class="construction-modal-form"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"></button><h4 class="modal-title">Labour Attendance / Payroll</h4></div><div class="modal-body"><div class="row"><div class="col-md-4"><label>Project</label><select class="form-control" name="project_id" required><?php echo construction_project_options($projects); ?></select></div><div class="col-md-4"><label>Labour</label><select class="form-control" name="labour_id" required><option value="">Select Labour</option><?php foreach($labours as $labour): ?><option value="<?php echo $labour['id']; ?>"><?php echo htmlspecialchars($labour['labour_name']); ?></option><?php endforeach; ?></select></div><div class="col-md-4"><label>Date</label><input type="date" class="form-control" name="attendance_date" value="<?php echo date('Y-m-d'); ?>" required></div></div><br><div class="row"><div class="col-md-4"><label>Status</label><select class="form-control" name="status"><option>Present</option><option>Absent</option></select></div><div class="col-md-4"><label>Overtime Hrs</label><input type="number" step="0.01" class="form-control" name="overtime_hours"></div><div class="col-md-4"><label>Overtime Amount</label><input type="number" step="0.01" class="form-control" name="overtime_amount"></div></div></div><div class="modal-footer"><button type="button" class="btn default" data-dismiss="modal">Close</button><button class="btn green">Save Attendance</button></div></form></div></div></div>
            <div class="modal fade" id="expenseModal" tabindex="-1" role="dialog"><div class="modal-dialog modal-lg"><div class="modal-content"><form method="post" enctype="multipart/form-data" action="<?php echo site_url();?>/construction/save_expense" class="construction-modal-form"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"></button><h4 class="modal-title">Site Expense</h4></div><div class="modal-body"><div class="row"><div class="col-md-4"><label>Project</label><select class="form-control" name="project_id" required><?php echo construction_project_options($projects); ?></select></div><div class="col-md-3"><label>Category</label><select class="form-control" name="category"><?php foreach($categories as $category): ?><option><?php echo $category; ?></option><?php endforeach; ?></select></div><div class="col-md-3"><label>Date</label><input type="date" class="form-control" name="expense_date" value="<?php echo date('Y-m-d'); ?>"></div><div class="col-md-2"><label>Amount</label><input type="number" step="0.01" class="form-control" name="amount"></div></div><br><label>Description</label><textarea class="form-control" name="description"></textarea><br><input type="file" name="attachment"></div><div class="modal-footer"><button type="button" class="btn default" data-dismiss="modal">Close</button><button class="btn green">Save Expense</button></div></form></div></div></div>
            <div class="modal fade" id="equipmentModal" tabindex="-1" role="dialog"><div class="modal-dialog modal-lg"><div class="modal-content"><form method="post" action="<?php echo site_url();?>/construction/save_equipment" class="construction-modal-form"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"></button><h4 class="modal-title">Equipment / Machinery</h4></div><div class="modal-body"><div class="row"><div class="col-md-4"><label>Project</label><select class="form-control" name="project_id"><?php echo construction_project_options($projects); ?></select></div><div class="col-md-4"><label>Equipment</label><input class="form-control" name="equipment_name" required></div><div class="col-md-4"><label>Operator</label><input class="form-control" name="operator"></div></div><br><div class="row"><div class="col-md-4"><label>Fuel</label><input type="number" step="0.01" class="form-control" name="fuel_consumption"></div><div class="col-md-4"><label>Maintenance Cost</label><input type="number" step="0.01" class="form-control" name="maintenance_cost"></div><div class="col-md-4"><label>Repair Cost</label><input type="number" step="0.01" class="form-control" name="repair_cost"></div></div><br><label>Usage History</label><textarea class="form-control" name="usage_history"></textarea></div><div class="modal-footer"><button type="button" class="btn default" data-dismiss="modal">Close</button><button class="btn green">Save Equipment</button></div></form></div></div></div>
            <div class="modal fade" id="progressModal" tabindex="-1" role="dialog"><div class="modal-dialog modal-lg"><div class="modal-content"><form method="post" action="<?php echo site_url();?>/construction/save_progress" class="construction-modal-form"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"></button><h4 class="modal-title">Work Progress</h4></div><div class="modal-body"><div class="row"><div class="col-md-4"><label>Project</label><select class="form-control" name="project_id" required><?php echo construction_project_options($projects); ?></select></div><div class="col-md-4"><label>Milestone</label><input class="form-control" name="milestone"></div><div class="col-md-4"><label>Date</label><input type="date" class="form-control" name="progress_date" value="<?php echo date('Y-m-d'); ?>"></div></div><br><div class="row"><div class="col-md-3"><label>Foundation %</label><input type="number" step="0.01" class="form-control" name="foundation_percent"></div><div class="col-md-3"><label>Structure %</label><input type="number" step="0.01" class="form-control" name="structure_percent"></div><div class="col-md-3"><label>Finishing %</label><input type="number" step="0.01" class="form-control" name="finishing_percent"></div><div class="col-md-3"><label>Overall %</label><input type="number" step="0.01" class="form-control" name="overall_percent"></div></div><br><label>Remarks</label><textarea class="form-control" name="remarks"></textarea></div><div class="modal-footer"><button type="button" class="btn default" data-dismiss="modal">Close</button><button class="btn green">Save Progress</button></div></form></div></div></div>
        <?php endif; ?>

        <?php if($section == 'entries'): ?>
            <div class="portlet box green">
                <div class="portlet-title"><div class="caption">Add Project Cost</div></div>
                <div class="portlet-body form">
                    <form method="post" action="<?php echo site_url();?>/construction/save_entry">
                        <div class="row">
                            <div class="col-md-3"><label>Project</label><select class="form-control" name="project_id" required><?php echo construction_project_options($projects); ?></select></div>
                            <div class="col-md-2"><label>Cost Type</label><select class="form-control" name="entry_type" id="construction_entry_type" required><option>Material</option><option>Labour</option><option>Site Expense</option><option>Equipment</option></select></div>
                            <div class="col-md-2"><label>Date</label><input type="date" class="form-control" name="entry_date" value="<?php echo date('Y-m-d'); ?>" required></div>
                            <div class="col-md-2 construction-amount-field"><label>Amount</label><input type="number" step="0.01" class="form-control" name="amount"></div>
                            <div class="col-md-3"><label>Remarks</label><input class="form-control" name="description"></div>
                        </div><br>
                        <div class="row construction-material-fields">
                            <div class="col-md-7"><label>Inventory Product</label><select class="form-control" name="product_id"><option value="">Select Product</option><?php foreach($products as $product): ?><option value="<?php echo $product['product_id']; ?>"><?php echo htmlspecialchars($product['product_name'].' | '.$product['campus_name'].' | '.$product['room_name'].' | Stock '.$product['stock_qty'].' | Unit '.$product['estimated_price']); ?></option><?php endforeach; ?></select></div>
                            <div class="col-md-2"><label>Quantity</label><input type="number" min="1" class="form-control" name="quantity"></div>
                        </div><br>
                        <button class="btn green">Save Cost</button>
                    </form>
                </div>
            </div>
            <div class="portlet box green">
                <div class="portlet-title"><div class="caption">Recent Cost Entries</div></div>
                <div class="portlet-body table-responsive">
                    <table class="table table-bordered table-hover">
                        <tr><th>Date</th><th>Project</th><th>Type</th><th>Detail</th><th>Amount</th></tr>
                        <?php foreach($entries as $row): ?>
                            <tr>
                                <td><?php echo $row['date']; ?></td>
                                <td><?php echo htmlspecialchars($row['project_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['type']); ?></td>
                                <td><?php echo htmlspecialchars($row['product_name'] ? $row['product_name'].' x '.$row['quantity'] : $row['description']); ?></td>
                                <td><?php echo round($row['amount']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
            <script>
                (function() {
                    function toggleConstructionEntryFields() {
                        var type = document.getElementById('construction_entry_type').value;
                        var materialFields = document.querySelectorAll('.construction-material-fields');
                        var amountFields = document.querySelectorAll('.construction-amount-field');
                        for (var i = 0; i < materialFields.length; i++) {
                            materialFields[i].style.display = type === 'Material' ? 'block' : 'none';
                        }
                        for (var j = 0; j < amountFields.length; j++) {
                            amountFields[j].style.display = type === 'Material' ? 'none' : 'block';
                        }
                    }
                    document.getElementById('construction_entry_type').onchange = toggleConstructionEntryFields;
                    toggleConstructionEntryFields();
                })();
            </script>
        <?php endif; ?>

        <?php if($section == 'contractors'): ?>
            <div class="portlet light bordered construction-panel">
                <div class="portlet-title">
                    <div class="caption"><i class="fa fa-briefcase font-green"></i> Contractors</div>
                    <div class="actions">
                        <button class="btn green btn-sm" data-toggle="modal" data-target="#contractorModal"><i class="fa fa-plus"></i> Add Contractor</button>
                    </div>
                </div>
                <div class="portlet-body table-responsive">
                    <table class="table table-bordered table-hover">
                        <tr><th>Project</th><th>Contractor</th><th>Contact</th><th>Contract Amount</th><th>Advance Agreed</th><th>Running Bills</th><th>Final / Done Amount</th><th>Paid</th><th>Remaining</th><th>Payments</th></tr>
                        <?php foreach($contractors as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['project_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['contractor_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['contact_details']); ?></td>
                                <td><?php echo round($row['contract_amount']); ?></td>
                                <td><?php echo round($row['advance_payment']); ?></td>
                                <td><?php echo round($row['running_bills']); ?></td>
                                <td><?php echo round($row['done_amount']); ?></td>
                                <td><?php echo round($row['paid_amount']); ?></td>
                                <td><?php echo round($row['remaining_amount']); ?></td>
                                <td><button class="btn blue btn-xs" data-toggle="modal" data-target="#contractorPaymentModal<?php echo $row['id']; ?>"><i class="fa fa-money"></i> Payments</button></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
            <div class="modal fade" id="contractorModal" tabindex="-1" role="dialog"><div class="modal-dialog modal-lg"><div class="modal-content"><form method="post" action="<?php echo site_url();?>/construction/save_contractor" class="construction-modal-form"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"></button><h4 class="modal-title">Add Contractor</h4></div><div class="modal-body"><label>Project</label><select class="form-control" name="project_id" required><?php echo construction_project_options($projects); ?></select><br><div class="row"><div class="col-md-6"><label>Contractor Name</label><input class="form-control" name="contractor_name" required></div><div class="col-md-6"><label>Contact Details</label><input class="form-control" name="contact_details"></div></div><br><div class="row"><div class="col-md-3"><label>Contract Amount</label><input type="number" step="0.01" class="form-control" name="contract_amount"></div><div class="col-md-3"><label>Advance Agreed</label><input type="number" step="0.01" class="form-control" name="advance_payment"></div><div class="col-md-3"><label>Running Bills</label><input type="number" step="0.01" class="form-control" name="running_bills"></div><div class="col-md-3"><label>Final / Done Amount</label><input type="number" step="0.01" class="form-control" name="final_bill"></div></div></div><div class="modal-footer"><button type="button" class="btn default" data-dismiss="modal">Close</button><button class="btn green">Save Contractor</button></div></form></div></div></div>
            <?php foreach($contractors as $contractor): ?>
                <?php $contractorPayments = isset($payments_by_contractor[$contractor['id']]) ? $payments_by_contractor[$contractor['id']] : array(); ?>
                <div class="modal fade" id="contractorPaymentModal<?php echo $contractor['id']; ?>" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form method="post" action="<?php echo site_url();?>/construction/save_contractor_payment" class="construction-modal-form">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal"></button>
                                    <h4 class="modal-title">Payments - <?php echo htmlspecialchars($contractor['contractor_name']); ?></h4>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="contractor_id" value="<?php echo $contractor['id']; ?>">
                                    <div class="row">
                                        <div class="col-md-4"><label>Date</label><input type="date" class="form-control" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required></div>
                                        <div class="col-md-4"><label>Amount</label><input type="number" step="0.01" class="form-control" name="amount" required></div>
                                        <div class="col-md-4"><label>Payment Type</label><select class="form-control" name="payment_type"><option>Advance</option><option>Running Bill</option><option>Final Bill</option></select></div>
                                    </div><br>
                                    <label>Remarks</label>
                                    <textarea class="form-control" name="remarks"></textarea><br>
                                    <h4>Payment History</h4>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <tr><th>Date</th><th>Type</th><th>Amount</th><th>Remarks</th></tr>
                                            <?php foreach($contractorPayments as $payment): ?>
                                                <tr>
                                                    <td><?php echo $payment['payment_date']; ?></td>
                                                    <td><?php echo htmlspecialchars($payment['payment_type']); ?></td>
                                                    <td><?php echo round($payment['amount']); ?></td>
                                                    <td><?php echo htmlspecialchars($payment['remarks']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </table>
                                    </div>
                                </div>
                                <div class="modal-footer"><button type="button" class="btn default" data-dismiss="modal">Close</button><button class="btn green">Save Payment</button></div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if($section == 'reports'): ?>
            <div class="portlet light bordered construction-panel">
                <div class="portlet-title">
                    <div class="caption"><i class="fa fa-file font-green"></i> Reports</div>
                </div>
                <div class="portlet-body">
                    <ul class="nav nav-tabs construction-tabs">
                        <li class="active"><a href="#report_cost" data-toggle="tab">Project Cost</a></li>
                        <li><a href="#report_contractor" data-toggle="tab">Contractors</a></li>
                        <li><a href="#report_material" data-toggle="tab">Materials</a></li>
                        <li><a href="#report_labour" data-toggle="tab">Labour</a></li>
                        <li><a href="#report_expense" data-toggle="tab">Expenses</a></li>
                        <li><a href="#report_equipment" data-toggle="tab">Equipment</a></li>
                        <li><a href="#report_progress" data-toggle="tab">Progress</a></li>
                    </ul>
                    <div class="tab-content construction-tab-body">
                        <div class="tab-pane active" id="report_cost">
                            <div class="table-responsive"><table class="table table-bordered table-hover"><tr><th>Project</th><th>Budget</th><th>Material</th><th>Labour</th><th>Contractor</th><th>Site Expense</th><th>Equipment</th><th>Actual Cost</th><th>Remaining Budget</th></tr><?php foreach($project_costs as $row): ?><tr><td><?php echo htmlspecialchars($row['project_name']); ?></td><td><?php echo round($row['budget']); ?></td><td><?php echo round($row['material_cost']); ?></td><td><?php echo round($row['labour_cost']); ?></td><td><?php echo round($row['contractor_cost']); ?></td><td><?php echo round($row['site_expense']); ?></td><td><?php echo round($row['equipment_cost']); ?></td><td><?php echo round($row['actual_cost']); ?></td><td><?php echo round($row['remaining_budget']); ?></td></tr><?php endforeach; ?></table></div>
                        </div>
                        <div class="tab-pane" id="report_contractor">
                            <div class="table-responsive"><table class="table table-bordered table-hover"><tr><th>Project</th><th>Contractor</th><th>Done Amount</th><th>Paid</th><th>Remaining</th></tr><?php foreach($contractors as $row): ?><tr><td><?php echo htmlspecialchars($row['project_name']); ?></td><td><?php echo htmlspecialchars($row['contractor_name']); ?></td><td><?php echo round($row['done_amount']); ?></td><td><?php echo round($row['paid_amount']); ?></td><td><?php echo round($row['remaining_amount']); ?></td></tr><?php endforeach; ?></table></div>
                        </div>
                        <div class="tab-pane" id="report_material">
                            <div class="table-responsive"><table class="table table-bordered table-hover"><tr><th>Date</th><th>Project</th><th>Material</th><th>Qty</th><th>Cost</th></tr><?php foreach($material as $row): ?><tr><td><?php echo $row['issue_date']; ?></td><td><?php echo htmlspecialchars($row['project_name']); ?></td><td><?php echo htmlspecialchars($row['product_name']); ?></td><td><?php echo $row['quantity']; ?></td><td><?php echo round($row['total_cost']); ?></td></tr><?php endforeach; ?></table></div>
                        </div>
                        <div class="tab-pane" id="report_labour">
                            <div class="table-responsive"><table class="table table-bordered table-hover"><tr><th>Project</th><th>Labour</th><th>Month</th><th>Payable</th><th>Paid</th><th>Remarks</th></tr><?php foreach($labour as $row): ?><tr><td><?php echo htmlspecialchars($row['project_name']); ?></td><td><?php echo htmlspecialchars($row['labour_name']); ?></td><td><?php echo htmlspecialchars($row['payroll_month']); ?></td><td><?php echo round($row['payable_amount']); ?></td><td><?php echo round($row['paid_amount']); ?></td><td><?php echo htmlspecialchars($row['remarks']); ?></td></tr><?php endforeach; ?></table></div>
                        </div>
                        <div class="tab-pane" id="report_expense">
                            <div class="table-responsive"><table class="table table-bordered table-hover"><tr><th>Date</th><th>Project</th><th>Category</th><th>Amount</th><th>Description</th><th>Attachment</th></tr><?php foreach($expenses as $row): ?><tr><td><?php echo $row['expense_date']; ?></td><td><?php echo htmlspecialchars($row['project_name']); ?></td><td><?php echo htmlspecialchars($row['category']); ?></td><td><?php echo round($row['amount']); ?></td><td><?php echo htmlspecialchars($row['description']); ?></td><td><?php if($row['attachment']): ?><a target="_blank" href="<?php echo base_url();?>uploads/<?php echo $row['attachment']; ?>">View</a><?php endif; ?></td></tr><?php endforeach; ?></table></div>
                        </div>
                        <div class="tab-pane" id="report_equipment">
                            <div class="table-responsive"><table class="table table-bordered table-hover"><tr><th>Project</th><th>Equipment</th><th>Operator</th><th>Fuel</th><th>Maintenance</th><th>Repair</th><th>Usage</th></tr><?php foreach($equipment as $row): ?><tr><td><?php echo htmlspecialchars($row['project_name']); ?></td><td><?php echo htmlspecialchars($row['equipment_name']); ?></td><td><?php echo htmlspecialchars($row['operator']); ?></td><td><?php echo round($row['fuel_consumption']); ?></td><td><?php echo round($row['maintenance_cost']); ?></td><td><?php echo round($row['repair_cost']); ?></td><td><?php echo htmlspecialchars($row['usage_history']); ?></td></tr><?php endforeach; ?></table></div>
                        </div>
                        <div class="tab-pane" id="report_progress">
                            <div class="table-responsive"><table class="table table-bordered table-hover"><tr><th>Date</th><th>Project</th><th>Milestone</th><th>Foundation</th><th>Structure</th><th>Finishing</th><th>Overall</th><th>Remarks</th></tr><?php foreach($progress as $row): ?><tr><td><?php echo $row['progress_date']; ?></td><td><?php echo htmlspecialchars($row['project_name']); ?></td><td><?php echo htmlspecialchars($row['milestone']); ?></td><td><?php echo $row['foundation_percent']; ?>%</td><td><?php echo $row['structure_percent']; ?>%</td><td><?php echo $row['finishing_percent']; ?>%</td><td><?php echo $row['overall_percent']; ?>%</td><td><?php echo htmlspecialchars($row['remarks']); ?></td></tr><?php endforeach; ?></table></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
