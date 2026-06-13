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
										if($chat['student_id']!=0):
									?>
									<li class="in">
										<img class="avatar" alt="" src="<?php echo $student_photo;?>"/>
										<div class="message">
											<span class="arrow">
											</span>
											<a href="javascript:;" class="name">
											<?php echo $student_details[0]['first_name'].' '.$student_details[0]['last_name']?> </a>
											<span class="datetime">
											at <?php echo date('d M, Y H:m:i A',strtotime($chat['created_at']));?> </span>
											<span class="body">
											<?php echo $chat['message']?></span>
										</div>
									</li>
									<?php
										elseif($chat['user_id']!=0):
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
										endif;
									?>
									<?php
										endforeach;
									?>
								</ul>
							</div>
							<div class="chat-form">
								<form method="post" action="<?php echo site_url();?>/complaints/replyComplaint/<?php echo $this->uri->segment(3);?>">
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
						if($complaint[0]['complaint_status']==0):
					?>
					<a href="<?php echo site_url();?>/complaints/solved/<?php echo $this->uri->segment(3);?>" class="btn green"><i class="fa fa-check"></i> Mark as Solved</a>
					<?php
						endif;
					?>
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->