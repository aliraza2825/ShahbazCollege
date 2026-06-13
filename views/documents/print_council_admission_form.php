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
<?php
    foreach($students as $student):
?>
<?php 
    $this->db->select('teacher_documents.image');
    $this->db->from('teacher_documents');
    $this->db->join('users','teacher_documents.teacher_id = users.user_id','inner');
    $this->db->where( array('users.designation_id'=>'26', 'users.campus_id' => $student['campus_id']));
    $resp= $this->db->get()->result_array(); 

    $photo = $this->db->get_where('student_documents', array('student_id'=>$student['student_id'], 'type'=>'Photo'))->result_array();
?>
<div class="container">
    <div class="header">
        <div class="right">
            <span>ROLL No.__________________</span>
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
            <div class="right photo" style="background-image:url('<?php if(@$photo[0]['online_image'] == ''){ echo base_url();?>uploads/<?php echo @$photo[0]['image']; } else {echo @str_replace($bucket_address,$cloudfront_address,$photo[0]['online_image']);}?>'); background-size:100% 100%;">
                <img src="<?php echo base_url() ?>uploads/<?php echo @$student['stamp'];?>" style="width: 100px;height: 67px;margin-top: 100px;margin-left: 45px;">
                <img src="<?php echo base_url() ?>uploads/<?php echo $resp[0]['image']?>" style="height: 109px;width: 75px;margin-left: 92px;margin-top:-80px;">
            </div>
        </div>
        <div class="clear"></div>
        <div class="content" style="position:relative;">

            <div style="position:absolute; left:130px; top:70px;">
                <?php echo $student['first_name'].' '.$student['last_name'];?>
            </div>

            <div style="position:absolute; left:165px; top:98px;">
                <?php echo $student['father_name'];?>
            </div>

            <div style="position:absolute; left:165px; top:128px;">
                <?php echo date('d-m-Y',strtotime($student['date_of_birth']));?>
            </div>

            <div style="position:absolute; left:100px; top:157px;">
                <?php echo $student['caste'];?>
            </div>

            <div style="position:absolute; left:495px; top:123px;">
                <?php echo $student['religion'];?>
            </div>

            <div style="position:absolute; left:420px; top:157px;">
                <?php echo $student['qualification'];?>
            </div>

            <div style="position:absolute; left:178px; top:259px;">
                <?php echo $student['cnic'];?>
            </div>

            <div style="position:absolute; left:160px; top:300px;">
                <?php echo $student['address'];?>
            </div>

            <div style="position:absolute; left:190px; top:355px;">
                <?php echo $student['campus_name'];?>
            </div>

            <div style="position:absolute; left:185px; top:424px; width:270px; height:30px; font-size:12px;">
                <?php echo $student['campus_address'];?>
            </div>

            <div style="position:absolute; left:560px; top:421px;">
                <?php echo $student['phone'];?>
            </div>

            <div style="position:absolute; left:105px; top:445px; font-size:14px;">
                <?php echo $student['email'];?>
            </div>

            <div style="position:absolute; left:435px; top:446px;">
                <?php echo $student['mobile'];?>
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
<!--                <img src="--><?php //echo base_url() ?><!--uploads/--><?php //echo $resp[0]['image']?><!--" style="height: 50px;width: 80px;margin-left: 442px;margin-top: -15px;">-->
                <img src="<?php echo base_url() ?>uploads/<?php echo @$student['stamp'];?>" style="width: 129px;height: 77px;margin-top: -30px;margin-left: 522px;">
                <p style="margin-top:-20px">Signature of Principal/Director of Institute with stamp _________________________</p>
                <p class="text-center underline" style="margin-top:10px; font-weight:bold; font-style:italic;">For Office Use only</p>
                <div style="border:1px solid #000; padding:0 10px;">
                    <p>Admission form has been received and required documents have been checked Admission Fee has also been received. May be admitted please.<br />Prepared by (Exam. Clerk) ________________ Checked by (Assistant) ___________ Cash Receipt No. ______________________________ Accountant ______________</p>
                </div>
                <p style="margin-top:10px;">
                    I) &nbsp; The Examination Fee is Rs. 4,500/, after the expiry of due date double fee amounting to &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Rs. 9,000/-has to be remitted.
                    <br />
                    II) &nbsp; Incomplete Form shall not be accepted.
                </p>
            </div>
        </div>

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
                    <p style="font-size:16px;">I Principle / Director of <span class="underline">&nbsp;&nbsp;&nbsp;<strong><?php echo $student['campus_name'];?></strong>&nbsp;&nbsp;&nbsp;</span> undertake that the said student </p>
                    <br />
                    <p style="font-size:16px;">fulfills the admission criteria of  Pharmacy  Technician  Program.   His /  Her admission form and documents</p>
                    <br />
                    <p style="font-size:16px;">are thoroughly  checked and verified as per detail mentioned below.  All the documents are attached </p>
                    <br />
                    <p style="font-size:16px;">according to the sequence given in the checklist .</p>
                    <br />
                    <p style="font-size:16px;"><strong>Student's Name:</strong><span class="underline" style="min-width: 120px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $student['first_name']." ".$student['last_name'];?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> <strong>List Sr No:</strong><span class="underline" style="min-width: 120px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo "";?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Session:</strong><span class="underline" style="min-width: 120px;">&nbsp;&nbsp;<?php echo $student['session'];?></span></p>
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
                    <div style="position:absolute; left:55px; top:700px; text-transform: uppercase; text-align: center">
                        <?php echo $student['first_name']." ".$student['last_name'];?>
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
                    <img src="<?php echo base_url() ?>uploads/<?php echo $student['stamp'];?>" style="width: 140px; position:absolute; top:-85px; left:350px;">
                    <div class="column">__________________<br /><strong>Signature & Thumb</strong><br />(Student)</div>
                    <div class="column">__________________<br /><strong>Signature & Stamp</strong><br />(Stamp of the College Principal)</div>
                    <div class="column">__________________<br /><strong>Signature & Stamp</strong><br />(Stamp of the College CEO/Director)</div>
                </div>
            </div>

        </div>
    </div>
</div>
<?php
    endforeach;
?>
</body>
</html>