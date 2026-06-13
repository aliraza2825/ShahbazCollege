
<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-6" style="background-color: white;padding: 20px 30px;    border-radius: 20px!important;box-shadow: 10px 10px 5px -8px rgba(0,0,0,0.75);">
                <h3 style="margin:5px 0px 20px 0px;text-align: center;font-weight: bold">Select Campus To Generate Salary</h3>
                <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/salary/salary_report">
                    <div class="form-body">
                        <div class="form-group">
                            <label class="col-md-2 control-label">Campus <span class="required">*</span></label>
                            <div class="col-md-8">
                                <select class="form-control campus" name="campus_id">
                                    <option value="">Select CAMPUS</option>
                                    <?php
                                    foreach($campuses as $campus):
                                        ?>
                                        <option value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name'];?></option>
                                    <?php
                                    endforeach;
                                    ?>
                                </select>
                                <!--<span class="help-inline"></span>-->
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label class="control-label col-md-3">Salary Date</label>
                        <div class="input-group input-medium date date-picker col-md-9" data-date="<?php echo @$to_date;?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                            <input type="text" name="to_date" class="form-control" value="<?php echo @$to_date;?>" readonly>
                            <span class="input-group-btn">
                                                            <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                            </span>
                        </div>
                    </div>

                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-12" style="text-align: center">
                                <button type="submit" class="btn green">Check</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php if(@$salary != ""){

            $advance=0;
            $user_alownce=0;
            $loan=0;
            $gross=0;
            $deductions=0;
            $earnings=0;
            $earnedsalary=0;
            $grosssals=0;
            $specials=0;

            ?>

            <button class="btn green" id="print" onclick="printContent('printtable');" >Print</button>

            <div class="row" style="margin-top: 20px">
                <div class="col-md-12" id="printtable">
                    <!-- BEGIN EXAMPLE TABLE PORTLET-->
                    <div class="portlet box grey-cascade">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-list"></i> Staff Salary List ( <?php echo @$salary[0]['campus_name'];?> )

                            </div>
                        </div>
                        <div class="portlet-body" >
                            <table class="table table-striped table-bordered table-hover" id="">
                                <thead>
                                <tr>
                                    <th>
                                        Staff.No
                                    </th>
                                    <th>
                                        Name
                                    </th>
                                    <th>
                                        Desigination
                                    </th>
									<th>
                                        Department
                                    </th>
									<th>
                                        No of Days
                                    </th>
									<th>
                                        Basic Salary
                                    </th>
									<th>
                                        Allownces
                                    </th>
									<th>
                                        Total Salary
                                    </th>
									<th>
                                        Earnings
                                    </th>
									<th>
                                        Gross Earnings
                                    </th>
								
                                    <th>
                                        Advance Salary
                                    </th>
                                    <th>
                                        Loan
                                    </th>
									<?php if($iscampus == 'false'): ?>
									 <th>
                                        Special
                                    </th>
									<?php endif; ?>

                                    <th>
                                        Earned Salary
                                    </th>
                                    <th>
                                        Action
                                    </th>

                                </tr>
                                </thead>
                                <tbody>

                                <?php
                                $i=1;
                                foreach($salary as $list):
                                    ?>
                                    <tr class="odd gradeX">
                                        <td >
                                            <?php echo $i;?>
                                        </td>
                                        <td>
                                            <?php echo $list['first_name'];?> <?php echo $list['last_name'];?>
                                        </td>
                                        <td>
                                            <?php echo $list['designation'];?>
                                        </td>
										<td>
                                            <?php echo $list['department'];?>
                                        </td>
										<td style="text-align: right;">
                                            <?php echo $list['no_of_days'];?>

                                        </td>
										<td style="text-align: right;">
                                            <?php echo $list['basic_salary'];?>
                                            <?php $gross+=$list['basic_salary']; ?>
                                        </td>
										<td style="text-align: right;">
                                            <?php echo $list['earnings'];?>
                                            <?php $earnings+=$list['earnings']; ?>

                                        </td>
										<td style="text-align: right;">
                                            <?php echo $list['gross_salary'];?>
                                            <?php $grosssals+=$list['gross_salary'];?>

                                        </td>
										<td style="text-align: right;">
                                            <?php echo $list['earnings']-$list['user_alownce'];?>
                                            <?php $user_alownce+=($list['earnings']-$list['user_alownce']) ?>

                                        </td>
										<td style="text-align: right;">
                                            <?php echo ($list['earnings']-$list['user_alownce'])+$list['gross_salary'];?>
                                            
                                        </td>
                                        <td style="text-align: right;">
                                            <?php echo $list['advance'];?>
                                            <?php $advance+=$list['advance'];?>
                                        </td>
                                        <td style="text-align: right;">
                                            <?php echo $list['loan'];?>
                                            <?php $loan+=$list['loan']; ?>
                                        </td>
                                        <?php if($iscampus == 'false'): ?>
											 <td>
												<?php echo $list['special'];?>
												<?php $specials+=$list['special']; ?>
											</td>
										<?php endif; ?>
                                                                             
                                        <td >
										<?php if($iscampus == 'false'){ 
												echo ($list['earned_salary']);
										}else{
											echo $list['earned_salary'];
											
										}?>
											
                                            <?php $earnedsalary+=$list['earned_salary']; ?>

                                        </td>
                                        <td>

                                            <a href="<?php echo site_url().'/salary/salary_view/'.$list['user_id'].'/'.$month.'/'.$year;?>" class="btn green"><i class="fa fa-eye" aria-hidden="true"></i></a>

                                        </td>
                                    </tr>
                                    <?php
                                    $i++;
                                endforeach;
                                ?>
                                <th>

                                </th>

                                <th>

                                </th>
								<th>

                                </th>

                                <th>

                                </th>
								<th>

                                </th>
								<th style = "font-weight:bold; text-align: right;">
                                    <?php echo $gross ?>
                                </th>
								<th style = "font-weight:bold; text-align: right;">
                                    <?php echo $earnings ?>
                                </th>
								<th style = "font-weight:bold; text-align: right;">
                                    <?php echo $grosssals ?>
                                </th>
								<th style = "font-weight:bold; text-align: right;">
                                    <?php echo $user_alownce ?>
                                </th>
								<th style = "font-weight:bold; text-align: right;">
                                    <?php echo $user_alownce+$grosssals ?>
                                </th>
                                <th style = "font-weight:bold; text-align: right;">
                                    <?php echo $advance ?>
                                </th>
                                <th style = "font-weight:bold; text-align: right;">
                                    <?php echo $loan ?>
                                </th>
                                <?php if($iscampus == 'false'): ?>
									 <th>
                                        <?php echo $specials ?>
                                    </th>
									<?php endif; ?>
                                <th style = "font-weight:bold; text-align: right;">
                                    <?php echo $earnedsalary+$specials ?>
                                </th>

                                </tbody>
                            </table>


                        </div>
                    </div>
                    <!-- END EXAMPLE TABLE PORTLET-->
                </div>
            </div>
            <?php
            if (count($disbursed)==0):?>

            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/salary/insert_expense">
                <div class="row">
                    <input type="hidden"  value="<?php echo $month ?>" id="month" name="month" readonly>
                    <input type="hidden"  value="<?php echo $year ?>" id="year" name="year" readonly>
                    <input type="hidden"  value="<?php print_r($campus); ?>" id="campus" name="campus" readonly>

                    Disburse Salary :  <input type="text"  STYLE="text-align: center; margin-left: 20px; font-weight: bolder; font-size: large" value="<?php echo $earnedsalary+$specials ?>" name="receivable_amount" readonly>
                    <br />
                    <br />
                    <br />
                    <button onclick="return confirm('Are you sure you want to Disburse by Cash?')" type="submit" id="cash" class="btn green">Disburse by Cash</button>
                    <button onclick="return confirm('Are you sure you want to Disburse by Bank?')" type="submit" id="bank" class="btn green">Disburse by Bank</button>
                </div>
            </form>

            <?php
            else:
                echo '<button type="Button" id="cash" class="btn btn-primary">Disbursed with '.$disbursed[0]['amount'].'</button>';

            endif; ?>

        <?php
        } ?>
    </div>
</div>
<!-- END CONTENT -->

<script>
    function printContent(el){
        var restorepage = $('body').html();
        var printcontent = $('#' + el).clone();
        $('body').empty().html(printcontent);
        window.print();
        $('body').html(restorepage);
    }
</script>