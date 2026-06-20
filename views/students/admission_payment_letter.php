<?php
$myAccess = checkUserAccess();

$student_fees = $this->db->get_where('students', array('student_id'=>$this->uri->segment(3)))->result_array();
$student_fee = $student_fees[0]['total_fee'];

$total_fee = $this->db->get_where('fee_rules',array('course_id'=>$student_fees[0]['course_id']))->row();
$this->db->select('sum(discount) as special_disc');
$this->db->where('status = "1" and student_id = "'.$this->uri->segment(3).'"');
$specialdisc=$this->db->get('discounts_approval')->result_array();
$specialdisc=$specialdisc[0]['special_disc'];
?>
<html>
<head>
    <title>Print Admission Form</title>
    <style>
        th {
            float : left;
            padding-left: 3px;
            padding-top: 10px;
        }
        td{
            padding-top: 10px;
            font-size: small;
        }
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
            /*background-image: url('*/<?php //echo base_url();?>/*uploads/*/<?php //echo $student[0]['logo'];?>/*');*/
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
        @media print {
            .pagebreak { page-break-before: always; } /* page-break-after works, as well */
        }
    </style>
</head>
<body>

<div class="container" style="margin:0 auto;
        height:992px;
        width:765px;
        padding:20px;
        background-repeat:no-repeat;
        background-position:-55px bottom;
        background-size:30%;
        position:relative;">
    <div class="header">
        <div class="left logo">
            <img src="<?php echo base_url();?>uploads/<?php echo $student[0]['logo'];?>" width="50%" alt="" />
        </div>
        <div class="left college_name text-center">
            <h3 style="text-transform: uppercase;"><?php echo $student[0]['campus_name'];?></h3>
        </div>
        <div class="clear"></div>
    </div>
    <div class="body">
        <table  style="width:100%">
            <tr>
                <th>Name : </th>
                <td><?php echo $student[0]['first_name'].' '.$student[0]['last_name'];?></td>
                <th>Roll No : </th>
                <td><?php echo $student[0]['roll_no'];?></td>
                <th>CNIC : </th>
                <td><?php echo $student[0]['cnic'];?></td>
            </tr>
            <tr>
                <th>Course : </th>
                <td><?php echo $this->db->get_where("courses","courses.course_id = '".$student_fees[0]['course_id']."'")->row()->course_name?></td>
                <th>Class : </th>
                <td><?php echo $student[0]['name'];?></td>
                <th>Session : </th>
                <td><?php echo $student[0]['session'];?></td>
            </tr>
            <tr>
                <th>Total Fee : </th>
                <td><?php echo $student[0]['total_fee']+$student[0]['extra_added_fee'];?></td>
                <th><!--Admission Fee : --></th>
                <td><?php //echo $total_fee->installment_on_admission;?></td>
                <th><!--Per Month Fee : --></th>
                <td><?php //echo $total_fee->per_installment_fee;?></td>
            </tr>
        </table>
        <br />
        <div class="left">
            <h3 style="text-transform: uppercase;">Payment Plan</h3>
        </div>
        <br />
        <table  style="width:100%">
            <tbody>
            <tr style="font-weight: bolder">
                <td>Deadline</td>
                <td>Challan No</td>
                <td>Payable Amount</td>
                <td>Paid</td>
            </tr>
            <?php 
                $challans = array();
            ?>
            <?php foreach ($paid_payments as $payment): ?>
                <tr>
                    <?php
                        if($payment['merged_challan']==NULL):
                    ?>
                    <td><?php echo $payment['dead_line'];?></td>
                    <td><?php echo $payment['challan_no'];?></td>
                    <td><?php echo $payment['amount'];?></td>
                    <td><?php if($payment['paid']==1){echo $payment['actual_amount'];}else{echo 0;}?></td>
                    <?php
                        else:
                            if(in_array($payment['paid_challans'],$challans))
                            {

                            }
                            else
                            {
                            array_push($challans,$payment['paid_challans']);
                            $this->db->select('*');
                            $this->db->from('payments');
                            $this->db->where('paid_challans',$payment['paid_challans']);
                            $this->db->order_by('dead_line','DESC');
                            $merge_challans = $this->db->get()->result_array();
                    ?>
                    <td><?php echo $merge_challans[0]['dead_line'];?></td>
                    <td><?php foreach($merge_challans as $merge_challan){echo $merge_challan['challan_no'].',';}?></td>
                    <td><?php $amount =0 ; foreach($merge_challans as $merge_challan){$amount += $merge_challan['amount'];}; echo $amount; ?></td>
                    <td><?php echo $merge_challans[0]['actual_amount'];?></td>
                    <?php
                            }
                        endif;
                    ?>
                </tr>
            <?php endforeach; ?>
            <?php foreach ($unpaid_payments as $payment): ?>
                <tr>
                    <td><?php echo $payment['dead_line'];?></td>
                    <td><?php echo $payment['challan_no'];?></td>
                    <td><?php echo $payment['amount'];?></td>
                    <td><?php if($payment['paid']==1){echo $payment['actual_amount'];}else{echo 0;}?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <br />
        <div class="pagebreak"> </div>

        <div>
            <?php echo $rules->rules ?>
        </div>
        <br />
        <p style="font-size:18px; text-align:right;">Student Signature&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p><br />
        <br />
        <p style="text-align:right;">______________________</p><br />
    </div>
    </div>
</div>
<div style="clear:both;"></div>
</body>
</html>