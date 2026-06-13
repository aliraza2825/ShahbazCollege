<?php
    include('qrcode/qrlib.php');
?>
<html>
<head>
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
    <link href="<?php echo base_url();?>assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo base_url();?>assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo base_url();?>assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo base_url();?>assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo base_url();?>assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css"/>
</head>
<body>
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Add Class <small>add your desire class</small>
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
            <?php 
                if($type=='new'):
                    $last_printed_qr = $last_printed_qr+1;

                    for($i = $last_printed_qr; $i < $last_printed_qr+$quantity; $i++)
                    {
                        if(file_exists(FCPATH.'qr/inv_qr-'.$i.'.png'))
                        {

                        }
                        else
                        {
                            QRcode::png('inv_qr-' . $i, 'qr/inv_qr-' . $i . '.png');
                        }
                    }
                    $this->db->set("number",$i-1);
                    $this->db->insert("inventory_qr");
                endif;

                if($type=='custom'):
                    for($i = $from_number; $i <= $to_number; $i++)
                    {
                        if(file_exists(FCPATH.'qr/inv_qr-'.$i.'.png'))
                        {

                        }
                        else
                        {
                            QRcode::png('inv_qr-' . $i, 'qr/inv_qr-' . $i . '.png');
                        }
                    }
                endif;
            ?>
			<div class="row">
                <table>
                    <tbody>
                        <tr>
                            <?php 
                                if($type=='new'):
                                    $counter=1;
                                    for($i=$last_printed_qr; $i<$last_printed_qr+$quantity;$i++):
                            ?>
                                <td><div style="text-align: center;"><img style="width: 120px; height: 120px;"  src="<?php echo base_url();?>qr/inv_qr-<?php echo $i; ?>.png" alt=""  /><br /><label><?php echo $i; ?></label></div></td>
                            <?php
                                    if($counter%11==0){echo '</tr><tr>';}
                                    $counter++;
                                    endfor;
                                endif;
                            ?>
                            <?php 
                                if($type=='old'):
                                    $counter=1;
                                    for($i=1; $i<=$quantity;$i++):
                            ?>
                                <td><div style="text-align: center;"><img style="width: 120px; height: 120px;"  src="<?php echo base_url();?>qr/inv_qr-<?php echo $qr_number; ?>.png" alt=""  /><br /><label><?php echo $qr_number; ?></label></div></td>
                            <?php
                                    if($counter%11==0){echo '</tr><tr>';}
                                    $counter++;
                                    endfor;
                                endif;
                            ?>
                            <?php 
                                if($type=='custom'):
                                    $counter=1;
                                    for($i=$from_number; $i<=$to_number;$i++):
                                        $checkProduct = $this->db->get_where('products',array('qr_code'=>'inv_qr-'.$i))->result_array();
                                        if(count($checkProduct)==0):
                            ?>
                                <td><div style="text-align: center;"><img style="width: 120px; height: 120px;"  src="<?php echo base_url();?>qr/inv_qr-<?php echo $i; ?>.png" alt=""  /><br /><label><?php echo $i; ?></label></div></td>
                            <?php
                                            if($counter%11==0){echo '</tr><tr>';}
                                            $counter++;
                                        endif;
                                    endfor;
                                endif;
                            ?>
                        </tr>
                    </tbody>
                </table>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->
</body>