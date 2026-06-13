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
				<?php
					if($chats[0]['question_id']!=0):
						$question = $this->db->get_where('questions',array('question_id'=>$chats[0]['question_id']))->result_array();
				?>
				<div class="col-md-12 col-sm-12">
					<h2>Questions</h2>
					<p><?php echo '<p><strong>'.strip_tags($question[0]['question']).'</strong></p>';?></p>
					<?php
						if($question[0]['option_1']!='')
						{
							echo '<p>A. '.strip_tags($question[0]['option_1']).'</p>';
						}
						if($question[0]['option_2']!='')
						{
							echo '<p>B. '.strip_tags($question[0]['option_2']).'</p>';
						}
						if($question[0]['option_3']!='')
						{
							echo '<p>C. '.strip_tags($question[0]['option_3']).'</p>';
						}
						if($question[0]['option_4']!='')
						{
							echo '<p>D. '.strip_tags($question[0]['option_4']).'</p>';
						}
						if($question[0]['answer']!='')
						{
							echo '<p><strong>Answer: </strong>'.strip_tags($question[0]['answer']).'</p>';
						}
					?>
				</div>
				<?php
					endif;
				?>
				<div class="col-md-12 col-sm-12">
					<!-- BEGIN PORTLET-->
					<div class="portlet light ">
						<div class="portlet-title">
							<div class="caption">
								<i class="icon-bubble font-red-sunglo"></i>
								<span class="caption-subject font-red-sunglo bold uppercase">Chats</span>
							</div>
							
						</div>
						<div class="portlet-body" id="chats">
							<div class="scroller" style="height: 341px;" data-always-visible="1" data-rail-visible1="1">
								<ul class="chats">
									<?php
										foreach($chats as $chat):
									?>
									<?php
										if($chat['user_id']!=0):
									?>
									<li class="out">
										<img class="avatar" alt="" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTdcJKRRGAiEryrgkD6emwh20vPm8-8PxdMiRH1hyk&s"/>
										<div class="message">
											<span class="arrow">
											</span>
											<a href="javascript:;" class="name">
											<?php echo $this->db->get_where('users',array('user_id'=>$chat['user_id']))->row()->first_name;?> </a>
											<span class="datetime">
											at <?php echo date('d M, Y H:m:i A',strtotime($chat['created_at']));?> </span>
											<span class="body">
											<?php echo $chat['message']?> </span>
										</div>
									</li>
									<?php
										else:
									?>
									<li class="in">
										<img class="avatar" alt="" src="<?php echo $student_photo;?>"/>
										<div class="message">
											<span class="arrow">
											</span>
											<?php if(count($student_details)>0):?>
											<a href="javascript:;" class="name"><?php echo $student_details[0]['first_name'].' '.$student_details[0]['last_name']?> </a>
											<?php else:?>
												<a href="javascript:;" class="name">Guest User</a>
											<?php endif;?>
											<span class="datetime">
											at <?php echo date('d M, Y H:m:i A',strtotime($chat['created_at']));?> </span>
											<span class="body">
											<?php echo $chat['message']?></span>
										</div>
									</li>
									<?php
										endif;
									?>
									<?php
										endforeach;
									?>
								</ul>
							</div>
							<div class="chat-form">
								<form method="post" action="<?php echo site_url();?>/chats/replyChat/<?php echo $this->uri->segment(3);?>">
								<div class="input-cont">
									<input class="form-control" type="text" name="message" placeholder="Type a message here..."/>
								</div>
								<div class="btn-cont">
									<span class="arrow">
									</span>
									<button type="submit" class="btn blue icn-only">
									<i class="fa fa-check icon-white"></i>
									</button>
								</div>
								</form>
							</div>
						</div>
					</div>
					<!-- END PORTLET-->
				</div>
				<div>
					<?php
						if($chats[0]['chat_status']==0):
					?>
					<a href="<?php echo site_url();?>/chats/solved/<?php echo $this->uri->segment(3);?>" class="btn green"><i class="fa fa-check"></i> Close This Chat</a>
					<?php
						endif;
					?>
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->