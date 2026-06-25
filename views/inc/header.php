<?php 
	if($this->session->has_userdata('logged_in')==''){
		redirect (base_url());
		}
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en" class="no-js">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
<meta charset="utf-8"/>
<title>Shahbaz College | Management System</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="width=device-width, initial-scale=1" name="viewport"/>
<meta content="" name="description"/>
<meta content="" name="author"/>
<!-- BEGIN GLOBAL MANDATORY STYLES -->
<link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
<link href="<?php echo base_url();?>assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo base_url();?>assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo base_url();?>assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo base_url();?>assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo base_url();?>assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css"/>
<!-- END GLOBAL MANDATORY STYLES -->
<!-- BEGIN PAGE LEVEL PLUGIN STYLES -->
<link href="<?php echo base_url();?>assets/global/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo base_url();?>assets/global/plugins/fullcalendar/fullcalendar.min.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo base_url();?>assets/global/plugins/jqvmap/jqvmap/jqvmap.css" rel="stylesheet" type="text/css"/>
<!-- END PAGE LEVEL PLUGIN STYLES -->
<!-- BEGIN PAGE STYLES -->
<link href="<?php echo base_url();?>assets/admin/pages/css/tasks.css" rel="stylesheet" type="text/css"/>
<!-- END PAGE STYLES -->
<!-- BEGIN THEME STYLES -->
<link href="<?php echo base_url();?>assets/global/css/components.css" id="style_components" rel="stylesheet" type="text/css"/>
<link href="<?php echo base_url();?>assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo base_url();?>assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo base_url();?>assets/admin/layout/css/themes/default.css" rel="stylesheet" type="text/css" id="style_color"/>
<link href="<?php echo base_url();?>assets/admin/layout/css/custom.css?ver=2" rel="stylesheet" type="text/css"/>
<!-- END THEME STYLES -->
<!---------START MANAGED TABLE STYLES------------->
<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/global/plugins/select2/select2.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<!---------END MANAGED TABLE STYLES--------------->
<!---------START DATE TIME PICKER STYLES------------->
<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/global/plugins/clockface/css/clockface.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/global/plugins/bootstrap-datepicker/css/datepicker3.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/global/plugins/bootstrap-colorpicker/css/colorpicker.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/global/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/global/plugins/bootstrap-wysihtml5/bootstrap-wysihtml5.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/global/plugins/bootstrap-markdown/css/bootstrap-markdown.min.css">
<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/global/plugins/bootstrap-summernote/summernote.css">
<!---------END DATE TIME PICKER STYLES------------->
<!-- BEGIN DROPDOWN STYLES -->
<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/global/plugins/bootstrap-select/bootstrap-select.min.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/global/plugins/select2/select2.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/global/plugins/jquery-multi-select/css/multi-select.css"/>
<!-- END DROPDOWN STYLES -->
<link rel="shortcut icon" href="favicon.ico"/>
<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css" />
<link href="<?php echo base_url();?>assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo base_url();?>assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo base_url();?>assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" href="<?php echo base_url();?>assets/crop/css/cropper.css">
<link href="<?php echo base_url();?>assets/global/dist/css/jquery.treegrid.css" rel="stylesheet">

<?php
	if($this->uri->segment(1)=='pos' && $this->uri->segment(2)=='invoice'):
?>
<link href="<?php echo base_url();?>assets/admin/pages/css/invoice.css" rel="stylesheet" type="text/css"/>
<?php
	endif;
?>
 <style>
        .sticky {
            position: fixed;
            top: 0;
            width: 100%;
        }

        .sticky + .content {
            padding-top: 102px;
        }

        .header {
            padding: 10px 16px;
            background: #555;
            color: #f1f1f1;
        }

		.item select{
			width:100% !important;
		}

    </style>



