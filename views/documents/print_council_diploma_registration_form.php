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
            background-image: url('<?php echo base_url();?>print_images/council.png');
            background-repeat: no-repeat;
            background-position: top center;
            background-size:15%;
            /*padding:1% 5% 5% 5%;*/
        }
        .underline{
            text-decoration:underline;
        }
        .footer{
            width:100%;
        }
        .photo{
            width:130px;
            height:150px;
            border:1px solid #000;
        }
        .registrar, .photo-container{
            width:20%;
        }
        .registrar, .text-container{
            width:80%;
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
    <div class="header">
        <div class="clear"></div>
        <div class="text-center">
            <h1 style="font-size:30px; color:#00803b; font-style:italic;">PUNJAB PHARMACY COUNCIL, LAHORE</h1>
            <h1 style="font-size:20px; color:#00803b;">(Established under Pharmacy Act, 1967)</h1>
            <p style="font-size:14px; color:#00803b; font-style:italic; font-weight: bold;">Sub Office: 169-A,Ahmed Block, New Garden Town, Lahore. Ph. # 042-35198418</p>
            <p style="font-size:14px; color:#00803b; font-style:italic; font-weight: bold;"><u>www.punjabpharmacycouncil.com</u></p>
        </div>
        <div class="clear"></div>
    </div>
    <div class="body">
        <div class="left text-container">
            <p style="font-size:14px; color:#000; font-weight:bold; margin-left: 12px; margin-top: 4px;"><br /><br /><br /><br /><br /><br /><br />Application Form for Registration as Pharmacy Technician in <span style="color:#3b4a9a;">Register-B</span></p>
        </div>
        <div class="right photo-container" style="margin-top: -15px;">
            <div class="right photo" style="background-image:url('<?php if(@$photo[0]['online_image'] == ''){ echo base_url();?>uploads/<?php echo @$photo[0]['image']; } else echo str_replace($bucket_address,$cloudfront_address,@$photo[0]['online_image']);?>'); background-size:100% 100%;">
                <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 100px;height: 67px;margin-top: 100px;margin-left: 45px;">
                <img src="<?php echo base_url() ?>uploads/<?php echo $resp[0]['image']?>" style="height: 109px;width: 75px;margin-left: 92px;margin-top:-80px;">
            </div>
        </div>
        
        <div class="clear"></div>
        <div class="content" style="position:relative;">

            <div style="position:absolute; left:190px; top:3px;">
                <?php echo strtoupper($student[0]['first_name'].' '.$student[0]['last_name']);?>
            </div>
            <div style="position:absolute; left:145px; top:26px;">
                <?php echo $student[0]['father_name'];?>
            </div>
            <div style="position:absolute; left:166px; top:50px;">
                <?php echo $student[0]['address'];?>
            </div>
            <div style="position:absolute; left:136px; top:98px;">
                <?php echo $student[0]['address'];?>
            </div>
            <div style="position:absolute; left:120px; top:145px;">
                <?php echo $student[0]['qualification'];?>
            </div>
            <div style="position:absolute; left:120px; top:168px;">
                <?php echo $student[0]['roll_no'];?>
            </div>
            <div style="position:absolute; left:372px; top:168px;">
                <?php echo $student[0]['session'];?>
            </div>
            <div style="position:absolute; left:588px; top:168px;">
                <?php echo "2022"?>
            </div>
            <div style="position:absolute; left:160px; top:192px;">
                <?php echo $student[0]['campus_name'];?>
            </div>
            <div style="position:absolute; left:160px; top:216px;">
                <?php echo $student[0]['date_of_birth'];?>
            </div>
            <div style="position:absolute; left:560px; top:216px;">
                <?php echo $student[0]['place_of_birth'];?>
            </div>
            <div style="position:absolute; left:200px; top:240px;">
                <?php echo $student[0]['cnic'];?>
            </div>
            <div style="position:absolute; left:500px; top:240px;">
                <?php echo "Pakistani";?>
            </div>
            <div style="position:absolute; left:155px; top:265px;">
                <?php echo $student[0]['email'];?>
            </div>
            <div style="position:absolute; left:500px; top:265px;">
                <?php echo $student[0]['mobile'];?>
            </div>
            <div style="position:absolute; left:195px; top:290px;">
                <?php echo $student[0]['mark_of_identification'];?>
            </div>

            <p style="margin-left:15px; line-height:1.7em; margin-top:12px;">
                1. &nbsp;Name (Block Letters) __________________________________________________________________
                <br />
                2. &nbsp;Father’s Name _______________________________________________________________________
                <br />
                3. &nbsp;Permanent Address: ___________________________________________________________________<br />______________________________________________________________________________________
                <br />
                4. &nbsp;Postal Address: ______________________________________________________________________<br />______________________________________________________________________________________
                <br />
                5. &nbsp;Qualification _________________________________________________________________________
                <br />
                6. &nbsp;Roll No.___________________________Session_______________________Held in_______________
                <br />
                7. &nbsp;From the Institute _____________________________________________________________________
                <br />
                8. &nbsp;Date of Birth _________________________________ Place of Birth ____________________________
                <br />
                9. &nbsp;National Identity Card No._____________________________Nationality_________________________
                <br />
                10. e-mail address._______________________________Phone No. ______________________________
                <br />
                11. Mark of Identification  _________________________________________________________________
                <br />
                12. The prescribed Fee of Rs.____________________________________________has been remitted by
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bank Draft/Pay Order No.__________________________________Dated: ______________________
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Receipt No._____________________________________________Dated: ______________________
            </p>
            <div class="left fifty" style="margin-top: 31px;">
                Dated: _________________________
            </div>
            <div class="right fifty">
                <p style="margin-left:15px;font-weight:bold; font-style:italic;">Signature of Applicant</p>
                <p style="margin-left:15px; margin-top:20px;">English ___________________________________</p>
                <p style="margin-left:15px; margin-top:20px;">Urdu _____________________________________</p>
            </div>
            <div class="clear"></div>
            <p style="font-size:18px; color:#000; font-weight:bold; margin-left: 12px; margin-top: 4px;">Following documents must be submitted/attached with the application form:</p><div class="clear"></div>
            <p><strong>1.  Six (6)</strong> Photographs Passport Size (blue background & clear photo) attested by the Principal/Director of the &nbsp;&nbsp;&nbsp;&nbsp;Institute concerned. (one photo front side & 5 from back side attested photographs).</p>
            <p><strong>2.  Two (2)</strong> Colored photocopies of Diploma Certificate attested by the Principal / Director / Head of the Institute &nbsp;&nbsp;&nbsp;&nbsp;concerned.</p>
            <p><strong>3.  Two (2)</strong> Specimen Signatures (signed with black marker) duly attested by the Principal / Director / Head of the Institute concerned.</p>
            <p><strong>4.  Two (2)</strong> Photocopies of National Identity Card attested by the Principal / Director / Head of the Institute &nbsp;&nbsp;&nbsp;&nbsp;concerned.</p>
            <p><strong>5.  One (1)</strong> Photocopy of 1st and 2nd Year Result Card attested by the Principal / Director / Head of the Institute &nbsp;&nbsp;&nbsp;&nbsp;concerned.</p>
            <p><strong>6.  </strong>Affidavit as per specimen given overleaf on Judicial paper of Rs. 100 duly attested by Oath Commissioner / &nbsp;&nbsp;&nbsp;&nbsp;Notary Public /Magistrate 1st Class.</p>
            <p><strong>7.  </strong>Original Character Certificate by the Principal / Director / Head of the Institute concerned.</p>
            <p><strong>8.  </strong>Admission letter / Enrollment card of the candidate attested by the Principal / Director / Head of the Institute &nbsp;&nbsp;&nbsp;&nbsp;concerned.</p>
            <p><strong>9.  Four (4)</strong> attested colored photocopies of Matric or equivalent certificates.</p>
            <p><strong>10. </strong> Pay Order / Demand Draft of <strong>Registration Fee</strong> in the name of Secretary, Punjab Pharmacy Council of &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Rs. 5000/- or Registration Fee may be submitted in the office of Punjab Pharmacy Council personally.</p>
            <p><strong>11. </strong>Fee for Matric or equivalent certificate verification as prescribed / required by the Board concerned.</p>
            <p><strong>12. </strong>In case, candidate is unable to appear personally in office Punjab Pharmacy Council, for registration, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;he/she will submit valid justification along with authority letter to the concerned person and valid NADRA &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;biometric verification from any center.</p>
            <p><strong>13. </strong>NOC from concerned Institute.</p>
        </div>
        <div class="clear"></div>
        <div class="pagebreak"> </div>
        <div class="content" style="position:relative;">
            <div style="position:absolute; left:30px; top:340px;">
                <?php echo strtoupper($student[0]['first_name'].' '.$student[0]['last_name']);?>
            </div>
            <div style="position:absolute; left:413px; top:340;">
                <?php echo $student[0]['father_name'];?>
            </div>
            <div style="position:absolute; left:70px; top:375px;">
                <?php echo strtoupper($student[0]['caste']);?>
            </div>
            <div style="position:absolute; left:362px; top:375px; font-size: 9px;">
                <?php echo $student[0]['address'];?>
            </div>
            <div style="position:absolute; left:70px; top:405px;">
                <?php echo strtoupper($student[0]['tehsil']);?>
            </div>
            <div style="position:absolute; left:360px; top:405px;">
                <?php echo $student[0]['district'];?>
            </div>
            <div style="position:absolute; left:530px; top:485px;">
                <?php //echo "2022"?>
            </div>
            <div style="position:absolute; left:115px; top:510px;">
                <?php echo $student[0]['roll_no'];?>
            </div>
            <div style="position:absolute; left:320px; top:510px;">
                <?php echo explode("-",$student[0]['session'])[0];?>
            </div>
            <div style="position:absolute; left:420px; top:510px;">
                <?php echo explode("-",$student[0]['session'])[1];?>
            </div>
            <div style="position:absolute; left:115px; top:535px;">
                <?php echo $student[0]['campus_name'];?>
            </div>
            <div style="position:absolute; left:125px; top:560px;">
                <?php echo $student[0]['computer_no'];?>
            </div>
            <div style="position:absolute; left:540px; top:560px;">
                <?php echo $student[0]['student_id'];?>
            </div>
            <div style="position:absolute;left: 210px;top: 610px;">
                <?php echo $student[0]['campus_name'];?>
            </div>
            <div style="position:absolute;left: 265px;top: 635px;">
                <?php echo $student[0]['student_id'];?>
            </div>
            <div style="position:absolute;left: 310px;top: 665px;">
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
        <div style="margin-top: 30px;">
            <div class="left sixty" style="width: 80%">
                <div class="left fourty">
                    <img src="<?php echo base_url();?>print_images/council.png" style="height: 82px;width: 88px;">
                </div>
                <div class="right sixty text-center">
                    <h1 style="font-size:18px; font-weight: bold;">PUNJAB PHARMACY COUNCIL, LAHORE</h1>
                    <h1 style="font-size:16px; font-weight: bold;">Specialized Health Care & Medical Education Department</h1>
                    <h1 style="font-size:16px; font-weight: bold;">Government of the Punjab</h1>
                    <p style="font-size:14px;">(Established under Pharmacy Act, 1967)</p>
                </div>
            </div>
            <div class="right fourty">
                <img src="<?php echo base_url();?>print_images/punjab.svg" style="height: 78px;width: 98px;">
            </div>
        </div>
        <div class="clear"></div>
        <div style="margin-top: 5px; margin-left: 5%; margin-right: 5%">
            <div class="left fourty">
                Ref. No. _____________________
            </div>
            <div class="right fourty">
                Date: _____________________
            </div>
        </div>
        <div class="clear"></div>
        <p style="margin-top: 5px; margin-left: 2%; margin-right: 5%"> To </p><br />
        <div>
            <div class="content" style="position:relative;">
            <div style="position:absolute;left: 240px;">
                <?php echo strtoupper($student[0]['first_name'].' '.$student[0]['last_name']);?>
            </div>
            <div style="position:absolute;left: 240px;top: 15px; width: 331px;">
                <?php echo $student[0]['address'];?>
            </div>
            <div style="position:absolute;left: 240px;top: 47px;">
                <?php echo $student[0]['campus_name'];?>
            </div>
            </div>
            <p style="margin-left: 105px;"><strong>Name of Applicant&nbsp;</strong> _____________________________________________</p>
            <p style="margin-left: 105px;"><strong>Address&nbsp;&nbsp;&nbsp;&nbsp;</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;_____________________________________________</p>
            <p style="margin-left: 105px;"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;_____________________________________________</p>
            <p style="margin-left: 105px;"><strong>College&nbsp;&nbsp;&nbsp;&nbsp;</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;_____________________________________________</p>
            <br />
            <p class="left twenty" style="font-size:14px; font-style:italic; font-weight: bold; width: 21%; text-align: end">Subject:</p>
            <p class="right sixty text-center" style="font-size:14px; font-style:italic; font-weight: bold; width: 79%;">DOCUMENTRY DEFFICIENCY IN YOUR APPLICATION FOR REGISTRATION AS PHARMACY TECHNICIAN IN REGISTER-B</p>
        </div>
        <div class="clear"></div>
        <div style="margin-left: 5%;">
            <div class="line-thin" style="height: 1px; background-color: black"></div>
            <div class="line-thin" style="height: 1px; background-color: black; margin-top: 1px"></div>
        </div>
        <div class="clear"></div>
        <div class="text-center" style="margin-left: 39px; margin-top: 10px;">
            <p>Reference your application for Registration as <strong><u>Pharmacy Technician</u></strong> in Register-B in Punjab Pharmacy</p>
            <p>Council, Lahore, on preliminary scrutiny of your application by the One Window Cell following documents</p>
            <p style="float: left">were found deficient / not as per format:</p>
        </div>
        <div class="clear"></div>
        <style>
            table, th, td {
                border: 1px solid black;
                font-size: 12px;
            }
            th, td {
                padding: 2px;
                text-align: left;
            }
            table {
                border-collapse: collapse;
            }
        </style>
        <table style="width: 100%; font-size: 13px;">
            <tr>
                <th>S#</th>
                <th>Requirement as per Volume 1.1</th>
                <th>Attached</th>
                <th>Not Attached</th>
                <th>As per format</th>
                <th>Not as per format</th>
            </tr>
            <tr>
                <td>1</td>
                <td><strong>Six (6)</strong> Photographs Passport Size (blue background & clear photo) attested by the Principal/Director of the Institute concerned. (one photo front side & 5 from back side attested photographs).</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>2</td>
                <td><strong>Two (2)</strong> Colored photocopies of Diploma Certificate attested by the Principal / Director / Head of the Institute concerned.</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>3</td>
                <td><strong>Two (2)</strong> Specimen Signatures duly attested by the Principal / Director / Head of the Institute concerned.</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>4</td>
                <td><strong>Two (2)</strong> Photocopies of National Identity Card attested by the Principal / Director / Head of the Institute concerned.</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>5</td>
                <td><strong>One (1)</strong> Photocopy of 1st  and 2nd Year Result Card attested by the Principal / Director / Head of the Institute concerned.</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>6</td>
                <td>Affidavit as per specimen given overleaf on Judicial paper of Rs. 100 duly attested by Oath Commissioner / Notary Public /Magistrate 1st Class.</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>7</td>
                <td>Original Character Certificate by the Principal / Director / Head of the Institute concerned.</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>8</td>
                <td>Admission letter / Enrollment card of the candidate attested by the Principal / Director / Head of the Institute concerned.</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>9</td>
                <td><strong>Four (4)</strong> attested colored photocopies of Matric or equivalent certificates.
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>10</td>
                <td>Pay Order / Demand Draft of Registration Fee in the name of Secretary, Punjab Pharmacy Council of Rs. 5000/- or Registration Fee may be submitted in the office of Punjab Pharmacy Council personally.</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>11</td>
                <td>Fee for Matric or equivalent certificate verification as prescribed / required by the Board concerned.</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>12</td>
                <td>In case, candidate is unable to appear personally in office Punjab Pharmacy Council, for registration, he/she will submit valid justification along with authority letter to the conocerned person and valid NADRA biometric verification from any center.</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>13</td>
                <td>NOC from concerned Institute.</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </table>
        <div class="clear"></div>
        <br />
        <table style="width: 100%;  font-size: 13px;" >
            <tr>
                <td>*</td>
                <td><strong style="font-family: Wingdings;">ü</strong> &nbsp;&nbsp;&nbsp;&nbsp;Tick the in the relevant box.</td>
            </tr>
            <tr>
                <td>**</td>
                <td>The candidate may proceed for Registration fee submission.</td>
            </tr>
            <tr>
                <td>***</td>
                <td>Fee as per Sr. No. 11 is the responsibility of applicant and less fee submission will delay the process of registration.</td>
            </tr>
            <tr>
                <td>****</td>
                <td>Candidate before submission of documents shall check all the documents the areas of objection usually are Session in the College & documents submitted are not as per format mention in Column 2 above. </td>
            </tr>
            <tr>
                <td>*****</td>
                <td>The applicant is required to furnish deficient documents along with this letter at the earliest to proceed further.</td>
            </tr>
        </table>

        <div class="left fourty" style="margin-top: 25px;">
            <p style="margin-left:30px">________________</p>
            <p style="margin-left:30px; font-size: 12px; font-weight: bold;">Signature & Thumb</p>
            <p style="margin-left:30px; font-size: 13px">(Candidate)</p>
        </div>
        <div class="right fourty"  style="margin-top: 25px;">
            <p style="margin-left:30px">_______________</p>
            <p style="margin-left:30px; font-size: 12px; font-weight: bold;">PPC Staff</p>
            <p style="margin-left:30px; font-size: 13px">Counter No. 2</p>
        </div>
        <div class="clear"></div>
        <div class="line-thin" style="height: 1px; background-color: black; margin-top: 3px"></div>
        <div class="clear"></div>
        <div class="text-center" style="margin-top: 5px;">
            <p style="font-size:12px; font-style:italic;"><strong>Sub-Office:</strong> 169-A, Ahmad Block, New Garden Town, Lahore Ph: 042-35198418</p>
            <p style="font-size:11px; ">Website: <strong><u style="color: #0c199c; font-size: 11px;">www.punjabpharmacycouncil.com</u></strong></p>
        </div>
        <br />
    </div>
    <div class="clear" style="page-break-after: auto;"></div>
</div>
</body>
</html>