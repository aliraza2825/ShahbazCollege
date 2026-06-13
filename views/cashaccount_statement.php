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
				
					<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/accounts/cashaccountreport" enctype="multipart/form-data">
								<div class="form-body">
									
									<div class="form-group">
										<label class="control-label col-md-2">From Date</label>
										<div class="col-md-2">
											<div class="input-group input-medium date date-picker" data-date="<?php echo date('m/d/Y')?>" data-date-format="mm/dd/yyyy" data-date-viewmode="years">
												<input type="text" name="from_date" class="form-control" value="<?php echo date('m-d-Y');?>" readonly>
												<span class="input-group-btn">
												<button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
												</span>
											</div>
										</div>
										
										<label class="control-label col-md-2">To Date</label>
										<div class="col-md-2">
											<div class="input-group input-medium date date-picker" data-date="<?php echo date('m/d/Y')?>" data-date-format="mm/dd/yyyy" data-date-viewmode="years">
												<input type="text" name="to_date" class="form-control" value="<?php echo date('m/d/Y');?>" readonly>
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
				
				
				

                    <div class="col-md-12 ">
                        <!-- BEGIN SAMPLE FORM PORTLET-->
                        <div class="portlet box green ">

                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-user"></i> Cash in Hand Statement
                                </div>
                            </div>



                            <div class="portlet-body table-responsive">


                                <table class="table table-bordered table-hover" >
                                    <thead>
                                    <tr>
                                        <th >
                                            Sr
                                        </th>
										<th>
                                            Transaction Date
                                        </th>
										<th>
                                            Closing Date
                                        </th>
                                        <th>
                                            Text
                                        </th>
                                        <th>
                                            Campus
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
                                        <th>
                                            View
                                        </th>



                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
									
									if(@$accountstatement){
									
                                    $i=0;
                                    $totalcredit =0;
                                    $totaldebit =0;
                                    $balance =0;

                                        foreach($accountstatement as $trans):
													$class = ($i%2 == 0)? 'white': 'lightgray';

													
                                            ?>
                                            <tr style="background-color : <?php echo $class ?>;">
                                                <td >
                                                    <?php echo $i+1;?>
                                                </td>
												<td><?php 

													    $sec = strtotime($trans['created_at']);  
														//converts seconds into a specific format  
														$newdate = date ("d-m-Y H:i", $sec);  
														//Appends seconds with the time  
														$newdate = $newdate . ":00";  
														
														echo   $newdate?></td>
														
												<td><?php  echo $trans ['closing_date']  ?></td>

                                                <td>
                                                    <?php  echo $trans ['text']  ?>
                                                </td>
                                                <td><?php  echo $trans ['campus_name'] ?></td>
                                                <td style="text-align : right;">
													<?php if($trans ['debit_credit'] == 'D'){
																echo $trans ['amount']; 
																
												} ?></td>
                                                <td style="text-align : right;">
													<?php if ($trans ['debit_credit'] == 'C')
															{ 
																echo $trans ['amount']; 
																
															}
													?>
												</td>
												
												<td style="text-align : right;">
													<?php if ($trans ['debit_credit'] == 'D')
															{ 
																
																$totaldebit+=$trans ['amount'];	
																$balance=$balance+$trans['amount'];
																echo $balance;
																
																 
															}else{
																
																
																$totalcredit+=$trans['amount'] ;
																$balance=$balance-$trans['amount'];
																
																echo $balance;
																
															}
															
															
													?>
												</td>
                                                <td>
													<?php if($trans ['closing_date'] !== '-'): 
													?>
													 
													 
													  <a target="_blank" title="View Closing" class="btn green" href="<?php echo site_url();?>/closing/dailyclosingview/<?php  echo $trans ['closing_id']?>"><i class="fa fa-eye"></i></a>
													 
													 <?php 
														else :
														
														if($trans['closing_id'] !== '' && $trans['closing_id'] !== ' '):
														
													 ?>
													 
													 <a class="btn green" href="<?php echo base_url();?>uploads/<?php echo $trans['closing_id']?>" target="_blank"> <i class="fa fa-image"></i> </a>
													 
													 <?php endif; endif; ?>  
												</td>

                                            </tr>
                                            <?php
                                            $i++;
                                        endforeach;
									}

                                    ?>
                                    </tbody>
                                    <tfoot>
                                    <tr class="odd gradeX">
                                        <td >

                                        </td>
                                        <td>

                                        </td>
                                        <td></td>
                                        <td></td>
										<td style="text-align : right; font-weight : bold;">Closing Amount</td>
                                        <td style="text-align : right; font-weight : bold;">Total Debit</td>
                                        <td style="text-align : right; font-weight : bold;">Total Credit</td>
                                        <td></td>
										<td></td>
                                        


                                    </tr>

                                    <tr class="odd gradeX">
                                        <td >

                                        </td>
                                        <td>

                                        </td>
                                        <td></td>
                                        <td></td>
										<td style="text-align : right; font-weight : bold;"><?php  echo $totaldebit-$totalcredit  ?></td>

                                        <td style="text-align : right; font-weight : bold;"><?php echo $totaldebit ?></td>
                                        <td style="text-align : right; font-weight : bold;"><?php echo $totalcredit ?></td>
                                        <td></td>
										<td></td>
                                        

                                    </tr>
                                    </tfoot>
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
