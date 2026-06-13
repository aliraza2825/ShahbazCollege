<html>
<head>
    <title>Print COuncil Admission Form</title>
    <style>
        *{
            margin:0;
            padding:0;
            font-family: sans-serif;
            font-size:17px;
        }
        .container{
            margin:0 auto;
            /*height:1132px;*/
            width:800px;
            padding:20px;
        }
        .left{
            float:left;
            display:inline;
        }
        .right{
            float:right;
            display:inline;
        }
        .clear{
            clear:both;
        }
        .header{
            width:100%;
            height:auto;
        }
        .text-center{
            text-align:center;
        }
        .body{
            width:688px;
            background-image: url('<?php echo base_url();?>print_images/council.png');
            background-repeat: no-repeat;
            background-position: top center;
            background-size:20%;
            padding:1% 5% 5% 5%;
        }
        .underline{
            text-decoration:underline;
        }
        .footer{
            width:100%;
        }
        .photo{
            width:150px;
            height:170px;
            border:1px solid #000;
        }
        .registrar, .photo-container{
            width:50%;
        }
        .bottom-photo-container{
            width:40%;
        }
        .fifty{
            width:50%;
        }
        .bold{
            text-weight:bold;
        }
        @media print {
            .pagebreak { page-break-before: always; } /* page-break-after works, as well */
        }
    </style>
    <style>
        .container2{
            margin:0 auto;
            height:1132px;
            width:800px;
            padding:20px;
            background-image:url('<?php echo base_url();?>print_images/noc_background.png');
            background-repeat:no-repeat;
            background-position:-55px bottom;
            background-size:30%;
            position:relative;
        }
        .container3{
            height:992px;
            width:765px;
            padding:20px;
            background-image:url('<?php echo base_url();?>print_images/noc_background.png');
            background-repeat:no-repeat;
            background-position:-55px bottom;
            background-size:30%;
            position:relative;
        }
        .container4{
            margin:0 auto;
            height:1132px;
            width:765px;
            padding:20px;
            background-repeat:no-repeat;
            background-position:-55px bottom;
            background-size:30%;
            position:relative;
        }


        .left2{
            float:left;
            display:inline;
        }
        .right2{
            float:right;
            display:inline;
        }
        .clear2{
            clear:both;
        }
        .header2{
            width:100%;
            height:auto;
        }
        .logo2{
            width:20%;
        }
        .college_name2{
            width:76%;
            padding:2%;
        }
        .text-center{
            text-align:center;
        }
        .line-thick{
            border-bottom:4px solid #00ac54;
            margin-bottom:1px;
        }
        .line-normal{
            border-bottom:2px solid #00ac54;
            margin-bottom:2px;
        }
        .line-thin{
            border-bottom:1px solid #00ac54;
            margin-bottom:1px;
        }
        .body2{
            width:100%;
            height:920px;
            /*background-image: url('<?php echo base_url();?>images/shahbaz.png');
				background-repeat: no-repeat;
				background-position: center;
				background-size:40%;	*/
        }
        .body2::before {
            background-image: url('<?php echo base_url();?>uploads/<?php echo $student[0]['logo'];?>');
            background-size: 50%;
            background-position:50% 50%;
            background-repeat:no-repeat;
            content: "";
            display: block;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            opacity: 0.3;
        }
        .college_name2 {
            width: 76%;
            padding: 2%;
        }

    </style>
