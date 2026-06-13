<?php
	$answers = explode(',',$question[0]['answer']);
?>
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Add Class <small>add your desire class</small>
			</h3>-->
			<!-- END PAGE HEADER-->
            <!-- BEGIN DASHBOARD STATS -->
			
			<!-- END DASHBOARD STATS -->
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
								<i class="fa fa-edit"></i> Edit Question (<?php echo $topics[0]['topic_name'];?>)
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/test_engine/update_question/<?php echo $this->uri->segment(3);?>/<?php echo $this->uri->segment(4);?>" enctype="multipart/form-data">
								<div class="form-body">
                                    <div class="row">
									<div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Select Topic <span class="required">*</span></label>
                                                <div class="col-md-6">

                                                    <select class="form-control question_type" name="topic">
                                                        <?php foreach ($alltopics as $top){ ?>
                                                            <option value="<?php echo $top['topic_id'] ?>" <?php if($question[0]['topic_id']==$top['topic_id']){echo 'selected';}?>><?php echo $top['topic_name'] ?></option>
                                                        <?php }?>

                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Type <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select class="form-control question_type" name="type">
                                                          <option value="radio" <?php if($question[0]['type']=='radio'){echo 'selected';}?>>Radio (MCQs)</option>
                                                          <option value="multiple" <?php if($question[0]['type']=='multiple'){echo 'selected';}?>>Multiple (MCQs)</option>
                                                          <option value="short-question" <?php if($question[0]['type']=='short-question'){echo 'selected';}?>>Short Question</option>
														  <option value="long-question" <?php if($question[0]['type']=='long-question'){echo 'selected';}?>>Long Question</option>
                                                      </select>
                                                      <!--QUESTION DIFFICULTY-->
                                                      <label class="radio-inline">
                                                      <input type="radio" name="difficulty" id="optionsRadios1" value="easy" <?php if($question[0]['difficulty']=='easy'){echo 'checked';}?>> Easy </label>
                                                      <label class="radio-inline">
                                                      <input type="radio" name="difficulty" id="optionsRadios2" value="medium" <?php if($question[0]['difficulty']=='medium'){echo 'checked';}?>> Medium </label>
                                                      <label class="radio-inline">
                                                      <input type="radio" name="difficulty" id="optionsRadios3" value="hard" <?php if($question[0]['difficulty']=='hard'){echo 'checked';}?>> Hard </label>
                                                      <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-1 control-label">Question <span class="required">*</span></label>
                                                <div class="col-md-11">
                                                    <textarea class="wysihtml5 form-control" rows="3" name="question" required><?php echo $question[0]['question']?></textarea>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                        	if($question[0]['type']!='short-question' && $question[0]['type']!='long-question'):
										?>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Option A <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <textarea class="form-control" rows="3" name="option_1" required><?php echo $question[0]['option_1']?></textarea>
                                                    <span class="help-inline">
                                                        <label class="checkbox-inline"><input type="checkbox" id="inlineCheckbox1" name="answer[]" value="A" <?php if(in_array('A',$answers)){echo 'checked';}?> /> Correct Option </label>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Option B <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <textarea class="form-control" rows="3" name="option_2" required><?php echo $question[0]['option_2']?></textarea>
                                                    <span class="help-inline">
                                                        <label class="checkbox-inline"><input type="checkbox" id="inlineCheckbox1" name="answer[]" value="B" <?php if(in_array('B',$answers)){echo 'checked';}?> /> Correct Option </label>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Option C <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <textarea class="form-control" rows="3" name="option_3" required><?php echo $question[0]['option_3']?></textarea>
                                                    <span class="help-inline">
                                                        <label class="checkbox-inline"><input type="checkbox" id="inlineCheckbox1" name="answer[]" value="C" <?php if(in_array('C',$answers)){echo 'checked';}?> /> Correct Option </label>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Option D <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <textarea class="form-control" rows="3" name="option_4" required><?php echo $question[0]['option_4']?></textarea>
                                                    <span class="help-inline">
                                                        <label class="checkbox-inline"><input type="checkbox" id="inlineCheckbox1" name="answer[]" value="D" <?php if(in_array('D',$answers)){echo 'checked';}?> /> Correct Option </label>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                        	endif;
										?>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-1 control-label">Explanation</label>
                                                <div class="col-md-11">
                                                    <textarea class="wysihtml5 form-control" rows="3" name="explanation"><?php echo $question[0]['explanation']?></textarea>
                                                    <span class="help-inline">
                                                        Write explanation if any.
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-1 control-label">Audio</label>
                                                <div class="col-md-11">
                                                    <input type="file" name="audio" value="" class="form-control" />
                                                    <span class="help-inline">
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Voice of Question (<?php echo $question[0]['question_id']?>)</label>
                                                <div class="col-md-9">
                                                    <?php
                                                        if($question[0]['audio']=='')
														{
															if($_SERVER['REMOTE_ADDR']=='::1')
															{
																$base_path = 'D:/server/htdocs/shahbaz/recording/';
															}
															else
															{
																$base_path = '/home/shahbazc/public_html/lahore-campus/recording/';
															}
														
                                                        if(file_exists($base_path.$question[0]['question_id'].'.ogg')):
                                                        
                                                    ?>
                                                    <audio controls id="audio" src="<?php echo base_url();?>recording/<?php echo $question[0]['question_id']?>.ogg"></audio>
                                                    <?php
                                                        endif;
														}
														else
														{
															if($_SERVER['REMOTE_ADDR']=='::1')
															{
																$base_path = 'D:/server/htdocs/shahbaz/recording/';
															}
															else
															{
																$base_path = '/home/shahbazc/public_html/lahore-campus/recording/';
															}
													
													?>
                                                    		<audio controls id="audio" src="<?php echo base_url();?>recording/<?php echo $question[0]['audio']?>"></audio>
                                                    <?php
                                                    
														}
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
                                            <input type="hidden" name="last_edit" value="<?php echo $this->session->userdata('name');?>" />
                                            <input type="hidden" name="old_audio" value="<?php echo $question[0]['audio'];?>" />
                                            <input type="hidden" name="status" value="0" />
											<button type="submit" class="btn green">Update Question</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->