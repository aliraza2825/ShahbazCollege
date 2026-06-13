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
			width:580px;
			float:left;
			padding:0px 10px;
			display:inline-block;
			border-right:2px dotted #000;
			position:relative;
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
		.half{
			margin-top:5px;
			width:50%;
			float:left;
			display:inline;
		}
		.clearfix{
			clear:both;
		}
    </style>
</head>
<body>
	<div class="container">
        <?php if ($type == null): ?>
            <div class="segment no-border" style="margin-top: -40px;">
                <?php
                    foreach($challans as $challan):
                ?>
                <?php
                    $logo = $this->db->get_where('campuses',array('campus_id'=>$challan['campus_id']))->row()->logo;
                ?>
                <div style="position:absolute;top:0;left:10;">
                    <img src="<?php echo base_url().'uploads/'.$logo;?>" height="86" />
                </div>
                <p class="title"><?php echo $challan['campus_name']?></p>
                <br />
                <p class="address"><?php echo $challan['address']?></p>
                <div class="copies-container">
                    <p class="copies">Fee Challan - Student Copy</p>
                </div>
                <div class="half"><p class="bank" style="text-align:left; word-wrap:break-word;">Challan # : <?php

                        if ($challan['paid_challans'] == null) {
                            echo $challan['challan_no'];
                        }else{
                            echo $challan['paid_challans'];
                        }
                        ?></p></div>
                <div class="half"><p class="bank" style="text-align:left;">Student Name : <?php echo $challan['first_name'].' '.$challan['last_name'];?></p></div>
                <div class="half"><p class="bank" style="text-align:left;">Last Date : <?php echo date('d M Y', strtotime($challan['dead_line']));?></p></div>
                <div class="half"><p class="bank" style="text-align:left;">Paid Date : <?php echo date('d M Y', strtotime($challan['paid_date']));?></p></div>
                <div class="half"><p class="bank" style="text-align:left;">Fee updated at : <?php echo $challan['updated_at'];?></p></div>
                <div class="half"><p class="bank" style="text-align:left;">Roll # : <?php echo $challan['roll_no']?></p></div>
                <?php
                    if($difference>0):
                ?>
                <div class="half"><p class="bank" style="text-align:left;">Late Days : <?php echo str_replace('+','',$difference);?></p></div>
                <?php
                    endif;
                ?>
                <div class="half"><p class="bank" style="text-align:left;">Class : <?php echo $challan['class_name'];?></p></div>
                <div class="half"><p class="bank" style="text-align:left;">CNIC : <?php echo $challan['cnic'];?></p></div>
                <div class="clearfix"></div>
                <p class="half bank" style="text-align:left;">Payment Type : <?php echo $challan['payment_comment'];?></p>
                <p class="half bank" style="text-align:left;">Fee Submitted By : <?php echo $challan['paid_by'];?></p>
                        <div class="clearfix"></div>
                <div class="row">
                    <div class="half">
                        <?php
                            $student_photo = $this->db->get_where('student_documents',array('student_id'=>$challan['student_id'],'type'=>'Photo'))->result_array();
                            //echo '<pre>';
                            //print_r($student_photo);
                            //echo '</pre>';
                            if($student_photo[0]['online_image']=='')
                            {
                                echo '<img src="'.base_url().'uploads/'.$student_photo[0]['image'].'" height="185" />';
                            }
                            else
                            {
                                echo '<img src="'.$student_photo[0]['online_image'].'" height="185" />';
                            }
                        ?>
                    </div>
                    <div class="half" style="text-align: right">
                        <h5>اپنی فیس کی ادائیگی کی تصدیق کےلیے رسید پر موجود کیو آر کوڈ کو سکین کریں اور رسید کو مکمل کریں۔</h5><br />
                        <h5>کوڈ پر غلط فیس ظاہر ہونے کی صورت میں فوری طور پرکیش وصول کرنےوالے افسر کو اصلاح کےلیے مطلع کریں۔</h5><br />
                        <h5>کاونٹرچھوڑنےسے پہلے اپنی فیس کی رسید کی تصدیق کریں۔کاونٹر چھوڑنےکے بعد کالج کسی بھی غلطی کا ذمہ دار نہیں ہوگا۔</h5>
                    </div>
                </div><div class="clearfix"></div>

                        <h5>Dear Students:
                            Scan and complete the receipt to confirm your fee payment. In case of incorrect fee appearing on the QR code,
                            immediately inform the cash receipt officer for correction.
                            Verify your fee payment receipt before leaving the counter. College will not be responsible for any errors.</h5>

                <table width="100%">
                            <thead>
                                <tr>
                                    <th colspan="3">FEE DETAILS</th>
                                </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td class="center"><strong>Description</strong></td>
                                <td class="center"><strong>Amount</strong></td>
                                <td class="center"><strong>Fill By Student From QR</strong></td>
                            </tr>
                            <tr>
                                <td>Installment Fee</td>
                                <?php
                                $totalpayable=0;
                                if ($challan['merged_challan'] != null ){ ?>

                                    <td>

                                        <?php

                                        $payment_ids = rtrim($challan['paid_challans'], ", ");

                                        $this->db->select('payments.*, students.first_name as first_name, students.last_name as last_name, students.roll_no as roll_no, students.father_name as father_name, classes.name as class_name, campuses.bank_name, campuses.account_no, campuses.address, campuses.campus_name, campuses.note, campuses.campus_id');
                                        $this->db->from('payments');
                                        $this->db->join('students', 'payments.student_id=students.student_id', 'inner');
                                        $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
                                        $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
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
                                <td>_________________________</td>
                            </tr>
                            <tr>
                                <td>Previous Installment Remaing</td>
                                <td>Rs. <?php echo $challan['remaining_installment_amount'];?></td>
                                <td>_________________________</td>
                            </tr>
                            <tr>
                                <td>Previous Fine Remaining</td>
                                <td>Rs. <?php echo $challan['extra_amount'];?></td>
                                <td>_________________________</td>
                            </tr>
                            <tr>
                                <td>Fine Amount</td>
                                <td>Rs. <?php
                                    if ($challan['fine_amount'] > 0){
                                        echo $fee_fine = $challan['fine_amount'];
                                    }else {
                                        echo $fee_fine = $challan['removed_fine'];
                                    } ?>
                                </td>
                                <td>_________________________</td>
                            </tr>
                            <tr>
                                <td><strong>Net Payable Amount</strong></td>
                                <td><strong>Rs. <?php
                                        if ($challan['paid_challans'] != null){
                                            $challancount = rtrim($challan['paid_challans'], ", ");
                                            $array = explode(',' ,$challancount);
                                            echo $totalpayable+$challan['remaining_installment_amount']+$challan['extra_amount']+$fee_fine;
                                        }else {
                                            echo $challan['amount'] + $challan['remaining_installment_amount'] + $challan['extra_amount'] + $fee_fine;
                                        }?>
                                    </strong>
                                </td>
                                <td>_________________________</td>
                            </tr>
                            <tr>
                            <td>Shifted Fine</td>
                            <td>Rs. <?php

                                if ($challan['shifted_fine'] > 0){
                                    echo $challan['shifted_fine'];
                                }else {
                                    echo '0';
                                } ?> </td>
                                <td>_________________________</td>
                        </tr>
                            <tr>
                                <td>Discount Amount</td>
                                <td>Rs. <?php
                                    if ($challan['discount'] > 0){
                                        echo $challan['discount'];
                                    }else {
                                        echo '0';
                                    } ?> </td>
                                <td>_________________________</td>
                            </tr>

                            <tr>
                                <td>Removed Fine Amount</td>
                                <td>Rs. <?php

                                    if ($challan['removed_fine'] > 0){
                                        echo $challan['removed_fine'];
                                    }else {
                                        echo '0';
                                    } ?>
                                </td>
                                <td>_________________________</td>
                            </tr>

                            <tr>
                                <td><strong>Paid Amount</strong></td>
                                <td><strong>Rs. <?php echo $challan['actual_amount'];?></strong></td>
                                <td>_________________________</td>
                            </tr>
                            <tr>
                                <td><strong></strong></td>
                                <td><strong>Payment unique Code Here</strong></td>
                                <td>_________________________</td>
                            </tr>
                            </tbody>
                        </table>
                
                        <div class="row">
                            <div class="half">
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
                            </div>
                            <div class="half" style="text-align:right;">
                                <?php
                                    $qr = 'https://qrcode.tec-it.com/API/QRCode?data=https%3A%2F%2Fwww.shahbazcollegeofpharmacy.edu.pk/lahore-campus/index.php/'.$this->uri->segment(1).'/'.$this->uri->segment(2).'/'.$this->uri->segment(3).'/print'.'%2F&choe=UTF-8';
                                ?>
                                <img src="<?php echo $qr;?>" width="150" />
                            </div>
                        </div>
                        
                        <div style="position:absolute; bottom:30px; left:280px; opacity:0.5;">
                            <img src="<?php echo base_url();?>images/paid-stamp.png" width="150" />
                        </div>
                <?php
                    endforeach;
                ?>
            </div>
            <div class="clear"></div>
        <?php else: ?>
            <div class="segment no-border" style="margin-top: -40px;">
                <?php
                foreach($challans as $challan):
                    ?>
                    <?php
                    $logo = $this->db->get_where('campuses',array('campus_id'=>$challan['campus_id']))->row()->logo;
                    ?>
                    <div style="position:absolute;top:0;left:10;">
                        <img src="<?php echo base_url().'uploads/'.$logo;?>" height="86" />
                    </div>
                    <p class="title"><?php echo $challan['campus_name']?></p>
                    <br />
                    <p class="address"><?php echo $challan['address']?></p>
                    <div class="copies-container">
                        <p class="copies">Fee Challan - Student Copy</p>
                    </div>
                    <div class="half"><p class="bank" style="text-align:left; word-wrap:break-word;">Challan # : <?php

                            if ($challan['paid_challans'] == null) {
                                echo $challan['challan_no'];
                            }else{
                                echo $challan['paid_challans'];
                            }
                            ?></p></div>
                    <div class="half"><p class="bank" style="text-align:left;">Student Name : <?php echo $challan['first_name'].' '.$challan['last_name'];?></p></div>
                    <div class="half"><p class="bank" style="text-align:left;">Last Date : <?php echo date('d M Y', strtotime($challan['dead_line']));?></p></div>
                    <div class="half"><p class="bank" style="text-align:left;">Paid Date : <?php echo date('d M Y', strtotime($challan['paid_date']));?></p></div>
                    <div class="half"><p class="bank" style="text-align:left;">Fee updated at : <?php echo $challan['updated_at'];?></p></div>
                    <div class="half"><p class="bank" style="text-align:left;">Roll # : <?php echo $challan['roll_no']?></p></div>
                    <?php
                    if($difference>0):
                        ?>
                        <div class="half"><p class="bank" style="text-align:left;">Late Days : <?php echo str_replace('+','',$difference);?></p></div>
                    <?php
                    endif;
                    ?>
                    <div class="clearfix"></div>
                    <p class="half bank" style="text-align:left;">Payment Type : <?php echo $challan['payment_comment'];?></p>
                    <p class="half bank" style="text-align:left;">Fee Submitted By : <?php echo $challan['paid_by'];?></p>
                    <div class="clearfix"></div>
                    <table width="100%">
                        <thead>
                        <tr>
                            <th colspan="3">FEE DETAILS</th>
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

                                    $this->db->select('payments.*, students.first_name as first_name, students.last_name as last_name, students.roll_no as roll_no, students.father_name as father_name, classes.name as class_name, campuses.bank_name, campuses.account_no, campuses.address, campuses.campus_name, campuses.note, campuses.campus_id');
                                    $this->db->from('payments');
                                    $this->db->join('students', 'payments.student_id=students.student_id', 'inner');
                                    $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
                                    $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
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

                        </tr>
                        <tr>
                            <td>Previous Installment Remaing</td>
                            <td>Rs. <?php echo $challan['remaining_installment_amount'];?></td>

                        </tr>
                        <tr>
                            <td>Previous Fine Remaining</td>
                            <td>Rs. <?php echo $challan['extra_amount'];?></td>

                        </tr>
                        <tr>
                            <td>Fine Amount</td>
                            <td>Rs. <?php
                                if ($challan['fine_amount'] > 0){
                                    echo $challan['fine_amount'];
                                }else {
                                    echo $challan['removed_fine'];
                                } ?>
                            </td>

                        </tr>
                        <tr>
                            <td><strong>Net Payable Amount</strong></td>
                            <td><strong>Rs. <?php
                                    if ($challan['paid_challans'] != null){
                                        $challancount = rtrim($challan['paid_challans'], ", ");
                                        $array = explode(',' ,$challancount);
                                        echo $totalpayable+$challan['remaining_installment_amount']+$challan['extra_amount']+$fee_fine;
                                    }else {
                                        echo $challan['amount'] + $challan['remaining_installment_amount'] + $challan['extra_amount'] + $fee_fine;
                                    }?>
                                </strong>
                            </td>

                        </tr>
                        <tr>
                            <td>Shifted Fine</td>
                            <td>Rs. <?php

                                if ($challan['shifted_fine'] > 0){
                                    echo $challan['shifted_fine'];
                                }else {
                                    echo '0';
                                } ?> </td>

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
                            <td>Removed Fine Amount</td>
                            <td>Rs. <?php

                                if ($challan['removed_fine'] > 0){
                                    echo $challan['removed_fine'];
                                }else {
                                    echo '0';
                                } ?>
                            </td>

                        </tr>

                        <tr>
                            <td><strong>Paid Amount</strong></td>
                            <td><strong>Rs. <?php echo $challan['actual_amount'];?></strong></td>

                        </tr>
                        </tbody>
                    </table>
                    <h1 style="text-align:center; margin-top:10px;">Unique Code : <?php echo $challan_id ?></h1>

                <?php
                endforeach;
                ?>
            </div>
            <div class="clear"></div>
        <?php endif; ?>
    </div>
</body>
</html>