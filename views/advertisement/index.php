
<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
    <div class="page-content">

        <?php

        if(isset($edit_device)){
        ?>

        <div class="row">
            <div class="col-md-12 ">
                <!-- BEGIN SAMPLE FORM PORTLET-->
                <div class="portlet box green ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-mobile" aria-hidden="true"></i>
                             Edit Advertisement Devices
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/AdvertisementDevices/update_device">
                            <input type="hidden" name="id" value="<?php echo $edit_device->id; ?>">
                            <div class="form-body row">
                                <div class="col-md-4 form-group ">
                                    <label class="col-md-3 control-label">Device ID :<span class="required">*</span></label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control input-inline input-medium" name="device_id" placeholder="Enter Device ID" value="<?php echo $edit_device->device_id; ?>" required>
                                        <span class="help-inline"></span>
                                    </div>
                                </div>
                                <div class=" col-md-4 form-group">
                                    <label class="col-md-3 control-label">Device No :<span class="required">*</span></label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control input-inline input-medium" name="device_no" placeholder="Enter Device Number" value="<?php echo $edit_device->device_no; ?>">
                                        <span class="help-inline"></span>
                                    </div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="col-md-3 control-label">Mobile No :<span class="required">*</span> </label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control input-inline input-medium" name="mobile_no" placeholder="Enter Mobile Number" value="<?php echo $edit_device->mobile_no; ?>">
                                        <span class="help-inline"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-9 col-md-3" style="text-align: right">
                                        <button type="submit" class="btn green">Update</button>
                                        <a href="index.php" type="submit" class="btn red">Cancle</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
                <!-- END SAMPLE FORM PORTLET-->
            </div>
        </div>
        <?php }else{ ?>
        <div class="row">
            <div class="col-md-12 ">
                <!-- BEGIN SAMPLE FORM PORTLET-->
                <div class="portlet box green ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-mobile" aria-hidden="true"></i>
                            Add Advertisement Devices
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/AdvertisementDevices/add_device">
                            <div class="form-body row">
                                <div class="col-md-4 form-group ">
                                    <label class="col-md-3 control-label">Device ID :<span class="required">*</span></label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control input-inline input-medium" name="device_id" placeholder="Enter Device ID" value="" required>
                                        <span class="help-inline"></span>
                                    </div>
                                </div>
                                <div class=" col-md-4 form-group">
                                    <label class="col-md-3 control-label">Device No :<span class="required">*</span></label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control input-inline input-medium" name="device_no" placeholder="Enter Device Number" value="">
                                        <span class="help-inline"></span>
                                    </div>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="col-md-3 control-label">Mobile No :<span class="required">*</span> </label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control input-inline input-medium" name="mobile_no" placeholder="Enter Mobile Number" value="">
                                        <span class="help-inline"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-9 col-md-3" style="text-align: right">
                                        <button type="submit" class="btn green">Add</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
                <!-- END SAMPLE FORM PORTLET-->
            </div>
        </div>
        <?php } ?>

        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet box grey-cascade">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i> All Advertisement List
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover" id="sample_2">
                            <thead>
                            <tr>
                                <th class="hidden">
                                    Sr.No
                                </th>
                                <th>
                                    Device ID
                                </th>
                                <th>
                                    Device Number
                                </th>
                                <th>
                                    Mobile Number
                                </th>
                                <th>
                                    Send SMS
                                </th>
                                <th>
                                    Difference In
                                </th>
                                <th>
                                    Remaining SMS
                                </th>
                                <th>
                                    Battery Level
                                </th>
                                <th>
                                    Status
                                </th>
                                <th>
                                    Date
                                </th>
                                <th>
                                    Action
                                </th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php
                            $i=0;
                            foreach($devices_list as $list):
                                ?>
                                <tr class="odd gradeX">
                                    <td class="hidden">
                                        <?php echo $i;?>
                                    </td>
                                    <td>
                                        <?php echo $list['device_id'];?>
                                    </td>
                                    <td>
                                        <?php echo $list['device_no'];?>
                                    </td>
                                    <td>
                                        <?php echo $list['mobile_no'];?>
                                    </td>
                                    <td>
                                        <?php echo $list['send_sms'];?>
                                    </td>
                                    <td>
                                        <?php echo $list['differnce_in'];?>
                                    </td>
                                    <td>
                                        <?php echo $list['remaining_sms'];?>
                                    </td>
                                    <td>
                                        <?php echo $list['battery_level'];?>
                                    </td>
                                    <td>
                                        <?php echo $list['status'];?>
                                    </td>
                                    <td>
                                        <?php echo date('F d, Y', strtotime($list['created_at']));?>
                                    </td>
                                    <td>
                                        <a href="<?php echo site_url().'/AdvertisementDevices/edit_device/'.$list['id'];?>" class="btn blue"><i class="fa fa-edit"></i></a>
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