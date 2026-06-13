<?php
	$myAccess = checkUserAccess();
?>	
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
								<i class="fa fa-list"></i>All Councils
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
									 Name
								</th>
								<th>
									 Code
								</th>
								<th>
									 Phone
								</th>
								<th>
									 Address
								</th>
								<th>
									 Location
								</th>
								<th>
									 Courses
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i = 0;
								foreach($councils as $council):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $council['council_id']?>
								</td>
                                <td>
									 <?php echo $council['name']?>
								</td>
								<td>
									 <?php echo $council['code']?>
								</td>
								<td>
									 <?php echo $council['phone']?>
								</td>
								<td>
									<?php echo $council['address']?>
								</td>
								<td>
									<?php echo $council['location']?>
								</td>
                                <td>
									<?php
									    $courses = explode(',',$council['course_ids']);
									    foreach($courses as $course)
									    {
									        echo $this->db->get_where('courses',array('course_id'=>$course))->row()->course_name.'<br />';
									    }
									?>
								</td>
								
								<td>
									<a href="<?php echo site_url().'/councils/edit_council/'.$council['council_id'];?>" title="Edit" class="btn blue"><i class="fa fa-edit"></i></a>
                                    <a onclick="return confirm('Are you sure you want to delete this Council?')" href="<?php echo site_url().'/councils/delete/'.$council['council_id'];?>" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>
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