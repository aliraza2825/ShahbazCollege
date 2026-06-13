<?php
	error_reporting(0);
?>
<html>
	<head>
		<title>Print Paper</title>
		<style type="text/css">
			*{
				font-family:Arial, sans-serif;
				margin:0;
				padding:0;
			}
			body{
				position:relative;
			}
			/*body::before {
				background-image: url('<?php echo base_url();?>uploads/<?php echo $campus_logo;?>');
				background-size: 50%;
				background-position:50% 50%;
				background-repeat: repeat-y;
				content: "";
				display: block;
				position: absolute;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				z-index: 1;
				opacity: 0.1;
			}*/
			.container{
				width:765px;
				padding:20px;
				position:relative;
				margin:0 auto;
			}
			.text-center{
				text-align:center;
			}
			.text-right{
				text-align:right;
			}
			.left{
				float:left;
				display:inline;
			}
			.right{
				float:right;
				display:inline;
			}
			p{
				padding:5px 0;
				font-size:12px;
			}
			.option{
				width:50%;
			}
			.paper_detail{
				width:33.33%;
			}
			.paper_detail_loop{
				width:30%;
			}
			.answer{
				width:100%;
				border-bottom:1px dotted #CCC;
				min-height:25px;
			}
			.student_image{
				width:10%;
				height:80px;
			}
			.save_paper{
				margin:0 auto;
				text-align:center;
				display:block;
				background-color:#F00;
				padding:20px;
				position:relative;
				display:block;
				cursor:pointer;
			}
			.save_paper a{
				display: block;
				width: 100%;
				cursor:pointer;
				color:#FFF;
				text-decoration:none;
			}
			
		</style>
		<script src="<?php echo base_url();?>assets/global/plugins/jquery.min.js" type="text/javascript"></script>
		<?php
			$mcqs_array=array();
			foreach($mcqs as $mcq)
			{
				array_push($mcqs_array,$mcq['question_id']);
			}
			$selected_mcqs = implode(',',$mcqs_array);
			//echo $selected_mcqs;
			$short_questions_array=array();
			foreach($short_questions as $short_question)
			{
				array_push($short_questions_array,$short_question['question_id']);
			}
			$selected_short_questions = implode(',',$short_questions_array);
			//echo $selected_short_questions;
		?>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery('.save_paper').click(function(){
					var campus_id = <?php echo $campus_id;?>;
					var course_id = <?php echo $course_id;?>;
					//var class_session = <?php echo $this->session->userdata('college_paper_session');?>;
					var subject_id = <?php echo "'".$subject_id."'";?>;
					var chapter_id = <?php echo "'".$chapter_id."'";?>;
					var topic_ids = '<?php echo implode(',',$topic_ids);?>';
					var cclass = '<?php echo $class;?>';
					var total_marks = <?php echo $total_marks;?>;
					var add_by = '<?php echo $this->session->userdata('name');?>';
					var mcqs = '<?php echo $selected_mcqs;?>';
					var short_questions = '<?php echo $selected_short_questions;?>';
					var print_type = '<?php echo $print_type;?>';
					var practical_ids = '<?php echo implode(',',$this->session->userdata('college_paper_practical_id'));?>';
					var mcqs_marks = <?php echo $mcqs_marks;?>;
					var short_questions_marks = <?php echo $short_question_marks;?>;
					var practical_marks = <?php echo $practical_marks;?>;
					
					jQuery.ajax({
						type: "post",
						async: false,
						url: '<?php echo site_url();?>/collegepapers/savePaper',
						data: { 
							campus_id:campus_id,
							course_id:course_id,
							subject_id:subject_id,
							chapter_id:chapter_id,
							topic_ids:topic_ids,
							cclass:cclass,
							total_marks:total_marks,
							add_by:add_by,
							mcqs:mcqs,
							short_questions:short_questions,
							print_type:print_type,
							practical_ids:practical_ids,
							mcqs_marks:mcqs_marks,
							short_questions_marks:short_questions_marks,
							practical_marks:practical_marks
						 },						  
						success: function(data) {
							jQuery('.save_paper').hide();
							window.print();
						}			  
					});
					
				});
			});
		</script>
	</head>
	<body>
		
		<?php
			if(count($students)>0):
				foreach($students as $student):
		?>
		<div class="save_paper">
			Print &amp; Save Paper
		</div>
		<div class="container">
			<h1 class="text-center"><?php echo $campus_name;?></h1>
			<?php if(!empty($campus_logo)){ ?>

        <div class="text-center" style="margin-bottom:15px;">

            <img src="<?php echo base_url('uploads/'.$campus_logo); ?>"

                 alt="<?php echo $campus_name; ?>"

                 style="max-height:100px; max-width:200px;">

        </div>

    <?php } ?>
			<div class="left paper_detail_loop">
				<p>Student Name <?php echo $student['first_name'].' '.$student['last_name'];?></p>
				<p>Student Roll No. <?php echo $student['roll_no'];?></p>
			</div>
			<div class="left paper_detail_loop">
				<p style="font-size:14px;"><strong>Subject : <?php echo $subject_name;?> <?php 
					$sess=$this->session->userdata('college_paper_session');

					foreach($sess as $s)
					{
						echo '('.$s.')';
				
					}
				
				?></strong></p>
				<p>Date : <?php echo date('F d, Y');?></p>
			</div>
			<div class="left paper_detail_loop">
				<p>Total Marks : <?php echo $total_marks;?></p>
				<p>Obtain Marks : __________________</p>
			</div>
			<div class="left student_image">
				<?php
					$photo = @$this->db->get_where('student_documents',array('student_id'=>$student['student_id'],'type'=>'Photo'))->row()->image;
					if(@$photo):
				?>
				<img src="<?php echo base_url();?>uploads/<?php echo @$photo;?>" alt="" width="100%" height="80" />
				<?php
					endif;
				?>
			</div>
			<div style="clear:both;"></div>
			<br />
			<?php
				if(count($mcqs)>0)
				{
					echo '<h3>MCQS (Marks : '.$mcqs_marks.')</h3>';
				}
				$i=1;
				foreach($mcqs as $mcq):
			?>
			<p><strong><?php echo $i.'. '.strip_tags($mcq['question']);?></strong></p>
			<div class="left option"><p>A. <?php echo strip_tags($mcq['option_1']);?></p></div>
			<div class="left option"><p>B. <?php echo strip_tags($mcq['option_2']);?></p></div>
			<div class="left option"><p>C. <?php echo strip_tags($mcq['option_3']);?></p></div>
			<div class="left option"><p>D. <?php echo strip_tags($mcq['option_4']);?></p></div>
			<div style="clear:both;"></div>
			<?php
				$i++;
				endforeach;
			?>
			<br /><br />
			<?php
				if(count($short_questions)>0)
				{
					echo '<h3>Short Questions (Marks : '.$short_question_marks.')</h3>';
				}
				$i=1;
				foreach($short_questions as $short_question):
			?>
					<p><strong><?php echo 'Question '.$i.'. </strong>'.strip_tags($short_question['question']);?></p>
					<?php
						for($j=1;$j<=$this->session->userdata('college_paper_short_question_lines');$j++):
					?>
					<div class="answer"></div>
					<?php
						endfor;
					?>
			<?php
				$i++;
				endforeach;
			?>
		</div>
		<?php
			endforeach;
			else:
		?>
		<div class="save_paper">
			Print &amp; Save Paper
		</div>
		<div class="container">
			<h1 class="text-center"><?php echo $campus_name;?></h1>
			<?php if(!empty($campus_logo)){ ?>
                <div class="text-center" style="margin-bottom:15px;">
        
                    <img src="<?php echo base_url('uploads/'.$campus_logo); ?>"
        
                         alt="<?php echo $campus_name; ?>"
        
                         style="max-height:100px; max-width:200px;">
        
                </div>
        
            <?php } ?>
			<div class="left paper_detail">
				<p>Student Name <?php if(count($students)>0){echo $students[0]['first_name'].' '.$students[0]['last_name'];}else{?>___________________<?php }?></p>
				<p>Student Roll No. __________________</p>
			</div>
			<div class="left paper_detail">
				<p class="text-center" style="font-size:14px;"><strong>Subject : <?php echo $subject_name;?> <?php 
					$sess=$this->session->userdata('college_paper_session');

					foreach($sess as $s)
					{
						echo '('.$s.')';
				
					}
				
				?></strong></p>
				<p class="text-center">Date : <?php echo date('F d, Y');?></p>
			</div>
			<div class="left paper_detail text-right">
				<p>Total Marks : &nbsp;&nbsp; <?php echo $total_marks;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
				<p>Obtain Marks : __________________</p>
			</div>
			<div style="clear:both;"></div>
			<br />
			<?php
				if(count($mcqs)>0)
				{
					echo '<h3>MCQS (Marks : '.$mcqs_marks.')</h3>';
				}
				$i=1;
				foreach($mcqs as $mcq):
			?>
			<p><strong><?php echo $i.'. '.strip_tags($mcq['question']);?></strong></p>
			<div class="left option"><p>A. <?php echo strip_tags($mcq['option_1']);?></p></div>
			<div class="left option"><p>B. <?php echo strip_tags($mcq['option_2']);?></p></div>
			<div class="left option"><p>C. <?php echo strip_tags($mcq['option_3']);?></p></div>
			<div class="left option"><p>D. <?php echo strip_tags($mcq['option_4']);?></p></div>
			<?php
				$i++;
				endforeach;
			?>
			<br /><br />
			<?php
				if(count($short_questions)>0)
				{
					echo '<br />';
					echo '<h3>Short Questions (Marks : '.$short_question_marks.')</h3>';
				}
				$i=1;
				foreach($short_questions as $short_question):
			?>
			<p><strong><?php echo 'Question '.$i.'. </strong>'.strip_tags($short_question['question']);?></p>
			<?php
				for($j=1;$j<=$this->session->userdata('college_paper_short_question_lines');$j++):
			?>
			<div class="answer"></div>
			<?php
				endfor;
			?>
			<?php
				$i++;
				endforeach;
			?>
			<?php
				if(count($practicals)>0)
				{
					echo '<br />';
					echo '<h3>Practical (Marks : '.$practical_marks.')</h3>';
				}
				$i=1;
				foreach($practicals as $practical):
			?>
			<p><strong>Practical # <?php echo $i;?> : <?php echo $practical['practical_name'];?></strong></p>
			<?php
				for($j=1;$j<=$this->session->userdata('college_paper_practical_lines');$j++):
			?>
			<div class="answer"></div>
			<?php
				endfor;
			?>
			<?php
				$i++;
				endforeach;
			?>
		</div>
		<?php
			endif;
		?>
	</body>
</html>