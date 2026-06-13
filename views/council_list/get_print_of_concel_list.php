<html>
<?php
//include('qrcode/qrlib.php');


?>
	<head>
        <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
        <title>Print Councel</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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
                background-image: url(<?php echo base_url().'uploads/'.$campus[0]['logo']?>);
                background-size: 50%;
                background-position: 51% 75%;
                background-repeat: no-repeat;
                content: "";
                display: block;
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 1;
                opacity: 0.2;
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
            th{
                border: 2px solid black!important;
            }
		</style>
	</head>
	<body>
	<?php  $this->db->select('teacher_documents.image');
    $this->db->from('teacher_documents');
    $this->db->join('users','teacher_documents.teacher_id = users.user_id','inner');
    $this->db->where( array('users.designation_id'=>'26', 'users.campus_id' => $campus[0]['campus_id']));

    $resp= $this->db->get()->result_array(); ?>
	<img src="https://www.shahbazcollegeofpharmacy.edu.pk/lahore-campus/uploads/<?php echo $resp[0]['image'] ?>" style="width: 150px;float: right; margin-top: 5px;margin-right: 0px; ">

	<img src="https://www.shahbazcollegeofpharmacy.edu.pk/lahore-campus/uploads/<?php echo $campus[0]['stamp'] ?>" style="height: 100px;width: 180px;float: right;margin-top: 5px; display:inline;">
    
            <div class="container" style="margin:0 auto;

				padding:20px;
				background-position:-55px bottom;
				background-size:30%;
				position:relative;">

                <div class="body">
                    <div style="width: 100%;text-align: center" >
                        <h2><?php echo $campus[0]['campus_name'] ?></h2>
                        <p style="font-weight: bolder;font-size: 18px"><?php echo $classess[0]['name'] ?> &nbsp; &nbsp; &nbsp; Annual &nbsp; &nbsp; &nbsp; Exam No: <?php echo $classess[0]['exam_no'] ?> </p>
                    </div>
                    <table class="table table-bordered" >
                        <thead>
                        <tr>
                            <th >
                                Sr.No
                            </th>
                            <th>
                                Roll#
                            </th>
                            <th>
                                CNIC No
                            </th>
                            <th>
                                Student & Father Name
                            </th>
                            <th>
                                Postal Address
                            </th>
                            <th>
                                Student Mobile No
                            </th>
                            <th>
                                Board Name
                            </th>
                            <th>
                                Institute Contact No
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $i=1;
                        foreach($result as $list):
                            if($list['gender']=='Male')
                            {
                                $name = ucfirst(strtolower($list['first_name'])).' '.ucfirst(strtolower($list['last_name'])).'<br />S/O '.ucfirst(strtolower($list['father_name']));
                            }
                            else
                            {
                                $name = ucfirst(strtolower($list['first_name'])).' '.ucfirst(strtolower($list['last_name'])).'<br />D/O '.ucfirst(strtolower($list['father_name']));
                            }
                            ?>
                            <tr class="odd gradeX">
                                <td >
                                    <?php echo $i;?>
                                </td>
                                <td>
                                    <?php echo $list['roll_no'];?>
                                </td>
                                <td>
                                    <?php echo $list['cnic'];?>
                                </td>
                                <td>
                                    <?php echo $name;?>
                                </td>
                                <td>
                                    <?php echo $list['address'];?>
                                </td>
                                <td>
                                    <?php echo $list['mobile'];?>
                                </td>
                                <td>
                                    <?php echo $list['board'];?>
                                </td>
                                <td>
                                    <?php echo $list['institute'];?>
                                </td>
                            </tr>
                            <?php
                            $i++;
                        endforeach;
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>


	</body>
</html>