</head>
<body>
    <div class="container"">
        <div class="container4"">
            <div class="header">
                <div class="text-center">
                    <h3 style="text-transform: uppercase; font-style: italic;" class="underline">CHECK LIST/UNDERTAKING</h3>
                    <div class="clear"></div>
                    <h3  style="text-transform: uppercase; font-style: italic;">(PHARMACY TECHNICIAN ADMISSION)</h3>
                </div>

            </div>
            <div>
                <div class="clear"></div>
                <div>
                    <br />
                    <p style="font-size:16px;">I Principle / Director of <span class="underline">&nbsp;&nbsp;&nbsp;<strong><?php echo $student[0]['campus_name'];?></strong>&nbsp;&nbsp;&nbsp;</span> undertake that the said student </p>
                    <br />
                    <p style="font-size:16px;">fulfills the admission criteria of  Pharmacy  Technician  Program.   His /  Her admission form and documents</p>
                    <br />
                    <p style="font-size:16px;">are thoroughly  checked and verified as per detail mentioned below.  All the documents are attached </p>
                    <br />
                    <p style="font-size:16px;">according to the sequence given in the checklist .</p>
                    <br />
                    <p style="font-size:16px;"><strong>Student's Name:</strong><span class="underline" style="min-width: 120px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $student[0]['first_name']." ".$student[0]['last_name'];?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> <strong>List Sr No:</strong><span class="underline" style="min-width: 120px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo "";?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Session:</strong><span class="underline" style="min-width: 120px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $student[0]['session'];?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></p>
                    <br />
                    <style>
                        table, th, td {
                            border: 1px solid black;
                        }
                        th, td {
                            padding: 5px;
                        }
                        table {
                            border-collapse: collapse;
                        }
                    </style>
                    <table style="width: 100%"; >
                        <tr>
                            <th>SR NO.</th>
                            <th>PARTICULAR</th>
                            <th>YES/NO</th>
                        </tr>
                        <tr>
                            <td>1.</td>
                            <td>Admission form is completely filled in capital letters as per <strong>Matric Certificate.</strong></td>
                            <td style="text-align: center;">YES</td>
                        </tr>
                        <tr>
                            <td style="text-align: start;">2.</td>
                            <td>Colored Photocopies of <strong>Matric Certificate (04)</strong> attested by the Principle/Director of institute. <strong>(Only Science Group) i.e,</strong> Physics,Chemistry and Biology/Computer Science/Electric Wiring/Poultry Farming.
                                <br />
                                <br />
                                <strong>*Note: Matric with arts/general science group and online certificate is not accepted in any case</strong>
                            </td>
                            <td style="text-align: center;">YES</td>
                        </tr>
                        <tr>
                            <td>3.</td>
                            <td>Recent Passport Size Photographs <strong>(04)</strong> with blue background attested from back by the Principle / Director of the institute.</td>
                            <td style="text-align: center;">YES</td>
                        </tr>
                        <tr>
                            <td>4.</td>
                            <td>Colored Photocopies of CNIC <strong>(02)</strong> attested by the Principle / Director of the institute.</td>
                            <td style="text-align: center;">YES</td>
                        </tr>
                        <tr>
                            <td>5.</td>
                            <td>Latest Character Certificate issued by institute/College on Letter Head.</td>
                            <td style="text-align: center;">YES</td>
                        </tr>
                        <tr>
                            <td>6.</td>
                            <td>Original Admission Letter issued  by institute/College on Letter Head.</td>
                            <td style="text-align: center;">YES</td>
                        </tr>
                        <tr>
                            <td>7.</td>
                            <td>Form is signed & stamped by the Principle / Director of the institute.</td>
                            <td style="text-align: center;">YES</td>
                        </tr>
                    </table>
                    <div style="position:absolute; left:55px; top:679px; text-transform: uppercase; text-align: center">
                        <?php echo $student[0]['first_name']." ".$student[0]['last_name'];?>
                    </div>
                    <br />
                    <p style="font-size:16px;">I, __________________________________  hereby further declare that the details furnished above are true</p>
                    <br />
                    <p style="font-size:16px;">and correct to the best of my knowledge. In case of any discrepancy / short coming <strong>Punjab Pharmacy</strong> </p>
                    <br />
                    <p style="font-size:16px;"><strong>Council, Lahore</strong> have right to cancel the admission.  I also understand that in case of bogus/counterfeit/ </p>
                    <br />
                    <p style="font-size:16px;">forged / tampered documents the Punjab Pharmacy Council, Lahore is fully authorized to cancelled the</p>
                    <br />
                    <p style="font-size:16px;">Enrollment / Registration of the student at any stage of the said course,even after passing the exams and</p>
                    <br />
                    <p style="font-size:16px;">getting the registration in register B.</p>
                    <br />
                    <br />
                    <br />
                </div>
                <style>
                    .column {
                        float: left;
                        width: 33.33%;
                    }
                    /* Clear floats after the columns */
                    .row:after {
                        content: "";
                        display: table;
                        clear: both;
                    }
                </style>
                <div class="row" style="margin-top: 10px;">
                    <div class="column">__________________<br /><strong>Signature & Thumb</strong><br />(Student)</div>
                    <div class="column">__________________<br /><strong>Signature & Stamp</strong><br />(Stamp of the College Principal)</div>
                    <div class="column">__________________<br /><strong>Signature & Stamp</strong><br />(Stamp of the College CEO/Director)</div>
                </div>
                <?php echo $student[0]['roll_no'];?>
            </div>
        </div>
    </div>
</body>
</html>