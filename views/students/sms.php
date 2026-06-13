
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Add Teacher <small>You can add teacher here</small>
			</h3>-->
			<!-- END PAGE HEADER-->
            <!-- BEGIN DASHBOARD STATS -->
			<!-- END DASHBOARD STATS -->
			<!-- BEGIN PAGE CONTENT-->
            <?php if(@$this->session->userdata('message')):?>
                <div class="alert alert-success">
                    <button class="close" data-close="alert"></button>
                    <span>
                    <?php echo $this->session->userdata('message');?> </span>
                </div>
            <?php endif;?>
            <?php if(@$this->session->userdata('error')):?>
                <div class="alert alert-danger">
                    <button class="close" data-close="alert"></button>
                    <span>
                    <?php echo $this->session->userdata('error');?> </span>
                </div>
            <?php endif;?>
			<div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-envelope"></i> Send Message
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/students/send_sms/<?php echo $this->uri->segment(3);?>">
								<div class="form-body">
                                    <div class="form-group">
										<label class="col-md-3 control-label">Message <span class="required">*</span></label>
										<div class="col-md-9">
                                            	<textarea class="form-control" rows="3" name="message" maxlength="250"></textarea>
										</div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                            <button type="submit" class="btn green">Send Message</button>
                                            <button onclick="location.href = '<?php echo site_url()?>/students/all_students'" type="button" class="btn default">Cancel</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
            <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> All SMS
							</div>
						</div>
						<div class="portlet-body">
                            <?php /*?><?php
                            	foreach($smss as $sms):
								
								if(@$sms['id'])
								{
							?>
                            	<div>
                                	<div class="pull-left" style="background-color:#d64635; color:#fff; padding:10px; margin:10px; border-radius:10px !important; min-width:200px;">
										<p style="font-size:20px;"><?php echo $sms['msg']?></p>
                                        <span class="pull-right" style="font-style:10px;"><?php echo $sms['date']?></span>
                                    </div>
                                	<div class="clearfix"></div>
                                </div>
                            <?php		
								}
								else
								{
							?>
                            	<div>
                                	<div class="pull-right" style="background-color:#397FAE; color:#fff; padding:10px; margin:10px; border-radius:10px !important; min-width:200px;">
										<p style="font-size:20px;"><?php echo $sms['message']?></p>
                                        <span class="pull-right" style="font-style:10px;"><?php echo $sms['date']?></span>
                                    </div>
                                	<div class="clearfix"></div>
                                </div>
                            <?php
								}
							?>
                            
                            <?php
                            	endforeach;
							?><?php */?>
                            
                            <?php
                            	echo date('Y-m-d H:i:s')
							?>
                            
							<table class="table table-striped table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
								<th class="hidden">
                                	 hidden
                                </th>
								<th>
									 Number
								</th>
                                <th>
									 SMS
								</th>
                                <th>
									 Date
								</th>
                                <th>
									 Send by
								</th>
                                <th>
                                	Status
                                </th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($smss as $sms):
								if(@$sms['id']):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
								<td>
									<?php echo $sms['phone']?>
								</td>
                                <td>
									<?php echo $sms['msg']?>
								</td>
                                <td>
									<?php echo date('Y-m-d H:i',strtotime('+2 hour +0 minutes +0seconds',strtotime($sms['date'])));?>
								</td>
                                <td>
									<?php //echo $sms['add_by']?>
								</td>
                                <td>
									<button class="btn red"><i class="fa fa-download"></i> Receive</button>
								</td>
							</tr>
                            <?php
								else:
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
								<td>
									<?php echo $sms['number']?>
								</td>
                                <td>
									<?php echo $sms['message']?>
								</td>
                                <td>
									<?php echo $sms['date']?>
								</td>
                                <td>
									<?php echo $sms['add_by']?>
								</td>
                                <td>
									<?php 
										if($sms['status']=='send')
										{
											echo '<button class="btn green"><i class="fa fa-check"></i> Send</button>';
										}
										elseif($sms['status']=='failed')
										{
											echo '<button class="btn red"><i class="fa fa-close"></i> Failed</button>';
										}
										else
										{
											echo '<button class="btn yellow"><i class="fa fa-refresh"></i> Pending</button>';
										}
									?>
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