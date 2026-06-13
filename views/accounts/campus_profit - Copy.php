<?php
$myAccess = checkUserAccess();
?>
<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row">
            <div class="col-md-12 ">
                <!-- BEGIN SAMPLE FORM PORTLET-->

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

                <div class="portlet box green ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-calendar"></i> Select Date
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form class="form-horizontal" role="form" method="post" action="<?php echo current_url();?>">
                            <div class="form-body">
                                <div class="row">
                                    <div class="form-group">
                                        <label class="control-label col-md-3">Till Date</label>
                                        <div class="col-md-3">
                                            <div class="input-group input-medium date date-picker" data-date="<?php echo $till_date;?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                <input type="text" name="till_date" class="form-control" value="<?php echo $till_date;?>" readonly>
                                                <span class="input-group-btn">
                                                    <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                    </span>
                                            </div>
                                        </div>
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
                <!-- END SAMPLE FORM PORTLET-->
            </div>
        </div>
        <h3 class="page-title">
            Campus Code <?php echo $campuses[0]['campus_code'];?> <small><?php echo $campuses[0]['campus_name'];?></small>
            From : <?php
            if(getFromDateProfitDistribution($this->uri->segment(3))=='')
            {
                $from_date = '2000-01-01';
                echo 'Start';
            }
            else
            {
                $from_date = getFromDateProfitDistribution($this->uri->segment(3));
                echo getFromDateProfitDistribution($this->uri->segment(3));
            }
            ?>
            -
            Till : <?php echo $till_date;?>
        </h3>
        <?php
        foreach($campuses as $campus):
            ?>
            <!-- END PAGE HEADER-->
            <!-- BEGIN DASHBOARD STATS -->

            <div class="row">
                <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12">
                    <div class="dashboard-stat red">
                        <div class="visual">
                            <i class="fa fa-money"></i>
                        </div>
                        <div class="details">
                            <div class="more">
                                <?php
                                $psart = 0;
                                $seats = 0;
                                $my_seats = 0;
                                $partner = $this->db->get_where("campus_partners","campus_id = '".$campus['campus_id']."'")->row();
                                @$port_campuses = json_decode($partner->campus_share_ids);
                                @$port_seats = json_decode($partner->no_of_seats);
                                if ($port_campuses):
                                    foreach (@$port_campuses as $i=>$port_campus):

                                        if ($port_campus == $campus['campus_id']){
                                            $my_seats = $port_seats[$i];
                                        }

                                        $seats += $port_seats[$i];

                                        $this_exp = totalExpense($port_campus, $from_date, date('Y-m-d'));
                                        echo @$this->db->get_where("campuses","campus_id = '$port_campus'")->row()->roll_no_code.' : '.$this_exp.'<br />';
                                        $psart += $this_exp ;
                                    endforeach;
                                endif;
                                if ($my_seats > 0)
                                    $totalExpense = (($psart / $seats) * $my_seats);
                                $totalExpense = number_format((float)$totalExpense, 2, '.', '');
                                echo 'Total Expense : Rs '.$psart;

                                ?>
                            </div>
                        </div>
                        <a class="more" href="#">
                            Total Expense <i class="m-icon-swapright m-icon-white"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12">
                    <div class="dashboard-stat red">
                        <div class="visual">
                            <i class="fa fa-money"></i>
                        </div>
                        <div class="details">
                            <div class="number">
                                <?php
                                   echo $totalExpense;
                                ?>
                            </div>
                            <div class="desc">
                                Expenses
                            </div>
                        </div>
                        <a class="more" href="<?php echo site_url();?>/accounts/expenses/<?php echo $from_date;?>/<?php echo $till_date;?>/<?php echo $this->uri->segment(3);?>">
                            View Details <i class="m-icon-swapright m-icon-white"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12">
                    <div class="dashboard-stat green-haze">
                        <div class="visual">
                            <i class="fa fa-credit-card"></i>
                        </div>
                        <div class="details">
                            <div class="number">
                                <?php
                                $exp = $this->db->select("sum(expense_amount) as amount")->get_where('student_shift_details', array('from_class'=>$this->uri->segment(3),'status'=>"0"))->row()->amount;
                                echo $exp;
                                ?>
                            </div>
                            <div class="desc">
                                Student Shift Deduction
                            </div>
                        </div>
                        <a class="more" href="<?php echo site_url();?>/accounts/shift_fee_recovery/<?php echo $this->uri->segment(3);?>/deduction">
                            View Details <i class="m-icon-swapright m-icon-white"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12">
                    <div class="dashboard-stat green-haze">
                        <div class="visual">
                            <i class="fa fa-credit-card"></i>
                        </div>
                        <div class="details">
                            <div class="number">
                                <?php
                                echo $exp+$totalExpense;
                                $totalExpense = $exp+$totalExpense;
                                ?>
                            </div>
                            <div class="desc">
                                Total Expense
                            </div>
                        </div>
                        <a class="more" href="">
                            View Details <i class="m-icon-swapright m-icon-white"></i>
                        </a>
                    </div>
                </div>

                 <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12">
                    <div class="dashboard-stat green-haze">
                        <div class="visual">
                            <i class="fa fa-credit-card"></i>
                        </div>
                        <div class="details">
                            <div class="number">
                                <?php
                                $earn = $this->db->select("sum(earned_amount) as amount")->get_where('student_shift_details', array('to_class'=>$this->uri->segment(3),'received_status'=>"0"))->row()->amount;
                                echo $earn;
                                ?>
                            </div>
                            <div class="desc">
                                Student Shift Earning
                            </div>
                        </div>
                        <a class="more" href="<?php echo site_url();?>/accounts/shift_fee_recovery/<?php echo $this->uri->segment(3);?>/earning">
                            View Details <i class="m-icon-swapright m-icon-white"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12">
                    <div class="dashboard-stat yellow">
                        <div class="visual">
                            <i class="fa fa-users"></i>
                        </div>
                        <div class="details">
                            <div class="number">
                                <?php
                                echo totalRecovery($campus['campus_id'], $from_date, $till_date)+totalRecoveryContractors($campus['campus_id'], $from_date, $till_date);
                                ?>
                            </div>
                            <div class="desc">
                                Regular Recovery
                            </div>
                        </div>
                        <a class="more" href="<?php echo site_url();?>/accounts/fee_recovery/<?php echo $from_date;?>/<?php echo $till_date;?>/<?php echo $this->uri->segment(3);?>">
                            View Details <i class="m-icon-swapright m-icon-white"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12">
                    <div class="dashboard-stat green-haze">
                        <div class="visual">
                            <i class="fa fa-credit-card"></i>
                        </div>
                        <div class="details">
                            <div class="number">
                                <?php
                                echo totalRecovery($campus['campus_id'], $from_date, $till_date)+totalRecoveryContractors($campus['campus_id'], $from_date, $till_date)+$earn;
                                $totalRecovery = totalRecovery($campus['campus_id'], $from_date, $till_date)+totalRecoveryContractors($campus['campus_id'], $from_date, $till_date)+$earn;
                                ?>
                            </div>
                            <div class="desc">
                                Total Recovery
                            </div>
                        </div>
                        <a class="more" href="#">
                            --- <i class="m-icon-swapright m-icon-white"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12">
                    <div class="dashboard-stat purple-plum">
                        <div class="visual">
                            <i class="fa fa-money"></i>
                        </div>
                        <div class="details">
                            <div class="number">
                                <?php echo $net_profit = $totalRecovery-$totalExpense;?>
                            </div>
                            <div class="desc">
                                Net Profit
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12">
                    <div class="dashboard-stat yellow">
                        <div class="visual">
                            <i class="fa fa-users"></i>
                        </div>
                        <div class="details">
                            <!--<div class="number">
                                Partners
                            </div>-->
                            <div class="desc">
                                <?php
                                echo getPartners($campus['campus_id']);
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="clearfix"></div>
                <?php
                if($campus['campus_id']==7)
                {
                    echo showPartnersProfitIslamabad($campus['campus_id'], $from_date, $till_date);
                }
                else
                {
                    echo showPartnersProfit($campus['campus_id'], $net_profit);
                }
                ?>
            </div>
        <?php
        endforeach;
        ?>

        <?php if ($net_profit > 0): ?>
            <div class="row">
            <div class="col-md-12">
                <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/accounts/insert_campus_profit" enctype="multipart/form-data">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Select Payment Type</label>
                                    <div class="col-md-9 radio-list">
                                        <label class="radio-inline">
                                            <input type="radio" name="section" id="optionsRadios4" value="bank" checked> Bank </label>
