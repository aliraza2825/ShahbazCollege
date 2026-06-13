	
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
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i>Expense Categories
							</div>
						</div>
                        <button id="print-btn" type="button" class="btn btn-primary btn-sm d-print-none"><i class="dripicons-print"></i> Print</button>
                        <div class="portlet-body" id="print_div">
                            <br />
                            <br />
                            <div>
                                <table class="table table-striped table-bordered table-hover tree"  id="sample_ali">
                                    <thead>
                                    <tr>
                                        <th>
                                            Head Name
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $i=1;
                                    foreach($categories as $category):
                                        if ($category['has_sub'] != "1"):
                                            ?>
                                            <tr class="treegrid-<?php echo $i?>">
                                                <td><?php echo $category['name'];?></td>
                                            </tr>
                                        <?php
                                        else:?>
                                            <tr class="treegrid-<?php echo $i?> expanded">
                                                <td><strong><?php echo $category['name'];?></strong></td>
                                            </tr>
                                            <?php
                                            $das = Print_simple_expenses($category['expense_category_id'],$i,"",$my_campus);
//                                        echo $das['data'];
                                            $i = $das['index'];
                                        endif;
                                        $i++;
                                    endforeach;
                                    ?>
                                    </tbody>
                                </table>
                            </div>
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
        document.addEventListener('DOMContentLoaded', () => {
            $('.tree').treegrid({
                enableMove: true,
                onMoveOver: function(item, helper, target, position) {
                    if (target.hasClass('treegrid-8')) return false;
                    return true;
                }
            });
            $("#print-btn").on("click", function(){
                var divToPrint=document.getElementById('print_div');
                var newWin=window.open('','Print-Window');
                newWin.document.open();
                newWin.document.write('<link rel="stylesheet" href="<?php echo base_url();?>assets/global/plugins/bootstrap/css/bootstrap.min.css" type="text/css"><style type="text/css">@media print {}</style><body onload="window.print()">'+divToPrint.innerHTML+'</body>');
                newWin.document.close();
            });
        });
    </script>