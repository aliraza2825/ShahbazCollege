<html>
	<head>
    	<title>Student Card</title>
        <style>
        	*{margin:0; padding:0}
        </style>
    </head>
    <body>
    	<div style="width:187px; position:relative; height:324px; background-color:#FFF; border-radius:10px; border-right:10px solid #03F; border-left:10px solid #03F; background-image:url('<?php echo base_url();?>uploads/<?php echo $campus[0]['logo'];?>');">
            <p style="text-align:center">
            	<img src="<?php echo base_url();?>uploads/<?php echo $campus[0]['logo'];?>" alt="" width="60" style="margin-top:10px;" />
            </p>
            <p style="text-align:center; font-size:1.3em; font-weight:500;"><?php echo $campus[0]['campus_name']?></p>
            <!--<p style="text-align:center; font-size:12px;">COLLEGE OF PHARMACY</p>-->
            <p style="text-align:center">	
            	<?php
                	if(@$photo[0]['image']!='' && @$photo[0]['online_image']==''):
				?>
            	<img src="<?php echo base_url();?>/uploads/<?php echo @$photo[0]['image'];?>" alt="" width="100" height="100" style="" />
                <?php
                	endif;
				?>
                <?php
                	if(@$photo[0]['image']!='' && @$photo[0]['online_image']!=''):
				?>
            	<img src="<?php echo @$photo[0]['online_image'];?>" alt="" width="100" height="100" style="" />
                <?php
                	endif;
				?>
            </p>
            <p style="text-align:center; font-size:12px;"><strong>Roll No : <?php echo $students[0]['roll_no'];?></strong></p>
            <p style="text-align:center; font-size:1.0em;"><strong><?php echo $students[0]['first_name'].' '.$students[0]['last_name'] ;?></strong></p>
            <p style="text-align:center; font-size:12px;"><?php echo $this->db->get_where('courses', array('course_id'=>$students[0]['course_id']))->row()->course_name;?></p>
            <p style="text-align:center;">
            	<img src="<?php echo base_url();?>barcode.php?text=<?php echo $students[0]['roll_no'];?>&print=false" />
            </p>
        </div>
    </body>
</html>