
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
			<div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> Status (report)
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/punjab_council_roll_number/status_report">
								<div class="form-body">
                                    <div class="row">
										<div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Class <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <label class="radio-inline">
                                                    <input type="radio" name="class" id="optionsRadios4" value="1" /> 1st year </label>
                                                    <label class="radio-inline">
                                                    <input type="radio" name="class" id="optionsRadios5" value="2" /> 2nd Year </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Exam # <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="number" class="form-control input-inline input-medium" name="council_exam_no" placeholder="Enter Council Exam" value="" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                        	<input type="hidden" name="check" value="1" />
                                            <button type="submit" class="btn green">Check</button>
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
            	if(@$this->input->post('check')==1):
			?>
            <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> Complete Status
							</div>
						</div>
						<div class="portlet-body">
                            <table class="table table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
								<th class="hidden">
									 Sr.
								</th>
								<th>
									 Campus
								</th>
                                <th>
									 Exam Number
								</th>
                                <th>
									 Total Amount Send to Council
								</th>
                                <th>
									 Admissions Send to council
								</th>
                                <th>
									 Recognized Roll No. Receive from PPC
								</th>
								<th>
									 Roll No. not Recognized from PPC
								</th>
                                <th>
									 Inform to Students
								</th>
                                <th>
									 Extra Roll No.
								</th>
								<th>
									 Total ID Card Mistakes
								</th>
								<th>
									 Total Pass in 1st Year
								</th>
								<th>
									 Total Pass in 2nd Year
								</th>
							</tr>
							</thead>
							<tbody>
                            
							<?php
								$i=1;
								foreach($campuses as $campus):
							?>
                            <tr>
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
								<td>
                                	<?php echo $campus['campus_name'];?>
                                </td>
                                <td>
                                	<?php echo $council_exam_no;?>
                                </td>
								<td>
                                	<?php 
										$totalAmountSendToCouncil = totalAmountSendToCouncil($campus['campus_id'],$class,$council_exam_no);
										$a=1;
										$totalAmount=0;
										foreach($totalAmountSendToCouncil as $amount)
										{
											echo '<span class="pull-left">Amount : '.$amount['amount'].'<br />';
											echo 'Date : '.$amount['date'].'<br /></span>';
											echo '<a class="btn green pull-right" href="'.base_url().'uploads/'.$amount['image'].'"><i class="fa fa-image"></i></a>';
											echo '<div class="clearfix"></div>';
											echo '<hr />';
											//echo $a.'. '.$amount['amount'].' '.$amount['date'].'<a class="btn green pull-right" href="'.base_url().'uploads/'.$amount['image'].'"><i class="fa fa-image"></i></a><br /><hr /> ';
											$totalAmount+=$amount['amount'];
											$a++;
										}
										echo '<strong>Total Amount : '.$totalAmount.'</strong>';
									?>
                                </td>
								<td>
                                	<?php echo admissionsSendToCouncil($campus['campus_id'],$class,$council_exam_no);?>
                                </td>
								<td>
                                	<?php echo recognizedRollNoReceiveFromCouncil($campus['campus_id'],$class,$council_exam_no);?>
                                </td>
								<td>
                                	<?php echo notRecognizedRollNoReceiveFromCouncil($campus['campus_id'],$class,$council_exam_no);?>
                                </td>
								<td>
                                	
                                </td>
								<td>
                                	
                                </td>
								<td>
                                	
                                </td>
								<td>
                                	
                                </td>
								<td>
                                	
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
            <?php
            	endif;
			?>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->