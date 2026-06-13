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
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i>All MCQs Questions
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
                                    Chapter
                                </th>
                                <th>
                                    Topic
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
                                    <?php echo $question['chapter_name']?>
                                </td>
                                <td>
                                    <?php echo $question['topic_name']?>
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
									<button class="<?php if ($question['test_status'] == 1) echo "btn green"; else echo "btn red" ?>" style="width: 60px; height: 60px;" onclick="updateStatus(<?php echo $question['question_id'];?>)" id="status-<?php echo $question['question_id'];?>" data-status ="<?php echo $question['test_status'];?>"><?php if ($question['test_status'] == 1) echo "active"; else echo "inactive" ?></button>
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
								<i class="fa fa-list"></i>All Short Questions
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
                                    Chapter
                                </th>
                                <th>
                                    Topic
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
                                    <?php echo $question['chapter_name']?>
                                </td>
                                <td>
                                    <?php echo $question['topic_name']?>
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
                                    <button class="<?php if ($question['test_status'] == 1) echo "btn green"; else echo "btn red" ?>" style="width: 60px; height: 60px;" onclick="updateStatus(<?php echo $question['question_id'];?>)" id="status-<?php echo $question['question_id'];?>" data-status ="<?php echo $question['test_status'];?>"><?php if ($question['test_status'] == 1) echo "active"; else echo "inactive" ?></button>
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
								<i class="fa fa-list"></i>All Long Questions
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
                                    Chapter
                                </th>
                                <th>
                                    Topic
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
                                    <?php echo $question['chapter_name']?>
                                </td>
                                <td>
                                    <?php echo $question['topic_name']?>
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
                                    <button class="<?php if ($question['test_status'] == 1) echo "btn green"; else echo "btn red" ?>" style="width: 60px; height: 60px;" onclick="updateStatus(<?php echo $question['question_id'];?>)" id="status-<?php echo $question['question_id'];?>" data-status ="<?php echo $question['test_status'];?>"><?php if ($question['test_status'] == 1) echo "active"; else echo "inactive" ?></button>
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
								<i class="fa fa-list"></i>All Word Meanings
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
                                    Chapter
                                </th>
                                <th>
                                    Topic
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
                                    <?php echo $question['chapter_name']?>
                                </td>
                                <td>
                                    <?php echo $question['topic_name']?>
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
                                    <button class="<?php if ($question['test_status'] == 1) echo "btn green"; else echo "btn red" ?>" style="width: 60px; height: 60px;" onclick="updateStatus(<?php echo $question['question_id'];?>)" id="status-<?php echo $question['question_id'];?>" data-status ="<?php echo $question['test_status'];?>"><?php if ($question['test_status'] == 1) echo "active"; else echo "inactive" ?></button>
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


    function updateStatus(id)
    {

        var stat = $("#status-"+id).attr("data-status") ;
        if (stat == 0)
        {
            stat = "1";
            $("#status-"+id).removeClass('btn red');
            $("#status-"+id).toggleClass('btn green');
        }
        else
        {
            stat = "0";
            $("#status-"+id).removeClass('btn green');
            $("#status-"+id).toggleClass('btn red');
        }

        $.ajax({
            url: "<?php echo site_url();?>/test_engine/update_question_status",
            type:'POST',
            data: {
                id: id,
                status: stat,
            },
            success: function(data) {

                if (stat == 1)
                {
                    $("#status-"+id).html('active');
                }
                else
                {
                    $("#status-"+id).html('inactive');
                }

                $("#status-"+id).attr("data-status",stat); //setter
            },
            error:function(data) {

            }
        });
    }
</script>