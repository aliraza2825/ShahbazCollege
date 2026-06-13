<?php
	$myAccess = checkUserAccess();
?>	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			All Teachers <small>Here you can find all teachers</small>
			</h3>-->
            <!-- BEGIN DASHBOARD STATS -->
			<!--<div class="row">
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat blue-madison">
						<div class="visual">
							<i class="fa fa-graduation-cap"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php //echo count($students);?>
							</div>
							<div class="desc">
								 Students
							</div>
						</div>
						<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
			</div>-->
			<!-- END DASHBOARD STATS -->
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
								<i class="fa fa-plus"></i> Add News &amp; Updates
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/mobile_app/insert_news_updates/" >
								<div class="form-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Courses <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select class="form-control" id="select2_sample2" name="course_ids[]" multiple required>
                                                        <?php
                                                            foreach($courses as $course):
                                                        ?>
                                                        <option value="<?php echo $course['course_id'];?>"><?php echo $course['course_name'];?></option>
                                                        <?php
                                                            endforeach;
                                                        ?>
                                                    </select>
                                                    <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">News <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <textarea class="wysihtml5 form-control" rows="6" name="news"></textarea>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Add News &amp; Updates</button>
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
								<i class="fa fa-list"></i> Manage News &amp; Updates
							</div>
						</div>
						<div class="portlet-body">
							<table class="table table-bordered table-hover" id="sample_3">
							<thead>
							<tr>
                                <th class="hidden">
									Hidden
								</th>
								<th class="hidden">
									Hidden
								</th>
								<th class="hidden">
									Hidden
								</th>
								<th>
									Sr No.
								</th>
                                <th>
                                	News &amp; Updates
                                </th>
								<th>
                                	Course
                                </th>
                                <th>
									Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=1;
								foreach($news_updates as $news):
							?>
                            <tr class="odd gradeX">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
								<td class="hidden">
                                	<?php echo $i;?>
                                </td>
								<td class="hidden">
                                	<?php echo $i;?>
                                </td>
								<td>
									<?php echo $i;?>
								</td>
                                <td>
									<?php echo strip_tags($news['news']);?>
								</td>
								<td>
									<?php
										$course_ids = explode(',',$news['course_ids']);
										$a=1;
										foreach($course_ids as $course_id)
										{
											echo $a.'. '.$this->db->get_where('courses',array('course_id'=>$course_id))->row()->course_name.'<br />';
											$a++;
										}
									?>
								</td>
								<td>
                                    <a href="<?php echo site_url().'/mobile_app/edit_news_updates/'.$news['news_id'];?>" title="Edit Campus" class="btn blue"><i class="fa fa-edit"></i></a>
									<a onclick="return confirm('Are you sure you want to delete this News?')" href="<?php echo site_url().'/mobile_app/delete_news_updates/'.$news['news_id'];?>" title="Edit Campus" class="btn red"><i class="fa fa-trash"></i></a>
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