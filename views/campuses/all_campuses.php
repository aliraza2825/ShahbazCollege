	
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
								<i class="fa fa-list"></i>All Campuses
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
									 Campus Code
								</th>
								<th>
									 Campus Roll No Code
								</th>
                                <th>
									 Campus Name
								</th>
                                <th>
									 Phones
								</th>
                                <th>
									 Address
								</th>
                                <th>
                                    For Mobile App
                                </th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($campuses as $campus):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $campus['campus_code'];?>
								</td>
                                <td>
									 <?php echo $campus['roll_no_code'];?>
								</td>
                                <td>
									 <?php echo $campus['campus_name'];?>
								</td>
                                <td>
									 <?php echo $campus['phone1'].' / '.$campus['phone2'];?>
								</td>
								<td>
									<?php echo $campus['address'];?>
								</td>
                                <td>
                                    <?php
                                    if ($campus['for_mobile_application'] == '1')
                                        echo  "<a data-toggle='modal' data-id='$i' class='btn btn-primary' style='width: 50px'>YES</a>";
                                    else
                                        echo  "<a data-toggle='modal' class='btn red'  style='width: 50px'  >NO</a>";?>
                                </td>
								<td>
                                    <a href="<?php echo site_url().'/campuses/edit_campus/'.$campus['campus_id'];?>" title="Edit" class="btn blue"><i class="fa fa-edit"></i></a>
                                    <a href="<?php echo site_url().'/campuses/upload_campus_documents/'.$campus['campus_id'];?>" title="Document" class="btn yellow"><i class="fa fa-image"></i></a>
                                    <a onclick="return confirm('Are you sure you want to delete this Campus?')" href="<?php echo site_url().'/campuses/delete/'.$campus['campus_id'];?>" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>
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
    