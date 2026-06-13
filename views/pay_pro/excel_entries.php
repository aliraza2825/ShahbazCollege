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
                            <i class="fa fa-user"></i>PayPro Settlements
                        </div>
                    </div>
                    <div class="portlet-body table-responsive">
                        <table class="table table-bordered table-hover" id="sample_ali">
                            <thead>
                            <tr>
                                <th class="hidden">
                                    Hidden
                                </th>
                                <th>
                                    Student
                                </th>
                                <th>
                                    Challan
                                </th>
                                <th>
                                    PayPro Challan No
                                </th>
                                <th>
                                    Paid Date
                                </th>
                                <th>
                                    Received Date
                                </th>
                                <th>
                                    Paid Amount
                                </th>
                                <th>
                                    Received Amount
                                </th>
                                <th>
                                    Expense Amount
                                </th>
                                <th>
                                    Paid Via
                                </th>
                                <th>
                                    Paid After Days
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $i=0;
                            foreach($settlements as $settlement):
                                ?>
                                <tr class="odd gradeX">
                                    <td class="hidden">
                                        <?php echo $i;?>
                                    </td>
                                    <td>
                                        Campus : <?php echo $settlement['campus_name'];?>
                                        <br />
                                        Course : <?php echo $settlement['course_name'];?>
                                        <br />
                                        Session : <span class="bold"><?php echo $settlement['session'];?></span>
                                        <br />
                                        Class : <?php echo $settlement['name'];?>
                                        <br />
                                        Registration Date : <span class="bold"><?php echo $settlement['registration_date'];?></span>
                                        <br />
                                        Student Name : <span class="bold"><?php echo $settlement['first_name'].' '.$settlement['last_name'];?></span>
                                        <br />
                                        CNIC : <?php echo $settlement['cnic'];?>
                                        <br />
                                        Father Name : <?php echo $settlement['father_name'];?>
                                        <br />
                                        Roll # : <span class="bold"><?php echo $settlement['roll_no'];?></span>
                                        <br />
                                        Mobile : <span class="bold"><?php echo $settlement['mobile'];?> - <?php echo $settlement['emergency_no'];?></span>
                                    </td>
                                    <td>
                                        <?php

                                            ?>
                                            Paid Campus : <?php echo @$this->db->get_where('campuses',array('campus_id'=>$settlement['submitted_fee_campus_id']))->row()->campus_name;?>
                                            <br />
                                            Paid Amount : <?php echo $settlement['actual_amount'];?>
                                            <br />
                                            <?php
                                            if($settlement['shifted_installment']>0):
                                                ?>
                                                Shifted Previous Installment Amount : <?php echo $settlement['shifted_installment'];?>
                                                <br />
                                            <?php
                                            endif;
                                            ?>
                                            <?php
                                            if($settlement['shifted_previous_fine']>0):
                                                ?>
                                                Shifted Previous Installment Fine : <?php echo $settlement['shifted_previous_fine'];?>
                                                <br />
                                            <?php
                                            endif;
                                            ?>
                                            <?php
                                            if($settlement['shifted_fine']>0):
                                                ?>
                                                Shifted Current Installment Fine : <?php echo $settlement['shifted_fine'];?>
                                                <br />
                                            <?php
                                            endif;
                                            ?>
                                            <?php
                                            if($settlement['removed_previous_fine']>0):
                                                ?>
                                                Removed Previous Installment Fine : <?php echo $settlement['removed_previous_fine'];?>
                                                <br />
                                            <?php
                                            endif;
                                            ?>
                                            <?php
                                            if($settlement['removed_fine']>0):
                                                ?>
                                                Removed Current Installment Fine : <?php echo $settlement['removed_fine'];?>
                                                <br />
                                            <?php
                                            endif;
                                            ?>
                                            Challan No : <?php echo $settlement['challan_no'];?>
                                            <br />
                                            Paid Date : <?php echo $settlement['paid_date'];?>
                                            <br />
                                            Paid Date System : <?php echo $settlement['updated_at'];?>
                                            <br />
                                            Fee Pay Through : <?php echo $settlement['fee_pay_through'];?>
                                            <br />
                                            <?php
                                            if($settlement['fee_pay_through']=='bank'):
                                                ?>
                                                Bank : <?php echo $settlement['bank_details'];?>
                                                <br />
                                                Bank Challan / TID No. : <?php echo $settlement['tid_no'];?>
                                                <br />

                                                Merged against Challan. : <?php echo $settlement['paid_challans'];?>
                                            <?php
                                            endif;
                                            ?>
                                            <?php
                                            if($settlement['fee_pay_through']=='college' && $settlement['fee_submit_type']=='receipt_book'):
                                                ?>
                                                Pad of : <?php echo @$this->db->get_where('campuses',array('campus_id'=>$settlement['submitted_fee_campus_id']))->row()->campus_name;?>
                                                <br />
                                                Book No. : <?php echo $settlement['book_no'];?>
                                                <br />
                                                Receipt No. : <?php echo $settlement['receipt_no'];?>
                                                <br />
                                                Merged against Challan. : <?php echo $settlement['paid_challans'];?>
                                            <?php
                                            endif;
                                            ?>
                                            <?php
                                            if($settlement['fee_pay_through']=='college' && $settlement['fee_submit_type']=='computer_challan'):
                                                ?>
                                                Pay by : Computer Challan
                                                <br />
                                                Merged against Challan. : <?php echo $settlement['paid_challans'];?>
                                            <?php
                                            endif;
                                            ?>
                                            <div class="clearfix"></div>
                                            <br />
                                            <?php
                                            if($settlement['scan_challan']=='')
                                            {

                                            }
                                            elseif($settlement['scan_challan']!='' )
                                            {
                                                echo '<a href="'.base_url().'uploads/'.$settlement['scan_challan'].'" target="_blank" class="btn purple pull-left">See Challan</a> <br />';
                                            }

                                            if($settlement['fee_pay_through']=='college' && $settlement['fee_submit_type']=='computer_challan')
                                            {
                                                echo '<a href="'.site_url().'/students/print_college_challan/'.$settlement['id'].'" target="_blank" class="btn blue college_fee_'.$i.'"><i class="fa fa-print"></i> See Challan</a> <br />';
                                            }
                                            ?>
                                            <?php
                                            if($settlement['fine_application']=='' && $settlement['paid']==0)
                                            {

                                            }
                                            else if($settlement['fine_application']!='' && $settlement['paid']==1)
                                            {
                                                echo '<a href="'.base_url().'uploads/'.$settlement['fine_application'].'" target="_blank" class="btn purple pull-left">See Application</a> <br />';
                                            }
                                            else
                                            {

                                            }
                                            ?>
                                            <div class="clearfix"></div>
                                        <?php
                                        ?>
                                    </td>
                                    <td><?php  echo $settlement ['order_no']        ?></td>
                                    <td><?php  echo $settlement ['paid_date']       ?></td>
                                    <td><?php  echo $settlement ['received_date']   ?></td>
                                    <td><?php  echo $settlement ['paid_amount']     ?></td>
                                    <td><?php  echo $settlement ['received_amount'] ?></td>
                                    <td><?php  echo $settlement ['paid_amount'] - $settlement ['received_amount'] ?></td>
                                    <td><?php  echo $settlement ['paid_via']        ?></td>
                                    <td><?php  echo $settlement ['paid_after_days'] ?></td>
                                </tr>
                                <?php
                                $i++;
                            endforeach;
                            ?>
                            </tbody>
                        </table>
                    </div>


                </div>
                <!-- END SAMPLE FORM PORTLET-->
            </div>

        </div>

        <!-- END PAGE CONTENT-->
    </div>
</div>
<!-- END CONTENT -->

