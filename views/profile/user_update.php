<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
    <meta charset="utf-8"/>
    <title>Shahbaz College Login</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <meta content="STEP AHEAD complete school management system" name="description"/>
    <meta content="Muhammad Umar" name="author"/>
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
    <link href="<?php echo base_url();?>assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo base_url();?>assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo base_url();?>assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo base_url();?>assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
    <!-- END GLOBAL MANDATORY STYLES -->
    <!-- BEGIN PAGE LEVEL STYLES -->
    <link href="<?php echo base_url();?>assets/admin/pages/css/login2.css" rel="stylesheet" type="text/css"/>
    <!-- END PAGE LEVEL SCRIPTS -->
    <!-- BEGIN THEME STYLES -->
    <link href="<?php echo base_url();?>assets/global/css/components.css" id="style_components" rel="stylesheet" type="text/css"/>
    <link href="<?php echo base_url();?>assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo base_url();?>assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo base_url();?>assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css" id="style_color"/>
    <link href="<?php echo base_url();?>assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>
    <!-- END THEME STYLES -->
    <link rel="shortcut icon" href="favicon.ico"/>
    <style>
        .sf-snow-flake {
            position: fixed;
            top: -20px;
            z-index: 99999;
        }
        .sf-snow-anim {
            top: 110%;
        }
    </style>
</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<body class="login">
<!-- BEGIN SIDEBAR TOGGLER BUTTON -->
<div class="menu-toggler sidebar-toggler">
</div>
<!-- END SIDEBAR TOGGLER BUTTON -->
<!-- BEGIN LOGO -->
<div class="logo">
    <a href="#">
        <h1>Shahbaz College</h1>
    </a>
</div>
<!-- END LOGO -->
<!-- BEGIN LOGIN -->
<div class="content">
	<!-- BEGIN CONTENT -->
    <!-- END PAGE HEADER-->
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
            <div class="col-md-12 ">
                <!-- BEGIN SAMPLE FORM PORTLET-->
                <div class="portlet box green ">
                    <div class="portlet-title" style="background-color: #6ba3c8;">
                        <div class="caption">
                            <i class="fa fa-user"></i> Reset Password
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <?php
                            foreach ($users as $user):
                        ?>
                        <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/login/update">
                            <div class="form-body">

                                <input type="hidden" class="form-control input-inline input-medium" name="first_name" placeholder="Enter first name" value="<?php echo @$user['first_name']?>">
                                <input type="hidden" class="form-control input-inline input-medium" name="last_name" placeholder="Enter last name" value="<?php echo @$user['last_name']?>">
                                <input type="hidden" class="form-control input-inline input-medium" name="email" placeholder="Enter email" value="<?php echo @$user['email']?>">
                                <input type="hidden" class="form-control input-inline input-medium" name="username" placeholder="Enter username" value="<?php echo @$user['username']?>" readonly="readonly">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Password <span class="required">*</span></label>
                                    <div class="col-md-9">
                                        <input type="password" class="form-control input-inline input-medium profile_password" name="password" placeholder="Enter password" value="" required>
                                        <span class="help-inline"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Retype Password <span class="required">*</span></label>
                                    <div class="col-md-9">
                                        <input type="password" class="form-control input-inline input-medium profile_password" name="r-password" placeholder="Retype password" value="" required>
                                        <span class="help-inline"></span>
                                    </div>
                                </div>
                                <input type="hidden" class="form-control input-inline input-medium" name="role"  value="<?php echo @$user['role']?>" readonly="readonly">

                            </div>
                            <div>
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <input type="hidden" name="status" value="1" />
                                        <input type="hidden" name="user_id" value="<?php echo @$user['user_id']?>" />
                                        <button type="submit" class="btn info" style="background-color: #6ba3c8;">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <?php
                            endforeach;
                        ?>
                    </div>
                </div>
                <!-- END SAMPLE FORM PORTLET-->
            </div>
        </div>
        <!-- END PAGE CONTENT-->

	<!-- END CONTENT -->
</div>
<div class="copyright hide">
    <?php echo date('Y')?> &copy; School System. All Rights Reserved.
</div>
<!-- END LOGIN -->
<!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
<!-- BEGIN CORE PLUGINS -->
<!--[if lt IE 9]>
<script src="<?php echo base_url();?>assets/global/plugins/respond.min.js"></script>
<script src="<?php echo base_url();?>assets/global/plugins/excanvas.min.js"></script>
<![endif]-->
<script src="<?php echo base_url();?>assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>assets/global/plugins/jquery-migrate.min.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>assets/global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>assets/global/plugins/jquery.cokie.min.js" type="text/javascript"></script>
<!-- END CORE PLUGINS -->
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="<?php echo base_url();?>assets/global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="<?php echo base_url();?>assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>assets/admin/layout/scripts/layout.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>assets/admin/layout/scripts/demo.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>assets/admin/pages/scripts/login.js" type="text/javascript"></script>
<!-- END PAGE LEVEL SCRIPTS -->
<script>
    jQuery(document).ready(function() {
        Metronic.init(); // init metronic core components
        Layout.init(); // init current layout
        Login.init();
        Demo.init();
    });
</script>
<script src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/130527/h5ab-snow-flurry.js"></script>

<script>
    jQuery(document).ready(function($){
        $(document).snowFlurry({
            maxSize: 5,
            numberOfFlakes: 400,
            minSpeed: 20,
            maxSpeed: 30,
            color: '#fff',
            timeout: 0
        });
    });
</script>
<!-- END JAVASCRIPTS -->
</body>
<!-- END BODY -->
</html>