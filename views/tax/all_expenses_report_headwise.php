<?php
$myAccess = checkUserAccess();
?>
<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <!--<h3 class="page-title">
        All Classes <small>Here you can find all classes</small>
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
		
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet box grey-cascade">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i> Check Headwise Expense
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/tax/expense_report_headwise">
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label col-md-3">From Date <span class="required">*</span></label>
                                            <div class="col-md-3">
                                                <div class="input-group input-medium date date-picker" data-date="<?php if(@$this->input->post('from_date')){echo $this->input->post('from_date');}else{echo date('Y-m-d');}?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                    <input type="text" name="from_date" class="form-control" value="<?php if(@$this->input->post('from_date')){echo $this->input->post('from_date');}else{echo date('Y-m-d');}?>" readonly>
                                                    <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label col-md-3">To Date <span class="required">*</span></label>
                                            <div class="col-md-3">
                                                <div class="input-group input-medium date date-picker" data-date="<?php if(@$this->input->post('to_date')){echo $this->input->post('to_date');}else{echo date('Y-m-d');}?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                    <input type="text" name="to_date" class="form-control" value="<?php if(@$this->input->post('to_date')){echo $this->input->post('to_date');}else{echo date('Y-m-d');}?>" readonly>
                                                    <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">Campus</label>
                                            <div class="col-md-9">
                                                <select name="campus_ids[]" id="select2_sample4" class="form-control input-inline input-large select2" multiple>
                                                    <?php
                                                    foreach($campuses as $campus):
                                                        ?>
                                                        <option value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name'];?></option>
                                                    <?php
                                                    endforeach;
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">Categories</label>
                                            <div class="col-md-9">
                                                <select name="categories[]" id="select2_sample1" class="form-control input-inline input-large select2" multiple>
                                                    <?php
                                                    foreach($headCategories as $category):
                                                        ?>
                                                        <option value="<?php echo $category['expense_category_id'];?>"><?php echo $category['name'];?></option>
                                                    <?php
                                                    endforeach;
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
									<div class="col-md-6">
										<div class="form-group">
											<label class="col-md-3 control-label">Date Type</label>
											<div class="col-md-6 radio-list">
												<label class="radio-inline">
												<input type="radio" name="date_type" id="optionsRadios4" value="actual_date" checked /> Actual Date </label>
												<label class="radio-inline">
												<input type="radio" name="date_type" id="optionsRadios5" value="upload_date" /> Upload Date </label>
											</div>
										</div>
									</div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <button type="submit" class="btn green">Check Expense</button>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>
                    <?php
                    if(@count(@$expenses)>0 ):
                        ?>
                        <?php $campus_ids = implode(',',$this->input->post('campus_ids'));?>
                        <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th class="hidden">
                                        Hidden
                                    </th>
                                    <th>
                                        Sr
                                    </th>
                                    <th>
                                        Category
                                    </th>
									<th>
										Paid By Bank
									</th>
									<th>
										Paid By Cash
									</th>
                                    <th>
                                        Total Expense
                                    </th>
                                    <th>
                                        View
                                    </th>

                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $i = 1;
                                $total=0;
								$total_by_bank =0;
								$total_by_cash = 0;
                                foreach($expenses as $expense):
									
                                    ?>
                                    <tr class="odd gradeX">
                                        <td class="hidden">
                                            <?php echo $i;?>
                                        </td>    
                                        <td >
                                            <?php echo $i;?>
                                        </td>
                                        <td>
                                            <?php

                                            echo $expense['name'];

                                            ?>
                                        </td>
										<td>
											<?php

                                            echo $expense['by_bank'];
											$total_by_bank += $expense['by_bank'];

                                            ?>
										</td>
										<td>
											<?php

                                            echo $expense['by_cash'];
											$total_by_cash += $expense['by_cash'];
                                            ?>
										</td>
                                        <td>
                                            <?php 
												echo $expense['total_amount'];
												$total+=$expense['total_amount'];
                                            ?>
                                        </td>

                                        <td>
                                            <a href="<?php echo site_url().'/tax/all_expenses_details_headwise/'.$from_date.'/'.$to_date.'/'.$expense['expense_category_id'].'/'.$campus_ids.'/'.$this->input->post('date_type');?>" target="_blank">
                                                <button type="button" class="btn btn-default"><i class="fa fa-eye"></i> View Details</button>
                                            </a>
                                        </td>

                                    </tr>
                                    <?php
                                    $i++;
                                endforeach;
                                ?>
								<!--
                                <tr>
                                    <th>

                                    </th>
                                    <th>
                                        Total Amount
                                    </th>
									<th>
										<?php //echo $total_by_bank;?>
                                    </th>
                                    <th>
										<?php //echo $total_by_cash;?>
                                    </th>
									

                                    <th>
                                        <?php //echo $total;
                                        ?>
                                    </th>
									<th>

                                    </th>
                                </tr>
								-->
                                </tbody>
                            </table>
                        </div>
                    <?php
                    endif;
                    ?>
                </div>
                <!-- END EXAMPLE TABLE PORTLET-->
            </div>
        </div>
		
		
		
		<div class="row">
			<div class="col-md-12">
				<!-- BEGIN EXAMPLE TABLE PORTLET-->
				<div class="portlet box grey-cascade">
					<div class="portlet-title">
						<div class="caption">
							<i class="fa fa-list"></i> Expenses
						</div>
					</div>
					<div class="portlet-body">

						<br />
						<br />
						<div>
							<table class="table table-striped table-bordered table-hover tree"  id="sample_ali">
								<thead>
									<tr>
										<th>
											Head Name
										</th>
										<th>
											Bank Expense
										</th>
										<th>
											Cash Expense
										</th>
										<th>
											Bank Expense Not Tagged
										</th>
										<th>
											Total Expense
										</th>
									</tr>
								</thead>
								<tbody>
								<?php
								$i=1;
								$totalBankExpenses = 0;
								$totalCashExpenses = 0;
								$totalUntaggedBankExpenses =0;
								$totalHeadExpenses = 0 ;
								foreach($categories as $category):
									if ($category['has_sub'] != "1"):
										?>
										<tr class="treegrid-<?php echo $i?>">
											<td><?php echo $category['name'];?></td>
											<td>
												<?php
													echo getBankExpense($category['expense_category_id'],$from_date,$to_date,$date_type,$campus_ids);
												?>
											</td>
											<td>
												<?php
													echo getCashExpense($category['expense_category_id'],$from_date,$to_date,$date_type,$campus_ids);
												?>
											</td>
											<td>
												<?php
													echo notTaggedBankExpenses($category['expense_category_id'],$from_date,$to_date,$date_type,$campus_ids);
												?>
											</td>
											<td>
												<?php
													echo getBothExpense($category['expense_category_id'],$from_date,$to_date,$date_type,$campus_ids);
												?>
											</td>
										</tr>
									<?php
									else:?>
										<tr class="treegrid-<?php echo $i?>">
											<td><strong><?php echo $category['name'];?></strong></td>
											<td>
												<?php
													echo $bankExpense = getBankExpense($category['expense_category_id'],$from_date,$to_date,$date_type,$campus_ids);
													$totalBankExpenses += $bankExpense;
												?>
											</td>
											<td>
												<?php
													echo $cashExpense = getCashExpense($category['expense_category_id'],$from_date,$to_date,$date_type,$campus_ids);
													$totalCashExpenses += $cashExpense;
												?>
											</td>
											<td>
												<?php
													echo $notTaggedBankExpenses = notTaggedBankExpenses($category['expense_category_id'],$from_date,$to_date,$date_type,$campus_ids);
													$totalUntaggedBankExpenses += $notTaggedBankExpenses;
												?>
											</td>
											<td>
												<?php
													echo $mainHeadExpenses = getBothExpense($category['expense_category_id'],$from_date,$to_date,$date_type,$campus_ids);
													$totalHeadExpenses +=$mainHeadExpenses;
												?>
											</td>
										</tr>
										<?php
										$das = Print_tax_expenses($category['expense_category_id'],$i,"",$my_campus,$from_date,$to_date,$date_type,$campus_ids);
										$i = $das['index'];
									endif;
									$i++;
								endforeach;
								?>
									<tr>
										<td><strong>Total</strong></td>
										<td><strong><?php echo $totalBankExpenses;?></strong></td>
										<td><strong><?php echo $totalCashExpenses;?></strong></td>
										<td><strong><?php echo $totalUntaggedBankExpenses;?></strong></td>
										<td><strong><?php echo $totalHeadExpenses;?></strong></td>
									</tr>
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
            $(document).on("click", ".open-expreversal", function () {

                var id = $(this).data('id');
                var name = $(this).data('name');

                $(".modal-body #expense_category_id").val(id);
                $(".modal-body #p_cat_name").val(name);

            });
            var table = $('#sample_ali');
            var printCounter = 0;
            table.dataTable({

                // Internationalisation. For more info refer to http://datatables.net/manual/i18n
                "language": {
                    "aria": {
                        "sortAscending": ": activate to sort column ascending",
                        "sortDescending": ": activate to sort column descending"
                    },
                    "emptyTable": "No data available in table",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                    "infoEmpty": "No entries found",
                    "infoFiltered": "(filtered1 from _MAX_ total entries)",
                    "lengthMenu": "Show _MENU_ entries",
                    "search": "Search:",
                    "zeroRecords": "No matching records found"
                },

                "buttons": [
                    'copy',
                    {
                        extend: 'excel',
                        messageTop: 'The information in this table is copyright to Sirius Cybernetics Corp.'
                    },
                    {
                        extend: 'pdf',
                        messageBottom: null
                    },
                    {
                        extend: 'print',
                        messageTop: function () {
                            printCounter++;

                            if ( printCounter === 1 ) {
                                return 'This is the first time you have printed this document.';
                            }
                            else {
                                return 'You have printed this document '+printCounter+' times';
                            }
                        },
                        messageBottom: null
                    }
                ],
                "ordering": false,
                "lengthMenu": [
                    [-1],
                    ["All"] // change per page values here
                ],

                // set the initial value
                "pageLength": -1,
                "dom": "<'row' <'col-md-12'T>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r><'table-scrollable't><'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>", // horizobtal scrollable datatable
                // Uncomment below line("dom" parameter) to fix the dropdown overflow issue in the datatable cells. The default datatable layout
                // setup uses scrollable div(table-scrollable) with overflow:auto to enable vertical scroll(see: assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js).
                // So when dropdowns used the scrollable div should be removed.
                //"dom": "<'row' <'col-md-12'T>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r>t<'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>",

                "tableTools": {
                    "sSwfPath": "../../assets/global/plugins/datatables/extensions/TableTools/swf/copy_csv_xls_pdf.swf",
                    "aButtons": [{
                        "sExtends": "pdf",
                        "sButtonText": "PDF"
                    }, {
                        "sExtends": "csv",
                        "sButtonText": "CSV"
                    }, {
                        "sExtends": "xls",
                        "sButtonText": "Excel"
                    }, {
                        "sExtends": "print",
                        "sButtonText": "Print",
                        "sInfo": 'Please press "CTRL+P" to print or "ESC" to quit',
                        "sMessage": "Generated by DataTables"
                    }, {
                        "sExtends": "copy",
                        "sButtonText": "Copy"
                    }]
                }
            });

            var tableWrapper = $('#sample_ali_wrapper'); // datatable creates the table wrapper by adding with id {your_table_jd}_wrapper
            tableWrapper.find('.dataTables_length select').select2(); // initialize select2 dropdown
        });
    </script>