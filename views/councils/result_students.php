<?php
$myAccess = checkUserAccess();

if (!function_exists('getOrdinal')) {
    function getOrdinal($number)
    {
        $suffixes = ['th','st','nd','rd','th','th','th','th','th','th'];

        if (($number % 100) >= 11 && ($number % 100) <= 13) {
            return $number . 'th';
        }

        return $number . $suffixes[$number % 10];
    }
}

$is_year = $course['course_type'] == "Annual" ? "Year" : $course['course_type'];

$class = $this->uri->segment(6);
$course_id = $this->uri->segment(3);
$exam_no = $this->uri->segment(5);

if (!isset($exams)) {
    $exams = [];
}

if (!isset($roll_map)) {
    $roll_map = [];
}

if (!isset($paper_result_map)) {
    $paper_result_map = [];
}
?>

<div class="page-content-wrapper">
    <div class="page-content">

        <?php if (@$this->session->userdata('message')): ?>
            <div class="alert alert-success">
                <button class="close" data-close="alert"></button>
                <span><?php echo $this->session->userdata('message'); ?></span>
            </div>
        <?php endif; ?>

        <?php if (@$this->session->userdata('error')): ?>
            <div class="alert alert-danger">
                <button class="close" data-close="alert"></button>
                <span><?php echo $this->session->userdata('error'); ?></span>
            </div>
        <?php endif; ?>

        <div class="caption" style="font-size:15px; line-height:24px;">
            <i class="fa fa-graduation-cap" style="color:#4CAF50;"></i>
            <strong>Fee Creation Information</strong>

            <div style="margin-top:10px; padding:10px; background:#f8f9fa; border-left:4px solid #4CAF50; border-radius:4px;">
                <div>
                    <i class="fa fa-info-circle text-primary"></i>
                    <strong>Note:</strong> The nearest upcoming Active Exam will be considered for Fee Creation.
                </div>

                <div style="margin-top:8px;">
                    <i class="fa fa-file-text-o"></i>
                    <strong>Exam No:</strong>
                    <span class="badge badge-info">
                        <?php echo $exam_no.' '.$current_exam_sequence['first_year_type']; ?>
                    </span>
                </div>

                <div style="margin-top:5px;">
                    <i class="fa fa-users"></i>
                    <strong>Class:</strong>
                    <span class="badge badge-success">
                        <?php echo getOrdinal($class)." ".$is_year; ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">

                <div class="portlet box green">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i> Council Roll Numbers
                        </div>
                    </div>

                    <div class="portlet-body form">

                        <div class="col-md-6">
                            <form class="form-horizontal" method="post" action="<?php echo site_url(); ?>/punjab_council_roll_number/upload_result" enctype="multipart/form-data">
                                <div class="form-body">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Upload Csv File <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="hidden" name="class" value="<?php echo $class; ?>">
                                            <input type="hidden" name="council_exam_no" value="<?php echo $exam_no; ?>">
                                            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                                            <input type="file" class="form-control" name="roll_no" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-offset-3 col-md-9">
                                            <button type="submit" class="btn green">Upload CSV File</button>
                                            <button onclick="location.href='<?php echo site_url(); ?>'" type="button" class="btn default">Cancel</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="col-md-6">
                            <form class="form-horizontal" method="post" action="<?php echo site_url(); ?>/punjab_council_roll_number/upload_result_cards" enctype="multipart/form-data">
                                <div class="form-body">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Upload Images <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="hidden" name="class" value="<?php echo $class; ?>">
                                            <input type="hidden" name="council_exam_no" value="<?php echo $exam_no; ?>">
                                            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                                            <input type="file" name="filefield[]" multiple required>
                                            <br>
                                            <span class="alert alert-danger">Maximum 75 files will be uploaded. Format should be .jpg</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-offset-3 col-md-9">
                                            <button type="submit" class="btn green">Upload</button>
                                            <button onclick="location.href='<?php echo site_url(); ?>'" type="button" class="btn default">Cancel</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="portlet box grey-cascade">
                                    <div class="portlet-body">
                                        <h2 class="text-center">Sample Format of Result in Excel Sheet</h2>
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">A</th>
                                                    <th class="text-center">B</th>
                                                    <th class="text-center">C</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="text-center">Roll No</td>
                                                    <td class="text-center">Name &amp; Father's Name</td>
                                                    <td class="text-center">Remarks</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <div class="row">
            <div class="col-md-12">

                <div class="portlet box grey-cascade">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i> All Students ( <?php echo $course['course_name']; ?> )
                        </div>

                        <div style="margin-bottom:15px; display:flex; justify-content:flex-end; gap:10px;">
                            <form method="post" action="<?php echo site_url('councils/create_fee_for_all'); ?>" id="createFeeForAllForm">
                                <input type="hidden" name="bulk_fee_payload" id="bulk_fee_payload">
                                <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                                <input type="hidden" name="current_exam_no" value="<?php echo $exam_no; ?>">
                                <input type="hidden" name="exam_class" value="<?php echo $class; ?>">
                                <input type="hidden" name="council_sequence_id" value="<?php echo $sequence['council_sequence_id']; ?>">
                                <div style="margin-top:10px;">
                                    <span class="label label-info" id="feeLoadCounter">
                                        Fee Status Loading: 0 / <?php echo count($students); ?>
                                    </span>
                                </div>
                                <button type="button" id="createFeeForAllBtn" class="btn btn-danger">
                                    Loading Fee Status...
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="portlet-body">
                        <div class="table-scroll">

                            <table class="table table-bordered table-hover" id="sample_2">
                                <thead>
                                    <tr>
                                        <th class="hidden">Hidden</th>
                                        <th>Sr.</th>
                                        <th>Student</th>
                                        <th>CNIC</th>
                                        <th>Class</th>
                                        <th>Status</th>
                                        <th>Roll No</th>

                                        <?php if ($sequence['action_type'] != 'fee'): ?>
                                            <th><?php echo $sequence['type_name']; ?></th>
                                        <?php endif; ?>

                                        <?php foreach ($exams as $exam): ?>
                                            <th><?php echo $exam['paper_name'].'<br>'.$exam['name']; ?></th>
                                        <?php endforeach; ?>

                                        <th>Next Fee Status</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php
                                    $i = 1;
                                    $total_students = count($students);
                                    $total_pass = 0;
                                    $total_fail = 0;
                                    $total_absent = 0;
                                    $college_totals = [];

                                    $paper_pass = [];
                                    $paper_fail = [];

                                    foreach ($exams as $exam) {
                                        $paper_pass[$exam['council_exam_id']] = 0;
                                        $paper_fail[$exam['council_exam_id']] = 0;
                                    }

                                    foreach ($students as $student):

                                        $roll_number_and_result = isset($roll_map[$student['cnic']])
                                            ? $roll_map[$student['cnic']]
                                            : [];
                                            
                                        $college_name = !empty($student['campus_name']) ? $student['campus_name'] : 'N/A';

                                        if (!isset($college_totals[$college_name])) {
                                            $college_totals[$college_name] = [
                                                'students' => 0,
                                                'pass' => 0,
                                                'fail' => 0,
                                                'absent' => 0,
                                            ];
                                        }
                                        
                                        $college_totals[$college_name]['students']++;
                                    ?>

                                    <tr class="odd gradeX">
                                        <td class="hidden"><?php echo $i; ?></td>
                                        <td><?php echo $i; ?></td>

                                        <td>
                                            <?php
                                            echo $student['roll_no'].'<br>'.
                                                 $student['first_name'].' '.$student['last_name'].'<br>'.
                                                 $student['father_name'].'<br>'.
                                                 $student['campus_name'];
                                            ?>
                                        </td>

                                        <td>
                                            <?php echo $student['cnic'].'<br><br>Mobile - '.$student['mobile'].'<br>Emergency No - '.$student['emergency_no']; ?>
                                        </td>

                                        <td><?php echo $student['class_name']; ?></td>

                                        <td><?php echo $student['status'] == 1 ? 'Active' : 'Inactive'; ?></td>

                                        <td>
                                            <?php echo !empty($roll_number_and_result) ? '<b>'.$roll_number_and_result['roll_no'].'</b>' : ''; ?>
                                        </td>

                                        <?php if ($sequence['action_type'] == 'add_result'): ?>
                                            <td>
                                                <?php
                                                if (!empty($roll_number_and_result['result_remarks'])):

                                                    $info = $roll_number_and_result;
                                                    $remarks = strtolower(trim($info['result_remarks']));

                                                    if ($remarks == 'pass' || $remarks == 'pass*') {
                                                        $total_pass++;
                                                        $college_totals[$college_name]['pass']++;
                                                    
                                                    } elseif (stripos($remarks, 'absent') !== false) {
                                                        $total_absent++;
                                                        $college_totals[$college_name]['absent']++;
                                                    
                                                    } elseif (stripos($remarks, 'fail') !== false) {
                                                        $total_fail++;
                                                        $college_totals[$college_name]['fail']++;
                                                    }
                                                ?>

                                                <div style="font-size:13px; margin-bottom:8px;">
                                                    <label><?php echo $info['result_remarks']; ?></label>

                                                    <div style="margin-bottom:5px; border-bottom:1px dashed #ccc; padding-bottom:5px;">
                                                        <?php if (!empty($info['result_image'])): ?>
                                                            <a href="<?php echo base_url($info['result_image']); ?>" target="_blank">
                                                                <img src="<?php echo base_url($info['result_image']); ?>" style="max-width:60px; cursor:pointer;">
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <?php endif; ?>

                                                <?php if (!empty($roll_number_and_result)): ?>
                                                    <button type="button" class="btn btn-xs btn-success add-comment-btn">Update Result</button>
                                                <?php endif; ?>

                                                <form method="post"
                                                      action="<?php echo site_url('councils/save_roll_no'); ?>"
                                                      enctype="multipart/form-data"
                                                      class="add-comment-form"
                                                      style="display:none; margin-top:8px;">

                                                    <input type="hidden" name="council_sequence_id" value="<?php echo $sequence['council_sequence_id']; ?>">
                                                    <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                                                    <input type="hidden" name="exam_no" value="<?php echo $exam_no; ?>">
                                                    <input type="hidden" name="class" value="<?php echo $class; ?>">
                                                    <input type="hidden" name="save_type" value="result">
                                                    <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">

                                                    <input type="file" name="image" class="form-control" style="margin-bottom:5px;">

                                                    <select name="type" class="form-control type-select" style="margin-bottom:5px;">
                                                        <option value="">Select Type</option>
                                                        <option value="direct">Direct</option>
                                                        <option value="marks">Marks</option>
                                                    </select>

                                                    <input type="text"
                                                           name="roll_no"
                                                           class="form-control result-marks"
                                                           placeholder="Enter Result Remarks"
                                                           style="margin-bottom:5px; display:none;">

                                                    <select name="roll_no" class="form-control result-direct" style="margin-bottom:5px; display:none;">
                                                        <option value="">Select Result</option>
                                                        <option value="Pass">Pass</option>
                                                        <option value="Pass*">Pass*</option>
                                                        <option value="Fail">Fail</option>
                                                    </select>

                                                    <?php if (@$myAccess[0]['council_report_add_information_can_add_expense'] == 1 || $this->session->userdata('role') == 'Admin'): ?>
                                                        <button type="submit" class="btn btn-xs btn-primary">Save</button>
                                                    <?php endif; ?>
                                                </form>
                                            </td>
                                        <?php endif; ?>

                                        <?php foreach ($exams as $exam): ?>
                                            <td>
                                                <?php
                                                $result = [];

                                                if (!empty($roll_number_and_result['id'])) {
                                                    $result_key = $roll_number_and_result['id'].'_'.$exam['council_exam_id'];

                                                    if (isset($paper_result_map[$result_key])) {
                                                        $result = $paper_result_map[$result_key];
                                                    }
                                                }

                                                if (!empty($result)):
                                                    if ($result['result'] == 'Pass' || $result['result'] == 'Pass*') {
                                                        $paper_pass[$exam['council_exam_id']]++;
                                                    } elseif ($result['result'] == 'Fail') {
                                                        $paper_fail[$exam['council_exam_id']]++;
                                                    }
                                                ?>

                                                    <div style="font-size:13px; margin-bottom:8px;">
                                                        <label><?php echo $result['result']; ?></label>
                                                    </div>

                                                <?php endif; ?>

                                                <?php if (!empty($roll_number_and_result)): ?>
                                                    <button type="button" class="btn btn-xs btn-success add-comment-btn">
                                                        <?php echo $exam['paper_name']; ?><br>Result
                                                    </button>
                                                <?php endif; ?>

                                                <form method="post"
                                                      action="<?php echo site_url('councils/save_result'); ?>"
                                                      class="add-comment-form"
                                                      style="display:none; margin-top:8px;">

                                                    <input type="hidden" name="council_exam_id" value="<?php echo $exam['council_exam_id']; ?>">
                                                    <input type="hidden" name="punjab_council_roll_number_id" value="<?php echo !empty($roll_number_and_result['id']) ? $roll_number_and_result['id'] : ''; ?>">

                                                    <select name="type" class="form-control type-select" style="margin-bottom:5px;">
                                                        <option value="">Select Type</option>
                                                        <option value="direct">Direct</option>
                                                        <option value="marks">Marks</option>
                                                    </select>

                                                    <select name="result_direct" class="form-control result-direct" style="margin-bottom:5px; display:none;">
                                                        <option value="">Select Result</option>
                                                        <option value="Pass">Pass</option>
                                                        <option value="Pass*">Pass*</option>
                                                        <option value="Fail">Fail</option>
                                                    </select>

                                                    <input type="number"
                                                           name="result_marks"
                                                           class="form-control result-marks"
                                                           placeholder="Enter Marks"
                                                           style="margin-bottom:5px; display:none;">

                                                    <button type="submit" class="btn btn-xs btn-primary">Save</button>
                                                </form>
                                            </td>
                                        <?php endforeach; ?>

                                        <td class="next-fee-status" data-student-id="<?php echo $student['student_id']; ?>">
                                            <span class="label label-info">Loading...</span>
                                        </td>
                                    </tr>

                                    <?php
                                        $i++;
                                    endforeach;
                                    ?>
                                </tbody>

                                <tfoot>
                                    <tr style="background:#f5f5f5;font-weight:bold">
                                        <td colspan="6">Total Students</td>
                                        <td><?php echo $total_students; ?></td>
                                        <?php foreach ($exams as $exam): ?>
                                            <td></td>
                                        <?php endforeach; ?>
                                        <td></td>
                                    </tr>

                                    <tr style="background:#f5f5f5;font-weight:bold">
                                        <td colspan="6">Total Results</td>
                                        <td><?php echo ($total_pass + $total_fail + $total_absent); ?></td>
                                        <?php foreach ($exams as $exam): ?>
                                            <td></td>
                                        <?php endforeach; ?>
                                        <td></td>
                                    </tr>

                                    <tr style="background:#f5f5f5;font-weight:bold">
                                        <td colspan="6">Total Pass</td>
                                        <td><?php echo $total_pass; ?></td>
                                        <?php foreach ($exams as $exam): ?>
                                            <td><?php echo $paper_pass[$exam['council_exam_id']]; ?></td>
                                        <?php endforeach; ?>
                                        <td></td>
                                    </tr>

                                    <tr style="background:#f5f5f5;font-weight:bold">
                                        <td colspan="6">Total Fail</td>
                                        <td><?php echo $total_fail; ?></td>
                                        <?php foreach ($exams as $exam): ?>
                                            <td><?php echo $paper_fail[$exam['council_exam_id']]; ?></td>
                                        <?php endforeach; ?>
                                        <td></td>
                                    </tr>

                                    <tr style="background:#f5f5f5;font-weight:bold">
                                        <td colspan="6">Total Absent</td>
                                        <td colspan="<?php echo count($exams) + 2; ?>"><?php echo $total_absent; ?></td>
                                    </tr>
                                
                                    <tr style="background:#f5f5f5;font-weight:bold">
                                        <td colspan="6">% Pass With Absent</td>
                                        <td colspan="<?php echo count($exams) + 2; ?>">
                                            <?php
                                            echo $total_students > 0
                                                ? round((($total_pass + $total_fail + $total_absent) / $total_students) * 100, 2).'%'
                                                : '0%';
                                            ?>
                                        </td>
                                    </tr>

                                    <tr style="background:#f5f5f5;font-weight:bold">
                                        <td colspan="6">% Pass Without Absent</td>
                                        <td colspan="<?php echo count($exams) + 2; ?>">
                                            <?php
                                            $appeared_students = $total_students - $total_absent;

                                            echo $appeared_students > 0
                                                ? round((($total_pass + $total_fail + $total_absent) / $appeared_students) * 100, 2).'%'
                                                : '0%';
                                            ?>
                                        </td>
                                    </tr>
                                    <tr style="background:#dff0d8;font-weight:bold">
    <td colspan="<?php echo count($exams) + 8; ?>">
        College Wise Result Summary
    </td>
