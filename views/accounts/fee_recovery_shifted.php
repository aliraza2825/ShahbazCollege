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
								<i class="fa fa-list"></i>Fee Recovery Shifted Details
							</div>
						</div>
						<div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover" id="sample_10">
							<thead>
							<tr>
								<th class="hidden">
                                	 hidden
                                </th>
                                <th>
                                    From Campus
                                </th>
                                <th>
                                    To Campus
                                </th>
                                <th>
                                    Shifted Date
                                </th>
                                <th>
									 Class
								</th>
                                <th>
									 Student / Contractor
								</th>
                                <th>
                                    Challan No
                                </th>
                                <th>
									 Amount
								</th>
                                <th>
                                	Paid Date
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
								foreach($fees as $fee):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
                                    <?php
                                    if ($this->uri->segment(2) == 'deduction')
                                        echo $from_campus[0]['campus_name'];
                                    else
                                        echo $this->db->get_where('campuses',array('campus_id'=>$details[0]['from_class']))->row()->campus_name;
                                    ?>
                                </td>
								<td>
									<?php
                                    if ($this->uri->segment(2) == 'deduction')
                                        echo $fee['campus_name'];
                                    else
                                        echo $from_campus[0]['campus_name'];
                                    ?>
								</td>
                                <td>
                                    <?php echo $details[0]['created_at']?>
                                </td>
                                <td>
									<?php echo $fee['class_name']?>
								</td>
                                <td>
									<?php echo $fee['first_name'].' '.$fee['last_name'].' ('.$fee['roll_no'].')';?>
								</td>
                                <td>
									<?php echo $fee['challan_no']?>
								</td>
                                <td>
									<?php echo $fee['actual_amount']?>
								</td>
                                <td>
									<?php echo $fee['paid_date']?>
								</td>
                                <td>
									<?php echo $fee['actual_paid_date']?>
								</td>
                                <td>
									<?php
                                    	if($fee['scan_challan']!=''):
									?>
                                    <a href="<?php echo base_url().'uploads/'.$fee['scan_challan'];?>" target="_blank">
                                    	<button type="button" class="btn btn-default"><i class="fa fa-image"></i> Image</button>
                                    </a>
                                    <?php
                                    	endif;
									?>
								</td>
                                <td>
									<?php echo $fee['add_by']?>
								</td>
                                <td>
									<?php echo $fee['last_edit']?>
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