<style>
    @font-face {
        font-family: SourceSansPro;
        src: url(assets/fonts/SourceSansPro-Regular.ttf);
    }

    .clearfix:after {
        content: "";
        display: table;
        clear: both;
    }

    a {
        color: #0087C3;
        text-decoration: none;
    }

    body {
        position: relative;
        width: 21cm;
        height: 29.7cm;
        margin: 0 auto;
        color: #555555;
        background: #FFFFFF;
        font-family: Arial, sans-serif;
        font-size: 14px;
        font-family: SourceSansPro;
    }

    header {
        padding: 10px 0;
        margin-bottom: 20px;
        border-bottom: 1px solid #AAAAAA;
    }

    #logo {
        float: right;
        margin-top: 8px;
    }

    #logo img {
        height: 70px;
    }

    #company {
        float: left;
        text-align: left;
    }


    #details {
        margin-bottom: 50px;
    }

    #client {
        padding-left: 6px;
        border-left: 6px solid #0087C3;
        float: left;
    }

    #client .to {
        color: #777777;
    }

    h2.name {
        font-size: 1.4em;
        font-weight: normal;
        margin: 0;
    }

    #invoice {
        float: right;
        text-align: right;
    }

    #invoice h1 {
        color: #0087C3;
        font-size: 2.4em;
        line-height: 1em;
        font-weight: normal;
        margin: 0  0 10px 0;
    }

    #invoice .date {
        font-size: 1.1em;
        color: #777777;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        border-spacing: 0;
        margin-bottom: 20px;
    }

    table th,
    table td {
        padding: 20px;
        background: #EEEEEE;
        text-align: center;
        border-bottom: 1px solid #FFFFFF;
    }

    table th {
        white-space: nowrap;
        font-weight: normal;
    }

    table td {
        text-align: right;
    }

    table td h3{
        color: #57B223;
        font-size: 1.2em;
        font-weight: normal;
        margin: 0 0 0.2em 0;
    }

    table .no {
        color: #FFFFFF;
        font-size: 1.6em;
        background: #57B223;
    }

    table .desc {
        text-align: left;
    }

    table .unit {
        background: #DDDDDD;
    }

    table .qty {
    }

    table .total {
        background: #57B223;
        color: #FFFFFF;
    }

    table td.unit,
    table td.qty,
    table td.total {
        font-size: 1.2em;
    }

    table tbody tr:last-child td {
        border: none;
    }

    table tfoot td {
        padding: 10px 20px;
        background: #FFFFFF;
        border-bottom: none;
        font-size: 1.2em;
        white-space: nowrap;
        border-top: 1px solid #AAAAAA;
    }

    table tfoot tr:first-child td {
        border-top: none;
    }

    table tfoot tr:last-child td {
        color: #57B223;
        font-size: 1.4em;
        border-top: 1px solid #57B223;

    }

    table tfoot tr td:first-child {
        border: none;
    }

    #thanks{
        font-size: 2em;
        margin-bottom: 50px;
    }

    #notices{
        padding-left: 6px;
        border-left: 6px solid #0087C3;
    }

    #notices .notice {
        font-size: 1.2em;
    }

    footer {
        color: #777777;
        width: 100%;
        height: 30px;
        position: absolute;
        bottom: 0;
        border-top: 1px solid #AAAAAA;
        padding: 8px 0;
        text-align: center;
    }



</style>

<div class="page-content-wrapper">

    <div class="page-content">

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
            <header class="clearfix">
                <?php $campus = $this->db->get_where('campuses','campus_id = "'.$loans[0]['campus_id'].'"')->row(); ?>
                <div id="company">
                    <h4 class="name"><?php echo $campus->campus_name ?></h4>
                    <div><?php echo $campus->address; ?></div>
                    <div><?php echo $campus->phone ?></div>
                    <div><a href="mailto:<?php echo $campus->email ?>"><?php echo $campus->email ?></a></div>
                </div>
                <div id="logo">
                    <img src="<?php echo base_url('uploads/'.$campus->logo); ?>">
                </div>
                </div>
            </header>

            <div id="details" class="clearfix">
                <div id="client">
                    <div class="to">LOAN TO:</div>
                    <h2 class="name"><?php echo $loans[0]['first_name']." ".$loans[0]['last_name'] ?></h2>
                    <div class="PHONE"><?php echo $loans[0]['mobile'] ?></div>
                    <div class="email"><a href=""><?php echo $loans[0]['email'] ?></a></div>
                </div>
                <div id="invoice">
                    <h1><?php echo "loan -".$loans[0]['id'] ?></h1>
                    <div class="date">Date of Loan: <?php echo $loans[0]['created_at'] ?></div>
                    <div class="date">No of Installments: <?php echo $loans[0]['months_approved'] ?></div>
                </div>
            </div>
            <table border="0" cellspacing="0" cellpadding="0">
                <thead>
                <tr>
                    <th class="no">#</th>
                    <th class="desc">DESCRIPTION</th>
                    <th class="unit"></th>
                    <th class="qty">Months</th>
                    <th class="total">TOTAL AMOUNT</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="no"></td>
                    <td class="desc"><h3>Loan Deduction from salary Per Month</td>
                    <td class="unit"></td>
                    <td class="qty"><?php echo $loans[0]['months_approved'] ?> - Installments</td>
                    <td class="total"><?php echo ($loans[0]['amount_approved']/$loans[0]['months_approved']) ?> Per month</td>
                </tr>
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="2"></td>
                    <td colspan="2">AMOUNT GIVEN</td>
                    <td><?php echo ($loans[0]['amount_approved']) ?></td>
                </tr>

                </tfoot>
            </table>
        <?php $loan_detail = $this->db->get_where("loan_plan","loan_id = '".$loans[0]['id']."'")->result_array() ?>
        <div class="row">
            <div class="col-md-12 ">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet box grey-cascade">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-money"> Loan Details</i>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table  class="table table-striped table-bordered table-hover" >
                            <thead>
                            <tr>
                                <th>
                                    Sr #
                                </th>
                                <th>
                                    Amount
                                </th>
                                <th>
                                    Paid Amount
                                </th>
                                <th>
                                    Dead Line
                                </th>

                                <th>
                                    Paid Status
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $i=1;
                            foreach($loan_detail as $payment):
                                ?>
                                <tr class="odd gradeX">

                                    <td>
                                        <?php echo $i;?>
                                    </td>

                                    <td>
                                        <?php echo $payment['amount'];?>
                                    </td>
                                    <td>
                                        <?php echo $payment['amount_paid'];?>
                                    </td>
                                    <td>
                                        <?php echo $payment['due_date'];?>
                                    </td>
                                    <td>
                                        <?php

                                        if($payment['amount_paid'] >0){
                                            echo "PAID";

                                        }else{

                                            echo "NOT PAID";

                                        }


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
                <!-- END EXAMPLE TABLE PORTLET-->
            </div>
        </div>


        <div id="thanks">Reeceiver Sign!</div>
            <div id="notices">
                <div>NOTICE:</div>
                <div class="notice">This Amount will be deducted from your salary as Per Above Plan.</div>
            </div>

        </div>

    </div>

</div>
