<html>
<head>
    <title>Print Council Admission Form</title>
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
    </style>
</head>
<body>
<div class="container">
    <br>
    <br>
    <br>

    <div class="picture" style="margin-bottom: 10px;float: left;width: 100%;">
        <!--        <div class="pic" style="width:24%;float: left;">-->
        <!--            <img src="--><?php //if(@$result_card[0]['online_image'] == ''){ echo base_url();?><!--uploads/--><?php //echo @$result_card[0]['image']; } else echo @$result_card[0]['online_image'];?><!--');" style="width:100%;height: 80px">-->
        <!--        </div>-->
        <div class="left photo-container" style="width: 25%;">
            <div class="right photo" style="background-image:url('<?php if($photo[0]['online_image']==''){echo base_url().'uploads/'.@$photo[0]['image'];}else{echo str_replace($bucket_address,$cloudfront_address,$photo[0]['online_image']);}?>'); background-size:100% 100%;">
                <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: 89px;margin-left: 35px;">

            </div>
        </div>
        <div class="left photo-container" style="width: 25%;">
            <div class="right photo" style="background-image:url('<?php if($photo[0]['online_image']==''){echo base_url().'uploads/'.@$photo[0]['image'];}else{echo str_replace($bucket_address,$cloudfront_address,$photo[0]['online_image']);}?>'); background-size:100% 100%;">
                <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: 89px;margin-left: 35px;">

            </div>
        </div>
        <div class="left photo-container" style="width: 25%;">
            <div class="right photo" style="background-image:url('<?php if($photo[0]['online_image']==''){echo base_url().'uploads/'.@$photo[0]['image'];}else{echo str_replace($bucket_address,$cloudfront_address,$photo[0]['online_image']);}?>'); background-size:100% 100%;">
                <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: 89px;margin-left: 35px;">

            </div>
        </div>
        <div class="left photo-container" style="width: 25%;">
            <div class="right photo" style="background-image:url('<?php if($photo[0]['online_image']==''){echo base_url().'uploads/'.@$photo[0]['image'];}else{echo str_replace($bucket_address,$cloudfront_address,$photo[0]['online_image']);}?>'); background-size:100% 100%;">
                <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: 89px;margin-left: 35px;">

            </div>
        </div>
    </div>

    <div class="id_card" style="margin-top: 10px;float: left;width: 100%;">
        <?php
        foreach($id_card as $list):
            ?>
            <div class="left photo-container" >
                <div class="right photo" style="width: 93%;height: 200px;background-image:url('<?php if($list['online_image']==''){echo base_url().'uploads/'.@$list['image'];}else{echo str_replace($bucket_address,$cloudfront_address,$list['online_image']);}?>'); background-size:100% 100%;">
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
                <div class="right photo" style="width: 93%;height: 200px;background-image:url('<?php if($list['online_image']==''){echo base_url().'uploads/'.@$list['image'];}else{echo str_replace($bucket_address,$cloudfront_address,$list['online_image']);}?>'); background-size:100% 100%;">
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
                <div class="right photo" style="width: 93%;height: 200px;background-image:url('<?php if($list['online_image']==''){echo base_url().'uploads/'.@$list['image'];}else{echo str_replace($bucket_address,$cloudfront_address,$list['online_image']);}?>'); background-size:100% 100%;">
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
                <div class="right photo" style="width: 93%;height: 200px;background-image:url('<?php if($list['online_image']==''){echo base_url().'uploads/'.@$list['image'];}else{echo str_replace($bucket_address,$cloudfront_address,$list['online_image']);}?>'); background-size:100% 100%;">
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
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>

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
            <p style="font-size:18px; color:#000; font-style:italic; font-weight:bold;">ADMISSION FORM FOR EXAMINATION OF <span style="color:#F00;">PHARMACY TECHNICIAN</span><br />(DIPLOMA COURSE)Year of Examination__________________</p>
        </div>
        <div class="clear"></div>
    </div>
    <div class="body">
        <div class="left registrar">
            <br /><br /><br /><br />
            <p>THE REGISTRAR<br />PUNJAB PHARMACY COUNCIL<br />LAHORE</p>
        </div>
        <div class="left photo-container">
            <div class="right photo" style="background-image:url('<?php if($photo[0]['online_image']==''){echo base_url().'uploads/'.@$photo[0]['image'];}else{echo str_replace($bucket_address,$cloudfront_address,$photo[0]['online_image']);}?>'); background-size:100% 100%;">
                <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: 122px;margin-left: -87px;">
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

            <div style="position:absolute; left:100px; top:157px;">
                <?php echo $student[0]['caste'];?>
            </div>

            <div style="position:absolute; left:495px; top:123px;">
                <?php echo $student[0]['religion'];?>
            </div>

            <div style="position:absolute; left:420px; top:157px;">
                <?php echo $student[0]['qualification'];?>
            </div>

            <div style="position:absolute; left:420px; top:232px;">
                <?php echo $student[0]['cnic'];?>
            </div>

            <div style="position:absolute; left:160px; top:300px;">
                <?php echo $student[0]['address'];?>
            </div>

            <div style="position:absolute; left:190px; top:355px;">
                <?php echo $student[0]['campus_name'];?>
            </div>

            <div style="position:absolute; left:185px; top:413px; width:270px; height:30px; font-size:12px;">
                <?php echo $student[0]['campus_address'];?>
            </div>

            <div style="position:absolute; left:560px; top:413px;">
                <?php echo $student[0]['phone'];?>
            </div>

            <div style="position:absolute; left:105px; top:445px; font-size:14px;">
                <?php echo $student[0]['email'];?>
            </div>

            <div style="position:absolute; left:435px; top:443px;">
                <?php echo $student[0]['mobile'];?>
            </div>

            <p>Sir,<br />Request for permission to appear in the Examination of the Punjab Pharmacy Council for Registration under Section 25(b) of the Pharmacy Act, 1967. Necessary particulars:-</p>
            <p style="margin-left:15px; line-height:1.7em; margin-top:10px;">
                1. &nbsp;Full Name ___________________________________________________________
                <br />
                2. &nbsp;Father’s Name _______________________________________________________
                <br />
                3. &nbsp;Date of Birth ____________________________Religion _______________________
                <br />
                4. &nbsp;Caste _______________________Qualification______________________________
                <br />
                5. &nbsp;Must attach the following:-
                <br />
                <span style="line-height:1.3em;">
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;i)    Matric Certificate
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ii) Institute Admission Letter <br/>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;iii) Four Photographs
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;iv). I.D. Card No.__________________________
						<br />
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;v)Character Certificate.
						<br />
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(Attach attested copies of all certificates)
						</span>
                <br />
                6. &nbsp;Postal Address ________________________________________________________
                <br />
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;____________________________________________________________________
                <br />
                7. &nbsp;Name of Institution _____________________________________________________
                <br />
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;____________________________________________________________________
                <br />
                8. &nbsp;Address (Institute) _____________________________ Phone No._______________
                <br />
                9. &nbsp;E-Mail:-___________________________ Cell No. ____________________________
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
                <p style="margin-top: -30px;">Signature of Principal/Director of Institute with stamp _____________________________</p>
                <p class="text-center underline" style="margin-top:10px; font-weight:bold; font-style:italic;">For Office Use only</p>
                <div style="border:1px solid #000; padding:0 10px;">
                    <p>Admission form has been received and required documents have been checked Admission Fee has also been received. May be admitted please. Prepared by (Exam. Clerk) ________________ Checked by (Assistant) ___________ Cash Receipt No. ______________________________ Accountant ______________</p>
                </div>
                <p style="margin-top:10px;">
                    I) &nbsp; The Examination Fee is Rs. 4,500/, after the expiry of due date double fee amounting to &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Rs. 9,000/-has to be remitted.
                    <br />
                    II) &nbsp; Incomplete Form shall not be accepted.
                </p>
            </div>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <div class="roll_no_slip" style="position:relative;">

                <div style="position:absolute; left:170px; top:116px;">
                    <?php echo $student[0]['first_name'].' '.$student[0]['last_name'];?>
                </div>

                <div style="position:absolute; left:122px; top:142px;">
                    <?php echo $student[0]['father_name'];?>
                </div>

                <h2 class="text-center" style="margin-top:20px; font-size:24px;">ROLL NO. SLIP</h2>
                <div class="right">
                    <span style="margin-left: 81px;position: absolute;"><?php //echo $student[0]['roll_no'];?></span>

                    <span>ROLL No.__________________</span>
                </div>
                <div class="clear"></div>
                <p style="font-size:15px;">Candidate will be admitted in the Examination Hall on production and delivery of this Roll Number Slip. Please bring your National Identity Card during Theory and Practical Examination.</p>
                <p style="text-align:center; font-weight:bold; font-size:17px;">PUNJAB PHARMACY COUNCIL, LAHORE</p>

                <p style="margin-top:20px">Admit Mr./Miss./Mrs.________________________________________________________</p>
                <p style="margin-top:5px;">S/o, D/o, W/o______________________________________________________________</p>
                <p style="margin-top:5px;">in the Examination being held on______________________________________________</p>
                <p style="margin-top:5px;">at Center_______________________ at the _________________________________</p>
                <div class="left bottom-photo-container" style="margin-top:10px;">

                    <div class="left photo" style="background-image:url('<?php if($photo[0]['online_image']==''){echo base_url().'uploads/'.@$photo[0]['image'];}else{echo str_replace($bucket_address,$cloudfront_address,$photo[0]['online_image']);}?>'); background-size:100% 100%;">
                        <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: 100px;margin-left: 65px;">

                    </div>
                </div>
                <div class="left">
                    <p style="margin-top:20px; font-weight:bold;">MOBILE PHONE, BAG, BOOKS<br />AND NOTES NOT ALLOWED IN<br />THE EXAMINATION HALL.</p>
                </div>
                <div class="clear"></div>

                <div class="left" style="width:60%;">
                    <p style="margin-top:30px;">Signature of Candidate___________________ </p>
                    <img src="<?php echo base_url() ?>uploads/<?php echo $resp[0]['image']?>" style="height: 50px;width: 80px;margin-left: 199px;margin-top:0px;">
                    <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: -100px;margin-left: 287px;">
                    <p style="margin-top:-30px;">Verified by Principal_____________________</p>
                </div>
                <div class="left">
                    <p style="font-weight:bolder;margin-top:30px;">REGISTRAR</p>
                    <p>Punjab Pharmacy Council</p>
                </div>
                <div class="clear"></div>
            </div>

            <br /><br />

            <div class="roll_no_slip" style="position:relative;">

                <div style="position:absolute; left:170px; top:116px;">
                    <?php echo $student[0]['first_name'].' '.$student[0]['last_name'];?>
                </div>

                <div style="position:absolute; left:122px; top:142px;">
                    <?php echo $student[0]['father_name'];?>
                </div>

                <h2 class="text-center" style="margin-top:20px; font-size:24px;">ROLL NO. SLIP</h2>
                <div class="right">
                    <span style="margin-left: 81px;position: absolute;"><?php echo $student[0]['roll_no'];?></span>
                    <span>ROLL No.__________________</span>
                </div>
                <div class="clear"></div>
                <p style="font-size:15px;">Candidate will be admitted in the Examination Hall on production and delivery of this Roll Number Slip. Please bring your National Identity Card during Theory and Practical Examination.</p>
                <p style="text-align:center; font-weight:bold; font-size:17px;">PUNJAB PHARMACY COUNCIL, LAHORE</p>

                <p style="margin-top:20px">Admit Mr./Miss./Mrs.________________________________________________________</p>
                <p style="margin-top:5px;">S/o, D/o, W/o______________________________________________________________</p>
                <p style="margin-top:5px;">in the Examination being held on______________________________________________</p>
                <p style="margin-top:5px;">at Center_______________________ at the _________________________________</p>
                <div class="left bottom-photo-container" style="margin-top:10px;">
                    <div class="left photo" style="background-image:url('<?php if($photo[0]['online_image']==''){echo base_url().'uploads/'.@$photo[0]['image'];}else{echo str_replace($bucket_address,$cloudfront_address,$photo[0]['online_image']);}?>'); background-size:100% 100%;">
                        <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: 100px;margin-left: 65px;">

                    </div>
                </div>
                <div class="left">
                    <p style="margin-top:20px; font-weight:bold;">MOBILE PHONE, BAG, BOOKS<br />AND NOTES NOT ALLOWED IN<br />THE EXAMINATION HALL.</p>
                </div>
                <div class="clear"></div>

                <div class="left" style="width:60%;">
                    <p style="margin-top:30px;">Signature of Candidate___________________ </p>
                    <img src="<?php echo base_url() ?>uploads/<?php echo $resp[0]['image']?>" style="height: 50px;width: 80px;margin-left: 199px;margin-top:0px;">
                    <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: -100px;margin-left: 287px;">
                    <p style="margin-top:-30px;">Verified by Principal_____________________</p>
                </div>
                <div class="left">
                    <p style="font-weight:bolder;margin-top:30px;">REGISTRAR</p>
                    <p>Punjab Pharmacy Council</p>
                </div>
                <div class="clear"></div>
            </div>

        </div>
    </div>
    <br>
    <br>
    <br>
    <br>


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
            height:940px;
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
    <div class="container2">
        <div class="header2">
            <div class="left2 logo2">
                <img src="<?php echo base_url();?>uploads/<?php echo $student[0]['logo'];?>" width="100%" alt="" />
            </div>
            <div class="left2 college_name2 text-center" >
                <h1 style="text-transform: uppercase;font-size: 31px;"><?php echo $student[0]['campus_name'];?></h1>
                <br />
                <p>Email : info@<?php echo $student[0]['website'];?></p>
            </div>
            <div class="clear"></div>
            <div class="line-thick"></div>
            <div class="line-normal"></div>
            <div class="line-thin"></div>
        </div>
        <div class="body2">
            <div class="left2 ref_no">
                <p>Ref No. <span class="underline"><?php echo date('Ymd',strtotime($student[0]['registration_date']));?></span></p>
            </div>
            <div class="right2 dated">
                <p>Date: <span class="underline"><?php echo date('M d, Y');?></span></p>
            </div>
            <div class="clear"></div>
            <div>
                <br /><br /><br /><br /><br />
                <h3 class="text-center">CHARACTER CERTIFICATE</h3>
                <br />
                <p style="font-size:18px;">This is to certify that <span class="underline"><?php if($student[0]['gender']=='Male'){echo 'Mr.';}else{echo 'Ms.';}?> <?php echo $student[0]['first_name'];?> <?php echo $student[0]['last_name'];?> <?php if($student[0]['gender']=='Male'){echo 'S/O';}else{echo 'D/O';}?> <?php echo $student[0]['father_name'];?></span> Roll No. <?php echo $student[0]['roll_no'];?> is regular Student of <?php echo $student[0]['campus_name'];?>. <?php if($student[0]['gender']=='Male'){echo 'He';}else{echo 'She';}?> is intelligent, Hardworking and Competent in academics. <?php if($student[0]['gender']=='Male'){echo 'He';}else{echo 'She';}?> brings Good Moral character. We wish <?php if($student[0]['gender']=='Male'){echo 'him';}else{echo 'her';}?> best in future.</p>
                <br />
                <p style="font-size:18px; text-align:right;">Sincerely,</p><br /><br /><br />
                <img src="<?php echo base_url() ?>uploads/<?php echo $resp[0]['image']?>" style="height: 50px;width: 80px;margin-left: 670px;margin-top:0px;">
                <p style="font-size:18px; text-align:right;">Principal</p>
                <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: -157px;margin-left: 655px;">

            </div>
        </div>
        <div class="footer text-center">
            <h4><?php echo $student[0]['campus_address'];?></h4>
            <h4>Phone : <?php echo $student[0]['phone'];?></h4>
        </div>
    </div>
    <br>
    <br>
    <br>
    <br>
    <div class="result_card">
        <img src="<?php if($result_card[0]['online_image']==''){echo base_url().'uploads/'.@$result_card[0]['image'];}else{echo str_replace($bucket_address,$cloudfront_address,$result_card[0]['online_image']);}?>" style="width:110%;height: 1220px;margin-top: 25px;margin-left: -40px">
        <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: -100px;margin-left: 614px;">
    </div>
    <br>
    <div class="result_card">
        <img src="<?php if($result_card[0]['online_image']==''){echo base_url().'uploads/'.@$result_card[0]['image'];}else{echo str_replace($bucket_address,$cloudfront_address,$result_card[0]['online_image']);}?>" style="width:110%;height: 1220px;margin-top: 25px;margin-left: -40px">
        <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: -100px;margin-left: 614px;">
    </div>
    <br>
    <div class="result_card">
        <img src="<?php if($result_card[0]['online_image']==''){echo base_url().'uploads/'.@$result_card[0]['image'];}else{echo str_replace($bucket_address,$cloudfront_address,$result_card[0]['online_image']);}?>" style="width:110%;height: 1220px;margin-top: 25px;margin-left: -40px">
        <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: -100px;margin-left: 614px;">
    </div>
    <br>
    <div class="result_card">
        <img src="<?php if($result_card[0]['online_image']==''){echo base_url().'uploads/'.@$result_card[0]['image'];}else{echo str_replace($bucket_address,$cloudfront_address,$result_card[0]['online_image']);}?>" style="width:110%;height: 1220px;margin-top: 25px;margin-left: -40px">
        <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: -100px;margin-left: 614px;">
    </div>
    <br>
</div>

</body>
</html>