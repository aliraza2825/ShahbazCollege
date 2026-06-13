	
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
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i>Fee Fine Details
							</div>
						</div>
						<div class="portlet-body">

                            <table class="table table-bordered table-hover" id="sample_2">
							<thead>
							<tr>

                                <th class="hidden">
									 Hidden
								</th>
                                <th>
                                    Student Details
                                </th>
                                <th>
									 Fee details
								</th>
                              
                                <th>
                                	Fine Collected
                                </th>

								 <th>
									 % For this Fee
								</th>

							</tr>
							</thead>

							<tbody>
							<?php
								$i=0;
								foreach($fine_students as $due):
//                                    if ($due['fine_amount'] > 0):
                                    $class = '';
							?>
                            <tr class="<?php echo $class ?>">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
                                <td>
                                    Campus : <?php echo $due['campus_name'];?>
                                    <br />
                                    Class : <?php echo $due['class_name'];?>
                                    <br />
                                    Student Name : <span class="bold"><?php echo $due['first_name'].' '.$due['last_name'];?></span>
                                    <br />
                                    CNIC : <?php echo $due['cnic'];?>
                                    <br />
                                    Roll # : <span class="bold"><?php echo $due['roll_no'];?></span>
                                    <br />
                                    Mobile : <span class="bold"><?php echo $due['mobile'];?> - <?php echo $due['emergency_no'];?></span>
                                    <br />
                                    <?php
                                    if($due['merged_challan'] != null){

                                        echo "Merged Challan # : ".$due['paid_challans'];

                                    }
                                    else
                                    {
                                        echo "Challan # : ".$due['challan_no'];
                                    }

                                    ?>
                                </td>
                                <td>
                                										
									Fee : <?php echo $due['amount'];?>
                                    <br />
                                    Remaining Fee : <?php echo $due['extra_amount'];?>
                                    <br />
                                    Last Date : <span class="bold"><?php echo date('d F, Y', strtotime($due['dead_line']));?></span>
									
                                </td>
                                
                                <td>
                                    <?php if ($due['isdel'] == '1')

                                        echo "Fee Deleted";
                                    else

                                        echo $due['fine_amount']; ?>
                                </td>
                               
								<td>
								
									<?php
									
										echo "50% in Rs : ".(0.1*$due['fine_amount']);
									
									?>
								</td>
							</tr>
                            <?php
								$i++;
//								endif;
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
