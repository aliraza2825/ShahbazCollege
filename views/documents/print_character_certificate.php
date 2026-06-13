<html>
	<head>
		<title>Print Character Certificate</title>
		<style>
			*{
				margin:0;
				padding:0;
			}
			.container{
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
				height:940px;
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
	<?php foreach($student as $students): ?>
		<div class="container">
			<div class="header">
				<div class="left logo">
					<img src="<?php echo base_url();?>uploads/<?php echo $students['logo'];?>" width="100%" alt="" />
				</div>
				<div class="left college_name text-center">
					<h1 style="text-transform: uppercase;"><?php echo $students['campus_name'];?></h1>
					<br />
					<p>Email : info@<?php echo $students['website'];?></p>
				</div>
				<div class="clear"></div>
				<div class="line-thick"></div>
				<div class="line-normal"></div>
				<div class="line-thin"></div>
			</div>
			<div class="body">
				<div class="left ref_no">
					<p>Ref No. <span class="underline"><?php echo date('Ymd',strtotime($students['registration_date']));?></span></p>
				</div>
				<div class="left dated">
					
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
                    <div style="height: 50px;width: 80px;margin-left: 670px;margin-top:0px;"></div>
                    <p style="font-size:18px; text-align:right;">Chief Executive/Principal</p><br />
                    <p style="font-size:18px; text-align:right;"><?php echo $student[0]['campus_name'];?></p>
                    <img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: -180px;margin-left: 655px;">
				</div>
			</div>
			<div class="footer text-center">
				<h4><?php echo $student[0]['address'];?></h4>
				<h4>Phone : <?php echo $students['phone5'];?> -- <?php echo $students['phone6'];?></h4>
			</div>
		</div>
		<?php endforeach; ?>
	</body>
</html>



