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
            <?php
                if(count($expenses)>0 ):
            ?>
                <div class="portlet box grey-cascade">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i> All Expenses
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
                                    Campus
                                </th>
                                <th>
                                    Title
                                </th>
                                <th>
                                    Purpose
                                </th>
                                <th>
                                    Amount
                                </th>
                                <th>
                                    Date
                                </th>
                                <th>
                                    Upload Date
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $i = 0;
                            foreach($expenses as $expense):
                                ?>
                                <tr class="odd gradeX">
                                    <td class="hidden">
                                        <?php echo $i;?>
                                    </td>
                                    <td>
                                        <?php echo $expense['campus_name']?>
                                    </td>
                                    <td>
                                        <?php echo $expense['title']?>
                                        <?php
                                        if($expense['expense_category_id']==1):
                                            ?>
                                            <br />
                                            Rickshaw Number : <?php echo $expense['rickshaw_number'];?>
                                            <br />
                                            Rickshaw Driver No : <?php echo $expense['driver_phone'];?>
                                        <?php
                                        endif;
                                        ?>
                                        <?php
                                        if($expense['expense_category_id']==13 && $expense['student_id']!=NULL):
                                            $student_data = $this->db->get_where('students',array('student_id'=>$expense['student_id']))->result_array();
                                            ?>
                                            Name : <?php echo $student_data[0]['first_name'];?> <?php echo $student_data[0]['last_name'];?> (<?php echo $student_data[0]['cnic'];?>)
                                            <br />
                                            Class : <?php echo $expense['class'];?> Year
                                            <br />
                                            Exam Number : <?php echo $expense['council_exam_no'];?>
                                        <?php
                                        endif;
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo $expense['purpose']?>
                                    </td>
                                    <td>
                                        <?php echo $expense['amount']?>
                                    </td>
                                    <td>
                                        <?php echo $expense['date']?>
                                    </td>
                                    <td>
                                        <?php echo $expense['actual_date']?>
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
            <?php
            endif;
            ?>
            </div>
		</div>
        <!-- END PAGE CONTENT-->
    </div>
</div>
<!-- END CONTENT -->