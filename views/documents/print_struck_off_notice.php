<html>
	<head>
		<title>Print Struckoff Notice</title>
		<style>
			*{
				margin:0;
				padding:0;
			}
			.container{
				margin:0 auto;
				height:992px;
				width:765px;
				padding:20px;
				background-image:url('<?php echo base_url();?>print_images/noc_background.png');
				background-repeat:no-repeat;
				background-position:-55px bottom;
				background-size:30%;
				position:relative;
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
			.logo{
				width:20%;
			}
			.college_name{
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
			.body{
				width:100%;
				height:800px;
				/*background-image: url('<?php echo base_url();?>images/shahbaz.png');
				background-repeat: no-repeat;
				background-position: center;
				background-size:40%;	*/
			}
			.body::before {
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

			.ref_no{
				width:50%;
				margin-top:20px;
			}
			.dated{
				width:50%;
				margin-top:20px;
				text-align:right;
			}
			.underline{
				text-decoration:underline;
			}
			.footer{
				width:100%;
			}
			
		</style>
	</head>
	<body>
	
	<?php 

	$student_id=$student[0]['student_id']; 

    $this->db->select('students.*, campuses.campus_id, campuses.campus_name,campuses.website, campuses.logo,campuses.phone2,campuses.phone1, campuses.address as campus_address, campuses.phone,campuses.stamp,classes.name,classes.session');
    $this->db->from('students');
    $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
    $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
    $this->db->where_in('students.student_id', $student_id);

    $student = $this->db->get()->result_array();
    $photo = $this->db->get_where('student_documents', array('student_id'=>$student_id, 'type'=>'Photo'))->result_array();
    $result_card = $this->db->get_where('student_documents', array('student_id'=>$student_id, 'type'=>'Result Card'))->result_array();
    $id_card = $this->db->get_where('student_documents', array('student_id'=>$student_id, 'type'=>'ID Card'))->result_array();

    ?>
    <?php
    $this->db->select('teacher_documents.image');
    $this->db->from('teacher_documents');
    $this->db->join('users','teacher_documents.teacher_id = users.user_id','inner');
    $this->db->where( array('users.designation_id'=>'26', 'users.campus_id' => $student[0]['campus_id']));

    $resp= $this->db->get()->result_array();
    ?>
	
		<div class="container">
			<div class="header">
				<div class="left logo">
					<img src="<?php echo base_url();?>uploads/<?php echo $student[0]['logo'];?>" width="100%" alt="" />
				</div>
				<div class="left college_name text-center">
					<h1 style="text-transform: uppercase;"><?php echo $student[0]['campus_name'];?></h1>
					<br />
					<p>Email : info@<?php echo $student[0]['website'];?></p>
				</div>
				<div class="clear"></div>
				<div class="line-thick"></div>
				<div class="line-normal"></div>
				<div class="line-thin"></div>
			</div>
			<div class="body">
				<div class="left ref_no">
					<p>Ref No. <span class="underline"><?php echo date('Ymd',strtotime($student[0]['registration_date']));?></span></p>
				</div>
				<div class="left dated">
					<p>Date: <span class="underline"><?php echo date('M d, Y');?></span></p>
				</div>
				<div class="clear"></div>
				<div>
					<br /><br /><br /><br /><br />
					<h3 class="text-center">Struck Off Notice</h3>
					<br />
					<p style="font-size:18px;"><?php if($student[0]['gender']=='Male'){echo 'Mr.';}else{echo 'Ms.';}?> <span class="underline"><?php echo $student[0]['first_name'];?> <?php echo $student[0]['last_name'];?></span>
					
					<?php if($student[0]['gender']=='Male'){echo 'S/O';}else{echo 'D/O';}?> <span class="underline"><?php echo $student[0]['father_name'];?></span>
					</p>
					<br />
					<p style="font-size:18px;">It is hereby informed to you in regard to your admission status in <?php echo $student[0]['campus_name'];?>. You are struck off from <?php echo $student[0]['campus_name'];?> on the grounds of your continuous absence status and primarily on account of your non-payment of college fee on due time.</p>
					<br />
					<p style="font-size:18px;">If you wish to proceed as a student in <?php echo $student[0]['campus_name'];?>, make the payment of tuition fee urgently and contact on this number promptly . 
					<br /><br />
					Contact Number : <?php echo $student[0]['phone'];?> for further information</p>
					<br />
					<p style="font-size:18px; text-align:right;">Regards,<br /><br /><br /><br /><br /><br />Administrator</p>
					 <img src="<?php echo base_url() ?>uploads/<?php echo @$resp[0]['image']?>" style="height: 50px;width: 80px;margin-left: 670px;margin-top:0px;">
                    <p style="font-size:18px; text-align:right;">Principal</p>
                    <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: -157px;margin-left: 655px;">
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
		<div style="clear:both;"></div>
	</body>
</html>