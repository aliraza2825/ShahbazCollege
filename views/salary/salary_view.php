<!DOCTYPE html>
<html lang="en">
<head>
    <title>Bootstrap Example</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"  media="screen, projection">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>

    <style>
        .container{
            margin:0 auto;
            /*height:1132px;*/
            width:800px;
            padding:20px;
        }
        .body{
            width:688px;
            background-image: url('<?php echo base_url();?>print_images/council.png');
            background-repeat: no-repeat;
            background-position: top center;
            background-size:20%;
            padding:1% 5% 5% 5%;
        }
        @media print {
            .col-md-1,.col-md-2,.col-md-3,.col-md-4,
            .col-md-5,.col-md-6,.col-md-7,.col-md-8,
            .col-md-9,.col-md-10,.col-md-11,.col-md-12 {
                float: left;
            }

            .col-md-1 {
                width: 8%;
            }
            .col-md-2 {
                width: 16%;
            }
            .col-md-3 {
                width: 25%;
            }
            .col-md-4 {
                width: 33%;
            }
            .col-md-5 {
                width: 42%;
            }
            .col-md-6 {
                width: 50%;
            }
            .col-md-7 {
                width: 58%;
            }
            .col-md-8 {
                width: 66%;
            }
            .col-md-9 {
                width: 75%;
            }
            .col-md-10 {
                width: 83%;
            }
            .col-md-11 {
                width: 92%;
            }
            .col-md-12 {
                width: 100%;
            }

            .table tbody td {
                padding: 10px 15px 10px 10px!important;
            }


            .upper-case{
                text-transform: uppercase!important;
            }
            .table tbody td {

                font-size: 14px!important;
                text-transform: uppercase!important;
                border: 1px solid #ddd;
            }

            /*table tr td{*/
            /*    padding: -5px!important;*/
            /*    margin: -5px!important;*/
            /*}*/
            /*tr{*/
            /*    padding: -5px!important;*/
            /*    margin: -5px!important;*/
            /*}*/
            /*ul li{*/
            /*    font-size: 12px;*/
            /*}*/
            /*ol li{*/
            /*    font-size: 12px;*/
            /*}*/
        }
    </style>
