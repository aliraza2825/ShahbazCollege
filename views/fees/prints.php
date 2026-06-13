<html>
<head>
	<title>Due Fees</title>
    <style>
    	/**{
			font-family:Verdana, Geneva, sans-serif;
		}
		table{
			text-align:center;
		}
		th, td {
			padding: 15px;
			text-align: left;
			border-bottom: 1px solid #ddd;
		}*/
		td{
			padding:15px !important;
		}
    </style>
    <link href="<?php echo base_url();?>assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<div style="margin:0 auto; text-align:center; width:980px;">
<h3><?php echo @$dues[0]['class_name'];?></h3>
<table style="width:100%;">
<thead>
<tr>
    <th>
         Roll No
    </th>
    <th>
         Name
    </th>
    <th>
         Mobile
    </th>
    <th>
         Fees
    </th>
    <th>
         Remaing Dues
    </th>
    <th>
         Last Date
    </th>
    <th>
         Manual Remarks
    </th>
</tr>
</thead>
<tbody>
<?php
    foreach($dues as $due):
	$class = '';
	if(date('Y-m-d')<= $due['dead_line'])
	{
		$class = 'alert alert-success';
	}
	else
	{
		$class = 'alert alert-danger';
	}
?>
<tr class="<?php echo $class;?>">
    <td>
        <?php echo $due['roll_no'];?>
    </td>
    <td>
        <?php echo $due['first_name'].' '.$due['last_name'];?>
    </td>
    <td>
        <?php echo $due['mobile']?>
        <hr>
        <?php echo $due['emergency_no']?>
    </td>
    <td>
        <?php echo $due['amount'];?>
    </td>
    <td>
        <?php echo $due['extra_amount'];?>
    </td>
    <td>
        <?php echo date('d F, Y', strtotime($due['dead_line']));?>
    </td>
    <td width="250">
        <?php
			$remarks = $this->db->get_where('fees_remarks', array('fee_id'=>$due['fee_id']))->result_array();
			foreach($remarks as $remark):
		?>
			<?php
				echo '<p>'.$remark['comment'].'</p>';
			?>
		<?php
			endforeach;
		?>
    </td>
</tr>
<?php
    endforeach;
?>
</tbody>
</table>
</div>
</body>
</html>