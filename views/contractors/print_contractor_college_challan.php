<html>
<?php
$challan_date = date_create($challans[0]['dead_line']);
$today_date = date_create($challans[0]['paid_date']);
$diff=date_diff($challan_date,$today_date);
$difference = $diff->format("%R%a");

if($difference>0)
{
    if($challans[0]['payment_plan']=='24 Installments')
    {
        $fee_fine = $difference*10;
    }
    else
    {
        $fee_fine = $difference*50;
    }
}
else
{
    $fee_fine = 0;
}
?>
<head>
	<title>Challan</title>
    <style>
    	*{
			margin:0;
			padding:0;
			font-family:Tahoma, Geneva, sans-serif;
		}
		.clear
		{
			clear:both;
		}
		.center{
			text-align:center;
		}
		.container{
			width:1206px;

			height:auto;
			margin:0 auto;
			padding:50px 10px;
		}
		.segment{
			width:550px;
			float:left;
			padding:0px 10px;
			display:inline-block;
			border-right:2px dotted #000;
		}
		.no-border{
			border:none;
		}
		.title{
			text-align:center;
			font-size:14px;
			font-weight:bold;
		}
		.address{
			text-align:center;
			font-size:12px;
			margin-top:5px;
		}
		.copies-container{
			background-color:#dddddd;
			color:#000;
			padding:5px;
			margin-top:5px;
		}
		.copies{
			text-align:center;
			font-size:12px;
			text-decoration:underline;
			font-weight:bold;
		}
		.bank{
			font-weight:bold;
			text-align:center;
			font-size:12px;
			margin-top:3px;
		}
		.college-address{
			text-align:center;
			font-size:11px;
			margin-top:3px;
		}
		table {
			font-family: arial, sans-serif;
			border-collapse: collapse;
			width: 100%;
			font-size:12px;
			margin-top:20px;
		}

		td, th {
			border: 1px solid #dddddd;
			text-align: left;
			padding: 8px;
		}

		tr:nth-child(even) {
			background-color: #dddddd;
		}
		.description{
			font-size:10px;
			margin-top:5px;
		}
    </style>
