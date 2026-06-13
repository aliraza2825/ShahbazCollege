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
	</head>
	<body>
		
		<?php
			foreach($collegepaper as $paper):
		?>
		<div class="container">
			<h1 class="text-center"><?php echo $this->db->get_where('campuses',array('campus_id'=>$paper['campus_id']))->row()->campus_name;?></h1>
			<div class="left paper_detail">
				<p>Student Name ___________________</p>
				<p>Student Roll No. __________________</p>
			</div>
			<div class="left paper_detail">
				<p class="text-center" style="font-size:14px;"><strong>Subject : 
				<?php
					$arr=explode(',',$paper['subject_id']);
				$sess= $this->db->where_in('course_subject_id',$arr)->get('course_subjects')->result_array();?>
				<?php 
					$this->session->userdata('college_paper_session');

					foreach($sess as $s)
					{
						echo '('.$s['subject_name'].')';
				
					}
				
				?>
				</strong></p>
				<p class="text-center">Date : <?php echo date('F d, Y',strtotime($paper['date']));?></p>
			</div>
			<div class="left paper_detail text-right">
				<p>Total Marks : &nbsp;&nbsp; <?php echo $paper['total_marks'];?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
				<p>Obtain Marks : __________________</p>
			</div>
			<div style="clear:both;"></div>
			<br />
			<?php
				$mcqs = explode(',',$paper['mcqs']);
						if(count($mcqs)>0)
						{
							echo '<h3>MCQS (Marks : '.$paper['mcqs_marks'].')</h3>';
						}
						$i=1;
						foreach($mcqs as $key=>$mcq):
						$mcq = $this->db->get_where('questions',array('question_id'=>$mcq))->result_array();
						?>
						<p><strong><?php echo $i.'. '.strip_tags($mcq[0]['question']);?></strong></p>
						<div class="left option"><p>A. <?php echo strip_tags($mcq[0]['option_1']);?></p></div>
						<div class="left option"><p>B. <?php echo strip_tags($mcq[0]['option_2']);?></p></div>
						<div class="left option"><p>C. <?php echo strip_tags($mcq[0]['option_3']);?></p></div>
						<div class="left option"><p>D. <?php echo strip_tags($mcq[0]['option_4']);?></p></div>
			<?php
				$i++;
				
				endforeach;
			?>
			<br /><br />
			<?php
				$short_questions = explode(',',$paper['short_questions']);
				if(count($short_questions)>0)
				{
					echo '<br />';
					echo '<h3>Short Questions (Marks : '.$paper['short_questions_marks'].')</h3>';
				}
				$i=1;
				foreach($short_questions as $short_question):
				$short_question = $this->db->get_where('questions',array('question_id'=>$short_question))->result_array();
			?>
			<p><strong><?php echo 'Question '.$i.'. </strong>'.strip_tags($short_question[0]['question']);?></p>
				
			<?php
				$i++;
				endforeach;
			?>
			<?php
				$practicals = explode(',',$paper['practicals']);
				if(count($practicals)>0)
				{
					echo '<br />';
					echo '<h3>Practical (Marks : '.$paper['practical_marks'].')</h3>';
				}
				$i=1;
				foreach($practicals as $practical):
				$practical = $this->db->get_where('practicals',array('practical_id'=>$practical))->result_array();
			?>
			<p><strong>Practical # <?php echo $i;?> : <?php echo $practical[0]['practical_name'];?></strong></p>
			<?php
				$i++;
			
				endforeach;
				
				endforeach;
			?>
		</div>
	</body>
</html>