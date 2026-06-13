<?php
include('qrcode/qrlib.php');
?>
<html>
	<head>
    	<title>Student Card</title>
        <style>
        	*{margin:0; padding:0}
        </style>
    </head>
    <body>
    <?php
    QRcode::png(current_url(), 'qr/'.$students[0]['student_id'].'.png');
    ?>
    	<div style="float:left;width:187px; position:relative; height:324px; background-color:#FFF; border-radius:10px; border-right:10px solid #03F; border-left:10px solid #03F; background-image:url('');">
            <p style="text-align:center">
            	<img src="<?php echo base_url();?>uploads/<?php echo $campus[0]['logo'];?>" alt="" width="60" style="margin-top:10px;" />
            </p>
            <p style="text-align:center; font-size:1.3em; font-weight:500;"><?php echo $campus[0]['campus_name']?></p>
            <!--<p style="text-align:center; font-size:12px;">COLLEGE OF PHARMACY</p>-->
            <p style="text-align:center">	
            	
            	<?php
                    if(@$photo[0]['online_image']==''):
                ?>
                <img src="<?php echo base_url();?>/uploads/<?php echo @$photo[0]['image'];?>" alt="" width="100" height="100" style="" />
                <?php
                    else:
                ?>
                <img src="<?php echo str_replace($bucket_address,$cloudfront_address,@$photo[0]['online_image']);?>" alt="" width="100" height="100" style="" />
                <?php
                    endif;
                ?>
            </p>
            <p style="text-align:center; font-size:12px;"><strong>Roll No : <?php echo $students[0]['roll_no'];?></strong></p>
            <p style="text-align:center; font-size:1.0em;"><strong><?php echo $students[0]['first_name'].' '.$students[0]['last_name'] ;?></strong></p>
            <p style="text-align:center; font-size:12px;"><?php echo $this->db->get_where('courses', array('course_id'=>$students[0]['course_id']))->row()->course_name;?></p>
            <p style="text-align:center;">
                    <?php 
                        $stringlength = strlen($students[0]['roll_no']);
                        if($stringlength>10)
                        {
                            $roll_no = substr($students[0]['roll_no'], 0, 10);
                        }
                        else
                        {
                            $roll_no = $students[0]['roll_no'];
                        }
                    ?>
            	<img src="<?php echo base_url();?>barcode.php?text=<?php echo $roll_no;?>&print=false" />
            </p>
        </div>
		
		<div style="float:left;width:187px; position:relative; height:324px; background-color:#EEE; border-radius:10px; padding:10px; margin-left:50px;">
            <p style="font-size:9px;">Authorised Signature</p>
            <p>
            	<img src="<?php echo base_url();?>images/shahbaz-signature.png" alt="" width="60" style="padding-left:40px;" />
            </p>
            <p style="text-align:center; font-size:12px;">
            	The Student must possess this card while in college.<br />It is olligatory to produce this card on demand.
            </p>
            <p style="font-size:18px; color:#F00; font-weight:bold; margin-top:10px;">
            	SESSION : <?php echo $students[0]['session'];?>
            </p>
            <p style="text-align:center; margin-top:10px;">
            	<img  src="<?php echo base_url();?>qr/<?php echo $students[0]['student_id'];?>.png" alt="" width="100" />
            </p>
            <p style="text-align:center; font-size:9px; margin-top:10px;">
            	<?php echo $campus[0]['address'];?>
            </p>
            <p style="text-align:center; font-size:9px;">
            	Help Line: 03-111-333-080 Cell No. <?php echo $campus[0]['phone6'];?>
            </p>
            <p style="text-align:center; font-size:9px;">
            	www.shahbazcollegeofpharmacy.edu.pk
            </p>
        </div>
		<div style="height:50px;clear:both;"></div>
    </body>
</html>