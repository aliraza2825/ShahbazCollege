<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
    <div class="page-content">
        <?php if(@$this->session->userdata('message')):?>
            <div class="alert alert-success">
                <button class="close" data-close="alert"></button>
                <span><?php echo $this->session->userdata('message');?></span>
            </div>
        <?php endif;?>
        <div class="row">
            <div class="col-md-12">
                <div class="portlet box grey-cascade">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-clock-o"></i>
                            Staff Timing (<?php echo $staff_type['staff_type_name']; ?>)
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/staff_type/save_staff_timing/<?php echo $staff_type['staff_type_id']; ?>">
                            <div class="form-body">
                                <div class="alert alert-info">
                                    OFF day ke liye timing fields mein <strong>00:00:00</strong> set karein. Salary calculation mein OFF ko present treat kiya jayega.
                                </div>
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
                                        <?php foreach($week_days as $day): ?>
                                            <?php $dayTiming = isset($timings[$day]) ? $timings[$day] : array(); ?>
                                            <tr>
                                                <td>
                                                    <?php echo $day; ?>
                                                    <input type="hidden" name="day[]" value="<?php echo $day; ?>" />
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="checkin_time[]" value="<?php echo isset($dayTiming['checkin_timing']) ? $dayTiming['checkin_timing'] : ''; ?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button"><i class="fa fa-clock-o"></i></button>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="checkout_time[]" value="<?php echo isset($dayTiming['checkout_timing']) ? $dayTiming['checkout_timing'] : ''; ?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button"><i class="fa fa-clock-o"></i></button>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="half_day_on[]" value="<?php echo isset($dayTiming['half_day_on']) ? $dayTiming['half_day_on'] : ''; ?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button"><i class="fa fa-clock-o"></i></button>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24" name="full_day_on[]" value="<?php echo isset($dayTiming['full_day_on']) ? $dayTiming['full_day_on'] : ''; ?>" required />
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button"><i class="fa fa-clock-o"></i></button>
                                                        </span>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <button type="submit" class="btn green">Save Timing</button>
                                        <a onclick="return confirm('Are you sure you want to delete timing for this staff type?')" href="<?php echo site_url();?>/staff_type/delete_staff_timing/<?php echo $staff_type['staff_type_id']; ?>" class="btn red">Delete Timing</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END CONTENT -->
