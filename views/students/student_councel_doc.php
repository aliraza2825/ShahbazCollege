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
            width:765px;
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
            margin:0 auto;
            height:1132px;
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
            height:925px;
            /*background-image: url('<?php echo base_url();?>images/shahbaz.png');
				background-repeat: no-repeat;
				background-position: center;
				background-size:40%;	*/
        }
        .body2::before {
            background-image: url('<?php echo base_url();?>uploads/<?php echo @$student[0]['logo'];?>');
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
        .body3{
            width:100%;
            height:840px;
            /*background-image: url('<?php echo base_url();?>images/shahbaz.png');
				background-repeat: no-repeat;
				background-position: center;
				background-size:40%;	*/
        }
        .body3::before {
            background-image: url('<?php echo base_url();?>uploads/<?php echo @$student[0]['logo'];?>');
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
<?php foreach($student_ids as $student_id ){

    $this->db->select('students.*, campuses.campus_id, campuses.campus_name,campuses.website, campuses.logo,campuses.phone2,campuses.phone1, campuses.address as campus_address, campuses.phone,campuses.stamp,campuses.head_stamp,classes.name,classes.session');
    $this->db->from('students');
    $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
    $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
    $this->db->where_in('students.student_id', $student_id);

    $student = $this->db->get()->result_array();
    $photo = $this->db->get_where('student_documents', array('student_id'=>$student_id, 'type'=>'Photo'))->result_array();
    $result_card = $this->db->get_where('student_documents', array('student_id'=>$student_id, 'type'=>'Result Card'))->result_array();
    $id_card = $this->db->get_where('student_documents', array('student_id'=>$student_id, 'type'=>'ID Card'))->result_array();
    $b_form = $this->db->get_where('student_documents', array('student_id'=>$student_id, 'type'=>'B - Form'))->result_array();
    $thumb = $this->db->get_where('student_documents', array('student_id'=>$student_id, 'type'=>'Student Signature'))->result_array();
    $signature = $this->db->get_where('student_documents', array('student_id'=>$student_id, 'type'=>'Student thumb'))->result_array();

    ?>
    <?php
    $this->db->select('teacher_documents.image');
    $this->db->from('teacher_documents');
    $this->db->join('users','teacher_documents.teacher_id = users.user_id','inner');
    $this->db->where( array('users.designation_id'=>'26', 'users.campus_id' => $student[0]['campus_id']));
    $resp= $this->db->get()->result_array();

    $this->db->select('teacher_documents.image');
    $this->db->from('teacher_documents');
    $this->db->join('users','teacher_documents.teacher_id = users.user_id','inner');
    $this->db->where( array('users.role' => "Admin"));
    $resp_ceo= $this->db->get()->result_array();
    ?>

    <div class="container" style="margin-top: 70px;">

        <div class="picture" style="margin-bottom: 10px; float: left;width: 100%;">
            <!--        <div class="pic" style="width:24%;float: left;">-->
            <!--            <img src="--><?php //if(@$result_card[0]['online_image'] == ''){ echo base_url();?><!--uploads/--><?php //echo @$result_card[0]['image']; } else echo @$result_card[0]['online_image'];?><!--');" style="width:100%;height: 80px">-->
            <!--        </div>-->
            <div class="left photo-container" style="width: 25%;">
                <div class="right photo" style="background-image:url('<?php if(@$photo[0]['online_image']=='') {echo base_url().'uploads/'.@$photo[0]['image'];}else{echo @$photo[0]['online_image'];}?>'); background-size:100% 100%;">
                    <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 100px;height: 67px;margin-top: 100px;margin-left: 45px;">
                    <img src="<?php echo base_url() ?>uploads/<?php echo $resp[0]['image']?>" style="height: 109px;width: 75px;margin-left: 92px;margin-top:-80px;">
                </div>
            </div>
            <div class="left photo-container" style="width: 25%;">
                <div class="right photo" style="background-image:url('<?php if(@$photo[0]['online_image']=='') {echo base_url().'uploads/'.@$photo[0]['image'];}else{echo @$photo[0]['online_image'];}?>'); background-size:100% 100%;">
                    <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 100px;height: 67px;margin-top: 100px;margin-left: 45px;">
                    <img src="<?php echo base_url() ?>uploads/<?php echo $resp[0]['image']?>" style="height: 109px;width: 75px;margin-left: 92px;margin-top:-80px;">
                </div>
            </div>
            <div class="left photo-container" style="width: 25%;">
                <div class="right photo" style="background-image:url('<?php if(@$photo[0]['online_image']=='') {echo base_url().'uploads/'.@$photo[0]['image'];}else{echo @$photo[0]['online_image'];}?>'); background-size:100% 100%;">
                    <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 100px;height: 67px;margin-top: 100px;margin-left: 45px;">
                    <img src="<?php echo base_url() ?>uploads/<?php echo $resp[0]['image']?>" style="height: 109px;width: 75px;margin-left: 92px;margin-top:-80px;">
                </div>
            </div>
            <div class="left photo-container" style="width: 25%;">
                <div class="right photo" style="background-image:url('<?php if(@$photo[0]['online_image']=='') {echo base_url().'uploads/'.@$photo[0]['image'];}else{echo @$photo[0]['online_image'];}?>'); background-size:100% 100%;">
                    <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 100px;height: 67px;margin-top: 100px;margin-left: 45px;">
                    <img src="<?php echo base_url() ?>uploads/<?php echo $resp[0]['image']?>" style="height: 109px;width: 75px;margin-left: 92px;margin-top:-80px;">
                </div>
            </div>
        </div>

        <div class="id_card" style="margin-top: 10px;float: left;width: 100%;">
            <?php if(count($id_card)>0){
                foreach($id_card as $list):
                    ?>
                    <div class="left photo-container" >
                        <div class="right photo" style="width: 93%;height: 200px;background-image:url('<?php if(@$list['online_image']=='') {echo base_url().'uploads/'.@$list['image'];}else{echo @$list['online_image'];}?>'); background-size:100% 100%;">
                            <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 91px;height: 86px;margin-top: 110px;margin-left: 10px;">
                            <img src="<?php echo base_url() ?>uploads/<?php echo $resp[0]['image']?>" style="height: 122px;width: 75px;margin-left: -100px;margin-top:-80px;">
                        </div>
                    </div>
                <?php
                endforeach;
            }else{
                ?>

                <div class="left photo-container" >
                    <div class="right photo" style="width: 93%;height: 200px;background-image:url('https://img.wallpapersafari.com/desktop/1024/576/24/80/82GQEX.jpg'); background-size:100% 100%;">


                    </div>
                </div>
                <div class="left photo-container" >
                    <div class="right photo" style="width: 93%;height: 200px;background-image:url('https://img.wallpapersafari.com/desktop/1024/576/24/80/82GQEX.jpg'); background-size:100% 100%;">

                    </div>
                </div>
            <?php } ?>


        </div>

        <div class="id_card" style="margin-top: 10px;float: left;width: 100%;">
            <?php if(count($id_card)>0){
                foreach($id_card as $list):
                    ?>
                    <div class="left photo-container" >
                        <div class="right photo" style="width: 93%;height: 200px;background-image:url('<?php if(@$list['online_image']=='') {echo base_url().'uploads/'.@$list['image'];}else{echo @$list['online_image'];}?>'); background-size:100% 100%;">
                            <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 91px;height: 86px;margin-top: 110px;margin-left: 10px;">
                            <img src="<?php echo base_url() ?>uploads/<?php echo $resp[0]['image']?>" style="height: 122px;width: 75px;margin-left: -100px;margin-top:-80px;">
                        </div>
                    </div>
                <?php
                endforeach;
            }else{
                ?>

                <div class="left photo-container" >
                    <div class="right photo" style="width: 93%;height: 200px;background-image:url('https://img.wallpapersafari.com/desktop/1024/576/24/80/82GQEX.jpg'); background-size:100% 100%;">


                    </div>
                </div>

                <div class="left photo-container" >
                    <div class="right photo" style="width: 93%;height: 200px;background-image:url('https://img.wallpapersafari.com/desktop/1024/576/24/80/82GQEX.jpg'); background-size:100% 100%;">

                    </div>
                </div>

            <?php } ?>


        </div>

        <div class="id_card" style="margin-top: 10px;float: left;width: 100%;">
            <?php if(count($id_card)>0){
                foreach($id_card as $list):
                    ?>
                    <div class="left photo-container" >
                        <div class="right photo" style="width: 93%;height: 200px;background-image:url('<?php if(@$list['online_image']=='') {echo base_url().'uploads/'.@$list['image'];}else{echo @$list['online_image'];}?>'); background-size:100% 100%;">
                            <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 91px;height: 86px;margin-top: 110px;margin-left: 10px;">
                            <img src="<?php echo base_url() ?>uploads/<?php echo $resp[0]['image']?>" style="height: 122px;width: 75px;margin-left: -100px;margin-top:-80px;">
                        </div>
                    </div>
                <?php
                endforeach;
            }else{
                ?>

                <div class="left photo-container" >
                    <div class="right photo" style="width: 93%;height: 200px;background-image:url('https://img.wallpapersafari.com/desktop/1024/576/24/80/82GQEX.jpg'); background-size:100% 100%;">


                    </div>
                </div>

                <div class="left photo-container" >
                    <div class="right photo" style="width: 93%;height: 200px;background-image:url('https://img.wallpapersafari.com/desktop/1024/576/24/80/82GQEX.jpg'); background-size:100% 100%;">

                    </div>
                </div>

            <?php } ?>


        </div>

        <div class="id_card" style="margin-top: 10px;float: left;width: 100%;">
            <?php if(count($id_card)>0){
                foreach($id_card as $list):
                    ?>
                    <div class="left photo-container" >
                        <div class="right photo" style="width: 93%;height: 200px;background-image:url('<?php if(@$list['online_image']=='') {echo base_url().'uploads/'.@$list['image'];}else{echo @$list['online_image'];}?>'); background-size:100% 100%;">
                            <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 91px;height: 86px;margin-top: 110px;margin-left: 10px;">
                            <img src="<?php echo base_url() ?>uploads/<?php echo $resp[0]['image']?>" style="height: 122px;width: 75px;margin-left: -100px;margin-top:-80px;">
                        </div>
                    </div>
                <?php
                endforeach;
            }else{
                ?>

                <div class="left photo-container" >
                    <div class="right photo" style="width: 93%;height: 200px;background-image:url('https://img.wallpapersafari.com/desktop/1024/576/24/80/82GQEX.jpg'); background-size:100% 100%;">


                    </div>
                </div>

                <div class="left photo-container" >
                    <div class="right photo" style="width: 93%;height: 200px;background-image:url('https://img.wallpapersafari.com/desktop/1024/576/24/80/82GQEX.jpg'); background-size:100% 100%;">

                    </div>
                </div>

            <?php } ?>


        </div>

        <div style="width: 100%;float: left;margin-bottom: 10px; margin-top: 8px">_______________________________________________________________________________________________</div>
        <div class="info">
            <!--            <span style="font-weight: bold;text-decoration: underline">Role N0: <span style="font-weight: normal">--><?php //echo $student[0]['roll_no'];?><!--</span></span> &nbsp;-->
            <span style="font-weight: bold;text-decoration: underline">Name: <span style="font-weight: normal"><?php echo $student[0]['first_name'].' '.$student[0]['last_name'];?></span></span> &nbsp;
            <span style="font-weight: bold;text-decoration: underline">Father Name: <span style="font-weight: normal"><?php echo $student[0]['father_name'];?></span></span> <br>
            <span style="font-weight: bold;text-decoration: underline">Session : <span style="font-weight: normal"><?php echo $student[0]['name'];?></span></span> &nbsp;
            <span style="font-weight: bold;text-decoration: underline">Collage Name : <span style="font-weight: normal"><?php echo $student[0]['campus_name'];?></span></span> <br />
            <span style="font-weight: bold;text-decoration: underline">Roll No : <span style="font-weight: medium"><?php echo $student[0]['roll_no'];?></span></span> &nbsp;
        </div>

        <div class="pagebreak"> </div>
        <div>
            <div class="header" >
                <div class="right">
                    <!--                <span style="margin-left: 81px;position: absolute;">--><?php //echo $student[0]['roll_no'];?><!--</span>-->

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
                    <div class="right photo" style="background-image:url('<?php if(@$photo[0]['online_image']=='') {echo base_url().'uploads/'.@$photo[0]['image'];}else{echo @$photo[0]['online_image'];}?>'); background-size:100% 100%;">
                        <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 100px;height: 67px;margin-top: 100px;margin-left: 45px;">
                        <img src="<?php echo base_url() ?>uploads/<?php echo $resp[0]['image']?>" style="height: 109px;width: 75px;margin-left: 92px;margin-top:-80px;">
                    </div>
                </div>
                <div class="clear"></div>
                <div class="content" style="position:relative;">

                    <div style="position:absolute; left:130px; top:70px; text-transform: uppercase;">
                        <?php echo $student[0]['first_name'].' '.$student[0]['last_name'];?>
                    </div>

                    <div style="position:absolute; left:165px; top:98px; text-transform: uppercase;">
                        <?php echo $student[0]['father_name'];?>
                    </div>

                    <div style="position:absolute; left:165px; top:128px; text-transform: uppercase;">
                        <?php echo date('d-m-Y',strtotime($student[0]['date_of_birth']));?>
                    </div>

                    <div style="position:absolute; left:100px; top:157px; text-transform: uppercase;">
                        <?php echo $student[0]['caste'];?>
                    </div>

                    <div style="position:absolute; left:495px; top:123px; text-transform: uppercase;">
                        <?php echo $student[0]['religion'];?>
                    </div>

                    <div style="position:absolute; left:420px; top:157px; text-transform: uppercase;">
                        <?php echo $student[0]['qualification'];?>
                    </div>

                    <div style="position:absolute; left:167px; top:259px; text-transform: uppercase;">
                        <?php echo $student[0]['cnic'];?>
                    </div>

                    <div style="position:absolute; left:193px; top:307px; text-transform: uppercase;">
                        <?php echo $student[0]['address'];?>
                    </div>

                    <div style="position:absolute; left:190px; top:360px; text-transform: uppercase;">
                        <?php echo $student[0]['campus_name'];?>
                    </div>

                    <div style="position:absolute; left:185px; top:436px; width:270px; height:30px; font-size:12px; text-transform: uppercase;">
                        <?php echo $student[0]['campus_address'];?>
                    </div>

                    <div style="position:absolute; left:560px; top:436px; text-transform: uppercase;">
                        <?php echo $student[0]['phone'];?>
                    </div>

                    <div style="position:absolute; left:105px; top:500px; font-size:14px; text-transform: uppercase;">
                        <?php echo $student[0]['email'];?>
                    </div>

                    <div style="position:absolute; left:435px; top:500px;">
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
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;i)    Colored photocopies Matric Certificate (4 Nos.)
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ii) Four Photographs <br/>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;iii) Institute Admission Letter
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;iv)Character Certificate.
                            <br />
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;v) I.D. Card No.__________________________
                            <br />
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(Attach attested copies of all certificates)
                            </span>
                        <br />
                        6. &nbsp;Permanent Address ____________________________________________________
                        <br />
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;____________________________________________________________________
                        <br />
                        7. &nbsp;Name of Institution _____________________________________________________
                        <br />
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;____________________________________________________________________
                        <br />
                        <br />
                        8. &nbsp;Address (Institute) _____________________________ Phone No._______________
                        <br />
                        <br/>
                        9. &nbsp;E-Mail:-___________________________ Cell No. ____________________________
                    </p>
                    <div class="left fifty text-center">
                        <span style="font-size:30px; font-weight:bold;"><br />&nbsp;<br />&nbsp;</span>
                    </div>
                    <div class="right fifty">
                        <p style="margin-left:15px;font-weight:bold; font-style:italic;">Signature of Applicant</p>
                        <p style="margin-left:15px; margin-top:20px;">English ___________________________</p>
                        <p style="margin-left:15px; margin-top:20px;">Urdu _____________________________</p>
                    </div>
                    <div class="clear"></div>
                    <div>
                        <p style="margin-top:10px">I verified the particulars mentioned in this form are correct. </p>
                        <div  style="height: 78px;width: 112px;margin-left: 406px;margin-top: -15px;"></div>
                        <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 129px;height: 84px;margin-top: -93px;margin-left: 535px;">
                        <p style="margin-top: -46px;">Signature of Principal/Director of Institute with stamp _____________________________</p>
                        <p class="text-center underline" style="margin-top:10px; font-weight:bold; font-style:italic;">For Office Use only</p>
                        <div style="border:1px solid #000; padding:0 10px;">
                            <p>Admission form has been received and required documents have been checked Admission Fee has also been received. May be admitted please.<br />Prepared by (Exam. Clerk) ________________ Checked by (Assistant) ___________ Cash Receipt No. ______________________________ Accountant ______________</p>
                        </div>
                        <p style="margin-top:10px;">
                            I) &nbsp; The Examination Fee is <strong>Rs. 6,000/</strong>, after the expiry of due date double fee amounting to &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <strong>Rs. 12,000/</strong>- has to be remitted.
                            <br />
                            II) &nbsp; Incomplete Form shall not be accepted.
                        </p>
                    </div>
                </div>
            </div>
            <div class="pagebreak"> </div>

            <!--
            <div>
                <div class="roll_no_slip" style="position:relative;">

                    <div style="position:absolute; left:158px; top:135px; text-transform: uppercase;">
                        <?php echo $student[0]['first_name'].' '.$student[0]['last_name'];?>
                    </div>

                    <div style="position:absolute; left:496px; top:135px; text-transform: uppercase;">
                        <?php echo $student[0]['father_name'];?>
                    </div>

                    <h2 class="text-center" style="margin-top:20px; font-size:24px;">ROLL NO. SLIP</h2>
                    <div class="right">

                        <span>ROLL No.__________________</span>
                    </div>
                    <div class="clear"></div>
                    <p style="font-size:15px;">Candidate will be admitted in the Examination Hall on production and delivery of this Roll Number Slip.<br />Please bring your National Identity Card during Theory and Practical Examination.</p>
                    <p style="text-align:center; font-weight:bold; font-size:17px;">PUNJAB PHARMACY COUNCIL, LAHORE</p>

                    <p style="margin-top:20px">Admit Mr./Miss./Mrs.________________________ S/o, D/o, W/o________________________________</p>
                    <p style="margin-top:5px;">in the Examination being held on______________________________________________</p>
                    <p style="margin-top:5px;">at Center_______________________ at the _________________________________</p>
                    <div class="left bottom-photo-container" style="margin-top:10px;">
                        <div class="left photo" style="background-image:url('<?php if(@$photo[0]['online_image']=='') {echo base_url().'uploads/'.@$photo[0]['image'];}else{echo @$photo[0]['online_image'];}?>'); background-size:100% 100%;">
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

                    <div style="position:absolute; left:158px; top:135px; text-transform: uppercase;">
                        <?php echo $student[0]['first_name'].' '.$student[0]['last_name'];?>
                    </div>

                    <div style="position:absolute; left:496px; top:135px; text-transform: uppercase;">
                        <?php echo $student[0]['father_name'];?>
                    </div>

                    <h2 class="text-center" style="margin-top:20px; font-size:24px;">ROLL NO. SLIP</h2>
                    <div class="right">
                        <span>ROLL No.__________________</span>
                    </div>
                    <div class="clear"></div>
                    <p style="font-size:15px;">Candidate will be admitted in the Examination Hall on production and delivery of this Roll Number Slip.<br />Please bring your National Identity Card during Theory and Practical Examination.</p>
                    <p style="text-align:center; font-weight:bold; font-size:17px;">PUNJAB PHARMACY COUNCIL, LAHORE</p>

                    <p style="margin-top:20px">Admit Mr./Miss./Mrs.________________________ S/o, D/o, W/o________________________________</p>
                    <p style="margin-top:5px;">in the Examination being held on______________________________________________</p>
                    <p style="margin-top:5px;">at Center_______________________ at the _________________________________</p>
                    <div class="left bottom-photo-container" style="margin-top:10px;">
                        <div class="left photo" style="background-image:url('<?php if(@$photo[0]['online_image']=='') {echo base_url().'uploads/'.@$photo[0]['image'];}else{echo @$photo[0]['online_image'];}?>'); background-size:100% 100%;">
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
        <div class="container4" style="margin-top : 50px;">
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
                    <p style="font-size:16px;"><strong>Student's Name:</strong><span class="underline" style="min-width: 120px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $student[0]['first_name']." ".$student[0]['last_name'];?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> <strong>List Sr No:</strong><span class="underline" style="min-width: 120px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo "";?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Session:</strong><span class="underline" style="min-width: 120px;">&nbsp;&nbsp;<?php echo $student[0]['session'];?></span></p>
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
                    <div style="position:absolute; left:55px; top:675px; text-transform: uppercase; text-align: center">
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
                <div class="row" style="margin-top: 20px; position:relative">
                    <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 140px; position:absolute; top:-85px; left:350px;">
                    <div class="column">__________________<br /><strong>Signature & Thumb</strong><br />(Student)</div>
                    <div class="column">__________________<br /><strong>Signature & Stamp</strong><br />(Stamp of the College Principal)</div>
                    <div class="column">__________________<br /><strong>Signature & Stamp</strong><br />(Stamp of the College CEO/Director)</div>
                </div>
            </div>

        </div>
        </div>
        <div class="pagebreak"> </div>

        <div class="container2" style="margin:0 auto;">
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
                <div class="left ref_no">
                    <p>Ref No. <span class="underline"><?php echo date('Ymd',strtotime($student[0]['registration_date']));?></span></p>
                </div>

                <div class="clear"></div>
                <div>
                    <br /><br /><br /><br />
                    <h3 class="text-center">Admission letter</h3>
                    <br />
                    <p style="font-size:18px;">It is certified that <strong><?php echo $student[0]['first_name'];?> <?php echo $student[0]['last_name'];?></strong> has been enrolled/admitted in the Part-I of Diploma in Pharmacy/Pharmacy Technician course as regular student for the session of <strong><?php echo $student[0]['session'];?></strong>.</p>
                    <br />
                    <p style="font-size:18px;">His / Her conduct and character as recorded by the college discipline committee during his / her stay in the college has been satisfactory.</p>

                    <br />
                    <br /><br /><br /><br />
                    <p style="font-size:18px; text-align:right;">Chief Executive/Principal</p><br />
                    <p style="font-size:18px; text-align:right;"><?php echo $student[0]['campus_name'];?></p>
                </div>
            </div>
            <div class="footer text-center">
                <h4><?php echo $student[0]['campus_address'];?></h4>
                <h4>Phone : <?php echo $student[0]['phone1'];?> -- <?php echo $student[0]['phone2'];?></h4>
            </div>
            <div class="student_information">
                <br />
                <hr />
                <br />
                <div style="float:left; display:inline; width:50%;">
                    <p><strong>To,</strong></p>
                    <p><strong>Name</strong> : <?php echo $student[0]['first_name'];?> <?php echo $student[0]['last_name'];?></p>
                    <p><strong>Phone</strong> : <?php echo $student[0]['mobile'];?> - <?php echo $student[0]['emergency_no'];?></p>
                    <p><strong>Address</strong> : <?php echo $student[0]['address'];?></p>
                </div>
                <div style="float:left; display:inline; width:50%;">
                    <p><strong>From,</strong></p>
                    <p><strong>Name</strong> : <?php echo $student[0]['campus_name'];?></p>
                    <p><strong>Phone</strong> : <?php echo $student[0]['phone'];?></p>
                    <p><strong>Address</strong> : <?php echo $student[0]['campus_address'];?></p>
                </div>
                <div style="clear:both;"></div>
            </div>
        </div>

        <div class="pagebreak"> </div>
        
        <div class="container3">
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
            <div class="body3">
                <div class="left2 ref_no">
                    <p>Ref No. <span class="underline"><?php echo date('Ymd',strtotime($student[0]['registration_date']));?></span></p>
                </div>

                <div class="clear"></div>
                <div>
                    <br /><br /><br /><br /><br />
                    <h3 class="text-center">CHARACTER CERTIFICATE</h3>
                    <br />
                    <p style="font-size:18px;">It is certified that <strong><?php echo $student[0]['first_name'];?> <?php echo $student[0]['last_name'];?></strong> has been a bonafied student of this college.He / She attended Pharmacy Technician Classes During the session of <strong><?php echo $student[0]['session'];?></strong>.</p>
                    <br />
                    <p style="font-size:18px;">His / Her conduct and character as recorded by the college discipline committee during his / her stay in the college has been satisfactory.</p>

                    <br />
                    <br /><br /><br /><br />
