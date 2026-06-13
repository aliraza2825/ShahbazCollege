
<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
    <div class="page-content">


        <?php if(@$this->session->userdata('message')):?>
            <div class="alert alert-success">
                <button class="close" data-close="alert"></button>
                <span>
                    <?php echo $this->session->userdata('message');?> </span>
            </div>
        <?php endif;?>

<!--        <div class="row">-->
<!--            <div class="col-md-12">-->
<!--                --><?php
//                $comments = array();
//                $comments['classes'] = array();
//                $comments['campuses'] = array();
//                $comments['students'] = array();
//                $comments['contractors'] = array();
//
//                foreach($fee_dues_comments as $fee_dues_comment)
//                {
//                    if(in_array($fee_dues_comment['class_id'], $comments['classes']))
//                    {
//
//                    }
//                    else
//                    {
//                        $class_id = $fee_dues_comment['class_id'];
//                        $comments['classes'][$class_id]=0;
//                        $comments['students'][$class_id]=0;
//                    }
//                    if(in_array($fee_dues_comment['campus_id'], $comments['campuses']))
//                    {
//
//                    }
//                    else
//                    {
//                        $campus_id = $fee_dues_comment['campus_id'];
//                        $comments['campuses'][$campus_id]=0;
//                        $comments['contractors'][$campus_id]=0;
//                    }
//                }
//                foreach($fee_dues_comments as $fee_dues_comment)
//                {
//                    $class_id = $fee_dues_comment['class_id'];
//                    $comments['classes'][$class_id]=$comments['classes'][$class_id]+1;
//                }
//                foreach($contracts_fee_dues_comments as $contracts_fee_dues_comment)
//                {
//                    $campus_id = $contracts_fee_dues_comment['campus_id'];
//                    $comments['campuses'][$campus_id]=$comments['campuses'][$campus_id]+1;
//                }
//
//                $students_ids = array();
//                foreach($fee_dues_comments as $fee_dues_comment)
//                {
//                    $class_id = $fee_dues_comment['class_id'];
//                    $student_id = $fee_dues_comment['student_id'];
//                    array_push($students_ids,$student_id);
//                    $comments['students'][$class_id]=count(array_unique($students_ids));
//                }
//
//                $contractors_ids = array();
//                foreach($contracts_fee_dues_comments as $contracts_fee_dues_comment)
//                {
//                    $campus_id = $contracts_fee_dues_comment['campus_id'];
//                    $contractor_id = $contracts_fee_dues_comment['contractor_id'];
//                    array_push($contractors_ids,$contractor_id);
//                    $comments['contractors'][$campus_id]=count(array_unique($contractors_ids));
//                }
//
//
//                ?>
<!--                <!-- BEGIN EXAMPLE TABLE PORTLET-->-->
<!--                <div class="portlet box grey-cascade">-->
<!--                    <div class="portlet-title">-->
<!--                        <div class="caption">-->
<!--                            <i class="fa fa-list"></i> Students Due Fees Status-->
<!--                        </div>-->
<!--                    </div>-->
<!--                    <div class="portlet-body">-->
<!--                        <table class="table table-bordered table-hover" id="sample_2">-->
<!--                            <thead>-->
<!--                            <tr>-->
<!--                                <th class="hidden">-->
<!--                                    Hidden-->
<!--                                </th>-->
<!--                                <th>-->
<!--                                    Campus / Class Name-->
<!--                                </th>-->
<!--                                <th>-->
<!--                                    Total Students-->
<!--                                </th>-->
<!--                                <th>-->
<!--                                    Total Contractors-->
<!--                                </th>-->
<!--                                <th>-->
<!--                                    Total Fee Entries-->
<!--                                </th>-->
<!--                            </tr>-->
<!--                            </thead>-->
<!--                            <tbody>-->
<!--                            --><?php
//                            $classes_comments = $comments['classes'];
//                            $i=1;
//                            foreach($classes_comments as $k=>$v):
//                                ?>
<!--                                <tr>-->
<!--                                    <td class="hidden">-->
<!--                                        --><?php //echo $i;?>
<!--                                    </td>-->
<!--                                    <td>-->
<!--                                        --><?php //echo $this->db->get_where('classes',array('class_id'=>$k))->row()->name;?>
<!--                                    </td>-->
<!--                                    <td>-->
<!--                                        --><?php //echo $comments['students'][$k];?>
<!--                                    </td>-->
<!--                                    <td>-->
<!--                                        N/A-->
<!--                                    </td>-->
<!--                                    <td>-->
<!--                                        --><?php //echo $v;?>
<!--                                    </td>-->
<!--                                </tr>-->
<!--                                --><?php
//                                $i++;
//                            endforeach;
//                            ?>
<!--                            --><?php
//                            $campuses_comments = $comments['campuses'];
//                            $i=1;
//                            foreach($campuses_comments as $k=>$v):
//                                ?>
<!--                                <tr>-->
<!--                                    <td class="hidden">-->
<!--                                        --><?php //echo $i;?>
<!--                                    </td>-->
<!--                                    <td>-->
<!--                                        --><?php //echo $this->db->get_where('campuses',array('campus_id'=>$k))->row()->campus_name;?>
<!--                                    </td>-->
<!--                                    <td>-->
<!--                                        N/A-->
<!--                                    </td>-->
<!--                                    <td>-->
<!--                                        --><?php //echo $comments['contractors'][$k];?>
<!--                                    </td>-->
<!--                                    <td>-->
<!--                                        --><?php //echo $v;?>
<!--                                    </td>-->
<!--                                </tr>-->
<!--                                --><?php
//                                $i++;
//                            endforeach;
//                            ?>
<!--                            </tbody>-->
<!--                        </table>-->
<!--                    </div>-->
<!--                </div>-->
<!--                <!-- END EXAMPLE TABLE PORTLET-->-->
<!--                -->
<!--            </div>-->
<!--        </div>-->

        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet box grey-cascade">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i>Students Due Fees
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-bordered table-hover" id="sample_10">
                            <thead>
                            <tr>
                                <th class="hidden">
                                    Hidden
                                </th>


                                <th>

                                    Student Details

                                </th>

                                <th>
                                    Fees
                                </th>
                                <th>
                                    Remaining Dues
                                </th>
                                <th>
                                    Last Date
                                </th>
                                <th>
                                    Result Remarks
                                </th>
                                <th>
                                    Add Comment
                                </th>
                                <th>
                                    Manual Remarks
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $i=0;
                            foreach($fee_dues_comments as $due):
                                $class = '';
                                if(date('Y-m-d')<= $due['dead_line'])
                                {
                                    $class = 'alert alert-success';
                                }
                                else
                                {
                                    $class = 'alert alert-danger';
                                }
                                ?>
                                <tr class="<?php echo $class;?>">
                                    <td class="hidden">
                                        <?php echo $i;?>
                                    </td>
                                    <td>
                                        Campus : <?php echo $due['campus_name'];?>
                                        <br />
                                        Class : <?php echo $due['class_name'];?>
                                        <br />
                                        Student Name : <span class="bold"><?php echo $due['first_name'].' '.$due['last_name'];?></span>
                                        <br />
                                        CNIC : <?php echo $due['cnic'];?>
                                        <br />
                                        Roll # : <span class="bold"><?php echo $due['roll_no'];?></span>
                                        <br />
                                        Mobile : <span class="bold"><?php echo $due['mobile'];?> - <?php echo $due['emergency_no'];?></span>
                                    </td>
                                    <td>
                                        <?php echo $due['amount'];?>
                                    </td>
                                    <td>
                                        <?php echo $due['extra_amount'];?>
                                    </td>
                                    <td>
                                        <?php echo date('d F, Y', strtotime($due['dead_line']));?>
                                    </td>
                                    <td>
                                        <?php getStudentResultRemarks($due['cnic']);?>
                                    </td>
                                    <td>
                                        <!--                                    --><?php
                                        //                                    	$fee_pending = $this->db->get_where('fees_remarks', array('fee_id'=>$due['fee_id'],'clear_status'=>0))->result_array();
                                        //										if(count($fee_pending)<1):
                                        //									?>
                                        <a data-toggle="modal" data-id="<?php echo $due['fee_id'] ?>" title="Add this item" class="open-AddBookDialog btn btn-primary" href="#insertcomment">
                                            <i class="fa fa-edit"> Add Comments</i>
                                        </a>

                                        <!--                                        --><?php
                                        //										else:
                                        //									?>
                                        <!--									    Approval Pending.-->
                                        <!--                                    --><?php
                                        //                                    	endif;
                                        //									?>
                                    </td>
                                    <td>
                                        <div class="fee_<?php echo $due['fee_id'];?>">
                                            <?php
                                            $remarks = $this->db->get_where('fees_remarks', array('fee_id'=>$due['fee_id']))->result_array();
                                            foreach($remarks as $remark):
                                                ?>
                                                <?php
                                                echo '<p>'.@$remark['comment'].' ('.@$remark['paid_on_date'].') ('.@$remark['add_by'].' on '.@date('d M, Y H:i:s A',strtotime(@$remark['date'])).')</p>';
                                                ?>
                                            <?php
                                            endforeach;
                                            ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                                $i++;
                            endforeach;
                            ?>

                            <?php
                            $i=0;
                            foreach($contracts_fee_dues_comments as $due):
                                $class = '';
                                if(date('Y-m-d')<= $due['dead_line'])
                                {
                                    $class = 'alert alert-success';
                                }
                                else
                                {
                                    $class = 'alert alert-danger';
                                }
                                ?>
                                <tr class="<?php echo $class;?>">
                                    <td class="hidden">
                                        <?php echo $i;?>
                                    </td>
                                    <td>
                                        <?php echo $due['campus_name']?>
                                    </td>
                                    <td>
                                        <?php echo 'N/A';?>
                                    </td>
                                    <td>
                                        <?php echo $due['contractor_id_from_college']?>
                                    </td>
                                    <td>
                                        <?php echo $due['name'];?>
                                    </td>
                                    <td>
                                        <?php echo $due['mobile'];?> <hr /> <?php echo $due['emergency_no'];?>
                                    </td>
                                    <td>
                                        <?php echo $due['amount'];?>
                                    </td>
                                    <td>
                                        <?php echo $due['extra_amount'];?>
                                    </td>
                                    <td>
                                        <?php echo date('d F, Y', strtotime($due['dead_line']));?>
                                    </td>
                                    <td>
                                        <?php getStudentResultRemarks($due['cnic']);?>
                                    </td>
                                    <td>
                                        <!--                                    -->
                                        //                                    	$fee_pending = $this->db->get_where('fees_remarks', array('fee_id'=>$due['fee_id'],'clear_status'=>0))->result_array();
                                        //										if(count($fee_pending)<1):
                                        //
                                        <form action="#" name="fees_form">
                                            <label>Comment</label>
                                            <input class="form-control comment_box comment-<?php echo $due['fee_id'];?>" type="text" name="comment-<?php echo $due['fee_id'];?>" value="" />
                                            <label>Next Due Date</label>
                                            <div class="input-group input-small date date-picker" data-date="<?php echo $due['dead_line'];?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                <input type="text" name="date_of_birth" class="form-control selected-date-<?php echo $due['fee_id'];?>" value="<?php echo $due['dead_line'];?>" readonly>
                                                <span class="input-group-btn">
                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                        </span>
                                            </div>
                                            <button type="button" class="btn green submit_fee_dues_comment" data-fee-id="<?php echo $due['fee_id'];?>">Submit</button>
                                        </form>
                                        <!--									--><?php
                                        //										else:
                                        //									?>
                                        <!--									Approval Pending.-->
                                        <!--                                    --><?php
                                        //                                    	endif;
                                        //									?>
                                    </td>
                                    <td>
                                        <div class="fee_<?php echo $due['fee_id'];?>">
                                            <?php
                                            $remarks = $this->db->get_where('fees_remarks', array('fee_id'=>$due['fee_id']))->result_array();
                                            foreach($remarks as $remark):
                                                ?>
                                                <?php
                                                echo '<p>'.@$remark['comment'].' ('.@$remark['paid_on_date'].') ('.@$remark['add_by'].' on '.@date('d M, Y H:i:s A',strtotime(@$remark['date'])).')</p>';
                                                ?>
                                            <?php
                                            endforeach;
                                            ?>
                                        </div>
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

        <!-- END PAGE CONTENT-->
    </div>
</div>
