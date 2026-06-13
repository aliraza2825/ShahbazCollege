<style>

.radio-group{
        position: relative;
    }

    .radio{
        display:inline-block;
        border-radius: 2px;
        width: 120px;
        border: 2px solid lightblue;
        cursor:pointer;
        margin: 5px 0;
        background: cadetblue;
    }

    .radio.selected{
        border-color: cadetblue;
        background-color: darkgreen;
        color: white;
    }

    /* Style the tab */
    .tab {
        overflow: hidden;
        border: 1px solid #ccc;
        background-color: #f1f1f1;
    }

    /* Style the buttons that are used to open the tab content */
    .tab button {
        background-color: inherit;
        float: left;
        border: none;
        outline: none;
        cursor: pointer;
        padding: 14px 16px;
        transition: 0.3s;
    }

    /* Change background color of buttons on hover */
    .tab button:hover {
        background-color: #ddd;
    }

    /* Create an active/current tablink class */
    .tab button.active {
        background-color: #26a69a;
    }

    /* Style the tab content */
    .tabcontent {
        display: none;
        padding: 6px 12px;
        border: 1px solid #ccc;
        border-top: none;
    }
    #accountsModal .modal-dialog {
        width: 90%;
        max-width: 1200px;
    }

    .report-loader{
        display:none;
        text-align:center;
        padding:40px 20px;
    }

    .report-loader .spinner{
        width:50px;
        height:50px;
        border:5px solid #ddd;
        border-top:5px solid #26a69a;
        border-radius:50%;
        animation:spin 1s linear infinite;
        margin:0 auto 15px;
    }

    @keyframes spin{
        100%{ transform:rotate(360deg); }
    }
</style>
<?php $status = isset($status) ? $status : 'Active' ?>

<div class="page-content-wrapper">
    <div class="page-content">

        <?php if(@$this->session->userdata('message')):?>
            <div class="alert alert-success">
                <button class="close" data-close="alert"></button>
                <span><?php echo $this->session->userdata('message');?></span>
            </div>
        <?php endif;?>

        <div class="row">
            <div class="col-md-12 ">
                <div class="portlet box green ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i> Report
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/councils/report">
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Select Courses <span class="required">*</span></label>
                                    <div class="col-md-6">
                                        <select class="form-control input-inline input-large" name="course_id" required>
                                            <?php foreach($courses as $course): ?>
                                                <option value="<?php echo $course['course_id']; ?>">
                                                    <?php echo $course['course_name']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <button type="submit" class="btn green">Check</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row tab">
            <button id='active_tab' class="tablinks <?php if($status == 'Active') echo 'active'; ?>" style="margin-left: 10px;" onclick="loadReport('Active','active_tab');">Active</button>
            <button id='inactive_tab' class="tablinks <?php if($status == 'InActive') echo 'active'; ?>" onclick="loadReport('InActive','inactive_tab');">Inactive</button>

            <div class="col-md-12">
                <div class="portlet box grey-cascade">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i> Report Result
                        </div>
                        <button style="float:right; background-color: #26a69a; font-size: medium;" type="button" class="btn btn-primary btn-sm" id="total_liablity">
                            Total Liablity : 0.00
                        </button>
                    </div>

                    <div class="portlet-body">
                        <div class="report-loader" id="reportLoader">
                            <div class="spinner"></div>
                            <div>Loading report, please wait...</div>
                        </div>

                        <div id="reportResult"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="accountsModal" tabindex="-1" role="dialog" aria-labelledby="accountsModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="accountsModalLabel">Accounts</h4>
            </div>

            <div class="modal-body" id="accountsModalBody">
                Loading...
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

<script>

function loadReport(status,tab) {
        
        if(status == 'Active'){
            $('#active_tab').addClass('active');
            $('#inactive_tab').removeClass('active');
            $('#total_liablity').show();
        }else{
            $('#active_tab').removeClass('active');
            $('#inactive_tab').addClass('active');
            $('#total_liablity').hide();
        }
        $('#reportLoader').show();
        $('#reportResult').html('');

        $.ajax({
            url: "<?php echo site_url('councils/report_ajax'); ?>"+"/"+status,
            type: "GET",
            success: function(response) {
                $('#reportLoader').hide();
                $('#reportResult').html(response);

                if ($.fn.DataTable.isDataTable('#sample_2')) {
                    $('#sample_2').DataTable().destroy();
                }

                $('#sample_2').DataTable({
                    pageLength: -1
                });
            },
            error: function() {
                $('#reportLoader').hide();
                $('#reportResult').html('<div class="alert alert-danger">Report load nahi ho saki.</div>');
            }
        });
    }
    
document.addEventListener("DOMContentLoaded", function () {
    $(document).ready(function () {
        
        loadReport('Active');
    
        $(document).on('click', '.view-accounts-btn', function () {
            var title = $(this).attr('data-title');
            var accounts = $(this).attr('data-accounts');
    
            try {
                accounts = JSON.parse(accounts);
            } catch (e) {
                accounts = [];
            }
    
            $('#accountsModalLabel').text(title);
    
            var totalFee = 0;
            var totalPaid = 0;
            var totalLiability = 0;
            var totalProfit = 0;
    
            var html = `
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Fee Received</th>
                                <th>Paid</th>
                                <th>Liability</th>
                                <th>Profit</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
    
            if (accounts.length > 0) {
                accounts.forEach(function(row) {
                    var fee = parseFloat(row.fee_amount || 0);
                    var paid = parseFloat(row.expense_amount || 0);
                    var liability = parseFloat(row.liability || 0);
                    var profit = parseFloat(row.profit_amount || 0);
    
                    totalFee += fee;
                    totalPaid += paid;
                    totalLiability += liability;
                    totalProfit += profit;
    
                    html += `
                        <tr>
                            <td>${row.task_name} - ${row.type_name}</td>
                            <td>${fee.toFixed(2)}</td>
                            <td>${paid.toFixed(2)}</td>
                            <td>${liability.toFixed(2)}</td>
                            <td>${profit.toFixed(2)}</td>
                        </tr>
                    `;
                });
            } else {
                html += `
                    <tr>
                        <td colspan="5" class="text-center">No data found</td>
                    </tr>
                `;
            }
    
            html += `
                        </tbody>
                        <tfoot>
                            <tr style="font-weight:bold; background:#f5f5f5;">
                                <td>Total</td>
                                <td>${totalFee.toFixed(2)}</td>
                                <td>${totalPaid.toFixed(2)}</td>
                                <td>${totalLiability.toFixed(2)}</td>
                                <td>${totalProfit.toFixed(2)}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            `;
    
            $('#accountsModalBody').html(html);
        });
    });
});

</script>