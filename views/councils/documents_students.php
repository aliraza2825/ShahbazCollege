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
            
            <div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> Council Roll Numbers
							</div>
						</div>
						<div class="portlet-body form">
						    <div class="col-md-6">
                                <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/punjab_council_roll_number/upload_roll_no" enctype="multipart/form-data">
    								<div class="form-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="col-md-3 control-label">Upload Csv File <span class="required">*</span></label>
                                                    <div class="col-md-9">
                                                        <input type="hidden" name="class" id="optionsRadios5" value="<?php echo $this->uri->segment(6); ?>"/>
                                                        <input type="hidden" class="form-control" name="council_exam_no" value="<?php echo $this->uri->segment(5); ?>" required />
                                                        <input type="hidden" class="form-control" name="course_id" value="<?php echo $this->uri->segment(3); ?>" required />
                                                        <input type="file" class="form-control" name="roll_no" value="" required />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
    								</div>
    								<div class="form-actions">
    									<div class="row">
    										<div class="col-md-offset-3 col-md-9">
    											<button type="submit" class="btn green">Upload CSV File</button>
    											<button onclick="location.href = '<?php echo site_url();?>'" type="button" class="btn default">Cancel</button>
    										</div>
    									</div>
    								</div>
    							</form>
							</div>
							<div class="col-md-6">
							    <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/punjab_council_roll_number/upload_roll_no_images" enctype="multipart/form-data">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="col-md-3 control-label">Upload Images <span class="required">*</span></label>
                                                    <div class="col-md-9">
                                                        <input type="hidden" name="class" id="optionsRadios5" value="<?php echo $this->uri->segment(6); ?>"/>
                                                        <input type="hidden" class="form-control" name="council_exam_no" value="<?php echo $this->uri->segment(5); ?>" required />
                                                        <input type="hidden" class="form-control" name="course_id" value="<?php echo $this->uri->segment(3); ?>" required />
                                                        <input type='file' name='files[]' multiple >
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-actions">
                                        <div class="row">
                                            <div class="col-md-offset-3 col-md-9">
                                                <button type="submit" class="btn green">Upload</button>
                                                <button onclick="location.href = '<?php echo site_url();?>'" type="button" class="btn default">Cancel</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="row">
                				<div class="col-md-12">
                					<div class="portlet box grey-cascade">
                					<div class="portlet-body">
                						<h2 class="text-center">Sample Format of Roll Numbers in Excel Sheet</h2>
                						<table class="table table-bordered">
                							<thead>
                								<tr>
                									<th class="text-center">A</th>
                									<th class="text-center">B</th>
                									<th class="text-center">C</th>
                									<th class="text-center">D</th>
                									<th class="text-center">E</th>
                									<th class="text-center">F</th>
                									<th class="text-center">G</th>
                									<th class="text-center">H</th>
                									<th class="text-center">I</th>
                									<th class="text-center">J</th>
                								</tr>
                							</thead>
                							<tbody>
                								<tr>
                									<td class="text-center">Roll No</td>
                									<td class="text-center">Previous</td>
                									<td class="text-center">Previous</td>
                									<td class="text-center">Previous</td>
                									<td class="text-center">Computer No.</td>
                									<td class="text-center">NIC Number</td>
                									<td class="text-center">Name &amp; Father's Name</td>
                									<td class="text-center">Address</td>
                									<td class="text-center"></td>
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
					<!-- END SAMPLE FORM PORTLET-->
				</div>
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
    								foreach($students as $student): ?>
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
                                        if ($sequence['action_type']=='add_roll_no'):?>
                                            <td>
                                                <?php
                                                if (!empty($roll_number_and_result['roll_no'])) {
                                                    $info = $roll_number_and_result;
                                                ?>
                                                <div style="font-size:13px; margin-bottom:8px;">
                                                    <label><?php echo $info['roll_no'] ?></label>
                                                    <div style="margin-bottom:5px; border-bottom:1px dashed #ccc; padding-bottom:5px;">
                                                        <?php if (!empty($info['slip_image'])): ?>
                                                            <a href="<?php echo base_url('rollno_slips/'.$info['slip_image']); ?>" target="_blank">
                                                                <img src="<?php echo base_url('rollno_slips/'.$info['slip_image']); ?>" style="max-width:60px; cursor:pointer;">
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <?php
                                                }
                                                ?>
                                                <button type="button" class="btn btn-xs btn-success add-comment-btn">Update Roll No</button>
                                                
                                                <form method="post"
                                                      action="<?php echo site_url('councils/save_roll_no'); ?>"
                                                      enctype="multipart/form-data"
                                                      class="add-comment-form"
                                                      style="display:none; margin-top:8px;">
                                                
                                                    <input type="hidden" name="council_sequence_id" value="<?php echo $sequence['council_sequence_id']; ?>">
                                                    <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                                                    <input type="hidden" name="exam_no" value="<?php echo $this->uri->segment(5); ?>">
                                                    <input type="hidden" name="class" value="<?php echo $this->uri->segment(6); ?>">
                                                    <input type="hidden" name="save_type" value="roll_no">
                                                    <input type="hidden" name="course_id" value="<?php echo $this->uri->segment(3); ?>">
                                                    <input type="file" name="image" class="form-control" style="margin-bottom:5px;">
                                                    <input type="text" name="roll_no" class="form-control" placeholder="Enter Roll No" style="margin-bottom:5px;">
                                                    <button type="submit" class="btn btn-xs btn-primary">Save</button>
                                                </form>
                                            </td>
    								    <?php 
    								    endif; ?>
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