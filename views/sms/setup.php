
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Edit Profile <small>You can edit your profile here</small>
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
								<i class="fa fa-envelope"></i> SMS Setup
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/sms/gateway_add">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control campus" name="campus_id">
                                                <option value="">SELECT CAMPUS</option>
                                                <?php 
                                                    foreach($campuses as $campus):
                                                ?>
                                                <option value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name'];?></option>
                                                <?php
                                                    endforeach;
                                                ?>
                                            </select>
                                            <!--<span class="help-inline"></span>-->
                                        </div>
                                    </div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Device ID </label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="device_id" placeholder="Enter device ID" value="">
											<span class="help-inline"></span>
										</div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Add</button>
										</div>
									</div>
								</div>
							</form>
						</div>
                        
					</div>
					<!-- END SAMPLE FORM PORTLET-->
                    <div class="row">
                        <div class="col-md-12">
                            <!-- BEGIN EXAMPLE TABLE PORTLET-->
                            <div class="portlet box grey-cascade">
                                <div class="portlet-title">
                                    <div class="caption">
                                        <i class="fa fa-list"></i> All SMS Gateways
                                    </div>
                                </div>
                                <div class="portlet-body">
                                    <table class="table table-striped table-bordered table-hover" id="sample_2">
                                    <thead>
                                    <tr>
                                        <th class="hidden">
                                             Hidden
                                        </th>
                                        <th>
                                        	Campus
                                        </th>
                                        <th>
                                             Device ID
                                        </th>
                                        <th>
                                             Action
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        $i=0;
                                        foreach($sms_gateways as $sms):
                                    ?>
                                    <tr class="odd gradeX">
                                        <td class="hidden">
                                            <?php echo $i;?>
                                        </td>
                                        <td>
                                        	<?php 
											$campus = $this->db->get_where('campuses', array('campus_id'=>$sms['campus_id']))->result_array();
											if(count($campus)>0)
											{
												echo $campus[0]['campus_name'];
											}
											else
											{
												echo 'N/A';
											}
											?>
                                        </td>
                                        <td>
                                            <?php echo $sms['device_id'];?>
                                        </td>
                                        <td>
                                            <a href="<?php echo site_url().'/sms/edit_sms_gateway/'.$sms['id'];?>" class="btn blue"><i class="fa fa-edit"></i></a>
                                            <a href="<?php echo site_url().'/sms/device_sms/'.$sms['id'];?>" class="btn green"><i class="fa fa-envelope"></i></a>
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
				</div>
			</div>

			<div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-envelope"></i> Advertisement SMS Setup
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/sms/advertisement_gateway_add">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control campus" name="campus_id">
                                                <option value="">SELECT CAMPUS</option>
                                                <?php 
                                                    foreach($campuses as $campus):
                                                ?>
                                                <option value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name'];?></option>
                                                <?php
                                                    endforeach;
                                                ?>
                                            </select>
                                            <!--<span class="help-inline"></span>-->
                                        </div>
                                    </div>
                                    <div class="form-group">
										<label class="col-md-3 control-label">Device ID </label>
										<div class="col-md-9">
											<input type="text" class="form-control input-inline input-medium" name="device_id" placeholder="Enter device ID" value="">
											<span class="help-inline"></span>
										</div>
									</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Add</button>
										</div>
									</div>
								</div>
							</form>
						</div>
                        
					</div>
					<!-- END SAMPLE FORM PORTLET-->
                    <div class="row">
                        <div class="col-md-12">
                            <!-- BEGIN EXAMPLE TABLE PORTLET-->
                            <div class="portlet box grey-cascade">
                                <div class="portlet-title">
                                    <div class="caption">
                                        <i class="fa fa-list"></i> All SMS Gateways
                                    </div>
                                </div>
                                <div class="portlet-body">
                                    <table class="table table-striped table-bordered table-hover" id="sample_2">
                                    <thead>
                                    <tr>
                                        <th class="hidden">
                                             Hidden
                                        </th>
                                        <th>
                                        	Campus
                                        </th>
                                        <th>
                                             Device ID
                                        </th>
                                        <th>
                                             Action
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        $i=0;
                                        foreach($advertisement_sms_gateways as $sms):
                                    ?>
                                    <tr class="odd gradeX">
                                        <td class="hidden">
                                            <?php echo $i;?>
                                        </td>
                                        <td>
                                        	<?php 
											$campus = $this->db->get_where('campuses', array('campus_id'=>$sms['campus_id']))->result_array();
											if(count($campus)>0)
											{
												echo $campus[0]['campus_name'];
											}
											else
											{
												echo 'N/A';
											}
											?>
                                        </td>
                                        <td>
                                            <?php echo $sms['device_id'];?>
                                        </td>
                                        <td>
                                            <a href="<?php echo site_url().'/sms/edit_advertisement_sms_gateway/'.$sms['id'];?>" class="btn blue"><i class="fa fa-edit"></i></a>
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
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->