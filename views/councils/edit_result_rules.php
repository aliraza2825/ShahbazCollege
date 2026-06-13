
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE CONTENT-->
            <?php if(@$this->session->userdata('message')):?>
                <div class="alert alert-success">
                    <button class="close" data-close="alert"></button>
                    <span>
                    <?php echo $this->session->userdata('message');?> </span>
                </div>
            <?php endif;?>
			<div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-edit"></i> Edit Result Rules
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/councils/update_result_rules/<?php echo $this->uri->segment(3);?>">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Select Courses <span class="required">*</span></label>
                                        <div class="col-md-6">
                                            <select class="form-control input-inline input-large" name="course_id" required>
                                                <?php
                                                foreach($courses as $course):
                                                ?>
                                                    <option value="<?php echo $course['course_id'];?>" <?php if($council_result_rule[0]['course_id']==$course['course_id']){echo 'selected';}?>><?php echo $course['course_name'];?></option>
                                                <?php
                                                endforeach;
                                                ?>
                                            </select>
                                        </div>
                                    </div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Total Chances <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="number" class="form-control input-inline input-large" name="total_chances" placeholder="Enter total chances" value="<?php echo $council_result_rule[0]['total_chances'];?>" required>
											<span class="help-inline"></span>
                                        </div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Add Result Rule</button>
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
								<i class="fa fa-list"></i> All Result Rules
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
									 Course Name
								</th>
								<th>
									 Total Chances
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i = 0;
								foreach($rules as $rule):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $rule['council_result_rule_id']?>
								</td>
                                <td>
									 <?php echo $rule['course_name']?>
								</td>
								<td>
									 <?php echo $rule['total_chances']?>
								</td>
								<td>
									<a href="<?php echo site_url().'/councils/edit_council_result_rule/'.$rule['council_result_rule_id'];?>" title="Edit" class="btn blue"><i class="fa fa-edit"></i></a>
                                    <a onclick="return confirm('Are you sure you want to delete this Council Rule?')" href="<?php echo site_url().'/councils/delete_council_result_rule/'.$rule['council_result_rule_id'];?>" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>
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