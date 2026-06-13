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
                            <i class="fa fa-list"></i>All Expenses
                        </div>
                    </div>

                    <?php
                    if(count($expenses)>0 ):
                        ?>
                        <!--<a class="btn btn-primary" href="<?php echo site_url();?>/reports/print_expense_report/<?php echo $selected_date;?>/<?php echo $this->uri->segment(4);?>/<?php echo $this->uri->segment(5);?>"><i class="dripicons-print"></i> Print</a>-->
                        <button id="print-btn" type="button" class="btn btn-primary btn-sm d-print-none"><i class="fa fa-print"></i> Print</button>
                        <button id="extra-hide-show" type="button" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i> Hide/Show Extra Columns</button>
                        <div class="portlet-body" id="print_div">
                            <div class="alert alert-success">
                                <p>Total expense for <?php echo $selected_date ?> is PKR <?php echo $total_expense;?></p>
                            </div>
                            <table class="table table-striped table-bordered table-hover" id="sample_3">
                                <thead>
                                <tr>
                                    <th class="hidden">
                                        hidden
                                    </th>
                                    <th>
                                        Campus
                                    </th>
                                    <th class="print-hide">
                                        History
                                    </th>
                                    <th>
                                        Category
                                    </th>
                                    <th>
                                        Title
                                    </th>
                                    <th>
                                        Purpose
                                    </th>
                                    <th>
                                        Expense Type
                                    </th>
                                    <th>
                                        Amount
                                    </th>
                                    <th>
                                        Expense Date
                                    </th>
                                    <th>
                                        Posted Date
                                    </th>
                                    <th class="print-hide">
                                        Receipt
                                    </th>
                                    <th>
                                        Add By
                                    </th>
                                    <th>
                                        First Approval
                                    </th>
                                    <th>
                                        Second Approval
                                    </th>
                                    <th class="print-hide">
                                        Status
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $i = 0;
                                $rickshaw_numbers = array();
                                foreach($expenses as $expense)
                                {
                                    //CHECK RICKSHAW NUMBER ENTRY
                                    if($expense['rickshaw_number']!='')
                                    {
                                        array_push($rickshaw_numbers,$expense['rickshaw_number']);
                                    }

                                    $duplicates = array_count_values($rickshaw_numbers);
                                }

                                foreach($expenses as $expense):
                                ?>
                                    <tr class="odd gradeX <?php if($expense['rickshaw_number']!=''){if($duplicates[$expense['rickshaw_number']]!=1){echo 'alert-danger font-weight-bold';}}?>">
                                        <td class="hidden">
                                            <?php echo $i;?>
                                        </td>
                                        <td>
                                            <?php echo $expense['campus_name']?>
                                        </td>
                                        <td class="print-hide">
                                            <a data-toggle='modal' data-id='<?php echo $i ?>' class='open-exphistDialog btn btn-primary' style='width: 100px' href='#historyexpense' >History
                                            </a>
                                        </td>
                                        <td>
                                            <?php
                                            if($expense['expense_category_id']==9){
                                                @print_expenses_categories($expense['expense_category_id'], 0);
                                                echo '<br />';
                                                $user = $this->db->get_where('users', array('user_id'=>$expense['user_id']))->result_array();
                                                echo @$user[0]['first_name'].' '.@$user[0]['last_name'];
                                            }elseif($expense['expense_category_id']==36){
                                                $payrolls = $this->db->join('users','users.user_id = payroll.user_id')->get_where('payroll', array('expense_id'=>$expense['expense_id']))->result_array();
                                                foreach($payrolls as $payroll){
                                                    echo $payroll['first_name'].' '.$payroll['last_name'].' ( '.$payroll['earned_salary'].' )'.'<br>';
                                                }
                                            }
                                            else{
                                                @print_expenses_categories($expense['expense_category_id'], 0);
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php echo $expense['title']?>
                                            <?php
                                            if($expense['expense_category_id']==1):
                                                ?>
                                                <br />
                                                Rickshaw Number : <?php echo $expense['rickshaw_number'];?>
                                                <br />
                                                Rickshaw Driver No : <?php echo $expense['driver_phone'];?>
                                            <?php
                                            endif;
                                            ?>
                                            <?php
                                            if($expense['expense_category_id']==13 && $expense['student_id']!=NULL):
                                                $student_data = $this->db->get_where('students',array('student_id'=>$expense['student_id']))->result_array();
                                                ?>
                                                Name : <?php echo $student_data[0]['first_name'];?> <?php echo $student_data[0]['last_name'];?> (<?php echo $student_data[0]['cnic'];?>)
                                                <br />
                                                Class : <?php echo $expense['class'];?> Year
                                                <br />
                                                Exam Number : <?php echo $expense['council_exam_no'];?>
                                            <?php
                                            endif;
                                            ?>
                                        </td>
                                        <td>
                                            <?php echo $expense['purpose']?>
                                            <?php 
                                                if($expense['rickshaw_number']!='')
                                                {
                                                    echo 'Rickshaw # : '.$expense['rickshaw_number'];
                                                }
                                            ?>
                                            <?php
                                                if($expense['expense_category_id']==30 ||$expense['expense_category_id']==31)
                                                {
                                                    $loan_user = $this->db->get_where('users',array('user_id'=>$expense['user_id']))->result_array();
                                                    echo '<br />'.$loan_user[0]['first_name'].' '.$loan_user[0]['last_name'];
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                                if($expense['paid_type']=='cash')
                                                {
                                                    echo $expense['paid_type'];
                                                }
                                                else
                                                {
                                                    //echo $expense['paid_type'].'<br />';
                                                    //GET DATA FROM RECONCILATION
                                                    /*
													$this->db->select('*');
                                                    $this->db->from('bank_reconciliation_statement');
                                                    $this->db->join('accounts','accounts.id=bank_reconciliation_statement.account_id','inner');
                                                    $this->db->where('bank_reconciliation_statement.expense_id',$expense['payment_type']);
                                                    $bank_statement = $this->db->get()->result_array();

                                                    if(count($bank_statement)>0)
                                                    {
                                                        echo '<strong>Bank Name: </strong>'.$bank_statement[0]['account_name'].'<br />';
                                                        echo '<strong>Description: </strong>'.$bank_statement[0]['description'].'<br />';
                                                    }
                                                    else
                                                    {
                                                        echo 'Expense Not Tagged in statement';
                                                    }*/
													$ban = $this->db->join("accounts",'accounts.id = bank_reconciliation_statement.account_id')->get_where('bank_reconciliation_statement',"bank_reconciliation_statement.expense_id = '".$expense['expense_id']."' or bank_reconciliation_statement.salary_expense_ids = '".$expense['expense_id']."'")->row();
													echo @$ban->account_name.'<br />'.@$ban->description;
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <?php echo $expense['amount']?>
                                        </td>
                                        <td>
                                            <?php
                                                if($expense['paid_type']=='cash')
                                                {
                                                    echo $expense['expense_date'];
                                                }
                                                else
                                                {
                                                    echo @$ban->trans_date;
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <?php echo $expense['date']?>
                                        </td>
                                        <td class="print-hide">
                                            <?php
                                            if($expense['image']!='' && $expense['online_image']==''):
                                                ?>
                                                <a href="<?php echo base_url().'uploads/'.$expense['image'];?>" target="_blank">
                                                    <button type="button" class="btn btn-default"><i class="fa fa-image"></i> Image</button>
                                                </a>
                                            <?php
                                            endif;
                                            ?>
                                            <?php
                                            if($expense['image']!='' && $expense['online_image']!=''): ?>
                                                <a href="<?php echo str_replace($bucket_address,$cloudfront_address,$expense['online_image']);?>" target="_blank">
                                                    <button type="button" class="btn btn-default"><i class="fa fa-image"></i> Image</button>
                                                </a>
                                            <?php
                                            endif;
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                                $user = $this->db->get_where("users","user_id = '".$expense['add_by_id']."'")->row();
                                                echo @$user->first_name.' '.@$user->last_name;
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $user = $this->db->get_where("users","user_id = '".$expense['approval_first_by']."'")->row();
                                            echo @$user->first_name.' '.@$user->last_name.'<br />'.@$expense['approval_first_comment'];
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $user = $this->db->get_where("users","user_id = '".$expense['approval_second_by']."'")->row();
                                            echo @$user->first_name.' '.@$user->last_name.'<br />'.@$expense['approval_second_comment'];
                                            ?>
                                        </td>
                                        <td class="print-hide"> 
                                            <?php
                                            if ($expense['approved_status'] == '0')
                                                echo  "<a data-toggle='modal' data-id='$i' class='btn btn-primary' style='width: 100px'>PENDING</a>";

                                            elseif ($expense['approved_status'] == '2')
                                                echo  "<a data-toggle='modal' class='btn red'  style='width: 100px'  >REJECTED</a>";

                                            elseif ($expense['approved_status'] == '1') {
                                                echo " <a data-toggle='modal' class='btn green' style='width: 100px' >APPROVED</a>  ";

                                                    if ($expense['rev_status'] === NULL || $expense['rev_status'] === '')
                                                        echo "<br /> <br />  <a data-toggle='modal' data-id='$i' class='open-expreversal btn btn-primary' style='width: 150px' href='#expensereversal' > Want Reverse?</a>";
                                                    elseif( $expense['rev_status'] === '0')
                                                        echo "<br /> <br />  <a data-toggle='modal' data-id='$i' class='open-expreversalapproval btn btn-warning' style='width: 150px' href='#expensereversalapproval' >Reversal Requested</a>";
                                                    elseif( $expense['rev_status'] === '2')
                                                        echo "<br /> <br />  <a data-toggle='modal' data-id='$i' class='open-expreversalapproval btn btn-danger' style='width: 150px' href='#expensereversalapproval' >Reversal Rejected</a>";
                                            }
                                            else
                                                echo " <a data-toggle='modal' class='btn yellow' style='width: 100px' >Reversed </a>";
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                    $i++;
                                endforeach;
                                ?>
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
        <!-- END PAGE CONTENT-->
    </div>
</div>
<!-- END CONTENT -->
<div class="modal fade" id="historyexpense" tabindex="-1"   data-width="1200" >

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Expense History</h4>
    </div>
    <div class="modal-body">
            <div class="form-body">
                <div class="portlet-body">
                    <table class="table table-striped table-bordered table-hover" id="histtable">
                        <thead>
                        <tr>
                            <th class="hidden">
                                hidden
                            </th>
                            <th>
                                Campus
                            </th>
                            <th>
                                Category
                            </th>
                            <th>
                                Title
                            </th>
                            <th>
                                Purpose
                            </th>
                            <th>
                                Amount
                            </th>
                            <th>
                                Date
                            </th>
                            <th>
                                Receipt
                            </th>
                            <th>
                                Add By
                            </th>

                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
    </div>
</div>
<script>
    document.addEventListener( "DOMContentLoaded", function(){
        $("#print-btn").on("click", function(){
            var divToPrint=document.getElementById('print_div');
            var newWin=window.open('','Print-Window');
            newWin.document.open();
            newWin.document.write('<link rel="stylesheet" href="<?php echo base_url();?>assets/global/plugins/bootstrap/css/bootstrap.min.css" type="text/css"><style type="text/css">@media print {}</style><body onload="window.print()">'+divToPrint.innerHTML+'</body>');
            newWin.document.close();
        });
        $(document).on("click", ".open-exphistDialog", function () {


            var myBookId = $(this).data('id');

            var loans = <?php echo json_encode($expenses) ?>;
            $(".modal-body #expense_id").val( loans[myBookId].expense_id );

            var campus_id = loans[myBookId].campus_id;
            var expense_id = loans[myBookId].expense_category_id;

            jQuery.ajax({
                type: "post",
                async: false,
                url: '<?php echo site_url()?>/expenses/singleexpensedetails/'+campus_id+'/'+expense_id,
                data: {

                },
                success: function(data) {

                    $(".modal-body #histtable tbody").html(data);
                    if(loans[myBookId].approved_status != '0')
                    {
                        $(".modal-body #apvdiv").hide();
                    }


                }

            });


        });
    }, false );
</script>