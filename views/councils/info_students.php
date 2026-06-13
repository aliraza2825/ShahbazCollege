<?php
	$myAccess = checkUserAccess();
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
            <?php endif;?>
			
			<div class="row" style="margin-bottom:20px;">
                <div class="col-md-6">
        
                <form method="post" action="<?php echo site_url('councils/add_sequence_information'); ?>" enctype="multipart/form-data">
        
                    <input type="hidden" name="council_sequence_id" value="<?php echo $sequence['council_sequence_id']; ?>">
        
                    <div class="form-group">
                        <label>General Comment</label>
                        <textarea name="general_comment" class="form-control" required></textarea>
                    </div>
        
                    <div class="form-group">
                        <label>Upload Image</label>
                        <input type="file" name="image" class="form-control">
                    </div>
        
                    <button type="submit" class="btn btn-success">
                        Add Information
                    </button>
        
                </form>
        
            </div>
                <?php
                    $reports = $this->db
                    ->where('council_sequenec_id',$sequence['council_sequence_id'])
                    ->order_by('id','desc')
                    ->get('council_sequence_inform_report')
                    ->result_array();
                    ?>
                    
                    <?php if(!empty($reports)): ?>
                    
                    <div class="row col-md-6">
                    <div class="col-md-12">
                    
                    <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>Comment</th>
                        <th>Image</th>
                        <th>Date</th>
                    </tr>
                    </thead>
                    
                    <tbody>
                    
                    <?php $i=1; foreach($reports as $r): ?>
                    
                    <tr>
                    
                    <td><?php echo $r['general_comment']; ?></td>
                    
                    <td>
                    
                    <?php if(!empty($r['image'])): ?>
                    
                    <a href="<?php echo base_url('uploads/'.$r['image']); ?>" target="_blank">
                    <img src="<?php echo base_url('uploads/'.$r['image']); ?>" style="max-width:70px;">
                    </a>
                    
                    <?php endif; ?>
                    
                    </td>
                    
                    <td><?php echo date("d M Y h:i A", strtotime($r['created_at'])); ?></td>
                    
                    </tr>
                    
                    <?php endforeach; ?>
                    
                    </tbody>
                    
                    </table>
                    
                    </div>
                    </div>
                    
                <?php endif; ?>
            </div>
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
							<table class="table table-bordered table-hover">
							<thead>
							<tr>
                                <th class="hidden">
									 Hidden
								</th>
								<th>
                                    Sr.
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
								<?php if ($sequence['action_type']!='fee'):?>
								<th>
								    <?php echo $sequence['type_name']; ?>
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
							?>
                            <tr class="odd gradeX">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
                                <td>
                                	<?php echo $i;?>
                                </td>
								<td>
                                    <?php echo $student['roll_no'].'<br>'.$student['campus_name'];?>
                                </td>
                                <td>
                                	<?php echo $student['first_name'].' '.$student['last_name'];?>
                                </td>
								<td>
                                    <?php echo $student['father_name'];?>
								</td>
								<td>
                                    <?php echo $student['cnic'].'<br><br>Mobile - '.$student['mobile'].'<br>Emergency No - '.$student['emergency_no'];?>
								</td>
                                <td>
									<?php echo $student['class_name'];?>
								</td>
								<td>
        									<?php echo $student['status'] == 1 ? 'Active' : 'Inactive';?>
        								</td>
                                
                                    <?php 
                                    $roll_number_and_result = $this->db->get_where('punjab_council_roll_number',array('cnic'=>$student['cnic'],'council_exam_no' => $this->uri->segment(5),'class' => $this->uri->segment(6),'course_id'=>$this->uri->segment(3)))->row_array();
                                    if ($sequence['action_type']=='information'):?>
                                    <td>

                                    <?php
                                    $extra_info = array();
                                    
                                    if (!empty($roll_number_and_result['extra_info'])) {
                                        $extra_info = json_decode($roll_number_and_result['extra_info'], true);
                                    }
                                    ?>
                                    
                                    <div style="font-size:13px; margin-bottom:8px;">
                                    <?php
                                    if (!empty($extra_info)) {
                                        foreach ($extra_info as $info) {
                                            if ($info['council_sequence_id'] == $sequence['council_sequence_id']) {
                                    ?>
                                        <div style="margin-bottom:5px; border-bottom:1px dashed #ccc; padding-bottom:5px;">
                                            Comment: <?php echo isset($info['comment']) ? $info['comment'] : '-'; ?> -
                                            Informed By: <?php echo isset($info['informed_by']) ? $info['informed_by'] : '-'; ?> -
                                            Informed At: <?php echo !empty($info['informed_at']) ? date("d M Y h:i A", strtotime($info['informed_at'])) : '-'; ?>
                                    
                                            <?php if (!empty($info['image_url'])): ?>
                                                - <a href="<?php echo base_url('uploads/'.$info['image_url']); ?>" target="_blank">
                                                    <img src="<?php echo base_url('uploads/'.$info['image_url']); ?>" style="max-width:60px; cursor:pointer;">
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php
                                            }
                                        }
                                    }
                                    ?>
                                    </div>
                                    
                                    <button type="button" class="btn btn-xs btn-success add-comment-btn">Add Comment</button>
                                    
                                    <form method="post"
                                          action="<?php echo site_url('councils/save_informed'); ?>"
                                          enctype="multipart/form-data"
                                          class="add-comment-form"
                                          style="display:none; margin-top:8px;">
                                    
                                        <input type="hidden" name="council_sequence_id" value="<?php echo $sequence['council_sequence_id']; ?>">
                                        <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                                        <input type="hidden" name="exam_no" value="<?php echo $this->uri->segment(5); ?>">
                                        <input type="hidden" name="class" value="<?php echo $this->uri->segment(6); ?>">
                                        <input type="hidden" name="course_id" value="<?php echo $this->uri->segment(3); ?>">
                                    
                                        <textarea name="comment" class="form-control" placeholder="Enter comment" style="margin-bottom:5px;"></textarea>
                                        <input type="file" name="image" class="form-control" style="margin-bottom:5px;">
                                        <label style="display:block; margin-bottom:5px;">
                                            <input type="hidden" name="informed" value="0">
                                            <input type="checkbox" name="informed" value="1">Fully Informed
                                        </label>
                                    
                                        <button type="submit" class="btn btn-xs btn-primary">Save</button>
                                    </form>
                                    
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
                                        <button type="button"
                                                class="btn btn-success"
                                                id="submitExpenseBtn">
                                            Add Expense For Selected Students
                                        </button>
                                
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
            
            document.addEventListener('click', function(e){

                if(e.target.classList.contains('add-comment-btn')){
                
                    const form = e.target.nextElementSibling;
                    
                    if(form.style.display === 'none'){
                        form.style.display = 'block';
                    }else{
                        form.style.display = 'none';
                    }
                
                }
            
            });
            
            /* ================= CHECKBOX HANDLER ================= */
            document.addEventListener('change', function (e) {
                if (!e.target.classList.contains('student-checkbox')) return;
            
                const studentId = e.target.dataset.studentId;
            
                if (e.target.checked) {
                    if (!selectedStudents.includes(studentId)) {
                        selectedStudents.push(studentId);
                    }
                } else {
                    selectedStudents = selectedStudents.filter(id => id !== studentId);
                }
            
                const ratePerStudent = <?php echo $sequence['expense_fee'] ? $sequence['expense_fee'] : 0 ?>;
                const totalAmount = selectedStudents.length * ratePerStudent;
            
                const amountInput = document.getElementById('amount');
                if (amountInput) {
                    amountInput.value = totalAmount;
                }
            
                console.log('Selected:', selectedStudents);
            });
            
            /* ================= BUTTON CLICK HANDLER ================= */
            document.addEventListener('click', function (e) {
                if (!e.target.matches('#submitExpenseBtn')) return;
            
                if (selectedStudents.length === 0) {
                    alert('Please select at least one student');
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
                    examclass: "<?php echo $this->uri->segment(6); ?>"
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
            
            
        </script>
	</div>
	<!-- END CONTENT -->