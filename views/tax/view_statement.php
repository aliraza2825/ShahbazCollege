<style>
    .button-tag {
        background-color: #e3a600;
        border: none;
        color: white;
        padding: 12px 24px;
        font-size: 16px;
        border-radius: 4px;
        margin: 20px;
    }
    .fata {
        margin-left: -12px;
        margin-right: 8px;
    }
</style>
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- END PAGE HEADER-->
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

            
            <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> Bank Statement Here
							</div>
						</div>
						<div class="portlet-body">
                            <table class="table table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
                                <th>
									 Sr
								</th>
								<?php
									if($this->uri->segment(6)=='closing_credit_in_bank' || $this->uri->segment(6)=='expenses'):
								?>
								<th>
									Campus Name
								</th>
								<?php
									endif;
								?>
								<?php
									if($this->uri->segment(6)=='expenses'):
								?>
								<th>
									Expense Head
								</th>
								<th>
									Expense Title
								</th>
								<?php
									endif;
								?>
								<th>
									 Bank Name
								</th>
                                <th>
									 Transaction Date
								</th>
                                <th>
									 Transaction Type
								</th>
<!--                                --><?php //if ($type == 'credit'): ?>
                                <th>
									 Credit
								</th>
<!--                                --><?php //else: ?>
                                <th>
									 Debit
								</th>
<!--                                --><?php //endif; ?>
                                <th>
                                    Balance
                                </th>
                                <th>
                                    Status
                                </th>
								<?php
									if($this->uri->segment(6)=='closing_credit_in_bank'):
								?>
								<th>
									Closing Image
								</th>
								<?php
									endif;
								?>
								<?php
									if($this->uri->segment(6)=='expenses'):
								?>
								<th>
									Expense Image
								</th>
								<?php
									endif;
								?>
                                
							</tr>
							</thead>
							<tbody>
							<?php
								$i=1;
								foreach($statements as $closing_rule):
							?>
                            <tr id="tr-<?php echo $i?>">
                                <td >
                                	<?php echo $i;?>
                                </td>
								<?php
									if($this->uri->segment(6)=='closing_credit_in_bank'):
								?>
								<td>
									<?php echo $this->db->get_where('campuses',array('campus_id'=>$closing_rule['closing_campus_id']))->row()->campus_name; ?>
								</td>
								<?php
									endif;
								?>
								<?php
									if($this->uri->segment(6)=='expenses'):
								?>
								<td>
									<?php echo $this->db->get_where('campuses',array('campus_id'=>$closing_rule['expense_campus_id']))->row()->campus_name; ?>
								</td>
								<td>
									<?php
										if($closing_rule['expense_category_id']==9){
											@print_expenses_categories($closing_rule['expense_category_id'], 0);
											echo '<br />';
											$user = $this->db->get_where('users', array('user_id'=>$closing_rule['user_id']))->result_array();
											echo @$user[0]['first_name'].' '.@$user[0]['last_name'];
										}else{
											@print_expenses_categories($closing_rule['expense_category_id'], 0);
										}
									?>
								</td>
								<td>
									<?php
										echo $closing_rule['expense_title'];
									?>
								</td>
								<?php
									endif;
								?>
                                <td>
									 <?php echo $closing_rule['account_title'].' '.$closing_rule['account_name']?>
								</td>
                                <td>
									 <?php echo $closing_rule['trans_date']?>
								</td>
                                <td>
									 <?php echo $closing_rule['description'].' '.$closing_rule['reference_no'];?>
								</td>
<!--                                --><?php //if ($type == 'credit'): ?>
								<td>
									 <?php echo $closing_rule['credit'];?>
								</td>
<!--                                --><?php //else: ?>
								<td>
                                    <?php echo $closing_rule['debit'];?>
								</td>
