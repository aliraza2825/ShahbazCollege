<?php
    $grouped = array();
    foreach ($rows as $row) {
        if (!isset($grouped[$row['group']])) {
            $grouped[$row['group']] = array();
        }
        $grouped[$row['group']][] = $row;
    }
?>
<div class="page-content-wrapper">
    <div class="page-content">
        <h3 class="page-title">Chart of Accounts <small>Financial Year</small></h3>

        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption"><i class="fa fa-calendar"></i> Financial Year Filter</div>
            </div>
            <div class="portlet-body form">
                <form method="get" action="<?php echo site_url();?>/accounts/chart_of_accounts" class="form-horizontal">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-3">
                                <label>From Date</label>
                                <input type="date" name="from_date" value="<?php echo $from_date; ?>" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label>To Date</label>
                                <input type="date" name="to_date" value="<?php echo $to_date; ?>" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label>&nbsp;</label><br>
                                <button class="btn green"><i class="fa fa-search"></i> View</button>
                                <a href="<?php echo site_url();?>/accounts/chart_of_accounts" class="btn default">Current Year</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="dashboard-stat purple">
                    <div class="visual"><i class="fa fa-history"></i></div>
                    <div class="details">
                        <div class="number"><?php echo number_format($totals['opening'], 2); ?></div>
                        <div class="desc">Opening / Carry Forward</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-stat green">
                    <div class="visual"><i class="fa fa-arrow-down"></i></div>
                    <div class="details">
                        <div class="number"><?php echo number_format($totals['credit'], 2); ?></div>
                        <div class="desc">Credit / Received</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-stat red">
                    <div class="visual"><i class="fa fa-arrow-up"></i></div>
                    <div class="details">
                        <div class="number"><?php echo number_format($totals['debit'], 2); ?></div>
                        <div class="desc">Debit / Spent</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-stat blue">
                    <div class="visual"><i class="fa fa-balance-scale"></i></div>
                    <div class="details">
                        <div class="number"><?php echo number_format($totals['balance'], 2); ?></div>
                        <div class="desc">Net Balance</div>
                    </div>
                </div>
            </div>
        </div>

        <?php foreach ($grouped as $group => $items): ?>
            <?php
                $groupOpening = array_sum(array_column($items, 'opening'));
                $groupCredit = array_sum(array_column($items, 'credit'));
                $groupDebit = array_sum(array_column($items, 'debit'));
                $groupBalance = array_sum(array_column($items, 'balance'));
            ?>
            <div class="portlet box grey-cascade">
                <div class="portlet-title">
                    <div class="caption"><i class="fa fa-list"></i> <?php echo htmlspecialchars($group); ?></div>
                    <div class="tools" style="color:#fff;padding-top:3px;">
                        Opening: <?php echo number_format($groupOpening, 2); ?> |
                        Credit: <?php echo number_format($groupCredit, 2); ?> |
                        Debit: <?php echo number_format($groupDebit, 2); ?> |
                        Balance: <?php echo number_format($groupBalance, 2); ?>
                    </div>
                </div>
                <div class="portlet-body table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Account / Head</th>
                                <th>Type</th>
                                <th style="text-align:right;">Opening / Carry Forward</th>
                                <th style="text-align:right;">Credit</th>
                                <th style="text-align:right;">Debit</th>
                                <th style="text-align:right;">Balance</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['type']); ?></td>
                                    <td style="text-align:right;"><?php echo number_format($row['opening'], 2); ?></td>
                                    <td style="text-align:right;"><?php echo number_format($row['credit'], 2); ?></td>
                                    <td style="text-align:right;"><?php echo number_format($row['debit'], 2); ?></td>
                                    <td style="text-align:right;font-weight:bold;"><?php echo number_format($row['balance'], 2); ?></td>
                                    <td>
                                        <?php if (!empty($row['url'])): ?>
                                            <a target="_blank" href="<?php echo $row['url']; ?>" class="btn blue btn-xs"><i class="fa fa-eye"></i> View</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Total</th>
                                <th></th>
                                <th style="text-align:right;"><?php echo number_format($groupOpening, 2); ?></th>
                                <th style="text-align:right;"><?php echo number_format($groupCredit, 2); ?></th>
                                <th style="text-align:right;"><?php echo number_format($groupDebit, 2); ?></th>
                                <th style="text-align:right;"><?php echo number_format($groupBalance, 2); ?></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
