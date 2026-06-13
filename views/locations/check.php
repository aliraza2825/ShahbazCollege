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
								<i class="fa fa-map-marker"></i> Check Location
							</div>
						</div>
                        <div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/locations/check">
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
											<button type="submit" class="btn green">Check Locations</button>
										</div>
									</div>
								</div>
                            </form>
                        </div>    
                        <?php
							if(count($locations)>0 && $this->input->post('from_date') && $this->input->post('to_date')):
						?>
						<div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
								<th class="hidden">
                                	 hidden
                                </th>
								<th>
									 User Name
								</th>
                                <th>
									 Date / Time
								</th>
                                <th>
									 Location
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($locations as $location):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
								<td>
									<?php echo $location['first_name'].' '.$location['last_name'];?>
								</td>
                                <td>
									<?php echo $location['date'];?>
								</td>
                                <td>
									<a href="<?php echo $location['url'];?>" class="btn green" target="_blank">View Location</a>
								</td>
							</tr>
                            <?php
								$i++;
                            	endforeach;
							?>
							</tbody>
							</table>
						</div>
                        <?php
                        	endif;
						?>
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->