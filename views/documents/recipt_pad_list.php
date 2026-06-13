
<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet box grey-cascade">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i> All Receipt Pad List
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover" id="sample_4">
                            <thead>
                            <tr>
                                <th >
                                    Sr.No
                                </th>
                                <th>
                                    Campus Name
                                </th>
                                <th>
                                    Campus Code
                                </th>
                                <th>
                                    Book Number
                                </th>
                                <th>
                                    Created By
                                </th>
                                <th>
                                    Created Data
                                </th>
                                <th>
                                    Action
                                </th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php
                            $i=1;
                            foreach($pad_list as $list):
                                ?>
                                <tr class="odd gradeX">
                                    <td >
                                        <?php echo $i;?>
                                    </td>
                                    <td>
                                        <?php echo $list['campus_name'];?>
                                    </td>
                                    <td>
                                        <?php echo $list['campus_code'];?>
                                    </td>
                                    <td>
                                        <?php echo $list['book'];?>
                                    </td>
                                    <td>
                                        <?php echo $list['first_name'];?> <?php echo $list['last_name'];?>
                                    </td>
                                    <td>
                                        <?php echo date('F d, Y', strtotime($list['created_at']));?>
                                    </td>
                                    <td>
                                        <a href="<?php echo site_url().'/documents/show_print_recipt/'.$list['id'];?>" class="btn blue"><i class="fa fa-eye" aria-hidden="true"></i>
                                        </a>
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