</head>
<body>
    <div class="container">
    	<div class="segment">
        	<?php
            	foreach($challans as $challan):
			?>
        	<p class="title">SHAHBAZ COLLEGE OF PHARMACY TECHNICIAN</p>
            <br />
            <p class="address">413 Gulshan Block,Al Town, Lahore</p>
            <div class="copies-container">
            	<p class="copies">Fee Challan - Contractor Copy</p>
            </div>
            <p class="bank" style="text-align:left; margin-top:15px;">Challan # : <?php

                if ($challan['paid_challans'] == null) {

                    echo $challan['challan_no'];

                }else{

                    echo $challan['paid_challans'];

                }
                ?></p>
            <p class="bank" style="text-align:left;">Last Date : <?php echo date('d M Y', strtotime($challan['dead_line']));?></p>
            <p class="bank" style="text-align:left;">Contractor Name : <?php echo $challan['contractor_name'];?></p>
            <table width="100%">
            	<thead>
                	<tr>
                    	<th colspan="2">FEE DETAILS</th>
                    </tr>
                </thead>
                <tbody>
                	<tr>
                    	<td class="center"><strong>Description</strong></td>
                        <td class="center"><strong>Amount</strong></td>
                    </tr>
                    <tr>
                    	<td>Installment Fee</td>
                        <?php

                            $totalpayable=0;
                            if ($challan['merged_challan'] != null ){ ?>

                        <td>

                            <?php

                            $payment_ids = rtrim($challan['paid_challans'], ", ");

                            $this->db->select('payments.*');
                            $this->db->from('payments');
                            $mergchalans=$this->db->where_in('payments.challan_no', explode(',', $payment_ids))->get()->result_array();


                            foreach ($mergchalans as  $key => $merg ){

                                $totalpayable+=$merg['amount'];
                                ?>

                                <?php
                                if ($key == sizeof($mergchalans)-1){
                                    echo $merg['amount'];

                                }else
                                    echo $merg['amount'].' + ';?>

                                <?php

                            }?>

                        </td>

                        <?php

                        }


                        else {

                            $totalpayable=$challan['amount'];
                            ?>

                            <td>Rs. <?php echo $challan['amount'];?></td>

                        <?php } ?>

                        </td>
                    </tr>
                    <tr>
                    	<td>Previous Dues</td>
                        <td>Rs. 0</td>
                    </tr>
                    <tr>
                    	<td>Late Fee Fine</td>
                        <td>Rs. 0</td>
                    </tr>
                    <tr>
                        <td>Discount Amount</td>
                        <td>Rs. <?php

                            if ($challan['discount'] > 0){

                                echo $challan['discount'];

                            }else {

                                echo '0';
                            } ?> </td>
                    </tr>
                    <tr>
                    	<td><strong>Net Payable Amount</strong></td>
                        <td><strong> <?php



                                if ($challan['paid_challans'] != null){
                                    $challancount = rtrim($challan['paid_challans'], ", ");
                                    $array = explode(',' ,$challancount);



                                    echo $totalpayable+$challan['remaining_installment_amount']+$challan['extra_amount'];

                                }else {

                                    echo $challan['amount'] + $challan['remaining_installment_amount'] + $challan['extra_amount'] ;
                                }?></strong></td>
                    </tr>
                </tbody>
            </table>
            <p class="bank" style="text-align:left; margin-top:10px;">Rules &amp; Regulations</p>
            <p class="description">Fee Should be deposited before 10th of every month.</p>
            <?php
            	if($challan['payment_plan']=='24 Installments')
				{
					$fine=10;
				}
				else
				{
					$fine=50;
				}
			?>
            <p class="bank" style="text-align:left; margin-top:10px;">After due date late fine Rs.<?php echo $fine;?>/day will charge.</p>
            <p class="description">Students must keep safe the fee receipts for record.</p>
            <p class="description">All fees are non-refundable and non-transferable.</p>
            <p class="description">Students must ask for receipt on depositing fee from Accounts.</p>
            <p class="description">This receipt is valid for Shahbaz College Lahore branch only.</p>
            <p class="description">For further details contact: 03158042977</p>
            <?php
            	endforeach;
			?>
        </div>
        <div class="segment no-border">
            <?php
            foreach($challans as $challan):
                ?>
                <p class="title">SHAHBAZ COLLEGE OF PHARMACY TECHNICIAN</p>
                <br />
                <p class="address">413 Gulshan Block,Al Town, Lahore</p>
                <div class="copies-container">
                    <p class="copies">Fee Challan - Student Copy</p>
                </div>
                <p class="bank" style="text-align:left; margin-top:15px;">Challan # : <?php

                    if ($challan['paid_challans'] == null) {

                        echo $challan['challan_no'];

                    }else{

                        echo $challan['paid_challans'];

                    }
                    ?></p>
                <p class="bank" style="text-align:left;">Last Date : <?php echo date('d M Y', strtotime($challan['dead_line']));?></p>
                <p class="bank" style="text-align:left;">Contractor Name : <?php echo $challan['contractor_name'];?></p>
                <table width="100%">
                    <thead>
                    <tr>
                        <th colspan="2">FEE DETAILS</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="center"><strong>Description</strong></td>
                        <td class="center"><strong>Amount</strong></td>
                    </tr>
                    <tr>
                        <td>Installment Fee</td>
                        <?php

                            $totalpayable=0;
                            if ($challan['merged_challan'] != null ){ ?>

                        <td>

                            <?php

                            $payment_ids = rtrim($challan['paid_challans'], ", ");

                            $this->db->select('payments.*');
                            $this->db->from('payments');
                            $mergchalans=$this->db->where_in('payments.challan_no', explode(',', $payment_ids))->get()->result_array();


                            foreach ($mergchalans as  $key => $merg ){

                                $totalpayable+=$merg['amount'];
                                ?>

                                <?php
                                if ($key == sizeof($mergchalans)-1){
                                    echo $merg['amount'];

                                }else
                                    echo $merg['amount'].' + ';?>

                                <?php

                            }?>

                        </td>

                        <?php

                        }


                        else {

                            $totalpayable=$challan['amount'];
                            ?>

                            <td>Rs. <?php echo $challan['amount'];?></td>

                        <?php } ?>

                        </td>
                    </tr>
                    <tr>
                        <td>Previous Dues</td>
                        <td>Rs. 0</td>
                    </tr>
                    <tr>
                        <td>Late Fee Fine</td>
                        <td>Rs. 0</td>
                    </tr>
                    <tr>
                        <td>Discount Amount</td>
                        <td>Rs. <?php

                            if ($challan['discount'] > 0){

                                echo $challan['discount'];

                            }else {

                                echo '0';
                            } ?> </td>
                    </tr>
                    <tr>
                        <td><strong>Net Payable Amount</strong></td>
                        <td><strong> <?php



                                if ($challan['paid_challans'] != null){
                                    $challancount = rtrim($challan['paid_challans'], ", ");
                                    $array = explode(',' ,$challancount);



                                    echo $totalpayable+$challan['remaining_installment_amount']+$challan['extra_amount'];

                                }else {

                                    echo $challan['amount'] + $challan['remaining_installment_amount'] + $challan['extra_amount'] ;
                                }?></strong></td>
                    </tr>
                    </tbody>
                </table>
                <p class="bank" style="text-align:left; margin-top:10px;">Rules &amp; Regulations</p>
                <p class="description">Fee Should be deposited before 10th of every month.</p>
                <?php
                if($challan['payment_plan']=='24 Installments')
                {
                    $fine=10;
                }
                else
                {
                    $fine=50;
                }
                ?>
                <p class="bank" style="text-align:left; margin-top:10px;">After due date late fine Rs.<?php echo $fine;?>/day will charge.</p>
                <p class="description">Students must keep safe the fee receipts for record.</p>
                <p class="description">All fees are non-refundable and non-transferable.</p>
                <p class="description">Students must ask for receipt on depositing fee from Accounts.</p>
                <p class="description">This receipt is valid for Shahbaz College Lahore branch only.</p>
                <p class="description">For further details contact: 03158042977</p>
            <?php
            endforeach;
            ?>
        </div>
        <div class="clear"></div>
    </div>
</body>
</html>