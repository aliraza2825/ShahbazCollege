<html>
	<head>
		<title>Print Covering Letter</title>
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
				background-image: url("<?php echo base_url();?>uploads/<?php echo @$campus[0]['logo'];?>");
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
            td{
                border: 1px solid black;
                text-align: left;
                width: 300px;
                padding-left: 10px;
            }
			
		</style>
	</head>
	<body>
    <?php
    $this->db->select('teacher_documents.image');
    $this->db->from('teacher_documents');
    $this->db->join('users','teacher_documents.teacher_id = users.user_id','inner');
    $this->db->where( array('users.designation_id'=>'26', 'users.campus_id' => $campus[0]['campus_id']));

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
					<img src="<?php echo base_url();?>uploads/<?php echo $campus[0]['logo'];?>" width="100%" alt="" />
				</div>
				<div class="left college_name text-center">
					<h1 style="text-transform: uppercase;"><?php echo $campus[0]['campus_name'];?></h1>
					<br />
					<p>Email : info@<?php echo $campus[0]['website'];?></p>
				</div>
				<div class="clear"></div>
				<div class="line-thick"></div>
				<div class="line-normal"></div>
				<div class="line-thin"></div>
			</div>
			<div class="body">
				<div class="left ref_no">
					<p>Ref No. <span class="underline"><?php echo date('Ymd');?></span></p>
				</div>
				<div class="left dated">
                    <p>Dated. <span class="underline"><?php echo date('d-m-Y');?></span></p>
				</div>
				<div class="clear"></div>
				<div>
                    <br />
					<p>THE  REGISTRAR</p>
                    <p>PUNJAB PHARMACY</p>
                    <p>COUNCIL LAHORE.</p>
                    <br />
                    <br />
                    <p class="text-center">SUBJECT: <span class="underline">SUBMISSION OF ADMISSION FILES</span></p>
					<br />
                    <p>Sir,</p>
					<br />
                    <p>It is stated that Admission files for 1st  Year / Supplementary  exams are being submitted to Punjab Pharmacy council. Details are given as below:</p>
                    <br />
                    <div  class="text-center" style="margin-left: 80px;">
                    <table>
                        <tr><td>Payment Method</td><td></td></tr>
                        <tr><td>Bank Name</td><td></td></tr>
                        <tr><td>Amount</td><td></td></tr>
                        <tr><td>Draft No.</td><td></td></tr>
                        <tr><td>Total Students</td><td></td></tr>
                        <tr><td>1st Year Annual</td><td></td></tr>
                        <tr><td>2nd Year Annual</td><td></td></tr>
                        <tr><td>1st Year Supplementary</td><td></td></tr>
                        <tr><td>2nd Year Supplementary</td><td></td></tr>
                    </table>
                    </div>
                    <br /><br />
                    <br /><br />
                    <br /><br />
                    <br /><br />
                    <br /><br />
                    <div  class="text-center"></div>
                    <p style="font-size:18px; text-align:right;">Chief Executive/Principal</p><br />
                    <p style="font-size:18px; text-align:right;"><?php echo $campus[0]['campus_name'];?></p>
                    <img src="<?php echo base_url() ?>uploads/<?php echo $resp[0]['image'];?>" style="height: 100px;margin-top: -180px;margin-left: 540px;">
					<img src="<?php echo base_url() ?>uploads/<?php echo $campus[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: -180px;margin-left: 655px;">
				</div>
			</div>
			<div class="footer text-center">
				<h4><?php echo $campus[0]['address'];?></h4>
				<h4>Phone : <?php echo $campus[0]['phone1'];?> -- <?php echo $campus[0]['phone2'];?></h4>
			</div>
		</div>
		<div style="clear:both;"></div>
	</body>
</html>