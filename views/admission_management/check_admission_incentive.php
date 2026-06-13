
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
			<div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
                            	<?php
									$campus_ids = explode(',',$recovery[0]['campus_ids']);
									$this->db->where_in('campus_id',$campus_ids);
									$campuses = $this->db->get('campuses')->result_array();


									  $recovery_management_id = $this->uri->segment(3);
                                                        $this->db->order_by('start','ASC');
                                                        //$this->db->limit(1);
                                                        $comission_rule = $this->db->get_where('admission_management_rules',array('admission_incentive_id'=>$recovery_management_id,'start<='=>$counted,'end>='=>$counted))->result_array();
														$com=0;
														if(count($comission_rule)>0)
														{
															$com=$comission_rule[0]['comission'];

														}


								?>
								<i class="fa fa-user"></i> <?php

									$user=$this->db->get_where('users',array('user_id'=>$this->uri->segment(4)))->result_array();

								echo $user[0]['first_name'].' '.$user[0]['last_name']?>
                                ( Admission Incentive Details)
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/admission_management/check_recovery/<?php echo $this->uri->segment(3);?>/<?php echo $this->uri->segment(4);?>">
								<div class="form-body">
                                    <div class="row">
									
										<div class="col-md-12">
                                            <div class="col-md-6">
                                                <div class="row">
                                                           <h2 class="col-md-12" style=" margin-top: 0px; margin-bottom: 0px;">Insentive Details</h2>
                                                </div>
                                                <div class="row" style="border: 1px solid black; padding: 5px; margin: 7px">

                                                   <?php
                                                   $rules = $this->db->get_where('admission_management_rules',array('admission_incentive_id'=>$this->uri->segment(3)))->result_array();

													echo '<div class="col-md-12">
													<div class="col-md-2">Slap</div>
													<div class="col-md-2">Admission From</div>
													<div class="col-md-2">Admission to</div>
													<div class="col-md-2">Amount</div>
													<div class="col-md-2">Minimum Amount</div>
													<div class="col-md-2">Maximum Amount</div>
													</div>';

                                                   foreach($rules as $key=>$rule)
                                                   {
                                                       echo '<div class="col-md-12"><div class="col-md-2">'. ($key+1).'</div><div class="col-md-2">'. $rule['start'].'</div>
													   <div class="col-md-2">'. $rule['end'].'</div>
													   <div class="col-md-2">'. $rule['comission'].'</div>
													   <div class="col-md-2">'. $rule['comission']*$rule['start'].'</div>
													   <div class="col-md-2">'. $rule['comission']*$rule['end'].'</div>
													   </div>';
                                                       
                                                   }
                                                   ?>

                                            </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <h2 class="col-md-12" style=" margin-top: 0px; margin-bottom: 0px;">Campuses</h2>
                                                </div>
                                                <div class="row" style="border: 1px solid black; padding: 5px; margin: 7px">

                                                    <?php
                                                    foreach($campuses as $campus)
                                                    {
                                                        echo $campus['campus_name'];
                                                        echo '<br />';
                                                    }
                                                    ?>

                                                </div>
                                            </div>

                                         </div>

                                              
                                        <div class="col-md-12">

                                           <div class="row">
                                               <div class="col-md-6">
                                                   <label class="control-label col-md-3">From Date</label>
                                                   <div class="input-group input-medium date date-picker col-md-9" data-date="<?php echo $from_date;?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                       <input type="text" name="from_date" class="form-control" value="<?php echo $from_date;?>" readonly>
                                                       <span class="input-group-btn">
                                                            <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                            </span>
                                                   </div>
                                               </div>

                                               <div class="col-md-6">
                                                   <label class="control-label col-md-3">To Date</label>
                                                   <div class="input-group input-medium date date-picker col-md-9" data-date="<?php echo $to_date;?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                       <input type="text" name="to_date" class="form-control" value="<?php echo $to_date;?>" readonly>
                                                       <span class="input-group-btn">
                                                            <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                            </span>
                                                   </div>
                                               </div>

                                           </div>

                                        </div>


                                    </div>

                                <hr/>

                                    <div class="row">

                                        <div class="col-md-2" style="height: 130px;">
                                            <div class="dashboard-stat blue-madison">
                                                <div class="visual">
                                                    <i class="fa fa-list"></i>
                                                </div>
                                                <div class="details">
                                                    <div class="number">
                                                        <?php echo count($total_paid_students) + count($total_unpaid_students);?>
                                                    </div>
                                                    <div class="desc" style="font-size: small">
                                                         Total Students
                                                    </div>
                                                </div>
                                                <a class="more" href="<?php echo site_url();?>/admission_management/all_entries/<?php echo $recoveryid ?>/0/<?php echo $this->uri->segment(4).'/'.$from_date.'/'.$to_date.'/'.$com;?>">
                                                    View more <i class="m-icon-swapright m-icon-white"></i>
                                                </a>
                                            </div>
                                            <br />
                                        </div>

                                       
                                        
                                        <div class="col-md-2" style="height: 130px;">
                                            <div class="dashboard-stat blue-madison">
                                                <div class="visual">
                                                    <i class="fa fa-trash"></i>
                                                </div>
                                                <div class="details">
                                                    <div class="number">
                                                        <?php echo $counted;?>
                                                    </div>
                                                    <div class="desc" style="font-size: small">
                                                         Total Countable Admissions
                                                    </div>
                                                </div>
                                                <a class="more" href="<?php echo site_url();?>/admission_management/all_entries/<?php echo $recoveryid ?>/1/<?php echo $this->uri->segment(4).'/'.$from_date.'/'.$to_date.'/'.$com;?>">
                                                    View more <i class="m-icon-swapright m-icon-white"></i>
                                                </a>
                                            </div>
                                            <br />
                                        </div>
                                        

                                        <div class="col-md-2" style="height: 130px;">
                                            <div class="dashboard-stat blue-madison">
                                                <div class="visual">
                                                    <i class="fa fa-bank"></i>
                                                </div>
                                                <div class="details">
                                                    <div class="number">
                                                        <?php

                                                        echo $uncounted;
                                                        ?>
                                                    </div>
                                                    <div class="desc" style="font-size: small">
                                                        Uncounted Admissions
                                                    </div>
                                                </div>
                                                <a class="more" href="<?php echo site_url();?>/admission_management/all_entries/<?php echo $recoveryid ?>/2/<?php echo $this->uri->segment(4).'/'.$from_date.'/'.$to_date.'/'.$com;?>">
                                                    View more <i class="m-icon-swapright m-icon-white"></i>
                                                </a>
                                            </div>
                                            <br />
                                        </div>

                                        <div class="col-md-2" style="height: 130px;">
                                            <div class="dashboard-stat blue-madison">
                                                <div class="visual">
                                                    <i class="fa fa-bank"></i>
                                                </div>
                                                <div class="details">
                                                    <div class="number">
                                                        <?php
                                                      
                                                        if(count($comission_rule)>0)
                                                        {
                                                            $installment_comission =  $comission_rule[0]['comission']*$counted;
                                                            echo 'Rs '.$installment_comission;
                                                        }
                                                        else
                                                        {
                                                            $installment_comission=0;
                                                            echo '0';
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="desc" style="font-size: small">
                                                        Total Incentive this Month
                                                    </div>
                                                </div>
													<a class="more" href="#">
                                                       ____ <i class=""></i>
                                                </a>
                                            </div>
                                            <br />
                                        </div>

                                    </div>

								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Check</button>
											<button onclick="location.href = '<?php echo site_url();?>/admission_management/check_recovery/<?php echo $recoveryid ?>'" type="button" class="btn default">Cancel</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->