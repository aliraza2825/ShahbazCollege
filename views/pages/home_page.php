	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
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
								<i class="fa fa-plus"></i> Add Website Content
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/pages/insert" enctype="multipart/form-data">
								<div class="form-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                        	<div class="form-group">
                                                <label class="col-md-2 control-label">Campus Name <span class="required">*</span></label>
                                                <div class="col-md-10">
                                                    <select class="form-control" name="campus_id" required>
                                                        <option value="">Select Campus</option>
														<?php
                                                            foreach($campuses as $campus):
                                                        ?>
                                                        <option value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name'];?></option>
                                                        <?php
                                                            endforeach;
                                                        ?>
                                                    </select>
                                                    <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <h2>Home Page Text</h2>
                                            <hr />
                                        </div>
                                        <div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Point 1 <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-large" name="point_1" placeholder="Enter point 1" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Point 1 Explanation <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <textarea class="form-control input-inline input-large" name="point_1_explanation" required></textarea>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Point 2 <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-large" name="point_2" placeholder="Enter point 2" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Point 2 Explanation <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <textarea class="form-control input-inline input-large" name="point_2_explanation" required></textarea>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4"></div>
                                        <div class="col-md-4">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Image <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="file" name="point_center_image"  value="" required />
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4"></div>
                                        <div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Point 3 <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-large" name="point_3" placeholder="Enter point 3" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Point 3 Explanation <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <textarea class="form-control input-inline input-large" name="point_3_explanation" required></textarea>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Point 4 <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-large" name="point_4" placeholder="Enter point 4" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Point 4 Explanation <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <textarea class="form-control input-inline input-large" name="point_4_explanation" required></textarea>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Image Left<span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="file" name="home_left_image"  value="" required />
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                        	<div class="form-group">
                                                <label class="col-md-2 control-label">Heading <span class="required">*</span></label>
                                                <div class="col-md-10">
                                                    <input type="text" class="form-control input-inline input-large" name="home_right_heading" placeholder="Heading" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-2 control-label">Paragraph 1 <span class="required">*</span></label>
                                                <div class="col-md-10">
                                                    <textarea class="form-control" name="home_right_paragraph" required></textarea>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label class="col-md-2 control-label">Paragraph 2 <span class="required">*</span></label>
                                                <div class="col-md-10">
                                                    <textarea class="form-control" name="home_left_paragraph" required></textarea>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Image Right<span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="file" name="home_right_image"  value="" required />
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <h2></h2>
                                            <hr />
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Add Content</button>
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
								<i class="fa fa-list"></i>Content for Website
							</div>
						</div>
						<div class="portlet-body">
							<table class="table table-striped table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
                                <th class="hidden">
									 Hidden
								</th>
                                <th>
									 Campus Name
								</th>
                                <th>
									 Point 1
								</th>
                                <th>
									 Point 1 Explanation
								</th>
                                <th>
									 Point 2
								</th>
                                <th>
									 Point 2 Explanation
								</th>
                                <th>
									 Center Image
								</th>
                                <th>
									 Point 3
								</th>
                                <th>
									 Point 3 Explanation
								</th>
                                <th>
									 Point 4
								</th>
                                <th>
									 Point 4 Explanation
								</th>
                                <th>
									 Bottom Left Image
								</th>
                                <th>
									 Bottom Right Heading
								</th>
                                <th>
									 Bottom Right Paragraph
								</th>
                                <th>
									 Bottom Left Paragraph
								</th>
                                <th>
									 Bottom Right Image
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=1;
								foreach($contents as $content):
							?>
                            <tr class="odd gradeX">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $this->db->get_where('campuses', array('campus_id'=>$content['campus_id']))->row()->campus_name;?>
								</td>
                                <td>
									<?php echo $content['point_1'];?>
								</td>
                                <td>
                                	<?php echo $content['point_1_explanation'];?>
                                </td>
                                <td>
                                	<?php echo $content['point_2'];?>
                                </td>
                                <td>
                                	<?php echo $content['point_2_explanation'];?>
                                </td>
                                <td>
                                	<img src="<?php echo base_url().'uploads/'.$content['point_center_image'];?>" width="100"  />
                                </td>
                                <td>
									<?php echo $content['point_3'];?>
								</td>
                                <td>
                                	<?php echo $content['point_3_explanation'];?>
                                </td>
                                <td>
                                	<?php echo $content['point_4'];?>
                                </td>
                                <td>
                                	<?php echo $content['point_4_explanation'];?>
                                </td>
                                <td>
									<img src="<?php echo base_url().'uploads/'.$content['home_left_image'];?>" width="100"  />
								</td>
                                <td>
                                	<?php echo $content['home_right_heading'];?>
                                </td>
                                <td>
                                	<?php echo $content['home_right_paragraph'];?>
                                </td>
                                <td>
                                	<?php echo $content['home_left_paragraph'];?>
                                </td>
                                <td>
                                	<img src="<?php echo base_url().'uploads/'.$content['home_right_image'];?>" width="100"  />
                                </td>
                                
								<td>
                                	<a title="Edit" href="<?php echo site_url().'/pages/edit_home_page/'.$content['website_content_id'];?>" class="btn blue"><i class="fa fa-edit"></i></a>
                                    <a title="Delete" onclick="return confirm('Are you sure you want to delete this Content?')" href="<?php echo site_url().'/pages/delete/'.$content['website_content_id'];?>" class="btn red"><i class="fa fa-trash"></i></a>
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