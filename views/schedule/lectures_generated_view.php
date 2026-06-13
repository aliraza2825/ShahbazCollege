<?php
	$myAccess = checkUserAccess();
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

            <?php
            if(@$myAccess[0]['dashboard_check_student_box']==1 || $this->session->userdata('role')=='Admin'):
                ?>
                <div class="row">
				
				

                    <div class="col-md-12 ">
					
					<form class="form-horizontal" enctype="multipart/form-data" role="form" method="post" action="<?php echo site_url();?>/schedule/insert_session_syllabus">

                    <div class="form-body">
					
                        <!-- BEGIN SAMPLE FORM PORTLET-->
                        <div class="portlet box green ">

                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-user"></i> Syllabus View
                                </div>
                            </div>



                        <div class="portlet-body table-responsive">


                                <table class="table table-bordered table-hover" >
                                    <thead>
                                    <tr>
                                        <th >
                                            Sr
                                        </th>

                                        <th>
                                            Lecture
                                        </th>

                                        <th>
                                            Day
                                        </th>

                                        <th>
                                            Date
                                        </th>

                                        <th>
                                            Topics
                                        </th>

                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $i=0;
                                    $t=1;

                                        foreach($lectures as $key=>$lecs):?>
											
											<input type="hidden" name="subject_id" value="<?php echo $subject; ?>">
											<input type="hidden" name="day[]" value="<?php echo $lecs ['day']; ?>"> 
											<input type="hidden" name="date[]" value="<?php echo $lecs ['date']; ?>"> 
											
                                            <tr class="odd gradeX">
                                                <td >
                                                    <?php echo $key+1;?>
                                                </td>

                                                <td><?php  echo $lecs ['name']  ?></td>
                                                <td><?php  echo $lecs ['day']  ?></td>
                                                <td><?php  echo $lecs ['date']  ?></td>
                                                <td>
                                                    <?php
													
													if($t>$this->input->post('test_after'))
													{
														echo "Quiz";
														$t=1;
													?>
														
														<input type="hidden" name="topic_ids[]" value="">
														<input type="hidden" name="practical_ids[]" value="">
														<input type="hidden" name="lecture_id" value="<?php echo $lecture_id; ?>">
														<input type="hidden" name="unique_syllabus_id" value="<?php echo $revision;?>">

													<?php
														
													}
													
													else
													{

                                                        $lecture_max =$this->db->get_where('syllabus','unique_syllabus_id = "'.$revision.'" and ((require_lectures like"%-'.($i+1).'%" or require_lectures like"%'.($i+1).'-%") or require_lectures = "'.($i+1).'")')->result_array();
													   
														$count=count($lecture_max);
														$total_topics='';
														foreach ($lecture_max as $key=>$lef)
														{
														    $arr = explode("-",$lef['require_lectures']);
														    if (in_array(($i+1),$arr))
                                                            {
															if ($key == $count - 1) {
                                                                if ($lef['topic_id'] != '')
                                                                    $total_topics .= $lef['topic_id'];

                                                            } else {
                                                                if ($lef['topic_id'] != '')
                                                                    $total_topics .= ($lef['topic_id'] . ',');
                                                            }
                                                        }

														}
														$topics=explode(",",$total_topics);
														$lecture_topics =$this->db->where_in('topic_id',$topics)->get('topics')->result_array();
                                                        if(count($lecture_topics)>0)
                                                        {
                                                            echo "<strong>Topics</strong><br />";
                                                        }
														foreach ($lecture_topics as $key=>$teps)
														{

															 echo $teps['topic_name'].'<br />';

														}
														
														$total_practical='';
														foreach ($lecture_max as $key=>$lef)
														{
															if ($key == $count-1)
															{
																if($lef['practical_id']!= '')
																$total_practical.=$lef['practical_id'];

															}else
															{
																if($lef['practical_id']!= '')
																$total_practical.=($lef['practical_id'].',');
															}

														}
														$topics=explode(",",$total_practical);
														$lecture_topics =$this->db->where_in('practical_id ',$topics)->get('practicals')->result_array();

														if(count($lecture_topics)>0)
														{
															echo "<strong>Practicals</strong><br />";
														}
														foreach ($lecture_topics as $key=>$teps)
														{
															echo $teps['practical_name'].'<br />';
														}
														$i++;
														$t++;
														?>
														
														<input type="hidden" name="topic_ids[]" value="<?php echo $total_topics; ?>">
														<input type="hidden" name="practical_ids[]" value="<?php echo $total_practical; ?>">
														<input type="hidden" name="lecture_id" value="<?php echo $lecture_id; ?>">
														<input type="hidden" name="start_date" value="<?php echo $start_date; ?>">
														
														<?php

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
						
						 <div class="form-group" style="display: none">
                            <label class="col-md-2 control-label"> Select Session <span class="required">*</span></label>
                                 <div class="col-md-9">
                                    <input name="sessions" id="select2_sample1" class="form-control input-inline input-large" value="<?php echo $lecture['session'] ?>" >
                                </div>
                        </div>
						
						
                        <!-- END SAMPLE FORM PORTLET-->
						</div>
					
							<div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">

                                        <button type="submit" class="btn red">Submit</button>

                                    </div>
                                </div>
                            </div>

                    </form>

                </div>

            <?php
            endif;
            ?>


		</div>

	</div>
	<!-- END CONTENT -->
