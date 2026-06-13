	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			All Classes <small>Here you can find all classes</small>
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
			<div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i>Teachers Report
							</div>
						</div>
                        <div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/teachers_paper_report">
								<div class="form-body">
                                	<div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Select Teacher <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select name="teacher_id" class="form-control input-inline input-medium">
                                                        <option value="">Select Teacher</option>
														<?php
                                                            foreach ($teachers as $teacher):
                                                        ?>
                                                        <option value="<?php echo $teacher['user_id']?>" <?php if($teacher['user_id']==$this->input->post('teacher_id')){echo 'selected=selected';}?>>
															<?php echo $teacher['first_name'].' '.$teacher['last_name'];?>
                                                        </option>
                                                        <?php
                                                            endforeach;
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Check Papers Report</button>
										</div>
									</div>
								</div>
                            </form>
                        </div>    
                        <?php
							if($this->input->post('teacher_id')):
						?>
						<div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover">
							<thead>
							<tr>
								<th class="hidden">
                                	 hidden
                                </th>
								<th>
									 Teacher Name
								</th>
                                <th>
									 Subject
								</th>
                                <th>
									 Papers
								</th>
                                <th>
                                	Date
                                </th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($teachers_data as $teacher_data):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
								<td>
									<?php echo $teacher_data['first_name'].' '.$teacher_data['last_name'];?>
								</td>
                                <td>
									<?php echo $teacher_data['name'];?>
								</td>
                                <td>
									<button onclick="location.href = '<?php echo base_url();?>uploads/<?php echo $teacher_data['content'];?>'" class="btn green">Download Paper</button>
								</td>
                                <td>
									<?php echo date('F d, Y', strtotime($teacher_data['date']));?>
								</td>
							</tr>
                            <?php
								$i++;
                            	endforeach;
							?>
							</tbody>
							</table>
						</div>
                        <?php
                        	endif;
						?>
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->