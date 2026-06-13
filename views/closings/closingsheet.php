
<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row">
            
            <div class="col-md-6" style="background-color: white;padding: 20px 30px;    border-radius: 20px!important;box-shadow: 10px 10px 5px -8px rgba(0,0,0,0.75);">
                <h3 style="margin:5px 0px 20px 0px;font-weight: bold">Select Date To view closing sheet</h3>
                <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/closing/index">
                    <div class="form-body">

						<?php 
								$challan_date = date_create(date('Y-m-d'));
                                $paid_date = date_create($campusclosingverified[0]['year'].'-'.$campusclosingverified[0]['month'].'-'.$campusclosingverified[0]['day']);
								$diff=date_diff($challan_date,$paid_date);
						 ?>
                        <div class="form-group">
                            <label class="control-label col-md-3">Closing Date</label>
                            <div class="input-group input-medium date date-picker col-md-9" data-date="<?php echo @$selected_date;?>" data-date-format="yyyy-mm-dd" <?php if($this->session->userdata('role') != "Admin"): ?> data-date-start-date="-<?php echo $diff->days;?>d" <?php endif; ?> data-date-end-date="0d" data-date-viewmode="years">
                                <input type="text" name="to_date" class="form-control" value="<?php echo @$selected_date;?>" readonly>
                                <span class="input-group-btn">
                                    <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                </span>
                            </div>
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
			<div class="col-md-6" style="background-color: white;padding: 20px 30px;    border-radius: 20px!important;box-shadow: 10px 10px 5px -8px rgba(0,0,0,0.75);">
                <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i> Last Closing/Verification Date
                        </div>
                    </div>
                
                    <table class="table table-striped table-bordered table-hover" id="">
                            <thead>
                            <tr>
							  <th>
                                    College
                                </th>
								
                               <th>
                                    Closing Date
                               </th>
                              
                               <th>
                                    Verification Date
                               </th>
                              
								
                                
                            </tr>
                            </thead>
                            <tbody>

                            <?php
                            $i=1;
                            foreach($campusclosings as $key=>$closes):
                                ?>
                                <tr class="odd gradeX">
                                    
									 <td>
                                        <?php echo $closes['campus_name'];?>
                                    </td>
                                   
                                    <td>
                                        <?php echo $closes['day'] .'-'.$closes['month'].'-'.$closes['year'] ;?>
                                    </td>
									<td>
                                        <?php echo $campusclosingverified[$key]['day'] .'-'.$campusclosingverified[$key]['month'].'-'.$campusclosingverified[$key]['year'] ;?>
                                    </td>
                                   
                                </tr>
                                <?php
                                $i++;
                            endforeach;
                            ?>


                            </tbody>
                    </table>
            </div>
        </div>
        <?php if(@$closings != ""){?>

        <div class="row" style="margin-top: 20px">
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet box grey-cascade">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i> Daily closing sheet
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover" id="">
                            <thead>
                            <tr>
                                <th >
                                    Sr
                                </th>
                                <th>
                                    Date
                                </th>
                                <th>
                                    College
                                </th>

                                <th>
                                    Cash Received
                                </th>

                                <th>
                                    Received Amount
                                </th>

                                <th>
                                    Closing Person
                                </th>
								
								<th>
                                    Closed By
                                </th>
								<th>
                                    Image
                                </th>
								
                                <th>
                                    Status
                                </th>
								<th>
                                    Accounts Status
                                </th>
                                <th>
                                    View
                                </th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php
                            $i=0;
                            foreach($closings as $list):
                                ?>
                                <tr class="odd gradeX">
                                    <td >
                                        <?php echo $i;?>
                                    </td>
                                    <td>
                                        <?php echo $selected_date;?>
                                    </td>
                                    <td>
                                        <?php echo $list['campus_name'];?>
                                    </td>
                                    <td>
                                        <?php echo $list['closing_amount'];?>
                                    </td>

									<th>
                                        <?php echo $list['closing_amount'];?>
									</th>
                                    <td>
                                        <?php echo $list['first_name']. ' '.$list['last_name'];?>
                                    </td>
									<td>
                                        <?php echo $list['closed_by'];?>
                                    </td>
									<td>
                                        <?php
                                        if( $list['partialy_closed_image']!=''):
                                            ?>
                                            <a href="<?php echo base_url().'uploads/'.$list['partialy_closed_image'];?>" target="_blank">
                                                <button type="button" class="btn btn-default"><i class="fa fa-image"></i> Image</button>
                                            </a>
                                        <?php
                                        endif;
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($list['closed_status'] == '0')
                                            echo  "<a data-toggle='modal' data-id='$i' class='btn btn-primary' style='width: 100px'>OPEN</a>";

                                        else {

                                                if ($list['close_type'] == '2' )
                                                    echo " <a data-toggle='modal' class='btn green' style='width: 100px' >Closed</a>";
                                                else {

                                                      if ($list['transaction_no'] == NULL)
                                                          echo '<a data-toggle="modal" data-id="'.$i.'" title="Add this item" class="open-AddClosing btn btn-warning" href="#closingdetails">
                                                        <i class="fa fa-dollar"> Partially Closed</i>
                                                    </a>';
                                                      else{
                                                          echo " <a data-toggle='modal' class='btn green' style='width: 100px' >Closed</a>";
														  if ($list['checked_by'] == 'NULL' || $list['checked_by'] == '' ){
														  echo '<a data-toggle="modal" data-id="'.$i.'" title="Add this item" class="open-EditClosing btn btn-warning" href="#closingdetails">
                                                        <i class="fa fa-dollar"> Edit</i>
                                                    </a>';
														  }
														  
													  }
                                                }
                                        }

                                        ?>
                                    </td>
									<td>
                                        <?php
										
										if($list['closed_status'] != '0'){
                                        if ($list['checked_by'] == 'NULL' || $list['checked_by'] == '' )
                                            echo  "<a  style='width: 100px; color:red;' >UnVerified</a>";

                                        else {

                                              echo  "<a  style='width: 100px; color:green;' >Verified</a>";
                                        }
										}

                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $key = array_search($list['campus_id'], array_column($campusclosings, 'campus_id'));
																				
                                        $reqdate=@$campusclosings[$key]['year'] .'-'.@$campusclosings[$key]['month'].'-'.@$campusclosings[$key]['day'].'';
                                        $stop_date = date('Y-m-d', strtotime($reqdate . ' +1 day'));
                                        if ($selected_date == $stop_date): ?>
                                            <a href="<?php  echo site_url().'/closing/viewclosing/'.$selected_date.'/'.$list['campus_id'].'/'.$list['closing_amount'].'/'.$list['closed_status']?>"> VIEW </a>
                                        <?php else: ?>
                                            <a href="<?php  echo site_url().'/closing/viewclosing/'.$selected_date.'/'.$list['campus_id'].'/'.$list['closing_amount'].'/1'?>"> VIEW </a>

                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php
                                $i++;
                            endforeach;
                            ?>


                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- END EXAMPLE TABLE PORTLET-->
            </div>
        </div>

    <?php } ?>
    </div>
