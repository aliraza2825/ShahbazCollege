	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			All Teachers <small>Here you can find all teachers</small>
			</h3>-->
			<!-- BEGIN PAGE CONTENT-->
            <?php if(@$this->session->userdata('message')):?>
                <div class="alert alert-success">
                    <button class="close" data-close="alert"></button>
                    <span>
                    <?php echo $this->session->userdata('message');?> </span>
                </div>
            <?php endif;?>
			<div class="row">
            	<div class="col-md-12">
                	<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-money"></i>Fee Status
							</div>
						</div>
						<div class="portlet-body">
							<table  class="table table-striped table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
                                <th class="hidden">
                                	Hidden
                                </th>
								<th>
									Contractor / Student Details &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								</th>
                                <th>
                                	Submit Date/Time
                                </th>
                                <th>
                                	Paid Date &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </th>
                                <th>
                                    Fee Details &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </th>
                                <th>
                                    Paid Fee Details &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </th>
                                <th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=1;
								foreach($college_fee as $unpaid):
								
								
								
								$challan_date = date_create($unpaid['dead_line']);
								$today_date = date_create($unpaid['paid_date']);
								$diff=date_diff($challan_date,$today_date);
								$difference = $diff->format("%R%a");
								
								if($difference>0)
								{
									if($unpaid['payment_plan']=='24 Installments')
									{
										$fine = $difference*10;
									}
									else
									{
										$fine = $difference*50;
									}
								}
								else
								{
									$fine = 0;
								}
								
								$installment_no=0;
								$installments = $this->db->get_where('payments',array('student_id'=>$unpaid['student_id'],'dead_line<='=>$unpaid['dead_line']))->result_array();
								$installment_no=count($installments);
								
							?>
                            <tr class="odd gradeX">
                                <td class="hidden">
									 <?php echo $i;?>
								</td>
								<td>
                                    <strong>Course Name :</strong> <?php echo $unpaid['course_name'];?>
                                    <br />
                                    <strong>Campus Name :</strong> <?php echo $unpaid['campus_name'];?>
                                    <br />
                                    <strong>Class Name :</strong> <?php echo $unpaid['name'];?>
                                    <br />
                                    <strong>Student Name :</strong> <?php echo $unpaid['first_name'].' '.$unpaid['last_name'];?>
                                    <br />
                                    <strong>Roll # :</strong> <?php echo $unpaid['roll_no'];?>
                                    <br />
                                    <strong>Student CNIC # :</strong> <?php echo $unpaid['cnic'];?>
                                    <br />
                                    <br />
                                    <strong>Student Phone :</strong> <?php echo $unpaid['mobile'].' '.$unpaid['emergency_no'];?>
                                    <br />
								</td>
                                <td>
									 <?php echo date('d M, Y',strtotime($unpaid['actual_paid_date']));?>
								</td>
                                <td>
									 <?php echo date('d M, Y',strtotime($unpaid['paid_date']));?>
								</td>
                                <td>


                                        <?php

                                        $totalpayable=0;
										$totalfine=0;
                                        if ($unpaid['merged_challan'] != null && $unpaid['actual_amount'] > 0){
                                            $unpaid_ids = rtrim($unpaid['paid_challans'], ", ");



                                            $this->db->select('payments.*, students.first_name as first_name, students.last_name as last_name, students.roll_no as roll_no, students.father_name as father_name, classes.name as class_name, campuses.bank_name, campuses.account_no, campuses.address, campuses.campus_name, campuses.note, campuses.campus_id');
                                            $this->db->from('payments');
                                            $this->db->join('students', 'payments.student_id=students.student_id', 'inner');
                                            $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
                                            $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
                                            $mergchalans=$this->db->where_in('payments.challan_no', explode(',', $unpaid_ids))->get()->result_array();


                                            foreach ($mergchalans as $merg){

                                                $totalpayable+=$merg['amount'];
												$challan_date = date_create($merg['dead_line']);
                                                $paid_date = date_create($merg['paid_date']);
                                                $diff=date_diff($challan_date,$paid_date);
                                                $difference = $diff->format("%R%a");

                                                if($difference>0)
                                                {

                                                        $totalfine += $difference*50;

                                                }

                                                ?>
                                                <br />
                                                Merged Challan # : <?php echo $merg['challan_no'];?>   <?php
											echo $merg['payment_comment'];
                                        ?> <br />
                                                <strong>Merged Amount : <?php echo $merg['amount'];?> </strong>

                                         <?php

                                            }
                                        }


                                        else {

                                            $totalpayable=$unpaid['amount'];
                                            ?>

                                            Challan # : <?php echo $unpaid['challan_no'];?>   <?php echo $unpaid['payment_comment']; ?>
											<br />
                                            <strong>Installment Amount : <?php echo $unpaid['amount'];?></strong>

                                        <?php } ?>


                                        <br />
                                        Discount : <?php echo $unpaid['discount'];?>
                                        <br />
                                        Previous Installment Amount : <?php echo $unpaid['remaining_installment_amount'];?>
                                        <br />
                                        Previous Fine Amount : <?php echo $unpaid['extra_amount'];?>
                                        <br />
                                        <hr />
                                        Installment Status : <?php if($unpaid['paid']==1){echo 'Paid';}else{echo 'Unpaid';}?>
                                        <br />
                                        <?php
                                        if($unpaid['paid']==1):
                                            $challan_date = date_create($unpaid['dead_line']);
                                            $paid_date = date_create($unpaid['paid_date']);
                                            $diff=date_diff($challan_date,$paid_date);
                                            $difference = $diff->format("%R%a");

                                            if($difference>0)
                                            {
                                                if($unpaid['payment_plan']=='24 Installments')
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

                                            echo 'Late Fee Fine : '.$totalfine.'<br />';
                                            echo 'Removed Fine : '.$unpaid['removed_fine'].'<br />';

                                            echo '<strong>Payable Amount : '.($totalpayable+$unpaid['remaining_installment_amount']+$unpaid['extra_amount']+$totalfine).'</strong><br />';
                                            echo '<strong>Paid Amount : '.$unpaid['actual_amount'].'</strong><br />';
                                        endif;
                                        ?>
                                        <?php
                                        if($unpaid['paid']==0):
                                            $challan_date = date_create($unpaid['dead_line']);
                                            $today_date = date_create(date('Y-m-d'));
                                            $diff=date_diff($challan_date,$today_date);
                                            $difference = $diff->format("%R%a");

                                            if($difference>0)
                                            {
                                                if($unpaid['payment_plan']=='24 Installments')
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
                                            if($difference>0)
                                            {
                                                echo 'Late Fee Days : '.str_replace('+','',$difference).'<br />';
                                            }
                                            echo 'Late Fee Amount : '.$fee_fine.'<br />';
                                            echo '<strong>Payable Amount : '.($unpaid['amount']+$unpaid['remaining_installment_amount']+$unpaid['extra_amount']+$fee_fine).'</strong>';
                                        endif;
                                        ?>
                                    </td>
                                <td>
                                        <?php
                                            // GET INSTALLMENT NO
                                            $this->db->select('*');
                                            $this->db->from('payments');
                                            $this->db->where(array('dead_line<='=>$unpaid['dead_line'],'student_id'=>$unpaid['student_id']));
                                            $this->db->group_by('merged_challan');
                                            $this->db->order_by('dead_line','ASC');
                                            $transactions = $this->db->get()->result_array();
                                        ?>
                                        <strong>Installment No. <?php echo count($transactions)?></strong><br />
                                        <?php
                                        if($unpaid['paid']==1):
                                            ?>
                                            Paid Amount : <?php echo $unpaid['actual_amount'];?>
                                            <br />
                                            <?php
                                            if($unpaid['shifted_installment']>0):
                                                ?>
                                                Shifted Previous Installment Amount : <?php echo $unpaid['shifted_installment'];?>
                                                <br />
                                            <?php
                                            endif;
                                            ?>
                                            <?php
                                            if($unpaid['shifted_previous_fine']>0):
                                                ?>
                                                Shifted Previous Installment Fine : <?php echo $unpaid['shifted_previous_fine'];?>
                                                <br />
                                            <?php
                                            endif;
                                            ?>
                                            <?php
                                            if($unpaid['shifted_fine']>0):
                                                ?>
                                                Shifted Current Installment Fine : <?php echo $unpaid['shifted_fine'];?>
                                                <br />
                                            <?php
                                            endif;
                                            ?>
                                            <?php
                                            if($unpaid['removed_previous_fine']>0):
                                                ?>
                                                Removed Previous Installment Fine : <?php echo $unpaid['removed_previous_fine'];?>
                                                <br />
                                            <?php
                                            endif;
                                            ?>
                                            <?php
                                            if($unpaid['removed_fine']>0):
                                                ?>
                                                Removed Current Installment Fine : <?php echo $unpaid['removed_fine'];?>
                                                <br />
                                            <?php
                                            endif;
                                            ?>
                                            Paid Date : <?php echo $unpaid['paid_date'];?>
                                            <br />
                                            Paid Date System : <?php echo $unpaid['updated_at'];?>
                                            <br />
                                            Fee Pay Through : <?php echo $unpaid['fee_pay_through'];?>
                                            <br />
                                            <?php
                                            if($unpaid['fee_pay_through']=='bank'):
                                                ?>
                                                Bank : <?php echo $unpaid['bank_details'];?>
                                                <br />
                                                Bank Challan / TID No. : <?php echo $unpaid['tid_no'];?>
                                                <br />

                                                Merged against Challan. : <?php echo $unpaid['paid_challans'];?>
                                            <?php
                                                $fee_counts = $this->db->join("students","students.student_id = payments.student_id")->get_where("payments","statement_id = '".$unpaid['statement_id']."' and challan_no != '".$unpaid['challan_no']."'")->result_array();
                                                if (count($fee_counts)>0){
                                                    foreach ($fee_counts as $fee_count){
                                                        echo "<br />";
                                                        echo "<strong>Student : </strong>".$fee_count['first_name'].' '.$fee_count['last_name']."<br />";
                                                        echo "<strong>Roll No : </strong>".$fee_count['roll_no']."<br />";
                                                        echo "<strong>Challan No : </strong>".$fee_count['challan_no']."<br />";
                                                        echo "<strong>Amount : </strong>".$fee_count['actual_amount']."<br />";
                                                    }
                                                }
                                            endif;
                                            ?>
                                            <?php
                                            if($unpaid['fee_pay_through']=='college' && $unpaid['fee_submit_type']=='receipt_book'):
                                                ?>
                                                Pad of : <?php echo @$this->db->get_where('campuses',array('campus_id'=>$unpaid['submitted_fee_campus_id']))->row()->campus_name;?>
                                                <br />
                                                Book No. : <?php echo $unpaid['book_no'];?>
                                                <br />
                                                Receipt No. : <?php echo $unpaid['receipt_no'];?>
                                                <br />
                                                Merged against Challan. : <?php echo $unpaid['paid_challans'];?>
                                            <?php
                                            endif;
                                            ?>
                                            <?php
                                            if($unpaid['fee_pay_through']=='college' && $unpaid['fee_submit_type']=='computer_challan'):
                                                ?>
                                                Pay by : Computer Challan
                                                <br />
                                                Merged against Challan. : <?php echo $unpaid['paid_challans'];?>
                                            <?php
                                            endif;
                                            ?>
                                            <?php
                                            $show = 0;
                                            if($unpaid['fee_pay_through']=='pay_pro'):
                                                $paypro_payment = $this->db->get_where("settlement_payments","id = '".$unpaid['settlement_payment_id']."'")->row();
                                                $stats = $this->db
                                                    ->join('pay_pro_settlement',"pay_pro_settlement.id = bank_reconciliation_statement.paypro_id")
                                                    ->join('accounts',"accounts.id = bank_reconciliation_statement.account_id")
                                                    ->get_where("bank_reconciliation_statement","bank_reconciliation_statement.paypro_id = '".$unpaid['settlement_id']."'")
                                                    ->result_array();
                                                foreach ($stats as $stat){
                                                    if ($paypro_payment->paid_via == "1LINK" || $paypro_payment->paid_via == "1Link" || $paypro_payment->paid_via == "MBL" || $paypro_payment->paid_after_days == "1LINK" || $paypro_payment->paid_after_days == "1Link" || $paypro_payment->paid_after_days == "MBL") {
                                                        if ((int)str_replace(",", "", $stat['credit']) == $stat ['link_amount'] || (int)str_replace(",", "", $stat['credit']) == $stat ['paid_amount']) {
                                                            echo "<strong>Bank Name</strong> : " . $stat['account_title'] . ' ' . $stat['account_name'] . "<br />" .
                                                                "<strong>Date</strong> : " . $stat['trans_date'] . "<br />" .
                                                                "<strong>Amount</strong> : " . $stat['link_amount'] . "<br />" .
                                                                "<strong>Description </strong>: " . $stat['description'] . "<br />";
                                                            $show = 1;
                                                        }
                                                    }else{
                                                        if ((int)str_replace(",", "", $stat['credit']) == $stat ['card_amount']) {
                                                            echo "<strong>Bank Name</strong> : " . $stat['account_title'] . ' ' . $stat['account_name'] . "<br />" .
                                                                "<strong>Date</strong> : " . $stat['trans_date'] . "<br />" .
                                                                "<strong>Amount</strong> : " . $stat['card_amount'] . "<br />" .
                                                                "<strong>Description </strong>: " . $stat['description'] . "<br />";
                                                            $show = 1;
                                                        }
                                                    }
                                                }
                                                if ($unpaid['settlement_id'] != NULL)
                                                    echo '<a href="'.site_url().'/excel_import/entries/'.$unpaid['settlement_id'].'" target="_blank" class="btn purple pull-left">See PayPro Details. </a> <br />';

                                                ?>

                                            <?php
                                            endif;
                                            ?>
											 <br />
											 Paid BY. : <?php echo $unpaid['paid_by'];?>
                                            <div class="clearfix"></div>
                                            <br />
                                            <?php
                                            if($unpaid['scan_challan']=='')
                                            {

                                            }
                                            elseif($unpaid['scan_challan']!='' )
                                            {
                                                if($unpaid['online_scan_challan']!='')
                                                {
                                                    if($unpaid['merged_challan']!='')
                                                    {
                                                        $this->db->order_by('id','desc');
                                                        $my_challan = $this->db->get_where('payments',array('merged_challan'=>$unpaid['merged_challan']))->result_array();
                                                        echo '<a href="'.str_replace($bucket_address,$cloudfront_address,$my_challan[0]['online_scan_challan']).'" target="_blank" class="btn purple pull-left">See Challan</a>';
                                                    }
                                                    else
                                                    {
                                                        echo '<a href="'.str_replace($bucket_address,$cloudfront_address,$unpaid['online_scan_challan']).'" target="_blank" class="btn purple pull-left">See Challan</a>';
                                                    }
                                                }
                                                else
                                                {
                                                    echo '<a href="'.base_url().'uploads/'.$unpaid['scan_challan'].'" target="_blank" class="btn purple pull-left">See Challan</a>';
                                                }
                                            }
                                            
                                            if($unpaid['fee_pay_through']=='college' && $unpaid['fee_submit_type']=='computer_challan')
                                            {
                                                echo '<a href="'.site_url().'/students/print_college_challan/'.$unpaid['id'].'" target="_blank" class="btn blue college_fee_'.$i.'"><i class="fa fa-print"></i> See Challan</a> <br />';
                                            }
                                            ?>
                                            <?php
                                            if($unpaid['fine_application']=='' && $unpaid['paid']==0)
                                            {

                                            }
                                            else if($unpaid['fine_application']!='' && $unpaid['paid']==1)
                                            {
                                                echo '<a href="'.base_url().'uploads/'.$unpaid['fine_application'].'" target="_blank" class="btn purple pull-left">See Application</a> <br />';
                                            }
                                            else
                                            {

                                            }
                                            ?>
                                            <div class="clearfix"></div>
                                        <?php
                                        endif;
                                        ?>
                                    </td>
                                <td>
<!--                                    --><?php //echo $unpaid['settlement_id']." ".$unpaid['settlement_payment_id']." ".$unpaid['fee_pay_through']." ".$show;?>
                                    <?php if($unpaid['fee_pay_through']=='pay_pro' && (($unpaid['settlement_id'] == NULL) || ($unpaid['settlement_id'] != NULL && $show == 0))){
                                            echo "Not Tagged";
                                    }else{
                                        ?>
                                        <form action="<?php echo site_url().'/dashboard/clear_unpaid_fee';?>" method="post" enctype="multipart/form-data">
                                        <input type="hidden" name="payment_id" value="<?php echo $unpaid['id'];?>" />
                                        <button type="submit" class="btn green">Clear</button>
                                        </form>
                                    <?php }?>
                                    <?php echo $unpaid['settlement_id'];?>
								</td>
							</tr>
                            <?php
								
								$i++;
                            	endforeach;
							?>
                            
                            <?php
								$i=1;
								foreach($contractor_fees as $unpaid):
							?>
                            <tr class="odd gradeX">
                            	<td class="hidden">
                                	<?php echo $i;?>
                                </td>
                                <td>
                                    <strong>Campus Name :</strong> <?php echo $unpaid['campus_name'];?>
                                    <br />
                                    <strong>Contractor Name :</strong> <?php echo $unpaid['name'];?>
									<br />
                                    <strong>Contract Name :</strong> <?php echo $this->db->get_where('contracts','contract_id = "'.$unpaid['contract_id'].'"')->row()->contract_name;?>
                                    <br />
                                    <strong>Roll # :</strong> <?php echo $unpaid['contractor_id_from_college'];?>
                                    <br />
                                </td>
                                <td>
									 <?php echo date('M d, Y',strtotime($unpaid['actual_paid_date']));?>
								</td>
                                <td>
									 <?php echo date('M d, Y',strtotime($unpaid['paid_date']));?>
								</td>
                                <td>
                                    <!-- Challan # : <?php echo $unpaid['challan_no'];?>
                                    <br />
                                    <strong>Installment Amount : <?php echo $unpaid['amount'];?></strong>
                                    <br />
                                    Previous Installment Amount : <?php echo $unpaid['remaining_installment_amount'];?>
                                    <br />
                                    Previous Fine Amount : <?php echo $unpaid['extra_amount'];?>
                                    <br />
                                    <hr />
                                    Installment Status : <?php if($unpaid['paid']==1){echo 'Paid';}else{echo 'Unpaid';}?>
                                    <br />-->
                                    <?php
                                    // if($unpaid['paid']==1):
                                    //     $challan_date = date_create($unpaid['dead_line']);
                                    //     $paid_date = date_create($unpaid['paid_date']);
                                    //     $diff=date_diff($challan_date,$paid_date);
                                    //     $difference = $diff->format("%R%a");

                                    //     if($difference>0)
                                    //     {
                                    //         if($unpaid['payment_plan']=='24 Installments')
                                    //         {
                                    //             $fee_fine = $difference*10;
                                    //         }
                                    //         else
                                    //         {
                                    //             $fee_fine = $difference*50;
                                    //         }
                                    //     }
                                    //     else
                                    //     {
                                    //         $fee_fine = 0;
                                    //     }

                                    //     echo 'Late Fee Fine : '.$fee_fine.'<br />';

                                    //     echo '<strong>Payable Amount : '.($unpaid['amount']+$unpaid['remaining_installment_amount']+$unpaid['extra_amount']+$fee_fine).'</strong><br />';
                                    // endif;
                                    ?>
                                    <?php
                                        if($unpaid['fee_pay_through']=='bank'):
                                            $fee_counts = $this->db->join("students","students.student_id = payments.student_id")->get_where("payments","statement_id = '".$unpaid['statement_id']."' ")->result_array();

                                            $students_count =0;
                                            $total_fee_count =0;
                                            if (count($fee_counts)>0){
                                                foreach ($fee_counts as $fee_count){
                                                    echo "<strong>Student : </strong>".$fee_count['first_name'].' '.$fee_count['last_name'].'<br />';
                                                    echo "<strong>Roll No : </strong>".$fee_count['roll_no'].'<br />';
                                                    echo "<strong>Challan No : </strong>".$fee_count['challan_no'].'<br />';
                                                    echo "<strong>Payable Amount : </strong>".$fee_count['amount'].'<br /><hr />';

                                                    $students_count++;
                                                    $total_fee_count+=$fee_count['amount'];
                                                }

                                                echo '<strong>Total Students: </strong>'.$students_count.'<br />';
                                                echo '<strong>Total Fees: </strong>'.$total_fee_count;
                                            }
                                            else
                                            {
                                                echo 'Challan # : '.$unpaid['challan_no'].'<br />';
                                                echo '<strong>Installment Amount : '.$unpaid['amount'].'</strong><br />';
                                                echo 'Previous Installment Amount : '.$unpaid['remaining_installment_amount'].'<br />';
                                                echo 'Previous Fine Amount : '.$unpaid['extra_amount'].'<br /><hr />';
                                                echo 'Installment Status : '; if($unpaid['paid']==1){echo 'Paid';}else{echo 'Unpaid';}
                                                if($unpaid['paid']==1):
                                                    $challan_date = date_create($unpaid['dead_line']);
                                                    $paid_date = date_create($unpaid['paid_date']);
                                                    $diff=date_diff($challan_date,$paid_date);
                                                    $difference = $diff->format("%R%a");

                                                    if($difference>0)
                                                    {
                                                        if($unpaid['payment_plan']=='24 Installments')
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

                                                    echo 'Late Fee Fine : '.$fee_fine.'<br />';

                                                    echo '<strong>Payable Amount : '.($unpaid['amount']+$unpaid['remaining_installment_amount']+$unpaid['extra_amount']+$fee_fine).'</strong><br />';
                                                endif;
                                            }
                                        endif;
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if($unpaid['paid']==1):
                                        ?>
                                        <strong>Paid Amount : <?php echo $unpaid['actual_amount'];?></strong>
                                        <br />
                                        <?php
                                        if($unpaid['shifted_installment']>0):
                                            ?>
                                            Shifted Previous Installment Amount : <?php echo $unpaid['shifted_installment'];?>
                                            <br />
                                        <?php
                                        endif;
                                        ?>
                                        <?php
                                        if($unpaid['shifted_previous_fine']>0):
                                            ?>
                                            Shifted Previous Installment Fine : <?php echo $unpaid['shifted_previous_fine'];?>
                                            <br />
                                        <?php
                                        endif;
                                        ?>
                                        <?php
                                        if($unpaid['shifted_fine']>0):
                                            ?>
                                            Shifted Current Installment Fine : <?php echo $unpaid['shifted_fine'];?>
                                            <br />
                                        <?php
                                        endif;
                                        ?>
                                        <?php
                                        if($unpaid['removed_previous_fine']>0):
                                            ?>
                                            Removed Previous Installment Fine : <?php echo $unpaid['removed_previous_fine'];?>
                                            <br />
                                        <?php
                                        endif;
                                        ?>
                                        <?php
                                        if($unpaid['removed_fine']>0):
                                            ?>
                                            Removed Current Installment Fine : <?php echo $unpaid['removed_fine'];?>
                                            <br />
                                        <?php
                                        endif;
                                        ?>
                                        Paid Date : <?php echo $unpaid['paid_date'];?>
                                        <br />
                                        Paid Date System : <?php echo $unpaid['actual_paid_date'];?>
                                        <br />
                                        Fee Pay Through : <?php echo $unpaid['fee_pay_through'];?>
                                        <br />
                                        <?php
                                        if($unpaid['fee_pay_through']=='bank'):
                                            ?>
                                            Bank : <?php echo $unpaid['bank_details'];?>
                                            <br />
                                            Bank Challan / TID No. : <?php echo $unpaid['tid_no'];?>
                                            <br />
                                            Statement ID. : <?php echo $unpaid['statement_id'];?>
                                            <br />
                                        <?php
                                        endif;
                                        ?>
                                        <?php
                                        if($unpaid['fee_pay_through']=='college' && $unpaid['fee_submit_type']=='receipt_book'):
                                            ?>
                                            Pad of : <?php echo @$this->db->get_where('campuses',array('campus_id'=>$unpaid['submitted_fee_campus_id']))->row()->campus_name;?>
                                            <br />
                                            Book No. : <?php echo $unpaid['book_no'];?>
                                            <br />
                                            Receipt No. : <?php echo $unpaid['receipt_no'];?>
                                            <br />
                                        <?php
                                        endif;
                                        ?>
                                        <?php
                                        if($unpaid['fee_pay_through']=='college' && $unpaid['fee_submit_type']=='computer_challan'):
                                            ?>
                                            Pay by : Computer Challan
                                            <br />
                                        <?php
                                        endif;
                                        ?>
                                        Submitted By : <?php echo @$unpaid['paid_by'];?>
                                        <div class="clearfix"></div>
                                        <br />
                                        <?php
                                        if($unpaid['scan_challan']=='')
                                        {

                                        }
                                        else
                                        {
                                            if($unpaid['online_scan_challan']!='')
                                            {
                                                if($unpaid['merged_challan']!='')
                                                {
                                                    $this->db->order_by('id','desc');
                                                    $my_challan = $this->db->get_where('payments',array('merged_challan'=>$unpaid['merged_challan']))->result_array();
                                                    echo '<a href="'.str_replace($bucket_address,$cloudfront_address,$my_challan[0]['online_scan_challan']).'" target="_blank" class="btn purple pull-left">See Challan</a>';
                                                }
                                                else
                                                {
                                                    echo '<a href="'.str_replace($bucket_address,$cloudfront_address,$unpaid['online_scan_challan']).'" target="_blank" class="btn purple pull-left">See Challan</a>';
                                                }
                                            }
                                            else
                                            {
                                                echo '<a href="'.base_url().'uploads/'.$unpaid['scan_challan'].'" target="_blank" class="btn purple pull-left">See Challan</a>';
                                            }
                                        }
                                        if($unpaid['fee_pay_through']=='college' && $unpaid['fee_submit_type']=='computer_challan')
                                        {
                                            echo '<a href="'.site_url().'/students/print_college_challan/'.$unpaid['id'].'" target="_blank" class="btn blue college_fee_'.$i.'"><i class="fa fa-print"></i> See Challan</a>';
                                        }
                                        ?>
                                        <?php
                                        if($unpaid['fine_application']=='' && $unpaid['paid']==0)
                                        {

                                        }
                                        else if($unpaid['fine_application']!='' && $unpaid['paid']==1)
                                        {
                                            echo '<a href="'.base_url().'uploads/'.$unpaid['fine_application'].'" target="_blank" class="btn purple pull-right">See Application</a>';
                                        }
                                        else
                                        {

                                        }
                                        ?>
                                        <div class="clearfix"></div>
                                    <?php
                                    endif;
                                    ?>
                                </td>

                                <td>
                                	<form action="<?php echo site_url().'/dashboard/clear_unpaid_fee';?>" method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="payment_id" value="<?php echo $unpaid['id'];?>" />
									<button type="submit" class="btn green" onclick="return confirm('Are you sure ?')" >Clear</button>
                                    </form>
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
                </div>
            </div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->