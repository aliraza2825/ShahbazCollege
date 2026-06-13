<html>
	<head>
		<title>Print Admission Form</title>
		<style>
			*{
				margin:0;
				padding:0;
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
	foreach($students as $student):
	
	$student_id=$student['student_id']; 

    $this->db->select('students.*, campuses.campus_id, campuses.campus_name,campuses.website, campuses.logo,campuses.phone2,campuses.phone1, campuses.address as campus_address, campuses.phone,campuses.stamp,classes.name,classes.session,courses.course_name');
    $this->db->from('students');
    $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
    $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
	$this->db->join('courses','courses.course_id=students.course_id','inner');
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
	
		<div class="container" style="margin:0 auto;
				height:992px;
				width:765px;
				padding:20px;
				background-image:url('<?php echo base_url();?>print_images/noc_background.png');
				background-repeat:no-repeat;
				background-position:-55px bottom;
				background-size:30%;
				position:relative;">
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
					
				</div>
				<div class="clear"></div>
				<div>
					<br /><br /><br /><br /><br />
					<h3 class="text-center">Admission letter</h3>
					<br />
					Dear <?php echo $student[0]['first_name'];?> <?php echo $student[0]['last_name'];?>,
					<br /><br />
					<p style="font-size:18px;">
						Congratulations! We are pleased to inform you that you have been admitted to <?php echo $student[0]['campus_name'];?> for the upcoming academic year (<?php echo $student[0]['session'];?>). Your academic achievements and passion for <?php echo $student[0]['course_name'];?> have truly impressed us.
					</p>
					<br />
					<p style="font-size:18px;">
						We believe that your unique perspective and talents will contribute to the vibrant community at <?php echo $student[0]['campus_name'];?>. We look forward to supporting your academic journey and witnessing the positive impact we know you'll have on our campus.
					</p>
					<br />
					<p>
						Welcome to <?php echo $student[0]['campus_name'];?>! We are excited to have you as part of our community.
					</p>
					
					<br /><br /><br /><br />
                    <div style="height: 50px;width: 80px;margin-left: 670px;margin-top:0px;"></div>
                    <p style="font-size:18px; text-align:right;">Chief Executive/Principal</p><br />
                    <p style="font-size:18px; text-align:right;"><?php echo $student[0]['campus_name'];?></p>
                    <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: -180px;margin-left: 655px;">
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
		<?php
			endforeach;
		?>
	</body>
</html>