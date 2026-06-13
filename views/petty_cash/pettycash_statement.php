<?php
	$myAccess = checkUserAccess();
?>	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">

		<div class="page-content">

			<!-- BEGIN PAGE CONTENT-->
            <?php if(@$this->session->userdata('message')):?>
                <div class="alert alert-success">
                    <button class="close" data-close="alert"></button>
                    <span>
                    <?php echo $this->session->userdata('message');?> </span>
                </div>
            <?php endif;?>
			<?php if(@$this->session->userdata('error')):?>
                <div class="alert alert-danger">
                    <button class="close" data-close="alert"></button>
                    <span>
                    <?php echo $this->session->userdata('error');?> </span>
                </div>
            <?php endif;?>

            <!-- Student Data-->
			
			
            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/pettycash/pettycash_statement/<?php echo $this->uri->segment(3)?>" enctype="multipart/form-data">
                <div class="form-body">

                    <div class="form-group">
                        <label class="control-label col-md-2">From Date</label>
                        <div class="col-md-2">
						
							<?php 
							
								$today_date=date_create(date('Y-m-d'));
																
																
								$diff=date_diff($today_date,date_create($check_record->given_date));
								?>
						
                            <div class="input-group input-medium date date-picker" data-date="<?php echo $from_date?>" data-date-format="yyyy/mm/dd" data-date-start-date="-<?php echo $diff->days;?>d" data-date-viewmode="years">
                                <input type="text" name="from_date" class="form-control" value="<?php echo $from_date;?>" readonly>
                                <span class="input-group-btn">
												<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
												</span>
                            </div>
                        </div>

                        <label class="control-label col-md-2">To Date</label>
                        <div class="col-md-2">
                            <div class="input-group input-medium date date-picker" data-date="<?php echo $to_date?>" data-date-format="yyyy/mm/dd" data-date-viewmode="years">
                                <input type="text" name="to_date" class="form-control" value="<?php echo $to_date;?>" readonly>
                                <span class="input-group-btn">
												<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
												</span>
                            </div>
                        </div>
                        <div class="col-md-2" style= "margin-left:30px" >
                            <input type="hidden" name="submit" value="1" />
                            <button type="submit" class="btn green">Check Statement</button>
                        </div>
                    </div>

            </form>



            <?php
            if(@$myAccess[0]['dashboard_check_student_box']==1 || $this->session->userdata('role')=='Admin'):
                ?>
                <div class="row">

                    <div class="col-md-12 ">
                        <!-- BEGIN SAMPLE FORM PORTLET-->
                        <div class="portlet box green ">

                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-user"></i> Petty Cash Statement - <?php $this->db->select('*');
                                                                    $this->db->from('petty_cash_college_wise');
                                                                    $this->db->join('users','users.user_id = petty_cash_college_wise.assign_to','inner');
                                                                    $this->db->where('id', $this->uri->segment(3));
                                                                    $use=$this->db->get()->result_array();

																		echo $use[0]['first_name'].' '.$use[0]['last_name'].' ( '.$from_date.' - '.$to_date.' )';
																		
																	?>

                                </div>
                            </div>



                            <div class="portlet-body table-responsive">


                                <table class="table table-bordered table-hover" >
                                    <thead>
                                    <tr>
                                        <th>
                                            Sr
                                        </th>

                                        <th>
                                            Date
                                        </th>

                                        <th>
                                            Detail
                                        </th>
										<th>
                                            image
                                        </th>
                                        <th>
                                            status
                                        </th>
										<th>
                                            Transaction By
                                        </th>
                                        <th>
                                            Debit
                                        </th>
                                        <th>
                                            Credit
                                        </th>
										<th>
                                            Balance
                                        </th>



                                    </tr>
                                    </thead>
                                    <tbody>
									<?php 
										$debit=0;
										$credit=0;
										$balance=0;
									
									?>
									<tr class="odd gradeX">
									
                                                <td >
                                                   1
                                                </td>
                                                
												<td >
                                                    
                                                </td>
												
												 <td >
                                                    Opening Balance
                                                </td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
												<td >
                                                    <?php  

												    $debit+=$openbalance;
													echo $debit;

												?>
                                                </td>
                                                <td>
												
												<?php  

												    $balance+=$openbalance;

												?>
												
												</td>
                                                <td><?php  echo $openbalance;
																	


												?></td>



                                            </tr>
									
									
                                    <?php
                                    $i=2;

                                        foreach($Pettycashs as $Pettycash):

												$class = ($i%2 == 0)? 'white': 'lightgray';
                                            ?>
                                            <tr class="odd gradeX" style="background-color : <?php echo $class ?>;">
                                                <td >
                                                    <?php echo $i;?>
                                                </td>
                                                <td >
                                                    <?php  echo $Pettycash ['created_at']  ?>
                                                </td>
												<td >
                                                    <?php

                                                        if ($Pettycash['trans_type'] == 'trans')
                                                        {
                                                            $this->db->select('*');
                                                            $this->db->from('petty_cash_history');
                                                            $this->db->where('id', $Pettycash['trans_id']);
                                                            $tran=$this->db->get()->result_array();

                                                            $transtext='';


                                                                if ($tran[0]['debit_credit'] == 'C' && $tran[0]['to_pettycash_id'] != NULL)
                                                                {
                                                                    $transtext.="Sent to Petty cash account ";

                                                                    $this->db->select('*');
                                                                    $this->db->from('petty_cash_college_wise');
                                                                    $this->db->join('users','users.user_id = petty_cash_college_wise.assign_to','inner');
                                                                    $this->db->where('id', $tran[0]['to_pettycash_id']);
                                                                    $to_petty=$this->db->get()->result_array();

                                                                    $transtext.=$to_petty[0]['first_name'].' '.$to_petty[0]['last_name'];

                                                                }

                                                                elseif ($tran[0]['debit_credit'] == 'C' && $tran[0]['to_account'] != NULL)
                                                                {
                                                                    $transtext.="Sent to main account ";

                                                                    $this->db->select('*');
                                                                    $this->db->from('accounts');
                                                                    $this->db->where('id', $tran[0]['to_account']);
                                                                    $to_petty=$this->db->get()->result_array();

                                                                    $transtext.=$to_petty[0]['account_title'].' '.$to_petty[0]['account_name'];
                                                                }


                                                                if ($tran[0]['debit_credit'] == 'D' && $tran[0]['from_pettycash_id'] != NULL)
                                                                {
                                                                    $transtext.="Receive from Petty cash account ";

                                                                    $this->db->select('*');
                                                                    $this->db->from('petty_cash_college_wise');
                                                                    $this->db->join('users','users.user_id = petty_cash_college_wise.assign_to','inner');
                                                                    $this->db->where('id', $tran[0]['from_pettycash_id']);
                                                                    $from_petty=$this->db->get()->result_array();

                                                                    $transtext.=$from_petty[0]['first_name'].' '.$from_petty[0]['last_name'];

                                                                }

                                                                elseif ($tran[0]['debit_credit'] == 'D' && $tran[0]['from_account'] != NULL)
                                                                {
                                                                    $transtext.="Receive from main account ";

                                                                    $this->db->select('*');
                                                                    $this->db->from('accounts');
                                                                    $this->db->where('id', $tran[0]['from_account']);
                                                                    $from_petty=$this->db->get()->result_array();
                                                                    $transtext.=$from_petty[0]['account_title'].' '.$from_petty[0]['account_name'];
                                                                }

																$transtext.= ' - '.$Pettycash['reason'];
                                                                echo $transtext;

                                                        }else
                                                            echo $Pettycash ['detail'];  ?>
                                                </td>
												
												<td >
                                                    <?php  if( $Pettycash ['image'] == '' ) {



													}else {

														echo '<a data-toggle="modal" title="Add this item"  href="'. base_url().'uploads/'.  $Pettycash['image'].'"> <i class="fa fa-eye"></i>   </a>';

													} ?>
                                                </td>
                                                <td >
                                                    <?php
                                                    if ($Pettycash['expstatus'] == '0')
                                                        echo  "PENDING";

                                                    elseif ($Pettycash['expstatus'] == '1') {
                                                        echo "APPROVED";

                                                    }else{
                                                        echo strtoupper($Pettycash['expstatus']);
                                                    }
                                                    ?>
                                                </td>
												<td >
                                                    <?php
                                                   
                                                        echo  $Pettycash['trans_by'];

                                                  
                                                    ?>
                                                </td>
												
                                                <td>
												<?php


													if( $Pettycash ['debit_credit'] == 'D' ){
																echo $Pettycash ['amount']; 
																$debit+=$Pettycash ['amount'];
																$balance+=$Pettycash ['amount'];
																
												} ?>
												
												</td>
												
                                                <td><?php


													if( $Pettycash ['debit_credit'] == 'C' ){
																echo $Pettycash ['amount']; 
																$credit+=$Pettycash ['amount'];
																$balance-=$Pettycash ['amount'];
																
												} ?></td>
												
												<td><?php


													
																echo $balance; 
																
												 ?></td>



                                            </tr>
                                            <?php
                                            $i++;
                                        endforeach;

                                    ?>

                                    </th>

                                    <th>

                                    </th>
                                    <th>

                                    </th>

                                    <th>

                                    </th>
                                    <th>

                                    </th><th>

                                    </th><th>

                                    </th>
                                    <th style = "font-weight:bold; text-align: right;">
                                        <?php echo $debit ?>
                                    </th>
                                    <th style = "font-weight:bold; text-align: right;">
                                        <?php echo $credit ?>
                                    </th>

									<th style = "font-weight:bold; text-align: right;">
                                        <?php echo $balance ?>
                                    </th>

                                    </tbody>
                                </table>
                            </div>


                        </div>
                        <!-- END SAMPLE FORM PORTLET-->
                    </div>

                </div>

            <?php
            endif;
            ?>

            <!-- Struck of Details-->

		</div>

	</div>
	<!-- END CONTENT -->
