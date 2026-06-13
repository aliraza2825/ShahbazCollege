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
								<i class="fa fa-list"></i>All Interviews
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
									 Date
								</th>
                                <th>
									 Campus
								</th>
                                <th>
                                	Name
                                </th>
								<th>
									 Qualification
								</th>
								<th>
									 Cell Number
								</th>
                                <th>
									 Timing
								</th>
                                <th>
									 Personality
								</th>
                                <th>
									 Salary Offer Responce
								</th>
                                <th>
									 Salary Demand
								</th>
                                <th>
									 Other Current Job
								</th>
                                <th>
									 Previous Experience
								</th>
                                <th>
									 Gender
								</th>
                                <th>
									 Marital Status
								</th>
                                <th>
									 Grantable
								</th>
                                <th>
									 Guarantee Person
								</th>
                                <th>
									 Father Occupation
								</th>
                                <th>
									 Job Post Wanted
								</th>
                                <th>
									 Residence
								</th>
                                <th>
									 Expert In
								</th>
                                
                                <th>
									 CV
								</th>
                                <th>
									 Suggestion
								</th>
                                <th>
									 Reviews
								</th>
                                <th>
									 Your Opinion
								</th>
                                <th>
									 Add By
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($interviews as $interview):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $interview['interview_id']?>
								</td>
                                <td>
									 <?php echo date('d M, Y',strtotime($interview['date']));?>
								</td>
								<td>
									 <?php echo $interview['campus_name']?>
								</td>
                                <td>
                                	<?php echo $interview['name'];?>
                                </td>
                                <td>
									 <?php echo $interview['qualification']?>
								</td>
								<td>
									 <a href="sms:<?php echo $interview['cell_number']?>?&body=Hello" class="btn green"><i class="fa fa-comment"></i> <?php echo $interview['cell_number']?></a>
									 <br /><br />
									 <a href="tel:<?php echo $interview['cell_number']?>" class="btn green"><i class="fa fa-phone"></i> <?php echo $interview['cell_number']?></a>
								</td>
                                <td>
									 <?php echo $interview['timing']?>
								</td>
                                <td>
									 <?php echo $interview['personality']?>
								</td>
                                <td>
									 <?php echo $interview['salary_offer_responce']?>
								</td>
                                <td>
									 <?php echo $interview['salary_demand']?>
								</td>
                                <td>
									 <?php echo $interview['other_current_job']?>
								</td>
                                <td>
									 <?php echo $interview['previous_experience']?>
								</td>
                                <td>
									 <?php echo $interview['gender']?>
								</td>
                                <td>
									 <?php echo $interview['marital_status']?>
								</td>
                                <td>
									 <?php echo $interview['grantable']?>
								</td>
                                <td>
									 <?php echo $interview['guarantee_person']?>
								</td>
                                <td>
									 <?php echo $interview['father_occupation']?>
								</td>
                                <td>
									 <?php echo $interview['job_post_wanted']?>
								</td>
                                <td>
									 <?php echo $interview['residence']?>
								</td>
                                <td>
									 <?php echo $interview['expert_in']?>
								</td>
                                
                                <td>
									 <a href="<?php if($interview['upload_cv']==''){echo base_url().'uploads/'.$interview['cv'];}else{echo str_replace($bucket_address,$cloudfront_address,$interview['online_cv']);}?>" target="_blank" class="btn purple"><i class="fa fa-image"></i> CV</a>
								</td>
                                <td>
									 <?php echo $interview['suggestion']?>
								</td>
                                <td>
									 <?php echo $interview['reviews']?>
								</td>
                                <td>
									 <?php echo $interview['your_opinion']?>
								</td>
                                <td>
									 <?php echo $interview['add_by']?>
								</td>
                                <td>
									 <?php
										if($this->session->userdata('role')=='Admin' || @$myAccess[0]['hr_edit_interview']==1):
									 ?>
                                     <a href="<?php echo site_url();?>/hr/edit_interview/<?php echo $interview['interview_id']?>" title="Edit" class="btn blue"><i class="fa fa-edit"></i></a>
                                      <?php
                                     	endif;
									 ?>
									 
									 <?php
										if($this->session->userdata('role')=='Admin' || @$myAccess[0]['hr_delete_interview']==1):
									 ?>
									 <a onclick="return confirm('Are you sure you want to delete this Interview?')" href="<?php echo site_url();?>/hr/delete/<?php echo $interview['interview_id']?>" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>
                                     <?php
                                     	endif;
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
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->