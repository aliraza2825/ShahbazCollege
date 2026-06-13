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
            if(@$myAccess[0]['loan_approval_accounts']==1 || $this->session->userdata('role')=='Admin'):
                ?>
                <div class="row">

                    <div class="col-md-12 ">
                        <!-- BEGIN SAMPLE FORM PORTLET-->
                        <div class="portlet box green ">

                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-user"></i>Accounts Loans List
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
                                            Amount Applied
                                        </th>
                                        <th>
                                            Status
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
                                            Given Through
                                        </th>

                                        <th>
                                            Cash Status
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
                                                <td><?php  echo $loan ['amount_applied']  ?></td>
                                                <td> <?php
                                                    if ($loan['status'] == '0')
                                                        echo  "<a data-toggle='modal' data-id='$i' class='open-AddBookDialog btn btn-primary' style='width: 100px' href='#updateleavemodal' >PENDING
</a>";

                                                    elseif ($loan['status'] == '2')

                                                        echo  "<a data-toggle='modal' class='btn red'  style='width: 100px'  >REJECTED
</a>";
                                                    else
                                                        echo " <a data-toggle='modal' class='btn green' style='width: 100px' >APPROVED
</a>";
                                                    if ($loan['cash_given'] != '')
                                                        echo  "<a data-toggle='modal' data-id='$i' class='open-AddBookDialog btn btn-success' style='width: 100px' href='#' >Given</a>";

                                                    ?>

                                                </td>
                                                <td>
                                                    <?php  $use = $this->db->get_where("users","user_id  = '".$loan['updated_by']."'")->row();
                                                    if ($use)
                                                        echo  $use->first_name." ".$use->last_name;
                                                    ?>
                                                </td>
                                                <td><?php  echo $loan ['amount_approved']  ?></td>
                                                <td><?php  echo $loan ['months_approved']  ?></td>
                                                <td><?php  echo $loan ['created_at']  ?></td>
                                                <td><?php  echo $loan ['give_through'];

                                                    if ($loan ['give_through'] == 'bank'){
                                                        $data = $this->db->join("accounts","accounts.id = bank_reconciliation_statement.account_id")->get_where("bank_reconciliation_statement","loan_id = '".$loan['id']."'")->row();
                                                        ?>
                                                        <strong> Transferred From Account : </strong><?php echo $data->account_name;?> <br>
                                                        <strong> Description : </strong><?php echo $data->trans_date;?> <br>
                                                        <strong> Date : </strong><?php echo $data->trans_date;?> <br>
                                                        <strong> Amount : </strong><?php echo $data->debit;?> <br>
                                                    <?php
                                                    }

                                                ?></td>
                                                <td> <?php
                                                    if ($loan['cash_given'] == '')
                                                        echo  "<a data-toggle='modal' data-id='$i' class='open-AddBookDialog btn btn-primary' style='width: 100px' href='#updateleavemodal' >PENDING</a>";

                                                    elseif ($loan['cash_given'] != '0')

                                                        echo  "<a data-toggle='modal' class='btn red'  style='width: 100px'  >".$loan['cash_given']." given </a>";
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
            <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/loans/loans_accounts_approval">
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

                    <div class="form-group">
                        <label class="col-md-6 control-label">Approved for months</label>
                        <div class="col-md-6">

                            <textarea readonly class="form-control" rows="1" id="in_month" name="in_month" ></textarea>

                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-6 control-label">Approved AMOUNT</label>
                        <div class="col-md-6">
                            <input readonly type="number"  name="amount" id="amount" class="form-control mobile" value="" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-6 control-label">REASON <span class="required">*</span></label>
                        <div class="col-md-6">

                            <textarea readonly class="form-control remarks" rows="3" name="reason" id="reason" required></textarea>

                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-6 control-label">AMOUNT GIVEN</label>
                        <div class="col-md-6">
                            <input type="number"  name="amount_given" id="amount_given" class="form-control number" value="" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12 control-label">1) This AMOUNT will be Deducted from your Petty Cash.</label><br />
                        <label class="col-md-12 control-label">2) For Bank Disburse Move to Bank Reconciliation</label><br />
                    </div>
                </div>

                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-offset-3 col-md-9">
                            <input type="hidden" id="loan_id" name="loan_id" value="" />
                            <button type="submit" class="btn red hideAfterClick">Submit</button>
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
    function myFunction() {

    }
    document.addEventListener( "DOMContentLoaded", function(){

        $("#amount_given").on("keyup onkeydown change", function(e) {
            let tot=<?php echo my_pettycash() ?>;
            let x = this.value;
            let approved_amount = $("#amount").val();
            if (x>tot){
                alert('Your Petty cash is low you cannot Give this Loan');
                $("#amount_given").val(0);
            }else {
                if (parseInt(x) > parseInt(approved_amount)){
                    $("#amount_given").val(approved_amount);
                }
            }
        })

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