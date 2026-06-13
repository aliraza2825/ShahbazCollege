<div class="page-content-wrapper">
    <div class="page-content">

        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-list"></i> Monthly Test Detail - <?php echo $month_name; ?>
                </div>
            </div>

            <div class="portlet-body">

                <?php
                $student_name = '';
                $exam_name = '';

                if (!empty($details)) {
                    $student_name = $details[0]['first_name'] . ' ' . $details[0]['last_name'];
                    $exam_name = $details[0]['exam_name'];
                }
                ?>

                <h4>
                    <strong>Student:</strong> <?php echo $student_name; ?>
                    <br>
                    <strong>Exam:</strong> <?php echo $exam_name; ?>
                    <br>
                    <strong>Month:</strong> <?php echo $month_name; ?>
                </h4>

                <hr>

                <table class="table table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Exam Name</th>
                        <th>Date</th>
                        <th>Marks</th>
                        <th>Percentage</th>
                        <th>Grade</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php if (!empty($details)): ?>
                        <?php foreach ($details as $row): ?>

                            <?php
                            $paper_percentage = 0;

                            if ($row['total_marks'] > 0) {
                                $paper_percentage = round(($row['obtain_marks'] / $row['total_marks']) * 100, 2);
                            }

                            if ($paper_percentage >= 80) {
                                $grade = 'A';
                            } elseif ($paper_percentage >= 70) {
                                $grade = 'B';
                            } elseif ($paper_percentage >= 60) {
                                $grade = 'C';
                            } elseif ($paper_percentage >= 50) {
                                $grade = 'D';
                            } else {
                                $grade = 'F';
                            }
                            ?>

                            <tr>
                                <td><?php echo !empty($row['subject_name']) ? $row['subject_name'] : $row['subject_id']; ?></td>
                                <td><?php echo $row['exam_name']; ?></td>
                                <td><?php echo $row['date']; ?></td>
                                <td><?php echo $row['obtain_marks']; ?>/<?php echo $row['total_marks']; ?></td>
                                <td><?php echo $paper_percentage; ?>%</td>
                                <td><?php echo $grade; ?></td>
                            </tr>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No details found</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>

                    <tfoot>
                    <tr>
                        <th colspan="3" style="text-align:right;">Total</th>
                        <th><?php echo $total_obtain; ?>/<?php echo $total_marks; ?></th>
                        <th><?php echo $percentage; ?>%</th>
                        <th></th>
                    </tr>
                    </tfoot>
                </table>

            </div>
        </div>

    </div>
</div>