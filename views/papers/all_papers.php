	
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
								<i class="fa fa-list"></i>All Papers
							</div>
						</div>
                        
						<div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover">
							<thead>
							<tr>
								<th class="hidden">
                                	 hidden
                                </th>
								<th>
									 Subject
								</th>
                                <th>
									 Date
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($papers as $paper):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
								<td>
									<?php echo $paper['subject_name']?>
								</td>
                                <td>
									<?php echo $paper['date']?>
								</td>
								<td>
                                    <a href="<?php echo site_url().'/papers/edit_paper/'.$paper['paper_id'];?>" title="Edit" class="btn blue"><i class="fa fa-edit"></i></a>
                                    <a onclick="return confirm('Are you sure you want to delete this Paper?')" href="<?php echo site_url();?>/papers/delete/<?php echo $paper['paper_id'];?>" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>
                                    <?php
                                    	$count = $this->db->get_where('results', array('paper_id'=>$paper['paper_id']))->result_array();
										if(count($count)>0):
									?>
                                    <a href="<?php echo site_url().'/papers/show_result/'.$paper['paper_id'];?>" title="Result" class="btn green"><i class="fa fa-check"></i></a>
                                    <?php
                                    	else:
									?>
                                    <a href="<?php echo site_url().'/papers/add_result/'.$paper['paper_id'];?>" title="Result" class="btn red"><i class="fa fa-times"></i></a>
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