</tr>

<?php foreach ($college_totals as $college_name => $ct): ?>

<?php
    $students = $ct['students'];
    $pass = $ct['pass'];
    $fail = $ct['fail'];
    $absent = $ct['absent'];

    $appeared = $students - $absent;

    // Pass %
    $pass_with_absent = $students > 0
        ? round(($pass / $students) * 100, 2)
        : 0;

    $pass_without_absent = $appeared > 0
        ? round(($pass / $appeared) * 100, 2)
        : 0;
?>

<tr style="background:#f9f9f9;font-weight:bold">
    <td colspan="6"><?php echo $college_name; ?></td>
    <td colspan="<?php echo count($exams) + 2; ?>">
        Students: <?php echo $students; ?> |
        Pass: <?php echo $pass; ?> |
        Fail: <?php echo $fail; ?> |
        Absent: <?php echo $absent; ?> |
        % Pass With Absent: <?php echo $pass_with_absent; ?>% |
        % Pass Without Absent: <?php echo $pass_without_absent; ?>%
    </td>
</tr>

<?php endforeach; ?>
                                </tfoot>
                            </table>

                        </div>

                        
                    </div>
                </div>

            </div>
        </div>

    </div>

    <script>
        let selectedStudents = [];
        let bulkFeePayload = [];
        let bulkFeeHasErrors = false;
        let feeLoadedCount = 0;
        let totalFeeBoxes = 0;

        function updateBulkFeeButton() {
            const bulkInput = document.getElementById('bulk_fee_payload');
            const bulkBtn = document.getElementById('createFeeForAllBtn');

            if (bulkInput) {
                bulkInput.value = JSON.stringify(bulkFeePayload);
            }

            if (!bulkBtn) return;

            if (feeLoadedCount < totalFeeBoxes) {
                bulkBtn.innerText = 'Loading Fee Status...';
                bulkBtn.classList.remove('btn-primary');
                bulkBtn.classList.add('btn-danger');
                return;
            }

            if (bulkFeeHasErrors || bulkFeePayload.length === 0) {
                bulkBtn.innerText = 'Please resolve all errors';
                bulkBtn.classList.remove('btn-primary');
                bulkBtn.classList.add('btn-danger');
            } else {
                bulkBtn.innerText = 'Create Fee For All';
                bulkBtn.classList.remove('btn-danger');
                bulkBtn.classList.add('btn-primary');
            }
        }

        function updateFeeCounter() {
            const counter = document.getElementById('feeLoadCounter');

            if (counter) {
                counter.innerHTML = 'Fee Status Loading: ' + feeLoadedCount + ' / ' + totalFeeBoxes;
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const feeBoxes = document.querySelectorAll('.next-fee-status');
            totalFeeBoxes = feeBoxes.length;

            let feeIndex = 0;
            let concurrent = 8;

            updateFeeCounter();
            updateBulkFeeButton();

            function loadOneFeeStatus() {
                if (feeIndex >= feeBoxes.length) {
                    updateBulkFeeButton();
                    return;
                }

                let box = feeBoxes[feeIndex++];
                let studentId = box.getAttribute('data-student-id');

                $.ajax({
                    url: '<?php echo site_url("councils/load_student_fee_status"); ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        student_id: studentId,
                        course_id: '<?php echo $course_id; ?>',
                        exam_no: '<?php echo $exam_no; ?>',
                        class: '<?php echo $class; ?>',
                        sequence_id: '<?php echo $sequence['council_sequence_id']; ?>',
                        exam_sequence: '<?php echo $current_exam_sequence['id']; ?>'
                    },
                    success: function (res) {
                        if (res && typeof res.html !== 'undefined') {
                            box.innerHTML = res.html;
                        } else {
                            box.innerHTML = '<span class="label label-danger">Invalid Response</span>';
                            bulkFeeHasErrors = true;
                        }

                        if (res && parseInt(res.has_error) === 1) {
                            bulkFeeHasErrors = true;
                        }

                        if (res && res.fee_items && res.fee_items.length > 0 && parseInt(res.has_error) === 0) {
                            bulkFeePayload.push({
                                student_id: studentId,
                                fees: res.fee_items
                            });
                        }
                    },
                    error: function () {
                        box.innerHTML = '<span class="label label-danger">Load Error</span>';
                        bulkFeeHasErrors = true;
                    },
                    complete: function () {
                        feeLoadedCount++;
                        updateFeeCounter();
                        updateBulkFeeButton();
                        loadOneFeeStatus();
                    }
                });
            }

            for (let i = 0; i < concurrent; i++) {
                loadOneFeeStatus();
            }

            const bulkBtn = document.getElementById('createFeeForAllBtn');
            const bulkForm = document.getElementById('createFeeForAllForm');

            if (bulkBtn) {
                bulkBtn.addEventListener('click', function () {
                    updateBulkFeeButton();

                    if (feeLoadedCount < totalFeeBoxes) {
                        alert('Please wait. Fee status is still loading.');
                        return;
                    }

                    if (bulkFeeHasErrors || bulkFeePayload.length === 0) {
                        alert('Please resolve all errors first.');
                        return;
                    }

                    bulkForm.submit();
                });
            }
        });

        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('add-comment-btn')) {
                const form = e.target.nextElementSibling;

                if (!form) return;

                form.style.display = form.style.display === 'block' ? 'none' : 'block';
            }
        });

        document.addEventListener('change', function (e) {
            if (!e.target.classList.contains('type-select')) return;

            let form = e.target.closest('form');

            if (!form) return;

            let direct = form.querySelector('.result-direct');
            let marks = form.querySelector('.result-marks');

            if (!direct || !marks) return;

            if (e.target.value === 'direct') {
                direct.style.display = 'block';
                marks.style.display = 'none';
            } else if (e.target.value === 'marks') {
                direct.style.display = 'none';
                marks.style.display = 'block';
            } else {
                direct.style.display = 'none';
                marks.style.display = 'none';
            }
        });
    </script>
</div>