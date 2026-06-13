<?php
	$myAccess = checkUserAccess();
	
	//GET STUDENT DETAILS
    $student_fees = $this_student = $this->db->get_where('students', array('student_id'=>$this->uri->segment(3)))->result_array();
    
    //GET STUDENT SPECIAL DISCOUNT
    $this->db->select('sum(discount) as special_disc');
    $this->db->where('status = "1" and student_id = "'.$this->uri->segment(3).'"');
    $specialdisc=$this->db->get('discounts_approval')->result_array();
    if(count($specialdisc)>0)
    {
        $specialdisc=$specialdisc[0]['special_disc'];
    }
    else
    {
        $specialdisc=0;
    }
    
    //GET COURSE TOTAL FEE
    
    $student_fee_plan = $this->db->get_where('fee_rules',array('fee_rule_id'=>$this_student[0]['plan_id']))->result_array();
    if(count($student_fee_plan)>0)
    {
        $total_fee = $student_fee_plan[0]['total_fee'];
    }
    else
    {
        $total_fee = $this->db->get_where('fee_rules',array('course_id'=>$student_fees[0]['course_id']))->row()->total_fee;
    }
?>	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">


		<div class="page-content">

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

            <!-- Student Data-->

            <?php
            if(@$myAccess[0]['student_payment_edit']==1 || $this->session->userdata('role')=='Admin'):
                ?>
                <div class="row">
                    <div class="col-md-12 ">
                        <!-- BEGIN SAMPLE FORM PORTLET-->
                        <div class="portlet box green ">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-user"></i> Special Discount Details of (<?php echo $student->first_name." ".$student->last_name."   ".$student->roll_no; ?>)
                                </div>
                            </div>

                            <div class="portlet-body table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th>
                                            SR
                                        </th>
										 <th>
                                            Remaining Fee on Discount
                                        </th>
										<th>
											Discount Amount
										</th>

                                        <th>
                                            Reason
                                        </th>
                                        <th>
                                            Discount By
                                        </th>
                                        <th>
                                            Application Image
                                        </th>
                                        <th>
                                            Discount Status
                                        </th>
                                        <th>
                                            Reverse Discount
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $i=0;
                                    foreach($discounts as $discount): ?>
                                        <tr class="odd gradeX">
                                            <td>
                                                <?php echo $i+1;?>
                                            </td>
                                            <td>
                                                <?php echo $discount['remaining_fee']?>
                                            </td>
                                            <td>
                                                <?php echo $discount['discount']?>
                                            </td>
                                            <td>
                                                <?php echo $discount['reason'];?>
                                            </td>
                                            <td>
                                                <?php echo $discount['created_by'];?>

                                            </td>
                                            <td>
                                                <?php if ($discount['application'] != ""):?>
                                                <a  href="<?php echo base_url().'uploads/'.@$discount['application'];?>"><i class="fa fa-eye"></i></a>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                if ($discount['status'] == 0)
                                                    echo '<button class="btn blue" type="button">Pending</button>';
                                                elseif ($discount['status'] == 1)
                                                    echo '<button class="btn green" type="button">Approved</button>';
                                                elseif ($discount['status'] == 2)
                                                    echo '<button class="btn red" type="button">Rejected</button>';
                                                elseif ($discount['status'] == 3)
                                                    echo '<button class="btn btn-warning" type="button">Reversed</button>';
                                                ?>
                                            </td>
                                            <td>
                                                <?php if (($discount['status'] == 1 && @$myAccess[0]['discount_reversal']==1) || ($discount['status'] == 1 && $this->session->userdata('role')=='Admin')): ?>
                                                <a  data-toggle='modal' data-id='<?php echo $i;?>' href='#purchased' title="Reverse Discount" class="btn btn-warning open-purchase_approval"><i class="fa fa-arrow-down"></i>Reverse Discount</a>
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
                        <!-- END SAMPLE FORM PORTLET-->
                    </div>
                </div>
                
                
                <div class="row">
                    <div class="col-md-12 ">
                        <!-- BEGIN SAMPLE FORM PORTLET-->
                        <div class="portlet box green ">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-user"></i> Discount on Admission Details of (<?php echo $student->first_name." ".$student->last_name."   ".$student->roll_no; ?>)
                                </div>
                            </div>

                            <div class="portlet-body table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th>
                                            SR
                                        </th>
										<th>
											Discount Amount
										</th>
                                        <th>
                                            Reverse Discount
                                        </th>
                                    </tr>
                                    </thead>
                                    <?php
                                        if($total_fee!=($this_student[0]['total_fee']+$specialdisc)):
                                    ?>
                                    <tbody>
                                        <tr class="odd gradeX">
                                            <td>
                                                1
                                            </td>
                                            <td>
                                                <?php echo $amount = ($total_fee-$this_student[0]['total_fee'])+$specialdisc;?>
                                            </td>
                                            <td>
                                                <!--<a onclick="return confirm('Are you sure you want to delete this Discount?')" href="<?php echo site_url();?>/students/remove_admission_discount/<?php echo $this->uri->segment(3);?>" title="Reverse Discount" class="btn btn-warning"><i class="fa fa-arrow-down"></i> Reverse Discount</a>-->
                                                <a  data-toggle="modal" data-amount="<?php echo $amount;?>" data-student-id="<?php echo $this->uri->segment(3);?>" href='#admission_discount' title="Reverse Discount" class="btn btn-warning reverse_admission_discount"><i class="fa fa-arrow-down"></i>Reverse Discount</a>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <?php
                                        endif;
                                    ?>
                                </table>
                            </div>
                        </div>
                        <!-- END SAMPLE FORM PORTLET-->
                    </div>
                </div>
                
                <div class="row">
    				<div class="col-md-12">
    					<!-- BEGIN EXAMPLE TABLE PORTLET-->
    					<div class="portlet box grey-cascade">
    						<div class="portlet-title">
    							<div class="caption">
    								<i class="fa fa-list"></i> Removed Discounts
    							</div>
    						</div>
    						<div class="portlet-body">
    							<table class="table table-striped table-bordered table-hover" id="sample_2">
    							<thead>
    							<tr>
    								<th class="hidden">
                                    	 hidden
                                    </th>
                                    <th>
    									 ID
    								</th>
                                    <th>
    									 Amount
    								</th>
    								<th>
    									 Type
    								</th>
    								<th>
    									 Reason
    								</th>
    								<th>
    									 Removed By
    								</th>
                                    <th>
    									 Date / Time
    								</th>
    							</tr>
    							</thead>
    							<tbody>
    							<?php
    								$removals = $this->db->get_where('discount_removals',array('student_id'=>$this->uri->segment(3)))->result_array();
    								$i=1;
    								foreach($removals as $removal):
    							?>
                                <tr class="odd gradeX">
    								<td class="hidden">
                                    	 <?php echo $i;?>
                                    </td>
                                    <td>
                                    	 <?php echo $i;?>
                                    </td>
                                    <td>
    									 <?php echo $removal['amount'];?>
    								</td>
                                    <td>
    									 <?php echo $removal['type'];?>
    								</td>
    								<td>
    									 <?php echo $removal['comment'];?>
    								</td>
    								<td>
    									<?php echo $removal['removed_by'];?>
    								</td>
    								<td>
    									<?php echo $removal['created_at'];?>
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
            <?php
            endif;
            ?>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->
