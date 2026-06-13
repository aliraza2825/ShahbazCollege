
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
								<i class="fa fa-plus"></i> Add Paid Tax
							</div>

						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" id="myForm" action="<?php echo site_url();?>/tax/insert_tax_paid" enctype="multipart/form-data">
								<div class="form-body">
                                    <div class="form-group">
										<label class="col-md-3 control-label">Type <span class="required">*</span></label>
										<div class="col-md-9 radio-list">
											<label class="radio-inline">
											<input type="radio" name="type" id="optionsRadios4" value="Personal" checked> Personal </label>
											<label class="radio-inline">
											<input type="radio" name="type" id="optionsRadios5" value="Shahbaz Educational Institutions"> Shahbaz Educational Institutions </label>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Tax Year <span class="required">*</span></label>
										<div class="col-md-9">
                                            <select class="form-control input-large" name="tax_year" required>
                                                <?php
													for($i=2000;$i<date('Y');$i++):
												?>
                                                <option value="<?php echo $i;?>"><?php echo $i;?></option>
                                                <?php
                                                	endfor;
												?>
                                            </select>
										</div>
									</div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Upload Paid Tax Document <span class="required">*</span></label>
										<div class="col-md-9">
                                            <input type="file" class="form-control input-large" name="tax_document" required>
										</div>
									</div>
                                </div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                            <button type="submit" id="submitbtn" class="btn green">Submit</button>
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
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> All Paid Taxes
							</div>
						</div>
						<div class="portlet-body">
							<table class="table table-striped table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
								<th class="hidden">
                                	 hidden
                                </th>
                                <th>
									 ID
								</th>
                                <th>
									 Type
								</th>
								<th>
									 Tax Year
								</th>
                                <th>
									 Tax File
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($taxes as $tax):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $tax['tax_paid_id']?>
								</td>
								<td>
									<?php echo $tax['type']?>
								</td>
                                <td>
									<?php echo $tax['tax_year']?>
								</td>
                                <td>
									<a class="btn green" target="_blank" href="<?php echo $tax['tax_document']?>"><i class="fa fa-download"></i> Download Tax File</a>
								</td>
								<td>
                                    <?php
                                    	if($this->session->userdata('role')=='Admin'):
									?>
                                    <a onclick="return confirm('Are you sure you want to delete this Tax?')" href="<?php echo site_url();?>/tax/delete_tax/<?php echo $tax['tax_paid_id']?>" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>
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
	</div>
	<!-- END CONTENT -->