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
            	<p class="copies">Fee Challan - Student Copy</p>
            </div>
            <p class="bank" style="text-align:left; margin-top:15px;">Challan # : <?php echo $challan['challan_no']?></p>
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
                        <td>Rs. <?php echo $challan['amount'];?></td>
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
                    	<td><strong>Net Payable Amount</strong></td>
                        <td><strong>Rs. <?php echo $challan['amount']+$challan['extra_amount'];?></strong></td>
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
            	<p class="copies">Fee Challan - College Copy</p>
            </div>
            <p class="bank" style="text-align:left; margin-top:15px;">Challan # : <?php echo $challan['challan_no']?></p>
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
                        <td>Rs. <?php echo $challan['amount'];?></td>
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
                    	<td><strong>Net Payable Amount</strong></td>
                        <td><strong>Rs. <?php echo $challan['amount']+$challan['extra_amount'];?></strong></td>
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