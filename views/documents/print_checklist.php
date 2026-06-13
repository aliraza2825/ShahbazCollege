<html>
	<head>
		<title>Print CheckList</title>
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
    <?php
    $this->db->select('teacher_documents.image');
    $this->db->from('teacher_documents');
    $this->db->join('users','teacher_documents.teacher_id = users.user_id','inner');
    $this->db->where( array('users.designation_id'=>'26', 'users.campus_id' => $student[0]['campus_id']));
    $resp= $this->db->get()->result_array();
    ?>
	<?php foreach($student as $key=>$students):?>
	
		<div class="container">
			<div class="header">
				<div class="text-center">
					<h3 style="text-transform: uppercase; font-style: italic;">CHECK LIST/UNDERTAKING</h3>
					<div class="clear"></div>
                    <div class="line-thin"></div>
					<h3  style="text-transform: uppercase; font-style: italic;">(PHARMACY TECHNICIAN ADMISSION)</h3>
				</div>

			</div>
			<div class="body">
				<div class="clear"></div>
				<div>
					<br />
					<p style="font-size:18px;">I Principle / Director of <span class="underline">&nbsp;&nbsp;&nbsp;<strong><?php echo $students['campus_name'];?></strong>&nbsp;&nbsp;&nbsp;</span> undertake that the said student </p>
                    <br />
					<p style="font-size:18px;">fulfills the admission criteria of Pharmacy Technician Program. His /  Her admission form and documents</p>
                    <br />
                    <p style="font-size:18px;">are thoroughly  checked and verified as per detail mentioned below. All the documents are attached </p>
                    <br />
                    <p style="font-size:18px;">according to the sequence given in the checklist .</p>
                    <br />
					<p style="font-size:18px;"><strong>Student's Name:</strong><span class="underline" style="min-width: 120px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $students['first_name']." ".$students['last_name'];?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> <strong>List Sr No:</strong><span class="underline" style="min-width: 120px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $key+1;?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Session:</strong><span class="underline" style="min-width: 120px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $students['session'];?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></p>
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
                    <br />
                    <p style="font-size:18px;">I <span class="underline">&nbsp;&nbsp;&nbsp;<strong><?php echo $students['first_name']." ".$students['last_name'];?></strong>&nbsp;&nbsp;&nbsp;</span> hereby further declare that the details furnished above are true and best of </p>
                    <br />
                    <p style="font-size:18px;">my knowledge.In case of any discrepancy/short coming <strong>Punjab Pharmacy Council, Lahore</strong> have right </p>
                    <br />
                    <p style="font-size:18px;">to cancel the admission.I also understand that in case of bogus/counterfeit/forged/tampered documents the </p>
                    <br />
                    <p style="font-size:18px;">Punjab Pharmacy Council,Lahore is fully authorized to cancelled the Enrollment/Registration of the</p>
                    <br />
                    <p style="font-size:17px;"> student at any stage of the said course,even after passing the exams and getting the registration in register B.</p>
                    <br />
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
                <div class="row" style="margin-top: 10px;">
                    <div class="column"><img src="<?php echo base_url() ?>uploads/<?php echo $resp[0]['image']?>" style="height: 50px;width: 80px;margin-left: 31px;margin-top:0px;">__________________<img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: -157px;margin-left: 0px;"><br /><strong>Signature & Thumb</strong><br />(Student)</div>
                    <div class="column"><img src="<?php echo base_url() ?>uploads/<?php echo $resp[0]['image']?>" style="height: 50px;width: 80px;margin-left: 31px;margin-top:0px;">__________________<img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: -157px;margin-left: 0px;"><br /><strong>Signature & Thumb</strong><br />(Stamp of the College Principal)</div>
                    <div class="column"><img src="<?php echo base_url() ?>uploads/<?php echo $resp[0]['image']?>" style="height: 50px;width: 80px;margin-left: 31px;margin-top:0px;">__________________<img src="<?php echo base_url() ?>uploads/<?php echo $student[0]['stamp'];?>" style="width: 150px;height: 100px;margin-top: -157px;margin-left: 0px;"><br /><strong>Signature & Thumb</strong><br />(Stamp of the College CEO/Director)</div>
                </div>
			</div>

		</div>
	<?php endforeach;  echo $students['roll_no']; ?>

	</body>
</html>