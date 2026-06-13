
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Edit Profile <small>You can edit your profile here</small>
			</h3>-->
			
			<!-- END PAGE HEADER-->
			<!-- BEGIN PAGE CONTENT-->
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
			<?php
                if($this->session->userdata('role')=='Admin'):
			?>
			        <div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> Punjab Council Roll Numbers
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/punjab_council_roll_number/upload_roll_no" enctype="multipart/form-data">
								<div class="form-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Council Exam # <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="number" class="form-control" name="council_exam_no" value="" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Class <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <label class="radio-inline">
                                                    <input type="radio" name="class" id="optionsRadios4" value="1" checked> 1st year </label>
                                                    <label class="radio-inline">
                                                    <input type="radio" name="class" id="optionsRadios5" value="2"> 2nd Year </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Upload Csv File <span class="required">*</span></label>
                                                <div class="col-md-9">
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
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
                    <div class="row">
                        <div class="col-md-12 ">
                            <!-- BEGIN SAMPLE FORM PORTLET-->
                            <div class="portlet box green ">
                                <div class="portlet-title">
                                    <div class="caption">
                                        <i class="fa fa-list"></i> Punjab Council Roll Numbers
                                    </div>
                                </div>
                                <div class="portlet-body form">
                                    <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/punjab_council_roll_number/upload_roll_no_images" enctype="multipart/form-data">
                                        <div class="form-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="col-md-3 control-label">Council Exam # <span class="required">*</span></label>
                                                        <div class="col-md-9">
                                                            <input type="number" class="form-control" name="council_exam_no" value="" required />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="col-md-3 control-label">Class <span class="required">*</span></label>
                                                        <div class="col-md-9">
                                                            <label class="radio-inline">
                                                                <input type="radio" name="class" id="optionsRadios4" value="1" checked> 1st year </label>
                                                            <label class="radio-inline">
                                                                <input type="radio" name="class" id="optionsRadios5" value="2"> 2nd Year </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="col-md-3 control-label">Upload Images <span class="required">*</span></label>
                                                        <div class="col-md-9">
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
                            </div>
                            <!-- END SAMPLE FORM PORTLET-->
                        </div>
                    </div>
            <?php
				endif;
			?>
			
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
            
            <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> Students Punjab council roll number
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
									 Campus
								</th>
                                <th>
									 Class
								</th>
                                <th>
									 Council Exam No.
								</th>
                                <th>
									 Entry
								</th>
                                <th>
									 Roll No
								</th>
                                <th>
									 Computer #
								</th>
                                <th>
									 CNIC
								</th>
                                <th>
									 Name S/O Father Name
								</th>
                                <th>
									 Contractor
								</th>
                                <th>
									 Phone
								</th>
                                <th>
                                	Address
                                </th>
                                <th>
                                	Remarks
                                </th>
                                <th>
                                	Action
                                </th>
							</tr>
							</thead>
							<tbody>
							<?php
								if($this->session->userdata('role')=='Admin'):
							?>
							<?php
								$i=0;
								foreach(@$roll_numbers as $roll_number):
							?>
                            <tr class="<?php if(@$roll_number['council_mistake']==1){echo 'alert alert-danger';}?>">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
                                <td>
									<?php echo $roll_number['campus_name']?>
								</td>
                                <td>
									<?php echo $roll_number['class_name']?>
								</td>
                                <td>
									<?php echo $roll_number['council_exam_no']?>
								</td>
                                <td>
									<?php echo $roll_number['class'].' Year';?>
								</td>
                                <td>
									<?php echo $roll_number['roll_no']?>
								</td>
								<td>
									<?php echo $roll_number['computer_no'];?>
								</td>
                                <td>
									<a href="#" class="table_cnic table_cnic_<?php echo $roll_number['id']?>" data-id="<?php echo $roll_number['id']?>">
										<?php 
											if($roll_number['cnic']=='')
											{
												echo '00000-0000000-0';
											}
											else
											{
												echo $roll_number['cnic'];
											}
										?>
                                    </a>
                                    <label class="council_mistake council_mistake_<?php echo $roll_number['id']?> hidden"><input type="checkbox" value="1" name="council_mistake" class="council_mistake_field_<?php echo $roll_number['id']?>" <?php if(@$roll_number['council_mistake']==1){echo 'checked';}?> /> Council Mistake</label>
                                    <input type="text" class="form-control cnic_field cnic_<?php echo $roll_number['id']?> hidden" value="<?php echo $roll_number['cnic']?>" data-roll-no-id="<?php echo $roll_number['id']?>" />
								</td>
                                <td>
									<?php echo $roll_number['name']?>
								</td>
                                <td>
                                    <?php
                                    	if($roll_number['contractor_name']=='')
										{
											echo 'N/A';
										}
										else
										{
											echo $roll_number['contractor_name'];
										}
									?>
								</td>
                                <td>
                                	<?php echo $roll_number['mobile'].'<br />'.$roll_number['emergency_no'];?>
                                </td>
                                <td>
                                	<?php echo $roll_number['address']?>
                                </td>
                                <td>
                                	<?php echo $roll_number['remarks']?>
                                </td>
                                <td>
                                	<?php
                                    	if($this->session->userdata('role')=='Admin'):
									?>
									<a onclick="return confirm('Are you sure you want to delete this Roll Number?')" class="btn red" href="<?php echo site_url();?>/punjab_council_roll_number/delete_roll_no/<?php echo $roll_number['id']?>"><i class="fa fa-trash"></i></a>
									<?php
										endif;
									?>
                                </td>
							</tr>
                            <?php
								$i++;
                            	endforeach;
							?>
							<?php
								endif;
							?>
							<?php
								if($this->session->userdata('role')!='Admin'):
							?>
							<?php
								$access = checkUserAccess();
								$campus_ids = @explode(',',$access[0]['campus_ids']);
								$i=0;
								foreach(@$roll_numbers as $roll_number):
								if(in_array($roll_number['campus_id'],$campus_ids) || $roll_number['campus_id']==NULL):
							?>
                            <tr class="<?php if(@$roll_number['council_mistake']==1){echo 'alert alert-danger';}?>">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
                                <td>
									<?php echo $roll_number['campus_name']?>
								</td>
                                <td>
									<?php echo $roll_number['class_name']?>
								</td>
                                <td>
									<?php echo $roll_number['council_exam_no']?>
								</td>
                                <td>
									<?php echo $roll_number['class'].' Year';?>
								</td>
                                <td>
									<?php echo $roll_number['roll_no']?>
								</td>
								<td>
									<?php echo $roll_number['computer_no'];?>
								</td>
                                <td>
									<a href="#" class="table_cnic table_cnic_<?php echo $roll_number['id']?>" data-id="<?php echo $roll_number['id']?>">
										<?php 
											if($roll_number['cnic']=='')
											{
												echo '00000-0000000-0';
											}
											else
											{
												echo $roll_number['cnic'];
											}
										?>
                                    </a>
                                    <label class="council_mistake council_mistake_<?php echo $roll_number['id']?> hidden"><input type="checkbox" value="1" name="council_mistake" class="council_mistake_field_<?php echo $roll_number['id']?>" <?php if(@$roll_number['council_mistake']==1){echo 'checked';}?> /> Council Mistake</label>
                                    <input type="text" class="form-control cnic_field cnic_<?php echo $roll_number['id']?> hidden" value="<?php echo $roll_number['cnic']?>" data-roll-no-id="<?php echo $roll_number['id']?>" />
								</td>
                                <td>
									<?php echo $roll_number['name']?>
								</td>
                                <td>
                                    <?php
                                    	if($roll_number['contractor_name']=='')
										{
											echo 'N/A';
										}
										else
										{
											echo $roll_number['contractor_name'];
										}
									?>
								</td>
                                <td>
                                	<?php echo $roll_number['mobile'].'<br />'.$roll_number['emergency_no'];?>
                                </td>
                                <td>
                                	<?php echo $roll_number['address']?>
                                </td>
                                <td>
                                	<?php echo $roll_number['remarks']?>
                                </td>
                                <td>
                                	<?php
                                    	if($this->session->userdata('role')=='Admin'):
									?>
									<a onclick="return confirm('Are you sure you want to delete this Roll Number?')" class="btn red" href="<?php echo site_url();?>/punjab_council_roll_number/delete_roll_no/<?php echo $roll_number['id']?>"><i class="fa fa-trash"></i></a>
									<?php
										endif;
									?>
                                </td>
							</tr>
                            <?php
								$i++;
								endif;
                            	endforeach;
							?>
							<?php
								endif;
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
	</div>
	<!-- END CONTENT -->