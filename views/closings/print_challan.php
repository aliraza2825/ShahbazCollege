<?php
$myAccess = checkUserAccess();
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
	<div class="container" style="
    padding: 0px;
">
    	<div class="segment">
        	<div class="copies-container">
            	<p class="copies">Fee Challan - Bank Copy</p>
            </div>
            <br />
            <p class="title"><?php echo $campus->campus_name;?></p>
            <p class="address"><?php echo $campus->address?></p>
            <div class="copies-container">
            	<p class="copies">BANK AL HABIB LIMITED</p>
            </div>
            <p class="bank">0080-900358-01</p>
            <div class="copies-container">
            	<p class="copies">Payable at any branch of BANK AL HABIB LIMITED</p>
            </div>
            <table>
            	<tbody>
                	<tr>
                    	<td>Challan # : <?php echo $closing_id;?></td>
                        <td>Last Date : <?php echo date('M d, Y');?></td>
                    </tr>
                    <tr>
                    	<td>Name : <?php echo $campus->campus_name;?></td>
                        <td>F / N : </td>
                    </tr>
                    <tr>
                    	<td>Roll # : <?php echo $closing_id;?></td>
                        <td>Class : </td>
                    </tr>
                    <tr>
                        <td style="width: 37%">Merged Challans </td>
                        <td style="word-wrap:break-word;"><?php echo $closing_id?></td>
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
                        <td style="word-wrap:break-word;">Rs. <?php echo $amount;?></td>
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
                    	<td>Bank Charges</td>
                        <td>Rs. <?php $bank_charges = 15;?></td>
                    </tr>
                    <tr>
                        <td>Discount</td>
                        <td>Rs. 0</td>
                    </tr>
                    <tr>
                    	<td><strong>Net Payable Amount</strong></td>
                        <td><strong>Rs. <?php echo $amount;?></strong></td>
                    </tr>
                </tbody>
            </table>
            <p class="bank" style="text-align:left; margin-top:10px;">Rules &amp; Regulations</p>
            <p class="description">Students must keep safe the fee receipt for records.</p>
            <p class="description">Students must ask for receipt on depositing fee from Accounts.</p>
            <p class="description">All fee are non-refundable / non-transferable.</p>
            <p class="description">For further details contact: 03158042977</p>
            <br />
            <div style="color:#fff; background-color:#000; padding:5px; float:right; display:inline;">
            	Bank Stamp
            </div>
            <div class="clear"></div>
            <br />
            <hr />
            <p class="description">These funds are intended for Shahbaz College of Pharmacy Technician. Account # 0121-0980-003570-01-7 held with Badar Block Branch, Lahore</p>
        </div>
        <div class="segment">
            <div class="copies-container">
                <p class="copies">Fee Challan - College Copy</p>
            </div>
            <br />
            <p class="title"><?php echo $campus->campus_name;?></p>
            <p class="address"><?php echo $campus->address?></p>
            <div class="copies-container">
                <p class="copies">BANK AL HABIB LIMITED</p>
            </div>
            <p class="bank">0080-900358-01</p>
            <div class="copies-container">
                <p class="copies">Payable at any branch of BANK AL HABIB LIMITED</p>
            </div>
            <table>
                <tbody>
                <tr>
                    <td>Challan # : <?php echo $closing_id;?></td>
                    <td>Last Date : <?php echo date('M d, Y');?></td>
                </tr>
                <tr>
                    <td>Name : <?php echo $campus->campus_name;?></td>
                    <td>F / N : </td>
                </tr>
                <tr>
                    <td>Roll # : <?php echo $closing_id;?></td>
                    <td>Class : </td>
                </tr>
                <tr>
                    <td style="width: 37%">Merged Challans </td>
                    <td style="word-wrap:break-word;"><?php echo $closing_id?></td>
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
                    <td style="word-wrap:break-word;">Rs. <?php echo $amount;?></td>
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
                    <td>Bank Charges</td>
                    <td>Rs. <?php $bank_charges = 15;?></td>
                </tr>
                <tr>
                    <td>Discount</td>
                    <td>Rs. 0</td>
                </tr>
                <tr>
                    <td><strong>Net Payable Amount</strong></td>
                    <td><strong>Rs. <?php echo $amount;?></strong></td>
                </tr>
                </tbody>
            </table>
            <p class="bank" style="text-align:left; margin-top:10px;">Rules &amp; Regulations</p>
            <p class="description">Students must keep safe the fee receipt for records.</p>
            <p class="description">Students must ask for receipt on depositing fee from Accounts.</p>
            <p class="description">All fee are non-refundable / non-transferable.</p>
            <p class="description">For further details contact: 03158042977</p>
            <br />
            <div style="color:#fff; background-color:#000; padding:5px; float:right; display:inline;">
                Bank Stamp
            </div>
            <div class="clear"></div>
            <br />
            <hr />
            <p class="description">These funds are intended for Shahbaz College of Pharmacy Technician. Account # 0121-0980-003570-01-7 held with Badar Block Branch, Lahore</p>
        </div>
        <div class="segment no-border">
            <div class="copies-container">
                <p class="copies">Fee Challan - College Copy</p>
            </div>
            <br />
            <p class="title"><?php echo $campus->campus_name;?></p>
            <p class="address"><?php echo $campus->address?></p>
            <div class="copies-container">
                <p class="copies">BANK AL HABIB LIMITED</p>
            </div>
            <p class="bank">0080-900358-01</p>
            <div class="copies-container">
                <p class="copies">Payable at any branch of BANK AL HABIB LIMITED</p>
            </div>
            <table>
                <tbody>
                <tr>
                    <td>Challan # : <?php echo $closing_id;?></td>
                    <td>Last Date : <?php echo date('M d, Y');?></td>
                </tr>
                <tr>
                    <td>Name : <?php echo $campus->campus_name;?></td>
                    <td>F / N : </td>
                </tr>
                <tr>
                    <td>Roll # : <?php echo $closing_id;?></td>
                    <td>Class : </td>
                </tr>
                <tr>
                    <td style="width: 37%">Merged Challans </td>
                    <td style="word-wrap:break-word;"><?php echo $closing_id?></td>
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
                    <td style="word-wrap:break-word;">Rs. <?php echo $amount;?></td>
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
                    <td>Bank Charges</td>
                    <td>Rs. <?php $bank_charges = 15;?></td>
                </tr>
                <tr>
                    <td>Discount</td>
                    <td>Rs. 0</td>
                </tr>
                <tr>
                    <td><strong>Net Payable Amount</strong></td>
                    <td><strong>Rs. <?php echo $amount;?></strong></td>
                </tr>
                </tbody>
            </table>
            <p class="bank" style="text-align:left; margin-top:10px;">Rules &amp; Regulations</p>
            <p class="description">Students must keep safe the fee receipt for records.</p>
            <p class="description">Students must ask for receipt on depositing fee from Accounts.</p>
            <p class="description">All fee are non-refundable / non-transferable.</p>
            <p class="description">For further details contact: 03158042977</p>
            <br />
            <div style="color:#fff; background-color:#000; padding:5px; float:right; display:inline;">
                Bank Stamp
            </div>
            <div class="clear"></div>
            <br />
            <hr />
            <p class="description">These funds are intended for Shahbaz College of Pharmacy Technician. Account # 0121-0980-003570-01-7 held with Badar Block Branch, Lahore</p>
        </div>
        <div class="clear"></div>
    </div>
</body>
</html>