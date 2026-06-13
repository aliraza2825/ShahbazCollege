
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
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet box grey-cascade">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i> Free Products
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/inventory/free_products_store">
                            <div class="form-body">
                                <div class="row">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="casmpus_id" class="form-control input-inline input-large select2 purchaser_campus_id" required>
                                                <option value="">SELECT CAMPUS</option>
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
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Course <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="course_id" class="form-control input-inline input-large select2 purchaser_course_id" required>
                                                <option value="">SELECT COURSE</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Class <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="class_id" class="form-control input-inline input-large select2 purchaser_class_id" required>
                                                <option value="">SELECT CLASS</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <hr />
                                <h3 class="text-center">SELECT PRODUCTS</h3>
                                <hr />
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody class="items">
                                        <tr class="item">
                                            <td>
                                                <select name="product_name_id[]" data-row-id="1" class="form-control input-inline input-large select2 product_name_id product_name_id_1" required>
                                                    <option value="">SELECT PRODUCT</option>
                                                    <?php
                                                        foreach($product_names as $product_name):
                                                    ?>
                                                    <option value="<?php echo $product_name['product_name_id'];?>"><?php echo $product_name['product_name'];?></option>
                                                    <?php
                                                        endforeach;
                                                    ?>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" data-row-id="1" class="form-control input-inline product_quantity input-large product_quantity_1" name="quantity[]" placeholder="Enter Quantity" value="" min="1" required>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <button type="button" class="btn green add_more_item"><i class="fa fa-plus"></i> Add Item</button>
                                <button type="button" class="btn red remove_item"><i class="fa fa-trash"></i> Remove Item</button>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <button type="submit" class="btn green">Create Invoice</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- END EXAMPLE TABLE PORTLET-->
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
	
	
