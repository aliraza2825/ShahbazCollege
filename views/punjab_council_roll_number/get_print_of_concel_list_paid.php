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
                        <p style="font-weight: bolder;font-size: 18px"><?php echo $type ?> Exam : <?php echo $exam_no ?>  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </p>
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
                                Last Exam NO / Roll#
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
                            ?>
                            <tr class="odd gradeX">
                                <td >
                                    <?php echo $i;?>
                                </td>
                                <td>
                                    <?php echo $list['roll_no'];?>
                                </td>
                                <td>
                                    <?php
                                    $lastCouncilexam = $this->db->order_by("id","DESC")->
                                    get_where('punjab_council_roll_number',array('cnic'=>@$list['cnic']))->row();
                                    echo @$lastCouncilexam->council_exam_no.'/'.@$lastCouncilexam->roll_no;
                                    ?>
                                </td>
                                <td>
                                    <?php echo $list['cnic'];?>
                                </td>
                                <td>
                                    <?php echo $list['name'];?>
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