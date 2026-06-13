	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			All Teachers <small>Here you can find all teachers</small>
			</h3>-->
			<!-- BEGIN PAGE CONTENT-->
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat purple-plum">
						<div class="visual">
							<i class="fa fa-users"></i>
						</div>
						<div class="details">
							<div class="number">
								<?php echo $fee_dues_students_count;?>
							</div>
							<div class="desc">
								Total Students
							</div>
						</div>
					</div>
				</div>
                
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat purple-plum">
						<div class="visual">
							<i class="fa fa-users"></i>
						</div>
						<div class="details">
							<div class="number">
								<?php echo $fee_dues_contractors_count;?>
							</div>
							<div class="desc">
								Total Contractors
							</div>
						</div>
					</div>
				</div>
				
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat purple-plum">
						<div class="visual">
							<i class="fa fa-money"></i>
						</div>
						<div class="details">
							<div class="number">
								<?php echo count($fee_dues_comments)+count($contracts_fee_dues_comments);?>
							</div>
							<div class="desc">
								Total Fee Entries
							</div>
						</div>
					</div>
				</div>
				
            <?php if(@$this->session->userdata('message')):?>
                <div class="alert alert-success">
                    <button class="close" data-close="alert"></button>
                    <span>
                    <?php echo $this->session->userdata('message');?> </span>
                </div>
            <?php endif;?>

            <div class="row">
                <div class="col-md-12">
			
