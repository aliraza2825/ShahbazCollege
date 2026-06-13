
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
                            <i class="fa fa-list"></i> Create Council List Print
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/council_list/print_councel">
                            <div class="form-body">
                                <div class="form-group">
                                        <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control campus" name="campus_id">
                                                <option value="">ALL CAMPUS</option>
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
                                        <label class="col-md-3 control-label">Course <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control course_id" name="course_id">
                                                <option value="">ALL COURSE</option>
												<?php
													foreach($courses as $course):
												?>
                                                <option value="<?php echo $course['course_id'];?>"><?php echo $course['course_name'];?></option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                        </div>
                                    </div>
							
									<div class="class">
										<div class="form-group">
											<label class="col-md-3 control-label">Class <span class="required">*</span></label>
											<div class="col-md-5">
												<select class="form-control classes" name="class_id">
												</select>
												<!--<span class="help-inline"></span>-->
											</div>
										</div>
								</div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <button type="submit" class="btn green">Create List</button>
                                        <button onclick="location.href = '<?php echo site_url();?>'" type="button" class="btn default">Cancel</button>

                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- END SAMPLE FORM PORTLET-->
            </div>
        </div>
        <!-- END PAGE CONTENT-->
        <?php

        if(isset($result)){
        ?>
        <div class="row" style="margin-bottom: 10px">
            <div class="col-md-12">
<!--                <button  class="btn green">Print</button>-->
<!--                <button onclick="printDiv('printMe')">Print only the above div</button>-->
                <a href="<?php echo site_url();?>/council_list/get_print_of_concel_list/<?php echo $campus_id;?>/<?php echo $class_id;?>" target="_blank" class="btn green">Print</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet box grey-cascade">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i> All Students
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover" id="printMe">
                            <thead>
                            <tr>
                                <th >
                                    Sr.No
                                </th>
                                <th>
                                    Roll #
                                </th>
                                <th>
                                    CNIC No
                                </th>
                                <th>
                                    Name & Father Name
                                </th>
                                <th>
                                    Postal Address
                                </th>
                                <th>
                                    Student Mobile Number
                                </th>
                                <th>
                                    Board Name
                                </th>
                                <th>
                                    Institute Contact Number
                                </th>
                                <th>
                                    Action
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $i=1;
                            foreach($result as $list):
                                if($list['gender']=='Male')
                                {
                                    $name = ucfirst(strtolower($list['first_name'])).' '.ucfirst(strtolower($list['last_name'])).'<br />S/O '.ucfirst(strtolower($list['father_name']));
                                }
                                else
                                {
                                    $name = ucfirst(strtolower($list['first_name'])).' '.ucfirst(strtolower($list['last_name'])).'<br />D/O '.ucfirst(strtolower($list['father_name']));
                                }
                                ?>
                                <tr class="odd gradeX">
                                    <td >
                                        <?php echo $i;?>
                                    </td>
                                    <td>
                                        <?php echo $list['roll_no'];?>
                                    </td>
                                    <td>
                                        <?php echo $list['cnic'];?>
                                    </td>
                                    <td>
                                        <?php echo $name;?>
                                    </td>
                                    <td>
                                        <?php echo $list['address'];?>
                                    </td>
                                    <td>
                                        <?php echo $list['mobile'];?>
                                    </td>
                                    <td>
                                        <?php echo $list['board'];?>
                                    </td>
                                    <td>
                                        <?php echo $list['institute'];?>
                                    </td>
                                    <td>
                                        <input type="checkbox" class="selection" name="selection" value="<?php echo $list['student_id'];?>" />
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

        <?php } ?>
    </div>
</div>

<!-- END CONTENT -->