<!--                                        <label class="radio-inline">-->
<!--                                            <input type="radio" name="section" id="optionsRadios5" value="cash"> Cash </label>-->
                                    </div>
                                </div>
                                <div class="form-group" id="cashaccounts" style="display: none;">
                                    <label class="col-md-3 control-label">To Account<span class="required">*</span></label>
                                    <div class="col-md-9">

                                        <select class="form-control bank_details" name="to_account" id="funds_account_id">
                                            <option value="">SELECT ACCOUNT</option>
                                            <?php
                                            foreach($accounts as $key=>$account):
                                                ?>
                                                <option value="<?php echo $account['id'];?>"><?php echo $account['account_name'];?></option>
                                            <?php
                                            endforeach;
                                            ?>
                                        </select>

                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-md-6 control-label">Upload Document <span class="required">*</span></label>
                                    <div class="col-md-6">
                                        <input type="file" class="form-control input-inline input-large pull-right" name="record" value="" required>
                                        <span class="help-inline"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">

                                    <?php
                                    $partners = $this->db->get_where('campus_partners', array('campus_id'=>$this->uri->segment(3)))->row()->partners;
                                    $partners = json_decode($partners);
                                    $partner = count($partners)/2;
                                    $a=1;
                                    $i=0;
                                    foreach($partners as $partner)
                                    {
                                        if($i%2==0)
                                        {
                                            echo '<input type="hidden" name="user_id_'.$a.'" value="'.$partner.'" />';
                                        }
                                        else
                                        {
                                            echo '<input type="hidden" name="amount_'.$a.'" value="'.($partner/100)*$net_profit.'" />';
                                            echo '<input type="hidden" name="percentage_'.$a.'" value="'.$partner.'" />';
                                            $a++;
                                        }
                                        $i++;
                                    }
                                    ?>

                                    <input type="hidden" name="campus_id" value="<?php echo $this->uri->segment(3);?>" />
                                    <input type="hidden" name="from_date" value="<?php echo $from_date;?>" />
                                    <input type="hidden" name="to_date" value="<?php echo $till_date;?>" />
                                    <input type="hidden" name="total_expense" value="<?php echo $totalExpense;?>" />
                                    <input type="hidden" name="total_recovery" value="<?php echo $totalRecovery;?>" />
                                    <input type="hidden" name="net_profit" value="<?php echo $net_profit;?>" />
                                    <button type="submit" class="btn green">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php else:
            echo "Can't close due to Remaining Balance";
        endif;
        ?>

        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet box grey-cascade">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i>Campus Code <?php echo $campus['campus_code'];?> <small><?php echo $campus['campus_name'];?></small>
                            <span class="text-right">
                                	(From : <?php
                                if(getFromDateProfitDistribution($campus['campus_id'])=='')
                                {
                                    $from_date = '2000-01-01';
                                    echo 'Start';
                                }
                                else
                                {
                                    $from_date = getFromDateProfitDistribution($campus['campus_id']);
                                    echo getFromDateProfitDistribution($campus['campus_id']);
                                }
                                ?> -
                                    Till : <?php echo date('d-m-Y');?>)
                                </span>
                        </div>
                    </div>
                    <form method="post"  action="<?php echo site_url();?>/accounts/campus_profit/<?php echo $this->uri->segment(3) ?>" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label col-md-3">From Date <span class="required">*</span></label>
                                    <div class="col-md-3">
                                        <div class="input-group input-medium date date-picker" data-date="<?php echo $search_from_date;?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                            <input type="text" name="search_from_date" class="form-control" value="<?php echo $search_from_date;?>" readonly>
                                            <span class="input-group-btn">
                                                <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label col-md-3">To Date <span class="required">*</span></label>
                                    <div class="col-md-3">
                                        <div class="input-group input-medium date date-picker" data-date="<?php echo $search_to_date;?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                            <input type="text" name="search_to_date" class="form-control" value="<?php echo $search_to_date;?>" readonly>
                                            <span class="input-group-btn">
                                                <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="submit" name="import" value="Find" class="btn green" />
                    </form>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                            <tr>
                                <th>
                                    Date
                                </th>
                                <th>
                                    User
                                </th>
                                <th>
                                    Percentage
                                </th>
                                <th>
                                    Total Recovery
                                </th>
                                <th>
                                    Total Expense
                                </th>
                                <th>
                                    Total Profit
                                </th>
                                <th>
                                    Image
                                </th>
                                <th>
                                    Profit Taken
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            foreach($profits as $profit):
                                ?>
                                <tr class="odd gradeX">
                                    <td>
                                        <?php echo 'From : '.$profit['from_date'].' To : '.$profit['to_date'];?>
                                    </td>
                                    <td>
                                        <?php echo $profit['first_name'].' '.$profit['last_name'];?>
                                    </td>
                                    <td>
                                        <?php echo $profit['percentage'];?>
                                    </td>
                                    <td>
                                        <?php echo $profit['amount'];?>
                                        <a class="btn green pull-right" href="<?php echo site_url();?>/accounts/profit_recovery_details/<?php echo $profit['from_date'];?>/<?php echo $profit['to_date'];?>/<?php echo $campus['campus_id'];?>"><i class="fa fa-eye"></i></a>
                                    </td>
                                    <td>
                                        <?php echo $profit['total_expense'];?>
                                        <a class="btn green pull-right" href="<?php echo site_url();?>/accounts/profit_expense_details/<?php echo $profit['from_date'];?>/<?php echo $profit['to_date'];?>/<?php echo $campus['campus_id'];?>"><i class="fa fa-eye"></i></a>
                                    </td>
                                    <td>
                                        <?php echo $profit['net_profit'];?>
                                    </td>
                                    <td>
                                        <a href="<?php echo base_url();?>uploads/<?php echo $profit['record'];?>" class="btn purple" target="_blank"><i class="fa fa-image"></i></a>
                                    </td>
                                    <td>
                                        <?php
                                        if($profit['take_profit']==0)
                                        {
                                            echo '<button class="btn red"><i class="fa fa-remove"></i></button>';
                                        }
                                        else
                                        {
                                            echo '<button class="btn green"><i class="fa fa-check"></i></button>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php
                            endforeach;
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- END EXAMPLE TABLE PORTLET-->
            </div>
        </div>

        <!-- END DASHBOARD STATS -->
        <div class="clearfix">
        </div>

    </div>
</div>
<!-- END CONTENT -->
</div>
<!-- END CONTAINER -->
<script>
    document.addEventListener( "DOMContentLoaded", function(){
        $("input[name='section'").change(function(){
            var type = jQuery(this).val();
            if (type === 'cash')
            {
                $('#funds_account_id').attr('required',true);
                $('#cashaccounts').show();
            }else {
                $('#funds_account_id').removeAttr('required');
                $('#cashaccounts').hide();
            }
        });
    }, false );
</script>