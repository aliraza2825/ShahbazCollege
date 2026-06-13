<?php
	$myAccess = checkUserAccess();
?>		
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
                	<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-money"></i> SMS Devices Data
							</div>
						</div>
						<div class="portlet-body">
							<table  class="table table-bordered table-hover" id="customer-table">
							<thead>
							<tr>
                                <th>ID</th>
								<th>Campus Name</th>
								<th>Device ID</th>
								<th>Battery %</th>
								<th>Last Time SMS</th>
                                <th>SMS SENT</th>
							</tr>
							</thead>
							</table>
						</div>
					</div>
                </div>
            </div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->
<script>
    document.addEventListener( "DOMContentLoaded", function(){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        const interval = setInterval(function() {
            $('#customer-table').DataTable().destroy();
            laodtable();
        }, 5000);
    }, false );
    function laodtable(){
        $('#customer-table').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                url: "<?php echo site_url().'/reports/smsReportTable';?>",
                data: {
                    city: ""
                },
                dataType: "json",
                type: "post",
                /*success:function(data){
                    console.log(data);
                }*/
            },
            "columns": [
                {"data": "id"},
                {"data": "campus_name"},
                {"data": "device_id"},
                {"data": "percentage"},
                {"data": "last_sent"},
                {"data": "sms_count"}
            ],
            'language': {
                /*'searchPlaceholder': "{{trans('file.Type date or purchase reference...')}}",*/
                'lengthMenu': '_MENU_ Record',
                "info": '<small>Showing _START_ - _END_ (_TOTAL_)</small>',
                "search": 'Search',
                'paginate': {
                    'previous': '<i class="dripicons-chevron-left"></i>',
                    'next': '<i class="dripicons-chevron-right"></i>'
                }
            },
            order: [['1', 'desc']],
            'columnDefs': [
                {
                    "orderable": false,
                    'targets': [2]
                }
            ],
            autofill: true,
            select: true,
            responsive: true,
            buttons: true,
            length: 10
        });
    }
</script>