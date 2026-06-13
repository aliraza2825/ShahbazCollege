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
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Edit Profile <small>You can edit your profile here</small>
			</h3>-->
			
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
								<i class="fa fa-list"></i> Bank count Statement Here
							</div>
						</div>
						<div class="portlet-body">
                            <table class="table table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
                                <th>
									 Sr
								</th>
								<th>
									 Bank Name
								</th>
                                <th>
									 Transaction Date
								</th>
                                <th>
									 Transaction Counts
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
								$i=0;
								$count = 0;
								foreach($statements as $closing_rule):
							?>
                            <tr id="tr-<?php echo $i?>">
                                <td >
                                	<?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $closing_rule['bank_name']?>
								</td>
                                <td>
									 <?php echo $closing_rule['trans_date']?>
								</td>
                                <td>
                                    <?php
                                        $count+= $closing_rule['count'];
                                    echo $closing_rule['count'];?>
                                </td>
                            </tr>
                            <?php
								$i++;
                            	endforeach;
							?>
                            <tr>
                                <th>

                                </th>
                                <th>

                                </th>
                                <th>
                                    Total Entries
                                </th>
                                <th style="text-align: right; font-weight: bolder">
                                    <?php echo $count?>
                                </th>
                            </tr>
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