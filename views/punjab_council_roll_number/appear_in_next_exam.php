
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Edit Profile <small>You can edit your profile here</small>
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
								<i class="fa fa-list"></i> Next Exam Status
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/punjab_council_roll_number/appear_in_next_exam" enctype="multipart/form-data">
								<div class="form-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label"> Campus <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select name="campus_id" class="form-control input-inline input-large">
                                                        <?php
                                                            foreach($campuses as $campus):
                                                        ?>
                                                        <option value="<?php echo $campus['campus_id'];?>" <?php if($campus['campus_id']==@$this->input->post('campus_id')){echo 'selected';}?>><?php echo $campus['campus_name'];?></option>
                                                        <?php
                                                            endforeach;
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
										<div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Class <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <label class="radio-inline">
                                                    <input type="radio" name="class" id="optionsRadios4" value="1" <?php if(@$this->input->post('class')==1){echo 'checked';}?> /> 1st year </label>
                                                    <label class="radio-inline">
                                                    <input type="radio" name="class" id="optionsRadios5" value="2" <?php if(@$this->input->post('class')==2){echo 'checked';}?> /> 2nd Year </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Exam # <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select name="council_exam_no" class="form-control input-inline input-medium council_exam_no">
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
										
										<div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Notice : <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <label class="input-inline input-medium">Please Print Last Exam of First Year.<br />Please Print Last 2 Exams of Second Year.
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                        	<input type="hidden" name="check" value="1" />
                                            <button type="submit" class="btn green">Check</button>
											<button onclick="location.href = '<?php echo site_url();?>'" type="button" class="btn default">Cancel</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
            <?php
            	if(count(@$results)>0):
			?>
            <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> Please Verify All Students
							</div>
						</div>
						<div class="portlet-body">
                            <div class="table-toolbar">
								<div class="row">
									<div class="col-md-3">
                                        <form action="<?php echo site_url();?>/punjab_council_roll_number/create_excel_sheet" method="post">
                                            <input type="hidden" name="campus_id" value="<?php echo $this->input->post('campus_id');?>" />
                                            <input type="hidden" name="council_exam_no" value="<?php echo $this->input->post('council_exam_no');?>" />
                                            <input type="hidden" name="class" value="<?php echo $this->input->post('class');?>" />
                                            <button type="submit" class="btn green btn-block">
                                            <i class="fa fa-download"></i> Download Excel Sheet
                                            </button>
                                        </form>
									</div>
									<div class="col-md-3">
                                        <form action="<?php echo site_url();?>/punjab_council_roll_number/get_print_of_concel_list_paid" method="post">
                                            <input type="hidden" name="campus_id" value="<?php echo $this->input->post('campus_id');?>" />
                                            <input type="hidden" name="council_exam_no" value="<?php echo $this->input->post('council_exam_no');?>" />
                                            <input type="hidden" name="class" value="<?php echo $this->input->post('class');?>" />
                                            <input type="hidden" name="student_ids" class="student_ids form-group" value="" required />
                                            <button type="submit" class="btn green btn-block">
                                            <i class="fa fa-download"></i> View Council List
                                            </button>
                                        </form>
									</div>
									<div class="col-md-3">
                                        <form action="<?php echo site_url();?>/punjab_council_roll_number/get_covering_letter" method="post">
                                            <input type="hidden" name="campus_id" value="<?php echo $this->input->post('campus_id');?>" />
                                            <input type="hidden" name="council_exam_no" value="<?php echo $this->input->post('council_exam_no');?>" />
                                            <input type="hidden" name="class" value="<?php echo $this->input->post('class');?>" />
                                            <input type="hidden" name="student_ids" class="student_ids form-group" value="" required />
                                            <button type="submit" class="btn green btn-block">
                                            <i class="fa fa-download"></i> Print Covering Letter
                                            </button>
                                        </form>
									</div>
								</div>
                                <div class="row" style="margin-top:10px;">
									<div class="col-md-3">
                                        <form action="<?php echo site_url();?>/students/download" method="post">
                                            <input type="hidden" name="student_ids" class="student_ids form-group" value="" required />
                                            <button type="submit" class="btn purple btn-block">
                                                <i class="fa fa-download"></i> Download Excel Sheet
                                            </button>
                                        </form>
									</div>
                                    <div class="col-md-3">
                                        <form action="<?php echo site_url();?>/students/download_photos" method="post">
                                            <input type="hidden" name="student_ids" class="student_ids form-group" value="" required />
                                            <input type="hidden" name="class_id" class="form-group" value="1" required />
                                            <button type="submit" class="btn purple btn-block">
                                                <i class="fa fa-download"></i> Download Photos
                                            </button>
                                        </form>
									</div>
                                    <div class="col-md-3">
                                        <form action="<?php echo site_url();?>/students/download_cnic" method="post">
                                            <input type="hidden" name="student_ids" class="student_ids form-group" value="" required />
                                            <input type="hidden" name="class_id" class="form-group" value="1" required />
                                            <button type="submit" class="btn purple btn-block">
                                                <i class="fa fa-download"></i> Download CNIC
                                            </button>
                                        </form>
									</div>
                                    <div class="col-md-3">
                                        <form action="<?php echo site_url();?>/students/download_result_card" method="post">
                                            <input type="hidden" name="student_ids" class="student_ids form-group" value="" required />
                                            <input type="hidden" name="class_id" class="form-group" value="1" required />
                                            <button type="submit" class="btn purple btn-block">
                                                <i class="fa fa-download"></i> Download Council Result Card
                                            </button>
                                        </form>
									</div>
                                    <div class="col-md-3">
                                        <form action="<?php echo site_url();?>/students/download_matric_result_card" method="post">
                                            <input type="hidden" name="student_ids" class="student_ids form-group" value="" required />
                                            <input type="hidden" name="class_id" class="form-group" value="1" required />
                                            <button type="submit" class="btn purple btn-block">
                                                <i class="fa fa-download"></i> Download Matric Result Card
                                            </button>
                                        </form>
									</div>
                                    <div class="col-md-3">
                                        <form action="<?php echo site_url();?>/students/new_download" method="post">
                                            <input type="hidden" name="student_ids" class="student_ids form-group" value="" required />
                                            <input type="hidden" name="class" value="<?php echo @$this->input->post('class');?>" required />
                                            <input type="hidden" name="council_exam_no" value="<?php echo @$this->input->post('council_exam_no');?>" required />
                                            <button type="submit" class="btn purple btn-block">
                                                <i class="fa fa-download"></i> Download New Excel Sheet
                                            </button>
                                        </form>
									</div>
                                    <div class="clearfix"></div>
								</div>
							</div>
                            <button id="print-btn" type="button" class="btn btn-primary btn-sm d-print-none"><i class="dripicons-print"></i> Print</button>
                            <br /><br />
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="col-md-3">
                                            <div class="input-group input-medium">
                                                Select All <input type="checkbox" id="checkAll" class="all_selection" name="all_selection" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive" id="print_div">
                                <table class="table table-bordered table-hover table-responsive">
                                <thead>
                                <tr>
                                    <th>
                                         Campus
                                    </th>
                                    <th>
                                         Class
                                    </th>
                                    <th>
                                         Student Name
                                    </th>
                                    <th>
                                         CNIC
                                    </th>
                                    <th>
                                        Phone No
                                    </th>
                                    <th>
                                         College Roll No
                                    </th>
                                    <th>
                                        Council Fee
                                    </th>
                                    <th>
                                         Dead Line
                                    </th>
                                    <th>
                                         Last Exam
                                    </th>
                                    <th>
                                         Last Roll No
                                    </th>
                                    <th>
                                         Computer No.
                                    </th>
                                    <th>
                                         Fee Remarks
                                    </th>
                                    <th>
                                         Student Status
                                    </th>
                                    <th>
                                         Contractor
                                    </th>
                                    <th>
                                         Submit Fee By Student
                                    </th>
                                    <th>
                                         Submit Fee By College in Council
                                    </th>
                                    <th>
                                         Result Card Uploaded
                                    </th>
                                    <th>
                                         Fee Created By
                                    </th>
                                    <th>
                                        Documents
                                    </th>
                                </tr>
                                </thead>
                                <tbody>

                                <?php
                                    $i=0;
                                    $counter=0;
                                    foreach(@$results as $result):

                                    $this->db->select('*,students.status as status');
                                    $this->db->from('students');
                                    $this->db->join('classes', 'classes.class_id=students.class_id', 'INNER');
                                    $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
                                    $this->db->where('students.student_id', $result['custom_student_id']);
                                    $this->db->or_where('students.student_id', $result['student_id']);
                                    $student_data = $this->db->get()->result_array();

                                        if (@$student_data[0]['status'] == 0) {
                                            $cl =  "danger";
                                        }   else {
                                            $cl = "";
                                        }

                                    if((@$student_data[0]['campus_id']==@$this->input->post('campus_id') || @$student_data[0]['campus_id']=='') && @$student_data[0]['status'] == '1'):
                                    $counter++;
                                ?>
                                <tr class="<?php echo $cl ?>">
                                    <td>
                                        <?php echo @$student_data[0]['campus_name'];?>
                                    </td>
                                    <td>
                                        <?php echo @$student_data[0]['name'];?>
                                    </td>
                                    <td>
                                        <?php echo @$student_data[0]['first_name'].' '.@$student_data[0]['last_name'];?>
                                    </td>
                                    <td>
                                        <?php echo @$student_data[0]['cnic'];?>
                                    </td>
                                    <td>
                                        <?php echo @$student_data[0]['mobile'];?><br />
                                        <?php echo @$student_data[0]['emergency_no'];?>
                                    </td>
                                    <td>
                                        <?php echo @$student_data[0]['roll_no'];?>
                                    </td>
                                    <td>
                                        <?php echo $result['amount'];?>
                                    </td>
                                    <td>
                                        <?php echo $result['dead_line'];?>
                                    </td>
                                    <td>
                                        <?php
                                        $lastCouncilexam = $this->db->order_by("id","DESC")->
                                        get_where('punjab_council_roll_number',array('cnic'=>@$student_data[0]['cnic']))->row();
                                        echo @$lastCouncilexam->council_exam_no;
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo @$lastCouncilexam->roll_no;?>
                                    </td>
                                    <td>
                                        <?php echo @$lastCouncilexam->computer_no;?>
                                    </td>
                                    <td>
                                        <?php echo $result['payment_comment'];?>
                                    </td>
                                    <td>
                                        <?php if ($student_data[0]['status'] == 0) {
                                            echo "Deleted";
                                        }   else {
                                            echo "Active";
                                        }

                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                            if(@$student_data[0]['contractor_id']!=0)
                                            {
                                                echo @$this->db->get_where('contractors', array('contractor_id'=>@$student_data[0]['contractor_id']))->row()->name;
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                            if($result['paid']==1)
                                            {
                                                if($result['scan_challan']=='')
                                                {

                                                }
                                                elseif($result['scan_challan']!='' )
                                                {
                                                    echo '<a target="_blank" href="'. base_url().'uploads/'.$result['scan_challan'].'" class="btn green"><i class="fa fa-check"></i></a><br />'.$result['paid_date'].'<br /><a target="_blank" href="'.site_url().'/documents/student_supplementary_document/'.@$student_data[0]['student_id'].'/'.$this->input->post('council_exam_no').'/'.$this->input->post('class').'" class="btn green">Council Doc</i></a>';
                                                }

                                                if($result['fee_pay_through']=='college' && $result['fee_submit_type']=='computer_challan')
                                                {
                                                     echo '<a target="_blank" href="'.site_url().'/students/print_college_challan/'.$result['id'].'" class="btn green"><i class="fa fa-check"></i></a><br />'.$result['paid_date'].'<br /><a target="_blank" href="'.site_url().'/documents/student_supplementary_document/'.@$student_data[0]['student_id'].'/'.$this->input->post('council_exam_no').'/'.$this->input->post('class').'" class="btn green">Council Doc</i></a>';

                                                }
                                                if($result['fee_pay_through']=='pay_pro')
                                                {
                                                    echo '<a href="#" class="btn green">PayPro<i class="fa fa-check"></i></a><br />'.$result['paid_date'].'<br /><a target="_blank" href="'.site_url().'/documents/student_supplementary_document/'.@$student_data[0]['student_id'].'/'.$this->input->post('council_exam_no').'/'.$this->input->post('class').'" class="btn green">Council Doc</i></a>';
                                                }

                                            }
                                            else
                                            {
                                                echo '<button class="btn red"><i class="fa fa-close"></i></button>';
                                            }
                                            if ($result['paid']==1):
                                        ?>
                                        <input type="checkbox" class="selection" name="selection" value="<?php echo @$student_data[0]['student_id'];?>"/>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                            $checkFeeSubmitByCollegeInCouncil = $this->db->
                                            get_where('expenses',array('student_id'=>@$student_data[0]['student_id'],
                                                      'council_exam_no'=>$this->input->post('council_exam_no'),
                                                      'class'=>$this->input->post('class')))->result_array();
                                            if(count($checkFeeSubmitByCollegeInCouncil)>0)
                                            {
                                                if($checkFeeSubmitByCollegeInCouncil[0]['online_image']!='')
												{
													echo '<a href="'.$checkFeeSubmitByCollegeInCouncil[0]['online_image'].'" target="_blank" class="btn green"><i class="fa fa-check"></i></a><br />'.$checkFeeSubmitByCollegeInCouncil[0]['date'];
												}
												else
												{
													echo '<a href="'.base_url().'uploads/'.$checkFeeSubmitByCollegeInCouncil[0]['image'].'" target="_blank" class="btn green"><i class="fa fa-check"></i></a><br />'.$checkFeeSubmitByCollegeInCouncil[0]['date'];
												}
                                            }
                                            else
                                            {
                                                echo '<button class="btn red"><i class="fa fa-close"></i></button>';
                                            }
                                        ?>
                                    </td>
                                    <td>

                                        <?php
                                            @$image=@$this->db->get_where('punjab_council_roll_number','cnic = "'.@$student_data[0]['cnic'].'" and class = "1" and council_exam_no = "'.@$lastCouncilexam->council_exam_no.'"')->row()->result_image;

                                            if(@$image!='' && @$image!= NULL):
                                                ?>
                                                <a href="<?php echo base_url().$image;?>" target="_blank">
                                                    <i class="fa fa-image"></i>
                                                </a>
                                            <?php
                                            endif;
                                        ?>

                                    </td>
                                    <td>
                                        <?php echo $result['add_by'];?>
                                    </td>
                                    <td>
                                        <?php
                                            //PHOTO CHECK
                                            $photo = $this->db->get_where('student_documents',array('student_id'=>$student_data[0]['student_id'],'type'=>'Photo'))->result_array();
                                            if(count($photo)>0)
                                            {
                                                echo '<button class="btn green"><i class="fa fa-check"></i> Photo</button>';
                                            }
                                            else
                                            {
                                                echo '<button class="btn red"><i class="fa fa-remove"></i> Photo</button>';
                                            }
                                            //RESULT CARD CHECK
                                            $result_card = $this->db->get_where('student_documents',array('student_id'=>$student_data[0]['student_id'],'type'=>'Result Card'))->result_array();
                                            if(count($result_card)>0)
                                            {
                                                echo '<button class="btn green"><i class="fa fa-check"></i> Result Card</button>';
                                            }
                                            else
                                            {
                                                echo '<button class="btn red"><i class="fa fa-remove"></i> Result Card</button>';
                                            }
                                            //ID CARD CHECK
                                            $id_card = $this->db->get_where('student_documents',array('student_id'=>$student_data[0]['student_id'],'type'=>'ID Card'))->result_array();
                                            if(count($id_card)>0)
                                            {
                                                $identity = '<button class="btn green"><i class="fa fa-check"></i> Identity</button>';
                                            }
                                            else
                                            {
                                                $identity = '<button class="btn red"><i class="fa fa-remove"></i> Identity</button>';
                                            }
                                            //BFORM CHECK
                                            if(count($id_card)<1)
                                            {
                                                $bform = $this->db->get_where('student_documents',array('student_id'=>$student_data[0]['student_id'],'type'=>'B - FORM'))->result_array();
                                                if(count($bform)>0)
                                                {
                                                    $identity = '<button class="btn green"><i class="fa fa-check"></i> Identity</button>';
                                                }
                                                else
                                                {
                                                    $identity = '<button class="btn red"><i class="fa fa-remove"></i> Identity</button>';
                                                }
                                            }
                                            echo $identity;
                                        ?>
                                    </td>
                                </tr>
                                <?php
                                    endif;
                                    $i++;
                                    endforeach;
                                ?>
                                </tbody>
                                </table>
                            </div>
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
    <script>
        document.addEventListener( "DOMContentLoaded", function(){
            jQuery('.selection').change(function(){
                var ids = [];
                jQuery.each(jQuery("input[name='selection']:checked"), function(){
                    ids.push(jQuery(this).val());
                });
                jQuery('.student_ids').val(ids.join(","));
            });
            jQuery('#checkAll').click(function(){
                //alert();
                jQuery('.selection').trigger('click');
            });
            $("#print-btn").on("click", function(){
                var divToPrint=document.getElementById('print_div');
                var newWin=window.open('','Print-Window');
                newWin.document.open();
                newWin.document.write('<link rel="stylesheet" href="<?php echo base_url();?>assets/global/plugins/bootstrap/css/bootstrap.min.css" type="text/css"><style type="text/css">@media print {}</style><body onload="window.print()">'+divToPrint.innerHTML+'</body>');
                newWin.document.close();
            });
        }, false );

    </script>