<!--                                --><?php //endif; ?>
                                <td>
                                    <?php echo $closing_rule['balance'];?>
                                </td>
                                <td>
                                    <?php
                                    echo $closing_rule['trans_id'];
                                    if($closing_rule['statement_id'] != '' || $closing_rule['statement_id'] != NULL)
                                    {

                                    }
                                    elseif ($closing_rule['expense_id'] != '' || $closing_rule['expense_id'] != NULL)
                                    {

                                    }
                                    elseif ($closing_rule['bank_transfer_id'] != '' || $closing_rule['bank_transfer_id'] != NULL)
                                    {
                                        echo $closing_rule['bank_transfer_id'].'<br />';
                                        if ($type == "sent" || $type == "received"){
                                            $this->db->select('*');
                                            $this->db->from('bank_reconciliation_statement');
                                            $this->db->join('accounts','accounts.id=bank_reconciliation_statement.account_id','left');
                                            $this->db->where(array('bank_reconciliation_statement.id'=>$closing_rule['bank_transfer_id']));
                                            $expense = $this->db->get()->row();

                                            if ($expense->credit != NULL && $expense->credit != ''):
                                                ?>
                                                <strong> Transfered to Account : </strong><?php echo $expense->account_name;?> <br>
                                            <?php else: ?>
                                                <strong> Received From Account : </strong><?php echo $expense->account_name;?> <br>
                                            <?php endif; ?>
                                            <strong> Date : </strong><?php echo $expense->trans_date;?> <br>
                                            <strong> Amount : </strong><?php echo $expense->credit.''.$expense->debit;?> <br>
                                            <?php
                                        }

                                    }
                                    elseif ($closing_rule['str_id'] != '' || $closing_rule['str_id'] != NULL)
                                    {
                                    }
                                    elseif ($closing_rule['closing_bank_id'] != '' || $closing_rule['closing_bank_id'] != NULL)
                                    {

                                    }
                                    elseif ($closing_rule['is_council_fee'] == "1" && ($closing_rule['expense_id'] == '' || $closing_rule['expense_id'] == NULL))
                                    {

                                    }
                                    elseif ( $closing_rule['paypro_id'] != '' || $closing_rule['paypro_id'] != NULL )
                                    {

                                    }
                                    elseif ( $closing_rule['salary_expense_ids'] != '' || $closing_rule['salary_expense_ids'] != NULL )
                                    {

                                    }
                                    elseif ( @$closing_rule['profit_distribution_id'] != '' || @$closing_rule['profit_distribution_id'] != NULL )
                                    {
                                        $this->db->select('*');
                                        $this->db->from('profit_distribution');
                                        $this->db->join('campuses','campuses.campus_id = profit_distribution.campus_id');
                                        $this->db->join('users','users.user_id = profit_distribution.user_id');
                                        $this->db->where("profit_distribution_id",$closing_rule['profit_distribution_id']);
                                        $entry = $this->db->get()->row();

                                        echo '<strong> Profit To : </strong>'. $entry->first_name.' '.$entry->last_name.'<br>
                                              <strong> From Date : </strong>'.$entry->from_date.'<br>
                                              <strong> To Date : </strong>'.$entry->to_date.'<br>
                                              <strong> Campus : </strong>'. $entry->campus_name .' <br>';

                                    }
                                    else
                                    {
                                       echo "UNTAGGED";
                                    }
                                    ?>
                                </td>
								<?php
									if($this->uri->segment(6)=='closing_credit_in_bank'):
								?>
								<td>
									<a href="<?php echo base_url();?>uploads/<?php echo $closing_rule['closing_image']?>" class="btn purple" target="_blank"><i class="fa fa-image"></i> Image</a>
								</td>
								<?php
									endif;
								?>
								<?php
									if($this->uri->segment(6)=='expenses'):
								?>
								<td>
									<?php
										if($closing_rule['expense_online_image']=='')
										{
											if($closing_rule['expense_image']!='')
											{
												$img_link = base_url().'uploads/'.$closing_rule['expense_image'];
											}
											else
											{
												$img_link='';
											}
										}
										else
										{
											$img_link = $closing_rule['expense_online_image'];
										}
									?>
									<?php
										if($img_link!=''):
									?>
									<a href="<?php echo $img_link;?>" class="btn purple" target="_blank"><i class="fa fa-image"></i> Image</a>
									<?php
										endif;
									?>
								</td>
								<?php
									endif;
								?>
                            </tr>
                            <?php
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
	</div>
	<!-- END CONTENT -->
<script>
    function deleteItem(id,index) {
        if (confirm("Are you sure you want to delete this entry?")) {
            $("#delete-"+index).hide();
            $("#loading_button-"+index).show();
            jQuery.ajax({
                url: '<?php echo site_url();?>/accounts/delete_entry',
                type: "post",
                async: false,
                data: {
                    id : id,
                },
                success: function (data) {
                    $('#tr-'+index).remove();
                },
                complete: function (data) {
                }
            });
            e.preventDefault();
        }
        return false;
    }
</script>