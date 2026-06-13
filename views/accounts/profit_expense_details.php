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
								<i class="fa fa-list"></i>All Expenses Campus Code <?php echo $campuses[0]['campus_code'];?> <small><?php echo $campuses[0]['campus_name'];?></small> From <?php echo $this->uri->segment(3);?> <?php echo $this->uri->segment(4);?>
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
									 Campus
								</th>
                                <th>
									 Category
								</th>
                                <th>
									 Title
								</th>
                                <th>
									 Amount
								</th>
                                <th>
									 Date
								</th>
                                <th>
									 Upload Date
								</th>
                                <th>
									 Receipt
								</th>
                                <th>
									 Add By
								</th>
                                <th>
									 Last Edit
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($expenses as $expense):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
								<td>
									<?php echo $expense['campus_name']?>
								</td>
                                <td>
									<?php echo $expense['name']?>
								</td>
                                <td>
									<?php echo $expense['title']?>
                                    <?php
                                    	if($expense['expense_category_id']==1):
									?>
                                    	<br />
                                    	Rickshaw Number : <?php echo $expense['rickshaw_number'];?>
                                        <br />
                                        Rickshaw Driver No : <?php echo $expense['driver_phone'];?>
                                    <?php
                                    	endif;
									?>
								</td>
                                <td>
									<?php echo $expense['amount']?>
								</td>
                                <td>
									<?php echo $expense['date']?>
								</td>
                                <td>
									<?php echo $expense['actual_date']?>
								</td>
                                <td>
									<?php
                                    	if($expense['image']!=''):
									?>
                                    <a href="<?php echo base_url().'uploads/'.$expense['image'];?>" target="_blank">
                                    	<button type="button" class="btn btn-default"><i class="fa fa-image"></i> Image</button>
                                    </a>
                                    <?php
                                    	endif;
									?>
								</td>
                                <td>
									<?php echo $expense['add_by']?>
								</td>
                                <td>
									<?php echo $expense['last_edit']?>
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