</div>

<div class="modal fade" id="closingdetails" tabindex="-1"   data-width="600" >

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Add Closing Details</h4>
    </div>
    <div class="modal-body">
        <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/closing/add_closing_details">
            <div class="form-body">

                <div class="form-group" id="fraccount">
                    <label class="col-md-3 control-label">Select Bank <span class="required">*</span></label>
                    <div class="col-md-9">
                        <select name="account_id" id="account_id" class="form-control input-inline input-large " required>
                            <option value="">Select Account From</option>
                            <?php
                            foreach($accounts as $account):
                                ?>
                                <option value="<?php echo $account['id'];?>"><?php echo $account['account_title'].' '.$account['account_name'];?></option>
                            <?php
                            endforeach;
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label">Transaction ID</label>
                    <div class="col-md-9">
                        <input type="text"  name="trans_id" id="trans_id" class="form-control mobile" minlength="4" required/>

                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label">Transaction Image<span class="required">*</span></label>
                    <div class="col-md-9">
                        <input type="file"  name="image" id="file" required/>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <div class="row">
                    <div class="col-md-offset-3 col-md-9">

                        <input type="hidden"  id="closingid" name="closingid" value="" class="form-control mobile"/>
                        <button type="submit" class="btn red">Submit</button>

                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn default close_button" data-dismiss="modal">Close</button>
    </div>
</div>

<!-- END CONTENT -->