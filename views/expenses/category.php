	
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
								<i class="fa fa-list"></i> Add Expense Category 
							</div>
						</div>
                        <div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/expenses/add_category">
								<div class="form-body">
								<div class="row" style="display: none;">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Head Category <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <Select name="head_category" style="width: 40%;" id="select2_sample1" class="form-control input-inline select2">
														<option value="">Select Head</option>
														<?php foreach($categories as $category): ?>
															<option value="<?php echo $category['expense_category_id']; ?>"><?php echo $category['name']; ?></option>
														<?php endforeach; ?>
													</Select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                	<div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Category <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="text" class="form-control input-inline input-medium" name="name" placeholder="Enter Category Name" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                	<div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label"> Select for Campus <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select class="form-control select2" id="select2_sample2" name="campus_ids[]" multiple>
                                                        <?php
                                                        foreach($campuses as $campuse):
                                                            ?>
                                                            <option value="<?php echo $campuse['campus_id'];?>"><?php echo $campuse['campus_name']?></option>
                                                        <?php
                                                        endforeach;
                                                        ?>
                                                    </select>
                                                    <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Add</button>
										</div>
									</div>
								</div>
                            </form>
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/expenses/category">
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label"> Select for Campus <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select class="form-control select2" name="campus_id">
                                                        <option value="">All Campuses</option>
                                                        <?php
                                                        foreach($campuses as $campus):
                                                            ?>
                                                            <option value="<?php echo $campus['campus_id'];?>"  <?php echo ($campus['campus_id'] == $my_campus) ? 'selected' : '';?>><?php echo $campus['campus_name']?>
                                                            </option>
                                                        <?php
                                                        endforeach;
                                                        ?>
                                                    </select>
                                                    <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-offset-3 col-md-9">
                                            <button type="submit" class="btn green">Find</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
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
								<i class="fa fa-list"></i>Expense Categories &nbsp; &nbsp; <?php if($my_campus): ?> <a target="_blank" class="btn btn-primary" href="<?php echo site_url();?>/expenses/print_categories/<?php echo $my_campus;?>"><i class="dripicons-print"></i> Print</a> <?php endif; ?>
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
                                            Status
                                        </th>
                                        <th>
                                            Action
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
                                                <td><?php echo $category['status'];?></td>
                                                <td><a href="<?php echo site_url();?>/expenses/edit_expense_category/<?php echo $category['expense_category_id'];?>" class="btn blue"><i class="fa fa-edit"></i></a> <a data-toggle='modal' data-id='<?php echo $category['expense_category_id']; ?>' data-name='<?php echo $category['name']; ?>' class='open-expreversal btn btn-primary' style='width: 150px' href='#expense_category_create' > Add Category?</a></td>
                                            </tr>
                                        <?php
                                        else:?>
                                            <tr class="treegrid-<?php echo $i?> expanded">
                                                <td><?php echo $category['name'];?></td>
                                                <td><?php echo $category['status'];?></td>
                                                <td><a href="<?php echo site_url();?>/expenses/edit_expense_category/<?php echo $category['expense_category_id'];?>" class="btn blue"><i class="fa fa-edit"></i></a> <a data-toggle='modal' data-id='<?php echo $category['expense_category_id']; ?>' data-name='<?php echo $category['name']; ?>' class='open-expreversal btn btn-primary' style='width: 150px' href='#expense_category_create' > Add Category?</a></td>
                                            </tr>
                                            <?php
                                            $das = Print_expenses($category['expense_category_id'],$i,"",$my_campus);
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
    <div class="modal fade" id="expense_category_create" tabindex="-1"   data-width="1200" >

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title">Expense Reversal</h4>
        </div>
        <div class="modal-body">
            <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/expenses/add_category">
                <div class="form-body">
                    <div class="form-group">
                        <div class="col-md-12">
                            <label class="form-control" style="text-align: center" >Create Expense?</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Parent Category</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control input-inline input-medium" id="p_cat_name" placeholder="Enter Category Name" value="" readonly required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Name</label>
                        <div class="col-md-9">
                            <input type="text"  name="name" id="name" class="form-control mobile" required/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="col-md-3 control-label"> Select for Campus <span class="required">*</span></label>
                                <div class="col-md-9">
                                    <select class="form-control select2" id="select2_sample3" name="campus_ids[]" multiple>
                                        <?php
                                        foreach($campuses as $campus):
                                            ?>
                                            <option value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name']?>
                                            </option>
                                        <?php
                                        endforeach;
                                        ?>
                                    </select>
                                    <!--<span class="help-inline"></span>-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-12" style="text-align: center">
                            <input type="hidden" id="expense_category_id" name="head_category" value="" />
                            <input type="hidden" name="last_edit" value="<?php echo $this->session->userdata('user_id');?>" />
                            <button type="submit" class="btn red">Submit</button>
                        </div>
                    </div>
                </div>
            </form>
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