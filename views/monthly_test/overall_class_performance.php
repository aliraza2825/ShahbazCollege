<div class="page-content-wrapper">
    <div class="page-content">

        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-bar-chart"></i> Monthly Class Performance Overview
                </div>
            </div>

            <div class="portlet-body table-responsive">

                <table class="table table-bordered table-hover" id="sample_2">
                    <thead>
                    <tr>
                        <th>Month</th>
                        <th>Class</th>
                        <th>Exam Name</th>
                        <th>Appeared</th>
                        <th>Passed</th>
                        <th>Failed</th>
                        <th>Avg Marks</th>
                        <th>Highest</th>
                        <th>Lowest</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php foreach($report as $row): ?>
                        <tr>
                            <td><?php echo $row['month_name']; ?></td>
                            <td><?php echo $row['class']; ?></td>
                            <td><?php echo $row['exam_name']; ?></td>
                            <td><?php echo $row['appeared']; ?></td>
                            <td><?php echo $row['passed']; ?></td>
                            <td><?php echo $row['failed']; ?></td>
                            <td><?php echo $row['avg_marks']; ?>%</td>
                            <td><?php echo $row['highest']; ?>%</td>
                            <td><?php echo $row['lowest']; ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <a href="<?php echo site_url(); ?>/collegepapers/improvement_report" class="btn default">
                    Back
                </a>

            </div>
        </div>

    </div>
</div>