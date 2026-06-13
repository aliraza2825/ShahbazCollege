<?php
	$myAccess = checkUserAccess();
?>
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<?php
            	foreach($campuses as $campus):
			?>
            <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i>Campus Code <?php echo $campus['campus_code'];?> <small><?php echo $campus['campus_name'];?></small> 
                                <span class="text-right">
                                	(From : <?php 
										if(getFromDateProfitDistribution($campus['campus_id'])=='')
										{
											$from_date = '2000-01-01';
											echo 'Start';
										}
										else
										{
											$from_date = getFromDateProfitDistribution($campus['campus_id']);
											echo getFromDateProfitDistribution($campus['campus_id']);
										}
									?> - 
                                    Till : <?php echo date('d-m-Y');?>)
                                </span>
							</div>
						</div>
						<div class="portlet-body">
							<table class="table table-striped table-bordered table-hover">
							<thead>
							<tr>
                                <th>
									 Total Expense
								</th>
                                <th>
									 Total Recovery
								</th>
								<th>
									 Net Profit
								</th>
                                <th>
									 Partners
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							
                            <tr class="odd gradeX">
                                <td>
									 <?php
                                     $psart = 0;
                                     $seats = 0;
                                     $my_seats = 0;
                                     $partner = $this->db->get_where("campus_partners","campus_id = '".$campus['campus_id']."'")->row();
                                     @$port_campuses = json_decode($partner->campus_share_ids);
                                     @$port_seats = json_decode($partner->no_of_seats);
                                     if ($port_campuses):
                                     foreach (@$port_campuses as $i=>$port_campus):

                                         if ($port_campus == $campus['campus_id']){
                                             $my_seats = $port_seats[$i];
                                         }

                                         $seats += $port_seats[$i];

                                         $this_exp = totalExpense($port_campus, $from_date, date('Y-m-d'));
                                         echo @$this->db->get_where("campuses","campus_id = '$port_campus'")->row()->campus_name.' : '.$this_exp.'<br />';
                                         $psart += $this_exp ;
                                     endforeach;
                                     endif;
                                     if ($my_seats > 0)
                                        $totalExpense = (($psart / $seats) * $my_seats);
                                     $totalExpense = number_format((float)$totalExpense, 2, '.', '');
                                     echo '<br />Total Expense : Rs '.$psart;
                                     echo '<br /><br /><br />Divided Expense : Rs '.$totalExpense;

									?>
								</td>
                                <td>
									 <?php 
										$totalRecovery = totalRecovery($campus['campus_id'], $from_date, date('Y-m-d'))+totalRecoveryContractors($campus['campus_id'], $from_date, date('Y-m-d'));
										echo 'Rs '.$totalRecovery;
									?>
								</td>
								<td>
									Rs <?php echo $totalRecovery-$totalExpense;?>
								</td>
                                <td>
									<?php 
										echo getPartners($campus['campus_id']); 
									?>
								</td>
								<td>
									<a href="<?php echo site_url();?>/accounts/campus_profit/<?php echo $campus['campus_id'];?>" class="btn green"><i class="fa fa-eye"></i> View</a>
								</td>
							</tr>
							</tbody>
							</table>
						</div>
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>
            <?php
            	endforeach;
			?>
            
			<!-- END DASHBOARD STATS -->
			<div class="clearfix">
			</div>
            
		</div>
	</div>
	<!-- END CONTENT -->
</div>
<!-- END CONTAINER -->
