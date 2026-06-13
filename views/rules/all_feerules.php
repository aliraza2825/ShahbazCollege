
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
        background-color: #ccc;
    }

    /* Style the tab content */
    .tabcontent {
        display: none;
        padding: 6px 12px;
        border: 1px solid #ccc;
        border-top: none;
    }

</style>

	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
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
                <div class="tab">
                    <button class="tablinks" onclick="openCity(event, 'Active')">Active</button>
                    <button class="tablinks" onclick="openCity(event, 'Inactive')">Inactive</button>
                    <div id="Active" class="tabcontent">
                        <div class="col-md-12 ">
                            <!-- BEGIN SAMPLE FORM PORTLET-->
                            <div class="portlet box green ">

                                <div class="portlet-title">
                                    <div class="caption">
                                        <i class="fa fa-user"></i> Fee Rules
                                    </div>
                                </div>
                                <div class="portlet-body table-responsive">
                                    <table class="table table-bordered table-hover" id="sample_3">
                                        <thead>
                                        <tr>
                                            <th >
                                                ID
                                            </th>
                                            <th >
                                                Course
                                            </th>
                                            <th >
                                                Total Fee
                                            </th>
                                            <th>
                                                Admission Fee
                                            </th>
                                            <th>
                                                Fee Per Installment
                                            </th>
                                            <th>
                                                Installment After Months
                                            </th>

                                            <th>
                                                Last Day of Each Installment
                                            </th>
                                            <th>
                                                Per day Fine
                                            </th>
                                            <th>
                                                Plan Last Date
                                            </th>
                                            <th>
                                                Council Last Date
                                            </th>
                                            <th>
                                                No of installments
                                            </th>

                                            <th>
                                                Discount on merge
                                            </th>

                                            <th>
                                                Session
                                            </th>
                                            <th>
                                                Maximum Comission
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
                                        $i=0;

                                        foreach($plans as $plan):
                                            if ($plan['status'] == 'active'):
                                                ?>
                                                <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
                                                <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/students/add_payment_plan/<?php echo $this->uri->segment(3)?>" enctype="multipart/form-data">
                                                    <tr class="odd gradeX">
                                                        <td >
                                                            <?php echo $plan['fee_rule_id'];?>
                                                        </td>
                                                        <td >
                                                            <?php echo $plan['course_name'];?>
                                                        </td>
                                                        <td >
                                                            <?php echo $plan['total_fee'];?>
                                                        </td>

                                                        <td >
                                                            <?php echo $plan['installment_on_admission'] ?>
                                                        </td>

                                                        <td>
                                                            <?php echo $plan['per_installment_fee'] ?>
                                                        </td>

                                                        <td><?php echo $plan['difference_in_installments_months'] ?></td>

                                                        <td><?php echo $plan['paid_date_each_installment'] ?></td>

                                                        <td><?php echo $plan['late_fee_per_day_fine'] ?> </td>

                                                        <td><?php echo $plan['last_date'] ?></td>

                                                        <td><?php echo $plan['last_date_council_fee'] ?></td>

                                                        <td><?php echo $plan['no_of_installments'] ?></td>

                                                        <td><?php echo $plan['disc_per_inst'] ?></td>

                                                        <td><?php echo $plan['session'] ?></td>

                                                        <td><?php echo $plan['max_comision'] ?></td>

                                                        <td>
                                                            <?php if ($plan['status'] == 'active'): ?>
                                                                <a href="<?php echo site_url().'/rules/fee_rules_status/'.$plan['fee_rule_id']?>/inactive" class="btn btn-danger">Inactive</a>
                                                            <?php else: ?>
                                                                <a href="<?php echo site_url().'/rules/fee_rules_status/'.$plan['fee_rule_id']?>/active" class="btn btn-success">Activate</a>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><a href="<?php echo site_url().'/rules/fee_rules/'.$plan['fee_rule_id']?>" target="_blank" class="btn purple pull-right">Edit Plan</a></td>
                                                    </tr>
                                                </form>
                                            <?php
                                            endif;
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
                    <div id="Inactive" class="tabcontent">
                        <div class="col-md-12 ">
                            <!-- BEGIN SAMPLE FORM PORTLET-->
                            <div class="portlet box green ">

                                <div class="portlet-title">
                                    <div class="caption">
                                        <i class="fa fa-user"></i> Fee Rules
                                    </div>
                                </div>
                                <div class="portlet-body table-responsive">
                                    <table class="table table-bordered table-hover" id="sample_2">
                                        <thead>
                                        <tr>
                                            <th >
                                                ID
                                            </th>
                                            <th >
                                                Course
                                            </th>
                                            <th >
                                                Total Fee
                                            </th>
                                            <th>
                                                Admission Fee
                                            </th>
                                            <th>
                                                Fee Per Installment
                                            </th>
                                            <th>
                                                Installment After Months
                                            </th>

                                            <th>
                                                Last Day of Each Installment
                                            </th>
                                            <th>
                                                Per day Fine
                                            </th>
                                            <th>
                                                Plan Last Date
                                            </th>
                                            <!--<th>-->
                                            <!--    Council Last Date-->
                                            <!--</th>-->
                                            <th>
                                                No of installments
                                            </th>

                                            <th>
                                                Discount on merge
                                            </th>

                                            <th>
                                                Session
                                            </th>
                                            <th>
                                                Maximum Comission
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
                                        $i=0;

                                        foreach($plans as $plan):
                                            if ($plan['status'] != 'active'):
                                                ?>
                                                <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>


                                                <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/students/add_payment_plan/<?php echo $this->uri->segment(3)?>" enctype="multipart/form-data">

                                                    <tr class="odd gradeX">
                                                        <td >
                                                            <?php echo $plan['fee_rule_id'];?>
                                                        </td>
                                                        <td >
                                                            <?php echo $plan['course_name'];?>
                                                        </td>
                                                        <td >
                                                            <?php echo $plan['total_fee'];?>
                                                        </td>

                                                        <td >
                                                            <?php echo $plan['installment_on_admission'] ?>
                                                        </td>

                                                        <td>
                                                            <?php echo $plan['per_installment_fee'] ?>
                                                        </td>

                                                        <td><?php echo $plan['difference_in_installments_months'] ?></td>

                                                        <td><?php echo $plan['paid_date_each_installment'] ?></td>

                                                        <td><?php echo $plan['late_fee_per_day_fine'] ?> </td>

                                                        <td><?php echo $plan['last_date'] ?></td>

                                                        <!--<td><?php echo $plan['last_date_council_fee'] ?></td>-->

                                                        <td><?php echo $plan['no_of_installments'] ?></td>

                                                        <td><?php echo $plan['disc_per_inst'] ?></td>

                                                        <td><?php echo $plan['session'] ?></td>

                                                        <td><?php echo $plan['max_comision'] ?></td>

                                                        <td>
                                                            <?php if ($plan['status'] == 'active'): ?>
                                                                <a href="<?php echo site_url().'/rules/fee_rules_status/'.$plan['fee_rule_id']?>/inactive" class="btn btn-danger">Inactive</a>
                                                            <?php else: ?>
                                                                <a href="<?php echo site_url().'/rules/fee_rules_status/'.$plan['fee_rule_id']?>/active" class="btn btn-success">Activate</a>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><a href="<?php echo site_url().'/rules/fee_rules/'.$plan['fee_rule_id']?>" target="_blank" class="btn purple pull-right">Edit Plan</a></td>
                                                    </tr>
                                                </form>
                                            <?php
                                            endif;
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
                </div>
            </div>
		</div>
	</div>
	<!-- END CONTENT -->
<script>
    $('.radio-group .radio').click(function(){

        $('.radio-group').find('.radio').removeClass('selected');
        $(this).addClass('selected');
        var val = $(this).attr('data-value');
        $('.form-actions').find('.plan_id').val(val);
       // alert(val);

        // $(this).parent().find('input').val(val);

    });

    function openCity(evt, cityName) {
        // Declare all variables
        var i, tabcontent, tablinks;

        // Get all elements with class="tabcontent" and hide them
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }

        // Get all elements with class="tablinks" and remove the class "active"
        tablinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }

        // Show the current tab, and add an "active" class to the button that opened the tab
        document.getElementById(cityName).style.display = "block";
        evt.currentTarget.className += " active";
    }
</script>