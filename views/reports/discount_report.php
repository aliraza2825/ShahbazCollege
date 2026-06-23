<div class="page-content-wrapper">
    <div class="page-content">
        <h3 class="page-title">Discount Report</h3>

        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption"><i class="fa fa-filter"></i> Filters</div>
            </div>
            <div class="portlet-body form">
                <form class="form-horizontal" method="post" action="<?php echo site_url(); ?>/reports/discount_report">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label>From Date</label>
                                <input type="date" name="from_date" class="form-control" value="<?php echo $from_date; ?>">
                            </div>
                            <div class="col-md-4">
                                <label>To Date</label>
                                <input type="date" name="to_date" class="form-control" value="<?php echo $to_date; ?>">
                            </div>
                            <div class="col-md-4" style="padding-top:24px;">
                                <button type="submit" class="btn green"><i class="fa fa-search"></i> Search</button>
                                <a href="<?php echo site_url(); ?>/reports/discount_report" class="btn default">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php
            $totalDiscount = 0;
            foreach ($discounts as $discount) {
                $totalDiscount += (float) $discount['discount'];
            }
        ?>
        <div class="row">
            <div class="col-md-3">
                <div class="dashboard-stat green">
                    <div class="visual"><i class="fa fa-list"></i></div>
                    <div class="details">
                        <div class="number"><?php echo count($discounts); ?></div>
                        <div class="desc">Approved Discounts</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-stat blue">
                    <div class="visual"><i class="fa fa-money"></i></div>
                    <div class="details">
                        <div class="number"><?php echo round($totalDiscount); ?></div>
                        <div class="desc">Total Discount</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption"><i class="fa fa-table"></i> Discount Details</div>
            </div>
            <div class="portlet-body table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Apply Date</th>
                        <th>Student</th>
                        <th>Campus</th>
                        <th>Course / Class</th>
                        <th>Remaining Fee</th>
                        <th>Discount</th>
                        <th>Applied By</th>
                        <th>Approved By</th>
                        <th>Approved Date</th>
                        <th>Reason</th>
                        <th>Application</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($discounts)): ?>
                        <tr>
                            <td colspan="12" class="text-center">No discount records found.</td>
                        </tr>
                    <?php endif; ?>
                    <?php $i = 1; foreach ($discounts as $discount): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo $discount['created_at']; ?></td>
                            <td>
                                <?php echo htmlspecialchars($discount['first_name'] . ' ' . $discount['last_name']); ?><br>
                                <small>Roll #: <?php echo htmlspecialchars($discount['roll_no']); ?></small><br>
                                <small>CNIC: <?php echo htmlspecialchars($discount['cnic']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($discount['campus_name']); ?></td>
                            <td><?php echo htmlspecialchars($discount['course_name'] . ' / ' . $discount['class_name']); ?></td>
                            <td><?php echo round($discount['remaining_fee']); ?></td>
                            <td><?php echo round($discount['discount']); ?></td>
                            <td><?php echo htmlspecialchars($discount['created_by']); ?></td>
                            <td><?php echo htmlspecialchars(!empty($discount['approved_by']) ? $discount['approved_by'] : 'Not saved'); ?></td>
                            <td><?php echo !empty($discount['approved_at']) ? $discount['approved_at'] : '-'; ?></td>
                            <td><?php echo htmlspecialchars($discount['reason']); ?></td>
                            <td>
                                <?php if (!empty($discount['application'])): ?>
                                    <a target="_blank" class="btn btn-xs purple" href="<?php echo base_url(); ?>uploads/<?php echo $discount['application']; ?>">View</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th colspan="6" class="text-right">Total</th>
                        <th><?php echo round($totalDiscount); ?></th>
                        <th colspan="5"></th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
