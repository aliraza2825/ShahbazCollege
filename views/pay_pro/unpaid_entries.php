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
                            <i class="fa fa-user"></i>PayPro Unpaid Entries
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
                                    Challans
                                </th>
                                <th>
                                    Paid Date
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
                                       <?php $payees = $this->db->group_by("paid_challans")->get_where("payments","paid_challans = '".$settlement['challan_ids']."'")->result_array();

                                            echo "Challan No : ".@$payees[0]['paid_challans'].'<br />';
                                            echo "Amount : ".@$payees[0]['actual_amount'];
                                       ?>
                                    </td>
                                    <td><?php  echo @$payees [0]['actual_paid_date']?></td>
									<td>
										<?php
											if(count($payees)==0):
										?>
										<a class="btn red" href="<?php echo site_url();?>/excel_import/manual_unpay/<?php echo $settlement['payment_id'];?>">Manual Unpaid</a>
										<?php
											endif;
										?>
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
                <!-- END SAMPLE FORM PORTLET-->
            </div>

        </div>

        <!-- END PAGE CONTENT-->
    </div>
</div>
<!-- END CONTENT -->