<!--			<div class="row">-->
<!--				<div class="col-md-12">-->
<!--				--><?php
//					$comments = array();
//					$comments['classes'] = array();
//					$comments['campuses'] = array();
//					$comments['students'] = array();
//					$comments['contractors'] = array();
//
//					foreach($fee_dues_comments as $fee_dues_comment)
//					{
//						if(in_array($fee_dues_comment['class_id'], $comments['classes']))
//						{
//
//						}
//						else
//						{
//							$class_id = $fee_dues_comment['class_id'];
//							$comments['classes'][$class_id]=0;
//							$comments['students'][$class_id]=0;
//						}
//						if(in_array($fee_dues_comment['campus_id'], $comments['campuses']))
//						{
//
//						}
//						else
//						{
//							$campus_id = $fee_dues_comment['campus_id'];
//							$comments['campuses'][$campus_id]=0;
//							$comments['contractors'][$campus_id]=0;
//						}
//					}
//					foreach($fee_dues_comments as $fee_dues_comment)
//					{
//						$class_id = $fee_dues_comment['class_id'];
//						$comments['classes'][$class_id]=$comments['classes'][$class_id]+1;
//					}
//					foreach($contracts_fee_dues_comments as $contracts_fee_dues_comment)
//					{
//						$campus_id = $contracts_fee_dues_comment['campus_id'];
//						$comments['campuses'][$campus_id]=$comments['campuses'][$campus_id]+1;
//					}
//
//					$students_ids = array();
//					foreach($fee_dues_comments as $fee_dues_comment)
//					{
//						$class_id = $fee_dues_comment['class_id'];
//						$student_id = $fee_dues_comment['student_id'];
//						array_push($students_ids,$student_id);
//						$comments['students'][$class_id]=count(array_unique($students_ids));
//					}
//
//					$contractors_ids = array();
//					foreach($contracts_fee_dues_comments as $contracts_fee_dues_comment)
//					{
//						$campus_id = $contracts_fee_dues_comment['campus_id'];
//						$contractor_id = $contracts_fee_dues_comment['contractor_id'];
//						array_push($contractors_ids,$contractor_id);
//						$comments['contractors'][$campus_id]=count(array_unique($contractors_ids));
//					}
//
////					echo '<pre>';
////					//print_r($comments);
////					echo '</pre>';
//				?>
<!--					<!-- BEGIN EXAMPLE TABLE PORTLET-->-->
<!--					<div class="portlet box grey-cascade">-->
<!--						<div class="portlet-title">-->
<!--							<div class="caption">-->
<!--								<i class="fa fa-list"></i> Students Due Fees Status-->
<!--							</div>-->
<!--						</div>-->
<!--						<div class="portlet-body">-->
<!--                            <table class="table table-bordered table-hover" id="sample_2">-->
<!--							<thead>-->
<!--								<tr>-->
<!--									<th class="hidden">-->
<!--										 Hidden-->
<!--									</th>-->
<!--									<th>-->
<!--										Campus / Class Name-->
<!--									</th>-->
<!--									<th>-->
<!--										Total Students-->
<!--									</th>-->
<!--									<th>-->
<!--										Total Contractors-->
<!--									</th>-->
<!--									<th>-->
<!--										Total Fee Entries-->
<!--									</th>-->
<!--								</tr>-->
<!--							</thead>-->
<!--							<tbody>-->
<!--								--><?php
//									$classes_comments = $comments['classes'];
//									$i=1;
//									foreach($classes_comments as $k=>$v):
//								?>
<!--								<tr>-->
<!--									<td class="hidden">-->
<!--										--><?php //echo $i;?>
<!--									</td>-->
<!--									<td>-->
<!--										--><?php //echo $this->db->get_where('classes',array('class_id'=>$k))->row()->name;?>
<!--									</td>-->
<!--									<td>-->
<!--										--><?php //echo $comments['students'][$k];?>
<!--									</td>-->
<!--									<td>-->
<!--										N/A-->
<!--									</td>-->
<!--									<td>-->
<!--										--><?php //echo $v;?>
<!--									</td>-->
<!--								</tr>-->
<!--								--><?php
//									$i++;
//									endforeach;
//								?>
<!--								--><?php
//									$campuses_comments = $comments['campuses'];
//									$i=1;
//									foreach($campuses_comments as $k=>$v):
//								?>
<!--								<tr>-->
<!--									<td class="hidden">-->
<!--										--><?php //echo $i;?>
<!--									</td>-->
<!--									<td>-->
<!--										--><?php //echo $this->db->get_where('campuses',array('campus_id'=>$k))->row()->campus_name;?>
<!--									</td>-->
<!--									<td>-->
<!--										N/A-->
<!--									</td>-->
<!--									<td>-->
<!--										--><?php //echo $comments['contractors'][$k];?>
<!--									</td>-->
<!--									<td>-->
<!--										--><?php //echo $v;?>
<!--									</td>-->
<!--								</tr>-->
<!--								--><?php
//									$i++;
//									endforeach;
//								?>
<!--							</tbody>-->
<!--							</table>-->
<!--						</div>-->
<!--					</div>-->
<!--					<!-- END EXAMPLE TABLE PORTLET-->-->
<!--					<form method="POST" action="--><?php //echo site_url();?><!--/dashboard/fee_dues_comments">-->
<!--						<input type="hidden" name="show" value="1" />-->
<!--						<button class="btn green">Show</button>-->
<!--						<br />-->
<!--						<br />-->
<!--					</form>-->
<!--				</div>-->
<!--			</div>-->


            <label style="font-size: x-large; font-weight: bolder">SELECT FILTER FROM HERE</label>
            <div class="row" style="margin-bottom:15px; padding:15px; margin-left: 0px; margin-right: 0px; border: 1px solid black">

                <div class="col-md-2">


                <form method="post" action="<?php echo site_url();?>/recovery_management/fee_dues_comments/<?php echo $recovery_management_id ?>/1">
                    <input type="submit" style="width: 100%;" class="btn yellow" value="Call Not Attend" />
                    <span class="badge" id="bcount" style="background-color: red; font-weight: bold"><?php echo $countcall ?></span>
                </form>
                </div>
                <div class="col-md-2">
                <form method="post" action="<?php echo site_url();?>/recovery_management/fee_dues_comments/<?php echo $recovery_management_id ?>/3">
                    <input type="submit" style="width: 100%;" class="btn blue" value="Cell off" />
                    <span class="badge" id="bcount" style="background-color: red; font-weight: bold"><?php echo $countcelloff ?></span>
                </form>
                </div>
                    <div class="col-md-2">
                <form method="post" action="<?php echo site_url();?>/recovery_management/fee_dues_comments/<?php echo $recovery_management_id ?>/4">
                    <input type="submit" style="width: 100%;" class="btn red" value="Struck of now" />
                    <span class="badge" id="bcount" style="background-color: red; font-weight: bold"><?php echo $countstruckof ?></span>
                </form>
                    </div>

                <div class="col-md-2">
                    <form method="post" action="<?php echo site_url();?>/recovery_management/fee_dues_comments/<?php echo $recovery_management_id ?>/2">
                        <input type="submit" style="width: 100%;" class="btn purple" value="Will pay on" />
                        <span class="badge" id="bcount" style="background-color: red; font-weight: bold"><?php echo $countwillpay ?></span>
                    </form>
                </div>

                <div class="col-md-2">
                    <form method="post" action="<?php echo site_url();?>/recovery_management/fee_dues_comments/<?php echo $recovery_management_id ?>/6">
                        <input type="submit" style="width: 100%;" class="btn purple" value="Will pay today" />
                        <span class="badge" id="bcount" style="background-color: red; font-weight: bold"><?php echo $countwillpayon ?></span>
                    </form>
                </div>

                <div class="col-md-2">
                    <form method="post" action="<?php echo site_url();?>/recovery_management/fee_dues_comments/<?php echo $recovery_management_id ?>/5">
                        <input type="submit" style="width: 100%;" class="btn blue" value="Fresh" />
                        <span class="badge" id="bcount" style="background-color: red; font-weight: bold"><?php echo $countnew ?></span>
                    </form>
                </div>

                <div class="col-md-2">
                    <form method="post" action="<?php echo site_url();?>/recovery_management/fee_dues_comments/<?php echo $recovery_management_id ?>/0">
                        <input type="submit" style="width: 100%;" class="btn green" value="All data" />
                        <span class="badge" id="bcount" style="background-color: red; font-weight: bold"><?php echo count($fee_dues_comments)+count($contracts_fee_dues_comments) ?></span>
                    </form>
                </div>

            </div>
			

			<div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i>Students Due Fees
							</div>
						</div>
						<div class="portlet-body">
                            <table class="table table-bordered table-hover" id="sample_10">
							<thead>
							<tr>
                                <th class="hidden">
									 Hidden
								</th>

                                <th>

                                    Student Details

                                </th>
								
								
                                <th>
                                    Fee Information&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </th>
                                <th>
                                    Paid Fee Details &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </th>
                                <th>
                                    Unpaid Fee Details&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </th>
                                <th>
                                	Add Comment
                                </th>
                                <th>
									 Manual Remarks
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=0;

								foreach($fee_dues_comments as $due):

                                    if ($filter != "0"){

                                        $currentdatehere=date('Y-m-d');

                                        if ($filter == "1"){

                                            $filterds = "Call Not Attended";

                                        }elseif ($filter == "2"){

                                            $filterds = "Will Pay On";

                                        }elseif ($filter == "3"){

                                            $filterds = "Cell Off";

                                        }elseif ($filter == "4"){

                                            $filterds = "Struck of now";
                                        }elseif ($filter == "6"){

                                            $filterds = "Will Pay On today";

                                        }else{

                                            $filterds = "Fresh";

                                        }

                                       $rem = $this->db->order_by('fees_remarks.fee_remarks_id ','desc')->limit(1)->get_where('fees_remarks', array('fee_id'=>$due['fee_id']))->result_array();



                                    }
								$class = '';
                                    
                                    if ( ($filter == "0"                         ) ||

                                        @$filterds == "Fresh"  && @strpos($rem[0]['comment'], "Call Not Attended") === false && @strpos($rem[0]['comment'], "Will Pay On") === false && @strpos($rem[0]['comment'], "Cell Off") === false && @strpos($rem[0]['comment'], "Struck of now") === false ||

                                        @$filterds == "Will Pay On today"  && @strpos($rem[0]['comment'], "Will Pay On") !== false &&  $rem[0]['paid_on_date'] < $currentdatehere ||

                                        @strpos($rem[0]['comment'], "$filterds") !== false && @$filterds != "Will Pay On today" && $filterds == "Will Pay On" &&  $rem[0]['paid_on_date'] > $currentdatehere ||
										
										@strpos($rem[0]['comment'], "$filterds") !== false && @$filterds != "Will Pay On today" && $filterds != "Will Pay On"
										

                                    ){


                                    $this->db->order_by('dead_line','ASC');
									
									$this->db->select('*');
									$this->db->from('payments');
									$this->db->where('student_id', $due['student_id']);
									$this->db->where('merged_challan IS NOT NULL and actual_amount > 0');
									$this->db->group_by("CASE WHEN merged_challan IS NOT NULL THEN merged_challan else '' end",false);
									$this->db->order_by('dead_line','ASC');
									$query = $this->db->get()->result_array();

									$this->db->select('*');
									$this->db->from('payments');
									$this->db->where('student_id', $due['student_id']);
									$this->db->where('merged_challan is null');
									$this->db->or_where('student_id = "'.$due['student_id'].'" and merged_challan IS not NULL and actual_amount = 0');
									$this->db->order_by('dead_line','ASC');
									$query2 = $this->db->get()->result_array();
									
									
                                    $payments =array_merge($query,$query2);
									$total_fee = 0;
                                    $created_council_fee = 0;
                                    $submitted_council_fee = 0;
                                    $fee_decided_current_time = 0;
                                    $total_fee_submitted = 0;
                                    $unpaid_installments_current_time = 0;
                                    foreach($payments as $payment)
                                    {
                                        if($payment['payment_plan']!='consulation fee')
                                        {
                                            $total_fee+=$payment['amount'];
                                        }
                                        if($payment['payment_plan']=='consulation fee')
                                        {
                                            $created_council_fee+=$payment['amount'];
                                            if($payment['paid']==1)
                                            {
                                                $submitted_council_fee+=$payment['actual_amount'];
                                            }
                                        }
                                        if($payment['dead_line']<date('Y-m-d'))
                                        {
                                            $fee_decided_current_time+=$payment['amount'];
                                            if($payment['paid']==0)
                                            {
                                                $unpaid_installments_current_time++;
                                            }
                                        }
                                        if($payment['paid']==1 && $payment['payment_plan']!='consulation fee')
                                        {
                                            $total_fee_submitted+=$payment['actual_amount'];
                                        }
                                        //CHECK ANY PAYMENT 1 MONTH BEFORE
                                        $oneMonthOldDate = date("Y-m-d", strtotime( date( "Y-m-d", strtotime( date("Y-m-d") ) ) . "-1 month" ) );
                                        if($payment['paid']==0 && $payment['dead_line']<$oneMonthOldDate)
                                        {
                                            $show=1;
                                        }
                                    }


							?>
                            <tr class="<?php echo $class;?>">
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
                                <td>
                                    Campus : <?php echo $due['campus_name'];?>
                                    <br />
                                    Class : <?php echo $due['class_name'];?>
                                    <br />
                                    Student Name : <span class="bold"><?php echo $due['first_name'].' '.$due['last_name'];?></span>
                                    <br />
                                    CNIC : <?php echo $due['cnic'];?>
                                    <br />
                                    Roll # : <span class="bold"><?php echo $due['roll_no'];?></span>
                                    <br />
                                    Mobile : <span class="bold"><?php echo $due['mobile'];?> - <?php echo $due['emergency_no'];?></span>
                                </td>
								
                                <td>
                                    Total Fee : <?php echo $due['total_fee'];?>
                                    <br />
                                    Total Created Fee : <?php echo $total_fee;?>
                                    <br />
                                    Total Created Council Fee : <?php echo $created_council_fee;?>
                                    <br />
                                    Total Submitted Council Fee : <?php echo $submitted_council_fee;?>
                                    <br />
                                    Fee Decided Current Time : <span class="bold"><?php echo $fee_decided_current_time;?></span>
                                    <br />
                                    Total Fee Submitted : <span class="bold"><?php echo $total_fee_submitted;?></span>
                                    <br />
                                    Remaining Fee Payable Current Time : <span class="bold"><?php echo $fee_decided_current_time-$total_fee_submitted;?></span>
                                    <br />
                                    Unpaid installments Current Time : <span class="bold"><?php echo $unpaid_installments_current_time;?></span>
                                    <br />
                                    Percentage Fee Received : <?php if ($total_fee_submitted > 0) {
                                        echo round(($total_fee_submitted / $total_fee) * 100) . '%';
                                    }else {
                                        echo "0 %";
                                    }
                                        ?>
                                    <br />
                                    Percentage Paid Fee According to Decision : <?php echo round(($total_fee_submitted/$fee_decided_current_time)*100).'%';?>
                                </td>
                                <td>
                                    <?php
                                    foreach($payments as $payment)
                                    {
                                        if($payment['paid']==1)
                                        {
                                            echo $payment['actual_amount'].' Paid on '.$payment['paid_date'].'<br />';
                                        }
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    foreach($payments as $payment)
                                    {
                                        if($payment['paid']==0)
                                        {
                                            if($payment['dead_line']<date('Y-m-d'))
                                            {
                                                echo '<span class="bold">'.$payment['amount'].' Not Paid on '.$payment['dead_line'].'</span><br />';
                                            }
                                            else
                                            {
                                                echo $payment['amount'].' Not Paid on '.$payment['dead_line'].'<br />';
                                            }
                                        }
                                    }
                                    ?>
                                </td>
                                <td>
<!--                                    --><?php
//                                    	$fee_pending = $this->db->get_where('fees_remarks', array('fee_id'=>$due['fee_id'],'clear_status'=>0))->result_array();
//										if(count($fee_pending)<1):
//									?>
                                            <a data-toggle="modal" data-id="<?php echo $due['fee_id'] ?>" title="Add this item" class="open-AddBookDialog btn btn-primary" href="#insertcomment">
                                                <i class="fa fa-edit"> Add Comments</i>
                                            </a>
											
											<br />
											<br />
											<br />
											<br />

                                     <a class="btn green" href="<?php echo site_url().'/documents/print_struck_off_notice/'.$due['student_id'];?>" target="_blanck" >
                                        <i class="fa fa-eye"></i> Struck of Letter
                                    </a>
								</td>
                                <td>
									<div class="fee_<?php echo $due['fee_id'];?>">
										<?php
                                            $remarks = $this->db->get_where('fees_remarks', array('fee_id'=>$due['fee_id']))->result_array();
                                            foreach($remarks as $remark):
                                        ?>
                                            <?php
											
                                                echo "<p><span class='bold'>".@$remark['comment']."</span> (".@$remark['add_by']." on ".@date('d M, Y H:i:s A',strtotime(@$remark['date'])).")</p>";
                                            ?>
                                        <?php
                                            endforeach;
                                        ?>
                                    </div>
								</td>

							</tr>
                            <?php

                                }
								$i++;
                            	endforeach;
							?>

                            <?php
								$i=0;
								foreach($contracts_fee_dues_comments as $due):

                            if ($filter != "0"){

                                if ($filter == "1"){

                                    $filterds = "Call Not Attended";

                                }elseif ($filter == "2"){

                                    $filterds = "Will Pay On";

                                }elseif ($filter == "3"){

                                    $filterds = "Cell Off";

                                }elseif ($filter == "4"){

                                    $filterds = "Struck of now";
                                }elseif ($filter == "6"){

                                    $filterds = "Will Pay On today";

                                }else{

                                    $filterds = "Fresh";

                                }

                                $rem = $this->db->order_by('fees_remarks.fee_remarks_id ','desc')->limit(1)->get_where('fees_remarks', array('fee_id'=>$due['fee_id']))->result_array();



                            }
                            $class = '';




                                     if ( ($filter == "0"                         ) ||

                                        @$filterds == "Fresh"  && @strpos($rem[0]['comment'], "Call Not Attended") === false && @strpos($rem[0]['comment'], "Will Pay On") === false && @strpos($rem[0]['comment'], "Cell Off") === false && @strpos($rem[0]['comment'], "Struck of now") === false ||

                                        @$filterds == "Will Pay On today"  && @strpos($rem[0]['comment'], "Will Pay On") !== false &&  $rem[0]['paid_on_date'] < $currentdatehere ||

                                        @strpos($rem[0]['comment'], "$filterds") !== false && @$filterds != "Will Pay On today" && $filterds == "Will Pay On" &&  $rem[0]['paid_on_date'] > $currentdatehere ||
										
										@strpos($rem[0]['comment'], "$filterds") !== false && @$filterds != "Will Pay On today" && $filterds != "Will Pay On"
										

                                    ){



                            $this->db->order_by('dead_line','ASC');
                                    $payments=$this->db->get_where('payments',array('contract_id'=>$due['contract_id']))->result_array();
									$total_fee = 0;
                                    $created_council_fee = 0;
                                    $submitted_council_fee = 0;
                                    $fee_decided_current_time = 0;
                                    $total_fee_submitted = 0;
                                    $unpaid_installments_current_time = 0;
                                    foreach($payments as $payment)
                                    {
                                        if($payment['payment_plan']!='consulation fee')
                                        {
											if($payment['paid']==1)
                                            {
												$total_fee+=$payment['actual_amount'];
											}
											else
											{
												$total_fee+=$payment['amount'];
											}
                                        }
                                        if($payment['payment_plan']=='consulation fee')
                                        {
                                            $created_council_fee+=$payment['amount'];
                                            if($payment['paid']==1)
                                            {
                                                $submitted_council_fee+=$payment['actual_amount'];
                                            }
                                        }
                                        if($payment['dead_line']<date('Y-m-d'))
                                        {
                                            $fee_decided_current_time+=$payment['amount'];
                                            if($payment['paid']==0)
                                            {
                                                $unpaid_installments_current_time++;
                                            }
                                        }
                                        if($payment['paid']==1 && $payment['payment_plan']!='consulation fee')
                                        {
                                            $total_fee_submitted+=$payment['actual_amount'];
                                        }
                                        //CHECK ANY PAYMENT 1 MONTH BEFORE
                                        $oneMonthOldDate = date("Y-m-d", strtotime( date( "Y-m-d", strtotime( date("Y-m-d") ) ) . "-1 month" ) );
                                        if($payment['paid']==0 && $payment['dead_line']<$oneMonthOldDate)
                                        {
                                            $show=1;
                                        }
                                    }
//								$class = '';
//								if(date('Y-m-d')<= $due['dead_line'])
//								{
//									$class = 'alert alert-success';
//								}
//								else
//								{
//									$class = 'alert alert-danger';
//								}
							?>
                            <tr class="<?php echo $class;?>">
                                <td class="hidden">
                                	<?php echo $i;
                                	?>


                                </td>
                                <td>

                                    Campus : <?php echo $due['campus_name'];?>
                                    <br /> 
									Contractor Name : <?php echo $due['name'];?>
                                    <br />
                                    Contrasctor ID : <?php echo $due['contractor_id_from_college']?>
                                    <br />
                                    Student Name : <span class="bold"><?php $due['name'] ?></span>
                                    <br />
                                    Mobile : <span class="bold"><?php echo $due['mobile'];?> - <?php echo $due['emergency_no'];?></span>

								</td>
                                <td>
<!--                                    Total Fee : --><?php //echo $due['total_fee'];?>
                                    <br />
                                    Total Created Fee : <?php echo $total_fee;?>
                                    <br />
                                    Total Created Council Fee : <?php echo $created_council_fee;?>
                                    <br />
                                    Total Submitted Council Fee : <?php echo $submitted_council_fee;?>
                                    <br />
                                    Fee Decided Current Time : <span class="bold"><?php echo $fee_decided_current_time;?></span>
                                    <br />
                                    Total Fee Submitted : <span class="bold"><?php echo $total_fee_submitted;?></span>
                                    <br />
                                    Remaining Fee Payable Current Time : <span class="bold"><?php echo $fee_decided_current_time-$total_fee_submitted;?></span>
                                    <br />
                                    Unpaid installments Current Time : <span class="bold"><?php echo $unpaid_installments_current_time;?></span>
                                    <br />
                                    Percentage Fee Received : <?php echo round(($total_fee_submitted/$total_fee)*100).'%';?>
                                    <br />
                                    Percentage Paid Fee According to Decision : <?php echo round(($total_fee_submitted/$fee_decided_current_time)*100).'%';?>
                                </td>
                                <td>
                                    <?php
                                    foreach($payments as $payment)
                                    {
                                        if($payment['paid']==1)
                                        {
                                            echo $payment['actual_amount'].' Paid on '.$payment['paid_date'].'<br />';
                                        }
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    foreach($payments as $payment)
                                    {
                                        if($payment['paid']==0)
                                        {
                                            if($payment['dead_line']<date('Y-m-d'))
                                            {
                                                echo '<span class="bold">'.$payment['amount'].' Not Paid on '.$payment['dead_line'].'</span><br />';
                                            }
                                            else
                                            {
                                                echo $payment['amount'].' Not Paid on '.$payment['dead_line'].'<br />';
                                            }
                                        }
                                    }
                                    ?>
                                </td>
                                <td>
                                    <!--                                    --><?php
                                    //                                    	$fee_pending = $this->db->get_where('fees_remarks', array('fee_id'=>$due['fee_id'],'clear_status'=>0))->result_array();
                                    //										if(count($fee_pending)<1):
                                    //									?>
                                    <a data-toggle="modal" data-id="<?php echo $due['fee_id'] ?>" title="Add this item" class="open-AddBookDialog btn btn-primary" href="#insertcomment">
                                        <i class="fa fa-edit"> Add Comments</i>
                                    </a>

                                    <!--                                        --><?php
                                    //										else:
                                    //									?>
                                    <!--									    Approval Pending.-->
                                    <!--                                    --><?php
                                    //                                    	endif;
                                    //									?>
                                </td>
                                <td>
                                    <div class="fee_<?php echo $due['fee_id'];?>">
                                        <?php
                                        $remarks = $this->db->get_where('fees_remarks', array('fee_id'=>$due['fee_id']))->result_array();
                                        foreach($remarks as $remark):
                                            ?>
                                            <?php
                                            echo '<p> <span class="bold">'.@$remark['comment'].'</span> ('.@$remark['add_by'].' on '.@date('d M, Y H:i:s A',strtotime(@$remark['date'])).')</p>';
                                            ?>
                                        <?php
                                        endforeach;
                                        ?>
                                    </div>
                                </td>
							</tr>
                            <?php
                            }
								$i++;
                            	endforeach;
							?>
							</tbody>
							</table>
						</div>
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>

			<!-- END PAGE CONTENT-->

	</div>
	<!-- END CONTENT -->
    <div class="modal fade" id="insertcomment" tabindex="-1"   data-width="500" >


        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title">Add Comments</h4>
        </div>
        <div class="modal-body">
            <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/fees/comment">
                <div class="form-body">


                    <div class="form-group">
                        <label class="col-md-6 control-label">Select Comment</label>
                        <div class="col-md-6">

                            <select class="form-control comment_box comment?>" name="comment" id="comment" required>

                                <option value="">Select Comment</option>
                                <option value="Call Not Attended">Call Not Attended</option>
                                <option value="Will Pay On">Will Pay On</option>
                                <option value="Cell Off">Cell Off</option>
                                <option value="Struck of now">Struck Of Now</option>

                            </select>

                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-6 control-label">Description <span class="required">*</span></label>
                        <div class="col-md-6">

                            <textarea class="form-control remarks" rows="3" name="description" required></textarea>

                        </div>
                    </div>


                    <div class="form-group" id="datesel">
                    <label class="col-md-6 control-label">Next Due Date</label>
                        <div class="col-md-6">
                            <div class="input-group input-small date date-picker" data-date="" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                        <input type="text" name="selected_date" class="form-control selected-date" value="" readonly>
                                        <span class="input-group-btn">
                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                        </span>
                            </div>
                        </div>

                    </div>

                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-offset-3 col-md-9">

                            <input type="hidden" id="fee_id" name="fee_id" value= $data-id />
                            <button type="submit" id="" class="btn red">Add Comment</button>

                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn default close_button" data-dismiss="modal">Close</button>
        </div>


    </div>