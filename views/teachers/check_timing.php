	
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
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/teachers/update_timing/<?php echo $this->uri->segment(3);?>">
								<div class="form-body">
                                    <table class="table table-striped table-bordered table-hover">
                                    	<thead>
                                        	<tr>
                                            	<th>Day</th>
                                                <th>CheckIn Time</th>
                                                <th>CheckOut Time</th>
                                                <th>Half Day After Time</th>
                                                <th>Full Day After Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        	<tr>
                                            	<td>Monday</td>
                                                <td>
                                                	<div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="checkin_time[]" value="<?php echo @$timings[0]['checkin_timing']?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button">
                                                                <i class="fa fa-clock-o"></i>
                                                            </button>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                	<div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="checkout_time[]" value="<?php echo @$timings[0]['checkout_timing']?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button">
                                                                <i class="fa fa-clock-o"></i>
                                                            </button>
                                                        </span>
                                                        <input type="hidden" name="day[]" value="Monday" />
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="half_day_on[]" value="<?php echo @$timings[0]['half_day_on']?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button">
                                                                <i class="fa fa-clock-o"></i>
                                                            </button>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="full_day_on[]" value="<?php echo @$timings[0]['full_day_on']?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button">
                                                                <i class="fa fa-clock-o"></i>
                                                            </button>
                                                        </span>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                            	<td>Tuesday</td>
                                                <td>
                                                	<div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="checkin_time[]" value="<?php echo @$timings[1]['checkin_timing']?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button">
                                                                <i class="fa fa-clock-o"></i>
                                                            </button>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                	<div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="checkout_time[]" value="<?php echo @$timings[1]['checkout_timing']?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button">
                                                                <i class="fa fa-clock-o"></i>
                                                            </button>
                                                        </span>
                                                        <input type="hidden" name="day[]" value="Tuesday" />
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="half_day_on[]" value="<?php echo @$timings[1]['half_day_on']?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button">
                                                                <i class="fa fa-clock-o"></i>
                                                            </button>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="full_day_on[]" value="<?php echo @$timings[1]['full_day_on']?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button">
                                                                <i class="fa fa-clock-o"></i>
                                                            </button>
                                                        </span>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                            	<td>Wednesday</td>
                                                <td>
                                                	<div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="checkin_time[]"  value="<?php echo @$timings[2]['checkin_timing']?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button">
                                                                <i class="fa fa-clock-o"></i>
                                                            </button>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                	<div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="checkout_time[]" value="<?php echo @$timings[2]['checkout_timing']?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button">
                                                                <i class="fa fa-clock-o"></i>
                                                            </button>
                                                        </span>
                                                        <input type="hidden" name="day[]" value="Wednesday" />
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="half_day_on[]" value="<?php echo @$timings[2]['half_day_on']?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button">
                                                                <i class="fa fa-clock-o"></i>
                                                            </button>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="full_day_on[]" value="<?php echo @$timings[2]['full_day_on']?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button">
                                                                <i class="fa fa-clock-o"></i>
                                                            </button>
                                                        </span>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                            	<td>Thursday</td>
                                                <td>
                                                	<div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="checkin_time[]" value="<?php echo @$timings[3]['checkin_timing']?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button">
                                                                <i class="fa fa-clock-o"></i>
                                                            </button>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                	<div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="checkout_time[]" value="<?php echo @$timings[3]['checkout_timing']?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button">
                                                                <i class="fa fa-clock-o"></i>
                                                            </button>
                                                        </span>
                                                        <input type="hidden" name="day[]" value="Thursday" />
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="half_day_on[]" value="<?php echo @$timings[3]['half_day_on']?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button">
                                                                <i class="fa fa-clock-o"></i>
                                                            </button>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="full_day_on[]" value="<?php echo @$timings[3]['full_day_on']?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button">
                                                                <i class="fa fa-clock-o"></i>
                                                            </button>
                                                        </span>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                            	<td>Friday</td>
                                                <td>
                                                	<div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="checkin_time[]" value="<?php echo @$timings[4]['checkin_timing']?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button">
                                                                <i class="fa fa-clock-o"></i>
                                                            </button>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                	<div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="checkout_time[]" value="<?php echo @$timings[4]['checkout_timing']?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button">
                                                                <i class="fa fa-clock-o"></i>
                                                            </button>
                                                        </span>
                                                        <input type="hidden" name="day[]" value="Friday" />
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="half_day_on[]" value="<?php echo @$timings[4]['half_day_on']?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button">
                                                                <i class="fa fa-clock-o"></i>
                                                            </button>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="full_day_on[]" value="<?php echo @$timings[4]['full_day_on']?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button">
                                                                <i class="fa fa-clock-o"></i>
                                                            </button>
                                                        </span>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                            	<td>Saturday</td>
                                                <td>
                                                	<div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="checkin_time[]" value="<?php echo @$timings[5]['checkin_timing']?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button">
                                                                <i class="fa fa-clock-o"></i>
                                                            </button>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                	<div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="checkout_time[]" value="<?php echo @$timings[5]['checkout_timing']?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button">
                                                                <i class="fa fa-clock-o"></i>
                                                            </button>
                                                        </span>
                                                        <input type="hidden" name="day[]" value="Saturday" />
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="half_day_on[]" value="<?php echo @$timings[5]['half_day_on']?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button">
                                                                <i class="fa fa-clock-o"></i>
                                                            </button>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="full_day_on[]" value="<?php echo @$timings[5]['full_day_on']?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button">
                                                                <i class="fa fa-clock-o"></i>
                                                            </button>
                                                        </span>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                            	<td>Sunday</td>
                                                <td>
                                                	<div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24"name="checkin_time[]" value="<?php echo @$timings[6]['checkin_timing']?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button">
                                                                <i class="fa fa-clock-o"></i>
                                                            </button>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                	<div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="checkout_time[]" value="<?php echo @$timings[6]['checkout_timing']?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button">
                                                                <i class="fa fa-clock-o"></i>
                                                            </button>
                                                        </span>
                                                        <input type="hidden" name="day[]" value="Sunday" />
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="half_day_on[]" value="<?php echo @$timings[6]['half_day_on']?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button">
                                                                <i class="fa fa-clock-o"></i>
                                                            </button>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="full_day_on[]" value="<?php echo @$timings[6]['full_day_on']?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button">
                                                                <i class="fa fa-clock-o"></i>
                                                            </button>
                                                        </span>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
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
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->