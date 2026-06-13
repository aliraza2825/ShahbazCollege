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

           
			<div class="row">
				
				<div class="col-md-12 ">
					
				
					<div class="form-body">
					
                        <!-- BEGIN SAMPLE FORM PORTLET-->
                        <div class="portlet box green ">

                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-user"></i> Syllabus View of <?php echo $this->db->where('course_subject_id',$subject)->get('course_subjects')->row()->subject_name ?>
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
                                            Topics
                                        </th>
										<th>
                                            Require Lectures
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $i=0;
                                    $t=1;

                                        foreach($syllabus as $key=>$lecs):?>
											
                                            <tr class="odd gradeX">
                                                <td >
                                                    <?php echo $key+1;?>
                                                </td>

												
                                                <td>
                                                    <?php
													
													if($lecs ['topic_id'] != "")
													{
														echo "<strong>Lecture</strong> <br />";
														$topic =$this->db->where('topic_id',$lecs ['topic_id'])->get('topics')->row()->topic_name;
														echo $topic;
													}else
													{
														echo "<strong>Practical</strong> <br />";
														$topic =$this->db->where('practical_id',$lecs ['practical_id'])->get('practicals')->row()->practical_name;
														echo $topic;
													}														
                                                    ?>
													
                                                </td>
												<td>
                                                    <?php  echo $lecs ['require_lectures']  ?>
													
                                                </td>

                                            </tr>
                                            <?php
                                           
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
	<!-- END CONTENT -->
