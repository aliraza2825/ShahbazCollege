<html>
<head>
    <title>Print COuncil Admission Form</title>
    <style>
        @font-face {
            font-family: 'Brigham';
            font-style: normal;
            font-weight: 400;
            src: local('Brigham'), url('<?php echo base_url();?>fonts/Brigham.otf') format('woff');
        }

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
</head>
<body>
<?php
$this->db->select('teacher_documents.image');
$this->db->from('teacher_documents');
$this->db->join('users','teacher_documents.teacher_id = users.user_id','inner');
$this->db->where( array('users.designation_id'=>'26', 'users.campus_id' => $student[0]['campus_id']));

$resp= $this->db->get()->result_array();
?>
<div class="container">
    <div class="header">
        <div class="right">
            <span style="margin-left: 81px;position: absolute;"><?php //echo $student[0]['roll_no'];?></span>
            <span>ROLL No. _______________________</span>
        </div>
        <div class="clear"></div>
        <div class="text-center">
            <h1 style="font-size:30px; color:#00803b; font-style:italic;">PUNJAB PHARMACY COUNCIL, LAHORE</h1>
            <p style="font-size:14px; color:#00803b; font-style:italic;">Block No.7, LDA Flats, Huma Block, Allama Iqbal Town, Lahore. Ph. # 042-99260298</p>
            <br />
            <p style="font-size:18px; color:#000; font-style:italic; font-weight:bold;">ADMISSION FORM FOR EXAMINATION OF <span style="color:#F00;">PHARMACY TECHNICIAN <?php if($checkExam[0]['type']=='supplementary'):?>(Supplementary)<?php endif;?></span><br />(DIPLOMA COURSE)Year of Examination__________________</p>
        </div>
        <div class="clear"></div>
    </div>
    <div class="body">
        <div class="left registrar">
            <br /><br /><br /><br />
            <p>THE REGISTRAR<br />PUNJAB PHARMACY COUNCIL<br />LAHORE</p>
        </div>
        <div class="left photo-container">
            <div class="right photo" style="background-image:url('<?php if(@$photo[0]['online_image']==''){echo base_url();?>uploads/<?php echo @$photo[0]['image'];}else{ echo str_replace($bucket_address,$cloudfront_address,$photo[0]['online_image']);}?>'); background-size:100% 100%;">
                <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: 100px;margin-left: 30px;">
            </div>
        </div>
        <div class="clear"></div>
        <div class="content" style="position:relative;">

            <div style="position:absolute; left:130px; top:70px;">
                <?php echo $student[0]['first_name'].' '.$student[0]['last_name'];?>
            </div>

            <div style="position:absolute; left:165px; top:98px;">
                <?php echo $student[0]['father_name'];?>
            </div>

            <div style="position:absolute; left:165px; top:128px;">
                <?php echo date('d-m-Y',strtotime($student[0]['date_of_birth']));?>
            </div>
            <div style="position:absolute; left:495px; top:123px;">
                <?php echo $student[0]['religion'];?>
            </div>

            <div style="position:absolute; left:420px; top:185px;">
                <?php echo $student[0]['cnic'];?>
            </div>

            <div style="position:absolute; left:190px; top:210px;">
                <?php echo $student[0]['address'];?>
            </div>

            <div style="position:absolute; left:190px; top:266px;">
                <?php echo $student[0]['campus_name'];?>
            </div>

            <div style="position:absolute; left:185px; top:317px; width:270px; height:30px; font-size:12px;">
                <?php echo $student[0]['campus_address'];?>
            </div>

            <div style="position:absolute; left:560px; top:327px;">
                <?php echo $student[0]['phone'];?>
            </div>

            <div style="position:absolute; left:105px; top:355px; font-size:14px;">
                <?php echo $student[0]['email'];?>
            </div>

            <div style="position:absolute; left:435px; top:355px;">
                <?php echo $student[0]['mobile'];?>
            </div>

            <div style="position:absolute; left:90px; top:573px;">
                <?php echo @$exam_details->council_exam_no;?>
            </div>
            <div style="position:absolute; left:140px; top:593px;">
                <?php echo @$exam_details->roll_no;?>
            </div>
            <div style="position:absolute; left:100px; top:618px; font-size: 11px;">
                <?php echo @$exam_details->result_remarks;?>
            </div>

            <p>Sir,<br />Request for permission to appear in the Examination of the Punjab Pharmacy Council for Registration under Section 25(b) of the Pharmacy Act, 1967. Necessary particulars:-</p>
            <p style="margin-left:15px; line-height:1.7em; margin-top:10px;">
                1. &nbsp;Full Name ___________________________________________________________
                <br />
                2. &nbsp;Father’s Name _______________________________________________________
                <br />
                3. &nbsp;Date of Birth ____________________________Religion _______________________
                <br />
                4. &nbsp;Must attach the following:-
                <br />
                <span style="line-height:1.3em;">
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;i)    Previous Result Card
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ii) I.D. Card No.__________________________
                </span>
                <br />
                5. &nbsp;Permanent Address ____________________________________________________
                <br />
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;____________________________________________________________________
                <br />
                6. &nbsp;Name of Institution _____________________________________________________
                <br />
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;____________________________________________________________________
                <br />
                7. &nbsp;Address (Institute) _____________________________ Phone No._______________
                <br />
                8. &nbsp;E-Mail:-___________________________ Cell No. ____________________________
            </p>
            <div class="left fifty text-center">
                <span style="font-size:30px; font-weight:bold;"><br />Fee in Cash<br />Accepted</span>
            </div>
            <div class="left fifty">
                <p style="margin-left:15px;font-weight:bold; font-style:italic;">Signature of Applicant</p>
                <p style="margin-left:15px; margin-top:20px;">English ___________________________</p>
                <p style="margin-left:15px; margin-top:20px;">Urdu _____________________________</p>
            </div>
            <div class="clear"></div>
            <div>
                <p style="margin-top:10px">I verified the particulars mentioned in this form are correct. </p>
                <img src="<?php echo base_url() ?>uploads/<?php echo $resp[0]['image']?>" style="height: 50px;width: 80px;margin-left: 442px;margin-top: -15px;">
                <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: -122px;margin-left: 0px;">
                <div style="position:absolute; top:410px; left: 450px;">
                    <p style="font-family: 'Brigham', sans-serif; color:#022B59; font-size:26px;"><?php echo ucfirst(strtolower($student[0]['first_name'])).' '.ucfirst(strtolower($student[0]['last_name']));?></p>
                </div>
                <p style="margin-top: -30px;">Signature of Principal/Director of Institute with stamp _____________________________</p>

                <p class="text-center underline" style="margin-top:10px; font-weight:bold;">FOR COMPARTMENT - EXEMPTED CANDIDATES ONLY</p>
                <p>Appear in ______________ Examination held in the month of __________ Year ________</p>
                <p>Under&nbsp;&nbsp; Roll No.&nbsp;&nbsp;&nbsp; ______________________ &nbsp;&nbsp;and&nbsp;&nbsp;&nbsp; is&nbsp;&nbsp; eligible&nbsp;&nbsp; to&nbsp;&nbsp;&nbsp; re-appear&nbsp;&nbsp;&nbsp;&nbsp; in&nbsp;&nbsp; the</p>
                <p>Subject&nbsp;&nbsp; of&nbsp;&nbsp; __________________________ &nbsp;&nbsp;in&nbsp;&nbsp; next&nbsp;&nbsp; one/two&nbsp;&nbsp; chance&nbsp;&nbsp; according&nbsp;&nbsp; to&nbsp;&nbsp;</p>
                <p>result card.</p>


                <p class="text-center underline" style="margin-top:10px; font-weight:bold; font-style:italic;">For Office Use only</p>
                <div style="border:1px solid #000; padding:0 10px;">
                    <p>Admission form has been received and required documents have been checked Admission Fee has also been received. May be admitted please. Prepared by (Exam. Clerk) ________________ Checked by (Assistant) ___________ Cash Receipt No. ______________________________ Accountant ______________</p>
                </div>
                <p style="margin-top:10px;">
                    I) &nbsp; The Examination Fee is <strong>Rs. 6,000/</strong>, after the expiry of due date double fee amounting to &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <strong>Rs. 12,000/-</strong> has to be remitted.
                    <br />
                    II) &nbsp; Incomplete Form shall not be accepted.
                </p>
            </div>
            <!--
            <div>
                <div class="roll_no_slip" style="position:relative;">

                    <div style="position:absolute; left:158px; top:121px; text-transform: uppercase;">
                        <?php echo $student[0]['first_name'].' '.$student[0]['last_name'];?>
                    </div>

                    <div style="position:absolute; left:496px; top:122px; text-transform: uppercase;">
                        <?php echo $student[0]['father_name'];?>
                    </div>

                    <h2 class="text-center" style="margin-top:20px; font-size:24px;">ROLL NO. SLIP</h2>
                    <div class="right">

                        <span>ROLL No.__________________</span>
                    </div>
                    <div class="clear"></div>
                    <p style="font-size:15px;">Candidate will be admitted in the Examination Hall on production and delivery of this Roll Number Slip.<br />Please bring your National Identity Card during Theory and Practical Examination.</p>
                    <p style="text-align:center; font-weight:bold; font-size:17px;">PUNJAB PHARMACY COUNCIL, LAHORE</p>

                    <p style="margin-top:20px">Admit Mr./Miss./Mrs.________________________ S/o, D/o, W/o_____________________</p>
                    <p style="margin-top:5px;">in the Examination being held on______________________________________________</p>
                    <p style="margin-top:5px;">at Center_______________________ at the _________________________________</p>
                    <div class="left bottom-photo-container" style="margin-top:10px;">
                        <div class="left photo" style="background-image:url('<?php if(@$photo[0]['online_image']==''){echo base_url();?>uploads/<?php echo @$photo[0]['image'];}else{ echo str_replace($bucket_address,$cloudfront_address,$photo[0]['online_image']);}?>'); background-size:100% 100%;">
                            <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 100px;height: 67px;margin-top: 100px;margin-left: 45px;">
                            <img src="<?php echo base_url() ?>uploads/<?php echo $resp[0]['image']?>" style="height: 109px;width: 75px;margin-left: 92px;margin-top:-80px;">                        </div>
                    </div>
                    <div class="left">
                        <p style="margin-top:20px; font-weight:bold;">MOBILE PHONE, BAG, BOOKS<br />AND NOTES NOT ALLOWED IN<br />THE EXAMINATION HALL.</p>
                    </div>
                    <div class="clear"></div>

                    <div class="left" style="width:60%;">
                        <p style="margin-top:30px;">Signature of Candidate___________________ </p>
                        <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 129px;height: 77px;margin-top: 5px;margin-left: 227px;">
                        <p style="margin-top:-46px;">Verified by Principal_____________________</p>
                    </div>
                    <div class="left">
                        <p style="font-weight:bolder;margin-top:30px;">REGISTRAR</p>
                        <p>Punjab Pharmacy Council</p>
                    </div>
                    <div class="clear"></div>
                </div>
                <br /><br />
                <div class="roll_no_slip" style="position:relative;">

                    <div style="position:absolute; left:158px; top:121px; text-transform: uppercase;">
                        <?php echo $student[0]['first_name'].' '.$student[0]['last_name'];?>
                    </div>

                    <div style="position:absolute; left:496px; top:122px; text-transform: uppercase;">
                        <?php echo $student[0]['father_name'];?>
                    </div>

                    <h2 class="text-center" style="margin-top:20px; font-size:24px;">ROLL NO. SLIP</h2>
                    <div class="right">
                        <span>ROLL No.__________________</span>
                    </div>
                    <div class="clear"></div>
                    <p style="font-size:15px;">Candidate will be admitted in the Examination Hall on production and delivery of this Roll Number Slip.<br />Please bring your National Identity Card during Theory and Practical Examination.</p>
                    <p style="text-align:center; font-weight:bold; font-size:17px;">PUNJAB PHARMACY COUNCIL, LAHORE</p>

                    <p style="margin-top:20px">Admit Mr./Miss./Mrs.________________________ S/o, D/o, W/o_____________________</p>
                    <p style="margin-top:5px;">in the Examination being held on______________________________________________</p>
                    <p style="margin-top:5px;">at Center_______________________ at the _________________________________</p>
                    <div class="left bottom-photo-container" style="margin-top:10px;">
                        <div class="left photo" style="background-image:url('<?php if(@$photo[0]['online_image']==''){echo base_url();?>uploads/<?php echo @$photo[0]['image'];}else{ echo str_replace($bucket_address,$cloudfront_address,$photo[0]['online_image']);}?>'); background-size:100% 100%;">
                            <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 100px;height: 67px;margin-top: 100px;margin-left: 45px;">
                            <img src="<?php echo base_url() ?>uploads/<?php echo $resp[0]['image']?>" style="height: 109px;width: 75px;margin-left: 92px;margin-top:-80px;"></div>
                    </div>
                    <div class="left">
                        <p style="margin-top:20px; font-weight:bold;">MOBILE PHONE, BAG, BOOKS<br />AND NOTES NOT ALLOWED IN<br />THE EXAMINATION HALL.</p>
                    </div>
                    <div class="clear"></div>

                    <div class="left" style="width:60%;">
                        <p style="margin-top:30px;">Signature of Candidate___________________ </p>
                        <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 129px;height: 77px;margin-top: 5px;margin-left: 227px;">
                        <p style="margin-top:-46px;">Verified by Principal_____________________</p>
                    </div>
                    <div class="left">
                        <p style="font-weight:bolder;margin-top:30px;">REGISTRAR</p>
                        <p>Punjab Pharmacy Council</p>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            -->
        </div>
    </div>
    <?php
        if(@$exam_details->result_image!=''):
    ?>
    <div class="pagebreak"> </div>
    <div class="result_card">
        <img src="<?php echo base_url()."/".@$exam_details->result_image ;?>" style="width:110%;height: 1200px;margin-top: 25px;margin-left: -40px">
    </div>
    <?php
        endif;
    ?>
