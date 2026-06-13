
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
								<i class="fa fa-list"></i> Add Exam Sequence
							</div>
						</div>
						<div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/punjab_council_roll_number/add_exam_sequence" enctype="multipart/form-data">
								<div class="form-body">
                                    <div class="row">
                                        <div class="row col-md-6">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="col-md-6 control-label">Class <span class="required">*</span></label>
                                                    <div class="col-md-6">
                                                        <label class="radio-inline">
                                                        <input type="radio" name="type" id="optionsRadios4" value="supplementary" <?php if(@$seq->type == "supplementary") echo "checked"; else echo "";?>> Supplementary </label>
                                                        <label class="radio-inline">
                                                        <input type="radio" name="type" id="optionsRadios5" value="annual"  <?php if(@$seq->type == "annual") echo "checked"; else echo "";?>> Annual </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="col-md-6 control-label"> Next Exam No First Year<span class="required">*</span></label>
                                                    <div class="col-md-6">
                                                        <input type="number" class="form-control input-inline input-medium" name="first_year" placeholder="Enter Exam No" value="<?php echo @$seq->first_year?>" required>
                                                        <span class="help-inline"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="col-md-6 control-label"> Next Exam No Second Year <span class="required">*</span></label>
                                                    <div class="col-md-6">
                                                        <input type="number" class="form-control input-inline input-medium" name="second_year" placeholder="Enter Exam No" value="<?php echo @$seq->second_year?>" required>
                                                        <span class="help-inline"></span>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="row col-md-6 row">
                                            <label class="col-md-12 control-label" style="text-align: left"><span class="required">*</span>  If council Demand Annual Exam again than Annual Exam value will be same like Supply Value</label>
                                            <br />
                                            <label class="col-md-12 control-label" style="text-align: left"> <span class="required">*</span>  if council Demand Supply again than value of Annual will Swap with Supply</label>
                                            <br />
                                            <label class="col-md-12 control-label" style="text-align: left"> <span class="required">*</span>  Must Create 2 Exams 1 Supplementary and 1 Annual after uploading Result</label>
                                        </div>
                                    </div>
                                    <?php
                                    if (@$seq):
                                    ?>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">First Year Roll NO</label>
                                                <div class="col-md-9">
                                                    <input type="file" name="first_year_roll_no">
                                                    <input type="hidden" name="old_first_year_roll_no" value="<?php echo $seq->first_year_roll_no;?>">
                                                    <span class="help-inline">
                                                        <?php 
                                                            if($seq->first_year_roll_no!=''):
                                                                echo '<a target="_blank" class="btn green" href="'.base_url().'exam_sequence_documents/'.$seq->first_year_roll_no.'">File</a>';
                                                            endif;
                                                        ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">First Year Council Date Sheet</label>
                                                <div class="col-md-9">
                                                    <input type="file" name="first_year_date_sheet">
                                                    <input type="hidden" name="old_first_year_date_sheet" value="<?php echo $seq->first_year_date_sheet;?>">
                                                    <span class="help-inline">
                                                        <?php 
                                                            if($seq->first_year_date_sheet!=''):
                                                                echo '<a target="_blank" class="btn green" href="'.base_url().'exam_sequence_documents/'.$seq->first_year_date_sheet.'">File</a>';
                                                            endif;
                                                        ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">First Year Council NTS Sheet</label>
                                                <div class="col-md-9">
                                                    <input type="file" name="first_year_date_sheet_nts">
                                                    <input type="hidden" name="old_first_year_date_sheet_nts" value="<?php echo $seq->first_year_date_sheet_nts;?>">
                                                    <span class="help-inline">
                                                        <?php 
                                                            if($seq->first_year_date_sheet_nts!=''):
                                                                echo '<a target="_blank" class="btn green" href="'.base_url().'exam_sequence_documents/'.$seq->first_year_date_sheet_nts.'">File</a>';
                                                            endif;
                                                        ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">First Year Result</label>
                                                <div class="col-md-9">
                                                    <input type="file" name="first_year_result">
                                                    <input type="hidden" name="old_first_year_result" value="<?php echo $seq->first_year_result;?>">
                                                    <span class="help-inline">
                                                        <?php 
                                                            if($seq->first_year_result!=''):
                                                                echo '<a target="_blank" class="btn green" href="'.base_url().'exam_sequence_documents/'.$seq->first_year_result.'">File</a>';
                                                            endif;
                                                        ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Second Year Roll NO</label>
                                                <div class="col-md-9">
                                                    <input type="file" name="second_year_roll_no">
                                                    <input type="hidden" name="old_second_year_roll_no" value="<?php echo $seq->second_year_roll_no;?>">
                                                    <span class="help-inline">
                                                        <?php 
                                                            if($seq->second_year_roll_no!=''):
                                                                echo '<a target="_blank" class="btn green" href="'.base_url().'exam_sequence_documents/'.$seq->second_year_roll_no.'">File</a>';
                                                            endif;
                                                        ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Second Year Council Date Sheet</label>
                                                <div class="col-md-9">
                                                    <input type="file" name="second_year_date_sheet">
                                                    <input type="hidden" name="old_second_year_date_sheet" value="<?php echo $seq->second_year_date_sheet;?>">
                                                    <span class="help-inline">
                                                        <?php 
                                                            if($seq->second_year_date_sheet!=''):
                                                                echo '<a target="_blank" class="btn green" href="'.base_url().'exam_sequence_documents/'.$seq->second_year_date_sheet.'">File</a>';
                                                            endif;
                                                        ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Second Year Council NTS Sheet</label>
                                                <div class="col-md-9">
                                                    <input type="file" name="second_year_date_sheet_nts">
                                                    <input type="hidden" name="old_second_year_date_sheet_nts" value="<?php echo $seq->second_year_date_sheet_nts;?>">
                                                    <span class="help-inline">
                                                        <?php 
                                                            if($seq->second_year_date_sheet_nts!=''):
                                                                echo '<a target="_blank" class="btn green" href="'.base_url().'exam_sequence_documents/'.$seq->second_year_date_sheet_nts.'">File</a>';
                                                            endif;
                                                        ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Second Year Result</label>
                                                <div class="col-md-9">
                                                    <input type="file" name="second_year_result">
                                                    <input type="hidden" name="old_second_year_result" value="<?php echo $seq->second_year_result;?>">
                                                    <span class="help-inline">
                                                        <?php 
                                                            if($seq->second_year_result!=''):
                                                                echo '<a target="_blank" class="btn green" href="'.base_url().'exam_sequence_documents/'.$seq->second_year_result.'">File</a>';
                                                            endif;
                                                        ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
								    <?php
                                    endif;
                                    ?>
                                </div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                            <input value = "<?php echo @$seq->id?>" name="seq_id" type="hidden"  />
                                            <input value = "1" name="submit" type="hidden" />
                                            <button type = "submit" class="btn green">Update Sequence</button>
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
            	if(count(@$sequences)>0):
			?>
            <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> Sequences
							</div>
						</div>
						<div class="portlet-body">
                            <table class="table table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
                                <th class="hidden">
									 Hidden
								</th>
								<th>
									 Type
								</th>
								<th>
									 First Year Exam No
								</th>
								<th>
									 Second Year Exam No
								</th>
                                <th>
                                    First Year FILES
                                </th>
                                <th>
                                    Second Year FILES
                                </th>
								<th>
									 Action
								</th>

							</tr>
							</thead>
							<tbody>
							<?php
								$i=0;
								foreach ($sequences as $sequence):
							?>
                            <tr>
                                <td class="hidden">
                                	<?php echo $i;?>
                                </td>
                                <td>
									<?php echo  strtoupper($sequence['type']);?>
								</td>
                                <td>
									<?php echo  $sequence['first_year'];?>
								</td>
                                <td>
									<?php echo  $sequence['second_year'];?>
								</td>
                                <td>
									<?php
                                        if ($sequence['first_year_roll_no'] != null)
                                            echo '<a href="'.base_url().'exam_sequence_documents/'.$sequence['first_year_roll_no'].'" target="_blank" class="btn btn-info">Roll No Slips</a>';
                                        if ($sequence['first_year_date_sheet'] != null)
                                            echo '<a href="'.base_url().'exam_sequence_documents/'.$sequence['first_year_date_sheet'].'" target="_blank" class="btn btn-info">Council Datesheet</a>';
                                        if ($sequence['first_year_date_sheet_nts'] != null)
                                            echo '<a href="'.base_url().'exam_sequence_documents/'.$sequence['first_year_date_sheet_nts'].'" target="_blank" class="btn btn-info">NTS Datesheet</a>';
                                        if ($sequence['first_year_result'] != null)
                                            echo '<a href="'.base_url().'exam_sequence_documents/'.$sequence['first_year_result'].'" target="_blank" class="btn btn-info">Result</a>';

                                    ?>
								</td>
                                <td>
									<?php
                                        if ($sequence['first_year_roll_no'] != null)
                                            echo '<a href="'.base_url().'exam_sequence_documents/'.$sequence['second_year_roll_no'].'" target="_blank" class="btn btn-info">Roll No Slips</a>';
                                        if ($sequence['second_year_date_sheet'] != null)
                                            echo '<a href="'.base_url().'exam_sequence_documents/'.$sequence['second_year_date_sheet'].'" target="_blank" class="btn btn-info">Council Datesheet</a>';
                                        if ($sequence['second_year_date_sheet_nts'] != null)
                                            echo '<a href="'.base_url().'exam_sequence_documents/'.$sequence['second_year_date_sheet_nts'].'" target="_blank" class="btn btn-info">NTS Datesheet</a>';
                                        if ($sequence['second_year_result'] != null)
                                            echo '<a href="'.base_url().'exam_sequence_documents/'.$sequence['second_year_result'].'" target="_blank" class="btn btn-info">Result</a>';

                                    ?>
								</td>
                                <td>
                                    <a href="<?php echo site_url().'/Punjab_council_roll_number/add_exam_sequence/'.$sequence['id']?>" class="btn btn-info">
                                        Edit
                                    </a>
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