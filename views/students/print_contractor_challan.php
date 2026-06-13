<?php

	$challan_date = date_create($challans[0]['dead_line']);
	$today_date = date_create(date('Y-m-d'));
	$diff=date_diff($challan_date,$today_date);
	$difference = $diff->format("%R%a");
	//echo $difference;
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
<html>
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
			width:380px;
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
        	<div class="copies-container">
            	<p class="copies">Fee Challan - College Copy</p>
            </div>
            <br />
            <p class="title"><?php echo $challan['campus_name'];?></p>
            <p class="address"><?php echo $challan['address'];?></p>
            <div class="copies-container">
            	<p class="copies"><?php echo $challan['bank_name'];?></p>
            </div>
            <p class="bank"><?php echo $challan['account_no'];?></p>
            <div class="copies-container">
            	<p class="copies">Payable at any branch of <?php echo $challan['bank_name'];?></p>
            </div>
            <table>
            	<tbody>
                	<tr>
                    	<td>Challan # : <?php echo $challan['challan_no']?></td>
                        <td>Last Date : <?php echo date('M d, Y', strtotime($challan['dead_line']));?></td>
                    </tr>
                    <tr>
                    	<td colspan="2">Contractor Name : <strong><?php echo $challan['contractor_name']?></strong> Contract Name : <?php echo $challan['contract_name']?></td>
                    </tr>
                </tbody>
            </table>
            
            <table width="100%">
                <tbody>
                	<tr>
                    	<td class="center"><strong>Particulars</strong></td>
                        <td class="center"><strong>Amount</strong></td>
                    </tr>
                    <tr>
                    	<td>Installment Fee</td>
                        <td>Rs. <?php echo $challan['amount'];?></td>
                    </tr>
                    <tr>
                    	<td>Previous Dues</td>
                        <td>Rs. <?php echo $challan['extra_amount'];?></td>
                    </tr>
                    <tr>
                    	<td>Late Fee Fine</td>
                        <td>Rs. <?php echo $fee_fine;?></td>
                    </tr>
                    <tr>
                    	<td>Bank Charges</td>
                        <td>Rs. <?php $bank_charges = 15; echo $bank_charges;?></td>
                    </tr>
                    <tr>
                    	<td><strong>Net Payable Amount</strong></td>
                        <td><strong>Rs. <?php echo $challan['amount']+$fee_fine+$challan['extra_amount']+$bank_charges;?></strong></td>
                    </tr>
                </tbody>
            </table>
            <p class="bank" style="text-align:left; margin-top:10px;">Rules &amp; Regulations</p>
            <p class="description">Bank charges will be paid by contractor.</p>
            <p class="description">Fee Should be deposited before 10th of every month.</p>
            <p class="description">After due date late fine Rs 50 per day will be charged.</p>
            <p class="description">Students must keep safe the fee receipt for records.</p>
            <p class="description">Students must ask for receipt on depositing fee from Accounts.</p>
            <p class="description">All fee are non-refundable / non-transferable.</p>
            <p class="description">For further details contact: 03158042977</p>
            <br /><br /><br />
            <div style="color:#fff; background-color:#000; padding:5px; float:right; display:inline;">
            	Bank Stamp
            </div>
            <div class="clear"></div>
            <br />
            <hr />
            <p class="description"><?php echo $challan['note'];?></p>
            <?php
            	endforeach;
			?>
        </div>
        
        <div class="segment">
        	<?php
            	foreach($challans as $challan):
			?>
        	<div class="copies-container">
            	<p class="copies">Fee Challan - Student Copy</p>
            </div>
            <br />
            <p class="title"><?php echo $challan['campus_name'];?></p>
            <p class="address"><?php echo $challan['address'];?></p>
            <div class="copies-container">
            	<p class="copies"><?php echo $challan['bank_name'];?></p>
            </div>
            <p class="bank"><?php echo $challan['account_no'];?></p>
            <div class="copies-container">
            	<p class="copies">Payable at any branch of <?php echo $challan['bank_name'];?></p>
            </div>
            <table>
            	<tbody>
                	<tr>
                    	<td>Challan # : <?php echo $challan['challan_no']?></td>
                        <td>Last Date : <?php echo date('M d, Y', strtotime($challan['dead_line']));?></td>
                    </tr>
                    <tr>
                    	<td colspan="2">Contractor Name : <strong><?php echo $challan['contractor_name']?></strong> Contract Name : <?php echo $challan['contract_name']?></td>
                    </tr>
                </tbody>
            </table>
            
            <table width="100%">
                <tbody>
                	<tr>
                    	<td class="center"><strong>Particulars</strong></td>
                        <td class="center"><strong>Amount</strong></td>
                    </tr>
                    <tr>
                    	<td>Installment Fee</td>
                        <td>Rs. <?php echo $challan['amount'];?></td>
                    </tr>
                    <tr>
                    	<td>Previous Dues</td>
                        <td>Rs. <?php echo $challan['extra_amount'];?></td>
                    </tr>
                    <tr>
                    	<td>Late Fee Fine</td>
                        <td>Rs. <?php echo $fee_fine;?></td>
                    </tr>
                    <tr>
                    	<td>Bank Charges</td>
                        <td>Rs. <?php $bank_charges = 15; echo $bank_charges;?></td>
                    </tr>
                    <tr>
                    	<td><strong>Net Payable Amount</strong></td>
                        <td><strong>Rs. <?php echo $challan['amount']+$fee_fine+$challan['extra_amount']+$bank_charges;?></strong></td>
                    </tr>
                </tbody>
            </table>
            <p class="bank" style="text-align:left; margin-top:10px;">Rules &amp; Regulations</p>
            <p class="description">Bank charges will be paid by contractor.</p>
            <p class="description">Fee Should be deposited before 10th of every month.</p>
            <p class="description">After due date late fine Rs 50 per day will be charged.</p>
            <p class="description">Students must keep safe the fee receipt for records.</p>
            <p class="description">Students must ask for receipt on depositing fee from Accounts.</p>
            <p class="description">All fee are non-refundable / non-transferable.</p>
            <p class="description">For further details contact: 03158042977</p>
            <br /><br /><br />
            <div style="color:#fff; background-color:#000; padding:5px; float:right; display:inline;">
            	Bank Stamp
            </div>
            <div class="clear"></div>
            <br />
            <hr />
            <p class="description"><?php echo $challan['note'];?></p>
            <?php
            	endforeach;
			?>
        </div>
        
        <div class="segment no-border">
        	<?php
            	foreach($challans as $challan):
			?>
        	<div class="copies-container">
            	<p class="copies">Fee Challan - Bank Copy</p>
            </div>
            <br />
            <p class="title"><?php echo $challan['campus_name'];?></p>
            <p class="address"><?php echo $challan['address'];?></p>
            <div class="copies-container">
            	<p class="copies"><?php echo $challan['bank_name'];?></p>
            </div>
            <p class="bank"><?php echo $challan['account_no'];?></p>
            <div class="copies-container">
            	<p class="copies">Payable at any branch of <?php echo $challan['bank_name'];?></p>
            </div>
            <table>
            	<tbody>
                	<tr>
                    	<td>Challan # : <?php echo $challan['challan_no']?></td>
                        <td>Last Date : <?php echo date('M d, Y', strtotime($challan['dead_line']));?></td>
                    </tr>
                    <tr>
                    	<td colspan="2">Contractor Name : <strong><?php echo $challan['contractor_name']?></strong> Contract Name : <?php echo $challan['contract_name']?></td>
                    </tr>
                </tbody>
            </table>
            
            <table width="100%">
                <tbody>
                	<tr>
                    	<td class="center"><strong>Particulars</strong></td>
                        <td class="center"><strong>Amount</strong></td>
                    </tr>
                    <tr>
                    	<td>Installment Fee</td>
                        <td>Rs. <?php echo $challan['amount'];?></td>
                    </tr>
                    <tr>
                    	<td>Previous Dues</td>
                        <td>Rs. <?php echo $challan['extra_amount'];?></td>
                    </tr>
                    <tr>
                    	<td>Late Fee Fine</td>
                        <td>Rs. <?php echo $fee_fine;?></td>
                    </tr>
                    <tr>
                    	<td>Bank Charges</td>
                        <td>Rs. <?php $bank_charges = 15; echo $bank_charges;?></td>
                    </tr>
                    <tr>
                    	<td><strong>Net Payable Amount</strong></td>
                        <td><strong>Rs. <?php echo $challan['amount']+$fee_fine+$challan['extra_amount']+$bank_charges;?></strong></td>
                    </tr>
                </tbody>
            </table>
            <p class="bank" style="text-align:left; margin-top:10px;">Rules &amp; Regulations</p>
            <p class="description">Bank charges will be paid by contractor.</p>
            <p class="description">Fee Should be deposited before 10th of every month.</p>
            <p class="description">After due date late fine Rs 50 per day will be charged.</p>
            <p class="description">Students must keep safe the fee receipt for records.</p>
            <p class="description">Students must ask for receipt on depositing fee from Accounts.</p>
            <p class="description">All fee are non-refundable / non-transferable.</p>
            <p class="description">For further details contact: 03158042977</p>
            <br /><br /><br />
            <div style="color:#fff; background-color:#000; padding:5px; float:right; display:inline;">
            	Bank Stamp
            </div>
            <div class="clear"></div>
            <br />
            <hr />
            <p class="description"><?php echo $challan['note'];?></p>
            <?php
            	endforeach;
			?>
        </div>
        
        <div class="clear"></div>
    </div>
</body>
</html>