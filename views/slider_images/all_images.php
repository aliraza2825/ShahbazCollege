	
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
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i>All Event Images
							</div>
						</div>
						<div class="portlet-body">
							<div class="table-toolbar">
								<div class="row">
									<div class="col-md-6">
										<div class="btn-group">
											<button onclick="location.href = '<?php echo site_url()?>/slider_images/add_slider_image'" class="btn green">
											Add New <i class="fa fa-plus"></i>
											</button>
										</div>
									</div>
								</div>
							</div>
							<table class="table table-striped table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
                                <th class="hidden">
									 Hidden
								</th>
                                <th>
									 Image #
								</th>
                                <th>
									 Image
								</th>
                                <th>
									 Campus
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=1;
								foreach($images as $image):
								$access = checkUserAccess();
								$campus_ids = @explode(',',$access[0]['campus_ids']);
								
								$campuses = explode(',',$image['campus_ids']);
								$result=array_intersect($campus_ids,$campuses);
								if($this->session->userdata('role')!='Admin' && count($result)>0):
								
							?>
                            <tr class="odd gradeX">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $i;?>
								</td>
                                <td>
									<img src="<?php echo base_url().'slider_images/'.$image['image']?>" alt="" width="100" />
								</td>
                                <td>
                                	<?php
                                    	$campuses = explode(',',$image['campus_ids']);
										foreach($campuses as $campus)
										{
											echo $this->db->get_where('campuses', array('campus_id'=>$campus))->row()->campus_name;
											echo '<br />';
										}
									?>
                                </td>
								<td>
                                	<a title="Edit" href="<?php echo site_url().'/slider_images/edit_slider_image/'.$image['id'];?>" class="btn blue"><i class="fa fa-edit"></i></a>
                                    <a title="Delete" onclick="return confirm('Are you sure you want to delete this Image?')" href="<?php echo site_url().'/slider_images/delete/'.$image['id'];?>" class="btn red"><i class="fa fa-trash"></i></a>
								</td>
							</tr>
                            <?php
                            	elseif($this->session->userdata('role')=='Admin'):
							?>
                            <tr class="odd gradeX">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $i;?>
								</td>
                                <td>
									<img src="<?php echo base_url().'slider_images/'.$image['image']?>" alt="" width="100" />
								</td>
                                <td>
                                	<?php
                                    	$campuses = explode(',',$image['campus_ids']);
										foreach($campuses as $campus)
										{
											echo $this->db->get_where('campuses', array('campus_id'=>$campus))->row()->campus_name;
											echo '<br />';
										}
									?>
                                </td>
								<td>
                                	<a title="Edit" href="<?php echo site_url().'/slider_images/edit_slider_image/'.$image['id'];?>" class="btn blue"><i class="fa fa-edit"></i></a>
                                    <a title="Delete" onclick="return confirm('Are you sure you want to delete this Image?')" href="<?php echo site_url().'/slider_images/delete/'.$image['id'];?>" class="btn red"><i class="fa fa-trash"></i></a>
								</td>
							</tr>
                            <?php
								endif;
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