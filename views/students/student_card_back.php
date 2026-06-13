<html>
	<head>
    	<title>Student Card</title>
        <style>
        	*{margin:0; padding:0}
        </style>
    </head>
    <body>
    	<div style="width:187px; position:relative; height:324px; background-color:#EEE; border-radius:10px; padding:10px">
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
            	<img src="<?php echo base_url();?>images/qr-code.png" alt="" width="100" />
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
    </body>
</html>