<!--                    <img src="--><?php //echo base_url() ?><!--uploads/--><?php //echo $resp[0]['image']?><!--" style="height: 50px;width: 80px;margin-left: 670px;margin-top:0px;">-->
                    <p style="font-size:18px; text-align:right;">Chief Executive/Principal</p><br />
                    <p style="font-size:18px; text-align:right;"><?php echo $student[0]['campus_name'];?></p>
<!--                    <img src="--><?php //echo base_url() ?><!--uploads/--><?php //echo $student[0]['stamp'];?><!--" style="width: 145px;height: 95px;margin-top: -180px;margin-left: 617px;">-->

                </div>
            </div>
            <div class="footer text-center">
                <h4><?php echo $student[0]['campus_address'];?></h4>
                <h4>Phone : <?php echo $student[0]['phone'];?></h4>
            </div>
        </div>

        
        <div style="clear:both;"></div>
        <div class="pagebreak"> </div>
        <?php
            if(@$result_card[0]['image']!=''):
        ?>
        <div class="result_card">
            <img src="<?php if(@$result_card[0]['online_image']=='') {echo base_url().'uploads/'.@$result_card[0]['image'];}else{echo @$result_card[0]['online_image'];}?>" style="width:110%;height: 1220px;margin-top: 25px;margin-left: -40px">
            <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: -156px;margin-left: 372px;">
            <img src="<?php echo base_url() ?>uploads/<?php echo $resp[0]['image']?>" style="height: 80px;width: 103px;margin-left: 422px;margin-top:-98px;">
        </div>
        <br>
        <div class="result_card">
            <img src="<?php if(@$result_card[0]['online_image']=='') {echo base_url().'uploads/'.@$result_card[0]['image'];}else{echo @$result_card[0]['online_image'];}?>" style="width:110%;height: 1220px;margin-top: 25px;margin-left: -40px">
            <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: -156px;margin-left: 372px;">
            <img src="<?php echo base_url() ?>uploads/<?php echo $resp[0]['image']?>" style="height: 80px;width: 103px;margin-left: 422px;margin-top:-98px;">
        </div>
        <br>
        <div class="result_card">
            <img src="<?php if(@$result_card[0]['online_image']=='') {echo base_url().'uploads/'.@$result_card[0]['image'];}else{echo @$result_card[0]['online_image'];}?>" style="width:110%;height: 1220px;margin-top: 25px;margin-left: -40px">
            <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: -156px;margin-left: 372px;">
            <img src="<?php echo base_url() ?>uploads/<?php echo $resp[0]['image']?>" style="height: 80px;width: 103px;margin-left: 422px;margin-top:-98px;">
        </div>
        <br>
        <div class="result_card">
            <img src="<?php if(@$result_card[0]['online_image']=='') {echo base_url().'uploads/'.@$result_card[0]['image'];}else{echo @$result_card[0]['online_image'];}?>" style="width:110%;height: 1220px;margin-top: 25px;margin-left: -40px">
            <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: -156px;margin-left: 372px;">
            <img src="<?php echo base_url() ?>uploads/<?php echo $resp[0]['image']?>" style="height: 80px;width: 103px;margin-left: 422px;margin-top:-98px;">
        </div>
        <br>
        <?php
            endif;
        ?>

        <?php if(count($b_form)>0 && count($id_card) <1 ): ?>
            <br>
            <br>
            <br>
            <br>
            <?php
                if(@$b_form[0]['image']!=''):
            ?>
            <div class="result_card">
                <img src="<?php if(@$b_form[0]['online_image']=='') {echo base_url().'uploads/'.@$b_form[0]['image'];}else{echo @$b_form[0]['online_image'];}?>" style="width:110%;height: 1220px;margin-top: 25px;margin-left: -40px">
                <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: -156px;margin-left: 372px;">
                <img src="<?php echo base_url() ?>uploads/<?php echo $resp[0]['image']?>" style="height: 100px;width: 145px;margin-left: 422px;margin-top:-120px;">
            </div>
            <br>
            <div class="result_card">
                <img src="<?php if(@$b_form[0]['online_image']=='') {echo base_url().'uploads/'.@$b_form[0]['image'];}else{echo @$b_form[0]['online_image'];}?>" style="width:110%;height: 1220px;margin-top: 25px;margin-left: -40px">
                <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: -156px;margin-left: 372px;">
                <img src="<?php echo base_url() ?>uploads/<?php echo $resp[0]['image']?>" style="height: 100px;width: 145px;margin-left: 422px;margin-top:-120px;">
            </div>
            <br>
            <div class="result_card">
                <img src="<?php if(@$b_form[0]['online_image']=='') {echo base_url().'uploads/'.@$b_form[0]['image'];}else{echo @$b_form[0]['online_image'];}?>" style="width:110%;height: 1220px;margin-top: 25px;margin-left: -40px">
                <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: -156px;margin-left: 372px;">
                <img src="<?php echo base_url() ?>uploads/<?php echo $resp[0]['image']?>" style="height: 100px;width: 145px;margin-left: 422px;margin-top:-120px;">
            </div>
            <br>
            <div class="result_card">
                <img src="<?php if(@$b_form[0]['online_image']=='') {echo base_url().'uploads/'.@$b_form[0]['image'];}else{echo @$b_form[0]['online_image'];}?>" style="width:110%;height: 1220px;margin-top: 25px;margin-left: -40px">
                <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: -156px;margin-left: 372px;">
                <img src="<?php echo base_url() ?>uploads/<?php echo $resp[0]['image']?>" style="height: 100px;width: 145px;margin-left: 422px;margin-top:-120px;">
            </div>
            <br>
            <?php
                endif;
            ?>
        <?php endif; ?>

    </div>

<?php }?>

<script>
    alert('Print only on Microsoft Edge Browser\nPage size A4\nScale (%) Actual Size\nMargins Default\nBackground Graphics');
</script>

</body>
</html>