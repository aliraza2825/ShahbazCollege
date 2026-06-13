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

            <?php
            if(@$myAccess[0]['dashboard_check_student_box']==1 || $this->session->userdata('role')=='Admin'):
                ?>
                <div class="row">
				
					<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/accounts/cashaccountreport/<?php echo $this->uri->segment(3); ?>" enctype="multipart/form-data">
								<div class="form-body">
									
									<div class="form-group">
										<label class="control-label col-md-2">From Date</label>
										<div class="col-md-2">
											<div class="input-group input-medium date date-picker" data-date="<?php echo date('Y/m/1')?>" data-date-format="yyyy/mm/dd" data-date-viewmode="years">
												<input type="text" name="from_date" class="form-control" value="<?php echo $from_date;?>" readonly>
												<span class="input-group-btn">
												<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
												</span>
											</div>
										</div>
										
										<label class="control-label col-md-2">To Date</label>
										<div class="col-md-2">
											<div class="input-group input-medium date date-picker" data-date="<?php echo date('Y/m/d')?>" data-date-format="yyyy/mm/dd" data-date-viewmode="years">
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
				
				<?php $check_account = $this->db->get_where('accounts', array('id'=>$this->uri->segment(3)))->result_array(); ?>

                    <div class="col-md-12 ">
                        <!-- BEGIN SAMPLE FORM PORTLET-->
                        <div class="portlet box green ">

                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-user"></i> Cash in Hand Statement &nbsp;&nbsp;&nbsp; <?php echo $check_account[0]['account_title'].' '.$check_account[0]['account_name']; ?>
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
                                            Image
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
                                    $i=1;
                                    $credit =0;
                                    $debit =0;
                                    $balance = 0; ?>

                                    <tr class="odd gradeX">

                                        <td >
                                            1
                                        </td>

                                        <td >

                                        </td>
										

                                        
                                        <td >
                                            Opening Balance
                                        </td>

                                       
										<td ></td>
                                        <td ></td>
                                        <td ></td>
                                        
                                        <td>

                                            <?php

                                            $balance+=$openbalance;

                                            ?>

                                        </td>
                                        <td style="text-align : right;"><?php  echo $balance;



                                            ?></td>



                                    </tr>



                                    <?php
									
									if(@$accountstatement){

                                      foreach($accountstatement as $trans):

											$class = ($i%2 == 0)? 'white': 'lightgray';
													
                                            ?>
                                            <tr style="background-color : <?php echo $class ?>;">

                                                <td >
                                                    <?php echo $i+1;?>
                                                </td>
												<td><?php  echo $trans ['created_at']  ?></td>
												<td> <?php

                                                    $transtext='';
                                                    if ($trans['debit_credit'] == 'C' && $trans['to_pettycash_id'] != NULL)
                                                    {
                                                        $transtext.="Sent to Petty cash account ";

                                                        $this->db->select('*');
                                                        $this->db->from('petty_cash_college_wise');
                                                        $this->db->join('users','users.user_id = petty_cash_college_wise.assign_to','inner');
                                                        $this->db->where('id', $trans['to_pettycash_id']);
                                                        $to_petty=$this->db->get()->result_array();

                                                        $transtext.=$to_petty[0]['first_name'].' '.$to_petty[0]['last_name'] .'  '.$trans['reason'];

                                                    }

                                                    elseif ($trans['debit_credit'] == 'C' && $trans['to_account_id'] != NULL)
                                                    {
                                                        $transtext.="Sent to main account ";

                                                        $this->db->select('*');
                                                        $this->db->from('accounts');
                                                        $this->db->where('id', $trans['to_account_id']);
                                                        $to_petty=$this->db->get()->result_array();

                                                        $transtext.=$to_petty[0]['account_title'].' '.$to_petty[0]['account_name'].'  '.$trans['reason'];
                                                    }


                                                    if ($trans['debit_credit'] == 'D' && $trans['from_pettycash_id'] != NULL)
                                                    {
                                                        $transtext.="Receive from Petty cash account ";

                                                        $this->db->select('*');
                                                        $this->db->from('petty_cash_college_wise');
                                                        $this->db->join('users','users.user_id = petty_cash_college_wise.assign_to','inner');
                                                        $this->db->where('id', $trans['from_pettycash_id']);
                                                        $from_petty=$this->db->get()->result_array();

                                                        $transtext.=$from_petty[0]['first_name'].' '.$from_petty[0]['last_name'].'  '.$trans['reason'];

                                                    }

                                                    elseif ($trans['debit_credit'] == 'D' && $trans['from_account_id'] != NULL)
                                                    {
                                                        $transtext.="Receive from main account ";

                                                        $this->db->select('*');
                                                        $this->db->from('accounts');
                                                        $this->db->where('id', $trans['from_account_id']);
                                                        $from_petty=$this->db->get()->result_array();
                                                        $transtext.=$from_petty[0]['account_title'].' '.$from_petty[0]['account_name'].'  '.$trans['reason'];
                                                    }

                                                    if ($transtext == '')
                                                    {

                                                        echo $trans ['reason'];
                                                    }else
                                                    {
                                                        echo $transtext;
                                                    }


                                                    ?> </td>
													<td> <?php  
														if( $trans ['proof_image'] == '' ) {
															
															if($trans ['daily_closing_id'] != null)
															{
																$this->db->select('*');
																$this->db->from('closing_perday');
																$this->db->where('id = "'.$trans ['daily_closing_id'].'"');
																$final = $this->db->get()->result_array();
																echo '<a target="_blank" href="'.site_url().'/closing/viewclosing/'.$final[0]['for_year'] .'-'.$final[0]['for_month'].'-'.$final[0]['for_day'].'/'.$final[0]['campus_id'].'/'.$final[0]['closed_amount'].'/1" class="fa fa-eye" > VIEW </a>';
															}



														}
														else {

															echo '<a data-toggle="modal" title="Add this item"  href="'. base_url().'uploads/'.  $trans['proof_image'].'" target="_blank" > <i class="fa fa-eye"></i>  VIEW   </a>';

														} ?> 
													</td>
													<td> <?php echo $trans ['transaction_by'] ?></td>

                                                <td style="text-align : right;"><?php


                                                    if( $trans ['debit_credit'] == 'D' ){
                                                        echo abs($trans ['amount']);
                                                        $debit+=abs($trans ['amount']);
                                                        $balance+=abs($trans ['amount']);

                                                    } ?></td>
                                                <td style="text-align : right;">
                                                    <?php


                                                    if( $trans ['debit_credit'] == 'C' ){
                                                        echo abs($trans ['amount']);
                                                        $credit+=abs($trans ['amount']);
                                                        $balance-=abs($trans ['amount']);

                                                    } ?>
                                                </td>
												
												<td style="text-align : right;"><?php



                                                    echo $balance;

                                                    ?></td>


                                            </tr>
                                            <?php
                                            $i++;
                                        endforeach;

									}

                                    ?>
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
