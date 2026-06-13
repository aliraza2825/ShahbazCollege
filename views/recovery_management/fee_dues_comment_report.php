	
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
								<i class="fa fa-list"></i>Fee Details
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
                                    Paid Date
                                </th>
                                <th>
                                	STATUS
                                </th>
                               
								 <th>
									 % For this Fee
								</th>

							</tr>
							</thead>

							<tbody>
							<?php
								$i=0;
								foreach($fee_dues_comments as $due):
                                    
                                    $class = '';
                                    if($due['Fstatus'] == 'Paid')
                                    {
                                        $class = 'alert alert-success';
                                    }
                                    else if ($due['Fstatus'] == 'UnPaid')
                                    {
                                        $class = 'alert alert-danger';
                                    }else{

                                    }

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
                                </td>
                                <td>
                                										
									Fee : <?php echo $due['amount'];?>
                                    <br />
                                    Remaining Fee : <?php echo $due['extra_amount'];?>
                                    <br />
                                    Last Date : <span class="bold"><?php echo date('d F, Y', strtotime($due['dead_line']));?></span>
									
                                </td>
                                <td>
                                    <?php
                                        if($due['Fstatus'] == 'Paid')
                                        {
                                            echo $due['paid_date'];
                                        }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($due['isdel'] == '1')

                                        echo "Fee Deleted";
                                    else

                                        echo $due['Fstatus']; ?>
                                </td>
                               
								<td>
								
									<?php
									if($due['Fstatus'] == 'Paid')
										$percent_amount=$this->uri->segment(5);	
									else	
										$percent_amount=0;										
									
									if($due['split'] == '1' ){
										
										echo "50% in Rs : ".(0.5*$percent_amount);
									} else if($due['split'] == '2' ){
										
										echo "25% in Rs : ".(0.25*$percent_amount);
									}else{
										echo "100% in Rs : ".($percent_amount);
									}
									?>
								</td>
							</tr>
                            <?php
								$i++;
                            	endforeach;
							?>
                            
                            <?php
								$i=0;
								foreach($contracts_fee_dues_comments as $due):
								$class = '';
								if($due['Fstatus'] == 'Paid')
								{
									$class = 'alert alert-success';
								}
								else
								{
									$class = 'alert alert-danger';
								}
							?>
                            <tr class="<?php echo $class;?>">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
                               
                               
                                <td>
									<?php echo @$due['campus_name']?><br />
									<?php echo @$due['contractor_id_from_college']?><br />
									<?php echo @$due['name'];?>
									<br />
									<?php echo @$due['mobile'];?> <br /> <?php echo @$due['emergency_no'];?>
								</td>
								<td>
                                										
									Fee : <?php echo @$due['amount'];?>
                                    <br />
                                    Remaining Fee : <?php echo @$due['extra_amount'];?>
                                    <br />
                                    Last Date : <span class="bold"><?php echo @date('d F, Y', strtotime($due['dead_line']));?></span>
									
                                </td>
                           
                                <td>
                                	<?php echo $due['Fstatus'];?>
                                </td>
                               
								<td>
								
									<?php
									if($due['Fstatus'] == 'Paid')
										$percent_amount=$this->uri->segment(5);	
									else	
										$percent_amount=0;										
									
									if($due['split'] == '1' ){
										
										echo "50% in Rs : ".(0.5*$percent_amount);
									} else if($due['split'] == '2' ){
										
										echo "25% in Rs : ".(0.25*$percent_amount);
									}else{
										echo "100% in Rs : ".($percent_amount);
									}
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
    <div class="modal fade" id="insertcomment" tabindex="-1"   data-width="500" >


        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title">Add Comments</h4>
        </div>
        <div class="modal-body">
            <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/fees/comment">
                <div class="form-body">


                    <div class="form-group">
                        <label class="col-md-6 control-label">Select Comment</label>
                        <div class="col-md-6">

                            <select class="form-control comment_box comment?>" name="comment" id="comment" >

                                <option value="">Select Comment</option>
                                <option value="Call Not Attended">Call Not Attended</option>
                                <option value="Will Pay On">Will Pay On</option>
                                <option value="Paid">Cell Off</option>
                                <option value="Struck of now">Struck of now</option>

                            </select>

                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-6 control-label">Description <span class="required">*</span></label>
                        <div class="col-md-6">

                            <textarea class="form-control remarks" rows="3" name="description" required></textarea>

                        </div>
                    </div>


                    <div class="form-group" id="datesel">
                    <label class="col-md-6 control-label">Next Due Date</label>
                        <div class="col-md-6">
                            <div class="input-group input-small date date-picker" data-date="" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                        <input type="text" name="selected_date" class="form-control selected-date" value="" readonly>
                                        <span class="input-group-btn">
                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                        </span>
                            </div>
                        </div>

                    </div>

                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-offset-3 col-md-9">

                            <input type="hidden" id="fee_id" name="fee_id" value= $data-id />
                            <button type="submit" id="" class="btn red">Add Comment</button>

                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn default close_button" data-dismiss="modal">Close</button>
        </div>


    </div>