<div class="modal fade" id="purchased" tabindex="-1"   data-width="600" >
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Discount Reversal</h4>
    </div>
    <div class="modal-body">
        <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/students/remove_special_discount/<?php echo $this->uri->segment(3);?>">
            <div class="form-body">
                <div class="form-group">
                    <div class="col-md-12">
                        <label class="form-control" style="text-align: center" >Do you want to Reverse Discount?</label>
                    </div>
                </div>
                <div id = "apvdiv">
                        <div class="form-group" >
                            <label class="col-md-4 control-label">Reverse Amount</label>
                            <div class="col-md-8" >
                                <label id="reverse_amount"></label>
                            </div>
                        </div>

                        <div class="form-group" >
                            <label class="col-md-4 control-label">Reason</label>
                            <div class="col-md-8" >
                                <textarea class="form-control" name="comment" required></textarea>
                            </div>
                        </div>
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-12" style="text-align: center">
                                    <input type="hidden" id="rev_id" name="rev_id" value="" />
                                    <input type="hidden" id="rev_amount" name="rev_amount" value="" />
                                    <button type="submit" class="btn red">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
        </form>
    </div>
</div>


<div class="modal fade" id="admission_discount" tabindex="-1"   data-width="600" >
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Discount Reversal</h4>
    </div>
    <div class="modal-body">
        <form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/students/remove_admission_discount/<?php echo $this->uri->segment(3);?>">
            <div class="form-body">
                <div class="form-group">
                    <div class="col-md-12">
                        <label class="form-control" style="text-align: center" >Do you want to Reverse Discount?</label>
                    </div>
                </div>
                <div id = "apvdiv">
                        <div class="form-group" >
                            <label class="col-md-4 control-label">Reverse Amount</label>
                            <div class="col-md-8" >
                                <label class="reverse_amount"></label>
                            </div>
                        </div>

                        <div class="form-group" >
                            <label class="col-md-4 control-label">Reason</label>
                            <div class="col-md-8" >
                                <textarea class="form-control" name="comment" required></textarea>
                            </div>
                        </div>
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-12" style="text-align: center">
                                    <input type="hidden" class="rev_amount" name="rev_amount" value="" />
                                    <button type="submit" class="btn red">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
        </form>
    </div>
</div>
<script>
    document.addEventListener( "DOMContentLoaded", function(){

        var loans = <?php echo json_encode($discounts);?>;
        $(document).on("click", ".open-purchase_approval", function () {

            var myBookId = $(this).data('id');

            $(".modal-body #rev_id").val(loans[myBookId].id );
            $(".modal-body #rev_amount").val( loans[myBookId].discount);
            $(".modal-body #reverse_amount").html( loans[myBookId].discount);
        });
        
        jQuery('.reverse_admission_discount').click(function(){
            var rev_amount = jQuery(this).data('amount');
            jQuery('.reverse_amount').html(rev_amount);
            jQuery('.rev_amount').val(rev_amount);
        });
        
    }, false );
</script>