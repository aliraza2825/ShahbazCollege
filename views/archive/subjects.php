	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			All Classes <small>Here you can find all classes</small>
			</h3>-->
            <!-- END PAGE HEADER-->
            <!-- BEGIN DASHBOARD STATS -->
			<div class="row">
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat blue-madison">
						<div class="visual">
							<i class="icon-map"></i>
						</div>
						<div  class="details">
							<div class="number">
								 <?php echo $count;?>
							</div>
							<div class="desc">
								 Subjects
							</div>
						</div>
						<a class="more" href="javascript:;">
						View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
			</div>
			<!-- END DASHBOARD STATS -->
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
								<i class="fa fa-list"></i>All Subjects
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
									 Subject Name
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($subjects as $subject):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $subject['course_subject_id']?>
								</td>
								<td>
									<?php echo $subject['subject_name']?>
								</td>
								<td>
									<?php
                                    	if($this->session->userdata('role')=='Admin'):
									?>
                                    <a href="<?php echo site_url().'/subjects/edit_subject/'.$subject['course_subject_id'];?>" title="Edit" class="btn blue"><i class="fa fa-edit"></i></a>
                                    <a href="<?php echo site_url().'/archive/restore_subject/'.$subject['course_subject_id'];?>" title="Edit" class="btn green"><i class="fa fa-refresh"></i></a>
                                    <a onclick="return confirm('Are you sure you want to delete this Subject permanently?')" href="<?php echo site_url().'/archive/delete_subject/'.$subject['course_subject_id'];?>" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>
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