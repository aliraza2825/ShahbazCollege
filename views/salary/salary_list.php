
<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-6" style="background-color: white;padding: 20px 30px;    border-radius: 20px!important;box-shadow: 10px 10px 5px -8px rgba(0,0,0,0.75);">
                <h3 style="margin:5px 0px 20px 0px;text-align: center;font-weight: bold">Select Campus To Generate Salary</h3>
                <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/salary/salary_list">
                    <div class="form-body">
                        <div class="form-group">
                            <label class="col-md-2 control-label">Campus <span class="required">*</span></label>
                            <div class="col-md-8">
                                <select class="form-control campus" name="campus_id">
                                    <option value="">Select CAMPUS</option>
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
                            <label class="control-label col-md-2">Select Month</label>
                            <div class="col-md-8">
                                <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                    <input type="text" name="date" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
                                    <span class="input-group-btn">
                                    <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-12" style="text-align: center">
                                <button type="submit" class="btn green">Check</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php if(@$staff != ""){?>

        <div class="row" style="margin-top: 20px">
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet box grey-cascade">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i> All Staff Salary List
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover" id="">
                            <thead>
                            <tr>
                                <th>
                                    Staff.No
                                </th>
                                <th>
                                    Name
                                </th>
<!--                                <th>-->
<!--                                    Designation-->
<!--                                </th>-->
                                <th>
                                    Collage
                                </th>
                                <th>
                                    Mobile
                                </th>
                                <th>
                                    Salary
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
                            foreach($staff as $list):
                                ?>
                                <tr class="odd gradeX">
                                    <td >
                                        <?php echo $i;?>
                                    </td>
                                    <td>
                                        <?php echo $list['first_name'];?> <?php echo $list['last_name'];?>
                                    </td>
                                    <td>
                                        <?php echo $list['campus_name'];?>
                                    </td>
                                    <td>
                                        <?php echo $list['mobile'];?>
                                    </td>
                                    <td>
                                        <?php echo $list['count'];?>
                                    </td>
                                    <td>
                                        <?php if ($list['count']==''): ?>
                                        <button class="btn red"><i class="fa fa-info" aria-hidden="true"></i>
                                            Not Generated </button>

                                        <?php else: ?>
<!--                                            <a class="btn green" href="--><?php //echo site_url().'/salary/salary_view/'.$list['user_id'];?><!--">View</a>-->
                                            <a href="<?php echo site_url().'/salary/salary_view/'.$list['user_id'].'/'.$month.'/'.$year;?>" class="btn green"><i class="fa fa-info" aria-hidden="true"></i>
                                                Salary Generated </a>

                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($list['count']==''): ?>
                                        <a href="<?php echo site_url().'/salary/generate_salary/'.$list['user_id'].'/'.$campus_id.'/'.$month.'/'.$year?>" class="btn blue"> <i class="fa fa-money" aria-hidden="true"></i>
                                             Generate Salary</a>
                                        <?php endif; ?>
                                    </td>
									<td>
                                        <?php if ($list['count']!=''): ?>
                                        <a href="<?php echo site_url().'/salary/delete_salary/'.$list['user_id'].'/'.$month.'/'.$year?>" class="btn blue"> <i class="fa fa-trash" aria-hidden="true"></i>
                                             Delete Salary</a>
                                        <?php endif; ?>
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