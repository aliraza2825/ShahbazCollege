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
                    <div class="col-md-12 ">
                        <!-- BEGIN SAMPLE FORM PORTLET-->
                        <div class="portlet box green ">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-user"></i> Petty Cash Accounts Debit / Credit Details of <?php echo $selected_date ?> ( <?php echo $user->first_name.' '.$user->last_name;?> )
                                </div>
                            </div>
                            <div class="portlet-body table-responsive">
                                <div class="col-md-12 ">
                                    <div class="col-md-4">
									</div>
                                </div>
                                <table class="table table-bordered table-hover" >
                                    <thead>
                                    <tr>
                                        <th>
                                            Sent From
                                        </th>
                                        <th>
                                            Received To
                                        </th>
                                        <th>
                                            Amount
                                        </th>
                                        <th>
                                            Reason
                                        </th>
                                        <th>
                                            Proof Image
                                        </th>
                                        <th>
                                            Status
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $i=0;
                                        foreach($debit_credit_data as $debit_credit):
                                            ?>
                                            <tr class="odd gradeX">
                                                <td><?php
                                                    if ($debit_credit['from_account'] == NULL){
                                                            $for = $this->db->join("users","users.user_id = petty_cash_college_wise.assign_to")->get_where("petty_cash_college_wise","id = '".$debit_credit['from_pettycash_id']."'")->row();
                                                            echo $for->first_name.' '.$for->last_name;
                                                    }
                                                    if ($debit_credit['from_pettycash_id'] == NULL){
                                                        $for = $this->db->get_where("accounts","id = '".$debit_credit['from_account']."'")->row();
                                                        echo $for->account_name;
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php
                                                    if ($debit_credit['to_account'] == NULL){
                                                            $for = $this->db->join("users","users.user_id = petty_cash_college_wise.assign_to")->get_where("petty_cash_college_wise","id = '".$debit_credit['to_pettycash_id']."'")->row();
                                                            echo $for->first_name.' '.$for->last_name;
                                                    }
                                                    if ($debit_credit['to_pettycash_id'] == NULL){
                                                        $for = $this->db->get_where("accounts","id = '".$debit_credit['to_account']."'")->row();
                                                        echo $for->account_name;
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php  echo $debit_credit['amount_given']; ?></td>
                                                <td><?php  echo $debit_credit['reason']; ?></td>
                                                <td><?php if ($debit_credit['proof_image'] != NULL && $debit_credit['proof_image'] != ""){
                                                        echo "<a href='".site_url()."/uploads/". $debit_credit['proof_image']."'></a>";

                                                    } ?> </td>
                                                <td><?php  echo $debit_credit['trans_status']; ?></td>
                                            </tr>
                                            <?php
                                            $i++;
                                        endforeach;
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
