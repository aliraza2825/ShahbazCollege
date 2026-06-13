<?php
	error_reporting(0);
?>
<html>
	<head>
		<title>Solve Assignment</title>
		<style type="text/css">
			*{
				font-family:Arial, sans-serif;
				margin:0;
				padding:0;
			}
			body{
				position:relative;
			}
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
	</head>
	<body>
		
		<?php
			foreach($assignments as $assignment):
		?>
		<div class="container">
			<h1 class="text-center"><?php echo $this->db->get_where('campuses',array('campus_id'=>$paper['campus_id']))->row()->campus_name;?></h1>
			<div class="left paper_detail">
                <p class="text-left">Date : <?php echo date('F d, Y',strtotime($assignment['date']));?></p>
			</div>
			<div class="left paper_detail">
				<p class="text-center" style="font-size:14px;"><strong>Subject : <?php echo $this->db->get_where('course_subjects',array('course_subject_id'=>$assignment['subject_id']))->row()->subject_name;?></strong></p>
			</div>
			<div class="left paper_detail text-right">
				<p>Total Marks : &nbsp;&nbsp; <?php echo $assignment['total_marks'];?></p>
			</div>
			<div style="clear:both;"></div>
			<br />
			<?php
				$mcqs = explode(',',$assignment['mcqs']);
				if(count($mcqs)>0)
				{
					echo '<h3>MCQS (Marks : '.$assignment['mcqs_marks'].')</h3>';
				}
				$i=1;
				foreach($mcqs as $mcq):
				$mcq = $this->db->get_where('questions',array('question_id'=>$mcq))->result_array();
			?>
			<p><strong><?php echo $i.'. '.strip_tags($mcq[0]['question']);?></strong></p>
			<div class="left option"><p>A. <?php echo strip_tags($mcq[0]['option_1']);?></p></div>
			<div class="left option"><p>B. <?php echo strip_tags($mcq[0]['option_2']);?></p></div>
			<div class="left option"><p>C. <?php echo strip_tags($mcq[0]['option_3']);?></p></div>
			<div class="left option"><p>D. <?php echo strip_tags($mcq[0]['option_4']);?></p></div>
			<div class="left option"><p style="color:#F00;"><strong>Answer : <?php echo strip_tags($mcq[0]['answer']);?></strong></p></div>
			<div style="clear:both;"></div>
			<?php
				$i++;
				endforeach;
			?>
			<br /><br />
			<?php
				$short_questions = explode(',',$assignment['short_questions']);
				if(count($short_questions)>0)
				{
					echo '<br />';
					echo '<h3>Short Questions (Marks : '.$assignment['short_questions_marks'].')</h3>';
				}
				$i=1;
				foreach($short_questions as $short_question):
				$short_question = $this->db->get_where('questions',array('question_id'=>$short_question))->result_array();
			?>
			<p style="font-size:16px;"><strong><?php echo 'Question '.$i.' '.strip_tags($short_question[0]['question']);?></strong></p>
			<div class="answer"><p><?php echo strip_tags($short_question[0]['explanation']);?></p></div>
			<?php
				$i++;
				endforeach;
			?>
			<?php
				$practicals = explode(',',$assignment['practicals']);
				if(count($practicals)>0)
				{
					echo '<br />';
					echo '<h3>Practical (Marks : '.$assignment['practical_marks'].')</h3>';
				}
				$i=1;
				foreach($practicals as $practical):
				$practical = $this->db->get_where('practicals',array('practical_id'=>$practical))->result_array();
			?>
			<p><strong>Practical # <?php echo $i;?> : <?php echo $practical[0]['practical_name'];?></strong></p>
			<div>
				<?php echo $practical[0]['data'];?>
			</div>
			<?php
				$i++;
				endforeach;
				endforeach;
			?>
		</div>
	</body>
</html>