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
            if(@$myAccess[0]['loan_approval']==1 || $this->session->userdata('role')=='Admin'):
                ?>
                <div class="row">
                    <div class="col-md-6">
                        <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/loans/loans_list" enctype="multipart/form-data">
                            <div class="form-body">
                                <div class="col-md-12">
                                    <label class="control-label col-md-3">Select Users</label>
                                    <div class="form-group col-md-6">
                                        <select class="form-control select2" name="users[]" id="select2_sample1" multiple>
                                            <?php
                                            foreach($users as $campus):
                                                ?>
                                                <option value="<?php echo $campus['user_id'];?>"><?php echo $campus['first_name'].' '.$campus['last_name'].' '.$campus['cnic']?></option>
                                            <?php
                                            endforeach;
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3">Select Status Type</label>
                                    <div class="form-group col-md-6">
                                        <select class="form-control" name="type" required>
                                            <option value="0">Pending</option>
                                            <option value="1">Running</option>
                                            <option value="3">Cleared</option>
                                            <option value="2">Rejected</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <input type="hidden" name="submit" value="1" />
                                        <button type="submit" class="btn green">Check Statement</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <a data-toggle='modal' class='btn btn-warning' style='width: 100px'>PENDING</a> Waiting For Loan Approval<br />
                        <a data-toggle='modal' class='btn btn-danger'  style='width: 100px'  >REJECTED</a> Rejected Loan Request<br />
                        <a data-toggle='modal' class='btn btn-success' style='width: 100px' >Cleared</a> Fully Paid Loans By Employee<br />
                        <a data-toggle='modal' class='btn btn-primary' style='width: 100px' >Running</a> Loan Given to Employee Had remaining Payable Installments.<br />
                    </div>
                    <div class="col-md-12">
                        <!-- BEGIN SAMPLE FORM PORTLET-->
                        <div class="portlet box green ">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-user"></i> Loans Approval
                                </div>
                            </div>
                            <div class="portlet-body table-responsive">
                                <table class="table table-bordered table-hover" id="sample_ali">
                                    <thead>
                                    <tr>
                                        <th class="hidden">
                                            Hidden
                                        </th>
                                        <th>
                                            View Loan Details
                                        </th>
                                        <th>
                                            Employee Info
                                        </th>
                                        <th>
                                            Loan Type
                                        </th>

                                        <th>
                                            For Months
                                        </th>
                                        <th>
                                            Undertaken
                                        </th>
                                        <th>
                                            Amount Applied
                                        </th>
                                        <th>
                                            Reason
                                        </th>
                                        <th>
                                            Status
                                        </th>
                                        <th>
                                            Paid installments
                                        </th>
                                        <th>
                                            Remaining installments
                                        </th>
                                        <th>
                                            Amount Approved
                                        </th>
                                        <th>
                                            Approved By
                                        </th>
                                        <th>
                                            For Months
                                        </th>
                                        <th>
                                            Created Date
                                        </th>
                                        <th>
                                            Created By
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $i=0;
                                        foreach($loans as $loan):
                                            ?>
                                            <tr class="odd gradeX">
                                                <td class="hidden">
                                                    <?php echo $i;?>
                                                </td>

                                                <td >
													 <a class="btn green" href="<?php echo site_url().'/loans/loan_print_view/'.$loan['id'];?>" >
                                                        <i class="fa fa-print"></i>
                                                    </a>
												
                                                    <?php

                                                    if ($loan['cash_given'] > 0):?>
                                                        <a class="btn green" href="<?php echo site_url().'/loans/loans_detail_view/'.$loan['id'];?>" >
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                    <?php

                                                    endif;
                                                    ?>
                                                </td>

                                                <td>

                                                    <strong>Name : </strong><?php echo $loan['first_name'].' '.$loan['last_name'];?> <br>
                                                    <strong>CNIC : </strong><?php echo $loan['cnic']?> <br>
                                                    <strong>Contact Details : </strong><?php echo $loan['mobile'];?> <br>
                                                    <strong>Emergency Contact : </strong><?php echo $loan['emergency_no'];?><br />


                                                </td>

                                                <td><?php  echo $loan ['type']  ?></td>
                                                <td><?php  echo $loan ['months']  ?></td>
                                                <td>

                                                    <?php

                                                    if ($loan['undertaken_img']!=''):

                                                        ?>
                                                        <a class="btn green" href="<?php echo base_url();?>uploads/<?php echo $loan['undertaken_img']?>" target="_blank">
                                                            <i class="fa fa-image"></i>  Show Image
                                                        </a>

                                                    <?php
                                                    endif;
                                                    ?>

                                                </td>
                                                <td><?php  echo $loan ['amount_applied']  ?></td>
                                                <td><?php  echo $loan ['reason']  ?></td>
                                                <td> <?php
                                                    if ($loan['status'] == '0')
                                                        echo  "<a data-toggle='modal' data-id='$i' class='open-AddBookDialog btn btn-warning' style='width: 100px' href='#updateleavemodal' >PENDING</a>";
                                                    else{
                                                        if ($loan['status'] == '2')
                                                        {
                                                            echo "<a data-toggle='modal' class='btn btn-danger'  style='width: 100px'  >REJECTED</a>";
                                                        }
                                                        else
                                                        {
                                                            if ($loan['remaining'] == "0")
                                                                echo " <a data-toggle='modal' class='btn btn-success' style='width: 100px' >Cleared</a>";
                                                            else
                                                                echo " <a data-toggle='modal' class='btn btn-primary' style='width: 100px' >Running</a>";

                                                        }
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php  echo $loan ['months_approved']-$loan ['remaining']  ?></td>
                                                <td><?php  echo $loan ['remaining']  ?></td>
                                                <td><?php  echo $loan ['amount_approved']  ?></td>
                                                <td>
                                                    <?php  $use = $this->db->get_where("users","user_id  = '".$loan['updated_by']."'")->row();
                                                    if ($use)
                                                        echo  $use->first_name." ".$use->last_name;
                                                    ?>
                                                </td>
                                                <td><?php  echo $loan ['months_approved']  ?></td>
                                                <td><?php  echo $loan ['created_at']  ?></td>
                                                <td><?php  $use = $this->db->get_where("users","user_id  = '".$loan['created_by']."'")->row();
                                                        if ($use)
                                                            echo  $use->first_name." ".$use->last_name;
                                                ?></td>

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

    <div class="modal fade" id="updateleavemodal" tabindex="-1"   data-width="600" >
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title">Loan Approval</h4>
        </div>
        <div class="modal-body">
            <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/loans/loans_approval">
                <div class="form-body">
                    <div class="form-group">
                        <label class="col-md-6 control-label">Employee</label>
                        <div class="col-md-6">
                            <textarea readonly class="form-control" rows="2" id="empinfo" name="empinfo" ></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-6 control-label">Loan Type</label>
                        <div class="col-md-6">
                            <textarea readonly class="form-control" rows="1" id="loan_type" name="loan_type" ></textarea>
                        </div>
                    </div>
                    <div class="form-group" id="for_months_div">
                        <label class="col-md-6 control-label">FOR NO OF MONTHS <span class="required">*</span></label>
                        <div class="col-md-6">
                            <select class="form-control" name="in_month" id="in_month" required>
                                <option value="1">1 Months</option>
                                <option value="2">2 Months</option>
                                <option value="3">3 Months</option>
                                <option value="4">4 Months</option>
                                <option value="5">5 Months</option>
                                <option value="6">6 Months</option>
                                <option value="7">7 Months</option>
                                <option value="8">8 Months</option>
                                <option value="9">9 Months</option>
                                <option value="10">10 Months</option>
                                <option value="11">11 Months</option>
                                <option value="12">12 Months</option>
                                <option value="13">13 Months</option>
                                <option value="14">14 Months</option>
                                <option value="15">15 Months</option>
                                <option value="16">16 Months</option>
                                <option value="17">17 Months</option>
                                <option value="18">18 Months</option>
                                <option value="19">19 Months</option>
                                <option value="20">20 Months</option>
                                <option value="21">21 Months</option>
                                <option value="22">22 Months</option>
                                <option value="23">23 Months</option>
                                <option value="24">24 Months</option>
                            </select>
                            <!--<span class="help-inline"></span>-->
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-6 control-label">AMOUNT</label>
                        <div class="col-md-6">
                            <input type="number"  name="amount" id="amount" class="form-control mobile" value="" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-6 control-label">REASON <span class="required">*</span></label>
                        <div class="col-md-6">
                            <textarea readonly class="form-control remarks" rows="3" name="reason" id="reason" required></textarea>
                        </div>
                    </div>
                    <div class="form-group" >
                        <label class="col-md-6 control-label">Accept or Reject this leave</label>
                        <div class="col-md-6 radio-list" name="radiolist" id="radiolist">
                            <label class="radio-inline">
                                <input type="radio" class="status" name="status"  value="1" >Accept</label>
                            <label class="radio-inline">
                                <input type="radio" class="status" name="status"  value="2">Reject</label>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-offset-3 col-md-9">
                            <input type="hidden" id="loan_id" name="loan_id" value="" />
                            <button type="submit" class="btn red">Submit</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn default close_button" data-dismiss="modal">Close</button>
        </div>
    </div>
    <!-- /.modal-dialog -->
<script>
    document.addEventListener( "DOMContentLoaded", function(){

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
            "order": [
                [0, 'asc']
            ],
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

    }, false );
</script>