</div>
<div class="container">
   
    <div class="picture" style="margin-bottom: 10px;float: left;width: 100%;">
        <!--        <div class="pic" style="width:24%;float: left;">-->
        <!--            <img src="--><?php //if(@$result_card[0]['online_image'] == ''){ echo base_url();?><!--uploads/--><?php //echo @$result_card[0]['image']; } else echo @$result_card[0]['online_image'];?><!--');" style="width:100%;height: 80px">-->
        <!--        </div>-->
        <div class="left photo-container" style="width: 25%;">
            <div class="right photo" style="background-image:url('<?php if(@$photo[0]['online_image']==''){echo base_url();?>uploads/<?php echo @$photo[0]['image'];}else{ echo str_replace($bucket_address,$cloudfront_address,$photo[0]['online_image']);}?>'); background-size:100% 100%;">
                <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: 89px;margin-left: 35px;">

            </div>
        </div>
        <div class="left photo-container" style="width: 25%;">
            <div class="right photo" style="background-image:url('<?php if(@$photo[0]['online_image']==''){echo base_url();?>uploads/<?php echo @$photo[0]['image'];}else{ echo str_replace($bucket_address,$cloudfront_address,$photo[0]['online_image']);}?>'); background-size:100% 100%;">
                <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: 89px;margin-left: 35px;">

            </div>
        </div>
        <div class="left photo-container" style="width: 25%;">
            <div class="right photo" style="background-image:url('<?php if(@$photo[0]['online_image']==''){echo base_url();?>uploads/<?php echo @$photo[0]['image'];}else{ echo str_replace($bucket_address,$cloudfront_address,$photo[0]['online_image']);}?>'); background-size:100% 100%;">
                <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: 89px;margin-left: 35px;">

            </div>
        </div>
        <div class="left photo-container" style="width: 25%;">
            <div class="right photo" style="background-image:url('<?php if(@$photo[0]['online_image']==''){echo base_url();?>uploads/<?php echo @$photo[0]['image'];}else{ echo str_replace($bucket_address,$cloudfront_address,$photo[0]['online_image']);}?>'); background-size:100% 100%;">
                <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: 89px;margin-left: 35px;">

            </div>
        </div>
    </div>

    <div class="id_card" style="margin-top: 10px;float: left;width: 100%;">
        <?php
        foreach($id_card as $list):
            ?>
            <div class="left photo-container" >
                <div class="right photo" style="width: 93%;height: 200px;background-image:url('<?php if(@$list['online_image']==''){echo base_url();?>uploads/<?php echo @$list['image'];}else{ echo str_replace($bucket_address,$cloudfront_address,$list['online_image']);}?>'); background-size:100% 100%;">
                    <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 90px;margin-top: 120px;margin-left: 231px;">

                </div>
            </div>
        <?php
        endforeach;
        ?>

    </div>

    <div class="id_card" style="margin-top: 10px;float: left;width: 100%;">
        <?php
        foreach($id_card as $list):
            ?>
            <div class="left photo-container" >
                <div class="right photo" style="width: 93%;height: 200px;background-image:url('<?php if(@$list['online_image']==''){echo base_url();?>uploads/<?php echo @$list['image'];}else{ echo str_replace($bucket_address,$cloudfront_address,$list['online_image']);}?>'); background-size:100% 100%;">
                    <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 90px;margin-top: 120px;margin-left: 231px;">

                </div>
            </div>
        <?php
        endforeach;
        ?>

    </div>

    <div class="id_card" style="margin-top: 10px;float: left;width: 100%;">
        <?php
        foreach($id_card as $list):
            ?>
            <div class="left photo-container" >
                <div class="right photo" style="width: 93%;height: 200px;background-image:url('<?php if(@$list['online_image']==''){echo base_url();?>uploads/<?php echo @$list['image'];}else{ echo str_replace($bucket_address,$cloudfront_address,$list['online_image']);}?>'); background-size:100% 100%;">
                    <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 90px;margin-top: 120px;margin-left: 231px;">

                </div>
            </div>
        <?php
        endforeach;
        ?>

    </div>

    <div class="id_card" style="margin-top: 10px;float: left;width: 100%;">
        <?php
        foreach($id_card as $list):
            ?>
            <div class="left photo-container" >
                <div class="right photo" style="width: 93%;height: 200px;background-image:url('<?php if(@$list['online_image']==''){echo base_url();?>uploads/<?php echo @$list['image'];}else{ echo str_replace($bucket_address,$cloudfront_address,$list['online_image']);}?>'); background-size:100% 100%;">
                    <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 90px;margin-top: 120px;margin-left: 231px;">

                </div>
            </div>
        <?php
        endforeach;
        ?>
    </div>

    <div style="width: 100%;float: left;margin-bottom: 10px">_______________________________________________________________________________________________</div>
    <div class="info">
        <span style="font-weight: bold;text-decoration: underline">Role N0: <span style="font-weight: normal"><?php echo $student[0]['roll_no'];?></span></span> &nbsp;
        <span style="font-weight: bold;text-decoration: underline">Name: <span style="font-weight: normal"><?php echo $student[0]['first_name'].' '.$student[0]['last_name'];?></span></span> &nbsp;
        <span style="font-weight: bold;text-decoration: underline">Father Name: <span style="font-weight: normal"><?php echo $student[0]['father_name'];?></span></span> <br>
        <span style="font-weight: bold;text-decoration: underline">Session : <span style="font-weight: normal"><?php echo $student[0]['name'];?></span></span> &nbsp;
        <span style="font-weight: bold;text-decoration: underline">Collage Name : <span style="font-weight: normal"><?php echo $student[0]['campus_name'];?></span></span> &nbsp;
    </div>
</div>
</body>
</html>