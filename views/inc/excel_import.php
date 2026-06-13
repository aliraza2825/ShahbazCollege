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
                            <i class="fa fa-list"></i>Import Attendance
                        </div>
                    </div>

                </div>
                <div class="container">
                    <br />
                    <form method="post" id="import_form" enctype="multipart/form-data">
                        <p><label>Select Excel File</label>
                            <input type="file" name="file" id="file" required accept=".xls, .xlsx" /></p>
                        <br />
                        <input type="submit" name="import" value="Import" class="btn green" />
                    </form>
                    <br />
                    <div class="table-responsive" id="customer_data">

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
$(document).ready(function(){

	

	$('#import_form').on('submit', function(event){
		
		alert('TEST');
		
		alert('<?php echo base_url(); ?>excel_import/import');
		
		$.ajax({
			url:"",
			method:"POST",
			data:new FormData(this),
			contentType:false,
			cache:false,
			processData:false,
			success:function(data){
				$('#file').val('');
				$('#customer_data').html(data);
				
			}
		})
		
		event.preventDefault();
	});

});
</script>
