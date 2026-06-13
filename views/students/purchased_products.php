
<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <!--<h3 class="page-title">
        All Teachers <small>Here you can find all teachers</small>
        </h3>-->
        <!-- BEGIN DASHBOARD STATS -->
        <!-- END DASHBOARD STATS -->
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
            <div class="col-md-12 ">
                <!-- BEGIN SAMPLE FORM PORTLET-->
                <div class="portlet box green ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i> Product Selling History
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form class="form-horizontal" role="form" method="post" action="#">
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Invoice #</t>
                                                    <th>Sold Date</t>
                                                    <th>Product</th>
                                                    <th>Campus / Room / Subroom</th>
                                                    <th>Sold Price</th>
                                                    <th>Sold By</th>
                                                </tr>
                                            </thead>
                                            <tbody class="sold_products">
                                                <?php
                                                    foreach($sold_products as $sold_product):
                                                ?>
                                                <tr>
                                                    <td>
                                                        <?php echo $sold_product['invoice_no'];?>
                                                    </td>
                                                    <td>
                                                        <?php echo $sold_product['sold_date'];?>
                                                    </td>
                                                    <td>
                                                        <?php echo $sold_product['product_name'];?>
                                                    </td>
                                                    <td>
                                                        <?php echo $sold_product['campus_name'];?>
                                                        <br />
                                                        <?php echo $sold_product['room_name'];?>
                                                        <br />
                                                        <?php echo $sold_product['subroom_name'];?>
                                                    </td>
                                                    <td>
                                                        <?php echo 'Rs '.$sold_product['sold_amount'];?>
                                                    </td>
                                                    <td>
                                                        <?php echo $sold_product['first_name'].' '.$sold_product['last_name'];?>
                                                    </td>
                                                </tr>
                                                <?php
                                                    endforeach;
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- END SAMPLE FORM PORTLET-->
            </div>
        </div>
        <!-- END PAGE CONTENT-->
    </div>
</div>


<script>

    document.addEventListener( "DOMContentLoaded", function(){

        $(".select2").select2();

    }, false );

</script>

<!-- END CONTENT -->
	
	
