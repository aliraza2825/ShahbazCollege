<?php
	include('qrcode/qrlib.php'); 
	QRcode::png(current_url(), 'qr/'.$this->uri->segment(3).'.png');
	
	$photo = $this->db->get_where('student_documents',array('student_id'=>$this->uri->segment(3),'type'=>'Photo'))->result_array();
	
?>
<html>
	<head>
		<title>Print Diploma</title>
		<style>
			*{
				margin:0;
				padding:0;
			}
			.container{
				
				width:1010px;
				height:675px;
				background-color:#DDDDDD;
				padding:20px;
				background:url('<?php echo base_url();?>/print_images/background.png') 100% 100%;
				background-size: 100% 100%;
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
			.half{
				width:50%;
			}
			.full{
				width:100%;
			}
			.text-right{
				text-align:right;
			}
			.text-center{
				text-align:center;
			}
			.blue_color{
				color:#393185;
				text-transform:uppercase;
			}
			.logo-container{
				width:170px;
				margin:10px auto;
			}
			.student_name{
				margin-top:5px;
				text-decoration:underline;
				text-transform:uppercase;
			}
			.bold{
				font-weight:bold;
				margin-top:5px;
			}
			.eighty{
				width:55%;
			}
			.twenty{
				width:45%;
			}
			.requirements{
				font-size:18px;
				margin-top:10px;
			}
			.principal{
				font-size:12px;
				margin-top:10px;
			}
			.underline{
				text-decoration:underline;
			}
			.general_info{
			    padding:10px 0 10px 10px;
			}
			.barcode{
			    padding:10px 10px 10px 0;
			}
			.website{
				margin-left:50px;
			}
		</style>
	</head>
	<body>
		<div class="container">
			<div class="left general_info">
				<table>
					<tbody>
						<tr>
							<td>College Roll No</td>
							<td>:</td>
							<td><?php echo $student[0]['roll_no'];?></td>
						</tr>
						<tr>
							<td>Reference #</td>
							<td>:</td>
							<td><?php echo $student[0]['student_id'];?></td>
						</tr>
						<!--<tr>-->
						<!--	<td>Enrollment #</td>-->
						<!--	<td>:</td>-->
						<!--	<td><?php echo $student[0]['computer_no'];?></td>-->
						<!--</tr>-->
						<tr>
							<td>PPC Roll No</td>
							<td>:</td>
							<td><?php echo $student[0]['result_roll_no'];?></td>
						</tr>
						<tr>
							<td>ID Card #</td>
							<td>:</td>
							<td><?php echo $student[0]['cnic'];?></td>
						</tr>
						<tr></tr>
					<tbody>
				</table>
			</div>
			<div class="right text-right barcode">
				<?php
				    if(count($photo)>0):
				?>
				    <?php 
						if($photo[0]['online_image']==''):
					?>
					<img src="<?php echo base_url();?>uploads/<?php echo $photo[0]['image']?>" height="90" alt="" />
					<?php
						else:
					?>
					<img src="<?php echo str_replace($bucket_address,$cloudfront_address,$photo[0]['online_image']);?>" height="90" alt="" />
					<?php
						endif;
					?>
				<?php
				    else:
				?>
				    Image Not Found!
				<?php
				    endif;
				?>
			</div>
			<div class="clear"></div>
			<div class="full">
				<h1 class="text-center blue_color"><?php echo $student[0]['campus_name']?></h1>
				<p class="text-center">Established Under Pharmacy Act 1967, Approved By Pharmacy Council of Pakistan</p>
				<div class="logo-container" style="width: 143px;">
					<img src="<?php echo base_url();?>uploads/<?php echo $student[0]['logo']?>" alt="logo" width="100%" />
				</div>
				<p class="text-center">Upon Recommendation of faculty of Department of Pharmacy has conferred upon</p>
				<?php
					if($student[0]['gender']=='Male')
					{
						$gender='S/O';
					}
					else
					{
						$gender='D/O';
					}
				?>
				<h2 class="student_name text-center"><?php echo $student[0]['first_name'].' '.$student[0]['last_name'].' '.$gender.' '.$student[0]['father_name']?></h2>
				<p class="text-center bold">Diploma in Pharmacy Technician</p>
				<p class="text-center bold">To be registered in register B under Pharmacy Act 1967</p>
				<p class="text-center bold">Session held <span class="underline"><?php echo $student[0]['session'];?></span></p>
			</div>
			<div class="eighty left">
				<p class="bold requirements" style="margin-left:10px;">Incumbent has completely fulfilled all requirements for the prescribed studies and examination of Punjab Pharmacy Council Given under the seal of institute on date <span class="underline"><?php echo date('M d, Y',strtotime($student[0]['result_update_date']));?></span></p>
				<img style="margin-left:10px; margin-top:20px;" src="<?php echo base_url();?>qr/<?php echo $this->uri->segment(3);?>.png" width="80" alt="" />
			</div>
			<div class="twenty left">
				<div class="left half">
					<p class="text-center">
					<br />
					<img src="<?php echo base_url();?>print_images/principal_stamp.png" alt="" width="70%" />
					</p>
				</div>
				<div class="left half">
					<br />
					<br />
					<br />
					<br />
					<br />
					<br />
					<hr />
					<p class="principal text-center">Principal</p>
					<p class="text-center">Signature &amp; Stamp</p>
				</div>
				<p style="text-align:right; margin-right:3px;">www.<?php echo $student[0]['website'];?></p>
			</div>
			<div class="clear"></div>
		</div>
	</body>
</html>