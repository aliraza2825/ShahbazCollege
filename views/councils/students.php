<?php
	$myAccess = checkUserAccess();
	$sequence = $this->db->get_where('council_sequence','council_sequence_id = '.$this->uri->segment(8))->row_array();
	$exam_sequence = $this->db->get_where('exam_sequence',array('course_id' => $sequence['course_id'],'first_year' => $this->uri->segment(5),'class' => $this->uri->segment(6),'status' => 'Active'))->row_array();
    $rules = $this->db->get_where('council_sequence_fee_rules',['sequence_fee_id' => $this->uri->segment(8),'exam_sequence_id' => $exam_sequence['id']])->result_array();
?>	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
            <?php if(@$this->session->userdata('message')):?>
                <div class="alert alert-success">
                    <button class="close" data-close="alert"></button>
                    <span>
                    <?php echo $this->session->userdata('message');?> </span>
                </div>
            <?php endif;?>
			<?php if(@$this->session->userdata('error')):?>
                <div class="alert alert-danger">
                    <button class="close" data-close="alert"></button>
                    <span>
                    <?php echo $this->session->userdata('error');?> </span>
                </div>
            <?php endif;
            
            
            if(!empty($rules))
            {
                echo '<div class="nested-table-wrapper">';
                echo '<table class="table table-condensed table-bordered inner-table" style="margin-bottom:0;">';
                echo '<thead>
                        <tr style="background:#eee;">
                            <th>From</th>
                            <th>To</th>
                            <th>Exam Fee</th>
                            <th>Expense</th>
                        </tr>
                      </thead>';
                echo '<tbody>';
        
                foreach($rules as $rule)
                {
                    echo '<tr>';
                    echo '<td>'.$rule['from_date'].'</td>';
                    echo '<td>'.$rule['to_date'].'</td>';
                    echo '<td>Rs '.$rule['exam_fee'].'</td>';
                    echo '<td>Rs '.$rule['expense_fee'].'</td>';
                
                    echo '</tr>';
                }
        
                echo '</tbody>
            </table>';
            echo '</div>';
            }
            else
            {
                echo '<div style="color:#999;">No fee rules added</div>';
            }

            
            ?>
			
			
            <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i>All Students ( <?php echo $course['course_name'] ?> )
							</div>
						</div>
						<div class="portlet-body">
						    <input type="hidden" name="selected_student_ids[]" value="">
							<table class="table table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
                                <th class="hidden">
									 Hidden
								</th>
								<th>
                                    Sr.
                                </th>
                                <th>
                                    Student ID
                                </th>
                                <th>
                                	Roll No.
                                </th>
                                <th>
                                	Name
                                </th>
								<th>
									 Father Name
								</th>
								<th>
									 CNIC
								</th>
								<th>
									 Class
								</th>
								<th>
									 Status
								</th>
                                <th>
									 Session
								</th>
								<?if ($this->uri->segment(7) && $this->uri->segment(7)=='paid'):?>
								<th>
								    Expense
								</th>
								<?php endif; ?>
								<th>
								    Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=1;
								foreach($students as $student):
								    $payment_alert = '';
								    if($student['status'] != 1){
								        $payment_alert='alert alert-danger';
								    }
								    
								    if($student['paid'] != 1 && $payment_alert != 'alert alert-danger'){
								        $payment_alert='alert alert-danger';
								    }
							?>
                            <tr class="odd gradeX <?php echo $payment_alert;?>">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
                                <td>
                                	<?php echo $i;?>
                                </td>
								<td>
                                    <?php echo $student['campus_name'];?>
                                </td>
								<td>
                                    <?php echo $student['roll_no'];?>
                                </td>
                                <td>
                                	<?php echo $student['first_name'].' '.$student['last_name'];?>
                                </td>
								<td>
                                    <?php echo $student['father_name'];?>
								</td>
								<td>
                                    <?php echo $student['cnic'];?>
								</td>
                                <td>
									<?php echo $student['class_name'];?>
								</td>
								<td>
    								Student Status : <?php echo $student['status'] == 1 ? '<label style="color:green">Active</label>' : '<label style="color:red">InActive</label>';?><br>
    								Fee Status : <?php echo $student['paid'] == 1 ? '<label style="color:green">Paid</label>' : '<label style="color:red">UnPaid</label>';?>
    							</td>
                                <td>
                                	<?php echo $student['session'];?>
                                </td>
                                <?if ($this->uri->segment(7) && $this->uri->segment(7)=='paid'):
                                    $expense = $this->db->get_where('expenses',array('student_id'=>$student['student_id'],'council_exam_no' => $this->uri->segment(5),'council_sequence_id' => $this->uri->segment(8),'class' => $this->uri->segment(6)))->result_array();
                                ?>
								<td>
                                    <?php if (!empty($expense)): ?>
                                        <!-- Expense exists → checked & disabled -->
                                        <input 
                                            type="checkbox" 
                                            checked 
                                            disabled
                                        >
                                    <?php 
                                        echo '<br>Comment : '.$expense[0]['purpose'].'<br>Expense Date : '.$expense[0]['date'].'<br>Amount : '.$expense[0]['amount'];
                                    
                                    else: ?>
                                        <!-- No expense → user can check -->
                                        <input 
                                            type="checkbox"
                                            class="student-checkbox"
                                            name="student_ids[]" 
                                            value="<?php echo $student['student_id']; ?>"
                                            data-student-id="<?php echo $student['student_id']; ?>"
                                        >
                                    <?php endif; ?>
                                </td>
								<?php endif; ?>
                                <td>
                                    <?php
                                    if(@$myAccess[0]['student_payments']==1 || $this->session->userdata('role')=='Admin'):
                                        ?>
                                        
                                        <a title="Payments" href="<?php echo site_url().'/students/payments/'.$student['student_id'];?>" target="_blank" class="btn purple"><i class="fa fa-money"></i></a>
                                    <?php
                                    endif;
                                    ?>
                                </td>
							</tr>
                            <?php
									$i++;
                            	endforeach;
							?>
							</tbody>
							</table>
							<?if ($this->uri->segment(7) && $this->uri->segment(7)=='paid'):?>
							    <div class="row" style="margin-top:15px;">
                                    <div class="col-md-12 text-right">
                                        <label style="margin-right:10px;">
                                            Total Amount : <span class="required">*</span>
                                        </label>
                                        <input type="number" class="form-control input-inline input-medium amount" name="amount" placeholder="Enter expense amount" id="amount" value="" required readonly>
                                        <br>
                                        <label style="margin-right:10px;">Date <span class="required">*</span></label>

                                        <div class="input-group input-inline input-medium date date-picker"
                                             style="margin-right:10px;"
                                             data-date="<?php echo date('Y-m-d')?>"
                                             data-date-format="yyyy-mm-dd"
                                             data-date-end-date="+0d"
                                             data-date-viewmode="years">
                                        
                                            <input type="text"
                                                   name="expense_date"
                                                   class="form-control"
                                                   value="<?php echo date('Y-m-d');?>"
                                                   readonly>
                                        
                                            <span class="input-group-btn">
                                                <button class="btn default" type="button">
                                                    <i class="fa fa-calendar"></i>
                                                </button>
                                            </span>
                                        </div>
                                        <br>
                                        <br>
                                        <label  style="margin-right:10px;">Expense Image </label>
                                            <div class="form-control input-inline">
                                                <input type="file" name="image"  value="" />
                                                <span class="help-inline"></span>
                                            </div>
                                        <br>
                                        <br>
                                        <label style="margin-right:10px;">
                                            Select Payment Type <span class="required">*</span>
                                        </label>
                                
                                        <select class="form-control"
                                                name="payment_type"
                                                id="payment_type"
                                                style="width:250px; display:inline-block; margin-right:10px;"
                                                required>
                                            <option value="cash">Cash</option>
                                            <?php foreach($council_ids as $council_id): ?>
                                                <option value="<?php echo $council_id['id']; ?>">
                                                    <?php echo $council_id['description']." ( ".$council_id['debit']." - ".$council_id['tagged_amount']." )"; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <br>
                                        <br>
                                        <?php if (@$myAccess[0]['council_report_add_information_can_add_expense']==1 || $this->session->userdata('role')=='Admin'): ?>
                                        <button type="button"
                                                class="btn btn-success"
                                                id="submitExpenseBtn">
                                            Add Expense For Selected Students
                                        </button>
                                <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
						</div>
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
		<script>
    let selectedStudents = [];
    const feeRules = <?php echo json_encode($rules); ?>;
    console.log('Selected fee:', feeRules.length);
    let ratePerStudent = 0;

    function getExpenseByDate(date) {
        let expense = 0;

        feeRules.forEach(function(rule) {
            if (date >= rule.from_date && date <= rule.to_date) {
                expense = parseFloat(rule.expense_fee) || 0;
            }
        });

        return expense;
    }

    function updateAmount() {
        const dateInput = document.querySelector('input[name="expense_date"]');
        const amountInput = document.getElementById('amount');

        if (!dateInput || !amountInput) return;

        const selectedDate = dateInput.value;
        ratePerStudent = getExpenseByDate(selectedDate);
        const totalAmount = selectedStudents.length * ratePerStudent;

        amountInput.value = totalAmount;
        console.log('Date:', selectedDate);
        console.log('Rate:', ratePerStudent);
        console.log('Selected Students:', selectedStudents.length);
        console.log('Total Amount:', totalAmount);
    }
    
    document.addEventListener( "DOMContentLoaded", function(){

        /* ================= CHECKBOX HANDLER ================= */
        document.addEventListener('change', function (e) {
            if (!e.target.classList.contains('student-checkbox')) return;
    
            const studentId = e.target.dataset.studentId;
    
            if (e.target.checked) {
                if (!selectedStudents.includes(studentId)) {
                    selectedStudents.push(studentId);
                }
            } else {
                selectedStudents = selectedStudents.filter(function(id) {
                    return id !== studentId;
                });
            }
    
            updateAmount();
        });
    
        /* ================= DATE INPUT CHANGE HANDLER ================= */
        document.addEventListener('change', function(e) {
            if (e.target.name === 'expense_date') {
                updateAmount();
            }
        });
    
        /* ================= DATEPICKER CHANGE EVENT ================= */
        $(document).ready(function() {
            $('.date-picker').datepicker({
                format: 'yyyy-mm-dd',
                endDate: '+0d',
                autoclose: true
            }).on('changeDate', function() {
                updateAmount();
            });
    
            updateAmount();
        });
    
        /* ================= BUTTON CLICK HANDLER ================= */
        document.addEventListener('click', function (e) {
            if (!e.target.closest('#submitExpenseBtn')) return;
            
            const amountInput = document.getElementById('amount');
    
            if (selectedStudents.length === 0 || amountInput.value == 0) {
                alert('Please select at least one student or Your Amount is 0.');
                return;
            }
    
            if (!myFunction()) return;
    
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "<?php echo site_url('councils/add_expense'); ?>";
            form.enctype = "multipart/form-data";
    
            selectedStudents.forEach(function(id) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'student_ids[]';
                input.value = id;
                form.appendChild(input);
            });
    
            const fields = {
                council_exam_no: "<?php echo $this->uri->segment(5); ?>",
                council_sequence_id: "<?php echo $this->uri->segment(8); ?>",
                payment_type: document.getElementById("payment_type").value,
                expense_date: document.querySelector('input[name="expense_date"]').value,
                examclass: "<?php echo $this->uri->segment(6); ?>",
                expense_amount: ratePerStudent
            };
    
            for (const key in fields) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = fields[key];
                form.appendChild(input);
            }
    
            const fileInput = document.querySelector('input[name="image"]');
            if (fileInput && fileInput.files.length > 0) {
                form.appendChild(fileInput);
            }
    
            document.body.appendChild(form);
            form.submit();
        });
        
        $(document).ready(function () {

    var isPaid = <?php echo ($this->uri->segment(7) == 'paid') ? 'true' : 'false'; ?>;

    if ($.fn.DataTable.isDataTable('#sample_2')) {
        $('#sample_2').DataTable().destroy();
    }

    $('#sample_2').DataTable({
        paging: !isPaid,
        info: !isPaid,
        searching: true,
        ordering: true
    });

});
        
    });

    /* ================= VALIDATION ================= */
    function myFunction() {
        const payment_type = document.getElementById("payment_type").value;

        if (payment_type === 'cash') {
            let tot = <?php echo $pettycash ?>;
            let x = parseFloat(document.getElementById("amount").value) || 0;

            if (x > tot) {
                alert('Your Petty cash is low you cannot add this expense');
                return false;
            }
        }

        return true;
    }
</script>
	</div>
	<!-- END CONTENT -->