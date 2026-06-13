<?php
$myAccess = checkUserAccess();
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
								<i class="fa fa-plus"></i> Add Topic Data (<?php echo $topics[0]['topic_name'];?>)
							</div>
                            <div class="tools">
                                <a href="" class="collapse"></a>
                            </div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/test_engine/insert_topic_data/<?php echo $this->uri->segment(3);?>" enctype="multipart/form-data">
								<div class="form-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <div class="col-md-12">
                                                    <textarea class="ckeditor form-control" rows="10" name="data" required><?php echo @$topicdata[0]['data'];?></textarea>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <div class="col-md-12">
                                                    <input type="test" name="video" value="<?php echo @$topicdata[0]['video'];?>" class="form-control" />
                                                    <span class="help-inline">
                                                    	Upload Video Here...
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-offset-3 col-md-9">
                                            <input type="hidden" name="add_by" value="<?php echo $this->session->userdata('name');?>" />
                                            <input type="hidden" name="last_edit" value="<?php echo $this->session->userdata('name');?>" />
                                            <input type="hidden" name="status" value="1" />
                                            <input type="hidden" name="old_video" value="<?php echo @$topicdata[0]['video'];?>" />
                                            <button type="submit" class="btn green submit_button">Add Topic Data</button>
                                        </div>
                                    </div>
                                </div>
							</form>
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
								<i class="fa fa-plus"></i> Import Questions (<?php echo $topics[0]['topic_name'];?>)
							</div>
                            <div class="tools">
                                <a href="" class="collapse"></a>
                            </div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/test_engine/import/<?php echo $this->uri->segment(3);?>" enctype="multipart/form-data">
								<div class="form-body">
                                    <div class="form-group">
										<label class="col-md-3 control-label">Type <span class="required">*</span></label>
										<div class="col-md-9">
											<select class="form-control input-large" name="type">
												<option value="mcqs">MCQs</option>
												<option value="short-question">Short Question</option>
												<option value="long-question">Long Question</option>
												<option value="word-meaning">Word Meaning</option>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-3 control-label">Excel (.csv) </label>
										<div class="col-md-9">
											<input type="file" name="csv"  value="" required />
											<span class="help-inline"></span>
										</div>
									</div>
									<div class="form-group">
    <label class="col-md-3 control-label">Sample Files</label>
    <div class="col-md-9">

        <a href="<?php echo base_url('uploads/mcqs_import_sample.csv'); ?>"
           target="_blank"
           class="btn blue btn-sm">
            <i class="fa fa-download"></i> MCQs Sample
        </a>

        <a href="<?php echo base_url('uploads/short_long_question_import_sample.csv'); ?>"
           target="_blank"
           class="btn green btn-sm">
            <i class="fa fa-download"></i> Short/Long Sample
        </a>

        <a href="<?php echo base_url('uploads/word_meaning_import_sample.csv'); ?>"
           target="_blank"
           class="btn purple btn-sm">
            <i class="fa fa-download"></i> Word Meaning Sample
        </a>

    </div>
