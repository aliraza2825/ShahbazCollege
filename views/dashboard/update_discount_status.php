	
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
            	<div class="col-md-12">
                	<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-money"></i> Update Fee Requests
							</div>
						</div>
						<div class="portlet-body">
							<table  class="table table-striped table-bordered table-hover" id="sample_15">
							<thead>
							<tr>
                                <th class="hidden">
                                	Hidden
                                </th>
                                <th>
                                	Request Date
                                </th>

                                <th>
                                	Student Detail
                                </th>
                                <th>
                                    Remaining Fee
                                </th>
                                <th>
                                    Discount Want
                                </th>
                                <th>
                                    Application
                                </th>
                                <th>
                                    Reason
                                </th>
                                <th>
									 Add By
								</th>

                                <th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
                            <?php
                            	$i=0;
								foreach($fee_requests as $fee_request):
								$originalDetail = getOriginalPayemntDetails($fee_request['id']);
							?>
                            <tr>
                            	<td class="hidden"><?php echo $i;?></td>
                                <td><?php echo $fee_request['created_at'];?>
                                </td>
                                <td>
                                    Campus : <?php echo $fee_request['campus_name'];?>
                                    <br />
                                    Course : <?php echo $fee_request['course_name'];?>
                                    <br />
                                    Session : <span class="bold"><?php echo $fee_request['session'];?></span>

                                    <br />
                                    Student Name : <span class="bold"><?php echo $fee_request['first_name'].' '.$fee_request['last_name'];?></span>
                                    <br />
                                    CNIC : <?php echo $fee_request['cnic'];?>
                                    <br />
                                    Father Name : <?php echo $fee_request['father_name'];?>
                                    <br />
                                    Roll # : <span class="bold"><?php echo $fee_request['roll_no'];?></span>
                                    <br />
                                    Mobile : <span class="bold"><?php echo $fee_request['mobile'];?> - <?php echo $fee_request['emergency_no'];?></span>
                                </td>
                                <td>
									<?php echo $fee_request['remaining_fee'];?>

                                </td>
                                <td>
									<?php echo $fee_request['discount'];?>

                                </td>
                                <td>
                                	<?php
                                    	if($fee_request['application']!=''):
									?>
                                    <a href="<?php echo base_url();?>uploads/<?php echo $fee_request['application'];?>" class="btn purple" target="_blank">Image</a>

                                    <?php endif; ?>
                                </td>
                                <td><?php echo $fee_request['created_by'];?></td>
                                <td><?php echo $fee_request['reason'];?></td>
                                <td>
                                	<a class="btn blue" target="_blank" href="<?php echo site_url();?>/students/payments_paid/<?php echo $fee_request['student_id'];?>">View</a>
                                    <br /><br />
                                    <a class="btn green rmv" href="<?php echo site_url();?>/dashboard/clear_discount_update/<?php echo $fee_request['id'];?>" onclick="return confirm('Are you sure ?')">Clear</a>
									<br /><br />
									<a class="btn red" href="<?php echo site_url();?>/dashboard/reject_discount_fee_update/<?php echo $fee_request['id'];?>" onclick="return confirm('Are you sure ?')">Reject</a>
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
                </div>
            </div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->
	<script>
	    document.addEventListener("DOMContentLoaded", function(event) {
            $('.rmv').click(function(){
                $(this).hide();
            });
        });
	</script>