
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Add Class <small>add your desire class</small>
			</h3>-->
			<!-- END PAGE HEADER-->
			<!-- BEGIN PAGE CONTENT-->
            <?php
                $count = 0;

            if(@$this->session->userdata('message')):?>
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
                                <i class="fa fa-plus" aria-hidden="true"></i>
                                Add Required Career
                            </div>
                        </div>
                        <div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/mobile_app/insert_required_career"  enctype="multipart/form-data">
                                <div class="form-body row">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Required Career <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="text" name="required_career" class="form-control input-inline input-large" value="" required/>
                                        </div>
                                    </div>    
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Status <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="status" class="form-control input-inline input-large" required>
                                                <option value="">Select Status</option>
                                                <option value="1" selected>Active</option>
                                                <option value="0">Deactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button  type="submit" class="btn green">Add</button>
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
                                <i class="fa fa-list"></i> All Required Careers
                            </div>
                        </div>
                        <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover" id="sample_2">
                                <thead>
                                    <tr>
                                        <th class="hidden">
                                            hidden
                                        </th>
                                        <th>
                                            Sr.No
                                        </th>
                                        <th>
                                            Required Career Type
                                        </th>
                                        <th>
                                            Status
                                        </th>
                                        <th>
                                            Action
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>

                                <?php
                                $i=1;
                                foreach($required_careers as $required_career):
                                ?>
                                <tr class="odd gradeX">
                                    <td class="hidden">
                                        <?php echo $i;?>
                                    </td>
                                    <td>
                                        <?php echo $i;?>
                                    </td>
									<td>
                                        <?php echo $required_career['required_career'];?>
                                    </td>
                                    <td>
                                        <?php 
                                            if($required_career['status']==1)
                                            {
                                                echo '<button class="btn green" type="button">Active</button>';
                                            }
                                            else
                                            {
                                                echo '<button class="btn red" type="button">Deactive</button>';
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo site_url().'/mobile_app/edit_required_career/'.$required_career['required_career_id'];?>" class="btn blue"><i class="fa fa-edit"></i></a>    
                                        <a href="<?php echo site_url().'/mobile_app/delete_required_career/'.$required_career['required_career_id'];?>" class="btn red"><i class="fa fa-trash"></i></a>
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
<!-- END CONTENT -->