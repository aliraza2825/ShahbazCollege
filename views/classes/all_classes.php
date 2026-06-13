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
            <!-- BEGIN DASHBOARD STATS -->
			<div class="row">
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div style="display: none;" class="dashboard-stat blue-madison">
						<div class="visual">
							<i class="icon-map"></i>
						</div>
						<div  class="details">
							<div class="number">
								 <?php echo 0;?>
							</div>
							<div class="desc">
								 Classes
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
								<i class="fa fa-list"></i>All Classes
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
									 Campus
								</th>
								<th>
									 Course
								</th>
								<th>
									 Class Name
								</th>
								<th>
									 Badge No
								</th>
                                <th>
									 Session
								</th>
								<th>
									 Per Student Fee
								</th>
								<th>
									Dead Line For Add / Edit Student
								</th>
								<th>
									 Maximum Fee Last Date
								</th>
                                <th>
									 Phone
								</th>
                                <th>
									 Total Students
								</th>
								<th>
									 First Time Council Exam No.
								</th>
								<th>
									 Online Study
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i = 0;
								foreach($classes as $class):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $class['class_id']?>
								</td>
                                <td>
									 <?php echo $class['campus_name']?>
								</td>
								<td>
									 <?php echo $class['course_name']?>
								</td>
								<td>
									<?php echo $class['name']?>
								</td>
								<td>
									<?php echo $class['badge_no']?>
								</td>
                                <td>
									<?php echo $class['session']?>
								</td>
								<td>
									<?php echo $class['class_fee']?>
								</td>
								<td>
									<?php
										if($class['dead_line_entry']>=date('Y-m-d'))
										{
											$alert = 'label label-sm label-success';
										}
										else
										{
											$alert = 'label label-sm label-danger';
										}
									?>
									<span class="<?php echo $alert;?>"><?php echo $class['dead_line_entry']?></span>
								</td>
								<td>
									<?php echo $class['maximum_fee_last_date']?>
								</td>
                                <td>
									<?php echo $class['phone1'].' / '.$class['phone2'];?>
								</td>
                                <td>
									<?php
										echo '<button class="btn btn-success">Active Students : '.count($this->db->get_where('students',array('class_id'=>$class['class_id'],'status'=>1))->result_array()).'</button>';
										echo '<br />';
										echo '<button class="btn btn-danger">Deactive Students : '.count($this->db->get_where('students',array('class_id'=>$class['class_id'],'status'=>0))->result_array()).'</button>';;
									?>
								</td>
								<td>
									<?php echo $class['exam_no']?>
								</td>
								<td>
									<?php 
										if($class['online_study']==1)
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
                                    	if(@$myAccess[0]['class_edit']==1 || $this->session->userdata('role')=='Admin'):
									?>
                                    <a href="<?php echo site_url().'/classes/edit_class/'.$class['class_id'];?>" title="Edit" class="btn blue"><i class="fa fa-edit"></i></a>
                                    <?php
                                    	endif;
									?>
                                    
									<a href="<?php echo site_url().'/classes/attendence/'.$class['class_id'];?>" title="Attendence" class="btn yellow"><i class="fa fa-calendar"></i></a>
									
                                    <a href="<?php echo site_url().'/classes/students/'.$class['class_id'];?>" title="Students" class="btn green"><i class="fa fa-graduation-cap"></i></a>
                                    
                                    <?php
                                    	if(@$myAccess[0]['class_delete']==1 || $this->session->userdata('role')=='Admin'):
									?>
                                    <a onclick="return confirm('Are you sure you want to delete this Class?')" href="<?php echo site_url().'/classes/delete/'.$class['class_id'];?>" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>
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