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

    $this->db->select('students.*, campuses.campus_id, campuses.campus_name,campuses.website, campuses.logo,campuses.phone2,campuses.phone1, campuses.address as campus_address, campuses.phone,campuses.stamp,campuses.head_stamp,classes.name,classes.session');
    $this->db->from('students');
    $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
    $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
    $this->db->where_in('students.student_id', $student['student_id']);

    $student = $this->db->get()->result_array();
    $photo = $this->db->get_where('student_documents', array('student_id'=>$student[0]['student_id'], 'type'=>'Photo'))->result_array();
    $result_card = $this->db->get_where('student_documents', array('student_id'=>$student[0]['student_id'], 'type'=>'Result Card'))->result_array();
    $id_card = $this->db->get_where('student_documents', array('student_id'=>$student[0]['student_id'], 'type'=>'ID Card'))->result_array();
    $b_form = $this->db->get_where('student_documents', array('student_id'=>$student[0]['student_id'], 'type'=>'B - Form'))->result_array();
    $thumb = $this->db->get_where('student_documents', array('student_id'=>$student[0]['student_id'], 'type'=>'Student Signature'))->result_array();
    $signature = $this->db->get_where('student_documents', array('student_id'=>$student[0]['student_id'], 'type'=>'Student thumb'))->result_array();

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
<?php
    endforeach;
?>
</body>
</html>