</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<!-- DOC: Apply "page-header-fixed-mobile" and "page-footer-fixed-mobile" class to body element to force fixed header or footer in mobile devices -->
<!-- DOC: Apply "page-sidebar-closed" class to the body and "page-sidebar-menu-closed" class to the sidebar menu element to hide the sidebar by default -->
<!-- DOC: Apply "page-sidebar-hide" class to the body to make the sidebar completely hidden on toggle -->
<!-- DOC: Apply "page-sidebar-closed-hide-logo" class to the body element to make the logo hidden on sidebar toggle -->
<!-- DOC: Apply "page-sidebar-hide" class to body element to completely hide the sidebar on sidebar toggle -->
<!-- DOC: Apply "page-sidebar-fixed" class to have fixed sidebar -->
<!-- DOC: Apply "page-footer-fixed" class to the body element to have fixed footer -->
<!-- DOC: Apply "page-sidebar-reversed" class to put the sidebar on the right side -->
<!-- DOC: Apply "page-full-width" class to the body element to have full width page without the sidebar menu -->
<body class="page-header-fixed page-quick-sidebar-over-content page-sidebar-closed-hide-logo page-container-bg-solid">
<!-- BEGIN HEADER -->
<div class="page-header -i navbar navbar-fixed-top">
	<!-- BEGIN HEADER INNER -->
	<div class="page-header-inner">
		<!-- BEGIN LOGO -->
		<div class="page-logo">
			<a href="<?php echo base_url();?>">
				<!--<img src="<?php //echo base_url();?>assets/admin/layout/img/logo.png" alt="logo" class="logo-default"/>-->
                <h4>SHAHBAZ COLLEGE</h4>
			</a>
			<div class="menu-toggler sidebar-toggler hide">
			</div>
		</div>
		<!-- END LOGO -->
        <!--TOP HEADER SEARCH BAR-->
        
        <!--END HEADER SEARCH BAR-->
		<!-- BEGIN RESPONSIVE MENU TOGGLER -->
		<a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse">
		</a>
		<!-- END RESPONSIVE MENU TOGGLER -->
		<!-- BEGIN TOP NAVIGATION MENU -->
        <div class="top-menu">
			<ul class="nav navbar-nav pull-right">
				<!-- BEGIN NOTIFICATION DROPDOWN -->
				<!-- DOC: Apply "dropdown-dark" class after below "dropdown-extended" to change the dropdown styte -->
				<li>
                    <?php  
                        $this->db->select('designation_name, description');
                        $this->db->from('designations');
                        $desig = $this->db
                            ->where_in("designation_id", explode(",", $this->session->userdata('designation_id')))
                            ->get()
                            ->result_array();
                    
                        if (count($desig) > 0):
                    ?>
                    <div style="margin-right: 25px; text-align: center; font-size: 20px; margin-top: 10px;">
                        <?php foreach($desig as $index => $d): ?>
                            <a href="javascript:void(0);"
                               class="designation-popup"
                               data-name="<?php echo htmlspecialchars($d['designation_name'], ENT_QUOTES, 'UTF-8'); ?>"
                               data-description="<?php echo htmlspecialchars($d['description'], ENT_QUOTES, 'UTF-8'); ?>"
                               style="color: white; font-weight: bolder;">
                                <?php echo $d['designation_name']; ?>
                            </a><?php echo ($index < count($desig)-1) ? ', ' : ''; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </li>
                <li>
                    <?php  
                        $this->db->select( 'id,remaining_amount');
                        $this->db->from('petty_cash_college_wise');
                        $petty=$this->db->where('assign_to = "'.$this->session->userdata('user_id').'" and petty_status = 1' )->get()->result_array();

                        if (count($petty) > 0):
                    ?>
                    <a target="_blank" style="color: white; font-weight: bolder; text-align: center;margin-right: 25px; font-size: 20px;"  href="<?php echo site_url().'/pettycash/pettycash_statement/'.$petty[0]['id'] ?>">
                        Petty Cash : <?php echo my_pettycash(); ?>
                    </a>
                    
                    <?php endif; ?>
                </li>
                <li>
                    <div style="margin-top:7px;">
                        <form class="form-horizontal" target="_blank" role="form" method="post" action="<?php echo site_url();?>/students/search" enctype="multipart/form-data">
                            <input type="text" class="form-control input-inline input-medium" name="search" placeholder="Any Query" value="" required>
                            <input type="submit" class="btn green" name="student_check" value="Search"  >
                        </form>
                    </div>
                </li>
                <?php if (@$myAccess[0]['accounts_sidebar']==1 || $this->session->userdata('role')=='Admin'): ?>
                <li class="dropdown dropdown-extended dropdown-notification" id="header_notification_bar">
					<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
					<i class="fa fa-bank"></i>
					<!--<span class="badge badge-default">
					7 </span>-->
					</a>
					<ul class="dropdown-menu">
						<li class="external">
							<h3><span class="bold">Manage</span> Accounts</h3>
							<!--<a href="extra_profile.html">view all</a>-->
						</li>
						<li>
							<ul class="dropdown-menu-list scroller" style="height: 250px;" data-handle-color="#637283">
								<li>
									<a href="<?php echo site_url();?>/accounts/account_details">
									<span class="details">
									<span class="label label-sm label-icon label-success">
									<i class="fa fa-bank"></i>
									</span>
									Account Details </span>
									</a>
								</li>
								<li>
									<a href="<?php echo site_url();?>/accounts">
									<span class="details">
									<span class="label label-sm label-icon label-success">
									<i class="fa fa-money"></i>
									</span>
									Profit Distribution Campus Wise </span>
									</a>
								</li>
                                <li>
                                    <a href="<?php echo site_url();?>/accounts/chart_of_accounts">
                                    <span class="details">
                                    <span class="label label-sm label-icon label-success">
                                    <i class="fa fa-sitemap"></i>
                                    </span>
                                    Chart of Accounts </span>
                                    </a>
                                </li>
                                <li>
									<a href="<?php echo site_url();?>/pettycash/index">
									<span class="details">
									<span class="label label-sm label-icon label-success">
									<i class="fa fa-money"></i>
									</span>
									Campus PettyCash </span>
									</a>
								</li>
                                <li>
									<a href="<?php echo site_url();?>/accounts/advance">
									<span class="details">
									<span class="label label-sm label-icon label-success">
									<i class="fa fa-check"></i>
									</span>
									Advance System </span>
									</a>
								</li>
                                <?php if(@$myAccess[0]['loan_approval_accounts']==1 || $this->session->userdata('role')=='Admin'): ?>
                                <li>
									<a href="<?php echo site_url();?>/loans/accounts_loans_list">
									<span class="details">
									<span class="label label-sm label-icon label-success">
									<i class="fa fa-money"></i>
									</span>
									Loans Approval Accounts </span>
									</a>
								</li>
                                <?php endif;?>
                                <?php if(@$myAccess[0]['dailyclosing']==1 || $this->session->userdata('role')=='Admin'): ?>
                                <li>
									<a href="<?php echo site_url();?>/closing/index">
									<span class="details">
									<span class="label label-sm label-icon label-success">
									<i class="fa fa-clock-o"></i>
									</span>
									Daily Closings </span>
									</a>
								</li>
                                <?php endif;?>
                                <?php if(@$myAccess[0]['closing_reconcile']==1 || $this->session->userdata('role')=='Admin'): ?>
                                <li>
									<a href="<?php echo site_url();?>/closing/accountsclosing">
									<span class="details">
									<span class="label label-sm label-icon label-success">
									<i class="fa fa-check"></i>
									</span>
									Closings conciliation </span>
									</a>
								</li>
                                <?php endif;?>
                                <?php if(@$myAccess[0]['bank_reconciliation']==1 || $this->session->userdata('role')=='Admin'): ?>
                                <li>
									<a href="<?php echo site_url();?>/accounts/uploadstatement">
									<span class="details">
									<span class="label label-sm label-icon label-success">
									<i class="fa fa-list"></i>
									</span>
									Bank Statement Reconciliation </span>
									</a>
								</li>
                                <li>
									<a href="<?php echo site_url();?>/excel_import/index">
									<span class="details">
									<span class="label label-sm label-icon label-success">
									<i class="fa fa-list"></i>
									</span>
									PayPro Statement Reconciliation </span>
									</a>
								</li>
                                <li>
									<a href="<?php echo site_url();?>/excel_import/unpaid_entries">
									<span class="details">
									<span class="label label-sm label-icon label-success">
									<i class="fa fa-link"></i>
									</span>
									Untagged Paypro Entries </span>
									</a>
								</li>
                                <li>
									<a href="<?php echo site_url();?>/reports/PettyCashReport">
									<span class="details">
									<span class="label label-sm label-icon label-success">
									<i class="fa fa-clock-o"></i>
									</span>
									Day Closing </span>
									</a>
								</li>
                                <?php endif;?>
							</ul>
						</li>
					</ul>
				</li>
                <?php endif;?>
				<li class="dropdown dropdown-extended dropdown-notification" id="header_notification_bar">
					<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
					<i class="fa fa-graduation-cap"></i>
					<!--<span class="badge badge-default">
					7 </span>-->
					</a>
					<ul class="dropdown-menu">
						<li class="external">
							<h3><span class="bold">Manage</span> Students</h3>
							<!--<a href="extra_profile.html">view all</a>-->
						</li>
						<li>
							<ul class="dropdown-menu-list scroller" style="height: 250px;" data-handle-color="#637283">
								<li>
									<a href="<?php echo site_url();?>/students/add_student">
									<!--<span class="time">just now</span>-->
									<span class="details">
									<span class="label label-sm label-icon label-success">
									<i class="fa fa-plus"></i>
									</span>
									Add Student </span>
									</a>
								</li>
								<li>
									<a href="<?php echo site_url();?>/students/all_students">
									<!--<span class="time">just now</span>-->
									<span class="details">
									<span class="label label-sm label-icon label-success">
									<i class="fa fa-users"></i>
									</span>
									All Students </span>
									</a>
								</li>
							</ul>
						</li>
					</ul>
				</li>
				<?php
                    if(@$myAccess[0]['expense_sidebar']==1 || $this->session->userdata('role')=='Admin'):
                        ?>
                <li class="dropdown dropdown-extended dropdown-notification" id="header_notification_bar">
					<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
					<i class="fa fa-money"></i>
					<!--<span class="badge badge-default">
					7 </span>-->
					</a>
					<ul class="dropdown-menu">
						<li class="external">
							<h3><span class="bold">Manage</span> Expenses</h3>
							<!--<a href="extra_profile.html">view all</a>-->
						</li>
						<li>
							<ul class="dropdown-menu-list scroller" style="height: 250px;" data-handle-color="#637283">
							    <?php
                                if(@$myAccess[0]['expense_add']==1 || $this->session->userdata('role')=='Admin'):
                                    ?>
								<li>
									<a href="<?php echo site_url();?>/expenses/add_expense">
									<!--<span class="time">just now</span>-->
									<span class="details">
									<span class="label label-sm label-icon label-success">
									<i class="fa fa-plus"></i>
									</span>
									Add Expense </span>
									</a>
								</li>
								<?php endif;?>
								<?php
                                if(@$myAccess[0]['expense_all']==1 || $this->session->userdata('role')=='Admin'):
                                    ?>
								<li>
									<a href="<?php echo site_url();?>/expenses/all_expenses">
									<!--<span class="time">just now</span>-->
									<span class="details">
									<span class="label label-sm label-icon label-success">
									<i class="fa fa-money"></i>
									</span>
									All Expenses </span>
									</a>
								</li>
								<?php endif;?>
							</ul>
						</li>
					</ul>
				</li>
				<?php endif;?>
                <li class="dropdown dropdown-extended dropdown-notification" id="header_notification_bar">
					<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
					<i class="fa fa-users"></i>
					<!--<span class="badge badge-default">
					7 </span>-->
					</a>
					<ul class="dropdown-menu">
						<li class="external">
							<h3><span class="bold">Manage</span> Visitors</h3>
							<!--<a href="extra_profile.html">view all</a>-->
						</li>
						<li>
							<ul class="dropdown-menu-list scroller" style="height: 250px;" data-handle-color="#637283">
								<li>
									<a href="<?php echo site_url();?>/visitors/add_visitor">
									<!--<span class="time">just now</span>-->
									<span class="details">
									<span class="label label-sm label-icon label-success">
									<i class="fa fa-plus"></i>
									</span>
									Add Visitor </span>
									</a>
								</li>
								<li>
									<a href="<?php echo site_url();?>/visitors/all_visitors">
									<!--<span class="time">just now</span>-->
									<span class="details">
									<span class="label label-sm label-icon label-success">
									<i class="fa fa-users"></i>
									</span>
									All Visitors </span>
									</a>
								</li>
							</ul>
						</li>
					</ul>
				</li>
				<!-- END NOTIFICATION DROPDOWN -->
				<!-- BEGIN USER LOGIN DROPDOWN -->
				<!-- DOC: Apply "dropdown-dark" class after below "dropdown-extended" to change the dropdown styte -->
				<li class="dropdown dropdown-user">
                    <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                    <!--<img alt="" class="img-circle" src="<?php //echo base_url();?>assets/admin/layout/img/avatar3_small.jpg"/>-->
                    <img alt="" class="img-circle" src="<?php echo base_url();?>images/profile-pic.png"/>
                    <span class="username username-hide-on-mobile">
                    <?php echo $this->session->userdata('name'); ?> </span>
                    <i class="fa fa-angle-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-default">
                        <li>
                            <a href="<?php echo site_url();?>/profile">
                            <i class="icon-user"></i> Edit Profile </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url();?>/logout">
                            <i class="icon-key"></i> Log Out </a>
                        </li>
                    </ul>
                </li>
				<!-- END USER LOGIN DROPDOWN -->
				<!-- BEGIN QUICK SIDEBAR TOGGLER -->
				<!-- DOC: Apply "dropdown-dark" class after below "dropdown-extended" to change the dropdown styte -->
				<!-- END QUICK SIDEBAR TOGGLER -->
			</ul>
		</div>
		<!-- END TOP NAVIGATION MENU -->
        <!--
		<div class="top-menu" style="width: 85%">

            <ul class="nav navbar-nav pull-left">
                <li class="nav-item dropdown">
                    <div style="margin-top: 7px;">
                        <form class="form-horizontal" target="_blank" role="form" method="post" action="<?php echo site_url();?>/students/search" enctype="multipart/form-data">

                            <input type="text" class="form-control input-inline input-medium" name="search" placeholder="Any Query" value="" required>

                            <input type="submit" class="btn green" name="student_check" value="Search"  >
                        </form>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                        <i class="fa fa-graduation-cap"></i>
                        <span class="title">Students</span>
                        <span class="selected"></span>
                        <span class="arrow <?php if($this->uri->segment(1)=='students'){echo 'open';}?>"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="head text-light bg-dark">
                            <a href="<?php echo site_url();?>/students/add_student">
                                <i class="icon-plus"></i>
                                Add Student</a>
                        </li>
                        <li class="head text-light bg-dark">
                            <a href="<?php echo site_url();?>/students/all_students">
                                <i class="icon-list"></i>
                                All Students</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                        <i class="fa fa-money"></i>
                        <span class="title">Expenses</span>
                        <span class="selected"></span>
                        <span class="arrow <?php if($this->uri->segment(1)=='students'){echo 'open';}?>"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="head text-light bg-dark">
                            <a href="<?php echo site_url();?>/expenses/add_expense">
                                <i class="icon-plus"></i>
                                Add Expense</a>
                        </li>
                        <li class="head text-light bg-dark">
                            <a href="<?php echo site_url();?>/expenses/all_expenses">
                                <i class="icon-list"></i>
                                All Expenses</a>
                        </li>
                        <li class="head text-light bg-dark">
                            <a href="<?php echo site_url();?>/expenses/all_expenses_report">
                                <i class="icon-list"></i>
                                All Expenses Report</a>
                        </li>
                        <li class="head text-light bg-dark">
                            <a href="<?php echo site_url();?>/expenses/category">
                                <i class="fa fa-sitemap"></i>
                                Manage Category</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                        <i class="fa fa-users"></i>
                        <span class="title">Visitors</span>
                        <span class="selected"></span>
                        <span class="arrow <?php if($this->uri->segment(1)=='visitors'){echo 'open';}?>"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="<?php if($this->uri->segment(2)=='add_visitor'){echo 'active';}?>">
                            <a href="<?php echo site_url();?>/visitors/add_visitor">
                                <i class="icon-plus"></i>
                                Add Visitor</a>
                        </li>
                        <li class="<?php if($this->uri->segment(2)=='all_visitors'){echo 'active';}?>">
                            <a href="<?php echo site_url();?>/visitors/all_visitors">
                                <i class="icon-list"></i>
                                All Visitors</a>
                        </li>
                    </ul>
                </li>
            </ul>

            <ul class="nav navbar-nav pull-right">

                    <li class="nav-item dropdown">
                            <a class="nav-link text-light" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-bell-o" style="color: white"></i>
                                <span class="badge" id="bcount" style="background-color: red; font-weight: bold">

                                    <?php echo getnotificationscount($this->session->userdata('user_id')) ?></span>

                            </a>
                            <ul class="dropdown-menu" id="DIG">
                                        <li class="head text-light bg-dark">
                                            <div class="row">
                                                <div class="col-lg-12 col-sm-12 col-12">
                                                    <span>Notifications </span>
                                                </div>
                                        </li>
                                        <hr>

                                        <?php echo getnotifications($this->session->userdata('user_id')) ?>

                                    </ul>
                        </li>
                    <li class="dropdown dropdown-user">
                        <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                        <!--<img alt="" class="img-circle" src="<?php //echo base_url();?>assets/admin/layout/img/avatar3_small.jpg"/>->
                        <img alt="" class="img-circle" src="<?php echo base_url();?>images/profile-pic.png"/>
                        <span class="username username-hide-on-mobile">
                        <?php echo $this->session->userdata('name'); ?> </span>
                        <i class="fa fa-angle-down"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-default">
                                        <li>
                                            <a href="<?php echo site_url();?>/profile">
                                            <i class="icon-user"></i> Edit Profile </a>
                                        </li>
                                        <li>
                                            <a href="<?php echo site_url();?>/logout">
                                            <i class="icon-key"></i> Log Out </a>
                                        </li>
                                    </ul>
                    </li>
            </ul>

            <ul class="nav navbar-nav pull-right">
                <li class="dropdown dropdown-user">
				<div style="margin-top: 7px;">
						 <?php  $this->db->select( 'id,remaining_amount');
						$this->db->from('petty_cash_college_wise');
						$petty=$this->db->where('assign_to = "'.$this->session->userdata('user_id').'" and petty_status = 1' )->get()->result_array();

						if (count($petty) > 0):
						?>
						<a data-toggle="modal" target="_blank" style="color: white; font-weight: bolder; text-align: center; margin-top: 6px;margin-right: 25px; font-size: 24px;"  title="Add this item"  href="<?php echo site_url().'/pettycash/pettycash_statement/'.$petty[0]['id'] ?>">
																   Petty Cash : <?php echo my_pettycash(); ?>
																</a>
						
						<?php endif; ?>
						</div>
                </li>
            </ul>

		</div>
		<!-- END TOP NAVIGATION MENU -->
	</div>
	<!-- END HEADER INNER -->
</div>
<div id="designationModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title" id="designationModalTitle">Designation Responsibilities</h4>
            </div>
            <div class="modal-body">
                <p id="designationModalDescription" style="white-space: pre-line; font-size: 15px;"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- END HEADER -->
<div class="clearfix">
</div>
