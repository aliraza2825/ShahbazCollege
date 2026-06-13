<html>
<head>
    <title>Print Council Diploma Registration Form</title>
    <style>
        *{
            margin:0;
            padding:0;
            font-family: sans-serif;
            font-size:14px;
        }
        .container{
            margin:0 auto;
            /*height:1132px;*/
            width:765px;
            padding:5px;
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
</head>
<body>
<?php $this->db->select('teacher_documents.image');
$this->db->from('teacher_documents');
$this->db->join('users','teacher_documents.teacher_id = users.user_id','inner');
$this->db->where( array('users.designation_id'=>'26', 'users.campus_id' => $student[0]['campus_id']));
$resp= $this->db->get()->result_array(); ?>
<div class="container">
    <div class="body">
        <div class="content" style="position:relative;">
            <div style="position:absolute; left:30px; top:50px;">
                <?php echo strtoupper($student[0]['first_name'].' '.$student[0]['last_name']);?>
            </div>
            <div style="position:absolute; left:413px; top:50;">
                <?php echo $student[0]['father_name'];?>
            </div>
            <div style="position:absolute; left:70px; top:80px;">
                <?php echo strtoupper($student[0]['caste']);?>
            </div>
            <div style="position:absolute; left:362px; top:80px; font-size: 9px;">
                <?php echo $student[0]['address'];?>
            </div>
            <div style="position:absolute; left:70px; top:110px;">
                <?php echo strtoupper($student[0]['tehsil']);?>
            </div>
            <div style="position:absolute; left:360px; top:110px;">
                <?php echo $student[0]['district'];?>
            </div>
            <div style="position:absolute; left:115px; top:220px;">
                <?php echo $student[0]['roll_no'];?>
            </div>
            <div style="position:absolute; left:320px; top:220px;">
                <?php echo explode("-",$student[0]['session'])[0];?>
            </div>
            <div style="position:absolute; left:420px; top:220px;">
                <?php echo explode("-",$student[0]['session'])[1];?>
            </div>
            <div style="position:absolute; left:115px; top:245px;">
                <?php echo $student[0]['campus_name'];?>
            </div>
            <div style="position:absolute; left:125px; top:270px;">
                <?php echo $student[0]['computer_no'];?>
            </div>
            <div style="position:absolute; left:540px; top:270px;">
                <?php echo $student[0]['student_id'];?>
            </div>
            <div style="position:absolute;left: 210px;top: 320px;">
                <?php echo $student[0]['campus_name'];?>
            </div>
            <div style="position:absolute;left: 265px;top: 350px;">
                <?php echo $student[0]['student_id'];?>
            </div>
            <div style="position:absolute;left: 310px;top: 375px;">
                <?php echo $student[0]['cnic'];?>
            </div>
        </div>
        <div class="text-center" style="margin-top:290px;">
            <h1 style="font-size:30px; font-weight:bold;">AFFIDAVIT</h1>
        </div>
        <div class="clear"></div>
        <p style="margin-left:12px; line-height:1.7em; margin-top:12px; font-size: 17px;">
            I _____________________________________ S/o_____________________________
            <br />
            Caste ______________________Resident of__________________________________
            <br />
            Tehsil ______________________District______________________________do hereby
            <br />
            solemnly affirm and declare as under: -
            <br />
            <br />
            <div style="margin-left: 20px; line-height:1.7em; font-size: 15px;">
                1. &nbsp;I passed &nbsp;Diploma/Pharmacy Technician, Examination &nbsp;&nbsp;in&nbsp; the &nbsp;Year _____________
                <br />
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Roll No._________________ Session _________ to _________ from the
                <br />
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Institute ____________________________________________my enrollment No. in
                <br />
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;college is  ______________________. My Diploma  Certificate  No. is ____________
                <br />
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;date is _________________________.
                <br />
                2. &nbsp;That  &nbsp;&nbsp;the &nbsp;&nbsp;&nbsp;&nbsp;institute ________________________________________ &nbsp;&nbsp;issued&nbsp;&nbsp; my
                <br />
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Diploma Certificate vide No.______________________ which is  genuine one.
                <br />
                3. &nbsp;That  my National Identity Card No ______________________________ is genuine.
                <br />
                4. &nbsp;That I have never been granted Registration Certificate by any of the Provincial
                <br />
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pharmacy Council in Pakistan. Neither, I have applied for the same elsewhere.
                <br />
                5. &nbsp;That I have never been convicted by any Court of Law for an offence involving Moral
                <br />
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Turpitude.
                <br />
                6. &nbsp;That I have never been declared unsound mind by any Court of Law.
                <br />
                7. &nbsp;That in case of false information Punjab Pharmacy Council may take action against
                <br />
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;me as per law.
                <br />
                8. &nbsp;That as stated above is true to the best of my knowledge and belief.
            </div>
        </p>
        <div class="left sixty" style="margin-top: 31px;">
            <p style="margin-left:30px;font-weight:bold; font-size: 20px">Verification</p>
            <br />
            <p style="margin-left:30px; font-size: 14px">Verified on oath this _____________________________________</p>
            <br />
            <p style="margin-left:30px; font-size: 14px">Day of _______________________ at ______________________</p>
            <br />
            <p style="margin-left:30px; font-size: 14px">That the contents of this affidavit are true to the best of my</p>
            <br />
            <p style="margin-left:30px; font-size: 14px">Knowledge and belief and nothing have been concealed.</p>
        </div>
        <div class="right fourty"  style="margin-top: 31px;">
            <p style="margin-left:30px;font-weight:bold; font-size: 20px">DEPONENT</p>
            <br /><br /><br /><br /><br /><br /><br /><br />
            <p style="margin-left:30px;font-weight:bold; font-size: 20px">DEPONENT</p>
        </div>
        <div class="clear"></div><div class="clear"></div>
        <br />
        <br />
        <div class="pagebreak"> </div>
    </div>
</div>
</body>
</html>