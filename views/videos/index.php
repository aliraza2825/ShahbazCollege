	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			All Teachers <small>Here you can find all teachers</small>
			</h3>-->
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
								<i class="fa fa-plus"></i> Add Videos
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/videos/insert" enctype="multipart/form-data">
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
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Title <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <div class="col-md-12">
                                                        <input type="text" class="form-control" name="title" value="" required />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Youtube Url <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <div class="col-md-12">
                                                        <input type="text" class="form-control" name="url" value="" required />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Show on Website Main Page </label>
                                                <div class="col-md-9 radio-list">
                                                    <label class="radio-inline">
                                                    <input type="radio" name="show_on_website" id="optionsRadios1" value="1" checked> Yes </label>
                                                    <label class="radio-inline">
                                                    <input type="radio" name="show_on_website" id="optionsRadios2" value="0"> No </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Show on Website Apply Now Page </label>
                                                <div class="col-md-9 radio-list">
                                                    <label class="radio-inline">
                                                    <input type="radio" name="for_apply_now" id="optionsRadios1" value="1" checked> Yes </label>
                                                    <label class="radio-inline">
                                                    <input type="radio" name="for_apply_now" id="optionsRadios2" value="0"> No </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Add</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>
            <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> All Videos
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
									 Campus Name
								</th>
                                <th>
									 Title
								</th>
                                <th>
									 Video URL
								</th>
                                <th>
									 Show on Website Main Page
								</th>
                                <th>
									 Show on Website Apply Now Page
								</th>
                                <th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=0;
								foreach($videos as $video):
							?>
                            <tr>
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
                                <td>
									<?php echo @$this->db->get_where('campuses', array('campus_id'=>$video['campus_id']))->row()->campus_name;?>
								</td>
                                <td>
									<?php echo $video['title']?>
								</td>
                                <td>
									<?php echo $video['url']?>
								</td>
								<td>
									<?php
                                    	if($video['show_on_website']==1)
										{
											echo 'Yes';
										}
										else
										{
											echo 'No';
										}
									?>
								</td>
                                <td>
									<?php
                                    	if($video['for_apply_now']==1)
										{
											echo 'Yes';
										}
										else
										{
											echo 'No';
										}
									?>
								</td>
                                <td>
									<a href="<?php echo site_url();?>/videos/delete/<?php echo $video['video_id'];?>" class="btn red"><i class="fa fa-trash"></i></a>
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