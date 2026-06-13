	
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
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-user"></i> New Expense Entries
							</div>
						</div>
                        <div class="portlet-body">
							<table class="table table-striped table-bordered table-hover" id="sample_13">
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
									 Purpose
								</th>
                                <th>
									 Amount
								</th>
                                <th>
									 Date
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
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($new_expense_entries as $expense):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
								<td>
									<?php echo $expense['campus_name']?>
								</td>
                                <td>
									<?php 
										if($expense['expense_category_id']==9):
											echo $expense['name'];
											echo '<br />';
											$user = $this->db->get_where('users', array('user_id'=>$expense['user_id']))->result_array();
											echo @$user[0]['first_name'].' '.@$user[0]['last_name'];
										else:
											echo @$expense['name'];
										endif;
									?>
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
									<?php echo $expense['purpose']?>
								</td>
								<td>
									<?php echo $expense['amount']?>
								</td>
                                <td>
									<?php echo $expense['date']?>
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
								<td>
                                    <a href="<?php echo site_url();?>/dashboard/clear_new_expense_entries/<?php echo $expense['expense_id']?>" class="btn green">Clear</a>
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
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->