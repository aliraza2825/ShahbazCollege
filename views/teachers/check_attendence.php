	
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
								<i class="fa fa-list"></i> Attendence <?php echo '('.$users[0]['first_name'].' '.$users[0]['last_name'].')';?>
							</div>
						</div>
                        <div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/teachers/check_attendence/<?php echo $this->uri->segment(3);?>">
								<div class="form-body">
                                	<div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label col-md-3">From Date <span class="required">*</span></label>
                                                <div class="col-md-3">
                                                    <div class="input-group input-medium date date-picker" data-date="<?php if(@$this->input->post('from_date')){echo $this->input->post('from_date');}else{echo date('Y-m-d');}?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                        <input type="text" name="from_date" class="form-control" value="<?php if(@$this->input->post('from_date')){echo $this->input->post('from_date');}else{echo date('Y-m-d');}?>" readonly>
                                                        <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label col-md-3">To Date <span class="required">*</span></label>
                                                <div class="col-md-3">
                                                    <div class="input-group input-medium date date-picker" data-date="<?php if(@$this->input->post('to_date')){echo $this->input->post('to_date');}else{echo date('Y-m-d');}?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                        <input type="text" name="to_date" class="form-control" value="<?php if(@$this->input->post('to_date')){echo $this->input->post('to_date');}else{echo date('Y-m-d');}?>" readonly>
                                                        <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Check Attendence</button>
										</div>
									</div>
								</div>
                            </form>
                        </div>
						<div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover">
							<thead>
							<tr>
								<th class="hidden">
                                	 hidden
                                </th>
								<th>
									 Date
								</th>
                                <th>
									 Check In Time
								</th>
                                <th>
									 Check Out Time
								</th>
							</tr>
							</thead>
							<tbody>
                            <?php
                            	foreach($dates as $date):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                </td>
								<td>
                                	<?php echo date('F d, Y', strtotime($date));?>
								</td>
                                <?php
                                	$holiday = $this->db->get_where('holidays', array('date'=>$date))->result_array();
									if(count($holiday)>0):
								?>
                                <td colspan="2" class="alert alert-success">
                                	<p style="text-align:center;">Holiday</p>
                                </td>
								<?php
                                	else:
								?>
                                <td>
                                	<?php 
										$qry = 'SELECT * FROM attendence WHERE machine_user_id='.$machine_id.' AND (time>="'.$date.' 00:00:00" AND time<"'.$date.' 23:59:59") ORDER BY time ASC LIMIT 1';
										$checkin_time = $this->db->query($qry)->result_array();
										if(count($checkin_time)>0)
										{
											echo @date('h:i:s A', strtotime($checkin_time[0]['time']));
										}
										else
										{
											echo 'Absent';
										}
									?>
								</td>
                                <td>
                                	<?php 
										$qry = 'SELECT * FROM attendence WHERE machine_user_id='.$machine_id.' AND (time>="'.$date.' 00:00:00.00" AND time<"'.$date.' 23:59:59.999") ORDER BY time DESC LIMIT 1';
										$checkout_time = $this->db->query($qry)->result_array();
										if(count($checkout_time)>0)
										{
											echo @date('h:i:s A', strtotime($checkout_time[0]['time']));
										}
										else
										{
											echo 'Absent';
										}
									?>
								</td>
								<?php
                                	endif;
								?>
							</tr>
                            <?php
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