</head>
<body>
<?php foreach ($sal as $salary){
    ?>

    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 upper-case" style="background-color:white; 20px;" id="pdf">
                <div class="row">
                    <div class="col-md-12" id="logo" style="text-align: center;">
                        <?php $campus = $this->db->get_where('campuses','campus_id = "'.$salary['campus_id'].'"')->row(); ?>
                        <img src="<?php echo base_url('uploads/'.$campus->logo); ?>" style="width:120px!important;">
                    </div>
                </div>
                <div class="row" style="background:white;padding-bottom:20px">
                    <div class="col-md-12">
                        <div style="min-height:850px">
                            <table class="table table-bordered" text-align="center" style="padding-top: 0; border-spacing: 0;background:white;">
                                <thead>
                                <tr style="text-align: center;">
                                    <td class="primary-color" colspan="5" style="width: 5%; text-align: center !important; font-weight: bold; border-bottom: 1px solid #ddd;">
                                        Salary Slip OF <?php echo $salary['payroll_month']?>  <?php echo $salary['payroll_year']?>
                                    </td>
                                </tr>
                                </thead>

                                <tbody class="table_body" style="background:white;">

                                <tr>
                                    <td style="padding-left: 20px;" class="primary-color"><strong>Employee ID</strong></td>
                                    <td style="padding-left: 20px;" class="primary-color"><?php echo $salary['user_id']?></td>
                                    <td style="padding-left: 20px;" class="primary-color"><strong>Employee CNIC</strong></td>
                                    <td style="padding-left: 20px;" colspan="2" class="primary-color"><?php echo $salary['cnic']?></td>
                                </tr>

                                <tr>
                                    <td style="padding-left: 20px; " class="primary-color"><strong>Employee Name</strong></td>
                                    <td style="padding-left: 20px;" class="primary-color"><?php echo $salary['first_name']?> <?php echo $salary['last_name']?></td>
                                    <td style="padding-left: 20px;" class="primary-color"><strong>Mobile No:</strong></td>
                                    <td style="padding-left: 20px;" colspan="2" class="primary-color"><?php echo $salary['mobile']?></td>
                                </tr>

                                <tr>
                                    <td style="padding-left: 20px; " class="primary-color"><strong>Designation</strong></td>
                                    <td style="padding-left: 20px;" class="primary-color"><?php echo $salary['designation_name']?></td>
                                    <td style="padding-left: 20px;" class="primary-color"><strong>Joining Date :</strong></td>
                                    <td style="padding-left: 20px;" colspan="2" class="primary-color"><?php echo $salary['joining_date']?></td>
                                </tr>

                                <!--                            <tr>-->
                                <!--                                <td style="padding-left: 20px; " class="primary-color"><strong>Late Minutes - Deductions</strong></td>-->
                                <!--                                <td style="padding-left: 20px;" class="primary-color">0 minutes - (Rs 0) </td>-->
                                <!--                                <td style="padding-left: 20px;" class="primary-color">Pension Deduction</td>-->
                                <!--                                <td style="padding-left: 20px;" colspan="2" class="primary-color">0</td>-->
                                <!--                            </tr>-->

                                <tr>
                                    <td style="padding-left: 20px; " class="primary-color"><strong>Working Days</strong></td>
                                    <td style="padding-left: 20px;" class="primary-color"><?php echo $salary['no_of_days']?></td>
                                    <td style="padding-left: 20px;" class="primary-color"><strong>Campus Name</strong></td>
                                    <td style="padding-left: 20px;" colspan="2" class="primary-color"><?php echo $salary['campus_name']?></td>
                                </tr>

                                <tr>
                                    <td style="padding-left: 20px; " class="primary-color"><strong>Leaves</strong></td>
                                    <td style="padding-left: 20px;" class="primary-color"><?php echo $salary['no_of_absents']?></td>
                                    <td style="padding-left: 20px;" class="primary-color"><strong>Gender:</strong></td>
                                    <td style="padding-left: 20px;" colspan="3" class="primary-color"><?php echo $salary['gender']?></td>
                                </tr>

                                </tbody>
                            </table>

                            <!--                            <tr>-->
                            <!--                                <td style="padding-left: 20px; " class="primary-color"><strong>Gross Salary</strong></td>-->
                            <!--                                <td style="padding-left: 20px;" class="primary-color">95000</td>-->
                            <!--                                <td style="padding-left: 20px;" class="primary-color">Staff Purchase</td>-->
                            <!--                                <td style="padding-left: 20px;" colspan="2" class="primary-color">0</td>-->
                            <!--                            </tr>-->
                            <!---->
                            <!--                            <tr>-->
                            <!--                                <td style="padding-left: 20px; " class="primary-color"><strong>Net Salary</strong></td>-->
                            <!--                                <td style="padding-left: 20px;" class="primary-color">95000</td>-->
                            <!--                                <td style="padding-left: 20px;" class="primary-color">Income Tax</td>-->
                            <!--                                <td style="padding-left: 20px;" colspan="2" class="primary-color">1941</td>-->
                            <!--                            </tr>-->

                            <table>
                                <tbody>
                                <tr>
                                    <td style="border:none!important;padding-left: 20px; " class="primary-color"><strong></strong></td>
                                    <td style="border:none!important;padding-left: 20px;" class="primary-color"><br></td>
                                    <td style="border:none!important;padding-left: 20px;" class="primary-color"></td>
                                    <td style="border:none!important;padding-left: 20px;" colspan="2" class="primary-color"></td>
                                </tr>
                                </tbody>
                            </table>

                            <div class="row" style="margin-bottom: 10px!important;">
                                <div class="col-md-6">
                                    <div style="border: 1px solid black;padding: 5px;width: 95%;height: 350px">
                                        <table style="padding-top: 0; border-spacing: 0;background:white;width: 100%">
                                            <tbody class="table_body" style="background:white;">
                                            <tr>
                                                <td style="padding-left: 20px; " class="primary-color">Basic Salary</td>
                                                <td style="padding-left: 20px;" class="primary-color"><?php echo $salary['basic_salary']?></td>
                                            </tr>
                                            <?php
                                            $payroll_id = $salary['id'];
                                            $this->db->select('*');
                                            $this->db->from('payroll_earn_deducs');
                                            $this->db->where(array('payroll_id'=>$payroll_id ,'type_id'=>0));
                                            $payer = $this->db->get()->result_array();

                                            $total_payables = $salary['basic_salary'];
                                            foreach ($payer as $payearning){

                                                $total_payables = $total_payables + $payearning['amount']
                                            ?>
                                            <tr>
                                                <td style="padding-left: 20px; " class="primary-color"><?php echo $payearning['name'] ?></td>
                                                <td style=" padding-left: 20px;" class="primary-color"><?php echo $payearning['amount']?></td>
                                            </tr>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div style="border: 1px solid black;padding: 5px;width: 95%;height: 40px">
                                        <table style="padding-top: 0; border-spacing: 0;background:white;width: 100%">
                                            <tbody class="table_body" style="background:white;">
                                            <tr>
                                                <td style="padding-left: 20px; " class="primary-color">Total Payments</td>
                                                <td style=" padding-left: 20px;" class="primary-color"><?php echo $total_payables?></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div style="border: 1px solid black;padding: 5px;width: 95%;margin-left: 8px;height: 350px">
                                        <table style="padding-top: 0; border-spacing: 0;background:white;width: 100%">
                                            <tbody class="table_body" style="background:white;">
                                            <?php
                                            $payroll_id_for_ded = $salary['id'];
                                            $this->db->select('*');
                                            $this->db->from('payroll_earn_deducs');
                                            $this->db->where(array('payroll_id'=>$payroll_id_for_ded ,'type_id'=>1));
                                            $payde = $this->db->get()->result_array();

                                            $total_deductions = 0;
                                            foreach ($payde as $payded){
                                                $total_deductions = $total_deductions + $payded['amount'];
                                                ?>
                                                <tr>
                                                    <td style="padding-left: 20px; " class="primary-color"><?php echo $payded['name'] ?></td>
                                                    <td style="padding-left: 20px;" class="primary-color"><?php echo $payded['amount']?></td>
                                                </tr>
                                            <?php } ?>

                                            </tbody>
                                        </table>
                                    </div>
                                    <div style="border: 1px solid black;padding: 5px;width: 95%;height: 40px;margin-left: 8px;">
                                        <table style="padding-top: 0; border-spacing: 0;background:white;width: 100%">
                                            <tbody class="table_body" style="background:white;">
                                            <tr>
                                                <td style="padding-left: 20px; " class="primary-color">Total Deductions</td>
                                                <td style=" padding-left: 20px;" class="primary-color"><?php echo $total_deductions?></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>


                            <table>
                                <tbody>
                                <tr>
                                    <td style="border:none!important;padding-left: 20px; " class="primary-color"><strong></strong></td>
                                    <td style="border:none!important;padding-left: 20px;" class="primary-color"><br></td>
                                    <td style="border:none!important;padding-left: 20px;" class="primary-color"></td>
                                    <td style="border:none!important;padding-left: 20px;" colspan="2" class="primary-color"></td>
                                </tr>
                                <tr>
                                    <td style="border:none!important;padding-left: 20px; " class="primary-color"><strong></strong></td>
                                    <td style="border:none!important;padding-left: 20px;" class="primary-color"><br></td>
                                    <td style="border:none!important;padding-left: 20px;" class="primary-color"></td>
                                    <td style="border:none!important;padding-left: 20px;" colspan="2" class="primary-color"></td>
                                </tr>
                                <tr>
                                    <td style="border:none!important;padding-left: 20px; " class="primary-color"><strong></strong></td>
                                    <td style="border:none!important;padding-left: 20px;" class="primary-color"><br></td>
                                    <td style="border:none!important;padding-left: 20px;" class="primary-color"></td>
                                    <td style="border:none!important;padding-left: 20px;" colspan="2" class="primary-color"></td>
                                </tr>
                                </tbody>
                            </table>






                        </div>

                    </div>
                </div>

                <style>
                    @page  { margin: 10px; width: 2550px; height: 3300px }
                    @media  print {
                        .abc {
                            position: fixed;
                            bottom: 0px;
                            left: 0px;
                            right: 0px;
                            height: 230px;
                            width:100%!important;

                            /* Extra personal styles */
                            /*background-color: #1d2f79;*/
                            color: black;

                            font-size:12px;
                            line-height: 20px;
                        }

                    }


                </style>


                            <footer class="abc">
                                <div class="container" >
                                    <div class="row">
                                        <div class="col-md-12">
                                            <table class="table table-bordered" text-align="center" style="padding-top: 10; border-spacing: 0;background:white;width: 100%">
                                                <tbody class="table_body" style="background:white;">
                                                <tr>
                                                    <td style="padding-left: 20px; " class="primary-color"><strong>Approved By</strong></td>
                                                    <td style=" border-bottom: 2px double black!important;padding-left: 20px;" class="primary-color">
                                                        <?php
                                                        $created_by = $salary['created_by'];
                                                        $this->db->select('users.first_name , users.last_name ');
                                                        $this->db->from('users');
                                                        $this->db->where(array('users.user_id'=>$created_by,'users.status'=>1));
                                                        $ge_created_by = $this->db->get()->result_array();
                                                        foreach ($ge_created_by as $cr_by){
                                                            ?>

                                                            <?php echo $cr_by['first_name']; ?>

                                                            <?php echo $cr_by['last_name'];?>


                                                            <?php
                                                        }
                                                        ?>

                                                    </td>
                                                    <td style="padding-left: 20px;" class="primary-color"><strong>Gross Salary</strong></td>
                                                    <td style="padding-left: 20px;" colspan="2" class="primary-color"><?php echo $salary['gross_salary'] ?></td>
                                                </tr>


                                                <tr>
                                                    <td style="padding-left: 20px; " class="primary-color"><strong>Recived By</strong></td>
                                                    <td style=" border-bottom: 2px double black!important;padding-left: 20px;" class="primary-color">&nbsp;</td>
                                                    <td style="padding-left: 20px;" class="primary-color"><strong>Net Salary</strong></td>
                                                    <td style=" border-bottom: 2px double black!important; border-top: 2px double black!important;padding-left: 20px;" colspan="3" class="primary-color"><span style=""><?php echo $salary['earned_salary'] ?></span></td>
                                                </tr>
                                                </tbody>
                                            </table>

                                            <p style="font-size: 10px;text-align: center;margin-top: 20px">
                                                <br>*Note: Total & monthly tax deductions are subjected to change in case of change in income.
                                                <br>This is system generated slip, needs no signature or stamp.<br>
                                                *Note: In case of any query, please submit your written request in Human Resource Department by 10th of every month.</p>
                                        </div>
                                    </div>
                                </div>


                            </footer>

                <!--<div class="row" style="background:white;">-->
                <!--    <div class="col-md-12" style="padding:0px;text-align:center">-->
                <!--        <img src="http://erp.holisticgroup.com.pk/public/invoice_2.png" style="width:50%!important;max-width:50%">-->
                <!--    </div>-->
                <!--</div>-->


            </div>
        </div>
    </div>


<?php
}?>

</body>
</html>
