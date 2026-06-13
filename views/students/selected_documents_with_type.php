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
    </style>
    <style>
        .container2{
            margin:0 auto;
            height:1132px;
            width:800px;
            padding:20px;
            /*background-image:url('*/<?php //echo base_url();?>/*print_images/noc_background.png');*/
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
</head>
<body>
<?php

?>
<?php
if($type == "Photo"){
?>
    <div class="container">
        <br>
        <br>
        <br>
        <div class="picture" style="margin-bottom: 10px;float: left;width: 100%;">
            <?php
            foreach($student_documents as $doc){
                $this->db->select('students.*, campuses.campus_id, campuses.campus_name,campuses.website, campuses.logo,campuses.phone2,campuses.phone1, campuses.address as campus_address, campuses.phone,campuses.stamp,classes.name');
                $this->db->from('students');
                $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
                $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
                $this->db->where('students.student_id', $doc['student_id']);

                $student = $this->db->get()->result_array();
                ?>

                <?php for($i = 0; $i < $doc_qty; $i++){

                    $this->db->select('teacher_documents.image');
                    $this->db->from('teacher_documents');
                    $this->db->join('users','teacher_documents.teacher_id = users.user_id','inner');
                    $this->db->where( array('users.designation_id'=>'26', 'users.campus_id' => $student[0]['campus_id']));

                    $resp= $this->db->get()->result_array();?>

                    <div class="left photo-container" style="width: 25%;margin-bottom: 5px">
                        <div class="right photo" style="background-image:url('<?php if(@$doc['online_image'] == ''){ echo base_url();?>uploads/<?php echo @$doc['image']; } else echo @$doc['online_image'];?>'); background-size:100% 100%;">
                            <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 100px;height: 67px;margin-top: 100px;margin-left: 45px;">
                        </div>

                        <div class="right photo" style="font-size: 12px;height: auto;padding: 10px 0px;    width: 150px;">
                            &nbsp; Name : <?php echo $student[0]['first_name']?> <?php echo $student[0]['last_name']?>
                            <br>
                            &nbsp; Roll No : <?php echo $student[0]['roll_no']?>
                        </div>
                    </div>
                <?php } ?>

                <?php } ?>

        </div>

    </div>
<?php
}
?>

<?php
if($type == "Result Card"){
    ?>

            <?php
            foreach($student_documents as $doc){
                $this->db->select('students.*, campuses.campus_id, campuses.campus_name,campuses.website, campuses.logo,campuses.phone2,campuses.phone1, campuses.address as campus_address, campuses.phone,campuses.stamp,classes.name');
                $this->db->from('students');
                $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
                $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
                $this->db->where('students.student_id', $doc['student_id']);


                $student = $this->db->get()->result_array();
                ?>
			<div class="container">
				<br>

                <?php for($i = 0; $i < $doc_qty; $i++){?>
                    <div class="result_card">
                        <img src="<?php echo base_url()?>uploads/<?php echo @$doc['image'];?>" style="width:110%;height: 1100px;margin-top: 25px;margin-left: -40px">
                        <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: -100px;margin-left: 614px;">
                    </div>
                    <div class="right photo" style="font-size: 12px;height: auto;padding: 10px 0px;    width: 100%;">
                        &nbsp; Name : <?php echo $student[0]['first_name']?> <?php echo $student[0]['last_name']?>
                        <br>
                        &nbsp; Roll No : <?php echo $student[0]['roll_no']?>
                    </div>
                <?php } ?>
			</div>

            <?php } ?>


    <?php
}
?>

<?php
if($type == "ID Card"){
    ?>

    <?php
    foreach($student_documents as $doc){
        $this->db->select('students.*, campuses.campus_id, campuses.campus_name,campuses.website, campuses.logo,campuses.phone2,campuses.phone1, campuses.address as campus_address, campuses.phone,campuses.stamp,classes.name');
        $this->db->from('students');
        $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
        $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
        $this->db->where('students.student_id', $doc['student_id']);


        $student = $this->db->get()->result_array();
        ?>
        <div class="container">
            <br>
            <div class="id_card" style="margin-top: 10px;float: left;width: 100%;">
            <?php for($i = 0; $i < $doc_qty; $i++){?>
                <div class="left photo-container" >
                    <div class="right photo" style="width:100%;height: 200px;background-image:url('<?php if(@$doc['online_image'] == ''){ echo base_url();?>uploads/<?php echo @$doc['image']; } else echo @$doc['online_image'];?>'); background-size:100% 100%;">
                        <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 90px;margin-top: 120px;margin-left: 231px;">

                    </div>
                    <div class="right photo" style="font-size: 12px;height: auto;padding: 10px 0px;    width:100%;">
                       &nbsp; Name : <?php echo $student[0]['first_name']?> <?php echo $student[0]['last_name']?>
                        <br>
                        &nbsp; Roll No : <?php echo $student[0]['roll_no']?>
                    </div>
                </div>

            <?php } ?>
            </div>
        </div>

    <?php } ?>


    <?php
}
?>

<?php
if($type == "B-Form"){
    ?>

    <?php
    foreach($student_documents as $doc){
        $this->db->select('students.*, campuses.campus_id, campuses.campus_name,campuses.website, campuses.logo,campuses.phone2,campuses.phone1, campuses.address as campus_address, campuses.phone,campuses.stamp,classes.name');
        $this->db->from('students');
        $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
        $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
        $this->db->where('students.student_id', $doc['student_id']);


        $student = $this->db->get()->result_array();
        ?>
        <div class="container">
            <br>

            <?php for($i = 0; $i < $doc_qty; $i++){?>
                <div class="result_card">
                    <img src="<?php echo base_url()?>uploads/<?php echo @$doc['image'];?>" style="width:110%;height: 1100px;margin-top: 25px;margin-left: -40px">
                    <img src="<?php echo base_url()?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: -100px;margin-left: 614px;">
                </div>
                <div class="right photo" style="font-size: 12px;height: auto;padding: 10px 0px;    width: 100%;">
                    &nbsp; Name : <?php echo $student[0]['first_name']?> <?php echo $student[0]['last_name']?>
                    <br>
                    &nbsp; Roll No : <?php echo $student[0]['roll_no']?>
                </div>
            <?php } ?>
        </div>

    <?php } ?>


    <?php
}
?>


</body>
</html>