</div>
                                </div>
                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-offset-3 col-md-9">
                                            <input type="hidden" name="add_by" value="<?php echo $this->session->userdata('name');?>" />
                                            <input type="hidden" name="last_edit" value="<?php echo $this->session->userdata('name');?>" />
                                            <input type="hidden" name="status" value="1" />
                                            <button type="submit" class="btn green submit_button">Import</button>
                                        </div>
                                    </div>
                                </div>
							</form>
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
								<i class="fa fa-plus"></i> Add Question (<?php echo $topics[0]['topic_name'];?>)
							</div>
                            <div class="tools">
                                <a href="" class="collapse"></a>
                            </div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/test_engine/insert_question/<?php echo $this->uri->segment(3);?>" enctype="multipart/form-data">
								<div class="form-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Type <span class="required">*</span></label>
                                                <div class="col-md-9 radio-list">
                                                    <select class="form-control question_type" name="type">
                                                        <option value="radio">Radio (MCQs)</option>
                                                        <option value="short-question">Short Question</option>
														<option value="long-question">Long Question</option>
                                                    </select>
                                                    <!--QUESTION DIFFICULTY-->
                                                    <label class="radio-inline">
                                                    <input type="radio" name="difficulty" id="optionsRadios1" value="easy" checked> Easy </label>
                                                    <label class="radio-inline">
                                                    <input type="radio" name="difficulty" id="optionsRadios2" value="medium"> Medium </label>
                                                    <label class="radio-inline">
                                                    <input type="radio" name="difficulty" id="optionsRadios3" value="hard"> Hard </label>
                                                    <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-1 control-label">Question <span class="required">*</span></label>
                                                <div class="col-md-11">
                                                    <textarea class="wysihtml5 form-control" rows="4" name="question" required></textarea>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 option">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Option A <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <textarea class="form-control textarea" rows="1" name="option_1" required></textarea>
                                                    <span class="help-inline">
                                                        <label class="checkbox-inline"><input type="checkbox" id="inlineCheckbox1" name="answer[]" value="A"/> Correct Option </label>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 option">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Option B <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <textarea class="form-control textarea" rows="1" name="option_2" required></textarea>
                                                    <span class="help-inline">
                                                        <label class="checkbox-inline"><input type="checkbox" id="inlineCheckbox1" name="answer[]" value="B"/> Correct Option </label>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 option">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Option C <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <textarea class="form-control textarea" rows="1" name="option_3" required></textarea>
                                                    <span class="help-inline">
                                                        <label class="checkbox-inline"><input type="checkbox" id="inlineCheckbox1" name="answer[]" value="C"/> Correct Option </label>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 option">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Option D <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <textarea class="form-control textarea" rows="1" name="option_4" required></textarea>
                                                    <span class="help-inline">
                                                        <label class="checkbox-inline"><input type="checkbox" id="inlineCheckbox1" name="answer[]" value="D"/> Correct Option </label>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-1 control-label">Explanation</label>
                                                <div class="col-md-11">
                                                    <textarea class="wysihtml5 form-control" rows="4" name="explanation"></textarea>
                                                    <span class="help-inline">
                                                        Write explanation if any.
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-1 control-label">Audio <span class="required">*</span></label>
                                                <div class="col-md-11">
                                                    <input type="file" name="audio" class="form-control" required/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-offset-3 col-md-9">
                                            <input type="hidden" name="recording" class="recording" value="" />
                                            <input type="hidden" name="add_by" value="<?php echo $this->session->userdata('name');?>" />
                                            <input type="hidden" name="last_edit" value="<?php echo $this->session->userdata('name');?>" />
                                            <input type="hidden" name="status" value="0" />
                                            <button type="submit" class="btn green submit_button">Add Question</button>
                                        </div>
                                    </div>
                                </div>
							</form>
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
                                <i class="fa fa-plus"></i> Add Multiple Short Questions (<?php echo $topics[0]['topic_name'];?>)
                            </div>
                            <div class="tools">
                                <a href="" class="collapse"></a>
                            </div>
                        </div>
                        <div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/test_engine/insert_questions/<?php echo $this->uri->segment(3);?>" enctype="multipart/form-data">
                                <div class="form-body">
                                    <div class="row question_area_container">
                                        <div class="col-md-12 question_area">
                                            <div class="col-md-12" style="background-color:#FFF;padding:10px 0;">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="col-md-2 control-label">Difficulty <span class="required">*</span></label>
                                                        <div class="col-md-10">
                                                            <select class="form-control" name="difficulty[]" required>
                                                                <option value="">Select Option</option>
                                                                <option value="easy">Easy</option>
                                                                <option value="medium">Medium</option>
                                                                <option value="hard">Hard</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label class="col-md-1 control-label">Question <span class="required">*</span></label>
                                                        <div class="col-md-11">
                                                            <textarea class="wysihtml5 form-control" rows="4" name="question[]" required></textarea>
                                                            <span class="help-inline"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label class="col-md-1 control-label">Explanation</label>
                                                        <div class="col-md-11">
                                                            <textarea class="wysihtml5 form-control" rows="4" name="explanation[]" required></textarea>
                                                            <span class="help-inline">
                                                        Write explanation if any.
                                                    </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label class="col-md-1 control-label">Audio <span class="required">*</span></label>
                                                        <div class="col-md-11">
                                                            <input type="file" name="audio[]" class="form-control" required/>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <hr />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button type="button" class="btn green add_question">Add More</button>
                                            <button type="button" class="btn red remove_question">Remove</button>
                                            <br /><br />
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-offset-3 col-md-9">
                                            <input type="hidden" name="recording" class="recording" value="" />
                                            <input type="hidden" name="add_by" value="<?php echo $this->session->userdata('name');?>" />
                                            <input type="hidden" name="last_edit" value="<?php echo $this->session->userdata('name');?>" />
                                            <input type="hidden" name="status" value="0" />
                                            <button type="submit" class="btn green submit_button">Add Question</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
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
								<i class="fa fa-plus"></i> Add Word Meanings (<?php echo $topics[0]['topic_name'];?>)
							</div>
                            <div class="tools">
                                <a href="" class="collapse"></a>
                            </div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/test_engine/insert_wordmeaning/<?php echo $this->uri->segment(3);?>">
								<div class="form-body">
                                    <div class="row">
                                        <div class="col-md-12 word-meaning-container">
                                            <div class="line">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <div class="col-md-12">
                                                            <input type="text" class="form-control input-inline input-large" name="word_1" placeholder="Enter Word" value="" required>
                                                            <span class="help-inline"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <div class="col-md-12">
                                                            <input type="text" class="form-control input-inline input-large" name="meaning_english_1" placeholder="Enter Meaning in English" value="" required>
                                                            <span class="help-inline"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <div class="col-md-12">
                                                            <input type="text" class="form-control input-inline input-large" name="meaning_urdu_1" placeholder="Enter Meaning in Urdu" value="" required>
                                                            <span class="help-inline"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <button type="button" class="btn green add_word">ADD WORD</button>
                                            <button type="button" class="btn red remove_word">Remove WORD</button>
                                            <br /><br />
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-offset-3 col-md-9">
                                            <input type="hidden" name="add_by" value="<?php echo $this->session->userdata('name');?>" />
                                            <input type="hidden" name="last_edit" value="<?php echo $this->session->userdata('name');?>" />
                                            <input type="hidden" name="status" value="0" />
                                            <button type="submit" class="btn green submit_button">Add</button>
                                        </div>
                                    </div>
                                </div>
							</form>
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
                                <i class="fa fa-plus"></i> Add Videos (<?php echo $topics[0]['topic_name'];?>)
                            </div>
                            <div class="tools">
                                <a href="" class="collapse"></a>
                            </div>
                        </div>
                        <div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/test_engine/insert_videos/<?php echo $this->uri->segment(3);?>">
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-md-12 add_video-container">
                                            <div class="line">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <div class="col-md-12">
                                                            <input type="text" class="form-control input-inline input-large" name="video_1" placeholder="Enter Title" value="" required>
                                                            <span class="help-inline"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <div class="col-md-12">
                                                            <input type="text" class="form-control input-inline input-large" name="video_link_1" placeholder="Enter Video Link" value="" required>
                                                            <span class="help-inline"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <button type="button" class="btn green add_video">ADD Video</button>
                                            <button type="button" class="btn red remove_video">Remove Video</button>
                                            <br /><br />
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-offset-3 col-md-9">
                                            <input type="hidden" name="add_by" value="<?php echo $this->session->userdata('name');?>" />
                                            <input type="hidden" name="status" value="0" />
                                            <button type="submit" class="btn green submit_button">Add</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
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
								<i class="fa fa-list"></i>All MCQs Questions of topic (<?php echo $topics[0]['topic_name'];?>)
							</div>
                            <div class="tools">
                                <a href="" class="collapse"></a>
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
									 Question ID
								</th>
                                <th>
									 Type
								</th>
                                <th>
									 Question
								</th>
                                <th>
                                	Option A
                                </th>
                                <th>
                                	Option B
                                </th>
                                <th>
                                	Option C
                                </th>
                                <th>
                                	Option D
                                </th>
                                <th>
                                    Audio
                                </th>
                                <th>
									 Add By
								</th>
                                <th>
									 Last Edit
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($questions as $question):
								$answers = explode(',',$question['answer']);
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $question['question_id']?>
								</td>
                                <td>
									<?php echo $question['type']?>
								</td>
                                <td>
									 <?php echo $question['question']?>
								</td>
                                <td class="<?php if(in_array('A', $answers)){echo 'alert alert-success';}?>">
									 <?php echo $question['option_1']?>
								</td>
                                <td class="<?php if(in_array('B', $answers)){echo 'alert alert-success';}?>">
									 <?php echo $question['option_2']?>
								</td>
                                <td class="<?php if(in_array('C', $answers)){echo 'alert alert-success';}?>">
									 <?php echo $question['option_3']?>
								</td>
                                <td class="<?php if(in_array('D', $answers)){echo 'alert alert-success';}?>">
									 <?php echo $question['option_4']?>
								</td>
                                <td>
                                    <?php
                                        if($question['audio']=='')
                                        {
                                            if($_SERVER['REMOTE_ADDR']=='::1')
                                            {
                                                $base_path = 'D:/server/htdocs/shahbaz/recording/';
                                            }
                                            else
                                            {
                                                $base_path = '/home/shahbazc/public_html/lahore-campus/recording/';
                                            }

                                            if(file_exists($base_path.$question['question_id'].'.ogg')):

                                                ?>
                                                <audio controls id="audio" src="<?php echo base_url();?>recording/<?php echo $question['question_id']?>.ogg"></audio>
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
                                            <audio controls id="audio" src="<?php echo base_url();?>recording/<?php echo $question['audio']?>"></audio>
                                            <?php

                                        }
                                    ?>
                                </td>
                                <td>
									<?php echo $question['add_by']?>
								</td>
                                <td>
									<?php echo $question['last_edit']?>
								</td>
								<td>
									<?php
                                    	if(@$myAccess[0]['test_engine_edit_question']==1 || $this->session->userdata('role')=='Admin'):
									?>
									<a class="btn blue" href="<?php echo site_url();?>/test_engine/edit_question/<?php echo $question['question_id']?>/<?php echo $this->uri->segment(3);?>"><i class="fa fa-edit"></i></a>
									<?php
										endif;
									?>
									<?php
                                    	if(@$myAccess[0]['test_engine_delete_question']==1 || $this->session->userdata('role')=='Admin'):
									?>
                                    <a class="btn red" onclick="return confirm('Are you sure you want to delete this Question?')" href="<?php echo site_url();?>/test_engine/delete_question/<?php echo $question['question_id']?>/<?php echo $this->uri->segment(3);?>"><i class="fa fa-trash"></i></a>
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
            
            <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i>All Short Questions of topic (<?php echo $topics[0]['topic_name'];?>)
							</div>
                            <div class="tools">
                                <a href="" class="collapse"></a>
                            </div>
						</div>
						<div class="portlet-body">
							<table class="table table-striped table-bordered table-hover" id="sample_3">
							<thead>
							<tr>
								<th class="hidden">
                                	 hidden
                                </th>
								<th>
									 Question ID
								</th>
                                <th>
									 Question
								</th>
                                <th>
                                	Answer
                                </th>
                                <th>
                                    Audio
                                </th>
                                <th>
									 Add By
								</th>
                                <th>
									 Last Edit
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($shortquestions as $question):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $question['question_id']?>
								</td>
                                <td>
									 <?php echo $question['question']?>
								</td>
                                <td>
									 <?php echo $question['explanation']?>
								</td>
                                <td>
                                    <?php
                                        if($question['audio']=='')
                                        {
                                            if($_SERVER['REMOTE_ADDR']=='::1')
                                            {
                                                $base_path = 'D:/server/htdocs/shahbaz/recording/';
                                            }
                                            else
                                            {
                                                $base_path = '/home/shahbazc/public_html/lahore-campus/recording/';
                                            }

                                            if(file_exists($base_path.$question['question_id'].'.ogg')):

                                                ?>
                                                <audio controls id="audio" src="<?php echo base_url();?>recording/<?php echo $question['question_id']?>.ogg"></audio>
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
                                            <audio controls id="audio" src="<?php echo base_url();?>recording/<?php echo $question['audio']?>"></audio>
                                            <?php

                                        }
                                    ?>
                                </td>
                                <td>
									<?php echo $question['add_by']?>
								</td>
                                <td>
									<?php echo $question['last_edit']?>
								</td>
								<td>
									<?php
                                    	if(@$myAccess[0]['test_engine_edit_question']==1 || $this->session->userdata('role')=='Admin'):
									?>
									<a class="btn blue" href="<?php echo site_url();?>/test_engine/edit_question/<?php echo $question['question_id']?>/<?php echo $this->uri->segment(3);?>"><i class="fa fa-edit"></i></a>
									<?php
										endif;
									?>
									<?php
                                    	if(@$myAccess[0]['test_engine_delete_question']==1 || $this->session->userdata('role')=='Admin'):
									?>
                                    <a class="btn red" onclick="return confirm('Are you sure you want to delete this Question?')" href="<?php echo site_url();?>/test_engine/delete_question/<?php echo $question['question_id']?>/<?php echo $this->uri->segment(3);?>"><i class="fa fa-trash"></i></a>
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
			
			<div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i>All Long Questions of topic (<?php echo $topics[0]['topic_name'];?>)
							</div>
                            <div class="tools">
                                <a href="" class="collapse"></a>
                            </div>
						</div>
						<div class="portlet-body">
							<table class="table table-striped table-bordered table-hover" id="sample_11">
							<thead>
							<tr>
								<th class="hidden">
                                	 hidden
                                </th>
								<th>
									 Question ID
								</th>
                                <th>
									 Question
								</th>
                                <th>
                                	Answer
                                </th>
                                <th>
                                    Audio
                                </th>
                                <th>
									 Add By
								</th>
                                <th>
									 Last Edit
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($longquestions as $question):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $question['question_id']?>
								</td>
                                <td>
									 <?php echo $question['question']?>
								</td>
                                <td>
									 <?php echo $question['explanation']?>
								</td>
                                <td>
                                    <?php
                                    if($question['audio']=='')
                                    {
                                        if($_SERVER['REMOTE_ADDR']=='::1')
                                        {
                                            $base_path = 'D:/server/htdocs/shahbaz/recording/';
                                        }
                                        else
                                        {
                                            $base_path = '/home/shahbazc/public_html/lahore-campus/recording/';
                                        }

                                        if(file_exists($base_path.$question['question_id'].'.ogg')):

                                            ?>
                                            <audio controls id="audio" src="<?php echo base_url();?>recording/<?php echo $question['question_id']?>.ogg"></audio>
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
                                        <audio controls id="audio" src="<?php echo base_url();?>recording/<?php echo $question['audio']?>"></audio>
                                        <?php

                                    }
                                    ?>
                                </td>
                                <td>
									<?php echo $question['add_by']?>
								</td>
                                <td>
									<?php echo $question['last_edit']?>
								</td>
								<td>
									<?php
                                    	if(@$myAccess[0]['test_engine_edit_question']==1 || $this->session->userdata('role')=='Admin'):
									?>
									<a class="btn blue" href="<?php echo site_url();?>/test_engine/edit_question/<?php echo $question['question_id']?>/<?php echo $this->uri->segment(3);?>"><i class="fa fa-edit"></i></a>
									<?php
										endif;
									?>
									<?php
                                    	if(@$myAccess[0]['test_engine_delete_question']==1 || $this->session->userdata('role')=='Admin'):
									?>
                                    <a class="btn red" onclick="return confirm('Are you sure you want to delete this Question?')" href="<?php echo site_url();?>/test_engine/delete_question/<?php echo $question['question_id']?>/<?php echo $this->uri->segment(3);?>"><i class="fa fa-trash"></i></a>
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
            
            <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i>All Word Meanings of topic (<?php echo $topics[0]['topic_name'];?>)
							</div>
                            <div class="tools">
                                <a href="" class="collapse"></a>
                            </div>
						</div>
						<div class="portlet-body">
							<table class="table table-striped table-bordered table-hover" id="sample_10">
							<thead>
							<tr>
								<th class="hidden">
                                	 hidden
                                </th>
                                <th>
									 ID
								</th>
                                <th>
									 Word
								</th>
                                <th>
                                	Meaning in English
                                </th>
                                <th>
                                	Meaning in Urdu
                                </th>
                                <th>
                                    Audio
                                </th>
                                <th>
									 Add By
								</th>
                                <th>
									 Last Edit
								</th>
								<th>
									 Action
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
                            	$i = 0;
								foreach($wordmeanings as $question):
							?>
                            <tr class="odd gradeX">
								<td class="hidden">
                                	 <?php echo $i;?>
                                </td>
                                <td>
									 <?php echo $question['question_id']?>
								</td>
                                <td>
									 <?php echo $question['word']?>
								</td>
                                <td>
									 <?php echo $question['meaning_english']?>
								</td>
                                <td>
									 <?php echo $question['meaning_urdu']?>
								</td>
                                <td>
                                    <?php
                                    if($question['audio']=='')
                                    {
                                        if($_SERVER['REMOTE_ADDR']=='::1')
                                        {
                                            $base_path = 'D:/server/htdocs/shahbaz/recording/';
                                        }
                                        else
                                        {
                                            $base_path = '/home/shahbazc/public_html/lahore-campus/recording/';
                                        }

                                        if(file_exists($base_path.$question['question_id'].'.ogg')):

                                            ?>
                                            <audio controls id="audio" src="<?php echo base_url();?>recording/<?php echo $question['question_id']?>.ogg"></audio>
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
                                        <audio controls id="audio" src="<?php echo base_url();?>recording/<?php echo $question['audio']?>"></audio>
                                        <?php

                                    }
                                    ?>
                                </td>
                                <td>
									<?php echo $question['add_by']?>
								</td>
                                <td>
									<?php echo $question['last_edit']?>
								</td>
								<td>
									<?php
                                    	if(@$myAccess[0]['test_engine_edit_question']==1 || $this->session->userdata('role')=='Admin'):
									?>
									<a class="btn blue" href="<?php echo site_url();?>/test_engine/edit_word_meanings/<?php echo $question['question_id']?>/<?php echo $this->uri->segment(3);?>"><i class="fa fa-edit"></i></a>
									<?php
										endif;
									?>
									<?php
                                    	if(@$myAccess[0]['test_engine_delete_question']==1 || $this->session->userdata('role')=='Admin'):
									?>
                                    <a class="btn red" onclick="return confirm('Are you sure you want to delete this Question?')" href="<?php echo site_url();?>/test_engine/delete_question/<?php echo $question['question_id']?>/<?php echo $this->uri->segment(3);?>"><i class="fa fa-trash"></i></a>
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

            <div class="row">
                <div class="col-md-12">
                    <!-- BEGIN EXAMPLE TABLE PORTLET-->
                    <div class="portlet box grey-cascade">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-list"></i>All Videos of topic (<?php echo $topics[0]['topic_name'];?>)
                            </div>
                            <div class="tools">
                                <a href="" class="collapse"></a>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover" id="sample_10">
                                <thead>
                                <tr>
                                    <th class="hidden">
                                        hidden
                                    </th>
                                    <th>
                                        ID
                                    </th>
                                    <th>
                                        Title
                                    </th>
                                    <th>
                                        Link
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
                                foreach($videos as $question):
                                    ?>
                                    <tr class="odd gradeX">
                                        <td class="hidden">
                                            <?php echo $i;?>
                                        </td>
                                        <td>
                                            <?php echo $question['id']?>
                                        </td>
                                        <td>
                                            <?php echo $question['title']?>
                                        </td>
                                        <td>
                                            <?php echo $question['file']?>
                                        </td>
                                        <td>
                                            <?php echo $question['created_by']?>
                                        </td>
                                        <td>

                                            <?php
                                            if(@$myAccess[0]['test_engine_edit_question']==1 || $this->session->userdata('role')=='Admin'):
                                                ?>
                                                <a class="btn blue" href="<?php echo site_url();?>/test_engine/edit_video/<?php echo $question['id']?>/<?php echo $this->uri->segment(3);?>"><i class="fa fa-edit"></i></a>
                                            <?php
                                            endif;
                                            ?>
                                            <?php
                                            if(@$myAccess[0]['test_engine_delete_question']==1 || $this->session->userdata('role')=='Admin'):
                                                ?>
                                                <a class="btn red" onclick="return confirm('Are you sure you want to delete this Question?')" href="<?php echo site_url();?>/test_engine/delete_video/<?php echo $question['id']?>/<?php echo $this->uri->segment(3);?>"><i class="fa fa-trash"></i></a>
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
<script>
    document.addEventListener( "DOMContentLoaded", function()
    {
        $('.add_video').click(function(){
            var lines = jQuery('.add_video-container').children('.line').length;
            var number = lines+1;
            var html = '<div class="line">' +
                            '<div class="col-md-6">' +
                                '<div class="form-group">' +
                                    '<div class="col-md-12">' +
                                        '<input type="text" class="form-control input-inline input-large" name="video_'+number+'" placeholder="Enter Title" value="" required>' +
                                        '<span class="help-inline"></span>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="col-md-6">' +
                '               <div class="form-group">' +
                '                   <div class="col-md-12">' +
                                        '<input type="text" class="form-control input-inline input-large" name="video_link_'+number+'" placeholder="Enter Video Link" value="" required>' +
                                        '<span class="help-inline"></span>' +
                                    '</div>' +
                '               </div>' +
                '            </div>' +
                       '</div>';
            jQuery('.add_video-container').append(html);
        });
        jQuery('.remove_video').click(function(){
            jQuery('.add_video-container').children('.line:last').remove();
        });

    }, false );
</script>
